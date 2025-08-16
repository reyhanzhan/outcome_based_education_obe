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
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KrsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

// Rute untuk login (akses tanpa autentikasi)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Logout (akses setelah autentikasi)
Route::middleware('auth')->post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('change.password');
    Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change.password.update');
});

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


Route::middleware(['auth'])->group(function () {
    // Rute untuk Pengelolaan Mahasiswa
    Route::middleware(['role:kps'])->group(function () {
        Route::prefix('mahasiswa')->group(function () {
            Route::get('/daftar', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
            Route::get('/tambah', [MahasiswaController::class, 'create'])->name('mahasiswa.create');
            Route::post('/store', [MahasiswaController::class, 'store'])->name('mahasiswa.store');
            Route::get('/edit/{id}', [MahasiswaController::class, 'edit'])->name('mahasiswa.edit');
            Route::put('/update/{id}', [MahasiswaController::class, 'update'])->name('mahasiswa.update');
            Route::delete('/delete/{id}', [MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
            Route::get('/template', [MahasiswaController::class, 'template'])->name('mahasiswa.template');
            Route::post('/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');
        });
    });

    // Rute untuk Pengelolaan Profil Lulusan
    Route::middleware(['role:kps'])->group(function () {
        Route::prefix('pl')->group(function () {
            Route::get('/index', [PlController::class, 'index'])->name('pl.index');
            Route::get('/create', [PlController::class, 'create'])->name('pl.create');
            Route::post('/index', [PlController::class, 'store'])->name('pl.store');
            Route::get('/{id}/edit', [PlController::class, 'edit'])->name('pl.edit');
            Route::put('/{id}', [PlController::class, 'update'])->name('pl.update');
            Route::delete('/{id}', [PlController::class, 'destroy'])->name('pl.destroy');
            Route::post('/import', [PlController::class, 'import'])->name('pl.import');
            Route::get('/template', [PlController::class, 'downloadTemplate'])->name('pl.template');
            Route::post('/pl/bulk-destroy', [PlController::class, 'bulkDestroy'])->name('pl.bulkDestroy');
            Route::post('/pl/delete-all', [PlController::class, 'deleteAll'])->name('pl.deleteAll');
        });
    });
});


// Rute untuk CPMK (hanya untuk KPS)
Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::get('/CPMK/index', [CpmkController::class, 'index'])->name('cpmk.index');
    Route::get('/CPMK/create', [CpmkController::class, 'create'])->name('cpmk.create');
    Route::post('/CPMK/index', [CpmkController::class, 'store'])->name('cpmk.store');
    Route::get('/cpmk/{id}/edit', [CpmkController::class, 'edit'])->name('cpmk.edit');
    Route::put('/cpmk/{id}', [CpmkController::class, 'update'])->name('cpmk.update');
    Route::delete('/cpmk/{id}', [CpmkController::class, 'destroy'])->name('cpmk.destroy');
    Route::get('cpmk/template', [CpmkController::class, 'downloadTemplate'])->name('cpmk.template');
    Route::post('cpmk/import', [CpmkController::class, 'import'])->name('cpmk.import');
});

// Rute untuk BK (hanya untuk KPS)
Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::get('/BK/index', [BkController::class, 'index'])->name('bk.index');
    Route::get('/BK/create', [BkController::class, 'create'])->name('bk.create');
    Route::post('/BK/index', [BkController::class, 'store'])->name('bk.store');
    Route::get('/bk/{id}/edit', [BkController::class, 'edit'])->name('bk.edit');
    Route::put('/bk/{id}', [BkController::class, 'update'])->name('bk.update');
    Route::delete('/bk/{id}', [BkController::class, 'destroy'])->name('bk.destroy');
    Route::get('bk/template', [BkController::class, 'downloadTemplate'])->name('bk.template');
    Route::post('bk/import', [BkController::class, 'import'])->name('bk.import');
});

// Rute untuk MK (hanya untuk KPS)
Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::get('/MK/index', [MkController::class, 'index'])->name('mk.index');
    Route::get('/MK/create', [MkController::class, 'create'])->name('mk.create');
    Route::post('/MK/index', [MkController::class, 'store'])->name('mk.store');
    Route::get('/mk/{id}/edit', [MkController::class, 'edit'])->name('mk.edit');
    Route::put('/mk/{id}', [MkController::class, 'update'])->name('mk.update');
    Route::delete('/mk/{id}', [MkController::class, 'destroy'])->name('mk.destroy');
    Route::get('mk/template', [MkController::class, 'downloadTemplate'])->name('mk.template');
    Route::post('mk/import', [MkController::class, 'import'])->name('mk.import');
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
    Route::get('cpl/template', [CplController::class, 'downloadTemplate'])->name('cpl.template');
    Route::post('cpl/import', [CplController::class, 'import'])->name('cpl.import');
});

// Rute untuk Pemetaan (hanya untuk KPS)
Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::get('/CPL-PL', [Cpl_PlController::class, 'index'])->name('Cpl_Pl.index');
    Route::get('/cpl-pl/template', [Cpl_PlController::class, 'downloadTemplate'])->name('cpl_pl.template');
    Route::post('/cpl-pl/import', [Cpl_PlController::class, 'import'])->name('cpl_pl.import');
    Route::post('/CPL-PL/update', [Cpl_PlController::class, 'update'])->name('Cpl_Pl.update');

    Route::get('/CPL-MK', [Cpl_MkController::class, 'index'])->name('Cpl_Mk.index');
    Route::post('/CPL-MK/update', [Cpl_MkController::class, 'update'])->name('Cpl_Mk.update');
    Route::get('/cpl-mk/template', [Cpl_MkController::class, 'downloadTemplate'])->name('cpl_mk.template');
    Route::post('/cpl-mk/import', [Cpl_MkController::class, 'import'])->name('cpl_mk.import');

    Route::get('/CPMK-CPL', [Cpmk_CplController::class, 'index'])->name('Cpmk_Cpl.index');
    Route::post('/CPMK-CPL/update', [Cpmk_CplController::class, 'update'])->name('Cpmk_Cpl.update');
    Route::get('/CPMK-CPL/template', [Cpmk_CplController::class, 'downloadTemplate'])->name('cpmk_cpl.template');
    Route::post('/CPMK-CPL/import', [Cpmk_CplController::class, 'import'])->name('cpmk_cpl.import');


    Route::get('/CPMK-MK', [Cpmk_MkController::class, 'index'])->name('Cpmk_Mk.index');
    Route::post('/CPMK-MK/update', [Cpmk_MkController::class, 'update'])->name('Cpmk_Mk.update');
    Route::get('/cpmk-mk/template', [Cpmk_MkController::class, 'downloadTemplate'])->name('cpmk_mk.template');
    Route::post('/cpmk-mk/import', [Cpmk_MkController::class, 'import'])->name('cpmk_mk.import');
    Route::get('/total-bobot', [Cpmk_MkController::class, 'getTotalBobot'])->name('Cpmk_Mk.totalBobot');

    Route::get('/CPL-BK', [Cpl_BkController::class, 'index'])->name('Cpl_Bk.index');
    Route::post('/CPL-BK/update', [Cpl_BkController::class, 'update'])->name('cpl_bk.update');
    Route::get('/CPL-BK/template', [Cpl_BkController::class, 'downloadTemplate'])->name('cpl_bk.template');
    Route::post('/CPL-BK/import', [Cpl_BkController::class, 'import'])->name('cpl_bk.import');


    Route::get('/cpl-cpmk-mk', [Cpmk_Cpl_Mk_Controller::class, 'index'])->name('Cpmk_Cpl_Mk.index');
    Route::post('/pemetaan-cpl-cpmk-mk/store', [Cpmk_Cpl_Mk_Controller::class, 'store'])->name('cpmk_cpl_mk.store');
    Route::get('/pemetaan_cpmkpl', [PemetaancpmkplController::class, 'index'])->name('pemetaan_CPMK-CPL.index');
    Route::post('/pemetaan_cpmkpl/update', [PemetaancpmkplController::class, 'update'])->name('pemetaan_CPMK-CPL.update');

    Route::resource('pl', PlController::class);
});

// Rute untuk Pembobotan (untuk Dosen&Kps)
Route::middleware(['auth', 'role:dosen|kps'])->group(function () {
    Route::get('/pembobotan', [PembobotanCpmkMkController::class, 'index'])->name('pembobotan.index');
    Route::get('/pembobotan/get-cpmks/{mk_id}', [PembobotanCpmkMkController::class, 'getCpmks'])->name('pembobotan.get-cpmks');
    Route::post('/pembobotan/update', [PembobotanCpmkMkController::class, 'update'])->name('pembobotan.update');
    Route::get('/pembobotan/search-mk', [PembobotanCpmkMkController::class, 'searchMk'])->name('pembobotan.search-mk'); // Rute baru untuk pencarian MK
    Route::get('/pembobotan/get-jumlah-penilaian/{mk_id}', [PembobotanCpmkMkController::class, 'getJumlahPenilaian'])->name('pembobotan.get-jumlah-penilaian'); // Route baru
    Route::post('/pembobotan/update-session-year', [PembobotanCpmkMkController::class, 'updateSessionYear'])->name('pembobotan.update-session-year');
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
    
    // rute untuk evaluasi cpmk
    Route::post('/penilaian/cpmk/{mahasiswa_id}/{mk_id}/store-evaluation', [PenilaianCpmkController::class, 'storeEvaluation'])->name('penilaian.cpmk.store.evaluation');
    Route::get('/evaluasi/obe/{mahasiswa_id}/{mk_id}/history', [PenilaianCpmkController::class, 'showEvaluationHistory'])->name('evaluasi.obe.history');
    Route::get('/evaluasi/obe/{mahasiswa_id}/{mk_id}/evaluation/{evaluation_id}', [PenilaianCpmkController::class, 'showEvaluationDetail'])->name('evaluasi.obe.detail');
});

// Rute untuk Visualisasi Grafik Radar (hanya untuk Dosen)
Route::middleware(['auth', 'role:dosen|kps'])->group(function () {
    Route::get('/visualisasi/cpmk/choose_mahasiswa', [VisualisasiCpmkController::class, 'chooseMahasiswa'])->name('visualisasi.cpmk.choose_mahasiswa');
    Route::get('/visualisasi/cpmk/choose_mk/{mahasiswa_id}', [VisualisasiCpmkController::class, 'chooseMk'])->name('visualisasi.cpmk.choose_mk');
    Route::get('/visualisasi/cpmk/radar/{mahasiswa_id}/{mk_id}', [VisualisasiCpmkController::class, 'showRadar'])->name('visualisasi.cpmk.radar');
});

Route::middleware(['auth', 'role:dosen|kps'])->group(function () {
    // rute menu penilaian cpl semua periode 
    Route::get('/penilaian/cpl/grafik', [PenilaianCplController::class, 'grafik'])->name('penilaian.cpl.grafik');

    // rute penilaian cpl untuk mahasiswa tertentu
    Route::get('/penilaian/cpl/{mahasiswa_id}', [PenilaianCplController::class, 'index'])->name('penilaian.cpl.index');
    
});


Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::prefix('dosen')->group(function () {
        Route::get('/tambah', [DosenController::class, 'create'])->name('dosen.create');
        Route::post('/store', [DosenController::class, 'store'])->name('dosen.store');
        Route::get('/daftar', [DosenController::class, 'index'])->name('dosen.index');
        Route::get('/edit/{id}', [DosenController::class, 'edit'])->name('dosen.edit');
        Route::put('/update/{id}', [DosenController::class, 'update'])->name('dosen.update');
        Route::delete('/delete/{id}', [DosenController::class, 'destroy'])->name('dosen.destroy');
        Route::get('/template', [DosenController::class, 'template'])->name('dosen.template');
        Route::post('/import', [DosenController::class, 'import'])->name('dosen.import');
    });
});

Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::prefix('mahasiswa')->group(function () {
        Route::get('/daftar', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
        Route::get('/tambah', [MahasiswaController::class, 'create'])->name('mahasiswa.create');
        Route::post('/store', [MahasiswaController::class, 'store'])->name('mahasiswa.store');
        Route::get('/edit/{id}', [MahasiswaController::class, 'edit'])->name('mahasiswa.edit');
        Route::put('/update/{id}', [MahasiswaController::class, 'update'])->name('mahasiswa.update');
        Route::delete('/delete/{id}', [MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
    });
});


Route::middleware(['auth', 'role:kps'])->group(function () {
    Route::prefix('kurikulum')->group(function () {
        Route::get('/index', [KurikulumController::class, 'index'])->name('kurikulum.index');
        Route::get('/create', [KurikulumController::class, 'create'])->name('kurikulum.create');
        Route::post('/store', [KurikulumController::class, 'store'])->name('kurikulum.store');
        Route::get('/edit/{id}', [KurikulumController::class, 'edit'])->name('kurikulum.edit');
        Route::put('/update/{id}', [KurikulumController::class, 'update'])->name('kurikulum.update');
        Route::delete('/destroy/{id}', [KurikulumController::class, 'destroy'])->name('kurikulum.destroy');
        Route::get('/template', [KurikulumController::class, 'template'])->name('kurikulum.template');
        Route::post('/import', [KurikulumController::class, 'import'])->name('kurikulum.import');
    });

    Route::prefix('kelas')->group(function () {
        Route::get('/index', [KelasController::class, 'index'])->name('kelas.index');
        Route::get('/create', [KelasController::class, 'create'])->name('kelas.create');
        Route::post('/store', [KelasController::class, 'store'])->name('kelas.store');
        Route::get('/edit/{id}', [KelasController::class, 'edit'])->name('kelas.edit');
        Route::put('/update/{id}', [KelasController::class, 'update'])->name('kelas.update');
        Route::delete('/destroy/{id}', [KelasController::class, 'destroy'])->name('kelas.destroy');
        Route::get('/template', [KelasController::class, 'template'])->name('kelas.template');
        Route::post('/import', [KelasController::class, 'import'])->name('kelas.import');
    });

    Route::prefix('krs')->group(function () {
        Route::get('/index', [KrsController::class, 'index'])->name('krs.index');
        Route::get('/create', [KrsController::class, 'create'])->name('krs.create');
        Route::post('/store', [KrsController::class, 'store'])->name('krs.store');
        Route::get('/edit/{id}', [KrsController::class, 'edit'])->name('krs.edit');
        Route::put('/update/{id}', [KrsController::class, 'update'])->name('krs.update');
        Route::delete('/destroy/{id}', [KrsController::class, 'destroy'])->name('krs.destroy');
        Route::get('/template', [KrsController::class, 'template'])->name('krs.template');
        Route::post('/import', [KrsController::class, 'import'])->name('krs.import');
    });
});