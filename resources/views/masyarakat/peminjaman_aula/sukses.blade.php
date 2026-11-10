@extends('layouts.masyarakat')

@section('content')
<div x-data="{ showEmailModal: true }" class="max-w-2xl mx-auto mt-6 mb-12">
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
            <div class="h-2 bg-gradient-to-r from-amber-500 via-emerald-500 to-blue-500"></div>

            <!-- Close Button -->
            <button @click="showEmailModal = false" type="button" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 transition"
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
                        Pengajuan peminjaman aula dengan nomor <strong class="text-gray-900">{{ $peminjaman->nomor_pengajuan }}</strong> telah berhasil terkirim dan tersimpan di sistem.
                    </p>

                    <div class="p-3.5 bg-blue-50/80 rounded-xl border border-blue-100 text-xs text-blue-900 flex items-start gap-2.5">
                        <i class="ph ph-paper-plane-tilt text-blue-600 text-base shrink-0 mt-0.5"></i>
                        <div>
                            Pemberitahuan verifikasi dan berkas resmi <strong>Surat Izin Peminjaman Aula (PDF)</strong> akan dikirimkan otomatis ke alamat email:
                            <span class="font-bold text-blue-700 block mt-0.5">{{ Auth::user()->email ?? $peminjaman->user->email }}</span>
                        </div>
                    </div>

                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 text-amber-950">
                        <div class="flex items-start gap-2.5">
                            <i class="ph ph-warning-circle text-amber-600 text-lg shrink-0 mt-0.5"></i>
                            <div class="text-xs sm:text-sm">
                                <span class="font-bold block text-amber-900 mb-1">Penting: Periksa Bagian / Folder Spam!</span>
                                <p class="leading-relaxed text-amber-900/90 text-xs">
                                    Email notifikasi dari sistem kecamatan terkadang disaring dan masuk ke dalam folder <strong>Spam</strong> atau <strong>Junk</strong> oleh penyedia email (Gmail, Yahoo, dll.).
                                    Apabila Anda tidak menemukan email pemberitahuan di <strong>Kotak Masuk (Inbox)</strong>, silakan periksa folder <strong>Spam</strong> Anda lalu pilih <strong>"Laporkan Bukan Spam"</strong> (Not Spam) agar surat izin dan informasi berikutnya dapat masuk ke kotak masuk utama.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dismiss Button -->
                <div class="mt-6">
                    <button type="button" @click="showEmailModal = false"
                            class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-semibold text-sm rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                        <i class="ph ph-check-circle text-lg"></i> Mengerti, Saya Paham
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Success Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden text-center p-6 sm:p-8">
        <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="ph ph-check-circle text-4xl"></i>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Pengajuan Peminjaman Aula Berhasil</h1>
        <p class="text-gray-500 mb-6">Tunggu proses verifikasi dari petugas Kecamatan Cikampek. Anda dapat memantau status pengajuan pada menu Riwayat Peminjaman.</p>

        <!-- Pemberitahuan Permanen di Halaman Mengenai Email & Spam -->
        <div class="bg-amber-50/90 rounded-xl p-5 mb-8 text-left border border-amber-200 shadow-xs">
            <div class="flex items-start gap-3.5">
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
                        Surat Izin Final dan pemberitahuan verifikasi akan dikirimkan ke email Anda: <strong>{{ Auth::user()->email ?? $peminjaman->user->email }}</strong>.
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
        
        <!-- Ringkasan Pengajuan -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left border border-gray-100">
            <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
                <div class="col-span-2 pb-3 border-b border-gray-200 flex justify-between items-center">
                    <span class="text-gray-500">Nomor Pengajuan</span>
                    <span class="font-bold text-gray-900 text-lg">{{ $peminjaman->nomor_pengajuan }}</span>
                </div>
                
                <div>
                    <span class="text-gray-500 block mb-1">Nama Kegiatan</span>
                    <span class="font-medium text-gray-900">{{ $peminjaman->nama_kegiatan }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block mb-1">Status</span>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $peminjaman->status }}
                    </span>
                </div>
                
                <div>
                    <span class="text-gray-500 block mb-1">Tanggal Kegiatan</span>
                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($peminjaman->tanggal)->translatedFormat('d F Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block mb-1">Waktu Kegiatan</span>
                    <span class="font-medium text-gray-900">{{ substr($peminjaman->jam_mulai, 0, 5) }} - {{ substr($peminjaman->jam_selesai, 0, 5) }}</span>
                </div>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="flex justify-center gap-4">
            <a href="{{ route('masyarakat.peminjaman_aula.index') }}" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                Kembali
            </a>
            <a href="{{ route('masyarakat.peminjaman_aula.show', $peminjaman->id) }}" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition">
                Lihat Detail Pengajuan
            </a>
        </div>
    </div>
</div>
@endsection
