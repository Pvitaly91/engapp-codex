<?php

namespace Tests\Feature;

use App\Models\Question;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarConjunctionsTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarImperativesTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarSentenceStructureSvoTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarSentenceTypesTheorySeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\ConjunctionsAndButOrBecauseSoV3QuestionsOnlySeeder as Opus46ConjunctionsSeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\ImperativesNakazoviRecenniaV3QuestionsOnlySeeder as Opus46ImperativesSeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\SentenceStructureBudovaRecenniaSvoV3QuestionsOnlySeeder as Opus46SentenceStructureSeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\SentenceTypesVidiRecenV3QuestionsOnlySeeder as Opus46SentenceTypesSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\ConjunctionsAndButOrBecauseSoV3QuestionsOnlySeeder as SonateConjunctionsSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\ImperativesNakazoviRecenniaV3QuestionsOnlySeeder as SonateImperativesSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\SentenceStructureBudovaRecenniaSvoV3QuestionsOnlySeeder as SonateSentenceStructureSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\SentenceTypesVidiRecenV3QuestionsOnlySeeder as SonateSentenceTypesSeeder;
use Database\Seeders\V3\Polyglot\PolyglotConjunctionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotImperativesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSentenceStructureSvoAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSentenceTypesAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\BasicGrammarConjunctionsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\BasicGrammarImperativesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\BasicGrammarSentenceStructureSvoTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\BasicGrammarSentenceTypesTheoryLinksSeeder;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class TheoryPageTestsUnificationAuditCommandTest extends TestCase
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
        Question::flushEventListeners();
    }

    public function test_audit_command_classifies_seeded_page_v3_theory_pages_and_writes_artifacts(): void
    {
        $this->seed([
            BasicGrammarCategorySeeder::class,
            BasicGrammarSentenceStructureSvoTheorySeeder::class,
            BasicGrammarSentenceTypesTheorySeeder::class,
            BasicGrammarConjunctionsTheorySeeder::class,
            BasicGrammarImperativesTheorySeeder::class,
            SonateSentenceStructureSeeder::class,
            Opus46SentenceStructureSeeder::class,
            PolyglotSentenceStructureSvoAllLevelsLessonSeeder::class,
            SonateSentenceTypesSeeder::class,
            Opus46SentenceTypesSeeder::class,
            PolyglotSentenceTypesAllLevelsLessonSeeder::class,
            SonateConjunctionsSeeder::class,
            Opus46ConjunctionsSeeder::class,
            PolyglotConjunctionsAllLevelsLessonSeeder::class,
            SonateImperativesSeeder::class,
            Opus46ImperativesSeeder::class,
            PolyglotImperativesAllLevelsLessonSeeder::class,
            BasicGrammarSentenceStructureSvoTheoryLinksSeeder::class,
            BasicGrammarSentenceTypesTheoryLinksSeeder::class,
            BasicGrammarConjunctionsTheoryLinksSeeder::class,
            BasicGrammarImperativesTheoryLinksSeeder::class,
        ]);

        $jsonPath = storage_path('framework/testing/theory-page-tests-unification-audit.json');
        $markdownPath = storage_path('framework/testing/theory-page-tests-unification-audit.md');
        File::delete([$jsonPath, $markdownPath]);

        $this->artisan('theory-pages:audit-tests-unification', [
            '--json' => $jsonPath,
            '--md' => $markdownPath,
        ])->assertExitCode(0);

        $this->assertFileExists($jsonPath);
        $this->assertFileExists($markdownPath);

        $audit = json_decode((string) File::get($jsonPath), true);
        $this->assertIsArray($audit);
        $this->assertSame(4, $audit['summary']['total_theory_pages'] ?? null);
        $this->assertSame(4, $audit['summary']['pages_ok'] ?? null);

        $allowedStatuses = [
            'OK',
            'missing_sentence_builder',
            'missing_mixed_test',
            'missing_v3_questions',
            'missing_polyglot_questions',
            'missing_theory_links',
            'missing_polyglot_theory_links',
            'missing_all',
        ];

        foreach ($audit['pages'] as $page) {
            $this->assertContains($page['status'], $allowedStatuses, $page['route']);
            $this->assertArrayHasKey('missing', $page, $page['route']);
            $this->assertIsArray($page['missing'], $page['route']);
        }

        $routes = collect($audit['pages'])->keyBy('route');
        foreach ([
            '/theory/basic-grammar/sentence-structure-svo',
            '/theory/basic-grammar/sentence-types',
            '/theory/basic-grammar/basic-conjunctions-and-but-or-because-so',
            '/theory/basic-grammar/imperatives-sit-down-dont-open-it',
        ] as $route) {
            $this->assertSame('OK', $routes->get($route)['status'] ?? null, $route);
        }

        $markdown = File::get($markdownPath);
        $this->assertStringContainsString('| Route | Status | Has Sentence Builder | Has Mixed A1-C2 | V3 seeder | Polyglot seeder | Has theory links | Missing |', $markdown);
        $this->assertStringContainsString('## Backlog', $markdown);
    }
}
