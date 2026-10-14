<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Independent mixed-level fixture: no working SQL, HTTP, source mirror or seeder. */
class TheoryNativeContentReleaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); app()->setLocale('uk');
        self::assertSame('testing', app()->environment());
        self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default'));
        self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
    }

    private function fixture(): array
    {
        $owner = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\ExampleTheorySeeder';
        $path = 'database/seeders/Page_V3/PassiveVoice/Basics/ExampleTheorySeeder/definition.json';
        $bank = 'Database\\Seeders\\V3\\Polyglot\\ExampleLessonSeeder';
        $base = str_repeat('a', 40); $url = 'http://gramlyze.loc/theory/passive-voice/example';
        $beforeDefinition = ['schema_version' => 1, 'content_type' => 'page', 'type' => 'theory', 'slug' => 'example',
            'seeder' => ['class' => $owner], 'page' => ['locale' => 'uk', 'title' => 'Raw title', 'tags' => ['Passive'],
                'category' => ['slug' => 'passive-voice', 'title' => 'Пасивний стан', 'language' => 'uk'],
                'subtitle_html' => '<p><strong>Погоджений заголовок</strong> Старе пояснення.</p>',
                'subtitle_text' => 'Старе пояснення.', 'blocks' => [
                    ['type' => 'hero', 'column' => 'header', 'level' => 'B1', 'body' => '{}'],
                    ['type' => 'usage-panels', 'column' => 'left', 'level' => 'B2', 'uuid_key' => 'accepted-usage', 'body' => '{}'],
                    ['type' => 'navigation-chips', 'column' => 'footer', 'body' => '{}'],
                ]]];
        $tasks = [];
        foreach (['selects', 'choices', 'inputs'] as $g => $group) {
            $tasks[$group] = [];
            foreach ([0, 1] as $n) {
                $answer = $group === 'choices' ? 'a' : ($group === 'inputs' ? 'I had it repaired.' : 'had');
                $options = $group === 'choices' ? ['a', 'b'] : ($group === 'inputs' ? [$answer] : ['had', 'has']);
                $feedback = []; foreach ($options as $option) { $feedback[mb_strtolower($option)] = 'I had it repaired. — Мені це полагодили.'; }
                $task = ['id' => 'fixture-'.$group.'-'.$n, 'source_index' => $g * 2 + $n + 1,
                    'answer' => $answer, 'accepted' => [$answer], 'feedback' => $feedback];
                if ($group === 'selects') { $task['options'] = $options; }
                if ($group === 'inputs') { $task += ['before' => 'repaired. / had / I / it', 'after' => 'Мені це полагодили. Почни з I.']; }
                $tasks[$group][] = $task;
            }
        }
        $practice = $tasks + ['choice_options' => ['a', 'b'], 'linked_practice' => [
            'source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => [$bank]]];
        $lesson = ['identity' => $owner, 'slug' => 'example', 'locale' => 'uk', 'technical_block_level' => 'B1',
            'display_level' => 'B1–B2', 'subtitle_html' => '<p><strong>Погоджений заголовок</strong> Нове пояснення.</p>',
            'subtitle_text' => 'Нове пояснення.', 'blocks' => [
                ['key' => 'hero', 'existing_source_index' => 0, 'type' => 'hero', 'column' => 'header', 'level' => 'B1',
                    'data' => ['level' => 'B1–B2', 'intro' => 'Модель послуги.', 'rules' => []]],
                ['key' => 'usage', 'existing_source_index' => 1, 'type' => 'usage-panels', 'column' => 'left', 'level' => 'B2',
                    'data' => ['title' => 'Окремий зміст', 'sections' => [['label' => 'Організована дія', 'color' => 'emerald',
                        'description' => 'Виконавець не збігається з організатором.',
                        'examples' => [['en' => 'I had it repaired.', 'ua' => 'Мені це полагодили.']]]]]],
                ['key' => 'navigation', 'existing_source_index' => 2, 'type' => 'navigation-chips', 'column' => 'footer',
                    'data' => ['title' => 'Уроки', 'items' => [['label' => 'Сусідній урок', 'url' => '/theory/passive-voice/neighbor']]]],
                ['key' => 'practice', 'existing_source_index' => null, 'type' => 'practice-set', 'column' => 'left', 'level' => 'B1', 'data' => $practice],
            ], 'details' => [['key' => 'service-roles', 'title' => 'Докладніше', 'owner_block_key' => 'usage',
                'owner_pointer' => '/sections/0', 'fragment' => ['type' => 'usage-panels', 'data' => ['sections' => [[
                    'label' => 'Виконавець і організатор', 'color' => 'slate',
                    'description' => 'Ця форма описує організацію роботи. Контекст має пояснювати, хто її виконав, щоб не змішувати ролі учасників.',
                    'examples' => [['en' => 'The mechanic repaired it for me.', 'ua' => 'Механік полагодив це для мене.']],
                ]]]]]]];
        $profile = ['label' => 'Fixture', 'base_sha' => $base, 'version' => '1.0.0', 'marker' => 'fixture_native_v1',
            'detail_marker' => 'fixture_detail_key', 'uuid_prefix' => 'fixture:', 'design' => 'fixture-native-v1',
            'artifacts' => [], 'bindings' => [['identity' => $owner, 'slug' => 'example', 'page_id' => 345,
                'category_id' => 1, 'ancestry' => ['passive-voice'], 'local_url' => $url, 'practice_level' => 'B1',
                'display_level' => 'B1–B2', 'rendered_title' => 'Погоджений заголовок', 'continuing_levels' => ['B1', 'B2', null]]]];
        $release = new TheoryNativeContentRelease($profile);
        $before = ['schema_version' => 1, 'base_sha' => $base, 'targets' => [['identity' => $owner, 'path' => $path, 'before' => $beforeDefinition]]];
        $master = ['schema_version' => 1, 'base_sha' => $base, 'version' => '1.0.0', 'lessons' => [$lesson]];
        $contract = ['schema_version' => 1, 'targets' => [['identity' => $owner, 'definition' => $path,
            'local_url' => $url, 'level' => 'B1', 'bank_seeders' => [$bank]]]];
        $patch = ['schema_version' => 1, 'base_sha' => $base, 'targets' => [['identity' => $owner, 'path' => $path,
            'slug' => 'example', 'ancestry' => ['passive-voice'], 'page_binding' => ['id' => 345, 'page_category_id' => 1],
            'local_url' => $url, 'block_keys' => array_column($lesson['blocks'], 'key'), 'after' => $release->definition($lesson, $beforeDefinition)]]];
        return [$release, $profile, $before, $patch, $master, $contract];
    }

    private function reject(callable $call): void
    {
        try { $call(); self::fail('Expected a closed-source refusal.'); }
        catch (RuntimeException $error) { self::assertNotSame('', $error->getMessage()); }
    }

    public function test_mixed_display_and_slot_levels_are_preserved_by_the_shared_serializer(): void
    {
        [$release, , $before, $patch, $master, $contract] = $this->fixture();
        $release->validate($before, $patch, $master, $contract);
        self::assertSame(['B1', 'B2', null, 'B1'], array_map(static fn ($b) => $b['level'] ?? null, $patch['targets'][0]['after']['page']['blocks']));
        self::assertSame('B1–B2', json_decode($patch['targets'][0]['after']['page']['blocks'][0]['body'], true)['level']);
        foreach ($before['targets'][0]['before']['page']['blocks'] as $slot => $old) {
            $new = $patch['targets'][0]['after']['page']['blocks'][$slot]; unset($old['body'], $new['body']); self::assertSame($old, $new);
        }
        $body = $patch['targets'][0]['after']['page']['blocks'][1]['body'];
        self::assertStringContainsString('fixture_native_v1', $body); self::assertStringContainsString('fixture_detail_key', $body);
        self::assertStringContainsString('The mechanic repaired it for me.', $body);
        self::assertStringNotContainsString('m49_native_v1', $body); self::assertStringNotContainsString('m49_detail_key', $body);
        self::assertSame('fixture:example:practice', $patch['targets'][0]['after']['page']['blocks'][3]['uuid_key']);
    }

    public function test_identity_ancestry_title_style_level_and_bank_changes_are_rejected(): void
    {
        [$release, , $before, $patch, $master, $contract] = $this->fixture();
        foreach (['locale', 'owner', 'display', 'level', 'title', 'style', 'view', 'ancestry', 'bank'] as $case) {
            $m = $master; $p = $patch; $c = $contract;
            if ($case === 'locale') { $m['lessons'][0]['locale'] = 'en'; }
            if ($case === 'owner') { $p['targets'][0]['page_binding']['id'] = 999; }
            if ($case === 'display') { $m['lessons'][0]['blocks'][0]['data']['level'] = 'B1'; }
            if ($case === 'level') { $m['lessons'][0]['technical_block_level'] = 'B2'; }
            if ($case === 'title') { $m['lessons'][0]['subtitle_html'] = '<p><strong>Чужа назва</strong> Пояснення.</p>'; }
            if ($case === 'style') { $m['lessons'][0]['blocks'][1]['data']['sections'][0]['description'] = '<p class="custom">Правило.</p>'; }
            if ($case === 'view') { $m['lessons'][0]['blocks'][1]['data']['view'] = 'theory.custom'; }
            if ($case === 'ancestry') { $p['targets'][0]['ancestry'] = ['modal-verbs']; }
            if ($case === 'bank') { $c['targets'][0]['bank_seeders'] = ['Database\\Seeders\\V3\\Polyglot\\ForeignLessonSeeder']; }
            $p['targets'][0]['after'] = $release->definition($m['lessons'][0], $before['targets'][0]['before']);
            $this->reject(fn () => $release->validate($before, $p, $m, $c));
        }
        $bad = $master; $bad['lessons'][0]['blocks'][1]['level'] = 'B1';
        $this->reject(fn () => $release->definition($bad['lessons'][0], $before['targets'][0]['before']));
    }

    public function test_empty_groups_duplicate_ids_and_foreign_detail_points_fail_closed(): void
    {
        [$release, , $before, $patch, $master, $contract] = $this->fixture();
        foreach (['inputs', 'duplicate', 'untranslated', 'detail', 'navigation'] as $case) {
            $m = $master;
            if ($case === 'inputs') { $m['lessons'][0]['blocks'][3]['data']['inputs'] = []; }
            if ($case === 'duplicate') { $m['lessons'][0]['blocks'][3]['data']['inputs'][0]['id'] = 'fixture-selects-0'; }
            if ($case === 'untranslated') { $m['lessons'][0]['blocks'][1]['data']['sections'][0]['examples'][0]['ua'] = ''; }
            if ($case === 'detail') { $m['lessons'][0]['details'][] = $m['lessons'][0]['details'][0]; }
            if ($case === 'navigation') { unset($m['lessons'][0]['blocks'][2]['data']['items'][0]['url']); }
            $p = $patch; $p['targets'][0]['after'] = $release->definition($m['lessons'][0], $before['targets'][0]['before']);
            $this->reject(fn () => $release->validate($before, $p, $m, $contract));
        }
        $bad = $contract; $bad['targets'] = [];
        $this->reject(fn () => $release->validate($before, $patch, $master, $bad));
    }

    private function frozenFixture(): array
    {
        [$release, $profile, $before, $patch, $master, $contract] = $this->fixture();
        $root = storage_path('app/native-release-'.bin2hex(random_bytes(6)));
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        File::makeDirectory($root, 0700, true); IsolatedTestEnvironment::assertOwnedPath($root);
        foreach ([$before, $patch, $master, $contract] as $i => $artifact) {
            $file = 'source-'.$i.'.json'; $bytes = M26DetailPackage::json($artifact); File::put($root.'/'.$file, $bytes);
            $profile['artifacts'][$file] = hash('sha256', $bytes);
        }
        return [new TheoryNativeContentRelease($profile), $profile, $root, $before, $patch, $master, $contract];
    }

    public function test_frozen_raw_bytes_are_required_and_draft_or_byte_drift_never_loads(): void
    {
        [$release, $profile, $root, $before, $patch, $master, $contract] = $this->frozenFixture();
        self::assertSame([$before, $patch, $master, $contract], $release->load($root));
        $draft = $profile; $draft['artifacts']['source-0.json'] = 'DRAFT';
        $this->reject(fn () => (new TheoryNativeContentRelease($draft))->load($root));
        File::append($root.'/source-1.json', "\n"); $this->reject(fn () => $release->load($root));
    }

    private function blocks(array $target): Collection
    {
        $definition = $target['after'];
        $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'], 'uuid_key' => 'subtitle'];
        $blocks = [];
        foreach ([$subtitle, ...$definition['page']['blocks']] as $position => $source) {
            $model = new TextBlock; $model->setRawAttributes(['id' => 500 + $position, 'page_id' => 345, 'page_category_id' => 1,
                'seeder' => $target['identity'], 'uuid' => M26DetailPackage::uuid($target['identity'], $source, $position),
                'locale' => 'uk', 'sort_order' => $position, 'type' => $source['type'], 'column' => $source['column'],
                'heading' => $source['heading'] ?? null, 'level' => $source['level'] ?? null, 'css_class' => null, 'body' => $source['body']], true);
            $blocks[] = $model;
        }
        return new Collection($blocks);
    }

    public function test_presentation_and_preservation_are_exact_bound_and_never_mutate_original_models(): void
    {
        [$release, $profile, $root, $before, $patch] = $this->frozenFixture();
        $originalBase = app()->basePath(); app()->setBasePath($root);
        try {
            $target = $patch['targets'][0]; $blocks = $this->blocks($target); $block = $blocks[2];
            $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR); $attributes = $block->getAttributes();
            $presentation = $release->presentation($block, $data); self::assertNotNull($presentation);
            self::assertSame([0], array_keys($presentation['points'])); self::assertTrue($release->validDesign($presentation['m42_native_design']));
            self::assertSame('emerald', $release->pointPanels($presentation['m42_native_design'])['points_by_index'][0]['accent']);
            self::assertStringNotContainsString('The mechanic repaired it for me.', json_encode($presentation['data'], JSON_UNESCAPED_UNICODE));
            foreach (['locale' => 'pl', 'seeder' => 'ForeignOwner', 'page_id' => 999, 'page_category_id' => 999,
                'uuid' => 'foreign', 'sort_order' => 999, 'level' => 'C1', 'body' => $block->body."\n"] as $key => $value) {
                $changed = clone $block; $changed->$key = $value; self::assertNull($release->presentation($changed, $data), $key);
            }
            self::assertSame($attributes, $block->getAttributes());
            self::assertSame('{}', $release->preserveExcludedTextBlock($block, 'standalone_test')->body);
            self::assertSame($block, $release->preserveExcludedTextBlock($block, 'theory'));
            self::assertNull($release->preserveExcludedTextBlock($blocks[4], 'standalone_test'));
            $page = new Page; $page->setRawAttributes(['id' => 345, 'page_category_id' => 1, 'seeder' => $target['identity'],
                'slug' => 'example', 'title' => 'Raw title', 'type' => 'theory', 'text' => 'Нове пояснення.'], true);
            $page->setRelation('textBlocks', $blocks); $snapshot = $blocks->map(static fn ($b) => $b->getAttributes())->all();
            $copy = $release->preserveCoursePage($page); self::assertNotSame($page, $copy);
            self::assertSame('Старе пояснення.', $copy->text); self::assertCount(4, $copy->textBlocks);
            self::assertSame($snapshot, $blocks->map(static fn ($b) => $b->getAttributes())->all());
            self::assertSame('Нове пояснення.', $page->text);
        } finally { app()->setBasePath($originalBase); }
    }
}
