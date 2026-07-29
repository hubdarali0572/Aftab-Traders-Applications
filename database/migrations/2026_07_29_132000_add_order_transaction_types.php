<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE customer_ledgers MODIFY transaction_type ENUM(
            'opening_balance',
            'sale',
            'sale_return',
            'order',
            'order_return',
            'payment_received',
            'debit_note',
            'credit_note',
            'adjustment'
        ) NOT NULL");

        DB::statement("ALTER TABLE stock_movements MODIFY transaction_type ENUM(
            'opening_stock',
            'purchase',
            'purchase_return',
            'sale',
            'sale_return',
            'order',
            'order_return',
            'adjustment',
            'transfer_in',
            'transfer_out',
            'damage'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::table('customer_ledgers')->whereIn('transaction_type', ['order', 'order_return'])->delete();
        DB::table('stock_movements')->whereIn('transaction_type', ['order', 'order_return'])->delete();

        DB::statement("ALTER TABLE customer_ledgers MODIFY transaction_type ENUM(
            'opening_balance',
            'sale',
            'sale_return',
            'payment_received',
            'debit_note',
            'credit_note',
            'adjustment'
        ) NOT NULL");

        DB::statement("ALTER TABLE stock_movements MODIFY transaction_type ENUM(
            'opening_stock',
            'purchase',
            'purchase_return',
            'sale',
            'sale_return',
            'adjustment',
            'transfer_in',
            'transfer_out',
            'damage'
        ) NOT NULL");
    }
};
