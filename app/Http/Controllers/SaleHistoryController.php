<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Reports\BuildsReportResponses;
use App\Services\Reports\ReportExportService;
use App\Services\SaleHistoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleHistoryController extends Controller
{
    use BuildsReportResponses;

    public function __construct(
        protected SaleHistoryService $history,
        protected ReportExportService $export
    ) {
    }

    public function index(Request $request)
    {
        $filters = $this->history->filters($request);

        if ($csv = $this->handleExport($request)) {
            return $csv;
        }

        return Inertia::render('InventoryManagement/SalesHistory/Index', [
            'summary' => $this->history->dashboardSummary($request),
            'sales' => $this->history->paginateSales($request),
            'returns' => $this->history->paginateReturns($request),
            'filters' => $filters,
            'options' => $this->history->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }

    protected function handleExport(Request $request)
    {
        $export = $request->input('export');

        if (! in_array($export, ['csv', 'excel', 'sales', 'returns', 'all'], true)) {
            return null;
        }

        if (in_array($export, ['returns'], true)) {
            return $this->export->csv('sales-return-history', [
                'Return Date & Time',
                'Return Reference No.',
                'Original Invoice',
                'Customer',
                'Warehouse',
                'Returned Product(s)',
                'Returned Quantity',
                'Return Amount',
                'Return Reason',
                'Returned By',
            ], $this->history->returnsExportRows($request));
        }

        if (in_array($export, ['sales'], true)) {
            return $this->export->csv('sales-history', [
                'Date & Time',
                'Invoice No.',
                'Customer',
                'Warehouse',
                'Product(s)',
                'Total Quantity',
                'Grand Total',
                'Paid Amount',
                'Due Amount',
                'Sold By',
                'Sale Status',
                'Payment Status',
            ], $this->history->salesExportRows($request));
        }

        $salesRows = $this->history->salesExportRows($request)->map(fn ($row) => array_merge(['Sale'], $row));
        $returnRows = $this->history->returnsExportRows($request)->map(function ($row) {
            return [
                'Sales Return',
                $row[0],
                $row[1],
                $row[2],
                $row[3],
                $row[4],
                $row[5],
                $row[6],
                $row[7],
                $row[8],
                $row[9],
                '',
            ];
        });

        return $this->export->csv('sales-and-returns-history', [
            'Transaction Type',
            'Date & Time',
            'Document No.',
            'Original Invoice',
            'Customer',
            'Warehouse',
            'Product(s)',
            'Quantity',
            'Amount',
            'Paid / Reason',
            'Extra',
            'Created By',
        ], $salesRows->concat($returnRows));
    }
}
