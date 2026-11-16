@extends('layouts.masyarakat')
@section('title', 'Detail Pengajuan Sertifikat / Piagam')

@section('content')
<div x-data="{ showEmailModal: {{ session('success') ? 'true' : 'false' }} }">
    <!-- Pop Up Modal: Cek Email & Folder Spam -->
    <div x-cloak x-show="showEmailModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-slate-900/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         role="dialog" aria-modal="true" aria-labelledby="modal-headline">
        
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-amber-200 overflow-hidden text-left"
             @click.away="showEmailModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <!-- Decorative Top Gradient Line -->
            <div class="h-2 bg-gradient-to-r from-amber-500 via-purple-500 to-blue-500"></div>

            <!-- Close Button -->
            <button @click="showEmailModal = false" type="button" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 transition cursor-pointer"
                    aria-label="Tutup pemberitahuan">
                <i class="ph ph-x text-xl"></i>
            </button>

            <div class="p-6 sm:p-7">
                <!-- Header Icon & Title -->
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 shadow-inner">
                        <i class="ph ph-envelope-open text-2xl font-bold"></i>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300/60 mb-1">
                            <i class="ph-fill ph-bell-ringing"></i> Pemberitahuan Penting
                        </span>
                        <h3 id="modal-headline" class="text-lg font-bold text-gray-900 leading-snug">
                            Cek Email &amp; Periksa Folder Spam Anda
                        </h3>
                    </div>
                </div>

                <!-- Explanation & Guide -->
                <div class="space-y-3.5 text-sm text-gray-600">
                    <p>
                        Pengajuan permohonan sertifikat/piagam dengan kode <strong class="text-gray-900">{{ $certRequest->request_code }}</strong> telah berhasil terkirim dan tersimpan di sistem.
                    </p>

                    <div class="p-3.5 bg-blue-50/80 rounded-xl border border-blue-100 text-xs text-blue-900 flex items-start gap-2.5">
                        <i class="ph ph-paper-plane-tilt text-blue-600 text-base shrink-0 mt-0.5"></i>
                        <div>
                            Pemberitahuan status verifikasi serta berkas resmi <strong>Sertifikat / Piagam (PDF)</strong> akan dikirimkan otomatis ke alamat email:
                            <span class="font-bold text-blue-700 block mt-0.5">{{ Auth::user()->email ?? $certRequest->user->email }}</span>
                        </div>
                    </div>

                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 text-amber-950">
                        <div class="flex items-start gap-2.5">
                            <i class="ph ph-warning-circle text-amber-600 text-lg shrink-0 mt-0.5"></i>
                            <div class="text-xs sm:text-sm">
                                <span class="font-bold block text-amber-900 mb-1">Penting: Periksa Bagian / Folder Spam!</span>
                                <p class="leading-relaxed text-amber-900/90 text-xs">
                                    Email notifikasi dari sistem kecamatan terkadang disaring dan masuk ke dalam folder <strong>Spam</strong> atau <strong>Junk</strong> oleh penyedia email (Gmail, Yahoo, dll.).
                                    Apabila Anda tidak menemukan email pemberitahuan di <strong>Kotak Masuk (Inbox)</strong>, silakan periksa folder <strong>Spam</strong> Anda lalu pilih <strong>"Laporkan Bukan Spam"</strong> (Not Spam) agar surat / berkas sertifikat dan informasi berikutnya dapat masuk ke kotak masuk utama.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dismiss Button -->
                <div class="mt-6">
                    <button type="button" @click="showEmailModal = false"
                            class="w-full py-2.5 px-4 bg-purple-600 hover:bg-purple-700 active:scale-[0.99] text-white font-semibold text-sm rounded-xl shadow-sm transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="ph ph-check-circle text-lg"></i> Mengerti, Saya Paham
                    </button>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Informasi Notifikasi Email & Folder Spam -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900 shadow-xs mb-6">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-amber-100 text-amber-700 rounded-xl flex items-center justify-center shrink-0">
                <i class="ph ph-envelope-simple text-2xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <h3 class="font-bold text-amber-950 text-sm sm:text-base flex items-center gap-1.5">
                        <i class="ph ph-bell-simple-ringing text-amber-600"></i> Informasi Pengecekan Email &amp; Folder Spam
                    </h3>
                    <button type="button" @click="showEmailModal = true" class="text-xs font-semibold text-amber-700 hover:text-amber-900 underline flex items-center gap-1 cursor-pointer">
                        <i class="ph ph-arrows-out-simple"></i> Buka Pop-up Petunjuk
                    </button>
                </div>
                <p class="text-xs sm:text-sm text-amber-900/90 mt-1.5 leading-relaxed">
                    Informasi status verifikasi dan berkas resmi sertifikat/piagam dikirimkan ke email terdaftar Anda (<strong>{{ $certRequest->user->email ?? Auth::user()->email }}</strong>). Apabila tidak ada di Kotak Masuk (Inbox), mohon periksa bagian / folder <strong>Spam</strong> atau <strong>Junk</strong>.
                </p>
                <div class="mt-2.5 p-3 bg-white/80 rounded-lg border border-amber-200/80 text-xs text-amber-900 flex items-start gap-2">
                    <i class="ph ph-info text-amber-600 text-base shrink-0 mt-0.5"></i>
                    <span>
                        <strong>Perhatian:</strong> Jika tidak menemukan email di Kotak Masuk (Inbox), mohon periksa bagian / folder <strong>Spam</strong> pada email Anda, kemudian pilih <em>"Bukan Spam"</em> agar pembaruan berikutnya dapat diterima langsung.
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Info --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Area Download jika sertifikat final sudah diunggah oleh admin --}}
            @if($certRequest->file_sertifikat_final || ($certRequest->files && $certRequest->files->count() > 0))
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl border border-emerald-200 p-6 shadow-xs">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center flex-shrink-0 text-emerald-600">
                        <i class="ph ph-certificate text-3xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-emerald-950">{{ $certRequest->document_type }} Anda Telah Tersedia!</h3>
                        <p class="text-xs text-emerald-700 mt-0.5 leading-relaxed">
                            Sertifikat/piagam final telah diunggah oleh pihak Kecamatan Cikampek. Silakan unduh dokumen Anda di bawah ini:
                        </p>
                        <p class="text-xs text-emerald-800 mt-2 flex items-center gap-1.5">
                            <i class="ph ph-envelope-check text-base shrink-0"></i>
                            <span>Berkas dan pemberitahuan sertifikat juga telah dikirim ke email Anda. Cek Kotak Masuk atau folder <strong>Spam</strong> jika belum terlihat.</span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    @if($certRequest->file_sertifikat_final)
                    <a href="{{ route('masyarakat.sertifikat.download_final', $certRequest->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-sm transition">
                        <i class="ph ph-download-simple text-lg"></i> Unduh Berkas Final (TTD/Cap)
                    </a>
                    @elseif($certRequest->files && $certRequest->files->count() > 0)
                    <a href="{{ route('masyarakat.sertifikat.download', $certRequest->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-sm transition">
                        <i class="ph ph-download-simple text-lg"></i> Unduh {{ $certRequest->document_type }}
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Detail Informasi Pengajuan --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="ph ph-info text-purple-600"></i> Informasi Pengajuan
                    </h2>
                    @php
                        $bc = 'bg-gray-100 text-gray-800';
                        if (in_array($certRequest->status, ['Selesai', 'Sertifikat Tersedia'])) $bc = 'bg-emerald-100 text-emerald-800';
                        elseif (in_array($certRequest->status, ['Diproses','Disetujui'])) $bc = 'bg-blue-100 text-blue-800';
                        elseif ($certRequest->status == 'Ditolak') $bc = 'bg-rose-100 text-rose-800';
                    @endphp
                    <span class="px-3 py-1 text-xs font-bold rounded-full {{ $bc }}">{{ $certRequest->status }}</span>
                </div>
                <div class="p-6">
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
                            <dt class="text-gray-500 text-xs font-medium">Nama Pemohon</dt>
                            <dd class="text-gray-900 font-medium mt-0.5">{{ $certRequest->applicant_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs font-medium">Nomor HP / WhatsApp</dt>
                            <dd class="text-gray-900 mt-0.5">{{ $certRequest->phone ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs font-medium">Nama Kegiatan</dt>
                            <dd class="text-gray-900 font-semibold mt-0.5">{{ $certRequest->activity_name }}</dd>
                        </div>
                        @if($certRequest->activity_theme)
                        <div>
                            <dt class="text-gray-500 text-xs font-medium">Tema / Judul Kegiatan</dt>
                            <dd class="text-gray-900 mt-0.5">{{ $certRequest->activity_theme }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-gray-500 text-xs font-medium">Tanggal Kegiatan</dt>
                            <dd class="text-gray-900 mt-0.5">{{ $certRequest->activity_date->translatedFormat('d F Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs font-medium">Tempat Kegiatan</dt>
                            <dd class="text-gray-900 mt-0.5">{{ $certRequest->activity_place }}</dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-gray-500 text-xs font-medium">Keperluan</dt>
                            <dd class="text-gray-900 mt-0.5">{{ $certRequest->purpose }}</dd>
                        </div>

                        @if($certRequest->description)
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500 text-xs font-medium">Keterangan Tambahan</dt>
                            <dd class="text-gray-900 mt-0.5">{{ $certRequest->description }}</dd>
                        </div>
                        @endif

                        @if($certRequest->rejection_reason)
                        <div class="sm:col-span-2 bg-red-50 p-3.5 rounded-xl border border-red-200">
                            <dt class="text-red-700 text-xs font-bold">Alasan Penolakan</dt>
                            <dd class="text-red-800 text-sm mt-0.5">{{ $certRequest->rejection_reason }}</dd>
                            <p class="text-xs text-red-600 mt-2 flex items-center gap-1.5">
                                <i class="ph ph-envelope-simple text-sm shrink-0"></i>
                                <span>Pemberitahuan resmi penolakan juga dikirimkan ke email Anda. Periksa folder <strong>Spam</strong> jika tidak ada di Kotak Masuk.</span>
                            </p>
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
</div>
@endsection
