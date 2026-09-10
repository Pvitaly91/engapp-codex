<?php

namespace App\Services;

use App\Support\TextBlock\TextBlockUuidGenerator;
use Illuminate\Database\Connection;
use RuntimeException;

/** Versioned M8.1 transition, not an editor or a relaxation of M8's empty-body guard. */
class OneOnesEditorialPatch
{
    public const ID = 'm8-1-one-ones-editorial-v1';
    public const SEEDER = PronounContentRepair::PREFIX.'OneOnesTheorySeeder';
    public const MANIFEST = 'content-patches/one-ones-m8-1.json';
    private const ORDERS = [2, 5, 7, 8, 9];
    private const BEFORE_SOURCE = '6916cab50b8bf363353d9211523238045461bf027903b95eff5ac2fd7fde708d';
    private const AFTER_SOURCE = '261e18e763e8ab9b7ce449fbbfda9ee4826e3262a2dbe7320c49c9f51eae2897';

    public function __construct(private Connection $db, private string $databasePath, private string $privateDirectory) {}

    public function connection(?string $expected = null, bool $writing = false): array
    {
        return (new PronounContentRepair($this->db, $this->databasePath, $this->privateDirectory))->connection($expected, $writing);
    }

    public function plan(bool $lock = false): array
    {
        $connection = $this->connection();
        $manifestRaw = file_get_contents($this->databasePath.'/'.self::MANIFEST);
        $manifest = json_decode($manifestRaw, true, flags: JSON_THROW_ON_ERROR);
        $relative = 'seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesOneOnesTheorySeeder/definition.json';
        if (($manifest['patch'] ?? '') !== self::ID || ($manifest['source'] ?? '') !== $relative
            || array_column($manifest['blocks'] ?? [], 'sort_order') !== self::ORDERS) {
            throw new RuntimeException('Unknown editorial manifest/allowlist.');
        }
        $raw = file_get_contents($this->databasePath.'/'.$relative);
        $source = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        if (PronounContentRepair::digest($source) !== self::AFTER_SOURCE) {
            throw new RuntimeException('Source differs from the approved M8.1 edition.');
        }
        $historical = $source;
        foreach ($manifest['blocks'] as $entry) {
            $i = $entry['sort_order'] - 1;
            $block = $source['page']['blocks'][$i];
            if ($entry['uuid'] !== TextBlockUuidGenerator::generate(self::SEEDER.'::uk', $i + 1)
                || !is_string($entry['before_body'] ?? null) || trim($entry['before_body']) === ''
                || $entry['before_body'] === $entry['after_body']
                || $entry['after_body'] !== $block['body'] || $block['body'] !== $block['content']['html']) {
                throw new RuntimeException('Manifest identity/body differs from the approved source.');
            }
            $historical['page']['blocks'][$i]['body'] = $entry['before_body'];
            $historical['page']['blocks'][$i]['content']['html'] = $entry['before_body'];
        }
        if (PronounContentRepair::digest($historical) !== self::BEFORE_SOURCE) {
            throw new RuntimeException('Historical bodies are not the accepted M8 text.');
        }
        $q = $this->db->table('pages')->where('seeder', self::SEEDER);
        $pages = ($lock ? $q->lockForUpdate() : $q)->get();
        if ($pages->count() !== 1) { throw new RuntimeException('Missing or ambiguous One/Ones Page.'); }
        $page = (array) $pages->first();
        $q = $this->db->table('page_categories')->where('id', $page['page_category_id']);
        $category = (array) ($lock ? $q->lockForUpdate() : $q)->first();
        if ($page['slug'] !== $source['slug'] || $page['title'] !== $source['page']['title'] || $page['type'] !== 'theory'
            || ($category['slug'] ?? '') !== $source['page']['category']['slug']) {
            throw new RuntimeException('Page/category identity differs from One/Ones.');
        }
        $q = $this->db->table('text_blocks')->where('page_id', $page['id'])->orderBy('id');
        $rows = ($lock ? $q->lockForUpdate() : $q)->get()->map(fn ($r) => (array) $r)->all();
        $snapshot = ['page' => $page, 'category' => $category, 'blocks' => $rows];
        foreach (['tag_text_block' => ['text_block_id', array_column($rows, 'id')], 'page_tag' => ['page_id', [$page['id']]]] as $table => [$column, $ids]) {
            $q = $this->db->table($table)->whereIn($column, $ids)->orderBy('id');
            $snapshot[$table] = ($lock ? $q->lockForUpdate() : $q)->get()->map(fn ($r) => (array) $r)->all();
        }
        $plan = ['patch' => self::ID, 'version' => 1, 'connection' => $connection,
            'sources' => [$relative => hash('sha256', $raw), self::MANIFEST => hash('sha256', $manifestRaw)],
            'snapshot' => $snapshot, 'changes' => []];
        foreach ($manifest['blocks'] as $entry) {
            $matches = array_values(array_filter($rows, fn ($r) => $r['uuid'] === $entry['uuid']));
            $sameOrder = array_filter($rows, fn ($r) => $r['locale'] === 'uk' && (int) $r['sort_order'] === $entry['sort_order']);
            if (count($matches) !== 1 || count($sameOrder) !== 1) { throw new RuntimeException('Missing/ambiguous target UUID/order.'); }
            $row = $matches[0];
            $block = $source['page']['blocks'][$entry['sort_order'] - 1];
            if ($row['locale'] !== 'uk' || $row['seeder'] !== self::SEEDER || (int) $row['sort_order'] !== $entry['sort_order']
                || $row['page_category_id'] != $page['page_category_id'] || $row['column'] !== ($block['column'] ?? 'left')
                || $row['css_class'] !== ($block['css_class'] ?? null) || $row['level'] !== ($block['level'] ?? null)
                || $row['type'] !== $block['type'] || $row['heading'] !== $block['heading']) {
                throw new RuntimeException('Ownership/locale/order/non-body fields differ: '.$entry['uuid']);
            }
            if ($row['body'] === $entry['after_body']) { continue; }
            if ($row['body'] !== $entry['before_body']) { throw new RuntimeException('Manual/unknown body preserved: '.$entry['uuid']); }
            $plan['changes'][] = ['id' => $row['id'], 'uuid' => $row['uuid'], 'seeder' => self::SEEDER, 'sort_order' => $entry['sort_order'],
                'before' => ['body' => $row['body']], 'after' => ['body' => $entry['after_body']]];
        }
        $plan['sha256'] = PronounContentRepair::digest($plan);
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
        $this->assertPrivatePath($planPath);
        $preview = json_decode(file_get_contents($planPath), true, flags: JSON_THROW_ON_ERROR);
        $hash = $preview['sha256'] ?? null;
        unset($preview['sha256']);
        if (($preview['patch'] ?? '') !== self::ID || ($preview['version'] ?? null) !== 1
            || !is_string($hash) || !hash_equals($hash, PronounContentRepair::digest($preview))
            || !isset($preview['changes'], $preview['snapshot'], $preview['sources'])) {
            throw new RuntimeException('Corrupt/incomplete M8.1 preview.');
        }
        $preview['sha256'] = $hash;
        return $this->db->transaction(function () use ($preview, $backupPath): array {
            $current = $this->plan(true);
            if (!$current['changes'] && $this->project($current) === $this->project($preview)) {
                return ['status' => 'no-op', 'updated' => 0];
            }
            if ($preview !== $current) { throw new RuntimeException('Preview is stale/incomplete. No writes.'); }
            $this->writeNew($backupPath, $preview); // Closed exclusive backup before first UPDATE.
            foreach ($current['changes'] as $i => $change) {
                if (array_keys($change['before']) !== ['body'] || array_keys($change['after']) !== ['body']) {
                    throw new RuntimeException('Only body updates are permitted.');
                }
                $count = $this->db->table('text_blocks')->where('id', $change['id'])->where('uuid', $change['uuid'])
                    ->where('page_id', $current['snapshot']['page']['id'])->where('seeder', self::SEEDER)->where('locale', 'uk')
                    ->where('sort_order', $change['sort_order'])->where('body', $change['before']['body'])->update($change['after']);
                if ($count !== 1) { throw new RuntimeException('Concurrent body edit; transaction rolled back.'); }
                $this->afterUpdate($i);
            }
            if ($this->project($this->plan(true)) !== $this->project($current)) {
                throw new RuntimeException('Postcondition failed; transaction rolled back.');
            }
            return ['status' => 'applied', 'updated' => count($current['changes']), 'backup' => $backupPath];
        });
    }

    protected function afterUpdate(int $index): void {}

    private function project(array $plan): array
    {
        foreach ($plan['changes'] as $change) {
            foreach ($plan['snapshot']['blocks'] as &$block) {
                if ($block['id'] === $change['id']) { $block = array_replace($block, $change['after']); }
            }
            unset($block);
        }
        $plan['changes'] = [];
        unset($plan['sha256']);
        return $plan;
    }

    private function assertPrivatePath(string $path): void
    {
        $root = realpath($this->privateDirectory);
        if (!$root || !stream_is_local($path) || realpath(dirname($path)) !== $root || is_link($path)
            || !preg_match('/^[a-zA-Z0-9_-]+\.json$/', basename($path))) {
            throw new RuntimeException('Plan/backup must be inside the M8.1 private directory.');
        }
    }

    private function writeNew(string $path, array $data): void
    {
        $this->assertPrivatePath($path);
        $bytes = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        $file = @fopen($path, 'x');
        if (!$file) { throw new RuntimeException('Cannot create exclusive file; existing evidence is never overwritten.'); }
        @chmod($path, 0600);
        try {
            if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) { throw new RuntimeException('Incomplete backup write.'); }
        } finally { fclose($file); }
    }
}
