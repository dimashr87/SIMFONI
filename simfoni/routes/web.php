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

Route::get('/', function () {
	return view('welcome');
});


use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ResetPassword;
use App\Http\Controllers\ChangePassword;
use App\Http\Controllers\FenomenaController;
use App\Http\Controllers\SummarizationController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArcController;
use App\Http\Controllers\WebScrappingController;

# Rute untuk Otomatisasi SIMFONI
require __DIR__ . '/otomatisasi.php';

// Rute untuk resource Fenomena
Route::get('/download-template', [FenomenaController::class, 'downloadTemplate'])->name('download.template');
Route::resource('fenomena', FenomenaController::class)->middleware('auth');
Route::get('/get-kabupaten', [FenomenaController::class, 'getKabupaten']);
Route::get('/get-portal', [FenomenaController::class, 'getPortalBerita']);
Route::get('/get-tema/{subjectId}', [FenomenaController::class, 'getThemes']);
Route::get('/summarize', [SummarizationController::class, 'showSummarizationForm'])->name('summarize.index');
Route::post('/summarize', [SummarizationController::class, 'summarize'])->name('summarize.text');
Route::get('/', function () {
	return redirect('/dashboard');
})->middleware('auth');
Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.perform');
Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login.perform');
Route::get('/reset-password', [ResetPassword::class, 'show'])->middleware('guest')->name('reset-password');
Route::post('/reset-password', [ResetPassword::class, 'send'])->middleware('guest')->name('reset.perform');
Route::get('/change-password', [ChangePassword::class, 'show'])->middleware('guest')->name('change-password');
Route::post('/change-password', [ChangePassword::class, 'update'])->middleware('guest')->name('change.perform');
Route::get('/dashboard', [HomeController::class, 'index'])->name('home')->middleware('auth');
Route::get('/change-password', [FenomenaController::class, 'show'])->middleware('guest')->name('change-password');
Route::get('/approval', [FenomenaController::class, 'approval'])->middleware('checkLevel:2')->name('fenomena.approval');
Route::get('/pencarian', [FenomenaController::class, 'pencarian'])->middleware('checkLevel:4')->name('fenomena.pencarian');
Route::post('/fenomena/{id}/{action}', [FenomenaController::class, 'approve'])
	->where('action', 'approve|reject')
	->name('fenomena.approve');
Route::get('/export-fenomena', [FenomenaController::class, 'exportView'])->middleware('auth')->name('fenomena.export');
Route::get('/fenomena/export/download', [FenomenaController::class, 'exportFenomena'])->middleware('auth')->name('export.fenomena');
Route::group(['middleware' => 'auth'], function () {
	Route::resource('webscrapping', WebScrappingController::class)->except(['edit', 'update', 'show']);
	Route::get('webscrapping/download/{id}', [WebScrappingController::class, 'download'])->name('webscrapping.download');
	Route::post('/arc/import', [ArcController::class, 'import'])->name('arc.import');
	Route::get('/arc/template', [ArcController::class, 'downloadTemplate'])->name('arc.template');
	Route::delete('/arc/bulk-delete', [ArcController::class, 'bulkDelete'])->name('arc.bulkDelete');
	Route::resource('arc', ArcController::class)->except(['show']);
	Route::get('/rtl', [PageController::class, 'rtl'])->name('rtl');
	Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');
	Route::get('/kelola', [UserController::class, 'index'])->name('user.index');
	Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
	Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
	Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
	Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');
	Route::get('/profile-static', [PageController::class, 'profile'])->name('profile-static');
	Route::get('/sign-in-static', [PageController::class, 'signin'])->name('sign-in-static');
	Route::get('/sign-up-static', [PageController::class, 'signup'])->name('sign-up-static');
	Route::get('/{page}', [PageController::class, 'index'])->name('page');
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});
Route::get('/summarize-tema/{id}', [SummarizationController::class, 'summarizeTema']);
// Route::get('/summarization', [SummarizationController::class, 'showSummarizationForm'])->name('summarization.form');
Route::post('/import-fenomena', [FenomenaController::class, 'importExcel'])->name('import.fenomena');

Route::get('/sso/login', [LoginController::class, 'showLoginForm'])->name('sso.login'); // Handle SSO login
Route::post('/feedback/store', [FeedbackController::class, 'store'])->name('feedback.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout'); // Handle logout
