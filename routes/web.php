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
use App\Http\Controllers\MasyarakatController;

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

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

Route::middleware(['auth', 'role:masyarakat'])->group(function () {
    Route::get('/masyarakat/dashboard', [MasyarakatController::class, 'dashboard'])->name('masyarakat.dashboard');
    Route::get('/masyarakat/profil', [MasyarakatController::class, 'profil'])->name('masyarakat.profil');
    Route::put('/masyarakat/profil', [MasyarakatController::class, 'updateProfil'])->name('masyarakat.profil.update');
    Route::get('/masyarakat/layanan', [MasyarakatController::class, 'layanan'])->name('masyarakat.layanan');
    Route::get('/masyarakat/riwayat', [MasyarakatController::class, 'riwayat'])->name('masyarakat.riwayat');
    Route::get('/masyarakat/notifikasi', [MasyarakatController::class, 'notifikasi'])->name('masyarakat.notifikasi');
});
