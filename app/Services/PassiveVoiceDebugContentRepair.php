<?php

namespace App\Services;

use Illuminate\Database\Connection;
use RuntimeException;

/** Repairs only the three identified fixture rows from the September 2026 audit. */
class PassiveVoiceDebugContentRepair
{
    public const SLUG = 'theory-passive-voice-formation-rules';
    public const DEBUG_TITLE = 'Page Folder Unseed Targets Debug';
    public const SEEDER = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormationRulesTheorySeeder';
    public const DEBUG_SEEDER = 'Database\\Seeders\\Page_V3\\PageFolderUnseedTargetsCategory\\PageFolderUnseedTargetsDebugTheorySeeder';
    public const DEFINITION = 'seeders/Page_V3/PassiveVoice/Basics/PassiveVoiceFormationRulesTheorySeeder/definition.json';
    private const REPAIR = 'passive-voice-debug-content-20260908';

    public function __construct(private Connection $db, private string $definitionPath)
    {
    }

    public static function blockSignatures(): array
    {
        return [
            '00506d7e-2974-534a-beff-f1e2b90ef704' => [
                'locale' => 'uk', 'type' => 'subtitle', 'column' => 'header', 'sort_order' => 0,
                'seeder' => self::DEBUG_SEEDER,
                'body' => '<p><strong>Page Folder Unseed Targets Debug</strong></p>',
            ],
            'fe0884c8-c21a-55c9-aaf8-1786d7164550' => [
                'locale' => 'uk', 'type' => 'box', 'column' => 'left', 'sort_order' => 1,
                'seeder' => self::DEBUG_SEEDER, 'body' => '<p>Page_V3 folder unseed block.</p>',
            ],
            '625d8416-a3ff-51ee-8219-1ae6901c3a21' => [
                'locale' => 'en', 'type' => 'box', 'column' => 'left', 'sort_order' => 1,
                'seeder' => 'Database\\Seeders\\Page_V3\\Localizations\\En\\PageFolderUnseedTargetsDebugTheoryLocalizationSeeder',
                'body' => '<p>Localized Page_V3 folder unseed block.</p>',
            ],
        ];
    }

    public function assertLocalConnection(?string $expectedDatabase = null, bool $writing = false): void
    {
        $driver = $this->db->getDriverName();
        if ($driver === 'sqlite' && $this->db->getDatabaseName() === ':memory:') {
            return;
        }
        if ($driver !== 'mysql' || ! in_array($this->db->getConfig('host'), ['localhost', '127.0.0.1', '::1'], true)
            || $this->db->getConfig('read') || $this->db->getConfig('write')) {
            throw new RuntimeException('Repair is restricted to an isolated SQLite memory database or a single local MySQL connection.');
        }
        if ($writing && ($expectedDatabase === null || $expectedDatabase !== $this->db->getDatabaseName())) {
            throw new RuntimeException('An exact --database value matching the inspected local database is required for --apply.');
        }
    }

    public function plan(bool $lock = false): array
    {
        $this->assertLocalConnection();
        $definition = json_decode(file_get_contents($this->definitionPath), true, 512, JSON_THROW_ON_ERROR);
        if (($definition['slug'] ?? null) !== self::SLUG || ($definition['seeder']['class'] ?? null) !== self::SEEDER
            || empty($definition['page']['title']) || str_contains(json_encode($definition), 'Page Folder Unseed')) {
            throw new RuntimeException('Canonical Passive Voice definition did not pass identity/content checks.');
        }
        $query = $this->db->table('pages')->where('slug', self::SLUG)->where('type', 'theory');
        $pages = ($lock ? $query->lockForUpdate() : $query)->get();
        $plan = ['repair' => self::REPAIR, 'database' => $this->db->getDatabaseName(), 'slug' => self::SLUG,
            'canonical_title' => $definition['page']['title'], 'page' => null, 'page_changes' => [],
            'text_blocks' => [], 'tag_links' => [], 'conflicts' => [], 'status' => 'not_present'];
        if ($pages->isEmpty()) {
            return $plan;
        }
        if ($pages->count() !== 1) {
            $plan['conflicts'][] = 'The scoped theory slug does not identify exactly one page.';
            return $plan;
        }
        $page = (array) $pages->first();
        $plan['page'] = $page;
        $category = $this->db->table('page_categories')->where('id', $page['page_category_id'])->value('slug');
        if ($category !== 'passive-voice') {
            $plan['conflicts'][] = 'The page no longer belongs to the expected Passive Voice category.';
        }
        if ($page['title'] === self::DEBUG_TITLE) {
            $plan['page_changes']['title'] = ['before' => $page['title'], 'after' => $definition['page']['title']];
        } elseif ($page['title'] !== $definition['page']['title']) {
            $plan['conflicts'][] = 'The page title differs from both the known fixture title and the canonical title; manual content is preserved.';
        }
        if ($page['seeder'] === self::DEBUG_SEEDER) {
            $plan['page_changes']['seeder'] = ['before' => $page['seeder'], 'after' => self::SEEDER];
        } elseif ($page['seeder'] !== self::SEEDER) {
            $plan['conflicts'][] = 'The page source differs from the known sources; manual content is preserved.';
        }
        if (! $this->db->table('text_blocks')->where('page_id', $page['id'])->where('seeder', self::SEEDER)
            ->whereNotIn('uuid', array_keys(self::blockSignatures()))->whereNotNull('body')->where('body', '<>', '')->exists()) {
            $plan['conflicts'][] = 'No canonical lesson blocks remain; this repair must not turn a diagnostic-only record into an empty public lesson.';
        }
        $dependentKeys = $this->dependentKeys();
        foreach (self::blockSignatures() as $uuid => $expected) {
            $query = $this->db->table('text_blocks')->where('uuid', $uuid);
            $row = ($lock ? $query->lockForUpdate() : $query)->first();
            if ($row === null) {
                continue;
            }
            $row = (array) $row;
            if (! $this->matches($row, $expected + ['page_id' => $page['id'], 'page_category_id' => $page['page_category_id'], 'heading' => null, 'css_class' => null, 'level' => null])) {
                $plan['conflicts'][] = "Block {$uuid} differs from the identified fixture; manual content is preserved.";
                continue;
            }
            if ($this->db->getSchemaBuilder()->hasColumn('questions', 'theory_text_block_uuid')
                && $this->db->table('questions')->where('theory_text_block_uuid', $uuid)->exists()) {
                $plan['conflicts'][] = "Block {$uuid} is referenced by a learning question; no records will be removed.";
                continue;
            }
            if ($this->db->getSchemaBuilder()->hasColumn('question_theory_text_blocks', 'text_block_uuid')
                && $this->db->table('question_theory_text_blocks')->where('text_block_uuid', $uuid)->exists()) {
                $plan['conflicts'][] = "Block {$uuid} is referenced by the question/theory junction; no records will be removed.";
                continue;
            }
            $referenced = false;
            foreach ($dependentKeys as $key) {
                $query = $this->db->table($key['table']);
                foreach ($key['columns'] as $index => $column) {
                    $query->where($column, $row[$key['foreign_columns'][$index]]);
                }
                if ($query->exists()) {
                    $plan['conflicts'][] = "Block {$uuid} has a dependent row in {$key['table']}; no records will be removed.";
                    $referenced = true;
                }
            }
            if ($referenced) {
                continue;
            }
            $plan['text_blocks'][] = $row;
        }
        $ids = array_column($plan['text_blocks'], 'id');
        if ($ids && $this->db->getSchemaBuilder()->hasTable('tag_text_block')) {
            $query = $this->db->table('tag_text_block')->whereIn('text_block_id', $ids)->orderBy('id');
            $plan['tag_links'] = ($lock ? $query->lockForUpdate() : $query)->get()->map(fn ($row) => (array) $row)->all();
        }
        $plan['status'] = $plan['conflicts'] ? 'conflict' : (($plan['text_blocks'] || $plan['page_changes']) ? 'repairable' : 'clean');

        return $plan;
    }

    public function apply(string $backupPath, ?string $expectedDatabase = null): array
    {
        $this->assertLocalConnection($expectedDatabase, true);

        return $this->db->transaction(function () use ($backupPath): array {
            $plan = $this->plan(true);
            $this->assertNoConflicts($plan);
            if ($plan['status'] !== 'repairable') {
                return $plan + ['applied' => false];
            }
            $backup = $plan + ['format_version' => 1, 'created_at_utc' => gmdate('c')];
            $this->writeNewBackup($backupPath, $backup);
            foreach ($plan['page_changes'] as $field => $change) {
                $affected = $this->db->table('pages')->where('id', $plan['page']['id'])->where($field, $change['before'])->update([$field => $change['after']]);
                if ($affected !== 1) {
                    throw new RuntimeException('Page changed during repair; transaction rolled back.');
                }
            }
            $ids = array_column($plan['text_blocks'], 'id');
            if ($ids) {
                if ($plan['tag_links']) {
                    $this->db->table('tag_text_block')->whereIn('id', array_column($plan['tag_links'], 'id'))->delete();
                }
                $this->db->table('text_blocks')->whereIn('id', $ids)->delete();
            }

            return $plan + ['applied' => true, 'backup' => $backupPath];
        });
    }

    public function restore(string $backupPath, bool $apply = false, ?string $expectedDatabase = null): array
    {
        $this->assertLocalConnection($expectedDatabase, $apply);
        if (! stream_is_local($backupPath) || ! is_file($backupPath)) {
            throw new RuntimeException('Restoration requires an existing local record-backup file.');
        }
        $backup = json_decode(file_get_contents($backupPath), true, 512, JSON_THROW_ON_ERROR);
        if (($backup['repair'] ?? null) !== self::REPAIR || ($backup['format_version'] ?? null) !== 1
            || ($backup['database'] ?? null) !== $this->db->getDatabaseName() || ($backup['slug'] ?? null) !== self::SLUG) {
            throw new RuntimeException('Backup identity/database does not match this repair.');
        }
        $definition = json_decode(file_get_contents($this->definitionPath), true, 512, JSON_THROW_ON_ERROR);
        $allowedChanges = [
            'title' => ['before' => self::DEBUG_TITLE, 'after' => $definition['page']['title']],
            'seeder' => ['before' => self::DEBUG_SEEDER, 'after' => self::SEEDER],
        ];
        foreach ($backup['page_changes'] as $field => $change) {
            if (! isset($allowedChanges[$field]) || ! $this->matches($change, $allowedChanges[$field])) {
                throw new RuntimeException('Backup contains page changes outside the identified repair.');
            }
        }

        return $this->db->transaction(function () use ($backup, $apply): array {
            $page = (array) $this->db->table('pages')->where('id', $backup['page']['id'])->lockForUpdate()->first();
            if (($page['slug'] ?? null) !== self::SLUG || ($page['type'] ?? null) !== 'theory') {
                throw new RuntimeException('Original page identity is no longer available.');
            }
            $changes = [];
            foreach ($backup['page_changes'] as $field => $change) {
                if (! in_array($field, ['title', 'seeder'], true) || ! in_array($page[$field], [$change['before'], $change['after']], true)) {
                    throw new RuntimeException('Page was edited after repair; restoration refused.');
                }
                if ($page[$field] === $change['after']) {
                    $changes[$field] = $change['before'];
                }
            }
            $insert = ['text_blocks' => [], 'tag_text_block' => []];
            foreach ($backup['text_blocks'] as $row) {
                $signature = self::blockSignatures()[$row['uuid']] ?? null;
                if (! $signature || ! $this->matches($row, $signature + ['page_id' => $page['id']])) {
                    throw new RuntimeException('Backup contains a block outside the identified repair scope.');
                }
                $existing = $this->db->table('text_blocks')->where('id', $row['id'])->orWhere('uuid', $row['uuid'])->lockForUpdate()->first();
                if ($existing && ! $this->matches((array) $existing, $row)) {
                    throw new RuntimeException('Block ID/UUID was reused or edited; restoration refused.');
                }
                if (! $existing) {
                    $insert['text_blocks'][] = $row;
                }
            }
            foreach ($backup['tag_links'] as $row) {
                if (! in_array($row['text_block_id'], array_column($backup['text_blocks'], 'id'), true)) {
                    throw new RuntimeException('Backup contains an unrelated tag link.');
                }
                $existing = $this->db->table('tag_text_block')->where('id', $row['id'])->lockForUpdate()->first();
                if ($existing && ! $this->matches((array) $existing, $row)) {
                    throw new RuntimeException('Tag-link ID was reused; restoration refused.');
                }
                if (! $existing) {
                    $insert['tag_text_block'][] = $row;
                }
            }
            if ($apply) {
                if ($changes) {
                    $this->db->table('pages')->where('id', $page['id'])->update($changes);
                }
                foreach ($insert as $table => $rows) {
                    if ($rows) {
                        $this->db->table($table)->insert($rows);
                    }
                }
            }

            return ['repair' => self::REPAIR, 'restored' => $apply, 'page_changes' => array_keys($changes),
                'text_blocks' => count($insert['text_blocks']), 'tag_links' => count($insert['tag_text_block'])];
        });
    }

    private function matches(array $actual, array $expected): bool
    {
        foreach ($expected as $field => $value) {
            if (! array_key_exists($field, $actual) || ($value === null ? $actual[$field] !== null : (string) $actual[$field] !== (string) $value)) {
                return false;
            }
        }

        return true;
    }

    private function dependentKeys(): array
    {
        $keys = [];
        $schema = $this->db->getSchemaBuilder();
        foreach ($schema->getTables() as $table) {
            foreach ($schema->getForeignKeys($table['name']) as $key) {
                if ($key['foreign_table'] !== 'text_blocks') {
                    continue;
                }
                if ($table['name'] === 'tag_text_block' && $key['columns'] === ['text_block_id'] && $key['foreign_columns'] === ['id']) {
                    continue; // These exact tag links are backed up and removed explicitly.
                }
                $keys[] = $key + ['table' => $table['name']];
            }
        }

        return $keys;
    }

    private function assertNoConflicts(array $plan): void
    {
        if ($plan['conflicts']) {
            throw new RuntimeException(implode(' ', $plan['conflicts']));
        }
    }

    private function writeNewBackup(string $path, array $backup): void
    {
        if (! stream_is_local($path)) {
            throw new RuntimeException('The record backup must be a local file.');
        }
        $payload = json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
        $handle = @fopen($path, 'x');
        if ($handle === false) {
            throw new RuntimeException('Cannot create a new backup file; no database changes were made.');
        }
        try {
            @chmod($path, 0600);
            if (fwrite($handle, $payload) !== strlen($payload) || ! fflush($handle)) {
                throw new RuntimeException('Backup could not be fully written; no database changes were made.');
            }
        } finally {
            fclose($handle);
        }
    }
}
