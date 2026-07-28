<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_no')->unique();
            $table->date('order_date');

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('order_type', [
                'wholesale',
                'retail',
            ]);

            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('discount', 18, 2)->default(0);
            $table->decimal('tax', 18, 2)->default(0);
            $table->decimal('other_charges', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0);

            $table->enum('order_status', [
                'pending',
                'confirmed',
                'processing',
                'completed',
                'cancelled',
            ])->default('pending');

            $table->foreignId('converted_sale_id')
                ->nullable()
                ->constrained('sales')
                ->nullOnDelete();

            $table->text('remarks')->nullable();
            $table->boolean('status')->default(true);

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('order_no');
            $table->index('customer_id');
            $table->index('warehouse_id');
            $table->index('order_date');
            $table->index('order_status');
            $table->index('converted_sale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
