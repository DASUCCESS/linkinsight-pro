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
use App\Http\Controllers\User\LinkedinAudienceController;
use App\Http\Controllers\User\LinkedinConnectionsController;
use App\Http\Controllers\User\LinkedinSyncJobsController;
use App\Http\Controllers\Extension\TokenController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Auth\RegisteredUserController;

// --------------------------------------------------------------
// Installer
// --------------------------------------------------------------
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

// --------------------------------------------------------------
// Public homepage
// --------------------------------------------------------------
Route::get('/', [PublicPageController::class, 'home'])->name('home');

// --------------------------------------------------------------
// Admin AUTH (custom login + admin register)
// --------------------------------------------------------------
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin login page (view only, POST still goes to default `login` route)
    Route::get('/login', function () {
        return view('admin.auth.login');
    })->name('login');

    // Admin register page + handler (only existing admins)
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/register', [RegisteredUserController::class, 'createAdmin'])
            ->name('register');

        Route::post('/register', [RegisteredUserController::class, 'storeAdmin'])
            ->name('register.store');
    });
});

// --------------------------------------------------------------
// Admin area (dashboard, settings, users, etc.)
// --------------------------------------------------------------
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
        Route::post('/settings/auth', [SettingsController::class, 'updateAuth'])->name('settings.auth.update');

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

        Route::get('/themes', [ThemeController::class, 'index'])->name('themes.index');
        Route::post('/themes/{theme}/activate', [ThemeController::class, 'activate'])->name('themes.activate');
        Route::post('/themes/rollback', [ThemeController::class, 'rollback'])->name('themes.rollback');
        Route::post('/themes/upload', [ThemeController::class, 'upload'])->name('themes.upload');
    });

// --------------------------------------------------------------
// User area (authenticated)
// --------------------------------------------------------------
Route::get('/extension/api-token', TokenController::class)
    ->middleware(['auth'])
    ->name('extension.api-token');

Route::middleware('auth')->group(function () {

    // User dashboard
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('user/linkedin')->name('user.linkedin.')->group(function () {

        // Profiles
        Route::get('/profiles', [LinkedinProfileController::class, 'index'])->name('profiles.index');

        // User analytics (profile metrics + posts)
        Route::get('/analytics', [UserAnalyticsController::class, 'index'])->name('analytics.index');

        // Demographics
        Route::get('/audience/demographics', [LinkedinAudienceController::class, 'demographics'])
            ->name('demographics.index');

        // Creator metrics
        Route::get('/audience/creator-metrics', [LinkedinAudienceController::class, 'creatorMetrics'])
            ->name('creator_metrics.index');

        // Connections directory
        Route::get('/connections', [LinkedinConnectionsController::class, 'index'])
            ->name('connections.index');

        // Sync jobs history
        Route::get('/sync-jobs', [LinkedinSyncJobsController::class, 'index'])
            ->name('sync_jobs.index');
    });

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --------------------------------------------------------------
// Auth scaffolding routes (login, register, email verify, etc.)
// --------------------------------------------------------------
require __DIR__ . '/auth.php';

// --------------------------------------------------------------
// Catch all CMS pages
// --------------------------------------------------------------
Route::get('/{slug}', [PublicPageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('page.show');
