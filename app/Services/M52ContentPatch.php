<?php

namespace App\Services;

use App\Support\M52PassiveBasicsPackage as Package;
use App\Support\TheorySentenceBuilderAcceptance;
use RuntimeException;

/**
 * Finite M52 content-only planner. All writes/locks/exclusive backup/postconditions
 * use unchanged M26 primitives; existing questions, options, answers and links are read-only.
 */
class M52ContentPatch extends M26ContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'd3d7abe58cff1439e4e11568e387a0ca3b818dfa11470b54981b78e6a25ff106';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = 'd871b45ffb1ae33085dc30b60b4cf7a553da23236afd02ec36445a921d20d256';
    public const CONTROLLER_WT_AFTER_SHA = 'e8b6dc81c4026872a40065310401bf87e42ef49c8e05b671a8eef218c3d1a59d';
    public const SYNC_CODE_PATHS = [
        'app/Support/M52PassiveBasicsPackage.php', 'app/Services/M52ContentPatch.php',
        'app/Services/M52LocalTargetGuard.php', 'app/Console/Commands/PatchM52Content.php',
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
    ];
    protected const ID = 'm52-passive-basics-standard-view-v1';

    protected function allowedPageUpdateFields(): array { return ['text']; }
    protected function localGuard(): string { return M52LocalTargetGuard::class; }
    protected function expectedInsertCount(): int { return count(Package::OWNERS); }
    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        throw new RuntimeException('M52 restore is outside the approved scope; no writes.');
    }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        if ($this->db->getDriverName() === 'mysql') {
            M49LocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget !== 'gramlyze.loc' || $this->localProof === null) {
                throw new RuntimeException('M52 working preview/apply requires explicit gramlyze.loc and fresh nonce proof.');
            }
        }

        return parent::connection($expected, $writing);
    }

    public static function bankPaths(array $contract): array
    {
        $paths = [];
        foreach ($contract['targets'] as $target) {
            foreach ($target['bank_seeders'] as $bank) {
                if (preg_match('/^Database\\\\Seeders\\\\V3\\\\[A-Za-z0-9_]+\\\\[A-Za-z0-9_]+$/D', $bank) !== 1) {
                    throw new RuntimeException('M52 independent existing-bank path is invalid.');
                }
                $paths[] = 'database/seeders/'.str_replace('\\', '/', substr($bank, strlen('Database\\Seeders\\'))).'/definition.json';
            }
        }
        $paths = array_values(array_unique($paths)); sort($paths);

        return $paths;
    }

    public static function syncPaths(array $patch, ?array $contract = null): array
    {
        $contract ??= Package::load()[3];
        $paths = [...self::SYNC_CODE_PATHS, Package::BEFORE, Package::SOURCE, Package::MASTER, Package::CONTRACT,
            ...array_column($patch['targets'], 'path'), ...self::bankPaths($contract)];
        $paths = array_values(array_unique($paths)); sort($paths);

        return $paths;
    }

    public static function sourcePaths(array $patch, array $contract, ?string $root = null): array
    {
        $root ??= base_path();
        $paths = [...self::syncPaths($patch, $contract),
            'app/Services/M26ContentPatch.php', 'app/Services/M11LocalTargetGuard.php',
            'app/Services/M49LocalTargetGuard.php', 'app/Services/M49ContentPatch.php',
            'app/Services/M51ContentPatch.php', 'app/Services/PronounContentRepair.php',
            'app/Services/PracticeCompletionPatch.php', 'app/Support/M49AdviceModalsPackage.php',
            'app/Support/M26DetailPackage.php', 'app/Support/TextBlock/TextBlockUuidGenerator.php',
            'app/Support/TheorySentenceBuilderAcceptance.php', 'app/Http/Controllers/PageController.php',
        ];
        foreach ($patch['targets'] as $target) {
            foreach (glob(dirname($root.'/'.$target['path']).'/localizations/*.json') ?: [] as $path) {
                $paths[] = substr(str_replace('\\', '/', $path), strlen(str_replace('\\', '/', $root)) + 1);
            }
        }
        $paths = array_values(array_unique($paths)); sort($paths);

        return $paths;
    }

    public static function assertDefinitionScope(array $before, array $after, array $target, int $i): void
    {
        $owner = self::NAMES[$i] ?? null;
        if ($owner === null || ($target['identity'] ?? null) !== $owner || ($before['seeder']['class'] ?? null) !== $owner
            || ($before['page']['locale'] ?? null) !== 'uk' || ($before['type'] ?? null) !== 'theory'
            || ($before['slug'] ?? null) !== Package::SLUGS[$i] || ($target['slug'] ?? null) !== Package::SLUGS[$i]
            || ($before['page']['category']['slug'] ?? null) !== 'passive-voice' || ($target['ancestry'] ?? null) !== ['passive-voice']
            || ($target['page_binding'] ?? null) !== ['id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i]]
            || ($target['path'] ?? null) !== TheorySentenceBuilderAcceptance::definitionPath($owner)
            || ($target['local_url'] ?? null) !== 'http://gramlyze.loc/theory/passive-voice/'.Package::SLUGS[$i]
            || count($before['page']['blocks']) !== Package::CONTINUING_COUNTS[$i]
            || count($after['page']['blocks']) !== count($before['page']['blocks']) + 1) {
            throw new RuntimeException('M52 exact definition owner/category/level/actual-slot scope differs.');
        }
        $protected = $after; $protected['page']['blocks'] = $before['page']['blocks'];
        foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $before['page'][$field]; }
        if ($protected !== $before) { throw new RuntimeException('M52 protected definition metadata changed.'); }
        foreach ($before['page']['blocks'] as $slot => $old) {
            $new = $after['page']['blocks'][$slot]; unset($old['body'], $new['body']);
            if ($old !== $new) { throw new RuntimeException('M52 continuing block metadata/order/UUID changed.'); }
        }
        $practice = $after['page']['blocks'][count($before['page']['blocks'])];
        if ($practice['type'] !== 'practice-set' || $practice['column'] !== 'left'
            || ($practice['level'] ?? null) !== Package::LEVELS[$i]
            || !str_starts_with($practice['uuid_key'] ?? '', 'm52:'.Package::SLUGS[$i].':')) {
            throw new RuntimeException('M52 appended native practice identity/technical level differs.');
        }
    }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath); $connection = $this->connection();
        [$before, $patch, $master, $contract] = Package::load($root); Package::validate($before, $patch, $master, $contract);
        $this->assertCapacity($patch);
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => self::NAMES, 'connection' => $connection,
            'sources' => [], 'pages' => [], 'updates' => [], 'inserts' => []];
        $baseline = $this->localTarget === null ? null : $this->assertServedSources($root, $patch, $contract);
        if ($baseline !== null) {
            foreach ([self::BEFORE_FILE, self::SYNC_RECEIPT, self::CONSUMERS_FILE] as $file) {
                $plan['sources']['private:'.$file] = self::fileHash($this->privateDirectory.'/'.$file);
            }
        }
        foreach (self::sourcePaths($patch, $contract, $root) as $path) { $plan['sources'][$path] = self::fileHash($root.'/'.$path); }
        $states = []; $targetIds = [];
        foreach ($patch['targets'] as $i => $target) {
            $name = $target['identity']; $old = $before['targets'][$i]['before']; $new = $target['after'];
            self::assertDefinitionScope($old, $new, $target, $i);
            if ($baseline !== null && ($baseline['definitions'][$name]['definition'] ?? null) !== $old) {
                throw new RuntimeException('M52 public source BEFORE differs from independent private source capture.');
            }
            if (json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR) !== $new) {
                throw new RuntimeException('M52 active versioned source differs from exact AFTER.');
            }
            $owners = $this->rows('pages', static fn ($query) => $query->where('seeder', $name), $lock);
            if (count($owners) !== 1) { throw new RuntimeException('M52 Page owner is missing or ambiguous.'); }
            $page = $owners[0]; $chain = $this->categoryChain($page['page_category_id'], $lock);
            if ($page['id'] !== Package::PAGE_IDS[$i] || $page['page_category_id'] !== Package::CATEGORY_IDS[$i]
                || $page['slug'] !== $old['slug'] || $page['title'] !== $old['page']['title'] || $page['type'] !== 'theory'
                || array_column($chain, 'slug') !== ['passive-voice']) { throw new RuntimeException('M52 Page/category identity differs.'); }
            $rows = $this->rows('text_blocks', static fn ($query) => $query->where('page_id', $page['id']), $lock);
            if ($baseline !== null) { M49ContentPatch::assertMetadataBaseline($baseline['pages'][$name], $page, $rows); }
            $uk = array_values(array_filter($rows, static fn ($row) => $row['locale'] === 'uk'));
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'heading' => null,
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle',
                'body' => $old['page']['subtitle_html']];
            $afterSubtitle = $subtitle; $afterSubtitle['body'] = $new['page']['subtitle_html'];
            $oldConfigs = [$subtitle, ...$old['page']['blocks']]; $newConfigs = [$afterSubtitle, ...$new['page']['blocks']];
            $rowStates = [];
            $state = $this->candidate($plan['updates'], 'pages', $page, $name,
                ['text' => $old['page']['subtitle_text']], ['text' => $new['page']['subtitle_text']]);
            if ($state !== null) { $rowStates[] = $state; }
            foreach ($oldConfigs as $position => $config) {
                $uuid = $this->resolveUuid($name, $config, $position);
                if ($uuid !== $this->resolveUuid($name, $newConfigs[$position], $position)) {
                    throw new RuntimeException('M52 continuing UUID differs.');
                }
                $matches = $this->rows('text_blocks', static fn ($query) => $query->where('uuid', $uuid), $lock);
                if (count($matches) !== 1) { throw new RuntimeException('M52 continuing row is missing or ambiguous.'); }
                $row = $matches[0]; $this->assertOwner($row, $page, $name, $position);
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                    if ($row[$field] !== ($config[$field] ?? null)) { throw new RuntimeException('M52 continuing row metadata differs: '.$field); }
                }
                $targetIds[] = $row['id'];
                $state = $this->candidate($plan['updates'], 'text_blocks', $row, $name,
                    ['body' => $config['body']], ['body' => $newConfigs[$position]['body']]);
                if ($state !== null) { $rowStates[] = $state; }
            }
            $rowStates = array_values(array_unique($rowStates));
            if (count($rowStates) !== 1) { throw new RuntimeException('M52 partial/manual owner state; no writes.'); }
            $state = $rowStates[0]; $position = count($oldConfigs); $practice = $newConfigs[$position];
            $uuid = $this->resolveUuid($name, $practice, $position);
            $fields = ['uuid' => $uuid, 'page_id' => $page['id'], 'page_category_id' => $page['page_category_id'],
                'locale' => 'uk', 'type' => $practice['type'], 'column' => $practice['column'], 'heading' => $practice['heading'] ?? null,
                'css_class' => $practice['css_class'] ?? null, 'sort_order' => $position,
                'body' => $practice['body'], 'level' => $practice['level'], 'seeder' => $name];
            $matches = $this->rows('text_blocks', static fn ($query) => $query->where('uuid', $uuid), $lock);
            if ($state === 'before') {
                if ($matches !== []) { throw new RuntimeException('M52 new practice UUID is collided or partially present.'); }
                $plan['inserts'][] = ['table' => 'text_blocks', 'seeder' => $name, 'fields' => $fields, 'sha256' => self::digest($fields)];
            } else {
                if (count($matches) !== 1) { throw new RuntimeException('M52 accepted practice is missing or duplicated.'); }
                foreach ($fields as $field => $value) {
                    if ($matches[0][$field] !== $value) { throw new RuntimeException('M52 appended practice row is edited: '.$field); }
                }
                if (empty($matches[0]['created_at']) || $matches[0]['created_at'] !== $matches[0]['updated_at']) {
                    throw new RuntimeException('M52 appended practice creation timestamps differ.');
                }
                $targetIds[] = $matches[0]['id'];
            }
            if (count($uk) !== count($oldConfigs) + (int) ($state === 'after')) { throw new RuntimeException('M52 unexpected UK row count.'); }
            $relations = $this->relations($page, $rows, $lock);
            $this->assertExistingPools($relations['questions'], $contract['targets'][$i], $baseline['pages'][$name] ?? null);
            $plan['pages'][$name] = ['page' => $page, 'category_ancestry' => $chain, 'blocks' => $rows, 'relations' => $relations];
            $states[] = $state;
        }
        if (count(array_unique($states)) !== 1) { throw new RuntimeException('M52 partial package; no writes.'); }
        $plan['state'] = $states[0];
        if (($plan['state'] === 'before' && count($plan['inserts']) !== $this->expectedInsertCount())
            || ($plan['state'] === 'after' && ($plan['updates'] !== [] || $plan['inserts'] !== []))) {
            throw new RuntimeException('M52 finite insert/no-op allowlist differs.');
        }
        $plan['protected'] = $this->protectedFingerprints($targetIds); ksort($plan['sources']);
        $plan['sha256'] = self::digest($plan);

        return $plan;
    }

    private function assertOwner(array $row, array $page, string $owner, int $position): void
    {
        if ($row['seeder'] !== $owner || $row['locale'] !== 'uk' || $row['page_id'] !== $page['id']
            || $row['page_category_id'] !== $page['page_category_id'] || (int) $row['sort_order'] !== $position) {
            throw new RuntimeException('M52 continuing row owner/locale/order differs.');
        }
    }

    private function assertCapacity(array $patch): void
    {
        if ($this->db->getDriverName() !== 'mysql') { return; }
        $column = (array) $this->db->selectOne('SELECT DATA_TYPE AS kind, CHARACTER_OCTET_LENGTH AS max_bytes '
            .'FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$this->db->getDatabaseName(), 'text_blocks', 'body']);
        if (!in_array($column['kind'] ?? null, ['tinytext', 'text', 'mediumtext', 'longtext'], true)
            || !is_numeric($column['max_bytes'] ?? null)) { throw new RuntimeException('M52 actual body column capacity is unknown.'); }
        foreach ($patch['targets'] as $target) {
            PracticeCompletionPatch::assertBodyCapacity($target['after']['page']['subtitle_html'], (int) $column['max_bytes']);
            foreach ($target['after']['page']['blocks'] as $block) {
                PracticeCompletionPatch::assertBodyCapacity($block['body'], (int) $column['max_bytes']);
            }
        }
    }

    /** Page-wide eligibility is read-only; exact CEFR and independently declared banks are mandatory. */
    private function assertExistingPools(array $questions, array $contract, ?array $baseline): void
    {
        $ids = [];
        foreach ($contract['bank_seeders'] as $bank) {
            $eligible = array_values(array_filter($questions, static fn ($question) => $question['seeder'] === $bank
                && (string) $question['type'] === '4' && $question['level'] === $contract['level']));
            usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            if ($baseline !== null && array_column($eligible, 'id') !== ($baseline['bank_inventory'][$bank]['eligible_ids_uuid_order'] ?? null)) {
                throw new RuntimeException('M52 existing page-wide exact bank/type/level pool changed after capture.');
            }
            array_push($ids, ...array_column($eligible, 'id'));
        }
        if (count(array_unique($ids)) !== count($ids) || count($ids) < 5) {
            throw new RuntimeException('M52 exact thematic pool is short or repeats question IDs.');
        }
    }

    /** Protect every actual table, including all question families, sessions and progress. */
    protected function protectedFingerprints(array $targetIds): array
    {
        $schema = $this->db->getDriverName() === 'sqlite' ? 'main' : $this->db->getDatabaseName();
        $tables = $this->db->getSchemaBuilder()->getTableListing(schema: $schema, schemaQualified: false); sort($tables);
        $out = [];
        foreach ($tables as $table) {
            $columns = $this->db->getSchemaBuilder()->getColumnListing($table); $query = $this->db->table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            if ($table === 'text_blocks') { $query->whereNotIn('id', $targetIds); }
            $hash = hash_init('sha256'); $count = 0;
            foreach ($query->cursor() as $record) {
                $row = (array) $record;
                if ($table === 'pages' && in_array($row['id'], Package::PAGE_IDS, true)) { unset($row['text']); }
                hash_update($hash, self::digest($row)."\n"); ++$count;
            }
            $out[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
        }

        return $out;
    }

    public static function fileHash(string $path): string
    {
        if (!is_file($path) || is_link($path)) { throw new RuntimeException('M52 reviewed source/evidence is missing or linked.'); }
        $sha = hash_file('sha256', $path);
        if (!is_string($sha)) { throw new RuntimeException('M52 source/evidence hash is unavailable.'); }

        return $sha;
    }

    private function assertServedSources(string $root, array $patch, array $contract): array
    {
        $normal = static fn ($path) => strtolower(str_replace('\\', '/', (string) realpath($path)));
        if (!in_array($normal($root), [$normal(M52LocalTargetGuard::ROOT), $normal(self::SOURCE_WORKTREE)], true)) {
            throw new RuntimeException('M52 source/application root differs from the finite ROOT/WT allowlist.');
        }
        foreach ([self::BEFORE_FILE => self::BEFORE_SHA, self::CONSUMERS_FILE => self::CONSUMERS_SHA] as $file => $sha) {
            if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || !hash_equals($sha, self::fileHash($this->privateDirectory.'/'.$file))) {
                throw new RuntimeException('M52 independent BEFORE/consumer receipt is not reviewed or changed.');
            }
        }
        $before = json_decode(file_get_contents($this->privateDirectory.'/'.self::BEFORE_FILE), true, flags: JSON_THROW_ON_ERROR);
        $consumers = json_decode(file_get_contents($this->privateDirectory.'/'.self::CONSUMERS_FILE), true, flags: JSON_THROW_ON_ERROR);
        M51ContentPatch::assertConsumerCapture($before, $consumers); // Pure unchanged capture policy only; not its planner.
        if (($before['base_sha'] ?? null) !== Package::BASE_SHA || ($before['served_root'] ?? null) !== M52LocalTargetGuard::ROOT
            || ($before['source_worktree'] ?? null) !== self::SOURCE_WORKTREE
            || ($before['sql_before'] ?? null) !== ($before['sql_after_http'] ?? null)
            || array_keys($before['pages'] ?? []) !== self::NAMES || empty($consumers['route_declarations']) || empty($consumers['http'])) {
            throw new RuntimeException('M52 independent source/DB/HTTP BEFORE scope differs.');
        }
        foreach ($before['http'] as $record) {
            if ($record['status'] !== 200 || empty($record['sha256'])) { throw new RuntimeException('M52 required theory/course BEFORE was not accessible.'); }
            if ($record['kind'] === 'course' && empty($record['course_theory_html_sha256'])) {
                throw new RuntimeException('M52 independent accessible course learning DOM is missing.');
            }
        }
        $kinds = array_count_values(array_column($consumers['http'], 'kind'));
        if (($kinds['course'] ?? 0) < 3 || ($kinds['ssr'] ?? 0) < 3 || ($kinds['json'] ?? 0) < 3) {
            throw new RuntimeException('M52 independent course/standalone consumer capture is incomplete.');
        }
        $sync = json_decode(file_get_contents($this->privateDirectory.'/'.self::SYNC_RECEIPT), true, flags: JSON_THROW_ON_ERROR);
        $paths = array_column($sync['files'] ?? [], 'path'); sort($paths);
        if (($sync['base_sha'] ?? null) !== Package::BASE_SHA || $paths !== self::syncPaths($patch, $contract)
            || ($sync['source_worktree'] ?? null) !== self::SOURCE_WORKTREE || ($sync['served_root'] ?? null) !== M52LocalTargetGuard::ROOT
            || ($sync['visual_before'] ?? null) !== ['file' => self::BEFORE_FILE, 'sha256' => self::BEFORE_SHA]) {
            throw new RuntimeException('M52 exact reviewed source-sync receipt differs.');
        }
        foreach ($sync['files'] as $record) {
            $a = file_get_contents(M52LocalTargetGuard::ROOT.'/'.$record['path']); $b = file_get_contents(self::SOURCE_WORKTREE.'/'.$record['path']);
            if (self::fileHash(M52LocalTargetGuard::ROOT.'/'.$record['path']) !== ($record['root_after_sha256'] ?? null)
                || self::fileHash(self::SOURCE_WORKTREE.'/'.$record['path']) !== ($record['worktree_sha256'] ?? null)) {
                throw new RuntimeException('M52 served/source bytes drifted after reviewed sync.');
            }
            if ($record['path'] === self::CONTROLLER_PATH) {
                foreach ([self::CONTROLLER_ROOT_AFTER_SHA, self::CONTROLLER_WT_AFTER_SHA] as $sha) {
                    if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1) { throw new RuntimeException('M52 exact foreign-inclusive controller hunks are not reviewed.'); }
                }
                if (hash('sha256', $a) !== self::CONTROLLER_ROOT_AFTER_SHA || hash('sha256', $b) !== self::CONTROLLER_WT_AFTER_SHA
                    || ($record['root_before_sha256'] ?? null) !== ($before['shared_source_hashes'][self::CONTROLLER_PATH]['root'] ?? null)) {
                    throw new RuntimeException('M52 controller differs from exact approved ROOT/WT bytes.');
                }
            } elseif ($a !== $b && !(str_ends_with($record['path'], '.php') && str_replace("\r\n", "\n", $a) === str_replace("\r\n", "\n", $b))) {
                throw new RuntimeException('M52 source mirror differs beyond preserved PHP EOL.');
            }
        }
        $unchangedController = 'app/Http/Controllers/PageController.php';
        if (self::fileHash(M52LocalTargetGuard::ROOT.'/'.$unchangedController) !== $before['shared_source_hashes'][$unchangedController]['root']
            || self::fileHash(self::SOURCE_WORKTREE.'/'.$unchangedController) !== $before['shared_source_hashes'][$unchangedController]['worktree']) {
            throw new RuntimeException('M52 cannot add a global title heuristic or modify PageController.');
        }
        if (empty($before['visual_hashes']) || empty($before['frozen_hashes'])) { throw new RuntimeException('M52 visual/frozen BEFORE is incomplete.'); }
        foreach ([$before['visual_hashes'], $before['frozen_hashes']] as $hashes) {
            foreach ($hashes as $path => $sha) {
                if (!hash_equals($sha, self::fileHash(M52LocalTargetGuard::ROOT.'/'.$path))) {
                    throw new RuntimeException('M52 visual or historical frozen source changed.');
                }
            }
        }

        return $before;
    }
}
