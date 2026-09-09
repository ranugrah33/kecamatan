@extends('layouts.masyarakat')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Menu Pelayanan</h1>
    <p class="text-sm text-gray-500 mt-1">Pilih layanan masyarakat yang ingin Anda ajukan.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Ahli Waris -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
        <div class="p-6">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-users-three text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Ahli Waris</h3>
            <p class="text-sm text-gray-500 mb-4">Pengurusan surat keterangan ahli waris untuk berbagai keperluan administratif.</p>
            <a href="#" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                Ajukan Sekarang <i class="ph ph-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Jual Beli Tanah -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
        <div class="p-6">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-map-trifold text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Jual Beli Tanah</h3>
            <p class="text-sm text-gray-500 mb-4">Pengurusan administrasi dan surat keterangan jual beli tanah di wilayah kecamatan.</p>
            <a href="#" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                Ajukan Sekarang <i class="ph ph-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Tanah & Bangunan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
        <div class="p-6">
            <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-buildings text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Tanah & Bangunan</h3>
            <p class="text-sm text-gray-500 mb-4">Pendaftaran, balik nama, atau pengurusan IMB dan administrasi tanah bangunan.</p>
            <a href="#" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                Ajukan Sekarang <i class="ph ph-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- UMKM -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
        <div class="p-6">
            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-storefront text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">UMKM</h3>
            <p class="text-sm text-gray-500 mb-4">Pendaftaran surat keterangan usaha (SKU) dan perizinan UMKM tingkat kecamatan.</p>
            <a href="#" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                Ajukan Sekarang <i class="ph ph-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Bantuan Sosial -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
        <div class="p-6">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-hand-heart text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Bantuan Sosial</h3>
            <p class="text-sm text-gray-500 mb-4">Pendaftaran atau pengajuan Surat Keterangan Tidak Mampu (SKTM) untuk bansos.</p>
            <a href="#" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                Ajukan Sekarang <i class="ph ph-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Pengaduan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
        <div class="p-6">
            <div class="w-12 h-12 bg-gray-100 text-gray-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-megaphone text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Pengaduan Fasilitas Umum</h3>
            <p class="text-sm text-gray-500 mb-4">Laporkan kerusakan jalan, lampu penerangan, atau fasilitas umum lainnya.</p>
            <a href="#" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                Buat Laporan <i class="ph ph-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>
@endsection
