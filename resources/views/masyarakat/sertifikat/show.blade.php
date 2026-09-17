@extends('layouts.masyarakat')
@section('title', 'Detail Pengajuan Sertifikat')

@section('content')
<div class="mb-6">
    <a href="{{ route('masyarakat.sertifikat.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
        <i class="ph ph-arrow-left"></i> Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Detail Pengajuan Sertifikat / Piagam</h1>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 text-sm text-green-700 flex items-center gap-2">
    <i class="ph ph-check-circle text-lg"></i> {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Informasi Pengajuan</h2>
                @php
                    $bc = 'bg-gray-100 text-gray-800';
                    if ($certRequest->status == 'Selesai') $bc = 'bg-green-100 text-green-800';
                    elseif (in_array($certRequest->status, ['Diproses','Menunggu Dokumen'])) $bc = 'bg-blue-100 text-blue-800';
                    elseif ($certRequest->status == 'Ditolak') $bc = 'bg-rose-100 text-rose-800';
                @endphp
                <span class="px-3 py-1 text-xs font-bold rounded-full {{ $bc }}">{{ $certRequest->status }}</span>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><dt class="text-gray-500 font-medium">Kode Pengajuan</dt><dd class="text-gray-900 font-bold mt-0.5">{{ $certRequest->request_code }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">Jenis Dokumen</dt><dd class="text-gray-900 mt-0.5">{{ $certRequest->document_type }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">Nama Pemohon</dt><dd class="text-gray-900 mt-0.5">{{ $certRequest->applicant_name }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">NIK</dt><dd class="text-gray-900 mt-0.5">{{ $certRequest->nik ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">No. HP</dt><dd class="text-gray-900 mt-0.5">{{ $certRequest->phone ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">Nama Kegiatan</dt><dd class="text-gray-900 mt-0.5">{{ $certRequest->activity_name }}</dd></div>
                    @if($certRequest->activity_theme)
                    <div><dt class="text-gray-500 font-medium">Tema / Judul</dt><dd class="text-gray-900 mt-0.5">{{ $certRequest->activity_theme }}</dd></div>
                    @endif
                    <div><dt class="text-gray-500 font-medium">Tanggal Kegiatan</dt><dd class="text-gray-900 mt-0.5">{{ $certRequest->activity_date->format('d M Y') }}</dd></div>
                    <div><dt class="text-gray-500 font-medium">Tempat Kegiatan</dt><dd class="text-gray-900 mt-0.5">{{ $certRequest->activity_place }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500 font-medium">Keperluan</dt><dd class="text-gray-900 mt-0.5">{{ $certRequest->purpose }}</dd></div>
                    @if($certRequest->description)
                    <div class="sm:col-span-2"><dt class="text-gray-500 font-medium">Keterangan</dt><dd class="text-gray-900 mt-0.5">{{ $certRequest->description }}</dd></div>
                    @endif
                    @if($certRequest->rejection_reason)
                    <div class="sm:col-span-2"><dt class="text-red-500 font-medium">Alasan Penolakan</dt><dd class="text-red-700 mt-0.5">{{ $certRequest->rejection_reason }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Download Area - only if Selesai --}}
        @if($certRequest->status == 'Selesai' && $certRequest->files->count() > 0)
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="ph ph-check-circle text-2xl text-green-600"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-green-900">Permohonan Anda telah selesai.</h3>
                    <p class="text-xs text-green-700">{{ $certRequest->document_type }} sudah tersedia dan dapat diunduh.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach($certRequest->files as $file)
                <a href="{{ route('masyarakat.sertifikat.download', $certRequest->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold shadow transition">
                    <i class="ph ph-download-simple text-lg"></i> Download {{ $certRequest->document_type }}
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Side Info --}}
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Timeline Status</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center"><i class="ph ph-paper-plane-tilt text-purple-600 text-sm"></i></div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">Diajukan</p>
                        <p class="text-[11px] text-gray-400">{{ $certRequest->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                </div>
                @if($certRequest->updated_at != $certRequest->created_at)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 {{ $certRequest->status == 'Selesai' ? 'bg-green-100' : 'bg-blue-100' }} rounded-full flex items-center justify-center">
                        <i class="ph ph-arrows-clockwise {{ $certRequest->status == 'Selesai' ? 'text-green-600' : 'text-blue-600' }} text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">{{ $certRequest->status }}</p>
                        <p class="text-[11px] text-gray-400">{{ $certRequest->updated_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($certRequest->application_file)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Dokumen Permohonan</h3>
            <a href="{{ asset($certRequest->application_file) }}" target="_blank" class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800">
                <i class="ph ph-file-pdf text-lg"></i> Lihat Surat Permohonan
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
