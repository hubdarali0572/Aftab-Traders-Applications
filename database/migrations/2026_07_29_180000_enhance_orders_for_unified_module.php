<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('paid_amount', 18, 2)->default(0)->after('grand_total');
            $table->decimal('due_amount', 18, 2)->default(0)->after('paid_amount');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('due_amount');
        });

        DB::table('orders')->where('order_status', 'processing')->update(['order_status' => 'confirmed']);

        DB::statement("ALTER TABLE orders MODIFY order_status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('order_returns', function (Blueprint $table) {
            $table->string('return_reason')->nullable()->after('total_amount');
        });

        DB::table('order_returns')->where('return_status', 'approved')->update(['return_status' => 'completed']);
        DB::table('order_returns')->where('return_status', 'draft')->update(['return_status' => 'pending']);

        DB::statement("ALTER TABLE order_returns MODIFY return_status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE order_returns MODIFY return_status ENUM('draft', 'approved', 'cancelled') NOT NULL DEFAULT 'draft'");
        DB::table('order_returns')->where('return_status', 'completed')->update(['return_status' => 'approved']);
        DB::table('order_returns')->where('return_status', 'pending')->update(['return_status' => 'draft']);

        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropColumn('return_reason');
        });

        DB::statement("ALTER TABLE orders MODIFY order_status ENUM('pending', 'confirmed', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'due_amount', 'payment_status']);
        });
    }
};
