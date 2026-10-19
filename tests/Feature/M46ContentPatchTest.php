<?php

namespace Tests\Feature;

use App\Services\M46ContentPatch as Patch;
use App\Support\Database\JsonPageSeeder;
use App\Support\M26DetailPackage;
use App\Support\M46ConditionalsPackage as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M46ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $evidence;
    private array $original;
    private array $package;
    private array $master;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->root = storage_path('app/m46-fixture-'.bin2hex(random_bytes(8)));
        $this->evidence = $this->root.'/evidence';
        File::makeDirectory($this->evidence, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($this->root);
        [$this->original, $this->package, $this->master] = Package::load();
        Package::validate($this->original, $this->package, $this->master);

        DB::table('page_categories')->insert([
            'slug' => 'conditionals', 'title' => 'Conditionals', 'type' => 'theory', 'language' => 'uk',
        ]);
        foreach (Patch::sourcePaths($this->original, $this->package) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true);
            File::copy(base_path($path), $this->root.'/'.$path);
        }
        // The DB fixture begins with the independent, pinned pre-M46 definitions, not generated AFTER data.
        foreach ($this->original['targets'] as $i => $target) {
            $path = $this->root.'/'.$target['path'];
            File::put($path, M26DetailPackage::json($target['before']));
            (new class($path) extends JsonPageSeeder {
                public function __construct(private string $path) {}
                protected function definitionPath(): string { return $this->path; }
            })->run();
            File::put($path, M26DetailPackage::json($this->package['targets'][$i]['after']));
        }
        $this->createProtectedFixtures();
    }

    private function createProtectedFixtures(): void
    {
        $target = (array) DB::table('text_blocks')->where('locale', 'uk')->where('type', 'forms-grid')->first();
        foreach (['en', 'pl'] as $locale) {
            $foreign = $target; unset($foreign['id']);
            $foreign['uuid'] = 'm46-protected-'.$locale; $foreign['locale'] = $locale;
            $foreign['body'] = 'Independent '.$locale.' content';
            DB::table('text_blocks')->insert($foreign);
        }
        $pageId = DB::table('pages')->insertGetId([
            'slug' => 'm46-protected-other-page', 'title' => 'Unchanged',
            'text' => 'Unchanged subtitle', 'type' => 'theory', 'seeder' => 'M46ProtectedOtherSeeder',
        ]);
        DB::table('text_blocks')->insert([
            'uuid' => 'm46-protected-other-block', 'page_id' => $pageId, 'locale' => 'uk',
            'body' => 'Unchanged neighbour', 'seeder' => 'M46ProtectedOtherSeeder',
        ]);
        $questionId = DB::table('questions')->insertGetId([
            'uuid' => 'm46-protected-question', 'question' => 'Protected question {a1}', 'type' => '4',
            'theory_text_block_uuid' => $target['uuid'],
        ]);
        $optionId = DB::table('question_options')->insertGetId(['option' => 'untouched answer']);
        DB::table('question_answers')->insert(['question_id' => $questionId, 'option_id' => $optionId, 'marker' => 'a1']);
        DB::table('question_option_question')->insert(['question_id' => $questionId, 'option_id' => $optionId, 'flag' => 1]);
        DB::table('verb_hints')->insert(['question_id' => $questionId, 'option_id' => $optionId, 'marker' => 'a1']);
        DB::table('question_hints')->insert([
            'question_id' => $questionId, 'provider' => 'fixture', 'locale' => 'uk', 'hint' => 'Не змінювати',
        ]);
        DB::table('question_theory_text_blocks')->insert([
            'question_uuid' => 'm46-protected-question', 'text_block_uuid' => $target['uuid'], 'position' => 0,
        ]);
        $testId = DB::table('saved_grammar_tests')->insertGetId([
            'uuid' => 'm46-protected-saved-test', 'name' => 'Existing test', 'slug' => 'm46-existing-test', 'filters' => '{}',
        ]);
        DB::table('saved_grammar_test_questions')->insert([
            'saved_grammar_test_id' => $testId, 'question_uuid' => 'm46-protected-question', 'position' => 0,
        ]);
        // Not in the base patch's historical table list: the subclass must protect actual tables.
        foreach (['m46_existing_sessions', 'm46_existing_progress', 'arbitrary_m46_protected'] as $table) {
            DB::connection()->getSchemaBuilder()->create($table, function (Blueprint $schema) {
                $schema->id(); $schema->text('value'); $schema->timestamps();
            });
            DB::table($table)->insert(['value' => 'unchanged', 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
        }
    }

    private function service(): Patch { return new Patch(DB::connection(), $this->root.'/database', $this->evidence); }
    private function path(string $name): string { return $this->evidence.'/'.$name.'.json'; }

    private function snapshot(): array
    {
        $out = [];
        $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false);
        sort($tables);
        foreach ($tables as $table) {
            $query = DB::table($table);
            $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $out[$table] = $query->get()->map(fn ($row) => (array) $row)->all();
        }
        return $out;
    }

    private function refusesWithoutDbChange(callable $operation): RuntimeException
    {
        $before = $this->snapshot(); $caught = null;
        try { $operation(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, 'Unsafe operation was accepted.');
        self::assertSame($before, $this->snapshot());
        return $caught;
    }

    private function protectedSnapshot(array $snapshot): array
    {
        $snapshot['text_blocks'] = array_values(array_filter($snapshot['text_blocks'],
            fn ($row) => $row['locale'] !== 'uk' || !in_array($row['seeder'], Package::OWNERS, true)));
        foreach ($snapshot['pages'] as &$row) {
            if (in_array($row['seeder'], Package::OWNERS, true)) { unset($row['text']); }
        }
        unset($row);
        return $snapshot;
    }

    public function test_preview_exact_content_diff_preserves_old_ids_and_all_protected_data_and_repeats_noop(): void
    {
        $patch = $this->service(); $before = $this->snapshot();
        $plan = $patch->savePlan($this->path('preview'));
        self::assertSame($before, $this->snapshot(), 'Preview must be read-only.');
        self::assertSame(Package::OWNERS, array_keys($plan['pages']));
        self::assertSame('before', $plan['state']);
        self::assertSame(array_keys($before), array_keys($plan['protected']));
        $oldBlocks = array_sum(array_map(fn ($target) => 1 + count($target['before']['page']['blocks']), $this->original['targets']));
        $newBlocks = array_sum(array_map(fn ($target) => count($target['after']['page']['blocks']), $this->package['targets']))
            - array_sum(array_map(fn ($target) => count($target['before']['page']['blocks']), $this->original['targets']));
        self::assertCount($oldBlocks, array_filter($plan['updates'], fn ($change) => $change['table'] === 'text_blocks'));
        self::assertCount(count(Package::OWNERS), array_filter($plan['updates'], fn ($change) => $change['table'] === 'pages'));
        self::assertCount($newBlocks, $plan['inserts']);
        foreach ($plan['updates'] as $change) {
            self::assertSame($change['table'] === 'pages' ? ['text'] : ['body'], array_keys($change['after']));
        }
        $expectedUuids = [];
        foreach ($this->package['targets'] as $i => $target) {
            $oldCount = count($this->original['targets'][$i]['before']['page']['blocks']);
            foreach ($target['after']['page']['blocks'] as $slot => $block) {
                if ($slot >= $oldCount) { $expectedUuids[] = M26DetailPackage::uuid($target['identity'], $block, $slot + 1); }
            }
        }
        self::assertSame($expectedUuids, array_column(array_column($plan['inserts'], 'fields'), 'uuid'));

        File::put($this->path('exists'), '{"keep":"exclusive"}');
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('exists')));
        self::assertSame('{"keep":"exclusive"}', File::get($this->path('exists')));
        $result = $patch->apply($this->path('preview'), $this->path('backup'));
        self::assertSame(['status' => 'applied', 'updated' => $oldBlocks + count(Package::OWNERS),
            'inserted' => $newBlocks, 'backup' => $this->path('backup')], $result);
        self::assertSame($plan, json_decode(File::get($this->path('backup')), true));

        $after = $this->snapshot();
        self::assertSame($this->protectedSnapshot($before), $this->protectedSnapshot($after));
        $oldById = array_column($before['text_blocks'], null, 'id');
        $actualById = array_column($after['text_blocks'], null, 'id');
        foreach ($oldById as $id => $old) {
            $actual = $actualById[$id]; unset($old['body'], $actual['body']);
            self::assertSame($old, $actual, 'Existing row identity, order and timestamps must remain unchanged.');
        }
        foreach ($this->package['targets'] as $target) {
            $owner = DB::table('pages')->where('seeder', $target['identity'])->first();
            self::assertSame($target['after']['page']['subtitle_text'], $owner->text);
            $rows = DB::table('text_blocks')->where('page_id', $owner->id)->where('locale', 'uk')->orderBy('sort_order')->get();
            self::assertSame([$target['after']['page']['subtitle_html'], ...array_column($target['after']['page']['blocks'], 'body')],
                $rows->pluck('body')->all());
        }
        $afterPlan = $patch->savePlan($this->path('after-preview'));
        self::assertSame($plan['protected'], $afterPlan['protected']);
        self::assertSame([], $afterPlan['updates']); self::assertSame([], $afterPlan['inserts']);
        foreach (['preview', 'after-preview'] as $preview) {
            self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0],
                $patch->apply($this->path($preview), $this->path('unused-'.$preview)));
            self::assertFileDoesNotExist($this->path('unused-'.$preview));
        }
        self::assertSame($after, $this->snapshot());
        $this->refusesWithoutDbChange(fn () => $patch->restore($this->path('backup')));
    }

    public function test_manual_or_mixed_owner_before_is_rejected_without_plan_or_backup(): void
    {
        $row = DB::table('text_blocks')->where('locale', 'uk')->where('seeder', Package::OWNERS[0])->where('sort_order', 1)->first();
        DB::table('text_blocks')->where('id', $row->id)->update(['body' => 'Newer manual content']);
        $this->refusesWithoutDbChange(fn () => $this->service()->savePlan($this->path('manual')));
        self::assertFileDoesNotExist($this->path('manual'));
        DB::table('text_blocks')->where('id', $row->id)->update(['body' => $this->package['targets'][0]['after']['page']['blocks'][0]['body']]);
        $this->refusesWithoutDbChange(fn () => $this->service()->savePlan($this->path('mixed')));
        self::assertFileDoesNotExist($this->path('mixed'));
    }

    public function test_stale_frozen_and_runtime_sources_or_foreign_locale_change_refuse_before_backup(): void
    {
        $patch = $this->service(); $patch->savePlan($this->path('preview'));
        foreach ([Package::MASTER, 'app/Services/M46ContentPatch.php'] as $path) {
            File::append($this->root.'/'.$path, "\n");
            $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('backup')));
            self::assertFileDoesNotExist($this->path('backup'));
            File::copy(base_path($path), $this->root.'/'.$path);
        }
        DB::table('text_blocks')->where('uuid', 'm46-protected-pl')->update(['body' => 'Concurrent localization edit']);
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_injected_failure_after_updates_or_inserts_rolls_back_every_actual_table(): void
    {
        foreach (['update', 'insert'] as $stage) {
            $patch = new class(DB::connection(), $this->root.'/database', $this->evidence, stage: $stage) extends Patch {
                public function __construct($db, string $databasePath, string $privateDirectory, private string $stage)
                { parent::__construct($db, $databasePath, $privateDirectory); }
                protected function afterUpdate(int $index): void
                { if ($this->stage === 'update' && $index === 2) { throw new RuntimeException('Injected update failure'); } }
                protected function afterInsert(int $index): void
                { if ($this->stage === 'insert' && $index === 1) { throw new RuntimeException('Injected insert failure'); } }
            };
            $plan = $patch->savePlan($this->path($stage.'-preview'));
            $this->refusesWithoutDbChange(fn () => $patch->apply($this->path($stage.'-preview'), $this->path($stage.'-backup')));
            self::assertSame($plan, json_decode(File::get($this->path($stage.'-backup')), true));
        }
    }

    public function test_non_target_transaction_side_effect_is_detected_and_rolled_back(): void
    {
        $patch = new class(DB::connection(), $this->root.'/database', $this->evidence) extends Patch {
            protected function afterUpdate(int $index): void
            {
                if ($index === 0) { $this->db->table('m46_existing_progress')->update(['value' => 'Unintended side effect']); }
            }
        };
        $patch->savePlan($this->path('preview'));
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('backup')));
        self::assertFileExists($this->path('backup'));
    }

    public function test_metadata_locale_and_production_profile_cannot_expand_write_authority(): void
    {
        $patch = $this->service(); $patch->savePlan($this->path('preview'));
        $row = DB::table('text_blocks')->where('locale', 'uk')->where('seeder', Package::OWNERS[0])->where('sort_order', 1)->first();
        DB::table('text_blocks')->where('id', $row->id)->update(['heading' => 'Manual metadata']);
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('metadata-backup')));
        self::assertFileDoesNotExist($this->path('metadata-backup'));
        DB::table('text_blocks')->where('id', $row->id)->update(['heading' => $row->heading]);
        DB::table('page_categories')->where('slug', 'conditionals')->update(['language' => 'pl']);
        $this->refusesWithoutDbChange(fn () => $patch->savePlan($this->path('foreign-category')));
        self::assertFileDoesNotExist($this->path('foreign-category'));
        DB::table('page_categories')->where('slug', 'conditionals')->update(['language' => 'uk']);
        $this->app->detectEnvironment(fn () => 'production');
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('production-backup')));
        self::assertFileDoesNotExist($this->path('production-backup'));
    }
}
