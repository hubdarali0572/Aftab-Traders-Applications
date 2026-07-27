<?php

namespace App\Models;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WarehouseStock extends Model
{

    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'product_id',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'average_cost',
        'stock_value',
        'minimum_stock',
        'maximum_stock',
        'reorder_level',
        'last_received_at',
        'last_issued_at',
        'status',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
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
            ->useLogName('warehouse-stock');
    }
}
