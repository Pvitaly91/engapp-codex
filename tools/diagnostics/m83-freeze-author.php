<?php

/** Source-only M83 freeze proposal: one exclusive private plan, no active-source/DB/HTTP writes. */
if (PHP_SAPI !== 'cli' || count($argv) !== 7
    || preg_match('/^author-projection-v[1-9][0-9]*\.json$/D', $argv[1]) !== 1
    || preg_match('/^bank-audit-projection-v[1-9][0-9]*\.json$/D', $argv[3]) !== 1
    || preg_match('/^prospective-metadata-v[1-9][0-9]*\.json$/D', $argv[5]) !== 1
    || array_filter([$argv[2], $argv[4], $argv[6]], static fn ($s) => preg_match('/^[a-f0-9]{64}$/D', $s) !== 1)) {
    throw new RuntimeException('M83 requires exact reviewed author/bank/metadata basenames and raw hashes.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = $root.'/storage/app/m75-worktree';
$dir = $root.'/storage/app/seo-m83-local';
require $root.'/vendor/autoload.php';
require_once $wt.'/app/Support/TheoryNativeContentRelease.php';
require_once $wt.'/app/Support/M83B2RevisionBasicGrammar.php';

use App\Support\M83B2RevisionBasicGrammar as Package;
use App\Support\M26DetailPackage as Digest;

if ([$argv[2], $argv[4], $argv[6]] !== [
    '1f52fa46777eca75dae57f0eec493c308cc533b5ba452e7b0d451a186983477a',
    '92f8a23391fd46781ecf5e57e3179f715fc7846d5ff622e0c7be384e5cac7ea2',
    '726f2c55f90a8077879b9d7b8a3eaca95c6e55d7c202a2c3f954ddd2d2c71b0a',
] || !is_dir($dir) || is_link($dir) || is_link($wt)) {
    throw new RuntimeException('M83 exact reviewed input tuple or safe workspace differs.');
}
$read = static function (string $name, string $sha) use ($dir): array {
    $path = $dir.'/'.$name;
    if (!is_file($path) || is_link($path) || !hash_equals($sha, hash_file('sha256', $path))) {
        throw new RuntimeException('M83 reviewed private artifact drift: '.$name);
    }
    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
};
$json = static fn ($value) => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$author = $read($argv[1], $argv[2]); $bank = $read($argv[3], $argv[4]); $metadata = $read($argv[5], $argv[6]);
$independentBeforeSha = 'd522a715b123748b84f52f076b156ccf5224892f138d5429062d9527a843e33e';
$targetDigest = '4a3344fc7ad21f3081b86f75a68ef2f8575c6ea2a5b44facc23b7fa71d1d0daf';
$nativeValidatorSha = '23b89050f061faddd80debce2fefc8dd356a0b49dffa44f79b2200cb5cbbef11';
$selectionPath = 'docs/content/m83-widget-selection.v1.json';
$selectionSha = 'f3ac4c50c2b3f3ca66c0b12516fe44601e6fba1294a41d34fc408c90f27eca40';
if (($author['independent_before_sha256'] ?? null) !== $independentBeforeSha
    || ($bank['independent_before_sha256'] ?? null) !== $independentBeforeSha
    || ($bank['source_proof']['author_projection_sha256'] ?? null) !== $argv[2]
    || ($bank['source_proof']['author_review_sha256'][$selectionPath] ?? null) !== $selectionSha
    || ($metadata['projection_sha256'] ?? null) !== $argv[2]
    || ($metadata['independent_before_sha256'] ?? null) !== $independentBeforeSha
    || !hash_equals($targetDigest, Digest::digest($bank['targets'] ?? []))
    || !hash_equals($nativeValidatorSha, hash_file('sha256', $wt.'/app/Support/TheoryNativeContentRelease.php'))
    || array_column($bank['targets'] ?? [], 'identity') !== Package::OWNERS
    || array_column($metadata['metadata'] ?? [], 'identity') !== Package::OWNERS
    || array_map(static fn ($t) => count($t['first_five'] ?? []), $bank['targets']) !== [5, 5, 5, 5, 5, 5]) {
    throw new RuntimeException('M83 independent source/bank/metadata/native-validator lineage differs.');
}
$release = new App\Support\TheoryNativeContentRelease(Package::draftProfile($author['native_bindings']['bindings']));
$release->validate($author['source_before'], $author['patch'], $author['master'], $author['contract']);
Package::assertEditorial($author['source_before'], $author['patch'], $author['master'], $author['navigation_review']);
foreach ($metadata['native_policy_pins'] ?? [] as $path => $sha) {
    if (!is_file($root.'/'.$path) || is_link($root.'/'.$path) || !hash_equals($sha, hash_file('sha256', $root.'/'.$path))
        || !is_file($wt.'/'.$path) || is_link($wt.'/'.$path)
        || str_replace("\r\n", "\n", file_get_contents($root.'/'.$path)) !== str_replace("\r\n", "\n", file_get_contents($wt.'/'.$path))
        || ($path === 'app/Support/PageMetadata.php'
            && !hash_equals('c5995a47e1f0e6b5346d2ef0191206836e2b0b7384bb8c6d2ecc0259d265bd2d', hash_file('sha256', $wt.'/'.$path)))) {
        throw new RuntimeException('M83 unchanged metadata policy drift: '.$path);
    }
}
if (!is_file($wt.'/'.$selectionPath) || is_link($wt.'/'.$selectionPath)
    || !hash_equals($selectionSha, hash_file('sha256', $wt.'/'.$selectionPath))) {
    throw new RuntimeException('M83 exact reviewed widget selection changed.');
}
$selection = json_decode(file_get_contents($wt.'/'.$selectionPath), true, flags: JSON_THROW_ON_ERROR);
if (array_column($selection['targets'] ?? [], 'identity') !== Package::OWNERS) {
    throw new RuntimeException('M83 selection owner vector differs.');
}
$master = $author['master']; $master['status'] = 'frozen';
$master['editorial_review']['expected_http_metadata'] = $metadata;
$patch = $author['patch']; $patch['status'] = 'frozen';
$bank['status'] = 'frozen'; $bank['pending'] = [];
$bank['review_summary']['human_review'] = true;
$bank['review_summary']['human_review_field_scope'] = 'Legacy acceptance field: main model-assisted author/model review of all 30 exact conditions and settled visible sentences. Not a claim of independent human review or live browser acceptance.';

/**
 * Finite semantic mapping into the five independently reviewed selections, by zero-based selected position.
 * Empty lists are intentional: visible explanations are not automatically assessed by a token sentence.
 */
$widgetRows = [
    [[0, 1], [1, 4], [3], [], [4], []],
    [[0, 4], [1, 2, 3, 4], [0, 4], [1, 3, 4], [0, 1, 2, 3, 4], [2], [], [], [0, 3]],
    [[], [], [], [], [], [], [0, 1, 2, 3, 4]],
    [[0, 1, 2, 3, 4], [], [1, 2, 3], [], [2, 3], [4], [], []],
    [[0, 1, 2, 3, 4], [0, 1, 2, 3, 4], [0, 3], [2], []],
    [[0], [1], [2], [3], [4], []],
];
// This item is NOT evidence for modal passive. It is an explicitly additional B2 revision function.
$additionalWidgetRows = [0 => [1 => [2]]];
$limits = [
    [
        'Оцінено процес у майбутній момент і завершений результат до межі. Present/Past Perfect та інші continuous-моделі лишаються видимими, але не оголошені вичерпно перевіреними.',
        'Модальний пасив перевіряє власне завдання. Питання03 — додаткова перевірка докору modal perfect, не modal passive. Повідомлене очікування не прирівнюємо до фактичного затвердження.',
        'Third Conditional перевірено в явній минулій перспективі; wish, unless, provided that та інші контрасти видимі, але кожному не приписано окремий оцінюваний контроль.',
        'Непряму мову оцінює власне повне речення зі збереженням Івана. У вибраній п’ятірці немає reported-speech питання; небезпечний кандидат не використовується.',
        'Власне речення містить relative clause; widget перевіряє поступку й пасив. Інші relative/linking моделі не вважаються повністю оціненими.',
        'Назву процесу й relative clause оцінює власне речення. У widget немає nominalisation. Повні форми потрібні лише за прямою умовою конкретного завдання, не за загальною забороною B2.',
    ],
    [
        'Widget використовує іменники в реченнях, а клас слова визначають власний select і повні розбори. Не всі noun patterns оцінено.',
        'Widget дає вживання lexical verb, be, частотного прислівника й минулої форми; класифікацію та is sleeping перевіряють власні розбори. Це не повний тест часової системи.',
        'Прикметники використовуються перед іменниками; контекст fast і слово-клас перевіряють власні controls. Усі adjective complements не охоплено.',
        'Quickly, often і yesterday є у вибраних реченнях; власні tasks розрізняють good/well, hard/hardly і fast. Суфікс -ly не є універсальним класифікатором.',
        'Widget містить personal/object pronouns, але не dummy it. Погодне it перевіряє окреме власне meaning-завдання; не вимагаємо неназваного попереднього предмета.',
        'Widget представляє at school; власні select/розбори/речення мають to/on/in. Не заявлено оцінювання всіх прийменникових значень.',
        'Conjunction оцінено у семислівному розборі та власному реченні про Олю. Вибрана п’ятірка не містить окремої conjunction-конструкції.',
        'Wow є в повному десятислівному розборі, а не у widget. Інтонація та весь каталог вигуків не оцінюються.',
        'Widget використовує an і My; власні розбори та речення дають явний article/determiner аналіз. Це не вичерпний курс артиклів.',
    ],
    [
        'Три власні selects класифікують S/V/O у повному реченні. Widget у рядку нейтрального порядку перевіряє складання, не автоматичну назву членів речення.',
        'Проста частина, складений підмет і багатослівна дієслівна група пояснені. Окремого parser для підрахунку частин або всіх таких моделей немає.',
        'Пасивний підмет, стан і початкова обставина є видимими межами визначення. Вибрана п’ятірка не оцінює пасив чи контекстне винесення.',
        'Give/send/buy та залежність to/for від дієслова пояснені. У вибраній п’ятірці немає double-object alternation; довільний to/for key не створено.',
        'Meaning-task перевіряє вже правильне S–V без вигаданого object. У widget ця модель окремо не оцінюється; весь словник неперехідних уживань не охоплено.',
        'Meaning-task відділяє subject complement happy від Object. У вибраній п’ятірці немає окремого linking-complement контролю.',
        'Три full inputs і дві corrections оцінюють нейтральний порядок та збереження учасників/часу. Widget містить нейтральний SVO, додаток із означенням, кінцеву обставину, заперечну дієслівну групу й питання як межу моделі. Контекстне fronting не заборонене.',
    ],
    [
        'Власні selects явно називають вісь класифікації; widget конструює приклади різних форм. Він не замінює класифікаційне завдання й не підтверджує істинність тверджень.',
        'Meaning-task відрізняє запит інформації від готової позитивної відповіді. Окремого widget на цей висновок немає.',
        'Власні перетворення й вибрані simple lexical-verb питання/заперечення перевіряють do-support. Усі часи й можливі скорочення не охоплено.',
        'Be/modals і subject-wh boundary пояснені. У вибраній п’ятірці немає modal/be або Who-as-subject перетворення; Where do you live не видається за subject-wh контроль.',
        'Власний input і два widget питання вимагають нейтральної повної форми. Розмовне перепитування не оголошено неграматичним; інтонація не оцінюється.',
        'Власні be/you corrections мають явний критерій; widget Open the door перевіряє звичайний base imperative, не контраст emphatic you.',
        'Meaning-task перевіряє negative imperative та відсутність факту виконання. Негативного імператива чи негативного питання у вибраному widget немає.',
        'Пунктуація, please та let’s лишаються в basic. Сам знак або текстове введення не є тестом інтонації чи всіх відтінків ввічливості.',
    ],
    [
        'Вибрано лише зразки заборони, інструкції, прохання, застереження та дії. Не всі шість прагматичних функцій assessed; факт виконання не виводиться з форми.',
        'Widget перевіряє звичайний опущений підмет; Listen carefully не потребує вигаданого object. Emphatic you має явний власний критерій, але окремого widget-контрасту немає.',
        'Widget має negative lexical imperative і affirmative be, але не negative be. Власні tasks зберігають don’t be; No parking/No smoking є видимим розмежуванням, не оціненим каталогом.',
        'Widget Please wait here представляє прохання. Let’s/let’s not видимі; власні meaning-tasks перевіряють спільну дію й межі please, не всі соціальні відтінки.',
        'Пунктуація пояснена. Текстовий score не перевіряє інтонацію, емоційний тон або сам знак оклику.',
    ],
    [
        'Widget оцінює додавання в повному реченні; не всі синтаксичні різновиди and чи причинний висновок.',
        'Власні умови задають контраст явно; widget дає ще один заданий контраст. Rich/smart не стають соціальними правилами.',
        'Widget представляє альтернативу, не універсальне «тільки одне». Заперечення обох варіантів оцінює власний meaning-task; demo лишається без балів.',
        'Widget дає причину після головної частини; because of і початкову because-частину оцінюють/пояснюють власні controls. Напрямок причини не змінюється.',
        'Widget so має фактичну позначку B1, яку збережено при SQL NULL практики. Його коротке речення відображається без коми; це не контроль вставляння коми. Purpose/resultative so that не оцінюється вичерпно.',
        'Перенесений source control порівнює змістовні A/B ключі про межу because-частини. Production лише коми не видається за розрізнюваний тест у normalizer.',
    ],
];
$coverage = ['schema_version' => 1, 'package' => 'm83-b2-revision-basic-grammar', 'version' => Package::VERSION, 'status' => 'frozen',
    'author_projection_sha256' => $argv[2], 'bank_projection_sha256' => $argv[4], 'widget_selection_sha256' => $selectionSha,
    'claim' => 'Finite source-topic/model coverage: 60 own graded controls, 5 ungraded demos and 30 selected widget questions are distinct; not a complete course or CEFR assessment.',
    'level_policy' => 'Actual practice levels are B2/NULL/NULL/NULL/NULL/NULL. Editorial focus A1 does not rewrite a NULL filter; B/F selected A2/B1 labels remain actual.',
    'course_boundary' => [
        'old_consumer_before_basename' => 'before-old-consumers-v2.json',
        'old_consumer_before_sha256' => '327edffb5a1949d3330b0799e8125d7e70afecd37cf239954600ff55a93bbd04',
        'source_course_before_sha256' => '0d7129811de13e6991c84c5b6d6b2b62d837458ab95e352e87bc7cf6e33186ef',
        'b2' => ['lessons' => 16, 'ordered_question_memberships' => 768, 'final_actual_questions' => 48,
            'raw_completion' => ['minimum_rating_percent' => 80, 'minimum_correct' => 38],
            'separate_public_manifest_normalization' => ['rolling_window' => 100, 'min_rating' => 4.5]],
        'contractions' => 'No blanket B2/course flag was found. One LinkingWordsB2 item explicitly asks a full form; lesson1 word_order_policy explicitly allows contractions. Keep each caller-owned criterion.',
        'access' => 'Independent read-only before recorded 48 HTTP 200; 16 existing branded course-compose 404 remain not_checked, not a reason to bypass access gates. No course JS/progress endpoint was executed.',
        'policy' => 'Standalone theory changes do not rewrite course lessons, final drill, question models/options/hints, memberships, completion or progress.',
    ], 'targets' => []];
$master['editorial_review']['coverage_resolution_log'] = [];
$allOwnCount = 0; $allWidgetCount = 0;
foreach ($master['lessons'] as $i => $lesson) {
    $owner = Package::OWNERS[$i]; $review = $master['editorial_review']['lesson_reviews'][$owner];
    $rows = $review['coverage_map'] ?? null; $selected = $selection['targets'][$i]['selected'] ?? [];
    if (!is_array($rows) || count($rows) !== count($widgetRows[$i]) || count($rows) !== count($limits[$i])
        || count($selected) !== 5 || ($selection['targets'][$i]['actual_practice_level'] ?? null) !== Package::LEVELS[$i]
        || array_column($bank['targets'][$i]['first_five'], 'uuid') !== array_column($selected, 'uuid')) {
        throw new RuntimeException('M83 finite coverage/source/actual selection dimensions differ.');
    }
    foreach ($selected as $n => $question) {
        $auditQuestion = $bank['targets'][$i]['first_five'][$n];
        if ($auditQuestion['id'] !== $question['id']
            || $auditQuestion['visible_condition_uk'] !== $question['condition']
            || $auditQuestion['actual_correct_visible'] !== $question['settled']
            || $auditQuestion['actual_selected_visible'] !== $question['settled']) {
            throw new RuntimeException('M83 finite coverage identity/condition/settled answer differs from actual audit.');
        }
    }
    $practice = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
    $own = []; $byId = [];
    foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
        foreach ($practice[$group] ?? [] as $index => $task) {
            if ($group === 'rephrase' && $index === 0 && $i > 0) { continue; }
            $key = $group.'/'.$index;
            $entry = ['group' => $group, 'index' => $index, 'control_key' => $key,
                'task_id_present' => array_key_exists('id', $task),
                'kind' => $group === 'inputs' ? (in_array($index, Package::SENTENCE_INPUT_INDICES[$i], true) ? 'full_sentence_manual_token' : 'word_form')
                    : ($group === 'rephrase' && $i === 1 ? 'complete_grammar_analysis' : $group)];
            if (array_key_exists('id', $task)) { $entry['task_id'] = $task['id']; $byId[$task['id']] = $key; }
            $own[$key] = $entry;
        }
    }
    if (count($own) !== Package::GRADED_COUNTS[$i]) { throw new RuntimeException('M83 own task inventory differs.'); }
    $resolveOwn = static function (mixed $ref) use ($own, $byId): array {
        if (is_array($ref)) { $key = ($ref['group'] ?? '').'/'.($ref['index'] ?? ''); }
        elseif (is_string($ref) && isset($byId[$ref])) { $key = $byId[$ref]; }
        elseif (is_string($ref) && preg_match('/^retained-(selects|inputs)-([0-9]+)$/D', $ref, $m)) { $key = $m[1].'/'.$m[2]; }
        elseif (is_string($ref) && preg_match('/^retained-analysis-([0-9]+)$/D', $ref, $m)) { $key = 'rephrase/'.$m[1]; }
        elseif (is_string($ref)) { $key = $ref; }
        else { throw new RuntimeException('M83 unsupported finite own-task reference.'); }
        if (!isset($own[$key])) { throw new RuntimeException('M83 coverage cites a foreign/ungraded own control.'); }
        return $own[$key];
    };
    $coveredOwn = []; $coveredWidget = []; $resolved = [];
    foreach ($rows as $r => $row) {
        $refs = [...($row['own_tasks'] ?? $row['own_task_ids'] ?? []), ...($row['own_task_references'] ?? [])];
        $resolvedOwn = [];
        foreach ($refs as $ref) { $item = $resolveOwn($ref); $resolvedOwn[$item['control_key']] = $item; $coveredOwn[$item['control_key']] = true; }
        $theory = $row['theoretical_reference'] ?? $row['theory_reference'] ?? null;
        if (is_string($theory)) { $theory = [$theory]; }
        if (!is_array($theory) || $theory === [] || array_filter($theory, static fn ($v) => !is_string($v) || trim($v) === '')) {
            throw new RuntimeException('M83 coverage needs a retained source-model reference.');
        }
        $makeWidgets = static function (array $indices, string $relation) use ($selected, &$coveredWidget): array {
            $out = [];
            foreach ($indices as $n) {
                if (!is_int($n) || !isset($selected[$n])) { throw new RuntimeException('M83 finite widget coverage position differs.'); }
                $q = $selected[$n]; $coveredWidget[$q['uuid']] = true;
                $out[] = ['id' => $q['id'], 'uuid' => $q['uuid'], 'level' => $q['level'],
                    'function' => $q['coverage'], 'relation_to_source_topic' => $relation];
            }
            return $out;
        };
        $widgets = $makeWidgets($widgetRows[$i][$r], 'bounded_model_use_not_automatic_classification');
        $additional = $makeWidgets($additionalWidgetRows[$i][$r] ?? [], 'additional_revision_not_modal_passive');
        $entry = ['source_topic' => $row['source_topic'], 'theoretical_reference' => $theory,
            'own_task_references' => array_values($resolvedOwn),
            'own_task_ids' => array_values(array_filter(array_column($resolvedOwn, 'task_id'), 'is_string')),
            'selected_widget_questions' => $widgets, 'additional_selected_widget_questions' => $additional,
            'uncovered_or_link' => $limits[$i][$r],
            'coverage_kind' => $resolvedOwn === [] ? ($widgets === [] ? 'theory_only' : 'widget_only')
                : ($widgets === [] ? 'own_only' : 'own_and_widget'),
            'reference_url' => $row['reference_url'] ?? parse_url($lesson['local_url'], PHP_URL_PATH)];
        if (isset($row['key'])) { $entry = ['key' => $row['key']] + $entry; }
        $resolved[] = $entry;
    }
    $ownKeys = array_keys($own); $coveredKeys = array_keys($coveredOwn); sort($ownKeys); sort($coveredKeys);
    $widgetKeys = array_column($selected, 'uuid'); $coveredWidgets = array_keys($coveredWidget); sort($widgetKeys); sort($coveredWidgets);
    if ($ownKeys !== $coveredKeys || $widgetKeys !== $coveredWidgets) {
        throw new RuntimeException('M83 finite coverage must account for every graded own control and each selected widget.');
    }
    $master['editorial_review']['coverage_resolution_log'][$owner] = ['original_draft_rows' => $rows,
        'resolved_rows_sha256' => hash('sha256', $json($resolved)),
        'reason' => 'Only editorial coverage is resolved against actual own positions and audited selected identities; lesson/task payloads stay byte-equivalent to the reviewed projection.'];
    $master['editorial_review']['lesson_reviews'][$owner]['coverage_map'] = $resolved;
    $master['editorial_review']['lesson_reviews'][$owner]['coverage_status'] = 'Resolved finite source-topic mapping; draft provenance retained, no full-course/CEFR/intonation claim.';
    $coverage['targets'][] = ['identity' => $owner, 'local_url' => $lesson['local_url'],
        'technical_practice_level' => Package::LEVELS[$i], 'editorial_focus_level' => Package::FOCUS_LEVELS[$i],
        'display_level' => Package::DISPLAY_LEVELS[$i], 'actual_selected_levels' => array_column($selected, 'level'),
        'own_graded_controls' => array_values($own), 'ungraded_demos' => $i === 0 ? 0 : 1,
        'selected_widget_ids' => array_column($selected, 'id'), 'selected_widget_uuids' => array_column($selected, 'uuid'), 'rows' => $resolved];
    $allOwnCount += count($own); $allWidgetCount += count($selected);
}
if ($allOwnCount !== 60 || $allWidgetCount !== 30 || $master['lessons'] !== $author['master']['lessons']) {
    throw new RuntimeException('M83 coverage changed learning data or distinct task totals.');
}
$master['editorial_review']['coverage_resolution'] = '41 finite source-topic rows account for 60 own controls and 30 selected widget questions. Additional modal-perfect evidence is explicitly not modal passive. Draft review/provenance is preserved separately.';
$master['editorial_review']['freeze_boundary'] = [
    'status' => 'source-frozen proposal only; not local apply or real-browser acceptance',
    'completed_source_gates' => ['independent source/SQL/HTTP BEFORE', 'finite author/task projection', 'actual selected bank audit', 'prospective native metadata', 'verified navigation data'],
    'pending_after_parent_plan_review' => ['materialize exact versioned artifacts/definitions', 'fresh target proof', 'preview/exact review/exclusive backup/transactional apply',
        'postconditions and repeated no-op', '36 actual browser states and64manual/token size cases', '30widget settled answers', 'all-old-row/course/progress final audit'],
];
$editorial = ['schema_version' => 1, 'base_sha' => Package::BASE_SHA, 'package' => 'm83-b2-revision-basic-grammar',
    'version' => Package::VERSION, 'status' => 'frozen',
    'review' => 'Agent-authored and independently inspected source proposal; not human review, not proof of DB/browser completion. Publisher exercises are not copied.',
    'author_projection_sha256' => $argv[2], 'bank_projection_sha256' => $argv[4], 'prospective_metadata_sha256' => $argv[6],
    'draft_parts_sha256' => $author['draft_sha256'], 'lesson_reviews' => $master['editorial_review']['lesson_reviews'],
    'projection_edits' => $master['editorial_review']['projection_edits'],
    'coverage_resolution_log' => $master['editorial_review']['coverage_resolution_log'],
    'expected_http_metadata' => $metadata,
    'navigation_review' => ['path' => Package::NAVIGATION, 'sha256' => hash('sha256', $json($author['navigation_review'])),
        'added_local_urls' => 30, 'unique_destinations' => 12,
        'scope' => 'All original navigation titles/labels/order/current flags and shared resolver preserved; exact verified destination data only.'],
    'coverage_artifact' => 'docs/content/m83-b2-revision-basic-grammar.coverage.v1.json',
    'source_access_policy' => 'Each lesson retains exact publisher URLs and actual access mode: direct body, indexed editorial excerpt,403or timeout are distinguished. Learner comments are not rules; no claim that every publisher page was directly readable.',
    'real_page_acceptance' => ['status' => 'not_checked_before_freeze', 'required_after_guarded_apply' => true, 'target' => 'http://gramlyze.loc',
        'widths' => [1440, 390, 320], 'themes' => ['light', 'dark'], 'target_states' => 36,
        'full_inputs' => 16, 'manual_token_size_cases' => 64, 'own_graded' => 60, 'ungraded_demos' => 5, 'widget_questions' => 30],
    'course_boundary' => $coverage['course_boundary'],
    'boundaries' => [
        '71continuing native blocks retain exact IDs/UUIDs/order/types/columns/levels. Only A appends one B2 practice; B-F continue their original practice sets.',
        '42source graded positions are retained/corrected, including one finite comma-control transfer;18new graded controls are separate. Five demos remain ungraded. There are16full manual/token inputs,3word-form inputs and2complete grammar-analysis controls.',
        'All six widgets select30existing questions from exact thematic banks;0question-family writes/new questions. B-F SQL NULL is not rewritten to A1 or a range.',
        '60 own controls and 30 widget questions do not certify a CEFR level or exhaust any course. Explicit coverage includes theory-only and unassessed boundaries.',
        'No visual-source, native validator, shared formatter/grader/parser or course-contraction policy change. The only usage accent correction is finite unsupported purple→slate; supported hero/mistake accents stay as reviewed.',
        'Each retained task ID/source_index absence is preserved; one newly represented retained semantic-choice control receives its explicit stable ID. Source task ledger and component maps remain checksum-bound.',
        'Full criteria use supported visible fields; manual and token modes share grading. Declared contractions and full forms are contextual aliases, not a blanket course policy.',
        'H1,raw titles,slugs,categories,ancestry,tags,anchors and protected canonical/robots/breadcrumb metadata remain exact. Prospective descriptions use unchanged local metadata policies.',
        'Standalone theory/source sync does not authorize course,final-drill,membership,score,enrollment,progress,EN/PL,category-description or production writes.',
        'Freeze-plan generation performs no DB/HTTP or active-source writes. Real apply/backup/no-op/browser/postconditions remain distinct required gates.',
    ]];
$release->validate($author['source_before'], $patch, $master, $author['contract']);
Package::assertEditorial($author['source_before'], $patch, $master, $author['navigation_review']);
$outputs = [
    Package::BINDINGS => $json($author['native_bindings']), Package::BEFORE => $json($author['source_before']),
    Package::SOURCE => $json($patch), Package::MASTER => $json($master), Package::CONTRACT => $json($author['contract']),
    Package::NAVIGATION => $json($author['navigation_review']),
    'docs/content/m83-b2-revision-basic-grammar.components.v1.json' => $json($author['component_map']),
    'docs/content/m83-b2-revision-basic-grammar.tasks.v1.json' => $json($author['task_map']),
    'docs/content/m83-b2-revision-basic-grammar.editorial.v1.json' => $json($editorial),
    'docs/content/m83-sentence-builder-bank-audit.v1.json' => $json($bank),
    'docs/content/m83-b2-revision-basic-grammar.coverage.v1.json' => $json($coverage),
];
$plain = static function (mixed $value): string {
    $text = preg_replace('~</(?:p|li|div|tr)>~i', "\n", (string) $value);
    return trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
};
$walk = static function (array $data) use (&$walk, $plain): string {
    $out = '';
    foreach ($data as $key => $value) {
        if (in_array($key, ['id', 'source_index', 'key', 'type', 'column', 'accent', 'color', 'icon', 'current', 'design',
            'linked_practice', 'seeder_classes', 'm83_native_v1', 'm83_detail_key', 'level'], true)) { continue; }
        if (is_array($value)) {
            if ($key === 'feedback') { $out .= "Пояснення відповідей:\n\n"; $value = array_values(array_unique($value)); }
            $out .= $walk($value); continue;
        }
        if (is_bool($value) || $value === null || trim((string) $value) === '') { continue; }
        $label = ['answer' => 'Ключ', 'accepted' => 'Прийнятні відповіді', 'prompt' => 'Умова', 'after' => 'Умова',
            'before' => 'Слова або стимул', 'title' => 'Пункт', 'subtitle' => 'Пояснення', 'text' => 'Пояснення',
            'en' => 'Англійською', 'ua' => 'Українською', 'url' => 'Посилання'][$key] ?? '';
        $out .= ($label === '' ? '' : $label.': ').$plain($value)."\n\n";
    }
    return $out;
};
$md = "# M83 — B2 повторення та базова граматика\n\nАвторська редакція1.0.0: заморожена пропозиція для локального застосування. Дизайн і механізм оцінювання не змінюються. Цей текст не є звітом про завершене приймання сайту.\n\n";
foreach ($master['lessons'] as $i => $lesson) {
    $md .= '## '.$lesson['preserved_h1']."\n\n[Відкрити локальний урок](".$lesson['local_url'].")\n\n";
    $md .= 'Редакційний орієнтир практики: '.Package::FOCUS_LEVELS[$i].'; фактичний SQL level: '.(Package::LEVELS[$i] ?? 'NULL')."; показаний діапазон: ".Package::DISPLAY_LEVELS[$i].".\n\n";
    $md .= $plain($lesson['subtitle_html'])."\n\n";
    foreach ($lesson['blocks'] as $block) { $md .= '### '.$block['key']."\n\n".$walk($block['data']); }
    foreach ($lesson['details'] as $detail) { $md .= '### Докладніше: '.$detail['title']."\n\n".$walk($detail['fragment']['data']); }
    $md .= "### Межі оцінювання\n\n";
    foreach ($coverage['targets'][$i]['rows'] as $row) { $md .= '- '.$row['source_topic'].': '.$row['uncovered_or_link']."\n"; }
    $md .= "\n";
}
$outputs['docs/content/m83-b2-revision-basic-grammar.v1.0.0.uk.md'] = rtrim($md)."\n";
$definitions = [];
foreach ($patch['targets'] as $target) { $definitions[$target['path']] = $json($target['after']); }
$checks = ['schema_version' => 1, 'package' => 'm83-b2-revision-basic-grammar', 'version' => Package::VERSION, 'status' => 'frozen',
    'hash_algorithm' => 'SHA-256 raw UTF-8 bytes',
    'pre_hash_checks' => ['utf8_bom' => false, 'eol' => 'LF', 'exactly_one_final_lf' => true, 'trailing_whitespace' => false],
    'independent_before_sha256' => $independentBeforeSha, 'bank_targets_sha256' => $targetDigest,
    'unchanged_native_validator_sha256' => $nativeValidatorSha,
    'artifacts' => [], 'active_definitions' => [], 'author_inputs' => [], 'notes' => $editorial['boundaries']];
$eol = static function (string $path, string $bytes): void {
    if (!mb_check_encoding($bytes, 'UTF-8') || str_contains($bytes, "\r") || str_starts_with($bytes, "\xEF\xBB\xBF")
        || !str_ends_with($bytes, "\n") || str_ends_with($bytes, "\n\n") || preg_match('/[\t ]+$/m', $bytes)) {
        throw new RuntimeException('M83 pre-hash UTF-8/LF/single EOF policy failed: '.$path);
    }
};
foreach ([$outputs, $definitions] as $groupIndex => $group) {
    foreach ($group as $path => $bytes) {
        $eol($path, $bytes);
        if (str_ends_with($path, '.json')) { json_decode($bytes, true, flags: JSON_THROW_ON_ERROR); }
        $checks[$groupIndex === 0 ? 'artifacts' : 'active_definitions'][] = ['path' => $path, 'sha256' => hash('sha256', $bytes), 'bytes' => strlen($bytes)];
    }
}
$inputPins = [$selectionPath => $selectionSha,
    'tests/Fixtures/m83-owned-source-hunks.json' => 'e73158bc4d0ecc145865d86c8e72e8b08dda2234771ec7b4003b44413b475dcd'];
foreach ($author['draft_sha256'] as $name => $sha) { $inputPins['docs/content/m83-author-parts/'.$name.'.v1.json'] = $sha; }
foreach ($inputPins as $path => $sha) {
    if (!is_file($wt.'/'.$path) || is_link($wt.'/'.$path) || !hash_equals($sha, hash_file('sha256', $wt.'/'.$path))) {
        throw new RuntimeException('M83 reviewed author/selection/fixture input changed: '.$path);
    }
    $bytes = file_get_contents($wt.'/'.$path); $eol($path, $bytes);
    $checks['author_inputs'][] = ['path' => $path, 'sha256' => $sha, 'bytes' => strlen($bytes)];
}
$outputs[Package::CHECKSUMS] = $json($checks);
$beforeBytes = [];
foreach ($definitions as $path => $bytes) {
    $matches = array_values(array_filter($author['source_before']['targets'], static fn ($t) => $t['path'] === $path));
    if (count($matches) !== 1 || !is_file($wt.'/'.$path) || is_link($wt.'/'.$path)) {
        throw new RuntimeException('M83 exact active BEFORE definition unavailable.');
    }
    $current = file_get_contents($wt.'/'.$path);
    if (json_decode($current, true, flags: JSON_THROW_ON_ERROR) !== $matches[0]['before']) {
        throw new RuntimeException('M83 active definition changed from independent BEFORE.');
    }
    $beforeBytes[$path] = $current;
    foreach ($matches[0]['before']['page']['blocks'] as $slot => $block) {
        if (strlen($patch['targets'][array_search($matches[0]['identity'], Package::OWNERS, true)]['after']['page']['blocks'][$slot]['body']) > 65535) {
            throw new RuntimeException('M83 generated body exceeds the inspected TEXT limit.');
        }
    }
}
foreach ($outputs as $path => $_) {
    if (file_exists($wt.'/'.$path) || is_link($wt.'/'.$path)) {
        throw new RuntimeException('M83 initial frozen artifact destination is not absent: '.$path);
    }
}
if (count($outputs) !== 13 || count($definitions) !== 6
    || array_map(static fn ($t) => count($t['rows']), $coverage['targets']) !== [6, 9, 7, 8, 5, 6]) {
    throw new RuntimeException('M83 exact initial freeze output/coverage vector differs.');
}
$plan = ['schema_version' => 1, 'revision' => Package::VERSION, 'kind' => 'source-only frozen proposal; not materialized or SQL applied',
    'author_projection_sha256' => $argv[2], 'bank_projection_sha256' => $argv[4],
    'prospective_metadata_sha256' => $argv[6], 'bank_targets_sha256' => $targetDigest,
    'outputs' => $outputs + $definitions, 'before_bytes' => $beforeBytes,
    'replace_before_bytes' => [], 'remove_before_bytes' => [],
    'artifacts' => $checks['artifacts'], 'active_definitions' => $checks['active_definitions'],
    'checksums_sha256' => hash('sha256', $outputs[Package::CHECKSUMS]),
    'coverage_rows' => [6, 9, 7, 8, 5, 6], 'own_graded' => 60, 'ungraded_demos' => 5, 'selected_widget_questions' => 30,
    'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0, 'active_source_writes' => 0];
$file = $dir.'/freeze-plan-v1.json';
if (file_exists($file) || is_link($file)) { throw new RuntimeException('M83 exclusive private freeze plan already exists.'); }
$bytes = $json($plan); $handle = fopen($file, 'x');
if (!$handle) { throw new RuntimeException('M83 private freeze plan unavailable.'); }
try {
    if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M83 private freeze plan incomplete.'); }
} finally { fclose($handle); }
echo $json(['file' => 'freeze-plan-v1.json', 'sha256' => hash('sha256', $bytes), 'bytes' => strlen($bytes),
    'output_files' => count($plan['outputs']), 'new_artifacts' => count($outputs), 'definition_proposals' => count($definitions),
    'coverage_rows' => $plan['coverage_rows'], 'checksums_sha256' => $plan['checksums_sha256'],
    'bank_targets_sha256' => $targetDigest, 'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0, 'active_source_writes' => 0]);
