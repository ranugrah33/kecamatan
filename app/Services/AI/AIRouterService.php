<?php

namespace App\Services\AI;

use App\Services\AI\Exceptions\AIFailoverException;
use App\Services\AI\Exceptions\AIServiceException;
use Illuminate\Support\Facades\Log;

class AIRouterService
{
    protected GeminiService $geminiService;
    protected GroqService $groqService;
    protected KnowledgeBaseService $knowledgeBaseService;
    protected string $primaryProvider;
    protected string $fallbackProvider;

    public function __construct(
        GeminiService $geminiService,
        GroqService $groqService,
        KnowledgeBaseService $knowledgeBaseService
    ) {
        $this->geminiService = $geminiService;
        $this->groqService = $groqService;
        $this->knowledgeBaseService = $knowledgeBaseService;
        $this->primaryProvider = config('services.ai.primary', 'gemini');
        $this->fallbackProvider = config('services.ai.fallback', 'groq');
    }

    /**
     * Route user chat to primary provider (Gemini) with automatic failover to fallback (Groq).
     *
     * @param string $userMessage
     * @param array $history
     * @return array
     */
    public function chat(string $userMessage, array $history = []): array
    {
        $systemPrompt = $this->knowledgeBaseService->getSystemPrompt();

        // 1. Always attempt Primary Provider (Gemini) first
        $geminiStartTime = microtime(true);

        try {
            $reply = $this->geminiService->generateResponse($userMessage, $history, $systemPrompt);
            $latency = round(microtime(true) - $geminiStartTime, 2);

            Log::info("[AI Chatbot] Provider: Gemini | Status: Success | Latency: {$latency}s");

            return [
                'success' => true,
                'reply' => $reply,
                'provider' => 'gemini',
            ];
        } catch (AIFailoverException $e) {
            $latency = round(microtime(true) - $geminiStartTime, 2);
            $reason = $e->getMessage();
            $reasonCode = $e->getReasonCode();
            $statusCode = $e->getStatusCode();

            Log::warning("[AI Chatbot] Provider: Gemini | Status: Failed | Reason: {$reason} (Code: {$reasonCode}, HTTP: {$statusCode}) | Latency: {$latency}s | Action: Failover to Groq");

            // 2. Failover to Fallback Provider (Groq)
            return $this->attemptFallback($userMessage, $history, $systemPrompt, $reason);

        } catch (AIServiceException $e) {
            // Non-recoverable client error that shouldn't be retried on Groq
            $latency = round(microtime(true) - $geminiStartTime, 2);
            Log::error("[AI Chatbot] Provider: Gemini | Status: Failed (Non-Failover) | Reason: {$e->getMessage()} | Latency: {$latency}s");

            return [
                'success' => false,
                'reply' => null,
                'message' => 'Maaf, pertanyaan Anda tidak dapat diproses. Silakan formulirkan kembali pertanyaan Anda.',
                'provider' => 'gemini',
            ];
        } catch (\Throwable $e) {
            // General unexpected failure in primary: attempt fallback
            $latency = round(microtime(true) - $geminiStartTime, 2);
            Log::error("[AI Chatbot] Provider: Gemini | Status: Unexpected Exception: {$e->getMessage()} | Latency: {$latency}s | Action: Failover to Groq");

            return $this->attemptFallback($userMessage, $history, $systemPrompt, $e->getMessage());
        }
    }

    /**
     * Execute fallback provider (Groq).
     */
    protected function attemptFallback(string $userMessage, array $history, string $systemPrompt, string $failoverReason): array
    {
        $groqStartTime = microtime(true);

        try {
            $reply = $this->groqService->generateResponse($userMessage, $history, $systemPrompt);
            $latency = round(microtime(true) - $groqStartTime, 2);

            Log::info("[AI Chatbot] Provider: Groq | Status: Success | Reason: Fallback Activated ({$failoverReason}) | Latency: {$latency}s");

            return [
                'success' => true,
                'reply' => $reply,
                'provider' => 'groq',
            ];
        } catch (\Throwable $e) {
            $latency = round(microtime(true) - $groqStartTime, 2);

            Log::error("[AI Chatbot] Provider: Groq | Status: Failed | Reason: {$e->getMessage()} | Latency: {$latency}s");
            Log::error("[AI Chatbot] Dual AI Failover Exhausted: Both Gemini and Groq failed to respond.");

            // Both providers failed: return friendly message for citizens
            return [
                'success' => false,
                'reply' => null,
                'message' => 'Maaf, layanan Asisten AI Kecamatan Cikampek sedang mengalami kendala teknis. Silakan coba kembali beberapa saat lagi atau hubungi langsung petugas pelayanan di kantor Kecamatan Cikampek.',
                'provider' => null,
            ];
        }
    }
}

