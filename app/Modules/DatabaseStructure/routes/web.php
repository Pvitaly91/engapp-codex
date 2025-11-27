<?php

use App\Modules\DatabaseStructure\Http\Controllers\DatabaseStructureController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DatabaseStructureController::class, 'index'])->name('index');
Route::get('/content-management', [DatabaseStructureController::class, 'contentManagement'])
    ->name('content-management');
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
Route::post('/content-management/menu', [DatabaseStructureController::class, 'storeContentManagementMenu'])
    ->name('content-management.menu.store');
Route::put('/content-management/menu', [DatabaseStructureController::class, 'updateContentManagementMenu'])
    ->name('content-management.menu.update');
Route::delete('/content-management/menu/{table}', [DatabaseStructureController::class, 'destroyContentManagementMenu'])
    ->where('table', '[^/]+')
    ->name('content-management.menu.destroy');
Route::get('/tables/{table}/filters/{scope}', [DatabaseStructureController::class, 'filters'])
    ->where(['table' => '[^/]+', 'scope' => 'records|content'])
    ->name('filters.index');
Route::post('/tables/{table}/filters/{scope}', [DatabaseStructureController::class, 'storeFilter'])
    ->where(['table' => '[^/]+', 'scope' => 'records|content'])
    ->name('filters.store');
Route::patch('/tables/{table}/filters/{scope}/{filter}/use', [DatabaseStructureController::class, 'useFilter'])
    ->where(['table' => '[^/]+', 'scope' => 'records|content', 'filter' => '[^/]+'])
    ->name('filters.use');
Route::patch('/tables/{table}/filters/{scope}/default', [DatabaseStructureController::class, 'setDefaultFilter'])
    ->where(['table' => '[^/]+', 'scope' => 'records|content'])
    ->name('filters.default');
Route::delete('/tables/{table}/filters/{scope}/{filter}', [DatabaseStructureController::class, 'destroyFilter'])
    ->where(['table' => '[^/]+', 'scope' => 'records|content', 'filter' => '[^/]+'])
    ->name('filters.destroy');
Route::get('/search-presets', [DatabaseStructureController::class, 'searchPresets'])
    ->name('search-presets.index');
Route::post('/search-presets', [DatabaseStructureController::class, 'storeSearchPreset'])
    ->name('search-presets.store');
Route::patch('/search-presets/{preset}/use', [DatabaseStructureController::class, 'useSearchPreset'])
    ->where('preset', '[^/]+')
    ->name('search-presets.use');
Route::delete('/search-presets/{preset}', [DatabaseStructureController::class, 'destroySearchPreset'])
    ->where('preset', '[^/]+')
    ->name('search-presets.destroy');
Route::get('/keyword-search', [DatabaseStructureController::class, 'keywordSearch'])
    ->name('keyword-search');
