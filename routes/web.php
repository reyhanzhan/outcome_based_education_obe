<?php

use App\Http\Controllers\CplController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PlController;
use App\Http\Controllers\PemetaanController;
use App\Http\Controllers\CpmkController;
use App\Http\Controllers\PemetaancpmkplController;
use App\Http\Controllers\PemetaanCPMKCPLMKController;
use App\Http\Controllers\PemetaanCPLMKController;
use App\Http\Controllers\BkController;
use App\Http\Controllers\MkController;
use App\Http\Controllers\Cpl_PlController;
use App\Http\Controllers\Cpl_MkController;
use App\Http\Controllers\Cpl_BkController;
use App\Http\Controllers\Cpmk_CplController;
use App\Http\Controllers\Cpmk_MkController;
use App\Http\Controllers\Cpmk_Cpl_Mk_Controller;
use App\Http\Controllers\PembobotanCpmkMkController;
use App\Http\Controllers\SubCpmkCrudController;
use App\Http\Controllers\NilaiMahasiswaController;
use App\Http\Controllers\PenilaianCpmkController;
use App\Http\Controllers\Metode_penilaianController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Controller;
use App\Models\Pl;
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

// Route::get('/', function () {
//     return view('dashboard');
// });




Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    // Route::get('/register', [RegisterController::class, 'showRegisterForm']);
    // Route::post('/register', [RegisterController::class, 'register']);
});

// Logout bisa diakses ketika pengguna sudah login
Route::middleware('auth')->post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');




// 
// Route untuk menampilkan PL start
Route::get('/PL/index', [PlController::class, 'index'])->name('pl.index')->middleware('auth');
// Route untuk menambahkan Profil lulusan
Route::get('/PL/create', [PlController::class, 'create'])->name('pl.create')->middleware('auth');
// Route untuk menyimpan profil lulusan
Route::post('/PL/index', [PlController::class, 'store'])->name('pl.store')->middleware('auth');
Route::get('/pl/{id}/edit', [PlController::class, 'edit'])->name('pl.edit')->middleware('auth');
// Route untuk mengupdate profil lulusan
Route::put('/pl/{id}', [PlController::class, 'update'])->name('pl.update')->middleware('auth');
// Route untuk menghapus profil lulusan
Route::delete('/pl/{id}', [PlController::class, 'destroy'])->name('pl.destroy')->middleware('auth');
// Route untuk menampilkan PL end
// 


// 
// Route untuk menampilkan CPMK start
Route::get('/CPMK/index', [CpmkController::class, 'index'])->name('cpmk.index')->middleware('auth');
// Route untuk menampilkan form cpmk
Route::get('/CPMK/create', [CpmkController::class, 'create'])->name('cpmk.create')->middleware('auth');
// Route untuk menyimpan cpmk
Route::post('/CPMK/index', [CpmkController::class, 'store'])->name('cpmk.store')->middleware('auth');
Route::get('/cpmk/{id}/edit', [CpmkController::class, 'edit'])->name('cpmk.edit')->middleware('auth');
// Route untuk mengupdate cpmk
Route::put('/cpmk/{id}', [CpmkController::class, 'update'])->name('cpmk.update')->middleware('auth');
// Route untuk menghapus cpmk
Route::delete('/cpmk/{id}', [CpmkController::class, 'destroy'])->name('cpmk.destroy')->middleware('auth');
// Route untuk menampilkan CPMK end
// 


// 
// Route untuk menampilkan BK start
Route::get('/BK/index', [BkController::class, 'index'])->name('bk.index')->middleware('auth');
// Route untuk menampilkan form bk
Route::get('/BK/create', [BkController::class, 'create'])->name('bk.create')->middleware('auth');
// Route untuk menyimpan bk
Route::post('/BK/index', [BkController::class, 'store'])->name('bk.store')->middleware('auth');
Route::get('/bk/{id}/edit', [BkController::class, 'edit'])->name('bk.edit')->middleware('auth');
// Route untuk mengupdate bk
Route::put('/bk/{id}', [BkController::class, 'update'])->name('bk.update')->middleware('auth');
// Route untuk menghapus bk
Route::delete('/bk/{id}', [BkController::class, 'destroy'])->name('bk.destroy')->middleware('auth');
// Route untuk menampilkan BK end
// 



// 
// Route untuk menampilkan MK index start
Route::get('/MK/index', [MkController::class, 'index'])->name('mk.index')->middleware('auth');
Route::get('/MK/create', [MkController::class, 'create'])->name('mk.create')->middleware('auth');
Route::post('/MK/index', [MkController::class, 'store'])->name('mk.store')->middleware('auth');
Route::get('/mk/{id}/edit', [MkController::class, 'edit'])->name('mk.edit')->middleware('auth');
Route::put('/mk/{id}', [MkController::class, 'update'])->name('mk.update')->middleware('auth');
Route::delete('/mk/{id}', [MkController::class, 'destroy'])->name('mk.destroy')->middleware('auth');
// Route untuk menampilkan CPMK index end
// 

// Route untuk CRUD CPL (Manajemen Data)
Route::get('/CPL', [CplController::class, 'index'])->name('cpl.index')->middleware('auth');
Route::get('/CPL/index', [CplController::class, 'list'])->name('cpl.list')->middleware('auth');
Route::get('/CPL/create', [CplController::class, 'create'])->name('cpl.create')->middleware('auth');
Route::post('/CPL/index', [CplController::class, 'store'])->name('cpl.store')->middleware('auth');
Route::get('/CPL/{id}/edit', [CplController::class, 'edit'])->name('cpl.edit')->middleware('auth');
Route::put('/CPL/{id}', [CplController::class, 'update'])->name('cpl.update')->middleware('auth');
Route::delete('/CPL/{id}', [CplController::class, 'destroy'])->name('cpl.destroy')->middleware('auth');






// 
// Routing pemetaan CPL-PL start
Route::get('/CPL-PL', [Cpl_PlController::class, 'index'])->name('Cpl_Pl.index');
Route::post('/CPL-PL/update', [Cpl_PlController::class, 'update'])->name('Cpl_Pl.update');
// Routing pemetaan CPL-PL end
// 


// 
// Routing pemetaan CPL-MK start
Route::get('/CPL-MK', [Cpl_MkController::class, 'index'])->name('Cpl_Mk.index');
Route::post('/CPL-MK/update', [Cpl_MkController::class, 'update'])->name('Cpl_Mk.update');
// Routing pemetaan CPL-MK end
// 

// 
// Routing pemetaan CPL-MK start
Route::get('/CPMK-CPL', [Cpmk_CplController::class, 'index'])->name('Cpmk_Cpl.index');
Route::post('/CPMK-CPL/update', [Cpmk_CplController::class, 'update'])->name('Cpmk_Cpl.update');
// Routing pemetaan CPL-MK end
// 


// 
// Routing pemetaan CPL-MK start
Route::get('/CPMK-MK', [Cpmk_MkController::class, 'index'])->name('Cpmk_Mk.index');
Route::post('/CPMK-MK/update', [Cpmk_MkController::class, 'update'])->name('Cpmk_Mk.update');
Route::get('/total-bobot', [Cpmk_MkController::class, 'getTotalBobot'])->name('Cpmk_Mk.totalBobot');

// Routing pemetaan CPL-MK end
// 


// 
// Routing pemetaan CPL-BK start
Route::get('/CPL-BK', [Cpl_BKController::class, 'index'])->name('Cpl_Bk.index');
Route::post('/CPL-BK/update', [Cpl_BKController::class, 'update'])->name('Cpl_Bk.update');
// Routing pemetaan CPL-BK end
// 

// pemetan cpmk-cpl-mk start
Route::get('/cpl-cpmk-mk', [Cpmk_Cpl_Mk_Controller::class, 'index'])->name('CplCpmkMk.index');
// pemetan cpmk-cpl-mk end

Route::get('/pembobotan', [PembobotanCpmkMkController::class, 'index'])->name('pembobotan.index');
Route::get('/pembobotan/get-cpmks/{mk_id}', [PembobotanCpmkMkController::class, 'getCpmks']);
Route::post('/pembobotan/update', [PembobotanCpmkMkController::class, 'update'])->name('pembobotan.update');
Route::get('/pembobotan/get-cpmks/{mk_id}', [Cpmk_MkController::class, 'getCpmks'])->name('pembobotan.get-cpmks');


// 
// Routing pemetaan CPMK-PL start
Route::get('/pemetaan_cpmkpl', [PemetaancpmkplController::class, 'index'])->name('pemetaan_CPMK-CPL.index');
Route::post('/pemetaan_cpmkpl/update', [PemetaancpmkplController::class, 'update'])->name('pemetaan_CPMK-CPL.update');
Route::post('/pemetaan-cpl-cpmk-mk/store', [Cpmk_Cpl_Mk_Controller::class, 'store'])->name('cpmk_cpl_mk.store');
// Routing pemetaan CPMK-PL end
// 

Route::group(['middleware' => 'auth'], function () {
    Route::get('/nilai/mahasiswa/choose', [NilaiMahasiswaController::class, 'chooseMk'])->name('nilai.mahasiswa.choose');
    Route::get('/nilai/mahasiswa/{mk_id}', [NilaiMahasiswaController::class, 'index'])->name('nilai.mahasiswa.index');
    Route::post('/nilai/mahasiswa/store', [NilaiMahasiswaController::class, 'store'])->name('nilai.mahasiswa.store');
    // Route::get('/penilaian/cpmk/{mk_id}', [PenilaianCpmkController::class, 'index'])->name('penilaian.cpmk.index');


    Route::get('/nilai/mahasiswa/choose', [NilaiMahasiswaController::class, 'chooseMk'])->name('nilai.mahasiswa.choose');
    Route::post('/penilaian/cpmk/store', [PenilaianCpmkController::class, 'store'])->name('penilaian.cpmk.store');
    // Route::get('/penilaian/cpmk/{mk_id}', [PenilaianCpmkController::class, 'index'])->name('penilaian.cpmk.index');

    Route::get('/penilaian/cpmk/choose_mahasiswa', [PenilaianCpmkController::class, 'chooseMahasiswa'])->name('penilaian.cpmk.choose_mahasiswa');
    Route::get('/penilaian/cpmk/choose_mk/{mahasiswa_id}', [PenilaianCpmkController::class, 'chooseMk'])->name('penilaian.cpmk.choose_mk');
    Route::get('/penilaian/cpmk/radar/{mahasiswa_id}/{mk_id}', [PenilaianCpmkController::class, 'showRadar'])->name('penilaian.cpmk.radar');
    Route::get('/penilaian/cpmk/{mk_id}', [PenilaianCpmkController::class, 'index'])->name('penilaian.cpmk.index');


    

    // Route untuk Penilaian CPL
    Route::get('/penilaian/cpl', [CplController::class, 'index'])->name('penilaian.cpl.index')->middleware('auth');
    Route::get('/penilaian/cpl/{mahasiswa_id}', [CplController::class, 'show'])->name('penilaian.cpl.show')->middleware('auth');
});




// Route untuk menampilkan profile pengguna
Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('profile.edit');
Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
Route::delete('/profile/delete-photo', [UserController::class, 'deletePhoto'])->name('profile.delete-photo');
