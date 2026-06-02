<?php

use App\Modules\PromptGenerator\Http\Controllers\PromptGeneratorController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('prompt-generator.middleware', ['web', 'auth.admin']))
    ->prefix(trim(config('prompt-generator.route_prefix', 'admin/prompt-generator'), '/'))
    ->name('prompt-generator.')
    ->group(function () {
        Route::get('/', [PromptGeneratorController::class, 'index'])->name('index');
        Route::post('/', [PromptGeneratorController::class, 'generate'])->name('generate');
    });
