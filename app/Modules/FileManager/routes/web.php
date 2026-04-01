<?php

use App\Modules\FileManager\Http\Controllers\FileManagerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FileManagerController::class, 'index'])->name('index');
Route::get('/open/{path?}', [FileManagerController::class, 'index'])
    ->where('path', '.*')
    ->name('show');
Route::get('/embed', [FileManagerController::class, 'embed'])->name('embed');
Route::get('/embed/bootstrap.js', [FileManagerController::class, 'embedBootstrap'])->name('embed.bootstrap');
Route::get('/embed/open/{path?}', [FileManagerController::class, 'embed'])
    ->where('path', '.*')
    ->name('embed.show');
Route::get('/embed/fragment', [FileManagerController::class, 'embedFragment'])->name('embed.fragment');
Route::get('/embed/fragment/open/{path?}', [FileManagerController::class, 'embedFragment'])
    ->where('path', '.*')
    ->name('embed.fragment.show');
Route::get('/ide', [FileManagerController::class, 'ide'])->name('ide');
Route::get('/assets/{path}', [FileManagerController::class, 'asset'])
    ->where('path', '.*')
    ->name('asset');
Route::get('/tree', [FileManagerController::class, 'tree'])->name('tree');
Route::get('/info', [FileManagerController::class, 'info'])->name('info');
Route::get('/preview', [FileManagerController::class, 'preview'])->name('preview');
Route::get('/content', [FileManagerController::class, 'content'])->name('content');
Route::get('/download', [FileManagerController::class, 'download'])->name('download');
Route::post('/update', [FileManagerController::class, 'update'])->name('update');

Route::prefix('/v2')->name('v2.')->group(function () {
    Route::get('/', [FileManagerController::class, 'v2'])->name('index');
    Route::get('/open/{path?}', [FileManagerController::class, 'v2'])
        ->where('path', '.*')
        ->name('show');
    Route::get('/tree', [FileManagerController::class, 'tree'])->name('tree');
    Route::get('/info', [FileManagerController::class, 'info'])->name('info');
    Route::get('/read', [FileManagerController::class, 'read'])->name('read');
    Route::get('/download', [FileManagerController::class, 'download'])->name('download');
    Route::post('/update', [FileManagerController::class, 'update'])->name('update');
    Route::post('/create-file', [FileManagerController::class, 'createFile'])->name('create-file');
    Route::post('/create-directory', [FileManagerController::class, 'createDirectory'])->name('create-directory');
    Route::delete('/delete', [FileManagerController::class, 'delete'])->name('delete');
});
