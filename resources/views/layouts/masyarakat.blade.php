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
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .sidebar-nav { background: linear-gradient(180deg, #1a2744 0%, #1e2d52 60%, #162040 100%); }
        .menu-active { background: rgba(96,165,250,0.18); color: #93c5fd; }
        .menu-active .menu-icon { color: #60a5fa; }
        .menu-item { color: #94a3b8; transition: all 0.15s ease; }
        .menu-item:hover { background: rgba(255,255,255,0.07); color: #e2e8f0; }
        .menu-item:hover .menu-icon { color: #cbd5e1; }
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
<body class="bg-[#f5f7fb] text-gray-800 antialiased">
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
                <img src="{{ asset('images/Kabupaten-Karawang-Logo.jpg') }}" alt="Logo Kabupaten Karawang"
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
        <div class="flex-shrink-0 px-4 pt-6 pb-6 relative z-10">
            <!-- Decorative Glow / Abstract Shape behind illustration -->
            <div class="absolute bottom-20 left-1/2 -translate-x-1/2 w-32 h-32 bg-blue-400 rounded-full blur-[40px] opacity-10 pointer-events-none"></div>

            <!-- Illustration: Gedung Kecamatan (Image Style) -->
            <div class="relative w-full h-32 mb-4 flex items-center justify-center overflow-hidden rounded-2xl border border-white/5 group">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0F1C3F]/80 via-transparent to-transparent z-10 pointer-events-none"></div>
                <img src="{{ asset('images/sidebar-bg.jpg') }}" alt="Gedung Kecamatan" class="w-full h-full object-cover object-center transform group-hover:scale-110 transition-transform duration-700 ease-in-out opacity-90">
            </div>

            <div class="border-t border-white/10 pt-4 mb-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl text-[13px] font-bold text-red-400 border border-red-400/20 bg-red-400/5 hover:bg-red-500 hover:text-white transition-all duration-300">
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
                        class="md:hidden text-gray-500 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition">
                    <i class="ph ph-list text-xl"></i>
                </button>
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
        <main class="flex-1 overflow-y-auto overflow-x-hidden bg-[#f5f7fb] p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

</div>{{-- end x-data sidebarOpen wrapper --}}
</body>
</html>
