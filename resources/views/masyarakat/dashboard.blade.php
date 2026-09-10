@extends('layouts.masyarakat')
@section('title', 'Beranda')

@section('content')

{{-- Two-column layout: Left content + Right panel --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ===== LEFT COLUMN ===== --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Welcome Card --}}
        <div class="welcome-card rounded-2xl p-6 border border-blue-100">
            <div class="flex flex-col sm:flex-row items-start gap-4 relative z-10">
                <div class="flex-1">
                    <p class="text-blue-600 text-sm font-medium">Selamat datang,</p>
                    <h1 class="text-2xl font-bold text-gray-900 mt-0.5">Halo, {{ Auth::user()->name }} 👋</h1>
                    <p class="text-gray-600 text-sm mt-2 leading-relaxed max-w-sm">
                        Nikmati layanan publik Kecamatan Cikampek dengan lebih mudah,<br class="hidden sm:block"> cepat dan transparan.
                    </p>
                </div>
                {{-- Date & Time Card --}}
                <div class="flex-shrink-0 bg-white/80 backdrop-blur-sm rounded-xl px-4 py-3 shadow-sm border border-blue-100/60 min-w-[190px]">
                    <div class="flex items-center gap-2">
                        <i class="ph ph-calendar-blank text-blue-500 text-sm"></i>
                        <span class="text-xs text-gray-500 font-medium" id="current-date">Senin, 15 September 2025</span>
                    </div>
                    <p class="text-xl font-bold text-gray-800 mt-1" id="current-time">13:24 WIB</p>
                </div>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">

            {{-- Total Pengajuan --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center mb-3">
                    <i class="ph ph-files text-xl text-blue-500"></i>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $total_pengajuan }}</p>
                <p class="text-xs text-gray-500 mt-0.5 font-medium">Total Pengajuan</p>
                <a href="{{ route('masyarakat.riwayat') }}" class="mt-2 text-xs text-blue-500 flex items-center gap-0.5 hover:text-blue-700 transition font-medium">
                    Lihat semua <i class="ph ph-arrow-right text-xs"></i>
                </a>
            </div>

            {{-- Sedang Diproses --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center mb-3">
                    <i class="ph ph-arrow-clockwise text-xl text-orange-500"></i>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $diproses }}</p>
                <p class="text-xs text-gray-500 mt-0.5 font-medium">Sedang Diproses</p>
                <a href="{{ route('masyarakat.riwayat') }}" class="mt-2 text-xs text-blue-500 flex items-center gap-0.5 hover:text-blue-700 transition font-medium">
                    Lihat semua <i class="ph ph-arrow-right text-xs"></i>
                </a>
            </div>

            {{-- Selesai --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mb-3">
                    <i class="ph ph-check-circle text-xl text-green-500"></i>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $selesai }}</p>
                <p class="text-xs text-gray-500 mt-0.5 font-medium">Selesai</p>
                <a href="{{ route('masyarakat.riwayat') }}" class="mt-2 text-xs text-blue-500 flex items-center gap-0.5 hover:text-blue-700 transition font-medium">
                    Lihat semua <i class="ph ph-arrow-right text-xs"></i>
                </a>
            </div>

            {{-- Perlu Diperbaiki --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center mb-3">
                    <i class="ph ph-warning-circle text-xl text-red-500"></i>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $perlu_diperbaiki }}</p>
                <p class="text-xs text-gray-500 mt-0.5 font-medium">Perlu Diperbaiki</p>
                <a href="{{ route('masyarakat.riwayat') }}" class="mt-2 text-xs text-blue-500 flex items-center gap-0.5 hover:text-blue-700 transition font-medium">
                    Lihat semua <i class="ph ph-arrow-right text-xs"></i>
                </a>
            </div>

        </div>

        {{-- Layanan Populer --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Layanan Populer</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Layanan yang paling sering digunakan masyarakat</p>
                </div>
                <a href="{{ route('masyarakat.layanan') }}" class="text-xs text-blue-500 font-medium hover:text-blue-700 flex items-center gap-1 transition">
                    Lihat Semua Layanan <i class="ph ph-arrow-right text-xs"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

                {{-- Peminjaman Aula --}}
                <div class="border border-gray-100 rounded-xl p-4 hover:border-blue-200 hover:shadow-sm transition-all group cursor-pointer">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-100 transition">
                        <i class="ph ph-door-open text-xl text-blue-500"></i>
                    </div>
                    <h3 class="text-xs font-semibold text-gray-800 leading-tight">Peminjaman Aula</h3>
                    <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">Ajukan peminjaman aula untuk kegiatan Anda</p>
                    <div class="mt-3 flex items-center justify-end">
                        <a href="{{ route('masyarakat.peminjaman_aula.index') }}"
                           class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-500 transition">
                            <i class="ph ph-arrow-right text-xs text-blue-500 group-hover:text-white transition"></i>
                        </a>
                    </div>
                </div>

                {{-- Arsip UMKM --}}
                <div class="border border-gray-100 rounded-xl p-4 hover:border-green-200 hover:shadow-sm transition-all group cursor-pointer">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mb-3 group-hover:bg-green-100 transition">
                        <i class="ph ph-storefront text-xl text-green-500"></i>
                    </div>
                    <h3 class="text-xs font-semibold text-gray-800 leading-tight">Arsip UMKM</h3>
                    <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">Lihat data dan informasi UMKM di wilayah Cikampek</p>
                    <div class="mt-3 flex items-center justify-end">
                        <div class="w-6 h-6 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-500 transition">
                            <i class="ph ph-arrow-right text-xs text-green-500 group-hover:text-white transition"></i>
                        </div>
                    </div>
                </div>

                {{-- Bantuan Sosial --}}
                <div class="border border-gray-100 rounded-xl p-4 hover:border-yellow-200 hover:shadow-sm transition-all group cursor-pointer">
                    <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center mb-3 group-hover:bg-yellow-100 transition">
                        <i class="ph ph-hand-heart text-xl text-yellow-500"></i>
                    </div>
                    <h3 class="text-xs font-semibold text-gray-800 leading-tight">Bantuan Sosial</h3>
                    <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">Cek informasi bantuan sosial yang tersedia</p>
                    <div class="mt-3 flex items-center justify-end">
                        <div class="w-6 h-6 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-500 transition">
                            <i class="ph ph-arrow-right text-xs text-yellow-500 group-hover:text-white transition"></i>
                        </div>
                    </div>
                </div>

                {{-- Jual Beli Tanah --}}
                <div class="border border-gray-100 rounded-xl p-4 hover:border-purple-200 hover:shadow-sm transition-all group cursor-pointer">
                    <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center mb-3 group-hover:bg-purple-100 transition">
                        <i class="ph ph-map-trifold text-xl text-purple-500"></i>
                    </div>
                    <h3 class="text-xs font-semibold text-gray-800 leading-tight">Jual Beli Tanah</h3>
                    <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">Informasi dan layanan jual beli tanah</p>
                    <div class="mt-3 flex items-center justify-end">
                        <div class="w-6 h-6 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-500 transition">
                            <i class="ph ph-arrow-right text-xs text-purple-500 group-hover:text-white transition"></i>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>{{-- end left column --}}

    {{-- ===== RIGHT COLUMN ===== --}}
    <div class="space-y-5">

        {{-- Notifikasi Terbaru --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Notifikasi Terbaru</h2>
                <a href="{{ route('masyarakat.notifikasi') }}" class="text-xs text-blue-500 font-medium hover:text-blue-700 flex items-center gap-1 transition">
                    Lihat Semua <i class="ph ph-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="divide-y divide-gray-50">

                @php
                $notifDisplay = [
                    ['icon' => 'ph-check-circle', 'color' => 'green', 'msg' => 'Pengajuan Peminjaman Aula disetujui', 'time' => 'Pada 11 Sep 2025, 11:45'],
                    ['icon' => 'ph-info', 'color' => 'blue', 'msg' => 'Dokumen Anda sedang diverifikasi', 'time' => 'Pada 08 Sep 2025, 15:10'],
                    ['icon' => 'ph-bell-ringing', 'color' => 'yellow', 'msg' => 'Jadwal pelayanan berubah', 'time' => 'Pada 05 Sep 2025, 08:20'],
                ];
                @endphp

                @foreach($notifDisplay as $n)
                <div class="px-5 py-4 flex items-start gap-3 hover:bg-gray-50 transition cursor-pointer">
                    <div class="w-8 h-8 bg-{{ $n['color'] }}-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="ph {{ $n['icon'] }} text-{{ $n['color'] }}-500 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-800 leading-relaxed">{{ $n['msg'] }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $n['time'] }}</p>
                    </div>
                    <i class="ph ph-caret-right text-gray-300 text-sm flex-shrink-0 mt-1"></i>
                </div>
                @endforeach

            </div>
        </div>

        {{-- Riwayat Pengajuan Terbaru --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Riwayat Pengajuan Terbaru</h2>
                <a href="{{ route('masyarakat.riwayat') }}" class="text-xs text-blue-500 font-medium hover:text-blue-700 flex items-center gap-1 transition">
                    Lihat Semua <i class="ph ph-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="divide-y divide-gray-50">

                @php
                $riwayatDisplay = [
                    ['icon' => 'ph-door-open', 'iconBg' => 'blue', 'jenis' => 'Peminjaman Aula', 'tgl' => '11 Sep 2025, 10:30', 'status' => 'Diproses', 'statusColor' => 'blue', 'route' => 'masyarakat.riwayat'],
                    ['icon' => 'ph-storefront', 'iconBg' => 'green', 'jenis' => 'Arsip UMKM', 'tgl' => '08 Sep 2025, 14:20', 'status' => 'Selesai', 'statusColor' => 'green', 'route' => 'masyarakat.riwayat'],
                    ['icon' => 'ph-hand-heart', 'iconBg' => 'yellow', 'jenis' => 'Bantuan Sosial', 'tgl' => '05 Sep 2025, 09:15', 'status' => 'Diterima', 'statusColor' => 'yellow', 'route' => 'masyarakat.riwayat'],
                ];
                @endphp

                @foreach($riwayatDisplay as $r)
                <a href="{{ route($r['route']) }}" class="px-5 py-4 flex items-center gap-3 hover:bg-gray-50 transition">
                    <div class="w-8 h-8 bg-{{ $r['iconBg'] }}-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="ph {{ $r['icon'] }} text-{{ $r['iconBg'] }}-400 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-800">{{ $r['jenis'] }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $r['tgl'] }}</p>
                    </div>
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full
                        {{ $r['statusColor'] === 'green' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $r['statusColor'] === 'blue' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $r['statusColor'] === 'yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        flex-shrink-0 whitespace-nowrap">{{ $r['status'] }}</span>
                    <i class="ph ph-caret-right text-gray-300 text-sm flex-shrink-0"></i>
                </a>
                @endforeach

            </div>
        </div>

    </div>{{-- end right column --}}

</div>

<script>
(function() {
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    function updateTime() {
        const now = new Date();
        const dateEl = document.getElementById('current-date');
        const timeEl = document.getElementById('current-time');
        if (dateEl) dateEl.textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
        if (timeEl) timeEl.textContent = `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')} WIB`;
    }
    updateTime();
    setInterval(updateTime, 1000);
})();
</script>

@endsection
