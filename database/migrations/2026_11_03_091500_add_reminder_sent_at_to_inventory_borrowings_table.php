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
            if (!Schema::hasColumn('inventory_borrowings', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable()->after('rejection_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_borrowings', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_borrowings', 'reminder_sent_at')) {
                $table->dropColumn('reminder_sent_at');
            }
        });
    }
};

