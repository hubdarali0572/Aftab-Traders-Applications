<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Reports\BuildsReportResponses;
use App\Services\ExpenseHistoryService;
use App\Services\Reports\ReportExportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseHistoryController extends Controller
{
    use BuildsReportResponses;

    public function __construct(
        protected ExpenseHistoryService $history,
        protected ReportExportService $export
    ) {
    }

    public function index(Request $request)
    {
        $filters = $this->history->filters($request);

        if ($csv = $this->handleExport($request)) {
            return $csv;
        }

        return Inertia::render('ExpenseManagement/ExpenseHistory/Index', [
            'summary' => $this->history->dashboardSummary($request),
            'expenses' => $this->history->paginateExpenses($request),
            'filters' => $filters,
            'options' => $this->history->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }

    protected function handleExport(Request $request)
    {
        $export = $request->input('export');

        if (! in_array($export, ['csv', 'excel', 'expenses'], true)) {
            return null;
        }

        return $this->export->csv('expense-history', [
            'Date',
            'Expense No.',
            'Expense Name',
            'Warehouse',
            'Payee',
            'Employee',
            'Amount',
            'Payment Method',
            'Reference No.',
            'Invoice No.',
            'Status',
            'Recorded By',
            'Description / Remarks',
        ], $this->history->exportRows($request));
    }
}
