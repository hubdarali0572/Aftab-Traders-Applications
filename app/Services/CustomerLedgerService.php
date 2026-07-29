<?php

namespace App\Services;

use App\Models\CustomerLedger;
use Illuminate\Support\Facades\Auth;

class CustomerLedgerService
{
    public function __construct(protected StockService $stockService)
    {
    }

    public function post(
        int $customerId,
        string $transactionType,
        $transactionDate,
        string $referenceType,
        int $referenceId,
        ?string $referenceNo,
        float $debit,
        float $credit,
        ?string $remarks = null
    ): CustomerLedger {
        $entry = CustomerLedger::create([
            'user_id' => Auth::id(),
            'customer_id' => $customerId,
            'transaction_date' => $transactionDate,
            'transaction_type' => $transactionType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reference_no' => $referenceNo,
            'debit' => $debit,
            'credit' => $credit,
            'balance' => 0,
            'remarks' => $remarks,
            'status' => true,
        ]);

        $this->recalculateBalances($customerId);

        return $entry;
    }

    public function reverse(string $referenceType, int $referenceId): void
    {
        $entries = CustomerLedger::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->get();

        $customerIds = $entries->pluck('customer_id')->unique();

        foreach ($entries as $entry) {
            $entry->delete();
        }

        foreach ($customerIds as $customerId) {
            $this->recalculateBalances($customerId);
        }
    }

    public function recalculateBalances(int $customerId): void
    {
        $entries = CustomerLedger::where('customer_id', $customerId)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $running = 0;
        foreach ($entries as $entry) {
            $running += ((float) $entry->debit - (float) $entry->credit);
            $entry->update(['balance' => $running]);
        }
    }

    public function getOutstanding(int $customerId): float
    {
        $latest = CustomerLedger::where('customer_id', $customerId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $latest ? (float) $latest->balance : 0;
    }

    public function deleteAllForCustomer(int $customerId): void
    {
        CustomerLedger::where('customer_id', $customerId)->delete();
    }
}
