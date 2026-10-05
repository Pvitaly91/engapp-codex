<?php

declare(strict_types=1);

// Source-only public inventory. Composer autoload is not Laravel bootstrap:
// no application, environment/config, models, DB, HTTP or private evidence is read.
$root = dirname(__DIR__, 2);
require_once $root.'/vendor/autoload.php';
require_once $root.'/app/Support/Database/QuestionUuidResolver.php';
require_once $root.'/app/Support/TextBlock/TextBlockUuidGenerator.php';

use App\Support\Database\QuestionUuidResolver;
use App\Support\TextBlock\TextBlockUuidGenerator;

const PPC_INVENTORY_OUTPUT = 'docs/reports/past-perfect-continuous-quality-inventory.json';
const PPC_INVENTORY_COURSE_PROJECTION = 'docs/reports/past-perfect-continuous-quality-course-projection.json';

function ppcInventoryCanonicalText(string $bytes): string
{
    return str_replace("\r\n", "\n", $bytes);
}

function ppcInventorySourceHash(string $path): string
{
    global $root;
    return hash('sha256', ppcInventoryCanonicalText(file_get_contents($root.'/'.$path)));
}

function ppcInventoryRequire(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function ppcInventoryRead(string $path, array &$hashes): array
{
    global $root;
    $raw = file_get_contents($root.'/'.$path);
    ppcInventoryRequire(is_string($raw), 'Unreadable authored source: '.$path);
    $hashes[$path] = hash('sha256', ppcInventoryCanonicalText($raw));
    return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
}

function ppcInventoryDefinitionPath(string $seeder): string
{
    ppcInventoryRequire(str_starts_with($seeder, 'Database\\Seeders\\'), 'Unexpected seeder namespace');
    return 'database/seeders/'.str_replace('\\', '/', substr($seeder, strlen('Database\\Seeders\\'))).'/definition.json';
}

function ppcInventoryRoutes(string $route): array
{
    ppcInventoryRequire(str_starts_with($route, '/'), 'Expected public relative route');
    return ['uk' => $route, 'en' => '/en'.$route, 'pl' => '/pl'.$route];
}

function ppcInventoryBlockUuid(string $seeder, array $definition, int $sortOrder): string
{
    $block = $definition['page']['blocks'][$sortOrder - 1] ?? null;
    ppcInventoryRequire(is_array($block), 'Missing source block: '.$seeder.'#'.$sortOrder);
    $scope = $seeder.'::'.($definition['page']['locale'] ?? 'uk');
    if (trim((string) ($block['uuid'] ?? '')) !== '') {
        return trim($block['uuid']);
    }
    if (trim((string) ($block['uuid_key'] ?? '')) !== '') {
        return TextBlockUuidGenerator::generateWithKey($scope, $block['uuid_key']);
    }
    // JsonPageSeeder counts an authored subtitle before the numbered blocks.
    $index = $sortOrder - 1 + (! empty($definition['page']['subtitle_html']) ? 1 : 0);
    return TextBlockUuidGenerator::generate($scope, $index);
}

function ppcInventoryAliases(string $name, array $aliases, array $bundles, array $stack = []): array
{
    ppcInventoryRequire(preg_match('/^[a-z0-9_]+$/D', $name) === 1, 'Non-normalized finite manifest key');
    if (isset($stack[$name]) && isset($aliases[$name])) {
        return [$name];
    }
    ppcInventoryRequire(! isset($stack[$name]), 'Circular finite bundle: '.$name);
    if (isset($bundles[$name])) {
        $stack[$name] = true;
        $result = [];
        foreach ($bundles[$name] as $child) {
            array_push($result, ...ppcInventoryAliases($child, $aliases, $bundles, $stack));
        }
        return $result;
    }
    ppcInventoryRequire(isset($aliases[$name]), 'Unknown finite alias: '.$name);
    return [$name];
}

function ppcInventoryTarget(array $question): string
{
    if (isset($question['target_text'])) {
        return $question['target_text'];
    }
    $target = preg_replace_callback('/\{([^{}]+)\}/u', static function (array $match) use ($question): string {
        $answer = $question['markers'][$match[1]]['answer'] ?? null;
        ppcInventoryRequire(is_string($answer), 'Missing authored answer marker '.$match[1]);
        return $answer;
    }, $question['question']);
    ppcInventoryRequire(is_string($target) && ! str_contains($target, '{'), 'Incomplete authored target');
    return $target;
}

/** A sanitized observed membership fixture, never a live selector or DB query. */
function ppcInventoryAdditionalProjections(array $records, array &$hashes): array
{
    $source = ppcInventoryRead(PPC_INVENTORY_COURSE_PROJECTION, $hashes);
    ppcInventoryRequire(array_keys($source) === [
        'schema_version', 'membership_basis', 'provenance', 'current_selector_regeneration_claimed',
        'verified_locales', 'verified_modes', 'saved_test_slug', 'theory_page_slug',
        'source_bank_saved_test_slug', 'persistent_question_uuids',
    ], 'Unexpected fields in sanitized course projection');
    ppcInventoryRequire($source['schema_version'] === 1
        && $source['membership_basis'] === 'observed-live-legacy-projection'
        && $source['current_selector_regeneration_claimed'] === false
        && $source['verified_locales'] === ['uk', 'en', 'pl']
        && $source['verified_modes'] === ['choose', 'manual', 'step/manual']
        && is_string($source['provenance']) && trim($source['provenance']) !== '', 'Invalid observed projection provenance');
    $slug = 'course-td-past-perfect-continuous-forms';
    $pageSlug = 'past-perfect-continuous-forms';
    $bankSlug = 'polyglot-past-perfect-continuous-basics-b2';
    ppcInventoryRequire($source['saved_test_slug'] === $slug
        && $source['theory_page_slug'] === $pageSlug
        && $source['source_bank_saved_test_slug'] === $bankSlug, 'Projection leaves the finite course/source scope');
    $blueprintPath = 'database/seeders/V3/Polyglot/Course/theory-driven.json';
    $blueprint = ppcInventoryRead($blueprintPath, $hashes);
    $lessons = array_values(array_filter($blueprint['lessons'], static fn (array $lesson): bool => $lesson['slug'] === $slug));
    ppcInventoryRequire($blueprint['course_slug'] === 'theory-driven' && count($lessons) === 1
        && $lessons[0]['theory_page_slug'] === $pageSlug
        && $lessons[0]['theory_category_slug'] === 'past-perfect-continuous', 'Course blueprint identity mismatch');
    $uuids = $source['persistent_question_uuids'];
    ppcInventoryRequire(is_array($uuids) && array_is_list($uuids) && count($uuids) === 15
        && count(array_unique($uuids)) === 15, 'Observed course membership must contain exactly 15 unique UUIDs');
    $byUuid = array_column($records, null, 'persistent_uuid');
    $questions = [];
    foreach ($uuids as $uuid) {
        ppcInventoryRequire(is_string($uuid) && preg_match('/^[a-zA-Z0-9_-]{1,36}$/D', $uuid) === 1
            && isset($byUuid[$uuid]), 'Observed course UUID is not in the canonical 624-question scope');
        $record = $byUuid[$uuid];
        ppcInventoryRequire($record['source_bank_saved_test_slug'] === $bankSlug
            && $record['bank_family'] === 'Basics' && $record['bank_kind'] === 'Builder'
            && $record['type'] === 4 && $record['level'] === 'B2'
            && $record['theory_page_seeder_class'] === 'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousFormsTheorySeeder',
            'Observed course UUID does not resolve to the canonical Forms-linked Basics B2 owner');
        $questions[] = $record;
    }
    $sorted = $uuids; sort($sorted, SORT_STRING);
    ppcInventoryRequire($uuids === $sorted, 'Observed identifiers must be sorted; no course question order is inferred');
    return [$slug => [
        'kind' => 'existing_saved_test_projection', 'membership_source_path' => PPC_INVENTORY_COURSE_PROJECTION,
        'membership_basis' => $source['membership_basis'], 'provenance' => $source['provenance'],
        'current_selector_regeneration_claimed' => false,
        'course_blueprint_path' => $blueprintPath, 'course_slug' => $blueprint['course_slug'],
        'saved_test_slug' => $slug, 'theory_page_slug' => $pageSlug,
        'routes' => [
            'choose' => ppcInventoryRoutes('/test/'.$slug),
            'manual' => ppcInventoryRoutes('/test/'.$slug.'/manual'),
            'step/manual' => ppcInventoryRoutes('/test/'.$slug.'/step/manual'),
        ],
        'question_count' => count($questions), 'authored_bank_count_contribution' => 0,
        'uuid_order' => 'sorted-identifiers-not-course-question-order',
        'persistent_question_uuids' => $uuids, 'questions' => $questions,
    ]];
}

function ppcInventoryGenerate(): array
{
    global $root;
    $hashes = [];
    foreach ([
        'app/Support/Database/QuestionUuidResolver.php',
        'app/Support/TextBlock/TextBlockUuidGenerator.php',
        'app/Support/Database/JsonPageSeeder.php',
        'app/Support/Database/JsonTheoryLinksSeederBase.php',
        'routes/web.php',
        'tools/diagnostics/generate-ppc-quality-inventory.php',
    ] as $path) {
        $hashes[$path] = ppcInventorySourceHash($path);
    }
    $families = ['Forms' => 'forms', 'Negatives' => 'negatives', 'Questions' => 'questions', 'TimeExpressions' => 'time-expressions'];
    $pages = [];
    foreach ($families as $family => $suffix) {
        $seeder = 'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuous'.$family.'TheorySeeder';
        $definitionPath = ppcInventoryDefinitionPath($seeder);
        $definition = ppcInventoryRead($definitionPath, $hashes);
        $route = '/theory/tenses/past-perfect-continuous/'.$definition['slug'];
        $pages[$seeder] = ['family' => $family, 'definition_path' => $definitionPath, 'routes' => ppcInventoryRoutes($route), 'definition' => $definition];
    }
    $resolver = new QuestionUuidResolver;
    $records = []; $banks = []; $manifests = []; $seenEditorial = []; $seenPersistent = [];
    foreach ($families as $family => $suffix) {
        $manifestPath = 'database/seeders/V3/TheoryLinks/data/past-perfect-continuous-'.$suffix.'-theory-links.json';
        $manifest = ppcInventoryRead($manifestPath, $hashes);
        $pageSeeder = $manifest['page']['page_seeder_class'];
        ppcInventoryRequire(isset($pages[$pageSeeder]) && $manifest['page']['route'] === $pages[$pageSeeder]['routes']['uk'], 'Manifest/source route mismatch');
        $aliases = [];
        foreach ($manifest['theory_text_block_aliases'] as $name => $spec) {
            $owner = $spec['seeder_class'] ?? $pageSeeder;
            ppcInventoryRequire(isset($pages[$owner]), 'Alias crosses finite four-page scope');
            $sort = $spec['sort_order'];
            $aliases[$name] = [
                'alias' => $name, 'page_routes' => $pages[$owner]['routes'], 'page_seeder_class' => $owner,
                'sort_order' => $sort, 'text_block_uuid' => ppcInventoryBlockUuid($owner, $pages[$owner]['definition'], $sort),
            ];
        }
        $testSummaries = [];
        foreach ($manifest['tests_on_page'] as $test) {
            ppcInventoryRequire(($test['strategy'] ?? '') === 'explicit_question_uuid_map', 'Inventory requires exact UUID maps');
            $seeder = $test['seeder_class'];
            $definitionPath = ppcInventoryDefinitionPath($seeder);
            $definition = ppcInventoryRead($definitionPath, $hashes);
            ppcInventoryRequire($definition['seeder']['class'] === $seeder, 'Seeder identity mismatch');
            $externalConditions = []; $externalPaths = [];
            foreach (['uk', 'en', 'pl'] as $locale) {
                $externalPath = dirname($definitionPath).'/localizations/'.$locale.'.json';
                if (! is_file($root.'/'.$externalPath)) {
                    continue;
                }
                $external = ppcInventoryRead($externalPath, $hashes);
                ppcInventoryRequire($external['target']['seeder_class'] === $seeder && $external['locale'] === $locale, 'External localization owner mismatch');
                foreach ($external['questions'] as $localizedQuestion) {
                    ppcInventoryRequire(! isset($externalConditions[$locale][$localizedQuestion['uuid']]), 'Duplicate external source condition');
                    $externalConditions[$locale][$localizedQuestion['uuid']] = $localizedQuestion['source_text'];
                }
                $externalPaths[$locale] = $externalPath;
            }
            $bankKind = $test['kind'] === 'virtual_mixed_test' ? 'Mixed' : 'Builder';
            $bankFamily = $test['kind'] === 'legacy_polyglot_coverage' ? 'Basics' : $family;
            $savedSlug = $definition['saved_test']['slug'];
            $pageSavedSlug = $test['saved_test_slug'] ?? $test['saved_test_slug_pattern'];
            if ($test['kind'] !== 'virtual_mixed_test') {
                ppcInventoryRequire($savedSlug === $pageSavedSlug, 'Direct saved-test slug mismatch');
            }
            $questionUuids = array_column($definition['questions'], 'uuid');
            ppcInventoryRequire(count(array_unique($questionUuids)) === count($questionUuids), 'Duplicate editorial UUID in bank');
            ppcInventoryRequire(array_diff($questionUuids, array_keys($test['question_links'])) === [] && array_diff(array_keys($test['question_links']), $questionUuids) === [], 'Manifest does not cover bank exactly');
            $types = []; $levels = [];
            foreach ($definition['questions'] as $question) {
                $editorialUuid = $question['uuid']; $persistentUuid = $resolver->toPersistent($editorialUuid);
                ppcInventoryRequire(! isset($seenEditorial[$editorialUuid]) && ! isset($seenPersistent[$persistentUuid]), 'Cross-bank UUID collision');
                $seenEditorial[$editorialUuid] = true; $seenPersistent[$persistentUuid] = true;
                $bundleNames = $test['question_links'][$editorialUuid]; $expanded = [];
                foreach ($bundleNames as $bundle) {
                    array_push($expanded, ...ppcInventoryAliases($bundle, $aliases, $manifest['bundles']));
                }
                $aliasNames = array_values(array_unique($expanded)); $orderedUuids = [];
                foreach ($expanded as $alias) {
                    if (! in_array($aliases[$alias]['text_block_uuid'], $orderedUuids, true)) {
                        $orderedUuids[] = $aliases[$alias]['text_block_uuid'];
                    }
                }
                ppcInventoryRequire($orderedUuids !== [], 'Question has no theory links');
                $sourceConditions = [];
                foreach (['uk', 'en', 'pl'] as $locale) {
                    $condition = $question['localizations'][$locale]['source_text'] ?? ($locale === 'uk' ? ($question['source_text_uk'] ?? $question['question']) : null);
                    ppcInventoryRequire(is_string($condition) && trim($condition) !== '', 'Missing localized source condition');
                    if (isset($externalConditions[$locale])) {
                        ppcInventoryRequire(($externalConditions[$locale][$editorialUuid] ?? null) === $condition, 'Inline/external source condition mismatch');
                    }
                    $sourceConditions[$locale] = $condition;
                }
                $type = (int) ($question['type'] ?? $definition['defaults']['type']); $level = $question['level'];
                $types[(string) $type] = ($types[(string) $type] ?? 0) + 1; $levels[$level] = ($levels[$level] ?? 0) + 1;
                $records[] = [
                    'theory_page_routes' => $pages[$pageSeeder]['routes'], 'theory_page_seeder_class' => $pageSeeder,
                    'theory_link_manifest' => $manifestPath, 'bundles' => $bundleNames,
                    'block_aliases' => array_map(static fn (string $name): array => $aliases[$name], $aliasNames),
                    'ordered_theory_text_block_uuids' => $orderedUuids, 'primary_theory_text_block_uuid' => $orderedUuids[0],
                    'page_saved_test_kind' => $test['kind'], 'page_saved_test_slug' => $pageSavedSlug,
                    'page_saved_test_routes' => ppcInventoryRoutes('/test/'.$pageSavedSlug),
                    'source_bank_saved_test_slug' => $savedSlug, 'source_bank_saved_test_routes' => ppcInventoryRoutes('/test/'.$savedSlug),
                    'bank_kind' => $bankKind, 'bank_family' => $bankFamily, 'seeder_class' => $seeder, 'definition_path' => $definitionPath,
                    'editorial_uuid' => $editorialUuid, 'persistent_uuid' => $persistentUuid, 'type' => $type, 'level' => $level,
                    'source_condition' => $sourceConditions, 'question_template' => $question['question'], 'completed_target' => ppcInventoryTarget($question),
                ];
            }
            foreach ($externalConditions as $localizedQuestions) {
                ppcInventoryRequire(count($localizedQuestions) === count($questionUuids), 'External localization coverage mismatch');
            }
            $banks[] = ['kind' => $bankKind, 'family' => $bankFamily, 'seeder_class' => $seeder, 'definition_path' => $definitionPath, 'external_localization_paths' => (object) $externalPaths, 'saved_test_slug' => $savedSlug, 'count' => count($questionUuids), 'types' => (object) $types, 'levels' => (object) $levels];
            $testSummaries[] = ['kind' => $test['kind'], 'page_saved_test_slug' => $pageSavedSlug, 'source_bank_saved_test_slug' => $savedSlug, 'seeder_class' => $seeder, 'question_count' => count($questionUuids)];
        }
        $manifests[] = ['path' => $manifestPath, 'page_routes' => $pages[$pageSeeder]['routes'], 'page_seeder_class' => $pageSeeder, 'block_aliases' => array_values($aliases), 'bundles' => $manifest['bundles'], 'tests_on_page' => $testSummaries];
    }
    ppcInventoryRequire(count($records) === 624 && count($banks) === 9, 'Expected all nine banks / 624 questions');
    $qualityPath = 'database/content-patches/ppc-practice-quality.v2.json';
    $quality = ppcInventoryRead($qualityPath, $hashes); $static = [];
    foreach ($quality['targets'] as $target) {
        $seeder = $target['identity']; $page = $pages[$seeder]; $sort = $target['sort_order'];
        $block = $page['definition']['page']['blocks'][$sort - 1];
        ppcInventoryRequire($block['uuid_key'] === $target['uuid_key'] && json_decode($block['body'], true, 512, JSON_THROW_ON_ERROR) === $target['body_data']['uk'], 'Static definition/package mismatch');
        foreach (['en', 'pl'] as $locale) {
            $localized = ppcInventoryRead(dirname($page['definition_path']).'/localizations/'.$locale.'.json', $hashes);
            $matches = array_values(array_filter($localized['blocks'], static fn (array $item): bool => ($item['uuid_key'] ?? null) === $target['uuid_key']));
            ppcInventoryRequire(count($matches) === 1 && json_decode($matches[0]['body'], true, 512, JSON_THROW_ON_ERROR) === $target['body_data'][$locale], 'Static localization/package mismatch');
        }
        $hero = json_decode($page['definition']['page']['blocks'][0]['body'], true, 512, JSON_THROW_ON_ERROR);
        foreach (['selects' => 'select', 'choices' => 'choice', 'inputs' => 'input'] as $collection => $type) {
            ppcInventoryRequire(count($target['body_data']['uk'][$collection]) === 2, 'Expected two static tasks per type');
            foreach ($target['body_data']['uk'][$collection] as $index => $task) {
                $conditions = []; $expected = []; $variants = [];
                foreach (['uk', 'en', 'pl'] as $locale) {
                    $localizedTask = $target['body_data'][$locale][$collection][$index];
                    $conditions[$locale] = array_intersect_key($localizedTask, array_flip(['label', 'prompt', 'before', 'after']));
                    $expected[$locale] = $localizedTask['answer'];
                    $variants[$locale] = $localizedTask['accepted'] ?? [];
                }
                $static[] = [
                    'theory_page_routes' => $page['routes'], 'page_seeder_class' => $seeder, 'definition_path' => $page['definition_path'],
                    'practice_package_path' => $qualityPath, 'practice_block_uuid_key' => $target['uuid_key'], 'practice_block_uuid' => ppcInventoryBlockUuid($seeder, $page['definition'], $sort),
                    'practice_block_uuids_by_locale' => array_combine(['uk', 'en', 'pl'], array_map(static fn (string $locale): string => TextBlockUuidGenerator::generateWithKey($seeder.'::'.$locale, $target['uuid_key']), ['uk', 'en', 'pl'])),
                    'practice_block_sort_order' => $sort, 'editorial_slot' => $target['uuid_key'].':'.$collection.':'.($index + 1),
                    'persistent_question_uuid' => null, 'saved_test_slug' => null, 'type' => $type, 'level' => null, 'page_display_level' => $hero['level'] ?? null,
                    'source_condition' => $conditions, 'expected_answer' => $expected, 'accepted_answer_variants' => $variants,
                ];
            }
        }
    }
    ppcInventoryRequire(count($static) === 24, 'Expected 24 static tasks');
    $additionalProjections = ppcInventoryAdditionalProjections($records, $hashes);
    ksort($hashes);
    foreach ($hashes as $path => $sha) {
        ppcInventoryRequire(ppcInventorySourceHash($path) === $sha, 'Source changed during generation: '.$path);
    }
    $counts = ['bank_questions' => count($records), 'unique_editorial_uuids' => count($seenEditorial), 'unique_persistent_uuids' => count($seenPersistent), 'Mixed' => 0, 'Builder' => 0, 'static_tasks' => count($static), 'static_pages' => count($quality['targets']), 'by_type' => [], 'by_level' => []];
    foreach ($records as $record) {
        $counts[$record['bank_kind']]++;
        $counts['by_type'][(string) $record['type']] = ($counts['by_type'][(string) $record['type']] ?? 0) + 1;
        $counts['by_level'][$record['level']] = ($counts['by_level'][$record['level']] ?? 0) + 1;
    }
    $counts['by_type'] = (object) $counts['by_type'];
    return [
        'schema_version' => 1, 'scope' => 'past-perfect-continuous-practice-quality-public-source-inventory',
        'generator' => 'tools/diagnostics/generate-ppc-quality-inventory.php',
        'notes' => [
            'Versioned source inputs only; no database IDs, application config, private evidence, HTTP or runtime state are read by this generator.',
            'Mechanical source fingerprints and output comparison use canonical LF text bytes, independent of checkout CRLF conversion. Frozen package byte-level guards are separate and unchanged.',
            'Mixed page-local saved-test slugs retain the manifest {page_id} placeholder. Exact source-bank saved slugs are listed separately; no local ID is inferred.',
            'Theory aliases resolve source base-locale block UUIDs using the real pure TextBlockUuidGenerator and JsonPageSeeder subtitle/key rules. Ordered UUIDs reproduce manifest bundle expansion and first-seen deduplication.',
            'Persistent question UUIDs use the real dependency-free QuestionUuidResolver; all editorial UUIDs are explicit.',
            'Static practice slots are embedded in page block JSON, not questions-table records: persistent question UUID, saved slug and per-question CEFR level are null. Page display level is listed separately.',
            'UK/EN/PL conditions are authored prompts. Completed bank targets remain English; accepted answer variants and explanations are not correctness heuristics.',
            'Additional saved-test projections use an explicit sanitized observed legacy membership fixture joined to the canonical 624 source questions. They are not extra authored banks or a promise of current selector regeneration; their identifiers are sorted, not inferred course question order.',
        ],
        'source_sha256' => (object) $hashes, 'counts' => $counts, 'banks' => $banks, 'theory_link_manifests' => $manifests,
        'questions' => $records, 'static_practice' => $static, 'additional_saved_test_projections' => (object) $additionalProjections,
    ];
}

try {
    $mode = $argv[1] ?? '--check';
    ppcInventoryRequire(count($argv) <= 2 && in_array($mode, ['--write', '--check'], true), 'Usage: php tools/diagnostics/generate-ppc-quality-inventory.php [--write|--check]');
    $inventory = ppcInventoryGenerate();
    $json = json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    $output = $root.'/'.PPC_INVENTORY_OUTPUT;
    if ($mode === '--write') {
        ppcInventoryRequire(is_dir(dirname($output)), 'Versioned reports directory missing');
        ppcInventoryRequire(file_put_contents($output, $json) === strlen($json), 'Unable to write finite inventory');
    } else {
        ppcInventoryRequire(is_file($output) && ppcInventoryCanonicalText(file_get_contents($output)) === $json, 'Inventory is stale; run --write after reviewing authored source changes');
    }
    echo json_encode(['pass' => true, 'mode' => $mode, 'path' => PPC_INVENTORY_OUTPUT, 'counts' => $inventory['counts'], 'sha256' => hash('sha256', $json)], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage()."\n");
    exit(1);
}
