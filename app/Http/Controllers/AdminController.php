<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeminjamanAula;
use App\Models\InventoryBorrowing;
use App\Models\CertificateRequest;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Combined stats from all services
        $total_aula = PeminjamanAula::count();
        $total_inventaris = InventoryBorrowing::count();
        $total_sertifikat = CertificateRequest::count();
        $total_permohonan = $total_aula + $total_inventaris + $total_sertifikat;

        $menunggu_review = PeminjamanAula::whereIn('status', ['Menunggu Verifikasi', 'Diproses', 'Perlu Perbaikan', 'Surat Diproses'])->count()
            + InventoryBorrowing::whereIn('status', ['Diajukan', 'Diproses'])->count()
            + CertificateRequest::whereIn('status', ['Diajukan', 'Diproses', 'Menunggu Dokumen'])->count();

        $selesai = PeminjamanAula::whereIn('status', ['Disetujui', 'Ditolak', 'Surat Tersedia', 'Selesai'])->count()
            + InventoryBorrowing::whereIn('status', ['Selesai', 'Dikembalikan'])->count()
            + CertificateRequest::where('status', 'Selesai')->count();

        $perlu_diperbaiki = PeminjamanAula::where('status', 'Perlu Perbaikan')->count()
            + InventoryBorrowing::where('status', 'Ditolak')->count()
            + CertificateRequest::where('status', 'Ditolak')->count();

        // Combine recent submissions from all services
        $pengajuan_terbaru = collect();

        $aulaRecent = PeminjamanAula::with('user')->orderBy('created_at', 'desc')->take(5)->get()
            ->map(fn($item) => (object)[
                'id' => $item->id,
                'jenis' => 'Peminjaman Aula',
                'pemohon' => $item->user->name,
                'tanggal' => $item->created_at,
                'status' => $item->status,
                'route' => 'admin.peminjaman_aula.show',
            ]);

        $invRecent = InventoryBorrowing::with('user')->orderBy('created_at', 'desc')->take(5)->get()
            ->map(fn($item) => (object)[
                'id' => $item->id,
                'jenis' => 'Peminjaman Inventaris',
                'pemohon' => $item->user->name,
                'tanggal' => $item->created_at,
                'status' => $item->status,
                'route' => 'admin.peminjaman_inventaris.show',
            ]);

        $certRecent = CertificateRequest::with('user')->orderBy('created_at', 'desc')->take(5)->get()
            ->map(fn($item) => (object)[
                'id' => $item->id,
                'jenis' => $item->document_type,
                'pemohon' => $item->user->name,
                'tanggal' => $item->created_at,
                'status' => $item->status,
                'route' => 'admin.sertifikat.show',
            ]);

        $pengajuan_terbaru = $aulaRecent->concat($invRecent)->concat($certRecent)
            ->sortByDesc('tanggal')->take(5)->values();

        // Chart data for last 7 days
        $chartLabels = [];
        $chartDataDiterima = [];
        $chartDataSelesai = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subDays($i);
            $chartLabels[] = $date->translatedFormat('j M');

            $masuk = PeminjamanAula::whereDate('created_at', $date->toDateString())->count()
                + InventoryBorrowing::whereDate('created_at', $date->toDateString())->count()
                + CertificateRequest::whereDate('created_at', $date->toDateString())->count();

            $selesaiHariIni = PeminjamanAula::whereDate('updated_at', $date->toDateString())
                ->whereIn('status', ['Disetujui', 'Surat Tersedia', 'Selesai'])->count()
                + InventoryBorrowing::whereDate('updated_at', $date->toDateString())
                    ->whereIn('status', ['Selesai', 'Dikembalikan'])->count()
                + CertificateRequest::whereDate('updated_at', $date->toDateString())
                    ->where('status', 'Selesai')->count();

            $chartDataDiterima[] = $masuk;
            $chartDataSelesai[] = $selesaiHariIni;
        }

        return view('admin.dashboard', compact('total_permohonan', 'menunggu_review', 'selesai', 'perlu_diperbaiki', 'pengajuan_terbaru', 'chartLabels', 'chartDataDiterima', 'chartDataSelesai'));
    }

    public function laporan()
    {
        $total = PeminjamanAula::count();
        $disetujui = PeminjamanAula::whereIn('status', ['Disetujui', 'Surat Tersedia', 'Selesai'])->count();
        $ditolak = PeminjamanAula::where('status', 'Ditolak')->count();
        $menunggu = PeminjamanAula::whereIn('status', ['Menunggu Verifikasi', 'Diproses', 'Surat Diproses', 'Perlu Perbaikan'])->count();
        
        $peminjamans = PeminjamanAula::with('user')->orderBy('created_at', 'desc')->get();
        
        return view('admin.laporan', compact('total', 'disetujui', 'ditolak', 'menunggu', 'peminjamans'));
    }

    public function pengaturan()
    {
        $aula = \App\Models\Aula::first();
        return view('admin.pengaturan', compact('aula'));
    }

    public function updateProfil(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
        ]);
        
        $user = auth()->user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        
        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:8|confirmed',
        ]);
        
        $user = auth()->user();
        if (!\Illuminate\Support\Facades\Hash::check($request->password_lama, $user->password)) {
            return redirect()->back()->withErrors('Password lama tidak sesuai!');
        }
        
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password_baru);
        $user->save();
        
        return redirect()->back()->with('success', 'Password berhasil diubah!');
    }

    public function updateAula(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'jam_buka' => 'required|date_format:H:i',
            'jam_tutup' => 'required|date_format:H:i',
            'fasilitas' => 'required|string'
        ]);
        
        $aula = \App\Models\Aula::first();
        if ($aula) {
            $aula->nama = $request->nama;
            $aula->kapasitas = $request->kapasitas;
            $aula->jam_buka = $request->jam_buka;
            $aula->jam_tutup = $request->jam_tutup;
            // Split fasilitas by comma
            $fasilitas_array = array_map('trim', explode(',', $request->fasilitas));
            $aula->fasilitas = $fasilitas_array;
            $aula->save();
        }
        
        return redirect()->back()->with('success', 'Data fasilitas aula berhasil diperbarui!');
    }
}
