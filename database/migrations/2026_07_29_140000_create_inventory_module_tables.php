<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 18, 2)->default(0);
            $table->decimal('average_cost', 18, 2)->default(0);
            $table->decimal('minimum_stock', 18, 2)->default(0);
            $table->decimal('reorder_level', 18, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['warehouse_id', 'product_id']);
            $table->index(['warehouse_id', 'product_id']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('transaction_type', [
                'opening_stock',
                'purchase',
                'purchase_return',
                'sale',
                'sale_return',
                'adjustment',
                'transfer_in',
                'transfer_out',
                'damage',
            ]);
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->string('reference_no')->nullable();
            $table->dateTime('transaction_date');
            $table->decimal('quantity_in', 18, 2)->default(0);
            $table->decimal('quantity_out', 18, 2)->default(0);
            $table->decimal('balance_quantity', 18, 2)->default(0);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->decimal('total_cost', 18, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['warehouse_id', 'product_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('opening_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('reference_no')->unique();
            $table->date('opening_date');
            $table->decimal('total_quantity', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('opening_stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opening_stock_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 18, 2);
            $table->decimal('unit_cost', 18, 2);
            $table->decimal('total_cost', 18, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['opening_stock_id', 'product_id']);
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('reference_no')->unique();
            $table->date('adjustment_date');
            $table->decimal('total_quantity', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('adjustment_quantity', 18, 2);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->decimal('total_cost', 18, 2)->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['stock_adjustment_id', 'product_id']);
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('reference_no')->unique();
            $table->date('transfer_date');
            $table->decimal('quantity', 18, 2);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('damaged_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('reference_no')->unique();
            $table->date('damage_date');
            $table->decimal('total_quantity', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('damaged_stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('damaged_stock_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 18, 2);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->decimal('total_cost', 18, 2)->default(0);
            $table->string('damage_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['damaged_stock_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damaged_stock_items');
        Schema::dropIfExists('damaged_stocks');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('opening_stock_items');
        Schema::dropIfExists('opening_stocks');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stocks');
    }
};
