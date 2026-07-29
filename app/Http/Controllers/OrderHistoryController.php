<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Reports\BuildsReportResponses;
use App\Services\OrderHistoryService;
use App\Services\Reports\ReportExportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderHistoryController extends Controller
{
    use BuildsReportResponses;

    public function __construct(
        protected OrderHistoryService $history,
        protected ReportExportService $export
    ) {
    }

    public function index(Request $request)
    {
        $filters = $this->history->filters($request);

        if ($csv = $this->handleExport($request)) {
            return $csv;
        }

        return Inertia::render('InventoryManagement/OrdersHistory/Index', [
            'summary' => $this->history->dashboardSummary($request),
            'orders' => $this->history->paginateOrders($request),
            'returns' => $this->history->paginateReturns($request),
            'filters' => $filters,
            'options' => $this->history->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }

    protected function handleExport(Request $request)
    {
        $export = $request->input('export');

        if (! in_array($export, ['csv', 'excel', 'orders', 'returns', 'all'], true)) {
            return null;
        }

        if (in_array($export, ['returns'], true)) {
            return $this->export->csv('order-return-history', [
                'Return Date & Time',
                'Return Reference No.',
                'Original Order No.',
                'Customer',
                'Warehouse',
                'Returned Product(s)',
                'Returned Quantity',
                'Return Amount',
                'Return Reason',
                'Processed By',
            ], $this->history->returnsExportRows($request));
        }

        if (in_array($export, ['orders'], true)) {
            return $this->export->csv('order-history', [
                'Order Date & Time',
                'Order No.',
                'Customer',
                'Warehouse',
                'Product(s)',
                'Total Quantity',
                'Grand Total',
                'Paid Amount',
                'Due Amount',
                'Processed By',
                'Order Status',
                'Payment Status',
            ], $this->history->ordersExportRows($request));
        }

        $orderRows = $this->history->ordersExportRows($request)->map(fn ($row) => array_merge(['Order'], $row));
        $returnRows = $this->history->returnsExportRows($request)->map(function ($row) {
            return [
                'Order Return',
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

        return $this->export->csv('orders-and-returns-history', [
            'Transaction Type',
            'Date & Time',
            'Document No.',
            'Original Order',
            'Customer',
            'Warehouse',
            'Product(s)',
            'Quantity',
            'Amount',
            'Paid / Reason',
            'Extra',
            'Created By',
        ], $orderRows->concat($returnRows));
    }
}
