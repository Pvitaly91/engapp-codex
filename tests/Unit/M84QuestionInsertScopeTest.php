<?php

namespace Tests\Unit;

use App\Services\M26ContentPatch;
use App\Services\M84QuestionInsertScope;
use App\Services\TheoryNativeContentPatch;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M84QuestionSupplement as Supplement;
use App\Support\M84WordOrderHaveGot as Package;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

require_once dirname(__DIR__, 2).'/app/Support/M84QuestionSupplement.php';
require_once dirname(__DIR__, 2).'/app/Services/M84QuestionInsertScope.php';

/** Invented SQLite evidence only; actual M84 finite scope and unchanged M26 backup/transaction are exercised. */
class M84QuestionInsertScopeTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private array $source;
    private array $evidence;
    private array $expected;
    private string $private;
    private string $evidenceSha;

    protected function setUp(): void
    {
        parent::setUp(); IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertNotSame(realpath(base_path()), realpath(app()->environmentPath()));
        self::assertSame('array', config('session.driver')); self::assertSame('array', config('cache.default'));
        $this->rebuildComposeTestSchema();
        DB::connection()->getSchemaBuilder()->drop('question_options');
        DB::statement('CREATE TABLE question_options (id INTEGER PRIMARY KEY AUTOINCREMENT, option VARCHAR(255) COLLATE NOCASE NOT NULL UNIQUE, created_at DATETIME NULL, updated_at DATETIME NULL)');
        $this->source = self::syntheticSource(); $time = '2001-02-03 04:05:06';
        DB::table('categories')->insert(['id' => 45, 'name' => 'Synthetic question category', 'created_at' => $time, 'updated_at' => $time]);
        foreach (Supplement::SOURCE_IDS as $i => $id) { DB::table('sources')->insert(['id' => $id, 'name' => 'Synthetic source '.$i, 'created_at' => $time, 'updated_at' => $time]); }
        $patch = ['targets' => []]; $captured = []; $heroes = []; $references = [];
        foreach (M84SyntheticQuestionPackage::OWNERS as $i => $owner) {
            $page = ['id' => M84SyntheticQuestionPackage::PAGE_IDS[$i], 'page_category_id' => M84SyntheticQuestionPackage::CATEGORY_IDS[$i], 'slug' => 'synthetic-'.$i,
                'title' => 'Synthetic owner '.$i, 'text' => 'Synthetic unchanged subtitle.', 'type' => 'theory', 'seeder' => $owner,
                'created_at' => $time, 'updated_at' => $time];
            DB::table('pages')->insert($page);
            $supp = array_search($owner, Supplement::OWNERS, true);
            $hero = ['id' => $supp === false ? 2000 + $i : Supplement::HERO_IDS[$supp],
                'uuid' => $supp === false ? '00000000-0000-4000-8000-00000000000'.$i : Supplement::HERO_UUIDS[$supp],
                'page_id' => $page['id'], 'page_category_id' => M84SyntheticQuestionPackage::CATEGORY_IDS[$i], 'locale' => 'uk', 'type' => 'hero', 'level' => M84SyntheticQuestionPackage::LEVELS[$i],
                'column' => 'header', 'heading' => null, 'css_class' => null, 'sort_order' => 1, 'body' => 'Synthetic hero before '.$i,
                'seeder' => $owner, 'created_at' => $time, 'updated_at' => $time];
            DB::table('text_blocks')->insert($hero);
            $slot = M84SyntheticQuestionPackage::PRACTICE_SOURCE_INDICES[$i];
            $practice = ['uuid' => M84SyntheticQuestionPackage::EXISTING_PRACTICE_UUIDS[$i] ?? '10000000-0000-4000-8000-000000000005', 'type' => 'practice-set', 'column' => 'left',
                'level' => M84SyntheticQuestionPackage::LEVELS[$i], 'body' => 'Synthetic practice after '.$i];
            $blocks = array_fill(0, M84SyntheticQuestionPackage::CONTINUING_COUNTS[$i] + ($i < 5 ? 0 : 1), ['type' => 'box', 'column' => 'left', 'body' => 'Synthetic unused source slot.']);
            $blocks[0] = ['type' => 'hero', 'body' => 'Synthetic hero after '.$i]; $blocks[$slot] = $practice;
            $patch['targets'][] = ['identity' => $owner, 'after' => ['page' => ['blocks' => $blocks]]];
            if ($i < 5) {
                DB::table('text_blocks')->insert(['id' => 10000 + $i, 'uuid' => $practice['uuid'], 'page_id' => $page['id'],
                    'page_category_id' => M84SyntheticQuestionPackage::CATEGORY_IDS[$i], 'locale' => 'uk', 'type' => 'practice-set', 'level' => $practice['level'], 'column' => 'left',
                    'heading' => null, 'css_class' => null, 'sort_order' => $slot + 1, 'body' => 'Synthetic practice before '.$i,
                    'seeder' => $owner, 'created_at' => $time, 'updated_at' => $time]);
            }
            $captured[$owner] = ['blocks' => DB::table('text_blocks')->where('page_id', $page['id'])->orderBy('id')->get()->map(static fn ($r) => (array) $r)->all(), 'relations' => ['questions' => []]];
            if ($supp !== false) {
                $heroes[$owner] = $hero;
                $q = ['id' => 10 + $supp, 'uuid' => 'synthetic-old-question-'.$supp, 'question' => 'Незмінна синтетична умова.',
                    'difficulty' => ['A1'=>1, 'A2'=>2, 'B2'=>4][Supplement::LEVELS[$supp]], 'level' => Supplement::LEVELS[$supp], 'category_id' => 45, 'source_id' => Supplement::SOURCE_IDS[$supp], 'flag' => 0,
                    'type' => '4', 'options_by_marker' => null, 'theory_text_block_uuid' => null, 'seeder' => Supplement::OLD_BANKS[$supp],
                    'created_at' => $time, 'updated_at' => $time];
                DB::table('questions')->insert($q); $references[$owner] = $q; $captured[$owner]['relations']['questions'][] = $q;
                DB::table('question_theory_text_blocks')->insert(['id' => 10 + $supp, 'question_uuid' => $q['uuid'], 'text_block_uuid' => $hero['uuid'], 'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
            }
        }
        M84SyntheticQuestionPackage::$patch = $patch;
        // Nonempty invented protected families make omission or accidental membership writes visible.
        foreach (['m84_protected_progress', 'm84_protected_course_lessons', 'm84_protected_course_enrollment'] as $table) {
            DB::connection()->getSchemaBuilder()->create($table, static function (Blueprint $schema): void { $schema->id(); $schema->text('payload'); });
            DB::table($table)->insert(['payload' => 'Unchanged invented M84 protected row.']);
        }
        DB::table('saved_grammar_tests')->insert(['id' => 1, 'uuid' => 'm84-synthetic-saved-test', 'name' => 'Unchanged synthetic drill', 'slug' => 'm84-synthetic-saved-test']);
        DB::table('saved_grammar_test_questions')->insert(['id' => 1, 'saved_grammar_test_id' => 1,
            'question_uuid' => 'synthetic-old-question-0', 'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
        $tokens = [];
        foreach ($this->source['banks'] as $bank) { foreach ($bank['questions'] as $q) { foreach ([...$q['tokens_correct'], ...$q['distractors']] as $token) { $tokens[$token] = true; } } }
        $seen = []; $n = 1;
        foreach (array_keys($tokens) as $token) {
            $key = mb_strtolower($token);
            if ($key === 'maps' || isset($seen[$key])) { continue; }
            $seen[$key] = true; DB::table('question_options')->insert(['id' => $n++, 'option' => $key, 'created_at' => $time, 'updated_at' => $time]);
        }
        $matches = [];
        foreach (array_keys($tokens) as $token) { $matches[$token] = DB::table('question_options')->where('option', $token)->orderBy('id')->get()->map(static fn ($r) => (array) $r)->all(); }
        $projection = Supplement::project($this->source, Supplement::bindings(), $matches); $schema = $allocation = $ids = [];
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            $schema[$table] = DB::connection()->getSchemaBuilder()->getColumns($table); $max = (int) (DB::table($table)->max('id') ?? 0);
            $allocation[$table] = ['max_id' => $max, 'next_id' => $max + 1]; $ids[$table] = [];
            foreach (array_column($projection[$table], 'key') as $offset => $key) { $ids[$table][$key] = $max + 1 + $offset; }
        }
        $stamp = '2002-03-04 05:06:07'; $this->expected = Rows::materialize($projection, $ids, $stamp);
        $taxonomy = ['categories' => DB::table('categories')->orderBy('id')->get()->map(static fn ($r) => (array) $r)->all(),
            'sources' => DB::table('sources')->orderBy('id')->get()->map(static fn ($r) => (array) $r)->all()];
        $inventory = ['bindings' => Supplement::bindings(), 'heroes_before' => $heroes, 'taxonomy_questions' => $references,
            'question_collisions' => array_fill_keys(array_keys(Rows::ROW_COLUMNS), []), 'projection' => $projection, 'option_matches' => $matches,
            'ids' => $ids, 'row_timestamp' => $stamp, 'materialized' => $this->expected, 'schema' => $schema, 'allocation' => $allocation, 'taxonomy' => $taxonomy];
        $this->evidence = ['bindings' => Supplement::bindings(), 'projection' => $projection, 'ids' => $ids, 'row_timestamp' => $stamp,
            'materialized' => $this->expected, 'taxonomy' => $taxonomy, '_inventory' => $inventory, '_before' => ['pages' => $captured]];
        $this->private = storage_path('app/m84-synthetic-'.bin2hex(random_bytes(7))); File::makeDirectory($this->private, 0700, true); $this->seal();
    }

    public static function syntheticSource(): array
    {
        $sentences = [
            'They have one map .', 'They have two maps .', 'We know the way .', 'We know the answer .', 'We know the name .',
            'They need the map .', 'They need the answer .', 'They need the name .', 'She likes this book .', 'He likes that film .',
            'We keep the keys .', 'They keep the tickets .', 'She brings the bags .', 'He brings the box .', 'We bring the chairs .',
            'They close the door .', 'She opens the window .', 'He opens the gate .', 'We watch the birds .', 'They watch the boats .',
            'She reads the letter .', 'He reads the message .', 'We read the report .', 'They clean the room .', 'She cleans the table .',
        ];
        $out = ['schema_version' => 1, 'package' => 'm84-question-bank-supplement', 'version' => '1.0.0', 'status' => 'draft', 'locale' => 'uk', 'banks' => []]; $offset = 0;
        foreach (Supplement::OWNERS as $i => $owner) {
            $bank = ['owner' => $owner, 'bank_seeder' => Supplement::BANKS[$i], 'level' => Supplement::LEVELS[$i], 'type' => 4,
                'local_url' => 'http://gramlyze.loc/theory/basic-grammar/word-order/'.Supplement::SLUGS[$i], 'questions' => []];
            foreach (array_slice(Supplement::UUIDS, $offset, 5) as $uid) {
                $tokens = explode(' ', $sentences[$offset]);
                $canonical = preg_replace('/\\s+([?.!,;:])/', '$1', implode(' ', $tokens));
                $bank['questions'][] = ['source_uid' => $uid, 'question_uk' => 'Синтетична тестова умова номер '.($offset + 1).'.',
                    'context_uk' => 'Це вигаданий ізольований контекст.', 'criterion_uk' => 'Збережи заданий зміст.', 'translation_uk' => 'Синтетичний український переклад.',
                    'ambiguity_note_uk' => 'Це не авторська редакція уроку.', 'canonical_en' => $canonical, 'tokens_correct' => $tokens,
                    'tokens_shuffled' => array_reverse($tokens), 'distractors' => ['not'], 'learning_requirements' => ['synthetic-only']]; ++$offset;
            }
            $out['banks'][] = $bank;
        }
        return $out;
    }

    private function seal(): void
    {
        File::put($this->private.'/synthetic-evidence.json', json_encode($this->evidence, JSON_THROW_ON_ERROR));
        $this->evidenceSha = hash_file('sha256', $this->private.'/synthetic-evidence.json');
    }
    private function service(): M84SyntheticQuestionWriter { return new M84SyntheticQuestionWriter(DB::connection(), $this->private.'/database', $this->private, $this->evidenceSha, $this->source); }
    private function snapshot(): array
    {
        $out = []; $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false); sort($tables);
        foreach ($tables as $table) { if (str_starts_with($table, 'sqlite_')) { continue; } $q = DB::table($table); $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table); foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $q->orderBy($column); } $out[$table] = $q->get()->map(static fn ($r) => (array) $r)->all(); }
        return $out;
    }
    private function refused(callable $call, ?string $contains = null): void
    {
        $caught = null; try { $call(); } catch (RuntimeException $e) { $caught = $e; }
        self::assertInstanceOf(RuntimeException::class, $caught); if ($contains !== null) { self::assertStringContainsString($contains, $caught->getMessage()); }
    }

    public function test_exact_finite_scope_is_one_new_sql_null_have_got_practice_and_five_continuing_practices(): void
    {
        $plan = $this->service()->plan();
        self::assertCount(1, $plan['inserts']);
        self::assertSame(Package::OWNERS[5], $plan['inserts'][0]['seeder']);
        self::assertSame(['locale' => 'uk', 'type' => 'practice-set', 'column' => 'left', 'sort_order' => 6, 'level' => null],
            array_intersect_key($plan['inserts'][0]['fields'], array_flip(['type', 'column', 'locale', 'sort_order', 'level'])));
        foreach (array_slice(Package::OWNERS, 0, 5) as $i => $owner) {
            $old = DB::table('text_blocks')->where('uuid', Package::EXISTING_PRACTICE_UUIDS[$i])->sole();
            self::assertSame($owner, $old->seeder); self::assertNull($old->level);
            self::assertSame([Package::EXISTING_PRACTICE_UUIDS[$i]], array_column(array_filter($plan['pages'][$owner]['blocks'], static fn ($b) => $b['type'] === 'practice-set'), 'uuid'));
        }
        self::assertCount(25, $plan['question_supplement']['expected']['questions']);
        self::assertCount(25, $plan['question_supplement']['expected']['question_theory_text_blocks']);
        self::assertSame([5, 5, 5, 5, 5], array_values(array_count_values(array_column($plan['question_supplement']['expected']['questions'], 'seeder'))));
    }

    public function test_pure_projection_has_twenty_five_questions_five_banks_visible_context_and_repeated_marker_instances(): void
    {
        $before = $this->snapshot(); $plan = $this->service()->plan(); self::assertSame($before, $this->snapshot());
        self::assertCount(1, $plan['inserts']); self::assertCount(25, $plan['question_supplement']['expected']['questions']);
        self::assertSame([5, 5, 5, 5, 5], array_values(array_count_values(array_column($this->expected['questions'], 'seeder'))));
        self::assertCount(1, $this->expected['question_options']);
        foreach ($this->source['banks'] as $bank) { foreach ($bank['questions'] as $q) { $row = array_values(array_filter($this->expected['questions'], static fn ($r) => $r['uuid'] === $q['source_uid']))[0]; self::assertSame(Supplement::condition($q), $row['question']); self::assertStringContainsString($q['ambiguity_note_uk'], $row['question']); } }
        $source = $this->source; $source['banks'][0]['questions'][0]['canonical_en'] = 'They know they have maps.';
        $source['banks'][0]['questions'][0]['tokens_correct'] = ['They', 'know', 'they', 'have', 'maps', '.'];
        $source['banks'][0]['questions'][0]['tokens_shuffled'] = ['maps', 'they', '.', 'They', 'have', 'know'];
        $matches = $this->evidence['_inventory']['option_matches']; $matches['they'] = $matches['They'];
        // Remove the old token request which is absent from every remaining task.
        unset($matches['one']);
        $projection = Supplement::project($source, Supplement::bindings(), $matches);
        $answers = array_values(array_filter($projection['question_answers'], static fn ($a) => $a['question_key'] === Supplement::UUIDS[0]));
        self::assertCount(6, $answers); self::assertSame($answers[0]['option_key'], $answers[2]['option_key']);
        self::assertSame(['a1', 'a2', 'a3', 'a4', 'a5', 'a6'], array_column(array_column($answers, 'fields'), 'marker'));
    }

    public function test_unchanged_m26_apply_inserts_only_exact_twenty_five_families_and_one_have_got_practice_then_repeats_noop(): void
    {
        $service = $this->service(); $before = $this->snapshot(); $plan = $service->savePlan($this->private.'/preview.json');
        $result = $service->apply($this->private.'/preview.json', $this->private.'/backup.json'); self::assertSame('applied', $result['status']); self::assertSame(1, $result['inserted']);
        $after = $this->snapshot(); $normalized = $after;
        foreach ($plan['updates'] as $u) { foreach ($normalized[$u['table']] as &$row) { if ($row['id'] === $u['id']) { $row = array_replace($row, $u['before']); } } unset($row); }
        foreach ($this->expected as $table => $rows) { foreach ($rows as $row) { self::assertContains($row, $after[$table]); } $ids = array_column($rows, 'id'); $normalized[$table] = array_values(array_filter($normalized[$table], static fn ($r) => !in_array($r['id'], $ids, true))); }
        $newUuids = array_column(array_column($plan['inserts'], 'fields'), 'uuid'); $normalized['text_blocks'] = array_values(array_filter($normalized['text_blocks'], static fn ($r) => !in_array($r['uuid'], $newUuids, true)));
        self::assertSame($before, $normalized); $afterPlan = $service->savePlan($this->private.'/after.json'); self::assertSame('after', $afterPlan['state']);
        self::assertSame($plan['protected'], $afterPlan['protected']);
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $service->apply($this->private.'/preview.json', $this->private.'/unused.json'));
        self::assertFileDoesNotExist($this->private.'/unused.json'); self::assertSame($after, $this->snapshot());
    }

    public function test_partial_new_question_bank_uuid_or_allocated_id_collision_refuses_before_backup(): void
    {
        foreach (['uuid', 'bank', 'id'] as $kind) {
            $service = $this->service(); $service->savePlan($this->private.'/preview-'.$kind.'.json'); $row = $this->expected['questions'][0];
            if ($kind !== 'id') { $row['id'] = 1; }
            if ($kind === 'uuid') { $row['seeder'] = 'Synthetic\\Foreign'; }
            if ($kind === 'bank') { $row['uuid'] = 'synthetic-foreign-bank-member'; }
            if ($kind === 'id') { $row['uuid'] = 'synthetic-foreign-id'; $row['seeder'] = 'Synthetic\\Foreign'; }
            DB::table('questions')->insert($row); $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->private.'/preview-'.$kind.'.json', $this->private.'/no-backup-'.$kind.'.json'));
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->private.'/no-backup-'.$kind.'.json'); DB::table('questions')->where('id', $row['id'])->delete();
        }
    }

    public function test_stale_evidence_allocation_taxonomy_hero_and_option_rows_refuse_without_writes(): void
    {
        $old = $this->evidence;
        foreach ([
            static function (&$e) { $e['_inventory']['allocation']['questions']['next_id']++; },
            static function (&$e) { $e['_inventory']['bindings'][1]['level'] = 'A2'; },
            static function (&$e) { $e['_inventory']['question_collisions']['questions'] = [['id' => 1]]; },
            static function (&$e) { $e['ids']['questions'][Supplement::UUIDS[0]]++; },
        ] as $change) { $this->evidence = $old; $change($this->evidence); $this->seal(); $before = $this->snapshot(); $this->refused(fn () => $this->service()->plan()); self::assertSame($before, $this->snapshot()); }
        $this->evidence = $old; $this->seal();
        foreach ([['categories', 45, 'name', 'Later category'], ['sources', Supplement::SOURCE_IDS[0], 'name', 'Later source'], ['text_blocks', Supplement::HERO_IDS[0], 'locale', 'en'], ['question_options', 1, 'updated_at', '2001-02-03 04:05:07']] as [$table, $id, $field, $value]) {
            $previous = DB::table($table)->where('id', $id)->value($field); DB::table($table)->where('id', $id)->update([$field => $value]); $before = $this->snapshot();
            $this->refused(fn () => $this->service()->plan()); self::assertSame($before, $this->snapshot()); DB::table($table)->where('id', $id)->update([$field => $previous]);
        }
    }

    public function test_failure_after_each_real_insert_table_rolls_back_content_one_practice_and_twenty_five_questions(): void
    {
        foreach (array_keys(Rows::ROW_COLUMNS) as $n => $table) {
            $service = $this->service(); $service->failTable = $table; $preview = $service->savePlan($this->private.'/rollback-preview-'.$n.'.json'); $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->private.'/rollback-preview-'.$n.'.json', $this->private.'/rollback-backup-'.$n.'.json'), 'Injected');
            self::assertSame($before, $this->snapshot()); self::assertSame($preview, json_decode(File::get($this->private.'/rollback-backup-'.$n.'.json'), true, flags: JSON_THROW_ON_ERROR));
        }
    }

    public function test_missing_edited_after_rows_and_unapproved_question_relations_are_not_noop(): void
    {
        $service = $this->service(); $service->savePlan($this->private.'/preview.json'); $service->apply($this->private.'/preview.json', $this->private.'/backup.json');
        foreach (['questions', 'question_answers', 'question_option_question', 'question_theory_text_blocks'] as $table) {
            $row = $this->expected[$table][0]; DB::table($table)->where('id', $row['id'])->delete(); $before = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($before, $this->snapshot()); DB::table($table)->insert($row);
        }
        $row = ['id' => 1, 'question_id' => $this->expected['questions'][0]['id'], 'provider' => 'synthetic', 'locale' => 'uk', 'hint' => 'Непогоджено.', 'created_at' => '2002-03-04 05:06:07', 'updated_at' => '2002-03-04 05:06:07'];
        DB::table('question_hints')->insert($row); $before = $this->snapshot(); $this->refused(fn () => $service->plan(), 'unapproved'); self::assertSame($before, $this->snapshot());
    }

    public function test_source_projection_rejects_wrong_owner_level_uid_context_case_multiplicity_and_collation(): void
    {
        foreach ([
            static function (&$s) { $s['banks'][0]['owner'] = Supplement::OWNERS[1]; },
            static function (&$s) { $s['banks'][1]['level'] = 'A2'; },
            static function (&$s) { $s['banks'][2]['questions'][0]['source_uid'] = Supplement::UUIDS[0]; },
            static function (&$s) { $s['banks'][0]['questions'][0]['context_uk'] = ''; },
            static function (&$s) { $s['banks'][0]['questions'][0]['tokens_correct'][1] = 'Have'; },
            static function (&$s) { array_pop($s['banks'][0]['questions'][0]['tokens_shuffled']); },
        ] as $change) { $source = $this->source; $change($source); $this->refused(fn () => Supplement::validate($source)); }
        $matches = $this->evidence['_inventory']['option_matches']; $bad = $matches; $bad['They'][] = $bad['They'][0];
        $this->refused(fn () => Supplement::project($this->source, Supplement::bindings(), $bad), 'ambiguous');
        $bad = $matches; $bad['have'][0]['option'] = 'Have'; $this->refused(fn () => Supplement::project($this->source, Supplement::bindings(), $bad), 'casing');
    }

    public function test_native_one_to_three_word_groups_and_short_fact_answer_are_finite_source_policy_only(): void
    {
        $source = $this->source;
        $source['banks'][0]['questions'][0]['tokens_correct'] = ['Yes,', 'I', 'do.'];
        $source['banks'][0]['questions'][0]['tokens_shuffled'] = ['do.', 'Yes,', 'I'];
        $source['banks'][0]['questions'][0]['canonical_en'] = 'Yes, I do.';
        $source['banks'][1]['questions'][0]['tokens_correct'] = ['I know', 'where Nina', 'works', '.'];
        $source['banks'][1]['questions'][0]['tokens_shuffled'] = ['.', 'works', 'I know', 'where Nina'];
        $source['banks'][1]['questions'][0]['canonical_en'] = 'I know where Nina works.';
        Supplement::validate($source); self::assertTrue(true);
        $bad = $source; $bad['banks'][1]['questions'][0]['tokens_correct'] = ['I know where Nina', 'works', '.'];
        $bad['banks'][1]['questions'][0]['tokens_shuffled'] = ['.', 'works', 'I know where Nina'];
        $this->refused(fn () => Supplement::validate($bad), 'one to three');
        $bad = $source; $bad['banks'][2]['questions'][0]['tokens_correct'] = ['Yes,', 'I', 'do.'];
        $bad['banks'][2]['questions'][0]['tokens_shuffled'] = ['do.', 'Yes,', 'I'];
        $bad['banks'][2]['questions'][0]['canonical_en'] = 'Yes, I do.';
        $this->refused(fn () => Supplement::validate($bad));
    }

    public function test_hook_requires_real_transaction_and_stale_private_evidence_or_existing_backup_is_never_overwritten(): void
    {
        $service = $this->service(); $service->plan(); $before = $this->snapshot();
        $this->refused(fn () => $service->invokeQuestionHook(0), 'transaction'); self::assertSame($before, $this->snapshot());
        $service->savePlan($this->private.'/preview.json'); File::put($this->private.'/owned-backup.json', 'Existing backup.');
        $this->refused(fn () => $service->apply($this->private.'/preview.json', $this->private.'/owned-backup.json'), 'exclusive');
        self::assertSame('Existing backup.', File::get($this->private.'/owned-backup.json')); self::assertSame($before, $this->snapshot());
        File::append($this->private.'/synthetic-evidence.json', "\n");
        $this->refused(fn () => $service->apply($this->private.'/preview.json', $this->private.'/no-stale-backup.json'), 'sealed');
        self::assertFileDoesNotExist($this->private.'/no-stale-backup.json'); self::assertSame($before, $this->snapshot());
    }

    public function test_stale_content_progress_and_tampered_preview_refuse_before_creating_backup(): void
    {
        $service = $this->service(); $service->savePlan($this->private.'/stale-preview.json');
        $id = Supplement::HERO_IDS[0]; $body = DB::table('text_blocks')->where('id', $id)->value('body');
        DB::table('text_blocks')->where('id', $id)->update(['body' => 'A later invented edit.']);
        $before = $this->snapshot();
        $this->refused(fn () => $service->apply($this->private.'/stale-preview.json', $this->private.'/stale-content-backup.json'));
        self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->private.'/stale-content-backup.json');
        DB::table('text_blocks')->where('id', $id)->update(['body' => $body]);
        DB::table('m84_protected_progress')->where('id', 1)->update(['payload' => 'A later invented progress row.']);
        $before = $this->snapshot();
        $this->refused(fn () => $service->apply($this->private.'/stale-preview.json', $this->private.'/stale-progress-backup.json'), 'stale');
        self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->private.'/stale-progress-backup.json');
        $preview = $service->savePlan($this->private.'/tampered-preview.json');
        $preview['inserts'][0]['fields']['level'] = 'A1';
        File::put($this->private.'/tampered-preview.json', json_encode($preview, JSON_THROW_ON_ERROR));
        $before = $this->snapshot();
        $this->refused(fn () => $service->apply($this->private.'/tampered-preview.json', $this->private.'/tampered-backup.json'));
        self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->private.'/tampered-backup.json');
    }
}

/** Exact finite owner IDs with invented SQLite bodies; never a working-database authority bypass. */
class M84SyntheticQuestionPackage
{
    public const OWNERS = Package::OWNERS;
    public const PAGE_IDS = Package::PAGE_IDS;
    public const CATEGORY_IDS = Package::CATEGORY_IDS;
    public const LEVELS = [null, null, null, null, null, null];
    public const PRACTICE_SOURCE_INDICES = [8, 8, 10, 8, 9, 5];
    public const CONTINUING_COUNTS = [10, 10, 12, 10, 11, 5];
    public const EXISTING_PRACTICE_UUIDS = Package::EXISTING_PRACTICE_UUIDS;
    public static array $patch = [];
    public static function load(?string $root = null): array { return [[], self::$patch]; }
    public static function profile(): array { return ['bindings' => array_fill(0, 6, [])]; }
}

/** Only invented source/evidence/planning is substituted. The real trait and M26 apply/backup remain inherited. */
class M84SyntheticQuestionWriter extends TheoryNativeContentPatch
{
    use M84QuestionInsertScope;
    public const NAMES = M84SyntheticQuestionPackage::OWNERS;
    public const BEFORE_FILE = 'synthetic-evidence.json';
    public const BEFORE_SHA = 'synthetic-only';
    protected const ID = 'm84-synthetic-question-insert-scope';
    public ?string $failTable = null;
    public function __construct(Connection $db, string $databasePath, string $private, private string $evidenceSha, private array $source)
    { parent::__construct($db, $databasePath, $private); }
    protected static function packageClass(): string { return M84SyntheticQuestionPackage::class; }
    protected static function policy(): array { return []; }
    public function connection(?string $expected = null, bool $writing = false): array
    { IsolatedTestEnvironment::assertSafeDatabase($this->db); return ['driver' => 'sqlite', 'database' => ':memory:']; }
    protected function questionSource(): array { return $this->source; }
    protected function questionEvidence(): array
    {
        IsolatedTestEnvironment::assertOwnedPath($this->privateDirectory); $path = $this->privateDirectory.'/synthetic-evidence.json';
        if (is_link($path) || !is_file($path) || !hash_equals($this->evidenceSha, hash_file('sha256', $path))) { throw new RuntimeException('Synthetic M84 sealed evidence changed.'); }
        return json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
    }
    protected function afterQuestionRowInsert(string $table, int $index): void { if ($this->failTable === $table) { throw new RuntimeException('Injected synthetic M84 failure.'); } }
    public function invokeQuestionHook(int $index): void { $this->afterInsert($index); }
    public function plan(bool $lock = false): array
    {
        $patch = $this->questionPatch(); $evidence = $this->questionEvidence();
        $plan = ['patch' => self::ID, 'version' => 1, 'names' => self::NAMES, 'connection' => $this->connection(),
            'sources' => ['private:synthetic-evidence.json' => $this->evidenceSha], 'pages' => [], 'updates' => [], 'inserts' => []];
        $states = []; $ids = [];
        foreach (self::NAMES as $i => $owner) {
            $page = $this->rows('pages', static fn ($q) => $q->where('seeder', $owner), $lock)[0];
            $blocks = $this->rows('text_blocks', static fn ($q) => $q->where('page_id', $page['id']), $lock);
            foreach ($evidence['_before']['pages'][$owner]['blocks'] as $old) {
                $row = array_values(array_filter($blocks, static fn ($b) => $b['id'] === $old['id']))[0];
                $body = $old['type'] === 'hero' ? 'Synthetic hero after '.$i : 'Synthetic practice after '.$i;
                $state = $this->candidate($plan['updates'], 'text_blocks', $row, $owner, ['body' => $old['body']], ['body' => $body]);
                if ($state !== null) { $states[] = $state; }
            }
            $slot = M84SyntheticQuestionPackage::PRACTICE_SOURCE_INDICES[$i];
            if ($i === 5) {
                $config = $patch['targets'][$i]['after']['page']['blocks'][$slot]; $matches = array_values(array_filter($blocks, static fn ($b) => $b['uuid'] === $config['uuid']));
                $fields = ['uuid' => $config['uuid'], 'page_id' => $page['id'], 'page_category_id' => M84SyntheticQuestionPackage::CATEGORY_IDS[$i], 'locale' => 'uk', 'type' => 'practice-set', 'column' => 'left',
                    'heading' => null, 'css_class' => null, 'sort_order' => $slot + 1, 'body' => $config['body'], 'level' => $config['level'], 'seeder' => $owner];
                if ($matches === []) { $plan['inserts'][] = ['table' => 'text_blocks', 'seeder' => $owner, 'fields' => $fields, 'sha256' => self::digest($fields)]; }
                else { foreach ($fields as $field => $value) { if ($matches[0][$field] !== $value) { throw new RuntimeException('Synthetic appended practice changed.'); } } }
            }
            array_push($ids, ...array_column($blocks, 'id'));
            $plan['pages'][$owner] = ['page' => $page, 'category_ancestry' => [], 'blocks' => $blocks, 'relations' => $this->relations($page, $blocks, $lock)];
        }
        if (count(array_unique($states)) !== 1) { throw new RuntimeException('Synthetic partial content state.'); }
        $plan['state'] = $states[0]; $this->extendPlan($plan, $patch, null, $lock);
        $plan['protected'] = $this->protectedFingerprints($ids); ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }
}
