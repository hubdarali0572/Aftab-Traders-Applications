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
        Schema::create('purchase_return_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('purchase_return_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('quantity', 18, 2);

            $table->decimal('unit_price', 18, 2);

            $table->decimal('total_price', 18, 2);

            $table->string('reason')->nullable();

            $table->text('remarks')->nullable();
            
            $table->boolean('status')->default(true);

            $table->timestamps();
               $table->softDeletes();

            $table->index('user_id');
            $table->index('purchase_return_id');
            $table->index('product_id');

            $table->unique([
                'purchase_return_id',
                'product_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_details');
    }
};
