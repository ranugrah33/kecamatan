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
            $table->string('nomor_surat_permohonan')->nullable()->after('catatan_petugas');
            $table->string('nomor_surat_izin')->nullable()->after('nomor_surat_permohonan');
            $table->string('file_surat_final')->nullable()->after('nomor_surat_izin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman_aulas', function (Blueprint $table) {
            $table->dropColumn(['nomor_surat_permohonan', 'nomor_surat_izin', 'file_surat_final']);
        });
    }
};
