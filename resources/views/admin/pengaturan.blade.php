@extends('layouts.admin')

@section('title', 'Pengaturan Admin')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Pengaturan Sistem</h1>
    <p class="text-sm text-gray-500 mt-1">Kelola profil administrator dan konfigurasi dasar aplikasi.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Sidebar Pengaturan -->
    <div class="lg:col-span-1">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Menu Pengaturan</h3>
            </div>
            <ul class="flex flex-col py-2">
                <li>
                    <a href="#" class="flex items-center gap-3 px-5 py-3 text-sm font-medium text-blue-700 bg-blue-50 border-l-2 border-blue-600 transition">
                        <i class="ph ph-user-circle text-lg"></i> Profil Administrator
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center gap-3 px-5 py-3 text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-gray-50 border-l-2 border-transparent hover:border-blue-300 transition">
                        <i class="ph ph-buildings text-lg"></i> Fasilitas Aula
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center gap-3 px-5 py-3 text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-gray-50 border-l-2 border-transparent hover:border-blue-300 transition">
                        <i class="ph ph-shield-check text-lg"></i> Keamanan & Password
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Konten Pengaturan (Profil) -->
    <div class="lg:col-span-2">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Profil Administrator</h2>
                <span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-md">Admin Utama</span>
            </div>
            
            <form action="#" method="POST" class="p-6">
                @csrf
                <div class="space-y-6">
                    <div class="flex items-center gap-6 pb-6 border-b border-gray-100">
                        <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0 text-3xl">
                            <i class="ph ph-user"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Foto Profil</h3>
                            <p class="text-sm text-gray-500 mb-3">Disarankan ukuran 1:1, maksimal 2MB.</p>
                            <div class="flex gap-2">
                                <button type="button" class="px-4 py-1.5 bg-white border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">Ubah Foto</button>
                                <button type="button" class="px-4 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ Auth::user()->name }}" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 outline-none" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ Auth::user()->email }}" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 outline-none" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role / Peran</label>
                            <input type="text" value="Administrator" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-500" disabled>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-gray-100 flex justify-end">
                    <button type="button" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
