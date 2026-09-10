@extends('layouts.masyarakat')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Menu Pelayanan</h1>
    <p class="text-sm text-gray-500 mt-1">Pilih layanan masyarakat yang ingin Anda ajukan.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

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
    <!-- Peminjaman Aula Kecamatan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
        <div class="p-6">
            <div class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="ph ph-house-line text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Peminjaman Aula</h3>
            <p class="text-sm text-gray-500 mb-4">Pengajuan izin penggunaan aula kecamatan untuk berbagai kegiatan masyarakat.</p>
            <a href="{{ route('masyarakat.peminjaman_aula.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                Ajukan Sekarang <i class="ph ph-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

</div>
@endsection
