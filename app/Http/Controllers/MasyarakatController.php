<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasyarakatController extends Controller
{
    public function dashboard()
    {
        // Fetch real PeminjamanAula for the user
        $peminjamanAulas = \App\Models\PeminjamanAula::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        
        $total_peminjaman = $peminjamanAulas->count();
        $diproses_peminjaman = $peminjamanAulas->whereIn('status', ['Menunggu Verifikasi', 'Diproses'])->count();
        $selesai_peminjaman = $peminjamanAulas->where('status', 'Disetujui')->count();
        $perlu_diperbaiki_peminjaman = $peminjamanAulas->where('status', 'Perlu Perbaikan')->count();

        $pengajuan_terbaru = [];
        foreach($peminjamanAulas->take(3) as $p) {
            $pengajuan_terbaru[] = [
                'jenis' => 'Peminjaman Aula',
                'tanggal' => $p->tanggal,
                'status' => $p->status
            ];
        }

        // Dummy data for others
        $pengajuan_terbaru[] = ['jenis' => 'Jual Beli Tanah', 'tanggal' => '2023-10-20', 'status' => 'Selesai'];

        $data = [
            'total_pengajuan' => 2 + $total_peminjaman, // 2 dummy
            'diproses' => 0 + $diproses_peminjaman,
            'selesai' => 1 + $selesai_peminjaman,
            'perlu_diperbaiki' => 1 + $perlu_diperbaiki_peminjaman,
            'pengajuan_terbaru' => $pengajuan_terbaru,
            'notifikasi' => [
                ['pesan' => 'Pengajuan Peminjaman Aula berhasil dibuat.', 'waktu' => 'Baru saja'],
                ['pesan' => 'Dokumen pengajuan UMKM perlu perbaikan.', 'waktu' => '1 hari yang lalu'],
            ]
        ];

        return view('masyarakat.dashboard', $data);
    }

    public function profil()
    {
        $user = Auth::user();
        $profil = $user->masyarakat ?? new \App\Models\Masyarakat();
        return view('masyarakat.profil', compact('user', 'profil'));
    }

    public function updateProfil(Request $request)
    {
        $user = Auth::user();

        // Validasi data profil
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nik' => 'required|string|max:20|unique:users,nik,' . $user->id,
            'no_hp' => 'required|string|max:15',
            'no_kk' => 'nullable|string|max:20',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'alamat' => 'nullable|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'desa' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|string|max:100',
            'status_perkawinan' => 'nullable|string|max:50',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'nik' => $request->nik,
            'no_hp' => $request->no_hp,
        ]);

        $profilData = $request->only([
            'no_kk', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
            'alamat', 'rt', 'rw', 'desa', 'pekerjaan', 'status_perkawinan'
        ]);

        if ($user->masyarakat) {
            $user->masyarakat->update($profilData);
        } else {
            $user->masyarakat()->create($profilData);
        }

        return redirect()->route('masyarakat.profil')->with('success', 'Profil berhasil diperbarui!');
    }

    public function layanan()
    {
        return view('masyarakat.layanan');
    }

    public function riwayat()
    {
        $riwayat = [];
        
        // Fetch real PeminjamanAula data
        $peminjamanAulas = \App\Models\PeminjamanAula::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        foreach($peminjamanAulas as $p) {
            $riwayat[] = [
                'nomor' => $p->nomor_pengajuan,
                'jenis' => 'Peminjaman Aula',
                'tanggal' => \Carbon\Carbon::parse($p->created_at)->format('Y-m-d'),
                'status' => $p->status,
                'catatan' => $p->catatan_petugas ?? 'Menunggu verifikasi',
                'updated_at' => \Carbon\Carbon::parse($p->updated_at)->format('Y-m-d'),
                'id' => $p->id // for link to detail later
            ];
        }

        // Data dummy untuk riwayat (selain Aula)
        $riwayat[] = ['nomor' => 'REG-002', 'jenis' => 'Jual Beli Tanah', 'tanggal' => '2023-10-20', 'status' => 'Selesai', 'catatan' => 'Sertifikat siap diambil', 'updated_at' => '2023-10-24'];
        $riwayat[] = ['nomor' => 'REG-003', 'jenis' => 'UMKM', 'tanggal' => '2023-10-15', 'status' => 'Perlu Perbaikan', 'catatan' => 'KTP buram, harap upload ulang', 'updated_at' => '2023-10-16'];

        return view('masyarakat.riwayat', compact('riwayat'));
    }

    public function notifikasi()
    {
        $notifikasi = [];
        
        $peminjamanAulas = \App\Models\PeminjamanAula::where('user_id', Auth::id())->orderBy('updated_at', 'desc')->get();
        foreach($peminjamanAulas as $p) {
            $notifikasi[] = [
                'judul' => 'Status Peminjaman Aula: ' . $p->status,
                'pesan' => 'Pengajuan Peminjaman Aula untuk kegiatan "' . $p->nama_kegiatan . '" saat ini berstatus ' . $p->status . '.',
                'waktu' => \Carbon\Carbon::parse($p->updated_at)->diffForHumans(),
                'dibaca' => false
            ];
        }

        // Data dummy notifikasi
        $notifikasi[] = ['judul' => 'Perlu Perbaikan', 'pesan' => 'Dokumen pengajuan UMKM perlu perbaikan (KTP buram).', 'waktu' => '1 hari yang lalu', 'dibaca' => true];
        $notifikasi[] = ['judul' => 'Pengajuan Selesai', 'pesan' => 'Pengajuan Jual Beli Tanah telah selesai. Silakan ambil sertifikat di kantor.', 'waktu' => '3 hari yang lalu', 'dibaca' => true];
        
        return view('masyarakat.notifikasi', compact('notifikasi'));
    }
}
