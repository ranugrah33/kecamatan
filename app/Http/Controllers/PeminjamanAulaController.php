<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Aula;
use App\Models\PeminjamanAula;
use Illuminate\Support\Facades\Auth;

class PeminjamanAulaController extends Controller
{
    public function index()
    {
        $aula = Aula::first();
        $riwayat = PeminjamanAula::where('user_id', Auth::id())
                                 ->orderBy('created_at', 'desc')
                                 ->get();
                                 
        $jadwal_disetujui = PeminjamanAula::where('status', 'Disetujui')
                                          ->where('tanggal', '>=', date('Y-m-d'))
                                          ->orderBy('tanggal', 'asc')
                                          ->orderBy('jam_mulai', 'asc')
                                          ->get();
                                 
        return view('masyarakat.peminjaman_aula.index', compact('aula', 'riwayat', 'jadwal_disetujui'));
    }

    public function create()
    {
        $aula = Aula::first();
        if (!$aula) {
            return redirect()->route('masyarakat.peminjaman_aula.index')->with('error', 'Data Aula belum tersedia.');
        }

        $user = Auth::user();
        $profil = $user->masyarakat;
        
        return view('masyarakat.peminjaman_aula.create', compact('aula', 'user', 'profil'));
    }

    public function store(Request $request)
    {
        $aula = Aula::first();
        if (!$aula) {
            return redirect()->back()->withErrors('Data Aula belum tersedia.');
        }

        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jenis_kegiatan' => 'required|string|max:255',
            'deskripsi_kegiatan' => 'required|string',
            'jumlah_peserta' => 'required|integer|min:1|max:' . $aula->kapasitas,
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Cek jam operasional
        if ($request->jam_mulai < substr($aula->jam_buka, 0, 5) || $request->jam_selesai > substr($aula->jam_tutup, 0, 5)) {
            return redirect()->back()->withErrors('Waktu peminjaman harus berada di dalam jam operasional aula (' . substr($aula->jam_buka, 0, 5) . ' - ' . substr($aula->jam_tutup, 0, 5) . ').')->withInput();
        }

        // Cek jadwal bentrok
        $bentrok = PeminjamanAula::where('status', 'Disetujui')
            ->where('tanggal', $request->tanggal)
            ->where(function ($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                      ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('jam_mulai', '<=', $request->jam_mulai)
                            ->where('jam_selesai', '>=', $request->jam_selesai);
                      });
            })
            ->exists();
            
        if ($bentrok) {
            return redirect()->back()->withErrors('Aula sudah di-booking pada tanggal dan jam tersebut. Silakan pilih jadwal lain.')->withInput();
        }

        // Generate Nomor Pengajuan AULA-YYYYMMDD-XXXX
        $dateStr = date('Ymd');
        $lastPeminjaman = PeminjamanAula::where('nomor_pengajuan', 'like', 'AULA-' . $dateStr . '-%')->orderBy('id', 'desc')->first();
        if ($lastPeminjaman) {
            $lastNum = intval(substr($lastPeminjaman->nomor_pengajuan, -4));
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }
        $nomorPengajuan = 'AULA-' . $dateStr . '-' . $newNum;

        // Siapkan data fasilitas
        $fasilitas = $request->fasilitas ?? [];
        if ($request->has('fasilitas_lainnya_check') && $request->fasilitas_lainnya_text) {
            $fasilitas[] = $request->fasilitas_lainnya_text;
        }

        // Simpan Data
        $peminjaman = PeminjamanAula::create([
            'user_id' => Auth::id(),
            'aula_id' => $aula->id,
            'nomor_pengajuan' => $nomorPengajuan,
            'nama_kegiatan' => $request->nama_kegiatan,
            'jenis_kegiatan' => $request->jenis_kegiatan === 'Lainnya' ? $request->jenis_kegiatan_lainnya : $request->jenis_kegiatan,
            'deskripsi_kegiatan' => $request->deskripsi_kegiatan,
            'jumlah_peserta' => $request->jumlah_peserta,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'fasilitas_dibutuhkan' => $fasilitas,
            'catatan_tambahan' => $request->catatan_tambahan,
            'status' => 'Menunggu Verifikasi',
        ]);

        // Upload Dokumen
        if ($request->hasFile('dokumen')) {
            $file = $request->file('dokumen');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('dokumen_aula', $filename, 'public');
            
            \App\Models\PeminjamanAulaDokumen::create([
                'peminjaman_aula_id' => $peminjaman->id,
                'nama_dokumen' => 'Surat Permohonan / Proposal',
                'path_dokumen' => $path,
            ]);
        }

        return redirect()->route('masyarakat.peminjaman_aula.sukses', $peminjaman->id);
    }

    public function sukses($id)
    {
        $peminjaman = PeminjamanAula::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return view('masyarakat.peminjaman_aula.sukses', compact('peminjaman'));
    }

    public function show($id)
    {
        $peminjaman = PeminjamanAula::with(['aula', 'dokumens'])->where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return view('masyarakat.peminjaman_aula.show', compact('peminjaman'));
    }
}
