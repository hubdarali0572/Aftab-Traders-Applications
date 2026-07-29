<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $stocks = Stock::query()
            ->with(['warehouse', 'product'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('warehouse', fn ($w) => $w->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/Stocks/Index', [
            'stocks' => $stocks,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/Stocks/Create', [
            'warehouses' => Warehouse::select('id', 'name')->orderBy('name')->get(),
            'products' => Product::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('stocks')->where(fn ($q) => $q
                    ->where('warehouse_id', $request->warehouse_id)
                    ->whereNull('deleted_at')),
            ],
            'minimum_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        Stock::create([
            'warehouse_id' => $request->warehouse_id,
            'product_id' => $request->product_id,
            'quantity' => 0,
            'average_cost' => 0,
            'minimum_stock' => $request->minimum_stock ?? 0,
            'reorder_level' => $request->reorder_level ?? 0,
        ]);

        return redirect()->route('stocks.index')->with('success', 'Stock record created successfully');
    }

    public function show(string $id)
    {
        $stock = Stock::with(['warehouse', 'product'])->findOrFail($id);

        return Inertia::render('InventoryManagement/Stocks/Show', [
            'stock' => $stock,
        ]);
    }

    public function edit(string $id)
    {
        $stock = Stock::with(['warehouse', 'product'])->findOrFail($id);

        return Inertia::render('InventoryManagement/Stocks/Edit', [
            'stock' => $stock,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $stock = Stock::findOrFail($id);

        $request->validate([
            'minimum_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        $stock->update($request->only(['minimum_stock', 'reorder_level']));

        return redirect()->route('stocks.index')->with('success', 'Stock thresholds updated successfully');
    }
}
