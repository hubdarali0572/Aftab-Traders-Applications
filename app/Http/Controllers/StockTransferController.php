<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\InventoryPostingService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use InvalidArgumentException;

class StockTransferController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting,
        protected StockService $stockService
    ) {
    }

    public function index(Request $request)
    {
        $transfers = StockTransfer::query()
            ->with(['fromWarehouse', 'toWarehouse', 'product', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('fromWarehouse', fn ($w) => $w->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('toWarehouse', fn ($w) => $w->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->latest('id')
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
            'warehouses' => Warehouse::select('id', 'name')->orderBy('name')->get(),
            'products' => Product::select('id', 'name')->orderBy('name')->get(),
            'stocks' => Stock::select('warehouse_id', 'product_id', 'quantity')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference_no' => 'required|string|unique:stock_transfers,reference_no',
            'transfer_date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|numeric|gt:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $this->assertSufficientStock(
            (int) $validated['from_warehouse_id'],
            (int) $validated['product_id'],
            (float) $validated['quantity']
        );

        try {
            DB::transaction(function () use ($validated, $request) {
                $transfer = StockTransfer::create([
                    'user_id' => Auth::id(),
                    'reference_no' => $validated['reference_no'],
                    'transfer_date' => $validated['transfer_date'],
                    'product_id' => $validated['product_id'],
                    'from_warehouse_id' => $validated['from_warehouse_id'],
                    'to_warehouse_id' => $validated['to_warehouse_id'],
                    'quantity' => $validated['quantity'],
                    'unit_cost' => $this->resolveUnitCost(
                        (int) $validated['from_warehouse_id'],
                        (int) $validated['product_id']
                    ),
                    'remarks' => $validated['remarks'] ?? null,
                    'status' => $request->boolean('status', true),
                ]);

                if ($transfer->status) {
                    $this->posting->postTransfer($transfer);
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('stock-transfers.index')->with('success', 'Stock transferred successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/StockTransfers/Show', [
            'transfer' => StockTransfer::with(['fromWarehouse', 'toWarehouse', 'product', 'user'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        $transfer = StockTransfer::findOrFail($id);

        return Inertia::render('InventoryManagement/StockTransfers/Edit', [
            'transfer' => $transfer,
            'warehouses' => Warehouse::select('id', 'name')->orderBy('name')->get(),
            'products' => Product::select('id', 'name')->orderBy('name')->get(),
            'stocks' => Stock::select('warehouse_id', 'product_id', 'quantity')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $transfer = StockTransfer::findOrFail($id);

        $validated = $request->validate([
            'reference_no' => ['required', 'string', Rule::unique('stock_transfers', 'reference_no')->ignore($transfer->id)],
            'transfer_date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|numeric|gt:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $wasPosted = (bool) $transfer->status;
        $restorableQty = $wasPosted
            && (int) $transfer->from_warehouse_id === (int) $validated['from_warehouse_id']
            && (int) $transfer->product_id === (int) $validated['product_id']
            ? (float) $transfer->quantity
            : 0;

        $this->assertSufficientStock(
            (int) $validated['from_warehouse_id'],
            (int) $validated['product_id'],
            (float) $validated['quantity'],
            $restorableQty
        );

        try {
            DB::transaction(function () use ($validated, $request, $transfer, $wasPosted) {
                if ($wasPosted) {
                    $this->posting->reverseTransfer($transfer);
                }

                $transfer->update([
                    'reference_no' => $validated['reference_no'],
                    'transfer_date' => $validated['transfer_date'],
                    'product_id' => $validated['product_id'],
                    'from_warehouse_id' => $validated['from_warehouse_id'],
                    'to_warehouse_id' => $validated['to_warehouse_id'],
                    'quantity' => $validated['quantity'],
                    'unit_cost' => $this->resolveUnitCost(
                        (int) $validated['from_warehouse_id'],
                        (int) $validated['product_id']
                    ),
                    'remarks' => $validated['remarks'] ?? null,
                    'status' => $request->boolean('status', true),
                ]);

                if ($transfer->status) {
                    $this->posting->postTransfer($transfer->fresh());
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer updated successfully');
    }

    public function destroy(string $id)
    {
        $transfer = StockTransfer::findOrFail($id);

        try {
            DB::transaction(function () use ($transfer) {
                if ($transfer->status) {
                    $this->posting->reverseTransfer($transfer);
                }
                $transfer->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Stock transfer deleted successfully');
    }

    protected function assertSufficientStock(
        int $warehouseId,
        int $productId,
        float $quantity,
        float $restorableQty = 0
    ): void {
        $available = $this->stockService->getAvailableQuantity($warehouseId, $productId) + $restorableQty;

        if ($quantity > $available + 0.0001) {
            throw new InvalidArgumentException(
                "Insufficient stock in the source warehouse. Available: {$available}, requested: {$quantity}."
            );
        }
    }

    protected function resolveUnitCost(int $warehouseId, int $productId): float
    {
        return (float) (Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->value('average_cost') ?? 0);
    }
}
