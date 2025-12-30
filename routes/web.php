<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GrammarTestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\IrregularVerbsTestController;
use App\Http\Controllers\SiteSearchController;
use App\Http\Controllers\TestJsV2Controller;
use App\Http\Controllers\TheoryController;
use App\Http\Controllers\WordSearchController;
use App\Http\Controllers\WordsTestController;
use Illuminate\Support\Facades\Route;

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

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.show');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [HomeController::class, 'index'])->name('home');

// Public locale switching route
Route::get('/set-locale', function (\Illuminate\Http\Request $request) {
    $lang = $request->input('lang', 'uk');
    
    // Get supported locales from Language Manager or fallback to config
    $supportedLocales = ['uk', 'en']; // Default fallback
    $defaultLocale = 'uk';
    
    if (\Illuminate\Support\Facades\Schema::hasTable('languages')) {
        $codes = \App\Modules\LanguageManager\Services\LocaleService::getActiveLanguages()->pluck('code')->toArray();
        if (!empty($codes)) {
            $supportedLocales = $codes;
        }
        $default = \App\Modules\LanguageManager\Services\LocaleService::getDefaultLanguage();
        if ($default) {
            $defaultLocale = $default->code;
        }
    } else {
        $supportedLocales = config('app.supported_locales', ['uk', 'en']);
        $defaultLocale = config('app.locale', 'uk');
    }
    
    if (! in_array($lang, $supportedLocales)) {
        $lang = $defaultLocale;
    }
    session(['locale' => $lang]);
    app()->setLocale($lang);

    // Set cookie for 1 year
    $cookie = cookie('locale', $lang, 60 * 24 * 365);

    // Use intended() with fallback to home for safety
    $referer = $request->headers->get('referer');
    $host = $request->getHost();
    
    // Validate referer is from same host to prevent open redirect
    if ($referer && parse_url($referer, PHP_URL_HOST) === $host) {
        return redirect()->back()->withCookie($cookie);
    }
    
    return redirect()->route('home')->withCookie($cookie);
})->name('locale.set');

Route::prefix('words/test')->group(function () {
    Route::get('/', [WordsTestController::class, 'index'])->name('words.test');
    Route::get('/medium', [WordsTestController::class, 'index'])->name('words.test.medium')->defaults('difficulty', 'medium');
    Route::get('/hard', [WordsTestController::class, 'index'])->name('words.test.hard')->defaults('difficulty', 'hard');

    Route::get('/state', [WordsTestController::class, 'state'])->name('words.test.state');
    Route::get('/medium/state', [WordsTestController::class, 'state'])->name('words.test.state.medium')->defaults('difficulty', 'medium');
    Route::get('/hard/state', [WordsTestController::class, 'state'])->name('words.test.state.hard')->defaults('difficulty', 'hard');

    Route::post('/check', [WordsTestController::class, 'check'])->name('words.test.check');
    Route::post('/medium/check', [WordsTestController::class, 'check'])->name('words.test.check.medium')->defaults('difficulty', 'medium');
    Route::post('/hard/check', [WordsTestController::class, 'check'])->name('words.test.check.hard')->defaults('difficulty', 'hard');

    Route::post('/reset', [WordsTestController::class, 'reset'])->name('words.test.reset');
    Route::post('/medium/reset', [WordsTestController::class, 'reset'])->name('words.test.reset.medium')->defaults('difficulty', 'medium');
    Route::post('/hard/reset', [WordsTestController::class, 'reset'])->name('words.test.reset.hard')->defaults('difficulty', 'hard');

    Route::post('/set-study-language', [WordsTestController::class, 'setStudyLanguage'])->name('words.test.set-study-language');
});

Route::prefix('verbs/test')->group(function () {
    Route::get('/', [IrregularVerbsTestController::class, 'index'])->name('verbs.test');
    Route::get('/data', [IrregularVerbsTestController::class, 'data'])->name('verbs.test.data');
});

// Public pages routes (no authentication required)
Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
Route::get('/pages/{category:slug}', [PageController::class, 'category'])->name('pages.category');
Route::get('/pages/{category:slug}/{pageSlug}', [PageController::class, 'show'])->name('pages.show');

// Public Theory pages routes (no authentication required)
Route::get('/theory', [TheoryController::class, 'index'])->name('theory.index');
Route::get('/theory/{category:slug}', [TheoryController::class, 'category'])->name('theory.category');
Route::get('/theory/{category:slug}/{pageSlug}', [TheoryController::class, 'show'])->name('theory.show');

// Public catalog and search
Route::get('/catalog/tests-cards', [GrammarTestController::class, 'catalogAggregated'])->name('catalog.tests-cards');
Route::get('/catalog-tests/cards', fn () => redirect()->route('catalog.tests-cards')); // legacy
Route::get('/tests/cards', fn () => redirect()->route('catalog.tests-cards')); // legacy

Route::get('/search', SiteSearchController::class)->name('site.search');
Route::get('/words', [WordSearchController::class, 'search'])->name('words.search');

Route::prefix('test')->group(function () {
    Route::get('/{slug}', [TestJsV2Controller::class, 'showSavedTestJsV2'])->name('test.show');
    Route::get('/{slug}/step', [TestJsV2Controller::class, 'showSavedTestJsStepV2'])->name('test.step');
    Route::get('/{slug}/step/input', [TestJsV2Controller::class, 'showSavedTestJsStepInputV2'])->name('test.step-input');
    Route::get('/{slug}/step/manual', [TestJsV2Controller::class, 'showSavedTestJsStepManualV2'])->name('test.step-manual');
    Route::get('/{slug}/step/select', [TestJsV2Controller::class, 'showSavedTestJsStepSelectV2'])->name('test.step-select');
    Route::get('/{slug}/select', [TestJsV2Controller::class, 'showSavedTestJsSelectV2'])->name('test.select');
    Route::get('/{slug}/input', [TestJsV2Controller::class, 'showSavedTestJsInputV2'])->name('test.input');
    Route::get('/{slug}/manual', [TestJsV2Controller::class, 'showSavedTestJsManualV2'])->name('test.manual');
});
