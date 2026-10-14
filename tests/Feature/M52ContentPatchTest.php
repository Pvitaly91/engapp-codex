<?php

namespace Tests\Feature;

use App\Services\M52ContentPatch as Patch;
use App\Support\M26DetailPackage;
use App\Support\M52PassiveBasicsPackage as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Unchanged inherited transaction/backup exercised against independent, isolated synthetic SQL. */
class M52ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $evidence;
    private array $before;
    private array $patch;
    private array $contract;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        [$this->before, $this->patch, $master, $this->contract] = Package::load();
        Package::validate($this->before, $this->patch, $master, $this->contract);
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $this->root = storage_path('app/m52-patch-fixture-'.bin2hex(random_bytes(8)));
        $this->evidence = $this->root.'/evidence';
        File::makeDirectory($this->evidence, 0700, true); IsolatedTestEnvironment::assertOwnedPath($this->root);
        foreach (Patch::sourcePaths($this->patch, $this->contract) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true);
            File::copy(base_path($path), $this->root.'/'.$path);
        }
        DB::table('page_categories')->insert(['id' => 1, 'slug' => 'passive-voice', 'title' => 'Пасивний стан',
            'type' => 'theory', 'language' => 'uk', 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before']; $owner = $target['identity'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => 1, 'seeder' => $owner,
                'slug' => $old['slug'], 'title' => $old['page']['title'], 'text' => $old['page']['subtitle_text'], 'type' => 'theory',
                'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
            $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $old['page']['subtitle_html'],
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'],
                ...$old['page']['blocks']];
            foreach ($configs as $position => $source) {
                $fields = ['uuid' => M26DetailPackage::uuid($owner, $source, $position),
                    'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => 1, 'locale' => 'uk',
                    'type' => $source['type'], 'column' => $source['column'], 'heading' => $source['heading'] ?? null,
                    'css_class' => $source['css_class'] ?? null, 'sort_order' => $position, 'level' => $source['level'] ?? null,
                    'body' => $source['body'], 'seeder' => $owner,
                    'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00'];
                DB::table('text_blocks')->insert($fields);
                if ($position === 0) { continue; }
                foreach (['en', 'pl'] as $locale) {
                    DB::table('text_blocks')->insert(array_replace($fields, [
                        'uuid' => 'm52-protected-'.$i.'-'.$position.'-'.$locale, 'locale' => $locale,
                        'body' => 'Synthetic protected '.$locale.' source '.$i.' slot '.$position,
                    ]));
                }
            }
            File::put($this->root.'/'.$target['path'], M26DetailPackage::json($target['after']));
            $block = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('locale', 'uk')->where('sort_order', 1)->sole();
            $banks = $this->contract['targets'][$i]['bank_seeders'];
            for ($n = 1; $n <= 5; ++$n) {
                $bankIndex = ($n - 1) % count($banks); $bank = $banks[$bankIndex];
                $uuid = 'm52-fixture-'.$i.'-'.$bankIndex.'-'.$n;
                $questionId = DB::table('questions')->insertGetId(['uuid' => $uuid,
                    'question' => 'Незалежна синтетична умова '.$i.' '.$bankIndex.' '.$n, 'type' => '4', 'level' => Package::LEVELS[$i],
                    'seeder' => $bank, 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
                $optionId = DB::table('question_options')->insertGetId(['option' => 'Synthetic unchanged answer '.$uuid,
                    'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
                DB::table('question_answers')->insert(['question_id' => $questionId, 'option_id' => $optionId, 'marker' => 'a1']);
                DB::table('question_option_question')->insert(['question_id' => $questionId, 'option_id' => $optionId, 'flag' => 1]);
                DB::table('verb_hints')->insert(['question_id' => $questionId, 'option_id' => $optionId, 'marker' => 'a1']);
                DB::table('question_hints')->insert(['question_id' => $questionId, 'provider' => 'fixture', 'locale' => 'uk', 'hint' => 'Незмінна стороння підказка.']);
                DB::table('question_variants')->insert(['question_id' => $questionId, 'text' => 'Незмінний сторонній варіант.']);
                DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $block->uuid,
                    'position' => $n, 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
            }
        }
        DB::connection()->getSchemaBuilder()->dropIfExists('m52_existing_progress');
        DB::connection()->getSchemaBuilder()->create('m52_existing_progress', static function (Blueprint $table): void {
            $table->id(); $table->text('value');
        });
        DB::table('m52_existing_progress')->insert(['value' => 'Original personal progress must stay exact.']);
        DB::table('tags')->insert(['id' => 500, 'name' => 'Foreign protected tag', 'category' => 'Fixture']);
        DB::table('page_tag')->insert(['page_id' => Package::PAGE_IDS[0], 'tag_id' => 500]);
        $oldBlock = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[0])->where('locale', 'uk')->where('sort_order', 1)->sole();
        DB::table('tag_text_block')->insert(['text_block_id' => $oldBlock->id, 'tag_id' => 500]);
    }

    private function service(): Patch { return new Patch(DB::connection(), $this->root.'/database', $this->evidence); }
    private function file(string $name): string { return $this->evidence.'/'.$name.'.json'; }

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

    private function refusesWithoutWrites(callable $operation): RuntimeException
    {
        $snapshot = $this->snapshot(); $error = null;
        try { $operation(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error);
        self::assertSame($snapshot, $this->snapshot());

        return $error;
    }

    public function test_actual_9_11_11_slots_preview_apply_and_noop_preserve_all_question_families_and_62_non_uk_rows(): void
    {
        $service = $this->service(); $original = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($original, $this->snapshot()); self::assertSame('before', $plan['state']);
        self::assertSame([9, 11, 11], Package::CONTINUING_COUNTS);
        self::assertSame([1, 98, 97], array_column(array_column($plan['pages'], 'page'), 'id'));
        self::assertCount(34, DB::table('text_blocks')->where('locale', 'uk')->get());
        self::assertCount(62, DB::table('text_blocks')->whereIn('locale', ['en', 'pl'])->get());
        $expectedUpdates = 3 + array_sum(array_map(static fn ($target) => 1 + count($target['before']['page']['blocks']), $this->before['targets']));
        self::assertSame(37, $expectedUpdates); self::assertCount($expectedUpdates, $plan['updates']); self::assertCount(3, $plan['inserts']);
        self::assertSame(array_keys($original), array_keys($plan['protected']));
        self::assertArrayHasKey('m52_existing_progress', $plan['protected']);
        foreach ($plan['updates'] as $change) {
            self::assertSame($change['table'] === 'pages' ? ['text'] : ['body'], array_keys($change['after']));
        }
        foreach ($plan['inserts'] as $insert) { self::assertSame('text_blocks', $insert['table']); self::assertSame('practice-set', $insert['fields']['type']); }
        foreach (['question_inserts', 'question_projection', 'question_ids', 'link_inserts'] as $forbidden) { self::assertArrayNotHasKey($forbidden, $plan); }
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame('applied', $result['status']); self::assertSame(count($plan['updates']), $result['updated']); self::assertSame(3, $result['inserted']);
        $after = $service->savePlan($this->file('after'));
        self::assertSame('after', $after['state']); self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']);
        self::assertSame($plan['protected'], $after['protected']);
        $current = $this->snapshot();
        foreach ($original as $table => $rows) {
            if (!in_array($table, ['pages', 'text_blocks'], true)) { self::assertSame($rows, $current[$table], $table); }
        }
        foreach ($plan['pages'] as $owner) {
            foreach ($owner['blocks'] as $row) {
                $actual = (array) DB::table('text_blocks')->where('id', $row['id'])->sole();
                if ($row['locale'] === 'uk') { unset($row['body'], $actual['body']); }
                self::assertSame($row, $actual);
            }
        }
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $service->apply($this->file('preview'), $this->file('unused-backup')));
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $service->apply($this->file('after'), $this->file('unused-after-backup')));
        self::assertFileDoesNotExist($this->file('unused-backup')); self::assertFileDoesNotExist($this->file('unused-after-backup'));
        self::assertSame($current, $this->snapshot());
    }

    public function test_failure_after_content_update_or_practice_insert_rolls_back_and_backup_cannot_be_overwritten(): void
    {
        foreach (['update', 'insert'] as $phase) {
            $service = new class(DB::connection(), $this->root.'/database', $this->evidence) extends Patch {
                public string $phase;
                protected function afterUpdate(int $index): void { if ($this->phase === 'update' && $index === 0) { throw new RuntimeException('Synthetic update failure.'); } }
                protected function afterInsert(int $index): void { if ($this->phase === 'insert' && $index === 0) { throw new RuntimeException('Synthetic insert failure.'); } }
            };
            $service->phase = $phase; $service->savePlan($this->file('preview-'.$phase));
            $this->refusesWithoutWrites(fn () => $service->apply($this->file('preview-'.$phase), $this->file('backup-'.$phase)));
            self::assertFileExists($this->file('backup-'.$phase)); $backup = File::get($this->file('backup-'.$phase));
            $this->refusesWithoutWrites(fn () => $this->service()->apply($this->file('preview-'.$phase), $this->file('backup-'.$phase)));
            self::assertSame($backup, File::get($this->file('backup-'.$phase)));
            $altered = json_decode($backup, true, flags: JSON_THROW_ON_ERROR);
            $altered['updates'][0]['after']['text'] = 'Tampered backup value.';
            File::put($this->file('backup-'.$phase), M26DetailPackage::json($altered));
            $this->refusesWithoutWrites(fn () => $this->service()->apply($this->file('backup-'.$phase), $this->file('no-tampered-backup-'.$phase)));
            self::assertFileDoesNotExist($this->file('no-tampered-backup-'.$phase));
        }
    }

    public function test_foreign_row_owner_locale_metadata_ancestry_and_stale_body_refuse_without_backup(): void
    {
        $row = (array) DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[1])->where('locale', 'uk')->where('sort_order', 1)->sole();
        foreach (['body' => 'Manual newer body', 'locale' => 'en', 'seeder' => 'ForeignOwner', 'page_id' => Package::PAGE_IDS[2],
            'page_category_id' => 999, 'sort_order' => 99, 'heading' => 'Foreign heading', 'level' => 'A2', 'css_class' => 'foreign-style'] as $field => $value) {
            DB::table('text_blocks')->where('id', $row['id'])->update([$field => $value]);
            $this->refusesWithoutWrites(fn () => $this->service()->savePlan($this->file('rejected-'.$field)));
            self::assertFileDoesNotExist($this->file('rejected-'.$field));
            DB::table('text_blocks')->where('id', $row['id'])->update([$field => $row[$field]]);
        }
        $page = (array) DB::table('pages')->where('id', Package::PAGE_IDS[1])->sole();
        foreach (['title' => 'Unapproved new title', 'slug' => 'shorter-passive-slug',
            'type' => 'course', 'text' => 'Manual newer description'] as $field => $value) {
            DB::table('pages')->where('id', $page['id'])->update([$field => $value]);
            $this->refusesWithoutWrites(fn () => $this->service()->plan());
            DB::table('pages')->where('id', $page['id'])->update([$field => $page[$field]]);
        }
        foreach (['slug' => 'wrong-category', 'language' => 'en', 'type' => 'course', 'parent_id' => 1] as $field => $value) {
            $category = (array) DB::table('page_categories')->where('id', 1)->sole();
            DB::table('page_categories')->where('id', 1)->update([$field => $value]);
            $this->refusesWithoutWrites(fn () => $this->service()->plan());
            DB::table('page_categories')->where('id', 1)->update([$field => $category[$field]]);
        }
    }

    public function test_partial_package_or_new_practice_uuid_collision_is_not_completed_automatically(): void
    {
        $service = $this->service(); $plan = $service->savePlan($this->file('preview'));
        $bodyChange = array_values(array_filter($plan['updates'], static fn ($change) => $change['table'] === 'text_blocks'))[0];
        DB::table('text_blocks')->where('id', $bodyChange['id'])->update($bodyChange['after']);
        $this->refusesWithoutWrites(fn () => $service->apply($this->file('preview'), $this->file('no-partial-backup')));
        self::assertFileDoesNotExist($this->file('no-partial-backup'));
        DB::table('text_blocks')->where('id', $bodyChange['id'])->update($bodyChange['before']);
        $fields = $plan['inserts'][0]['fields'];
        DB::table('text_blocks')->insert($fields + ['created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
        $this->refusesWithoutWrites(fn () => $service->apply($this->file('preview'), $this->file('no-collision-backup')));
        self::assertFileDoesNotExist($this->file('no-collision-backup'));
    }

    public function test_missing_foreign_bank_type_or_level_cannot_fill_the_thematic_five(): void
    {
        $bank = $this->contract['targets'][1]['bank_seeders'][0];
        $question = (array) DB::table('questions')->where('seeder', $bank)->orderBy('id')->first();
        foreach (['seeder' => 'ForeignBank', 'type' => '1', 'level' => 'A2'] as $field => $value) {
            DB::table('questions')->where('id', $question['id'])->update([$field => $value]);
            $this->refusesWithoutWrites(fn () => $this->service()->plan());
            DB::table('questions')->where('id', $question['id'])->update([$field => $question[$field]]);
        }
        $pivot = (array) DB::table('question_theory_text_blocks')->where('question_uuid', $question['uuid'])->sole();
        DB::table('question_theory_text_blocks')->where('id', $pivot['id'])->delete();
        $this->refusesWithoutWrites(fn () => $this->service()->plan());
        DB::table('question_theory_text_blocks')->insert($pivot);
    }

    public function test_question_payload_pivots_non_target_metadata_and_progress_drift_refuse_a_saved_preview(): void
    {
        foreach (['question_answers' => 'marker', 'question_options' => 'option', 'question_hints' => 'hint',
            'question_variants' => 'text', 'questions' => 'question', 'question_theory_text_blocks' => 'position',
            'm52_existing_progress' => 'value', 'page_tag' => 'tag_id'] as $table => $field) {
            $row = (array) DB::table($table)->orderBy('id')->first(); $name = 'drift-'.$table;
            $service = $this->service(); $service->savePlan($this->file($name));
            DB::table($table)->where('id', $row['id'])->update([$field => $field === 'position' || $field === 'tag_id' ? 777 : 'Foreign changed field']);
            $this->refusesWithoutWrites(fn () => $service->apply($this->file($name), $this->file('no-backup-'.$table)));
            self::assertFileDoesNotExist($this->file('no-backup-'.$table));
            DB::table($table)->where('id', $row['id'])->update([$field => $row[$field]]);
        }
        $row = (array) DB::table('text_blocks')->where('locale', 'en')->orderBy('id')->first();
        $service = $this->service(); $service->savePlan($this->file('non-uk-drift'));
        DB::table('text_blocks')->where('id', $row['id'])->update(['body' => 'Changed excluded consumer body.']);
        $this->refusesWithoutWrites(fn () => $service->apply($this->file('non-uk-drift'), $this->file('no-en-backup')));
        self::assertFileDoesNotExist($this->file('no-en-backup'));
    }

    public function test_source_or_plan_value_drift_refuses_before_any_backup_or_learning_write(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $source = $this->root.'/app/Services/M26ContentPatch.php'; $original = File::get($source);
        File::put($source, $original."\n// Synthetic source drift.\n");
        $this->refusesWithoutWrites(fn () => $service->apply($this->file('preview'), $this->file('no-source-backup')));
        self::assertFileDoesNotExist($this->file('no-source-backup')); File::put($source, $original);
        $plan = json_decode(File::get($this->file('preview')), true, flags: JSON_THROW_ON_ERROR);
        $plan['updates'][0]['after']['text'] = 'Unreviewed plan value';
        File::put($this->file('tampered'), M26DetailPackage::json($plan));
        $this->refusesWithoutWrites(fn () => $service->apply($this->file('tampered'), $this->file('no-plan-backup')));
        self::assertFileDoesNotExist($this->file('no-plan-backup'));
        $path = $this->root.'/'.$this->patch['targets'][2]['path']; $definition = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
        $definition['page']['blocks'][1]['body'] = '{"unreviewed":"body"}'; File::put($path, M26DetailPackage::json($definition));
        $this->refusesWithoutWrites(fn () => $service->plan());
    }

    public function test_edited_accepted_practice_or_foreign_practice_links_refuse_repeated_apply_and_restore_is_unavailable(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview')); $service->apply($this->file('preview'), $this->file('backup'));
        $practice = (array) DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[0])->where('type', 'practice-set')->sole();
        foreach (['body' => '{"edited":true}', 'level' => 'B1', 'updated_at' => '2030-01-01 00:00:00'] as $field => $value) {
            DB::table('text_blocks')->where('id', $practice['id'])->update([$field => $value]);
            $this->refusesWithoutWrites(fn () => $service->apply($this->file('preview'), $this->file('no-edited-'.$field)));
            self::assertFileDoesNotExist($this->file('no-edited-'.$field));
            DB::table('text_blocks')->where('id', $practice['id'])->update([$field => $practice[$field]]);
        }
        $question = DB::table('questions')->orderBy('id')->first();
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => $question->uuid, 'text_block_uuid' => $practice['uuid'], 'position' => 0]);
        $this->refusesWithoutWrites(fn () => $service->apply($this->file('preview'), $this->file('no-link-backup')));
        self::assertFileDoesNotExist($this->file('no-link-backup'));
        $this->refusesWithoutWrites(fn () => $service->restore($this->file('backup')));
    }
}
