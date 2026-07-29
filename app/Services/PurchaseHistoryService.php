<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseTransaction;
use Illuminate\Support\Facades\Auth;

class PurchaseHistoryService
{
    public function purchaseSnapshot(Purchase $purchase): array
    {
        $purchase->loadMissing('returns');

        $grandTotal = (float) $purchase->grand_total;
        $returnsTotal = (float) $purchase->returns->sum('total_amount');
        $netPayable = max(0, $grandTotal - $returnsTotal);
        $paidTotal = (float) $purchase->paid_amount;
        $dueTotal = max(0, $netPayable - $paidTotal);

        return compact('grandTotal', 'returnsTotal', 'netPayable', 'paidTotal', 'dueTotal');
    }

    public function recordPurchaseCreated(Purchase $purchase): void
    {
        if ($purchase->purchase_status === 'cancelled') {
            return;
        }

        $snapshot = $this->purchaseSnapshot($purchase);

        $this->createEntry(
            purchase: $purchase,
            purchaseReturn: null,
            transactionDate: $purchase->purchase_date,
            transactionType: 'purchase',
            referenceType: 'Purchase',
            referenceId: $purchase->id,
            referenceNo: $purchase->purchase_no,
            debit: $snapshot['grandTotal'],
            credit: 0,
            snapshot: $snapshot,
            remarks: 'Purchase recorded' . ($purchase->purchase_status === 'draft' ? ' (Draft)' : ''),
        );

        if ($snapshot['paidTotal'] > 0) {
            $this->createEntry(
                purchase: $purchase,
                purchaseReturn: null,
                transactionDate: $purchase->purchase_date,
                transactionType: 'payment',
                referenceType: 'Purchase',
                referenceId: $purchase->id,
                referenceNo: $purchase->purchase_no,
                debit: 0,
                credit: $snapshot['paidTotal'],
                snapshot: $snapshot,
                remarks: 'Initial payment on purchase',
            );
        }

        $this->recalculateBalances((int) $purchase->id);
    }

    public function recordPurchaseUpdated(Purchase $purchase, Purchase $before): void
    {
        $snapshot = $this->purchaseSnapshot($purchase);

        if ($before->purchase_status !== 'cancelled' && $purchase->purchase_status === 'cancelled') {
            $this->createEntry(
                purchase: $purchase,
                purchaseReturn: null,
                transactionDate: now()->toDateString(),
                transactionType: 'cancellation',
                referenceType: 'Purchase',
                referenceId: $purchase->id,
                referenceNo: $purchase->purchase_no,
                debit: 0,
                credit: $snapshot['dueTotal'],
                snapshot: $snapshot,
                remarks: 'Purchase cancelled',
            );
            $this->recalculateBalances((int) $purchase->id);

            return;
        }

        if ($purchase->purchase_status === 'cancelled') {
            return;
        }

        $grandDelta = round((float) $purchase->grand_total - (float) $before->grand_total, 2);
        if (abs($grandDelta) >= 0.01) {
            $this->createEntry(
                purchase: $purchase,
                purchaseReturn: null,
                transactionDate: $purchase->purchase_date,
                transactionType: 'adjustment',
                referenceType: 'Purchase',
                referenceId: $purchase->id,
                referenceNo: $purchase->purchase_no,
                debit: $grandDelta > 0 ? $grandDelta : 0,
                credit: $grandDelta < 0 ? abs($grandDelta) : 0,
                snapshot: $snapshot,
                remarks: 'Purchase amount adjusted',
            );
        }

        $paidDelta = round((float) $purchase->paid_amount - (float) $before->paid_amount, 2);
        if (abs($paidDelta) >= 0.01) {
            $this->createEntry(
                purchase: $purchase,
                purchaseReturn: null,
                transactionDate: now()->toDateString(),
                transactionType: 'payment',
                referenceType: 'Purchase',
                referenceId: $purchase->id,
                referenceNo: $purchase->purchase_no,
                debit: $paidDelta < 0 ? abs($paidDelta) : 0,
                credit: $paidDelta > 0 ? $paidDelta : 0,
                snapshot: $snapshot,
                remarks: $paidDelta > 0 ? 'Payment recorded' : 'Payment reversed',
            );
        }

        $this->recalculateBalances((int) $purchase->id);
    }

    public function recordReturn(PurchaseReturn $return): void
    {
        $return->loadMissing('purchase');
        $purchase = $return->purchase;
        if (! $purchase) {
            return;
        }

        $snapshot = $this->purchaseSnapshot($purchase);

        $this->reverseByReference('PurchaseReturn', (int) $return->id);

        $this->createEntry(
            purchase: $purchase,
            purchaseReturn: $return,
            transactionDate: $return->return_date,
            transactionType: 'purchase_return',
            referenceType: 'PurchaseReturn',
            referenceId: $return->id,
            referenceNo: $return->reference_no,
            debit: 0,
            credit: (float) $return->total_amount,
            snapshot: $snapshot,
            remarks: 'Purchase return to supplier',
        );

        $this->recalculateBalances((int) $purchase->id);
        $this->syncPurchaseDue($purchase);
    }

    public function reverseReturn(PurchaseReturn $return): void
    {
        $purchase = Purchase::find($return->purchase_id);
        $this->reverseByReference('PurchaseReturn', (int) $return->id);

        if ($purchase) {
            $this->recalculateBalances((int) $purchase->id);
            $this->syncPurchaseDue($purchase);
        }
    }

    public function reverseByReference(string $referenceType, int $referenceId): void
    {
        $entries = PurchaseTransaction::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->get();

        $purchaseIds = $entries->pluck('purchase_id')->unique();

        foreach ($entries as $entry) {
            $entry->delete();
        }

        foreach ($purchaseIds as $purchaseId) {
            $this->recalculateBalances((int) $purchaseId);
        }
    }

    public function recalculateBalances(int $purchaseId): void
    {
        $entries = PurchaseTransaction::where('purchase_id', $purchaseId)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $running = 0.0;
        foreach ($entries as $entry) {
            $running += ((float) $entry->debit - (float) $entry->credit);
            $entry->update(['balance' => round(max(0, $running), 2)]);
        }
    }

    public function syncPurchaseDue(Purchase $purchase): void
    {
        $purchase->refresh();
        $snapshot = $this->purchaseSnapshot($purchase);

        $paymentStatus = 'unpaid';
        if ($snapshot['paidTotal'] > 0 && $snapshot['dueTotal'] > 0) {
            $paymentStatus = 'partial';
        } elseif ($snapshot['dueTotal'] <= 0 && $snapshot['netPayable'] > 0) {
            $paymentStatus = 'paid';
        }

        $purchase->update([
            'due_amount' => $snapshot['dueTotal'],
            'payment_status' => $paymentStatus,
        ]);
    }

    public function ensureHistory(Purchase $purchase): void
    {
        if (PurchaseTransaction::where('purchase_id', $purchase->id)->exists()) {
            return;
        }

        $purchase->load(['returns']);

        if ($purchase->purchase_status !== 'cancelled' && (float) $purchase->grand_total > 0) {
            $this->recordPurchaseCreated($purchase);
        }

        foreach ($purchase->returns as $return) {
            $this->recordReturn($return);
        }
    }

    protected function createEntry(
        Purchase $purchase,
        ?PurchaseReturn $purchaseReturn,
        $transactionDate,
        string $transactionType,
        string $referenceType,
        int $referenceId,
        ?string $referenceNo,
        float $debit,
        float $credit,
        array $snapshot,
        ?string $remarks = null,
    ): PurchaseTransaction {
        return PurchaseTransaction::create([
            'purchase_id' => $purchase->id,
            'purchase_return_id' => $purchaseReturn?->id,
            'user_id' => Auth::id(),
            'transaction_date' => $transactionDate,
            'transaction_type' => $transactionType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reference_no' => $referenceNo,
            'debit' => $debit,
            'credit' => $credit,
            'balance' => 0,
            'grand_total' => $snapshot['grandTotal'],
            'returns_total' => $snapshot['returnsTotal'],
            'net_payable' => $snapshot['netPayable'],
            'paid_total' => $snapshot['paidTotal'],
            'due_total' => $snapshot['dueTotal'],
            'remarks' => $remarks,
            'status' => true,
        ]);
    }
}
