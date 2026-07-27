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
        Schema::create('product_selling_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            // Costing
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('landing_cost', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->default(0);

            // Selling Prices
            $table->decimal('retail_price', 12, 2);
            $table->decimal('wholesale_price', 12, 2)->nullable();
            $table->decimal('dealer_price', 12, 2)->nullable();
            $table->decimal('distributor_price', 12, 2)->nullable();
            $table->decimal('online_price', 12, 2)->nullable();

            // Discount / Limits
            $table->decimal('minimum_selling_price', 12, 2)->nullable();
            $table->decimal('maximum_discount', 8, 2)->nullable()
                ->comment('Percentage');

            // Margin
            $table->decimal('profit_margin', 8, 2)->nullable()
                ->comment('Percentage');

            // Price Validity
            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            // Current Active Price
            $table->boolean('is_default')->default(true);

            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_selling_prices');
    }
};
