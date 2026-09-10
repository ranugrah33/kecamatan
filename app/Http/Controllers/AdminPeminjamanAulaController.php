<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PeminjamanAula;

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
            'status' => 'required|in:Disetujui,Ditolak,Perlu Perbaikan',
            'catatan_petugas' => 'nullable|string'
        ]);

        $peminjaman = PeminjamanAula::findOrFail($id);
        $peminjaman->update([
            'status' => $request->status,
            'catatan_petugas' => $request->catatan_petugas
        ]);

        return redirect()->route('admin.peminjaman_aula.show', $id)->with('success', 'Status pengajuan berhasil diperbarui!');
    }
}
