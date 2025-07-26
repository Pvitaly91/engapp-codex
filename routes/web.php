<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrainController;
use App\Http\Controllers\GrammarTestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('set-locale', function (\Illuminate\Http\Request $request) {
    $lang = $request->input('lang', 'en');
    if (!in_array($lang, ['en','uk','pl'])) $lang = 'en';
    session(['locale' => $lang]);
    app()->setLocale($lang);
    return redirect()->back();
})->name('setlocale');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/train/{topic?}', [TrainController::class, 'index'])->name('train');

use App\Http\Controllers\WordsTestController;

Route::get('/words/test', [WordsTestController::class, 'index'])->name('words.test');
Route::post('/words/test/check', [WordsTestController::class, 'check'])->name('words.test.check');
Route::post('/words/test/reset', function() {
    session()->forget('words_test_stats');
    return redirect()->route('words.test');
})->name('words.test.reset');


Route::get('/grammar-test', [GrammarTestController::class, 'index'])->name('grammar-test');
Route::post('/grammar-test', [GrammarTestController::class, 'generate'])->name('grammar-test.generate');
Route::post('/grammar-test-check', [GrammarTestController::class, 'check'])->name('grammar-test.check');
Route::get('/grammar-test-autocomplete', [GrammarTestController::class, 'autocomplete'])->name('grammar-test.autocomplete');
Route::post('/grammar-test-check-answer', [GrammarTestController::class, 'checkOneAnswer'])->name('grammar-test.checkOne');

Route::post('/grammar-test-save', [GrammarTestController::class, 'save'])->name('grammar-test.save');
Route::get('/test/{slug}', [GrammarTestController::class, 'showSavedTest'])->name('saved-test.show');
Route::get('/tests', [\App\Http\Controllers\GrammarTestController::class, 'list'])->name('saved-tests.list');
Route::delete('/tests/{test}', [\App\Http\Controllers\GrammarTestController::class, 'destroy'])->name('saved-tests.destroy');

