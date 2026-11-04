<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateRequest;
use App\Models\CertificateFile;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminCertificateController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = CertificateRequest::with(['user', 'latestFile']);

        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();
        return view('admin.sertifikat.index', compact('requests'));
    }

    public function show($id)
    {
        $certRequest = CertificateRequest::with(['user.masyarakat', 'files'])->findOrFail($id);
        return view('admin.sertifikat.show', compact('certRequest'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Diajukan,Diproses,Disetujui,Sertifikat Tersedia,Selesai,Ditolak',
            'rejection_reason' => 'nullable|string',
            'nomor_sertifikat' => 'nullable|string',
        ]);

        $certRequest = CertificateRequest::findOrFail($id);

        $data = [
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason,
        ];

        if ($request->filled('nomor_sertifikat')) {
            $data['nomor_sertifikat'] = $request->nomor_sertifikat;
        } elseif (in_array($request->status, ['Disetujui', 'Sertifikat Tersedia', 'Selesai']) && empty($certRequest->nomor_sertifikat)) {
            $year = date('Y');
            $count = CertificateRequest::whereNotNull('nomor_sertifikat')->whereYear('created_at', $year)->count() + 1;
            $nomor = str_pad($count, 3, '0', STR_PAD_LEFT);
            $data['nomor_sertifikat'] = "421.1/{$nomor}/Kec-Ckp/{$year}";
        }

        $certRequest->update($data);

        return redirect()->route('admin.sertifikat.show', $id)->with('success', 'Status pengajuan berhasil diperbarui!');
    }

    public function cetakSertifikat($id)
    {
        $certRequest = CertificateRequest::with('user')->findOrFail($id);

        // Jika belum ada nomor sertifikat, buat otomatis
        if (empty($certRequest->nomor_sertifikat)) {
            $year = date('Y');
            $count = CertificateRequest::whereNotNull('nomor_sertifikat')->whereYear('created_at', $year)->count() + 1;
            $nomor = str_pad($count, 3, '0', STR_PAD_LEFT);
            $nomor_sertifikat = "421.1/{$nomor}/Kec-Ckp/{$year}";

            $newStatus = in_array($certRequest->status, ['Diajukan', 'Diproses']) ? 'Disetujui' : $certRequest->status;

            $certRequest->update([
                'nomor_sertifikat' => $nomor_sertifikat,
                'status' => $newStatus,
            ]);
        }

        $bgPath = public_path('images/sertifikat-bg.png');
        $bgNeutralPath = public_path('images/sertifikat-frame-bg.png');
        $ttdPath = public_path('images/tanda-camat.png');

        $bgBase64 = file_exists($bgPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bgPath)) : '';
        $bgNeutralBase64 = file_exists($bgNeutralPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bgNeutralPath)) : '';
        $ttdBase64 = file_exists($ttdPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($ttdPath)) : '';

        $pdf = Pdf::loadView('admin.sertifikat.pdf', compact('certRequest', 'bgBase64', 'bgNeutralBase64', 'ttdBase64'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('draft_' . strtolower($certRequest->document_type) . '_' . $certRequest->request_code . '.pdf');
    }

    public function uploadCertificateFinal(Request $request, $id)
    {
        $request->validate([
            'file_sertifikat_final' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $certRequest = CertificateRequest::findOrFail($id);

        if ($request->hasFile('file_sertifikat_final')) {
            $file = $request->file('file_sertifikat_final');
            $filename = time() . '_final_' . $certRequest->request_code . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('sertifikat_final', $filename, 'public');

            $certRequest->update([
                'file_sertifikat_final' => $path,
                'status' => 'Selesai',
            ]);

            return redirect()->back()->with('success', 'Sertifikat final berhasil diunggah. Masyarakat kini dapat mengunduhnya.');
        }

        return redirect()->back()->withErrors('Gagal mengunggah file.');
    }

    public function uploadCertificate(Request $request, $id)
    {
        $request->validate([
            'certificate_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $certRequest = CertificateRequest::findOrFail($id);

        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $originalFilename = $file->getClientOriginalName();
            $hashedFilename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Store in private storage (not publicly accessible)
            $directory = storage_path('app/private/certificates');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            $file->move($directory, $hashedFilename);

            CertificateFile::create([
                'certificate_request_id' => $certRequest->id,
                'file_path' => $hashedFilename,
                'original_filename' => $originalFilename,
                'uploaded_by' => Auth::id(),
            ]);

            // Auto update status to Selesai
            $certRequest->update(['status' => 'Selesai']);

            return redirect()->back()->with('success', 'Sertifikat/piagam berhasil diunggah. Masyarakat kini dapat mengunduhnya.');
        }

        return redirect()->back()->withErrors('Gagal mengunggah file.');
    }

    public function downloadApplication($id)
    {
        $certRequest = CertificateRequest::findOrFail($id);

        if (!$certRequest->application_file) {
            return redirect()->back()->withErrors('Surat permohonan tidak tersedia.');
        }

        $fullPath = public_path($certRequest->application_file);
        if (!file_exists($fullPath)) {
            return redirect()->back()->withErrors('File tidak ditemukan.');
        }

        return response()->download($fullPath);
    }
}
