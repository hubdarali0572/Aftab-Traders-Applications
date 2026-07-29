<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_transfers')) {
            return;
        }

        if (Schema::hasTable('stock_transfer_items')) {
            $itemsByTransfer = DB::table('stock_transfer_items')
                ->orderBy('id')
                ->get()
                ->groupBy('stock_transfer_id');

            foreach ($itemsByTransfer as $transferId => $items) {
                $item = $items->first();
                DB::table('stock_transfers')
                    ->where('id', $transferId)
                    ->update([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_cost' => $item->unit_cost ?? 0,
                    ]);
            }

            Schema::dropIfExists('stock_transfer_items');
        }

        if (! Schema::hasColumn('stock_transfers', 'product_id')) {
            Schema::table('stock_transfers', function (Blueprint $table) {
                $table->foreignId('product_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('stock_transfers', 'quantity')) {
            Schema::table('stock_transfers', function (Blueprint $table) {
                $table->decimal('quantity', 18, 2)->default(0)->after('transfer_date');
            });
        }

        if (! Schema::hasColumn('stock_transfers', 'unit_cost')) {
            Schema::table('stock_transfers', function (Blueprint $table) {
                $table->decimal('unit_cost', 18, 2)->default(0)->after('quantity');
            });
        }

        foreach (['transfer_status', 'total_quantity', 'total_amount'] as $column) {
            if (Schema::hasColumn('stock_transfers', $column)) {
                Schema::table('stock_transfers', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('stock_transfers')) {
            return;
        }

        Schema::table('stock_transfers', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_transfers', 'transfer_status')) {
                $table->enum('transfer_status', ['draft', 'completed', 'cancelled'])->default('draft')->after('transfer_date');
            }
            if (! Schema::hasColumn('stock_transfers', 'total_quantity')) {
                $table->decimal('total_quantity', 18, 2)->default(0)->after('transfer_status');
            }
            if (! Schema::hasColumn('stock_transfers', 'total_amount')) {
                $table->decimal('total_amount', 18, 2)->default(0)->after('total_quantity');
            }
        });

        if (! Schema::hasTable('stock_transfer_items')) {
            Schema::create('stock_transfer_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->decimal('quantity', 18, 2);
                $table->decimal('unit_cost', 18, 2)->default(0);
                $table->decimal('total_cost', 18, 2)->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['stock_transfer_id', 'product_id']);
            });
        }

        if (Schema::hasColumn('stock_transfers', 'product_id')) {
            Schema::table('stock_transfers', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
                $table->dropColumn(['product_id', 'quantity', 'unit_cost']);
            });
        }
    }
};
