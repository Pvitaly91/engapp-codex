<?php

namespace App\Services;

use App\Support\TextBlock\TextBlockUuidGenerator;
use Illuminate\Database\Connection;
use RuntimeException;

/** M24 only: update established native content and insert one practice box per page. */
class AuthoredNativeContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\Tenses\\TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\TensesNarrativeTensesTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarB1MixedRevisionTheorySeeder',
    ];

    private const DEFINITIONS = [
        'seeders/Page_V3/Tenses/TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder/definition.json',
        'seeders/Page_V3/Tenses/TensesNarrativeTensesTheorySeeder/definition.json',
        'seeders/Page_V3/BasicGrammar/BasicGrammarB1MixedRevisionTheorySeeder/definition.json',
    ];

    private const ANCESTRY = [['tenses'], ['tenses'], ['mixed-revision']];
    private const ID = 'm24-authored-native-content-v1';
    private const MASTER_SHA256 = '33373412ed077bf7fa0aea8dde8a6d2c211c126288b6cedfdde299ded43bf5a2';
    private const MASTER = 'content/m24-authored-content.v1.json';
    private const MANIFEST = 'content-patches/m24-authored-native-before.json';

    public function __construct(private Connection $db, private string $databasePath, private string $privateDirectory,
        private ?string $localTarget = null, private ?string $localProof = null) {}

    public static function digest(array $value): string
    {
        return PronounContentRepair::digest($value);
    }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        $physical = (new PronounContentRepair($this->db, $this->databasePath, $this->privateDirectory))->connection($expected, $writing);
        if ($this->localTarget !== null) {
            app(M24LocalTargetGuard::class)->verify($this->db, $this->localTarget, $this->privateDirectory, $this->localProof, $physical);
        }
        return $physical;
    }

    public function plan(bool $lock = false): array
    {
        $masterPath = dirname($this->databasePath).'/docs/'.self::MASTER;
        $masterBytes = file_get_contents($masterPath);
        if (hash('sha256', $masterBytes) !== self::MASTER_SHA256) {
            throw new RuntimeException('Immutable M24 author master differs.');
        }
        $master = json_decode($masterBytes, true, flags: JSON_THROW_ON_ERROR);
        $manifestBytes = file_get_contents($this->databasePath.'/'.self::MANIFEST);
        $manifest = json_decode($manifestBytes, true, flags: JSON_THROW_ON_ERROR);
        if (($master['package'] ?? null) !== 'M24' || ($manifest['patch'] ?? null) !== self::ID
            || ($manifest['master_sha256'] ?? null) !== self::MASTER_SHA256
            || array_keys($manifest['definitions'] ?? []) !== self::NAMES
            || array_column($master['lessons'] ?? [], 'seeder') !== self::NAMES) {
            throw new RuntimeException('M24 master/before-manifest scope differs.');
        }
        $plan = ['patch' => self::ID, 'version' => 1, 'names' => self::NAMES,
            'connection' => $this->connection(), 'sources' => [self::MASTER => self::MASTER_SHA256,
                self::MANIFEST => hash('sha256', $manifestBytes)], 'pages' => [], 'updates' => [], 'inserts' => []];
        $states = [];
        foreach ($master['lessons'] as $index => $lesson) {
            $name = self::NAMES[$index];
            $relative = self::DEFINITIONS[$index];
            if (($lesson['seeder'] ?? null) !== $name || ($lesson['definition_path'] ?? null) !== 'database/'.$relative
                || ($lesson['category_path'] ?? null) !== self::ANCESTRY[$index]) {
                throw new RuntimeException('M24 author identity/category differs: '.$name);
            }
            $sourceBytes = file_get_contents($this->databasePath.'/'.$relative);
            $source = json_decode($sourceBytes, true, flags: JSON_THROW_ON_ERROR);
            $before = $manifest['definitions'][$name];
            $this->assertSource($lesson, $before, $source, $name);
            $plan['sources'][$relative] = hash('sha256', $sourceBytes);
            foreach (glob(dirname($this->databasePath.'/'.$relative).'/localizations/*.json') ?: [] as $localePath) {
                $path = substr(str_replace('\\', '/', $localePath), strlen(str_replace('\\', '/', $this->databasePath)) + 1);
                $plan['sources'][$path] = hash_file('sha256', $localePath);
            }
            $pages = $this->rows('pages', fn ($q) => $q->where('seeder', $name), $lock);
            if (count($pages) !== 1) { throw new RuntimeException('Missing or ambiguous M24 Page: '.$name); }
            $page = $pages[0];
            $category = $this->categoryChain($page['page_category_id'], $lock);
            if (array_column($category, 'slug') !== self::ANCESTRY[$index] || $page['slug'] !== $before['slug']
                || $page['title'] !== $before['page']['title'] || $page['type'] !== 'theory'
                || $page['seeder'] !== $name) {
                throw new RuntimeException('M24 Page/category identity differs: '.$name);
            }
            $blocks = $this->rows('text_blocks', fn ($q) => $q->where('page_id', $page['id']), $lock);
            $uk = array_values(array_filter($blocks, fn ($row) => $row['locale'] === 'uk'));
            $newConfig = $source['page']['blocks'][count($before['page']['blocks'])];
            $newUuid = $this->resolveUuid($name, $newConfig, count($before['page']['blocks']) + 1);
            $global = $this->rows('text_blocks', fn ($q) => $q->where('uuid', $newUuid), $lock);
            if (count($global) > 1 || ($global && ($global[0]['page_id'] != $page['id'] || $global[0]['seeder'] !== $name))) {
                throw new RuntimeException('M24 practice UUID collision with another owner: '.$newUuid);
            }
            $expectedOld = [['type' => 'subtitle', 'column' => 'header', 'level' => $before['page']['subtitle_level'] ?? null,
                'heading' => null, 'body' => $before['page']['subtitle_html'],
                'uuid_key' => $before['page']['subtitle_uuid_key'] ?? 'subtitle'], ...$before['page']['blocks']];
            $expectedNew = [['type' => 'subtitle', 'column' => 'header', 'level' => $source['page']['subtitle_level'] ?? null,
                'heading' => null, 'body' => $source['page']['subtitle_html'],
                'uuid_key' => $source['page']['subtitle_uuid_key'] ?? 'subtitle'], ...$source['page']['blocks']];
            if (count($uk) !== count($expectedOld) + (int) (bool) $global) {
                throw new RuntimeException('Unexpected M24 UK block count: '.$name);
            }
            $rowStates = [];
            $pageState = $this->candidate($plan['updates'], 'pages', $page, $name,
                ['text' => $before['page']['subtitle_text']], ['text' => $source['page']['subtitle_text']]);
            if ($pageState !== null) { $rowStates[] = $pageState; }
            foreach ($expectedOld as $position => $old) {
                $uuid = $this->resolveUuid($name, $old, $position);
                $matches = array_values(array_filter($uk, fn ($row) => $row['uuid'] === $uuid));
                if (count($matches) !== 1) { throw new RuntimeException('Missing/ambiguous old M24 UUID: '.$uuid); }
                $row = $matches[0];
                $new = $expectedNew[$position];
                foreach (['type', 'column', 'level'] as $field) {
                    if ($row[$field] !== ($old[$field] ?? null)) { throw new RuntimeException('M24 old block identity differs: '.$uuid.' '.$field); }
                }
                if ($row['heading'] !== ($old['heading'] ?? null) || $row['css_class'] !== ($old['css_class'] ?? null)
                    || $row['seeder'] !== $name || $row['page_category_id'] != $page['page_category_id']
                    || (int) $row['sort_order'] !== $position) {
                    throw new RuntimeException('M24 old block owner/order/heading differs: '.$uuid);
                }
                $state = $this->candidate($plan['updates'], 'text_blocks', $row, $name,
                    ['body' => $old['body']], ['body' => $new['body']]);
                if ($state !== null) { $rowStates[] = $state; }
            }
            $rowStates = array_unique($rowStates);
            if (count($rowStates) !== 1 || !in_array($rowStates[0], ['before', 'after'], true)) {
                throw new RuntimeException('Partially edited/unchanged M24 lesson: '.$name);
            }
            $state = $rowStates[0];
            if (($state === 'before' && $global) || ($state === 'after' && !$global)) {
                throw new RuntimeException('Partial M24 practice/content state: '.$name);
            }
            $insert = $this->insertFields($page, $newConfig, $name, $newUuid, count($before['page']['blocks']) + 1);
            if ($global) { $this->assertInserted($global[0], $insert); }
            if ($state === 'before') {
                $plan['inserts'][] = ['table' => 'text_blocks', 'seeder' => $name, 'fields' => $insert,
                    'sha256' => self::digest($insert)];
            }
            $snapshot = ['page' => $page, 'category_ancestry' => $category, 'blocks' => $blocks];
            $snapshot['relations'] = $this->relations($page, $blocks, $lock);
            $plan['pages'][$name] = $snapshot;
            $states[] = $state;
        }
        if (count(array_unique($states)) !== 1) { throw new RuntimeException('Partially applied M24 package; no write allowed.'); }
        $plan['state'] = $states[0];
        ksort($plan['sources']);
        $plan['sha256'] = self::digest($plan);
        return $plan;
    }

    private function assertSource(array $lesson, array $before, array $after, string $name): void
    {
        if (($before['seeder']['class'] ?? null) !== $name || ($after['seeder']['class'] ?? null) !== $name
            || ($after['page']['locale'] ?? null) !== 'uk' || ($after['type'] ?? null) !== 'theory'
            || ($before['page']['title'] ?? null) !== ($lesson['preserve_page_title'] ?? null)
            || count($before['page']['blocks'] ?? []) !== count($lesson['existing_blocks'] ?? [])
            || count($after['page']['blocks'] ?? []) !== count($before['page']['blocks']) + 1
            || count($lesson['append_blocks'] ?? []) !== 1) {
            throw new RuntimeException('M24 source identity or block count differs: '.$name);
        }
        $reverted = $after;
        $reverted['page']['subtitle_html'] = $before['page']['subtitle_html'];
        $reverted['page']['subtitle_text'] = $before['page']['subtitle_text'];
        foreach ($lesson['existing_blocks'] as $item) {
            $i = $item['source_index'];
            $old = $before['page']['blocks'][$i] ?? null;
            $new = $after['page']['blocks'][$i] ?? null;
            if (!$old || !$new || $old['type'] !== $item['preserve_type']
                || $old['column'] !== $item['preserve_column'] || $old['level'] !== $item['preserve_level']
                || json_decode($new['body'], true, flags: JSON_THROW_ON_ERROR) !== $item['replacement_body_json']) {
                throw new RuntimeException('M24 authored native block differs: '.$name.' index '.$i);
            }
            $reverted['page']['blocks'][$i]['body'] = $old['body'];
        }
        $append = $lesson['append_blocks'][0];
        if (($append['source_index'] ?? -1) !== count($before['page']['blocks'])
            || hash('sha256', $append['body_html']) !== $append['body_sha256']
            || $after['page']['blocks'][count($before['page']['blocks'])] !== [
                'type' => $append['type'], 'column' => $append['column'], 'heading' => $append['heading'],
                'level' => $append['level'], 'body' => $append['body_html'], 'uuid_key' => $append['uuid_key'],
                'inherit_base_tags' => $append['inherit_base_tags'], 'tags' => $append['tags']]
            || $after['page']['subtitle_html'] !== $lesson['subtitle_html']
            || $after['page']['subtitle_text'] !== $lesson['subtitle_text']) {
            throw new RuntimeException('M24 authored subtitle/practice differs: '.$name);
        }
        array_pop($reverted['page']['blocks']);
        if ($reverted !== $before) { throw new RuntimeException('M24 changed a non-authorized source field: '.$name); }
    }

    private function resolveUuid(string $name, array $block, int $position): string
    {
        $scope = $name.'::uk';
        if (trim((string) ($block['uuid'] ?? '')) !== '') { return trim($block['uuid']); }
        if (trim((string) ($block['uuid_key'] ?? '')) !== '') {
            return TextBlockUuidGenerator::generateWithKey($scope, trim($block['uuid_key']));
        }
        return TextBlockUuidGenerator::generate($scope, $position);
    }

    private function insertFields(array $page, array $block, string $name, string $uuid, int $position): array
    {
        return ['uuid' => $uuid, 'page_id' => $page['id'], 'page_category_id' => $page['page_category_id'],
            'locale' => 'uk', 'type' => $block['type'], 'column' => $block['column'],
            'heading' => $block['heading'], 'css_class' => $block['css_class'] ?? null,
            'sort_order' => $position, 'body' => $block['body'], 'level' => $block['level'], 'seeder' => $name];
    }

    private function assertInserted(array $row, array $fields): void
    {
        foreach ($fields as $field => $value) {
            if (!array_key_exists($field, $row) || $row[$field] !== $value) {
                throw new RuntimeException('M24 practice block differs in '.$field.' or belongs to another owner.');
            }
        }
        if (empty($row['id']) || empty($row['created_at']) || $row['created_at'] !== $row['updated_at']) {
            throw new RuntimeException('M24 practice block timestamp/identity was edited.');
        }
    }

    private function candidate(array &$updates, string $table, array $row, string $seeder, array $before, array $after): ?string
    {
        $actual = array_replace($before, array_intersect_key($row, $before));
        if ($actual === $after) { return $before === $after ? null : 'after'; }
        if ($actual !== $before) { throw new RuntimeException('Manual M24 content conflict: '.$seeder.' '.$table.' id='.$row['id']); }
        $updates[] = ['table' => $table, 'id' => $row['id'], 'uuid' => $row['uuid'] ?? null, 'seeder' => $seeder,
            'before' => $before, 'after' => $after, 'before_sha256' => self::digest($before), 'after_sha256' => self::digest($after)];
        return 'before';
    }

    private function categoryChain(int $id, bool $lock): array
    {
        $chain = []; $seen = [];
        while ($id) {
            if (isset($seen[$id]) || count($chain) >= 8) { throw new RuntimeException('Cyclic/too deep M24 category ancestry.'); }
            $seen[$id] = true;
            $rows = $this->rows('page_categories', fn ($q) => $q->where('id', $id), $lock);
            if (count($rows) !== 1 || $rows[0]['language'] !== 'uk' || $rows[0]['type'] !== 'theory') {
                throw new RuntimeException('M24 category identity differs.');
            }
            array_unshift($chain, $rows[0]);
            $id = $rows[0]['parent_id'];
        }
        return $chain;
    }

    private function rows(string $table, callable $filter, bool $lock): array
    {
        $query = $this->db->table($table);
        $filter($query);
        $rows = ($lock ? $query->lockForUpdate() : $query)->get()->map(fn ($row) => (array) $row)->all();
        usort($rows, fn ($a, $b) => ($a['id'] ?? 0) <=> ($b['id'] ?? 0) ?: strcmp(self::digest($a), self::digest($b)));
        return $rows;
    }

    private function relations(array $page, array $blocks, bool $lock): array
    {
        $ids = array_column($blocks, 'id'); $uuids = array_column($blocks, 'uuid');
        $out = [];
        foreach (['page_tag' => ['page_id', [$page['id']]], 'tag_text_block' => ['text_block_id', $ids],
            'page_category_tag' => ['page_category_id', [$page['page_category_id']]],
            'question_theory_text_blocks' => ['text_block_uuid', $uuids]] as $table => [$column, $values]) {
            $out[$table] = $this->rows($table, fn ($q) => $q->whereIn($column, $values), $lock);
        }
        $tagIds = array_unique(array_merge(array_column($out['page_tag'], 'tag_id'), array_column($out['tag_text_block'], 'tag_id'), array_column($out['page_category_tag'], 'tag_id')));
        $out['tags'] = $this->rows('tags', fn ($q) => $q->whereIn('id', $tagIds), $lock);
        $out['questions'] = $this->rows('questions', fn ($q) => $q->whereIn('uuid', array_column($out['question_theory_text_blocks'], 'question_uuid'))
            ->orWhereIn('theory_text_block_uuid', $uuids), $lock);
        $questionIds = array_column($out['questions'], 'id');
        foreach (['question_answers', 'question_option_question', 'question_tag', 'question_marker_tag',
            'verb_hints', 'question_hints', 'question_variants'] as $table) {
            $out[$table] = $this->rows($table, fn ($q) => $q->whereIn('question_id', $questionIds), $lock);
        }
        $optionIds = array_unique(array_merge(array_column($out['question_answers'], 'option_id'),
            array_column($out['question_option_question'], 'option_id'), array_column($out['verb_hints'], 'option_id')));
        $out['question_options'] = $this->rows('question_options', fn ($q) => $q->whereIn('id', $optionIds), $lock);
        $out['saved_grammar_test_questions'] = $this->rows('saved_grammar_test_questions',
            fn ($q) => $q->whereIn('question_uuid', array_column($out['questions'], 'uuid')), $lock);
        return $out;
    }

    public function savePlan(string $path): array
    {
        $plan = $this->plan();
        $this->writeNew($path, $plan);
        return $plan;
    }

    public function apply(string $planPath, string $backupPath, ?string $expectedDatabase = null): array
    {
        $this->assertWriteEnvironment();
        $this->connection($expectedDatabase, true);
        $preview = $this->readPlan($planPath);
        return $this->db->transaction(function () use ($preview, $backupPath): array {
            $current = $this->plan(true);
            if ($current['state'] === 'after' && $current['sources'] === $preview['sources']
                && $current['connection'] === $preview['connection']) {
                return ['status' => 'no-op', 'updated' => 0, 'inserted' => 0];
            }
            if ($current !== $preview || $preview['state'] !== 'before') {
                throw new RuntimeException('M24 preview is stale/incomplete; no writes.');
            }
            $this->writeNew($backupPath, $preview);
            foreach ($preview['updates'] as $i => $change) {
                $this->update($change, false); $this->afterUpdate($i);
            }
            foreach ($preview['inserts'] as $i => $insert) {
                $this->insert($insert); $this->afterInsert($i);
            }
            $after = $this->plan(true);
            if ($after['state'] !== 'after') { throw new RuntimeException('M24 postcondition failed; transaction rolled back.'); }
            $this->assertProjectedAfter($preview, $after);
            return ['status' => 'applied', 'updated' => count($preview['updates']),
                'inserted' => count($preview['inserts']), 'backup' => $backupPath];
        });
    }

    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        $this->assertWriteEnvironment();
        $this->connection($expectedDatabase, true);
        $backup = $this->readPlan($backupPath);
        if ($backup['state'] !== 'before' || count($backup['inserts']) !== 3) {
            throw new RuntimeException('M24 restore requires the exact before backup.');
        }
        return $this->db->transaction(function () use ($backup): array {
            $current = $this->plan(true);
            if ($current === $backup) { return ['status' => 'no-op', 'updated' => 0, 'deleted' => 0]; }
            if ($current['state'] !== 'after') { throw new RuntimeException('M24 restore refused: edited/partial current state.'); }
            $this->assertProjectedAfter($backup, $current);
            foreach ($backup['inserts'] as $insert) { $this->deleteInserted($current, $insert); }
            foreach ($backup['updates'] as $i => $change) {
                $this->update($change, true); $this->afterUpdate($i);
            }
            if ($this->plan(true) !== $backup) { throw new RuntimeException('M24 restore postcondition failed; transaction rolled back.'); }
            return ['status' => 'restored', 'updated' => count($backup['updates']), 'deleted' => count($backup['inserts'])];
        });
    }

    protected function afterUpdate(int $index): void {}
    protected function afterInsert(int $index): void {}

    private function assertProjectedAfter(array $before, array $after): void
    {
        if ($before['sources'] !== $after['sources'] || $before['connection'] !== $after['connection']) {
            throw new RuntimeException('M24 source/connection changed since preview.');
        }
        $projected = $after['pages'];
        foreach ($before['inserts'] as $insert) {
            $name = $insert['seeder']; $fields = $insert['fields'];
            $matches = array_filter($projected[$name]['blocks'], fn ($row) => $row['uuid'] === $fields['uuid']);
            if (count($matches) !== 1) { throw new RuntimeException('M24 new box missing/ambiguous after apply.'); }
            $this->assertInserted(array_values($matches)[0], $fields);
            $projected[$name]['blocks'] = array_values(array_filter($projected[$name]['blocks'],
                fn ($row) => $row['uuid'] !== $fields['uuid']));
        }
        foreach ($before['updates'] as $change) {
            if ($change['table'] === 'pages') {
                $projected[$change['seeder']]['page'] = array_replace($projected[$change['seeder']]['page'], $change['before']);
            } else {
                foreach ($projected[$change['seeder']]['blocks'] as &$row) {
                    if ($row['id'] === $change['id']) { $row = array_replace($row, $change['before']); }
                }
                unset($row);
            }
        }
        if ($projected !== $before['pages']) {
            throw new RuntimeException('M24 protected page, block or relation changed since preview.');
        }
    }

    private function assertWriteEnvironment(): void
    {
        if (!app()->environment('local', 'testing') && $this->localTarget === null) {
            throw new RuntimeException('Production-profile M24 writes require verified gramlyze.loc local-target.');
        }
    }

    private function update(array $change, bool $reverse): void
    {
        $fields = match ($change['table'] ?? '') { 'pages' => ['text'], 'text_blocks' => ['body'],
            default => throw new RuntimeException('Disallowed M24 update table.') };
        $old = $change[$reverse ? 'after' : 'before']; $new = $change[$reverse ? 'before' : 'after'];
        if (array_keys($old) !== $fields || array_keys($new) !== $fields) {
            throw new RuntimeException('Disallowed M24 update fields.');
        }
        $q = $this->db->table($change['table'])->where('id', $change['id'])->where('seeder', $change['seeder']);
        if ($change['table'] === 'text_blocks') { $q->where('uuid', $change['uuid'])->where('locale', 'uk'); }
        foreach ($old as $field => $value) { $q->where($field, $value); }
        if ($q->update($new) !== 1) { throw new RuntimeException('Concurrent M24 update; transaction rolled back.'); }
    }

    private function insert(array $insert): void
    {
        if ($insert['table'] !== 'text_blocks' || $insert['sha256'] !== self::digest($insert['fields'])) {
            throw new RuntimeException('Invalid M24 insert plan.');
        }
        $fields = $insert['fields'];
        if ($this->db->table('text_blocks')->where('uuid', $fields['uuid'])->exists()) {
            throw new RuntimeException('M24 practice UUID collision before insert.');
        }
        $timestamp = now()->toDateTimeString();
        $this->db->table('text_blocks')->insert($fields + ['created_at' => $timestamp, 'updated_at' => $timestamp]);
    }

    private function deleteInserted(array $current, array $insert): void
    {
        $fields = $insert['fields']; $name = $insert['seeder'];
        $matches = array_values(array_filter($current['pages'][$name]['blocks'], fn ($row) => $row['uuid'] === $fields['uuid']));
        if (count($matches) !== 1) { throw new RuntimeException('M24 new box missing during restore.'); }
        $row = $matches[0]; $this->assertInserted($row, $fields);
        $q = $this->db->table('text_blocks')->where('id', $row['id'])->where('uuid', $fields['uuid']);
        foreach ($fields as $field => $value) { $q->where($field, $value); }
        $q->where('created_at', $row['created_at'])->where('updated_at', $row['updated_at']);
        if ($q->delete() !== 1) { throw new RuntimeException('Concurrent M24 new box edit; restore rolled back.'); }
    }

    private function readPlan(string $path): array
    {
        $this->assertPrivatePath($path);
        $plan = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $hash = $plan['sha256'] ?? null; unset($plan['sha256']);
        if (($plan['patch'] ?? null) !== self::ID || ($plan['version'] ?? null) !== 1
            || ($plan['names'] ?? null) !== self::NAMES || !is_string($hash)
            || !hash_equals($hash, self::digest($plan)) || !isset($plan['pages'], $plan['updates'], $plan['inserts'], $plan['sources'])) {
            throw new RuntimeException('Corrupt/incomplete M24 plan or backup.');
        }
        foreach ($plan['updates'] as $change) {
            if (($change['before_sha256'] ?? null) !== self::digest($change['before'] ?? [])
                || ($change['after_sha256'] ?? null) !== self::digest($change['after'] ?? [])) {
                throw new RuntimeException('M24 update value hash mismatch.');
            }
        }
        foreach ($plan['inserts'] as $insert) {
            if (($insert['sha256'] ?? null) !== self::digest($insert['fields'] ?? [])) {
                throw new RuntimeException('M24 insert value hash mismatch.');
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
            throw new RuntimeException('M24 evidence must be a direct private JSON file.');
        }
    }

    private function writeNew(string $path, array $value): void
    {
        $this->assertPrivatePath($path);
        $bytes = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        $file = @fopen($path, 'x');
        if (!$file) { throw new RuntimeException('M24 exclusive evidence already exists; no overwrite.'); }
        @chmod($path, 0600);
        try {
            if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) {
                throw new RuntimeException('Incomplete M24 evidence write; no DB updates allowed.');
            }
        } finally { fclose($file); }
    }
}
