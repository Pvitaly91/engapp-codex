<?php

use App\Modules\DatabaseStructure\Http\Controllers\DatabaseStructureController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DatabaseStructureController::class, 'index'])->name('index');
Route::get('/tables/{table}/structure', [DatabaseStructureController::class, 'structure'])
    ->where('table', '[^/]+')
    ->name('structure');
Route::get('/tables/{table}/records', [DatabaseStructureController::class, 'records'])
    ->where('table', '[^/]+')
    ->name('records');
Route::post('/tables/{table}/records/record', [DatabaseStructureController::class, 'record'])
    ->where('table', '[^/]+')
    ->name('record');
Route::post('/tables/{table}/records/value', [DatabaseStructureController::class, 'value'])
    ->where('table', '[^/]+')
    ->name('value');
Route::put('/tables/{table}/records/value', [DatabaseStructureController::class, 'update'])
    ->where('table', '[^/]+')
    ->name('update');
Route::delete('/tables/{table}/records', [DatabaseStructureController::class, 'destroy'])
    ->where('table', '[^/]+')
    ->name('destroy');
Route::post('/tables/{table}/columns/{column}/manual-foreign', [DatabaseStructureController::class, 'storeManualForeign'])
    ->where(['table' => '[^/]+', 'column' => '[^/]+'])
    ->name('manual-foreign.store');
Route::delete('/tables/{table}/columns/{column}/manual-foreign', [DatabaseStructureController::class, 'destroyManualForeign'])
    ->where(['table' => '[^/]+', 'column' => '[^/]+'])
    ->name('manual-foreign.destroy');
