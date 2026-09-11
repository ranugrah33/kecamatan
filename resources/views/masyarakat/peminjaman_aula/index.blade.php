@extends('layouts.masyarakat')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Peminjaman Aula Kecamatan Cikampek</h1>
    <p class="text-sm text-gray-500 mt-1">Ajukan peminjaman aula Kecamatan Cikampek untuk mendukung berbagai kegiatan masyarakat.</p>
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
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full border border-gray-200">
                                    {{ $fasilitas }}
                                </span>
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

        <!-- Cek Ketersediaan -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="{
            tanggal: '',
            jam_mulai: '',
            jam_selesai: '',
            hasil: null,
            cekKetersediaan() {
                if(!this.tanggal || !this.jam_mulai || !this.jam_selesai) {
                    alert('Harap lengkapi tanggal dan jam');
                    return;
                }
                // Simulasi cek ketersediaan
                this.hasil = 'Tersedia'; // Placeholder untuk AJAX nanti
            }
        }">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="ph ph-calendar-check text-green-600 text-xl"></i> Cek Ketersediaan Aula
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" x-model="tanggal" class="w-full px-4 py-2 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai</label>
                        <input type="text" x-model="jam_mulai" 
                               x-init="flatpickr($el, { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, onChange: (sel, str) => { jam_mulai = str; } })"
                               class="w-full px-4 py-2 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" placeholder="--:--">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai</label>
                        <input type="text" x-model="jam_selesai" 
                               x-init="flatpickr($el, { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, onChange: (sel, str) => { jam_selesai = str; } })"
                               class="w-full px-4 py-2 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" placeholder="--:--">
                    </div>
                </div>
                <button @click="cekKetersediaan" type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition text-sm mb-4">
                    Cek Sekarang
                </button>
                
                <div x-show="hasil !== null" class="mt-2 p-3 rounded-lg border text-sm" 
                     :class="hasil === 'Tersedia' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'" x-cloak>
                    <i class="ph" :class="hasil === 'Tersedia' ? 'ph-check-circle' : 'ph-x-circle'"></i>
                    <span x-text="hasil === 'Tersedia' ? 'Aula tersedia pada tanggal dan waktu yang dipilih.' : 'Aula tidak tersedia pada tanggal dan waktu tersebut.'"></span>
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
                        @if($item->status == 'Disetujui' || $item->status == 'Selesai')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ $item->status }}</span>
                        @elseif($item->status == 'Menunggu Verifikasi' || $item->status == 'Diproses')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $item->status }}</span>
                        @elseif($item->status == 'Perlu Perbaikan')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $item->status }}</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ $item->status }}</span>
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

@endsection
