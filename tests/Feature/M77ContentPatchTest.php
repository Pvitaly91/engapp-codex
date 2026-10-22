<?php

namespace Tests\Feature;

use App\Services\M77ContentPatch as Patch;
use App\Support\M26DetailPackage;
use App\Support\M77OnePrepositionsPhrasal as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Inherited transaction/backup on isolated SQLite; synthetic banks are not live acceptance. */
class M77ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $private;
    private array $patch;
    private array $before;
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
        $this->rebuildComposeTestSchema();
        app()->setLocale('uk');
        $sourceRoot = str_replace('\\', '/', dirname(__DIR__, 2));
        [$this->before, $this->patch, , $this->contract] = Package::load($sourceRoot);
        self::assertSame([123, 120, 119, 118, 115, 116], Package::PAGE_IDS);
        self::assertSame([20, 19, 19, 19, 19, 19], Package::CATEGORY_IDS);
        self::assertSame([11, 6, 6, 6, 6, 6], Package::CONTINUING_COUNTS);
        self::assertSame([null, 'A1', 'A1', 'A2', 'B1', 'B1'], Package::LEVELS);
        $this->root = storage_path('app/m77-native-fixture-'.bin2hex(random_bytes(7)));
        $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        File::makeDirectory($this->private, 0700, true);
        foreach (Patch::sourcePaths($this->patch, $this->contract, $sourceRoot) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true);
            File::copy($sourceRoot.'/'.$path, $this->root.'/'.$path);
        }
        foreach ([[1, 'foreign-category'], [19, 'prepositions-and-phrasal-verbs'], [20, 'zaimennyky-ta-vkazivni-slova']] as [$id, $slug]) {
            DB::table('page_categories')->insert(['id' => $id, 'parent_id' => null, 'slug' => $slug,
                'title' => $slug, 'type' => 'theory', 'language' => 'uk']);
        }
        $time = '2026-01-01 12:00:00';
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i],
                'seeder' => $target['identity'], 'slug' => $old['slug'], 'title' => $old['page']['title'],
                'text' => $old['page']['subtitle_text'], 'type' => 'theory']);
            $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $old['page']['subtitle_html'],
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle']];
            foreach ($old['page']['blocks'] as $slot => $config) { $configs[$slot + 1] = $config; }
            foreach ($configs as $position => $config) {
                DB::table('text_blocks')->insert(['uuid' => M26DetailPackage::uuid($target['identity'], $config, $position),
                    'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i], 'locale' => 'uk',
                    'seeder' => $target['identity'], 'sort_order' => $position, 'type' => $config['type'],
                    'column' => $config['column'], 'heading' => $config['heading'] ?? null, 'level' => $config['level'] ?? null,
                    'css_class' => $config['css_class'] ?? null, 'body' => $config['body'], 'created_at' => $time, 'updated_at' => $time]);
            }
            $hero = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->value('uuid');
            foreach ($this->contract['targets'][$i]['bank_seeders'] as $bank) {
                for ($n = 0; $n < ($i === 0 ? 5 : 6); ++$n) {
                    $questionId = 7700000 + $i * 10 + $n;
                    $uuid = 'm77-synthetic-bank-'.$questionId;
                    $level = $i === 0 ? ['A1', 'A2', 'B1', 'B2', 'C2'][$n] : ($n < 5 ? Package::LEVELS[$i] : 'C2');
                    DB::table('questions')->insert(['id' => $questionId, 'uuid' => $uuid, 'question' => 'Ізольований fixture, не live selected question.',
                        'difficulty' => 2, 'level' => $level, 'type' => '4', 'seeder' => $bank, 'created_at' => $time, 'updated_at' => $time]);
                    DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $hero,
                        'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
                }
            }
            $foreign = (array) DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->sole();
            foreach (['en', 'pl'] as $locale) {
                unset($foreign['id']);
                $foreign['uuid'] = 'm77-nontarget-'.$i.'-'.$locale;
                $foreign['locale'] = $locale;
                $foreign['body'] = 'Unchanged '.$locale;
                DB::table('text_blocks')->insert($foreign);
            }
        }
        DB::connection()->getSchemaBuilder()->create('m77_protected_progress', static function (Blueprint $table): void { $table->id(); $table->text('value'); });
        DB::table('m77_protected_progress')->insert(['value' => 'Unchanged synthetic progress.']);
    }

    private function service(): M77SyntheticNativePatch { return new M77SyntheticNativePatch(DB::connection(), $this->root.'/database', $this->private); }
    private function file(string $name): string { return $this->private.'/'.$name.'.json'; }
    private function practiceUuid(int $i): string
    {
        $slot = Package::PRACTICE_SOURCE_INDICES[$i];
        return M26DetailPackage::uuid(Package::OWNERS[$i], $this->patch['targets'][$i]['after']['page']['blocks'][$slot], $slot + 1);
    }

    private function snapshot(): array
    {
        $out = [];
        $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false);
        sort($tables);
        foreach ($tables as $table) {
            if (str_starts_with($table, 'sqlite_')) { continue; }
            $query = DB::table($table);
            $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $out[$table] = $query->get()->map(static fn ($row) => (array) $row)->all();
        }
        return $out;
    }

    private function refused(callable $callback): void
    {
        $error = null;
        try { $callback(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error);
    }

    private function expectedCounts(): array
    {
        $counts = ['pages' => 0, 'blocks' => 0, 'inserts' => 0, 'metadata_rows' => 0, 'metadata_fields' => 0];
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before']['page'];
            $new = $target['after']['page'];
            if ($old['subtitle_text'] !== $new['subtitle_text']) { ++$counts['pages']; }
            if ($old['subtitle_html'] !== $new['subtitle_html']) { ++$counts['blocks']; }
            foreach ($old['blocks'] as $slot => $source) {
                $metadata = 0;
                foreach (['type', 'heading'] as $field) {
                    if (($source[$field] ?? null) !== ($new['blocks'][$slot][$field] ?? null)) { ++$metadata; }
                }
                if ($source['body'] !== $new['blocks'][$slot]['body'] || $metadata) { ++$counts['blocks']; }
                if ($metadata) { ++$counts['metadata_rows']; $counts['metadata_fields'] += $metadata; }
            }
            $counts['inserts'] += count($new['blocks']) - count($old['blocks']);
        }
        return $counts;
    }

    public function test_exact_six_owner_transaction_has_only_five_practice_inserts_and_preserves_every_old_row_and_timestamp(): void
    {
        $service = $this->service();
        $before = $this->snapshot();
        $plan = $service->savePlan($this->file('preview'));
        self::assertSame($before, $this->snapshot());
        self::assertSame('before', $plan['state']);
        $counts = $this->expectedCounts();
        self::assertSame(6, $counts['pages']);
        self::assertSame(5, $counts['inserts']);
        self::assertSame(29, $counts['metadata_rows']);
        self::assertSame(57, $counts['metadata_fields']);
        self::assertCount($counts['pages'] + $counts['blocks'], $plan['updates']);
        self::assertCount(5, $plan['inserts']);
        self::assertSame([], $plan['deletes'] ?? []);
        self::assertSame([0, 0, 0, 0, 0, 0], array_map(static fn ($id) => DB::table('text_blocks')->where('page_id', $id)->where('locale', 'uk')->where('type', 'practice-set')->count(), Package::PAGE_IDS));
        foreach ($plan['updates'] as $update) {
            $fields = $update['table'] === 'pages' ? ['text'] : ['body', 'type', 'heading'];
            self::assertSame($fields, array_keys($update['before']));
            self::assertSame($fields, array_keys($update['after']));
        }
        foreach ($plan['inserts'] as $insert) {
            self::assertSame('text_blocks', $insert['table']);
            self::assertSame('practice-set', $insert['fields']['type']);
            self::assertNotSame(Package::OWNERS[0], $insert['seeder']);
        }
        $oldPractice = (array) DB::table('text_blocks')->where('uuid', $this->practiceUuid(0))->sole();
        self::assertSame('3e5975ed-f03b-5639-b9fe-f5f0f051f734', $oldPractice['uuid']);
        self::assertSame(9, $oldPractice['sort_order']);
        self::assertNull($oldPractice['level']);
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame('applied', $result['status']);
        self::assertSame(5, $result['inserted']);
        self::assertCount($result['updated'], $plan['updates']);
        $afterPlan = $service->savePlan($this->file('after'));
        self::assertSame('after', $afterPlan['state']);
        self::assertSame([], $afterPlan['updates']);
        self::assertSame([], $afterPlan['inserts']);
        self::assertSame($plan['protected'], $afterPlan['protected']);
        $after = $this->snapshot();
        self::assertCount(count($before['text_blocks']) + 5, $after['text_blocks']);
        foreach ($before['text_blocks'] as $row) {
            $now = (array) DB::table('text_blocks')->where('id', $row['id'])->sole();
            if ($row['locale'] === 'uk') {
                $i = array_search($row['page_id'], Package::PAGE_IDS, true);
                $page = $this->patch['targets'][$i]['after']['page'];
                $expected = $row['sort_order'] === 0 ? ['body' => $page['subtitle_html'], 'type' => 'subtitle', 'heading' => null] : $page['blocks'][$row['sort_order'] - 1];
                foreach (['body', 'type', 'heading'] as $field) {
                    self::assertSame($expected[$field] ?? null, $now[$field]);
                    unset($row[$field], $now[$field]);
                }
            }
            self::assertSame($row, $now, 'Every existing UUID/id/column/order/level/CSS/EN/PL/timestamp remains exact.');
        }
        foreach ($before['pages'] as $row) {
            $now = (array) DB::table('pages')->where('id', $row['id'])->sole();
            unset($row['text'], $now['text']);
            self::assertSame($row, $now);
        }
        foreach ($before as $table => $rows) {
            if (!in_array($table, ['pages', 'text_blocks'], true)) { self::assertSame($rows, $after[$table], $table.' unchanged, including every question-family/progress/relation row'); }
        }
        foreach (Package::PAGE_IDS as $i => $id) {
            $practice = DB::table('text_blocks')->where('page_id', $id)->where('locale', 'uk')->where('type', 'practice-set')->sole();
            self::assertSame($this->practiceUuid($i), $practice->uuid);
            self::assertSame(Package::LEVELS[$i], $practice->level);
            self::assertSame(Package::PRACTICE_SOURCE_INDICES[$i] + 1, $practice->sort_order);
        }
        foreach (['preview', 'after'] as $name) {
            self::assertSame('no-op', $service->apply($this->file($name), $this->file('unused-'.$name))['status']);
            self::assertFileDoesNotExist($this->file('unused-'.$name));
            self::assertSame($after, $this->snapshot());
        }
    }

    public function test_failures_after_first_last_update_and_first_last_insert_roll_back_the_whole_release(): void
    {
        foreach (['first-update', 'last-update', 'first-insert', 'last-insert'] as $stage) {
            $service = $this->service();
            $plan = $service->savePlan($this->file('preview-'.$stage));
            $before = $this->snapshot();
            if (str_ends_with($stage, 'update')) { $service->failAfterUpdateIndex = str_starts_with($stage, 'first') ? 0 : count($plan['updates']) - 1; }
            else { $service->failAfterInsertIndex = str_starts_with($stage, 'first') ? 0 : count($plan['inserts']) - 1; }
            $this->refused(fn () => $service->apply($this->file('preview-'.$stage), $this->file('backup-'.$stage)));
            self::assertSame($before, $this->snapshot());
            self::assertSame($plan, $service->plan());
            self::assertFileExists($this->file('backup-'.$stage));
        }
    }

    public function test_duplicate_old_practice_colliding_append_and_partial_owner_are_refused_without_any_write(): void
    {
        $service = $this->service();
        $plan = $service->plan();
        $duplicate = (array) DB::table('text_blocks')->where('uuid', $this->practiceUuid(0))->sole();
        unset($duplicate['id']);
        $duplicate['uuid'] = 'm77-duplicate-practice';
        DB::table('text_blocks')->insert($duplicate);
        $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan());
        self::assertSame($snapshot, $this->snapshot());
        DB::table('text_blocks')->where('uuid', 'm77-duplicate-practice')->delete();
        $collision = $plan['inserts'][0]['fields'];
        DB::table('text_blocks')->insert($collision);
        $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan());
        self::assertSame($snapshot, $this->snapshot());
        DB::table('text_blocks')->where('uuid', $collision['uuid'])->delete();
        foreach ($plan['updates'] as $update) {
            if ($update['seeder'] === Package::OWNERS[0]) { DB::table($update['table'])->where('id', $update['id'])->update($update['after']); }
        }
        $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan());
        self::assertSame($snapshot, $this->snapshot());
    }

    public function test_missing_one_of_six_owners_foreign_category_and_continuing_level_or_identity_are_refused(): void
    {
        $service = $this->service();
        $page = (array) DB::table('pages')->where('id', 116)->sole();
        foreach (['seeder' => 'Foreign\\Owner', 'page_category_id' => 1, 'slug' => 'foreign', 'type' => 'category'] as $field => $value) {
            DB::table('pages')->where('id', 116)->update([$field => $value]);
            $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan());
            self::assertSame($snapshot, $this->snapshot());
            DB::table('pages')->where('id', 116)->update([$field => $page[$field]]);
        }
        $category = (array) DB::table('page_categories')->where('id', 19)->sole();
        foreach (['language' => 'en', 'slug' => 'foreign', 'parent_id' => 1, 'type' => 'page'] as $field => $value) {
            DB::table('page_categories')->where('id', 19)->update([$field => $value]);
            $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan());
            self::assertSame($snapshot, $this->snapshot());
            DB::table('page_categories')->where('id', 19)->update([$field => $category[$field]]);
        }
        $row = (array) DB::table('text_blocks')->where('uuid', $this->practiceUuid(0))->sole();
        foreach (['uuid' => 'm77-lost-uuid', 'level' => 'A2', 'locale' => 'en', 'column' => 'right',
            'sort_order' => 99, 'type' => 'practice-set', 'heading' => 'Later heading'] as $field => $value) {
            DB::table('text_blocks')->where('id', $row['id'])->update([$field => $value]);
            $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan());
            self::assertSame($snapshot, $this->snapshot());
            DB::table('text_blocks')->where('id', $row['id'])->update([$field => $row[$field]]);
        }
    }

    public function test_stale_body_progress_source_unknown_tail_and_existing_backup_do_not_authorize_writes(): void
    {
        $service = $this->service();
        $service->savePlan($this->file('preview'));
        $row = (array) DB::table('text_blocks')->where('uuid', $this->practiceUuid(0))->sole();
        DB::table('text_blocks')->where('id', $row['id'])->update(['body' => $row['body']."\n"]);
        $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-body')));
        self::assertSame($snapshot, $this->snapshot());
        self::assertFileDoesNotExist($this->file('not-created-body'));
        DB::table('text_blocks')->where('id', $row['id'])->update(['body' => $row['body']]);
        DB::table('m77_protected_progress')->where('id', 1)->update(['value' => 'Later synthetic progress.']);
        $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-progress')));
        self::assertSame($snapshot, $this->snapshot());
        self::assertFileDoesNotExist($this->file('not-created-progress'));
        DB::table('m77_protected_progress')->where('id', 1)->update(['value' => 'Unchanged synthetic progress.']);
        $source = $this->root.'/'.$this->patch['targets'][0]['path'];
        $bytes = File::get($source);
        File::put($source, $bytes."\n");
        $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-source')));
        self::assertSame($snapshot, $this->snapshot());
        self::assertFileDoesNotExist($this->file('not-created-source'));
        File::put($source, $bytes);
        File::put($this->file('existing-backup'), 'Pre-existing private backup.');
        $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('existing-backup')));
        self::assertSame('Pre-existing private backup.', File::get($this->file('existing-backup')));
        self::assertSame($snapshot, $this->snapshot());
        unset($row['id']);
        $row['uuid'] = 'm77-unregistered-tail';
        $row['sort_order'] = 99;
        DB::table('text_blocks')->insert($row);
        $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan());
        self::assertSame($snapshot, $this->snapshot());
    }

    public function test_one_null_pool_keeps_all_levels_but_other_five_exact_filters_do_not_use_display_ranges(): void
    {
        $service = $this->service();
        $before = $this->snapshot();
        $plan = $service->plan();
        self::assertSame($before, $this->snapshot());
        $method = new ReflectionMethod(Patch::class, 'assertExistingPools');
        foreach (Package::OWNERS as $i => $owner) {
            $questions = $plan['pages'][$owner]['relations']['questions'];
            $contract = $this->contract['targets'][$i];
            $method->invoke($service, $questions, $contract, ['relations' => ['questions' => $questions]]);
            if ($i === 0) {
                self::assertNull($contract['level']);
                self::assertContains('A1', array_column($questions, 'level'));
                self::assertContains('C2', array_column($questions, 'level'));
            } else {
                $filtered = array_values(array_filter($questions, static fn ($row) => $row['level'] === Package::LEVELS[$i]));
                self::assertCount(5, $filtered);
                self::assertCount(6, $questions);
            }
            $stale = $questions;
            $stale[0]['updated_at'] = '2026-01-02 12:00:00';
            $this->refused(fn () => $method->invoke($service, $stale, $contract, ['relations' => ['questions' => $questions]]));
            $range = $contract;
            $range['level'] = Package::DISPLAY_LEVELS[$i];
            $this->refused(fn () => $method->invoke($service, $questions, $range, null));
        }
        self::assertSame($before, $this->snapshot());
    }

    public function test_foreign_type_bank_or_unlinked_candidate_and_four_eligible_plus_one_offlevel_are_short(): void
    {
        $service = $this->service();
        $question = (array) DB::table('questions')->where('id', 7700014)->sole();
        foreach (['type' => '0', 'seeder' => 'Foreign\\Bank', 'level' => 'C2'] as $field => $value) {
            DB::table('questions')->where('id', $question['id'])->update([$field => $value]);
            $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan());
            self::assertSame($snapshot, $this->snapshot());
            DB::table('questions')->where('id', $question['id'])->update([$field => $question[$field]]);
        }
        DB::table('question_theory_text_blocks')->where('question_uuid', $question['uuid'])->delete();
        $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan());
        self::assertSame($snapshot, $this->snapshot());
    }

    public function test_continuing_practice_relations_stay_visible_and_new_practice_relation_drift_is_still_protected(): void
    {
        $service = $this->service();
        $service->savePlan($this->file('preview'));
        $service->apply($this->file('preview'), $this->file('backup'));
        $method = new ReflectionMethod(Patch::class, 'relations');
        foreach (Package::PAGE_IDS as $i => $id) {
            $page = (array) DB::table('pages')->where('id', $id)->sole();
            $rows = DB::table('text_blocks')->where('page_id', $id)->get()->map(static fn ($row) => (array) $row)->all();
            $before = $method->invoke($service, $page, $rows, false);
            $questionUuid = $before['questions'][0]['uuid'];
            DB::table('question_theory_text_blocks')->insert(['question_uuid' => $questionUuid,
                'text_block_uuid' => $this->practiceUuid($i), 'position' => 9]);
            $after = $method->invoke($service, $page, $rows, false);
            if ($i === 0) {
                self::assertCount(count($before['question_theory_text_blocks']) + 1, $after['question_theory_text_blocks']);
            } else {
                self::assertSame($before, $after, 'Only fully exact new practice rows are omitted from the old-consumer relation projection.');
            }
            $snapshot = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('unused-relations-'.$i)));
            self::assertSame($snapshot, $this->snapshot());
            self::assertFileDoesNotExist($this->file('unused-relations-'.$i));
            DB::table('question_theory_text_blocks')->where('question_uuid', $questionUuid)->where('text_block_uuid', $this->practiceUuid($i))->delete();
        }
    }

    public function test_cli_refuses_ambient_or_foreign_authority_before_any_real_target_access(): void
    {
        $before = $this->snapshot();
        foreach ([[], ['--local-target' => 'gramlyze.ub', '--local-proof' => 'local-proof-'.str_repeat('a', 32).'.json'],
            ['--local-target' => 'gramlyze.loc', '--local-proof' => '../proof.json'],
            ['--local-target' => 'gramlyze.loc', '--local-proof' => 'local-proof-'.str_repeat('a', 32).'.json', '--backup' => 'without-apply.json']] as $arguments) {
            $this->artisan('content:patch-one-prepositions-phrasal-m77', $arguments)->assertExitCode(1);
            self::assertSame($before, $this->snapshot());
        }
    }
}

class M77SyntheticNativePatch extends Patch
{
    public ?int $failAfterUpdateIndex = null;
    public ?int $failAfterInsertIndex = null;

    protected function afterUpdate(int $index): void
    {
        if ($this->failAfterUpdateIndex === $index) { throw new RuntimeException('Injected isolated update failure.'); }
    }

    protected function afterInsert(int $index): void
    {
        if ($this->failAfterInsertIndex === $index) { throw new RuntimeException('Injected isolated insert failure.'); }
    }
}
