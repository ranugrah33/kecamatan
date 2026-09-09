<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Masyarakat - Kecamatan Cikampek</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- AlpineJS for interactive components (Sidebar toggle, dropdowns) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="text-gray-800 antialiased bg-gray-50 flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out md:relative md:translate-x-0"
           :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
        <div class="h-16 flex items-center justify-between px-4 border-b border-gray-100 bg-blue-700 text-white md:bg-white md:text-gray-800 md:border-b-0">
            <span class="text-lg font-bold">Kec. Cikampek</span>
            <button @click="sidebarOpen = false" class="md:hidden text-white focus:outline-none">
                <i class="ph ph-x text-2xl"></i>
            </button>
        </div>

        <div class="overflow-y-auto h-full pb-20 pt-4 px-3">
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('masyarakat.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('masyarakat.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ph ph-squares-four text-xl"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('masyarakat.profil') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('masyarakat.profil') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ph ph-user text-xl"></i> Profil Saya
                    </a>
                </li>
                <li>
                    <a href="{{ route('masyarakat.layanan') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('masyarakat.layanan') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ph ph-folder-open text-xl"></i> Menu Pelayanan
                    </a>
                </li>
                <li>
                    <a href="{{ route('masyarakat.riwayat') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('masyarakat.riwayat') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ph ph-clock-counter-clockwise text-xl"></i> Riwayat Pelayanan
                    </a>
                </li>
                <li>
                    <a href="{{ route('masyarakat.notifikasi') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('masyarakat.notifikasi') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ph ph-bell text-xl"></i> Notifikasi
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Overlay for mobile sidebar -->
    <div x-cloak x-show="sidebarOpen" class="fixed inset-0 bg-gray-900/50 z-40 md:hidden" @click="sidebarOpen = false"></div>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 z-30">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="md:hidden text-gray-500 focus:outline-none">
                    <i class="ph ph-list text-2xl"></i>
                </button>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 font-medium hover:text-red-700 hover:bg-red-50 px-3 py-1.5 rounded-lg transition">Logout</button>
                </form>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

</body>
</html>
