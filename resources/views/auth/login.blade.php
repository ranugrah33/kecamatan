@extends('layouts.app')

@section('content')
<div class="min-h-screen flex w-full relative overflow-hidden">
    {{-- Kolom Kiri: Branding & Image (TETAP SAMA) --}}
    <div class="hidden lg:flex w-[45%] relative items-center justify-center bg-blue-900">
        <!-- Background Image -->
        <div class="absolute inset-0">
            <img src="{{ asset('images/login-bg.jpg') }}" alt="Kecamatan Cikampek" class="w-full h-full object-cover" />
            <!-- Dark Blue Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-b from-blue-900/80 to-blue-900/95"></div>
        </div>

        <!-- Content Kiri -->
        <div class="relative z-10 px-12 text-white flex flex-col justify-center h-full max-w-xl">
            <!-- Logo/Icon -->
            <div class="w-16 h-16 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-8 border border-white/20">
                <i class="ph ph-buildings text-3xl text-white"></i>
            </div>
            
            <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight mb-3">Kecamatan Cikampek</h1>
            <h2 class="text-xl lg:text-2xl text-blue-100 font-medium mb-6">Sistem Pelayanan Masyarakat</h2>
            
            <div class="w-12 h-1 bg-blue-400 rounded-full mb-6"></div>
            
            <p class="text-blue-50/80 text-lg leading-relaxed">
                Pelayanan publik yang mudah, cepat, dan transparan.
            </p>
        </div>
    </div>

    {{-- Kolom Kanan: Form Login (REDESIGN) --}}
    <div class="flex-1 flex items-center justify-center relative bg-[#F8FAFC]">
        
        <!-- ================= DECORATIVE BACKGROUND ELEMENTS ================= -->
        <!-- Subtle Blue Blob Bottom Left -->
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-blue-400 rounded-full mix-blend-multiply filter blur-[100px] opacity-[0.08] pointer-events-none"></div>
        
        <!-- Subtle Indigo Blob Top Right -->
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-indigo-400 rounded-full mix-blend-multiply filter blur-[100px] opacity-[0.08] pointer-events-none"></div>
        
        <!-- Large Transparent Circle Outline (Dipertegas) -->
        <div class="absolute top-10 right-10 w-72 h-72 border-2 border-blue-200 rounded-full opacity-40 pointer-events-none"></div>
        <div class="absolute top-28 right-0 w-36 h-36 border-2 border-blue-300 rounded-full opacity-30 pointer-events-none"></div>
        
        <!-- Subtle Dotted Pattern (SVG background) -->
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#2563EB 1px, transparent 1px); background-size: 24px 24px;"></div>
        <!-- ================================================================ -->


        <!-- Floating Login Panel & Decorative Elements Container -->
        <div class="relative z-10 w-full max-w-[440px] mx-4 sm:mx-0">
            
            <!-- Main Card -->
            <div class="w-full px-8 py-10 bg-white/95 backdrop-blur-sm sm:rounded-[24px] sm:shadow-[0_8px_40px_rgb(37,99,235,0.08)] border border-white/80 relative">
                
                <!-- Decorative Abstract Shape inside card (Pojok kanan atas) -->
                <div class="absolute -top-4 -right-4 w-12 h-12 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl rotate-12 opacity-80 blur-[1px] -z-10"></div>
            <div class="mb-8 text-center sm:text-left">
                <!-- Mobile Logo -->
                <div class="lg:hidden w-14 h-14 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl flex items-center justify-center mb-6 mx-auto shadow-inner">
                    <i class="ph ph-buildings text-3xl text-blue-600"></i>
                </div>
                
                <h2 class="text-3xl font-extrabold text-gray-900 mb-2 tracking-tight">Selamat Datang 👋</h2>
                <p class="text-[15px] text-slate-500 font-medium">Masuk untuk mengakses layanan Kecamatan Cikampek</p>
                <div class="w-10 h-1 bg-blue-600 rounded-full mt-5 mx-auto sm:mx-0 opacity-80"></div>
            </div>

            <!-- Error Alert -->
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
                    <i class="ph ph-warning-circle text-red-500 text-lg mt-0.5"></i>
                    <div class="text-sm text-red-700 font-medium">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                
                <!-- Email Input -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-semibold text-slate-700">Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors duration-300">
                            <i class="ph ph-envelope-simple text-[20px]"></i>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" 
                            class="w-full pl-11 pr-4 py-3 bg-[#F8FAFC] border border-slate-200 rounded-xl text-[15px] text-slate-700 font-medium transition-all duration-300 outline-none focus:bg-white focus:border-blue-500 focus:ring-[4px] focus:ring-blue-500/15 placeholder-slate-400 hover:border-slate-300" 
                            placeholder="Masukkan email Anda" required>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-2" x-data="{ show: false }">
                    <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors duration-300">
                            <i class="ph ph-lock-key text-[20px]"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" name="password" id="password" 
                            class="w-full pl-11 pr-11 py-3 bg-[#F8FAFC] border border-slate-200 rounded-xl text-[15px] text-slate-700 font-medium transition-all duration-300 outline-none focus:bg-white focus:border-blue-500 focus:ring-[4px] focus:ring-blue-500/15 placeholder-slate-400 hover:border-slate-300" 
                            placeholder="Masukkan password" required>
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition-colors">
                            <i class="ph text-[20px]" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between pt-1 pb-2">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <div class="relative flex items-center justify-center">
                            <input type="checkbox" name="remember" class="peer appearance-none w-4.5 h-4.5 border-2 border-slate-300 rounded-[4px] checked:bg-blue-600 checked:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all cursor-pointer">
                            <i class="ph ph-check text-white text-xs absolute opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity"></i>
                        </div>
                        <span class="text-[14px] font-medium text-slate-600 group-hover:text-slate-800 transition-colors">Ingat saya</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-[14px] font-semibold text-blue-600 hover:text-blue-700 hover:underline transition-colors">
                        Lupa password?
                    </a>
                </div>

                <!-- Submit Button -->
                <div class="pt-1">
                    <button type="submit" 
                        class="w-full relative flex items-center justify-center py-3.5 px-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold text-[15px] rounded-xl shadow-[0_4px_12px_rgb(37,99,235,0.2)] hover:shadow-[0_6px_20px_rgb(37,99,235,0.3)] focus:outline-none focus:ring-[4px] focus:ring-blue-500/30 transition-all duration-300 transform hover:-translate-y-[1px] active:translate-y-0 overflow-hidden group">
                        <!-- Loading State (hidden by default) -->
                        <div class="absolute inset-0 flex items-center justify-center bg-blue-700 transition-opacity duration-300 opacity-0" id="loading-overlay">
                            <i class="ph ph-spinner animate-spin text-[22px]"></i>
                        </div>
                        <span class="transition-transform duration-300 group-active:scale-[0.98]" id="btn-text">Masuk</span>
                    </button>
                </div>
            </form>

            <!-- Register Link -->
            <div class="mt-8 text-center">
                <p class="text-[14px] font-medium text-slate-600">
                    Belum memiliki akun? 
                    <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-700 hover:underline transition-colors ml-1">
                        Daftar sekarang
                    </a>
                </p>
            </div>

        </div>
    </div>
</div>

<!-- Simple script to show loading on form submit -->
<script>
    document.querySelector('form').addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        btn.classList.add('cursor-wait');
        btn.querySelector('#loading-overlay').classList.remove('opacity-0');
        btn.querySelector('#btn-text').classList.add('opacity-0');
    });
</script>
@endsection
