<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OpeningStockDetail extends Model
{
  
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'opening_stock_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
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
            ->useLogName('opening-stock-detail');
    }
}
