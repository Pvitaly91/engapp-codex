<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\PageCategory;
use App\Services\PolyglotV3PromptGeneratorService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class PolyglotV3PromptGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $viewsPath = storage_path('framework/views/' . uniqid('polyglot-v3-prompt-generator-', true));
        if (! is_dir($viewsPath)) {
            mkdir($viewsPath, 0777, true);
        }
        config(['view.compiled' => $viewsPath]);

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_categories');
        Schema::enableForeignKeyConstraints();

        Schema::create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('language', 8)->default('uk');
            $table->string('type')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_category_id')->nullable()->index();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('type')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });
    }

    public function test_admin_can_view_polyglot_v3_prompt_generator_page(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('polyglot-v3-prompt-generator.index'));

        $response->assertOk();
        $response->assertSee('Polyglot V3 Prompt Generator');
        $response->assertSee('/admin/polyglot-v3-prompt-generator');
        $response->assertSee('theory_category_slug');
        $response->assertSee('theory_page_slug');
        $response->assertSee('Seeder class base name');
    }

    public function test_theory_page_search_returns_page_metadata(): void
    {
        $page = $this->createTheoryPage();

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson(route('polyglot-v3-prompt-generator.theory-pages.search', [
                'q' => 'Verb To Be',
            ]));

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $page->id,
            'title' => 'Verb To Be Present',
            'slug' => 'verb-to-be-present',
        ]);
        $response->assertJsonFragment([
            'category_slug_path' => 'basic-grammar/verb-to-be',
        ]);
        $response->assertJsonFragment([
            'url' => 'https://gramlyze.com/theory/verb-to-be/verb-to-be-present',
        ]);
    }

    public function test_generation_validation_rejects_missing_and_invalid_input(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->from(route('polyglot-v3-prompt-generator.index'))
            ->post(route('polyglot-v3-prompt-generator.generate'), [
                'theory_page_id' => '',
                'lesson_slug' => '',
                'lesson_order' => 0,
                'lesson_title' => '',
                'topic' => '',
                'level' => 'Z9',
                'course_slug' => '',
                'previous_lesson_slug' => 'bad slug',
                'next_lesson_slug' => 'bad slug',
                'items_count' => 0,
                'seeder_class_base_name' => 'Bad Class Name',
                'prompt_id' => 'bad id',
            ]);

        $response->assertRedirect(route('polyglot-v3-prompt-generator.index'));
        $response->assertSessionHasErrors([
            'theory_page_id',
            'lesson_slug',
            'lesson_order',
            'lesson_title',
            'topic',
            'level',
            'course_slug',
            'items_count',
            'seeder_class_base_name',
            'prompt_id',
        ]);
    }

    public function test_successful_generation_renders_prompt_id_summary_prompt_text_and_theory_context(): void
    {
        $page = $this->createTheoryPage();
        $promptId = 'GLZ-PROMPT-TEST-001';
        $promptIdLine = 'CODEX PROMPT ID: '.$promptId;
        $summary = [
            'goal' => 'Створити канонічний V3 Polyglot lesson package для theory page "Verb To Be Present" у курсі polyglot-english-a1.',
            'work' => 'Згенерувати lesson package polyglot-verb-to-be-present-a1 (A1, 24 items) з V3 seeder package, definition.json, localizations/en|pl|uk, theory binding metadata і тестами.',
            'constraints' => 'Не додавати нові таблиці, не ламати compose/course/theory runtime і використовувати чинний prompt_generator.theory_page contract.',
            'result' => 'Готовий до seeding V3 Polyglot package, прив’язаний до /theory/verb-to-be/verb-to-be-present.',
        ];
        $summaryTopText = implode("\n", [
            'Codex Summary (Top):',
            $promptIdLine,
            '- Мета:',
            '  '.$summary['goal'],
            '- Що саме зробити:',
            '  '.$summary['work'],
            '- Ключові обмеження / адаптації:',
            '  '.$summary['constraints'],
            '- Підсумковий результат:',
            '  '.$summary['result'],
        ]);
        $summaryBottomText = implode("\n", [
            'Codex Summary (Bottom):',
            $promptIdLine,
            '- Мета:',
            '  '.$summary['goal'],
            '- Що саме зробити:',
            '  '.$summary['work'],
            '- Ключові обмеження / адаптації:',
            '  '.$summary['constraints'],
            '- Підсумковий результат:',
            '  '.$summary['result'],
        ]);
        $promptText = implode("\n", [
            $promptIdLine,
            '',
            $summaryTopText,
            '',
            'MAIN GOAL',
            'Implement the admin-facing Polyglot V3 prompt generator wrapper.',
            '',
            $summaryBottomText,
            '',
            $promptIdLine,
        ]);
        $generated = [
            'prompt_id' => $promptId,
            'prompt_text' => $promptText,
            'summary' => $summary,
            'meta' => [
                'lesson_slug' => 'polyglot-verb-to-be-present-a1',
                'lesson_order' => 3,
                'lesson_title' => 'Verb To Be Present',
                'topic' => 'verb to be',
                'course_slug' => 'polyglot-english-a1',
                'level' => 'A1',
                'items_count' => 24,
                'previous_lesson_slug' => 'polyglot-intro-a1',
                'next_lesson_slug' => 'polyglot-there-is-there-are-a1',
                'seeder_class_base_name' => 'PolyglotVerbToBePresentSeeder',
            ],
            'theory_context' => [
                'database_page_id' => $page->id,
                'page_title' => 'Verb To Be Present',
                'page_slug' => 'verb-to-be-present',
                'category_title_path' => 'Basic Grammar / Verb To Be',
                'category_slug_path' => 'basic-grammar/verb-to-be',
                'route_path' => '/theory/verb-to-be/verb-to-be-present',
                'route_url' => 'https://gramlyze.com/theory/verb-to-be/verb-to-be-present',
                'page_seeder_class' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder',
            ],
            'target_paths' => [
                'loader_relative_path' => 'database/seeders/V3/Polyglot/PolyglotVerbToBePresentSeeder.php',
                'real_seeder_relative_path' => 'database/seeders/V3/Polyglot/PolyglotVerbToBePresentSeeder/PolyglotVerbToBePresentSeeder.php',
                'definition_relative_path' => 'database/seeders/V3/Polyglot/PolyglotVerbToBePresentSeeder/definition.json',
                'uk_relative_path' => 'database/seeders/V3/Polyglot/PolyglotVerbToBePresentSeeder/localizations/uk.json',
                'en_relative_path' => 'database/seeders/V3/Polyglot/PolyglotVerbToBePresentSeeder/localizations/en.json',
                'pl_relative_path' => 'database/seeders/V3/Polyglot/PolyglotVerbToBePresentSeeder/localizations/pl.json',
            ],
        ];

        $this->mock(PolyglotV3PromptGeneratorService::class, function ($mock) use ($generated, $summaryTopText, $summaryBottomText) {
            $mock->shouldReceive('generate')
                ->once()
                ->with(Mockery::on(function (array $input): bool {
                    return $input['theory_category_slug'] === 'basic-grammar/verb-to-be'
                        && $input['theory_page_slug'] === 'verb-to-be-present'
                        && $input['lesson_slug'] === 'polyglot-verb-to-be-present-a1'
                        && $input['lesson_order'] === 3
                        && $input['lesson_title'] === 'Verb To Be Present'
                        && $input['topic'] === 'verb to be'
                        && $input['level'] === 'A1'
                        && $input['course_slug'] === 'polyglot-english-a1'
                        && $input['previous_lesson_slug'] === 'polyglot-intro-a1'
                        && $input['next_lesson_slug'] === 'polyglot-there-is-there-are-a1'
                        && $input['items_count'] === 24
                        && $input['seeder_class_base_name'] === 'PolyglotVerbToBePresentSeeder'
                        && $input['prompt_id'] === 'GLZ-PROMPT-TEST-001';
                }))
                ->andReturn($generated);
            $mock->shouldReceive('formatPromptIdLine')
                ->once()
                ->with('GLZ-PROMPT-TEST-001')
                ->andReturn('CODEX PROMPT ID: GLZ-PROMPT-TEST-001');
            $mock->shouldReceive('formatSummaryBlock')
                ->once()
                ->with('Top', 'GLZ-PROMPT-TEST-001', $generated['summary'])
                ->andReturn($summaryTopText);
            $mock->shouldReceive('formatSummaryBlock')
                ->once()
                ->with('Bottom', 'GLZ-PROMPT-TEST-001', $generated['summary'])
                ->andReturn($summaryBottomText);
        });

        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('polyglot-v3-prompt-generator.generate'), [
                'theory_page_id' => $page->id,
                'lesson_slug' => 'polyglot-verb-to-be-present-a1',
                'lesson_order' => 3,
                'lesson_title' => 'Verb To Be Present',
                'topic' => 'verb to be',
                'level' => 'A1',
                'course_slug' => 'polyglot-english-a1',
                'previous_lesson_slug' => 'polyglot-intro-a1',
                'next_lesson_slug' => 'polyglot-there-is-there-are-a1',
                'items_count' => 24,
                'seeder_class_base_name' => 'PolyglotVerbToBePresentSeeder',
                'prompt_id' => $promptId,
            ]);

        $response->assertOk();
        $response->assertSee('CODEX PROMPT ID (Top)', false);
        $response->assertSee('CODEX PROMPT ID (Bottom)', false);
        $response->assertSee('Summary (Top)');
        $response->assertSee('Summary (Bottom)');
        $response->assertSee($promptIdLine);
        $response->assertSee($summary['goal']);
        $response->assertSee($summary['result']);
        $response->assertSee('Verb To Be Present');
        $response->assertSee('basic-grammar/verb-to-be');
        $response->assertSee('https://gramlyze.com/theory/verb-to-be/verb-to-be-present');
        $response->assertSee('database/seeders/V3/Polyglot/PolyglotVerbToBePresentSeeder/definition.json');

        $content = str_replace("\r\n", "\n", $response->getContent());
        $this->assertMatchesRegularExpression(
            '/id="polyglot-prompt-id-top"[^>]*value="'.preg_quote(e($promptIdLine), '/').'"/s',
            $content
        );
        $this->assertMatchesRegularExpression(
            '/id="polyglot-prompt-id-bottom"[^>]*value="'.preg_quote(e($promptIdLine), '/').'"/s',
            $content
        );
        $this->assertStringContainsString(e($summaryTopText), $content);
        $this->assertStringContainsString(e($summaryBottomText), $content);
        $this->assertStringContainsString(e($promptText), $content);
        $this->assertGreaterThanOrEqual(6, substr_count($content, e($promptIdLine)));
        $this->assertGreaterThanOrEqual(2, substr_count($content, e($summary['goal'])));
        $this->assertGreaterThanOrEqual(1, substr_count($content, 'Codex Summary (Top):'));
        $this->assertGreaterThanOrEqual(1, substr_count($content, 'Codex Summary (Bottom):'));
    }

    private function createTheoryPage(): Page
    {
        $rootCategory = PageCategory::create([
            'title' => 'Basic Grammar',
            'slug' => 'basic-grammar',
            'language' => 'uk',
            'type' => 'theory',
            'seeder' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarCategorySeeder',
        ]);

        $category = PageCategory::create([
            'title' => 'Verb To Be',
            'slug' => 'verb-to-be',
            'language' => 'uk',
            'type' => 'theory',
            'parent_id' => $rootCategory->id,
            'seeder' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeCategorySeeder',
        ]);

        return Page::create([
            'title' => 'Verb To Be Present',
            'slug' => 'verb-to-be-present',
            'text' => 'Use the verb to be in the present simple to describe states, names, and identities.',
            'type' => 'theory',
            'seeder' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder',
            'page_category_id' => $category->id,
        ]);
    }
}
