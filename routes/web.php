<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Farmer\ActivityController;
use App\Http\Controllers\Farmer\DashboardController as FarmerDashboardController;
use App\Http\Controllers\Farmer\HarvestController;
use App\Http\Controllers\Farmer\LandController;
use App\Http\Controllers\Farmer\PlantingSeasonController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => to_route('login'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:register')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])
            ->whereNumber('user')->name('users.status');
        Route::resource('users', UserController::class)->only(['index', 'show'])
            ->where(['user' => '[0-9]+']);
    });
    Route::name('farmer.')->middleware('role:petani')->group(function () {
        Route::get('/dashboard', FarmerDashboardController::class)->name('dashboard');
        Route::resource('lands', LandController::class)->where(['land' => '[0-9]+']);
        Route::scopeBindings()->group(function () {
            Route::resource('lands.planting-seasons.harvests', HarvestController::class)
                ->where(['land' => '[0-9]+', 'planting_season' => '[0-9]+', 'harvest' => '[0-9]+']);
            Route::patch('/lands/{land}/planting-seasons/{planting_season}/activities/{activity}/finish', [ActivityController::class, 'finish'])
                ->whereNumber(['land', 'planting_season', 'activity'])->name('lands.planting-seasons.activities.finish');
            Route::resource('lands.planting-seasons.activities', ActivityController::class)
                ->where(['land' => '[0-9]+', 'planting_season' => '[0-9]+', 'activity' => '[0-9]+']);
            Route::patch('/lands/{land}/planting-seasons/{planting_season}/finish', [PlantingSeasonController::class, 'finish'])
                ->whereNumber(['land', 'planting_season'])->name('lands.planting-seasons.finish');
            Route::patch('/lands/{land}/planting-seasons/{planting_season}/reopen', [PlantingSeasonController::class, 'reopen'])
                ->whereNumber(['land', 'planting_season'])->name('lands.planting-seasons.reopen');
            Route::resource('lands.planting-seasons', PlantingSeasonController::class)
                ->where(['land' => '[0-9]+', 'planting_season' => '[0-9]+']);
        });
    });
});
