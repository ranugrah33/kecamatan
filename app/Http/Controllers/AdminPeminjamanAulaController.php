<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PeminjamanAula;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminPeminjamanAulaController extends Controller
{
    public function index(Request $request)
    {
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
        $peminjaman = PeminjamanAula::with(['user.masyarakat', 'dokumens'])->findOrFail($id);
        return view('admin.peminjaman_aula.show', compact('peminjaman'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Menunggu Verifikasi,Surat Diproses,Disetujui,Surat Tersedia,Ditolak,Perlu Perbaikan',
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

        // Jika belum punya nomor_surat_izin, buat nomor urut
        if (empty($peminjaman->nomor_surat_izin)) {
            $year = date('Y');
            $count = PeminjamanAula::whereNotNull('nomor_surat_izin')->whereYear('created_at', $year)->count() + 1;
            $nomor = str_pad($count, 3, '0', STR_PAD_LEFT);
            $nomor_surat = "400.2/{$nomor}/Kec-Ckp/{$year}";
            
            $peminjaman->update([
                'nomor_surat_izin' => $nomor_surat,
                'status' => $peminjaman->status == 'Disetujui' ? 'Surat Diproses' : $peminjaman->status
            ]);
        }

        $pdf = Pdf::loadView('admin.peminjaman_aula.surat', compact('peminjaman'));
        return $pdf->download('draft_surat_izin_' . $peminjaman->nomor_pengajuan . '.pdf');
    }

    public function uploadSuratFinal(Request $request, $id)
    {
        $request->validate([
            'file_surat_final' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $peminjaman = PeminjamanAula::findOrFail($id);
        
        if ($request->hasFile('file_surat_final')) {
            $file = $request->file('file_surat_final');
            $filename = time() . '_surat_final_' . $peminjaman->nomor_pengajuan . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('surat_izin', $filename, 'public');
            
            $peminjaman->update([
                'file_surat_final' => $path,
                'status' => 'Surat Tersedia'
            ]);
            
            return redirect()->back()->with('success', 'Surat final berhasil diunggah. Masyarakat kini dapat mendownloadnya.');
        }
        
        return redirect()->back()->withErrors('Gagal mengunggah file.');
    }
}
