<?php

use App\Http\Controllers\CplController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PlController;
use App\Http\Controllers\PemetaanController;
use App\Http\Controllers\CpmkController;
use App\Http\Controllers\PemetaancpmkplController;
use App\Http\Controllers\PemetaanCPMKCPLMKController;
use App\Http\Controllers\BkController;
use App\Http\Controllers\MkController;
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

Route::middleware('guest')->group(function() {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    // Route::get('/register', [RegisterController::class, 'showRegisterForm']);
    // Route::post('/register', [RegisterController::class, 'register']);
});

// Logout bisa diakses ketika pengguna sudah login
Route::middleware('auth')->post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', [LoginController::class,  'showLoginForm'])->name('login');




// Route untuk menampilkan PL start
Route::get('/PL/index', [PlController::class, 'index'])->name('pl.index')->middleware('auth');

// Route untuk menampilkan form tambah kartu nama
Route::get('/PL/create', [PlController::class, 'create'])->name('pl.create')->middleware('auth');

// Route untuk menyimpan kartu nama yang baru dibuat
Route::post('/PL/index', [PlController::class, 'store'])->name('pl.store')->middleware('auth');

Route::get('/pl/{id}/edit', [PlController::class, 'edit'])->name('pl.edit')->middleware('auth');

// Route untuk mengupdate kartu bisnis
Route::put('/pl/{id}', [PlController::class, 'update'])->name('pl.update')->middleware('auth');

// Route untuk menghapus kartu bisnis
Route::delete('/pl/{id}', [PlController::class, 'destroy'])->name('pl.destroy')->middleware('auth');
// Route untuk menampilkan PL end



// Route untuk menampilkan CPMK start
Route::get('/CPMK/index', [CpmkController::class, 'index'])->name('cpmk.index')->middleware('auth');

// Route untuk menampilkan form tambah kartu nama
Route::get('/CPMK/create', [CpmkController::class, 'create'])->name('cpmk.create')->middleware('auth');

// Route untuk menyimpan kartu nama yang baru dibuat
Route::post('/CPMK/index', [CpmkController::class, 'store'])->name('cpmk.store')->middleware('auth');

Route::get('/cpmk/{id}/edit', [CpmkController::class, 'edit'])->name('cpmk.edit')->middleware('auth');

// Route untuk mengupdate kartu bisnis
Route::put('/cpmk/{id}', [CpmkController::class, 'update'])->name('cpmk.update')->middleware('auth');

// Route untuk menghapus kartu bisnis
Route::delete('/cpmk/{id}', [CpmkController::class, 'destroy'])->name('cpmk.destroy')->middleware('auth');
// Route untuk menampilkan CPMK end



// Route untuk menampilkan CPMK index start
Route::get('/BK/index', [BkController::class, 'index'])->name('bk.index')->middleware('auth');

// Route untuk menampilkan form tambah kartu nama
Route::get('/BK/create', [BkController::class, 'create'])->name('bk.create')->middleware('auth');

// Route untuk menyimpan kartu nama yang baru dibuat
Route::post('/BK/index', [BkController::class, 'store'])->name('bk.store')->middleware('auth');

Route::get('/bk/{id}/edit', [BkController::class, 'edit'])->name('bk.edit')->middleware('auth');

// Route untuk mengupdate kartu bisnis
Route::put('/bk/{id}', [BkController::class, 'update'])->name('bk.update')->middleware('auth');

// Route untuk menghapus kartu bisnis
Route::delete('/bk/{id}', [BkController::class, 'destroy'])->name('bk.destroy')->middleware('auth');
// Route untuk menampilkan CPMK index end


// Route untuk menampilkan CPMK index start
Route::get('/MK/index', [MkController::class, 'index'])->name('mk.index')->middleware('auth');

// Route untuk menampilkan form tambah kartu nama
Route::get('/MK/create', [MkController::class, 'create'])->name('mk.create')->middleware('auth');

// Route untuk menyimpan kartu nama yang baru dibuat
Route::post('/MK/index', [MkController::class, 'store'])->name('mk.store')->middleware('auth');

Route::get('/mk/{id}/edit', [MkController::class, 'edit'])->name('mk.edit')->middleware('auth');

// Route untuk mengupdate kartu bisnis
Route::put('/mk/{id}', [MkController::class, 'update'])->name('mk.update')->middleware('auth');

// Route untuk menghapus kartu bisnis
Route::delete('/mk/{id}', [MkController::class, 'destroy'])->name('mk.destroy')->middleware('auth');
// Route untuk menampilkan CPMK index end






// Route untuk menampilkan CPL index start
Route::get('/CPL/index', [CplController::class, 'index'])->name('cpl.index')->middleware('auth');

// Route untuk menampilkan form tambah kartu nama
Route::get('/CPL/create', [CplController::class, 'create'])->name('cpl.create')->middleware('auth');

// Route untuk menyimpan kartu nama yang baru dibuat
Route::post('/CPL/index', [CplController::class, 'store'])->name('cpl.store')->middleware('auth');

// Route untuk mengedit kartu bisnis
Route::get('/v/member/edit/{id}', [CplController::class, 'edit'])->name('cpl.edit')->middleware('auth');

// Route untuk mengupdate kartu bisnis
Route::put('/v/member/update/{name}', [CplController::class, 'update'])->name('cpl.update')->middleware('auth');

// Route untuk menghapus kartu bisnis
Route::delete('/v/{name}', [CplController::class, 'destroy'])->name('cpl.destroy')->middleware('auth');
// Route untuk menampilkan CPL index end




// Routing pemetaan CPL-PL start
Route::get('/pemetaan_cpl-pl', [PemetaanController::class, 'index'])->name('pemetaan_CPL-PL.index');
Route::post('/pemetaan_cpl-pl/update', [PemetaanController::class, 'update'])->name('pemetaan_CPL-PL.update');
// Routing pemetaan CPL-PL end

// Routing pemetaan CPMK-PL start
Route::get('/pemetaan_cpmkpl', [PemetaancpmkplController::class, 'index'])->name('pemetaan_CPMK-CPL.index');
Route::post('/pemetaan_cpmkpl/update', [PemetaancpmkplController::class, 'update'])->name('pemetaan_CPMK-CPL.update');
// Routing pemetaan CPMK-PL end

// Routing pemetaan CPMK-CPL-MK start
Route::get('/pemetaan_cpmk-cpl-mk', [PemetaanCPMKCPLMKController::class, 'index'])->name('pemetaan_CPMK-CPL-MK.index');
// Route untuk menyimpan pemetaan
Route::post('/pemetaan_cpmk-cpl-mk', [PemetaanCPMKCPLMKController::class, 'store'])->name('pemetaan_CPMK-CPL-MK.store');
// Routing pemetaan CPMK-PL-MK end




// Route pemetaan cpmk-cpl-mk start
Route::get('/pemetaan_cpmk-cpl-mk', [PemetaanCPMKCPLMKController::class, 'index'])->name('pemetaan_CPMK-CPL-MK.index');

// Route untuk menyimpan pemetaan
Route::post('/pemetaan_cpmk-cpl-mk', [PemetaanCPMKCPLMKController::class, 'store'])->name('pemetaan_CPMK-CPL-MK.store');
// Route pemetaan cpmk-cpl-mk end
