@extends('layouts.app')

@section('content')
<!-- Navbar -->
<nav class="bg-blue-800 text-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <span class="font-bold text-xl">Dashboard Admin Kecamatan</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm">Selamat datang, {{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-md text-sm font-medium transition">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex">
    <!-- Sidebar -->
    <div class="w-64 bg-white rounded-xl shadow-sm border border-gray-100 p-4 mr-6 hidden md:block">
        <ul class="space-y-2">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">Dashboard</a>
            </li>
            <li>
                <a href="{{ route('admin.peminjaman_aula.index') ?? '#' }}" class="block px-4 py-2 {{ request()->routeIs('admin.peminjaman_aula.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">Peminjaman Aula</a>
            </li>
            <li>
                <a href="#" class="block px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">Data Masyarakat</a>
            </li>
            <li>
                <a href="#" class="block px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">Pengaturan</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Dashboard</h2>
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">✅ Informasi bahwa dashboard admin berhasil dibuat. Saat ini Anda login sebagai <strong>Admin</strong>.</p>
        </div>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-6 rounded-lg border border-blue-100 text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $total_permohonan }}</div>
                <div class="text-sm text-gray-500 mt-1">Total Permohonan</div>
            </div>
            <div class="bg-yellow-50 p-6 rounded-lg border border-yellow-100 text-center">
                <div class="text-3xl font-bold text-yellow-600">{{ $menunggu_review }}</div>
                <div class="text-sm text-gray-500 mt-1">Menunggu Review</div>
            </div>
            <div class="bg-green-50 p-6 rounded-lg border border-green-100 text-center">
                <div class="text-3xl font-bold text-green-600">{{ $selesai }}</div>
                <div class="text-sm text-gray-500 mt-1">Selesai</div>
            </div>
        </div>
    </div>
</div>
@endsection
