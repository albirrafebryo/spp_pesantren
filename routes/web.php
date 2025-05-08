<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\SppController;
use App\Http\Controllers\PembayaranController;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Dashboard (autentikasi dan verifikasi)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rute yang membutuhkan autentikasi
Route::middleware(['auth'])->group(function () {
    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Siswa (cek role di controller)
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
    Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->name('siswa.destroy');

    // Kelas (akses melalui controller)
    Route::resource('kelas', KelasController::class);

    // SPP (akses melalui controller)
    Route::resource('spp', SppController::class);

    // Pembayaran (akses melalui controller)
    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::get('pembayaran/{nisn}/{tahunAjaran}/{bulan}', [PembayaranController::class, 'bayar'])->name('pembayaran.form');
    Route::post('pembayaran/{nisn}/{tahunAjaran}/{bulan}/update', [PembayaranController::class, 'updateStatus'])->name('pembayaran.updateStatus');
});

require __DIR__ . '/auth.php';
