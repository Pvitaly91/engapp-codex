<?php

namespace App\Services;

use App\Support\M56GerundSimplePassivesPackage as Package;
use RuntimeException;

/** Four existing UK bodies only; M26 owns preview equality, locks, backup and transaction. */
class M56HrefCorrectionPatch extends M26ContentPatch
{
    public const NAMES = Package::OWNERS;
    public const DELTA = 'database/content-patches/m56-gerund-simple-passives-correction-delta.v1.0.1.json';
    public const DELTA_SHA = '2c6e25b7983cb7e2928d5b9e12ddac864ac5390ea53cca4ca94013a7c8097cd7';
    public const BASELINE = 'final-readonly-v2.json';
    public const BASELINE_SHA = 'e6edbcfefbb34e299323ca31d895e47bcceee22da4ae47a604d43e4e9d00756b';
    public const IDS = [3701, 3706, 3597, 3600];
    private const BINDINGS = [
        [1, 3701, '766abe40-ece7-52e4-9f9e-ddd297c46b4e', 3, 'usage-panels', 'left'],
        [1, 3706, '58b6b8dd-3b28-553e-9f54-bb4a33f879f6', 8, 'navigation-chips', 'footer'],
        [2, 3597, '729c0883-d974-555e-9957-b82953473863', 6, 'usage-panels', 'left'],
        [2, 3600, 'e34ea2d3-5316-579f-bb50-49811a851b15', 9, 'navigation-chips', 'footer'],
    ];
    protected const ID = 'm56-href-correction-1.0.0-to-1.0.1';

    protected function localGuard(): string { return M56LocalTargetGuard::class; }
    protected function expectedInsertCount(): int { return 0; }
    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        throw new RuntimeException('M56 href restore is outside the approved four-body forward correction.');
    }
    public function connection(?string $expected = null, bool $writing = false): array
    {
        if ($this->db->getDriverName() === 'mysql') {
            M49LocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget !== 'gramlyze.loc' || $this->localProof === null) {
                throw new RuntimeException('M56 href correction requires fresh explicit local proof.');
            }
        }
        return parent::connection($expected, $writing);
    }

    public static function correction(string $root): array
    {
        $path = $root.'/'.self::DELTA;
        if (!hash_equals(self::DELTA_SHA, M56ContentPatch::fileHash($path))) {
            throw new RuntimeException('M56 href delta is not independently frozen.');
        }
        $delta = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($delta['schema_version'] ?? null) !== 1 || ($delta['status'] ?? null) !== 'frozen'
            || ($delta['base_sha'] ?? null) !== Package::BASE_SHA
            || ($delta['from_version'] ?? null) !== '1.0.0' || ($delta['to_version'] ?? null) !== '1.0.1'
            || array_column($delta['updates'] ?? [], 'id') !== self::IDS || empty($delta['source_artifacts'])) {
            throw new RuntimeException('M56 exact four-body href transition differs.');
        }
        foreach ($delta['updates'] as $n => $change) {
            [$i, $id, $uuid, $slot, $type, $column] = self::BINDINGS[$n];
            $metadata = ['locale' => 'uk', 'type' => $type, 'column' => $column, 'heading' => null,
                'level' => 'A2', 'css_class' => null, 'sort_order' => $slot + 1, 'seeder' => Package::OWNERS[$i]];
            if (($change['identity'] ?? null) !== Package::OWNERS[$i] || ($change['page_id'] ?? null) !== Package::PAGE_IDS[$i]
                || ($change['category_id'] ?? null) !== 1 || ($change['ancestry'] ?? null) !== ['passive-voice']
                || ($change['id'] ?? null) !== $id || ($change['uuid'] ?? null) !== $uuid
                || ($change['source_index'] ?? null) !== $slot || ($change['metadata'] ?? null) !== $metadata
                || !is_string($change['before_body'] ?? null) || !is_string($change['after_body'] ?? null)
                || $change['before_body'] === $change['after_body']) {
                throw new RuntimeException('M56 href row identity/metadata differs.');
            }
            $projected = $change['before_body'];
            foreach (['present-continuous', 'present-perfect', 'past-continuous', 'past-perfect',
                'future-simple', 'future-continuous', 'future-perfect'] as $topic) {
                $projected = str_replace('/theory/passive-voice/theory-passive-voice-'.$topic,
                    '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-'.$topic, $projected);
            }
            if ($projected !== $change['after_body']) { throw new RuntimeException('M56 correction changes more than exact seven href destinations.'); }
        }
        foreach ($delta['source_artifacts'] as $relative => $sha) {
            if (!is_string($relative) || str_contains($relative, '..') || str_starts_with($relative, '/')
                || preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1
                || !hash_equals($sha, M56ContentPatch::fileHash($root.'/'.$relative))) {
                throw new RuntimeException('M56 href frozen transition source differs.');
            }
        }
        return $delta;
    }

    protected function baselineEvidence(): array
    {
        $path = $this->privateDirectory.'/'.self::BASELINE;
        if (!hash_equals(self::BASELINE_SHA, M56ContentPatch::fileHash($path))) {
            throw new RuntimeException('M56 href independent after-1.0.0 baseline differs.');
        }
        $data = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($data['all_old_rows_and_timestamps_exact'] ?? null) !== true
            || ($data['independent_before_projection_exact'] ?? null) !== true
            || ($data['reviewed_protected_projection_exact'] ?? null) !== true
            || ($data['db_writes'] ?? null) !== 0 || ($data['http_requests'] ?? null) !== 0
            || ($data['plan_digest'] ?? null) !== '89633674fd38791d061ab332cca181044f0af328a6cb85b3842147107a5f04b1'
            || count($data['sql_after'] ?? []) !== 46 || count($data['normalized_before'] ?? []) !== 46) {
            throw new RuntimeException('M56 href baseline lacks exact original BEFORE reconstruction.');
        }
        if ($this->db->getDriverName() === 'mysql'
            && ($data['runtime'] ?? null) !== M49LocalTargetGuard::publicIdentity($this->db, dirname($this->databasePath).'/public')) {
            throw new RuntimeException('M56 href current runtime differs from the independent local baseline.');
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
        $path = $this->privateDirectory.'/'.M56ContentPatch::BEFORE_FILE;
        if (!hash_equals(M56ContentPatch::BEFORE_SHA, M56ContentPatch::fileHash($path))) {
            throw new RuntimeException('M56 href original independent source baseline differs.');
        }
        $before = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (empty($before['visual_hashes']) || empty($before['frozen_hashes'])) {
            throw new RuntimeException('M56 href original visual/frozen inventories are incomplete.');
        }
        $sources = [];
        foreach ([$before['visual_hashes'], $before['frozen_hashes']] as $hashes) {
            foreach ($hashes as $relative => $sha) {
                if (!hash_equals($sha, M56ContentPatch::fileHash($root.'/'.$relative))) {
                    throw new RuntimeException('M56 href visual/historical frozen source changed: '.$relative);
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
            throw new RuntimeException('M56 href full-table state differs outside four exact body values.');
        }
        return ['baseline_sha256' => self::BASELINE_SHA, 'sql_after' => $this->fingerprints(),
            'normalized_after_1_0_0' => $normalized, 'normalized_before' => $baseline['normalized_before'],
            'independent_before_sha256' => $baseline['independent_before_sha256'], 'runtime' => $baseline['runtime']];
    }

    /** SELECT-only aggregate evidence; never calls savePlan/apply or writes a receipt. */
    public function verification(): array
    {
        if ($this->db->getDriverName() === 'mysql') { M49LocalTargetGuard::assertConfiguredDatabase($this->db); }
        $root = dirname($this->databasePath); $delta = self::correction($root); $this->sourceAudit($root);
        foreach ($delta['updates'] as $change) {
            $rows = $this->rows('text_blocks', fn ($q) => $q->where('id', $change['id']), false);
            if (count($rows) !== 1 || $rows[0]['body'] !== $change['after_body']) {
                throw new RuntimeException('M56 href final verification requires four exact AFTER bodies.');
            }
        }
        return $this->evidence($delta) + ['state' => 'after',
            'all_old_rows_and_timestamps_exact' => true, 'independent_before_projection_exact' => true,
            'reviewed_protected_projection_exact' => true, 'db_writes' => 0, 'http_requests' => 0];
    }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath); $connection = $this->connection(); $delta = self::correction($root);
        [$before, $patch, $master, $contract] = Package::load($root);
        Package::validate($before, $patch, $master, $contract);
        $paths = [...M56ContentPatch::sourcePaths($patch, $contract, $root), self::DELTA,
            'app/Services/M56HrefCorrectionPatch.php', 'app/Console/Commands/PatchM56Hrefs.php',
            ...array_keys($delta['source_artifacts'])];
        $plan = ['patch' => self::ID, 'version' => 1, 'names' => self::NAMES, 'connection' => $connection,
            'sources' => $this->sourceAudit($root), 'pages' => [], 'updates' => [], 'inserts' => []];
        foreach (array_unique($paths) as $path) { $plan['sources'][$path] = M56ContentPatch::fileHash($root.'/'.$path); }
        if ($this->db->getDriverName() === 'mysql') {
            $plan['sources']['private:'.self::BASELINE] = M56ContentPatch::fileHash($this->privateDirectory.'/'.self::BASELINE);
            $plan['sources']['private:'.M56ContentPatch::BEFORE_FILE] = M56ContentPatch::fileHash($this->privateDirectory.'/'.M56ContentPatch::BEFORE_FILE);
            $syncPath = $this->privateDirectory.'/'.M56ContentPatch::SYNC_RECEIPT;
            $sync = json_decode(file_get_contents($syncPath), true, flags: JSON_THROW_ON_ERROR);
            $expected = M56ContentPatch::syncPaths($patch, $contract); $actual = array_column($sync['files'] ?? [], 'path'); sort($actual);
            if ($actual !== $expected || ($sync['base_sha'] ?? null) !== Package::BASE_SHA
                || ($sync['served_root'] ?? null) !== M56LocalTargetGuard::ROOT
                || ($sync['source_worktree'] ?? null) !== M56ContentPatch::SOURCE_WORKTREE) {
                throw new RuntimeException('M56 href exact active source-sync scope differs.');
            }
            foreach ($sync['files'] as $record) {
                if (M56ContentPatch::fileHash(M56LocalTargetGuard::ROOT.'/'.$record['path']) !== $record['root_after_sha256']
                    || M56ContentPatch::fileHash(M56ContentPatch::SOURCE_WORKTREE.'/'.$record['path']) !== $record['worktree_sha256']) {
                    throw new RuntimeException('M56 href served/source hashes drifted after sync.');
                }
            }
            $plan['sources']['private:'.M56ContentPatch::SYNC_RECEIPT] = M56ContentPatch::fileHash($syncPath);
        }
        $states = [];
        foreach ($patch['targets'] as $i => $target) {
            if (json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR) !== $target['after']) {
                throw new RuntimeException('M56 href active definition differs from reviewed 1.0.1 AFTER.');
            }
            $owner = self::NAMES[$i]; $pages = $this->rows('pages', fn ($q) => $q->where('seeder', $owner), $lock);
            if (count($pages) !== 1) { throw new RuntimeException('M56 href Page missing or ambiguous.'); }
            $page = $pages[0]; $chain = $this->categoryChain($page['page_category_id'], $lock);
            if ($page['id'] !== Package::PAGE_IDS[$i] || $page['page_category_id'] !== Package::CATEGORY_IDS[$i]
                || $page['slug'] !== Package::SLUGS[$i] || $page['type'] !== 'theory'
                || array_column($chain, 'slug') !== Package::ANCESTRIES[$i]) {
                throw new RuntimeException('M56 href Page/category binding differs.');
            }
            $blocks = $this->rows('text_blocks', fn ($q) => $q->where('page_id', $page['id']), $lock);
            foreach ($delta['updates'] as $change) {
                if ($change['identity'] !== $owner) { continue; }
                if ($target['after']['page']['blocks'][$change['source_index']]['body'] !== $change['after_body']) {
                    throw new RuntimeException('M56 href delta differs from the complete 1.0.1 author projection.');
                }
                $matches = array_values(array_filter($blocks, fn ($row) => $row['id'] === $change['id']));
                if (count($matches) !== 1) { throw new RuntimeException('M56 href exact row missing.'); }
                $row = $matches[0];
                foreach ($change['metadata'] + ['uuid' => $change['uuid'], 'page_id' => $change['page_id'],
                    'page_category_id' => $change['category_id']] as $field => $value) {
                    if (($row[$field] ?? null) !== $value) { throw new RuntimeException('M56 href current row metadata differs.'); }
                }
                $states[] = $this->candidate($plan['updates'], 'text_blocks', $row, $owner,
                    ['body' => $change['before_body']], ['body' => $change['after_body']]);
            }
            $plan['pages'][$owner] = ['page' => $page, 'category_ancestry' => $chain, 'blocks' => $blocks,
                'relations' => $this->relations($page, $blocks, $lock)];
        }
        if (count(array_unique($states)) !== 1 || count($states) !== 4) { throw new RuntimeException('M56 href partial correction refused.'); }
        $plan['state'] = $states[0];
        if (count($plan['updates']) !== ($plan['state'] === 'before' ? 4 : 0)) { throw new RuntimeException('M56 href exact four/zero update count differs.'); }
        $this->evidence($delta);
        $plan['protected'] = $this->protectedFingerprints(self::IDS); ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }
}
