<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConvertController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\ManageController;
use App\Http\Controllers\SplatViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ExploreController::class, 'index'])->name('explore');
Route::get('/s/{splat}', [SplatViewController::class, 'show'])->name('splat.show');

Route::get('/convert', [ConvertController::class, 'show'])->name('convert');
Route::post('/convert', [ConvertController::class, 'convert'])->name('convert.run');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/manage', [ManageController::class, 'index'])->name('manage.index');
    Route::get('/manage/upload', [ManageController::class, 'create'])->name('manage.create');
    Route::post('/manage/upload', [ManageController::class, 'store'])->name('manage.store');
    Route::get('/manage/{splat}/edit', [ManageController::class, 'edit'])->name('manage.edit');
    Route::put('/manage/{splat}', [ManageController::class, 'update'])->name('manage.update');
    Route::delete('/manage/{splat}', [ManageController::class, 'destroy'])->name('manage.destroy');
});
