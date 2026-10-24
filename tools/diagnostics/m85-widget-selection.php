<?php

/** Pure M85 existing-bank selection. No application bootstrap, connection, HTTP or source mutation. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 2
    || preg_match('/^widget-selection-v[1-9][0-9]*\.json$/D', $argv[1]) !== 1) {
    throw new RuntimeException('M85 requires an exclusive private widget-selection basename.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = $root.'/storage/app/m75-worktree';
$private = $root.'/storage/app/seo-m85-local';
$normal = static fn ($p) => strtolower(str_replace('\\', '/', (string) realpath($p)));
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || is_link($private) || is_link($wt)) {
    throw new RuntimeException('M85 exact source/private paths differ.');
}
$out = $private.'/'.$argv[1];
if (!is_dir($private) || file_exists($out) || is_link($out)) { throw new RuntimeException('M85 exclusive output collision.'); }
$beforeSha = 'fe639c06446c6fb46b5b4ab25ae654e3910b6733cc124a9e4bee262e41b411b7';
$beforeFile = $private.'/before-v1.json';
if (is_link($beforeFile) || !hash_equals($beforeSha, hash_file('sha256', $beforeFile))) {
    throw new RuntimeException('M85 independent fresh BEFORE changed.');
}
$before = json_decode(file_get_contents($beforeFile), true, flags: JSON_THROW_ON_ERROR);
require $root.'/vendor/autoload.php';
require_once $root.'/app/Support/M85VerbToBe.php';

use App\Support\ComposeTokenCase;
use App\Support\M26DetailPackage as Digest;
use App\Support\M85VerbToBe as Package;

if ($before['base_sha'] !== Package::BASE_SHA || $before['sql_before'] !== $before['sql_after_http']) {
    throw new RuntimeException('M85 independent source/SQL lineage differs.');
}
$sourceHashes = [
    '3b8010923c3ce43fab74f5889aaa058308b4643c8199fa08562281abb67f6d6a',
    '5c9905be8231a3e6e4485bded03051d86aef1620c01f4fc3501b1f250bbbb0a1',
    '45f4233a5c084633b1d2fba80efa5e4cc9df4f4e369e1034ee2c8415e78839e6',
    'cb83d9637fdfbc491f29616eba7057bbcd8729e08c0cced2c432a0cdff503ee8',
    'b8c6e6b15970f779c7dedf721c48fa591554291ff6868554f6a647a76379fef1',
    '37dfa33ea0abb6eafa5117b0a416461a9b99fc24a99807890fcc7ce894e9eaf4',
];
$expectedPools = [71, 72, 72, 72, 72, 70];
$knownConditionDrifts = [
    'poly-vtbpa-a2-01' => ['Я не був на зустрічі вчора.', 'Я був на зустрічі вчора.'],
    'poly-vtbpa-a2-02' => ['Вона не була готова до тесту.', 'Вона була готова до тесту.'],
    'poly-vtbpa-a2-03' => ['Вони не були в школі в понеділок.', 'Вони були в школі в понеділок.'],
    'poly-vtbpa-a2-04' => ['Ми не були раді цій новині.', 'Ми були раді цій новині.'],
    'poly-vtbpa-a2-05' => ['Фільм зовсім не був цікавим.', 'Фільм був зовсім нецікавий.'],
    'poly-vtbpa-a2-06' => ['Ти не був в офісі, коли я подзвонив.', 'Ти був в офісі, коли я подзвонив.'],
];
$observedConditionDrifts = [];
// Each literal condition/translation/settled sequence was editorially reviewed; no key is synthesized from its translation.
$reviews = [
    [
        ['poly-vtbpr-a1-02', 'Говориш про себе й свій стан саме сьогодні; почни з «я». Я щасливий сьогодні.', 'Я щасливий сьогодні.', 'I am happy today.', 'First-person present state; explicit time.'],
        ['poly-vtbpr-a1-05', 'Йдеться про жінку; почни з «вона». Вона вчителька.', 'Вона вчителька.', 'She is a teacher.', 'Third-person role; explicit she, not a gender inference from a profession.'],
        ['poly-vtbpr-a1-08', 'Назви місце вже відомої книги на відомому столі; почни з «книга». Книга на столі.', 'Книга на столі.', 'The book is on the table.', 'Named singular subject and location; two separate the instances.'],
        ['poly-vtbpr-a1-10', 'Повідом про погоду зараз: надворі холодно.', 'Надворі холодно.', 'It is cold outside.', 'Weather/state and impersonal it.'],
        ['poly-vtbpr-a1-12', 'Говориш про себе й друзів; почни з «ми». Ми в парку.', 'Ми в парку.', 'We are in the park.', 'First-person plural and location.'],
    ],
    [
        ['poly-vtbng-a1-01', 'Про свій стан саме сьогодні; збережи заперечення повною формою. Я не втомлений сьогодні.', 'Я не втомлений сьогодні.', 'I am not tired today.', 'First-person negative state; am not, no inference about another state.'],
        ['poly-vtbng-a1-02', 'Про жінку саме зараз; почни з «вона» й використай повну заперечну форму. Вона зараз не вдома.', 'Вона зараз не вдома.', 'She is not at home right now.', 'Third-person negative place/time; does not identify her actual location.'],
        ['poly-vtbng-a1-05', 'Говориш від імені своєї групи; почни з «ми» й використай повну заперечну форму. Ми не готові до тесту.', 'Ми не готові до тесту.', 'We are not ready for the test.', 'Plural negative state and complement.'],
        ['poly-vtbng-a1-09', 'Про погоду сьогодні; склади заперечення зі скороченою формою. Сьогодні не холодно.', 'Сьогодні не холодно.', "It isn't cold today.", 'Contracted negative state; not cold does not entail hot.'],
        ['poly-vtbng-a1-10', 'Про своїх друзів; почни з «мої друзі» й використай скорочену заперечну форму. Мої друзі не тут.', 'Мої друзі не тут.', "My friends aren't here.", 'Plural noun agreement and contracted negative presence.'],
    ],
    [
        ['poly-vtbqs-a1-01', 'Ти учень і запитуєш учителя про себе. Я запізнився на урок?', 'Я запізнився на урок?', 'Am I late for class?', 'First-person neutral direct be-question.'],
        ['poly-vtbqs-a1-03', 'Запитуєш про групу людей, не про себе. Вони з Іспанії?', 'Вони з Іспанії?', 'Are they from Spain?', 'Third-person plural neutral question and origin.'],
        ['poly-vtbqs-a1-07', 'Говориш про себе й супутника. Ми в правильній аудиторії?', 'Ми в правильній аудиторії?', 'Are we in the right room?', 'First-person plural neutral question and location.'],
        ['poly-vtbqs-a1-09', 'Запитуєш про жінку й отримуєш ствердну коротку відповідь про неї. Вона вдома? — Так, вона вдома.', 'Вона вдома? — Так, вона вдома.', 'Is she at home? Yes, she is.', 'Question plus positive third-person short answer; two distinct she instances.'],
        ['poly-vtbqs-a1-12', 'Ти запитуєш одну людину, чи маєш рацію; вона підтверджує це короткою відповіддю. Я маю рацію? — Так, маєш.', 'Я маю рацію? — Так, маєш.', 'Am I right? Yes, you are.', 'Two speakers; I in the question becomes singular you in the reply.'],
    ],
    [
        ['poly-vtbpa-a2-01', 'Про себе й учорашню зустріч; передай заперечення скороченою формою. Я не був на зустрічі вчора.', 'Я не був на зустрічі вчора.', "I wasn't at the meeting yesterday.", 'Past negative presence; explicit not corrects only the theory clone of an old positive UK condition.'],
        ['poly-vtbpa-a2-03', 'Про групу людей у понеділок; почни з «вони» й передай заперечення скороченою формою. Вони не були в школі в понеділок.', 'Вони не були в школі в понеділок.', "They weren't at school on Monday.", 'Past plural negative; explicit not corrects only the theory clone of an old positive UK condition.'],
        ['poly-vtbpa-a2-07', 'Звертаєшся до однієї людини й запитуєш про минулу ніч. Ти був на концерті минулої ночі?', 'Ти був на концерті минулої ночі?', 'Were you at the concert last night?', 'Past neutral yes/no question with singular you.'],
        ['poly-vtbpa-a2-09', 'Запитуєш про дітей і стан на момент дев’ятої години. Діти були в ліжку до дев’ятої?', 'Діти були в ліжку до дев’ятої?', 'Were the children in bed by nine?', 'Past neutral question with a plural noun subject and a stated time boundary.'],
        ['poly-vtbpa-a2-10', 'Запитуєш про погоду саме минулої суботи. Минулої суботи було сонячно?', 'Минулої суботи було сонячно?', 'Was it sunny last Saturday?', 'Past neutral weather question; no inference about current weather.'],
    ],
    [
        ['poly-vtbfu-a2-02', 'Вислови свою думку про майбутній перегляд. Думаю, фільм буде цікавим.', 'Думаю, фільм буде цікавим.', 'I think the film will be interesting.', 'Future-state prediction presented as the speaker’s opinion, not a guarantee.'],
        ['poly-vtbfu-a2-04', 'Скажи, скільки років буде дівчині наступного місяця; почни з «вона». Наступного місяця їй виповниться вісімнадцять.', 'Наступного місяця їй виповниться вісімнадцять.', 'She will be eighteen next month.', 'Future age with an explicit female referent and time.'],
        ['poly-vtbfu-a2-06', 'Ти запитуєш одну людину про спортзал; вона коротко відповідає, що її там не буде. Ти будеш у спортзалі? — Ні, не буду.', 'Ти будеш у спортзалі? — Ні, не буду.', "Will you be at the gym? No, I won't.", 'Two-speaker future question and fact-bound negative short reply; singular you becomes I.'],
        ['poly-vtbfu-a2-10', 'Запитай, яка людина обійматиме цю роль. Хто буде наступним капітаном команди?', 'Хто буде наступним капітаном команди?', 'Who will be the next team captain?', 'Subject-who future be-question; no additional subject pronoun.'],
        ['poly-vtbfu-a2-12', 'Скажи про стан дітей після майбутньої поїздки; почни з «діти». Діти втомляться після поїздки.', 'Діти втомляться після поїздки.', 'The children will be tired after the trip.', 'Plural noun future state and a stated future context.'],
    ],
    [
        ['poly-thia-a1-01', 'Уперше повідом про наявність предмета, а не називай місце вже відомої книги. На столі є книга.', 'На столі є книга.', 'There is a book on the table.', 'Singular existential introduction; not a known-subject location answer.'],
        ['poly-thia-a1-04', 'Повідом, що в цьому місці є саме три предмети. Біля вікна є три стільці.', 'Біля вікна є три стільці.', 'There are three chairs near the window.', 'Plural existence, exact quantity and location.'],
        ['poly-thia-a1-05', 'Уперше повідом про тварину в цьому місці. Під ліжком є собака.', 'Під ліжком є собака.', 'There is a dog under the bed.', 'Singular existence and a distinct location.'],
        ['poly-thia-a1-08', 'Повідом про наявність великої кількості предметів. У коробці є багато іграшок.', 'У коробці є багато іграшок.', 'There are many toys in the box.', 'Plural existential statement with many; not an exact numeric claim.'],
        ['poly-thia-a1-12', 'Повідом про наявність кількох предметів без точної кількості. У вазі є квіти.', 'У вазі є квіти.', 'There are some flowers in the vase.', 'Plural existential statement with some; exact count remains unspecified.'],
    ],
];
$result = ['schema_version' => 1, 'kind' => 'independent-existing-bank-selection', 'status' => 'private-draft-not-frozen',
    'independent_before_sha256' => $beforeSha, 'base_sha' => Package::BASE_SHA,
    'selection_policy' => 'Thirty existing questions only. No question insertion, source/answer/option/formatter mutation or technical NULL-filter rewrite.',
    'actual_pool_counts' => $expectedPools, 'source_pool_counts' => [72, 72, 72, 72, 72, 72],
    'new_questions' => 0, 'selected_existing' => 30, 'source_findings' => [], 'targets' => [],
    'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0, 'active_source_writes' => 0];
$lowerTokens = static fn (array $tokens) => array_map(static fn ($v) => mb_strtolower(trim((string) $v)), $tokens);
foreach (Package::OWNERS as $i => $owner) {
    $bank = Package::BANKS[$i]; $name = substr($bank, strrpos($bank, '\\') + 1);
    $path = 'database/seeders/V3/Polyglot/'.$name.'/definition.json';
    foreach ([$root, $wt] as $base) {
        if (is_link($base.'/'.$path) || !hash_equals($sourceHashes[$i], hash_file('sha256', $base.'/'.$path))) {
            throw new RuntimeException('M85 paired existing-bank source changed: '.$name);
        }
    }
    $source = json_decode(file_get_contents($wt.'/'.$path), true, flags: JSON_THROW_ON_ERROR);
    $sourceByUuid = array_column($source['questions'], null, 'uuid');
    if (($source['seeder']['class'] ?? null) !== $bank || count($sourceByUuid) !== 72) { throw new RuntimeException('M85 exact source bank population differs.'); }
    $page = $before['pages'][$owner];
    $pool = array_filter($page['relations']['question_snapshots'], static fn ($s) => $s['question']['seeder'] === $bank && (string) $s['question']['type'] === '4');
    if (count($pool) !== $expectedPools[$i]) { throw new RuntimeException('M85 actual NULL-eligible thematic pool differs.'); }
    $actualByLevel = []; $sourceByLevel = []; $nativeComparisons = [];
    foreach ($sourceByUuid as $q) { $sourceByLevel[$q['level']] = ($sourceByLevel[$q['level']] ?? 0) + 1; }
    foreach ($pool as $uid => $snapshot) {
        $q = $snapshot['question']; $sq = $sourceByUuid[$uid] ?? null;
        $sourceAnswers = $sq['answers'] ?? []; uksort($sourceAnswers, 'strnatcasecmp');
        $actualAnswers = array_column($snapshot['ordered_answer_instances'], 'option');
        $conditionExact = is_array($sq) && $q['question'] === $sq['question'];
        if (!$conditionExact && is_array($sq)) {
            if (($knownConditionDrifts[$uid] ?? null) !== [$sq['question'], $q['question']]) {
                throw new RuntimeException('M85 unreviewed source/SQL condition drift: '.$uid);
            }
            $observedConditionDrifts[$uid] = [$sq['question'], $q['question']];
        }
        if (!is_array($sq) || $q['uuid'] !== $uid || $q['level'] !== $sq['level']
            || $lowerTokens(array_values($sourceAnswers)) !== $lowerTokens($actualAnswers)) {
            throw new RuntimeException('M85 existing source/actual question identity, condition or lexical ordered key differs: '.$uid);
        }
        $actualByLevel[$q['level']] = ($actualByLevel[$q['level']] ?? 0) + 1;
        $nativeComparisons[] = ['id' => $q['id'], 'uuid' => $uid, 'level' => $q['level'],
            'source_condition_exact' => $conditionExact, 'source_answer_lexical_sequence_exact' => true,
            'known_source_condition_drift' => $conditionExact ? null : ['source' => $sq['question'], 'SQL_before' => $q['question'], 'action' => 'No source/SQL mutation; selected condition is reviewed separately.'],
            'raw_case_variation_is_shared_option_storage' => array_values($sourceAnswers) !== $actualAnswers,
            'complete_private_snapshot_sha256' => Digest::digest($snapshot)];
    }
    ksort($sourceByLevel); ksort($actualByLevel);
    $missing = array_values(array_diff(array_keys($sourceByUuid), array_keys($pool)));
    $expectedMissing = [0 => ['poly-vtbpr-a1-01'], 1 => [], 2 => [], 3 => [], 4 => [], 5 => ['poly-thia-a1-02', 'poly-thia-a2-12']][$i];
    if ($missing !== $expectedMissing) { throw new RuntimeException('M85 source/page-linked missing UUID boundary differs.'); }
    $selected = [];
    foreach ($reviews[$i] as [$uid, $condition, $translation, $settled, $coverage]) {
        $snapshot = $pool[$uid] ?? null; $q = $snapshot['question'] ?? null;
        if (!is_array($q) || $q['level'] !== Package::FOCUS_LEVELS[$i]) { throw new RuntimeException('M85 reviewed candidate is absent or not at the intended focus: '.$uid); }
        $rawTokens = array_column($snapshot['ordered_answer_instances'], 'option');
        $selected[] = ['id' => $q['id'], 'uuid' => $uid, 'level' => $q['level'], 'source' => $q['question'],
            'condition' => $condition, 'translation' => $translation, 'settled' => $settled, 'coverage' => $coverage,
            'raw_tokens' => $rawTokens, 'tokens' => ComposeTokenCase::normalize($rawTokens)];
    }
    usort($selected, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
    $result['targets'][] = ['identity' => $owner, 'bank' => $bank, 'level' => null,
        'intended_selected_level' => Package::FOCUS_LEVELS[$i], 'selected_levels' => array_column($selected, 'level'),
        'source_path' => $path, 'source_sha256' => $sourceHashes[$i], 'source_count' => 72, 'actual_eligible_count' => count($pool),
        'source_count_by_level' => $sourceByLevel, 'actual_count_by_level' => $actualByLevel,
        'source_uuids_absent_from_page_linked_capture' => $missing,
        'missing_policy' => 'Absence in this exact page-linked capture is not a claim that the UUID is absent globally. No inserts or link repairs are authorized/needed for this finite five.',
        'source_to_actual_review' => $nativeComparisons, 'selected' => $selected,
        'coverage_boundary' => $i === 5 ? 'The five A1 widget questions assess affirmative existential introduction/countable nouns only. Own controls separately assess uncountable is, a plural question, positive short-answer format, and not-two versus zero. This is not a complete There Is/Are course.'
            : ($i === 4 ? 'Five A2 widget models cover prediction, age, plural future state, a negative short reply and subject-who. Own controls separately assess a complete negative statement and a named-subject Where-question. Not every future construction is tested.'
                : 'Finite five-question widget coverage is separate from six own graded controls; no claim of complete coverage of all topic usages.')];
    foreach ($missing as $uid) { $result['source_findings'][] = ['identity' => $owner, 'uuid' => $uid, 'finding' => 'Source UUID not in actual page-linked eligible pool.', 'action' => 'Retain absence; choose a suitable existing linked candidate, no insertion.']; }
}
if ($observedConditionDrifts !== $knownConditionDrifts) { throw new RuntimeException('M85 exact six known stale SQL conditions differ.'); }
$result['source_SQL_condition_drifts'] = array_map(static fn ($uid, $pair) => ['uuid' => $uid, 'source_condition_uk' => $pair[0],
    'SQL_before_condition_uk' => $pair[1], 'action' => 'Preserve source and all old SQL rows; only selected canonical-theory unsaved clones can present a corrected condition.'],
    array_keys($observedConditionDrifts), array_values($observedConditionDrifts));
$result['source_findings'][] = ['identity' => Package::OWNERS[2], 'uuid' => 'poly-vtbqs-a1-10',
    'finding' => 'Existing key splits I and m as separate tokens; terminal contraction reconstruction is outside this package.',
    'action' => 'Exclude from finite selected five; no source/formatter repair or global invalidity claim.'];
foreach ([['poly-vtbpa-a2-01', 'Я був на зустрічі вчора.', 'Я не був на зустрічі вчора.', "I wasn't at the meeting yesterday."],
    ['poly-vtbpa-a2-03', 'Вони були в школі в понеділок.', 'Вони не були в школі в понеділок.', "They weren't at school on Monday."]] as [$uid, $old, $new, $answer]) {
    $result['source_findings'][] = ['identity' => Package::OWNERS[3], 'uuid' => $uid, 'original_condition_uk' => $old,
        'canonical_theory_translation_uk' => $new, 'unchanged_key_as_sentence' => $answer,
        'finding' => 'The versioned source condition is correctly negative; the stale SQL Ukrainian condition is positive while its unchanged ordered English key is negative.',
        'action' => 'Correct the visible canonical-theory condition on exact snapshot-bound unsaved clones only; original question/answer/option/link rows stay unchanged.'];
}
$json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($out, 'x'); if (!$handle) { throw new RuntimeException('M85 exclusive private selection unavailable.'); }
try { if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) { throw new RuntimeException('M85 selection write incomplete.'); } }
finally { fclose($handle); }
echo json_encode(['status' => 'private-existing-selection-awaiting-formatter-review', 'file' => $argv[1],
    'sha256' => hash('sha256', $json), 'pool_counts' => $expectedPools,
    'selected_ids' => array_map(static fn ($t) => array_column($t['selected'], 'id'), $result['targets']),
    'new_questions' => 0, 'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
