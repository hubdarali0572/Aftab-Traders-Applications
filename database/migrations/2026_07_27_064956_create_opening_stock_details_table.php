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
        Schema::create('opening_stock_details', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Header
            |--------------------------------------------------------------------------
            */

            $table->foreignId('opening_stock_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Product Information
            |--------------------------------------------------------------------------
            */

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Quantity
            |--------------------------------------------------------------------------
            */

            $table->decimal('quantity', 18, 2);

            /*
            |--------------------------------------------------------------------------
            | Cost
            |--------------------------------------------------------------------------
            */

            $table->decimal('unit_cost', 18, 2);

            $table->decimal('total_cost', 18, 2);

            /*
            |--------------------------------------------------------------------------
            | Optional Fields
            |--------------------------------------------------------------------------
            */

            $table->string('batch_no')->nullable();

            $table->string('serial_no')->nullable();

            $table->date('expiry_date')->nullable();

            $table->text('remarks')->nullable();
            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('opening_stock_id');

            $table->index('product_id');

            $table->unique([
                'opening_stock_id',
                'product_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opening_stock_details');
    }
};
