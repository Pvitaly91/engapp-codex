<?php

use App\Modules\FileManager\Http\Controllers\FileManagerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FileManagerController::class, 'index'])->name('index');
Route::get('/tree', [FileManagerController::class, 'tree'])->name('tree');
Route::get('/info', [FileManagerController::class, 'info'])->name('info');
Route::get('/preview', [FileManagerController::class, 'preview'])->name('preview');
Route::get('/download', [FileManagerController::class, 'download'])->name('download');
Route::post('/update', [FileManagerController::class, 'update'])->name('update');
