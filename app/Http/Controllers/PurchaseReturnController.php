<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Warehouse;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index(Request $request)
    {
        $returns = PurchaseReturn::query()
            ->with(['purchase', 'warehouse', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('purchase', fn ($p) => $p->where('purchase_no', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/PurchaseReturns/Index', [
            'returns' => $returns,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/PurchaseReturns/Create', [
            'purchases' => Purchase::select('id', 'purchase_no')->get(),
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_no' => 'required|string|unique:purchase_returns,reference_no',
            'purchase_id' => 'required|exists:purchases,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'total_quantity' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        PurchaseReturn::create(array_merge($request->all(), [
            'user_id' => Auth::id(),
            'total_quantity' => $request->total_quantity ?? 0,
            'total_amount' => $request->total_amount ?? 0,
        ]));

        return redirect()->route('purchase-returns.index')->with('success', 'Purchase return created successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/PurchaseReturns/Show', [
            'purchaseReturn' => PurchaseReturn::with(['purchase', 'warehouse', 'user', 'details.product'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/PurchaseReturns/Edit', [
            'purchaseReturn' => PurchaseReturn::findOrFail($id),
            'purchases' => Purchase::select('id', 'purchase_no')->get(),
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $purchaseReturn = PurchaseReturn::findOrFail($id);

        $request->validate([
            'reference_no' => 'required|string|unique:purchase_returns,reference_no,' . $id,
            'purchase_id' => 'required|exists:purchases,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'total_quantity' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $purchaseReturn->update($request->all());

        return redirect()->route('purchase-returns.index')->with('success', 'Purchase return updated successfully');
    }

    public function destroy(string $id)
    {
        $purchaseReturn = PurchaseReturn::with('details')->findOrFail($id);

        try {
            DB::transaction(function () use ($purchaseReturn) {
                foreach ($purchaseReturn->details as $detail) {
                    $this->posting->reversePurchaseReturnDetail($detail);
                }
                $purchaseReturn->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Purchase return deleted successfully');
    }
}
