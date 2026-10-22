<?php

/** Pure snapshot-to-teaching projection. No application bootstrap, SQL, HTTP, seeding or active-source write. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 3
    || preg_match('/^--author-sha256=([a-f0-9]{64})$/D', $argv[1], $argument) !== 1
    || !in_array($argv[2],['bank-audit-projection-v1.json','bank-audit-projection-v2.json'],true)) {
    throw new RuntimeException('M76 requires an exact reviewed author SHA256 and the exclusive private projection basename.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = $root.'/storage/app/m75-worktree';
$private = $root.'/storage/app/seo-m76-local';
$normal = static fn (string $path): string => strtolower(str_replace('\\', '/', (string) realpath($path)));
if ($normal(dirname(__DIR__, 2)) !== $normal($wt) || !is_dir($private) || is_link($private) || is_link($wt)) {
    throw new RuntimeException('M76 exact worktree/private paths differ.');
}
$output = $private.'/'.$argv[2];
if (file_exists($output) || is_link($output)) { throw new RuntimeException('M76 private projection already exists or is linked.'); }
$beforeSha = 'b0cae6f629aae1b4354aefcf8d704b73030ef2022846c2cd1ea2f9f5d3a05985';
$authorSha = 'e7b80b784c1caf783d15429f859fa3c96f90bd88131c841152ba083ad202e910';
if (!hash_equals($authorSha,$argument[1])) { throw new RuntimeException('M76 reviewed author hash differs.'); }
$read = static function (string $path, string $sha): array {
    if (!is_file($path) || is_link($path) || !hash_equals($sha, hash_file('sha256', $path))) {
        throw new RuntimeException('M76 exact independent input is missing, linked or changed.');
    }
    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
};
$before = $read($private.'/before-v1.json', $beforeSha);
$author = $read($private.'/author-projection-v3.json', $authorSha);
require $root.'/vendor/autoload.php';
require_once $wt.'/app/Support/TheoryNativeContentRelease.php';
require_once $wt.'/app/Support/M76PronounRelations.php';

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Support\ComposeTokenCase;
use App\Support\M26DetailPackage as Digest;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M76PronounRelations as Package;
use App\Support\TheoryBuilderSelectionPresentation;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

if (($before['base_sha'] ?? null) !== Package::BASE_SHA
    || ($author['source_before']['base_sha'] ?? null) !== Package::BASE_SHA
    || ($before['sql_before'] ?? null) !== ($before['sql_after_http'] ?? null)) {
    throw new RuntimeException('M76 independent BEFORE/package lineage differs.');
}
Package::validate($author['source_before'],$author['patch'],$author['master'],$author['contract']);

/** The six hashes exactly mirror the shared matcher, including null position and the three loaded pivot fields. */
$runtimeParts = static function (array $snapshot): array {
    $question = $snapshot['question']; $ordered = [];
    $keys = array_keys($question); $expected = Rows::ROW_COLUMNS['questions']; sort($keys); sort($expected);
    if ($keys !== $expected) { throw new RuntimeException('M76 original question is not the complete fourteen-column snapshot.'); }
    foreach (Rows::ROW_COLUMNS['questions'] as $field) { $ordered[$field] = $question[$field]; }
    $options = array_map(static fn ($r) => ['id'=>$r['id'], 'option'=>$r['option']], $snapshot['options']);
    usort($options, static fn ($a,$b) => $a['id'] <=> $b['id']);
    $optionMap = array_column($options, 'option', 'id'); $answers = [];
    foreach ($snapshot['answers'] as $answer) {
        if ($answer['question_id'] !== $question['id'] || !isset($optionMap[$answer['option_id']])) {
            throw new RuntimeException('M76 original answer/option ownership differs.');
        }
        $answers[] = ['id'=>$answer['id'], 'marker'=>$answer['marker'], 'position'=>$answer['position'] ?? null,
            'option_id'=>$answer['option_id'], 'option'=>$optionMap[$answer['option_id']]];
    }
    usort($answers, static fn ($a,$b) => strnatcasecmp($a['marker'], $b['marker']));
    if ($answers !== $snapshot['ordered_answer_instances']) { throw new RuntimeException('M76 ordered answer instances do not match complete original families.'); }
    $pivots = array_map(static fn ($r) => ['question_id'=>$r['question_id'], 'option_id'=>$r['option_id'], 'flag'=>$r['flag']], $snapshot['option_pivots']);
    usort($pivots, static fn ($a,$b) => $a['option_id'] <=> $b['option_id']);
    if (array_column($pivots,'option_id') !== array_column($options,'id')
        || array_filter($pivots, static fn ($p) => $p['question_id'] !== $question['id'] || !in_array($p['flag'],[null,0],true))) {
        throw new RuntimeException('M76 captured options differ from the unchanged native loaded pivot set.');
    }
    $hints = array_map(static fn ($r) => ['id'=>$r['id'], 'question_id'=>$r['question_id'], 'provider'=>$r['provider'],
        'locale'=>$r['locale'], 'hint'=>$r['hint']], $snapshot['hints']);
    if ($snapshot['verb_hints'] !== []) { throw new RuntimeException('M76 type-four pool unexpectedly contains verb hints.'); }
    return ['question'=>$ordered, 'answers'=>$answers, 'options'=>$options, 'pivots'=>$pivots, 'hints'=>$hints];
};
$snapshotHashes = static function (array $snapshot) use ($runtimeParts): array {
    $r = $runtimeParts($snapshot);
    return ['question_snapshot'=>Digest::digest($r['question']), 'ordered_answer_snapshot'=>Digest::digest($r['answers']),
        'original_options_snapshot'=>Digest::digest($r['options']), 'loaded_option_pivots'=>Digest::digest($r['pivots']),
        'hints_snapshot'=>Digest::digest($r['hints']), 'verb_hints_snapshot'=>Digest::digest([])];
};
$publicHashes = static function (array $snapshot,array $links) use ($snapshotHashes): array {
    return ['full_private_snapshot_sha256'=>Digest::digest($snapshot), 'snapshot_hashes'=>$snapshotHashes($snapshot),
        'variants_sha256'=>Digest::digest($snapshot['variants']), 'links_sha256'=>Digest::digest($links),
        'all_original_option_attributes_sha256'=>Digest::digest($snapshot['options']),
        'all_original_answer_attributes_sha256'=>Digest::digest($snapshot['answers']),
        'all_original_option_pivot_attributes_sha256'=>Digest::digest($snapshot['option_pivots']),
        'all_original_tag_relations_sha256'=>Digest::digest(['tags'=>$snapshot['tags'],'marker_tags'=>$snapshot['marker_tags']])];
};

$selectedIds = [[33928,33930,33933,33936,33937], [47523,47527,47528,47531,47533], [33843,33847,33851,33852,33854]];
$conditions = [
    33928 => ['Ідеться про книжку, яку купив саме мовець: «Книжка, яку я купив, корисна». Почни з The book. У цьому пункті вжий which, хоча that або пропуск теж можливі у відповідній моделі. Купив — bought; корисна — useful.', 'Книжка, яку я купив, корисна.', 'The book which I bought is useful.', 'which as object of bought; I remains its subject'],
    33930 => ['Скажи про студента, чия сумка зникла: «Студент, чия сумка зникла, засмучений». Почни з The student та вжий whose перед bag; missing означає «зникла», upset — «засмучений». Збережи обидва is.', 'Студент, чия сумка зникла, засмучений.', 'The student whose bag is missing is upset.', 'whose plus noun phrase; two separate is instances'],
    33933 => ['Розрізняємо камери за тим, що з ними сталося: «Камера, яка зламалася, стара». Почни з The camera та в цьому пункті використай which. Broke означає «зламалася», old — «стара»; that у такій моделі теж можливе, але тут тренуємо which.', 'Камера, яка зламалася, стара.', 'The camera which broke is old.', 'which as subject of broke; no repeated subject pronoun'],
    33936 => ['Саме мовець розмовляв із цією людиною; вона була ввічлива. Скажи: «Людина, з якою я розмовляв, була ввічлива». Почни з The person і побудуй формальнішу модель із to перед whom. Розмовляв — spoke; ввічлива — polite.', 'Людина, з якою я розмовляв, була ввічлива.', 'The person to whom I spoke was polite.', 'preposition plus object whom; I remains the speaker'],
    33937 => ['Порівнюємо кілька фільмів. Скажи про вже названий: «Це найкращий фільм, який я бачив». Почни з It та вжий that після film у цьому пункті. Which або пропуск у відповідній моделі не є універсально неправильними; тут відпрацьовуємо саме that.', 'Це найкращий фільм, який я бачив.', 'It is the best film that I have seen.', 'finite that focus after superlative, not an only-that rule'],
    47523 => ['У групі четверо студентів, і кожен отримав окремий квиток. Скажи: «Кожен студент отримав окремий квиток». Почни з Each; every могло б передати близький зміст, але тут тренуємо each. Отримав — got, окремий — separate; крапку постав наприкінці.', 'Кожен студент отримав окремий квиток.', 'Each student got a separate ticket.', 'each plus singular noun; separate is an explicit lexical fact'],
    47527 => ['У групі четверо дітей, і жодну не пропустили під час вручення значків. Скажи: «Кожен із дітей отримав значок». Почни з Every one — двох окремих слів — та далі використай of. Отримав — received, значок — badge; крапка завершує речення.', 'Кожен із дітей отримав значок.', 'Every one of the children received a badge.', 'every one of, not every of or everyone of'],
    47528 => ['Усі учасники відомої групи погодилися з планом; чи сталося це одночасно, не сказано. Передай: «Усі вони погодилися з планом». У цьому пункті почни з All of them, а не перебудовуй речення з They. Погодилися — agreed; крапка стоїть у кінці.', 'Усі вони погодилися з планом.', 'All of them agreed with the plan.', 'all of plus object pronoun; no simultaneity inference'],
    47531 => ['Регулярна домовленість групи: «Ми зустрічаємося щопонеділка вранці». Почни з We та використай every перед Monday. Це повторення щотижня, не весь ранок; крапку постав наприкінці.', 'Ми зустрічаємося щопонеділка вранці.', 'We meet every Monday morning.', 'regular every time phrase; capital Monday unchanged'],
    47533 => ['У кожного учасника вже відомої групи є квиток. Передай: «Вони всі мають квитки». Почни з They та постав all перед have; перебудова з All of them у цьому пункті не потрібна. Квитки — tickets; крапку постав наприкінці.', 'Вони всі мають квитки.', 'They all have tickets.', 'subject they plus mid-position all and plural have'],
    33843 => ['Двоє друзів часто пишуть одне одному: кожен надсилає повідомлення іншому. Склади це повідомлення, починаючи з The friends. У цьому пункті використовуй each та other, а не one another; після write потрібне to.', 'Друзі часто пишуть одне одному.', 'The friends often write to each other.', 'reciprocal writing in both directions; write to'],
    33847 => ['Дві сестри сиділи по різні боки столу й дивилися одна на одну, не кожна на себе. Скажи: «Сестри дивилися одна на одну через стіл». Почни з The sisters, ужий looked at та форму each other; збережи два екземпляри the.', 'Сестри дивилися одна на одну через стіл.', 'The sisters looked at each other across the table.', 'reciprocal look at; two The/the token instances'],
    33851 => ['Двоє вже відомих людей зустріли одне одного на станції. Скажи це, починаючи з They. Хоча коротке They met теж може описувати зустріч, у цьому завданні явно вжий each та other. Зустріли — met.', 'Вони зустріли одне одного на станції.', 'They met each other at the station.', 'explicit reciprocal construction with met; no obligatory to'],
    33852 => ['Двоє людей подивилися в дзеркало: кожен побачив власне відображення, не іншого учасника. Скажи: «Вони побачили себе в дзеркалі». Почни з They та вжий зворотну, а не взаємну форму. Побачили — saw; дзеркало — mirror.', 'Вони побачили себе в дзеркалі.', 'They saw themselves in the mirror.', 'reflexive boundary: each sees their own reflection, not each other'],
    33854 => ['Під час довгої зміни волонтери підбадьорювали інших учасників своєї групи у відповідь одне одному; точну кількість усіх можливих пар не задано. Передай: «Волонтери підбадьорювали одне одного під час довгої зміни». Почни з The volunteers і вжий one та another, не each other.', 'Волонтери підбадьорювали одне одного під час довгої зміни.', 'The volunteers encouraged one another during the long shift.', 'one another reciprocal support; no all-pairs inference']
];

$audit = ['schema_version'=>1, 'package'=>'m76-pronoun-relations-bank-audit', 'version'=>'1.0.0', 'status'=>'draft', 'locale'=>'uk',
    'independent_before_sha256'=>$beforeSha,
    'scope'=>'15 selected / 15 reused / 0 new; no question/answer/option/grader/progress writes',
    'pending'=>['Independent main review/freeze, exact post-apply local widget acceptance and all-old-row preservation proof.'],
    'privacy_policy'=>'Teaching IDs, original and cloned conditions, complete native token/option text and opaque snapshot hashes only; no raw database row families, timestamps, runtime identity, secrets or progress.',
    'source_proof'=>['author_projection_sha256'=>$authorSha, 'independent_before_sha256'=>$beforeSha],
    'review_summary'=>['actual_filtered_pool'=>216, 'selected'=>15, 'reused'=>15, 'new'=>0, 'technical_filter'=>[null,null,null],
        'intended_selected_levels'=>['B1','A2','A2'],
        'null_policy'=>'Actual SQL NULL practice levels preserve the explicit-link matcher all-level selection: each original exact type-four bank has 72 eligible rows. Old diagnostic eligible_count=0 came from strict SQL NULL filtering and is not the matcher pool.',
        'boundary'=>'Only the finite fifteen selected functions are accepted here. Other pool rows are scope exclusions, not a claim that every excluded question is invalid or individually semantically accepted.', 'human_review'=>false],
    'clone_only_policy'=>'The complete fourteen question attributes and loaded answer/option/pivot/hint snapshots must match. Only the Ukrainian condition may change on unsaved canonical-theory clones. No answer, token, option, formatter, casing or grader change.',
    'formatter_review'=>['native_case_source_sha256'=>hash_file('sha256',$root.'/app/Support/ComposeTokenCase.php'),
        'native_blade_lf_sha256'=>hash('sha256',str_replace("\r\n","\n",file_get_contents($root.'/resources/views/components/text-block-practice-questions.blade.php'))),
        'settled_string_policy'=>'Canonical statements preserve every original token, including the standalone period in Each. Actual unchanged native methods are replayed below; no strict case or punctuation grading is claimed.',
        'browser_acceptance'=>'not-claimed-by-offline-projection'], 'targets'=>[], 'practice_bindings'=>[]];
$privateSelected = [];
foreach (Package::OWNERS as $i=>$owner) {
    $page = $before['pages'][$owner] ?? null; $declaration = $author['contract']['targets'][$i];
    $index = Package::PRACTICE_SOURCE_INDICES[$i]; $block = $author['patch']['targets'][$i]['after']['page']['blocks'][$index];
    $data = json_decode($block['body'],true,flags:JSON_THROW_ON_ERROR); $banks = $declaration['bank_seeders'];
    if (!is_array($page) || $page['page']['id'] !== Package::PAGE_IDS[$i] || $declaration['identity'] !== $owner
        || $declaration['level'] !== null || ($data['linked_practice']['seeder_classes'] ?? null) !== $banks
        || array_column($page['category_ancestry'],'slug') !== Package::ANCESTRIES[$i]) {
        throw new RuntimeException('M76 exact continuing page/bank/ancestry binding differs.');
    }
    $snapshots = $page['relations']['question_snapshots']; $poolRows = []; $poolByBank = [];
    foreach ($banks as $bank) {
        $questions = array_values(array_filter($page['relations']['questions'], static fn ($q) => $q['seeder'] === $bank && (string)$q['type'] === '4'));
        if (count($questions) !== 72) { throw new RuntimeException('M76 exact all-level bank population differs.'); }
        $poolByBank[$bank] = array_column($questions,'id'); sort($poolByBank[$bank]);
        foreach ($questions as $q) {
            if ($q['seeder'] !== $bank || (string)$q['type'] !== '4' || isset($poolRows[$q['id']])) { throw new RuntimeException('M76 pool contains a foreign or duplicate question.'); }
            $s = $snapshots[$q['uuid']] ?? null;
            if (!is_array($s) || $s['question'] !== $q) { throw new RuntimeException('M76 complete independent pool snapshot is missing.'); }
            $poolRows[$q['id']] = $s;
        }
    }
    $orderedSelected = $selectedIds[$i];
    foreach ($orderedSelected as $id) { if (!isset($poolRows[$id],$conditions[$id])) { throw new RuntimeException('M76 finite selected question is not in its exact pool.'); } }
    $nativeOrder = $orderedSelected;
    usort($nativeOrder, static fn ($a,$b) => strcmp($poolRows[$a]['question']['uuid'],$poolRows[$b]['question']['uuid']));
    if ($nativeOrder !== $orderedSelected) { throw new RuntimeException('M76 native UUID order does not produce the declared five.'); }
    $excluded = array_values(array_diff(array_merge(...array_values($poolByBank)),$orderedSelected)); sort($excluded);
    $linksFor = static fn ($uuid) => array_values(array_filter(
        $page['relations']['question_theory_text_blocks'],
        static fn ($row) => $row['question_uuid'] === $uuid));
    $target = ['identity'=>$owner, 'page_id'=>Package::PAGE_IDS[$i], 'category_id'=>Package::CATEGORY_IDS[$i],
        'category_ancestry'=>array_map(static fn ($slug) => ['slug'=>$slug],Package::ANCESTRIES[$i]),
        'local_url'=>$declaration['local_url'], 'locale'=>'uk', 'level'=>null,
        'intended_selected_level'=>$audit['review_summary']['intended_selected_levels'][$i], 'type'=>4, 'bank_seeders'=>$banks,
        'expected_pool_ids_by_bank'=>$poolByBank, 'expected_selected_ids'=>$orderedSelected,
        'expected_selected_uuids'=>array_map(static fn ($id) => $poolRows[$id]['question']['uuid'],$orderedSelected),
        'excluded_question_ids'=>$excluded, 'all_pool_snapshots'=>[], 'all_eligible_review'=>[],
        'scope_exclusions'=>['ids'=>$excluded,'reason'=>'Finite five-question widget scope only; original rows/keys/case remain unchanged.'], 'known_scope_exclusions'=>[], 'first_five'=>[]];
    ksort($poolRows);
    foreach ($poolRows as $id=>$snapshot) {
        $q = $snapshot['question']; $hashes = $publicHashes($snapshot,$linksFor($q['uuid']));
        $target['all_pool_snapshots'][] = ['id'=>$id,'uuid'=>$q['uuid'],'level'=>$q['level'],...$hashes];
        if (in_array($id,$excluded,true)) {
            $interior = array_slice(array_column($runtimeParts($snapshot)['answers'],'option'),1);
            if (array_intersect($interior,['Who','Students','Managers','Participants']) !== []) {
                $target['known_scope_exclusions'][] = ['id'=>$id,'uuid'=>$q['uuid'],
                    'reason'=>'Original interior Who/Students/Managers/Participants casing survives the unchanged native formatter; not selected. This is not a judgment that every excluded question is invalid.'];
            }
            if ($id===33849) { $target['known_scope_exclusions'][] = ['id'=>$id,'uuid'=>$q['uuid'],
                'reason'=>'Apostrophe-only each others / each other\'s distractor collision under the actual punctuation-insensitive native normalizer; possessive meaning remains in own semantic practice, not declared universally invalid.']; }
        }
    }
    foreach ($orderedSelected as $id) {
        $snapshot = $poolRows[$id]; $q = $snapshot['question']; $r = $runtimeParts($snapshot);
        [$condition,$translation,$settled,$coverage] = $conditions[$id];
        if (!str_ends_with($condition,'.') || preg_match('/[\x{0400}-\x{04FF}]/u',$condition)!==1
            || preg_match('/<[^>]+>|\{a[0-9]+\}|&(?:#[0-9]+|[a-z]+);/iu',$condition)) {
            throw new RuntimeException('M76 complete plain Ukrainian clone condition/punctuation differs.');
        }
        $rawTokens = array_column($r['answers'],'option'); $tokens = ComposeTokenCase::normalize($rawTokens);
        $joined = implode(' ',$tokens);
        $sourceDerivedSettled = preg_replace('/\\s+([?.!,;:])/u','$1',$joined.(preg_match('/[?.!]$/u',$joined)===1 ? '' : '.'));
        if ($sourceDerivedSettled !== $settled || ComposeTokenCase::normalize($tokens) !== $tokens) {
            throw new RuntimeException('M76 unchanged native casing does not yield the reviewed canonical sentence.');
        }
        $target['first_five'][] = ['id'=>$id,'uuid'=>$q['uuid'],'is_new'=>false,
            'question_attribute_keys'=>Rows::ROW_COLUMNS['questions'], 'displayed_condition_uk'=>$q['question'],
            'visible_condition_uk'=>$condition,'translation_uk'=>$translation,'correct_tokens'=>$tokens,'raw_correct_tokens'=>$rawTokens,
            'expected_word_key'=>implode(' ',$tokens),'actual_correct_visible'=>$settled,'actual_selected_visible'=>$settled,
            'source_ordered_instance_count'=>count($r['answers']), 'original_option_texts'=>array_column($r['options'],'option'),
            'token_bank_with_multiplicity'=>ComposeTokenCase::mergeOptions(array_column($r['options'],'option'),$rawTokens),
            'token_presentations'=>[],'option_presentations'=>[],...$publicHashes($snapshot,$linksFor($q['uuid']))];
        $target['all_eligible_review'][] = ['id'=>$id,'uuid'=>$q['uuid'],'decision'=>'selected','function'=>$coverage,
            'key_policy'=>'Exact original answer sequence; any alternative synonym/word order is outside this visibly focused finite token-bank task, not universally ungrammatical.'];
        $privateSelected[$i][] = $snapshot;
    }
    $audit['targets'][] = $target;
    $audit['practice_bindings'][] = ['identity'=>$owner,'page_id'=>Package::PAGE_IDS[$i],'category_id'=>Package::CATEGORY_IDS[$i],
        'locale'=>'uk','source_index'=>$index,'uuid'=>Digest::uuid($owner,$block,$index+1),'type'=>$block['type'],'column'=>$block['column'],
        'heading'=>$block['heading']??null,'level'=>null,'css_class'=>$block['css_class']??null,
        'body_sha256'=>hash('sha256',$block['body']),'linked_practice'=>$data['linked_practice']];
    if ($audit['practice_bindings'][$i]['uuid'] !== Package::PRACTICE_UUIDS[$i]) { throw new RuntimeException('M76 continuing practice UUID changed.'); }
}

/** Replay actual unchanged native methods against finite available-token adversaries, without application bootstrap. */
$bladePath = $root.'/resources/views/components/text-block-practice-questions.blade.php';
$nativeBeforeSha = $before['visual_hashes']['resources/views/components/text-block-practice-questions.blade.php'] ?? '';
if (!is_string($nativeBeforeSha) || !hash_equals($nativeBeforeSha,hash_file('sha256',$bladePath))) {
    throw new RuntimeException('M76 served native component changed from independent visual BEFORE.');
}
$selectedProbe = [];
foreach ($audit['targets'] as $target) { foreach ($target['first_five'] as $sample) {
    $selectedProbe[] = ['id'=>$sample['id'],'condition'=>$sample['visible_condition_uk'],
        'correct'=>$sample['correct_tokens'],'bank'=>$sample['token_bank_with_multiplicity'],'settled'=>$sample['actual_correct_visible']];
} }
$trapSnapshot = $before['pages'][Package::OWNERS[2]]['relations']['question_snapshots']['rcp-poly-a2-07'] ?? null;
if (!is_array($trapSnapshot) || $trapSnapshot['question']['id']!==33849) { throw new RuntimeException('M76 excluded apostrophe evidence is missing.'); }
$trapRaw = array_column($runtimeParts($trapSnapshot)['answers'],'option');
$trapProbe = ['id'=>33849,'condition'=>'Партнери перевірили нотатки одне одного.',
    'correct'=>ComposeTokenCase::normalize($trapRaw),
    'bank'=>ComposeTokenCase::mergeOptions(array_column($trapSnapshot['options'],'option'),$trapRaw),
    'settled'=>"The partners checked each other's notes."];
$nodeSource = <<<'JS'
const fs=require('node:fs'),vm=require('node:vm'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const payload=JSON.parse(fs.readFileSync(0,'utf8'));
function runProfile(input){
const bytes=fs.readFileSync(input.blade),hash=b=>crypto.createHash('sha256').update(b).digest('hex');
assert.equal(hash(bytes),input.raw_sha256,'Served native source must equal independent BEFORE bytes.');
const text=bytes.toString('utf8').replace(/\r\n/g,'\n');
function take(start,end){const i=text.indexOf(start),j=text.indexOf(end,i);assert.ok(i>=0&&j>i);assert.equal(text.indexOf(start,i+start.length),-1);return text.slice(i,j);}
const source=take('                composeCorrectText() {','                getDisplayText() {')
    +take('                checkAnswer() {','                hasMoreQuestions() {');
const methods=vm.runInNewContext('({'+source+'})',{window:{}},{timeout:1000});
const lexical=s=>String(s).toLowerCase().replace(/[\u2018\u2019]/g,"'").replace(/[^\p{L}\p{N}']+/gu,' ').replace(/\s+/g,' ').trim();
function run(sample,tokens){
 const m={...methods,currentQuestion:{question:sample.condition,correct_tokens:sample.correct,type:'4',authored_compose:false},
 selectedTokens:tokens.map((value,id)=>({id,value})),answered:false,isCorrect:false,correctCount:0,currentIndex:0,answeredIndices:[],
 isComposeQuestion(){return true;},persistState(){}};
 m.correctAnswer=m.composeCorrectText();assert.equal(m.correctAnswer,sample.settled,'Actual native canonical settled text.');
 m.checkAnswer();const first=m.correctCount;m.checkAnswer();assert.equal(m.correctCount,first,'Repeated Check cannot double score.');
 return{correct:m.isCorrect,display:m.composeSelectedText()};
}
function check(sample){
 const canonical=run(sample,sample.correct);assert.equal(canonical.correct,true);assert.equal(canonical.display,sample.settled);
 assert.equal(run(sample,[]).correct,false,'Empty answer cannot score.');
 const capacity=new Map;for(const token of sample.bank)capacity.set(token,(capacity.get(token)||0)+1);
 const possible=tokens=>{const counts=new Map;for(const token of tokens){const n=(counts.get(token)||0)+1;counts.set(token,n);if(n>(capacity.get(token)||0))return false;}return true;};
 const cases=new Map;const add=tokens=>{if(possible(tokens))cases.set(JSON.stringify(tokens),tokens);};add([]);
 for(let start=0;start<sample.correct.length;start++){
  add([...sample.correct.slice(0,start),...sample.correct.slice(start+1)]);
  for(let length=1;length<=3&&start+length<=sample.correct.length;length++){
   for(const option of sample.bank)add([...sample.correct.slice(0,start),option,...sample.correct.slice(start+length)]);
  }
  for(let other=start+1;other<sample.correct.length;other++){
   const copy=[...sample.correct];[copy[start],copy[other]]=[copy[other],copy[start]];add(copy);
  }
  if(/^[?.!,;:]$/.test(sample.correct[start]))for(let at=0;at<sample.correct.length;at++){
   const copy=[...sample.correct];const [punctuation]=copy.splice(start,1);copy.splice(at,0,punctuation);add(copy);
  }
 }
 const bad=[],formatting=[];let correct=0;
 for(const tokens of cases.values()){const r=run(sample,tokens);if(r.correct){correct++;if(r.display!==sample.settled){
  const item={tokens,visible:r.display};
  if(lexical(r.display)===lexical(sample.settled))formatting.push(item);else bad.push(item);
 }}}
 return{id:sample.id,canonical_check:true,empty_rejected:true,repeated_check_stable:true,
 adversarial_sequences:cases.size,accepted_sequences:correct,wrong_lexical_visible_scored_correct:bad,
 formatting_only_normalization_matches:formatting.length,formatting_only_examples:formatting.slice(0,3)};
}
const selected=input.selected.map(check);for(const result of selected)assert.equal(result.wrong_lexical_visible_scored_correct.length,0,'A selected lexical/apostrophe trap cannot be frozen.');
const trap=check(input.excluded_trap);assert.ok(trap.wrong_lexical_visible_scored_correct.some(x=>x.visible.includes('each others')),'Excluded actual apostrophe-normalization trap must reproduce.');
return {profile:input.label,kind:'actual-unchanged-native-method-replay',native_raw_sha256:hash(bytes),
 native_lf_sha256:hash(Buffer.from(text)),method_source_sha256:hash(Buffer.from(source)),
 finite_adversarial_scope:'Empty, each token removal, all pairwise token swaps, standalone punctuation moves, and all bank-option replacements of contiguous spans of one to three answer tokens, bounded by exact available bank instances. Not exhaustive permutations.',
 selected,excluded_apostrophe_trap:{id:trap.id,reproduced:true,wrong_lexical_visible_scored_correct:trap.wrong_lexical_visible_scored_correct},
 formatting_boundary:'Case and non-apostrophe punctuation differences are deliberately not graded strictly by this unchanged engine; formatting-only accepted variants are recorded, not represented as strict rejection or as wrong lexical content.',
 db_connections:0,db_writes:0,http_requests:0,file_writes:0};
}
const profiles=payload.profiles.map(profile=>runProfile({...payload,...profile}));
process.stdout.write(JSON.stringify({kind:'actual-unchanged-native-method-replay',profiles,db_connections:0,db_writes:0,http_requests:0,file_writes:0}));
JS;
$process = new \Symfony\Component\Process\Process(['node','-e',$nodeSource],$wt);
$process->setTimeout(45);
$acceptedWtBladeSha = '94fc2ecd7525cacfac652cc39aebf3ac057d8f845daf1425fe8cd845c9c0dd80';
$process->setInput(json_encode(['profiles'=>[
    ['label'=>'independent-served-ROOT-before','blade'=>$bladePath,'raw_sha256'=>$nativeBeforeSha],
    ['label'=>'accepted-worktree','blade'=>$wt.'/resources/views/components/text-block-practice-questions.blade.php','raw_sha256'=>$acceptedWtBladeSha]],
    'selected'=>$selectedProbe,'excluded_trap'=>$trapProbe],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
$process->run();
if (!$process->isSuccessful()) { throw new RuntimeException('M76 actual native canonical/adversarial probe did not pass; no projection written.'); }
$nativeProbe = json_decode($process->getOutput(),true,flags:JSON_THROW_ON_ERROR);
if (($nativeProbe['kind']??null)!=='actual-unchanged-native-method-replay'
    || array_column($nativeProbe['profiles']??[],'profile')!==['independent-served-ROOT-before','accepted-worktree']
    || ($nativeProbe['db_connections']??null)!==0 || ($nativeProbe['db_writes']??null)!==0
    || ($nativeProbe['http_requests']??null)!==0 || ($nativeProbe['file_writes']??null)!==0) {
    throw new RuntimeException('M76 actual native dual-profile probe scope differs.');
}
foreach ($nativeProbe['profiles'] as $i=>$profile) {
    if (($profile['native_raw_sha256']??null)!==[$nativeBeforeSha,$acceptedWtBladeSha][$i]
        || array_column($profile['selected']??[],'id')!==array_column($selectedProbe,'id')
        || ($profile['excluded_apostrophe_trap']['id']??null)!==33849
        || ($profile['excluded_apostrophe_trap']['reproduced']??null)!==true) {
        throw new RuntimeException('M76 exact coherent native source profile or excluded trap differs.');
    }
    foreach ($profile['selected'] as $result) {
        if (($result['canonical_check']??null)!==true || ($result['empty_rejected']??null)!==true
            || ($result['repeated_check_stable']??null)!==true || ($result['wrong_lexical_visible_scored_correct']??null)!==[]) {
            throw new RuntimeException('M76 selected canonical/negative lexical acceptance differs.');
        }
    }
}
$audit['native_runtime_probe'] = $nativeProbe;

/** An in-memory validation policy cannot load or freeze a public source. */
final class M76BankAuditProjectionValidator extends TheoryBuilderSelectionPresentation
{
    public static string $targetDigest;
    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return ['label'=>'M76 projection','package'=>'m76-pronoun-relations-bank-audit','version'=>'1.0.0',
            'scope'=>'15 selected / 15 reused / 0 new; no question/answer/option/grader/progress writes',
            'before_sha256'=>'b0cae6f629aae1b4354aefcf8d704b73030ef2022846c2cd1ea2f9f5d3a05985',
            'targets_sha256'=>self::$targetDigest];
    }
}
M76BankAuditProjectionValidator::$targetDigest = Digest::digest($audit['targets']);
$validationCopy = $audit; $validationCopy['status'] = 'frozen'; $validationCopy['pending'] = [];
M76BankAuditProjectionValidator::validateAudit($validationCopy,$author['patch'],$author['contract']);

/** Rehydrate the captured loaded relations in memory only, then exercise the unchanged exact private matcher. */
$model = static function (string $class,array $attributes): object { $m = new $class; $m->setRawAttributes($attributes,true); return $m; };
$hydrate = static function (array $s) use ($model): Question {
    $q = $model(Question::class,$s['question']); $optionMap = []; $options = [];
    $pivots = array_column($s['option_pivots'],null,'option_id');
    foreach ($s['options'] as $row) {
        $o = $model(QuestionOption::class,$row); $p = $pivots[$row['id']];
        $o->setRelation('pivot',$model(Pivot::class,['question_id'=>$p['question_id'],'option_id'=>$p['option_id'],'flag'=>$p['flag']]));
        $optionMap[$row['id']] = $o; $options[] = $o;
    }
    $answers = [];
    foreach ($s['answers'] as $row) { $a = $model(QuestionAnswer::class,$row); $a->setRelation('option',$optionMap[$row['option_id']]); $answers[] = $a; }
    $q->setRelation('answers',new Collection($answers)); $q->setRelation('options',new Collection($options));
    $q->setRelation('hints',new Collection(array_map(static fn ($row) => $model(QuestionHint::class,$row),$s['hints'])));
    $q->setRelation('verbHints',new Collection); return $q;
};
$matcher = new ReflectionMethod(TheoryBuilderSelectionPresentation::class,'matchesQuestions'); $checks = 0;
foreach ($audit['targets'] as $i=>$target) {
    $questions = new Collection(array_map($hydrate,$privateSelected[$i]));
    if ($matcher->invoke(null,$questions,$target)!==true) { throw new RuntimeException('M76 complete model-only snapshot matcher rejected canonical selection.'); }
    ++$checks;
    $stale = $questions->map(static fn ($q) => clone $q); $first = $stale->first();
    $first->setRawAttributes(array_replace($first->getAttributes(),['question'=>$first->question.' stale']),true);
    if ($matcher->invoke(null,$stale,$target)!==false) { throw new RuntimeException('M76 exact matcher failed to reject a stale question.'); }
    ++$checks;
    $stale = $questions->map(static fn ($q) => clone $q); $first = $stale->first();
    $options = $first->getRelation('options')->map(static fn ($o) => clone $o); $option = $options->first();
    $option->setRawAttributes(array_replace($option->getAttributes(),['option'=>$option->option.' stale']),true); $first->setRelation('options',$options);
    if ($matcher->invoke(null,$stale,$target)!==false) { throw new RuntimeException('M76 exact matcher failed to reject a stale shared option.'); }
    ++$checks;
}
if (array_map(static fn ($t) => array_map('count',$t['expected_pool_ids_by_bank']),$audit['targets']) !== [
    [$audit['targets'][0]['bank_seeders'][0]=>72], [$audit['targets'][1]['bank_seeders'][0]=>72],
    [$audit['targets'][2]['bank_seeders'][0]=>72]]) {
    throw new RuntimeException('M76 final exact bank pool counts differ.');
}
$json = json_encode($audit,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
if (preg_match('/"(?:created_at|updated_at|started_at|finished_at|row_timestamp|identity|sql_before|served_root)"\s*:/',
    json_encode(['privacy_check'=>array_diff_key($audit,['targets'=>true,'practice_bindings'=>true])],JSON_THROW_ON_ERROR))) {
    throw new RuntimeException('M76 public header contains a forbidden private runtime field.');
}
if (file_exists($output) || is_link($output)) { throw new RuntimeException('M76 private output collided before exclusive create.'); }
$handle = fopen($output,'x');
if ($handle===false) { throw new RuntimeException('M76 exclusive private output cannot be opened.'); }
try { if (fwrite($handle,$json)!==strlen($json)||!fflush($handle)) { throw new RuntimeException('M76 private projection write was incomplete.'); } }
finally { fclose($handle); }
echo json_encode(['status'=>'draft-source-only-projection','file'=>$argv[2],'sha256'=>hash('sha256',$json),
    'targets_sha256'=>Digest::digest($audit['targets']),'selected_counts'=>array_map(static fn ($t) => count($t['first_five']),$audit['targets']),
    'pool_counts'=>array_map(static fn ($t) => array_sum(array_map('count',$t['expected_pool_ids_by_bank'])),$audit['targets']),
    'shared_model_matcher_checks'=>$checks,'db_connections'=>0,'db_writes'=>0,'http_requests'=>0,'active_source_writes'=>0],
    JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
