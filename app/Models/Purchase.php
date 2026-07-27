<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Purchase extends Model
{
  use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'purchase_no',
        'supplier_invoice_no',
        'supplier_name',
        'purchase_date',
        'subtotal',
        'discount',
        'tax',
        'shipping_cost',
        'other_charges',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'purchase_status',
        'remarks',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('purchases');
    }
}
