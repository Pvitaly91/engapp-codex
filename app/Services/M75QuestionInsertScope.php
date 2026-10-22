<?php

namespace App\Services;

use App\Support\M51QuestionSupplement as Rows;
use App\Support\M75PronounForms as Package;
use App\Support\M75QuestionSupplement as Supplement;
use RuntimeException;

/** Two insert-only question families. M26 retains exclusive backup, stale-plan checks and transaction ownership. */
trait M75QuestionInsertScope
{
    public const QUESTION_PLAN_FILE = 'question-plan-v1.json';
    public const QUESTION_PLAN_SHA = '5f6062a2c12e73e7077d7240b01cda84ef873a9b0c6582e7467bd761b2705bf0';
    public const QUESTION_INVENTORY_FILE = 'question-inventory-v1.json';
    public const QUESTION_INVENTORY_SHA = '5c1d4ee1eb53dfbf07c69b669b2bb5f824ee8e31f6b0d8a7a6ef6c4327845900';
    private array $m75ApprovedQuestionRows = [];
    private int $m75LastUpdateIndex = -1;

    protected function questionSource(): array { return Supplement::load(dirname($this->databasePath)); }
    protected function questionPatch(): array { return Package::load(dirname($this->databasePath))[1]; }

    private function m75PinnedEvidence(string $name, string $sha): array
    {
        if (!in_array($name, [self::BEFORE_FILE, self::QUESTION_PLAN_FILE, self::QUESTION_INVENTORY_FILE], true)
            || preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1
            || !hash_equals($sha, self::fileHash($this->privateDirectory.'/'.$name))) {
            throw new RuntimeException('M75 question evidence is pending, changed, linked or outside the finite allowlist.');
        }
        return json_decode(file_get_contents($this->privateDirectory.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
    }

    /** Public pins contain only opaque hashes; private runtime/source rows never enter versioned policy. */
    public static function questionEvidencePins(): array
    {
        return ['private:'.self::QUESTION_PLAN_FILE => self::QUESTION_PLAN_SHA,
            'private:'.self::QUESTION_INVENTORY_FILE => self::QUESTION_INVENTORY_SHA];
    }

    protected function questionEvidence(): array
    {
        $evidence = $this->m75PinnedEvidence(self::QUESTION_PLAN_FILE, self::QUESTION_PLAN_SHA);
        $inventory = $this->m75PinnedEvidence(self::QUESTION_INVENTORY_FILE, self::QUESTION_INVENTORY_SHA);
        $before = $this->m75PinnedEvidence(self::BEFORE_FILE, self::BEFORE_SHA);
        if (($evidence['schema_version'] ?? null) !== 1 || ($evidence['kind'] ?? null) !== 'M75_question_insert_plan'
            || ($inventory['schema_version'] ?? null) !== 1 || ($inventory['kind'] ?? null) !== 'M75_question_supplement_inventory'
            || ($evidence['base_sha'] ?? null) !== Package::BASE_SHA || ($inventory['base_sha'] ?? null) !== Package::BASE_SHA
            || ($before['base_sha'] ?? null) !== Package::BASE_SHA
            || ($evidence['before_sha256'] ?? null) !== self::BEFORE_SHA || ($inventory['before_sha256'] ?? null) !== self::BEFORE_SHA
            || ($evidence['source_sha256'] ?? null) !== Supplement::SOURCE_SHA
            || ($inventory['source_sha256'] ?? null) !== Supplement::SOURCE_SHA
            || ($evidence['inventory'] ?? null) !== ['file' => self::QUESTION_INVENTORY_FILE, 'sha256' => self::QUESTION_INVENTORY_SHA]
            || ($inventory['identity'] ?? null) !== ($before['identity'] ?? null) || empty($inventory['identity'])
            || ($inventory['sql_before'] ?? null) !== ($before['sql_before'] ?? null) || empty($inventory['sql_before'])
            || ($before['sql_before'] ?? null) !== ($before['sql_after_http'] ?? null)
            || ($inventory['db_writes'] ?? null) !== 0 || ($inventory['http_requests'] ?? null) !== 0
            || ($inventory['id_step_offset'] ?? null) !== [1, 1]) {
            throw new RuntimeException('M75 question plan/inventory/source/independent BEFORE lineage differs.');
        }
        $evidence['_inventory'] = $inventory; $evidence['_before'] = $before;
        return $evidence;
    }

    protected function extendPlan(array &$plan, array $patch, ?array $baseline, bool $lock): void
    {
        $scope = $this->m75QuestionState($plan['state'], $patch, $lock);
        if ($baseline !== null && ($scope['evidence']['_inventory']['identity'] ?? null) !== ($baseline['identity'] ?? null)) {
            throw new RuntimeException('M75 question evidence/runtime differs from fresh BEFORE.');
        }
        foreach (self::questionEvidencePins() as $path => $sha) { $plan['sources'][$path] = $sha; }
        $plan['sources'][Supplement::SOURCE] = Supplement::SOURCE_SHA;
        $plan['question_supplement'] = $scope;
        $this->m75ApprovedQuestionRows = [];
        foreach ($scope['actual'] as $table => $rows) { $this->m75ApprovedQuestionRows[$table] = array_column($rows, null, 'id'); }
        $this->m75LastUpdateIndex = $plan['state'] === 'before' ? count($plan['updates']) - 1 : -1;
        if ($plan['state'] === 'before' && ($this->m75LastUpdateIndex < 0 || $plan['inserts'] !== [])) {
            throw new RuntimeException('M75 question insertion requires continuing content updates and no new practice rows.');
        }
    }

    protected function omitProtectedRow(string $table, array $row): bool
    {
        // A matching numeric ID alone never exempts old rows or reused shared options.
        return isset($row['id']) && ($this->m75ApprovedQuestionRows[$table][$row['id']] ?? null) === $row;
    }

    /** Continuing practice links expose the new families, so filter only full proven inserts from a copied relation inventory. */
    protected function relations(array $page, array $blocks, bool $lock): array
    {
        $relations = parent::relations($page, $blocks, $lock);
        if ($page['seeder'] !== Supplement::OWNER) { return $relations; }
        if ($page['id'] !== Supplement::PAGE_ID || $page['page_category_id'] !== Supplement::PAGE_CATEGORY_ID) {
            throw new RuntimeException('M75 supplemental relation Page/category differs.');
        }
        $patch = $this->questionPatch();
        $matches = array_values(array_filter($blocks, static fn ($b) => $b['uuid'] === Supplement::PRACTICE_UUID && $b['locale'] === 'uk'));
        if (count($matches) !== 1) { throw new RuntimeException('M75 exactly one continuing Indefinite practice is required.'); }
        $after = $patch['targets'][2]['after']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[2]]['body'] ?? null;
        $sourceBefore = Package::load(dirname($this->databasePath))[0]['targets'][2]['before']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[2]]['body'] ?? null;
        $state = $matches[0]['body'] === $sourceBefore ? 'before' : ($matches[0]['body'] === $after ? 'after' : null);
        if ($state === null) { throw new RuntimeException('M75 practice is neither exact BEFORE nor exact AFTER.'); }
        $scope = $this->m75QuestionState($state, $patch, $lock);
        if ($state === 'before') { return $relations; }
        foreach ($scope['expected'] as $table => $rows) {
            if ($table === 'question_options') { continue; }
            $map = array_column($rows, null, 'id'); $found = [];
            $relations[$table] = array_values(array_filter($relations[$table] ?? [], static function ($row) use ($map, &$found) {
                if (!isset($map[$row['id']])) { return true; }
                if ($map[$row['id']] !== $row) { throw new RuntimeException('M75 relation row differs from its complete reviewed insert.'); }
                $found[] = $row['id']; return false;
            }));
            $expectedIds = array_keys($map); sort($expectedIds); sort($found);
            if ($found !== $expectedIds) { throw new RuntimeException('M75 inserted relation family is missing, duplicated or foreign.'); }
        }
        // An existing shared option used only by a new question is not part of the old page relation snapshot.
        // Re-derive that snapshot from all remaining original answers, option pivots and verb hints.
        $oldOptionIds = array_unique(array_merge(array_column($relations['question_answers'], 'option_id'),
            array_column($relations['question_option_question'], 'option_id'), array_column($relations['verb_hints'], 'option_id')));
        $relations['question_options'] = array_values(array_filter($relations['question_options'],
            static fn ($row) => in_array($row['id'], $oldOptionIds, true)));
        return $relations;
    }

    protected function m75QuestionState(string $state, array $patch, bool $lock, bool $contentAlreadyApplied = false): array
    {
        if (!in_array($state, ['before', 'after'], true)) { throw new RuntimeException('M75 unknown question family state.'); }
        $evidence = $this->questionEvidence(); $inventory = $evidence['_inventory']; $before = $evidence['_before'];
        $source = $this->questionSource();
        if (($evidence['binding'] ?? null) !== Supplement::binding() || ($inventory['binding'] ?? null) !== Supplement::binding()
            || ($inventory['question_collisions'] ?? null) !== array_fill_keys(array_keys(Rows::ROW_COLUMNS), [])) {
            throw new RuntimeException('M75 exact binding or pre-insert collision inventory differs.');
        }
        $projection = Supplement::project($source, Supplement::binding(), $inventory['option_matches'] ?? []);
        $ids = $evidence['ids'] ?? []; $timestamp = $evidence['row_timestamp'] ?? null;
        if (array_keys($ids) !== array_keys(Rows::ROW_COLUMNS) || $ids !== ($inventory['ids'] ?? null)
            || !is_string($timestamp) || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $timestamp) !== 1
            || $timestamp !== ($inventory['row_timestamp'] ?? null) || $projection !== ($evidence['projection'] ?? null)
            || $projection !== ($inventory['projection'] ?? null)
            || array_keys($inventory['allocation'] ?? []) !== array_keys(Rows::ROW_COLUMNS)
            || array_keys($inventory['schema'] ?? []) !== array_keys(Rows::ROW_COLUMNS)) {
            throw new RuntimeException('M75 projection/ID/timestamp/schema evidence differs.');
        }
        $expected = Rows::materialize($projection, $ids, $timestamp);
        if ($expected !== ($evidence['materialized'] ?? null) || $expected !== ($inventory['materialized'] ?? null)
            || count($expected['questions']) !== 2 || count($expected['question_answers']) !== 10
            || count($expected['question_theory_text_blocks']) !== 2
            || count($expected['question_option_question']) !== array_sum(array_map(static fn ($q) => count($q['options']), $source['questions']))) {
            throw new RuntimeException('M75 exact two-family materialization differs.');
        }
        $captured = array_values(array_filter($before['pages'][Supplement::OWNER]['blocks'] ?? [],
            static fn ($b) => $b['id'] === Supplement::PRACTICE_ID));
        if (count($captured) !== 1 || ($inventory['practice_before'] ?? null) !== $captured[0]) {
            throw new RuntimeException('M75 independently captured continuing practice differs.');
        }
        $practiceExpected = $captured[0];
        foreach (['id' => Supplement::PRACTICE_ID, 'uuid' => Supplement::PRACTICE_UUID, 'page_id' => Supplement::PAGE_ID,
            'page_category_id' => Supplement::PAGE_CATEGORY_ID, 'locale' => 'uk', 'seeder' => Supplement::OWNER,
            'type' => 'practice-set', 'column' => 'left', 'level' => null, 'sort_order' => 11] as $field => $value) {
            if (!array_key_exists($field, $practiceExpected) || $practiceExpected[$field] !== $value) {
                throw new RuntimeException('M75 existing practice metadata differs: '.$field);
            }
        }
        $target = $patch['targets'][2] ?? [];
        $config = $target['after']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[2]] ?? [];
        if (($target['identity'] ?? null) !== Supplement::OWNER || ($config['type'] ?? null) !== 'practice-set'
            || ($config['column'] ?? null) !== 'left' || ($config['level'] ?? null) !== null
            || $this->resolveUuid(Supplement::OWNER, $config, 11) !== Supplement::PRACTICE_UUID) {
            throw new RuntimeException('M75 exact AFTER practice binding differs.');
        }
        if ($state === 'after' || $contentAlreadyApplied) { $practiceExpected['body'] = $config['body']; }
        if ($this->rows('text_blocks', static fn ($q) => $q->where('id', Supplement::PRACTICE_ID), $lock) !== [$practiceExpected]) {
            throw new RuntimeException('M75 continuing practice changed beyond its exact body transition.');
        }
        $taxonomy = $inventory['taxonomy'] ?? [];
        if (array_keys($taxonomy) !== ['categories', 'sources']
            || array_column($taxonomy['categories'] ?? [], 'id') !== [Supplement::CATEGORY_ID]
            || array_column($taxonomy['sources'] ?? [], 'id') !== [Supplement::SOURCE_ID]
            || ($evidence['taxonomy'] ?? null) !== $taxonomy) {
            throw new RuntimeException('M75 exact pre-existing taxonomy evidence differs.');
        }
        foreach ($taxonomy as $table => $rows) {
            if ($this->rows($table, static fn ($q) => $q->whereIn('id', array_column($rows, 'id')), $lock) !== $rows) {
                throw new RuntimeException('M75 original taxonomy rows or timestamps changed.');
            }
        }
        $reference = $inventory['taxonomy_question'] ?? null;
        $oldQuestions = $before['pages'][Supplement::OWNER]['relations']['questions'] ?? [];
        if (!is_array($reference) || !in_array($reference, $oldQuestions, true)
            || $reference['seeder'] !== Supplement::OLD_BANK || (string) $reference['type'] !== '4'
            || $reference['category_id'] !== Supplement::CATEGORY_ID || $reference['source_id'] !== Supplement::SOURCE_ID
            || $this->rows('questions', static fn ($q) => $q->where('id', $reference['id']), $lock) !== [$reference]) {
            throw new RuntimeException('M75 original thematic taxonomy reference question changed.');
        }
        foreach (Rows::ROW_COLUMNS as $table => $columns) {
            $actualColumns = $this->db->getSchemaBuilder()->getColumnListing($table); $sortedColumns = $columns;
            sort($actualColumns); sort($sortedColumns);
            if ($actualColumns !== $sortedColumns || ($this->db->getDriverName() === 'mysql'
                && $this->db->getSchemaBuilder()->getColumns($table) !== ($inventory['schema'][$table] ?? null))) {
                throw new RuntimeException('M75 exact inspected insert-family schema differs: '.$table);
            }
            $allocation = $inventory['allocation'][$table]; $expectedIds = [];
            if (array_keys($allocation) !== ['max_id', 'next_id'] || !is_int($allocation['max_id']) || $allocation['max_id'] < 0
                || !is_int($allocation['next_id']) || $allocation['next_id'] < 1) {
                throw new RuntimeException('M75 physical allocation evidence is incomplete.');
            }
            $start = max($allocation['max_id'] + 1, $allocation['next_id']);
            foreach (array_column($projection[$table], 'key') as $offset => $key) { $expectedIds[$key] = $start + $offset; }
            if ($ids[$table] !== $expectedIds) { throw new RuntimeException('M75 sequential reviewed ID allocation differs.'); }
            $count = count($expected[$table]);
            $maxExpected = $state === 'before' || $count === 0 ? $allocation['max_id'] : max(array_column($expected[$table], 'id'));
            if ((int) ($this->db->table($table)->max('id') ?? 0) !== $maxExpected) {
                throw new RuntimeException('M75 inspected ID allocation became stale.');
            }
            if ($this->db->getDriverName() === 'mysql') {
                $auto = (array) $this->db->selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$this->db->getDatabaseName(), $table]);
                $nextExpected = $state === 'before' || $count === 0 ? $allocation['next_id'] : $maxExpected + 1;
                if ((int) ($auto['next_id'] ?? -1) !== $nextExpected) {
                    throw new RuntimeException('M75 physical automatic ID sequence differs.');
                }
            }
        }
        $actual = $this->m75QuestionRows($expected, $lock);
        if ($state === 'before') { Rows::assertAbsent($actual); } else { Rows::assertApplied($expected, $actual); }
        $newOptions = [];
        foreach ($projection['question_options'] as $i => $record) { $newOptions[$record['key']] = $expected['question_options'][$i]; }
        $reused = array_column($projection['reused_options'], null, 'key');
        foreach ($inventory['option_matches'] as $token => $matches) {
            $key = 'option-'.hash('sha256', mb_strtolower($token));
            $expectedLookup = $matches !== [] ? [$reused[$key]['row']] : ($state === 'after' ? [$newOptions[$key]] : []);
            if ($this->rows('question_options', static fn ($q) => $q->where('option', $token), $lock) !== $expectedLookup) {
                throw new RuntimeException('M75 full SQL-collation option lookup changed or now collides.');
            }
        }
        $questionIds = array_values($ids['questions']);
        foreach (['question_tag', 'question_marker_tag', 'verb_hints', 'question_hints', 'question_variants'] as $table) {
            if ($this->rows($table, static fn ($q) => $q->whereIn('question_id', $questionIds), $lock) !== []) {
                throw new RuntimeException('M75 insert exception gained an unapproved question relation.');
            }
        }
        if ($this->rows('saved_grammar_test_questions', static fn ($q) => $q->whereIn('question_uuid', Supplement::UUIDS), $lock) !== []) {
            throw new RuntimeException('M75 supplement may not change standalone test membership.');
        }
        return compact('evidence', 'projection', 'expected', 'actual');
    }

    private function m75QuestionRows(array $expected, bool $lock): array
    {
        $out = []; $questionIds = array_column($expected['questions'], 'id');
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            $ids = array_column($expected[$table], 'id');
            $out[$table] = $this->rows($table, static function ($q) use ($table, $ids, $questionIds): void {
                $q->whereIn('id', $ids);
                if ($table === 'questions') { $q->orWhereIn('uuid', Supplement::UUIDS)->orWhere('seeder', Supplement::BANK); }
                elseif ($table === 'question_theory_text_blocks') { $q->orWhereIn('question_uuid', Supplement::UUIDS); }
                elseif (in_array($table, ['question_answers', 'question_option_question'], true)) { $q->orWhereIn('question_id', $questionIds); }
            }, $lock);
        }
        return $out;
    }

    protected function afterUpdate(int $index): void
    {
        if ($index !== $this->m75LastUpdateIndex) { return; }
        if ($this->m75LastUpdateIndex < 0 || $this->db->transactionLevel() < 1) {
            throw new RuntimeException('M75 insertion requires the inherited exact last-update transaction.');
        }
        $patch = $this->questionPatch(); $evidence = $this->questionEvidence();
        foreach (Package::OWNERS as $i => $owner) {
            $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $body = $patch['targets'][$i]['after']['page']['blocks'][$slot]['body'];
            $beforeRows = $evidence['_before']['pages'][$owner]['blocks'] ?? [];
            $old = array_values(array_filter($beforeRows, static fn ($b) => $b['uuid'] === Package::PRACTICE_UUIDS[$i] && $b['locale'] === 'uk'));
            if (count($old) !== 1) { throw new RuntimeException('M75 all three independent continuing practices are required.'); }
            $expected = $old[0]; $expected['body'] = $body;
            if ($this->rows('text_blocks', static fn ($q) => $q->where('id', $expected['id']), true) !== [$expected]) {
                throw new RuntimeException('M75 all three practices must be exact AFTER before question insertion.');
            }
        }
        $scope = $this->m75QuestionState('before', $patch, true, true);
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            foreach ($scope['expected'][$table] as $n => $row) {
                if (!$this->db->table($table)->insert($row)) { throw new RuntimeException('M75 finite question insert failed; inherited transaction rolls back.'); }
                $this->afterQuestionRowInsert($table, $n);
            }
        }
        $this->m75QuestionState('after', $patch, true);
    }

    protected function afterQuestionRowInsert(string $table, int $index): void {}
}
