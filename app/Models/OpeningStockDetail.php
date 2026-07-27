<?php

namespace App\Models;

use App\Models\OpeningStock;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OpeningStockDetail extends Model
{

    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'opening_stock_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'batch_no',
        'serial_no',
        'expiry_date',
        'remarks',
        'status',
    ];
    protected $casts = [
        'expiry_date' => 'date:Y-m-d',
        'status' => 'boolean',
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function openingStock()
    {
        return $this->belongsTo(OpeningStock::class);
    }
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
            ->useLogName('opening-stock-detail');
    }
}
