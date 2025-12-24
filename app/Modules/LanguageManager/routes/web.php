<?php

use App\Modules\LanguageManager\Http\Controllers\LanguageManagerController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('language-manager.middleware', ['web']))
    ->prefix(config('language-manager.route_prefix', 'admin/languages'))
    ->as(config('language-manager.route_name_prefix', 'language-manager.'))
    ->group(function () {
        Route::get('/', [LanguageManagerController::class, 'index'])->name('index');
        Route::get('/create', [LanguageManagerController::class, 'create'])->name('create');
        Route::post('/', [LanguageManagerController::class, 'store'])->name('store');
        Route::get('/{language}/edit', [LanguageManagerController::class, 'edit'])->name('edit');
        Route::put('/{language}', [LanguageManagerController::class, 'update'])->name('update');
        Route::delete('/{language}', [LanguageManagerController::class, 'destroy'])->name('destroy');
        Route::post('/{language}/set-default', [LanguageManagerController::class, 'setDefault'])->name('set-default');
        Route::post('/{language}/toggle-active', [LanguageManagerController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/update-order', [LanguageManagerController::class, 'updateOrder'])->name('update-order');
    });
