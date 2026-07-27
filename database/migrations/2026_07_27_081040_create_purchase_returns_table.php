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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();

            /*
    |--------------------------------------------------------------------------
    | Document Information
    |--------------------------------------------------------------------------
    */

            $table->string('reference_no')->unique();

            $table->foreignId('purchase_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('return_date');

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
    | Totals
    |--------------------------------------------------------------------------
    */

            $table->decimal('total_quantity', 18, 2)->default(0);

            $table->decimal('total_amount', 18, 2)->default(0);

            /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

            $table->boolean('status')->default(true);
            /*
    |--------------------------------------------------------------------------
    | Remarks
    |--------------------------------------------------------------------------
    */

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

            $table->index('purchase_id');
            $table->index('warehouse_id');
            $table->index('return_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
