<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();

            $table->string('reference_no')->unique();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('return_date');

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('total_quantity', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);

            $table->enum('return_status', [
                'draft',
                'approved',
                'cancelled',
            ])->default('draft');

            $table->foreignId('converted_sale_return_id')
                ->nullable()
                ->constrained('sale_returns')
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
            $table->index('reference_no');
            $table->index('order_id');
            $table->index('customer_id');
            $table->index('return_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_returns');
    }
};
