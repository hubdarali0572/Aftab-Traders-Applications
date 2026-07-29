<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Warehouse;
use App\Services\InventoryPostingService;
use App\Services\PurchaseHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting,
        protected PurchaseHistoryService $history,
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

    public function create(Request $request)
    {
        $selectedPurchase = null;
        if ($request->filled('purchase_id')) {
            $selectedPurchase = Purchase::select('id', 'purchase_no', 'warehouse_id')
                ->find($request->purchase_id);
        }

        return Inertia::render('InventoryManagement/PurchaseReturns/Create', [
            'purchases' => Purchase::select('id', 'purchase_no', 'warehouse_id')->orderByDesc('id')->get(),
            'warehouses' => Warehouse::select('id', 'name')->orderBy('name')->get(),
            'products' => Product::select('id', 'name', 'purchase_price')->orderBy('name')->get(),
            'selectedPurchase' => $selectedPurchase,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge($this->headerRules(), $this->itemRules()));

        $returnId = null;

        try {
            DB::transaction(function () use ($request, $validated, &$returnId) {
                $purchaseReturn = PurchaseReturn::create(array_merge(
                    $this->pickHeaderFields($validated),
                    [
                        'user_id' => Auth::id(),
                        'total_quantity' => 0,
                        'total_amount' => 0,
                        'status' => $request->boolean('status', true),
                    ]
                ));

                $this->syncReturnItems($purchaseReturn, $request->items);
                $returnId = $purchaseReturn->id;
            });

            $purchaseReturn = PurchaseReturn::with('purchase.returns')->findOrFail($returnId);
            $this->history->recordReturn($purchaseReturn);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

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
            'purchaseReturn' => PurchaseReturn::with('details.product')->findOrFail($id),
            'purchases' => Purchase::select('id', 'purchase_no')->orderByDesc('id')->get(),
            'warehouses' => Warehouse::select('id', 'name')->orderBy('name')->get(),
            'products' => Product::select('id', 'name', 'purchase_price')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $purchaseReturn = PurchaseReturn::with('details')->findOrFail($id);

        $validated = $request->validate(array_merge(
            $this->headerRules($purchaseReturn->id),
            $this->itemRules()
        ));

        $mustResync = $purchaseReturn->details->isNotEmpty() && (
            $purchaseReturn->warehouse_id != $validated['warehouse_id']
            || $purchaseReturn->purchase_id != $validated['purchase_id']
            || $purchaseReturn->return_date->format('Y-m-d') !== $validated['return_date']
            || $purchaseReturn->reference_no !== $validated['reference_no']
        );

        try {
            DB::transaction(function () use ($request, $purchaseReturn, $validated, $mustResync) {
                $this->history->reverseReturn($purchaseReturn);

                $purchaseReturn->update(array_merge(
                    $this->pickHeaderFields($validated),
                    ['status' => $request->boolean('status', true)]
                ));

                $this->syncReturnItems($purchaseReturn, $request->items);

                if ($mustResync) {
                    $this->posting->syncPurchaseReturnStock($purchaseReturn->fresh());
                }
            });

            $updated = PurchaseReturn::with('purchase.returns')->findOrFail($id);
            $this->history->recordReturn($updated);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase-returns.index')->with('success', 'Purchase return updated successfully');
    }

    public function destroy(string $id)
    {
        $purchaseReturn = PurchaseReturn::with('details')->findOrFail($id);

        try {
            DB::transaction(function () use ($purchaseReturn) {
                $this->history->reverseReturn($purchaseReturn);

                foreach ($purchaseReturn->details as $detail) {
                    $this->posting->reversePurchaseReturnDetail($detail);
                    $detail->delete();
                }
                $purchaseReturn->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Purchase return deleted successfully');
    }

    protected function headerRules(?int $ignoreId = null): array
    {
        $uniqueRule = 'required|string|unique:purchase_returns,reference_no';
        if ($ignoreId) {
            $uniqueRule .= ',' . $ignoreId;
        }

        return [
            'reference_no' => $uniqueRule,
            'purchase_id' => 'required|exists:purchases,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ];
    }

    protected function itemRules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
            'items.*.remarks' => 'nullable|string',
        ];
    }

    protected function pickHeaderFields(array $data): array
    {
        return collect($data)->only([
            'reference_no',
            'purchase_id',
            'warehouse_id',
            'return_date',
            'remarks',
        ])->toArray();
    }

    protected function syncReturnItems(PurchaseReturn $purchaseReturn, array $items): void
    {
        foreach ($purchaseReturn->details as $detail) {
            $this->posting->reversePurchaseReturnDetail($detail);
        }
        $purchaseReturn->details()->delete();

        foreach ($items as $row) {
            $totalPrice = round((float) $row['quantity'] * (float) $row['unit_price'], 2);
            $detail = $purchaseReturn->details()->create([
                'user_id' => Auth::id(),
                'product_id' => $row['product_id'],
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'total_price' => $totalPrice,
                'reason' => $row['reason'] ?? null,
                'remarks' => $row['remarks'] ?? null,
                'status' => true,
            ]);

            $this->posting->postPurchaseReturnDetail($detail);
        }
    }
}
