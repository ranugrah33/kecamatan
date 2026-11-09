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
        Schema::table('peminjaman_aulas', function (Blueprint $table) {
            if (!Schema::hasColumn('peminjaman_aulas', 'surat_notified_at')) {
                $table->timestamp('surat_notified_at')->nullable()->after('file_surat_final');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman_aulas', function (Blueprint $table) {
            if (Schema::hasColumn('peminjaman_aulas', 'surat_notified_at')) {
                $table->dropColumn('surat_notified_at');
            }
        });
    }
};

