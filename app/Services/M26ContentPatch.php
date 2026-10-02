<?php

namespace App\Services;

use App\Support\M26DetailPackage;
use App\Support\TextBlock\TextBlockUuidGenerator;
use Illuminate\Database\Connection;
use RuntimeException;

/** M26-R2 only. Transaction/backup/restore primitives retained from M24. Never seeds. */
class M26ContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousCategorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousFormsTheorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousNegativesTheorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousTimeExpressionsTheorySeeder',
    ];
    private const ID = 'm26-ppc-layers-r2-v1';

    public function __construct(private Connection $db, private string $databasePath, private string $privateDirectory,
        private ?string $localTarget = null, private ?string $localProof = null) {}

    public static function digest(array $value): string { return PronounContentRepair::digest($value); }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        $physical = (new PronounContentRepair($this->db, $this->databasePath, $this->privateDirectory))->connection($expected, $writing);
        if ($this->localTarget !== null) {
            app(M26LocalTargetGuard::class)->verify($this->db, $this->localTarget, $this->privateDirectory, $this->localProof, $physical);
        } elseif ($writing && $this->db->getDriverName() !== 'sqlite') {
            throw new RuntimeException('M26 working writes require explicit physical gramlyze.loc proof.');
        }
        return $physical;
    }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath);
        [$master, $manifest] = M26DetailPackage::load($root);
        if (array_column($master['targets'], 'identity') !== self::NAMES || array_keys($manifest['definitions']) !== self::NAMES) {
            throw new RuntimeException('M26 exact five-identity scope differs.');
        }
        $plan = ['patch' => self::ID, 'version' => 1, 'names' => self::NAMES, 'connection' => $this->connection(),
            'sources' => [M26DetailPackage::MASTER => M26DetailPackage::MASTER_SHA, M26DetailPackage::BEFORE => M26DetailPackage::BEFORE_SHA],
            'pages' => [], 'updates' => [], 'inserts' => []];
        foreach (['app/Support/M26DetailPackage.php', 'app/Support/TheorySection.php',
            'app/Services/M26ContentPatch.php', 'app/Services/M26LocalTargetGuard.php',
            'app/Console/Commands/PatchM26Content.php', 'resources/views/theory/partials/content-block.blade.php',
            'resources/views/theory/partials/section-disclosure.blade.php', 'resources/views/components/theory-section.blade.php',
            'resources/views/engram/theory/widgets/lesson-rule-cards.blade.php',
            'resources/views/engram/theory/blocks-v3/forms-grid.blade.php',
            'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
            'resources/views/engram/theory/blocks-v3/comparison-table.blade.php',
            'resources/views/engram/theory/blocks-v3/summary-list.blade.php'] as $path) {
            $plan['sources'][$path] = hash_file('sha256', $root.'/'.$path);
        }
        $states = []; $targetIds = [];
        foreach ($master['targets'] as $target) {
            $name = $target['identity']; $relative = $target['definition_path'];
            $before = $manifest['definitions'][$name];
            $bytes = file_get_contents($root.'/'.$relative);
            $source = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            if ($source !== M26DetailPackage::definition($target, $before)) {
                throw new RuntimeException('M26 source changes outside exact author extension: '.$name);
            }
            $plan['sources'][$relative] = hash('sha256', $bytes);
            foreach (glob(dirname($root.'/'.$relative).'/localizations/*.json') ?: [] as $localePath) {
                $plan['sources'][substr(str_replace('\\', '/', $localePath), strlen(str_replace('\\', '/', $root)) + 1)] = hash_file('sha256', $localePath);
            }
            $categoryTarget = $target['owner_kind'] === 'category';
            $owners = $this->rows($categoryTarget ? 'page_categories' : 'pages', fn ($q) => $q->where('seeder', $name), $lock);
            if (count($owners) !== 1) { throw new RuntimeException('Missing/ambiguous M26 owner: '.$name); }
            $owner = $owners[0]; $categoryId = $categoryTarget ? $owner['id'] : $owner['page_category_id'];
            $category = $this->categoryChain($categoryId, $lock);
            $config = $before[$target['source_content_root']];
            if (array_column($category, 'slug') !== ['tenses', 'past-perfect-continuous']
                || $owner['slug'] !== $before['slug'] || $owner['title'] !== $config['title']
                || $owner['type'] !== 'theory' || (!$categoryTarget && $owner['text'] !== $config['subtitle_text'])) {
                throw new RuntimeException('M26 owner/category identity differs.');
            }
            $blocks = $this->rows('text_blocks', function ($q) use ($categoryTarget, $owner, $categoryId) {
                if ($categoryTarget) { $q->whereNull('page_id')->where('page_category_id', $categoryId); }
                else { $q->where('page_id', $owner['id']); }
            }, $lock);
            $uk = array_values(array_filter($blocks, fn ($r) => $r['locale'] === 'uk'));
            $oldConfigs = [['type' => 'subtitle', 'column' => 'header', 'body' => $config['subtitle_html'],
                'uuid_key' => $config['subtitle_uuid_key'] ?? 'subtitle', 'level' => $config['subtitle_level'] ?? null], ...$config['blocks']];
            $afterConfigs = $oldConfigs;
            foreach ($source[$target['source_content_root']]['blocks'] as $i => $b) { $afterConfigs[$i + 1] = $b; }
            $new = $categoryTarget ? null : end($afterConfigs);
            $newUuid = $new ? $this->resolveUuid($name, $new, count($oldConfigs)) : null;
            $global = $newUuid ? $this->rows('text_blocks', fn ($q) => $q->where('uuid', $newUuid), $lock) : [];
            if (count($global) > 1 || ($global && ($global[0]['page_id'] != $owner['id'] || $global[0]['seeder'] !== $name))) {
                throw new RuntimeException('M26 practice UUID collision.');
            }
            if (count($uk) !== count($oldConfigs) + (int) (bool) $global) { throw new RuntimeException('M26 unexpected UK block count.'); }
            $rowStates = [];
            foreach ($oldConfigs as $position => $old) {
                $uuid = $this->resolveUuid($name, $old, $position);
                $matches = array_values(array_filter($uk, fn ($r) => $r['uuid'] === $uuid));
                if (count($matches) !== 1) { throw new RuntimeException('M26 missing/ambiguous source UUID.'); }
                $row = $matches[0]; $targetIds[] = $row['id'];
                foreach (['type', 'column', 'level', 'heading', 'css_class'] as $field) {
                    if ($row[$field] !== ($old[$field] ?? null)) { throw new RuntimeException('M26 old block identity differs: '.$field); }
                }
                if ($row['seeder'] !== $name || $row['page_category_id'] != $categoryId
                    || $row['page_id'] !== ($categoryTarget ? null : $owner['id']) || (int) $row['sort_order'] !== $position) {
                    throw new RuntimeException('M26 old block owner/locale/order differs.');
                }
                $state = $this->candidate($plan['updates'], 'text_blocks', $row, $name,
                    ['body' => $old['body']], ['body' => $afterConfigs[$position]['body']]);
                if ($state !== null) { $rowStates[] = $state; }
            }
            $rowStates = array_values(array_unique($rowStates));
            if (count($rowStates) !== 1) { throw new RuntimeException('M26 partial/manual native state.'); }
            $state = $rowStates[0];
            if ($new) {
                if (($state === 'before' && $global) || ($state === 'after' && !$global)) { throw new RuntimeException('M26 partial practice state.'); }
                $insert = $this->insertFields($owner, $new, $name, $newUuid, count($oldConfigs));
                if ($global) { $this->assertInserted($global[0], $insert); $targetIds[] = $global[0]['id']; }
                if ($state === 'before') { $plan['inserts'][] = ['table' => 'text_blocks', 'seeder' => $name, 'fields' => $insert, 'sha256' => self::digest($insert)]; }
            }
            $relationsOwner = ['id' => $categoryTarget ? null : $owner['id'], 'page_category_id' => $categoryId];
            $plan['pages'][$name] = ['page' => $owner, 'category_ancestry' => $category, 'blocks' => $blocks,
                'relations' => $this->relations($relationsOwner, $blocks, $lock)];
            $states[] = $state;
        }
        if (count(array_unique($states)) !== 1) { throw new RuntimeException('M26 partial package; no writes.'); }
        $plan['protected'] = $this->protectedFingerprints($targetIds);
        $plan['state'] = $states[0]; ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }

    private function protectedFingerprints(array $targetIds): array
    {
        $out = [];
        foreach (['pages', 'page_categories', 'text_blocks', 'tags', 'page_tag', 'page_category_tag', 'tag_text_block',
            'questions', 'question_answers', 'question_options', 'question_option_question', 'question_theory_text_blocks',
            'question_tag', 'question_marker_tag', 'verb_hints', 'question_hints', 'question_variants',
            'saved_grammar_tests', 'saved_grammar_test_questions', 'seed_runs'] as $table) {
            $q = $this->db->table($table);
            $columns = $this->db->getSchemaBuilder()->getColumnListing($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $q->orderBy($column); }
            if ($table === 'text_blocks') { $q->whereNotIn('id', $targetIds); }
            $hash = hash_init('sha256'); $count = 0;
            foreach ($q->cursor() as $row) { hash_update($hash, self::digest((array) $row)."\n"); $count++; }
            $out[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
        }
        return $out;
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
                throw new RuntimeException('M26 practice block differs in '.$field.' or belongs to another owner.');
            }
        }
        if (empty($row['id']) || empty($row['created_at']) || $row['created_at'] !== $row['updated_at']) {
            throw new RuntimeException('M26 practice block timestamp/identity was edited.');
        }
    }

    private function candidate(array &$updates, string $table, array $row, string $seeder, array $before, array $after): ?string
    {
        $actual = array_replace($before, array_intersect_key($row, $before));
        if ($actual === $after) { return $before === $after ? null : 'after'; }
        if ($actual !== $before) { throw new RuntimeException('Manual M26 content conflict: '.$seeder.' '.$table.' id='.$row['id']); }
        $updates[] = ['table' => $table, 'id' => $row['id'], 'uuid' => $row['uuid'] ?? null, 'seeder' => $seeder,
            'before' => $before, 'after' => $after, 'before_sha256' => self::digest($before), 'after_sha256' => self::digest($after)];
        return 'before';
    }

    private function categoryChain(int $id, bool $lock): array
    {
        $chain = []; $seen = [];
        while ($id) {
            if (isset($seen[$id]) || count($chain) >= 8) { throw new RuntimeException('Cyclic/too deep M26 category ancestry.'); }
            $seen[$id] = true;
            $rows = $this->rows('page_categories', fn ($q) => $q->where('id', $id), $lock);
            if (count($rows) !== 1 || $rows[0]['language'] !== 'uk' || $rows[0]['type'] !== 'theory') {
                throw new RuntimeException('M26 category identity differs.');
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
                $this->assertProjectedAfter($preview, $current);
                return ['status' => 'no-op', 'updated' => 0, 'inserted' => 0];
            }
            if ($current !== $preview || $preview['state'] !== 'before') {
                throw new RuntimeException('M26 preview is stale/incomplete; no writes.');
            }
            $this->writeNew($backupPath, $preview);
            foreach ($preview['updates'] as $i => $change) {
                $this->update($change, false); $this->afterUpdate($i);
            }
            foreach ($preview['inserts'] as $i => $insert) {
                $this->insert($insert); $this->afterInsert($i);
            }
            $after = $this->plan(true);
            if ($after['state'] !== 'after') { throw new RuntimeException('M26 postcondition failed; transaction rolled back.'); }
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
        if ($backup['state'] !== 'before' || count($backup['inserts']) !== 4) {
            throw new RuntimeException('M26 restore requires the exact before backup.');
        }
        return $this->db->transaction(function () use ($backup): array {
            $current = $this->plan(true);
            if ($current === $backup) { return ['status' => 'no-op', 'updated' => 0, 'deleted' => 0]; }
            if ($current['state'] !== 'after') { throw new RuntimeException('M26 restore refused: edited/partial current state.'); }
            $this->assertProjectedAfter($backup, $current);
            foreach ($backup['inserts'] as $insert) { $this->deleteInserted($current, $insert); }
            foreach ($backup['updates'] as $i => $change) {
                $this->update($change, true); $this->afterUpdate($i);
            }
            if ($this->plan(true) !== $backup) { throw new RuntimeException('M26 restore postcondition failed; transaction rolled back.'); }
            return ['status' => 'restored', 'updated' => count($backup['updates']), 'deleted' => count($backup['inserts'])];
        });
    }

    protected function afterUpdate(int $index): void {}
    protected function afterInsert(int $index): void {}

    private function assertProjectedAfter(array $before, array $after): void
    {
        if ($before['sources'] !== $after['sources'] || $before['connection'] !== $after['connection']
            || $before['protected'] !== $after['protected']) {
            throw new RuntimeException('M26 source/connection changed since preview.');
        }
        $projected = $after['pages'];
        foreach ($before['inserts'] as $insert) {
            $name = $insert['seeder']; $fields = $insert['fields'];
            $matches = array_filter($projected[$name]['blocks'], fn ($row) => $row['uuid'] === $fields['uuid']);
            if (count($matches) !== 1) { throw new RuntimeException('M26 new box missing/ambiguous after apply.'); }
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
            throw new RuntimeException('M26 protected page, block or relation changed since preview.');
        }
    }

    private function assertWriteEnvironment(): void
    {
        if (!app()->environment('local', 'testing') && $this->localTarget === null) {
            throw new RuntimeException('Production-profile M26 writes require verified gramlyze.loc local-target.');
        }
    }

    private function update(array $change, bool $reverse): void
    {
        $fields = match ($change['table'] ?? '') { 'text_blocks' => ['body'],
            default => throw new RuntimeException('Disallowed M26 update table.') };
        $old = $change[$reverse ? 'after' : 'before']; $new = $change[$reverse ? 'before' : 'after'];
        if (array_keys($old) !== $fields || array_keys($new) !== $fields) {
            throw new RuntimeException('Disallowed M26 update fields.');
        }
        $q = $this->db->table($change['table'])->where('id', $change['id'])->where('seeder', $change['seeder']);
        if ($change['table'] === 'text_blocks') { $q->where('uuid', $change['uuid'])->where('locale', 'uk'); }
        foreach ($old as $field => $value) { $q->where($field, $value); }
        if ($q->update($new) !== 1) { throw new RuntimeException('Concurrent M26 update; transaction rolled back.'); }
    }

    private function insert(array $insert): void
    {
        if ($insert['table'] !== 'text_blocks' || $insert['sha256'] !== self::digest($insert['fields'])) {
            throw new RuntimeException('Invalid M26 insert plan.');
        }
        $fields = $insert['fields'];
        if ($this->db->table('text_blocks')->where('uuid', $fields['uuid'])->exists()) {
            throw new RuntimeException('M26 practice UUID collision before insert.');
        }
        $timestamp = now()->toDateTimeString();
        $this->db->table('text_blocks')->insert($fields + ['created_at' => $timestamp, 'updated_at' => $timestamp]);
    }

    private function deleteInserted(array $current, array $insert): void
    {
        $fields = $insert['fields']; $name = $insert['seeder'];
        $matches = array_values(array_filter($current['pages'][$name]['blocks'], fn ($row) => $row['uuid'] === $fields['uuid']));
        if (count($matches) !== 1) { throw new RuntimeException('M26 new box missing during restore.'); }
        $row = $matches[0]; $this->assertInserted($row, $fields);
        $q = $this->db->table('text_blocks')->where('id', $row['id'])->where('uuid', $fields['uuid']);
        foreach ($fields as $field => $value) { $q->where($field, $value); }
        $q->where('created_at', $row['created_at'])->where('updated_at', $row['updated_at']);
        if ($q->delete() !== 1) { throw new RuntimeException('Concurrent M26 new box edit; restore rolled back.'); }
    }

    private function readPlan(string $path): array
    {
        $this->assertPrivatePath($path);
        $plan = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $hash = $plan['sha256'] ?? null; unset($plan['sha256']);
        if (($plan['patch'] ?? null) !== self::ID || ($plan['version'] ?? null) !== 1
            || ($plan['names'] ?? null) !== self::NAMES || !is_string($hash)
            || !hash_equals($hash, self::digest($plan)) || !isset($plan['pages'], $plan['updates'], $plan['inserts'], $plan['sources'])) {
            throw new RuntimeException('Corrupt/incomplete M26 plan or backup.');
        }
        foreach ($plan['updates'] as $change) {
            if (($change['before_sha256'] ?? null) !== self::digest($change['before'] ?? [])
                || ($change['after_sha256'] ?? null) !== self::digest($change['after'] ?? [])) {
                throw new RuntimeException('M26 update value hash mismatch.');
            }
        }
        foreach ($plan['inserts'] as $insert) {
            if (($insert['sha256'] ?? null) !== self::digest($insert['fields'] ?? [])) {
                throw new RuntimeException('M26 insert value hash mismatch.');
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
            throw new RuntimeException('M26 evidence must be a direct private JSON file.');
        }
    }

    private function writeNew(string $path, array $value): void
    {
        $this->assertPrivatePath($path);
        $bytes = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        $file = @fopen($path, 'x');
        if (!$file) { throw new RuntimeException('M26 exclusive evidence already exists; no overwrite.'); }
        @chmod($path, 0600);
        try {
            if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) {
                throw new RuntimeException('Incomplete M26 evidence write; no DB updates allowed.');
            }
        } finally { fclose($file); }
    }
}
