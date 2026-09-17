@extends('layouts.admin')
@section('title', 'Pengajuan Peminjaman Inventaris')

@section('content')
<div class="mb-5 flex items-center justify-between">
    <h1 class="text-xl font-bold text-gray-900">Pengajuan Peminjaman Inventaris</h1>
</div>

{{-- Filter --}}
<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.peminjaman_inventaris.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">Semua</a>
    @foreach(['Diajukan','Diproses','Disetujui','Ditolak','Sedang Dipinjam','Dikembalikan','Selesai'] as $s)
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
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Tgl Pinjam</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Tgl Kembali</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($borrowings as $b)
                @php
                    $color = 'blue';
                    if (in_array($b->status, ['Disetujui','Dikembalikan','Selesai'])) $color = 'green';
                    if ($b->status == 'Ditolak') $color = 'red';
                    if ($b->status == 'Sedang Dipinjam') $color = 'yellow';
                @endphp
                <tr class="hover:bg-gray-50/80 transition">
                    <td class="px-4 py-3 text-xs font-bold text-gray-900">{{ $b->request_code }}</td>
                    <td class="px-4 py-3 text-xs text-gray-700">{{ $b->user->name }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $b->details->map(fn($d) => $d->item->name . ' ('. $d->quantity .')')->implode(', ') }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $b->borrow_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $b->return_date->format('d M Y') }}</td>
                    <td class="px-4 py-3"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-{{ $color }}-100 text-{{ $color }}-700">{{ $b->status }}</span></td>
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
