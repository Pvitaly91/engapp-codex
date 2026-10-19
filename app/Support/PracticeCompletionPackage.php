<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/** Exact supplemental practice data; historical releases and visual consumers stay unchanged. */
final class PracticeCompletionPackage
{
    public const BASE_SHA = '7d339c237060d76e9f7c7981ab71f07dfad3529d';
    public const MASTER = 'docs/content/m46-m49-practice-completion.v1.0.1.json';
    public const MASTER_SHA = 'f98fb2e19a8bd619802dcd2f3e350b19414f81a508c69a75a2de3147f3652b89';
    public const BEFORE = 'database/content-patches/m46-m49-practice-completion-before.v1.json';
    public const BEFORE_SHA = 'b59cc3c523244fb1285b149a4e27418df80ebcedb7b03968de497ec062edd099';
    public const SOURCE = 'database/content-patches/m46-m49-practice-completion.v1.0.1.json';
    public const SOURCE_SHA = '51b35c0b099d4ab2f72c33ccfee070763e781a5c54888adde11079b0482b78a8';
    public const PACKAGE = 'm46-m49-practice-completion-v1';

    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsZeroTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsFirstTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsSecondTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsThirdTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsMixedTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsUnlessProvidedAsLongAsTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsInCaseTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWishIfOnlyTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWouldRatherTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsHadBetterTheorySeeder',
        'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsCanCouldTheorySeeder',
        'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMayMightTheorySeeder',
    ];

    /** Provider classes and definition paths are code-owned, never executable payload fields. */
    public const BINDINGS = [
        ['identity' => self::OWNERS[0], 'slug' => 'zero-conditional', 'ancestry' => ['conditionals'],
            'definition' => 'database/seeders/Page_V3/Conditionals/ConditionalsZeroTheorySeeder/definition.json', 'legacy_package' => M46ConditionalsPackage::class],
        ['identity' => self::OWNERS[1], 'slug' => 'first-conditional', 'ancestry' => ['conditionals'],
            'definition' => 'database/seeders/Page_V3/Conditionals/ConditionalsFirstTheorySeeder/definition.json', 'legacy_package' => M46ConditionalsPackage::class],
        ['identity' => self::OWNERS[2], 'slug' => 'second-conditional', 'ancestry' => ['conditionals'],
            'definition' => 'database/seeders/Page_V3/Conditionals/ConditionalsSecondTheorySeeder/definition.json', 'legacy_package' => M46ConditionalsPackage::class],
        ['identity' => self::OWNERS[3], 'slug' => 'third-conditional', 'ancestry' => ['conditionals'],
            'definition' => 'database/seeders/Page_V3/Conditionals/ConditionalsThirdTheorySeeder/definition.json', 'legacy_package' => M47ConditionalsPackage::class],
        ['identity' => self::OWNERS[4], 'slug' => 'mixed-conditionals', 'ancestry' => ['conditionals'],
            'definition' => 'database/seeders/Page_V3/Conditionals/ConditionalsMixedTheorySeeder/definition.json', 'legacy_package' => M47ConditionalsPackage::class],
        ['identity' => self::OWNERS[5], 'slug' => 'unless-provided-as-long-as', 'ancestry' => ['conditionals'],
            'definition' => 'database/seeders/Page_V3/Conditionals/ConditionalsUnlessProvidedAsLongAsTheorySeeder/definition.json', 'legacy_package' => M47ConditionalsPackage::class],
        ['identity' => self::OWNERS[6], 'slug' => 'in-case', 'ancestry' => ['conditionals'],
            'definition' => 'database/seeders/Page_V3/Conditionals/ConditionalsInCaseTheorySeeder/definition.json', 'legacy_package' => M48ConditionalsPackage::class],
        ['identity' => self::OWNERS[7], 'slug' => 'wish-if-only', 'ancestry' => ['conditionals'],
            'definition' => 'database/seeders/Page_V3/Conditionals/ConditionalsWishIfOnlyTheorySeeder/definition.json', 'legacy_package' => M48ConditionalsPackage::class],
        ['identity' => self::OWNERS[8], 'slug' => 'would-rather', 'ancestry' => ['conditionals'],
            'definition' => 'database/seeders/Page_V3/Conditionals/ConditionalsWouldRatherTheorySeeder/definition.json', 'legacy_package' => M48ConditionalsPackage::class],
        ['identity' => self::OWNERS[9], 'slug' => 'had-better', 'ancestry' => ['conditionals'],
            'definition' => 'database/seeders/Page_V3/Conditionals/ConditionalsHadBetterTheorySeeder/definition.json', 'legacy_package' => M49AdviceModalsPackage::class],
        ['identity' => self::OWNERS[10], 'slug' => 'can-could', 'ancestry' => ['modal-verbs'],
            'definition' => 'database/seeders/Page_V3/ModalVerbs/ModalVerbsCanCouldTheorySeeder/definition.json', 'legacy_package' => M49AdviceModalsPackage::class],
        ['identity' => self::OWNERS[11], 'slug' => 'may-might', 'ancestry' => ['modal-verbs'],
            'definition' => 'database/seeders/Page_V3/ModalVerbs/ModalVerbsMayMightTheorySeeder/definition.json', 'legacy_package' => M49AdviceModalsPackage::class],
    ];

    private const BLOCK_FIELDS = ['type', 'column', 'heading', 'level', 'css_class', 'sort_order', 'uuid', 'body'];
    private const PAGE_FIELDS = ['id', 'title', 'slug', 'type', 'locale', 'page_category_id'];

    /** @return array{0: array, 1: array, 2: array} */
    public static function load(?string $root = null): array
    {
        $root ??= base_path();
        $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA, self::MASTER => self::MASTER_SHA] as $path => $sha) {
            $file = $root.'/'.$path;
            if (!is_file($file) || is_link($file)) { throw new RuntimeException('Practice completion source missing or linked: '.$path); }
            $bytes = file_get_contents($file);
            if (!is_string($bytes) || !hash_equals($sha, hash('sha256', $bytes))) {
                throw new RuntimeException('Practice completion immutable source differs: '.$path);
            }
            $value = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($value)) { throw new RuntimeException('Practice completion source is not an object.'); }
            $out[] = $value;
        }
        return $out;
    }

    /** The only permitted learning-data delta; all old fields remain in their original order. */
    public static function afterBody(string $beforeBody, array $lesson): string
    {
        $data = json_decode($beforeBody, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($data) || isset($data['practice_completion_v1'])
            || !empty($data['inputs']) || isset($data['input_title']) || isset($data['input_intro'])) {
            throw new RuntimeException('Practice completion BEFORE already has supplemental input data.');
        }
        foreach (['input_title', 'input_intro'] as $field) {
            if (!is_string($lesson[$field] ?? null) || trim($lesson[$field]) === '') {
                throw new RuntimeException('Practice completion input heading/instruction is empty.');
            }
            $data[$field] = $lesson[$field];
        }
        $data['inputs'] = $lesson['inputs'] ?? null;
        $data['practice_completion_v1'] = [
            'version' => 1, 'source_before_sha256' => hash('sha256', $beforeBody), 'package' => self::PACKAGE,
        ];
        return M26DetailPackage::json($data);
    }

    public static function validate(array $before, array $patch, array $master, ?string $root = null): void
    {
        $root ??= base_path();
        foreach ([$before, $patch, $master] as $artifact) {
            if (($artifact['schema_version'] ?? null) !== 1 || ($artifact['base_sha'] ?? null) !== self::BASE_SHA) {
                throw new RuntimeException('Practice completion schema/base differs.');
            }
        }
        if (($master['version'] ?? null) !== '1.0.1'
            || array_column($before['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($patch['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($master['lessons'] ?? [], 'identity') !== self::OWNERS) {
            throw new RuntimeException('Practice completion exact owner/version scope differs.');
        }

        $releases = []; $ids = [];
        foreach (self::BINDINGS as $i => $scope) {
            $original = $before['targets'][$i]; $target = $patch['targets'][$i]; $lesson = $master['lessons'][$i];
            if (!self::exactKeys($original, ['identity', 'slug', 'definition', 'ancestry', 'local_url', 'practice', 'page'])
                || !self::exactKeys($lesson, ['identity', 'slug', 'ancestry', 'input_title', 'input_intro', 'inputs'])) {
                throw new RuntimeException('Practice completion owner or lesson has unsupported or missing fields.');
            }
            foreach (['identity', 'slug', 'ancestry'] as $field) {
                if (($original[$field] ?? null) !== $scope[$field] || ($lesson[$field] ?? null) !== $scope[$field]) {
                    throw new RuntimeException('Practice completion owner/slug/category differs.');
                }
            }
            if (($original['definition'] ?? null) !== $scope['definition']
                || ($original['local_url'] ?? null) !== 'http://gramlyze.loc/theory/'.implode('/', $scope['ancestry']).'/'.$scope['slug']) {
                throw new RuntimeException('Practice completion definition/local route differs.');
            }
            $page = $original['page'] ?? [];
            if (!self::exactKeys($page, self::PAGE_FIELDS) || ($page['slug'] ?? null) !== $scope['slug']
                || ($page['type'] ?? null) !== 'theory' || ($page['locale'] ?? null) !== 'uk'
                || !is_string($page['title'] ?? null) || trim($page['title']) === ''
                || !is_int($page['id'] ?? null) || $page['id'] <= 0
                || !is_int($page['page_category_id'] ?? null) || $page['page_category_id'] <= 0) {
                throw new RuntimeException('Practice completion captured page metadata differs.');
            }

            $provider = $scope['legacy_package'];
            if (!isset($releases[$provider])) {
                $release = $provider::load($root);
                $provider::validate(...$release);
                $releases[$provider] = $release[1];
            }
            $historical = null;
            foreach ($releases[$provider]['targets'] as $candidate) {
                if ($candidate['identity'] === $scope['identity']) { $historical = $candidate; break; }
            }
            if ($historical === null || $historical['path'] !== $scope['definition']
                || ($historical['after']['slug'] ?? null) !== $scope['slug']
                || ($historical['after']['page']['category']['slug'] ?? null) !== $scope['ancestry'][0]) {
                throw new RuntimeException('Practice completion historical release owner differs.');
            }
            $source = null; $position = null;
            foreach ($historical['after']['page']['blocks'] as $slot => $candidate) {
                if ($candidate['type'] !== 'practice-set') { continue; }
                if ($source !== null) { throw new RuntimeException('Practice completion historical practice is not unique.'); }
                $source = $candidate; $position = $slot + 1;
            }
            $practice = $original['practice'] ?? [];
            if ($source === null || !self::exactKeys($practice, self::BLOCK_FIELDS)) {
                throw new RuntimeException('Practice completion BEFORE practice shape differs.');
            }
            foreach (['type', 'column', 'heading', 'level', 'css_class', 'body'] as $field) {
                if ($practice[$field] !== ($source[$field] ?? null)) {
                    throw new RuntimeException('Practice completion BEFORE is not the exact historical practice.');
                }
            }
            if ($practice['sort_order'] !== $position
                || $practice['uuid'] !== M26DetailPackage::uuid($scope['identity'], $source, $position)) {
                throw new RuntimeException('Practice completion old UUID/order differs.');
            }

            $data = json_decode($practice['body'], true, flags: JSON_THROW_ON_ERROR);
            $oldTasks = [...($data['selects'] ?? []), ...($data['choices'] ?? [])];
            if (count($oldTasks) !== 6 || array_column($oldTasks, 'source_index') !== range(1, 6)) {
                throw new RuntimeException('Practice completion historical six-task identity differs.');
            }
            foreach ($oldTasks as $task) {
                // M46/M47 identify their immutable controls by source_index only.
                // Preserve absent historical IDs; never invent or renumber them.
                if (!array_key_exists('id', $task)) { continue; }
                if (!is_string($task['id'] ?? null) || $task['id'] === '' || isset($ids[$task['id']])) {
                    throw new RuntimeException('Practice completion historical task ID collides.');
                }
                $ids[$task['id']] = true;
            }
            $inputs = $lesson['inputs'] ?? null;
            if (!is_array($inputs) || !array_is_list($inputs) || count($inputs) !== 2
                || array_column($inputs, 'source_index') !== [7, 8]) {
                throw new RuntimeException('Practice completion requires exactly two explicit new inputs.');
            }
            self::assertLearningData([$lesson['input_title'] ?? null, $lesson['input_intro'] ?? null, $inputs]);
            foreach ($inputs as $input) {
                if (!is_array($input) || !self::exactKeys($input, ['id', 'source_index', 'before', 'after', 'answer', 'accepted', 'feedback'])) {
                    throw new RuntimeException('Practice completion input has unsupported or missing fields.');
                }
                if (!is_string($input['id'] ?? null) || trim($input['id']) === '' || isset($ids[$input['id']])) {
                    throw new RuntimeException('Practice completion new task ID is missing or collides.');
                }
                $ids[$input['id']] = true;
                if (!is_string($input['before'] ?? null) || !str_contains($input['before'], '/')
                    || count(array_filter(array_map('trim', explode('/', $input['before'])), static fn ($v) => $v !== '')) < 2
                    || !is_string($input['after'] ?? null) || trim($input['after']) === ''
                    || !is_string($input['answer'] ?? null) || trim($input['answer']) === ''
                    || !is_array($input['accepted'] ?? null) || !array_is_list($input['accepted'])
                    || !in_array($input['answer'], $input['accepted'], true) || !is_array($input['feedback'] ?? null)) {
                    throw new RuntimeException('Practice completion input bank, visible condition or answer is incomplete.');
                }
                foreach ($input['accepted'] as $answer) {
                    if (!is_string($answer) || trim($answer) === ''
                        || !is_string($input['feedback'][mb_strtolower(trim($answer))] ?? null)
                        || trim($input['feedback'][mb_strtolower(trim($answer))]) === '') {
                        throw new RuntimeException('Practice completion accepted answer/translated feedback is missing.');
                    }
                }
            }

            $after = $practice;
            $after['body'] = self::afterBody($practice['body'], $lesson);
            $expected = $original;
            unset($expected['practice']);
            $expected['before'] = ['practice' => $practice];
            $expected['after'] = ['practice' => $after];
            if ($target !== $expected) {
                throw new RuntimeException('Practice completion exact BEFORE-to-AFTER projection differs.');
            }
        }
    }

    private static function exactKeys(array $value, array $keys): bool
    {
        $actual = array_keys($value); sort($actual); sort($keys);
        return $actual === $keys;
    }

    private static function assertLearningData(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), ['view', 'native_view', 'stylesheet', 'css_class', 'class', 'style'], true)) {
                throw new RuntimeException('Practice completion learning payload selects presentation.');
            }
            if (is_array($value)) { self::assertLearningData($value); }
            elseif (is_string($value) && preg_match('~<[^>]*\\s(?:class|style|on[a-z]+)\\s*=|<(?:script|style|iframe)\\b~i', $value)) {
                throw new RuntimeException('Practice completion learning payload contains executable/styled markup.');
            }
        }
    }

    private static function binding(object $block, array $data): ?array
    {
        if (($block->locale ?? null) !== 'uk' || ($block->type ?? null) !== 'practice-set'
            || !in_array($block->seeder ?? null, self::OWNERS, true) || !isset($data['practice_completion_v1'])) {
            return null;
        }
        try {
            [$before, $patch, $master] = self::load();
            self::validate($before, $patch, $master);
            $i = array_search($block->seeder, self::OWNERS, true);
            $target = $patch['targets'][$i]; $practice = $target['after']['practice'];
            if (($block->page_id ?? null) !== $target['page']['id']
                || ($block->page_category_id ?? null) !== $target['page']['page_category_id']) { return null; }
            foreach (self::BLOCK_FIELDS as $field) {
                $actual = $field === 'sort_order' ? (int) ($block->$field ?? -1) : ($block->$field ?? null);
                if ($actual !== $practice[$field]) { return null; }
            }
            if ($data !== json_decode($practice['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }
            return ['scope' => self::BINDINGS[$i], 'target' => $target];
        } catch (\Throwable) { return null; }
    }

    private static function cloneBlock(object $block, string $body): object
    {
        $copy = clone $block;
        if (method_exists($copy, 'setRawAttributes') && method_exists($copy, 'getAttributes')) {
            $copy->setRawAttributes(array_replace($copy->getAttributes(), ['body' => $body]), true);
        } else {
            $copy->body = $body;
        }
        return $copy;
    }

    /** The theory caller receives the full supplemental AFTER, never the course's normalized copy. */
    public static function presentation(object $block, array $data): ?array
    {
        $binding = self::binding($block, $data);
        if ($binding === null) { return null; }
        try {
            $target = $binding['target']; $provider = $binding['scope']['legacy_package'];
            $oldBody = $target['before']['practice']['body'];
            $old = $provider::presentation(self::cloneBlock($block, $oldBody), json_decode($oldBody, true, flags: JSON_THROW_ON_ERROR));
            if ($old === null || ($old['points'] ?? []) !== [] || ($old['m42_native_design']['component'] ?? null) !== 'practice-set') {
                return null;
            }
            return ['data' => PracticeCompletionLinkedPractice::presentationData($block, $data),
                'points' => [], 'legacy_section' => null, 'legacy_practice_id' => null,
                'm42_native_design' => ['package' => self::PACKAGE, 'identity' => $target['identity'], 'locale' => 'uk',
                    'uuid' => $target['after']['practice']['uuid'], 'source_index' => $target['after']['practice']['sort_order'] - 1,
                    'body_sha256' => hash('sha256', $target['after']['practice']['body']), 'component' => 'practice-set']];
        } catch (\Throwable) { return null; }
    }

    /** Course-only, all-or-nothing clone; refused legacy sibling guards return the untouched original page. */
    public static function preserveCoursePage(?Page $page): ?Page
    {
        if ($page === null || !in_array($page->getRawOriginal('seeder'), self::OWNERS, true)
            || !$page->relationLoaded('textBlocks')) { return $page; }
        try {
            $matches = $page->textBlocks->filter(static fn ($block) => $block->locale === 'uk' && $block->type === 'practice-set')->values();
            if ($matches->count() !== 1) { return $page; }
            $block = $matches->first();
            $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($data)) { return $page; }
            $binding = self::binding($block, $data);
            if ($binding === null || $binding['target']['identity'] !== $page->getRawOriginal('seeder')) { return $page; }
            foreach (self::PAGE_FIELDS as $field) {
                // Locale belongs to TextBlock; the captured page locale is the UK target scope.
                if ($field === 'locale') { continue; }
                if ($page->getRawOriginal($field) !== $binding['target']['page'][$field]) { return $page; }
            }
            $copy = clone $page;
            $restored = $page->textBlocks->map(static fn ($value) => $value === $block
                ? self::cloneBlock($value, $binding['target']['before']['practice']['body']) : $value);
            $copy->setRelation('textBlocks', new Collection($restored->all()));
            $provider = $binding['scope']['legacy_package'];
            $legacy = $provider::preserveCoursePage($copy);
            return $legacy === $copy ? $page : $legacy;
        } catch (\Throwable) { return $page; }
    }
}
