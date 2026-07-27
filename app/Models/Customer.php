<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'customer_code',
        'customer_type',
        'company_name',
        'customer_name',
        'phone',
        'alternate_phone',
        'email',
        'city',
        'state',
        'address',
        'country',
        'opening_balance',
        'opening_balance_type',
        'credit_limit',
        'tax_number',
        'remarks',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('customers');
    }
}
