<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\FinancialReportService;
use App\Services\Reports\ReportExportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinancialReportController extends Controller
{
    use BuildsReportResponses;

    public function __construct(
        protected FinancialReportService $financial,
        protected ReportExportService $export
    ) {
    }

    public function expenses(Request $request)
    {
        $result = $this->financial->expenses($request);
        $paginated = $this->paginateCollection(collect($result['rows']), $request);

        if ($csv = $this->maybeCsv($request, $this->export, 'expense-report', [
            'Date', 'Expense No', 'Expense Name', 'Amount', 'Paid To', 'Payment Method', 'Remarks', 'Recorded By', 'Warehouse', 'Status',
        ], collect($result['rows'])->map(fn ($r) => [
            $r['expense_date'], $r['expense_no'], $r['expense_name'], $r['amount'], $r['paid_to'],
            $r['payment_method'], $r['remarks'], $r['recorded_by'], $r['warehouse'], $r['status'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Financial/Expenses', [
            'rows' => $paginated,
            'summary' => $result['summary'],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }

    public function profitLoss(Request $request)
    {
        $result = $this->financial->profitAndLoss($request);

        if ($csv = $this->maybeCsv($request, $this->export, 'profit-loss-summary', [
            'Metric', 'Amount',
        ], collect([
            ['Sales Revenue', $result['summary']['sales_revenue']],
            ['Sales Returns', $result['summary']['sales_returns']],
            ['Net Sales', $result['summary']['net_sales']],
            ['Opening Stock', $result['summary']['opening_stock']],
            ['Purchases', $result['summary']['purchases']],
            ['Purchase Expenses', $result['summary']['purchase_expenses']],
            ['Purchase Returns', $result['summary']['purchase_returns']],
            ['Closing Stock', $result['summary']['closing_stock']],
            ['COGS', $result['summary']['cogs']],
            ['Gross Profit', $result['summary']['gross_profit']],
            ['Operating Expenses', $result['summary']['operating_expenses']],
            ['Net Profit', $result['summary']['net_profit']],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Financial/ProfitLoss', [
            'summary' => $result['summary'],
            'expenseDistribution' => $result['expense_distribution'],
            'monthlyTrend' => $result['monthly_trend'],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }
}
