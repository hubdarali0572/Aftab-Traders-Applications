<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseTransaction extends Model
{
    protected $fillable = [
        'purchase_id',
        'purchase_return_id',
        'user_id',
        'transaction_date',
        'transaction_type',
        'reference_type',
        'reference_id',
        'reference_no',
        'debit',
        'credit',
        'balance',
        'grand_total',
        'returns_total',
        'net_payable',
        'paid_total',
        'due_total',
        'remarks',
        'status',
    ];

    protected $casts = [
        'transaction_date' => 'date:Y-m-d',
        'status' => 'boolean',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'returns_total' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'due_total' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
