<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeminjamanAula;

class AdminController extends Controller
{
    public function dashboard()
    {
        $total_permohonan = PeminjamanAula::count();
        $menunggu_review = PeminjamanAula::whereIn('status', ['Menunggu Verifikasi', 'Diproses', 'Perlu Perbaikan'])->count();
        $selesai = PeminjamanAula::whereIn('status', ['Disetujui', 'Ditolak'])->count();
        $perlu_diperbaiki = PeminjamanAula::where('status', 'Perlu Perbaikan')->count();

        $pengajuan_terbaru = PeminjamanAula::with('user')->orderBy('created_at', 'desc')->take(5)->get();

        return view('admin.dashboard', compact('total_permohonan', 'menunggu_review', 'selesai', 'perlu_diperbaiki', 'pengajuan_terbaru'));
    }
}

