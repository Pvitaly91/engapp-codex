<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PageLocaleContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpContentTables();
    }

    public function test_show_uses_preferred_locale_blocks_when_available(): void
    {
        app()->setLocale('en');

        $category = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'en',
        ]);

        $page = Page::create([
            'title' => 'Conditionals',
            'slug' => 'conditionals',
            'page_category_id' => $category->id,
        ]);

        TextBlock::create([
            'uuid' => Str::uuid(),
            'page_id' => $page->id,
            'locale' => 'en',
            'type' => 'subtitle',
            'column' => 'left',
            'sort_order' => 1,
            'body' => 'English subtitle',
        ]);

        TextBlock::create([
            'uuid' => Str::uuid(),
            'page_id' => $page->id,
            'locale' => 'en',
            'type' => 'text',
            'column' => 'left',
            'sort_order' => 2,
            'body' => 'English text block',
        ]);

        TextBlock::create([
            'uuid' => Str::uuid(),
            'page_id' => $page->id,
            'locale' => 'uk',
            'type' => 'text',
            'column' => 'right',
            'sort_order' => 3,
            'body' => 'Ukrainian text block',
        ]);

        $response = $this->get(route('pages.show', [$category->slug, $page->slug]));

        $response->assertOk();
        $response->assertViewHas('locale', 'en');
        $response->assertViewHas('subtitleBlock', fn ($block) => $block?->locale === 'en');
        $response->assertViewHas('columns', function ($columns) {
            return $columns['left']->count() === 2
                && $columns['left']->every(fn ($block) => $block->locale === 'en')
                && $columns['right']->isEmpty();
        });
    }

    public function test_show_falls_back_to_default_locale_when_preferred_missing(): void
    {
        app()->setLocale('en');

        $category = PageCategory::create([
            'title' => 'Tenses',
            'slug' => 'tenses',
            'language' => 'uk',
        ]);

        $page = Page::create([
            'title' => 'Past Simple',
            'slug' => 'past-simple',
            'page_category_id' => $category->id,
        ]);

        TextBlock::create([
            'uuid' => Str::uuid(),
            'page_id' => $page->id,
            'locale' => 'uk',
            'type' => 'subtitle',
            'column' => 'left',
            'sort_order' => 1,
            'body' => 'Ukrainian subtitle',
        ]);

        TextBlock::create([
            'uuid' => Str::uuid(),
            'page_id' => $page->id,
            'locale' => 'uk',
            'type' => 'text',
            'column' => 'right',
            'sort_order' => 2,
            'body' => 'Ukrainian text block',
        ]);

        $response = $this->get(route('pages.show', [$category->slug, $page->slug]));

        $response->assertOk();
        $response->assertViewHas('locale', 'uk');
        $response->assertViewHas('columns', function ($columns) {
            return $columns['left']->count() === 1
                && $columns['right']->count() === 1
                && $columns['left']->first()->locale === 'uk'
                && $columns['right']->first()->locale === 'uk';
        });
    }

    private function setUpContentTables(): void
    {
        Schema::dropIfExists('tag_text_block');
        Schema::dropIfExists('page_category_tag');
        Schema::dropIfExists('page_tag');
        Schema::dropIfExists('text_blocks');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_categories');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_tag');

        Schema::create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('language')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('type')->nullable();
            $table->string('seeder')->nullable();
            $table->foreignId('page_category_id');
            $table->timestamps();
        });

        Schema::create('text_blocks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->foreignId('page_id')->nullable();
            $table->foreignId('page_category_id')->nullable();
            $table->string('locale')->nullable();
            $table->string('type')->nullable();
            $table->string('column')->nullable();
            $table->string('heading')->nullable();
            $table->string('css_class')->nullable();
            $table->integer('sort_order')->default(0);
            $table->text('body')->nullable();
            $table->string('level')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('page_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('page_id');
            $table->timestamps();
        });

        Schema::create('page_category_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('page_category_id');
            $table->timestamps();
        });

        Schema::create('tag_text_block', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('text_block_id');
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->text('question')->nullable();
            $table->string('level')->nullable();
            $table->unsignedTinyInteger('flag')->nullable();
            $table->timestamps();
        });

        Schema::create('question_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('tag_id');
            $table->timestamps();
        });
    }
}
