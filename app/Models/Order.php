<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'order_no',
        'order_date',
        'customer_id',
        'warehouse_id',
        'order_type',
        'subtotal',
        'discount',
        'tax',
        'other_charges',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'order_status',
        'converted_sale_id',
        'remarks',
        'status',
    ];

    protected $casts = [
        'order_date' => 'date:Y-m-d',
        'status' => 'boolean',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function orderReturns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function convertedSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'converted_sale_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('orders');
    }
}
