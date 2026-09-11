<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard Admin Kecamatan Cikampek, Kabupaten Karawang">
    <title>@yield('title', 'Admin') – Kec. Cikampek</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                    <a href="{{ route('admin.dashboard') }}"
                       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'menu-active' : '' }}">
                        <i class="ph ph-squares-four menu-icon text-lg {{ request()->routeIs('admin.dashboard') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.peminjaman_aula.index') }}"
                       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.peminjaman_aula.*') ? 'menu-active' : '' }}">
                        <i class="ph ph-door-open menu-icon text-lg {{ request()->routeIs('admin.peminjaman_aula.*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                        Peminjaman Aula
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium">
                        <i class="ph ph-users menu-icon text-lg text-slate-400"></i>
                        Data Masyarakat
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium">
                        <i class="ph ph-hand-heart menu-icon text-lg text-slate-400"></i>
                        Bantuan Sosial
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium">
                        <i class="ph ph-map-trifold menu-icon text-lg text-slate-400"></i>
                        Jual Beli Tanah
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium">
                        <i class="ph ph-chart-bar menu-icon text-lg text-slate-400"></i>
                        Laporan
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium">
                        <i class="ph ph-gear menu-icon text-lg text-slate-400"></i>
                        Pengaturan
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Sidebar Bottom -->
        <div class="flex-shrink-0 px-3 pt-4 pb-5 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="menu-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-400 hover:bg-red-500/10 hover:text-red-300 transition text-left">
                    <i class="ph ph-sign-out text-lg"></i>
                    Keluar
                </button>
            </form>
            <div class="mt-4 mx-1 p-3 rounded-xl bg-white/5 border border-white/10">
                <p class="text-blue-300 text-xs font-semibold leading-tight">Bersama Melayani Masyarakat</p>
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
                <div>
                    <h1 class="text-sm font-bold text-gray-900 leading-tight">Dashboard Admin Kecamatan</h1>
                    <p class="text-xs text-gray-500 hidden sm:block leading-tight">Selamat datang di Sistem Informasi Pelayanan Kecamatan Cikampek.</p>
                </div>
            </div>

            <div class="flex items-center gap-2" x-data="{ userOpen: false }">
                <!-- Date/Time -->
                <div class="hidden md:flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2">
                    <i class="ph ph-calendar-blank text-blue-500 text-sm"></i>
                    <div class="text-right leading-tight">
                        <p class="text-[11px] text-gray-500" id="admin-date">Senin, 15 Sep 2025</p>
                        <p class="text-xs font-bold text-gray-800" id="admin-time">13:24 WIB</p>
                    </div>
                </div>

                <!-- Notification Bell -->
                <button class="relative p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                    <i class="ph ph-bell text-xl"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                </button>

                <!-- User Dropdown -->
                <div class="relative ml-1">
                    <button @click="userOpen = !userOpen"
                            class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                        <div class="w-8 h-8 bg-blue-700 rounded-full flex items-center justify-center text-white flex-shrink-0">
                            <i class="ph ph-user-circle text-lg"></i>
                        </div>
                        <div class="hidden sm:block text-left leading-tight">
                            <p class="text-sm font-semibold text-gray-800">Admin Kecamatan</p>
                            <p class="text-xs text-gray-500">Administrator</p>
                        </div>
                        <i class="ph ph-caret-down text-gray-400 text-xs hidden sm:block"></i>
                    </button>

                    <div x-cloak x-show="userOpen" @click.away="userOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-200/80 py-1.5 z-50">
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
        <main class="flex-1 overflow-y-auto overflow-x-hidden bg-[#f5f7fb] p-4 sm:p-6">
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
        }
        updateAdminTime();
        setInterval(updateAdminTime, 1000);
    })();
    </script>

    @stack('scripts')
</div>{{-- end x-data sidebarOpen wrapper --}}
</body>
</html>