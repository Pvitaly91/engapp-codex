<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\PageCategory;
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

    public function test_admin_can_view_page_v3_prompt_generator_page(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('page-v3-prompt-generator.index'));

        $response->assertOk();
        $response->assertSee('Page_V3 Prompt Generator');
        $response->assertSee('/admin/page-v3-prompt-generator');
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
        $response->assertSee('database/seeders/Page_V3/definitions/passive_voice_causative_theory.json');
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
            ]);

        $response->assertOk();
        $response->assertSee('Prompt for LLM JSON generation');
        $response->assertSee('Prompt for Codex seeder generation');
        $response->assertSee('Prompt буде згенеровано тільки на основі URL');
        $response->assertSee('database/seeders/Page_V3/definitions/types_of_questions_category.json');
        $response->assertSee('database/seeders/Page_V3/definitions/alternative_questions_theory.json');
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
}
