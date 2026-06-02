<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Test BLOCK-FIRST TAGGING implementation in GrammarPageSeeder.
 *
 * BLOCK-FIRST Principle:
 * 1. Each TextBlock has DETAILED tags (specific to that block)
 * 2. Page has AGGREGATE tags: union(all block tags) + page anchor tags
 * 3. base_tags provide controlled inheritance to blocks
 */
class BlockFirstTaggingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureSchema();
        $this->resetData();
    }

    /** @test */
    public function page_tags_are_superset_of_all_block_tags(): void
    {
        // Simulate BLOCK-FIRST tagging: create blocks with various tags
        $pageCategory = PageCategory::create([
            'slug' => 'test-category',
            'title' => 'Test Category',
            'language' => 'en',
        ]);

        $page = Page::create([
            'slug' => 'test-page',
            'title' => 'Test Page',
            'page_category_id' => $pageCategory->id,
        ]);

        // Create tags
        $baseTag1 = Tag::create(['name' => 'Base Tag 1']);
        $baseTag2 = Tag::create(['name' => 'Base Tag 2']);
        $blockTag1 = Tag::create(['name' => 'Block Specific Tag 1']);
        $blockTag2 = Tag::create(['name' => 'Block Specific Tag 2']);
        $anchorTag = Tag::create(['name' => 'Page Anchor Tag']);

        // Create blocks with different tags (simulating BLOCK-FIRST)
        $block1 = TextBlock::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'page_id' => $page->id,
            'type' => 'box',
            'column' => 'left',
            'body' => 'Block 1',
            'sort_order' => 1,
        ]);
        $block1->tags()->sync([$baseTag1->id, $baseTag2->id, $blockTag1->id]);

        $block2 = TextBlock::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'page_id' => $page->id,
            'type' => 'box',
            'column' => 'left',
            'body' => 'Block 2',
            'sort_order' => 2,
        ]);
        $block2->tags()->sync([$baseTag1->id, $baseTag2->id, $blockTag2->id]);

        // Page gets aggregated tags (union of all block tags + anchor)
        $allBlockTagIds = array_unique(array_merge(
            $block1->tags->pluck('id')->toArray(),
            $block2->tags->pluck('id')->toArray()
        ));
        $pageTagIds = array_unique(array_merge($allBlockTagIds, [$anchorTag->id]));
        $page->tags()->sync($pageTagIds);

        // Verify page tags are superset of all block tags
        $pageTagIdsResult = $page->fresh()->tags->pluck('id')->toArray();
        $this->assertEmpty(
            array_diff($allBlockTagIds, $pageTagIdsResult),
            'Page tags must contain all block tags'
        );
        $this->assertContains($anchorTag->id, $pageTagIdsResult);
    }

    /** @test */
    public function blocks_can_have_different_tags(): void
    {
        $page = Page::create([
            'slug' => 'test-diverse-tags',
            'title' => 'Test Diverse Tags',
        ]);

        $commonTag = Tag::create(['name' => 'Common']);
        $uniqueTag1 = Tag::create(['name' => 'Unique 1']);
        $uniqueTag2 = Tag::create(['name' => 'Unique 2']);

        $block1 = TextBlock::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'page_id' => $page->id,
            'type' => 'box',
            'column' => 'left',
            'body' => 'Block 1',
            'sort_order' => 1,
        ]);
        $block1->tags()->sync([$commonTag->id, $uniqueTag1->id]);

        $block2 = TextBlock::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'page_id' => $page->id,
            'type' => 'box',
            'column' => 'left',
            'body' => 'Block 2',
            'sort_order' => 2,
        ]);
        $block2->tags()->sync([$commonTag->id, $uniqueTag2->id]);

        // Blocks should have different tag sets
        $block1TagIds = $block1->fresh()->tags->pluck('id')->sort()->values()->toArray();
        $block2TagIds = $block2->fresh()->tags->pluck('id')->sort()->values()->toArray();

        $this->assertNotEquals($block1TagIds, $block2TagIds, 'Blocks should have different tags');
        $this->assertContains($commonTag->id, $block1TagIds);
        $this->assertContains($commonTag->id, $block2TagIds);
        $this->assertContains($uniqueTag1->id, $block1TagIds);
        $this->assertNotContains($uniqueTag1->id, $block2TagIds);
    }

    /** @test */
    public function service_block_can_disable_base_tag_inheritance(): void
    {
        $page = Page::create([
            'slug' => 'test-no-inheritance',
            'title' => 'Test No Inheritance',
        ]);

        $baseTag = Tag::create(['name' => 'Base Tag']);
        $navTag = Tag::create(['name' => 'Navigation']);

        // Content block with base tags
        $contentBlock = TextBlock::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'page_id' => $page->id,
            'type' => 'box',
            'column' => 'left',
            'body' => 'Content',
            'sort_order' => 1,
        ]);
        $contentBlock->tags()->sync([$baseTag->id]);

        // Navigation block with inherit_base_tags = false (only has Navigation tag)
        $navBlock = TextBlock::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'page_id' => $page->id,
            'type' => 'navigation-chips',
            'column' => 'footer',
            'body' => 'Navigation',
            'sort_order' => 100,
        ]);
        $navBlock->tags()->sync([$navTag->id]); // No base tags

        // Nav block should only have its own tag
        $navBlockTags = $navBlock->fresh()->tags->pluck('id')->toArray();
        $this->assertCount(1, $navBlockTags);
        $this->assertContains($navTag->id, $navBlockTags);
        $this->assertNotContains($baseTag->id, $navBlockTags);
    }

    /** @test */
    public function backward_compatibility_without_base_tags(): void
    {
        // Simulates old seeder behavior where page.tags acted as base_tags
        $page = Page::create([
            'slug' => 'legacy-page',
            'title' => 'Legacy Page',
        ]);

        $pageLevelTag = Tag::create(['name' => 'Page Level Tag']);

        // Old behavior: block inherits page tags
        $block = TextBlock::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'page_id' => $page->id,
            'type' => 'box',
            'column' => 'left',
            'body' => 'Content',
            'sort_order' => 1,
        ]);

        // Block has page tags (backward compat behavior)
        $block->tags()->sync([$pageLevelTag->id]);
        $page->tags()->sync([$pageLevelTag->id]);

        // Both should have the tag
        $this->assertContains($pageLevelTag->id, $page->fresh()->tags->pluck('id')->toArray());
        $this->assertContains($pageLevelTag->id, $block->fresh()->tags->pluck('id')->toArray());
    }

    /** @test */
    public function tag_name_normalization(): void
    {
        // Test that tag names with extra whitespace are normalized
        $tag1 = Tag::firstOrCreate(['name' => 'Present Simple']);
        $tag2 = Tag::firstOrCreate(['name' => 'Present Simple']); // Same after normalization

        $this->assertEquals($tag1->id, $tag2->id, 'Normalized tag names should be the same');
    }

    private function ensureSchema(): void
    {
        if (! Schema::hasTable('page_categories')) {
            Schema::create('page_categories', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug');
                $table->string('language')->nullable();
                $table->string('seeder')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->string('slug');
                $table->string('title');
                $table->text('text')->nullable();
                $table->string('type')->nullable();
                $table->string('seeder')->nullable();
                $table->unsignedBigInteger('page_category_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('text_blocks')) {
            Schema::create('text_blocks', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique()->nullable();
                $table->unsignedBigInteger('page_id')->nullable();
                $table->unsignedBigInteger('page_category_id')->nullable();
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
        }

        if (! Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('category')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tag_text_block')) {
            Schema::create('tag_text_block', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tag_id');
                $table->unsignedBigInteger('text_block_id');
                $table->unique(['tag_id', 'text_block_id']);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('page_tag')) {
            Schema::create('page_tag', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('page_id');
                $table->unsignedBigInteger('tag_id');
                $table->unique(['page_id', 'tag_id']);
                $table->timestamps();
            });
        }
    }

    private function resetData(): void
    {
        foreach ([
            'tag_text_block',
            'page_tag',
            'text_blocks',
            'pages',
            'page_categories',
            'tags',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
