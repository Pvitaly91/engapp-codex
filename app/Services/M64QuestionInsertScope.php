<?php

namespace App\Services;

use App\Support\M51QuestionSupplement as Rows;
use App\Support\M64ReasonResultContrastPackage as Package;
use App\Support\M64QuestionSupplement as Supplement;
use RuntimeException;

/** Finite M64 insert policy; M26 exclusively owns preview, backup, locks and transaction. */
trait M64QuestionInsertScope
{
    public const QUESTION_PLAN_FILE = 'question-plan-v2.json';
    public const QUESTION_PLAN_SHA = '06c54c9f4fdb46deda07e4fe9005fd2636a7eb62c9716f4df5d3a01f157f66f7';
    public const QUESTION_INVENTORY_FILE = 'question-inventory-v2.json';
    public const QUESTION_INVENTORY_SHA = '98e5c7478b01f1b6d330868d6916d343240c5263be5089f3d075ba7038ae6c24';
    private array $m64ApprovedRows = [];

    protected function questionEvidence(): array
    {
        foreach ([self::QUESTION_PLAN_FILE => self::QUESTION_PLAN_SHA,
            self::QUESTION_INVENTORY_FILE => self::QUESTION_INVENTORY_SHA] as $file => $sha) {
            if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1
                || !hash_equals($sha, self::fileHash($this->privateDirectory.'/'.$file))) {
                throw new RuntimeException('M64 independent question evidence is not reviewed/frozen.');
            }
        }
        $evidence = json_decode(file_get_contents($this->privateDirectory.'/'.self::QUESTION_PLAN_FILE), true, flags: JSON_THROW_ON_ERROR);
        if (($evidence['inventory'] ?? null) !== ['file' => self::QUESTION_INVENTORY_FILE, 'sha256' => self::QUESTION_INVENTORY_SHA]) {
            throw new RuntimeException('M64 question-plan inventory lineage differs.');
        }
        $evidence['_inventory'] = json_decode(file_get_contents($this->privateDirectory.'/'.self::QUESTION_INVENTORY_FILE), true, flags: JSON_THROW_ON_ERROR);
        return $evidence;
    }

    protected function questionSource(): array { return Supplement::load(dirname($this->databasePath)); }
    protected function questionPatch(): array { return Package::load(dirname($this->databasePath))[1]; }

    protected function extendPlan(array &$plan, array $patch, ?array $baseline, bool $lock): void
    {
        $audit = json_decode(file_get_contents(dirname($this->databasePath).'/'.self::AUDIT), true, flags: JSON_THROW_ON_ERROR);
        \App\Support\M64BuilderSelectionPresentation::validateAudit($audit, $patch, Package::load(dirname($this->databasePath))[3]);
        $scope = $this->m64QuestionState($plan['state'], $patch, $lock);
        if ($baseline !== null && ($scope['evidence']['_inventory']['identity'] ?? null) !== ($baseline['identity'] ?? null)) {
            throw new RuntimeException('M64 inventory and fresh runtime BEFORE differ.');
        }
        $plan['sources']['private:'.self::QUESTION_PLAN_FILE] = self::QUESTION_PLAN_SHA;
        $plan['sources']['private:'.self::QUESTION_INVENTORY_FILE] = self::QUESTION_INVENTORY_SHA;
        $plan['question_supplement'] = $scope;
        $this->m64ApprovedRows = [];
        foreach ($scope['actual'] as $table => $rows) { $this->m64ApprovedRows[$table] = array_column($rows, null, 'id'); }
    }

    protected function omitProtectedRow(string $table, array $row): bool
    {
        return isset($row['id']) && ($this->m64ApprovedRows[$table][$row['id']] ?? null) === $row;
    }

    protected function assertExistingPools(array $questions, array $contract, ?array $baseline): void
    {
        $i = array_search($contract['identity'] ?? null, Package::OWNERS, true);
        if ($i === false || ($contract['bank_seeders'] ?? null) !== [Supplement::OLD_BANKS[$i], Supplement::BANKS[$i]]
            || ($contract['level'] ?? null) !== Package::LEVELS[$i]) { throw new RuntimeException('M64 exact new thematic bank declaration differs.'); }
        $auditPath = dirname($this->databasePath).'/'.self::AUDIT;
        if (preg_match('/^[a-f0-9]{64}$/D', self::AUDIT_SHA) !== 1
            || !hash_equals(self::AUDIT_SHA, self::fileHash($auditPath))) { throw new RuntimeException('M64 independent bank audit differs.'); }
        $audit = json_decode(file_get_contents($auditPath), true, flags: JSON_THROW_ON_ERROR);
        $target = $audit['targets'][$i] ?? [];
        $old = $target['old_bank_seeder'] ?? null;
        if (($target['identity'] ?? null) !== Package::OWNERS[$i] || !is_string($old) || $old === Supplement::BANKS[$i]) {
            throw new RuntimeException('M64 independent old-bank identity differs.');
        }
        $oldContract = $contract; $oldContract['bank_seeders'] = [$old];
        parent::assertExistingPools($questions, $oldContract, $baseline);
        if ($baseline !== null) {
            $current = array_column($questions, null, 'id');
            $captured = array_column($baseline['relations']['questions'] ?? [], null, 'id');
            ksort($current); ksort($captured);
            if ($current !== $captured) { throw new RuntimeException('M64 old page-wide question payload differs from independent BEFORE.'); }
        }
        $eligible = array_values(array_filter($questions, static fn ($q) => $q['seeder'] === $old && (string) $q['type'] === '4' && $q['level'] === Package::LEVELS[$i]));
        usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
        if (array_column($eligible, 'id') !== ($target['expected_old_pool_ids'] ?? null)
            || array_filter($questions, static fn ($q) => in_array($q['seeder'], Supplement::BANKS, true)) !== []) {
            throw new RuntimeException('M64 old pool or isolated new-practice relation changed.');
        }
    }

    protected function m64QuestionState(string $state, array $patch, bool $lock): array
    {
        $evidence = $this->questionEvidence(); $inventory = $evidence['_inventory'];
        if (!in_array($state, ['before', 'after'], true) || ($evidence['schema_version'] ?? null) !== 1
            || ($evidence['base_sha'] ?? null) !== Package::BASE_SHA || ($evidence['before_sha256'] ?? null) !== self::BEFORE_SHA
            || ($evidence['source_sha256'] ?? null) !== Supplement::SOURCE_SHA || ($evidence['audit_sha256'] ?? null) !== self::AUDIT_SHA
            || ($inventory['schema_version'] ?? null) !== 1 || ($inventory['base_sha'] ?? null) !== Package::BASE_SHA
            || ($inventory['source_sha256'] ?? null) !== Supplement::SOURCE_SHA || ($inventory['question_collisions'] ?? null) !== []) {
            throw new RuntimeException('M64 source/inventory/plan lineage differs.');
        }
        $source = $this->questionSource(); $bindings = [];
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $target['after']['page']['blocks']; $position = count($blocks); $practice = $blocks[$position - 1];
            $bank = $source['banks'][$i];
            $bindings[] = ['owner' => Package::OWNERS[$i], 'level' => Package::LEVELS[$i],
                'category_id' => $bank['category_id'], 'source_id' => $bank['source_id'],
                'practice_uuid' => $this->resolveUuid(Package::OWNERS[$i], $practice, $position)];
            if ($target['identity'] !== Package::OWNERS[$i] || $practice['type'] !== 'practice-set' || $practice['level'] !== Package::LEVELS[$i]) {
                throw new RuntimeException('M64 exact appended practice binding differs.');
            }
        }
        if (($evidence['bindings'] ?? null) !== $bindings) { throw new RuntimeException('M64 all three reviewed bindings differ.'); }
        $projection = Supplement::project($source, $bindings, M51ContentPatch::optionRowsFromInventory($inventory['option_matches'] ?? []));
        if (($evidence['projection'] ?? null) !== $projection || count($projection['questions']) !== 8
            || count($projection['question_theory_text_blocks']) !== 8) { throw new RuntimeException('M64 exact eight-question projection differs.'); }
        $taxonomy = $inventory['taxonomy'] ?? [];
        if (array_keys($taxonomy) !== ['categories', 'sources'] || ($evidence['taxonomy'] ?? null) !== $taxonomy) {
            throw new RuntimeException('M64 taxonomy inventory differs.');
        }
        foreach (['categories' => 'category_id', 'sources' => 'source_id'] as $table => $field) {
            $ids = array_values(array_unique(array_column($bindings, $field))); sort($ids);
            if (array_column($taxonomy[$table], 'id') !== $ids
                || $this->rows($table, static fn ($q) => $q->whereIn('id', $ids), $lock) !== $taxonomy[$table]) {
                throw new RuntimeException('M64 existing taxonomy changed or was replaced.');
            }
        }
        foreach ($projection['reused_options'] as $option) {
            if ($this->rows('question_options', static fn ($q) => $q->where('id', $option['id']), $lock) !== [$option['row']]
                || self::digest($option['row']) !== $option['sha256']) { throw new RuntimeException('M64 reused option drift.'); }
        }
        foreach (Rows::ROW_COLUMNS as $table => $columns) {
            $actual = $this->db->getSchemaBuilder()->getColumnListing($table); sort($actual); sort($columns);
            if ($actual !== $columns || ($this->db->getDriverName() === 'mysql'
                && $this->db->getSchemaBuilder()->getColumns($table) !== ($inventory['schema'][$table] ?? null))) {
                throw new RuntimeException('M64 inspected insert schema differs: '.$table);
            }
            $allocation = $inventory['allocation'][$table] ?? [];
            $start = max((int) ($allocation['max_id'] ?? -1) + 1, (int) ($allocation['auto_increment']['next_id'] ?? -1));
            $keys = array_column($projection[$table], 'key');
            if ($start < 1 || array_keys($evidence['ids'][$table] ?? []) !== $keys
                || array_values($evidence['ids'][$table]) !== ($keys === [] ? [] : range($start, $start + count($keys) - 1))) {
                throw new RuntimeException('M64 new sequential ID allowlist differs.');
            }
            if ($state === 'before' && (int) ($this->db->table($table)->max('id') ?? 0) !== (int) $allocation['max_id']) {
                throw new RuntimeException('M64 ID allocation became stale.');
            }
            if ($state === 'before' && $this->db->getDriverName() === 'mysql') {
                $auto = (array) $this->db->selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?', [$this->db->getDatabaseName(), $table]);
                if ((int) $auto['next_id'] !== (int) $allocation['auto_increment']['next_id']) { throw new RuntimeException('M64 physical ID sequence changed.'); }
            }
        }
        $audit = json_decode(file_get_contents(dirname($this->databasePath).'/'.self::AUDIT), true, flags: JSON_THROW_ON_ERROR);
        $publicIds = [];
        foreach ($audit['targets'] as $target) {
            foreach ($target['first_five'] as $sample) { if ($sample['is_new']) { $publicIds[$sample['uuid']] = $sample['id']; } }
        }
        $orderedIds = [];
        foreach (Supplement::UUIDS as $uuid) { $orderedIds[$uuid] = $publicIds[$uuid] ?? null; }
        if ($orderedIds !== $evidence['ids']['questions']) { throw new RuntimeException('M64 private allocation and public selected-question IDs differ.'); }
        $actual = []; $questionIds = array_values($evidence['ids']['questions']); $practiceUuids = array_column($bindings, 'practice_uuid');
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            $ids = array_values($evidence['ids'][$table]);
            $actual[$table] = $this->rows($table, static function ($q) use ($table, $ids, $questionIds, $practiceUuids): void {
                $q->whereIn('id', $ids);
                if ($table === 'questions') { $q->orWhereIn('uuid', Supplement::UUIDS)->orWhereIn('seeder', Supplement::BANKS)->orWhereIn('theory_text_block_uuid', $practiceUuids); }
                elseif ($table === 'question_theory_text_blocks') { $q->orWhereIn('question_uuid', Supplement::UUIDS)->orWhereIn('text_block_uuid', $practiceUuids); }
                elseif (in_array($table, ['question_answers', 'question_option_question'], true)) { $q->orWhereIn('question_id', $questionIds); }
            }, $lock);
        }
        if ($state === 'before') {
            Rows::materialize($projection, $evidence['ids'], Supplement::ROW_TIMESTAMP); Rows::assertAbsent($actual);
        } else {
            $timestamp = $actual['questions'][0]['created_at'] ?? null;
            if ($timestamp !== Supplement::ROW_TIMESTAMP) { throw new RuntimeException('M64 new question timestamp missing.'); }
            Rows::assertApplied(Rows::materialize($projection, $evidence['ids'], $timestamp), $actual);
        }
        return compact('evidence', 'actual');
    }

    protected function afterInsert(int $index): void
    {
        if ($index !== 2) { return; }
        if ($this->db->transactionLevel() < 1) { throw new RuntimeException('M64 question insert requires inherited backup transaction.'); }
        $patch = $this->questionPatch();
        foreach ($patch['targets'] as $i => $target) {
            $position = count($target['after']['page']['blocks']); $practice = $target['after']['page']['blocks'][$position - 1];
            $uuid = $this->resolveUuid($target['identity'], $practice, $position);
            $rows = $this->rows('text_blocks', static fn ($q) => $q->where('uuid', $uuid), true);
            $fields = ['uuid' => $uuid, 'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i], 'locale' => 'uk',
                'seeder' => Package::OWNERS[$i], 'type' => 'practice-set', 'column' => $practice['column'],
                'heading' => $practice['heading'] ?? null, 'css_class' => $practice['css_class'] ?? null,
                'sort_order' => $position, 'body' => $practice['body'], 'level' => Package::LEVELS[$i]];
            if (count($rows) !== 1) { throw new RuntimeException('M64 all three practices required before question insert.'); }
            foreach ($fields as $field => $value) { if ($rows[0][$field] !== $value) { throw new RuntimeException('M64 new practice differs before question insert.'); } }
        }
        $scope = $this->m64QuestionState('before', $patch, true);
        $rows = Rows::materialize($scope['evidence']['projection'], $scope['evidence']['ids'], Supplement::ROW_TIMESTAMP);
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            foreach ($rows[$table] as $n => $row) {
                if (!$this->db->table($table)->insert($row)) { throw new RuntimeException('M64 scoped insert failed.'); }
                $this->afterQuestionRowInsert($table, $n);
            }
        }
        $this->m64QuestionState('after', $patch, true);
    }

    protected function afterQuestionRowInsert(string $table, int $index): void {}
}
