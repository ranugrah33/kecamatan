<?php

namespace App\Services\AI;

use App\Services\AI\Exceptions\AIServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected ?string $apiKey;
    protected string $model;
    protected int $timeout;
    protected int $maxRetries;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->model = config('services.groq.model', 'qwen/qwen3.8-27b');
        $this->timeout = (int) config('services.ai.timeout', 20);
        $this->maxRetries = (int) config('services.ai.max_retries', 1);
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

        $messages = [];

        // 1. System Prompt
        $messages[] = [
            'role' => 'system',
            'content' => $systemPrompt
        ];

        // 2. Conversation History
        foreach ($history as $msg) {
            if (!empty($msg['isError'])) {
                continue;
            }
            // In OpenAI / Groq standard: 'user' or 'assistant'
            $role = ($msg['role'] ?? '') === 'user' ? 'user' : 'assistant';
            $messages[] = [
                'role' => $role,
                'content' => (string) ($msg['text'] ?? '')
            ];
        }

        // 3. Current User Message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.5,
            'max_tokens' => 1024,
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
            $reply = $data['choices'][0]['message']['content'] ?? null;

            if ($reply !== null && trim($reply) !== '') {
                return trim($reply);
            }

            Log::warning('[GroqService] Empty response received from Groq.');
            throw new AIServiceException('groq', 'Groq returned empty response.', 500);
        }

        $statusCode = $response->status();
        $body = $response->body();

        Log::error("[GroqService] Groq API Error (HTTP {$statusCode}): {$body}");
        throw new AIServiceException('groq', "Groq API Error (HTTP {$statusCode})", $statusCode);
    }
}

