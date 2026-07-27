<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseDetail extends Model
{
  
  use SoftDeletes, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('purchase-details');
    }
}
