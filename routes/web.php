<?php

use App\Http\Controllers\CplController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PlController;
use App\Http\Controllers\PemetaanController;
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




// Route untuk menampilkan form tambah kartu nama
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







// Route untuk menampilkan form tambah kartu nama
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

// Routing pemetaan
Route::get('/pemetaan', [PemetaanController::class, 'index'])->name('pemetaan.index');
Route::post('/pemetaan/update', [PemetaanController::class, 'update'])->name('pemetaan.update');