@extends('layouts.app')

@section('content')
<div class="relative min-h-screen w-full flex flex-col justify-between overflow-x-hidden">
    <!-- ===== BACKGROUND SAMA DENGAN PORTAL BERANDA (play.jpeg + Obsidian Glass Tint) ===== -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat"
             style="background-image: 
                radial-gradient(circle at 50% 20%, rgba(16, 185, 129, 0.15) 0%, transparent 60%),
                linear-gradient(180deg, rgba(4, 20, 15, 0.42) 0%, rgba(4, 20, 15, 0.58) 50%, rgba(3, 16, 12, 0.78) 100%),
                url('{{ asset('images/play.jpeg') }}');">
        </div>
        <!-- Ambient Glowing Orb -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[550px] h-[320px] bg-emerald-500/12 rounded-full blur-[90px] pointer-events-none"></div>
    </div>

    <!-- ===== TOP HEADER NAVIGATION (Floating Glass Navbar) ===== -->
    <header class="relative z-20 w-full px-4 sm:px-8 py-3 sm:py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <!-- Tombol Kembali ke Portal -->
            <a href="{{ url('/') }}" 
               class="group inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-slate-200 hover:text-white transition-all duration-200 text-xs sm:text-sm font-semibold shadow-lg shadow-black/40 hover:scale-[1.02]"
               style="background: rgba(6, 36, 28, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(52, 211, 153, 0.3);">
                <i class="ph ph-arrow-left text-base text-emerald-400 group-hover:-translate-x-1 transition-transform"></i>
                <span>Kembali ke Beranda</span>
            </a>

            <!-- Identitas Resmi Karawang -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-right">
                    <h2 class="text-xs sm:text-sm font-extrabold text-white tracking-wide leading-tight">Pemerintah Kab. Karawang</h2>
                    <p class="text-[10.5px] text-slate-300">Kecamatan Cikampek</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/95 p-1 flex items-center justify-center shadow-lg shadow-black/40 border border-emerald-400/40 shrink-0">
                    <img src="{{ asset('images/kop-surat.png') }}" alt="Logo Karawang" class="w-full h-full object-contain"
                         onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </header>

    <!-- ===== MAIN CONTENT (Modern Obsidian Glassmorphism Card) ===== -->
    <main class="relative z-10 flex-1 flex items-center justify-center px-4 py-2 sm:py-4">
        <div class="w-full max-w-[420px] page-enter-reveal">
            
            <!-- Glass Card -->
            <div class="relative rounded-3xl p-5 sm:p-7"
                 style="background: rgba(6, 28, 22, 0.90); backdrop-filter: blur(28px) saturate(180%); -webkit-backdrop-filter: blur(28px) saturate(180%); border: 1px solid rgba(52, 211, 153, 0.28); border-top: 1px solid rgba(110, 231, 183, 0.48); box-shadow: 0 24px 60px -10px rgba(0, 0, 0, 0.8), 0 0 35px rgba(5, 150, 105, 0.2);">
                
                <!-- Card Header -->
                <div class="text-center mb-4">
                    <!-- Brand Icon Emblem -->
                    <div class="w-12 h-12 rounded-2xl bg-white/95 p-1 mx-auto mb-2.5 shadow-xl shadow-black/40 border border-emerald-400/40 flex items-center justify-center transition-transform hover:scale-105">
                        <img src="{{ asset('images/kop-surat.png') }}" alt="Logo Karawang" class="w-full h-full object-contain"
                             onerror="this.style.display='none'">
                    </div>

                    <!-- Category Pill -->
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-[10.5px] font-bold uppercase tracking-wider text-emerald-300 mb-1.5">
                        <i class="ph ph-shield-check text-xs text-emerald-400"></i>
                        <span>Pelayanan Masyarakat Digital</span>
                    </div>

                    <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight leading-tight">
                        Selamat Datang
                    </h1>
                    <p class="mt-1 text-xs text-slate-300/90 leading-snug">
                        Masuk untuk akses peminjaman & permohonan sertifikat terpadu
                    </p>
                </div>

                <!-- Alert Notifikasi Error -->
                @if ($errors->any())
                    <div class="mb-3.5 rounded-xl border border-red-500/35 bg-red-950/70 p-3 text-xs text-red-200 backdrop-blur-md shadow-lg" role="alert">
                        <div class="flex items-start gap-2">
                            <i class="ph ph-warning-circle text-base text-red-400 shrink-0 mt-0.5"></i>
                            <ul class="list-disc pl-3 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Form Login -->
                <form class="space-y-3" action="{{ route('login') }}" method="POST">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-[11.5px] font-bold uppercase tracking-wider text-emerald-300 mb-1 flex items-center gap-1.5">
                            <i class="ph ph-envelope-simple text-sm text-emerald-400"></i>
                            <span>Alamat Email</span>
                        </label>
                        <div class="relative">
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autocomplete="email"
                                   placeholder="nama@email.com" 
                                   class="w-full rounded-xl px-3.5 py-2 text-sm text-white placeholder-slate-400/70 outline-none transition-all duration-200 focus:ring-2 focus:ring-emerald-400/30"
                                   style="background: rgba(3, 20, 15, 0.75); border: 1px solid rgba(52, 211, 153, 0.28);">
                        </div>
                    </div>

                    <!-- Password Input with Alpine Toggle -->
                    <div x-data="{ show: false }">
                        <label for="password" class="block text-[11.5px] font-bold uppercase tracking-wider text-emerald-300 mb-1 flex items-center gap-1.5">
                            <i class="ph ph-lock-key text-sm text-emerald-400"></i>
                            <span>Kata Sandi</span>
                        </label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" 
                                   name="password" 
                                   id="password" 
                                   required 
                                   autocomplete="current-password"
                                   placeholder="••••••••" 
                                   class="w-full rounded-xl px-3.5 py-2 pr-10 text-sm text-white placeholder-slate-400/70 outline-none transition-all duration-200 focus:ring-2 focus:ring-emerald-400/30"
                                   style="background: rgba(3, 20, 15, 0.75); border: 1px solid rgba(52, 211, 153, 0.28);">
                            <button type="button" 
                                    @click="show = !show" 
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-emerald-300 transition-colors focus:outline-none" 
                                    :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                                <i class="ph text-base" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember & Forgot Password -->
                    <div class="flex items-center justify-between gap-4 pt-0.5 pb-0.5">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" 
                                   name="remember" 
                                   id="remember"
                                   class="w-3.5 h-3.5 rounded border-emerald-500/40 bg-[#03140f] text-emerald-500 focus:ring-emerald-400/30 focus:ring-offset-0 cursor-pointer">
                            <span class="text-xs text-slate-300 font-medium">Ingat saya</span>
                        </label>
                        <a href="{{ route('password.request') }}" 
                           class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 transition-colors">
                            Lupa kata sandi?
                        </a>
                    </div>

                    <!-- Tombol Masuk Primary -->
                    <button type="submit" 
                            class="w-full group relative overflow-hidden rounded-xl py-2.5 px-5 font-bold text-white text-sm shadow-xl shadow-black/50 hover:shadow-emerald-500/30 transition-all duration-200 hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-emerald-400/50 mt-1"
                            style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: 1px solid rgba(110, 231, 183, 0.35);">
                        <span class="relative z-10 tracking-wide font-extrabold">Masuk</span>
                        <i class="ph ph-arrow-right text-base transition-transform group-hover:translate-x-1 relative z-10"></i>
                        <div class="absolute inset-0 bg-white/15 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </button>
                </form>

                <!-- Prompt Registrasi -->
                <div class="mt-3.5 text-center text-xs text-slate-300">
                    Belum memiliki akun?
                    <a href="{{ route('register') }}" 
                       class="font-bold text-emerald-400 hover:text-emerald-300 underline underline-offset-4 decoration-emerald-400/40 hover:decoration-emerald-300 transition-colors ml-1">
                        Daftar sekarang
                    </a>
                </div>

                <!-- Pemisah Divider -->
                <div class="relative my-3 flex items-center justify-center">
                    <div class="w-full border-t border-emerald-500/20"></div>
                    <span class="absolute px-2.5 text-[10.5px] font-bold uppercase tracking-wider text-slate-400"
                          style="background: rgba(6, 28, 22, 0.98);">Atau</span>
                </div>

                <!-- Tombol Panduan -->
                <a href="{{ route('panduan') }}" 
                   class="w-full flex items-center justify-center gap-2 py-2 px-3.5 rounded-xl text-xs font-semibold text-slate-200 hover:text-white transition-all shadow-md hover:scale-[1.01]"
                   style="background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(52, 211, 153, 0.22);">
                    <i class="ph ph-book-open-text text-base text-emerald-400"></i>
                    <span>Baca Buku Panduan Pelayanan</span>
                </a>

            </div>

            <!-- Catatan Keamanan / Trust Footer Card -->
            <div class="mt-3 text-center text-[10.5px] text-slate-300/80 flex items-center justify-center gap-1.5">
                <i class="ph ph-lock-key text-emerald-400 text-xs"></i>
                <span>Enkripsi Aman SSL 256-bit • Server Resmi Kec. Cikampek</span>
            </div>

        </div>
    </main>

    <!-- ===== FOOTER MINIMALIS ===== -->
    <footer class="relative z-10 w-full py-2.5 px-4 text-center text-[11px] text-slate-400/80 border-t border-emerald-500/10 backdrop-blur-sm bg-black/30">
        <p>© {{ date('Y') }} Pemerintah Kecamatan Cikampek, Kabupaten Karawang. Hak Cipta Dilindungi.</p>
    </footer>
</div>
@endsection
