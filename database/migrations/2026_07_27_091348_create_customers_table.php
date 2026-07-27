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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Customer Information
            |--------------------------------------------------------------------------
            */

            $table->string('customer_code')->unique();

            $table->enum('customer_type', [
                'retail',
                'wholesale',
                'dealer',
                'distributor',
                'corporate',
                'contractor',
                'reseller',
                'walk_in',
            ]);

            $table->string('company_name')->nullable();

            $table->string('customer_name');

            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */

            $table->string('phone', 30);

            $table->string('alternate_phone', 30)->nullable();

            $table->string('email')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            $table->string('address')->nullable();

            $table->string('city')->nullable();

            $table->string('state')->nullable();

            $table->string('country')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Financial
            |--------------------------------------------------------------------------
            */

            $table->decimal('opening_balance', 18, 2)->default(0);

            $table->enum('opening_balance_type', [
                'debit',
                'credit'
            ])->default('debit');

            $table->decimal('credit_limit', 18, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Other
            |--------------------------------------------------------------------------
            */

            $table->string('tax_number')->nullable();

            $table->boolean('status')->default(true);

            $table->text('remarks')->nullable();

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

            $table->index('customer_code');

            $table->index('customer_name');

            $table->index('customer_type');

            $table->index('phone');

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
