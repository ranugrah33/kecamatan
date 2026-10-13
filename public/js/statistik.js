/**
 * STATISTIK WILAYAH & 10 DESA CIKAMPEK - DIGITAL SYSTEM
 * Kecamatan Cikampek, Kabupaten Karawang
 * 
 * Features:
 * 1. Web Audio Synthesizer (Cyber SFX on demand)
 * 2. Eased Digital Counter Animation with Indonesian Locale
 * 3. Real-Time WIB Office Status & Digital Clock
 * 4. 3D Card Hover Perspective & Holographic Spotlight
 * 5. Interactive 10 Desa Filtering & Live Search
 * 6. Digital Dossier Modal with Clipboard & Google Maps Integration
 */

(function () {
    'use strict';

    // =========================================================================
    // 1. DATA 10 DESA KECAMATAN CIKAMPEK
    // =========================================================================
    const DATA_DESA_CIKAMPEK = [
        {
            id: '01',
            nama: 'Cikampek Kota',
            kategori: 'pusat',
            kategoriLabel: 'Pusat Niaga & Stasiun',
            icon: 'ph-buildings',
            luas: '2,15 km²',
            kodePos: '41373',
            status: 'Desa Mandiri',
            ringkasan: 'Jantung perniagaan timur Karawang dengan simpul utama Stasiun Besar Cikampek, pasar induk regional, dan pusat perbankan.',
            profil: 'Desa Cikampek Kota merupakan episentrum pertumbuhan ekonomi kecamatan. Menjadi simpul pertemuan jalur rel kereta api utama pulau Jawa dan kawasan perdagangan grosir pasar tradisional modern.',
            potensi: 'Pusat Perdagangan & Jasa, Kawasan Transit Kereta Api Cepat/Antarkota, Perhotelan & Restoran, Sentra Bisnis Ekspedisi.',
            fasilitas: ['Stasiun Besar Cikampek', 'Pasar Induk Cikampek', 'Sentra Perbankan & ATM', 'Pusat Kuliner 24 Jam', 'Masjid Jami Cikampek Kota', 'Pangkalan Ojek & Angkutan'],
            mapsQuery: 'Kantor+Desa+Cikampek+Kota+Karawang'
        },
        {
            id: '02',
            nama: 'Dawuan Tengah',
            kategori: 'pusat',
            kategoriLabel: 'Pusat Pemerintahan',
            icon: 'ph-bank',
            luas: '3,42 km²',
            kodePos: '41373',
            status: 'Desa Mandiri',
            ringkasan: 'Pusat pelayanan birokrasi & administrasi terpadu tempat berdirinya Kantor Kecamatan Cikampek, Polsek, dan Puskesmas DTP.',
            profil: 'Dawuan Tengah merupakan ibukota administratif Kecamatan Cikampek. Seluruh kantor koordinasi pelayanan umum, keamanan, dan kesehatan primer berada di poros wilayah ini.',
            potensi: 'Pusat Layanan Administrasi Publik, Koridor Bisnis Ahmad Yani, Sarana Kesehatan Rujukan, Kompleks Institusi Pemerintahan.',
            fasilitas: ['Kantor Camat Cikampek', 'Puskesmas DTP Cikampek', 'Polsek Cikampek', 'Koramil Cikampek', 'Gedung Serbaguna Kecamatan', 'Sentra Layanan Kependudukan'],
            mapsQuery: 'Kantor+Kecamatan+Cikampek+Dawuan+Tengah'
        },
        {
            id: '03',
            nama: 'Cikampek Barat',
            kategori: 'pusat',
            kategoriLabel: 'Niaga & Kuliner Pantura',
            icon: 'ph-storefront',
            luas: '3,18 km²',
            kodePos: '41373',
            status: 'Desa Mandiri',
            ringkasan: 'Persimpangan vital jalur utama Pantura dan akses Purwakarta/Bandung, sentra niaga ritel, dan kuliner khas Cikampek.',
            profil: 'Wilayah dengan aksesibilitas tinggi yang menghubungkan Karawang dengan jalur arteri antar-provinsi. Menjadi pusat UMKM makanan khas, bengkel otomotif, dan logistik jalur darat.',
            potensi: 'Sentra Kuliner Khas Cikampek, Industri Jasa Otomotif, Pergudangan Skala Menengah, Ritel Komersial.',
            fasilitas: ['Simpang Jomin Arteri', 'Sentra Kuliner Sate Maranggi & Soto', 'Klinik Pratama', 'Sekolah Dasar & Madrasah', 'Sentra UMKM Lokal'],
            mapsQuery: 'Kantor+Desa+Cikampek+Barat+Karawang'
        },
        {
            id: '04',
            nama: 'Cikampek Timur',
            kategori: 'pusat',
            kategoriLabel: 'Permukiman & UMKM',
            icon: 'ph-users-three',
            luas: '4,20 km²',
            kodePos: '41373',
            status: 'Desa Maju',
            ringkasan: 'Kawasan permukiman padat produktif dengan komunitas masyarakat aktif, sentra wirausaha rumahan, dan kerajinan.',
            profil: 'Desa Cikampek Timur memiliki densitas penduduk yang harmonis dengan berbagai unit usaha mikro masyarakat dan pusat pengrajin industri kreatif rumahan.',
            potensi: 'Industri Kreatif Rumahan, Budidaya Ikan Tawar, Jasa Boga & Katering, Koperasi Warga Produktif.',
            fasilitas: ['Kantor Desa Cikampek Timur', 'Poskesdes', 'Balai Warga', 'Sarana Olahraga Masyarakat', 'Masjid Jamie & Majelis'],
            mapsQuery: 'Kantor+Desa+Cikampek+Timur+Karawang'
        },
        {
            id: '05',
            nama: 'Cikampek Selatan',
            kategori: 'pariwisata',
            kategoriLabel: 'Agrikultur & Komunitas',
            icon: 'ph-plant',
            luas: '5,10 km²',
            kodePos: '41373',
            status: 'Desa Maju',
            ringkasan: 'Wilayah perpaduan permukiman hijau asri dan kawasan agrikultur modern sebagai lumbung ketahanan pangan lokal.',
            profil: 'Cikampek Selatan memiliki hamparan lahan pertanian subur yang didukung irigasi teknis serta pengembangan perumahan modern berkonsep ramah lingkungan.',
            potensi: 'Pertanian Padi Unggul, Agrowisata Edukasi Tani, Pengembangan Perumahan Asri, Peternakan Terpadu.',
            fasilitas: ['Kelompok Tani Berdaya', 'Saluran Irigasi Teknis', 'Balai Pertemuan Tani', 'Pustu Cikampek Selatan', 'SDN Cikampek Selatan'],
            mapsQuery: 'Kantor+Desa+Cikampek+Selatan+Karawang'
        },
        {
            id: '06',
            nama: 'Cikampek Pusaka',
            kategori: 'pariwisata',
            kategoriLabel: 'Budaya & Kearifan Lokal',
            icon: 'ph-sparkle',
            luas: '4,85 km²',
            kodePos: '41373',
            status: 'Desa Berkembang',
            ringkasan: 'Kawasan pemekaran sarat sejarah lokal dengan tradisi gotong royong kental, sanggar seni budaya, dan agrowisata.',
            profil: 'Desa Cikampek Pusaka memegang teguh warisan tradisi leluhur Sunda Karawang dengan lanskap perbukitan landai yang asri dan udara sejuk.',
            potensi: 'Seni Pertunjukan Tradisional, Wisata Budaya Lokal, Kerajinan Tangan Bambu, Ketahanan Pangan Nabati.',
            fasilitas: ['Sanggar Seni Budaya', 'Kantor Desa Cikampek Pusaka', 'Area Hijau Terbuka', 'Pusat Kegiatan Belajar Warga', 'Posyandu Mandiri'],
            mapsQuery: 'Kantor+Desa+Cikampek+Pusaka+Karawang'
        },
        {
            id: '07',
            nama: 'Dawuan Barat',
            kategori: 'industri',
            kategoriLabel: 'Niaga & Pergudangan Tol',
            icon: 'ph-warehouse',
            luas: '5,30 km²',
            kodePos: '41373',
            status: 'Desa Mandiri',
            ringkasan: 'Koridor strategis penghubung akses jalan tol dengan fasilitas pergudangan logistik modern dan sentra industri penunjang.',
            profil: 'Dawuan Barat merupakan pilar pendukung arus pergerakan logistik barang industri berkat kedekatannya dengan akses gerbang jalan tol Jakarta-Cikampek.',
            potensi: 'Pergudangan & Depo Kontainer, Sentra Logistik Nasional, Industri Pengemasan, Workshop Otomotif Berat.',
            fasilitas: ['Akses Dekat Gerbang Tol', 'Kawasan Pergudangan Terpadu', 'SPBU & Rest Area Arteri', 'Pusat Pelatihan Kerja', 'Klinik 24 Jam'],
            mapsQuery: 'Kantor+Desa+Dawuan+Barat+Karawang'
        },
        {
            id: '08',
            nama: 'Dawuan Timur',
            kategori: 'industri',
            kategoriLabel: 'Sentra Pendidikan',
            icon: 'ph-graduation-cap',
            luas: '4,65 km²',
            kodePos: '41373',
            status: 'Desa Maju',
            ringkasan: 'Pusat institusi edukasi sekolah menengah & kejuruan terkemuka serta pemukiman dinamis ramah keluarga.',
            profil: 'Dawuan Timur dikenal sebagai pusat pembinaan generasi muda melalui konsentrasi lembaga pendidikan kejuruan yang menyuplai tenaga kerja siap pakai untuk kawasan industri.',
            potensi: 'Pusat Lembaga Pendidikan SMK/SMA, Hunian Kos & Asrama Siswa, Penyediaan Tenaga Kerja Terampil, Wirausaha Digital.',
            fasilitas: ['Kompleks SMK Negeri & Swasta', 'Pondok Pesantren Modern', 'Lapangan Sepak Bola Dawuan', 'Perpustakaan Desa', 'Pusat Kursus Komputer'],
            mapsQuery: 'Kantor+Desa+Dawuan+Timur+Karawang'
        },
        {
            id: '09',
            nama: 'Kalihurip',
            kategori: 'industri',
            kategoriLabel: 'Kawasan Industri Strategis',
            icon: 'ph-factory',
            luas: '7,25 km²',
            kodePos: '41373',
            status: 'Desa Mandiri',
            ringkasan: 'Kawasan industri bertaraf internasional penggerak manufaktur nasional (Kawasan Industri Pupuk Kujang & BIC).',
            profil: 'Desa Kalihurip merupakan kawasan industri terpadu paling bergengsi di Cikampek dengan keberadaan pabrik pupuk nasional PT Pupuk Kujang dan berbagai industri multinasional.',
            potensi: 'Industri Kimia & Pupuk Nasional, Manufaktur Otomotif & Mesin, Lapangan Kerja Ribuan Karyawan, Perhotelan Bisnis.',
            fasilitas: ['Kawasan Industri Pupuk Kujang', 'Akses Tol Kalihurip Utama', 'Rumah Sakit Pupuk Kujang', 'Helipad & Fasilitas VIP', 'Pusat Keamanan Obvitnas'],
            mapsQuery: 'Kantor+Desa+Kalihurip+Cikampek+Karawang'
        },
        {
            id: '10',
            nama: 'Kamojing',
            kategori: 'pariwisata',
            kategoriLabel: 'Wisata Alam Situ Kamojing',
            icon: 'ph-drop',
            luas: '7,50 km²',
            kodePos: '41373',
            status: 'Desa Maju',
            ringkasan: 'Destinasi wisata danau buatan bersejarah Situ Kamojing (sejak 1912), konservasi resapan air, dan kuliner apung.',
            profil: 'Desa Kamojing memiliki keindahan eksotis danau Situ Kamojing yang dibangun pada masa Hindia Belanda. Merupakan paru-paru hijau Cikampek sekaligus destinasi wisata keluarga favorit.',
            potensi: 'Pariwisata Danau & Sunset Spot, Wisata Kuliner Apung Sunda, Konservasi Air Irigasi, Olahraga Dayung & Memancing.',
            fasilitas: ['Danau Wisata Situ Kamojing', 'Pusat Wisata Kuliner Ikan Bakar', 'Dermaga Perahu Wisata', 'Jalur Lari/Jogging Pinggir Danau', 'Taman Edukasi Air'],
            mapsQuery: 'Situ+Kamojing+Cikampek+Karawang'
        }
    ];

    // =========================================================================
    // 2. WEB AUDIO DIGITAL SYNTHESIZER (Cyber SFX)
    // =========================================================================
    let audioCtx = null;
    let isSoundEnabled = false;

    function initAudio() {
        if (!audioCtx && (window.AudioContext || window.webkitAudioContext)) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            audioCtx = new AudioContextClass();
        }
    }

    function playDigitalBeep(freq = 880, duration = 0.08, type = 'sine') {
        if (!isSoundEnabled || !audioCtx) return;
        try {
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();

            osc.type = type;
            osc.frequency.setValueAtTime(freq, audioCtx.currentTime);

            gain.gain.setValueAtTime(0.04, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duration);

            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.start();
            osc.stop(audioCtx.currentTime + duration);
        } catch (e) {
            // Audio context safely ignored if blocked
        }
    }

    function playCyberWhoosh() {
        if (!isSoundEnabled || !audioCtx) return;
        try {
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(320, audioCtx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(960, audioCtx.currentTime + 0.15);

            gain.gain.setValueAtTime(0.05, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.18);

            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.2);
        } catch (e) {}
    }

    // =========================================================================
    // 3. DIGITAL NUMBER COUNTER WITH SMOOTH EASING
    // =========================================================================
    let statsHasAnimated = false;

    function runStatsCounter() {
        const counters = document.querySelectorAll('.stat-card-number[data-target]');
        const barFills = document.querySelectorAll('.stat-card-bar-fill[data-percent]');

        counters.forEach(counter => {
            const targetVal = parseFloat(counter.getAttribute('data-target'));
            const isDecimal = counter.getAttribute('data-decimal') === 'true';
            const duration = 2200; // ms
            const startTime = performance.now();

            function update(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Ease Out Expo
                const ease = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                const currentVal = targetVal * ease;

                if (isDecimal) {
                    // e.g. 47,60
                    counter.textContent = currentVal.toFixed(2).replace('.', ',');
                } else {
                    // e.g. 112.780
                    counter.textContent = Math.round(currentVal).toLocaleString('id-ID');
                }

                if (progress < 1) {
                    requestAnimationFrame(update);
                } else {
                    if (isDecimal) {
                        counter.textContent = targetVal.toFixed(2).replace('.', ',');
                    } else {
                        counter.textContent = Math.round(targetVal).toLocaleString('id-ID');
                    }
                }
            }

            requestAnimationFrame(update);
        });

        // Animate gold accent progress bars
        barFills.forEach(bar => {
            const pct = bar.getAttribute('data-percent') || '50';
            setTimeout(() => {
                bar.style.width = pct + '%';
            }, 200);
        });
    }

    // =========================================================================
    // 4. REAL-TIME WIB OPERATIONAL HOURS & DIGITAL CLOCK
    // =========================================================================
    function updateLiveStatus() {
        const liveBadge = document.getElementById('statLiveBadge');
        const liveClock = document.getElementById('statDigitalClock');

        // Calculate WIB (UTC + 7)
        const now = new Date();
        const utcMs = now.getTime() + (now.getTimezoneOffset() * 60000);
        const wibTime = new Date(utcMs + (3600000 * 7));

        const hours = wibTime.getHours();
        const minutes = wibTime.getMinutes();
        const seconds = wibTime.getSeconds();
        const dayOfWeek = wibTime.getDay(); // 0 is Sunday, 6 is Saturday

        // Format HH:MM:SS WIB
        const hh = String(hours).padStart(2, '0');
        const mm = String(minutes).padStart(2, '0');
        const ss = String(seconds).padStart(2, '0');

        if (liveClock) {
            liveClock.textContent = `${hh}:${mm}:${ss} WIB`;
        }

        // Operational hours: Senin - Jumat (1-5), 07:45 - 14:45 WIB
        const currentMinutes = (hours * 60) + minutes;
        const openMinutes = (7 * 60) + 45;   // 07:45 = 465 min
        const closeMinutes = (14 * 60) + 45; // 14:45 = 885 min

        const isWeekday = dayOfWeek >= 1 && dayOfWeek <= 5;
        const isOpen = isWeekday && (currentMinutes >= openMinutes && currentMinutes < closeMinutes);

        if (liveBadge) {
            if (isOpen) {
                liveBadge.className = 'stat-live-badge open';
                liveBadge.innerHTML = '<span class="hud-pulse-dot" style="background:#34d399"></span> BUKA (Sedang Melayani)';
            } else {
                liveBadge.className = 'stat-live-badge closed';
                if (!isWeekday) {
                    liveBadge.innerHTML = '<span class="hud-pulse-dot" style="background:#f87171"></span> TUTUP (Libur Akhir Pekan)';
                } else if (currentMinutes < openMinutes) {
                    liveBadge.innerHTML = '<span class="hud-pulse-dot" style="background:#fbbf24"></span> TUTUP (Buka Pukul 07.45 WIB)';
                } else {
                    liveBadge.innerHTML = '<span class="hud-pulse-dot" style="background:#f87171"></span> TUTUP (Buka Besok 07.45 WIB)';
                }
            }
        }
    }

    // =========================================================================
    // 5. 3D CARD INTERACTIVE SPOTLIGHT & PERSPECTIVE
    // =========================================================================
    function initCardSpotlights() {
        const cards = document.querySelectorAll('.stat-card, .desa-card');

        cards.forEach(card => {
            card.addEventListener('mousemove', function (e) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                card.style.setProperty('--mouse-x', `${x}px`);
                card.style.setProperty('--mouse-y', `${y}px`);
            });

            card.addEventListener('mouseenter', function () {
                playDigitalBeep(1200, 0.04, 'triangle');
            });
        });
    }

    // =========================================================================
    // 6. RENDER & FILTER 10 DESA CIKAMPEK
    // =========================================================================
    function renderDesaCards(list) {
        const grid = document.getElementById('statDesaGrid');
        const emptyState = document.getElementById('statDesaEmpty');
        const countBadge = document.getElementById('statDesaCount');

        if (!grid) return;

        if (countBadge) {
            countBadge.textContent = `${list.length} / 10 Desa`;
        }

        if (list.length === 0) {
            grid.innerHTML = '';
            if (emptyState) emptyState.style.display = 'block';
            return;
        }

        if (emptyState) emptyState.style.display = 'none';

        let html = '';
        list.forEach(desa => {
            html += `
                <div class="desa-card" data-desa-id="${desa.id}" onclick="window.openDesaModal('${desa.id}')">
                    <div>
                        <div class="desa-card-top">
                            <span class="desa-number">#${desa.id}</span>
                            <div class="desa-icon-badge">
                                <i class="ph ${desa.icon}"></i>
                            </div>
                        </div>
                        <h4 class="desa-name">${desa.nama}</h4>
                        <span class="desa-tag">${desa.kategoriLabel}</span>
                        <p class="desa-desc">${desa.ringkasan}</p>
                    </div>
                    <div class="desa-meta">
                        <span><i class="ph ph-map-pin"></i> ${desa.luas}</span>
                        <button type="button" class="desa-btn-detail">
                            Detail <i class="ph ph-arrow-right"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        grid.innerHTML = html;
        initCardSpotlights();
    }

    // Filtering logic
    function applyDesaFilter() {
        const activeTab = document.querySelector('.filter-tab.active');
        const searchInput = document.getElementById('desaSearchInput');

        const filterKategori = activeTab ? activeTab.getAttribute('data-filter') : 'all';
        const searchQuery = searchInput ? searchInput.value.trim().toLowerCase() : '';

        let filtered = DATA_DESA_CIKAMPEK;

        if (filterKategori !== 'all') {
            filtered = filtered.filter(item => item.kategori === filterKategori);
        }

        if (searchQuery) {
            filtered = filtered.filter(item => {
                return item.nama.toLowerCase().includes(searchQuery) ||
                       item.kategoriLabel.toLowerCase().includes(searchQuery) ||
                       item.ringkasan.toLowerCase().includes(searchQuery) ||
                       item.potensi.toLowerCase().includes(searchQuery);
            });
        }

        renderDesaCards(filtered);
    }

    // =========================================================================
    // 7. MODAL DIGITAL DOSSIER (DETAIL LENGKAP 10 DESA)
    // =========================================================================
    window.openDesaModal = function (desaId) {
        initAudio();
        playCyberWhoosh();

        const desa = DATA_DESA_CIKAMPEK.find(item => item.id === desaId);
        if (!desa) return;

        const modal = document.getElementById('statDesaModal');
        const iconEl = document.getElementById('modalDesaIcon');
        const titleEl = document.getElementById('modalDesaTitle');
        const badgeEl = document.getElementById('modalDesaBadge');
        const luasEl = document.getElementById('modalDesaLuas');
        const posEl = document.getElementById('modalDesaKodePos');
        const statusEl = document.getElementById('modalDesaStatus');
        const descEl = document.getElementById('modalDesaDesc');
        const potensiEl = document.getElementById('modalDesaPotensi');
        const facList = document.getElementById('modalDesaFacilities');
        const mapsBtn = document.getElementById('modalDesaMapsBtn');
        const copyBtn = document.getElementById('modalDesaCopyBtn');

        if (iconEl) iconEl.innerHTML = `<i class="ph ${desa.icon}"></i>`;
        if (titleEl) titleEl.textContent = `Desa ${desa.nama}`;
        if (badgeEl) badgeEl.textContent = `${desa.kategoriLabel} • #${desa.id}`;
        if (luasEl) luasEl.textContent = desa.luas;
        if (posEl) posEl.textContent = desa.kodePos;
        if (statusEl) statusEl.textContent = desa.status;
        if (descEl) descEl.textContent = desa.profil;
        if (potensiEl) potensiEl.textContent = desa.potensi;

        if (facList) {
            facList.innerHTML = desa.fasilitas.map(f => `
                <span class="facility-chip">
                    <i class="ph ph-check-circle"></i> ${f}
                </span>
            `).join('');
        }

        if (mapsBtn) {
            mapsBtn.href = `https://www.google.com/maps/search/${desa.mapsQuery}`;
            mapsBtn.target = '_blank';
        }

        if (copyBtn) {
            copyBtn.onclick = function () {
                const infoText = `Desa ${desa.nama} - Kec. Cikampek, Kab. Karawang\nKategori: ${desa.kategoriLabel}\nLuas: ${desa.luas}\nKode Pos: ${desa.kodePos}\nPotensi: ${desa.potensi}`;
                navigator.clipboard.writeText(infoText).then(() => {
                    playDigitalBeep(1760, 0.12, 'sine');
                    showToast(`Data Desa ${desa.nama} berhasil disalin!`);
                });
            };
        }

        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeDesaModal = function () {
        playDigitalBeep(440, 0.05, 'sine');
        const modal = document.getElementById('statDesaModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    function showToast(msg) {
        let toast = document.getElementById('statToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'statToast';
            toast.className = 'stat-toast';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="ph ph-check-circle"></i> <span>${msg}</span>`;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2800);
    }

    // =========================================================================
    // 8. DOM READY INITIALIZATION
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function () {
        // Initial Desa Render
        renderDesaCards(DATA_DESA_CIKAMPEK);

        // Filter tab clicks
        const tabs = document.querySelectorAll('.filter-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                initAudio();
                playDigitalBeep(700, 0.06, 'triangle');
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                applyDesaFilter();
            });
        });

        // Search input
        const searchInput = document.getElementById('desaSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                applyDesaFilter();
            });
        }

        // Audio HUD Toggle Button
        const audioBtn = document.getElementById('statAudioToggle');
        if (audioBtn) {
            audioBtn.addEventListener('click', function () {
                initAudio();
                isSoundEnabled = !isSoundEnabled;
                this.innerHTML = isSoundEnabled 
                    ? '<i class="ph ph-speaker-high"></i> Suara Digital: AKTIF' 
                    : '<i class="ph ph-speaker-slash"></i> Suara Digital: NONAKTIF';
                this.style.color = isSoundEnabled ? '#86efac' : '#fca5a5';
                if (isSoundEnabled) {
                    playDigitalBeep(880, 0.1, 'sine');
                    showToast('Efek suara digital diaktifkan!');
                } else {
                    showToast('Efek suara dimatikan.');
                }
            });
        }

        // Replay/Refresh Data Button
        const replayBtn = document.getElementById('statReplayBtn');
        if (replayBtn) {
            replayBtn.addEventListener('click', function () {
                initAudio();
                playCyberWhoosh();
                runStatsCounter();
                showToast('Animasi digital diperbarui!');
            });
        }

        // Scroll to 10 Desa explorer from Card 3
        const exploreDesaBtn = document.getElementById('exploreDesaBtn');
        if (exploreDesaBtn) {
            exploreDesaBtn.addEventListener('click', function (e) {
                e.preventDefault();
                initAudio();
                playDigitalBeep(980, 0.08, 'triangle');
                const target = document.getElementById('desaShowcase');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }

        // Intersection Observer for Statistics Counter
        const statSection = document.getElementById('statistik');
        if (statSection) {
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && !statsHasAnimated) {
                    statsHasAnimated = true;
                    runStatsCounter();
                }
            }, { threshold: 0.15 });

            observer.observe(statSection);
        }

        // Real-time clock tick every second
        updateLiveStatus();
        setInterval(updateLiveStatus, 1000);

        // Close modal on escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                window.closeDesaModal();
            }
        });

        // Close modal on click overlay
        const modalOverlay = document.getElementById('statDesaModal');
        if (modalOverlay) {
            modalOverlay.addEventListener('click', function (e) {
                if (e.target === this) {
                    window.closeDesaModal();
                }
            });
        }
    });

})();
