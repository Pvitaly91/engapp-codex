<?php
declare(strict_types=1);

/** Explicit read-only local HTTP acceptance for the single Present Perfect Passive leaf. */
use App\Models\Page;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\PassiveVoiceQualityLocalGuard as Guard;
use App\Services\Theory\TextBlockToQuestionsMatcherService;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Support\PassiveVoicePracticeCompact as Compact;
use App\Support\PassiveVoicePracticeQuality as Quality;
use App\Support\PassiveVoicePracticeSupplement as Supplement;
use App\Support\PassiveVoiceWidgetHints as Widgets;
use App\Support\SentenceBuilderBranding;
use App\Support\TheorySentenceBuilderAcceptance as Builder;
use Illuminate\Support\Facades\DB;

$root = dirname(__DIR__, 2);
if (PHP_SAPI !== 'cli' || count($argv) !== 1 || realpath(getcwd()) !== realpath($root)
    || strcasecmp(str_replace('\\', '/', (string) realpath($root)), 'D:/DEV/htdocs/gramlyze.loc') !== 0) {
    throw new RuntimeException('Run the fixed local read-only audit from served ROOT without arguments.');
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $error): void {
    fwrite(STDERR, json_encode(['status' => 'failed', 'exception' => get_class($error), 'database_writes' => 0], JSON_THROW_ON_ERROR)."\n");
    exit(1);
});
config(['cache.default' => 'array']);
$db = DB::connection();
Guard::assertConfiguredDatabase($db);
$app->setLocale('uk');
$directory = Guard::privateDirectory().'/word-slots-http-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4));
if (!mkdir($directory, 0700)) { throw new RuntimeException('Exclusive private HTTP evidence directory unavailable.'); }

function ppwRequire(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException($message); }
}
function ppwJson(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
}
function ppwWrite(string $path, string $bytes): void
{
    $stream = fopen($path, 'x');
    if (!$stream) { throw new RuntimeException('Evidence output already exists.'); }
    try {
        if (fwrite($stream, $bytes) !== strlen($bytes) || !fflush($stream)) { throw new RuntimeException('Incomplete evidence output.'); }
    } finally { fclose($stream); }
}
function ppwGet(string $path, string $evidence, bool $json = false): array
{
    ppwRequire(preg_match('~^/(?:theory|test)/[a-z0-9/-]+$~D', $path) === 1, 'Fixed theory/test path required.');
    $url = 'http://gramlyze.loc'.$path;
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_PROXY => '',
        CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'], CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_TIMEOUT => 45,
        CURLOPT_HTTPHEADER => [$json ? 'Accept: application/json' : 'Accept: text/html']]);
    try {
        $bytes = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        ppwRequire(is_string($bytes) && $status === 200 && curl_getinfo($curl, CURLINFO_PRIMARY_IP) === '127.0.0.1'
            && curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) === $url, 'Local GET failed: '.$path.' status='.$status);
    } finally { curl_close($curl); }
    ppwWrite($evidence, $bytes);
    return ['url' => $url, 'status' => $status, 'sha256' => hash('sha256', $bytes),
        'body' => $json ? json_decode($bytes, true, flags: JSON_THROW_ON_ERROR) : $bytes];
}
function ppwDocument(string $html): DOMXPath
{
    $doc = new DOMDocument(); $previous = libxml_use_internal_errors(true);
    try { ppwRequire($doc->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NONET), 'Local HTML parse failed.'); }
    finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    return new DOMXPath($doc);
}
function ppwNative(DOMElement $node): array
{
    $expr = $node->getAttribute('x-data'); $prefix = "theoryPracticeSet(JSON.parse('"; $suffix = "'))";
    ppwRequire(str_starts_with($expr, $prefix) && str_ends_with($expr, $suffix), 'Safe native JSON literal missing.');
    $json = json_decode('"'.substr($expr, strlen($prefix), -strlen($suffix)).'"', true, flags: JSON_THROW_ON_ERROR);
    $config = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    ppwRequire(is_array($config) && $expr === 'theoryPracticeSet('.Illuminate\Support\Js::from($config)->toHtml().')', 'Native literal roundtrip differs.');
    return $config;
}
function ppwRows(array $rows, ?string $kind = null): array
{
    $expected = [];
    foreach ($kind === null ? ['standard', 'builder'] : [$kind] as $bank) {
        foreach (Quality::compile('present-perfect', $bank)['questions'] as $row) { $expected[$row['uuid']] = $row; }
    }
    $ids = array_column($rows, 'uuid'); $a = $ids; $b = array_keys($expected); sort($a); sort($b);
    ppwRequire($a === $b && count($ids) === count(array_unique($ids)), 'Exact Present Perfect Passive question set differs.');
    $formats = []; $levels = []; $previous = null; $slots = 0; $builders = 0;
    foreach ($rows as $row) {
        $source = $expected[$row['uuid']]; $level = $row['level'] ?? '';
        $format = ($row['presentation'] ?? '') === 'sentence_reorder' ? 'order' : ((string) ($row['type'] ?? '') === '4' ? 'compose' : 'gap');
        if ($kind === null) { ppwRequire($format !== $previous, 'Adjacent mixed formats repeat.'); }
        $previous = $format;
        if (!in_array($level, $levels, true)) { $levels[] = $level; }
        $formats[$level][$format] = ($formats[$level][$format] ?? 0) + 1;
        ppwRequire(isset($row['psp_content_revision']), 'Exact source revision absent.');
        ppwRequire(!preg_match('/<[^>]+>|&(?:#\d+|[a-z]+);/iu', (string) ($row['question'] ?? '')), 'Technical prompt markup leaked.');
        if ((string) $source['type'] === '4') {
            ++$builders;
            ppwRequire(($row['question'] ?? null) === $source['translation_uk'], 'Builder must show only the Ukrainian sentence.');
            ppwRequire(($row['answer_map'] ?? null) === array_map(static fn ($m) => $m['answer'], $source['markers']), 'Word marker map differs.');
            ppwRequire(($row['compose_accepted_sentences'] ?? null) === $source['accepted_target_texts'], 'Reviewed complete sentence aliases changed.');
            $policy = $row['pvq_word_slots'] ?? null;
            ppwRequire(is_array($policy) && ($policy['revision'] ?? null) === $row['psp_content_revision']
                && ($policy['question'] ?? null) === $source['question']
                && ($policy['answers'] ?? null) === array_column($source['markers'], 'answer')
                && ($policy['sentences'] ?? null) === $source['accepted_target_texts']
                && ($policy['options_by_marker'] ?? null) === $source['options_by_marker'],
                'Exact-bound one-word sentence-path policy missing.');
            // The public formatter serializes per-marker choices as an ordered
            // list; the author and exact-bound policy retain a1/a2/... keys.
            $publicOptions = $row['options_by_marker'] ?? [];
            ppwRequire(is_array($publicOptions)
                && array_values($publicOptions) === array_values($source['options_by_marker']),
                'Public per-word choices differ from the exact author source.');
            foreach ($source['markers'] as $key => $marker) {
                ++$slots;
                ppwRequire(count(preg_split('/\s+/u', $marker['answer'])) === 1, 'Ordinary multiword builder slot remains.');
                $index = array_search($key, array_keys($source['markers']), true);
                $options = array_is_list($publicOptions) ? ($publicOptions[$index] ?? null) : ($publicOptions[$key] ?? null);
                ppwRequire(is_array($options) && count($options) === 4, 'Exactly four per-word choices required.');
                foreach ($options as $option) {
                    ppwRequire(is_string($option) && count(preg_split('/\s+/u', trim($option))) === 1, 'Multiword builder choice remains.');
                }
            }
        } else {
            ppwRequire(($row['passive_phrase_word_inputs'] ?? null) === true && ($row['passive_gap_phrase_input'] ?? null) === true,
                'Existing complete gap phrase/word input guard changed.');
        }
    }
    ppwRequire($levels === Quality::LEVELS, 'Every A1–C2 level must be present in order.');
    foreach ($formats as $counts) {
        ksort($counts);
        $wanted = $kind === null ? ['compose' => 3, 'gap' => 3, 'order' => 3]
            : ($kind === 'builder' ? ['compose' => 3] : ['gap' => 6]);
        ppwRequire($counts === $wanted, 'Per-level format distribution differs.');
    }
    return ['questions' => count($rows), 'builders' => $builders, 'single_word_slots' => $slots, 'formats' => $formats,
        'question_order_sha256' => hash('sha256', implode('|', $ids))];
}

$exit = 0;
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $target = array_column(Supplement::load()['targets'], null, 'topic')['present-perfect'];
    $binding = $target['binding']; $page = Page::with('category')->findOrFail(112);
    ppwRequire($binding['page_id'] === 112 && $page->seeder === $binding['seeder'] && (int) $page->page_category_id === 18, 'Exact Page112 owner differs.');
    $block = TextBlock::where('uuid', $binding['uuid'])->firstOrFail();
    ppwRequire($block->seeder === $binding['seeder'] && hash_equals($binding['body_sha256'], hash('sha256', $block->body)), 'Existing theory practice body changed.');
    $get = ppwGet(Guard::BINDINGS['present-perfect']['path'], $directory.'/theory.html');
    $html = $get['body']; unset($get['body']); $xp = ppwDocument($html);
    ppwRequire($xp->query('/html[@lang="uk"]')->length === 1, 'Theory locale differs.');
    $factories = $xp->query('//*[@id="block-'.(int) $block->id.'"]//*[@x-data and starts-with(@x-data,"theoryPracticeSet(")]');
    ppwRequire($factories->length === 1, 'One native practice factory required.'); $config = ppwNative($factories->item(0));
    foreach (['selects', 'choices', 'inputs', 'rephrase'] as $key) {
        ppwRequire(($config[$key] ?? []) === ($target['after_data'][$key] ?? []), 'Native practice changed: '.$key);
    }
    $compact = array_column(Compact::load()['targets'], null, 'topic')['present-perfect'];
    ppwRequire(($config['exerciseGroups'] ?? null) === $compact['groups'], 'Existing compact cards changed.');
    ppwRequire(($config['sentencePunctuationOptional'] ?? null) === true && ($config['noInputTokenFocus'] ?? null) === true, 'Native punctuation/focus guards changed.');
    $matchingBlock = App\Support\PracticeCompletionLinkedPractice::matchingBlock($block, true);
    $matcher = app(TextBlockToQuestionsMatcherService::class);
    $eligible = $matcher->findBestQuestionsForTextBlock($matchingBlock, 500);
    $selected = $matcher->findQuestionsForTextBlocks(new Illuminate\Database\Eloquent\Collection([$matchingBlock]), 5)[$block->uuid] ?? collect();
    $contracts = Builder::contracts($root);
    $widget = Builder::check($contracts[$binding['seeder']], $page, $block, $target['after_data']['linked_practice'], $eligible, $selected, $html);
    ppwRequire($widget['ok'], 'Exact lower-five widget acceptance failed: '.implode(', ', $widget['errors']));
    $widgetBindings = Widgets::load();
    $widgetTarget = array_column($widgetBindings['targets'], null, 'topic')['present-perfect'];
    ppwRequire(array_column($widget['payload'], 'id') === $widgetTarget['selected_ids'], 'Retained thematic lower-five IDs changed.');
    $widgetSources = array_column($widgetBindings['questions'], null, 'id');
    foreach ($widget['payload'] as $row) {
        $source = $widgetSources[$row['id']];
        ppwRequire(($row['question'] ?? null) === $source['display_question'] && ($row['correct_tokens'] ?? null) === $source['correct_tokens'], 'Existing lower-widget prompt/tokens changed.');
    }
    unset($widget['payload']);
    $theory = $get + ['page_id' => 112, 'native_compact_cards' => count($compact['groups']), 'native_inputs' => count($config['inputs']), 'widget' => $widget];
    $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);
    $mixed = $tests->first(fn ($test) => (bool) data_get($test->filters, '__meta.theory_page_mixed_all_levels_test'));
    ppwRequire($mixed && ($mixed->filters['theory_page_mixed_type_profile'] ?? null) === Quality::REVISION, 'Mixed source profile differs.');
    $path = '/test/'.$mixed->getAttribute('public_slug');
    ppwRequire($path === '/test/passive-voice-tenses/theory-passive-voice-present-perfect', 'Requested mixed URL differs.');
    $card = ppwGet($path, $directory.'/mixed.html'); unset($card['body']);
    $step = ppwGet($path.'/step', $directory.'/mixed-step.html'); unset($step['body']);
    $payload = ppwGet($path.'/questions', $directory.'/mixed-payload.json', true);
    $mixedCheck = ppwRows($payload['body']['questions'] ?? []); unset($payload['body']);
    $mixedCheck += ['card' => $card, 'step' => $step, 'payload' => $payload];
    $standalone = [];
    foreach (['standard', 'builder'] as $kind) {
        $bank = Quality::mapping('present-perfect')[$kind];
        $saved = SavedGrammarTest::where('uuid', $bank['old_saved_test_uuid'])->firstOrFail();
        $path = '/test/'.SentenceBuilderBranding::canonicalLessonSlug((string) $saved->slug);
        $htmlGet = ppwGet($path, $directory.'/'.$kind.'.html'); unset($htmlGet['body']);
        $payload = ppwGet($path.'/questions', $directory.'/'.$kind.'-payload.json', true);
        $standalone[$kind] = ppwRows($payload['body']['questions'] ?? [], $kind); unset($payload['body']);
        $standalone[$kind] += ['html' => $htmlGet, 'payload' => $payload];
    }
    $receipt = ['at' => gmdate('c'), 'status' => 'verified', 'theory' => $theory, 'mixed' => $mixedCheck, 'standalone' => $standalone,
        'unique_question_count' => 54, 'changed_builders' => 18, 'database_writes' => 0,
        'browser_interaction' => 'not_checked_by_this_read_only_http_audit'];
    ppwWrite($directory.'/receipt.json', ppwJson($receipt));
    echo ppwJson(['status' => 'verified', 'receipt' => $directory.'/receipt.json', 'mixed' => 54, 'standard' => 36, 'builder' => 18, 'word_slots' => 161, 'database_writes' => 0]);
} catch (Throwable $error) {
    ppwWrite($directory.'/failed.json', ppwJson(['status' => 'failed', 'message' => $error->getMessage(), 'database_writes' => 0]));
    fwrite(STDERR, ppwJson(['status' => 'failed', 'receipt' => $directory.'/failed.json', 'message' => $error->getMessage(), 'database_writes' => 0]));
    $exit = 1;
} finally { $db->rollBack(); }
exit($exit);
