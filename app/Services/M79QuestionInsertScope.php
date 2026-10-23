<?php

namespace App\Services;

use App\Support\M51QuestionSupplement as Rows;
use App\Support\M79QuestionSupplement as Supplement;
use RuntimeException;

/** Eight inserts across three finite banks; the unchanged M26 writer owns backup, locks and transaction. */
trait M79QuestionInsertScope
{
    public const QUESTION_PLAN_FILE = 'question-plan-v1.json';
    public const QUESTION_PLAN_SHA = 'a070855f69cab527373c1bcb02b88f1da6cbe1074b6bddb5eb67e9dbeacfaaaf';
    public const QUESTION_INVENTORY_FILE = 'question-inventory-v2.json';
    public const QUESTION_INVENTORY_SHA = '99db8539361eeea4b331c23a5b386e8af875c1e9812afd384a3ed50500a639d4';
    private array $m79ApprovedQuestionRows = [];
    private int $m79LastInsertIndex = -1;

    protected function questionSource(): array { return Supplement::load(dirname($this->databasePath)); }
    protected function questionPatch(): array { $package = static::packageClass(); return $package::load(dirname($this->databasePath))[1]; }

    public static function questionEvidencePins(): array
    {
        return ['private:'.self::QUESTION_PLAN_FILE => self::QUESTION_PLAN_SHA,
            'private:'.self::QUESTION_INVENTORY_FILE => self::QUESTION_INVENTORY_SHA];
    }

    private function m79PinnedEvidence(string $name, string $sha): array
    {
        if (!in_array($name, [self::BEFORE_FILE, self::QUESTION_PLAN_FILE, self::QUESTION_INVENTORY_FILE], true)
            || preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1
            || !hash_equals($sha, self::fileHash($this->privateDirectory.'/'.$name))) {
            throw new RuntimeException('M79 question evidence is pending, changed, linked or outside the finite allowlist.');
        }
        return json_decode(file_get_contents($this->privateDirectory.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
    }

    protected function questionEvidence(): array
    {
        $plan = $this->m79PinnedEvidence(self::QUESTION_PLAN_FILE, self::QUESTION_PLAN_SHA);
        $inventory = $this->m79PinnedEvidence(self::QUESTION_INVENTORY_FILE, self::QUESTION_INVENTORY_SHA);
        $before = $this->m79PinnedEvidence(self::BEFORE_FILE, self::BEFORE_SHA);
        if (($plan['schema_version'] ?? null) !== 1 || ($plan['kind'] ?? null) !== 'M79_question_insert_plan'
            || ($inventory['schema_version'] ?? null) !== 1 || ($inventory['kind'] ?? null) !== 'M79_question_supplement_inventory'
            || ($plan['base_sha'] ?? null) !== Supplement::BASE_SHA || ($inventory['base_sha'] ?? null) !== Supplement::BASE_SHA
            || ($before['base_sha'] ?? null) !== Supplement::BASE_SHA
            || ($plan['before_sha256'] ?? null) !== self::BEFORE_SHA || ($inventory['before_sha256'] ?? null) !== self::BEFORE_SHA
            || ($plan['source_sha256'] ?? null) !== Supplement::SOURCE_SHA || ($inventory['source_sha256'] ?? null) !== Supplement::SOURCE_SHA
            || ($plan['inventory'] ?? null) !== ['file' => self::QUESTION_INVENTORY_FILE, 'sha256' => self::QUESTION_INVENTORY_SHA]
            || empty($inventory['identity']) || ($inventory['identity'] ?? null) !== ($before['identity'] ?? null)
            || empty($inventory['sql_before']) || ($inventory['sql_before'] ?? null) !== ($before['sql_before'] ?? null)
            || ($before['sql_before'] ?? null) !== ($before['sql_after_http'] ?? null)
            || ($inventory['db_writes'] ?? null) !== 0 || ($inventory['http_requests'] ?? null) !== 0
            || ($inventory['id_step_offset'] ?? null) !== [1, 1]) {
            throw new RuntimeException('M79 question plan/inventory/source/independent BEFORE lineage differs.');
        }
        $plan['_inventory'] = $inventory; $plan['_before'] = $before;
        return $plan;
    }

    protected function extendPlan(array &$plan, array $patch, ?array $baseline, bool $lock): void
    {
        $scope = $this->m79QuestionState($plan['state'], $patch, $lock);
        if ($baseline !== null && ($scope['evidence']['_inventory']['identity'] ?? null) !== ($baseline['identity'] ?? null)) {
            throw new RuntimeException('M79 question evidence/runtime differs from fresh BEFORE.');
        }
        foreach (self::questionEvidencePins() as $path => $sha) { $plan['sources'][$path] = $sha; }
        $plan['sources'][Supplement::SOURCE] = Supplement::SOURCE_SHA;
        $plan['question_supplement'] = $scope;
        $this->m79ApprovedQuestionRows = [];
        foreach ($scope['actual'] as $table => $rows) { $this->m79ApprovedQuestionRows[$table] = array_column($rows, null, 'id'); }
        $this->m79LastInsertIndex = $plan['state'] === 'before' ? count($plan['inserts']) - 1 : -1;
        if ($plan['state'] === 'before') {
            $package = static::packageClass(); $owners = array_map(static fn ($i) => $package::OWNERS[$i], [3, 4, 5]);
            if ($plan['updates'] === [] || count($plan['inserts']) !== 3
                || array_column($plan['inserts'], 'seeder') !== $owners
                || array_column($plan['inserts'], 'table') !== ['text_blocks', 'text_blocks', 'text_blocks']) {
                throw new RuntimeException('M79 questions require continuing updates and exactly the three reviewed new practices.');
            }
            foreach ($plan['inserts'] as $insert) {
                if (($insert['fields']['type'] ?? null) !== 'practice-set' || ($insert['fields']['locale'] ?? null) !== 'uk'
                    || ($insert['fields']['column'] ?? null) !== 'left' || ($insert['fields']['sort_order'] ?? null) !== 7) {
                    throw new RuntimeException('M79 question hook cannot broaden the exact three-practice insertion scope.');
                }
            }
        }
    }

    protected function omitProtectedRow(string $table, array $row): bool
    {
        return isset($row['id']) && ($this->m79ApprovedQuestionRows[$table][$row['id']] ?? null) === $row;
    }

    /** Old page relation snapshots exclude only complete proven inserts, not reused options or partial families. */
    protected function relations(array $page, array $blocks, bool $lock): array
    {
        $relations = parent::relations($page, $blocks, $lock);
        $i = array_search($page['seeder'], Supplement::OWNERS, true);
        if ($i === false) { return $relations; }
        if ($page['id'] !== Supplement::PAGE_IDS[$i] || $page['page_category_id'] !== 22) {
            throw new RuntimeException('M79 supplemental relation Page/category differs.');
        }
        $evidence = $this->questionEvidence(); $expected = $evidence['materialized'] ?? [];
        $actual = $this->m79QuestionRows($expected, $lock);
        if (array_filter($actual, static fn ($rows) => $rows !== []) === []) { return $relations; }
        Rows::assertApplied($expected, $actual);
        $questionIds = array_column(array_filter($expected['questions'], static fn ($q) => $q['seeder'] === Supplement::BANKS[$i]), 'id');
        $questionUuids = array_column(array_filter($expected['questions'], static fn ($q) => $q['seeder'] === Supplement::BANKS[$i]), 'uuid');
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            if ($table === 'question_options') { continue; }
            $owned = array_values(array_filter($expected[$table], static fn ($row) => $table === 'questions'
                ? in_array($row['id'], $questionIds, true)
                : ($table === 'question_theory_text_blocks' ? in_array($row['question_uuid'], $questionUuids, true)
                    : in_array($row['question_id'], $questionIds, true))));
            $map = array_column($owned, null, 'id'); $found = [];
            $relations[$table] = array_values(array_filter($relations[$table] ?? [], static function ($row) use ($map, &$found) {
                if (!isset($map[$row['id']])) { return true; }
                if ($map[$row['id']] !== $row) { throw new RuntimeException('M79 relation insert differs from its complete reviewed row.'); }
                $found[] = $row['id']; return false;
            }));
            $expectedIds = array_keys($map); sort($expectedIds); sort($found);
            if ($found !== $expectedIds) { throw new RuntimeException('M79 inserted relation family is missing, duplicated or foreign.'); }
        }
        $oldIds = array_unique(array_merge(array_column($relations['question_answers'], 'option_id'),
            array_column($relations['question_option_question'], 'option_id'), array_column($relations['verb_hints'], 'option_id')));
        $relations['question_options'] = array_values(array_filter($relations['question_options'], static fn ($row) => in_array($row['id'], $oldIds, true)));
        return $relations;
    }

    /** Return false for unrelated owners so the caller retains its complete ordinary pool contract. */
    protected function assertSupplementPools(array $questions, array $contract, ?array $baseline): bool
    {
        $i = array_search($contract['identity'] ?? null, Supplement::OWNERS, true);
        if ($i === false) { return false; }
        $banks = $contract['bank_seeders'] ?? [];
        if (($contract['level'] ?? null) !== 'B1' || !is_array($banks) || !array_is_list($banks)
            || count(array_unique($banks)) !== count($banks) || !in_array(Supplement::OLD_BANKS[$i], $banks, true)
            || array_values(array_intersect($banks, Supplement::BANKS)) !== [Supplement::BANKS[$i]]) {
            throw new RuntimeException('M79 exact original/supplemental bank and actual B1 filter differ.');
        }
        $ids = [];
        foreach ($banks as $bank) {
            $eligible = array_values(array_filter($questions, static fn ($q) => $q['seeder'] === $bank && (string) $q['type'] === '4' && $q['level'] === 'B1'));
            usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            if ($bank === Supplement::BANKS[$i]) {
                if ($eligible !== []) { throw new RuntimeException('M79 full proven inserts must be excluded from original pool snapshots.'); }
                continue;
            }
            if ($baseline !== null) {
                $expected = array_values(array_filter($baseline['relations']['questions'] ?? [], static fn ($q) => $q['seeder'] === $bank && (string) $q['type'] === '4' && $q['level'] === 'B1'));
                usort($expected, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
                if ($eligible !== $expected) { throw new RuntimeException('M79 full original thematic B1 rows changed.'); }
            }
            array_push($ids, ...array_column($eligible, 'id'));
        }
        if (count($ids) < 5 || count(array_unique($ids)) !== count($ids)) { throw new RuntimeException('M79 original thematic pool is short or repeats IDs.'); }
        return true;
    }

    protected function m79QuestionState(string $state, array $patch, bool $lock, bool $contentAlreadyApplied = false): array
    {
        if (!in_array($state, ['before', 'after'], true)) { throw new RuntimeException('M79 unknown insert-only question state.'); }
        $evidence = $this->questionEvidence(); $inventory = $evidence['_inventory']; $before = $evidence['_before'];
        $bindings = Supplement::bindings(); $source = $this->questionSource();
        if (($evidence['bindings'] ?? null) !== $bindings || ($inventory['bindings'] ?? null) !== $bindings
            || ($inventory['question_collisions'] ?? null) !== array_fill_keys(array_keys(Rows::ROW_COLUMNS), [])) {
            throw new RuntimeException('M79 exact hero/taxonomy bindings or original collision inventory differ.');
        }
        $projection = Supplement::project($source, $bindings, $inventory['option_matches'] ?? []);
        $ids = $evidence['ids'] ?? []; $timestamp = $evidence['row_timestamp'] ?? null;
        if (array_keys($ids) !== array_keys(Rows::ROW_COLUMNS) || $ids !== ($inventory['ids'] ?? null)
            || !is_string($timestamp) || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $timestamp) !== 1
            || $timestamp !== ($inventory['row_timestamp'] ?? null) || $projection !== ($evidence['projection'] ?? null)
            || $projection !== ($inventory['projection'] ?? null)
            || array_keys($inventory['allocation'] ?? []) !== array_keys(Rows::ROW_COLUMNS)
            || array_keys($inventory['schema'] ?? []) !== array_keys(Rows::ROW_COLUMNS)) {
            throw new RuntimeException('M79 projection/IDs/timestamp/schema evidence differs.');
        }
        $expected = Rows::materialize($projection, $ids, $timestamp);
        if ($expected !== ($evidence['materialized'] ?? null) || $expected !== ($inventory['materialized'] ?? null)
            || count($expected['questions']) !== 8 || count($expected['question_theory_text_blocks']) !== 8
            || array_count_values(array_column($expected['questions'], 'seeder')) !== array_combine(Supplement::BANKS, [2, 3, 3])) {
            throw new RuntimeException('M79 exact eight-family materialization differs.');
        }
        foreach ($bindings as $i => $binding) {
            $owner = $binding['owner']; $slot = Supplement::TARGET_INDICES[$i];
            $captured = array_values(array_filter($before['pages'][$owner]['blocks'] ?? [], static fn ($b) => $b['id'] === $binding['theory_block_id']));
            if (count($captured) !== 1 || ($inventory['heroes_before'][$owner] ?? null) !== $captured[0]) {
                throw new RuntimeException('M79 independent existing hero reference differs.');
            }
            $hero = $captured[0];
            foreach (['uuid' => $binding['theory_block_uuid'], 'page_id' => $binding['page_id'], 'page_category_id' => 22,
                'locale' => 'uk', 'seeder' => $owner, 'type' => 'hero', 'sort_order' => 1, 'level' => 'B1'] as $field => $value) {
                if (!array_key_exists($field, $hero) || $hero[$field] !== $value) { throw new RuntimeException('M79 exact hero reference metadata differs: '.$field); }
            }
            if (($patch['targets'][$slot]['identity'] ?? null) !== $owner || ($patch['targets'][$slot]['after']['page']['blocks'][0]['type'] ?? null) !== 'hero') {
                throw new RuntimeException('M79 exact AFTER hero/owner slot differs.');
            }
            if ($state === 'after' || $contentAlreadyApplied) { $hero['body'] = $patch['targets'][$slot]['after']['page']['blocks'][0]['body']; }
            if ($this->rows('text_blocks', static fn ($q) => $q->where('id', $binding['theory_block_id']), $lock) !== [$hero]) {
                throw new RuntimeException('M79 continuing hero changed beyond its exact body transition.');
            }
            $reference = $inventory['taxonomy_questions'][$owner] ?? null;
            if (!is_array($reference) || !in_array($reference, $before['pages'][$owner]['relations']['questions'] ?? [], true)
                || $reference['seeder'] !== Supplement::OLD_BANKS[$i] || (string) $reference['type'] !== '4' || $reference['level'] !== 'B1'
                || $reference['category_id'] !== $binding['category_id'] || $reference['source_id'] !== $binding['source_id']
                || $this->rows('questions', static fn ($q) => $q->where('id', $reference['id']), $lock) !== [$reference]) {
                throw new RuntimeException('M79 original thematic taxonomy reference question changed.');
            }
        }
        $taxonomy = $inventory['taxonomy'] ?? [];
        if (array_keys($taxonomy) !== ['categories', 'sources'] || ($evidence['taxonomy'] ?? null) !== $taxonomy
            || array_column($taxonomy['categories'] ?? [], 'id') !== [45] || array_column($taxonomy['sources'] ?? [], 'id') !== Supplement::SOURCE_IDS) {
            throw new RuntimeException('M79 existing taxonomy evidence differs.');
        }
        foreach ($taxonomy as $table => $rows) {
            if ($this->rows($table, static fn ($q) => $q->whereIn('id', array_column($rows, 'id')), $lock) !== $rows) { throw new RuntimeException('M79 original taxonomy rows/timestamps changed.'); }
        }
        if ($this->db->getDriverName() === 'mysql') {
            $variables = (array) $this->db->selectOne('SELECT @@auto_increment_increment AS step_value, @@auto_increment_offset AS offset_value');
            if ([(int) $variables['step_value'], (int) $variables['offset_value']] !== [1, 1]) { throw new RuntimeException('M79 physical allocation step/offset changed.'); }
        }
        foreach (Rows::ROW_COLUMNS as $table => $columns) {
            $actualColumns = $this->db->getSchemaBuilder()->getColumnListing($table); $sorted = $columns; sort($actualColumns); sort($sorted);
            if ($actualColumns !== $sorted || ($this->db->getDriverName() === 'mysql' && $this->db->getSchemaBuilder()->getColumns($table) !== $inventory['schema'][$table])) {
                throw new RuntimeException('M79 exact inspected insert-family schema differs: '.$table);
            }
            $a = $inventory['allocation'][$table];
            if (array_keys($a) !== ['max_id', 'next_id'] || !is_int($a['max_id']) || $a['max_id'] < 0 || !is_int($a['next_id']) || $a['next_id'] < 1) { throw new RuntimeException('M79 allocation evidence is incomplete.'); }
            $start = max($a['max_id'] + 1, $a['next_id']); $expectedIds = [];
            foreach (array_column($projection[$table], 'key') as $offset => $key) { $expectedIds[$key] = $start + $offset; }
            if ($ids[$table] !== $expectedIds) { throw new RuntimeException('M79 sequential reviewed ID allocation differs.'); }
            $count = count($expected[$table]); $max = $state === 'before' || $count === 0 ? $a['max_id'] : max(array_column($expected[$table], 'id'));
            if ((int) ($this->db->table($table)->max('id') ?? 0) !== $max) { throw new RuntimeException('M79 inspected ID allocation became stale.'); }
            if ($this->db->getDriverName() === 'mysql') {
                $auto = (array) $this->db->selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$this->db->getDatabaseName(), $table]);
                $next = $state === 'before' || $count === 0 ? $a['next_id'] : $max + 1;
                if ((int) ($auto['next_id'] ?? -1) !== $next) { throw new RuntimeException('M79 physical automatic ID sequence differs.'); }
            }
        }
        $actual = $this->m79QuestionRows($expected, $lock);
        if ($state === 'before') { Rows::assertAbsent($actual); } else { Rows::assertApplied($expected, $actual); }
        $newOptions = [];
        foreach ($projection['question_options'] as $n => $record) { $newOptions[$record['key']] = $expected['question_options'][$n]; }
        $reused = array_column($projection['reused_options'], null, 'key');
        foreach ($inventory['option_matches'] as $token => $matches) {
            $key = 'option-'.hash('sha256', mb_strtolower($token));
            $lookup = $matches !== [] ? [$reused[$key]['row']] : ($state === 'after' ? [$newOptions[$key]] : []);
            if ($this->rows('question_options', static fn ($q) => $q->where('option', $token), $lock) !== $lookup) { throw new RuntimeException('M79 full SQL-collation option lookup changed or collided.'); }
        }
        $questionIds = array_values($ids['questions']);
        foreach (['question_tag', 'question_marker_tag', 'verb_hints', 'question_hints', 'question_variants'] as $table) {
            if ($this->rows($table, static fn ($q) => $q->whereIn('question_id', $questionIds), $lock) !== []) { throw new RuntimeException('M79 new questions gained an unapproved relation.'); }
        }
        if ($this->rows('saved_grammar_test_questions', static fn ($q) => $q->whereIn('question_uuid', Supplement::UUIDS), $lock) !== []) { throw new RuntimeException('M79 supplement cannot change standalone test membership.'); }
        return compact('evidence', 'projection', 'expected', 'actual');
    }

    private function m79QuestionRows(array $expected, bool $lock): array
    {
        $out = []; $questionIds = array_column($expected['questions'] ?? [], 'id');
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            $ids = array_column($expected[$table] ?? [], 'id');
            $out[$table] = $this->rows($table, static function ($q) use ($table, $ids, $questionIds) {
                $q->whereIn('id', $ids);
                if ($table === 'questions') { $q->orWhereIn('uuid', Supplement::UUIDS)->orWhereIn('seeder', Supplement::BANKS); }
                elseif ($table === 'question_theory_text_blocks') { $q->orWhereIn('question_uuid', Supplement::UUIDS); }
                elseif (in_array($table, ['question_answers', 'question_option_question'], true)) { $q->orWhereIn('question_id', $questionIds); }
            }, $lock);
        }
        return $out;
    }

    protected function afterInsert(int $index): void
    {
        if ($index !== $this->m79LastInsertIndex) { return; }
        if ($this->m79LastInsertIndex !== 2 || $this->db->transactionLevel() < 1) { throw new RuntimeException('M79 question inserts require the inherited last-practice transaction.'); }
        $package = static::packageClass(); $patch = $this->questionPatch(); $evidence = $this->questionEvidence();
        foreach ($package::OWNERS as $i => $owner) {
            $slot = $package::PRACTICE_SOURCE_INDICES[$i]; $config = $patch['targets'][$i]['after']['page']['blocks'][$slot];
            $uuid = $this->resolveUuid($owner, $config, $slot + 1);
            $actual = $this->rows('text_blocks', static fn ($q) => $q->where('uuid', $uuid), true);
            $fields = ['uuid' => $uuid, 'page_id' => $package::PAGE_IDS[$i], 'page_category_id' => 22, 'locale' => 'uk', 'seeder' => $owner,
                'type' => 'practice-set', 'column' => 'left', 'heading' => $config['heading'] ?? null, 'css_class' => $config['css_class'] ?? null,
                'sort_order' => $slot + 1, 'level' => $config['level'] ?? null, 'body' => $config['body']];
            if (count($actual) !== 1) { throw new RuntimeException('M79 all six exact practices are required before question insertion.'); }
            foreach ($fields as $field => $value) { if ($actual[0][$field] !== $value) { throw new RuntimeException('M79 exact AFTER practice binding differs.'); } }
            if ($i < 3) {
                $old = array_values(array_filter($evidence['_before']['pages'][$owner]['blocks'] ?? [], static fn ($b) => $b['uuid'] === $uuid));
                if (count($old) !== 1) { throw new RuntimeException('M79 continuing practice captured identity missing.'); }
                $old[0]['body'] = $config['body'];
                if ($actual !== $old) { throw new RuntimeException('M79 continuing practice identity/timestamps changed.'); }
            } elseif (empty($actual[0]['created_at']) || $actual[0]['created_at'] !== $actual[0]['updated_at']) { throw new RuntimeException('M79 new practice timestamps differ.'); }
        }
        $scope = $this->m79QuestionState('before', $patch, true, true);
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            foreach ($scope['expected'][$table] as $n => $row) {
                if (!$this->db->table($table)->insert($row)) { throw new RuntimeException('M79 finite question insert failed; inherited transaction rolls back.'); }
                $this->afterQuestionRowInsert($table, $n);
            }
        }
        $this->m79QuestionState('after', $patch, true);
    }

    protected function afterQuestionRowInsert(string $table, int $index): void {}
}
