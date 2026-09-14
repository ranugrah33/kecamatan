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
        // For MySQL, the easiest way to update an ENUM column is to re-declare it with all old and new values.
        // Also it seems we might need to cast existing values if we drop any, but here we just add new values.
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE peminjaman_aulas MODIFY COLUMN status ENUM('Menunggu Verifikasi', 'Diproses', 'Disetujui', 'Ditolak', 'Perlu Perbaikan', 'Dibatalkan', 'Selesai', 'Surat Diproses', 'Surat Tersedia') DEFAULT 'Menunggu Verifikasi'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old enum (Warning: if any rows have the new statuses, this will fail or truncate data)
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE peminjaman_aulas MODIFY COLUMN status ENUM('Menunggu Verifikasi', 'Diproses', 'Disetujui', 'Ditolak', 'Perlu Perbaikan', 'Dibatalkan', 'Selesai') DEFAULT 'Menunggu Verifikasi'");
    }
};
