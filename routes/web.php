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
use App\Http\Controllers\MasyarakatController;
use App\Http\Controllers\PeminjamanAulaController;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/peminjaman-aula', [AdminPeminjamanAulaController::class, 'index'])->name('admin.peminjaman_aula.index');
    Route::get('/admin/peminjaman-aula/{id}', [AdminPeminjamanAulaController::class, 'show'])->name('admin.peminjaman_aula.show');
    Route::put('/admin/peminjaman-aula/{id}/status', [AdminPeminjamanAulaController::class, 'updateStatus'])->name('admin.peminjaman_aula.updateStatus');
});

Route::middleware(['auth', 'role:masyarakat'])->group(function () {
    Route::get('/masyarakat/dashboard', [MasyarakatController::class, 'dashboard'])->name('masyarakat.dashboard');
    Route::get('/masyarakat/profil', [MasyarakatController::class, 'profil'])->name('masyarakat.profil');
    Route::put('/masyarakat/profil', [MasyarakatController::class, 'updateProfil'])->name('masyarakat.profil.update');
    Route::get('/masyarakat/layanan', [MasyarakatController::class, 'layanan'])->name('masyarakat.layanan');
    Route::get('/masyarakat/riwayat', [MasyarakatController::class, 'riwayat'])->name('masyarakat.riwayat');
    Route::get('/masyarakat/notifikasi', [MasyarakatController::class, 'notifikasi'])->name('masyarakat.notifikasi');
    Route::get('/masyarakat/peminjaman-aula', [PeminjamanAulaController::class, 'index'])->name('masyarakat.peminjaman_aula.index');
    Route::get('/masyarakat/peminjaman-aula/create', [PeminjamanAulaController::class, 'create'])->name('masyarakat.peminjaman_aula.create');
    Route::post('/masyarakat/peminjaman-aula', [PeminjamanAulaController::class, 'store'])->name('masyarakat.peminjaman_aula.store');
    Route::get('/masyarakat/peminjaman-aula/sukses/{id}', [PeminjamanAulaController::class, 'sukses'])->name('masyarakat.peminjaman_aula.sukses');
    Route::get('/masyarakat/peminjaman-aula/{id}', [PeminjamanAulaController::class, 'show'])->name('masyarakat.peminjaman_aula.show');
    Route::put('/masyarakat/peminjaman-aula/{id}/cancel', [PeminjamanAulaController::class, 'cancel'])->name('masyarakat.peminjaman_aula.cancel');
});
