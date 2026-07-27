<?php

namespace App\Models;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockAdjustment extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'reference_no',
        'adjustment_date',
        'adjustment_type',
        'total_quantity',
        'total_amount',
        'remarks',
        'status',
    ];


    protected $casts = [
        'adjustment_date' => 'date:Y-m-d',
        'status' => 'boolean',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('stock-adjustment');
    }
}
