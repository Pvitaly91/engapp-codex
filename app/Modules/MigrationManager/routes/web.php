<?php

use App\Modules\MigrationManager\Http\Controllers\MigrationController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('migration-manager.middleware', ['web', 'auth.admin']))
    ->prefix(trim(config('migration-manager.route_prefix', 'admin/migrations'), '/'))
    ->name('migrations.')
    ->group(function () {
        Route::get('/', [MigrationController::class, 'index'])->name('index');
        Route::post('/run', [MigrationController::class, 'run'])->name('run');
        Route::post('/rollback', [MigrationController::class, 'rollback'])->name('rollback');
        Route::delete('/records/{migration}', [MigrationController::class, 'destroy'])->name('destroy');
    });
