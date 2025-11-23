<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use Database\Seeders\PageTagAssignmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PageTagAssignmentSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run necessary migrations
        Artisan::call('migrate');
    }

    /** @test */
    public function seeder_prevents_duplicate_tags_on_repeated_runs(): void
    {
        // Create a test category
        $category = PageCategory::create([
            'title' => 'Часи',
            'slug' => 'tenses',
            'language' => 'uk',
        ]);

        // Create test tags
        $tag1 = Tag::create(['name' => 'Present Simple', 'category' => 'Tenses']);
        $tag2 = Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);
        $tag3 = Tag::create(['name' => 'Future Simple', 'category' => 'Tenses']);

        // Create a test page
        $page = Page::create([
            'title' => 'Present Simple — Теперішній простий час',
            'slug' => 'present-simple',
            'page_category_id' => $category->id,
        ]);

        // Run seeder first time
        $seeder = new PageTagAssignmentSeeder();
        
        // Mock the config files by creating them temporarily
        $this->createTestConfigFiles($category, $page);

        $seeder->run();

        // Check initial tag assignments
        $initialCategoryTags = $category->tags()->count();
        $initialPageTags = $page->tags()->count();

        $this->assertGreaterThan(0, $initialCategoryTags, 'Category should have tags after first run');
        $this->assertGreaterThan(0, $initialPageTags, 'Page should have tags after first run');

        // Run seeder second time
        $seeder->run();

        // Check that tag counts haven't increased (no duplicates)
        $secondCategoryTags = $category->tags()->count();
        $secondPageTags = $page->tags()->count();

        $this->assertEquals($initialCategoryTags, $secondCategoryTags, 'Category tags should not duplicate on second run');
        $this->assertEquals($initialPageTags, $secondPageTags, 'Page tags should not duplicate on second run');

        // Verify no duplicate entries in pivot tables
        $categoryTagDuplicates = \DB::table('page_category_tag')
            ->select('page_category_id', 'tag_id', \DB::raw('COUNT(*) as count'))
            ->groupBy('page_category_id', 'tag_id')
            ->having('count', '>', 1)
            ->get();

        $pageTagDuplicates = \DB::table('page_tag')
            ->select('page_id', 'tag_id', \DB::raw('COUNT(*) as count'))
            ->groupBy('page_id', 'tag_id')
            ->having('count', '>', 1)
            ->get();

        $this->assertCount(0, $categoryTagDuplicates, 'No duplicate category-tag relationships should exist');
        $this->assertCount(0, $pageTagDuplicates, 'No duplicate page-tag relationships should exist');

        // Clean up test config files
        $this->cleanupTestConfigFiles();
    }

    /** @test */
    public function seeder_assigns_general_tags_to_categories(): void
    {
        // Create a test category
        $category = PageCategory::create([
            'title' => 'Модальні дієслова',
            'slug' => 'modal-verbs',
            'language' => 'uk',
        ]);

        // Create modal verb tags
        Tag::create(['name' => 'Modal Verbs', 'category' => 'Modals']);
        Tag::create(['name' => 'Can / Could', 'category' => 'English Grammar Modal Pair']);
        Tag::create(['name' => 'Must / Have to', 'category' => 'English Grammar Modal Pair']);

        $this->createTestConfigFiles($category, null);

        $seeder = new PageTagAssignmentSeeder();
        $seeder->run();

        // Category should have general modal verb tags
        $this->assertGreaterThan(0, $category->tags()->count(), 'Category should have general tags assigned');

        $tagNames = $category->tags()->pluck('name')->toArray();
        $this->assertContains('Modal Verbs', $tagNames, 'Category should have general Modal Verbs tag');

        $this->cleanupTestConfigFiles();
    }

    /** @test */
    public function seeder_assigns_specific_tags_to_pages(): void
    {
        // Create a test category and page
        $category = PageCategory::create([
            'title' => 'Часи',
            'slug' => 'tenses',
            'language' => 'uk',
        ]);

        $page = Page::create([
            'title' => 'Present Simple — Теперішній простий час',
            'slug' => 'present-simple',
            'page_category_id' => $category->id,
        ]);

        // Create specific tags
        Tag::create(['name' => 'Present Simple', 'category' => 'Tenses']);
        Tag::create(['name' => 'Present Simple Do/Does Choice', 'category' => 'English Grammar Structure']);
        Tag::create(['name' => 'present_simple', 'category' => null]);

        $this->createTestConfigFiles($category, $page);

        $seeder = new PageTagAssignmentSeeder();
        $seeder->run();

        // Page should have specific detailed tags
        $this->assertGreaterThan(0, $page->tags()->count(), 'Page should have specific tags assigned');

        $tagNames = $page->tags()->pluck('name')->toArray();
        $this->assertContains('Present Simple', $tagNames, 'Page should have specific Present Simple tag');

        $this->cleanupTestConfigFiles();
    }

    /**
     * Create temporary test config files
     */
    private function createTestConfigFiles(?PageCategory $category, ?Page $page): void
    {
        $pagesData = [
            'exported_at' => now()->toIso8601String(),
            'total_categories' => $category ? 1 : 0,
            'total_pages' => $page ? 1 : 0,
            'categories' => []
        ];

        if ($category) {
            $categoryData = [
                'category_id' => $category->id,
                'category_title' => $category->title,
                'category_slug' => $category->slug,
                'category_language' => $category->language,
                'pages' => []
            ];

            if ($page) {
                $categoryData['pages'][] = [
                    'page_id' => $page->id,
                    'page_title' => $page->title,
                    'page_slug' => $page->slug,
                ];
            }

            $pagesData['categories'][] = $categoryData;
        }

        // Create pages config
        if (!is_dir(config_path('pages'))) {
            mkdir(config_path('pages'), 0755, true);
        }
        file_put_contents(
            config_path('pages/exported_pages.json'),
            json_encode($pagesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        // Create tags config
        $tags = Tag::all();
        $tagsData = [
            'exported_at' => now()->toIso8601String(),
            'total_categories' => 1,
            'total_tags' => $tags->count(),
            'categories' => [
                [
                    'category' => 'Test Category',
                    'tags' => $tags->map(function ($tag) {
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'category' => $tag->category,
                            'questions_count' => 0,
                            'pages_count' => 0,
                            'page_categories_count' => 0,
                            'created_at' => $tag->created_at->toIso8601String(),
                            'updated_at' => $tag->updated_at->toIso8601String(),
                        ];
                    })->toArray()
                ]
            ]
        ];

        if (!is_dir(config_path('tags'))) {
            mkdir(config_path('tags'), 0755, true);
        }
        file_put_contents(
            config_path('tags/exported_tags.json'),
            json_encode($tagsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Clean up temporary test config files
     */
    private function cleanupTestConfigFiles(): void
    {
        $pagesConfig = config_path('pages/exported_pages.json');
        $tagsConfig = config_path('tags/exported_tags.json');

        if (file_exists($pagesConfig)) {
            unlink($pagesConfig);
        }

        if (file_exists($tagsConfig)) {
            unlink($tagsConfig);
        }
    }
}
