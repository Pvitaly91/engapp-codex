<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Support\Database\QuestionUuidResolver;
use Database\Seeders\V3\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotArticlesAAnTheLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotHaveGotHasGotLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleIrregularVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleRegularVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleToBeLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentContinuousLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentSimpleVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotThereIsThereAreLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotToBeLessonSeeder;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotV3SeedersTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        $this->rebuildComposeTestSchema();
    }

    public function test_v3_polyglot_seeders_exist_and_work(): void
    {
        $this->seed(PolyglotToBeLessonSeeder::class);
        $this->seed(PolyglotThereIsThereAreLessonSeeder::class);
        $this->seed(PolyglotHaveGotHasGotLessonSeeder::class);
        $this->seed(PolyglotPresentSimpleVerbsLessonSeeder::class);
        $this->seed(PolyglotCanCannotLessonSeeder::class);
        $this->seed(PolyglotPresentContinuousLessonSeeder::class);
        $this->seed(PolyglotPastSimpleToBeLessonSeeder::class);
        $this->seed(PolyglotPastSimpleRegularVerbsLessonSeeder::class);
        $this->seed(PolyglotPastSimpleIrregularVerbsLessonSeeder::class);
        $this->seed(PolyglotFutureSimpleWillLessonSeeder::class);
        $this->seed(PolyglotArticlesAAnTheLessonSeeder::class);

        $lessonOne = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-to-be-a1')
            ->firstOrFail();
        $lessonTwo = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-there-is-there-are-a1')
            ->firstOrFail();
        $lessonThree = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-have-got-has-got-a1')
            ->firstOrFail();
        $lessonFour = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-present-simple-verbs-a1')
            ->firstOrFail();
        $lessonFive = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-can-cannot-a1')
            ->firstOrFail();
        $lessonSix = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-present-continuous-a1')
            ->firstOrFail();
        $lessonSeven = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-past-simple-to-be-a1')
            ->firstOrFail();
        $lessonEight = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-past-simple-regular-verbs-a1')
            ->firstOrFail();
        $lessonNine = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-past-simple-irregular-verbs-a1')
            ->firstOrFail();
        $lessonTen = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-future-simple-will-a1')
            ->firstOrFail();
        $lessonEleven = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-articles-a-an-the-a1')
            ->firstOrFail();

        $questions = Question::query()
            ->whereIn('uuid', array_merge(
                $lessonOne->questionLinks->pluck('question_uuid')->all(),
                $lessonTwo->questionLinks->pluck('question_uuid')->all(),
                $lessonThree->questionLinks->pluck('question_uuid')->all(),
                $lessonFour->questionLinks->pluck('question_uuid')->all(),
                $lessonFive->questionLinks->pluck('question_uuid')->all(),
                $lessonSix->questionLinks->pluck('question_uuid')->all(),
                $lessonSeven->questionLinks->pluck('question_uuid')->all(),
                $lessonEight->questionLinks->pluck('question_uuid')->all(),
                $lessonNine->questionLinks->pluck('question_uuid')->all(),
                $lessonTen->questionLinks->pluck('question_uuid')->all(),
                $lessonEleven->questionLinks->pluck('question_uuid')->all()
            ))
            ->get();

        $this->assertSame('polyglot-to-be-a1', $lessonOne->slug);
        $this->assertSame('polyglot-there-is-there-are-a1', $lessonTwo->slug);
        $this->assertSame('polyglot-have-got-has-got-a1', $lessonThree->slug);
        $this->assertSame('polyglot-present-simple-verbs-a1', $lessonFour->slug);
        $this->assertSame('polyglot-can-cannot-a1', $lessonFive->slug);
        $this->assertSame('polyglot-present-continuous-a1', $lessonSix->slug);
        $this->assertSame('polyglot-past-simple-to-be-a1', $lessonSeven->slug);
        $this->assertSame('polyglot-past-simple-regular-verbs-a1', $lessonEight->slug);
        $this->assertSame('polyglot-past-simple-irregular-verbs-a1', $lessonNine->slug);
        $this->assertSame('polyglot-future-simple-will-a1', $lessonTen->slug);
        $this->assertSame('polyglot-articles-a-an-the-a1', $lessonEleven->slug);
        $this->assertCount(24, $lessonOne->questionLinks);
        $this->assertCount(24, $lessonTwo->questionLinks);
        $this->assertCount(24, $lessonThree->questionLinks);
        $this->assertCount(24, $lessonFour->questionLinks);
        $this->assertCount(24, $lessonFive->questionLinks);
        $this->assertCount(24, $lessonSix->questionLinks);
        $this->assertCount(24, $lessonSeven->questionLinks);
        $this->assertCount(24, $lessonEight->questionLinks);
        $this->assertCount(24, $lessonNine->questionLinks);
        $this->assertCount(24, $lessonTen->questionLinks);
        $this->assertCount(24, $lessonEleven->questionLinks);
        $this->assertTrue($questions->every(
            fn (Question $question) => (string) $question->type === Question::TYPE_COMPOSE_TOKENS
        ));
    }

    public function test_generator_driven_lesson_three_package_exists_in_canonical_v3_structure(): void
    {
        $loaderPath = database_path('seeders/V3/Polyglot/PolyglotHaveGotHasGotLessonSeeder.php');
        $packagePath = database_path('seeders/V3/Polyglot/PolyglotHaveGotHasGotLessonSeeder');
        $definitionPath = $packagePath . '/definition.json';
        $realSeederPath = $packagePath . '/PolyglotHaveGotHasGotLessonSeeder.php';
        $ukPath = $packagePath . '/localizations/uk.json';
        $enPath = $packagePath . '/localizations/en.json';
        $plPath = $packagePath . '/localizations/pl.json';

        $this->assertFileExists($loaderPath);
        $this->assertFileExists($definitionPath);
        $this->assertFileExists($realSeederPath);
        $this->assertFileExists($ukPath);
        $this->assertFileExists($enPath);
        $this->assertFileExists($plPath);
        $ukLocalization = json_decode(File::get($ukPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString(
            "require_once __DIR__ . '/PolyglotHaveGotHasGotLessonSeeder/PolyglotHaveGotHasGotLessonSeeder.php';",
            File::get($loaderPath)
        );
        $this->assertSame(
            'Database\\Seeders\\V3\\Localizations\\Uk\\Polyglot\\PolyglotHaveGotHasGotLessonLocalizationSeeder',
            $ukLocalization['seeder']['class'] ?? null
        );
    }

    public function test_generator_driven_lesson_four_package_exists_in_canonical_v3_structure(): void
    {
        $loaderPath = database_path('seeders/V3/Polyglot/PolyglotPresentSimpleVerbsLessonSeeder.php');
        $packagePath = database_path('seeders/V3/Polyglot/PolyglotPresentSimpleVerbsLessonSeeder');
        $definitionPath = $packagePath . '/definition.json';
        $realSeederPath = $packagePath . '/PolyglotPresentSimpleVerbsLessonSeeder.php';
        $ukPath = $packagePath . '/localizations/uk.json';
        $enPath = $packagePath . '/localizations/en.json';
        $plPath = $packagePath . '/localizations/pl.json';

        $this->assertFileExists($loaderPath);
        $this->assertFileExists($definitionPath);
        $this->assertFileExists($realSeederPath);
        $this->assertFileExists($ukPath);
        $this->assertFileExists($enPath);
        $this->assertFileExists($plPath);
        $ukLocalization = json_decode(File::get($ukPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString(
            "require_once __DIR__ . '/PolyglotPresentSimpleVerbsLessonSeeder/PolyglotPresentSimpleVerbsLessonSeeder.php';",
            File::get($loaderPath)
        );
        $this->assertSame(
            'Database\\Seeders\\V3\\Localizations\\Uk\\Polyglot\\PolyglotPresentSimpleVerbsLessonLocalizationSeeder',
            $ukLocalization['seeder']['class'] ?? null
        );
    }

    public function test_generator_driven_lesson_five_package_exists_in_canonical_v3_structure(): void
    {
        $loaderPath = database_path('seeders/V3/Polyglot/PolyglotCanCannotLessonSeeder.php');
        $packagePath = database_path('seeders/V3/Polyglot/PolyglotCanCannotLessonSeeder');
        $definitionPath = $packagePath . '/definition.json';
        $realSeederPath = $packagePath . '/PolyglotCanCannotLessonSeeder.php';
        $ukPath = $packagePath . '/localizations/uk.json';
        $enPath = $packagePath . '/localizations/en.json';
        $plPath = $packagePath . '/localizations/pl.json';

        $this->assertFileExists($loaderPath);
        $this->assertFileExists($definitionPath);
        $this->assertFileExists($realSeederPath);
        $this->assertFileExists($ukPath);
        $this->assertFileExists($enPath);
        $this->assertFileExists($plPath);
        $ukLocalization = json_decode(File::get($ukPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString(
            "require_once __DIR__ . '/PolyglotCanCannotLessonSeeder/PolyglotCanCannotLessonSeeder.php';",
            File::get($loaderPath)
        );
        $this->assertSame(
            'Database\\Seeders\\V3\\Localizations\\Uk\\Polyglot\\PolyglotCanCannotLessonLocalizationSeeder',
            $ukLocalization['seeder']['class'] ?? null
        );
    }

    public function test_generator_driven_lesson_six_package_exists_in_canonical_v3_structure(): void
    {
        $loaderPath = database_path('seeders/V3/Polyglot/PolyglotPresentContinuousLessonSeeder.php');
        $packagePath = database_path('seeders/V3/Polyglot/PolyglotPresentContinuousLessonSeeder');
        $definitionPath = $packagePath . '/definition.json';
        $realSeederPath = $packagePath . '/PolyglotPresentContinuousLessonSeeder.php';
        $ukPath = $packagePath . '/localizations/uk.json';
        $enPath = $packagePath . '/localizations/en.json';
        $plPath = $packagePath . '/localizations/pl.json';
        $promptPath = storage_path('app/polyglot-prompts/polyglot-present-continuous-a1.txt');

        $this->assertFileExists($loaderPath);
        $this->assertFileExists($definitionPath);
        $this->assertFileExists($realSeederPath);
        $this->assertFileExists($ukPath);
        $this->assertFileExists($enPath);
        $this->assertFileExists($plPath);
        $this->assertFileExists($promptPath);
        $ukLocalization = json_decode(File::get($ukPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString(
            "require_once __DIR__ . '/PolyglotPresentContinuousLessonSeeder/PolyglotPresentContinuousLessonSeeder.php';",
            File::get($loaderPath)
        );
        $this->assertSame(
            'Database\\Seeders\\V3\\Localizations\\Uk\\Polyglot\\PolyglotPresentContinuousLessonLocalizationSeeder',
            $ukLocalization['seeder']['class'] ?? null
        );
        $this->assertStringContainsString('present-continuous-forms', File::get($promptPath));
    }

    public function test_generator_driven_lesson_seven_package_exists_in_canonical_v3_structure(): void
    {
        $loaderPath = database_path('seeders/V3/Polyglot/PolyglotPastSimpleToBeLessonSeeder.php');
        $packagePath = database_path('seeders/V3/Polyglot/PolyglotPastSimpleToBeLessonSeeder');
        $definitionPath = $packagePath . '/definition.json';
        $realSeederPath = $packagePath . '/PolyglotPastSimpleToBeLessonSeeder.php';
        $ukPath = $packagePath . '/localizations/uk.json';
        $enPath = $packagePath . '/localizations/en.json';
        $plPath = $packagePath . '/localizations/pl.json';
        $promptPath = storage_path('app/polyglot-prompts/polyglot-past-simple-to-be-a1.txt');

        $this->assertFileExists($loaderPath);
        $this->assertFileExists($definitionPath);
        $this->assertFileExists($realSeederPath);
        $this->assertFileExists($ukPath);
        $this->assertFileExists($enPath);
        $this->assertFileExists($plPath);
        $this->assertFileExists($promptPath);
        $ukLocalization = json_decode(File::get($ukPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString(
            "require_once __DIR__ . '/PolyglotPastSimpleToBeLessonSeeder/PolyglotPastSimpleToBeLessonSeeder.php';",
            File::get($loaderPath)
        );
        $this->assertSame(
            'Database\\Seeders\\V3\\Localizations\\Uk\\Polyglot\\PolyglotPastSimpleToBeLessonLocalizationSeeder',
            $ukLocalization['seeder']['class'] ?? null
        );
        $this->assertStringContainsString('verb-to-be-past', File::get($promptPath));
    }

    public function test_generator_driven_lesson_eight_package_exists_in_canonical_v3_structure(): void
    {
        $loaderPath = database_path('seeders/V3/Polyglot/PolyglotPastSimpleRegularVerbsLessonSeeder.php');
        $packagePath = database_path('seeders/V3/Polyglot/PolyglotPastSimpleRegularVerbsLessonSeeder');
        $definitionPath = $packagePath . '/definition.json';
        $realSeederPath = $packagePath . '/PolyglotPastSimpleRegularVerbsLessonSeeder.php';
        $ukPath = $packagePath . '/localizations/uk.json';
        $enPath = $packagePath . '/localizations/en.json';
        $plPath = $packagePath . '/localizations/pl.json';
        $promptPath = storage_path('app/polyglot-prompts/polyglot-past-simple-regular-verbs-a1.txt');

        $this->assertFileExists($loaderPath);
        $this->assertFileExists($definitionPath);
        $this->assertFileExists($realSeederPath);
        $this->assertFileExists($ukPath);
        $this->assertFileExists($enPath);
        $this->assertFileExists($plPath);
        $this->assertFileExists($promptPath);
        $ukLocalization = json_decode(File::get($ukPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString(
            "require_once __DIR__ . '/PolyglotPastSimpleRegularVerbsLessonSeeder/PolyglotPastSimpleRegularVerbsLessonSeeder.php';",
            File::get($loaderPath)
        );
        $this->assertSame(
            'Database\\Seeders\\V3\\Localizations\\Uk\\Polyglot\\PolyglotPastSimpleRegularVerbsLessonLocalizationSeeder',
            $ukLocalization['seeder']['class'] ?? null
        );
        $this->assertStringContainsString('past-simple-forms', File::get($promptPath));
    }

    public function test_generator_driven_lesson_nine_package_exists_in_canonical_v3_structure(): void
    {
        $loaderPath = database_path('seeders/V3/Polyglot/PolyglotPastSimpleIrregularVerbsLessonSeeder.php');
        $packagePath = database_path('seeders/V3/Polyglot/PolyglotPastSimpleIrregularVerbsLessonSeeder');
        $definitionPath = $packagePath . '/definition.json';
        $realSeederPath = $packagePath . '/PolyglotPastSimpleIrregularVerbsLessonSeeder.php';
        $ukPath = $packagePath . '/localizations/uk.json';
        $enPath = $packagePath . '/localizations/en.json';
        $plPath = $packagePath . '/localizations/pl.json';
        $promptPath = storage_path('app/polyglot-prompts/polyglot-past-simple-irregular-verbs-a1.txt');

        $this->assertFileExists($loaderPath);
        $this->assertFileExists($definitionPath);
        $this->assertFileExists($realSeederPath);
        $this->assertFileExists($ukPath);
        $this->assertFileExists($enPath);
        $this->assertFileExists($plPath);
        $this->assertFileExists($promptPath);
        $ukLocalization = json_decode(File::get($ukPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString(
            "require_once __DIR__ . '/PolyglotPastSimpleIrregularVerbsLessonSeeder/PolyglotPastSimpleIrregularVerbsLessonSeeder.php';",
            File::get($loaderPath)
        );
        $this->assertSame(
            'Database\\Seeders\\V3\\Localizations\\Uk\\Polyglot\\PolyglotPastSimpleIrregularVerbsLessonLocalizationSeeder',
            $ukLocalization['seeder']['class'] ?? null
        );
        $this->assertStringContainsString('past-simple-forms', File::get($promptPath));
    }

    public function test_generator_driven_lesson_ten_package_exists_in_canonical_v3_structure(): void
    {
        $loaderPath = database_path('seeders/V3/Polyglot/PolyglotFutureSimpleWillLessonSeeder.php');
        $packagePath = database_path('seeders/V3/Polyglot/PolyglotFutureSimpleWillLessonSeeder');
        $definitionPath = $packagePath . '/definition.json';
        $realSeederPath = $packagePath . '/PolyglotFutureSimpleWillLessonSeeder.php';
        $ukPath = $packagePath . '/localizations/uk.json';
        $enPath = $packagePath . '/localizations/en.json';
        $plPath = $packagePath . '/localizations/pl.json';
        $promptPath = storage_path('app/polyglot-prompts/polyglot-future-simple-will-a1.txt');

        $this->assertFileExists($loaderPath);
        $this->assertFileExists($definitionPath);
        $this->assertFileExists($realSeederPath);
        $this->assertFileExists($ukPath);
        $this->assertFileExists($enPath);
        $this->assertFileExists($plPath);
        $this->assertFileExists($promptPath);
        $ukLocalization = json_decode(File::get($ukPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString(
            "require_once __DIR__ . '/PolyglotFutureSimpleWillLessonSeeder/PolyglotFutureSimpleWillLessonSeeder.php';",
            File::get($loaderPath)
        );
        $this->assertSame(
            'Database\\Seeders\\V3\\Localizations\\Uk\\Polyglot\\PolyglotFutureSimpleWillLessonLocalizationSeeder',
            $ukLocalization['seeder']['class'] ?? null
        );
        $this->assertStringContainsString('will-vs-be-going-to', File::get($promptPath));
    }

    public function test_generator_driven_lesson_eleven_package_exists_in_canonical_v3_structure(): void
    {
        $loaderPath = database_path('seeders/V3/Polyglot/PolyglotArticlesAAnTheLessonSeeder.php');
        $packagePath = database_path('seeders/V3/Polyglot/PolyglotArticlesAAnTheLessonSeeder');
        $definitionPath = $packagePath . '/definition.json';
        $realSeederPath = $packagePath . '/PolyglotArticlesAAnTheLessonSeeder.php';
        $ukPath = $packagePath . '/localizations/uk.json';
        $enPath = $packagePath . '/localizations/en.json';
        $plPath = $packagePath . '/localizations/pl.json';
        $promptPath = storage_path('app/polyglot-prompts/polyglot-articles-a-an-the-a1.txt');

        $this->assertFileExists($loaderPath);
        $this->assertFileExists($definitionPath);
        $this->assertFileExists($realSeederPath);
        $this->assertFileExists($ukPath);
        $this->assertFileExists($enPath);
        $this->assertFileExists($plPath);
        $this->assertFileExists($promptPath);
        $ukLocalization = json_decode(File::get($ukPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString(
            "require_once __DIR__ . '/PolyglotArticlesAAnTheLessonSeeder/PolyglotArticlesAAnTheLessonSeeder.php';",
            File::get($loaderPath)
        );
        $this->assertSame(
            'Database\\Seeders\\V3\\Localizations\\Uk\\Polyglot\\PolyglotArticlesAAnTheLessonLocalizationSeeder',
            $ukLocalization['seeder']['class'] ?? null
        );
        $this->assertStringContainsString('articles-common-mistakes', File::get($promptPath));
    }

    public function test_v3_definition_and_locale_json_are_used_by_seeder(): void
    {
        $definitionPath = database_path('seeders/V3/Polyglot/PolyglotToBeLessonSeeder/definition.json');
        $localizationPath = database_path('seeders/V3/Polyglot/PolyglotToBeLessonSeeder/localizations/uk.json');

        $definition = json_decode(File::get($definitionPath), true, 512, JSON_THROW_ON_ERROR);
        $localization = json_decode(File::get($localizationPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayNotHasKey('localizations', $definition['questions'][0]);
        $this->assertNotEmpty($localization['questions']);

        $this->seed(PolyglotToBeLessonSeeder::class);

        $test = SavedGrammarTest::query()
            ->where('slug', $definition['saved_test']['slug'])
            ->firstOrFail();
        $question = Question::query()
            ->where('uuid', $definition['questions'][0]['uuid'])
            ->with('hints')
            ->firstOrFail();
        $hint = $question->hints
            ->firstWhere('locale', 'uk');

        $this->assertSame($definition['saved_test']['name'], $test->name);
        $this->assertSame($definition['saved_test']['description'], $test->description);
        $this->assertNotNull($hint);
        $this->assertSame($localization['hint_provider'], $hint->provider);
        $this->assertSame($localization['questions'][0]['hints'][0], $hint->hint);
    }

    public function test_current_compose_routes_and_course_route_remain_valid_after_v3_migration(): void
    {
        $this->seed(PolyglotToBeLessonSeeder::class);
        $this->seed(PolyglotThereIsThereAreLessonSeeder::class);
        $this->seed(PolyglotHaveGotHasGotLessonSeeder::class);
        $this->seed(PolyglotPresentSimpleVerbsLessonSeeder::class);
        $this->seed(PolyglotCanCannotLessonSeeder::class);
        $this->seed(PolyglotPresentContinuousLessonSeeder::class);
        $this->seed(PolyglotPastSimpleToBeLessonSeeder::class);
        $this->seed(PolyglotPastSimpleRegularVerbsLessonSeeder::class);
        $this->seed(PolyglotPastSimpleIrregularVerbsLessonSeeder::class);
        $this->seed(PolyglotFutureSimpleWillLessonSeeder::class);
        $this->seed(PolyglotArticlesAAnTheLessonSeeder::class);

        $this->get('/test/polyglot-to-be-a1/step/compose')->assertOk();
        $this->get('/test/polyglot-there-is-there-are-a1/step/compose')->assertOk();
        $this->get('/test/polyglot-have-got-has-got-a1/step/compose')->assertOk();
        $this->get('/test/polyglot-present-simple-verbs-a1/step/compose')->assertOk();
        $this->get('/test/polyglot-can-cannot-a1/step/compose')->assertOk();
        $this->get('/test/polyglot-present-continuous-a1/step/compose')->assertOk();
        $this->get('/test/polyglot-past-simple-to-be-a1/step/compose')->assertOk();
        $this->get('/test/polyglot-past-simple-regular-verbs-a1/step/compose')->assertOk();
        $this->get('/test/polyglot-past-simple-irregular-verbs-a1/step/compose')->assertOk();
        $this->get('/test/polyglot-future-simple-will-a1/step/compose')->assertOk();
        $this->get('/test/polyglot-articles-a-an-the-a1/step/compose')->assertOk();
        $this->get('/courses/polyglot-english-a1')->assertOk();
    }

    public function test_theory_binding_metadata_exists_for_v3_polyglot_lessons(): void
    {
        $this->seed(PolyglotToBeLessonSeeder::class);
        $this->seed(PolyglotThereIsThereAreLessonSeeder::class);
        $this->seed(PolyglotHaveGotHasGotLessonSeeder::class);
        $this->seed(PolyglotPresentSimpleVerbsLessonSeeder::class);
        $this->seed(PolyglotCanCannotLessonSeeder::class);
        $this->seed(PolyglotPresentContinuousLessonSeeder::class);
        $this->seed(PolyglotPastSimpleToBeLessonSeeder::class);
        $this->seed(PolyglotPastSimpleRegularVerbsLessonSeeder::class);
        $this->seed(PolyglotPastSimpleIrregularVerbsLessonSeeder::class);
        $this->seed(PolyglotFutureSimpleWillLessonSeeder::class);
        $this->seed(PolyglotArticlesAAnTheLessonSeeder::class);

        $lessonOne = SavedGrammarTest::query()
            ->where('slug', 'polyglot-to-be-a1')
            ->firstOrFail();
        $lessonTwo = SavedGrammarTest::query()
            ->where('slug', 'polyglot-there-is-there-are-a1')
            ->firstOrFail();
        $lessonThree = SavedGrammarTest::query()
            ->where('slug', 'polyglot-have-got-has-got-a1')
            ->firstOrFail();
        $lessonFour = SavedGrammarTest::query()
            ->where('slug', 'polyglot-present-simple-verbs-a1')
            ->firstOrFail();
        $lessonFive = SavedGrammarTest::query()
            ->where('slug', 'polyglot-can-cannot-a1')
            ->firstOrFail();
        $lessonSix = SavedGrammarTest::query()
            ->where('slug', 'polyglot-present-continuous-a1')
            ->firstOrFail();
        $lessonSeven = SavedGrammarTest::query()
            ->where('slug', 'polyglot-past-simple-to-be-a1')
            ->firstOrFail();
        $lessonEight = SavedGrammarTest::query()
            ->where('slug', 'polyglot-past-simple-regular-verbs-a1')
            ->firstOrFail();
        $lessonNine = SavedGrammarTest::query()
            ->where('slug', 'polyglot-past-simple-irregular-verbs-a1')
            ->firstOrFail();
        $lessonTen = SavedGrammarTest::query()
            ->where('slug', 'polyglot-future-simple-will-a1')
            ->firstOrFail();
        $lessonEleven = SavedGrammarTest::query()
            ->where('slug', 'polyglot-articles-a-an-the-a1')
            ->firstOrFail();

        $lessonOneTheory = $lessonOne->filters['prompt_generator']['theory_page'] ?? [];
        $lessonTwoTheory = $lessonTwo->filters['prompt_generator']['theory_page'] ?? [];
        $lessonThreeTheory = $lessonThree->filters['prompt_generator']['theory_page'] ?? [];
        $lessonFourTheory = $lessonFour->filters['prompt_generator']['theory_page'] ?? [];
        $lessonFiveTheory = $lessonFive->filters['prompt_generator']['theory_page'] ?? [];
        $lessonSixTheory = $lessonSix->filters['prompt_generator']['theory_page'] ?? [];
        $lessonSevenTheory = $lessonSeven->filters['prompt_generator']['theory_page'] ?? [];
        $lessonEightTheory = $lessonEight->filters['prompt_generator']['theory_page'] ?? [];
        $lessonNineTheory = $lessonNine->filters['prompt_generator']['theory_page'] ?? [];
        $lessonTenTheory = $lessonTen->filters['prompt_generator']['theory_page'] ?? [];
        $lessonElevenTheory = $lessonEleven->filters['prompt_generator']['theory_page'] ?? [];

        $this->assertSame(
            'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder',
            $lessonOneTheory['page_seeder_class'] ?? null
        );
        $this->assertSame('verb-to-be-present', $lessonOneTheory['slug'] ?? null);
        $this->assertSame('basic-grammar/verb-to-be', $lessonOneTheory['category_slug_path'] ?? null);

        $this->assertSame(
            'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder',
            $lessonTwoTheory['page_seeder_class'] ?? null
        );
        $this->assertSame('there-is-there-are', $lessonTwoTheory['slug'] ?? null);
        $this->assertSame('basic-grammar/verb-to-be', $lessonTwoTheory['category_slug_path'] ?? null);

        $this->assertSame(
            'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarHaveGotHasGotTheorySeeder',
            $lessonThreeTheory['page_seeder_class'] ?? null
        );
        $this->assertSame('have-got-has-got', $lessonThreeTheory['slug'] ?? null);
        $this->assertSame('basic-grammar', $lessonThreeTheory['category_slug_path'] ?? null);
        $this->assertSame(
            'Database\\Seeders\\Page_V3\\Tenses\\PresentSimple\\PresentSimpleQuestionsTheorySeeder',
            $lessonFourTheory['page_seeder_class'] ?? null
        );
        $this->assertSame('present-simple-questions', $lessonFourTheory['slug'] ?? null);
        $this->assertSame('tenses/present-simple', $lessonFourTheory['category_slug_path'] ?? null);
        $this->assertSame(
            'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsCanCouldTheorySeeder',
            $lessonFiveTheory['page_seeder_class'] ?? null
        );
        $this->assertSame('can-could', $lessonFiveTheory['slug'] ?? null);
        $this->assertSame('modal-verbs', $lessonFiveTheory['category_slug_path'] ?? null);
        $this->assertSame(
            'Database\\Seeders\\Page_V3\\Tenses\\PresentContinuous\\PresentContinuousFormsTheorySeeder',
            $lessonSixTheory['page_seeder_class'] ?? null
        );
        $this->assertSame('present-continuous-forms', $lessonSixTheory['slug'] ?? null);
        $this->assertSame('tenses/present-continuous', $lessonSixTheory['category_slug_path'] ?? null);
        $this->assertSame(
            'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder',
            $lessonSevenTheory['page_seeder_class'] ?? null
        );
        $this->assertSame('verb-to-be-past', $lessonSevenTheory['slug'] ?? null);
        $this->assertSame('basic-grammar/verb-to-be', $lessonSevenTheory['category_slug_path'] ?? null);
        $this->assertSame(
            'Database\\Seeders\\Page_V3\\Tenses\\PastSimple\\PastSimpleFormsTheorySeeder',
            $lessonEightTheory['page_seeder_class'] ?? null
        );
        $this->assertSame('past-simple-forms', $lessonEightTheory['slug'] ?? null);
        $this->assertSame('tenses/past-simple', $lessonEightTheory['category_slug_path'] ?? null);
        $this->assertSame(
            'Database\\Seeders\\Page_V3\\Tenses\\PastSimple\\PastSimpleFormsTheorySeeder',
            $lessonNineTheory['page_seeder_class'] ?? null
        );
        $this->assertSame('past-simple-forms', $lessonNineTheory['slug'] ?? null);
        $this->assertSame('tenses/past-simple', $lessonNineTheory['category_slug_path'] ?? null);
        $this->assertSame(
            'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsWillVsBeGoingToTheorySeeder',
            $lessonTenTheory['page_seeder_class'] ?? null
        );
        $this->assertSame('will-vs-be-going-to', $lessonTenTheory['slug'] ?? null);
        $this->assertSame('maibutni-formy', $lessonTenTheory['category_slug_path'] ?? null);
        $this->assertSame(
            'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesArticlesTheorySeeder',
            $lessonElevenTheory['page_seeder_class'] ?? null
        );
        $this->assertSame('articles-common-mistakes', $lessonElevenTheory['slug'] ?? null);
        $this->assertSame('common-mistakes', $lessonElevenTheory['category_slug_path'] ?? null);
        $this->assertSame('polyglot-have-got-has-got-a1', $lessonTwo->filters['next_lesson_slug'] ?? null);
        $this->assertSame('polyglot-there-is-there-are-a1', $lessonThree->filters['previous_lesson_slug'] ?? null);
        $this->assertSame('polyglot-present-simple-verbs-a1', $lessonThree->filters['next_lesson_slug'] ?? null);
        $this->assertSame('polyglot-have-got-has-got-a1', $lessonFour->filters['previous_lesson_slug'] ?? null);
        $this->assertSame('polyglot-can-cannot-a1', $lessonFour->filters['next_lesson_slug'] ?? null);
        $this->assertSame('polyglot-present-simple-verbs-a1', $lessonFive->filters['previous_lesson_slug'] ?? null);
        $this->assertSame('polyglot-present-continuous-a1', $lessonFive->filters['next_lesson_slug'] ?? null);
        $this->assertSame('polyglot-can-cannot-a1', $lessonSix->filters['previous_lesson_slug'] ?? null);
        $this->assertSame('polyglot-past-simple-to-be-a1', $lessonSix->filters['next_lesson_slug'] ?? null);
        $this->assertSame('polyglot-present-continuous-a1', $lessonSeven->filters['previous_lesson_slug'] ?? null);
        $this->assertSame('polyglot-past-simple-regular-verbs-a1', $lessonSeven->filters['next_lesson_slug'] ?? null);
        $this->assertSame('polyglot-past-simple-to-be-a1', $lessonEight->filters['previous_lesson_slug'] ?? null);
        $this->assertSame('polyglot-past-simple-irregular-verbs-a1', $lessonEight->filters['next_lesson_slug'] ?? null);
        $this->assertSame('polyglot-past-simple-regular-verbs-a1', $lessonNine->filters['previous_lesson_slug'] ?? null);
        $this->assertSame('polyglot-future-simple-will-a1', $lessonNine->filters['next_lesson_slug'] ?? null);
        $this->assertSame('polyglot-past-simple-irregular-verbs-a1', $lessonTen->filters['previous_lesson_slug'] ?? null);
        $this->assertSame('polyglot-articles-a-an-the-a1', $lessonTen->filters['next_lesson_slug'] ?? null);
        $this->assertSame('polyglot-future-simple-will-a1', $lessonEleven->filters['previous_lesson_slug'] ?? null);
        $this->assertSame('polyglot-some-any-a1', $lessonEleven->filters['next_lesson_slug'] ?? null);
    }

    public function test_long_question_uuid_mapping_is_deterministic_for_polyglot_lessons(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $lessonEightDefinition = json_decode(
            File::get(database_path('seeders/V3/Polyglot/PolyglotPastSimpleRegularVerbsLessonSeeder/definition.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $lessonNineDefinition = json_decode(
            File::get(database_path('seeders/V3/Polyglot/PolyglotPastSimpleIrregularVerbsLessonSeeder/definition.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $lessonTenDefinition = json_decode(
            File::get(database_path('seeders/V3/Polyglot/PolyglotFutureSimpleWillLessonSeeder/definition.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $lessonElevenDefinition = json_decode(
            File::get(database_path('seeders/V3/Polyglot/PolyglotArticlesAAnTheLessonSeeder/definition.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $lessonEightExternalUuids = array_column($lessonEightDefinition['questions'], 'uuid');
        $lessonNineExternalUuids = array_column($lessonNineDefinition['questions'], 'uuid');
        $lessonTenExternalUuids = array_column($lessonTenDefinition['questions'], 'uuid');
        $lessonElevenExternalUuids = array_column($lessonElevenDefinition['questions'], 'uuid');
        $lessonEightPersistentUuids = $resolver->toPersistentMany($lessonEightExternalUuids);
        $lessonNinePersistentUuids = $resolver->toPersistentMany($lessonNineExternalUuids);
        $lessonTenPersistentUuids = $resolver->toPersistentMany($lessonTenExternalUuids);
        $lessonElevenPersistentUuids = $resolver->toPersistentMany($lessonElevenExternalUuids);

        $this->assertContains('polyglot-past-simple-regular-verbs-q24', $lessonEightExternalUuids);
        $this->assertContains('polyglot-past-simple-irregular-verbs-q24', $lessonNineExternalUuids);
        $this->assertContains('polyglot-future-simple-will-q24', $lessonTenExternalUuids);
        $this->assertContains('polyglot-articles-a-an-the-q24', $lessonElevenExternalUuids);
        $this->assertCount(24, array_unique($lessonEightPersistentUuids));
        $this->assertCount(24, array_unique($lessonNinePersistentUuids));
        $this->assertCount(24, array_unique($lessonTenPersistentUuids));
        $this->assertCount(24, array_unique($lessonElevenPersistentUuids));
        $this->assertSame(
            $resolver->toPersistent('polyglot-past-simple-regular-verbs-q24'),
            $resolver->toPersistent('polyglot-past-simple-regular-verbs-q24')
        );
        $this->assertSame(
            $resolver->toPersistent('polyglot-past-simple-irregular-verbs-q24'),
            $resolver->toPersistent('polyglot-past-simple-irregular-verbs-q24')
        );
        $this->assertSame(
            'polyglot-future-simple-will-q24',
            $resolver->toPersistent('polyglot-future-simple-will-q24')
        );
        $this->assertSame(
            'polyglot-articles-a-an-the-q24',
            $resolver->toPersistent('polyglot-articles-a-an-the-q24')
        );
        $this->assertSame($lessonTenExternalUuids, $lessonTenPersistentUuids);
        $this->assertSame($lessonElevenExternalUuids, $lessonElevenPersistentUuids);

        $this->seed(PolyglotPastSimpleRegularVerbsLessonSeeder::class);
        $this->seed(PolyglotPastSimpleRegularVerbsLessonSeeder::class);
        $this->seed(PolyglotPastSimpleIrregularVerbsLessonSeeder::class);
        $this->seed(PolyglotPastSimpleIrregularVerbsLessonSeeder::class);
        $this->seed(PolyglotFutureSimpleWillLessonSeeder::class);
        $this->seed(PolyglotFutureSimpleWillLessonSeeder::class);
        $this->seed(PolyglotArticlesAAnTheLessonSeeder::class);
        $this->seed(PolyglotArticlesAAnTheLessonSeeder::class);

        $lessonEight = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-past-simple-regular-verbs-a1')
            ->firstOrFail();
        $lessonNine = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-past-simple-irregular-verbs-a1')
            ->firstOrFail();
        $lessonTen = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-future-simple-will-a1')
            ->firstOrFail();
        $lessonEleven = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-articles-a-an-the-a1')
            ->firstOrFail();

        $this->assertSame(24, Question::query()->whereIn('uuid', $lessonEightPersistentUuids)->count());
        $this->assertSame(24, Question::query()->whereIn('uuid', $lessonNinePersistentUuids)->count());
        $this->assertSame(24, Question::query()->whereIn('uuid', $lessonTenPersistentUuids)->count());
        $this->assertSame(24, Question::query()->whereIn('uuid', $lessonElevenPersistentUuids)->count());
        $this->assertSame($lessonEightPersistentUuids, $lessonEight->questionLinks->pluck('question_uuid')->all());
        $this->assertSame($lessonNinePersistentUuids, $lessonNine->questionLinks->pluck('question_uuid')->all());
        $this->assertSame($lessonTenPersistentUuids, $lessonTen->questionLinks->pluck('question_uuid')->all());
        $this->assertSame($lessonElevenPersistentUuids, $lessonEleven->questionLinks->pluck('question_uuid')->all());
        $this->assertSame(
            1,
            SavedGrammarTest::query()->where('slug', 'polyglot-past-simple-regular-verbs-a1')->count()
        );
        $this->assertSame(
            1,
            SavedGrammarTest::query()->where('slug', 'polyglot-past-simple-irregular-verbs-a1')->count()
        );
        $this->assertSame(
            1,
            SavedGrammarTest::query()->where('slug', 'polyglot-future-simple-will-a1')->count()
        );
        $this->assertSame(
            1,
            SavedGrammarTest::query()->where('slug', 'polyglot-articles-a-an-the-a1')->count()
        );
    }

    public function test_v3_generator_template_file_exists_and_contains_required_markers(): void
    {
        $path = base_path('docs/prompts/polyglot-v3-lesson-generator.md');

        $this->assertFileExists($path);

        $contents = File::get($path);

        $this->assertStringContainsString('Polyglot V3 Lesson Generator Workflow', $contents);
        $this->assertStringContainsString('polyglot:generate-v3-prompt', $contents);
        $this->assertStringContainsString('definition.json', $contents);
        $this->assertStringContainsString('localizations/uk.json', $contents);
        $this->assertStringContainsString('prompt_generator', $contents);
        $this->assertStringContainsString('page_seeder_class', $contents);
        $this->assertStringContainsString('compose_tokens', $contents);
        $this->assertStringContainsString('Duplicate correct tokens must remain preserved', $contents);
    }
}
