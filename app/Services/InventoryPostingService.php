<?php

namespace App\Services;

use App\Models\DamagedStock;
use App\Models\DamagedStockDetail;
use App\Models\OpeningStock;
use App\Models\OpeningStockDetail;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use App\Models\StockTransfer;
use App\Models\StockTransferDetail;
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

    public function postOpeningDetail(OpeningStockDetail $detail): void
    {
        $header = $detail->openingStock ?? OpeningStock::find($detail->opening_stock_id);
        if (! $header) {
            return;
        }

        $this->stock->reverse('opening-stock-detail', $detail->id);

        $this->stock->post(
            (int) $header->warehouse_id,
            (int) $detail->product_id,
            'opening_stock',
            'opening-stock-detail',
            (int) $detail->id,
            $header->reference_no,
            $header->opening_date,
            (float) $detail->quantity,
            0,
            (float) $detail->unit_cost,
            $detail->remarks
        );

        $this->recalcOpeningTotals($header);
    }

    public function reverseOpeningDetail(OpeningStockDetail $detail): void
    {
        $this->stock->reverse('opening-stock-detail', $detail->id);
        $header = OpeningStock::find($detail->opening_stock_id);
        if ($header) {
            $this->recalcOpeningTotals($header);
        }
    }

    protected function recalcOpeningTotals(OpeningStock $header): void
    {
        $header->update([
            'total_quantity' => $header->details()->sum('quantity'),
            'total_amount' => $header->details()->sum('total_cost'),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Stock Adjustment                                                    */
    /* ------------------------------------------------------------------ */

    public function postAdjustmentDetail(StockAdjustmentDetail $detail): void
    {
        $header = $detail->stockAdjustment ?? StockAdjustment::find($detail->stock_adjustment_id);
        if (! $header) {
            return;
        }

        $this->stock->reverse('stock-adjustment-detail', $detail->id);

        $qty = (float) $detail->adjustment_quantity;
        $qtyIn = $qty > 0 ? $qty : 0;
        $qtyOut = $qty < 0 ? abs($qty) : 0;

        if ($qtyIn <= 0 && $qtyOut <= 0) {
            $this->recalcAdjustmentTotals($header);
            return;
        }

        $this->stock->post(
            (int) $header->warehouse_id,
            (int) $detail->product_id,
            'adjustment',
            'stock-adjustment-detail',
            (int) $detail->id,
            $header->reference_no,
            $header->adjustment_date,
            $qtyIn,
            $qtyOut,
            (float) $detail->unit_cost,
            $detail->reason ?? $detail->remarks
        );

        $this->recalcAdjustmentTotals($header);
    }

    public function reverseAdjustmentDetail(StockAdjustmentDetail $detail): void
    {
        $this->stock->reverse('stock-adjustment-detail', $detail->id);
        $header = StockAdjustment::find($detail->stock_adjustment_id);
        if ($header) {
            $this->recalcAdjustmentTotals($header);
        }
    }

    protected function recalcAdjustmentTotals(StockAdjustment $header): void
    {
        $header->update([
            'total_quantity' => $header->details()->sum(DB::raw('ABS(adjustment_quantity)')),
            'total_amount' => $header->details()->sum('total_cost'),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Stock Transfer                                                      */
    /* ------------------------------------------------------------------ */

    public function postTransferDetail(StockTransferDetail $detail): void
    {
        $header = $detail->stockTransfer ?? StockTransfer::with([])->find($detail->stock_transfer_id);
        if (! $header || $header->stock_status !== 'completed') {
            $this->recalcTransferTotals($header);
            return;
        }

        $this->stock->reverse('stock-transfer-detail', $detail->id);

        // Out from source
        $this->stock->post(
            (int) $header->from_warehouse_id,
            (int) $detail->product_id,
            'transfer_out',
            'stock-transfer-detail',
            (int) $detail->id,
            $header->reference_no,
            $header->transfer_date,
            0,
            (float) $detail->quantity,
            (float) $detail->unit_cost,
            $detail->remarks
        );

        // In to destination
        $this->stock->post(
            (int) $header->to_warehouse_id,
            (int) $detail->product_id,
            'transfer_in',
            'stock-transfer-detail',
            (int) $detail->id,
            $header->reference_no,
            $header->transfer_date,
            (float) $detail->quantity,
            0,
            (float) $detail->unit_cost,
            $detail->remarks
        );

        $this->recalcTransferTotals($header);
    }

    public function reverseTransferDetail(StockTransferDetail $detail): void
    {
        $this->stock->reverse('stock-transfer-detail', $detail->id);
        $header = StockTransfer::find($detail->stock_transfer_id);
        if ($header) {
            $this->recalcTransferTotals($header);
        }
    }

    public function syncTransferStock(StockTransfer $transfer): void
    {
        $transfer->load('details');

        foreach ($transfer->details as $detail) {
            $this->stock->reverse('stock-transfer-detail', $detail->id);
        }

        if ($transfer->stock_status === 'completed') {
            foreach ($transfer->details as $detail) {
                $this->postTransferDetail($detail);
            }
        } else {
            $this->recalcTransferTotals($transfer);
        }
    }

    protected function recalcTransferTotals(?StockTransfer $header): void
    {
        if (! $header) {
            return;
        }
        $header->update([
            'total_quantity' => $header->details()->sum('quantity'),
            'total_amount' => $header->details()->sum('total_cost'),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Damaged Stock                                                       */
    /* ------------------------------------------------------------------ */

    public function postDamagedDetail(DamagedStockDetail $detail): void
    {
        $header = $detail->damagedStock ?? DamagedStock::find($detail->damaged_stock_id);
        if (! $header) {
            return;
        }

        $this->stock->reverse('damaged-stock-detail', $detail->id);

        $this->stock->post(
            (int) $header->warehouse_id,
            (int) $detail->product_id,
            'damage',
            'damaged-stock-detail',
            (int) $detail->id,
            $header->reference_no,
            $header->damage_date,
            0,
            (float) $detail->quantity,
            (float) $detail->unit_cost,
            $detail->damage_reason
        );

        $this->recalcDamagedTotals($header);
    }

    public function reverseDamagedDetail(DamagedStockDetail $detail): void
    {
        $this->stock->reverse('damaged-stock-detail', $detail->id);
        $header = DamagedStock::find($detail->damaged_stock_id);
        if ($header) {
            $this->recalcDamagedTotals($header);
        }
    }

    protected function recalcDamagedTotals(DamagedStock $header): void
    {
        $header->update([
            'total_quantity' => $header->details()->sum('quantity'),
            'total_amount' => $header->details()->sum('total_cost'),
        ]);
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
        $due = max(0, $grand - (float) $header->paid_amount);

        $paymentStatus = 'unpaid';
        if ($header->paid_amount > 0 && $due > 0) {
            $paymentStatus = 'partial';
        } elseif ($due <= 0 && $grand > 0) {
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
            $ledger = DB::table('stock_ledgers')
                ->where('reference_type', 'sale-details')
                ->where('reference_id', $saleDetail->id)
                ->orderByDesc('id')
                ->first();

            if ($ledger && $ledger->unit_cost !== null) {
                return (float) $ledger->unit_cost;
            }
        }

        return (float) DB::table('warehouse_stocks')
            ->where('warehouse_id', Sale::whereKey($saleId)->value('warehouse_id'))
            ->where('product_id', $productId)
            ->value('average_cost');
    }
}
