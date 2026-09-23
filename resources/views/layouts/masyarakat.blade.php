<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal Layanan Publik Kecamatan Cikampek, Kabupaten Karawang">
    <title>@yield('title', 'Dashboard') – Kec. Cikampek</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="{{ asset('css/interactive-display.css') }}">
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        
        /* 1. Base Gradient Background (Zona Atas & Bawah) */
        .sidebar-nav {
            background: linear-gradient(180deg, #0b1429 0%, #0f1c3f 100%);
            overflow: hidden;
            position: relative;
        }

        /* 2. Layer Ilustrasi Gedung & Dekorasi (Zona Bawah-Tengah) */
        /* Memastikan ilustrasi TIDAK PERNAH menyentuh area tombol keluar (disisakan 160px di bawah) */
        .sidebar-nav::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 160px; /* Jarak aman mutlak untuk tombol Keluar */
            background-image: url('{{ asset('images/side-bar.png') }}');
            background-size: cover;
            background-position: center bottom;
            background-repeat: no-repeat;
            opacity: 0.9;
            z-index: 0;
            pointer-events: none;
            /* Transisi halus agar gambar menyatu dengan background polos di bawahnya */
            -webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 85%, rgba(0,0,0,0) 100%);
            mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 85%, rgba(0,0,0,0) 100%);
        }

        /* 3. Pastikan konten sidebar berada di atas background */
        .sidebar-nav > * {
            position: relative;
            z-index: 10;
        }

        /* 4. Area Khusus Tombol Keluar (Foreground Polos) */
        .sidebar-bottom-area {
            background: #0b152c; /* Background navy solid yang bersih */
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            z-index: 20;
        }

        .menu-active { 
            background: rgba(96,165,250,0.18); 
            color: #93c5fd; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .menu-active .menu-icon { color: #60a5fa; }
        .menu-item { 
            color: #94a3b8; 
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); 
        }
        .menu-item:hover { 
            background: rgba(255,255,255,0.07); 
            color: #e2e8f0; 
            transform: translateX(6px);
        }
        .menu-item:active {
            transform: scale(0.95);
        }
        .menu-item:hover .menu-icon { 
            color: #cbd5e1; 
            transform: scale(1.15) translateY(-1px);
        }
        .menu-icon {
            transition: all 0.3s ease;
        }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .welcome-card {
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            position: relative; overflow: hidden;
        }
        .welcome-card::before {
            content: ''; position: absolute; right: -30px; bottom: -40px;
            width: 180px; height: 180px; border-radius: 50%;
            background: rgba(147,197,253,0.25);
        }
        .welcome-card::after {
            content: ''; position: absolute; right: 60px; bottom: -60px;
            width: 140px; height: 140px; border-radius: 50%;
            background: rgba(165,180,252,0.2);
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
<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

    <!-- Mobile Overlay -->
    <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/50 md:hidden"></div>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar-nav fixed md:relative inset-y-0 left-0 z-50 w-64 flex flex-col flex-shrink-0
                  transition-transform duration-300 ease-in-out md:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <!-- Brand -->
        <div class="flex items-center gap-3 px-5 py-[18px] border-b border-white/10 flex-shrink-0">
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden shadow-sm">
                <img src="{{ asset('images/kop-surat.png') }}" alt="Logo Kabupaten Karawang"
                     class="w-8 h-8 object-contain"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div style="display:none" class="w-full h-full items-center justify-center bg-blue-700 rounded-xl">
                    <i class="ph ph-shield-star text-white text-lg"></i>
                </div>
            </div>
            <div class="min-w-0">
                <p class="text-white font-bold text-sm leading-tight">Kec. Cikampek</p>
                <p class="text-blue-300/80 text-xs leading-tight mt-0.5">Kabupaten Karawang</p>
            </div>
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
        <div class="sidebar-bottom-area flex-shrink-0 px-4 pt-6 pb-6">
            <!-- Background decoration kini menyatu di pseudo-element .sidebar-nav::before -->

            <div class="border-t border-white/10 pt-4 mb-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl text-[13px] font-bold text-red-400 border border-red-400/20 bg-red-400/5 hover:bg-red-500 hover:text-white active:scale-95 transition-all duration-300">
                        <i class="ph ph-sign-out text-lg"></i>
                        Keluar
                    </button>
                </form>
            </div>

            <!-- Tagline -->
            <div class="text-center px-2">
                <p class="text-blue-200/60 text-[11px] font-medium italic tracking-wide">"Melayani dengan Hati"</p>
                <p class="text-blue-300/40 text-[10px] mt-0.5">untuk Cikampek yang Lebih Baik</p>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN AREA ===== -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

        <!-- Header -->
        <header class="h-16 bg-white border-b border-gray-200/80 flex items-center justify-between px-4 sm:px-6 flex-shrink-0 z-30 shadow-sm">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen"
                        class="md:hidden text-gray-500 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition flex-shrink-0">
                    <i class="ph ph-list text-xl"></i>
                </button>
                <div class="hidden sm:block">
                    <h1 class="text-sm font-bold text-gray-900 leading-tight">@yield('title', 'Portal Layanan')</h1>
                    <p class="text-[11px] text-gray-500 font-medium mt-0.5">Sistem Informasi Pelayanan Masyarakat Kecamatan Cikampek.</p>
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
                         class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-200/80 py-1.5 z-50">
                        <div class="px-4 py-2.5 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('masyarakat.profil') }}"
                           class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
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
        <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('images/latar0.png') }}');">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    <script src="{{ asset('js/interactive-display.js') }}"></script>
</div>{{-- end x-data sidebarOpen wrapper --}}

<x-chatbot />
</body>
</html>
