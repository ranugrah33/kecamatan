<?php

namespace Tests\Feature;

use App\Services\AI\AIRouterService;
use App\Services\AI\Exceptions\AIServiceException;
use App\Services\AI\GroqService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SipafGroqFallbackOptimizationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    /**
     * Helper: Instantiate fresh GroqService with configured key/model/max_tokens.
     */
    private function makeGroqService(?int $maxTokens = null): GroqService
    {
        config(['services.groq.api_key' => 'gsk_test_secret_key_do_not_leak']);
        if ($maxTokens !== null) {
            config(['services.groq.max_tokens' => $maxTokens]);
        }

        return new GroqService();
    }

    /**
     * TEST 1 — Respons Groq normal, batas max_tokens dari konfigurasi (default konservatif 1024),
     * dan tidak mengirim parameter reasoning yang belum terbukti didukung model.
     */
    public function test_groq_normal_response_and_configurable_max_tokens_without_unverified_reasoning_params(): void
    {
        $capturedPayloads = [];

        Http::fake([
            'api.groq.com/*' => function ($request) use (&$capturedPayloads) {
                $capturedPayloads[] = $request->data();

                return Http::response([
                    'choices' => [[
                        'finish_reason' => 'stop',
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Inflasi adalah kenaikan harga barang dan jasa secara umum.',
                        ],
                    ]],
                    'usage' => [
                        'prompt_tokens' => 420,
                        'completion_tokens' => 95,
                        'total_tokens' => 515,
                    ],
                ], 200);
            },
        ]);

        // Default konservatif di config/services.php = 1024
        $this->assertSame(1024, (int) config('services.groq.max_tokens'));

        $replyDefault = $this->makeGroqService()->generateResponse('Apa itu inflasi?', [], 'System prompt');
        $this->assertSame('Inflasi adalah kenaikan harga barang dan jasa secara umum.', $replyDefault);
        $this->assertSame(1024, $capturedPayloads[0]['max_tokens'] ?? null);
        $this->assertArrayNotHasKey('reasoning_effort', $capturedPayloads[0]);
        $this->assertArrayNotHasKey('reasoning_format', $capturedPayloads[0]);
        $this->assertArrayNotHasKey('include_reasoning', $capturedPayloads[0]);

        // Dapat dikonfigurasi lewat config('services.groq.max_tokens') dan dibatasi rentang aman [256..4096]
        $this->makeGroqService(1536)->generateResponse('Jelaskan deflasi.', [], 'System prompt');
        $this->assertSame(1536, $capturedPayloads[1]['max_tokens'] ?? null);

        $this->makeGroqService(99999)->generateResponse('Uji batas atas.', [], 'System prompt');
        $this->assertSame(4096, $capturedPayloads[2]['max_tokens'] ?? null);

        $this->makeGroqService(64)->generateResponse('Uji batas bawah.', [], 'System prompt');
        $this->assertSame(256, $capturedPayloads[3]['max_tokens'] ?? null);
    }

    /**
     * TEST 2 — Respons dengan finish_reason = stop membersihkan <think> dengan benar,
     * mencatat usage tokens aman, dan tidak membocorkan API key atau data pribadi.
     */
    public function test_groq_finish_reason_stop_logs_usage_safely_without_leaking_secrets_or_pii(): void
    {
        Log::spy();

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [[
                    'finish_reason' => 'stop',
                    'message' => [
                        'role' => 'assistant',
                        'content' => "<think>Susun jawaban singkat dan sopan.</think>\n\nPeminjaman aula dapat diajukan secara online melalui SIPAF.",
                    ],
                ]],
                'usage' => [
                    'prompt_tokens' => 540,
                    'completion_tokens' => 160,
                    'total_tokens' => 700,
                ],
            ], 200),
        ]);

        $reply = $this->makeGroqService()->generateResponse(
            'NIK saya 3215130101010001, bagaimana cara pinjam aula?',
            [],
            'System prompt rahasia'
        );

        $this->assertSame('Peminjaman aula dapat diajukan secara online melalui SIPAF.', $reply);
        $this->assertStringNotContainsString('<think>', $reply);
        $this->assertStringNotContainsString('mencapai batas maksimum panjang respons AI', $reply);

        Log::shouldHaveReceived('info')
            ->withArgs(function (string $message): bool {
                return str_contains($message, '[GroqService] Response completed')
                    && str_contains($message, 'FinishReason: stop')
                    && str_contains($message, 'PromptTokens: 540')
                    && str_contains($message, 'CompletionTokens: 160')
                    && str_contains($message, 'TotalTokens: 700')
                    && str_contains($message, 'ReasoningStripped: yes')
                    && !str_contains($message, 'gsk_test_secret_key_do_not_leak')
                    && !str_contains($message, '3215130101010001')
                    && !str_contains($message, 'Peminjaman aula dapat diajukan');
            })
            ->once();
    }

    /**
     * TEST 3 — Respons dengan finish_reason = length tidak dianggap lengkap, tidak auto-retry berulang,
     * mencatat warning usage tokens, dan menyertakan catatan pemotongan yang jelas.
     */
    public function test_groq_finish_reason_length_marks_truncation_logs_warning_and_does_not_retry(): void
    {
        $calls = 0;
        Log::spy();

        Http::fake([
            'api.groq.com/*' => function () use (&$calls) {
                $calls++;

                return Http::response([
                    'choices' => [[
                        'finish_reason' => 'length',
                        'message' => [
                            'role' => 'assistant',
                            'content' => '<think>Analisis panjang...</think>Inflasi terjadi ketika permintaan agregat meningkat melebihi kapasitas',
                        ],
                    ]],
                    'usage' => [
                        'prompt_tokens' => 620,
                        'completion_tokens' => 1024,
                        'total_tokens' => 1644,
                    ],
                ], 200);
            },
        ]);

        $reply = $this->makeGroqService()->generateResponse('Jelaskan inflasi secara lengkap.', [], 'System prompt');

        $this->assertSame(1, $calls, 'finish_reason=length tidak boleh memicu pengulangan request otomatis.');
        $this->assertStringContainsString('Inflasi terjadi ketika permintaan agregat meningkat melebihi kapasitas', $reply);
        $this->assertStringContainsString('mencapai batas maksimum panjang respons AI', $reply);
        $this->assertStringNotContainsString('<think>', $reply);

        Log::shouldHaveReceived('warning')
            ->withArgs(function (string $message): bool {
                return str_contains($message, 'FinishReason: length')
                    && str_contains($message, 'PromptTokens: 620')
                    && str_contains($message, 'CompletionTokens: 1024')
                    && str_contains($message, 'TotalTokens: 1644');
            })
            ->once();
    }

    /**
     * TEST 4 — Respons kosong dari Groq ditangani dengan exception terkendali dan warning log.
     */
    public function test_groq_empty_response_throws_service_exception_and_logs_warning(): void
    {
        Log::spy();

        Http::fake([
            'api.groq.com/*' => Http::sequence()
                ->push([
                    'choices' => [[
                        'finish_reason' => 'stop',
                        'message' => ['role' => 'assistant', 'content' => '   '],
                    ]],
                ], 200)
                ->push([
                    'choices' => [[
                        'finish_reason' => 'content_filter',
                        'message' => ['role' => 'assistant', 'content' => null],
                    ]],
                ], 200),
        ]);

        $service = $this->makeGroqService();

        try {
            $service->generateResponse('Tes kosong 1', [], 'System prompt');
            $this->fail('Seharusnya melempar AIServiceException saat content hanya spasi.');
        } catch (AIServiceException $e) {
            $this->assertSame('groq', $e->getProvider());
            $this->assertSame(500, $e->getStatusCode());
            $this->assertStringContainsString('empty response', $e->getMessage());
        }

        try {
            $service->generateResponse('Tes kosong 2', [], 'System prompt');
            $this->fail('Seharusnya melempar AIServiceException saat content null.');
        } catch (AIServiceException $e) {
            $this->assertSame('groq', $e->getProvider());
            $this->assertStringContainsString('content_filter', $e->getMessage());
        }

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $msg) => str_contains($msg, 'Empty response received from Groq'))
            ->twice();
    }

    /**
     * TEST 5 — Respons yang hanya berisi <think>...</think> (atau terpotong di dalam <think>)
     * tidak menghasilkan jawaban kosong diam-diam, tetapi ditangani eksplisit hingga level endpoint.
     */
    public function test_groq_reasoning_only_think_block_throws_and_triggers_graceful_fallback(): void
    {
        Log::spy();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'Service unavailable'], 503),
            'api.groq.com/*' => Http::sequence()
                ->push([
                    'choices' => [[
                        'finish_reason' => 'stop',
                        'message' => [
                            'role' => 'assistant',
                            'content' => '<think>Saya harus memeriksa data terlebih dahulu tetapi belum menulis jawaban.</think>',
                        ],
                    ]],
                    'usage' => [
                        'prompt_tokens' => 450,
                        'completion_tokens' => 120,
                        'total_tokens' => 570,
                    ],
                ], 200)
                ->push([
                    'choices' => [[
                        'finish_reason' => 'length',
                        'message' => [
                            'role' => 'assistant',
                            'content' => '<think>Berpikir sangat panjang hingga menyentuh batas max_tokens tanpa output akhir',
                        ],
                    ]],
                    'usage' => [
                        'prompt_tokens' => 450,
                        'completion_tokens' => 1024,
                        'total_tokens' => 1474,
                    ],
                ], 200),
        ]);

        // Uji langsung GroqService pada kasus <think>...</think> tertutup tanpa jawaban
        try {
            $this->makeGroqService()->generateResponse('Pertanyaan 1', [], 'System prompt');
            $this->fail('Seharusnya melempar AIServiceException saat isi hanya <think>...</think>.');
        } catch (AIServiceException $e) {
            $this->assertSame('groq', $e->getProvider());
            $this->assertStringContainsString('reasoning-only', $e->getMessage());
        }

        // Uji integrasi penuh endpoint chatbot saat Gemini 503 dan Groq hanya mengembalikan <think> terpotong (length)
        config(['services.groq.api_key' => 'gsk_test_secret_key_do_not_leak']);
        app()->forgetInstance(GroqService::class);
        app()->forgetInstance(AIRouterService::class);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Bagaimana cara meminjam aula?',
        ]);

        $response->assertStatus(200)->assertJson(['success' => false]);
        $message = (string) $response->json('message');
        $this->assertNotSame('', trim($message));
        $this->assertStringContainsString('kendala teknis', $message);
        $this->assertStringNotContainsString('<think>', $message);

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $msg) => str_contains($msg, 'Reasoning-only response yielded empty visible content')
                && str_contains($msg, 'ReasoningStripped: yes'))
            ->twice();
    }

    /**
     * TEST 6 — Respons Groq tanpa metadata `usage` tetap berhasil diproses dan tercatat aman sebagai `Usage: unavailable`.
     */
    public function test_groq_response_without_usage_metadata_succeeds_safely(): void
    {
        Log::spy();

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [[
                    'finish_reason' => 'stop',
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Halo! Ada yang bisa saya bantu hari ini?',
                    ],
                ]],
            ], 200),
        ]);

        $reply = $this->makeGroqService()->generateResponse('Hai', [], 'System prompt');
        $this->assertSame('Halo! Ada yang bisa saya bantu hari ini?', $reply);

        Log::shouldHaveReceived('info')
            ->withArgs(fn (string $msg) => str_contains($msg, '[GroqService] Response completed')
                && str_contains($msg, 'Usage: unavailable'))
            ->once();
    }

    /**
     * TEST 7 — Groq HTTP 429 ditangani bersih tanpa retry loop dan tanpa membocorkan API key.
     */
    public function test_groq_http_429_throws_rate_limit_exception_cleanly(): void
    {
        $calls = 0;

        Http::fake([
            'api.groq.com/*' => function () use (&$calls) {
                $calls++;

                return Http::response([
                    'error' => [
                        'message' => 'Rate limit reached for model `qwen/qwen3.8-27b` OTPM: Limit 1000',
                        'type' => 'tokens',
                    ],
                ], 429);
            },
        ]);

        try {
            $this->makeGroqService()->generateResponse('Apa itu inflasi?', [], 'System prompt');
            $this->fail('Seharusnya melempar AIServiceException HTTP 429.');
        } catch (AIServiceException $e) {
            $this->assertSame('groq', $e->getProvider());
            $this->assertSame(429, $e->getStatusCode());
            $this->assertStringContainsString('HTTP 429', $e->getMessage());
        }

        $this->assertSame(1, $calls, 'HTTP 429 tidak boleh diulang-ulang pada Groq.');
    }

    /**
     * TEST 8 — Groq HTTP 500 melempar AIServiceException status 500 tanpa crash.
     */
    public function test_groq_http_500_throws_server_exception_cleanly(): void
    {
        Http::fake([
            'api.groq.com/*' => Http::response([
                'error' => ['message' => 'Internal Server Error'],
            ], 500),
        ]);

        try {
            $this->makeGroqService()->generateResponse('Apa kabar?', [], 'System prompt');
            $this->fail('Seharusnya melempar AIServiceException HTTP 500.');
        } catch (AIServiceException $e) {
            $this->assertSame('groq', $e->getProvider());
            $this->assertSame(500, $e->getStatusCode());
            $this->assertStringContainsString('HTTP 500', $e->getMessage());
        }
    }

    /**
     * TEST 9 — Timeout (ConnectionException) pada Groq melempar AIServiceException 408
     * dan menghasilkan fallback darurat yang aman saat Gemini juga gagal.
     */
    public function test_groq_timeout_throws_408_and_router_returns_graceful_fallback(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'Unavailable'], 503),
            'api.groq.com/*' => function () {
                throw new ConnectionException('cURL error 28: Operation timed out after 20001 milliseconds');
            },
        ]);

        try {
            $this->makeGroqService()->generateResponse('Apa itu inflasi?', [], 'System prompt');
            $this->fail('Seharusnya melempar AIServiceException timeout 408.');
        } catch (AIServiceException $e) {
            $this->assertSame('groq', $e->getProvider());
            $this->assertSame(408, $e->getStatusCode());
            $this->assertStringContainsString('timeout', strtolower($e->getMessage()));
        }

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Apa itu inflasi?',
        ]);

        $response->assertStatus(200)->assertJson(['success' => false]);
        $this->assertStringContainsString('kendala teknis', (string) $response->json('message'));
        $this->assertStringNotContainsString('gsk_', (string) $response->getContent());
    }

    /**
     * TEST 10 — Integrasi failover dari Gemini ke Groq menjaga alur utama Gemini tetap utuh
     * dan meneruskan finish_reason stop maupun length dari Groq dengan akurat.
     */
    public function test_gemini_primary_remains_intact_and_failover_to_groq_handles_stop_and_length(): void
    {
        $geminiPayloads = [];
        $groqPayloads = [];
        $geminiAttempt = 0;

        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) use (&$geminiAttempt, &$geminiPayloads) {
                $geminiAttempt++;
                $geminiPayloads[] = $request->data();

                if ($geminiAttempt === 1) {
                    return Http::response([
                        'candidates' => [[
                            'finishReason' => 'STOP',
                            'content' => ['parts' => [['text' => 'Jawaban utama langsung dari Gemini.']]],
                        ]],
                    ], 200);
                }

                return $geminiAttempt === 2
                    ? Http::response(['error' => ['code' => 429, 'status' => 'RESOURCE_EXHAUSTED']], 429)
                    : Http::response(['error' => 'Temporary 503'], 503);
            },
            'api.groq.com/*' => function ($request) use (&$groqPayloads) {
                $groqPayloads[] = $request->data();

                if (count($groqPayloads) === 1) {
                    return Http::response([
                        'choices' => [[
                            'finish_reason' => 'stop',
                            'message' => ['content' => '<think>proses</think>Jawaban lengkap dari Groq fallback.'],
                        ]],
                        'usage' => ['prompt_tokens' => 400, 'completion_tokens' => 70, 'total_tokens' => 470],
                    ], 200);
                }

                return Http::response([
                    'choices' => [[
                        'finish_reason' => 'length',
                        'message' => ['content' => 'Penjelasan sebagian dari Groq sebelum menyentuh batas token'],
                    ]],
                    'usage' => ['prompt_tokens' => 400, 'completion_tokens' => 1024, 'total_tokens' => 1424],
                ], 200);
            },
        ]);

        // 1. Gemini normal -> alur utama tidak berubah, Groq tidak dipanggil
        $r1 = $this->postJson(route('chatbot.chat'), ['message' => 'Apa itu inflasi?']);
        $r1->assertStatus(200)->assertJson([
            'success' => true,
            'reply' => 'Jawaban utama langsung dari Gemini.',
        ]);
        $this->assertSame(2048, $geminiPayloads[0]['generationConfig']['maxOutputTokens'] ?? null);
        $this->assertCount(0, $groqPayloads);

        // 2. Gemini 429 -> fallback ke Groq (finish_reason=stop)
        $r2 = $this->postJson(route('chatbot.chat'), ['message' => 'Apa itu deflasi?']);
        $r2->assertStatus(200)->assertJson([
            'success' => true,
            'reply' => 'Jawaban lengkap dari Groq fallback.',
        ]);
        $this->assertCount(1, $groqPayloads);
        $this->assertSame(1024, $groqPayloads[0]['max_tokens'] ?? null);

        // 3. Gemini 503 -> fallback ke Groq (finish_reason=length) tidak dianggap jawaban lengkap
        $r3 = $this->postJson(route('chatbot.chat'), ['message' => 'Jelaskan perbedaan inflasi dan deflasi.']);
        $r3->assertStatus(200)->assertJson(['success' => true]);
        $reply3 = (string) $r3->json('reply');
        $this->assertStringContainsString('Penjelasan sebagian dari Groq sebelum menyentuh batas token', $reply3);
        $this->assertStringContainsString('mencapai batas maksimum panjang respons AI', $reply3);
    }
}

