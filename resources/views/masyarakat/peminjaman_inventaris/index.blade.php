@extends('layouts.masyarakat')
@section('title', 'Peminjaman Barang Inventaris')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Peminjaman Barang Inventaris</h1>
        <p class="text-sm text-gray-500 mt-1">Lihat ketersediaan barang dan ajukan peminjaman</p>
    </div>
    <a href="{{ route('masyarakat.peminjaman_inventaris.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow transition">
        <i class="ph ph-plus-circle text-lg"></i> Ajukan Peminjaman
    </a>
</div>

{{-- Daftar Barang Tersedia --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
            <i class="ph ph-cube text-indigo-500"></i> Barang Tersedia
        </h2>
    </div>
    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($items as $item)
        <div class="border border-gray-100 rounded-xl p-4 hover:border-indigo-200 hover:shadow-sm transition">
            <div class="flex items-start gap-3">
                @if($item->image)
                    <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" class="w-14 h-14 rounded-lg object-cover flex-shrink-0">
                @else
                    <div class="w-14 h-14 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="ph ph-package text-2xl text-indigo-400"></i>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-gray-900">{{ $item->name }}</h3>
                    @if($item->category)
                        <p class="text-xs text-gray-400">{{ $item->category }}</p>
                    @endif
                    <div class="mt-2 flex items-center gap-3">
                        <span class="text-xs font-medium {{ $item->available_quantity > 0 ? 'text-green-600' : 'text-red-500' }}">
                            Tersedia: {{ $item->available_quantity }} / {{ $item->total_quantity }}
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $item->condition == 'Baik' ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600' }}">
                            {{ $item->condition }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-8 text-gray-500">
            <i class="ph ph-package text-4xl text-gray-300 mb-2"></i>
            <p class="text-sm">Belum ada barang inventaris yang tersedia.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Riwayat Peminjaman --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
            <i class="ph ph-clock-counter-clockwise text-indigo-500"></i> Riwayat Peminjaman Anda
        </h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Pinjam</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Kembali</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($riwayat as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $r->request_code }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $r->borrow_date->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $r->return_date->format('d M Y') }}</td>
                    <td class="px-6 py-4">
                        @php
                            $bc = 'bg-gray-100 text-gray-800';
                            if (in_array($r->status, ['Disetujui','Dikembalikan','Selesai'])) $bc = 'bg-green-100 text-green-800';
                            elseif (in_array($r->status, ['Diproses'])) $bc = 'bg-blue-100 text-blue-800';
                            elseif ($r->status == 'Ditolak') $bc = 'bg-rose-100 text-rose-800';
                            elseif ($r->status == 'Sedang Dipinjam') $bc = 'bg-amber-100 text-amber-800';
                        @endphp
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $bc }}">{{ $r->status }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('masyarakat.peminjaman_inventaris.show', $r->id) }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                            <i class="ph ph-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">Belum ada riwayat peminjaman.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
