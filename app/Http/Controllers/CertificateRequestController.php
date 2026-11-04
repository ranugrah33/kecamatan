<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateRequest;
use App\Models\CertificateFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateRequestController extends Controller
{
    public function index()
    {
        $requests = CertificateRequest::with('latestFile')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('masyarakat.sertifikat.index', compact('requests'));
    }

    public function create()
    {
        $user = Auth::user();
        return view('masyarakat.sertifikat.create', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'applicant_name' => 'required|string|max:255',
            'certificate_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'document_type' => 'required|in:Sertifikat,Piagam',
            'activity_name' => 'required|string|max:255',
            'activity_theme' => 'nullable|string|max:255',
            'activity_date' => 'required|date',
            'activity_place' => 'required|string|max:255',
            'certificate_text' => 'nullable|string',
            'purpose' => 'required|string',
            'description' => 'nullable|string',
            'application_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'applicant_name.required' => 'Nama pemohon wajib diisi.',
            'certificate_name.required' => 'Nama penerima yang akan dicetak pada sertifikat/piagam wajib diisi.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'document_type.required' => 'Pilih jenis dokumen (Sertifikat atau Piagam).',
            'activity_name.required' => 'Nama kegiatan / acara wajib diisi.',
            'activity_date.required' => 'Tanggal kegiatan wajib diisi.',
            'activity_place.required' => 'Tempat pelaksanaan kegiatan wajib diisi.',
            'purpose.required' => 'Keperluan pengajuan wajib diisi.',
        ]);

        // Handle file upload
        $filePath = null;
        if ($request->hasFile('application_file')) {
            $file = $request->file('application_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/surat_sertifikat'), $filename);
            $filePath = 'uploads/surat_sertifikat/' . $filename;
        }

        // Generate request code CERT-YYYYMMDD-XXXX
        $dateStr = date('Ymd');
        $lastRequest = CertificateRequest::where('request_code', 'like', 'CERT-' . $dateStr . '-%')
            ->orderBy('id', 'desc')->first();
        if ($lastRequest) {
            $lastNum = intval(substr($lastRequest->request_code, -4));
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }
        $requestCode = 'CERT-' . $dateStr . '-' . $newNum;

        // Jika teks keterangan khusus kosong, buat default template otomatis yang rapi
        $certificateText = $request->certificate_text;
        if (empty($certificateText)) {
            $certificateText = "Atas kontribusi dan partisipasi aktif dalam " . $request->activity_name . ", serta dedikasi dan kinerja yang ditunjukkan dalam mencapai tujuan kegiatan.";
        }

        $certRequest = CertificateRequest::create([
            'user_id' => Auth::id(),
            'request_code' => $requestCode,
            'applicant_name' => $request->applicant_name,
            'certificate_name' => $request->certificate_name,
            'phone' => $request->phone,
            'document_type' => $request->document_type,
            'activity_name' => $request->activity_name,
            'activity_theme' => $request->activity_theme,
            'activity_date' => $request->activity_date,
            'activity_place' => $request->activity_place,
            'certificate_text' => $certificateText,
            'purpose' => $request->purpose,
            'description' => $request->description,
            'application_file' => $filePath,
            'status' => 'Diajukan',
        ]);

        return redirect()->route('masyarakat.sertifikat.show', $certRequest->id)
            ->with('success', 'Pengajuan sertifikat/piagam berhasil dikirim!');
    }

    public function show($id)
    {
        $certRequest = CertificateRequest::with('files')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('masyarakat.sertifikat.show', compact('certRequest'));
    }

    public function downloadPdf($id)
    {
        $certRequest = CertificateRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!in_array($certRequest->status, ['Disetujui', 'Sertifikat Tersedia', 'Selesai'])) {
            return redirect()->back()->withErrors('Sertifikat belum dapat diunduh karena pengajuan belum disetujui.');
        }

        $bgPath = public_path('images/sertifikat-bg.png');
        $bgNeutralPath = public_path('images/sertifikat-frame-bg.png');
        $ttdPath = public_path('images/tanda-camat.png');

        $bgBase64 = file_exists($bgPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bgPath)) : '';
        $bgNeutralBase64 = file_exists($bgNeutralPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bgNeutralPath)) : '';
        $ttdBase64 = file_exists($ttdPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($ttdPath)) : '';

        $pdf = Pdf::loadView('admin.sertifikat.pdf', compact('certRequest', 'bgBase64', 'bgNeutralBase64', 'ttdBase64'))
            ->setPaper('a4', 'landscape');

        return $pdf->download(strtolower($certRequest->document_type) . '_' . $certRequest->request_code . '.pdf');
    }

    public function downloadFinal($id)
    {
        $certRequest = CertificateRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$certRequest->file_sertifikat_final) {
            return redirect()->back()->withErrors('File sertifikat final belum tersedia.');
        }

        return Storage::disk('public')->download($certRequest->file_sertifikat_final);
    }

    public function download($id)
    {
        $certRequest = CertificateRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Cek jika ada file_sertifikat_final
        if ($certRequest->file_sertifikat_final) {
            return Storage::disk('public')->download($certRequest->file_sertifikat_final);
        }

        $file = $certRequest->latestFile;
        if ($file) {
            $fullPath = storage_path('app/private/certificates/' . $file->file_path);
            if (file_exists($fullPath)) {
                return response()->download($fullPath, $file->original_filename);
            }
        }

        // Jika tidak ada file upload, tapi status Disetujui/Selesai, download auto-generated PDF
        if (in_array($certRequest->status, ['Disetujui', 'Sertifikat Tersedia', 'Selesai'])) {
            return $this->downloadPdf($id);
        }

        return redirect()->back()->withErrors('File sertifikat belum tersedia.');
    }
}
