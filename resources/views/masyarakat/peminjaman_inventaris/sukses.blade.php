@extends('layouts.masyarakat')
@section('title', 'Pengajuan Berhasil')

@section('content')
<div class="max-w-lg mx-auto mt-10">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ph ph-check-circle text-3xl text-green-600"></i>
        </div>
        <h1 class="text-xl font-bold text-gray-900 mb-2">Pengajuan Berhasil Dikirim!</h1>
        <p class="text-sm text-gray-500 mb-4">Pengajuan peminjaman inventaris Anda telah berhasil dikirim dan sedang menunggu proses verifikasi.</p>
        
        <div class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
            <div class="text-sm space-y-2">
                <div class="flex justify-between"><span class="text-gray-500">Kode Pengajuan</span><span class="font-bold text-gray-900">{{ $borrowing->request_code }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Tanggal & Jam Pinjam</span><span class="text-gray-700 font-medium">{{ $borrowing->borrow_date->translatedFormat('d F Y') }}{{ $borrowing->borrowing_time ? ' (' . \Carbon\Carbon::parse($borrowing->borrowing_time)->format('H:i') . ' WIB)' : '' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Tanggal & Jam Kembali</span><span class="text-gray-700 font-medium">{{ $borrowing->return_date->translatedFormat('d F Y') }}{{ $borrowing->return_time ? ' (' . \Carbon\Carbon::parse($borrowing->return_time)->format('H:i') . ' WIB)' : '' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Status</span><span class="px-2.5 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">{{ $borrowing->status_label }}</span></div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('masyarakat.peminjaman_inventaris.show', $borrowing->id) }}" class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition text-center">Lihat Detail</a>
            <a href="{{ route('masyarakat.dashboard') }}" class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition text-center">Kembali ke Beranda</a>
        </div>
    </div>
</div>
@endsection
