<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OpeningStock extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'reference_no',
        'opening_date',
        'total_quantity',
        'total_amount',
        'remarks',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'opening_date' => 'date:Y-m-d',
        'status' => 'boolean',
        'total_quantity' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the warehouse associated with the opening stock.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who created the opening stock record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('opening-stock');
    }
}
