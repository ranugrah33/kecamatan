<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateRequest;
use App\Models\CertificateFile;
use App\Services\PengajuanStatusNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function updateStatus(Request $request, $id, PengajuanStatusNotificationService $notificationService)
    {
        $reasonRequiredMessage = 'Alasan penolakan wajib diisi ketika status diubah menjadi Ditolak.';

        $request->validate([
            'status' => 'required|in:Diajukan,Diproses,Disetujui,Sertifikat Tersedia,Selesai,Ditolak',
            'rejection_reason' => PengajuanStatusNotificationService::reasonRules(
                $request->input('status'),
                ['Ditolak'],
                $reasonRequiredMessage
            ),
            'nomor_sertifikat' => 'nullable|string',
        ], [
            'rejection_reason.required' => $reasonRequiredMessage,
        ]);

        $certRequest = null;
        $oldStatus = null;

        DB::transaction(function () use ($request, $id, &$certRequest, &$oldStatus): void {
            $certRequest = CertificateRequest::where('id', $id)->lockForUpdate()->firstOrFail();
            $oldStatus = $certRequest->status;

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
        });

        $certRequest->refresh();

        $notifResult = $notificationService->notifyCertificateRequestIfNeeded($certRequest, $oldStatus);

        return redirect()
            ->route('admin.sertifikat.show', $id)
            ->with('success', PengajuanStatusNotificationService::buildStatusSuccessMessage($notifResult));
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
