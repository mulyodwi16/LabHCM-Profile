<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\GalleryController as AdminGallery;
use App\Http\Controllers\Admin\ProjectController as AdminProject;
use App\Http\Controllers\Admin\UserController as AdminUser;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PublicProjectController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/',                   [PublicController::class, 'home'])->name('home');
Route::get('/people',             [DirectoryController::class, 'index'])->name('people');
Route::get('/projects/{project}', [PublicProjectController::class, 'show'])->name('projects.show');

// Breeze needs /dashboard for post-login redirect
Route::get('/dashboard', function () {
    return redirect(auth()->user()?->isAdmin() ? route('admin.dashboard') : route('profile.edit'));
})->middleware(['auth', 'verified'])->name('dashboard');

// Authenticated — own profile
Route::middleware(['auth'])->group(function () {
    Route::get('/profile',                            [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',                          [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/documents',                 [ProfileController::class, 'uploadDocument'])->name('profile.documents.store');
    Route::delete('/profile/documents/{document}',    [ProfileController::class, 'destroyDocument'])->name('profile.documents.destroy');
});

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');

    Route::get('/{role}',                 [AdminUser::class, 'index'])->whereIn('role', ['member', 'alumni', 'dosen'])->name('users.index');
    Route::get('/{role}/create',          [AdminUser::class, 'create'])->whereIn('role', ['member', 'alumni', 'dosen'])->name('users.create');
    Route::post('/{role}',                [AdminUser::class, 'store'])->whereIn('role', ['member', 'alumni', 'dosen'])->name('users.store');
    Route::get('/{role}/{user}/edit',     [AdminUser::class, 'edit'])->whereIn('role', ['member', 'alumni', 'dosen'])->name('users.edit');
    Route::patch('/{role}/{user}',        [AdminUser::class, 'update'])->whereIn('role', ['member', 'alumni', 'dosen'])->name('users.update');
    Route::delete('/{role}/{user}',       [AdminUser::class, 'destroy'])->whereIn('role', ['member', 'alumni', 'dosen'])->name('users.destroy');

    Route::resource('projects', AdminProject::class)->except(['show']);
    Route::resource('gallery',  AdminGallery::class)->except(['show'])->parameters(['gallery' => 'galleryItem']);
});

if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
}
