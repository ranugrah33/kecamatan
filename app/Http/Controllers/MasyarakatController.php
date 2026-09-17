<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasyarakatController extends Controller
{
    public function dashboard()
    {
        $riwayatDisplay = [];
        $notifDisplay = [];

        // --- Peminjaman Aula ---
        $peminjamanAulas = \App\Models\PeminjamanAula::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        foreach($peminjamanAulas->take(2) as $p) {
            $statusColor = 'blue';
            if(in_array($p->status, ['Disetujui', 'Surat Tersedia', 'Selesai'])) {
                $statusColor = 'emerald';
            } elseif(in_array($p->status, ['Ditolak', 'Dibatalkan'])) {
                $statusColor = 'rose';
            } elseif($p->status == 'Perlu Perbaikan') {
                $statusColor = 'amber';
            }

            $riwayatDisplay[] = [
                'icon' => 'ph-building',
                'iconBg' => 'blue',
                'jenis' => 'Peminjaman Aula',
                'tgl' => \Carbon\Carbon::parse($p->created_at)->translatedFormat('d M Y, H:i'),
                'status' => $p->status,
                'statusColor' => $statusColor,
                'route' => 'masyarakat.peminjaman_aula.show',
                'id' => $p->id
            ];

            $notifIcon = 'ph-info';
            $notifColor = 'blue';
            if(in_array($p->status, ['Disetujui', 'Surat Tersedia'])) {
                $notifIcon = 'ph-check-circle'; $notifColor = 'emerald';
            } elseif(in_array($p->status, ['Ditolak', 'Dibatalkan'])) {
                $notifIcon = 'ph-x-circle'; $notifColor = 'rose';
            } elseif($p->status == 'Perlu Perbaikan') {
                $notifIcon = 'ph-warning-circle'; $notifColor = 'amber';
            }

            $notifDisplay[] = [
                'icon' => $notifIcon,
                'color' => $notifColor,
                'msg' => 'Peminjaman Aula: ' . $p->status,
                'time' => \Carbon\Carbon::parse($p->updated_at)->diffForHumans()
            ];
        }

        // --- Peminjaman Inventaris ---
        $inventoryBorrowings = \App\Models\InventoryBorrowing::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        foreach($inventoryBorrowings->take(2) as $b) {
            $statusColor = 'blue';
            if(in_array($b->status, ['Disetujui', 'Dikembalikan', 'Selesai'])) {
                $statusColor = 'emerald';
            } elseif(in_array($b->status, ['Ditolak'])) {
                $statusColor = 'rose';
            } elseif($b->status == 'Sedang Dipinjam') {
                $statusColor = 'amber';
            }

            $riwayatDisplay[] = [
                'icon' => 'ph-package',
                'iconBg' => 'indigo',
                'jenis' => 'Peminjaman Inventaris',
                'tgl' => \Carbon\Carbon::parse($b->created_at)->translatedFormat('d M Y, H:i'),
                'status' => $b->status,
                'statusColor' => $statusColor,
                'route' => 'masyarakat.peminjaman_inventaris.show',
                'id' => $b->id
            ];

            $notifIcon = 'ph-info';
            $notifColor = 'blue';
            if(in_array($b->status, ['Disetujui', 'Dikembalikan', 'Selesai'])) {
                $notifIcon = 'ph-check-circle'; $notifColor = 'emerald';
            } elseif($b->status == 'Ditolak') {
                $notifIcon = 'ph-x-circle'; $notifColor = 'rose';
            } elseif($b->status == 'Sedang Dipinjam') {
                $notifIcon = 'ph-warning-circle'; $notifColor = 'amber';
            }

            $notifDisplay[] = [
                'icon' => $notifIcon,
                'color' => $notifColor,
                'msg' => 'Peminjaman Inventaris: ' . $b->status,
                'time' => \Carbon\Carbon::parse($b->updated_at)->diffForHumans()
            ];
        }

        // --- Sertifikat / Piagam ---
        $certRequests = \App\Models\CertificateRequest::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        foreach($certRequests->take(2) as $c) {
            $statusColor = 'blue';
            if($c->status == 'Selesai') {
                $statusColor = 'emerald';
            } elseif($c->status == 'Ditolak') {
                $statusColor = 'rose';
            } elseif($c->status == 'Menunggu Dokumen') {
                $statusColor = 'amber';
            }

            $riwayatDisplay[] = [
                'icon' => 'ph-certificate',
                'iconBg' => 'purple',
                'jenis' => 'Sertifikat / Piagam',
                'tgl' => \Carbon\Carbon::parse($c->created_at)->translatedFormat('d M Y, H:i'),
                'status' => $c->status,
                'statusColor' => $statusColor,
                'route' => 'masyarakat.sertifikat.show',
                'id' => $c->id
            ];

            $notifIcon = 'ph-info';
            $notifColor = 'blue';
            if($c->status == 'Selesai') {
                $notifIcon = 'ph-check-circle'; $notifColor = 'emerald';
            } elseif($c->status == 'Ditolak') {
                $notifIcon = 'ph-x-circle'; $notifColor = 'rose';
            }

            $notifDisplay[] = [
                'icon' => $notifIcon,
                'color' => $notifColor,
                'msg' => 'Sertifikat/Piagam: ' . $c->status,
                'time' => \Carbon\Carbon::parse($c->updated_at)->diffForHumans()
            ];
        }

        // Sort all by most recent
        usort($notifDisplay, function($a, $b) {
            return 0; // keep insertion order which is already desc
        });

        $data = [
            'riwayatDisplay' => array_slice($riwayatDisplay, 0, 5),
            'notifDisplay' => array_slice($notifDisplay, 0, 5),
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

    public function riwayat(Request $request)
    {
        $riwayat = [];
        $filter = $request->query('filter', 'semua');
        $statusFilter = $request->query('status');

        // Fetch PeminjamanAula
        if (in_array($filter, ['semua', 'aula'])) {
            $peminjamanAulas = \App\Models\PeminjamanAula::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
            foreach($peminjamanAulas as $p) {
                if ($statusFilter && $p->status !== $statusFilter) continue;
                $riwayat[] = [
                    'nomor' => $p->nomor_pengajuan,
                    'jenis' => 'Peminjaman Aula',
                    'jenis_key' => 'aula',
                    'tanggal' => \Carbon\Carbon::parse($p->created_at)->format('Y-m-d'),
                    'status' => $p->status,
                    'catatan' => $p->catatan_petugas ?? 'Menunggu verifikasi',
                    'updated_at' => \Carbon\Carbon::parse($p->updated_at)->format('Y-m-d'),
                    'id' => $p->id,
                    'route' => 'masyarakat.peminjaman_aula.show',
                    'file_surat_final' => $p->file_surat_final
                ];
            }
        }

        // Fetch InventoryBorrowing
        if (in_array($filter, ['semua', 'inventaris'])) {
            $borrowings = \App\Models\InventoryBorrowing::with('details.item')->where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
            foreach($borrowings as $b) {
                if ($statusFilter && $b->status !== $statusFilter) continue;
                $itemNames = $b->details->map(fn($d) => $d->item->name ?? '-')->implode(', ');
                $riwayat[] = [
                    'nomor' => $b->request_code,
                    'jenis' => 'Peminjaman Inventaris',
                    'jenis_key' => 'inventaris',
                    'tanggal' => \Carbon\Carbon::parse($b->created_at)->format('Y-m-d'),
                    'status' => $b->status,
                    'catatan' => $b->rejection_reason ?? $itemNames,
                    'updated_at' => \Carbon\Carbon::parse($b->updated_at)->format('Y-m-d'),
                    'id' => $b->id,
                    'route' => 'masyarakat.peminjaman_inventaris.show',
                    'file_surat_final' => null
                ];
            }
        }

        // Fetch CertificateRequest
        if (in_array($filter, ['semua', 'sertifikat'])) {
            $certs = \App\Models\CertificateRequest::with('latestFile')->where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
            foreach($certs as $c) {
                if ($statusFilter && $c->status !== $statusFilter) continue;
                $riwayat[] = [
                    'nomor' => $c->request_code,
                    'jenis' => 'Sertifikat / Piagam',
                    'jenis_key' => 'sertifikat',
                    'tanggal' => \Carbon\Carbon::parse($c->created_at)->format('Y-m-d'),
                    'status' => $c->status,
                    'catatan' => $c->rejection_reason ?? $c->document_type . ': ' . $c->activity_name,
                    'updated_at' => \Carbon\Carbon::parse($c->updated_at)->format('Y-m-d'),
                    'id' => $c->id,
                    'route' => 'masyarakat.sertifikat.show',
                    'file_surat_final' => $c->latestFile ? true : null
                ];
            }
        }

        // Sort by date descending
        usort($riwayat, function($a, $b) {
            return strcmp($b['tanggal'], $a['tanggal']);
        });

        return view('masyarakat.riwayat', compact('riwayat', 'filter', 'statusFilter'));
    }

    public function notifikasi()
    {
        $notifikasi = [];

        // Peminjaman Aula
        $peminjamanAulas = \App\Models\PeminjamanAula::where('user_id', Auth::id())->orderBy('updated_at', 'desc')->get();
        foreach($peminjamanAulas as $p) {
            $notifikasi[] = [
                'judul' => 'Peminjaman Aula: ' . $p->status,
                'pesan' => 'Pengajuan peminjaman aula untuk kegiatan "' . $p->nama_kegiatan . '" saat ini berstatus ' . $p->status . '.',
                'waktu' => \Carbon\Carbon::parse($p->updated_at)->diffForHumans(),
                'waktu_sort' => $p->updated_at,
                'dibaca' => in_array($p->status, ['Selesai', 'Dibatalkan'])
            ];
        }

        // Peminjaman Inventaris
        $borrowings = \App\Models\InventoryBorrowing::where('user_id', Auth::id())->orderBy('updated_at', 'desc')->get();
        foreach($borrowings as $b) {
            $notifikasi[] = [
                'judul' => 'Peminjaman Inventaris: ' . $b->status,
                'pesan' => 'Pengajuan peminjaman barang inventaris (' . $b->request_code . ') saat ini berstatus ' . $b->status . '.',
                'waktu' => \Carbon\Carbon::parse($b->updated_at)->diffForHumans(),
                'waktu_sort' => $b->updated_at,
                'dibaca' => in_array($b->status, ['Selesai', 'Dikembalikan'])
            ];
        }

        // Sertifikat / Piagam
        $certs = \App\Models\CertificateRequest::with('latestFile')->where('user_id', Auth::id())->orderBy('updated_at', 'desc')->get();
        foreach($certs as $c) {
            $pesan = 'Pengajuan ' . $c->document_type . ' untuk kegiatan "' . $c->activity_name . '" saat ini berstatus ' . $c->status . '.';
            if ($c->status == 'Selesai' && $c->latestFile) {
                $pesan .= ' Sertifikat sudah tersedia dan dapat diunduh.';
            }
            $notifikasi[] = [
                'judul' => $c->document_type . ': ' . $c->status,
                'pesan' => $pesan,
                'waktu' => \Carbon\Carbon::parse($c->updated_at)->diffForHumans(),
                'waktu_sort' => $c->updated_at,
                'dibaca' => $c->status == 'Selesai'
            ];
        }

        // Sort by most recent
        usort($notifikasi, function($a, $b) {
            return $b['waktu_sort'] <=> $a['waktu_sort'];
        });

        return view('masyarakat.notifikasi', compact('notifikasi'));
    }
}
