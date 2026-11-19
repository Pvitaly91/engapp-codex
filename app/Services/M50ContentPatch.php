<?php

namespace App\Services;

use App\Support\M50ObligationAdvicePackage as Package;
use RuntimeException;

/** Three-definition planner only; M26 retains backup, transaction, locks and postconditions. */
class M50ContentPatch extends M26ContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'd9f299235683df8962dcf35b9582c3ce8b40a1e6a45663f01ea6e18546de53f9';
    public const FROZEN_FILE = 'frozen-before-v1.json';
    public const FROZEN_SHA = 'a7704cdf368f55060a3e741a0b1baac173b55f150264c37ba3c2033ea3261084';
    public const SYNC_RECEIPT = 'source-sync-v3.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const LINKS = 'database/content-patches/m50-need-practice-links.v1.json';
    public const LINKS_SHA = '3f7107a3c6f3eab917ff3fcf9f33d2816793f947950b3d7095a6d528b8459b67';
    public const LINKS_BEFORE_FILE = 'm50-links-before-v1.json';
    public const LINKS_BEFORE_SHA = '98c70fb4fc8604758c9c32cbcf5f1fad2664aff1804fac6d61b1c5235c13e7da';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_BEFORE_FILE = 'controller-before-v1.bin';
    public const CONTROLLER_BEFORE_SHA = '940fd8165b466ad2cc6aa8e96338074ed147558ec082dcea5152d49568a9f797';
    public const CONTROLLER_WT_BEFORE_FILE = 'controller-wt-before-v1.bin';
    public const CONTROLLER_WT_BEFORE_SHA = '9829522c0b0312a51ccd399719367f650a4d62d9441fc0cd6bace08840bc4f0d';
    public const CONTROLLER_GIT_BASE_LF_SHA = 'cab4bfd14a1382ac3caf71cf67f94b908cf7b24a0f3e5d3bbd2f01ba8d20854f';
    public const NEED_PRACTICE_UUID = '99bf6a7c-b493-5e4a-b66b-9b3ac8be3c65';
    private const LINK_QUESTION_IDS = [158, 164, 166, 133, 135];
    private const QUESTION_SNAPSHOT_FIELDS = ['question', 'answers', 'options', 'option_pivots', 'verb_hints', 'hints', 'variants'];
    public const SYNC_CODE_PATHS = [
        'app/Support/M50ObligationAdvicePackage.php', 'app/Services/M50ContentPatch.php',
        'app/Services/M50LocalTargetGuard.php', 'app/Console/Commands/PatchM50Content.php',
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php',
        'app/Http/Controllers/PageController.php',
    ];
    protected const ID = 'm50-obligation-advice-standard-view-v1';

    protected function localGuard(): string { return M50LocalTargetGuard::class; }
    protected function allowedPageUpdateFields(): array { return ['text']; }
    protected function expectedInsertCount(): int { return 3; }
    public function restore(string $backupPath, ?string $expectedDatabase = null): array { throw new RuntimeException('M50 restore is outside scope; no writes.'); }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        if ($this->db->getDriverName() === 'mysql') {
            M50LocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget !== 'gramlyze.loc' || $this->localProof === null) {
                throw new RuntimeException('M50 requires explicit gramlyze.loc and fresh local proof.');
            }
        }

        return parent::connection($expected, $writing);
    }

    public static function syncPaths(array $patch): array
    {
        $paths = [...self::SYNC_CODE_PATHS, Package::MASTER, Package::BEFORE, Package::SOURCE,
            'docs/content/theory-sentence-builders/m50-obligation-advice.v1.json', self::LINKS, ...array_column($patch['targets'], 'path')];
        $paths = array_values(array_unique($paths)); sort($paths);

        return $paths;
    }

    public static function sourcePaths(array $patch, ?string $root = null): array
    {
        $root ??= base_path();
        $paths = [...self::syncPaths($patch), 'app/Services/M26ContentPatch.php', 'app/Services/M11LocalTargetGuard.php',
            'app/Services/M49LocalTargetGuard.php', 'app/Services/PracticeCompletionPatch.php',
            'app/Support/M49AdviceModalsPackage.php', 'app/Support/M26DetailPackage.php', 'app/Support/TextBlock/TextBlockUuidGenerator.php'];
        foreach (self::loadLinks($root)['pairs'] as $pair) { $paths[] = $pair['provenance']['source_path']; }
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
            || $target['path'] !== \App\Support\TheorySentenceBuilderAcceptance::definitionPath($owner)
            || $target['local_url'] !== 'http://gramlyze.loc/theory/modal-verbs/'.Package::SLUGS[$i]
            || count($before['page']['blocks']) !== 6 || count($after['page']['blocks']) !== 7) {
            throw new RuntimeException('M50 exact definition/category/slot scope differs.');
        }
        $protected = $after; $protected['page']['blocks'] = $before['page']['blocks'];
        foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $before['page'][$field]; }
        if ($protected !== $before) { throw new RuntimeException('M50 protected definition metadata changed.'); }
        foreach ($before['page']['blocks'] as $slot => $old) {
            $new = $after['page']['blocks'][$slot]; unset($old['body'], $new['body']);
            if ($old !== $new) { throw new RuntimeException('M50 continuing block metadata/order changed.'); }
        }
        $practice = $after['page']['blocks'][6];
        if ($practice['type'] !== 'practice-set' || ($practice['level'] ?? null) !== 'A2'
            || !str_starts_with($practice['uuid_key'] ?? '', 'm50:'.Package::SLUGS[$i].':')) {
            throw new RuntimeException('M50 new practice identity differs.');
        }
    }

    /** Capacity is checked after local identity, before backup, locks or content writes. */
    private function assertCapacity(array $patch): void
    {
        if ($this->db->getDriverName() !== 'mysql') { return; }
        $column = (array) $this->db->selectOne('SELECT DATA_TYPE AS kind, CHARACTER_OCTET_LENGTH AS max_bytes '
            .'FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$this->db->getDatabaseName(), 'text_blocks', 'body']);
        if (!in_array($column['kind'] ?? null, ['tinytext', 'text', 'mediumtext', 'longtext'], true)
            || !is_numeric($column['max_bytes'] ?? null)) { throw new RuntimeException('M50 actual body column capacity is unknown.'); }
        foreach ($patch['targets'] as $target) {
            PracticeCompletionPatch::assertBodyCapacity($target['after']['page']['subtitle_html'], (int) $column['max_bytes']);
            foreach ($target['after']['page']['blocks'] as $block) {
                PracticeCompletionPatch::assertBodyCapacity($block['body'], (int) $column['max_bytes']);
            }
        }
    }

    public function plan(bool $lock = false): array
    {
        $connection = $this->connection(); $root = dirname($this->databasePath);
        [$before, $patch, $master] = Package::load($root); Package::validate($before, $patch, $master);
        $this->assertCapacity($patch);
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => self::NAMES, 'connection' => $connection,
            'sources' => [], 'pages' => [], 'updates' => [], 'inserts' => []];
        $baseline = null;
        if ($this->localTarget !== null) {
            $baseline = $this->assertServedSources($root, $patch);
            foreach ([self::BEFORE_FILE, self::FROZEN_FILE, self::SYNC_RECEIPT, self::LINKS_BEFORE_FILE,
                self::CONTROLLER_BEFORE_FILE, self::CONTROLLER_WT_BEFORE_FILE] as $name) {
                $plan['sources']['private:'.$name] = self::fileHash($this->privateDirectory.'/'.$name);
            }
        }
        foreach (self::sourcePaths($patch, $root) as $path) { $plan['sources'][$path] = self::fileHash($root.'/'.$path); }
        $links = $this->links(); $this->assertQuestionSnapshots($links, $lock);
        $states = []; $ids = [];
        foreach ($patch['targets'] as $i => $target) {
            $name = $target['identity']; $old = $before['targets'][$i]['before']; $new = $target['after'];
            self::assertDefinitionScope($old, $new, $target, $i);
            if (json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR) !== $new) {
                throw new RuntimeException('M50 active source differs from exact AFTER.');
            }
            $owners = $this->rows('pages', static fn ($q) => $q->where('seeder', $name), $lock);
            if (count($owners) !== 1) { throw new RuntimeException('M50 page owner is missing/ambiguous.'); }
            $page = $owners[0];
            $chain = $this->categoryChain($page['page_category_id'], $lock);
            if ($page['id'] !== $target['page_binding']['id'] || $page['page_category_id'] !== $target['page_binding']['page_category_id']
                || $page['slug'] !== $old['slug'] || $page['title'] !== $old['page']['title'] || $page['type'] !== 'theory'
                || array_column($chain, 'slug') !== ['modal-verbs']) { throw new RuntimeException('M50 Page/category binding differs.'); }
            $rows = $this->rows('text_blocks', static fn ($q) => $q->where('page_id', $page['id']), $lock);
            if ($baseline !== null) { M49ContentPatch::assertMetadataBaseline($baseline['pages'][$name], $page, $rows); }
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'heading' => null,
                'level' => $old['page']['subtitle_level'] ?? null, 'body' => $old['page']['subtitle_html'],
                'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
            $newSubtitle = $subtitle; $newSubtitle['body'] = $new['page']['subtitle_html'];
            $oldConfigs = [$subtitle, ...$old['page']['blocks']]; $newConfigs = [$newSubtitle, ...$new['page']['blocks']];
            $rowStates = [];
            $state = $this->candidate($plan['updates'], 'pages', $page, $name,
                ['text' => $old['page']['subtitle_text']], ['text' => $new['page']['subtitle_text']]);
            if ($state !== null) { $rowStates[] = $state; }
            foreach ($oldConfigs as $position => $config) {
                $uuid = $this->resolveUuid($name, $config, $position);
                if ($uuid !== $this->resolveUuid($name, $newConfigs[$position], $position)) { throw new RuntimeException('M50 continuing UUID changed.'); }
                $global = $this->rows('text_blocks', static fn ($q) => $q->where('uuid', $uuid), $lock);
                if (count($global) !== 1) { throw new RuntimeException('M50 continuing row missing/ambiguous.'); }
                $row = $global[0]; $this->assertOwner($row, $page, $name, $position);
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                    if ($row[$field] !== ($config[$field] ?? null)) { throw new RuntimeException('M50 continuing row metadata differs.'); }
                }
                $ids[] = $row['id'];
                $state = $this->candidate($plan['updates'], 'text_blocks', $row, $name,
                    ['body' => $config['body']], ['body' => $newConfigs[$position]['body']]);
                if ($state !== null) { $rowStates[] = $state; }
            }
            $rowStates = array_values(array_unique($rowStates));
            if (count($rowStates) !== 1) { throw new RuntimeException('M50 partial/manual owner state.'); }
            $state = $rowStates[0]; $practice = $newConfigs[7]; $uuid = $this->resolveUuid($name, $practice, 7);
            $fields = ['uuid' => $uuid, 'page_id' => $page['id'], 'page_category_id' => $page['page_category_id'], 'locale' => 'uk',
                'type' => 'practice-set', 'column' => $practice['column'], 'heading' => $practice['heading'] ?? null,
                'css_class' => $practice['css_class'] ?? null, 'sort_order' => 7, 'body' => $practice['body'], 'level' => 'A2', 'seeder' => $name];
            $global = $this->rows('text_blocks', static fn ($q) => $q->where('uuid', $uuid), $lock);
            if ($state === 'before') {
                if ($global !== []) { throw new RuntimeException('M50 new practice UUID collision or partial state.'); }
                $plan['inserts'][] = ['table' => 'text_blocks', 'seeder' => $name, 'fields' => $fields, 'sha256' => self::digest($fields)];
            } else {
                if (count($global) !== 1) { throw new RuntimeException('M50 applied practice missing/ambiguous.'); }
                foreach ($fields as $field => $value) { if ($global[0][$field] !== $value) { throw new RuntimeException('M50 applied practice edited.'); } }
                if (empty($global[0]['created_at']) || $global[0]['created_at'] !== $global[0]['updated_at']) { throw new RuntimeException('M50 practice timestamps changed.'); }
                $ids[] = $global[0]['id'];
            }
            if (count(array_filter($rows, static fn ($row) => $row['locale'] === 'uk')) !== 7 + (int) ($state === 'after')) {
                throw new RuntimeException('M50 unexpected UK row count.');
            }
            $states[] = $state;
            $plan['pages'][$name] = ['page' => $page, 'category_ancestry' => $chain, 'blocks' => $rows,
                'relations' => $this->relations($page, $rows, $lock)];
        }
        if (count(array_unique($states)) !== 1) { throw new RuntimeException('M50 partial package; no writes.'); }
        $plan['state'] = $states[0];
        if (($plan['state'] === 'before' && count($plan['inserts']) !== 3)
            || ($plan['state'] === 'after' && ($plan['updates'] !== [] || $plan['inserts'] !== []))) { throw new RuntimeException('M50 insert/no-op scope differs.'); }
        $this->assertLinkPairs($links, $plan['state'], $lock);
        $this->assertNeedEligiblePool($links, $plan['state'], $lock);
        $plan['link_state'] = $plan['state'];
        $plan['link_inserts'] = $plan['state'] === 'before' ? array_map(static function ($pair): array {
            $fields = array_intersect_key($pair, array_flip(['question_uuid', 'text_block_uuid', 'position']));

            return ['table' => 'question_theory_text_blocks', 'fields' => $fields, 'sha256' => self::digest($fields)];
        }, $links['pairs']) : [];
        $plan['protected'] = $this->protectedFingerprints($ids); ksort($plan['sources']); $plan['sha256'] = self::digest($plan);

        return $plan;
    }

    private function assertOwner(array $row, array $page, string $name, int $position): void
    {
        if ($row['seeder'] !== $name || $row['locale'] !== 'uk' || $row['page_id'] !== $page['id']
            || $row['page_category_id'] !== $page['page_category_id'] || (int) $row['sort_order'] !== $position) {
            throw new RuntimeException('M50 row ownership/order differs.');
        }
    }

    protected function protectedFingerprints(array $targetIds): array
    {
        $schema = $this->db->getDriverName() === 'sqlite' ? 'main' : $this->db->getDatabaseName();
        $tables = $this->db->getSchemaBuilder()->getTableListing(schema: $schema, schemaQualified: false); sort($tables);
        $pageIds = $this->db->table('pages')->whereIn('seeder', self::NAMES)->pluck('id')->all(); $out = [];
        $links = $this->links();
        foreach ($tables as $table) {
            $columns = $this->db->getSchemaBuilder()->getColumnListing($table); $query = $this->db->table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            if ($table === 'text_blocks') { $query->whereNotIn('id', $targetIds); }
            $hash = hash_init('sha256'); $count = 0;
            foreach ($query->cursor() as $record) {
                $row = (array) $record;
                if ($table === 'question_theory_text_blocks' && $this->isApprovedLinkRow($row, $links)) { continue; }
                if ($table === 'pages' && in_array($row['id'], $pageIds, true)) { unset($row['text']); }
                hash_update($hash, self::digest($row)."\n"); $count++;
            }
            $out[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
        }

        return $out;
    }

    /** Course/continuing relation projection never includes newly appended practices. */
    protected function relations(array $page, array $blocks, bool $lock): array
    {
        $continuing = array_values(array_filter($blocks, static fn ($block) => !($block['locale'] === 'uk'
            && in_array($block['seeder'], self::NAMES, true) && $block['type'] === 'practice-set' && (int) $block['sort_order'] === 7)));

        return parent::relations($page, $continuing, $lock);
    }

    /** Native M26 calls this hook inside the same transaction after inserting each practice. */
    protected function afterInsert(int $index): void
    {
        $rows = $this->rows('text_blocks', static fn ($query) => $query->where('uuid', self::NEED_PRACTICE_UUID), true);
        if ($rows === []) { return; }
        $row = $rows[0];
        [, $patch] = Package::load(dirname($this->databasePath));
        $practice = $patch['targets'][2]['after']['page']['blocks'][6];
        if (count($rows) !== 1 || $row['seeder'] !== Package::OWNERS[2] || $row['page_id'] !== 76
            || $row['page_category_id'] !== 15 || $row['locale'] !== 'uk' || $row['type'] !== 'practice-set'
            || $row['level'] !== 'A2' || (int) $row['sort_order'] !== 7 || $row['body'] !== $practice['body']) {
            throw new RuntimeException('M50 Need link owner/practice identity differs.');
        }
        $links = $this->links(); $this->assertQuestionSnapshots($links, true); $this->assertLinkPairs($links, 'before', true);
        $timestamp = now()->toDateTimeString();
        foreach ($links['pairs'] as $n => $pair) {
            $fields = array_intersect_key($pair, array_flip(['question_uuid', 'text_block_uuid', 'position']));
            $this->db->table('question_theory_text_blocks')->insert($fields + ['created_at' => $timestamp, 'updated_at' => $timestamp]);
            $this->afterPracticeLinkInsert($n);
        }
        $this->assertLinkPairs($links, 'after', true);
    }

    protected function afterPracticeLinkInsert(int $index): void {}

    public static function loadLinks(?string $root = null): array
    {
        $root ??= base_path(); $path = $root.'/'.self::LINKS;
        if (preg_match('/^[a-f0-9]{64}$/D', self::LINKS_SHA) !== 1 || self::fileHash($path) !== self::LINKS_SHA) {
            throw new RuntimeException('M50 Need link curation is not frozen or its source changed.');
        }
        $links = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($links['schema_version'] ?? null) !== 1 || ($links['base_sha'] ?? null) !== Package::BASE_SHA
            || ($links['identity'] ?? null) !== Package::OWNERS[2] || ($links['page_id'] ?? null) !== 76
            || ($links['practice_uuid'] ?? null) !== self::NEED_PRACTICE_UUID || ($links['question_type'] ?? null) !== '4'
            || ($links['level'] ?? null) !== 'A2' || array_column($links['pairs'] ?? [], 'question_id') !== self::LINK_QUESTION_IDS) {
            throw new RuntimeException('M50 exact five-question Need curation scope differs.');
        }
        foreach ($links['pairs'] as $pair) {
            if (($pair['text_block_uuid'] ?? null) !== self::NEED_PRACTICE_UUID || ($pair['position'] ?? null) !== 0
                || !is_string($pair['question_uuid'] ?? null)
                || !in_array($pair['bank_seeder'] ?? null, Package::BANK_SETS[2], true)
                || array_keys($pair['hashes'] ?? []) !== self::QUESTION_SNAPSHOT_FIELDS) {
                throw new RuntimeException('M50 curated pair/bank/hash fields differ.');
            }
            foreach ($pair['hashes'] as $sha) {
                if (!is_string($sha) || preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1) { throw new RuntimeException('M50 curated snapshot SHA is invalid.'); }
            }
            $source = $pair['provenance'];
            if (!str_starts_with($source['source_path'] ?? '', 'database/seeders/V3/') || str_contains($source['source_path'], '..')
                || !str_ends_with($source['source_path'], '.json') || preg_match('~^/questions/[0-9]+$~D', $source['source_uid'] ?? '') !== 1
                || preg_match('/^[a-f0-9]{64}$/D', $source['source_sha256'] ?? '') !== 1
                || preg_match('/^[a-f0-9]{64}$/D', $source['source_question_sha256'] ?? '') !== 1) {
                throw new RuntimeException('M50 independent question-source provenance differs.');
            }
        }
        if (count(array_unique(array_column($links['pairs'], 'question_uuid'))) !== 5) { throw new RuntimeException('M50 curation repeats a question UUID.'); }

        return $links;
    }

    protected function links(): array { return self::loadLinks(dirname($this->databasePath)); }

    private function isApprovedLinkRow(array $row, array $links): bool
    {
        foreach ($links['pairs'] as $pair) {
            if ($row['question_uuid'] === $pair['question_uuid'] && $row['text_block_uuid'] === $pair['text_block_uuid']
                && (int) $row['position'] === $pair['position']) { return true; }
        }

        return false;
    }

    private function assertLinkPairs(array $links, string $state, bool $lock): void
    {
        $rows = $this->rows('question_theory_text_blocks', static fn ($q) => $q->where('text_block_uuid', self::NEED_PRACTICE_UUID), $lock);
        if ($state === 'before' && $rows !== []) { throw new RuntimeException('M50 Need new link BEFORE is stale or collided.'); }
        if ($state === 'after') {
            $timestamps = [];
            foreach ($rows as $row) {
                if (empty($row['created_at']) || $row['created_at'] !== $row['updated_at']) {
                    throw new RuntimeException('M50 new link timestamps changed or are missing.');
                }
                $timestamps[] = $row['created_at'];
            }
            if (count(array_unique($timestamps)) !== 1) { throw new RuntimeException('M50 new links do not share their atomic creation timestamp.'); }
            $actual = array_map(static fn ($row) => array_intersect_key($row, array_flip(['question_uuid', 'text_block_uuid', 'position'])), $rows);
            $expected = array_map(static fn ($pair) => array_intersect_key($pair, array_flip(['question_uuid', 'text_block_uuid', 'position'])), $links['pairs']);
            usort($actual, static fn ($a, $b) => strcmp($a['question_uuid'], $b['question_uuid']));
            usort($expected, static fn ($a, $b) => strcmp($a['question_uuid'], $b['question_uuid']));
            if ($actual !== $expected) { throw new RuntimeException('M50 Need AFTER must have exactly the approved five position-zero links.'); }
        }
    }

    /** The native matcher is page-wide; finite curation must not admit old or extra same-bank A2 records. */
    private function assertNeedEligiblePool(array $links, string $state, bool $lock): void
    {
        $blocks = $this->rows('text_blocks', static fn ($q) => $q->where('page_id', 76)->where('page_category_id', 15)
            ->where('locale', 'uk')->where('seeder', Package::OWNERS[2]), $lock);
        $uuids = array_column($blocks, 'uuid');
        $pivots = $this->rows('question_theory_text_blocks', static fn ($q) => $q->whereIn('text_block_uuid', $uuids), $lock);
        $questions = $this->rows('questions', static fn ($q) => $q->whereIn('uuid', array_column($pivots, 'question_uuid'))
            ->orWhereIn('theory_text_block_uuid', $uuids), $lock);
        $eligible = array_values(array_filter($questions, static fn ($q) => (string) $q['type'] === '4'
            && $q['level'] === 'A2' && in_array($q['seeder'], Package::BANK_SETS[2], true)));
        $actual = array_column($eligible, 'id'); sort($actual);
        $expected = $state === 'after' ? self::LINK_QUESTION_IDS : []; sort($expected);
        if ($actual !== $expected) { throw new RuntimeException('M50 Need page-wide exact-bank A2 pool differs from the finite approved curation.'); }
    }

    protected function questionSnapshot(array $pair, bool $lock): array
    {
        $question = $this->rows('questions', static fn ($q) => $q->where('id', $pair['question_id']), $lock);
        if (count($question) !== 1 || $question[0]['uuid'] !== $pair['question_uuid']
            || $question[0]['seeder'] !== $pair['bank_seeder'] || (string) $question[0]['type'] !== '4' || $question[0]['level'] !== 'A2') {
            throw new RuntimeException('M50 curated question owner/type/level/UUID differs.');
        }
        $snapshot = ['question' => $question[0]];
        foreach (['answers' => 'question_answers', 'option_pivots' => 'question_option_question', 'verb_hints' => 'verb_hints',
            'hints' => 'question_hints', 'variants' => 'question_variants'] as $key => $table) {
            $snapshot[$key] = $this->rows($table, static fn ($q) => $q->where('question_id', $pair['question_id']), $lock);
        }
        $optionIds = array_values(array_unique([...array_column($snapshot['answers'], 'option_id'),
            ...array_column($snapshot['option_pivots'], 'option_id'), ...array_column($snapshot['verb_hints'], 'option_id')]));
        $snapshot['options'] = $this->rows('question_options', static fn ($q) => $q->whereIn('id', $optionIds), $lock);

        return $snapshot;
    }

    private function assertQuestionSnapshots(array $links, bool $lock): void
    {
        $root = dirname($this->databasePath);
        foreach ($links['pairs'] as $pair) {
            $snapshot = $this->questionSnapshot($pair, $lock);
            foreach (self::QUESTION_SNAPSHOT_FIELDS as $key) {
                if (self::digest($snapshot[$key]) !== $pair['hashes'][$key]) { throw new RuntimeException('M50 original question/answer/option snapshot drifted: '.$key); }
            }
            $source = $pair['provenance'];
            if (self::fileHash($root.'/'.$source['source_path']) !== $source['source_sha256']) { throw new RuntimeException('M50 immutable source bank definition changed.'); }
            $bank = json_decode(file_get_contents($root.'/'.$source['source_path']), true, flags: JSON_THROW_ON_ERROR);
            $i = (int) substr($source['source_uid'], strlen('/questions/')); $question = $bank['questions'][$i] ?? null;
            if (!is_array($question) || self::digest($question) !== $source['source_question_sha256']) {
                throw new RuntimeException('M50 finite independent semantic source record differs.');
            }
        }
    }

    public static function fileHash(string $file): string
    {
        if (!is_file($file) || is_link($file)) { throw new RuntimeException('M50 source/evidence missing or linked.'); }

        return hash_file('sha256', $file);
    }

    /** Four literal nonvisual insertions only; every existing byte, including EOL/BOM, is retained. */
    public static function approvedControllerAfter(string $before): string
    {
        if (str_contains($before, 'M50ObligationAdvicePackage::preserveExcludedTextBlock')) {
            throw new RuntimeException('M50 controller BEFORE already contains owned insertions.');
        }
        $lf = str_replace("\r\n", "\n", $before);
        if (str_contains($lf, "\r")) { throw new RuntimeException('M50 controller uses unsupported mixed line endings.'); }
        $eol = str_contains($before, "\r\n") ? "\r\n" : "\n";
        if ($eol === "\r\n" && str_contains(str_replace("\r\n", '', $before), "\n")) {
            throw new RuntimeException('M50 controller BEFORE uses mixed line endings.');
        }
        $first = <<<'PHP'
                    if (! $block || $block->uuid === null) {
                        continue;
                    }
                    if (isset($seenBlockUuids[$block->uuid])) {
PHP;
        $second = <<<'PHP'
                if (! $block || $block->uuid === null || isset($seen[$block->uuid])) {
                    continue;
                }
                $seen[$block->uuid] = true;
PHP;
        $firstAfter = str_replace('                    if (isset($seenBlockUuids[$block->uuid])) {',
            "                    \$block = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$block, 'standalone_test');\n"
            ."                    if (\$block === null) { continue; }\n                    if (isset(\$seenBlockUuids[\$block->uuid])) {", $first);
        $secondAfter = str_replace('                $seen[$block->uuid] = true;',
            "                \$block = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$block, 'standalone_test');\n"
            ."                if (\$block === null) { continue; }\n                \$seen[\$block->uuid] = true;", $second);
        $qFallback = "                \$textBlock = TextBlock::where('uuid', \$q->theory_text_block_uuid)->first();\n                if (\$textBlock) {";
        $questionFallback = "            \$textBlock = TextBlock::where('uuid', \$question->theory_text_block_uuid)->first();\n            if (\$textBlock) {";
        $hunks = [
            [$first, $firstAfter],
            [$qFallback, str_replace("                if (\$textBlock) {",
                "                if (\$textBlock) { \$textBlock = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\n"
                ."                if (\$textBlock) {", $qFallback)],
            [$second, $secondAfter],
            [$questionFallback, str_replace("            if (\$textBlock) {",
                "            if (\$textBlock) { \$textBlock = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\n"
                ."            if (\$textBlock) {", $questionFallback)],
        ];
        $after = $before;
        foreach ($hunks as [$anchor, $replacement]) {
            $anchor = str_replace("\n", $eol, $anchor); $replacement = str_replace("\n", $eol, $replacement);
            if (substr_count($after, $anchor) !== 1) { throw new RuntimeException('M50 controller exact insertion anchor missing or ambiguous.'); }
            $after = str_replace($anchor, $replacement, $after);
        }

        return $after;
    }

    private function assertControllerSync(string $rootAfter, string $worktreeAfter): void
    {
        $rootFile = $this->privateDirectory.'/'.self::CONTROLLER_BEFORE_FILE;
        $wtFile = $this->privateDirectory.'/'.self::CONTROLLER_WT_BEFORE_FILE;
        if (self::fileHash($rootFile) !== self::CONTROLLER_BEFORE_SHA || self::fileHash($wtFile) !== self::CONTROLLER_WT_BEFORE_SHA) {
            throw new RuntimeException('M50 controller independent ROOT/WT BEFORE snapshots drifted.');
        }
        $rootBefore = file_get_contents($rootFile); $wtBefore = file_get_contents($wtFile);
        if (hash('sha256', str_replace("\r\n", "\n", $wtBefore)) !== self::CONTROLLER_GIT_BASE_LF_SHA
            || $rootAfter !== self::approvedControllerAfter($rootBefore) || $worktreeAfter !== self::approvedControllerAfter($wtBefore)) {
            throw new RuntimeException('M50 controller differs beyond the four approved insertions; foreign bytes are protected.');
        }
    }

    private function assertServedSources(string $root, array $patch): array
    {
        $normal = static fn ($path) => strtolower(str_replace('\\', '/', (string) realpath($path)));
        if (!in_array($normal($root), [$normal(M50LocalTargetGuard::ROOT), $normal(self::SOURCE_WORKTREE)], true)
            || $normal(self::SOURCE_WORKTREE) !== strtolower(self::SOURCE_WORKTREE)) {
            throw new RuntimeException('M50 active/source application root differs from the finite ROOT/WT allowlist.');
        }
        $beforeFile = $this->privateDirectory.'/'.self::BEFORE_FILE; $frozenFile = $this->privateDirectory.'/'.self::FROZEN_FILE;
        if (!hash_equals(self::BEFORE_SHA, self::fileHash($beforeFile)) || !hash_equals(self::FROZEN_SHA, self::fileHash($frozenFile))) {
            throw new RuntimeException('M50 independent private BEFORE/frozen receipt changed.');
        }
        $before = json_decode(file_get_contents($beforeFile), true, flags: JSON_THROW_ON_ERROR);
        $frozen = json_decode(file_get_contents($frozenFile), true, flags: JSON_THROW_ON_ERROR);
        $linksBeforeFile = $this->privateDirectory.'/'.self::LINKS_BEFORE_FILE;
        if (!hash_equals(self::LINKS_BEFORE_SHA, self::fileHash($linksBeforeFile))) {
            throw new RuntimeException('M50 independent private link BEFORE changed.');
        }
        $linksBefore = json_decode(file_get_contents($linksBeforeFile), true, flags: JSON_THROW_ON_ERROR);
        $links = $this->links();
        $pairs = array_map(static fn ($pair) => array_intersect_key($pair, array_flip(['question_uuid', 'text_block_uuid', 'position'])), $links['pairs']);
        if (($linksBefore['base_sha'] ?? null) !== Package::BASE_SHA || ($linksBefore['before_sha256'] ?? null) !== self::BEFORE_SHA
            || ($linksBefore['new_practice_absent'] ?? null) !== true || ($linksBefore['all_new_practice_pivots_absent'] ?? null) !== true
            || ($linksBefore['practice_uuid'] ?? null) !== self::NEED_PRACTICE_UUID || ($linksBefore['pairs'] ?? null) !== $pairs) {
            throw new RuntimeException('M50 public curation differs from independently captured absent pairs.');
        }
        foreach ($links['pairs'] as $pair) {
            $record = $linksBefore['questions'][$pair['question_uuid']] ?? [];
            foreach (self::QUESTION_SNAPSHOT_FIELDS as $key) {
                if (($record[$key.'_sha256'] ?? null) !== $pair['hashes'][$key]
                    || self::digest($record[$key] ?? null) !== $pair['hashes'][$key]) {
                    throw new RuntimeException('M50 public question hashes differ from private BEFORE.');
                }
            }
        }
        $sync = json_decode(file_get_contents($this->privateDirectory.'/'.self::SYNC_RECEIPT), true, flags: JSON_THROW_ON_ERROR);
        $paths = array_column($sync['files'] ?? [], 'path'); sort($paths);
        if (($sync['base_sha'] ?? null) !== Package::BASE_SHA || $paths !== self::syncPaths($patch)
            || ($sync['source_worktree'] ?? null) !== self::SOURCE_WORKTREE || ($sync['served_root'] ?? null) !== M50LocalTargetGuard::ROOT
            || ($sync['visual_before'] ?? null) !== ['file' => self::BEFORE_FILE, 'sha256' => self::BEFORE_SHA]) {
            throw new RuntimeException('M50 exact reviewed served-source sync differs.');
        }
        foreach ($sync['files'] as $file) {
            if (self::fileHash(M50LocalTargetGuard::ROOT.'/'.$file['path']) !== $file['root_after_sha256']
                || self::fileHash(self::SOURCE_WORKTREE.'/'.$file['path']) !== $file['worktree_sha256']) { throw new RuntimeException('M50 served/source bytes drifted.'); }
            $a = file_get_contents(M50LocalTargetGuard::ROOT.'/'.$file['path']); $b = file_get_contents(self::SOURCE_WORKTREE.'/'.$file['path']);
            if ($file['path'] === self::CONTROLLER_PATH) {
                $this->assertControllerSync($a, $b);
                continue;
            }
            if ($a !== $b && !(str_ends_with($file['path'], '.php') && str_replace("\r\n", "\n", $a) === str_replace("\r\n", "\n", $b))) {
                throw new RuntimeException('M50 served source differs beyond preserved PHP EOL.');
            }
        }
        foreach ($before['visual_hashes'] ?? [] as $path => $sha) {
            if (self::fileHash(M50LocalTargetGuard::ROOT.'/'.$path) !== $sha) { throw new RuntimeException('M50 visual source changed.'); }
        }
        if (empty($before['visual_hashes'])) { throw new RuntimeException('M50 visual BEFORE is incomplete.'); }
        foreach ($frozen['files'] ?? [] as $path => $sha) {
            if (self::fileHash(M50LocalTargetGuard::ROOT.'/'.$path) !== $sha) { throw new RuntimeException('M50 frozen/non-target source changed.'); }
        }
        if (empty($frozen['files'])) { throw new RuntimeException('M50 frozen BEFORE is incomplete.'); }

        return $before;
    }
}
