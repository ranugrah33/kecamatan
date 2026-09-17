<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateRequest;
use App\Models\CertificateFile;
use Illuminate\Support\Facades\Auth;

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
            'status' => 'required|in:Diajukan,Diproses,Menunggu Dokumen,Selesai,Ditolak',
            'rejection_reason' => 'nullable|string',
        ]);

        $certRequest = CertificateRequest::findOrFail($id);

        $certRequest->update([
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('admin.sertifikat.show', $id)->with('success', 'Status pengajuan berhasil diperbarui!');
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
