<?php
namespace App\Support;

/** Finite complete alternatives for twenty retained, exact-bound thematic questions. */
final class SentenceStructureWidgetPlans
{
    public const SNAPSHOTS_SHA='921605a0b7a6d1f120cc74fedc3e1882d192ddea2b7d44e2e3d85238134e1517';
    public static function ids():array{return[
        'cleft-sentences-basics'=>[17396,17370,17390,17381,17368],
        'cleft-sentences-emphasis'=>[17844,17852,17845,17863,17848],
        'ellipsis-substitution-and-reference'=>[18603,18572,18593,18599,18582],
        'complex-noun-phrases'=>[18609,18610,18611,18612,18613],
    ];}
    public static function hints():array{return[
        17396=>'Почни з назви місця; наступна частина уточнює, де вони перебували, а завершення вказує на річку.',
        17370=>'Виділи останнє питання, не студентів; збережи минулий час і значення розгубленості.',
        17390=>'Почни з місця зустрічі; назви маленьке кафе як це місце, не як учасника зустрічі.',
        17381=>'Почни з того, що ми засвоїли; виділена частина називає важливість підготовки.',
        17368=>'Виділи саме ціну; збережи теперішній час і те, що вона турбує більшість клієнтів.',
        17844=>'Виділи Лондон як місце першої зустрічі; не змінюй учасників або минулий час.',
        17852=>'Виділи Тома як автора пропозиції; іншим було саме запропоноване рішення.',
        17845=>'Виділи мого брата як того, хто знайшов помилку; збережи минулий час.',
        17863=>'Почни з того, що я зараз намагаюся сказати; оцінка стосується простоти цього повідомлення.',
        17848=>'Виділи клієнта як прохача; він попросив більше подробиць, а не надав їх.',
        18603=>'Відсилання в другій частині стосується всього факту відхилення пропозиції; це здивувало інвесторів.',
        18572=>'Збережи минуле твердження про точність звіту й теперішню відсутність підтвердження; заміна має відсилати до твердження.',
        18593=>'Назви таку позицію; передай лише можливість враження надмірної категоричності, не встановлений факт.',
        18599=>'Почни з указування на причину; рішення потребує перегляду, але не стверджуй, що його вже переглянули.',
        18582=>'Передай нереалізовану можливість минулого; у другій частині заміни повтор дії й збережи заперечення.',
        18609=>'Головне слово називає зростання; воно стосується цін на енергію та вже вплинуло на малі підприємства.',
        18610=>'Головне слово називає кілька досліджень; вони нещодавно опубліковані й тепер ставлять під сумнів припущення.',
        18611=>'Головне слово називає попит на доступне житло; саме цей попит створює політичний тиск.',
        18612=>'Назви довгострокові наслідки швидкої автоматизації; невизначеність цих наслідків зберігається.',
        18613=>'Почни з багатьох низькооплачуваних працівників; їм важко покривати житлові витрати, не обов’язково лише оренду.',
    ];}
    public static function conditions():array{return[
        17396=>'Місце, де вони зупинилися, було біля річки.',
        17370=>'Саме останнє питання збило студентів з пантелику.',
        17390=>'Місцем, де ми зустрілися, було маленьке кафе.',
        17381=>'Ми засвоїли саме важливість підготовки.',
        17368=>'Саме ціна турбує більшість клієнтів.',
        17844=>'Саме в Лондоні ми вперше зустрілися.',
        17852=>'Саме Том запропонував інше рішення.',
        17845=>'Саме мій брат знайшов помилку.',
        17863=>'Те, що я зараз намагаюся сказати, досить просте.',
        17848=>'Саме клієнт попросив додаткові подробиці.',
        18603=>'Комітет відхилив пропозицію; це здивувало інвесторів.',
        18572=>'Вони стверджували, що звіт точний, але докази цього не підтверджують.',
        18593=>'Така позиція може здатися надто категоричною.',
        18599=>'Саме тому це рішення треба переглянути.',
        18582=>'Вони могли б обрати простіший варіант, але не зробили цього.',
        18609=>'Різке зростання цін на енергію вплинуло на малі підприємства.',
        18610=>'Кілька нещодавно опублікованих досліджень про забруднення повітря ставлять під сумнів старі припущення.',
        18611=>'Зростаючий попит на доступне житло створює політичний тиск.',
        18612=>'Довгострокові наслідки швидкої автоматизації залишаються невизначеними.',
        18613=>'Багатьом низькооплачуваним працівникам важко покривати витрати на житло.',
    ];}
    public static function instructions():array{$out=[];foreach(self::ids()as$ids)foreach($ids as$id)$out[$id]='Склади повне речення; збережи виділення або відсилання та зміст.';return$out;}
    public static function corrections():array{return[
        17396=>[2,'Where','where'],17370=>[8,'Students','students'],17390=>[2,'Where','where'],
        17852=>[3,'Who','who'],17845=>[4,'Who','who'],17848=>[4,'Who','who'],
        18572=>[12,'so','that'],18599=>[3,'Why','why'],18611=>[2,'Demand','demand'],
    ];}
    public static function correctedTokens(int$id,array$tokens):array
    {
        if(isset(self::corrections()[$id])){[$slot,$raw,$correct]=self::corrections()[$id];if(($tokens[$slot]??null)!==$raw)throw new \RuntimeException('Exact sentence-structure token source differs.');$tokens[$slot]=$correct;}return$tokens;
    }
    public static function correctedOptions(int$id,array$options):array
    {
        if(!isset(self::corrections()[$id]))return$options;[, $raw,$correct]=self::corrections()[$id];return array_map(static fn($v)=>$v===$raw?$correct:$v,$options);
    }
    public static function tokenLabels(int$id,array$tokens,array$accepted):array{return SentenceStructurePracticeSupplement::tokenLabels($tokens,$accepted);}
    public static function aliases():array
    {
        $out=[];foreach(self::ids()as$ids)foreach($ids as$id)$out[$id]=[];
        foreach(['near','by','beside']as$prep){$out[17396][]='The place where they stayed was '.$prep.' the river';$out[17396][]='It was '.$prep.' the river that they stayed';}
        foreach(['last','final']as$adjective)foreach(['that','which']as$link)foreach(['confused','puzzled','baffled','perplexed']as$verb)$out[17370][]='It was the '.$adjective.' question '.$link.' '.$verb.' the students';
        foreach(['café','cafe']as$noun){$out[17390][]='The place where we met was a small '.$noun;$out[17390][]='It was in a small '.$noun.' that we met';}
        foreach(['learned','learnt']as$verb)foreach(['preparation','being prepared']as$noun)$out[17381][]='What we '.$verb.' was the importance of '.$noun;
        foreach(['that','which']as$link)foreach(['concerns','worries','troubles']as$verb)foreach(['customers','clients']as$noun)$out[17368][]='It is the price '.$link.' '.$verb.' most '.$noun;
        $out[17844]=['It was in London that we first met','It was in London that we met for the first time'];
        foreach(['who','that']as$link)foreach(['suggested','proposed']as$verb)foreach(['another','a different']as$determiner)$out[17852][]='It was Tom '.$link.' '.$verb.' '.$determiner.' solution';
        foreach(['who','that']as$link)foreach(['found','discovered','spotted','detected']as$verb)foreach(['mistake','error']as$noun)$out[17845][]='It was my brother '.$link.' '.$verb.' the '.$noun;
        foreach(['quite','rather','fairly']as$degree)foreach(['simple','straightforward']as$adjective)$out[17863][]='What I am trying to say is '.$degree.' '.$adjective;
        foreach(['client','customer']as$noun)foreach(['who','that']as$link)foreach(['asked for','requested']as$verb)foreach(['more','additional']as$quantity)$out[17848][]='It was the '.$noun.' '.$link.' '.$verb.' '.$quantity.' details';
        foreach(['committee','panel']as$noun)foreach(['rejected','turned down']as$verb)foreach(['this surprised','which surprised']as$link)$out[18603][]='The '.$noun.' '.$verb.' the proposal, '.$link.' the investors';
        foreach(['','that ']as$link)foreach(['does not show that','does not show this','does not confirm that','does not confirm this','does not support that','does not support this']as$end)$out[18572][]='They claimed '.$link.'the report was accurate but the evidence '.$end;
        foreach(['position','stance']as$noun)foreach(['may','might']as$modal)foreach(['seem','appear']as$verb)foreach(['too','overly']as$degree)$out[18593][]='Such a '.$noun.' '.$modal.' '.$verb.' '.$degree.' categorical';
        foreach(['precisely','exactly']as$adverb)foreach(['needs to be reviewed','has to be reviewed','needs reviewing','requires review','needs to be reconsidered']as$verb)$out[18599][]='This is '.$adverb.' why the decision '.$verb;
        foreach(['chosen','selected']as$verb)foreach(['simpler','easier']as$adjective)foreach(['option','alternative']as$noun)foreach(['did not do so','they did not do so']as$end)$out[18582][]='They could have '.$verb.' a '.$adjective.' '.$noun.' but '.$end;
        foreach(['sharp','steep']as$adjective)foreach(['increase','rise']as$noun)foreach(['has affected','affected']as$verb)foreach(['businesses','firms','enterprises']as$business)$out[18609][]='The '.$adjective.' '.$noun.' in energy prices '.$verb.' small '.$business;
        foreach(['Several','A few','A number of']as$quantity)foreach(['on','about']as$prep)foreach(['challenge','call into question','cast doubt on']as$verb)$out[18610][]=$quantity.' recently published studies '.$prep.' air pollution '.$verb.' old assumptions';
        foreach(['growing','increasing']as$adjective)foreach(['creates','generates']as$verb)$out[18611][]='The '.$adjective.' demand for affordable housing '.$verb.' political pressure';
        foreach(['long term','long-term']as$modifier)foreach(['effects','consequences']as$noun)foreach(['rapid','fast']as$adjective)foreach(['uncertain','unclear']as$state)$out[18612][]='The '.$modifier.' '.$noun.' of '.$adjective.' automation remain '.$state;
        foreach(['low paid','low-paid','poorly paid']as$modifier)foreach(['struggle with','have difficulty covering','have trouble covering']as$verb)foreach(['costs','expenses']as$noun)$out[18613][]='Many '.$modifier.' workers '.$verb.' housing '.$noun;
        return array_map(static fn($rows)=>array_values(array_unique($rows)),$out);
    }
}
