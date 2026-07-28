<?php

namespace App\Services;

use App\Models\StockLedger;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    /**
     * Post a stock movement: ledger entry + warehouse stock update.
     */
    public function post(
        int $warehouseId,
        int $productId,
        string $transactionType,
        string $referenceType,
        int $referenceId,
        ?string $referenceNo,
        $transactionDate,
        float $quantityIn,
        float $quantityOut,
        float $unitCost,
        ?string $remarks = null
    ): StockLedger {
        if ($quantityIn <= 0 && $quantityOut <= 0) {
            throw new InvalidArgumentException('Stock movement requires a positive quantity.');
        }

        if ($quantityOut > 0) {
            $this->assertSufficientStock($warehouseId, $productId, $quantityOut);
        }

        $qty = $quantityIn > 0 ? $quantityIn : $quantityOut;
        $totalCost = round($qty * $unitCost, 2);

        $ledger = StockLedger::create([
            'user_id' => Auth::id(),
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'transaction_type' => $transactionType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reference_no' => $referenceNo,
            'transaction_date' => $transactionDate,
            'quantity_in' => $quantityIn,
            'quantity_out' => $quantityOut,
            'balance_quantity' => 0,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'remarks' => $remarks,
            'status' => true,
        ]);

        $this->applyWarehouseStockDelta(
            $warehouseId,
            $productId,
            $quantityIn - $quantityOut,
            $unitCost,
            $quantityIn > 0,
            $quantityOut > 0
        );

        $this->recalculateBalances($productId, $warehouseId);

        return $ledger;
    }

    /**
     * Reverse all ledger movements for a reference and sync warehouse stocks.
     */
    public function reverse(string $referenceType, int $referenceId): void
    {
        $entries = StockLedger::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->get();

        if ($entries->isEmpty()) {
            return;
        }

        $pairs = [];

        foreach ($entries as $entry) {
            // Reverse the original delta
            $this->applyWarehouseStockDelta(
                $entry->warehouse_id,
                $entry->product_id,
                $entry->quantity_out - $entry->quantity_in,
                (float) $entry->unit_cost,
                $entry->quantity_out > 0, // receiving when reversing an out
                $entry->quantity_in > 0   // issuing when reversing an in
            );

            $pairs[$entry->product_id . ':' . $entry->warehouse_id] = [
                $entry->product_id,
                $entry->warehouse_id,
            ];

            $entry->delete();
        }

        foreach ($pairs as [$productId, $warehouseId]) {
            $this->recalculateBalances($productId, $warehouseId);
        }
    }

    /**
     * Sync warehouse stock quantity to the latest ledger running balance.
     */
    protected function findOrCreateWarehouseStock(int $warehouseId, int $productId): WarehouseStock
    {
        $stock = WarehouseStock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        if ($stock) {
            return $stock;
        }

        return WarehouseStock::create([
            'user_id' => Auth::id() ?? 1,
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'quantity' => 0,
            'reserved_quantity' => 0,
            'available_quantity' => 0,
            'average_cost' => 0,
            'stock_value' => 0,
            'minimum_stock' => 0,
            'reorder_level' => 0,
            'status' => true,
        ]);
    }

    public function syncWarehouseQuantity(int $productId, int $warehouseId): void
    {
        $latest = StockLedger::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $stock = $this->findOrCreateWarehouseStock($warehouseId, $productId);

        $balance = $latest ? (float) $latest->balance_quantity : 0;
        $stock->quantity = $balance;
        $stock->available_quantity = $balance - (float) $stock->reserved_quantity;
        $stock->stock_value = round($balance * (float) $stock->average_cost, 2);
        $stock->save();
    }

    public function recalculateBalances(int $productId, int $warehouseId): void
    {
        $entries = StockLedger::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = 0;
        foreach ($entries as $entry) {
            $runningBalance += ((float) $entry->quantity_in - (float) $entry->quantity_out);
            $entry->update(['balance_quantity' => $runningBalance]);
        }

        $this->syncWarehouseQuantity($productId, $warehouseId);
    }

    public function getAvailableQuantity(int $warehouseId, int $productId): float
    {
        $stock = WarehouseStock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        return $stock ? (float) $stock->available_quantity : 0;
    }

    protected function assertSufficientStock(int $warehouseId, int $productId, float $quantityOut): void
    {
        $available = $this->getAvailableQuantity($warehouseId, $productId);

        if ($quantityOut > $available + 0.0001) {
            throw new InvalidArgumentException(
                "Insufficient stock. Available: {$available}, requested: {$quantityOut}."
            );
        }
    }

    protected function applyWarehouseStockDelta(
        int $warehouseId,
        int $productId,
        float $deltaQty,
        float $unitCost,
        bool $isReceive,
        bool $isIssue
    ): void {
        $stock = $this->findOrCreateWarehouseStock($warehouseId, $productId);

        $oldQty = (float) $stock->quantity;
        $oldAvg = (float) $stock->average_cost;

        if ($deltaQty > 0 && $isReceive) {
            $newQty = $oldQty + $deltaQty;
            if ($newQty > 0) {
                $stock->average_cost = round((($oldQty * $oldAvg) + ($deltaQty * $unitCost)) / $newQty, 2);
            }
            $stock->last_received_at = now();
        } elseif ($deltaQty < 0 || $isIssue) {
            // Keep average cost on outbound; quantity adjusted below
            if ($isIssue) {
                $stock->last_issued_at = now();
            }
            if ($deltaQty > 0 && ! $isReceive) {
                // Reversing an issue (stock coming back) — weighted average with existing cost
                $newQty = $oldQty + $deltaQty;
                if ($newQty > 0 && $oldQty > 0) {
                    $stock->average_cost = round((($oldQty * $oldAvg) + ($deltaQty * $unitCost)) / $newQty, 2);
                } elseif ($oldQty <= 0) {
                    $stock->average_cost = $unitCost;
                }
                $stock->last_received_at = now();
            }
        }

        $stock->quantity = round($oldQty + $deltaQty, 2);
        $stock->available_quantity = round((float) $stock->quantity - (float) $stock->reserved_quantity, 2);
        $stock->stock_value = round((float) $stock->quantity * (float) $stock->average_cost, 2);
        $stock->save();
    }

    /**
     * Run callback inside a DB transaction.
     */
    public function transaction(callable $callback)
    {
        return DB::transaction($callback);
    }
}
