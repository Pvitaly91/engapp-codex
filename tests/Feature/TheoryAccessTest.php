<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use Illuminate\Support\Str;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class TheoryAccessTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config(['coming-soon.enabled' => false]);
        $this->rebuildComposeTestSchema();
    }

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
}
