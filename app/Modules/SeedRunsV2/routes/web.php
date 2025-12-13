<?php

use Illuminate\Support\Facades\Route;

Route::middleware(config('seed-runs-v2.middleware', ['web', 'auth.admin']))
    ->prefix(trim(config('seed-runs-v2.route_prefix', 'admin/seed-runs-v2'), '/'))
    ->name('seed-runs.v2.')
    ->group(function () {
        Route::get('/', function () {
            return view('seed-runs-v2::index');
        })->name('index');
    });
