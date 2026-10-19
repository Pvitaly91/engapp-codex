<?php

namespace App\Services;

use App\Support\TextBlock\TextBlockUuidGenerator;
use Illuminate\Database\Connection;
use RuntimeException;

/** Narrow M11 patch: existing UK subtitle, hero and box; no seeding or identity changes. */
class LinkingWordsContentPatch
{
    public const NAMES = ['LinkingWordsReasonResultContrastTheorySeeder', 'AdvancedLinkingDevicesTheorySeeder', 'ConcessiveAndContrastiveStructuresTheorySeeder'];
    public const PREFIX = 'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\';
    protected const ID = 'm11-linking-words-v1';
    protected const MANIFEST = 'content-patches/m11-linking-words-before.json';
    protected const LABEL = 'M11';
    protected const LOCAL_GUARD = M11LocalTargetGuard::class;

    public function __construct(protected Connection $db, private string $databasePath, private string $privateDirectory,
        private ?string $localTarget = null, private ?string $localProof = null) {}

    public static function digest(array $value): string
    {
        return PronounContentRepair::digest($value);
    }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        // Keep the established physical PDO/host/database guards, not merely APP_ENV.
        $physical = (new PronounContentRepair($this->db, $this->databasePath, $this->privateDirectory))->connection($expected, $writing);
        if ($this->localTarget !== null) {
            app(static::LOCAL_GUARD)->verify($this->db, $this->localTarget, $this->privateDirectory, $this->localProof, $physical);
        }
        return $physical;
    }

    public function plan(?array $names = null, bool $lock = false): array
    {
        $names ??= static::NAMES;
        if (!$names || count(array_unique($names)) !== count($names) || array_diff($names, static::NAMES)) {
            throw new RuntimeException('Only the three explicit '.static::LABEL.' lesson identities are allowed.');
        }
        $names = array_values(array_intersect(static::NAMES, $names));
        $manifestBytes = file_get_contents($this->databasePath.'/'.static::MANIFEST);
        $manifest = json_decode($manifestBytes, true, flags: JSON_THROW_ON_ERROR);
        if (($manifest['patch'] ?? '') !== static::ID || array_keys($manifest['definitions'] ?? []) !== static::NAMES) {
            throw new RuntimeException('Unexpected historical '.static::LABEL.' source manifest.');
        }
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => $names, 'connection' => $this->connection(),
            'sources' => [static::MANIFEST => hash('sha256', $manifestBytes)], 'pages' => [], 'changes' => []];
        foreach ($names as $name) {
            ['seeder' => $seeder, 'relative' => $relative] = $this->identity($name);
            $bytes = file_get_contents($this->databasePath.'/'.$relative);
            $after = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            $before = $manifest['definitions'][$name];
            $this->assertSources($name, $before, $after);
            $plan['sources'][$relative] = hash('sha256', $bytes);
            foreach (glob(dirname($this->databasePath.'/'.$relative).'/localizations/*.json') ?: [] as $localePath) {
                $plan['sources'][substr(str_replace('\\', '/', $localePath), strlen(str_replace('\\', '/', $this->databasePath)) + 1)] = hash_file('sha256', $localePath);
            }
            $pages = $this->rows('pages', fn ($q) => $q->where('seeder', $seeder), $lock);
            if (count($pages) !== 1) { throw new RuntimeException('Missing or ambiguous Page: '.$name); }
            $page = $pages[0];
            $category = $this->rows('page_categories', fn ($q) => $q->where('id', $page['page_category_id']), $lock);
            if (count($category) !== 1 || $page['slug'] !== $before['slug'] || $page['title'] !== $before['page']['title']
                || $page['type'] !== 'theory' || $category[0]['slug'] !== $before['page']['category']['slug']
                || $category[0]['language'] !== 'uk' || $category[0]['type'] !== 'theory') {
                throw new RuntimeException('Page/category identity differs: '.$name);
            }
            $blocks = $this->rows('text_blocks', fn ($q) => $q->where('page_id', $page['id']), $lock);
            $uk = array_values(array_filter($blocks, fn ($r) => $r['locale'] === 'uk'));
            if (count($uk) !== 3) { throw new RuntimeException('Expected exactly the three established UK blocks: '.$name); }
            $snapshot = ['page' => $page, 'category' => $category[0], 'blocks' => $blocks];
            $snapshot += $this->categorySnapshot($category[0], $name, $lock);
            $snapshot += $this->relations($page, $blocks, $lock);
            $plan['pages'][$seeder] = $snapshot;
            $pageChanges = [];
            $states = [$this->candidate($pageChanges, 'pages', $page, $seeder, ['text' => $before['page']['subtitle_text']], ['text' => $after['page']['subtitle_text']])];
            foreach ($this->sourceBlocks($before) as $index => $expected) {
                $uuid = $index === 0 ? TextBlockUuidGenerator::generateWithKey($seeder.'::uk', 'subtitle') : TextBlockUuidGenerator::generate($seeder.'::uk', $index);
                $matches = array_values(array_filter($uk, fn ($r) => $r['uuid'] === $uuid));
                if (count($matches) !== 1) { throw new RuntimeException('Missing/ambiguous expected UUID: '.$uuid); }
                $row = $matches[0];
                foreach (['type', 'column', 'css_class', 'level'] as $field) {
                    if ($row[$field] !== ($expected[$field] ?? null)) { throw new RuntimeException('Block identity '.$field.' differs: '.$uuid); }
                }
                if ($row['seeder'] !== $seeder || (int) $row['sort_order'] !== $index || $row['page_category_id'] != $page['page_category_id']) {
                    throw new RuntimeException('Block owner/order/category differs: '.$uuid);
                }
                $new = $this->sourceBlocks($after)[$index];
                $states[] = $this->candidate($pageChanges, 'text_blocks', $row, $seeder,
                    ['heading' => $expected['heading'] ?? null, 'body' => $expected['body']],
                    ['heading' => $new['heading'] ?? null, 'body' => $new['body']]);
            }
            $states = array_unique(array_filter($states));
            if (count($states) > 1) { throw new RuntimeException('Partially edited lesson; preserve it and inspect separately: '.$name); }
            array_push($plan['changes'], ...$pageChanges);
        }
        ksort($plan['sources']);
        $plan['sha256'] = self::digest($plan);
        return $plan;
    }

    private function assertSources(string $name, array $before, array $after): void
    {
        if (($after['seeder']['class'] ?? '') !== $this->identity($name)['seeder'] || ($after['page']['locale'] ?? '') !== 'uk'
            || ($after['type'] ?? '') !== 'theory' || count($after['page']['blocks'] ?? []) !== 2) {
            throw new RuntimeException('Unexpected '.static::LABEL.' source identity/locale/block count: '.$name);
        }
        $immutable = function (array $source): array {
            unset($source['page']['subtitle_html'], $source['page']['subtitle_text']);
            foreach ($source['page']['blocks'] as &$block) { unset($block['heading'], $block['body']); }
            unset($block);
            return $source;
        };
        if ($immutable($before) !== $immutable($after)) { throw new RuntimeException(static::LABEL.' may change only subtitle, heading and body: '.$name); }
        foreach ([$before, $after] as $source) {
            $hero = json_decode($source['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
            if (empty($hero['intro']) || ($hero['level'] ?? '') !== $source['page']['blocks'][0]['level']) {
                throw new RuntimeException('Invalid hero JSON/level: '.$name);
            }
        }
    }

    private function sourceBlocks(array $source): array
    {
        return [['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'body' => $source['page']['subtitle_html']], ...$source['page']['blocks']];
    }

    private function candidate(array &$changes, string $table, array $row, string $seeder, array $before, array $after): ?string
    {
        $actual = array_intersect_key($row, $before);
        // SQL column order is irrelevant; values must be byte-exact (no trimming/normalizing).
        $actual = array_replace($before, $actual);
        if ($actual === $after) { return $before === $after ? null : 'after'; }
        if ($actual !== $before) { throw new RuntimeException('Manual/source mismatch preserved: '.$seeder.' '.$table.' id='.$row['id']); }
        $changes[] = ['table' => $table, 'id' => $row['id'], 'uuid' => $row['uuid'] ?? null, 'seeder' => $seeder,
            'before' => $before, 'after' => $after, 'before_sha256' => self::digest($before), 'after_sha256' => self::digest($after)];
        return 'before';
    }

    protected function identity(string $name): array
    {
        return ['seeder' => self::PREFIX.$name, 'relative' => 'seeders/Page_V3/ClausesAndLinkingWords/'.$name.'/definition.json'];
    }

    /** M11 deliberately retains its original snapshot shape for existing backups. */
    protected function categorySnapshot(array $category, string $name, bool $lock): array { return []; }

    protected function rows(string $table, callable $filter, bool $lock): array
    {
        $query = $this->db->table($table);
        $filter($query);
        $rows = ($lock ? $query->lockForUpdate() : $query)->get()->map(fn ($r) => (array) $r)->all();
        usort($rows, fn ($a, $b) => strcmp(self::digest($a), self::digest($b)));
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
        $questionUuids = array_column($out['question_theory_text_blocks'], 'question_uuid');
        $out['questions'] = $this->rows('questions', fn ($q) => $q->whereIn('uuid', $questionUuids)->orWhereIn('theory_text_block_uuid', $uuids), $lock);
        $questionIds = array_column($out['questions'], 'id');
        foreach (['question_answers', 'question_option_question', 'question_tag', 'question_marker_tag', 'verb_hints', 'question_hints', 'question_variants'] as $table) {
            $out[$table] = $this->rows($table, fn ($q) => $q->whereIn('question_id', $questionIds), $lock);
        }
        $optionIds = array_unique(array_merge(array_column($out['question_answers'], 'option_id'), array_column($out['question_option_question'], 'option_id'), array_column($out['verb_hints'], 'option_id')));
        $out['question_options'] = $this->rows('question_options', fn ($q) => $q->whereIn('id', $optionIds), $lock);
        $out['saved_grammar_test_questions'] = $this->rows('saved_grammar_test_questions', fn ($q) => $q->whereIn('question_uuid', array_column($out['questions'], 'uuid')), $lock);
        return $out;
    }

    public function savePlan(string $path, ?array $names = null): array
    {
        $plan = $this->plan($names);
        $this->writeNew($path, $plan);
        return $plan;
    }

    public function apply(string $planPath, string $backupPath, ?string $expectedDatabase = null): array
    {
        $this->assertWriteEnvironment();
        $this->connection($expectedDatabase, true);
        $preview = $this->readPlan($planPath);
        return $this->db->transaction(function () use ($preview, $backupPath): array {
            $current = $this->plan($preview['names'], true);
            if ($this->project($preview) === $this->project($current) && !$current['changes']) { return ['status' => 'no-op', 'updated' => 0]; }
            if ($preview !== $current) { throw new RuntimeException('Preview stale/incomplete; source, rows, locales or relations changed. No writes.'); }
            $this->writeNew($backupPath, $preview);
            foreach ($preview['changes'] as $i => $change) { $this->update($change, false); $this->afterUpdate($i); }
            if ($this->project($this->plan($preview['names'], true)) !== $this->project($preview)) {
                throw new RuntimeException('Postcondition failed; entire patch rolled back.');
            }
            return ['status' => 'applied', 'updated' => count($preview['changes']), 'backup' => $backupPath];
        });
    }

    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        $this->assertWriteEnvironment();
        $this->connection($expectedDatabase, true);
        $backup = $this->readPlan($backupPath);
        return $this->db->transaction(function () use ($backup): array {
            $current = $this->plan($backup['names'], true);
            if ($current === $backup) { return ['status' => 'no-op', 'updated' => 0]; }
            if ($this->project($backup) !== $this->project($current) || $current['changes']) { throw new RuntimeException('Stale/incomplete backup or subsequent edits; restore refused.'); }
            foreach ($backup['changes'] as $i => $change) { $this->update($change, true); $this->afterUpdate($i); }
            if ($this->plan($backup['names'], true) !== $backup) { throw new RuntimeException('Invalid backup; entire restore rolled back.'); }
            return ['status' => 'restored', 'updated' => count($backup['changes'])];
        });
    }

    protected function afterUpdate(int $index): void {}

    private function assertWriteEnvironment(): void
    {
        if (!app()->environment('local', 'testing') && $this->localTarget === null) {
            throw new RuntimeException('Local/testing environment only; production writes are refused without verified '.static::LABEL.' local-target.');
        }
    }

    private function update(array $change, bool $reverse): void
    {
        $fields = match ($change['table'] ?? '') { 'pages' => ['text'], 'text_blocks' => ['heading', 'body'], default => throw new RuntimeException('Disallowed table.') };
        $old = $change[$reverse ? 'after' : 'before']; $new = $change[$reverse ? 'before' : 'after'];
        if (array_keys($old) !== $fields || array_keys($new) !== $fields) { throw new RuntimeException('Disallowed update fields.'); }
        $q = $this->db->table($change['table'])->where('id', $change['id'])->where('seeder', $change['seeder']);
        if ($change['table'] === 'text_blocks') { $q->where('uuid', $change['uuid'])->where('locale', 'uk'); }
        foreach ($old as $field => $value) { $q->where($field, $value); }
        if ($q->update($new) !== 1) { throw new RuntimeException('Concurrent update; entire patch rolled back.'); }
    }

    private function project(array $plan): array
    {
        foreach ($plan['changes'] as $change) {
            if ($change['table'] === 'pages') {
                $plan['pages'][$change['seeder']]['page'] = array_replace($plan['pages'][$change['seeder']]['page'], $change['after']);
            } else {
                foreach ($plan['pages'][$change['seeder']]['blocks'] as &$row) {
                    if ($row['id'] === $change['id']) { $row = array_replace($row, $change['after']); }
                }
                unset($row);
            }
        }
        // Snapshot row sorting must not depend on content, which the patch changes.
        foreach ($plan['pages'] as &$page) { usort($page['blocks'], fn ($a, $b) => $a['id'] <=> $b['id']); }
        unset($page);
        $plan['changes'] = []; unset($plan['sha256']);
        return $plan;
    }

    private function readPlan(string $path): array
    {
        $this->assertPrivatePath($path);
        $plan = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $hash = $plan['sha256'] ?? null; unset($plan['sha256']);
        if (($plan['patch'] ?? '') !== static::ID || ($plan['version'] ?? 0) !== 1 || !is_string($hash)
            || !hash_equals($hash, self::digest($plan)) || !isset($plan['names'], $plan['pages'], $plan['changes'], $plan['sources'])) {
            throw new RuntimeException('Corrupt/incomplete '.static::LABEL.' plan or backup.');
        }
        foreach ($plan['changes'] as $change) {
            if (($change['before_sha256'] ?? '') !== self::digest($change['before'] ?? []) || ($change['after_sha256'] ?? '') !== self::digest($change['after'] ?? [])) {
                throw new RuntimeException('Value hash mismatch.');
            }
        }
        $plan['sha256'] = $hash;
        return $plan;
    }

    private function assertPrivatePath(string $path): void
    {
        $root = realpath($this->privateDirectory);
        if (!$root || !stream_is_local($path) || realpath(dirname($path)) !== $root || is_link($path)
            || !preg_match('/^[a-zA-Z0-9_-]+\.json$/', basename($path))) { throw new RuntimeException('Plan/backup must be directly inside the private '.static::LABEL.' directory.'); }
    }

    private function writeNew(string $path, array $data): void
    {
        $this->assertPrivatePath($path);
        $bytes = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        $file = @fopen($path, 'x');
        if (!$file) { throw new RuntimeException('Cannot create exclusive plan/backup; existing evidence is never overwritten.'); }
        @chmod($path, 0600);
        try {
            if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) { throw new RuntimeException('Incomplete private backup; no DB updates allowed.'); }
        } finally { fclose($file); }
    }
}
