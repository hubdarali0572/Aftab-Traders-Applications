<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('expense_name')->nullable()->after('expense_date');
        });

        if (Schema::hasTable('expense_heads') && Schema::hasColumn('expenses', 'expense_head_id')) {
            DB::statement('
                UPDATE expenses e
                INNER JOIN expense_heads eh ON e.expense_head_id = eh.id
                SET e.expense_name = eh.name
                WHERE e.expense_name IS NULL
            ');
        }

        DB::table('expenses')->whereNull('expense_name')->update(['expense_name' => 'General Expense']);

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_head_id');
        });

        DB::statement('ALTER TABLE expenses MODIFY expense_name VARCHAR(255) NOT NULL');

        Schema::table('expenses', function (Blueprint $table) {
            $table->index('expense_name');
        });

        Schema::dropIfExists('expense_heads');
    }

    public function down(): void
    {
        Schema::create('expense_heads', function (Blueprint $table) {
            $table->id();
            $table->string('head_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_head_id')->nullable()->after('expense_date')->constrained()->cascadeOnDelete();
            $table->dropIndex(['expense_name']);
            $table->dropColumn('expense_name');
        });
    }
};
