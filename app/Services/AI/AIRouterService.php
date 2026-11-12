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
    protected IntentClassifierService $intentClassifier;
    protected WebSearchService $webSearchService;
    protected PiiMaskingService $piiMaskingService;
    protected string $primaryProvider;
    protected string $fallbackProvider;

    public function __construct(
        GeminiService $geminiService,
        GroqService $groqService,
        KnowledgeBaseService $knowledgeBaseService,
        ?IntentClassifierService $intentClassifier = null,
        ?WebSearchService $webSearchService = null,
        ?PiiMaskingService $piiMaskingService = null
    ) {
        $this->geminiService = $geminiService;
        $this->groqService = $groqService;
        $this->knowledgeBaseService = $knowledgeBaseService;
        $this->intentClassifier = $intentClassifier ?? new IntentClassifierService();
        $this->webSearchService = $webSearchService ?? new WebSearchService();
        $this->piiMaskingService = $piiMaskingService ?? new PiiMaskingService();
        $this->primaryProvider = config('services.ai.primary', 'gemini');
        $this->fallbackProvider = config('services.ai.fallback', 'groq');
    }

    /**
     * Route user chat through context-aware intent classification
     * (CONVERSATIONAL / SIPAF_RAG / GENERAL_LLM / CLARIFICATION / WEB_SEARCH / HYBRID_RAG_WEB)
     * to primary provider (Gemini) with automatic failover to fallback (Groq).
     *
     * @param string $userMessage
     * @param array $history
     * @return array
     */
    public function chat(string $userMessage, array $history = []): array
    {
        // 1. Resolve conversation context, classify intent & retrieve relevant KB / Web context only when needed
        $classification = $this->intentClassifier->classify($userMessage, $history);
        $intent = $classification['intent'];
        $context = $classification['context'] ?? [];
        $contextNote = $context['context_note'] ?? null;
        $resolutionType = $context['resolution_type'] ?? $classification['reason'];

        $webSearchResult = null;
        if (in_array($intent, [IntentClassifierService::INTENT_WEB_SEARCH, IntentClassifierService::INTENT_HYBRID_RAG_WEB], true)) {
            $webSearchResult = $this->webSearchService->search(
                (string) ($classification['web_query'] ?? $userMessage),
                ['reference_query' => $userMessage]
            );
        }

        $contextNoteMask = $contextNote !== null ? $this->piiMaskingService->maskText($contextNote) : ['text' => null, 'count' => 0, 'categories' => []];

        $ragPackage = $this->knowledgeBaseService->preparePromptForQuery(
            $classification['retrieval_query'],
            $intent,
            $classification['retrieval'],
            $contextNoteMask['text'],
            $webSearchResult
        );
        $systemPrompt = $ragPackage['system_prompt'];
        $retrievalResult = $ragPackage['retrieval'];

        // Prevent prompt-delimiter tag spoofing inside copies of user message & bounded relevant history
        $cleanedUserMessage = $this->knowledgeBaseService->sanitizeDataBlock($userMessage);
        $userMask = $this->piiMaskingService->maskText($cleanedUserMessage);
        $safeUserMessage = $userMask['text'];

        $relevantHistory = is_array($context['relevant_history'] ?? null)
            ? $context['relevant_history']
            : (new ConversationContextService())->sanitizeHistory($history);
        $cleanedHistory = array_values(array_filter(array_map(function ($item): ?array {
            if (!is_array($item) || !empty($item['isError'])) {
                return null;
            }

            $role = is_string($item['role'] ?? null) ? trim($item['role']) : '';
            if (!in_array($role, ['user', 'model', 'assistant'], true)) {
                return null;
            }

            $sanitizedText = $this->knowledgeBaseService->sanitizeDataBlock((string) ($item['text'] ?? ''));
            if ($sanitizedText === '') {
                return null;
            }

            return [
                'role' => $role,
                'text' => $sanitizedText,
                'isError' => false,
            ];
        }, $relevantHistory)));
        $historyMask = $this->piiMaskingService->maskHistory($cleanedHistory);
        $safeHistory = $historyMask['history'];

        $maskedCount = $contextNoteMask['count'] + $userMask['count'] + $historyMask['count'];
        $maskedCategories = array_values(array_unique(array_merge(
            $contextNoteMask['categories'],
            $userMask['categories'],
            $historyMask['categories']
        )));

        // Safe diagnostic logging (no API key, no user personal data or raw query text)
        Log::info(sprintf(
            '[AI RAG] Intent: %s (%s) | Resolution: %s | HistoryIn: %d -> Kept: %d | Strategy: %s | Retrieved: %d | WebStatus: %s | WebResults: %d | WebCached: %s | RetrievalTime: %.2fms | ContextChars: %d | SystemPromptChars: %d | QueryLen: %d | MaskedPII: %d (%s)',
            $intent,
            $classification['reason'],
            $resolutionType,
            count($history),
            count($safeHistory),
            $ragPackage['strategy'],
            $ragPackage['retrieved_count'],
            $webSearchResult['status'] ?? 'skipped',
            count($webSearchResult['results'] ?? []),
            !empty($webSearchResult['cached']) ? 'yes' : 'no',
            $ragPackage['retrieval_time_ms'],
            $ragPackage['context_chars'],
            mb_strlen($systemPrompt),
            mb_strlen($safeUserMessage),
            $maskedCount,
            empty($maskedCategories) ? 'none' : implode(',', $maskedCategories)
        ));

        // 2. Always attempt Primary Provider (Gemini) first
        $geminiStartTime = microtime(true);

        try {
            $reply = $this->geminiService->generateResponse($safeUserMessage, $safeHistory, $systemPrompt);
            $reply = $this->finalizeReply($reply, $intent, $webSearchResult);
            $latency = round(microtime(true) - $geminiStartTime, 2);

            Log::info("[AI Chatbot] Provider: Gemini | Intent: {$intent} | Status: Success | Latency: {$latency}s | RetrievedDocs: {$ragPackage['retrieved_count']}");

            return [
                'success' => true,
                'reply' => $reply,
                'provider' => 'gemini',
                'intent' => $intent,
                'resolution_type' => $resolutionType,
                'retrieved_count' => $ragPackage['retrieved_count'],
                'web_search' => $this->summarizeWebSearchMeta($webSearchResult),
            ];
        } catch (AIFailoverException $e) {
            $latency = round(microtime(true) - $geminiStartTime, 2);
            $reason = $e->getMessage();
            $reasonCode = $e->getReasonCode();
            $statusCode = $e->getStatusCode();

            Log::warning("[AI Chatbot] Provider: Gemini | Intent: {$intent} | Status: Failed | Reason: {$reason} (Code: {$reasonCode}, HTTP: {$statusCode}) | Latency: {$latency}s | Action: Failover to Groq");

            // 3. Failover to Fallback Provider (Groq) with the exact same system prompt, filtered history, & web context
            return $this->attemptFallback($safeUserMessage, $safeHistory, $systemPrompt, $reason, $retrievalResult, $ragPackage['retrieved_count'], $intent, $resolutionType, $webSearchResult);

        } catch (AIServiceException $e) {
            // Non-recoverable client error that shouldn't be retried on Groq
            $latency = round(microtime(true) - $geminiStartTime, 2);
            Log::error("[AI Chatbot] Provider: Gemini | Intent: {$intent} | Status: Failed (Non-Failover) | Reason: {$e->getMessage()} | Latency: {$latency}s");

            return [
                'success' => false,
                'reply' => null,
                'message' => 'Maaf, pertanyaan Anda tidak dapat diproses. Silakan formulirkan kembali pertanyaan Anda.',
                'provider' => 'gemini',
                'intent' => $intent,
                'resolution_type' => $resolutionType,
                'retrieved_count' => $ragPackage['retrieved_count'],
                'web_search' => $this->summarizeWebSearchMeta($webSearchResult),
            ];
        } catch (\Throwable $e) {
            // General unexpected failure in primary: attempt fallback
            $latency = round(microtime(true) - $geminiStartTime, 2);
            Log::error("[AI Chatbot] Provider: Gemini | Intent: {$intent} | Status: Unexpected Exception: {$e->getMessage()} | Latency: {$latency}s | Action: Failover to Groq");

            return $this->attemptFallback($safeUserMessage, $safeHistory, $systemPrompt, $e->getMessage(), $retrievalResult, $ragPackage['retrieved_count'], $intent, $resolutionType, $webSearchResult);
        }
    }

    /**
     * Execute fallback provider (Groq) with identical RAG, conversation, and web context.
     */
    protected function attemptFallback(
        string $userMessage,
        array $history,
        string $systemPrompt,
        string $failoverReason,
        array $retrievalResult = [],
        int $retrievedCount = 0,
        string $intent = IntentClassifierService::INTENT_SIPAF_RAG,
        string $resolutionType = 'direct',
        ?array $webSearchResult = null
    ): array {
        $groqStartTime = microtime(true);

        try {
            $reply = $this->groqService->generateResponse($userMessage, $history, $systemPrompt);
            $reply = $this->finalizeReply($reply, $intent, $webSearchResult);
            $latency = round(microtime(true) - $groqStartTime, 2);

            Log::info("[AI Chatbot] Provider: Groq | Intent: {$intent} | Status: Success | Reason: Fallback Activated ({$failoverReason}) | Latency: {$latency}s | RetrievedDocs: {$retrievedCount}");

            return [
                'success' => true,
                'reply' => $reply,
                'provider' => 'groq',
                'intent' => $intent,
                'resolution_type' => $resolutionType,
                'retrieved_count' => $retrievedCount,
                'web_search' => $this->summarizeWebSearchMeta($webSearchResult),
            ];
        } catch (\Throwable $e) {
            $latency = round(microtime(true) - $groqStartTime, 2);

            Log::error("[AI Chatbot] Provider: Groq | Intent: {$intent} | Status: Failed | Reason: {$e->getMessage()} | Latency: {$latency}s");
            Log::error("[AI Chatbot] Dual AI Failover Exhausted: Both Gemini and Groq failed to respond.");

            // Both providers failed: return safe consistent fallback from KnowledgeBaseService
            return [
                'success' => false,
                'reply' => null,
                'message' => $this->knowledgeBaseService->getEmergencyFallbackReply($retrievalResult),
                'provider' => null,
                'intent' => $intent,
                'resolution_type' => $resolutionType,
                'retrieved_count' => $retrievedCount,
                'web_search' => $this->summarizeWebSearchMeta($webSearchResult),
            ];
        }
    }

    /**
     * Ensure replies powered by web search always disclose verified source names, clickable URLs,
     * and checked date — without fabricating links or attaching irrelevant links when unverified.
     */
    protected function finalizeReply(string $reply, string $intent, ?array $webSearchResult): string
    {
        if (
            !in_array($intent, [IntentClassifierService::INTENT_WEB_SEARCH, IntentClassifierService::INTENT_HYBRID_RAG_WEB], true)
            || $webSearchResult === null
        ) {
            return $reply;
        }

        $checkedAtLabel = (string) ($webSearchResult['checked_at_label'] ?? '');
        $results = $webSearchResult['results'] ?? [];

        if (($webSearchResult['status'] ?? '') !== 'ok' || empty($results)) {
            if ($checkedAtLabel !== '' && !str_contains($reply, $checkedAtLabel)) {
                return rtrim($reply)
                    . "\n\n*Dicek pada {$checkedAtLabel}. Jika data belum tersedia dari pencarian otomatis, silakan merujuk kanal resmi terkait seperti karawangkab.go.id, kemendagri.go.id, atau Kantor Kecamatan Cikampek.*";
            }

            return $reply;
        }

        $hasVerifiedUrl = false;
        foreach ($results as $item) {
            $url = (string) ($item['url'] ?? '');
            if ($url !== '' && str_contains($reply, $url)) {
                $hasVerifiedUrl = true;
                break;
            }
        }

        $hasCheckedDate = $checkedAtLabel !== '' && str_contains($reply, $checkedAtLabel);

        // Do not force-append source links when the AI explicitly indicates info was not found or unverified
        if ($this->isUnverifiedOrNotFoundReply($reply) && !$hasVerifiedUrl) {
            if (!$hasCheckedDate && $checkedAtLabel !== '') {
                return rtrim($reply)
                    . "\n\n*Dicek pada {$checkedAtLabel}. Informasi belum berhasil diverifikasi dari sumber web yang relevan; silakan memeriksa kanal resmi terkait seperti karawangkab.go.id, jabarprov.go.id, atau kemendagri.go.id.*";
            }

            return $reply;
        }

        if ($hasVerifiedUrl && $hasCheckedDate) {
            return $reply;
        }

        $lines = [];
        if (!$hasVerifiedUrl) {
            $lines[] = "**Sumber Informasi (Dicek pada {$checkedAtLabel}):**";
            foreach (array_slice($results, 0, 3) as $item) {
                $domain = (string) ($item['domain'] ?? 'Sumber');
                $title = (string) ($item['title'] ?? $domain);
                $url = (string) ($item['url'] ?? '');
                $pub = ($item['published_date'] ?? '') !== '' ? " ({$item['published_date']})" : '';
                if ($url !== '') {
                    $lines[] = "- [{$domain} — {$title}]({$url}){$pub}";
                }
            }
        } elseif (!$hasCheckedDate && $checkedAtLabel !== '') {
            $lines[] = "*Tanggal pengecekan informasi: {$checkedAtLabel}*";
        }

        if (empty($lines)) {
            return $reply;
        }

        return rtrim($reply) . "\n\n" . implode("\n", $lines);
    }

    protected function isUnverifiedOrNotFoundReply(string $reply): bool
    {
        $lower = mb_strtolower($reply);

        return preg_match(
            '/\b(belum\s+(berhasil\s+)?(ditemukan|menemukan|dapat\s+diverifikasi|terverifikasi|terkonfirmasi)|tidak\s+(berhasil\s+)?(ditemukan|menemukan)|tidak\s+dapat\s+(diverifikasi|memastikan|mengonfirmasi)|belum\s+dapat\s+(memastikan|mengonfirmasi|diverifikasi)|tidak\s+memiliki\s+informasi\s+(terbaru|yang\s+pasti|mengenai|tentang)|informasi\s+(terbaru\s+)?(yang\s+)?(diminta\s+)?(belum|tidak)\s+(tersedia|ditemukan|terverifikasi|relevan)|hasil\s+pencarian\s+(tidak|belum)\s+(relevan|memuat|menampilkan|mencantumkan)|tidak\s+tercantum\s+dalam\s+(hasil|sumber)\s+pencarian|belum\s+tersedia\s+dalam\s+(hasil|sumber)\s+pencarian)\b/u',
            $lower
        ) === 1;
    }

    protected function summarizeWebSearchMeta(?array $webSearchResult): ?array
    {
        if ($webSearchResult === null) {
            return null;
        }

        return [
            'status' => $webSearchResult['status'] ?? 'unknown',
            'cached' => (bool) ($webSearchResult['cached'] ?? false),
            'checked_at_label' => $webSearchResult['checked_at_label'] ?? null,
            'result_count' => count($webSearchResult['results'] ?? []),
        ];
    }
}
