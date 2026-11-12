<?php

namespace App\Services\AI;

class IntentClassifierService
{
    public const INTENT_CONVERSATIONAL = 'CONVERSATIONAL';
    public const INTENT_SIPAF_RAG = 'SIPAF_RAG';
    public const INTENT_GENERAL_LLM = 'GENERAL_LLM';
    public const INTENT_CLARIFICATION = 'CLARIFICATION';
    public const INTENT_WEB_SEARCH = 'WEB_SEARCH';
    public const INTENT_HYBRID_RAG_WEB = 'HYBRID_RAG_WEB';

    /**
     * Strong civic / SIPAF domain keywords that always route to SIPAF RAG.
     */
    protected const STRONG_CIVIC_KEYWORDS = [
        'sipaf',
        'cikampek',
        'kecamatan',
        'paten',
        'ppats',
        'kalihurip',
        'kamojing',
        'dawuan',
        'aula',
        'inventaris',
        'kursi',
        'proyektor',
        'sound system',
        'piagam',
        'ahli waris',
        'sktm',
        'tidak mampu',
        'kartu keluarga',
        'akta kelahiran',
        'akta kematian',
        'mall pelayanan publik',
        'disdukcapil',
        'sertifikat tanah',
        'jual beli tanah',
        'riwayat tanah',
        'letter c',
        'girik',
        'bpn',
        'kesos',
        'bansos',
        'bantuan sosial',
        'dtks',
        'pkh',
        'blt',
    ];

    /**
     * Short or ambiguous civic tokens requiring exact word boundary matching.
     */
    protected const EXACT_CIVIC_TOKENS = [
        'ktp',
        'kk',
        'mpp',
        'ajb',
        'shm',
        'sku',
        'waris',
    ];

    /**
     * Words that confirm a service/local-procedure intent when paired with semi-ambiguous terms
     * such as "sertifikat", "umkm", "komputer", "pc", "sound", "usaha", "tanah", "desa".
     */
    protected const SERVICE_ACTION_TERMS = [
        'pinjam', 'pinjem', 'minjam', 'minjem', 'peminjaman',
        'sewa', 'biaya', 'tarif', 'harga', 'retribusi', 'stok', 'sedia', 'tersedia',
        'aju', 'ajukan', 'mengajukan', 'pengajuan',
        'urus', 'ngurus', 'mengurus', 'pengurusan',
        'buat', 'bikin', 'membuat', 'pembuatan',
        'daftar', 'mendaftar', 'pendaftaran',
        'syarat', 'persyaratan', 'prosedur', 'alur', 'cara', 'langkah',
        'riwayat', 'status', 'akun', 'login',
        'kantor', 'kecamatan', 'cikampek', 'sipaf', 'desa', 'loket', 'petugas',
    ];

    /**
     * Semi-ambiguous terms that only indicate SIPAF_RAG when paired with service/civic context.
     */
    protected const CONTEXTUAL_CIVIC_TERMS = [
        'sertifikat',
        'umkm',
        'izin usaha',
        'surat keterangan usaha',
        'komputer',
        'sound',
        'mic',
        'speaker',
        'tanah',
        'akta',
        'desa',
        'gedung',
        'kantor',
    ];

    /**
     * Short contextual terms requiring exact word boundary matching.
     */
    protected const EXACT_CONTEXTUAL_TOKENS = [
        'pc',
    ];

    /**
     * Office/overview/demography combinations specific to Kecamatan Cikampek.
     */
    protected const CIVIC_PHRASE_PATTERNS = [
        '/\b(jam|waktu|jadwal)\s+(buka|tutup|kerja|operasional|pelayanan|layanan)\b/u',
        '/\b(alamat|lokasi|letak|telepon|telp|nomor|kontak|email)\s+(kantor|kecamatan|camat|sipaf)\b/u',
        '/\bkantor\s+(?:camat|kecamatan|desa)\b/u',
        '/\bstruktur\s+(?:organisasi|pemerintahan)\s+(?:kantor\s+|kecamatan\s+)?(?:camat|cikampek|kecamatan)\b/u',
        '/\b(luas\s+(wilayah|desa)|jumlah\s+(penduduk|jiwa)|10\s+desa|sepuluh\s+desa)\b/u',
        '/\b(pelayanan|layanan|fasilitas|menu|fitur)\s+apa\s+saja\b/u',
        '/\b(pengantar\s+rt|rt\s*\/?\s*rw\s+.*(desa|kecamatan|surat|sktm|waris|usaha))\b/u',
        '/\b(status\s+pengajuan|riwayat\s+pengajuan|daftar\s+akun|akun\s+masyarakat)\b/u',
    ];

    /**
     * Public official / government leadership titles used to detect incumbent web-search queries.
     */
    public const PUBLIC_OFFICIAL_ROLE_PATTERN = '/\b(?:(?:penjabat|pj|plt)\s+)?(?:wakil\s+)?(?:presiden|gubernur|bupati|wali\s*kota|walikota|sekretaris\s+camat|sekcam|camat|sekretaris\s+daerah|sekda|kepala\s+desa|kades|sekretaris\s+desa|sekdes|lurah|ketua\s+(?:dprd|dpr|mpr|dpd|kpu|bawaslu|bpd)|kepala\s+(?:dinas|badan|kantor|bagian|bidang|biro|kejaksaan|puskesmas)|kadis|kaban|kakan|menteri|kapolsek|kapolres|kapolresta|kapolda|kapolri|danramil|dandim|danrem|pangdam|kajari|kajati)\b/u';

    /**
     * Filler tokens ignored when checking whether a short official-title query names a region or agency.
     */
    protected const PUBLIC_OFFICIAL_STOPWORDS = [
        'siapa', 'siapakah', 'nama', 'namanya', 'yang', 'menjabat', 'menjadi', 'jadi',
        'sebagai', 'adalah', 'itu', 'ini', 'di', 'pada', 'dari', 'ke', 'untuk', 'tentang',
        'mengenai', 'info', 'informasi', 'profil', 'biodata', 'data', 'daftar',
        'sekarang', 'saat', 'terbaru', 'terkini', 'aktif', 'periode', 'tolong', 'mohon',
        'beritahu', 'kasih', 'tahu', 'dong', 'min', 'admin', 'kak', 'pak', 'bu', 'mas', 'mbak',
        'halo', 'hai', 'assalamualaikum', 'kabupaten', 'kab', 'kota', 'kecamatan', 'kec',
        'provinsi', 'prov', 'daerah', 'wilayah', 'pemerintah', 'pemerintahan', 'ri', 'indonesia',
        'secara', 'umum', 'dengan', 'atau', 'dan', 'oleh',
    ];

    /**
     * Pure conversational / greeting / gratitude / identity patterns (no factual intent).
     */
    protected const CONVERSATIONAL_PATTERNS = [
        '/^(halo+|hai+|hello+|hi+|hey+|assalamu\s*\'?alaikum|salam|permisi|selamat\s+(pagi|siang|sore|malam))(\s+(min|admin|kak|pak|bu|mas|mbak|bot|asisten|semua|apa\s+kabar(nya)?(\s+hari\s+ini)?|kabarnya(\s+bagaimana|\s+gimana)?|gimana\s+kabar(nya)?))*\s*$/u',
        '/^(apa\s+kabar(nya)?(\s+hari\s+ini)?|gimana\s+kabar(nya)?|kabar\s+baik)\s*$/u',
        '/^(terima\s*kasih|makasih|thanks|thank\s+you|trims)(\s+(banyak|ya+|kak|min|pak|bu|mas|mbak|infonya|bantuannya|atas\s+bantuannya|sama\s+sama))*\s*$/u',
        '/^(ok|oke|siap|sip|baik|mantap|bagus)(\s+(min|kak|pak|bu|mas|mbak|terima\s*kasih|makasih|thanks|infonya))*\s*$/u',
        '/^(siapa\s+(kamu|anda|namamu|nama\s+kamu)|kamu\s+siapa|kamu\s+(ai|robot|bot|asisten)\s*(ya)?|perkenalkan\s+diri(mu)?)\s*$/u',
    ];

    /**
     * Conceptual / encyclopedic / general-knowledge query patterns.
     */
    public const CONCEPTUAL_GENERAL_PATTERNS = [
        '/^(apa\s+itu|apa\s+yang\s+dimaksud\s+dengan|apa\s+pengertian|pengertian(\s+dari)?|definisi(\s+dari)?|arti(\s+kata)?|apa\s+kepanjangan(\s+dari)?)\s+/u',
        '/^apa\s+perbedaan(\s+antara)?\s+/u',
        '/^jelaskan(\s+(apa\s+itu|pengertian|tentang|mengenai|sejarah|cara\s+kerja|dampak|manfaat|perbedaan))?\s+/u',
        '/^(siapa\s+(penemu|pencipta|presiden|wakil\s+presiden)|kapan\s+(terjadinya|berdirinya|indonesia\s+merdeka))\s+/u',
        '/^bagaimana\s+(cara\s+kerja|proses\s+terjadinya|cara\s+menulis\s+surat)\s+/u',
        '/^(?:apa\s+(?:saja\s+)?(?:tugas|tupoksi|tugas\s+pokok(?:\s+dan\s+fungsi)?|fungsi|peran|wewenang|kewenangan|tanggung\s+jawab|kewajiban|hak|syarat\s+menjadi)|(?:tugas|tupoksi|fungsi|peran|wewenang|kewenangan)(?:\s+dan\s+(?:tugas|tupoksi|fungsi|peran|wewenang|kewajiban))?\s+(?:dari\s+)?(?:seorang\s+)?(?:camat|sekcam|sekretaris\s+camat|bupati|wakil\s+bupati|gubernur|wakil\s+gubernur|walikota|wali\s+kota|kepala\s+desa|kades|lurah|kepala\s+dinas|kapolsek|kapolres|sekda|ketua\s+dprd|presiden|menteri)|jelaskan\s+(?:tugas|tupoksi|fungsi|peran|wewenang|kewenangan|tanggung\s+jawab|kewajiban)|bagaimana\s+(?:tugas|peran|wewenang|cara\s+menjadi|mekanisme\s+(?:kerja|pemilihan|pengangkatan)))\b/u',
    ];

    /**
     * Elliptical follow-up markers indicating reliance on previous conversation turn.
     */
    protected const FOLLOWUP_TOKENS = [
        'syarat', 'syaratnya', 'persyaratan', 'persyaratannya', 'berkas', 'berkasnya',
        'dokumen', 'dokumennya', 'biaya', 'biayanya', 'harga', 'harganya',
        'tarif', 'tarifnya', 'sewa', 'sewanya', 'gratis', 'bayar',
        'stok', 'stoknya', 'jam', 'jamnya', 'buka', 'bukanya', 'tutup', 'tutupnya',
        'alamat', 'alamatnya', 'lokasi', 'lokasinya', 'cara', 'caranya',
        'alur', 'alurnya', 'prosedur', 'prosedurnya', 'langkah', 'langkahnya',
        'lama', 'prosesnya', 'status', 'statusnya', 'daftar', 'daftarnya',
        'itu', 'tersebut', 'tadi', 'kalau', 'terus', 'lalu', 'gimana', 'bagaimana', 'berapa',
    ];

    /**
     * Clear out-of-domain terms that prove a short follow-up switched topics away from SIPAF.
     */
    protected const NON_CIVIC_TOPIC_SHIFT_TOKENS = [
        'inflasi', 'deflasi', 'ekonomi', 'paspor', 'sim', 'stnk', 'tilang',
        'kereta', 'pesawat', 'tiket', 'hotel', 'cuaca', 'hujan', 'gempa',
        'presiden', 'planet', 'fotosintesis', 'matematika', 'coding', 'python', 'javascript',
    ];

    protected KnowledgeRetrievalService $retrievalService;
    protected ConversationContextService $contextService;

    public function __construct(
        ?KnowledgeRetrievalService $retrievalService = null,
        ?ConversationContextService $contextService = null
    ) {
        $this->retrievalService = $retrievalService ?? new KnowledgeRetrievalService();
        $this->contextService = $contextService ?? new ConversationContextService($this->retrievalService);
    }

    /**
     * Classify user query into CONVERSATIONAL, SIPAF_RAG, GENERAL_LLM, CLARIFICATION,
     * WEB_SEARCH, or HYBRID_RAG_WEB while resolving conversation context and RAG retrieval.
     *
     * @param string $userMessage
     * @param array<int, array{role?: string, text?: string, isError?: bool}> $history
     * @return array{
     *     intent: string,
     *     reason: string,
     *     retrieval_query: string,
     *     web_query: ?string,
     *     retrieval: array|null,
     *     context: array
     * }
     */
    public function classify(string $userMessage, array $history = []): array
    {
        $normalized = $this->normalizeText($userMessage);

        if ($normalized === '') {
            $context = $this->contextService->analyze($userMessage, $history, [
                'is_conversational' => true,
            ]);

            return [
                'intent' => self::INTENT_CONVERSATIONAL,
                'reason' => 'empty_normalized',
                'retrieval_query' => $userMessage,
                'web_query' => null,
                'retrieval' => null,
                'context' => $context,
            ];
        }

        $isPureConversational = $this->isPureConversational($normalized, $userMessage);
        $hasCivicAnchor = $this->hasCivicDomainSignal($normalized);
        $isConceptualGeneral = $this->isConceptualGeneralQuery($normalized);
        $parsed = $this->retrievalService->prepareQuery($userMessage);
        $isOverviewQuery = !empty($parsed['is_overview_query'])
            || preg_match('/\b(pelayanan|layanan|fasilitas|menu|fitur)\s+apa\s+saja\b/u', $normalized) === 1;

        $webSignal = $isPureConversational ? null : $this->detectExternalWebSignal($normalized);
        $isHybridMix = $webSignal !== null && $this->isHybridInternalAndExternal($normalized, $webSignal);

        $context = $this->contextService->analyze($userMessage, $history, [
            'is_conversational' => $isPureConversational,
            'has_civic_anchor' => $hasCivicAnchor,
            'is_conceptual_general' => $isConceptualGeneral,
            'is_overview_query' => $isOverviewQuery,
            'has_web_signal' => $webSignal !== null,
            'has_hybrid_signal' => $isHybridMix,
        ]);

        // 1. Pure conversational greeting / gratitude / identity without civic/factual terms
        if ($isPureConversational) {
            return [
                'intent' => self::INTENT_CONVERSATIONAL,
                'reason' => 'pure_conversational',
                'retrieval_query' => $userMessage,
                'web_query' => null,
                'retrieval' => null,
                'context' => $context,
            ];
        }

        // 2. Underspecified / ambiguous referential query without safe prior context
        if (!empty($context['is_ambiguous'])) {
            return [
                'intent' => self::INTENT_CLARIFICATION,
                'reason' => 'clarification_ambiguous',
                'retrieval_query' => $userMessage,
                'web_query' => null,
                'retrieval' => $this->buildSkippedRetrievalResult($userMessage, 'clarification_ambiguous'),
                'context' => $context,
            ];
        }

        // 3. Hybrid SIPAF RAG + Live Web Search (both internal KB and live external info needed)
        if ($webSignal !== null && $isHybridMix) {
            $retrievalQuery = $context['resolved_query'];
            $retrieval = $this->retrievalService->retrieve($retrievalQuery);
            $webQuery = $this->buildWebSearchQuery($userMessage, $normalized);

            // Only keep HYBRID_RAG_WEB if internal KB actually has relevant docs; otherwise clean WEB_SEARCH
            if ($retrieval['documents']->isNotEmpty()) {
                return [
                    'intent' => self::INTENT_HYBRID_RAG_WEB,
                    'reason' => 'hybrid_' . $webSignal,
                    'retrieval_query' => $retrievalQuery,
                    'web_query' => $webQuery,
                    'retrieval' => $retrieval,
                    'context' => $context,
                ];
            }

            return [
                'intent' => self::INTENT_WEB_SEARCH,
                'reason' => 'web_' . $webSignal . '_no_kb',
                'retrieval_query' => $userMessage,
                'web_query' => $webQuery,
                'retrieval' => $retrieval,
                'context' => $context,
            ];
        }

        // 4. Live / External Web Search (incumbent officials, national schedules, latest regulations/news, web follow-ups)
        if ($webSignal !== null || ($context['active_domain'] ?? '') === 'WEB') {
            $queryForWeb = ($context['active_domain'] ?? '') === 'WEB'
                ? $context['resolved_query']
                : $userMessage;

            return [
                'intent' => self::INTENT_WEB_SEARCH,
                'reason' => $webSignal !== null ? 'web_' . $webSignal : 'followup_web_history',
                'retrieval_query' => $userMessage,
                'web_query' => $this->buildWebSearchQuery($queryForWeb, $normalized),
                'retrieval' => $this->buildSkippedRetrievalResult($userMessage, 'web_search_external'),
                'context' => $context,
            ];
        }

        // 5. Explicit SIPAF topic, cross-domain SIPAF bridge, or elliptical SIPAF follow-up
        if (($context['active_domain'] ?? '') === 'SIPAF' || $hasCivicAnchor) {
            $retrievalQuery = $context['resolved_query'];
            $retrieval = $this->retrievalService->retrieve($retrievalQuery);
            $resolutionType = $context['resolution_type'] ?? 'sipaf_context';
            $reason = $resolutionType === 'followup_sipaf' ? 'followup_sipaf_history' : $resolutionType;

            return [
                'intent' => self::INTENT_SIPAF_RAG,
                'reason' => $reason,
                'retrieval_query' => $retrievalQuery,
                'web_query' => null,
                'retrieval' => $retrieval,
                'context' => $context,
            ];
        }

        // 6. General follow-up ("Apa penyebabnya?") or conceptual general query ("Apa itu inflasi?")
        if (($context['resolution_type'] ?? '') === 'followup_general' || $isConceptualGeneral) {
            $isFollowUpGeneral = ($context['resolution_type'] ?? '') === 'followup_general';

            return [
                'intent' => self::INTENT_GENERAL_LLM,
                'reason' => $isFollowUpGeneral ? 'followup_general_history' : 'conceptual_general',
                'retrieval_query' => $userMessage,
                'web_query' => null,
                'retrieval' => $this->buildSkippedRetrievalResult(
                    $userMessage,
                    $isFollowUpGeneral ? 'general_llm_followup' : 'general_llm_conceptual'
                ),
                'context' => $context,
            ];
        }

        // 7. Fallback probe against active KB: if genuinely relevant KB exists, use SIPAF_RAG;
        // otherwise route cleanly to GENERAL_LLM so general questions can be answered naturally.
        $retrieval = $this->retrievalService->retrieve($context['resolved_query']);

        if ($retrieval['documents']->isNotEmpty()) {
            return [
                'intent' => self::INTENT_SIPAF_RAG,
                'reason' => 'kb_matched',
                'retrieval_query' => $context['resolved_query'],
                'web_query' => null,
                'retrieval' => $retrieval,
                'context' => $context,
            ];
        }

        return [
            'intent' => self::INTENT_GENERAL_LLM,
            'reason' => 'no_kb_match_general',
            'retrieval_query' => $userMessage,
            'web_query' => null,
            'retrieval' => $retrieval,
            'context' => $context,
        ];
    }

    protected function isPureConversational(string $normalized, string $rawMessage): bool
    {
        if ($this->hasCivicDomainSignal($normalized) || self::matchesPublicOfficialLookup($normalized)) {
            return false;
        }

        foreach (self::CONVERSATIONAL_PATTERNS as $pattern) {
            if (preg_match($pattern, $normalized) === 1) {
                return true;
            }
        }

        $parsed = $this->retrievalService->prepareQuery($rawMessage);
        return !empty($parsed['is_conversational']);
    }

    protected function hasCivicDomainSignal(string $normalized): bool
    {
        foreach (self::STRONG_CIVIC_KEYWORDS as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return true;
            }
        }

        foreach (self::EXACT_CIVIC_TOKENS as $token) {
            if ($this->containsWholeWord($normalized, $token)) {
                return true;
            }
        }

        foreach (self::CIVIC_PHRASE_PATTERNS as $pattern) {
            if (preg_match($pattern, $normalized) === 1) {
                return true;
            }
        }

        $hasServiceAction = false;
        foreach (self::SERVICE_ACTION_TERMS as $action) {
            if ($this->containsWholeWord($normalized, $action) || (mb_strlen($action) >= 5 && str_contains($normalized, $action))) {
                $hasServiceAction = true;
                break;
            }
        }

        if ($hasServiceAction) {
            foreach (self::CONTEXTUAL_CIVIC_TERMS as $term) {
                if ($this->containsWholeWord($normalized, $term) || (mb_strlen($term) >= 6 && str_contains($normalized, $term))) {
                    return true;
                }
            }

            foreach (self::EXACT_CONTEXTUAL_TOKENS as $token) {
                if ($this->containsWholeWord($normalized, $token)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function isConceptualGeneralQuery(string $normalized): bool
    {
        return self::matchesConceptualGeneralPattern($normalized);
    }

    /**
     * Enrich short elliptical follow-up queries using recent non-error SIPAF user turns.
     */
    protected function resolveFollowUpQuery(string $normalized, string $rawMessage, array $history): ?string
    {
        if (empty($history)) {
            return null;
        }

        $words = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) === 0 || count($words) > 9) {
            return null;
        }

        foreach (self::NON_CIVIC_TOPIC_SHIFT_TOKENS as $shiftToken) {
            if ($this->containsWholeWord($normalized, $shiftToken)) {
                return null;
            }
        }

        $hasFollowUpCue = false;
        foreach ($words as $word) {
            if (in_array($word, self::FOLLOWUP_TOKENS, true) || str_ends_with($word, 'nya')) {
                $hasFollowUpCue = true;
                break;
            }
        }

        if (!$hasFollowUpCue) {
            return null;
        }

        $recentUserMessages = [];
        foreach (array_reverse($history) as $item) {
            if (!empty($item['isError'])) {
                continue;
            }
            if (($item['role'] ?? '') === 'user') {
                $text = trim((string) ($item['text'] ?? ''));
                if ($text !== '') {
                    $recentUserMessages[] = $text;
                    if (count($recentUserMessages) >= 2) {
                        break;
                    }
                }
            }
        }

        foreach ($recentUserMessages as $prevUserText) {
            $prevNorm = $this->normalizeText($prevUserText);
            if ($prevNorm !== '' && $this->hasCivicDomainSignal($prevNorm)) {
                $prevParsed = $this->retrievalService->prepareQuery($prevUserText);
                $contextTokens = array_slice(
                    array_values(array_unique(array_merge($prevParsed['specific_tokens'], $prevParsed['generic_tokens']))),
                    0,
                    5
                );

                if (!empty($contextTokens)) {
                    return trim(implode(' ', $contextTokens) . ' ' . $rawMessage);
                }
            }
        }

        return null;
    }

    protected function buildSkippedRetrievalResult(string $userMessage, string $strategy): array
    {
        $parsed = $this->retrievalService->prepareQuery($userMessage);

        return [
            'documents' => collect(),
            'strategy' => $strategy,
            'retrieval_time_ms' => 0.0,
            'context_chars' => 0,
            'is_conversational' => false,
            'query_tokens' => $parsed['all_tokens'],
        ];
    }

    /**
     * Detect whether a normalized query matches conceptual/general-knowledge patterns (including official role duties).
     */
    public static function matchesConceptualGeneralPattern(string $normalized): bool
    {
        $stripped = preg_replace(
            '/^((halo+|hai+|hello+|hi+|assalamu\s*\'?alaikum|selamat\s+(pagi|siang|sore|malam)|permisi|min|admin|kak|pak|bu|mas|mbak|tolong|mohon|coba|bisa|mau\s+tanya|saya\s+(mau|ingin)\s+tanya|tanya\s+dong)\s+)+/u',
            '',
            trim($normalized)
        ) ?? $normalized;
        $stripped = trim($stripped);

        foreach (self::CONCEPTUAL_GENERAL_PATTERNS as $pattern) {
            if (preg_match($pattern, $normalized) === 1 || ($stripped !== '' && preg_match($pattern, $stripped) === 1)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect whether a normalized query looks up an incumbent/current public official
     * (either explicitly with "siapa/nama/sekarang" or as a short entity-region lookup like "Camat Cikampek").
     */
    public static function matchesPublicOfficialLookup(string $normalized): bool
    {
        $normalized = trim($normalized);
        if ($normalized === '' || preg_match(self::PUBLIC_OFFICIAL_ROLE_PATTERN, $normalized) !== 1) {
            return false;
        }

        if (preg_match('/\b(pertama|kedua|ketiga|keempat|kelima|sejarah|terdahulu|dulu)\b/u', $normalized) === 1) {
            return false;
        }

        if (preg_match(
            '/\b(tugas|tupoksi|tugas\s+pokok|fungsi|peran|wewenang|kewenangan|tanggung\s+jawab|kewajiban|definisi|pengertian|arti|kepanjangan|perbedaan|cara\s+kerja|cara\s+menjadi|syarat\s+menjadi|mekanisme|mengangkat|melantik|atasan|gaji|tunjangan|masa\s+jabatan|berapa\s+jumlah|ada\s+berapa|apa\s+itu|apa\s+yang\s+dimaksud)\b/u',
            $normalized
        ) === 1) {
            return false;
        }

        if (preg_match(
            '/\b(alamat|lokasi|letak|jam\s+(?:buka|tutup|kerja|operasional|pelayanan)|nomor\s+(?:telepon|telp|hp|wa|whatsapp|kontak)|telepon|telp|email|sipaf|aula|inventaris)\b/u',
            $normalized
        ) === 1) {
            return false;
        }

        $hasExplicitIncumbentCue = preg_match(
            '/\b(siapa|siapakah|nama|menjabat|sekarang|saat\s+ini|terbaru|terkini|aktif|periode\s+(?:ini|sekarang))\b/u',
            $normalized
        ) === 1;

        if (!$hasExplicitIncumbentCue && preg_match(
            '/\b(kantor|paten|ppats|mpp|kursi|sound|proyektor|ktp|kk|akta|sktm|sku|umkm|ahli\s+waris|riwayat\s+tanah|letter\s+c|girik|bpn|ajb|shm|bansos|kesos|dtks|pkh|blt|jam|buka|tutup|operasional|surat|pengajuan|peminjaman|pinjam|meminjam|sewa|biaya|tarif|retribusi|syarat|persyaratan|luas|penduduk|struktur\s+organisasi|seksi\s+pelayanan|pelayanan|layanan|menghubungi|hubungi|kontak|whatsapp|wa)\b/u',
            $normalized
        ) === 1) {
            return false;
        }

        if (
            preg_match('/\b(siapa|siapakah|nama)\s+(?:yang\s+(?:kini\s+|sekarang\s+|saat\s+ini\s+)?(?:menjabat|menjadi|jadi)\s+(?:sebagai\s+)?)?(?:(?:penjabat|pj|plt)\s+)?(?:wakil\s+)?(?:presiden|gubernur|bupati|wali\s*kota|walikota|sekretaris\s+camat|sekcam|camat|sekretaris\s+daerah|sekda|kepala\s+desa|kades|sekretaris\s+desa|sekdes|lurah|ketua\s+(?:dprd|dpr|mpr|dpd|kpu|bawaslu|bpd)|kepala\s+(?:dinas|badan|kantor|bagian|bidang|biro|kejaksaan|puskesmas)|kadis|kaban|kakan|menteri|kapolsek|kapolres|kapolresta|kapolda|kapolri|danramil|dandim|danrem|pangdam|kajari|kajati)\b/u', $normalized) === 1
            || preg_match('/\b(?:sekarang|saat\s+ini|terbaru|terkini|aktif|menjabat|periode\s+(?:ini|sekarang))\b/u', $normalized) === 1
        ) {
            return true;
        }

        $words = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) > 10) {
            return false;
        }

        $withoutRoles = trim(preg_replace(self::PUBLIC_OFFICIAL_ROLE_PATTERN, ' ', $normalized) ?? $normalized);
        $remainingTokens = preg_split('/\s+/u', $withoutRoles, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $entityTokens = array_values(array_filter(
            $remainingTokens,
            static fn (string $token): bool => mb_strlen($token) >= 2 && !in_array($token, self::PUBLIC_OFFICIAL_STOPWORDS, true)
        ));

        return count($entityTokens) >= 1;
    }

    /**
     * Detect whether a normalized query requires real-time / external internet information.
     */
    protected function detectExternalWebSignal(string $normalized): ?string
    {
        if (self::matchesPublicOfficialLookup($normalized)) {
            return 'official_incumbent';
        }

        if (
            preg_match('/\b(libur\s+nasional|cuti\s+bersama|hari\s+libur\s+nasional|tanggal\s+merah|skb\s+3\s+menteri|kalender\s+202[4-9]|jadwal\s+(cpns|pppk|snbt|ppdb|pemilu|pilkada))\b/u', $normalized) === 1
            || (
                preg_match('/\b(202[5-9]|tahun\s+ini|bulan\s+ini)\b/u', $normalized) === 1
                && preg_match('/\b(libur|cuti|umk|ump|pppk|cpns|subsidi|bantuan\s+pemerintah|aturan\s+baru|kebijakan|peraturan|regulasi)\b/u', $normalized) === 1
            )
        ) {
            return 'external_schedule';
        }

        if (
            preg_match('/\b(perubahan|revisi|pembaruan|update)\b.*\b(prosedur|aturan|kebijakan|peraturan|regulasi)\b.*\b(pelayanan|layanan|publik|pemerintah|ktp|kk|akta|perizinan|umkm|oss|nib|bpn|pertanahan)\b/u', $normalized) === 1
            || (
                preg_match('/\b(kebijakan|peraturan|regulasi|aturan|standar\s+pelayanan|prosedur\s+pelayanan|pelayanan\s+publik|program\s+pemerintah|kemendagri|permendagri|perpres|perbup|oss|nib|atrbpn)\b/u', $normalized) === 1
                && preg_match('/\b(terbaru|terkini|saat\s+ini|sekarang|perubahan|berubah|revisi|baru|update|resmi\s+pemerintah|202[5-9])\b/u', $normalized) === 1
            )
        ) {
            return 'policy_regulation_update';
        }

        if (preg_match('/\b(berita\s+(terbaru|terkini|hari\s+ini)|info\s+terkini|kabar\s+terbaru|prakiraan\s+cuaca|bmkg)\b/u', $normalized) === 1) {
            return 'external_news';
        }

        return null;
    }

    /**
     * Determine whether an external-web query ALSO relies on internal SIPAF/Kecamatan KB data.
     */
    protected function isHybridInternalAndExternal(string $normalized, string $webSignal): bool
    {
        $hasInternalServiceFocus = preg_match(
            '/\b(sipaf|aula|inventaris|kursi|sound|proyektor|piagam|sertifikat|ktp|kk|akta|mpp|sktm|sku|umkm|ahli\s+waris|riwayat\s+tanah|bansos|kesos|dtks|pkh|blt|jam\s+(buka|tutup|kerja|operasional|pelayanan)|pelayanan\s+kantor|layanan\s+kecamatan|cara\s+meminjam|cara\s+mengajukan)\b/u',
            $normalized
        ) === 1;

        if ($webSignal === 'official_incumbent') {
            return $hasInternalServiceFocus;
        }

        return $hasInternalServiceFocus
            || preg_match('/\b(kecamatan\s+cikampek|kantor\s+kecamatan)\b/u', $normalized) === 1;
    }

    protected function buildWebSearchQuery(string $rawQuery, string $normalized): string
    {
        $clean = trim(preg_replace('/\s+/u', ' ', strip_tags($rawQuery)) ?? $rawQuery);
        if ($clean === '') {
            return '';
        }

        $cleanNorm = $this->normalizeText($clean);

        // Pertahankan perilaku pencarian Camat Cikampek & pejabat wilayah Cikampek yang akurat
        if (str_contains($cleanNorm, 'cikampek')) {
            if (!str_contains($cleanNorm, 'karawang')) {
                $clean .= ' Kabupaten Karawang';
            }

            return trim($clean);
        }

        // Perkaya query pejabat pemerintahan / instansi publik menggunakan konteks resmi Indonesia tanpa mengarang nama pejabat.
        if (preg_match(self::PUBLIC_OFFICIAL_ROLE_PATTERN, $cleanNorm) === 1) {
            if (preg_match('/\b(wakil\s+gubernur|gubernur|pj\s+gubernur|plt\s+gubernur)\b/u', $cleanNorm) === 1) {
                if (preg_match('/\b(jawa\s+barat|jabar)\b/u', $cleanNorm) === 1 && !str_contains($cleanNorm, 'pemprov') && !str_contains($cleanNorm, 'pemerintah')) {
                    $clean .= ' Pemerintah Provinsi Jawa Barat resmi Indonesia';
                } elseif (!str_contains($cleanNorm, 'pemerintah') && !str_contains($cleanNorm, 'pemprov')) {
                    $clean .= ' Pemerintah Provinsi resmi Indonesia';
                } elseif (!str_contains($cleanNorm, 'indonesia')) {
                    $clean .= ' Indonesia';
                }
            } elseif (preg_match('/\b(wakil\s+bupati|bupati|pj\s+bupati|plt\s+bupati)\b/u', $cleanNorm) === 1) {
                if (str_contains($cleanNorm, 'karawang') && !str_contains($cleanNorm, 'pemkab') && !str_contains($cleanNorm, 'pemerintah')) {
                    $clean .= ' Pemerintah Kabupaten Karawang resmi Indonesia';
                } elseif (!str_contains($cleanNorm, 'pemerintah') && !str_contains($cleanNorm, 'pemkab')) {
                    $clean .= ' Pemerintah Kabupaten resmi Indonesia';
                } elseif (!str_contains($cleanNorm, 'indonesia')) {
                    $clean .= ' Indonesia';
                }
            } elseif (preg_match('/\b(wakil\s+wali\s*kota|wali\s*kota|wakil\s+walikota|walikota|pj\s+wali\s*kota|pj\s+walikota)\b/u', $cleanNorm) === 1) {
                if (!str_contains($cleanNorm, 'pemerintah') && !str_contains($cleanNorm, 'pemkot')) {
                    $clean .= ' Pemerintah Kota resmi Indonesia';
                } elseif (!str_contains($cleanNorm, 'indonesia')) {
                    $clean .= ' Indonesia';
                }
            } elseif (preg_match('/\b(kapolsek|kapolres|kapolresta|kapolda|kapolri|danramil|dandim|danrem|pangdam|kajari|kajati)\b/u', $cleanNorm) === 1) {
                if (str_contains($cleanNorm, 'karawang') && !str_contains($cleanNorm, 'indonesia')) {
                    $clean .= ' Kabupaten Karawang resmi Indonesia';
                } elseif (!str_contains($cleanNorm, 'indonesia')) {
                    $clean .= ' resmi Indonesia';
                }
            } elseif (str_contains($cleanNorm, 'karawang') && !str_contains($cleanNorm, 'pemkab') && !str_contains($cleanNorm, 'pemerintah')) {
                $clean .= ' Pemerintah Kabupaten Karawang resmi Indonesia';
            } elseif (!str_contains($cleanNorm, 'pemerintah') && !str_contains($cleanNorm, 'indonesia')) {
                $clean .= ' pemerintah daerah resmi Indonesia';
            }
        }

        return trim($clean);
    }

    protected function normalizeText(string $text): string
    {
        $cleaned = strip_tags($text);
        $cleaned = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $cleaned) ?? '';
        $lower = mb_strtolower(trim($cleaned));
        $alphaNum = preg_replace('/[^\p{L}\p{N}\s\/]+/u', ' ', $lower) ?? '';
        return preg_replace('/\s+/u', ' ', trim($alphaNum)) ?? '';
    }

    protected function containsWholeWord(string $haystack, string $word): bool
    {
        if ($haystack === '' || $word === '') {
            return false;
        }

        return preg_match('/\b' . preg_quote($word, '/') . '\b/u', $haystack) === 1;
    }
}
