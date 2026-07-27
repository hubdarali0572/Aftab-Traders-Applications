<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DamagedStockDetail extends Model
{
  
    use SoftDeletes, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('damaged-stock-detail');
    }
}
