<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SaleDetail extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'sale_id',
        'product_id',
        'selling_unit',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'line_total',
        'remarks',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('sale-details');
    }
}
