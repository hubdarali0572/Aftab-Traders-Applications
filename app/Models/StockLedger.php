<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockLedger extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'product_id',
        'transaction_type',
        'reference_type',
        'reference_id',
        'reference_no',
        'transaction_date',
        'quantity_in',
        'quantity_out',
        'balance_quantity',
        'unit_cost',
        'total_cost',
        'remarks',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('warehouse-ledger');
    }
}
