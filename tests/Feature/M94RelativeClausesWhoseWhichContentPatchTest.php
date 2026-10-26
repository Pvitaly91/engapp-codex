<?php

namespace Tests\Feature;

use App\Services\M94ContentPatch as Patch;
use App\Support\M26DetailPackage as Identity;
use App\Support\M94RelativeClausesWhoseWhich as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** SQLite-only synthetic writer tests. They are not working-local apply evidence. */
class M94RelativeClausesWhoseWhichContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $private;
    private array $before;
    private array $patch;
    private array $contract;

    protected function setUp(): void
    {
        parent::setUp();
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('testing', app()->environment());
        self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default'));
        self::assertSame('array', config('session.driver'));
        $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        $source = str_replace('\\', '/', dirname(__DIR__, 2));
        // Pending/unpublished package hashes must fail here, never skip tests.
        [$this->before, $this->patch, , $this->contract] = Package::load($source);
        self::assertSame([284], Package::PAGE_IDS);
        self::assertSame([34], Package::CATEGORY_IDS);
        self::assertSame([['B2', null]], Package::CONTINUING_LEVELS);
        self::assertSame(['hero', 'box'], array_column($this->before['targets'][0]['before']['page']['blocks'], 'type'));
        $this->root = storage_path('app/m94-patch-fixture-'.bin2hex(random_bytes(8)));
        $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        File::makeDirectory($this->private, 0700, true);
        foreach (Patch::sourcePaths($this->patch, $this->contract, $source) as $path) {
            self::assertFileExists($source.'/'.$path);
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true);
            File::copy($source.'/'.$path, $this->root.'/'.$path);
        }
        foreach ([[1, 'foreign'], [34, 'clauses-and-linking-words']] as [$id, $slug]) {
            DB::table('page_categories')->insert(['id' => $id, 'slug' => $slug, 'title' => $slug,
                'parent_id' => null, 'type' => 'theory', 'language' => 'uk']);
        }
        $time = '2026-01-01 12:00:00'; $old = $this->before['targets'][0]['before']; $owner = Package::OWNERS[0];
        DB::table('pages')->insert(['id' => 284, 'page_category_id' => 34, 'seeder' => $owner,
            'slug' => $old['slug'], 'title' => $old['page']['title'], 'text' => $old['page']['subtitle_text'],
            'type' => 'theory', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('pages')->insert(['id' => 900, 'page_category_id' => 1, 'seeder' => 'Unrelated\\Owner',
            'slug' => 'foreign', 'title' => 'Unchanged foreign Page', 'text' => 'Unchanged foreign teaser',
            'type' => 'theory', 'created_at' => $time, 'updated_at' => $time]);
        $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $old['page']['subtitle_html'],
            'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'],
            ...$old['page']['blocks']];
        foreach ($configs as $position => $config) {
            DB::table('text_blocks')->insert(['id' => [8739, 8740, 8741][$position],
                'uuid' => Identity::uuid($owner, $config, $position), 'page_id' => 284, 'page_category_id' => 34,
                'locale' => 'uk', 'seeder' => $owner, 'sort_order' => $position, 'type' => $config['type'],
                'column' => $config['column'], 'heading' => $config['heading'] ?? null, 'level' => $config['level'] ?? null,
                'css_class' => $config['css_class'] ?? null, 'body' => $config['body'], 'created_at' => $time, 'updated_at' => $time]);
        }
        $foreign = (array) DB::table('text_blocks')->where('id', 8740)->sole(); unset($foreign['id']);
        foreach (['en', 'pl'] as $locale) {
            $foreign['uuid'] = 'm94-foreign-'.$locale; $foreign['locale'] = $locale; $foreign['body'] = 'Unchanged '.$locale;
            DB::table('text_blocks')->insert($foreign);
        }
        $foreign['uuid'] = 'm94-unrelated-row'; $foreign['locale'] = 'uk'; $foreign['page_id'] = 900;
        $foreign['page_category_id'] = 1; $foreign['seeder'] = 'Unrelated\\Owner'; $foreign['body'] = 'Unchanged other owner';
        DB::table('text_blocks')->insert($foreign);
        $banks = array_values(array_filter(Patch::BANK_SOURCE_PROFILES, static fn ($p) => $p['kind'] === 'definition'));
        self::assertSame([72, 72, 48], array_column($banks, 'question_count'));
        foreach ($banks as $bankIndex => $bank) {
            for ($n = 0; $n < $bank['question_count']; $n++) {
                $id = 8000000 + $bankIndex * 1000 + $n; $uuid = 'm94-synthetic-'.$id;
                DB::table('questions')->insert(['id' => $id, 'uuid' => $uuid, 'question' => 'Synthetic isolated fixture; not a live question.',
                    'difficulty' => 2, 'level' => $bankIndex === 2 ? 'B2' : ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'][$n % 6],
                    'type' => $bankIndex === 0 ? '1' : '4', 'seeder' => $bank['identity'], 'created_at' => $time, 'updated_at' => $time]);
                DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid,
                    'text_block_uuid' => Package::CONTINUING_UUIDS[0], 'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
            }
        }
        DB::table('question_options')->insert(['id' => 1, 'option' => 'Unchanged synthetic token']);
        DB::table('question_option_question')->insert(['question_id' => 8002000, 'option_id' => 1, 'flag' => null]);
        DB::table('question_answers')->insert(['question_id' => 8002000, 'option_id' => 1, 'marker' => 'a1', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('verb_hints')->insert(['question_id' => 8002000, 'option_id' => 1, 'marker' => 'a1', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('question_variants')->insert(['question_id' => 8002000, 'text' => 'Unchanged variant', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('saved_grammar_tests')->insert(['id' => 1, 'uuid' => 'm94-saved', 'name' => 'Unchanged saved test', 'slug' => 'm94-saved']);
        DB::table('saved_grammar_test_questions')->insert(['saved_grammar_test_id' => 1, 'question_uuid' => 'm94-synthetic-8002000',
            'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
        foreach (['m94_protected_progress', 'm94_protected_course', 'm94_protected_enrollment'] as $table) {
            DB::connection()->getSchemaBuilder()->create($table, static function (Blueprint $schema): void { $schema->id(); $schema->text('value'); });
            DB::table($table)->insert(['value' => 'Unchanged synthetic '.$table]);
        }
    }

    private function service(): M94SyntheticContentPatch { return new M94SyntheticContentPatch(DB::connection(), $this->root.'/database', $this->private); }
    private function file(string $name): string { return $this->private.'/'.$name.'.json'; }
    private function refused(callable $call, string $case = ''): void
    {
        $caught = null; try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }
    private function snapshot(): array
    {
        $out = []; $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false); sort($tables);
        foreach ($tables as $table) {
            if (str_starts_with($table, 'sqlite_')) { continue; }
            $query = DB::table($table); $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $out[$table] = $query->get()->map(static fn ($row) => (array) $row)->all();
        }
        return $out;
    }
    private function writePlan(string $name, array $plan): void
    {
        unset($plan['sha256']); $plan['sha256'] = Patch::digest($plan);
        File::put($this->file($name), json_encode($plan, JSON_THROW_ON_ERROR));
    }

    public function test_exact_four_updates_six_appends_and_repeated_noops_preserve_every_old_row(): void
    {
        $service = $this->service(); $before = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($before, $this->snapshot()); self::assertSame('before', $plan['state']);
        self::assertCount(4, $plan['updates']); self::assertCount(6, $plan['inserts']); self::assertArrayNotHasKey('deletes', $plan);
        $headings = [];
        foreach ($plan['updates'] as $change) {
            if ($change['table'] === 'pages') {
                self::assertSame(284, $change['id']); self::assertSame(['text'], array_keys($change['after']));
            } else {
                self::assertSame('text_blocks', $change['table']); self::assertSame(['body', 'heading'], array_keys($change['after']));
                if ($change['before']['heading'] !== $change['after']['heading']) { $headings[] = $change; }
            }
        }
        self::assertCount(1, $headings); self::assertSame(8741, $headings[0]['id']);
        self::assertSame('B2 focus', $headings[0]['before']['heading']); self::assertSame('1. Належність чи уточнення?', $headings[0]['after']['heading']);
        self::assertStringStartsWith('<p>', $headings[0]['after']['body']); self::assertStringNotContainsString('<h2', $headings[0]['after']['body']);
        self::assertSame(['forms-grid', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips', 'practice-set'],
            array_column(array_column($plan['inserts'], 'fields'), 'type'));
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame(['applied', 4, 6], [$result['status'], $result['updated'], $result['inserted']]);
        self::assertSame($plan, json_decode(File::get($this->file('backup')), true, flags: JSON_THROW_ON_ERROR));
        $after = $this->snapshot(); self::assertCount(count($before['text_blocks']) + 6, $after['text_blocks']);
        foreach ($before['text_blocks'] as $row) {
            $now = (array) DB::table('text_blocks')->where('id', $row['id'])->sole();
            if ($row['page_id'] === 284 && $row['locale'] === 'uk') {
                $new = $this->patch['targets'][0]['after']['page'];
                $expected = $row['sort_order'] === 0 ? ['body' => $new['subtitle_html'], 'heading' => null] : $new['blocks'][$row['sort_order'] - 1];
                foreach (['body', 'heading'] as $field) { self::assertSame($expected[$field] ?? null, $now[$field]); unset($row[$field], $now[$field]); }
            }
            self::assertSame($row, $now, 'Old UUID/ID/type/column/NULL level/order/timestamps and EN/PL/foreign rows are exact.');
        }
        foreach ($before['pages'] as $row) {
            $now = (array) DB::table('pages')->where('id', $row['id'])->sole();
            if ($row['id'] === 284) { self::assertSame(Package::SUBTITLE_TEXT_AFTER, $now['text']); unset($row['text'], $now['text']); }
            self::assertSame($row, $now);
        }
        foreach ($before as $table => $rows) { if (!in_array($table, ['pages', 'text_blocks'], true)) { self::assertSame($rows, $after[$table], $table); } }
        $practice = DB::table('text_blocks')->where('page_id', 284)->where('locale', 'uk')->where('type', 'practice-set')->sole();
        self::assertSame(['B2', 8], [$practice->level, $practice->sort_order]);
        self::assertCount(3, array_filter($before['text_blocks'], static fn ($r) => $r['page_id'] === 284 && $r['locale'] === 'uk'));
        $repeat = $service->savePlan($this->file('repeat')); self::assertSame(['after', [], []], [$repeat['state'], $repeat['updates'], $repeat['inserts']]);
        foreach (['preview', 'repeat'] as $name) {
            $noop = $service->apply($this->file($name), $this->file('unused-'.$name));
            self::assertSame(['no-op', 0, 0], [$noop['status'], $noop['updated'], $noop['inserted']]);
            self::assertFileDoesNotExist($this->file('unused-'.$name)); self::assertSame($after, $this->snapshot());
        }
    }

    public function test_first_and_last_update_or_insert_failures_roll_back_the_entire_owner(): void
    {
        foreach (['first-update', 'last-update', 'first-insert', 'last-insert'] as $stage) {
            $service = $this->service(); $plan = $service->savePlan($this->file($stage)); $before = $this->snapshot();
            self::assertCount(4, $plan['updates']); self::assertCount(6, $plan['inserts']);
            if (str_ends_with($stage, 'insert')) { $service->failInsert = str_starts_with($stage, 'first') ? 0 : 5; }
            else { $service->failUpdate = str_starts_with($stage, 'first') ? 0 : 3; }
            $this->refused(fn () => $service->apply($this->file($stage), $this->file('backup-'.$stage)));
            self::assertSame($before, $this->snapshot()); self::assertSame($plan, $service->plan());
            self::assertFileExists($this->file('backup-'.$stage));
        }
    }

    public function test_foreign_owner_category_locale_type_and_technical_metadata_fail_without_writes(): void
    {
        $service = $this->service();
        foreach ([['pages', 284, ['seeder' => 'Unrelated\\Owner', 'page_category_id' => 1, 'slug' => 'wrong', 'title' => 'Wrong title']],
            ['text_blocks', 8740, ['locale' => 'en', 'seeder' => 'Unrelated\\Owner', 'level' => null,
                'column' => 'right', 'heading' => 'Unknown', 'type' => 'box', 'sort_order' => 3]],
            ['text_blocks', 8741, ['level' => 'B2', 'page_category_id' => 1, 'heading' => 'Unapproved heading']],
            ['questions', 8002000, ['seeder' => 'Unrelated\\Bank', 'type' => '1', 'level' => 'C1']]] as [$table, $id, $changes]) {
            $old = (array) DB::table($table)->where('id', $id)->sole();
            foreach ($changes as $field => $value) {
                DB::table($table)->where('id', $id)->update([$field => $value]); $now = $this->snapshot();
                $this->refused(fn () => $service->plan(), $table.'.'.$field); self::assertSame($now, $this->snapshot());
                DB::table($table)->where('id', $id)->update([$field => $old[$field]]);
            }
        }
        DB::table('page_categories')->where('id', 34)->update(['slug' => 'articles']); $now = $this->snapshot();
        $this->refused(fn () => $service->plan(), 'Articles ancestry is not relative-clause authority.');
        self::assertSame($now, $this->snapshot());
    }

    public function test_body_heading_page_and_append_partial_states_cannot_be_completed_silently(): void
    {
        $service = $this->service(); $plan = $service->savePlan($this->file('partial-preview'));
        $box = array_values(array_filter($plan['updates'], static fn ($r) => $r['table'] === 'text_blocks' && $r['id'] === 8741))[0];
        foreach ([['heading' => $box['after']['heading']], ['body' => $box['after']['body']], $box['after']] as $partial) {
            DB::table('text_blocks')->where('id', 8741)->update($partial); $now = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($now, $this->snapshot());
            DB::table('text_blocks')->where('id', 8741)->update($box['before']);
        }
        $page = array_values(array_filter($plan['updates'], static fn ($r) => $r['table'] === 'pages'))[0];
        DB::table('pages')->where('id', 284)->update($page['after']); $now = $this->snapshot();
        $this->refused(fn () => $service->plan()); self::assertSame($now, $this->snapshot());
        DB::table('pages')->where('id', 284)->update($page['before']);
        $append = $plan['inserts'][0]['fields'];
        DB::table('text_blocks')->insert($append + ['created_at' => '2026-01-02 12:00:00', 'updated_at' => '2026-01-02 12:00:00']);
        $now = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($now, $this->snapshot());
        DB::table('text_blocks')->where('uuid', $append['uuid'])->delete();
        foreach ($plan['updates'] as $change) { DB::table($change['table'])->where('id', $change['id'])->update($change['after']); }
        $now = $this->snapshot(); $this->refused(fn () => $service->plan(), 'All continuing rows AFTER do not excuse missing six appends.');
        self::assertSame($now, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created'));
    }

    public function test_stale_body_protected_families_and_exclusive_backup_collision_refuse_before_writes(): void
    {
        $service = $this->service(); $service->savePlan($this->file('stale-preview'));
        $cases = [
            ['text_blocks', 8740, 'body'], ['text_blocks', DB::table('text_blocks')->where('uuid', 'm94-foreign-en')->value('id'), 'body'],
            ['pages', 900, 'text'], ['questions', 8002000, 'question'], ['question_options', 1, 'option'],
            ['question_answers', 1, 'marker'], ['verb_hints', 1, 'marker'], ['question_variants', 1, 'text'],
            ['saved_grammar_test_questions', 1, 'position'], ['m94_protected_progress', 1, 'value'],
            ['m94_protected_course', 1, 'value'], ['m94_protected_enrollment', 1, 'value'],
        ];
        foreach ($cases as $n => [$table, $id, $field]) {
            $old = (array) DB::table($table)->where('id', $id)->sole();
            DB::table($table)->where('id', $id)->update([$field => $field === 'position' ? 99 : 'Later unrelated state']);
            $now = $this->snapshot(); $backup = $this->file('no-stale-'.$n);
            $this->refused(fn () => $service->apply($this->file('stale-preview'), $backup), $table.'.'.$field);
            self::assertSame($now, $this->snapshot()); self::assertFileDoesNotExist($backup);
            DB::table($table)->where('id', $id)->update([$field => $old[$field]]);
        }
        File::put($this->file('occupied'), 'Unrelated evidence'); $now = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('stale-preview'), $this->file('occupied')));
        self::assertSame('Unrelated evidence', File::get($this->file('occupied'))); self::assertSame($now, $this->snapshot());
    }

    public function test_source_scope_allows_only_box_heading_without_type_level_uuid_or_key_migration(): void
    {
        $old = $this->before['targets'][0]['before']; $target = $this->patch['targets'][0]; $after = $target['after'];
        Patch::assertDefinitionScope($old, $after, $target, 0);
        self::assertArrayNotHasKey('level', $old['page']['blocks'][1]);
        self::assertArrayNotHasKey('level', $after['page']['blocks'][1]);
        self::assertSame('box', $after['page']['blocks'][1]['type']);
        foreach (['hero-heading', 'box-type', 'box-level', 'explicit-null-key', 'box-uuid-key', 'box-column', 'wrong-index'] as $case) {
            $bad = $after; $index = 0;
            if ($case === 'hero-heading') { $bad['page']['blocks'][0]['heading'] = 'New heading'; }
            elseif ($case === 'box-type') { $bad['page']['blocks'][1]['type'] = 'usage-panels'; }
            elseif ($case === 'box-level') { $bad['page']['blocks'][1]['level'] = 'B2'; }
            elseif ($case === 'explicit-null-key') { $bad['page']['blocks'][1]['level'] = null; }
            elseif ($case === 'box-uuid-key') { $bad['page']['blocks'][1]['uuid_key'] = 'new-key'; }
            elseif ($case === 'box-column') { $bad['page']['blocks'][1]['column'] = 'right'; }
            else { $index = 1; }
            $this->refused(fn () => Patch::assertDefinitionScope($old, $bad, $target, $index), $case);
        }
        self::assertSame(['text_blocks' => ['body', 'heading'], 'pages' => ['text']], $this->service()->allowedFieldPolicy());
    }

    public function test_all_three_banks_six_raw_profiles_remain_exact_and_bank_paths_use_canonical_json(): void
    {
        self::assertSame('5fd7c5c02445d3cdf9488aefa7136c203b2c77b14767c5783484b01f42a0322c', Patch::BANK_SOURCE_CAPTURE_SHA);
        self::assertCount(6, Patch::BANK_SOURCE_PROFILES);
        self::assertCount(3, array_filter(Patch::BANK_SOURCE_PROFILES, static fn ($p) => $p['kind'] === 'definition'));
        self::assertCount(3, array_filter(Patch::BANK_SOURCE_PROFILES, static fn ($p) => $p['kind'] === 'wrapper'));
        self::assertSame(192, array_sum(array_column(Patch::BANK_SOURCE_PROFILES, 'question_count')));
        self::assertSame(['database/seeders/V3/Polyglot/PolyglotRelativeClausesWithWhoseAndWhichB2LessonSeeder/definition.json'], Patch::bankPaths($this->contract));
        $sources = [];
        foreach (Patch::BANK_SOURCE_PROFILES as $path => $profile) {
            foreach (['root' => 'D:/DEV/htdocs/gramlyze.loc', 'worktree' => Patch::SOURCE_WORKTREE] as $context => $root) {
                self::assertFileExists($root.'/'.$path); self::assertFalse(is_link($root.'/'.$path));
                $sources[$path][$context] = file_get_contents($root.'/'.$path);
            }
        }
        Patch::assertOriginalBankSourceBytes($sources); $unchanged = $sources; $first = array_key_first($sources);
        foreach (['missing', 'unknown', 'raw-eol', 'json-token', 'swapped'] as $case) {
            $bad = $sources;
            if ($case === 'missing') { unset($bad[$first]); }
            elseif ($case === 'unknown') { $bad['not-owned.json'] = $bad[$first]; }
            elseif ($case === 'raw-eol') {
                $raw = $bad[$first]['root']; $bad[$first]['root'] = str_contains($raw, "\r\n") ? str_replace("\r\n", "\n", $raw) : str_replace("\n", "\r\n", $raw);
            } elseif ($case === 'json-token') { $bad[$first]['worktree'] .= ' '; }
            else { $keys = array_keys($sources); $bad[$first]['root'] = $sources[$keys[2]]['root']; }
            $this->refused(fn () => Patch::assertOriginalBankSourceBytes($bad), $case);
        }
        self::assertSame($unchanged, $sources, 'No original source is normalized or repaired.');
    }

    public function test_b2_pool_is_exact_forty_eight_full_rows_not_alllevels_or_a_display_range(): void
    {
        $service = $this->service(); $plan = $service->plan(); $baseline = $plan['pages'][Package::OWNERS[0]];
        $questions = $baseline['relations']['questions']; self::assertCount(192, $questions);
        $eligible = array_values(array_filter($questions, static fn ($q) => $q['seeder'] === Package::BANKS[0] && $q['type'] === '4' && $q['level'] === 'B2'));
        usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid'])); self::assertCount(48, $eligible);
        $baseline['bank_inventory'][Package::BANKS[0]] = ['eligible_ids_uuid_order' => array_column($eligible, 'id')];
        $now = $this->snapshot(); $service->inspectPools($questions, $this->contract['targets'][0], $baseline);
        $service->inspectPools($questions, $this->contract['targets'][0], null);
        foreach (['missing', 'text', 'level', 'duplicate'] as $case) {
            $bad = $questions; $n = array_search(8002000, array_column($bad, 'id'), true);
            if ($case === 'missing') { unset($bad[$n]); }
            elseif ($case === 'text') { $bad[$n]['question'] .= ' changed'; }
            elseif ($case === 'level') { $bad[$n]['level'] = 'B1-B2'; }
            else { $bad[$n]['id'] = 8002001; }
            $this->refused(fn () => $service->inspectPools(array_values($bad), $this->contract['targets'][0], $baseline), $case);
        }
        foreach (['bank', 'level', 'owner'] as $case) {
            $bad = $this->contract['targets'][0];
            if ($case === 'bank') { $bad['bank_seeders'] = ['Database\\Seeders\\V3\\Polyglot\\PolyglotRelativeClausesWithWhoseAndWhichAllLevelsLessonSeeder']; }
            elseif ($case === 'level') { $bad['level'] = 'B1-B2'; }
            else { $bad['identity'] = 'Database\\Seeders\\Page_V3\\Unrelated'; }
            $this->refused(fn () => $service->inspectPools($questions, $bad, $baseline));
        }
        self::assertSame($now, $this->snapshot(), 'Pure pool inspection changes no family.');
    }

    public function test_digest_consistent_tampered_plans_cannot_add_widget_question_write_or_foreign_heading(): void
    {
        $service = $this->service(); $original = $service->savePlan($this->file('tamper-original')); $now = $this->snapshot();
        foreach (['extra-practice', 'question-write', 'foreign-heading'] as $case) {
            $plan = $original;
            if ($case === 'extra-practice') {
                $insert = $plan['inserts'][5]; $insert['fields']['uuid'] = 'm94-unapproved-widget';
                $insert['sha256'] = Patch::digest($insert['fields']); $plan['inserts'][] = $insert;
            } elseif ($case === 'question-write') {
                $old = ['question' => 'Synthetic isolated fixture; not a live question.']; $new = ['question' => 'Unapproved rewrite'];
                $plan['updates'][] = ['table' => 'questions', 'id' => 8002000, 'uuid' => 'm94-synthetic-8002000',
                    'seeder' => Package::BANKS[0], 'before' => $old, 'after' => $new,
                    'before_sha256' => Patch::digest($old), 'after_sha256' => Patch::digest($new)];
            } else {
                foreach ($plan['updates'] as &$change) {
                    if ($change['table'] === 'text_blocks' && $change['id'] === 8740) {
                        $change['after']['heading'] = 'Unapproved hero heading'; $change['after_sha256'] = Patch::digest($change['after']);
                    }
                }
                unset($change);
            }
            $this->writePlan($case, $plan);
            $this->refused(fn () => $service->apply($this->file($case), $this->file('no-'.$case)));
            self::assertSame($now, $this->snapshot()); self::assertFileDoesNotExist($this->file('no-'.$case));
        }
    }

    public function test_private_fixture_source_drift_and_restore_have_no_write_authority(): void
    {
        $service = $this->service(); $service->savePlan($this->file('source-preview')); $now = $this->snapshot();
        $path = $this->root.'/app/Support/AcceptedAnswerVariants.php'; $bytes = File::get($path);
        File::put($path, $bytes."\n// Synthetic private fixture drift; never executed.\n");
        $this->refused(fn () => $service->apply($this->file('source-preview'), $this->file('no-source')));
        self::assertSame($now, $this->snapshot()); self::assertFileDoesNotExist($this->file('no-source'));
        File::put($path, $bytes); $this->refused(fn () => $service->restore($this->file('not-authorized')));
        self::assertSame($now, $this->snapshot());
    }
}

class M94SyntheticContentPatch extends Patch
{
    public ?int $failUpdate = null;
    public ?int $failInsert = null;
    protected function afterUpdate(int $index): void { if ($this->failUpdate === $index) { throw new RuntimeException('Injected isolated update failure.'); } }
    protected function afterInsert(int $index): void { if ($this->failInsert === $index) { throw new RuntimeException('Injected isolated insert failure.'); } }
    public function allowedFieldPolicy(): array { return ['text_blocks' => $this->allowedUpdateFields(), 'pages' => $this->allowedPageUpdateFields()]; }
    public function inspectPools(array $questions, array $contract, ?array $baseline): void { $this->assertExistingPools($questions, $contract, $baseline); }
}
