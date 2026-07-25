<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

require __DIR__ . '/public-site.php';

// activitylogs Route

// Main page to see all logs
Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.index');
Route::delete('/activity-logs/{id}', [ActivityLogController::class, 'destroy'])->name('activity.destroy');
Route::delete('/activity-logs-clear', [ActivityLogController::class, 'clearAll'])->name('activity.clear');

// Your existing API route for modals
Route::get('/api/activities/{module}/{id}', [ActivityLogController::class, 'getLogs']);

Route::get('/media/{media}/{conversion?}', [MediaController::class, 'show'])
    ->name('media.show');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // User Routes
    Route::resource('users', UserController::class);
    // Role Routes
    Route::resource('roles', RoleController::class);
    // Brand Routes
    Route::resource('brands', BrandController::class);
    // ProductCategory Routes
    Route::resource('product-categories', ProductCategoryController::class);


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
