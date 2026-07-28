<?php

namespace App\Http\Controllers;

use App\Models\DamagedStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DamagedStockController extends Controller
{
    public function index(Request $request)
    {
        $stocks = DamagedStock::query()
            ->with(['warehouse', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('warehouse', fn ($w) => $w->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/DamagedStocks/Index', [
            'stocks' => $stocks,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/DamagedStocks/Create', [
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_no' => 'required|string|unique:damaged_stocks,reference_no',
            'damage_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'total_quantity' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        DamagedStock::create(array_merge($request->all(), ['user_id' => Auth::id()]));

        return redirect()->route('damaged-stocks.index')->with('success', 'Damaged stock record created successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/DamagedStocks/Show', [
            'stock' => DamagedStock::with(['warehouse', 'user', 'details.product'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/DamagedStocks/Edit', [
            'stock' => DamagedStock::findOrFail($id),
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $stock = DamagedStock::findOrFail($id);

        $request->validate([
            'reference_no' => 'required|string|unique:damaged_stocks,reference_no,' . $id,
            'damage_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'total_quantity' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $stock->update($request->all());

        return redirect()->route('damaged-stocks.index')->with('success', 'Damaged stock record updated successfully');
    }

    public function destroy(string $id)
    {
        DamagedStock::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Damaged stock record deleted successfully');
    }
}
