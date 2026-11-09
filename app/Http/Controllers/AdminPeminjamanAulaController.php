<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PeminjamanAula;
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Menunggu Verifikasi,Surat Diproses,Disetujui,Surat Tersedia,Selesai,Ditolak,Perlu Perbaikan',
            'catatan_petugas' => 'nullable|string'
        ]);

        $peminjaman = PeminjamanAula::findOrFail($id);
        
        $peminjaman->update([
            'status' => $request->status,
            'catatan_petugas' => $request->catatan_petugas
        ]);

        return redirect()->route('admin.peminjaman_aula.show', $id)->with('success', 'Status pengajuan berhasil diperbarui!');
    }

    public function cetakSurat($id)
    {
        $peminjaman = PeminjamanAula::with(['user.masyarakat'])->findOrFail($id);
        
        if (!in_array($peminjaman->status, ['Disetujui', 'Surat Diproses', 'Surat Tersedia'])) {
            return redirect()->back()->withErrors('Surat hanya dapat dicetak jika status Disetujui.');
        }

        if ($peminjaman->status === 'Disetujui') {
            $peminjaman->update([
                'status' => 'Surat Diproses'
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
                'status' => 'Surat Tersedia'
            ];

            if ($request->filled('nomor_surat_izin')) {
                $data['nomor_surat_izin'] = $request->nomor_surat_izin;
            }
            
            $peminjaman->update($data);
            
            return redirect()->back()->with('success', 'Surat final berhasil diunggah. Masyarakat kini dapat mendownloadnya.');
        }
        
        return redirect()->back()->withErrors('Gagal mengunggah file.');
    }
}
