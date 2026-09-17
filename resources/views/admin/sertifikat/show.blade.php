@extends('layouts.admin')
@section('title', 'Detail Pengajuan Sertifikat')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.sertifikat.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2"><i class="ph ph-arrow-left"></i> Kembali</a>
    <h1 class="text-xl font-bold text-gray-900">Detail Pengajuan: {{ $certRequest->request_code }}</h1>
</div>

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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Request Info --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Data Permohonan</h2>
                @php
                    $bc = 'bg-gray-100 text-gray-800';
                    if ($certRequest->status == 'Selesai') $bc = 'bg-green-100 text-green-800';
                    elseif (in_array($certRequest->status, ['Diproses','Menunggu Dokumen'])) $bc = 'bg-blue-100 text-blue-800';
                    elseif ($certRequest->status == 'Ditolak') $bc = 'bg-rose-100 text-rose-800';
                @endphp
                <span class="px-3 py-1 text-xs font-bold rounded-full {{ $bc }}">{{ $certRequest->status }}</span>
            </div>
            <div class="p-5">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500 text-xs">Nama Pemohon</dt><dd class="text-gray-900 font-medium">{{ $certRequest->applicant_name }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">NIK</dt><dd class="text-gray-900">{{ $certRequest->nik ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">No. HP</dt><dd class="text-gray-900">{{ $certRequest->phone ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Jenis Dokumen</dt><dd class="text-gray-900 font-bold">{{ $certRequest->document_type }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Nama Kegiatan</dt><dd class="text-gray-900">{{ $certRequest->activity_name }}</dd></div>
                    @if($certRequest->activity_theme)
                    <div><dt class="text-gray-500 text-xs">Tema / Judul</dt><dd class="text-gray-900">{{ $certRequest->activity_theme }}</dd></div>
                    @endif
                    <div><dt class="text-gray-500 text-xs">Tanggal Kegiatan</dt><dd class="text-gray-900">{{ $certRequest->activity_date->format('d M Y') }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Tempat Kegiatan</dt><dd class="text-gray-900">{{ $certRequest->activity_place }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500 text-xs">Keperluan</dt><dd class="text-gray-900">{{ $certRequest->purpose }}</dd></div>
                    @if($certRequest->description)
                    <div class="sm:col-span-2"><dt class="text-gray-500 text-xs">Keterangan</dt><dd class="text-gray-900">{{ $certRequest->description }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Uploaded Certificates --}}
        @if($certRequest->files->count() > 0)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">File Sertifikat/Piagam yang Diunggah</h3>
            <div class="space-y-2">
                @foreach($certRequest->files as $file)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-2 text-sm text-gray-700">
                        <i class="ph ph-file-pdf text-lg text-green-500"></i>
                        <span>{{ $file->original_filename }}</span>
                    </div>
                    <span class="text-xs text-gray-400">{{ $file->created_at->format('d M Y H:i') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Application File --}}
        @if($certRequest->application_file)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Dokumen Permohonan</h3>
            <a href="{{ route('admin.sertifikat.downloadApplication', $certRequest->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-sm font-medium transition">
                <i class="ph ph-download-simple"></i> Download Surat Permohonan
            </a>
        </div>
        @endif
    </div>

    {{-- Actions Sidebar --}}
    <div class="space-y-6">
        {{-- Status Update --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4">Ubah Status</h3>
            <form action="{{ route('admin.sertifikat.updateStatus', $certRequest->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select name="status" required class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 outline-none">
                            @foreach(['Diajukan','Diproses','Menunggu Dokumen','Selesai','Ditolak'] as $s)
                            <option value="{{ $s }}" {{ $certRequest->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Penolakan / Catatan</label>
                        <textarea name="rejection_reason" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 outline-none" placeholder="Opsional">{{ $certRequest->rejection_reason }}</textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow transition">Simpan Status</button>
                </div>
            </form>
        </div>

        {{-- Upload Certificate --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4">Upload Sertifikat / Piagam</h3>
            <form action="{{ route('admin.sertifikat.upload', $certRequest->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-3">
                    <div>
                        <input type="file" name="certificate_file" required accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-600 hover:file:bg-purple-100">
                        <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG. Maks 5MB.</p>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-semibold shadow transition flex items-center justify-center gap-2">
                        <i class="ph ph-upload-simple"></i> Upload Sertifikat
                    </button>
                </div>
            </form>
            <p class="text-xs text-gray-400 mt-2 leading-relaxed">Setelah upload berhasil, status akan otomatis berubah menjadi <strong>"Selesai"</strong> dan masyarakat dapat mengunduh file.</p>
        </div>
    </div>
</div>
@endsection
