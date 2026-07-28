<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\InventoryReportService;
use App\Services\Reports\ReportExportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryReportController extends Controller
{
    use BuildsReportResponses;

    public function __construct(
        protected InventoryReportService $inventory,
        protected ReportExportService $export
    ) {
    }

    public function currentStock(Request $request)
    {
        $result = $this->inventory->currentStock($request);
        $paginated = $this->paginateCollection(collect($result['rows']), $request);

        if ($csv = $this->maybeCsv($request, $this->export, 'current-stock-report', [
            'Warehouse', 'Product', 'SKU', 'Category', 'Unit', 'Opening', 'Purchased', 'Sold', 'Purchase Returns', 'Sales Returns', 'Stock In', 'Stock Out', 'Adjustments', 'Available', 'Stock Value',
        ], collect($result['rows'])->map(fn ($r) => [
            $r['warehouse'], $r['product'], $r['sku'], $r['category'], $r['unit'], $r['opening_stock'], $r['purchased'], $r['sold'],
            $r['purchase_returns'], $r['sales_returns'], $r['stock_in'], $r['stock_out'], $r['adjustments'], $r['current_available'], $r['stock_value'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Inventory/CurrentStock', [
            'rows' => $paginated,
            'summary' => $result['summary'],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }

    public function lowStock(Request $request)
    {
        $result = $this->inventory->lowStock($request);
        $paginated = $this->paginateCollection(collect($result['rows']), $request);

        if ($csv = $this->maybeCsv($request, $this->export, 'low-stock-report', [
            'Product', 'SKU', 'Warehouse', 'Current Qty', 'Minimum Qty', 'Difference', 'Reorder Status',
        ], collect($result['rows'])->map(fn ($r) => [
            $r['product'], $r['sku'], $r['warehouse'], $r['current_quantity'], $r['minimum_quantity'], $r['difference'], $r['reorder_status'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Inventory/LowStock', [
            'rows' => $paginated,
            'summary' => $result['summary'],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }

    public function stockMovement(Request $request)
    {
        $result = $this->inventory->stockMovement($request);
        $rows = $result['query']->paginate(50)->withQueryString();
        $rows->getCollection()->transform(fn ($row) => $this->inventory->mapMovementRow($row, $result['typeLabels']));

        if ($csv = $this->maybeCsv($request, $this->export, 'stock-movement-report', [
            'Date', 'Product', 'SKU', 'Warehouse', 'Type', 'Reference', 'Stock In', 'Stock Out', 'Running Balance', 'User',
        ], collect($rows->items())->map(fn ($r) => [
            $r['date'], $r['product'], $r['sku'], $r['warehouse'], $r['transaction_type'], $r['reference_no'],
            $r['stock_in'], $r['stock_out'], $r['running_balance'], $r['user'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Inventory/StockMovement', [
            'rows' => $rows,
            'summary' => [
                'movements' => $rows->total(),
                'stock_in' => (float) collect($rows->items())->sum('stock_in'),
                'stock_out' => (float) collect($rows->items())->sum('stock_out'),
            ],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'transactionTypes' => $result['typeLabels'],
            'printMode' => $request->boolean('print'),
        ]);
    }

    public function damagedStock(Request $request)
    {
        $result = $this->inventory->damagedStock($request);
        $paginated = $this->paginateCollection(collect($result['rows']), $request);

        if ($csv = $this->maybeCsv($request, $this->export, 'damaged-stock-report', [
            'Product', 'SKU', 'Warehouse', 'Damage Date', 'Reference', 'Quantity', 'Unit Cost', 'Total Cost', 'Reason', 'Recorded By',
        ], collect($result['rows'])->map(fn ($r) => [
            $r['product'], $r['sku'], $r['warehouse'], $r['damage_date'], $r['reference_no'], $r['quantity'], $r['unit_cost'], $r['total_cost'], $r['reason'], $r['recorded_by'],
        ]))) {
            return $csv;
        }

        return Inertia::render('ReportManagement/Inventory/DamagedStock', [
            'rows' => $paginated,
            'summary' => $result['summary'],
            'filters' => $this->export->filters($request),
            'options' => $this->filterOptions(),
            'printMode' => $request->boolean('print'),
        ]);
    }
}
