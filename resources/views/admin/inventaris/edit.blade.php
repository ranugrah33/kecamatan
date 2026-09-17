@extends('layouts.admin')
@section('title', 'Edit Barang Inventaris')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.inventaris.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2"><i class="ph ph-arrow-left"></i> Kembali</a>
    <h1 class="text-xl font-bold text-gray-900">Edit Barang: {{ $item->name }}</h1>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 text-sm text-red-700">
    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
</div>
@endif

<form action="{{ route('admin.inventaris.update', $item->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4 max-w-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $item->name) }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <input type="text" name="category" value="{{ old('category', $item->category) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Total <span class="text-red-500">*</span></label>
                <input type="number" name="total_quantity" value="{{ old('total_quantity', $item->total_quantity) }}" min="0" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none">
                <p class="text-xs text-gray-400 mt-1">Saat ini tersedia: {{ $item->available_quantity }} dari {{ $item->total_quantity }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi <span class="text-red-500">*</span></label>
                <select name="condition" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none">
                    <option value="Baik" {{ $item->condition == 'Baik' ? 'selected' : '' }}>Baik</option>
                    <option value="Cukup Baik" {{ $item->condition == 'Cukup Baik' ? 'selected' : '' }}>Cukup Baik</option>
                    <option value="Rusak Ringan" {{ $item->condition == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="Rusak" {{ $item->condition == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none">{{ old('description', $item->description) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Barang</label>
            @if($item->image)
                <div class="mb-2"><img src="{{ asset($item->image) }}" class="w-20 h-20 rounded-lg object-cover" alt="Foto"></div>
            @endif
            <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
            <p class="text-xs text-gray-400 mt-1">Biarkan kosong jika tidak ingin mengubah foto.</p>
        </div>
        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('admin.inventaris.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow transition">Simpan Perubahan</button>
        </div>
    </div>
</form>
@endsection
