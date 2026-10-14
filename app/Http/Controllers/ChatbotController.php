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
            // 2. Load All Active Knowledge Base
            $knowledges = AiKnowledge::where('is_active', true)->get();

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
Tugas kamu adalah menjawab pertanyaan pengguna dengan bahasa Indonesia yang ramah, natural, profesional, mudah dipahami masyarakat umum, dan langsung pada intinya (tidak kaku seperti robot).

ATURAN SUMBER JAWABAN (PRIORITAS):
1. Data resmi dari 'Context Data' di bawah ini.
2. Pengetahuan umum yang kamu miliki.

ATURAN MENJAWAB:
- Khusus jika pertanyaan berkaitan dengan pembuatan, pengurusan, atau perbaikan KTP, Kartu Keluarga (KK), dan Akta (Akta Kelahiran, Kematian, dll):
  * Jawab dengan jelas dan ramah bahwa pelayanan untuk ketiga dokumen tersebut (KTP, KK, dan Akta) sudah TIDAK ADA di kantor Kecamatan.
  * Informasikan bahwa pelayanan tersebut sudah dipindahkan atau dialihkan ke gedung di sebelah kantor kecamatan, yaitu gedung Mall Pelayanan Publik (MPP).
- Jika pertanyaan berkaitan dengan layanan/informasi spesifik Kecamatan Cikampek:
  * Pahami maksud (intent) dari pengguna. Mereka mungkin menggunakan kalimat informal, typo, atau singkatan (misal: 'minjem aula', 'ijin umkm').
  * Jawab menggunakan informasi dari 'Context Data'.
  * JANGAN PERNAH mengarang informasi resmi (seperti biaya, persyaratan, jam pelayanan, prosedur, nomor telepon) jika tidak ada di Context Data.
  * Jika informasi spesifik tersebut TIDAK ADA di Context Data, katakan secara jujur: 'Maaf, informasi tersebut belum tersedia dalam data Asisten Kecamatan Cikampek.', lalu arahkan untuk bertanya ke petugas.
- Jika pertanyaan terlalu ambigu atau kurang jelas:
  * Jangan langsung menjawab asal. Berikan respons klarifikasi. Contoh: 'Maaf, apakah maksud Anda ingin bertanya tentang peminjaman aula atau hal lain?'
- Mengenai profil wilayah & luas 10 desa di Kecamatan Cikampek (Total luas 47,60 km²):
  * Kalihurip: 19,91 km²
  * Kamojing: 12,31 km²
  * Dawuan Barat: 3,19 km²
  * Dawuan Timur: 2,80 km²
  * Dawuan Tengah: 2,27 km²
  * Cikampek Barat: 1,94 km²
  * Cikampek Pusaka: 1,87 km²
  * Cikampek Selatan: 1,22 km²
  * Cikampek Timur: 1,12 km²
  * Cikampek Kota: 0,97 km²
- Jika pertanyaan bersifat umum (misal: 'Apa itu AI?', 'Cara membuat CV?'):
  * Jawab secara normal berdasarkan pengetahuan umum yang kamu miliki.
- Jika ditanya 'Siapa kamu?' atau sejenisnya:
  * Jawab: 'Saya Asisten Kecamatan Cikampek. Saya dapat membantu memberikan informasi mengenai layanan dan administrasi Kecamatan Cikampek serta menjawab pertanyaan umum.'

Context Data:
" . $contextText;

            // 4. Construct Gemini API Request with History
            $history = $request->input('history', []);
            $contents = [];

            foreach ($history as $msg) {
                if (isset($msg['isError']) && $msg['isError']) continue;
                $role = $msg['role'] === 'user' ? 'user' : 'model';
                $contents[] = [
                    'role' => $role,
                    'parts' => [
                        ['text' => $msg['text']]
                    ]
                ];
            }

            // Append the latest message with the system prompt context
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => "INSTRUKSI SISTEM:\n" . $systemPrompt . "\n\nPertanyaan User: " . $userMessage]
                ]
            ];

            $apiKey = config('services.gemini.api_key');
            $model = config('services.gemini.model', 'gemini-3.6-flash');

            $fallbackResponse = function() use ($knowledges) {
                if ($knowledges->count() > 0) {
                    $reply = "Berdasarkan informasi Kecamatan Cikampek:\n";
                    foreach ($knowledges as $k) {
                        $reply .= "- " . $k->answer . "\n\n";
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
            };

            if (!$apiKey) {
                Log::warning('Gemini API Key is not set. Using local knowledge fallback.');
                return $fallbackResponse();
            }

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::timeout(15)->post($url, [
                'contents' => $contents
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
                return $fallbackResponse();
            }

        } catch (\Exception $e) {
            Log::error('Chatbot Controller Exception: ' . $e->getMessage());
            return isset($fallbackResponse) ? $fallbackResponse() : response()->json([
                'success' => false,
                'message' => 'Maaf, Asisten Kecamatan Cikampek sedang mengalami kendala teknis. Silakan coba kembali beberapa saat lagi.'
            ]);
        }
    }
}
