<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class V3PromptGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $viewsPath = storage_path('framework/views');
        if (! is_dir($viewsPath)) {
            mkdir($viewsPath, 0777, true);
        }
        config(['view.compiled' => $viewsPath]);

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('text_blocks');
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

        Schema::create('text_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id')->nullable()->index();
            $table->unsignedBigInteger('page_category_id')->nullable();
            $table->string('locale', 8)->default('uk');
            $table->string('type')->nullable();
            $table->string('column')->nullable();
            $table->string('heading')->nullable();
            $table->string('css_class')->nullable();
            $table->unsignedInteger('sort_order')->default(10);
            $table->text('body')->nullable();
            $table->string('level')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });
    }

    public function test_admin_can_view_v3_prompt_generator_page(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('v3-prompt-generator.index'));

        $response->assertOk();
        $response->assertSee('V3 Prompt Generator');
        $response->assertSee('/admin/v3-prompt-generator');
        $response->assertSee('\u0022target_namespace\u0022:\u0022AI\u0022', false);
        $response->assertSee("targetNamespace: config.form.target_namespace || 'AI'", false);
        $response->assertSee("selectedNamespaceBase: 'AI'", false);
        $response->assertSee("namespacePresets: ['AI', 'AI\\\\ChatGpt', 'AI\\\\ChatGptPro', 'AI\\\\Gemini', 'AI\\\\Claude']", false);
        $response->assertSee('AI\\ChatGptPro');
        $response->assertSee('Additional folders inside selected namespace');
        $response->assertSee('Mode A1 / repository-connected');
        $response->assertSee('Mode A2 / no-repository fallback');
    }

    public function test_theory_page_search_returns_page_metadata(): void
    {
        $page = $this->createTheoryPage();

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson(route('v3-prompt-generator.theory-pages.search', [
                'q' => 'Passive',
                'site_domain' => 'gramlyze.com',
            ]));

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $page->id,
            'title' => 'Passive Voice Overview',
            'slug' => 'passive-voice-overview',
        ]);
        $response->assertJsonFragment([
            'resolved_seeder_class' => 'Database\\Seeders\\Page_v2\\Grammar\\PassiveVoiceSeeder',
        ]);
        $response->assertJsonFragment([
            'page_seeder_class' => 'Database\\Seeders\\Page_v2\\Grammar\\PassiveVoiceSeeder',
        ]);
        $response->assertJsonFragment([
            'url' => 'https://gramlyze.com/theory/voice/passive-voice-overview',
        ]);
    }

    public function test_generates_single_mode_prompt_from_theory_page(): void
    {
        $page = $this->createTheoryPage();

        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('v3-prompt-generator.generate'), [
                'source_type' => 'theory_page',
                'theory_page_id' => $page->id,
                'site_domain' => 'gramlyze.com',
                'target_namespace' => 'AI\\ChatGptPro',
                'levels' => ['A1', 'B1'],
                'questions_per_level' => 4,
                'generation_mode' => 'single',
            ]);

        $response->assertOk();
        $response->assertSee('Prompt for Codex');
        $response->assertSee('Passive Voice Overview');
        $response->assertSee('PassiveVoiceOverviewV3QuestionsOnlySeeder');
        $response->assertSee('database/seeders/V3/AI/ChatGptPro/PassiveVoiceOverviewV3QuestionsOnlySeeder/definition.json');
        $response->assertSee('database/seeders/V3/AI/ChatGptPro/PassiveVoiceOverviewV3QuestionsOnlySeeder/localizations/uk.json');
        $response->assertSee('database/seeders/V3/AI/ChatGptPro/PassiveVoiceOverviewV3QuestionsOnlySeeder/localizations/en.json');
        $response->assertSee('database/seeders/V3/AI/ChatGptPro/PassiveVoiceOverviewV3QuestionsOnlySeeder/localizations/pl.json');
        $response->assertSee('https://gramlyze.com/theory/voice/passive-voice-overview');
        $response->assertSee('prompt_generator');
        $response->assertSee('theory_page_id');
        $response->assertSee((string) $page->id);
        $response->assertSee('theory_page_ids');
        $response->assertSee('page_seeder_class');
        $response->assertSee('Database\\Seeders\\Page_v2\\Grammar\\PassiveVoiceSeeder');
        $response->assertSee('saved_test.uuid');
        $response->assertSee('at most 36 characters');
        $response->assertSee('saved_test.slug');
        $response->assertSee('must be unique across repository V3 definitions');
        $response->assertSee('saved_test.question_uuids references questions that were not seeded.');
        $response->assertSee('Never invent `saved_test.question_uuids` from planned numbering');
        $response->assertSee('omit `saved_test.question_uuids` entirely and let the loader use the seeded question UUID order automatically');
        $response->assertSee('keep the top-level PHP file as a compatibility loader stub');
        $response->assertSee('Use package-local localization JSON files under `localizations/uk.json`, `localizations/en.json`, and `localizations/pl.json`');
        $response->assertSee('`target.definition_path` pointed at `../definition.json`');
        $response->assertSee('Keep every learner-facing `question` and every `variants` entry in English only.');
        $response->assertSee('Make Ukrainian `hints` and `explanations` genuinely instructional');
        $response->assertSee('does not mean omitting teaching feedback');
        $response->assertSee('Write at least two Ukrainian `hints` for every question');
        $response->assertSee('Do not use vague clue phrasing like');
        $response->assertSee('A1: 4 question(s)');
        $response->assertSee('B1: 4 question(s)');
    }

    public function test_supports_nested_ai_namespace_targets(): void
    {
        $page = $this->createTheoryPage();

        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('v3-prompt-generator.generate'), [
                'source_type' => 'theory_page',
                'theory_page_id' => $page->id,
                'site_domain' => 'gramlyze.com',
                'target_namespace' => 'AI\\ChatGptPro\\Grammar\\PluralNouns',
                'levels' => ['A2'],
                'questions_per_level' => 3,
                'generation_mode' => 'single',
            ]);

        $response->assertOk();
        $response->assertSee('Database\\Seeders\\V3\\AI\\ChatGptPro\\Grammar\\PluralNouns\\PassiveVoiceOverviewV3QuestionsOnlySeeder');
        $response->assertSee('database/seeders/V3/AI/ChatGptPro/Grammar/PluralNouns/PassiveVoiceOverviewV3QuestionsOnlySeeder/definition.json');
    }

    public function test_generates_split_mode_prompts_for_external_url_even_when_fetch_fails(): void
    {
        Http::fake([
            '*' => Http::response('Server error', 500),
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('v3-prompt-generator.generate'), [
                'source_type' => 'external_url',
                'external_url' => 'https://93.184.216.34/grammar/passive-voice',
                'target_namespace' => 'AI\\ChatGptPro',
                'levels' => ['B2'],
                'questions_per_level' => 3,
                'generation_mode' => 'split',
                'prompt_a_mode' => 'no_repository',
            ]);

        $response->assertOk();
        $response->assertSee('Prompt for LLM JSON generation');
        $response->assertSee('Prompt for Codex seeder generation');
        $response->assertSee('Prompt буде згенеровано тільки на основі URL');
        $response->assertSee('Passive Voice');
        $response->assertSee('downloadable `.json` file');
        $response->assertSee('uploaded filename may be arbitrary');
        $response->assertSee('Selected Prompt A mode: Mode A2 / no-repository fallback');
        $response->assertSee('This prompt must work without a connected repository. Do not assume live repo access.');
        $response->assertSee('Embedded compatibility reference');
        $response->assertSee('Neighbor reference filenames embedded for offline use');
        $response->assertSee('filters.seeder_classes');
        $response->assertSee('saved_test.uuid');
        $response->assertSee('at most 36 characters');
        $response->assertSee('saved_test.slug');
        $response->assertSee('must be unique across repository V3 definitions');
        $response->assertSee('saved_test.question_uuids references questions that were not seeded.');
        $response->assertSee('Never invent `saved_test.question_uuids` from planned numbering');
        $response->assertSee('omit `saved_test.question_uuids` entirely. The loader will fall back to the seeded question UUID order automatically');
        $response->assertSee('validate any incoming `saved_test.question_uuids` against the actual question UUIDs that will be seeded');
        $response->assertSee('Write every `question` and every `variants` entry in English only.');
        $response->assertSee('include detailed `localizations.uk.hints` and `localizations.uk.explanations` in this JSON');
        $response->assertSee('Make `localizations.uk.explanations` more detailed for every option');
        $response->assertSee('Planned seeder package folder');
        $response->assertSee('Planned localization path (uk): `database/seeders/V3/AI/ChatGptPro/PassiveVoiceV3QuestionsOnlySeeder/localizations/uk.json`');
        $response->assertSee('keep these files inside the same seeder package under `localizations/<locale>.json`');
        $response->assertSee('create or enrich the companion `localizations/uk.json` file inside the seeder package');
        $response->assertSee('Avoid generic repeated templates like');
        $response->assertSee('database/seeders/V3/AI/ChatGptPro/PassiveVoiceV3QuestionsOnlySeeder/definition.json');
    }

    public function test_rejects_unsafe_external_url_hosts(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->from(route('v3-prompt-generator.index'))
            ->post(route('v3-prompt-generator.generate'), [
                'source_type' => 'external_url',
                'external_url' => 'http://127.0.0.1/internal/theory',
                'target_namespace' => 'AI\\ChatGptPro',
                'levels' => ['A2'],
                'questions_per_level' => 2,
                'generation_mode' => 'split',
            ]);

        $response->assertRedirect(route('v3-prompt-generator.index'));
        $response->assertSessionHasErrors(['external_url']);
    }

    public function test_validation_requires_levels_and_positive_question_count(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->from(route('v3-prompt-generator.index'))
            ->post(route('v3-prompt-generator.generate'), [
                'source_type' => 'manual_topic',
                'manual_topic' => 'Plural nouns',
                'target_namespace' => 'AI\\ChatGptPro',
                'levels' => [],
                'questions_per_level' => 0,
                'generation_mode' => 'single',
            ]);

        $response->assertRedirect(route('v3-prompt-generator.index'));
        $response->assertSessionHasErrors(['levels', 'questions_per_level']);
    }

    private function createTheoryPage(): Page
    {
        $category = PageCategory::create([
            'title' => 'Grammar / Voice',
            'slug' => 'voice',
            'language' => 'uk',
            'type' => 'theory',
            'seeder' => 'Database\\Seeders\\Page_v2\\Grammar\\PassiveVoiceSeeder',
        ]);

        $page = Page::create([
            'title' => 'Passive Voice Overview',
            'slug' => 'passive-voice-overview',
            'text' => 'Passive voice is used when the action is more important than the doer.',
            'type' => 'theory',
            'seeder' => 'Database\\Seeders\\Page_v2\\Grammar\\PassiveVoiceSeeder',
            'page_category_id' => $category->id,
        ]);

        TextBlock::create([
            'page_id' => $page->id,
            'locale' => 'uk',
            'type' => 'box',
            'heading' => 'When to use passive voice',
            'body' => 'Use passive voice for process descriptions, formal tone, and unknown agents.',
            'sort_order' => 10,
        ]);

        return $page;
    }
}
