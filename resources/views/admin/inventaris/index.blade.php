@extends('layouts.admin')
@section('title', 'Kelola Barang Inventaris')

@section('content')

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 text-sm text-green-700 flex items-center gap-2">
    <i class="ph ph-check-circle text-lg"></i> {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 text-sm text-red-700">
    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
</div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center"><i class="ph ph-cube text-xl text-blue-500"></i></div>
            <span class="text-xs font-medium text-gray-500">Total Jenis Barang</span>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ $totalItems }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center"><i class="ph ph-check-circle text-xl text-green-500"></i></div>
            <span class="text-xs font-medium text-gray-500">Barang Tersedia</span>
        </div>
        <p class="text-3xl font-bold text-green-600">{{ $totalAvailable }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center"><i class="ph ph-arrows-clockwise text-xl text-orange-500"></i></div>
            <span class="text-xs font-medium text-gray-500">Sedang Dipinjam</span>
        </div>
        <p class="text-3xl font-bold text-orange-500">{{ $totalBorrowed }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center"><i class="ph ph-warning-circle text-xl text-red-500"></i></div>
            <span class="text-xs font-medium text-gray-500">Barang Rusak</span>
        </div>
        <p class="text-3xl font-bold text-red-500">{{ $totalDamaged }}</p>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-bold text-gray-900">Daftar Barang Inventaris</h2>
        <a href="{{ route('admin.inventaris.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
            <i class="ph ph-plus-circle"></i> Tambah Barang
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Nama</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Kategori</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Total</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Tersedia</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Kondisi</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50/80 transition">
                    <td class="px-4 py-3 text-sm font-medium text-gray-800 flex items-center gap-2">
                        @if($item->image)
                            <img src="{{ asset($item->image) }}" class="w-8 h-8 rounded-lg object-cover">
                        @else
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center"><i class="ph ph-package text-gray-400"></i></div>
                        @endif
                        {{ $item->name }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $item->category ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs font-bold text-gray-700">{{ $item->total_quantity }}</td>
                    <td class="px-4 py-3 text-xs font-bold {{ $item->available_quantity > 0 ? 'text-green-600' : 'text-red-500' }}">{{ $item->available_quantity }}</td>
                    <td class="px-4 py-3">
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $item->condition == 'Baik' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $item->condition }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $item->is_active ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.inventaris.edit', $item->id) }}" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Edit"><i class="ph ph-pencil-simple"></i></a>
                            <form action="{{ route('admin.inventaris.toggle', $item->id) }}" method="POST" class="inline">
                                @csrf @method('PUT')
                                <button type="submit" class="p-1.5 {{ $item->is_active ? 'text-amber-500 hover:bg-amber-50' : 'text-green-500 hover:bg-green-50' }} rounded-lg transition" title="{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="ph {{ $item->is_active ? 'ph-eye-slash' : 'ph-eye' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.inventaris.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus barang ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition" title="Hapus"><i class="ph ph-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-xs text-gray-500">Belum ada barang inventaris.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
