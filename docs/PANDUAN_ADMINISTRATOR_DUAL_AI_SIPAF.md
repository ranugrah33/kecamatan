# PANDUAN ADMINISTRATOR DUAL AI PROVIDER SIPAF
## Pengelolaan Google Gemini & Groq pada Chatbot Kecamatan Cikampek

Dokumen ini ditujukan untuk **Administrator Sistem SIPAF**. Panduan ini disusun menggunakan langkah-langkah praktis dan bahasa yang mudah dipahami tanpa perlu menguasai bahasa pemrograman (*coding*).

---

## 1. PENGENALAN SINGKAT SISTEM

Chatbot SIPAF menggunakan dua kecerdasan buatan (AI) sekaligus:
1. **Google Gemini (AI Utama):** Selalu menjadi pilihan pertama untuk menjawab pertanyaan warga masyarakat.
2. **Groq (AI Cadangan Otomatis):** Otomatis bekerja menggantikan Gemini jika kuota Gemini habis (HTTP 429), server Google sedang sibuk, atau koneksi terputus. Begitu Gemini pulih, sistem akan otomatis kembali menggunakan Gemini.

Semua pengaturan API Key dan Model AI dilakukan melalui file konfigurasi `.env`. **Administrator tidak perlu mengubah kode program aplikasi.**

---

## 2. CARA MENDAPATKAN API KEY RESMI

### A. Mendapatkan Google Gemini API Key
1. Buka browser dan kunjungi situs resmi Google AI Studio:  
   👉 **[https://aistudio.google.com/](https://aistudio.google.com/)**
2. Masuk menggunakan akun Google (Gmail) kedinasan atau akun pengelola resmi Kecamatan Cikampek.
3. Di panel sebelah kiri, klik menu **Get API key**.
4. Klik tombol **Create API key**.
5. Pilih proyek Google Cloud yang sesuai (atau pilih *Create API key in new project*).
6. Salin (*copy*) kunci API yang dihasilkan (berawalan huruf `AIzaSy...`).
7. Simpan kunci tersebut di tempat yang aman.

### B. Mendapatkan Groq API Key
1. Buka browser dan kunjungi portal resmi Groq Console:  
   👉 **[https://console.groq.com/](https://console.groq.com/)**
2. Masuk menggunakan email atau akun Google pengelola.
3. Pada menu navigasi sebelah kiri, klik **API Keys**.
4. Klik tombol **Create API Key**.
5. Beri nama kunci, misalnya: `SIPAF-Cikampek-Chatbot`.
6. Klik **Submit**.
7. Salin (*copy*) kunci API yang muncul (berawalan huruf `gsk_...`).  
   *(Catatan: Kunci ini hanya ditampilkan satu kali saat dibuat, pastikan langsung disalin).*

---

## 3. CARA MEMASUKKAN & MENGUBAH PENGATURAN DI FILE `.ENV`

Pengaturan AI tersimpan pada file tersembunyi bernama `.env` di folder utama aplikasi SIPAF (`c:\Users\ASUS\kecamatan\.env`).

### Langkah-langkah Membuka dan Mengedit:
1. Buka folder aplikasi SIPAF melalui teks editor pilihan Anda (misalnya **Notepad**, **VS Code**, atau **Sublime Text**).
2. Buka file `.env`.
3. Gulir ke bawah hingga menemukan bagian konfigurasi AI:

```env
# ========================================================
# GOOGLE GEMINI - PRIMARY AI
# ========================================================
GEMINI_API_KEY=masukkan_api_key_gemini_disini
GEMINI_MODEL=gemini-3.6-flash

# ========================================================
# GROQ - FALLBACK AI (CADANGAN OTOMATIS)
# ========================================================
GROQ_API_KEY=masukkan_api_key_groq_disini
GROQ_MODEL=qwen/qwen3.8-27b

# ========================================================
# AI ROUTER CONFIGURATION
# ========================================================
AI_PRIMARY=gemini
AI_FALLBACK=groq
AI_TIMEOUT=20
AI_MAX_RETRIES=1
```

4. Tempelkan (*paste*) API Key yang telah Anda dapatkan ke variabel `GEMINI_API_KEY` dan `GROQ_API_KEY`.
5. Simpan file (`Ctrl + S`).

---

## 4. CARA MENGGANTI NAMA MODEL AI

Jika pihak Google atau Groq merilis versi model baru yang lebih stabil atau hemat kuota, Anda dapat mengganti nama model langsung di file `.env` tanpa menyentuh kode program.

### Model yang Didukung & Tersedia:
- **Google Gemini:**  
  - `gemini-3.6-flash` (Aktif / Default saat ini)  
  - `gemini-2.5-flash` / `gemini-1.5-flash` (Tergantung ketersediaan akun Google Anda)
- **Groq:**  
  - `qwen/qwen3.8-27b` (Aktif / Default saat ini)  
  - `llama-3.3-70b-versatile`  
  - `llama-3.1-8b-instant`

Cukup ubah nilai `GEMINI_MODEL=` atau `GROQ_MODEL=` pada file `.env` lalu simpan.

---

## 5. LANGKAH WAJIB SETELAH MENGUBAH FILE `.ENV` (CLEAR CONFIG CACHE)

Setiap kali Anda mengubah file `.env`, Laravel mungkin masih menyimpan konfigurasi lama di memori (*cache*). Anda **WAJIB** membersihkan cache konfigurasi agar pengaturan baru terbaca:

1. Buka **Terminal / PowerShell / Command Prompt**.
2. Masuk ke folder proyek SIPAF:
   ```powershell
   cd c:\Users\ASUS\kecamatan
   ```
3. Jalankan perintah berikut:
   ```powershell
   php artisan config:clear
   ```
4. Jika muncul tulisan:
   `Configuration cache cleared successfully.`
   Maka konfigurasi baru Anda sudah aktif 100%!

---

## 6. CARA MEMERIKSA APAKAH CHATBOT BERFUNGSI

1. Buka website SIPAF di browser (misal: `http://localhost:8000` atau domain resmi).
2. Klik tombol ikon robot warna biru di pojok kanan bawah.
3. Ketik pertanyaan uji coba, misalnya:
   - *"Bagaimana cara meminjam aula kecamatan?"*
   - *"Berapa luas wilayah Kecamatan Cikampek?"*
   - *"Apakah bisa buat KTP di kantor kecamatan?"*
4. Pastikan bot menjawab dengan ramah dan memuat informasi resmi Kecamatan Cikampek.

---

## 7. CARA MENGETAHUI PROVIDER YANG DIGUNAKAN (LOGGING)

Untuk mengetahui apakah pertanyaan masyarakat sedang dilayani oleh **Gemini** atau **Groq**, Administrator dapat memeriksa file log Laravel:

- **Lokasi File Log:**  
  `storage/logs/laravel.log`

### Contoh Tampilan Log di File `laravel.log`:

1. **Ketika Gemini Berhasil (Normal):**
   ```text
   [2026-11-06 17:15:20] local.INFO: [AI Chatbot] Provider: Gemini | Status: Success | Latency: 1.25s
   ```
   *Artinya: Permintaan diproses lancar oleh Google Gemini.*

2. **Ketika Gemini Habis Kuota dan Groq Mengambil Alih (Failover):**
   ```text
   [2026-11-06 17:18:10] local.WARNING: [AI Chatbot] Provider: Gemini | Status: Failed | Reason: Gemini Quota/Rate Limit Exceeded (HTTP 429) | Latency: 0.82s | Action: Failover to Groq
   [2026-11-06 17:18:11] local.INFO: [AI Chatbot] Provider: Groq | Status: Success | Reason: Fallback Activated | Latency: 0.95s
   ```
   *Artinya: Gemini terkena batas kuota, dan Groq otomatis mengambil alih secara instan.*

3. **Ketika Gemini Sudah Pulih (Stateless Recovery):**
   ```text
   [2026-11-06 17:25:00] local.INFO: [AI Chatbot] Provider: Gemini | Status: Success | Latency: 1.10s
   ```
   *Artinya: Gemini otomatis kembali menjadi AI utama tanpa perlu intervensi admin.*

---

## 8. CARA MENGATASI GANGGUAN (TROUBLESHOOTING)

### Skenario A: Gemini Sering Gagal / Masuk ke Groq Terus Menerus
- **Penyebab:** Kuota gratis bulanan Gemini habis, atau API key kadaluarsa.
- **Solusi:**
  1. Masuk ke [Google AI Studio](https://aistudio.google.com/).
  2. Buat API Key baru.
  3. Buka file `.env`, perbarui `GEMINI_API_KEY=...` dengan key baru tersebut.
  4. Jalankan `php artisan config:clear`.

### Skenario B: Groq Gagal Saat Gemini Habis Kuota
- **Penyebab:** Kunci Groq salah atau kuota token gratis harian di Groq Console habis.
- **Solusi:**
  1. Masuk ke [Groq Console](https://console.groq.com/).
  2. Periksa menu **Limits** untuk melihat sisa kuota.
  3. Buat API key baru di menu **API Keys**.
  4. Perbarui `GROQ_API_KEY=...` di file `.env` dan jalankan `php artisan config:clear`.

### Skenario C: Kedua Provider Gagal (Chatbot Menampilkan Pesan Gangguan)
- **Tampilan di Pengguna:**  
  *"Maaf, layanan Asisten AI Kecamatan Cikampek sedang mengalami kendala teknis. Silakan coba kembali beberapa saat lagi atau hubungi langsung petugas pelayanan di kantor Kecamatan Cikampek."*
- **Langkah Penanganan Cepat Admin:**
  1. Periksa koneksi internet pada server / komputer tempat SIPAF berjalan.
  2. Buka `storage/logs/laravel.log` untuk melihat pesan error persisnya.
  3. Periksa validitas kedua API Key (Gemini dan Groq).
  4. Jika kedua key bermasalah, buat key baru untuk masing-masing provider dan perbarui file `.env`.
  5. Jalankan `php artisan config:clear`.

---

## 9. PANDUAN PEMELIHARAAN BERKALA (MAINTENANCE)

Untuk menjaga chatbot tetap optimal:

1. **Pemeriksaan Log Mingguan:**
   Buka `storage/logs/laravel.log` seminggu sekali untuk memantau frekuensi failover dari Gemini ke Groq. Jika failover terjadi sangat sering, pertimbangkan memperbarui paket Google Cloud atau memperbanyak rotasi key.
2. **Backup File `.env` Secara Aman:**
   Simpan salinan cadangan file `.env` di media penyimpanan offline yang aman (seperti flashdisk terenkripsi kedinasan).
   > **PERINGATAN:** Jangan pernah mengunggah file `.env` ke Google Drive publik, WhatsApp grup, atau repository GitHub.
3. **Pembaruan Data Knowledge Base Kecamatan:**
   Admin dapat memperbarui informasi resmi (jam pelayanan, syarat aula, dll) melalui menu **Admin > AI Knowledge Base** di dashboard SIPAF tanpa perlu restart server atau mengubah file konfigurasi.

