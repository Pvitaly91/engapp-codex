<?php

namespace App\Support;

use App\Models\Question;
use App\Models\TextBlock;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

/** Explicit acceptance gate, never a renderer, selector, startup hook or content writer. */
final class TheorySentenceBuilderAcceptance
{
    public const CONTRACT_DIRECTORY = 'docs/content/theory-sentence-builders';

    private const OWNER_PREFIX = 'Database\\Seeders\\Page_V3\\';

    public static function definitionPath(string $identity): string
    {
        if (!str_starts_with($identity, self::OWNER_PREFIX)
            || preg_match('/^[A-Za-z_][A-Za-z0-9_]*(?:\\\\[A-Za-z_][A-Za-z0-9_]*)*$/D', substr($identity, strlen(self::OWNER_PREFIX))) !== 1) {
            throw new RuntimeException('Sentence-builder contract has an invalid theory owner.');
        }

        return 'database/seeders/Page_V3/'.str_replace('\\', '/', substr($identity, strlen(self::OWNER_PREFIX))).'/definition.json';
    }

    private static function json(string $path): array
    {
        if (!is_file($path) || is_link($path)) {
            throw new RuntimeException('Sentence-builder source is missing or linked: '.basename($path));
        }
        $value = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($value)) {
            throw new RuntimeException('Sentence-builder source must be a JSON object: '.basename($path));
        }

        return $value;
    }

    /** Independent editorial owner/bank bindings, not banks inferred from received questions. */
    public static function contracts(string $root): array
    {
        $contracts = [];
        $files = glob($root.'/'.self::CONTRACT_DIRECTORY.'/*.json') ?: [];
        sort($files);
        foreach ($files as $file) {
            $source = self::json($file);
            if (($source['schema_version'] ?? null) !== 1 || !is_array($source['targets'] ?? null)
                || !array_is_list($source['targets'])) {
                throw new RuntimeException('Sentence-builder contract schema differs: '.basename($file));
            }
            foreach ($source['targets'] as $contract) {
                if (!is_array($contract)) {
                    throw new RuntimeException('Sentence-builder contract target is not an object.');
                }
                $keys = array_keys($contract); sort($keys);
                if ($keys !== ['bank_seeders', 'definition', 'identity', 'level', 'local_url']) {
                    throw new RuntimeException('Sentence-builder contract has unsupported or missing fields.');
                }
                $owner = $contract['identity'];
                if (!is_string($owner) || $contract['definition'] !== self::definitionPath($owner)
                    || !is_string($contract['local_url'])
                    || preg_match('~^http://gramlyze\.loc/theory/[a-z0-9-]+(?:/[a-z0-9-]+)+$~D', $contract['local_url']) !== 1
                    || (!is_string($contract['level']) && $contract['level'] !== null)
                    || !is_array($contract['bank_seeders']) || !array_is_list($contract['bank_seeders'])
                    || $contract['bank_seeders'] === []) {
                    throw new RuntimeException('Sentence-builder contract identity, URL, level or bank differs.');
                }
                foreach ($contract['bank_seeders'] as $bank) {
                    if (!is_string($bank) || preg_match('/^Database\\\\Seeders\\\\[A-Za-z_][A-Za-z0-9_]*(?:\\\\[A-Za-z_][A-Za-z0-9_]*)*$/D', $bank) !== 1) {
                        throw new RuntimeException('Sentence-builder contract bank is invalid.');
                    }
                }
                if (count(array_unique($contract['bank_seeders'])) !== count($contract['bank_seeders'])
                    || isset($contracts[$owner])) {
                    throw new RuntimeException('Sentence-builder contract repeats a bank or owner.');
                }
                $contracts[$owner] = $contract;
            }
        }

        return $contracts;
    }

    /**
     * No M-number filter or count cap. A new versioned AFTER owner is a gate target
     * even if its practice/config/independent bank contract was accidentally omitted.
     * Discovery is a readiness obligation, not a claim that a proposed package is applied.
     */
    public static function discoverTargets(string $root): array
    {
        $targets = [];
        foreach (self::contracts($root) as $owner => $contract) {
            $targets[$owner] = ['identity' => $owner, 'definition' => $contract['definition'],
                'local_url' => $contract['local_url'], 'contract' => $contract, 'sources' => [], 'errors' => []];
        }
        $files = glob($root.'/database/content-patches/*.json') ?: [];
        sort($files);
        foreach ($files as $file) {
            if (preg_match('/(?:^|[-_.])(?:before|draft|preview|planned)(?:[-_.]|$)/i', basename($file)) === 1) {
                continue;
            }
            $source = self::json($file);
            if (in_array(strtolower((string) ($source['status'] ?? '')), ['draft', 'preview', 'planned'], true)) {
                continue;
            }
            if (array_key_exists('targets', $source)
                && (!is_array($source['targets']) || !array_is_list($source['targets']))) {
                throw new RuntimeException('Sentence-builder target registry is malformed: '.basename($file));
            }
            foreach ($source['targets'] ?? [] as $target) {
                if (!is_array($target)) {
                    throw new RuntimeException('Sentence-builder target is not an object: '.basename($file));
                }
                if (!array_key_exists('after', $target)) {
                    // BEFOREs and presentation-only maps do not register new lessons.
                    continue;
                }
                if (!is_array($target['after']) || !is_string($target['identity'] ?? null)
                    || !str_starts_with($target['identity'], self::OWNER_PREFIX)) {
                    throw new RuntimeException('Sentence-builder AFTER owner is malformed: '.basename($file));
                }
                $declaredLocale = $target['after']['page']['locale'] ?? $target['page']['locale'] ?? null;
                if ($declaredLocale !== 'uk') {
                    throw new RuntimeException('Sentence-builder AFTER must declare the accepted UK scope: '.basename($file));
                }
                $owner = $target['identity'];
                $definition = self::definitionPath($owner);
                $targets[$owner] ??= ['identity' => $owner, 'definition' => $definition,
                    // A source payload cannot supply a diagnostic/request URL.
                    // Only a validated independent contract owns this field.
                    'local_url' => null, 'contract' => null, 'sources' => [], 'errors' => []];
                $targets[$owner]['sources'][] = 'database/content-patches/'.basename($file);
                $declared = $target['definition'] ?? $target['path'] ?? $definition;
                if ($declared !== $definition) {
                    $targets[$owner]['errors'][] = 'source_definition_mismatch:'.basename($file);
                }
            }
        }
        foreach ($targets as &$target) {
            if ($target['contract'] === null) {
                $target['errors'][] = 'missing_independent_bank_contract';
            }
            if (!is_file($root.'/'.$target['definition']) || is_link($root.'/'.$target['definition'])) {
                $target['errors'][] = 'missing_definition';
            }
            $target['sources'] = array_values(array_unique($target['sources']));
            $target['errors'] = array_values(array_unique($target['errors']));
        }
        unset($target);

        return array_values($targets);
    }

    /** Read the existing component's literal JSON; never execute JS or read hidden browser state. */
    public static function payload(string $html, string $blockUuid): ?array
    {
        $factory = 'practiceQuestion_practice_'.str_replace('-', '_', Str::slug($blockUuid));
        if (preg_match('/function\s+'.preg_quote($factory, '/').'\(\)\s*\{.*?^\s*questions:\s*(\[[^\r\n]*\]),\s*$/ms', $html, $match) !== 1) {
            return null;
        }
        try {
            $value = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);

            return is_array($value) && array_is_list($value) ? $value : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<meta charset="utf-8">'.$html, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    /** Server/model acceptance only. Browser visibility and interaction remain separate evidence. */
    public static function check(
        array $contract,
        object $page,
        TextBlock $block,
        ?array $effectiveConfig,
        Collection $eligible,
        Collection $selected,
        string $html,
        bool $canonicalTheory = true,
    ): array {
        $errors = [];
        $fail = static function (bool $condition, string $code) use (&$errors): void {
            if (!$condition) { $errors[] = $code; }
        };
        $fail($canonicalTheory, 'noncanonical_caller');
        $fail(app()->getLocale() === 'uk' && $block->locale === 'uk', 'wrong_locale');
        $fail(($page->seeder ?? null) === $contract['identity'] && $block->seeder === $contract['identity'], 'wrong_owner');
        $fail(($page->type ?? null) === 'theory' && $block->type === 'practice-set'
            && $block->page_id === ($page->id ?? null)
            && $block->page_category_id === ($page->page_category_id ?? null), 'wrong_practice_identity');
        $fail($block->level === $contract['level'], 'wrong_practice_level');
        $fail(($effectiveConfig['source'] ?? null) === 'theory_links', 'missing_linked_practice');
        $fail(($effectiveConfig['question_types'] ?? null) === [Question::TYPE_COMPOSE_TOKENS], 'wrong_config_type');
        $actualBanks = $effectiveConfig['seeder_classes'] ?? [];
        $expectedBanks = $contract['bank_seeders'];
        if (!is_array($actualBanks)) { $actualBanks = []; }
        sort($actualBanks); sort($expectedBanks);
        $fail($actualBanks === $expectedBanks, 'wrong_config_bank');
        $fail($eligible->count() >= 5, 'fewer_than_five_eligible');
        $fail($selected->count() === 5, 'wrong_selected_count');
        $ids = $selected->pluck('id')->all();
        $fail(count(array_unique($ids)) === count($ids), 'duplicate_question_id');
        $fail(array_diff($ids, $eligible->pluck('id')->all()) === [], 'selected_not_page_linked_eligible');
        foreach ($selected as $question) {
            $fail((string) $question->type === Question::TYPE_COMPOSE_TOKENS, 'wrong_question_type');
            $fail(in_array($question->seeder, $contract['bank_seeders'], true), 'foreign_question_bank');
            if (preg_match('/^[ABC][12]$/D', (string) $contract['level']) === 1) {
                $fail($question->level === $contract['level'], 'wrong_question_level');
            }
        }
        $xp = self::xpath($html);
        $widgets = $xp->query('//*[@data-sentence-builder]');
        $inside = $xp->query('//*[@id="block-'.(int) $block->id.'"]//*[@data-sentence-builder]');
        $fail($widgets->length === 1, $widgets->length > 1 ? 'duplicate_widget' : 'missing_widget');
        $fail($inside->length === 1, 'widget_not_inside_practice');
        $factory = 'practiceQuestion_practice_'.str_replace('-', '_', Str::slug((string) $block->uuid)).'()';
        $fail($inside->item(0)?->attributes?->getNamedItem('x-data')?->nodeValue === $factory, 'wrong_widget_factory');
        $payload = self::payload($html, (string) $block->uuid);
        $fail($payload !== null, 'missing_widget_payload');
        if ($payload !== null) {
            $payloadIds = array_column($payload, 'id');
            $fail(count($payload) === 5, 'wrong_payload_count');
            $fail($payloadIds === $ids, 'payload_ids_differ_from_matcher');
            $fail(count(array_unique($payloadIds)) === count($payloadIds), 'duplicate_payload_id');
            foreach ($payload as $question) {
                $fail(($question['type'] ?? null) === Question::TYPE_COMPOSE_TOKENS, 'wrong_payload_type');
                $fail(is_string($question['question'] ?? null)
                    && preg_match('/[\x{0400}-\x{04FF}]/u', strip_tags($question['question'])) === 1, 'missing_uk_condition');
                $tokens = $question['correct_tokens'] ?? [];
                $options = $question['options'] ?? [];
                $fail(is_array($tokens) && $tokens !== [] && is_array($options) && $options !== [], 'empty_compose_bank');
                if (is_array($tokens) && is_array($options)) {
                    $fail(array_diff($tokens, $options) === [], 'compose_answer_missing_from_bank');
                }
            }
        }

        return ['ok' => $errors === [], 'errors' => array_values(array_unique($errors)),
            'eligible_count' => $eligible->count(), 'selected_count' => $selected->count(),
            'selected_ids' => $ids, 'selected_types' => $selected->pluck('type')->map(static fn ($type) => (string) $type)->all(),
            'widget_count' => $widgets->length, 'practice_widget_count' => $inside->length,
            'payload' => $payload, 'browser_visibility' => 'not_checked', 'interaction' => 'not_checked'];
    }
}
