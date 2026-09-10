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
        Schema::create('peminjaman_aulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('aula_id')->constrained('aulas')->onDelete('cascade');
            $table->string('nomor_pengajuan')->unique();
            $table->string('nama_kegiatan');
            $table->string('jenis_kegiatan');
            $table->text('deskripsi_kegiatan')->nullable();
            $table->integer('jumlah_peserta');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->json('fasilitas_dibutuhkan')->nullable();
            $table->text('catatan_tambahan')->nullable();
            $table->enum('status', ['Menunggu Verifikasi', 'Perlu Perbaikan', 'Diproses', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan'])->default('Menunggu Verifikasi');
            $table->text('catatan_petugas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_aulas');
    }
};
