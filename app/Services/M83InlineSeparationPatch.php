<?php

namespace App\Services;

use App\Support\M83B2RevisionBasicGrammar as Package;
use RuntimeException;

/** One reviewed body cell only. The unchanged M26 writer owns every DB mutation. */
class M83InlineSeparationPatch extends M26ContentPatch
{
    public const NAMES = Package::OWNERS;
    public const MANIFEST = 'docs/content/m83-inline-separation.v1.0.1.json';
    public const MANIFEST_SHA = 'fe8dafb7673451b136723b328662b3253a82a523b2a9910f1e3b61ebd5498f97';
    public const BASELINE = 'final-readonly-v2.json';
    public const BASELINE_SHA = 'c5d643f519d26b19081113a1400920b168d9e7f02fb975c0419d9cfcaf277b62';
    public const SYNC_RECEIPT = 'source-sync-v1.0.1b.json';
    public const SOURCE_BEFORE = 'independent-source-before-v1.json';
    public const SOURCE_BEFORE_SHA = 'bae358ff481654fcd38be6a5802a9419f4b4edf7132c931e3e38ef82bc535618';
    public const SOURCE_FIXTURE = 'tests/Fixtures/m83-owned-source-hunks.json';
    public const SOURCE_FIXTURE_SHA = 'e73158bc4d0ecc145865d86c8e72e8b08dda2234771ec7b4003b44413b475dcd';
    public const PREVIOUS_SOURCE = 'database/content-patches/m83-b2-revision-basic-grammar.v1.json';
    public const PREVIOUS_SOURCE_SHA = 'b88875f26454dd2a6e6be1b81ca2ccc8dfbdfef1731231b87c325bf311a05cbc';
    public const NEXT_SOURCE = 'database/content-patches/m83-b2-revision-basic-grammar.v1.0.1.json';
    public const NEXT_SOURCE_SHA = 'd0ca64f24cd6c1767e331aaeda258ba778e9eac49c6accc55a23237b89478e7c';
    public const BEFORE_BODY_SHA = '40bcc789a1e51293a8f8eb4077c30c0d30ff00501b813cc268e34d17976e70c1';
    public const AFTER_BODY_SHA = '12adb7840627d0c7e5aaa5c6b6d29395d8bd221e3d56f8adf1e79aa42b0f100c';
    public const TARGET_ID = 1153;
    public const TARGET_UUID = '2c280a48-644e-5876-8435-bf975022255b';
    protected const ID = 'm83-inline-separation-1.0.0-to-1.0.1';
    private string $previousBody = '';
    private string $nextBody = '';

    protected function localGuard(): string { return M83LocalTargetGuard::class; }
    protected function expectedInsertCount(): int { return 0; }
    protected function allowedUpdateFields(): array { return ['body']; }

    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        throw new RuntimeException('M83 inline-separation restore is outside this approved scope; no writes.');
    }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        if ($this->db->getDriverName() === 'mysql') {
            M49LocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget !== 'gramlyze.loc' || $this->localProof === null) {
                throw new RuntimeException('M83 correction preview/apply requires gramlyze.loc and fresh nonce proof.');
            }
        }
        return parent::connection($expected, $writing);
    }

    private static function read(string $root, string $path, string $sha): array
    {
        if (!hash_equals($sha, self::fileHash($root.'/'.$path))) {
            throw new RuntimeException('M83 correction immutable source/evidence differs: '.$path);
        }
        return json_decode(file_get_contents($root.'/'.$path), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function fileHash(string $path): string
    {
        return TheoryNativeContentPatch::fileHash($path);
    }

    /** Independent exact transition: no native protocol, task, metadata or other source edit. */
    public static function revision(?string $root = null): array
    {
        $root ??= base_path();
        $manifest = self::read($root, self::MANIFEST, self::MANIFEST_SHA);
        $old = self::read($root, self::PREVIOUS_SOURCE, self::PREVIOUS_SOURCE_SHA);
        $next = self::read($root, self::NEXT_SOURCE, self::NEXT_SOURCE_SHA);
        $identity = ['id' => self::TARGET_ID, 'uuid' => self::TARGET_UUID, 'locale' => 'uk',
            'sort_order' => 2, 'type' => 'forms-grid', 'column' => 'left', 'heading' => null,
            'css_class' => null, 'level' => null];
        if (($manifest['schema_version'] ?? null) !== 1 || ($manifest['native_version'] ?? null) !== '1.0.0'
            || ($manifest['author_revision'] ?? null) !== '1.0.1' || ($manifest['status'] ?? null) !== 'frozen'
            || ($manifest['owner'] ?? null) !== self::NAMES[4] || ($manifest['page_id'] ?? null) !== 21
            || ($manifest['category_id'] ?? null) !== 2 || ($manifest['source_index'] ?? null) !== 1
            || ($manifest['row'] ?? null) !== $identity || ($manifest['added_br'] ?? null) !== 7
            || ($manifest['added_body_utf8_bytes'] ?? null) !== 28 || ($manifest['learning_text_exact'] ?? null) !== true
            || ($manifest['sql_updates'] ?? null) !== 1 || ($manifest['sql_inserts'] ?? null) !== 0
            || ($manifest['predecessor']['source'] ?? null) !== self::PREVIOUS_SOURCE
            || ($manifest['predecessor']['source_sha256'] ?? null) !== self::PREVIOUS_SOURCE_SHA
            || ($manifest['predecessor']['independent_post_apply_basename'] ?? null) !== self::BASELINE
            || ($manifest['predecessor']['independent_post_apply_sha256'] ?? null) !== self::BASELINE_SHA
            || ($manifest['successor']['source'] ?? null) !== self::NEXT_SOURCE) {
            throw new RuntimeException('M83 correction finite manifest identity/transition differs.');
        }
        $beforeBody = $old['targets'][4]['after']['page']['blocks'][1]['body'] ?? null;
        $afterBody = $next['targets'][4]['after']['page']['blocks'][1]['body'] ?? null;
        if (!is_string($beforeBody) || !is_string($afterBody)
            || hash('sha256', $beforeBody) !== self::BEFORE_BODY_SHA
            || hash('sha256', $afterBody) !== self::AFTER_BODY_SHA
            || ($manifest['before_body_sha256'] ?? null) !== self::BEFORE_BODY_SHA
            || ($manifest['after_body_sha256'] ?? null) !== self::AFTER_BODY_SHA
            || strlen($beforeBody) !== 1736 || strlen($afterBody) !== 1764) {
            throw new RuntimeException('M83 correction exact predecessor/successor body differs.');
        }
        $normalized = $next; $normalized['targets'][4]['after']['page']['blocks'][1]['body'] = $beforeBody;
        if ($normalized !== $old || count($manifest['changes'] ?? []) !== 6) {
            throw new RuntimeException('M83 correction changes source outside the one reviewed body.');
        }
        $beforeData = json_decode($beforeBody, true, flags: JSON_THROW_ON_ERROR);
        $afterData = json_decode($afterBody, true, flags: JSON_THROW_ON_ERROR);
        $normalized = $afterData; $total = 0;
        foreach ($manifest['changes'] as $i => $change) {
            $a = $beforeData['items'][$i]['subtitle'] ?? null; $b = $afterData['items'][$i]['subtitle'] ?? null;
            $count = $i === 0 ? 2 : 1;
            if (($change['item_index'] ?? null) !== $i
                || ($change['pointer'] ?? null) !== '/lessons/4/blocks/1/data/items/'.$i.'/subtitle'
                || !is_string($a) || !is_string($b) || ($change['before'] ?? null) !== $a || ($change['after'] ?? null) !== $b
                || ($change['before_sha256'] ?? null) !== hash('sha256', $a)
                || ($change['after_sha256'] ?? null) !== hash('sha256', $b)
                || ($change['added_br'] ?? null) !== $count || ($change['added_utf8_bytes'] ?? null) !== 4 * $count
                || ($change['learning_text_exact'] ?? null) !== true || substr_count($a, '</p><p>') !== $count
                || str_replace('</p><p>', '</p><br><p>', $a) !== $b || strip_tags($a) !== strip_tags($b)) {
                throw new RuntimeException('M83 correction six subtitle boundaries or learning text differ.');
            }
            $normalized['items'][$i]['subtitle'] = $a; $total += $count;
        }
        if ($normalized !== $beforeData || $total !== 7) {
            throw new RuntimeException('M83 correction altered another forms-grid field.');
        }
        foreach (['source', 'master', 'checksums'] as $key) {
            self::read($root, $manifest['predecessor'][$key], $manifest['predecessor'][$key.'_sha256']);
        }
        [$before, $active, $master, $contract] = Package::load($root);
        Package::validate($before, $active, $master, $contract);
        if (Package::VERSION !== '1.0.0' || Package::SOURCE !== self::NEXT_SOURCE
            || Package::SOURCE_SHA !== self::NEXT_SOURCE_SHA || $active !== $next
            || Package::MASTER !== ($manifest['successor']['master'] ?? null)
            || Package::CHECKSUMS !== ($manifest['successor']['checksums'] ?? null)
            || Package::CONTRACT !== ($manifest['successor']['contract'] ?? null)
            || Package::CONTRACT_SHA !== ($manifest['successor']['contract_sha256'] ?? null)) {
            throw new RuntimeException('M83 correction canonical successor/native protocol differs.');
        }
        foreach ($next['targets'] as $target) {
            if (json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR) !== $target['after']) {
                throw new RuntimeException('M83 correction active definition differs from exact canonical successor.');
            }
        }
        return [$manifest, $old, $next];
    }

    public static function sourcePaths(?string $root = null): array
    {
        $root ??= base_path(); [$manifest, , $next] = self::revision($root);
        $contract = json_decode(file_get_contents($root.'/'.Package::CONTRACT), true, flags: JSON_THROW_ON_ERROR);
        $paths = [...M83ContentPatch::sourcePaths($next, $contract, $root), self::MANIFEST, self::SOURCE_FIXTURE,
            self::PREVIOUS_SOURCE, $manifest['predecessor']['master'], $manifest['predecessor']['checksums'],
            $manifest['successor']['components'], $manifest['successor']['readable'],
            'app/Services/M83InlineSeparationPatch.php', 'tools/diagnostics/m83-inline-separation-patch.php'];
        $paths = array_values(array_unique($paths)); sort($paths); return $paths;
    }

    /** Production evidence is pinned; isolated tests override only this SQL receipt seam. */
    protected function baselineEvidence(): array
    {
        $data = self::read($this->privateDirectory, self::BASELINE, self::BASELINE_SHA);
        if (($data['all_old_rows_and_timestamps_exact'] ?? null) !== true
            || ($data['independent_before_projection_exact'] ?? null) !== true
            || ($data['reviewed_protected_projection_exact'] ?? null) !== true
            || ($data['db_writes'] ?? null) !== 0 || ($data['http_requests'] ?? null) !== 0
            || ($data['sql_tables'] ?? null) !== 46 || count($data['sql_after'] ?? []) !== 46
            || ($data['continuing_uk_rows'] ?? null) !== 77 || ($data['unchanged_en_pl_rows'] ?? null) !== 142
            || ($data['new_question_families'] ?? null) !== [] || count($data['new_practices'] ?? []) !== 1
            || ($data['visual_files_unchanged'] ?? null) !== 326 || ($data['frozen_files_unchanged'] ?? null) !== 2826
            || ($data['source_sync_unchanged'] ?? null) !== true) {
            throw new RuntimeException('M83 correction baseline lacks complete applied six-owner/46-table evidence.');
        }
        if ($this->db->getDriverName() === 'mysql'
            && ($data['runtime'] ?? null) !== M49LocalTargetGuard::publicIdentity($this->db, dirname($this->databasePath).'/public')) {
            throw new RuntimeException('M83 correction baseline runtime differs from proven local connection.');
        }
        return $data;
    }

    /** Every row of every actual table. Only the exact target body may be normalized/omitted. */
    protected function correctionFingerprints(bool $normalize = false, bool $protect = false): array
    {
        if ($normalize && $this->previousBody === '') { throw new RuntimeException('M83 correction predecessor body is not prepared.'); }
        $schema = $this->db->getDriverName() === 'sqlite' ? 'main' : $this->db->getDatabaseName();
        $tables = $this->db->getSchemaBuilder()->getTableListing(schema: $schema, schemaQualified: false);
        sort($tables); $out = [];
        foreach ($tables as $table) {
            $columns = $this->db->getSchemaBuilder()->getColumnListing($table); $query = $this->db->table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $hash = hash_init('sha256'); $count = 0;
            foreach ($query->cursor() as $record) {
                $row = (array) $record;
                if ($table === 'text_blocks' && ($row['id'] ?? null) === self::TARGET_ID) {
                    if ($protect) { unset($row['body']); } elseif ($normalize) { $row['body'] = $this->previousBody; }
                }
                hash_update($hash, self::digest($row)."\n"); ++$count;
            }
            $out[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
        }
        return $out;
    }

    protected function protectedFingerprints(array $targetIds): array
    {
        return $this->correctionFingerprints(protect: true);
    }


    /** Pure exact reversal for the one explicitly installed read-only nonce route. */
    public static function assertProofRouteBytes(string $routes, string $saved, array $proof, string $proofName, array $profile): void
    {
        $nonce = $proof['nonce'] ?? '';
        if (!is_string($nonce) || preg_match('/^[a-f0-9]{32}$/D', $nonce) !== 1
            || ($proof['target'] ?? null) !== 'gramlyze.loc' || $proofName !== 'local-proof-'.$nonce.'.json'
            || ($proof['routes_before_file'] ?? null) !== 'routes-before-'.$nonce.'.bin'
            || ($proof['routes_before_sha256'] ?? null) !== hash('sha256', $saved)
            || ($profile['exists'] ?? null) !== true || ($profile['bytes'] ?? null) !== strlen($saved)
            || ($profile['raw_sha256'] ?? null) !== hash('sha256', $saved)
            || ($profile['lf_sha256'] ?? null) !== hash('sha256', str_replace("\r\n", "\n", $saved))) {
            throw new RuntimeException('M83 correction nonce route snapshot differs from this fresh proof and paired BEFORE.');
        }
        $hunk = <<<'ROUTE'
// Temporary M83 revision local-target proof; removed after guarded apply/no-op.
Route::get('/_local/m83-target-__M83_NONCE__', function (Request $request) {
    $nonce = '__M83_NONCE__';
    abort_unless(\App\Services\M83LocalTargetGuard::allowsProofRequest($request, $nonce, base_path(), public_path()), 404);
    $db = \Illuminate\Support\Facades\DB::connection();
    \App\Services\M83LocalTargetGuard::assertConfiguredDatabase($db);
    $db->statement('SET TRANSACTION READ ONLY');
    $db->beginTransaction();
    try {
        return response()->json([
            'runtime' => \App\Services\M83LocalTargetGuard::publicIdentity($db, public_path()),
            'proof' => \App\Services\M11LocalTargetGuard::runtimeProof($db, $nonce, public_path()),
        ])->header('Cache-Control', 'no-store');
    } finally {
        $db->rollBack();
    }
})->domain('gramlyze.loc');
ROUTE;
        $hunk = str_replace("\n", "\r\n", str_replace('__M83_NONCE__', $nonce, $hunk))."\r\n\r\n";
        if (substr_count($routes, $hunk) !== 1) {
            throw new RuntimeException('M83 correction requires exactly the reviewed bounded read-only nonce route hunk.');
        }
        $reversed = str_replace($hunk, '', $routes, $count);
        if ($count !== 1 || $reversed !== $saved
            || hash('sha256', $reversed) !== $profile['raw_sha256']
            || hash('sha256', str_replace("\r\n", "\n", $reversed)) !== $profile['lf_sha256']) {
            throw new RuntimeException('M83 correction nonce route reversal did not restore exact paired BEFORE raw/LF bytes.');
        }
    }

    private function assertInstalledProofRoute(string $path, array $profile, array &$sources): void
    {
        $proof = M83LocalTargetGuard::readFreshProof($this->localProof);
        $nonce = $proof['nonce'];
        if (($proof['routes_before_file'] ?? null) !== 'routes-before-'.$nonce.'.bin') {
            throw new RuntimeException('M83 correction proof route snapshot basename differs.');
        }
        $snapshot = $this->privateDirectory.'/'.$proof['routes_before_file'];
        self::fileHash($snapshot); self::fileHash($path);
        self::assertProofRouteBytes(file_get_contents($path), file_get_contents($snapshot), $proof, $this->localProof, $profile);
        $sources['private:'.$this->localProof] = self::fileHash($this->privateDirectory.'/'.$this->localProof);
        $sources['private:'.$proof['routes_before_file']] = self::fileHash($snapshot);
        $sources['served:routes/api.php'] = self::fileHash($path);
    }

    /** Fresh actual ROOT/WT bytes, preserved foreign EOL, visuals and historical frozen sources. */
    private function assertServedSources(string $root, array $next, array &$sources): void
    {
        $normal = static fn ($p) => strtolower(str_replace('\\', '/', (string) realpath($p)));
        $roots = ['served_root' => M83LocalTargetGuard::ROOT, 'worktree' => M83ContentPatch::SOURCE_WORKTREE];
        if (!in_array($normal($root), array_map($normal, array_values($roots)), true)
            || $normal($this->privateDirectory) !== $normal($roots['served_root'].'/storage/app/seo-m83-local')) {
            throw new RuntimeException('M83 correction configured source/private roots differ.');
        }
        $before = self::read($this->privateDirectory, M83ContentPatch::BEFORE_FILE, M83ContentPatch::BEFORE_SHA);
        $paired = self::read($this->privateDirectory, self::SOURCE_BEFORE, self::SOURCE_BEFORE_SHA);
        $fixture = self::read($root, self::SOURCE_FIXTURE, self::SOURCE_FIXTURE_SHA);
        $syncPath = $this->privateDirectory.'/'.self::SYNC_RECEIPT;
        $sync = json_decode(file_get_contents($syncPath), true, flags: JSON_THROW_ON_ERROR);
        $contract = json_decode(file_get_contents($root.'/'.Package::CONTRACT), true, flags: JSON_THROW_ON_ERROR);
        $expected = M83ContentPatch::syncPaths($next, $contract); $actual = array_column($sync['files'] ?? [], 'path'); sort($actual);
        if (($sync['base_sha'] ?? null) !== Package::BASE_SHA || $actual !== $expected
            || ($sync['served_root'] ?? null) !== $roots['served_root']
            || ($sync['source_worktree'] ?? null) !== $roots['worktree']
            || ($sync['visual_before'] ?? null) !== ['file' => M83ContentPatch::BEFORE_FILE, 'sha256' => M83ContentPatch::BEFORE_SHA]
            || count($paired['files'] ?? []) !== 3333 || count($fixture['files'] ?? []) !== 8
            || count($before['visual_hashes'] ?? []) !== 326 || count($before['frozen_hashes'] ?? []) !== 2826) {
            throw new RuntimeException('M83 correction fresh source-sync/independent source profile scope differs.');
        }
        $byPath = [];
        foreach ($sync['files'] as $record) {
            $path = $record['path']; $byPath[$path] = $record;
            foreach (['served_root' => 'root_after_sha256', 'worktree' => 'worktree_sha256'] as $context => $field) {
                if (self::fileHash($roots[$context].'/'.$path) !== ($record[$field] ?? null)) {
                    throw new RuntimeException('M83 correction source-sync actual raw bytes differ: '.$path);
                }
            }
            $a = file_get_contents($roots['served_root'].'/'.$path); $b = file_get_contents($roots['worktree'].'/'.$path);
            if ($path !== M83ContentPatch::CONTROLLER_PATH && $a !== $b
                && !(str_ends_with($path, '.php') && str_replace("\r\n", "\n", $a) === str_replace("\r\n", "\n", $b))) {
                throw new RuntimeException('M83 correction source mirror differs beyond preserved PHP EOL.');
            }
        }
        $shared = array_column($fixture['files'], null, 'path'); $definitions = array_column($next['targets'], 'path');
        foreach ($paired['files'] as $path => $contexts) {
            foreach ($roots as $context => $directory) {
                $profile = $contexts[$context]; $file = $directory.'/'.$path;
                if ($path === 'routes/api.php' && $context === 'served_root') {
                    $this->assertInstalledProofRoute($file, $profile, $sources);
                    continue;
                }
                if ($path === '.gitattributes' && $context === 'worktree') {
                    $bytes = file_get_contents($file);
                    if (!is_string($bytes) || is_link($file)
                        || hash('sha256', substr($bytes, 0, $profile['bytes'])) !== $profile['raw_sha256']) {
                        throw new RuntimeException('M83 correction delivery attributes lost the exact foreign prefix.');
                    }
                    continue;
                }
                if (isset($shared[$path])) {
                    $expectedSha = $shared[$path]['profiles'][$context]['after_raw_sha256'] ?? null;
                } elseif (in_array($path, $definitions, true)) {
                    $expectedSha = $byPath[$path][$context === 'served_root' ? 'root_after_sha256' : 'worktree_sha256'] ?? null;
                } else {
                    if (($profile['exists'] ?? null) === false) {
                        if (file_exists($file) || is_link($file)) { throw new RuntimeException('M83 correction previously absent source appeared.'); }
                        continue;
                    }
                    $expectedSha = $profile['raw_sha256'] ?? null;
                }
                if (!is_string($expectedSha) || self::fileHash($file) !== $expectedSha) {
                    throw new RuntimeException('M83 correction independent raw source profile differs: '.$path);
                }
            }
        }
        foreach ([$before['visual_hashes'], $before['frozen_hashes']] as $hashes) {
            foreach ($hashes as $path => $sha) {
                if (self::fileHash($roots['served_root'].'/'.$path) !== $sha) {
                    throw new RuntimeException('M83 correction visual/historical frozen source changed.');
                }
            }
        }
        foreach ($before['schema'] as $table => $columns) {
            if ($this->db->getSchemaBuilder()->getColumns($table) !== $columns) {
                throw new RuntimeException('M83 correction captured SQL schema changed.');
            }
        }
        foreach ([M83ContentPatch::BEFORE_FILE, self::SOURCE_BEFORE, self::BASELINE, self::SYNC_RECEIPT] as $file) {
            $sources['private:'.$file] = self::fileHash($this->privateDirectory.'/'.$file);
        }
    }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath); $connection = $this->connection();
        [$manifest, $previous, $next] = self::revision($root);
        $this->previousBody = $previous['targets'][4]['after']['page']['blocks'][1]['body'];
        $this->nextBody = $next['targets'][4]['after']['page']['blocks'][1]['body'];
        $baseline = $this->baselineEvidence();
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => static::NAMES, 'connection' => $connection,
            'sources' => [], 'pages' => [], 'updates' => [], 'inserts' => []];
        foreach (self::sourcePaths($root) as $path) { $plan['sources'][$path] = self::fileHash($root.'/'.$path); }
        if ($this->db->getDriverName() === 'mysql') { $this->assertServedSources($root, $next, $plan['sources']); }
        $target = null;
        foreach (self::NAMES as $i => $owner) {
            $pages = $this->rows('pages', static fn ($q) => $q->where('seeder', $owner), $lock);
            if (count($pages) !== 1) { throw new RuntimeException('M83 correction raw owner is missing/ambiguous.'); }
            $page = $pages[0]; $chain = $this->categoryChain($page['page_category_id'], $lock);
            if ($page['id'] !== Package::PAGE_IDS[$i] || $page['page_category_id'] !== Package::CATEGORY_IDS[$i]
                || $page['slug'] !== Package::SLUGS[$i] || $page['type'] !== 'theory'
                || array_column($chain, 'slug') !== Package::ANCESTRIES[$i]) {
                throw new RuntimeException('M83 correction exact Page/category identity differs.');
            }
            $blocks = $this->rows('text_blocks', static fn ($q) => $q->where('page_id', $page['id']), $lock);
            $plan['pages'][$owner] = ['page' => $page, 'category_ancestry' => $chain, 'blocks' => $blocks,
                'relations' => $this->relations($page, $blocks, $lock)];
            foreach ($blocks as $row) {
                if ($row['id'] !== self::TARGET_ID && $row['uuid'] !== self::TARGET_UUID) { continue; }
                if ($target !== null || $i !== 4) { throw new RuntimeException('M83 correction target is duplicated or belongs to another owner.'); }
                $expected = $manifest['row'] + ['page_id' => 21, 'page_category_id' => 2, 'seeder' => self::NAMES[4]];
                foreach ($expected as $field => $value) {
                    if (!array_key_exists($field, $row) || $row[$field] !== $value) {
                        throw new RuntimeException('M83 correction target metadata differs: '.$field);
                    }
                }
                $target = $row;
            }
        }
        if ($target === null) { throw new RuntimeException('M83 correction target body is missing.'); }
        $plan['state'] = $this->candidate($plan['updates'], 'text_blocks', $target, self::NAMES[4],
            ['body' => $this->previousBody], ['body' => $this->nextBody]);
        if (($plan['state'] === 'before' && count($plan['updates']) !== 1)
            || ($plan['state'] === 'after' && $plan['updates'] !== [])
            || $this->correctionFingerprints(normalize: true) !== ($baseline['sql_after'] ?? null)) {
            throw new RuntimeException('M83 correction raw SQL changed outside the one reviewed body cell.');
        }
        $plan['protected'] = $this->protectedFingerprints([self::TARGET_ID]);
        ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }
}
