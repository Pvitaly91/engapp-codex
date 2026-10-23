<?php

/** Finite teaching selection from the fresh private BEFORE; no bootstrap/SQL/source mutation. */
if (PHP_SAPI!=='cli'||count($argv)!==2||preg_match('/^widget-selection-plan-v[1-9][0-9]*\.json$/D',$argv[1])!==1) {
    throw new RuntimeException('M83 requires one exclusive private selection receipt basename.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m83-local';
require $root.'/vendor/autoload.php';
$beforeSha='d522a715b123748b84f52f076b156ccf5224892f138d5429062d9527a843e33e';
$file=$dir.'/before-v1.json';
if(is_link($file)||!is_file($file)||!hash_equals($beforeSha,hash_file('sha256',$file))){throw new RuntimeException('M83 fresh BEFORE changed.');}
$before=json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
$names=['B2MixedRevisionTheorySeeder','BasicGrammarPartsOfSpeechTheorySeeder','BasicGrammarSentenceStructureSvoTheorySeeder',
    'BasicGrammarSentenceTypesTheorySeeder','BasicGrammarImperativesTheorySeeder','BasicGrammarConjunctionsTheorySeeder'];
$banks=['PolyglotB2MixedRevisionAllLevelsLessonSeeder','PolyglotPartsOfSpeechAllLevelsLessonSeeder',
    'PolyglotSentenceStructureSvoAllLevelsLessonSeeder','PolyglotSentenceTypesAllLevelsLessonSeeder',
    'PolyglotImperativesAllLevelsLessonSeeder','PolyglotConjunctionsAllLevelsLessonSeeder'];
// Literal independent editorial decisions, in the native UUID order. Only conditions change on exact-bound unsaved clones.
$rows=[
    [
        ['mr-b2-poly-b2-01','Завтра в цей час ми будемо в польоті до Берліна. Почни з обставини «завтра в цей час»; передай процес у зазначений майбутній момент.','This time tomorrow, we will be flying to Berlin.','future continuous; explicit reference time'],
        ['mr-b2-poly-b2-02','До п’ятниці перевірку звіту вже завершать. Почни зі звіту та передай майбутній завершений результат у пасиві.','The report will have been checked by Friday.','future perfect passive; completed result by a deadline'],
        ['mr-b2-poly-b2-03','Тобі слід було сказати мені раніше, але ти цього не зробив. Передай докір щодо минулого, а не теперішню пораду.','You should have told me earlier.','modal perfect; unperformed past advice'],
        ['mr-b2-poly-b2-04','Тоді я цього не знав і не допоміг. Склади: якби я тоді знав, я б допоміг. Почни з умовної частини; обидві ситуації стосуються минулого.','If I had known, I would have helped.','third conditional; past counterfactual without mandatory regret'],
        ['mr-b2-poly-b2-06','Хоч план був ризикованим, його схвалили. Почни з допустової частини зі значенням «хоча»; збережи пасив у головній частині.','Although the plan was risky, it was approved.','concession and past passive'],
    ],
    [
        ['poly-pos-a1-01','Я читаю цікаву книгу. Склади речення з прикметником перед назвою предмета.','I read an interesting book.','pronoun, verb, article, adjective, noun in use'],
        ['poly-pos-a1-02','Вона швидко біжить. Назви дію та спосіб її виконання.','She runs quickly.','adverb modifies the action; third-person verb'],
        ['poly-pos-a1-03','Ми в школі на заняттях. Передай звичайне перебування в школі, а не розташування всередині будівлі.','We are at school.','pronoun, linking verb and contextual preposition'],
        ['poly-pos-a2-01','Мій друг часто допомагає мені. Збережи прислівник частотності перед основним дієсловом.','My friend often helps me.','possessive determiner and object pronoun; frequency adverb'],
        ['poly-pos-a2-02','Вони купили червоні яблука вчора. Збережи ознаку предметів та постав обставину часу наприкінці.','They bought red apples yesterday.','adjective versus adverb; past verb in full sentence'],
    ],
    [
        ['poly-ss-svo-a1-01','Анна читає книгу. Побудуй нейтральне стверджувальне речення: спочатку підмет, потім дія та додаток.','Anna reads a book.','neutral SVO'],
        ['poly-ss-svo-a1-05','Вона купила червону машину. Збережи прикметник перед іменником усередині додатка.','She bought a red car.','noun phrase as object'],
        ['poly-ss-svo-a1-06','Він читає книжки вдома. Почни з підмета; обставину місця постав після додатка.','He reads books at home.','object before neutral place adjunct'],
        ['poly-ss-svo-a1-10','Я не дивлюся телевізор щодня. Передай звичайне заперечення; обставину частотності постав наприкінці.','I do not watch TV every day.','negative auxiliary and base verb'],
        ['poly-ss-svo-a1-11','Постав нейтральне загальне запитання, не перепитування зі стверджувальним порядком: вона читає книжки?','Does she read books?','neutral question order'],
    ],
    [
        ['poly-stype-a1-01','Вона живе тут. Передай звичайне стверджувальне повідомлення.','She lives here.','declarative affirmative'],
        ['poly-stype-a1-02','Вона тут не живе. Передай заперечне повідомлення, не запитання.','She does not live here.','declarative negative; separate polarity axis'],
        ['poly-stype-a1-03','Постав нейтральне загальне запитання про вподобання: тобі подобається чай?','Do you like tea?','yes/no interrogative'],
        ['poly-stype-a1-04','Де ти живеш? Постав пряме нейтральне запитання про місце.','Where do you live?','wh-interrogative'],
        ['poly-stype-a1-05','Відкрий двері. Склади звичайне спонукальне речення без окремого наголошеного підмета.','Open the door.','imperative with understood addressee'],
    ],
    [
        ['poly-imp-a1-02','Не відкривай коробку. Склади звичайну заборону без окремого наголошеного підмета.','Do not open the box.','negative imperative'],
        ['poly-imp-a1-03','Слухай уважно. Збережи прислівник способу дії; не додавай окремого підмета.','Listen carefully.','verb plus manner adverb without required object'],
        ['poly-imp-a1-06','Будь ласка, зачекай тут. Почни зі слова ввічливості; не додавай окремого наголошеного підмета.','Please wait here.','initial please and imperative'],
        ['poly-imp-a1-07','Будь обережним біля дороги. Використай наказову форму зв’язки та прикметник.','Be careful near the road.','be imperative and complement'],
        ['poly-imp-a1-10','Поклади це на стіл. Використай займенник it для названої речі.','Put it on the table.','imperative with object and place phrase'],
    ],
    [
        ['poly-conj-a1-01','Я люблю чай і каву. Поєднай обидві назви напоїв як додавання.','I like tea and coffee.','and: addition'],
        ['poly-conj-a1-02','Вона втомлена, але щаслива. Автор явно протиставляє ці дві ознаки; передай цей контраст.','She is tired but happy.','but: explicit intended contrast'],
        ['poly-conj-a2-01','Ти можеш залишитися або піти. Передай дві запропоновані альтернативи.','You can stay or leave.','or: alternatives without universal exclusive claim'],
        ['poly-conj-a2-02','Я залишився вдома, бо йшов дощ. Назви дощ причиною рішення залишитися; почни з рішення.','I stayed home because it was raining.','because: reason in second clause'],
        ['poly-conj-b1-01','Було пізно, тому ми пішли додому. Почни з причини, а далі назви її наслідок.','It was late so we went home.','so: result; short-clause no-comma form; existing B1 label retained'],
    ],
];
$out=['schema_version'=>1,'package'=>'m83-b2-revision-basic-grammar-widget-selection','version'=>'1.0.0',
    'independent_before_sha256'=>$beforeSha,
    'review'=>'Finite main-agent source/key/token/options review. Not human approval or browser acceptance.',
    'policy'=>['new_questions'=>0,'question_key_option_hint_writes'=>0,'editorial_focus'=>['B2','A1','A1','A1','A1','A1'],
        'actual_filter'=>['B2',null,null,null,null,null],
        'null_policy'=>'Five continuing practice rows are SQL NULL. Keep the native no-exact-CEFR filter; select existing A1/A2 Parts models and A1/A2/B1 Conjunctions models by actual semantics rather than retagging them or creating duplicates.',
        'coverage_limit'=>'Token construction does not automatically assess every word class or every B2 program topic. Full word-class analyses remain separately graded own tasks.',
        'course_boundary'=>'A uses only its exact page-linked primary theory bank; course-final membership and every original model remain unchanged.'],
    'targets'=>[]];
foreach($names as $i=>$name){
    $owner='Database\\Seeders\\Page_V3\\BasicGrammar\\'.$name;$page=$before['pages'][$owner]??null;
    if(!is_array($page)||$page['continuing_levels']!==array_fill(0,[5,15,13,12,13,13][$i],$i===0?'B2':null)){
        throw new RuntimeException('M83 captured continuing level vector differs.');
    }
    $bank='Database\\Seeders\\V3\\Polyglot\\'.$banks[$i];$selected=[];
    foreach($rows[$i] as [$uuid,$condition,$settled,$coverage]){
        $s=$page['relations']['question_snapshots'][$uuid]??null;$q=$s['question']??[];
        if(($q['uuid']??null)!==$uuid||($q['seeder']??null)!==$bank||(string)($q['type']??'')!=='4'
            ||($i===0&&$q['level']!=='B2')||($s['hints']??null)!==[]||($s['verb_hints']??null)!==[]
            ||preg_match('/<[^>]+>|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu',$condition)){
            throw new RuntimeException('M83 selected candidate snapshot/scope differs: '.$uuid);
        }
        $raw=array_column($s['ordered_answer_instances'],'option');$tokens=App\Support\ComposeTokenCase::normalize($raw);
        // Canonical formatter owns commas and terminal punctuation; this guard only checks word/case sequence.
        $words=static fn($v)=>preg_replace('/[.,?!]/u','',preg_replace('/\s+/u',' ',trim($v)));
        if($words(implode(' ',$tokens))!==$words($settled)){
            throw new RuntimeException('M83 literal settled sentence differs from native token sequence: '.$uuid);
        }
        $selected[]=['id'=>$q['id'],'uuid'=>$uuid,'level'=>$q['level'],'source'=>$q['question'],
            'condition'=>$condition,'translation'=>$condition,'settled'=>$settled,'raw_tokens'=>$raw,'tokens'=>$tokens,
            'original_options'=>array_column($s['options'],'option'),'original_hints'=>$s['hints'],
            'original_verb_hints'=>$s['verb_hints'],'coverage'=>$coverage];
    }
    $uuids=array_column($selected,'uuid');$sorted=$uuids;sort($sorted,SORT_STRING);
    if(count(array_unique($uuids))!==5||$uuids!==$sorted){throw new RuntimeException('M83 native UUID ordering differs.');}
    $out['targets'][]=['identity'=>$owner,'bank'=>$bank,'actual_practice_level'=>$i===0?'B2':null,
        'selected_levels'=>array_column($selected,'level'),'selected'=>$selected];
}
$json=static fn($v)=>json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$bytes=$json($out);$plan=['path'=>'docs/content/m83-widget-selection.v1.json','bytes'=>$bytes,'sha256'=>hash('sha256',$bytes),
    'selected'=>30,'new_questions'=>0,'db_connections'=>0,'db_writes'=>0,'http_requests'=>0];
$file=$dir.'/'.$argv[1];if(file_exists($file)||is_link($file)){throw new RuntimeException('M83 selection receipt exists.');}
$h=fopen($file,'x');if(!$h){throw new RuntimeException('M83 selection receipt unavailable.');}
try{$bytes=$json($plan);if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M83 selection receipt incomplete.');}}finally{fclose($h);}
echo $json(['receipt'=>$argv[1],'selection_sha256'=>$plan['sha256'],'selected'=>30,'new_questions'=>0,'active_source_writes'=>0]);
