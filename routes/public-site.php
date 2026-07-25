<?php

use App\Http\Controllers\PublicSite\PublicSitePageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::controller(PublicSitePageController::class)->group(function () {
    Route::get('/home', 'home')->name('publicSite.home');
    Route::get('/', function () {
    return Inertia::render('login');
});
    Route::get('/about', 'about')->name('publicSite.about');
});
