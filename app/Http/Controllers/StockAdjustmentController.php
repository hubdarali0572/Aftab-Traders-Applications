<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $adjustments = StockAdjustment::query()
            ->with(['warehouse', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('warehouse', fn($w) => $w->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/StockAdjustments/Index', [
            'adjustments' => $adjustments,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/StockAdjustments/Create', [
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_no' => 'required|string|unique:stock_adjustments,reference_no',
            'adjustment_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_type' => 'required|in:increase,decrease',
            'total_quantity' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        StockAdjustment::create(array_merge($request->all(), ['user_id' => Auth::id()]));

        return redirect()->route('stock-adjustments.index')->with('success', 'Adjustment recorded successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/StockAdjustments/Show', [
            'adjustment' => StockAdjustment::with(['warehouse', 'user', 'details.product'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/StockAdjustments/Edit', [
            'adjustment' => StockAdjustment::findOrFail($id),
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $adjustment = StockAdjustment::findOrFail($id);

        $request->validate([
            'reference_no' => 'required|string|unique:stock_adjustments,reference_no,' . $id,
            'adjustment_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_type' => 'required|in:increase,decrease',
            'total_quantity' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'boolean',
        ]);

        $adjustment->update($request->all());

        return redirect()->route('stock-adjustments.index')->with('success', 'Adjustment updated successfully');
    }

    public function destroy(string $id)
    {
        StockAdjustment::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Adjustment deleted');
    }
}
