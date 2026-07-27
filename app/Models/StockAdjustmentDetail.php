<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockAdjustmentDetail extends Model
{
      use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'product_id',
        'stock_adjustment_id',
        'system_quantity',
        'physical_quantity',
        'adjustment_quantity',
        'unit_cost',
        'total_cost',
        'reason',
        'remarks',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('stock-adjustment-detail');
    }
}
