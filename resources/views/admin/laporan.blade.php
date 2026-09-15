@extends('layouts.admin')

@section('title', 'Laporan Peminjaman')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Laporan & Statistik</h1>
    <p class="text-sm text-gray-500 mt-1">Data real-time rekapitulasi pengajuan layanan publik dari masyarakat.</p>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-start gap-4">
        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="ph ph-files text-2xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Pengajuan</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $total }}</h3>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-start gap-4">
        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="ph ph-check-circle text-2xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Disetujui / Selesai</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $disetujui }}</h3>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-start gap-4">
        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="ph ph-clock text-2xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Dalam Proses</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $menunggu }}</h3>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-start gap-4">
        <div class="w-12 h-12 bg-red-50 text-red-600 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="ph ph-x-circle text-2xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Ditolak</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $ditolak }}</h3>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-8">
    <div class="p-5 border-b border-gray-200 bg-gray-50/50 flex justify-between items-center">
        <h2 class="text-lg font-bold text-gray-900">Semua Data Pengajuan</h2>
        <button onclick="window.print()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center gap-2 transition">
            <i class="ph ph-printer text-lg"></i> Cetak Laporan
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                    <th class="px-6 py-4 font-semibold">Nomor Surat</th>
                    <th class="px-6 py-4 font-semibold">Pemohon</th>
                    <th class="px-6 py-4 font-semibold">Kegiatan</th>
                    <th class="px-6 py-4 font-semibold">Tgl Kegiatan</th>
                    <th class="px-6 py-4 font-semibold">Status</th>
                    <th class="px-6 py-4 font-semibold">Tgl Pengajuan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($peminjamans as $item)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                        {{ $item->nomor_pengajuan }}
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900">{{ $item->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $item->user->nik }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-800">{{ $item->nama_kegiatan }}</p>
                        <p class="text-xs text-gray-500">{{ $item->jenis_kegiatan }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusColor = match($item->status) {
                                'Menunggu Verifikasi' => 'bg-yellow-100 text-yellow-800',
                                'Diproses', 'Surat Diproses' => 'bg-blue-100 text-blue-800',
                                'Perlu Perbaikan' => 'bg-orange-100 text-orange-800',
                                'Disetujui', 'Surat Tersedia', 'Selesai' => 'bg-green-100 text-green-800',
                                'Ditolak', 'Dibatalkan' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp
                        <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full {{ $statusColor }}">
                            {{ $item->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="ph ph-folder-open text-4xl text-gray-300 mb-2"></i>
                            <p class="text-sm">Belum ada data pengajuan peminjaman aula.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .bg-[#f5f7fb] { background: white !important; }
        .bg-white { box-shadow: none !important; border: none !important; }
        main, main * {
            visibility: visible;
        }
        main {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0 !important;
        }
        .mb-8 { margin-bottom: 20px !important; }
        button { display: none !important; }
        .grid { display: flex !important; gap: 10px !important; }
        .grid > div { flex: 1 !important; border: 1px solid #ddd !important; }
    }
</style>
@endsection
