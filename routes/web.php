<?php

use App\Http\Controllers\AiTestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatGPTExplanationController;
use App\Http\Controllers\GrammarTestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MigrationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PageManageController;
use App\Http\Controllers\QuestionAnswerController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionHelpController;
use App\Http\Controllers\QuestionHintController;
use App\Http\Controllers\QuestionOptionController;
use App\Http\Controllers\QuestionReviewController;
use App\Http\Controllers\QuestionReviewResultController;
use App\Http\Controllers\QuestionVariantController;
use App\Http\Controllers\SeedRunController;
use App\Http\Controllers\SentenceTranslationTestController;
use App\Http\Controllers\SiteSearchController;
use App\Http\Controllers\TrainController;
use App\Http\Controllers\VerbHintController;
use App\Http\Controllers\WordSearchController;
use App\Modules\GitDeployment\Http\Controllers\DeploymentController as GitDeploymentController;
use App\Modules\GitDeployment\Http\Controllers\NativeDeploymentController;
use App\Http\Controllers\WordsTestController;
use App\Http\Controllers\TestTagController;
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

Route::middleware('auth.admin')->group(function () {
    Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
    Route::get('/pages/{category:slug}', [PageController::class, 'category'])->name('pages.category');
    Route::get('/pages/{category:slug}/{pageSlug}', [PageController::class, 'show'])->name('pages.show');

    Route::get('/tests/cards', [GrammarTestController::class, 'catalog'])->name('saved-tests.cards');
    Route::get('/catalog-tests/cards', [GrammarTestController::class, 'catalog'])->name('catalog-tests.cards');

    Route::post('/test/{slug}/js/state', [GrammarTestController::class, 'storeSavedTestJsState'])->name('saved-test.js.state');
    Route::get('/test/{slug}/js/questions', [GrammarTestController::class, 'fetchSavedTestJsQuestions'])->name('saved-test.js.questions');
    Route::get('/test/{slug}/js', [GrammarTestController::class, 'showSavedTestJs'])->name('saved-test.js');
    Route::get('/test/{slug}/js/step', [GrammarTestController::class, 'showSavedTestJsStep'])->name('saved-test.js.step');
    Route::get('/test/{slug}/js/manual', [GrammarTestController::class, 'showSavedTestJsManual'])->name('saved-test.js.manual');
    Route::get('/test/{slug}/js/step/manual', [GrammarTestController::class, 'showSavedTestJsStepManual'])->name('saved-test.js.step-manual');
    Route::get('/test/{slug}/js/step/input', [GrammarTestController::class, 'showSavedTestJsStepInput'])->name('saved-test.js.step-input');
    Route::get('/test/{slug}/js/input', [GrammarTestController::class, 'showSavedTestJsInput'])->name('saved-test.js.input');
    Route::get('/test/{slug}/js/step/select', [GrammarTestController::class, 'showSavedTestJsStepSelect'])->name('saved-test.js.step-select');
    Route::get('/test/{slug}/js/select', [GrammarTestController::class, 'showSavedTestJsSelect'])->name('saved-test.js.select');
    Route::get('/test/{slug}/js/drag-drop', [GrammarTestController::class, 'showSavedTestJsDragDrop'])->name('saved-test.js.drag-drop');
    Route::get('/test/{slug}/js/match', [GrammarTestController::class, 'showSavedTestJsMatch'])->name('saved-test.js.match');

    Route::prefix('admin')->group(function () {
        Route::get('/', [GitDeploymentController::class, 'index'])->name('admin.dashboard');

        Route::get('set-locale', function (\Illuminate\Http\Request $request) {
            $lang = $request->input('lang', 'en');
            if (! in_array($lang, ['en', 'uk', 'pl'])) {
                $lang = 'en';
            }
            session(['locale' => $lang]);
            app()->setLocale($lang);

            return redirect()->back();
        })->name('setlocale');

        Route::get('/train/{topic?}', [TrainController::class, 'index'])->name('train');

        Route::get('/migrations', [MigrationController::class, 'index'])->name('migrations.index');
        Route::post('/migrations/run', [MigrationController::class, 'run'])->name('migrations.run');
        Route::post('/migrations/rollback', [MigrationController::class, 'rollback'])->name('migrations.rollback');

        Route::prefix('pages/manage')->name('pages.manage.')->group(function () {
            Route::get('/', [PageManageController::class, 'index'])->name('index');
            Route::get('/create', [PageManageController::class, 'create'])->name('create');
            Route::post('/', [PageManageController::class, 'store'])->name('store');
            Route::get('/{page}/edit', [PageManageController::class, 'edit'])
                ->whereNumber('page')
                ->name('edit');
            Route::put('/{page}', [PageManageController::class, 'update'])
                ->whereNumber('page')
                ->name('update');
            Route::delete('/{page}', [PageManageController::class, 'destroy'])
                ->whereNumber('page')
                ->name('destroy');
            Route::post('/categories', [PageManageController::class, 'storeCategory'])->name('categories.store');
            Route::put('/categories/{category}', [PageManageController::class, 'updateCategory'])
                ->whereNumber('category')
                ->name('categories.update');
            Route::delete('/categories/{category}', [PageManageController::class, 'destroyCategory'])
                ->whereNumber('category')
                ->name('categories.destroy');
            Route::delete('/categories-empty', [PageManageController::class, 'destroyEmptyCategories'])->name('categories.destroy-empty');
            Route::get('/{page}/blocks/create', [PageManageController::class, 'createBlock'])
                ->whereNumber('page')
                ->name('blocks.create');
            Route::post('/{page}/blocks', [PageManageController::class, 'storeBlock'])
                ->whereNumber('page')
                ->name('blocks.store');
            Route::get('/{page}/blocks/{block}/edit', [PageManageController::class, 'editBlock'])
                ->whereNumber('page')
                ->whereNumber('block')
                ->name('blocks.edit');
            Route::put('/{page}/blocks/{block}', [PageManageController::class, 'updateBlock'])
                ->whereNumber('page')
                ->whereNumber('block')
                ->name('blocks.update');
            Route::delete('/{page}/blocks/{block}', [PageManageController::class, 'destroyBlock'])
                ->whereNumber('page')
                ->whereNumber('block')
                ->name('blocks.destroy');
        });

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

        Route::prefix('test-tags')->name('test-tags.')->group(function () {
            Route::get('/', [TestTagController::class, 'index'])->name('index');
            Route::get('/create', [TestTagController::class, 'create'])->name('create');
            Route::post('/', [TestTagController::class, 'store'])->name('store');
            Route::get('/categories/{category}/edit', [TestTagController::class, 'editCategory'])
                ->where('category', '.*')
                ->name('categories.edit');
            Route::put('/categories/{category}', [TestTagController::class, 'updateCategory'])
                ->where('category', '.*')
                ->name('categories.update');
            Route::delete('/categories/{category}', [TestTagController::class, 'destroyCategory'])
                ->where('category', '.*')
                ->name('categories.destroy');
            Route::get('/{tag}/questions', [TestTagController::class, 'questions'])->name('questions');
            Route::get('/{tag}/questions/{question}/answers', [TestTagController::class, 'questionAnswers'])
                ->name('questions.answers');
            Route::get('/{tag}/questions/{question}/tags', [TestTagController::class, 'questionTags'])
                ->name('questions.tags');
            Route::get('/{tag}/edit', [TestTagController::class, 'edit'])->name('edit');
            Route::put('/{tag}', [TestTagController::class, 'update'])->name('update');
            Route::delete('/{tag}', [TestTagController::class, 'destroy'])->name('destroy');
        });

        Route::get('/grammar-test', [GrammarTestController::class, 'index'])->name('grammar-test');
        Route::post('/grammar-test', [GrammarTestController::class, 'generate'])->name('grammar-test.generate');
        Route::get('/grammar-test/v2', fn () => redirect()->route('grammar-test'))->name('grammar-test.v2');
        Route::post('/grammar-test/v2', [GrammarTestController::class, 'generate'])->name('grammar-test-v2.generate');
        Route::post('/grammar-test-check', [GrammarTestController::class, 'check'])->name('grammar-test.check');
        Route::get('/grammar-test-autocomplete', [GrammarTestController::class, 'autocomplete'])->name('grammar-test.autocomplete');
        Route::post('/grammar-test-check-answer', [GrammarTestController::class, 'checkOneAnswer'])->name('grammar-test.checkOne');

        Route::post('/grammar-test-save', [GrammarTestController::class, 'save'])->name('grammar-test.save');
        Route::post('/grammar-test-save-v2', [GrammarTestController::class, 'saveV2'])->name('grammar-test.save-v2');
        Route::post('/grammar-test-v2-save', [GrammarTestController::class, 'saveV2'])->name('grammar-test-v2.save');
        Route::get('/test/{slug}/tech', [GrammarTestController::class, 'showSavedTestTech'])->name('saved-test.tech');
        Route::post('/test/{slug}/questions', [GrammarTestController::class, 'storeSavedTestQuestion'])->name('saved-test.questions.store');
        Route::delete('/test/{slug}/questions', [GrammarTestController::class, 'deleteAllQuestions'])->name('saved-test.questions.destroy-all');
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
        Route::get('/tests', [GrammarTestController::class, 'list'])->name('saved-tests.list');
        Route::delete('/tests/{slug}', [GrammarTestController::class, 'destroy'])->name('saved-tests.destroy');

        Route::get('/words', [WordSearchController::class, 'search'])->name('words.search');

        Route::get('/search', SiteSearchController::class)->name('site.search');

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
        Route::delete('/ai-test/step/remove-tag', [AiTestController::class, 'removeTag'])->name('ai-test.step.remove-tag');

        Route::get('/question-review', [QuestionReviewController::class, 'index'])->name('question-review.index');
        Route::post('/question-review', [QuestionReviewController::class, 'store'])->name('question-review.store');
        Route::get('/question-review/{question}', [QuestionReviewController::class, 'edit'])->name('question-review.edit');

        Route::get('/question-review-results', [QuestionReviewResultController::class, 'index'])->name('question-review-results.index');

        Route::post('/verb-hints', [VerbHintController::class, 'store'])->name('verb-hints.store');
        Route::put('/verb-hints/{verbHint}', [VerbHintController::class, 'update'])->name('verb-hints.update');
        Route::delete('/verb-hints/{verbHint}', [VerbHintController::class, 'destroy'])->name('verb-hints.destroy');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::post('/questions/{question}/export', [QuestionController::class, 'export'])->name('questions.export');
        Route::post('/questions/export/by-uuid', [QuestionController::class, 'exportByUuid'])->name('questions.export-by-uuid');
        Route::post('/questions/restore/from-dumps', [QuestionController::class, 'restoreFromDumps'])->name('questions.restore-from-dumps');
        Route::post('/questions/restore/by-uuid', [QuestionController::class, 'restoreByUuid'])->name('questions.restore-by-uuid');
        Route::post('/question-answers', [QuestionAnswerController::class, 'store'])->name('question-answers.store');
        Route::put('/question-answers/{questionAnswer}', [QuestionAnswerController::class, 'update'])->name('question-answers.update');
        Route::delete('/question-answers/{questionAnswer}', [QuestionAnswerController::class, 'destroy'])->name('question-answers.destroy');
        Route::post('/questions/{question}/options', [QuestionOptionController::class, 'store'])->name('questions.options.store');
        Route::put('/questions/{question}/options/{option}', [QuestionOptionController::class, 'update'])->name('questions.options.update');
        Route::delete('/questions/{question}/options/{option}', [QuestionOptionController::class, 'destroy'])->name('questions.options.destroy');
        Route::put('/question-variants/{questionVariant}', [QuestionVariantController::class, 'update'])->name('question-variants.update');
        Route::delete('/question-variants/{questionVariant}', [QuestionVariantController::class, 'destroy'])->name('question-variants.destroy');
        Route::post('/question-hints', [QuestionHintController::class, 'store'])->name('question-hints.store');
        Route::put('/question-hints/{questionHint}', [QuestionHintController::class, 'update'])->name('question-hints.update');
        Route::delete('/question-hints/{questionHint}', [QuestionHintController::class, 'destroy'])->name('question-hints.destroy');
        Route::post('/chatgpt-explanations', [ChatGPTExplanationController::class, 'store'])->name('chatgpt-explanations.store');
        Route::delete('/chatgpt-explanations/{chatGPTExplanation}', [ChatGPTExplanationController::class, 'destroy'])->name('chatgpt-explanations.destroy');

        Route::post('/question-hint', [QuestionHelpController::class, 'hint'])->name('question.hint');
        Route::post('/question-explain', [QuestionHelpController::class, 'explain'])->name('question.explain');

        Route::get('/seed-runs', [SeedRunController::class, 'index'])->name('seed-runs.index');
        Route::get('/seed-runs/preview', [SeedRunController::class, 'preview'])->name('seed-runs.preview');
        Route::get('/seed-runs/file', [SeedRunController::class, 'showSeederFile'])->name('seed-runs.file.show');
        Route::put('/seed-runs/file', [SeedRunController::class, 'updateSeederFile'])->name('seed-runs.file.update');
        Route::post('/seed-runs/run', [SeedRunController::class, 'run'])->name('seed-runs.run');
        Route::delete('/seed-runs/delete-file', [SeedRunController::class, 'destroySeederFile'])
            ->name('seed-runs.destroy-seeder-file');
        Route::delete('/seed-runs/delete-files/bulk', [SeedRunController::class, 'destroySeederFiles'])
            ->name('seed-runs.destroy-seeder-files');
        Route::post('/seed-runs/mark-executed', [SeedRunController::class, 'markAsExecuted'])->name('seed-runs.mark-executed');
        Route::post('/seed-runs/run-missing', [SeedRunController::class, 'runMissing'])->name('seed-runs.run-missing');
        Route::get('/seed-runs/folders/children', [SeedRunController::class, 'loadFolderChildren'])->name('seed-runs.folders.children');
        Route::get('/seed-runs/{seedRun}/categories', [SeedRunController::class, 'loadSeederCategories'])->name('seed-runs.seeders.categories');
        Route::get('/seed-runs/{seedRun}/categories/{categoryKey}/sources/{sourceKey}', [SeedRunController::class, 'loadSourceQuestions'])->name('seed-runs.seeders.sources.questions');
        Route::get('/seed-runs/{seedRun}/questions/{question}/answers', [SeedRunController::class, 'loadQuestionAnswers'])->name('seed-runs.questions.answers');
        Route::get('/seed-runs/{seedRun}/questions/{question}/tags', [SeedRunController::class, 'loadQuestionTags'])->name('seed-runs.questions.tags');
        Route::delete('/seed-runs/folders/delete-records', [SeedRunController::class, 'destroyFolder'])
            ->name('seed-runs.folders.destroy');
        Route::delete('/seed-runs/folders/delete-with-questions', [SeedRunController::class, 'destroyFolderWithQuestions'])
            ->name('seed-runs.folders.destroy-with-questions');
        Route::delete('/seed-runs/{seedRun}', [SeedRunController::class, 'destroy'])->name('seed-runs.destroy');
        Route::delete('/seed-runs/{seedRun}/with-questions', [SeedRunController::class, 'destroyWithQuestions'])
            ->name('seed-runs.destroy-with-questions');
        Route::delete('/seed-runs/questions/{question}', [SeedRunController::class, 'destroyQuestion'])
            ->name('seed-runs.questions.destroy');
    });
});
