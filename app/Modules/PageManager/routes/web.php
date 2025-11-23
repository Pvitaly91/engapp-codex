<?php

use App\Modules\PageManager\Http\Controllers\PageManagerController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('page-manager.middleware', ['web']))
    ->prefix(config('page-manager.route_prefix', 'admin/pages/manage'))
    ->as(config('page-manager.route_name_prefix', 'pages.manage.'))
    ->group(function () {
        Route::get('/', [PageManagerController::class, 'index'])->name('index');
        Route::get('/create', [PageManagerController::class, 'create'])->name('create');
        Route::post('/', [PageManagerController::class, 'store'])->name('store');
        Route::post('/export', [PageManagerController::class, 'exportToJson'])->name('export');
        Route::get('/export/view', [PageManagerController::class, 'viewExportedJson'])->name('export.view');
        Route::get('/export/download', [PageManagerController::class, 'downloadExportedJson'])->name('export.download');
        Route::get('/{page}/edit', [PageManagerController::class, 'edit'])
            ->whereNumber('page')
            ->name('edit');
        Route::put('/{page}', [PageManagerController::class, 'update'])
            ->whereNumber('page')
            ->name('update');
        Route::delete('/{page}', [PageManagerController::class, 'destroy'])
            ->whereNumber('page')
            ->name('destroy');

        Route::get('/categories/create', [PageManagerController::class, 'createCategory'])->name('categories.create');
        Route::post('/categories', [PageManagerController::class, 'storeCategory'])->name('categories.store');
        Route::get('/categories/{category}/edit', [PageManagerController::class, 'editCategory'])
            ->name('categories.edit');
        Route::put('/categories/{category}', [PageManagerController::class, 'updateCategory'])
            ->name('categories.update');
        Route::delete('/categories/{category}', [PageManagerController::class, 'destroyCategory'])
            ->name('categories.destroy');
        Route::delete('/categories-empty', [PageManagerController::class, 'destroyEmptyCategories'])->name('categories.destroy-empty');

        Route::get('/categories/{category}/blocks', [PageManagerController::class, 'categoryBlocks'])
            ->name('categories.blocks.index');
        Route::get('/categories/{category}/blocks/create', [PageManagerController::class, 'createCategoryBlock'])
            ->name('categories.blocks.create');
        Route::post('/categories/{category}/blocks', [PageManagerController::class, 'storeCategoryBlock'])
            ->name('categories.blocks.store');
        Route::get('/categories/{category}/blocks/{block}/edit', [PageManagerController::class, 'editCategoryBlock'])
            ->whereNumber('block')
            ->name('categories.blocks.edit');
        Route::put('/categories/{category}/blocks/{block}', [PageManagerController::class, 'updateCategoryBlock'])
            ->whereNumber('block')
            ->name('categories.blocks.update');
        Route::delete('/categories/{category}/blocks/{block}', [PageManagerController::class, 'destroyCategoryBlock'])
            ->whereNumber('block')
            ->name('categories.blocks.destroy');

        Route::get('/{page}/blocks/create', [PageManagerController::class, 'createBlock'])
            ->whereNumber('page')
            ->name('blocks.create');
        Route::post('/{page}/blocks', [PageManagerController::class, 'storeBlock'])
            ->whereNumber('page')
            ->name('blocks.store');
        Route::get('/{page}/blocks/{block}/edit', [PageManagerController::class, 'editBlock'])
            ->whereNumber('page')
            ->whereNumber('block')
            ->name('blocks.edit');
        Route::put('/{page}/blocks/{block}', [PageManagerController::class, 'updateBlock'])
            ->whereNumber('page')
            ->whereNumber('block')
            ->name('blocks.update');
        Route::delete('/{page}/blocks/{block}', [PageManagerController::class, 'destroyBlock'])
            ->whereNumber('page')
            ->whereNumber('block')
            ->name('blocks.destroy');
    });
