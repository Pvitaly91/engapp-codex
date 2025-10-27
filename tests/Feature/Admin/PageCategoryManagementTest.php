<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PageCategoryManagementTest extends TestCase
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
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('language', 8)->default('uk');
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_category_id')
                ->nullable()
                ->constrained('page_categories')
                ->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('text')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('text_blocks');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_categories');
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->withSession($this->adminSession())
            ->post(route('pages.manage.categories.store'), [
                'title' => 'Grammar',
                'slug' => 'grammar',
                'language' => 'uk',
            ]);

        $response->assertRedirect(route('pages.manage.index', ['tab' => 'categories']));

        $this->assertDatabaseHas('page_categories', [
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $category = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);

        $response = $this->withSession($this->adminSession())
            ->put(route('pages.manage.categories.update', $category), [
                'title' => 'Updated Grammar',
                'slug' => 'updated-grammar',
                'language' => 'en',
            ]);

        $response->assertRedirect(route('pages.manage.index', ['tab' => 'categories']));

        $this->assertDatabaseHas('page_categories', [
            'id' => $category->id,
            'title' => 'Updated Grammar',
            'slug' => 'updated-grammar',
            'language' => 'en',
        ]);
    }

    public function test_admin_can_delete_category_and_detach_pages(): void
    {
        $category = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);

        $page = Page::create([
            'slug' => 'verbs',
            'title' => 'Verbs',
            'text' => '',
            'page_category_id' => $category->id,
        ]);

        $response = $this->withSession($this->adminSession())
            ->delete(route('pages.manage.categories.destroy', $category));

        $response->assertRedirect(route('pages.manage.index', ['tab' => 'categories']));

        $this->assertDatabaseMissing('page_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'page_category_id' => null,
        ]);
    }

    public function test_categories_tab_shows_page_counts(): void
    {
        $category = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);

        Page::create([
            'slug' => 'verbs',
            'title' => 'Verbs',
            'text' => '',
            'page_category_id' => $category->id,
        ]);

        Page::create([
            'slug' => 'nouns',
            'title' => 'Nouns',
            'text' => '',
            'page_category_id' => $category->id,
        ]);

        PageCategory::create([
            'title' => 'Vocabulary',
            'slug' => 'vocabulary',
            'language' => 'uk',
        ]);

        $response = $this->withSession($this->adminSession())
            ->get(route('pages.manage.index', ['tab' => 'categories']));

        $response->assertOk();

        $response->assertViewHas('categories', function ($categories) use ($category) {
            $found = $categories->firstWhere('id', $category->id);

            return $found && (int) $found->pages_count === 2;
        });
    }

    public function test_admin_can_delete_all_empty_categories(): void
    {
        $filled = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);

        $emptyOne = PageCategory::create([
            'title' => 'Vocabulary',
            'slug' => 'vocabulary',
            'language' => 'uk',
        ]);

        $emptyTwo = PageCategory::create([
            'title' => 'Listening',
            'slug' => 'listening',
            'language' => 'uk',
        ]);

        Page::create([
            'slug' => 'verbs',
            'title' => 'Verbs',
            'text' => '',
            'page_category_id' => $filled->id,
        ]);

        $response = $this->withSession($this->adminSession())
            ->delete(route('pages.manage.categories.destroy-empty'));

        $response->assertRedirect(route('pages.manage.index', ['tab' => 'categories']));

        $this->assertDatabaseHas('page_categories', ['id' => $filled->id]);
        $this->assertDatabaseMissing('page_categories', ['id' => $emptyOne->id]);
        $this->assertDatabaseMissing('page_categories', ['id' => $emptyTwo->id]);
    }

    private function adminSession(): array
    {
        return ['admin_authenticated' => true];
    }
}
