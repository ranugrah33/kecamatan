<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Panduan - Sistem Pelayanan Kecamatan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .prose h2 { font-weight: 800; font-size: 1.875rem; margin-top: 2rem; margin-bottom: 1rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem; }
        .prose h3 { font-weight: 700; font-size: 1.25rem; margin-top: 1.5rem; margin-bottom: 0.75rem; color: #334155; }
        .prose p { margin-bottom: 1rem; color: #475569; line-height: 1.7; }
        .prose li { color: #475569; margin-bottom: 0.5rem; line-height: 1.7; }
        .prose ul, .prose ol { padding-left: 1.5rem; margin-bottom: 1.5rem; }
        .prose ul { list-style-type: disc; }
        .prose ol { list-style-type: decimal; }
        .prose .step-card { background: white; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); }
        .toc-link.active { background-color: #eff6ff; color: #2563eb; font-weight: 600; border-left: 3px solid #2563eb; }
    </style>
</head>
<body class="bg-slate-50 antialiased text-slate-800" x-data="{ mobileMenuOpen: false }">

    <!-- Top Navbar (Mobile only) -->
    <div class="lg:hidden bg-white border-b border-gray-200 sticky top-0 z-50 flex items-center justify-between p-4 shadow-sm">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white">
                <i class="ph ph-book-open-text text-lg"></i>
            </div>
            <span class="font-bold text-slate-800">Buku Panduan</span>
        </div>
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-slate-500 hover:text-slate-800 focus:outline-none">
            <i class="ph ph-list text-2xl" x-show="!mobileMenuOpen"></i>
            <i class="ph ph-x text-2xl" x-show="mobileMenuOpen" x-cloak></i>
        </button>
    </div>

    <div class="flex h-screen overflow-hidden relative">
        <!-- Sidebar Navigation -->
        <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 w-72 bg-white border-r border-gray-200 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:h-screen shadow-lg lg:shadow-none">
            <!-- Sidebar Header -->
            <div class="p-6 border-b border-gray-100 hidden lg:flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-md">
                    <i class="ph ph-book-open-text text-xl"></i>
                </div>
                <div>
                    <h1 class="font-extrabold text-[15px] text-slate-800 leading-tight">Buku Panduan</h1>
                    <p class="text-[12px] text-slate-500">Kecamatan Cikampek</p>
                </div>
            </div>

            <!-- Sidebar Links -->
            <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1" id="toc">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 px-3 mt-2">Daftar Isi</div>
                
                <a href="#pendahuluan" class="toc-link flex items-center gap-3 px-3 py-2.5 text-[14px] text-slate-600 rounded-lg hover:bg-slate-50 transition-colors" @click="mobileMenuOpen = false">
                    <i class="ph ph-info text-lg"></i> Pendahuluan
                </a>
                <a href="#daftar-login" class="toc-link flex items-center gap-3 px-3 py-2.5 text-[14px] text-slate-600 rounded-lg hover:bg-slate-50 transition-colors" @click="mobileMenuOpen = false">
                    <i class="ph ph-user-plus text-lg"></i> Daftar & Login
                </a>
                <a href="#beranda" class="toc-link flex items-center gap-3 px-3 py-2.5 text-[14px] text-slate-600 rounded-lg hover:bg-slate-50 transition-colors" @click="mobileMenuOpen = false">
                    <i class="ph ph-squares-four text-lg"></i> Beranda (Dashboard)
                </a>
                <a href="#profil" class="toc-link flex items-center gap-3 px-3 py-2.5 text-[14px] text-slate-600 rounded-lg hover:bg-slate-50 transition-colors" @click="mobileMenuOpen = false">
                    <i class="ph ph-user-circle text-lg"></i> Melengkapi Profil
                </a>
                
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 px-3 mt-6">Layanan Publik</div>
                
                <a href="#layanan-aula" class="toc-link flex items-center gap-3 px-3 py-2.5 text-[14px] text-slate-600 rounded-lg hover:bg-slate-50 transition-colors" @click="mobileMenuOpen = false">
                    <i class="ph ph-building text-lg"></i> Peminjaman Aula
                </a>
                <a href="#layanan-inventaris" class="toc-link flex items-center gap-3 px-3 py-2.5 text-[14px] text-slate-600 rounded-lg hover:bg-slate-50 transition-colors" @click="mobileMenuOpen = false">
                    <i class="ph ph-package text-lg"></i> Peminjaman Inventaris
                </a>
                <a href="#layanan-sertifikat" class="toc-link flex items-center gap-3 px-3 py-2.5 text-[14px] text-slate-600 rounded-lg hover:bg-slate-50 transition-colors" @click="mobileMenuOpen = false">
                    <i class="ph ph-certificate text-lg"></i> Sertifikat / Piagam
                </a>
                
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 px-3 mt-6">Informasi & Bantuan</div>

                <a href="#status-riwayat" class="toc-link flex items-center gap-3 px-3 py-2.5 text-[14px] text-slate-600 rounded-lg hover:bg-slate-50 transition-colors" @click="mobileMenuOpen = false">
                    <i class="ph ph-clock-counter-clockwise text-lg"></i> Cek Status Pengajuan
                </a>
                <a href="#chatbot" class="toc-link flex items-center gap-3 px-3 py-2.5 text-[14px] text-slate-600 rounded-lg hover:bg-slate-50 transition-colors" @click="mobileMenuOpen = false">
                    <i class="ph ph-robot text-lg"></i> Bantuan Chatbot AI
                </a>
            </div>

            <div class="p-4 border-t border-gray-100">
                <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2 bg-slate-800 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-700 transition-colors">
                    <i class="ph ph-arrow-left"></i> Kembali ke Aplikasi
                </a>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div x-show="mobileMenuOpen" class="fixed inset-0 bg-slate-900/50 z-30 lg:hidden" @click="mobileMenuOpen = false" x-transition.opacity></div>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto scroll-smooth bg-slate-50 relative pb-20 lg:pb-0" id="main-content">
            
            <!-- Hero Header -->
            <div class="bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-800 py-16 px-8 lg:px-16 text-white relative overflow-hidden">
                <!-- Abstract Wave/Curve SVG -->
                <svg class="absolute bottom-0 left-0 w-full opacity-10 pointer-events-none" viewBox="0 0 1440 320" preserveAspectRatio="none">
                    <path fill="#ffffff" fill-opacity="1" d="M0,224L48,213.3C96,203,192,181,288,186.7C384,192,480,224,576,213.3C672,203,768,149,864,128C960,107,1056,117,1152,144C1248,171,1344,213,1392,234.7L1440,256L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
                
                <div class="max-w-3xl mx-auto relative z-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-[13px] font-medium text-blue-50 mb-4 border border-white/10">
                        <i class="ph ph-book text-sm"></i> Panduan Resmi
                    </div>
                    <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight mb-4 drop-shadow-md">Cara Menggunakan Aplikasi Pelayanan Kecamatan</h1>
                    <p class="text-blue-100 text-lg max-w-2xl leading-relaxed">
                        Langkah demi langkah menggunakan sistem digital untuk kemudahan akses layanan permohonan Anda.
                    </p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="max-w-3xl mx-auto px-6 lg:px-12 py-12 prose prose-blue prose-lg">
                
                <!-- Section 1 -->
                <section id="pendahuluan" class="scroll-mt-24 mb-16">
                    <h2><i class="ph ph-info text-blue-600"></i> Pendahuluan</h2>
                    <p>Selamat datang di <strong>Sistem Pelayanan Masyarakat Kecamatan Cikampek</strong>. Sistem ini dirancang untuk memudahkan warga dalam mengajukan berbagai layanan administratif tanpa harus selalu datang langsung ke kantor kecamatan. Dengan sistem ini, pengajuan menjadi lebih transparan, mudah, dan cepat.</p>
                </section>

                <!-- Section 2 -->
                <section id="daftar-login" class="scroll-mt-24 mb-16">
                    <h2><i class="ph ph-user-plus text-blue-600"></i> Mendaftar & Masuk Akun</h2>
                    <p>Sebelum dapat mengajukan permohonan, Anda wajib memiliki akun di sistem kami.</p>
                    
                    <div class="step-card">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 shrink-0 bg-blue-100 text-blue-600 font-bold rounded-full flex items-center justify-center text-lg">1</div>
                            <div>
                                <h3>Membuat Akun Baru</h3>
                                <p>Jika Anda belum pernah menggunakan aplikasi ini:</p>
                                <ol>
                                    <li>Buka halaman awal lalu klik tombol <strong>Daftar Akun Baru</strong>.</li>
                                    <li>Isi data diri Anda secara lengkap seperti Nama, NIK, dan Email.</li>
                                    <li>Buat password yang mudah Anda ingat namun aman.</li>
                                    <li>Klik <strong>Daftar</strong>.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="step-card">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 shrink-0 bg-indigo-100 text-indigo-600 font-bold rounded-full flex items-center justify-center text-lg">2</div>
                            <div>
                                <h3>Masuk ke Aplikasi (Login)</h3>
                                <p>Setelah punya akun, Anda bisa langsung masuk:</p>
                                <ol>
                                    <li>Buka halaman <strong>Login</strong>.</li>
                                    <li>Masukkan <strong>Email</strong> dan <strong>Password</strong> yang sudah didaftarkan.</li>
                                    <li>Klik <strong>Masuk</strong>. Anda akan diarahkan ke Beranda.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 3 -->
                <section id="beranda" class="scroll-mt-24 mb-16">
                    <h2><i class="ph ph-squares-four text-blue-600"></i> Memahami Beranda</h2>
                    <p>Beranda (Dashboard) adalah pusat informasi Anda. Di sini Anda bisa dengan cepat melihat:</p>
                    <ul>
                        <li><strong>Akses Cepat Layanan:</strong> Tombol-tombol besar untuk langsung menuju layanan yang Anda butuhkan.</li>
                        <li><strong>Notifikasi:</strong> Pemberitahuan penting jika permohonan Anda disetujui, ditolak, atau butuh tindakan.</li>
                        <li><strong>Riwayat Singkat:</strong> Daftar permohonan terakhir yang Anda lakukan beserta statusnya saat ini.</li>
                    </ul>
                </section>

                <!-- Section 4 -->
                <section id="profil" class="scroll-mt-24 mb-16">
                    <h2><i class="ph ph-user-circle text-blue-600"></i> Melengkapi Profil</h2>
                    <p>Sangat penting untuk memastikan data profil Anda selalu valid. Data ini akan digunakan oleh pihak kecamatan untuk menghubungi Anda jika diperlukan.</p>
                    <ol>
                        <li>Klik menu <strong>Profil</strong> di navigasi sebelah kiri.</li>
                        <li>Perbarui data seperti <strong>Nomor Telepon/WhatsApp</strong> dan <strong>Alamat</strong>.</li>
                        <li>Klik tombol <strong>Simpan Perubahan</strong>.</li>
                    </ol>
                    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg mt-4">
                        <p class="text-amber-800 text-sm font-medium !m-0 flex items-center gap-2">
                            <i class="ph ph-warning-circle text-lg"></i>
                            Admin kecamatan tidak akan memproses pengajuan jika nomor telepon Anda tidak valid/tidak bisa dihubungi.
                        </p>
                    </div>
                </section>

                <hr class="my-12 border-gray-200">

                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-slate-800">Panduan Pengajuan Layanan</h2>
                    <p class="text-slate-500 mt-2">Pilih jenis layanan yang ingin Anda ajukan di bawah ini.</p>
                </div>

                <!-- Section 5 -->
                <section id="layanan-aula" class="scroll-mt-24 mb-16">
                    <h2><i class="ph ph-building text-blue-600"></i> Peminjaman Aula</h2>
                    <p>Layanan ini digunakan jika Anda (pribadi/organisasi/komunitas) ingin menggunakan fasilitas Aula Kecamatan Cikampek untuk kegiatan tertentu.</p>
                    
                    <div class="step-card bg-blue-50/30 border-blue-100">
                        <ol class="!m-0">
                            <li>Buka menu <strong>Layanan > Peminjaman Aula</strong>.</li>
                            <li>Klik tombol <strong>Ajukan Peminjaman</strong>.</li>
                            <li>Isi form yang disediakan: Tanggal kegiatan, nama instansi, tujuan kegiatan, dsb.</li>
                            <li>Unggah (Upload) file <strong>Surat Permohonan Resmi</strong> jika Anda mewakili instansi/organisasi.</li>
                            <li>Klik <strong>Kirim Pengajuan</strong>.</li>
                        </ol>
                    </div>
                </section>

                <!-- Section 6 -->
                <section id="layanan-inventaris" class="scroll-mt-24 mb-16">
                    <h2><i class="ph ph-package text-blue-600"></i> Peminjaman Inventaris</h2>
                    <p>Layanan untuk meminjam barang milik kecamatan, seperti kursi, proyektor, sound system, dll.</p>
                    
                    <div class="step-card bg-indigo-50/30 border-indigo-100">
                        <ol class="!m-0">
                            <li>Buka menu <strong>Layanan > Peminjaman Inventaris</strong>.</li>
                            <li>Klik <strong>Ajukan Peminjaman</strong>.</li>
                            <li>Pilih barang yang tersedia dan masukkan jumlah yang ingin dipinjam.</li>
                            <li>Tentukan <strong>Tanggal Pinjam</strong> dan <strong>Tanggal Kembali</strong>.</li>
                            <li>Sertakan keterangan keperluan.</li>
                            <li>Klik <strong>Kirim Pengajuan</strong>.</li>
                        </ol>
                    </div>
                </section>

                <!-- Section 7 -->
                <section id="layanan-sertifikat" class="scroll-mt-24 mb-16">
                    <h2><i class="ph ph-certificate text-blue-600"></i> Sertifikat / Piagam</h2>
                    <p>Untuk mengajukan pembuatan surat keterangan, sertifikat, atau piagam penghargaan dari kecamatan.</p>
                    
                    <div class="step-card bg-purple-50/30 border-purple-100">
                        <ol class="!m-0">
                            <li>Buka menu <strong>Layanan > Sertifikat / Piagam</strong>.</li>
                            <li>Klik <strong>Buat Permohonan</strong>.</li>
                            <li>Isi jenis dokumen yang dibutuhkan dan lengkapi deskripsinya.</li>
                            <li>Lampirkan dokumen pendukung (KTP, KK, dsb) dalam bentuk PDF atau Gambar.</li>
                            <li>Klik <strong>Kirim Permohonan</strong>.</li>
                        </ol>
                        <p class="mt-4 text-sm text-purple-700 bg-purple-100 p-3 rounded-lg border border-purple-200">
                            <i class="ph ph-download-simple"></i> <strong>Tips:</strong> Jika status permohonan disetujui, Anda bisa mendownload soft-copy sertifikat langsung dari aplikasi.
                        </p>
                    </div>
                </section>

                <hr class="my-12 border-gray-200">

                <!-- Section 8 -->
                <section id="status-riwayat" class="scroll-mt-24 mb-16">
                    <h2><i class="ph ph-clock-counter-clockwise text-blue-600"></i> Melacak Status Pengajuan</h2>
                    <p>Anda tidak perlu datang ke kantor untuk menanyakan proses pengajuan. Semua bisa dilacak lewat aplikasi.</p>
                    <ul>
                        <li>Buka menu <strong>Riwayat</strong>.</li>
                        <li>Anda akan melihat daftar pengajuan dengan berbagai status:
                            <ul class="mt-2 space-y-2">
                                <li><span class="inline-block px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-xs font-bold">Menunggu Persetujuan</span> : Berkas Anda sedang dalam antrean pemeriksaan.</li>
                                <li><span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-bold">Disetujui</span> : Permohonan diterima. Anda bisa melanjutkan proses selanjutnya.</li>
                                <li><span class="inline-block px-2 py-0.5 bg-rose-100 text-rose-700 rounded text-xs font-bold">Ditolak</span> : Berkas tidak memenuhi syarat. Klik untuk melihat catatan alasan penolakan.</li>
                                <li><span class="inline-block px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-xs font-bold">Selesai</span> : Seluruh proses telah berakhir dengan sukses.</li>
                            </ul>
                        </li>
                    </ul>
                </section>

                <!-- Section 9 -->
                <section id="chatbot" class="scroll-mt-24 mb-16">
                    <h2><i class="ph ph-robot text-blue-600"></i> Bantuan Chatbot AI</h2>
                    <p>Masih kebingungan atau butuh info cepat soal kecamatan?</p>
                    <p>Di pojok kanan bawah aplikasi, terdapat ikon <strong>Chatbot AI</strong>. Anda bisa mengetik pertanyaan apa saja seputar pelayanan kecamatan (contoh: <em>"Berapa maksimal pinjam kursi?"</em> atau <em>"Apa syarat buat surat kematian?"</em>).</p>
                    <p>Chatbot akan membalas secara instan berdasarkan pusat pengetahuan (Knowledge Base) resmi kecamatan.</p>
                </section>

                <div class="mt-20 p-8 bg-blue-600 rounded-2xl text-center text-white shadow-xl shadow-blue-600/20">
                    <i class="ph ph-check-circle text-5xl mb-4"></i>
                    <h3 class="text-2xl font-bold mb-2">Anda Sudah Siap!</h3>
                    <p class="text-blue-100 mb-6">Sekarang Anda sudah memahami cara menggunakan aplikasi pelayanan ini.</p>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-white text-blue-600 px-6 py-3 rounded-xl font-bold hover:bg-blue-50 transition-colors shadow-lg">
                        Mulai Gunakan Aplikasi <i class="ph ph-arrow-right"></i>
                    </a>
                </div>
                
                <div class="h-20"></div> <!-- spacing at bottom -->
            </div>
        </main>
    </div>

    <!-- Script for smooth scroll & active state tracking -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('.toc-link');
            const mainContent = document.getElementById('main-content');

            mainContent.addEventListener('scroll', () => {
                let current = '';
                
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    // Adjust offset based on padding
                    if (mainContent.scrollTop >= sectionTop - 120) {
                        current = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href').substring(1) === current) {
                        link.classList.add('active');
                    }
                });
            });
            
            // Trigger initial scroll to set active
            mainContent.dispatchEvent(new Event('scroll'));
        });
    </script>
</body>
</html>
