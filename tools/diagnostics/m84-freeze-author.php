<?php

/** Pure source-only M84 freeze proposal. It creates one private plan, never active/public files or DB rows. */
if (PHP_SAPI !== 'cli' || count($argv) !== 7
    || [$argv[1], $argv[3], $argv[5]] !== ['author-projection-v2.json', 'bank-audit-projection-v1.json', 'prospective-metadata-v1.json']
    || [$argv[2], $argv[4], $argv[6]] !== [
        '442a5f202e978c6afc84e86867945c733780a1d8c1176c100f85535c5d397faf',
        '170c9966b5c230c2bf92ce52ab2dc5430a29807d0c5db448fb17f29e3f69f7ca',
        '1cbe8d50fcebe590b4039db7b8f12d9f74d27cc78039a502e730cf6483c9183d',
    ]) {
    throw new RuntimeException('M84 requires the exact reviewed author-v2/bank-v1/prospective-metadata-v1 tuple.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc'; $wt = $root.'/storage/app/m75-worktree'; $dir = $root.'/storage/app/seo-m84-local';
$normal = static fn ($p) => strtolower(str_replace('\\', '/', (string) realpath($p)));
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || !is_dir($dir) || is_link($dir) || is_link($wt)) {
    throw new RuntimeException('M84 exact served/delivery/private paths differ.');
}
$file = $dir.'/freeze-plan-v2.json';
if (file_exists($file) || is_link($file)) { throw new RuntimeException('M84 exclusive initial freeze plan already exists.'); }
require $root.'/vendor/autoload.php';
require_once $root.'/app/Support/M84WordOrderHaveGot.php';
require_once $root.'/app/Support/M84QuestionSupplement.php';

use App\Support\M84WordOrderHaveGot as Package;
use App\Support\M84QuestionSupplement as Supplement;
use App\Support\M26DetailPackage as Digest;

$read = static function (string $name, string $sha) use ($dir): array {
    $path = $dir.'/'.$name;
    if (!is_file($path) || is_link($path) || !hash_equals($sha, hash_file('sha256', $path))) {
        throw new RuntimeException('M84 immutable private freeze input drift: '.$name);
    }
    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
};
$json = static fn ($v) => json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$beforeSha = 'babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916';
$targetDigest = '5364c4be978371bc8e4bf6adac8425a29ab5f29373cd556d17f9055ef25e9c18';
$selectionSha = 'c7fea3c8e87e1cd60e6de2f92f02509c36a89ef9556df702ed78b297294a1779';
$supplementSha = '6c316edf96be3411387e06118e96832305357ce52ae1698e3e5b8b0b85db274a';
$validatorSha = '23b89050f061faddd80debce2fefc8dd356a0b49dffa44f79b2200cb5cbbef11';
$fixturePath = 'tests/Fixtures/m84-owned-source-hunks.json';
$fixtureSha = '5e1785016c849aee0e7ec92a919c82e392e40ea48e2c32b6d4a9e7bcdd3d319a';
$author = $read($argv[1], $argv[2]); $bank = $read($argv[3], $argv[4]); $metadata = $read($argv[5], $argv[6]);
$before = $read('before-v1.json', $beforeSha); $selection = $read('widget-selection-v1.json', $selectionSha);
if (($author['independent_before_sha256'] ?? null) !== $beforeSha || ($bank['independent_before_sha256'] ?? null) !== $beforeSha
    || ($metadata['independent_before_sha256'] ?? null) !== $beforeSha || ($metadata['projection_sha256'] ?? null) !== $argv[2]
    || ($bank['source_proof']['author_projection_sha256'] ?? null) !== $argv[2]
    || ($bank['source_proof']['widget_selection_sha256'] ?? null) !== $selectionSha
    || ($bank['source_proof']['supplement_source_sha256'] ?? null) !== $supplementSha
    || !hash_equals($targetDigest, Digest::digest($bank['targets'] ?? []))
    || array_column($bank['targets'] ?? [], 'identity') !== Package::OWNERS
    || array_column($metadata['metadata'] ?? [], 'identity') !== Package::OWNERS
    || array_column($selection['targets'] ?? [], 'identity') !== Package::OWNERS
    || array_map(static fn ($t) => count($t['first_five']), $bank['targets']) !== [5, 5, 5, 5, 5, 5]
    || ($author['db_connections'] ?? null) !== 0 || ($author['db_writes'] ?? null) !== 0 || ($author['http_requests'] ?? null) !== 0
    || ($metadata['db_connections'] ?? null) !== 0 || ($metadata['db_writes'] ?? null) !== 0 || ($metadata['http_requests'] ?? null) !== 0) {
    throw new RuntimeException('M84 independent author/bank/selection/metadata lineage differs.');
}
foreach ([$root, $wt] as $base) {
    if (!hash_equals($validatorSha, hash_file('sha256', $base.'/app/Support/TheoryNativeContentRelease.php'))) {
        throw new RuntimeException('M84 unchanged native validator differs.');
    }
}
foreach ($metadata['native_policy_pins'] as $path => $sha) {
    if (!is_file($root.'/'.$path) || is_link($root.'/'.$path) || !hash_equals($sha, hash_file('sha256', $root.'/'.$path))
        || !is_file($wt.'/'.$path) || is_link($wt.'/'.$path)
        || str_replace("\r\n", "\n", file_get_contents($root.'/'.$path)) !== str_replace("\r\n", "\n", file_get_contents($wt.'/'.$path))) {
        throw new RuntimeException('M84 unchanged metadata policy differs: '.$path);
    }
}
foreach ([Supplement::SOURCE => $supplementSha, $fixturePath => $fixtureSha] as $path => $sha) {
    if (!is_file($wt.'/'.$path) || is_link($wt.'/'.$path) || !hash_equals($sha, hash_file('sha256', $wt.'/'.$path))) {
        throw new RuntimeException('M84 existing frozen supplement/source-fixture input differs: '.$path);
    }
}
$supplement = json_decode(file_get_contents($wt.'/'.Supplement::SOURCE), true, flags: JSON_THROW_ON_ERROR);
Supplement::validate($supplement);
if ($supplement['status'] !== 'frozen') { throw new RuntimeException('M84 initial freeze requires the reviewed frozen supplement input.'); }
$release = new App\Support\TheoryNativeContentRelease(Package::draftProfile($author['native_bindings']['bindings']));
$release->validate($author['source_before'], $author['patch'], $author['master'], $author['contract']);
Package::assertEditorial($author['source_before'], $author['patch'], $author['master'], $author['navigation_review']);

// These private audits contain runtime paths: publish only their explicit primary-editorial source catalogues.
$reviewPins = [
    'author-ab-review.json' => '7536bf46b3e148ec8a8be14da1afc1b9e5e0b715d2f75b94e95c8c764531738a',
    'author-cd-review.json' => '341c26a45dd37f7db8dda8f2cd38c7a4a8b9c480b2a06e9b31057fa7fd26cb91',
    'author-ef-review.json' => '6bc851e883a444e27984dc0694745e0929daed2c65cee0f4e61193a2aef0799d',
];
$reviews = []; foreach ($reviewPins as $name => $sha) { $reviews[$name] = $read($name, $sha); }
$sources = [];
foreach (['ab' => ['author-ab-review.json', 'primary_language_sources'], 'cd' => ['author-cd-review.json', 'external_primary_review'],
    'ef' => ['author-ef-review.json', 'primary_sources']] as $group => [$name, $key]) {
    foreach ($reviews[$name][$key] ?? [] as $source) {
        if (!is_string($source['url'] ?? null) || !str_starts_with($source['url'], 'https://') || empty($source['access'])) {
            throw new RuntimeException('M84 primary source URL/access evidence is incomplete.');
        }
        $sources[] = ['review_group' => $group, 'catalog_key' => $group.'/'.($source['id'] ?? count($sources))] + $source;
    }
}
if (count($sources) !== 34) { throw new RuntimeException('M84 exact primary editorial source catalogue cardinality differs.'); }

/** Explicit source-topic specification. Demos are separate references, never graded controls. */
$row = static fn ($key, $topic, $blocks, $own, $widgets, $limit, $demos = []) => compact('key', 'topic', 'blocks', 'own', 'widgets', 'limit', 'demos');
$specs = [
    [
        $row('a-neutral-roles', 'Нейтральне твердження та незмінні ролі учасників', [0, 1], ['choices/0'], [0, 3], 'SVO — поширена модель з додатком, не вимога object до кожного дієслова. Змістову роль перевіряє власний choice; widget складає речення, а не автоматично класифікує всі члени.', ['rephrase/0']),
        $row('a-frequency', 'Частотність із lexical verb та be', [2], ['selects/0', 'inputs/0', 'rephrase/1'], [1, 2], 'Задана позиція often/always оцінюється за видимою умовою. Не всі front/end варіанти та всі прислівники оцінено; незвичайний порядок не оголошено універсально неможливим.'),
        $row('a-time', 'Кінцевий та допустимий початковий час', [3], ['selects/1', 'inputs/1', 'choices/1'], [3, 4], 'Власний input приймає reviewed initial-time alias. Widget05 прямо просить початковий час; пунктуація й кома окремо не оцінюються.'),
        $row('a-place-time', 'Місце та час у заданому нейтральному порядку', [4], ['selects/2', 'inputs/2', 'rephrase/2'], [0, 4], 'Поєднання місце-перед-часом оцінюють own controls. Вибрані widget речення мають місце, але не є повною перевіркою кожної комбінації обставин.'),
        $row('a-expanded-model', 'Необов’язкові позиції розширеної схеми', [1, 5, 7], [], [2, 4], 'Видимий basic відділяє повне SV та complement після be від object. Widget використовує повні моделі без вигаданого додатка, але не перевіряє класифікацію complement або обов’язкове заповнення всіх шести слотів.'),
    ],
    [
        $row('b-do-support', 'Do/does/did і збереження часу та змісту', [0, 1, 2], ['selects/0', 'inputs/0', 'inputs/1', 'inputs/2'], [0, 2], 'Власний Past Simple input зберігає went→did go; у вибраному widget немає Past Simple або Perfect-перетворення. Do-support не поширюється на всі без винятку дієслова.'),
        $row('b-wh-subject-object', 'Wh-підмет проти відомого підмета та невідомої обставини', [3], ['selects/1', 'choices/0'], [2, 3], 'Власний meaning task визначає невідомого caller; widget04 дає нейтральне ствердне subject question. Continuous/Perfect, negative subject questions та emphatic do пояснені, але окремо не оцінені цими п’ятьма.'),
        $row('b-existing-auxiliary', 'Be, модальні та допоміжне have', [2, 3], ['choices/1'], [1], 'Ready і місцеперебування дають повні be-моделі без вигаданого іншого дієслова. Is he coming/Can you swim/Have you finished збережені в basic, але widget01–05 не є їх повним практичним покриттям.'),
        $row('b-negatives', 'Допоміжне, not і базова форма в запереченні', [4], ['selects/2', 'rephrase/1', 'rephrase/2'], [4], 'Власні перетворення дають Present/Past Simple, widget — Present Simple do not. Must not та інші негативні допоміжні моделі лишаються видимими, не всі оцінені.', ['rephrase/0']),
        $row('b-contractions', 'Повні й скорочені форми за критерієм', [5, 7], ['rephrase/1', 'rephrase/2'], [], 'Declared full-form aliases у двох власних rephrases приймаються незміненим matcher. У widget немає окремого contraction-control; не заявляється строгий тест кожного скорочення або заборона contractions.'),
        $row('b-neutral-checking', 'Нейтральне повне питання та розмовне перепитування', [6, 7], ['selects/0'], [0], 'Явний own criterion відрізняє потрібну форму від контекстно можливого statement question. Widget використовує нейтральне питання; це не автоматична перевірка інтонації, здивування або всіх conversation contexts.'),
    ],
    [
        $row('c-frequency-positions', 'Позиції частотності з lexical verb, be та auxiliary chain', [1, 2], ['selects/0', 'inputs/0'], [0, 1, 2], 'Widget03 дає конкретне have often visited. Це не доказ правила після будь-якого auxiliary або оцінювання кожної frequency/front/end позиції.', ['rephrase/0']),
        $row('c-manner', 'Спосіб дії та короткий додаток', [3], ['selects/1', 'inputs/1', 'rephrase/1'], [4], 'Own controls задають потрібну кінцеву позицію; widget05 свідомо приймає manner перед дієсловом. Не вводиться універсальний manner-at-end закон.'),
        $row('c-place-time', 'Місце, час і початкова обставина', [4, 5, 6], ['selects/2', 'inputs/2'], [3], 'Власний input має кінцеве місце+час; widget04 — початковий час і місце. Here noun/pronoun contrast та всі Manner-Place-Time альтернативи збережені в basic, але не оцінені кожна окремо.'),
        $row('c-degree-scope', 'Ступінь ознаки та сфера not always', [2, 7], ['choices/0', 'choices/1'], [], 'Два власні meaning tasks оцінюють конкретні повідомлення. У вибраному widget немає degree або not-always питання; не заявляється повне покриття much/very much і всіх позицій ступеня.'),
        $row('c-initial-never', 'Контрольоване початкове Never як збережене поглиблення', [2], ['rephrase/2'], [], 'Один retained rephrase прямо зберігає Never на початку. Це own-only extension, не заміна A2-практики набором C1-інверсій і не інверсія після будь-якого внутрішнього never.'),
    ],
    [
        $row('d-auxiliary-main', 'Допоміжне та смислове дієслово', [1], ['choices/1'], [2], 'Власний choice відрізняє auxiliary have у Perfect, а widget03 зберігає does not. Це не повний тест часів, lexical be/do/have або всіх можливих об’єктів.'),
        $row('d-core-modal', 'Основне дієслово після core modal', [2], ['selects/1', 'rephrase/1'], [3], 'Власний control зберігає can play, widget04 — must not move. Наступні have/be та відмінність ought to/have to пояснені; їм не приписано окремий widget-control.', ['rephrase/0']),
        $row('d-separable-object', 'Розділювані конструкції: іменник і займенник', [3, 5], ['selects/0', 'choices/0', 'inputs/0', 'inputs/1', 'inputs/2', 'rephrase/2'], [0, 4], 'Іменник допускає два reviewed порядки, коли умова їх не звужує; it/them має конкретний референт. Три full inputs — повні імперативні речення, а не фрагменти. Правило залежить від значення дієслова, не самої частки.'),
        $row('d-inseparable-model', 'Нерозділювана прийменникова модель', [4], ['selects/2'], [1], 'Look after оцінено прямо; put up with/look forward to/run out of лишаються поясненими source-моделями. Не оцінено всі трислівні дієслова і не створено алгоритм за кількістю слів.'),
    ],
    [
        $row('e-inversion-scope', 'Початкова негативна/обмежувальна інверсія та її сфера', [2, 6], ['selects/0', 'choices/1', 'rephrase/1', 'rephrase/2'], [0], 'Never/Rarely/Not only own controls зберігають час і зміст. Meaning task має Not only у групі підмета без довільної інверсії. Hardly/No sooner/Only after видимі, але не кожній моделі приписано окремий widget.', ['rephrase/0']),
        $row('e-it-cleft', 'It-cleft: особа, час і предмет у фокусі', [3], ['selects/1', 'choices/0', 'inputs/0', 'inputs/1', 'inputs/2'], [1], 'Три full inputs і meaning task зберігають факти та фокус. Human who/that manual aliases задекларовано; це не означає автоматичну заміну токенів або вичерпну перевірку she/her та всіх clefts.'),
        $row('e-what-cleft', 'What-cleft з іменною або інфінітивною фокусною частиною', [4, 7], ['selects/2'], [2], 'Own select має явний noun-focus criterion; widget03 дає іншу правильну infinitival-focus модель What she wants is to leave. Some rest/to rest не протиставляються як глобально правильне/неправильне.'),
        $row('e-wh-clause-boundary', 'Початкова wh-частина не є автоматично тим самим cleft', [4], [], [], 'Where/Why/How source-речення збережено з термінологічними межами. Вибрана п’ятірка не є окремим класифікаційним тестом для кожного початкового wh-clause; це theory-only coverage.'),
        $row('e-fronting', 'Контрастне винесення без автоматичної інверсії', [5], [], [3], 'Widget04 оцінює конкретне object fronting з незмінним I still use. Away ran the children / Away they ran пояснено, але ці дві directional-моделі не пройдені окремими власними controls.'),
        $row('e-emphatic-do', 'Наголошене do/does/did зі збереженням фактів', [5, 8], [], [4], 'Widget05 дає повне She did bring the documents. Source finish/know пари не додають нових facts лише в target. Spoken stress та інтонація не оцінюються письмовим score.'),
    ],
    [
        $row('f-have-has-agreement', 'Have got / has got із займенниками й іменними підметами', [0, 1], ['selects/0', 'inputs/0'], [0, 1], 'Узгодження та наявність у повному повідомленні оцінені конкретними реченнями. Назва професії сама не визначає стать; не всі характеристики тіла або всі іменні підмети перевірено.'),
        $row('f-negative', 'Заперечна have-got модель', [1, 2, 3], ['inputs/1'], [2, 3], 'Own full input та два widget речення зберігають not/got і референти. Повні форми задані за навчальним контекстом; незмінений matcher може приймати доречні скорочення.'),
        $row('f-questions', 'Have/Has перед підметом у питаннях', [1, 2, 3], ['selects/1'], [4], 'Own select та widget перевіряють конкретну have-got question model. Коректне lexical-have питання з do не оголошується неправильним загалом; усі wh-питання не охоплені цими п’ятьма.'),
        $row('f-relationship-meaning', 'Родина як зв’язок, не юридична власність', [2, 4], ['choices/0'], [3], 'Meaning task визначає сімейне повідомлення; widget04 має negative brother relation. Інші значення have/get — їжа, діяльність, обов’язок, минуле — відмежовано видимо, не всі оцінені.'),
        $row('f-short-answer-speakers', 'Коротка відповідь із заданими мовцем та адресатом', [1, 2, 4], ['choices/1'], [], 'Власний meaning choice явно задає мовця і have; у вибраній п’ятірці немає short-answer question. Got не вимагається механічно в кожній короткій відповіді; курсова політика лишається caller-owned.'),
    ],
];
$master = $author['master']; $master['status'] = 'frozen';
$master['sources'] = $sources; $master['editorial_review']['expected_http_metadata'] = $metadata;
$master['editorial_review']['coverage_resolution_log'] = [];
$coverage = ['schema_version' => 1, 'package' => 'm84-word-order-have-got', 'version' => Package::VERSION, 'status' => 'frozen',
    'author_projection_sha256' => $argv[2], 'bank_projection_sha256' => $argv[4], 'private_widget_selection_sha256' => $selectionSha,
    'claim' => 'Finite model/source coverage only: 56 distinct own graded controls, 5 ungraded demos and 30 widget questions remain separate. Not a full course, CEFR certification, punctuation or spoken-stress assessment.',
    'level_policy' => 'All six actual practice SQL levels remain NULL. Editorial A1/A1/A2/A2/B2/A1 focus and display ranges do not rewrite exact CEFR filters.',
    'targets' => []];
$ownTotal = 0; $demoTotal = 0; $widgetTotal = 0; $fullTotal = 0; $rowsVector = [];
foreach ($master['lessons'] as $i => $lesson) {
    $owner = Package::OWNERS[$i]; $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $practice = $lesson['blocks'][$slot]['data'];
    $own = []; $demos = [];
    foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) { foreach ($practice[$group] ?? [] as $n => $task) {
        $key = $group.'/'.$n;
        if ($group === 'rephrase' && $n === 0 && $i < 5) {
            if (isset($task['answer']) || empty($task['example_original']) || empty($task['example_target'])) { throw new RuntimeException('M84 demo acquired a grading key.'); }
            $demos[$key] = ['control_key' => $key, 'group' => $group, 'index' => $n, 'graded' => false, 'task_sha256' => Digest::digest($task)];
            continue;
        }
        $entry = ['control_key' => $key, 'group' => $group, 'index' => $n, 'graded' => true,
            'kind' => $group === 'inputs' ? 'full_sentence_manual_token' : $group,
            'task_id_present' => array_key_exists('id', $task), 'task_sha256' => Digest::digest($task)];
        if (array_key_exists('id', $task)) { $entry['task_id'] = $task['id']; }
        $own[$key] = $entry;
        if ($group === 'inputs') { $fullTotal++; }
    } }
    if (count($own) !== ($i < 5 ? 10 : 6) || count($demos) !== ($i < 5 ? 1 : 0)) { throw new RuntimeException('M84 own/demonstration inventory differs.'); }
    $selected = $selection['targets'][$i]['selected']; $audited = $bank['targets'][$i]['first_five'];
    if (count($selected) !== 5 || $selection['targets'][$i]['actual_practice_level'] !== null) { throw new RuntimeException('M84 finite widget count/NULL filter differs.'); }
    foreach ($selected as $n => $s) {
        if ($s['id'] !== $audited[$n]['id'] || $s['uuid'] !== $audited[$n]['uuid'] || $s['condition'] !== $audited[$n]['visible_condition_uk']
            || $s['settled'] !== $audited[$n]['actual_correct_visible'] || $s['settled'] !== $audited[$n]['actual_selected_visible']) {
            throw new RuntimeException('M84 coverage widget identity/condition/settled sentence differs.');
        }
    }
    $seenOwn = []; $seenDemos = []; $seenWidget = []; $resolved = [];
    foreach ($specs[$i] as $spec) {
        $theory = [];
        foreach ($spec['blocks'] as $blockIndex) {
            if (!is_int($blockIndex) || !isset($lesson['blocks'][$blockIndex]) || $blockIndex === $slot
                || $lesson['blocks'][$blockIndex]['type'] === 'navigation-chips') { throw new RuntimeException('M84 coverage has a foreign/non-teaching block reference.'); }
            $theory[] = ['source_index' => $blockIndex, 'block_key' => $lesson['blocks'][$blockIndex]['key'],
                'type' => $lesson['blocks'][$blockIndex]['type'], 'data_sha256' => Digest::digest($lesson['blocks'][$blockIndex]['data'])];
        }
        $resolvedOwn = []; foreach ($spec['own'] as $key) {
            if (!isset($own[$key])) { throw new RuntimeException('M84 coverage cites a missing or ungraded own control: '.$key); }
            $resolvedOwn[] = $own[$key]; $seenOwn[$key] = true;
        }
        $resolvedDemos = []; foreach ($spec['demos'] as $key) {
            if (!isset($demos[$key])) { throw new RuntimeException('M84 coverage cites a missing demo.'); }
            $resolvedDemos[] = $demos[$key]; $seenDemos[$key] = true;
        }
        $widgets = []; foreach ($spec['widgets'] as $n) {
            if (!is_int($n) || !isset($selected[$n])) { throw new RuntimeException('M84 finite widget coverage index is invalid.'); }
            $s = $selected[$n]; $seenWidget[$s['uuid']] = true;
            $widgets[] = ['selected_index' => $n, 'id' => $s['id'], 'uuid' => $s['uuid'], 'level' => $s['level'],
                'function' => $s['coverage'], 'relation_to_source_topic' => 'bounded_model_use_not_exhaustive_classification'];
        }
        $resolved[] = ['key' => $spec['key'], 'source_topic' => $spec['topic'], 'theoretical_reference' => $theory,
            'own_task_references' => $resolvedOwn, 'ungraded_demo_references' => $resolvedDemos, 'selected_widget_questions' => $widgets,
            'coverage_kind' => $resolvedOwn === [] ? ($widgets === [] ? 'theory_only' : 'widget_only') : ($widgets === [] ? 'own_only' : 'own_and_widget'),
            'uncovered_or_link' => $spec['limit'], 'reference_url' => parse_url($lesson['local_url'], PHP_URL_PATH)];
    }
    $keys = array_keys($own); $covered = array_keys($seenOwn); sort($keys); sort($covered);
    $demoKeys = array_keys($demos); $coveredDemos = array_keys($seenDemos); sort($demoKeys); sort($coveredDemos);
    $widgetKeys = array_column($selected, 'uuid'); $coveredWidgets = array_keys($seenWidget); sort($widgetKeys); sort($coveredWidgets);
    if ($keys !== $covered || $demoKeys !== $coveredDemos || $widgetKeys !== $coveredWidgets) {
        throw new RuntimeException('M84 coverage does not account for every own control, separate demo and selected widget.');
    }
    $original = $master['editorial_review']['lesson_reviews'][$owner];
    $master['editorial_review']['coverage_resolution_log'][$owner] = [
        'draft_coverage_map_present' => array_key_exists('coverage_map', $original), 'original_coverage_map' => $original['coverage_map'] ?? null,
        'original_coverage' => $original['coverage'] ?? null, 'resolved_rows_sha256' => hash('sha256', $json($resolved)),
        'reason' => 'Explicit finite M84 editorial resolution; original draft review is retained. C/D rephrase/0 references are demos, not graded evidence. Main semantic review remains a separate gate before materialization.'];
    $master['editorial_review']['lesson_reviews'][$owner]['coverage_map'] = $resolved;
    $master['editorial_review']['lesson_reviews'][$owner]['coverage_status'] = 'Finite source/topic mapping, proposal for main semantic review; no full-course or CEFR claim.';
    $coverage['targets'][] = ['identity' => $owner, 'local_url' => $lesson['local_url'], 'technical_practice_level' => null,
        'editorial_focus_level' => Package::FOCUS_LEVELS[$i], 'display_level' => Package::DISPLAY_LEVELS[$i],
        'actual_selected_levels' => array_column($selected, 'level'), 'own_graded_controls' => array_values($own),
        'ungraded_demos' => array_values($demos), 'selected_widget_ids' => array_column($selected, 'id'),
        'selected_widget_uuids' => array_column($selected, 'uuid'), 'rows' => $resolved];
    $ownTotal += count($own); $demoTotal += count($demos); $widgetTotal += count($selected); $rowsVector[] = count($resolved);
}
if ([$ownTotal, $demoTotal, $fullTotal, $widgetTotal] !== [56, 5, 17, 30] || $rowsVector !== [5, 6, 5, 4, 6, 5]
    || $master['lessons'] !== $author['master']['lessons']) { throw new RuntimeException('M84 coverage changed teaching payloads or exact distinct counts.'); }
$master['editorial_review']['coverage_resolution'] = '31 finite topic/model rows account for56own graded controls,5separate demos and30widget questions. Theory-only, own-only and widget-only limits remain explicit.';
$master['editorial_review']['freeze_boundary'] = ['status' => 'Source-only proposal; not materialized, SQL applied or browser accepted.',
    'required_next' => ['main semantic/source-plan review', 'materialize exact reviewed sources', 'fresh target proof', 'preview/exact diff/exclusive backup/transactional apply',
        'postconditions/repeated no-op', '36actual page states', '68manual/token size cases for17fullinputs', 'all30settled widget answers', 'old-consumer/source/SQL preservation audit']];
$patch = $author['patch']; $patch['status'] = 'frozen';
$bank['status'] = 'frozen'; $bank['pending'] = [];
$bank['review_summary']['human_review'] = false;
$bank['review_summary']['review_kind'] = 'Agent-authored and independently inspected proposal, not human review or live browser proof.';
if (Digest::digest($bank['targets']) !== $targetDigest) { throw new RuntimeException('M84 freeze changed the reviewed bank targets.'); }

// Finite public selection: useful teaching fields + opaque old-state hashes, not full option/hint families.
$publicSelection = ['schema_version' => 1, 'package' => 'm84-word-order-have-got-widget-selection', 'version' => Package::VERSION,
    'status' => 'frozen', 'independent_before_sha256' => $beforeSha, 'private_selection_input_sha256' => $selectionSha,
    'supplement_source_sha256' => $supplementSha,
    'scope' => 'Thirty reviewed teaching conditions and complete canonical sentences;25new insert-only plus5reusedHaveGot. Raw old options/hints/SQL families omitted.',
    'human_review' => false, 'targets' => []];
foreach ($selection['targets'] as $i => $target) {
    $row = ['identity' => $target['identity'], 'local_url' => $author['contract']['targets'][$i]['local_url'],
        'bank_seeders' => $author['contract']['targets'][$i]['bank_seeders'], 'actual_practice_level' => null,
        'selected_levels' => $target['selected_levels'], 'selected' => []];
    foreach ($target['selected'] as $n => $sample) {
        $safe = array_intersect_key($sample, array_flip(['id', 'uuid', 'level', 'source', 'condition', 'translation', 'settled', 'raw_tokens', 'tokens', 'coverage']));
        if (count($safe) !== 10) { throw new RuntimeException('M84 safe teaching selection lost a required field.'); }
        $safe['snapshot_hashes'] = $bank['targets'][$i]['first_five'][$n]['snapshot_hashes'];
        $safe['full_private_snapshot_sha256'] = $bank['targets'][$i]['first_five'][$n]['full_private_snapshot_sha256'];
        $row['selected'][] = $safe;
    }
    $publicSelection['targets'][] = $row;
}
$componentMap = []; $taskMap = $master['editorial_review']['task_map'];
foreach ($patch['targets'] as $i => $target) {
    $old = $author['source_before']['targets'][$i]['before']; $lesson = $master['lessons'][$i];
    foreach ($target['after']['page']['blocks'] as $slot => $block) {
        $prior = $old['page']['blocks'][$slot] ?? null;
        if (strlen($block['body']) > (int) $before['body_capacity']['max_bytes']) { throw new RuntimeException('M84 projected native body exceeds captured TEXT capacity.'); }
        $componentMap[] = ['identity' => $target['identity'], 'source_index' => $slot, 'key' => $lesson['blocks'][$slot]['key'],
            'before_type' => $prior['type'] ?? null, 'type' => $block['type'], 'column' => $block['column'], 'level' => $block['level'] ?? null,
            'uuid' => Digest::uuid($target['identity'], $block, $slot + 1), 'before_block_sha256' => $prior === null ? null : Digest::digest($prior),
            'body_sha256' => hash('sha256', $block['body']), 'new_block' => $prior === null,
            'detail_count' => count(array_filter($lesson['details'], static fn ($d) => $d['owner_block_key'] === $lesson['blocks'][$slot]['key']))];
    }
}
$editorial = ['schema_version' => 1, 'package' => 'm84-word-order-have-got', 'version' => Package::VERSION, 'status' => 'frozen',
    'base_sha' => Package::BASE_SHA, 'review' => 'Agent editorial/source proposal. No independent human review and no live acceptance claimed by freeze.',
    'author_projection_sha256' => $argv[2], 'bank_projection_sha256' => $argv[4], 'prospective_metadata_sha256' => $argv[6],
    'draft_parts_sha256' => $author['draft_input_pins'], 'lesson_reviews' => $master['editorial_review']['lesson_reviews'],
    'coverage_resolution_log' => $master['editorial_review']['coverage_resolution_log'], 'primary_editorial_sources' => $sources,
    'private_review_input_sha256' => $reviewPins, 'expected_http_metadata' => $metadata,
    'navigation_review' => ['path' => Package::NAVIGATION, 'sha256' => hash('sha256', $json($author['navigation_review'])),
        'added_noncurrent_local_urls' => 20, 'unique_destinations' => 5, 'have_got_footer_added' => false,
        'scope' => 'Five existing WordOrder labels/order/current states and native resolver preserved. HaveGot had no footer and none is manufactured.'],
    'coverage_artifact' => 'docs/content/m84-word-order-have-got.coverage.v1.json',
    'source_access_policy' => 'Actual primary access mode retained: direct editorial body, indexed primary text,403and timeout remain distinct. Learner comments are not rules; exercises are original, not copied publisher banks.',
    'real_page_acceptance' => ['status' => 'not_checked_before_freeze', 'required_after_guarded_apply' => true, 'target' => 'http://gramlyze.loc',
        'widths' => [1440, 390, 320], 'themes' => ['light', 'dark'], 'target_states' => 36,
        'full_inputs' => 17, 'manual_token_size_cases' => 68, 'own_graded' => 56, 'ungraded_demos' => 5, 'widget_questions' => 30],
    'boundaries' => [
        '53 continuing WordOrder native blocks plus 5 HaveGot native blocks retain type/order/column/actual NULL level and continuing identity. Only HaveGot appends one native practice.',
        '40source graded positions and5demos retained/corrected;16new graded tasks give56total, including17full manual/token inputs. Dimperatives are full sentences; modes are not double-counted.',
        'All six widgets independently select five fullsentences.25newWordOrder questions supplement preserved fragment banks;HaveGot reuses5existing complete questions. Old shared options/keys/memberships are not rewritten.',
        'All selected question snapshots are exact-bound; complete old SQL families remain private opaque hashes. Teaching token/distractor labels are intentionally public educational data, not raw SQL records.',
        'No visual-source, native validator, formatter, grader, parser or new design change. Short required boundaries/translations/criteria remain basic; details are not manufactured.',
        'All six actual practice levels remainNULL; display ranges and editorial focus are not new exact CEFR filters.',
        'All old raw titles and actual bilingual H1,category/ancestry,canonical,breadcrumb,anchors and protected metadata remain unchanged; prospective descriptions are independently pinned before apply.',
        'A1HaveGot course policy, course sequence/final drills, standalone tests/theoryhelp,EN/PL,category descriptions,saved tests and personalprogress are read-only consumers. No deploy permission follows from freeze.',
        'Main semantic review of this finite coverage resolution is required before source materialization; a source-frozen proposal is not DB/browser completion.',
        'Future live acceptance must separately read all30settled sentences, test17inputs in68cases and all56own tasks plus5demos across36states; Print/noJS/WCAG remain not_checked unless actually verified.',
    ]];
$release->validate($author['source_before'], $patch, $master, $author['contract']);
Package::assertEditorial($author['source_before'], $patch, $master, $author['navigation_review']);
$outputs = [
    Package::BEFORE => $json($author['source_before']), Package::SOURCE => $json($patch), Package::MASTER => $json($master),
    Package::CONTRACT => $json($author['contract']), Package::BINDINGS => $json($author['native_bindings']), Package::NAVIGATION => $json($author['navigation_review']),
    'docs/content/m84-word-order-have-got.components.v1.json' => $json($componentMap),
    'docs/content/m84-word-order-have-got.tasks.v1.json' => $json($taskMap),
    'docs/content/m84-word-order-have-got.editorial.v1.json' => $json($editorial),
    'docs/content/m84-sentence-builder-bank-audit.v1.json' => $json($bank),
    'docs/content/m84-word-order-have-got.coverage.v1.json' => $json($coverage),
    'docs/content/m84-widget-selection.v1.json' => $json($publicSelection),
];
$plain = static function ($v): string {
    $text = preg_replace('~<br\s*/?>|</(?:p|li|div|tr)>~i', "\n", (string) $v);
    return trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
};
$walk = static function (array $data) use (&$walk, $plain): string {
    $out = '';
    foreach ($data as $key => $value) {
        if (in_array($key, ['id', 'source_index', 'key', 'type', 'column', 'accent', 'color', 'icon', 'current', 'design',
            'linked_practice', 'seeder_classes', 'm84_native_v1', 'm84_detail_key', 'level'], true)) { continue; }
        if (is_array($value)) {
            if ($key === 'feedback') { $out .= "Пояснення відповідей:\n\n"; $value = array_values(array_unique($value)); }
            $out .= $walk($value); continue;
        }
        if (is_bool($value) || $value === null || trim((string) $value) === '') { continue; }
        $label = ['answer' => 'Ключ', 'accepted' => 'Прийнятні відповіді', 'after' => 'Умова', 'before' => 'Слова або стимул',
            'title' => 'Пункт', 'subtitle' => 'Пояснення', 'text' => 'Пояснення', 'en' => 'Англійською', 'ua' => 'Українською', 'url' => 'Посилання'][$key] ?? '';
        $out .= ($label === '' ? '' : $label.': ').$plain($value)."\n\n";
    }
    return $out;
};
$md = "# M84 — порядок слів і Have Got / Has Got\n\nАвторська редакція1.0.0: source-only пропозиція до локального застосування після перегляду main. Не звіт про застосовану БД або завершене приймання сайту. Канонічний дизайн і механізм оцінювання незмінні.\n\n";
foreach ($master['lessons'] as $i => $lesson) {
    $md .= '## '.$lesson['preserved_h1']."\n\n[Відкрити локальний урок](".$lesson['local_url'].")\n\n";
    $md .= 'Авторський орієнтир: '.Package::FOCUS_LEVELS[$i].'; фактичний SQL level: NULL; показаний рівень: '.Package::DISPLAY_LEVELS[$i].".\n\n".$plain($lesson['subtitle_html'])."\n\n";
    foreach ($lesson['blocks'] as $block) { $md .= '### '.$block['key']."\n\n".$walk($block['data']); }
    foreach ($lesson['details'] as $detail) { $md .= '### Докладніше: '.$detail['title']."\n\n".$walk($detail['fragment']['data']); }
    $md .= "### П’ять окремих тематичних питань конструктора\n\n";
    foreach ($publicSelection['targets'][$i]['selected'] as $s) {
        $md .= '- '.$s['condition']."\n  Ключ: ".$s['settled']."\n  Переклад: ".$s['translation']."\n\n";
    }
    $md .= "### Межі оцінювання\n\n";
    foreach ($coverage['targets'][$i]['rows'] as $r) { $md .= '- '.$r['source_topic'].': '.$r['uncovered_or_link']."\n"; }
    $md .= "\n";
}
$outputs['docs/content/m84-word-order-have-got.v1.0.0.uk.md'] = rtrim($md)."\n";
foreach (['ab', 'cd', 'ef'] as $group) {
    $name = 'author-'.$group.'-draft.json'; $sha = $author['draft_input_pins'][$name] ?? '';
    $read($name, $sha); $outputs['docs/content/m84-author-parts/'.$group.'.v1.json'] = file_get_contents($dir.'/'.$name);
}
$definitions = []; $beforeBytes = [];
foreach ($patch['targets'] as $i => $target) {
    $path = $target['path']; $definitions[$path] = $json($target['after']);
    $captured = $before['definitions'][$target['identity']];
    if ($captured['path'] !== $path || $author['source_before']['targets'][$i]['before'] !== $captured['definition']) {
        throw new RuntimeException('M84 exact independent source BEFORE differs.');
    }
    foreach ([$root, $wt] as $base) {
        if (!is_file($base.'/'.$path) || is_link($base.'/'.$path) || file_get_contents($base.'/'.$path) !== $captured['bytes']) {
            throw new RuntimeException('M84 active definition changed before freeze proposal: '.$path);
        }
    }
    $beforeBytes[$path] = $captured['bytes'];
}
$checks = ['schema_version' => 1, 'package' => 'm84-word-order-have-got', 'version' => Package::VERSION, 'status' => 'frozen',
    'hash_algorithm' => 'SHA-256 raw UTF-8 bytes', 'pre_hash_checks' => ['utf8_bom' => false, 'eol' => 'LF', 'exactly_one_final_lf' => true, 'trailing_whitespace' => false],
    'independent_before_sha256' => $beforeSha, 'bank_targets_sha256' => $targetDigest, 'unchanged_native_validator_sha256' => $validatorSha,
    'artifacts' => [], 'active_definitions' => [], 'author_inputs' => [], 'private_review_input_sha256' => $reviewPins, 'notes' => $editorial['boundaries']];
$eol = static function (string $path, string $bytes): void {
    if (!mb_check_encoding($bytes, 'UTF-8') || str_contains($bytes, "\r") || str_starts_with($bytes, "\xEF\xBB\xBF")
        || !str_ends_with($bytes, "\n") || str_ends_with($bytes, "\n\n") || preg_match('/[\t ]+$/m', $bytes)) {
        throw new RuntimeException('M84 pre-hash UTF-8/LF/one-EOF policy failed: '.$path);
    }
};
$privacy = static function ($value) use (&$privacy): void {
    if (!is_array($value)) { return; }
    foreach ($value as $key => $child) {
        if (is_string($key) && preg_match('/^(?:created_at|updated_at|started_at|finished_at|row_timestamp|served_root|source_worktree|sql_before|sql_after_http|password|secret|credentials|physical|runtime_identity|option_matches|materialized|allocation)$/iD', $key)) {
            throw new RuntimeException('M84 public proposal contains a private SQL/runtime field.');
        }
        $privacy($child);
    }
};
foreach ([$outputs, $definitions] as $groupIndex => $group) {
    foreach ($group as $path => $bytes) {
        $eol($path, $bytes);
        if (str_ends_with($path, '.json')) { $privacy(json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)); }
        $checks[$groupIndex === 0 ? 'artifacts' : 'active_definitions'][] = ['path' => $path, 'sha256' => hash('sha256', $bytes), 'bytes' => strlen($bytes)];
    }
}
foreach ([Supplement::SOURCE => $supplementSha, $fixturePath => $fixtureSha] as $path => $sha) {
    $bytes = file_get_contents($wt.'/'.$path); $eol($path, $bytes);
    $checks['author_inputs'][] = ['path' => $path, 'sha256' => $sha, 'bytes' => strlen($bytes), 'state' => 'existing_frozen_input_not_rewritten'];
}
$outputs[Package::CHECKSUMS] = $json($checks);
foreach ($outputs as $path => $bytes) {
    $eol($path, $bytes);
    foreach ([$root, $wt] as $base) {
        if (file_exists($base.'/'.$path) || is_link($base.'/'.$path)) { throw new RuntimeException('M84 initial frozen artifact target is not absent: '.$path); }
    }
}
if (count($outputs) !== 17 || count($definitions) !== 6 || count($componentMap) !== 59 || count($coverage['targets']) !== 6) {
    throw new RuntimeException('M84 finite initial source-only output/component/coverage dimensions differ.');
}
$plan = ['schema_version' => 1, 'revision' => Package::VERSION, 'kind' => 'source-only frozen proposal; not materialized or SQL applied',
    'author_projection_sha256' => $argv[2], 'bank_projection_sha256' => $argv[4], 'prospective_metadata_sha256' => $argv[6],
    'bank_targets_sha256' => $targetDigest, 'private_selection_input_sha256' => $selectionSha,
    'public_selection_sha256' => hash('sha256', $outputs['docs/content/m84-widget-selection.v1.json']),
    'outputs' => $outputs + $definitions, 'before_bytes' => $beforeBytes, 'replace_before_bytes' => [], 'remove_before_bytes' => [],
    'artifacts' => $checks['artifacts'], 'active_definitions' => $checks['active_definitions'],
    'checksums_sha256' => hash('sha256', $outputs[Package::CHECKSUMS]), 'coverage_rows' => $rowsVector,
    'own_graded' => $ownTotal, 'ungraded_demos' => $demoTotal, 'full_sentence_inputs' => $fullTotal, 'selected_widget_questions' => $widgetTotal,
    'semantic_review_notes' => ['No draft coverage_map existed; C/D coverage rephrase/0 rows were separated as demos.',
        'E fronting/emphatic-do widget-only; broader wh-classification theory-only.', 'C retained initialNever own-only controlled extension.',
        'F short answers own-only; no widget short-answer claim.', 'All thirty selected questions and all56owncontrols plus5demos accounted; no full-course claim.'],
    'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0, 'active_source_writes' => 0];
$bytes = $json($plan); $handle = fopen($file, 'x');
if (!$handle) { throw new RuntimeException('M84 exclusive private freeze plan unavailable.'); }
try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M84 private freeze plan write incomplete.'); } }
finally { fclose($handle); }
echo $json(['file' => 'freeze-plan-v2.json', 'sha256' => hash('sha256', $bytes), 'bytes' => strlen($bytes),
    'output_files' => count($plan['outputs']), 'new_artifacts' => count($outputs), 'definition_proposals' => count($definitions),
    'coverage_rows' => $rowsVector, 'checksums_sha256' => $plan['checksums_sha256'], 'public_selection_sha256' => $plan['public_selection_sha256'],
    'bank_targets_sha256' => $targetDigest, 'own_graded' => $ownTotal, 'demos' => $demoTotal, 'full_inputs' => $fullTotal, 'widget_questions' => $widgetTotal,
    'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0, 'active_source_writes' => 0]);
