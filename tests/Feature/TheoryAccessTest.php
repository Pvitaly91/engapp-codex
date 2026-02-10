<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TheoryAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_theory_index(): void
    {
        $category = PageCategory::create([
            'title' => 'Theory',
            'slug' => 'theory-category',
            'language' => 'uk',
            'type' => 'theory',
        ]);

        $page = Page::create([
            'slug' => 'sample-theory',
            'title' => 'Sample Theory',
            'text' => '',
            'type' => 'theory',
            'page_category_id' => $category->id,
        ]);

        TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'page_id' => $page->id,
            'page_category_id' => $category->id,
            'locale' => 'uk',
            'type' => 'subtitle',
            'column' => 'left',
            'body' => '{}',
            'sort_order' => 0,
        ]);

        $response = $this->get('/theory');

        $response->assertOk();
    }

    public function test_category_page_shows_only_category_level_text_blocks(): void
    {
        $category = PageCategory::create([
            'title' => 'Grammar Category',
            'slug' => 'grammar-category',
            'language' => 'uk',
            'type' => 'theory',
        ]);

        $page = Page::create([
            'slug' => 'sample-page',
            'title' => 'Sample Page',
            'text' => '',
            'type' => 'theory',
            'page_category_id' => $category->id,
        ]);

        // Create a category-level text block (page_id is NULL)
        $categoryBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'page_id' => null,
            'page_category_id' => $category->id,
            'locale' => 'uk',
            'type' => 'box',
            'column' => 'left',
            'body' => 'This is a category description block',
            'sort_order' => 0,
        ]);

        // Create a page-level text block (page_id is set)
        $pageBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'page_id' => $page->id,
            'page_category_id' => $category->id,
            'locale' => 'uk',
            'type' => 'box',
            'column' => 'left',
            'body' => 'This is a page-specific block',
            'sort_order' => 0,
        ]);

        $response = $this->get('/theory/grammar-category');

        $response->assertOk();
        // Category page should show category-level blocks
        $response->assertSee('This is a category description block', false);
        // Category page should NOT show page-specific blocks
        $response->assertDontSee('This is a page-specific block', false);
    }
}
