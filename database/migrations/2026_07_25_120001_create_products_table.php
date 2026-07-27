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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('tax', 8, 2)->default(0);
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->string('model_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->decimal('weight', 10, 3)->nullable();
            $table->string('hsn_code')->nullable();
            $table->string('origin_country')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->unsignedInteger('maximum_stock')->nullable();
            $table->boolean('track_stock')->default(true);
            $table->boolean('has_expiry')->default(false);
            $table->boolean('is_serialized')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
