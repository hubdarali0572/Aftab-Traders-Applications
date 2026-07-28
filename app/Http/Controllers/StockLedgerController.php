<?php

namespace App\Http\Controllers;

use App\Models\StockLedger;
use App\Models\Warehouse;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockLedgerController extends Controller
{
    public function __construct(
        protected StockService $stock
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $ledgers = StockLedger::query()
            ->with(['warehouse', 'product', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('warehouse', fn($w) => $w->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/StockLedgers/Index', [
            'ledgers' => $ledgers,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('InventoryManagement/StockLedgers/Create', [
            'warehouses' => Warehouse::select('id', 'name')->get(),
            'products' => Product::select('id', 'name')->get(),
            'transaction_types' => [
                'opening_stock',
                'purchase',
                'purchase_return',
                'sale',
                'sale_return',
                'adjustment',
                'transfer_in',
                'transfer_out',
                'damage'
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'transaction_type' => 'required|string',
            'reference_no' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'quantity_in' => 'required|numeric|min:0',
            'quantity_out' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            // Calculate total cost based on the movement
            $qty = $request->quantity_in > 0 ? $request->quantity_in : $request->quantity_out;
            $totalCost = $qty * $request->unit_cost;

            StockLedger::create([
                'user_id' => Auth::id(),
                'warehouse_id' => $request->warehouse_id,
                'product_id' => $request->product_id,
                'transaction_type' => $request->transaction_type,
                'reference_type' => 'Manual', // Default for manual entries
                'reference_id' => 0,          // Default for manual entries
                'reference_no' => $request->reference_no,
                'transaction_date' => $request->transaction_date,
                'quantity_in' => $request->quantity_in,
                'quantity_out' => $request->quantity_out,
                'unit_cost' => $request->unit_cost,
                'total_cost' => $totalCost,
                'remarks' => $request->remarks,
                'status' => $request->status ?? true,
                'balance_quantity' => 0, // Will be updated by recalculateBalances
            ]);

            $this->stock->recalculateBalances($request->product_id, $request->warehouse_id);
        });

        return redirect()->route('stock-ledgers.index')->with('success', 'Ledger entry created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ledger = StockLedger::with(['warehouse', 'product', 'user'])->findOrFail($id);
        return Inertia::render('InventoryManagement/StockLedgers/Show', [
            'ledger' => $ledger
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ledger = StockLedger::findOrFail($id);

        return Inertia::render('InventoryManagement/StockLedgers/Edit', [
            'ledger' => $ledger,
            'warehouses' => Warehouse::select('id', 'name')->get(),
            'products' => Product::select('id', 'name')->get(),
            'transaction_types' => [
                'opening_stock',
                'purchase',
                'purchase_return',
                'sale',
                'sale_return',
                'adjustment',
                'transfer_in',
                'transfer_out',
                'damage'
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ledger = StockLedger::findOrFail($id);

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'transaction_type' => 'required|string',
            'reference_no' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'quantity_in' => 'required|numeric|min:0',
            'quantity_out' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $ledger) {
            $qty = $request->quantity_in > 0 ? $request->quantity_in : $request->quantity_out;
            $totalCost = $qty * $request->unit_cost;

            $ledger->update([
                'warehouse_id' => $request->warehouse_id,
                'product_id' => $request->product_id,
                'transaction_type' => $request->transaction_type,
                'reference_no' => $request->reference_no,
                'transaction_date' => $request->transaction_date,
                'quantity_in' => $request->quantity_in,
                'quantity_out' => $request->quantity_out,
                'unit_cost' => $request->unit_cost,
                'total_cost' => $totalCost,
                'remarks' => $request->remarks,
                'status' => $request->status,
            ]);

            // If product or warehouse changed, recalculate both old and new
            $this->stock->recalculateBalances($request->product_id, $request->warehouse_id);
        });

        return redirect()->route('stock-ledgers.index')->with('success', 'Ledger entry updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ledger = StockLedger::findOrFail($id);
        $p_id = $ledger->product_id;
        $w_id = $ledger->warehouse_id;

        $ledger->delete();

        // Recalculate balances after deletion to fix the running totals
        $this->stock->recalculateBalances($p_id, $w_id);

        return redirect()->back()->with('success', 'Ledger entry deleted successfully');
    }
}
