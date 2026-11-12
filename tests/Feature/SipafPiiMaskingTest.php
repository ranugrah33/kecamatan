<?php

namespace Tests\Feature;

use App\Services\AI\AIRouterService;
use App\Services\AI\GeminiService;
use App\Services\AI\GroqService;
use App\Services\AI\PiiMaskingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SipafPiiMaskingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        Cache::flush();
    }

    /**
     * TEST 1 — NIK 16 digit dimasking menjadi [NIK DISEMBUNYIKAN].
     */
    public function test_1_masks_16_digit_nik_to_nik_placeholder(): void
    {
        $service = app(PiiMaskingService::class);

        $labeled = $service->maskText('NIK saya 3215130101900001 untuk pengajuan surat.');
        $this->assertSame('NIK saya [NIK DISEMBUNYIKAN] untuk pengajuan surat.', $labeled['text']);
        $this->assertSame(1, $labeled['count']);
        $this->assertSame(['NIK'], $labeled['categories']);

        $colon = $service->maskText('NIK: 3215130101900002');
        $this->assertSame('NIK: [NIK DISEMBUNYIKAN]', $colon['text']);
        $this->assertSame(1, $colon['count']);
        $this->assertSame(['NIK'], $colon['categories']);

        $standalone = $service->maskText('Tolong cek data 3215130101900003 ini.');
        $this->assertSame('Tolong cek data [NIK DISEMBUNYIKAN] ini.', $standalone['text']);
        $this->assertSame(1, $standalone['count']);
        $this->assertSame(['NIK'], $standalone['categories']);
    }

    /**
     * TEST 2 — Nomor KK 16 digit dimasking menjadi [NO KK DISEMBUNYIKAN].
     */
    public function test_2_masks_16_digit_kk_to_kk_placeholder(): void
    {
        $service = app(PiiMaskingService::class);

        $r1 = $service->maskText('No. KK: 3215130202880004');
        $this->assertSame('No. KK: [NO KK DISEMBUNYIKAN]', $r1['text']);
        $this->assertSame(1, $r1['count']);
        $this->assertSame(['KK'], $r1['categories']);

        $r2 = $service->maskText('Nomor Kartu Keluarga saya 3215130202880005 ya min');
        $this->assertSame('Nomor Kartu Keluarga saya [NO KK DISEMBUNYIKAN] ya min', $r2['text']);
        $this->assertSame(1, $r2['count']);
        $this->assertSame(['KK'], $r2['categories']);

        $r3 = $service->maskText('KK: 3215 1302 0288 0006 dan NIK: 3215 1301 0190 0001');
        $this->assertSame('KK: [NO KK DISEMBUNYIKAN] dan NIK: [NIK DISEMBUNYIKAN]', $r3['text']);
        $this->assertSame(2, $r3['count']);
        $this->assertEqualsCanonicalizing(['KK', 'NIK'], $r3['categories']);
    }

    /**
     * TEST 3 — Alamat email dimasking menjadi [EMAIL DISEMBUNYIKAN].
     */
    public function test_3_masks_email_addresses_to_email_placeholder(): void
    {
        $service = app(PiiMaskingService::class);

        $r1 = $service->maskText('Hubungi saya di budi.santoso90@example.co.id untuk konfirmasi.');
        $this->assertSame('Hubungi saya di [EMAIL DISEMBUNYIKAN] untuk konfirmasi.', $r1['text']);
        $this->assertSame(1, $r1['count']);
        $this->assertSame(['EMAIL'], $r1['categories']);

        $r2 = $service->maskText('Email: sari_w+kec@desa-cikampek.go.id');
        $this->assertSame('Email: [EMAIL DISEMBUNYIKAN]', $r2['text']);
        $this->assertSame(1, $r2['count']);
        $this->assertSame(['EMAIL'], $r2['categories']);
    }

    /**
     * TEST 4 — Nomor HP / telepon Indonesia dalam berbagai format dimasking menjadi [NOMOR TELEPON DISEMBUNYIKAN].
     */
    public function test_4_masks_indonesian_phone_numbers_with_various_formats(): void
    {
        $service = app(PiiMaskingService::class);

        $cases = [
            'Nomor saya 081234567890' => 'Nomor saya [NOMOR TELEPON DISEMBUNYIKAN]',
            'No. HP: 0812-3456-7890' => 'No. HP: [NOMOR TELEPON DISEMBUNYIKAN]',
            'WA 0812 3456 7890 mohon dibalas' => 'WA [NOMOR TELEPON DISEMBUNYIKAN] mohon dibalas',
            'Kontak +62 812 3456 7890 aktif' => 'Kontak [NOMOR TELEPON DISEMBUNYIKAN] aktif',
            'Telepon 6281234567890' => 'Telepon [NOMOR TELEPON DISEMBUNYIKAN]',
            'No Telp: (021) 7654321' => 'No Telp: [NOMOR TELEPON DISEMBUNYIKAN]',
            'HP saya 0857.1122.3344 ya' => 'HP saya [NOMOR TELEPON DISEMBUNYIKAN] ya',
        ];

        foreach ($cases as $input => $expected) {
            $result = $service->maskText($input);
            $this->assertSame($expected, $result['text'], "Failed phone format: {$input}");
            $this->assertSame(1, $result['count'], "Count mismatch for: {$input}");
            $this->assertSame(['PHONE'], $result['categories'], "Category mismatch for: {$input}");
        }
    }

    /**
     * TEST 5 — NIK dan KK dengan spasi atau tanda hubung tetap termasking penuh.
     */
    public function test_5_masks_nik_and_kk_with_spaces_and_hyphens(): void
    {
        $service = app(PiiMaskingService::class);

        $r1 = $service->maskText('NIK: 3215 1301 0190 0001');
        $this->assertSame('NIK: [NIK DISEMBUNYIKAN]', $r1['text']);
        $this->assertStringNotContainsString('0001', $r1['text']);

        $r2 = $service->maskText('NIK saya 3215-1301-0190-0001');
        $this->assertSame('NIK saya [NIK DISEMBUNYIKAN]', $r2['text']);
        $this->assertStringNotContainsString('3215', $r2['text']);

        $r3 = $service->maskText('Data pemohon 321513-010190-0001 mohon diproses');
        $this->assertSame('Data pemohon [NIK DISEMBUNYIKAN] mohon diproses', $r3['text']);
        $this->assertStringNotContainsString('010190', $r3['text']);

        $r4 = $service->maskText('No. KK: 3215-1302-0288-0002');
        $this->assertSame('No. KK: [NO KK DISEMBUNYIKAN]', $r4['text']);
        $this->assertStringNotContainsString('0002', $r4['text']);
    }

    /**
     * TEST 6 — Angka non-PII, tanggal, biaya, kode transaksi SIPAF, nomor surat, dan pertanyaan umum tidak over-masking.
     */
    public function test_6_preserves_non_pii_numbers_sipaf_codes_dates_currency_and_office_labels(): void
    {
        $service = app(PiiMaskingService::class);

        $safeInputs = [
            'Apakah tersedia kapasitas 100 kursi di aula kecamatan?',
            'Apakah biaya retribusi Rp 250.000 untuk tanggal 17-08-2026?',
            'Jadwal pengajuan pada 08-11-2026 dan 08.11.2026 jam 08.00 - 15.00 WIB.',
            'Cek status peminjaman dengan kode AUL-20261111-0001.',
            'Kode transaksi 1234567890123456 sudah masuk sistem.',
            'Nomor pengajuan 2026111100001234 sedang diproses.',
            'Nomor surat 1234-5678-9012-3456 sudah diterbitkan.',
            'Siapa nama camat Cikampek saat ini?',
            'Nama layanan: Peminjaman Aula Kecamatan Cikampek.',
            'Nama instansi adalah Pemerintah Kecamatan Cikampek.',
            'Alamat kantor Kecamatan Cikampek ada di mana?',
        ];

        foreach ($safeInputs as $input) {
            $result = $service->maskText($input);
            $this->assertSame($input, $result['text'], "Over-masked non-PII input: {$input}");
            $this->assertSame(0, $result['count'], "Non-zero PII count for: {$input}");
            $this->assertSame([], $result['categories'], "Unexpected categories for: {$input}");
        }

        // Pastikan panggilan end-to-end ke Gemini tetap membawa kode SIPAF asli dan data KB internal (telepon kantor)
        $capturedGeminiPayload = null;
        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) use (&$capturedGeminiPayload) {
                $capturedGeminiPayload = $request->data();

                return Http::response([
                    'candidates' => [[
                        'finishReason' => 'STOP',
                        'content' => ['parts' => [['text' => 'Untuk kode AUL-20261111-0001 silakan hubungi kantor.']]],
                    ]],
                ], 200);
            },
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Berapa kapasitas aula untuk kode AUL-20261111-0001 tanggal 17-08-2026 jam 08.00?',
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($capturedGeminiPayload);
        $outboundUserText = $capturedGeminiPayload['contents'][0]['parts'][0]['text'] ?? '';
        $this->assertStringContainsString('AUL-20261111-0001', $outboundUserText);
        $this->assertStringContainsString('17-08-2026', $outboundUserText);
        $this->assertStringNotContainsString('DISEMBUNYIKAN', $outboundUserText);
    }

    /**
     * TEST 7 — Seluruh riwayat percakapan (user, model, assistant) dan context note dimasking sebelum dikirim.
     */
    public function test_7_masks_entire_conversation_history_including_user_and_model_assistant_and_context_note(): void
    {
        $service = app(PiiMaskingService::class);

        $rawHistory = [
            [
                'role' => 'user',
                'text' => 'Nama: Budi Santoso, NIK saya 3215130101900001, ingin pinjam aula.',
                'isError' => false,
            ],
            [
                'role' => 'model',
                'text' => 'Baik Budi Santoso, data NIK 3215130101900001 dan email budi@contoh.id kami catat.',
                'isError' => false,
            ],
            [
                'role' => 'assistant',
                'text' => 'Nomor kontak 0812-3456-7890 dan No. KK: 3215130202880004 sudah diterima.',
                'isError' => true,
            ],
        ];

        $masked = $service->maskHistory($rawHistory);

        $this->assertCount(3, $masked['history']);
        $this->assertSame('user', $masked['history'][0]['role']);
        $this->assertSame('model', $masked['history'][1]['role']);
        $this->assertSame('assistant', $masked['history'][2]['role']);
        $this->assertFalse($masked['history'][0]['isError']);
        $this->assertFalse($masked['history'][1]['isError']);
        $this->assertTrue($masked['history'][2]['isError']);

        // Pastikan original array tidak bermutasi
        $this->assertStringContainsString('3215130101900001', $rawHistory[0]['text']);

        $encodedMaskedHistory = json_encode($masked['history']);
        $this->assertStringNotContainsString('Budi Santoso', $masked['history'][0]['text']);
        $this->assertStringNotContainsString('3215130101900001', $encodedMaskedHistory);
        $this->assertStringNotContainsString('budi@contoh.id', $encodedMaskedHistory);
        $this->assertStringNotContainsString('0812-3456-7890', $encodedMaskedHistory);
        $this->assertStringNotContainsString('3215130202880004', $encodedMaskedHistory);

        // Uji via endpoint chatbot ke Gemini (history berisi user + assistant/model non-error)
        $capturedPayload = null;
        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) use (&$capturedPayload) {
                $capturedPayload = $request->data();

                return Http::response([
                    'candidates' => [[
                        'finishReason' => 'STOP',
                        'content' => ['parts' => [['text' => 'Persyaratan aula tersedia di SIPAF.']]],
                    ]],
                ], 200);
            },
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Kalau persyaratannya apa saja?',
            'history' => [
                [
                    'role' => 'user',
                    'text' => 'Nama saya Adi Nugroho, NIK 3215130101900099 mau meminjam aula kecamatan',
                ],
                [
                    'role' => 'assistant',
                    'text' => 'Baik, kami terima kontak 0813-9988-7766 dan email adi@contoh.id untuk peminjaman aula.',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($capturedPayload);

        $serializedPayload = json_encode($capturedPayload);
        $this->assertStringNotContainsString('Adi Nugroho', $serializedPayload);
        $this->assertStringNotContainsString('3215130101900099', $serializedPayload);
        $this->assertStringNotContainsString('0813-9988-7766', $serializedPayload);
        $this->assertStringNotContainsString('adi@contoh.id', $serializedPayload);
        $this->assertStringContainsString('[IDENTITAS DISEMBUNYIKAN]', $serializedPayload);
        $this->assertStringContainsString('[NIK DISEMBUNYIKAN]', $serializedPayload);
        $this->assertStringContainsString('[NOMOR TELEPON DISEMBUNYIKAN]', $serializedPayload);
        $this->assertStringContainsString('[EMAIL DISEMBUNYIKAN]', $serializedPayload);

        // Pastikan role mapping Gemini tetap valid (user, model, user)
        $roles = array_map(fn ($c) => $c['role'], $capturedPayload['contents']);
        $this->assertSame(['user', 'model', 'user'], $roles);
    }

    /**
     * TEST 8 — Input asli tetap dipakai untuk klasifikasi intent dan RAG internal sebelum masking ke AI.
     */
    public function test_8_preserves_original_unmasked_input_for_intent_classification_and_internal_rag(): void
    {
        $capturedPayload = null;
        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) use (&$capturedPayload) {
                $capturedPayload = $request->data();

                return Http::response([
                    'candidates' => [[
                        'finishReason' => 'STOP',
                        'content' => ['parts' => [['text' => 'Untuk peminjaman aula silakan ikuti prosedur SIPAF.']]],
                    ]],
                ], 200);
            },
        ]);

        $router = app(AIRouterService::class);
        $result = $router->chat(
            'Nama Lengkap: Siti Aminah, NIK: 3215130101900011, Alamat rumah: Jl. Ahmad Yani No. 12 Cikampek, bagaimana cara meminjam aula?'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('SIPAF_RAG', $result['intent']);
        $this->assertGreaterThan(0, $result['retrieved_count']);
        $this->assertNotNull($capturedPayload);

        $systemPrompt = $capturedPayload['system_instruction']['parts'][0]['text'] ?? '';
        $outboundUser = $capturedPayload['contents'][0]['parts'][0]['text'] ?? '';
        $serialized = json_encode($capturedPayload);

        // RAG internal berhasil memasukkan dokumen Aula ke system_instruction
        $this->assertStringContainsString('<KNOWLEDGE_CONTEXT>', $systemPrompt);
        $this->assertStringContainsStringIgnoringCase('aula', $systemPrompt);

        // PII pada user message dan payload keluar termasking bersih
        $this->assertStringContainsString('[IDENTITAS DISEMBUNYIKAN]', $outboundUser);
        $this->assertStringContainsString('[NIK DISEMBUNYIKAN]', $outboundUser);
        $this->assertStringNotContainsString('Siti Aminah', $serialized);
        $this->assertStringNotContainsString('3215130101900011', $serialized);
        $this->assertStringNotContainsString('Jl. Ahmad Yani No. 12 Cikampek', $serialized);
    }

    /**
     * TEST 9 — Request HTTP keluar ke Gemini tidak pernah mengandung PII asli (via router & defense-in-depth).
     */
    public function test_9_gemini_outbound_http_payload_never_contains_raw_pii(): void
    {
        $capturedPayloads = [];
        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) use (&$capturedPayloads) {
                $capturedPayloads[] = $request->data();

                return Http::response([
                    'candidates' => [[
                        'finishReason' => 'STOP',
                        'content' => ['parts' => [['text' => 'Permintaan Anda diproses.']]],
                    ]],
                ], 200);
            },
        ]);

        // Jalur 1: HTTP controller -> AIRouterService -> GeminiService
        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Nama Pemohon: Rahmat Hidayat, NIK: 3215-1301-0190-0022, No. KK: 3215 1302 0288 0033, No. HP: +62 812-3456-7890, Email: rahmat.h@contoh.go.id',
        ]);
        $response->assertStatus(200);

        // Jalur 2: Pemanggilan langsung GeminiService (defense-in-depth)
        $geminiService = app(GeminiService::class);
        $directResult = $geminiService->generateResponse(
            'Email saya rina.kusuma@mail.test dan WA 081355667788.',
            [['role' => 'user', 'text' => 'NIK saya 3215130101900044', 'isError' => false]],
            'System instruction test'
        );
        $this->assertSame('Permintaan Anda diproses.', $directResult);

        $this->assertCount(2, $capturedPayloads);

        $payload1Json = json_encode($capturedPayloads[0]);
        foreach (['Rahmat Hidayat', '3215-1301-0190-0022', '3215 1302 0288 0033', '+62 812-3456-7890', 'rahmat.h@contoh.go.id'] as $pii) {
            $this->assertStringNotContainsString($pii, $payload1Json);
        }
        $this->assertStringContainsString('[IDENTITAS DISEMBUNYIKAN]', $payload1Json);
        $this->assertStringContainsString('[NIK DISEMBUNYIKAN]', $payload1Json);
        $this->assertStringContainsString('[NO KK DISEMBUNYIKAN]', $payload1Json);
        $this->assertStringContainsString('[NOMOR TELEPON DISEMBUNYIKAN]', $payload1Json);
        $this->assertStringContainsString('[EMAIL DISEMBUNYIKAN]', $payload1Json);

        $payload2Json = json_encode($capturedPayloads[1]);
        $this->assertStringNotContainsString('rina.kusuma@mail.test', $payload2Json);
        $this->assertStringNotContainsString('081355667788', $payload2Json);
        $this->assertStringNotContainsString('3215130101900044', $payload2Json);
    }

    /**
     * TEST 10 — Request HTTP keluar ke Groq tidak pernah mengandung PII asli (langsung & via circuit breaker).
     */
    public function test_10_groq_outbound_http_payload_never_contains_raw_pii(): void
    {
        $capturedGroqPayloads = [];
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'Rate limited'], 429),
            'api.groq.com/*' => function ($request) use (&$capturedGroqPayloads) {
                $capturedGroqPayloads[] = $request->data();

                return Http::response([
                    'choices' => [['finish_reason' => 'stop', 'message' => ['content' => 'Respons Groq aman.']]],
                ], 200);
            },
        ]);

        // Jalur 1: Pemanggilan langsung GroqService (defense-in-depth)
        $groqService = app(GroqService::class);
        $direct = $groqService->generateResponse(
            'Nama: Dewi Lestari, NIK: 3215130101900055, HP: 0812-1122-3344',
            [
                ['role' => 'user', 'text' => 'No. KK: 3215130202880066', 'isError' => false],
                ['role' => 'model', 'text' => 'Email dewi@mail.test tercatat', 'isError' => false],
            ],
            'System prompt test'
        );
        $this->assertSame('Respons Groq aman.', $direct);

        // Jalur 2: Trigger 429 cooldown Gemini lalu request berikutnya via AIRouterService langsung skip Gemini ke Groq
        $this->postJson(route('chatbot.chat'), ['message' => 'Apa itu inflasi?'])->assertStatus(200);
        $cooldownResult = app(AIRouterService::class)->chat(
            'No. HP saya 0857-9988-1122 dan email agus.s@contoh.id, apa itu SKTM?'
        );
        $this->assertTrue($cooldownResult['success']);
        $this->assertSame('groq', $cooldownResult['provider']);

        $this->assertCount(3, $capturedGroqPayloads);

        $directJson = json_encode($capturedGroqPayloads[0]);
        foreach (['Dewi Lestari', '3215130101900055', '0812-1122-3344', '3215130202880066', 'dewi@mail.test'] as $pii) {
            $this->assertStringNotContainsString($pii, $directJson);
        }

        $cooldownJson = json_encode($capturedGroqPayloads[2]);
        $this->assertStringNotContainsString('0857-9988-1122', $cooldownJson);
        $this->assertStringNotContainsString('agus.s@contoh.id', $cooldownJson);
        $this->assertStringContainsString('[NOMOR TELEPON DISEMBUNYIKAN]', $cooldownJson);
        $this->assertStringContainsString('[EMAIL DISEMBUNYIKAN]', $cooldownJson);
    }

    /**
     * TEST 11 — Saat Gemini gagal (HTTP 503), fallback Groq tetap menerima pesan, history, dan konteks yang termasking.
     */
    public function test_11_gemini_failover_to_groq_still_sends_masked_user_history_and_context(): void
    {
        $capturedGemini = null;
        $capturedGroq = null;

        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) use (&$capturedGemini) {
                $capturedGemini = $request->data();

                return Http::response(['error' => 'Service unavailable'], 503);
            },
            'api.groq.com/*' => function ($request) use (&$capturedGroq) {
                $capturedGroq = $request->data();

                return Http::response([
                    'choices' => [['finish_reason' => 'stop', 'message' => ['content' => 'Fallback Groq menerima data termasking.']]],
                ], 200);
            },
        ]);

        $result = app(AIRouterService::class)->chat(
            'NIK saya 3215130101900077, No. HP: 0812-7766-5544, bagaimana syarat pinjam aula?',
            [
                ['role' => 'user', 'text' => 'Email saya hendra@mail.id, ingin meminjam aula'],
                ['role' => 'model', 'text' => 'Baik, pengajuan aula bisa lewat SIPAF.'],
            ]
        );

        $this->assertTrue($result['success']);
        $this->assertSame('groq', $result['provider']);
        $this->assertSame('SIPAF_RAG', $result['intent']);
        $this->assertGreaterThan(0, $result['retrieved_count']);

        $this->assertNotNull($capturedGemini);
        $this->assertNotNull($capturedGroq);

        $geminiJson = json_encode($capturedGemini);
        $groqJson = json_encode($capturedGroq);

        foreach ([$geminiJson, $groqJson] as $payloadJson) {
            $this->assertStringNotContainsString('3215130101900077', $payloadJson);
            $this->assertStringNotContainsString('0812-7766-5544', $payloadJson);
            $this->assertStringNotContainsString('hendra@mail.id', $payloadJson);
            $this->assertStringContainsString('[NIK DISEMBUNYIKAN]', $payloadJson);
            $this->assertStringContainsString('[NOMOR TELEPON DISEMBUNYIKAN]', $payloadJson);
            $this->assertStringContainsString('[EMAIL DISEMBUNYIKAN]', $payloadJson);
        }

        // Pastikan RAG Aula tetap ikut dikirim ke Groq failover
        $groqSystem = $capturedGroq['messages'][0]['content'] ?? '';
        $this->assertStringContainsString('<KNOWLEDGE_CONTEXT>', $groqSystem);
        $this->assertStringContainsStringIgnoringCase('aula', $groqSystem);
    }

    /**
     * TEST 12 — Pesan tanpa PII tetap diteruskan utuh tanpa modifikasi.
     */
    public function test_12_messages_without_pii_pass_through_unchanged(): void
    {
        $cleanMessage = 'Bagaimana prosedur pengajuan SKTM di Kecamatan Cikampek?';
        $capturedPayload = null;

        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) use (&$capturedPayload) {
                $capturedPayload = $request->data();

                return Http::response([
                    'candidates' => [[
                        'finishReason' => 'STOP',
                        'content' => ['parts' => [['text' => 'Silakan siapkan pengantar RT/RW.']]],
                    ]],
                ], 200);
            },
        ]);

        $service = app(PiiMaskingService::class);
        $maskResult = $service->maskText($cleanMessage);
        $this->assertSame($cleanMessage, $maskResult['text']);
        $this->assertSame(0, $maskResult['count']);
        $this->assertSame([], $maskResult['categories']);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => $cleanMessage,
        ]);

        $response->assertStatus(200);
        $outboundText = $capturedPayload['contents'][0]['parts'][0]['text'] ?? '';
        $this->assertSame($cleanMessage, $outboundText);
    }

    /**
     * TEST 13 — Masking bersifat idempoten saat dijalankan berulang atau pada teks yang sudah dimasking.
     */
    public function test_13_pii_masking_is_strictly_idempotent(): void
    {
        $service = app(PiiMaskingService::class);

        $input = 'Nama: Joko Widodo, NIK: 3215130101900088, No. KK: 3215130202880099, WA: 0812-3344-5566, Email: joko@mail.id';
        $first = $service->maskText($input);
        $second = $service->maskText($first['text']);
        $third = $service->maskText($second['text']);

        $this->assertGreaterThanOrEqual(5, $first['count']);
        $this->assertSame($first['text'], $second['text']);
        $this->assertSame($second['text'], $third['text']);
        $this->assertSame(0, $second['count']);
        $this->assertSame([], $second['categories']);
        $this->assertStringNotContainsString('[[', $third['text']);

        // Lewatkan teks yang sudah dimasking ke router + Gemini
        $capturedPayload = null;
        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) use (&$capturedPayload) {
                $capturedPayload = $request->data();

                return Http::response([
                    'candidates' => [[
                        'finishReason' => 'STOP',
                        'content' => ['parts' => [['text' => 'Diterima.']]],
                    ]],
                ], 200);
            },
        ]);

        $router = app(AIRouterService::class);
        $res = $router->chat($first['text']);
        $this->assertTrue($res['success']);
        $this->assertSame($first['text'], $capturedPayload['contents'][0]['parts'][0]['text'] ?? '');
    }

    /**
     * TEST 14 — Percakapan lanjutan dengan PII tetap menjaga konteks RAG dan log bebas PII mentah.
     */
    public function test_14_followup_and_rag_continuity_with_pii_and_zero_pii_in_logs(): void
    {
        Log::spy();
        config(['services.gemini.api_key' => 'secret_gemini_key_4d_test']);

        $capturedPayload = null;
        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) use (&$capturedPayload) {
                $capturedPayload = $request->data();

                return Http::response([
                    'candidates' => [[
                        'finishReason' => 'STOP',
                        'content' => ['parts' => [['text' => 'Persyaratan peminjaman aula meliputi surat permohonan resmi.']]],
                    ]],
                ], 200);
            },
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'NIK saya 3215-1301-0190-0019, No. HP 0812-9988-7766, kalau persyaratannya apa?',
            'history' => [
                ['role' => 'user', 'text' => 'Bagaimana cara meminjam aula di Kecamatan Cikampek?'],
                ['role' => 'model', 'text' => 'Peminjaman aula dapat diajukan melalui aplikasi SIPAF.'],
            ],
        ]);

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'reply' => 'Persyaratan peminjaman aula meliputi surat permohonan resmi.',
        ]);

        $this->assertNotNull($capturedPayload);
        $payloadJson = json_encode($capturedPayload);
        $this->assertStringNotContainsString('3215-1301-0190-0019', $payloadJson);
        $this->assertStringNotContainsString('0812-9988-7766', $payloadJson);
        $this->assertStringContainsString('[NIK DISEMBUNYIKAN]', $payloadJson);
        $this->assertStringContainsString('[NOMOR TELEPON DISEMBUNYIKAN]', $payloadJson);
        $this->assertStringContainsStringIgnoringCase('aula', $capturedPayload['system_instruction']['parts'][0]['text'] ?? '');

        // Pastikan log [AI RAG] mencatat intent SIPAF_RAG dan summary MaskedPII (jumlah & kategori), tanpa PII mentah atau API key
        Log::shouldHaveReceived('info')
            ->withArgs(function (string $message): bool {
                return str_contains($message, '[AI RAG]')
                    && str_contains($message, 'Intent: SIPAF_RAG')
                    && str_contains($message, 'MaskedPII: 2 (NIK,PHONE)')
                    && !str_contains($message, '3215-1301-0190-0019')
                    && !str_contains($message, '0812-9988-7766')
                    && !str_contains($message, 'secret_gemini_key_4d_test');
            });
    }
}
