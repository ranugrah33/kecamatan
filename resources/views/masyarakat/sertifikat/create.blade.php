@extends('layouts.masyarakat')
@section('title', 'Form Pengajuan Sertifikat / Piagam')

@section('content')
<div class="mb-6">
    <a href="{{ route('masyarakat.sertifikat.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
        <i class="ph ph-arrow-left"></i> Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Form Pengajuan Sertifikat / Piagam</h1>
    <p class="text-sm text-gray-500 mt-1">Lengkapi data di bawah untuk mengajukan pembuatan sertifikat atau piagam.</p>
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

<form action="{{ route('masyarakat.sertifikat.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Data Pemohon --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="ph ph-user text-purple-500"></i> Data Pemohon
            </h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemohon <span class="text-red-500">*</span></label>
                <input type="text" name="applicant_name" value="{{ old('applicant_name', $user->name) }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                <input type="text" value="{{ $user->nik }}" disabled class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $user->no_hp) }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Dokumen <span class="text-red-500">*</span></label>
                <select name="document_type" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-400 outline-none">
                    <option value="">-- Pilih jenis --</option>
                    <option value="Sertifikat" {{ old('document_type') == 'Sertifikat' ? 'selected' : '' }}>Sertifikat</option>
                    <option value="Piagam" {{ old('document_type') == 'Piagam' ? 'selected' : '' }}>Piagam</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Detail Kegiatan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="ph ph-calendar-blank text-purple-500"></i> Detail Kegiatan
            </h2>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kegiatan <span class="text-red-500">*</span></label>
                    <input type="text" name="activity_name" value="{{ old('activity_name') }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-400 outline-none" placeholder="Nama kegiatan">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tema / Judul Kegiatan</label>
                    <input type="text" name="activity_theme" value="{{ old('activity_theme') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-400 outline-none" placeholder="Opsional">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kegiatan <span class="text-red-500">*</span></label>
                    <input type="date" name="activity_date" value="{{ old('activity_date') }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-400 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Kegiatan <span class="text-red-500">*</span></label>
                    <input type="text" name="activity_place" value="{{ old('activity_place') }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-400 outline-none" placeholder="Lokasi kegiatan">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan <span class="text-red-500">*</span></label>
                <textarea name="purpose" rows="3" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-400 outline-none" placeholder="Jelaskan keperluan pengajuan sertifikat/piagam">{{ old('purpose') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Tambahan</label>
                <textarea name="description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-400 outline-none" placeholder="Opsional">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Dokumen --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="ph ph-file-arrow-up text-purple-500"></i> Upload Dokumen
            </h2>
        </div>
        <div class="p-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Surat Permohonan</label>
                <input type="file" name="application_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-600 hover:file:bg-purple-100">
                <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, JPEG, PNG. Maks 5MB.</p>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('masyarakat.sertifikat.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition">Batal</a>
        <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-semibold shadow transition flex items-center gap-2">
            <i class="ph ph-paper-plane-tilt"></i> Kirim Pengajuan
        </button>
    </div>
</form>
@endsection
