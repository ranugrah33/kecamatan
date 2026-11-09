<?php

namespace App\Http\Controllers;

use App\Services\AI\AIRouterService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    protected AIRouterService $aiRouter;

    public function __construct(AIRouterService $aiRouter)
    {
        $this->aiRouter = $aiRouter;
    }

    public function chat(Request $request)
    {
        // 1. Request Validation
        $request->validate([
            'message' => 'required|string|max:500',
            'history' => 'nullable|array|max:20',
        ]);

        $userMessage = $request->input('message');
        $history = $request->input('history', []);

        // 2. Delegate to AIRouterService (Gemini primary -> Groq fallback)
        $result = $this->aiRouter->chat($userMessage, $history);

        // 3. Return JSON response compatible with frontend component
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'reply' => $result['reply'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Maaf, Asisten Kecamatan Cikampek sedang mengalami kendala teknis. Silakan coba kembali beberapa saat lagi atau hubungi petugas kantor Kecamatan Cikampek.',
        ]);
    }
}
