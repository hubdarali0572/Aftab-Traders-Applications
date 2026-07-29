<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
            ->with(['warehouse', 'user', 'items'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('warehouse', fn ($w) => $w->where('name', 'like', "%{$search}%"));
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
            'products' => Product::select('id', 'name', 'purchase_price')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_no' => 'required|string|unique:stock_adjustments,reference_no',
            'adjustment_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.adjustment_quantity' => 'required|numeric|not_in:0',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $header = StockAdjustment::create([
                    'user_id' => Auth::id(),
                    'warehouse_id' => $request->warehouse_id,
                    'reference_no' => $request->reference_no,
                    'adjustment_date' => $request->adjustment_date,
                    'remarks' => $request->remarks,
                    'status' => $request->boolean('status', true),
                    'total_quantity' => 0,
                    'total_amount' => 0,
                ]);

                foreach ($request->items as $row) {
                    $qty = (float) $row['adjustment_quantity'];
                    $item = $header->items()->create([
                        'product_id' => $row['product_id'],
                        'adjustment_quantity' => $qty,
                        'unit_cost' => $row['unit_cost'],
                        'total_cost' => round(abs($qty) * (float) $row['unit_cost'], 2),
                        'reason' => $row['reason'] ?? null,
                    ]);
                    $this->posting->postAdjustmentItem($item);
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('stock-adjustments.index')->with('success', 'Adjustment recorded successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/StockAdjustments/Show', [
            'adjustment' => StockAdjustment::with(['warehouse', 'user', 'items.product'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/StockAdjustments/Edit', [
            'adjustment' => StockAdjustment::with('items.product')->findOrFail($id),
            'warehouses' => Warehouse::select('id', 'name')->get(),
            'products' => Product::select('id', 'name', 'purchase_price')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $adjustment = StockAdjustment::with('items')->findOrFail($id);

        $request->validate([
            'reference_no' => 'required|string|unique:stock_adjustments,reference_no,' . $id,
            'adjustment_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.adjustment_quantity' => 'required|numeric|not_in:0',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        $mustResync = $adjustment->warehouse_id != $request->warehouse_id
            || $adjustment->adjustment_date->format('Y-m-d') !== $request->adjustment_date
            || $adjustment->reference_no !== $request->reference_no;

        try {
            DB::transaction(function () use ($adjustment, $request, $mustResync) {
                foreach ($adjustment->items as $item) {
                    $this->posting->reverseAdjustmentItem($item);
                }

                $adjustment->update([
                    'reference_no' => $request->reference_no,
                    'adjustment_date' => $request->adjustment_date,
                    'warehouse_id' => $request->warehouse_id,
                    'remarks' => $request->remarks,
                    'status' => $request->boolean('status', true),
                ]);

                $adjustment->items()->delete();

                foreach ($request->items as $row) {
                    $qty = (float) $row['adjustment_quantity'];
                    $item = $adjustment->items()->create([
                        'product_id' => $row['product_id'],
                        'adjustment_quantity' => $qty,
                        'unit_cost' => $row['unit_cost'],
                        'total_cost' => round(abs($qty) * (float) $row['unit_cost'], 2),
                        'reason' => $row['reason'] ?? null,
                    ]);
                    $this->posting->postAdjustmentItem($item);
                }

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
        $adjustment = StockAdjustment::with('items')->findOrFail($id);

        try {
            DB::transaction(function () use ($adjustment) {
                foreach ($adjustment->items as $item) {
                    $this->posting->reverseAdjustmentItem($item);
                    $item->delete();
                }

                $adjustment->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Adjustment deleted');
    }
}
