<?php

namespace App\Services\AI;

use App\Models\AiKnowledge;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KnowledgeRetrievalService
{
    /**
     * Cache whether MySQL FULLTEXT index is available for the current process/request.
     */
    protected ?bool $hasFullTextIndex = null;

    /**
     * Conversational Indonesian & English stopwords removed during query tokenization.
     */
    protected const STOPWORDS = [
        'yang', 'di', 'ke', 'dari', 'dan', 'atau', 'apa', 'apakah', 'bagaimana',
        'gimana', 'gmna', 'gmn', 'berapa', 'dimana', 'mana', 'siapa', 'kapan',
        'kenapa', 'mengapa', 'saya', 'aku', 'kami', 'kita', 'anda', 'kamu',
        'mau', 'ingin', 'bisa', 'dapat', 'boleh', 'ada', 'saja', 'aja', 'nya',
        'dong', 'nih', 'sih', 'deh', 'kok', 'ya', 'yah', 'kah', 'lah', 'pun',
        'itu', 'ini', 'untuk', 'tentang', 'mengenai', 'soal', 'tolong', 'mohon',
        'kasih', 'tahu', 'info', 'infonya', 'min', 'admin', 'kak', 'pak', 'bu',
        'mas', 'mbak', 'dengan', 'pada', 'oleh', 'sebagai', 'kalau', 'jika',
        'bila', 'saat', 'waktu', 'sudah', 'udah', 'belum', 'lagi', 'tidak',
        'tak', 'bukan', 'gak', 'ga', 'nggak', 'enggak', 'dll', 'dsb', 'yaa',
        'nanya', 'tanya', 'bertanya', 'bantu', 'jelaskan', 'sebutkan',
        'the', 'is', 'are', 'in', 'on', 'of', 'to', 'for', 'me', 'my', 'you', 'your',
        'please', 'can', 'how', 'what', 'where', 'when', 'who',
    ];

    /**
     * High-frequency domain terms weighted low so they cannot qualify irrelevant queries alone.
     */
    protected const GENERIC_DOMAIN_TOKENS = [
        'kecamatan', 'cikampek', 'sipaf', 'layanan', 'pelayanan', 'umum',
        'surat', 'keterangan', 'cara', 'prosedur', 'alur', 'syarat',
        'dokumen', 'berkas', 'online', 'website', 'sistem', 'system',
        'kantor', 'masyarakat', 'warga', 'aju', 'buat', 'urus', 'data',
        'resmi', 'petugas', 'kabupaten', 'karawang', 'cek', 'pantau',
        'permohonan',
    ];

    /**
     * General service overview triggers when query contains no more specific noun.
     */
    protected const OVERVIEW_TRIGGER_TOKENS = [
        'layanan', 'pelayanan', 'sipaf', 'fasilitas', 'menu', 'fitur',
    ];

    /**
     * Core civic domain anchors confirming the user is asking about Kecamatan Cikampek / SIPAF.
     */
    protected const CIVIC_ANCHOR_TOKENS = [
        'kecamatan', 'cikampek', 'sipaf', 'kantor', 'desa',
    ];

    /**
     * Generic attribute/modifier tokens that cannot qualify an out-of-domain query on their own
     * when multiple concrete specific nouns go completely unmatched.
     */
    protected const GENERIC_ATTRIBUTE_TOKENS = [
        'harga', 'biaya', 'tarif', 'bayar', 'gratis', 'alamat', 'lokasi', 'letak',
        'telepon', 'nomor', 'kontak', 'jam', 'buka', 'tutup', 'kerja', 'jadwal',
        'hari', 'tanggal', 'waktu', 'bulan', 'tahun', 'jumlah',
    ];

    /**
     * Pure conversational / greeting expressions that do not require KB lookup.
     */
    protected const CONVERSATIONAL_TOKENS = [
        'halo', 'hai', 'hello', 'hi', 'hey', 'assalamualaikum', 'salam',
        'selamat', 'pagi', 'siang', 'sore', 'malam', 'terima', 'kasih',
        'makasih', 'thanks', 'thank', 'kabar', 'kabarnya', 'oke', 'ok',
        'siap', 'sip', 'baik', 'mantap', 'permisi', 'trims',
    ];

    /**
     * Canonical normalization map for Indonesian slang, typos, and common affixes.
     */
    protected const CANONICAL_MAP = [
        'pinjem' => 'pinjam',
        'minjem' => 'pinjam',
        'minjm' => 'pinjam',
        'pinjm' => 'pinjam',
        'peminjaman' => 'pinjam',
        'meminjam' => 'pinjam',
        'dipinjam' => 'pinjam',
        'pinjamnya' => 'pinjam',
        'peminjman' => 'pinjam',
        'ngurus' => 'urus',
        'mengurus' => 'urus',
        'pengurusan' => 'urus',
        'diurus' => 'urus',
        'ngurusnya' => 'urus',
        'urusnya' => 'urus',
        'bikin' => 'buat',
        'membuat' => 'buat',
        'pembuatan' => 'buat',
        'dibuat' => 'buat',
        'buatnya' => 'buat',
        'ngajuin' => 'aju',
        'ajuin' => 'aju',
        'mengajukan' => 'aju',
        'pengajuan' => 'aju',
        'ajukan' => 'aju',
        'diajukan' => 'aju',
        'persyaratan' => 'syarat',
        'persyaratannya' => 'syarat',
        'syaratnya' => 'syarat',
        'syrat' => 'syarat',
        'berkasnya' => 'berkas',
        'dokumennya' => 'dokumen',
        'biayanya' => 'biaya',
        'harganya' => 'harga',
        'tarifnya' => 'tarif',
        'sewanya' => 'sewa',
        'oprasional' => 'operasional',
        'operasionalnya' => 'operasional',
        'sertipikat' => 'sertifikat',
        'sertifikatnya' => 'sertifikat',
        'piagamnya' => 'piagam',
        'invetaris' => 'inventaris',
        'inventarisnya' => 'inventaris',
        'aulanya' => 'aula',
        'aulla' => 'aula',
        'gedungnya' => 'gedung',
        'kursinya' => 'kursi',
        'krsi' => 'kursi',
        'warisnya' => 'waris',
        'warisan' => 'waris',
        'warriss' => 'waris',
        'desanya' => 'desa',
        'penduduknya' => 'penduduk',
        'warganya' => 'warga',
        'kantornya' => 'kantor',
        'alamatnya' => 'alamat',
        'lokasinya' => 'lokasi',
        'telpon' => 'telepon',
        'telp' => 'telepon',
        'teleponnya' => 'telepon',
        'kontaknya' => 'kontak',
        'hubunginya' => 'hubungi',
        'menghubungi' => 'hubungi',
        'statusnya' => 'status',
        'riwayatnya' => 'riwayat',
        'pendaftaran' => 'daftar',
        'mendaftar' => 'daftar',
        'daftarnya' => 'daftar',
        'kegiatannya' => 'kegiatan',
        'ketersediaan' => 'sedia',
        'tersedia' => 'sedia',
        'pemerintahan' => 'pemerintah',
        'kependudukan' => 'penduduk',
        'kelahirannya' => 'kelahiran',
        'kematiannya' => 'kematian',
        'pertanahan' => 'tanah',
        'bukanya' => 'buka',
        'tutupnya' => 'tutup',
        'kerjanya' => 'kerja',
        'luasnya' => 'luas',
        'wilayahnya' => 'wilayah',
        'stoknya' => 'stok',
        'jamnya' => 'jam',
        'kecamatn' => 'kecamatan',
        'cikamfk' => 'cikampek',
        'sond' => 'sound',
        'pengembaliannya' => 'pengembalian',
        'mengembalikannya' => 'pengembalian',
        'kembalikannya' => 'kembali',
        'mengembalikan' => 'kembali',
        'dikembalikan' => 'kembali',
        'meminjamnya' => 'pinjam',
        'mengajukannya' => 'aju',
        'mengurusnya' => 'urus',
        'penyebabnya' => 'penyebab',
    ];

    /**
     * Domain-specific synonym expansions for query recall (short tokens & civic terms).
     */
    protected const EXPANSION_MAP = [
        'bansos' => ['bantuan', 'sosial', 'kesos'],
        'pc' => ['komputer', 'elektronik'],
        'komputer' => ['pc', 'elektronik'],
        'kk' => ['kartu', 'keluarga'],
        'ktp' => ['ktp-el', 'mpp'],
        'umkm' => ['usaha', 'sku', 'mikro'],
        'sku' => ['usaha', 'umkm'],
        'sktm' => ['mampu', 'miskin'],
        'ajb' => ['jual', 'beli', 'tanah'],
        'sewa' => ['biaya', 'tarif'],
        'harga' => ['biaya', 'tarif'],
        'bayar' => ['biaya', 'retribusi'],
        'gratis' => ['biaya'],
        'buka' => ['operasional', 'jam'],
        'tutup' => ['operasional', 'jam'],
        'kerja' => ['operasional', 'jam'],
        'lokasi' => ['alamat', 'letak'],
        'sound' => ['sound system', 'inventaris'],
        'mic' => ['sound', 'inventaris'],
        'speaker' => ['sound', 'inventaris'],
        'proyektor' => ['inventaris'],
        'kursi' => ['inventaris'],
        'meja' => ['inventaris'],
        'jiwa' => ['penduduk', 'demografi'],
        'populasi' => ['penduduk', 'demografi'],
        'wilayah' => ['luas', 'desa'],
        'riwayat' => ['status'],
        'login' => ['akun', 'daftar'],
        'register' => ['akun', 'daftar'],
        'meninggal' => ['kematian', 'waris'],
        'wafat' => ['kematian', 'waris'],
        'ahli' => ['waris'],
        'waris' => ['ahli'],
        'akta' => ['kelahiran', 'kematian', 'mpp'],
        'tanah' => ['bpn', 'pertanahan'],
    ];

    /**
     * Retrieve top relevant active knowledge entries for a user query.
     *
     * @return array{
     *     documents: Collection<int, array{knowledge: AiKnowledge, score: float, matched_tokens: array<int, string>}>,
     *     strategy: string,
     *     retrieval_time_ms: float,
     *     context_chars: int,
     *     is_conversational: bool,
     *     query_tokens: array<int, string>
     * }
     */
    public function retrieve(string $userQuery, ?int $maxResults = null, ?float $minScore = null): array
    {
        $startTime = microtime(true);

        $maxResults = $maxResults ?? (int) config('services.ai.rag.max_results', 4);
        $maxResults = max(1, min($maxResults, 5));
        $minScore = $minScore ?? (float) config('services.ai.rag.min_score', 3.5);
        $maxContextChars = (int) config('services.ai.rag.max_context_chars', 6000);

        $parsed = $this->prepareQuery($userQuery);

        // Pure greeting / identity question without factual/service tokens
        if ($parsed['is_conversational']) {
            return [
                'documents' => collect(),
                'strategy' => 'conversational',
                'retrieval_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'context_chars' => 0,
                'is_conversational' => true,
                'query_tokens' => $parsed['all_tokens'],
            ];
        }

        if (empty($parsed['all_tokens'])) {
            return [
                'documents' => collect(),
                'strategy' => 'empty_query',
                'retrieval_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'context_chars' => 0,
                'is_conversational' => false,
                'query_tokens' => [],
            ];
        }

        // Step 1: Primary candidate lookup (MySQL FULLTEXT when available + LIKE fallback)
        [$candidates, $strategy] = $this->fetchCandidates($parsed);

        // Step 2: Typo rescue query ONLY if SQL candidate lookup found < 3 candidates
        // and the query has specific words long enough for fuzzy edit-distance matching.
        $hasFuzzyCandidateToken = collect($parsed['specific_tokens'])
            ->contains(fn (string $token) => mb_strlen($token) >= 5);

        if ($candidates->count() < 3 && $hasFuzzyCandidateToken) {
            $rescueCandidates = AiKnowledge::query()
                ->where('is_active', true)
                ->orderBy('priority', 'desc')
                ->limit(100)
                ->get();

            if ($rescueCandidates->isNotEmpty()) {
                $existingIds = $candidates->pluck('id')->all();
                foreach ($rescueCandidates as $row) {
                    if (!in_array($row->id, $existingIds, true)) {
                        $row->setAttribute('ft_score', 0);
                        $candidates->push($row);
                    }
                }
                $strategy .= '+fuzzy_rescue';
            }
        }

        if ($candidates->isEmpty()) {
            return [
                'documents' => collect(),
                'strategy' => $strategy . ':no_active_candidates',
                'retrieval_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'context_chars' => 0,
                'is_conversational' => false,
                'query_tokens' => $parsed['all_tokens'],
            ];
        }

        // Step 3: Score, enforce minimum relevance threshold, sort, and cap to 3-5 entries
        $scored = $candidates
            ->map(fn (AiKnowledge $item) => $this->scoreCandidate($item, $parsed))
            ->filter(fn (array $entry) => $entry['score'] >= $minScore)
            ->sort(function (array $a, array $b) {
                if ($a['score'] !== $b['score']) {
                    return $b['score'] <=> $a['score'];
                }
                $prioDiff = (int) $b['knowledge']->priority <=> (int) $a['knowledge']->priority;
                if ($prioDiff !== 0) {
                    return $prioDiff;
                }
                return (int) $a['knowledge']->id <=> (int) $b['knowledge']->id;
            })
            ->values();

        $selected = collect();
        $accumulatedChars = 0;

        foreach ($scored as $entry) {
            if ($selected->count() >= $maxResults) {
                break;
            }

            $entryChars = mb_strlen($entry['knowledge']->category)
                + mb_strlen($entry['knowledge']->title)
                + mb_strlen($entry['knowledge']->answer)
                + 24;

            if ($selected->isNotEmpty() && ($accumulatedChars + $entryChars) > $maxContextChars) {
                break;
            }

            $selected->push($entry);
            $accumulatedChars += $entryChars;
        }

        return [
            'documents' => $selected,
            'strategy' => $selected->isEmpty() ? $strategy . ':below_threshold' : $strategy,
            'retrieval_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'context_chars' => $accumulatedChars,
            'is_conversational' => false,
            'query_tokens' => $parsed['all_tokens'],
        ];
    }

    /**
     * Normalize, clean, tokenize, and expand user query without altering user intent.
     *
     * @return array{
     *     normalized_text: string,
     *     all_tokens: array<int, string>,
     *     specific_tokens: array<int, string>,
     *     generic_tokens: array<int, string>,
     *     expansions: array<int, string>,
     *     phrases: array<int, string>,
     *     is_overview_query: bool,
     *     is_conversational: bool
     * }
     */
    public function prepareQuery(string $rawQuery): array
    {
        $cleaned = strip_tags($rawQuery);
        $cleaned = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $cleaned) ?? '';
        $lower = mb_strtolower(trim($cleaned));

        // Handle "dimana <kantor/kecamatan/cikampek>" as location intent
        $locationHint = str_contains($lower, 'dimana')
            && (str_contains($lower, 'kantor') || str_contains($lower, 'kecamatan') || str_contains($lower, 'cikampek'));

        $onlyAlphaNum = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $lower) ?? '';
        $onlyAlphaNum = preg_replace('/\s+/u', ' ', trim($onlyAlphaNum)) ?? '';

        $rawWords = $onlyAlphaNum === '' ? [] : explode(' ', $onlyAlphaNum);

        // Detect conversational identity/greetings and small-talk fillers before token extraction
        $isIdentityQuestion = (str_contains($onlyAlphaNum, 'siapa kamu') || str_contains($onlyAlphaNum, 'kamu siapa') || str_contains($onlyAlphaNum, 'siapa nama kamu'));
        $hasSmallTalkFiller = preg_match('/\b(apa\s+kabar(nya)?(\s+hari\s+ini)?|kabar(nya)?\s+(hari\s+ini|baik)|gimana\s+kabar(nya)?)\b/u', $onlyAlphaNum) === 1;
        $cleanedForConversational = $hasSmallTalkFiller
            ? trim(preg_replace('/\b(apa\s+kabar(nya)?(\s+hari\s+ini)?|kabar(nya)?\s+(hari\s+ini|baik)|gimana\s+kabar(nya)?)\b/u', ' ', $onlyAlphaNum) ?? $onlyAlphaNum)
            : $onlyAlphaNum;
        $wordsForCheck = $cleanedForConversational === '' ? [] : preg_split('/\s+/u', $cleanedForConversational, -1, PREG_SPLIT_NO_EMPTY);

        $nonStopwords = [];
        $conversationalHits = $hasSmallTalkFiller ? 1 : 0;

        foreach ($rawWords as $word) {
            if (mb_strlen($word) < 2) {
                continue;
            }
            if ($hasSmallTalkFiller && in_array($word, ['hari', 'ini'], true) && count($wordsForCheck) <= 2) {
                $conversationalHits++;
                continue;
            }
            if (in_array($word, self::CONVERSATIONAL_TOKENS, true)) {
                $conversationalHits++;
                continue;
            }
            if (in_array($word, self::STOPWORDS, true)) {
                continue;
            }
            $canonical = self::CANONICAL_MAP[$word] ?? $word;
            $nonStopwords[] = $canonical;
        }

        $nonStopwords = array_values(array_unique($nonStopwords));

        if ($locationHint && !in_array('alamat', $nonStopwords, true)) {
            $nonStopwords[] = 'alamat';
        }

        // Pure greeting / identity question when no service/topic words remain
        $isConversational = ($isIdentityQuestion && count($nonStopwords) === 0)
            || ($conversationalHits > 0 && count($nonStopwords) === 0);

        $specificTokens = [];
        $genericTokens = [];
        $expansions = [];

        foreach ($nonStopwords as $token) {
            if (in_array($token, self::GENERIC_DOMAIN_TOKENS, true)) {
                $genericTokens[] = $token;
            } else {
                $specificTokens[] = $token;
            }

            if (isset(self::EXPANSION_MAP[$token])) {
                foreach (self::EXPANSION_MAP[$token] as $exp) {
                    if (!in_array($exp, $nonStopwords, true)) {
                        $expansions[] = $exp;
                    }
                }
            }
        }

        // Multi-word expansion for "bantuan sosial" -> "bansos", "kesos"
        if (str_contains($onlyAlphaNum, 'bantuan sosial') || (in_array('bantuan', $specificTokens, true) && in_array('sosial', $specificTokens, true))) {
            foreach (['bansos', 'kesos'] as $exp) {
                if (!in_array($exp, $nonStopwords, true)) {
                    $expansions[] = $exp;
                }
            }
        }

        $expansions = array_values(array_unique($expansions));

        // Build 2-word consecutive phrases from canonicalized meaningful tokens
        $phrases = [];
        $count = count($nonStopwords);
        for ($i = 0; $i < $count - 1; $i++) {
            $phrases[] = $nonStopwords[$i] . ' ' . $nonStopwords[$i + 1];
        }

        $isOverviewQuery = empty($specificTokens)
            && !empty(array_intersect($genericTokens, self::OVERVIEW_TRIGGER_TOKENS));

        return [
            'normalized_text' => implode(' ', $nonStopwords),
            'all_tokens' => $nonStopwords,
            'specific_tokens' => $specificTokens,
            'generic_tokens' => $genericTokens,
            'expansions' => $expansions,
            'phrases' => $phrases,
            'is_overview_query' => $isOverviewQuery,
            'is_conversational' => $isConversational,
        ];
    }

    /**
     * Fetch candidate records from database using MySQL FULLTEXT (if indexed) and keyword LIKE fallback.
     *
     * @return array{0: Collection<int, AiKnowledge>, 1: string}
     */
    protected function fetchCandidates(array $parsed): array
    {
        $specificAndExp = array_values(array_unique(array_merge(
            $parsed['specific_tokens'],
            $parsed['expansions']
        )));

        // Do not dilute SQL LIKE candidate pool (LIMIT 20) with ubiquitous generic tokens
        // ("kecamatan", "cikampek", "sipaf") when concrete specific terms exist.
        $sqlTerms = !empty($specificAndExp)
            ? array_slice($specificAndExp, 0, 12)
            : array_slice(array_values(array_unique($parsed['generic_tokens'])), 0, 12);

        $ftSearchString = implode(' ', $sqlTerms);
        $useFullText = $this->hasFullTextIndex() && $ftSearchString !== '';

        if ($useFullText) {
            try {
                $candidates = AiKnowledge::query()
                    ->select('ai_knowledges.*')
                    ->selectRaw(
                        'MATCH(category, title, question, keywords, answer) AGAINST(? IN NATURAL LANGUAGE MODE) AS ft_score',
                        [$ftSearchString]
                    )
                    ->where('is_active', true)
                    ->where(function ($q) use ($ftSearchString, $sqlTerms) {
                        $q->whereRaw(
                            'MATCH(category, title, question, keywords, answer) AGAINST(? IN NATURAL LANGUAGE MODE)',
                            [$ftSearchString]
                        );

                        // Compensate MySQL InnoDB min token size (< 3 chars e.g. pc, kk) & canonical stems
                        foreach ($sqlTerms as $term) {
                            $like = '%' . $this->escapeLike($term) . '%';
                            $q->orWhere('keywords', 'like', $like)
                                ->orWhere('title', 'like', $like)
                                ->orWhere('question', 'like', $like)
                                ->orWhere('category', 'like', $like);
                        }
                    })
                    ->orderByDesc('ft_score')
                    ->orderByDesc('priority')
                    ->limit(20)
                    ->get();

                return [$candidates, 'mysql_fulltext+keyword'];
            } catch (QueryException $e) {
                $this->hasFullTextIndex = false;
            }
        }

        // Safe SQL keyword fallback (when FULLTEXT index is not yet migrated or on SQLite)
        $candidates = AiKnowledge::query()
            ->where('is_active', true)
            ->where(function ($q) use ($sqlTerms) {
                foreach ($sqlTerms as $term) {
                    $like = '%' . $this->escapeLike($term) . '%';
                    $q->orWhere('keywords', 'like', $like)
                        ->orWhere('title', 'like', $like)
                        ->orWhere('question', 'like', $like)
                        ->orWhere('category', 'like', $like)
                        ->orWhere('answer', 'like', $like);
                }
            })
            ->orderByDesc('priority')
            ->limit(20)
            ->get()
            ->each(fn (AiKnowledge $row) => $row->setAttribute('ft_score', 0));

        return [$candidates, 'eloquent_keyword'];
    }

    /**
     * Score a candidate document across weighted fields, coverage, FULLTEXT score, and priority.
     *
     * @return array{knowledge: AiKnowledge, score: float, matched_tokens: array<int, string>}
     */
    protected function scoreCandidate(AiKnowledge $item, array $parsed): array
    {
        $categoryText = $this->normalizeFieldText((string) $item->category);
        $titleText = $this->normalizeFieldText((string) $item->title);
        $questionText = $this->normalizeFieldText((string) $item->question);
        $keywordsText = $this->normalizeFieldText((string) ($item->keywords ?? ''));
        $answerText = $this->normalizeFieldText((string) $item->answer);

        $metadataWords = array_values(array_unique(array_merge(
            $this->extractUniqueWords(implode(' ', [$categoryText, $titleText, $questionText, $keywordsText])),
            $this->extractRawWords(implode(' ', [
                (string) $item->category,
                (string) $item->title,
                (string) $item->question,
                (string) ($item->keywords ?? ''),
            ]))
        )));

        $score = 0.0;
        $matchedSpecific = [];
        $matchedOriginalSpecific = [];
        $matchedMetadataTokens = 0;

        // 1. Score specific user query tokens
        foreach ($parsed['specific_tokens'] as $token) {
            $tokenScore = 0.0;
            $matched = false;
            $matchedInMetadata = false;

            if ($this->containsWordOrStem($keywordsText, $token)) {
                $tokenScore += 6.0;
                $matched = true;
                $matchedInMetadata = true;
            }
            if ($this->containsWordOrStem($titleText, $token)) {
                $tokenScore += 5.0;
                $matched = true;
                $matchedInMetadata = true;
            }
            if ($this->containsWordOrStem($questionText, $token)) {
                $tokenScore += 4.0;
                $matched = true;
                $matchedInMetadata = true;
            }
            if ($this->containsWordOrStem($categoryText, $token)) {
                $tokenScore += 3.5;
                $matched = true;
                $matchedInMetadata = true;
            }
            if ($this->containsWordOrStem($answerText, $token)) {
                $tokenScore += 2.0;
                $matched = true;
            }

            // Typo-tolerant fuzzy check against metadata words when exact match fails
            if (!$matched && mb_strlen($token) >= 5 && $this->hasFuzzyMatch($token, $metadataWords)) {
                $tokenScore += 3.6;
                $matched = true;
                $matchedInMetadata = true;
            }

            if ($matched) {
                $matchedSpecific[] = $token;
                $matchedOriginalSpecific[] = $token;
                if ($matchedInMetadata) {
                    $matchedMetadataTokens++;
                }
                $score += $tokenScore;
            }
        }

        // 2. Score expanded synonym tokens (also counts as matching the parent intent)
        foreach ($parsed['expansions'] as $exp) {
            $expMatched = false;
            $expScore = 0.0;
            $expInMetadata = false;

            if ($this->containsWordOrStem($keywordsText, $exp)) {
                $expScore += 4.5;
                $expMatched = true;
                $expInMetadata = true;
            }
            if ($this->containsWordOrStem($titleText, $exp)) {
                $expScore += 3.8;
                $expMatched = true;
                $expInMetadata = true;
            }
            if ($this->containsWordOrStem($questionText, $exp)) {
                $expScore += 3.0;
                $expMatched = true;
                $expInMetadata = true;
            }
            if ($this->containsWordOrStem($categoryText, $exp)) {
                $expScore += 2.5;
                $expMatched = true;
                $expInMetadata = true;
            }
            if ($this->containsWordOrStem($answerText, $exp)) {
                $expScore += 1.5;
                $expMatched = true;
            }

            if ($expMatched) {
                $matchedSpecific[] = $exp;
                if ($expInMetadata) {
                    $matchedMetadataTokens++;
                }
                foreach ($parsed['specific_tokens'] as $parentToken) {
                    if (in_array($exp, self::EXPANSION_MAP[$parentToken] ?? [], true)) {
                        $matchedOriginalSpecific[] = $parentToken;
                    }
                }
                $score += $expScore;
            }
        }

        // Anti-false-positive coverage guard:
        // If user provided specific non-generic terms and NONE matched (even via synonyms/fuzzy),
        // or only generic attribute tokens / incidental body-only words matched while concrete
        // subject nouns missed completely without civic anchors, disqualify the candidate.
        $specificCount = count($parsed['specific_tokens']);
        $matchedSpecificUnique = array_values(array_unique($matchedSpecific));
        $matchedOriginalUnique = array_values(array_unique($matchedOriginalSpecific));

        if ($specificCount > 0 && count($matchedSpecificUnique) === 0) {
            return [
                'knowledge' => $item,
                'score' => 0.0,
                'matched_tokens' => [],
            ];
        }

        $unmatchedSpecificCount = $specificCount - count($matchedOriginalUnique);
        $onlyAttributeMatches = !empty($matchedOriginalUnique)
            && empty(array_diff($matchedOriginalUnique, self::GENERIC_ATTRIBUTE_TOKENS));
        $hasCivicAnchor = !empty(array_intersect($parsed['generic_tokens'], self::CIVIC_ANCHOR_TOKENS));

        if ($onlyAttributeMatches && $unmatchedSpecificCount >= 2 && !$hasCivicAnchor) {
            return [
                'knowledge' => $item,
                'score' => 0.0,
                'matched_tokens' => [],
            ];
        }

        if ($matchedMetadataTokens === 0 && !$hasCivicAnchor && $unmatchedSpecificCount >= 1) {
            return [
                'knowledge' => $item,
                'score' => 0.0,
                'matched_tokens' => [],
            ];
        }

        // 3. Score generic domain tokens lightly (or boost overview queries like "Pelayanan apa saja?")
        foreach ($parsed['generic_tokens'] as $token) {
            $genericWeight = $parsed['is_overview_query'] ? 2.5 : 0.35;
            if (
                $this->containsWordOrStem($keywordsText, $token)
                || $this->containsWordOrStem($titleText, $token)
                || $this->containsWordOrStem($categoryText, $token)
            ) {
                $score += $genericWeight;
            } elseif ($this->containsWordOrStem($answerText, $token)) {
                $score += ($parsed['is_overview_query'] ? 1.2 : 0.15);
            }
        }

        if ($parsed['is_overview_query'] && mb_strtolower($item->category) === 'pelayanan sipaf') {
            $score += 3.0;
        }

        // 4. Multi-word phrase match bonus
        foreach ($parsed['phrases'] as $phrase) {
            if (str_contains($keywordsText, $phrase) || str_contains($titleText, $phrase) || str_contains($questionText, $phrase)) {
                $score += 4.0;
            }
        }

        // 5. Coverage ratio bonus for multi-intent queries
        if ($specificCount >= 2 && count($matchedSpecificUnique) >= 2) {
            $coverageRatio = min(1.0, count($matchedSpecificUnique) / $specificCount);
            $score += 3.0 * $coverageRatio;
        }

        // 6. MySQL FULLTEXT relevance bonus
        $ftScore = (float) ($item->getAttribute('ft_score') ?? 0.0);
        if ($ftScore > 0) {
            $score += min($ftScore * 1.2, 6.0);
        }

        // 7. Priority tie-breaker (bounded so priority alone cannot qualify weak matches)
        if ($score > 0) {
            $priorityBoost = min(max((int) $item->priority, 0), 50) * 0.05;
            $score += $priorityBoost;
        }

        return [
            'knowledge' => $item,
            'score' => round($score, 2),
            'matched_tokens' => $matchedSpecificUnique,
        ];
    }

    /**
     * Check whether the FULLTEXT index on ai_knowledges exists in MySQL.
     */
    public function hasFullTextIndex(): bool
    {
        if ($this->hasFullTextIndex !== null) {
            return $this->hasFullTextIndex;
        }

        try {
            $connection = DB::connection();
            if (!in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
                return $this->hasFullTextIndex = false;
            }

            $indexes = $connection->select("SHOW INDEX FROM `ai_knowledges` WHERE Index_type = 'FULLTEXT'");
            if (empty($indexes)) {
                return $this->hasFullTextIndex = false;
            }

            $grouped = [];
            foreach ($indexes as $idx) {
                $keyName = $idx->Key_name ?? '';
                $colName = $idx->Column_name ?? '';
                if ($keyName !== '' && $colName !== '') {
                    $grouped[$keyName][] = $colName;
                }
            }

            $required = ['category', 'title', 'question', 'keywords', 'answer'];
            foreach ($grouped as $cols) {
                if (empty(array_diff($required, $cols))) {
                    return $this->hasFullTextIndex = true;
                }
            }

            return $this->hasFullTextIndex = false;
        } catch (\Throwable $e) {
            return $this->hasFullTextIndex = false;
        }
    }

    /**
     * Reset cached FULLTEXT index flag (useful in test environments).
     */
    public function resetIndexCache(): void
    {
        $this->hasFullTextIndex = null;
    }

    protected function normalizeFieldText(string $text): string
    {
        $lower = mb_strtolower($text);
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $lower) ?? '';
        $words = preg_split('/\s+/u', trim($clean), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $mapped = array_map(
            fn (string $w) => self::CANONICAL_MAP[$w] ?? $w,
            $words
        );

        return implode(' ', $mapped);
    }

    /**
     * @return array<int, string>
     */
    protected function extractUniqueWords(string $normalizedText): array
    {
        $words = preg_split('/\s+/u', trim($normalizedText), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        return array_values(array_unique(array_filter(
            $words,
            fn (string $w) => mb_strlen($w) >= 3
        )));
    }

    /**
     * @return array<int, string>
     */
    protected function extractRawWords(string $rawText): array
    {
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', mb_strtolower($rawText)) ?? '';
        return $this->extractUniqueWords($clean);
    }

    protected function containsWordOrStem(string $normalizedHaystack, string $needle): bool
    {
        if ($normalizedHaystack === '' || $needle === '') {
            return false;
        }

        if (str_contains($needle, ' ')) {
            return str_contains($normalizedHaystack, $needle);
        }

        // Exact token boundary match or clear prefix match for long Indonesian words (>= 5 chars)
        $escaped = preg_quote($needle, '/');
        if (preg_match('/\b' . $escaped . '\b/u', $normalizedHaystack) === 1) {
            return true;
        }

        if (mb_strlen($needle) >= 5 && str_contains($normalizedHaystack, $needle)) {
            return true;
        }

        return false;
    }

    /**
     * Bounded Levenshtein + similarity matching for user typo tolerance.
     *
     * @param array<int, string> $candidateWords
     */
    protected function hasFuzzyMatch(string $token, array $candidateWords): bool
    {
        $tokenLen = mb_strlen($token);
        if ($tokenLen < 5 || $tokenLen > 30) {
            return false;
        }

        foreach ($candidateWords as $candidate) {
            $candLen = mb_strlen($candidate);
            if ($candLen < 5 || $candLen > 30) {
                continue;
            }

            $maxDistance = ($tokenLen >= 7 && $candLen >= 6) ? 2 : 1;
            if (abs($candLen - $tokenLen) > $maxDistance) {
                continue;
            }

            // Require matching first letter for distance 2 or short tokens to avoid unrelated word collisions
            if (($maxDistance === 2 || $tokenLen <= 6) && mb_substr($token, 0, 1) !== mb_substr($candidate, 0, 1)) {
                continue;
            }

            $distance = levenshtein($token, $candidate);
            if ($distance <= $maxDistance) {
                similar_text($token, $candidate, $percent);
                $minPercent = $maxDistance === 2 ? 80.0 : 75.0;
                if ($percent >= $minPercent) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}
