<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Warehouse;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class PurchaseController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index(Request $request)
    {
        $purchases = Purchase::query()
            ->with(['warehouse', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('purchase_no', 'like', "%{$search}%")
                        ->orWhere('supplier_name', 'like', "%{$search}%")
                        ->orWhere('supplier_invoice_no', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/Purchases/Index', [
            'purchases' => $purchases,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/Purchases/Create', [
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_no' => 'required|string|unique:purchases,purchase_no',
            'supplier_invoice_no' => 'nullable|string',
            'supplier_name' => 'nullable|string',
            'purchase_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'purchase_status' => 'required|in:draft,received,cancelled',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $amounts = $this->computePaymentFields($validated, 0);

        Purchase::create(array_merge($validated, $amounts, ['user_id' => Auth::id()]));

        return redirect()->route('purchases.index')->with('success', 'Purchase created successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/Purchases/Show', [
            'purchase' => Purchase::with(['warehouse', 'user', 'details.product', 'returns', 'expenses'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/Purchases/Edit', [
            'purchase' => Purchase::findOrFail($id),
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $purchase = Purchase::findOrFail($id);

        $validated = $request->validate([
            'purchase_no' => 'required|string|unique:purchases,purchase_no,' . $id,
            'supplier_invoice_no' => 'nullable|string',
            'supplier_name' => 'nullable|string',
            'purchase_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'purchase_status' => 'required|in:draft,received,cancelled',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $mustResync = $purchase->purchase_status !== $validated['purchase_status']
            || ($validated['purchase_status'] === 'received' && (
                $purchase->warehouse_id != $validated['warehouse_id']
                || $purchase->purchase_date->format('Y-m-d') !== $validated['purchase_date']
                || $purchase->purchase_no !== $validated['purchase_no']
            ));
        $subtotal = (float) $purchase->details()->sum('line_total');
        $amounts = $this->computePaymentFields($validated, $subtotal);

        try {
            DB::transaction(function () use ($purchase, $validated, $amounts, $mustResync) {
                $purchase->update(array_merge($validated, $amounts));

                if ($mustResync) {
                    $this->posting->syncPurchaseStock($purchase->fresh());
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully');
    }

    public function destroy(string $id)
    {
        $purchase = Purchase::with('details')->findOrFail($id);

        try {
            DB::transaction(function () use ($purchase) {
                foreach ($purchase->details as $detail) {
                    $this->posting->reversePurchaseDetail($detail);
                    $detail->delete();
                }
                $purchase->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Purchase deleted successfully');
    }

    protected function computePaymentFields(array $data, float $subtotal): array
    {
        $grand = $subtotal
            - (float) ($data['discount'] ?? 0)
            + (float) ($data['tax'] ?? 0)
            + (float) ($data['shipping_cost'] ?? 0)
            + (float) ($data['other_charges'] ?? 0);
        $paid = (float) ($data['paid_amount'] ?? 0);
        $due = max(0, $grand - $paid);

        $paymentStatus = 'unpaid';
        if ($paid > 0 && $due > 0) {
            $paymentStatus = 'partial';
        } elseif ($due <= 0 && $grand > 0) {
            $paymentStatus = 'paid';
        }

        return [
            'subtotal' => $subtotal,
            'grand_total' => $grand,
            'due_amount' => $due,
            'payment_status' => $paymentStatus,
        ];
    }
}
