<?php

use App\Modules\FileManager\Http\Controllers\FileManagerController;
use Illuminate\Support\Facades\Route;

$prefix = config('file-manager.route_prefix', 'admin/file-manager');
$middleware = config('file-manager.middleware', ['web', 'auth.admin']);

Route::middleware($middleware)
    ->prefix($prefix)
    ->name('file-manager.')
    ->group(function () {
        Route::get('/', [FileManagerController::class, 'index'])->name('index');
        Route::get('/browse', [FileManagerController::class, 'browse'])->name('browse');
        Route::get('/file', [FileManagerController::class, 'show'])->name('show');
    });
