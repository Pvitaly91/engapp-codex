<?php

use App\Http\Controllers\MarkerTheoryTagController;
use App\Http\Controllers\WordSearchController;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;
use App\Models\Word;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/search', function (\Illuminate\Http\Request $request) {
    $lang = $request->get('lang', 'uk');
    $words = Word::with(['translates' => function($q) use ($lang) {
        $q->where('lang', $lang);
    }])
        ->where('word', 'like', $request->q.'%')
        ->limit(8)
        ->get();

    return $words->map(function ($word) use ($lang) {
        return [
            'en' => $word->word,
            'translation' => optional($word->translates->first())->translation ?? '',
        ];
    });
})->name('api.search');

// Test autocomplete must stay outside both file-backed session and rate-limit
// locks. The lookup is read-only, indexed, capped at ten rows and browser
// cached; progress writes can otherwise make the dropdown wait for seconds.
Route::get('/word-search/{lang?}', [WordSearchController::class, 'search'])
    ->where('lang', 'uk|en|pl')
    ->withoutMiddleware(ThrottleRequests::class . ':api')
    ->name('api.words.search');

