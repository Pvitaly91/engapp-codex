<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ArtisanCommands\Http\Controllers\ArtisanCommandsController;

Route::middleware(config('artisan-commands.middleware', ['web', 'auth.admin']))
    ->prefix(config('artisan-commands.route_prefix', 'admin/artisan-commands'))
    ->name('artisan-commands.')
    ->group(function () {
        Route::get('/', [ArtisanCommandsController::class, 'index'])->name('index');
        Route::post('/execute', [ArtisanCommandsController::class, 'execute'])->name('execute');
    });
