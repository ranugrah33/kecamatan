<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateRequest;
use App\Models\CertificateFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'phone' => 'required|string|max:20',
            'document_type' => 'required|in:Sertifikat,Piagam',
            'activity_name' => 'required|string|max:255',
            'activity_theme' => 'nullable|string|max:255',
            'activity_date' => 'required|date',
            'activity_place' => 'required|string|max:255',
            'purpose' => 'required|string',
            'description' => 'nullable|string',
            'application_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
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

        $user = Auth::user();

        $certRequest = CertificateRequest::create([
            'user_id' => Auth::id(),
            'request_code' => $requestCode,
            'applicant_name' => $request->applicant_name,
            'nik' => $user->nik,
            'phone' => $request->phone,
            'document_type' => $request->document_type,
            'activity_name' => $request->activity_name,
            'activity_theme' => $request->activity_theme,
            'activity_date' => $request->activity_date,
            'activity_place' => $request->activity_place,
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

    public function download($id)
    {
        $certRequest = CertificateRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $file = $certRequest->latestFile;
        if (!$file) {
            return redirect()->back()->withErrors('File sertifikat belum tersedia.');
        }

        $fullPath = storage_path('app/private/certificates/' . $file->file_path);
        if (!file_exists($fullPath)) {
            return redirect()->back()->withErrors('File tidak ditemukan.');
        }

        return response()->download($fullPath, $file->original_filename);
    }
}
