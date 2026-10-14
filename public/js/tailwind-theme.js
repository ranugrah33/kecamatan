/**
 * TEMA GLOBAL: HIJAU ZAMRUD & EMAS (Emerald & Gold)
 * Kecamatan Cikampek, Kabupaten Karawang
 *
 * Harus dimuat SETELAH <script src="https://cdn.tailwindcss.com">.
 * Memetakan ulang palet Tailwind agar seluruh halaman (beranda, auth,
 * dashboard admin & masyarakat) otomatis memakai tema zamrud & emas
 * tanpa perlu mengubah class di setiap view.
 *
 *  - blue   -> emerald (warna utama)
 *  - indigo -> jade/teal (pendamping zamrud)
 *  - purple -> gold (aksen elegan)
 *  - sky    -> gold
 *  - gold   -> palet emas baru (bisa dipakai: bg-gold-500, text-gold-400, dst.)
 *
 * Warna status (green, red, amber, yellow) sengaja tidak diubah.
 */
(function () {
    var emerald = {
        50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7',
        400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857',
        800: '#065f46', 900: '#064e3b', 950: '#022c22'
    };

    var jade = {
        50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 300: '#5eead4',
        400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e',
        800: '#115e59', 900: '#134e4a', 950: '#042f2e'
    };

    var gold = {
        50: '#fdfaef', 100: '#faf1d3', 200: '#f3e0a3', 300: '#eacb6c',
        400: '#e0b84a', 500: '#d4a72c', 600: '#b8891d', 700: '#946a17',
        800: '#785518', 900: '#634618', 950: '#3a2709'
    };

    window.tailwind = window.tailwind || {};
    var existingConfig = window.tailwind.config || {};
    window.tailwind.config = Object.assign({}, existingConfig, {
        theme: Object.assign({}, existingConfig.theme || {}, {
            extend: Object.assign({}, (existingConfig.theme && existingConfig.theme.extend) || {}, {
                colors: {
                    blue: emerald,
                    indigo: jade,
                    purple: gold,
                    sky: gold,
                    gold: gold,
                    emerald: emerald
                }
            })
        })
    });
})();
