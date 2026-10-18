<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use App\Services\TheoryNativeContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M68ComparisonBasics;
use App\Support\TheoryNativeContentRelease;
use App\Support\TheorySentenceBuilderAcceptance;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** One finite missing-subtitle fixture; synthetic SQLite is not working-DB or browser acceptance. */
class M69SubtitleTransitionTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $private;
    private array $before;
    private array $patch;
    private array $master;
    private array $contract;
    private array $captured;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        $this->root = storage_path('app/m69-subtitle-fixture-'.bin2hex(random_bytes(7))); $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app')); File::makeDirectory($this->private, 0700, true);
        M69SubtitleFixturePackage::boot($this->root);
        [$this->before, $this->patch, $this->master, $this->contract] = M69SubtitleFixturePackage::load();
        $old = $this->before['targets'][0]['before']; $time = '2026-01-01 12:00:00';
        DB::table('page_categories')->insert(['id' => 52, 'parent_id' => null, 'slug' => 'prykmetniky-ta-pryslinknyky',
            'title' => 'Synthetic category', 'language' => 'uk', 'type' => 'theory']);
        DB::table('pages')->insert(['id' => 275, 'page_category_id' => 52, 'slug' => $old['slug'],
            'title' => $old['page']['title'], 'type' => 'theory', 'seeder' => M69SubtitleFixturePackage::OWNER, 'text' => null]);
        foreach ($old['page']['blocks'] as $slot => $source) {
            DB::table('text_blocks')->insert(['uuid' => M69SubtitleFixturePackage::PINS[$slot], 'page_id' => 275,
                'page_category_id' => 52, 'locale' => 'uk', 'seeder' => M69SubtitleFixturePackage::OWNER, 'sort_order' => $slot + 1,
                'type' => $source['type'], 'column' => $source['column'], 'heading' => null, 'css_class' => null,
                'level' => $source['level'] ?? null, 'body' => $source['body'], 'created_at' => $time, 'updated_at' => $time]);
        }
        $foreign = (array) DB::table('text_blocks')->where('sort_order', 1)->sole(); unset($foreign['id']);
        foreach (['en', 'pl'] as $locale) {
            $foreign['uuid'] = 'm69-subtitle-foreign-'.$locale; $foreign['locale'] = $locale; $foreign['body'] = 'Unchanged '.$locale;
            DB::table('text_blocks')->insert($foreign);
        }
        for ($n = 0; $n < 5; ++$n) {
            $uuid = 'm69-subtitle-synthetic-question-'.$n;
            DB::table('questions')->insert(['id' => 6900000 + $n, 'uuid' => $uuid, 'question' => 'Ізольована умова.',
                'level' => 'A2', 'type' => '4', 'seeder' => M69SubtitleFixturePackage::BANK, 'created_at' => $time, 'updated_at' => $time]);
            DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid,
                'text_block_uuid' => M69SubtitleFixturePackage::PINS[0], 'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
        }
        DB::connection()->getSchemaBuilder()->dropIfExists('m69_subtitle_protected_progress');
        DB::connection()->getSchemaBuilder()->create('m69_subtitle_protected_progress', static function (Blueprint $table): void {
            $table->id(); $table->text('value');
        });
        DB::table('m69_subtitle_protected_progress')->insert(['value' => 'Synthetic progress remains exact.']);
        $this->captured = ['page' => (array) DB::table('pages')->where('id', 275)->sole(),
            'blocks' => DB::table('text_blocks')->where('page_id', 275)->get()->map(static fn ($row) => (array) $row)->all()];
    }

    private function release(?array $profile = null): TheoryNativeContentRelease
    {
        return new TheoryNativeContentRelease($profile ?? M69SubtitleFixturePackage::profile());
    }
    private function service(): M69SubtitleFixturePatch { return new M69SubtitleFixturePatch(DB::connection(), $this->root.'/database', $this->private); }
    private function file(string $name): string { return $this->private.'/'.$name.'.json'; }
    private function refused(callable $call): void
    {
        try { $call(); self::fail('Expected finite missing-subtitle refusal.'); }
        catch (RuntimeException $error) { self::assertNotSame('', $error->getMessage()); }
    }
    private function snapshot(): array
    {
        $out = []; $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false); sort($tables);
        foreach ($tables as $table) {
            $query = DB::table($table); $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $out[$table] = $query->get()->map(static fn ($row) => (array) $row)->all();
        }
        return $out;
    }
    private function applyFixture(): array
    {
        $service = $this->service(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame('applied', $service->apply($this->file('preview'), $this->file('backup'))['status']);
        return $plan;
    }

    public function test_absent_source_is_not_coerced_to_empty_and_only_projected_configs_gain_the_six_captured_uuids(): void
    {
        $old = $this->before['targets'][0]['before']; $copy = $old; $release = $this->release();
        self::assertTrue($release->subtitleWasAbsent($old));
        self::assertArrayNotHasKey('subtitle_html', $old['page']); self::assertArrayNotHasKey('subtitle_text', $old['page']);
        $projected = $release->projectedBefore($old); $after = $release->definition($this->master['lessons'][0], $old);
        foreach ($old['page']['blocks'] as $slot => $source) {
            self::assertArrayNotHasKey('uuid', $source);
            self::assertSame(M69SubtitleFixturePackage::PINS[$slot], M26DetailPackage::uuid(M69SubtitleFixturePackage::OWNER, $source, $slot));
            self::assertSame(M69SubtitleFixturePackage::PINS[$slot], $projected['page']['blocks'][$slot]['uuid']);
            self::assertSame(M69SubtitleFixturePackage::PINS[$slot], $after['page']['blocks'][$slot]['uuid']);
            $expected = $source; $expected['uuid'] = M69SubtitleFixturePackage::PINS[$slot];
            self::assertSame($expected, $projected['page']['blocks'][$slot]);
        }
        self::assertSame($copy, $old); self::assertSame($this->patch['targets'][0]['after'], $after);
        self::assertCount(7, $after['page']['blocks']);
        self::assertSame($old['page']['title'], $after['page']['title']);
    }

    public function test_null_empty_wrong_owner_category_locale_or_unproved_uuid_vector_cannot_claim_absence(): void
    {
        $old = $this->before['targets'][0]['before'];
        foreach (['subtitle_html', 'subtitle_text'] as $field) {
            foreach ([null, ''] as $value) {
                $bad = $old; $bad['page'][$field] = $value;
                $this->refused(fn () => $this->release()->projectedBefore($bad));
            }
        }
        foreach (['title' => 'Changed title', 'locale' => 'en'] as $field => $value) {
            $bad = $old; $bad['page'][$field] = $value; $this->refused(fn () => $this->release()->projectedBefore($bad));
        }
        $bad = $old; $bad['page']['category']['slug'] = 'foreign'; $this->refused(fn () => $this->release()->projectedBefore($bad));
        $bad = $old; $bad['seeder']['class'] = 'ForeignOwner'; $this->refused(fn () => $this->release()->projectedBefore($bad));
        foreach (['source_html_present' => true, 'source_text_present' => true, 'page_text' => '', 'subtitle_rows' => 1,
            'extracted_title' => '-ed / -ing Adjectives', 'h1' => 'Foreign title'] as $field => $value) {
            $profile = M69SubtitleFixturePackage::profile(); $profile['bindings'][0]['before_subtitle'][$field] = $value;
            $this->refused(fn () => $this->release($profile)->projectedBefore($old));
        }
        foreach (['missing', 'short', 'duplicate', 'shifted', 'malformed', 'unregistered'] as $case) {
            $profile = M69SubtitleFixturePackage::profile();
            if ($case === 'missing') { unset($profile['bindings'][0]['continuing_uuids']); }
            elseif ($case === 'short') { array_pop($profile['bindings'][0]['continuing_uuids']); }
            elseif ($case === 'duplicate') { $profile['bindings'][0]['continuing_uuids'][1] = M69SubtitleFixturePackage::PINS[0]; }
            elseif ($case === 'shifted') { $profile['bindings'][0]['continuing_uuids'][0] = M69SubtitleFixturePackage::PINS[1]; }
            elseif ($case === 'malformed') { $profile['bindings'][0]['continuing_uuids'][0] = 'not-a-uuid'; }
            else { $profile['bindings'][0]['before_subtitle']['mode'] = 'captured-plain'; }
            $this->refused(fn () => $this->release($profile)->projectedBefore($old));
        }
    }

    public function test_public_projection_requires_the_literal_independent_absence_record_and_preserves_all_other_metadata(): void
    {
        $release = $this->release(); $release->validate($this->before, $this->patch, $this->master, $this->contract);
        foreach (['source_html_present', 'source_text_present', 'page_text', 'subtitle_rows', 'extracted_title', 'h1'] as $field) {
            $bad = $this->master; unset($bad['editorial_review']['pre_apply_metadata'][0][$field]);
            $this->refused(fn () => $release->validate($this->before, $this->patch, $bad, $this->contract));
        }
        $old = $this->before['targets'][0]['before']; $after = $this->patch['targets'][0]['after'];
        M69SubtitleFixturePatch::assertDefinitionScope($old, $after, $this->patch['targets'][0], 0);
        foreach (['title' => 'Rewritten title', 'locale' => 'pl'] as $field => $value) {
            $bad = $after; $bad['page'][$field] = $value;
            $this->refused(fn () => M69SubtitleFixturePatch::assertDefinitionScope($old, $bad, $this->patch['targets'][0], 0));
        }
        $bad = $after; unset($bad['page']['blocks'][0]['uuid']);
        $this->refused(fn () => M69SubtitleFixturePatch::assertDefinitionScope($old, $bad, $this->patch['targets'][0], 0));
    }

    public function test_transaction_inserts_only_new_subtitle_zero_and_practice_seven_without_rekeying_old_rows_and_repeats_as_noop(): void
    {
        $service = $this->service(); $old = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($old, $this->snapshot()); self::assertSame('before', $plan['state']);
        self::assertCount(7, $plan['updates']); self::assertCount(2, $plan['inserts']);
        self::assertSame([0, 7], array_column(array_column($plan['inserts'], 'fields'), 'sort_order'));
        self::assertSame(['subtitle', 'practice-set'], array_column(array_column($plan['inserts'], 'fields'), 'type'));
        foreach ($plan['updates'] as $update) {
            self::assertSame($update['table'] === 'pages' ? ['text'] : ['body'], array_keys($update['before']));
        }
        self::assertSame('applied', $service->apply($this->file('preview'), $this->file('backup'))['status']);
        $after = $service->savePlan($this->file('after')); self::assertSame('after', $after['state']);
        self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']); self::assertSame($plan['protected'], $after['protected']);
        $current = $this->snapshot();
        foreach ($old['text_blocks'] as $row) {
            $now = array_values(array_filter($current['text_blocks'], static fn ($value) => $value['id'] === $row['id']))[0];
            if ($row['locale'] === 'uk') { unset($row['body'], $now['body']); }
            self::assertSame($row, $now, 'Existing UUID, order, metadata, timestamps and EN/PL remain exact.');
        }
        foreach ($old as $table => $rows) { if (!in_array($table, ['pages', 'text_blocks'], true)) { self::assertSame($rows, $current[$table], $table); } }
        self::assertSame('no-op', $service->apply($this->file('preview'), $this->file('unused-old'))['status']);
        self::assertSame('no-op', $service->apply($this->file('after'), $this->file('unused-fresh'))['status']);
        self::assertFileDoesNotExist($this->file('unused-old')); self::assertFileDoesNotExist($this->file('unused-fresh'));
    }

    public function test_absent_sql_capture_is_checked_independently_and_does_not_accept_empty_text_or_an_invented_subtitle(): void
    {
        $method = new \ReflectionMethod(TheoryNativeContentPatch::class, 'assertMetadataBaseline');
        $old = $this->before['targets'][0]['before']; $after = $this->patch['targets'][0]['after'];
        $method->invoke($this->service(), $this->captured, $this->captured['page'], $this->captured['blocks'], $old, $after, 0);
        foreach (['empty-text', 'subtitle', 'missing-uuid', 'wrong-order', 'wrong-owner', 'wrong-page', 'wrong-category'] as $case) {
            $captured = $this->captured;
            if ($case === 'empty-text') { $captured['page']['text'] = ''; }
            elseif ($case === 'subtitle') { $captured['blocks'][0]['type'] = 'subtitle'; }
            elseif ($case === 'missing-uuid') { $captured['blocks'][0]['uuid'] = 'foreign'; }
            elseif ($case === 'wrong-order') { $captured['blocks'][0]['sort_order'] = 0; }
            elseif ($case === 'wrong-owner') { $captured['blocks'][0]['seeder'] = 'ForeignOwner'; }
            elseif ($case === 'wrong-page') { $captured['page']['id'] = 999; }
            else { $captured['page']['page_category_id'] = 999; }
            $this->refused(fn () => $method->invoke($this->service(), $captured, $this->captured['page'], $this->captured['blocks'], $old, $after, 0));
        }
    }

    public function test_partial_insert_collisions_manual_page_text_and_shifted_continuing_order_are_refused_without_writes(): void
    {
        $service = $this->service(); $plan = $service->plan();
        foreach ($plan['inserts'] as $insert) {
            DB::table('text_blocks')->insert($insert['fields']); $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
            DB::table('text_blocks')->where('uuid', $insert['fields']['uuid'])->delete();
        }
        $collision = $plan['inserts'][0]['fields']; $collision['seeder'] = 'ForeignOwner'; $collision['locale'] = 'en';
        DB::table('text_blocks')->insert($collision); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
        DB::table('text_blocks')->where('uuid', $collision['uuid'])->delete();
        DB::table('pages')->where('id', 275)->update(['text' => '']); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
        DB::table('pages')->where('id', 275)->update(['text' => null]);
        DB::table('text_blocks')->where('uuid', M69SubtitleFixturePackage::PINS[0])->update(['sort_order' => 0]);
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_applied_subtitle_requires_exact_metadata_body_timestamp_and_presence(): void
    {
        $this->applyFixture(); $service = $this->service();
        $subtitle = (array) DB::table('text_blocks')->where('type', 'subtitle')->sole();
        foreach (['seeder' => 'ForeignOwner', 'locale' => 'en', 'page_id' => 999, 'page_category_id' => 999,
            'sort_order' => 1, 'type' => 'box', 'column' => 'left', 'level' => 'B1', 'heading' => 'Manual',
            'body' => 'Manual subtitle', 'updated_at' => '2026-01-01 00:00:00'] as $field => $value) {
            DB::table('text_blocks')->where('id', $subtitle['id'])->update([$field => $value]); $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
            DB::table('text_blocks')->where('id', $subtitle['id'])->update([$field => $subtitle[$field]]);
        }
        DB::table('text_blocks')->where('id', $subtitle['id'])->delete(); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_course_and_standalone_reconstruct_real_absence_on_clones_and_unknown_sources_remain_readable(): void
    {
        $this->applyFixture(); $release = $this->release();
        $page = Page::with('textBlocks')->findOrFail(275); $attrs = $page->getAttributes();
        $blocks = $page->textBlocks->map(static fn ($block) => $block->getAttributes())->all(); $snapshot = $this->snapshot();
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        $restored = $release->preserveCoursePage($page); self::assertNotSame($page, $restored);
        self::assertNull($restored->getRawOriginal('text')); self::assertNull($restored->text);
        self::assertCount(6, $restored->textBlocks->where('locale', 'uk'));
        self::assertCount(0, $restored->textBlocks->where('locale', 'uk')->where('type', 'subtitle'));
        foreach ($this->before['targets'][0]['before']['page']['blocks'] as $slot => $source) {
            $block = $restored->textBlocks->firstWhere('uuid', M69SubtitleFixturePackage::PINS[$slot]);
            self::assertNotNull($block); self::assertSame($slot + 1, $block->sort_order); self::assertSame($source['body'], $block->body);
        }
        foreach ($page->textBlocks as $block) {
            $copy = $release->preserveExcludedTextBlock($block, 'standalone_test');
            if ($block->locale === 'uk' && in_array($block->type, ['subtitle', 'practice-set'], true)) { self::assertNull($copy); }
            elseif ($block->locale === 'uk') {
                self::assertSame($restored->textBlocks->firstWhere('uuid', $block->uuid)->getAttributes(), $copy->getAttributes());
            } else { self::assertSame($block, $copy); }
            self::assertSame($block, $release->preserveExcludedTextBlock($block, 'theory'));
        }
        $subtitle = $page->textBlocks->firstWhere('type', 'subtitle');
        foreach (['locale' => 'en', 'seeder' => 'ForeignOwner', 'page_id' => 999, 'page_category_id' => 999,
            'sort_order' => 1, 'uuid' => 'foreign', 'type' => 'box', 'column' => 'left', 'body' => 'Readable manual body'] as $field => $value) {
            $foreign = clone $subtitle; $foreign->$field = $value;
            self::assertSame($foreign, $release->preserveExcludedTextBlock($foreign, 'standalone_test'), $field);
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
        self::assertSame($attrs, $page->getAttributes());
        self::assertSame($blocks, $page->textBlocks->map(static fn ($block) => $block->getAttributes())->all());
        self::assertSame($snapshot, $this->snapshot());
    }

    public function test_exact_new_subtitle_relation_exclusion_does_not_hide_foreign_or_edited_rows(): void
    {
        $this->applyFixture(); $service = $this->service(); $page = (array) DB::table('pages')->where('id', 275)->sole();
        $rows = DB::table('text_blocks')->where('page_id', 275)->get()->map(static fn ($row) => (array) $row)->all();
        $method = new \ReflectionMethod(TheoryNativeContentPatch::class, 'relations'); $expected = $method->invoke($service, $page, $rows, false);
        $subtitle = array_values(array_filter($rows, static fn ($row) => $row['type'] === 'subtitle'))[0];
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => 'm69-subtitle-synthetic-question-0',
            'text_block_uuid' => $subtitle['uuid'], 'position' => 9]);
        self::assertSame($expected, $method->invoke($service, $page, $rows, false));
        foreach (['seeder' => 'ForeignOwner', 'locale' => 'en', 'page_id' => 999, 'page_category_id' => 999,
            'type' => 'box', 'column' => 'left', 'level' => 'A2', 'sort_order' => 99, 'body' => 'Changed'] as $field => $value) {
            $changed = $rows; foreach ($changed as &$row) { if ($row['uuid'] === $subtitle['uuid']) { $row[$field] = $value; } } unset($row);
            self::assertNotSame($expected, $method->invoke($service, $page, $changed, false), $field);
        }
    }

    public function test_accepted_m68_profiles_keep_the_original_subtitle_defaults_and_literal_projection(): void
    {
        [$before, $patch, $master] = M68ComparisonBasics::load();
        $release = new TheoryNativeContentRelease(M68ComparisonBasics::profile());
        foreach ($before['targets'] as $i => $target) {
            self::assertFalse($release->subtitleWasAbsent($target['before']));
            self::assertSame($target['before'], $release->projectedBefore($target['before']));
            self::assertSame($patch['targets'][$i]['after'], $release->definition($master['lessons'][$i], $target['before']));
        }
    }
}

/** Finite private test artifacts exercise the real generic policy without registering a release. */
final class M69SubtitleFixturePackage
{
    public const BASE_SHA = 'c41f65749e39768a69e62dff4e4b942ffa751fe4';
    public const OWNER = 'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesEdIngAdjectivesTheorySeeder';
    public const BANK = 'Database\\Seeders\\V3\\Adjectives\\M69SubtitleIsolatedBankSeeder';
    public const OWNERS = [self::OWNER];
    public const PAGE_IDS = [275];
    public const CONTINUING_COUNTS = [6];
    public const PINS = ['0a02f7ae-2ec7-5d2e-9c51-db26555f5399', '3b1b7594-5b90-554d-aee0-52edb21ff743',
        'dc206a89-c512-54e1-8e05-af43faf9cc93', 'b2dbf466-24bd-51f6-a4a9-6648f8ceb27f',
        '16ccf335-824b-5d64-8fc8-45e9fa73e514', '322f2cef-ad93-5e79-b9de-b0501f4da3a5'];
    private static array $profile;

    public static function profile(): array { return self::$profile; }
    public static function load(?string $root = null): array { return (new TheoryNativeContentRelease(self::profile()))->load(); }
    public static function validate(array $before, array $patch, array $master, ?array $contract = null): void
    {
        (new TheoryNativeContentRelease(self::profile()))->validate($before, $patch, $master, $contract);
    }
    public static function boot(string $root): void
    {
        $absence = ['mode' => 'captured-absent', 'source_html_present' => false, 'source_text_present' => false,
            'page_text' => null, 'subtitle_rows' => 0, 'raw_page_title' => '-ed / -ing Adjectives',
            'extracted_title' => null, 'h1' => '-ed / -ing Adjectives'];
        $path = TheorySentenceBuilderAcceptance::definitionPath(self::OWNER); $url = 'http://gramlyze.loc/theory/prykmetniky-ta-pryslinknyky/ed-ing-adjectives';
        self::$profile = ['label' => 'M69 missing-subtitle isolated fixture', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm69_subtitle_fixture_v1', 'detail_marker' => 'm69_subtitle_fixture_detail', 'design' => 'm69-subtitle-fixture',
            'uuid_prefix' => 'm69-subtitle-fixture:', 'artifacts' => [], 'bindings' => [[
                'identity' => self::OWNER, 'page_id' => 275, 'category_id' => 52, 'slug' => 'ed-ing-adjectives',
                'ancestry' => ['prykmetniky-ta-pryslinknyky'], 'local_url' => $url, 'practice_level' => 'A2', 'display_level' => 'A2',
                'rendered_title' => '-ed / -ing Adjectives', 'continuing_levels' => ['A2', 'A2', 'A2', 'A2', 'A2', null],
                'before_subtitle' => $absence, 'continuing_uuids' => self::PINS,
            ]]];
        $types = ['hero', 'forms-grid', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips'];
        $columns = ['header', 'left', 'left', 'right', 'right', 'footer']; $configs = []; $blocks = [];
        $data = [
            ['level' => 'A2', 'intro' => 'Ізольований опис.', 'rules' => []],
            ['title' => 'Форми', 'items' => []],
            ['title' => 'Значення', 'sections' => [['label' => 'Стан', 'color' => 'emerald', 'description' => 'Ізольоване пояснення.',
                'examples' => [['en' => 'She is interested.', 'ua' => 'Вона зацікавлена.']]]]],
            ['title' => 'Перевір форму', 'items' => [['label' => 'Форма', 'color' => 'rose', 'title' => 'Умова прямо вимагає стан.',
                'wrong_en' => 'She are interested.', 'wrong_ua' => 'Вона зацікавлена.', 'right_en' => 'She is interested.', 'right_ua' => 'Вона зацікавлена.']]],
            ['title' => 'Підсумок', 'items' => ['Збережи значення.']],
            ['title' => 'Навігація', 'items' => [['label' => '-ed / -ing Adjectives', 'current' => true]]],
        ];
        foreach ($types as $slot => $type) {
            $config = ['type' => $type, 'column' => $columns[$slot]];
            if ($slot < 5) { $config['level'] = 'A2'; }
            $configs[] = $config + ['body' => M26DetailPackage::json(['original_slot' => $slot])];
            $blocks[] = ['key' => 'fixture-'.$slot, 'existing_source_index' => $slot, 'type' => $type,
                'column' => $columns[$slot], 'level' => $slot < 5 ? 'A2' : null, 'data' => $data[$slot]];
        }
        $selects = []; $choices = []; $inputs = [];
        foreach ([0, 1] as $n) {
            $selects[] = ['id' => 'm69-subtitle-fixture-select-'.$n, 'source_index' => $n + 1, 'label' => 'Обери задану форму.',
                'options' => ['is', 'are'], 'answer' => 'is', 'accepted' => ['is'], 'feedback' => ['is' => 'She is interested. — Вона зацікавлена.', 'are' => 'Потрібна форма is.']];
            $choices[] = ['id' => 'm69-subtitle-fixture-choice-'.$n, 'source_index' => $n + 3, 'label' => 'Обери заданий зміст.',
                'options' => ['Стан', 'Причина'], 'answer' => 'Стан', 'accepted' => ['Стан'], 'feedback' => ['стан' => 'Названо стан.', 'причина' => 'Тут названо стан.']];
            $inputs[] = ['id' => 'm69-subtitle-fixture-input-'.$n, 'source_index' => $n + 5,
                'before' => 'interested. / is / She', 'after' => 'Скажи саме: Вона зацікавлена.',
                'answer' => 'She is interested.', 'accepted' => ['She is interested.'], 'feedback' => ['she is interested.' => 'She is interested. — Вона зацікавлена.']];
        }
        $blocks[] = ['key' => 'fixture-practice', 'existing_source_index' => null, 'type' => 'practice-set', 'column' => 'left', 'level' => 'A2',
            'data' => ['title' => 'Практика', 'selects' => $selects, 'choices' => $choices, 'inputs' => $inputs,
                'linked_practice' => ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => [self::BANK]]]];
        $old = ['schema_version' => 1, 'content_type' => 'page', 'slug' => 'ed-ing-adjectives',
            'page' => ['title' => '-ed / -ing Adjectives', 'locale' => 'uk',
                'category' => ['slug' => 'prykmetniky-ta-pryslinknyky', 'title' => 'Прикметники та прислівники', 'language' => 'uk'],
                'tags' => ['A2', 'Adjectives'], 'blocks' => $configs], 'seeder' => ['class' => self::OWNER], 'type' => 'theory'];
        $lesson = ['identity' => self::OWNER, 'slug' => 'ed-ing-adjectives', 'locale' => 'uk', 'technical_block_level' => 'A2', 'display_level' => 'A2',
            'subtitle_html' => '<p><strong>-ed / -ing Adjectives</strong></p><p>Ізольований опис.</p>',
            'subtitle_text' => '-ed / -ing Adjectives. Ізольований опис.', 'blocks' => $blocks, 'details' => []];
        $before = ['schema_version' => 1, 'base_sha' => self::BASE_SHA, 'targets' => [['identity' => self::OWNER, 'path' => $path, 'before' => $old]]];
        $after = (new TheoryNativeContentRelease(self::profile()))->definition($lesson, $old);
        $patch = ['schema_version' => 1, 'base_sha' => self::BASE_SHA, 'targets' => [['identity' => self::OWNER, 'path' => $path,
            'slug' => 'ed-ing-adjectives', 'ancestry' => ['prykmetniky-ta-pryslinknyky'],
            'page_binding' => ['id' => 275, 'page_category_id' => 52], 'local_url' => $url,
            'block_keys' => array_column($blocks, 'key'), 'after' => $after]]];
        $metadata = ['identity' => self::OWNER]; foreach (['source_html_present', 'source_text_present', 'page_text', 'subtitle_rows', 'extracted_title', 'h1'] as $field) { $metadata[$field] = $absence[$field]; }
        $master = ['schema_version' => 1, 'base_sha' => self::BASE_SHA, 'version' => '1.0.0', 'lessons' => [$lesson],
            'editorial_review' => ['pre_apply_metadata' => [$metadata]]];
        $contract = ['schema_version' => 1, 'targets' => [['identity' => self::OWNER, 'definition' => $path,
            'local_url' => $url, 'level' => 'A2', 'bank_seeders' => [self::BANK]]]];
        File::makeDirectory(dirname($root.'/'.$path), 0700, true, true);
        File::put($root.'/'.$path, M26DetailPackage::json($after)."\n");
        $relative = substr(str_replace('\\', '/', $root), strlen(str_replace('\\', '/', base_path())) + 1);
        foreach (['before' => $before, 'patch' => $patch, 'master' => $master, 'contract' => $contract] as $name => $artifact) {
            $bytes = M26DetailPackage::json($artifact)."\n"; File::put($root.'/'.$name.'.json', $bytes);
            self::$profile['artifacts'][$relative.'/'.$name.'.json'] = hash('sha256', $bytes);
        }
    }
}

final class M69SubtitleFixturePatch extends TheoryNativeContentPatch
{
    public const NAMES = M69SubtitleFixturePackage::OWNERS;
    protected const ID = 'm69-subtitle-isolated-fixture';
    protected static function packageClass(): string { return M69SubtitleFixturePackage::class; }
    protected static function policy(): array { return ['guard' => 'unused-sqlite-only-fixture']; }
    public static function sourcePaths(array $patch, array $contract, ?string $root = null): array { return array_column($patch['targets'], 'path'); }
}
