<?php

namespace App\Services;

use App\Models\DamagedStock;
use App\Models\DamagedStockItem;
use App\Models\OpeningStock;
use App\Models\OpeningStockItem;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;

/**
 * Posts / reverses inventory & customer ledger effects for document line items.
 */
class InventoryPostingService
{
    public function __construct(
        protected StockService $stock,
        protected CustomerLedgerService $customerLedger
    ) {
    }

    /* ------------------------------------------------------------------ */
    /* Opening Stock                                                       */
    /* ------------------------------------------------------------------ */

    public function postOpeningItem(OpeningStockItem $item): void
    {
        $header = $item->openingStock ?? OpeningStock::find($item->opening_stock_id);
        if (! $header) {
            return;
        }

        $this->stock->reverse('opening-stock-item', $item->id);

        $this->stock->post(
            (int) $header->warehouse_id,
            (int) $item->product_id,
            'opening_stock',
            'opening-stock-item',
            (int) $item->id,
            $header->reference_no,
            $header->opening_date,
            (float) $item->quantity,
            0,
            (float) $item->unit_cost,
            $item->remarks
        );

        $this->recalcOpeningTotals($header);
    }

    public function reverseOpeningItem(OpeningStockItem $item): void
    {
        $this->stock->reverse('opening-stock-item', $item->id);
        $header = OpeningStock::find($item->opening_stock_id);
        if ($header) {
            $this->recalcOpeningTotals($header);
        }
    }

    protected function recalcOpeningTotals(OpeningStock $header): void
    {
        $header->update([
            'total_quantity' => $header->items()->sum('quantity'),
            'total_amount' => $header->items()->sum('total_cost'),
        ]);
    }

    public function syncOpeningStock(OpeningStock $header): void
    {
        $header->load('items');

        foreach ($header->items as $item) {
            $this->stock->reverse('opening-stock-item', $item->id);
        }

        foreach ($header->items as $item) {
            $this->postOpeningItem($item);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Stock Adjustment                                                    */
    /* ------------------------------------------------------------------ */

    public function postAdjustmentItem(StockAdjustmentItem $item): void
    {
        $header = $item->stockAdjustment ?? StockAdjustment::find($item->stock_adjustment_id);
        if (! $header) {
            return;
        }

        $this->stock->reverse('stock-adjustment-item', $item->id);

        $qty = (float) $item->adjustment_quantity;
        $qtyIn = $qty > 0 ? $qty : 0;
        $qtyOut = $qty < 0 ? abs($qty) : 0;

        if ($qtyIn <= 0 && $qtyOut <= 0) {
            $this->recalcAdjustmentTotals($header);
            return;
        }

        $this->stock->post(
            (int) $header->warehouse_id,
            (int) $item->product_id,
            'adjustment',
            'stock-adjustment-item',
            (int) $item->id,
            $header->reference_no,
            $header->adjustment_date,
            $qtyIn,
            $qtyOut,
            (float) $item->unit_cost,
            $item->reason
        );

        $this->recalcAdjustmentTotals($header);
    }

    public function reverseAdjustmentItem(StockAdjustmentItem $item): void
    {
        $this->stock->reverse('stock-adjustment-item', $item->id);
        $header = StockAdjustment::find($item->stock_adjustment_id);
        if ($header) {
            $this->recalcAdjustmentTotals($header);
        }
    }

    protected function recalcAdjustmentTotals(StockAdjustment $header): void
    {
        $header->update([
            'total_quantity' => $header->items()->sum(DB::raw('ABS(adjustment_quantity)')),
            'total_amount' => $header->items()->sum('total_cost'),
        ]);
    }

    public function syncAdjustmentStock(StockAdjustment $header): void
    {
        $header->load('items');

        foreach ($header->items as $item) {
            $this->stock->reverse('stock-adjustment-item', $item->id);
        }

        foreach ($header->items as $item) {
            $this->postAdjustmentItem($item);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Stock Transfer                                                      */
    /* ------------------------------------------------------------------ */

    public function postTransfer(StockTransfer $transfer): void
    {
        if (! $transfer->status) {
            return;
        }

        $this->stock->reverse('stock-transfer', $transfer->id);

        $unitCost = $this->resolveTransferUnitCost($transfer);

        $this->stock->post(
            (int) $transfer->from_warehouse_id,
            (int) $transfer->product_id,
            'transfer_out',
            'stock-transfer',
            (int) $transfer->id,
            $transfer->reference_no,
            $transfer->transfer_date,
            0,
            (float) $transfer->quantity,
            $unitCost,
            $transfer->remarks
        );

        $this->stock->post(
            (int) $transfer->to_warehouse_id,
            (int) $transfer->product_id,
            'transfer_in',
            'stock-transfer',
            (int) $transfer->id,
            $transfer->reference_no,
            $transfer->transfer_date,
            (float) $transfer->quantity,
            0,
            $unitCost,
            $transfer->remarks
        );
    }

    public function reverseTransfer(StockTransfer $transfer): void
    {
        $this->stock->reverse('stock-transfer', $transfer->id);
    }

    protected function resolveTransferUnitCost(StockTransfer $transfer): float
    {
        if ((float) $transfer->unit_cost > 0) {
            return (float) $transfer->unit_cost;
        }

        return (float) DB::table('stocks')
            ->where('warehouse_id', $transfer->from_warehouse_id)
            ->where('product_id', $transfer->product_id)
            ->value('average_cost');
    }

    /* ------------------------------------------------------------------ */
    /* Damaged Stock                                                       */
    /* ------------------------------------------------------------------ */

    public function postDamagedItem(DamagedStockItem $item): void
    {
        $header = $item->damagedStock ?? DamagedStock::find($item->damaged_stock_id);
        if (! $header) {
            return;
        }

        $this->stock->reverse('damaged-stock-item', $item->id);

        $this->stock->post(
            (int) $header->warehouse_id,
            (int) $item->product_id,
            'damage',
            'damaged-stock-item',
            (int) $item->id,
            $header->reference_no,
            $header->damage_date,
            0,
            (float) $item->quantity,
            (float) $item->unit_cost,
            $item->damage_reason
        );

        $this->recalcDamagedTotals($header);
    }

    public function reverseDamagedItem(DamagedStockItem $item): void
    {
        $this->stock->reverse('damaged-stock-item', $item->id);
        $header = DamagedStock::find($item->damaged_stock_id);
        if ($header) {
            $this->recalcDamagedTotals($header);
        }
    }

    protected function recalcDamagedTotals(DamagedStock $header): void
    {
        $header->update([
            'total_quantity' => $header->items()->sum('quantity'),
            'total_amount' => $header->items()->sum('total_cost'),
        ]);
    }

    public function syncDamagedStock(DamagedStock $header): void
    {
        $header->load('items');

        foreach ($header->items as $item) {
            $this->stock->reverse('damaged-stock-item', $item->id);
        }

        foreach ($header->items as $item) {
            $this->postDamagedItem($item);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Purchase                                                            */
    /* ------------------------------------------------------------------ */

    public function postPurchaseDetail(PurchaseDetail $detail): void
    {
        $header = $detail->purchase ?? Purchase::find($detail->purchase_id);
        if (! $header) {
            return;
        }

        $this->stock->reverse('purchase-details', $detail->id);

        if ($header->purchase_status === 'received') {
            $qty = (float) $detail->quantity + (float) $detail->free_quantity;
            $this->stock->post(
                (int) $header->warehouse_id,
                (int) $detail->product_id,
                'purchase',
                'purchase-details',
                (int) $detail->id,
                $header->purchase_no,
                $header->purchase_date,
                $qty,
                0,
                (float) $detail->unit_price,
                $detail->remarks
            );
        }

        $this->recalcPurchaseTotals($header);
    }

    public function reversePurchaseDetail(PurchaseDetail $detail): void
    {
        $this->stock->reverse('purchase-details', $detail->id);
        $header = Purchase::find($detail->purchase_id);
        if ($header) {
            $this->recalcPurchaseTotals($header);
        }
    }

    public function syncPurchaseStock(Purchase $purchase): void
    {
        $purchase->load('details');

        foreach ($purchase->details as $detail) {
            $this->stock->reverse('purchase-details', $detail->id);
        }

        if ($purchase->purchase_status === 'received') {
            foreach ($purchase->details as $detail) {
                $this->postPurchaseDetail($detail);
            }
        } else {
            $this->recalcPurchaseTotals($purchase);
        }
    }

    protected function recalcPurchaseTotals(Purchase $header): void
    {
        $subtotal = (float) $header->details()->sum('line_total');
        $grand = $subtotal - (float) $header->discount + (float) $header->tax
            + (float) $header->shipping_cost + (float) $header->other_charges;
        $returnsTotal = (float) $header->returns()->sum('total_amount');
        $netPayable = max(0, $grand - $returnsTotal);
        $due = max(0, $netPayable - (float) $header->paid_amount);

        $paymentStatus = 'unpaid';
        if ($header->paid_amount > 0 && $due > 0) {
            $paymentStatus = 'partial';
        } elseif ($due <= 0 && $netPayable > 0) {
            $paymentStatus = 'paid';
        }

        $header->update([
            'subtotal' => $subtotal,
            'grand_total' => $grand,
            'due_amount' => $due,
            'payment_status' => $paymentStatus,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Purchase Return                                                     */
    /* ------------------------------------------------------------------ */

    public function postPurchaseReturnDetail(PurchaseReturnDetail $detail): void
    {
        $header = $detail->purchaseReturn ?? PurchaseReturn::find($detail->purchase_return_id);
        if (! $header) {
            return;
        }

        $this->stock->reverse('purchase-return-detail', $detail->id);

        $this->stock->post(
            (int) $header->warehouse_id,
            (int) $detail->product_id,
            'purchase_return',
            'purchase-return-detail',
            (int) $detail->id,
            $header->reference_no,
            $header->return_date,
            0,
            (float) $detail->quantity,
            (float) $detail->unit_price,
            $detail->reason
        );

        $this->recalcPurchaseReturnTotals($header);
    }

    public function reversePurchaseReturnDetail(PurchaseReturnDetail $detail): void
    {
        $this->stock->reverse('purchase-return-detail', $detail->id);
        $header = PurchaseReturn::find($detail->purchase_return_id);
        if ($header) {
            $this->recalcPurchaseReturnTotals($header);
        }
    }

    protected function recalcPurchaseReturnTotals(PurchaseReturn $header): void
    {
        $header->update([
            'total_quantity' => $header->details()->sum('quantity'),
            'total_amount' => $header->details()->sum('total_price'),
        ]);

        $purchase = Purchase::find($header->purchase_id);
        if ($purchase) {
            $this->recalcPurchaseTotals($purchase);
        }
    }

    public function syncPurchaseReturnStock(PurchaseReturn $purchaseReturn): void
    {
        $purchaseReturn->load('details');

        foreach ($purchaseReturn->details as $detail) {
            $this->stock->reverse('purchase-return-detail', $detail->id);
        }

        foreach ($purchaseReturn->details as $detail) {
            $this->postPurchaseReturnDetail($detail);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Sale                                                                */
    /* ------------------------------------------------------------------ */

    public function postSaleDetail(SaleDetail $detail): void
    {
        $header = $detail->sale ?? Sale::find($detail->sale_id);
        if (! $header) {
            return;
        }

        $this->stock->reverse('sale-details', $detail->id);

        if ($header->sale_status === 'completed') {
            $this->stock->post(
                (int) $header->warehouse_id,
                (int) $detail->product_id,
                'sale',
                'sale-details',
                (int) $detail->id,
                $header->invoice_no,
                $header->sale_date,
                0,
                (float) $detail->quantity,
                (float) $detail->unit_price,
                $detail->remarks
            );
        }

        $this->recalcSaleTotals($header);
        $this->syncSaleCustomerLedger($header);
    }

    public function reverseSaleDetail(SaleDetail $detail): void
    {
        $this->stock->reverse('sale-details', $detail->id);
        $header = Sale::find($detail->sale_id);
        if ($header) {
            $this->recalcSaleTotals($header);
            $this->syncSaleCustomerLedger($header);
        }
    }

    public function syncSaleStock(Sale $sale): void
    {
        $sale->load('details');

        foreach ($sale->details as $detail) {
            $this->stock->reverse('sale-details', $detail->id);
        }

        if ($sale->sale_status === 'completed') {
            foreach ($sale->details as $detail) {
                $this->postSaleDetail($detail);
            }
        } else {
            $this->recalcSaleTotals($sale);
            $this->syncSaleCustomerLedger($sale);
        }
    }

    protected function recalcSaleTotals(Sale $header): void
    {
        $subtotal = (float) $header->details()->sum('line_total');
        $grand = $subtotal - (float) $header->discount + (float) $header->tax + (float) $header->other_charges;
        $due = max(0, $grand - (float) $header->paid_amount);

        $header->update([
            'subtotal' => $subtotal,
            'grand_total' => $grand,
            'due_amount' => $due,
        ]);
    }

    public function syncSaleCustomerLedger(Sale $sale): void
    {
        $this->customerLedger->reverse('sales', $sale->id);

        if (! $sale->customer_id || $sale->sale_status !== 'completed') {
            return;
        }

        $sale->refresh();

        if ((float) $sale->grand_total > 0) {
            $this->customerLedger->post(
                (int) $sale->customer_id,
                'sale',
                $sale->sale_date,
                'sales',
                (int) $sale->id,
                $sale->invoice_no,
                (float) $sale->grand_total,
                0,
                'Sale invoice'
            );
        }

        if ((float) $sale->paid_amount > 0) {
            $this->customerLedger->post(
                (int) $sale->customer_id,
                'payment_received',
                $sale->sale_date,
                'sales',
                (int) $sale->id,
                $sale->invoice_no,
                0,
                (float) $sale->paid_amount,
                'Payment against sale'
            );
        }
    }

    /* ------------------------------------------------------------------ */
    /* Sale Return                                                         */
    /* ------------------------------------------------------------------ */

    public function postSaleReturnDetail(SaleReturnDetail $detail): void
    {
        $header = $detail->saleReturn ?? SaleReturn::find($detail->sale_return_id);
        if (! $header) {
            return;
        }

        $this->stock->reverse('sale-return-detail', $detail->id);

        $unitCost = $this->getSaleReturnUnitCost($header->sale_id, $detail->product_id);

        $this->stock->post(
            (int) $header->warehouse_id,
            (int) $detail->product_id,
            'sale_return',
            'sale-return-detail',
            (int) $detail->id,
            $header->reference_no,
            $header->return_date,
            (float) $detail->quantity,
            0,
            $unitCost,
            $detail->reason ?? $detail->remarks
        );

        $this->recalcSaleReturnTotals($header);
        $this->syncSaleReturnCustomerLedger($header);
    }

    public function reverseSaleReturnDetail(SaleReturnDetail $detail): void
    {
        $this->stock->reverse('sale-return-detail', $detail->id);
        $header = SaleReturn::find($detail->sale_return_id);
        if ($header) {
            $this->recalcSaleReturnTotals($header);
            $this->syncSaleReturnCustomerLedger($header);
        }
    }

    protected function recalcSaleReturnTotals(SaleReturn $header): void
    {
        $header->update([
            'total_quantity' => $header->details()->sum('quantity'),
            'total_amount' => $header->details()->sum('line_total'),
        ]);
    }

    public function syncSaleReturnStock(SaleReturn $saleReturn): void
    {
        $saleReturn->load('details');

        foreach ($saleReturn->details as $detail) {
            $this->stock->reverse('sale-return-detail', $detail->id);
        }

        foreach ($saleReturn->details as $detail) {
            $this->postSaleReturnDetail($detail);
        }
    }

    public function syncSaleReturnCustomerLedger(SaleReturn $saleReturn): void
    {
        $this->customerLedger->reverse('sale-return', $saleReturn->id);

        if (! $saleReturn->customer_id) {
            return;
        }

        $saleReturn->refresh();

        if ((float) $saleReturn->total_amount > 0) {
            $this->customerLedger->post(
                (int) $saleReturn->customer_id,
                'sale_return',
                $saleReturn->return_date,
                'sale-return',
                (int) $saleReturn->id,
                $saleReturn->reference_no,
                0,
                (float) $saleReturn->total_amount,
                'Sales return credit note'
            );
        }
    }

    protected function getSaleReturnUnitCost(int $saleId, int $productId): float
    {
        $saleDetail = SaleDetail::where('sale_id', $saleId)
            ->where('product_id', $productId)
            ->first();

        if ($saleDetail) {
            $movement = DB::table('stock_movements')
                ->where('reference_type', 'sale-details')
                ->where('reference_id', $saleDetail->id)
                ->orderByDesc('id')
                ->first();

            if ($movement && $movement->unit_cost !== null) {
                return (float) $movement->unit_cost;
            }
        }

        return (float) DB::table('stocks')
            ->where('warehouse_id', Sale::whereKey($saleId)->value('warehouse_id'))
            ->where('product_id', $productId)
            ->value('average_cost');
    }
}
