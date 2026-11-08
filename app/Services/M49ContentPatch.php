<?php

namespace App\Services;

use App\Support\M49AdviceModalsPackage as Package;
use RuntimeException;
use Symfony\Component\Process\Process;

/** Finite M49 content-only planner; writes use the existing exclusive-backup M26 transaction. */
class M49ContentPatch extends M26ContentPatch
{
    public const NAMES = Package::OWNERS;
    public const ACCEPTED_BASE = Package::BASE_SHA;
    public const BEFORE_FILE = 'before-v1.json';
    public const FROZEN_FILE = 'frozen-before-v1.json';
    public const FROZEN_SHA = 'a1977720bdda51f39cfc3f00d7092b11c63e604d4f5b88a805ed7f0696d4474c';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const BEFORE_SHA = 'c3bfec76d7ce222f094a07b0053f8a50b12c5016fb7abae5381d1185d746d772';
    public const VISUAL_DIRECTORIES = ['resources/views', 'resources/css', 'resources/js', 'public/js'];
    public const SYNC_CODE_PATHS = [
        'app/Support/M49AdviceModalsPackage.php',
        'app/Services/M49LocalTargetGuard.php',
        'app/Services/M49ContentPatch.php',
        'app/Console/Commands/PatchM49Content.php',
        'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php',
        'tools/diagnostics/m49-working-local.php',
    ];
    protected const ID = 'm49-advice-modals-standard-view-v1';

    protected function allowedUpdateFields(): array { return ['body']; }
    protected function allowedPageUpdateFields(): array { return ['text']; }
    protected function localGuard(): string { return M49LocalTargetGuard::class; }
    protected function expectedInsertCount(): int
    {
        [$before, $package] = Package::load(dirname($this->databasePath));
        return array_sum(array_map(static fn ($i) => count($package['targets'][$i]['after']['page']['blocks'])
            - count($before['targets'][$i]['before']['page']['blocks']), array_keys($package['targets'])));
    }

    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        throw new RuntimeException('M49 restore is outside scope; no writes.');
    }

    public static function assertDefinitionScope(array $before, array $after, array $target, int $index): void
    {
        $name = self::NAMES[$index] ?? null; $binding = Package::BINDINGS[$index] ?? null;
        [, $package] = Package::load();
        $count = count($package['targets'][$index]['after']['page']['blocks'] ?? []);
        if ($name === null || ($target['identity'] ?? null) !== $name
            || ($before['seeder']['class'] ?? null) !== $name
            || ($before['page']['locale'] ?? null) !== 'uk' || ($before['type'] ?? null) !== 'theory'
            || ($before['page']['category']['slug'] ?? null) !== ($binding['ancestry'][0] ?? null)
            || ($target['ancestry'] ?? null) !== ($binding['ancestry'] ?? null)
            || count($before['page']['blocks']) !== 6 || count($after['page']['blocks']) !== $count
            || ($target['slug'] ?? null) !== $before['slug']
            || ($target['path'] ?? null) !== ($binding['path'] ?? null)
            || ($target['local_url'] ?? null) !== 'http://gramlyze.loc/theory/'.implode('/', $binding['ancestry']).'/'.$before['slug']) {
            throw new RuntimeException('M49 definition owner/category/locale/slots differ.');
        }
        $protected = $after;
        $protected['page']['blocks'] = $before['page']['blocks'];
        foreach (['subtitle_html', 'subtitle_text'] as $field) {
            $protected['page'][$field] = $before['page'][$field];
        }
        if ($protected !== $before) {
            throw new RuntimeException('M49 protected definition metadata changed.');
        }
        foreach ($before['page']['blocks'] as $i => $old) {
            $new = $after['page']['blocks'][$i];
            unset($old['body'], $new['body']);
            if ($old !== $new) { throw new RuntimeException('M49 continuing block metadata/order changed.'); }
        }
        foreach (array_slice($after['page']['blocks'], 6) as $new) {
            if (!is_string($new['uuid_key'] ?? null) || !str_starts_with($new['uuid_key'], 'm49:'.$before['slug'].':')) {
                throw new RuntimeException('M49 new block requires its stable content key.');
            }
        }
    }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath);
        [$manifest, $package, $master] = Package::load($root);
        Package::validate($manifest, $package, $master);
        if (array_column($package['targets'], 'identity') !== self::NAMES) {
            throw new RuntimeException('M49 exact owner scope differs.');
        }
        $plan = [
            'patch' => static::ID, 'version' => 1, 'names' => self::NAMES,
            'connection' => $this->connection(), 'sources' => [], 'pages' => [], 'updates' => [], 'inserts' => [],
        ];
        if ($this->localTarget !== null) {
            self::assertServedSources($root, $this->privateDirectory, $package);
            $plan['sources']['private:'.self::SYNC_RECEIPT] = self::fileHash($this->privateDirectory.'/'.self::SYNC_RECEIPT);
            $plan['sources']['private:'.self::BEFORE_FILE] = self::BEFORE_SHA;
            $plan['sources']['private:'.self::FROZEN_FILE] = self::FROZEN_SHA;
        }
        foreach (self::sourcePaths($manifest, $package, $root) as $path) {
            $plan['sources'][$path] = self::fileHash($root.'/'.$path);
        }

        $baseline = $this->localTarget === null ? null : json_decode(file_get_contents($this->privateDirectory.'/'.self::BEFORE_FILE), true, flags: JSON_THROW_ON_ERROR);
        $states = []; $targetIds = [];
        foreach ($package['targets'] as $i => $target) {
            $name = $target['identity']; $before = $manifest['targets'][$i]['before'];
            $after = $target['after']; $path = $target['path'];
            self::assertDefinitionScope($before, $after, $target, $i);
            $bytes = file_get_contents($root.'/'.$path);
            $actual = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            if ($actual !== $after) { throw new RuntimeException('M49 canonical source differs: '.$path); }
            $plan['sources'][$path] = hash('sha256', $bytes);

            $owners = $this->rows('pages', fn ($q) => $q->where('seeder', $name), $lock);
            if (count($owners) !== 1) { throw new RuntimeException('M49 missing/ambiguous page owner.'); }
            $owner = $owners[0];
            $chain = $this->categoryChain($owner['page_category_id'], $lock);
            if (array_column($chain, 'slug') !== Package::BINDINGS[$i]['ancestry'] || $owner['slug'] !== $before['slug']
                || $owner['type'] !== 'theory' || $owner['title'] !== $before['page']['title']) {
                throw new RuntimeException('M49 page/category identity conflict.');
            }

            $rowStates = [];
            $state = $this->candidate($plan['updates'], 'pages', $owner, $name,
                ['text' => $before['page']['subtitle_text']], ['text' => $after['page']['subtitle_text']]);
            if ($state !== null) { $rowStates[] = $state; }

            $rows = $this->rows('text_blocks', fn ($q) => $q->where('page_id', $owner['id']), $lock);
            if ($baseline !== null) {
                self::assertMetadataBaseline($baseline['pages'][$name], $owner, $rows);
            }
            $uk = array_values(array_filter($rows, fn ($r) => $r['locale'] === 'uk'));
            $subtitle = [
                'type' => 'subtitle', 'column' => 'header', 'heading' => null,
                'level' => $before['page']['subtitle_level'] ?? null, 'body' => $before['page']['subtitle_html'],
                'uuid_key' => $before['page']['subtitle_uuid_key'] ?? 'subtitle',
            ];
            $newSubtitle = $subtitle; $newSubtitle['body'] = $after['page']['subtitle_html'];
            $oldConfigs = [$subtitle, ...$before['page']['blocks']];
            $newConfigs = [$newSubtitle, ...$after['page']['blocks']];

            foreach ($oldConfigs as $position => $old) {
                $new = $newConfigs[$position] ?? null;
                if ($new === null) { throw new RuntimeException('M49 removes an existing block slot.'); }
                $oldIdentity = $old; $newIdentity = $new;
                unset($oldIdentity['body'], $newIdentity['body']);
                if ($oldIdentity !== $newIdentity) { throw new RuntimeException('M49 existing block metadata changed.'); }
                $uuid = $this->resolveUuid($name, $old, $position);
                if ($this->resolveUuid($name, $new, $position) !== $uuid) { throw new RuntimeException('M49 old UUID changed.'); }
                $global = $this->rows('text_blocks', fn ($q) => $q->where('uuid', $uuid), $lock);
                if (count($global) !== 1) { throw new RuntimeException('M49 old UUID missing/ambiguous.'); }
                $row = $global[0]; $this->assertOwner($row, $owner, $name, $position);
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                    if ($row[$field] !== ($old[$field] ?? null)) {
                        throw new RuntimeException('M49 old block metadata differs: '.$field);
                    }
                }
                $targetIds[] = $row['id'];
                $state = $this->candidate($plan['updates'], 'text_blocks', $row, $name,
                    ['body' => $old['body']], ['body' => $new['body']]);
                if ($state !== null) { $rowStates[] = $state; }
            }
            $rowStates = array_values(array_unique($rowStates));
            if (count($rowStates) !== 1) { throw new RuntimeException('M49 partial/manual owner state.'); }
            $state = $rowStates[0]; $present = 0;

            foreach (array_slice($newConfigs, count($oldConfigs), null, true) as $position => $block) {
                $uuid = $this->resolveUuid($name, $block, $position);
                $global = $this->rows('text_blocks', fn ($q) => $q->where('uuid', $uuid), $lock);
                $fields = [
                    'uuid' => $uuid, 'page_id' => $owner['id'], 'page_category_id' => $owner['page_category_id'],
                    'locale' => 'uk', 'type' => $block['type'], 'column' => $block['column'],
                    'heading' => $block['heading'] ?? null, 'css_class' => $block['css_class'] ?? null,
                    'sort_order' => $position, 'body' => $block['body'], 'level' => $block['level'] ?? null, 'seeder' => $name,
                ];
                if ($state === 'before') {
                    if ($global !== []) { throw new RuntimeException('M49 unknown/partial new UUID.'); }
                    $plan['inserts'][] = ['table' => 'text_blocks', 'seeder' => $name,
                        'fields' => $fields, 'sha256' => self::digest($fields)];
                } else {
                    if (count($global) !== 1) { throw new RuntimeException('M49 inserted row missing/ambiguous.'); }
                    $row = $global[0]; $this->assertOwner($row, $owner, $name, $position);
                    foreach ($fields as $field => $value) {
                        if ($row[$field] !== $value) { throw new RuntimeException('M49 inserted row edited: '.$field); }
                    }
                    if (empty($row['created_at']) || $row['created_at'] !== $row['updated_at']) {
                        throw new RuntimeException('M49 inserted timestamps changed.');
                    }
                    $targetIds[] = $row['id']; $present++;
                }
            }
            if (count($uk) !== count($oldConfigs) + $present) { throw new RuntimeException('M49 unexpected UK rows.'); }
            $states[] = $state;
            $plan['pages'][$name] = ['page' => $owner, 'category_ancestry' => $chain, 'blocks' => $rows,
                'relations' => $this->relations($owner, $rows, $lock)];
        }
        if (count(array_unique($states)) !== 1) { throw new RuntimeException('M49 partial package; no writes.'); }
        $plan['state'] = $states[0];
        if (($plan['state'] === 'before' && count($plan['inserts']) !== $this->expectedInsertCount())
            || ($plan['state'] === 'after' && ($plan['updates'] !== [] || $plan['inserts'] !== []))) {
            throw new RuntimeException('M49 finite insert/no-op scope differs.');
        }
        $plan['protected'] = $this->protectedFingerprints($targetIds);
        ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }

    /** Actual tables, including sessions/progress, with only approved fields excluded. */
    protected function protectedFingerprints(array $targetIds): array
    {
        $schema = $this->db->getDriverName() === 'sqlite' ? 'main' : $this->db->getDatabaseName();
        $tables = $this->db->getSchemaBuilder()->getTableListing(schema: $schema, schemaQualified: false);
        sort($tables);
        $pageIds = $this->db->table('pages')->whereIn('seeder', self::NAMES)->pluck('id')->all();
        $result = [];
        foreach ($tables as $table) {
            $columns = $this->db->getSchemaBuilder()->getColumnListing($table);
            $query = $this->db->table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            if ($table === 'text_blocks') { $query->whereNotIn('id', $targetIds); }
            $hash = hash_init('sha256'); $count = 0;
            foreach ($query->cursor() as $record) {
                $row = (array) $record;
                if ($table === 'pages' && in_array($row['id'], $pageIds, true)) { unset($row['text']); }
                hash_update($hash, self::digest($row)."\n"); $count++;
            }
            $result[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
        }
        return $result;
    }

    /** Pure protection policy; source/content checks remain in the finite planner. No I/O. */
    public static function assertMetadataBaseline(array $captured, array $owner, array $rows): void
    {
        $capturedOwner = $captured['page'];
        $protectedOwner = $owner; unset($capturedOwner['text'], $protectedOwner['text']);
        if ($capturedOwner !== $protectedOwner) { throw new RuntimeException('M49 independent page BEFORE is stale.'); }
        $seen = [];
        foreach ($captured['blocks'] as $capturedRow) {
            if (isset($seen[$capturedRow['id']])) { throw new RuntimeException('M49 captured row ID is duplicated.'); }
            $seen[$capturedRow['id']] = true;
            $matches = array_values(array_filter($rows, static fn ($r) => $r['id'] === $capturedRow['id']));
            if (count($matches) !== 1) { throw new RuntimeException('M49 captured row ID is missing or replaced.'); }
            $currentRow = $matches[0];
            if ($capturedRow['locale'] === 'uk') { unset($capturedRow['body'], $currentRow['body']); }
            if ($currentRow !== $capturedRow) { throw new RuntimeException('M49 independent block BEFORE metadata/locale is stale.'); }
        }
    }

    private function assertOwner(array $row, array $owner, string $name, int $position): void
    {
        if ($row['seeder'] !== $name || $row['locale'] !== 'uk' || $row['page_id'] !== $owner['id']
            || $row['page_category_id'] !== $owner['page_category_id'] || (int) $row['sort_order'] !== $position) {
            throw new RuntimeException('M49 existing row ownership/order differs.');
        }
    }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        if ($this->db->getDriverName() === 'mysql') {
            M49LocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget !== 'gramlyze.loc' || $this->localProof === null) {
                throw new RuntimeException('M49 requires explicit local target and fresh nonce proof.');
            }
        }
        return parent::connection($expected, $writing);
    }

    public static function syncPaths(array $package): array
    {
        $paths = [...self::SYNC_CODE_PATHS, Package::MASTER, Package::BEFORE, Package::SOURCE,
            ...array_column($package['targets'], 'path')];
        $paths = array_values(array_unique($paths)); sort($paths);
        return $paths;
    }

    public static function sourcePaths(array $before, array $package, ?string $root = null): array
    {
        $root ??= base_path();
        $paths = [...self::syncPaths($package),
            'app/Services/M26ContentPatch.php', 'app/Services/M11LocalTargetGuard.php',
            'app/Services/PronounContentRepair.php', 'app/Support/M26DetailPackage.php',
            'app/Support/TextBlock/TextBlockUuidGenerator.php'];
        foreach ($package['targets'] as $target) {
            foreach (glob(dirname($root.'/'.$target['path']).'/localizations/*.json') ?: [] as $path) {
                $paths[] = substr(str_replace('\\', '/', $path), strlen(str_replace('\\', '/', $root)) + 1);
            }
        }
        $paths = array_values(array_unique($paths)); sort($paths);
        return $paths;
    }

    private static function fileHash(string $path): string
    {
        if (!is_file($path) || is_link($path)) { throw new RuntimeException('M49 source missing or linked: '.$path); }
        $hash = hash_file('sha256', $path);
        if (!is_string($hash)) { throw new RuntimeException('M49 source hash unavailable.'); }
        return $hash;
    }

    private static function assertServedSources(string $source, string $directory, array $package): void
    {
        $file = $directory.'/'.self::SYNC_RECEIPT; self::fileHash($file);
        $sync = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        if (($sync['base_sha'] ?? null) !== self::ACCEPTED_BASE || !is_array($sync['files'] ?? null)
            || ($sync['visual_before'] ?? null) !== ['file' => self::BEFORE_FILE, 'sha256' => self::BEFORE_SHA]) {
            throw new RuntimeException('M49 reviewed source/visual sync receipt differs.');
        }
        $actual = array_column($sync['files'], 'path'); sort($actual);
        if ($actual !== self::syncPaths($package)) {
            throw new RuntimeException('M49 source sync allowlist differs.');
        }
        foreach ($sync['files'] as $record) {
            $rootHash = self::fileHash(M49LocalTargetGuard::ROOT.'/'.$record['path']);
            $worktreeHash = self::fileHash($source.'/'.$record['path']);
            // Preserve existing CRLF/LF in shared PHP. Both raw hashes stay bound
            // independently; only line-ending-equivalent PHP is accepted.
            $sameCode = str_ends_with($record['path'], '.php')
                && str_replace("\r\n", "\n", file_get_contents(M49LocalTargetGuard::ROOT.'/'.$record['path']))
                    === str_replace("\r\n", "\n", file_get_contents($source.'/'.$record['path']));
            if ($rootHash !== ($record['root_after_sha256'] ?? null)
                || $worktreeHash !== ($record['worktree_sha256'] ?? null) || ($rootHash !== $worktreeHash && !$sameCode)) {
                throw new RuntimeException('M49 served source drift: '.$record['path']);
            }
        }
        $beforePath = $directory.'/'.self::BEFORE_FILE;
        if (self::fileHash($beforePath) !== self::BEFORE_SHA) { throw new RuntimeException('M49 independent BEFORE changed.'); }
        $before = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
        $expected = $before['visual_hashes'] ?? null;
        if (!is_array($expected) || $expected === []) { throw new RuntimeException('M49 visual BEFORE is incomplete.'); }
        $actual = self::visualHashes(M49LocalTargetGuard::ROOT); ksort($expected);
        if ($actual !== $expected) { throw new RuntimeException('M49 visual source changed; content-only apply refused.'); }

        $frozenPath = $directory.'/'.self::FROZEN_FILE;
        if (self::fileHash($frozenPath) !== self::FROZEN_SHA) { throw new RuntimeException('M49 frozen BEFORE changed.'); }
        $frozen = json_decode(file_get_contents($frozenPath), true, flags: JSON_THROW_ON_ERROR);
        foreach ($frozen['files'] as $relative => $sha) {
            if (self::fileHash(M49LocalTargetGuard::ROOT.'/'.$relative) !== $sha) {
                throw new RuntimeException('M49 non-target frozen source changed: '.$relative);
            }
        }

        // Only this source worktree is compared to its accepted base; foreign served visual changes are preserved above.
        foreach ([
            ['git', '-c', 'safe.directory='.$source, '-c', 'core.bare=false', '-C', $source, 'diff', '--name-only', self::ACCEPTED_BASE, '--', ...self::VISUAL_DIRECTORIES],
            ['git', '-c', 'safe.directory='.$source, '-c', 'core.bare=false', '-C', $source, 'ls-files', '--others', '--exclude-standard', '--', ...self::VISUAL_DIRECTORIES],
        ] as $arguments) {
            $process = new Process($arguments, $source); $process->setTimeout(20); $process->run();
            if (!$process->isSuccessful() || trim($process->getOutput()) !== '') {
                throw new RuntimeException('M49 worktree has unapproved visual source changes or Git proof unavailable.');
            }
        }
    }

    private static function visualHashes(string $root): array
    {
        $out = [];
        foreach (self::VISUAL_DIRECTORIES as $directory) {
            $path = $root.'/'.$directory;
            if (!is_dir($path) || is_link($path)) { throw new RuntimeException('M49 visual directory missing or linked.'); }
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->isLink()) { throw new RuntimeException('M49 linked visual source refused.'); }
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
