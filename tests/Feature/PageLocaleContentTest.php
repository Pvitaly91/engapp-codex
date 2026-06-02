<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use Illuminate\Support\Str;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PageLocaleContentTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config(['coming-soon.enabled' => false]);
        $this->rebuildComposeTestSchema();
    }

    public function test_show_uses_preferred_locale_blocks_when_available(): void
    {
        app()->setLocale('en');
        $this->withSession(['locale' => 'en']);

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

        $response = $this->get('/en/pages/'.$category->slug.'/'.$page->slug);

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

}
