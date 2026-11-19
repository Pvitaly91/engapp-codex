<?php

namespace App\Services;

use App\Support\M60ReportedSpeechBasicsPackage as Package;
use RuntimeException;

/** Three existing UK bodies only; M26 owns preview equality, locks, backup and transaction. */
class M60PracticeFeedbackCorrectionPatch extends M26ContentPatch
{
    public const NAMES = Package::OWNERS;
    public const DELTA = 'database/content-patches/m60-reported-speech-basics-feedback-correction.v1.0.1.json';
    public const DELTA_SHA = 'a045e71ef9384cca2396c84798fc9a5250900198a5cf9851b05dbaa6ff6f9c83';
    public const BASELINE = 'final-readonly-v2.json';
    public const BASELINE_SHA = 'c8fa8663e7817b54c6a3de289f2e2290d3369e9a8d611611b93735259b9633be';
    public const IDS = [12720, 12722, 12724];
    private const BINDINGS = [
        [0, 12720, '8e555dad-3389-5f80-822f-8c3bfc10c799', 7, 'practice-set', 'left'],
        [1, 12722, '509dae3b-1d28-5d72-8212-dfe7251dba5f', 7, 'practice-set', 'left'],
        [2, 12724, '32a5a3ba-f454-535b-a240-e1741868c874', 7, 'practice-set', 'left'],
    ];
    public const FEEDBACK_COUNTS = [52, 22, 20];
    protected const ID = 'm60-feedback-correction-1.0.0-to-1.0.1';

    protected function localGuard(): string { return M60LocalTargetGuard::class; }
    protected function expectedInsertCount(): int { return 0; }
    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        throw new RuntimeException('M60 feedback restore is outside the approved three-body forward correction.');
    }
    public function connection(?string $expected = null, bool $writing = false): array
    {
        if ($this->db->getDriverName() === 'mysql') {
            M49LocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget !== 'gramlyze.loc' || $this->localProof === null) {
                throw new RuntimeException('M60 feedback correction requires fresh explicit local proof.');
            }
        }
        return parent::connection($expected, $writing);
    }

    public static function correction(string $root): array
    {
        $path = $root.'/'.self::DELTA;
        if (!hash_equals(self::DELTA_SHA, M60ContentPatch::fileHash($path))) {
            throw new RuntimeException('M60 feedback delta is not independently frozen.');
        }
        $delta = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($delta['schema_version'] ?? null) !== 1 || ($delta['status'] ?? null) !== 'frozen'
            || ($delta['base_sha'] ?? null) !== Package::BASE_SHA
            || ($delta['from_version'] ?? null) !== '1.0.0' || ($delta['to_version'] ?? null) !== '1.0.1'
            || array_column($delta['updates'] ?? [], 'id') !== self::IDS || empty($delta['source_artifacts'])) {
            throw new RuntimeException('M60 exact three-body feedback transition differs.');
        }
        $historical = json_decode(file_get_contents($root.'/database/content-patches/m60-reported-speech-basics.v1.json'), true, flags: JSON_THROW_ON_ERROR);
        $active = json_decode(file_get_contents($root.'/'.Package::SOURCE), true, flags: JSON_THROW_ON_ERROR);
        foreach ($delta['updates'] as $n => $change) {
            [$i, $id, $uuid, $slot, $type, $column] = self::BINDINGS[$n];
            $metadata = ['locale' => 'uk', 'type' => $type, 'column' => $column, 'heading' => null,
                'level' => 'B1', 'css_class' => null, 'sort_order' => $slot + 1, 'seeder' => Package::OWNERS[$i]];
            if (($change['identity'] ?? null) !== Package::OWNERS[$i] || ($change['page_id'] ?? null) !== Package::PAGE_IDS[$i]
                || ($change['category_id'] ?? null) !== 3 || ($change['ancestry'] ?? null) !== ['reported-speech']
                || ($change['id'] ?? null) !== $id || ($change['uuid'] ?? null) !== $uuid
                || ($change['source_index'] ?? null) !== $slot || ($change['metadata'] ?? null) !== $metadata
                || !is_string($change['before_body'] ?? null) || !is_string($change['after_body'] ?? null)
                || $change['before_body'] === $change['after_body']) {
                throw new RuntimeException('M60 feedback row identity/metadata differs.');
            }
            if (self::plainFeedbackBody($change['before_body'], self::FEEDBACK_COUNTS[$i]) !== $change['after_body']) {
                throw new RuntimeException('M60 correction changes more than exact reviewed plain-text feedback leaves.');
            }
            $oldTarget = $historical['targets'][$i] ?? null; $newTarget = $active['targets'][$i] ?? null;
            if (($oldTarget['after']['page']['blocks'][$slot]['body'] ?? null) !== $change['before_body']
                || ($newTarget['after']['page']['blocks'][$slot]['body'] ?? null) !== $change['after_body']) {
                throw new RuntimeException('M60 correction bodies differ from exact historical/active native sources.');
            }
            $newTarget['after']['page']['blocks'][$slot]['body'] = $change['before_body'];
            if ($newTarget !== $oldTarget) {
                throw new RuntimeException('M60 correction changed non-practice blocks or non-body metadata.');
            }
        }
        foreach ($delta['source_artifacts'] as $relative => $sha) {
            if (!is_string($relative) || str_contains($relative, '..') || str_starts_with($relative, '/')
                || preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1
                || !hash_equals($sha, M60ContentPatch::fileHash($root.'/'.$relative))) {
                throw new RuntimeException('M60 feedback frozen transition source differs.');
            }
        }
        return $delta;
    }

    /** Only four literal author wrappers may change; native labels/prompts/answers remain byte-exact data. */
    public static function plainFeedbackBody(string $body, int $expectedChanges): string
    {
        $data = json_decode($body, true, flags: JSON_THROW_ON_ERROR); $changes = 0; $tasks = 0;
        foreach (['selects', 'choices', 'inputs'] as $group) {
            if (!is_array($data[$group] ?? null) || count($data[$group]) !== 2) {
                throw new RuntimeException('M60 feedback correction requires exact native 2/2/2 task groups.');
            }
            foreach ($data[$group] as &$task) {
                ++$tasks;
                if (!is_array($task['feedback'] ?? null) || $task['feedback'] === []) {
                    throw new RuntimeException('M60 feedback map is absent.');
                }
                foreach ($task['feedback'] as &$value) {
                    if (!is_string($value)) { throw new RuntimeException('M60 feedback value is not a string.'); }
                    $plain = str_replace(['<br>', '<span lang="en">', '<span lang="uk">', '</span>'], ["\n", '', '', ''], $value);
                    if ($plain !== $value) { ++$changes; }
                    if (preg_match('/<[^>]*>/', $plain)) { throw new RuntimeException('M60 plain feedback retains unsupported markup.'); }
                    $value = $plain;
                }
                unset($value);
            }
            unset($task);
        }
        if ($tasks !== 6 || $changes !== $expectedChanges) { throw new RuntimeException('M60 exact feedback leaf count differs.'); }
        return \App\Support\M26DetailPackage::json($data);
    }

    protected function baselineEvidence(): array
    {
        $path = $this->privateDirectory.'/'.self::BASELINE;
        if (!hash_equals(self::BASELINE_SHA, M60ContentPatch::fileHash($path))) {
            throw new RuntimeException('M60 feedback independent after-1.0.0 baseline differs.');
        }
        $data = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($data['all_old_rows_and_timestamps_exact'] ?? null) !== true
            || ($data['independent_before_projection_exact'] ?? null) !== true
            || ($data['reviewed_protected_projection_exact'] ?? null) !== true
            || ($data['db_writes'] ?? null) !== 0 || ($data['http_requests'] ?? null) !== 0
            || ($data['plan_digest'] ?? null) !== '5f7c3fdbd94bcf780bff6d0c5571de9312afab3a972152ec6ee6985c7ff76389'
            || count($data['sql_after'] ?? []) !== 46 || count($data['normalized_before'] ?? []) !== 46) {
            throw new RuntimeException('M60 feedback baseline lacks exact original BEFORE reconstruction.');
        }
        if ($this->db->getDriverName() === 'mysql'
            && ($data['runtime'] ?? null) !== M49LocalTargetGuard::publicIdentity($this->db, dirname($this->databasePath).'/public')) {
            throw new RuntimeException('M60 feedback current runtime differs from the independent local baseline.');
        }
        return $data;
    }

    private function fingerprints(array $normalize = [], array $exclude = []): array
    {
        $schema = $this->db->getDriverName() === 'sqlite' ? 'main' : $this->db->getDatabaseName();
        $tables = $this->db->getSchemaBuilder()->getTableListing(schema: $schema, schemaQualified: false); sort($tables); $out = [];
        foreach ($tables as $table) {
            $columns = $this->db->getSchemaBuilder()->getColumnListing($table); $query = $this->db->table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $hash = hash_init('sha256'); $count = 0;
            foreach ($query->cursor() as $record) {
                $row = (array) $record;
                if ($table === 'text_blocks' && in_array($row['id'], $exclude, true)) { continue; }
                if ($table === 'text_blocks' && isset($normalize[$row['id']])) { $row['body'] = $normalize[$row['id']]; }
                hash_update($hash, self::digest($row)."\n"); ++$count;
            }
            $out[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
        }
        return $out;
    }
    protected function protectedFingerprints(array $targetIds): array { return $this->fingerprints(exclude: $targetIds); }

    /** Fresh bytes, not inherited PASS flags, guard every original visual/non-target source. */
    protected function sourceAudit(string $root): array
    {
        $path = $this->privateDirectory.'/'.M60ContentPatch::BEFORE_FILE;
        if (!hash_equals(M60ContentPatch::BEFORE_SHA, M60ContentPatch::fileHash($path))) {
            throw new RuntimeException('M60 feedback original independent source baseline differs.');
        }
        $before = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (empty($before['visual_hashes']) || empty($before['frozen_hashes'])) {
            throw new RuntimeException('M60 feedback original visual/frozen inventories are incomplete.');
        }
        $sources = [];
        foreach ([$before['visual_hashes'], $before['frozen_hashes']] as $hashes) {
            foreach ($hashes as $relative => $sha) {
                if (!hash_equals($sha, M60ContentPatch::fileHash($root.'/'.$relative))) {
                    throw new RuntimeException('M60 feedback visual/historical frozen source changed: '.$relative);
                }
                $sources[$relative] = $sha;
            }
        }
        return $sources;
    }

    private function evidence(array $delta): array
    {
        $baseline = $this->baselineEvidence(); $before = [];
        foreach ($delta['updates'] as $change) { $before[$change['id']] = $change['before_body']; }
        $normalized = $this->fingerprints($before);
        if ($normalized !== ($baseline['sql_after'] ?? null)) {
            throw new RuntimeException('M60 feedback full-table state differs outside three exact body values.');
        }
        return ['baseline_sha256' => self::BASELINE_SHA, 'sql_after' => $this->fingerprints(),
            'normalized_after_1_0_0' => $normalized, 'normalized_before' => $baseline['normalized_before'],
            'independent_before_sha256' => $baseline['independent_before_sha256'], 'runtime' => $baseline['runtime'],
            'new_native_blocks' => $baseline['new_native_blocks'], 'new_question_families' => [],
            'sql_tables' => count($normalized)];
    }

    /** Exact reviewed v2 filesystem mirror is checked both before writes and during read-only verification. */
    private function sourceSync(array $patch, array $contract): ?string
    {
        if ($this->db->getDriverName() !== 'mysql') { return null; }
        $syncPath = $this->privateDirectory.'/'.M60ContentPatch::SYNC_RECEIPT;
        $sync = json_decode(file_get_contents($syncPath), true, flags: JSON_THROW_ON_ERROR);
        $expected = M60ContentPatch::syncPaths($patch, $contract); $actual = array_column($sync['files'] ?? [], 'path'); sort($actual);
        if ($actual !== $expected || ($sync['base_sha'] ?? null) !== Package::BASE_SHA
            || ($sync['served_root'] ?? null) !== M60LocalTargetGuard::ROOT
            || ($sync['source_worktree'] ?? null) !== M60ContentPatch::SOURCE_WORKTREE) {
            throw new RuntimeException('M60 feedback exact active source-sync scope differs.');
        }
        foreach ($sync['files'] as $record) {
            if (M60ContentPatch::fileHash(M60LocalTargetGuard::ROOT.'/'.$record['path']) !== $record['root_after_sha256']
                || M60ContentPatch::fileHash(M60ContentPatch::SOURCE_WORKTREE.'/'.$record['path']) !== $record['worktree_sha256']) {
                throw new RuntimeException('M60 feedback served/source hashes drifted after sync.');
            }
        }
        return M60ContentPatch::fileHash($syncPath);
    }

    /** SELECT-only aggregate evidence; never calls savePlan/apply or writes a receipt. */
    public function verification(): array
    {
        if ($this->db->getDriverName() === 'mysql') { M49LocalTargetGuard::assertConfiguredDatabase($this->db); }
        $root = dirname($this->databasePath); $delta = self::correction($root); $this->sourceAudit($root);
        [$before, $patch, $master, $contract] = Package::load($root);
        Package::validate($before, $patch, $master, $contract);
        $syncSha = $this->sourceSync($patch, $contract);
        foreach ($delta['updates'] as $change) {
            $rows = $this->rows('text_blocks', fn ($q) => $q->where('id', $change['id']), false);
            if (count($rows) !== 1 || $rows[0]['body'] !== $change['after_body']) {
                throw new RuntimeException('M60 feedback final verification requires three exact AFTER bodies.');
            }
        }
        return $this->evidence($delta) + ['state' => 'after',
            'all_old_rows_and_timestamps_exact' => true, 'independent_before_projection_exact' => true,
            'reviewed_protected_projection_exact' => true, 'source_sync_unchanged' => $syncSha !== null,
            'source_sync_receipt_sha256' => $syncSha, 'db_writes' => 0, 'http_requests' => 0];
    }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath); $connection = $this->connection(); $delta = self::correction($root);
        [$before, $patch, $master, $contract] = Package::load($root);
        Package::validate($before, $patch, $master, $contract);
        $paths = [...M60ContentPatch::sourcePaths($patch, $contract, $root), self::DELTA,
            'app/Services/M60PracticeFeedbackCorrectionPatch.php', 'app/Console/Commands/PatchM60PracticeFeedback.php',
            ...array_keys($delta['source_artifacts'])];
        $plan = ['patch' => self::ID, 'version' => 1, 'names' => self::NAMES, 'connection' => $connection,
            'sources' => $this->sourceAudit($root), 'pages' => [], 'updates' => [], 'inserts' => []];
        foreach (array_unique($paths) as $path) { $plan['sources'][$path] = M60ContentPatch::fileHash($root.'/'.$path); }
        $syncSha = $this->sourceSync($patch, $contract);
        if ($syncSha !== null) {
            $plan['sources']['private:'.self::BASELINE] = M60ContentPatch::fileHash($this->privateDirectory.'/'.self::BASELINE);
            $plan['sources']['private:'.M60ContentPatch::BEFORE_FILE] = M60ContentPatch::fileHash($this->privateDirectory.'/'.M60ContentPatch::BEFORE_FILE);
            $plan['sources']['private:'.M60ContentPatch::SYNC_RECEIPT] = $syncSha;
        }
        $states = [];
        foreach ($patch['targets'] as $i => $target) {
            if (json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR) !== $target['after']) {
                throw new RuntimeException('M60 feedback active definition differs from reviewed 1.0.1 AFTER.');
            }
            $owner = self::NAMES[$i]; $pages = $this->rows('pages', fn ($q) => $q->where('seeder', $owner), $lock);
            if (count($pages) !== 1) { throw new RuntimeException('M60 feedback Page missing or ambiguous.'); }
            $page = $pages[0]; $chain = $this->categoryChain($page['page_category_id'], $lock);
            if ($page['id'] !== Package::PAGE_IDS[$i] || $page['page_category_id'] !== Package::CATEGORY_IDS[$i]
                || $page['slug'] !== Package::SLUGS[$i] || $page['type'] !== 'theory'
                || array_column($chain, 'slug') !== Package::ANCESTRIES[$i]) {
                throw new RuntimeException('M60 feedback Page/category binding differs.');
            }
            $blocks = $this->rows('text_blocks', fn ($q) => $q->where('page_id', $page['id']), $lock);
            foreach ($delta['updates'] as $change) {
                if ($change['identity'] !== $owner) { continue; }
                if ($target['after']['page']['blocks'][$change['source_index']]['body'] !== $change['after_body']) {
                    throw new RuntimeException('M60 feedback delta differs from the complete 1.0.1 author projection.');
                }
                $matches = array_values(array_filter($blocks, fn ($row) => $row['id'] === $change['id']));
                if (count($matches) !== 1) { throw new RuntimeException('M60 feedback exact row missing.'); }
                $row = $matches[0];
                foreach ($change['metadata'] + ['uuid' => $change['uuid'], 'page_id' => $change['page_id'],
                    'page_category_id' => $change['category_id']] as $field => $value) {
                    if (($row[$field] ?? null) !== $value) { throw new RuntimeException('M60 feedback current row metadata differs.'); }
                }
                $states[] = $this->candidate($plan['updates'], 'text_blocks', $row, $owner,
                    ['body' => $change['before_body']], ['body' => $change['after_body']]);
            }
            $plan['pages'][$owner] = ['page' => $page, 'category_ancestry' => $chain, 'blocks' => $blocks,
                'relations' => $this->relations($page, $blocks, $lock)];
        }
        if (count(array_unique($states)) !== 1 || count($states) !== 3) { throw new RuntimeException('M60 feedback partial correction refused.'); }
        $plan['state'] = $states[0];
        if (count($plan['updates']) !== ($plan['state'] === 'before' ? 3 : 0)) { throw new RuntimeException('M60 feedback exact three/zero update count differs.'); }
        $this->evidence($delta);
        $plan['protected'] = $this->protectedFingerprints(self::IDS); ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }
}
