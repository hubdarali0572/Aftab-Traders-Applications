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
        Schema::create('stock_transfer_details', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Header
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('stock_transfer_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Product
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
            | Optional Information
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

            $table->index('user_id');
            $table->index('stock_transfer_id');

            $table->index('product_id');

            $table->unique([
                'stock_transfer_id',
                'product_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_details');
    }
};
