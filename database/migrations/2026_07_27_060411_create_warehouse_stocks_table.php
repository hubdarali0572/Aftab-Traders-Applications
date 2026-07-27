<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Stock Information
            |--------------------------------------------------------------------------
            */

            // Total Physical Quantity
            $table->decimal('quantity', 18, 2)->default(0);

            // Quantity reserved for pending sales/orders
            $table->decimal('reserved_quantity', 18, 2)->default(0);

            // Available Quantity = Quantity - Reserved
            $table->decimal('available_quantity', 18, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Costing
            |--------------------------------------------------------------------------
            */

            // Moving Average Cost
            $table->decimal('average_cost', 18, 2)->default(0);

            // quantity × average_cost
            $table->decimal('stock_value', 18, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Stock Levels
            |--------------------------------------------------------------------------
            */

            $table->decimal('minimum_stock', 18, 2)->default(0);

            $table->decimal('maximum_stock', 18, 2)->nullable();

            $table->decimal('reorder_level', 18, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            $table->timestamp('last_received_at')->nullable();

            $table->timestamp('last_issued_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(['warehouse_id', 'product_id','user_id']);

            $table->index('warehouse_id');

            $table->index('product_id');
            $table->index('user_id');

            $table->index('quantity');

            $table->index('reorder_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_stocks');
    }
};
