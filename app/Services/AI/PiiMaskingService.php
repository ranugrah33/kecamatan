<?php

namespace App\Services\AI;

class PiiMaskingService
{
    public const PLACEHOLDER_NIK = '[NIK DISEMBUNYIKAN]';
    public const PLACEHOLDER_KK = '[NO KK DISEMBUNYIKAN]';
    public const PLACEHOLDER_PHONE = '[NOMOR TELEPON DISEMBUNYIKAN]';
    public const PLACEHOLDER_EMAIL = '[EMAIL DISEMBUNYIKAN]';
    public const PLACEHOLDER_IDENTITY = '[IDENTITAS DISEMBUNYIKAN]';

    /**
     * Context keywords right before a numeric sequence that indicate a non-PII transaction/reference number.
     */
    protected const NON_PII_NUMBER_CONTEXT_PATTERN = '/\b(kode\s*(?:transaksi|booking|pengajuan|referensi|bayar|surat|tiket)|(?:id|no(?:mor)?\.?)\s*(?:transaksi|pengajuan|referensi|surat|resi|va|rekening|rek|invoice|order|tiket)|virtual\s+account|invoice|resi|sku|isbn|imei|serial)\b/iu';

    /**
     * Context keywords indicating Kartu Keluarga (KK) rather than NIK.
     */
    protected const KK_CONTEXT_PATTERN = '/\b(no(?:mor)?\.?\s*kk|nokk|kk|kartu\s+keluarga)\b/iu';

    /**
     * Question/generic non-person words that must not be masked after "Nama saya ...".
     */
    protected const NON_PERSON_NAME_PREFIXES = [
        'apa',
        'siapa',
        'bagaimana',
        'gimana',
        'berapa',
        'kapan',
        'dimana',
        'di',
        'yang',
        'kok',
        'kenapa',
        'bisa',
        'tidak',
        'bukan',
        'belum',
        'sudah',
        'layanan',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'desa',
        'instansi',
        'acara',
        'kegiatan',
        'fasilitas',
        'aula',
    ];

    /**
     * Mask PII inside a text copy without altering non-PII numbers, dates, SIPAF transaction codes, or existing placeholders.
     * Strictly idempotent: calling maskText() repeatedly on an already-masked string returns the identical string and count=0.
     *
     * @return array{text: string, count: int, categories: array<int, string>}
     */
    public function maskText(string $text): array
    {
        if ($text === '') {
            return ['text' => '', 'count' => 0, 'categories' => []];
        }

        $working = $text;
        $totalCount = 0;
        $categories = [];

        // 1. Emails first so local-parts with digits or dots aren't fragmented
        [$working, $count] = $this->maskEmails($working);
        $this->recordStepStats($count, 'EMAIL', $totalCount, $categories);

        // 2. Explicitly labeled KK & NIK numbers (supports spaces/hyphens/dots)
        [$working, $count] = $this->maskLabeledKk($working);
        $this->recordStepStats($count, 'KK', $totalCount, $categories);

        [$working, $count] = $this->maskLabeledNik($working);
        $this->recordStepStats($count, 'NIK', $totalCount, $categories);

        // 3. Unlabeled 16-digit NIK / KK sequences (exact 16 digits with optional spaces/hyphens and context guard)
        [$working, $kkCount, $nikCount] = $this->maskUnlabeledSixteenDigitIds($working);
        $this->recordStepStats($kkCount, 'KK', $totalCount, $categories);
        $this->recordStepStats($nikCount, 'NIK', $totalCount, $categories);

        // 4. Labeled phones & unlabeled Indonesian mobile numbers (+628 / 628 / 08)
        [$working, $count] = $this->maskPhones($working);
        $this->recordStepStats($count, 'PHONE', $totalCount, $categories);

        // 5. Explicit personal identity fields (Nama: ..., Nama saya ..., Alamat rumah: ...)
        [$working, $count] = $this->maskPersonalIdentityFields($working);
        $this->recordStepStats($count, 'IDENTITY', $totalCount, $categories);

        return [
            'text' => $working,
            'count' => $totalCount,
            'categories' => $categories,
        ];
    }

    /**
     * Convenience wrapper returning only masked string text.
     */
    public function mask(string $text): string
    {
        return $this->maskText($text)['text'];
    }

    /**
     * Mask every message in a conversation history copy while preserving roles, order, and metadata flags.
     *
     * @param array<int, array<string, mixed>> $history
     * @return array{history: array<int, array<string, mixed>>, count: int, categories: array<int, string>}
     */
    public function maskHistory(array $history): array
    {
        $maskedHistory = [];
        $totalCount = 0;
        $categories = [];

        foreach ($history as $item) {
            if (!is_array($item)) {
                continue;
            }

            $rawText = (string) ($item['text'] ?? '');
            $masked = $this->maskText($rawText);

            if ($masked['count'] > 0) {
                $totalCount += $masked['count'];
                foreach ($masked['categories'] as $cat) {
                    if (!in_array($cat, $categories, true)) {
                        $categories[] = $cat;
                    }
                }
            }

            $copy = $item;
            $copy['text'] = $masked['text'];
            $maskedHistory[] = $copy;
        }

        return [
            'history' => $maskedHistory,
            'count' => $totalCount,
            'categories' => $categories,
        ];
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function maskEmails(string $text): array
    {
        $count = 0;
        $result = preg_replace_callback(
            '/(?<![\w.\-+%])[A-Z0-9][A-Z0-9._%+\-]*@[A-Z0-9.\-]+\.[A-Z]{2,}(?![\w\-])/i',
            function () use (&$count): string {
                $count++;
                return self::PLACEHOLDER_EMAIL;
            },
            $text
        ) ?? $text;

        return [$result, $count];
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function maskLabeledKk(string $text): array
    {
        $count = 0;
        $result = preg_replace_callback(
            '/\b((?:no(?:mor)?\.?\s*kk|nokk|kartu\s+keluarga)(?:\s*(?:saya|anda|pemohon))?\s*(?:[:=]|adalah)?\s*+)(?!\s*\[)(\d(?:[\s\-\.]?\d){5,19})(?![\dA-Z])/iu',
            function (array $matches) use (&$count): string {
                $count++;
                return $matches[1] . self::PLACEHOLDER_KK;
            },
            $text
        ) ?? $text;

        return [$result, $count];
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function maskLabeledNik(string $text): array
    {
        $count = 0;
        $result = preg_replace_callback(
            '/\b((?:nik|no(?:mor)?\.?\s*ktp|nomor\s+induk\s+kependudukan)(?:\s*(?:saya|anda|pemohon))?\s*(?:[:=]|adalah)?\s*+)(?!\s*\[)(\d(?:[\s\-\.]?\d){5,19})(?![\dA-Z])/iu',
            function (array $matches) use (&$count): string {
                $count++;
                return $matches[1] . self::PLACEHOLDER_NIK;
            },
            $text
        ) ?? $text;

        return [$result, $count];
    }

    /**
     * Detect standalone 16-digit sequences (with optional spaces or hyphens) while honoring transaction/letter context.
     *
     * @return array{0: string, 1: int, 2: int} [maskedText, kkCount, nikCount]
     */
    protected function maskUnlabeledSixteenDigitIds(string $text): array
    {
        if (!preg_match_all('/(?<![\dA-Z\-])(\d(?:[\s\-]?\d){14,22})(?![\dA-Z\-])/iu', $text, $matches, PREG_OFFSET_CAPTURE)) {
            return [$text, 0, 0];
        }

        $replacements = [];
        $kkCount = 0;
        $nikCount = 0;

        foreach ($matches[1] as $match) {
            [$rawToken, $byteOffset] = $match;
            $digitsOnly = preg_replace('/\D+/', '', $rawToken) ?? '';

            // Indonesian NIK and KK numbers are strictly 16 digits
            if (strlen($digitsOnly) !== 16) {
                continue;
            }

            $before = $this->extractBeforeContext($text, $byteOffset, 55);
            if ($this->hasNonPiiNumberContext($before)) {
                continue;
            }

            if ($this->hasKkContext($before)) {
                $placeholder = self::PLACEHOLDER_KK;
                $kkCount++;
            } else {
                $placeholder = self::PLACEHOLDER_NIK;
                $nikCount++;
            }

            $replacements[] = [
                'offset' => $byteOffset,
                'length' => strlen($rawToken),
                'replacement' => $placeholder,
            ];
        }

        return [$this->applyReplacementsRightToLeft($text, $replacements), $kkCount, $nikCount];
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function maskPhones(string $text): array
    {
        $total = 0;
        $working = $text;

        // 4a. Labeled Indonesian phones / WA / Telp (mobile & landline numbers 8..15 digits)
        $working = preg_replace_callback(
            '/\b((?:no(?:mor)?\.?\s*(?:hp|wa|telepon|telp|handphone)|whatsapp|telepon|telp|hp|wa)(?:\s*(?:saya|anda|pemohon|yang\s+bisa\s+dihubungi))?\s*(?:[:=]|adalah)?\s*+)(?!\s*\[)(\+?(?:\(\d{2,5}\)|\d{2,4})(?:[\s\-\.]?\d{1,6}){1,6})(?![\dA-Z])/iu',
            function (array $matches) use (&$total): string {
                $digits = preg_replace('/\D+/', '', $matches[2]) ?? '';
                if (strlen($digits) < 8 || strlen($digits) > 15) {
                    return $matches[0];
                }

                $total++;
                return $matches[1] . self::PLACEHOLDER_PHONE;
            },
            $working
        ) ?? $working;

        // 4b. Unlabeled Indonesian mobile numbers (+628..., 628..., 08...) with 9..15 digits
        if (preg_match_all('/(?<!\d)((?:\+?62|0)[\s\-\.]?8\d(?:[\s\-\.]?\d){7,13})(?!\d)/u', $working, $matches, PREG_OFFSET_CAPTURE)) {
            $replacements = [];
            foreach ($matches[1] as $match) {
                [$rawPhone, $byteOffset] = $match;
                $digits = preg_replace('/\D+/', '', $rawPhone) ?? '';
                if (strlen($digits) < 9 || strlen($digits) > 15) {
                    continue;
                }

                $before = $this->extractBeforeContext($working, $byteOffset, 50);
                if ($this->hasNonPiiNumberContext($before)) {
                    continue;
                }

                $total++;
                $replacements[] = [
                    'offset' => $byteOffset,
                    'length' => strlen($rawPhone),
                    'replacement' => self::PLACEHOLDER_PHONE,
                ];
            }

            $working = $this->applyReplacementsRightToLeft($working, $replacements);
        }

        return [$working, $total];
    }

    /**
     * Mask unambiguous personal identity key-value fields (e.g. "Nama: ...", "Alamat rumah: ...")
     * while sparing public/institutional queries like "Nama camat", "Nama layanan", or "alamat kantor kecamatan".
     *
     * @return array{0: string, 1: int}
     */
    protected function maskPersonalIdentityFields(string $text): array
    {
        $total = 0;
        $working = $text;

        // 5a. Explicit personal label with ':' or '=' ("Nama: H. Budi Santoso", "Nama Lengkap: Siti Aminah", "Alamat rumah: Jl. Melati No. 12")
        $working = preg_replace_callback(
            '/\b((?:nama(?:\s+(?:lengkap|pemohon|warga|saya|penanggung\s+jawab))?|pemohon|alamat\s+(?:rumah|domisili|tinggal|pemohon))\s*[:=]\s*+)(?!\s*\[)([^\r\n,;|<>()]+?)(?=\s*(?:[,;|\r\n]|$|\b(?:nik|no(?:mor)?\.?\s*(?:kk|ktp|hp|wa|telepon|telp)|hp|wa|telepon|telp|email|alamat)\b))/iu',
            function (array $matches) use (&$total): string {
                $rawValue = trim($matches[2]);
                $trailingDot = str_ends_with($rawValue, '.') ? '.' : '';
                $value = rtrim($rawValue, ". \t");
                $firstWord = mb_strtolower(preg_split('/\s+/u', $value, 2)[0] ?? '');

                if (
                    $value === ''
                    || mb_strlen($value) < 2
                    || str_starts_with($value, '[')
                    || str_contains($value, 'DISEMBUNYIKAN]')
                    || in_array($firstWord, self::NON_PERSON_NAME_PREFIXES, true)
                ) {
                    return $matches[0];
                }

                $total++;
                return $matches[1] . self::PLACEHOLDER_IDENTITY . $trailingDot;
            },
            $working
        ) ?? $working;

        // 5b. Natural self-introduction phrasing ("Nama saya Budi Santoso, ..." / "Nama lengkap saya adalah Andi Wijaya")
        $working = preg_replace_callback(
            '/\b(nama\s+(?:lengkap\s+)?saya(?:\s+adalah)?\s++)(?!\s*\[)([\p{L}\'\-\.]{2,25}(?:\s+[\p{L}\'\-\.]{2,25}){0,2})(?=\s*(?:[,.;|\r\n]|\b(?:ingin|mau|akan|dari|nik|no(?:mor)?|email|hp|wa|alamat)\b|$))/iu',
            function (array $matches) use (&$total): string {
                $candidate = trim($matches[2]);
                $words = preg_split('/\s+/u', mb_strtolower($candidate)) ?: [];
                $firstWord = $words[0] ?? '';

                if (
                    $firstWord === ''
                    || str_starts_with($candidate, '[')
                    || in_array($firstWord, self::NON_PERSON_NAME_PREFIXES, true)
                ) {
                    return $matches[0];
                }

                $total++;
                return $matches[1] . self::PLACEHOLDER_IDENTITY;
            },
            $working
        ) ?? $working;

        return [$working, $total];
    }

    protected function extractBeforeContext(string $text, int $byteOffset, int $charLimit): string
    {
        $prefix = substr($text, 0, $byteOffset);
        $charLen = mb_strlen($prefix);
        return mb_strtolower(mb_substr($prefix, max(0, $charLen - $charLimit), $charLimit));
    }

    protected function hasNonPiiNumberContext(string $lowerBefore): bool
    {
        return preg_match(self::NON_PII_NUMBER_CONTEXT_PATTERN, $lowerBefore) === 1;
    }

    protected function hasKkContext(string $lowerBefore): bool
    {
        return preg_match(self::KK_CONTEXT_PATTERN, $lowerBefore) === 1;
    }

    /**
     * @param array<int, array{offset: int, length: int, replacement: string}> $replacements
     */
    protected function applyReplacementsRightToLeft(string $text, array $replacements): string
    {
        if (empty($replacements)) {
            return $text;
        }

        usort($replacements, static fn (array $a, array $b) => $b['offset'] <=> $a['offset']);

        $result = $text;
        foreach ($replacements as $item) {
            $result = substr_replace($result, $item['replacement'], $item['offset'], $item['length']);
        }

        return $result;
    }

    /**
     * @param array<int, string> $categories
     */
    protected function recordStepStats(int $count, string $category, int &$totalCount, array &$categories): void
    {
        if ($count <= 0) {
            return;
        }

        $totalCount += $count;
        if (!in_array($category, $categories, true)) {
            $categories[] = $category;
        }
    }
}
