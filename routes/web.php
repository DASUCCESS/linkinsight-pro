<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Installer\InstallerController;


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
Route::prefix('installer')
    ->name('installer.')
    ->group(function () {
        Route::get('/', [InstallerController::class, 'requirements'])->name('requirements');

        Route::get('/requirements', [InstallerController::class, 'requirements'])->name('requirements');
        Route::post('/requirements', [InstallerController::class, 'requirementsNext'])->name('requirements.next');

        Route::get('/permissions', [InstallerController::class, 'permissions'])->name('permissions');
        Route::post('/permissions', [InstallerController::class, 'permissionsNext'])->name('permissions.next');

        Route::get('/database', [InstallerController::class, 'database'])->name('database');
        Route::post('/database', [InstallerController::class, 'databaseSave'])->name('database.save');

        Route::get('/admin', [InstallerController::class, 'admin'])->name('admin');
        Route::post('/admin', [InstallerController::class, 'adminSave'])->name('admin.save');

        Route::get('/smtp', [InstallerController::class, 'smtp'])->name('smtp');
        Route::post('/smtp', [InstallerController::class, 'smtpSave'])->name('smtp.save');

        Route::get('/license', [InstallerController::class, 'license'])->name('license');
        Route::post('/license', [InstallerController::class, 'licenseSave'])->name('license.save');

        Route::get('/finish', [InstallerController::class, 'finish'])->name('finish');
    });

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
