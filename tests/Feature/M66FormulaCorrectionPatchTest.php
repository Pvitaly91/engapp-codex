<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use App\Services\M66ContentPatch;
use App\Services\M66FormulaCorrectionPatch as Patch;
use App\Support\M26DetailPackage;
use App\Support\M66VerbPatternsCorePackage as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Exact applied-1.0.1 to final-1.0.2 body correction; isolated SQLite, never a working DB. */
class M66FormulaCorrectionPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $private;
    private array $before;
    private array $patch;
    private string $previousBody;
    private string $finalBody;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        [$this->before, $this->patch, $master, $contract] = Package::load();
        self::assertSame('1.0.2', $master['version']);
        self::assertSame(Patch::PREVIOUS_SOURCE_SHA, hash_file('sha256', base_path(Patch::PREVIOUS_SOURCE)));
        $previous = json_decode(File::get(base_path(Patch::PREVIOUS_SOURCE)), true, flags: JSON_THROW_ON_ERROR);
        $this->previousBody = $previous['targets'][0]['after']['page']['blocks'][1]['body'];
        $this->finalBody = $this->patch['targets'][0]['after']['page']['blocks'][1]['body'];
        self::assertSame(1, substr_count($this->previousBody, Patch::OLD_FORMULA));
        self::assertSame(str_replace(Patch::OLD_FORMULA, Patch::NEW_FORMULA, $this->previousBody), $this->finalBody);
        $this->root = storage_path('app/m66-formula-fixture-'.bin2hex(random_bytes(7)));
        $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app')); File::makeDirectory($this->private, 0700, true);
        foreach (Patch::sourcePaths($this->patch, $contract) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true);
            File::copy(base_path($path), $this->root.'/'.$path);
        }
        foreach ([[1, 'foreign-category'], [33, 'verb-patterns']] as [$id, $slug]) {
            DB::table('page_categories')->insert(['id' => $id, 'parent_id' => null, 'slug' => $slug,
                'title' => $slug, 'type' => 'theory', 'language' => 'uk']);
        }
        $rowId = 5755; $questionId = 6900000; $time = '2026-01-01 12:00:00';
        foreach ($this->patch['targets'] as $i => $target) {
            $source = $target['after']; $page = $source['page'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => 33,
                'seeder' => $target['identity'], 'slug' => $source['slug'], 'title' => $page['title'],
                'text' => $page['subtitle_text'], 'type' => 'theory']);
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $page['subtitle_html'],
                'level' => $page['subtitle_level'] ?? null, 'uuid_key' => $page['subtitle_uuid_key'] ?? 'subtitle'];
            foreach ([$subtitle, ...$page['blocks']] as $position => $config) {
                $uuid = M26DetailPackage::uuid($target['identity'], $config, $position);
                $body = $i === 0 && $position === 2 ? $this->previousBody : $config['body'];
                DB::table('text_blocks')->insert(['id' => $rowId++, 'uuid' => $uuid,
                    'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => 33, 'locale' => 'uk',
                    'seeder' => $target['identity'], 'sort_order' => $position, 'type' => $config['type'],
                    'column' => $config['column'], 'heading' => $config['heading'] ?? null,
                    'level' => $config['level'] ?? null, 'css_class' => $config['css_class'] ?? null,
                    'body' => $body, 'created_at' => $time, 'updated_at' => $time]);
            }
            $heroUuid = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->value('uuid');
            foreach ($contract['targets'][$i]['bank_seeders'] as $bank) {
                for ($n = 0; $n < 5; ++$n) {
                    $uuid = 'm66-formula-synthetic-bank-'.$questionId;
                    DB::table('questions')->insert(['id' => $questionId++, 'uuid' => $uuid, 'question' => 'Ізольоване питання.',
                        'level' => Package::LEVELS[$i], 'difficulty' => $i === 0 ? 2 : 3, 'type' => '4', 'seeder' => $bank,
                        'created_at' => $time, 'updated_at' => $time]);
                    DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $heroUuid,
                        'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
                }
            }
            $foreign = (array) DB::table('text_blocks')->where('uuid', $heroUuid)->sole();
            foreach (['en', 'pl'] as $locale) {
                $foreign['id'] = $rowId++; $foreign['uuid'] = 'm66-formula-foreign-'.$i.'-'.$locale;
                $foreign['locale'] = $locale; $foreign['body'] = 'Protected '.$locale;
                DB::table('text_blocks')->insert($foreign);
            }
        }
        self::assertSame(Patch::TARGET_UUID, DB::table('text_blocks')->where('id', Patch::TARGET_ID)->value('uuid'));
        DB::connection()->getSchemaBuilder()->create('m66_formula_protected_progress', static function (Blueprint $table): void {
            $table->id(); $table->text('value');
        });
        DB::table('m66_formula_protected_progress')->insert(['value' => 'Exact synthetic progress.']);
    }

    private function service(): M66SyntheticFormulaCorrectionPatch
    {
        return new M66SyntheticFormulaCorrectionPatch(DB::connection(), $this->root.'/database', $this->private);
    }
    private function file(string $name): string { return $this->private.'/'.$name.'.json'; }
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
    private function refused(callable $call): void
    {
        try { $call(); self::fail('Expected exact M66 formula-correction refusal.'); }
        catch (RuntimeException $error) { self::assertNotSame('', $error->getMessage()); }
    }

    public function test_exact_one_body_plan_is_read_only_and_inherited_apply_backup_and_repeated_noop_preserve_every_other_byte(): void
    {
        $service = $this->service(); $before = $this->snapshot();
        $models = [Page::findOrFail(193), TextBlock::findOrFail(Patch::TARGET_ID)];
        $attributes = array_map(static fn ($model) => $model->getAttributes(), $models);
        DB::connection()->flushQueryLog(); DB::connection()->enableQueryLog();
        $plan = $service->plan(); $queries = DB::connection()->getQueryLog(); DB::connection()->disableQueryLog();
        foreach ($queries as $query) { self::assertDoesNotMatchRegularExpression('/^\s*(insert|update|delete|replace|create|drop|alter|vacuum|attach|detach)\b/i', $query['query']); }
        self::assertSame($before, $this->snapshot()); self::assertSame($attributes, array_map(static fn ($model) => $model->getAttributes(), $models));
        self::assertSame('before', $plan['state']); self::assertSame('m66-verb-patterns-core-formula-correction-v1', $plan['patch']);
        self::assertCount(1, $plan['updates']); self::assertSame([], $plan['inserts']);
        self::assertSame(Patch::PREVIOUS_SOURCE_SHA, $plan['sources'][Patch::PREVIOUS_SOURCE]);
        $change = $plan['updates'][0]; self::assertSame('text_blocks', $change['table']); self::assertSame(Patch::TARGET_ID, $change['id']);
        self::assertSame(Patch::TARGET_UUID, $change['uuid']); self::assertSame(Package::OWNERS[0], $change['seeder']);
        self::assertSame(['body' => $this->previousBody], $change['before']); self::assertSame(['body' => $this->finalBody], $change['after']);
        $ordinary = new M66ContentPatch(DB::connection(), $this->root.'/database', $this->private);
        $this->refused(fn () => $ordinary->plan()); // The normal planner never accepts this intermediate applied release.
        self::assertSame($plan, $service->savePlan($this->file('preview')));
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame('applied', $result['status']); self::assertSame(1, $result['updated']); self::assertSame(0, $result['inserted']);
        self::assertSame($plan, json_decode(File::get($this->file('backup')), true, flags: JSON_THROW_ON_ERROR));
        $expected = $before;
        foreach ($expected['text_blocks'] as &$row) { if ($row['id'] === Patch::TARGET_ID) { $row['body'] = $this->finalBody; } } unset($row);
        self::assertSame($expected, $this->snapshot(), 'Only the one body value changed, including no timestamp/metadata/progress changes.');
        self::assertSame($attributes, array_map(static fn ($model) => $model->getAttributes(), $models), 'Original model instances were not modified.');
        $after = $service->savePlan($this->file('after'));
        self::assertSame('after', $after['state']); self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']);
        self::assertSame($plan['protected'], $after['protected']); self::assertSame('after', $ordinary->plan()['state']);
        self::assertSame('no-op', $service->apply($this->file('preview'), $this->file('unused-old'))['status']);
        self::assertSame('no-op', $service->apply($this->file('after'), $this->file('unused-after'))['status']);
        self::assertFileDoesNotExist($this->file('unused-old')); self::assertFileDoesNotExist($this->file('unused-after'));
    }

    public function test_exclusive_backup_and_transaction_failure_never_leave_a_partial_correction(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview')); $before = $this->snapshot();
        File::put($this->file('existing'), 'Existing private backup.');
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('existing')));
        self::assertSame('Existing private backup.', File::get($this->file('existing'))); self::assertSame($before, $this->snapshot());
        $service->failAfterUpdate = true;
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('rollback')));
        self::assertSame($before, $this->snapshot()); self::assertSame('before', $service->plan()['state']);
    }

    public function test_stale_progress_manual_body_and_changed_immutable_source_refuse_before_backup(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        DB::table('m66_formula_protected_progress')->where('id', 1)->update(['value' => 'Later progress.']);
        $current = $this->snapshot(); $this->refused(fn () => $service->apply($this->file('preview'), $this->file('stale-progress')));
        self::assertSame($current, $this->snapshot()); self::assertFileDoesNotExist($this->file('stale-progress'));
        DB::table('m66_formula_protected_progress')->where('id', 1)->update(['value' => 'Exact synthetic progress.']);
        DB::table('text_blocks')->where('id', Patch::TARGET_ID)->update(['body' => $this->previousBody.' ']);
        $current = $this->snapshot(); $this->refused(fn () => $service->apply($this->file('preview'), $this->file('stale-body')));
        self::assertSame($current, $this->snapshot()); self::assertFileDoesNotExist($this->file('stale-body'));
        DB::table('text_blocks')->where('id', Patch::TARGET_ID)->update(['body' => $this->previousBody]);
        $path = $this->root.'/'.Patch::PREVIOUS_SOURCE; File::put($path, File::get($path)."\n");
        $current = $this->snapshot(); $this->refused(fn () => $service->apply($this->file('preview'), $this->file('stale-source')));
        self::assertSame($current, $this->snapshot()); self::assertFileDoesNotExist($this->file('stale-source'));
    }

    public function test_foreign_identity_metadata_partial_owner_and_missing_practice_are_never_corrected(): void
    {
        $service = $this->service(); $original = (array) DB::table('text_blocks')->where('id', Patch::TARGET_ID)->sole();
        foreach (['id' => 9900000, 'uuid' => 'foreign-m66-formula', 'seeder' => 'ForeignOwner', 'locale' => 'en',
            'page_id' => 199, 'page_category_id' => 1, 'sort_order' => 99, 'type' => 'box', 'column' => 'right', 'level' => 'C2', 'heading' => 'Manual heading'] as $field => $value) {
            DB::table('text_blocks')->where('id', Patch::TARGET_ID)->update([$field => $value]);
            $current = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($current, $this->snapshot(), $field);
            DB::table('text_blocks')->where('id', $field === 'id' ? $value : Patch::TARGET_ID)->update([$field => $original[$field]]);
        }
        self::assertCount(1, $service->plan()['updates'], 'Private pending state resets after each rejected plan.');
        $hero = DB::table('text_blocks')->where('page_id', 196)->where('locale', 'uk')->where('sort_order', 1)->sole();
        DB::table('text_blocks')->where('id', $hero->id)->update(['body' => $this->before['targets'][1]['before']['page']['blocks'][0]['body']]);
        $current = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($current, $this->snapshot());
        DB::table('text_blocks')->where('id', $hero->id)->update(['body' => $hero->body]);
        DB::table('text_blocks')->where('page_id', 199)->where('locale', 'uk')->where('sort_order', 7)->delete();
        $current = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($current, $this->snapshot());
    }

    public function test_original_pre_m66_release_cannot_use_the_correction_as_an_initial_apply(): void
    {
        foreach ($this->before['targets'] as $i => $target) {
            $old = $target['before']['page'];
            DB::table('pages')->where('id', Package::PAGE_IDS[$i])->update(['text' => $old['subtitle_text']]);
            DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('locale', 'uk')->where('sort_order', 7)->delete();
            foreach ([$old['subtitle_html'], ...array_column($old['blocks'], 'body')] as $position => $body) {
                DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('locale', 'uk')->where('sort_order', $position)->update(['body' => $body]);
            }
        }
        $current = $this->snapshot(); $this->refused(fn () => $this->service()->plan()); self::assertSame($current, $this->snapshot());
    }
}

class M66SyntheticFormulaCorrectionPatch extends Patch
{
    public bool $failAfterUpdate = false;
    protected function afterUpdate(int $index): void
    {
        if ($this->failAfterUpdate && $index === 0) { throw new RuntimeException('Injected formula correction failure.'); }
    }
}
