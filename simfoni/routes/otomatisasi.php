<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Otomatisasi\OtomatisasiLoginController;
use App\Http\Controllers\Otomatisasi\OtomatisasiController;

/*
|--------------------------------------------------------------------------
| Rute Menu Otomatisasi
|--------------------------------------------------------------------------
*/

Route::prefix('otomatisasi')->name('otomatisasi.')->group(function () {
    // login & logout
    Route::get('/login', [OtomatisasiLoginController::class, 'show'])->name('login');
    Route::post('/login', [OtomatisasiLoginController::class, 'login'])->name('login.perform');
    Route::post('/logout', [OtomatisasiLoginController::class, 'logout'])
        ->middleware('auth')->name('logout');

    // dashboard & ringkasan
    Route::get('/', [OtomatisasiController::class, 'index'])
        ->middleware('auth')->name('dashboard');
    Route::get('/ringkasan', [OtomatisasiController::class, 'ringkasan'])
        ->middleware('auth')->name('ringkasan');

    // pengaturan keyword scraping
    Route::middleware('auth')->prefix('keyword')->name('keyword.')->group(function () {
        Route::get('/', [OtomatisasiController::class, 'daftarKeyword'])->name('index');
        Route::post('/', [OtomatisasiController::class, 'tambahKeyword'])->name('store');
        Route::delete('/{keyword}', [OtomatisasiController::class, 'hapusKeyword'])->name('destroy');
    });
});
