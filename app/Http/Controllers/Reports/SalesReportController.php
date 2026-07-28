<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportExportService;
use App\Services\Reports\SalesReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesReportController extends Controller
{
    use BuildsReportResponses;

    public function __construct(
        protected SalesReportService $sales,
        protected ReportExportService $export
    ) {
    }

    public function daily(Request $request)
    {
        $result = $this->sales->daily($request);
        $rows = $result['query']->paginate(25)->withQueryString();
        $rows->getCollection()->transform(fn ($sale) => $this->sales->mapDailyRow($sale));

        if ($csv = $this->maybeCsv($request, $this->export, 'daily-sales-report', [
            'Invoice No', 'Invoice Date', 'Customer', 'Sales Person', 'Warehouse', 'Total Qty', 'Gross', 'Discount', 'Tax', 'Net', 'Paid', 'Due', 'Payment Status',
        ], collect($rows->items())->map(fn ($r) => [
            $r['invoice_no'], $r['invoice_date'], $r['customer'], $r['sales_person'], $r['warehouse'], $r['total_quantity'],
            $r['gross_amount'], $r['discount'], $r['tax'], $r['net_amount'], $r['paid_amount'], $r['due_amount'], $r['payment_status'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Sales/Daily', [
            'rows' => $rows,
            'summary' => $result['summary'],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }

    public function monthly(Request $request)
    {
        $result = $this->sales->monthly($request);

        if ($csv = $this->maybeCsv($request, $this->export, 'monthly-sales-report', [
            'Month', 'Invoices', 'Customers', 'Quantity', 'Gross Sales', 'Discounts', 'Taxes', 'Net Sales', 'Payments', 'Outstanding',
        ], collect($result['rows'])->map(fn ($r) => [
            $r['label'], $r['invoice_count'], $r['customer_count'], $r['total_quantity'], $r['gross_sales'],
            $r['discounts'], $r['taxes'], $r['net_sales'], $r['payments_received'], $r['outstanding'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Sales/Monthly', [
            'rows' => $result['rows'],
            'summary' => $result['summary'],
            'chart' => $result['chart'],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }

    public function customerWise(Request $request)
    {
        $result = $this->sales->customerWise($request);
        $paginated = $this->paginateCollection(collect($result['rows']), $request);

        if ($csv = $this->maybeCsv($request, $this->export, 'customer-wise-sales-report', [
            'Customer', 'Code', 'Invoices', 'Products', 'Quantity', 'Total Sales', 'Paid', 'Remaining', 'Last Purchase',
        ], collect($result['rows'])->map(fn ($r) => [
            $r['customer'], $r['customer_code'], $r['invoice_count'], $r['total_products'], $r['total_quantity'],
            $r['total_sales'], $r['paid_amount'], $r['remaining_balance'], $r['last_purchase_date'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Sales/CustomerWise', [
            'rows' => $paginated,
            'summary' => $result['summary'],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }

    public function productWise(Request $request)
    {
        $result = $this->sales->productWise($request);
        $paginated = $this->paginateCollection(collect($result['rows']), $request);

        if ($csv = $this->maybeCsv($request, $this->export, 'product-wise-sales-report', [
            'Product', 'SKU', 'Category', 'Brand', 'Qty Sold', 'Sales Amount', 'Avg Price', 'Returns', 'Net Qty',
        ], collect($result['rows'])->map(fn ($r) => [
            $r['product'], $r['sku'], $r['category'], $r['brand'], $r['quantity_sold'], $r['sales_amount'],
            $r['avg_selling_price'], $r['sales_returns'], $r['net_quantity_sold'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Sales/ProductWise', [
            'rows' => $paginated,
            'summary' => $result['summary'],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }
}
