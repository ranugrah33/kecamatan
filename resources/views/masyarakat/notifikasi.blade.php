@extends('layouts.masyarakat')
@section('title', 'Notifikasi')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Notifikasi</h1>
        <p class="text-sm text-gray-500 mt-1">Pembaruan status pengajuan layanan Anda.</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="divide-y divide-gray-50">
        @forelse($notifikasi as $n)
        <div class="px-6 py-4 flex items-start gap-4 {{ $n['dibaca'] ? 'opacity-60' : '' }} hover:bg-gray-50 transition-colors">
            <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                @if(str_contains($n['judul'], 'Selesai') || str_contains($n['judul'], 'Disetujui') || str_contains($n['judul'], 'Dikembalikan') || str_contains($n['judul'], 'Surat Tersedia'))
                    <i class="ph ph-check-circle text-green-600 text-xl"></i>
                @elseif(str_contains($n['judul'], 'Ditolak') || str_contains($n['judul'], 'Dibatalkan'))
                    <i class="ph ph-x-circle text-red-600 text-xl"></i>
                @elseif(str_contains($n['judul'], 'Perlu Perbaikan') || str_contains($n['judul'], 'Sedang Dipinjam'))
                    <i class="ph ph-warning-circle text-amber-600 text-xl"></i>
                @else
                    <i class="ph ph-info text-blue-600 text-xl"></i>
                @endif
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-gray-800 mb-0.5">{{ $n['judul'] }}</p>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $n['pesan'] }}</p>
                <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1"><i class="ph ph-clock text-xs"></i> {{ $n['waktu'] }}</p>
            </div>
            @if($n['dibaca'])
            <span class="text-[10px] text-gray-400 font-medium px-2 py-0.5 bg-gray-100 rounded-full flex-shrink-0">Dibaca</span>
            @endif
        </div>
        @empty
        <div class="px-6 py-12 text-center">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ph ph-bell-slash text-3xl text-gray-300"></i>
            </div>
            <p class="text-sm text-gray-400 font-medium">Belum ada notifikasi saat ini.</p>
            <p class="text-xs text-gray-300 mt-1">Notifikasi akan muncul ketika ada pembaruan status pengajuan Anda.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
