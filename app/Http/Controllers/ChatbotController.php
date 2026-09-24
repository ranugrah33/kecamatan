<?php

namespace App\Http\Controllers;

use App\Models\AiKnowledge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        // 1. Validation
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $userMessage = $request->input('message');

        try {
            // 2. Search Knowledge Base
            $userMessageClean = preg_replace('/[^a-z0-9 ]/', '', strtolower($userMessage));
            $keywords = array_filter(explode(' ', $userMessageClean), function($w) {
                return strlen($w) > 2;
            });
            
            $query = AiKnowledge::where('is_active', true);
            
            if (!empty($keywords)) {
                $query->where(function($q) use ($keywords) {
                    foreach ($keywords as $word) {
                        $q->orWhere('keywords', 'like', "%{$word}%")
                          ->orWhere('category', 'like', "%{$word}%")
                          ->orWhere('question', 'like', "%{$word}%");
                    }
                });
            }

            $knowledges = $query->orderBy('priority', 'desc')->take(3)->get();

            $contextText = "";
            if ($knowledges->count() > 0) {
                $contextText = "Berikut adalah informasi resmi dari database Kecamatan Cikampek:\n";
                foreach ($knowledges as $k) {
                    $contextText .= "- Kategori: {$k->category}\n";
                    $contextText .= "  Informasi: {$k->answer}\n\n";
                }
            } else {
                $contextText = "Tidak ada informasi spesifik yang ditemukan di database mengenai pertanyaan ini. Arahkan pengguna untuk menghubungi petugas kecamatan secara langsung.";
            }

            // 3. System Prompt Construction
            $systemPrompt = "Kamu adalah Asisten Digital Kecamatan Cikampek. Tugas kamu adalah membantu masyarakat mendapatkan informasi mengenai pelayanan dan informasi Kecamatan Cikampek. 
Gunakan HANYA informasi yang diberikan melalui context di bawah ini. Jangan mengarang informasi. Jangan membuat persyaratan, biaya, prosedur, jadwal, alamat, nomor kontak, atau kebijakan yang tidak terdapat di context. 
Jika informasi tidak tersedia, katakan bahwa informasi tersebut belum tersedia dalam basis informasi Asisten Kecamatan Cikampek dan silakan menghubungi petugas Kecamatan Cikampek untuk mendapatkan informasi resmi.
Gunakan bahasa Indonesia yang ramah, jelas, singkat, dan mudah dipahami. Jangan mengaku sebagai pegawai Kecamatan Cikampek. Kamu adalah asisten informasi digital. 
Jika pertanyaan berada di luar informasi Kecamatan Cikampek, arahkan pengguna untuk menghubungi petugas Kecamatan Cikampek. Jangan memberikan keputusan administratif. Jangan memberikan informasi yang tidak didukung oleh context.

Context Data:
" . $contextText;

            // 4. Call Gemini API
            $apiKey = config('services.gemini.api_key');
            $model = config('services.gemini.model', 'gemini-3.6-flash');

            if (!$apiKey) {
                Log::warning('Gemini API Key is not set. Using local knowledge fallback.');
                if ($knowledges->count() > 0) {
                    $reply = "Berdasarkan informasi Kecamatan Cikampek:\n";
                    foreach ($knowledges as $k) {
                        $reply .= "- " . $k->answer . "\n";
                    }
                    return response()->json([
                        'success' => true,
                        'reply' => $reply
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'reply' => 'Maaf, saya belum memiliki informasi mengenai hal tersebut. Silakan tanyakan hal lain seputar pelayanan publik Kecamatan Cikampek.'
                    ]);
                }
            }

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::timeout(15)->post($url, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nPertanyaan User: " . $userMessage]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak dapat memproses pertanyaan Anda saat ini.';
                
                return response()->json([
                    'success' => true,
                    'reply' => $reply
                ]);
            } else {
                Log::error('Gemini API Error: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => 'Maaf, Asisten Kecamatan sedang mengalami gangguan. Silakan coba kembali beberapa saat lagi.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Chatbot Controller Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Maaf, Asisten Kecamatan sedang mengalami gangguan. Silakan coba kembali beberapa saat lagi.'
            ]);
        }
    }
}
