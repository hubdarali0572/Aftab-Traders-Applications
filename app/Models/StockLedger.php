<?php

namespace App\Models;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
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
    protected $casts = [
        'transaction_date' => 'date:Y-m-d', // Fixes the date format for Vue
        'status' => 'boolean',
    ];

    /**
     * Relationship with the User who created the entry
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with the Warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Relationship with the Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('stock-ledger');
    }
}
