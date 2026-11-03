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
        Schema::table('inventory_borrowings', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_borrowings', 'borrowing_time')) {
                $table->time('borrowing_time')->nullable()->after('borrow_date');
            }
            if (!Schema::hasColumn('inventory_borrowings', 'return_time')) {
                $table->time('return_time')->nullable()->after('return_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_borrowings', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_borrowings', 'borrowing_time')) {
                $table->dropColumn('borrowing_time');
            }
            if (Schema::hasColumn('inventory_borrowings', 'return_time')) {
                $table->dropColumn('return_time');
            }
        });
    }
};

