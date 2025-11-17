<?php

use App\Modules\ArtisanManager\Http\Controllers\ArtisanManagerController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('artisan-manager.middleware', ['web', 'auth.admin']))
    ->prefix(trim(config('artisan-manager.route_prefix', 'admin/artisan'), '/'))
    ->name('artisan.')
    ->group(function () {
        Route::get('/', [ArtisanManagerController::class, 'index'])->name('index');
        Route::post('/execute/{command}', [ArtisanManagerController::class, 'execute'])->name('execute');
    });
