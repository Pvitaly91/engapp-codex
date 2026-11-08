<?php

namespace App\Support;

use RuntimeException;

/** Read-only exact source adapter for ten accepted modal practice sets; no new questions or grading changes. */
final class ModalVerbsPracticeSource
{
    public const SOURCE = 'docs/content/modal-verbs-practice-source.v1.json';
    public const SOURCE_SHA = '81d6d561010772e71c89f6fb2e50ec5c2d34b8098b8a29313282f662afb95809';

    public const OWNERS = [
        'modal-perfect-and-deduction' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalPerfectAndDeductionTheorySeeder',
        'subtle-modal-meanings' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\SubtleModalMeaningsTheorySeeder',
        'must-have-to' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMustHaveToTheorySeeder',
        'should-ought-to' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsShouldOughtToTheorySeeder',
        'need-neednt-dont-have-to' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsNeedNeedntDontHaveToTheorySeeder',
        'modals-of-deduction' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsDeductionTheorySeeder',
        'past-modals' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsPastModalsTheorySeeder',
        'requests-offers-suggestions' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsRequestsOffersSuggestionsTheorySeeder',
        'can-could' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsCanCouldTheorySeeder',
        'may-might' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMayMightTheorySeeder',
    ];

    private const PROVIDERS = [
        'modal-perfect-and-deduction' => [M36ModalsSubjunctivePackage::class, 0],
        'subtle-modal-meanings' => [M36ModalsSubjunctivePackage::class, 2],
        'must-have-to' => [M50ObligationAdvicePackage::class, 0],
        'should-ought-to' => [M50ObligationAdvicePackage::class, 1],
        'need-neednt-dont-have-to' => [M50ObligationAdvicePackage::class, 2],
        'modals-of-deduction' => [M51ModalMeaningsPackage::class, 0],
        'past-modals' => [M51ModalMeaningsPackage::class, 1],
        'requests-offers-suggestions' => [M51ModalMeaningsPackage::class, 2],
        'can-could' => [PracticeCompletionPackage::class, 10],
        'may-might' => [PracticeCompletionPackage::class, 11],
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
                || ($sourceTarget['slug'] ?? null) !== $topic) { throw new RuntimeException('Modal practice source identity differs.'); }

            if ($provider === PracticeCompletionPackage::class) {
                $source = $sourceTarget['after']['practice']; $slot = $source['sort_order'] - 1;
                $page = $sourceTarget['page'];
            } else {
                $found = [];
                foreach ($sourceTarget['after']['page']['blocks'] as $i => $block) {
                    if (($block['type'] ?? null) === 'practice-set') { $found[] = [$i, $block]; }
                }
                if (count($found) !== 1) { throw new RuntimeException('Modal practice source cardinality differs.'); }
                [$slot, $source] = $found[0]; $page = $sourceTarget['page_binding'] ?? [];
            }
            $binding = ['seeder' => $identity, 'locale' => 'uk',
                'uuid' => M26DetailPackage::uuid($identity, $source, $slot + 1), 'sort_order' => $slot + 1];
            foreach (['id' => 'page_id', 'page_category_id' => 'page_category_id'] as $from => $to) {
                if (isset($page[$from])) { $binding[$to] = $page[$from]; }
            }
            foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) { $binding[$field] = $source[$field] ?? null; }
            $block = (object) ($binding + ['body' => $source['body']]);
            $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
            $presentation = $provider::presentation($block, $data);
            if ($provider === M36ModalsSubjunctivePackage::class) {
                $presentation = M42NativeDesignPackage::decorate($block, $data, $presentation, true);
            }
            if (!is_array($presentation) || ($presentation['m42_native_design']['component'] ?? null) !== 'practice-set') {
                throw new RuntimeException('Modal accepted presenter rejected its source.');
            }
            $render = $presentation['data'] ?? $data;
            $counts = [];
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $kind) { $counts[$kind] = count($render[$kind] ?? []); }
            $binding['body_sha256'] = hash('sha256', $source['body']);
            $definition = 'database/seeders/Page_V3/ModalVerbs/'.substr($identity, strrpos($identity, '\\') + 1).'/definition.json';
            $definitionPath = base_path($definition);
            if (!is_file($definitionPath) || is_link($definitionPath)) { throw new RuntimeException('Modal native definition missing.'); }
            $definitionData = json_decode(file_get_contents($definitionPath), true, flags: JSON_THROW_ON_ERROR);
            if (($definitionData['seeder']['class'] ?? null) !== $identity || ($definitionData['slug'] ?? null) !== $topic
                || ($definitionData['page']['locale'] ?? null) !== 'uk' || ($definitionData['page']['category']['slug'] ?? null) !== 'modal-verbs'
                || ($definitionData['page']['blocks'][$slot]['body'] ?? null) !== $source['body']) {
                throw new RuntimeException('Modal canonical definition and accepted source differ.');
            }
            $targets[] = ['topic' => $topic, 'local_url' => 'http://gramlyze.loc/theory/modal-verbs/'.$topic,
                'identity' => $identity, 'provider' => $provider, 'source' => $provider::SOURCE, 'source_sha256' => $provider::SOURCE_SHA,
                'definition' => $definition, 'definition_sha256' => hash_file('sha256', $definitionPath),
                'binding' => $binding, 'source_index' => $slot, 'design' => $presentation['m42_native_design'],
                'before_data' => $render, 'after_data' => $render, 'before_data_sha256' => self::digest($render),
                'after_data_sha256' => self::digest($render), 'counts' => $counts];
        }
        if (count($targets) !== 10 || array_sum(array_map(static fn ($target) => array_sum($target['counts']), $targets)) !== 64) {
            throw new RuntimeException('Modal practice finite scope differs.');
        }
        return $targets;
    }

    private static function manifest(array $targets): array
    {
        foreach ($targets as &$target) { unset($target['before_data'], $target['after_data']); } unset($target);
        return ['schema_version' => 1, 'purpose' => 'Exact accepted Modal Verbs practice metadata; no question, answer or design mutation.', 'targets' => $targets];
    }

    /** Pure regeneration input for the reviewed metadata manifest. */
    public static function compile(): array
    {
        return self::manifest(self::deriveTargets());
    }

    public static function validate(array $manifest): void
    {
        if ($manifest !== self::compile()) { throw new RuntimeException('Modal practice manifest differs from accepted sources.'); }
    }

    /** SUPPLEMENT-compatible interface; before_data and after_data are deliberately identical. */
    public static function load(): array
    {
        $path = base_path(self::SOURCE);
        if (!is_file($path) || is_link($path) || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $path))) {
            throw new RuntimeException('Modal practice source manifest differs.');
        }
        static $document;
        if (!isset($document)) {
            $manifest = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            $targets = self::deriveTargets();
            if ($manifest !== self::manifest($targets)) { throw new RuntimeException('Modal practice source bindings differ.'); }
            $document = ['schema_version' => 1, 'targets' => $targets];
        }
        return $document;
    }
}
