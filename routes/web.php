<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminPeminjamanAulaController;
use App\Http\Controllers\AdminInventoryController;
use App\Http\Controllers\AdminInventoryBorrowingController;
use App\Http\Controllers\AdminCertificateController;
use App\Http\Controllers\MasyarakatController;
use App\Http\Controllers\PeminjamanAulaController;
use App\Http\Controllers\InventoryBorrowingController;
use App\Http\Controllers\CertificateRequestController;
use App\Http\Controllers\ChatbotController;

Route::get('/', function () {
    return redirect('/login');
});

// Chatbot Public Route
Route::post('/chatbot/chat', [ChatbotController::class, 'chat'])->name('chatbot.chat')->middleware('throttle:15,1');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/lupa-password', [AuthController::class, 'forgotPassword'])->name('password.request');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Peminjaman Aula
    Route::get('/admin/peminjaman-aula', [AdminPeminjamanAulaController::class, 'index'])->name('admin.peminjaman_aula.index');
    Route::get('/admin/peminjaman-aula/{id}', [AdminPeminjamanAulaController::class, 'show'])->name('admin.peminjaman_aula.show');
    Route::put('/admin/peminjaman-aula/{id}/status', [AdminPeminjamanAulaController::class, 'updateStatus'])->name('admin.peminjaman_aula.updateStatus');
    Route::get('/admin/peminjaman-aula/{id}/cetak-surat', [AdminPeminjamanAulaController::class, 'cetakSurat'])->name('admin.peminjaman_aula.cetakSurat');
    Route::post('/admin/peminjaman-aula/{id}/upload-surat', [AdminPeminjamanAulaController::class, 'uploadSuratFinal'])->name('admin.peminjaman_aula.uploadSuratFinal');

    // Kelola Barang Inventaris
    Route::get('/admin/inventaris', [AdminInventoryController::class, 'index'])->name('admin.inventaris.index');
    Route::get('/admin/inventaris/create', [AdminInventoryController::class, 'create'])->name('admin.inventaris.create');
    Route::post('/admin/inventaris', [AdminInventoryController::class, 'store'])->name('admin.inventaris.store');
    Route::get('/admin/inventaris/{id}/edit', [AdminInventoryController::class, 'edit'])->name('admin.inventaris.edit');
    Route::put('/admin/inventaris/{id}', [AdminInventoryController::class, 'update'])->name('admin.inventaris.update');
    Route::put('/admin/inventaris/{id}/toggle', [AdminInventoryController::class, 'toggleActive'])->name('admin.inventaris.toggle');
    Route::delete('/admin/inventaris/{id}', [AdminInventoryController::class, 'destroy'])->name('admin.inventaris.destroy');

    // Pengajuan Peminjaman Inventaris
    Route::get('/admin/peminjaman-inventaris', [AdminInventoryBorrowingController::class, 'index'])->name('admin.peminjaman_inventaris.index');
    Route::get('/admin/peminjaman-inventaris/{id}', [AdminInventoryBorrowingController::class, 'show'])->name('admin.peminjaman_inventaris.show');
    Route::put('/admin/peminjaman-inventaris/{id}/status', [AdminInventoryBorrowingController::class, 'updateStatus'])->name('admin.peminjaman_inventaris.updateStatus');

    // Sertifikat / Piagam
    Route::get('/admin/sertifikat', [AdminCertificateController::class, 'index'])->name('admin.sertifikat.index');
    Route::get('/admin/sertifikat/{id}', [AdminCertificateController::class, 'show'])->name('admin.sertifikat.show');
    Route::put('/admin/sertifikat/{id}/status', [AdminCertificateController::class, 'updateStatus'])->name('admin.sertifikat.updateStatus');
    Route::post('/admin/sertifikat/{id}/upload', [AdminCertificateController::class, 'uploadCertificate'])->name('admin.sertifikat.upload');
    Route::get('/admin/sertifikat/{id}/download-permohonan', [AdminCertificateController::class, 'downloadApplication'])->name('admin.sertifikat.downloadApplication');

    // Knowledge Base AI
    Route::get('/admin/ai-knowledge', [\App\Http\Controllers\AdminAiKnowledgeController::class, 'index'])->name('admin.ai_knowledge.index');
    Route::get('/admin/ai-knowledge/create', [\App\Http\Controllers\AdminAiKnowledgeController::class, 'create'])->name('admin.ai_knowledge.create');
    Route::post('/admin/ai-knowledge', [\App\Http\Controllers\AdminAiKnowledgeController::class, 'store'])->name('admin.ai_knowledge.store');
    Route::get('/admin/ai-knowledge/{id}/edit', [\App\Http\Controllers\AdminAiKnowledgeController::class, 'edit'])->name('admin.ai_knowledge.edit');
    Route::put('/admin/ai-knowledge/{id}', [\App\Http\Controllers\AdminAiKnowledgeController::class, 'update'])->name('admin.ai_knowledge.update');
    Route::put('/admin/ai-knowledge/{id}/toggle', [\App\Http\Controllers\AdminAiKnowledgeController::class, 'toggleActive'])->name('admin.ai_knowledge.toggle');
    Route::delete('/admin/ai-knowledge/{id}', [\App\Http\Controllers\AdminAiKnowledgeController::class, 'destroy'])->name('admin.ai_knowledge.destroy');

    Route::get('/admin/laporan', [AdminController::class, 'laporan'])->name('admin.laporan');
    Route::get('/admin/pengaturan', [AdminController::class, 'pengaturan'])->name('admin.pengaturan');
    Route::put('/admin/pengaturan/profil', [AdminController::class, 'updateProfil'])->name('admin.pengaturan.profil');
    Route::put('/admin/pengaturan/password', [AdminController::class, 'updatePassword'])->name('admin.pengaturan.password');
    Route::put('/admin/pengaturan/aula', [AdminController::class, 'updateAula'])->name('admin.pengaturan.aula');
});

Route::middleware(['auth', 'role:masyarakat'])->group(function () {
    Route::get('/masyarakat/dashboard', [MasyarakatController::class, 'dashboard'])->name('masyarakat.dashboard');
    Route::get('/masyarakat/profil', [MasyarakatController::class, 'profil'])->name('masyarakat.profil');
    Route::put('/masyarakat/profil', [MasyarakatController::class, 'updateProfil'])->name('masyarakat.profil.update');
    Route::get('/masyarakat/layanan', [MasyarakatController::class, 'layanan'])->name('masyarakat.layanan');
    Route::get('/masyarakat/riwayat', [MasyarakatController::class, 'riwayat'])->name('masyarakat.riwayat');
    Route::get('/masyarakat/notifikasi', [MasyarakatController::class, 'notifikasi'])->name('masyarakat.notifikasi');

    // Peminjaman Aula
    Route::get('/masyarakat/peminjaman-aula', [PeminjamanAulaController::class, 'index'])->name('masyarakat.peminjaman_aula.index');
    Route::get('/masyarakat/peminjaman-aula/create', [PeminjamanAulaController::class, 'create'])->name('masyarakat.peminjaman_aula.create');
    Route::post('/masyarakat/peminjaman-aula', [PeminjamanAulaController::class, 'store'])->name('masyarakat.peminjaman_aula.store');
    Route::get('/masyarakat/peminjaman-aula/sukses/{id}', [PeminjamanAulaController::class, 'sukses'])->name('masyarakat.peminjaman_aula.sukses');
    Route::get('/masyarakat/peminjaman-aula/{id}', [PeminjamanAulaController::class, 'show'])->name('masyarakat.peminjaman_aula.show');
    Route::put('/masyarakat/peminjaman-aula/{id}/cancel', [PeminjamanAulaController::class, 'cancel'])->name('masyarakat.peminjaman_aula.cancel');

    // Peminjaman Inventaris
    Route::get('/masyarakat/peminjaman-inventaris', [InventoryBorrowingController::class, 'index'])->name('masyarakat.peminjaman_inventaris.index');
    Route::get('/masyarakat/peminjaman-inventaris/create', [InventoryBorrowingController::class, 'create'])->name('masyarakat.peminjaman_inventaris.create');
    Route::post('/masyarakat/peminjaman-inventaris', [InventoryBorrowingController::class, 'store'])->name('masyarakat.peminjaman_inventaris.store');
    Route::get('/masyarakat/peminjaman-inventaris/sukses/{id}', [InventoryBorrowingController::class, 'sukses'])->name('masyarakat.peminjaman_inventaris.sukses');
    Route::get('/masyarakat/peminjaman-inventaris/{id}', [InventoryBorrowingController::class, 'show'])->name('masyarakat.peminjaman_inventaris.show');

    // Sertifikat / Piagam
    Route::get('/masyarakat/sertifikat', [CertificateRequestController::class, 'index'])->name('masyarakat.sertifikat.index');
    Route::get('/masyarakat/sertifikat/create', [CertificateRequestController::class, 'create'])->name('masyarakat.sertifikat.create');
    Route::post('/masyarakat/sertifikat', [CertificateRequestController::class, 'store'])->name('masyarakat.sertifikat.store');
    Route::get('/masyarakat/sertifikat/{id}', [CertificateRequestController::class, 'show'])->name('masyarakat.sertifikat.show');
    Route::get('/masyarakat/sertifikat/{id}/download', [CertificateRequestController::class, 'download'])->name('masyarakat.sertifikat.download');
});
