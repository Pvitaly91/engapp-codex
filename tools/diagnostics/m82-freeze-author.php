<?php

/** Finite artifact orchestration; emits an exclusive private plan, never active-source/SQL writes. */
if (PHP_SAPI!=='cli'||count($argv)!==7
    ||preg_match('/^author-projection-v[1-9][0-9]*\.json$/D',$argv[1])!==1
    ||preg_match('/^bank-audit-projection-v[1-9][0-9]*\.json$/D',$argv[3])!==1
    ||preg_match('/^prospective-metadata-v[1-9][0-9]*\.json$/D',$argv[5])!==1
    ||array_filter([$argv[2],$argv[4],$argv[6]],static fn($s)=>preg_match('/^[a-f0-9]{64}$/D',$s)!==1)) {
    throw new RuntimeException('M82 needs exact reviewed author/bank/metadata basenames and hashes.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m82-local';
require $root.'/vendor/autoload.php';
require_once $wt.'/app/Support/TheoryNativeContentRelease.php';
require_once $wt.'/app/Support/M82CommonMistakesLevelRevision.php';
use App\Support\M82CommonMistakesLevelRevision as Package;
$read=static function($name,$sha)use($dir) {
    $path=$dir.'/'.$name;if (is_link($path)||!is_file($path)||!hash_equals($sha,hash_file('sha256',$path))) { throw new RuntimeException('M82 reviewed private artifact drift.'); }
    return json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
};
if([$argv[2],$argv[4],$argv[6]]!==[
    '256cd5fa67379d596432b3b431588cce61450ac4b7d71fb9514c6c4d34101611',
    '21e3d014243c4e11afb16cdee8f5dbdaeee71cf7d73e1852ec811eadc41060de',
    '2e9d800395321e18a6e2f6c25d6f9dca28d7cd59c6a313d0f80690563835e9c3']){
    throw new RuntimeException('M82 reviewed exact author/bank/metadata tuple differs.');
}
if(!is_dir($dir)||is_link($dir)||is_link($wt)){throw new RuntimeException('M82 private/source directory is unsafe.');}
$author=$read($argv[1],$argv[2]);$bank=$read($argv[3],$argv[4]);$metadata=$read($argv[5],$argv[6]);
$previousPlanSha='fdc49ad56bd5fc4b9b0ce45fb06f1840e9ea680a0034e98991bc47f0e5aeb61c';
$previousPlan=$read('freeze-plan-v1.json',$previousPlanSha);
if(count($previousPlan['outputs']??[])!==18||count($previousPlan['before_bytes']??[])!==6
    ||(Package::draftProfile($author['native_bindings']['bindings'])['version']??null)!=='1.0.1'
    ||str_contains(json_encode($author['native_bindings'],JSON_THROW_ON_ERROR),'preserved_label_navigation')
    ||!hash_equals('23b89050f061faddd80debce2fefc8dd356a0b49dffa44f79b2200cb5cbbef11',hash_file('sha256',$wt.'/app/Support/TheoryNativeContentRelease.php'))){
    throw new RuntimeException('M82 revision 1.0.1 must use the exact prior plan and unchanged native validator without navigation opt-ins.');
}
(new App\Support\TheoryNativeContentRelease(Package::draftProfile($author['native_bindings']['bindings'])))
    ->validate($author['source_before'],$author['patch'],$author['master'],$author['contract']);
Package::assertEditorial($author['source_before'],$author['patch'],$author['master']);
if (($bank['source_proof']['author_projection_sha256']??null)!==$argv[2]
    ||array_column($bank['targets']??[],'identity')!==Package::OWNERS||($metadata['projection_sha256']??null)!==$argv[2]
    ||array_column($metadata['metadata']??[],'identity')!==Package::OWNERS
    ||array_map(static fn($t)=>count($t['first_five']),$bank['targets'])!==[5,5,5,5,5,5]) {
    throw new RuntimeException('M82 bank/metadata exact author lineage differs.');
}
$master=$author['master'];$master['status']='frozen';$master['editorial_review']['expected_http_metadata']=$metadata;
$patch=$author['patch'];$patch['status']='frozen';$bank['status']='frozen';$bank['pending']=[];
$selectionPath='docs/content/m82-widget-selection.v1.json';
$reviewedInputs=[
    'docs/content/m82-widget-review-ab.v1.json'=>'dddbaf13b999ec1d6d3eb2b544d58adf0a7be97c0789df162160c4a60a9be65b',
    'docs/content/m82-widget-review-cd.v1.json'=>'15fc1b31a943b007439e70e064595678ed5609741fc92a583d2ca2e408993865',
    'docs/content/m82-widget-review-draft.v1.json'=>'d8a7812fde184eab6ccd33928c8029885a0efab0b407c69b030ac6050407d0a7',
    $selectionPath=>'4f907d2ca2877469e9c534ec6b199fece0418e64d5ee2a4f91f6729af0bc2fff',
    'docs/content/m82-semantic-fixtures.v1.json'=>'20172800e3accc10abea102b168d99b11c0ea7ab8ebbf10ccbefd4b9d6d3b3be',
    'docs/content/m82-navigation-review.v1.json'=>'0e45611733b84425689238b5068cb8d359c75b05b5ffa7295cb5fbf921aff63d',
];
foreach($reviewedInputs as $path=>$sha){
    if(is_link($wt.'/'.$path)||!is_file($wt.'/'.$path)||!hash_equals($sha,hash_file('sha256',$wt.'/'.$path))){
        throw new RuntimeException('M82 reviewed author/semantic/widget input changed: '.$path);
    }
}
$selection=json_decode(file_get_contents($wt.'/'.$selectionPath),true,flags:JSON_THROW_ON_ERROR);
$navigationReview=json_decode(file_get_contents($wt.'/docs/content/m82-navigation-review.v1.json'),true,flags:JSON_THROW_ON_ERROR);
if(count($navigationReview['targets']??[])!==8){throw new RuntimeException('M82 exactly eight approved navigation URLs are required.');}
$navKeys=[];$navExpected=[];
foreach([4,5] as $i){
    $owner=Package::OWNERS[$i];$source=$author['source_before']['targets'][$i]['before']['page']['blocks'][3];
    if($source['type']!=='navigation-chips'||$source['column']!=='footer'){
        throw new RuntimeException('M82 original E/F navigation source slot differs.');
    }
    $navExpected[$owner]=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR)['items'];
    if(count($navExpected[$owner])!==5){throw new RuntimeException('M82 E/F navigation must retain five items.');}
}
foreach($navigationReview['targets'] as $target){
    $owner=$target['owner']??null;$index=$target['item_index']??null;$url=$target['url']??null;
    if(!isset($navExpected[$owner])||($target['source_index']??null)!==3||!is_int($index)||$index<0||$index>3
        ||($target['current']??null)!==false||!is_string($url)||preg_match('~^/theory/[a-z0-9/-]+$~D',$url)!==1
        ||($target['http_status']??null)!==200||($target['target_page_id']??0)<1
        ||isset($navKeys[$owner.':'.$index])){
        throw new RuntimeException('M82 reviewed navigation target identity/order/local path differs.');
    }
    $item=$navExpected[$owner][$index];
    if(($item['label']??null)!==$target['label']||($item['current']??null)!==false||array_key_exists('url',$item)){
        throw new RuntimeException('M82 navigation approval does not extend the exact old label/current item.');
    }
    $navExpected[$owner][$index]['url']=$url;$navKeys[$owner.':'.$index]=true;
}
foreach([4,5] as $i){
    $owner=Package::OWNERS[$i];
    if($author['master']['lessons'][$i]['blocks'][3]['data']['items']!==$navExpected[$owner]
        ||array_key_exists('url',$navExpected[$owner][4])||($navExpected[$owner][4]['current']??null)!==true){
        throw new RuntimeException('M82 E/F footer differs from the eight approved data-only additions.');
    }
}
if(array_column($selection['targets']??[],'identity')!==Package::OWNERS
    ||($selection['independent_before_sha256']??null)!=='94312691251f96433ee72c3fe544db71767c795495874af3ba1c80c2cbb49c62'
    ||array_map(static fn($lesson)=>count($lesson['blocks']),$master['lessons'])!==[7,7,7,7,5,5]){
    throw new RuntimeException('M82 exact six-owner selection/component shape differs.');
}
$prefixes=['cmpp-poly-a2-','cmu-poly-a2-','cmp-poly-a2-','cmbt-poly-b1-','mr-a1-poly-a1-','polyglot-final-drill-a2-'];
$rowSelections=[
    [['01'],['08'],['07'],['02','06'],['02','06']],
    [['04'],['05'],['03','09','10'],['09'],[]],
    [['01'],['02'],['07','08','10'],[],[]],
    [['04'],['03'],['08'],['09'],['01']],
    [['01'],[],['03'],['12'],['04'],[],[],['01','10'],[],[],[]],
    [[],[],[],['q04'],['q05'],[],['q07'],['q08'],[],[],[],['q13'],[]],
];
$uncoveredOverrides=[
    1=>[
        1=>'Very little time перевіряє вибране питання cmu-poly-a2-05; little milk лишається видимим прикладом, а не окремо оціненим widget завданням. Достатність кількості визначається заданими фактами.',
        2=>'Information, одна одиниця advice та homework представлені вибраними питаннями; furniture/luggage лишаються видимими орієнтирами без заяви про повне лексичне оцінювання.',
    ],
    2=>[
        2=>'Own task перевіряє depend у заданій моделі, widget — depend on, interested in і listen to. Good/afraid/reason та інші значення залишаються видимими поясненнями, а не повністю оціненим словником.',
    ],
    3=>[
        4=>'Вибране cmbt-poly-b1-01 перевіряє завершену подію з явним finished-time критерієм. Це додаткова діагностика початкової source-групи, не повна перевірка часової системи.',
    ],
];
$sourceBoundaryNormalizations=[
    4=>[
        'before'=>'BasicGrammar source owner, actual mixed-revision category; four continuing native types/columns/levels and label-only navigation remain exact. No course, final-drill, requirement, membership, progress or enrollment mutation.',
        'after'=>'BasicGrammar source owner, actual mixed-revision category. Four native source functions/types/columns/levels and all five navigation labels/order/current flags are retained. The first four footer items gain exact verified user-approved local data URLs; the native view/resolver/validator and all course, final-drill, requirement, membership, progress and enrollment data remain unchanged.',
    ],
    5=>[
        'before'=>'BasicGrammar source owner, actual mixed-revision category. Four native functions/types/columns/levels retained; label-only navigation remains under native resolver. No course/final-drill/requirement/score/enrollment/progress writes.',
        'after'=>'BasicGrammar source owner, actual mixed-revision category. Four native source functions/types/columns/levels and all five navigation labels/order/current flags are retained. The first four footer items gain exact verified user-approved local data URLs; the native view/resolver/validator and all course, final-drill, requirement, score, enrollment and progress data remain unchanged.',
    ],
];
$master['editorial_review']['review_normalizations']=[];
$coverage=['schema_version'=>1,'package'=>'m82-common-mistakes-level-revision','version'=>'1.0.1','status'=>'frozen',
    'author_projection_sha256'=>$argv[2],'widget_selection_sha256'=>$reviewedInputs[$selectionPath],
    'bank_projection_sha256'=>$argv[4],
    'claim'=>'Finite source-topic/model coverage only; 36 own tasks and 30 selected widget questions are not a complete course or CEFR assessment.',
    'course_boundary'=>[
        'source_and_consumer_before_sha256'=>'1a8cb25b84914738dcab29f957c3c38087685364281d061b6fcb2980b9fdbaa8',
        'a1'=>['course'=>'English Sentence Builder A1','lessons'=>16,'final_lesson_slug'=>'polyglot-final-drill-a1','final_question_count'=>24],
        'a2'=>['course'=>'English Sentence Builder A2','lessons'=>16,'final_lesson_slug'=>'polyglot-final-drill-a2','final_question_count'=>24],
        'completion'=>['rolling_window'=>100,'min_rating'=>4.5],
        'policy'=>'Standalone theory/widget selection never rewrites course lessons, original question models/options/hints, memberships, completion, enrollment or progress.',
        'existing_pool_difference_preserved'=>'Two first A1 lesson pools contain 72 actual questions versus 24 in historical seed definitions; capture preserves the actual pools, not a reseed.',
    ],
    'targets'=>[]];
foreach($master['lessons'] as $i=>$lesson){
    $owner=Package::OWNERS[$i];$review=$master['editorial_review']['lesson_reviews'][$owner]??null;
    $rows=$review['coverage_map']??null;$selected=$selection['targets'][$i]['selected']??null;
    if(!is_array($rows)||!array_is_list($rows)||count($rows)!==count($rowSelections[$i])
        ||!is_array($selected)||count($selected)!==5){
        throw new RuntimeException('M82 finite coverage/selection dimensions differ.');
    }
    $selectedByUuid=array_column($selected,null,'uuid');$actualFive=$bank['targets'][$i]['first_five'];
    if(array_column($actualFive,'uuid')!==array_column($selected,'uuid')){
        throw new RuntimeException('M82 coverage selection order differs from audited widget.');
    }
    foreach($actualFive as $n=>$question){
        if($question['id']!==$selected[$n]['id']||$question['visible_condition_uk']!==$selected[$n]['condition']){
            throw new RuntimeException('M82 coverage audit/selection exact condition identity differs.');
        }
    }
    $ownIds=[];foreach($lesson['blocks'] as $block){if($block['type']!=='practice-set'){continue;}
        foreach(['selects','choices','inputs','rephrase'] as $group){foreach($block['data'][$group]??[] as $task){$ownIds[]=$task['id'];}}
    }
    if(count($ownIds)!==6||count(array_unique($ownIds))!==6){throw new RuntimeException('M82 six distinct own tasks are required for resolved coverage.');}
    $resolved=[];$coveredOwn=[];$coveredWidget=[];
    foreach($rows as $r=>$row){
        $own=$row['own_tasks']??$row['own_task_ids']??[];
        if(!is_array($own)||!array_is_list($own)||array_diff($own,$ownIds)!==[]){
            throw new RuntimeException('M82 coverage cites a foreign/missing own task.');
        }
        $theory=$row['theoretical_reference']??$row['theory_reference']??null;
        if(is_string($theory)){$theory=[$theory];}
        if(!is_array($theory)||$theory===[]||array_filter($theory,static fn($text)=>!is_string($text)||trim($text)==='')){
            throw new RuntimeException('M82 coverage needs the retained theoretical reference.');
        }
        $widgets=[];foreach($rowSelections[$i][$r] as $suffix){
            $uuid=$prefixes[$i].$suffix;$question=$selectedByUuid[$uuid]??null;
            if(!is_array($question)){throw new RuntimeException('M82 coverage cites an unselected widget candidate.');}
            $widgets[]=['id'=>$question['id'],'uuid'=>$uuid,'level'=>$question['level'],'function'=>$question['coverage']];
            $coveredWidget[]=$uuid;
        }
        $uncovered=$uncoveredOverrides[$i][$r]??$row['uncovered_or_link']??$row['uncovered']??null;
        if(!is_string($uncovered)||trim($uncovered)===''){throw new RuntimeException('M82 coverage must retain an explicit uncovered boundary.');}
        $entry=['source_topic'=>$row['source_topic'],'theoretical_reference'=>$theory,'own_task_ids'=>$own,
            'selected_widget_questions'=>$widgets,'uncovered_or_link'=>$uncovered,
            'coverage_kind'=>$own===[]?($widgets===[]?'theory_only':'widget_only'):($widgets===[]?'own_only':'own_and_widget')];
        if(isset($row['key'])){$entry=['key'=>$row['key']]+$entry;}
        if(array_key_exists('reference_url',$row)){$entry['reference_url']=$row['reference_url'];}
        $resolved[]=$entry;$coveredOwn=[...$coveredOwn,...$own];
    }
    $a=array_values(array_unique($coveredOwn));$b=$ownIds;sort($a);sort($b);
    $c=array_values(array_unique($coveredWidget));$d=array_keys($selectedByUuid);sort($c);sort($d);
    if($a!==$b||$c!==$d){throw new RuntimeException('M82 final coverage must account for every own task and each selected widget at least once.');}
    $master['editorial_review']['lesson_reviews'][$owner]['coverage_map']=$resolved;
    $master['editorial_review']['lesson_reviews'][$owner]['coverage_status']='resolved against exact own IDs and actual selected widget IDs; not full course assessment';
    if(isset($sourceBoundaryNormalizations[$i])){
        $normalization=$sourceBoundaryNormalizations[$i];
        if(($review['source_loader_boundary']??null)!==$normalization['before']){
            throw new RuntimeException('M82 finite final-review boundary normalization input differs.');
        }
        $master['editorial_review']['lesson_reviews'][$owner]['source_loader_boundary']=$normalization['after'];
        $master['editorial_review']['review_normalizations'][]=['identity'=>$owner,'field'=>'source_loader_boundary',
            'before'=>$normalization['before'],'after'=>$normalization['after'],
            'reason'=>'Parent-authorized final editorial correction for eight user-approved footer data URLs; checksum-bound V2 lesson data and author-input provenance are unchanged.'];
    }
    $coverage['targets'][]=['identity'=>$owner,'local_url'=>$lesson['local_url'],'own_task_ids'=>$ownIds,
        'selected_widget_ids'=>array_column($selected,'id'),'selected_widget_uuids'=>array_column($selected,'uuid'),'rows'=>$resolved];
}
$master['editorial_review']['coverage_resolution']='Final finite mapping replaces pending widget-candidate prose; draft provenance remains in checksum-bound author input files.';
$editorial=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'version'=>'1.0.1','status'=>'frozen',
    'review'=>'Main independently reviewed six M82 lessons, own tasks, actual widget selections and agent findings. Agent/main review is not human review. Publisher exercises are not copied.',
    'author_projection_sha256'=>$argv[2],'draft_parts_sha256'=>$author['draft_sha256'],
    'lesson_reviews'=>$master['editorial_review']['lesson_reviews'],'projection_edits'=>$master['editorial_review']['projection_edits'],
    'review_normalizations'=>$master['editorial_review']['review_normalizations'],
    'expected_http_metadata'=>$metadata,'coverage_artifact'=>'docs/content/m82-common-mistakes-level-revision.coverage.v1.json',
    'navigation_approval'=>['user_approved'=>true,'review_path'=>'docs/content/m82-navigation-review.v1.json',
        'review_sha256'=>$reviewedInputs['docs/content/m82-navigation-review.v1.json'],'added_urls'=>8,
        'scope'=>'Only the first four footer items of E/F gain verified local URLs; all five labels/order/current flags, native blocks/design/resolver and current fifth items remain unchanged. No shared core navigation opt-in.'],
    'real_page_acceptance'=>['status'=>'not_checked_before_freeze','required_after_guarded_apply'=>true,'target'=>'http://gramlyze.loc',
        'widths'=>[1440,390,320],'themes'=>['light','dark'],'target_states'=>36,'full_inputs'=>12,'manual_token_size_cases'=>48,
        'widget_questions'=>30,'autonomous_html_preview_required'=>false,'user_screenshots_required'=>false],
    'main_language_review'=>[
        'A: Present Perfect diagnosis follows the assessed event, not any marker anywhere. Been to, gone to and been in have explicit reference/return/current-stay contexts; gone alone does not establish arrival. For/since retain duration/start distinctions without invented arithmetic; already/yet/just and BrE/AmE alternatives are scoped. Have/has, V3, questions, negatives and short answers remain visible.',
        'B: Countability follows the intended English meaning, not physical divisibility or abstraction. Substance and servings remain distinct. Few/a few and little/a little do not establish sufficiency without facts. Explicit one advice keeps one piece of advice; information amount, participants and tense remain. Mass-noun versus piece/pieces head agreement and contextual much/many are retained.',
        'C: Time/place, dependent patterns and movement are diagnosed separately. Jump in and related established motion patterns are not universally banned; own entry task explicitly requests into. Arrive keeps its verb pattern and late; intended destination does not prove arrival. Depend/interested/good/reason/listen are meaning-specific, and no-preposition tasks have nonempty full responses.',
        'D: All four source groups remain: conditionals, modal perfect, linkers and inversion. Ordinary Second Conditional keeps its source qualification, mixed time is not automatically an error, and deduction is not obligation. Clause/noun/-ing linker patterns preserve actors; initial negative adverbials and only-adverbial scope differ from medial negatives and subject-only.',
        'E: A1 is a short review of the actual sixteen-lesson local course, not a new grammar topic or official CEFR assessment. All declared models stay visible, with be/do-support, there agreement, routine/current action, question/negative and comparison scope guidance. Some/any is not turned into a universal question ban; the alone does not prove superlative. Eleven finite own/widget checks do not exhaust the course.',
        'F: A2 preserves its stated local-course program and sixteen-lesson final-drill relationship. Perfect/past, real/imagined conditions, plans/advice/obligation, verb patterns, past process, relatives, passive, reported roles, used to and tags have compact contrasts. Reported Olya remains the speaker; passive retains Ivan as agent. Optional is not forbidden; ordinary confirmation tags retain polarity, pronoun and auxiliary.',
    ],
    'main_source_access'=>[
        ['url'=>'https://learnenglish.britishcouncil.org/free-resources/grammar/a1-a2/present-simple','access'=>'Main confirmed direct editorial body read for routine/general meaning and ordinary do/does support; no exercise copied.'],
        ['url'=>'https://learnenglish.britishcouncil.org/free-resources/grammar/a1-a2/nouns-countable-uncountable','access'=>'Main confirmed direct editorial body read for meaning-specific countability/quantity and some in offers/requests; no general serving prohibition.'],
        ['url'=>'https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/conditionals-zero-first-second','access'=>'Main confirmed direct editorial body read; publisher grouping is not used to relabel the actual local A1/A2 course.'],
        ['url'=>'https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/active-passive-voice','access'=>'Main confirmed direct editorial body plus separately identified Peter M. editorial reply on both by-agent/time orders; learner comments are not rules.'],
        ['url'=>'https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/reported-speech-statements','access'=>'Main confirmed direct editorial body read for perspective, pronouns and backshift; the own task explicitly fixes the past state.'],
        ['url'=>'https://learnenglishteens.britishcouncil.org/grammar/a1-a2-grammar/some-any-every-no','access'=>'Main confirmed direct editorial body/conversation read; ordinary positive/negative tendency is not an absolute question rule.'],
        ['url'=>'https://learnenglishteens.britishcouncil.org/grammar/b1-b2-grammar/question-tags','access'=>'Main confirmed direct editorial body read for ordinary confirmation polarity/auxiliary/pronoun, not all same-polarity uses.'],
        ['url'=>'https://dictionary.cambridge.org/grammar/british-grammar/infinitive-or-verb-ing','access'=>'Main confirmed indexed primary editorial excerpt only, not a direct full-body claim. Per-lesson agent access at its exact URL remains separately recorded.'],
        ['url'=>'https://developers.google.com/search/docs/fundamentals/creating-helpful-content','access'=>'Main confirmed direct primary guidance read; no indexing, ranking or local-publication guarantee.'],
    ],
    'boundaries'=>[
        '36 own graded tasks: twelve form, twelve meaning and twelve full manual/token inputs. No existing native practice was present in the independently captured six target sources/current SQL.',
        'Six widgets select thirty existing questions. Own tasks, theory widgets and original course questions are counted separately. F reads five existing linked FinalDrillA2 originals on unsaved theory-only clones; no course model/hint/membership/progress write.',
        'A-D have eight explicit box type/heading transitions. E/F retain their four native source block functions; eight expressly approved footer data URLs are added to their original non-current items, preserving all five labels/order/current flags. The shared native validator, view and resolver remain unchanged.',
        'Continuing UUID/id/order/columns/levels, including SQL NULL, remain exact. The package appends six native practice blocks; it does not retag questions or rewrite courses to fit the selection.',
        'A1/A2 actual course bindings remain sixteen lessons and final-drill twenty-four questions each, with completion window100/minimum4.5. Existing extra questions in the first two A1 lessons are preserved, not replaced from historical seed JSON.',
        'Finite source-topic coverage is explicit. A1 there is/are and other ungraded forms, plus A2 relative/gerund/time-expression submodels, remain visible/linkable without a false full-course or CEFR assessment claim.',
        'Aliases are finite contextual alternatives. Shared formatter, grading, token identity, every/shift labels, reset/score, native case/punctuation behavior and all visual sources remain unchanged.',
        'Known unsafe old candidates are excluded from the finite five, not repaired by changing shared keys/options or the formatter. Original options/hints are snapshot-bound and retained.',
        'Meaningful details belong to finite points; essential rules, translations and control criteria remain visible in basic. Full content stays server-rendered.',
        'Freeze and isolated/offline probes are not real working-local or browser acceptance. Guarded apply, postconditions/no-op, all-old-row preservation and actual page acceptance remain separate required evidence.',
    ]];

$json=static fn($v)=>json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$outputs=[Package::BINDINGS=>$json($author['native_bindings']),Package::BEFORE=>$json($author['source_before']),
    Package::SOURCE=>$json($patch),Package::MASTER=>$json($master),Package::CONTRACT=>$json($author['contract']),
    'docs/content/m82-common-mistakes-level-revision.components.v1.json'=>$json($author['component_map']),
    'docs/content/m82-common-mistakes-level-revision.tasks.v1.json'=>$json($author['task_map']),
    'docs/content/m82-common-mistakes-level-revision.editorial.v1.json'=>$json($editorial),
    'docs/content/m82-sentence-builder-bank-audit.v1.json'=>$json($bank),
    'docs/content/m82-common-mistakes-level-revision.coverage.v1.json'=>$json($coverage)];
$plain=static fn($v)=>trim(html_entity_decode(strip_tags((string)$v),ENT_QUOTES|ENT_HTML5,'UTF-8'));
$walk=static function($data)use(&$walk,$plain) {
    $out='';foreach ($data as $key=>$value) {
        if (in_array($key,['id','source_index','key','type','column','accent','color','icon','current','design','linked_practice','seeder_classes','m82_native_v1','m82_detail_key','level'],true)) { continue; }
        if (is_array($value)) { $out.=$key==='feedback'?"\nПояснення відповідей:\n\n":'';$out.=$walk($value);continue; }
        if (is_bool($value)||$value===null||trim((string)$value)==='') { continue; }
        $label=['answer'=>'Ключ','question'=>'Умова','prompt'=>'Умова','translation'=>'Переклад','title'=>'Пункт','subtitle'=>'Пояснення','text'=>'Пояснення','en'=>'Англійською','ua'=>'Українською','url'=>'Посилання'][$key]??'';
        $out.=($label===''?'':$label.': ').$plain($value)."\n\n";
    }return $out;
};
$md="# M82 — типові помилки та змішане повторення A1/A2\n\nАвторська редакція 1.0.1. Чинний канонічний дизайн і механізм оцінювання не змінюються. Вісім погоджених локальних URL доповнюють незмінні підписи навігації A1/A2.\n\n";
foreach ($master['lessons'] as $lesson) {
    $md.='## '.$lesson['preserved_h1']."\n\n".$lesson['local_url']."\n\n".$plain($lesson['subtitle_html'])."\n\n";
    foreach ($lesson['blocks'] as $block) { $md.='### '.$block['key']."\n\n".$walk($block['data']); }
    foreach ($lesson['details'] as $detail) { $md.='### Докладніше: '.$detail['title']."\n\n".$walk($detail['fragment']['data']); }
}
$outputs['docs/content/m82-common-mistakes-level-revision.v1.0.1.uk.md']=rtrim($md)."\n";
$definitions=[];foreach ($patch['targets'] as $target) { $definitions[$target['path']]=$json($target['after']); }
$checks=['schema_version'=>1,'package'=>'m82-common-mistakes-level-revision','version'=>'1.0.1','status'=>'frozen',
    'hash_algorithm'=>'SHA-256 raw UTF-8 bytes','pre_hash_checks'=>['utf8_bom'=>false,'eol'=>'LF','exactly_one_final_lf'=>true,'trailing_whitespace'=>false],
    'artifacts'=>[],'active_definitions'=>[],'author_inputs'=>[],'notes'=>$editorial['boundaries']];
$eol=static function($path,$bytes) {
    if (str_contains($bytes,"\r")||str_starts_with($bytes,"\xEF\xBB\xBF")||!str_ends_with($bytes,"\n")||str_ends_with($bytes,"\n\n")||preg_match('/[\t ]+$/m',$bytes)) {
        throw new RuntimeException('M82 pre-hash UTF8/LF/single EOF policy failed: '.$path);
    }
};
foreach ([$outputs,$definitions] as $i=>$group) { foreach ($group as $path=>$bytes) { $eol($path,$bytes);$checks[$i===0?'artifacts':'active_definitions'][]=['path'=>$path,'sha256'=>hash('sha256',$bytes),'bytes'=>strlen($bytes)]; } }
$inputs=array_keys($reviewedInputs);$inputPins=$reviewedInputs;
foreach ($author['draft_sha256'] as $name=>$sha) {
    $path='docs/content/m82-author-parts/'.$name.'.v1.json';if (!hash_equals($sha,hash_file('sha256',$wt.'/'.$path))) { throw new RuntimeException('M82 reviewed author input changed.'); }$inputs[]=$path;$inputPins[$path]=$sha;
}
foreach ($inputs as $path) {
    if (!is_file($wt.'/'.$path)||is_link($wt.'/'.$path)) { throw new RuntimeException('M82 reviewed input unavailable.'); }
    $bytes=file_get_contents($wt.'/'.$path);$eol($path,$bytes);
    if(!hash_equals($inputPins[$path],hash('sha256',$bytes))){throw new RuntimeException('M82 reviewed input changed during freeze.');}
    $checks['author_inputs'][]=['path'=>$path,'sha256'=>hash('sha256',$bytes),'bytes'=>strlen($bytes)];
}
$selectionPin=$bank['source_proof']['author_review_sha256']['docs/content/m82-widget-selection.v1.json']??null;
if (!is_string($selectionPin)||!hash_equals($selectionPin,hash_file('sha256',$wt.'/docs/content/m82-widget-selection.v1.json'))) { throw new RuntimeException('M82 bank selection provenance changed.'); }
$outputs[Package::CHECKSUMS]=$json($checks);$beforeBytes=[];$replaceBeforeBytes=[];$removeBeforeBytes=[];
$oldArtifactPaths=array_values(array_diff(array_keys($previousPlan['outputs']),array_keys($previousPlan['before_bytes'])));
$oldMarkdown='docs/content/m82-common-mistakes-level-revision.v1.0.0.uk.md';
$newMarkdown='docs/content/m82-common-mistakes-level-revision.v1.0.1.uk.md';
if(count($oldArtifactPaths)!==12||count($outputs)!==12
    ||array_values(array_diff($oldArtifactPaths,array_keys($outputs)))!==[$oldMarkdown]
    ||array_values(array_diff(array_keys($outputs),$oldArtifactPaths))!==[$newMarkdown]){
    throw new RuntimeException('M82 exact revision output path transition differs.');
}
foreach($oldArtifactPaths as $path){
    if(is_link($wt.'/'.$path)||!is_file($wt.'/'.$path)){
        throw new RuntimeException('M82 exact V1 artifact required before revision: '.$path);
    }
    $current=file_get_contents($wt.'/'.$path);
    if($current!==$previousPlan['outputs'][$path]){throw new RuntimeException('M82 V1 artifact bytes changed before revision: '.$path);}
    if(array_key_exists($path,$outputs)){$replaceBeforeBytes[$path]=$current;}
    else{$removeBeforeBytes[$path]=$current;}
}
if(file_exists($wt.'/'.$newMarkdown)||is_link($wt.'/'.$newMarkdown)){
    throw new RuntimeException('M82 new revision Markdown already exists.');
}
foreach ($definitions as $path=>$bytes) {
    $old=array_values(array_filter($author['source_before']['targets'],static fn($t)=>$t['path']===$path));
    if(is_link($wt.'/'.$path)||!is_file($wt.'/'.$path)||count($old)!==1
        ||!array_key_exists($path,$previousPlan['before_bytes'])){throw new RuntimeException('M82 original active source is unavailable.');}
    $current=file_get_contents($wt.'/'.$path);
    if($current!==$previousPlan['before_bytes'][$path]||json_decode($current,true,flags:JSON_THROW_ON_ERROR)!==$old[0]['before']) {
        throw new RuntimeException('M82 active source must be restored to exact original BEFORE before revised freeze.');
    }
    $beforeBytes[$path]=$current;
}
$plan=['schema_version'=>1,'revision'=>'1.0.1','supersedes_private_plan_sha256'=>$previousPlanSha,
    'author_projection_sha256'=>$argv[2],'bank_projection_sha256'=>$argv[4],
    'prospective_metadata_sha256'=>$argv[6],'outputs'=>$outputs+$definitions,'before_bytes'=>$beforeBytes,
    'replace_before_bytes'=>$replaceBeforeBytes,'remove_before_bytes'=>$removeBeforeBytes,
    'artifacts'=>$checks['artifacts'],'active_definitions'=>$checks['active_definitions'],'checksums_sha256'=>hash('sha256',$outputs[Package::CHECKSUMS]),
    'coverage_rows'=>array_map(static fn($target)=>count($target['rows']),$coverage['targets']),
    'db_connections'=>0,'db_writes'=>0,'http_requests'=>0];
$file=$dir.'/freeze-plan-v2.json';if (file_exists($file)||is_link($file)) { throw new RuntimeException('M82 exclusive freeze plan already exists.'); }
$bytes=$json($plan);$h=fopen($file,'x');if (!$h) { throw new RuntimeException('M82 private freeze plan unavailable.'); }
try { if (fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)) { throw new RuntimeException('M82 freeze plan incomplete.'); } } finally { fclose($h); }
echo $json(['file'=>'freeze-plan-v2.json','revision'=>'1.0.1','sha256'=>hash('sha256',$bytes),'artifacts'=>$checks['artifacts'],
    'same_path_replacements'=>count($replaceBeforeBytes),'exact_old_markdown_removals'=>count($removeBeforeBytes),
    'active_definitions'=>$checks['active_definitions'],'checksums_sha256'=>$plan['checksums_sha256'],'db_writes'=>0,'active_source_writes'=>0]);
