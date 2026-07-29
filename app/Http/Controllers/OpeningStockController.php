<?php

namespace App\Http\Controllers;

use App\Models\OpeningStock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class OpeningStockController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index(Request $request)
    {
        $stocks = OpeningStock::query()
            ->with(['warehouse', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where('reference_no', 'like', "%{$search}%")
                      ->orWhereHas('warehouse', fn ($w) => $w->where('name', 'like', "%{$search}%"));
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
            'products' => Product::select('id', 'name', 'purchase_price')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_no' => 'required|string|unique:opening_stocks,reference_no',
            'opening_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.remarks' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $header = OpeningStock::create([
                    'user_id' => Auth::id(),
                    'warehouse_id' => $request->warehouse_id,
                    'reference_no' => $request->reference_no,
                    'opening_date' => $request->opening_date,
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
                        'remarks' => $row['remarks'] ?? null,
                    ]);
                    $this->posting->postOpeningItem($item);
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('opening-stocks.index')->with('success', 'Opening stock created successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/OpeningStocks/Show', [
            'stock' => OpeningStock::with(['warehouse', 'user', 'items.product'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/OpeningStocks/Edit', [
            'stock' => OpeningStock::with('items.product')->findOrFail($id),
            'warehouses' => Warehouse::select('id', 'name')->get(),
            'products' => Product::select('id', 'name', 'purchase_price')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $stock = OpeningStock::with('items')->findOrFail($id);

        $request->validate([
            'reference_no' => 'required|string|unique:opening_stocks,reference_no,' . $id,
            'opening_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.remarks' => 'nullable|string',
        ]);

        $mustResync = $stock->warehouse_id != $request->warehouse_id
            || $stock->opening_date->format('Y-m-d') !== $request->opening_date
            || $stock->reference_no !== $request->reference_no;

        try {
            DB::transaction(function () use ($stock, $request, $mustResync) {
                foreach ($stock->items as $item) {
                    $this->posting->reverseOpeningItem($item);
                }

                $stock->update([
                    'reference_no' => $request->reference_no,
                    'opening_date' => $request->opening_date,
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
                        'remarks' => $row['remarks'] ?? null,
                    ]);
                    $this->posting->postOpeningItem($item);
                }

                if ($mustResync) {
                    $this->posting->syncOpeningStock($stock->fresh());
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('opening-stocks.index')->with('success', 'Opening stock updated successfully');
    }

    public function destroy(string $id)
    {
        $stock = OpeningStock::with('items')->findOrFail($id);

        try {
            DB::transaction(function () use ($stock) {
                foreach ($stock->items as $item) {
                    $this->posting->reverseOpeningItem($item);
                    $item->delete();
                }

                $stock->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Opening stock deleted successfully');
    }
}
