<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseTransaction;
use App\Services\PurchaseHistoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseHistoryController extends Controller
{
    public function __construct(
        protected PurchaseHistoryService $history
    ) {
    }

    public function index(Request $request)
    {
        $baseQuery = Purchase::query();
        $this->applyPurchaseFilters($baseQuery, $request);

        $summaryQuery = Purchase::query();
        if (! $request->filled('purchase_status')) {
            $summaryQuery->where('purchase_status', '!=', 'cancelled');
        }
        $this->applyPurchaseFilters($summaryQuery, $request);

        $purchases = (clone $baseQuery)
            ->with(['warehouse', 'returns'])
            ->withSum('returns as returns_total_sum', 'total_amount')
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $purchases->getCollection()->transform(function (Purchase $purchase) {
            $returnsTotal = (float) ($purchase->returns_total_sum ?? 0);
            $netPayable = max(0, (float) $purchase->grand_total - $returnsTotal);
            $remaining = max(0, $netPayable - (float) $purchase->paid_amount);

            $purchase->returns_total = $returnsTotal;
            $purchase->net_payable = $netPayable;
            $purchase->remaining_amount = $remaining;

            return $purchase;
        });

        $recentTransactions = PurchaseTransaction::query()
            ->with(['purchase', 'purchaseReturn', 'user'])
            ->when($request->filled('date_from'), fn (Builder $q) => $q->whereDate('transaction_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn (Builder $q) => $q->whereDate('transaction_date', '<=', $request->date_to))
            ->whereHas('purchase', fn (Builder $q) => $this->applyPurchaseFilters($q, $request))
            ->latest('transaction_date')
            ->latest('id')
            ->limit(15)
            ->get();

        $summaryPurchases = (clone $summaryQuery)
            ->withSum('returns as returns_total_sum', 'total_amount')
            ->get();

        $summary = [
            'total_purchases' => round((float) $summaryPurchases->sum('grand_total'), 2),
            'total_paid' => round((float) $summaryPurchases->sum('paid_amount'), 2),
            'total_returns' => round((float) $summaryPurchases->sum('returns_total_sum'), 2),
            'total_remaining' => round((float) $summaryPurchases->sum('due_amount'), 2),
            'total_count' => $summaryPurchases->count(),
        ];

        return Inertia::render('InventoryManagement/PurchaseHistory/Index', [
            'purchases' => $purchases,
            'recentTransactions' => $recentTransactions,
            'summary' => $summary,
            'filters' => $request->only('search', 'payment_status', 'purchase_status', 'date_from', 'date_to'),
        ]);
    }

    public function show(string $id)
    {
        $purchase = Purchase::with([
            'warehouse',
            'user',
            'details.product',
            'returns.details.product',
            'expenses',
            'transactions' => fn ($q) => $q->orderBy('transaction_date')->orderBy('id'),
            'transactions.user',
        ])->findOrFail($id);

        $this->history->ensureHistory($purchase);

        $purchase->refresh()->load([
            'warehouse',
            'user',
            'details.product',
            'returns.details.product',
            'expenses',
            'transactions' => fn ($q) => $q->orderBy('transaction_date')->orderBy('id'),
            'transactions.user',
        ]);

        $snapshot = $this->history->purchaseSnapshot($purchase);

        return Inertia::render('InventoryManagement/PurchaseHistory/Show', [
            'purchase' => $purchase,
            'snapshot' => $snapshot,
        ]);
    }

    protected function applyPurchaseFilters(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                $search = $request->search;
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('purchase_no', 'like', "%{$search}%")
                        ->orWhere('supplier_name', 'like', "%{$search}%")
                        ->orWhere('supplier_invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('payment_status'), fn (Builder $q) => $q->where('payment_status', $request->payment_status))
            ->when($request->filled('purchase_status'), fn (Builder $q) => $q->where('purchase_status', $request->purchase_status))
            ->when($request->filled('date_from'), fn (Builder $q) => $q->whereDate('purchase_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn (Builder $q) => $q->whereDate('purchase_date', '<=', $request->date_to));
    }
}
