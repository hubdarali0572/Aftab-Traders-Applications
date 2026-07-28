<?php

namespace App\Http\Controllers;

use App\Models\OpeningStock;
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
        $stock = OpeningStock::withCount('details')->findOrFail($id);

        $request->validate([
            'reference_no' => 'required|string|unique:opening_stocks,reference_no,' . $id,
            'opening_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'total_quantity' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $mustResync = $stock->details_count > 0 && (
            $stock->warehouse_id != $request->warehouse_id ||
            $stock->opening_date->format('Y-m-d') !== $request->opening_date ||
            $stock->reference_no !== $request->reference_no
        );

        try {
            DB::transaction(function () use ($stock, $request, $mustResync) {
                $stock->update($request->all());

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
        $stock = OpeningStock::with('details')->findOrFail($id);

        try {
            DB::transaction(function () use ($stock) {
                foreach ($stock->details as $detail) {
                    $this->posting->reverseOpeningDetail($detail);
                    $detail->delete();
                }

                $stock->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Opening stock deleted successfully');
    }
}