<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class StockAdjustmentController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

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
        $adjustment = StockAdjustment::withCount('details')->findOrFail($id);

        $request->validate([
            'reference_no' => 'required|string|unique:stock_adjustments,reference_no,' . $id,
            'adjustment_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_type' => 'required|in:increase,decrease',
            'total_quantity' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'boolean',
        ]);

        $mustResync = $adjustment->details_count > 0 && (
            $adjustment->warehouse_id != $request->warehouse_id ||
            $adjustment->adjustment_type !== $request->adjustment_type ||
            $adjustment->adjustment_date->format('Y-m-d') !== $request->adjustment_date ||
            $adjustment->reference_no !== $request->reference_no
        );

        try {
            DB::transaction(function () use ($adjustment, $request, $mustResync) {
                $adjustment->update($request->all());

                if ($mustResync) {
                    $this->posting->syncAdjustmentStock($adjustment->fresh());
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('stock-adjustments.index')->with('success', 'Adjustment updated successfully');
    }

    public function destroy(string $id)
    {
        $adjustment = StockAdjustment::with('details')->findOrFail($id);

        try {
            DB::transaction(function () use ($adjustment) {
                foreach ($adjustment->details as $detail) {
                    $this->posting->reverseAdjustmentDetail($detail);
                    $detail->delete();
                }

                $adjustment->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Adjustment deleted');
    }
}
