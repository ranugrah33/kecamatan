@extends('layouts.app')

@section('content')
<div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 sm:px-6">
    <div class="absolute inset-0">
        <img src="{{ asset('images/gambar-kecamatan.png') }}" alt="Kecamatan Cikampek" class="h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-blue-950/35"></div>
    </div>

    <div class="relative z-10 w-full max-w-lg rounded-2xl border border-white/40 bg-blue-50/25 p-6 shadow-2xl shadow-blue-950/30 backdrop-blur-xl sm:p-8">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-black drop-shadow-md">Pendaftaran Akun</h1>
            <p class="mt-2 text-sm text-black drop-shadow">Masyarakat Kecamatan Cikampek</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200/50 bg-red-950/40 p-4 text-sm text-white" role="alert">
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
                <label for="nik" class="block text-sm font-medium text-black drop-shadow">NIK (Nomor Induk Kependudukan)</label>
                <input type="text" name="nik" id="nik" value="{{ old('nik') }}" class="mt-1 w-full rounded-lg border border-white/45 bg-white/20 px-4 py-2 text-sm text-black outline-none backdrop-blur-md transition placeholder:text-black/50 focus:border-white focus:bg-white/30 focus:ring-2 focus:ring-blue-200/60" placeholder="Masukkan 16 digit NIK" required>
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-black drop-shadow">Nama Lengkap</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border border-white/45 bg-white/20 px-4 py-2 text-sm text-black outline-none backdrop-blur-md transition placeholder:text-black/50 focus:border-white focus:bg-white/30 focus:ring-2 focus:ring-blue-200/60" placeholder="Sesuai KTP" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-black drop-shadow">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border border-white/45 bg-white/20 px-4 py-2 text-sm text-black outline-none backdrop-blur-md transition placeholder:text-black/50 focus:border-white focus:bg-white/30 focus:ring-2 focus:ring-blue-200/60" placeholder="Alamat Email Aktif" required>
            </div>
            <div>
                <label for="no_hp" class="block text-sm font-medium text-black drop-shadow">Nomor HP / WhatsApp</label>
                <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp') }}" class="mt-1 w-full rounded-lg border border-white/45 bg-white/20 px-4 py-2 text-sm text-black outline-none backdrop-blur-md transition placeholder:text-black/50 focus:border-white focus:bg-white/30 focus:ring-2 focus:ring-blue-200/60" placeholder="08xxxxxxxxxx" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-black drop-shadow">Password</label>
                <input type="password" name="password" id="password" class="mt-1 w-full rounded-lg border border-white/45 bg-white/20 px-4 py-2 text-sm text-black outline-none backdrop-blur-md transition placeholder:text-black/50 focus:border-white focus:bg-white/30 focus:ring-2 focus:ring-blue-200/60" placeholder="Minimal 8 karakter" required>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-black drop-shadow">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 w-full rounded-lg border border-white/45 bg-white/20 px-4 py-2 text-sm text-black outline-none backdrop-blur-md transition placeholder:text-black/50 focus:border-white focus:bg-white/30 focus:ring-2 focus:ring-blue-200/60" placeholder="Ulangi password" required>
            </div>

            <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-950/25 transition duration-150 hover:bg-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-200/60">
                Daftar Sekarang
            </button>
        </form>

        <div class="mt-4 text-center">
            <p class="text-sm text-black drop-shadow">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-medium text-blue-700 hover:text-blue-900 hover:underline">Login di sini</a>
            </p>
        </div>
    </div>
</div>
@endsection
