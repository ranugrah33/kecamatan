@extends('layouts.admin')
@section('title', 'Detail Pengajuan Sertifikat / Piagam')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.sertifikat.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2"><i class="ph ph-arrow-left"></i> Kembali</a>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Detail Pengajuan: {{ $certRequest->request_code }}</h1>
            @if($certRequest->nomor_sertifikat)
                <p class="text-xs text-gray-500 font-medium mt-0.5">Nomor Sertifikat: <span class="font-bold text-gray-800">{{ $certRequest->nomor_sertifikat }}</span></p>
            @endif
        </div>

        {{-- Tombol Cetak / Generate Draft Sertifikat --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.sertifikat.cetak', $certRequest->id) }}" target="_blank" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-sm transition flex items-center gap-2">
                <i class="ph ph-printer text-lg"></i> Cetak / Generate Sertifikat (PDF)
            </a>
        </div>
    </div>
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
        {{-- Data Sertifikat yang Akan Dicetak --}}
        <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-emerald-100 bg-emerald-50/50 flex items-center justify-between">
                <h2 class="text-sm font-bold text-emerald-900 flex items-center gap-2">
                    <i class="ph ph-certificate text-emerald-600 text-lg"></i> Konten yang Dicetak pada Sertifikat
                </h2>
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                    {{ $certRequest->document_type }}
                </span>
            </div>
            <div class="p-5 space-y-4">
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-xs">
                    <span class="text-xs text-gray-400 font-medium block mb-1">Nama Penerima di Sertifikat:</span>
                    <p class="text-xl font-bold text-gray-900 tracking-wide">{{ $certRequest->certificate_name ?: $certRequest->applicant_name }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-gray-400 font-medium block">Kegiatan / Proyek:</span>
                        <p class="text-gray-900 font-semibold">{{ $certRequest->activity_name }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 font-medium block">Tanggal Kegiatan:</span>
                        <p class="text-gray-900 font-semibold">{{ $certRequest->activity_date->translatedFormat('d F Y') }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 font-medium block">Tempat:</span>
                        <p class="text-gray-900 font-semibold">{{ $certRequest->activity_place }}</p>
                    </div>
                    @if($certRequest->activity_theme)
                    <div>
                        <span class="text-xs text-gray-400 font-medium block">Tema:</span>
                        <p class="text-gray-900">{{ $certRequest->activity_theme }}</p>
                    </div>
                    @endif
                </div>

                <div class="border-t border-gray-100 pt-3">
                    <span class="text-xs text-gray-400 font-medium block mb-1">Teks / Keterangan di Bawah Nama:</span>
                    <p class="text-sm text-gray-700 bg-gray-50 p-3.5 rounded-xl border border-gray-200/60 leading-relaxed font-serif italic">
                        "{{ $certRequest->certificate_text ?: ('Atas kontribusi dan partisipasi aktif dalam ' . $certRequest->activity_name . ', serta dedikasi dan kinerja yang ditunjukkan dalam mencapai tujuan kegiatan.') }}"
                    </p>
                </div>
            </div>
        </div>

        {{-- Request Info --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Data Pemohon</h2>
                @php
                    $bc = 'bg-gray-100 text-gray-800';
                    if (in_array($certRequest->status, ['Selesai', 'Sertifikat Tersedia'])) $bc = 'bg-green-100 text-green-800';
                    elseif (in_array($certRequest->status, ['Diproses','Disetujui'])) $bc = 'bg-blue-100 text-blue-800';
                    elseif ($certRequest->status == 'Ditolak') $bc = 'bg-rose-100 text-rose-800';
                @endphp
                <span class="px-3 py-1 text-xs font-bold rounded-full {{ $bc }}">{{ $certRequest->status }}</span>
            </div>
            <div class="p-5">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500 text-xs">Nama Pemohon (Akun)</dt><dd class="text-gray-900 font-medium">{{ $certRequest->applicant_name }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">No. HP / WhatsApp</dt><dd class="text-gray-900 font-medium">{{ $certRequest->phone ?? '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500 text-xs">Keperluan Pengajuan</dt><dd class="text-gray-900">{{ $certRequest->purpose }}</dd></div>
                    @if($certRequest->description)
                    <div class="sm:col-span-2"><dt class="text-gray-500 text-xs">Keterangan Tambahan</dt><dd class="text-gray-900">{{ $certRequest->description }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- File Sertifikat Final yang Sudah Diunggah --}}
        @if($certRequest->file_sertifikat_final)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                <i class="ph ph-file-check text-green-600 text-lg"></i> File Sertifikat Final (Hasil Scan / Stempel Basah)
            </h3>
            <div class="flex items-center justify-between p-3.5 bg-green-50/60 border border-green-200 rounded-xl">
                <div class="flex items-center gap-2.5 text-sm font-medium text-green-900">
                    <i class="ph ph-file-pdf text-xl text-green-600"></i>
                    <span>Sertifikat Final Sudah Tersedia</span>
                </div>
                <a href="{{ asset('storage/' . $certRequest->file_sertifikat_final) }}" target="_blank" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1">
                    <i class="ph ph-download-simple"></i> Lihat / Unduh
                </a>
            </div>
        </div>
        @endif

        {{-- Dokumen Permohonan Pemohon --}}
        @if($certRequest->application_file)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Dokumen Berkas Permohonan</h3>
            <a href="{{ route('admin.sertifikat.downloadApplication', $certRequest->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-sm font-medium transition">
                <i class="ph ph-download-simple"></i> Download Berkas Permohonan
            </a>
        </div>
        @endif
    </div>

    {{-- Actions Sidebar --}}
    <div class="space-y-6">
        {{-- Status Update --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="ph ph-sliders text-blue-500"></i> Kelola Status
            </h3>
            <form action="{{ route('admin.sertifikat.updateStatus', $certRequest->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Status Pengajuan</label>
                        <select name="status" required class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 outline-none">
                            @foreach(['Diajukan','Diproses','Disetujui','Sertifikat Tersedia','Selesai','Ditolak'] as $s)
                            <option value="{{ $s }}" {{ $certRequest->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Sertifikat / Piagam</label>
                        <input type="text" name="nomor_sertifikat" value="{{ old('nomor_sertifikat', $certRequest->nomor_sertifikat) }}" placeholder="Otomatis jika kosong" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 outline-none">
                        <span class="text-[11px] text-gray-400 mt-0.5 block">Format: 421.1/XXX/Kec-Ckp/YYYY</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Penolakan / Catatan</label>
                        <textarea name="rejection_reason" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 outline-none" placeholder="Opsional bila ditolak">{{ $certRequest->rejection_reason }}</textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Upload Certificate Final (Seperti Aula) --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-2">
                <i class="ph ph-upload-simple text-emerald-600"></i> Upload Sertifikat Final
            </h3>
            <p class="text-xs text-gray-500 mb-4 leading-relaxed">Jika sertifikat dicetak fisik dan ditandatangani basah / stempel, unggah file scan di sini.</p>
            <form action="{{ route('admin.sertifikat.upload_final', $certRequest->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-3">
                    <div>
                        <input type="file" name="file_sertifikat_final" required accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-gray-600 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        <p class="text-[11px] text-gray-400 mt-1">Format: PDF, JPG, PNG (Maks 5MB)</p>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow transition flex items-center justify-center gap-2">
                        <i class="ph ph-check-circle"></i> Upload & Selesaikan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
