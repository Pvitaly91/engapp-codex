<?php

namespace App\Services;

use App\Support\TheorySentenceBuilderAcceptance;
use RuntimeException;

/**
 * Reusable content-only planner for finite reviewed native releases. The M26
 * writer owns locks, stale-plan equality, exclusive backup and transaction.
 * Question supplements must declare their own evidence and exact insert hooks.
 */
abstract class TheoryNativeContentPatch extends M26ContentPatch
{
    abstract protected static function packageClass(): string;
    abstract protected static function policy(): array;

    protected function allowedPageUpdateFields(): array { return ['text']; }
    protected function localGuard(): string { return static::policy()['guard']; }
    protected function expectedInsertCount(): int
    {
        $package = static::packageClass();
        return array_sum(array_map(static fn ($binding) => count($binding['appended_blocks'] ?? [null])
            + (($binding['before_subtitle']['mode'] ?? null) === 'captured-absent' ? 1 : 0), $package::profile()['bindings']));
    }
    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        throw new RuntimeException('Native release restore is outside this approved scope; no writes.');
    }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        if ($this->db->getDriverName() === 'mysql') {
            M49LocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget !== 'gramlyze.loc' || $this->localProof === null) {
                throw new RuntimeException('Native working preview/apply requires gramlyze.loc and fresh nonce proof.');
            }
        }
        return parent::connection($expected, $writing);
    }

    public static function bankPaths(array $contract): array
    {
        $paths = []; $overrides = static::policy()['bank_source_overrides'] ?? [];
        foreach ($contract['targets'] as $target) {
            foreach ($target['bank_seeders'] as $bank) {
                if (!is_string($bank) || preg_match('/^Database\\\\Seeders\\\\V3\\\\[A-Za-z0-9_]+\\\\[A-Za-z0-9_]+$/D', $bank) !== 1) {
                    throw new RuntimeException('Native independent bank path has an invalid identity.');
                }
                $paths[] = $overrides[$bank] ?? 'database/seeders/'.str_replace('\\', '/', substr($bank, strlen('Database\\Seeders\\'))).'/definition.json';
            }
        }
        $paths = array_values(array_unique($paths)); sort($paths); return $paths;
    }

    public static function syncPaths(array $patch, ?array $contract = null): array
    {
        $package = static::packageClass(); $contract ??= $package::load()[3];
        $paths = [...static::policy()['sync_code_paths'], $package::BEFORE, $package::SOURCE, $package::MASTER, $package::CONTRACT,
            ...array_column($patch['targets'], 'path'), ...static::bankPaths($contract)];
        $paths = array_values(array_unique($paths)); sort($paths); return $paths;
    }

    public static function sourcePaths(array $patch, array $contract, ?string $root = null): array
    {
        $root ??= base_path(); $paths = [...static::syncPaths($patch, $contract),
            'app/Services/M26ContentPatch.php', 'app/Services/M11LocalTargetGuard.php',
            'app/Services/M49LocalTargetGuard.php', 'app/Services/M49ContentPatch.php', 'app/Services/M51ContentPatch.php',
            'app/Services/PronounContentRepair.php', 'app/Services/PracticeCompletionPatch.php',
            'app/Support/M49AdviceModalsPackage.php', 'app/Support/M26DetailPackage.php',
            'app/Support/TextBlock/TextBlockUuidGenerator.php', 'app/Support/TheorySentenceBuilderAcceptance.php',
            'app/Http/Controllers/PageController.php', ...(static::policy()['extra_source_paths'] ?? [])];
        foreach ($patch['targets'] as $target) {
            foreach (glob(dirname($root.'/'.$target['path']).'/localizations/*.json') ?: [] as $path) {
                $paths[] = substr(str_replace('\\', '/', $path), strlen(str_replace('\\', '/', $root)) + 1);
            }
        }
        $paths = array_values(array_unique($paths)); sort($paths); return $paths;
    }

    public static function assertDefinitionScope(array $before, array $after, array $target, int $i): void
    {
        $package = static::packageClass(); $binding = $package::profile()['bindings'][$i] ?? null;
        $added = count($binding['appended_blocks'] ?? [null]);
        if ($binding === null || ($target['identity'] ?? null) !== $binding['identity']
            || ($before['seeder']['class'] ?? null) !== $binding['identity'] || ($before['page']['locale'] ?? null) !== 'uk'
            || ($before['type'] ?? null) !== 'theory' || ($before['slug'] ?? null) !== $binding['slug']
            || ($target['slug'] ?? null) !== $binding['slug'] || ($target['ancestry'] ?? null) !== $binding['ancestry']
            || ($before['page']['category']['slug'] ?? null) !== end($binding['ancestry'])
            || ($target['path'] ?? null) !== TheorySentenceBuilderAcceptance::definitionPath($binding['identity'])
            || ($target['page_binding'] ?? null) !== ['id' => $binding['page_id'], 'page_category_id' => $binding['category_id']]
            || ($target['local_url'] ?? null) !== $binding['local_url']
            || count($before['page']['blocks']) !== count($binding['continuing_levels'])
            || ($added < 1 && !(new \App\Support\TheoryNativeContentRelease($package::profile()))->existingPractice($binding, $before))
            || count($after['page']['blocks']) !== count($before['page']['blocks']) + $added) {
            throw new RuntimeException('Native exact definition/owner/category/actual slots differ.');
        }
        $protected = $after; $protected['page']['blocks'] = $before['page']['blocks'];
        foreach (['subtitle_html', 'subtitle_text'] as $field) {
            if (array_key_exists($field, $before['page'])) { $protected['page'][$field] = $before['page'][$field]; }
            else { unset($protected['page'][$field]); }
        }
        if ($protected !== $before) { throw new RuntimeException('Native protected definition metadata changed.'); }
        $projection = (new \App\Support\TheoryNativeContentRelease($package::profile()))->projectedBefore($before);
        foreach ($before['page']['blocks'] as $slot => $old) {
            $new = $after['page']['blocks'][$slot];
            if (($old['level'] ?? null) !== $binding['continuing_levels'][$slot]) {
                throw new RuntimeException('Native independent continuing level vector differs.');
            }
            $old = $projection['page']['blocks'][$slot]; unset($old['body'], $new['body']);
            if ($old !== $new) { throw new RuntimeException('Native continuing metadata/UUID/order changed.'); }
        }
        foreach ($binding['appended_blocks'] ?? [] as $offset => $config) {
            $source = $after['page']['blocks'][count($before['page']['blocks']) + $offset];
            if (($source['type'] ?? null) !== $config['type'] || ($source['column'] ?? null) !== $config['column']
                || ($source['level'] ?? null) !== $config['level']
                || ($source['uuid_key'] ?? null) !== $package::profile()['uuid_prefix'].$binding['slug'].':'.$config['key']) {
                throw new RuntimeException('Native finite appended teaching identity differs.');
            }
        }
        $practiceSlot = $binding['existing_practice']['source_index'] ?? count($after['page']['blocks']) - 1;
        $practice = $after['page']['blocks'][$practiceSlot];
        if ($practice['type'] !== 'practice-set' || $practice['column'] !== 'left'
            || ($practice['level'] ?? null) !== $binding['practice_level']
            || (!isset($binding['existing_practice'])
                && !str_starts_with($practice['uuid_key'] ?? '', $package::profile()['uuid_prefix'].$binding['slug'].':'))) {
            throw new RuntimeException('Native appended practice identity/technical level differs.');
        }
    }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath); $package = static::packageClass(); $connection = $this->connection();
        [$before, $patch, $master, $contract] = $package::load($root); $package::validate($before, $patch, $master, $contract);
        $this->assertCapacity($patch);
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => static::NAMES, 'connection' => $connection,
            'sources' => [], 'pages' => [], 'updates' => [], 'inserts' => []];
        $baseline = $this->localTarget === null ? null : $this->assertServedSources($root, $patch, $contract);
        if ($baseline !== null) {
            foreach ([static::policy()['before_file'], static::policy()['sync_receipt'], static::policy()['consumers_file']] as $file) {
                $plan['sources']['private:'.$file] = self::fileHash($this->privateDirectory.'/'.$file);
            }
        }
        foreach (static::sourcePaths($patch, $contract, $root) as $path) { $plan['sources'][$path] = self::fileHash($root.'/'.$path); }
        $states = []; $targetIds = [];
        foreach ($patch['targets'] as $i => $target) {
            $name = $target['identity']; $old = $before['targets'][$i]['before']; $new = $target['after'];
            static::assertDefinitionScope($old, $new, $target, $i);
            if ($baseline !== null && ($baseline['definitions'][$name]['definition'] ?? null) !== $old) {
                throw new RuntimeException('Native public source BEFORE differs from independent private capture.');
            }
            if (json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR) !== $new) {
                throw new RuntimeException('Native active source differs from exact AFTER.');
            }
            $owners = $this->rows('pages', static fn ($q) => $q->where('seeder', $name), $lock);
            if (count($owners) !== 1) { throw new RuntimeException('Native Page owner is missing or ambiguous.'); }
            $page = $owners[0]; $chain = $this->categoryChain($page['page_category_id'], $lock);
            if ($page['id'] !== $target['page_binding']['id'] || $page['page_category_id'] !== $target['page_binding']['page_category_id']
                || $page['slug'] !== $old['slug'] || $page['title'] !== $old['page']['title'] || $page['type'] !== 'theory'
                || array_column($chain, 'slug') !== $target['ancestry']) { throw new RuntimeException('Native Page/category identity differs.'); }
            $rows = $this->rows('text_blocks', static fn ($q) => $q->where('page_id', $page['id']), $lock);
            if ($baseline !== null) { $this->assertMetadataBaseline($baseline['pages'][$name], $page, $rows, $old, $new, $i); }
            $release = new \App\Support\TheoryNativeContentRelease($package::profile());
            $subtitleAbsent = $release->subtitleWasAbsent($old);
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'heading' => null,
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle',
                'body' => $subtitleAbsent ? null : $old['page']['subtitle_html']];
            $afterSubtitle = $subtitle; $afterSubtitle['body'] = $new['page']['subtitle_html'];
            $oldConfigs = $subtitleAbsent ? [] : [$subtitle];
            foreach ($old['page']['blocks'] as $slot => $config) {
                if ($subtitleAbsent) { $config['uuid'] = $package::profile()['bindings'][$i]['continuing_uuids'][$slot]; }
                $oldConfigs[$slot + 1] = $config;
            }
            $newConfigs = [$afterSubtitle, ...$new['page']['blocks']]; $rowStates = [];
            $state = $this->candidate($plan['updates'], 'pages', $page, $name,
                ['text' => $subtitleAbsent ? null : $old['page']['subtitle_text']], ['text' => $new['page']['subtitle_text']]);
            if ($state !== null) { $rowStates[] = $state; }
            foreach ($oldConfigs as $position => $config) {
                $uuid = $this->resolveUuid($name, $config, $position);
                if ($uuid !== $this->resolveUuid($name, $newConfigs[$position], $position)) { throw new RuntimeException('Native continuing UUID differs.'); }
                $matches = $this->rows('text_blocks', static fn ($q) => $q->where('uuid', $uuid), $lock);
                if (count($matches) !== 1) { throw new RuntimeException('Native continuing row missing or ambiguous.'); }
                $row = $matches[0]; $this->assertOwner($row, $page, $name, $position);
                $fields = $this->allowedUpdateFields(); $oldValues = []; $newValues = [];
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                    if (!in_array($field, $fields, true) && $row[$field] !== ($config[$field] ?? null)) {
                        throw new RuntimeException('Native continuing metadata differs: '.$field);
                    }
                }
                foreach ($fields as $field) { $oldValues[$field] = $config[$field] ?? null; $newValues[$field] = $newConfigs[$position][$field] ?? null; }
                $targetIds[] = $row['id'];
                $state = $this->candidate($plan['updates'], 'text_blocks', $row, $name, $oldValues, $newValues);
                if ($state !== null) { $rowStates[] = $state; }
            }
            $rowStates = array_values(array_unique($rowStates));
            if (count($rowStates) !== 1) { throw new RuntimeException('Native partial/manual owner state; no writes.'); }
            $state = $rowStates[0]; $addedCount = count($newConfigs) - count($oldConfigs);
            foreach (array_diff_key($newConfigs, $oldConfigs) as $position => $source) {
                $uuid = $this->resolveUuid($name, $source, $position);
                $fields = ['uuid' => $uuid, 'page_id' => $page['id'], 'page_category_id' => $page['page_category_id'], 'locale' => 'uk',
                    'type' => $source['type'], 'column' => $source['column'], 'heading' => $source['heading'] ?? null,
                    'css_class' => $source['css_class'] ?? null, 'sort_order' => $position,
                    'body' => $source['body'], 'level' => $source['level'] ?? null, 'seeder' => $name];
                $matches = $this->rows('text_blocks', static fn ($q) => $q->where('uuid', $uuid), $lock);
                if ($state === 'before') {
                    if ($matches !== []) { throw new RuntimeException('Native new block UUID collides or is partially present.'); }
                    $plan['inserts'][] = ['table' => 'text_blocks', 'seeder' => $name, 'fields' => $fields, 'sha256' => self::digest($fields)];
                } else {
                    if (count($matches) !== 1) { throw new RuntimeException('Native applied block missing or duplicated.'); }
                    foreach ($fields as $field => $value) {
                        if ($matches[0][$field] !== $value) { throw new RuntimeException('Native appended block edited: '.$field); }
                    }
                    if (empty($matches[0]['created_at']) || $matches[0]['created_at'] !== $matches[0]['updated_at']) {
                        throw new RuntimeException('Native block creation timestamps differ.');
                    }
                    $targetIds[] = $matches[0]['id'];
                }
            }
            if (count(array_filter($rows, static fn ($r) => $r['locale'] === 'uk')) !== count($oldConfigs) + ($state === 'after' ? $addedCount : 0)) {
                throw new RuntimeException('Native unexpected UK row count.');
            }
            $relations = $this->relations($page, $rows, $lock);
            $this->assertExistingPools($relations['questions'], $contract['targets'][$i], $baseline['pages'][$name] ?? null);
            $plan['pages'][$name] = ['page' => $page, 'category_ancestry' => $chain, 'blocks' => $rows, 'relations' => $relations]; $states[] = $state;
        }
        if (count(array_unique($states)) !== 1) { throw new RuntimeException('Native partial release; no writes.'); }
        $plan['state'] = $states[0];
        if (($plan['state'] === 'before' && count($plan['inserts']) !== $this->expectedInsertCount())
            || ($plan['state'] === 'after' && ($plan['updates'] !== [] || $plan['inserts'] !== []))) {
            throw new RuntimeException('Native content insert/no-op allowlist differs.');
        }
        $this->extendPlan($plan, $patch, $baseline, $lock);
        $plan['protected'] = $this->protectedFingerprints($targetIds); ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }

    /** Exact reviewed supplements may add evidence; the default authorises no question write. */
    protected function extendPlan(array &$plan, array $patch, ?array $baseline, bool $lock): void {}

    /** Supplement rows may point solely to the exact new practice; old relation projection stays stable. */
    protected function relations(array $page, array $blocks, bool $lock): array
    {
        $package = static::packageClass(); $i = array_search($page['seeder'], $package::OWNERS, true);
        if ($i === false) { throw new RuntimeException('Native relation owner is outside the descriptor.'); }
        $patch = $package::load(dirname($this->databasePath))[1]; $target = $patch['targets'][$i];
        $appended = [];
        foreach (array_slice($target['after']['page']['blocks'], $package::CONTINUING_COUNTS[$i], null, true) as $slot => $config) {
            $uuid = $this->resolveUuid($page['seeder'], $config, $slot + 1);
            $appended[$uuid] = ['uuid' => $uuid, 'seeder' => $page['seeder'], 'locale' => 'uk',
                'page_id' => $page['id'], 'page_category_id' => $page['page_category_id'], 'sort_order' => $slot + 1,
                'type' => $config['type'], 'column' => $config['column'], 'heading' => $config['heading'] ?? null,
                'level' => $config['level'] ?? null, 'css_class' => $config['css_class'] ?? null, 'body' => $config['body']];
        }
        if (($package::profile()['bindings'][$i]['before_subtitle']['mode'] ?? null) === 'captured-absent') {
            $old = $package::load(dirname($this->databasePath))[0]['targets'][$i]['before'];
            $config = ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
            $uuid = $this->resolveUuid($page['seeder'], $config, 0);
            $appended[$uuid] = ['uuid' => $uuid, 'seeder' => $page['seeder'], 'locale' => 'uk', 'page_id' => $page['id'],
                'page_category_id' => $page['page_category_id'], 'sort_order' => 0, 'type' => 'subtitle', 'column' => 'header',
                'heading' => null, 'css_class' => null, 'level' => $config['level'], 'body' => $target['after']['page']['subtitle_html']];
        }
        $continuing = array_values(array_filter($blocks, static function ($block) use ($appended) {
            $expected = $appended[$block['uuid']] ?? null;
            if ($expected === null) { return true; }
            foreach ($expected as $field => $value) { if (($block[$field] ?? null) !== $value) { return true; } }
            return false;
        }));
        return parent::relations($page, $continuing, $lock);
    }

    /** Normalize only a fully proven AFTER transition on copies, never original rows. */
    private function assertMetadataBaseline(array $captured, array $page, array $rows, array $old, array $new, int $index): void
    {
        $package = static::packageClass(); $binding = $package::profile()['bindings'][$index];
        $release = new \App\Support\TheoryNativeContentRelease($package::profile());
        if ($release->subtitleWasAbsent($old)) {
            $capturedUk = array_values(array_filter($captured['blocks'], static fn ($row) => $row['locale'] === 'uk'));
            if ($captured['page']['id'] !== $binding['page_id'] || $captured['page']['page_category_id'] !== $binding['category_id']
                || $captured['page']['text'] !== null || count($capturedUk) !== count($binding['continuing_uuids'])
                || array_filter($capturedUk, static fn ($row) => $row['type'] === 'subtitle') !== []) {
                throw new RuntimeException('Native independently captured SQL subtitle absence differs.');
            }
            foreach ($binding['continuing_uuids'] as $slot => $uuid) {
                $matches = array_values(array_filter($capturedUk, static fn ($row) => $row['uuid'] === $uuid));
                if (count($matches) !== 1 || $matches[0]['sort_order'] !== $slot + 1
                    || $matches[0]['seeder'] !== $binding['identity'] || $matches[0]['page_id'] !== $binding['page_id']
                    || $matches[0]['page_category_id'] !== $binding['category_id']) {
                    throw new RuntimeException('Native captured continuing UUID/order differs.');
                }
            }
        }
        foreach ($binding['continuing_metadata'] ?? [] as $slot => $transition) {
            $sourceBefore = $old['page']['blocks'][$slot];
            if ($release->subtitleWasAbsent($old)) { $sourceBefore['uuid'] = $binding['continuing_uuids'][$slot]; }
            $uuid = $this->resolveUuid($page['seeder'], $sourceBefore, $slot + 1);
            foreach ($rows as &$row) {
                if ($row['uuid'] !== $uuid || $row['locale'] !== 'uk') { continue; }
                $source = $new['page']['blocks'][$slot]; $matches = true;
                foreach (['type', 'column', 'heading', 'level', 'css_class', 'body'] as $field) {
                    if ($row[$field] !== ($source[$field] ?? null)) { $matches = false; }
                }
                if ($matches) { foreach ($transition['before'] as $field => $value) { $row[$field] = $value; } }
            }
            unset($row);
        }
        M49ContentPatch::assertMetadataBaseline($captured, $page, $rows);
    }

    /** Descriptor supplements can recognise only rows already proven by their finite projection. */
    protected function omitProtectedRow(string $table, array $row): bool { return false; }

    protected function protectedFingerprints(array $targetIds): array
    {
        $package = static::packageClass(); $schema = $this->db->getDriverName() === 'sqlite' ? 'main' : $this->db->getDatabaseName();
        $tables = $this->db->getSchemaBuilder()->getTableListing(schema: $schema, schemaQualified: false); sort($tables); $out = [];
        foreach ($tables as $table) {
            $columns = $this->db->getSchemaBuilder()->getColumnListing($table); $query = $this->db->table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            if ($table === 'text_blocks') { $query->whereNotIn('id', $targetIds); }
            $hash = hash_init('sha256'); $count = 0;
            foreach ($query->cursor() as $record) {
                $row = (array) $record; if ($this->omitProtectedRow($table, $row)) { continue; }
                if ($table === 'pages' && in_array($row['id'], $package::PAGE_IDS, true)) { unset($row['text']); }
                hash_update($hash, self::digest($row)."\n"); ++$count;
            }
            $out[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
        }
        return $out;
    }

    protected function assertExistingPools(array $questions, array $contract, ?array $baseline): void
    {
        $ids = [];
        foreach ($contract['bank_seeders'] as $bank) {
            $eligible = array_values(array_filter($questions, static fn ($q) => $q['seeder'] === $bank
                && (string) $q['type'] === '4' && $q['level'] === $contract['level']));
            usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            if ($baseline !== null && array_column($eligible, 'id') !== ($baseline['bank_inventory'][$bank]['eligible_ids_uuid_order'] ?? null)) {
                throw new RuntimeException('Native existing page-wide bank/type/level pool drifted.');
            }
            array_push($ids, ...array_column($eligible, 'id'));
        }
        if (count(array_unique($ids)) !== count($ids) || count($ids) < 5) { throw new RuntimeException('Native thematic pool is short or repeats IDs.'); }
    }

    private function assertOwner(array $row, array $page, string $owner, int $position): void
    {
        if ($row['seeder'] !== $owner || $row['locale'] !== 'uk' || $row['page_id'] !== $page['id']
            || $row['page_category_id'] !== $page['page_category_id'] || (int) $row['sort_order'] !== $position) {
            throw new RuntimeException('Native continuing owner/locale/order differs.');
        }
    }

    private function assertCapacity(array $patch): void
    {
        if ($this->db->getDriverName() !== 'mysql') { return; }
        $column = (array) $this->db->selectOne('SELECT DATA_TYPE AS kind, CHARACTER_OCTET_LENGTH AS max_bytes FROM information_schema.COLUMNS '
            .'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?', [$this->db->getDatabaseName(), 'text_blocks', 'body']);
        if (!in_array($column['kind'] ?? null, ['tinytext', 'text', 'mediumtext', 'longtext'], true) || !is_numeric($column['max_bytes'] ?? null)) {
            throw new RuntimeException('Native actual body SQL capacity is unknown.');
        }
        foreach ($patch['targets'] as $target) {
            PracticeCompletionPatch::assertBodyCapacity($target['after']['page']['subtitle_html'], (int) $column['max_bytes']);
            foreach ($target['after']['page']['blocks'] as $block) { PracticeCompletionPatch::assertBodyCapacity($block['body'], (int) $column['max_bytes']); }
        }
    }

    public static function fileHash(string $path): string
    {
        if (!is_file($path) || is_link($path)) { throw new RuntimeException('Native reviewed source/evidence missing or linked.'); }
        $sha = hash_file('sha256', $path);
        if (!is_string($sha)) { throw new RuntimeException('Native source hash unavailable.'); }
        return $sha;
    }

    private function assertServedSources(string $root, array $patch, array $contract): array
    {
        $policy = static::policy(); $package = static::packageClass();
        $normal = static fn ($path) => strtolower(str_replace('\\', '/', (string) realpath($path)));
        if (!in_array($normal($root), [$normal($policy['served_root']), $normal($policy['source_worktree'])], true)) {
            throw new RuntimeException('Native application/source ROOT differs from finite allowlist.');
        }
        foreach ([$policy['before_file'] => $policy['before_sha'], $policy['consumers_file'] => $policy['consumers_sha']] as $file => $sha) {
            if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || !hash_equals($sha, self::fileHash($this->privateDirectory.'/'.$file))) {
                throw new RuntimeException('Native independent BEFORE/consumer evidence is not reviewed or changed.');
            }
        }
        $before = json_decode(file_get_contents($this->privateDirectory.'/'.$policy['before_file']), true, flags: JSON_THROW_ON_ERROR);
        $consumers = json_decode(file_get_contents($this->privateDirectory.'/'.$policy['consumers_file']), true, flags: JSON_THROW_ON_ERROR);
        M51ContentPatch::assertConsumerCapture($before, $consumers);
        if (($before['base_sha'] ?? null) !== $package::BASE_SHA || ($before['served_root'] ?? null) !== $policy['served_root']
            || ($before['source_worktree'] ?? null) !== $policy['source_worktree']
            || ($before['sql_before'] ?? null) !== ($before['sql_after_http'] ?? null)
            || array_keys($before['pages'] ?? []) !== static::NAMES || empty($consumers['route_declarations']) || empty($consumers['http'])) {
            throw new RuntimeException('Native independent source/DB/HTTP BEFORE scope differs.');
        }
        foreach ($before['http'] as $record) {
            $allowedGate = $record['kind'] === 'course' && in_array($record['status'], [301, 302, 303, 307, 308, 401, 403, 404], true);
            if (empty($record['sha256']) || ($record['status'] !== 200 && !$allowedGate)) {
                throw new RuntimeException('Native required theory BEFORE unavailable or unrecognised course gate.');
            }
            if ($record['kind'] === 'course' && $record['status'] === 200 && empty($record['course_theory_html_sha256'])) {
                throw new RuntimeException('Native accessible BEFORE course learning DOM is missing.');
            }
        }
        $kinds = array_count_values(array_column($consumers['http'], 'kind'));
        if (($kinds['course'] ?? 0) < count(static::NAMES) || ($kinds['ssr'] ?? 0) < count(static::NAMES) || ($kinds['json'] ?? 0) < count(static::NAMES)) {
            throw new RuntimeException('Native independent consumer capture is incomplete.');
        }
        $sync = json_decode(file_get_contents($this->privateDirectory.'/'.$policy['sync_receipt']), true, flags: JSON_THROW_ON_ERROR);
        $paths = array_column($sync['files'] ?? [], 'path'); sort($paths);
        if (($sync['base_sha'] ?? null) !== $package::BASE_SHA || $paths !== static::syncPaths($patch, $contract)
            || ($sync['source_worktree'] ?? null) !== $policy['source_worktree'] || ($sync['served_root'] ?? null) !== $policy['served_root']
            || ($sync['visual_before'] ?? null) !== ['file' => $policy['before_file'], 'sha256' => $policy['before_sha']]) {
            throw new RuntimeException('Native exact reviewed source-sync receipt differs.');
        }
        foreach ($sync['files'] as $record) {
            $a = file_get_contents($policy['served_root'].'/'.$record['path']); $b = file_get_contents($policy['source_worktree'].'/'.$record['path']);
            if (self::fileHash($policy['served_root'].'/'.$record['path']) !== ($record['root_after_sha256'] ?? null)
                || self::fileHash($policy['source_worktree'].'/'.$record['path']) !== ($record['worktree_sha256'] ?? null)) {
                throw new RuntimeException('Native served/source bytes drifted after reviewed sync.');
            }
            if ($record['path'] === $policy['controller_path']) {
                if (preg_match('/^[a-f0-9]{64}$/D', $policy['controller_root_after_sha']) !== 1
                    || preg_match('/^[a-f0-9]{64}$/D', $policy['controller_worktree_after_sha']) !== 1
                    || hash('sha256', $a) !== $policy['controller_root_after_sha'] || hash('sha256', $b) !== $policy['controller_worktree_after_sha']
                    || ($record['root_before_sha256'] ?? null) !== ($before['shared_source_hashes'][$policy['controller_path']]['root'] ?? null)) {
                    throw new RuntimeException('Native controller differs from exact approved foreign-inclusive bytes.');
                }
            } elseif ($a !== $b && !(str_ends_with($record['path'], '.php') && str_replace("\r\n", "\n", $a) === str_replace("\r\n", "\n", $b))) {
                throw new RuntimeException('Native source mirror differs beyond preserved PHP EOL.');
            }
        }
        $unchangedController = 'app/Http/Controllers/PageController.php';
        if (self::fileHash($policy['served_root'].'/'.$unchangedController) !== $before['shared_source_hashes'][$unchangedController]['root']
            || self::fileHash($policy['source_worktree'].'/'.$unchangedController) !== $before['shared_source_hashes'][$unchangedController]['worktree']) {
            throw new RuntimeException('Native release cannot change global PageController/title heuristics.');
        }
        if (empty($before['visual_hashes']) || empty($before['frozen_hashes'])) { throw new RuntimeException('Native visual/frozen BEFORE is incomplete.'); }
        foreach ([$before['visual_hashes'], $before['frozen_hashes']] as $hashes) {
            foreach ($hashes as $path => $sha) {
                if (!hash_equals($sha, self::fileHash($policy['served_root'].'/'.$path))) {
                    throw new RuntimeException('Native visual or historical frozen source changed.');
                }
            }
        }
        return $before;
    }
}
