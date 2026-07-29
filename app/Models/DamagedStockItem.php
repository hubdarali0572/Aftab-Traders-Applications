<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DamagedStockItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'damaged_stock_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'damage_reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function damagedStock(): BelongsTo
    {
        return $this->belongsTo(DamagedStock::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
