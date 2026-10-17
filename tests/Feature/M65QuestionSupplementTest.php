<?php

namespace Tests\Feature;

use App\Services\M51ContentPatch;
use App\Services\M65ContentPatch as Patch;
use App\Support\Database\JsonPageSeeder;
use App\Support\M26DetailPackage;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M65BuilderSelectionPresentation;
use App\Support\M65PurposeGerundInfinitivePackage as Package;
use App\Support\M65QuestionSupplement as Supplement;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Real inherited backup/transaction against private SQLite; never the working environment or MySQL. */
class M65QuestionSupplementTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $private;
    private array $patch;
    private array $evidence;
    private array $audit;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        [$before, $this->patch, , $contract] = Package::load();
        $this->audit = json_decode(File::get(base_path(M65BuilderSelectionPresentation::AUDIT)), true, flags: JSON_THROW_ON_ERROR);
        $this->root = storage_path('app/m65-question-fixture-'.bin2hex(random_bytes(8))); $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app')); File::makeDirectory($this->private, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($this->root);
        foreach (array_unique([...Patch::sourcePaths($this->patch, $contract), Supplement::SOURCE, M65BuilderSelectionPresentation::AUDIT]) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true); File::copy(base_path($path), $this->root.'/'.$path);
        }
        DB::table('page_categories')->insert([
            ['id'=>34,'slug'=>'clauses-and-linking-words','title'=>'Clauses','type'=>'theory','language'=>'uk'],
            ['id'=>33,'slug'=>'verb-patterns','title'=>'Дієслівні моделі','type'=>'theory','language'=>'uk'],
        ]);
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $file = $this->root.'/'.$target['path'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i], 'seeder' => $target['identity'],
                'slug' => $old['slug'], 'title' => $old['page']['title'], 'text' => $old['page']['subtitle_text'], 'type' => 'theory']);
            File::put($file, M26DetailPackage::json($old));
            (new class($file) extends JsonPageSeeder {
                public function __construct(private string $file) {}
                protected function definitionPath(): string { return $this->file; }
            })->run();
            File::put($file, M26DetailPackage::json($target['after']));
            $foreign = (array) DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('type', 'hero')->first();
            foreach (['en', 'pl'] as $locale) {
                $copy = $foreign; unset($copy['id']); $copy['locale'] = $locale; $copy['uuid'] = 'm65-protected-'.$i.'-'.$locale; $copy['body'] = 'Unchanged '.$locale;
                DB::table('text_blocks')->insert($copy);
            }
        }
        $time = '2026-01-01 12:00:00';
        DB::table('categories')->insert(['id' => 45, 'name' => 'English Sentence Builder', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('sources')->insert(['id' => 308, 'name' => 'Purpose', 'created_at' => $time, 'updated_at' => $time]);
        foreach ($this->audit['targets'] as $i => $target) {
            $path = 'database/seeders/V3/Polyglot/'.class_basename($target['old_bank_seeder']).'/definition.json';
            $source = json_decode(File::get(base_path($path)), true, flags: JSON_THROW_ON_ERROR);
            $questions = array_values(array_filter($source['questions'], static fn ($q) => $q['level'] === $target['level']));
            usort($questions, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            $ids = $target['expected_old_pool_ids'];
            foreach ($questions as $n => $q) {
                $id = $ids[$n];
                DB::table('questions')->insert(['id' => $id, 'uuid' => $q['uuid'], 'question' => $q['question'], 'difficulty' => 3,
                    'level' => $q['level'], 'type' => '4', 'category_id' => 45, 'source_id' => 308, 'seeder' => $target['bank_seeders'][0],
                    'created_at' => $time, 'updated_at' => $time]);
                foreach ($q['answers'] as $marker => $token) {
                    DB::table('question_answers')->insert(['question_id' => $id, 'option_id' => $this->option($token), 'marker' => $marker,
                        'created_at' => $time, 'updated_at' => $time]);
                }
                foreach (array_unique(array_map(fn ($token) => $this->option($token), $q['options'])) as $oid) {
                    DB::table('question_option_question')->insert(['question_id' => $id, 'option_id' => $oid, 'flag' => null]);
                }
                $oldBlock = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('locale', 'uk')->where('sort_order', 1)->value('uuid');
                DB::table('question_theory_text_blocks')->insert(['question_uuid' => $q['uuid'], 'text_block_uuid' => $oldBlock, 'position' => 0,
                    'created_at' => $time, 'updated_at' => $time]);
            }
        }
        DB::table('question_hints')->insert(['question_id' => 30615, 'provider' => 'protected', 'locale' => 'uk', 'hint' => 'Чинний старий текст.']);
        DB::connection()->getSchemaBuilder()->dropIfExists('m65_protected_progress');
        DB::connection()->getSchemaBuilder()->create('m65_protected_progress', static function (Blueprint $table): void { $table->id(); $table->text('value'); });
        DB::table('m65_protected_progress')->insert(['value' => 'Original progress.']);
        $source = Supplement::load();
        foreach ($source['questions'] as $q) { foreach ($q['options'] as $token) { $this->option($token); } }
        $this->evidence = $this->buildEvidence($source);
    }

    private function option(string $token): int
    {
        $existing = DB::table('question_options')->whereRaw('LOWER(option) = ?', [mb_strtolower($token)])->value('id');
        if ($existing !== null) { return (int) $existing; }
        $value = in_array($token, ['Iryna', 'Friday', 'June', 'July', 'Max', 'English', 'Marta'], true) ? $token : mb_strtolower($token);

        return DB::table('question_options')->insertGetId(['option' => $value, 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
    }

    private function rows(string $table): array { return DB::table($table)->orderBy('id')->get()->map(static fn ($row) => (array) $row)->all(); }

    private function buildEvidence(array $source): array
    {
        $target = $this->patch['targets'][0]; $position = count($target['after']['page']['blocks']); $practice = $target['after']['page']['blocks'][$position - 1];
        $binding = ['owner' => Supplement::OWNER, 'level' => 'B1', 'category_id' => 45, 'source_id' => 308,
            'practice_uuid' => M26DetailPackage::uuid(Supplement::OWNER, $practice, $position)];
        $matches = [];
        foreach ($source['questions'] as $q) {
            foreach ($q['options'] as $token) { $matches[$token] = ['requested' => $token, 'rows' => DB::table('question_options')
                ->whereRaw('LOWER(option) = ?', [mb_strtolower($token)])->orderBy('id')->get()->map(static fn ($r) => (array) $r)->all()]; }
        }
        $projection = Supplement::project($source, $binding, M51ContentPatch::optionRowsFromInventory($matches)); $ids = []; $allocation = [];
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            $max = (int) (DB::table($table)->max('id') ?? 0); $next = $max + 1;
            $allocation[$table] = ['max_id' => $max, 'auto_increment' => ['next_id' => $next]]; $ids[$table] = [];
            foreach ($projection[$table] as $row) { $ids[$table][$row['key']] = $next++; }
        }
        $taxonomy = ['categories' => $this->rows('categories'), 'sources' => $this->rows('sources')];

        return ['schema_version' => 1, 'base_sha' => Package::BASE_SHA, 'before_sha256' => Patch::BEFORE_SHA,
            'source_sha256' => Supplement::SOURCE_SHA, 'audit_sha256' => M65BuilderSelectionPresentation::AUDIT_SHA,
            'binding' => $binding, 'projection' => $projection, 'ids' => $ids, 'taxonomy' => $taxonomy, 'row_timestamp'=>'2026-01-01 12:00:00',
            '_inventory' => ['schema_version' => 1, 'base_sha' => Package::BASE_SHA, 'source_draft_sha256' => Patch::QUESTION_INVENTORY_DRAFT_SHA,
                'created_at'=>'2026-01-01T12:00:00Z', 'question_collisions' => [], 'option_matches' => $matches, 'taxonomy' => $taxonomy, 'allocation' => $allocation]];
    }

    private function service(): M65SyntheticQuestionPatch
    {
        $service = new M65SyntheticQuestionPatch(DB::connection(), $this->root.'/database', $this->private);
        $service->fixtureEvidence = $this->evidence;

        return $service;
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
        try { $call(); self::fail('Expected M65 supplement refusal.'); }
        catch (RuntimeException $error) { self::assertNotSame('', $error->getMessage()); }
    }

    public function test_real_inherited_preview_backup_transaction_three_questions_and_repeated_noop_preserve_old_families(): void
    {
        $service = $this->service(); $original = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($original, $this->snapshot()); self::assertSame('before', $plan['state']); self::assertCount(3, $plan['inserts']);
        $projection = $plan['question_supplement']['evidence']['projection'];
        self::assertSame([3, 0, 38, 44, 3], array_values(array_map('count', array_intersect_key($projection, Rows::ROW_COLUMNS))));
        self::assertSame('applied', $service->apply($this->file('preview'), $this->file('backup'))['status']); self::assertFileExists($this->file('backup'));
        $after = $service->savePlan($this->file('after')); self::assertSame('after', $after['state']);
        self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']); self::assertSame($plan['protected'], $after['protected']);
        $snapshot = $this->snapshot();
        foreach ($original as $table => $rows) {
            if (in_array($table, ['pages', 'text_blocks'], true)) { continue; }
            $newIds = array_values($this->evidence['ids'][$table] ?? []);
            self::assertSame($rows, array_values(array_filter($snapshot[$table], static fn ($row) => !isset($row['id']) || !in_array($row['id'], $newIds, true))), $table);
        }
        foreach (['preview', 'after'] as $name) {
            self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0,
                'question_families_inserted' => array_fill_keys(array_keys(Rows::ROW_COLUMNS), 0)],
                $service->apply($this->file($name), $this->file('unused-'.$name)));
            self::assertFileDoesNotExist($this->file('unused-'.$name));
        }
    }

    public function test_failure_in_each_actual_question_insert_family_rolls_back_every_content_and_question_write(): void
    {
        foreach (['questions', 'question_answers', 'question_option_question', 'question_theory_text_blocks'] as $family) {
            $service = new class(DB::connection(), $this->root.'/database', $this->private) extends M65SyntheticQuestionPatch {
                public string $failFamily;
                protected function afterQuestionRowInsert(string $table, int $index): void
                { if ($table === $this->failFamily && $index === 0) { throw new RuntimeException('Injected isolated family failure'); } }
            };
            $service->failFamily = $family; $service->fixtureEvidence = $this->evidence; $before = $this->snapshot();
            $service->savePlan($this->file('preview-'.$family));
            $this->refused(fn () => $service->apply($this->file('preview-'.$family), $this->file('backup-'.$family)));
            self::assertSame($before, $this->snapshot(), $family); self::assertFileExists($this->file('backup-'.$family));
            $bytes = File::get($this->file('backup-'.$family));
            $this->refused(fn () => $this->service()->apply($this->file('preview-'.$family), $this->file('backup-'.$family)));
            self::assertSame($bytes, File::get($this->file('backup-'.$family))); self::assertSame($before, $this->snapshot());
        }
    }

    public function test_existing_taxonomy_options_lineage_ids_foreign_bindings_and_collision_edits_refuse_without_writes(): void
    {
        foreach (['categories' => ['id' => 45, 'field' => 'name'], 'sources' => ['id' => 308, 'field' => 'name'],
            'question_options' => ['id' => $this->evidence['projection']['reused_options'][0]['id'], 'field' => 'option']] as $table => $key) {
            $old = (array) DB::table($table)->where('id', $key['id'])->first(); DB::table($table)->where('id', $key['id'])->update([$key['field'] => 'Changed']);
            $snapshot = $this->snapshot(); $this->refused(fn () => $this->service()->plan()); self::assertSame($snapshot, $this->snapshot());
            DB::table($table)->where('id', $key['id'])->update([$key['field'] => $old[$key['field']]]);
        }
        foreach (['base', 'before', 'source', 'audit', 'owner', 'level', 'practice', 'ids', 'projection', 'short-pool', 'old-key'] as $case) {
            $service = $this->service();
            if (in_array($case, ['base', 'before', 'source', 'audit'], true)) {
                $field = ['base' => 'base_sha', 'before' => 'before_sha256', 'source' => 'source_sha256', 'audit' => 'audit_sha256'][$case];
                $service->fixtureEvidence[$field] = str_repeat('0', 64);
            } elseif (in_array($case, ['owner', 'level', 'practice'], true)) {
                $field = ['owner' => 'owner', 'level' => 'level', 'practice' => 'practice_uuid'][$case]; $service->fixtureEvidence['binding'][$field] = 'foreign';
            } elseif ($case === 'ids') { $service->fixtureEvidence['ids']['questions'][Supplement::UUIDS[0]]++; }
            elseif ($case === 'projection') { $service->fixtureEvidence['projection']['questions'][0]['fields']['type'] = '0'; }
            elseif ($case === 'short-pool') { DB::table('questions')->where('id', 30626)->update(['level' => 'A2']); }
            elseif ($case === 'old-key') { DB::table('questions')->where('id', 30615)->update(['question' => 'Стороння умова.']); }
            $before = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($before, $this->snapshot(), $case);
            if ($case === 'short-pool') { DB::table('questions')->where('id', 30626)->update(['level' => 'B1']); }
            if ($case === 'old-key') { DB::table('questions')->where('id', 30615)->update(['question' => 'Марта все записала, щоб уникнути помилок']); }
        }
        DB::table('questions')->insert(['uuid' => 'unrelated-sequence-change', 'question' => 'Стороннє питання.', 'seeder' => 'ForeignBank']);
        $before = $this->snapshot(); $this->refused(fn () => $this->service()->plan()); self::assertSame($before, $this->snapshot());
    }

    public function test_applied_rows_extra_links_hints_and_option_tampering_cannot_be_omitted_from_protection(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $service->apply($this->file('preview'), $this->file('backup'));
        $id = $this->evidence['ids']['questions'][Supplement::UUIDS[0]];
        DB::table('questions')->where('id', $id)->update(['question' => 'Edited after apply.']); $this->refused(fn () => $service->plan());
        DB::table('questions')->where('id', $id)->update(['question' => Supplement::load()['questions'][0]['question']]);
        $cases = [
            ['questions', $id, 'type', '0'],
            ['question_answers', array_values($this->evidence['ids']['question_answers'])[0], 'marker', 'a99'],
            ['question_answers', array_values($this->evidence['ids']['question_answers'])[0], 'updated_at', '2000-01-01 00:00:00'],
            ['question_option_question', array_values($this->evidence['ids']['question_option_question'])[0], 'flag', 1],
            ['question_theory_text_blocks', array_values($this->evidence['ids']['question_theory_text_blocks'])[0], 'position', 99],
            ['question_options', $this->evidence['projection']['reused_options'][0]['id'], 'option', 'Foreign'],
        ];
        foreach ($cases as [$table, $rowId, $field, $value]) {
            $original = (array) DB::table($table)->where('id', $rowId)->first();
            DB::table($table)->where('id', $rowId)->update([$field => $value]); $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot(), $table.':'.$field);
            DB::table($table)->where('id', $rowId)->update([$field => $original[$field]]);
        }
        $foreign = DB::table('text_blocks')->where('page_id', 35)->where('locale', 'uk')->where('sort_order', 1)->value('uuid');
        $link = DB::table('question_theory_text_blocks')->insertGetId(['question_uuid' => Supplement::UUIDS[0], 'text_block_uuid' => $foreign, 'position' => 0]);
        $this->refused(fn () => $service->plan()); DB::table('question_theory_text_blocks')->where('id', $link)->delete();
        DB::table('question_hints')->insert(['question_id' => $id, 'provider' => 'unexpected', 'locale' => 'uk', 'hint' => 'Непогоджене доповнення.']);
        self::assertNotSame($service->plan()['protected'], json_decode(File::get($this->file('preview')), true)['protected']);
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created'))); self::assertFileDoesNotExist($this->file('not-created'));
    }
}

/** Only immutable private-file reading is replaced with explicitly synthetic SQLite evidence. */
class M65SyntheticQuestionPatch extends Patch
{
    public array $fixtureEvidence;
    protected function questionEvidence(): array { return $this->fixtureEvidence; }
}
