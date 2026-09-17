@extends('layouts.masyarakat')
@section('title', 'Form Peminjaman Inventaris')

@section('content')
<div class="mb-6">
    <a href="{{ route('masyarakat.peminjaman_inventaris.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
        <i class="ph ph-arrow-left"></i> Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Form Peminjaman Barang Inventaris</h1>
    <p class="text-sm text-gray-500 mt-1">Lengkapi data di bawah untuk mengajukan peminjaman barang.</p>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
    <div class="flex items-center gap-2 text-red-700 text-sm font-semibold mb-1"><i class="ph ph-warning-circle"></i> Terjadi kesalahan</div>
    <ul class="list-disc list-inside text-sm text-red-600">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('masyarakat.peminjaman_inventaris.store') }}" method="POST" enctype="multipart/form-data" x-data="inventoryForm()">
    @csrf

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="ph ph-user text-indigo-500"></i> Data Pemohon
            </h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemohon <span class="text-red-500">*</span></label>
                <input type="text" name="applicant_name" value="{{ old('applicant_name', $user->name) }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                <input type="text" value="{{ $user->nik }}" disabled class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $user->no_hp) }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Instansi / Organisasi</label>
                <input type="text" name="institution" value="{{ old('institution') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 outline-none" placeholder="Opsional">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="ph ph-package text-indigo-500"></i> Detail Peminjaman
            </h2>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan <span class="text-red-500">*</span></label>
                <textarea name="purpose" rows="3" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 outline-none" placeholder="Jelaskan keperluan peminjaman">{{ old('purpose') }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Peminjaman <span class="text-red-500">*</span></label>
                    <input type="date" name="borrow_date" value="{{ old('borrow_date') }}" required min="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengembalian <span class="text-red-500">*</span></label>
                    <input type="date" name="return_date" value="{{ old('return_date') }}" required min="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 outline-none">
                </div>
            </div>
        </div>
    </div>

    {{-- Pilihan Barang --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="ph ph-cube text-indigo-500"></i> Pilihan Barang
            </h2>
            <button type="button" @click="addItem()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                <i class="ph ph-plus-circle"></i> Tambah Barang
            </button>
        </div>
        <div class="p-6 space-y-4">
            <template x-for="(entry, index) in selectedItems" :key="index">
                <div class="flex flex-col sm:flex-row gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Pilih Barang</label>
                        <select x-model="entry.id" :name="'items['+index+'][id]'" required @change="updateMax(index)" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 outline-none">
                            <option value="">-- Pilih barang --</option>
                            @foreach($items as $item)
                            <option value="{{ $item->id }}" data-max="{{ $item->available_quantity }}">{{ $item->name }} (Tersedia: {{ $item->available_quantity }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-32">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jumlah</label>
                        <input type="number" x-model="entry.quantity" :name="'items['+index+'][quantity]'" min="1" :max="entry.maxQty" required class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 outline-none">
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="removeItem(index)" x-show="selectedItems.length > 1" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                            <i class="ph ph-trash text-lg"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="ph ph-note text-indigo-500"></i> Dokumen & Catatan
            </h2>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Tambahan</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 outline-none" placeholder="Opsional">{{ old('notes') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Surat Permohonan</label>
                <input type="file" name="application_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, JPEG, PNG. Maks 5MB.</p>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('masyarakat.peminjaman_inventaris.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition">Batal</a>
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow transition flex items-center gap-2">
            <i class="ph ph-paper-plane-tilt"></i> Kirim Pengajuan
        </button>
    </div>
</form>

<script>
function inventoryForm() {
    return {
        selectedItems: [{ id: '', quantity: 1, maxQty: 999 }],
        addItem() {
            this.selectedItems.push({ id: '', quantity: 1, maxQty: 999 });
        },
        removeItem(index) {
            this.selectedItems.splice(index, 1);
        },
        updateMax(index) {
            const select = document.querySelectorAll('select[name^="items"]')[index];
            if (select) {
                const option = select.options[select.selectedIndex];
                this.selectedItems[index].maxQty = parseInt(option.dataset.max) || 999;
                if (this.selectedItems[index].quantity > this.selectedItems[index].maxQty) {
                    this.selectedItems[index].quantity = this.selectedItems[index].maxQty;
                }
            }
        }
    }
}
</script>
@endsection
