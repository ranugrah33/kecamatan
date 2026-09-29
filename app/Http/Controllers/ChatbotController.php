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
                $contextText = "Belum ada informasi resmi spesifik mengenai hal ini dalam basis data Asisten Kecamatan Cikampek.";
            }

            // 3. System Prompt Construction
            $systemPrompt = "Kamu adalah Asisten Digital Kecamatan Cikampek.
Tugas kamu adalah menjawab pertanyaan pengguna dengan bahasa Indonesia yang ramah, natural, mudah dipahami, tidak terlalu formal, dan tidak seperti robot.
Jika jawaban membutuhkan daftar atau langkah, gunakan format yang mudah dibaca.

ATURAN SUMBER JAWABAN (PRIORITAS):
1. Data resmi dari 'Context Data' di bawah ini.
2. Pengetahuan umum yang kamu miliki.

ATURAN MENJAWAB:
- Jika pertanyaan berkaitan dengan layanan/informasi spesifik Kecamatan Cikampek:
  * Jawab HANYA menggunakan informasi dari 'Context Data'.
  * JANGAN PERNAH mengarang informasi resmi (seperti biaya, persyaratan, jam pelayanan, nomor telepon, alamat, prosedur, kebijakan, nama pejabat, peraturan, atau jadwal) jika tidak ada di Context Data.
  * Jika informasi spesifik tersebut TIDAK ADA di Context Data, berikan respons variatif yang menyatakan bahwa informasi resmi belum tersedia di basis data kamu dan arahkan untuk menghubungi petugas Kecamatan Cikampek. Contoh variasi respons (buat senatural mungkin menyesuaikan konteks, jangan monoton): 'Saya belum menemukan informasi resmi mengenai hal tersebut dalam basis informasi saya.', 'Untuk informasi spesifik mengenai layanan tersebut, saya belum memiliki data yang cukup.', 'Informasi tersebut belum tersedia dalam data layanan Kecamatan Cikampek yang saya gunakan.'
- Jika pertanyaan bersifat administratif secara umum namun informasinya tidak ada di Context Data:
  * Kamu Boleh memberikan informasi umum, namun HARUS membedakan antara informasi umum dan resmi Kecamatan.
  * Gunakan awalan seperti 'Secara umum...', 'Pada umumnya...', lalu tambahkan kalimat 'Namun, untuk ketentuan resmi yang berlaku di Kecamatan Cikampek, sebaiknya dikonfirmasi langsung kepada petugas...'.
- Jika pertanyaan berupa pengetahuan umum (misal: 'Apa itu KTP?', 'Kenapa hujan turun?', 'Siapa penemu telepon?'):
  * Jawab secara normal berdasarkan pengetahuan umum yang kamu miliki, tanpa harus menyuruh menghubungi petugas.

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
                        'success' => false,
                        'message' => 'Maaf, Asisten Kecamatan Cikampek sedang mengalami kendala teknis. Silakan coba kembali beberapa saat lagi.'
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
                    'message' => 'Maaf, Asisten Kecamatan Cikampek sedang mengalami kendala teknis. Silakan coba kembali beberapa saat lagi.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Chatbot Controller Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Maaf, Asisten Kecamatan Cikampek sedang mengalami kendala teknis. Silakan coba kembali beberapa saat lagi.'
            ]);
        }
    }
}
