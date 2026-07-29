<?php

namespace App\Services;

use App\Models\DamagedStockItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StockHistoryService
{
    public function transferHistory(Request $request): LengthAwarePaginator
    {
        $query = StockTransfer::query()
            ->with(['product', 'fromWarehouse', 'toWarehouse', 'user'])
            ->when($request->filled('date_from'), fn (Builder $q) => $q->whereDate('transfer_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn (Builder $q) => $q->whereDate('transfer_date', '<=', $request->date_to))
            ->when($request->filled('product_id'), fn (Builder $q) => $q->where('product_id', $request->product_id))
            ->when($request->filled('warehouse_id'), function (Builder $q) use ($request) {
                $q->where(function (Builder $inner) use ($request) {
                    $inner->where('from_warehouse_id', $request->warehouse_id)
                        ->orWhere('to_warehouse_id', $request->warehouse_id);
                });
            })
            ->where('status', true)
            ->orderByDesc('transfer_date')
            ->orderByDesc('id');

        $paginator = $query->paginate(10, ['*'], 'transfer_page')->withQueryString();

        $transferIds = $paginator->getCollection()->pluck('id');
        $movements = StockMovement::query()
            ->where('reference_type', 'stock-transfer')
            ->whereIn('reference_id', $transferIds)
            ->get()
            ->groupBy(fn ($m) => $m->reference_id . ':' . $m->transaction_type);

        $paginator->getCollection()->transform(function (StockTransfer $transfer) use ($movements) {
            $outKey = $transfer->id . ':transfer_out';
            $inKey = $transfer->id . ':transfer_in';
            $outMovement = $movements->get($outKey)?->first();
            $inMovement = $movements->get($inKey)?->first();

            $qty = (float) $transfer->quantity;
            $unitCost = (float) $transfer->unit_cost;
            $outBalance = $outMovement ? (float) $outMovement->balance_quantity : null;
            $inBalance = $inMovement ? (float) $inMovement->balance_quantity : null;

            return [
                'id' => $transfer->id,
                'transfer_datetime' => $transfer->transfer_date?->format('Y-m-d') . ' ' . $transfer->created_at?->format('H:i:s'),
                'reference_no' => $transfer->reference_no,
                'product_name' => $transfer->product?->name,
                'sku' => $transfer->product?->sku,
                'from_warehouse' => $transfer->fromWarehouse?->name,
                'to_warehouse' => $transfer->toWarehouse?->name,
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'total_value' => round($qty * $unitCost, 2),
                'from_balance_before' => $outMovement ? round($outBalance + (float) $outMovement->quantity_out, 2) : null,
                'from_balance_after' => $outBalance,
                'to_balance_before' => $inMovement ? round($inBalance - (float) $inMovement->quantity_in, 2) : null,
                'to_balance_after' => $inBalance,
                'created_by' => $transfer->user?->name,
                'remarks' => $transfer->remarks,
            ];
        });

        return $paginator;
    }

    public function damagedHistory(Request $request): LengthAwarePaginator
    {
        $query = DamagedStockItem::query()
            ->when($request->filled('product_id'), fn (Builder $q) => $q->where('damaged_stock_items.product_id', $request->product_id))
            ->join('damaged_stocks', 'damaged_stock_items.damaged_stock_id', '=', 'damaged_stocks.id')
            ->whereNull('damaged_stocks.deleted_at')
            ->whereNull('damaged_stock_items.deleted_at')
            ->where('damaged_stocks.status', true)
            ->when($request->filled('date_from'), fn (Builder $q) => $q->whereDate('damaged_stocks.damage_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn (Builder $q) => $q->whereDate('damaged_stocks.damage_date', '<=', $request->date_to))
            ->when($request->filled('warehouse_id'), fn (Builder $q) => $q->where('damaged_stocks.warehouse_id', $request->warehouse_id))
            ->select('damaged_stock_items.*')
            ->orderByDesc('damaged_stocks.damage_date')
            ->orderByDesc('damaged_stock_items.id');

        $paginator = $query->paginate(10, ['damaged_stock_items.*'], 'damaged_page')->withQueryString();
        $paginator->load(['product', 'damagedStock.warehouse', 'damagedStock.user']);

        $itemIds = $paginator->getCollection()->pluck('id');
        $movements = StockMovement::query()
            ->where('reference_type', 'damaged-stock-item')
            ->whereIn('reference_id', $itemIds)
            ->get()
            ->keyBy('reference_id');

        $paginator->getCollection()->transform(function (DamagedStockItem $item) use ($movements) {
            $header = $item->damagedStock;
            $movement = $movements->get($item->id);
            $qty = (float) $item->quantity;
            $unitCost = (float) $item->unit_cost;
            $balanceAfter = $movement ? (float) $movement->balance_quantity : null;

            return [
                'id' => $item->id,
                'damage_datetime' => $header?->damage_date?->format('Y-m-d') . ' ' . ($header?->created_at?->format('H:i:s') ?? ''),
                'reference_no' => $header?->reference_no,
                'product_name' => $item->product?->name,
                'sku' => $item->product?->sku,
                'warehouse' => $header?->warehouse?->name,
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'total_loss' => (float) $item->total_cost,
                'damage_reason' => $item->damage_reason,
                'balance_before' => $movement ? round($balanceAfter + (float) $movement->quantity_out, 2) : null,
                'balance_after' => $balanceAfter,
                'recorded_by' => $header?->user?->name,
                'remarks' => $header?->remarks,
            ];
        });

        return $paginator;
    }

    public function transferDetail(int $id): array
    {
        $transfer = StockTransfer::query()
            ->with(['product', 'fromWarehouse', 'toWarehouse', 'user'])
            ->where('status', true)
            ->findOrFail($id);

        $movements = StockMovement::query()
            ->where('reference_type', 'stock-transfer')
            ->where('reference_id', $transfer->id)
            ->get()
            ->keyBy('transaction_type');

        $outMovement = $movements->get('transfer_out');
        $inMovement = $movements->get('transfer_in');
        $qty = (float) $transfer->quantity;
        $unitCost = (float) $transfer->unit_cost;
        $outBalance = $outMovement ? (float) $outMovement->balance_quantity : null;
        $inBalance = $inMovement ? (float) $inMovement->balance_quantity : null;

        return [
            'id' => $transfer->id,
            'reference_no' => $transfer->reference_no,
            'transfer_date' => $transfer->transfer_date?->format('Y-m-d'),
            'transfer_time' => $transfer->created_at?->format('H:i:s'),
            'transfer_datetime' => $transfer->transfer_date?->format('Y-m-d') . ' ' . $transfer->created_at?->format('H:i:s'),
            'product_name' => $transfer->product?->name,
            'sku' => $transfer->product?->sku,
            'from_warehouse' => $transfer->fromWarehouse?->name,
            'to_warehouse' => $transfer->toWarehouse?->name,
            'quantity' => $qty,
            'unit_cost' => $unitCost,
            'total_value' => round($qty * $unitCost, 2),
            'from_balance_before' => $outMovement ? round($outBalance + (float) $outMovement->quantity_out, 2) : null,
            'from_balance_after' => $outBalance,
            'to_balance_before' => $inMovement ? round($inBalance - (float) $inMovement->quantity_in, 2) : null,
            'to_balance_after' => $inBalance,
            'created_by' => $transfer->user?->name,
            'remarks' => $transfer->remarks,
            'status' => (bool) $transfer->status,
            'created_at' => $transfer->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $transfer->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function damagedItemDetail(int $id): array
    {
        $item = DamagedStockItem::query()
            ->with(['product', 'damagedStock.warehouse', 'damagedStock.user'])
            ->findOrFail($id);

        $header = $item->damagedStock;
        abort_if(! $header || $header->trashed(), 404);

        $movement = StockMovement::query()
            ->where('reference_type', 'damaged-stock-item')
            ->where('reference_id', $item->id)
            ->first();

        $qty = (float) $item->quantity;
        $unitCost = (float) $item->unit_cost;
        $balanceAfter = $movement ? (float) $movement->balance_quantity : null;

        return [
            'id' => $item->id,
            'damaged_stock_id' => $header->id,
            'reference_no' => $header->reference_no,
            'damage_date' => $header->damage_date?->format('Y-m-d'),
            'damage_time' => $header->created_at?->format('H:i:s'),
            'damage_datetime' => $header->damage_date?->format('Y-m-d') . ' ' . ($header->created_at?->format('H:i:s') ?? ''),
            'product_name' => $item->product?->name,
            'sku' => $item->product?->sku,
            'warehouse' => $header->warehouse?->name,
            'quantity' => $qty,
            'unit_cost' => $unitCost,
            'total_loss' => (float) $item->total_cost,
            'damage_reason' => $item->damage_reason,
            'balance_before' => $movement ? round($balanceAfter + (float) $movement->quantity_out, 2) : null,
            'balance_after' => $balanceAfter,
            'recorded_by' => $header->user?->name,
            'remarks' => $header->remarks,
            'status' => (bool) $header->status,
            'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function summary(Request $request): array
    {
        $damagedQuery = DamagedStockItem::query()
            ->whereHas('damagedStock', function (Builder $q) use ($request) {
                $q->where('status', true);
                $this->applyDamagedHeaderFilters($q, $request);
            });
        if ($request->filled('product_id')) {
            $damagedQuery->where('product_id', $request->product_id);
        }

        $damagedQty = (float) (clone $damagedQuery)->sum('quantity');
        $damagedAmount = (float) (clone $damagedQuery)->sum('total_cost');

        $stockQuery = Stock::query();
        if ($request->filled('warehouse_id')) {
            $stockQuery->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('product_id')) {
            $stockQuery->where('product_id', $request->product_id);
        }

        $currentQty = (float) (clone $stockQuery)->sum('quantity');
        $currentValue = (float) (clone $stockQuery)->selectRaw('SUM(quantity * average_cost) as total')->value('total');

        return [
            'warehouse_totals' => $this->warehouseTotals($request),
            'total_damaged_quantity' => $damagedQty,
            'total_damaged_amount' => round($damagedAmount, 2),
            'current_stock_quantity' => $currentQty,
            'current_stock_value' => round($currentValue, 2),
        ];
    }

    public function warehouseTotals(Request $request): array
    {
        $warehouses = Warehouse::query()
            ->when($request->filled('warehouse_id'), fn (Builder $q) => $q->where('id', $request->warehouse_id))
            ->orderBy('name')
            ->get(['id', 'name']);

        return $warehouses->map(function (Warehouse $warehouse) use ($request) {
            $quantity = (float) Stock::query()
                ->where('warehouse_id', $warehouse->id)
                ->when($request->filled('product_id'), fn (Builder $q) => $q->where('product_id', $request->product_id))
                ->sum('quantity');

            return [
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->name,
                'quantity' => $quantity,
            ];
        })->values()->all();
    }

    public function filterKeys(): array
    {
        return [
            'date_from',
            'date_to',
            'product_id',
            'warehouse_id',
        ];
    }

    protected function applyDamagedHeaderFilters(Builder $query, Request $request): void
    {
        $query
            ->when($request->filled('date_from'), fn (Builder $q) => $q->whereDate('damage_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn (Builder $q) => $q->whereDate('damage_date', '<=', $request->date_to))
            ->when($request->filled('warehouse_id'), fn (Builder $q) => $q->where('warehouse_id', $request->warehouse_id));
    }
}
