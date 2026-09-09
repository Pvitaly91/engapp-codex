<?php

namespace App\Services;

use App\Support\TextBlock\TextBlockUuidGenerator;
use Illuminate\Database\Connection;
use PDO;
use RuntimeException;

/** M8 only: in-place repair of two identified UK lessons; never invokes a seeder. */
class PronounContentRepair
{
    public const NAMES = ['OneOnes', 'ReciprocalPronouns'];
    public const PREFIX = 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstratives';
    private const ID = 'm8-pronoun-content-v1';
    private const FIELDS = ['type', 'heading', 'body'];

    public function __construct(private Connection $db, private string $databasePath, private string $privateDirectory) {}

    public static function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        $driver = $this->db->getDriverName();
        if ($driver === 'sqlite' && $this->db->getDatabaseName() === ':memory:'
            && $this->db->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            return ['driver' => 'sqlite', 'database' => ':memory:'];
        }
        if ($driver !== 'mysql' || !in_array($this->db->getConfig('host'), ['localhost', '127.0.0.1', '::1'], true)
            || $this->db->getConfig('read') || $this->db->getConfig('write')
            || $this->db->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
            throw new RuntimeException('Only single local MySQL or isolated SQLite memory is allowed.');
        }
        $actual = (array) $this->db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port');
        $status = $this->db->getPdo()->getAttribute(PDO::ATTR_CONNECTION_STATUS);
        if ($actual['db'] !== $this->db->getDatabaseName() || !preg_match('/^(localhost|127\.0\.0\.1|::1)\b/i', $status)
            || ($writing && ($expected === null || $expected !== $actual['db']))) {
            throw new RuntimeException('Actual connection/database differs; exact --database is required for writes.');
        }
        return ['driver' => $driver, 'database' => $actual['db'], 'server' => $actual['server'], 'port' => (int) $actual['port']];
    }

    public function plan(bool $lock = false): array
    {
        $plan = ['repair' => self::ID, 'version' => 1, 'connection' => $this->connection(), 'sources' => [], 'pages' => [], 'changes' => []];
        foreach (self::NAMES as $name) {
            $seeder = self::PREFIX.$name.'TheorySeeder';
            $relative = 'seeders/Page_V3/PronounsDemonstratives/PronounsDemonstratives'.$name.'TheorySeeder/definition.json';
            $raw = file_get_contents($this->databasePath.'/'.$relative);
            $source = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
            $config = $source['page'] ?? [];
            if (($source['seeder']['class'] ?? '') !== $seeder || ($source['type'] ?? '') !== 'theory'
                || ($config['locale'] ?? '') !== 'uk' || empty($config['subtitle_html'])) {
                throw new RuntimeException('Source identity/locale changed.');
            }
            $plan['sources'][$seeder] = ['path' => $relative, 'sha256' => hash('sha256', $raw)];
            $query = $this->db->table('pages')->where('seeder', $seeder);
            $pages = ($lock ? $query->lockForUpdate() : $query)->get();
            if ($pages->count() !== 1) { throw new RuntimeException('Missing or ambiguous target Page: '.$name); }
            $page = (array) $pages->first();
            $category = (array) $this->db->table('page_categories')->where('id', $page['page_category_id'])->first();
            if ($page['slug'] !== $source['slug'] || $page['title'] !== $config['title'] || $page['type'] !== 'theory'
                || ($category['slug'] ?? '') !== $config['category']['slug']) {
                throw new RuntimeException('Page/category no longer corresponds to the source: '.$name);
            }
            $query = $this->db->table('text_blocks')->where('page_id', $page['id'])->orderBy('id');
            $rows = ($lock ? $query->lockForUpdate() : $query)->get()->map(fn ($r) => (array) $r)->all();
            $ids = array_column($rows, 'id');
            $snapshot = ['page' => $page, 'category' => $category, 'blocks' => $rows];
            foreach (['tag_text_block' => ['text_block_id', $ids], 'page_tag' => ['page_id', [$page['id']]]] as $table => [$column, $values]) {
                $q = $this->db->table($table)->whereIn($column, $values)->orderBy('id');
                $snapshot[$table] = ($lock ? $q->lockForUpdate() : $q)->get()->map(fn ($r) => (array) $r)->all();
            }
            $plan['pages'][$seeder] = $snapshot;
            foreach ($config['blocks'] as $index => $block) {
                // Only legacy structures identified in these two sources. Subtitle and
                // established comparison/navigation tables are never candidates.
                if (!isset($block['layout'])) { continue; }
                if (isset($block['uuid']) || isset($block['uuid_key'])
                    || ($block['type'] ?? '') !== 'box' || !is_string($block['body'] ?? null) || trim($block['body']) === '') {
                    throw new RuntimeException('Source is not normalized or changes block identity: '.$name.' #'.($index + 1));
                }
                $uuid = TextBlockUuidGenerator::generate($seeder.'::uk', $index + 1);
                $matches = array_values(array_filter($rows, fn ($r) => $r['uuid'] === $uuid));
                if (count($matches) !== 1) { throw new RuntimeException('Missing/ambiguous source UUID '.$uuid); }
                $row = $matches[0];
                if ($row['locale'] !== 'uk' || $row['seeder'] !== $seeder || (int) $row['sort_order'] !== $index + 1
                    || $row['page_category_id'] != $page['page_category_id'] || $row['column'] !== ($block['column'] ?? 'left')
                    || $row['css_class'] !== ($block['css_class'] ?? null) || $row['level'] !== ($block['level'] ?? null)) {
                    throw new RuntimeException('Ownership/order/locale differs for '.$uuid);
                }
                // Stable field order across SQL drivers and JSON files.
                $before = array_combine(self::FIELDS, array_map(fn ($k) => $row[$k], self::FIELDS));
                $after = ['type' => $block['type'], 'heading' => $block['heading'] ?? null, 'body' => $block['body']];
                if ($before === $after) { continue; }
                if ($row['type'] !== 'box' || $row['heading'] !== null || trim($row['body'] ?? '') !== '') {
                    throw new RuntimeException('Nonempty/manual content is preserved: '.$uuid);
                }
                $plan['changes'][] = ['id' => $row['id'], 'uuid' => $uuid, 'seeder' => $seeder, 'sort_order' => $index + 1,
                    'before' => $before, 'after' => $after, 'before_sha256' => self::digest($before), 'after_sha256' => self::digest($after)];
            }
        }
        $plan['sha256'] = self::digest($plan);
        return $plan;
    }

    public function savePlan(string $path): array
    {
        $plan = $this->plan();
        $this->writeNew($path, $plan);
        return $plan;
    }

    public function apply(string $planPath, string $backupPath, ?string $expectedDatabase = null): array
    {
        $this->connection($expectedDatabase, true);
        $preview = $this->readPlan($planPath);
        return $this->db->transaction(function () use ($preview, $backupPath): array {
            $current = $this->plan(true);
            if ($this->project($preview) === $this->project($current) && !$current['changes']) {
                return ['status' => 'no-op', 'updated' => 0];
            }
            if ($preview !== $current) { throw new RuntimeException('Preview is stale/incomplete; source or database changed. No writes.'); }
            // Exclusive backup is closed successfully before the first update.
            $this->writeNew($backupPath, $preview);
            foreach ($preview['changes'] as $index => $change) {
                $this->update($change, false);
                $this->afterUpdate($index);
            }
            if ($this->project($this->plan(true)) !== $this->project($preview)) {
                throw new RuntimeException('Postcondition failed; transaction rolled back.');
            }
            return ['status' => 'applied', 'updated' => count($preview['changes']), 'backup' => $backupPath];
        });
    }

    /** Guarded restore is deliberately exposed only as a service for fixture tests. */
    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        $this->connection($expectedDatabase, true);
        $backup = $this->readPlan($backupPath);
        return $this->db->transaction(function () use ($backup): array {
            $current = $this->plan(true);
            if ($current === $backup) { return ['status' => 'no-op', 'updated' => 0]; }
            if ($this->project($backup) !== $this->project($current) || $current['changes']) {
                throw new RuntimeException('Incomplete/stale backup or post-repair edits; restore refused.');
            }
            // Reconstruct the entire proposed old snapshot and regenerate a canonical
            // plan in the transaction. Even a re-hashed, truncated backup is rejected.
            foreach ($backup['changes'] as $index => $change) {
                $this->update($change, true);
                $this->afterUpdate($index);
            }
            if ($this->plan(true) !== $backup) { throw new RuntimeException('Invalid backup; entire restore rolled back.'); }
            return ['status' => 'restored', 'updated' => count($backup['changes'])];
        });
    }

    protected function afterUpdate(int $index): void {}

    private function update(array $change, bool $reverse): void
    {
        $old = $change[$reverse ? 'after' : 'before'];
        $new = $change[$reverse ? 'before' : 'after'];
        if (array_keys($old) !== self::FIELDS || array_keys($new) !== self::FIELDS) { throw new RuntimeException('Disallowed update fields.'); }
        $q = $this->db->table('text_blocks')->where('id', $change['id'])->where('uuid', $change['uuid'])->where('seeder', $change['seeder'])->where('locale', 'uk');
        foreach ($old as $field => $value) { $q->where($field, $value); }
        if ($q->update($new) !== 1) { throw new RuntimeException('Concurrent update; transaction rolled back.'); }
    }

    private function project(array $plan): array
    {
        foreach ($plan['changes'] as $change) {
            foreach ($plan['pages'][$change['seeder']]['blocks'] as &$block) {
                if ($block['id'] === $change['id']) { $block = array_replace($block, $change['after']); }
            }
            unset($block);
        }
        $plan['changes'] = [];
        unset($plan['sha256']);
        return $plan;
    }

    private function readPlan(string $path): array
    {
        $this->assertPrivatePath($path);
        $plan = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $hash = $plan['sha256'] ?? null;
        unset($plan['sha256']);
        if (($plan['repair'] ?? '') !== self::ID || ($plan['version'] ?? 0) !== 1 || !is_string($hash)
            || !hash_equals($hash, self::digest($plan)) || !isset($plan['changes'], $plan['pages'], $plan['sources'])) {
            throw new RuntimeException('Corrupt/incomplete plan or backup.');
        }
        foreach ($plan['changes'] as $change) {
            if (($change['before_sha256'] ?? '') !== self::digest($change['before'] ?? [])
                || ($change['after_sha256'] ?? '') !== self::digest($change['after'] ?? [])) {
                throw new RuntimeException('Value hash mismatch.');
            }
        }
        $plan['sha256'] = $hash;
        return $plan;
    }

    private function assertPrivatePath(string $path): void
    {
        $root = realpath($this->privateDirectory);
        if (!$root || !stream_is_local($path) || realpath(dirname($path)) !== $root || is_link($path)
            || !preg_match('/^[a-zA-Z0-9_-]+\.json$/', basename($path))) {
            throw new RuntimeException('Plan/backup must be directly inside the designated private directory.');
        }
    }

    private function writeNew(string $path, array $data): void
    {
        $this->assertPrivatePath($path);
        $bytes = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        $file = @fopen($path, 'x');
        if (!$file) { throw new RuntimeException('Cannot create exclusive private plan/backup (existing evidence is never overwritten).'); }
        @chmod($path, 0600);
        try {
            if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) { throw new RuntimeException('Incomplete backup write; no DB updates allowed.'); }
        } finally { fclose($file); }
    }
}
