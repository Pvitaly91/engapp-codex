<?php

namespace App\Support;

use App\Models\Page;
use App\Models\TextBlock;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/** One reviewed owner. A literal legacy box is not a native-type migration. */
final class M94RelativeClausesWhoseWhich
{
    public const BASE_SHA = '8fbc16ab6dd6c6636b93f4f09406addaf77d3cb2';
    public const VERSION = '1.0.0';
    public const BEFORE = 'docs/content/m94-relative-clauses-whose-which.before.v1.json';
    public const SOURCE = 'database/content-patches/m94-relative-clauses-whose-which.v1.json';
    public const MASTER = 'docs/content/m94-relative-clauses-whose-which.v1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m94-relative-clauses-whose-which.v1.json';
    public const CHECKSUMS = 'docs/content/m94-relative-clauses-whose-which.checksums.v1.json';
    public const BINDINGS = 'docs/content/m94-relative-clauses-whose-which.bindings.v1.json';
    public const NAVIGATION = 'docs/content/m94-relative-clauses-whose-which.navigation.v1.json';
    // Exact frozen artifacts; modified or incomplete packages fail closed.
    public const BEFORE_SHA = '5861cee2458772c9c405747f02efc1811c4b5fe3d7467d777c5f7b0b69c2039a';
    public const SOURCE_SHA = '75bc91658f333d6386a1cec5434168930983506f89bda2e641a179f4fc7efd37';
    public const MASTER_SHA = '39d88cc16eef08bacb58103edff346e094cd39de27997d8b87a875501b465ade';
    public const CONTRACT_SHA = 'bca6a55a65acf293249e4f2355e4b9fa55456374b57a04a647ea5101f6567be2';
    public const CHECKSUMS_SHA = '7fa04d1e582282cf8dab478767311a1c7d6a9a6e534f33a49322f29375386127';
    public const BINDINGS_SHA = 'b45d2f7cdb22c53adce88b3b05cef2f7d5524f5c0856da1e5afa5f0d744bdf5b';
    public const NAVIGATION_SHA = '4fe142269b1934a1873f85da15c4bffb9c0274c284c674ed3b4c02dd55e0792f';
    public const OWNERS = ['Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\RelativeClausesWithWhoseAndWhichTheorySeeder'];
    public const PAGE_IDS = [284];
    public const CATEGORY_IDS = [34];
    public const BANKS = ['Database\\Seeders\\V3\\Polyglot\\PolyglotRelativeClausesWithWhoseAndWhichB2LessonSeeder'];
    public const SLUGS = ['relative-clauses-with-whose-and-which'];
    public const ANCESTRIES = [['clauses-and-linking-words']];
    public const LEVELS = ['B2'];
    public const FOCUS_LEVELS = ['B2'];
    public const DISPLAY_LEVELS = ['B2'];
    public const CONTINUING_COUNTS = [2];
    public const CONTINUING_LEVELS = [['B2', null]];
    public const CONTINUING_IDS = [8740, 8741];
    public const CONTINUING_UUIDS = ['9cf0dba9-355d-5b1b-a56b-36deeb988f23', '2d66a1e4-cb61-5b4d-bfd8-fdb58b9d2887'];
    public const SUBTITLE_ID = 8739;
    public const SUBTITLE_UUID = 'b0104b91-d163-5c7f-a213-07ce501f7b00';
    public const RENDERED_TITLES = ['Relative Clauses with Whose and Which'];
    public const PRACTICE_SOURCE_INDICES = [7];
    public const NAVIGATION_SOURCE_INDICES = [6];
    public const PRACTICE_KEYS = ['m94-practice'];
    public const SENTENCE_INPUT_INDICES = [[0, 1]];
    public const BLOCK_KEYS = ['m94-hero', 'm94-overview', 'm94-forms', 'm94-relations-and-reference', 'm94-mistakes', 'm94-summary', 'm94-navigation', 'm94-practice'];
    public const BLOCK_TYPES = ['hero', 'box', 'forms-grid', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips', 'practice-set'];
    public const BLOCK_COLUMNS = ['header', 'left', 'left', 'left', 'right', 'right', 'footer', 'left'];
    public const BLOCK_LEVELS = ['B2', null, 'B2', 'B2', 'B2', 'B2', null, 'B2'];
    public const BOX_HEADING = '1. Належність чи уточнення?';
    public const TASK_IDS = ['m94-form-whose-gallery', 'm94-form-which-subject', 'm94-meaning-defining-context', 'm94-meaning-whole-situation', 'm94-full-whose-studio', 'm94-full-which-bus'];
    public const MANUAL_ONLY_ALIAS = 'We missed the last bus, which made us arrive late.';
    public const SUBTITLE_TEXT_AFTER = 'Пояснюй належність і зв’язки через whose, уточнюй предмет або попередню ситуацію через which та розрізняй визначальну й додаткову інформацію.';

    private static function fail(string $message): never { throw new RuntimeException('M94 '.$message); }
    private static function json(array $value): string { return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n"; }
    private static function readFrozen(string $path, string $hash, ?string $root = null): array
    {
        $file = ($root ?? dirname(__DIR__, 2)).'/'.$path;
        if (preg_match('/^[a-f0-9]{64}$/D', $hash) !== 1 || !is_file($file) || is_link($file)
            || !hash_equals($hash, hash_file('sha256', $file))) { self::fail('artifact is pending or changed: '.$path); }
        return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function draftProfile(array $bindings): array
    {
        self::assertBindings($bindings);
        return ['label' => 'M94', 'base_sha' => self::BASE_SHA, 'version' => self::VERSION,
            'marker' => 'm94_native_v1', 'detail_marker' => 'm94_detail_key', 'uuid_prefix' => 'm94:',
            'design' => 'm94-native-v1', 'bindings' => $bindings];
    }

    public static function assertBindings(array $bindings): void
    {
        $b = $bindings[0] ?? [];
        $appended = [];
        foreach (range(2, 7) as $slot) {
            $appended[] = ['key' => self::BLOCK_KEYS[$slot], 'type' => self::BLOCK_TYPES[$slot],
                'column' => self::BLOCK_COLUMNS[$slot], 'level' => self::BLOCK_LEVELS[$slot]];
        }
        if (!array_is_list($bindings) || count($bindings) !== 1 || ($b['identity'] ?? null) !== self::OWNERS[0]
            || ($b['slug'] ?? null) !== self::SLUGS[0] || ($b['page_id'] ?? null) !== 284 || ($b['category_id'] ?? null) !== 34
            || ($b['ancestry'] ?? null) !== self::ANCESTRIES[0]
            || ($b['local_url'] ?? null) !== 'http://gramlyze.loc/theory/clauses-and-linking-words/'.self::SLUGS[0]
            || ($b['practice_level'] ?? null) !== 'B2' || ($b['display_level'] ?? null) !== 'B2'
            || ($b['rendered_title'] ?? null) !== self::RENDERED_TITLES[0]
            || ($b['continuing_levels'] ?? null) !== ['B2', null]
            || ($b['appended_blocks'] ?? null) !== $appended
            || ($b['practice_contract'] ?? null) !== ['counts' => ['selects' => 2, 'choices' => 2, 'inputs' => 2], 'sentence_input_indices' => [0, 1]]
            || array_key_exists('existing_practice', $b) || array_key_exists('legacy_box_projection', $b)
            || array_key_exists('component_overrides', $b)) { self::fail('one-owner binding or explicit legacy/native boundary differs.'); }
    }

    public static function profile(): array
    {
        $bindings = self::readFrozen(self::BINDINGS, self::BINDINGS_SHA);
        if (($bindings['base_sha'] ?? null) !== self::BASE_SHA) { self::fail('binding base differs.'); }
        return self::draftProfile($bindings['bindings'] ?? []) + ['artifacts' => [
            self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA, self::MASTER => self::MASTER_SHA,
            self::CONTRACT => self::CONTRACT_SHA, self::CHECKSUMS => self::CHECKSUMS_SHA,
            self::BINDINGS => self::BINDINGS_SHA, self::NAVIGATION => self::NAVIGATION_SHA]];
    }

    public static function load(?string $root = null): array
    {
        // Keep the established seven-artifact return order. No native release is
        // asked to reinterpret this package's literal HTML legacy body.
        $paths = [self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA, self::MASTER => self::MASTER_SHA,
            self::CONTRACT => self::CONTRACT_SHA, self::CHECKSUMS => self::CHECKSUMS_SHA,
            self::BINDINGS => self::BINDINGS_SHA, self::NAVIGATION => self::NAVIGATION_SHA];
        $out = [];
        foreach ($paths as $path => $hash) { $out[] = self::readFrozen($path, $hash, $root); }
        self::assertBindings($out[5]['bindings'] ?? []);
        return $out;
    }

    /** Reuse the native serializer, then make only the explicit box1 override. */
    public static function definition(array $lesson, array $before): array
    {
        if (($before['seeder']['class'] ?? null) !== self::OWNERS[0]
            || count($before['page']['blocks'] ?? []) !== 2
            || ($before['page']['blocks'][0]['type'] ?? null) !== 'hero'
            || ($before['page']['blocks'][1]['type'] ?? null) !== 'box') { self::fail('definition has a foreign or migrated legacy source.'); }
        $after = M49AdviceModalsPackage::definition($lesson, $before);
        $rename = function (array $value) use (&$rename): array {
            $out = [];
            foreach ($value as $key => $child) {
                $key = match ($key) { 'm49_native_v1' => 'm94_native_v1', 'm49_detail_key' => 'm94_detail_key', default => $key };
                $out[$key] = is_array($child) ? $rename($child) : $child;
            }
            return $out;
        };
        foreach ($after['page']['blocks'] as $slot => &$block) {
            if ($slot === 1) {
                // Never json_decode the continuing literal HTML body.
                $block['body'] = $lesson['blocks'][1]['data']['html'] ?? '';
                $block['heading'] = self::BOX_HEADING;
            } else {
                $block['body'] = M26DetailPackage::json($rename(json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR)));
            }
            if (isset($block['uuid_key']) && str_starts_with($block['uuid_key'], 'm49:')) {
                $block['uuid_key'] = 'm94:'.substr($block['uuid_key'], 4);
            }
        }
        unset($block);
        return $after;
    }

    public static function validate(array $before, array $patch, array $master, ?array $contract = null): void
    {
        foreach ([[$before, self::BEFORE_SHA], [$patch, self::SOURCE_SHA], [$master, self::MASTER_SHA]] as [$value, $hash]) {
            if (preg_match('/^[a-f0-9]{64}$/D', $hash) !== 1 || !hash_equals($hash, hash('sha256', self::json($value)))) {
                self::fail('frozen document is pending or differs.');
            }
        }
        $contract ??= self::readFrozen(self::CONTRACT, self::CONTRACT_SHA);
        if (preg_match('/^[a-f0-9]{64}$/D', self::CONTRACT_SHA) !== 1
            || !hash_equals(self::CONTRACT_SHA, hash('sha256', self::json($contract)))) { self::fail('frozen contract differs.'); }
        self::assertEditorial($before, $patch, $master, self::readFrozen(self::NAVIGATION, self::NAVIGATION_SHA), $contract);
    }

    /** Pure pre-freeze review entry: all authorities must be supplied explicitly. */
    public static function assertEditorial(array $before, array $patch, array $master, array $navigation, array $contract): void
    {
        $definitionPath = 'database/seeders/Page_V3/ClausesAndLinkingWords/RelativeClausesWithWhoseAndWhichTheorySeeder/definition.json';
        $url = 'http://gramlyze.loc/theory/clauses-and-linking-words/'.self::SLUGS[0];
        if (($before['content_type'] ?? null) !== 'M94_source_before'
            || ($patch['content_type'] ?? null) !== 'M94_Relative_Clauses_Whose_Which'
            || array_column($before['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($patch['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($master['lessons'] ?? [], 'identity') !== self::OWNERS
            || array_column($navigation['targets'] ?? [], 'identity') !== self::OWNERS
            || ($before['base_sha'] ?? null) !== self::BASE_SHA || ($patch['base_sha'] ?? null) !== self::BASE_SHA
            || ($master['base_sha'] ?? null) !== self::BASE_SHA || ($master['version'] ?? null) !== self::VERSION) {
            self::fail('finite source/master/navigation scope differs.');
        }
        $oldTarget = $before['targets'][0]; $target = $patch['targets'][0]; $lesson = $master['lessons'][0];
        $old = $oldTarget['before'] ?? []; $blocks = $lesson['blocks'] ?? [];
        if (($oldTarget['path'] ?? null) !== $definitionPath || ($target['path'] ?? null) !== $definitionPath
            || ($target['page_binding'] ?? null) !== ['id' => 284, 'page_category_id' => 34]
            || ($target['ancestry'] ?? null) !== self::ANCESTRIES[0] || ($target['local_url'] ?? null) !== $url
            || ($old['seeder']['class'] ?? null) !== self::OWNERS[0] || ($old['type'] ?? null) !== 'theory'
            || ($old['slug'] ?? null) !== self::SLUGS[0] || ($old['page']['locale'] ?? null) !== 'uk'
            || ($old['page']['title'] ?? null) !== self::RENDERED_TITLES[0]
            || ($old['page']['category']['slug'] ?? null) !== 'clauses-and-linking-words'
            || count($old['page']['blocks'] ?? []) !== 2 || count($blocks) !== 8
            || ($lesson['slug'] ?? null) !== self::SLUGS[0] || ($lesson['locale'] ?? null) !== 'uk'
            || ($lesson['raw_title'] ?? null) !== self::RENDERED_TITLES[0]
            || ($lesson['preserved_h1'] ?? null) !== self::RENDERED_TITLES[0]
            || ($lesson['display_level'] ?? null) !== 'B2' || ($lesson['technical_block_level'] ?? null) !== 'B2'
            || ($lesson['intended_own_practice_level'] ?? null) !== 'B2'
            || ($lesson['subtitle_text'] ?? null) !== self::SUBTITLE_TEXT_AFTER
            || preg_match('~^<p><strong>Relative Clauses with Whose and Which</strong>~u', $lesson['subtitle_html'] ?? '') !== 1
            || empty($lesson['point_detail_decisions'])) { self::fail('captured identity/metadata or one-owner lesson differs.'); }
        if (($target['block_keys'] ?? null) !== self::BLOCK_KEYS || array_column($blocks, 'key') !== self::BLOCK_KEYS
            || array_column($blocks, 'type') !== self::BLOCK_TYPES || array_column($blocks, 'column') !== self::BLOCK_COLUMNS
            || array_column($blocks, 'level') !== self::BLOCK_LEVELS
            || array_column($blocks, 'existing_source_index') !== [0, 1, null, null, null, null, null, null]
            || ($target['after'] ?? null) !== self::definition($lesson, $old)) { self::fail('literal eight-slot projection differs.'); }
        $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
        foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
        if ($protected !== $old) { self::fail('protected raw title/slug/tags/category or source shape changed.'); }
        foreach ($blocks as $slot => $block) {
            self::assertLearningData($block['data'] ?? []);
            if ($slot < 2) {
                $oldConfig = $old['page']['blocks'][$slot]; $newConfig = $target['after']['page']['blocks'][$slot];
                if (($oldConfig['type'] ?? null) !== self::BLOCK_TYPES[$slot]
                    || ($oldConfig['column'] ?? null) !== self::BLOCK_COLUMNS[$slot]
                    || ($oldConfig['level'] ?? null) !== self::BLOCK_LEVELS[$slot]
                    || M26DetailPackage::uuid(self::OWNERS[0], $oldConfig, $slot + 1) !== self::CONTINUING_UUIDS[$slot]
                    || M26DetailPackage::uuid(self::OWNERS[0], $newConfig, $slot + 1) !== self::CONTINUING_UUIDS[$slot]) {
                    self::fail('continuing type/column/technical level or UUID changed.');
                }
                unset($oldConfig['body'], $newConfig['body']);
                if ($slot === 1) {
                    if (($oldConfig['heading'] ?? null) !== 'B2 focus' || ($newConfig['heading'] ?? null) !== self::BOX_HEADING) {
                        self::fail('only the reviewed box heading transition is allowed.');
                    }
                    $newConfig['heading'] = $oldConfig['heading'];
                }
                if ($oldConfig !== $newConfig) { self::fail('continuing metadata/key absence changed.'); }
            }
        }
        $html = $blocks[1]['data']['html'] ?? null;
        if (array_keys($blocks[1]['data']) !== ['html'] || !is_string($html)
            || preg_match('~^(?:<p>(?:[^<]|<strong>[^<]*</strong>)+</p>)+$~uD', $html) !== 1
            || !self::ukrainian($html) || str_contains($html, 'theory anchor') || str_contains($html, '<h2')) {
            self::fail('continuing box requires reviewed p/strong HTML without an inner heading/card.');
        }
        $transition = $master['editorial_review']['legacy_box_transition'] ?? [];
        foreach (['source_index' => 1, 'stored_type' => 'box', 'before_heading' => 'B2 focus', 'after_heading' => self::BOX_HEADING,
            'before_body_sha256' => hash('sha256', $old['page']['blocks'][1]['body']), 'after_body_sha256' => hash('sha256', $html),
            'body_format' => 'literal-supported-HTML'] as $key => $value) {
            if (($transition[$key] ?? null) !== $value) { self::fail('explicit legacy heading/body transition is missing.'); }
        }
        if (!self::ukrainian($transition['reason'] ?? null)) { self::fail('legacy filler replacement needs an explicit reason.'); }
        $heroBefore = json_decode($old['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
        if (($blocks[0]['data']['level'] ?? null) !== 'B2' || count($blocks[0]['data']['rules'] ?? []) !== 3
            || !self::ukrainian($blocks[0]['data']['intro'] ?? null)) { self::fail('hero level/rules or visible Ukrainian intro differs.'); }
        foreach ($heroBefore['rules'] as $n => $rule) {
            $example = $blocks[0]['data']['rules'][$n]['example'] ?? '';
            if (!str_starts_with($example, $rule['example'].' — ') || !self::ukrainian($example)) {
                self::fail('retained English source example or full translation was lost.');
            }
        }
        if (count($blocks[2]['data']['items'] ?? []) !== 3 || count($blocks[3]['data']['sections'] ?? []) !== 4
            || count($blocks[4]['data']['items'] ?? []) !== 4 || count($blocks[5]['data']['items'] ?? []) !== 5) {
            self::fail('reviewed forms/usage/mistakes/summary vector differs.');
        }
        foreach ($blocks[3]['data']['sections'] as $section) {
            self::assertSection($section);
        }
        foreach ($blocks[4]['data']['items'] as $item) {
            foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) {
                if (!self::plain($item[$field] ?? null)) { self::fail('paired mistake field or note missing.'); }
            }
            if (!self::ukrainian($item['wrong_ua']) || !self::ukrainian($item['right_ua']) || !self::ukrainian($item['note'])) {
                self::fail('paired mistake translation/visible explanation missing.');
            }
        }
        $details = $lesson['details'] ?? [];
        $detail = $details[0] ?? [];
        if (count($details) !== 1 || ($detail['key'] ?? null) !== 'm94-whose-of-which'
            || ($detail['owner_block_key'] ?? null) !== self::BLOCK_KEYS[3] || ($detail['owner_pointer'] ?? null) !== '/sections/0'
            || !self::ukrainian($detail['title'] ?? null) || ($detail['fragment']['type'] ?? null) !== 'usage-panels'
            || count($detail['fragment']['data']['sections'] ?? []) !== 1) { self::fail('one semantic of-which detail ownership differs.'); }
        self::assertLearningData($details); $section = $detail['fragment']['data']['sections'][0];
        self::assertSection($section);
        if (!str_contains($section['description'], 'of which') || !str_contains($section['description'], 'whose')
            || array_column($section['examples'], 'en') !== [
                'The old gate, whose lock was broken, was replaced.',
                'The old gate, the lock of which was broken, was replaced.']) { self::fail('detail lost its whole-group restructuring and complete models.'); }
        $data = $blocks[7]['data'];
        if (($data['linked_practice']['source'] ?? null) !== 'theory_links'
            || ($data['linked_practice']['question_types'] ?? null) !== ['4']
            || ($data['linked_practice']['seeder_classes'] ?? null) !== self::BANKS
            || count($data['selects'] ?? []) !== 2 || count($data['choices'] ?? []) !== 2 || count($data['inputs'] ?? []) !== 2
            || count($data['rephrase'] ?? []) !== 0 || count(array_filter($blocks, static fn ($b) => $b['type'] === 'practice-set')) !== 1) {
            self::fail('six own controls and one exact thematic type4 widget are required.');
        }
        $ledger = $master['editorial_review']['task_map'][self::OWNERS[0]] ?? []; $n = 0; $aliases = 0;
        foreach (['selects', 'choices', 'inputs'] as $group) {
            foreach ($data[$group] as $index => $task) {
                if (($task['id'] ?? null) !== self::TASK_IDS[$n] || ($task['source_index'] ?? null) !== $n) { self::fail('new task identity/order differs.'); }
                $row = $ledger[$n] ?? [];
                foreach (['old_task', 'old_group', 'old_group_index', 'old_block_source_index'] as $field) {
                    if (!array_key_exists($field, $row) || $row[$field] !== null) { self::fail('new task invents an OLD identity.'); }
                }
                if (($row['after_task'] ?? null) !== $task || ($row['after_group'] ?? null) !== $group
                    || ($row['after_group_index'] ?? null) !== $index || ($row['after_block_source_index'] ?? null) !== 7
                    || ($row['graded'] ?? null) !== true || ($row['classification'] ?? null) !== 'new'
                    || ($row['assessed_role'] ?? null) !== match ($group) { 'selects' => 'grammar_form', 'choices' => 'meaning', default => 'full_sentence' }
                    || !self::ukrainian($row['change_reason'] ?? null)) { self::fail('exact new-task ledger/meaning role is stale or incomplete.'); }
                self::assertTask($task, $group); $aliases += count($task['accepted']);
                if ($group === 'inputs') { self::assertSentence($task, $index); }
                $n++;
            }
        }
        if ($n !== 6 || count($ledger) !== 6 || $aliases !== 7) { self::fail('six controls/seven declared keys differs.'); }
        $navItems = $blocks[6]['data']['items'] ?? [];
        if ($navItems === [] || ($navigation['targets'][0]['items'] ?? null) !== $navItems) { self::fail('independent navigation projection differs.'); }
        foreach ($navItems as $item) {
            if (!is_string($item['label'] ?? null) || trim($item['label']) === ''
                || (empty($item['current']) && preg_match('~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)*$~D', $item['url'] ?? '') !== 1)) {
                self::fail('navigation needs reviewed labels and explicit local destinations.');
            }
        }
        if (($contract['targets'] ?? null) !== [['identity' => self::OWNERS[0], 'definition' => $definitionPath,
            'local_url' => $url, 'level' => 'B2', 'bank_seeders' => self::BANKS]]) { self::fail('independent owner/bank/level contract differs.'); }
    }

    private static function assertSection(array $section): void
    {
        if (!self::ukrainian($section['label'] ?? null) || !self::ukrainian($section['description'] ?? null)
            || !is_array($section['examples'] ?? null) || $section['examples'] === []) { self::fail('explained native point is incomplete.'); }
        foreach ($section['examples'] as $example) {
            if (!self::plain($example['en'] ?? null) || !self::ukrainian($example['ua'] ?? null)) { self::fail('complete bilingual example is missing.'); }
        }
    }
    private static function assertLearningData(array $data): void
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), ['view', 'native_view', 'stylesheet', 'css_class', 'class', 'style'], true)) {
                self::fail('learning data cannot select presentation.');
            }
            if (is_array($value)) { self::assertLearningData($value); }
            elseif (is_string($value) && preg_match('~<[^>]*\s(?:class|style|on[a-z]+)\s*=|<(?:script|style|iframe)\b~i', $value)) {
                self::fail('learning data contains styled/executable markup.');
            }
        }
    }
    private static function assertTask(array $task, string $group): void
    {
        $visible = $group === 'inputs' ? ($task['after'] ?? null) : ($task['label'] ?? null);
        if (!self::ukrainian($visible) || preg_match('/\{a[0-9]+\}/u', $visible) === 1
            || isset($task['strict']) || isset($task['punctuation_sensitive'])
            || !is_array($task['accepted'] ?? null) || !array_is_list($task['accepted'])
            || ($task['accepted'][0] ?? null) !== ($task['answer'] ?? null)) { self::fail('visible criterion/canonical native contract differs.'); }
        foreach ($task['feedback'] ?? [] as $key => $value) {
            if ($key !== mb_strtolower(trim($key)) || !self::plain($value) || !self::ukrainian($value)) { self::fail('feedback must be plain explanatory Ukrainian.'); }
        }
        foreach ($task['accepted'] as $alias) {
            if (!self::plain($alias) || !self::plain($task['feedback'][mb_strtolower(trim($alias))] ?? null)) { self::fail('declared alias lacks complete plain feedback.'); }
        }
        if ($group !== 'inputs') {
            $options = $task['options'] ?? []; $correct = 0;
            $keys = array_map(self::comparisonKey(...), $options);
            if (count($options) !== 3 || count(array_unique($keys)) !== 3 || in_array('', $keys, true) || count($task['accepted']) !== 1) {
                self::fail('each choice needs three distinct candidates and one declared key.');
            }
            foreach ($options as $option) {
                if (!self::plain($option) || !self::plain($task['feedback'][mb_strtolower(trim($option))] ?? null)) { self::fail('option lacks plain feedback.'); }
                foreach (AcceptedAnswerVariants::for($task['answer']) as $alias) {
                    if (self::comparisonKey($alias) === self::comparisonKey($option)) { $correct++; break; }
                }
            }
            if ($correct !== 1) { self::fail('options collide or have no unique native-correct candidate.'); }
        }
    }
    private static function assertSentence(array $task, int $index): void
    {
        $tokens = array_map('trim', explode('/', $task['before'] ?? ''));
        if (count($tokens) < 2 || implode(' ', $tokens) === $task['answer'] || !self::plain($task['after'] ?? null)) {
            self::fail('full input requires shuffled native groups and plain visible condition.');
        }
        foreach ($tokens as $token) {
            if (!self::plain($token) || count(preg_split('/\s+/u', $token)) > 3) { self::fail('tokens must have one to three words.'); }
        }
        $remaining = $tokens; $tail = $task['answer'];
        while ($remaining !== []) {
            $found = false;
            foreach ($remaining as $n => $token) {
                if ($tail === $token || str_starts_with($tail, $token.' ')) {
                    $tail = $tail === $token ? '' : substr($tail, strlen($token) + 1); unset($remaining[$n]); $found = true; break;
                }
            }
            if (!$found) { self::fail('literal token multiset cannot compose the canonical sentence.'); }
        }
        if ($tail !== '' || ($task['alias_modes']['canonical'] ?? null) !== ['manual', 'token']) { self::fail('canonical manual/token modes differ.'); }
        if ($index === 0) {
            if ($task['accepted'] !== [$task['answer']] || array_keys($task['alias_modes']) !== ['canonical']) { self::fail('studio task has unreviewed aliases.'); }
        } elseif ($task['accepted'] !== [$task['answer'], self::MANUAL_ONLY_ALIAS]
            || ($task['alias_modes'] ?? null) !== ['canonical' => ['manual', 'token'], self::MANUAL_ONLY_ALIAS => ['manual']]) {
            self::fail('exactly one extra bus alias is manual-only, never claimed token-composable.');
        }
    }
    private static function ukrainian(mixed $value): bool { return is_string($value) && preg_match('/[\x{0400}-\x{04FF}]/u', $value) === 1; }
    private static function plain(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '' && preg_match('/<[^>]+>|\{a[0-9]+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $value) !== 1;
    }
    public static function comparisonKey(string $value): string { return M76PronounRelations::comparisonKey($value); }

    private static function validated(): array
    {
        $all = self::load(); self::validate($all[0], $all[1], $all[2], $all[3]); return $all;
    }

    private static function exactAttributes(object $model, array $expected): bool
    {
        if (!method_exists($model, 'getRawOriginal') || !method_exists($model, 'getAttributes')
            || $model->getRawOriginal() !== $model->getAttributes()) { return false; }
        foreach ($expected as $field => $value) {
            if ($model->getRawOriginal($field) !== $value || $model->getAttribute($field) !== $value) { return false; }
        }
        return true;
    }

    private static function exactBlock(TextBlock $block, array $source, int $slot): bool
    {
        $id = $block->getRawOriginal('id');
        if (!is_int($id) || $id < 1 || ($slot < 2 && $id !== self::CONTINUING_IDS[$slot])) { return false; }
        return self::exactAttributes($block, ['seeder' => self::OWNERS[0], 'locale' => 'uk', 'page_id' => 284,
            'page_category_id' => 34, 'uuid' => M26DetailPackage::uuid(self::OWNERS[0], $source, $slot + 1),
            'type' => $source['type'], 'column' => $source['column'], 'heading' => $source['heading'] ?? null,
            'level' => $source['level'] ?? null, 'css_class' => $source['css_class'] ?? null,
            'sort_order' => $slot + 1, 'body' => $source['body']]);
    }

    /** A literal HTML box is matched as a string; it is never decoded as JSON. */
    private static function binding(object $block, ?array $data = null): ?array
    {
        if (!$block instanceof TextBlock || $block->getRawOriginal('seeder') !== self::OWNERS[0]
            || $block->getRawOriginal('locale') !== 'uk') { return null; }
        try {
            [$before, $patch, $master] = self::validated(); $target = $patch['targets'][0];
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                if ($block->getRawOriginal('uuid') !== M26DetailPackage::uuid(self::OWNERS[0], $source, $slot + 1)
                    || !self::exactBlock($block, $source, $slot)) { continue; }
                if ($slot === 1) {
                    if ($data !== null) { return null; }
                } elseif ($data !== null && $data !== json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) {
                    return null;
                }
                return ['target' => $target, 'lesson' => $master['lessons'][0], 'source' => $source, 'slot' => $slot,
                    'before' => $before['targets'][0]['before']];
            }
        } catch (\Throwable) { /* A stale or foreign source remains untouched. */ }
        return null;
    }

    private static function design(array $binding): array
    {
        return ['package' => 'm94-native-v1', 'identity' => self::OWNERS[0], 'locale' => 'uk',
            'uuid' => M26DetailPackage::uuid(self::OWNERS[0], $binding['source'], $binding['slot'] + 1),
            'source_index' => $binding['slot'], 'body_sha256' => hash('sha256', $binding['source']['body']),
            'component' => $binding['source']['type']];
    }

    public static function presentation(object $block, array $data): ?array
    {
        if (app()->getLocale() !== 'uk') { return null; }
        $binding = self::binding($block, $data);
        if ($binding === null || $binding['slot'] === 1) { return null; }
        $slot = $binding['slot']; $marker = $data['m94_native_v1'] ?? null;
        if ($marker !== ['key' => self::BLOCK_KEYS[$slot], 'detail_keys' => $slot === 3 ? ['m94-whose-of-which'] : []]) { return null; }
        $points = [];
        foreach ($binding['lesson']['details'] as $detail) {
            if ($detail['owner_block_key'] !== self::BLOCK_KEYS[$slot]) { continue; }
            $index = (int) explode('/', $detail['owner_pointer'])[2];
            $points[$index] = ['key' => $detail['key'], 'title' => $detail['title'], 'fragments' => array_map(
                static fn ($section, $n) => ['id' => $detail['key'].'-detail-'.$n, 'type' => 'usage-panels', 'value' => $section],
                $detail['fragment']['data']['sections'], array_keys($detail['fragment']['data']['sections']))];
        }
        return ['data' => $binding['lesson']['blocks'][$slot]['data'] + ['m94_native_v1' => $marker],
            'points' => $points, 'legacy_section' => null, 'legacy_practice_id' => null,
            'm42_native_design' => self::design($binding)];
    }

    public static function validDesign(?array $design): bool
    {
        if (($design['package'] ?? null) !== 'm94-native-v1' || ($design['identity'] ?? null) !== self::OWNERS[0]
            || ($design['locale'] ?? null) !== 'uk') { return false; }
        try {
            [, $patch] = self::validated(); $target = $patch['targets'][0];
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                if ($slot !== 1 && $design === self::design(compact('target', 'source', 'slot'))) { return true; }
            }
        } catch (\Throwable) {}
        return false;
    }

    public static function pointPanels(?array $design): ?array
    {
        if (!self::validDesign($design) || $design['component'] !== 'usage-panels') { return null; }
        [, , $master] = self::validated(); $points = [];
        foreach ($master['lessons'][0]['blocks'][$design['source_index']]['data']['sections'] as $n => $section) {
            $points[$n] = ['label' => $section['label'], 'accent' => $section['color'],
                'description_sha256' => [hash('sha256', $section['description'])]];
        }
        return ['points_by_index' => $points];
    }

    public static function correction(array $item, ?array $design): ?array
    {
        if (!self::validDesign($design) || $design['component'] !== 'mistakes-grid') { return null; }
        [, , $master] = self::validated();
        $items = $master['lessons'][0]['blocks'][$design['source_index']]['data']['items'];
        if (!in_array($item, $items, true)) { return null; }
        return ['wrong_en' => TheoryInlineHtml::render($item['wrong_en']), 'wrong_uk' => TheoryInlineHtml::render($item['wrong_ua']),
            'right_en' => TheoryInlineHtml::render($item['right_en']), 'right_uk' => TheoryInlineHtml::render($item['right_ua']),
            'hint_html' => TheoryInlineHtml::render($item['note'])];
    }

    private static function exactSubtitle(TextBlock $block, array $target): bool
    {
        return self::exactAttributes($block, ['id' => self::SUBTITLE_ID, 'uuid' => self::SUBTITLE_UUID,
            'seeder' => self::OWNERS[0], 'locale' => 'uk', 'page_id' => 284, 'page_category_id' => 34,
            'sort_order' => 0, 'type' => 'subtitle', 'column' => 'header', 'heading' => null,
            'level' => null, 'css_class' => null, 'body' => $target['after']['page']['subtitle_html']]);
    }

    private static function cloneConfig(TextBlock $block, array $source): TextBlock
    {
        $copy = clone $block; $fields = ['body' => $source['body']];
        foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) { $fields[$field] = $source[$field] ?? null; }
        $copy->setRawAttributes(array_replace($block->getAttributes(), $fields), true);
        return $copy;
    }

    /**
     * BelongsToMany moves these three loaded aliases to Pivot without removing
     * them from raw originals. Normalize only an unsaved standalone clone and
     * only when that exact framework tuple is the entire original/current gap.
     */
    private static function standalonePivotClone(TextBlock $block): ?TextBlock
    {
        $original = $block->getRawOriginal(); $attributes = $block->getAttributes();
        if ($original === $attributes) { return $block; }
        $keys = ['question_uuid', 'text_block_uuid', 'position'];
        $aliases = ['pivot_question_uuid', 'pivot_text_block_uuid', 'pivot_position'];
        $extra = array_diff_key($original, $attributes);
        if (array_keys($extra) !== $aliases || array_diff_key($original, array_flip($aliases)) !== $attributes
            || !$block->relationLoaded('pivot')) { return null; }
        $pivot = $block->getRelation('pivot');
        if (!$pivot instanceof \Illuminate\Database\Eloquent\Relations\Pivot
            || $pivot->getTable() !== 'question_theory_text_blocks'
            || $pivot->getRawOriginal() !== $pivot->getAttributes()
            || array_keys($pivot->getAttributes()) !== $keys) { return null; }
        $values = $pivot->getAttributes();
        if (!is_string($values['question_uuid']) || trim($values['question_uuid']) === ''
            || !is_string($values['text_block_uuid']) || $values['text_block_uuid'] !== ($attributes['uuid'] ?? null)
            || !is_int($values['position']) || $values['position'] < 0) { return null; }
        foreach ($keys as $n => $key) { if ($extra[$aliases[$n]] !== $values[$key]) { return null; } }
        $copy = clone $block; $copy->setRawAttributes($attributes, true);
        return $copy;
    }

    public static function preserveExcludedTextBlock(object $block, string $caller): ?object
    {
        if ($caller !== 'standalone_test' || !$block instanceof TextBlock || $block->getRawOriginal('seeder') !== self::OWNERS[0]
            || $block->getRawOriginal('locale') !== 'uk') { return $block; }
        $original = $block;
        try {
            $candidate = self::standalonePivotClone($block);
            if ($candidate === null) { return $original; }
            $block = $candidate;
            if ($block->getRawOriginal('type') === 'subtitle') {
                [$before, $patch] = self::validated();
                if (!self::exactSubtitle($block, $patch['targets'][0])) { return $original; }
                $copy = clone $block;
                $copy->setRawAttributes(array_replace($block->getAttributes(), ['body' => $before['targets'][0]['before']['page']['subtitle_html']]), true);
                return $copy;
            }
            $binding = self::binding($block);
            if ($binding === null) { return $original; }
            if ($binding['slot'] >= 2) { return null; }
            return self::cloneConfig($block, $binding['before']['page']['blocks'][$binding['slot']]);
        } catch (\Throwable) { return $original; }
    }

    /** Loaded relations only. Restores the exact old subtitle/hero/HTML box on clones. */
    public static function preserveCoursePage(?Page $page): ?Page
    {
        if ($page === null || $page->getRawOriginal('seeder') !== self::OWNERS[0] || !$page->relationLoaded('textBlocks')) { return $page; }
        try {
            [$before, $patch] = self::validated(); $target = $patch['targets'][0]; $old = $before['targets'][0]['before'];
            if (!self::exactAttributes($page, ['id' => 284, 'page_category_id' => 34, 'seeder' => self::OWNERS[0],
                'slug' => self::SLUGS[0], 'title' => self::RENDERED_TITLES[0], 'type' => 'theory',
                'text' => $target['after']['page']['subtitle_text']])) { return $page; }
            $blocks = $page->getRelation('textBlocks');
            if (!$blocks instanceof Collection) { return $page; }
            $uk = $blocks->filter(static fn ($b) => $b instanceof TextBlock && $b->getRawOriginal('locale') === 'uk')->values();
            if ($uk->count() !== 9) { return $page; }
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                $matches = $uk->filter(static fn ($b) => $b->getRawOriginal('uuid') === M26DetailPackage::uuid(self::OWNERS[0], $source, $slot + 1))->values();
                if ($matches->count() !== 1 || !self::exactBlock($matches[0], $source, $slot)) { return $page; }
            }
            $subtitles = $uk->filter(static fn ($b) => $b->getRawOriginal('type') === 'subtitle')->values();
            if ($subtitles->count() !== 1 || !self::exactSubtitle($subtitles[0], $target)) { return $page; }
            $copy = clone $page;
            $copy->setRawAttributes(array_replace($page->getAttributes(), ['text' => $old['page']['subtitle_text']]), true);
            $restored = $blocks->reject(static fn ($b) => $b instanceof TextBlock && $b->getRawOriginal('locale') === 'uk')->values();
            $subtitle = clone $subtitles[0];
            $subtitle->setRawAttributes(array_replace($subtitles[0]->getAttributes(), ['body' => $old['page']['subtitle_html']]), true);
            $restored->push($subtitle);
            foreach ($old['page']['blocks'] as $slot => $source) {
                $model = $uk->first(static fn ($b) => $b->getRawOriginal('uuid') === self::CONTINUING_UUIDS[$slot]);
                $restored->push(self::cloneConfig($model, $source));
            }
            $copy->setRelation('textBlocks', new Collection($restored->sortBy('sort_order')->values()->all()));
            return $copy;
        } catch (\Throwable) { return $page; }
    }
}
