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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Document Information
            |--------------------------------------------------------------------------
            */

            $table->string('invoice_no')->unique();

            $table->date('sale_date');

            /*
            |--------------------------------------------------------------------------
            | Sale Type
            |--------------------------------------------------------------------------
            */

            $table->enum('sale_type', [
                'retail',
                'wholesale',
                'dealer',
                'distributor',
                'corporate',
                'contractor',
                'reseller',
                'walk_in',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Warehouse
            |--------------------------------------------------------------------------
            */

            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            $table->enum('payment_method', [
                'cash',
                'bank',
                'cheque',
                'card',
                'online',
            ])->default('cash');

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal('subtotal', 18, 2)->default(0);

            $table->decimal('discount', 18, 2)->default(0);

            $table->decimal('tax', 18, 2)->default(0);

            $table->decimal('other_charges', 18, 2)->default(0);

            $table->decimal('grand_total', 18, 2)->default(0);

            $table->decimal('paid_amount', 18, 2)->default(0);

            $table->decimal('due_amount', 18, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('sale_status', [
                'draft',
                'completed',
                'cancelled'
            ])->default('completed');

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

            $table->index('invoice_no');

            $table->index('sale_date');

            $table->index('sale_type');

            $table->index('customer_id');

            $table->index('warehouse_id');

            $table->index('payment_method');

            $table->index('sale_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
