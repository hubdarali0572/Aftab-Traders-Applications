<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OrderReturn extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'reference_no',
        'order_id',
        'return_date',
        'customer_id',
        'warehouse_id',
        'total_quantity',
        'total_amount',
        'return_reason',
        'return_status',
        'converted_sale_return_id',
        'remarks',
        'status',
    ];

    protected $casts = [
        'return_date' => 'date:Y-m-d',
        'status' => 'boolean',
        'total_quantity' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

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
        return $this->hasMany(OrderReturnDetail::class);
    }

    public function convertedSaleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class, 'converted_sale_return_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('order-returns');
    }
}
