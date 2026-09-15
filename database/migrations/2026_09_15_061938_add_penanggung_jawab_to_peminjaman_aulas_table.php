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
            $table->string('instansi')->nullable()->after('nomor_pengajuan');
            $table->string('penanggung_jawab')->nullable()->after('instansi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman_aulas', function (Blueprint $table) {
            $table->dropColumn(['instansi', 'penanggung_jawab']);
        });
    }
};
