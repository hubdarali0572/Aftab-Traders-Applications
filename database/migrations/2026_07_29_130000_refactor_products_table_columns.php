<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * No-op on fresh installs where create_products_table already uses the new column names.
     * Applies renames and drops only when upgrading from the legacy products schema.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'model_number')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
        });

        $columnsToDrop = collect(['tax', 'track_stock', 'has_expiry', 'is_serialized'])
            ->filter(fn (string $column) => Schema::hasColumn('products', $column))
            ->values()
            ->all();

        if ($columnsToDrop !== []) {
            Schema::table('products', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('model_number', 'carton_qty');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('manufacturer', 'price_per_carton');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('size', 'pieces_per_carton');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('hsn_code', 'price_per_piece');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('barcode', 'selling_price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('maximum_stock', 'purchase_price');
        });

        foreach (DB::table('products')->select('id', 'carton_qty')->get() as $product) {
            if ($product->carton_qty !== null && ! is_numeric($product->carton_qty)) {
                DB::table('products')->where('id', $product->id)->update(['carton_qty' => null]);
            }
        }

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->change();
            $table->unsignedInteger('carton_qty')->nullable()->change();
            $table->decimal('price_per_carton', 12, 2)->nullable()->change();
            $table->unsignedInteger('pieces_per_carton')->nullable()->change();
            $table->decimal('price_per_piece', 12, 2)->nullable()->change();
            $table->decimal('selling_price', 12, 2)->nullable()->change();
            $table->decimal('purchase_price', 12, 2)->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('products', 'carton_qty') || Schema::hasColumn('products', 'model_number')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('purchase_price', 'maximum_stock');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('selling_price', 'barcode');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('price_per_piece', 'hsn_code');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('pieces_per_carton', 'size');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('price_per_carton', 'manufacturer');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('carton_qty', 'model_number');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('model_number')->nullable()->change();
            $table->string('manufacturer')->nullable()->change();
            $table->string('size')->nullable()->change();
            $table->string('hsn_code')->nullable()->change();
            $table->string('barcode')->nullable()->change();
            $table->unsignedInteger('maximum_stock')->nullable()->change();
            $table->unsignedBigInteger('unit_id')->nullable(false)->change();

            $table->decimal('tax', 8, 2)->default(0);
            $table->boolean('track_stock')->default(true);
            $table->boolean('has_expiry')->default(false);
            $table->boolean('is_serialized')->default(false);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('unit_id')->references('id')->on('units')->cascadeOnDelete();
        });
    }
};
