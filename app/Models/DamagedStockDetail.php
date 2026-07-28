<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DamagedStockDetail extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'damaged_stock_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'damage_reason',
        'batch_no',
        'serial_no',
        'expiry_date',
        'remarks',
        'status',
    ];

    protected $casts = [
        'expiry_date' => 'date:Y-m-d',
        'status' => 'boolean',
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function damagedStock(): BelongsTo
    {
        return $this->belongsTo(DamagedStock::class);
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
            ->useLogName('damaged-stock-detail');
    }
}
