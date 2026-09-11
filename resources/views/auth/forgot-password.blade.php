@extends('layouts.app')

@section('content')
<div class="min-h-screen flex w-full relative overflow-hidden bg-[#F8FAFC]">
    
    <!-- Decorative Elements -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-400 rounded-full mix-blend-multiply filter blur-[100px] opacity-[0.08] pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-400 rounded-full mix-blend-multiply filter blur-[100px] opacity-[0.08] pointer-events-none"></div>
    <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#2563EB 1px, transparent 1px); background-size: 24px 24px;"></div>

    <div class="relative z-10 w-full max-w-lg mx-auto flex flex-col justify-center px-4 sm:px-0">
        
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl flex items-center justify-center mx-auto shadow-inner border border-blue-100 mb-4">
                <i class="ph ph-buildings text-3xl text-blue-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Kecamatan Cikampek</h1>
            <p class="text-slate-500 text-sm mt-1">Sistem Pelayanan Masyarakat</p>
        </div>

        <!-- Card -->
        <div class="w-full px-8 py-10 bg-white/95 backdrop-blur-sm rounded-[24px] shadow-[0_8px_40px_rgb(37,99,235,0.06)] border border-white/80">
            
            <div class="text-center mb-8">
                <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ph ph-key text-2xl"></i>
                </div>
                <h2 class="text-2xl font-extrabold text-gray-900 mb-2">Lupa Password?</h2>
                <p class="text-[15px] text-slate-500 font-medium leading-relaxed">
                    Demi alasan keamanan dan perlindungan data penduduk, sistem tidak menyediakan fitur *reset password* otomatis melalui email.
                </p>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 mb-8">
                <h3 class="text-sm font-bold text-blue-900 mb-3 flex items-center gap-2">
                    <i class="ph ph-info text-blue-600 text-lg"></i>
                    Panduan Pemulihan Akun
                </h3>
                <ul class="text-sm text-blue-800 space-y-3">
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-200 text-blue-700 flex items-center justify-center text-[10px] font-bold mt-0.5 flex-shrink-0">1</span>
                        <span>Silakan datang langsung ke <strong>Kantor Kecamatan Cikampek</strong> pada jam kerja.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-200 text-blue-700 flex items-center justify-center text-[10px] font-bold mt-0.5 flex-shrink-0">2</span>
                        <span>Bawa <strong>KTP Asli</strong> Anda untuk proses verifikasi identitas oleh petugas.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-200 text-blue-700 flex items-center justify-center text-[10px] font-bold mt-0.5 flex-shrink-0">3</span>
                        <span>Petugas akan membantu mengatur ulang kata sandi Anda di sistem.</span>
                    </li>
                </ul>
            </div>

            <div class="pt-2 text-center border-t border-slate-100">
                <p class="text-sm text-slate-500 mb-4">Butuh bantuan lain? Hubungi WhatsApp Admin di <br><strong class="text-slate-700">0812-XXXX-XXXX</strong></p>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-2.5 bg-white border border-slate-200 text-slate-700 font-bold text-sm rounded-xl hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-[4px] focus:ring-slate-100 transition-all">
                    <i class="ph ph-arrow-left mr-2 text-lg"></i>
                    Kembali ke Halaman Login
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
