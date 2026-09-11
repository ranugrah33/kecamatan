@extends('layouts.admin')
@section('title', 'Dashboard Admin')

@section('content')

{{-- ===== STAT CARDS ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    {{-- Total Permohonan --}}
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="ph ph-files text-xl text-blue-500"></i>
            </div>
            <span class="text-xs font-medium text-gray-500 leading-tight">Total Permohonan</span>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ $total_permohonan }}</p>
        <p class="text-xs text-green-600 mt-1.5 flex items-center gap-0.5 font-medium">
            <i class="ph ph-arrow-up text-xs"></i> 2 dari kemarin
        </p>
    </div>

    {{-- Menunggu Review --}}
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="ph ph-clock text-xl text-orange-500"></i>
            </div>
            <span class="text-xs font-medium text-gray-500 leading-tight">Menunggu Review</span>
        </div>
        <p class="text-3xl font-bold text-orange-500">{{ $menunggu_review }}</p>
        <p class="text-xs text-green-600 mt-1.5 flex items-center gap-0.5 font-medium">
            <i class="ph ph-arrow-up text-xs"></i> 1 dari kemarin
        </p>
    </div>

    {{-- Selesai --}}
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="ph ph-check-circle text-xl text-green-500"></i>
            </div>
            <span class="text-xs font-medium text-gray-500 leading-tight">Selesai</span>
        </div>
        <p class="text-3xl font-bold text-green-600">{{ $selesai }}</p>
        <p class="text-xs text-green-600 mt-1.5 flex items-center gap-0.5 font-medium">
            <i class="ph ph-arrow-up text-xs"></i> 2 dari kemarin
        </p>
    </div>

    {{-- Perlu Diperbaiki --}}
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="ph ph-warning-circle text-xl text-red-500"></i>
            </div>
            <span class="text-xs font-medium text-gray-500 leading-tight">Perlu Diperbaiki</span>
        </div>
        <p class="text-3xl font-bold text-red-500">{{ $perlu_diperbaiki }}</p>
        <p class="text-xs text-red-500 mt-1.5 flex items-center gap-0.5 font-medium">
            <i class="ph ph-arrow-down text-xs"></i> 1 dari kemarin
        </p>
    </div>

</div>

{{-- ===== CHART + TABLE ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-5">

    {{-- Bar Chart --}}
    <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-sm font-bold text-gray-900">Statistik Layanan (7 Hari Terakhir)</h2>
                <div class="flex items-center gap-4 mt-1.5">
                    <span class="flex items-center gap-1.5 text-xs text-gray-500">
                        <span class="w-2.5 h-2.5 rounded-sm bg-blue-400 inline-block"></span>Diterima
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-gray-500">
                        <span class="w-2.5 h-2.5 rounded-sm bg-green-400 inline-block"></span>Selesai
                    </span>
                </div>
            </div>
            <select class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-200 bg-white">
                <option>Semua Layanan</option>
                <option>Peminjaman Aula</option>
                <option>Bantuan Sosial</option>
                <option>Jual Beli Tanah</option>
            </select>
        </div>
        <div style="height: 200px; position: relative;">
            <canvas id="layananChart"></canvas>
        </div>
    </div>

    {{-- Pengajuan Terbaru Table --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Pengajuan Terbaru</h2>
            <a href="{{ route('admin.peminjaman_aula.index') }}" class="text-xs text-blue-500 font-medium hover:text-blue-700 flex items-center gap-1 transition">
                Lihat Semua <i class="ph ph-arrow-right text-xs"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wide">No</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Jenis</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Pemohon</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Tgl</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pengajuan_terbaru as $index => $item)
                    @php
                        $color = 'blue';
                        if ($item->status == 'Selesai' || $item->status == 'Disetujui') $color = 'green';
                        if ($item->status == 'Perlu Perbaikan') $color = 'yellow';
                        if ($item->status == 'Ditolak' || $item->status == 'Dibatalkan') $color = 'red';
                    @endphp
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-xs font-medium text-gray-800">Peminjaman Aula</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $item->user->name }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full whitespace-nowrap bg-{{$color}}-100 text-{{$color}}-700">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.peminjaman_aula.show', $item->id) }}" class="text-gray-300 hover:text-blue-500 transition">
                                <i class="ph ph-caret-right text-sm"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-xs text-gray-500">Belum ada data pengajuan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ===== QUICK ACTIONS ===== --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

    <a href="{{ route('admin.peminjaman_aula.index') }}"
       class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-blue-200 hover:shadow-md transition-all group flex items-center gap-4">
        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
            <i class="ph ph-door-open text-xl text-blue-500"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-800">Kelola Peminjaman Aula</p>
            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">Atur jadwal, verifikasi dan kelola peminjaman aula</p>
        </div>
        <i class="ph ph-arrow-right text-gray-300 group-hover:text-blue-400 transition flex-shrink-0"></i>
    </a>

    <a href="#"
       class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-purple-200 hover:shadow-md transition-all group flex items-center gap-4">
        <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-purple-100 transition">
            <i class="ph ph-chart-bar text-xl text-purple-500"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-800">Laporan</p>
            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">Lihat laporan dan rekapitulasi data</p>
        </div>
        <i class="ph ph-arrow-right text-gray-300 group-hover:text-purple-400 transition flex-shrink-0"></i>
    </a>

    <a href="#"
       class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-gray-300 hover:shadow-md transition-all group flex items-center gap-4">
        <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-gray-200 transition">
            <i class="ph ph-gear text-xl text-gray-500"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-800">Pengaturan Sistem</p>
            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">Kelola informasi dan konfigurasi aplikasi</p>
        </div>
        <i class="ph ph-arrow-right text-gray-300 group-hover:text-gray-500 transition flex-shrink-0"></i>
    </a>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('layananChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['9 Sep', '10 Sep', '11 Sep', '12 Sep', '13 Sep', '14 Sep', '15 Sep'],
            datasets: [
                {
                    label: 'Diterima',
                    data: [3, 5, 8, 4, 6, 3, 5],
                    backgroundColor: 'rgba(96, 165, 250, 0.85)',
                    borderRadius: 5,
                    borderSkipped: false,
                },
                {
                    label: 'Selesai',
                    data: [2, 3, 6, 3, 4, 2, 4],
                    backgroundColor: 'rgba(74, 222, 128, 0.85)',
                    borderRadius: 5,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a2744',
                    cornerRadius: 8,
                    padding: 10,
                    titleFont: { size: 11, family: 'Inter' },
                    bodyFont: { size: 11, family: 'Inter' },
                    callbacks: {
                        label: function(ctx) {
                            return ` ${ctx.dataset.label}: ${ctx.parsed.y} pengajuan`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10, family: 'Inter' }, color: '#9ca3af' },
                    border: { display: false }
                },
                y: {
                    grid: { color: '#f3f4f6' },
                    ticks: { font: { size: 10, family: 'Inter' }, color: '#9ca3af', stepSize: 2 },
                    border: { display: false },
                    beginAtZero: true,
                    max: 10
                }
            },
            barPercentage: 0.6,
            categoryPercentage: 0.75
        }
    });
});
</script>
@endpush
