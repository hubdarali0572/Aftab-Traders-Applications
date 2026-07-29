<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('purchase_return_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('transaction_date');

            $table->enum('transaction_type', [
                'purchase',
                'payment',
                'purchase_return',
                'adjustment',
                'cancellation',
            ]);

            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->string('reference_no')->nullable();

            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->decimal('balance', 18, 2)->default(0);

            $table->decimal('grand_total', 18, 2)->default(0);
            $table->decimal('returns_total', 18, 2)->default(0);
            $table->decimal('net_payable', 18, 2)->default(0);
            $table->decimal('paid_total', 18, 2)->default(0);
            $table->decimal('due_total', 18, 2)->default(0);

            $table->text('remarks')->nullable();
            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->index(['purchase_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('transaction_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_transactions');
    }
};
