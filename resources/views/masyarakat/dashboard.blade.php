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



        {{-- Layanan Populer — 3 Cards --}}
        <div class="bg-white rounded-[20px] border border-gray-100 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden">
            <div class="px-7 py-6 border-b border-gray-50 flex items-center justify-between bg-white relative overflow-hidden">
                <!-- Subtle bg accent -->
                <div class="absolute top-0 right-0 w-64 h-full bg-gradient-to-l from-blue-50/50 to-transparent pointer-events-none"></div>
                <div class="relative z-10">
                    <h2 class="text-[16px] font-extrabold text-slate-800 flex items-center gap-2">
                        <i class="ph ph-squares-four text-blue-600 text-lg"></i>
                        Layanan Populer
                    </h2>
                    <p class="text-[13px] text-slate-500 mt-0.5 ml-7">Layanan utama yang tersedia untuk masyarakat</p>
                </div>
                <a href="{{ route('masyarakat.layanan') }}" class="hidden sm:flex text-[13px] text-blue-600 font-bold hover:text-white items-center gap-1.5 transition-colors px-4 py-2 bg-blue-50 hover:bg-blue-600 rounded-xl relative z-10">
                    Semua Layanan <i class="ph ph-arrow-right font-bold"></i>
                </a>
            </div>

            <div class="p-7 grid grid-cols-1 sm:grid-cols-3 gap-5 bg-slate-50/50">

                {{-- Peminjaman Aula --}}
                <div class="bg-white border border-gray-100 rounded-[16px] p-5 hover:border-blue-300 hover:shadow-[0_8px_30px_rgb(37,99,235,0.12)] transition-all duration-300 group cursor-pointer flex flex-col items-start relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-50 rounded-full group-hover:scale-[2.5] transition-transform duration-500 ease-out z-0"></div>
                    <div class="relative z-10 w-12 h-12 bg-blue-100 text-blue-600 rounded-[14px] flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300 shadow-sm mb-3">
                        <i class="ph ph-building text-[24px]"></i>
                    </div>
                    <div class="relative z-10 flex-1">
                        <h3 class="text-[15px] font-bold text-slate-800 mb-1">Peminjaman Aula</h3>
                        <p class="text-[12px] text-slate-500 leading-relaxed mb-3">Ajukan peminjaman aula Kecamatan Cikampek untuk kegiatan Anda.</p>
                        <a href="{{ route('masyarakat.peminjaman_aula.index') }}" class="inline-flex items-center text-[12px] font-bold text-blue-600 group-hover:text-blue-800 transition-colors">
                            Mulai Pengajuan <i class="ph ph-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                {{-- Peminjaman Barang Inventaris --}}
                <div class="bg-white border border-gray-100 rounded-[16px] p-5 hover:border-indigo-300 hover:shadow-[0_8px_30px_rgb(99,102,241,0.12)] transition-all duration-300 group cursor-pointer flex flex-col items-start relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-indigo-50 rounded-full group-hover:scale-[2.5] transition-transform duration-500 ease-out z-0"></div>
                    <div class="relative z-10 w-12 h-12 bg-indigo-100 text-indigo-600 rounded-[14px] flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300 shadow-sm mb-3">
                        <i class="ph ph-package text-[24px]"></i>
                    </div>
                    <div class="relative z-10 flex-1">
                        <h3 class="text-[15px] font-bold text-slate-800 mb-1">Peminjaman Inventaris</h3>
                        <p class="text-[12px] text-slate-500 leading-relaxed mb-3">Ajukan peminjaman barang inventaris seperti kursi, sound system, dan PC.</p>
                        <a href="{{ route('masyarakat.peminjaman_inventaris.index') }}" class="inline-flex items-center text-[12px] font-bold text-indigo-600 group-hover:text-indigo-800 transition-colors">
                            Mulai Pengajuan <i class="ph ph-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                {{-- Sertifikat / Piagam --}}
                <div class="bg-white border border-gray-100 rounded-[16px] p-5 hover:border-purple-300 hover:shadow-[0_8px_30px_rgb(168,85,247,0.12)] transition-all duration-300 group cursor-pointer flex flex-col items-start relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-purple-50 rounded-full group-hover:scale-[2.5] transition-transform duration-500 ease-out z-0"></div>
                    <div class="relative z-10 w-12 h-12 bg-purple-100 text-purple-600 rounded-[14px] flex items-center justify-center flex-shrink-0 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300 shadow-sm mb-3">
                        <i class="ph ph-certificate text-[24px]"></i>
                    </div>
                    <div class="relative z-10 flex-1">
                        <h3 class="text-[15px] font-bold text-slate-800 mb-1">Sertifikat / Piagam</h3>
                        <p class="text-[12px] text-slate-500 leading-relaxed mb-3">Ajukan pembuatan sertifikat atau piagam untuk kegiatan Anda.</p>
                        <a href="{{ route('masyarakat.sertifikat.index') }}" class="inline-flex items-center text-[12px] font-bold text-purple-600 group-hover:text-purple-800 transition-colors">
                            Mulai Pengajuan <i class="ph ph-arrow-right ml-1"></i>
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

                @if(count($notifDisplay) > 0)

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
                @else
                <div class="px-6 py-8 text-center">
                    <p class="text-[13px] text-slate-400 font-medium">Belum ada notifikasi saat ini.</p>
                </div>
                @endif

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

                @if(count($riwayatDisplay) > 0)
                @foreach($riwayatDisplay as $r)
                <a href="{{ route($r['route'], $r['id']) }}" class="px-6 py-4 flex flex-col hover:bg-slate-50 transition-colors group">
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
                            {{ $r['statusColor'] === 'rose' ? 'bg-gradient-to-r from-rose-400 to-rose-500 text-white' : '' }}
                            {{ $r['statusColor'] === 'indigo' ? 'bg-gradient-to-r from-indigo-400 to-indigo-500 text-white' : '' }}
                            {{ $r['statusColor'] === 'purple' ? 'bg-gradient-to-r from-purple-400 to-purple-500 text-white' : '' }}
                            whitespace-nowrap">{{ $r['status'] }}</span>
                    </div>
                </a>
                @endforeach
                @else
                <div class="px-6 py-8 text-center">
                    <p class="text-[13px] text-slate-400 font-medium">Belum ada riwayat pengajuan.</p>
                </div>
                @endif

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
