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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Document Information
            |--------------------------------------------------------------------------
            */

            $table->string('purchase_no')->unique();

            $table->string('supplier_invoice_no')->nullable();
            $table->string('supplier_name')->nullable();

            $table->date('purchase_date');

            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Amounts
            |--------------------------------------------------------------------------
            */

            $table->decimal('subtotal', 18, 2)->default(0);

            $table->decimal('discount', 18, 2)->default(0);

            $table->decimal('tax', 18, 2)->default(0);

            $table->decimal('shipping_cost', 18, 2)->default(0);

            $table->decimal('other_charges', 18, 2)->default(0);

            $table->decimal('grand_total', 18, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            $table->decimal('paid_amount', 18, 2)->default(0);

            $table->decimal('due_amount', 18, 2)->default(0);

            $table->enum('payment_status', [
                'unpaid',
                'partial',
                'paid'
            ])->default('unpaid');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('purchase_status', [
                'draft',
                'received',
                'cancelled'
            ])->default('draft');

            /*
            |--------------------------------------------------------------------------
            | Remarks
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')->nullable();
            $table->boolean('status')->default(true);

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

            $table->index('purchase_no');

            $table->index('purchase_date');

            $table->index('user_id');
            $table->index('warehouse_id');

            $table->index('payment_status');

            $table->index('purchase_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
