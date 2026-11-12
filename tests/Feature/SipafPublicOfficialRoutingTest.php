<?php

namespace Tests\Feature;

use App\Services\AI\AIRouterService;
use App\Services\AI\ConversationContextService;
use App\Services\AI\IntentClassifierService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SipafPublicOfficialRoutingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        Cache::flush();

        config([
            'services.tavily.api_key' => 'tvly-test-stage4i-secret',
            'services.tavily.endpoint' => 'https://api.tavily.com/search',
            'services.tavily.enabled' => true,
            'services.tavily.search_depth' => 'basic',
            'services.tavily.max_results' => 5,
            'services.tavily.cache_ttl_minutes' => 60,
            'services.tavily.per_minute_limit' => 15,
            'services.tavily.daily_limit' => 80,
            'services.gemini.api_key' => 'gemini-test-stage4i-secret',
            'services.gemini.model' => 'gemini-2.5-flash',
            'services.gemini.cooldown_seconds' => 45,
            'services.groq.api_key' => 'groq-test-stage4i-secret',
            'services.groq.max_tokens' => 1024,
        ]);
    }

    /**
     * TEST 1: All public-official queries (short entity lookups without question words and explicit incumbent questions)
     * must route strictly to WEB_SEARCH and build focused official search queries.
     */
    public function test_1_public_official_short_and_explicit_queries_route_to_web_search(): void
    {
        /** @var IntentClassifierService $classifier */
        $classifier = app(IntentClassifierService::class);

        $cases = [
            'Camat Cikampek' => 'Kabupaten Karawang',
            'camat cikampek' => 'Kabupaten Karawang',
            'Nama Camat Cikampek' => 'Kabupaten Karawang',
            'Siapa Camat Cikampek sekarang?' => 'Kabupaten Karawang',
            'Bupati Karawang' => 'Pemerintah Kabupaten Karawang resmi Indonesia',
            'Gubernur Jawa Barat' => 'Pemerintah Provinsi Jawa Barat resmi Indonesia',
            'Sekretaris Camat Cikampek' => 'Kabupaten Karawang',
            'Sekcam Cikampek' => 'Kabupaten Karawang',
            'Kepala Dinas Pendidikan Karawang' => 'Pemerintah Kabupaten Karawang resmi Indonesia',
            'Kapolsek Cikampek' => 'Kabupaten Karawang',
        ];

        foreach ($cases as $query => $expectedEnrichment) {
            $result = $classifier->classify($query, []);

            $this->assertSame(
                IntentClassifierService::INTENT_WEB_SEARCH,
                $result['intent'],
                "Expected WEB_SEARCH for query: {$query} (got {$result['intent']} / {$result['reason']})"
            );
            $this->assertNotNull($result['web_query'], "Expected web_query for {$query}");
            $this->assertStringContainsString($expectedEnrichment, (string) $result['web_query']);
            $this->assertSame('WEB', $result['context']['active_domain']);
        }
    }

    /**
     * TEST 2: Full required routing matrix across WEB_SEARCH, GENERAL_LLM, SIPAF_RAG, and CONVERSATIONAL,
     * including historical queries and local office address/hours/organization structure queries.
     */
    public function test_2_routing_matrix_distinguishes_web_search_general_sipaf_rag_and_conversational(): void
    {
        /** @var IntentClassifierService $classifier */
        $classifier = app(IntentClassifierService::class);

        $expectedMatrix = [
            'Camat Cikampek' => IntentClassifierService::INTENT_WEB_SEARCH,
            'Nama Camat Cikampek' => IntentClassifierService::INTENT_WEB_SEARCH,
            'Siapa Camat Cikampek sekarang?' => IntentClassifierService::INTENT_WEB_SEARCH,
            'Bupati Karawang' => IntentClassifierService::INTENT_WEB_SEARCH,
            'Gubernur Jawa Barat' => IntentClassifierService::INTENT_WEB_SEARCH,
            'Sekretaris Camat Cikampek' => IntentClassifierService::INTENT_WEB_SEARCH,
            'Kepala Dinas Pendidikan Karawang' => IntentClassifierService::INTENT_WEB_SEARCH,
            'Kapolsek Cikampek' => IntentClassifierService::INTENT_WEB_SEARCH,
            'Apa tugas Camat?' => IntentClassifierService::INTENT_GENERAL_LLM,
            'Apa fungsi Sekretaris Camat?' => IntentClassifierService::INTENT_GENERAL_LLM,
            'Apa wewenang Bupati?' => IntentClassifierService::INTENT_GENERAL_LLM,
            'Apa peran Kapolsek?' => IntentClassifierService::INTENT_GENERAL_LLM,
            'Siapa Bupati Karawang pertama?' => IntentClassifierService::INTENT_GENERAL_LLM,
            'Apa itu Kecamatan Cikampek?' => IntentClassifierService::INTENT_SIPAF_RAG,
            'Cara meminjam aula SIPAF' => IntentClassifierService::INTENT_SIPAF_RAG,
            'Persyaratan peminjaman inventaris SIPAF' => IntentClassifierService::INTENT_SIPAF_RAG,
            'Apa itu SIPAF?' => IntentClassifierService::INTENT_SIPAF_RAG,
            'Alamat kantor Camat Cikampek' => IntentClassifierService::INTENT_SIPAF_RAG,
            'Jam buka kantor Camat Cikampek' => IntentClassifierService::INTENT_SIPAF_RAG,
            'Apa struktur organisasi Kecamatan Cikampek?' => IntentClassifierService::INTENT_SIPAF_RAG,
            'Halo' => IntentClassifierService::INTENT_CONVERSATIONAL,
        ];

        foreach ($expectedMatrix as $message => $expectedIntent) {
            $classification = $classifier->classify($message, []);

            $this->assertSame(
                $expectedIntent,
                $classification['intent'],
                "Unexpected intent for '{$message}': got {$classification['intent']} ({$classification['reason']})"
            );
        }
    }

    /**
     * TEST 3: Mocked E2E flow for "camat cikampek" calls Tavily once, prioritizes .go.id domain,
     * passes <WEB_CONTEXT> into Gemini without injecting unverified KB contact defaults, and returns ok.
     */
    public function test_3_camat_cikampek_triggers_tavily_prioritizes_go_id_and_omits_kb_contact_noise(): void
    {
        $tavilyRequests = [];
        $capturedGeminiSystem = '';

        Http::fake([
            'api.tavily.com/*' => function ($request) use (&$tavilyRequests) {
                $tavilyRequests[] = $request->data();

                return Http::response([
                    'query' => $request->data()['query'] ?? '',
                    'results' => [
                        [
                            'title' => 'Berita Daerah Seputar Cikampek',
                            'url' => 'https://karawangnews.example.com/camat-cikampek-update',
                            'content' => 'Kegiatan pelayanan Kecamatan Cikampek Kabupaten Karawang bersama Camat Cikampek.',
                            'score' => 0.90,
                        ],
                        [
                            'title' => 'Profil Kecamatan Cikampek - Portal Resmi Pemkab Karawang',
                            'url' => 'https://karawangkab.go.id/kecamatan-cikampek/camat',
                            'content' => 'Informasi resmi Pemerintah Kabupaten Karawang mengenai pimpinan Kecamatan Cikampek.',
                            'score' => 0.85,
                        ],
                    ],
                ], 200);
            },
            'generativelanguage.googleapis.com/*' => function ($request) use (&$capturedGeminiSystem) {
                $capturedGeminiSystem = (string) ($request->data()['system_instruction']['parts'][0]['text'] ?? '');

                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => 'Berdasarkan sumber resmi Pemerintah Kabupaten Karawang, silakan merujuk pada portal resmi karawangkab.go.id untuk data Camat Cikampek aktif.'],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'camat cikampek',
            'history' => [],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertStringContainsString('karawangkab.go.id', (string) $response->json('reply'));

        $this->assertCount(1, $tavilyRequests, 'Tavily must be invoked once for "camat cikampek".');
        $this->assertStringContainsStringIgnoringCase('camat cikampek', (string) ($tavilyRequests[0]['query'] ?? ''));
        $this->assertStringContainsString('Kabupaten Karawang', (string) ($tavilyRequests[0]['query'] ?? ''));

        // Official .go.id source must be prioritized first in <WEB_CONTEXT>
        $this->assertStringContainsString('MODE AKTIF SAAT INI: WEB_SEARCH', $capturedGeminiSystem);
        $this->assertStringContainsString('<WEB_CONTEXT>', $capturedGeminiSystem);
        $govPos = strpos($capturedGeminiSystem, 'karawangkab.go.id');
        $newsPos = strpos($capturedGeminiSystem, 'karawangnews.example.com');
        $this->assertNotFalse($govPos);
        $this->assertNotFalse($newsPos);
        $this->assertLessThan($newsPos, $govPos, 'Official .go.id result must be ranked ahead of non-.go.id media.');

        // Section 4 safety: WEB_SEARCH must not inject KB office phone/address defaults into the system prompt
        $this->assertStringNotContainsString('(0264) 301234', $capturedGeminiSystem);
        $this->assertStringNotContainsString('Jl. Ahmad Yani No. 1', $capturedGeminiSystem);
        $this->assertStringNotContainsString('07.45 - 15.45 WIB', $capturedGeminiSystem);

        // Verify meta diagnostics on AIRouterService directly
        Cache::flush();
        /** @var AIRouterService $router */
        $router = app(AIRouterService::class);
        $routerResult = $router->chat('camat cikampek', []);

        $this->assertSame('gemini', $routerResult['provider']);
        $this->assertSame(IntentClassifierService::INTENT_WEB_SEARCH, $routerResult['intent']);
        $this->assertSame('ok', $routerResult['web_search']['status']);
        $this->assertGreaterThanOrEqual(1, $routerResult['web_search']['result_count']);
    }

    /**
     * TEST 4: If Tavily returns empty or unverified results on "camat cikampek",
     * the system never fabricates external links or falls back to KB contact details.
     */
    public function test_4_camat_cikampek_empty_tavily_discloses_limitation_and_does_not_inject_kb_contacts(): void
    {
        $capturedSystem = '';

        Http::fake([
            'api.tavily.com/*' => Http::response(['results' => []], 200),
            'generativelanguage.googleapis.com/*' => function ($request) use (&$capturedSystem) {
                $capturedSystem = (string) ($request->data()['system_instruction']['parts'][0]['text'] ?? '');

                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => 'Maaf, informasi resmi mengenai nama Camat Cikampek saat ini belum tersedia dari hasil pencarian web yang terverifikasi.'],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        /** @var AIRouterService $router */
        $router = app(AIRouterService::class);
        $result = $router->chat('camat cikampek', []);

        $this->assertTrue($result['success']);
        $this->assertSame(IntentClassifierService::INTENT_WEB_SEARCH, $result['intent']);
        $this->assertSame('empty', $result['web_search']['status']);
        $this->assertSame(0, $result['web_search']['result_count']);
        $this->assertStringNotContainsString('http', (string) $result['reply']);
        $this->assertStringContainsString('MODE AKTIF SAAT INI: WEB_SEARCH', $capturedSystem);
        $this->assertStringNotContainsString('(0264) 301234', $capturedSystem);
        $this->assertStringNotContainsString('Jl. Ahmad Yani No. 1', $capturedSystem);
    }

    /**
     * TEST 5: "Apa tugas Camat?", "Apa itu Kecamatan Cikampek?", "Cara meminjam aula SIPAF",
     * "Alamat kantor Camat Cikampek", and "Halo" NEVER invoke Tavily.
     */
    public function test_5_general_role_sipaf_rag_and_conversational_queries_never_invoke_tavily(): void
    {
        $tavilyCalls = 0;
        $capturedPrompts = [];

        Http::fake([
            'api.tavily.com/*' => function () use (&$tavilyCalls) {
                $tavilyCalls++;

                return Http::response(['results' => []], 200);
            },
            'generativelanguage.googleapis.com/*' => function ($request) use (&$capturedPrompts) {
                $prompt = (string) ($request->data()['system_instruction']['parts'][0]['text'] ?? '');
                $capturedPrompts[] = $prompt;

                return Http::response([
                    'candidates' => [
                        ['content' => ['parts' => [['text' => 'Jawaban AI valid sesuai mode.']]]],
                    ],
                ], 200);
            },
        ]);

        /** @var AIRouterService $router */
        $router = app(AIRouterService::class);

        $cases = [
            'Apa tugas Camat?' => IntentClassifierService::INTENT_GENERAL_LLM,
            'Apa itu Kecamatan Cikampek?' => IntentClassifierService::INTENT_SIPAF_RAG,
            'Cara meminjam aula SIPAF' => IntentClassifierService::INTENT_SIPAF_RAG,
            'Persyaratan peminjaman inventaris SIPAF' => IntentClassifierService::INTENT_SIPAF_RAG,
            'Alamat kantor Camat Cikampek' => IntentClassifierService::INTENT_SIPAF_RAG,
            'Jam buka kantor Camat Cikampek' => IntentClassifierService::INTENT_SIPAF_RAG,
            'Halo' => IntentClassifierService::INTENT_CONVERSATIONAL,
        ];

        foreach ($cases as $query => $expectedIntent) {
            $res = $router->chat($query, []);
            $this->assertTrue($res['success']);
            $this->assertSame($expectedIntent, $res['intent'], "Query '{$query}' routed wrongly.");
            $this->assertNull($res['web_search']);
            if ($expectedIntent === IntentClassifierService::INTENT_GENERAL_LLM) {
                $this->assertSame(0, $res['retrieved_count'], "Query '{$query}' must not pull KB documents.");
            }
        }

        $this->assertSame(0, $tavilyCalls, 'None of GENERAL_LLM, SIPAF_RAG, or CONVERSATIONAL should call Tavily.');
        $this->assertStringContainsString('MODE AKTIF SAAT INI: GENERAL_LLM', $capturedPrompts[0]);
        $this->assertStringContainsString('TIDAK ADA DATA KNOWLEDGE BASE YANG RELEVAN UNTUK PERTANYAAN UMUM INI', $capturedPrompts[0]);
        $this->assertStringContainsString('MODE AKTIF SAAT INI: SIPAF_RAG', $capturedPrompts[1]);
        $this->assertStringContainsString('MODE AKTIF SAAT INI: CONVERSATIONAL', $capturedPrompts[6]);
    }

    /**
     * TEST 6: Multi-turn topic shifts between SIPAF <-> Public Official WEB_SEARCH <-> General role query
     * cleanly detect topic_shifted and prune irrelevant history.
     */
    public function test_6_topic_shifts_between_sipaf_public_officials_and_general_roles_prune_history(): void
    {
        /** @var ConversationContextService $contextService */
        $contextService = app(ConversationContextService::class);
        /** @var IntentClassifierService $classifier */
        $classifier = app(IntentClassifierService::class);

        // A. SIPAF -> Pejabat ("Cara meminjam aula SIPAF" -> "Camat Cikampek")
        $sipafToOfficialHistory = [
            ['role' => 'user', 'text' => 'Cara meminjam aula SIPAF'],
            ['role' => 'model', 'text' => 'Anda dapat mengajukan peminjaman aula melalui menu Pinjam Aula di SIPAF.'],
        ];
        $toOfficial = $classifier->classify('Camat Cikampek', $sipafToOfficialHistory);

        $this->assertSame(IntentClassifierService::INTENT_WEB_SEARCH, $toOfficial['intent']);
        $this->assertSame('WEB', $toOfficial['context']['active_domain']);
        $this->assertTrue($toOfficial['context']['topic_shifted']);
        $this->assertSame([], $toOfficial['context']['relevant_history']);

        // B. Pejabat -> SIPAF ("Camat Cikampek" -> "Cara meminjam aula SIPAF")
        $officialToSipafHistory = [
            ['role' => 'user', 'text' => 'Camat Cikampek'],
            ['role' => 'model', 'text' => 'Data Camat Cikampek berasal dari sumber resmi pemerintah.'],
        ];
        $toSipaf = $classifier->classify('Cara meminjam aula SIPAF', $officialToSipafHistory);

        $this->assertSame(IntentClassifierService::INTENT_SIPAF_RAG, $toSipaf['intent']);
        $this->assertSame('SIPAF', $toSipaf['context']['active_domain']);
        $this->assertTrue($toSipaf['context']['topic_shifted']);
        $this->assertSame([], $toSipaf['context']['relevant_history']);

        // C. Pejabat -> General Kecamatan overview without subtopic ("Camat Cikampek" -> "Apa itu Kecamatan Cikampek?")
        $toOverview = $classifier->classify('Apa itu Kecamatan Cikampek?', $officialToSipafHistory);

        $this->assertSame(IntentClassifierService::INTENT_SIPAF_RAG, $toOverview['intent']);
        $this->assertSame('SIPAF', $toOverview['context']['active_domain']);
        $this->assertTrue($toOverview['context']['topic_shifted']);
        $this->assertSame([], $toOverview['context']['relevant_history']);

        // D. Pejabat -> General official role ("Camat Cikampek" -> "Apa tugas Camat?")
        $toRoleGeneral = $classifier->classify('Apa tugas Camat?', $officialToSipafHistory);

        $this->assertSame(IntentClassifierService::INTENT_GENERAL_LLM, $toRoleGeneral['intent']);
        $this->assertSame('GENERAL', $toRoleGeneral['context']['active_domain']);
        $this->assertTrue($toRoleGeneral['context']['topic_shifted']);
        $this->assertSame([], $toRoleGeneral['context']['relevant_history']);

        // Direct history domain inspection confirms short "Camat Cikampek" history turn is classified as WEB
        $directAnalyze = $contextService->analyze('Siapa penanggung jawab aula?', $officialToSipafHistory, [
            'has_civic_anchor' => true,
        ]);
        $this->assertSame('SIPAF', $directAnalyze['active_domain']);
        $this->assertCount(0, $directAnalyze['relevant_history']);
    }

    /**
     * TEST 7: Gemini HTTP 429 cooldown failover to Groq preserves WEB_SEARCH routing and <WEB_CONTEXT> for "camat cikampek".
     */
    public function test_7_camat_cikampek_failover_to_groq_preserves_web_search_context_on_gemini_429(): void
    {
        $capturedGroqSystem = '';

        Http::fake([
            'api.tavily.com/*' => Http::response([
                'results' => [
                    [
                        'title' => 'Portal Pemkab Karawang - Kecamatan Cikampek',
                        'url' => 'https://karawangkab.go.id/kecamatan-cikampek',
                        'content' => 'Informasi resmi Pemerintah Kabupaten Karawang tentang Kecamatan Cikampek.',
                        'score' => 0.92,
                    ],
                ],
            ], 200),
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['code' => 429, 'message' => 'Resource exhausted'],
            ], 429),
            'api.groq.com/*' => function ($request) use (&$capturedGroqSystem) {
                $capturedGroqSystem = (string) ($request->data()['messages'][0]['content'] ?? '');

                return Http::response([
                    'choices' => [
                        [
                            'finish_reason' => 'stop',
                            'message' => ['content' => 'Berikut informasi resmi Kecamatan Cikampek dari sumber terverifikasi Pemkab Karawang.'],
                        ],
                    ],
                    'usage' => ['prompt_tokens' => 200, 'completion_tokens' => 30, 'total_tokens' => 230],
                ], 200);
            },
        ]);

        $response = $this->postJson(route('chatbot.chat'), [
            'message' => 'Camat Cikampek',
            'history' => [],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertStringContainsString('karawangkab.go.id', (string) $response->json('reply'));
        $this->assertStringContainsString('MODE AKTIF SAAT INI: WEB_SEARCH', $capturedGroqSystem);
        $this->assertStringContainsString('karawangkab.go.id', $capturedGroqSystem);
        $this->assertStringNotContainsString('(0264) 301234', $capturedGroqSystem);

        /** @var AIRouterService $router */
        $router = app(AIRouterService::class);
        $routerResult = $router->chat('Camat Cikampek', []);
        $this->assertSame('groq', $routerResult['provider']);
        $this->assertSame(IntentClassifierService::INTENT_WEB_SEARCH, $routerResult['intent']);
    }
}
