<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class StockTransferController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index(Request $request)
    {
        $transfers = StockTransfer::query()
            ->with(['fromWarehouse', 'toWarehouse', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('fromWarehouse', fn ($w) => $w->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('toWarehouse', fn ($w) => $w->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/StockTransfers/Index', [
            'transfers' => $transfers,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/StockTransfers/Create', [
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_no' => 'required|string|unique:stock_transfers,reference_no',
            'transfer_date' => 'required|date',
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'total_quantity' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'stock_status' => 'required|in:draft,in_transit,completed,cancelled',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        StockTransfer::create(array_merge($request->all(), ['user_id' => Auth::id()]));

        return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer created successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/StockTransfers/Show', [
            'transfer' => StockTransfer::with(['fromWarehouse', 'toWarehouse', 'user', 'details.product'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/StockTransfers/Edit', [
            'transfer' => StockTransfer::findOrFail($id),
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $transfer = StockTransfer::findOrFail($id);

        $request->validate([
            'reference_no' => 'required|string|unique:stock_transfers,reference_no,' . $id,
            'transfer_date' => 'required|date',
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'total_quantity' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'stock_status' => 'required|in:draft,in_transit,completed,cancelled',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $statusChanged = $transfer->stock_status !== $request->stock_status;

        try {
            DB::transaction(function () use ($request, $transfer, $statusChanged) {
                $transfer->update($request->all());

                if ($statusChanged) {
                    $this->posting->syncTransferStock($transfer->fresh());
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer updated successfully');
    }

    public function destroy(string $id)
    {
        $transfer = StockTransfer::with('details')->findOrFail($id);

        try {
            DB::transaction(function () use ($transfer) {
                foreach ($transfer->details as $detail) {
                    $this->posting->reverseTransferDetail($detail);
                }
                $transfer->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Stock transfer deleted successfully');
    }
}
