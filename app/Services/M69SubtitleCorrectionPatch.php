<?php

namespace App\Services;

use App\Support\M69AdjectivesAdverbs as Package;
use RuntimeException;

/** Two exact existing values only; unchanged M26 owns locks, preview equality, backup and transaction. */
class M69SubtitleCorrectionPatch extends M69ContentPatch
{
    public const PREVIOUS_SOURCE = 'database/content-patches/m69-adjectives-adverbs.v1.json';
    public const PREVIOUS_SOURCE_SHA = '105a09e3bd47749f8470d4cc965f27abc0785e20b7f87740b692835f805bd19a';
    public const BASELINE = 'final-readonly-v1.json';
    public const BASELINE_SHA = '89c7e301a2bd03b82e4eb448ff3c4a28edb3faba218f64502ce1c54e006b5d8e';
    public const TARGET_ID = 12755;
    public const TARGET_UUID = '2667ffc4-91d0-58f6-8fad-0e1ebc42d150';
    protected const ID = 'm69-subtitle-1.0.0-to-1.0.1';

    private array $pending = [];
    private bool $normalizingForParent = false;
    private ?string $previousBody = null;
    private ?string $previousText = null;
    private ?string $nextBody = null;
    private ?string $nextText = null;

    protected function expectedInsertCount(): int { return 0; }

    public static function sourcePaths(array $patch, array $contract, ?string $root = null): array
    {
        $paths = [...parent::sourcePaths($patch, $contract, $root), self::PREVIOUS_SOURCE,
            'app/Services/M69SubtitleCorrectionPatch.php'];
        $paths = array_values(array_unique($paths)); sort($paths); return $paths;
    }

    private function prepareTransition(): void
    {
        $root = dirname($this->databasePath); $path = $root.'/'.self::PREVIOUS_SOURCE;
        if (!hash_equals(self::PREVIOUS_SOURCE_SHA, self::fileHash($path))) {
            throw new RuntimeException('M69 correction historical 1.0.0 source differs.');
        }
        $bytes = file_get_contents($path);
        if (!is_string($bytes) || !hash_equals(self::PREVIOUS_SOURCE_SHA, hash('sha256', $bytes))) {
            throw new RuntimeException('M69 correction historical source changed while reading.');
        }
        $previous = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        [$before, $active, $master, $contract] = Package::load($root); Package::validate($before, $active, $master, $contract);
        if (($master['version'] ?? null) !== '1.0.1' || ($previous['schema_version'] ?? null) !== 1
            || ($previous['base_sha'] ?? null) !== Package::BASE_SHA
            || array_column($previous['targets'] ?? [], 'identity') !== Package::OWNERS
            || count($active['targets']) !== 3) { throw new RuntimeException('M69 correction exact historical/active scope differs.'); }
        foreach ($active['targets'] as $i => $target) {
            $old = $previous['targets'][$i]; $copy = $target;
            if ($i === 2) {
                $this->previousBody = $old['after']['page']['subtitle_html']; $this->previousText = $old['after']['page']['subtitle_text'];
                $this->nextBody = $target['after']['page']['subtitle_html']; $this->nextText = $target['after']['page']['subtitle_text'];
                foreach (['subtitle_html', 'subtitle_text'] as $field) { $copy['after']['page'][$field] = $old['after']['page'][$field]; }
            }
            if ($copy !== $old) { throw new RuntimeException('M69 correction changes content outside two exact subtitle fields.'); }
        }
        foreach ([[$this->previousBody, $this->nextBody], [$this->previousText, $this->nextText]] as [$old, $new]) {
            if (!is_string($old) || !is_string($new) || $old === '' || $new === '' || $old === $new) {
                throw new RuntimeException('M69 correction requires two distinct nonempty reviewed old/new values.');
            }
        }
    }

    /** The independent post-1.0.0 SQL receipt is evidence, not an instruction or a writer. */
    protected function baselineEvidence(): array
    {
        $path = $this->privateDirectory.'/'.self::BASELINE;
        if (!hash_equals(self::BASELINE_SHA, self::fileHash($path))) {
            throw new RuntimeException('M69 correction independent 1.0.0 SQL baseline differs.');
        }
        $data = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($data['all_old_rows_and_timestamps_exact'] ?? null) !== true
            || ($data['independent_before_projection_exact'] ?? null) !== true
            || ($data['reviewed_protected_projection_exact'] ?? null) !== true
            || ($data['db_writes'] ?? null) !== 0 || ($data['http_requests'] ?? null) !== 0
            || ($data['sql_tables'] ?? null) !== 46 || count($data['sql_after'] ?? []) !== 46
            || ($data['new_subtitles'] ?? null) !== [['owner' => Package::OWNERS[2], 'id' => self::TARGET_ID, 'uuid' => self::TARGET_UUID]]
            || count($data['new_practices'] ?? []) !== 3 || ($data['new_question_families'] ?? null) !== []) {
            throw new RuntimeException('M69 correction baseline lacks independent exact 46-table 1.0.0 evidence.');
        }
        if ($this->db->getDriverName() === 'mysql'
            && ($data['runtime'] ?? null) !== M49LocalTargetGuard::publicIdentity($this->db, dirname($this->databasePath).'/public')) {
            throw new RuntimeException('M69 correction baseline runtime differs from the proven local connection.');
        }
        return $data;
    }

    /** Raw SQL, never the parent-validation copies. Only the two reviewed fields may be normalized/excluded. */
    protected function correctionFingerprints(bool $normalize = false, bool $protect = false): array
    {
        if ($normalize && ($this->previousBody === null || $this->previousText === null)) {
            throw new RuntimeException('M69 correction normalization has no frozen historical values.');
        }
        $schema = $this->db->getDriverName() === 'sqlite' ? 'main' : $this->db->getDatabaseName();
        $tables = $this->db->getSchemaBuilder()->getTableListing(schema: $schema, schemaQualified: false); sort($tables); $out = [];
        foreach ($tables as $table) {
            $columns = $this->db->getSchemaBuilder()->getColumnListing($table); $query = $this->db->table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $hash = hash_init('sha256'); $count = 0;
            foreach ($query->cursor() as $record) {
                $row = (array) $record;
                if ($table === 'pages' && ($row['id'] ?? null) === 275) {
                    if ($protect) { unset($row['text']); } elseif ($normalize) { $row['text'] = $this->previousText; }
                }
                if ($table === 'text_blocks' && ($row['id'] ?? null) === self::TARGET_ID) {
                    if ($protect) { unset($row['body']); } elseif ($normalize) { $row['body'] = $this->previousBody; }
                }
                hash_update($hash, self::digest($row)."\n"); ++$count;
            }
            $out[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
        }
        return $out;
    }

    protected function protectedFingerprints(array $targetIds): array { return $this->correctionFingerprints(protect: true); }

    private function collect(string $key, array $change): void
    {
        if (isset($this->pending[$key]) && $this->pending[$key] !== $change) {
            throw new RuntimeException('M69 correction repeated read produced a different pending value.');
        }
        $this->pending[$key] = $change;
    }

    protected function candidate(array &$updates, string $table, array $row, string $seeder, array $before, array $after): ?string
    {
        if (!$this->normalizingForParent || $table !== 'pages' || ($row['id'] ?? null) !== 275) {
            return parent::candidate($updates, $table, $row, $seeder, $before, $after);
        }
        if ($seeder !== Package::OWNERS[2] || ($row['seeder'] ?? null) !== $seeder
            || ($row['page_category_id'] ?? null) !== 52 || ($row['slug'] ?? null) !== 'ed-ing-adjectives'
            || ($row['title'] ?? null) !== '-ed / -ing Adjectives' || ($row['type'] ?? null) !== 'theory'
            || array_keys($before) !== ['text'] || $after !== ['text' => $this->nextText]) {
            throw new RuntimeException('M69 correction exact Page/text target differs.');
        }
        if (($row['text'] ?? null) !== $this->previousText) { return parent::candidate($updates, $table, $row, $seeder, $before, $after); }
        $change = []; $state = parent::candidate($change, $table, $row, $seeder, ['text' => $this->previousText], $after);
        if ($state !== 'before' || count($change) !== 1) { throw new RuntimeException('M69 correction exact Page transition differs.'); }
        $this->collect('pages:275', $change[0]);
        return 'after';
    }

    /** Parent validation alone sees an exact AFTER copy of the one newly inserted 1.0.0 subtitle. */
    protected function rows(string $table, callable $filter, bool $lock): array
    {
        $rows = parent::rows($table, $filter, $lock);
        if (!$this->normalizingForParent || $table !== 'text_blocks') { return $rows; }
        foreach ($rows as &$row) {
            if (($row['id'] ?? null) !== self::TARGET_ID && ($row['uuid'] ?? null) !== self::TARGET_UUID) { continue; }
            $expected = ['id' => self::TARGET_ID, 'uuid' => self::TARGET_UUID, 'page_id' => 275, 'page_category_id' => 52,
                'seeder' => Package::OWNERS[2], 'locale' => 'uk', 'sort_order' => 0, 'type' => 'subtitle',
                'column' => 'header', 'heading' => null, 'css_class' => null, 'level' => null];
            foreach ($expected as $field => $value) {
                if (($row[$field] ?? null) !== $value) { throw new RuntimeException('M69 correction subtitle exact metadata differs: '.$field); }
            }
            if ($row['body'] === $this->previousBody) {
                $change = []; $state = parent::candidate($change, 'text_blocks', $row, Package::OWNERS[2],
                    ['body' => $this->previousBody], ['body' => $this->nextBody]);
                if ($state !== 'before' || count($change) !== 1) { throw new RuntimeException('M69 correction exact body transition differs.'); }
                $this->collect('text_blocks:'.self::TARGET_ID, $change[0]);
                $row['body'] = $this->nextBody;
            }
        }
        unset($row);
        return $rows;
    }

    public function plan(bool $lock = false): array
    {
        $this->pending = []; $this->normalizingForParent = false; $this->prepareTransition();
        $baseline = $this->baselineEvidence();
        $this->normalizingForParent = true;
        try { $plan = parent::plan($lock); }
        finally { $this->normalizingForParent = false; }
        if ($plan['state'] !== 'after' || $plan['updates'] !== [] || $plan['inserts'] !== []
            || !in_array(count($this->pending), [0, 2], true)
            || (count($this->pending) === 2 && array_diff(['pages:275', 'text_blocks:'.self::TARGET_ID], array_keys($this->pending)) !== [])) {
            throw new RuntimeException('M69 correction requires all three otherwise-applied owners and both old values or neither.');
        }
        if (($plan['sources'][self::PREVIOUS_SOURCE] ?? null) !== self::PREVIOUS_SOURCE_SHA
            || $this->correctionFingerprints(normalize: true) !== ($baseline['sql_after'] ?? null)) {
            throw new RuntimeException('M69 correction changes SQL outside two values or the historical source drifted.');
        }
        foreach ($plan['pages'] as $owner => &$entry) {
            $pages = parent::rows('pages', static fn ($q) => $q->where('seeder', $owner), $lock);
            if (count($pages) !== 1) { throw new RuntimeException('M69 correction raw owner became ambiguous.'); }
            $entry['page'] = $pages[0];
            $entry['blocks'] = parent::rows('text_blocks', static fn ($q) => $q->where('page_id', $pages[0]['id']), $lock);
            $entry['relations'] = parent::relations($entry['page'], $entry['blocks'], $lock);
        }
        unset($entry);
        $plan['updates'] = $this->pending === [] ? [] : [$this->pending['pages:275'], $this->pending['text_blocks:'.self::TARGET_ID]];
        $plan['state'] = $this->pending === [] ? 'after' : 'before';
        if ($this->db->getDriverName() === 'mysql') { $plan['sources']['private:'.self::BASELINE] = self::fileHash($this->privateDirectory.'/'.self::BASELINE); }
        unset($plan['sha256']); ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }
}
