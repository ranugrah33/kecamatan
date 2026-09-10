@extends('layouts.masyarakat')

@section('content')
<div class="max-w-2xl mx-auto mt-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden text-center p-8">
        <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="ph ph-check-circle text-4xl"></i>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Pengajuan Peminjaman Aula Berhasil</h1>
        <p class="text-gray-500 mb-8">Tunggu proses verifikasi dari petugas Kecamatan Cikampek. Anda dapat melihat status pengajuan pada menu Riwayat Peminjaman.</p>
        
        <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left border border-gray-100">
            <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
                <div class="col-span-2 pb-3 border-b border-gray-200 flex justify-between items-center">
                    <span class="text-gray-500">Nomor Pengajuan</span>
                    <span class="font-bold text-gray-900 text-lg">{{ $peminjaman->nomor_pengajuan }}</span>
                </div>
                
                <div>
                    <span class="text-gray-500 block mb-1">Nama Kegiatan</span>
                    <span class="font-medium text-gray-900">{{ $peminjaman->nama_kegiatan }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block mb-1">Status</span>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $peminjaman->status }}
                    </span>
                </div>
                
                <div>
                    <span class="text-gray-500 block mb-1">Tanggal Kegiatan</span>
                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($peminjaman->tanggal)->translatedFormat('d F Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block mb-1">Waktu Kegiatan</span>
                    <span class="font-medium text-gray-900">{{ substr($peminjaman->jam_mulai, 0, 5) }} - {{ substr($peminjaman->jam_selesai, 0, 5) }}</span>
                </div>
            </div>
        </div>
        
        <div class="flex justify-center gap-4">
            <a href="{{ route('masyarakat.peminjaman_aula.index') }}" class="px-6 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                Kembali
            </a>
            <a href="{{ route('masyarakat.peminjaman_aula.show', $peminjaman->id) }}" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition">
                Lihat Detail Pengajuan
            </a>
        </div>
    </div>
</div>
@endsection
