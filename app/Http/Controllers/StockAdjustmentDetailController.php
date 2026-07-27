<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustmentDetail;
use App\Models\StockAdjustment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StockAdjustmentDetailController extends Controller
{
    public function index(Request $request)
    {
        $details = StockAdjustmentDetail::query()
            ->with(['stockAdjustment', 'product'])
            // Search by Product Name or Adjustment Ref
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', fn($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('stockAdjustment', fn($s) => $s->where('reference_no', 'like', "%{$search}%"));
                });
            })
            // Filter by Parent Adjustment
            ->when($request->filled('adjustment_id'), function ($query) use ($request) {
                $query->where('stock_adjustment_id', $request->adjustment_id);
            })
            // Filter by Status
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/StockAdjustmentDetails/Index', [
            'details' => $details,
            'adjustments' => StockAdjustment::select('id', 'reference_no')->get(),
            'filters' => $request->only('search', 'adjustment_id', 'status'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/StockAdjustmentDetails/Create', [
            'adjustments' => StockAdjustment::select('id', 'reference_no')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'stock_adjustment_id' => [
                'required',
                Rule::unique('stock_adjustment_details')->where(fn($q) => $q->where('product_id', $request->product_id))
            ],
            'product_id' => 'required|exists:products,id',
            'system_quantity' => 'required|numeric',
            'physical_quantity' => 'required|numeric',
            'unit_cost' => 'required|numeric|min:0',
        ]);

        $adjQty = $request->physical_quantity - $request->system_quantity;

        StockAdjustmentDetail::create(array_merge($request->all(), [
            'adjustment_quantity' => $adjQty,
            'total_cost' => abs($adjQty) * $request->unit_cost
        ]));

        return redirect()->route('stock-adjustment-details.index')->with('success', 'Adjustment item added');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/StockAdjustmentDetails/Show', [
            'detail' => StockAdjustmentDetail::with(['stockAdjustment', 'product'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/StockAdjustmentDetails/Edit', [
            'detail' => StockAdjustmentDetail::findOrFail($id),
            'adjustments' => StockAdjustment::select('id', 'reference_no')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $detail = StockAdjustmentDetail::findOrFail($id);

        $request->validate([
            'stock_adjustment_id' => ['required', Rule::unique('stock_adjustment_details')->where(fn($q) => $q->where('product_id', $request->product_id))->ignore($id)],
            'product_id' => 'required|exists:products,id',
            'system_quantity' => 'required|numeric',
            'physical_quantity' => 'required|numeric',
            'unit_cost' => 'required|numeric|min:0',
        ]);

        $adjQty = $request->physical_quantity - $request->system_quantity;

        $detail->update(array_merge($request->all(), [
            'adjustment_quantity' => $adjQty,
            'total_cost' => abs($adjQty) * $request->unit_cost
        ]));

        return redirect()->route('stock-adjustment-details.index')->with('success', 'Adjustment item updated');
    }

    public function destroy(string $id)
    {
        StockAdjustmentDetail::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Item removed');
    }
}
