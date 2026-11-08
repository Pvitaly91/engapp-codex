<?php
declare(strict_types=1);

// Explicit local read-only acceptance. Never applies content, seeds questions or changes test filters.
$mode = $argv[1] ?? ''; $retryName = $argv[2] ?? '';
$argumentsValid = ($mode === 'run' && count($argv) === 2) || ($mode === 'retry' && count($argv) === 3
    && preg_match('/^mixed-http-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$/D', $retryName) === 1);
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !$argumentsValid
    || strcasecmp(str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2))), 'D:/DEV/htdocs/gramlyze.loc') !== 0) {
    fwrite(STDERR, "Usage: php tools/diagnostics/finite-builder-word-slots-mixed-http.php run | retry <private-receipt-basename>\n");
    exit(2);
}
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $error): void {
    fwrite(STDERR, json_encode(['status'=>'failed', 'stage'=>$GLOBALS['mixed_audit_stage'] ?? 'startup',
        'exception'=>get_class($error)], JSON_THROW_ON_ERROR)."\n");
    exit(1);
});
App\Services\M49LocalTargetGuard::assertConfiguredDatabase(Illuminate\Support\Facades\DB::connection());
config(['cache.default'=>'array']);
$app->setLocale('uk');

function mixedWordRequire(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException($message); }
}

function mixedWordDefinition(string $path): array
{
    $file = base_path($path);
    mixedWordRequire(is_file($file) && !is_link($file), 'Plain canonical definition required.');
    return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
}

/** Check the existing three-format distribution against this bank's actual authored levels. */
function mixedWordRows(array $rows, array $expected, array $expectedCounts): array
{
    $ids = array_column($rows, 'uuid'); $want = array_keys($expected); $actual = $ids;
    sort($actual); sort($want);
    mixedWordRequire(count($ids) === count($rows) && count($ids) === count(array_unique($ids)) && $actual === $want,
        'Exact mixed question inventory differs.');
    $counts = []; $seenLevels = []; $previousLevel = null; $previousFormat = null; $builders = 0; $slots = 0; $repeatedFormats = 0;
    foreach ($rows as $row) {
        $source = $expected[$row['uuid']]; $expectedFormat = $source['expected_format'];
        unset($source['expected_format']); $level = $row['level'] ?? '';
        mixedWordRequire($level === $source['level'], 'Question level differs.');
        $format = ($row['presentation'] ?? '') === 'sentence_reorder' ? 'order'
            : ((string) ($row['type'] ?? '') === '4' ? 'compose' : 'gap');
        if ($level !== $previousLevel) {
            mixedWordRequire(!isset($seenLevels[$level]), 'A completed level reappears.');
            $seenLevels[$level] = true; $previousFormat = null;
        }
        mixedWordRequire($format === $expectedFormat, 'Existing source-bound format differs.');
        if ($format === $previousFormat) { ++$repeatedFormats; }
        $previousLevel = $level; $previousFormat = $format;
        $counts[$level][$format] = ($counts[$level][$format] ?? 0) + 1;
        if ((string) $source['type'] !== '4') { continue; }
        ++$builders;
        $answers = array_column($source['markers'], 'answer');
        $revision = hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $policy = $row['finite_word_slots'] ?? null;
        mixedWordRequire($format === 'compose' && ($row['question'] ?? null) === $source['question']
            && ($row['question'] ?? null) === $source['translation_uk']
            && !preg_match('/<[^>]+>|\{a\d+\}|&(?:#\d+|[a-z]+);/iu', $row['question']),
            'Builder sentence-only prompt differs.');
        mixedWordRequire(($row['answer_map'] ?? null) === array_map(static fn ($marker) => $marker['answer'], $source['markers'])
            && ($row['compose_accepted_sentences'] ?? null) === $source['accepted_target_texts']
            && ($row['psp_content_revision'] ?? null) === $revision,
            'Builder answers, accepted sentences or exact source revision differ.');
        mixedWordRequire(is_array($policy) && ($policy['schema'] ?? null) === 1
            && ($policy['uuid'] ?? null) === $source['uuid'] && ($policy['revision'] ?? null) === $revision
            && ($policy['question'] ?? null) === $source['question'] && ($policy['answers'] ?? null) === $answers
            && ($policy['sentences'] ?? null) === $source['accepted_target_texts']
            && ($policy['options_by_marker'] ?? null) === $source['options_by_marker'],
            'Exact-bound word-path policy differs.');
        // The legacy formatter keeps the raw canonical answer (for example
        // "bike.") beside its clean option. The exact finite client policy
        // normalizes/deduplicates that compatibility representation. Shared
        // options also use case/accent-insensitive matching, as in the existing
        // PassivePracticeQuality decorator (Sea/sea and café/cafe).
        $publicChoices = $row['options_by_marker'] ?? null;
        $optionKey = static fn ($value) => mb_strtolower(Illuminate\Support\Str::ascii(App\Support\FiniteBuilderWordSlots::clean($value)));
        $normalizedChoices = is_array($publicChoices) ? array_map(static fn ($values) => is_array($values)
            ? array_values(array_unique(array_map($optionKey, $values))) : null,
            array_values($publicChoices)) : null;
        $expectedChoices = array_map(static fn ($values) => array_map($optionKey, $values), array_values($source['options_by_marker']));
        mixedWordRequire($normalizedChoices === $expectedChoices,
            'Public word choices differ from their canonical projection.');
        foreach ($source['markers'] as $marker) {
            ++$slots;
            mixedWordRequire(is_string($marker['answer']) && !preg_match('/\s/u', $marker['answer']), 'A builder field contains several words.');
            mixedWordRequire(count($marker['options']) === 4 && count(array_unique($marker['options'])) === 4,
                'Four distinct word choices required.');
            foreach ($marker['options'] as $option) {
                mixedWordRequire(is_string($option) && $option !== '' && !preg_match('/\s|[<>{}]/u', $option)
                    && $option === App\Support\FiniteBuilderWordSlots::clean($option), 'Unclean or multiword option.');
            }
        }
    }
    foreach ($counts as &$levelCounts) { ksort($levelCounts); } unset($levelCounts);
    ksort($counts); ksort($expectedCounts);
    mixedWordRequire($counts === $expectedCounts, 'Actual source-level three-format counts differ.');
    return ['questions'=>count($rows), 'builders'=>$builders, 'word_slots'=>$slots, 'levels'=>$counts,
        'adjacent_same_format'=>$repeatedFormats,
        'question_order_sha256'=>hash('sha256', implode('|', $ids))];
}

$plans = []; $seenSlugs = [];
$db = Illuminate\Support\Facades\DB::connection();
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    foreach (App\Support\FinitePracticeQualityCatalogs::CATALOGS as $catalog) {
        foreach (array_keys($catalog::TOPICS) as $topic) {
            $GLOBALS['mixed_audit_stage'] = 'mapping:'.$catalog.'/'.$topic;
            $mapping = $catalog::mapping($topic);
            $original = $catalog::originalDefinition($topic, 'standard');
            $owner = $original['saved_test']['filters']['prompt_generator']['theory_page']['page_seeder_class'] ?? null;
            mixedWordRequire(is_string($owner) && $owner !== '', 'Canonical theory owner missing.');
            $pages = App\Models\Page::with('category')->where('seeder', $owner)->get();
            mixedWordRequire($pages->count() === 1 && $pages->first()->category !== null, 'Unique theory page/category binding required.');
            $page = $pages->first();
            $testUuids = [$mapping['standard']['old_saved_test_uuid'], $mapping['builder']['old_saved_test_uuid']];
            mixedWordRequire(App\Models\SavedGrammarTest::whereIn('uuid', $testUuids)->count() === 2,
                'Both persistent source tests must exist.');
            $slug = App\Support\TheoryPageTestSlug::forPage($page);
            mixedWordRequire(preg_match('~^[a-z0-9/-]+$~D', $slug) === 1 && !isset($seenSlugs[$slug]), 'Unique safe mixed slug required.');
            $seenSlugs[$slug] = true; $expected = []; $projectionRows = [];
            foreach (['standard','builder'] as $kind) {
                $definition = mixedWordDefinition($mapping[$kind]['definition']);
                mixedWordRequire(($definition['seeder']['class'] ?? null) === $mapping[$kind]['class'], 'Canonical bank identity differs.');
                foreach ($definition['questions'] as $row) {
                    mixedWordRequire(!isset($expected[$row['uuid']]), 'Duplicate canonical question UUID.');
                    $expected[$row['uuid']] = $row;
                    $projectionRows[] = ['uuid'=>$row['uuid'], 'question'=>$row['question'], 'type'=>$row['type'],
                        'level'=>$row['level'], 'answer_map'=>array_map(static fn ($marker) => $marker['answer'], $row['markers']),
                        'reorder_groups'=>$row['token_groups'] ?? [],
                        'psp_content_revision'=>hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))];
                }
            }
            // Three historical banks contain extra builders. The mixed selector
            // deliberately takes the existing per-level quota in database-ID order.
            $databaseRows = App\Models\Question::whereIn('uuid', array_keys($expected))->orderBy('id')
                ->get(['id','uuid','type','level']);
            mixedWordRequire($databaseRows->count() === count($expected), 'Complete canonical database inventory required.');
            $quota = App\Support\SentenceReorderQuestionFactory::compactTypeCounts($catalog::REVISION);
            $selectedIds = []; $taken = [];
            foreach ($databaseRows as $databaseRow) {
                $source = $expected[$databaseRow->uuid]; $level = $source['level']; $type = (string) $source['type'];
                mixedWordRequire((string) $databaseRow->level === $level && (string) $databaseRow->type === $type,
                    'Canonical database level/type differs.');
                if (($taken[$level][$type] ?? 0) < ($quota[$type] ?? 0)) {
                    $selectedIds[$databaseRow->uuid] = true;
                    $taken[$level][$type] = ($taken[$level][$type] ?? 0) + 1;
                }
            }
            $availableBuilders = count(array_filter($expected, static fn ($row) => (string) $row['type'] === '4'));
            $expected = array_intersect_key($expected, $selectedIds);
            $projectionRows = array_values(array_filter($projectionRows, static fn ($row) => isset($selectedIds[$row['uuid']])));
            // Reuse the existing presentation rules, including historically uneven banks.
            // This audit does not impose new counts or introduce any new exercise types.
            $projectionRows = App\Support\SentenceReorderQuestionFactory::addToMixedTheoryTest($projectionRows,
                ['aggregated_theory_page_test'=>true, 'theory_page_mixed_all_levels'=>true,
                    'theory_page_mixed_type_profile'=>$catalog::REVISION]);
            $expectedCounts = [];
            foreach ($projectionRows as $projected) {
                $format = ($projected['presentation'] ?? '') === 'sentence_reorder' ? 'order'
                    : ((string) $projected['type'] === '4' ? 'compose' : 'gap');
                $expected[$projected['uuid']]['expected_format'] = $format;
                $level = $projected['level'];
                $expectedCounts[$level][$format] = ($expectedCounts[$level][$format] ?? 0) + 1;
            }
            foreach ($expectedCounts as &$levelCounts) { ksort($levelCounts); } unset($levelCounts);
            $plans[] = ['catalog'=>$catalog, 'topic'=>$topic, 'page_id'=>(int) $page->id, 'page_owner'=>$owner,
                'source_test_uuids'=>$testUuids, 'url'=>'http://gramlyze.loc/test/'.$slug,
                'available_builders'=>$availableBuilders,
                'expected'=>$expected, 'expected_counts'=>$expectedCounts];
        }
    }
    mixedWordRequire(count($plans) === 193, 'Exact 193 related mixed tests required.');
} finally { $db->rollBack(); }

$receipt = ['status'=>'verified', 'banks'=>[], 'failures'=>[], 'builders'=>0, 'questions'=>0, 'database_writes'=>0];
if ($mode === 'retry') {
    $priorPath = base_path('storage/app/finite-builder-word-slots/'.$retryName);
    mixedWordRequire(is_file($priorPath) && !is_link($priorPath), 'Plain private earlier receipt required.');
    $priorBytes = file_get_contents($priorPath);
    $prior = json_decode($priorBytes, true, flags: JSON_THROW_ON_ERROR);
    mixedWordRequire(($prior['status'] ?? null) === 'failed' && ($prior['database_writes'] ?? null) === 0
        && count($prior['banks'] ?? []) + count($prior['failures'] ?? []) === 193, 'Complete earlier failed audit required.');
    $byIdentity = []; foreach ($plans as $plan) { $byIdentity[$plan['catalog'].'|'.$plan['topic']] = $plan; }
    $seen = [];
    foreach (array_merge($prior['banks'], $prior['failures']) as $previous) {
        $key = $previous['catalog'].'|'.$previous['topic'];
        mixedWordRequire(isset($byIdentity[$key]) && !isset($seen[$key]) && $previous['url'] === $byIdentity[$key]['url'],
            'Earlier receipt catalog/topic/URL scope differs.');
        $seen[$key] = true;
    }
    foreach ($prior['banks'] as $previous) {
        $key = $previous['catalog'].'|'.$previous['topic']; $plan = $byIdentity[$key];
        $builderCount = count(array_filter($plan['expected'], static fn ($row) => (string) $row['type'] === '4'));
        mixedWordRequire(($previous['status'] ?? null) === 200 && $previous['source_test_uuids'] === $plan['source_test_uuids']
            && $previous['page_id'] === $plan['page_id'] && $previous['page_owner'] === $plan['page_owner']
            && $previous['questions'] === count($plan['expected']) && $previous['builders'] === $builderCount
            && preg_match('/^[a-f0-9]{64}$/D', $previous['response_sha256'] ?? '') === 1, 'Earlier successful scope differs.');
        $receipt['banks'][] = $previous;
        $receipt['builders'] += $previous['builders']; $receipt['questions'] += $previous['questions'];
        unset($byIdentity[$key]);
    }
    $receipt['retry_of'] = ['basename'=>$retryName, 'sha256'=>hash('sha256', $priorBytes),
        'retained_verified_banks'=>count($receipt['banks']), 'previous_failures'=>$prior['failures']];
    $plans = array_values($byIdentity);
}
foreach ($plans as $plan) {
    $GLOBALS['mixed_audit_stage'] = $plan['catalog'].'/'.$plan['topic'];
    $failureReason = null;
    try {
        $curl = curl_init($plan['url'].'/questions');
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>false, CURLOPT_PROXY=>'',
            CURLOPT_RESOLVE=>['gramlyze.loc:80:127.0.0.1'], CURLOPT_CONNECTTIMEOUT=>4, CURLOPT_TIMEOUT=>60,
            CURLOPT_HTTPHEADER=>['Accept: application/json']]);
        $body = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $local = curl_getinfo($curl, CURLINFO_PRIMARY_IP) === '127.0.0.1'; curl_close($curl);
        mixedWordRequire($status === 200 && $local && is_string($body), 'Pinned local JSON GET failed.');
        $payload = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        mixedWordRequire(is_array($payload['questions'] ?? null), 'Actual questions endpoint schema differs.');
        $result = mixedWordRows($payload['questions'], $plan['expected'], $plan['expected_counts']);
        $receipt['banks'][] = array_intersect_key($plan, array_flip(['catalog','topic','page_id','page_owner','source_test_uuids','url','available_builders']))
            + $result + ['status'=>$status, 'response_sha256'=>hash('sha256', $body)];
        $receipt['builders'] += $result['builders']; $receipt['questions'] += $result['questions'];
    } catch (Throwable $error) {
        $failureReason = $error instanceof RuntimeException ? $error->getMessage() : get_class($error);
        $receipt['failures'][] = ['catalog'=>$plan['catalog'], 'topic'=>$plan['topic'], 'url'=>$plan['url'],
            'reason'=>$failureReason];
    }
    echo json_encode(['completed'=>count($receipt['banks']) + count($receipt['failures']), 'topic'=>$plan['topic'],
        'failures'=>count($receipt['failures']), 'failure'=>$failureReason], JSON_THROW_ON_ERROR)."\n";
}
$receipt['status'] = $receipt['failures'] === [] && count($receipt['banks']) === 193 ? 'verified' : 'failed';
$directory = base_path('storage/app/finite-builder-word-slots');
mixedWordRequire(is_dir($directory) && !is_link($directory), 'Existing private receipt directory required.');
$path = $directory.'/mixed-http-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json';
$stream = fopen($path, 'x');
mixedWordRequire($stream !== false, 'Exclusive receipt unavailable.');
try {
    $json = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    mixedWordRequire(fwrite($stream, $json) === strlen($json) && fflush($stream), 'Receipt write incomplete.');
} finally { fclose($stream); }
echo json_encode(['status'=>$receipt['status'], 'banks'=>count($receipt['banks']), 'builders'=>$receipt['builders'],
    'questions'=>$receipt['questions'], 'failures'=>count($receipt['failures']), 'receipt'=>$path,
    'database_writes'=>0], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
exit($receipt['status'] === 'verified' ? 0 : 1);
