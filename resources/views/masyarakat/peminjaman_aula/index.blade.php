@extends('layouts.masyarakat')

@section('content')
<div class="page-header-card flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-xs">
            <i class="ph ph-building text-2xl font-bold"></i>
        </div>
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight">Peminjaman Aula Kecamatan Cikampek</h1>
            <p class="text-xs sm:text-sm text-slate-600 font-medium mt-0.5">Ajukan peminjaman aula Kecamatan Cikampek untuk mendukung berbagai kegiatan masyarakat.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Kolom Kiri: Informasi Aula & Cek Ketersediaan -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Informasi Aula -->
        @if($aula)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="ph ph-info text-blue-600 text-xl"></i> Informasi Aula
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Nama Aula</p>
                        <p class="font-medium text-gray-900">{{ $aula->nama }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Lokasi</p>
                        <p class="font-medium text-gray-900">{{ $aula->lokasi }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Kapasitas</p>
                        <p class="font-medium text-gray-900">{{ $aula->kapasitas }} Orang</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Jam Operasional</p>
                        <p class="font-medium text-gray-900">{{ substr($aula->jam_buka, 0, 5) }} - {{ substr($aula->jam_tutup, 0, 5) }}</p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <p class="text-sm text-gray-500 mb-2">Fasilitas Tersedia</p>
                    <div class="flex flex-wrap gap-2">
                        @if($aula->fasilitas)
                            @foreach($aula->fasilitas as $fasilitas)
                                @if(strcasecmp(trim($fasilitas), 'toilet') !== 0)
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full border border-gray-200">
                                    {{ $fasilitas }}
                                </span>
                                @endif
                            @endforeach
                        @else
                            <span class="text-sm text-gray-500">-</span>
                        @endif
                    </div>
                </div>
                
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-100 rounded-lg">
                    <p class="text-sm font-medium text-yellow-800 mb-1">Ketentuan Penggunaan:</p>
                    <p class="text-sm text-yellow-700">{{ $aula->ketentuan ?? 'Harap menjaga kebersihan dan ketertiban selama menggunakan fasilitas.' }}</p>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
            <p class="text-gray-500">Data aula belum tersedia.</p>
        </div>
        @endif

        <!-- Cek Ketersediaan Aula & Daftar Tanggal Ter-booking -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="cekKetersediaanAula()">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <i class="ph ph-calendar-check text-emerald-600 text-xl font-bold"></i> Cek Ketersediaan Aula
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Periksa ketersediaan jam atau lihat langsung daftar tanggal yang sudah di-booking masyarakat.</p>
                </div>
                <div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1.5"
                          :class="bookedList.length ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'">
                        <i class="ph" :class="bookedList.length ? 'ph-calendar-x' : 'ph-check-circle'"></i>
                        <span x-text="bookedList.length + ' Jadwal Ter-booking'"></span>
                    </span>
                </div>
            </div>

            <div class="p-6">
                <!-- Box Daftar Tanggal yang Sudah Ter-booking -->
                <div class="mb-6 p-4 rounded-xl border" :class="bookedList.length ? 'bg-amber-50/70 border-amber-200' : 'bg-emerald-50/70 border-emerald-200'">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <i class="ph text-lg font-bold" :class="bookedList.length ? 'ph-calendar-x text-amber-600' : 'ph-check-circle text-emerald-600'"></i>
                            <h3 class="text-sm font-bold" :class="bookedList.length ? 'text-amber-950' : 'text-emerald-950'">
                                <span x-show="bookedList.length">Daftar Tanggal yang Sudah Ter-booking (Mendatang):</span>
                                <span x-show="!bookedList.length">Semua Tanggal Masih Kosong</span>
                            </h3>
                        </div>
                        <span class="text-[11px] text-gray-500 font-medium hidden sm:inline">Klik tanggal untuk cek jam</span>
                    </div>

                    <!-- Tampilan Tanggal Terbooking jika ada -->
                    <template x-if="bookedList.length">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <template x-for="item in bookedList" :key="item.id">
                                <div @click="selectDate(item.tanggal)" 
                                     class="p-3 bg-white rounded-lg border border-amber-200 hover:border-blue-500 hover:shadow-xs transition cursor-pointer flex items-center justify-between gap-3 group">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 text-xs font-bold text-gray-900 group-hover:text-blue-600 transition">
                                            <i class="ph ph-calendar-blank text-amber-600"></i>
                                            <span x-text="item.tanggal_formatted"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[11px] font-semibold px-2 py-0.5 bg-red-50 text-red-700 rounded border border-red-200 shrink-0">
                                                <i class="ph ph-clock text-[10px]"></i> <span x-text="item.jam_mulai + ' - ' + item.jam_selesai + ' WIB'"></span>
                                            </span>
                                            <span class="text-xs text-gray-600 truncate" x-text="item.nama_kegiatan"></span>
                                        </div>
                                    </div>
                                    <button type="button" class="shrink-0 text-xs px-2.5 py-1 bg-gray-100 text-gray-700 group-hover:bg-blue-600 group-hover:text-white rounded-md transition font-medium">
                                        Pilih
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Jika belum ada booking sama sekali -->
                    <template x-if="!bookedList.length">
                        <p class="text-xs text-emerald-800 leading-relaxed">
                            Belum ada jadwal peminjaman aula yang disetujui dalam waktu dekat. Anda bebas memilih tanggal dan jam mana pun sesuai jam operasional ({{ $aula ? substr($aula->jam_buka, 0, 5) : '08:00' }} - {{ $aula ? substr($aula->jam_tutup, 0, 5) : '17:00' }} WIB).
                        </p>
                    </template>
                </div>

                <!-- Form Input Cek Tanggal & Jam -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Peminjaman <span class="text-red-500">*</span>
                        </label>
                        <input type="date" x-model="tanggal" @change="hasil = null"
                               class="w-full px-4 py-2.5 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none bg-white font-medium text-gray-800 shadow-2xs">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="jam_mulai" 
                               x-init="flatpickr($el, { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, onChange: (sel, str) => { jam_mulai = str; hasil = null; } })"
                               class="w-full px-4 py-2.5 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none bg-white font-medium text-gray-800 shadow-2xs" placeholder="08:00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="jam_selesai" 
                               x-init="flatpickr($el, { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, onChange: (sel, str) => { jam_selesai = str; hasil = null; } })"
                               class="w-full px-4 py-2.5 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none bg-white font-medium text-gray-800 shadow-2xs" placeholder="12:00">
                    </div>
                </div>

                <!-- Live Info Status Tanggal Terpilih (Sebelum klik tombol Cek) -->
                <div x-show="tanggal !== ''" class="mb-4" x-cloak>
                    <template x-if="getBookingsOnDate(tanggal).length">
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900">
                            <div class="font-bold flex items-center gap-1.5 mb-1 text-amber-950">
                                <i class="ph ph-warning-circle text-amber-600 text-sm"></i>
                                Tanggal ini sudah memiliki jadwal peminjaman:
                            </div>
                            <ul class="list-disc pl-5 space-y-0.5">
                                <template x-for="b in getBookingsOnDate(tanggal)" :key="b.id">
                                    <li>
                                        <strong x-text="b.jam_mulai + ' - ' + b.jam_selesai + ' WIB'"></strong>: 
                                        <span x-text="b.nama_kegiatan"></span> (<span x-text="b.instansi"></span>)
                                    </li>
                                </template>
                            </ul>
                            <p class="mt-1.5 text-amber-800 italic">
                                💡 Anda masih dapat mengajukan peminjaman di jam lain di luar rentang waktu tersebut.
                            </p>
                        </div>
                    </template>
                    <template x-if="!getBookingsOnDate(tanggal).length">
                        <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-800 flex items-center gap-2">
                            <i class="ph ph-check-circle text-emerald-600 text-sm"></i>
                            <span>Tanggal ini masih <strong>kosong (belum ada booking)</strong>. Aula tersedia bebas sepanjang jam operasional.</span>
                        </div>
                    </template>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="cekKetersediaan" type="button" class="px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition text-sm flex items-center gap-2 shadow-xs cursor-pointer">
                        <i class="ph ph-magnifying-glass font-bold"></i> Cek Sekarang
                    </button>
                    <span class="text-xs text-gray-500">
                        Jam Operasional Aula: {{ $aula ? substr($aula->jam_buka, 0, 5) : '08:00' }} – {{ $aula ? substr($aula->jam_tutup, 0, 5) : '17:00' }} WIB
                    </span>
                </div>

                <!-- Box Hasil Pengecekan -->
                <div x-show="hasil !== null" class="mt-4" x-cloak>
                    <!-- Kasus 1: Bentrok / Tidak Tersedia -->
                    <template x-if="hasil === 'tidak_tersedia'">
                        <div class="p-4 rounded-xl border bg-red-50 border-red-200 text-red-900">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="ph ph-x-circle text-xl font-bold"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-sm text-red-950">Aula TIDAK TERSEDIA (Bentrok dengan Jadwal Lain)</h4>
                                    <p class="text-xs text-red-800 mt-1 leading-relaxed">
                                        Waktu yang Anda pilih (<span class="font-bold" x-text="jam_mulai + ' - ' + jam_selesai + ' WIB'"></span>) bertabrakan dengan jadwal yang sudah di-booking sebelumnya:
                                    </p>
                                    <div class="mt-2 p-2.5 bg-white/80 rounded-lg border border-red-200 text-xs">
                                        <template x-for="b in bentrokList" :key="b.id">
                                            <div class="flex items-center justify-between py-1">
                                                <div>
                                                    <span class="font-bold text-red-900" x-text="b.jam_mulai + ' - ' + b.jam_selesai + ' WIB'"></span>
                                                    <span class="text-gray-700"> — <span x-text="b.nama_kegiatan"></span></span>
                                                </div>
                                                <span class="px-2 py-0.5 bg-red-100 text-red-700 font-semibold rounded text-[11px]">Sudah Dibooking</span>
                                            </div>
                                        </template>
                                    </div>
                                    <p class="text-xs text-red-700 mt-2">
                                        Silakan ubah jam mulai dan selesai agar tidak bersinggungan dengan jadwal di atas, atau pilih tanggal yang lain.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Kasus 2: Tersedia dengan Catatan (Ada booking lain di tanggal yang sama tapi jam berbeda) -->
                    <template x-if="hasil === 'tersedia_dengan_catatan'">
                        <div class="p-4 rounded-xl border bg-emerald-50 border-emerald-200 text-emerald-950">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="ph ph-check-circle text-xl font-bold"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-sm text-emerald-950">Aula TERSEDIA pada Jam yang Anda Pilih!</h4>
                                    <p class="text-xs text-emerald-800 mt-1 leading-relaxed">
                                        Waktu <span class="font-bold" x-text="jam_mulai + ' - ' + jam_selesai + ' WIB'"></span> pada tanggal ini masih kosong dan dapat Anda ajukan.
                                    </p>
                                    <div class="mt-2.5 p-2.5 bg-amber-50/80 rounded-lg border border-amber-200 text-xs text-amber-900">
                                        <span class="font-bold block mb-1">Catatan Jadwal Lain di Tanggal Ini:</span>
                                        <template x-for="b in bentrokList" :key="b.id">
                                            <div class="py-0.5">
                                                • Jam <span class="font-semibold" x-text="b.jam_mulai + ' - ' + b.jam_selesai + ' WIB'"></span>: <span x-text="b.nama_kegiatan"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="mt-3">
                                        <a :href="'{{ route('masyarakat.peminjaman_aula.create') }}?tanggal=' + tanggal + '&jam_mulai=' + jam_mulai + '&jam_selesai=' + jam_selesai" 
                                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-xs">
                                            <span>Ajukan Peminjaman Jadwal Ini</span>
                                            <i class="ph ph-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Kasus 3: Tersedia Penuh -->
                    <template x-if="hasil === 'tersedia_penuh'">
                        <div class="p-4 rounded-xl border bg-emerald-50 border-emerald-200 text-emerald-950">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="ph ph-check-circle text-xl font-bold"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-sm text-emerald-950">Aula TERSEDIA PENUH!</h4>
                                    <p class="text-xs text-emerald-800 mt-1 leading-relaxed">
                                        Aula tersedia pada tanggal dan jam yang Anda pilih (<span class="font-bold" x-text="jam_mulai + ' - ' + jam_selesai + ' WIB'"></span>). Belum ada peminjaman lain pada hari tersebut.
                                    </p>
                                    <div class="mt-3">
                                        <a :href="'{{ route('masyarakat.peminjaman_aula.create') }}?tanggal=' + tanggal + '&jam_mulai=' + jam_mulai + '&jam_selesai=' + jam_selesai" 
                                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-xs">
                                            <span>Ajukan Peminjaman Jadwal Ini</span>
                                            <i class="ph ph-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Kasus 4: Error Validasi Jam -->
                    <template x-if="hasil === 'error_jam' || hasil === 'error_operasional'">
                        <div class="p-3 rounded-lg border bg-amber-50 border-amber-200 text-amber-900 text-xs flex items-center gap-2">
                            <i class="ph ph-warning-circle text-amber-600 text-lg shrink-0"></i>
                            <span x-text="pesanHasil"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <!-- Kolom Kanan: Kalender & Aksi -->
    <div class="space-y-6">
        
        <!-- Tombol Aksi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col gap-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Ingin Menggunakan Aula?</h3>
            <p class="text-sm text-gray-500 mb-4">Pastikan Anda telah mengecek ketersediaan jadwal dan membaca ketentuan penggunaan.</p>
            <a href="{{ route('masyarakat.peminjaman_aula.create') }}" class="w-full text-center px-4 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition">
                Ajukan Peminjaman
            </a>
        </div>

        <!-- Kalender Jadwal -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-md font-semibold text-gray-900 flex items-center gap-2">
                    <i class="ph ph-calendar text-gray-500 text-xl"></i> Jadwal Disetujui
                </h2>
            </div>
            <div class="p-0">
                @if($jadwal_disetujui->count() > 0)
                    <ul class="divide-y divide-gray-100">
                        @foreach($jadwal_disetujui as $jadwal)
                        <li class="p-4 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-medium text-gray-900 text-sm">
                                    {{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') }}
                                </span>
                                <span class="px-2 py-0.5 bg-red-50 text-red-700 text-xs font-medium rounded border border-red-100">
                                    Tidak Tersedia
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 flex items-center gap-1">
                                <i class="ph ph-clock"></i> {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                            </p>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="p-6 text-center text-sm text-gray-500">
                        Belum ada jadwal yang disetujui dalam waktu dekat.
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<!-- Riwayat Peminjaman -->
<div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <i class="ph ph-clock-counter-clockwise text-blue-600 text-xl"></i> Riwayat Peminjaman Saya
        </h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Pengajuan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kegiatan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($riwayat as $item)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->nomor_pengajuan }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $item->nama_kegiatan }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}<br>
                        <span class="text-xs">{{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($item->status == 'Surat Tersedia' || $item->status == 'Selesai' || $item->status == 'Disetujui')
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ $item->status_label }}</span>
                        @elseif($item->status == 'Menunggu Verifikasi' || $item->status == 'Surat Diproses' || $item->status == 'Diproses')
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $item->status_label }}</span>
                        @elseif($item->status == 'Perlu Perbaikan')
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $item->status_label }}</span>
                        @else
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ $item->status_label }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('masyarakat.peminjaman_aula.show', $item->id) }}" class="text-blue-600 hover:text-blue-900 flex items-center gap-1">
                            <i class="ph ph-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                        Anda belum pernah mengajukan peminjaman aula.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<!-- Tambahkan CSS & JS Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
function cekKetersediaanAula() {
    return {
        tanggal: '',
        jam_mulai: '',
        jam_selesai: '',
        hasil: null,
        pesanHasil: '',
        bentrokList: [],
        bookedList: @json($booked_jadwal ?? []),
        jamBuka: '{{ $aula ? substr($aula->jam_buka, 0, 5) : '08:00' }}',
        jamTutup: '{{ $aula ? substr($aula->jam_tutup, 0, 5) : '17:00' }}',

        getBookingsOnDate: function(tgl) {
            if (!tgl) return [];
            return this.bookedList.filter(function(item) {
                return item.tanggal === tgl;
            });
        },

        selectDate: function(tgl) {
            this.tanggal = tgl;
            this.hasil = null;
        },

        cekKetersediaan: function() {
            if (!this.tanggal) {
                alert('Harap pilih tanggal terlebih dahulu.');
                return;
            }
            if (!this.jam_mulai || !this.jam_selesai) {
                alert('Harap lengkapi jam mulai dan jam selesai.');
                return;
            }
            if (this.jam_selesai <= this.jam_mulai) {
                this.hasil = 'error_jam';
                this.pesanHasil = 'Jam selesai harus lebih besar daripada jam mulai.';
                this.bentrokList = [];
                return;
            }
            if (this.jam_mulai < this.jamBuka || this.jam_selesai > this.jamTutup) {
                this.hasil = 'error_operasional';
                this.pesanHasil = 'Waktu peminjaman harus berada di dalam jam operasional aula (' + this.jamBuka + ' - ' + this.jamTutup + ' WIB).';
                this.bentrokList = [];
                return;
            }

            var self = this;
            var clashes = this.bookedList.filter(function(item) {
                if (item.tanggal !== self.tanggal) return false;
                return (self.jam_mulai < item.jam_selesai && self.jam_selesai > item.jam_mulai);
            });

            if (clashes.length > 0) {
                this.hasil = 'tidak_tersedia';
                this.bentrokList = clashes;
                this.pesanHasil = 'Aula TIDAK TERSEDIA pada waktu tersebut karena sudah di-booking.';
            } else {
                var otherBookings = this.getBookingsOnDate(this.tanggal);
                if (otherBookings.length > 0) {
                    this.hasil = 'tersedia_dengan_catatan';
                    this.bentrokList = otherBookings;
                    this.pesanHasil = 'Aula TERSEDIA pada jam ' + this.jam_mulai + ' - ' + this.jam_selesai + ' WIB.';
                } else {
                    this.hasil = 'tersedia_penuh';
                    this.bentrokList = [];
                    this.pesanHasil = 'Aula TERSEDIA PENUH pada tanggal dan waktu yang dipilih.';
                }
            }
        }
    };
}
</script>

@endsection
