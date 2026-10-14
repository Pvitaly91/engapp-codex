<?php

namespace App\Services;

use App\Support\M51ModalMeaningsPackage as Package;
use App\Support\M51QuestionSupplement as Supplement;
use RuntimeException;

/** Finite insert-only supplement around the unchanged M26 backup/lock/transaction primitives. */
class M51ContentPatch extends M26ContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'f4ade377a942b92afee53ed3349ed49861bba43bde9f704408df8bace3b6daef';
    public const QUESTION_PLAN_FILE = 'question-plan-v1.json';
    public const QUESTION_PLAN_SHA = '7f27cc7ab2d68aa132e88fc5bcf6b7da2190738d705c6847e27f66d1b5f9c3df';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const CONSUMERS_FILE = 'before-old-consumers-v2.json';
    public const CONSUMERS_SHA = '6b4c18f351c0c4064a5b19ec53e896512c66a2a249702ab622bc75f44cded872';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_REQUIRED = true;
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_BEFORE_FILE = 'controller-before-v1-root.bin';
    public const CONTROLLER_BEFORE_SHA = '2052fbc10f5170b61a02ac8bb446b4b3e7271f5b8c3885f8ee9aa4d6a00778a5';
    public const CONTROLLER_WT_BEFORE_FILE = 'controller-before-v1-worktree.bin';
    public const CONTROLLER_WT_BEFORE_SHA = 'ea3c2881b17e6e6868f86fca9417f6ef365d066481560ab6953b3bb0eed34bfa';
    public const CONTROLLER_RECEIPT = 'controller-before-v1.json';
    public const CONTROLLER_RECEIPT_SHA = '28120a57d0aafbbe25c93e4eada6666af50829ee6616a127b33a8cabeee66a66';
    public const CONTROLLER_GIT_BASE_LF_SHA = '3c876bb3b52c70475d20d9b09ac79ddeab83b581208eb8e3c2c255dcde09833b';
    public const SYNC_CODE_PATHS = [
        'app/Support/M51ModalMeaningsPackage.php', 'app/Support/M51QuestionSupplement.php',
        'app/Services/M51ContentPatch.php', 'app/Services/M51LocalTargetGuard.php',
        'app/Console/Commands/PatchM51Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php',
    ];
    protected const ID = 'm51-modal-meanings-standard-view-v1';
    private const SOURCE_IDS = [198, 204, 397];
    private array $approvedQuestionRows = [];

    protected function localGuard(): string { return M51LocalTargetGuard::class; }
    protected function allowedPageUpdateFields(): array { return ['text']; }
    protected function expectedInsertCount(): int { return 3; }
    public function restore(string $backupPath, ?string $expectedDatabase = null): array { throw new RuntimeException('M51 restore is outside scope; no writes.'); }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        if ($this->db->getDriverName() === 'mysql') {
            M51LocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget !== 'gramlyze.loc' || $this->localProof === null) {
                throw new RuntimeException('M51 requires explicit gramlyze.loc and fresh local proof.');
            }
        }

        return parent::connection($expected, $writing);
    }

    public static function bankPaths(): array
    {
        $paths = [];
        foreach (Supplement::BANKS as $bank) {
            $name = substr($bank, strrpos($bank, '\\') + 1); $base = 'database/seeders/V3/Polyglot/'.$name;
            array_push($paths, $base.'.php', $base.'/'.$name.'.php', $base.'/definition.json');
        }

        return $paths;
    }

    public static function syncPaths(array $patch): array
    {
        $paths = [...self::SYNC_CODE_PATHS, ...self::bankPaths(), Package::MASTER, Package::BEFORE, Package::SOURCE,
            Supplement::SOURCE, 'docs/content/theory-sentence-builders/m51-modal-meanings.v1.json', ...array_column($patch['targets'], 'path')];
        if (self::CONTROLLER_REQUIRED) { $paths[] = self::CONTROLLER_PATH; }
        $paths = array_values(array_unique($paths)); sort($paths);

        return $paths;
    }

    public static function sourcePaths(array $patch, ?string $root = null): array
    {
        $root ??= base_path();
        $paths = [...self::syncPaths($patch), 'app/Services/M26ContentPatch.php', 'app/Services/M11LocalTargetGuard.php',
            'app/Services/M49LocalTargetGuard.php', 'app/Services/PracticeCompletionPatch.php',
            'app/Support/M49AdviceModalsPackage.php', 'app/Support/M26DetailPackage.php',
            'app/Support/TextBlock/TextBlockUuidGenerator.php', 'app/Support/Database/QuestionUuidResolver.php', 'app/Support/ComposeTokenCase.php'];
        foreach ($patch['targets'] as $target) {
            foreach (glob(dirname($root.'/'.$target['path']).'/localizations/*.json') ?: [] as $file) {
                $paths[] = substr(str_replace('\\', '/', $file), strlen(str_replace('\\', '/', $root)) + 1);
            }
        }
        $paths = array_values(array_unique($paths)); sort($paths);

        return $paths;
    }

    public static function assertDefinitionScope(array $before, array $after, array $target, int $i): void
    {
        $owner = self::NAMES[$i] ?? null;
        if ($owner === null || $target['identity'] !== $owner || $before['seeder']['class'] !== $owner
            || $before['page']['locale'] !== 'uk' || $before['type'] !== 'theory'
            || $before['page']['category']['slug'] !== 'modal-verbs' || $target['ancestry'] !== ['modal-verbs']
            || $target['slug'] !== Package::SLUGS[$i] || $before['slug'] !== Package::SLUGS[$i]
            || $target['page_binding'] !== ['id' => Package::PAGE_IDS[$i], 'page_category_id' => 15]
            || $target['path'] !== \App\Support\TheorySentenceBuilderAcceptance::definitionPath($owner)
            || $target['local_url'] !== 'http://gramlyze.loc/theory/modal-verbs/'.Package::SLUGS[$i]
            || count($before['page']['blocks']) !== 6 || count($after['page']['blocks']) !== 7) {
            throw new RuntimeException('M51 exact definition/category/slot scope differs.');
        }
        $protected = $after; $protected['page']['blocks'] = $before['page']['blocks'];
        foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $before['page'][$field]; }
        if ($protected !== $before) { throw new RuntimeException('M51 protected definition metadata changed.'); }
        foreach ($before['page']['blocks'] as $slot => $old) {
            $new = $after['page']['blocks'][$slot]; unset($old['body'], $new['body']);
            if ($old !== $new) { throw new RuntimeException('M51 continuing block metadata/order changed.'); }
        }
        $practice = $after['page']['blocks'][6];
        if ($practice['type'] !== 'practice-set' || ($practice['level'] ?? null) !== Package::TECHNICAL_LEVELS[$i]
            || !str_starts_with($practice['uuid_key'] ?? '', 'm51:'.Package::SLUGS[$i].':')) {
            throw new RuntimeException('M51 new practice identity/technical level differs.');
        }
    }

    private function assertCapacity(array $patch): void
    {
        if ($this->db->getDriverName() !== 'mysql') { return; }
        $column = (array) $this->db->selectOne('SELECT DATA_TYPE AS kind, CHARACTER_OCTET_LENGTH AS max_bytes '
            .'FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$this->db->getDatabaseName(), 'text_blocks', 'body']);
        if (!in_array($column['kind'] ?? null, ['tinytext', 'text', 'mediumtext', 'longtext'], true)
            || !is_numeric($column['max_bytes'] ?? null)) { throw new RuntimeException('M51 actual body column capacity is unknown.'); }
        foreach ($patch['targets'] as $target) {
            PracticeCompletionPatch::assertBodyCapacity($target['after']['page']['subtitle_html'], (int) $column['max_bytes']);
            foreach ($target['after']['page']['blocks'] as $block) { PracticeCompletionPatch::assertBodyCapacity($block['body'], (int) $column['max_bytes']); }
        }
    }

    public function plan(bool $lock = false): array
    {
        $connection = $this->connection(); $root = dirname($this->databasePath);
        [$before, $patch, $master] = Package::load($root); Package::validate($before, $patch, $master); $this->assertCapacity($patch);
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => self::NAMES, 'connection' => $connection,
            'sources' => [], 'pages' => [], 'updates' => [], 'inserts' => []];
        $baseline = null;
        if ($this->localTarget !== null) {
            $baseline = $this->assertServedSources($root, $patch);
            foreach ([self::BEFORE_FILE, self::SYNC_RECEIPT, self::QUESTION_PLAN_FILE, self::CONSUMERS_FILE] as $name) {
                $plan['sources']['private:'.$name] = self::fileHash($this->privateDirectory.'/'.$name);
            }
            if (self::CONTROLLER_REQUIRED) {
                foreach ([self::CONTROLLER_BEFORE_FILE, self::CONTROLLER_WT_BEFORE_FILE, self::CONTROLLER_RECEIPT] as $name) { $plan['sources']['private:'.$name] = self::fileHash($this->privateDirectory.'/'.$name); }
            }
        }
        foreach (self::sourcePaths($patch, $root) as $path) { $plan['sources'][$path] = self::fileHash($root.'/'.$path); }
        $states = []; $ids = [];
        foreach ($patch['targets'] as $i => $target) {
            $name = $target['identity']; $old = $before['targets'][$i]['before']; $new = $target['after'];
            self::assertDefinitionScope($old, $new, $target, $i);
            if (json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR) !== $new) { throw new RuntimeException('M51 active source differs from exact AFTER.'); }
            $owners = $this->rows('pages', static fn ($q) => $q->where('seeder', $name), $lock);
            if (count($owners) !== 1) { throw new RuntimeException('M51 page owner missing/ambiguous.'); }
            $page = $owners[0]; $chain = $this->categoryChain($page['page_category_id'], $lock);
            if ($page['id'] !== Package::PAGE_IDS[$i] || $page['page_category_id'] !== 15 || $page['slug'] !== $old['slug']
                || $page['title'] !== $old['page']['title'] || $page['type'] !== 'theory' || array_column($chain, 'slug') !== ['modal-verbs']) {
                throw new RuntimeException('M51 Page/category binding differs.');
            }
            $rows = $this->rows('text_blocks', static fn ($q) => $q->where('page_id', $page['id']), $lock);
            if ($baseline !== null) { M49ContentPatch::assertMetadataBaseline($baseline['pages'][$name], $page, $rows); }
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'level' => $old['page']['subtitle_level'] ?? null,
                'body' => $old['page']['subtitle_html'], 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
            $newSubtitle = $subtitle; $newSubtitle['body'] = $new['page']['subtitle_html'];
            $oldConfigs = [$subtitle, ...$old['page']['blocks']]; $newConfigs = [$newSubtitle, ...$new['page']['blocks']]; $rowStates = [];
            $state = $this->candidate($plan['updates'], 'pages', $page, $name, ['text' => $old['page']['subtitle_text']], ['text' => $new['page']['subtitle_text']]);
            if ($state !== null) { $rowStates[] = $state; }
            foreach ($oldConfigs as $position => $config) {
                $uuid = $this->resolveUuid($name, $config, $position);
                if ($uuid !== $this->resolveUuid($name, $newConfigs[$position], $position)) { throw new RuntimeException('M51 continuing UUID changed.'); }
                $global = $this->rows('text_blocks', static fn ($q) => $q->where('uuid', $uuid), $lock);
                if (count($global) !== 1) { throw new RuntimeException('M51 continuing row missing/ambiguous.'); }
                $row = $global[0];
                if ($row['seeder'] !== $name || $row['locale'] !== 'uk' || $row['page_id'] !== $page['id']
                    || $row['page_category_id'] !== 15 || (int) $row['sort_order'] !== $position) { throw new RuntimeException('M51 row ownership/order differs.'); }
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                    if ($row[$field] !== ($config[$field] ?? null)) { throw new RuntimeException('M51 continuing row metadata differs.'); }
                }
                $ids[] = $row['id']; $state = $this->candidate($plan['updates'], 'text_blocks', $row, $name, ['body' => $config['body']], ['body' => $newConfigs[$position]['body']]);
                if ($state !== null) { $rowStates[] = $state; }
            }
            $rowStates = array_values(array_unique($rowStates));
            if (count($rowStates) !== 1) { throw new RuntimeException('M51 partial/manual owner state.'); }
            $state = $rowStates[0]; $practice = $newConfigs[7]; $uuid = $this->resolveUuid($name, $practice, 7);
            $fields = ['uuid' => $uuid, 'page_id' => $page['id'], 'page_category_id' => 15, 'locale' => 'uk', 'type' => 'practice-set',
                'column' => $practice['column'], 'heading' => $practice['heading'] ?? null, 'css_class' => $practice['css_class'] ?? null,
                'sort_order' => 7, 'body' => $practice['body'], 'level' => Package::TECHNICAL_LEVELS[$i], 'seeder' => $name];
            $global = $this->rows('text_blocks', static fn ($q) => $q->where('uuid', $uuid), $lock);
            if ($state === 'before') {
                if ($global !== []) { throw new RuntimeException('M51 practice UUID collision or partial state.'); }
                $plan['inserts'][] = ['table' => 'text_blocks', 'seeder' => $name, 'fields' => $fields, 'sha256' => self::digest($fields)];
            } else {
                if (count($global) !== 1) { throw new RuntimeException('M51 applied practice missing/ambiguous.'); }
                foreach ($fields as $field => $value) { if ($global[0][$field] !== $value) { throw new RuntimeException('M51 applied practice edited.'); } }
                if (empty($global[0]['created_at']) || $global[0]['created_at'] !== $global[0]['updated_at']) { throw new RuntimeException('M51 practice timestamps changed.'); }
                $ids[] = $global[0]['id'];
            }
            if (count(array_filter($rows, static fn ($row) => $row['locale'] === 'uk')) !== 7 + (int) ($state === 'after')) { throw new RuntimeException('M51 unexpected UK row count.'); }
            $states[] = $state; $plan['pages'][$name] = ['page' => $page, 'category_ancestry' => $chain, 'blocks' => $rows, 'relations' => $this->relations($page, $rows, $lock)];
        }
        if (count(array_unique($states)) !== 1) { throw new RuntimeException('M51 partial package; no writes.'); }
        $plan['state'] = $states[0];
        if (($plan['state'] === 'before' && count($plan['inserts']) !== 3) || ($plan['state'] === 'after' && ($plan['updates'] !== [] || $plan['inserts'] !== []))) { throw new RuntimeException('M51 content insert/no-op scope differs.'); }
        $questions = $this->questionState($plan['state'], $patch, $lock);
        $plan['question_projection'] = array_intersect_key($questions['evidence']['projection'], Supplement::ROW_COLUMNS);
        $plan['question_ids'] = $questions['evidence']['ids']; $plan['question_state'] = $plan['state'];
        $plan['question_inserts'] = $plan['state'] === 'before' ? $plan['question_projection'] : array_fill_keys(array_keys(Supplement::ROW_COLUMNS), []);
        $plan['question_inventory'] = $questions['evidence']['inventory'];
        $this->approvedQuestionRows = $questions['actual'];
        $plan['protected'] = $this->protectedFingerprints($ids); ksort($plan['sources']); $plan['sha256'] = self::digest($plan);

        return $plan;
    }

    /** Only continuing rows reach the inherited relation projection; new questions point solely to new practices. */
    protected function relations(array $page, array $blocks, bool $lock): array
    {
        $continuing = array_values(array_filter($blocks, static fn ($block) => !($block['locale'] === 'uk'
            && in_array($block['seeder'], self::NAMES, true) && $block['type'] === 'practice-set' && (int) $block['sort_order'] === 7)));

        return parent::relations($page, $continuing, $lock);
    }

    protected function protectedFingerprints(array $targetIds): array
    {
        $schema = $this->db->getDriverName() === 'sqlite' ? 'main' : $this->db->getDatabaseName();
        $tables = $this->db->getSchemaBuilder()->getTableListing(schema: $schema, schemaQualified: false); sort($tables);
        $pageIds = $this->db->table('pages')->whereIn('seeder', self::NAMES)->pluck('id')->all(); $out = [];
        foreach ($tables as $table) {
            $columns = $this->db->getSchemaBuilder()->getColumnListing($table); $query = $this->db->table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            if ($table === 'text_blocks') { $query->whereNotIn('id', $targetIds); }
            $approvedIds = array_column($this->approvedQuestionRows[$table] ?? [], 'id');
            if ($approvedIds !== []) { $query->whereNotIn('id', $approvedIds); }
            $hash = hash_init('sha256'); $count = 0;
            foreach ($query->cursor() as $record) {
                $row = (array) $record; if ($table === 'pages' && in_array($row['id'], $pageIds, true)) { unset($row['text']); }
                hash_update($hash, self::digest($row)."\n"); $count++;
            }
            $out[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
        }

        return $out;
    }

    /** Private evidence pins original option/taxonomy snapshots and reviewed explicit IDs; never committed. */
    protected function questionEvidence(): array
    {
        $file = $this->privateDirectory.'/'.self::QUESTION_PLAN_FILE;
        if (preg_match('/^[a-f0-9]{64}$/D', self::QUESTION_PLAN_SHA) !== 1 || !hash_equals(self::QUESTION_PLAN_SHA, self::fileHash($file))) {
            throw new RuntimeException('M51 question plan is not reviewed/frozen or its private bytes changed.');
        }
        $evidence = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        $inventory = $evidence['inventory'] ?? [];
        if (preg_match('/^question-inventory-v[0-9]+\.json$/D', $inventory['file'] ?? '') !== 1
            || preg_match('/^[a-f0-9]{64}$/D', $inventory['sha256'] ?? '') !== 1
            || !hash_equals($inventory['sha256'], self::fileHash($this->privateDirectory.'/'.$inventory['file']))) {
            throw new RuntimeException('M51 independently captured question inventory differs.');
        }
        $evidence['_inventory'] = json_decode(file_get_contents($this->privateDirectory.'/'.$inventory['file']), true, flags: JSON_THROW_ON_ERROR);

        return $evidence;
    }

    private function questionState(string $state, array $patch, bool $lock): array
    {
        $evidence = $this->questionEvidence(); $inventory = $evidence['_inventory'];
        if (($evidence['schema_version'] ?? null) !== 1 || ($evidence['base_sha'] ?? null) !== Package::BASE_SHA
            || ($evidence['before_sha256'] ?? null) !== self::BEFORE_SHA || ($evidence['source_sha256'] ?? null) !== Supplement::SOURCE_SHA
            || ($inventory['base_sha'] ?? null) !== Package::BASE_SHA || ($inventory['draft_sha256'] ?? null) !== Supplement::SOURCE_SHA
            || !in_array($state, ['before', 'after'], true)) { throw new RuntimeException('M51 frozen question evidence/base/source differs.'); }
        $source = Supplement::load(dirname($this->databasePath));
        if (!is_array($evidence['bindings'] ?? null) || !array_is_list($evidence['bindings']) || count($evidence['bindings']) !== 3) { throw new RuntimeException('M51 complete three-owner question binding inventory required.'); }
        foreach ($evidence['bindings'] ?? [] as $i => $binding) {
            $target = $patch['targets'][$i] ?? [];
            if ($binding !== ['owner' => Package::OWNERS[$i], 'level' => Package::TECHNICAL_LEVELS[$i], 'category_id' => 45,
                'source_id' => self::SOURCE_IDS[$i], 'practice_uuid' => $this->resolveUuid($target['identity'], $target['after']['page']['blocks'][6], 7)]) {
                throw new RuntimeException('M51 exact existing taxonomy/practice binding differs.');
            }
        }
        $projection = Supplement::project($source, $evidence['bindings'], self::optionRowsFromInventory($inventory['option_matches'] ?? []));
        if ($projection !== $evidence['projection']) { throw new RuntimeException('M51 question projection differs from independent frozen source/inventory.'); }
        $taxonomy = ['question_categories' => array_values(array_filter($inventory['categories'] ?? [], static fn ($row) => $row['id'] === 45)), 'sources' => $inventory['sources'] ?? []];
        if ($taxonomy !== ($evidence['taxonomy'] ?? null) || array_column($taxonomy['question_categories'], 'id') !== [45]
            || array_column($taxonomy['sources'], 'id') !== self::SOURCE_IDS) { throw new RuntimeException('M51 independently captured taxonomy differs.'); }
        foreach ($taxonomy as $table => $rows) {
            $databaseTable = $table === 'question_categories' ? 'categories' : $table;
            if ($this->rows($databaseTable, static fn ($q) => $q->whereIn('id', array_column($rows, 'id')), $lock) !== $rows) { throw new RuntimeException('M51 existing taxonomy row was changed/replaced.'); }
        }
        foreach ($projection['reused_options'] as $option) {
            if ($this->rows('question_options', static fn ($q) => $q->where('id', $option['id']), $lock) !== [$option['row']]
                || self::digest($option['row']) !== $option['sha256']) { throw new RuntimeException('M51 reused original option snapshot drifted.'); }
        }
        foreach (Supplement::ROW_COLUMNS as $table => $columns) {
            $actual = $this->db->getSchemaBuilder()->getColumnListing($table); $expected = $columns; sort($actual); sort($expected);
            if ($actual !== $expected) { throw new RuntimeException('M51 exact insert-family schema differs: '.$table); }
            $allocation = $inventory['allocation'][$table] ?? [];
            $start = max((int) ($allocation['max_id'] ?? -1) + 1, (int) ($allocation['auto_increment']['next_id'] ?? -1));
            $keys = array_column($projection[$table], 'key'); $values = [];
            foreach ($keys as $key) { $values[] = $evidence['ids'][$table][$key] ?? null; }
            $expectedIds = $keys === [] ? [] : range($start, $start + count($keys) - 1);
            if ($start < 1 || $values !== $expectedIds) { throw new RuntimeException('M51 reviewed sequential ID allocation differs.'); }
            if ($state === 'before' && (int) ($this->db->table($table)->max('id') ?? 0) !== (int) ($allocation['max_id'] ?? -1)) { throw new RuntimeException('M51 original new-ID inventory became stale.'); }
            if ($state === 'before' && $this->db->getDriverName() === 'mysql') {
                $auto = (array) $this->db->selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$this->db->getDatabaseName(), $table]);
                if ((int) ($auto['next_id'] ?? -1) !== (int) ($allocation['auto_increment']['next_id'] ?? -1)) { throw new RuntimeException('M51 physical ID sequence changed since inventory.'); }
            }
        }
        $actual = $this->questionRows($projection, $evidence['ids'], $lock);
        if ($state === 'before') {
            Supplement::materialize($projection, $evidence['ids'], '2000-01-01 00:00:00'); // Shape/IDs only; never written.
            Supplement::assertAbsent($actual);
        } else {
            $timestamp = $actual['questions'][0]['created_at'] ?? null;
            if (!is_string($timestamp)) { throw new RuntimeException('M51 applied question timestamp missing.'); }
            Supplement::assertApplied(Supplement::materialize($projection, $evidence['ids'], $timestamp), $actual);
        }

        return compact('evidence', 'actual');
    }

    /** Scope includes IDs plus semantic/relationship collisions, not just whichever rows were inserted. */
    private function questionRows(array $projection, array $ids, bool $lock): array
    {
        $questionIds = array_values($ids['questions']); $optionIds = array_values($ids['question_options']);
        $uuids = array_column(array_column($projection['questions'], 'fields'), 'uuid');
        $practices = array_values(array_unique(array_column(array_column($projection['question_theory_text_blocks'], 'fields'), 'text_block_uuid')));
        $out = [];
        foreach (Supplement::ROW_COLUMNS as $table => $_) {
            $newIds = array_values($ids[$table]);
            $out[$table] = $this->rows($table, static function ($q) use ($table, $newIds, $questionIds, $optionIds, $uuids, $practices, $projection): void {
                $q->whereIn('id', $newIds);
                if ($table === 'questions') { $q->orWhereIn('uuid', $uuids)->orWhereIn('seeder', Supplement::BANKS)->orWhereIn('theory_text_block_uuid', $practices); }
                elseif ($table === 'question_options') { $q->orWhereIn('option', array_column(array_column($projection['question_options'], 'fields'), 'option')); }
                elseif ($table === 'question_theory_text_blocks') { $q->orWhereIn('question_uuid', $uuids)->orWhereIn('text_block_uuid', $practices); }
                else { $q->orWhereIn('question_id', $questionIds)->orWhereIn('option_id', $optionIds); }
            }, $lock);
        }

        return $out;
    }

    /** Called only after the third native M26 practice insertion, still inside its exclusive-backup transaction. */
    protected function afterInsert(int $index): void
    {
        if ($index !== 2) { return; }
        [, $patch] = Package::load(dirname($this->databasePath));
        foreach ($patch['targets'] as $i => $target) {
            $practice = $target['after']['page']['blocks'][6]; $uuid = $this->resolveUuid($target['identity'], $practice, 7);
            $rows = $this->rows('text_blocks', static fn ($q) => $q->where('uuid', $uuid), true);
            $fields = ['uuid' => $uuid, 'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => 15, 'locale' => 'uk', 'seeder' => Package::OWNERS[$i],
                'type' => 'practice-set', 'column' => $practice['column'], 'heading' => $practice['heading'] ?? null,
                'css_class' => $practice['css_class'] ?? null, 'sort_order' => 7, 'body' => $practice['body'], 'level' => Package::TECHNICAL_LEVELS[$i]];
            if (count($rows) !== 1) { throw new RuntimeException('M51 all three exact new practices required before questions.'); }
            foreach ($fields as $field => $value) { if ($rows[0][$field] !== $value) { throw new RuntimeException('M51 question link practice identity differs.'); } }
        }
        $questions = $this->questionState('before', $patch, true);
        $rows = Supplement::materialize($questions['evidence']['projection'], $questions['evidence']['ids'], now()->toDateTimeString());
        foreach (Supplement::ROW_COLUMNS as $table => $_) {
            foreach ($rows[$table] as $n => $row) {
                if (!$this->db->table($table)->insert($row)) { throw new RuntimeException('M51 scoped question insert failed; transaction rolled back.'); }
                $this->afterQuestionRowInsert($table, $n);
            }
        }
        $this->questionState('after', $patch, true);
    }

    protected function afterQuestionRowInsert(string $table, int $index): void {}

    /** The captured SQL lookup records retain the exact requested token beside their complete row array. */
    public static function optionRowsFromInventory(array $records): array
    {
        $rows = [];
        foreach ($records as $token => $record) {
            $keys = is_array($record) ? array_keys($record) : []; sort($keys);
            if (!is_string($token) || $token === '' || $keys !== ['requested', 'rows']
                || ($record['requested'] ?? null) !== $token || !is_array($record['rows'] ?? null) || !array_is_list($record['rows'])) {
                throw new RuntimeException('M51 captured option lookup record/request shape differs.');
            }
            $rows[$token] = $record['rows'];
        }

        return $rows;
    }

    /** A capture has no preview reference: its entire independently discovered BEFORE must equal the original baseline. */
    public static function assertConsumerCapture(array $before, array $capture): void
    {
        if (($capture['schema_version'] ?? null) !== 1 || ($capture['mode'] ?? null) !== 'capture'
            || !array_key_exists('before_sha256', $capture) || $capture['before_sha256'] !== null
            || ($capture['unchanged_during_http'] ?? null) !== true || empty($before['sql_before'])
            || ($capture['all_tables_before_http'] ?? null) !== $before['sql_before']
            || $capture['all_tables_before_http'] !== ($capture['all_tables_after_http'] ?? null)) {
            throw new RuntimeException('M51 old-consumer capture differs from the full independent baseline or wrote to the database.');
        }
        foreach ($capture['all_tables_before_http'] as $fingerprint) {
            if (!is_int($fingerprint['count'] ?? null) || $fingerprint['count'] < 0 || preg_match('/^[a-f0-9]{64}$/D', $fingerprint['sha256'] ?? '') !== 1) {
                throw new RuntimeException('M51 full old-consumer capture fingerprint is incomplete.');
            }
        }
    }

    public static function fileHash(string $file): string
    {
        if (!is_file($file) || is_link($file)) { throw new RuntimeException('M51 source/evidence missing or linked.'); }

        return hash_file('sha256', $file);
    }

    /** Pure literal insertions after the four accepted M50 calls; no controller file is edited here. */
    public static function approvedControllerAfter(string $before): string
    {
        if (str_contains($before, 'M51ModalMeaningsPackage::preserveExcludedTextBlock')) { throw new RuntimeException('M51 controller already contains owned insertions.'); }
        $lf = str_replace("\r\n", "\n", $before); $eol = str_contains($before, "\r\n") ? "\r\n" : "\n";
        if (str_contains($lf, "\r") || ($eol === "\r\n" && str_contains(str_replace("\r\n", '', $before), "\n"))) { throw new RuntimeException('M51 controller has unsupported mixed EOL.'); }
        $hunks = [
            ["\n                    \$block = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$block, 'standalone_test');\n                    if (\$block === null) { continue; }",
                "\n                    \$block = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$block, 'standalone_test');\n                    if (\$block === null) { continue; }\n                    \$block = \\App\\Support\\M51ModalMeaningsPackage::preserveExcludedTextBlock(\$block, 'standalone_test');\n                    if (\$block === null) { continue; }"],
            ["\n                \$block = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$block, 'standalone_test');\n                if (\$block === null) { continue; }",
                "\n                \$block = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$block, 'standalone_test');\n                if (\$block === null) { continue; }\n                \$block = \\App\\Support\\M51ModalMeaningsPackage::preserveExcludedTextBlock(\$block, 'standalone_test');\n                if (\$block === null) { continue; }"],
            ["\n                if (\$textBlock) { \$textBlock = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }",
                "\n                if (\$textBlock) { \$textBlock = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\n                if (\$textBlock) { \$textBlock = \\App\\Support\\M51ModalMeaningsPackage::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }"],
            ["\n            if (\$textBlock) { \$textBlock = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }",
                "\n            if (\$textBlock) { \$textBlock = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\n            if (\$textBlock) { \$textBlock = \\App\\Support\\M51ModalMeaningsPackage::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }"],
        ];
        $after = $before;
        foreach ($hunks as [$anchor, $replacement]) {
            $anchor = str_replace("\n", $eol, $anchor); $replacement = str_replace("\n", $eol, $replacement);
            if (substr_count($after, $anchor) !== 1) { throw new RuntimeException('M51 exact controller insertion anchor missing/ambiguous.'); }
            $after = str_replace($anchor, $replacement, $after);
        }

        return $after;
    }

    private function assertServedSources(string $root, array $patch): array
    {
        $normal = static fn ($path) => strtolower(str_replace('\\', '/', (string) realpath($path)));
        if (!in_array($normal($root), [$normal(M51LocalTargetGuard::ROOT), $normal(self::SOURCE_WORKTREE)], true)
            || $normal(self::SOURCE_WORKTREE) !== strtolower(self::SOURCE_WORKTREE)) { throw new RuntimeException('M51 active/source root differs from finite ROOT/WT allowlist.'); }
        $file = $this->privateDirectory.'/'.self::BEFORE_FILE;
        if (!hash_equals(self::BEFORE_SHA, self::fileHash($file))) { throw new RuntimeException('M51 independent private BEFORE drifted.'); }
        $before = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        if (!hash_equals(self::CONSUMERS_SHA, self::fileHash($this->privateDirectory.'/'.self::CONSUMERS_FILE))) { throw new RuntimeException('M51 independent old-consumer BEFORE drifted.'); }
        $consumers = json_decode(file_get_contents($this->privateDirectory.'/'.self::CONSUMERS_FILE), true, flags: JSON_THROW_ON_ERROR);
        self::assertConsumerCapture($before, $consumers);
        $sync = json_decode(file_get_contents($this->privateDirectory.'/'.self::SYNC_RECEIPT), true, flags: JSON_THROW_ON_ERROR);
        $paths = array_column($sync['files'] ?? [], 'path'); sort($paths);
        if (($sync['base_sha'] ?? null) !== Package::BASE_SHA || $paths !== self::syncPaths($patch)
            || ($sync['source_worktree'] ?? null) !== self::SOURCE_WORKTREE || ($sync['served_root'] ?? null) !== M51LocalTargetGuard::ROOT
            || ($sync['visual_before'] ?? null) !== ['file' => self::BEFORE_FILE, 'sha256' => self::BEFORE_SHA]) { throw new RuntimeException('M51 finite reviewed served-source sync differs.'); }
        foreach ($sync['files'] as $record) {
            $a = file_get_contents(M51LocalTargetGuard::ROOT.'/'.$record['path']); $b = file_get_contents(self::SOURCE_WORKTREE.'/'.$record['path']);
            if (self::fileHash(M51LocalTargetGuard::ROOT.'/'.$record['path']) !== $record['root_after_sha256']
                || self::fileHash(self::SOURCE_WORKTREE.'/'.$record['path']) !== $record['worktree_sha256']) { throw new RuntimeException('M51 served/source bytes drifted.'); }
            if ($record['path'] === self::CONTROLLER_PATH) {
                if (!hash_equals(self::CONTROLLER_RECEIPT_SHA, self::fileHash($this->privateDirectory.'/'.self::CONTROLLER_RECEIPT))) { throw new RuntimeException('M51 controller independent receipt drifted.'); }
                $receipt = json_decode(file_get_contents($this->privateDirectory.'/'.self::CONTROLLER_RECEIPT), true, flags: JSON_THROW_ON_ERROR);
                if (($receipt['base_sha'] ?? null) !== Package::BASE_SHA || ($receipt['path'] ?? null) !== self::CONTROLLER_PATH
                    || ($receipt['root_file'] ?? null) !== self::CONTROLLER_BEFORE_FILE || ($receipt['root_sha256'] ?? null) !== self::CONTROLLER_BEFORE_SHA
                    || ($receipt['worktree_file'] ?? null) !== self::CONTROLLER_WT_BEFORE_FILE || ($receipt['worktree_sha256'] ?? null) !== self::CONTROLLER_WT_BEFORE_SHA
                    || ($receipt['worktree_base_lf_sha256'] ?? null) !== self::CONTROLLER_GIT_BASE_LF_SHA) { throw new RuntimeException('M51 exact ROOT/WT controller evidence differs.'); }
                foreach ([self::CONTROLLER_BEFORE_FILE => self::CONTROLLER_BEFORE_SHA, self::CONTROLLER_WT_BEFORE_FILE => self::CONTROLLER_WT_BEFORE_SHA] as $name => $sha) {
                    if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || !hash_equals($sha, self::fileHash($this->privateDirectory.'/'.$name))) { throw new RuntimeException('M51 independent controller BEFORE changed.'); }
                }
                if (hash('sha256', str_replace("\r\n", "\n", file_get_contents($this->privateDirectory.'/'.self::CONTROLLER_WT_BEFORE_FILE))) !== self::CONTROLLER_GIT_BASE_LF_SHA
                    || $a !== self::approvedControllerAfter(file_get_contents($this->privateDirectory.'/'.self::CONTROLLER_BEFORE_FILE))
                    || $b !== self::approvedControllerAfter(file_get_contents($this->privateDirectory.'/'.self::CONTROLLER_WT_BEFORE_FILE))) { throw new RuntimeException('M51 controller changed beyond exact owned insertions.'); }
            } elseif ($a !== $b && !(str_ends_with($record['path'], '.php') && str_replace("\r\n", "\n", $a) === str_replace("\r\n", "\n", $b))) {
                throw new RuntimeException('M51 source mirror differs beyond retained PHP EOL.');
            }
        }
        if (empty($before['visual_hashes']) || empty($before['frozen_hashes'])) { throw new RuntimeException('M51 independent visual/frozen BEFORE incomplete.'); }
        foreach ([$before['visual_hashes'], $before['frozen_hashes']] as $hashes) {
            foreach ($hashes as $path => $sha) { if (!hash_equals($sha, self::fileHash(M51LocalTargetGuard::ROOT.'/'.$path))) { throw new RuntimeException('M51 visual or previous frozen source changed.'); } }
        }

        return $before;
    }
}
