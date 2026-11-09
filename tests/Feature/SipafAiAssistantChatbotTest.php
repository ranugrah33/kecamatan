<?php

namespace Tests\Feature;

use App\Models\AiKnowledge;
use App\Services\AI\AIRouterService;
use App\Services\AI\GeminiService;
use App\Services\AI\GroqService;
use App\Services\AI\KnowledgeBaseService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SipafAiAssistantChatbotTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable throttle middleware so test suite runs smoothly without hitting 15 req/min rate limit
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    /**
     * TEST 1 — Gemini Normal
     * Simulasikan Gemini HTTP 200, jawaban Gemini diterima, Groq tidak dipanggil.
     */
    public function test_gemini_normal_success_does_not_call_groq(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Halo! Ini adalah jawaban resmi dari Google Gemini.']
                            ]
                        ]
                    ]
                ]
            ], 200),
            'api.groq.com/*' => Http::response([
                'error' => 'Groq should not be invoked when Gemini succeeds'
            ], 500),
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Bagaimana cara meminjam aula kecamatan?',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'reply' => 'Halo! Ini adalah jawaban resmi dari Google Gemini.',
            ]);

        // Verifikasi Gemini dipanggil, Groq TIDAK dipanggil
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'api.groq.com');
        });
    }

    /**
     * TEST 2 — Gemini Rate Limit (HTTP 429)
     * Simulasikan Gemini 429, router otomatis fallback ke Groq dan menampilkan jawaban Groq.
     */
    public function test_gemini_rate_limit_429_automatically_fails_over_to_groq(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => [
                    'code' => 429,
                    'message' => 'Resource has been exhausted (e.g. check quota).',
                    'status' => 'RESOURCE_EXHAUSTED',
                ]
            ], 429),
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Halo! Ini adalah jawaban failover otomatis dari Groq AI.',
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Berapa luas wilayah Kecamatan Cikampek?',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'reply' => 'Halo! Ini adalah jawaban failover otomatis dari Groq AI.',
            ]);

        // Verifikasi Gemini gagal, lalu Groq dipanggil
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.groq.com');
        });
    }

    /**
     * TEST 3 — Gemini Server Error (HTTP 500 / 503)
     * Simulasikan Gemini mengalami gangguan server, router mencoba Groq.
     */
    public function test_gemini_server_error_500_or_503_fails_over_to_groq(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => [
                    'code' => 503,
                    'message' => 'The service is temporarily unavailable.',
                    'status' => 'UNAVAILABLE',
                ]
            ], 503),
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Jawaban dari Groq karena Gemini sedang mengalami 503 server error.',
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Pelayanan apa saja yang ada di Cikampek?',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'reply' => 'Jawaban dari Groq karena Gemini sedang mengalami 503 server error.',
            ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.groq.com');
        });
    }

    /**
     * TEST 4 — Gemini Timeout / Connection Error
     * Simulasikan ConnectionException pada Gemini, Groq berhasil merespons.
     */
    public function test_gemini_timeout_or_connection_error_fails_over_to_groq(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => function () {
                throw new ConnectionException('cURL error 28: Operation timed out after 20000 milliseconds');
            },
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Jawaban Groq setelah Gemini connection timeout.',
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Bagaimana syarat pinjam sound system?',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'reply' => 'Jawaban Groq setelah Gemini connection timeout.',
            ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.groq.com');
        });
    }

    /**
     * TEST 5 — Gemini Kembali Normal (Stateless Recovery)
     * Simulasikan dua request berurutan:
     * Request 1: Gemini gagal (429), Groq mengambil alih.
     * Request 2: Gemini pulih (200), sistem kembali menggunakan Gemini dan Groq tidak dipanggil.
     */
    public function test_gemini_recovery_automatically_resumes_gemini_on_next_request(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push(['error' => 'Rate limit exceeded'], 429) // Request 1: Gemini gagal
                ->push([                                         // Request 2: Gemini pulih
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => 'Halo, Gemini sudah pulih dan aktif kembali!']
                                ]
                            ]
                        ]
                    ]
                ], 200),
            'api.groq.com/*' => Http::sequence()
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => 'Jawaban sementara dari Groq saat Gemini down.',
                            ]
                        ]
                    ]
                ], 200)
                ->push(['error' => 'Groq tidak boleh dipanggil pada request kedua!'], 500),
        ]);

        // Request 1: Gemini 429 -> Groq menangani
        $response1 = $this->postJson(route('chatbot.chat'), [
            'message' => 'Pertanyaan 1 saat Gemini bermasalah',
        ]);

        $response1->assertStatus(200)
            ->assertJson([
                'success' => true,
                'reply' => 'Jawaban sementara dari Groq saat Gemini down.',
            ]);

        // Request 2: Gemini pulih -> Gemini langsung dipakai kembali secara otomatis
        $response2 = $this->postJson(route('chatbot.chat'), [
            'message' => 'Pertanyaan 2 setelah Gemini normal kembali',
        ]);

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'reply' => 'Halo, Gemini sudah pulih dan aktif kembali!',
            ]);

        // Gemini dipanggil 2 kali
        Http::assertSentCount(3); // 2 kali Gemini + 1 kali Groq pada request 1
    }

    /**
     * TEST 6 — Kedua Provider Gagal
     * Simulasikan Gemini dan Groq sama-sama mengalami gangguan.
     * Tampilkan pesan error ramah, tanpa membocorkan API key atau data teknis internal.
     */
    public function test_both_providers_fail_returns_friendly_error_message(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'Gemini unavailable'], 500),
            'api.groq.com/*' => Http::response(['error' => 'Groq unavailable'], 500),
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Informasi bantuan sosial',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data['message']);
        $this->assertStringContainsString('petugas', $data['message']);

        // Pastikan tidak ada credential, API key, atau exception dump di response publik
        $rawContent = $response->getContent();
        $this->assertStringNotContainsString('AIzaSy', $rawContent);
        $this->assertStringNotContainsString('gsk_', $rawContent);
        $this->assertStringNotContainsString('Exception', $rawContent);
        $this->assertStringNotContainsString('Stack trace', $rawContent);
    }

    /**
     * TEST 7A — Knowledge Base dan Conversation History pada Gemini
     * Pastikan konteks database knowledge base dan riwayat percakapan diteruskan ke Gemini.
     * Pastikan role 'model' dan pesan error ('isError: true') difilter dari context.
     */
    public function test_gemini_receives_knowledge_base_and_preserves_history(): void
    {
        AiKnowledge::create([
            'category' => 'Uji Khusus',
            'title' => 'Topik Verifikasi Unit Testing Dual AI Gemini',
            'question' => 'Apakah ini uji knowledge?',
            'answer' => 'KODE-RAHASIA-KNOWLEDGE-GEMINI-888',
            'keywords' => 'uji, knowledge',
            'priority' => 999,
            'is_active' => true,
        ]);

        $historyPayload = [
            ['role' => 'user', 'text' => 'Halo bot', 'isError' => false],
            ['role' => 'model', 'text' => 'Halo masyarakat Cikampek', 'isError' => false],
            ['role' => 'model', 'text' => 'Pesan error sebelumnya tidak boleh ikut', 'isError' => true],
        ];

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Jawaban Gemini']]]]
                ]
            ], 200),
        ]);

        $this->postJson(route('chatbot.chat'), [
            'message' => 'Cek knowledge base pada Gemini',
            'history' => $historyPayload,
        ]);

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), 'generativelanguage.googleapis.com')) {
                return false;
            }

            $data = $request->data();
            $systemInstruction = $data['system_instruction']['parts'][0]['text'] ?? '';
            $contents = $data['contents'] ?? [];

            $hasKnowledge = str_contains($systemInstruction, 'KODE-RAHASIA-KNOWLEDGE-GEMINI-888');
            $hasHistoryUser = isset($contents[0]) && $contents[0]['role'] === 'user' && $contents[0]['parts'][0]['text'] === 'Halo bot';
            $hasHistoryModel = isset($contents[1]) && $contents[1]['role'] === 'model' && $contents[1]['parts'][0]['text'] === 'Halo masyarakat Cikampek';

            $noErrorMessage = true;
            foreach ($contents as $c) {
                if (($c['parts'][0]['text'] ?? '') === 'Pesan error sebelumnya tidak boleh ikut') {
                    $noErrorMessage = false;
                }
            }

            return $hasKnowledge && $hasHistoryUser && $hasHistoryModel && $noErrorMessage;
        });
    }

    /**
     * TEST 7B — Knowledge Base dan Conversation History pada Groq (Fallback)
     * Pastikan konteks yang sama diteruskan ke Groq saat fallback.
     * Pastikan role 'model' dipetakan menjadi 'assistant' pada format Groq OpenAI.
     * Pastikan pesan error ('isError: true') difilter dari context.
     */
    public function test_groq_fallback_receives_same_knowledge_base_and_mapped_history(): void
    {
        AiKnowledge::create([
            'category' => 'Uji Khusus',
            'title' => 'Topik Verifikasi Unit Testing Dual AI Groq',
            'question' => 'Apakah ini uji knowledge?',
            'answer' => 'KODE-RAHASIA-KNOWLEDGE-GROQ-777',
            'keywords' => 'uji, knowledge',
            'priority' => 999,
            'is_active' => true,
        ]);

        $historyPayload = [
            ['role' => 'user', 'text' => 'Halo bot', 'isError' => false],
            ['role' => 'model', 'text' => 'Halo masyarakat Cikampek', 'isError' => false],
            ['role' => 'model', 'text' => 'Pesan error sebelumnya tidak boleh ikut', 'isError' => true],
        ];

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => '429 Rate limit'], 429),
            'api.groq.com/*' => Http::response([
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => 'Jawaban Groq']]
                ]
            ], 200),
        ]);

        $this->postJson(route('chatbot.chat'), [
            'message' => 'Cek knowledge base pada Groq',
            'history' => $historyPayload,
        ]);

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), 'api.groq.com')) {
                return false;
            }

            $data = $request->data();
            $messages = $data['messages'] ?? [];

            $systemMsg = $messages[0]['content'] ?? '';
            $hasKnowledge = str_contains($systemMsg, 'KODE-RAHASIA-KNOWLEDGE-GROQ-777');
            $hasHistoryUser = isset($messages[1]) && $messages[1]['role'] === 'user' && $messages[1]['content'] === 'Halo bot';
            $hasHistoryAssistant = isset($messages[2]) && $messages[2]['role'] === 'assistant' && $messages[2]['content'] === 'Halo masyarakat Cikampek';

            $noErrorMessage = true;
            foreach ($messages as $m) {
                if (($m['content'] ?? '') === 'Pesan error sebelumnya tidak boleh ikut') {
                    $noErrorMessage = false;
                }
            }

            return $hasKnowledge && $hasHistoryUser && $hasHistoryAssistant && $noErrorMessage;
        });
    }

    /**
     * TEST TAMBAHAN 1 — Missing Gemini API Key Automatically Falls Over to Groq
     * Jika konfigurasi GEMINI_API_KEY kosong, sistem otomatis mengalihkan ke Groq.
     */
    public function test_missing_gemini_api_key_falls_over_to_groq(): void
    {
        config(['services.gemini.api_key' => '']);

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => 'Jawaban Groq saat API key Gemini kosong.']]
                ]
            ], 200),
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Halo apa kabar?',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'reply' => 'Jawaban Groq saat API key Gemini kosong.',
            ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.groq.com');
        });

        // Gemini tidak melakukan HTTP call karena key kosong
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });
    }

    /**
     * TEST TAMBAHAN 2 — Invalid Gemini API Key (HTTP 401 / 403) Falls Over to Groq
     */
    public function test_invalid_gemini_api_key_falls_over_to_groq(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => [
                    'code' => 403,
                    'message' => 'The provided API key is invalid or unauthorized.',
                    'status' => 'PERMISSION_DENIED',
                ]
            ], 403),
            'api.groq.com/*' => Http::response([
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => 'Jawaban Groq saat API key Gemini 403.']]
                ]
            ], 200),
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Halo tes auth failure',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'reply' => 'Jawaban Groq saat API key Gemini 403.',
            ]);
    }

    /**
     * TEST TAMBAHAN 3 — Request Input Validation
     * Memastikan pesan kosong atau melebihi 500 karakter ditolak validasi Laravel tanpa memanggil API.
     */
    public function test_request_validation_fails_without_calling_any_ai_provider(): void
    {
        Http::fake();

        // 1. Pesan kosong
        $responseEmpty = $this->postJson(route('chatbot.chat'), [
            'message' => '',
        ]);
        $responseEmpty->assertStatus(422)
            ->assertJsonValidationErrors(['message']);

        // 2. Pesan melebihi 500 karakter
        $responseTooLong = $this->postJson(route('chatbot.chat'), [
            'message' => str_repeat('a', 501),
        ]);
        $responseTooLong->assertStatus(422)
            ->assertJsonValidationErrors(['message']);

        // Tidak ada provider yang boleh dipanggil saat validasi gagal
        Http::assertNothingSent();
    }

    /**
     * TEST TAMBAHAN 4 — Non-failover Client Error Does Not Fall Over to Groq
     * Kesalahan format request (HTTP 400 tanpa quota) tidak dialihkan ke Groq.
     */
    public function test_non_failover_client_error_does_not_trigger_groq(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => [
                    'code' => 400,
                    'message' => 'Invalid JSON payload structure.',
                    'status' => 'INVALID_ARGUMENT',
                ]
            ], 400),
            'api.groq.com/*' => Http::response([
                'error' => 'Groq should NOT be called for unrecoverable 400 bad request'
            ], 500),
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Pertanyaan yang memicu format error',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });

        // Groq TIDAK boleh dipanggil karena error 400 bukan error recoverable
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'api.groq.com');
        });
    }

    /**
     * TEST TAMBAHAN 5 — API Keys Never Leaked to Frontend
     * Memastikan string API key Gemini maupun Groq tidak pernah bocor ke respons pengguna.
     */
    public function test_api_keys_are_never_leaked_in_any_response(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Jawaban normal.']]]]
                ]
            ], 200),
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Tes kebocoran key',
        ]);

        $content = $response->getContent();
        $geminiKey = config('services.gemini.api_key');
        $groqKey = config('services.groq.api_key');

        if (!empty($geminiKey)) {
            $this->assertStringNotContainsString($geminiKey, $content);
        }
        if (!empty($groqKey)) {
            $this->assertStringNotContainsString($groqKey, $content);
        }
    }
}
