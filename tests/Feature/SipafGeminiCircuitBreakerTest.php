<?php

namespace Tests\Feature;

use App\Services\AI\AIRouterService;
use App\Services\AI\GeminiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SipafGeminiCircuitBreakerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * TEST 1 — Gemini berhasil normal tanpa mengaktifkan cooldown circuit breaker.
     */
    public function test_gemini_success_does_not_activate_cooldown(): void
    {
        $service = app(GeminiService::class);
        $cacheKey = $service->getCircuitBreakerCacheKey();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'finishReason' => 'STOP',
                    'content' => ['parts' => [['text' => 'Inflasi adalah kenaikan harga secara umum.']]],
                ]],
            ], 200),
            'api.groq.com/*' => Http::response(['error' => 'Groq tidak boleh dipanggil'], 500),
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Apa itu inflasi?',
        ]);

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'reply' => 'Inflasi adalah kenaikan harga secara umum.',
        ]);

        $this->assertSame('ai:circuit_breaker:gemini:gemini-3.6-flash', $cacheKey);
        $this->assertNull(Cache::get($cacheKey));

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'api.groq.com'));
    }

    /**
     * TEST 2 — Gemini HTTP 429 mengaktifkan cooldown default 45 detik dan evaluasi Retry-After yang aman.
     */
    public function test_gemini_http_429_activates_default_cooldown_and_handles_retry_after_safely(): void
    {
        Log::spy();
        Carbon::setTestNow(Carbon::create(2026, 11, 11, 14, 0, 0, 'Asia/Jakarta'));

        config(['services.gemini.api_key' => 'gemini_secret_key_do_not_log']);
        $service = new GeminiService();
        $cacheKey = $service->getCircuitBreakerCacheKey();

        // A. Tanpa Retry-After -> memakai durasi default 45 detik
        $this->assertSame(45, (int) config('services.gemini.cooldown_seconds'));

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push(['error' => 'Rate limited'], 429)
                ->push(['error' => '429'], 429, ['Retry-After' => '75'])
                ->push(['error' => '429'], 429, ['Retry-After' => '9999'])
                ->push(['error' => '429'], 429, ['Retry-After' => '-10'])
                ->push(['error' => '429'], 429, ['Retry-After' => Carbon::now()->addSeconds(60)->toRfc7231String()]),
            'api.groq.com/*' => Http::response([
                'choices' => [['finish_reason' => 'stop', 'message' => ['content' => 'Fallback Groq aktif.']]],
            ], 200),
        ]);

        $r1 = $this->postJson(route('chatbot.chat'), ['message' => 'NIK saya 3215130101010001, apa itu inflasi?']);
        $r1->assertStatus(200)->assertJson(['success' => true, 'reply' => 'Fallback Groq aktif.']);

        $stateDefault = Cache::get($cacheKey);
        $this->assertIsArray($stateDefault);
        $this->assertSame('open', $stateDefault['state']);
        $this->assertSame(45, $stateDefault['duration_seconds']);
        $this->assertSame('default', $stateDefault['source']);

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $msg) => str_contains($msg, 'Gemini rate limit hit (HTTP 429)')
                && !str_contains($msg, 'gemini_secret_key_do_not_log')
                && !str_contains($msg, '3215130101010001'))
            ->once();
        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $msg) => str_contains($msg, 'Circuit breaker cooldown activated')
                && str_contains($msg, 'DurationSeconds: 45')
                && str_contains($msg, 'Source: default'))
            ->once();

        // B. Retry-After valid (75s), Retry-After ekstrem (9999 -> capped 180s), dan Retry-After invalid -> 45s
        Cache::forget($cacheKey);

        try {
            (new GeminiService())->generateResponse('Uji A', [], 'Prompt');
        } catch (\Throwable) {
        }
        $this->assertSame(75, Cache::get($cacheKey)['duration_seconds']);
        $this->assertSame('retry_after', Cache::get($cacheKey)['source']);

        Cache::forget($cacheKey);
        try {
            (new GeminiService())->generateResponse('Uji B', [], 'Prompt');
        } catch (\Throwable) {
        }
        $this->assertSame(180, Cache::get($cacheKey)['duration_seconds']);
        $this->assertSame('retry_after_capped', Cache::get($cacheKey)['source']);

        Cache::forget($cacheKey);
        try {
            (new GeminiService())->generateResponse('Uji C', [], 'Prompt');
        } catch (\Throwable) {
        }
        $this->assertSame(45, Cache::get($cacheKey)['duration_seconds']);
        $this->assertSame('default', Cache::get($cacheKey)['source']);

        Cache::forget($cacheKey);
        try {
            (new GeminiService())->generateResponse('Uji D', [], 'Prompt');
        } catch (\Throwable) {
        }
        $this->assertSame(60, Cache::get($cacheKey)['duration_seconds']);
        $this->assertSame('retry_after', Cache::get($cacheKey)['source']);
    }

    /**
     * TEST 3 & 4 — Request berikutnya selama cooldown langsung ke Groq dan Gemini tidak dipanggil ulang.
     */
    public function test_requests_during_cooldown_skip_gemini_and_route_directly_to_groq(): void
    {
        Log::spy();
        Carbon::setTestNow(Carbon::create(2026, 11, 11, 14, 1, 0, 'Asia/Jakarta'));

        $geminiCalls = 0;
        $groqCalls = 0;

        Http::fake([
            'generativelanguage.googleapis.com/*' => function () use (&$geminiCalls) {
                $geminiCalls++;
                return Http::response(['error' => '429 Quota exceeded'], 429);
            },
            'api.groq.com/*' => function () use (&$groqCalls) {
                $groqCalls++;

                return Http::response([
                    'choices' => [[
                        'finish_reason' => 'stop',
                        'message' => ['content' => "<think>internal</think>Respon Groq ke-{$groqCalls}."],
                    ]],
                    'usage' => ['prompt_tokens' => 300, 'completion_tokens' => 50, 'total_tokens' => 350],
                ], 200);
            },
        ]);

        // Request 1 terkena 429 -> cooldown aktif
        $r1 = $this->postJson(route('chatbot.chat'), ['message' => 'Apa itu inflasi?']);
        $r1->assertStatus(200)->assertJson(['success' => true, 'reply' => 'Respon Groq ke-1.']);

        // Request 2 & 3 masih dalam jendela 45 detik (t+10s dan t+30s) -> Gemini tidak boleh dipanggil lagi
        $this->travel(10)->seconds();
        $r2 = $this->postJson(route('chatbot.chat'), ['message' => 'Apa itu deflasi?']);
        $r2->assertStatus(200)->assertJson(['success' => true, 'reply' => 'Respon Groq ke-2.']);

        $this->travel(20)->seconds();
        $r3 = $this->postJson(route('chatbot.chat'), ['message' => 'Bagaimana cara meminjam aula?']);
        $r3->assertStatus(200)->assertJson(['success' => true, 'reply' => 'Respon Groq ke-3.']);

        $this->assertSame(1, $geminiCalls, 'Gemini tidak boleh dipanggil lagi selama masa cooldown 45 detik.');
        $this->assertSame(3, $groqCalls, 'Groq harus menangani seluruh request selama Gemini cooldown.');

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $msg) => str_contains($msg, 'Request skipped — circuit breaker cooldown active'))
            ->twice();
    }

    /**
     * TEST 5 & 6 — Setelah cooldown berakhir Gemini dicoba kembali, pulih, dan menutup circuit breaker.
     */
    public function test_after_cooldown_expires_gemini_is_retried_and_recovers_primary_role(): void
    {
        Log::spy();
        Carbon::setTestNow(Carbon::create(2026, 11, 11, 14, 2, 0, 'Asia/Jakarta'));

        $cacheKey = app(GeminiService::class)->getCircuitBreakerCacheKey();
        $geminiCalls = 0;
        $groqCalls = 0;

        Http::fake([
            'generativelanguage.googleapis.com/*' => function () use (&$geminiCalls) {
                $geminiCalls++;
                if ($geminiCalls === 1) {
                    return Http::response(['error' => 'Rate limit hit'], 429);
                }

                return Http::response([
                    'candidates' => [[
                        'finishReason' => 'STOP',
                        'content' => ['parts' => [['text' => "Jawaban Gemini pulih ke-{$geminiCalls}."]]],
                    ]],
                ], 200);
            },
            'api.groq.com/*' => function () use (&$groqCalls) {
                $groqCalls++;

                return Http::response([
                    'choices' => [['finish_reason' => 'stop', 'message' => ['content' => 'Respon sementara Groq.']]],
                ], 200);
            },
        ]);

        // 1. Trigger 429 -> cooldown 45 detik
        $this->postJson(route('chatbot.chat'), ['message' => 'Pertanyaan 1'])->assertJson(['reply' => 'Respon sementara Groq.']);
        $this->assertSame('open', Cache::get($cacheKey)['state'] ?? null);

        // 2. Maju melewati 45 detik -> cooldown berakhir -> Gemini dicoba kembali dan pulih
        $this->travel(46)->seconds();
        $r2 = $this->postJson(route('chatbot.chat'), ['message' => 'Pertanyaan 2']);
        $r2->assertStatus(200)->assertJson(['success' => true, 'reply' => 'Jawaban Gemini pulih ke-2.']);
        $this->assertNull(Cache::get($cacheKey), 'Cache cooldown harus dibersihkan setelah Gemini pulih.');

        // 3. Request berikutnya tetap memakai Gemini normal tanpa Groq
        $r3 = $this->postJson(route('chatbot.chat'), ['message' => 'Pertanyaan 3']);
        $r3->assertStatus(200)->assertJson(['success' => true, 'reply' => 'Jawaban Gemini pulih ke-3.']);

        $this->assertSame(3, $geminiCalls);
        $this->assertSame(1, $groqCalls);

        Log::shouldHaveReceived('info')
            ->withArgs(fn (string $msg) => str_contains($msg, 'Circuit breaker cooldown expired — retrying Gemini'))
            ->once();
        Log::shouldHaveReceived('info')
            ->withArgs(fn (string $msg) => str_contains($msg, 'Gemini recovered — circuit breaker closed'))
            ->once();
    }

    /**
     * TEST 7 — HTTP 500 tidak mengaktifkan cooldown 429 dan request berikutnya tetap mencoba Gemini.
     */
    public function test_http_500_does_not_activate_429_cooldown(): void
    {
        $cacheKey = app(GeminiService::class)->getCircuitBreakerCacheKey();
        $geminiCalls = 0;

        Http::fake([
            'generativelanguage.googleapis.com/*' => function () use (&$geminiCalls) {
                $geminiCalls++;
                if ($geminiCalls === 1) {
                    return Http::response(['error' => 'Internal Server Error'], 500);
                }

                return Http::response([
                    'candidates' => [[
                        'finishReason' => 'STOP',
                        'content' => ['parts' => [['text' => 'Gemini langsung berhasil setelah 500.']]],
                    ]],
                ], 200);
            },
            'api.groq.com/*' => Http::response([
                'choices' => [['finish_reason' => 'stop', 'message' => ['content' => 'Groq menangani error 500.']]],
            ], 200),
        ]);

        $r1 = $this->postJson(route('chatbot.chat'), ['message' => 'Uji 500']);
        $r1->assertStatus(200)->assertJson(['reply' => 'Groq menangani error 500.']);
        $this->assertNull(Cache::get($cacheKey), 'HTTP 500 tidak boleh mengaktifkan cache cooldown 429.');

        // Langsung pada detik yang sama, Gemini tetap dipanggil ulang tanpa menunggu cooldown
        $r2 = $this->postJson(route('chatbot.chat'), ['message' => 'Uji lanjut']);
        $r2->assertStatus(200)->assertJson(['reply' => 'Gemini langsung berhasil setelah 500.']);
        $this->assertSame(2, $geminiCalls);
    }

    /**
     * TEST 8 — Timeout (ConnectionException) tetap menggunakan fallback tanpa mengaktifkan cooldown 429.
     */
    public function test_timeout_uses_existing_failover_without_activating_429_cooldown(): void
    {
        $cacheKey = app(GeminiService::class)->getCircuitBreakerCacheKey();
        $geminiCalls = 0;

        Http::fake([
            'generativelanguage.googleapis.com/*' => function () use (&$geminiCalls) {
                $geminiCalls++;
                if ($geminiCalls === 1) {
                    throw new ConnectionException('cURL error 28: Operation timed out');
                }

                return Http::response([
                    'candidates' => [[
                        'finishReason' => 'STOP',
                        'content' => ['parts' => [['text' => 'Gemini merespons normal pasca timeout.']]],
                    ]],
                ], 200);
            },
            'api.groq.com/*' => Http::response([
                'choices' => [['finish_reason' => 'stop', 'message' => ['content' => 'Groq fallback timeout.']]],
            ], 200),
        ]);

        $r1 = $this->postJson(route('chatbot.chat'), ['message' => 'Uji timeout']);
        $r1->assertStatus(200)->assertJson(['reply' => 'Groq fallback timeout.']);
        $this->assertNull(Cache::get($cacheKey), 'Timeout tidak boleh mengaktifkan cache cooldown 429.');

        $r2 = $this->postJson(route('chatbot.chat'), ['message' => 'Uji pasca timeout']);
        $r2->assertStatus(200)->assertJson(['reply' => 'Gemini merespons normal pasca timeout.']);
        $this->assertSame(2, $geminiCalls);
    }

    /**
     * TEST 9 — Request bersamaan aman terhadap race condition, menjaga monotonic cooldown,
     * menjaga single recovery probe, serta mengisolasi cache key per model Gemini.
     */
    public function test_concurrent_requests_preserve_monotonic_cooldown_single_probe_and_model_isolation(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 11, 14, 5, 0, 'Asia/Jakarta'));

        config(['services.gemini.api_key' => 'gemini_secret_key']);
        $flashService = new GeminiService();
        $flashKey = $flashService->getCircuitBreakerCacheKey();

        $geminiCallsOnProbe = 0;
        $groqCalls = 0;

        Http::fake([
            'generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash*' => Http::response([
                'candidates' => [[
                    'finishReason' => 'STOP',
                    'content' => ['parts' => [['text' => 'Model lain tetap normal.']]],
                ]],
            ], 200),
            'generativelanguage.googleapis.com/*' => function () use (&$geminiCallsOnProbe) {
                $geminiCallsOnProbe++;
                if ($geminiCallsOnProbe === 1) {
                    return Http::response(['error' => '429'], 429, ['Retry-After' => '90']);
                }

                return Http::response(['error' => 'Should not probe twice simultaneously'], 500);
            },
            'api.groq.com/*' => function () use (&$groqCalls) {
                $groqCalls++;
                return Http::response([
                    'choices' => [['finish_reason' => 'stop', 'message' => ['content' => 'Groq tangani antrean bersamaan.']]],
                ], 200);
            },
        ]);

        // A. Cooldown lebih panjang (Retry-After: 90s) tidak boleh ditimpa menjadi lebih pendek oleh 429 berbarengan (45s)
        try {
            (new GeminiService())->generateResponse('Req A', [], 'Prompt');
        } catch (\Throwable) {
        }
        $expiresAfterReqA = Cache::get($flashKey)['expires_at'];

        // Request B menulis ulang lewat activateCooldown via refleksi/simulasi race 429 tanpa Retry-After (45s)
        $method = new \ReflectionMethod(GeminiService::class, 'activateCooldown');
        $method->setAccessible(true);
        $method->invoke($flashService, new \Illuminate\Http\Client\Response(new \GuzzleHttp\Psr7\Response(429, [], '{}')));

        $this->assertSame(
            $expiresAfterReqA,
            Cache::get($flashKey)['expires_at'],
            '429 susulan dengan durasi lebih singkat (45s) tidak boleh memendekkan cooldown aktif (90s).'
        );

        // B. Model berbeda memiliki cache key terisolasi dan tidak ikut terblokir oleh gemini-3.6-flash
        config(['services.gemini.model' => 'gemini-2.5-flash']);
        $otherModelService = new GeminiService();
        $this->assertSame('ai:circuit_breaker:gemini:gemini-2.5-flash', $otherModelService->getCircuitBreakerCacheKey());
        $this->assertNotSame($flashKey, $otherModelService->getCircuitBreakerCacheKey());

        $this->assertSame('Model lain tetap normal.', $otherModelService->generateResponse('Tes', [], 'Prompt'));

        // C. Saat satu request sedang melakukan probe pemulihan (state=probing), request bersamaan dialihkan ke fallback
        config(['services.gemini.model' => 'gemini-3.6-flash']);
        $geminiCallsOnProbe = 0;

        Cache::put($flashKey, [
            'state' => 'probing',
            'model' => 'gemini-3.6-flash',
            'activated_at' => Carbon::now()->getTimestamp() - 50,
            'expires_at' => Carbon::now()->getTimestamp() - 5,
            'probe_started_at' => Carbon::now()->getTimestamp(),
            'probe_expires_at' => Carbon::now()->getTimestamp() + 15,
        ], 60);

        $concurrentResp = $this->postJson(route('chatbot.chat'), ['message' => 'Request bersamaan saat probing']);
        $concurrentResp->assertStatus(200)->assertJson(['reply' => 'Groq tangani antrean bersamaan.']);
        $this->assertSame(0, $geminiCallsOnProbe, 'Request bersamaan tidak boleh ikut menembak Gemini saat probe lain masih berjalan.');
        $this->assertSame(1, $groqCalls);
    }

    /**
     * TEST 10 — Integrasi penuh Gemini + Circuit Breaker + Groq Tahap 4B (finish_reason stop & length).
     */
    public function test_end_to_end_gemini_circuit_breaker_and_groq_stage_4b_integration(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 11, 14, 10, 0, 'Asia/Jakarta'));

        $geminiAttempt = 0;
        $groqAttempt = 0;
        $groqMaxTokensSent = [];

        Http::fake([
            'generativelanguage.googleapis.com/*' => function () use (&$geminiAttempt) {
                $geminiAttempt++;

                return match ($geminiAttempt) {
                    1 => Http::response([
                        'candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => '1. Normal dari Gemini.']]]]],
                    ], 200),
                    2 => Http::response(['error' => 'Rate Limit 429'], 429),
                    default => Http::response([
                        'candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => '5. Gemini pulih pasca cooldown.']]]]],
                    ], 200),
                };
            },
            'api.groq.com/*' => function ($request) use (&$groqAttempt, &$groqMaxTokensSent) {
                $groqAttempt++;
                $groqMaxTokensSent[] = $request->data()['max_tokens'] ?? null;

                if ($groqAttempt === 1) {
                    return Http::response([
                        'choices' => [['finish_reason' => 'stop', 'message' => ['content' => '<think>thinking</think>2. Groq stop.']]],
                        'usage' => ['prompt_tokens' => 250, 'completion_tokens' => 40, 'total_tokens' => 290],
                    ], 200);
                }

                return Http::response([
                    'choices' => [['finish_reason' => 'length', 'message' => ['content' => '3. Groq terpotong']]],
                    'usage' => ['prompt_tokens' => 250, 'completion_tokens' => 1024, 'total_tokens' => 1274],
                ], 200);
            },
        ]);

        // Step 1: Gemini Normal
        $this->postJson(route('chatbot.chat'), ['message' => 'Hai'])->assertJson(['reply' => '1. Normal dari Gemini.']);

        // Step 2: Gemini 429 -> Groq (stop) & Cooldown Aktif
        $this->postJson(route('chatbot.chat'), ['message' => 'Apa itu inflasi?'])->assertJson(['reply' => '2. Groq stop.']);

        // Step 3: Masih dalam Cooldown -> Langsung Groq (length Tahap 4B tetap terdeteksi)
        $this->travel(15)->seconds();
        $r3 = $this->postJson(route('chatbot.chat'), ['message' => 'Jelaskan deflasi.']);
        $r3->assertStatus(200);
        $this->assertStringContainsString('3. Groq terpotong', (string) $r3->json('reply'));
        $this->assertStringContainsString('mencapai batas maksimum panjang respons AI', (string) $r3->json('reply'));

        // Step 4: Melewati 45s -> Gemini pulih dan menangani kembali
        $this->travel(35)->seconds();
        $this->postJson(route('chatbot.chat'), ['message' => 'Bagaimana cara meminjam aula?'])
            ->assertJson(['reply' => '5. Gemini pulih pasca cooldown.']);

        $this->assertSame(3, $geminiAttempt);
        $this->assertSame(2, $groqAttempt);
        $this->assertSame([1024, 1024], $groqMaxTokensSent);
    }
}
