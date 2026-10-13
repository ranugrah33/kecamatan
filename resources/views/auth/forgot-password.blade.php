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
                 style="background: rgba(6, 28, 22, 0.90); backdrop-filter: blur(28px) saturate(180%); -webkit-backdrop-filter: blur(28px) saturate(180%); border: 1px solid rgba(52, 211, 153, 0.28); border-top: 1px solid rgba(110, 231, 183, 0.48); box-shadow: 0 24px 60px -10px rgba(0, 0, 0, 0.8), 0 0 35px rgba(5, 150, 105, 0.2);">
                
                <!-- Card Header -->
                <div class="text-center mb-5">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/15 border border-amber-400/30 text-amber-400 flex items-center justify-center mx-auto mb-3 shadow-lg shadow-black/40">
                        <i class="ph ph-key text-3xl"></i>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-amber-500/15 border border-amber-400/30 text-[11px] font-bold uppercase tracking-wider text-amber-300 mb-2">
                        <i class="ph ph-shield-warning text-xs text-amber-400"></i>
                        <span>Keamanan Data Penduduk</span>
                    </div>

                    <h1 class="text-2xl sm:text-[26px] font-extrabold text-white tracking-tight leading-tight">
                        Lupa Kata Sandi?
                    </h1>
                    <p class="mt-1 text-xs sm:text-[13px] text-slate-300/90 leading-snug">
                        Demi perlindungan data kependudukan, pemulihan akun memerlukan verifikasi identitas resmi.
                    </p>
                </div>

                <!-- Info Box Panduan Pemulihan -->
                <div class="rounded-2xl p-4 sm:p-5 mb-5"
                     style="background: rgba(3, 20, 15, 0.80); border: 1px solid rgba(52, 211, 153, 0.25);">
                    <h2 class="text-xs sm:text-sm font-bold text-emerald-300 mb-3 flex items-center gap-2 uppercase tracking-wide">
                        <i class="ph ph-info text-base text-emerald-400"></i>
                        <span>Tata Cara Pemulihan Akun</span>
                    </h2>
                    <ul class="text-xs sm:text-[13px] text-slate-200 space-y-2.5">
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 flex items-center justify-center text-[11px] font-bold mt-0.5 shrink-0">1</span>
                            <span>Kunjungi langsung <strong>Kantor Camat Cikampek</strong> pada hari kerja (Senin – Jumat, 07.45 – 14.45 WIB).</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 flex items-center justify-center text-[11px] font-bold mt-0.5 shrink-0">2</span>
                            <span>Bawa <strong>KTP Asli</strong> Anda untuk verifikasi identitas fisik oleh petugas pelayanan umum.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 flex items-center justify-center text-[11px] font-bold mt-0.5 shrink-0">3</span>
                            <span>Petugas akan mereset kata sandi Anda secara langsung di sistem terpusat.</span>
                        </li>
                    </ul>
                </div>

                <!-- WhatsApp Helpdesk -->
                <div class="pt-3 border-t border-emerald-500/20 text-center">
                    <p class="text-xs text-slate-400 mb-2.5">
                        Butuh konfirmasi atau bantuan lebih lanjut? Hubungi Helpdesk Pelayanan:
                    </p>
                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-emerald-300 font-bold text-xs sm:text-sm transition-all mb-3 hover:scale-[1.02]"
                       style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(52, 211, 153, 0.3);">
                        <i class="ph ph-whatsapp-logo text-lg text-emerald-400"></i>
                        <span>WhatsApp Helpdesk Cikampek</span>
                    </a>

                    <div>
                        <a href="{{ route('login') }}" 
                           class="w-full inline-flex items-center justify-center gap-2 py-3 px-5 rounded-xl font-bold text-xs sm:text-sm text-white shadow-lg shadow-black/50 transition-all hover:scale-[1.01]"
                           style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: 1px solid rgba(110, 231, 183, 0.35);">
                            <i class="ph ph-arrow-left text-base"></i>
                            <span>Kembali ke Form Login</span>
                        </a>
                    </div>
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
