<?php

use App\Modules\ArtisanManager\Http\Controllers\ArtisanController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('artisan-manager.middleware', ['web', 'auth.admin']))
    ->prefix(trim(config('artisan-manager.route_prefix', 'admin/artisan'), '/'))
    ->name('artisan.')
    ->group(function () {
        Route::get('/', [ArtisanController::class, 'index'])->name('index');

        // Clear commands
        Route::post('/cache-clear', [ArtisanController::class, 'cacheClear'])->name('cache.clear');
        Route::post('/config-clear', [ArtisanController::class, 'configClear'])->name('config.clear');
        Route::post('/route-clear', [ArtisanController::class, 'routeClear'])->name('route.clear');
        Route::post('/view-clear', [ArtisanController::class, 'viewClear'])->name('view.clear');
        Route::post('/event-clear', [ArtisanController::class, 'eventClear'])->name('event.clear');
        Route::post('/optimize-clear', [ArtisanController::class, 'optimizeClear'])->name('optimize.clear');

        // Cache commands
        Route::post('/config-cache', [ArtisanController::class, 'configCache'])->name('config.cache');
        Route::post('/route-cache', [ArtisanController::class, 'routeCache'])->name('route.cache');
        Route::post('/view-cache', [ArtisanController::class, 'viewCache'])->name('view.cache');
        Route::post('/event-cache', [ArtisanController::class, 'eventCache'])->name('event.cache');
        Route::post('/optimize', [ArtisanController::class, 'optimize'])->name('optimize');

        // Other commands
        Route::post('/storage-link', [ArtisanController::class, 'storageLinkCreate'])->name('storage.link');
    });
