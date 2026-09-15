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
        
        $riwayatDisplay = [];
        $notifDisplay = [];

        foreach($peminjamanAulas->take(3) as $p) {
            // Mapping for Riwayat
            $statusColor = 'blue';
            if(in_array($p->status, ['Disetujui', 'Surat Tersedia', 'Selesai'])) {
                $statusColor = 'emerald';
            } elseif(in_array($p->status, ['Ditolak', 'Dibatalkan'])) {
                $statusColor = 'rose';
            } elseif($p->status == 'Perlu Perbaikan') {
                $statusColor = 'amber';
            }

            $riwayatDisplay[] = [
                'icon' => 'ph-door-open', 
                'iconBg' => 'blue', 
                'jenis' => 'Peminjaman Aula', 
                'tgl' => \Carbon\Carbon::parse($p->created_at)->translatedFormat('d M Y, H:i'), 
                'status' => $p->status, 
                'statusColor' => $statusColor, 
                'route' => 'masyarakat.peminjaman_aula.show',
                'id' => $p->id
            ];

            // Mapping for Notifikasi
            $notifIcon = 'ph-info';
            $notifColor = 'blue';
            if(in_array($p->status, ['Disetujui', 'Surat Tersedia'])) {
                $notifIcon = 'ph-check-circle';
                $notifColor = 'emerald';
            } elseif(in_array($p->status, ['Ditolak', 'Dibatalkan'])) {
                $notifIcon = 'ph-x-circle';
                $notifColor = 'rose';
            } elseif($p->status == 'Perlu Perbaikan') {
                $notifIcon = 'ph-warning-circle';
                $notifColor = 'amber';
            }

            $notifDisplay[] = [
                'icon' => $notifIcon, 
                'color' => $notifColor, 
                'msg' => 'Status Peminjaman Aula: ' . $p->status, 
                'time' => 'Pada ' . \Carbon\Carbon::parse($p->updated_at)->translatedFormat('d M Y, H:i')
            ];
        }

        $data = [
            'riwayatDisplay' => $riwayatDisplay,
            'notifDisplay' => $notifDisplay,
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
                'id' => $p->id, // for link to detail later
                'file_surat_final' => $p->file_surat_final
            ];
        }

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
