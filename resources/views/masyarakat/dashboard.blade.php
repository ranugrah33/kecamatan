@extends('layouts.masyarakat')
@section('title', 'Beranda')

@section('content')

{{-- Two-column layout: Left content + Right panel --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

    {{-- ===== LEFT COLUMN ===== --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Welcome Card (VIBRANT REDESIGN) --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-800 rounded-[20px] p-8 shadow-lg border border-blue-500/30">
            <!-- Decorative Background Elements -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 bg-white rounded-full mix-blend-overlay filter blur-[50px] opacity-20 animate-pulse"></div>
            <div class="absolute bottom-0 right-40 -mb-20 w-56 h-56 bg-blue-300 rounded-full mix-blend-overlay filter blur-[40px] opacity-20"></div>
            
            <!-- Abstract Wave/Curve SVG -->
            <svg class="absolute bottom-0 left-0 w-full opacity-10 pointer-events-none" viewBox="0 0 1440 320" preserveAspectRatio="none">
                <path fill="#ffffff" fill-opacity="1" d="M0,224L48,213.3C96,203,192,181,288,186.7C384,192,480,224,576,213.3C672,203,768,149,864,128C960,107,1056,117,1152,144C1248,171,1344,213,1392,234.7L1440,256L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>

            <div class="flex flex-col sm:flex-row items-center sm:items-start justify-between gap-6 relative z-10">
                <div class="flex-1 text-center sm:text-left">
                    <p class="text-blue-200 text-[13px] font-bold tracking-wider uppercase mb-1">Selamat datang,</p>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-sm">Halo, {{ Auth::user()->name }} 👋</h1>
                    <p class="text-blue-100/90 text-[15px] mt-2.5 leading-relaxed max-w-md">
                        Nikmati layanan publik Kecamatan Cikampek dengan lebih mudah, cepat dan transparan.
                    </p>
                </div>
                {{-- Date & Time Card --}}
                <div class="flex-shrink-0 bg-white/10 backdrop-blur-md rounded-[16px] px-6 py-4 shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-white/20 min-w-[200px] text-center sm:text-right transform hover:scale-105 transition-transform duration-300">
                    <div class="flex items-center justify-center sm:justify-end gap-2 mb-1.5">
                        <i class="ph ph-calendar-blank text-blue-200 text-sm"></i>
                        <span class="text-[13px] text-blue-50 font-medium" id="current-date">Senin, 15 September 2025</span>
                    </div>
                    <p class="text-[26px] font-black text-white tracking-tight drop-shadow-md" id="current-time">13:24 WIB</p>
                </div>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-5">

            {{-- Total Pengajuan --}}
            <div class="bg-white rounded-[18px] p-5 border-l-4 border-l-blue-500 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:shadow-[0_8px_25px_rgb(37,99,235,0.08)] hover:-translate-y-1 transition-all duration-300 group cursor-default relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-blue-50 to-transparent rounded-bl-full opacity-50 pointer-events-none"></div>
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-[14px] flex items-center justify-center mb-4 shadow-md shadow-blue-500/30 group-hover:scale-110 transition-transform duration-300">
                    <i class="ph ph-files text-[24px] text-white"></i>
                </div>
                <p class="text-[28px] font-black text-slate-800 leading-none tracking-tight">{{ $total_pengajuan }}</p>
                <p class="text-[13px] text-slate-500 mt-1.5 font-medium">Total Pengajuan</p>
            </div>

            {{-- Sedang Diproses --}}
            <div class="bg-white rounded-[18px] p-5 border-l-4 border-l-amber-500 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:shadow-[0_8px_25px_rgb(245,158,11,0.08)] hover:-translate-y-1 transition-all duration-300 group cursor-default relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-amber-50 to-transparent rounded-bl-full opacity-50 pointer-events-none"></div>
                <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-[14px] flex items-center justify-center mb-4 shadow-md shadow-amber-500/30 group-hover:scale-110 transition-transform duration-300">
                    <i class="ph ph-arrow-clockwise text-[24px] text-white"></i>
                </div>
                <p class="text-[28px] font-black text-slate-800 leading-none tracking-tight">{{ $diproses }}</p>
                <p class="text-[13px] text-slate-500 mt-1.5 font-medium">Sedang Diproses</p>
            </div>

            {{-- Selesai --}}
            <div class="bg-white rounded-[18px] p-5 border-l-4 border-l-emerald-500 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:shadow-[0_8px_25px_rgb(16,185,129,0.08)] hover:-translate-y-1 transition-all duration-300 group cursor-default relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-emerald-50 to-transparent rounded-bl-full opacity-50 pointer-events-none"></div>
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-green-500 rounded-[14px] flex items-center justify-center mb-4 shadow-md shadow-emerald-500/30 group-hover:scale-110 transition-transform duration-300">
                    <i class="ph ph-check-circle text-[24px] text-white"></i>
                </div>
                <p class="text-[28px] font-black text-slate-800 leading-none tracking-tight">{{ $selesai }}</p>
                <p class="text-[13px] text-slate-500 mt-1.5 font-medium">Selesai</p>
            </div>

            {{-- Perlu Diperbaiki --}}
            <div class="bg-white rounded-[18px] p-5 border-l-4 border-l-rose-500 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:shadow-[0_8px_25px_rgb(225,29,72,0.08)] hover:-translate-y-1 transition-all duration-300 group cursor-default relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-rose-50 to-transparent rounded-bl-full opacity-50 pointer-events-none"></div>
                <div class="w-12 h-12 bg-gradient-to-br from-rose-400 to-red-500 rounded-[14px] flex items-center justify-center mb-4 shadow-md shadow-rose-500/30 group-hover:scale-110 transition-transform duration-300">
                    <i class="ph ph-warning-circle text-[24px] text-white"></i>
                </div>
                <p class="text-[28px] font-black text-slate-800 leading-none tracking-tight">{{ $perlu_diperbaiki }}</p>
                <p class="text-[13px] text-slate-500 mt-1.5 font-medium">Perlu Diperbaiki</p>
            </div>

        </div>

        {{-- Layanan Populer --}}
        <div class="bg-white rounded-[20px] border border-gray-100 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden">
            <div class="px-7 py-6 border-b border-gray-50 flex items-center justify-between bg-white relative overflow-hidden">
                <!-- Subtle bg accent -->
                <div class="absolute top-0 right-0 w-64 h-full bg-gradient-to-l from-blue-50/50 to-transparent pointer-events-none"></div>
                <div class="relative z-10">
                    <h2 class="text-[16px] font-extrabold text-slate-800 flex items-center gap-2">
                        <i class="ph ph-squares-four text-blue-600 text-lg"></i>
                        Layanan Populer
                    </h2>
                    <p class="text-[13px] text-slate-500 mt-0.5 ml-7">Layanan yang paling sering digunakan masyarakat</p>
                </div>
                <a href="{{ route('masyarakat.layanan') }}" class="hidden sm:flex text-[13px] text-blue-600 font-bold hover:text-white items-center gap-1.5 transition-colors px-4 py-2 bg-blue-50 hover:bg-blue-600 rounded-xl relative z-10">
                    Semua Layanan <i class="ph ph-arrow-right font-bold"></i>
                </a>
            </div>

            <div class="p-7 grid grid-cols-1 sm:grid-cols-2 gap-5 bg-slate-50/50">

                {{-- Peminjaman Aula --}}
                <div class="bg-white border border-gray-100 rounded-[16px] p-5 hover:border-blue-300 hover:shadow-[0_8px_30px_rgb(37,99,235,0.12)] transition-all duration-300 group cursor-pointer flex gap-4 items-start relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-50 rounded-full group-hover:scale-[2.5] transition-transform duration-500 ease-out z-0"></div>
                    <div class="relative z-10 w-12 h-12 bg-blue-100 text-blue-600 rounded-[14px] flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300 shadow-sm">
                        <i class="ph ph-door-open text-[24px]"></i>
                    </div>
                    <div class="relative z-10 flex-1">
                        <h3 class="text-[15px] font-bold text-slate-800 mb-1">Peminjaman Aula</h3>
                        <p class="text-[12px] text-slate-500 leading-relaxed mb-3">Ajukan peminjaman aula untuk kegiatan Anda</p>
                        <a href="{{ route('masyarakat.peminjaman_aula.index') }}" class="inline-flex items-center text-[12px] font-bold text-blue-600 group-hover:text-blue-800 transition-colors">
                            Mulai Pengajuan <i class="ph ph-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                {{-- Arsip UMKM --}}
                <div class="bg-white border border-gray-100 rounded-[16px] p-5 hover:border-emerald-300 hover:shadow-[0_8px_30px_rgb(16,185,129,0.12)] transition-all duration-300 group cursor-pointer flex gap-4 items-start relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-50 rounded-full group-hover:scale-[2.5] transition-transform duration-500 ease-out z-0"></div>
                    <div class="relative z-10 w-12 h-12 bg-emerald-100 text-emerald-600 rounded-[14px] flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300 shadow-sm">
                        <i class="ph ph-storefront text-[24px]"></i>
                    </div>
                    <div class="relative z-10 flex-1">
                        <h3 class="text-[15px] font-bold text-slate-800 mb-1">Arsip UMKM</h3>
                        <p class="text-[12px] text-slate-500 leading-relaxed mb-3">Lihat data dan informasi UMKM di wilayah Cikampek</p>
                        <a href="#" class="inline-flex items-center text-[12px] font-bold text-emerald-600 group-hover:text-emerald-800 transition-colors">
                            Lihat Arsip <i class="ph ph-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                {{-- Bantuan Sosial --}}
                <div class="bg-white border border-gray-100 rounded-[16px] p-5 hover:border-amber-300 hover:shadow-[0_8px_30px_rgb(245,158,11,0.12)] transition-all duration-300 group cursor-pointer flex gap-4 items-start relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-amber-50 rounded-full group-hover:scale-[2.5] transition-transform duration-500 ease-out z-0"></div>
                    <div class="relative z-10 w-12 h-12 bg-amber-100 text-amber-600 rounded-[14px] flex items-center justify-center flex-shrink-0 group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300 shadow-sm">
                        <i class="ph ph-hand-heart text-[24px]"></i>
                    </div>
                    <div class="relative z-10 flex-1">
                        <h3 class="text-[15px] font-bold text-slate-800 mb-1">Bantuan Sosial</h3>
                        <p class="text-[12px] text-slate-500 leading-relaxed mb-3">Cek informasi bantuan sosial yang tersedia</p>
                        <a href="#" class="inline-flex items-center text-[12px] font-bold text-amber-600 group-hover:text-amber-800 transition-colors">
                            Cek Bantuan <i class="ph ph-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                {{-- Jual Beli Tanah --}}
                <div class="bg-white border border-gray-100 rounded-[16px] p-5 hover:border-purple-300 hover:shadow-[0_8px_30px_rgb(168,85,247,0.12)] transition-all duration-300 group cursor-pointer flex gap-4 items-start relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-purple-50 rounded-full group-hover:scale-[2.5] transition-transform duration-500 ease-out z-0"></div>
                    <div class="relative z-10 w-12 h-12 bg-purple-100 text-purple-600 rounded-[14px] flex items-center justify-center flex-shrink-0 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300 shadow-sm">
                        <i class="ph ph-map-trifold text-[24px]"></i>
                    </div>
                    <div class="relative z-10 flex-1">
                        <h3 class="text-[15px] font-bold text-slate-800 mb-1">Jual Beli Tanah</h3>
                        <p class="text-[12px] text-slate-500 leading-relaxed mb-3">Informasi dan layanan jual beli tanah</p>
                        <a href="#" class="inline-flex items-center text-[12px] font-bold text-purple-600 group-hover:text-purple-800 transition-colors">
                            Lihat Info <i class="ph ph-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

            </div>
            
            <div class="p-4 bg-white border-t border-gray-50 text-center sm:hidden">
                 <a href="{{ route('masyarakat.layanan') }}" class="text-[13px] text-blue-600 font-bold hover:text-blue-700">Lihat Semua Layanan</a>
            </div>
        </div>

    </div>{{-- end left column --}}

    {{-- ===== RIGHT COLUMN ===== --}}
    <div class="space-y-6">

        {{-- Notifikasi Terbaru --}}
        <div class="bg-white rounded-[20px] border border-gray-100 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between bg-gradient-to-r from-white to-slate-50">
                <h2 class="text-[15px] font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-bell text-blue-600 text-lg"></i>
                    Notifikasi Terbaru
                </h2>
                <a href="{{ route('masyarakat.notifikasi') }}" class="text-[12px] text-blue-600 font-bold hover:bg-blue-50 px-2 py-1 rounded-md transition">
                    Lihat Semua
                </a>
            </div>
            <div class="divide-y divide-gray-50">

                @php
                $notifDisplay = [
                    ['icon' => 'ph-check-circle', 'color' => 'emerald', 'msg' => 'Pengajuan Peminjaman Aula disetujui', 'time' => 'Pada 11 Sep 2025, 11:45'],
                    ['icon' => 'ph-info', 'color' => 'blue', 'msg' => 'Dokumen Anda sedang diverifikasi', 'time' => 'Pada 08 Sep 2025, 15:10'],
                    ['icon' => 'ph-bell-ringing', 'color' => 'amber', 'msg' => 'Jadwal pelayanan berubah', 'time' => 'Pada 05 Sep 2025, 08:20'],
                ];
                @endphp

                @foreach($notifDisplay as $n)
                <div class="px-6 py-4 flex items-start gap-3.5 hover:bg-slate-50 transition-colors cursor-pointer group">
                    <div class="w-10 h-10 bg-{{ $n['color'] }}-50 rounded-[12px] flex items-center justify-center flex-shrink-0 mt-0.5 border border-{{ $n['color'] }}-100 group-hover:bg-{{ $n['color'] }}-500 transition-colors duration-300">
                        <i class="ph {{ $n['icon'] }} text-{{ $n['color'] }}-600 text-[20px] group-hover:text-white transition-colors duration-300"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13.5px] font-bold text-slate-800 leading-tight mb-1">{{ $n['msg'] }}</p>
                        <p class="text-[11.5px] font-medium text-slate-400 flex items-center gap-1">
                            <i class="ph ph-clock text-[12px]"></i> {{ $n['time'] }}
                        </p>
                    </div>
                </div>
                @endforeach

            </div>
        </div>

        {{-- Riwayat Pengajuan Terbaru --}}
        <div class="bg-white rounded-[20px] border border-gray-100 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between bg-gradient-to-r from-white to-slate-50">
                <h2 class="text-[15px] font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-clock-counter-clockwise text-blue-600 text-lg"></i>
                    Riwayat Pengajuan
                </h2>
                <a href="{{ route('masyarakat.riwayat') }}" class="text-[12px] text-blue-600 font-bold hover:bg-blue-50 px-2 py-1 rounded-md transition">
                    Lihat Semua
                </a>
            </div>
            <div class="divide-y divide-gray-50">

                @php
                $riwayatDisplay = [
                    ['icon' => 'ph-door-open', 'iconBg' => 'blue', 'jenis' => 'Peminjaman Aula', 'tgl' => '11 Sep 2025, 10:30', 'status' => 'Diproses', 'statusColor' => 'blue', 'route' => 'masyarakat.riwayat'],
                    ['icon' => 'ph-storefront', 'iconBg' => 'emerald', 'jenis' => 'Arsip UMKM', 'tgl' => '08 Sep 2025, 14:20', 'status' => 'Selesai', 'statusColor' => 'emerald', 'route' => 'masyarakat.riwayat'],
                    ['icon' => 'ph-hand-heart', 'iconBg' => 'amber', 'jenis' => 'Bantuan Sosial', 'tgl' => '05 Sep 2025, 09:15', 'status' => 'Diterima', 'statusColor' => 'amber', 'route' => 'masyarakat.riwayat'],
                ];
                @endphp

                @foreach($riwayatDisplay as $r)
                <a href="{{ route($r['route']) }}" class="px-6 py-4 flex flex-col hover:bg-slate-50 transition-colors group">
                    <div class="flex items-center gap-3.5 mb-3">
                        <div class="w-10 h-10 bg-{{ $r['iconBg'] }}-50 rounded-[12px] flex items-center justify-center flex-shrink-0 border border-{{ $r['iconBg'] }}-100 group-hover:bg-{{ $r['iconBg'] }}-500 transition-colors duration-300">
                            <i class="ph {{ $r['icon'] }} text-{{ $r['iconBg'] }}-600 text-[20px] group-hover:text-white transition-colors duration-300"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[14px] font-bold text-slate-800 truncate group-hover:text-{{ $r['iconBg'] }}-600 transition-colors">{{ $r['jenis'] }}</p>
                            <p class="text-[11.5px] font-medium text-slate-400 mt-0.5 flex items-center gap-1">
                                <i class="ph ph-calendar-blank text-[12px]"></i> {{ $r['tgl'] }}
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center bg-[#FAFCFF] p-2.5 rounded-xl border border-gray-100">
                        <span class="text-[11px] font-semibold text-slate-500">Status Pengajuan:</span>
                        <span class="text-[11px] font-bold px-3 py-1 rounded-lg shadow-sm
                            {{ $r['statusColor'] === 'emerald' ? 'bg-gradient-to-r from-emerald-400 to-emerald-500 text-white' : '' }}
                            {{ $r['statusColor'] === 'blue' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white' : '' }}
                            {{ $r['statusColor'] === 'amber' ? 'bg-gradient-to-r from-amber-400 to-orange-500 text-white' : '' }}
                            whitespace-nowrap">{{ $r['status'] }}</span>
                    </div>
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
