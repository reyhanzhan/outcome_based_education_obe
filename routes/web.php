<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PlController;
use App\Http\Controllers\CpmkController;
use App\Http\Controllers\BkController;
use App\Http\Controllers\MkController;
use App\Http\Controllers\CplController;
use App\Http\Controllers\Cpl_PlController;
use App\Http\Controllers\Cpl_MkController;
use App\Http\Controllers\Cpmk_CplController;
use App\Http\Controllers\Cpmk_MkController;
use App\Http\Controllers\Cpl_BKController;
use App\Http\Controllers\Cpmk_Cpl_Mk_Controller;
use App\Http\Controllers\PembobotanCpmkMkController;
use App\Http\Controllers\PemetaancpmkplController;
use App\Http\Controllers\NilaiMahasiswaController;
use App\Http\Controllers\PenilaianCpmkController;
use App\Http\Controllers\VisualisasiCpmkController;
use App\Http\Controllers\PenilaianCplController;
use Illuminate\Support\Facades\Route;

// Rute untuk login (akses tanpa autentikasi)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Logout (akses setelah autentikasi)
Route::middleware('auth')->post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rute utama setelah login (pengalihan berdasarkan role)
Route::get('/', function () {
    $user = auth()->user();
    if ($user && $user->role === 'kps') {
        return redirect()->route('pl.index');
    } elseif ($user && $user->role === 'dosen') {
        return redirect()->route('pembobotan.index');
    }
    return redirect()->route('login'); // Jika role tidak valid, kembali ke login
})->middleware('auth');

// Rute untuk PL (hanya untuk KPS)
Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::get('/PL/index', [PlController::class, 'index'])->name('pl.index');
    Route::get('/PL/create', [PlController::class, 'create'])->name('pl.create');
    Route::post('/PL/index', [PlController::class, 'store'])->name('pl.store');
    Route::get('/pl/{id}/edit', [PlController::class, 'edit'])->name('pl.edit');
    Route::put('/pl/{id}', [PlController::class, 'update'])->name('pl.update');
    Route::delete('/pl/{id}', [PlController::class, 'destroy'])->name('pl.destroy');
});

// Rute untuk CPMK (hanya untuk KPS)
Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::get('/CPMK/index', [CpmkController::class, 'index'])->name('cpmk.index');
    Route::get('/CPMK/create', [CpmkController::class, 'create'])->name('cpmk.create');
    Route::post('/CPMK/index', [CpmkController::class, 'store'])->name('cpmk.store');
    Route::get('/cpmk/{id}/edit', [CpmkController::class, 'edit'])->name('cpmk.edit');
    Route::put('/cpmk/{id}', [CpmkController::class, 'update'])->name('cpmk.update');
    Route::delete('/cpmk/{id}', [CpmkController::class, 'destroy'])->name('cpmk.destroy');
});

// Rute untuk BK (hanya untuk KPS)
Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::get('/BK/index', [BkController::class, 'index'])->name('bk.index');
    Route::get('/BK/create', [BkController::class, 'create'])->name('bk.create');
    Route::post('/BK/index', [BkController::class, 'store'])->name('bk.store');
    Route::get('/bk/{id}/edit', [BkController::class, 'edit'])->name('bk.edit');
    Route::put('/bk/{id}', [BkController::class, 'update'])->name('bk.update');
    Route::delete('/bk/{id}', [BkController::class, 'destroy'])->name('bk.destroy');
});

// Rute untuk MK (hanya untuk KPS)
Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::get('/MK/index', [MkController::class, 'index'])->name('mk.index');
    Route::get('/MK/create', [MkController::class, 'create'])->name('mk.create');
    Route::post('/MK/index', [MkController::class, 'store'])->name('mk.store');
    Route::get('/mk/{id}/edit', [MkController::class, 'edit'])->name('mk.edit');
    Route::put('/mk/{id}', [MkController::class, 'update'])->name('mk.update');
    Route::delete('/mk/{id}', [MkController::class, 'destroy'])->name('mk.destroy');
});

// Rute untuk CPL (hanya untuk KPS)
Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::get('/CPL', [CplController::class, 'index'])->name('cpl.index');
    Route::get('/CPL/index', [CplController::class, 'list'])->name('cpl.list');
    Route::get('/CPL/create', [CplController::class, 'create'])->name('cpl.create');
    Route::post('/CPL/index', [CplController::class, 'store'])->name('cpl.store');
    Route::get('/CPL/{id}/edit', [CplController::class, 'edit'])->name('cpl.edit');
    Route::put('/CPL/{id}', [CplController::class, 'update'])->name('cpl.update');
    Route::delete('/CPL/{id}', [CplController::class, 'destroy'])->name('cpl.destroy');
});

// Rute untuk Pemetaan (hanya untuk KPS)
Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::get('/CPL-PL', [Cpl_PlController::class, 'index'])->name('Cpl_Pl.index');
    Route::post('/CPL-PL/update', [Cpl_PlController::class, 'update'])->name('Cpl_Pl.update');
    Route::get('/CPL-MK', [Cpl_MkController::class, 'index'])->name('Cpl_Mk.index');
    Route::post('/CPL-MK/update', [Cpl_MkController::class, 'update'])->name('Cpl_Mk.update');
    Route::get('/CPMK-CPL', [Cpmk_CplController::class, 'index'])->name('Cpmk_Cpl.index');
    Route::post('/CPMK-CPL/update', [Cpmk_CplController::class, 'update'])->name('Cpmk_Cpl.update');
    Route::get('/CPMK-MK', [Cpmk_MkController::class, 'index'])->name('Cpmk_Mk.index');
    Route::post('/CPMK-MK/update', [Cpmk_MkController::class, 'update'])->name('Cpmk_Mk.update');
    Route::get('/total-bobot', [Cpmk_MkController::class, 'getTotalBobot'])->name('Cpmk_Mk.totalBobot');
    Route::get('/CPL-BK', [Cpl_BKController::class, 'index'])->name('Cpl_Bk.index');
    Route::post('/CPL-BK/update', [Cpl_BKController::class, 'update'])->name('Cpl_Bk.update');
    Route::get('/cpl-cpmk-mk', [Cpmk_Cpl_Mk_Controller::class, 'index'])->name('CplCpmkMk.index');
    Route::get('/pemetaan_cpmkpl', [PemetaancpmkplController::class, 'index'])->name('pemetaan_CPMK-CPL.index');
    Route::post('/pemetaan_cpmkpl/update', [PemetaancpmkplController::class, 'update'])->name('pemetaan_CPMK-CPL.update');
    Route::post('/pemetaan-cpl-cpmk-mk/store', [Cpmk_Cpl_Mk_Controller::class, 'store'])->name('cpmk_cpl_mk.store');
});

// Rute untuk Pembobotan (untuk Dosen&Kps)
Route::middleware(['auth', 'role:dosen|kps'])->group(function () {
    Route::get('/pembobotan', [PembobotanCpmkMkController::class, 'index'])->name('pembobotan.index');
    Route::get('/pembobotan/get-cpmks/{mk_id}', [PembobotanCpmkMkController::class, 'getCpmks'])->name('pembobotan.get-cpmks');
    Route::post('/pembobotan/update', [PembobotanCpmkMkController::class, 'update'])->name('pembobotan.update');
    Route::get('/pembobotan/search-mk', [PembobotanCpmkMkController::class, 'searchMk'])->name('pembobotan.search-mk'); // Rute baru untuk pencarian MK
});

Route::middleware(['auth', 'role:dosen|kps'])->group(function () {
    Route::prefix('nilai')->group(function () {
        Route::get('/nilai/mahasiswa/choose-mata-kuliah', [NilaiMahasiswaController::class, 'chooseMataKuliah'])->name('nilai.mahasiswa.choose_mata_kuliah');
        Route::get('/nilai/mahasiswa/choose', [NilaiMahasiswaController::class, 'chooseMahasiswa'])
            ->name('nilai.mahasiswa.choose_mahasiswa');
        // Halaman input nilai setelah memilih mata kuliah
        Route::get('/mahasiswa/{nim}/mata-kuliah/{kode_mk}', [NilaiMahasiswaController::class, 'index'])
            ->name('nilai.mahasiswa.index');
        // Menyimpan nilai mahasiswa
        Route::post('/mahasiswa/store', [NilaiMahasiswaController::class, 'store'])
            ->name('nilai.mahasiswa.store');
        Route::get('/get-kelas-by-periode', [NilaiMahasiswaController::class, 'getKelasByPeriode'])->name('get.kelas.by.periode');
        // route grafik
        Route::get('/mahasiswa/{nim}/mata-kuliah/{kode_mk}/grafik', [NilaiMahasiswaController::class, 'grafik'])->name('nilai.mahasiswa.grafik');
        
    });
});



// Rute untuk Penilaian CPMK (untuk Dosen&kps)
Route::middleware(['auth', 'role:dosen|kps'])->group(function () {
    Route::get('/penilaian/cpmk/choose_mahasiswa', [PenilaianCpmkController::class, 'chooseMahasiswa'])->name('penilaian.cpmk.choose_mahasiswa');
    Route::get('/penilaian/cpmk/choose_mk/{mahasiswa_id}', [PenilaianCpmkController::class, 'chooseMk'])->name('penilaian.cpmk.choose_mk');
    Route::get('/penilaian/cpmk/{mk_id}', [PenilaianCpmkController::class, 'index'])->name('penilaian.cpmk.index');
    Route::post('/penilaian/cpmk/store', [PenilaianCpmkController::class, 'store'])->name('penilaian.cpmk.store');
    Route::get('/penilaian/cpmk/{mahasiswa_id}/{mk_id}', [PenilaianCpmkController::class, 'index'])->name('penilaian.cpmk.index'); 
    Route::get('nilai/mahasiswa/get-mata-kuliah', [NilaiMahasiswaController::class, 'getMataKuliah'])
        ->name('nilai.mahasiswa.get_mata_kuliah');
    
});

// Rute untuk Visualisasi Grafik Radar (hanya untuk Dosen)
Route::middleware(['auth', 'role:dosen|kps'])->group(function () {
    Route::get('/visualisasi/cpmk/choose_mahasiswa', [VisualisasiCpmkController::class, 'chooseMahasiswa'])->name('visualisasi.cpmk.choose_mahasiswa');
    Route::get('/visualisasi/cpmk/choose_mk/{mahasiswa_id}', [VisualisasiCpmkController::class, 'chooseMk'])->name('visualisasi.cpmk.choose_mk');
    Route::get('/visualisasi/cpmk/radar/{mahasiswa_id}/{mk_id}', [VisualisasiCpmkController::class, 'showRadar'])->name('visualisasi.cpmk.radar');
});

// Rute untuk Penilaian CPL (hanya untuk Dosen)
Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::get('/penilaian/cpl/choose_mahasiswa', [PenilaianCplController::class, 'chooseMahasiswa'])->name('penilaian.cpl.choose_mahasiswa');
    Route::get('/penilaian/cpl/{mahasiswa_id}', [PenilaianCplController::class, 'index'])->name('penilaian.cpl.index');
});