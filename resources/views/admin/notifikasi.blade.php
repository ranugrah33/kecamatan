@extends('layouts.admin')

@section('title', 'Notifikasi Pengajuan')

@section('content')
<!-- Header Card -->
<div class="page-header-card flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-xs">
            <i class="ph ph-bell text-2xl font-bold"></i>
        </div>
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight">Notifikasi Pengajuan</h1>
            <p class="text-xs sm:text-sm text-slate-600 font-medium mt-0.5">Pemberitahuan aktivitas permohonan masuk dan pembaruan status dari masyarakat.</p>
        </div>
    </div>
    @if($totalMenunggu > 0)
    <div class="inline-flex items-center gap-2 px-3.5 py-2 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs font-semibold self-start sm:self-auto shadow-xs">
        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
        <span>{{ $totalMenunggu }} Permohonan Perlu Ditinjau</span>
    </div>
    @else
    <div class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold self-start sm:self-auto shadow-xs">
        <i class="ph ph-check-circle text-emerald-600 text-sm font-bold"></i>
        <span>Semua Permohonan Terkendali</span>
    </div>
    @endif
</div>

<!-- Filter Tabs -->
<div class="flex items-center gap-2 overflow-x-auto pb-2 mb-6 scrollbar-none">
    <a href="{{ route('admin.notifikasi', ['filter' => 'semua']) }}"
       class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition whitespace-nowrap {{ $filter === 'semua' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
        Semua Notifikasi
    </a>
    <a href="{{ route('admin.notifikasi', ['filter' => 'perlu_tindakan']) }}"
       class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition whitespace-nowrap flex items-center gap-1.5 {{ $filter === 'perlu_tindakan' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
        <span>Perlu Tindakan</span>
        @if($totalMenunggu > 0)
        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filter === 'perlu_tindakan' ? 'bg-white text-amber-700' : 'bg-amber-100 text-amber-800' }}">
            {{ $totalMenunggu }}
        </span>
        @endif
    </a>
    <a href="{{ route('admin.notifikasi', ['filter' => 'aula']) }}"
       class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition whitespace-nowrap {{ $filter === 'aula' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
        Peminjaman Aula
    </a>
    <a href="{{ route('admin.notifikasi', ['filter' => 'inventaris']) }}"
       class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition whitespace-nowrap {{ $filter === 'inventaris' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
        Peminjaman Inventaris
    </a>
    <a href="{{ route('admin.notifikasi', ['filter' => 'sertifikat']) }}"
       class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition whitespace-nowrap {{ $filter === 'sertifikat' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
        Sertifikat / Piagam
    </a>
</div>

<!-- Notification List -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="divide-y divide-gray-100">
        @forelse($notifikasi as $n)
        <div class="p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-slate-50/80 transition-colors {{ $n['perlu_tindakan'] ? 'bg-amber-50/30' : '' }}">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center flex-shrink-0 mt-0.5
                    @if(in_array($n['status'], ['Disetujui', 'Selesai', 'Surat Tersedia', 'Dikembalikan']))
                        bg-emerald-100/70 text-emerald-600
                    @elseif(in_array($n['status'], ['Ditolak', 'Dibatalkan']))
                        bg-red-100/70 text-red-600
                    @elseif(in_array($n['status'], ['Perlu Perbaikan', 'Sedang Dipinjam']))
                        bg-amber-100/70 text-amber-600
                    @else
                        bg-blue-100/70 text-blue-600
                    @endif">
                    @if(in_array($n['status'], ['Disetujui', 'Selesai', 'Surat Tersedia', 'Dikembalikan']))
                        <i class="ph ph-check-circle text-2xl font-bold"></i>
                    @elseif(in_array($n['status'], ['Ditolak', 'Dibatalkan']))
                        <i class="ph ph-x-circle text-2xl font-bold"></i>
                    @elseif(in_array($n['status'], ['Perlu Perbaikan', 'Sedang Dipinjam']))
                        <i class="ph ph-warning-circle text-2xl font-bold"></i>
                    @else
                        <i class="ph ph-clock text-2xl font-bold"></i>
                    @endif
                </div>

                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-lg border {{ $n['badge_class'] }}">
                            {{ $n['jenis'] }}
                        </span>
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-lg
                            @if(in_array($n['status'], ['Disetujui', 'Selesai', 'Surat Tersedia', 'Dikembalikan']))
                                bg-emerald-100 text-emerald-800
                            @elseif(in_array($n['status'], ['Ditolak', 'Dibatalkan']))
                                bg-red-100 text-red-800
                            @elseif(in_array($n['status'], ['Perlu Perbaikan']))
                                bg-amber-100 text-amber-800
                            @else
                                bg-blue-100 text-blue-800
                            @endif">
                            {{ $n['status'] }}
                        </span>
                        @if($n['perlu_tindakan'])
                        <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider bg-rose-50 text-rose-600 border border-rose-200 rounded-lg">
                            Perlu Tindakan
                        </span>
                        @endif
                    </div>

                    <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-snug">
                        {{ $n['judul'] }}
                    </h3>

                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                        {{ $n['pesan'] }}
                    </p>

                    <p class="text-xs text-gray-400 flex items-center gap-1.5 pt-0.5">
                        <i class="ph ph-clock"></i> {{ $n['waktu'] }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:self-center w-full sm:w-auto justify-end pt-2 sm:pt-0">
                <a href="{{ $n['route'] }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-95 transition shadow-xs">
                    <span>Tinjau</span>
                    <i class="ph ph-arrow-right text-xs font-bold"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="px-6 py-16 text-center">
            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-gray-100">
                <i class="ph ph-bell-slash text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">Belum ada notifikasi saat ini</h3>
            <p class="text-xs sm:text-sm text-gray-400 mt-1 max-w-sm mx-auto">
                Notifikasi akan otomatis masuk setiap kali masyarakat mengajukan permohonan baru atau memerlukan tindak lanjut.
            </p>
        </div>
        @endforelse
    </div>
</div>
@endsection
