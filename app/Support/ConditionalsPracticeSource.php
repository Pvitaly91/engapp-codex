<?php

namespace App\Support;

use RuntimeException;

/** Read-only exact source adapter for thirteen accepted conditional practice sets; no new questions or grading changes. */
final class ConditionalsPracticeSource
{
    public const SOURCE = 'docs/content/conditionals-practice-source.v1.json';
    public const SOURCE_SHA = '4c37c6686bd380a25d40ee4df769bdf129612d3596d3f738768718f6b2f60528';
    private const PAGE_IDS=['zero-conditional'=>53,'first-conditional'=>44,'second-conditional'=>48,'third-conditional'=>49,'mixed-conditionals'=>47,'unless-provided-as-long-as'=>50,'in-case'=>46,'wish-if-only'=>51,'would-rather'=>52,'had-better'=>45,'conditionals-with-unless-provided-as-long-as'=>287,'advanced-conditionals'=>294,'conditional-alternatives-and-nuance'=>309];

    public const OWNERS = [
        'zero-conditional' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsZeroTheorySeeder',
        'first-conditional' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsFirstTheorySeeder',
        'second-conditional' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsSecondTheorySeeder',
        'third-conditional' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsThirdTheorySeeder',
        'mixed-conditionals' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsMixedTheorySeeder',
        'unless-provided-as-long-as' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsUnlessProvidedAsLongAsTheorySeeder',
        'in-case' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsInCaseTheorySeeder',
        'wish-if-only' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWishIfOnlyTheorySeeder',
        'would-rather' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWouldRatherTheorySeeder',
        'had-better' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsHadBetterTheorySeeder',
        'conditionals-with-unless-provided-as-long-as' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWithUnlessProvidedAsLongAsTheorySeeder',
        'advanced-conditionals' => 'Database\\Seeders\\Page_V3\\Conditionals\\AdvancedConditionalsTheorySeeder',
        'conditional-alternatives-and-nuance' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalAlternativesAndNuanceTheorySeeder',
    ];

    private const PROVIDERS = [
        'zero-conditional' => [PracticeCompletionPackage::class, 0],
        'first-conditional' => [PracticeCompletionPackage::class, 1],
        'second-conditional' => [PracticeCompletionPackage::class, 2],
        'third-conditional' => [PracticeCompletionPackage::class, 3],
        'mixed-conditionals' => [PracticeCompletionPackage::class, 4],
        'unless-provided-as-long-as' => [PracticeCompletionPackage::class, 5],
        'in-case' => [PracticeCompletionPackage::class, 6],
        'wish-if-only' => [PracticeCompletionPackage::class, 7],
        'would-rather' => [PracticeCompletionPackage::class, 8],
        'had-better' => [PracticeCompletionPackage::class, 9],
        'conditionals-with-unless-provided-as-long-as' => [M31ConditionalsPackage::class, 0],
        'advanced-conditionals' => [M31ConditionalsPackage::class, 1],
        'conditional-alternatives-and-nuance' => [M31ConditionalsPackage::class, 2],
    ];

    public static function digest(array $value): string
    {
        return hash('sha256', M26DetailPackage::json($value));
    }

    /** Code-owned providers and target slots; no manifest value selects executable code. */
    public static function specs(): array
    {
        return self::PROVIDERS;
    }

    private static function deriveTargets(): array
    {
        $packages = []; $targets = [];
        foreach (self::PROVIDERS as $topic => [$provider, $targetIndex]) {
            if (!isset($packages[$provider])) { [, $packages[$provider]] = $provider::load(); }
            $sourceTarget = $packages[$provider]['targets'][$targetIndex] ?? null;
            $identity = self::OWNERS[$topic];
            if (!is_array($sourceTarget) || ($sourceTarget['identity'] ?? null) !== $identity
                || ($sourceTarget['slug'] ?? null) !== $topic) { throw new RuntimeException('Conditional practice source identity differs.'); }

            if ($provider === PracticeCompletionPackage::class) {
                $source = $sourceTarget['after']['practice']; $slot = $source['sort_order'] - 1;
                $page = $sourceTarget['page'];
            } else {
                $found = [];
                foreach ($sourceTarget['after']['page']['blocks'] as $i => $block) {
                    if (($block['type'] ?? null) === 'practice-set') { $found[] = [$i, $block]; }
                }
                if (count($found) !== 1) { throw new RuntimeException('Conditional practice source cardinality differs.'); }
                [$slot, $source] = $found[0]; $page = $sourceTarget['page_binding'] ?? [];
            }
            $binding = ['seeder' => $identity, 'locale' => 'uk',
                'uuid' => M26DetailPackage::uuid($identity, $source, $slot + 1), 'sort_order' => $slot + 1];
            foreach (['id' => 'page_id', 'page_category_id' => 'page_category_id'] as $from => $to) {
                if (isset($page[$from])) { $binding[$to] = $page[$from]; }
            }
            if((isset($binding['page_id'])&&$binding['page_id']!==self::PAGE_IDS[$topic])||(isset($binding['page_category_id'])&&$binding['page_category_id']!==10))throw new RuntimeException('Conditional native exact page/category differs.');
            $binding['page_id']=self::PAGE_IDS[$topic];$binding['page_category_id']=10;
            foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) { $binding[$field] = $source[$field] ?? null; }
            $block = (object) ($binding + ['body' => $source['body']]);
            $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
            $presentation = $provider::presentation($block, $data);
            if ($provider === M31ConditionalsPackage::class) {
                $presentation = M42NativeDesignPackage::decorate($block, $data, $presentation, true);
            }
            if (!is_array($presentation) || ($presentation['m42_native_design']['component'] ?? null) !== 'practice-set') {
                throw new RuntimeException('Conditional accepted presenter rejected its source.');
            }
            $render = $presentation['data'] ?? $data;
            $counts = [];
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $kind) { $counts[$kind] = count($render[$kind] ?? []); }
            $binding['body_sha256'] = hash('sha256', $source['body']);
            $definition = 'database/seeders/Page_V3/Conditionals/'.substr($identity, strrpos($identity, '\\') + 1).'/definition.json';
            $definitionPath = base_path($definition);
            if (!is_file($definitionPath) || is_link($definitionPath)) { throw new RuntimeException('Conditional native definition missing.'); }
            $definitionData = json_decode(file_get_contents($definitionPath), true, flags: JSON_THROW_ON_ERROR);
            if (($definitionData['seeder']['class'] ?? null) !== $identity || ($definitionData['slug'] ?? null) !== $topic
                || ($definitionData['page']['locale'] ?? null) !== 'uk' || ($definitionData['page']['category']['slug'] ?? null) !== 'conditionals'
                || ($definitionData['page']['blocks'][$slot]['body'] ?? null) !== $source['body']) {
                throw new RuntimeException('Conditional canonical definition and accepted source differ.');
            }
            $targets[] = ['topic' => $topic, 'local_url' => 'http://gramlyze.loc/theory/conditionals/'.$topic,
                'identity' => $identity, 'provider' => $provider, 'source' => $provider::SOURCE, 'source_sha256' => $provider::SOURCE_SHA,
                'definition' => $definition, 'definition_sha256' => hash_file('sha256', $definitionPath),
                'binding' => $binding, 'source_index' => $slot, 'design' => $presentation['m42_native_design'],
                'before_data' => $render, 'after_data' => $render, 'before_data_sha256' => self::digest($render),
                'after_data_sha256' => self::digest($render), 'counts' => $counts];
        }
        if (count($targets) !== 13) {
            throw new RuntimeException('Conditional practice finite scope differs.');
        }
        return $targets;
    }

    public static function detailed(): array { return ['schema_version'=>1, 'targets'=>self::deriveTargets()]; }

    private static function manifest(array $targets): array
    {
        foreach ($targets as &$target) { unset($target['before_data'], $target['after_data']); } unset($target);
        return ['schema_version' => 1, 'purpose' => 'Exact accepted Conditional Verbs practice metadata; no question, answer or design mutation.', 'targets' => $targets];
    }

    /** Pure regeneration input for the reviewed metadata manifest. */
    public static function compile(): array
    {
        return self::manifest(self::deriveTargets());
    }

    public static function validate(array $manifest): void
    {
        if ($manifest !== self::compile()) { throw new RuntimeException('Conditional practice manifest differs from accepted sources.'); }
    }

    /** SUPPLEMENT-compatible interface; before_data and after_data are deliberately identical. */
    public static function load(): array
    {
        $path = base_path(self::SOURCE);
        if (!is_file($path) || is_link($path) || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $path))) {
            throw new RuntimeException('Conditional practice source manifest differs.');
        }
        static $document;
        if (!isset($document)) {
            $manifest = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            $targets = self::deriveTargets();
            if ($manifest !== self::manifest($targets)) { throw new RuntimeException('Conditional practice source bindings differ.'); }
            $document = ['schema_version' => 1, 'targets' => $targets];
        }
        return $document;
    }
}


