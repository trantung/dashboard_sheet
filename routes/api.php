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
use App\Http\Controllers\OrderController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
})->name('api.user');

Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.logout');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum')->name('api.user.info');

// Google OAuth routes
Route::post('/auth/google', [GoogleAuthController::class, 'handleGoogleAuth'])->name('api.auth.google');
Route::post('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('api.auth.google.callback');

// Profile routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('api.profile.avatar.upload');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('api.profile.avatar.remove');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('api.profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('api.profile.destroy');
});

// Workspace routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('api.workspaces.index');
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('api.workspaces.store');
    Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show'])->name('api.workspaces.show');
    Route::put('/workspaces/{workspace}', [WorkspaceController::class, 'update'])->name('api.workspaces.update');
    Route::delete('/workspaces/{workspace}', [WorkspaceController::class, 'destroy'])->name('api.workspaces.destroy');
    Route::post('/workspaces/{workspace}/users', [WorkspaceController::class, 'addUser'])->name('api.workspaces.users.add');
    Route::delete('/workspaces/{workspace}/users', [WorkspaceController::class, 'removeUser'])->name('api.workspaces.users.remove');
});

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

// Orders management routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders/{site}', [OrderController::class, 'index'])->name('api.orders.index');
    Route::get('/orders/{site}/show/{id}', [OrderController::class, 'show'])->name('api.orders.show');
    Route::get('/orders/{site}/export/excel', [OrderController::class, 'export'])->name('api.orders.export');
});

// Domain test routes
Route::post('/domain/test', [ApiTestController::class, 'index'])->name('api.domain.test.index');
Route::post('/domain/test/import_data', [ApiTestController::class, 'importData'])->name('api.domain.test.getdata');
Route::post('/domain/test/delete', [ApiTestController::class, 'deleteDomain'])->name('api.domain.test.delete');

Route::prefix('api_ecomerce')
    ->middleware('api')
    ->group(base_path('routes/api_ecomerce.php'));