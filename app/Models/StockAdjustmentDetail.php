<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockAdjustmentDetail extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'system_quantity',
        'physical_quantity',
        'adjustment_quantity',
        'unit_cost',
        'total_cost',
        'reason',
        'remarks',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'system_quantity' => 'decimal:2',
        'physical_quantity' => 'decimal:2',
        'adjustment_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function stockAdjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('stock-adjustment-detail');
    }
}
