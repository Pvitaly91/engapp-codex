<?php

namespace App\Services;

use App\Support\M51QuestionSupplement as Rows;
use App\Support\M74OtherPronounsBasics as Package;
use App\Support\M74QuestionSupplement as Supplement;
use Illuminate\Database\Connection;
use RuntimeException;

/** One exact linked-bank body transition and one insert-only question family. */
class M74BankCompletionPatch extends M26ContentPatch
{
    public const NAMES = [Supplement::OWNER];
    public const PREVIOUS_PATCH = 'database/content-patches/m74-other-pronouns-basics.v1.json';
    public const PREVIOUS_PATCH_SHA = 'af216b9846f40bbb4bf86cd414fd7305a5449e6d4e115f082eddf662afbf859d';
    public const NEXT_PATCH = 'database/content-patches/m74-other-pronouns-basics.v1.0.1.json';
    public const NEXT_PATCH_SHA = '7346e58f395daaceaec2d2630f66ee49b52794db958e83ecc82d50543bcb77a8';
    public const BASELINE_FILE = 'final-readonly-v1.json';
    public const BASELINE_SHA = '0046548da833c5e4bccc90499fd5826f78c3d91194281c216c12149e4a3e38fd';
    public const INVENTORY_FILE = 'bank-completion-inventory-v1.json';
    public const INVENTORY_SHA = '0647df860679b640b0cb6bbe8dd8fe6752cf3218faf81f9644a263b72062dd80';
    public const QUESTION_PLAN_FILE = 'bank-completion-question-plan-v1.json';
    public const QUESTION_PLAN_SHA = '392401b69a9f09b925fd8af38a394214567fd7a81ad269cf3aaec733b83430c8';
    public const PRACTICE_ID = 12760;
    protected const ID = 'm74-another-bank-completion-v1.0.1';

    protected function localGuard(): string { return M74LocalTargetGuard::class; }
    protected function expectedInsertCount(): int { return 0; }
    protected function allowedPageUpdateFields(): array { return []; }
    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        throw new RuntimeException('M74 bank completion restore is outside the approved scope.');
    }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        if ($this->db->getDriverName() === 'mysql') {
            M74LocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget !== 'gramlyze.loc' || $this->localProof === null) {
                throw new RuntimeException('Bank preview/apply requires exact local target and fresh nonce proof.');
            }
        } elseif ($this->db->getDriverName() !== 'sqlite' || !app()->environment('testing')) {
            throw new RuntimeException('Only isolated testing SQLite or proven local MySQL is supported.');
        }
        return parent::connection($expected, $writing);
    }

    public static function fileHash(string $path): string
    {
        if (!is_file($path) || is_link($path)) { throw new RuntimeException('Bank evidence is missing or linked.'); }
        $sha = hash_file('sha256', $path);
        if (!is_string($sha)) { throw new RuntimeException('Bank evidence cannot be hashed.'); }
        return $sha;
    }

    private function pinned(string $path, string $sha): array
    {
        if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || !hash_equals($sha, self::fileHash($path))) {
            throw new RuntimeException('Bank source/evidence is not reviewed and frozen.');
        }
        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    /** All tables and every original field remain protected; only complete proven new rows may be omitted. */
    public static function fingerprints(Connection $db, array $omit = [], ?array $bodyBefore = null): array
    {
        $tables = $db->getSchemaBuilder()->getTableListing(schema: $db->getDriverName() === 'sqlite' ? 'main' : $db->getDatabaseName(), schemaQualified: false);
        sort($tables); $maps = []; $seen = []; $result = []; $bodySeen = false;
        foreach ($omit as $table => $rows) { foreach ($rows as $row) {
            if (!is_int($row['id'] ?? null) || isset($maps[$table][$row['id']])) { throw new RuntimeException('Exact omitted row identity is invalid.'); }
            $maps[$table][$row['id']] = $row;
        } }
        foreach ($tables as $table) {
            $columns = $db->getSchemaBuilder()->getColumnListing($table); $query = $db->table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $context = hash_init('sha256'); $count = 0;
            foreach ($query->cursor() as $record) {
                $row = (array) $record; $id = $row['id'] ?? null;
                if (isset($maps[$table][$id])) {
                    $expected = $maps[$table][$id]; ksort($expected); $actual = $row; ksort($actual);
                    if ($actual !== $expected) { throw new RuntimeException('A new row is not its complete reviewed projection.'); }
                    $seen[$table][$id] = true; continue;
                }
                if ($bodyBefore !== null && $table === 'text_blocks' && $id === $bodyBefore['id']) {
                    if ($row['body'] !== $bodyBefore['after']) { throw new RuntimeException('Applied practice body differs from exact successor.'); }
                    $row['body'] = $bodyBefore['before']; $bodySeen = true;
                }
                hash_update($context, self::digest($row)."\n"); ++$count;
            }
            $result[$table] = ['count' => $count, 'sha256' => hash_final($context)];
        }
        foreach ($maps as $table => $rows) { if (count($seen[$table] ?? []) !== count($rows)) { throw new RuntimeException('An exact inserted row disappeared.'); } }
        if ($bodyBefore !== null && !$bodySeen) { throw new RuntimeException('Exact practice normalization target disappeared.'); }
        return $result;
    }

    /** Read-only inventory; the caller owns an explicit READ ONLY transaction and exclusive receipt. */
    public function inventory(): array
    {
        $root = dirname($this->databasePath); $source = Supplement::load($root);
        $baseline = $this->pinned($this->privateDirectory.'/'.self::BASELINE_FILE, self::BASELINE_SHA);
        $old = $this->pinned($root.'/'.self::PREVIOUS_PATCH, self::PREVIOUS_PATCH_SHA);
        $block = $this->db->table('text_blocks')->where('id', self::PRACTICE_ID)->sole(); $block = (array) $block;
        $oldBody = $old['targets'][0]['after']['page']['blocks'][6]['body'];
        $this->assertPractice($block, $oldBody);
        if ($this->db->getDriverName() !== 'mysql' || M74LocalTargetGuard::publicIdentity($this->db, $root.'/public') !== $baseline['runtime']
            || ($baseline['independent_before_projection_exact'] ?? null) !== true || count($baseline['sql_after'] ?? []) !== 46
            || self::fingerprints($this->db) !== $baseline['sql_after']) {
            throw new RuntimeException('Inventory is not the exact reviewed bank BEFORE/runtime.');
        }
        $taxonomyQuestion = (array) $this->db->table('questions')->where('id', 32707)->sole();
        if ($taxonomyQuestion['seeder'] !== Supplement::OLD_BANK || $taxonomyQuestion['type'] !== '4'
            || $taxonomyQuestion['level'] !== 'B1' || $taxonomyQuestion['category_id'] !== Supplement::CATEGORY_ID
            || $taxonomyQuestion['source_id'] !== Supplement::SOURCE_ID) { throw new RuntimeException('Existing question taxonomy differs.'); }
        $binding = ['owner' => Supplement::OWNER, 'level' => 'B1', 'category_id' => $taxonomyQuestion['category_id'],
            'source_id' => $taxonomyQuestion['source_id'], 'practice_uuid' => Supplement::PRACTICE_UUID];
        $tokens = [...$source['questions'][0]['tokens_correct'], ...$source['questions'][0]['distractors']]; $matches = [];
        foreach ($tokens as $token) {
            $matches[$token] = $this->db->table('question_options')->where('option', $token)->orderBy('id')->get()->map(static fn ($r) => (array) $r)->all();
        }
        $projection = Supplement::project($source, $binding, $matches); $schema = []; $allocation = []; $ids = [];
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            $schema[$table] = $this->db->getSchemaBuilder()->getColumns($table);
            $auto = (array) $this->db->selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$table]);
            $allocation[$table] = ['max_id' => (int) ($this->db->table($table)->max('id') ?? 0), 'next_id' => (int) ($auto['next_id'] ?? 0)];
            $start = max($allocation[$table]['max_id'] + 1, $allocation[$table]['next_id']); $ids[$table] = [];
            foreach (array_column($projection[$table], 'key') as $offset => $key) { $ids[$table][$key] = $start + $offset; }
        }
        $time = gmdate('Y-m-d H:i:s'); $expected = Rows::materialize($projection, $ids, $time);
        Rows::assertAbsent($this->questionRows($expected, false));
        $variables = (array) $this->db->selectOne('SELECT @@auto_increment_increment AS step_value, @@auto_increment_offset AS offset_value');
        if ([(int) $variables['step_value'], (int) $variables['offset_value']] !== [1, 1]) { throw new RuntimeException('Exact 1/1 ID step/offset required.'); }
        return ['schema_version' => 1, 'kind' => 'M74_bank_completion_inventory', 'captured_at' => gmdate('c'),
            'baseline_file' => self::BASELINE_FILE, 'baseline_sha256' => self::BASELINE_SHA, 'source_sha256' => Supplement::SOURCE_SHA,
            'runtime' => $baseline['runtime'], 'sql_before' => $baseline['sql_after'], 'practice_before' => $block,
            'taxonomy_question' => $taxonomyQuestion, 'binding' => $binding, 'option_matches' => $matches,
            'schema' => $schema, 'allocation' => $allocation, 'ids' => $ids, 'row_timestamp' => $time,
            'projection' => $projection, 'materialized' => $expected, 'db_writes' => 0, 'http_requests' => 0];
    }

    protected function loadEvidence(): array
    {
        $root = dirname($this->databasePath); $source = Supplement::load($root);
        $inventory = $this->pinned($this->privateDirectory.'/'.self::INVENTORY_FILE, self::INVENTORY_SHA);
        $evidence = $this->pinned($this->privateDirectory.'/'.self::QUESTION_PLAN_FILE, self::QUESTION_PLAN_SHA);
        $baseline = $this->pinned($this->privateDirectory.'/'.self::BASELINE_FILE, self::BASELINE_SHA);
        if (($inventory['kind'] ?? null) !== 'M74_bank_completion_inventory' || ($inventory['source_sha256'] ?? null) !== Supplement::SOURCE_SHA
            || ($inventory['baseline_sha256'] ?? null) !== self::BASELINE_SHA || ($inventory['runtime'] ?? null) !== $baseline['runtime']
            || ($inventory['sql_before'] ?? null) !== $baseline['sql_after'] || ($inventory['db_writes'] ?? null) !== 0
            || ($inventory['http_requests'] ?? null) !== 0 || ($evidence['inventory_sha256'] ?? null) !== self::INVENTORY_SHA
            || ($evidence['source_sha256'] ?? null) !== Supplement::SOURCE_SHA || ($evidence['baseline_sha256'] ?? null) !== self::BASELINE_SHA) {
            throw new RuntimeException('Bank source/inventory/plan/baseline lineage differs.');
        }
        $projection = Supplement::project($source, $inventory['binding'], $inventory['option_matches']);
        if (array_keys($inventory['ids'] ?? []) !== array_keys(Rows::ROW_COLUMNS)
            || array_keys($inventory['allocation'] ?? []) !== array_keys(Rows::ROW_COLUMNS)
            || !is_string($inventory['row_timestamp'] ?? null)
            || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $inventory['row_timestamp']) !== 1) {
            throw new RuntimeException('Exact inventory allocation family or timestamp shape differs.');
        }
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            $allocation = $inventory['allocation'][$table]; $expectedIds = [];
            if (array_keys($allocation) !== ['max_id', 'next_id'] || !is_int($allocation['max_id'])
                || !is_int($allocation['next_id']) || $allocation['max_id'] < 0 || $allocation['next_id'] < 1) {
                throw new RuntimeException('Exact inventory allocation values differ.');
            }
            $start = max($allocation['max_id'] + 1, $allocation['next_id']);
            foreach (array_column($projection[$table], 'key') as $offset => $key) { $expectedIds[$key] = $start + $offset; }
            if ($inventory['ids'][$table] !== $expectedIds) { throw new RuntimeException('Reviewed sequential physical ID allocation differs.'); }
        }
        $expected = Rows::materialize($projection, $inventory['ids'], $inventory['row_timestamp']);
        if ($projection !== $inventory['projection'] || $expected !== $inventory['materialized']
            || ($evidence['projection'] ?? null) !== $projection || ($evidence['materialized'] ?? null) !== $expected
            || ($evidence['ids'] ?? null) !== $inventory['ids'] || ($evidence['row_timestamp'] ?? null) !== $inventory['row_timestamp']
            || array_map('count', $expected) !== ['questions' => 1, 'question_options' => 0, 'question_answers' => 4,
                'question_option_question' => 8, 'question_theory_text_blocks' => 1]) { throw new RuntimeException('Exact fourteen insert-only rows differ.'); }
        return compact('source', 'inventory', 'evidence', 'baseline', 'projection', 'expected');
    }

    protected function assertTeachingSources(): array
    {
        $root = dirname($this->databasePath); $old = $this->pinned($root.'/'.self::PREVIOUS_PATCH, self::PREVIOUS_PATCH_SHA);
        $next = $this->pinned($root.'/'.self::NEXT_PATCH, self::NEXT_PATCH_SHA);
        $before = $old['targets'][0]['after']['page']['blocks'][6]['body']; $after = $next['targets'][0]['after']['page']['blocks'][6]['body'];
        $oldData = json_decode($before, true, flags: JSON_THROW_ON_ERROR); $newData = json_decode($after, true, flags: JSON_THROW_ON_ERROR);
        if (($oldData['linked_practice']['seeder_classes'] ?? null) !== [Supplement::OLD_BANK]
            || ($newData['linked_practice']['seeder_classes'] ?? null) !== [Supplement::OLD_BANK, Supplement::BANK]) {
            throw new RuntimeException('Only exact old plus supplemental thematic banks are allowed.');
        }
        $normalized = $newData; $normalized['linked_practice']['seeder_classes'] = [Supplement::OLD_BANK];
        if ($normalized !== $oldData || $after === $before) { throw new RuntimeException('Bank revision rewrites learning fields beyond one bank list.'); }
        $normalized = $next; $normalized['targets'][0]['after']['page']['blocks'][6]['body'] = $before;
        if ($normalized !== $old) { throw new RuntimeException('Bank revision changes another content-patch field.'); }
        [$packageBefore, $active, $master, $contract] = Package::load($root); Package::validate($packageBefore, $active, $master, $contract);
        if ($active !== $next) { throw new RuntimeException('Active native content package is not exact reviewed bank successor.'); }
        $sources = [self::PREVIOUS_PATCH => self::PREVIOUS_PATCH_SHA, self::NEXT_PATCH => self::NEXT_PATCH_SHA,
            Supplement::SOURCE => Supplement::SOURCE_SHA, 'app/Support/M51QuestionSupplement.php' => self::fileHash($root.'/app/Support/M51QuestionSupplement.php'),
            'app/Support/M74QuestionSupplement.php' => self::fileHash($root.'/app/Support/M74QuestionSupplement.php'),
            'app/Services/M74BankCompletionPatch.php' => self::fileHash(__FILE__)];
        foreach (Package::profile()['artifacts'] as $path => $sha) { $sources[$path] = $sha; }
        foreach ($next['targets'] as $target) {
            $path = $target['path']; $sources[$path] = self::fileHash($root.'/'.$path);
            if (json_decode(file_get_contents($root.'/'.$path), true, flags: JSON_THROW_ON_ERROR) !== $target['after']) {
                throw new RuntimeException('Active versioned definition differs from exact successor.');
            }
        }
        return ['before' => $before, 'after' => $after, 'sources' => $sources];
    }

    protected function evidencePins(): array
    {
        return ['private:'.self::INVENTORY_FILE => self::INVENTORY_SHA,
            'private:'.self::QUESTION_PLAN_FILE => self::QUESTION_PLAN_SHA,
            'private:'.self::BASELINE_FILE => self::BASELINE_SHA];
    }

    private function assertPractice(array $row, string $body): void
    {
        foreach (['id' => self::PRACTICE_ID, 'uuid' => Supplement::PRACTICE_UUID, 'page_id' => 81, 'page_category_id' => 16,
            'locale' => 'uk', 'seeder' => Supplement::OWNER, 'type' => 'practice-set', 'column' => 'left', 'sort_order' => 7,
            'heading' => null, 'css_class' => null, 'level' => 'B1', 'body' => $body] as $key => $value) {
            if (($row[$key] ?? null) !== $value) { throw new RuntimeException('Exact bank practice identity/body differs: '.$key); }
        }
    }

    protected function questionRows(array $expected, bool $lock): array
    {
        $out = []; $questionId = $expected['questions'][0]['id'];
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            $ids = array_column($expected[$table], 'id');
            $out[$table] = $this->rows($table, static function ($query) use ($table, $ids, $questionId): void {
                $query->whereIn('id', $ids);
                if ($table === 'questions') { $query->orWhere('uuid', Supplement::UUID)->orWhere('seeder', Supplement::BANK); }
                elseif ($table === 'question_theory_text_blocks') { $query->orWhere('question_uuid', Supplement::UUID); }
                elseif (in_array($table, ['question_answers', 'question_option_question'], true)) { $query->orWhere('question_id', $questionId); }
            }, $lock);
        }
        return $out;
    }

    public function plan(bool $lock = false): array
    {
        $connection = $this->connection(); $scope = $this->loadEvidence(); $transition = $this->assertTeachingSources();
        $inventory = $scope['inventory']; $root = dirname($this->databasePath);
        $practice = $this->rows('text_blocks', static fn ($q) => $q->where('id', self::PRACTICE_ID), $lock);
        if (count($practice) !== 1) { throw new RuntimeException('Exact one bank practice required.'); } $practice = $practice[0];
        $state = $practice['body'] === $transition['before'] ? 'before' : ($practice['body'] === $transition['after'] ? 'after' : null);
        if ($state === null) { throw new RuntimeException('Practice is neither exact predecessor nor exact bank successor.'); }
        $this->assertPractice($practice, $transition[$state === 'before' ? 'before' : 'after']);
        $actual = $this->questionRows($scope['expected'], $lock);
        if ($state === 'before') { Rows::assertAbsent($actual); } else { Rows::assertApplied($scope['expected'], $actual); }
        foreach ($scope['projection']['reused_options'] as $option) {
            if ($this->rows('question_options', static fn ($q) => $q->where('id', $option['id']), $lock) !== [$option['row']]
                || self::digest($option['row']) !== $option['sha256']) { throw new RuntimeException('Original reused option changed.'); }
        }
        if ($this->rows('questions', static fn ($q) => $q->where('id', 32707), $lock) !== [$inventory['taxonomy_question']]) {
            throw new RuntimeException('Original taxonomy reference question changed.');
        }
        foreach (Rows::ROW_COLUMNS as $table => $columns) {
            $current = $this->db->getSchemaBuilder()->getColumns($table);
            if ($current !== $inventory['schema'][$table]) { throw new RuntimeException('Insert-family detailed schema changed.'); }
            $allocation = $inventory['allocation'][$table]; $count = count($scope['expected'][$table]);
            $max = (int) ($this->db->table($table)->max('id') ?? 0);
            $expectedMax = $state === 'before' || $count === 0 ? $allocation['max_id'] : max(array_column($scope['expected'][$table], 'id'));
            if ($max !== $expectedMax) { throw new RuntimeException('Reviewed row allocation changed.'); }
            if ($this->db->getDriverName() === 'mysql') {
                $auto = (array) $this->db->selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$table]);
                $expectedNext = $state === 'before' || $count === 0 ? $allocation['next_id'] : $expectedMax + 1;
                if ((int) $auto['next_id'] !== $expectedNext) { throw new RuntimeException('Physical automatic allocation changed.'); }
            }
        }
        $body = $state === 'after' ? ['id' => self::PRACTICE_ID, 'before' => $transition['before'], 'after' => $transition['after']] : null;
        $protected = self::fingerprints($this->db, $state === 'after' ? $scope['expected'] : [], $body);
        Rows::assertOriginalsUnchanged($inventory['sql_before'], $protected);
        $page = $this->rows('pages', static fn ($q) => $q->where('id', 81), $lock);
        if (count($page) !== 1 || $page[0]['seeder'] !== Supplement::OWNER || $page[0]['page_category_id'] !== 16) { throw new RuntimeException('Exact owner Page differs.'); }
        $blocks = $this->rows('text_blocks', static fn ($q) => $q->where('page_id', 81), $lock); $updates = [];
        if ($state === 'before') { $this->candidate($updates, 'text_blocks', $practice, Supplement::OWNER, ['body' => $transition['before']], ['body' => $transition['after']]); }
        $sources = $transition['sources'];
        foreach ($this->evidencePins() as $path => $sha) { $sources[$path] = $sha; }
        foreach ($sources as $path => $sha) {
            $file = str_starts_with($path, 'private:') ? $this->privateDirectory.'/'.substr($path, 8) : $root.'/'.$path;
            if (!hash_equals($sha, self::fileHash($file))) { throw new RuntimeException('Reviewed bank source changed.'); }
        }
        ksort($sources);
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => self::NAMES, 'connection' => $connection, 'state' => $state,
            'sources' => $sources, 'pages' => [Supplement::OWNER => ['page' => $page[0], 'blocks' => $blocks]],
            'updates' => $updates, 'inserts' => [], 'protected' => $protected, 'question_rows' => $scope['expected'],
            'question_family_counts' => array_map('count', $scope['expected']), 'question_source_sha256' => Supplement::SOURCE_SHA];
        $plan['sha256'] = self::digest($plan); return $plan;
    }

    protected function afterUpdate(int $index): void
    {
        if ($index !== 0 || $this->db->transactionLevel() < 1) { throw new RuntimeException('Bank insert requires exact inherited body-update transaction.'); }
        $scope = $this->loadEvidence(); Rows::assertAbsent($this->questionRows($scope['expected'], true));
        foreach (Rows::ROW_COLUMNS as $table => $_) { foreach ($scope['expected'][$table] as $n => $row) {
            if (!$this->db->table($table)->insert($row)) { throw new RuntimeException('Scoped bank insert failed.'); }
            $this->afterQuestionRowInsert($table, $n);
        } }
        Rows::assertApplied($scope['expected'], $this->questionRows($scope['expected'], true));
    }

    protected function afterQuestionRowInsert(string $table, int $index): void {}
}
