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

    <!-- ===== TOP HEADER NAVIGATION ===== -->
    <header class="relative z-20 w-full px-4 sm:px-8 lg:px-10 py-3.5 sm:py-4">
        <div class="w-full flex items-center justify-between">
            <a href="{{ route('login') }}" 
               class="group inline-flex items-center gap-2 px-3.5 py-2 sm:px-4 sm:py-2.5 rounded-xl text-slate-200 hover:text-white transition-all duration-200 text-xs sm:text-sm font-semibold shadow-lg shadow-black/40 hover:scale-[1.02]"
               style="background: rgba(6, 36, 28, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(52, 211, 153, 0.3);">
                <i class="ph ph-arrow-left text-base text-emerald-400 group-hover:-translate-x-1 transition-transform"></i>
                <span>Kembali ke Halaman Login</span>
            </a>

            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Portal Resmi</span>
                    </div>
                    <h2 class="text-xs sm:text-sm font-extrabold text-white tracking-wide leading-tight">Pemerintah Kab. Karawang</h2>
                    <p class="text-[10.5px] text-slate-300">Kecamatan Cikampek</p>
                </div>
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-white/95 p-1 flex items-center justify-center shadow-lg shadow-black/40 border border-emerald-400/40 shrink-0">
                    <img src="{{ asset('images/kop-surat.png') }}" alt="Logo Karawang" class="w-full h-full object-contain"
                         onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </header>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="relative z-10 flex-1 flex items-center justify-center px-4 py-4 sm:py-6">
        <div class="w-full max-w-lg page-enter-reveal">
            
            <!-- Glass Card -->
            <div class="relative rounded-3xl p-6 sm:p-8"
                 style="background: rgba(6, 28, 22, 0.92); backdrop-filter: blur(28px) saturate(180%); -webkit-backdrop-filter: blur(28px) saturate(180%); border: 1px solid rgba(52, 211, 153, 0.28); border-top: 1px solid rgba(110, 231, 183, 0.48); box-shadow: 0 24px 60px -10px rgba(0, 0, 0, 0.8), 0 0 35px rgba(5, 150, 105, 0.2);">
                
                <!-- Card Header -->
                <div class="text-center mb-5">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-500/15 border border-emerald-400/30 text-emerald-400 flex items-center justify-center mx-auto mb-3 shadow-lg shadow-black/40">
                        <i class="ph ph-key text-3xl"></i>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-[11px] font-bold uppercase tracking-wider text-emerald-300 mb-2">
                        <i class="ph ph-shield-check text-xs text-emerald-400"></i>
                        <span>Pemulihan Akun Mandiri</span>
                    </div>

                    <h1 class="text-2xl sm:text-[26px] font-extrabold text-white tracking-tight leading-tight">
                        Lupa Kata Sandi?
                    </h1>
                    <p class="mt-1 text-xs sm:text-[13px] text-slate-300/90 leading-snug">
                        Masukkan Email atau Nomor HP terdaftar untuk membuat kata sandi baru.
                    </p>
                </div>

                <!-- Alert Notifikasi Error -->
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-500/35 bg-red-950/70 p-3.5 text-xs text-red-200 backdrop-blur-md shadow-lg" role="alert">
                        <div class="flex items-start gap-2.5">
                            <i class="ph ph-warning-circle text-base text-red-400 shrink-0 mt-0.5"></i>
                            <ul class="list-disc pl-3 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Form Reset Password Mandiri -->
                <form action="{{ route('password.update') }}" method="POST" class="space-y-4 mb-6">
                    @csrf

                    <!-- Identifier: Email atau Nomor HP -->
                    <div>
                        <label for="identifier" class="block text-xs font-bold uppercase tracking-wider text-emerald-300 mb-1 flex items-center gap-1.5">
                            <i class="ph ph-user-circle text-sm text-emerald-400"></i>
                            <span>Email atau Nomor HP / WhatsApp Terdaftar</span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   name="identifier" 
                                   id="identifier" 
                                   value="{{ old('identifier') }}" 
                                   required 
                                   autocomplete="username"
                                   placeholder="Contoh: nama@gmail.com atau 081234567890" 
                                   class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-400/70 outline-none transition-all duration-200 focus:ring-2 focus:ring-emerald-400/40"
                                   style="background: rgba(3, 20, 15, 0.80); border: 1px solid rgba(52, 211, 153, 0.3);">
                        </div>
                        <p class="mt-1 text-[11px] text-slate-400 flex items-center gap-1">
                            <i class="ph ph-info text-emerald-400"></i>
                            <span>Bisa gunakan email atau nomor WhatsApp saat pertama kali registrasi.</span>
                        </p>
                    </div>

                    <!-- Kata Sandi Baru & Konfirmasi Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Kata Sandi Baru -->
                        <div x-data="{ show: false }">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-emerald-300 mb-1 flex items-center gap-1.5">
                                <i class="ph ph-lock-key text-sm text-emerald-400"></i>
                                <span>Kata Sandi Baru</span>
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" 
                                       name="password" 
                                       id="password" 
                                       required 
                                       minlength="8"
                                       placeholder="Minimal 8 karakter" 
                                       class="w-full rounded-xl px-4 py-2.5 pr-10 text-sm text-white placeholder-slate-400/70 outline-none transition-all duration-200 focus:ring-2 focus:ring-emerald-400/40"
                                       style="background: rgba(3, 20, 15, 0.80); border: 1px solid rgba(52, 211, 153, 0.3);">
                                <button type="button" 
                                        @click="show = !show" 
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-emerald-400 transition-colors p-1"
                                        aria-label="Lihat kata sandi">
                                    <i class="ph text-base" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Konfirmasi Kata Sandi -->
                        <div x-data="{ show: false }">
                            <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-emerald-300 mb-1 flex items-center gap-1.5">
                                <i class="ph ph-check-circle text-sm text-emerald-400"></i>
                                <span>Konfirmasi Sandi</span>
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" 
                                       name="password_confirmation" 
                                       id="password_confirmation" 
                                       required 
                                       minlength="8"
                                       placeholder="Ulangi sandi baru" 
                                       class="w-full rounded-xl px-4 py-2.5 pr-10 text-sm text-white placeholder-slate-400/70 outline-none transition-all duration-200 focus:ring-2 focus:ring-emerald-400/40"
                                       style="background: rgba(3, 20, 15, 0.80); border: 1px solid rgba(52, 211, 153, 0.3);">
                                <button type="button" 
                                        @click="show = !show" 
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-emerald-400 transition-colors p-1"
                                        aria-label="Lihat konfirmasi kata sandi">
                                    <i class="ph text-base" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center gap-2 py-3 px-5 rounded-xl font-bold text-xs sm:text-sm text-white shadow-lg shadow-black/50 transition-all hover:scale-[1.01] active:scale-[0.99] cursor-pointer"
                                style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: 1px solid rgba(110, 231, 183, 0.45); box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.35);">
                            <i class="ph ph-check-circle text-lg"></i>
                            <span>Simpan & Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>

                <!-- Box Bantuan: Jika Lupa Email Maupun Nomor HP -->
                <div x-data="{ open: false }" 
                     class="rounded-2xl p-4 sm:p-4.5 border transition-all duration-300"
                     style="background: rgba(3, 20, 15, 0.75); border: 1px solid rgba(52, 211, 153, 0.22);">
                    
                    <button type="button" 
                            @click="open = !open" 
                            class="w-full flex items-center justify-between text-left text-xs sm:text-[13px] font-bold text-emerald-300 hover:text-emerald-200 transition-colors">
                        <div class="flex items-center gap-2">
                            <i class="ph ph-question text-base text-amber-400"></i>
                            <span>Lupa Email Maupun Nomor HP yang Terdaftar?</span>
                        </div>
                        <i class="ph ph-caret-down text-sm transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div x-show="open" x-cloak x-collapse class="mt-3 pt-3 border-t border-emerald-500/20 text-xs text-slate-300 space-y-3">
                        <p class="leading-relaxed">
                            Jika Anda lupa alamat email dan nomor HP Anda sudah tidak aktif lagi, jangan khawatir. Akun Anda dapat dipulihkan secara langsung melalui verifikasi petugas resmi:
                        </p>
                        
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-black/30 border border-emerald-500/15">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/15 border border-emerald-400/25 text-emerald-300 flex items-center justify-center shrink-0">
                                <i class="ph ph-buildings text-base text-emerald-400"></i>
                            </div>
                            <div>
                                <strong class="text-white block text-[13px] mb-0.5">Datang ke Kantor Camat Cikampek:</strong>
                                <span class="leading-relaxed text-slate-300">Kunjungi loket pelayanan pada hari kerja (Senin – Jumat, 07.45 – 14.45 WIB) dengan membawa KTP Asli untuk verifikasi identitas.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Link Kembali ke Login -->
                <div class="mt-5 text-center">
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-300 hover:text-emerald-300 transition-colors">
                        <i class="ph ph-arrow-left text-sm text-emerald-400"></i>
                        <span>Ingat kata sandi Anda? Kembali ke Login</span>
                    </a>
                </div>

            </div>

        </div>
    </main>

    <!-- ===== FOOTER MINIMALIS ===== -->
    <footer class="relative z-10 w-full py-3 px-4 text-center text-[11px] text-slate-400/80 border-t border-emerald-500/10 backdrop-blur-sm bg-black/30">
        <p>© {{ date('Y') }} Pemerintah Kecamatan Cikampek, Kabupaten Karawang. Hak Cipta Dilindungi.</p>
    </footer>
</div>
@endsection
