@extends('layouts.masyarakat')
@section('title', 'Detail Peminjaman Inventaris')

@section('content')
<div x-data="{ showEmailModal: false }">
    <!-- Pop Up Modal: Petunjuk Email & Folder Spam -->
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
            <div class="h-2 bg-gradient-to-r from-amber-500 via-indigo-500 to-blue-500"></div>

            <!-- Close Button -->
            <button @click="showEmailModal = false" type="button" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 transition cursor-pointer"
                    aria-label="Tutup petunjuk">
                <i class="ph ph-x text-xl"></i>
            </button>

            <div class="p-6 sm:p-7">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 shadow-inner">
                        <i class="ph ph-envelope-open text-2xl font-bold"></i>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300/60 mb-1">
                            <i class="ph-fill ph-bell-ringing"></i> Petunjuk Notifikasi Email
                        </span>
                        <h3 id="modal-headline" class="text-lg font-bold text-gray-900 leading-snug">
                            Cek Email &amp; Periksa Folder Spam Anda
                        </h3>
                    </div>
                </div>

                <div class="space-y-3.5 text-sm text-gray-600">
                    <p>
                        Informasi pembaruan status permohonan peminjaman inventaris dengan kode <strong class="text-gray-900">{{ $borrowing->request_code }}</strong> dikirimkan ke email terdaftar Anda:
                    </p>

                    <div class="p-3.5 bg-blue-50/80 rounded-xl border border-blue-100 text-xs text-blue-900 flex items-start gap-2.5">
                        <i class="ph ph-paper-plane-tilt text-blue-600 text-base shrink-0 mt-0.5"></i>
                        <div>
                            Alamat email terdaftar:
                            <span class="font-bold text-blue-700 block mt-0.5">{{ $borrowing->user->email ?? Auth::user()->email }}</span>
                        </div>
                    </div>

                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 text-amber-950">
                        <div class="flex items-start gap-2.5">
                            <i class="ph ph-warning-circle text-amber-600 text-lg shrink-0 mt-0.5"></i>
                            <div class="text-xs sm:text-sm">
                                <span class="font-bold block text-amber-900 mb-1">Penting: Periksa Bagian / Folder Spam!</span>
                                <p class="leading-relaxed text-amber-900/90 text-xs">
                                    Email notifikasi dari sistem kecamatan terkadang disaring dan masuk ke dalam folder <strong>Spam</strong> atau <strong>Junk</strong> oleh penyedia email (Gmail, Yahoo, dll.).
                                    Apabila Anda tidak menemukan email pemberitahuan di <strong>Kotak Masuk (Inbox)</strong>, silakan periksa folder <strong>Spam</strong> Anda lalu pilih <strong>"Laporkan Bukan Spam"</strong> (Not Spam) agar pembaruan berikutnya dapat diterima langsung.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="button" @click="showEmailModal = false"
                            class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.99] text-white font-semibold text-sm rounded-xl shadow-sm transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="ph ph-check-circle text-lg"></i> Mengerti, Saya Paham
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6">
        <a href="{{ route('masyarakat.peminjaman_inventaris.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
            <i class="ph ph-arrow-left"></i> Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Detail Peminjaman Inventaris</h1>
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
                        <i class="ph ph-bell-simple-ringing text-amber-600"></i> Pemberitahuan Email &amp; Folder Spam
                    </h3>
                    <button type="button" @click="showEmailModal = true" class="text-xs font-semibold text-amber-700 hover:text-amber-900 underline flex items-center gap-1 cursor-pointer">
                        <i class="ph ph-arrows-out-simple"></i> Buka Petunjuk Email
                    </button>
                </div>
                <p class="text-xs sm:text-sm text-amber-900/90 mt-1 leading-relaxed">
                    Informasi status verifikasi dan pengingat pengembalian barang inventaris dikirimkan ke email terdaftar Anda (<strong>{{ $borrowing->user->email ?? Auth::user()->email }}</strong>). Apabila tidak ada di Kotak Masuk (Inbox), mohon periksa bagian / folder <strong>Spam</strong> atau <strong>Junk</strong>.
                </p>
                <div class="mt-2.5 p-2.5 bg-white/80 rounded-lg border border-amber-200/80 text-xs text-amber-900 flex items-start gap-2">
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
            {{-- Box Notifikasi Status Disetujui --}}
            @if(in_array($borrowing->status, [\App\Models\InventoryBorrowing::STATUS_DISETUJUI, \App\Models\InventoryBorrowing::STATUS_SEDANG_DIPINJAM]))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs sm:text-sm text-emerald-900 shadow-xs">
                <div class="flex items-center gap-2 font-bold mb-1 text-emerald-950">
                    <i class="ph ph-check-circle text-emerald-600 text-xl"></i>
                    <span>Pengajuan Peminjaman Disetujui</span>
                </div>
                <p class="text-xs text-emerald-800 leading-relaxed">
                    Peminjaman barang telah disetujui. Informasi tata cara pengambilan dan pengembalian barang juga dikirimkan ke email Anda.
                </p>
                <p class="text-xs text-emerald-700 mt-2 flex items-center gap-1.5">
                    <i class="ph ph-envelope-check text-base shrink-0"></i>
                    <span>Cek Kotak Masuk atau folder <strong>Spam</strong> jika email belum terlihat.</span>
                </p>
            </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900">Informasi Pengajuan</h2>
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
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div><dt class="text-gray-500 font-medium">Kode Pengajuan</dt><dd class="text-gray-900 font-bold mt-0.5">{{ $borrowing->request_code }}</dd></div>
                        <div><dt class="text-gray-500 font-medium">Nama Pemohon</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->applicant_name }}</dd></div>
                        <div><dt class="text-gray-500 font-medium">No. HP</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->phone ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500 font-medium">Instansi</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->institution ?? '-' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500 font-medium">Penanggung Jawab</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->penanggung_jawab }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500 font-medium">Keperluan</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->purpose }}</dd></div>
                        <div>
                            <dt class="text-gray-500 font-medium">Tanggal Peminjaman</dt>
                            <dd class="text-gray-900 mt-0.5 font-medium">{{ $borrowing->borrow_date->translatedFormat('d F Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 font-medium">Jam Peminjaman</dt>
                            <dd class="text-gray-900 mt-0.5 font-medium">{{ $borrowing->borrowing_time ? \Carbon\Carbon::parse($borrowing->borrowing_time)->format('H:i') . ' WIB' : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 font-medium">Tanggal Pengembalian</dt>
                            <dd class="text-gray-900 mt-0.5 font-medium">{{ $borrowing->return_date->translatedFormat('d F Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 font-medium">Jam Pengembalian</dt>
                            <dd class="text-gray-900 mt-0.5 font-medium">{{ $borrowing->return_time ? \Carbon\Carbon::parse($borrowing->return_time)->format('H:i') . ' WIB' : '-' }}</dd>
                        </div>
                        @if($borrowing->notes)
                        <div class="sm:col-span-2"><dt class="text-gray-500 font-medium">Catatan</dt><dd class="text-gray-900 mt-0.5">{{ $borrowing->notes }}</dd></div>
                        @endif
                        @if($borrowing->rejection_reason)
                        <div class="sm:col-span-2 bg-red-50 p-3.5 rounded-xl border border-red-200">
                            <dt class="text-red-700 font-bold">Alasan Penolakan</dt>
                            <dd class="text-red-700 mt-0.5">{{ $borrowing->rejection_reason }}</dd>
                            <p class="text-xs text-red-600 mt-2 flex items-center gap-1.5">
                                <i class="ph ph-envelope-simple text-sm shrink-0"></i>
                                <span>Pemberitahuan resmi penolakan juga dikirimkan ke email Anda. Periksa folder <strong>Spam</strong> jika tidak ada di Kotak Masuk.</span>
                            </p>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Daftar Barang --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-sm font-bold text-gray-900">Barang yang Dipinjam</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($borrowing->details as $detail)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 flex items-center gap-2">
                                    <i class="ph ph-package text-indigo-400"></i> {{ $detail->item->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-semibold">{{ $detail->quantity }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Side Info --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Timeline Status</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center"><i class="ph ph-paper-plane-tilt text-indigo-600 text-sm"></i></div>
                        <div>
                            <p class="text-xs font-bold text-gray-800">Pengajuan Masuk</p>
                            <p class="text-[11px] text-gray-400">{{ $borrowing->created_at->translatedFormat('d F Y, H:i') . ' WIB' }}</p>
                        </div>
                    </div>
                    @if($borrowing->updated_at != $borrowing->created_at)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center"><i class="ph ph-arrows-clockwise text-blue-600 text-sm"></i></div>
                        <div>
                            <p class="text-xs font-bold text-gray-800">{{ $borrowing->status_label }}</p>
                            <p class="text-[11px] text-gray-400">{{ $borrowing->updated_at->translatedFormat('d F Y, H:i') . ' WIB' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($borrowing->application_file)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Dokumen</h3>
                <a href="{{ asset($borrowing->application_file) }}" target="_blank" class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800">
                    <i class="ph ph-file-pdf text-lg"></i> Surat Permohonan
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
