<?php

namespace Tests\Feature;

use App\Services\M49ContentPatch as Patch;
use App\Support\Database\JsonPageSeeder;
use App\Support\M26DetailPackage;
use App\Support\M49AdviceModalsPackage as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M49ContentPatchTest extends TestCase
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
        $this->root = storage_path('app/m49-fixture-'.bin2hex(random_bytes(8)));
        $this->evidence = $this->root.'/evidence';
        File::makeDirectory($this->evidence, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($this->root);
        [$this->original, $this->package, $this->master] = Package::load();
        Package::validate($this->original, $this->package, $this->master);

        DB::table('page_categories')->insert([
            ['slug' => 'conditionals', 'title' => 'Умовні речення', 'type' => 'theory', 'language' => 'uk'],
            ['slug' => 'modal-verbs', 'title' => 'Модальні дієслова', 'type' => 'theory', 'language' => 'uk'],
        ]);
        foreach (Patch::sourcePaths($this->original, $this->package) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true);
            File::copy(base_path($path), $this->root.'/'.$path);
        }
        // The DB fixture begins with the independent, pinned pre-M49 definitions, not generated AFTER data.
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
        $target = (array) DB::table('text_blocks')->where('locale', 'uk')->where('type', 'forms-grid')
            ->where('seeder', Package::OWNERS[0])->first();
        foreach (Package::OWNERS as $i => $identity) {
            $source = (array) DB::table('text_blocks')->where('locale', 'uk')
                ->where('type', 'forms-grid')->where('seeder', $identity)->first();
            foreach (['en', 'pl'] as $locale) {
                $foreign = $source; unset($foreign['id']);
                $foreign['uuid'] = 'm49-protected-'.($i === 0 ? '' : $i.'-').$locale;
                $foreign['locale'] = $locale;
                $foreign['body'] = 'Independent '.$locale.' content for '.$identity;
                DB::table('text_blocks')->insert($foreign);
            }
        }
        $pageId = DB::table('pages')->insertGetId([
            'slug' => 'm49-protected-other-page', 'title' => 'Unchanged',
            'text' => 'Unchanged subtitle', 'type' => 'theory', 'seeder' => 'M49ProtectedOtherSeeder',
        ]);
        DB::table('text_blocks')->insert([
            'uuid' => 'm49-protected-other-block', 'page_id' => $pageId, 'locale' => 'uk',
            'body' => 'Unchanged neighbour', 'seeder' => 'M49ProtectedOtherSeeder',
        ]);
        $questionId = DB::table('questions')->insertGetId([
            'uuid' => 'm49-protected-question', 'question' => 'Protected question {a1}', 'type' => '4',
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
            'question_uuid' => 'm49-protected-question', 'text_block_uuid' => $target['uuid'], 'position' => 0,
        ]);
        $testId = DB::table('saved_grammar_tests')->insertGetId([
            'uuid' => 'm49-protected-saved-test', 'name' => 'Existing test', 'slug' => 'm49-existing-test', 'filters' => '{}',
        ]);
        DB::table('saved_grammar_test_questions')->insert([
            'saved_grammar_test_id' => $testId, 'question_uuid' => 'm49-protected-question', 'position' => 0,
        ]);
        // Not in the base patch's historical table list: the subclass must protect actual tables.
        foreach (['m49_existing_sessions', 'm49_existing_progress', 'arbitrary_m49_protected'] as $table) {
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
        $oldBlocks = 0; $pageUpdates = 0;
        foreach ($this->original['targets'] as $i => $target) {
            $old = $target['before']['page']; $new = $this->package['targets'][$i]['after']['page'];
            $oldBlocks += (int) ($old['subtitle_html'] !== $new['subtitle_html']);
            $pageUpdates += (int) ($old['subtitle_text'] !== $new['subtitle_text']);
            foreach ($old['blocks'] as $slot => $block) {
                $oldBlocks += (int) ($block['body'] !== $new['blocks'][$slot]['body']);
            }
        }
        $newBlocks = array_sum(array_map(fn ($target) => count($target['after']['page']['blocks']), $this->package['targets']))
            - array_sum(array_map(fn ($target) => count($target['before']['page']['blocks']), $this->original['targets']));
        self::assertCount($oldBlocks, array_filter($plan['updates'], fn ($change) => $change['table'] === 'text_blocks'));
        self::assertCount($pageUpdates, array_filter($plan['updates'], fn ($change) => $change['table'] === 'pages'));
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
        self::assertSame(['status' => 'applied', 'updated' => $oldBlocks + $pageUpdates,
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

    public function test_stale_frozen_and_definition_sources_or_foreign_locale_change_refuse_before_backup(): void
    {
        $patch = $this->service(); $patch->savePlan($this->path('preview'));
        foreach ([Package::MASTER, $this->package['targets'][0]['path']] as $path) {
            $source = json_decode(File::get($this->root.'/'.$path), true, flags: JSON_THROW_ON_ERROR);
            if ($path === Package::MASTER) {
                $source['lessons'][0]['blocks'][0]['data']['intro'] .= ' Substantive concurrent source edit.';
            } else {
                $body = json_decode($source['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
                $body['intro'] .= ' Substantive concurrent definition edit.';
                $source['page']['blocks'][0]['body'] = M26DetailPackage::json($body);
            }
            File::put($this->root.'/'.$path, M26DetailPackage::json($source));
            $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('backup')));
            self::assertFileDoesNotExist($this->path('backup'));
            File::copy(base_path($path), $this->root.'/'.$path);
        }
        DB::table('text_blocks')->where('uuid', 'm49-protected-pl')->update(['body' => 'Concurrent localization edit']);
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
                if ($index === 0) { $this->db->table('m49_existing_progress')->update(['value' => 'Unintended side effect']); }
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
        foreach (['conditionals', 'modal-verbs'] as $slug) {
            DB::table('page_categories')->where('slug', $slug)->update(['language' => 'pl']);
            $this->refusesWithoutDbChange(fn () => $patch->savePlan($this->path('foreign-'.$slug)));
            self::assertFileDoesNotExist($this->path('foreign-'.$slug));
            DB::table('page_categories')->where('slug', $slug)->update(['language' => 'uk']);
        }
        $this->app->detectEnvironment(fn () => 'production');
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('production-backup')));
        self::assertFileDoesNotExist($this->path('production-backup'));
    }

    public function test_scope_keeps_exact_owners_two_categories_and_per_lesson_levels(): void
    {
        self::assertSame([
            'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsHadBetterTheorySeeder',
            'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsCanCouldTheorySeeder',
            'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMayMightTheorySeeder',
        ], Package::OWNERS);
        self::assertSame(['had-better', 'can-could', 'may-might'],
            array_column($this->package['targets'], 'slug'));
        foreach ([
            ['conditionals', 'A2', 'A2-B1'],
            ['modal-verbs', 'A1', 'A1-A2'],
            ['modal-verbs', 'A2', 'A2-B1'],
        ] as $i => [$category, $technical, $display]) {
            $target = $this->package['targets'][$i];
            self::assertSame([$category], $target['ancestry']);
            self::assertSame('http://gramlyze.loc/theory/'.$category.'/'.$target['slug'], $target['local_url']);
            foreach ([$this->original['targets'][$i]['before'], $target['after']] as $definition) {
                self::assertSame($category, $definition['page']['category']['slug']);
                foreach ($definition['page']['blocks'] as $block) {
                    if ($block['type'] !== 'navigation-chips') {
                        self::assertSame($technical, $block['level']);
                    }
                }
                self::assertSame($display, json_decode($definition['page']['blocks'][0]['body'], true)['level']);
            }
        }
    }

    public function test_each_owner_rejects_swapped_category_ancestry_and_row_identity(): void
    {
        $patch = $this->service();
        $patch->savePlan($this->path('identity-preview'));
        foreach (Package::OWNERS as $i => $identity) {
            $page = (array) DB::table('pages')->where('seeder', $identity)->first();
            $wrongCategory = $i === 0 ? 'modal-verbs' : 'conditionals';
            $categoryId = DB::table('page_categories')->where('slug', $wrongCategory)->value('id');
            DB::table('pages')->where('id', $page['id'])->update(['page_category_id' => $categoryId]);
            $this->refusesWithoutDbChange(fn () => $patch->savePlan($this->path('category-'.$i)));
            $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('identity-preview'), $this->path('category-backup-'.$i)));
            self::assertFileDoesNotExist($this->path('category-'.$i));
            self::assertFileDoesNotExist($this->path('category-backup-'.$i));
            DB::table('pages')->where('id', $page['id'])->update(['page_category_id' => $page['page_category_id']]);

            $row = (array) DB::table('text_blocks')->where('page_id', $page['id'])
                ->where('locale', 'uk')->where('type', 'forms-grid')->first();
            foreach ([
                'seeder' => Package::OWNERS[($i + 1) % 3],
                'locale' => 'en',
                'page_category_id' => $categoryId,
                'sort_order' => 999,
                'level' => 'B1',
                'uuid' => 'm49-foreign-old-uuid-'.$i,
            ] as $field => $wrongValue) {
                DB::table('text_blocks')->where('id', $row['id'])->update([$field => $wrongValue]);
                $this->refusesWithoutDbChange(fn () => $patch->apply(
                    $this->path('identity-preview'), $this->path('row-'.$i.'-'.$field)
                ));
                self::assertFileDoesNotExist($this->path('row-'.$i.'-'.$field));
                DB::table('text_blocks')->where('id', $row['id'])->update([$field => $row[$field]]);
            }
        }
    }

    public function test_definition_scope_rejects_foreign_owner_locale_and_swapped_category_bindings(): void
    {
        foreach ($this->original['targets'] as $i => $original) {
            $before = $original['before']; $target = $this->package['targets'][$i];
            $foreign = ($i + 1) % 3;
            $wrongCategory = $i === 0 ? 'modal-verbs' : 'conditionals';
            foreach (['target-owner', 'before-seeder', 'locale', 'before-category', 'target-ancestry', 'path', 'local-url'] as $kind) {
                $old = $before; $candidate = $target;
                if ($kind === 'target-owner') { $candidate['identity'] = Package::OWNERS[$foreign]; }
                if ($kind === 'before-seeder') { $old['seeder']['class'] = Package::OWNERS[$foreign]; }
                if ($kind === 'locale') { $old['page']['locale'] = 'en'; }
                if ($kind === 'before-category') { $old['page']['category']['slug'] = $wrongCategory; }
                if ($kind === 'target-ancestry') { $candidate['ancestry'] = [$wrongCategory]; }
                if ($kind === 'path') { $candidate['path'] = $this->package['targets'][$foreign]['path']; }
                if ($kind === 'local-url') { $candidate['local_url'] = 'http://gramlyze.loc/theory/'.$wrongCategory.'/'.$target['slug']; }
                $error = $this->refusesWithoutDbChange(
                    fn () => Patch::assertDefinitionScope($old, $target['after'], $candidate, $i)
                );
                self::assertSame('M49 definition owner/category/locale/slots differ.', $error->getMessage(), $kind);
            }
        }
    }

    public function test_changed_actual_target_body_after_preview_is_rejected_before_backup(): void
    {
        $patch = $this->service(); $patch->savePlan($this->path('preview'));
        $row = DB::table('text_blocks')->where('locale', 'uk')->where('seeder', Package::OWNERS[0])
            ->where('type', 'hero')->first();
        $body = json_decode($row->body, true, flags: JSON_THROW_ON_ERROR);
        $body['intro'] .= ' Concurrent human explanation.';
        DB::table('text_blocks')->where('id', $row->id)->update(['body' => M26DetailPackage::json($body)]);
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('stale-backup')));
        self::assertFileDoesNotExist($this->path('stale-backup'));
    }

    public function test_tampered_plan_cannot_authorize_an_unreviewed_body(): void
    {
        $patch = $this->service(); $plan = $patch->savePlan($this->path('preview'));
        foreach ($plan['updates'] as &$update) {
            if ($update['table'] === 'text_blocks') {
                $update['after']['body'] = 'Unreviewed replacement';
                break;
            }
        }
        unset($update);
        File::put($this->path('preview'), M26DetailPackage::json($plan));
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('tampered-backup')));
        self::assertFileDoesNotExist($this->path('tampered-backup'));
    }

    public function test_new_uuid_collision_and_unknown_uk_row_are_not_overwritten_or_adopted(): void
    {
        $patch = $this->service(); $plan = $patch->savePlan($this->path('preview'));
        self::assertNotEmpty($plan['inserts']);
        $first = $plan['inserts'][0]['fields'];
        $foreignPage = DB::table('pages')->where('slug', 'm49-protected-other-page')->value('id');
        $collision = DB::table('text_blocks')->insertGetId([
            'uuid' => $first['uuid'], 'page_id' => $foreignPage, 'locale' => 'uk',
            'body' => 'Existing unrelated row owns this UUID', 'seeder' => 'M49ProtectedOtherSeeder',
        ]);
        $this->refusesWithoutDbChange(fn () => $patch->savePlan($this->path('collision-preview')));
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('collision-backup')));
        self::assertFileDoesNotExist($this->path('collision-preview'));
        self::assertFileDoesNotExist($this->path('collision-backup'));
        DB::table('text_blocks')->where('id', $collision)->delete();

        DB::table('text_blocks')->insert([
            'uuid' => 'm49-extra-manual-uk', 'page_id' => $first['page_id'], 'locale' => 'uk',
            'page_category_id' => $first['page_category_id'], 'seeder' => $first['seeder'],
            'sort_order' => 999, 'body' => 'Existing manual extra content',
        ]);
        $this->refusesWithoutDbChange(fn () => $patch->savePlan($this->path('extra-preview')));
        self::assertFileDoesNotExist($this->path('extra-preview'));
    }

    public function test_inserted_rows_cannot_be_adopted_after_manual_content_or_timestamp_edits(): void
    {
        $patch = $this->service();
        $plan = $patch->savePlan($this->path('preview'));
        $patch->apply($this->path('preview'), $this->path('backup'));
        $uuid = $plan['inserts'][0]['fields']['uuid'];
        $row = DB::table('text_blocks')->where('uuid', $uuid)->first();

        DB::table('text_blocks')->where('id', $row->id)->update(['body' => 'A newer manual explanation.']);
        $this->refusesWithoutDbChange(fn () => $patch->savePlan($this->path('edited-new-body')));
        self::assertFileDoesNotExist($this->path('edited-new-body'));
        DB::table('text_blocks')->where('id', $row->id)->update(['body' => $row->body]);

        DB::table('text_blocks')->where('id', $row->id)->update(['updated_at' => '2099-01-01 00:00:00']);
        $this->refusesWithoutDbChange(fn () => $patch->savePlan($this->path('edited-new-timestamp')));
        self::assertFileDoesNotExist($this->path('edited-new-timestamp'));
    }

    public function test_definition_contract_cannot_expand_existing_slots_metadata_or_new_identity(): void
    {
        foreach ($this->original['targets'] as $i => $original) {
            self::assertCount(6, $original['before']['page']['blocks']);
            $target = $this->package['targets'][$i];
            Patch::assertDefinitionScope($original['before'], $target['after'], $target, $i);
            $this->addToAssertionCount(1);
            foreach (['page-title', 'existing-column', 'new-uuid-key'] as $kind) {
                $after = $target['after'];
                if ($kind === 'page-title') { $after['page']['title'] .= ' Foreign title'; }
                if ($kind === 'existing-column') { $after['page']['blocks'][1]['column'] = 'right'; }
                if ($kind === 'new-uuid-key') { $after['page']['blocks'][6]['uuid_key'] = 'm47:foreign:new-block'; }
                $error = $this->refusesWithoutDbChange(
                    fn () => Patch::assertDefinitionScope($original['before'], $after, $target, $i)
                );
                self::assertStringContainsString('M49', $error->getMessage());
            }
        }
    }

    public function test_independent_before_metadata_policy_is_pure_and_only_exempts_approved_body_and_text(): void
    {
        $owner = (array) DB::table('pages')->where('seeder', Package::OWNERS[0])->first();
        $rows = DB::table('text_blocks')->where('page_id', $owner['id'])->orderBy('id')
            ->get()->map(fn ($row) => (array) $row)->all();
        $captured = ['page' => $owner, 'blocks' => $rows];
        $changedOwner = $owner;
        $changedOwner['text'] = 'The approved text is checked separately by the finite candidate planner.';
        $changedRows = $rows;
        foreach ($changedRows as &$row) {
            if ($row['locale'] === 'uk') { $row['body'] = 'Body validity is checked separately by the finite candidate planner.'; }
        }
        unset($row);

        DB::flushQueryLog();
        DB::enableQueryLog();
        try {
            Patch::assertMetadataBaseline($captured, $owner, $rows);
            Patch::assertMetadataBaseline($captured, $changedOwner, $changedRows);
            self::assertSame([], DB::getQueryLog(), 'The metadata policy must not obtain working data.');
        } finally {
            DB::disableQueryLog();
        }

        foreach (['id', 'slug', 'title', 'seeder', 'page_category_id', 'created_at', 'updated_at'] as $field) {
            $mutated = $owner;
            $mutated[$field] = 'concurrent-'.$field;
            $caught = null;
            try { Patch::assertMetadataBaseline($captured, $mutated, $rows); }
            catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught, 'Changed page '.$field.' was accepted.');
        }
        $ukIndex = array_search('uk', array_column($rows, 'locale'), true);
        self::assertNotFalse($ukIndex);
        foreach (['id', 'uuid', 'locale', 'page_id', 'page_category_id', 'type', 'column', 'heading',
            'level', 'css_class', 'sort_order', 'seeder', 'created_at', 'updated_at'] as $field) {
            $mutated = $rows;
            $mutated[$ukIndex][$field] = 'concurrent-'.$field;
            $caught = null;
            try { Patch::assertMetadataBaseline($captured, $owner, $mutated); }
            catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught, 'Changed block '.$field.' was accepted.');
        }
        foreach (['en', 'pl'] as $locale) {
            $index = array_search($locale, array_column($rows, 'locale'), true);
            self::assertNotFalse($index);
            $mutated = $rows;
            $mutated[$index]['body'] = 'A concurrent localization edit.';
            $caught = null;
            try { Patch::assertMetadataBaseline($captured, $owner, $mutated); }
            catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught, 'Foreign body was exempted.');
        }
        foreach ([array_slice($rows, 1), [...$rows, $rows[$ukIndex]]] as $mutated) {
            $caught = null;
            try { Patch::assertMetadataBaseline($captured, $owner, $mutated); }
            catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught, 'Missing or duplicate captured ID was accepted.');
        }
    }

    public function test_existing_uuid_with_moved_ownership_is_rejected(): void
    {
        $patch = $this->service(); $patch->savePlan($this->path('preview'));
        $row = DB::table('text_blocks')->where('locale', 'uk')->where('seeder', Package::OWNERS[0])
            ->where('type', 'forms-grid')->first();
        $foreignPage = DB::table('pages')->where('slug', 'm49-protected-other-page')->value('id');
        DB::table('text_blocks')->where('id', $row->id)->update(['page_id' => $foreignPage]);
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('ownership-backup')));
        self::assertFileDoesNotExist($this->path('ownership-backup'));
    }

    public function test_missing_or_ambiguous_page_owner_is_rejected_for_each_category(): void
    {
        foreach (Package::OWNERS as $i => $identity) {
            $page = (array) DB::table('pages')->where('seeder', $identity)->first();
            DB::table('pages')->where('id', $page['id'])->update(['seeder' => 'ForeignOwner'.$i]);
            $error = $this->refusesWithoutDbChange(fn () => $this->service()->savePlan($this->path('missing-owner-'.$i)));
            self::assertSame('M49 missing/ambiguous page owner.', $error->getMessage());
            self::assertFileDoesNotExist($this->path('missing-owner-'.$i));
            DB::table('pages')->where('id', $page['id'])->update(['seeder' => $identity]);

            unset($page['id']);
            $duplicateId = DB::table('pages')->insertGetId($page);
            $error = $this->refusesWithoutDbChange(fn () => $this->service()->savePlan($this->path('duplicate-owner-'.$i)));
            self::assertSame('M49 missing/ambiguous page owner.', $error->getMessage());
            self::assertFileDoesNotExist($this->path('duplicate-owner-'.$i));
            DB::table('pages')->where('id', $duplicateId)->delete();
        }
    }

    public function test_ambiguous_old_uuid_query_is_rejected_without_weakening_unique_schema(): void
    {
        // SQLite keeps its real unique index. Simulate a corrupt read result at
        // the service boundary; no duplicate production/fixture row is inserted.
        $uuid = DB::table('text_blocks')->where('locale', 'uk')->where('seeder', Package::OWNERS[1])
            ->where('type', 'forms-grid')->value('uuid');
        $patch = new class(DB::connection(), $this->root.'/database', $this->evidence, duplicateUuid: $uuid) extends Patch {
            public function __construct($db, string $databasePath, string $privateDirectory, private string $duplicateUuid)
            { parent::__construct($db, $databasePath, $privateDirectory); }
            protected function rows(string $table, callable $filter, bool $lock): array
            {
                $rows = parent::rows($table, $filter, $lock);
                if ($table === 'text_blocks' && count($rows) === 1 && ($rows[0]['uuid'] ?? null) === $this->duplicateUuid) {
                    $rows[] = $rows[0];
                }
                return $rows;
            }
        };
        $error = $this->refusesWithoutDbChange(fn () => $patch->savePlan($this->path('duplicate-uuid')));
        self::assertSame('M49 old UUID missing/ambiguous.', $error->getMessage());
        self::assertFileDoesNotExist($this->path('duplicate-uuid'));
    }
}
