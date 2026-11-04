@extends('layouts.masyarakat')
@section('title', 'Detail Pengajuan Sertifikat / Piagam')

@section('content')
<div class="mb-6">
    <a href="{{ route('masyarakat.sertifikat.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
        <i class="ph ph-arrow-left"></i> Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Detail Pengajuan {{ $certRequest->document_type }}</h1>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 text-sm text-green-700 flex items-center gap-2">
    <i class="ph ph-check-circle text-lg"></i> {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Area Download jika sudah disetujui / selesai --}}
        @if(in_array($certRequest->status, ['Disetujui', 'Sertifikat Tersedia', 'Selesai']))
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl border border-emerald-200 p-6 shadow-xs">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center flex-shrink-0 text-emerald-600">
                    <i class="ph ph-certificate text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-emerald-950">{{ $certRequest->document_type }} Anda Telah Tersedia!</h3>
                    <p class="text-xs text-emerald-700 mt-0.5 leading-relaxed">
                        Permohonan telah disetujui oleh pihak Kecamatan Cikampek. Silakan unduh dokumen Anda di bawah ini:
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                {{-- Download Auto-Generated PDF --}}
                <a href="{{ route('masyarakat.sertifikat.download_pdf', $certRequest->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-sm transition">
                    <i class="ph ph-file-pdf text-lg"></i> Unduh {{ $certRequest->document_type }} (PDF)
                </a>

                {{-- Download File Final jika admin unggah file scan stempel --}}
                @if($certRequest->file_sertifikat_final)
                <a href="{{ route('masyarakat.sertifikat.download_final', $certRequest->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-sm transition">
                    <i class="ph ph-download-simple text-lg"></i> Unduh Berkas Final (TTD/Cap)
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Detail Konten Sertifikat --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <i class="ph ph-certificate text-emerald-600"></i> Konten Sertifikat / Piagam
                </h2>
                @php
                    $bc = 'bg-gray-100 text-gray-800';
                    if (in_array($certRequest->status, ['Selesai', 'Sertifikat Tersedia'])) $bc = 'bg-emerald-100 text-emerald-800';
                    elseif (in_array($certRequest->status, ['Diproses','Disetujui'])) $bc = 'bg-blue-100 text-blue-800';
                    elseif ($certRequest->status == 'Ditolak') $bc = 'bg-rose-100 text-rose-800';
                @endphp
                <span class="px-3 py-1 text-xs font-bold rounded-full {{ $bc }}">{{ $certRequest->status }}</span>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-emerald-50/40 p-4 rounded-xl border border-emerald-100">
                    <span class="text-xs text-gray-500 font-medium block">Nama Penerima Tercetak:</span>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">{{ $certRequest->certificate_name ?: $certRequest->applicant_name }}</p>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 text-xs font-medium">Kode Pengajuan</dt>
                        <dd class="text-gray-900 font-bold mt-0.5">{{ $certRequest->request_code }}</dd>
                    </div>
                    @if($certRequest->nomor_sertifikat)
                    <div>
                        <dt class="text-gray-500 text-xs font-medium">Nomor Sertifikat</dt>
                        <dd class="text-emerald-700 font-bold mt-0.5">{{ $certRequest->nomor_sertifikat }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-gray-500 text-xs font-medium">Jenis Dokumen</dt>
                        <dd class="text-gray-900 font-semibold mt-0.5">{{ $certRequest->document_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs font-medium">Nama Kegiatan</dt>
                        <dd class="text-gray-900 font-semibold mt-0.5">{{ $certRequest->activity_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs font-medium">Tanggal Kegiatan</dt>
                        <dd class="text-gray-900 mt-0.5">{{ $certRequest->activity_date->translatedFormat('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs font-medium">Tempat Kegiatan</dt>
                        <dd class="text-gray-900 mt-0.5">{{ $certRequest->activity_place }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 text-xs font-medium">Teks / Kalimat Keterangan di Bawah Nama</dt>
                        <dd class="text-gray-800 bg-gray-50 p-3 rounded-lg border border-gray-100 mt-1 font-serif italic text-xs leading-relaxed">
                            "{{ $certRequest->certificate_text ?: ('Atas kontribusi dan partisipasi aktif dalam ' . $certRequest->activity_name . ', serta dedikasi dan kinerja yang ditunjukkan dalam mencapai tujuan kegiatan.') }}"
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 text-xs font-medium">Keperluan</dt>
                        <dd class="text-gray-900 mt-0.5">{{ $certRequest->purpose }}</dd>
                    </div>

                    @if($certRequest->rejection_reason)
                    <div class="sm:col-span-2 bg-red-50 p-3.5 rounded-xl border border-red-200">
                        <dt class="text-red-700 text-xs font-bold">Alasan Penolakan</dt>
                        <dd class="text-red-800 text-sm mt-0.5">{{ $certRequest->rejection_reason }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    {{-- Side Info --}}
    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Timeline Pengajuan</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600"><i class="ph ph-paper-plane-tilt text-sm"></i></div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">Diajukan</p>
                        <p class="text-[11px] text-gray-400">{{ $certRequest->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
                    </div>
                </div>
                @if($certRequest->updated_at != $certRequest->created_at)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 {{ in_array($certRequest->status, ['Selesai', 'Sertifikat Tersedia', 'Disetujui']) ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600' }} rounded-full flex items-center justify-center">
                        <i class="ph ph-arrows-clockwise text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">{{ $certRequest->status }}</p>
                        <p class="text-[11px] text-gray-400">{{ $certRequest->updated_at->translatedFormat('d M Y, H:i') }} WIB</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($certRequest->application_file)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Berkas Permohonan Anda</h3>
            <a href="{{ asset($certRequest->application_file) }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-semibold text-blue-600 hover:text-blue-800">
                <i class="ph ph-file-pdf text-lg"></i> Lihat Dokumen yang Diunggah
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
