<?php

namespace App\Services\Reports;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Sale;
use Illuminate\Http\Request;

class CustomerReportService
{
    public function __construct(protected ReportExportService $export)
    {
    }

    public function ledger(Request $request)
    {
        $query = CustomerLedger::query()
            ->with(['customer', 'user'])
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('transaction_type'), fn ($q) => $q->where('transaction_type', $request->transaction_type))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($inner) use ($search) {
                    $inner->where('reference_no', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('customer_name', 'like', "%{$search}%"));
                });
            });

        $this->export->applyDateRange($query, $request, 'transaction_date');
        $query->orderBy('transaction_date')->orderBy('id');

        $typeLabels = [
            'opening_balance' => 'Opening Balance',
            'sale' => 'Sales',
            'sale_return' => 'Sales Returns',
            'payment_received' => 'Payments',
            'debit_note' => 'Debit Note',
            'credit_note' => 'Credit Note',
            'adjustment' => 'Adjustments',
        ];

        return compact('query', 'typeLabels');
    }

    public function mapLedgerRow(CustomerLedger $row, array $typeLabels): array
    {
        return [
            'date' => $row->transaction_date?->format('Y-m-d'),
            'customer' => $row->customer?->customer_name,
            'voucher_type' => $typeLabels[$row->transaction_type] ?? $row->transaction_type,
            'voucher_number' => $row->reference_no,
            'debit' => (float) $row->debit,
            'credit' => (float) $row->credit,
            'balance' => (float) $row->balance,
            'remarks' => $row->remarks,
        ];
    }

    public function outstanding(Request $request): array
    {
        $customers = Customer::query()
            ->withSum('ledgers as total_debit', 'debit')
            ->withSum('ledgers as total_credit', 'credit')
            ->when($request->filled('customer_id'), fn ($q) => $q->where('id', $request->customer_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($inner) use ($search) {
                    $inner->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('customer_code', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(function ($c) {
                $outstanding = (float) $c->total_debit - (float) $c->total_credit;
                $pendingSales = Sale::query()
                    ->where('customer_id', $c->id)
                    ->where('sale_status', '!=', 'cancelled')
                    ->where('due_amount', '>', 0);

                $oldest = (clone $pendingSales)->orderBy('sale_date')->value('sale_date');
                $totalSales = (float) Sale::where('customer_id', $c->id)->where('sale_status', '!=', 'cancelled')->sum('grand_total');
                $totalPaid = (float) Sale::where('customer_id', $c->id)->where('sale_status', '!=', 'cancelled')->sum('paid_amount');

                return [
                    'customer_id' => $c->id,
                    'customer' => $c->customer_name ?: $c->company_name,
                    'customer_code' => $c->customer_code,
                    'total_sales' => $totalSales,
                    'total_paid' => $totalPaid,
                    'outstanding_balance' => $outstanding,
                    'pending_invoices' => (clone $pendingSales)->count(),
                    'oldest_due_date' => $oldest?->format('Y-m-d'),
                    'oldest_due_days' => $oldest ? now()->diffInDays($oldest) : 0,
                ];
            })
            ->filter(fn ($r) => $r['outstanding_balance'] > 0.009)
            ->values();

        if ($request->filled('aging')) {
            $aging = $request->aging;
            $customers = $customers->filter(function ($r) use ($aging) {
                $days = (int) $r['oldest_due_days'];

                return match ($aging) {
                    '30' => $days <= 30,
                    '60' => $days > 30 && $days <= 60,
                    '90' => $days > 60 && $days <= 90,
                    '90_plus' => $days > 90,
                    default => true,
                };
            })->values();
        }

        $customers = $customers->sortByDesc('outstanding_balance')->values();

        $summary = [
            'customers' => $customers->count(),
            'total_outstanding' => (float) $customers->sum('outstanding_balance'),
            'pending_invoices' => (int) $customers->sum('pending_invoices'),
        ];

        return ['rows' => $customers, 'summary' => $summary];
    }

    public function paymentHistory(Request $request): array
    {
        $ledgerQuery = CustomerLedger::query()
            ->with(['customer', 'user'])
            ->where('customer_ledgers.transaction_type', 'payment_received');

        $this->export->applyDateRange($ledgerQuery, $request, 'customer_ledgers.transaction_date');

        if ($request->filled('customer_id')) {
            $ledgerQuery->where('customer_ledgers.customer_id', $request->customer_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $ledgerQuery->where(function ($q) use ($search) {
                $q->where('customer_ledgers.reference_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $salePayments = Sale::query()
            ->with(['customer', 'user'])
            ->where('sales.sale_status', '!=', 'cancelled')
            ->where('sales.paid_amount', '>', 0);

        $this->export->applyDateRange($salePayments, $request, 'sales.sale_date');
        if ($request->filled('customer_id')) {
            $salePayments->where('sales.customer_id', $request->customer_id);
        }
        if ($request->filled('payment_method')) {
            $salePayments->where('sales.payment_method', $request->payment_method);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $salePayments->where(function ($q) use ($search) {
                $q->where('sales.invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $fromLedger = $ledgerQuery->orderByDesc('customer_ledgers.transaction_date')->orderByDesc('customer_ledgers.id')->get()->map(fn ($row) => [
            'payment_date' => $row->transaction_date?->format('Y-m-d'),
            'customer' => $row->customer?->customer_name ?: $row->customer?->company_name,
            'invoice' => $row->reference_no,
            'payment_method' => '—',
            'reference_number' => $row->reference_no,
            'amount' => (float) $row->credit,
            'received_by' => $row->user?->name,
            'source' => 'ledger',
        ]);

        $fromSales = $salePayments->orderByDesc('sales.sale_date')->orderByDesc('sales.id')->get()->map(fn ($sale) => [
            'payment_date' => $sale->sale_date?->format('Y-m-d'),
            'customer' => $sale->customer?->customer_name ?: $sale->customer?->company_name,
            'invoice' => $sale->invoice_no,
            'payment_method' => $sale->payment_method,
            'reference_number' => $sale->invoice_no,
            'amount' => (float) $sale->paid_amount,
            'received_by' => $sale->user?->name,
            'source' => 'sale',
        ]);

        // Use ledger payments when available; fall back to sale paid amounts
        $rows = $fromLedger->isNotEmpty() ? $fromLedger : $fromSales;

        if ($request->filled('payment_method')) {
            $rows = $fromSales;
        }

        $rows = $rows->sortByDesc('payment_date')->values();

        $summary = [
            'total_payments' => (float) $rows->sum('amount'),
            'payment_count' => $rows->count(),
        ];

        return ['rows' => $rows, 'summary' => $summary];
    }

    public function salesHistory(Request $request): array
    {
        $customerId = $request->input('customer_id');
        $customer = $customerId ? Customer::find($customerId) : null;

        $query = Sale::query()
            ->with(['details.product', 'warehouse', 'user'])
            ->where('sale_status', '!=', 'cancelled')
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId));

        $this->export->applyDateRange($query, $request, 'sale_date');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('invoice_no', 'like', "%{$search}%");
        }

        $sales = $query->latest('sale_date')->get();

        $rows = $sales->map(function ($sale) {
            $products = $sale->details->map(fn ($d) => $d->product?->name)->filter()->implode(', ');
            $qty = (float) $sale->details->sum('quantity');

            return [
                'id' => $sale->id,
                'invoice_no' => $sale->invoice_no,
                'invoice_date' => $sale->sale_date?->format('Y-m-d'),
                'products' => $products,
                'quantity' => $qty,
                'amount' => (float) $sale->subtotal,
                'discount' => (float) $sale->discount,
                'net_total' => (float) $sale->grand_total,
                'payment_status' => $this->export->paymentStatus($sale->paid_amount, $sale->due_amount, $sale->grand_total),
                'warehouse' => $sale->warehouse?->name,
            ];
        })->values();

        $profile = null;
        if ($customer) {
            $allSales = Sale::where('customer_id', $customer->id)->where('sale_status', '!=', 'cancelled');
            $profile = [
                'customer_id' => $customer->id,
                'customer' => $customer->customer_name,
                'customer_code' => $customer->customer_code,
                'total_purchases' => (float) (clone $allSales)->sum('grand_total'),
                'total_paid' => (float) (clone $allSales)->sum('paid_amount'),
                'outstanding_balance' => (float) (clone $allSales)->sum('due_amount'),
                'last_purchase_date' => (clone $allSales)->max('sale_date'),
            ];
        }

        $summary = [
            'invoices' => $rows->count(),
            'total_amount' => (float) $rows->sum('net_total'),
            'total_quantity' => (float) $rows->sum('quantity'),
        ];

        return ['rows' => $rows, 'summary' => $summary, 'profile' => $profile, 'customer' => $customer];
    }
}
