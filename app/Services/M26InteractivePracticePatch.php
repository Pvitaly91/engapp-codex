<?php

namespace App\Services;

use App\Support\M26DetailPackage;
use App\Support\M26InteractivePractice;
use RuntimeException;

/** Explicit upgrade of four existing M26 UK practice rows; no inserts, seeds or bank writes. */
class M26InteractivePracticePatch extends M26ContentPatch
{
    protected const ID = 'm26-ppc-interactive-practice-v1';
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousFormsTheorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousNegativesTheorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousTimeExpressionsTheorySeeder',
    ];

    protected function expectedInsertCount(): int { return 0; }
    protected function allowedUpdateFields(): array { return ['type', 'heading', 'body']; }
    protected function localGuard(): string { return M26InteractiveLocalTargetGuard::class; }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath);
        [$master, $manifest] = M26DetailPackage::load($root);
        $payload = M26InteractivePractice::load($root);
        $targets = array_values(array_filter($master['targets'], fn ($t) => isset($t['practice_insert'])));
        if (array_column($targets, 'identity') !== static::NAMES
            || array_column($payload['targets'], 'identity') !== static::NAMES
            || array_column($master['targets'], 'identity') !== M26ContentPatch::NAMES
            || array_keys($manifest['definitions']) !== M26ContentPatch::NAMES) {
            throw new RuntimeException('M26 interactive exact four-lesson scope differs.');
        }
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => static::NAMES,
            'connection' => $this->connection(), 'sources' => [], 'pages' => [], 'updates' => [], 'inserts' => []];
        foreach ([M26DetailPackage::MASTER, M26DetailPackage::BEFORE, M26InteractivePractice::SOURCE,
            'app/Support/M26DetailPackage.php', 'app/Support/M26InteractivePractice.php',
            'app/Services/M26ContentPatch.php', 'app/Services/M26InteractivePracticePatch.php',
            'app/Services/M26InteractiveLocalTargetGuard.php', 'app/Services/M11LocalTargetGuard.php',
            'app/Console/Commands/PatchM26InteractivePractice.php',
            'resources/views/theory/partials/content-block.blade.php',
            'resources/views/theory/partials/section-disclosure.blade.php',
            'app/Support/TheorySection.php',
            'resources/views/engram/theory/widgets/lesson-rule-cards.blade.php',
            'resources/views/engram/theory/blocks-v3/forms-grid.blade.php',
            'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
            'resources/views/engram/theory/blocks-v3/comparison-table.blade.php',
            'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
            'resources/views/engram/theory/blocks-v3/practice-set.blade.php',
            'resources/views/components/text-block-practice-questions.blade.php',
            'resources/views/components/english-answer-variants.blade.php',
            'app/Services/Theory/TextBlockToQuestionsMatcherService.php'] as $path) {
            if (!is_file($root.'/'.$path)) { throw new RuntimeException('M26 interactive source missing: '.$path); }
            $plan['sources'][$path] = hash_file('sha256', $root.'/'.$path);
        }
        $states = []; $practiceIds = [];
        // Overview is read-only but checked as well: all fourteen detail bodies must already be exact M26.
        foreach ($master['targets'] as $target) {
            $name = $target['identity']; $contentRoot = $target['source_content_root'];
            $original = $manifest['definitions'][$name];
            $legacy = M26DetailPackage::definition($target, $original);
            $after = isset($target['practice_insert'])
                ? M26InteractivePractice::definition($target, $original) : $legacy;
            $path = $target['definition_path']; $bytes = file_get_contents($root.'/'.$path);
            if (json_decode($bytes, true, flags: JSON_THROW_ON_ERROR) !== $after) {
                throw new RuntimeException('M26 interactive source differs from exact approved package: '.$name);
            }
            $plan['sources'][$path] = hash('sha256', $bytes);
            foreach (glob(dirname($root.'/'.$path).'/localizations/*.json') ?: [] as $localePath) {
                $plan['sources'][substr(str_replace('\\', '/', $localePath), strlen(str_replace('\\', '/', $root)) + 1)] = hash_file('sha256', $localePath);
            }
            $categoryTarget = $target['owner_kind'] === 'category';
            $owners = $this->rows($categoryTarget ? 'page_categories' : 'pages', fn ($q) => $q->where('seeder', $name), $lock);
            if (count($owners) !== 1) { throw new RuntimeException('Missing/ambiguous M26 interactive owner: '.$name); }
            $owner = $owners[0]; $categoryId = $categoryTarget ? $owner['id'] : $owner['page_category_id'];
            $category = $this->categoryChain($categoryId, $lock); $config = $legacy[$contentRoot];
            if (array_column($category, 'slug') !== ['tenses', 'past-perfect-continuous']
                || $owner['slug'] !== $legacy['slug'] || $owner['title'] !== $config['title']
                || $owner['type'] !== 'theory' || (!$categoryTarget && $owner['text'] !== $config['subtitle_text'])) {
                throw new RuntimeException('M26 interactive owner/category identity differs.');
            }
            $blocks = $this->rows('text_blocks', function ($q) use ($categoryTarget, $owner, $categoryId): void {
                if ($categoryTarget) { $q->whereNull('page_id')->where('page_category_id', $categoryId); }
                else { $q->where('page_id', $owner['id']); }
            }, $lock);
            $uk = array_values(array_filter($blocks, fn ($r) => $r['locale'] === 'uk'));
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $config['subtitle_html'],
                'uuid_key' => $config['subtitle_uuid_key'] ?? 'subtitle', 'level' => $config['subtitle_level'] ?? null];
            $beforeConfigs = [$subtitle, ...$config['blocks']];
            $afterConfigs = [$subtitle, ...$after[$contentRoot]['blocks']];
            if (count($uk) !== count($beforeConfigs) || count($afterConfigs) !== count($beforeConfigs)) {
                throw new RuntimeException('M26 interactive unexpected UK block count; apply M26 layers first.');
            }
            foreach ($beforeConfigs as $position => $old) {
                $uuid = $this->resolveUuid($name, $old, $position);
                $matches = array_values(array_filter($uk, fn ($r) => $r['uuid'] === $uuid));
                $global = $this->rows('text_blocks', fn ($q) => $q->where('uuid', $uuid), $lock);
                if (count($matches) !== 1 || count($global) !== 1) {
                    throw new RuntimeException('M26 interactive missing/ambiguous block UUID.');
                }
                $row = $matches[0]; $new = $afterConfigs[$position];
                $isPractice = isset($target['practice_insert']) && $position === count($beforeConfigs) - 1;
                if ($row['seeder'] !== $name || $row['page_category_id'] != $categoryId
                    || $row['page_id'] !== ($categoryTarget ? null : $owner['id'])
                    || (int) $row['sort_order'] !== $position) {
                    throw new RuntimeException('M26 interactive block owner/locale/order differs.');
                }
                $fixedFields = $isPractice ? ['column', 'level', 'css_class'] : ['type', 'column', 'level', 'heading', 'css_class', 'body'];
                foreach ($fixedFields as $field) {
                    if ($row[$field] !== ($old[$field] ?? null) || ($new[$field] ?? null) !== ($old[$field] ?? null)) {
                        throw new RuntimeException('M26 interactive protected native block differs: '.$field);
                    }
                }
                if ($isPractice) {
                    if ($this->resolveUuid($name, $new, $position) !== $uuid
                        || empty($row['created_at']) || $row['created_at'] !== $row['updated_at']) {
                        throw new RuntimeException('M26 interactive practice identity/timestamps differ.');
                    }
                    $practiceIds[] = $row['id'];
                    $states[] = $this->candidate($plan['updates'], 'text_blocks', $row, $name,
                        ['type' => $old['type'], 'heading' => $old['heading'] ?? null, 'body' => $old['body']],
                        ['type' => $new['type'], 'heading' => $new['heading'] ?? null, 'body' => $new['body']]);
                }
            }
            $relationsOwner = ['id' => $categoryTarget ? null : $owner['id'], 'page_category_id' => $categoryId];
            $plan['pages'][$name] = ['page' => $owner, 'category_ancestry' => $category, 'blocks' => $blocks,
                'relations' => $this->relations($relationsOwner, $blocks, $lock)];
        }
        if (count($practiceIds) !== 4 || count($states) !== 4 || count(array_unique($states)) !== 1
            || !in_array($states[0], ['before', 'after'], true)) {
            throw new RuntimeException('M26 interactive partial/manual package; no writes.');
        }
        $plan['protected'] = $this->protectedFingerprints($practiceIds);
        $plan['state'] = $states[0]; ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }
}
