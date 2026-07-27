<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sale extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'customer_id',
        'warehouse_id',
        'invoice_no',
        'sale_date',
        'sale_type',
        'payment_method',
        'subtotal',
        'discount',
        'tax',
        'other_charges',
        'grand_total',
        'paid_amount',
        'due_amount',
        'sale_status',
        'remarks',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('sales');
    }
}
