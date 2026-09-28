<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HospitalController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\Hospital\AuthController as HospitalAuthController;
use App\Http\Controllers\Hospital\DashboardController as HospitalDashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Autentikasi Admin (SSO Hasnur Group)
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::get('/sso/redirect', [SsoController::class, 'redirect'])->name('sso.redirect');
Route::post('/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');
// GET dipakai tombol logout agar tidak bergantung CSRF (menghindari 419 palsu).
Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Panel Admin
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Rumah Sakit
    Route::get('/hospitals', [HospitalController::class, 'index'])->name('hospitals.index');
    Route::get('/hospitals/create', [HospitalController::class, 'create'])->name('hospitals.create');
    Route::post('/hospitals', [HospitalController::class, 'store'])->name('hospitals.store');
    Route::get('/hospitals/{hospital}/edit', [HospitalController::class, 'edit'])->name('hospitals.edit');
    Route::put('/hospitals/{hospital}', [HospitalController::class, 'update'])->name('hospitals.update');
    Route::delete('/hospitals/{hospital}', [HospitalController::class, 'destroy'])->name('hospitals.destroy');
    Route::get('/hospitals/{hospital}/pks', [HospitalController::class, 'pks'])->name('hospitals.pks');
    Route::get('/hospitals/{hospital}/pks/download', [HospitalController::class, 'pksDownload'])->name('hospitals.pks.download');
    Route::delete('/hospitals/{hospital}/pks', [HospitalController::class, 'pksDestroy'])->name('hospitals.pks.destroy');

    // Log aktivitas
    Route::get('/logs', [LogController::class, 'verifications'])->name('logs.verifications');
    Route::get('/logs/logins', [LogController::class, 'logins'])->name('logs.logins');
    Route::get('/logs/export', [LogController::class, 'exportVerifications'])->name('logs.export');

    // Kelola admin (Super Admin)
    Route::middleware('superadmin')->group(function () {
        Route::get('/admins', [AdminUserController::class, 'index'])->name('admins.index');
        Route::post('/admins', [AdminUserController::class, 'store'])->name('admins.store');
        Route::put('/admins/{admin}', [AdminUserController::class, 'update'])->name('admins.update');
        Route::delete('/admins/{admin}', [AdminUserController::class, 'destroy'])->name('admins.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Akses Rumah Sakit (link unik per RS: /rs/{slug})
|--------------------------------------------------------------------------
*/
Route::prefix('rs/{hospital}')->name('hospital.')->middleware('hospital.resolve')->group(function () {
    Route::get('/', [HospitalAuthController::class, 'showLogin'])->name('login');
    Route::post('/request-otp', [HospitalAuthController::class, 'requestOtp'])
        ->middleware('throttle:otp-request')->name('otp.request');
    Route::get('/otp', [HospitalAuthController::class, 'showOtp'])->name('otp.show');
    Route::post('/verify-otp', [HospitalAuthController::class, 'verifyOtp'])
        ->middleware('throttle:otp-verify')->name('otp.verify');
    Route::match(['get', 'post'], '/logout', [HospitalAuthController::class, 'logout'])->name('logout');

    Route::middleware('hospital.auth')->group(function () {
        Route::get('/dashboard', [HospitalDashboardController::class, 'index'])->name('dashboard');
        Route::post('/lookup', [HospitalDashboardController::class, 'lookup'])
            ->middleware('throttle:lookup')->name('lookup');
        Route::get('/pks', [HospitalDashboardController::class, 'pks'])->name('pks');
    });
});
