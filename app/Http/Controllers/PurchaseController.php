<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Warehouse;
use App\Services\InventoryPostingService;
use App\Services\PurchaseHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class PurchaseController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting,
        protected PurchaseHistoryService $history,
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
            'warehouses' => Warehouse::select('id', 'name')->orderBy('name')->get(),
            'products' => Product::select('id', 'name', 'purchase_price')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge($this->headerRules(), $this->itemRules()));

        $purchaseId = null;

        try {
            DB::transaction(function () use ($request, $validated, &$purchaseId) {
                $purchase = Purchase::create(array_merge(
                    $this->pickHeaderFields($validated),
                    $this->computePaymentFields($validated, 0),
                    [
                        'user_id' => Auth::id(),
                        'status' => $request->boolean('status', true),
                    ]
                ));

                $this->syncPurchaseItems($purchase, $request->items);
                $purchaseId = $purchase->id;
            });

            $purchase = Purchase::with('returns')->findOrFail($purchaseId);
            $this->history->recordPurchaseCreated($purchase);
            $this->history->syncPurchaseDue($purchase);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchases.index')->with('success', 'Purchase created successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/Purchases/Show', [
            'purchase' => Purchase::with(['warehouse', 'user', 'details.product', 'returns'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/Purchases/Edit', [
            'purchase' => Purchase::with('details.product')->findOrFail($id),
            'warehouses' => Warehouse::select('id', 'name')->orderBy('name')->get(),
            'products' => Product::select('id', 'name', 'purchase_price')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $purchase = Purchase::with('details')->findOrFail($id);

        $validated = $request->validate(array_merge(
            $this->headerRules($purchase->id),
            $this->itemRules()
        ));

        $mustResync = $purchase->purchase_status !== $validated['purchase_status']
            || ($validated['purchase_status'] === 'received' && (
                $purchase->warehouse_id != $validated['warehouse_id']
                || $purchase->purchase_date->format('Y-m-d') !== $validated['purchase_date']
                || $purchase->purchase_no !== $validated['purchase_no']
            ));

        $before = Purchase::with('returns')->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $purchase, $validated, $mustResync, $before) {
                $purchase->update(array_merge(
                    $this->pickHeaderFields($validated),
                    ['status' => $request->boolean('status', true)]
                ));

                $this->syncPurchaseItems($purchase, $request->items);

                if ($mustResync) {
                    $this->posting->syncPurchaseStock($purchase->fresh());
                }
            });

            $updated = Purchase::with('returns')->findOrFail($id);
            $this->history->recordPurchaseUpdated($updated, $before);
            $this->history->syncPurchaseDue($updated);
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

    protected function headerRules(?int $ignoreId = null): array
    {
        $uniqueRule = 'required|string|unique:purchases,purchase_no';
        if ($ignoreId) {
            $uniqueRule .= ',' . $ignoreId;
        }

        return [
            'purchase_no' => $uniqueRule,
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
        ];
    }

    protected function itemRules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.free_quantity' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.remarks' => 'nullable|string',
        ];
    }

    protected function pickHeaderFields(array $data): array
    {
        return collect($data)->only([
            'purchase_no',
            'supplier_invoice_no',
            'supplier_name',
            'purchase_date',
            'warehouse_id',
            'discount',
            'tax',
            'shipping_cost',
            'other_charges',
            'paid_amount',
            'purchase_status',
            'remarks',
        ])->toArray();
    }

    protected function syncPurchaseItems(Purchase $purchase, array $items): void
    {
        foreach ($purchase->details as $detail) {
            $this->posting->reversePurchaseDetail($detail);
        }
        $purchase->details()->delete();

        foreach ($items as $row) {
            $lineTotal = $this->computeLineTotal($row);
            $detail = $purchase->details()->create([
                'user_id' => Auth::id(),
                'product_id' => $row['product_id'],
                'quantity' => $row['quantity'],
                'free_quantity' => $row['free_quantity'] ?? 0,
                'unit_price' => $row['unit_price'],
                'discount' => $row['discount'] ?? 0,
                'tax' => $row['tax'] ?? 0,
                'line_total' => $lineTotal,
                'remarks' => $row['remarks'] ?? null,
                'status' => true,
            ]);

            $this->posting->postPurchaseDetail($detail);
        }
    }

    protected function computeLineTotal(array $row): float
    {
        return round(
            ((float) $row['quantity'] * (float) $row['unit_price'])
            - (float) ($row['discount'] ?? 0)
            + (float) ($row['tax'] ?? 0),
            2
        );
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
