<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;


class ProductSellingPrice extends Model
{

    use SoftDeletes, LogsActivity;
    protected $fillable = [
        'user_id',
        'product_id',
        'purchase_price',
        'landing_cost',
        'cost_price',
        'retail_price',
        'wholesale_price',
        'dealer_price',
        'distributor_price',
        'online_price',
        'minimum_selling_price',
        'maximum_discount',
        'profit_margin',
        'effective_from',
        'effective_to',
        'is_default',
        'status',
    ];

    protected $casts = [
        'purchase_price'         => 'decimal:2',
        'landing_cost'           => 'decimal:2',
        'cost_price'             => 'decimal:2',
        'retail_price'           => 'decimal:2',
        'wholesale_price'        => 'decimal:2',
        'dealer_price'           => 'decimal:2',
        'distributor_price'      => 'decimal:2',
        'online_price'           => 'decimal:2',
        'minimum_selling_price'  => 'decimal:2',
        'maximum_discount'       => 'decimal:2',
        'profit_margin'          => 'decimal:2',
        'effective_from'         => 'date',
        'effective_to'           => 'date',
        'is_default'             => 'boolean',
        'status'                 => 'boolean',
    ];

    /**
     * Product Price belongs to Product
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
            ->useLogName('product-selling-price');
    }
}
