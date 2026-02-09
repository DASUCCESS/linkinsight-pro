<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Installer\InstallerController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Web\PageController as PublicPageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AnalyticsController;

use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\LinkedinProfileController;
use App\Http\Controllers\User\AnalyticsController as UserAnalyticsController;
use App\Http\Controllers\Extension\TokenController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

if (! config('installer.installed')) {
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
}

/**
 * Public CMS homepage
 */
Route::get('/', [PublicPageController::class, 'home'])->name('home');

/**
 * Admin area
 */
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
        Route::post('/settings/appearance', [SettingsController::class, 'updateAppearance'])->name('settings.appearance.update');
        Route::post('/settings/seo', [SettingsController::class, 'updateSeo'])->name('settings.seo.update');
        Route::post('/settings/smtp', [SettingsController::class, 'updateSmtp'])->name('settings.smtp.update');
        Route::post('/settings/smtp/test', [SettingsController::class, 'testSmtp'])->name('settings.smtp.test');

        // CMS page manager
        Route::prefix('cms')->name('cms.')->group(function () {
            Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
            Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
            Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');

            Route::get('/pages/{page}/sections', [PageSectionController::class, 'index'])->name('sections.index');
            Route::put('/pages/{page}/sections/{section}', [PageSectionController::class, 'update'])->name('sections.update');
        });

        // User management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');

        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    });

/**
 * Authenticated user dashboard
 */
// Route::get('/dashboard', [UserDashboardController::class, 'index'])
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::get('/extension/api-token', TokenController::class)
    ->middleware(['auth'])
    ->name('extension.api-token');
    
/**
 * Profile routes
 */
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    Route::get('/linkedin/profiles', [LinkedinProfileController::class, 'index'])
        ->name('user.linkedin.profiles.index');

    Route::get('/linkedin/analytics', [UserAnalyticsController::class, 'index'])
        ->name('user.linkedin.analytics.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * Auth scaffolding routes (login, register, etc.)
 */
require __DIR__ . '/auth.php';

/**
 * Catch all CMS pages
 * This must stay at the bottom so it does not override /login, /admin, /dashboard etc.
 */
Route::get('/{slug}', [PublicPageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('page.show');
