<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    /**
     * Post a stock movement: ledger entry + stock balance update.
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
    ): StockMovement {
        if ($quantityIn <= 0 && $quantityOut <= 0) {
            throw new InvalidArgumentException('Stock movement requires a positive quantity.');
        }

        if ($quantityOut > 0) {
            $this->assertSufficientStock($warehouseId, $productId, $quantityOut);
        }

        $qty = $quantityIn > 0 ? $quantityIn : $quantityOut;
        $totalCost = round($qty * $unitCost, 2);

        $movement = StockMovement::create([
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
        ]);

        $this->applyStockDelta(
            $warehouseId,
            $productId,
            $quantityIn - $quantityOut,
            $unitCost,
            $quantityIn > 0,
            $quantityOut > 0
        );

        $this->recalculateBalances($productId, $warehouseId);

        return $movement;
    }

    /**
     * Reverse all movements for a reference and sync stock balances.
     */
    public function reverse(string $referenceType, int $referenceId): void
    {
        $entries = StockMovement::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->get();

        if ($entries->isEmpty()) {
            return;
        }

        $pairs = [];

        foreach ($entries as $entry) {
            $this->applyStockDelta(
                $entry->warehouse_id,
                $entry->product_id,
                $entry->quantity_out - $entry->quantity_in,
                (float) $entry->unit_cost,
                $entry->quantity_out > 0,
                $entry->quantity_in > 0
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

    protected function findOrCreateStock(int $warehouseId, int $productId): Stock
    {
        $stock = Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        if ($stock) {
            return $stock;
        }

        return Stock::create([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'quantity' => 0,
            'average_cost' => 0,
            'minimum_stock' => 0,
            'reorder_level' => 0,
        ]);
    }

    public function syncStockQuantity(int $productId, int $warehouseId): void
    {
        $latest = StockMovement::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $stock = $this->findOrCreateStock($warehouseId, $productId);
        $stock->quantity = $latest ? (float) $latest->balance_quantity : 0;
        $stock->save();
    }

    public function recalculateBalances(int $productId, int $warehouseId): void
    {
        $entries = StockMovement::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = 0;
        foreach ($entries as $entry) {
            $runningBalance += ((float) $entry->quantity_in - (float) $entry->quantity_out);
            $entry->update(['balance_quantity' => $runningBalance]);
        }

        $this->syncStockQuantity($productId, $warehouseId);
    }

    public function getAvailableQuantity(int $warehouseId, int $productId): float
    {
        $stock = Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        return $stock ? (float) $stock->quantity : 0;
    }

    protected function assertSufficientStock(int $warehouseId, int $productId, float $quantityOut): void
    {
        $available = $this->getAvailableQuantity($warehouseId, $productId);

        if ($quantityOut > $available + 0.0001) {
            throw new InvalidArgumentException('Insufficient stock in the selected warehouse.');
        }
    }

    protected function applyStockDelta(
        int $warehouseId,
        int $productId,
        float $deltaQty,
        float $unitCost,
        bool $isReceive,
        bool $isIssue
    ): void {
        $stock = $this->findOrCreateStock($warehouseId, $productId);

        $oldQty = (float) $stock->quantity;
        $oldAvg = (float) $stock->average_cost;

        if ($deltaQty > 0 && $isReceive) {
            $newQty = $oldQty + $deltaQty;
            if ($newQty > 0) {
                $stock->average_cost = round((($oldQty * $oldAvg) + ($deltaQty * $unitCost)) / $newQty, 2);
            }
        } elseif ($deltaQty < 0 || $isIssue) {
            if ($deltaQty > 0 && ! $isReceive) {
                $newQty = $oldQty + $deltaQty;
                if ($newQty > 0 && $oldQty > 0) {
                    $stock->average_cost = round((($oldQty * $oldAvg) + ($deltaQty * $unitCost)) / $newQty, 2);
                } elseif ($oldQty <= 0) {
                    $stock->average_cost = $unitCost;
                }
            }
        }

        $stock->quantity = round($oldQty + $deltaQty, 2);
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
