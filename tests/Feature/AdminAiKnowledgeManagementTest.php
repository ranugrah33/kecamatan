<?php

namespace Tests\Feature;

use App\Models\AiKnowledge;
use App\Models\User;
use App\Services\AI\KnowledgeBaseService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminAiKnowledgeManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * TEST 1 — Admin dapat melihat daftar knowledge base dengan kategori
     */
    public function test_admin_can_view_knowledge_base_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.ai_knowledge.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ai_knowledge.index');
        $response->assertViewHas('knowledges');
        $response->assertViewHas('allCategories');
    }

    /**
     * TEST 2 — Admin dapat memfilter knowledge base berdasarkan kategori
     */
    public function test_admin_can_filter_by_category(): void
    {
        AiKnowledge::create([
            'category' => 'Persyaratan Umum',
            'title' => 'Test Syarat Ahli Waris Filter',
            'question' => 'Apa saja syarat ahli waris?',
            'answer' => 'Dokumen ahli waris.',
            'keywords' => 'ahli waris',
            'priority' => 10,
            'is_active' => true,
        ]);

        AiKnowledge::create([
            'category' => 'Pelayanan SIPAF',
            'title' => 'Test Peminjaman Aula Filter',
            'question' => 'Cara pinjam aula?',
            'answer' => 'Alur pinjam aula.',
            'keywords' => 'pinjam aula',
            'priority' => 10,
            'is_active' => true,
        ]);

        // Filter kategori: 'Persyaratan Umum'
        $response = $this->actingAs($this->admin)
            ->get(route('admin.ai_knowledge.index', ['category' => 'Persyaratan Umum']));

        $response->assertStatus(200);
        $response->assertSee('Test Syarat Ahli Waris Filter');
        $response->assertDontSee('Test Peminjaman Aula Filter');
    }

    /**
     * TEST 3 — Admin dapat mencari knowledge base berdasarkan kata kunci
     */
    public function test_admin_can_search_by_keyword_and_question(): void
    {
        AiKnowledge::create([
            'category' => 'Pelayanan SIPAF',
            'title' => 'Pengujian Kursi Unik',
            'question' => 'Bagaimana cara pinjam kursi lipat?',
            'answer' => 'Ajukan di SIPAF.',
            'keywords' => 'kursi lipat besi khusus',
            'priority' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.ai_knowledge.index', ['search' => 'kursi lipat besi khusus']));

        $response->assertStatus(200);
        $response->assertSee('Pengujian Kursi Unik');
    }

    /**
     * TEST 4 — Admin dapat menambahkan data knowledge baru
     */
    public function test_admin_can_create_new_knowledge(): void
    {
        $payload = [
            'category' => 'Profil Kecamatan',
            'title' => 'Sejarah Baru Cikampek',
            'question' => 'Kapan berdirinya Cikampek?',
            'answer' => 'Informasi sejarah Cikampek.',
            'keywords' => 'sejarah, berdiri',
            'priority' => 8,
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ai_knowledge.store'), $payload);

        $response->assertRedirect(route('admin.ai_knowledge.index'));
        $this->assertDatabaseHas('ai_knowledges', [
            'title' => 'Sejarah Baru Cikampek',
            'category' => 'Profil Kecamatan',
            'is_active' => true,
        ]);
    }

    /**
     * TEST 5 — Admin dapat mengedit data knowledge yang ada
     */
    public function test_admin_can_edit_existing_knowledge(): void
    {
        $knowledge = AiKnowledge::create([
            'category' => 'Informasi Umum',
            'title' => 'Jam Buka Lama',
            'question' => 'Jam buka kecamatan?',
            'answer' => '08.00 sampai 15.00',
            'keywords' => 'jam buka',
            'priority' => 5,
            'is_active' => true,
        ]);

        $updatedPayload = [
            'category' => 'Informasi Umum',
            'title' => 'Jam Buka Baru Resmi',
            'question' => 'Jam berapa operasional pelayanan?',
            'answer' => 'Senin sampai Jumat pukul 07.45 - 15.45 WIB',
            'keywords' => 'jam buka, jam operasional',
            'priority' => 15,
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.ai_knowledge.update', $knowledge->id), $updatedPayload);

        $response->assertRedirect(route('admin.ai_knowledge.index'));
        $this->assertDatabaseHas('ai_knowledges', [
            'id' => $knowledge->id,
            'title' => 'Jam Buka Baru Resmi',
            'priority' => 15,
        ]);
    }

    /**
     * TEST 6 — Admin dapat menonaktifkan dan mengaktifkan status knowledge (toggle)
     */
    public function test_admin_can_toggle_knowledge_active_status(): void
    {
        $knowledge = AiKnowledge::create([
            'category' => 'Informasi Umum',
            'title' => 'Test Toggle Status',
            'question' => 'Tes?',
            'answer' => 'Jawaban tes.',
            'keywords' => 'tes',
            'priority' => 5,
            'is_active' => true,
        ]);

        // Toggle menjadi nonaktif
        $response = $this->actingAs($this->admin)
            ->put(route('admin.ai_knowledge.toggle', $knowledge->id));

        $response->assertRedirect(route('admin.ai_knowledge.index'));
        $this->assertDatabaseHas('ai_knowledges', [
            'id' => $knowledge->id,
            'is_active' => false,
        ]);

        // Toggle kembali menjadi aktif
        $this->actingAs($this->admin)
            ->put(route('admin.ai_knowledge.toggle', $knowledge->id));

        $this->assertDatabaseHas('ai_knowledges', [
            'id' => $knowledge->id,
            'is_active' => true,
        ]);
    }

    /**
     * TEST 7 — Admin dapat menghapus knowledge
     */
    public function test_admin_can_delete_knowledge(): void
    {
        $knowledge = AiKnowledge::create([
            'category' => 'FAQ (Frequently Asked Questions)',
            'title' => 'Data Akan Dihapus',
            'question' => 'Pertanyaan hapus?',
            'answer' => 'Jawaban hapus.',
            'keywords' => 'hapus',
            'priority' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.ai_knowledge.destroy', $knowledge->id));

        $response->assertRedirect(route('admin.ai_knowledge.index'));
        $this->assertDatabaseMissing('ai_knowledges', [
            'id' => $knowledge->id,
        ]);
    }

    /**
     * TEST 8 — KnowledgeBaseService membangun prompt dengan 6 Kategori dan 5 Aturan Menjawab
     */
    public function test_knowledge_base_service_builds_prompt_with_6_categories_and_5_rules(): void
    {
        $service = app(KnowledgeBaseService::class);
        $systemPrompt = $service->getSystemPrompt();

        // 1. Verifikasi persona
        $this->assertStringContainsString('Asisten Digital Kecamatan Cikampek', $systemPrompt);
        $this->assertStringContainsString('SIPAF', $systemPrompt);

        // 2. Verifikasi 6 kategori
        $this->assertStringContainsString('Profil Kecamatan', $systemPrompt);
        $this->assertStringContainsString('Persyaratan Umum', $systemPrompt);
        $this->assertStringContainsString('Pelayanan SIPAF', $systemPrompt);
        $this->assertStringContainsString('Prosedur Pelayanan', $systemPrompt);
        $this->assertStringContainsString('Informasi Umum', $systemPrompt);
        $this->assertStringContainsString('FAQ', $systemPrompt);

        // 3. Verifikasi 5 Aturan Utama
        $this->assertStringContainsString('ATURAN 1', $systemPrompt);
        $this->assertStringContainsString('ATURAN 2', $systemPrompt);
        $this->assertStringContainsString('ATURAN 3', $systemPrompt);
        $this->assertStringContainsString('ATURAN 4', $systemPrompt);
        $this->assertStringContainsString('ATURAN 5', $systemPrompt);

        // 4. Verifikasi aturan zero hallucination & anti-mengarang biaya/stok
        $this->assertStringContainsString('Dilarang mengarang nominal biaya', $systemPrompt);
        $this->assertStringContainsString('Dilarang mengarang jumlah persediaan stok barang', $systemPrompt);
        $this->assertStringContainsString('Sertifikat Tanah ≠ Pengajuan Sertifikat/Piagam SIPAF', $systemPrompt);
        $this->assertStringContainsString('Mall Pelayanan Publik (MPP)', $systemPrompt);
    }
}

