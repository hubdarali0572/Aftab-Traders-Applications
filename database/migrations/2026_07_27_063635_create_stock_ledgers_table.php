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
        Schema::create('stock_ledgers', function (Blueprint $table) {
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
            | Transaction Information
            |--------------------------------------------------------------------------
            */

            $table->enum('transaction_type', [
                'opening_stock',
                'purchase',
                'purchase_return',
                'sale',
                'sale_return',
                'adjustment',
                'transfer_in',
                'transfer_out',
                'damage'
            ]);

            /*
            |--------------------------------------------------------------------------
            | Reference
            |--------------------------------------------------------------------------
            */

            // opening_stocks, purchases, sales etc.
            $table->string('reference_type');

            // Related table primary key
            $table->unsignedBigInteger('reference_id');

            $table->string('reference_no')->nullable();

            $table->dateTime('transaction_date');

            /*
            |--------------------------------------------------------------------------
            | Stock Movement
            |--------------------------------------------------------------------------
            */

            $table->decimal('quantity_in', 18, 2)->default(0);

            $table->decimal('quantity_out', 18, 2)->default(0);

            // Running balance after transaction
            $table->decimal('balance_quantity', 18, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Cost
            |--------------------------------------------------------------------------
            */

            $table->decimal('unit_cost', 18, 2)->default(0);

            $table->decimal('total_cost', 18, 2)->default(0);
               $table->boolean('status')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Remarks
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')->nullable();


            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('user_id');
            $table->index('warehouse_id');

            $table->index('product_id');

            $table->index('transaction_type');

            $table->index('transaction_date');

            $table->index(['reference_type', 'reference_id']);

            $table->index(['warehouse_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');
    }
};
