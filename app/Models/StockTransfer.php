<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockTransfer extends Model
{
   use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'reference_no',
        'transfer_date',
        'from_warehouse_id',
        'to_warehouse_id',
        'total_quantity',
        'total_amount',
        'stock_status',
        'remarks',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('stock-transfer');
    }
}
