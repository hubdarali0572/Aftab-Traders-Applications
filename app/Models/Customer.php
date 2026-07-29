<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    protected $casts = [
        'status' => 'boolean',
        'opening_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(CustomerLedger::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function saleReturns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function orderReturns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('customers');
    }
}
