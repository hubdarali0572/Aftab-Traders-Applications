<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_stocks', function (Blueprint $table) {
            $table->timestamp('last_updated_at')->nullable()->after('last_issued_at');
            $table->index('last_updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_stocks', function (Blueprint $table) {
            $table->dropIndex(['last_updated_at']);
            $table->dropColumn('last_updated_at');
        });
    }
};
