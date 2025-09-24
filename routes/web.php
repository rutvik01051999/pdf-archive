<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\PdfArchiveController;
use App\Http\Controllers\ArchiveAuthController;
use App\Http\Controllers\ArchiveAdminController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Custom authentication routes with secure-login
Route::get('secure-login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('secure-login', [App\Http\Controllers\Auth\LoginController::class, 'login'])
    ->middleware(['rotate.session', 'custom.throttle:3,1']);
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout')->middleware('rotate.session');

// Session management routes for admin
Route::middleware(['auth', 'inactive.admin.logout'])->group(function () {
    Route::post('/admin/extend-session', [App\Http\Middleware\InactiveAdminLogout::class, 'extendSession'])->name('admin.extend-session');
    Route::get('/admin/session-status', [App\Http\Middleware\InactiveAdminLogout::class, 'getSessionStatus'])->name('admin.session-status');
});


// Password reset routes
Route::get('password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

Route::get('/', function () {
    return redirect()->route('login');
});

// PDF Archive Routes
Route::prefix('archive')->name('archive.')->group(function () {
    // Authentication routes
    Route::get('/login', [ArchiveAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/authenticate', [ArchiveAuthController::class, 'authenticate'])->name('authenticate');
    Route::post('/logout', [ArchiveAuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [ArchiveAuthController::class, 'profile'])->name('profile');
    Route::post('/update-profile', [ArchiveAuthController::class, 'updateProfile'])->name('update-profile');
    
    // Public routes
    Route::get('/', [PdfArchiveController::class, 'index'])->name('index');
    
    // Protected routes (require archive authentication)
    Route::middleware(['archive.auth'])->group(function () {
        Route::get('/display', [PdfArchiveController::class, 'display'])->name('display');
        Route::post('/upload', [PdfArchiveController::class, 'upload'])->name('upload');
        Route::get('/view/{id}', [PdfArchiveController::class, 'view'])->name('view');
        Route::get('/download/{id}', [PdfArchiveController::class, 'download'])->name('download');
        Route::delete('/delete/{id}', [PdfArchiveController::class, 'delete'])->name('delete');
        Route::get('/statistics', [PdfArchiveController::class, 'statistics'])->name('statistics');
        Route::get('/categories', [PdfArchiveController::class, 'getCategories'])->name('categories');
        Route::get('/search', [PdfArchiveController::class, 'search'])->name('search');
    });
});

// Archive Admin Routes (use existing admin authentication)
Route::prefix('admin/archive')->name('admin.archive.')->middleware(['auth', 'rotate.session', 'inactive.admin.logout'])->group(function () {
    Route::get('/dashboard', [ArchiveAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/archives', [ArchiveAdminController::class, 'archives'])->name('archives');
    Route::put('/archives/{id}/status', [ArchiveAdminController::class, 'updateArchiveStatus'])->name('archives.update-status');
    Route::delete('/archives/{id}', [ArchiveAdminController::class, 'deleteArchive'])->name('archives.delete');
    
    // Archive Upload Routes
    Route::get('/upload', [ArchiveAdminController::class, 'upload'])->name('upload');
    Route::post('/upload', [ArchiveAdminController::class, 'storeUpload'])->name('upload.store');
    Route::post('/center-editions', [ArchiveAdminController::class, 'getCenterEditions'])->name('center-editions');
    
    // Archive Display Routes
    Route::get('/display', [ArchiveAdminController::class, 'display'])->name('display');
    Route::post('/search', [ArchiveAdminController::class, 'searchArchives'])->name('search');
    Route::post('/search-total', [ArchiveAdminController::class, 'searchTotal'])->name('search-total');
    Route::post('/download-log', [ArchiveAdminController::class, 'downloadLog'])->name('download-log');
    Route::post('/delete', [ArchiveAdminController::class, 'deleteArchive'])->name('delete');
    Route::get('/edit/{id}', [ArchiveAdminController::class, 'edit'])->name('edit');
    Route::put('/edit/{id}', [ArchiveAdminController::class, 'update'])->name('update');
    Route::get('/copy/{id}', [ArchiveAdminController::class, 'copy'])->name('copy');
    Route::post('/copy/{id}', [ArchiveAdminController::class, 'copyToCategory'])->name('copy-to-category');
    
    // Category Management Routes
    Route::get('/categories', [ArchiveAdminController::class, 'categories'])->name('categories');
    Route::post('/categories/data', [ArchiveAdminController::class, 'getCategoriesData'])->name('categories.data');
    Route::post('/categories', [ArchiveAdminController::class, 'storeCategory'])->name('categories.store');
    Route::post('/categories/{id}/edit', [ArchiveAdminController::class, 'editCategory'])->name('categories.edit');
    Route::post('/categories/{id}/delete', [ArchiveAdminController::class, 'deleteCategory'])->name('categories.delete');
    
    // Special Dates Management Routes
    Route::get('/special-dates', [ArchiveAdminController::class, 'specialDates'])->name('special-dates');
    Route::post('/special-dates/data', [ArchiveAdminController::class, 'getSpecialDatesData'])->name('special-dates.data');
    Route::post('/special-dates', [ArchiveAdminController::class, 'storeSpecialDate'])->name('special-dates.store');
    Route::post('/special-dates/{id}/edit', [ArchiveAdminController::class, 'editSpecialDate'])->name('special-dates.edit');
    Route::post('/special-dates/{id}/delete', [ArchiveAdminController::class, 'deleteSpecialDate'])->name('special-dates.delete');
    
    Route::get('/centers', [ArchiveAdminController::class, 'centers'])->name('centers');
    Route::post('/centers', [ArchiveAdminController::class, 'storeCenter'])->name('centers.store');
    Route::put('/centers/{id}', [ArchiveAdminController::class, 'updateCenter'])->name('centers.update');
    Route::delete('/centers/{id}', [ArchiveAdminController::class, 'deleteCenter'])->name('centers.delete');
    Route::get('/users', [ArchiveAdminController::class, 'users'])->name('users');
    Route::put('/users/{id}/status', [ArchiveAdminController::class, 'updateUserStatus'])->name('users.update-status');
    Route::get('/login-logs', [ArchiveAdminController::class, 'loginLogs'])->name('login-logs');
    Route::get('/statistics', [ArchiveAdminController::class, 'getStatistics'])->name('statistics');
});

Route::middleware(SetLocale::class)->group(function () {
    Route::prefix('admin/dashboard')->name('admin.dashboard.')->middleware(['auth', 'rotate.session', 'inactive.admin.logout'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
    });
    



});
