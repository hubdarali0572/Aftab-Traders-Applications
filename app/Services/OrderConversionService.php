<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderReturn;
use App\Models\OrderReturnDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class OrderConversionService
{
    protected array $sellingUnits = ['piece', 'carton', 'box', 'dozen', 'bundle', 'pair'];

    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function convertOrderToSale(Order $order): Sale
    {
        $order->load(['details.unit', 'details.product']);

        if ($order->converted_sale_id) {
            throw new InvalidArgumentException('This order has already been converted to a sales invoice.');
        }

        if ($order->order_status === 'cancelled') {
            throw new InvalidArgumentException('Cancelled orders cannot be converted to sales.');
        }

        if ($order->details->isEmpty()) {
            throw new InvalidArgumentException('Add at least one order line item before converting to a sale.');
        }

        $invoiceNo = 'INV-' . now()->format('YmdHis');
        while (Sale::withTrashed()->where('invoice_no', $invoiceNo)->exists()) {
            $invoiceNo = 'INV-' . now()->format('YmdHis') . '-' . random_int(10, 99);
        }

        $grandTotal = (float) $order->grand_total;

        $sale = Sale::create([
            'user_id' => Auth::id(),
            'customer_id' => $order->customer_id,
            'warehouse_id' => $order->warehouse_id,
            'invoice_no' => $invoiceNo,
            'sale_date' => now()->toDateString(),
            'sale_type' => $order->order_type,
            'payment_method' => 'cash',
            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'tax' => $order->tax,
            'other_charges' => $order->other_charges,
            'grand_total' => $grandTotal,
            'paid_amount' => 0,
            'due_amount' => $grandTotal,
            'sale_status' => 'completed',
            'remarks' => trim(($order->remarks ? $order->remarks . ' | ' : '') . 'Converted from order ' . $order->order_no),
            'status' => true,
        ]);

        foreach ($order->details as $detail) {
            SaleDetail::create([
                'user_id' => Auth::id(),
                'sale_id' => $sale->id,
                'product_id' => $detail->product_id,
                'selling_unit' => $this->mapSellingUnit($detail->unit),
                'quantity' => $detail->quantity,
                'unit_price' => $detail->unit_price,
                'discount' => $detail->discount,
                'tax' => $detail->tax,
                'line_total' => $detail->line_total,
                'remarks' => $detail->remarks,
                'status' => true,
            ]);
        }

        $this->posting->syncSaleStock($sale->fresh(['details']));

        $order->update([
            'converted_sale_id' => $sale->id,
            'order_status' => 'completed',
        ]);

        return $sale->fresh(['details', 'customer', 'warehouse']);
    }

    public function convertOrderReturnToSaleReturn(OrderReturn $orderReturn): SaleReturn
    {
        $orderReturn->load(['details', 'order']);

        if ($orderReturn->converted_sale_return_id) {
            throw new InvalidArgumentException('This order return has already been converted to a sales return.');
        }

        if ($orderReturn->return_status !== 'approved') {
            throw new InvalidArgumentException('Only approved order returns can be converted to sales returns.');
        }

        if ($orderReturn->details->isEmpty()) {
            throw new InvalidArgumentException('Add at least one return line item before converting.');
        }

        $order = $orderReturn->order;
        if (! $order || ! $order->converted_sale_id) {
            throw new InvalidArgumentException('Convert the related order to a sale before converting this order return.');
        }

        $referenceNo = 'SR-' . now()->format('YmdHis');
        while (SaleReturn::withTrashed()->where('reference_no', $referenceNo)->exists()) {
            $referenceNo = 'SR-' . now()->format('YmdHis') . '-' . random_int(10, 99);
        }

        $saleReturn = SaleReturn::create([
            'user_id' => Auth::id(),
            'sale_id' => $order->converted_sale_id,
            'customer_id' => $orderReturn->customer_id,
            'warehouse_id' => $orderReturn->warehouse_id,
            'reference_no' => $referenceNo,
            'return_date' => $orderReturn->return_date,
            'total_quantity' => 0,
            'total_amount' => 0,
            'remarks' => trim(($orderReturn->remarks ? $orderReturn->remarks . ' | ' : '') . 'Converted from order return ' . $orderReturn->reference_no),
            'status' => true,
        ]);

        foreach ($orderReturn->details as $detail) {
            $saleDetail = SaleDetail::where('sale_id', $order->converted_sale_id)
                ->where('product_id', $detail->product_id)
                ->first();

            $qty = (float) $detail->quantity;
            $unitPrice = (float) $detail->unit_price;
            $discount = 0;
            $tax = 0;

            if ($saleDetail) {
                $baseQty = max((float) $saleDetail->quantity, 0.01);
                $discount = round(((float) $saleDetail->discount / $baseQty) * $qty, 2);
                $tax = round(((float) $saleDetail->tax / $baseQty) * $qty, 2);
                $unitPrice = (float) $saleDetail->unit_price;
            }

            $lineTotal = round(($qty * $unitPrice) - $discount + $tax, 2);

            $saleReturnDetail = SaleReturnDetail::create([
                'user_id' => Auth::id(),
                'sale_return_id' => $saleReturn->id,
                'product_id' => $detail->product_id,
                'unit_id' => $detail->unit_id,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'tax' => $tax,
                'line_total' => $lineTotal,
                'reason' => $detail->reason,
                'remarks' => $detail->remarks,
            ]);

            $this->posting->postSaleReturnDetail($saleReturnDetail);
        }

        $orderReturn->update([
            'converted_sale_return_id' => $saleReturn->id,
        ]);

        return $saleReturn->fresh(['details', 'sale', 'customer', 'warehouse']);
    }

    public function recalcOrderTotals(Order $order): void
    {
        $subtotal = (float) $order->details()->sum('line_total');
        $grand = $subtotal - (float) $order->discount + (float) $order->tax + (float) $order->other_charges;

        $order->update([
            'subtotal' => $subtotal,
            'grand_total' => max(0, $grand),
        ]);
    }

    public function recalcOrderReturnTotals(OrderReturn $orderReturn): void
    {
        $orderReturn->update([
            'total_quantity' => (float) $orderReturn->details()->sum('quantity'),
            'total_amount' => (float) $orderReturn->details()->sum('line_total'),
        ]);
    }

    protected function mapSellingUnit(?Unit $unit): string
    {
        if (! $unit) {
            return 'piece';
        }

        $candidates = [
            strtolower((string) $unit->short_name),
            strtolower((string) $unit->name),
        ];

        foreach ($candidates as $value) {
            $normalized = str_replace([' ', '-'], '_', $value);
            if (in_array($normalized, $this->sellingUnits, true)) {
                return $normalized;
            }
            if (str_contains($normalized, 'carton')) {
                return 'carton';
            }
            if (str_contains($normalized, 'box')) {
                return 'box';
            }
            if (str_contains($normalized, 'dozen')) {
                return 'dozen';
            }
            if (str_contains($normalized, 'bundle')) {
                return 'bundle';
            }
            if (str_contains($normalized, 'pair')) {
                return 'pair';
            }
        }

        return 'piece';
    }
}
