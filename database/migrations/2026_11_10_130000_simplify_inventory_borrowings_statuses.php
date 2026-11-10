<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah nilai default kolom status dan memperbarui status lama secara aman (Zero Data Loss).
     */
    public function up(): void
    {
        // 1. Ubah nilai default kolom status menjadi 'Menunggu Persetujuan'
        try {
            DB::statement("ALTER TABLE inventory_borrowings ALTER COLUMN status SET DEFAULT 'Menunggu Persetujuan'");
        } catch (\Throwable $e) {
            // Fallback jika database engine tidak mendukung ALTER COLUMN SET DEFAULT
            Schema::table('inventory_borrowings', function (Blueprint $table) {
                $table->string('status')->default('Menunggu Persetujuan')->change();
            });
        }

        // 2. Migrasi data transaksi lama ke status baru
        DB::table('inventory_borrowings')
            ->whereIn('status', ['Diajukan', 'Diproses'])
            ->update(['status' => 'Menunggu Persetujuan']);

        DB::table('inventory_borrowings')
            ->where('status', 'Dikembalikan')
            ->update(['status' => 'Selesai']);
    }

    /**
     * Reverse the migrations.
     * Mengembalikan status dan nilai default ke kondisi semula.
     */
    public function down(): void
    {
        // 1. Kembalikan default kolom status ke 'Diajukan'
        try {
            DB::statement("ALTER TABLE inventory_borrowings ALTER COLUMN status SET DEFAULT 'Diajukan'");
        } catch (\Throwable $e) {
            Schema::table('inventory_borrowings', function (Blueprint $table) {
                $table->string('status')->default('Diajukan')->change();
            });
        }

        // 2. Rollback data status jika diperlukan
        DB::table('inventory_borrowings')
            ->where('status', 'Menunggu Persetujuan')
            ->update(['status' => 'Diajukan']);
    }
};

