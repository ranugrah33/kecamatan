<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan index aditif untuk mendukung pencarian RAG (FULLTEXT & filter aktif)
     * tanpa mengubah struktur kolom maupun data pada tabel ai_knowledges.
     */
    public function up(): void
    {
        if (!Schema::hasTable('ai_knowledges')) {
            return;
        }

        $driver = DB::getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('ai_knowledges', function (Blueprint $table) {
                $table->index('is_active', 'ai_knowledges_is_active_index');
            });
            return;
        }

        $existingIndexes = collect(DB::select('SHOW INDEX FROM `ai_knowledges`'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        Schema::table('ai_knowledges', function (Blueprint $table) use ($existingIndexes) {
            if (!in_array('ai_knowledges_is_active_index', $existingIndexes, true)) {
                $table->index('is_active', 'ai_knowledges_is_active_index');
            }

            if (!in_array('ai_knowledges_rag_fulltext', $existingIndexes, true)) {
                $table->fullText(
                    ['category', 'title', 'question', 'keywords', 'answer'],
                    'ai_knowledges_rag_fulltext'
                );
            }
        });
    }

    /**
     * Reverse the migrations.
     * Hanya menghapus index aditif tanpa menghapus tabel, kolom, atau data pengetahuan.
     */
    public function down(): void
    {
        if (!Schema::hasTable('ai_knowledges')) {
            return;
        }

        $driver = DB::getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('ai_knowledges', function (Blueprint $table) {
                $table->dropIndex('ai_knowledges_is_active_index');
            });
            return;
        }

        $existingIndexes = collect(DB::select('SHOW INDEX FROM `ai_knowledges`'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        Schema::table('ai_knowledges', function (Blueprint $table) use ($existingIndexes) {
            if (in_array('ai_knowledges_rag_fulltext', $existingIndexes, true)) {
                $table->dropFullText('ai_knowledges_rag_fulltext');
            }

            if (in_array('ai_knowledges_is_active_index', $existingIndexes, true)) {
                $table->dropIndex('ai_knowledges_is_active_index');
            }
        });
    }
};

