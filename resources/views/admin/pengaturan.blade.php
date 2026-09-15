@extends('layouts.admin')

@section('title', 'Pengaturan Admin')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Pengaturan Sistem</h1>
    <p class="text-sm text-gray-500 mt-1">Kelola profil administrator dan konfigurasi dasar aplikasi.</p>
</div>

@if (session('success'))
    <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200">
        {{ session('success') }}
    </div>
@endif
@if ($errors->any())
    <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ activeTab: 'profil' }">
    <!-- Sidebar Pengaturan -->
    <div class="lg:col-span-1">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Menu Pengaturan</h3>
            </div>
            <ul class="flex flex-col py-2">
                <li>
                    <a href="#" @click.prevent="activeTab = 'profil'" :class="activeTab === 'profil' ? 'text-blue-700 bg-blue-50 border-blue-600' : 'text-gray-600 hover:text-blue-600 hover:bg-gray-50 border-transparent hover:border-blue-300'" class="flex items-center gap-3 px-5 py-3 text-sm font-medium border-l-2 transition">
                        <i class="ph ph-user-circle text-lg"></i> Profil Administrator
                    </a>
                </li>
                <li>
                    <a href="#" @click.prevent="activeTab = 'aula'" :class="activeTab === 'aula' ? 'text-blue-700 bg-blue-50 border-blue-600' : 'text-gray-600 hover:text-blue-600 hover:bg-gray-50 border-transparent hover:border-blue-300'" class="flex items-center gap-3 px-5 py-3 text-sm font-medium border-l-2 transition">
                        <i class="ph ph-buildings text-lg"></i> Fasilitas Aula
                    </a>
                </li>
                <li>
                    <a href="#" @click.prevent="activeTab = 'password'" :class="activeTab === 'password' ? 'text-blue-700 bg-blue-50 border-blue-600' : 'text-gray-600 hover:text-blue-600 hover:bg-gray-50 border-transparent hover:border-blue-300'" class="flex items-center gap-3 px-5 py-3 text-sm font-medium border-l-2 transition">
                        <i class="ph ph-shield-check text-lg"></i> Keamanan & Password
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Konten Pengaturan -->
    <div class="lg:col-span-2">
        <!-- Tab: Profil Administrator -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm" x-show="activeTab === 'profil'" x-cloak>
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Profil Administrator</h2>
                <span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-md">Admin Utama</span>
            </div>
            
            <form action="{{ route('admin.pengaturan.profil') }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ Auth::user()->name }}" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ Auth::user()->email }}" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role / Peran</label>
                            <input type="text" value="Administrator" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-500" disabled>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab: Fasilitas Aula -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm" x-show="activeTab === 'aula'" x-cloak>
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Konfigurasi Fasilitas Aula</h2>
            </div>
            
            <form action="{{ route('admin.pengaturan.aula') }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Aula</label>
                            <input type="text" name="nama" value="{{ $aula->nama ?? '' }}" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Maksimal (Orang)</label>
                            <input type="number" name="kapasitas" value="{{ $aula->kapasitas ?? '' }}" min="1" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jam Buka</label>
                                <input type="time" name="jam_buka" value="{{ isset($aula->jam_buka) ? substr($aula->jam_buka, 0, 5) : '' }}" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jam Tutup</label>
                                <input type="time" name="jam_tutup" value="{{ isset($aula->jam_tutup) ? substr($aula->jam_tutup, 0, 5) : '' }}" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fasilitas Tersedia (Pisahkan dengan koma)</label>
                            <textarea name="fasilitas" rows="3" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>{{ isset($aula->fasilitas) ? implode(', ', (array)$aula->fasilitas) : '' }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Contoh: Meja, Kursi, Sound System, Mic, Kipas, AC</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab: Keamanan & Password -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm" x-show="activeTab === 'password'" x-cloak>
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Keamanan & Password</h2>
            </div>
            
            <form action="{{ route('admin.pengaturan.password') }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-6 max-w-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Lama</label>
                        <input type="password" name="password_lama" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                        <input type="password" name="password_baru" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                        <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="password_baru_confirmation" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
