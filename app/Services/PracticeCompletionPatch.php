<?php

namespace App\Services;

use App\Support\M26DetailPackage;
use App\Support\PracticeCompletionPackage as Package;
use RuntimeException;
use Symfony\Component\Process\Process;

/** Twelve existing practice bodies only; M26 supplies exclusive backup, transaction and postconditions. */
class PracticeCompletionPatch extends M26ContentPatch
{
    public const NAMES = Package::OWNERS;
    public const ACCEPTED_BASE = Package::BASE_SHA;
    public const BEFORE_FILE = 'before-v3.json';
    public const BEFORE_SHA = 'c3e045006579df0193be1bafa5ad6e54e5a4d6828b4d333598d62714d375c1d6';
    public const FROZEN_FILE = 'frozen-before-v2.json';
    public const FROZEN_SHA = '8a30c976ed5d214dcf122724ad8e4a1f6e2c3703c742a909782b210b79075c87';
    public const SYNC_RECEIPT = 'source-sync-v4.json';
    public const VISUAL_DIRECTORIES = ['resources/views', 'resources/css', 'resources/js', 'public/js'];
    public const SYNC_CODE_PATHS = [
        'app/Support/PracticeCompletionPackage.php',
        'app/Services/PracticeCompletionLocalTargetGuard.php',
        'app/Services/PracticeCompletionPatch.php',
        'app/Console/Commands/PatchPracticeCompletion.php',
        'app/Support/TheoryContentSource.php',
        'app/Services/TheoryCourseManifestService.php',
        'tools/diagnostics/practice-completion-working-local.php',
        'docs/content/theory-template.md',
    ];
    protected const ID = 'm46-m49-practice-completion-v1';

    protected function allowedUpdateFields(): array { return ['body']; }
    protected function allowedPageUpdateFields(): array { return []; }
    protected function expectedInsertCount(): int { return 0; }
    protected function localGuard(): string { return PracticeCompletionLocalTargetGuard::class; }

    /** MySQL TEXT limits bytes, not Unicode characters; reject before backup or any update. */
    public static function assertBodyCapacity(string $body, int $maximum): void
    {
        if ($maximum <= 0 || strlen($body) > $maximum) {
            throw new RuntimeException('Practice completion body exceeds the current column byte capacity; no writes.');
        }
    }

    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        throw new RuntimeException('Practice completion restore is outside scope; no writes.');
    }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        if ($this->db->getDriverName() === 'mysql') {
            PracticeCompletionLocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget !== 'gramlyze.loc' || $this->localProof === null) {
                throw new RuntimeException('Practice completion requires explicit local target and fresh proof.');
            }
        }
        return parent::connection($expected, $writing);
    }

    public static function historicalDefinition(array $target, ?string $root = null): array
    {
        $index = array_search($target['identity'] ?? null, Package::OWNERS, true);
        if ($index === false) { throw new RuntimeException('Practice completion unknown historical owner.'); }
        $provider = Package::BINDINGS[$index]['legacy_package'];
        [$before, $source, $master] = $provider::load($root);
        $provider::validate($before, $source, $master);
        foreach ($source['targets'] as $old) {
            if ($old['identity'] === $target['identity']) { return $old['after']; }
        }
        throw new RuntimeException('Practice completion historical owner missing.');
    }

    /** Exact active definition projection; no theory, navigation, subtitle or metadata exemptions. */
    public static function afterDefinition(array $before, array $target): array
    {
        $after = $before;
        $slot = ($target['before']['practice']['sort_order'] ?? 0) - 1;
        if ($slot < 0 || !isset($after['page']['blocks'][$slot])
            || ($after['page']['blocks'][$slot]['body'] ?? null) !== ($target['before']['practice']['body'] ?? null)) {
            throw new RuntimeException('Practice completion source practice position/body differs.');
        }
        $after['page']['blocks'][$slot]['body'] = $target['after']['practice']['body'];
        return $after;
    }

    public static function assertDefinitionScope(array $before, array $after, array $target, int $index): void
    {
        $scope = Package::BINDINGS[$index] ?? null;
        if ($scope === null || ($target['identity'] ?? null) !== $scope['identity']
            || ($target['definition'] ?? null) !== $scope['definition'] || ($target['ancestry'] ?? null) !== $scope['ancestry']
            || ($target['slug'] ?? null) !== $scope['slug'] || ($before['seeder']['class'] ?? null) !== $scope['identity']
            || ($before['slug'] ?? null) !== $scope['slug'] || ($before['type'] ?? null) !== 'theory'
            || ($before['page']['locale'] ?? null) !== 'uk' || ($before['page']['category']['slug'] ?? null) !== $scope['ancestry'][0]) {
            throw new RuntimeException('Practice completion definition owner/category/locale differs.');
        }
        $slot = $target['before']['practice']['sort_order'] - 1;
        $old = $before['page']['blocks'][$slot] ?? null;
        if (!is_array($old) || $old['type'] !== 'practice-set'
            || M26DetailPackage::uuid($scope['identity'], $old, $slot + 1) !== $target['before']['practice']['uuid']
            || $after !== self::afterDefinition($before, $target)) {
            throw new RuntimeException('Practice completion active definition changes fields outside practice body.');
        }
    }

    public function plan(bool $lock = false): array
    {
        $connection = $this->connection();
        $root = dirname($this->databasePath);
        [$before, $patch, $master] = Package::load($root);
        Package::validate($before, $patch, $master, $root);
        if ($this->db->getDriverName() === 'mysql') {
            $column = (array) $this->db->selectOne(
                'SELECT DATA_TYPE AS kind, CHARACTER_OCTET_LENGTH AS max_bytes FROM information_schema.COLUMNS '
                .'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$this->db->getDatabaseName(), 'text_blocks', 'body']
            );
            if (!in_array($column['kind'] ?? null, ['tinytext', 'text', 'mediumtext', 'longtext'], true)
                || !is_numeric($column['max_bytes'] ?? null)) {
                throw new RuntimeException('Practice completion current body column capacity is unknown; no writes.');
            }
            foreach ($patch['targets'] as $target) {
                self::assertBodyCapacity($target['after']['practice']['body'], (int) $column['max_bytes']);
            }
        }
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => self::NAMES,
            'connection' => $connection, 'sources' => [], 'pages' => [], 'updates' => [], 'inserts' => []];
        $baseline = null;
        if ($this->localTarget !== null) {
            self::assertServedSources($root, $this->privateDirectory, $patch);
            foreach ([self::SYNC_RECEIPT, self::BEFORE_FILE, self::FROZEN_FILE] as $file) {
                $plan['sources']['private:'.$file] = self::fileHash($this->privateDirectory.'/'.$file);
            }
            $baseline = json_decode(file_get_contents($this->privateDirectory.'/'.self::BEFORE_FILE), true, flags: JSON_THROW_ON_ERROR);
        }
        foreach (self::sourcePaths($before, $patch, $root) as $path) { $plan['sources'][$path] = self::fileHash($root.'/'.$path); }

        $states = []; $ids = [];
        foreach ($patch['targets'] as $i => $target) {
            $name = $target['identity']; $historical = self::historicalDefinition($target, $root);
            $path = $target['definition'];
            if ($baseline !== null) {
                $captured = $baseline['definitions'][$name] ?? null;
                if (!is_array($captured) || ($captured['path'] ?? null) !== $path
                    || !is_string($captured['bytes'] ?? null)
                    || ($captured['sha256'] ?? null) !== hash('sha256', $captured['bytes'])
                    || json_decode($captured['bytes'], true, flags: JSON_THROW_ON_ERROR) !== $historical
                    || ($captured['definition'] ?? null) !== $historical) {
                    throw new RuntimeException('Practice completion independent definition BEFORE differs.');
                }
            }
            $actual = json_decode(file_get_contents($root.'/'.$path), true, flags: JSON_THROW_ON_ERROR);
            self::assertDefinitionScope($historical, $actual, $target, $i);

            $owners = $this->rows('pages', fn ($q) => $q->where('seeder', $name), $lock);
            if (count($owners) !== 1) { throw new RuntimeException('Practice completion missing/ambiguous page owner.'); }
            $owner = $owners[0]; $chain = $this->categoryChain($owner['page_category_id'], $lock);
            if (array_column($chain, 'slug') !== $target['ancestry']) {
                throw new RuntimeException('Practice completion category ancestry differs.');
            }
            foreach (['id', 'title', 'slug', 'type', 'page_category_id'] as $field) {
                if (($owner[$field] ?? null) !== $target['page'][$field]) {
                    throw new RuntimeException('Practice completion captured page identity differs: '.$field);
                }
            }
            if ($owner['text'] !== $historical['page']['subtitle_text']) {
                throw new RuntimeException('Practice completion page text changed outside scope.');
            }
            $rows = $this->rows('text_blocks', fn ($q) => $q->where('page_id', $owner['id']), $lock);
            $old = $target['before']['practice']; $new = $target['after']['practice'];
            $global = $this->rows('text_blocks', fn ($q) => $q->where('uuid', $old['uuid']), $lock);
            if (count($global) !== 1) { throw new RuntimeException('Practice completion practice UUID missing/ambiguous.'); }
            $row = $global[0];
            if ($row['page_id'] !== $owner['id'] || $row['page_category_id'] !== $owner['page_category_id']
                || $row['seeder'] !== $name || $row['locale'] !== 'uk') {
                throw new RuntimeException('Practice completion practice UUID belongs to another owner/locale.');
            }
            foreach (['uuid', 'type', 'column', 'heading', 'level', 'css_class', 'sort_order'] as $field) {
                if ($row[$field] !== $old[$field]) { throw new RuntimeException('Practice completion row metadata changed: '.$field); }
            }

            $configs = [['type' => 'subtitle', 'column' => 'header', 'heading' => null,
                'level' => $historical['page']['subtitle_level'] ?? null, 'css_class' => null,
                'body' => $historical['page']['subtitle_html'],
                'uuid_key' => $historical['page']['subtitle_uuid_key'] ?? 'subtitle'], ...$historical['page']['blocks']];
            $uk = array_values(array_filter($rows, static fn ($r) => $r['locale'] === 'uk'));
            if (count($uk) !== count($configs)) { throw new RuntimeException('Practice completion UK row set differs.'); }
            foreach ($configs as $position => $config) {
                $uuid = $this->resolveUuid($name, $config, $position);
                $matches = array_values(array_filter($uk, static fn ($r) => $r['uuid'] === $uuid));
                if (count($matches) !== 1) { throw new RuntimeException('Practice completion historical block missing/ambiguous.'); }
                $current = $matches[0];
                if ($current['seeder'] !== $name || $current['page_category_id'] !== $owner['page_category_id']
                    || $current['page_id'] !== $owner['id'] || $current['sort_order'] !== $position) {
                    throw new RuntimeException('Practice completion historical block ownership/order differs.');
                }
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                    if ($current[$field] !== ($config[$field] ?? null)) {
                        throw new RuntimeException('Practice completion historical block metadata differs.');
                    }
                }
                if ($uuid !== $old['uuid'] && $current['body'] !== $config['body']) {
                    throw new RuntimeException('Practice completion non-practice teaching body changed.');
                }
            }
            if ($baseline !== null) {
                self::assertMetadataBaseline($baseline['pages'][$name] ?? [], $owner, $rows, $old['uuid'], $old['body'], $new['body']);
            }
            $state = $this->candidate($plan['updates'], 'text_blocks', $row, $name, ['body' => $old['body']], ['body' => $new['body']]);
            if (!in_array($state, ['before', 'after'], true)) { throw new RuntimeException('Practice completion has no exact body delta.'); }
            $states[] = $state; $ids[] = $row['id'];
            $plan['pages'][$name] = ['page' => $owner, 'category_ancestry' => $chain, 'blocks' => $rows,
                'relations' => $this->relations($owner, $rows, $lock)];
        }
        if (count(array_unique($states)) !== 1 || count(array_unique($ids)) !== count(self::NAMES)) {
            throw new RuntimeException('Practice completion partial/manual package state; no writes.');
        }
        $plan['state'] = $states[0];
        if ($plan['inserts'] !== [] || count($plan['updates']) !== ($plan['state'] === 'before' ? count(self::NAMES) : 0)) {
            throw new RuntimeException('Practice completion body-only update/no-op scope differs.');
        }
        $plan['protected'] = $this->protectedFingerprints($ids);
        ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }

    /** Pure baseline check: only one known body may change; every page/row field remains protected. */
    public static function assertMetadataBaseline(array $captured, array $owner, array $rows, string $practiceUuid, string $beforeBody, string $afterBody): void
    {
        if (($captured['page'] ?? null) !== $owner || !is_array($captured['blocks'] ?? null)
            || count($captured['blocks']) !== count($rows)) {
            throw new RuntimeException('Practice completion independent page/row-set BEFORE is stale.');
        }
        $currentById = [];
        foreach ($rows as $row) {
            if (!isset($row['id']) || isset($currentById[$row['id']])) { throw new RuntimeException('Practice completion current row ID is duplicated.'); }
            $currentById[$row['id']] = $row;
        }
        $seen = []; $practiceCount = 0;
        foreach ($captured['blocks'] as $old) {
            if (!isset($old['id']) || isset($seen[$old['id']]) || !isset($currentById[$old['id']])) {
                throw new RuntimeException('Practice completion captured row ID missing/duplicated.');
            }
            $seen[$old['id']] = true; $current = $currentById[$old['id']];
            if (($old['uuid'] ?? null) === $practiceUuid) {
                $practiceCount++;
                if (($old['locale'] ?? null) !== 'uk' || ($old['type'] ?? null) !== 'practice-set'
                    || ($old['body'] ?? null) !== $beforeBody
                    || !in_array($current['body'] ?? null, [$beforeBody, $afterBody], true)) {
                    throw new RuntimeException('Practice completion captured practice body is not exact BEFORE/AFTER.');
                }
                unset($old['body'], $current['body']);
            }
            if ($old !== $current) { throw new RuntimeException('Practice completion protected row metadata/locale/body changed.'); }
        }
        if ($practiceCount !== 1) { throw new RuntimeException('Practice completion baseline practice UUID is not unique.'); }
    }

    /** Include all actual rows and columns, excluding only the twelve approved body values. */
    protected function protectedFingerprints(array $targetIds): array
    {
        $schema = $this->db->getDriverName() === 'sqlite' ? 'main' : $this->db->getDatabaseName();
        $tables = $this->db->getSchemaBuilder()->getTableListing(schema: $schema, schemaQualified: false);
        sort($tables); $result = [];
        foreach ($tables as $table) {
            $columns = $this->db->getSchemaBuilder()->getColumnListing($table);
            $query = $this->db->table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $hash = hash_init('sha256'); $count = 0;
            foreach ($query->cursor() as $record) {
                $row = (array) $record;
                if ($table === 'text_blocks' && in_array($row['id'] ?? null, $targetIds, true)) { unset($row['body']); }
                hash_update($hash, self::digest($row)."\n"); $count++;
            }
            $result[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
        }
        return $result;
    }

    public static function syncPaths(array $patch): array
    {
        $paths = [...self::SYNC_CODE_PATHS, Package::MASTER, Package::BEFORE, Package::SOURCE,
            ...array_column($patch['targets'], 'definition')];
        $paths = array_values(array_unique($paths)); sort($paths);
        return $paths;
    }

    public static function sourcePaths(array $before, array $patch, ?string $root = null): array
    {
        $root ??= base_path();
        $paths = [...self::syncPaths($patch), 'app/Services/M26ContentPatch.php', 'app/Services/M11LocalTargetGuard.php',
            'app/Services/PronounContentRepair.php', 'app/Support/M26DetailPackage.php',
            'app/Support/TextBlock/TextBlockUuidGenerator.php'];
        foreach (array_unique(array_column(Package::BINDINGS, 'legacy_package')) as $provider) {
            $paths[] = 'app/Support/'.class_basename($provider).'.php';
            foreach (['MASTER', 'BEFORE', 'SOURCE'] as $constant) { $paths[] = constant($provider.'::'.$constant); }
        }
        foreach ($patch['targets'] as $target) {
            foreach (glob(dirname($root.'/'.$target['definition']).'/localizations/*.json') ?: [] as $path) {
                $paths[] = substr(str_replace('\\', '/', $path), strlen(str_replace('\\', '/', $root)) + 1);
            }
        }
        $paths = array_values(array_unique($paths)); sort($paths);
        return $paths;
    }

    private static function fileHash(string $path): string
    {
        if (!is_file($path) || is_link($path)) { throw new RuntimeException('Practice completion source missing or linked: '.$path); }
        $hash = hash_file('sha256', $path);
        if (!is_string($hash)) { throw new RuntimeException('Practice completion source hash unavailable.'); }
        return $hash;
    }

    private static function assertServedSources(string $source, string $directory, array $patch): void
    {
        $file = $directory.'/'.self::SYNC_RECEIPT; self::fileHash($file);
        $sync = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        if (($sync['base_sha'] ?? null) !== self::ACCEPTED_BASE || !is_array($sync['files'] ?? null)
            || ($sync['visual_before'] ?? null) !== ['file' => self::BEFORE_FILE, 'sha256' => self::BEFORE_SHA]) {
            throw new RuntimeException('Practice completion reviewed source/visual sync receipt differs.');
        }
        $actual = array_column($sync['files'], 'path'); sort($actual);
        if ($actual !== self::syncPaths($patch)) { throw new RuntimeException('Practice completion source sync allowlist differs.'); }
        foreach ($sync['files'] as $record) {
            $rootPath = PracticeCompletionLocalTargetGuard::ROOT.'/'.$record['path'];
            $worktreePath = $source.'/'.$record['path'];
            $rootHash = self::fileHash($rootPath); $worktreeHash = self::fileHash($worktreePath);
            $sameCode = str_ends_with($record['path'], '.php')
                && str_replace("\r\n", "\n", file_get_contents($rootPath)) === str_replace("\r\n", "\n", file_get_contents($worktreePath));
            if ($rootHash !== ($record['root_after_sha256'] ?? null) || $worktreeHash !== ($record['worktree_sha256'] ?? null)
                || ($rootHash !== $worktreeHash && !$sameCode)) {
                throw new RuntimeException('Practice completion served source drift: '.$record['path']);
            }
        }
        $beforePath = $directory.'/'.self::BEFORE_FILE;
        if (self::fileHash($beforePath) !== self::BEFORE_SHA) { throw new RuntimeException('Practice completion independent BEFORE changed.'); }
        $before = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
        $expected = $before['visual_hashes'] ?? null;
        if (!is_array($expected) || $expected === []) { throw new RuntimeException('Practice completion visual BEFORE is incomplete.'); }
        ksort($expected);
        if (self::visualHashes(PracticeCompletionLocalTargetGuard::ROOT) !== $expected) {
            throw new RuntimeException('Practice completion visual sources changed; content-only apply refused.');
        }
        $frozenPath = $directory.'/'.self::FROZEN_FILE;
        if (self::fileHash($frozenPath) !== self::FROZEN_SHA) { throw new RuntimeException('Practice completion frozen BEFORE changed.'); }
        $frozen = json_decode(file_get_contents($frozenPath), true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($frozen['files'] ?? null) || $frozen['files'] === []) { throw new RuntimeException('Practice completion frozen inventory is empty.'); }
        foreach ($frozen['files'] as $relative => $sha) {
            if (self::fileHash(PracticeCompletionLocalTargetGuard::ROOT.'/'.$relative) !== $sha) {
                throw new RuntimeException('Practice completion non-target frozen source changed: '.$relative);
            }
        }
        foreach ([
            ['git', '-c', 'safe.directory='.$source, '-c', 'core.bare=false', '-C', $source, 'diff', '--name-only', self::ACCEPTED_BASE, '--', ...self::VISUAL_DIRECTORIES],
            ['git', '-c', 'safe.directory='.$source, '-c', 'core.bare=false', '-C', $source, 'ls-files', '--others', '--exclude-standard', '--', ...self::VISUAL_DIRECTORIES],
        ] as $arguments) {
            $process = new Process($arguments, $source); $process->setTimeout(20); $process->run();
            if (!$process->isSuccessful() || trim($process->getOutput()) !== '') {
                throw new RuntimeException('Practice completion worktree visual changes or Git proof unavailable.');
            }
        }
    }

    public static function visualHashes(string $root): array
    {
        $out = [];
        foreach (self::VISUAL_DIRECTORIES as $directory) {
            $path = $root.'/'.$directory;
            if (!is_dir($path) || is_link($path)) { throw new RuntimeException('Practice completion visual directory missing or linked.'); }
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->isLink()) { throw new RuntimeException('Practice completion linked visual source refused.'); }
                if (!$file->isFile()) { continue; }
                $absolute = str_replace('\\', '/', $file->getPathname());
                $relative = substr($absolute, strlen(str_replace('\\', '/', $root)) + 1);
                $out[$relative] = self::fileHash($absolute);
            }
        }
        ksort($out);
        return $out;
    }
}
