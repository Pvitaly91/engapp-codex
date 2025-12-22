<?php

use App\Http\Controllers\AiTestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatGPTExplanationController;
use App\Http\Controllers\DynamicPageController;
use App\Http\Controllers\GrammarTestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarkerTheoryTagController;
use App\Http\Controllers\PageController;
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
use App\Http\Controllers\SiteTreeController;
use App\Http\Controllers\TestJsV2Controller;
use App\Http\Controllers\TestTagController;
use App\Http\Controllers\TheoryController;
use App\Http\Controllers\TrainController;
use App\Http\Controllers\VerbHintController;
use App\Http\Controllers\WordSearchController;
use App\Http\Controllers\WordsTestController;
use App\Modules\GitDeployment\Http\Controllers\DeploymentController as GitDeploymentController;
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

// Words Test routes (public, no authentication required)
Route::prefix('words/test')->group(function () {
    Route::get('/', [WordsTestController::class, 'index'])->name('words.test');
    Route::get('/state', [WordsTestController::class, 'state'])->name('words.test.state');
    Route::post('/check', [WordsTestController::class, 'check'])->name('words.test.check');
    Route::post('/reset', [WordsTestController::class, 'reset'])->name('words.test.reset');

    Route::get('/medium', [WordsTestController::class, 'index'])->name('words.test.medium');
    Route::get('/medium/state', [WordsTestController::class, 'state'])->name('words.test.medium.state');
    Route::post('/medium/check', [WordsTestController::class, 'check'])->name('words.test.medium.check');
    Route::post('/medium/reset', [WordsTestController::class, 'reset'])->name('words.test.medium.reset');

    Route::get('/hard', [WordsTestController::class, 'index'])->name('words.test.hard');
    Route::get('/hard/state', [WordsTestController::class, 'state'])->name('words.test.hard.state');
    Route::post('/hard/check', [WordsTestController::class, 'check'])->name('words.test.hard.check');
    Route::post('/hard/reset', [WordsTestController::class, 'reset'])->name('words.test.hard.reset');
});

// Public pages routes (no authentication required)
Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
Route::get('/pages/{category:slug}', [PageController::class, 'category'])->name('pages.category');
Route::get('/pages/{category:slug}/{pageSlug}', [PageController::class, 'show'])->name('pages.show');

// Public Theory pages routes (no authentication required)
Route::get('/theory', [TheoryController::class, 'index'])->name('theory.index');
Route::get('/theory/{category:slug}', [TheoryController::class, 'category'])->name('theory.category');
Route::get('/theory/{category:slug}/{pageSlug}', [TheoryController::class, 'show'])->name('theory.show');

// Define a pattern that excludes reserved route prefixes for dynamic page type routes
$reservedPrefixes = '^(?!pages|login|logout|admin|test|tests|catalog-tests|catalog|words|search|grammar-test|ai-test|question-review|question-review-results|verb-hints|questions|question-answers|question-variants|question-hints|chatgpt-explanations|question-hint|question-explain|seed-runs|translate|train|test-tags|theory)$';

Route::middleware('auth.admin')->group(function () use ($reservedPrefixes) {

    // Dynamic page type routes (authentication required)
    // These routes handle any other page type dynamically based on pages.type in DB
    Route::get('/{pageType}', [DynamicPageController::class, 'indexForType'])
        ->where('pageType', $reservedPrefixes)
        ->name('dynamic-pages.index');
    Route::get('/{pageType}/{category}', [DynamicPageController::class, 'categoryForType'])
        ->where('pageType', $reservedPrefixes)
        ->name('dynamic-pages.category');
    Route::get('/{pageType}/{category}/{pageSlug}', [DynamicPageController::class, 'showForType'])
        ->where('pageType', $reservedPrefixes)
        ->name('dynamic-pages.show');

    Route::get('/tests/cards', [GrammarTestController::class, 'catalog'])->name('saved-tests.cards');
    Route::get('/catalog-tests/cards', [GrammarTestController::class, 'catalog'])->name('catalog-tests.cards');
    Route::get('/catalog/tests-cards', [GrammarTestController::class, 'catalogAggregated'])->name('catalog.tests-cards');

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
    Route::get('/test/{slug}/js/dialogue', [GrammarTestController::class, 'showSavedTestJsDialogue'])->name('saved-test.js.dialogue');

    // Test V2 - New UI version (public test pages without /js suffix)
    Route::get('/test/{slug}', [TestJsV2Controller::class, 'showSavedTestJsV2'])->name('test.show');
    Route::get('/test/{slug}/step', [TestJsV2Controller::class, 'showSavedTestJsStepV2'])->name('test.step');
    Route::get('/test/{slug}/step/input', [TestJsV2Controller::class, 'showSavedTestJsStepInputV2'])->name('test.step-input');
    Route::get('/test/{slug}/step/manual', [TestJsV2Controller::class, 'showSavedTestJsStepManualV2'])->name('test.step-manual');
    Route::get('/test/{slug}/step/select', [TestJsV2Controller::class, 'showSavedTestJsStepSelectV2'])->name('test.step-select');
    Route::get('/test/{slug}/select', [TestJsV2Controller::class, 'showSavedTestJsSelectV2'])->name('test.select');
    Route::get('/test/{slug}/input', [TestJsV2Controller::class, 'showSavedTestJsInputV2'])->name('test.input');
    Route::get('/test/{slug}/manual', [TestJsV2Controller::class, 'showSavedTestJsManualV2'])->name('test.manual');

    Route::prefix('admin')->group(function () {
        Route::get('/', [GitDeploymentController::class, 'index'])->name('admin.dashboard');

        Route::get('/site-tree', [SiteTreeController::class, 'index'])->name('site-tree.index');
        Route::get('/site-tree/variant/{variantSlug}', [SiteTreeController::class, 'index'])->name('site-tree.variant');
        Route::get('/site-tree/api', [SiteTreeController::class, 'getTree'])->name('site-tree.api');
        Route::post('/site-tree', [SiteTreeController::class, 'store'])->name('site-tree.store');
        Route::post('/site-tree/{item}/toggle', [SiteTreeController::class, 'toggle'])->name('site-tree.toggle');
        Route::put('/site-tree/{item}', [SiteTreeController::class, 'update'])->name('site-tree.update');
        Route::delete('/site-tree/{item}', [SiteTreeController::class, 'destroy'])->name('site-tree.destroy');
        Route::post('/site-tree/{item}/move', [SiteTreeController::class, 'move'])->name('site-tree.move');
        Route::post('/site-tree/reset', [SiteTreeController::class, 'reset'])->name('site-tree.reset');
        Route::post('/site-tree/sync-missing', [SiteTreeController::class, 'syncMissingFromBase'])->name('site-tree.sync-missing');
        Route::get('/site-tree/export', [SiteTreeController::class, 'exportTree'])->name('site-tree.export');
        Route::post('/site-tree/import', [SiteTreeController::class, 'importTree'])->name('site-tree.import');
        
        // Variant management
        Route::get('/site-tree-variants', [SiteTreeController::class, 'listVariants'])->name('site-tree.variants.list');
        Route::post('/site-tree-variants', [SiteTreeController::class, 'storeVariant'])->name('site-tree.variants.store');
        Route::put('/site-tree-variants/{variant}', [SiteTreeController::class, 'updateVariant'])->name('site-tree.variants.update');
        Route::delete('/site-tree-variants/{variant}', [SiteTreeController::class, 'destroyVariant'])->name('site-tree.variants.destroy');

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
            Route::post('/export', [TestTagController::class, 'exportToJson'])->name('export');
            Route::get('/export/view', [TestTagController::class, 'viewExportedJson'])->name('export.view');
            Route::get('/export/download', [TestTagController::class, 'downloadExportedJson'])->name('export.download');
            Route::delete('/empty', [TestTagController::class, 'destroyEmptyTags'])->name('destroy-empty');

            Route::prefix('aggregations')->name('aggregations.')->group(function () {
                Route::get('/', [TestTagController::class, 'aggregations'])->name('index');
                Route::get('/auto', [TestTagController::class, 'autoAggregationsPage'])->name('auto-page');
                Route::post('/', [TestTagController::class, 'storeAggregation'])->name('store');
                Route::get('/generate-prompt', [TestTagController::class, 'generateAggregationPrompt'])->name('generate-prompt');
                Route::post('/auto', [TestTagController::class, 'autoAggregations'])->name('auto');
                Route::post('/auto-chatgpt', [TestTagController::class, 'autoAggregationsChatGPT'])->name('auto-chatgpt');
                Route::post('/import', [TestTagController::class, 'importAggregations'])->name('import');
                Route::put('/{mainTag}', [TestTagController::class, 'updateAggregation'])
                    ->where('mainTag', '.*')
                    ->name('update');
                Route::delete('/{mainTag}', [TestTagController::class, 'destroyAggregation'])
                    ->where('mainTag', '.*')
                    ->name('destroy');
                Route::put('/category/{category}', [TestTagController::class, 'updateAggregationCategory'])->name('update-category');
                Route::delete('/category/{category}', [TestTagController::class, 'destroyAggregationCategory'])->name('destroy-category');
            });

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
        Route::get('/grammar-test/search-questions', [GrammarTestController::class, 'searchQuestions'])->name('grammar-test.search-questions');
        Route::post('/grammar-test/render-questions', [GrammarTestController::class, 'renderQuestions'])->name('grammar-test.render-questions');
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
        Route::get('/tests/{slug}/edit', [GrammarTestController::class, 'edit'])->name('saved-tests.edit');
        Route::put('/tests/{slug}', [GrammarTestController::class, 'update'])->name('saved-tests.update');
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
        Route::post('/question-marker-theory', [QuestionHelpController::class, 'markerTheory'])->name('question.marker-theory');

        // Marker Theory Tags API
        Route::get('/api/v2/questions/{questionUuid}/markers/{marker}/available-theory-tags', [MarkerTheoryTagController::class, 'availableTheoryTags'])
            ->name('api.v2.markers.available-theory-tags');
        Route::post('/api/v2/questions/{questionUuid}/markers/{marker}/add-tags-from-theory-page', [MarkerTheoryTagController::class, 'addTagsFromTheoryPage'])
            ->name('api.v2.markers.add-tags-from-theory-page');

        Route::get('/seed-runs', [SeedRunController::class, 'index'])->name('seed-runs.index');
        Route::get('/seed-runs/preview', [SeedRunController::class, 'preview'])->name('seed-runs.preview');
        Route::get('/seed-runs/file', [SeedRunController::class, 'showSeederFile'])->name('seed-runs.file.show');
        Route::put('/seed-runs/file', [SeedRunController::class, 'updateSeederFile'])->name('seed-runs.file.update');
        Route::post('/seed-runs/file', [SeedRunController::class, 'storeSeederFile'])->name('seed-runs.file.store');
        Route::get('/seed-runs/folders', [SeedRunController::class, 'getSeederFolders'])->name('seed-runs.folders.list');
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
        Route::post('/seed-runs/{seedRun}/refresh', [SeedRunController::class, 'refresh'])
            ->name('seed-runs.refresh');
        Route::delete('/seed-runs/questions/{question}', [SeedRunController::class, 'destroyQuestion'])
            ->name('seed-runs.questions.destroy');
    });
});
