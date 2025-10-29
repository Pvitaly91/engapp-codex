<?php

use App\Modules\DatabaseStructure\Http\Controllers\DatabaseStructureController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DatabaseStructureController::class, 'index'])->name('index');
Route::get('/tables/{table}/records', [DatabaseStructureController::class, 'records'])
    ->where('table', '[^/]+')
    ->name('records');
Route::delete('/tables/{table}/records', [DatabaseStructureController::class, 'destroy'])
    ->where('table', '[^/]+')
    ->name('destroy');
