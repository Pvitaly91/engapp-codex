<?php

namespace Tests\Feature;

use App\Http\Controllers\TestJsV2Controller;
use App\Models\Page;
use App\Models\Question;
use App\Models\TextBlock;
use App\Services\TheoryCourseManifestService;
use App\Support\M26DetailPackage;
use App\Support\M69AdjectivesAdverbs as M69;
use App\Support\TheoryContentSource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Actual current M69/shared PHP/Blade/controller/service; no historical controller fixture and no invented old subtitle. */
class M69CurrentRuntimeCompatibilityTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); app()->setLocale('uk'); $this->withoutVite();
        self::assertSame('testing', app()->environment());
        self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
    }

    private function blocks(array $target, array $definition): Collection
    {
        $hasSubtitle = array_key_exists('subtitle_html', $definition['page']); $configs = [];
        $i = array_search($target['identity'], M69::OWNERS, true);
        self::assertNotFalse($i);
        if ($hasSubtitle) {
            $configs[0] = ['type'=>'subtitle', 'column'=>'header', 'body'=>$definition['page']['subtitle_html'],
                'level'=>$definition['page']['subtitle_level'] ?? null, 'uuid_key'=>$definition['page']['subtitle_uuid_key'] ?? 'subtitle'];
        } else {
            self::assertSame(2, $i); self::assertArrayNotHasKey('subtitle_text', $definition['page']);
            self::assertSame('captured-absent', M69::BEFORE_SUBTITLES[$i]['mode']);
        }
        foreach ($definition['page']['blocks'] as $slot=>$config) {
            if (!$hasSubtitle) { $config['uuid'] = M69::EDING_CONTINUING_UUIDS[$slot]; }
            $configs[$slot + 1] = $config;
        }
        $blocks = [];
        foreach ($configs as $position => $config) {
            $block = new TextBlock;
            $block->setRawAttributes(['id' => 6800000 + $target['page_binding']['id'] * 100 + $position,
                'uuid' => M26DetailPackage::uuid($target['identity'], $config, $position),
                'page_id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
                'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $position,
                'type' => $config['type'], 'column' => $config['column'] ?? null, 'level' => $config['level'] ?? null,
                'heading' => $config['heading'] ?? null, 'css_class' => $config['css_class'] ?? null,
                'body' => $config['body'], 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'], true);
            $block->setRelation('tags', new Collection); $block->setRelation('page', null); $blocks[] = $block;
        }

        return new Collection($blocks);
    }

    public function test_current_source_and_universal_view_resolve_all_three_m69_owners_with_real_native_vectors_without_mutation(): void
    {
        foreach ([M69::class] as $package) {
            [$before, $patch, $master] = $package::load();
            self::assertSame([11, 6, 6], array_map(static fn ($t) => count($t['before']['page']['blocks']), $before['targets']));
            self::assertSame([0, 1, 0], array_map(static fn ($l) => count($l['details']), $master['lessons']));
            foreach ($patch['targets'] as $target) {
                $ownerIndex = array_search($target['identity'], M69::OWNERS, true);
                self::assertSame(M69::CONTINUING_LEVELS[$ownerIndex], array_map(static fn ($b) => $b['level'] ?? null,
                    $before['targets'][$ownerIndex]['before']['page']['blocks']), 'All original null/mixed technical levels stay exact.');
                $blocks = $this->blocks($target, $target['after']);
                foreach ($blocks as $block) {
                    $attributes = $block->getAttributes();
                    if ($block->type === 'subtitle') { continue; }
                    $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
                    $expected = $package::presentation($block, $data);
                    self::assertNotNull($expected, $target['identity'].':'.$block->sort_order);
                    $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
                    if (in_array($block->type, ['hero', 'navigation-chips'], true)) {
                        // theory.show owns these auxiliaries outside the content-block loop.
                        self::assertNull($resolved['view']); self::assertNull($resolved['presentation']);
                    } else { self::assertSame($expected, $resolved['presentation']); }
                    self::assertNull(TheoryContentSource::resolve($block)['presentation']);
                    if ($package !== M69::class) {
                        self::assertNull(M69::presentation($block, $data), 'Foreign accepted owner remains foreign to M69.');
                    } else { self::assertSame($expected, M69::presentation($block, $data)); }
                    if (!in_array($block->type, ['hero', 'navigation-chips', 'practice-set'], true)) {
                        app('view')->flushState();
                        $html = view('theory.partials.content-block', ['block' => $block, 'theoryCanonical' => true,
                            'm42StyleContext' => true, 'practiceQuestions' => collect(), 'lessonLinks' => []])->render();
                        self::assertStringContainsString('theory.content:node', $html);
                        self::assertStringNotContainsString('m69-section', $html);
                    }
                    self::assertSame($attributes, $block->getAttributes());
                }
                $this->assertActualPageAuxiliaries($target, $blocks);
            }
        }
    }

    private function assertActualPageAuxiliaries(array $target, Collection $blocks): void
    {
        $page = new Page;
        $page->setRawAttributes(['id' => $target['page_binding']['id'],
            'page_category_id' => $target['page_binding']['page_category_id'], 'type' => 'theory',
            'seeder' => $target['identity'], 'slug' => $target['slug'], 'title' => $target['after']['page']['title'],
            'text' => $target['after']['page']['subtitle_text']], true);
        $page->setRelation('textBlocks', $blocks); $page->setRelation('tags', new Collection); $page->setRelation('category', null);
        $attributes = $page->getAttributes(); $snapshot = $blocks->map(static fn ($block) => $block->getAttributes())->all();
        app('view')->flushState();
        $html = view('theory.show', ['page' => $page, 'categories' => collect(), 'categoryPages' => collect(),
            'topicTests' => collect(), 'selectedCategory' => null, 'practiceQuestionsByBlock' => []])->render();
        $plain = static fn ($text) => trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $text = $plain($html); $hero = json_decode($blocks->firstWhere('type', 'hero')->body, true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(1, substr_count($html, 'class="theory-hero"'));
        self::assertStringContainsString($plain($hero['intro']), $text);
        foreach ($hero['rules'] as $rule) {
            foreach (['label', 'text', 'example'] as $field) {
                self::assertStringContainsString($plain($rule[$field]), $text, 'Actual theory.show hero '.$field);
            }
        }
        $navigation = json_decode($blocks->firstWhere('type', 'navigation-chips')->body, true, flags: JSON_THROW_ON_ERROR);
        self::assertStringContainsString($plain($navigation['title']), $text);
        foreach ($navigation['items'] as $item) {
            self::assertStringContainsString($plain($item['label']), $text);
            if (empty($item['current'])) { self::assertStringContainsString('href="'.e($item['url']).'"', $html); }
        }
        self::assertSame($attributes, $page->getAttributes());
        self::assertSame($snapshot, $blocks->map(static fn ($block) => $block->getAttributes())->all());
    }

    public function test_current_standalone_controller_reconstructs_before_for_all_three_m69_owners_including_missing_subtitle_on_clones(): void
    {
        $controller = (new \ReflectionClass(TestJsV2Controller::class))->newInstanceWithoutConstructor();
        $serialize = new \ReflectionMethod(TestJsV2Controller::class, 'resolveTheoryBlocksForQuestion');
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        foreach ([M69::class] as $package) {
            [$before, $patch] = $package::load();
            foreach ($patch['targets'] as $i => $target) {
                $old = $this->blocks($target, $before['targets'][$i]['before']); $after = $this->blocks($target, $target['after']);
                $question = new Question; $question->setRawAttributes(['id' => 6800000 + $i, 'theory_text_block_uuid' => null], true);
                $question->setRelation('theoryTextBlocks', $old); $expected = $serialize->invoke($controller, $question);
                $question->setRelation('theoryTextBlocks', $after);
                $snapshot = $after->map(static fn ($block) => $block->getAttributes())->all();
                foreach (['uk', 'en', 'pl'] as $locale) {
                    app()->setLocale($locale); self::assertSame($expected, $serialize->invoke($controller, $question));
                }
                app()->setLocale('uk');
                self::assertSame($snapshot, $after->map(static fn ($block) => $block->getAttributes())->all());
                foreach ($after as $block) {
                    $restored = $package::preserveExcludedTextBlock($block, 'standalone_test');
                    if (!$old->contains('uuid', $block->uuid)) {
                        self::assertNull($restored, 'Every exact new practice and the independently absent old subtitle are excluded.');
                    } else {
                        self::assertNotNull($restored);
                        self::assertSame($old->firstWhere('uuid', $block->uuid)->getAttributes(), $restored->getAttributes(),
                            'Current standalone boundary restores literal OLD body AND metadata on clones.');
                    }
                    self::assertSame($block, $package::preserveExcludedTextBlock($block, 'theory'));
                    foreach (['locale' => 'en', 'seeder' => 'ForeignOwner', 'page_id' => 999999,
                        'page_category_id' => 999999, 'uuid' => 'foreign', 'body' => $block->body."\n"] as $field => $value) {
                        $foreign = clone $block; $foreign->$field = $value;
                        self::assertSame($foreign, $package::preserveExcludedTextBlock($foreign, 'standalone_test'), $field);
                    }
                }
            }
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_current_course_service_reconstructs_all_three_m69_before_values_including_null_page275_text_without_db_writes(): void
    {
        $this->rebuildComposeTestSchema();
        foreach ([[52, null, 'prykmetniky-ta-pryslinknyky']] as [$id, $parent, $slug]) {
            DB::table('page_categories')->insert(['id' => $id, 'parent_id' => $parent, 'slug' => $slug,
                'title' => $slug, 'type' => 'theory', 'language' => 'uk']);
        }
        $expected = [];
        foreach ([M69::class] as $package) {
            [$before, $patch] = $package::load();
            foreach ($patch['targets'] as $i => $target) {
                $id = $target['page_binding']['id']; $old = $before['targets'][$i]['before'];
                DB::table('pages')->insert(['id' => $id, 'page_category_id' => $target['page_binding']['page_category_id'],
                    'type' => 'theory', 'seeder' => $target['identity'], 'slug' => $target['slug'],
                    'title' => $old['page']['title'], 'text' => $target['after']['page']['subtitle_text']]);
                foreach ($this->blocks($target, $target['after']) as $block) { DB::table('text_blocks')->insert($block->getAttributes()); }
                $expected[$id] = ['text' => $old['page']['subtitle_text'] ?? null,
                    'bodies' => $this->blocks($target, $old)->pluck('body', 'uuid')->all(),
                    'metadata' => $this->blocks($target, $old)->mapWithKeys(fn ($block) => [$block->uuid => $this->metadata($block)])->all()];
            }
        }
        $pages = DB::table('pages')->orderBy('id')->get()->map(static fn ($row) => (array) $row)->all();
        $blocks = DB::table('text_blocks')->orderBy('id')->get()->map(static fn ($row) => (array) $row)->all();
        $service = app(TheoryCourseManifestService::class);
        foreach ($expected as $id => $old) {
            $page = $service->resolvePageForLesson(['page_id' => $id]); self::assertInstanceOf(Page::class, $page);
            self::assertSame($old['text'], $page->text);
            self::assertSame($old['bodies'], $page->textBlocks->where('locale', 'uk')->pluck('body', 'uuid')->all());
            self::assertSame($old['metadata'], $page->textBlocks->where('locale', 'uk')->mapWithKeys(fn ($block) => [$block->uuid => $this->metadata($block)])->all());
        }
        self::assertSame($pages, DB::table('pages')->orderBy('id')->get()->map(static fn ($row) => (array) $row)->all());
        self::assertSame($blocks, DB::table('text_blocks')->orderBy('id')->get()->map(static fn ($row) => (array) $row)->all());
    }

    private function metadata(TextBlock $block): array
    {
        // Compare all six exact metadata fields independently of Eloquent hydration key order.
        return ['type' => $block->type, 'column' => $block->column, 'heading' => $block->heading,
            'level' => $block->level, 'css_class' => $block->css_class, 'sort_order' => $block->sort_order];
    }
}
