@extends('layouts.masyarakat')
@section('title', 'Layanan Publik')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Menu Pelayanan</h1>
    <p class="text-sm text-gray-500 mt-1">Pilih layanan masyarakat yang ingin Anda ajukan.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- Peminjaman Aula --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
        <div class="p-6">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-building text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Peminjaman Aula</h3>
            <p class="text-sm text-gray-500 mb-4">Ajukan peminjaman aula Kecamatan Cikampek untuk kegiatan Anda.</p>
            <a href="{{ route('masyarakat.peminjaman_aula.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                Mulai Pengajuan <i class="ph ph-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    {{-- Peminjaman Barang Inventaris --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
        <div class="p-6">
            <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-package text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Peminjaman Inventaris</h3>
            <p class="text-sm text-gray-500 mb-4">Ajukan peminjaman barang inventaris seperti kursi, sound system, dan PC.</p>
            <a href="{{ route('masyarakat.peminjaman_inventaris.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                Mulai Pengajuan <i class="ph ph-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    {{-- Sertifikat / Piagam --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
        <div class="p-6">
            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-certificate text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Sertifikat / Piagam</h3>
            <p class="text-sm text-gray-500 mb-4">Ajukan pembuatan sertifikat atau piagam untuk kegiatan Anda.</p>
            <a href="{{ route('masyarakat.sertifikat.index') }}" class="inline-flex items-center text-sm font-medium text-purple-600 hover:text-purple-800">
                Mulai Pengajuan <i class="ph ph-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

</div>
@endsection
