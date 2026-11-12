<?php

namespace App\Http\Controllers;

use App\Services\AI\AIRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    protected AIRouterService $aiRouter;

    public function __construct(AIRouterService $aiRouter)
    {
        $this->aiRouter = $aiRouter;
    }

    public function chat(Request $request)
    {
        // 1. Strict Request & History Element Validation
        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'required|string|in:user,model,assistant',
            'history.*.text' => 'required|string|max:3000',
            'history.*.isError' => 'nullable|boolean',
        ]);

        $userMessage = $this->sanitizeControlChars((string) $validated['message']);
        if ($userMessage === '') {
            return response()->json([
                'message' => 'Pertanyaan tidak boleh kosong.',
                'errors' => ['message' => ['Pertanyaan tidak boleh kosong.']],
            ], 422);
        }

        $history = collect($validated['history'] ?? [])
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item) {
                $cleanText = $this->sanitizeControlChars((string) ($item['text'] ?? ''));

                return [
                    'role' => (string) $item['role'],
                    'text' => $cleanText,
                    'isError' => (bool) ($item['isError'] ?? false),
                ];
            })
            ->filter(fn (array $item) => $item['text'] !== '')
            ->values()
            ->all();

        // 2. Delegate to AIRouterService (RAG + Gemini primary -> Groq fallback)
        try {
            $result = $this->aiRouter->chat($userMessage, $history);
        } catch (\Throwable $e) {
            Log::error('[ChatbotController] Unhandled chatbot exception | Type: ' . class_basename($e));

            return response()->json([
                'success' => false,
                'message' => 'Maaf, layanan Asisten Digital Kecamatan Cikampek sedang mengalami gangguan sementara. Silakan coba kembali beberapa saat lagi.',
            ], 500);
        }

        // 3. Return JSON response compatible with frontend component
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'reply' => $result['reply'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Maaf, Asisten Kecamatan Cikampek sedang mengalami kendala teknis. Silakan coba kembali beberapa saat lagi atau hubungi langsung petugas kantor Kecamatan Cikampek.',
        ]);
    }

    protected function sanitizeControlChars(string $input): string
    {
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $input) ?? $input;
        return trim($cleaned);
    }
}
