@extends('layouts.admin')
@section('title', 'Detail Pengajuan Inventaris')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.peminjaman_inventaris.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2"><i class="ph ph-arrow-left"></i> Kembali</a>
    <h1 class="text-xl font-bold text-gray-900">Detail Pengajuan: {{ $borrowing->request_code }}</h1>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 text-sm text-green-700 flex items-center gap-2">
    <i class="ph ph-check-circle text-lg"></i> {{ session('success') }}
</div>
@endif
@if(isset($errors) && $errors->any())
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 text-sm text-red-700">
    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Borrowing Info --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Data Pemohon</h2>
                @php
                    $displayStatus = $borrowing->status_label;
                    $bc = match($displayStatus) {
                        'Menunggu Persetujuan' => 'bg-blue-100 text-blue-800',
                        'Disetujui' => 'bg-emerald-100 text-emerald-800',
                        'Sedang Dipinjam' => 'bg-amber-100 text-amber-800',
                        'Ditolak' => 'bg-rose-100 text-rose-800',
                        'Selesai' => 'bg-green-100 text-green-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                @endphp
                <span class="px-3 py-1 text-xs font-bold rounded-full {{ $bc }}">{{ $displayStatus }}</span>
            </div>
            <div class="p-5">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500 text-xs">Nama Pemohon</dt><dd class="text-gray-900 font-medium">{{ $borrowing->applicant_name }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">No. HP</dt><dd class="text-gray-900">{{ $borrowing->phone ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Instansi</dt><dd class="text-gray-900">{{ $borrowing->institution ?? '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500 text-xs">Penanggung Jawab</dt><dd class="text-gray-900">{{ $borrowing->penanggung_jawab }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500 text-xs">Keperluan</dt><dd class="text-gray-900">{{ $borrowing->purpose }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Tanggal Peminjaman</dt><dd class="text-gray-900 font-medium">{{ $borrowing->borrow_date->translatedFormat('d F Y') }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Jam Peminjaman</dt><dd class="text-gray-900 font-medium">{{ $borrowing->borrowing_time ? \Carbon\Carbon::parse($borrowing->borrowing_time)->format('H:i') . ' WIB' : '-' }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Tanggal Pengembalian</dt><dd class="text-gray-900 font-medium">{{ $borrowing->return_date->translatedFormat('d F Y') }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Jam Pengembalian</dt><dd class="text-gray-900 font-medium">{{ $borrowing->return_time ? \Carbon\Carbon::parse($borrowing->return_time)->format('H:i') . ' WIB' : '-' }}</dd></div>
                    @if($borrowing->notes)
                    <div class="sm:col-span-2"><dt class="text-gray-500 text-xs">Catatan</dt><dd class="text-gray-900">{{ $borrowing->notes }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100"><h2 class="text-sm font-bold text-gray-900">Barang yang Dipinjam</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead><tr class="bg-gray-50"><th class="px-4 py-2 text-left text-[11px] font-semibold text-gray-400 uppercase">Barang</th><th class="px-4 py-2 text-left text-[11px] font-semibold text-gray-400 uppercase">Jumlah</th><th class="px-4 py-2 text-left text-[11px] font-semibold text-gray-400 uppercase">Tersedia</th></tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($borrowing->details as $d)
                        <tr><td class="px-4 py-3 text-sm text-gray-800">{{ $d->item->name }}</td><td class="px-4 py-3 text-sm font-bold text-gray-700">{{ $d->quantity }}</td><td class="px-4 py-3 text-sm text-gray-500">{{ $d->item->available_quantity }} / {{ $d->item->total_quantity }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($borrowing->application_file)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Dokumen Permohonan Pemohon</h3>
            <a href="{{ asset($borrowing->application_file) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1"><i class="ph ph-file-pdf text-lg"></i> Download Surat Permohonan</a>
        </div>
        @endif

        {{-- DOKUMEN PERSETUJUAN PEMINJAMAN --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ showReplaceForm: false, showHistory: false }">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        <i class="ph ph-file-text text-lg"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">DOKUMEN PERSETUJUAN PEMINJAMAN</h2>
                </div>
                <div>
                    @php
                        $docStatus = $borrowing->document_status_label;
                        $docBadgeClass = match($docStatus) {
                            'Surat Final Tersedia' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'Draft Tersedia (Menunggu Surat Final)' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                            default => 'bg-gray-100 text-gray-600 border-gray-200',
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $docBadgeClass }}">
                        <i class="ph ph-circle-wavy-check text-sm"></i> {{ $docStatus }}
                    </span>
                </div>
            </div>

            <div class="p-5 space-y-6">
                {{-- 1. Draft Surat Otomatis --}}
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <div class="flex items-start justify-between gap-4 flex-wrap sm:flex-nowrap">
                        <div class="space-y-1">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-100 text-indigo-800 uppercase tracking-wider">
                                Draft Otomatis
                            </span>
                            <h3 class="text-sm font-bold text-gray-900">Draft Surat Persetujuan Peminjaman</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Draft dokumen PDF digenerate secara dinamis dari data transaksi terkini untuk dicetak dan diproses pengesahan fisik (tanda tangan &amp; stempel resmi Camat).
                            </p>
                        </div>
                        <div class="shrink-0 w-full sm:w-auto">
                            @if(in_array($borrowing->status, [\App\Models\InventoryBorrowing::STATUS_DISETUJUI, \App\Models\InventoryBorrowing::STATUS_SEDANG_DIPINJAM, \App\Models\InventoryBorrowing::STATUS_SELESAI, 'Dikembalikan']))
                                <a href="{{ route('admin.peminjaman_inventaris.cetakDraftSurat', $borrowing->id) }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                                    <i class="ph ph-printer text-base"></i> Unduh Draft (PDF)
                                </a>
                            @else
                                <button type="button" disabled class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-200 text-gray-400 text-xs font-semibold rounded-xl cursor-not-allowed">
                                    <i class="ph ph-lock text-base"></i> Draft Terkunci
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 2. Surat Final --}}
                @php
                    $activeDoc = $borrowing->activeFinalDocument;
                @endphp

                @if($activeDoc)
                    {{-- Detail Surat Final Aktif --}}
                    <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-xl space-y-3">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-900">
                                    Surat Persetujuan Final (Aktif &mdash; Versi {{ $activeDoc->version }})
                                </h4>
                            </div>
                            @if($borrowing->status === \App\Models\InventoryBorrowing::STATUS_DISETUJUI)
                                <button type="button" @click="showReplaceForm = !showReplaceForm" class="text-xs font-semibold text-emerald-800 hover:text-emerald-950 underline flex items-center gap-1 cursor-pointer">
                                    <i class="ph ph-arrows-clockwise text-sm"></i>
                                    <span x-text="showReplaceForm ? 'Tutup Formulir Ganti' : 'Ganti Surat Final'">Ganti Surat Final</span>
                                </button>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                            <div>
                                <span class="text-gray-500 block">Nomor Surat Resmi:</span>
                                <span class="font-bold text-gray-900">{{ $activeDoc->nomor_surat ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Nama Berkas:</span>
                                <span class="font-medium text-gray-800 truncate block" title="{{ $activeDoc->file_name }}">
                                    {{ $activeDoc->file_name }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Waktu Upload:</span>
                                <span class="font-medium text-gray-800">
                                    {{ $activeDoc->created_at->translatedFormat('d F Y, H:i') }} WIB
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Admin Pengunggah:</span>
                                <span class="font-medium text-gray-800">
                                    {{ $activeDoc->uploader->name ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-2 flex items-center gap-2 flex-wrap">
                            <a href="{{ route('admin.peminjaman_inventaris.downloadSuratFinal', $borrowing->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                <i class="ph ph-download-simple text-sm"></i> Unduh Surat Final
                            </a>

                            @if($borrowing->finalDocuments->count() > 1)
                                <button type="button" @click="showHistory = !showHistory" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-medium transition cursor-pointer">
                                    <i class="ph ph-clock-counter-clockwise text-sm"></i>
                                    <span>Riwayat Versi ({{ $borrowing->finalDocuments->count() }})</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Riwayat Dokumen Persetujuan Sebelumnya (Bila ada pergantian) --}}
                    @if($borrowing->finalDocuments->count() > 1)
                        <div x-cloak x-show="showHistory" class="border border-gray-200 rounded-xl overflow-hidden text-xs">
                            <div class="bg-gray-50 px-4 py-2.5 font-bold text-gray-700 flex items-center justify-between">
                                <span>Riwayat Arsip Dokumen Surat Persetujuan</span>
                                <span class="text-[11px] text-gray-500 font-normal">File lama tetap tersimpan sebagai arsip</span>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @foreach($borrowing->finalDocuments as $histDoc)
                                    <div class="p-3 flex items-center justify-between gap-2 hover:bg-gray-50">
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-gray-800">Versi {{ $histDoc->version }}</span>
                                                @if($histDoc->is_active)
                                                    <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 font-bold rounded text-[10px]">Aktif</span>
                                                @else
                                                    <span class="px-1.5 py-0.2 bg-gray-100 text-gray-600 rounded text-[10px]">Arsip / Replaced</span>
                                                @endif
                                            </div>
                                            <p class="text-gray-500 text-[11px]">
                                                Diupload: {{ $histDoc->created_at->translatedFormat('d M Y, H:i') }} WIB oleh {{ $histDoc->uploader->name ?? '-' }}
                                            </p>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.peminjaman_inventaris.downloadSuratFinal', ['id' => $borrowing->id, 'documentId' => $histDoc->id]) }}" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1">
                                                <i class="ph ph-download-simple"></i> Unduh
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Form Ganti Surat Final --}}
                    @if($borrowing->status === \App\Models\InventoryBorrowing::STATUS_DISETUJUI)
                        <div x-cloak x-show="showReplaceForm" class="p-4 bg-amber-50/70 border border-amber-200 rounded-xl space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-amber-950 flex items-center gap-1.5">
                                    <i class="ph ph-arrow-counter-clockwise text-amber-600"></i> Form Penggantian Surat Persetujuan Final
                                </h4>
                                <button type="button" @click="showReplaceForm = false" class="text-gray-400 hover:text-gray-600">
                                    <i class="ph ph-x text-sm"></i>
                                </button>
                            </div>
                            <p class="text-xs text-amber-900/90 leading-relaxed">
                                Unggah berkas baru untuk menggantikan surat persetujuan aktif. Berkas lama tidak akan dihapus dan akan disimpan secara aman dalam riwayat arsip dokumen.
                            </p>

                            <form action="{{ route('admin.peminjaman_inventaris.uploadSuratFinal', $borrowing->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Surat Resmi (Opsional)</label>
                                    <input type="text" name="nomor_surat" value="{{ old('nomor_surat', $activeDoc->nomor_surat) }}" placeholder="Contoh: 000.1.2/012/Kec-Ckp/2026" class="w-full px-3 py-2 border rounded-lg text-xs bg-white focus:ring-2 focus:ring-blue-100 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Pilih Berkas PDF Baru <span class="text-red-500">*</span></label>
                                    <input type="file" name="file_surat_final" accept=".pdf,application/pdf" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                                    <p class="text-[11px] text-gray-500 mt-1">Format wajib: PDF (.pdf), ukuran maksimal 5 MB.</p>
                                </div>
                                <div class="flex items-center gap-2 pt-1">
                                    <input type="checkbox" name="kirim_notifikasi_email" id="replace_kirim_email" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <label for="replace_kirim_email" class="text-xs text-gray-700 cursor-pointer">
                                        Kirimkan email pemberitahuan pembaruan surat ke pemohon
                                    </label>
                                </div>
                                <div class="pt-2 flex items-center gap-2">
                                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                                        <i class="ph ph-upload-simple"></i> Simpan &amp; Perbarui Berkas
                                    </button>
                                    <button type="button" @click="showReplaceForm = false" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-xs font-medium transition">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                @else
                    {{-- Belum ada Surat Final yang Diunggah --}}
                    @if($borrowing->status === \App\Models\InventoryBorrowing::STATUS_DISETUJUI)
                        <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-xl space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-blue-900">
                                    Unggah Surat Persetujuan Final (Tertandatangan)
                                </h4>
                            </div>
                            <p class="text-xs text-blue-900/90 leading-relaxed">
                                Pengajuan ini telah berstatus <strong>Disetujui</strong>. Setelah draft dicetak dan ditandatangani Camat, silakan unggah hasil scan surat final di bawah ini. Sistem akan otomatis mengirimkan email notifikasi ke pemohon bahwa surat final telah tersedia.
                            </p>

                            <form action="{{ route('admin.peminjaman_inventaris.uploadSuratFinal', $borrowing->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3 pt-1">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Surat Resmi (Opsional)</label>
                                    <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}" placeholder="Contoh: 000.1.2/012/Kec-Ckp/2026" class="w-full px-3 py-2 border rounded-lg text-xs bg-white focus:ring-2 focus:ring-blue-100 outline-none">
                                    <p class="text-[11px] text-gray-500 mt-1">Masukkan nomor surat fisik dari buku agenda Kecamatan jika ada.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Berkas Surat Persetujuan Final (PDF) <span class="text-red-500">*</span></label>
                                    <input type="file" name="file_surat_final" accept=".pdf,application/pdf" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                                    <p class="text-[11px] text-gray-500 mt-1">Format wajib: PDF (.pdf), ukuran maksimal 5 MB.</p>
                                </div>
                                <div class="pt-2">
                                    <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition flex items-center justify-center gap-2">
                                        <i class="ph ph-upload-simple text-base"></i> Unggah Surat Persetujuan Final
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-600 flex items-start gap-2.5">
                            <i class="ph ph-info text-base text-gray-500 shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold text-gray-800 block mb-0.5">Surat Final Belum Dapat Diunggah</span>
                                Sesuai prosedur administrasi, formulir upload surat persetujuan final hanya aktif apabila status pengajuan peminjaman telah disetujui (<strong>Disetujui</strong>).
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Status Update & Reminder Monitoring --}}
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4">Ubah Status</h3>
            @if($borrowing->isFinalStatus())
                <div class="p-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-600 mb-3">
                    <i class="ph ph-lock-key font-bold text-gray-500 mr-1"></i>
                    Status <strong>{{ $displayStatus }}</strong> merupakan status akhir transaksi. Status ini tidak dapat diubah kembali.
                </div>
                @if($borrowing->rejection_reason)
                <div class="text-xs text-gray-600">
                    <span class="font-medium text-gray-500 block mb-1">Alasan Penolakan / Catatan:</span>
                    <p class="text-gray-800">{{ $borrowing->rejection_reason }}</p>
                </div>
                @endif
            @else
            <form action="{{ route('admin.peminjaman_inventaris.updateStatus', $borrowing->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select name="status" required class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 outline-none">
                            @foreach(\App\Models\InventoryBorrowing::STATUSES as $s)
                            <option value="{{ $s }}" {{ $displayStatus == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Penolakan / Catatan</label>
                        <textarea name="rejection_reason" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 outline-none" placeholder="Opsional">{{ $borrowing->rejection_reason }}</textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow transition">Simpan Perubahan</button>
                </div>
            </form>
            @endif
        </div>

        {{-- Monitoring Email Reminder Pengembalian --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                <i class="ph ph-envelope-simple text-blue-600"></i> Status Reminder Email
            </h3>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-gray-500 block mb-1">Status Pengingat:</span>
                    @if($borrowing->reminder_sent_at)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            <i class="ph ph-check-circle"></i> Berhasil dikirim
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                            <i class="ph ph-clock"></i> Belum dikirim
                        </span>
                    @endif
                </div>

                <div>
                    <span class="text-gray-500 block mb-1">Waktu Pengiriman Terakhir:</span>
                    <span class="font-medium text-gray-800">
                        {{ $borrowing->reminder_sent_at ? $borrowing->reminder_sent_at->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}
                    </span>
                </div>

                <div>
                    <span class="text-gray-500 block mb-1">Status Pengembalian:</span>
                    <span class="font-medium {{ in_array($borrowing->status, ['Dikembalikan', 'Selesai']) ? 'text-emerald-700' : 'text-gray-800' }}">
                        {{ in_array($borrowing->status, ['Dikembalikan', 'Selesai']) ? 'Sudah Dikembalikan (Selesai)' : 'Belum Dikembalikan' }}
                    </span>
                </div>

                <div>
                    <span class="text-gray-500 block mb-1">Email Penerima:</span>
                    <span class="font-medium text-gray-800 break-all">
                        {{ $borrowing->user->email ?? '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
