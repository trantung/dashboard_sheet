<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\TempController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\ApiTestController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\NavbarController;
use App\Http\Controllers\EmailController;

// Temp routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/temps', [TempController::class, 'store'])->name('api.temps.store');
    Route::put('/temps/{temp}/google-sheet', [TempController::class, 'updateGoogleSheet'])->name('api.temps.google_sheet.update');
    Route::get('/temps/{temp}/google-sheet-data', [TempController::class, 'getGoogleSheetData'])->name('api.temps.google_sheet.data');
    Route::post('/temps/{temp}/finish', [TempController::class, 'finish'])->name('api.temps.finish');
});

// Site routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/sites', [SiteController::class, 'index'])->name('api.sites.index');
    Route::get('/sites/{site}', [SiteController::class, 'show'])->name('api.sites.show');
    Route::put('/sites/{site}', [SiteController::class, 'update'])->name('api.sites.update');
    Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('api.sites.destroy');
    Route::post('/sites/{site}/subdomain', [SiteController::class, 'updateSubdomain'])->name('api.sites.subdomain.update');
    Route::put('/sites/{site}/setting/update', [SiteController::class, 'settingUpdate'])->name('api.sites.setting.update');
    Route::post('/sites/{site}/sync', [SiteController::class, 'syncSheets'])->name('api.sites.sync');
});

// Page routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pages/{site}', [PageController::class, 'index'])->name('api.pages.index');
    Route::post('/pages/{site}', [PageController::class, 'store'])->name('api.pages.store');
    Route::put('/pages/{site}/update/{page}', [PageController::class, 'update'])->name('api.pages.update');
    Route::delete('/pages/{site}/delete/{page}', [PageController::class, 'destroy'])->name('api.pages.destroy');
});

// Navbar routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/navbar-items/{site}', [NavbarController::class, 'index'])->name('api.navbar.index');
    Route::post('/navbar-items/{site}', [NavbarController::class, 'store'])->name('api.navbar.store');
    Route::put('/navbar-items/{site}/update/{id}', [NavbarController::class, 'update'])->name('api.navbar.update');
    Route::delete('/navbar-items/{site}/delete/{id}', [NavbarController::class, 'destroy'])->name('api.navbar.destroy');
    Route::post('/navbar-items/reorder', [NavbarController::class, 'reorder'])->name('api.navbar.reorder');
});

// Email management routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/emails/{site}', [EmailController::class, 'index'])->name('api.emails.index');
    Route::get('/emails/stats/{site}', [EmailController::class, 'stats'])->name('api.emails.stats');
});

// Domain test routes
Route::post('/domain/test', [ApiTestController::class, 'index'])->name('api.domain.test.index');
Route::post('/domain/test/import_data', [ApiTestController::class, 'importData'])->name('api.domain.test.getdata');
Route::post('/domain/test/delete', [ApiTestController::class, 'deleteDomain'])->name('api.domain.test.delete');
