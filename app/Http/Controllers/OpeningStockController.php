<?php

namespace App\Http\Controllers;

use App\Models\OpeningStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OpeningStockController extends Controller
{
    public function index(Request $request)
    {
        $stocks = OpeningStock::query()
            ->with(['warehouse', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where('reference_no', 'like', "%{$search}%")
                      ->orWhereHas('warehouse', fn($w) => $w->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/OpeningStocks/Index', [
            'stocks' => $stocks,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/OpeningStocks/Create', [
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_no' => 'required|string|unique:opening_stocks,reference_no',
            'opening_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'total_quantity' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        OpeningStock::create(array_merge($request->all(), ['user_id' => Auth::id()]));

        return redirect()->route('opening-stocks.index')->with('success', 'Opening stock created successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/OpeningStocks/Show', [
            'stock' => OpeningStock::with(['warehouse', 'user', 'details.product'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/OpeningStocks/Edit', [
            'stock' => OpeningStock::findOrFail($id),
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $stock = OpeningStock::findOrFail($id);

        $request->validate([
            'reference_no' => 'required|string|unique:opening_stocks,reference_no,' . $id,
            'opening_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'total_quantity' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $stock->update($request->all());

        return redirect()->route('opening-stocks.index')->with('success', 'Opening stock updated successfully');
    }

    public function destroy(string $id)
    {
        OpeningStock::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Opening stock deleted successfully');
    }
}