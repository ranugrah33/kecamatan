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
        Schema::create('inventory_borrowing_documents', function (Blueprint $table) {
            $table->id();
            // Menggunakan restrictOnDelete untuk mencegah penghapusan data transaksi secara tidak sengaja
            // menghapus dokumen persetujuan yang telah diarsipkan.
            $table->foreignId('inventory_borrowing_id')
                ->constrained('inventory_borrowings')
                ->restrictOnDelete();
            $table->string('document_type'); // 'draft' / 'final'
            $table->string('file_path');
            $table->string('file_name');
            $table->string('document_status')->default('Surat Final Tersedia');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('version')->default(1);
            $table->string('nomor_surat')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('notified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['inventory_borrowing_id', 'document_type', 'is_active'], 'inv_docs_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_borrowing_documents');
    }
};
