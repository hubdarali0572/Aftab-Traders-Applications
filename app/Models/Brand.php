<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Brand extends Model
{
    // Use the traits inside the class
    use SoftDeletes, LogsActivity;

    protected $fillable = ['user_id', 'name', 'slug', 'description', 'status'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Configure the Activity Log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()                // Logs all fields defined in $fillable
            ->logOnlyDirty()               // Only log fields that actually changed
            ->dontSubmitEmptyLogs()        // Don't create a log if nothing changed
            ->useLogName('brand');         // Optional: Categorize logs as 'brand'
    }
}
