<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal Layanan Publik Digital Kecamatan Cikampek, Kabupaten Karawang. Peminjaman aula, inventaris, dan layanan sertifikat secara online.">
    <title>Beranda — Kecamatan Cikampek | Kabupaten Karawang</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/beranda.css') }}">
    <link rel="stylesheet" href="{{ asset('css/statistik.css') }}">
</head>
<body class="beranda-page">

    <!-- ===== ANIMATED BACKGROUND ===== -->
    <div class="bg-animated"></div>
    <div class="particles"></div>

    <!-- ===== NAVIGASI (Glassmorphism Navbar) ===== -->
    <nav class="navbar" id="navbar">
        <a href="#beranda" class="nav-brand">
            <img src="{{ asset('images/kop-surat.png') }}" alt="Logo Karawang" class="nav-brand-logo"
                 onerror="this.style.display='none'">
            <div class="nav-brand-text">
                <span class="nav-brand-title">Kec. Cikampek</span>
                <span class="nav-brand-sub">Kabupaten Karawang</span>
            </div>
        </a>

        <ul class="nav-menu" id="navMenu">
            <li><a href="#beranda" class="active"><i class="ph ph-house"></i> Beranda</a></li>
            <li><a href="#statistik"><i class="ph ph-chart-bar"></i> Statistik</a></li>
            <li><a href="#layanan"><i class="ph ph-squares-four"></i> Layanan</a></li>
            <li><a href="#tentang"><i class="ph ph-info"></i> Tentang</a></li>
            <li class="nav-menu-mobile-cta">
                <div class="nav-cta mobile">
                    <a href="{{ route('login') }}" class="btn-login">Masuk</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-register">Daftar</a>
                    @endif
                </div>
            </li>
        </ul>

        <div class="nav-cta desktop" id="navCTA">
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn-register">Daftar</a>
            @endif
            <a href="{{ route('login') }}" class="btn-login">Masuk →</a>
        </div>

        <button class="nav-hamburger" id="navHamburger" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </nav>

    <!-- ===== HERO SECTION (Glassmorphism + Animasi 3D) ===== -->
    <section class="hero" id="beranda">
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-badge">
                    <span class="hero-badge-dot"></span>
                    Portal Digital Kecamatan
                </div>

                <h1 class="hero-title">
                    Layanan Publik<br>
                    yang <span class="gradient-text typewriter-text">Cepat</span>
                </h1>

                <p class="hero-description">
                    Sistem Pelayanan Masyarakat Kecamatan Cikampek, Kabupaten Karawang.
                    Akses layanan peminjaman aula, inventaris, dan sertifikat secara digital
                    — kapan saja, di mana saja.
                </p>

                <div class="hero-actions">
                    <a href="{{ route('login') }}" class="btn-hero-primary">
                        <i class="ph ph-sign-in"></i>
                        Masuk Sekarang
                    </a>
                    <a href="#layanan" class="btn-hero-secondary">
                        <i class="ph ph-arrow-down"></i>
                        Lihat Layanan
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== STATISTIK WILAYAH (Cikampek dalam Angka) & 10 DESA ===== -->
    <section class="section-statistik" id="statistik">
        <div class="stat-ambient-glow"></div>
        <div class="stat-container">
            
            <!-- Section Header (Sesuai Gambar) -->
            <div class="stat-header reveal">
                <div class="stat-badge">
                    <span class="stat-badge-line"></span>
                    <span class="stat-badge-text">STATISTIK WILAYAH</span>
                    <span class="stat-badge-line right"></span>
                </div>
                <h2 class="stat-main-title">
                    Cikampek dalam <span class="stat-title-gold">Angka</span>
                </h2>
                <p class="stat-subtitle">
                    Gambaran singkat wilayah dan masyarakat Kecamatan Cikampek.
                </p>

                <!-- Digital HUD Control Bar (Animasi Digital) -->
                <div class="stat-digital-hud">
                    <div class="hud-pill">
                        <span class="hud-pulse-dot"></span>
                        <span id="statDigitalClock">--:--:-- WIB</span>
                    </div>
                    <button type="button" class="hud-pill hud-pill-btn" id="statAudioToggle">
                        <i class="ph ph-speaker-slash"></i> Suara Digital: NONAKTIF
                    </button>
                    <button type="button" class="hud-pill hud-pill-btn" id="statReplayBtn">
                        <i class="ph ph-arrows-counter-clockwise"></i> Replay Animasi
                    </button>
                </div>
            </div>

            <!-- 4 Cards Statistik (Sesuai Gambar) -->
            <div class="stat-metrics-grid">
                <!-- Card 1: Jumlah Penduduk -->
                <div class="stat-card reveal reveal-delay-1">
                    <div>
                        <div class="stat-card-icon-wrap">
                            <i class="ph ph-users"></i>
                        </div>
                        <div class="stat-card-value-wrap">
                            <span class="stat-card-number" data-target="112780">0</span>
                            <span class="stat-card-unit">+ jiwa</span>
                        </div>
                        <div class="stat-card-title">Jumlah Penduduk</div>
                        <div class="stat-card-desc">Perkiraan 112.780 – 123.000 jiwa</div>
                    </div>
                    <div>
                        <div class="stat-card-bar-track">
                            <div class="stat-card-bar-fill" data-percent="75"></div>
                        </div>
                        <a href="#desaShowcase" class="stat-card-action">
                            <i class="ph ph-chart-line-up"></i> Data BPS 2024
                        </a>
                    </div>
                </div>

                <!-- Card 2: Luas Wilayah -->
                <div class="stat-card reveal reveal-delay-2">
                    <div>
                        <div class="stat-card-icon-wrap">
                            <i class="ph ph-map-trifold"></i>
                        </div>
                        <div class="stat-card-value-wrap">
                            <span class="stat-card-number" data-target="47.60" data-decimal="true">0,00</span>
                            <span class="stat-card-unit">km²</span>
                        </div>
                        <div class="stat-card-title">Luas Wilayah</div>
                        <div class="stat-card-desc">Sekitar 47,60 kilometer persegi</div>
                    </div>
                    <div>
                        <div class="stat-card-bar-track">
                            <div class="stat-card-bar-fill" data-percent="45"></div>
                        </div>
                        <a href="#desaShowcase" class="stat-card-action">
                            <i class="ph ph-compass"></i> Wilayah Strategis
                        </a>
                    </div>
                </div>

                <!-- Card 3: Desa -->
                <div class="stat-card reveal reveal-delay-3">
                    <div>
                        <div class="stat-card-icon-wrap">
                            <i class="ph ph-house-line"></i>
                        </div>
                        <div class="stat-card-value-wrap">
                            <span class="stat-card-number" data-target="10">0</span>
                            <span class="stat-card-unit">desa</span>
                        </div>
                        <div class="stat-card-title">Desa</div>
                        <div class="stat-card-desc">Tersebar di seluruh wilayah kecamatan</div>
                    </div>
                    <div>
                        <div class="stat-card-bar-track">
                            <div class="stat-card-bar-fill" data-percent="70"></div>
                        </div>
                        <a href="#desaShowcase" class="stat-card-action" id="exploreDesaBtn">
                            <i class="ph ph-squares-four"></i> Jelajahi 10 Desa ↓
                        </a>
                    </div>
                </div>

                <!-- Card 4: Jam Pelayanan -->
                <div class="stat-card reveal reveal-delay-4">
                    <div>
                        <div class="stat-card-icon-wrap">
                            <i class="ph ph-clock"></i>
                        </div>
                        <div class="stat-card-value-wrap">
                            <span class="stat-card-number">07.45</span>
                            <span class="stat-card-unit">- 14.45</span>
                        </div>
                        <div class="stat-card-title">Jam Pelayanan</div>
                        <div class="stat-card-desc">Waktu pelayanan di kantor kecamatan</div>
                    </div>
                    <div>
                        <div class="stat-card-bar-track">
                            <div class="stat-card-bar-fill" data-percent="42"></div>
                        </div>
                        <div class="stat-live-badge open" id="statLiveBadge">
                            <span class="hud-pulse-dot"></span> BUKA (Sedang Melayani)
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Information Pill Bar (Sesuai Gambar) -->
            <div class="stat-info-bar reveal">
                <div class="stat-info-item">
                    <i class="ph ph-map-pin"></i>
                    <span>Kabupaten Karawang, Jawa Barat</span>
                </div>
                <div class="stat-info-divider"></div>
                <div class="stat-info-item">
                    <i class="ph ph-envelope-simple"></i>
                    <span>Kode Pos 41373</span>
                </div>
                <div class="stat-info-divider"></div>
                <div class="stat-info-item">
                    <i class="ph ph-train"></i>
                    <span>Simpul jalur tol & kereta api</span>
                </div>
            </div>

            <!-- ===== EKSPLORASI 10 DESA CIKAMPEK ===== -->
            <div class="stat-desa-section" id="desaShowcase">
                <div class="stat-desa-header reveal">
                    <div>
                        <div class="stat-desa-badge">
                            <i class="ph ph-shield-check"></i> Direktori Wilayah Administratif
                        </div>
                        <h3 class="stat-desa-title">10 Desa di Kecamatan Cikampek</h3>
                        <p class="stat-desa-subtitle">
                            Mengenal karakteristik, potensi unggulan, dan fasilitas 10 desa di wilayah Kecamatan Cikampek.
                        </p>
                    </div>
                    <div class="stat-desa-count-badge" id="statDesaCount">
                        10 / 10 Desa
                    </div>
                </div>

                <!-- Controls: Search & Category Tabs -->
                <div class="stat-desa-controls reveal">
                    <div class="stat-desa-search-row">
                        <div class="stat-desa-search-box">
                            <i class="ph ph-magnifying-glass"></i>
                            <input type="text" id="desaSearchInput" class="stat-desa-search-input"
                                   placeholder="Cari nama desa, potensi, atau fasilitas..."
                                   autocomplete="off">
                        </div>
                        <div class="stat-desa-filters">
                            <button type="button" class="filter-tab active" data-filter="all">Semua Desa (10)</button>
                            <button type="button" class="filter-tab" data-filter="pusat">Pusat & Niaga (4)</button>
                            <button type="button" class="filter-tab" data-filter="industri">Industri & Tol (3)</button>
                            <button type="button" class="filter-tab" data-filter="pariwisata">Wisata & Agrikultur (3)</button>
                        </div>
                    </div>
                </div>

                <!-- 10 Desa Cards Grid -->
                <div class="stat-desa-grid" id="statDesaGrid">
                    <!-- Dinamis dirender oleh statistik.js -->
                </div>

                <!-- Empty State -->
                <div class="stat-desa-empty" id="statDesaEmpty">
                    <i class="ph ph-magnifying-glass" style="font-size: 36px; color: #d4af37; margin-bottom: 12px; display: block;"></i>
                    <p style="font-size: 16px; font-weight: 600; color: #ffffff;">Desa tidak ditemukan</p>
                    <p style="font-size: 13.5px; margin-top: 4px;">Coba gunakan kata kunci pencarian yang lain.</p>
                </div>
            </div>

        </div>
    </section>

    <!-- ===== MODAL DETAIL DIGITAL 10 DESA ===== -->
    <div class="stat-modal-overlay" id="statDesaModal">
        <div class="stat-modal-box">
            <button type="button" class="stat-modal-close" onclick="window.closeDesaModal()" aria-label="Tutup">
                <i class="ph ph-x"></i>
            </button>
            <div class="stat-modal-header">
                <div class="stat-modal-icon" id="modalDesaIcon">
                    <i class="ph ph-buildings"></i>
                </div>
                <div>
                    <h3 class="stat-modal-title" id="modalDesaTitle">Desa Cikampek Kota</h3>
                    <span class="stat-modal-badge" id="modalDesaBadge">Pusat Niaga & Stasiun • #01</span>
                </div>
            </div>

            <div class="stat-modal-grid">
                <div class="stat-modal-fact">
                    <div class="stat-modal-fact-label">Luas Wilayah</div>
                    <div class="stat-modal-fact-value" id="modalDesaLuas">2,15 km²</div>
                </div>
                <div class="stat-modal-fact">
                    <div class="stat-modal-fact-label">Kode Pos</div>
                    <div class="stat-modal-fact-value" id="modalDesaKodePos">41373</div>
                </div>
                <div class="stat-modal-fact">
                    <div class="stat-modal-fact-label">Status Wilayah</div>
                    <div class="stat-modal-fact-value" id="modalDesaStatus">Desa Mandiri</div>
                </div>
                <div class="stat-modal-fact">
                    <div class="stat-modal-fact-label">Kecamatan</div>
                    <div class="stat-modal-fact-value">Cikampek (Karawang)</div>
                </div>
            </div>

            <div class="stat-modal-section-title">Profil & Gambaran Wilayah</div>
            <p class="stat-modal-desc" id="modalDesaDesc"></p>

            <div class="stat-modal-section-title">Potensi Utama Wilayah</div>
            <p class="stat-modal-desc" id="modalDesaPotensi"></p>

            <div class="stat-modal-section-title">Fasilitas & Layanan Unggulan</div>
            <div class="stat-modal-facilities" id="modalDesaFacilities"></div>

            <div class="stat-modal-actions">
                <a href="#" id="modalDesaMapsBtn" target="_blank" class="btn-modal-action btn-modal-maps">
                    <i class="ph ph-navigation-arrow"></i> Petunjuk Arah (Google Maps)
                </a>
                <button type="button" id="modalDesaCopyBtn" class="btn-modal-action btn-modal-copy">
                    <i class="ph ph-copy"></i> Salin Data Desa
                </button>
            </div>
        </div>
    </div>

    <!-- ===== LAYANAN SECTION (Neo-Brutalism Cards) ===== -->
    <section class="section" id="layanan">
        <div class="section-header reveal">
            <div class="section-label">
                <i class="ph ph-star-four"></i> Layanan Kami
            </div>
            <h2 class="section-title">Layanan Publik Digital</h2>
            <p class="section-desc">
                Tiga layanan utama yang tersedia secara online untuk memudahkan masyarakat Kecamatan Cikampek
                dalam mengurus kebutuhan administrasi.
            </p>
        </div>

        <div class="services-grid">
            <div class="service-card reveal reveal-delay-1">
                <div class="service-icon">
                    <i class="ph ph-building"></i>
                </div>
                <div class="service-title">Peminjaman Aula</div>
                <div class="service-desc">
                    Ajukan peminjaman aula kecamatan untuk acara dan kegiatan masyarakat secara mudah
                    melalui sistem digital kami.
                </div>
                <div class="service-arrow">
                    <i class="ph ph-arrow-right"></i>
                </div>
            </div>

            <div class="service-card reveal reveal-delay-2">
                <div class="service-icon">
                    <i class="ph ph-package"></i>
                </div>
                <div class="service-title">Peminjaman Inventaris</div>
                <div class="service-desc">
                    Pinjam barang inventaris kecamatan seperti meja, kursi, tenda, dan peralatan lainnya
                    untuk kegiatan masyarakat.
                </div>
                <div class="service-arrow">
                    <i class="ph ph-arrow-right"></i>
                </div>
            </div>

            <div class="service-card reveal reveal-delay-3">
                <div class="service-icon">
                    <i class="ph ph-certificate"></i>
                </div>
                <div class="service-title">Pengajuan Sertifikat</div>
                <div class="service-desc">
                    Ajukan permohonan sertifikat dan piagam penghargaan secara online, 
                    proses cepat dan transparan.
                </div>
                <div class="service-arrow">
                    <i class="ph ph-arrow-right"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== TENTANG KAMI SECTION (Glassmorphism) ===== -->
    <section class="section" id="tentang">
        <div class="section-header reveal">
            <div class="section-label">
                <i class="ph ph-info"></i> Tentang Kami
            </div>
            <h2 class="section-title">Kecamatan Cikampek</h2>
            <p class="section-desc">
                Menghadirkan pelayanan publik yang modern, cepat, dan transparan untuk masyarakat Cikampek.
            </p>
        </div>

        <div class="about-grid">
            <div class="about-image-wrapper reveal">
                <img src="{{ asset('images/gambar-kecamatan.png') }}" alt="Kantor Kecamatan Cikampek" class="about-image"
                     onerror="this.style.background='linear-gradient(135deg, #1e40af, #2563eb)'; this.style.minHeight='300px'">
                <div class="about-image-overlay"></div>
                <div class="about-stats-floating">
                    <div class="about-stat-chip">
                        <div class="about-stat-chip-number">12</div>
                        <div class="about-stat-chip-label">Kelurahan</div>
                    </div>
                    <div class="about-stat-chip">
                        <div class="about-stat-chip-number">3</div>
                        <div class="about-stat-chip-label">Layanan</div>
                    </div>
                    <div class="about-stat-chip">
                        <div class="about-stat-chip-number">24/7</div>
                        <div class="about-stat-chip-label">Online</div>
                    </div>
                </div>
            </div>

            <div class="about-content">
                <div class="about-feature reveal reveal-delay-1">
                    <div class="about-feature-icon">
                        <i class="ph ph-lightning"></i>
                    </div>
                    <div>
                        <div class="about-feature-title">Proses Cepat & Efisien</div>
                        <div class="about-feature-desc">
                            Pengajuan layanan diproses secara digital sehingga lebih cepat dan mengurangi birokrasi.
                        </div>
                    </div>
                </div>

                <div class="about-feature reveal reveal-delay-2">
                    <div class="about-feature-icon">
                        <i class="ph ph-lock-simple"></i>
                    </div>
                    <div>
                        <div class="about-feature-title">Aman & Terpercaya</div>
                        <div class="about-feature-desc">
                            Data masyarakat terlindungi dengan sistem keamanan modern dan terverifikasi.
                        </div>
                    </div>
                </div>

                <div class="about-feature reveal reveal-delay-3">
                    <div class="about-feature-icon">
                        <i class="ph ph-eye"></i>
                    </div>
                    <div>
                        <div class="about-feature-title">Transparan & Akuntabel</div>
                        <div class="about-feature-desc">
                            Pantau status pengajuan secara real-time dari awal hingga selesai.
                        </div>
                    </div>
                </div>

                <div class="about-feature reveal reveal-delay-4">
                    <div class="about-feature-icon">
                        <i class="ph ph-devices"></i>
                    </div>
                    <div>
                        <div class="about-feature-title">Akses Multi-Perangkat</div>
                        <div class="about-feature-desc">
                            Gunakan dari komputer, tablet, atau smartphone — responsif dan nyaman.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER (Minimalist) ===== -->
    <footer class="footer">
        <div class="footer-content">
            <div>
                <div class="footer-brand-title">Kecamatan Cikampek</div>
                <p class="footer-brand-desc">
                    Portal Layanan Publik Digital Kecamatan Cikampek, Kabupaten Karawang, Provinsi Jawa Barat.
                    Melayani masyarakat dengan hati, transparan, dan profesional.
                </p>
            </div>

            <div>
                <div class="footer-heading">Layanan</div>
                <ul class="footer-links">
                    <li><a href="#layanan"><i class="ph ph-caret-right"></i> Peminjaman Aula</a></li>
                    <li><a href="#layanan"><i class="ph ph-caret-right"></i> Peminjaman Inventaris</a></li>
                    <li><a href="#layanan"><i class="ph ph-caret-right"></i> Pengajuan Sertifikat</a></li>
                </ul>
            </div>

            <div>
                <div class="footer-heading">Navigasi</div>
                <ul class="footer-links">
                    <li><a href="#beranda"><i class="ph ph-caret-right"></i> Beranda</a></li>
                    <li><a href="#statistik"><i class="ph ph-caret-right"></i> Statistik Wilayah</a></li>
                    <li><a href="#tentang"><i class="ph ph-caret-right"></i> Tentang Kami</a></li>
                    <li><a href="{{ route('login') }}"><i class="ph ph-caret-right"></i> Masuk Akun</a></li>
                    <li><a href="{{ route('panduan') }}"><i class="ph ph-caret-right"></i> Buku Panduan</a></li>
                </ul>
            </div>

            <div>
                <div class="footer-heading">Kontak</div>
                <ul class="footer-links">
                    <li><a href="#"><i class="ph ph-map-pin"></i> Jl. Ahmad Yani, Cikampek</a></li>
                    <li><a href="#"><i class="ph ph-phone"></i> (0264) 301234</a></li>
                    <li><a href="#"><i class="ph ph-envelope-simple"></i> kec.cikampek@karawangkab.go.id</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-copyright">
                © {{ date('Y') }} Kecamatan Cikampek — Kabupaten Karawang. Hak Cipta Dilindungi.
            </div>
            <div class="footer-socials">
                <a href="#" class="footer-social-btn" aria-label="Instagram">
                    <i class="ph ph-instagram-logo"></i>
                </a>
                <a href="#" class="footer-social-btn" aria-label="Facebook">
                    <i class="ph ph-facebook-logo"></i>
                </a>
                <a href="#" class="footer-social-btn" aria-label="YouTube">
                    <i class="ph ph-youtube-logo"></i>
                </a>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button class="scroll-top-btn" aria-label="Scroll to Top">
        <i class="ph ph-caret-up"></i>
    </button>

    <!-- Scripts -->
    <script src="{{ asset('js/beranda.js') }}"></script>
    <script src="{{ asset('js/statistik.js') }}"></script>
</body>
</html>
