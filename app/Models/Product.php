<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'product_category_id',
        'brand_id',
        'unit_id',
        'tax',
        'name',
        'slug',
        'sku',
        'barcode',
        'model_number',
        'manufacturer',
        'color',
        'size',
        'weight',
        'hsn_code',
        'origin_country',
        'description',
        'minimum_stock',
        'maximum_stock',
        'track_stock',
        'has_expiry',
        'is_serialized',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tax' => 'decimal:2',
            'weight' => 'decimal:3',
            'minimum_stock' => 'integer',
            'maximum_stock' => 'integer',
            'track_stock' => 'boolean',
            'has_expiry' => 'boolean',
            'is_serialized' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('product');
    }
}
