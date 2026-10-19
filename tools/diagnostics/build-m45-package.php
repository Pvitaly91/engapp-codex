<?php

// Mechanical, worktree-only assembly. No application bootstrap, database, HTTP, source sync or Git mutation.
if (PHP_SAPI !== 'cli' || !in_array($argv[1] ?? null, ['--author', '--project'], true) || count($argv) !== 2) { exit(1); }
$root = str_replace('\\', '/', realpath(__DIR__.'/../..'));
if (strcasecmp($root, 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc') !== 0) {
    throw new RuntimeException('M45 assembly is restricted to the reviewed worktree.');
}
require $root.'/vendor/autoload.php';
use App\Support\M26DetailPackage;
use App\Support\M45FutureComparisonsPackage as Package;

const M45_DRAFT_SHAS = [
    'e20e2fecb6c71fd159d54aabf4d9c5f71fea5da6b433021d4e6ba0237e2c9ac6',
    '14eb88be2393b0d137ba65df6e295afa3c0b57bca603e14ec6e9fabbd8ccf69a',
    'a4979bb8df4480e2b7546ff821edac913a1ba4fc49d7f04aa82934aaaa5590cd',
];
const M45_INVENTORY_SHA = '2aa704d62c3e24419bb9b7ef99327e442286f1d9cc830cc851f900fa19aa15c9';
const M45_OWN_BANKS = [
    'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectVsFutureContinuousAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectVsFuturePerfectContinuousAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousVsFuturePerfectContinuousAllLevelsLessonSeeder',
];
const M45_READABLE = 'docs/content/m45-author-readable.v1.0.0.md';
const M45_EDITORIAL = 'docs/content/m45-editorial-notes.v1.0.0.md';
const M45_MAPPING = 'docs/content/m45-presentation-mapping.v1.0.0.md';
const M45_EXPECTED = 'docs/content/m45-author-expected.v1.0.0.json';
const M45_CHECKSUMS = 'docs/content/m45-checksums.v1.0.0.json';
const M45_CANONICAL_CHECKSUMS = 'docs/content/m45-canonical-checksums.v1.0.0.json';

function m45Pretty(array $value): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
}
function m45Read(string $path): array
{
    $bytes = file_get_contents($path);
    if (!is_string($bytes)) { throw new RuntimeException('M45 input unavailable: '.$path); }
    return json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
}
function m45Bytes(string $bytes): string
{
    if (str_contains($bytes, "\r") || str_starts_with($bytes, "\xef\xbb\xbf") || !str_ends_with($bytes, "\n")
        || str_ends_with($bytes, "\n\n") || preg_match('/[ \t]+$/m', $bytes)) {
        throw new RuntimeException('M45 output encoding/whitespace differs.');
    }
    return $bytes;
}
function m45Destination(string $root, string $path): string
{
    if (!preg_match('~^(?:docs/content|database/content-patches|database/seeders/Page_V3/FutureForms)/[a-zA-Z0-9_/.-]+$~D', $path)
        || str_contains($path, '..') || str_contains($path, '//')) { throw new RuntimeException('M45 output path outside allowlist.'); }
    $directory = str_replace('\\', '/', realpath(dirname($root.'/'.$path)));
    if (!str_starts_with(strtolower($directory.'/'), strtolower($root.'/')) || is_link($root.'/'.$path)) {
        throw new RuntimeException('M45 output resolves outside worktree.');
    }
    return $root.'/'.$path;
}
function m45Exclusive(string $root, array $files): void
{
    foreach ($files as $path => $bytes) {
        if (file_exists(m45Destination($root, $path))) { throw new RuntimeException('Do not replace a frozen M45 file: '.$path); }
        m45Bytes($bytes);
    }
    foreach ($files as $path => $bytes) {
        $handle = fopen($root.'/'.$path, 'xb');
        try {
            if (!$handle || fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Incomplete exclusive M45 write.'); }
        } finally { if ($handle) { fclose($handle); } }
    }
}
function m45Examples(array $examples): string
{
    $text = '';
    foreach ($examples as $example) {
        $text .= $example['en']."\n\n".$example['uk']."\n\n";
        if (isset($example['note_uk'])) { $text .= 'Примітка: '.$example['note_uk']."\n\n"; }
    }
    return $text;
}
function m45Detail(array $detail): string
{
    return '#### Докладніше — '.$detail['title']."\n\n".'ID: `'.$detail['id']."`\n\n"
        .implode("\n\n", $detail['paragraphs_uk'])."\n\n".m45Examples($detail['examples']);
}
function m45Readable(array $lessons): string
{
    $text = "# M45 — читабельна авторська редакція 1.0.0\n\n"
        ."Це повна авторська редакція, а не дизайн сторінки. Basic і відповідні поглиблення визначено до проєкції. Ключі практики наведено для авторської перевірки; learner UI показує їх за власним check/no-JS механізмом.\n\n";
    foreach ($lessons as $lesson) {
        $text .= '## '.$lesson['title']."\n\n".$lesson['subtitle_uk']."\n\n".$lesson['hero_intro_uk']."\n\n";
        foreach ($lesson['sections'] as $section) {
            $text .= '### '.$section['title']."\n\n".'ID: `'.$section['id']."`\n\n";
            foreach ($section['points'] as $point) {
                $text .= '#### '.$point['title']."\n\n".'ID: `'.$point['id']."`\n\n"
                    .implode("\n\n", $point['basic_uk'])."\n\n".m45Examples($point['examples']);
                if (isset($point['detail'])) { $text .= m45Detail($point['detail']); }
            }
            foreach ($section['cards'] ?? [] as $card) {
                $text .= '#### '.$card['title']."\n\n".'ID: `'.$card['id']."`\n\n";
                foreach ($card['rows'] as $row) {
                    $text .= $row['label_uk'].' — `'.$row['formula']."`\n\n".m45Examples([$row]);
                }
                $text .= $card['note_uk']."\n\n";
                if (isset($card['detail'])) { $text .= m45Detail($card['detail']); }
            }
            foreach ($section['notes_uk'] ?? [] as $note) { $text .= $note."\n\n"; }
        }
        $text .= "### Практика\n\n";
        foreach ($lesson['practice'] as $task) {
            $text .= '#### '.$task['title']."\n\n".'ID: `'.$task['id']."`\n\n".$task['prompt_uk']."\n\n";
            if (isset($task['context_uk'])) { $text .= $task['context_uk']."\n\n"; }
            foreach ($task['controls'] as $control) {
                $text .= 'Контроль `'.$control['id'].'` — '.$control['label_uk']."\n\n";
                foreach (['stimulus_en', 'stimulus_uk'] as $field) { if (isset($control[$field])) { $text .= $control[$field]."\n\n"; } }
                if (isset($control['tokens'])) { $text .= 'Токени: '.implode(' · ', $control['tokens'])."\n\n"; }
                foreach ($control['options'] ?? [] as $option) { $text .= '- `'.$option['value'].'`: '.$option['label_uk']."\n"; }
                if (isset($control['options'])) { $text .= "\n"; }
                $answer = $control['correct_value'] ?? $control['canonical_answer'];
                $text .= 'Ключ: `'.$answer."`\n\n";
                foreach ($control['accepted_answers'] ?? [] as $alias) { $text .= '- Прийнятний варіант: `'.$alias."`\n"; }
                if (isset($control['accepted_answers']) && $control['accepted_answers']) { $text .= "\n"; }
            }
            $text .= implode("\n\n", $task['feedback']['paragraphs_uk'])."\n\n".m45Examples($task['feedback']['answer_examples']);
        }
    }
    return rtrim($text)."\n";
}
function m45Editorial(array $lessons): string
{
    $text = "# M45 — редакторські зміни та доступ до джерел, 1.0.0\n\n"
        ."Мовний і редакторський проходи виконані моделями до freeze; це не зовнішня мовна сертифікація чи погодження користувачем кожного речення. Після freeze змістова правка потребує нової версії та окремого diff.\n\n";
    foreach ($lessons as $lesson) {
        $text .= '## '.$lesson['title']."\n\n";
        foreach ($lesson['editorial'] as $note) {
            $text .= '- `'.$note['old_field'].'` — '.$note['decision'].': '.$note['reason_uk']."\n";
        }
        $text .= "\n### Фактичний доступ до редакційних джерел\n\n";
        foreach ($lesson['source_access'] as $source) {
            $text .= '#### '.$source['title']."\n\n".'ID: `'.$source['id']."`\n\n"
                .'['.$source['title'].']('.$source['url'].")\n\n".$source['access']."\n\n";
            foreach (['requested_url', 'indexed_url'] as $field) {
                if (isset($source[$field])) { $text .= $field.': ['.$source[$field].']('.$source[$field].")\n\n"; }
            }
            if (isset($source['indexed_url_direct_access'])) { $text .= $source['indexed_url_direct_access']."\n\n"; }
            foreach ($source['verified_claims_uk'] as $claim) { $text .= '- '.$claim."\n"; }
            $text .= "\n";
            if (isset($source['author_inference_uk'])) { $text .= 'Авторський висновок: '.$source['author_inference_uk']."\n\n"; }
            $text .= "Коментарі учнів та видавничі вправи не використано як нормативне джерело; речення видавців не копіювалися. Історичні речення Gramlyze залишено лише для явного контекстного аналізу.\n\n";
        }
    }
    return rtrim($text)."\n";
}
function m45Mapping(array $lessons): string
{
    $text = "# M45 — явне відображення basic/detail, 1.0.0\n\n"
        ."Відображення переносить авторські поля без переказу: subtitle та hero → header; slots 1–5 → відповідні наявні theory blocks; practice → один новий deterministic practice-set. Short basic не обчислюється з довгих абзаців у runtime.\n\n"
        ."У кожному уроці дві картки форм, кожна з трьома рядками: формула, один англійський приклад, точний український переклад. Notes і основні застереження видимі. Details належать лише своїй картці/пункту; вкладених disclosures немає.\n\n";
    foreach ($lessons as $lesson) {
        $text .= '## '.$lesson['title']."\n\n".'Owner: `'.$lesson['identity']."`\n\n"
            .'Local URL: [відкрити]('.$lesson['url'].")\n\n"
            ."| Slot | Section ID | Видимі groups | Detail → owner |\n|---|---|---:|---|\n";
        $details = 0;
        foreach ($lesson['sections'] as $section) {
            $groups = [...$section['points'], ...($section['cards'] ?? [])];
            $bindings = [];
            foreach ($groups as $group) {
                if (isset($group['detail'])) { $bindings[] = '`'.$group['detail']['id'].'` → `'.$group['id'].'`'; $details++; }
            }
            $text .= '| '.$section['slot'].' | `'.$section['id'].'` | '.count($groups).' | '.($bindings ? implode('<br>', $bindings) : '—')." |\n";
        }
        $controls = array_sum(array_map(fn ($task) => count(array_filter($task['controls'], fn ($c) => $c['required'])), $lesson['practice']));
        $text .= "\n".'Разом: '.$details.' details; '.count($lesson['practice']).' tasks; '.$controls." required controls.\n\n"
            ."Практика зберігає exact IDs, stimuli та stimulus_uk, canonical answers, explicit aliases, token order/multisets і feedback. Canonical answer включається до accepted set незалежно від aliases. Усі required parts потрібні для бала; токени проєктуються як manual + source_kind=tokens.\n\n";
        $text .= "### Рішення про поглиблення\n\n";
        foreach ($lesson['sections'] as $section) foreach ([...$section['points'], ...($section['cards'] ?? [])] as $group) {
            if (isset($group['detail'])) {
                $text .= '- `'.$group['detail']['id'].'/reason_uk` — role=`editorial-meta`: '.$group['detail']['reason_uk']."\n";
            }
        }
        $text .= "\n";
    }
    return rtrim($text)."\n";
}
function m45Leaves(mixed $value, string $path, array &$fields): void
{
    if (is_array($value)) {
        foreach ($value as $key => $child) { m45Leaves($child, $path.'/'.str_replace(['~','/'], ['~0','~1'], (string) $key), $fields); }
    } else { $fields[] = ['path' => $path, 'role'=>str_ends_with($path, '/reason_uk') ? 'editorial-meta' : 'author-field', 'value' => $value]; }
}
function m45Source(string $id, string $title, string $url, string $access, array $claims, ?string $inference = null, array $extra = []): array
{
    $record = ['id'=>$id, 'title'=>$title, 'url'=>$url, 'checked_on'=>'2026-10-09', 'access'=>$access, 'verified_claims_uk'=>$claims,
        'comments_used'=>false, 'publisher_examples_copied'=>false];
    if ($inference !== null) { $record['author_inference_uk'] = $inference; }
    return $record + $extra;
}
function m45PeerSources(int $lesson): array
{
    $bc = 'https://learnenglish.britishcouncil.org/free-resources/grammar/';
    $cambridge = 'https://dictionary.cambridge.org/grammar/british-grammar/';
    $simple = $cambridge.'future-perfect-simple-i-will-have-worked-eight-hours';
    $continuous = $cambridge.'future-perfect-continuous-i-will-have-been-working-here-ten-years';
    if ($lesson === 1) {
        return [
            m45Source('m45-b-bc-future', 'British Council: Future continuous and future perfect', $bc.'b1-b2/future-continuous-future-perfect',
                'Успішне пряме web open після redirect зі старого /grammar/b1-b2-grammar/future-continuous-future-perfect. Прочитано повне редакційне пояснення, рядки 21–48; коментарі не використано.',
                ['Моделі форм, завершеність до майбутньої точки та тривалість стану у Future Perfect.']),
            m45Source('m45-b-bc-perfect-aspect', 'British Council: Perfect aspect', $bc.'english-grammar-reference/perfect-aspect',
                'Успішне пряме web open; прочитано редакційні рядки 5–21 та 67–72.',
                ['Ретроспективна перспектива з майбутньої точки; Future Perfect може підсумовувати період роботи.']),
            m45Source('m45-b-bc-present-perfect', 'British Council: Present perfect', $bc.'english-grammar-reference/present-perfect',
                'Успішне пряме web open; прочитано редакційні рядки 5–19 та 129–162.',
                ['Perfect simple зі станами й тривалим проживанням; Continuous має повний допоміжний ланцюжок і зазвичай не вживається зі станами.'],
                'Перенесення загальних perfect-aspect принципів на study/work/live з майбутньою точкою — авторський аналіз, а не цитата майбутнього уроку видавця.'),
            m45Source('m45-b-bc-present-continuous', 'British Council: Present continuous', $bc.'english-grammar-reference/present-continuous',
                'Успішне пряме web open; прочитано редакційні рядки 90–125.', ['Know у звичайному значенні належить до станів і зазвичай не вживається в Continuous.']),
            m45Source('m45-b-bc-simple-continuous', 'British Council: Present perfect simple and continuous', $bc.'b1-b2/present-perfect-simple-continuous',
                'Успішне пряме web open; прочитано повне редакційне пояснення, рядки 23–54.',
                ['Результат проти діяльності; for/how long можуть описувати прості стани; Continuous охоплює одноразову або повторювану діяльність.'],
                'Застосування цих загальних аспектних принципів до майбутньої точки є авторським аналізом.'),
            m45Source('m45-b-cambridge-perfect', 'Cambridge Grammar Today: Future perfect simple', $simple,
                'Пряме відкриття цього URL та /us варіанта дало 403. Прочитано індексовані form/use sections під правильним заголовком, але за URL із невідповідною назвою; це не повне пряме читання та не підтверджений canonical.',
                ['Future Perfect simple може описувати накопичений період роботи або стану, а не лише припинення діяльності.'], null,
                ['indexed_url'=>'https://dictionary.cambridge.org/us/grammar/british-grammar/future-simple-i-will', 'canonical_confirmed'=>false]),
            m45Source('m45-b-cambridge-perfect-continuous', 'Cambridge Grammar Today: Future perfect continuous', $continuous,
                'Пряме відкриття цього URL та /us варіанта дало 403. Через офіційний indexed/search-cache результат прочитано form section і весь use section; не повне пряме читання.',
                ['Повна конструкція will have been + -ing та тривалість діяльності з майбутньої точки; процеси study/work/live.'], null,
                ['indexed_url'=>'https://dictionary.cambridge.org/us/grammar/british-grammar/future-perfect-continuous-i-will-have-been-working-here-ten-years']),
            m45Source('m45-b-cambridge-aspect-comparison', 'Cambridge Grammar Today: Present perfect simple or present perfect continuous',
                'https://dictionary.cambridge.org/de/grammatik/british-grammar/present-perfect-simple-or-present-perfect-continuous',
                'Пряме відкриття дало 403. Прочитано офіційний indexed/search-cache editorial comparison, не пряме читання.',
                ['Допустимі simple/continuous варіанти live; know як стан; finish зазвичай позначає межу й не є звичайною тривалою діяльністю.'],
                'Багатоденний етап завершального доопрацювання у Gramlyze — явно заданий авторський контекст, не видавничий висновок про конкретне речення.'),
        ];
    }
    return [
        m45Source('m45-c-bc-future', 'British Council: Future continuous and future perfect', $bc.'b1-b2/future-continuous-future-perfect',
            'Успішне пряме web open після redirect зі старого /grammar/b1-b2-grammar/future-continuous-future-perfect. Прочитано редакційне пояснення, рядки 21–48; коментарі від рядка 69 виключено.',
            ['will/won’t be + -ing: процес у майбутній момент і тимчасова майбутня діяльність із періодом.']),
        m45Source('m45-c-bc-statives', 'British Council: Stative verbs', $bc.'b1-b2/stative-verbs',
            'Успішне пряме web open; прочитано редакційні рядки 23–65.',
            ['Know у звичайному значенні описує стан, не Continuous; деякі дієслова змінюють state/activity значення залежно від контексту.']),
        m45Source('m45-c-cambridge-continuous', 'Cambridge Grammar Today: Future continuous', $cambridge.'future-continuous-i-will-be-working',
            'Пряме відкриття URL та варіанта ?q=Future+continuous%3A+use дало 403. Прочитано indexed editorial text, не повне пряме читання.',
            ['will/shall + be + -ing і тимчасовий процес у майбутньому.']),
        m45Source('m45-c-cambridge-perfect-continuous', 'Cambridge Grammar Today: Future perfect continuous',
            'https://dictionary.cambridge.org/us/grammar/british-grammar/future-perfect-continuous-i-will-have-been-working-here-ten-years',
            'Пряме web open дало 403. Прочитано індексовані form/use sections; початковий скорочений URL -working був недоступний і не називається canonical.',
            ['will/shall + have + been + -ing; погляд назад із майбутньої точки для тривалості. Числова тривалість не є обов’язковою в кожному реченні.'],
            'Висновки про старі by 8 p.m. і by the time речення — авторський контекстний аналіз, не видавничий вердикт про ці приклади.'),
        m45Source('m45-c-cambridge-perfect-simple', 'Cambridge Grammar Today: Future perfect simple', $simple,
            'Пряме відкриття цього URL і початкового скороченого -worked дало 403. Search повернув editorial FP simple heading/body за URL із невідповідною назвою. Canonical не підтверджено; цей результат не використано для C-specific тверджень.',
            [], null, ['indexed_url'=>'https://dictionary.cambridge.org/us/grammar/british-grammar/future-simple-i-will', 'canonical_confirmed'=>false]),
    ];
}

if ($argv[1] === '--project') {
    if (!preg_match('/^[a-f0-9]{64}$/D', Package::MASTER_SHA) || !preg_match('/^[a-f0-9]{64}$/D', Package::BEFORE_SHA)) {
        throw new RuntimeException('Main must fill reviewed MASTER_SHA and BEFORE_SHA before projection.');
    }
    $beforeBytes = file_get_contents($root.'/'.Package::BEFORE);
    if (!hash_equals(Package::BEFORE_SHA, hash('sha256', $beforeBytes))) { throw new RuntimeException('M45 frozen BEFORE differs.'); }
    $before = json_decode($beforeBytes, true, flags: JSON_THROW_ON_ERROR);
    $master = Package::authorMaster($before, $root);
    $authorChecksums = m45Read($root.'/'.M45_CHECKSUMS);
    foreach ($authorChecksums['files'] as $path => $sha) {
        if (!hash_equals($sha, hash_file('sha256', $root.'/'.$path))) { throw new RuntimeException('M45 frozen author edition differs: '.$path); }
    }
    $projection = Package::project($before, $master);
    $sourceBytes = m45Pretty($projection);
    $sha = hash('sha256', $sourceBytes);
    if (Package::SOURCE_SHA !== 'PENDING' && !hash_equals(Package::SOURCE_SHA, $sha)) { throw new RuntimeException('M45 reviewed projection SHA differs.'); }
    $canonical = [];
    foreach ($projection['targets'] as $i => $target) {
        $destination = m45Destination($root, $target['path']);
        $current = m45Read($destination);
        if ($current !== $before['targets'][$i]['before'] && $current !== $target['after']) { throw new RuntimeException('M45 canonical conflict; no writes.'); }
        $canonical[$target['path']] = m45Bytes(m45Pretty($target['after']));
    }
    $manifest = ['schema'=>'gramlyze.m45.canonical-checksums.v1', 'version'=>'1.0.0', 'base_sha'=>Package::BASE_SHA,
        'master_sha256'=>Package::MASTER_SHA, 'before_sha256'=>Package::BEFORE_SHA, 'source_sha256'=>$sha,
        'expected_origin'=>'Pure projection of frozen author master and independent BEFORE; calculated before canonical writes.',
        'definitions'=>array_map(fn ($bytes)=>hash('sha256', $bytes), $canonical)];
    $files = [Package::SOURCE=>$sourceBytes, M45_CANONICAL_CHECKSUMS=>m45Pretty($manifest)];
    foreach ($files as $path => $bytes) {
        if (file_exists($root.'/'.$path)) {
            if (file_get_contents($root.'/'.$path) !== $bytes) { throw new RuntimeException('Frozen M45 projection differs; no writes.'); }
            unset($files[$path]);
        }
    }
    m45Exclusive($root, $files);
    foreach ($canonical as $path => $bytes) {
        if (file_get_contents($root.'/'.$path) !== $bytes && file_put_contents($root.'/'.$path, $bytes, LOCK_EX) !== strlen($bytes)) {
            throw new RuntimeException('Incomplete scoped M45 canonical transfer.');
        }
    }
    echo m45Pretty(['stage'=>'project', 'source_sha256'=>$sha, 'canonical_definitions'=>$manifest['definitions'], 'root_sync'=>false, 'db_access'=>false]);
    exit;
}

$inventoryPath = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m45-local/m45-before-v1.json';
if (!hash_equals(M45_INVENTORY_SHA, hash_file('sha256', $inventoryPath))) { throw new RuntimeException('M45 independent inventory bytes differ.'); }
$inventory = m45Read($inventoryPath);
if (count($inventory['targets'] ?? []) !== 3 || array_column($inventory['targets'], 'identity') !== Package::OWNERS) {
    throw new RuntimeException('M45 independently observed owners differ.');
}
$before = ['schema'=>'gramlyze.m45.baseline.v1', 'base_sha'=>Package::BASE_SHA,
    'inventory_sha256'=>hash_file('sha256', $inventoryPath), 'inventory_at'=>$inventory['at'], 'targets'=>[]];
$master = ['schema'=>'gramlyze.m45.author.v1', 'version'=>'1.0.0', 'base_sha'=>Package::BASE_SHA,
    'authored_on'=>'2026-10-09', 'lessons'=>[]];
$expected = ['schema'=>'gramlyze.m45.author-expected.v1', 'version'=>'1.0.0',
    'origin'=>'Exact education fields of reviewed drafts, captured before source projection; never generated from AFTER.', 'draft_sources'=>[], 'fields'=>[]];
$ids = [60,61,59];
foreach (Package::OWNERS as $i => $owner) {
    $draftPath = 'docs/content/m45-draft-'.['a','b','c'][$i].'.json';
    $draftBytes = file_get_contents($root.'/'.$draftPath);
    m45Bytes($draftBytes);
    if (!hash_equals(M45_DRAFT_SHAS[$i], hash('sha256', $draftBytes))) { throw new RuntimeException('M45 reviewed draft differs: '.$draftPath); }
    $draft = json_decode($draftBytes, true, flags: JSON_THROW_ON_ERROR);
    $record = $inventory['targets'][$i];
    $definitionBytes = file_get_contents($root.'/'.$record['path']);
    if (!$record['source_db_exact'] || !$record['db_matches_frozen_source'] || $record['canonical_state'] !== 'before'
        || $record['page']['id'] !== $ids[$i] || $record['category_chain'][0]['id'] !== 12
        || array_column($record['category_chain'], 'slug') !== ['maibutni-formy']
        || !hash_equals($record['definition_sha256'], hash('sha256', $definitionBytes))) {
        throw new RuntimeException('M45 independent BEFORE/source/identity conflict.');
    }
    $definition = json_decode($definitionBytes, true, flags: JSON_THROW_ON_ERROR);
    if ($definition['seeder']['class'] !== $owner || $definition['page']['locale'] !== 'uk'
        || $definition['page']['category']['slug'] !== 'maibutni-formy' || count($definition['page']['blocks']) !== 7
        || $record['page']['title'] !== $definition['page']['title'] || $draft['key'] !== $definition['slug']
        || count($draft['sections']) !== 5 || array_column($draft['sections'], 'slot') !== [1,2,3,4,5] || count($draft['practice']) !== 6) {
        throw new RuntimeException('M45 reviewed definition/draft scope differs.');
    }
    $nav = [];
    foreach ($record['navigation'] as $link) {
        if ($link['slot'] !== 6 || !str_starts_with($link['url'], '/theory/maibutni-formy/')) { throw new RuntimeException('M45 navigation resolver result differs.'); }
        $nav[$link['old_url']] = $link['url'];
    }
    $oldNav = json_decode($definition['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
    if (array_keys($nav) !== array_column($oldNav['items'], 'url')) { throw new RuntimeException('M45 navigation order/coverage differs.'); }
    $banks = array_values(array_filter($record['banks'], fn ($bank)=>str_contains($bank['seeder_class'], '\\Polyglot\\') && $bank['linked_types'] === ['4']));
    if (count($banks) !== 1 || $banks[0]['seeder_class'] !== M45_OWN_BANKS[$i] || !$banks[0]['full_own_bank'] || $banks[0]['linked_count'] !== 72 || $banks[0]['global_count'] !== 72
        || $banks[0]['linked_ids'] !== $banks[0]['global_ids']) { throw new RuntimeException('M45 exact own bank inventory differs.'); }
    $bank = ['seeder_class'=>$banks[0]['seeder_class'], 'question_count'=>72, 'question_type'=>'4',
        'question_ids_sha256'=>M26DetailPackage::digest($banks[0]['global_ids'])];
    $inventoryBanks = array_map(fn ($b)=>['seeder_class'=>$b['seeder_class'], 'linked_count'=>$b['linked_count'], 'global_count'=>$b['global_count'],
        'question_types'=>$b['linked_types'], 'linked_ids_sha256'=>M26DetailPackage::digest($b['linked_ids']),
        'global_ids_sha256'=>M26DetailPackage::digest($b['global_ids']), 'full_own_bank'=>$b['full_own_bank']], $record['banks']);
    $before['targets'][] = ['identity'=>$owner, 'path'=>$record['path'], 'definition_sha256'=>$record['definition_sha256'],
        'page_id'=>$record['page']['id'], 'ancestry'=>['maibutni-formy'], 'before'=>$definition, 'navigation_urls'=>$nav,
        'linked_practice'=>['source'=>'theory_links', 'question_types'=>['4'], 'seeder_classes'=>[$bank['seeder_class']]],
        'bank'=>$bank, 'banks_inventory'=>$inventoryBanks];
    unset($draft['draft_status'], $draft['review']);
    if (isset($draft['title']) && $draft['title'] !== $definition['page']['title']) { throw new RuntimeException('M45 author title differs from protected H1.'); }
    $draft['title'] ??= $definition['page']['title'];
    if (!isset($draft['source_access'])) { $draft['source_access'] = m45PeerSources($i); }
    $lesson = ['identity'=>$owner, 'definition_path'=>$record['path'], 'baseline_definition_sha256'=>$record['definition_sha256'],
        'page_id'=>$record['page']['id'], 'theory_path'=>$record['theory_path'], 'url'=>$record['url'], 'category_path'=>['maibutni-formy']] + $draft;
    $master['lessons'][] = $lesson;
    $expected['draft_sources'][] = ['path'=>$draftPath, 'sha256'=>M45_DRAFT_SHAS[$i], 'identity'=>$owner];
    foreach (['subtitle_uk','hero_intro_uk','sections','practice'] as $field) { m45Leaves($draft[$field], '/lessons/'.$i.'/'.$field, $expected['fields']); }
}
$files = [Package::MASTER_PATH=>m45Pretty($master), Package::BEFORE=>m45Pretty($before),
    M45_READABLE=>m45Readable($master['lessons']), M45_EDITORIAL=>m45Editorial($master['lessons']),
    M45_MAPPING=>m45Mapping($master['lessons']), M45_EXPECTED=>m45Pretty($expected)];
$checksums = ['schema'=>'gramlyze.m45.checksums.v1', 'version'=>'1.0.0', 'base_sha'=>Package::BASE_SHA,
    'draft_sources'=>$expected['draft_sources'], 'files'=>array_map(fn ($bytes)=>hash('sha256', $bytes), $files)];
$files[M45_CHECKSUMS] = m45Pretty($checksums);
m45Exclusive($root, $files);
echo m45Pretty(['stage'=>'author', 'files'=>array_map(fn ($bytes)=>hash('sha256', $bytes), $files),
    'counts'=>array_map(function ($lesson) {
        $groups = array_merge(...array_map(fn ($s)=>[...$s['points'], ...($s['cards'] ?? [])], $lesson['sections']));
        return ['key'=>$lesson['key'], 'sections'=>count($lesson['sections']), 'details'=>count(array_filter($groups, fn ($p)=>isset($p['detail']))),
            'tasks'=>count($lesson['practice']), 'controls'=>array_sum(array_map(fn ($t)=>count($t['controls']), $lesson['practice']))];
    }, $master['lessons']), 'expected_fields'=>count($expected['fields']), 'root_sync'=>false, 'db_access'=>false]);
