<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasyarakatController extends Controller
{
    public function dashboard()
    {
        // Data dummy untuk memenuhi requirement tanpa membuat table pengajuan/pengaduan sungguhan dulu
        $data = [
            'total_pengajuan' => 5,
            'diproses' => 2,
            'selesai' => 2,
            'perlu_diperbaiki' => 1,
            'pengaduan_diproses' => 1,
            'pengajuan_terbaru' => [
                ['jenis' => 'Ahli Waris', 'tanggal' => '2023-10-25', 'status' => 'Diproses'],
                ['jenis' => 'Jual Beli Tanah', 'tanggal' => '2023-10-20', 'status' => 'Selesai'],
            ],
            'notifikasi' => [
                ['pesan' => 'Pengajuan Ahli Waris sedang diproses petugas.', 'waktu' => '2 jam yang lalu'],
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

        // Validasi data profil (users table & masyarakats table)
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

        // Update Users table
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'nik' => $request->nik,
            'no_hp' => $request->no_hp,
        ]);

        // Update or Create Masyarakats table
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
        // Data dummy untuk riwayat
        $riwayat = [
            ['nomor' => 'REG-001', 'jenis' => 'Ahli Waris', 'tanggal' => '2023-10-25', 'status' => 'Diproses', 'catatan' => 'Menunggu verifikasi lapangan', 'updated_at' => '2023-10-26'],
            ['nomor' => 'REG-002', 'jenis' => 'Jual Beli Tanah', 'tanggal' => '2023-10-20', 'status' => 'Selesai', 'catatan' => 'Sertifikat siap diambil', 'updated_at' => '2023-10-24'],
            ['nomor' => 'REG-003', 'jenis' => 'UMKM', 'tanggal' => '2023-10-15', 'status' => 'Perlu Perbaikan', 'catatan' => 'KTP buram, harap upload ulang', 'updated_at' => '2023-10-16'],
            ['nomor' => 'REG-004', 'jenis' => 'Bantuan Sosial', 'tanggal' => '2023-10-01', 'status' => 'Ditolak', 'catatan' => 'Tidak memenuhi syarat', 'updated_at' => '2023-10-05'],
        ];
        return view('masyarakat.riwayat', compact('riwayat'));
    }

    public function notifikasi()
    {
        // Data dummy notifikasi
        $notifikasi = [
            ['judul' => 'Pengajuan Diproses', 'pesan' => 'Pengajuan Ahli Waris Anda sedang diproses oleh petugas.', 'waktu' => '2 jam yang lalu', 'dibaca' => false],
            ['judul' => 'Perlu Perbaikan', 'pesan' => 'Dokumen pengajuan UMKM perlu perbaikan (KTP buram).', 'waktu' => '1 hari yang lalu', 'dibaca' => true],
            ['judul' => 'Pengajuan Selesai', 'pesan' => 'Pengajuan Jual Beli Tanah telah selesai. Silakan ambil sertifikat di kantor.', 'waktu' => '3 hari yang lalu', 'dibaca' => true],
            ['judul' => 'Pengaduan Diterima', 'pesan' => 'Pengaduan jalan rusak telah diterima dan diteruskan ke dinas terkait.', 'waktu' => '1 minggu yang lalu', 'dibaca' => true],
        ];
        return view('masyarakat.notifikasi', compact('notifikasi'));
    }
}
