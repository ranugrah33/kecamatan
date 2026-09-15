<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeminjamanAula;

class AdminController extends Controller
{
    public function dashboard()
    {
        $total_permohonan = PeminjamanAula::count();
        $menunggu_review = PeminjamanAula::whereIn('status', ['Menunggu Verifikasi', 'Diproses', 'Perlu Perbaikan', 'Surat Diproses'])->count();
        $selesai = PeminjamanAula::whereIn('status', ['Disetujui', 'Ditolak', 'Surat Tersedia'])->count();
        $perlu_diperbaiki = PeminjamanAula::where('status', 'Perlu Perbaikan')->count();

        $pengajuan_terbaru = PeminjamanAula::with('user')->orderBy('created_at', 'desc')->take(5)->get();

        // Prepare chart data for last 7 days
        $chartLabels = [];
        $chartDataDiterima = [];
        $chartDataSelesai = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subDays($i);
            $chartLabels[] = $date->translatedFormat('j M');
            
            // Total pengajuan masuk pada tanggal tersebut
            $masuk = PeminjamanAula::whereDate('created_at', $date->toDateString())->count();
            
            // Total pengajuan yang diproses/selesai pada tanggal tersebut
            $selesaiHariIni = PeminjamanAula::whereDate('updated_at', $date->toDateString())
                                            ->whereIn('status', ['Disetujui', 'Surat Tersedia', 'Selesai'])
                                            ->count();

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
        return view('admin.pengaturan');
    }
}

