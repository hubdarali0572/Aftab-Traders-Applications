<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    public const UI_CUSTOMER_TYPES = ['retail', 'wholesale'];

    /** Manual entry types allowed in Customer Ledger form (system types excluded). */
    public const MANUAL_TRANSACTION_TYPES = [
        'payment_received',
        'debit_note',
        'credit_note',
        'adjustment',
    ];

    public function __construct(
        protected CustomerLedgerService $ledgerService
    ) {
    }

    public function generateCustomerCode(): string
    {
        $next = (int) Customer::withTrashed()->max('id') + 1;

        return 'CUS-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    public function allowedTypesFor(?Customer $customer): array
    {
        $types = self::UI_CUSTOMER_TYPES;

        if ($customer && ! in_array($customer->customer_type, $types, true)) {
            $types[] = $customer->customer_type;
        }

        return $types;
    }

    public function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($inner) use ($search) {
                    $inner->where('customer_code', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(! empty($filters['customer_type']), fn ($q) => $q->where('customer_type', $filters['customer_type']))
            ->when(isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null, function ($q) use ($filters) {
                $q->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
            })
            ->when(! empty($filters['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']));
    }

    public function dashboardSummary(array $filters = []): array
    {
        $baseQuery = Customer::query();
        $this->applyFilters($baseQuery, $filters);
        $customerIds = (clone $baseQuery)->pluck('id');

        $latestLedgerIds = CustomerLedger::query()
            ->whereIn('customer_id', $customerIds)
            ->selectRaw('MAX(id) as id')
            ->groupBy('customer_id')
            ->pluck('id');

        $totalOutstanding = (float) CustomerLedger::query()
            ->whereIn('id', $latestLedgerIds)
            ->get()
            ->sum(fn (CustomerLedger $entry) => max(0, (float) $entry->balance));

        $ledgerQuery = CustomerLedger::query()->whereIn('customer_id', $customerIds);
        if (! empty($filters['date_from'])) {
            $ledgerQuery->whereDate('transaction_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $ledgerQuery->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        $totalSales = (float) (clone $ledgerQuery)->where('transaction_type', 'sale')->sum('debit');
        $totalPayments = (float) (clone $ledgerQuery)->where('transaction_type', 'payment_received')->sum('credit');

        return [
            'total_customers' => $customerIds->count(),
            'active_customers' => Customer::whereIn('id', $customerIds)->where('status', true)->count(),
            'retail_customers' => Customer::whereIn('id', $customerIds)->where('customer_type', 'retail')->count(),
            'wholesale_customers' => Customer::whereIn('id', $customerIds)->where('customer_type', 'wholesale')->count(),
            'total_outstanding' => round($totalOutstanding, 2),
            'total_sales' => round($totalSales, 2),
            'total_payments_received' => round($totalPayments, 2),
        ];
    }

    public function profileSnapshot(Customer $customer): array
    {
        $outstanding = $this->ledgerService->getOutstanding((int) $customer->id);

        $completedSales = $customer->sales()->where('sale_status', 'completed');

        $totalSales = (float) CustomerLedger::where('customer_id', $customer->id)
            ->where('transaction_type', 'sale')
            ->sum('debit');

        $totalPaid = (float) CustomerLedger::where('customer_id', $customer->id)
            ->where('transaction_type', 'payment_received')
            ->sum('credit');

        return [
            'outstanding' => round($outstanding, 2),
            'opening_balance' => (float) $customer->opening_balance,
            'opening_balance_type' => $customer->opening_balance_type,
            'total_sales' => round($totalSales, 2),
            'total_paid' => round($totalPaid, 2),
            'total_due' => round(max(0, $outstanding), 2),
            'last_purchase_date' => $completedSales->max('sale_date'),
            'last_payment_date' => CustomerLedger::where('customer_id', $customer->id)
                ->where('transaction_type', 'payment_received')
                ->max('transaction_date'),
            'account_status' => $customer->status ? 'active' : 'inactive',
            'total_invoices' => $completedSales->count(),
        ];
    }

    public function defaultListOrder(Builder $query): Builder
    {
        return $query->orderBy('id', 'asc');
    }

    public function assertActiveForTransaction(int $customerId): void
    {
        $customer = Customer::find($customerId);

        if ($customer && ! $customer->status) {
            throw ValidationException::withMessages([
                'customer_id' => 'This customer is inactive. Reactivate the account before recording new transactions.',
            ]);
        }
    }

    /**
     * @return array{allowed: bool, reason: string|null}
     */
    public function canDelete(Customer $customer): array
    {
        if ($customer->sales()->exists()) {
            return [
                'allowed' => false,
                'reason' => 'This customer has sales records and cannot be deleted.',
            ];
        }

        if ($customer->orders()->exists()) {
            return [
                'allowed' => false,
                'reason' => 'This customer has order records and cannot be deleted.',
            ];
        }

        if ($customer->saleReturns()->exists()) {
            return [
                'allowed' => false,
                'reason' => 'This customer has sale return records and cannot be deleted.',
            ];
        }

        if ($customer->orderReturns()->exists()) {
            return [
                'allowed' => false,
                'reason' => 'This customer has order return records and cannot be deleted.',
            ];
        }

        $hasSystemLedger = CustomerLedger::where('customer_id', $customer->id)
            ->whereIn('transaction_type', ['sale', 'sale_return'])
            ->exists();

        if ($hasSystemLedger) {
            return [
                'allowed' => false,
                'reason' => 'This customer has linked sale transactions and cannot be deleted.',
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    public function validateManualLedgerAmounts(string $transactionType, float $debit, float $credit): ?string
    {
        if ($debit <= 0 && $credit <= 0) {
            return 'Either debit or credit amount must be greater than zero.';
        }

        if ($debit > 0 && $credit > 0) {
            return 'Enter either a debit or a credit amount, not both.';
        }

        if (in_array($transactionType, ['payment_received', 'credit_note', 'sale_return'], true) && $credit <= 0) {
            return 'This transaction type requires a credit amount.';
        }

        if (in_array($transactionType, ['debit_note', 'sale', 'opening_balance'], true) && $debit <= 0) {
            return 'This transaction type requires a debit amount.';
        }

        return null;
    }
}
