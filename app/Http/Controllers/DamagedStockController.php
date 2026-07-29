<?php

namespace App\Http\Controllers;

use App\Models\DamagedStock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class DamagedStockController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

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
            'products' => Product::select('id', 'name', 'purchase_price')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_no' => 'required|string|unique:damaged_stocks,reference_no',
            'damage_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.damage_reason' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $header = DamagedStock::create([
                    'user_id' => Auth::id(),
                    'warehouse_id' => $request->warehouse_id,
                    'reference_no' => $request->reference_no,
                    'damage_date' => $request->damage_date,
                    'remarks' => $request->remarks,
                    'status' => $request->boolean('status', true),
                    'total_quantity' => 0,
                    'total_amount' => 0,
                ]);

                foreach ($request->items as $row) {
                    $item = $header->items()->create([
                        'product_id' => $row['product_id'],
                        'quantity' => $row['quantity'],
                        'unit_cost' => $row['unit_cost'],
                        'total_cost' => round((float) $row['quantity'] * (float) $row['unit_cost'], 2),
                        'damage_reason' => $row['damage_reason'] ?? null,
                    ]);
                    $this->posting->postDamagedItem($item);
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('damaged-stocks.index')->with('success', 'Damaged stock record created successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/DamagedStocks/Show', [
            'stock' => DamagedStock::with(['warehouse', 'user', 'items.product'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/DamagedStocks/Edit', [
            'stock' => DamagedStock::with('items.product')->findOrFail($id),
            'warehouses' => Warehouse::select('id', 'name')->get(),
            'products' => Product::select('id', 'name', 'purchase_price')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $stock = DamagedStock::with('items')->findOrFail($id);

        $request->validate([
            'reference_no' => 'required|string|unique:damaged_stocks,reference_no,' . $id,
            'damage_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.damage_reason' => 'nullable|string',
        ]);

        $mustResync = $stock->warehouse_id != $request->warehouse_id
            || $stock->damage_date->format('Y-m-d') !== $request->damage_date
            || $stock->reference_no !== $request->reference_no;

        try {
            DB::transaction(function () use ($stock, $request, $mustResync) {
                foreach ($stock->items as $item) {
                    $this->posting->reverseDamagedItem($item);
                }

                $stock->update([
                    'reference_no' => $request->reference_no,
                    'damage_date' => $request->damage_date,
                    'warehouse_id' => $request->warehouse_id,
                    'remarks' => $request->remarks,
                    'status' => $request->boolean('status', true),
                ]);

                $stock->items()->delete();

                foreach ($request->items as $row) {
                    $item = $stock->items()->create([
                        'product_id' => $row['product_id'],
                        'quantity' => $row['quantity'],
                        'unit_cost' => $row['unit_cost'],
                        'total_cost' => round((float) $row['quantity'] * (float) $row['unit_cost'], 2),
                        'damage_reason' => $row['damage_reason'] ?? null,
                    ]);
                    $this->posting->postDamagedItem($item);
                }

                if ($mustResync) {
                    $this->posting->syncDamagedStock($stock->fresh());
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('damaged-stocks.index')->with('success', 'Damaged stock record updated successfully');
    }

    public function destroy(string $id)
    {
        $stock = DamagedStock::with('items')->findOrFail($id);

        try {
            DB::transaction(function () use ($stock) {
                foreach ($stock->items as $item) {
                    $this->posting->reverseDamagedItem($item);
                    $item->delete();
                }
                $stock->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Damaged stock record deleted successfully');
    }
}
