<?php

use App\Modules\DatabaseStructure\Http\Controllers\DatabaseStructureController;
use Illuminate\Support\Facades\Route;

Route::get('/', DatabaseStructureController::class)->name('index');
