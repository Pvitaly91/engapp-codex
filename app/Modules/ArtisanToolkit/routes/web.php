<?php

use App\Modules\ArtisanToolkit\Http\Controllers\ArtisanCommandController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('artisan-toolkit.middleware', ['web', 'auth.admin']))
    ->prefix(trim(config('artisan-toolkit.route_prefix', 'admin/artisan'), '/'))
    ->name('artisan-toolkit.')
    ->group(function () {
        Route::get('/', [ArtisanCommandController::class, 'index'])->name('index');
        Route::post('/{commandKey}', [ArtisanCommandController::class, 'run'])->name('run');
    });
