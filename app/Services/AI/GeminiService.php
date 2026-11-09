<?php

namespace App\Services\AI;

use App\Services\AI\Exceptions\AIFailoverException;
use App\Services\AI\Exceptions\AIServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?string $apiKey;
    protected string $model;
    protected int $timeout;
    protected int $maxRetries;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-3.6-flash');
        $this->timeout = (int) config('services.ai.timeout', 20);
        $this->maxRetries = (int) config('services.ai.max_retries', 1);
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

        // Format history into Gemini format
        $contents = [];
        foreach ($history as $msg) {
            if (!empty($msg['isError'])) {
                continue;
            }
            $role = ($msg['role'] ?? '') === 'user' ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => (string) ($msg['text'] ?? '')]
                ]
            ];
        }

        // Append current user message
        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $userMessage]
            ]
        ];

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemPrompt]
                ]
            ],
            'contents' => $contents
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->maxRetries, 300, function ($exception) {
                    return $exception instanceof ConnectionException;
                }, throw: false)
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            throw new AIFailoverException('gemini', 'Gemini connection timeout or network unreachable: ' . $e->getMessage(), 408, 'TIMEOUT', $e);
        } catch (\Exception $e) {
            throw new AIFailoverException('gemini', 'Gemini request exception: ' . $e->getMessage(), 500, 'NETWORK_ERROR', $e);
        }

        if ($response->successful()) {
            $data = $response->json();
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($reply !== null && trim($reply) !== '') {
                return trim($reply);
            }

            // Check if blocked by safety ratings or finished with other reasons
            $finishReason = $data['candidates'][0]['finishReason'] ?? 'EMPTY_RESPONSE';
            Log::warning("[GeminiService] Empty response received. Finish reason: {$finishReason}");
            throw new AIFailoverException('gemini', "Gemini returned empty response ({$finishReason}).", 500, 'EMPTY_RESPONSE');
        }

        $statusCode = $response->status();
        $body = $response->body();

        // 1. Rate Limit / Quota Exhausted
        if ($statusCode === 429 || str_contains($body, 'RESOURCE_EXHAUSTED') || stripos($body, 'quota') !== false) {
            throw new AIFailoverException('gemini', "Gemini Quota/Rate Limit Exceeded (HTTP {$statusCode})", 429, 'RATE_LIMIT');
        }

        // 2. Server Errors (5xx)
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

        // 5. Other unrecoverable client errors (e.g. invalid format 400 not related to quota)
        throw new AIServiceException('gemini', "Gemini Bad Request (HTTP {$statusCode})", $statusCode);
    }
}

