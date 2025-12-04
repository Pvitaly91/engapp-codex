<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PageExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $viewsPath = storage_path('framework/views');
        if (!is_dir($viewsPath)) {
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
            $table->timestamps();
            
            $table->foreign('parent_id')
                ->references('id')
                ->on('page_categories')
                ->nullOnDelete();
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

        // Clean up any existing export file
        $exportPath = config_path('pages/exported_pages.json');
        if (file_exists($exportPath)) {
            unlink($exportPath);
        }
    }

    protected function tearDown(): void
    {
        // Clean up export file after test
        $exportPath = config_path('pages/exported_pages.json');
        if (file_exists($exportPath)) {
            unlink($exportPath);
        }

        $exportDir = config_path('pages');
        if (is_dir($exportDir)) {
            rmdir($exportDir);
        }

        parent::tearDown();
    }

    public function test_export_creates_json_file_with_correct_structure(): void
    {
        // Create test data
        $category = PageCategory::create([
            'title' => 'Test Category',
            'slug' => 'test-category',
            'language' => 'uk',
        ]);

        Page::create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'text' => 'Test content',
            'page_category_id' => $category->id,
        ]);

        // Make export request
        $response = $this->post(route('pages.manage.export'));

        // Assert redirect with success message
        $response->assertRedirect(route('pages.manage.index'));
        $response->assertSessionHas('status');

        // Assert file was created
        $exportPath = config_path('pages/exported_pages.json');
        $this->assertFileExists($exportPath);

        // Assert JSON structure
        $jsonContent = file_get_contents($exportPath);
        $jsonData = json_decode($jsonContent, true);

        $this->assertIsArray($jsonData);
        $this->assertArrayHasKey('exported_at', $jsonData);
        $this->assertArrayHasKey('total_categories', $jsonData);
        $this->assertArrayHasKey('total_pages', $jsonData);
        $this->assertArrayHasKey('categories', $jsonData);

        $this->assertEquals(1, $jsonData['total_categories']);
        $this->assertEquals(1, $jsonData['total_pages']);

        // Assert category data
        $this->assertCount(1, $jsonData['categories']);
        $categoryData = $jsonData['categories'][0];

        $this->assertEquals($category->id, $categoryData['category_id']);
        $this->assertEquals('Test Category', $categoryData['category_title']);
        $this->assertEquals('test-category', $categoryData['category_slug']);
        $this->assertEquals('uk', $categoryData['category_language']);

        // Assert page data
        $this->assertCount(1, $categoryData['pages']);
        $pageData = $categoryData['pages'][0];

        $this->assertArrayHasKey('page_id', $pageData);
        $this->assertEquals('Test Page', $pageData['page_title']);
        $this->assertEquals('test-page', $pageData['page_slug']);
    }

    public function test_view_export_displays_json_content(): void
    {
        // Create test data and export
        $category = PageCategory::create([
            'title' => 'Test Category',
            'slug' => 'test-category',
            'language' => 'uk',
        ]);

        Page::create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'text' => 'Test content',
            'page_category_id' => $category->id,
        ]);

        $this->post(route('pages.manage.export'));

        // View export
        $response = $this->get(route('pages.manage.export.view'));

        $response->assertStatus(200);
        $response->assertViewIs('page-manager::view-export');
        $response->assertViewHas('jsonContent');
        $response->assertViewHas('jsonData');
        $response->assertViewHas('filePath');
        $response->assertViewHas('fileSize');
        $response->assertViewHas('lastModified');
    }

    public function test_download_export_returns_file(): void
    {
        // Create test data and export
        $category = PageCategory::create([
            'title' => 'Test Category',
            'slug' => 'test-category',
            'language' => 'uk',
        ]);

        Page::create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'text' => 'Test content',
            'page_category_id' => $category->id,
        ]);

        $this->post(route('pages.manage.export'));

        // Download export
        $response = $this->get(route('pages.manage.export.download'));

        $response->assertStatus(200);
        $response->assertDownload('exported_pages.json');
    }

    public function test_view_export_without_file_redirects_with_error(): void
    {
        $response = $this->get(route('pages.manage.export.view'));

        $response->assertRedirect(route('pages.manage.index'));
        $response->assertSessionHas('error');
    }

    public function test_download_export_without_file_redirects_with_error(): void
    {
        $response = $this->get(route('pages.manage.export.download'));

        $response->assertRedirect(route('pages.manage.index'));
        $response->assertSessionHas('error');
    }
}
