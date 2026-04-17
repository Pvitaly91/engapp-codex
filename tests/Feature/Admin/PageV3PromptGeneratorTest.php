<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Modules\LanguageManager\Services\LocaleService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PageV3PromptGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $viewsPath = storage_path('framework/views');
        if (! is_dir($viewsPath)) {
            mkdir($viewsPath, 0777, true);
        }

        config([
            'view.compiled' => $viewsPath,
            'app.locale' => 'uk',
            'app.supported_locales' => ['uk', 'en', 'pl'],
            'language-manager.fallback_locale' => 'uk',
        ]);

        app()->setLocale('uk');
        LocaleService::clearCache();

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
            $table->uuid('uuid')->nullable();
            $table->unsignedBigInteger('page_id')->nullable()->index();
            $table->unsignedBigInteger('page_category_id')->nullable()->index();
            $table->string('locale', 12)->default('uk');
            $table->string('type')->nullable();
            $table->string('column')->nullable();
            $table->string('heading')->nullable();
            $table->string('css_class')->nullable();
            $table->integer('sort_order')->default(0);
            $table->longText('body')->nullable();
            $table->string('level')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });
    }

    public function test_admin_can_view_page_v3_prompt_generator_page(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('page-v3-prompt-generator.index'));

        $response->assertOk();
        $response->assertSee('Page_V3 Prompt Generator');
        $response->assertSee('/admin/page-v3-prompt-generator');
        $response->assertSee('Mode A1 / repository-connected');
        $response->assertSee('Mode A2 / no-repository fallback');
    }

    public function test_guest_cannot_access_page_v3_prompt_generator_page(): void
    {
        $response = $this->get(route('page-v3-prompt-generator.index'));

        $response->assertRedirect(route('login.show'));
    }

    public function test_theory_variant_mode_renders_on_existing_screen(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('page-v3-prompt-generator.index'));

        $response->assertOk();
        $response->assertSee('Theory Variant Prompt');
        $response->assertSee('Source URL input');
        $response->assertSee('Target type');
        $response->assertSee('Generate Theory Variant Prompt');
    }

    public function test_generates_single_mode_prompt_for_existing_category(): void
    {
        $category = $this->createTheoryCategory([
            'title' => 'Passive Voice',
            'slug' => 'passive-voice',
            'seeder' => 'Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveVoiceCategorySeeder',
        ]);

        Page::create([
            'title' => 'Existing passive voice page',
            'slug' => 'existing-passive-voice-page',
            'type' => 'theory',
            'page_category_id' => $category->id,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('page-v3-prompt-generator.generate'), [
                'source_type' => 'manual_topic',
                'manual_topic' => 'Passive Voice Causative',
                'category_mode' => 'existing',
                'existing_category_id' => $category->id,
                'generation_mode' => 'single',
            ]);

        $response->assertOk();
        $response->assertSee('Prompt for Codex');
        $response->assertSee('Passive Voice Causative');
        $response->assertSee('Passive Voice');
        $response->assertSee('Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveVoiceCategorySeeder');
        $response->assertSee('database/seeders/Page_V3/PassiveVoice/PassiveVoiceCausativeTheorySeeder.php');
        $response->assertSee('database/seeders/Page_V3/PassiveVoice/PassiveVoiceCausativeTheorySeeder/definition.json');
    }

    public function test_generates_split_mode_prompts_for_new_category_from_external_url_even_when_fetch_fails(): void
    {
        Http::fake([
            '*' => Http::response('Server error', 500),
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('page-v3-prompt-generator.generate'), [
                'source_type' => 'external_url',
                'external_url' => 'https://93.184.216.34/grammar/alternative-questions',
                'category_mode' => 'new',
                'new_category_title' => 'Types of Questions',
                'generation_mode' => 'split',
                'prompt_a_mode' => 'repository_connected',
            ]);

        $response->assertOk();
        $response->assertSee('Prompt for LLM JSON generation');
        $response->assertSee('Prompt for Codex seeder generation');
        $response->assertSee('Prompt буде згенеровано тільки на основі URL');
        $response->assertSee('separate downloadable `.json` file');
        $response->assertSee('Attachment filenames may be arbitrary');
        $response->assertSee('Selected Prompt A mode: Mode A1 / repository-connected');
        $response->assertSee('This prompt assumes the repository is connected. Inspect the real Page_V3 files first and follow the live project contract.');
        $response->assertSee('Primary live repository references to inspect before generating JSON:');
        $response->assertSee('database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsCategorySeeder/definition.json');
        $response->assertSee('database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder/definition.json');
    }

    public function test_ai_category_mode_includes_current_category_catalog(): void
    {
        $this->createTheoryCategory([
            'title' => 'Word Order',
            'slug' => 'word-order',
            'seeder' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\WordOrderCategorySeeder',
        ]);
        $this->createTheoryCategory([
            'title' => 'Questions Negations',
            'slug' => 'questions-negations',
            'seeder' => 'Database\\Seeders\\Page_V3\\QuestionsNegations\\QuestionsNegationsCategorySeeder',
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('page-v3-prompt-generator.generate'), [
                'source_type' => 'manual_topic',
                'manual_topic' => 'Alternative Questions',
                'category_mode' => 'ai_select',
                'generation_mode' => 'single',
            ]);

        $response->assertOk();
        $response->assertSee('Current theory category catalog');
        $response->assertSee('slug=word-order');
        $response->assertSee('slug=questions-negations');
    }

    public function test_rejects_unsafe_external_url_hosts(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->from(route('page-v3-prompt-generator.index'))
            ->post(route('page-v3-prompt-generator.generate'), [
                'source_type' => 'external_url',
                'external_url' => 'http://127.0.0.1/internal/theory',
                'category_mode' => 'ai_select',
                'generation_mode' => 'split',
            ]);

        $response->assertRedirect(route('page-v3-prompt-generator.index'));
        $response->assertSessionHasErrors(['external_url']);
    }

    public function test_generates_theory_variant_prompt_for_existing_page(): void
    {
        $category = $this->createTheoryCategory([
            'title' => 'Tenses',
            'slug' => 'tenses',
        ]);
        $page = $this->createTheoryPage($category, [
            'title' => 'Present Simple',
            'slug' => 'present-simple',
            'seeder' => 'Database\\Seeders\\Page_V3\\Tenses\\PresentSimpleTheorySeeder',
        ]);

        $this->createTextBlock([
            'page_id' => $page->id,
            'page_category_id' => $category->id,
            'locale' => 'en',
            'type' => 'subtitle',
            'column' => 'left',
            'sort_order' => 5,
            'body' => '<p>Present Simple explains habits and routines.</p>',
        ]);
        $this->createTextBlock([
            'page_id' => $page->id,
            'page_category_id' => $category->id,
            'locale' => 'en',
            'type' => 'box',
            'column' => 'left',
            'heading' => 'Main uses',
            'sort_order' => 10,
            'body' => '<p>Use it for habits, routines, and general truths.</p>',
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('page-v3-prompt-generator.generate'), $this->theoryVariantPayload([
                'target_type' => 'page',
                'target_category_slug' => 'tenses',
                'target_page_slug' => 'present-simple',
                'locale' => 'en',
                'namespace' => 'Database\\Seeders\\Page_v2\\Variants\\Tenses',
                'class_name' => 'PresentSimpleEnV1Seeder',
                'variant_key' => 'generated-en-v1',
                'label' => 'Generated EN v1',
                'provider' => 'Claude',
                'model' => 'Sonnet',
                'prompt_version' => 'v1',
                'target_learner_levels' => 'A2-B1',
                'tone' => 'teacher-friendly',
                'rewrite_goal' => 'fresh alternative explanation',
                'content_strategy' => 'keep structure, simplify wording',
                'must_cover_list' => "- habits\n- routines",
                'avoid_list' => '- copying the original wording',
                'editor_notes' => 'Focus on practical classroom explanations.',
            ]));

        $response->assertOk();
        $response->assertSee('Theory variant prompt generated.');
        $response->assertSee('Copy prompt');
        $response->assertSee('prompt-theory-variant');
        $response->assertSee('Do not output JsonTestSeeder');
        $response->assertSee('protected function targetPageSlug(): ?string');
        $response->assertSee('protected function status(): string');
        $response->assertSee('App\\Support\\TheoryVariantPayloadSanitizer::sanitizePayload()');
        $response->assertSee('namespace: Database\\Seeders\\Page_v2\\Variants\\Tenses');
        $response->assertSee('class_name: PresentSimpleEnV1Seeder');
        $response->assertSee('target_type: page');
        $response->assertSee('target_category_slug: tenses');
        $response->assertSee('target_page_slug: present-simple');
        $response->assertSee('locale: en');
        $response->assertSee('variant_key: generated-en-v1');
        $response->assertSee('label: Generated EN v1');
        $response->assertSee('source_locale_requested: en');
        $response->assertSee('source_locale_used: en');
        $response->assertSee('Present Simple');
        $response->assertSee('habits and routines');
        $response->assertSee('Use it for habits, routines, and general truths.');
    }

    public function test_generates_theory_variant_prompt_for_existing_category_from_source_url(): void
    {
        $category = $this->createTheoryCategory([
            'title' => 'Conditionals',
            'slug' => 'conditionals',
            'seeder' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsCategorySeeder',
        ]);

        $this->createTheoryPage($category, [
            'title' => 'Zero Conditional',
            'slug' => 'zero-conditional',
        ]);
        $this->createTheoryPage($category, [
            'title' => 'First Conditional',
            'slug' => 'first-conditional',
        ]);

        $this->createTextBlock([
            'page_category_id' => $category->id,
            'locale' => 'uk',
            'type' => 'subtitle',
            'column' => 'left',
            'sort_order' => 5,
            'body' => '<p>Conditionals describe real and hypothetical results.</p>',
        ]);
        $this->createTextBlock([
            'page_category_id' => $category->id,
            'locale' => 'uk',
            'type' => 'box',
            'column' => 'left',
            'heading' => 'Overview',
            'sort_order' => 10,
            'body' => '<p>Use this category to compare zero, first, second, and third conditionals.</p>',
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('page-v3-prompt-generator.generate'), $this->theoryVariantPayload([
                'source_lookup_url' => '/theory/conditionals',
                'locale' => 'uk',
                'namespace' => 'Database\\Seeders\\Page_v2\\Variants\\Conditionals',
                'class_name' => 'ConditionalsUkV1Seeder',
                'variant_key' => 'generated-uk-v1',
                'label' => 'Generated UK v1',
                'prompt_version' => 'v1',
                'output_mode_preference' => 'fenced_php_code_block',
            ]));

        $response->assertOk();
        $response->assertSee('Theory Variant Prompt');
        $response->assertSee('target_type: category');
        $response->assertSee('target_category_slug: conditionals');
        $response->assertSee('target_page_slug: null');
        $response->assertSee('class_name: ConditionalsUkV1Seeder');
        $response->assertSee('source_url: /theory/conditionals');
        $response->assertSee('Conditionals');
        $response->assertSee('Zero Conditional');
        $response->assertSee('First Conditional');
        $response->assertSee('Use this category to compare zero, first, second, and third conditionals.');
        $response->assertSee('Prefer exactly one fenced `php` code block and nothing else.');
    }

    public function test_theory_variant_prompt_uses_locale_fallback_and_escapes_output_html(): void
    {
        $category = $this->createTheoryCategory([
            'title' => 'Articles',
            'slug' => 'articles',
        ]);
        $page = $this->createTheoryPage($category, [
            'title' => 'Definite Article',
            'slug' => 'definite-article',
        ]);

        $this->createTextBlock([
            'page_id' => $page->id,
            'page_category_id' => $category->id,
            'locale' => 'uk',
            'type' => 'subtitle',
            'column' => 'left',
            'sort_order' => 5,
            'body' => '<script>alert(1)</script><p>The definite article points to something specific.</p>',
        ]);
        $this->createTextBlock([
            'page_id' => $page->id,
            'page_category_id' => $category->id,
            'locale' => 'uk',
            'type' => 'box',
            'column' => 'left',
            'heading' => 'Specific reference',
            'sort_order' => 10,
            'body' => '<script>alert(1)</script><p>Use "the" when both speaker and listener know the noun.</p>',
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('page-v3-prompt-generator.generate'), $this->theoryVariantPayload([
                'target_type' => 'page',
                'target_category_slug' => 'articles',
                'target_page_slug' => 'definite-article',
                'locale' => 'en',
                'namespace' => 'Database\\Seeders\\Page_v2\\Variants\\Articles',
                'class_name' => 'DefiniteArticleEnV1Seeder',
                'variant_key' => 'generated-en-v1',
                'label' => 'Generated EN v1',
                'prompt_version' => 'v1',
            ]));

        $response->assertOk();
        $response->assertSee('source_locale_requested: en');
        $response->assertSee('source_locale_used: uk');
        $response->assertSee('Loaded source excerpt');
        $response->assertSee('Copy prompt');
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_theory_variant_prompt_uses_runtime_localized_titles_for_source_identity(): void
    {
        $category = $this->createTheoryCategory([
            'title' => 'Base Tenses',
            'slug' => 'tenses',
        ]);
        $page = $this->createTheoryPage($category, [
            'title' => 'Base Present Simple',
            'slug' => 'present-simple',
        ]);

        $this->createTextBlock([
            'page_category_id' => $category->id,
            'locale' => 'en',
            'type' => 'subtitle',
            'column' => 'left',
            'sort_order' => 5,
            'body' => '<p><strong>Tenses</strong> describe time relations in grammar.</p>',
        ]);
        $this->createTextBlock([
            'page_id' => $page->id,
            'page_category_id' => $category->id,
            'locale' => 'en',
            'type' => 'subtitle',
            'column' => 'left',
            'sort_order' => 5,
            'body' => '<p><strong>Present Simple</strong> is used for habits and routines.</p>',
        ]);
        $this->createTextBlock([
            'page_id' => $page->id,
            'page_category_id' => $category->id,
            'locale' => 'en',
            'type' => 'box',
            'column' => 'left',
            'heading' => 'Usage',
            'sort_order' => 10,
            'body' => '<p>Use it for habits and general truths.</p>',
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('page-v3-prompt-generator.generate'), $this->theoryVariantPayload([
                'target_type' => 'page',
                'target_category_slug' => 'tenses',
                'target_page_slug' => 'present-simple',
                'locale' => 'en',
                'namespace' => 'Database\\Seeders\\Page_v2\\Variants\\Tenses',
                'class_name' => 'PresentSimpleEnV1Seeder',
                'variant_key' => 'generated-en-v1',
                'label' => 'Generated EN v1',
                'prompt_version' => 'v1',
            ]));

        $response->assertOk();
        $response->assertSee('source_page_title: Present Simple');
        $response->assertSee('source_category_title: Tenses');
        $response->assertSee('"title": "Present Simple"');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createTheoryCategory(array $attributes): PageCategory
    {
        return PageCategory::create(array_merge([
            'title' => 'Basic Grammar',
            'slug' => 'basic-grammar',
            'language' => 'uk',
            'type' => 'theory',
            'seeder' => null,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createTheoryPage(PageCategory $category, array $attributes): Page
    {
        return Page::create(array_merge([
            'title' => 'Theory Page',
            'slug' => 'theory-page',
            'type' => 'theory',
            'seeder' => null,
            'page_category_id' => $category->id,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createTextBlock(array $attributes): TextBlock
    {
        return TextBlock::create(array_merge([
            'uuid' => null,
            'page_id' => null,
            'page_category_id' => null,
            'locale' => 'uk',
            'type' => 'box',
            'column' => 'left',
            'heading' => null,
            'css_class' => null,
            'sort_order' => 10,
            'body' => '<p>Body</p>',
            'level' => null,
            'seeder' => null,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function theoryVariantPayload(array $overrides = []): array
    {
        return array_merge([
            'generator_mode' => 'theory_variant',
            'locale' => 'uk',
            'namespace' => 'Database\\Seeders\\Page_v2\\Variants\\Theory',
            'class_name' => 'TheoryUkV1Seeder',
            'variant_key' => 'generated-uk-v1',
            'label' => 'Generated UK v1',
            'provider' => null,
            'model' => null,
            'prompt_version' => 'v1',
            'output_mode_preference' => 'downloadable_php_file',
        ], $overrides);
    }
}
