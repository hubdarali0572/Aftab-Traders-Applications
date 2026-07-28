<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\CustomerReportService;
use App\Services\Reports\ReportExportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerReportController extends Controller
{
    use BuildsReportResponses;

    public function __construct(
        protected CustomerReportService $customers,
        protected ReportExportService $export
    ) {
    }

    public function ledger(Request $request)
    {
        $result = $this->customers->ledger($request);
        $rows = $result['query']->paginate(50)->withQueryString();
        $rows->getCollection()->transform(fn ($row) => $this->customers->mapLedgerRow($row, $result['typeLabels']));

        if ($csv = $this->maybeCsv($request, $this->export, 'customer-ledger-report', [
            'Date', 'Customer', 'Voucher Type', 'Voucher No', 'Debit', 'Credit', 'Balance', 'Remarks',
        ], collect($rows->items())->map(fn ($r) => [
            $r['date'], $r['customer'], $r['voucher_type'], $r['voucher_number'], $r['debit'], $r['credit'], $r['balance'], $r['remarks'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Customers/Ledger', [
            'rows' => $rows,
            'summary' => [
                'total_debit' => (float) collect($rows->items())->sum('debit'),
                'total_credit' => (float) collect($rows->items())->sum('credit'),
                'entries' => $rows->total(),
            ],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'transactionTypes' => $result['typeLabels'],
            'printMode' => $request->boolean('print'),
        ]);
    }

    public function outstanding(Request $request)
    {
        $result = $this->customers->outstanding($request);
        $paginated = $this->paginateCollection(collect($result['rows']), $request);

        if ($csv = $this->maybeCsv($request, $this->export, 'outstanding-balance-report', [
            'Customer', 'Code', 'Total Sales', 'Total Paid', 'Outstanding', 'Pending Invoices', 'Oldest Due Date', 'Days',
        ], collect($result['rows'])->map(fn ($r) => [
            $r['customer'], $r['customer_code'], $r['total_sales'], $r['total_paid'], $r['outstanding_balance'],
            $r['pending_invoices'], $r['oldest_due_date'], $r['oldest_due_days'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Customers/Outstanding', [
            'rows' => $paginated,
            'summary' => $result['summary'],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }

    public function paymentHistory(Request $request)
    {
        $result = $this->customers->paymentHistory($request);
        $paginated = $this->paginateCollection(collect($result['rows']), $request);

        if ($csv = $this->maybeCsv($request, $this->export, 'payment-history-report', [
            'Payment Date', 'Customer', 'Invoice', 'Payment Method', 'Reference', 'Amount', 'Received By',
        ], collect($result['rows'])->map(fn ($r) => [
            $r['payment_date'], $r['customer'], $r['invoice'], $r['payment_method'], $r['reference_number'], $r['amount'], $r['received_by'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Customers/PaymentHistory', [
            'rows' => $paginated,
            'summary' => $result['summary'],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }

    public function salesHistory(Request $request)
    {
        $result = $this->customers->salesHistory($request);
        $paginated = $this->paginateCollection(collect($result['rows']), $request);

        if ($csv = $this->maybeCsv($request, $this->export, 'customer-sales-history-report', [
            'Invoice No', 'Date', 'Products', 'Qty', 'Amount', 'Discount', 'Net Total', 'Payment Status', 'Warehouse',
        ], collect($result['rows'])->map(fn ($r) => [
            $r['invoice_no'], $r['invoice_date'], $r['products'], $r['quantity'], $r['amount'], $r['discount'], $r['net_total'], $r['payment_status'], $r['warehouse'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Customers/SalesHistory', [
            'rows' => $paginated,
            'summary' => $result['summary'],
            'profile' => $result['profile'],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }
}
