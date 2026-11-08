<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Exact, finite UK theory teaching projection; no query, model mutation, style or grader. */
final class NounsArticlesQuantityPracticeSupplement
{
    public const SOURCE = 'docs/content/nouns-articles-quantity-practice-supplement.v1.json';
    public const SOURCE_SHA = '51b50d2140d1297716017473c51dab12703b7ad112058dd1265c03fb01ecaa47';
    public const TOPICS = ['generic-reference','another-other-the-other','articles','geographical-articles','collective-nouns',
        'countable-uncountable','irregular-plurals','no-none-neither-either','partitives','plural-nouns','quantifiers','zero-article'];

    private static function root(): string { return dirname(__DIR__,2); }
    public static function digest(array $value): string
    {
        return hash('sha256',json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
    }
    public static function specs(): array
    {
        return [
            'generic-reference'=>[M73GenericIrregularCollective::class,0,'B1'],
            'another-other-the-other'=>[M74OtherPronounsBasics::class,0,'B1'],
            'articles'=>[M71ArticlesQuantifiers::class,0,'A1'],
            'geographical-articles'=>[M72PartitivesNegativesGeography::class,2,'A2'],
            'collective-nouns'=>[M73GenericIrregularCollective::class,2,'B1'],
            'countable-uncountable'=>[M70IntensifiersNounBasics::class,2,'A1'],
            'irregular-plurals'=>[M73GenericIrregularCollective::class,1,'A2'],
            'no-none-neither-either'=>[M72PartitivesNegativesGeography::class,1,'B1'],
            'partitives'=>[M72PartitivesNegativesGeography::class,0,'A2'],
            'plural-nouns'=>[M70IntensifiersNounBasics::class,1,'A1'],
            'quantifiers'=>[M71ArticlesQuantifiers::class,2,'A1'],
            'zero-article'=>[M71ArticlesQuantifiers::class,1,'A1'],
        ];
    }

    /** Existing answer/slot indices stay fixed. Null retains the complete original frame. */
    public static function retainedPlans(): array
    {
        return [
            'generic-reference'=>[
                [null,'Потрібен означений артикль у назві групи людей, утвореній від прикметника.'],
                [null,'Використай означений артикль перед одниною, що називає винахід як тип.'],
                [null,'Загальна множина не має артикля; уведи непорожню навчальну позначку його відсутності.'],
                ['Digital dictionaries|do not replace|every printed dictionary.','Почни із цифрових словників як типу; збережи заперечення та значення «кожен друкований словник».'],
                ['The Italians|in our group|have chosen|the same topic.','Назви визначених учасників нашої групи й ту саму тему; потрібні два окремі означені артиклі.'],
            ],
            'another-other-the-other'=>[
                ['I need|some|others.','Після повідомлення про потребу назви інші предмети без повторення їхньої назви; не всю решту набору.'],
                ['The other chairs|are|by the window.','Почни з усіх стільців, що залишилися, і назви їхнє місце біля відомого вікна.'],
            ],
            'articles'=>[
                [null,'Уведи іменникову групу з неозначеним артиклем; наступне слово починається з приголосного звука.'],
                [null,'Уведи групу «чесна відповідь» з неозначеним артиклем; початковий приголосний не вимовляється.'],
                [null,'Уведи групу «європейська країна» з неозначеним артиклем; перший вимовлений звук приголосний.'],
                ['I|bought|an old bicycle.','Почни з мовця й минулої покупки; перед уперше названим старим велосипедом потрібен неозначений артикль.'],
                ['Can you|close|the door?','Склади пряме прохання до співрозмовника зачинити відомі обом двері.'],
            ],
            'geographical-articles'=>[
                ['I live|in Ukraine.','Почни з мовця; звичайна назва цієї сучасної країни не потребує артикля.'],
                ['The Dnipro|is|a beautiful river.','Назви річку з означеним артиклем; потім повідом, що це красива річка.'],
                ['I climbed|Mount Everest.','Почни з мовця й минулого сходження; повна назва вершини містить родове слово без артикля.'],
                ['She lives|in the Netherlands.','Почни з жінки й теперішнього місця проживання; у назві країни потрібен означений артикль.'],
                ['We visited|Lake Geneva.','Почни з нас і минулого відвідування; назва озера містить родове слово без артикля.'],
            ],
            'collective-nouns'=>[
                ['These families|have|separate tickets.','Кілька сімей потребують дієслова множини; збережи вказівне слово та окремі квитки.'],
                ['The team|are packing|their bags.','Дозволено узгоджену британську множину про учасників або однину про команду як ціле; не змішуй моделі.'],
            ],
            'countable-uncountable'=>[
                [null,'Для питання про кількість окремих книжок потрібен кількісний означник для злічуваної множини.'],
                [null,'Після заперечення назви кількість незлічуваного часу, не кількість окремих предметів.'],
                [null,'Для великої кількості людей потрібна форма зі злічуваною множиною; дотримуйся заданих двох варіантів.'],
                ['I need|some advice.','Почни з мовця та потреби; порада має незлічуване значення й невизначений кількісний означник.'],
                ['Two bottles|of water|are|on the table.','Почни з двох пляшок, назви їхній вміст і місце; дієслово узгоджується з пляшками.'],
            ],
            'irregular-plurals'=>[
                ['These children|are not|hungry.','Збережи вказівку на цих дітей, заперечення та ознаку голоду; можливе природне скорочення.'],
                ['Were|those women|here yesterday?','Побудуй загальне питання про тих жінок у минулому; час постав наприкінці.'],
            ],
            'no-none-neither-either'=>[
                [null,'Запереч результат для всіх п’ятьох із визначеної групи; потрібне слово перед прийменником частини цілого.'],
                [null,'Назви нульову кількість прямо перед іменником у множині.'],
                [null,'Запереч наявність обох людей із двох; у цій умові приймається і загальніша форма для нульової кількості.'],
                ['Neither key|works.','Назви жоден із двох ключів; після однини потрібна особова форма дієслова.'],
                ['You can use|either entrance.','Збережи можливість вибору будь-якого з двох входів; не повідомляй про вже зроблений вибір.'],
            ],
            'partitives'=>[
                [null,'Назви одну окрему відомість через одиницю «шматочок» і прийменник вмісту, не невизначену кількість.'],
                [null,'Назви одну чашку напою: неозначений артикль, посуд і прийменник вмісту.'],
                [null,'Назви одну скляну банку з широким отвором і кришкою, далі потрібен прийменник вмісту.'],
                ['There are|two bottles|of water|here.','Почни з конструкції наявності; назви дві пляшки, їхній вміст і місце наприкінці.'],
                ['We need|three cups|of tea.','Почни з нас і потреби; збережи три чашки та назву напою після прийменника.'],
            ],
            'plural-nouns'=>[
                [null,'Утвори неправильну множину назви дитини; уведи лише одне слово.'],
                [null,'У множині назви ножа кінцеві приголосні змінюються; уведи одне слово.'],
                [null,'Утвори неправильну множину назви миші-тварини, не комп’ютерного пристрою.'],
                [null,'Утвори множину назви героя з потрібним закінченням після кінцевого голосного.'],
                ['These boxes|are|on the table.','Почни з цих коробок; узгодь множину й назви місце на відомому столі.'],
                ['Do these books|have|pictures?','Побудуй пряме питання про ці книжки; основне дієслово після допоміжного має початкову форму.'],
            ],
            'quantifiers'=>[
                [null,'Назви велику кількість роботи однією з двох заданих багатослівних сполук.'],
                [null,'Для питання про кількість грошей потрібна форма для незлічуваного значення.'],
                [null,'Назви велику кількість людей; приймаються означник множини та обидві природні багатослівні сполуки.'],
                ['We have|a few chairs.','Підкресли наявність кількох стільців; у кількісній сполуці потрібен неозначений артикль.'],
                ['We do not|have|much time.','Почни з нас і збережи заперечення; для незлічуваної тривалості потрібен відповідний кількісний означник.'],
            ],
            'zero-article'=>[
                ['I go|to work|by bus.','Почни з мовця; звичайна діяльність і спосіб пересування тут не мають артиклів.'],
                [null,'Загальна абстрактна цінність не має артикля; уведи непорожню навчальну позначку його відсутності.'],
                [null,'У звичайній скороченій назві цієї держави потрібен означений артикль.'],
                ['Marta goes|to school|on Saturdays.','Почни з Марти; назви навчання як звичайну діяльність, а повторюваний день постав наприкінці.'],
                ['We had breakfast|at home.','Почни з нас і минулого прийому їжі; звичайний сніданок та стале місце не потребують артиклів.'],
            ],
        ];
    }

    /** New, independent native questions; these are not copies of the mixed catalogue. */
    public static function additions(): array
    {
        return [
            'generic-reference'=>[
                ['gap','{a1} young sometimes question old customs.','The','Назви молодих людей як групу за усталеною моделлю: означений артикль перед прикметником.'],
                ['gap','{a1} eagle has excellent eyesight.','The','Назви орла як вид за прямо заданою моделлю: означений артикль перед назвою в однині.'],
                ['translation','Жирафи мають довгі шиї. Це загальна характеристика виду; почни з назви тварин у множині без артикля.','Giraffes|have|long necks.','Назви тварин загалом, потім їхню ознаку; окрему відому групу не виділяй.'],
                ['translation','Телефон змінив спілкування. Назви винахід як тип за моделлю означеного артикля та однини.','The telephone|changed|communication.','Почни з винаходу як типу; далі назви минулу зміну спілкування.'],
            ],
            'another-other-the-other'=>[
                ['gap','These gloves are too small. May I try {a1} pair?','another','Потрібна інша пара замість цієї, не вся решта набору; назви один інший предмет.',['a different']],
                ['gap','There are two boxes. One is full; {a1} is empty.','the other','Назви другу з рівно двох коробок; іменник повторювати не потрібно.'],
                ['translation','Постав решту тарілок на полицю. Група тарілок і полиця вже відомі; використай слово для «інші» перед повтореною назвою предметів.','Put|the other plates|on the shelf.','Побудуй команду; означену решту тарілок постав після дії.'],
                ['translation','Я хотів би ще одну скибку хліба. Ідеться про додаткову скибку, а не заміну.','I would like|another slice|of bread.','Почни з мовця й ввічливого бажання; назви одну додаткову скибку.'],
            ],
            'articles'=>[
                ['gap','She adopted {a1} orange kitten.','an','Кошеня згадують уперше; потрібен неозначений артикль перед голосним звуком.'],
                ['gap','Please pass {a1} salt beside your plate.','the','Попроси конкретну сіль, визначену місцем біля тарілки співрозмовника; потрібен означений артикль.'],
                ['translation','На лавці лежить зелений шарф. Це перша згадка; почни з конструкції наявності.','There is|a green scarf|on the bench.','Назви один невизначений шарф і його місце на відомій лавці.'],
                ['translation','Будь ласка, вимкни лампу. Обоє знають, про яку саме лампу йдеться.','Please|turn off|the lamp.','Почни зі ввічливого слова; перед визначеною лампою потрібен означений артикль.',['Please switch off the lamp.'],['turn off'=>['switch off']]],
            ],
            'geographical-articles'=>[
                ['gap','They crossed {a1} Danube at sunset.','the','Річка Дунай у звичайній англійській назві потребує означеного артикля.'],
                ['gap','They arrived in {a1} United Kingdom yesterday.','the','У повній назві Сполученого Королівства потрібен означений артикль.'],
                ['translation','Ліна провела тиждень у Португалії. Почни з Ліни; використай звичайну коротку назву країни.','Lina spent|a week|in Portugal.','Назви минуле перебування, тривалість і країну без артикля.'],
                ['translation','Ми фотографували Піренеї в липні. Почни з нас, місяць постав наприкінці.','We photographed|the Pyrenees|in July.','Перед назвою гірської системи потрібен означений артикль; назва місяця його не має.',
                    ['We took photos of the Pyrenees in July.','We took pictures of the Pyrenees in July.'],['photographed'=>['took photos of','took pictures of']]],
            ],
            'collective-nouns'=>[
                ['gap','The committee {a1} meeting as one body today.','is','Використай однину допоміжного дієслова: за умовою колектив розглядають як одну установу.'],
                ['gap','In this British report, the staff {a1} checking their individual schedules.','are','Використай британське узгодження у множині: кожен працівник перевіряє власний розклад.'],
                ['translation','Журі ухвалило своє рішення. У цій вправі подай журі як одну установу та використай теперішній доконаний час.','The jury|has reached|its decision.','Узгодь однину установи з допоміжним дієсловом і присвійним словом однини.'],
                ['translation','Хористи займають свої місця. Назви відомий хор збірним іменником; використай британське узгодження з увагою до окремих учасників.','The choir|are taking|their seats.','Збережи теперішній тривалий процес і узгоджену множину про учасників хору.'],
            ],
            'countable-uncountable'=>[
                ['gap','There is very {a1} salt left.','little','Залишилося зовсім мало солі; потрібен означник малої кількості незлічуваної речовини.'],
                ['gap','I need two {a1} of paper.','sheets','Назви дві окремі одиниці паперу: аркуші або шматки; потрібен іменник у множині.',['pieces']],
                ['translation','Це борошно дуже сухе. Почни зі вказівки на відоме борошно.','This flour|is|very dry.','Речовина має незлічуване значення; зв’язка й вказівне слово стоять в однині.'],
                ['translation','У кошику чотири лимони. Почни з конструкції наявності; кошик уже відомий.','There are|four lemons|in the basket.','Після конструкції наявності назви кількість окремих плодів, потім місце.'],
            ],
            'irregular-plurals'=>[
                ['gap','The farmer feeds the {a1} at dawn.','geese','Назви гусей у множині; йдеться про птахів, яких годують на світанку.'],
                ['gap','Please show the dentist your {a1}.','teeth','Покажи стоматологу свої зуби; уведи неправильну множину назви частини тіла.'],
                ['translation','П’ять мишей утекли з клітки. Почни з кількості тварин; клітка вже відома.','Five mice|escaped|from the cage.','Збережи неправильну множину назви тварини й минулу подію.'],
                ['translation','Ті чоловіки носять важкі сумки. Почни зі вказівки «ті»; використай простий теперішній час.','Those men|carry|heavy bags.','Узгодь вказівне слово та дієслово з неправильною множиною назви людей.'],
            ],
            'no-none-neither-either'=>[
                ['gap','We invited four friends, but {a1} of them came.','none','Із чотирьох запрошених не прийшов ніхто; потрібна форма нульової кількості перед прийменником.'],
                ['gap','I saw {a1} footprints in the fresh snow.','no','Я не побачив жодного сліду; потрібен заперечний означник безпосередньо перед множиною.'],
                ['translation','Жодна з двох відповідей не правильна. Почни з означника, що заперечує обидва варіанти.','Neither answer|is|correct.','Збережи рівно два варіанти; перед одниною іменника потрібен означник без прийменника.',['Neither answer is right.'],['correct.'=>['right.']]],
                ['translation','Тут немає вільних кімнат. Почни з конструкції наявності й використай заперечний означник перед множиною.','There are|no vacant rooms|here.','Назви нульову кількість вільних кімнат; місце постав наприкінці.',['There are no available rooms here.'],['vacant'=>['available']]],
            ],
            'partitives'=>[
                ['gap','She bought a {a1} of soap.','bar','Вона купила один брусок мила; уведи лише назву цієї окремої одиниці.'],
                ['gap','He added a {a1} of lemon to the tea.','slice','Він додав до чаю одну тонку круглу скибку лимона; уведи назву відрізаної скибки.'],
                ['translation','Принеси дві миски супу. Почни з дії й прямо назви миски як окремі порції.','Bring|two bowls|of soup.','Після кількості та посуду потрібен прийменник, який пов’язує його з вмістом.'],
                ['translation','Я загорнув шматок сиру. Почни з мовця й минулої дії; прямо назви один шматок.','I wrapped|a piece|of cheese.','Не рахуй сир як речовину безпосередньо; назви окремий шматок із неозначеним артиклем.'],
            ],
            'plural-nouns'=>[
                ['gap','Three {a1} are sleeping under the tree.','foxes','Три лисиці сплять під деревом; утвори множину назви тварини з потрібним розширеним закінченням.'],
                ['gap','The room has two new {a1}.','shelves','У кімнаті дві нові полиці; у множині назви предмета змінюється кінцевий приголосний.'],
                ['translation','Медсестрам потрібні чисті рушники. Ідеться про вже відому групу; почни з її назви з означеним артиклем.','The nurses|need|clean towels.','Обидва злічувані іменники мають звичайну множину; дієслово узгоджується з групою.'],
                ['translation','Ті тарілки швидко висихають. Почни зі вказівки «ті» й використай простий теперішній час.','Those dishes|dry|quickly.','Потрібна множина назви посуду з розширеним закінченням; ознаку дії постав наприкінці.'],
            ],
            'quantifiers'=>[
                ['gap','We have {a1} oranges for a small bowl.','a few','У нас є кілька апельсинів; підкресли їхню наявність двослівним означником із неозначеним артиклем.'],
                ['gap','There is very {a1} fuel in the tank.','little','У баку зовсім мало пального; потрібен означник малої кількості незлічуваної речовини.'],
                ['translation','У Сари багато марок. Почни з Сари й використай багатослівну сполуку з неозначеним артиклем для великої кількості.','Sara has|a lot of|stamps.','Після власниці назви наявність і велику кількість окремих предметів.',['Sara has lots of stamps.'],['a lot of'=>['lots of']]],
                ['translation','На підносі лише кілька горіхів. Почни з конструкції наявності; збережи слово «лише» та додатну наявність кількох.','There are|only a few|nuts|on the tray.','Для злічуваної множини потрібна двослівна сполука з неозначеним артиклем; місце постав наприкінці.'],
            ],
            'zero-article'=>[
                ['gap','Marta studies {a1} after dinner.','history','Марта вивчає історію після вечері; назви навчальний предмет без артикля.'],
                ['gap','They travel by {a1} every summer.','train','Вони щоліта подорожують поїздом; назви спосіб пересування без артикля.',['rail']],
                ['translation','Назар грає в баскетбол після школи. Почни з Назара; йдеться про звичайне заняття спортом і навчання.','Nazar plays|basketball|after school.','Назви спорту та звичайного навчання не потребують артиклів.'],
                ['translation','Вони повернулися додому на таксі. Почни з них і використай простий минулий час.','They returned|home|by taxi.','Назви напрям додому та спосіб пересування без артиклів.',['They returned home by cab.'],['by taxi.'=>['by cab.']]],
            ],
        ];
    }

    public static function editingHints(): array
    {
        return [
            'generic-reference'=>[1=>'Прибери артикль перед загальною множиною інструкцій; можливість їхньої користі залиш незмінною.',
                2=>'Для заданого загального правила назви українців множиною без артикля; збережи дозвіл подати заявку на визначену програму.'],
            'another-other-the-other'=>[],
            'articles'=>[1=>'За умовою йдеться про любов до музики загалом: прибери артикль, не змінюючи вподобання.',
                2=>'Прибери означений артикль перед звичайною короткою назвою Франції; особу й місце проживання збережи.'],
            'geographical-articles'=>[1=>'Перед назвою річки Темзи додай означений артикль; характеристику довжини не змінюй.',
                2=>'Прибери артикль перед повною назвою вершини з родовим словом; збережи минуле відвідування.'],
            'collective-nouns'=>[],
            'countable-uncountable'=>[1=>'Інформацію назви незлічуваним словом без множини; велику кількість передай доречним означником або багатослівною сполукою.',
                2=>'Заміни артикль окремої порції невизначеним кількісним означником речовини; початкову питальну конструкцію збережи.'],
            'irregular-plurals'=>[],
            'no-none-neither-either'=>[1=>'Початкове слово вже заперечує обох людей; прибери зайве заперечення біля дієслова й збережи минулий час.',
                2=>'Безпосередньо перед незлічуваними грошима потрібен заперечний означник, не самостійний займенник.'],
            'partitives'=>[1=>'Між назвою чашки й назвою напою додай прийменник вмісту; решту прохання збережи.',
                2=>'Після кількості двох потрібна правильна множина назви буханця зі зміною кінцевого приголосного.'],
            'plural-nouns'=>[1=>'Після трьох потрібна множина назви коробки з розширеним закінченням; решту речення не змінюй.',
                2=>'Інформація має незлічуване значення: прибери помилкове закінчення множини й збережи решту повідомлення.',
                3=>'Одні ножиці назви однією парою та іменником у множині; час покупки залиш наприкінці.'],
            'quantifiers'=>[1=>'Для незлічуваної тривалості заміни означник окремих предметів відповідним кількісним словом; заперечення збережи.',
                2=>'Для людей у злічуваній множині потрібен відповідний означник або природна багатослівна сполука великої кількості.'],
            'zero-article'=>[1=>'Прибери артикль перед звичайною сучасною назвою України; місце проживання та особу збережи.',
                2=>'За заданим загальним твердженням назви собак множиною без артикля; не перетворюй їх на відому групу.'],
        ];
    }

    public static function shuffled(array $groups,string $seed): array
    {
        $rows=[];foreach($groups as $i=>$text){$rows[]=['text'=>$text,'key'=>hash('sha256',$seed.'|'.$i.'|'.$text)];}
        usort($rows,static fn($a,$b)=>strcmp($a['key'],$b['key']));$result=array_column($rows,'text');
        if($result===$groups){$result[]=array_shift($result);}return $result;
    }
    private static function uk(string $text): void
    {
        if(trim($text)===''||preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$text)!==1
            ||preg_match('/[A-Za-z]|<[^>]+>|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu',$text)===1){throw new RuntimeException('Native hint/condition must be plain Ukrainian.');}
    }
    private static function groups(array $groups,string $answer): void
    {
        if(count($groups)<2||implode(' ',$groups)!==$answer){throw new RuntimeException('Native ordered groups change retained answer.');}
        foreach($groups as $group){if(trim($group)!==$group||$group===''||str_contains($group,'/')
            ||count(preg_split('/\s+/u',$group))>3){throw new RuntimeException('Native group needs one to three words.');}}
    }
    private static function labels(array $groups,array $aliases): array
    {
        $out=[];
        foreach($groups as $group){
            $values=$aliases[$group]??[];
            if($values===[]){foreach($aliases as $word=>$synonyms){$p='/(?<![A-Za-z])'.preg_quote($word,'/').'(?![A-Za-z])/u';
                if(preg_match_all($p,$group)!==1){continue;}foreach($synonyms as $synonym){$values[]=preg_replace($p,$synonym,$group,1);}}}
            $values=array_values(array_unique(array_filter($values,static fn($v)=>is_string($v)&&$v!==$group)));
            if($values!==[]){$out[$group]=$group.' ('.implode(' / ',$values).')';}
        }return $out;
    }

    /** Four independently reviewed lexical alternatives in unchanged lower widgets. */
    private static function widgetBindings(): array
    {
        $plans=[32115=>[M72BuilderSelectionPresentation::class,'piece','item'],
            32122=>[M72BuilderSelectionPresentation::class,'made','prepared'],
            32123=>[M72BuilderSelectionPresentation::class,'can','tin'],
            32045=>[M71BuilderSelectionPresentation::class,'photos','photographs']];$out=[];
        foreach($plans as $id=>[$presenter,$word,$alias]){
            $audit=$presenter::loadAudit();$matches=[];
            foreach($audit['targets'] as $i=>$target){foreach($target['first_five'] as $sample){if($sample['id']===$id){$matches[]=[$i,$target,$sample];}}}
            if(count($matches)!==1){throw new RuntimeException('Exact lexical widget binding missing.');}
            [$i,$target,$sample]=$matches[0];$bank=$target['bank_seeders'][0];
            $definition='database/seeders/'.str_replace('\\','/',substr($bank,strlen('Database\\Seeders\\'))).'/definition.json';
            $document=json_decode(file_get_contents(self::root().'/'.$definition),true,flags:JSON_THROW_ON_ERROR);
            $rows=array_values(array_filter($document['questions'],static fn($q)=>($q['uuid']??null)===$sample['uuid']));
            if(count($rows)!==1||$rows[0]['question']!==$sample['displayed_condition_uk']||($document['seeder']['class']??null)!==$bank){throw new RuntimeException('Lexical widget canonical source differs.');}
            $tokens=$sample['correct_tokens'];$where=array_keys($tokens,$word,true);
            if(count($where)!==1){throw new RuntimeException('Lexical widget must replace exactly one token.');}
            $alternate=$tokens;$alternate[$where[0]]=$alias;$addedOptions=[$alias];$tokenLabels=[$word=>$word.' ('.$alias.')'];
            if($id===32115){
                if(($tokens[$where[0]-1]??null)!=='a'){throw new RuntimeException('Furniture alias article frame differs.');}
                $alternate[$where[0]-1]='an';$addedOptions[]='an';$tokenLabels['a']='a (an)';
            }
            $out[]=['id'=>$id,'uuid'=>$sample['uuid'],'seeder'=>$bank,'type'=>'4','level'=>$rows[0]['level'],
                'block_uuid'=>$audit['practice_bindings'][$i]['uuid'],'question'=>$sample['visible_condition_uk'],
                'source_question'=>$sample['displayed_condition_uk'],'correct_tokens'=>$tokens,
                'original_ordered_instances'=>$sample['original_ordered_instances'],
                'accepted_sentences'=>[implode(' ',$tokens),implode(' ',$alternate)],'added_options'=>$addedOptions,
                'token_labels'=>$tokenLabels,'definition'=>$definition,'definition_sha256'=>hash_file('sha256',self::root().'/'.$definition),
                'audit'=>$presenter::AUDIT,'audit_sha256'=>$presenter::AUDIT_SHA];
        }return $out;
    }

    public static function compile(): array
    {
        $document=['schema_version'=>1,'package'=>'nouns-articles-quantity-native-practice-supplement','status'=>'frozen','targets'=>[]];$releases=[];
        foreach(self::specs() as $topic=>[$release,$i,$focus]){
            if(!isset($releases[$release])){$releases[$release]=$release::load(self::root());$release::validate(...$releases[$release]);}
            [,$patch]=$releases[$release];$target=$patch['targets'][$i];$slot=$release::PRACTICE_SOURCE_INDICES[$i];$stored=$target['after']['page']['blocks'][$slot];
            $binding=['seeder'=>$target['identity'],'page_id'=>$release::PAGE_IDS[$i],'page_category_id'=>16,
                'uuid'=>M26DetailPackage::uuid($target['identity'],$stored,$slot+1),'locale'=>'uk','type'=>'practice-set',
                'column'=>'left','sort_order'=>$slot+1,'level'=>$release::LEVELS[$i],'heading'=>null,'css_class'=>null];
            $block=(object)($binding+['body'=>$stored['body']]);$raw=json_decode($stored['body'],true,flags:JSON_THROW_ON_ERROR);
            $presentation=$release::presentation($block,$raw);
            if(!is_array($presentation['data']??null)){throw new RuntimeException('Retained native presentation missing.');}
            $before=$presentation['data'];$after=$before;$updates=[];$tokenLabels=[];
            if(count($before['inputs'])!==count(self::retainedPlans()[$topic])){throw new RuntimeException('Retained native input slots differ.');}
            foreach(self::retainedPlans()[$topic] as $index=>[$ordered,$hint]){
                self::uk($hint);$old=$before['inputs'][$index];$new=$old;$new['verb_hint']=$hint;
                if($ordered===null){$new['practice_kind']='gap';$updates[]=['index'=>$index,'kind'=>'gap','hint_uk'=>$hint];}
                else{$groups=explode('|',$ordered);self::groups($groups,$old['answer']);$shuffled=self::shuffled($groups,$topic.':retained:'.$index);
                    $new['before']=implode(' / ',$shuffled);$new['practice_kind']='order';
                    $updates[]=['index'=>$index,'kind'=>'order','ordered_groups'=>$groups,'shuffled_groups'=>$shuffled,'hint_uk'=>$hint];}
                $new['after']=($new['after']??'').' Підказка: '.$hint;$after['inputs'][$index]=$new;
            }
            $editing=[];$editingPlans=self::editingHints()[$topic];
            foreach($after['rephrase']??[] as $index=>$item){if(!is_string($item['answer']??null)){continue;}
                if(!isset($editingPlans[$index])){throw new RuntimeException('Exact contextual editing hint missing.');}
                $hint=$editingPlans[$index];self::uk($hint);
                $after['rephrase'][$index]['verb_hint']=$hint;$after['rephrase'][$index]['original'].=' Підказка: '.$hint;$editing[]=$index;}
            if(array_keys($editingPlans)!==$editing){throw new RuntimeException('Unexpected contextual editing hint slot.');}
            $added=[];
            foreach(self::additions()[$topic] as $n=>$row){
                [$kind,$question,$answer,$hint]=$row;self::uk($hint);$id='naqq-native-'.$topic.'-'.($kind==='gap'?'gap':'translate').'-'.($n%2+1);
                if($kind==='translation'){self::uk($question);$groups=explode('|',$answer);$answer=implode(' ',$groups);self::groups($groups,$answer);
                    $shuffled=self::shuffled($groups,$id);$visible=implode(' / ',$shuffled);$afterText=$question.' Підказка: '.$hint;
                }else{if(substr_count($question,'{a1}')!==1||preg_match('/\{a[2-9]/',$question)===1){throw new RuntimeException('Native gap needs one phrase slot.');}
                    $visible=str_replace('{a1}','____',$question);$afterText=$hint;$groups=[];$shuffled=[];}
                $accepted=array_values(array_unique(array_merge([$answer],$row[4]??[])));$index=count($after['inputs']);
                if($kind==='gap'&&count($accepted)>1){$afterText.=' Допустимі варіанти: ('.implode(' / ',$accepted).').';}
                $after['inputs'][]=['id'=>$id,'practice_kind'=>$kind,'before'=>$visible,'after'=>$afterText,'verb_hint'=>$hint,'answer'=>$answer,'accepted'=>$accepted,
                    'feedback'=>array_fill_keys(array_map('mb_strtolower',$accepted),'Правильно. '.$hint)];
                $labels=self::labels($groups,$row[5]??[]);if($labels!==[]){$tokenLabels[$index]=$labels;}
                $added[]=['id'=>$id,'index'=>$index,'kind'=>$kind,'focus_level'=>$focus,'condition'=>$question,'answer'=>$answer,
                    'accepted'=>$accepted,'hint_uk'=>$hint,'ordered_groups'=>$groups,'shuffled_groups'=>$shuffled];
            }
            $oldCount=count($before['inputs']);
            $after['input_title']='Вправа 3. Заповни пропуски та склади речення';
            $after['input_intro']='Спочатку виконай початкові '.$oldCount.' '.($oldCount===2?'завдання':'завдань').' за їхніми умовами. Далі заповни два пропуски й переклади два українські речення. Повні речення вводь вручну або складай із перемішаних груп.';
            $after['nouns_articles_quantity_practice_supplement']=['revision'=>'v1','topic'=>$topic];
            $document['targets'][]=['topic'=>$topic,'definition'=>$target['path'],'definition_sha256'=>hash_file('sha256',self::root().'/'.$target['path']),
                'release'=>$release::SOURCE,'release_sha256'=>$release::SOURCE_SHA,'focus_level'=>$focus,
                'binding'=>$binding+['body_sha256'=>hash('sha256',$stored['body'])],
                'before_data'=>$before,'before_data_sha256'=>self::digest($before),'after_data'=>$after,'after_data_sha256'=>self::digest($after),
                'input_updates'=>$updates,'editing_hint_indices'=>$editing,'added'=>$added,'input_token_labels'=>$tokenLabels];
        }$document['widgets']=self::widgetBindings();return $document;
    }
    public static function load(): array
    {
        $path=self::root().'/'.self::SOURCE;
        if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path))){throw new RuntimeException('Native supplement bytes differ.');}
        static $verified=null;
        if($verified===null){$verified=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);self::validate($verified);}
        foreach($verified['targets'] as $target){foreach(['definition','release'] as $kind){$file=self::root().'/'.$target[$kind];
            if(!is_file($file)||is_link($file)||!hash_equals($target[$kind.'_sha256'],hash_file('sha256',$file))){throw new RuntimeException('Native source pin differs.');}}}
        foreach($verified['widgets'] as $widget){foreach(['definition','audit'] as $kind){$file=self::root().'/'.$widget[$kind];
            if(!is_file($file)||is_link($file)||!hash_equals($widget[$kind.'_sha256'],hash_file('sha256',$file))){throw new RuntimeException('Widget lexical source pin differs.');}}}
        return $verified;
    }
    public static function validate(array $document): void
    {
        if($document!==self::compile()){throw new RuntimeException('Native finite teaching projection differs.');}
    }
    private static function entry(object $block,array $data,bool $projected): ?array
    {
        if(app()->getLocale()!=='uk'||($block->locale??null)!=='uk'||($block->type??null)!=='practice-set'
            ||!str_starts_with($block->seeder??'','Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\')){return null;}
        try{foreach(self::load()['targets'] as $target){foreach($target['binding'] as $field=>$value){if($field==='body_sha256'){continue;}
            if(($block->$field??null)!==$value){continue 2;}}
            if(is_string($block->body??null)&&hash_equals($target['binding']['body_sha256'],hash('sha256',$block->body))
                &&$data===$target[$projected?'after_data':'before_data']){return $target;}
        }}catch(Throwable){/* Changed or foreign data retains its whole original native practice. */}return null;
    }
    public static function project(object $block,array $data,bool $canonicalCaller): array
    {
        return $canonicalCaller?(self::entry($block,$data,false)['after_data']??$data):$data;
    }
    public static function isProjected(object $block,array $data): bool { return self::entry($block,$data,true)!==null; }
    public static function originalDataForDisplay(object $block,array $data): array { return self::entry($block,$data,true)['before_data']??$data; }
    public static function inputTokenLabels(object $block,array $data): array { return self::entry($block,$data,true)['input_token_labels']??[]; }

    /** Source-bound alias overlay; canonical markers, storage key and progress remain unchanged. */
    public static function projectWidget(object $question,?string $blockUuid,array $payload,bool $canonicalCaller=false): array
    {
        if(!$canonicalCaller||app()->getLocale()!=='uk'||($payload['type']??null)!=='4'||(string)($question->type??'')!=='4'){return $payload;}
        try{
            foreach(self::load()['widgets'] as $binding){
                foreach(['id','uuid','seeder','type','level','question'] as $field){
                    $value=$question->$field??null;if($field==='type'){$value=(string)$value;}
                    if($value!==$binding[$field]){continue 2;}
                }
                if($blockUuid!==$binding['block_uuid']||($payload['id']??null)!==$binding['id']||($payload['level']??null)!==$binding['level']
                    ||($payload['question']??null)!==$binding['question']||($payload['correct_tokens']??null)!==$binding['correct_tokens']
                    ||($payload['accepted_sentences']??[])!==[]||!is_array($payload['options']??null)
                    ||array_slice($payload['options'],0,count($binding['correct_tokens']))!==$binding['correct_tokens']){return $payload;}
                if(method_exists($question,'relationLoaded')&&!$question->relationLoaded('answers')){return $payload;}
                $loaded=collect($question->answers??[])->sortBy('marker',SORT_NATURAL)->values();
                if($loaded->count()!==count($binding['original_ordered_instances'])||count($payload['answers']??[])!==$loaded->count()){return $payload;}
                foreach($binding['original_ordered_instances'] as $i=>$answer){$actual=$loaded[$i];$shown=$payload['answers'][$i];
                    if(($actual->id??null)!==$answer['id']||($actual->marker??null)!==$answer['marker']||($actual->option_id??null)!==$answer['option_id']
                        ||($actual->option->option??null)!==$answer['option']||($shown['marker']??null)!==$answer['marker']
                        ||($shown['correct']??null)!==$answer['option']){return $payload;}}
                $payload['accepted_sentences']=$binding['accepted_sentences'];
                $payload['options']=array_values(array_unique(array_merge($payload['options'],$binding['added_options'])));
                $payload['derived_compose_options']=array_values(array_unique(array_merge($payload['derived_compose_options']??[],$binding['added_options'])));
                $payload['token_labels']=array_replace($payload['token_labels']??[],$binding['token_labels']);
                $payload['naq_widget_alias_revision']=self::digest($binding);
                return $payload;
            }
        }catch(Throwable){/* Unchanged or incomplete source keeps its complete old payload. */}return $payload;
    }
}
