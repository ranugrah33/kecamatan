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
            $table->string('file_surat_permohonan')->nullable()->after('nomor_surat_permohonan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman_aulas', function (Blueprint $table) {
            $table->dropColumn('file_surat_permohonan');
        });
    }
};
