<?php

namespace App\Services;

use App\Support\M51QuestionSupplement as Rows;
use App\Support\M53BuilderHintPresentation;
use App\Support\M53PassiveConstructionsPackage as Package;
use App\Support\M53QuestionSupplement as Supplement;
use RuntimeException;

/** The finite three-Get-question exception; M26 still owns backup, locks and transaction. */
trait M53QuestionInsertScope
{
    public const QUESTION_PLAN_FILE = 'question-plan-v1.json';
    public const QUESTION_PLAN_SHA = '4845dc645f6303ddc829d923763e1d54b5b9fc2aa207e245089694c69b7f183d';
    public const QUESTION_INVENTORY_FILE = 'question-inventory-v1.json';
    public const QUESTION_INVENTORY_SHA = '3cdf6c32166d647f86170d02c505d0abafd5dc90a47015c7490c3fbf53afb8a2';
    public const QUESTION_INVENTORY_DRAFT_SHA = '7c21a82ea9f600fac3369640f448dbaefda1442d7abf736f4c53d2e9ba23f44f';
    private array $m53ApprovedQuestionRows = [];

    protected function questionEvidence(): array
    {
        $path = $this->privateDirectory.'/'.self::QUESTION_PLAN_FILE;
        if (preg_match('/^[a-f0-9]{64}$/D', self::QUESTION_PLAN_SHA) !== 1
            || !hash_equals(self::QUESTION_PLAN_SHA, self::fileHash($path))) {
            throw new RuntimeException('M53 question plan is not reviewed/frozen.');
        }
        $evidence = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($evidence['inventory'] ?? null) !== ['file' => self::QUESTION_INVENTORY_FILE, 'sha256' => self::QUESTION_INVENTORY_SHA]
            || !hash_equals(self::QUESTION_INVENTORY_SHA, self::fileHash($this->privateDirectory.'/'.self::QUESTION_INVENTORY_FILE))) {
            throw new RuntimeException('M53 independent question inventory bytes differ.');
        }
        $evidence['_inventory'] = json_decode(file_get_contents($this->privateDirectory.'/'.self::QUESTION_INVENTORY_FILE), true, flags: JSON_THROW_ON_ERROR);

        return $evidence;
    }

    protected function questionSource(): array { return Supplement::load(dirname($this->databasePath)); }
    protected function questionPatch(): array { return Package::load(dirname($this->databasePath))[1]; }

    protected function extendPlan(array &$plan, array $patch, ?array $baseline, bool $lock): void
    {
        $scope = $this->m53QuestionState($plan['state'], $patch, $lock);
        if ($baseline !== null && ($scope['evidence']['_inventory']['identity'] ?? null) !== ($baseline['identity'] ?? null)) {
            throw new RuntimeException('M53 question inventory/runtime identity differs from fresh BEFORE.');
        }
        $plan['sources']['private:'.self::QUESTION_PLAN_FILE] = self::QUESTION_PLAN_SHA;
        $plan['sources']['private:'.self::QUESTION_INVENTORY_FILE] = self::QUESTION_INVENTORY_SHA;
        $plan['question_supplement'] = $scope;
        $this->m53ApprovedQuestionRows = [];
        foreach ($scope['actual'] as $table => $rows) { $this->m53ApprovedQuestionRows[$table] = array_column($rows, null, 'id'); }
    }

    protected function omitProtectedRow(string $table, array $row): bool
    {
        // IDs alone never exclude a row. Reused options are not approved inserts.
        return isset($row['id']) && ($this->m53ApprovedQuestionRows[$table][$row['id']] ?? null) === $row;
    }

    protected function assertExistingPools(array $questions, array $contract, ?array $baseline): void
    {
        if (($contract['identity'] ?? null) !== Supplement::OWNER) {
            parent::assertExistingPools($questions, $contract, $baseline); return;
        }
        $old = 'Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoiceGetPassiveAllLevelsLessonSeeder';
        if (($contract['bank_seeders'] ?? null) !== [$old, Supplement::BANK] || ($contract['level'] ?? null) !== 'B1') {
            throw new RuntimeException('M53 exact old/new Get bank contract differs.');
        }
        $oldContract = $contract; $oldContract['bank_seeders'] = [$old];
        parent::assertExistingPools($questions, $oldContract, $baseline);
        $eligible = array_values(array_filter($questions, static fn ($q) => $q['seeder'] === $old && (string) $q['type'] === '4' && $q['level'] === 'B1'));
        usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
        $audit = $this->m53BankAudit(); $target = $audit['targets'][1] ?? [];
        if (array_column($eligible, 'id') !== ($target['expected_old_pool_ids'] ?? null)
            || count($eligible) !== ($target['UK_pagewide_eligible_count'] ?? null)
            || array_filter($questions, static fn ($q) => $q['seeder'] === Supplement::BANK) !== []) {
            throw new RuntimeException('M53 full old Get pool or excluded-new-practice relation differs.');
        }
        foreach ($target['existing_selected_two'] ?? [] as $n => $sample) {
            $q = $eligible[$n] ?? [];
            foreach (['id' => 'id', 'uuid' => 'uuid', 'question' => 'condition_uk', 'level' => 'level'] as $field => $key) {
                if (($q[$field] ?? null) !== ($sample[$key] ?? null)) { throw new RuntimeException('M53 first two preserved Get questions differ.'); }
            }
        }
        if (count($target['existing_selected_two'] ?? []) !== 2) { throw new RuntimeException('M53 independent Get first-two audit is incomplete.'); }
    }

    protected function m53BankAudit(): array
    {
        $path = dirname($this->databasePath).'/'.M53BuilderHintPresentation::AUDIT;
        if (preg_match('/^[a-f0-9]{64}$/D', M53BuilderHintPresentation::AUDIT_SHA) !== 1
            || !hash_equals(M53BuilderHintPresentation::AUDIT_SHA, self::fileHash($path))) {
            throw new RuntimeException('M53 frozen independent bank audit differs.');
        }

        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    protected function m53QuestionState(string $state, array $patch, bool $lock): array
    {
        $evidence = $this->questionEvidence(); $inventory = $evidence['_inventory'];
        if (!in_array($state, ['before', 'after'], true) || ($evidence['schema_version'] ?? null) !== 1
            || ($evidence['base_sha'] ?? null) !== Package::BASE_SHA || ($evidence['before_sha256'] ?? null) !== self::BEFORE_SHA
            || ($evidence['source_sha256'] ?? null) !== Supplement::SOURCE_SHA
            || ($evidence['audit_sha256'] ?? null) !== M53BuilderHintPresentation::AUDIT_SHA
            || ($inventory['schema_version'] ?? null) !== 1 || ($inventory['base_sha'] ?? null) !== Package::BASE_SHA
            || ($inventory['source_draft_sha256'] ?? null) !== self::QUESTION_INVENTORY_DRAFT_SHA
            || ($inventory['question_collisions'] ?? null) !== []) {
            throw new RuntimeException('M53 question evidence/base/source/audit lineage differs.');
        }
        $this->m53BankAudit(); $target = $patch['targets'][1] ?? []; $blocks = $target['after']['page']['blocks'] ?? [];
        $position = count($blocks); $practice = $blocks[$position - 1] ?? [];
        $binding = ['owner' => Supplement::OWNER, 'level' => 'B1', 'category_id' => 45, 'source_id' => 354,
            'practice_uuid' => $this->resolveUuid(Supplement::OWNER, $practice, $position)];
        if (($target['identity'] ?? null) !== Supplement::OWNER || ($practice['type'] ?? null) !== 'practice-set'
            || ($practice['level'] ?? null) !== 'B1' || ($evidence['binding'] ?? null) !== $binding) {
            throw new RuntimeException('M53 exact Get practice/taxonomy binding differs.');
        }
        $projection = Supplement::project($this->questionSource(), $binding,
            M51ContentPatch::optionRowsFromInventory($inventory['option_matches'] ?? []));
        $counts = array_map('count', array_intersect_key($projection, Rows::ROW_COLUMNS));
        if ($projection !== ($evidence['projection'] ?? null) || $counts !== ['questions' => 3, 'question_options' => 0,
            'question_answers' => 26, 'question_option_question' => 35, 'question_theory_text_blocks' => 3]) {
            throw new RuntimeException('M53 reviewed three-question insert projection/zero-new-options differs.');
        }
        $taxonomy = $inventory['taxonomy'] ?? [];
        if (array_keys($taxonomy) !== ['categories', 'sources'] || $taxonomy !== ($evidence['taxonomy'] ?? null) || array_column($taxonomy['categories'] ?? [], 'id') !== [45]
            || array_column($taxonomy['sources'] ?? [], 'id') !== [354]) { throw new RuntimeException('M53 existing Get taxonomy snapshot differs.'); }
        foreach ($taxonomy as $table => $rows) {
            if ($this->rows($table, static fn ($q) => $q->whereIn('id', array_column($rows, 'id')), $lock) !== $rows) {
                throw new RuntimeException('M53 original taxonomy changed or was replaced.');
            }
        }
        foreach ($projection['reused_options'] as $option) {
            if ($this->rows('question_options', static fn ($q) => $q->where('id', $option['id']), $lock) !== [$option['row']]
                || self::digest($option['row']) !== $option['sha256']) { throw new RuntimeException('M53 reused option snapshot differs.'); }
        }
        foreach (Rows::ROW_COLUMNS as $table => $columns) {
            $actual = $this->db->getSchemaBuilder()->getColumnListing($table); $expected = $columns; sort($actual); sort($expected);
            if ($actual !== $expected || ($this->db->getDriverName() === 'mysql'
                && $this->db->getSchemaBuilder()->getColumns($table) !== ($inventory['schema'][$table] ?? null))) {
                throw new RuntimeException('M53 exact inspected insert-family schema differs: '.$table);
            }
            $allocation = $inventory['allocation'][$table] ?? [];
            $start = max((int) ($allocation['max_id'] ?? -1) + 1, (int) ($allocation['auto_increment']['next_id'] ?? -1));
            $keys = array_column($projection[$table], 'key'); $values = array_values($evidence['ids'][$table] ?? []);
            if ($start < 1 || array_keys($evidence['ids'][$table] ?? []) !== $keys
                || $values !== ($keys === [] ? [] : range($start, $start + count($keys) - 1))) {
                throw new RuntimeException('M53 reviewed sequential new IDs differ.');
            }
            if ($state === 'before' && (int) ($this->db->table($table)->max('id') ?? 0) !== (int) ($allocation['max_id'] ?? -1)) {
                throw new RuntimeException('M53 ID allocation inventory became stale.');
            }
            if ($state === 'before' && $this->db->getDriverName() === 'mysql') {
                $auto = (array) $this->db->selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$this->db->getDatabaseName(), $table]);
                if ((int) ($auto['next_id'] ?? -1) !== (int) ($allocation['auto_increment']['next_id'] ?? -1)) {
                    throw new RuntimeException('M53 physical ID sequence changed since inventory.');
                }
            }
        }
        $actual = $this->m53QuestionRows($projection, $evidence['ids'], $lock);
        if ($state === 'before') {
            Rows::materialize($projection, $evidence['ids'], '2000-01-01 00:00:00'); Rows::assertAbsent($actual);
        } else {
            $timestamp = $actual['questions'][0]['created_at'] ?? null;
            if (!is_string($timestamp)) { throw new RuntimeException('M53 applied question timestamp missing.'); }
            Rows::assertApplied(Rows::materialize($projection, $evidence['ids'], $timestamp), $actual);
        }

        return compact('evidence', 'actual');
    }

    private function m53QuestionRows(array $projection, array $ids, bool $lock): array
    {
        $questionIds = array_values($ids['questions']); $uuids = Supplement::UUIDS;
        $practice = array_column(array_column($projection['question_theory_text_blocks'], 'fields'), 'text_block_uuid'); $out = [];
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            $newIds = array_values($ids[$table]);
            $out[$table] = $this->rows($table, static function ($q) use ($table, $newIds, $questionIds, $uuids, $practice): void {
                $q->whereIn('id', $newIds);
                if ($table === 'questions') { $q->orWhereIn('uuid', $uuids)->orWhere('seeder', Supplement::BANK)->orWhereIn('theory_text_block_uuid', $practice); }
                elseif ($table === 'question_theory_text_blocks') { $q->orWhereIn('question_uuid', $uuids)->orWhereIn('text_block_uuid', $practice); }
                elseif (in_array($table, ['question_answers', 'question_option_question'], true)) { $q->orWhereIn('question_id', $questionIds); }
            }, $lock);
        }

        return $out;
    }

    protected function afterInsert(int $index): void
    {
        if ($index !== 2) { return; }
        if ($this->db->transactionLevel() < 1) { throw new RuntimeException('M53 question insertion requires the inherited backup transaction.'); }
        $patch = $this->questionPatch();
        foreach ($patch['targets'] as $i => $target) {
            $position = count($target['after']['page']['blocks']); $practice = $target['after']['page']['blocks'][$position - 1];
            $uuid = $this->resolveUuid($target['identity'], $practice, $position);
            $rows = $this->rows('text_blocks', static fn ($q) => $q->where('uuid', $uuid), true);
            $fields = ['uuid' => $uuid, 'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => 1, 'locale' => 'uk',
                'seeder' => Package::OWNERS[$i], 'type' => 'practice-set', 'column' => $practice['column'],
                'heading' => $practice['heading'] ?? null, 'css_class' => $practice['css_class'] ?? null, 'sort_order' => $position,
                'body' => $practice['body'], 'level' => Package::LEVELS[$i]];
            if (count($rows) !== 1) { throw new RuntimeException('M53 all three exact new practices required before questions.'); }
            foreach ($fields as $field => $value) { if ($rows[0][$field] !== $value) { throw new RuntimeException('M53 new practice binding differs before question insertion.'); } }
        }
        $scope = $this->m53QuestionState('before', $patch, true);
        $rows = Rows::materialize($scope['evidence']['projection'], $scope['evidence']['ids'], now()->toDateTimeString());
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            foreach ($rows[$table] as $n => $row) {
                if (!$this->db->table($table)->insert($row)) { throw new RuntimeException('M53 scoped question insert failed; transaction rolls back.'); }
                $this->afterQuestionRowInsert($table, $n);
            }
        }
        $this->m53QuestionState('after', $patch, true);
    }

    protected function afterQuestionRowInsert(string $table, int $index): void {}
}
