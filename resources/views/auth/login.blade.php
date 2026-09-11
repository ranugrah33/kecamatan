@extends('layouts.app')

@section('content')
<div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 sm:px-6">
    <div class="absolute inset-0">
        <img src="{{ asset('images/gambar-kecamatan.png') }}" alt="Kecamatan Cikampek" class="h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-blue-950/35"></div>
    </div>

    <div class="relative z-10 w-full max-w-lg rounded-2xl border border-white/40 bg-blue-50/25 p-6 shadow-2xl shadow-blue-950/30 backdrop-blur-xl sm:p-8">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-black drop-shadow-md">Selamat Datang</h1>
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

        <form class="space-y-4" action="{{ route('login') }}" method="POST">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-black drop-shadow">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border border-white/45 bg-white/20 px-4 py-2 text-sm text-black outline-none backdrop-blur-md transition placeholder:text-black/50 focus:border-white focus:bg-white/30 focus:ring-2 focus:ring-blue-200/60" placeholder="Masukkan email Anda" required>
            </div>
            <div x-data="{ show: false }">
                <label for="password" class="block text-sm font-medium text-black drop-shadow">Password</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" name="password" id="password" class="mt-1 w-full rounded-lg border border-white/45 bg-white/20 px-4 py-2 pr-12 text-sm text-black outline-none backdrop-blur-md transition placeholder:text-black/50 focus:border-white focus:bg-white/30 focus:ring-2 focus:ring-blue-200/60" placeholder="Masukkan password" required>
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-4 text-black/70 transition hover:text-blue-700 focus:outline-none" :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
                        <i class="ph text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 py-1 text-sm">
                <label class="flex items-center gap-2 text-black">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-black/30 text-blue-600 focus:ring-blue-500/40">
                    <span>Ingat saya</span>
                </label>
                <a href="{{ route('password.request') }}" class="font-medium text-black hover:text-blue-700 hover:underline">Lupa password?</a>
            </div>

            <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-950/25 transition duration-150 hover:bg-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-200/60">
                Masuk
            </button>
        </form>

        <div class="mt-4 text-center">
            <p class="text-sm text-black drop-shadow">
                Belum memiliki akun?
                <a href="{{ route('register') }}" class="font-medium text-blue-700 hover:text-blue-900 hover:underline">Daftar sekarang</a>
            </p>
        </div>
    </div>
</div>
@endsection
