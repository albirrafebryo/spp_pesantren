<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SppController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TabunganController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\JenisPembayaranController;
use App\Http\Controllers\PengaturanKelasController;
use App\Http\Controllers\DetailPembayaranController;
use App\Http\Controllers\PembayaranHistoryController;

Route::get('/', function () {
    return redirect()->route('login');
});
// Profile
Route::middleware(['auth', 'role:admin|petugas|wali'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');

    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('pembayaran/checkout', [PembayaranController::class, 'checkout'])->name('pembayaran.checkout');
    // Route::get('/pembayaran/rekap', [PembayaranController::class, 'rekap'])->name('pembayaran.rekap');
    Route::post('/pembayaran/verifikasi-bukti/{bukti_id}', [PembayaranController::class, 'verifikasiBukti'])->name('pembayaran.verifikasi-bukti');
    Route::post('/pembayaran/update-status', [PembayaranController::class, 'updateStatus'])->name('pembayaran.updateStatus');
    Route::post('/pembayaran/store-daftar-ulang', [PembayaranController::class, 'storeDaftarUlang'])->name('pembayaran.storeDaftarUlang');
    Route::post('/pembayaran/setor-tarik-tabungan', [PembayaranController::class, 'setorTarikTabungan'])
        ->name('pembayaran.setorTarikTabungan'); 
});

Route::get('/dashboard', [DashboardController::class, 'dashboard'])
    ->middleware(['auth', 'role:admin|petugas|wali'])
    ->name('dashboard');


Route::group(['middleware' => ['role:admin']], function () {

Route::resource('users', UserController::class);

Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index'])->name('tahun_ajarans.index');
    Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->name('tahun_ajarans.store');
    Route::get('/tahun-ajaran/{id}/edit', [TahunAjaranController::class, 'edit'])->name('tahun_ajarans.edit');
    Route::patch('/tahun-ajaran/{id}', [TahunAjaranController::class, 'update'])->name('tahun_ajarans.update');
    Route::delete('/tahun-ajaran/{id}', [TahunAjaranController::class, 'destroy'])->name('tahun_ajarans.destroy');

    
    Route::resource('kelas', KelasController::class);

    Route::get('/pengaturan_kelas/atur-siswa', [PengaturanKelasController::class, 'aturSiswaForm'])->name('pengaturan_kelas.aturSiswa');
    Route::post('/pengaturan_kelas/atur-siswa', [PengaturanKelasController::class, 'aturSiswaProses'])->name('pengaturan_kelas.aturSiswaProses');
    Route::get('pengaturan_kelas/riwayat/{siswa_id}', [PengaturanKelasController::class, 'riwayat'])->name('pengaturan_kelas.riwayat');
    Route::post('pengaturan_kelas/proses-naik-kelas-massal', [PengaturanKelasController::class, 'prosesNaikKelasMassal'])->name('pengaturan_kelas.prosesNaikKelasMassal');
    Route::resource('pengaturan_kelas', PengaturanKelasController::class);

    Route::resource('jenispembayaran', JenisPembayaranController::class);

    Route::get('/detailpembayaran', [DetailPembayaranController::class, 'index'])->name('detailpembayaran.index');
    Route::resource('detail_pembayaran', DetailPembayaranController::class);

    Route::get('spp', [SppController::class, 'index'])->name('spp.index');
    Route::get('/spp/{id}/daftar-siswa', [SppController::class, 'daftarSiswa'])->name('spp.daftar-siswa');
    Route::get('/api/cari-siswa', [SppController::class, 'cariSiswa']);
    Route::post('/spp/{id}/set-bulan-mulai', [SppController::class, 'setBulanMulai'])->name('spp.setBulanMulai');
});

Route::middleware(['auth', 'role:admin|petugas'])->group(function () {
Route::get('/siswa/search', [SiswaController::class, 'search'])->name('siswa.search');
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::get('/siswa/template-sample', [SiswaController::class, 'downloadTemplate'])->name('siswa.template');
    Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
    Route::post('/siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
    Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->name('siswa.destroy');

    Route::get('/api/livesearch-siswa', [PembayaranController::class, 'livesearchSiswa'])->name('pembayaran.livesearch');
    Route::post('pembayaran/delete-pembayaran', [PembayaranController::class, 'deletePembayaran'])->name('pembayaran.deletePembayaran');
    Route::post('/pembayaran/deletePembayaran', [PembayaranController::class, 'deletePembayaran'])->name('pembayaran.deletePembayaran');

});


Route::group(['middleware' => ['role:petugas']], function () {  


    // Route::get('/siswa/search', [SiswaController::class, 'search'])->name('siswa.search');
    // Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');

    

});

Route::middleware(['auth', 'role:petugas|wali'])->group(function () {
    

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/detail', [LaporanController::class, 'detail'])->name('laporan.detail');
    Route::get('/laporan/perkelas', [LaporanController::class, 'laporanPerKelas'])->name('laporan.perkelas');
    Route::get('/laporan/individu', [LaporanController::class, 'laporanIndividu'])->name('laporan.individu');
    Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
    Route::get('/laporan/rekap', [LaporanController::class, 'rekap'])->name('laporan.rekap');
    Route::get('/laporan/rekap/detail', [LaporanController::class, 'rekapDetail'])->name('laporan.rekap.detail');
    Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
    Route::get('/laporan/api/rekap-siswa', [LaporanController::class, 'apiRekapSiswa'])->name('laporan.api.rekap_siswa');

});

//     Route::middleware(['auth'])->group(function () {


// });


require __DIR__ . '/auth.php';
