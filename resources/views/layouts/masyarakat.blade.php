<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal Layanan Publik Kecamatan Cikampek, Kabupaten Karawang">
    <title>@yield('title', 'Dashboard') – Kec. Cikampek</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('js/tailwind-theme.js') }}"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="{{ asset('css/interactive-display.css') }}">
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        
        /* 1. Base Dark Forest Sidebar */
        .sidebar-nav {
            background-color: #021a12;
            overflow: hidden;
            position: relative;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.22);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* 2. Layer Gambar sidebarr.jpg Berhenti Tepat di Atas Area Tombol Keluar (Bebas Tabrakan) */
        .sidebar-nav::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 105px; /* Berhenti tepat di atas area tombol keluar agar gedung & tombol TIDAK TABRAKAN */
            background-image: url('{{ asset('images/sidebarr.jpg') }}');
            background-size: cover;
            background-position: center bottom;
            background-repeat: no-repeat;
            opacity: 1;
            z-index: 1;
            pointer-events: none;
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

        /* 5. Area Khusus Tombol Keluar: Pedestal Bersih di Bawah Gambar (Rapi & Bebas Tabrakan) */
        .sidebar-bottom-area {
            background: #021a12;
            border-top: 1px solid rgba(52, 211, 153, 0.20);
            position: relative;
            z-index: 20;
            height: 105px;
            box-sizing: border-box;
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

        .welcome-card {
            background: linear-gradient(135deg, #d1fae5 0%, #faf1d3 100%);
            position: relative; overflow: hidden;
        }
        .welcome-card::before {
            content: ''; position: absolute; right: -30px; bottom: -40px;
            width: 180px; height: 180px; border-radius: 50%;
            background: rgba(110, 231, 183,0.25);
        }
        .welcome-card::after {
            content: ''; position: absolute; right: 60px; bottom: -60px;
            width: 140px; height: 140px; border-radius: 50%;
            background: rgba(224, 184, 74, 0.2);
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
      data-user-role="masyarakat"
      data-user-name="{{ Auth::check() ? Auth::user()->name : 'Warga' }}"
      data-session-success="{{ session('success') }}"
      data-session-error="{{ session('error') }}"
      data-session-warning="{{ session('warning') }}"
      data-session-info="{{ session('info') }}">
<div class="flex h-screen overflow-hidden" 
     x-data="{ 
         sidebarOpen: window.innerWidth >= 768 ? (localStorage.getItem('sidebarOpen_masyarakat') !== null ? localStorage.getItem('sidebarOpen_masyarakat') === 'true' : true) : false,
         toggleSidebar() {
             this.sidebarOpen = !this.sidebarOpen;
             if (window.innerWidth >= 768) {
                 localStorage.setItem('sidebarOpen_masyarakat', this.sidebarOpen);
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

            <!-- Tombol Tutup / Minimize di dalam Sidebar -->
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
                    <a href="{{ route('masyarakat.dashboard') }}"
                       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('masyarakat.dashboard') ? 'menu-active' : '' }}">
                        <i class="ph ph-house menu-icon text-lg {{ request()->routeIs('masyarakat.dashboard') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Beranda
                    </a>
                </li>
                <li>
                    <a href="{{ route('masyarakat.layanan') }}"
                       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('masyarakat.layanan') ? 'menu-active' : '' }}">
                        <i class="ph ph-squares-four menu-icon text-lg {{ request()->routeIs('masyarakat.layanan') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Layanan Publik
                    </a>
                </li>
                <li>
                    <a href="{{ route('masyarakat.riwayat') }}"
                       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('masyarakat.riwayat') ? 'menu-active' : '' }}">
                        <i class="ph ph-clock-counter-clockwise menu-icon text-lg {{ request()->routeIs('masyarakat.riwayat') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Riwayat Pengajuan
                    </a>
                </li>
                <li>
                    <a href="{{ route('masyarakat.profil') }}"
                       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('masyarakat.profil') ? 'menu-active' : '' }}">
                        <i class="ph ph-user menu-icon text-lg {{ request()->routeIs('masyarakat.profil') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Profil Saya
                    </a>
                </li>
                <li>
                    <a href="{{ route('masyarakat.notifikasi') }}"
                       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('masyarakat.notifikasi') ? 'menu-active' : '' }}">
                        <i class="ph ph-bell menu-icon text-lg {{ request()->routeIs('masyarakat.notifikasi') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Notifikasi
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Sidebar Bottom -->
        <div class="sidebar-bottom-area flex-shrink-0 px-4 py-2.5 flex flex-col justify-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-[13px] font-bold text-red-300 border border-red-500/30 bg-red-950/40 hover:bg-red-600 hover:text-white hover:border-red-500 active:scale-95 transition-all duration-300 shadow-sm">
                    <i class="ph ph-sign-out text-lg"></i>
                    Keluar
                </button>
            </form>

            <!-- Tagline -->
            <div class="text-center px-2 mt-1.5">
                <p class="text-white/70 text-[11px] italic font-medium leading-tight">"Melayani dengan Hati"</p>
                <p class="text-white/45 text-[10px] mt-0.5 leading-tight">untuk Cikampek yang Lebih Baik</p>
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
                    <h1 class="text-sm font-bold text-gray-900 leading-tight">@yield('title', 'Portal Layanan')</h1>
                    <p class="text-[11px] text-gray-500 font-medium mt-0.5 hidden sm:block">Sistem Informasi Pelayanan Masyarakat Kecamatan Cikampek.</p>
                </div>
            </div>

            <div class="flex items-center gap-1.5" x-data="{ userOpen: false }">


                <!-- Notification Bell -->
                <a href="{{ route('masyarakat.notifikasi') }}"
                   class="relative p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                    <i class="ph ph-bell text-xl"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                </a>

                <!-- User Dropdown -->
                <div class="relative ml-1">
                    <button @click="userOpen = !userOpen"
                            class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden sm:block text-left leading-tight">
                            <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">Masyarakat</p>
                        </div>
                        <i class="ph ph-caret-down text-gray-400 text-xs hidden sm:block"></i>
                    </button>

                    <div x-cloak x-show="userOpen" @click.away="userOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-52 bg-white/95 backdrop-blur-lg rounded-2xl shadow-xl border border-gray-100 py-1.5 z-50">
                        <div class="px-4 py-2.5 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('masyarakat.profil') }}"
                           class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <i class="ph ph-user text-gray-400"></i> Profil Saya
                        </a>
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

    @stack('scripts')
    <script src="{{ asset('js/interactive-display.js') }}"></script>
</div>{{-- end x-data sidebarOpen wrapper --}}

<x-chatbot />
</body>
</html>
