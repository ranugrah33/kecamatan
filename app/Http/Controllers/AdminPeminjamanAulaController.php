<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeminjamanAula;
use App\Services\PengajuanStatusNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminPeminjamanAulaController extends Controller
{
    public function index(Request $request)
    {
        PeminjamanAula::updateExpiredToSelesai();

        $status = $request->query('status');
        $query = PeminjamanAula::with('user');
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $peminjamans = $query->orderBy('created_at', 'desc')->get();
        return view('admin.peminjaman_aula.index', compact('peminjamans'));
    }

    public function show($id)
    {
        PeminjamanAula::updateExpiredToSelesai();

        $peminjaman = PeminjamanAula::with(['user.masyarakat', 'dokumens'])->findOrFail($id);
        return view('admin.peminjaman_aula.show', compact('peminjaman'));
    }

    public function updateStatus(Request $request, $id, PengajuanStatusNotificationService $notificationService)
    {
        $reasonRequiredMessage = 'Alasan atau catatan admin wajib diisi ketika status diubah menjadi Perlu Perbaikan atau Ditolak.';

        $request->validate([
            'status' => 'required|in:Menunggu Verifikasi,Perlu Perbaikan,Surat Diproses,Surat Tersedia,Selesai,Ditolak',
            'catatan_petugas' => PengajuanStatusNotificationService::reasonRules(
                $request->input('status'),
                [PeminjamanAula::STATUS_PERLU_PERBAIKAN, PeminjamanAula::STATUS_DITOLAK],
                $reasonRequiredMessage
            ),
        ], [
            'catatan_petugas.required' => $reasonRequiredMessage,
        ]);

        $peminjaman = null;
        $oldStatus = null;

        DB::transaction(function () use ($request, $id, &$peminjaman, &$oldStatus): void {
            $peminjaman = PeminjamanAula::where('id', $id)->lockForUpdate()->firstOrFail();
            $oldStatus = $peminjaman->status;

            $peminjaman->update([
                'status' => $request->status,
                'catatan_petugas' => $request->catatan_petugas,
            ]);
        });

        $peminjaman->refresh();

        // Jika status diubah menjadi Surat Tersedia dan file final ada serta belum dinotifikasikan, kirimkan email
        if ($peminjaman->status === PeminjamanAula::STATUS_SURAT_TERSEDIA) {
            $peminjaman->sendSuratFinalNotificationIfNeeded();
        }

        // Kirim notifikasi email otomatis hanya jika status berubah menjadi Perlu Perbaikan atau Ditolak
        $notifResult = $notificationService->notifyPeminjamanAulaIfNeeded($peminjaman, $oldStatus);

        return redirect()
            ->route('admin.peminjaman_aula.show', $id)
            ->with('success', PengajuanStatusNotificationService::buildStatusSuccessMessage($notifResult));
    }

    public function cetakSurat($id)
    {
        $peminjaman = PeminjamanAula::with(['user.masyarakat'])->findOrFail($id);
        
        if (!in_array($peminjaman->status, [PeminjamanAula::STATUS_SURAT_DIPROSES, PeminjamanAula::STATUS_SURAT_TERSEDIA, PeminjamanAula::STATUS_DISETUJUI])) {
            return redirect()->back()->withErrors('Surat draft hanya dapat dicetak jika status pengajuan sedang diproses (Surat Diproses).');
        }

        if ($peminjaman->status === PeminjamanAula::STATUS_DISETUJUI) {
            $peminjaman->update([
                'status' => PeminjamanAula::STATUS_SURAT_DIPROSES
            ]);
        }

        $pdf = Pdf::loadView('admin.peminjaman_aula.surat', compact('peminjaman'));
        return $pdf->stream('draft_surat_izin_' . $peminjaman->nomor_pengajuan . '.pdf');
    }

    public function uploadSuratFinal(Request $request, $id)
    {
        $request->validate([
            'file_surat_final' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'nomor_surat_izin' => 'nullable|string|max:255'
        ]);

        $peminjaman = PeminjamanAula::findOrFail($id);
        
        if ($request->hasFile('file_surat_final')) {
            $file = $request->file('file_surat_final');
            $filename = time() . '_surat_final_' . $peminjaman->nomor_pengajuan . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('surat_izin', $filename, 'public');
            
            $data = [
                'file_surat_final' => $path,
                'status' => PeminjamanAula::STATUS_SURAT_TERSEDIA
            ];

            if ($request->filled('nomor_surat_izin')) {
                $data['nomor_surat_izin'] = $request->nomor_surat_izin;
            }
            
            $peminjaman->update($data);
            $peminjaman->refresh();

            // Kirim notifikasi email otomatis hanya jika file surat final berhasil disimpan dan belum pernah dinotifikasikan
            $emailSent = $peminjaman->sendSuratFinalNotificationIfNeeded();
            
            $message = $emailSent
                ? 'Surat final berhasil diunggah. Notifikasi email telah otomatis dikirimkan ke pemohon.'
                : 'Surat final berhasil diunggah. Masyarakat kini dapat mendownloadnya.';

            return redirect()->back()->with('success', $message);
        }
        
        return redirect()->back()->withErrors('Gagal mengunggah file.');
    }

    public function downloadSurat($id)
    {
        $peminjaman = PeminjamanAula::findOrFail($id);

        if (!$peminjaman->file_surat_final || !Storage::disk('public')->exists($peminjaman->file_surat_final)) {
            return redirect()->back()->withErrors('File surat izin belum tersedia atau tidak ditemukan di penyimpanan server.');
        }

        $extension = pathinfo($peminjaman->file_surat_final, PATHINFO_EXTENSION) ?: 'pdf';
        $safeName = 'Surat_Izin_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $peminjaman->nomor_pengajuan) . '.' . $extension;

        return Storage::disk('public')->download($peminjaman->file_surat_final, $safeName);
    }
}
