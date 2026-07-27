<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseReturnDetail extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'purchase_return_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
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
            ->useLogName('purchase-return-detail');
    }
}
