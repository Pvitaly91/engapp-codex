<?php

use App\Http\Controllers\GrammarTestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrainController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionHelpController;

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
    if (! in_array($lang, ['en', 'uk', 'pl'])) {
        $lang = 'en';
    }
    session(['locale' => $lang]);
    app()->setLocale($lang);

    return redirect()->back();
})->name('setlocale');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/train/{topic?}', [TrainController::class, 'index'])->name('train');

use App\Http\Controllers\WordsTestController;
use App\Http\Controllers\SentenceTranslationTestController;

Route::get('/words/test', [WordsTestController::class, 'index'])->name('words.test');
Route::post('/words/test/check', [WordsTestController::class, 'check'])->name('words.test.check');
Route::post('/words/test/reset', function () {
    session()->forget('words_test_stats');

    return redirect()->route('words.test');
})->name('words.test.reset');

Route::get('/translate/test', [SentenceTranslationTestController::class, 'index'])->name('translate.test');
Route::post('/translate/test/check', [SentenceTranslationTestController::class, 'check'])->name('translate.test.check');
Route::post('/translate/test/reset', [SentenceTranslationTestController::class, 'reset'])->name('translate.test.reset');
Route::get('/translate/test2', [SentenceTranslationTestController::class, 'indexV2'])->name('translate.test2');
Route::post('/translate/test2/check', [SentenceTranslationTestController::class, 'checkV2'])->name('translate.test2.check');
Route::post('/translate/test2/reset', [SentenceTranslationTestController::class, 'resetV2'])->name('translate.test2.reset');

Route::get('/grammar-test', [GrammarTestController::class, 'index'])->name('grammar-test');
Route::post('/grammar-test', [GrammarTestController::class, 'generate'])->name('grammar-test.generate');
Route::post('/grammar-test-check', [GrammarTestController::class, 'check'])->name('grammar-test.check');
Route::get('/grammar-test-autocomplete', [GrammarTestController::class, 'autocomplete'])->name('grammar-test.autocomplete');
Route::post('/grammar-test-check-answer', [GrammarTestController::class, 'checkOneAnswer'])->name('grammar-test.checkOne');

Route::post('/grammar-test-save', [GrammarTestController::class, 'save'])->name('grammar-test.save');
Route::get('/test/{slug}', [GrammarTestController::class, 'showSavedTest'])->name('saved-test.show');
Route::get('/test/{slug}/random', [GrammarTestController::class, 'showSavedTestRandom'])->name('saved-test.random');
Route::get('/test/{slug}/step', [GrammarTestController::class, 'showSavedTestStep'])->name('saved-test.step');
Route::post('/test/{slug}/refresh-description', [GrammarTestController::class, 'refreshDescription'])->name('saved-test.refresh');
Route::post('/test/{slug}/refresh-description-gemini', [GrammarTestController::class, 'refreshDescriptionGemini'])->name('saved-test.refresh-gemini');
Route::post('/test/{slug}/step/check', [GrammarTestController::class, 'checkSavedTestStep'])->name('saved-test.step.check');
Route::post('/test/{slug}/step/reset', [GrammarTestController::class, 'resetSavedTestStep'])->name('saved-test.step.reset');
Route::post('/test/{slug}/step/determine-tense', [GrammarTestController::class, 'determineTense'])->name('saved-test.step.determine-tense');
Route::post('/test/{slug}/step/determine-tense-gemini', [GrammarTestController::class, 'determineTenseGemini'])->name('saved-test.step.determine-tense-gemini');
Route::post('/test/{slug}/step/determine-level', [GrammarTestController::class, 'determineLevel'])->name('saved-test.step.determine-level');
Route::post('/test/{slug}/step/determine-level-gemini', [GrammarTestController::class, 'determineLevelGemini'])->name('saved-test.step.determine-level-gemini');
Route::post('/test/{slug}/step/set-level', [GrammarTestController::class, 'setLevel'])->name('saved-test.step.set-level');
Route::post('/test/{slug}/step/add-tag', [GrammarTestController::class, 'addTag'])->name('saved-test.step.add-tag');
Route::delete('/test/{slug}/step/remove-tag', [GrammarTestController::class, 'removeTag'])->name('saved-test.step.remove-tag');
Route::delete('/test/{slug}/question/{question}', [GrammarTestController::class, 'deleteQuestion'])->name('saved-test.question.destroy');
Route::get('/tests', [\App\Http\Controllers\GrammarTestController::class, 'list'])->name('saved-tests.list');
Route::delete('/tests/{test}', [\App\Http\Controllers\GrammarTestController::class, 'destroy'])->name('saved-tests.destroy');
Route::get('/tests/cards', [\App\Http\Controllers\GrammarTestController::class, 'catalog'])->name('saved-tests.cards');

use App\Http\Controllers\AiTestController;
Route::get('/ai-test', [AiTestController::class, 'form'])->name('ai-test.form');
Route::post('/ai-test/start', [AiTestController::class, 'start'])->name('ai-test.start');
Route::get('/ai-test/step', [AiTestController::class, 'step'])->name('ai-test.step');
Route::get('/ai-test/next', [AiTestController::class, 'next'])->name('ai-test.next');
Route::post('/ai-test/check', [AiTestController::class, 'check'])->name('ai-test.check');
Route::post('/ai-test/skip', [AiTestController::class, 'skip'])->name('ai-test.skip');
Route::post('/ai-test/reset', [AiTestController::class, 'reset'])->name('ai-test.reset');
Route::post('/ai-test/provider', [AiTestController::class, 'provider'])->name('ai-test.provider');
Route::post('/ai-test/step/determine-tense', [AiTestController::class, 'determineTense'])->name('ai-test.step.determine-tense');
Route::post('/ai-test/step/determine-tense-gemini', [AiTestController::class, 'determineTenseGemini'])->name('ai-test.step.determine-tense-gemini');
Route::post('/ai-test/step/determine-level', [AiTestController::class, 'determineLevel'])->name('ai-test.step.determine-level');
Route::post('/ai-test/step/determine-level-gemini', [AiTestController::class, 'determineLevelGemini'])->name('ai-test.step.determine-level-gemini');
Route::post('/ai-test/step/set-level', [AiTestController::class, 'setLevel'])->name('ai-test.step.set-level');
Route::post('/ai-test/step/add-tag', [AiTestController::class, 'addTag'])->name('ai-test.step.add-tag');

use App\Http\Controllers\QuestionReviewController;
Route::get('/question-review', [QuestionReviewController::class, 'index'])->name('question-review.index');
Route::post('/question-review', [QuestionReviewController::class, 'store'])->name('question-review.store');
Route::get('/question-review/{question}', [QuestionReviewController::class, 'edit'])->name('question-review.edit');

use App\Http\Controllers\QuestionReviewResultController;
use App\Http\Controllers\VerbHintController;
Route::get('/question-review-results', [QuestionReviewResultController::class, 'index'])->name('question-review-results.index');

Route::post('/verb-hints', [VerbHintController::class, 'store'])->name('verb-hints.store');
Route::put('/verb-hints/{verbHint}', [VerbHintController::class, 'update'])->name('verb-hints.update');
Route::delete('/verb-hints/{verbHint}', [VerbHintController::class, 'destroy'])->name('verb-hints.destroy');

Route::post('/question-hint', [QuestionHelpController::class, 'hint'])->name('question.hint');

