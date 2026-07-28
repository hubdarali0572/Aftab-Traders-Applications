<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseDetail extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'purchase_id',
        'product_id',
        'quantity',
        'free_quantity',
        'unit_price',
        'discount',
        'tax',
        'line_total',
        'batch_no',
        'serial_no',
        'manufacturing_date',
        'expiry_date',
        'remarks',
        'status',
    ];

    protected $casts = [
        'manufacturing_date' => 'date:Y-m-d',
        'expiry_date' => 'date:Y-m-d',
        'status' => 'boolean',
        'quantity' => 'decimal:2',
        'free_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('purchase-details');
    }
}
