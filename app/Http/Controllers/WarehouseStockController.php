<?php

namespace App\Http\Controllers;

use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WarehouseStockController extends Controller
{
    public function index(Request $request)
    {
        $stocks = WarehouseStock::query()
            ->with(['warehouse', 'product'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('warehouse', fn($w) => $w->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/WarehouseStocks/Index', [
            'stocks' => $stocks,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/WarehouseStocks/Create', [
            'warehouses' => Warehouse::select('id', 'name')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
                Rule::unique('warehouse_stocks')->where(fn($q) => $q
                    ->where('product_id', $request->product_id)
                    ->where('user_id', Auth::id())),
            ],
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'reserved_quantity' => 'nullable|numeric|min:0',
            'average_cost' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'last_received_at' => 'nullable|date',
            'last_issued_at' => 'nullable|date',
            'status' => 'nullable|boolean',
        ]);

        $authUser = Auth::user();

        $quantity = $request->quantity;
        $reserved = $request->reserved_quantity ?? 0;
        $averageCost = $request->average_cost ?? 0;

        WarehouseStock::create([
            'user_id' => $authUser->id,
            'warehouse_id' => $request->warehouse_id,
            'product_id' => $request->product_id,
            'quantity' => $quantity,
            'reserved_quantity' => $reserved,
            'available_quantity' => $quantity - $reserved,
            'average_cost' => $averageCost,
            'stock_value' => $quantity * $averageCost,
            'minimum_stock' => $request->minimum_stock ?? 0,
            'maximum_stock' => $request->maximum_stock,
            'reorder_level' => $request->reorder_level ?? 0,
            'last_received_at' => $request->last_received_at,
            'last_issued_at' => $request->last_issued_at,
            'status' => $request->status ?? true,
        ]);

        return redirect()->route('warehouse-stocks.index')->with('success', 'Warehouse stock created successfully');
    }

    public function edit(string $id)
    {
        $stock = WarehouseStock::findOrFail($id);

        return Inertia::render('InventoryManagement/WarehouseStocks/Edit', [
            'stock' => $stock,
            'warehouses' => Warehouse::select('id', 'name')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function show(string $id)
    {
        $stock = WarehouseStock::with(['warehouse', 'product'])->findOrFail($id);

        return Inertia::render('InventoryManagement/WarehouseStocks/Show', [
            'stock' => $stock,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $stock = WarehouseStock::findOrFail($id);

        $request->validate([
            'warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
                Rule::unique('warehouse_stocks')
                    ->where(fn($q) => $q
                        ->where('product_id', $request->product_id)
                        ->where('user_id', Auth::id()))
                    ->ignore($stock->id),
            ],
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'reserved_quantity' => 'nullable|numeric|min:0',
            'average_cost' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'last_received_at' => 'nullable|date',
            'last_issued_at' => 'nullable|date',
            'status' => 'nullable|boolean',
        ]);

        $quantity = $request->quantity;
        $reserved = $request->reserved_quantity ?? 0;
        $averageCost = $request->average_cost ?? 0;

        $stock->update([
            'warehouse_id' => $request->warehouse_id,
            'product_id' => $request->product_id,
            'quantity' => $quantity,
            'reserved_quantity' => $reserved,
            'available_quantity' => $quantity - $reserved,
            'average_cost' => $averageCost,
            'stock_value' => $quantity * $averageCost,
            'minimum_stock' => $request->minimum_stock ?? 0,
            'maximum_stock' => $request->maximum_stock,
            'reorder_level' => $request->reorder_level ?? 0,
            'last_received_at' => $request->last_received_at,
            'last_issued_at' => $request->last_issued_at,
            'status' => $request->status ?? true,
        ]);

        return redirect()->route('warehouse-stocks.index')->with('success', 'Warehouse stock updated successfully');
    }

    public function destroy(string $id)
    {
        $stock = WarehouseStock::findOrFail($id);
        $stock->delete();

        return redirect()->route('warehouse-stocks.index')->with('success', 'Warehouse stock deleted successfully');
    }
}
