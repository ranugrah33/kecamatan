<?php

namespace App\Services\AI;

use App\Services\AI\Exceptions\AIFailoverException;
use App\Services\AI\Exceptions\AIServiceException;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?string $apiKey;
    protected string $model;
    protected int $timeout;
    protected int $maxRetries;
    protected int $maxOutputTokens;
    protected PiiMaskingService $piiMaskingService;

    public function __construct(?PiiMaskingService $piiMaskingService = null)
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-3.6-flash');
        $this->timeout = (int) config('services.ai.timeout', 20);
        $this->maxRetries = (int) config('services.ai.max_retries', 1);
        $this->maxOutputTokens = $this->resolveMaxOutputTokens();
        $this->piiMaskingService = $piiMaskingService ?? new PiiMaskingService();
    }

    protected function resolveMaxOutputTokens(): int
    {
        return max(256, min(8192, (int) config('services.gemini.max_output_tokens', 2048)));
    }

    protected function resolveCooldownSeconds(): int
    {
        return max(5, min(600, (int) config('services.gemini.cooldown_seconds', 45)));
    }

    protected function resolveMaxCooldownSeconds(): int
    {
        $default = $this->resolveCooldownSeconds();
        return max($default, min(600, (int) config('services.gemini.cooldown_max_seconds', 180)));
    }

    /**
     * Model-scoped cache key so Gemini cooldown stays isolated per provider/model.
     */
    public function getCircuitBreakerCacheKey(): string
    {
        $safeModel = preg_replace('/[^a-zA-Z0-9._-]+/', '_', strtolower(trim($this->model))) ?: 'default';
        return "ai:circuit_breaker:gemini:{$safeModel}";
    }

    protected function getCircuitBreakerLockKey(): string
    {
        return $this->getCircuitBreakerCacheKey() . ':lock';
    }

    /**
     * Send prompt to Google Gemini API.
     *
     * @param string $userMessage
     * @param array $history
     * @param string $systemPrompt
     * @return string
     * @throws AIFailoverException
     * @throws AIServiceException
     */
    public function generateResponse(string $userMessage, array $history, string $systemPrompt): string
    {
        if (empty($this->apiKey)) {
            Log::warning('[GeminiService] Gemini API Key is missing. Administrator should check GEMINI_API_KEY in .env.');
            throw new AIFailoverException('gemini', 'Gemini API Key is not configured.', 500, 'CONFIG_ERROR');
        }

        $isRecoveryProbe = $this->acquireCircuitSlot();

        // Defense-in-depth PII masking on outbound copies (strictly idempotent if already masked by AIRouterService)
        $safeUserMessage = $this->piiMaskingService->mask($userMessage);

        // Format strictly allowlisted history into Gemini format (drop system/developer/unknown roles)
        $contents = [];
        foreach ($history as $msg) {
            if (!is_array($msg) || !empty($msg['isError'])) {
                continue;
            }

            $rawRole = is_string($msg['role'] ?? null) ? trim($msg['role']) : '';
            if (!in_array($rawRole, ['user', 'model', 'assistant'], true)) {
                continue;
            }

            $maskedText = trim($this->piiMaskingService->mask((string) ($msg['text'] ?? '')));
            if ($maskedText === '') {
                continue;
            }

            $role = $rawRole === 'user' ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $maskedText]
                ]
            ];
        }

        // Append current user message
        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $safeUserMessage]
            ]
        ];

        $this->maxOutputTokens = $this->resolveMaxOutputTokens();

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemPrompt]
                ]
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => $this->maxOutputTokens,
            ],
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        try {
            $response = Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
            ])->timeout($this->timeout)
                ->retry($this->maxRetries, 300, function ($exception) {
                    return $exception instanceof ConnectionException;
                }, throw: false)
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            $this->releaseProbeOnNonRateLimitError($isRecoveryProbe);
            throw new AIFailoverException('gemini', 'Gemini connection timeout or network unreachable: ' . $e->getMessage(), 408, 'TIMEOUT', $e);
        } catch (\Exception $e) {
            $this->releaseProbeOnNonRateLimitError($isRecoveryProbe);
            throw new AIFailoverException('gemini', 'Gemini request exception: ' . $e->getMessage(), 500, 'NETWORK_ERROR', $e);
        }

        if ($response->successful()) {
            $data = $response->json();
            $candidate = is_array($data['candidates'][0] ?? null) ? $data['candidates'][0] : [];
            $rawFinishReason = $candidate['finishReason'] ?? null;
            $finishReason = strtoupper(trim((string) ($rawFinishReason ?? 'STOP')));
            $usage = is_array($data['usageMetadata'] ?? null) ? $data['usageMetadata'] : [];
            $reply = $this->extractCandidateText($candidate);

            if ($reply !== null && trim($reply) !== '') {
                $this->markCircuitRecovered($isRecoveryProbe);
                $cleanReply = trim($reply);

                if ($finishReason === 'MAX_TOKENS') {
                    $this->logTokenLimitEvent($finishReason, $usage);

                    // Do not silently cut off output or loop automatic retries when hitting token cap
                    return $cleanReply
                        . "\n\n*(Catatan: Jawaban terpotong karena mencapai batas maksimum panjang respons AI. Silakan ajukan pertanyaan lanjutan yang lebih spesifik jika ingin melanjutkan penjelasan.)*";
                }

                if (!in_array($finishReason, ['STOP', 'FINISH_REASON_UNSPECIFIED', ''], true)) {
                    $this->logTokenLimitEvent($finishReason, $usage);
                }

                return $cleanReply;
            }

            $this->releaseProbeOnNonRateLimitError($isRecoveryProbe);

            // Check if blocked by safety ratings or finished with other reasons
            $emptyReason = is_string($rawFinishReason) && trim($rawFinishReason) !== ''
                ? strtoupper(trim($rawFinishReason))
                : 'EMPTY_RESPONSE';
            Log::warning("[GeminiService] Empty response received. Finish reason: {$emptyReason}");
            throw new AIFailoverException('gemini', "Gemini returned empty response ({$emptyReason}).", 500, 'EMPTY_RESPONSE');
        }

        $statusCode = $response->status();
        $body = $response->body();

        // 1. HTTP 429 Rate Limit -> Activate model-scoped circuit breaker cooldown before failing over
        if ($statusCode === 429) {
            Log::warning("[GeminiService] Gemini rate limit hit (HTTP 429) | Model: {$this->model}");
            $this->activateCooldown($response);
            throw new AIFailoverException('gemini', "Gemini Quota/Rate Limit Exceeded (HTTP 429)", 429, 'RATE_LIMIT');
        }

        $this->releaseProbeOnNonRateLimitError($isRecoveryProbe);

        // 2. Server Errors (5xx) — standard failover without triggering 429 cooldown
        if ($statusCode >= 500) {
            throw new AIFailoverException('gemini', "Gemini Server Error (HTTP {$statusCode})", $statusCode, 'SERVER_ERROR');
        }

        // 3. Authentication / Key Errors (401, 403)
        if ($statusCode === 401 || $statusCode === 403) {
            Log::error("[GeminiService] Gemini API Key is invalid or forbidden (HTTP {$statusCode}). Please check GEMINI_API_KEY configuration.");
            throw new AIFailoverException('gemini', "Gemini Authentication Failure (HTTP {$statusCode})", $statusCode, 'AUTH_ERROR');
        }

        // 4. Model not found (404)
        if ($statusCode === 404) {
            Log::error("[GeminiService] Gemini model '{$this->model}' not found (HTTP 404). Check GEMINI_MODEL in .env.");
            throw new AIFailoverException('gemini', "Gemini model '{$this->model}' not found (HTTP 404).", 404, 'MODEL_NOT_FOUND');
        }

        // 5. Quota text on non-429 client error -> existing failover without 429 circuit breaker cooldown
        if (str_contains($body, 'RESOURCE_EXHAUSTED') || stripos($body, 'quota') !== false) {
            throw new AIFailoverException('gemini', "Gemini Quota Exceeded (HTTP {$statusCode})", $statusCode, 'RATE_LIMIT');
        }

        // 6. Other unrecoverable client errors (e.g. invalid format 400 not related to quota)
        throw new AIServiceException('gemini', "Gemini Bad Request (HTTP {$statusCode})", $statusCode);
    }

    /**
     * Gate Gemini requests against active 429 cooldown using atomic cache locks.
     *
     * @return bool True when this request is a single recovery probe after cooldown expiry.
     * @throws AIFailoverException
     */
    protected function acquireCircuitSlot(): bool
    {
        $cacheKey = $this->getCircuitBreakerCacheKey();
        if (!is_array(Cache::get($cacheKey))) {
            return false;
        }

        return (bool) $this->withCircuitBreakerLock(function () use ($cacheKey): bool {
            $state = Cache::get($cacheKey);
            if (!is_array($state)) {
                return false;
            }

            $nowTs = Carbon::now()->getTimestamp();
            $status = (string) ($state['state'] ?? 'open');

            if ($status === 'open') {
                $expiresAt = (int) ($state['expires_at'] ?? 0);
                if ($nowTs < $expiresAt) {
                    $remainingSeconds = max(1, $expiresAt - $nowTs);
                    Log::warning(sprintf(
                        '[GeminiService] Request skipped — circuit breaker cooldown active | Model: %s | CacheKey: %s | RemainingSeconds: %d',
                        $this->model,
                        $cacheKey,
                        $remainingSeconds
                    ));
                    throw new AIFailoverException(
                        'gemini',
                        "Gemini circuit breaker cooldown active ({$remainingSeconds}s remaining).",
                        429,
                        'CIRCUIT_OPEN'
                    );
                }

                $probeLeaseSeconds = max(5, min(30, $this->timeout));
                Cache::put($cacheKey, [
                    'state' => 'probing',
                    'model' => $this->model,
                    'activated_at' => (int) ($state['activated_at'] ?? $nowTs),
                    'expires_at' => $expiresAt,
                    'probe_started_at' => $nowTs,
                    'probe_expires_at' => $nowTs + $probeLeaseSeconds,
                ], $probeLeaseSeconds + 30);

                Log::info(sprintf(
                    '[GeminiService] Circuit breaker cooldown expired — retrying Gemini | Model: %s | CacheKey: %s',
                    $this->model,
                    $cacheKey
                ));

                return true;
            }

            if ($status === 'probing') {
                $probeExpiresAt = (int) ($state['probe_expires_at'] ?? 0);
                if ($nowTs < $probeExpiresAt) {
                    Log::warning(sprintf(
                        '[GeminiService] Request skipped — Gemini recovery probe already in progress | Model: %s | CacheKey: %s',
                        $this->model,
                        $cacheKey
                    ));
                    throw new AIFailoverException(
                        'gemini',
                        'Gemini recovery probe already in progress; routing to fallback.',
                        429,
                        'CIRCUIT_OPEN'
                    );
                }

                $probeLeaseSeconds = max(5, min(30, $this->timeout));
                Cache::put($cacheKey, [
                    'state' => 'probing',
                    'model' => $this->model,
                    'activated_at' => (int) ($state['activated_at'] ?? $nowTs),
                    'expires_at' => (int) ($state['expires_at'] ?? $nowTs),
                    'probe_started_at' => $nowTs,
                    'probe_expires_at' => $nowTs + $probeLeaseSeconds,
                ], $probeLeaseSeconds + 30);

                Log::info(sprintf(
                    '[GeminiService] Circuit breaker cooldown expired — retrying Gemini | Model: %s | CacheKey: %s',
                    $this->model,
                    $cacheKey
                ));

                return true;
            }

            return false;
        });
    }

    /**
     * Atomically open or extend HTTP 429 cooldown while honoring monotonic expiration bounds.
     */
    protected function activateCooldown(Response $response): void
    {
        $cacheKey = $this->getCircuitBreakerCacheKey();
        $resolved = $this->resolveCooldownDurationFromResponse($response);

        $result = $this->withCircuitBreakerLock(function () use ($cacheKey, $resolved): array {
            $nowTs = Carbon::now()->getTimestamp();
            $existing = Cache::get($cacheKey);
            $existingExpiresAt = (is_array($existing) && ($existing['state'] ?? '') === 'open')
                ? (int) ($existing['expires_at'] ?? 0)
                : 0;

            $candidateExpiresAt = $nowTs + $resolved['seconds'];
            $expiresAt = max($candidateExpiresAt, $existingExpiresAt);
            $effectiveDuration = max(1, $expiresAt - $nowTs);
            $source = ($expiresAt === $candidateExpiresAt)
                ? $resolved['source']
                : (string) ($existing['source'] ?? $resolved['source']);

            Cache::put($cacheKey, [
                'state' => 'open',
                'model' => $this->model,
                'activated_at' => $nowTs,
                'expires_at' => $expiresAt,
                'duration_seconds' => $effectiveDuration,
                'source' => $source,
            ], $effectiveDuration + 600);

            return [
                'duration' => $effectiveDuration,
                'source' => $source,
            ];
        });

        Log::warning(sprintf(
            '[GeminiService] Circuit breaker cooldown activated | Model: %s | CacheKey: %s | DurationSeconds: %d | Source: %s',
            $this->model,
            $cacheKey,
            $result['duration'],
            $result['source']
        ));
    }

    /**
     * Parse Retry-After header (seconds or HTTP-date) with safe min/max bounds, falling back to default 45s.
     *
     * @return array{seconds: int, source: string}
     */
    protected function resolveCooldownDurationFromResponse(Response $response): array
    {
        $defaultSeconds = $this->resolveCooldownSeconds();
        $maxSeconds = $this->resolveMaxCooldownSeconds();
        $rawHeader = $response->header('Retry-After');

        if (!is_string($rawHeader) || trim($rawHeader) === '') {
            return ['seconds' => $defaultSeconds, 'source' => 'default'];
        }

        $trimmed = trim($rawHeader);
        if (preg_match('/^\d+$/', $trimmed) === 1) {
            $parsedSeconds = (int) $trimmed;
            if ($parsedSeconds <= 0) {
                return ['seconds' => $defaultSeconds, 'source' => 'default'];
            }

            $bounded = max(5, min($maxSeconds, $parsedSeconds));
            return [
                'seconds' => $bounded,
                'source' => $bounded < $parsedSeconds ? 'retry_after_capped' : 'retry_after',
            ];
        }

        try {
            $retryDate = Carbon::parse($trimmed);
            $deltaSeconds = $retryDate->getTimestamp() - Carbon::now()->getTimestamp();
            if ($deltaSeconds <= 0) {
                return ['seconds' => $defaultSeconds, 'source' => 'default'];
            }

            $bounded = max(5, min($maxSeconds, $deltaSeconds));
            return [
                'seconds' => $bounded,
                'source' => $bounded < $deltaSeconds ? 'retry_after_capped' : 'retry_after',
            ];
        } catch (\Throwable) {
            return ['seconds' => $defaultSeconds, 'source' => 'default'];
        }
    }

    /**
     * Close circuit breaker once a post-cooldown probe succeeds.
     */
    protected function markCircuitRecovered(bool $isRecoveryProbe): void
    {
        if (!$isRecoveryProbe) {
            return;
        }

        $cacheKey = $this->getCircuitBreakerCacheKey();
        $this->withCircuitBreakerLock(function () use ($cacheKey): void {
            Cache::forget($cacheKey);
        });

        Log::info(sprintf(
            '[GeminiService] Gemini recovered — circuit breaker closed | Model: %s | CacheKey: %s',
            $this->model,
            $cacheKey
        ));
    }

    /**
     * Release temporary probing lease if a non-429 failure occurs right after cooldown expiry.
     */
    protected function releaseProbeOnNonRateLimitError(bool $isRecoveryProbe): void
    {
        if (!$isRecoveryProbe) {
            return;
        }

        $cacheKey = $this->getCircuitBreakerCacheKey();
        $this->withCircuitBreakerLock(function () use ($cacheKey): void {
            $state = Cache::get($cacheKey);
            if (is_array($state) && ($state['state'] ?? '') === 'probing') {
                Cache::forget($cacheKey);
            }
        });
    }

    /**
     * Execute critical circuit-breaker state transitions under atomic cache lock when supported.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    protected function withCircuitBreakerLock(callable $callback)
    {
        $store = Cache::getStore();
        if ($store instanceof LockProvider) {
            try {
                return Cache::lock($this->getCircuitBreakerLockKey(), 5)->block(3, $callback);
            } catch (LockTimeoutException) {
                return $callback();
            }
        }

        return $callback();
    }

    /**
     * Extract visible candidate text across parts while skipping internal reasoning-only parts.
     *
     * @param array<string, mixed> $candidate
     */
    protected function extractCandidateText(array $candidate): ?string
    {
        $parts = $candidate['content']['parts'] ?? null;
        if (!is_array($parts)) {
            return null;
        }

        $chunks = [];
        foreach ($parts as $part) {
            if (!is_array($part) || !empty($part['thought'])) {
                continue;
            }
            if (isset($part['text']) && is_string($part['text']) && trim($part['text']) !== '') {
                $chunks[] = $part['text'];
            }
        }

        return empty($chunks) ? null : implode("\n", $chunks);
    }

    /**
     * Record finishReason and usageMetadata token counters whenever output ends abnormally or hits MAX_TOKENS.
     *
     * @param array<string, mixed> $usage
     */
    protected function logTokenLimitEvent(string $finishReason, array $usage): void
    {
        $promptTokens = (int) ($usage['promptTokenCount'] ?? 0);
        $candidateTokens = (int) ($usage['candidatesTokenCount'] ?? 0);
        $thoughtsTokens = (int) ($usage['thoughtsTokenCount'] ?? 0);
        $totalTokens = (int) ($usage['totalTokenCount'] ?? ($promptTokens + $candidateTokens + $thoughtsTokens));

        Log::warning(sprintf(
            '[GeminiService] Non-STOP Finish | Model: %s | FinishReason: %s | MaxOutputTokens: %d | PromptTokens: %d | CandidatesTokens: %d | ThoughtsTokens: %d | TotalTokens: %d',
            $this->model,
            $finishReason,
            $this->maxOutputTokens,
            $promptTokens,
            $candidateTokens,
            $thoughtsTokens,
            $totalTokens
        ));
    }
}

