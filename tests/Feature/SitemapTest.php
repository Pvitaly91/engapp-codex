<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('page_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('language')->nullable();
            $table->string('type')->nullable();
            $table->foreignId('parent_id')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
            $table->string('title');
            $table->longText('text')->nullable();
            $table->string('type')->nullable();
            $table->string('seeder')->nullable();
            $table->foreignId('page_category_id')->nullable();
            $table->timestamps();
        });
    }

    public function test_sitemap_contains_canonical_theory_urls_from_production_origin(): void
    {
        config(['site-mode.production_origin' => 'https://gramlyze.com/']);

        $root = PageCategory::create([
            'title' => 'Майбутні форми',
            'slug' => 'maibutni-formy',
            'language' => 'uk',
            'type' => 'theory',
        ]);
        $child = PageCategory::create([
            'title' => 'Future Simple',
            'slug' => 'future-simple',
            'language' => 'uk',
            'type' => 'theory',
            'parent_id' => $root->id,
        ]);
        Page::create([
            'title' => 'Forms and Use',
            'slug' => 'future-simple-forms',
            'type' => 'theory',
            'page_category_id' => $child->id,
        ]);

        $response = $this->get('http://gramlyze.ub/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('https://gramlyze.com/theory', false);
        $response->assertSee('https://gramlyze.com/theory/maibutni-formy', false);
        $response->assertSee(
            'https://gramlyze.com/theory/maibutni-formy/future-simple/future-simple-forms',
            false
        );
        $response->assertDontSee('gramlyze.ub/theory', false);
    }

    public function test_sitemap_excludes_non_theory_and_non_ukrainian_content(): void
    {
        PageCategory::create([
            'title' => 'English theory',
            'slug' => 'english-theory',
            'language' => 'en',
            'type' => 'theory',
        ]);
        PageCategory::create([
            'title' => 'Other content',
            'slug' => 'other-content',
            'language' => 'uk',
            'type' => 'pages',
        ]);

        $this->get('http://gramlyze.com/sitemap.xml')
            ->assertOk()
            ->assertDontSee('english-theory', false)
            ->assertDontSee('other-content', false);
    }
}
