@extends('layouts.masyarakat')
@section('title', 'Layanan Publik')

@section('content')
<div class="page-header-card flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-xs">
            <i class="ph ph-squares-four text-2xl font-bold"></i>
        </div>
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight">Menu Pelayanan</h1>
            <p class="text-xs sm:text-sm text-slate-600 font-medium mt-0.5">Pilih layanan publik masyarakat yang ingin Anda ajukan.</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- Peminjaman Aula --}}
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-100 hover:border-emerald-300 hover:shadow-md transition-all duration-300 overflow-hidden group">
        <div class="p-6">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-building text-2xl font-bold"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Peminjaman Aula</h3>
            <p class="text-sm text-slate-600 mb-4 leading-relaxed">Ajukan peminjaman aula Kecamatan Cikampek untuk kegiatan Anda.</p>
            <a href="{{ route('masyarakat.peminjaman_aula.index') }}" class="inline-flex items-center text-sm font-bold text-emerald-600 hover:text-emerald-700 transition-colors">
                Mulai Pengajuan <i class="ph ph-arrow-right ml-1 font-bold"></i>
            </a>
        </div>
    </div>

    {{-- Peminjaman Barang Inventaris --}}
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-100 hover:border-indigo-300 hover:shadow-md transition-all duration-300 overflow-hidden group">
        <div class="p-6">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-package text-2xl font-bold"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Peminjaman Inventaris</h3>
            <p class="text-sm text-slate-600 mb-4 leading-relaxed">Ajukan peminjaman barang inventaris seperti kursi, sound system, dan PC.</p>
            <a href="{{ route('masyarakat.peminjaman_inventaris.index') }}" class="inline-flex items-center text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                Mulai Pengajuan <i class="ph ph-arrow-right ml-1 font-bold"></i>
            </a>
        </div>
    </div>

    {{-- Sertifikat / Piagam --}}
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-100 hover:border-purple-300 hover:shadow-md transition-all duration-300 overflow-hidden group">
        <div class="p-6">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-certificate text-2xl font-bold"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Sertifikat / Piagam</h3>
            <p class="text-sm text-slate-600 mb-4 leading-relaxed">Ajukan pembuatan sertifikat atau piagam untuk kegiatan Anda.</p>
            <a href="{{ route('masyarakat.sertifikat.index') }}" class="inline-flex items-center text-sm font-bold text-purple-600 hover:text-purple-700 transition-colors">
                Mulai Pengajuan <i class="ph ph-arrow-right ml-1 font-bold"></i>
            </a>
        </div>
    </div>

</div>

<div class="mt-8 bg-amber-50/95 backdrop-blur-sm border border-amber-200/90 rounded-2xl p-5 flex items-start gap-4 shadow-sm">
    <div class="text-amber-600 mt-0.5 bg-amber-100/90 p-2.5 rounded-xl flex-shrink-0">
        <i class="ph ph-info text-2xl font-bold"></i>
    </div>
    <div>
        <h4 class="text-amber-900 font-bold text-base mb-1">Informasi Pengalihan Layanan Administrasi</h4>
        <p class="text-amber-800 text-sm leading-relaxed">
            Pembuatan <strong>KTP, Akta, Kartu Keluarga (KK), ataupun SKCK</strong> sudah tidak dilayani di Kantor Kecamatan Cikampek. Seluruh layanan tersebut telah dipindahkan/dialihkan ke gedung <strong>Mall Pelayanan Publik (MPP)</strong> Kabupaten Karawang.
        </p>
    </div>
</div>
@endsection
