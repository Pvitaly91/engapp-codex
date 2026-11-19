<?php

namespace App\Support;

/** Explicit guidance and finite grammatical corrections for thirty exact retained questions. */
final class FormalEnglishWidgetPlans
{
    public const SNAPSHOTS_SHA='44a71ac9a5c094d20968c06e984296c181120d620c829be381beb223ecafc47f';
    public static function ids():array{return[
        'formal-register-and-nominalisation-basics'=>[17518,17530,17526,17507,17536],
        'nominalisation-formal-register'=>[18071,18055,18079,18075,18064],
        'register-tone-and-paraphrase'=>[18376,18384,18411,18404,18415],
        'paraphrase-and-reformulation'=>[19118,19104,19116,19133,19124],
        'subjunctive-and-formal-structures'=>[18208,18203,18201,18181,18220],
        'nominal-style-and-information-density'=>[19075,19045,19085,19060,19073],
    ];}
    public static function hints():array{return[
        17518=>'Почни з іменника аналізу результатів; збережи тривалість кілька тижнів і минулий час.',
        17530=>'Назви здатність пристосовуватися; збережи її важливість у сучасній роботі, не лише в одному випадку.',
        17526=>'Почни зі схвалення конкретної пропозиції; порівняй фактичну тривалість з очікуваною.',
        17507=>'Використай безособове повідомлення про значне минуле поліпшення; назви саме якість обслуговування.',
        17536=>'Почни з невиконання інструкцій; збережи минулу причину плутанини, а не тільки послідовність подій.',
        18071=>'Назви організацію та невдале забезпечення захисту саме цих даних; використай іменникову назву захисту.',
        18055=>'Збережи дослідників і поведінку дітей; використай іменникову назву спостережень у минулому.',
        18079=>'Уведи питальне слово; запитай про конкретне покращення команди, використовуючи іменникову конструкцію.',
        18075=>'Запитай про минуле порівняння двох визначених методів; використай іменникову назву порівняння.',
        18064=>'Назви потребу в ретельному оцінюванні даних пасивною минулою формою; виконавця не задано.',
        18376=>'Збережи нас як майбутніх виконавців і конкретну пропозицію адресата; передай розгляд, а не вже ухвалене рішення.',
        18384=>'Покажи, що є ситуації, для яких підхід не підходить; не стверджуй повну непридатність.',
        18411=>'Передай стриману можливість посилення звіту, а не категоричну образливу оцінку.',
        18404=>'Уведи питальне слово; запитай про майбутній вплив саме на кінцевий результат.',
        18415=>'Почни з цього чинника; назви його внесок у поліпшення системи, не єдину доведену причину.',
        19118=>'Передай можливу інтерпретацію саме цього результату як часткового, не повного успіху.',
        19104=>'Уведи все загальне запитання; суть твердження — недостатність доказів, не їх повна відсутність.',
        19116=>'Протистав доказ провалу й ознаку обережності; запереч лише перше, не саму обережність.',
        19133=>'Передай фактичне за змістом визнання недосконалості процесу; не додавай повної непридатності.',
        19124=>'Почни стислим підсумковим переходом; рішення ще потрібно відкласти, а не вже відкладено.',
        18208=>'Почни безособовою конструкцією про давно назрілу дію; використай минулу форму для бажаної теперішньої зміни.',
        18203=>'Почни конструкцією про вже належний час; відповідальність має взяти співрозмовник, не мовець.',
        18201=>'Почни конструкцією про вже належний час; перегляд стратегії потрібен нам зараз, не підтверджений у минулому.',
        18181=>'Використай офіційну базову форму без додаткового модального слова; вимога охоплює кожен відділ.',
        18220=>'Збережи наше бажання щодо зустрічі; після конструкції переваги використай минулу форму для бажаної події.',
        19075=>'Уведи питальне слово про належність участі; збережи переговори та найвищу важливість.',
        19045=>'Почни з відмови конкретної компанії; передай належність правильною присвійною формою, коментування й викликану критику.',
        19085=>'Назви підвищення обізнаності громадськості як важливу діяльність, не вже підтверджений минулий результат.',
        19060=>'Почни з визнання конкретної помилки; збережи допомогу у відновленні довіри, не гарантію повного відновлення.',
        19073=>'Уведи питальне слово про час; почни з нього та запитай про майбутню можливість завершення конкретного звіту.',
    ];}
    public static function conditions():array{return[
        17518=>'Аналіз результатів зайняв кілька тижнів.',17530=>'Здатність адаптуватися важлива в сучасній роботі.',17526=>'Схвалення пропозиції зайняло більше часу, ніж очікувалося.',17507=>'Відбулося значне покращення якості обслуговування.',17536=>'Невиконання інструкцій спричинило плутанину.',
        18071=>'Організація не забезпечила захисту цих даних.',18055=>'Дослідники спостерігали за поведінкою дітей.',18079=>'Яке покращення зробила команда?',18075=>'Чи порівняли експерти ці два методи?',18064=>'Було потрібне ретельне оцінювання даних.',
        18376=>'Ми розглянемо вашу пропозицію.',18384=>'Цей підхід не підходить для всіх ситуацій.',18411=>'Звіт можна було б посилити.',18404=>'Як це вплине на кінцевий результат?',18415=>'Це допомогло покращити систему.',
        19118=>'Цей результат можна розглядати як частковий успіх.',19104=>'Чи суть у тому, що доказів недостатньо?',19116=>'Це не доказ провалу, а ознака обережності.',19133=>'Він фактично визнав, що процес був недосконалим.',19124=>'Коротко кажучи, рішення потрібно відкласти.',
        18208=>'Давно час їм інвестувати в безпеку.',18203=>'Вже час тобі взяти на себе відповідальність.',18201=>'Вже час нам переглянути стратегію.',18181=>'Важливо, щоб кожен відділ дотримувався процедури.',18220=>'Ми б воліли, щоб зустріч відбулася онлайн.',
        19075=>'Чия участь у переговорах була найважливішою?',19045=>'Відмова компанії коментувати ситуацію викликала критику.',19085=>'Підвищення обізнаності громадськості є важливим.',19060=>'Визнання помилки допомогло відновити довіру.',19073=>'Коли завершення звіту стане можливим?',
    ];}
    public static function instructions():array{$out=[];foreach(self::ids()as$ids)foreach($ids as$id)$out[$id]='Склади повне речення; збережи зміст і формальну конструкцію.';return$out;}
    public static function correctedTokens(int$id,array$tokens):array
    {
        $corrections=[17536=>[3,'Follow','follow'],18181=>[6,'Follow','follow'],19116=>[10,'Caution','caution'],19045=>[1,'company','company’s']];
        if(isset($corrections[$id])){[$slot,$raw,$corrected]=$corrections[$id];if(($tokens[$slot]??null)!==$raw)throw new \RuntimeException('Exact retained widget token correction source differs.');$tokens[$slot]=$corrected;}return$tokens;
    }
    public static function correctedOptions(int$id,array$options):array
    {
        $case=[17536=>['Follow','follow'],18181=>['Follow','follow'],19116=>['Caution','caution']][$id]??null;
        return $case===null?$options:array_map(static fn($value)=>$value===$case[0]?$case[1]:$value,$options);
    }
    public static function tokenLabels(int$id,array$tokens,array$accepted):array
    {
        $labels=FormalEnglishPracticeSupplement::tokenLabels($tokens,$accepted);if($id===19045)unset($labels['company’s']);return$labels;
    }
    public static function aliases():array{return[
        17518=>['Analysis of the results took several weeks'],17530=>['The ability to adapt is important in the modern workplace','The ability to adapt is important in today’s workplace'],17526=>['Approval of the proposal took longer than expected'],17507=>['There was a substantial improvement in service quality','There was a considerable improvement in service quality','There was a marked improvement in service quality'],17536=>['The failure to follow instructions led to confusion','Failure to follow instructions caused confusion','Failure to follow instructions led to confusion'],
        18071=>['The organisation failed to ensure the protection of the data'],18055=>['The researchers made observations of children’s behavior','The researchers conducted observations of children’s behaviour','The researchers conducted observations of children’s behavior'],18079=>['What improvement has the team made'],18075=>['Did the experts carry out a comparison between the two methods','Did the experts conduct a comparison between the two methods'],18064=>['A careful assessment of the data was required','A thorough evaluation of the data was required','A thorough assessment of the data was required'],
        18376=>['We will consider your proposal'],18384=>['This approach is not appropriate for all situations','This approach is not suitable for every situation','This approach is not appropriate for every situation'],18411=>['The report could be improved','The report could be made stronger'],18404=>['How will this influence the final outcome','How will this affect the final result','How will this influence the final result'],18415=>['This contributed to improving the system','This helped improve the system','This helped to improve the system'],
        19118=>['This result can be seen as a partial success','This result can be viewed as a partial success','This result may be regarded as a partial success'],19104=>['Is the point that there is insufficient evidence','Is the point that there is not enough evidence'],19116=>['This is not proof of failure but a sign of caution','This is not evidence of failure but an indication of caution','This is not proof of failure but an indication of caution'],19133=>['He effectively acknowledged that the process was flawed','He in effect admitted that the process was flawed'],19124=>['In short the decision needs to be delayed','In brief the decision needs to be postponed','In brief the decision needs to be delayed'],
        18208=>['It is high time they invested in safety'],18203=>[],18201=>['It is time we reconsidered the strategy'],18181=>['It is important that each department follow the procedure'],18220=>['We would prefer the meeting to take place online'],
        19075=>['Whose involvement in the negotiations was the most important'],19045=>["The company's refusal to comment on the situation attracted criticism",'The company’s refusal to comment on the situation drew criticism',"The company's refusal to comment on the situation drew criticism"],19085=>['Raising public awareness is important'],19060=>['The acknowledgment of the mistake helped restore trust','Acknowledging the mistake helped restore trust','The acknowledgement of the error helped restore trust','The acknowledgment of the error helped restore trust'],19073=>['When will completion of the report become possible','When will it become possible to complete the report'],
    ];}
}
