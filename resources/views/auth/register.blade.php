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
    <header class="relative z-20 w-full px-4 sm:px-8 py-4 sm:py-5">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="{{ url('/') }}" 
               class="group inline-flex items-center gap-2 px-3.5 py-2 sm:px-4 sm:py-2.5 rounded-xl text-slate-200 hover:text-white transition-all duration-200 text-xs sm:text-sm font-semibold shadow-lg shadow-black/40 hover:scale-[1.02]"
               style="background: rgba(6, 36, 28, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(52, 211, 153, 0.3);">
                <i class="ph ph-arrow-left text-base text-emerald-400 group-hover:-translate-x-1 transition-transform"></i>
                <span>Kembali ke Beranda</span>
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
                 style="background: rgba(6, 28, 22, 0.90); backdrop-filter: blur(28px) saturate(180%); -webkit-backdrop-filter: blur(28px) saturate(180%); border: 1px solid rgba(52, 211, 153, 0.28); border-top: 1px solid rgba(110, 231, 183, 0.48); box-shadow: 0 24px 60px -10px rgba(0, 0, 0, 0.8), 0 0 35px rgba(5, 150, 105, 0.2);">
                
                <!-- Card Header -->
                <div class="text-center mb-5">
                    <div class="w-14 h-14 rounded-2xl bg-white/95 p-1.5 mx-auto mb-3 shadow-xl shadow-black/40 border border-emerald-400/40 flex items-center justify-center transition-transform hover:scale-105">
                        <img src="{{ asset('images/kop-surat.png') }}" alt="Logo Karawang" class="w-full h-full object-contain"
                             onerror="this.style.display='none'">
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-[11px] font-bold uppercase tracking-wider text-emerald-300 mb-2">
                        <i class="ph ph-user-plus text-xs text-emerald-400"></i>
                        <span>Pendaftaran Akun Warga</span>
                    </div>

                    <h1 class="text-2xl sm:text-[26px] font-extrabold text-white tracking-tight leading-tight">
                        Buat Akun Baru
                    </h1>
                    <p class="mt-1 text-xs sm:text-[13px] text-slate-300/90 leading-snug">
                        Daftar untuk mengajukan peminjaman sarana dan permohonan sertifikat digital
                    </p>
                </div>

                <!-- Alert Notifikasi Error -->
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-500/35 bg-red-950/70 p-3.5 text-xs text-red-200 backdrop-blur-md shadow-lg" role="alert">
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

                <!-- Form Registrasi -->
                <form class="space-y-3.5" action="{{ route('register') }}" method="POST">
                    @csrf

                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-emerald-300 mb-1 flex items-center gap-1.5">
                            <i class="ph ph-user text-sm text-emerald-400"></i>
                            <span>Nama Lengkap</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name') }}" 
                               required 
                               autocomplete="name"
                               placeholder="Sesuai KTP" 
                               class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-400/70 outline-none transition-all duration-200 focus:ring-2 focus:ring-emerald-400/30"
                               style="background: rgba(3, 20, 15, 0.75); border: 1px solid rgba(52, 211, 153, 0.28);">
                    </div>

                    <!-- Email & No HP Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-emerald-300 mb-1 flex items-center gap-1.5">
                                <i class="ph ph-envelope-simple text-sm text-emerald-400"></i>
                                <span>Alamat Email</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autocomplete="email"
                                   placeholder="nama@email.com" 
                                   class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-400/70 outline-none transition-all duration-200 focus:ring-2 focus:ring-emerald-400/30"
                                   style="background: rgba(3, 20, 15, 0.75); border: 1px solid rgba(52, 211, 153, 0.28);">
                        </div>

                        <div>
                            <label for="no_hp" class="block text-xs font-bold uppercase tracking-wider text-emerald-300 mb-1 flex items-center gap-1.5">
                                <i class="ph ph-whatsapp-logo text-sm text-emerald-400"></i>
                                <span>Nomor HP / WhatsApp</span>
                            </label>
                            <input type="text" 
                                   name="no_hp" 
                                   id="no_hp" 
                                   value="{{ old('no_hp') }}" 
                                   required 
                                   placeholder="08xxxxxxxxxx" 
                                   class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-400/70 outline-none transition-all duration-200 focus:ring-2 focus:ring-emerald-400/30"
                                   style="background: rgba(3, 20, 15, 0.75); border: 1px solid rgba(52, 211, 153, 0.28);">
                        </div>
                    </div>

                    <!-- Kata Sandi & Konfirmasi Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div x-data="{ show: false }">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-emerald-300 mb-1 flex items-center gap-1.5">
                                <i class="ph ph-lock-key text-sm text-emerald-400"></i>
                                <span>Kata Sandi</span>
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" 
                                       name="password" 
                                       id="password" 
                                       required 
                                       autocomplete="new-password"
                                       placeholder="Min. 8 karakter" 
                                       class="w-full rounded-xl px-4 py-2.5 pr-10 text-sm text-white placeholder-slate-400/70 outline-none transition-all duration-200 focus:ring-2 focus:ring-emerald-400/30"
                                       style="background: rgba(3, 20, 15, 0.75); border: 1px solid rgba(52, 211, 153, 0.28);">
                                <button type="button" 
                                        @click="show = !show" 
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-emerald-300 transition-colors focus:outline-none">
                                    <i class="ph text-base" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                                </button>
                            </div>
                        </div>

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
                                       autocomplete="new-password"
                                       placeholder="Ulangi sandi" 
                                       class="w-full rounded-xl px-4 py-2.5 pr-10 text-sm text-white placeholder-slate-400/70 outline-none transition-all duration-200 focus:ring-2 focus:ring-emerald-400/30"
                                       style="background: rgba(3, 20, 15, 0.75); border: 1px solid rgba(52, 211, 153, 0.28);">
                                <button type="button" 
                                        @click="show = !show" 
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-emerald-300 transition-colors focus:outline-none">
                                    <i class="ph text-base" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Daftar Primary -->
                    <button type="submit" 
                            class="w-full group relative overflow-hidden rounded-xl py-3 px-5 font-bold text-white text-sm shadow-xl shadow-black/50 hover:shadow-emerald-500/30 transition-all duration-200 hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-emerald-400/50 mt-2"
                            style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: 1px solid rgba(110, 231, 183, 0.35);">
                        <span class="relative z-10 tracking-wide font-extrabold">Daftar Akun Baru</span>
                        <i class="ph ph-arrow-right text-base transition-transform group-hover:translate-x-1 relative z-10"></i>
                        <div class="absolute inset-0 bg-white/15 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </button>
                </form>

                <!-- Prompt Login -->
                <div class="mt-4 text-center text-xs text-slate-300">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}" 
                       class="font-bold text-emerald-400 hover:text-emerald-300 underline underline-offset-4 decoration-emerald-400/40 hover:decoration-emerald-300 transition-colors ml-1">
                        Masuk di sini
                    </a>
                </div>

            </div>

            <!-- Trust Footer Card -->
            <div class="mt-4 text-center text-[11px] text-slate-300/80 flex items-center justify-center gap-1.5">
                <i class="ph ph-shield-check text-emerald-400 text-sm"></i>
                <span>Data Warga Terlindungi Sistem Keamanan Terpadu Kec. Cikampek</span>
            </div>

        </div>
    </main>

    <!-- ===== FOOTER MINIMALIS ===== -->
    <footer class="relative z-10 w-full py-3 px-4 text-center text-[11px] text-slate-400/80 border-t border-emerald-500/10 backdrop-blur-sm bg-black/30">
        <p>© {{ date('Y') }} Pemerintah Kecamatan Cikampek, Kabupaten Karawang. Hak Cipta Dilindungi.</p>
    </footer>
</div>
@endsection
