<?php

namespace App\Services\Reports;

use App\Models\DamagedStockDetail;
use App\Models\StockLedger;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportService
{
    public function __construct(protected ReportExportService $export)
    {
    }

    public function currentStock(Request $request): array
    {
        $query = WarehouseStock::query()
            ->with(['product.category', 'product.brand', 'product.unit', 'warehouse'])
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->product_id))
            ->when($request->filled('category_id'), function ($q) use ($request) {
                $q->whereHas('product', fn ($p) => $p->where('product_category_id', $request->category_id));
            })
            ->when($request->filled('brand_id'), function ($q) use ($request) {
                $q->whereHas('product', fn ($p) => $p->where('brand_id', $request->brand_id));
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });

        $stocks = $query->orderByDesc('available_quantity')->get();

        $movementAgg = StockLedger::query()
            ->selectRaw('warehouse_id, product_id')
            ->selectRaw("SUM(CASE WHEN transaction_type = 'opening_stock' THEN quantity_in ELSE 0 END) as opening_stock")
            ->selectRaw("SUM(CASE WHEN transaction_type = 'purchase' THEN quantity_in ELSE 0 END) as purchased")
            ->selectRaw("SUM(CASE WHEN transaction_type = 'sale' THEN quantity_out ELSE 0 END) as sold")
            ->selectRaw("SUM(CASE WHEN transaction_type = 'purchase_return' THEN quantity_out ELSE 0 END) as purchase_returns")
            ->selectRaw("SUM(CASE WHEN transaction_type = 'sale_return' THEN quantity_in ELSE 0 END) as sales_returns")
            ->selectRaw('SUM(quantity_in) as stock_in')
            ->selectRaw('SUM(quantity_out) as stock_out')
            ->selectRaw("SUM(CASE WHEN transaction_type = 'adjustment' THEN quantity_in - quantity_out ELSE 0 END) as adjustments")
            ->groupBy('warehouse_id', 'product_id')
            ->get()
            ->keyBy(fn ($r) => $r->warehouse_id . ':' . $r->product_id);

        $rows = $stocks->map(function ($stock) use ($movementAgg) {
            $key = $stock->warehouse_id . ':' . $stock->product_id;
            $m = $movementAgg->get($key);

            return [
                'warehouse' => $stock->warehouse?->name,
                'warehouse_id' => $stock->warehouse_id,
                'product' => $stock->product?->name,
                'product_id' => $stock->product_id,
                'sku' => $stock->product?->sku,
                'category' => $stock->product?->category?->name ?? 'Uncategorized',
                'unit' => $stock->product?->unit?->name ?? '—',
                'opening_stock' => (float) ($m->opening_stock ?? 0),
                'purchased' => (float) ($m->purchased ?? 0),
                'sold' => (float) ($m->sold ?? 0),
                'purchase_returns' => (float) ($m->purchase_returns ?? 0),
                'sales_returns' => (float) ($m->sales_returns ?? 0),
                'stock_in' => (float) ($m->stock_in ?? 0),
                'stock_out' => (float) ($m->stock_out ?? 0),
                'adjustments' => (float) ($m->adjustments ?? 0),
                'current_available' => (float) $stock->available_quantity,
                'stock_value' => (float) $stock->stock_value,
                'average_cost' => (float) $stock->average_cost,
            ];
        })->values();

        $summary = [
            'total_products' => $rows->pluck('product_id')->unique()->count(),
            'total_quantity' => (float) $rows->sum('current_available'),
            'total_stock_value' => (float) $rows->sum('stock_value'),
        ];

        return ['rows' => $rows, 'summary' => $summary];
    }

    public function lowStock(Request $request): array
    {
        $query = WarehouseStock::query()
            ->with(['product', 'warehouse'])
            ->whereColumn('available_quantity', '<=', 'minimum_stock')
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            })
            ->orderBy('available_quantity');

        $rows = $query->get()->map(function ($stock) {
            $current = (float) $stock->available_quantity;
            $min = (float) $stock->minimum_stock;
            $diff = $min - $current;

            return [
                'product' => $stock->product?->name,
                'sku' => $stock->product?->sku,
                'warehouse' => $stock->warehouse?->name,
                'current_quantity' => $current,
                'minimum_quantity' => $min,
                'difference' => $diff,
                'reorder_status' => $current <= 0 ? 'Out of Stock' : ($diff > 0 ? 'Reorder Needed' : 'At Minimum'),
            ];
        })->values();

        $summary = [
            'low_stock_items' => $rows->count(),
            'out_of_stock' => $rows->where('current_quantity', '<=', 0)->count(),
            'total_shortage' => (float) $rows->sum(fn ($r) => max($r['difference'], 0)),
        ];

        return ['rows' => $rows, 'summary' => $summary];
    }

    public function stockMovement(Request $request)
    {
        $query = StockLedger::query()
            ->with(['product', 'warehouse', 'user']);

        $this->export->applyDateRange($query, $request, 'transaction_date');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        $query->orderBy('transaction_date')->orderBy('id');

        $typeLabels = [
            'opening_stock' => 'Opening Stock',
            'purchase' => 'Purchase',
            'purchase_return' => 'Purchase Return',
            'sale' => 'Sale',
            'sale_return' => 'Sales Return',
            'transfer_in' => 'Stock Transfer In',
            'transfer_out' => 'Stock Transfer Out',
            'adjustment' => 'Stock Adjustment',
            'damage' => 'Damaged Stock',
        ];

        return compact('query', 'typeLabels');
    }

    public function mapMovementRow(StockLedger $row, array $typeLabels): array
    {
        return [
            'date' => $row->transaction_date?->format('Y-m-d'),
            'product' => $row->product?->name,
            'sku' => $row->product?->sku,
            'warehouse' => $row->warehouse?->name,
            'transaction_type' => $typeLabels[$row->transaction_type] ?? $row->transaction_type,
            'reference_no' => $row->reference_no,
            'stock_in' => (float) $row->quantity_in,
            'stock_out' => (float) $row->quantity_out,
            'running_balance' => (float) $row->balance_quantity,
            'user' => $row->user?->name,
        ];
    }

    public function damagedStock(Request $request): array
    {
        $query = DamagedStockDetail::query()
            ->with(['product', 'damagedStock.warehouse', 'damagedStock.user', 'user'])
            ->whereHas('damagedStock');

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->whereHas('damagedStock', function ($q) use ($request) {
                if ($request->filled('date_from')) {
                    $q->whereDate('damage_date', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $q->whereDate('damage_date', '<=', $request->date_to);
                }
            });
        }
        if ($request->filled('warehouse_id')) {
            $query->whereHas('damagedStock', fn ($q) => $q->where('warehouse_id', $request->warehouse_id));
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('damage_reason', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('damagedStock', fn ($d) => $d->where('reference_no', 'like', "%{$search}%"));
            });
        }

        $rows = $query->latest('id')->get()->map(function ($detail) {
            return [
                'product' => $detail->product?->name,
                'sku' => $detail->product?->sku,
                'warehouse' => $detail->damagedStock?->warehouse?->name,
                'damage_date' => $detail->damagedStock?->damage_date?->format('Y-m-d'),
                'reference_no' => $detail->damagedStock?->reference_no,
                'quantity' => (float) $detail->quantity,
                'unit_cost' => (float) $detail->unit_cost,
                'total_cost' => (float) $detail->total_cost,
                'reason' => $detail->damage_reason,
                'recorded_by' => $detail->damagedStock?->user?->name ?? $detail->user?->name,
            ];
        })->values();

        $summary = [
            'total_damaged_quantity' => (float) $rows->sum('quantity'),
            'total_damage_value' => (float) $rows->sum('total_cost'),
            'records' => $rows->count(),
        ];

        return ['rows' => $rows, 'summary' => $summary];
    }
}
