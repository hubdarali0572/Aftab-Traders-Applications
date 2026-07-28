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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            /*
    |--------------------------------------------------------------------------
    | Document
    |--------------------------------------------------------------------------
    */

            $table->string('expense_no')->unique();

            $table->date('expense_date');

            /*
    |--------------------------------------------------------------------------
    | Expense Information
    |--------------------------------------------------------------------------
    */

            $table->foreignId('expense_head_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
    |--------------------------------------------------------------------------
    | Who Requested / Added Expense
    |--------------------------------------------------------------------------
    */

            $table->string('employee_name')->nullable();

            /*
    |--------------------------------------------------------------------------
    | Who Received Payment
    |--------------------------------------------------------------------------
    */

            $table->string('payee_name')->nullable();

            /*
    |--------------------------------------------------------------------------
    | Amount
    |--------------------------------------------------------------------------
    */

            $table->decimal('amount', 18, 2);

            /*
    |--------------------------------------------------------------------------
    | Payment Method
    |--------------------------------------------------------------------------
    */

            $table->enum('payment_method', [
                'cash',
                'bank',
                'cheque',
                'online'
            ])->default('cash');

            /*
    |--------------------------------------------------------------------------
    | References
    |--------------------------------------------------------------------------
    */

            $table->string('reference_no')->nullable();

            $table->string('invoice_no')->nullable();

            /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

            $table->text('description')->nullable();

            $table->text('remarks')->nullable();

            /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

            $table->enum('status', [
                'draft',
                'approved',
                'paid',
                'cancelled'
            ])->default('paid');

            /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            $table->timestamps();

            $table->softDeletes();

            /*
    |--------------------------------------------------------------------------
    | Indexes
    |--------------------------------------------------------------------------
    */

            $table->index('expense_no');
            $table->index('expense_date');
            $table->index('expense_head_id');
            $table->index('warehouse_id');
            $table->index('employee_name');
            $table->index('payment_method');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
