<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use Illuminate\Support\Facades\Artisan;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotImportLessonCommandTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildComposeTestSchema();
    }

    public function test_console_command_preview_works(): void
    {
        $exitCode = Artisan::call('polyglot:import-lesson', [
            'path' => database_path('seeders/V2/Polyglot/Data/polyglot-to-be-a1.json'),
            '--preview' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertSame(0, SavedGrammarTest::query()->count());
        $this->assertSame(0, Question::query()->count());
        $this->assertStringContainsString('Preview only. No database changes were written.', $output);
        $this->assertStringContainsString('Slug: polyglot-to-be-a1', $output);
        $this->assertStringContainsString('Lesson order: 1', $output);
        $this->assertStringContainsString('Items: 24', $output);
        $this->assertStringContainsString('polyglot-to-be-q01', $output);
    }

    public function test_console_command_import_works(): void
    {
        $exitCode = Artisan::call('polyglot:import-lesson', [
            'path' => database_path('seeders/V2/Polyglot/Data/polyglot-there-is-there-are-a1.json'),
        ]);

        $output = Artisan::output();

        $test = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-there-is-there-are-a1')
            ->firstOrFail();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Polyglot lesson imported successfully.', $output);
        $this->assertSame('polyglot-there-is-there-are-a1', $test->slug);
        $this->assertCount(24, $test->questionLinks);
    }
}
