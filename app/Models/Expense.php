<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Expense extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'expense_no',
        'expense_date',
        'expense_name',
        'warehouse_id',
        'employee_name',
        'payee_name',
        'amount',
        'payment_method',
        'reference_no',
        'invoice_no',
        'description',
        'remarks',
        'status',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date:Y-m-d',
            'amount' => 'decimal:2',
            'warehouse_id' => 'integer',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Approved and paid expenses count toward P&L and financial reports. */
    public function scopeFinancial(Builder $query): Builder
    {
        return $query->whereIn('status', ['approved', 'paid']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('expense');
    }
}
