<?php

namespace App\Services\AI;

class ConversationContextService
{
    protected const MAX_HISTORY_MESSAGES = 6;
    protected const MAX_MESSAGE_CHARS = 900;
    protected const MAX_TOTAL_HISTORY_CHARS = 3500;

    /**
     * Canonical SIPAF topic buckets for continuity & topic-shift detection.
     */
    protected const SIPAF_TOPIC_PATTERNS = [
        'aula' => [
            'label' => 'Peminjaman Aula Kecamatan Cikampek',
            'anchor' => 'pinjam aula kecamatan',
            'terms' => ['aula', 'gedung kecamatan'],
        ],
        'inventaris' => [
            'label' => 'Peminjaman Barang Inventaris Kecamatan',
            'anchor' => 'pinjam barang inventaris',
            'terms' => ['inventaris', 'kursi', 'sound system', 'proyektor', 'speaker'],
            'exact' => ['pc', 'sound', 'mic'],
            'contextual_terms' => ['komputer'],
        ],
        'sertifikat_tanah' => [
            'label' => 'Pertanahan / Sertifikat Tanah (Kewenangan BPN)',
            'anchor' => 'sertifikat tanah ajb bpn',
            'terms' => ['sertifikat tanah', 'jual beli tanah', 'riwayat tanah', 'letter c', 'girik', 'bpn', 'ppats', 'pertanahan'],
            'exact' => ['ajb', 'shm'],
        ],
        'sertifikat_piagam' => [
            'label' => 'Pengajuan Sertifikat / Piagam Penghargaan SIPAF',
            'anchor' => 'pengajuan sertifikat piagam sipaf',
            'terms' => ['piagam', 'sertifikat penghargaan', 'sertifikat kegiatan'],
        ],
        'ktp_kk_mpp' => [
            'label' => 'Layanan KTP, KK, & Akta (Mall Pelayanan Publik / MPP)',
            'anchor' => 'ktp kk akta mpp',
            'terms' => ['kartu keluarga', 'akta kelahiran', 'akta kematian', 'mall pelayanan publik', 'disdukcapil'],
            'exact' => ['ktp', 'kk', 'mpp'],
        ],
        'ahli_waris' => [
            'label' => 'Persyaratan Surat Keterangan Ahli Waris',
            'anchor' => 'surat keterangan ahli waris',
            'terms' => ['ahli waris', 'warisan'],
            'exact' => ['waris'],
        ],
        'sktm' => [
            'label' => 'Persyaratan Surat Keterangan Tidak Mampu (SKTM)',
            'anchor' => 'sktm surat keterangan tidak mampu',
            'terms' => ['tidak mampu'],
            'exact' => ['sktm'],
        ],
        'sku_umkm' => [
            'label' => 'Surat Keterangan Usaha (SKU) / Izin Usaha UMKM',
            'anchor' => 'sku surat keterangan usaha umkm',
            'terms' => ['surat keterangan usaha', 'izin usaha', 'usaha mikro'],
            'exact' => ['sku'],
        ],
        'bansos' => [
            'label' => 'Informasi Bantuan Sosial (Bansos / Kesos)',
            'anchor' => 'bantuan sosial bansos kesos',
            'terms' => ['bansos', 'bantuan sosial', 'kesos'],
            'exact' => ['dtks', 'pkh', 'blt'],
        ],
        'profil_kecamatan' => [
            'label' => 'Profil, Wilayah 10 Desa & Demografi Kecamatan Cikampek',
            'anchor' => 'profil luas wilayah penduduk desa cikampek',
            'terms' => ['luas wilayah', 'luas desa', 'jumlah penduduk', 'jumlah jiwa', 'demografi', '10 desa', 'struktur organisasi', 'struktur organisasi kantor camat', 'kalihurip', 'kamojing', 'dawuan'],
        ],
        'jam_kontak' => [
            'label' => 'Alamat, Kontak & Jam Pelayanan Kantor Kecamatan Cikampek',
            'anchor' => 'alamat kontak jam pelayanan kantor kecamatan',
            'terms' => ['jam buka', 'jam tutup', 'jam kerja', 'jam operasional', 'jam pelayanan', 'alamat kantor', 'nomor telepon', 'telepon kantor', 'kantor camat'],
        ],
        'akun_riwayat' => [
            'label' => 'Akun Masyarakat, Status & Riwayat Pengajuan SIPAF',
            'anchor' => 'akun riwayat status pengajuan sipaf',
            'terms' => ['status pengajuan', 'riwayat pengajuan', 'daftar akun', 'akun masyarakat', 'login sipaf'],
        ],
    ];

    /**
     * Inheritable concrete nouns from a prior General turn when user bridges into Kecamatan/SIPAF.
     */
    protected const CROSS_DOMAIN_BRIDGE_TERMS = [
        'aula' => ['aula', 'pinjam aula kecamatan'],
        'inventaris' => ['inventaris', 'pinjam barang inventaris'],
        'kursi' => ['kursi', 'pinjam kursi inventaris'],
        'sound' => ['sound', 'pinjam sound system inventaris'],
        'proyektor' => ['proyektor', 'pinjam proyektor inventaris'],
        'komputer' => ['komputer', 'pinjam komputer inventaris'],
        'piagam' => ['piagam', 'pengajuan sertifikat piagam sipaf'],
        'sertifikat' => ['sertifikat', 'sertifikat piagam sipaf'],
        'waris' => ['ahli waris', 'surat keterangan ahli waris'],
        'sktm' => ['sktm', 'sktm surat keterangan tidak mampu'],
        'umkm' => ['umkm', 'sku surat keterangan usaha umkm'],
        'bansos' => ['bansos', 'bantuan sosial kesos'],
        'tanah' => ['tanah', 'administrasi pertanahan bpn'],
    ];

    /**
     * Follow-up cues expecting an administrative / procedural / service topic.
     */
    protected const ADMINISTRATIVE_CUES = [
        'syarat', 'syaratnya', 'persyaratan', 'persyaratannya', 'berkas', 'berkasnya',
        'dokumen', 'dokumennya', 'biaya', 'biayanya', 'harga', 'harganya',
        'tarif', 'tarifnya', 'sewa', 'sewanya', 'gratis', 'bayar',
        'stok', 'stoknya', 'ketersediaan', 'pengembalian', 'pengembaliannya',
        'mengembalikannya', 'dikembalikan', 'ajukan', 'mengajukannya', 'ajukannya',
        'pengajuannya', 'urus', 'mengurusnya', 'ngurusnya', 'pinjam', 'meminjamnya',
        'dipinjam', 'minjamnya', 'disetujui', 'ditolak', 'setuju', 'daftar',
        'daftarnya', 'mendaftarnya', 'pendaftarannya', 'alur', 'alurnya',
        'prosedur', 'prosedurnya', 'loket', 'petugas', 'jam', 'buka', 'tutup',
    ];

    /**
     * Follow-up cues typically extending a conceptual / general knowledge topic.
     */
    protected const GENERAL_ANALYSIS_CUES = [
        'penyebab', 'penyebabnya', 'sebab', 'sebabnya', 'akibat', 'akibatnya',
        'dampak', 'dampaknya', 'contoh', 'contohnya', 'mengatasi', 'mengatasinya',
        'mencegah', 'mencegahnya', 'solusi', 'solusinya', 'manfaat', 'manfaatnya',
        'faktor', 'faktornya', 'ciri', 'cirinya', 'jenis', 'jenisnya',
        'fungsi', 'fungsinya', 'tujuan', 'tujuannya', 'pengertian', 'artinya',
        'definisinya', 'sejarahnya',
    ];

    /**
     * Referential / underspecified cues that indicate dependence on prior context.
     */
    protected const REFERENTIAL_CUES = [
        'itu', 'tersebut', 'tadi', 'ini', 'selanjutnya', 'lanjutannya', 'lanjut',
        'kalau', 'terus', 'lalu', 'bagaimana', 'gimana', 'gmna', 'gmn',
        'apa', 'berapa', 'dimana', 'kapan', 'prosesnya', 'caranya', 'langkahnya',
        'maksudnya', 'waktunya', 'durasi', 'durasinya', 'lama', 'selesainya',
    ];

    /**
     * Tokens that do NOT establish a concrete standalone subject on their own.
     */
    protected const NON_SUBJECT_TOKENS = [
        'yang', 'di', 'ke', 'dari', 'dan', 'atau', 'apa', 'apakah', 'bagaimana',
        'gimana', 'gmna', 'gmn', 'berapa', 'dimana', 'mana', 'siapa', 'kapan',
        'kenapa', 'mengapa', 'saya', 'aku', 'kami', 'kita', 'anda', 'kamu',
        'mau', 'ingin', 'bisa', 'dapat', 'boleh', 'ada', 'saja', 'aja', 'nya',
        'dong', 'nih', 'sih', 'deh', 'kok', 'ya', 'yah', 'kah', 'lah', 'pun',
        'itu', 'ini', 'untuk', 'tentang', 'mengenai', 'soal', 'tolong', 'mohon',
        'kasih', 'tahu', 'info', 'infonya', 'min', 'admin', 'kak', 'pak', 'bu',
        'mas', 'mbak', 'dengan', 'pada', 'oleh', 'sebagai', 'kalau', 'jika',
        'bila', 'saat', 'waktu', 'sudah', 'udah', 'belum', 'lagi', 'tidak',
        'tak', 'bukan', 'gak', 'ga', 'nggak', 'enggak', 'terus', 'lalu', 'tadi',
        'tersebut', 'selanjutnya', 'lanjut', 'lanjutannya', 'maksudnya',
        'cara', 'caranya', 'proses', 'prosesnya', 'langkah', 'langkahnya',
        'alur', 'alurnya', 'prosedur', 'prosedurnya', 'syarat', 'syaratnya',
        'persyaratan', 'persyaratannya', 'berkas', 'berkasnya', 'dokumen', 'dokumennya',
        'biaya', 'biayanya', 'harga', 'harganya', 'tarif', 'tarifnya', 'sewa', 'sewanya',
        'gratis', 'bayar', 'stok', 'stoknya', 'ketersediaan', 'pengembalian',
        'pengembaliannya', 'mengembalikannya', 'dikembalikan', 'mengembalikan',
        'pinjam', 'meminjam', 'meminjamnya', 'dipinjam', 'pinjem', 'minjam', 'minjem',
        'aju', 'ajukan', 'mengajukan', 'mengajukannya', 'ajukannya', 'pengajuan', 'pengajuannya',
        'urus', 'mengurus', 'mengurusnya', 'ngurus', 'ngurusnya', 'pengurusan',
        'buat', 'bikin', 'membuat', 'membuatnya', 'daftar', 'mendaftar', 'mendaftarnya',
        'daftarnya', 'pendaftaran', 'pendaftarannya', 'disetujui', 'ditolak', 'setuju',
        'selesai', 'selesainya', 'lama', 'durasi', 'durasinya', 'jam', 'buka', 'tutup',
        'penyebab', 'penyebabnya', 'akibat', 'akibatnya', 'dampak', 'dampaknya',
        'contoh', 'contohnya', 'manfaat', 'manfaatnya', 'solusi', 'solusinya',
        'mengatasi', 'mengatasinya', 'mencegah', 'mencegahnya', 'jelaskan', 'lebih', 'detail',
        'lebih', 'lanjut', 'kira', 'misal', 'sekarang', 'hari', 'ini', 'dong',
    ];

    protected KnowledgeRetrievalService $retrievalService;

    public function __construct(?KnowledgeRetrievalService $retrievalService = null)
    {
        $this->retrievalService = $retrievalService ?? new KnowledgeRetrievalService();
    }

    /**
     * Analyze conversation history + current user message to resolve active topic,
     * filter out stale/irrelevant turns, detect ambiguity, and build bounded context.
     *
     * @param string $userMessage
     * @param array<int, array{role?: string, text?: string, isError?: bool}> $history
     * @param array{
     *     is_conversational?: bool,
     *     has_civic_anchor?: bool,
     *     is_conceptual_general?: bool,
     *     is_overview_query?: bool
     * } $signals
     * @return array{
     *     sanitized_history: array,
     *     relevant_history: array,
     *     active_domain: string,
     *     active_topic: ?string,
     *     active_topic_label: ?string,
     *     resolution_type: string,
     *     topic_shifted: bool,
     *     is_ambiguous: bool,
     *     resolved_query: string,
     *     context_note: ?string
     * }
     */
    public function analyze(string $userMessage, array $history = [], array $signals = []): array
    {
        $sanitizedHistory = $this->sanitizeHistory($history);
        $normalized = $this->normalizeText($userMessage);

        $isConversational = (bool) ($signals['is_conversational'] ?? false);
        $hasCivicAnchor = (bool) ($signals['has_civic_anchor'] ?? false);
        $isConceptualGeneral = (bool) ($signals['is_conceptual_general'] ?? false);
        $isOverviewQuery = (bool) ($signals['is_overview_query'] ?? false);
        $hasWebSignal = (bool) ($signals['has_web_signal'] ?? false);
        $hasHybridSignal = (bool) ($signals['has_hybrid_signal'] ?? false);

        if ($isConversational || $normalized === '') {
            return [
                'sanitized_history' => $sanitizedHistory,
                'relevant_history' => $this->boundHistory($sanitizedHistory),
                'active_domain' => 'NONE',
                'active_topic' => null,
                'active_topic_label' => null,
                'resolution_type' => 'conversational',
                'topic_shifted' => false,
                'is_ambiguous' => false,
                'resolved_query' => $userMessage,
                'context_note' => null,
            ];
        }

        $turns = $this->pairHistoryTurns($sanitizedHistory);
        $words = $normalized === '' ? [] : (preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        // 0. Explicit real-time / external Web Search transition (non-hybrid)
        if ($hasWebSignal && !$hasHybridSignal) {
            $latestTurn = !empty($turns) ? $turns[count($turns) - 1] : null;
            $topicShifted = $latestTurn !== null && in_array($latestTurn['domain'], ['SIPAF', 'GENERAL'], true);
            $relevantMessages = $topicShifted ? [] : $this->filterWebOrNeutralTurns($turns);
            $webLabel = $this->extractGeneralTopicLabel($normalized) ?? 'Informasi Publik Terkini';

            return [
                'sanitized_history' => $sanitizedHistory,
                'relevant_history' => $this->boundHistory($relevantMessages),
                'active_domain' => 'WEB',
                'active_topic' => $webLabel,
                'active_topic_label' => $webLabel,
                'resolution_type' => $topicShifted ? 'topic_shift' : 'direct_web',
                'topic_shifted' => $topicShifted,
                'is_ambiguous' => false,
                'resolved_query' => $userMessage,
                'context_note' => $topicShifted
                    ? "Perpindahan topik ke pencarian informasi terkini: {$webLabel}. Abaikan topik sebelumnya yang tidak relevan."
                    : null,
            ];
        }

        $currentTopic = $this->detectSipafTopic($normalized);
        if (
            $currentTopic !== null
            && $isConceptualGeneral
            && !$hasCivicAnchor
            && preg_match('/\b(pinjam|sewa|aju|ajukan|urus|syarat|biaya|tarif|stok|lokasi|alamat|jam)\b/u', $normalized) !== 1
        ) {
            $currentTopic = null;
        }

        $hasAdminCue = $this->hasAnyToken($words, self::ADMINISTRATIVE_CUES);
        $hasGeneralAnalysisCue = $this->hasAnyToken($words, self::GENERAL_ANALYSIS_CUES);
        $hasReferentialCue = $this->hasReferentialSignal($words);
        $hasConcreteSubject = $this->hasConcreteSubjectToken($words);

        // 1. Cross-domain transition: General -> SIPAF with referential verb (e.g. "Apa itu aula?" -> "Di Cikampek cara meminjamnya?")
        if ($hasCivicAnchor && $currentTopic === null && !$isOverviewQuery && ($hasReferentialCue || $hasAdminCue) && !empty($turns)) {
            $bridged = $this->findCrossDomainBridge($turns);
            if ($bridged !== null) {
                return [
                    'sanitized_history' => $sanitizedHistory,
                    'relevant_history' => $this->boundHistory($bridged['turn']['messages']),
                    'active_domain' => 'SIPAF',
                    'active_topic' => $bridged['topic'],
                    'active_topic_label' => $bridged['label'],
                    'resolution_type' => 'cross_domain_sipaf',
                    'topic_shifted' => false,
                    'is_ambiguous' => false,
                    'resolved_query' => trim($bridged['anchor'] . ' ' . $userMessage),
                    'context_note' => "Topik dilanjutkan dari pembahasan sebelumnya ({$bridged['label']}) ke konteks layanan Kecamatan Cikampek.",
                ];
            }
        }

        // 2. Explicit SIPAF topic in current message (handle topic shift vs continuation)
        if ($currentTopic !== null || $hasCivicAnchor) {
            $latestSipafTurn = $this->findLatestTurnByDomain($turns, 'SIPAF');
            $latestTurn = !empty($turns) ? $turns[count($turns) - 1] : null;
            $topicShifted = false;
            $relevantMessages = $sanitizedHistory;
            $contextNote = null;

            if ($currentTopic !== null) {
                $topicLabel = self::SIPAF_TOPIC_PATTERNS[$currentTopic]['label'] ?? $currentTopic;

                if ($latestTurn !== null && in_array($latestTurn['domain'], ['GENERAL', 'WEB'], true)) {
                    // User switched from General or Web search to a specific SIPAF topic: prune unrelated prior turn
                    $topicShifted = true;
                    $relevantMessages = $this->filterMessagesBySipafTopic($turns, $currentTopic);
                    $contextNote = $latestTurn['domain'] === 'WEB'
                        ? "Perpindahan topik aktif ke layanan SIPAF/Kecamatan: {$topicLabel}. Abaikan topik pencarian informasi publik sebelumnya."
                        : "Perpindahan topik aktif ke layanan SIPAF/Kecamatan: {$topicLabel}. Abaikan topik umum sebelumnya yang tidak berkaitan.";
                } elseif ($latestSipafTurn !== null && $latestSipafTurn['sipaf_topic'] !== null && $latestSipafTurn['sipaf_topic'] !== $currentTopic) {
                    // Explicit SIPAF topic shift (e.g. Aula -> Inventaris) unless current message explicitly references the previous topic too
                    $prevTopic = $latestSipafTurn['sipaf_topic'];
                    if (!$this->mentionsTopic($normalized, $prevTopic)) {
                        $topicShifted = true;
                        $relevantMessages = $this->filterMessagesBySipafTopic($turns, $currentTopic);
                        $prevLabel = self::SIPAF_TOPIC_PATTERNS[$prevTopic]['label'] ?? $prevTopic;
                        $contextNote = "Perpindahan topik aktif dari {$prevLabel} ke {$topicLabel}. Jawab fokus pada {$topicLabel} tanpa mencampurkan aturan topik sebelumnya.";
                    }
                }

                return [
                    'sanitized_history' => $sanitizedHistory,
                    'relevant_history' => $this->boundHistory($relevantMessages),
                    'active_domain' => 'SIPAF',
                    'active_topic' => $currentTopic,
                    'active_topic_label' => $topicLabel,
                    'resolution_type' => $topicShifted ? 'topic_shift' : 'direct_sipaf',
                    'topic_shifted' => $topicShifted,
                    'is_ambiguous' => false,
                    'resolved_query' => $userMessage,
                    'context_note' => $contextNote,
                ];
            }

            // Explicit civic anchor without sub-topic (e.g. "Kecamatan Cikampek dimana?")
            if ($latestTurn !== null && in_array($latestTurn['domain'], ['GENERAL', 'WEB'], true)) {
                $topicShifted = true;
                $relevantMessages = $this->filterNonGeneralTurns($turns);
                $contextNote = $latestTurn['domain'] === 'WEB'
                    ? 'Perpindahan topik aktif ke informasi Kecamatan Cikampek / SIPAF. Abaikan topik pencarian informasi publik sebelumnya.'
                    : null;
            }

            return [
                'sanitized_history' => $sanitizedHistory,
                'relevant_history' => $this->boundHistory($relevantMessages),
                'active_domain' => 'SIPAF',
                'active_topic' => null,
                'active_topic_label' => null,
                'resolution_type' => $topicShifted ? 'topic_shift' : 'direct_sipaf',
                'topic_shifted' => $topicShifted,
                'is_ambiguous' => false,
                'resolved_query' => $userMessage,
                'context_note' => $contextNote,
            ];
        }

        // 3. Explicit conceptual / concrete standalone general topic (e.g. "Apa itu inflasi?")
        if ($isConceptualGeneral || ($hasConcreteSubject && !$hasReferentialCue)) {
            $latestTurn = !empty($turns) ? $turns[count($turns) - 1] : null;
            $topicShifted = $latestTurn !== null && in_array($latestTurn['domain'], ['SIPAF', 'WEB'], true);
            $relevantMessages = $topicShifted ? [] : $this->filterGeneralOrNeutralTurns($turns);
            $generalLabel = $this->extractGeneralTopicLabel($normalized);

            return [
                'sanitized_history' => $sanitizedHistory,
                'relevant_history' => $this->boundHistory($relevantMessages),
                'active_domain' => 'GENERAL',
                'active_topic' => $generalLabel,
                'active_topic_label' => $generalLabel,
                'resolution_type' => $topicShifted ? 'topic_shift' : 'direct_general',
                'topic_shifted' => $topicShifted,
                'is_ambiguous' => false,
                'resolved_query' => $userMessage,
                'context_note' => $topicShifted && $generalLabel !== null
                    ? "Perpindahan topik ke pertanyaan umum: {$generalLabel}. Abaikan topik layanan SIPAF sebelumnya."
                    : null,
            ];
        }

        // 4. Elliptical / referential follow-up resolution from conversation history
        $isShortElliptical = count($words) <= 10 && ($hasReferentialCue || $hasAdminCue || $hasGeneralAnalysisCue);
        if ($isShortElliptical && !$hasConcreteSubject) {
            // 4a. Administrative follow-up ("Persyaratannya apa saja?", "Berapa biayanya?", "Kalau pengembaliannya bagaimana?")
            if ($hasAdminCue && !$hasGeneralAnalysisCue) {
                $antecedent = $this->findBestAdministrativeAntecedent($turns);
                if ($antecedent !== null) {
                    $topicKey = $antecedent['sipaf_topic'];
                    $label = $topicKey !== null
                        ? (self::SIPAF_TOPIC_PATTERNS[$topicKey]['label'] ?? $topicKey)
                        : ($antecedent['topic_label'] ?? 'Layanan SIPAF');
                    $anchor = $topicKey !== null
                        ? (self::SIPAF_TOPIC_PATTERNS[$topicKey]['anchor'] ?? implode(' ', $antecedent['keywords']))
                        : implode(' ', $antecedent['keywords']);

                    if ($antecedent['domain'] === 'SIPAF') {
                        return [
                            'sanitized_history' => $sanitizedHistory,
                            'relevant_history' => $this->boundHistory($antecedent['messages']),
                            'active_domain' => 'SIPAF',
                            'active_topic' => $topicKey,
                            'active_topic_label' => $label,
                            'resolution_type' => 'followup_sipaf',
                            'topic_shifted' => count($turns) > 1 && end($turns) !== $antecedent,
                            'is_ambiguous' => false,
                            'resolved_query' => trim($anchor . ' ' . $userMessage),
                            'context_note' => "Topik aktif lanjutan: {$label}. Jawab pertanyaan lanjutan pengguna sesuai topik ini (abaikan selingan topik lain yang tidak relevan).",
                        ];
                    }

                    // Procedural general antecedent (e.g. "Bagaimana cara membuat paspor?" -> "Persyaratannya apa saja?")
                    return [
                        'sanitized_history' => $sanitizedHistory,
                        'relevant_history' => $this->boundHistory($antecedent['messages']),
                        'active_domain' => 'GENERAL',
                        'active_topic' => $antecedent['topic_label'],
                        'active_topic_label' => $antecedent['topic_label'],
                        'resolution_type' => 'followup_general',
                        'topic_shifted' => false,
                        'is_ambiguous' => false,
                        'resolved_query' => $userMessage,
                        'context_note' => "Topik aktif lanjutan (Pengetahuan Umum): {$antecedent['topic_label']}.",
                    ];
                }

                // Administrative follow-up without any compatible antecedent -> ask clarification!
                return $this->buildAmbiguousResult($sanitizedHistory, $userMessage);
            }

            // 4b. General analysis follow-up ("Apa penyebabnya?", "Bagaimana dampaknya?")
            if ($hasGeneralAnalysisCue && !$hasAdminCue) {
                $generalTurn = $this->findLatestTurnByDomain($turns, 'GENERAL');
                if ($generalTurn !== null) {
                    return [
                        'sanitized_history' => $sanitizedHistory,
                        'relevant_history' => $this->boundHistory($generalTurn['messages']),
                        'active_domain' => 'GENERAL',
                        'active_topic' => $generalTurn['topic_label'],
                        'active_topic_label' => $generalTurn['topic_label'],
                        'resolution_type' => 'followup_general',
                        'topic_shifted' => false,
                        'is_ambiguous' => false,
                        'resolved_query' => $userMessage,
                        'context_note' => "Topik aktif lanjutan (Pengetahuan Umum): {$generalTurn['topic_label']}.",
                    ];
                }

                return $this->buildAmbiguousResult($sanitizedHistory, $userMessage);
            }

            // 4c. Generic referential follow-up ("Yang tadi gimana?", "Selanjutnya bagaimana?", "Itu prosesnya gimana?")
            $latestFactTurn = $this->findLatestFactualTurn($turns);
            if ($latestFactTurn !== null) {
                if ($latestFactTurn['domain'] === 'WEB') {
                    $webLabel = $latestFactTurn['topic_label'] ?? 'Informasi Publik Terkini';

                    return [
                        'sanitized_history' => $sanitizedHistory,
                        'relevant_history' => $this->boundHistory($latestFactTurn['messages']),
                        'active_domain' => 'WEB',
                        'active_topic' => $webLabel,
                        'active_topic_label' => $webLabel,
                        'resolution_type' => 'followup_web',
                        'topic_shifted' => false,
                        'is_ambiguous' => false,
                        'resolved_query' => trim($webLabel . ' ' . $userMessage),
                        'context_note' => "Topik aktif lanjutan (Pencarian Web): {$webLabel}.",
                    ];
                }

                if ($latestFactTurn['domain'] === 'SIPAF') {
                    $topicKey = $latestFactTurn['sipaf_topic'];
                    $label = $topicKey !== null
                        ? (self::SIPAF_TOPIC_PATTERNS[$topicKey]['label'] ?? $topicKey)
                        : ($latestFactTurn['topic_label'] ?? 'Layanan SIPAF');
                    $anchor = $topicKey !== null
                        ? (self::SIPAF_TOPIC_PATTERNS[$topicKey]['anchor'] ?? implode(' ', $latestFactTurn['keywords']))
                        : implode(' ', $latestFactTurn['keywords']);

                    return [
                        'sanitized_history' => $sanitizedHistory,
                        'relevant_history' => $this->boundHistory($latestFactTurn['messages']),
                        'active_domain' => 'SIPAF',
                        'active_topic' => $topicKey,
                        'active_topic_label' => $label,
                        'resolution_type' => 'followup_sipaf',
                        'topic_shifted' => false,
                        'is_ambiguous' => false,
                        'resolved_query' => trim($anchor . ' ' . $userMessage),
                        'context_note' => "Topik aktif lanjutan: {$label}.",
                    ];
                }

                return [
                    'sanitized_history' => $sanitizedHistory,
                    'relevant_history' => $this->boundHistory($latestFactTurn['messages']),
                    'active_domain' => 'GENERAL',
                    'active_topic' => $latestFactTurn['topic_label'],
                    'active_topic_label' => $latestFactTurn['topic_label'],
                    'resolution_type' => 'followup_general',
                    'topic_shifted' => false,
                    'is_ambiguous' => false,
                    'resolved_query' => $userMessage,
                    'context_note' => "Topik aktif lanjutan (Pengetahuan Umum): {$latestFactTurn['topic_label']}.",
                ];
            }

            return $this->buildAmbiguousResult($sanitizedHistory, $userMessage);
        }

        // 5. Fallback for queries with concrete tokens (may have mixed/followup nuance)
        if (!$hasConcreteSubject) {
            return $this->buildAmbiguousResult($sanitizedHistory, $userMessage);
        }

        return [
            'sanitized_history' => $sanitizedHistory,
            'relevant_history' => $this->boundHistory($sanitizedHistory),
            'active_domain' => 'NONE',
            'active_topic' => null,
            'active_topic_label' => null,
            'resolution_type' => 'direct',
            'topic_shifted' => false,
            'is_ambiguous' => false,
            'resolved_query' => $userMessage,
            'context_note' => null,
        ];
    }

    protected const ALLOWED_HISTORY_ROLES = ['user', 'model', 'assistant'];

    /**
     * Sanitize and normalize client-supplied history entries against role spoofing,
     * prompt injection, invalid turn sequences, and oversized payloads.
     *
     * @param array<int, array{role?: string, text?: string, isError?: bool}> $history
     * @return array<int, array{role: string, text: string, isError: bool}>
     */
    public function sanitizeHistory(array $history): array
    {
        $clean = [];
        $hasUserTurn = false;
        $lastRole = null;

        foreach ($history as $item) {
            if (!is_array($item) || !empty($item['isError'])) {
                continue;
            }

            if (!isset($item['role']) || !is_string($item['role'])) {
                continue;
            }

            // Strictly allow only user / model / assistant (reject system, developer, tool, unknown, mixed-case spoof attempts)
            $role = trim($item['role']);
            if (!in_array($role, self::ALLOWED_HISTORY_ROLES, true)) {
                continue;
            }

            // Reject invalid conversation sequences from manipulated frontends:
            // 1) Orphan model/assistant turns before any user message
            // 2) Consecutive model/assistant turns fabricating multi-turn AI agreement
            $isAssistantRole = $role !== 'user';
            if ($isAssistantRole && (!$hasUserTurn || $lastRole === 'assistant')) {
                continue;
            }

            $text = is_string($item['text'] ?? null) ? $item['text'] : (is_scalar($item['text'] ?? null) ? (string) $item['text'] : '');
            $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $text) ?? $text;
            $text = preg_replace('/<\/?(SYSTEM_INSTRUCTION|KNOWLEDGE_CONTEXT|INTENT_MODE|WEB_CONTEXT|CONVERSATION_CONTEXT|USER_MESSAGE|SYSTEM|DEVELOPER|ASSISTANT|MODEL|PROMPT|INSTRUCTION|HISTORY)[^>]*>/i', '', $text) ?? $text;
            $text = preg_replace('/^\s*(\[?(SYSTEM|DEVELOPER|SYSTEM_INSTRUCTION|ATURAN\s+UTAMA|MODE\s+AKTIF|ASSISTANT_OVERRIDE)\]?\s*:)/imu', '', $text) ?? $text;
            $text = trim($text);

            if ($text === '') {
                continue;
            }

            if (mb_strlen($text) > self::MAX_MESSAGE_CHARS) {
                $text = rtrim(mb_substr($text, 0, self::MAX_MESSAGE_CHARS - 3)) . '...';
            }

            if ($role === 'user') {
                $hasUserTurn = true;
                $lastRole = 'user';
            } else {
                $lastRole = 'assistant';
            }

            $clean[] = [
                'role' => $role,
                'text' => $text,
                'isError' => false,
            ];
        }

        return $clean;
    }

    /**
     * Enforce strict message count and character budget on forwarded history.
     *
     * @param array<int, array{role: string, text: string, isError: bool}> $messages
     * @return array<int, array{role: string, text: string, isError: bool}>
     */
    protected function boundHistory(array $messages): array
    {
        if (empty($messages)) {
            return [];
        }

        $sliced = array_slice($messages, -self::MAX_HISTORY_MESSAGES);
        $selected = [];
        $totalChars = 0;

        for ($i = count($sliced) - 1; $i >= 0; $i--) {
            $len = mb_strlen($sliced[$i]['text']);
            if (!empty($selected) && ($totalChars + $len) > self::MAX_TOTAL_HISTORY_CHARS) {
                break;
            }
            array_unshift($selected, $sliced[$i]);
            $totalChars += $len;
        }

        return $selected;
    }

    /**
     * Group sanitized history into conversational turns (user prompt + optional assistant response)
     * annotated with domain and topic metadata.
     *
     * @return array<int, array{
     *     domain: string,
     *     sipaf_topic: ?string,
     *     topic_label: ?string,
     *     is_procedural: bool,
     *     keywords: array<int, string>,
     *     user_text: string,
     *     messages: array<int, array{role: string, text: string, isError: bool}>
     * }>
     */
    protected function pairHistoryTurns(array $sanitizedHistory): array
    {
        $turns = [];
        $currentTurn = null;

        foreach ($sanitizedHistory as $msg) {
            if ($msg['role'] === 'user') {
                if ($currentTurn !== null) {
                    $turns[] = $currentTurn;
                }
                $currentTurn = $this->classifyHistoryUserTurn($msg);
            } elseif ($currentTurn !== null) {
                $currentTurn['messages'][] = $msg;
            } else {
                // Leading assistant greeting without preceding user turn
                $turns[] = [
                    'domain' => 'CONVERSATIONAL',
                    'sipaf_topic' => null,
                    'topic_label' => null,
                    'is_procedural' => false,
                    'keywords' => [],
                    'user_text' => '',
                    'messages' => [$msg],
                ];
            }
        }

        if ($currentTurn !== null) {
            $turns[] = $currentTurn;
        }

        return $turns;
    }

    protected function classifyHistoryUserTurn(array $userMsg): array
    {
        $rawText = $userMsg['text'];
        $norm = $this->normalizeText($rawText);
        $parsed = $this->retrievalService->prepareQuery($rawText);

        if (!empty($parsed['is_conversational'])) {
            return [
                'domain' => 'CONVERSATIONAL',
                'sipaf_topic' => null,
                'topic_label' => null,
                'is_procedural' => false,
                'keywords' => [],
                'user_text' => $rawText,
                'messages' => [$userMsg],
            ];
        }

        $keywords = array_slice(
            array_values(array_unique(array_merge($parsed['specific_tokens'], $parsed['generic_tokens']))),
            0,
            5
        );

        // Distinguish standalone real-time/official-person queries from internal SIPAF topics
        if ($this->isHistoryRealTimeWebQuery($norm)) {
            return [
                'domain' => 'WEB',
                'sipaf_topic' => null,
                'topic_label' => $this->extractGeneralTopicLabel($norm) ?? 'Informasi Publik Terkini',
                'is_procedural' => false,
                'keywords' => $keywords,
                'user_text' => $rawText,
                'messages' => [$userMsg],
            ];
        }

        $sipafTopic = $this->detectSipafTopic($norm);
        $isConceptual = $this->matchesConceptualPattern($norm);
        if (
            $sipafTopic !== null
            && $isConceptual
            && !$this->hasBroadCivicAnchor($norm)
            && preg_match('/\b(pinjam|sewa|aju|ajukan|urus|syarat|biaya|tarif|stok|ktp|kk|sktm|sku|waris|bpn|ajb|piagam|bansos)\b/u', $norm) !== 1
        ) {
            $sipafTopic = null;
        }
        $hasCivic = $sipafTopic !== null || $this->hasBroadCivicAnchor($norm);

        if ($hasCivic) {
            $label = $sipafTopic !== null
                ? (self::SIPAF_TOPIC_PATTERNS[$sipafTopic]['label'] ?? $sipafTopic)
                : $this->extractGeneralTopicLabel($norm);

            return [
                'domain' => 'SIPAF',
                'sipaf_topic' => $sipafTopic,
                'topic_label' => $label,
                'is_procedural' => true,
                'keywords' => $keywords,
                'user_text' => $rawText,
                'messages' => [$userMsg],
            ];
        }

        $isProceduralGeneral = !$isConceptual && (
            preg_match('/\b(cara|membuat|mengurus|mendaftar|daftar|syarat|prosedur|pengajuan)\b/u', $norm) === 1
        );

        return [
            'domain' => 'GENERAL',
            'sipaf_topic' => null,
            'topic_label' => $this->extractGeneralTopicLabel($norm),
            'is_procedural' => $isProceduralGeneral,
            'keywords' => $keywords,
            'user_text' => $rawText,
            'messages' => [$userMsg],
        ];
    }

    /**
     * For administrative follow-ups ("Persyaratannya apa saja?"), skip non-procedural conceptual
     * intermezzos (e.g. "Apa itu inflasi?") to recover the active service topic (e.g. "pinjam aula").
     */
    protected function findBestAdministrativeAntecedent(array $turns): ?array
    {
        for ($i = count($turns) - 1; $i >= 0; $i--) {
            $turn = $turns[$i];
            if ($turn['domain'] === 'CONVERSATIONAL') {
                continue;
            }
            if ($turn['domain'] === 'SIPAF') {
                return $turn;
            }
            if ($turn['domain'] === 'GENERAL' && $turn['is_procedural']) {
                return $turn;
            }
        }

        return null;
    }

    protected function findLatestTurnByDomain(array $turns, string $domain): ?array
    {
        for ($i = count($turns) - 1; $i >= 0; $i--) {
            if ($turns[$i]['domain'] === $domain) {
                return $turns[$i];
            }
        }

        return null;
    }

    protected function findLatestFactualTurn(array $turns): ?array
    {
        for ($i = count($turns) - 1; $i >= 0; $i--) {
            if (in_array($turns[$i]['domain'], ['SIPAF', 'GENERAL', 'WEB'], true)) {
                return $turns[$i];
            }
        }

        return null;
    }

    protected function filterWebOrNeutralTurns(array $turns): array
    {
        $messages = [];
        foreach ($turns as $turn) {
            if (in_array($turn['domain'], ['WEB', 'CONVERSATIONAL'], true)) {
                $messages = array_merge($messages, $turn['messages']);
            }
        }

        return $messages;
    }

    protected function isHistoryRealTimeWebQuery(string $normalized): bool
    {
        if (IntentClassifierService::matchesPublicOfficialLookup($normalized)) {
            return true;
        }

        return preg_match('/\b(libur\s+nasional|cuti\s+bersama|kebijakan\s+pemerintah\s+terbaru|peraturan\s+terbaru|perubahan\s+prosedur\s+pelayanan\s+publik)\b/u', $normalized) === 1;
    }

    /**
     * Find a bridgeable topic from the most recent General turn when user switches into Kecamatan/SIPAF.
     *
     * @return array{topic: ?string, label: string, anchor: string, turn: array}|null
     */
    protected function findCrossDomainBridge(array $turns): ?array
    {
        $latestTurn = !empty($turns) ? $turns[count($turns) - 1] : null;
        if ($latestTurn === null || $latestTurn['domain'] !== 'GENERAL') {
            return null;
        }

        $normPrev = $this->normalizeText($latestTurn['user_text']);
        foreach (self::CROSS_DOMAIN_BRIDGE_TERMS as $topicKey => [$matchTerm, $anchor]) {
            if ($this->containsWholeWord($normPrev, $matchTerm) || (mb_strlen($matchTerm) >= 5 && str_contains($normPrev, $matchTerm))) {
                $mappedTopic = match ($topicKey) {
                    'kursi', 'sound', 'proyektor', 'komputer' => 'inventaris',
                    'sertifikat', 'piagam' => 'sertifikat_piagam',
                    'waris' => 'ahli_waris',
                    'umkm' => 'sku_umkm',
                    'tanah' => 'sertifikat_tanah',
                    default => $topicKey,
                };
                $label = self::SIPAF_TOPIC_PATTERNS[$mappedTopic]['label'] ?? $topicKey;

                return [
                    'topic' => $mappedTopic,
                    'label' => $label,
                    'anchor' => $anchor,
                    'turn' => $latestTurn,
                ];
            }
        }

        return null;
    }

    protected function filterMessagesBySipafTopic(array $turns, string $targetTopic): array
    {
        $messages = [];
        foreach ($turns as $turn) {
            if ($turn['domain'] === 'CONVERSATIONAL') {
                $messages = array_merge($messages, $turn['messages']);
                continue;
            }
            if ($turn['domain'] === 'SIPAF' && ($turn['sipaf_topic'] === $targetTopic || $turn['sipaf_topic'] === null)) {
                $messages = array_merge($messages, $turn['messages']);
            }
        }

        return $messages;
    }

    protected function filterNonGeneralTurns(array $turns): array
    {
        $messages = [];
        foreach ($turns as $turn) {
            if (!in_array($turn['domain'], ['GENERAL', 'WEB'], true)) {
                $messages = array_merge($messages, $turn['messages']);
            }
        }

        return $messages;
    }

    protected function filterGeneralOrNeutralTurns(array $turns): array
    {
        $messages = [];
        foreach ($turns as $turn) {
            if (!in_array($turn['domain'], ['SIPAF', 'WEB'], true)) {
                $messages = array_merge($messages, $turn['messages']);
            }
        }

        return $messages;
    }

    public function detectSipafTopic(string $normalized): ?string
    {
        foreach (self::SIPAF_TOPIC_PATTERNS as $topicKey => $config) {
            foreach ($config['terms'] ?? [] as $term) {
                if (str_contains($normalized, $term)) {
                    return $topicKey;
                }
            }

            foreach ($config['exact'] ?? [] as $token) {
                if ($this->containsWholeWord($normalized, $token)) {
                    return $topicKey;
                }
            }

            if (!empty($config['contextual_terms']) && preg_match('/\b(pinjam|pinjem|minjam|sewa|stok|sipaf|kecamatan|cikampek|kantor)\b/u', $normalized) === 1) {
                foreach ($config['contextual_terms'] as $ctxTerm) {
                    if ($this->containsWholeWord($normalized, $ctxTerm)) {
                        return $topicKey;
                    }
                }
            }
        }

        // Handle generic "sertifikat" in civic/service context (when not tanah)
        if (str_contains($normalized, 'sertifikat') && preg_match('/\b(sipaf|kecamatan|cikampek|aju|ajukan|mengajukan|pengajuan|buat|bikin|cara|syarat|prosedur|alur)\b/u', $normalized) === 1) {
            return 'sertifikat_piagam';
        }

        // Handle generic "umkm" in service context
        if ($this->containsWholeWord($normalized, 'umkm') && preg_match('/\b(izin|sku|daftar|syarat|urus|ngurus|buat|bikin|kecamatan|cikampek|desa|cara|prosedur)\b/u', $normalized) === 1) {
            return 'sku_umkm';
        }

        // Handle "tanah" in service context
        if ($this->containsWholeWord($normalized, 'tanah') && preg_match('/\b(urus|ngurus|jual|beli|ajb|sertifikat|riwayat|kecamatan|cikampek|bpn|ppats|cara|syarat)\b/u', $normalized) === 1) {
            return 'sertifikat_tanah';
        }

        return null;
    }

    protected function mentionsTopic(string $normalized, string $topicKey): bool
    {
        $config = self::SIPAF_TOPIC_PATTERNS[$topicKey] ?? null;
        if ($config === null) {
            return false;
        }

        foreach ($config['terms'] ?? [] as $term) {
            if (str_contains($normalized, $term)) {
                return true;
            }
        }

        foreach ($config['exact'] ?? [] as $token) {
            if ($this->containsWholeWord($normalized, $token)) {
                return true;
            }
        }

        return false;
    }

    protected function hasBroadCivicAnchor(string $normalized): bool
    {
        return preg_match('/\b(sipaf|cikampek|kecamatan|paten|ppats|desa)\b/u', $normalized) === 1;
    }

    protected function matchesConceptualPattern(string $normalized): bool
    {
        return IntentClassifierService::matchesConceptualGeneralPattern($normalized);
    }

    protected function hasReferentialSignal(array $words): bool
    {
        if ($this->hasAnyToken($words, self::REFERENTIAL_CUES)) {
            return true;
        }

        foreach ($words as $word) {
            if (mb_strlen($word) >= 5 && str_ends_with($word, 'nya') && !in_array($word, ['sebenarnya', 'biasanya', 'umumnya', 'misalnya', 'contohnya', 'tanya', 'nanya', 'bertanya'], true)) {
                return true;
            }
        }

        return false;
    }

    protected function hasConcreteSubjectToken(array $words): bool
    {
        foreach ($words as $word) {
            if (mb_strlen($word) < 2) {
                continue;
            }
            if (!in_array($word, self::NON_SUBJECT_TOKENS, true)) {
                return true;
            }
        }

        return false;
    }

    protected function hasAnyToken(array $words, array $targets): bool
    {
        foreach ($words as $word) {
            if (in_array($word, $targets, true)) {
                return true;
            }
        }

        return false;
    }

    protected function extractGeneralTopicLabel(string $normalized): ?string
    {
        $words = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $meaningful = array_values(array_filter(
            $words,
            fn (string $w) => mb_strlen($w) >= 2 && !in_array($w, self::NON_SUBJECT_TOKENS, true)
        ));

        if (empty($meaningful)) {
            return null;
        }

        return implode(' ', array_slice($meaningful, 0, 5));
    }

    protected function buildAmbiguousResult(array $sanitizedHistory, string $userMessage): array
    {
        return [
            'sanitized_history' => $sanitizedHistory,
            'relevant_history' => $this->boundHistory($sanitizedHistory),
            'active_domain' => 'NONE',
            'active_topic' => null,
            'active_topic_label' => null,
            'resolution_type' => 'ambiguous',
            'topic_shifted' => false,
            'is_ambiguous' => true,
            'resolved_query' => $userMessage,
            'context_note' => 'Pertanyaan pengguna bersifat ambigu dan belum merujuk pada topik atau layanan tertentu. Minta klarifikasi secara ramah tanpa menebak.',
        ];
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
