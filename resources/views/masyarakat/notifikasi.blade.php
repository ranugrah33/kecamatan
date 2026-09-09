@extends('layouts.masyarakat')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Notifikasi</h1>
    <p class="text-sm text-gray-500 mt-1">Pemberitahuan terkait status pengajuan layanan Anda.</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-xl">
        <h2 class="text-sm font-medium text-gray-700">Semua Notifikasi</h2>
        <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">Tandai Semua Dibaca</button>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse($notifikasi as $n)
        <div class="p-6 flex gap-4 {{ !$n['dibaca'] ? 'bg-blue-50/50' : 'hover:bg-gray-50 transition' }}">
            <div class="shrink-0 mt-1">
                @if(!$n['dibaca'])
                    <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                        <i class="ph ph-bell-ringing text-xl"></i>
                    </div>
                @else
                    <div class="w-10 h-10 bg-gray-100 text-gray-500 rounded-full flex items-center justify-center">
                        <i class="ph ph-bell text-xl"></i>
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <div class="flex justify-between items-start mb-1">
                    <h3 class="text-sm font-semibold {{ !$n['dibaca'] ? 'text-gray-900' : 'text-gray-700' }}">{{ $n['judul'] }}</h3>
                    <span class="text-xs text-gray-500 whitespace-nowrap ml-4">{{ $n['waktu'] }}</span>
                </div>
                <p class="text-sm {{ !$n['dibaca'] ? 'text-gray-800' : 'text-gray-600' }}">{{ $n['pesan'] }}</p>
            </div>
            @if(!$n['dibaca'])
                <div class="shrink-0 flex items-center">
                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                </div>
            @endif
        </div>
        @empty
        <div class="p-8 text-center text-gray-500">
            <i class="ph ph-bell-slash text-4xl mb-3 text-gray-300"></i>
            <p>Belum ada notifikasi saat ini.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
