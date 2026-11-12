<?php

namespace App\Services\AI;

use App\Services\AI\Exceptions\AIServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected const TRUNCATION_NOTICE = "\n\n*(Catatan: Jawaban terpotong karena mencapai batas maksimum panjang respons AI. Silakan ajukan pertanyaan lanjutan yang lebih spesifik jika ingin melanjutkan penjelasan.)*";

    protected ?string $apiKey;
    protected string $model;
    protected int $timeout;
    protected int $maxRetries;
    protected int $maxTokens;
    protected PiiMaskingService $piiMaskingService;

    public function __construct(?PiiMaskingService $piiMaskingService = null)
    {
        $this->apiKey = config('services.groq.api_key');
        $this->model = config('services.groq.model', 'qwen/qwen3.8-27b');
        $this->timeout = (int) config('services.ai.timeout', 20);
        $this->maxRetries = (int) config('services.ai.max_retries', 1);
        $this->maxTokens = $this->resolveMaxTokens();
        $this->piiMaskingService = $piiMaskingService ?? new PiiMaskingService();
    }

    /**
     * Resolve configurable max_tokens with conservative safety bounds [256..4096].
     */
    protected function resolveMaxTokens(): int
    {
        return max(256, min(4096, (int) config('services.groq.max_tokens', 1024)));
    }

    /**
     * Send prompt to Groq Chat Completions API.
     *
     * @param string $userMessage
     * @param array $history
     * @param string $systemPrompt
     * @return string
     * @throws AIServiceException
     */
    public function generateResponse(string $userMessage, array $history, string $systemPrompt): string
    {
        if (empty($this->apiKey)) {
            Log::warning('[GroqService] Groq API Key is missing. Administrator should check GROQ_API_KEY in .env.');
            throw new AIServiceException('groq', 'Groq API Key is not configured.', 500);
        }

        $this->maxTokens = $this->resolveMaxTokens();
        $messages = $this->buildMessages($userMessage, $history, $systemPrompt);

        // Send only proven OpenAI-compatible parameters; avoid unverified reasoning_effort/reasoning_format flags on qwen/qwen3.8-27b
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.5,
            'max_tokens' => $this->maxTokens,
        ];

        $url = 'https://api.groq.com/openai/v1/chat/completions';

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->retry($this->maxRetries, 300, function ($exception) {
                    return $exception instanceof ConnectionException;
                }, throw: false)
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            throw new AIServiceException('groq', 'Groq connection timeout or network unreachable: ' . $e->getMessage(), 408, $e);
        } catch (\Exception $e) {
            throw new AIServiceException('groq', 'Groq request exception: ' . $e->getMessage(), 500, $e);
        }

        if ($response->successful()) {
            $data = $response->json();
            return $this->parseSuccessfulResponse(is_array($data) ? $data : []);
        }

        $statusCode = $response->status();
        $bodySnippet = preg_replace(
            '/(gsk_[A-Za-z0-9_-]+|AIza[A-Za-z0-9_-]+)/',
            '[REDACTED]',
            mb_substr($response->body(), 0, 300)
        ) ?? mb_substr($response->body(), 0, 300);

        Log::error("[GroqService] Groq API Error (HTTP {$statusCode}): {$bodySnippet}");
        throw new AIServiceException('groq', "Groq API Error (HTTP {$statusCode})", $statusCode);
    }

    /**
     * Build OpenAI/Groq chat messages array from system prompt, valid history, and current query.
     *
     * @param array<int, array<string, mixed>> $history
     * @return array<int, array{role: string, content: string}>
     */
    protected function buildMessages(string $userMessage, array $history, string $systemPrompt): array
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Defense-in-depth role allowlist & PII masking on outbound copies (drop system/developer/unknown roles)
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

            $role = $rawRole === 'user' ? 'user' : 'assistant';
            $messages[] = [
                'role' => $role,
                'content' => $maskedText,
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $this->piiMaskingService->mask($userMessage)];

        return $messages;
    }

    /**
     * Validate Groq completion choice, inspect finish_reason, monitor usage tokens, and strip reasoning blocks.
     *
     * @param array<string, mixed> $data
     * @throws AIServiceException
     */
    protected function parseSuccessfulResponse(array $data): string
    {
        $choice = is_array($data['choices'][0] ?? null) ? $data['choices'][0] : [];
        $rawFinishReason = $choice['finish_reason'] ?? null;
        $finishReason = is_string($rawFinishReason) && trim($rawFinishReason) !== ''
            ? strtolower(trim($rawFinishReason))
            : 'unspecified';
        $usage = is_array($data['usage'] ?? null) ? $data['usage'] : [];

        $rawContent = $choice['message']['content'] ?? null;
        $hadThinkingTag = is_string($rawContent) && stripos($rawContent, '<think') !== false;
        $cleanReply = is_string($rawContent) ? $this->stripReasoningTags($rawContent) : '';

        if ($cleanReply === '') {
            $statusLabel = $hadThinkingTag
                ? 'Reasoning-only response yielded empty visible content'
                : 'Empty response received from Groq';
            $this->logUsageEvent('warning', $statusLabel, $finishReason, $usage, $hadThinkingTag, 0);

            $errorDetail = $hadThinkingTag
                ? "Groq returned reasoning-only response with no visible content (finish_reason: {$finishReason})."
                : "Groq returned empty response (finish_reason: {$finishReason}).";

            throw new AIServiceException('groq', $errorDetail, 500);
        }

        if ($finishReason === 'length') {
            $this->logUsageEvent(
                'warning',
                'Truncated response reached max_tokens',
                $finishReason,
                $usage,
                $hadThinkingTag,
                mb_strlen($cleanReply)
            );

            // Do not treat truncated completion as full answer or trigger automatic retry loops
            return $cleanReply . self::TRUNCATION_NOTICE;
        }

        if (!in_array($finishReason, ['stop', 'unspecified'], true)) {
            $this->logUsageEvent(
                'warning',
                'Non-stop completion finish',
                $finishReason,
                $usage,
                $hadThinkingTag,
                mb_strlen($cleanReply)
            );

            return $cleanReply;
        }

        $this->logUsageEvent(
            'info',
            'Response completed',
            $finishReason,
            $usage,
            $hadThinkingTag,
            mb_strlen($cleanReply)
        );

        return $cleanReply;
    }

    /**
     * Log safe token usage and finish_reason diagnostics without secrets, PII, or message contents.
     *
     * @param array<string, mixed> $usage
     */
    protected function logUsageEvent(
        string $level,
        string $status,
        string $finishReason,
        array $usage,
        bool $reasoningStripped,
        int $replyChars
    ): void {
        $hasPrompt = isset($usage['prompt_tokens']) && is_numeric($usage['prompt_tokens']);
        $hasCompletion = isset($usage['completion_tokens']) && is_numeric($usage['completion_tokens']);
        $hasTotal = isset($usage['total_tokens']) && is_numeric($usage['total_tokens']);

        if ($hasPrompt || $hasCompletion || $hasTotal) {
            $promptTokens = $hasPrompt ? (int) $usage['prompt_tokens'] : 0;
            $completionTokens = $hasCompletion ? (int) $usage['completion_tokens'] : 0;
            $totalTokens = $hasTotal ? (int) $usage['total_tokens'] : ($promptTokens + $completionTokens);
            $usageSummary = sprintf(
                'PromptTokens: %d | CompletionTokens: %d | TotalTokens: %d',
                $promptTokens,
                $completionTokens,
                $totalTokens
            );
        } else {
            $usageSummary = 'Usage: unavailable';
        }

        $message = sprintf(
            '[GroqService] %s | Model: %s | FinishReason: %s | MaxTokens: %d | ReasoningStripped: %s | %s | ReplyChars: %d',
            $status,
            $this->model,
            $finishReason,
            $this->maxTokens,
            $reasoningStripped ? 'yes' : 'no',
            $usageSummary,
            $replyChars
        );

        if ($level === 'warning') {
            Log::warning($message);
            return;
        }

        Log::info($message);
    }

    /**
     * Strip internal reasoning blocks (e.g. <think>...</think> or truncated unclosed <think>) emitted by reasoning models.
     */
    protected function stripReasoningTags(string $text): string
    {
        $cleaned = preg_replace('/<think\b[^>]*>.*?<\/think>/is', '', $text) ?? $text;
        $cleaned = preg_replace('/<think\b[^>]*>.*$/is', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/<\/think>/is', '', $cleaned) ?? $cleaned;
        return trim($cleaned);
    }
}

