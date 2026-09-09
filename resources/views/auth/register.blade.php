@extends('layouts.app')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100 py-10">
    <div class="w-full max-w-lg p-8 space-y-6 bg-white rounded-xl shadow-lg border border-gray-200">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900">Pendaftaran Akun</h1>
            <p class="text-sm text-gray-500 mt-2">Masyarakat Kecamatan Cikampek</p>
        </div>

        @if ($errors->any())
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="space-y-4" action="{{ route('register') }}" method="POST">
            @csrf
            <div>
                <label for="nik" class="block text-sm font-medium text-gray-700">NIK (Nomor Induk Kependudukan)</label>
                <input type="text" name="nik" id="nik" value="{{ old('nik') }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Masukkan 16 digit NIK" required>
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Sesuai KTP" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Alamat Email Aktif" required>
            </div>
            <div>
                <label for="no_hp" class="block text-sm font-medium text-gray-700">Nomor HP / WhatsApp</label>
                <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp') }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="08xxxxxxxxxx" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Minimal 8 karakter" required>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Ulangi password" required>
            </div>

            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition duration-150">
                Daftar Sekarang
            </button>
        </form>

        <div class="text-center">
            <p class="text-sm text-gray-600">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 hover:underline">Login di sini</a>
            </p>
        </div>
    </div>
</div>
@endsection
