@extends('layouts.admin')
@section('title', 'Pengajuan Peminjaman Inventaris')

@section('content')
<div class="page-header-card flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 bg-indigo-500/10 text-indigo-600 border border-indigo-500/20 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-xs">
            <i class="ph ph-package text-2xl font-bold"></i>
        </div>
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight">Pengajuan Peminjaman Inventaris</h1>
            <p class="text-xs sm:text-sm text-slate-600 font-medium mt-0.5">Kelola data peminjaman barang dan inventaris kantor.</p>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.peminjaman_inventaris.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">Semua</a>
    @foreach(\App\Models\InventoryBorrowing::STATUSES as $s)
    <a href="{{ route('admin.peminjaman_inventaris.index', ['status' => $s]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') == $s ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">{{ $s }}</a>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Kode</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Pemohon</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Barang</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Waktu Pinjam</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Waktu Kembali</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($borrowings as $b)
                @php
                    $displayStatus = $b->status_label;
                    $badgeClass = match($displayStatus) {
                        'Menunggu Persetujuan' => 'bg-blue-100 text-blue-700',
                        'Disetujui' => 'bg-emerald-100 text-emerald-700',
                        'Sedang Dipinjam' => 'bg-amber-100 text-amber-700',
                        'Ditolak' => 'bg-rose-100 text-rose-700',
                        'Selesai' => 'bg-green-100 text-green-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <tr class="hover:bg-gray-50/80 transition">
                    <td class="px-4 py-3 text-xs font-bold text-gray-900">{{ $b->request_code }}</td>
                    <td class="px-4 py-3 text-xs text-gray-700">{{ $b->user->name ?? $b->applicant_name }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $b->details->map(fn($d) => ($d->item->name ?? 'Barang') . ' ('. $d->quantity .')')->implode(', ') }}</td>
                    <td class="px-4 py-3 text-xs text-gray-700">
                        <div>{{ $b->borrow_date->translatedFormat('d M Y') }}</div>
                        @if($b->borrowing_time)
                            <div class="text-[10px] text-gray-500 font-medium">{{ \Carbon\Carbon::parse($b->borrowing_time)->format('H:i') }} WIB</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-700">
                        <div>{{ $b->return_date->translatedFormat('d M Y') }}</div>
                        @if($b->return_time)
                            <div class="text-[10px] text-gray-500 font-medium">{{ \Carbon\Carbon::parse($b->return_time)->format('H:i') }} WIB</div>
                        @endif
                    </td>
                    <td class="px-4 py-3"><span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full {{ $badgeClass }}">{{ $displayStatus }}</span></td>
                    <td class="px-4 py-3"><a href="{{ route('admin.peminjaman_inventaris.show', $b->id) }}" class="text-blue-500 hover:text-blue-700 text-xs font-medium">Detail →</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-xs text-gray-500">Belum ada pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
