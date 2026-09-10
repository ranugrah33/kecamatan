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
        Schema::create('peminjaman_aula_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_aula_id')->constrained('peminjaman_aulas')->onDelete('cascade');
            $table->string('nama_dokumen');
            $table->string('path_dokumen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_aula_dokumens');
    }
};
