<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotGenerateV3PromptCommandTest extends TestCase
{
    use RebuildsComposeTestSchema;

    /**
     * @var array<int, string>
     */
    private array $cleanupPaths = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildComposeTestSchema();
    }

    protected function tearDown(): void
    {
        foreach (array_reverse($this->cleanupPaths) as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
                continue;
            }

            File::delete($path);
        }

        parent::tearDown();
    }

    public function test_command_prints_prompt_for_existing_theory_page(): void
    {
        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'verb-to-be',
            'theoryPageSlug' => 'verb-to-be-present',
            'lessonSlug' => 'polyglot-sample-v3-lesson',
            'lessonOrder' => 3,
            '--title' => 'Polyglot Sample Lesson',
            '--topic' => 'verb to be',
            '--seeder' => 'PolyglotSampleLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-there-is-there-are-a1',
            '--items' => 24,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Resolved theory page: Verb to Be: Present Forms', $output);
        $this->assertStringContainsString('/theory/verb-to-be/verb-to-be-present', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotSampleLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('Codex Summary (Top):', $output);
        $this->assertMatchesRegularExpression('/CODEX PROMPT ID: GLZ-PROMPT-[A-F0-9]{8}/', $output);
    }

    public function test_command_writes_output_file(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-sample-v3-there-lesson.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'verb-to-be',
            'theoryPageSlug' => 'there-is-there-are',
            'lessonSlug' => 'polyglot-sample-v3-there-lesson',
            'lessonOrder' => 4,
            '--title' => 'Polyglot There Sample',
            '--topic' => 'there is / there are',
            '--seeder' => 'PolyglotSampleThereLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-sample-v3-lesson',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-TEST-WRITE',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Prompt output: ' . $outputRelativePath, $output);
        $this->assertStringStartsWith(
            'CODEX PROMPT ID: GLZ-PROMPT-TEST-WRITE' . "\n\n" . 'Codex Summary (Top):' . "\n" . 'CODEX PROMPT ID: GLZ-PROMPT-TEST-WRITE',
            $contents
        );
        $this->assertStringEndsWith("\n\nCODEX PROMPT ID: GLZ-PROMPT-TEST-WRITE", $contents);
        $this->assertStringContainsString('Codex Summary (Top):', $contents);
        $this->assertStringContainsString('Codex Summary (Bottom):', $contents);
    }

    public function test_command_writes_have_got_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-have-got-has-got-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'basic-grammar',
            'theoryPageSlug' => 'have-got-has-got',
            'lessonSlug' => 'polyglot-have-got-has-got-a1',
            'lessonOrder' => 3,
            '--title' => 'Polyglot Have Got / Has Got',
            '--topic' => 'have got / has got',
            '--seeder' => 'PolyglotHaveGotHasGotLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-there-is-there-are-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-HAVE-GOT-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Have Got / Has Got', $output);
        $this->assertStringContainsString('/theory/basic-grammar/have-got-has-got', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotHaveGotHasGotLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-HAVE-GOT-TEST', $contents);
        $this->assertStringContainsString('polyglot-have-got-has-got-a1', $contents);
    }

    public function test_command_writes_present_simple_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-present-simple-verbs-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'present-simple',
            'theoryPageSlug' => 'present-simple-questions',
            'lessonSlug' => 'polyglot-present-simple-verbs-a1',
            'lessonOrder' => 4,
            '--title' => 'Polyglot Present Simple Verbs',
            '--topic' => 'present simple lexical verbs',
            '--seeder' => 'PolyglotPresentSimpleVerbsLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-have-got-has-got-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PRESENT-SIMPLE-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString(
            'Resolved theory page: Present Simple: Questions and Short Answers',
            $output
        );
        $this->assertStringContainsString('/theory/present-simple/present-simple-questions', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPresentSimpleVerbsLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-PRESENT-SIMPLE-TEST', $contents);
        $this->assertStringContainsString('polyglot-present-simple-verbs-a1', $contents);
    }

    public function test_command_writes_can_cannot_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-can-cannot-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'modal-verbs',
            'theoryPageSlug' => 'can-could',
            'lessonSlug' => 'polyglot-can-cannot-a1',
            'lessonOrder' => 5,
            '--title' => 'Polyglot Can / Cannot',
            '--topic' => 'can / cannot',
            '--seeder' => 'PolyglotCanCannotLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-present-simple-verbs-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-CAN-CANNOT-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Can / Could', $output);
        $this->assertStringContainsString('/theory/modal-verbs/can-could', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotCanCannotLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-CAN-CANNOT-TEST', $contents);
        $this->assertStringContainsString('polyglot-can-cannot-a1', $contents);
    }

    public function test_command_writes_present_continuous_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-present-continuous-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'present-continuous',
            'theoryPageSlug' => 'present-continuous-forms',
            'lessonSlug' => 'polyglot-present-continuous-a1',
            'lessonOrder' => 6,
            '--title' => 'Polyglot Present Continuous',
            '--topic' => 'present continuous',
            '--seeder' => 'PolyglotPresentContinuousLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-can-cannot-a1',
            '--next' => 'polyglot-past-simple-to-be-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PRESENT-CONTINUOUS-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString(
            'Resolved theory page: Present Continuous: Forms and Use',
            $output
        );
        $this->assertStringContainsString(
            '/theory/present-continuous/present-continuous-forms',
            $output
        );
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPresentContinuousLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-PRESENT-CONTINUOUS-TEST', $contents);
        $this->assertStringContainsString('polyglot-present-continuous-a1', $contents);
    }

    public function test_command_writes_past_simple_to_be_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-past-simple-to-be-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'verb-to-be',
            'theoryPageSlug' => 'verb-to-be-past',
            'lessonSlug' => 'polyglot-past-simple-to-be-a1',
            'lessonOrder' => 7,
            '--title' => 'Polyglot: past simple of to be (A1)',
            '--topic' => 'past simple of to be',
            '--seeder' => 'PolyglotPastSimpleToBeLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-present-continuous-a1',
            '--next' => 'polyglot-past-simple-regular-verbs-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PAST-SIMPLE-TO-BE-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Verb to Be: Past Forms', $output);
        $this->assertStringContainsString('/theory/verb-to-be/verb-to-be-past', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPastSimpleToBeLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-PAST-SIMPLE-TO-BE-TEST', $contents);
        $this->assertStringContainsString('polyglot-past-simple-to-be-a1', $contents);
    }

    public function test_command_writes_past_simple_regular_verbs_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-past-simple-regular-verbs-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'past-simple',
            'theoryPageSlug' => 'past-simple-forms',
            'lessonSlug' => 'polyglot-past-simple-regular-verbs-a1',
            'lessonOrder' => 8,
            '--title' => 'Polyglot: past simple regular verbs (A1)',
            '--topic' => 'past simple regular verbs',
            '--seeder' => 'PolyglotPastSimpleRegularVerbsLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-past-simple-to-be-a1',
            '--next' => 'polyglot-past-simple-irregular-verbs-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PAST-SIMPLE-REGULAR-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Past Simple: Forms and Use', $output);
        $this->assertStringContainsString('/theory/past-simple/past-simple-forms', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPastSimpleRegularVerbsLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-PAST-SIMPLE-REGULAR-TEST', $contents);
        $this->assertStringContainsString('polyglot-past-simple-regular-verbs-a1', $contents);
    }

    public function test_command_writes_past_simple_irregular_verbs_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-past-simple-irregular-verbs-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'past-simple',
            'theoryPageSlug' => 'past-simple-forms',
            'lessonSlug' => 'polyglot-past-simple-irregular-verbs-a1',
            'lessonOrder' => 9,
            '--title' => 'Polyglot: past simple irregular verbs (A1)',
            '--topic' => 'past simple irregular verbs',
            '--seeder' => 'PolyglotPastSimpleIrregularVerbsLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-past-simple-regular-verbs-a1',
            '--next' => 'polyglot-future-simple-will-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PAST-SIMPLE-IRREGULAR-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Past Simple: Forms and Use', $output);
        $this->assertStringContainsString('/theory/past-simple/past-simple-forms', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPastSimpleIrregularVerbsLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-PAST-SIMPLE-IRREGULAR-TEST', $contents);
        $this->assertStringContainsString('polyglot-past-simple-irregular-verbs-a1', $contents);
    }

    public function test_command_writes_future_simple_will_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-future-simple-will-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'maibutni-formy',
            'theoryPageSlug' => 'will-vs-be-going-to',
            'lessonSlug' => 'polyglot-future-simple-will-a1',
            'lessonOrder' => 10,
            '--title' => 'Polyglot: future simple with will (A1)',
            '--topic' => 'future simple with will',
            '--seeder' => 'PolyglotFutureSimpleWillLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-past-simple-irregular-verbs-a1',
            '--next' => 'polyglot-articles-a-an-the-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-FUTURE-SIMPLE-WILL-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Will vs Be Going To — Вибір форми', $output);
        $this->assertStringContainsString('/theory/maibutni-formy/will-vs-be-going-to', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotFutureSimpleWillLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-FUTURE-SIMPLE-WILL-TEST', $contents);
        $this->assertStringContainsString('polyglot-future-simple-will-a1', $contents);
    }

    public function test_command_writes_articles_a_an_the_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-articles-a-an-the-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'common-mistakes',
            'theoryPageSlug' => 'articles-common-mistakes',
            'lessonSlug' => 'polyglot-articles-a-an-the-a1',
            'lessonOrder' => 11,
            '--title' => 'Polyglot: articles a / an / the (A1)',
            '--topic' => 'articles a / an / the',
            '--seeder' => 'PolyglotArticlesAAnTheLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-future-simple-will-a1',
            '--next' => 'polyglot-some-any-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-ARTICLES-A-AN-THE-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Articles: Common Mistakes', $output);
        $this->assertStringContainsString('/theory/common-mistakes/articles-common-mistakes', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotArticlesAAnTheLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-ARTICLES-A-AN-THE-TEST', $contents);
        $this->assertStringContainsString('polyglot-articles-a-an-the-a1', $contents);
    }

    public function test_command_writes_some_any_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-some-any-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'some-any',
            'theoryPageSlug' => 'theory-some-any-things',
            'lessonSlug' => 'polyglot-some-any-a1',
            'lessonOrder' => 12,
            '--title' => 'Polyglot: some / any (A1)',
            '--topic' => 'some / any',
            '--seeder' => 'PolyglotSomeAnyLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-articles-a-an-the-a1',
            '--next' => 'polyglot-much-many-a-lot-of-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-SOME-ANY-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Some / Any — Things', $output);
        $this->assertStringContainsString('/theory/some-any/theory-some-any-things', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotSomeAnyLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-SOME-ANY-TEST', $contents);
        $this->assertStringContainsString('polyglot-some-any-a1', $contents);
    }

    public function test_command_writes_much_many_a_lot_of_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-much-many-a-lot-of-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'imennyky-artykli-ta-kilkist',
            'theoryPageSlug' => 'quantifiers-much-many-a-lot-few-little',
            'lessonSlug' => 'polyglot-much-many-a-lot-of-a1',
            'lessonOrder' => 13,
            '--title' => 'Polyglot: much / many / a lot of (A1)',
            '--topic' => 'much / many / a lot of',
            '--seeder' => 'PolyglotMuchManyALotOfLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-some-any-a1',
            '--next' => 'polyglot-comparatives-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-MUCH-MANY-A-LOT-OF-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString(
            'Resolved theory page: Quantifiers — much, many, few, little',
            $output
        );
        $this->assertStringContainsString(
            '/theory/imennyky-artykli-ta-kilkist/quantifiers-much-many-a-lot-few-little',
            $output
        );
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotMuchManyALotOfLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-MUCH-MANY-A-LOT-OF-TEST', $contents);
        $this->assertStringContainsString('polyglot-much-many-a-lot-of-a1', $contents);
    }

    public function test_command_writes_comparatives_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-comparatives-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'prykmetniky-ta-pryslinknyky',
            'theoryPageSlug' => 'theory-degrees-of-comparison',
            'lessonSlug' => 'polyglot-comparatives-a1',
            'lessonOrder' => 14,
            '--title' => 'Polyglot: comparative adjectives (A1)',
            '--topic' => 'comparative adjectives',
            '--seeder' => 'PolyglotComparativesLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-much-many-a-lot-of-a1',
            '--next' => 'polyglot-superlatives-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-COMPARATIVES-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Degrees of Comparison', $output);
        $this->assertStringContainsString(
            '/theory/prykmetniky-ta-pryslinknyky/theory-degrees-of-comparison',
            $output
        );
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotComparativesLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-COMPARATIVES-TEST', $contents);
        $this->assertStringContainsString('polyglot-comparatives-a1', $contents);
    }

    public function test_command_writes_superlatives_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-superlatives-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'prykmetniky-ta-pryslinknyky',
            'theoryPageSlug' => 'comparative-vs-superlative',
            'lessonSlug' => 'polyglot-superlatives-a1',
            'lessonOrder' => 15,
            '--title' => 'Polyglot: superlatives (A1)',
            '--topic' => 'superlatives',
            '--seeder' => 'PolyglotSuperlativesLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-comparatives-a1',
            '--next' => 'polyglot-final-drill-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-SUPERLATIVES-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Comparative vs Superlative', $output);
        $this->assertStringContainsString(
            '/theory/prykmetniky-ta-pryslinknyky/comparative-vs-superlative',
            $output
        );
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotSuperlativesLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-SUPERLATIVES-TEST', $contents);
        $this->assertStringContainsString('polyglot-superlatives-a1', $contents);
    }

    public function test_command_writes_final_drill_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-final-drill-a1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'basic-grammar',
            'theoryPageSlug' => 'a1-mixed-revision',
            'lessonSlug' => 'polyglot-final-drill-a1',
            'lessonOrder' => 16,
            '--title' => 'Polyglot: mixed revision / final drill (A1)',
            '--topic' => 'mixed revision / final drill',
            '--seeder' => 'PolyglotFinalDrillLessonSeeder',
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--previous' => 'polyglot-superlatives-a1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-FINAL-DRILL-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: A1 Mixed Revision', $output);
        $this->assertStringContainsString('/theory/basic-grammar/a1-mixed-revision', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotFinalDrillLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-FINAL-DRILL-TEST', $contents);
        $this->assertStringContainsString('polyglot-final-drill-a1', $contents);
    }

    public function test_command_writes_present_perfect_basic_a2_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-present-perfect-basic-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'present-perfect',
            'theoryPageSlug' => 'present-perfect-forms',
            'lessonSlug' => 'polyglot-present-perfect-basic-a2',
            'lessonOrder' => 1,
            '--title' => 'Polyglot: present perfect basic (A2)',
            '--topic' => 'present perfect basic',
            '--seeder' => 'PolyglotPresentPerfectBasicLessonSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--next' => 'polyglot-present-perfect-vs-past-simple-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PRESENT-PERFECT-BASIC-A2-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Present Perfect: Forms and Use', $output);
        $this->assertStringContainsString('/theory/present-perfect/present-perfect-forms', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPresentPerfectBasicLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-PRESENT-PERFECT-BASIC-A2-TEST', $contents);
        $this->assertStringContainsString('polyglot-present-perfect-basic-a2', $contents);
    }

    public function test_command_writes_present_perfect_vs_past_simple_a2_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-present-perfect-vs-past-simple-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'tenses',
            'theoryPageSlug' => 'present-perfect-vs-past-simple',
            'lessonSlug' => 'polyglot-present-perfect-vs-past-simple-a2',
            'lessonOrder' => 2,
            '--title' => 'Polyglot: present perfect vs past simple (A2)',
            '--topic' => 'present perfect vs past simple',
            '--seeder' => 'PolyglotPresentPerfectVsPastSimpleLessonSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-present-perfect-basic-a2',
            '--next' => 'polyglot-first-conditional-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PRESENT-PERFECT-VS-PAST-SIMPLE-A2-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Present Perfect vs Past Simple', $output);
        $this->assertStringContainsString('/theory/tenses/present-perfect-vs-past-simple', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPresentPerfectVsPastSimpleLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-PRESENT-PERFECT-VS-PAST-SIMPLE-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-present-perfect-vs-past-simple-a2', $contents);
    }

    public function test_command_writes_first_conditional_a2_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-first-conditional-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'conditionals',
            'theoryPageSlug' => 'first-conditional',
            'lessonSlug' => 'polyglot-first-conditional-a2',
            'lessonOrder' => 3,
            '--title' => 'Polyglot: first conditional (A2)',
            '--topic' => 'first conditional',
            '--seeder' => 'PolyglotFirstConditionalLessonSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-present-perfect-vs-past-simple-a2',
            '--next' => 'polyglot-be-going-to-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-FIRST-CONDITIONAL-A2-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: First Conditional', $output);
        $this->assertStringContainsString('/theory/conditionals/first-conditional', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotFirstConditionalLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-FIRST-CONDITIONAL-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-first-conditional-a2', $contents);
    }

    public function test_command_writes_be_going_to_a2_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-be-going-to-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'maibutni-formy',
            'theoryPageSlug' => 'will-vs-be-going-to',
            'lessonSlug' => 'polyglot-be-going-to-a2',
            'lessonOrder' => 4,
            '--title' => 'Polyglot: be going to (A2)',
            '--topic' => 'be going to',
            '--seeder' => 'PolyglotBeGoingToLessonSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-first-conditional-a2',
            '--next' => 'polyglot-should-ought-to-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-BE-GOING-TO-A2-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Will vs Be Going To — Вибір форми', $output);
        $this->assertStringContainsString('/theory/maibutni-formy/will-vs-be-going-to', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotBeGoingToLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-BE-GOING-TO-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-be-going-to-a2', $contents);
    }

    public function test_command_writes_should_ought_to_a2_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-should-ought-to-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'modal-verbs',
            'theoryPageSlug' => 'should-ought-to',
            'lessonSlug' => 'polyglot-should-ought-to-a2',
            'lessonOrder' => 5,
            '--title' => 'Polyglot: should / ought to (A2)',
            '--topic' => 'should / ought to',
            '--seeder' => 'PolyglotShouldOughtToLessonSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-be-going-to-a2',
            '--next' => 'polyglot-must-have-to-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-SHOULD-OUGHT-TO-A2-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Should / Ought to', $output);
        $this->assertStringContainsString('/theory/modal-verbs/should-ought-to', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotShouldOughtToLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-SHOULD-OUGHT-TO-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-should-ought-to-a2', $contents);
    }

    public function test_command_writes_must_have_to_a2_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-must-have-to-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'modal-verbs',
            'theoryPageSlug' => 'must-have-to',
            'lessonSlug' => 'polyglot-must-have-to-a2',
            'lessonOrder' => 6,
            '--title' => 'Polyglot: must / have to (A2)',
            '--topic' => 'must / have to',
            '--seeder' => 'PolyglotMustHaveToLessonSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-should-ought-to-a2',
            '--next' => 'polyglot-gerund-vs-infinitive-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-MUST-HAVE-TO-A2-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Must / Have to', $output);
        $this->assertStringContainsString('/theory/modal-verbs/must-have-to', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotMustHaveToLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-MUST-HAVE-TO-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-must-have-to-a2', $contents);
    }

    public function test_command_writes_gerund_vs_infinitive_a2_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-gerund-vs-infinitive-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'verb-patterns',
            'theoryPageSlug' => 'gerund-vs-infinitive',
            'lessonSlug' => 'polyglot-gerund-vs-infinitive-a2',
            'lessonOrder' => 7,
            '--title' => 'Polyglot: gerund vs infinitive basics (A2)',
            '--topic' => 'gerund vs infinitive basics',
            '--seeder' => 'PolyglotGerundVsInfinitiveLessonSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-must-have-to-a2',
            '--next' => 'polyglot-past-continuous-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-GERUND-VS-INFINITIVE-A2-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Gerund vs Infinitive', $output);
        $this->assertStringContainsString('/theory/verb-patterns/gerund-vs-infinitive', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotGerundVsInfinitiveLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-GERUND-VS-INFINITIVE-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-gerund-vs-infinitive-a2', $contents);
    }

    public function test_command_writes_past_continuous_a2_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-past-continuous-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'past-continuous',
            'theoryPageSlug' => 'past-continuous-forms',
            'lessonSlug' => 'polyglot-past-continuous-a2',
            'lessonOrder' => 8,
            '--title' => 'Polyglot: past continuous (A2)',
            '--topic' => 'past continuous',
            '--seeder' => 'PolyglotPastContinuousLessonSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-gerund-vs-infinitive-a2',
            '--next' => 'polyglot-present-perfect-time-expressions-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PAST-CONTINUOUS-A2-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Past Continuous: Forms and Use', $output);
        $this->assertStringContainsString('/theory/past-continuous/past-continuous-forms', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPastContinuousLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-PAST-CONTINUOUS-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-past-continuous-a2', $contents);
    }

    public function test_command_writes_present_perfect_time_expressions_a2_prompt_for_real_theory_page(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-present-perfect-time-expressions-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'present-perfect',
            'theoryPageSlug' => 'present-perfect-time-expressions',
            'lessonSlug' => 'polyglot-present-perfect-time-expressions-a2',
            'lessonOrder' => 9,
            '--title' => 'Polyglot: present perfect time expressions (A2)',
            '--topic' => 'present perfect time expressions',
            '--seeder' => 'PolyglotPresentPerfectTimeExpressionsLessonSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-past-continuous-a2',
            '--next' => 'polyglot-relative-clauses-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PRESENT-PERFECT-TIME-EXPRESSIONS-A2-TEST',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Resolved theory page: Present Perfect: Time Expressions', $output);
        $this->assertStringContainsString('/theory/present-perfect/present-perfect-time-expressions', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPresentPerfectTimeExpressionsLessonSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-PRESENT-PERFECT-TIME-EXPRESSIONS-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-present-perfect-time-expressions-a2', $contents);
    }

    public function test_command_writes_relative_clauses_a2_prompt_for_real_theory_page_and_skeleton(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-relative-clauses-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $loaderPath = base_path('database/seeders/V3/Polyglot/PolyglotRelativeClausesPromptTestSeeder.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/PolyglotRelativeClausesPromptTestSeeder');
        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $outputAbsolutePath,
            dirname($outputAbsolutePath),
            $loaderPath,
            $packagePath,
        ]);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'clauses-and-linking-words',
            'theoryPageSlug' => 'relative-clauses',
            'lessonSlug' => 'polyglot-relative-clauses-a2',
            'lessonOrder' => 10,
            '--title' => 'Polyglot: relative clauses basics (A2)',
            '--topic' => 'relative clauses basics',
            '--seeder' => 'PolyglotRelativeClausesPromptTestSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-present-perfect-time-expressions-a2',
            '--next' => 'polyglot-passive-voice-basics-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-RELATIVE-CLAUSES-A2-TEST',
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($loaderPath);
        $this->assertFileExists($packagePath . '/definition.json');
        $this->assertStringContainsString('Resolved theory page: Relative Clauses', $output);
        $this->assertStringContainsString('/theory/clauses-and-linking-words/relative-clauses', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotRelativeClausesPromptTestSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-RELATIVE-CLAUSES-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-relative-clauses-a2', $contents);
    }

    public function test_command_writes_passive_voice_basics_a2_prompt_for_real_theory_page_and_skeleton(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-passive-voice-basics-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $loaderPath = base_path('database/seeders/V3/Polyglot/PolyglotPassiveVoiceBasicsPromptTestSeeder.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/PolyglotPassiveVoiceBasicsPromptTestSeeder');
        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $outputAbsolutePath,
            dirname($outputAbsolutePath),
            $loaderPath,
            $packagePath,
        ]);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'passive-voice',
            'theoryPageSlug' => 'theory-passive-voice-formation-rules',
            'lessonSlug' => 'polyglot-passive-voice-basics-a2',
            'lessonOrder' => 11,
            '--title' => 'Polyglot: passive voice basics (A2)',
            '--topic' => 'passive voice basics',
            '--seeder' => 'PolyglotPassiveVoiceBasicsPromptTestSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-relative-clauses-a2',
            '--next' => 'polyglot-reported-speech-basics-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PASSIVE-VOICE-BASICS-A2-TEST',
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($loaderPath);
        $this->assertFileExists($packagePath . '/definition.json');
        $this->assertStringContainsString(
            'Resolved theory page: Formation Rules — Правила утворення пасиву',
            $output
        );
        $this->assertStringContainsString(
            '/theory/passive-voice/theory-passive-voice-formation-rules',
            $output
        );
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPassiveVoiceBasicsPromptTestSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-PASSIVE-VOICE-BASICS-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-passive-voice-basics-a2', $contents);
    }

    public function test_command_writes_reported_speech_basics_a2_prompt_for_real_theory_page_and_skeleton(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-reported-speech-basics-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $loaderPath = base_path('database/seeders/V3/Polyglot/PolyglotReportedSpeechBasicsPromptTestSeeder.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/PolyglotReportedSpeechBasicsPromptTestSeeder');
        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $outputAbsolutePath,
            dirname($outputAbsolutePath),
            $loaderPath,
            $packagePath,
        ]);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'reported-speech',
            'theoryPageSlug' => 'reported-statements',
            'lessonSlug' => 'polyglot-reported-speech-basics-a2',
            'lessonOrder' => 12,
            '--title' => 'Polyglot: reported speech basics (A2)',
            '--topic' => 'reported speech basics',
            '--seeder' => 'PolyglotReportedSpeechBasicsPromptTestSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-passive-voice-basics-a2',
            '--next' => 'polyglot-used-to-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-REPORTED-SPEECH-BASICS-A2-TEST',
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($loaderPath);
        $this->assertFileExists($packagePath . '/definition.json');
        $this->assertStringContainsString('Resolved theory page: Reported Statements', $output);
        $this->assertStringContainsString('/theory/reported-speech/reported-statements', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotReportedSpeechBasicsPromptTestSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-REPORTED-SPEECH-BASICS-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-reported-speech-basics-a2', $contents);
    }

    public function test_command_writes_used_to_a2_prompt_for_real_theory_page_and_skeleton(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-used-to-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $loaderPath = base_path('database/seeders/V3/Polyglot/PolyglotUsedToPromptTestSeeder.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/PolyglotUsedToPromptTestSeeder');
        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $outputAbsolutePath,
            dirname($outputAbsolutePath),
            $loaderPath,
            $packagePath,
        ]);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'tenses',
            'theoryPageSlug' => 'used-to-would',
            'lessonSlug' => 'polyglot-used-to-a2',
            'lessonOrder' => 13,
            '--title' => 'Polyglot: used to (A2)',
            '--topic' => 'used to',
            '--seeder' => 'PolyglotUsedToPromptTestSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-reported-speech-basics-a2',
            '--next' => 'polyglot-question-tags-basics-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-USED-TO-A2-TEST',
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($loaderPath);
        $this->assertFileExists($packagePath . '/definition.json');
        $this->assertStringContainsString('Resolved theory page: Used to / Would', $output);
        $this->assertStringContainsString('/theory/tenses/used-to-would', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotUsedToPromptTestSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-USED-TO-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-used-to-a2', $contents);
    }

    public function test_command_writes_question_tags_basics_a2_prompt_for_real_theory_page_and_skeleton(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-question-tags-basics-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $loaderPath = base_path('database/seeders/V3/Polyglot/PolyglotQuestionTagsBasicsPromptTestSeeder.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/PolyglotQuestionTagsBasicsPromptTestSeeder');
        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $outputAbsolutePath,
            dirname($outputAbsolutePath),
            $loaderPath,
            $packagePath,
        ]);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'types-of-questions',
            'theoryPageSlug' => 'question-tags-disjunctive-questions-dont-you-isnt-it',
            'lessonSlug' => 'polyglot-question-tags-basics-a2',
            'lessonOrder' => 14,
            '--title' => 'Polyglot: question tags basics (A2)',
            '--topic' => 'question tags basics',
            '--seeder' => 'PolyglotQuestionTagsBasicsPromptTestSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-used-to-a2',
            '--next' => 'polyglot-second-conditional-basics-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-QUESTION-TAGS-BASICS-A2-TEST',
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($loaderPath);
        $this->assertFileExists($packagePath . '/definition.json');
        $this->assertStringContainsString('Resolved theory page: Question Tags', $output);
        $this->assertStringContainsString(
            '/theory/types-of-questions/question-tags-disjunctive-questions-dont-you-isnt-it',
            $output
        );
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotQuestionTagsBasicsPromptTestSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-QUESTION-TAGS-BASICS-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-question-tags-basics-a2', $contents);
    }

    public function test_command_writes_second_conditional_basics_a2_prompt_for_real_theory_page_and_skeleton(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-second-conditional-basics-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $loaderPath = base_path('database/seeders/V3/Polyglot/PolyglotSecondConditionalBasicsPromptTestSeeder.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/PolyglotSecondConditionalBasicsPromptTestSeeder');
        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $outputAbsolutePath,
            dirname($outputAbsolutePath),
            $loaderPath,
            $packagePath,
        ]);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'conditionals',
            'theoryPageSlug' => 'second-conditional',
            'lessonSlug' => 'polyglot-second-conditional-basics-a2',
            'lessonOrder' => 15,
            '--title' => 'Polyglot: second conditional basics (A2)',
            '--topic' => 'second conditional basics',
            '--seeder' => 'PolyglotSecondConditionalBasicsPromptTestSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-question-tags-basics-a2',
            '--next' => 'polyglot-final-drill-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-SECOND-CONDITIONAL-BASICS-A2-TEST',
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($loaderPath);
        $this->assertFileExists($packagePath . '/definition.json');
        $this->assertStringContainsString('Resolved theory page: Second Conditional', $output);
        $this->assertStringContainsString('/theory/conditionals/second-conditional', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotSecondConditionalBasicsPromptTestSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-SECOND-CONDITIONAL-BASICS-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-second-conditional-basics-a2', $contents);
    }

    public function test_command_writes_final_drill_a2_prompt_for_real_theory_page_and_skeleton(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-final-drill-a2.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $loaderPath = base_path('database/seeders/V3/Polyglot/PolyglotFinalDrillA2PromptTestSeeder.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/PolyglotFinalDrillA2PromptTestSeeder');
        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $outputAbsolutePath,
            dirname($outputAbsolutePath),
            $loaderPath,
            $packagePath,
        ]);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'basic-grammar',
            'theoryPageSlug' => 'a2-mixed-revision',
            'lessonSlug' => 'polyglot-final-drill-a2',
            'lessonOrder' => 16,
            '--title' => 'Polyglot: mixed revision / final drill (A2)',
            '--topic' => 'mixed revision / final drill',
            '--seeder' => 'PolyglotFinalDrillA2PromptTestSeeder',
            '--course' => 'polyglot-english-a2',
            '--level' => 'A2',
            '--previous' => 'polyglot-second-conditional-basics-a2',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-FINAL-DRILL-A2-TEST',
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($loaderPath);
        $this->assertFileExists($packagePath . '/definition.json');
        $this->assertStringContainsString('Resolved theory page: A2 Mixed Revision', $output);
        $this->assertStringContainsString('/theory/basic-grammar/a2-mixed-revision', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotFinalDrillA2PromptTestSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-FINAL-DRILL-A2-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-final-drill-a2', $contents);
    }

    public function test_command_writes_present_perfect_continuous_basics_b1_prompt_for_real_theory_page_and_skeleton(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-present-perfect-continuous-basics-b1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $loaderPath = base_path('database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousBasicsPromptTestSeeder.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousBasicsPromptTestSeeder');
        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $outputAbsolutePath,
            dirname($outputAbsolutePath),
            $loaderPath,
            $packagePath,
        ]);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'present-perfect-continuous',
            'theoryPageSlug' => 'present-perfect-continuous-forms',
            'lessonSlug' => 'polyglot-present-perfect-continuous-basics-b1',
            'lessonOrder' => 1,
            '--title' => 'Polyglot: present perfect continuous basics (B1)',
            '--topic' => 'present perfect continuous basics',
            '--seeder' => 'PolyglotPresentPerfectContinuousBasicsPromptTestSeeder',
            '--course' => 'polyglot-english-b1',
            '--level' => 'B1',
            '--next' => 'polyglot-present-perfect-continuous-vs-present-perfect-b1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PP-CONT-BASICS-B1-TEST',
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($loaderPath);
        $this->assertFileExists($packagePath . '/definition.json');
        $this->assertStringContainsString(
            'Resolved theory page: Present Perfect Continuous: Forms and Use',
            $output
        );
        $this->assertStringContainsString(
            '/theory/present-perfect-continuous/present-perfect-continuous-forms',
            $output
        );
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousBasicsPromptTestSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-PP-CONT-BASICS-B1-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-present-perfect-continuous-basics-b1', $contents);
        $this->assertStringContainsString('polyglot-english-b1', $contents);
    }

    public function test_command_writes_present_perfect_continuous_vs_present_perfect_b1_prompt_for_real_theory_page_and_skeleton(): void
    {
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-present-perfect-continuous-vs-present-perfect-b1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $loaderPath = base_path('database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousVsPresentPerfectPromptTestSeeder.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousVsPresentPerfectPromptTestSeeder');
        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $outputAbsolutePath,
            dirname($outputAbsolutePath),
            $loaderPath,
            $packagePath,
        ]);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'tenses',
            'theoryPageSlug' => 'present-perfect-vs-present-perfect-continuous',
            'lessonSlug' => 'polyglot-present-perfect-continuous-vs-present-perfect-b1',
            'lessonOrder' => 2,
            '--title' => 'Polyglot: present perfect continuous vs present perfect (B1)',
            '--topic' => 'present perfect continuous vs present perfect',
            '--seeder' => 'PolyglotPresentPerfectContinuousVsPresentPerfectPromptTestSeeder',
            '--course' => 'polyglot-english-b1',
            '--level' => 'B1',
            '--previous' => 'polyglot-present-perfect-continuous-basics-b1',
            '--next' => 'polyglot-past-perfect-basics-b1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PP-CONT-VS-PP-B1-TEST',
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($loaderPath);
        $this->assertFileExists($packagePath . '/definition.json');
        $this->assertStringContainsString(
            'Resolved theory page: Present Perfect vs Present Perfect Continuous',
            $output
        );
        $this->assertStringContainsString(
            '/theory/tenses/present-perfect-vs-present-perfect-continuous',
            $output
        );
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousVsPresentPerfectPromptTestSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-PP-CONT-VS-PP-B1-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-present-perfect-continuous-vs-present-perfect-b1', $contents);
        $this->assertStringContainsString('polyglot-english-b1', $contents);
    }

    public function test_command_writes_past_perfect_basics_b1_prompt_for_real_theory_page_and_skeleton(): void
    {
        $seeder = 'PolyglotPastPerfectBasicsPromptTestSeeder';
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-past-perfect-basics-b1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $loaderPath = base_path('database/seeders/V3/Polyglot/' . $seeder . '.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/' . $seeder);

        File::delete($outputAbsolutePath);
        File::delete($loaderPath);
        File::deleteDirectory($packagePath);

        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $outputAbsolutePath,
            dirname($outputAbsolutePath),
            $loaderPath,
            $packagePath,
        ]);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'past-perfect',
            'theoryPageSlug' => 'past-perfect-forms',
            'lessonSlug' => 'polyglot-past-perfect-basics-b1',
            'lessonOrder' => 3,
            '--title' => 'Polyglot: past perfect basics (B1)',
            '--topic' => 'past perfect basics',
            '--seeder' => 'PolyglotPastPerfectBasicsPromptTestSeeder',
            '--course' => 'polyglot-english-b1',
            '--level' => 'B1',
            '--previous' => 'polyglot-present-perfect-continuous-vs-present-perfect-b1',
            '--next' => 'polyglot-narrative-tenses-basics-b1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-PAST-PERFECT-B1-TEST',
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($loaderPath);
        $this->assertFileExists($packagePath . '/definition.json');
        $this->assertStringContainsString(
            'Resolved theory page: Past Perfect: Forms and Use',
            $output
        );
        $this->assertStringContainsString(
            '/theory/past-perfect/past-perfect-forms',
            $output
        );
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotPastPerfectBasicsPromptTestSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-PAST-PERFECT-B1-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-past-perfect-basics-b1', $contents);
        $this->assertStringContainsString('polyglot-english-b1', $contents);
    }

    public function test_command_writes_narrative_tenses_basics_b1_prompt_for_real_theory_page_and_skeleton(): void
    {
        $seeder = 'PolyglotNarrativeTensesBasicsPromptTestSeeder';
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-narrative-tenses-basics-b1.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $loaderPath = base_path('database/seeders/V3/Polyglot/' . $seeder . '.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/' . $seeder);

        File::delete($outputAbsolutePath);
        File::delete($loaderPath);
        File::deleteDirectory($packagePath);

        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $outputAbsolutePath,
            dirname($outputAbsolutePath),
            $loaderPath,
            $packagePath,
        ]);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'tenses',
            'theoryPageSlug' => 'narrative-tenses',
            'lessonSlug' => 'polyglot-narrative-tenses-basics-b1',
            'lessonOrder' => 4,
            '--title' => 'Polyglot: narrative tenses basics (B1)',
            '--topic' => 'narrative tenses basics',
            '--seeder' => 'PolyglotNarrativeTensesBasicsPromptTestSeeder',
            '--course' => 'polyglot-english-b1',
            '--level' => 'B1',
            '--previous' => 'polyglot-past-perfect-basics-b1',
            '--next' => 'polyglot-future-continuous-basics-b1',
            '--items' => 24,
            '--prompt-id' => 'GLZ-PROMPT-NARRATIVE-TENSES-B1-TEST',
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($loaderPath);
        $this->assertFileExists($packagePath . '/definition.json');
        $this->assertStringContainsString(
            'Resolved theory page: Narrative Tenses: Past Simple, Past Continuous and Past Perfect',
            $output
        );
        $this->assertStringContainsString('/theory/tenses/narrative-tenses', $output);
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotNarrativeTensesBasicsPromptTestSeeder/definition.json',
            $output
        );
        $this->assertStringContainsString(
            'CODEX PROMPT ID: GLZ-PROMPT-NARRATIVE-TENSES-B1-TEST',
            $contents
        );
        $this->assertStringContainsString('polyglot-narrative-tenses-basics-b1', $contents);
        $this->assertStringContainsString('polyglot-english-b1', $contents);
    }

    public function test_skeleton_writer_creates_canonical_package_and_respects_force_flag(): void
    {
        $seeder = 'PolyglotSkeletonDemoTestSeeder';
        $loaderPath = base_path('database/seeders/V3/Polyglot/' . $seeder . '.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/' . $seeder);
        $definitionPath = $packagePath . '/definition.json';
        $realSeederPath = $packagePath . '/' . $seeder . '.php';
        $ukPath = $packagePath . '/localizations/uk.json';
        $enPath = $packagePath . '/localizations/en.json';
        $plPath = $packagePath . '/localizations/pl.json';

        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $loaderPath,
            $packagePath,
        ]);

        $firstExitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'verb-to-be',
            'theoryPageSlug' => 'verb-to-be-present',
            'lessonSlug' => 'polyglot-skeleton-demo-test',
            'lessonOrder' => 5,
            '--title' => 'Polyglot Skeleton Demo Test',
            '--topic' => 'verb to be',
            '--seeder' => $seeder,
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--items' => 24,
            '--write-skeleton' => true,
        ]);

        $this->assertSame(0, $firstExitCode);
        $this->assertFileExists($loaderPath);
        $this->assertFileExists($definitionPath);
        $this->assertFileExists($realSeederPath);
        $this->assertFileExists($ukPath);
        $this->assertFileExists($enPath);
        $this->assertFileExists($plPath);
        $this->assertStringContainsString(
            "require_once __DIR__ . '/{$seeder}/{$seeder}.php';",
            File::get($loaderPath)
        );
        $definition = json_decode(File::get($definitionPath), true, 512, JSON_THROW_ON_ERROR);
        $ukLocalization = json_decode(File::get($ukPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(
            'Database\\Seeders\\V3\\Polyglot\\' . $seeder,
            $definition['seeder']['class'] ?? null
        );
        $this->assertSame(
            'Database\\Seeders\\V3\\Localizations\\Uk\\Polyglot\\PolyglotSkeletonDemoTestLocalizationSeeder',
            $ukLocalization['seeder']['class'] ?? null
        );

        File::put($loaderPath, 'do-not-overwrite');

        $secondExitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'verb-to-be',
            'theoryPageSlug' => 'verb-to-be-present',
            'lessonSlug' => 'polyglot-skeleton-demo-test',
            'lessonOrder' => 5,
            '--title' => 'Polyglot Skeleton Demo Test',
            '--topic' => 'verb to be',
            '--seeder' => $seeder,
            '--course' => 'polyglot-english-a1',
            '--level' => 'A1',
            '--items' => 24,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(1, $secondExitCode);
        $this->assertStringContainsString('Refusing to overwrite existing files without --force', $output);
        $this->assertSame('do-not-overwrite', File::get($loaderPath));
    }

    public function test_invalid_theory_page_is_rejected_without_writing_files(): void
    {
        $seeder = 'PolyglotInvalidTheorySeeder';
        $outputRelativePath = 'storage/app/testing/polyglot-prompts/polyglot-invalid-theory.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $loaderPath = base_path('database/seeders/V3/Polyglot/' . $seeder . '.php');
        $packagePath = base_path('database/seeders/V3/Polyglot/' . $seeder);

        $this->cleanupPaths = array_merge($this->cleanupPaths, [
            $outputAbsolutePath,
            dirname($outputAbsolutePath),
            $loaderPath,
            $packagePath,
        ]);

        $exitCode = Artisan::call('polyglot:generate-v3-prompt', [
            'theoryCategorySlug' => 'verb-to-be',
            'theoryPageSlug' => 'missing-theory-page',
            'lessonSlug' => 'polyglot-invalid-theory',
            'lessonOrder' => 9,
            '--title' => 'Polyglot Invalid Theory',
            '--topic' => 'verb to be',
            '--seeder' => $seeder,
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Theory page [verb-to-be/missing-theory-page] was not found', $output);
        $this->assertFileDoesNotExist($outputAbsolutePath);
        $this->assertFileDoesNotExist($loaderPath);
        $this->assertFalse(File::isDirectory($packagePath));
    }
}
