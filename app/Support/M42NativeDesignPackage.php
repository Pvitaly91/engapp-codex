<?php

namespace App\Support;

use RuntimeException;

/** Presentation-only metadata for the exact accepted M11–M24 owner set. */
final class M42NativeDesignPackage
{
    public const REGISTRY = 'database/content-patches/m42-native-design-registry.v1.json';
    public const REGISTRY_SHA = '49f1cab8eebaa4c12216ba4df96ed6e8ba875fcd3b7d21fceced2e07630e09b6';
    public const SOURCE = 'database/content-patches/m42-native-design.v1.json';
    public const SOURCE_SHA = 'fea2c1d8999a237e58eb039579d4e20b23b867439bd29256f3ba79d216905ec0';
    public const REFERENCE_SHA = '053c6ddbe4b42b23f724ec336fdf472b6d219215';

    private const PACKAGES = [
        27 => M27LinkingWordsPackage::class, 28 => M28EmphasisPackage::class,
        29 => M29SentenceStructurePackage::class, 30 => M30ParticipleClausesPackage::class,
        31 => M31ConditionalsPackage::class, 32 => M32FormalEnglishPackage::class,
        33 => M33AcademicEnglishPackage::class, 34 => M34ArgumentationCohesionPackage::class,
        35 => M35PassiveReportingPackage::class, 36 => M36ModalsSubjunctivePackage::class,
        37 => M37GrammarStructuresPackage::class, 38 => M38ArticlesCollocationsPackage::class,
        39 => M39AuthoredRevisionPackage::class, 40 => M40TensesB1Package::class,
    ];

    /**
     * Finite editorial palette decisions in accepted source order.
     * B=core explanation; E=constructive application; S=contrast/comparison;
     * A=caution/limits; R=explicit mistake warning (not a false correction icon);
     * N=unchanged hero or navigation. This is not title/arrow/length inference.
     */
    private const PALETTES = [
        'linking-words-reason-result-contrast' => 'NBSBESABN',
        'advanced-linking-devices' => 'NBSEBAERBN',
        'concessive-and-contrastive-structures' => 'NBSSSAARBN',
        'cleft-sentences-basics' => 'NSEEABBN',
        'inversion-basics' => 'NBSEEAABN',
        'advanced-fronting-and-emphasis' => 'NSEBEAE BN',
        'cleft-sentences-emphasis' => 'NSSBEAERBN',
        'complex-noun-phrases' => 'NBSEBEAABN',
        'ellipsis-substitution-and-reference' => 'NSEBSEAAEBN',
        'participle-clauses-basics' => 'NBESERA BN',
        'participle-clauses' => 'NBS ESAEBE',
        'advanced-participle-and-absolute-clauses' => 'NBSAAAA BE',
        'conditionals-with-unless-provided-as-long-as' => 'NBSAEEEBE',
        'advanced-conditionals' => 'NBSBSEABE',
        'conditional-alternatives-and-nuance' => 'NBEASAE BE',
        'formal-register-and-nominalisation-basics' => 'NSEEAESBE',
        'nominalisation-formal-register' => 'NBESAEABE',
        'register-tone-and-paraphrase' => 'NBSEESRBE',
        'hedging-and-cautious-language-basics' => 'NBESASABE',
        'hedging-and-cautious-language' => 'NBSAAEABE',
        'stance-register-and-evaluation' => 'NBSA EEABE',
        'argumentation-and-academic-tone' => 'NSEASEABE',
        'discourse-markers-and-cohesion' => 'NBSSE EABE',
        'paraphrase-and-reformulation' => 'NSEE ASEBE',
        'passive-reporting-structures' => 'NBSAAR EBE',
        'complex-passive-and-causative' => 'NBSSEA SBE',
        'complex-passive-impersonal-style' => 'NBSAA AE BE',
        'modal-perfect-and-deduction' => 'NBSSA ARBE',
        'subjunctive-and-formal-structures' => 'NBESA SEBE',
        'subtle-modal-meanings' => 'NBSES ERBE',
        'advanced-gerund-infinitive-patterns' => 'NBS ESEABE',
        'complex-relative-clauses' => 'NBES SSA BE',
        'inversion-after-negative-adverbials' => 'NBSE SAE BE',
        'advanced-article-and-quantifier-nuance' => 'NBSE EAE BE',
        'precision-with-articles-and-determiners' => 'NBSE SAE BE',
        'advanced-collocation-and-lexical-choice' => 'NBSE AEE BE',
        'nominal-style-and-information-density' => 'NBE SEEE BE',
        'c1-mixed-revision' => 'NSEEEA EBE',
        'c2-mixed-revision' => 'NBSS ASEBE',
        'present-perfect-vs-present-perfect-continuous' => 'NSSEREENSEB',
        'narrative-tenses' => 'NS SERNEAB',
        'b1-mixed-revision' => 'NESNEAB',
    ];

    /** Four reviewed mixed-language formula placeholders, not English example prose. */
    private const EM_EXCEPTIONS = [
        'advanced-linking-devices' => [5 => ['/sections/0/description' => [
            0 => '275cd6391e4f47244b75800f8601f96269804c26241e33bd2dc2d81eed64f151',
            1 => '27c01a4afec60a3d09daeb7fa54d533117f96d53b89b2f1c21e3b64ce9976a88',
        ]]],
        'ellipsis-substitution-and-reference' => [3 => ['/sections/3/description' => [
            2 => '71b9d54d2cae43921a170ad800e2b3d056e5d9874c45bff6796005be3012de29',
        ]]],
        'c2-mixed-revision' => [2 => ['/sections/4/description' => [
            2 => 'f7255030c16385c3cfab16880ccef3e8dbd0bba741ad28f450b84b65d9e8e7b1',
        ]]],
    ];

    /** Exact English selectors in sixteen accepted mixed fields; no dash/language inference. */
    private const MIXED_FIELDS = [
        'present-perfect-vs-present-perfect-continuous' => [
            2 => [
                '/items/0/subtitle' => 'She has labelled the boxes.',
                '/items/1/subtitle' => 'She has not labelled the boxes yet.',
                '/items/2/subtitle' => 'Have you labelled the boxes?',
                '/items/3/subtitle' => 'She has been labelling boxes for an hour.',
                '/items/4/subtitle' => 'She has not been sleeping well lately.',
                '/items/5/subtitle' => 'How long have you been labelling boxes?',
            ],
            4 => [
                '/items/0/right' => '✅ He has checked the list.',
                '/items/1/right' => '✅ I have known Vera since April.',
                '/items/2/right' => '✅ How long have they been waiting?',
                '/items/3/right' => '✅ We have been packing for two hours.',
            ],
        ],
        'narrative-tenses' => [
            2 => [
                '/items/0/subtitle' => 'She opened the gate. / She did not open the gate. / Did she open the gate?',
                '/items/1/subtitle' => 'They were waiting. / They were not waiting. / Were they waiting?',
                '/items/2/subtitle' => 'She had left. / She had not left. / Had she left?',
            ],
            4 => [
                '/items/0/right' => '✅ Did Marta open the cupboard?',
                '/items/1/right' => '✅ The visitors were waiting when we arrived.',
                '/items/2/right' => '✅ The guide had gone home before I called.',
            ],
        ],
    ];

    public static function bytes(array $value): string
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    }

    public static function registry(?string $root = null): array
    {
        $root ??= base_path();
        $bytes = file_get_contents($root.'/'.self::REGISTRY);
        if (!hash_equals(self::REGISTRY_SHA, hash('sha256', $bytes))) {
            throw new RuntimeException('M42 exact 42-owner registry differs.');
        }
        return json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    }

    /** Accepted payload references only. Frozen files are read, never changed. */
    public static function sourceTargets(?string $root = null): array
    {
        $root ??= base_path(); $out = [];
        static $cache = [];
        $signature = self::sourceSignature($root);
        if (isset($cache[$root][$signature])) { return $cache[$root][$signature]; }
        foreach (self::PACKAGES as $stage => $class) {
            [, $package] = $class::load($root);
            $overlay = $stage === 39 ? M39PracticeUiPackage::load($root) : null;
            foreach ($package['targets'] as $owner => $target) {
                if ($overlay !== null) {
                    if ($overlay['targets'][$owner]['identity'] !== $target['identity']
                        || $overlay['targets'][$owner]['before'] !== $target['after']) {
                        throw new RuntimeException('M42 accepted M39 UI overlay owner differs.');
                    }
                    $target['after'] = $overlay['targets'][$owner]['after'];
                }
                if (isset($out[$target['identity']])) { throw new RuntimeException('M42 duplicate source identity.'); }
                $target['package_class'] = $class; $target['package_number'] = $stage;
                $out[$target['identity']] = $target;
            }
        }
        if (count($out) !== 42) { throw new RuntimeException('M42 source count is not 42.'); }
        $cache[$root] = [$signature => $out];
        return $out;
    }

    /** Request-local memoization is invalidated by every protected source-file byte. */
    private static function sourceSignature(string $root): string
    {
        $hashes = [];
        foreach (self::PACKAGES as $class) {
            foreach (['BEFORE', 'SOURCE', 'MASTER_PATH', 'NOTES_PATH'] as $name) {
                if (!defined($class.'::'.$name)) { continue; }
                $path = $root.'/'.constant($class.'::'.$name);
                $hashes[] = is_file($path) ? hash_file('sha256', $path) : 'absent';
            }
        }
        $hashes[] = hash_file('sha256', $root.'/'.M39PracticeUiPackage::SOURCE);
        return hash('sha256', implode('|', $hashes));
    }

    /** No teaching strings or fragments are substituted by this metadata projection. */
    public static function build(?string $root = null): array
    {
        $registry = self::registry($root); $targets = self::sourceTargets($root);
        $colors = ['B' => 'blue', 'E' => 'emerald', 'S' => 'sky', 'A' => 'amber', 'R' => 'rose', 'N' => null];
        $roles = ['B' => 'core-explanation', 'E' => 'constructive-application', 'S' => 'comparison',
            'A' => 'caution-or-limit', 'R' => 'source-explicit-mistake-warning', 'N' => 'preserve-existing'];
        $mapping = ['schema' => 'gramlyze.m42.native-design.v1', 'reference_sha' => self::REFERENCE_SHA,
            'registry_sha256' => self::REGISTRY_SHA, 'targets' => []];
        foreach ($registry['targets'] as $record) {
            $target = $targets[$record['identity']]; $sourceBlocks = $target['after']['page']['blocks'];
            $palette = str_replace(' ', '', self::PALETTES[$record['slug']] ?? '');
            if (strlen($palette) !== count($sourceBlocks)) {
                throw new RuntimeException('M42 finite color scope differs: '.$record['slug'].' ('.strlen($palette).'/'.count($sourceBlocks).')');
            }
            $item = ['identity' => $record['identity'], 'slug' => $record['slug'], 'locale' => 'uk', 'blocks' => []];
            foreach ($sourceBlocks as $slot => $block) {
                $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
                $choice = $palette[$slot]; $color = $colors[$choice];
                if (!array_key_exists($choice, $colors)) { throw new RuntimeException('M42 unknown finite palette choice.'); }
                $sectionColors = [];
                foreach ($data['sections'] ?? [] as $section) {
                    $sourceColor = $section['color'] ?? null;
                    $sectionColors[] = in_array($sourceColor, ['blue', 'emerald', 'sky', 'amber', 'rose'], true) ? $sourceColor : $color;
                }
                $richFields = []; $exceptions = self::EM_EXCEPTIONS[$record['slug']][$slot] ?? [];
                if ($block['type'] !== 'practice-set') {
                    $fieldStrings = self::richFieldStrings($data);
                    $sourcePlan = $target['plans'][$slot - 1] ?? null;
                    foreach ($sourcePlan['points'] ?? [] as $pointIndex => $point) {
                        if (($point['detail'] ?? '') === '') { continue; }
                        $fragmentId = 'block-'.$sourcePlan['key'].'-point-'.($pointIndex + 1).'-detail';
                        // The exact existing fragment remains owned by its point;
                        // this key is a render-only annotation selector, not a new anchor.
                        $fieldStrings['/details/'.$fragmentId] = $point['detail'];
                    }
                    foreach ($fieldStrings as $pointer => $html) {
                        $variants = [$html];
                        if (preg_match('~^/sections/(\d+)/description$~', $pointer, $match)
                            && isset($target['plans'][$slot - 1]['points'][(int) $match[1]])) {
                            $point = $target['plans'][$slot - 1]['points'][(int) $match[1]];
                            if (($point['detail'] ?? '') !== '') { $variants[] = $point['basic']; }
                        }
                        $richFields[$pointer] = [];
                        foreach (array_unique($variants) as $variant) {
                            preg_match_all('~<em\b[^>]*>(.*?)</em>~su', $variant, $matches);
                            $ukIndices = [];
                            foreach ($exceptions[$pointer] ?? [] as $emIndex => $expectedHash) {
                                if (!isset($matches[1][$emIndex]) || hash('sha256', $matches[1][$emIndex]) !== $expectedHash) {
                                    throw new RuntimeException('M42 finite English-em exception source differs.');
                                }
                                $ukIndices[] = $emIndex;
                            }
                            $richFields[$pointer][] = ['sha256' => hash('sha256', $variant),
                                'em_count' => count($matches[0]), 'uk_em_indices' => $ukIndices];
                        }
                    }
                }
                $mixedFields = [];
                foreach (self::MIXED_FIELDS[$record['slug']][$slot] ?? [] as $pointer => $english) {
                    $field = $data;
                    foreach (explode('/', ltrim($pointer, '/')) as $part) { $field = $field[$part]; }
                    if (!is_string($field) || str_contains($field, '<') || substr_count($field, $english) !== 1) {
                        throw new RuntimeException('M42 exact mixed-field English selector differs.');
                    }
                    $start = strpos($field, $english); $end = $start + strlen($english); $ranges = [];
                    if ($start > 0) { $ranges[] = ['role' => $block['type'] === 'forms-grid' ? 'form-context' : 'plain',
                        'start_bytes' => 0, 'length_bytes' => $start]; }
                    $ranges[] = ['role' => 'en', 'start_bytes' => $start, 'length_bytes' => strlen($english)];
                    if ($end >= strlen($field)) { throw new RuntimeException('M42 mixed field is missing its accepted translation.'); }
                    $ranges[] = ['role' => 'translation', 'start_bytes' => $end, 'length_bytes' => strlen($field) - $end];
                    $mixedFields[$pointer] = ['source_sha256' => hash('sha256', $field), 'ranges' => $ranges];
                    self::mixedFragments($field, $mixedFields[$pointer]);
                }
                $item['blocks'][] = ['source_index' => $slot, 'uuid' => M26DetailPackage::uuid($record['identity'], $block, $slot + 1),
                    'sort_order' => $slot + 1, 'body_sha256' => hash('sha256', $block['body']),
                    'component' => $block['type'], 'color' => $color, 'semantic_role' => $roles[$choice],
                    'section_colors' => $sectionColors, 'rich_fields' => $richFields,
                    'rich_em_exceptions' => $exceptions, 'rich_mixed_fields' => $mixedFields];
            }
            $mapping['targets'][] = $item;
        }
        return $mapping;
    }

    /** Field selectors only; every annotation variant is SHA-bound to accepted HTML bytes. */
    private static function richFieldStrings(array $data, string $prefix = ''): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            $pointer = $prefix.'/'.str_replace(['~', '/'], ['~0', '~1'], (string) $key);
            if (is_array($value)) { $out += self::richFieldStrings($value, $pointer); }
            elseif (is_string($value) && str_contains($value, '<em')) { $out[$pointer] = $value; }
        }
        return $out;
    }

    /** Full coverage once, byte-exact order, and no UTF-8 character may be split. */
    public static function mixedFragments(string $source, array $mapping): array
    {
        if (($mapping['source_sha256'] ?? null) !== hash('sha256', $source)) {
            throw new RuntimeException('M42 mixed-field source bytes differ.');
        }
        $out = []; $cursor = 0; $roles = [];
        foreach ($mapping['ranges'] ?? [] as $range) {
            $role = $range['role'] ?? null; $start = $range['start_bytes'] ?? null; $length = $range['length_bytes'] ?? null;
            if (!in_array($role, ['plain', 'form-context', 'en', 'translation'], true) || !is_int($start) || !is_int($length)
                || $start !== $cursor || $length <= 0 || $start + $length > strlen($source)) {
                throw new RuntimeException('M42 mixed ranges must cover the exact ordered source once.');
            }
            $text = substr($source, $start, $length);
            if (!mb_check_encoding($text, 'UTF-8')) { throw new RuntimeException('M42 mixed range splits a UTF-8 character.'); }
            $out[] = ['role' => $role, 'text' => $text]; $roles[] = $role; $cursor += $length;
        }
        if ($cursor !== strlen($source) || implode('', array_column($out, 'text')) !== $source
            || count(array_filter($roles, fn ($role) => $role === 'en')) !== 1
            || count(array_filter($roles, fn ($role) => $role === 'translation')) !== 1) {
            throw new RuntimeException('M42 mixed field loses or duplicates English/translation/source text.');
        }
        return $out;
    }

    /** Add attributes to verified opening em tags; all original text/tag bytes stay intact. */
    public static function richFragment(string $html, ?array $plan, string $pointer): string
    {
        if ($plan === null || (!isset($plan['rich_fields'][$pointer]) && !isset($plan['rich_mixed_fields'][$pointer]))) { return $html; }
        try {
            $verified = false;
            foreach (self::load()['targets'] as $target) {
                foreach ($target['blocks'] as $candidate) {
                    if ($candidate === $plan) { $verified = true; break 2; }
                }
            }
            if (!$verified) { return $html; }
            if (isset($plan['rich_mixed_fields'][$pointer])) {
                $result = '';
                foreach (self::mixedFragments($html, $plan['rich_mixed_fields'][$pointer]) as $fragment) {
                    $text = htmlspecialchars($fragment['text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $result .= match ($fragment['role']) {
                        'en' => '<span class="m42-english" lang="en" data-m42-mixed-role="en">'.$text.'</span>',
                        'translation' => '<span class="m42-paired-translation" lang="uk" data-m42-mixed-role="translation">'.$text.'</span>',
                        'form-context' => '<span class="m42-form-context" lang="uk" data-m42-mixed-role="form-context">'.$text.'</span>',
                        default => $text,
                    };
                }
                return $result;
            }
            $variant = null;
            foreach ($plan['rich_fields'][$pointer] as $candidate) {
                if (hash_equals($candidate['sha256'], hash('sha256', $html))) { $variant = $candidate; break; }
            }
            if ($variant === null) { return $html; }
            $index = -1;
            $result = preg_replace_callback('~<em\b[^>]*>~u', function ($match) use (&$index, $variant) {
                $index++;
                $language = in_array($index, $variant['uk_em_indices'], true) ? 'uk-template' : 'en';
                // Do not replace existing authored class/lang attributes or serialize the DOM.
                $attribute = ' data-m42-em-language="'.$language.'"';
                if ($language === 'en' && !preg_match('~\blang\s*=~u', $match[0])) { $attribute .= ' lang="en"'; }
                return substr($match[0], 0, -1).$attribute.'>';
            }, $html);
            return $index + 1 === $variant['em_count'] && is_string($result) ? $result : $html;
        } catch (\Throwable) { return $html; }
    }

    public static function load(?string $root = null): array
    {
        $root ??= base_path();
        static $cache = [];
        $bytes = file_get_contents($root.'/'.self::SOURCE);
        if (!hash_equals(self::SOURCE_SHA, hash('sha256', $bytes))) {
            throw new RuntimeException('M42 finite native presentation bytes differ.');
        }
        $signature = self::sourceSignature($root).'|'.hash_file('sha256', $root.'/'.self::REGISTRY);
        if (isset($cache[$root][$signature])) { return $cache[$root][$signature]; }
        $mapping = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        if ($mapping !== self::build($root)) { throw new RuntimeException('M42 finite semantic presentation differs.'); }
        $cache[$root] = [$signature => $mapping];
        return $mapping;
    }

    public static function validate(array $mapping, ?string $root = null): void
    {
        if ($mapping !== self::build($root)) { throw new RuntimeException('M42 finite style ownership/order differs.'); }
    }

    /** Rebind one exact UK source block, including hero and original M40 native blocks. */
    public static function binding(object $block, array $data, ?string $root = null): ?array
    {
        if (($block->locale ?? null) !== 'uk') { return null; }
        try {
            $targets = self::sourceTargets($root); $target = $targets[$block->seeder ?? ''] ?? null;
            if ($target === null) { return null; }
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                if (($block->uuid ?? null) !== M26DetailPackage::uuid($target['identity'], $source, $slot + 1)) { continue; }
                if (($block->type ?? null) !== $source['type'] || (int) ($block->sort_order ?? -1) !== $slot + 1
                    || ($block->column ?? null) !== ($source['column'] ?? null)
                    || ($block->heading ?? null) !== ($source['heading'] ?? null)
                    || ($block->level ?? null) !== ($source['level'] ?? null)
                    || ($block->css_class ?? null) !== ($source['css_class'] ?? null)
                    || $data !== json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }
                foreach (self::load($root)['targets'] as $record) {
                    if ($record['identity'] === $target['identity']) { return ['target' => $target, 'plan' => $record['blocks'][$slot]]; }
                }
            }
        } catch (\Throwable) { /* Existing complete native/basic/detail fallback remains available. */ }
        return null;
    }

    /** Existing package fragments/keys/data are untouched; style metadata is top-level only. */
    public static function decorate(object $block, array $data, ?array $presentation, bool $theoryPage = false): ?array
    {
        if (!$theoryPage) { return null; }
        $binding = self::binding($block, $data);
        if ($binding === null) { return null; }
        try {
            $class = $binding['target']['package_class'];
            $expected = $binding['target']['package_number'] === 39
                ? (M39PracticeUiPackage::presentation($block, $data) ?? $class::presentation($block, $data))
                : $class::presentation($block, $data);
            if ($presentation !== $expected) { return null; }
            $result = $presentation ?? ['data' => $data, 'points' => [], 'legacy_section' => null, 'legacy_practice_id' => null];
            // Exact data bytes must remain guard-valid inside practice renderers.
            $result['m42_native_design'] = $binding['plan'];
            return $result;
        } catch (\Throwable) { return null; }
    }
}
