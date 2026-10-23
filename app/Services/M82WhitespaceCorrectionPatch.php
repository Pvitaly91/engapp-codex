<?php

namespace App\Services;

use App\Support\M82CommonMistakesLevelRevision as Package;
use App\Support\M82WhitespaceRevision as Revision;
use App\Support\TheoryNativeContentRelease;
use RuntimeException;

/** Exactly two applied 1.0.1 bodies; unchanged M26 owns preview, locks, backup and transaction. */
class M82WhitespaceCorrectionPatch extends M82ContentPatch
{
    public const SYNC_RECEIPT = 'source-sync-whitespace-v1.json';
    protected const ID = 'm82-whitespace-1.0.1-to-1.0.2';
    private array $pending = [];
    private array $previousBodies = [];
    private array $nextBodies = [];
    private bool $normalizingForParent = false;

    protected function expectedInsertCount(): int { return 0; }

    /** The parent validates historical type/heading transitions; the writer can change only body. */
    protected function allowedUpdateFields(): array
    {
        return $this->normalizingForParent ? parent::allowedUpdateFields() : ['body'];
    }

    protected static function policy(): array
    {
        $policy = parent::policy(); $policy['sync_receipt'] = self::SYNC_RECEIPT;
        $policy['sync_code_paths'] = array_values(array_unique([...$policy['sync_code_paths'],
            'app/Support/M82WhitespaceRevision.php', 'app/Services/M82WhitespaceCorrectionPatch.php',
            'app/Console/Commands/ContentPatchM82WhitespaceCorrection.php', Revision::MANIFEST,
            Revision::PREVIOUS_SOURCE, Revision::PREVIOUS_MASTER, Revision::PREVIOUS_CHECKSUMS]));
        return $policy;
    }

    private function prepareTransition(): void
    {
        $root = dirname($this->databasePath);
        foreach ([Revision::PREVIOUS_SOURCE => Revision::PREVIOUS_SOURCE_SHA, Revision::PREVIOUS_MASTER => Revision::PREVIOUS_MASTER_SHA,
            Revision::PREVIOUS_CHECKSUMS => Revision::PREVIOUS_CHECKSUMS_SHA] as $path => $sha) {
            if (!hash_equals($sha, self::fileHash($root.'/'.$path))) { throw new RuntimeException('M82 correction immutable 1.0.1 predecessor differs.'); }
        }
        $previous = json_decode(file_get_contents($root.'/'.Revision::PREVIOUS_SOURCE), true, flags: JSON_THROW_ON_ERROR);
        [$before, $active, $master, $contract] = Package::load($root); Package::validate($before, $active, $master, $contract);
        Revision::assertTransition($previous, $active, $master, $before, new TheoryNativeContentRelease(Package::profile()));
        foreach (Revision::TARGETS as $slot => $identity) {
            $id = $identity['id'];
            $this->previousBodies[$id] = $previous['targets'][1]['after']['page']['blocks'][$slot]['body'];
            $this->nextBodies[$id] = $active['targets'][1]['after']['page']['blocks'][$slot]['body'];
        }
    }

    /** Pinned independent post-1.0.1 evidence, not a writer or an ambient target choice. */
    protected function baselineEvidence(): array
    {
        $path = $this->privateDirectory.'/'.Revision::BASELINE;
        if (!hash_equals(Revision::BASELINE_SHA, self::fileHash($path))) { throw new RuntimeException('M82 correction independent 1.0.1 SQL baseline differs.'); }
        $data = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($data['all_old_rows_and_timestamps_exact'] ?? null) !== true || ($data['independent_before_projection_exact'] ?? null) !== true
            || ($data['reviewed_protected_projection_exact'] ?? null) !== true || ($data['db_writes'] ?? null) !== 0
            || ($data['http_requests'] ?? null) !== 0 || ($data['sql_tables'] ?? null) !== 46 || count($data['sql_after'] ?? []) !== 46
            || count($data['new_practices'] ?? []) !== 6 || ($data['new_question_families'] ?? null) !== []
            || ($data['approved_footer_url_additions'] ?? null) !== 8) {
            throw new RuntimeException('M82 correction baseline lacks exact six-owner/46-table applied evidence.');
        }
        if ($this->db->getDriverName() === 'mysql'
            && ($data['runtime'] ?? null) !== M49LocalTargetGuard::publicIdentity($this->db, dirname($this->databasePath).'/public')) {
            throw new RuntimeException('M82 correction baseline runtime differs from the proven local connection.');
        }
        return $data;
    }

    /** Raw SQL. Protect all row metadata/timestamps and every table except two body values. */
    protected function correctionFingerprints(bool $normalize = false, bool $protect = false): array
    {
        if ($normalize && count($this->previousBodies) !== 2) { throw new RuntimeException('M82 correction has no exact predecessor bodies.'); }
        $schema = $this->db->getDriverName() === 'sqlite' ? 'main' : $this->db->getDatabaseName();
        $tables = $this->db->getSchemaBuilder()->getTableListing(schema: $schema, schemaQualified: false); sort($tables); $out = [];
        foreach ($tables as $table) {
            $columns = $this->db->getSchemaBuilder()->getColumnListing($table); $query = $this->db->table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $hash = hash_init('sha256'); $count = 0;
            foreach ($query->cursor() as $record) {
                $row = (array) $record;
                if ($table === 'text_blocks' && isset($this->previousBodies[$row['id'] ?? null])) {
                    if ($protect) { unset($row['body']); } elseif ($normalize) { $row['body'] = $this->previousBodies[$row['id']]; }
                }
                hash_update($hash, self::digest($row)."\n"); ++$count;
            }
            $out[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
        }
        return $out;
    }

    protected function protectedFingerprints(array $targetIds): array { return $this->correctionFingerprints(protect: true); }

    /** Only parent validation sees AFTER copies; backup pages/relations are restored to raw SQL below. */
    protected function rows(string $table, callable $filter, bool $lock): array
    {
        $rows = parent::rows($table, $filter, $lock);
        if (!$this->normalizingForParent || $table !== 'text_blocks') { return $rows; }
        foreach ($rows as &$row) {
            foreach (Revision::TARGETS as $slot => $identity) {
                if (($row['id'] ?? null) !== $identity['id'] && ($row['uuid'] ?? null) !== $identity['uuid']) { continue; }
                $expected = ['id' => $identity['id'], 'uuid' => $identity['uuid'], 'page_id' => 40, 'page_category_id' => 9,
                    'seeder' => Package::OWNERS[1], 'locale' => 'uk', 'sort_order' => $slot + 1, 'type' => $identity['type'],
                    'column' => $identity['column'], 'heading' => null, 'css_class' => null, 'level' => $identity['level']];
                foreach ($expected as $field => $value) {
                    if (($row[$field] ?? null) !== $value) { throw new RuntimeException('M82 correction exact B identity/metadata differs: '.$field); }
                }
                $id = $identity['id'];
                if ($row['body'] !== $this->previousBodies[$id]) { continue; }
                $change = []; $state = parent::candidate($change, 'text_blocks', $row, Package::OWNERS[1],
                    ['body' => $this->previousBodies[$id]], ['body' => $this->nextBodies[$id]]);
                if ($state !== 'before' || count($change) !== 1) { throw new RuntimeException('M82 correction exact body transition differs.'); }
                if (isset($this->pending[$id]) && $this->pending[$id] !== $change[0]) { throw new RuntimeException('M82 correction repeated pending read differs.'); }
                $this->pending[$id] = $change[0]; $row['body'] = $this->nextBodies[$id];
            }
        }
        unset($row);
        return $rows;
    }

    public function plan(bool $lock = false): array
    {
        $this->pending = []; $this->normalizingForParent = false; $this->prepareTransition(); $baseline = $this->baselineEvidence();
        $this->normalizingForParent = true;
        try { $plan = parent::plan($lock); } finally { $this->normalizingForParent = false; }
        if ($plan['state'] !== 'after' || $plan['updates'] !== [] || $plan['inserts'] !== [] || !in_array(count($this->pending), [0, 2], true)
            || (count($this->pending) === 2 && array_diff([1673, 1675], array_keys($this->pending)) !== [])) {
            throw new RuntimeException('M82 correction requires six otherwise-applied owners and both old bodies or neither.');
        }
        if (($plan['sources'][Revision::PREVIOUS_SOURCE] ?? null) !== Revision::PREVIOUS_SOURCE_SHA
            || $this->correctionFingerprints(normalize: true) !== ($baseline['sql_after'] ?? null)) {
            throw new RuntimeException('M82 correction raw SQL changes outside two body values or predecessor source differs.');
        }
        foreach ($plan['pages'] as $owner => &$entry) {
            $pages = parent::rows('pages', static fn ($q) => $q->where('seeder', $owner), $lock);
            if (count($pages) !== 1) { throw new RuntimeException('M82 correction raw owner is ambiguous.'); }
            $entry['page'] = $pages[0];
            $entry['blocks'] = parent::rows('text_blocks', static fn ($q) => $q->where('page_id', $pages[0]['id']), $lock);
            $entry['relations'] = parent::relations($entry['page'], $entry['blocks'], $lock);
        }
        unset($entry);
        $plan['updates'] = $this->pending === [] ? [] : [$this->pending[1673], $this->pending[1675]];
        $plan['state'] = $this->pending === [] ? 'after' : 'before';
        if ($this->db->getDriverName() === 'mysql') { $plan['sources']['private:'.Revision::BASELINE] = self::fileHash($this->privateDirectory.'/'.Revision::BASELINE); }
        unset($plan['sha256']); ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }
}
