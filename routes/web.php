<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/test/doc', [SiteController::class, 'showGoogleDocHtml'])->name('web.sites.test');
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

// Route::get('/test/doc', [SiteController::class, 'showGoogleDocHtml'])->name('web.sites.test');