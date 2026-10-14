<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard Admin Kecamatan Cikampek, Kabupaten Karawang">
    <title>@yield('title', 'Admin') – Kec. Cikampek</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('js/tailwind-theme.js') }}"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/interactive-display.css') }}">
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }

        /* 1. Base Emerald Green Sidebar (Hijau Zamrud Elegan Digital) */
        .sidebar-nav {
            background: linear-gradient(180deg, #064e3b 0%, #033c2d 50%, #02241b 100%);
            overflow: hidden;
            position: relative;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.25);
            border-right: 1px solid rgba(52, 211, 153, 0.15);
        }

        /* 2. Soft Ambient Emerald Glow (Tanpa Background Gambar) */
        .sidebar-nav::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 85% 15%, rgba(52, 211, 153, 0.12) 0%, transparent 60%);
            pointer-events: none;
            z-index: 1;
        }

        /* 3. Konten sidebar diposisikan di atas overlay */
        .sidebar-nav > * {
            position: relative;
            z-index: 10;
        }

        /* 4. Brand Area di bagian atas sidebar: Menyatu mulus tanpa pembatas */
        .sidebar-brand-area {
            background: transparent;
            border-bottom: none;
        }

        /* 5. Area Khusus Tombol Keluar: Frosted Glass Zamrud Rapi */
        .sidebar-bottom-area {
            background: rgba(2, 36, 27, 0.65);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-top: 1px solid rgba(52, 211, 153, 0.18);
            position: relative;
            z-index: 20;
        }

        /* 6. Active & Interactive Menu States */
        .menu-active { 
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.28), rgba(6, 95, 70, 0.18)); 
            color: #ffffff; 
            box-shadow: inset 3.5px 0 0 #34d399, 0 4px 15px rgba(0, 0, 0, 0.18);
            border: 1px solid rgba(52, 211, 153, 0.25);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }
        .menu-active .menu-icon { color: #34d399 !important; }
        .menu-item { 
            color: #cbd5e1; 
            transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1); 
        }
        .menu-item:hover { 
            background: rgba(255, 255, 255, 0.08); 
            color: #ffffff; 
            transform: translateX(4px);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        .menu-item:active {
            transform: scale(0.97);
        }
        .menu-item:hover .menu-icon { 
            color: #34d399; 
            transform: scale(1.12);
        }
        .menu-icon {
            transition: all 0.3s ease;
        }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* 7. Header Glassmorphism */
        .dashboard-header {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        /* 8. Main Dashboard Background: hijaubg.png */
        .dashboard-main-bg {
            background-color: #f0fdf4;
            background-image: url('{{ asset('images/hijaubg.png') }}');
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            background-attachment: scroll;
        }

        /* 9. Page Header Glass Card: Kontras 100% Sempurna & Estetis di Pojok Kiri Atas */
        .page-header-card {
            background: rgba(255, 255, 255, 0.88) !important;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.95) !important;
            border-radius: 20px !important;
            padding: 18px 24px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03) !important;
            margin-bottom: 1.5rem !important;
        }
        .page-header-card h1 {
            color: #0f172a !important;
            font-weight: 800 !important;
        }
        .page-header-card p {
            color: #475569 !important;
            font-weight: 500 !important;
        }

        /* 10. Otomatis memperjelas dan membingkai judul pertama di pojok kiri atas bila belum berkelas kartu */
        .dashboard-main-bg > div:first-child:not(.grid):not(.relative) {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 18px 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
        }
        .dashboard-main-bg > div:first-child:not(.grid):not(.relative) h1 {
            color: #0f172a !important;
            font-weight: 800 !important;
        }
        .dashboard-main-bg > div:first-child:not(.grid):not(.relative) p {
            color: #475569 !important;
            font-weight: 500 !important;
        }

        /* 11. Penajaman Kontras & Estetika Global untuk Kartu Fitur-Fitur Lainnya */
        .dashboard-main-bg .bg-white {
            background-color: rgba(255, 255, 255, 0.96) !important;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .dashboard-main-bg h1:not(.text-white):not([class*="text-white"]), 
        .dashboard-main-bg h2:not(.text-white):not([class*="text-white"]), 
        .dashboard-main-bg h3:not(.text-white):not([class*="text-white"]) {
            color: #0f172a;
        }
    </style>
</head>
<body class="bg-[#f5f7fb] text-gray-800 antialiased"
      data-user-role="admin"
      data-user-name="{{ Auth::check() ? Auth::user()->name : 'Admin' }}"
      data-session-success="{{ session('success') }}"
      data-session-error="{{ session('error') }}"
      data-session-warning="{{ session('warning') }}"
      data-session-info="{{ session('info') }}">
<div class="flex h-screen overflow-hidden" 
     x-data="{ 
         sidebarOpen: window.innerWidth >= 768 ? (localStorage.getItem('sidebarOpen_admin') !== null ? localStorage.getItem('sidebarOpen_admin') === 'true' : true) : false,
         toggleSidebar() {
             this.sidebarOpen = !this.sidebarOpen;
             if (window.innerWidth >= 768) {
                 localStorage.setItem('sidebarOpen_admin', this.sidebarOpen);
             }
         }
     }"
     @resize.window="if (window.innerWidth < 768 && sidebarOpen) { sidebarOpen = false; }">

    <!-- Mobile Overlay -->
    <div x-cloak x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/50 backdrop-blur-xs md:hidden"></div>

    @php
        $pendingCount = \App\Models\PeminjamanAula::whereIn('status', ['Menunggu Verifikasi', 'Diproses', 'Perlu Perbaikan', 'Surat Diproses'])->count()
            + \App\Models\InventoryBorrowing::whereIn('status', ['Diajukan', 'Diproses'])->count()
            + \App\Models\CertificateRequest::whereIn('status', ['Diajukan', 'Diproses', 'Menunggu Dokumen'])->count();
    @endphp

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar-nav fixed md:static inset-y-0 left-0 z-50 w-64 flex flex-col flex-shrink-0
                  transition-all duration-300 ease-in-out"
           :class="sidebarOpen ? 'translate-x-0 md:ml-0' : '-translate-x-full md:translate-x-0 md:-ml-64'">

        <!-- Brand -->
        <div class="sidebar-brand-area flex items-center justify-between px-4 sm:px-5 py-[18px] flex-shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden shadow-sm">
                    <img src="{{ asset('images/kop-surat.png') }}" alt="Logo Kabupaten Karawang"
                         class="w-8 h-8 object-contain"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div style="display:none" class="w-full h-full items-center justify-center bg-blue-700 rounded-xl">
                        <i class="ph ph-shield-star text-white text-lg"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <p class="text-white font-bold text-sm leading-tight truncate">Kec. Cikampek</p>
                    <p class="text-emerald-300/80 text-xs leading-tight mt-0.5 truncate">Kabupaten Karawang</p>
                </div>
            </div>

            <!-- Tombol Tutup / Toggle di dalam Sidebar -->
            <button @click="toggleSidebar()" 
                    type="button"
                    class="text-white/70 hover:text-white hover:bg-white/10 p-1.5 rounded-lg transition-all active:scale-95 cursor-pointer flex items-center justify-center shrink-0 ml-1"
                    title="Tutup Sidebar"
                    aria-label="Tutup Sidebar">
                <i class="ph ph-x text-xl md:hidden"></i>
                <i class="ph ph-caret-left text-xl hidden md:block"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto scrollbar-hide px-3 py-4">
            <ul class="space-y-0.5">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'menu-active' : '' }}">
                        <i class="ph ph-squares-four menu-icon text-lg {{ request()->routeIs('admin.dashboard') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Dashboard
                    </a>
                </li>
            </ul>

            {{-- Layanan Section --}}
            <div class="mt-5 mb-2 px-3">
                <p class="text-[10px] font-bold text-emerald-300/80 uppercase tracking-widest">Layanan</p>
            </div>
            <ul class="space-y-0.5">
                <li>
                    <a href="{{ route('admin.peminjaman_aula.index') }}"
                       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.peminjaman_aula.*') ? 'menu-active' : '' }}">
                        <i class="ph ph-building menu-icon text-lg {{ request()->routeIs('admin.peminjaman_aula.*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Peminjaman Aula
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.inventaris.index') }}"
                       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.inventaris.*') ? 'menu-active' : '' }}">
                        <i class="ph ph-cube menu-icon text-lg {{ request()->routeIs('admin.inventaris.*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Kelola Inventaris
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.peminjaman_inventaris.index') }}"
                       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.peminjaman_inventaris.*') ? 'menu-active' : '' }}">
                        <i class="ph ph-package menu-icon text-lg {{ request()->routeIs('admin.peminjaman_inventaris.*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Pengajuan Inventaris
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.sertifikat.index') }}"
                       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.sertifikat.*') ? 'menu-active' : '' }}">
                        <i class="ph ph-certificate menu-icon text-lg {{ request()->routeIs('admin.sertifikat.*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Sertifikat / Piagam
                    </a>
                </li>
            </ul>

            {{-- Sistem Section --}}
            <div class="mt-5 mb-2 px-3">
                <p class="text-[10px] font-bold text-emerald-300/80 uppercase tracking-widest">Sistem</p>
            </div>
            <ul class="space-y-0.5">
                <li>
                    <a href="{{ route('admin.laporan') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.laporan') ? 'menu-active' : '' }}">
                        <i class="ph ph-chart-bar menu-icon text-lg {{ request()->routeIs('admin.laporan') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Laporan
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.ai_knowledge.index') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.ai_knowledge.*') ? 'menu-active' : '' }}">
                        <i class="ph ph-robot menu-icon text-lg {{ request()->routeIs('admin.ai_knowledge.*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Knowledge Base AI
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.pengaturan') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.pengaturan') ? 'menu-active' : '' }}">
                        <i class="ph ph-gear menu-icon text-lg {{ request()->routeIs('admin.pengaturan') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Pengaturan
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.notifikasi') }}" class="menu-item flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.notifikasi') ? 'menu-active' : '' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-bell menu-icon text-lg {{ request()->routeIs('admin.notifikasi') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                            <span>Notifikasi</span>
                        </div>
                        @if($pendingCount > 0)
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-500/20 text-amber-300 rounded-full border border-amber-500/30">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Sidebar Bottom -->
        <div class="sidebar-bottom-area flex-shrink-0 px-3 py-2.5 flex flex-col justify-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-[13px] font-bold text-red-300 border border-red-500/30 bg-red-950/40 hover:bg-red-600 hover:text-white hover:border-red-500 active:scale-95 transition-all duration-300 shadow-sm">
                    <i class="ph ph-sign-out text-lg"></i>
                    Keluar
                </button>
            </form>
            <div class="mt-2 mx-1 py-1.5 px-2 rounded-xl bg-white/5 border border-white/10 text-center">
                <p class="text-emerald-200/80 text-[11px] font-medium leading-tight">Bersama Melayani Masyarakat</p>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN AREA ===== -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

        <!-- Header -->
        <header class="dashboard-header h-16 flex items-center justify-between px-4 sm:px-6 flex-shrink-0 z-30 shadow-xs">
            <div class="flex items-center gap-3">
                <button @click="toggleSidebar()"
                        type="button"
                        class="flex items-center justify-center w-10 h-10 rounded-xl text-gray-700 hover:text-emerald-700 hover:bg-emerald-50 active:scale-95 transition-all duration-200 border border-gray-200/80 shadow-xs flex-shrink-0 cursor-pointer"
                        title="Buka / Tutup Sidebar"
                        aria-label="Toggle Sidebar">
                    <i class="ph ph-list text-2xl font-bold"></i>
                </button>
                <div>
                    <h1 class="text-sm font-bold text-gray-900 leading-tight">@yield('title', 'Portal Admin')</h1>
                    <p class="text-[11px] text-gray-500 mt-0.5 leading-tight hidden sm:block">Sistem Informasi Pelayanan Kecamatan Cikampek.</p>
                </div>
            </div>

            <div class="flex items-center gap-2" x-data="{ userOpen: false }">
                <!-- Date/Time -->
                <div class="hidden md:flex items-center gap-2 bg-white/80 backdrop-blur-sm border border-emerald-900/10 rounded-xl px-3 py-2 shadow-xs">
                    <i class="ph ph-calendar-blank text-emerald-600 text-sm"></i>
                    <div class="text-right leading-tight">
                        <p class="text-[11px] text-gray-500" id="admin-date">Senin, 15 Sep 2025</p>
                        <p class="text-xs font-bold text-gray-800" id="admin-time">13:24 WIB</p>
                    </div>
                </div>



                <!-- Notification Bell -->
                <a href="{{ route('admin.notifikasi') }}"
                   class="relative p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition {{ request()->routeIs('admin.notifikasi') ? 'text-blue-600 bg-blue-50' : '' }}"
                   title="Notifikasi Pengajuan">
                    <i class="ph ph-bell text-xl"></i>
                    @if($pendingCount > 0)
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    @endif
                </a>

                <!-- User Dropdown -->
                <div class="relative ml-1">
                    <button @click="userOpen = !userOpen"
                            class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                        <div class="w-8 h-8 bg-blue-700 rounded-full flex items-center justify-center text-white flex-shrink-0">
                            <i class="ph ph-user-circle text-lg"></i>
                        </div>
                        <div class="hidden sm:block text-left leading-tight">
                            <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-0.5" id="admin-greeting-user">Selamat datang,</p>
                            <p class="text-sm font-semibold text-gray-800 leading-none">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-gray-500 mt-0.5">Administrator</p>
                        </div>
                        <i class="ph ph-caret-down text-gray-400 text-xs hidden sm:block"></i>
                    </button>

                    <div x-cloak x-show="userOpen" @click.away="userOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-52 bg-white/95 backdrop-blur-lg rounded-2xl shadow-xl border border-gray-100 py-1.5 z-50">
                        <div class="px-4 py-2.5 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">Administrator</p>
                        </div>
                        <div class="border-t border-gray-100 my-0.5"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition text-left">
                                <i class="ph ph-sign-out text-red-400"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="dashboard-main-bg flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    <script>
    (function() {
        const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        function updateAdminTime() {
            const now = new Date();
            const dateEl = document.getElementById('admin-date');
            const timeEl = document.getElementById('admin-time');
            if (dateEl) dateEl.textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
            if (timeEl) timeEl.textContent = `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')} WIB`;
            
            const greetingEl = document.getElementById('admin-greeting-user');
            if (greetingEl) {
                const hour = now.getHours();
                let greeting = 'Selamat Malam,';
                if (hour >= 5 && hour < 11) greeting = 'Selamat Pagi,';
                else if (hour >= 11 && hour < 15) greeting = 'Selamat Siang,';
                else if (hour >= 15 && hour < 18) greeting = 'Selamat Sore,';
                greetingEl.textContent = greeting;
            }
        }
        updateAdminTime();
        setInterval(updateAdminTime, 1000);
    })();
    </script>

    @stack('scripts')
    <script src="{{ asset('js/interactive-display.js') }}"></script>
</div>{{-- end x-data sidebarOpen wrapper --}}
</body>
</html>