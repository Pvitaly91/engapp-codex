<?php

namespace Tests\Feature\Theory;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\SiteTreeItem;
use App\Models\SiteTreeVariant;
use App\Models\TextBlock;
use App\Modules\LanguageManager\Services\LocaleService;
use App\Services\TheoryMenuStore;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

final class TheoryMenuBuildCommandTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('native_name');
            $table->boolean('is_default');
            $table->boolean('is_active');
            $table->integer('sort_order');
        });
        DB::table('languages')->insert([
            ['code' => 'uk', 'name' => 'Ukrainian', 'native_name' => 'Українська', 'is_default' => 1, 'is_active' => 1, 'sort_order' => 1],
            ['code' => 'pl', 'name' => 'Polish', 'native_name' => 'Polski', 'is_default' => 0, 'is_active' => 1, 'sort_order' => 2],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_default' => 0, 'is_active' => 0, 'sort_order' => 3],
        ]);
        LocaleService::clearCache();
        config(['app.url' => 'http://gramlyze.loc', 'theory-menu.path' => storage_path('app/theory-menu-'.bin2hex(random_bytes(8)))]);
        app()->setLocale('uk');
    }

    protected function tearDown(): void
    {
        LocaleService::clearCache();
        parent::tearDown();
    }

    public function test_build_reads_fresh_tree_titles_and_order_without_touching_existing_cache_or_database(): void
    {
        $root = PageCategory::query()->create(['title' => 'Tenses', 'slug' => 'tenses', 'type' => 'theory', 'language' => 'uk']);
        $child = PageCategory::query()->create(['title' => 'Nested theory', 'slug' => 'nested', 'type' => 'theory', 'language' => 'uk', 'parent_id' => $root->id]);
        $first = Page::query()->create(['title' => 'Alpha lesson', 'slug' => 'alpha', 'type' => 'theory', 'page_category_id' => $child->id]);
        $second = Page::query()->create(['title' => 'Beta lesson', 'slug' => 'beta', 'type' => 'theory', 'page_category_id' => $child->id]);
        Page::query()->create(['title' => 'Foreign page', 'slug' => 'foreign', 'page_category_id' => $child->id]);
        $translation = TextBlock::query()->create(['page_id' => $first->id, 'locale' => 'pl', 'type' => 'subtitle',
            'body' => '<p><strong>Pierwsza lekcja</strong> — bez zapisywania osobistej treści.</p>']);
        $variant = SiteTreeVariant::query()->create(['name' => 'Base', 'slug' => 'base', 'is_base' => true]);
        $alpha = SiteTreeItem::query()->create(['variant_id' => $variant->id, 'title' => $first->title, 'linked_page_title' => $first->title, 'sort_order' => 1]);
        $beta = SiteTreeItem::query()->create(['variant_id' => $variant->id, 'title' => $second->title, 'linked_page_title' => $second->title, 'sort_order' => 2]);
        $catalogKey = 'page-controller:category-list:theory:theory:pl:uk:uk';
        $titles = [$root->title, $child->title, $first->title, $second->title];
        $orderingKey = 'page-controller:site-tree-ordering:'.md5(json_encode($titles, JSON_UNESCAPED_UNICODE));
        Cache::put($catalogKey, collect(), 3600);
        Cache::put($orderingKey, [$second->title => 0, $first->title => 99], 3600);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->artisan('theory:menu-build', ['--locale' => 'pl'])->assertSuccessful();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        self::assertNotEmpty($queries);
        foreach ($queries as $query) self::assertMatchesRegularExpression('/^\s*(select|pragma)\b/i', $query['query']);
        self::assertSame('uk', app()->getLocale());
        self::assertTrue(Cache::get($catalogKey)->isEmpty());
        self::assertSame([$second->title => 0, $first->title => 99], Cache::get($orderingKey));

        $store = app(TheoryMenuStore::class);
        $before = $store->check('development', 'pl');
        self::assertSame(['categories' => 2, 'lessons' => 2], $before['counts']);
        foreach (TheoryMenuStore::VARIANTS as $menuVariant) {
            $html = $store->read('development', 'pl', $menuVariant)['html'];
            self::assertStringContainsString('Pierwsza lekcja', $html);
            self::assertStringContainsString('/pl/theory/tenses/nested/alpha', $html);
            self::assertStringNotContainsString('Foreign page', $html);
            self::assertStringNotContainsString('bez zapisywania osobistej treści', $html);
            self::assertStringNotContainsString('gramlyze.loc', $html);
            self::assertStringNotContainsString('aria-current', $html);
            self::assertLessThan(strpos($html, '/pl/theory/tenses/nested/beta'), strpos($html, '/pl/theory/tenses/nested/alpha'));
        }
        $alpha->update(['sort_order' => 3]);
        $beta->update(['sort_order' => 1]);
        $translation->update(['body' => '<p><strong>Zmieniona lekcja</strong> — zaktualizowany opis.</p>']);
        $this->artisan('theory:menu-build', ['--locale' => 'pl'])->assertSuccessful();
        $after = $store->check('development', 'pl');
        self::assertNotSame($before['revision'], $after['revision']);
        foreach (TheoryMenuStore::VARIANTS as $menuVariant) {
            $html = $store->read('development', 'pl', $menuVariant)['html'];
            self::assertStringContainsString('Zmieniona lekcja', $html);
            self::assertLessThan(strpos($html, '/pl/theory/tenses/nested/alpha'), strpos($html, '/pl/theory/tenses/nested/beta'));
        }
        $this->artisan('theory:menu-build', ['--locale' => 'pl'])->assertSuccessful();
        self::assertSame($after['revision'], $store->check('development', 'pl')['revision']);
        self::assertCount(2, glob(config('theory-menu.path').'/development/pl/theory/activations/*.json'));
    }

    public function test_command_respects_enabled_locales_and_isolates_the_synthetic_production_mode(): void
    {
        $category = PageCategory::query()->create(['title' => 'Tenses', 'slug' => 'tenses', 'type' => 'theory', 'language' => 'uk']);
        Page::query()->create(['title' => 'A & B <sample>', 'slug' => 'one', 'type' => 'theory', 'page_category_id' => $category->id]);
        $store = app(TheoryMenuStore::class);
        $this->artisan('theory:menu-build', ['--locale' => 'en'])->assertFailed();
        $this->artisan('theory:menu-build')->assertSuccessful();
        self::assertNotNull($store->check('development', 'uk'));
        self::assertNotNull($store->check('development', 'pl'));
        self::assertNull($store->check('development', 'en'));

        // Only an in-process request object is created; there is no external HTTP or production DB access.
        config(['app.url' => 'https://production.invalid', 'site-mode.production_domains' => ['production.invalid'], 'site-mode.production_locales' => ['uk']]);
        $this->artisan('theory:menu-build', ['--locale' => 'pl'])->assertFailed();
        $this->artisan('theory:menu-build')->assertSuccessful();
        $production = $store->check('production', 'uk');
        self::assertNotSame($store->check('development', 'uk')['revision'], $production['revision']);
        self::assertNull($store->check('production', 'pl'));
        $html = $store->read('production', 'uk', 'desktop')['html'];
        self::assertStringContainsString('/theory/tenses/one', $html);
        self::assertStringContainsString('A &amp; B &lt;sample&gt;', $html);
        self::assertStringNotContainsString('production.invalid', $html);
    }
}
