<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Finite UK teaching supplement; native components, stored releases and progress remain intact. */
final class QuestionsNegationsPracticeSupplement
{
    public const SOURCE = 'docs/content/questions-negations-practice-supplement.v1.json';
    public const SOURCE_SHA = '6d32985849fa54b8f7a240b265214f455104ef7c9e82bf06c23bebe4f911d0b2';
    public const WIDGET_BINDINGS = 'docs/content/questions-negations-widget-bindings.v1.json';
    public const WIDGET_BINDINGS_SHA = 'bf3f3146bb5cbfc73545702e04e8e458b7106caf0e98dda6aa26de012296e259';
    public const TOPICS = ['question-forms','yes-no','wh','answers','alternative','tags','negative-questions','subject','embedded','indirect','negative-forms','double-negatives'];

    private static function root(): string { return dirname(__DIR__, 2); }
    public static function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
    public static function specs(): array
    {
        return [
            'question-forms'=>[M78PhrasalQuestionBasics::class,2,M78BuilderSelectionPresentation::class],
            'yes-no'=>[M78PhrasalQuestionBasics::class,3,M78BuilderSelectionPresentation::class],
            'wh'=>[M78PhrasalQuestionBasics::class,4,M78BuilderSelectionPresentation::class],
            'answers'=>[M78PhrasalQuestionBasics::class,5,M78BuilderSelectionPresentation::class],
            'alternative'=>[M79QuestionTypesCompletion::class,0,M79BuilderSelectionPresentation::class],
            'tags'=>[M79QuestionTypesCompletion::class,1,M79BuilderSelectionPresentation::class],
            'negative-questions'=>[M79QuestionTypesCompletion::class,2,M79BuilderSelectionPresentation::class],
            'subject'=>[M79QuestionTypesCompletion::class,3,M79BuilderSelectionPresentation::class],
            'embedded'=>[M79QuestionTypesCompletion::class,4,M79BuilderSelectionPresentation::class],
            'indirect'=>[M79QuestionTypesCompletion::class,5,M79BuilderSelectionPresentation::class],
            'negative-forms'=>[M80NegativesTransformations::class,0,M80BuilderSelectionPresentation::class],
            'double-negatives'=>[M80NegativesTransformations::class,1,M80BuilderSelectionPresentation::class],
        ];
    }

    /** Reviewed grouping, visible conditions and contextual hints for every retained input. */
    public static function retained(): array
    {
        return [
            'question-forms'=>[
                ['Does she|speak French?','Вона розмовляє французькою?','Запитай про звичне вміння однієї жінки; після допоміжного потрібна початкова форма дії.'],
                ['Are they|students?','Вони студенти?','Запитай про теперішній статус кількох людей; додаткове допоміжне для дії не потрібне.'],
                ['Can he|drive?','Він уміє водити автомобіль?','Постав модальне дієслово здатності перед займенником; далі потрібна початкова форма дії.'],
                ['Where did you|go?','Куди ти їздив?','Запитай співрозмовника про місце минулої поїздки; конкретне місце у питанні не називай.'],
            ],
            'yes-no'=>[
                ['Does she|like tea?','Їй подобається чай?','Запитай про теперішнє вподобання жінки; після допоміжного не додавай закінчення до дії.'],
                ['Are they|happy?','Вони щасливі?','Запитай про стан кількох людей, не додаючи допоміжного для звичайної дії.'],
                ['Can he|dance?','Він уміє танцювати?','Запитай про навичку чоловіка модальною формою здатності, не про дозвіл.'],
                ['Do you|have a car?','У тебе є машина?','Запитай одного співрозмовника про теперішнє володіння; обидві прийняті моделі дозволені.'],
            ],
            'wh'=>[
                ['Where do you|live?','Де ти живеш?','Запитай співрозмовника про звичне місце проживання; не змінюй звертання на мовця.'],
                ['How old|is she?','Скільки їй років?','Почни з усієї питальної групи про вік; збережи жіночу особу та теперішній стан.'],
                ['Where did he|go?','Куди він пішов?','Запитай про напрямок минулого руху чоловіка; після допоміжного потрібна початкова форма дії.'],
                ['When did they|arrive?','Коли вони прибули?','Запитай про час минулого прибуття групи, а не про місце.'],
            ],
            'answers'=>[
                [null,'Оля розмовляє англійською й відповідає про себе. Доповни стверджувальну відповідь.','Після вже показаного ствердження введи лише займенник мовця й потрібне допоміжне.'],
                [null,'Вона не вчителька. Доповни заперечну відповідь про неї.','Уведи займенник для названої жінки та заперечну форму стану; початок відповіді не повторюй.'],
                [null,'Вони можуть допомогти. Доповни стверджувальну відповідь.','Повтори займенник для групи й модальне дієслово можливості; назву дії не повторюй.'],
                [null,'Оля цього не бачила й відповідає про себе. Доповни заперечну відповідь.','Потрібні займенник мовця й заперечне допоміжне минулого часу; початок відповіді не повторюй.'],
                ['Yes, I am|ready.','На запитання «Ти готова?» Оля відповідає: «Так, я готова». Склади всю відповідь.','Почни зі ствердження й збережи ознаку готовності; перед ознакою можливе звичайне скорочення.'],
                ['No, he|does not|work here.','Оля відповідає про брата: «Ні, він тут не працює». Склади всю відповідь.','Почни із заперечної відповіді; робота стосується чоловіка в теперішньому часі.'],
            ],
            'alternative'=>[
                ['Does she|like|cats or dogs?','Їй подобаються коти чи собаки? Котів назви першими.','Запитай про вподобання жінки й збережи обидві альтернативи в заданому порядку.'],
                ['Are you|from Spain|or Italy?','Ти з Іспанії чи Італії? Іспанію назви першою.','Запитай співрозмовника про походження; збережи обидві країни та повний питальний порядок.'],
                ['Will they come|today or tomorrow?','Вони прийдуть сьогодні чи завтра? Сьогодні назви першим.','Збережи майбутній прихід групи та дві обставини часу зі словом вибору.'],
                ['Is it|big or small?','Це велике чи маленьке? Велике назви першим.','Запитай про розмір одного предмета; постав форму стану перед займенником.'],
            ],
            'tags'=>[
                [null,'Доповни стандартний запит підтвердження про втому жінки.','Головна частина стверджувальна: уведи лише заперечне допоміжне та займенник для цієї жінки.'],
                [null,'Доповни стандартний запит підтвердження про вподобання співрозмовника.','Після заперечної головної частини потрібні стверджувальне допоміжне та займенник співрозмовника.'],
                [null,'Доповни стандартний запит підтвердження про вміння водити.','Збережи модальне дієслово головної частини; потрібні його заперечення та займенник чоловіка.'],
                [null,'Доповни стандартний запит підтвердження про запізнення групи.','Після заперечної майбутньої частини потрібні стверджувальне допоміжне та займенник групи.'],
                ["Marta works here,|doesn't she?",'Марта працює тут, правда? Склади все речення з імені.','Після позитивного повідомлення про звичну роботу додай стандартне заперечне підтвердження.'],
                ["There isn't|a bus,|is there?",'Автобуса немає, так? Склади все речення.','Збережи конструкцію існування в обох частинах; після заперечення потрібне стверджувальне підтвердження.'],
            ],
            'negative-questions'=>[
                ["Don't you|know?",'Хіба ти не знаєш?','Побудуй заперечне питання про теперішнє знання співрозмовника; повна форма також дозволена.'],
                ["Isn't she|coming?",'Хіба вона не прийде? Використай форму поточного плану.','Запитай про запланований прихід жінки заперечною тривалою формою; збережи питальний порядок.'],
                ["Can't they|help?",'Хіба вони не можуть допомогти?','Постав заперечне модальне перед групою й збережи значення можливості допомогти.'],
                ["Didn't you|see it?",'Хіба ти цього не бачив?','Збережи заперечне питання про минулу подію та займенник предмета; після допоміжного дія початкова.'],
            ],
            'subject'=>[
                ['How many|children|live here?','Скільки дітей тут живе?','Уся група кількості є підметом; у нейтральному питанні про звичну ситуацію не додавай питальне допоміжне.'],
                ['Who|is waiting|outside?','Хто чекає надворі просто зараз?','Невідома людина є підметом; допоміжне тривалої форми збережи.'],
            ],
            'embedded'=>[
                ["I don't know|what Lina|does.",'Я не знаю, що робить Ліна.','Почни з незнання мовця; у внутрішній частині залиш підмет перед особовою формою дії.'],
                ['Do you know|if the door|is locked?','Ти знаєш, чи двері замкнені?','Запитай співрозмовника про знання стану одних дверей; обидва сполучники значення «чи» приймаються.'],
            ],
            'indirect'=>[
                ['Could you|tell me|where you work?','Можете сказати, де ви працюєте? Збережи подану ввічливу рамку.','Після прохання сказати інформацію використай порядок твердження про роботу співрозмовника.'],
                ['I was wondering|if the room|is available|tonight.','Хотів би дізнатися, чи вільна кімната сьогодні ввечері. Збережи подану рамку.','М’яка минула рамка стосується теперішньої доступності для бронювання, не безкоштовності.'],
            ],
            'negative-forms'=>[
                ['He promised|not to|be late.','Він пообіцяв не запізнюватися. Постав заперечення перед часткою інфінітива.','Заперечуй саме запізнення всередині обіцянки, не факт обіцянки.'],
                ['Marko|did not|call us|yesterday.','Учора Марко не зателефонував нам. Почни з імені.','Збережи заперечення й минулий час; звичайні синоніми телефонного дзвінка та скорочення приймаються.'],
            ],
            'double-negatives'=>[
                ['We did not|meet anyone|yesterday.','Ми вчора нікого не зустріли. Почни з «ми» й заперечного допоміжного.','У нейтральній стандартній формі одного заперечення достатньо; обидва природні займенники для будь-якої людини приймаються.'],
                ['Nobody|called us|after the meeting.','Ніхто не зателефонував нам після зустрічі. Почни із заперечного займенника.','Не додавай друге заперечення; обидва звичайні займенники й дієслова телефонного дзвінка приймаються.'],
            ],
        ];
    }

    /** Independent new native questions, not copies from mixed or lower-widget banks. */
    public static function additions(): array
    {
        return [
            'question-forms'=>[
                ['gap','____ the lift stop on this floor?','Does','Запитай про звичну зупинку одного ліфта; підмет і дія вже показані.'],
                ['gap','Why ____ the windows open?','are','Запитай про причину теперішнього стану кількох вікон; потрібна особова форма стану.'],
                ['translation','Чи може Олег полагодити цей стілець?','Can Oleh|repair this chair?','Запитай про здатність чоловіка полагодити вже вказаний стілець.', ['Can Oleh fix this chair?'], ['repair'=>['fix']]],
                ['translation','Коли починається ваша обідня перерва?','When does|your lunch break|start?','Запитай про час звичного початку однієї перерви, не про її тривалість.', ['When does your lunch break begin?'], ['start?'=>['begin?']]],
            ],
            'yes-no'=>[
                ['gap','____ these towels dry?','Are','Запитай про теперішній стан кількох уже показаних рушників.'],
                ['gap','____ your cousin paint this picture yesterday?','Did','Запитай про завершену вчорашню дію; підмет і початкова форма дії вже показані.'],
                ['translation','Твоя сестра пече хліб щонеділі?','Does your sister|bake bread|every Sunday?','Побудуй загальне питання про регулярну дію сестри; назви кожну неділю.'],
                ['translation','Ці рукавички твої?','Are these gloves|yours?','Запитай про належність кількох близьких рукавичок; назву предметів не повторюй після власника.'],
            ],
            'wh'=>[
                ['gap','How ____ is this rope?','long','Потрібен запит про довжину вже показаної мотузки, не її ціну чи вік.'],
                ['gap','Why ____ the dog barking now?','is','Запитай про причину поточної дії однієї тварини; питальне слово та дія вже показані.'],
                ['translation','Де ви зберігаєте запасні ключі?','Where do you|keep|the spare keys?','Запитай співрозмовників про звичне місце зберігання вже відомих запасних ключів.', ['Where do you store the spare keys?'], ['keep'=>['store']]],
                ['translation','Чому Анна покинула зустріч рано?','Why did Anna|leave the meeting|early?','Запитай про причину минулого раннього відходу; після допоміжного потрібна початкова форма.'],
            ],
            'answers'=>[
                ['gap','Can you carry this box? — Yes, ____.','I can','Мовець може нести коробку й відповідає про себе; уведи лише відсутню коротку частину.'],
                ['gap','Were the shops open? — No, ____.',"they weren't",'Крамниці були зачинені; уведи займенник групи й минуле заперечення, без повторення початку.', ['they were not']],
                ['translation','На запитання, чи музей зараз відчинений, дай всю коротку відповідь: «Так, відчинений».','Yes,|it is.','Відповідай про теперішній стан одного музею стверджувально; в кінці короткої відповіді форму не скорочуй.'],
                ['translation','На запитання у простому минулому часі, чи діти прийшли, дай всю коротку відповідь: «Ні, не прийшли».','No,|they did not.','Потрібна заперечна коротка відповідь про минулу дію групи; скорочення приймається.', ["No, they didn't."]],
            ],
            'alternative'=>[
                ['gap','Are the plates round ____ square?','or','Поєднай дві запропоновані форми тарілок словом вибору.'],
                ['gap','Does Danylo ____ chess or tennis on Saturdays?','play','Запитай про звичну гру в один із двох видів; після допоміжного потрібна початкова форма.'],
                ['translation','Ви хочете суп чи салат? Спочатку назви суп.','Do you want|soup or salad?','Побудуй повне питання про вибір співрозмовників між двома стравами.'],
                ['translation','Зустріч буде онлайн чи в офісі? Спочатку назви онлайн.','Will the meeting|be online|or in|the office?','Запитай про майбутній формат уже відомої зустрічі; збережи обидві альтернативи.'],
            ],
            'tags'=>[
                ['gap','The soup smells good, ____?',"doesn't it",'Головна частина позитивна: уведи лише заперечний запит підтвердження про одну страву.', ['does it not']],
                ['gap',"Olena hasn't left, ____?",'has she','Головна частина заперечна: уведи лише позитивний запит підтвердження про ту саму жінку.'],
                ['translation','Ви забронювали квитки, правда? Почни зі співрозмовників.','You have booked|the tickets,|haven\'t you?','Попроси стандартне підтвердження теперішнього результату; використай протилежну полярність у кінці.', ['You have booked the tickets, have you not?']],
                ['translation','Повернімося до цього питання, добре?','Let\'s return|to this question,|shall we?','Запропонуй спільну дію й додай типовий запит підтвердження саме до такої пропозиції.'],
            ],
            'negative-questions'=>[
                ['gap','____ the library open today?',"Isn't",'Очікуєш, що бібліотека відчинена; почни повне заперечне питання скороченою формою стану.'],
                ['gap','____ you hear the bell yesterday?',"Didn't",'Здивовано запитай про вчорашнє сприйняття дзвінка; потрібне скорочене заперечне допоміжне.'],
                ['translation','Хіба Павло не любить груші?','Doesn\'t Pavlo|like pears?','Побудуй повне заперечне питання про теперішнє вподобання чоловіка; після допоміжного дія початкова.', ['Does Pavlo not like pears?']],
                ['translation','Хіба вони не можуть зачекати надворі?','Can\'t they|wait outside?','Запитай заперечно про можливість групи почекати не в приміщенні.', ['Can they not wait outside?', "Can't they wait outdoors?", 'Can they not wait outdoors?'], ['outside?'=>['outdoors?']]],
            ],
            'subject'=>[
                ['gap','Who ____ the last slice yesterday?','ate','Запитай, хто виконав учорашню дію з останнім шматком; потрібна минула форма дієслова їжі.'],
                ['gap','Which students ____ missing today?','are','Питальна група вже є підметом множини; назви її теперішній стан відсутності.'],
                ['translation','Що впало з полиці вночі?','What fell|off the shelf|during the night?','Запитай про невідому річ як підмет минулої події; не додавай питальне допоміжне.', ['What fell off the shelf at night?']],
                ['translation','Хто приніс ці квіти?','Who brought|these flowers?','Запитай про невідомого виконавця минулого принесення близьких квітів, не про їхнього одержувача.'],
            ],
            'embedded'=>[
                ['gap','I wonder where the new path ____.','leads','Повідом про цікавість до напрямку нової стежки; усередині потрібна особова форма теперішнього часу.'],
                ['gap','Do you know ____ the concert has started?','if','У головному питанні запитай про знання факту початку; обидва звичайні сполучники значення «чи» приймаються.', ['whether']],
                ['translation','Я пам’ятаю, де ми залишили велосипеди.','I remember|where we left|the bikes.','Збережи теперішню пам’ять про минуле залишення вже відомих велосипедів; усередині не роби інверсії.', ['I recall where we left the bikes.','I remember where we left the bicycles.','I recall where we left the bicycles.'], ['remember'=>['recall'],'bikes.'=>['bicycles.']]],
                ['translation','Ти знаєш, чому Андрій пішов рано?','Do you know|why Andrii|left early?','Головна частина є питанням про знання; внутрішня частина зберігає звичайний порядок минулої дії.'],
            ],
            'indirect'=>[
                ['gap','Could you explain how this lock ____?','works','Увічливо запитай про спосіб роботи одного замка; у внутрішній частині підмет уже стоїть перед дією.'],
                ['gap','Would you tell me ____ there is a later bus?','if','Увічливо запитай про існування пізнішого автобуса; обидва сполучники значення «чи» приймаються.', ['whether']],
                ['translation','Можете сказати мені, де зупиняється автобус? Збережи рамку прохання з поданим модальним.','Could you|tell me|where the bus|stops?','Увічливо попроси інформацію; внутрішню частину про звичну зупинку не перетворюй на пряме питання.'],
                ['translation','Хотів би дізнатися, чи можна тут сфотографувати. Збережи подану м’яку рамку.','I was wondering|if I can|take a photo|here.','М’яка минула рамка запитує про теперішній дозвіл самому мовцеві.', ['I was wondering whether I can take a photo here.','I was wondering if I can take a photograph here.','I was wondering whether I can take a photograph here.'], ['if'=>['whether'],'photo'=>['photograph']]],
            ],
            'negative-forms'=>[
                ['gap','The children ____ asleep yet.','are not','Діти ще не сплять: назви заперечний стан множини, не додаючи допоміжного для дії.', ["aren't"]],
                ['gap','He decided ____ open the envelope.','not to','Він вирішив не відкривати конверт: заперечення стосується інфінітива, а сам факт рішення збережений.', ['to not']],
                ['translation','Ми не користуємося цим входом уночі.','We do not|use this entrance|at night.','Почни з нашої групи й збережи регулярне заперечення та нічний час.', ["We don't use this entrance at night."]],
                ['translation','Вона ще не надіслала рахунок.','She has not|sent the invoice|yet.','Збережи відсутність результату до теперішнього моменту; підмет жіночий.', ["She hasn't sent the invoice yet.",'She has not sent the bill yet.',"She hasn't sent the bill yet."], ['invoice'=>['bill']]],
            ],
            'double-negatives'=>[
                ['gap',"We didn't find ____ under the bed.",'anything','Ми нічого не знайшли: після вже показаного заперечення не додавай другого заперечного займенника.'],
                ['gap','____ of the two lamps works.','Neither','Жодна з рівно двох ламп не працює; уведи заперечну форму без ще одного заперечення.', ['Neither one']],
                ['translation','Ніхто не торкався цього важеля. Почни із заперечного займенника.','Nobody|touched this lever.','У минулому ніхто не виконав дії; одного заперечного займенника достатньо.', ['No one touched this lever.']],
                ['translation','У нас немає жодних запасних батарейок. Почни з нашої групи та заперечного допоміжного.','We do not|have any|spare batteries.','Передай повну відсутність уже названих запасних предметів; не вживай другого заперечного означника.', ["We don't have any spare batteries."]],
            ],
        ];
    }

    public static function shuffled(array $groups, string $seed): array
    {
        $rows=[]; foreach($groups as $i=>$text){$rows[]=['text'=>$text,'key'=>hash('sha256',$seed.'|'.$i.'|'.$text)];}
        usort($rows,static fn($a,$b)=>strcmp($a['key'],$b['key'])); $out=array_column($rows,'text');
        if($out===$groups){$out[]=array_shift($out);} return $out;
    }
    private static function plainUk(string $text): void
    {
        if(trim($text)==='' || !preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$text)
            || preg_match('/[A-Za-z]|<[^>]+>|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu',$text)){
            throw new RuntimeException('A native condition or hint is not plain Ukrainian.');
        }
    }
    private static function groups(string $ordered, string $answer): array
    {
        $groups=explode('|',$ordered);
        if(count($groups)<2 || implode(' ',$groups)!==$answer){throw new RuntimeException('Native groups do not reconstruct the full answer.');}
        foreach($groups as $group){if(trim($group)!==$group || $group==='' || str_contains($group,'/') || count(preg_split('/\s+/u',$group))>3){throw new RuntimeException('Native token group is not one to three words.');}}
        return $groups;
    }
    private static function labels(array $groups, array $aliases): array
    {
        $out=[];
        foreach($groups as $group){$values=$aliases[$group]??[];
            foreach($aliases as $word=>$synonyms){if(isset($aliases[$group])){break;}
                $pattern='/(?<![A-Za-z])'.preg_quote($word,'/').'(?![A-Za-z])/u';
                if(preg_match($pattern,$group)!==1){continue;}
                foreach($synonyms as $synonym){$values[]=preg_replace($pattern,$synonym,$group,1);}
            }
            if($values!==[]){$out[$group]=$group.' ('.implode(' / ',array_unique($values)).')';}
        }
        return $out;
    }

    public static function compile(): array
    {
        $document=['schema_version'=>1,'package'=>'questions-negations-native-practice-quality','status'=>'frozen','targets'=>[]]; $releases=[];
        foreach(self::specs() as $topic=>[$release,$i,$presenter]){
            if(!isset($releases[$release])){$releases[$release]=$release::load(self::root()); $release::validate(...$releases[$release]);}
            [,$patch]=$releases[$release]; $target=$patch['targets'][$i]; $slot=$release::PRACTICE_SOURCE_INDICES[$i]; $stored=$target['after']['page']['blocks'][$slot];
            $binding=['seeder'=>$target['identity'],'page_id'=>$release::PAGE_IDS[$i],'page_category_id'=>$release::CATEGORY_IDS[$i],
                'uuid'=>M26DetailPackage::uuid($target['identity'],$stored,$slot+1),'locale'=>'uk','type'=>'practice-set',
                'column'=>'left','sort_order'=>$slot+1,'level'=>$release::LEVELS[$i],'heading'=>null,'css_class'=>null];
            $block=(object)($binding+['body'=>$stored['body']]); $raw=json_decode($stored['body'],true,flags:JSON_THROW_ON_ERROR);
            $presentation=$release::presentation($block,$raw);
            if(!is_array($presentation['data']??null)){throw new RuntimeException('Exact accepted native presentation missing.');}
            $before=$presentation['data']; $after=$before; $updates=[]; $tokenLabels=[];
            if(count($before['inputs'])!==count(self::retained()[$topic])){throw new RuntimeException('Native retained input count differs.');}
            foreach(self::retained()[$topic] as $index=>[$ordered,$condition,$hint]){
                self::plainUk($condition); self::plainUk($hint); $old=$after['inputs'][$index];
                $kind=$ordered===null?'gap':'order'; $after['inputs'][$index]['practice_kind']=$kind;
                $after['inputs'][$index]['verb_hint']=$hint;
                $after['inputs'][$index]['after']=$condition;
                unset($after['inputs'][$index]['prompt']);
                $groups=$ordered===null?[]:self::groups($ordered,$old['answer']);
                if($groups!==[]){$after['inputs'][$index]['before']=implode(' / ',self::shuffled($groups,'qnq:retained:'.$topic.':'.$index));}
                $updates[]=['index'=>$index,'kind'=>$kind,'condition_uk'=>$condition,'hint_uk'=>$hint,'ordered_groups'=>$groups];
            }
            // Editing conditions retain all source facts; the separate hint is an explicit reviewed Ukrainian sentence.
            foreach($after['rephrase']??[] as $index=>$item){if(!is_string($item['answer']??null)){continue;}
                $hint=self::editingHints()[$topic][$index]??null;
                if(!is_string($hint)){throw new RuntimeException('Retained editing hint missing: '.$topic.' / '.$index);} self::plainUk($hint);
                $after['rephrase'][$index]['verb_hint']=$hint;
            }
            $added=[];
            foreach(self::additions()[$topic] as $n=>$row){
                [$kind,$condition,$ordered,$hint]=$row; self::plainUk($hint);
                $id='qnq-native-'.$topic.'-'.$kind.'-'.($n%2+1); $groups=[];
                if($kind==='translation'){self::plainUk($condition);$answer=str_replace('|',' ',$ordered);$groups=self::groups($ordered,$answer);
                    $beforeText=implode(' / ',self::shuffled($groups,$id));$afterText=$condition;
                }else{if(substr_count($condition,'____')!==1){throw new RuntimeException('Native gap must have one complete phrase field.');}
                    $beforeText=$condition;$afterText=$hint;$answer=$ordered;}
                $accepted=array_values(array_unique([$answer,...($row[4]??[])]));$index=count($after['inputs']);
                if($kind==='gap'&&count($accepted)>1){$afterText.=' Приймаються: ('.implode(' / ',$accepted).').';}
                $after['inputs'][]=['id'=>$id,'practice_kind'=>$kind,'before'=>$beforeText,'after'=>$afterText,'verb_hint'=>$hint,
                    'answer'=>$answer,'accepted'=>$accepted,'feedback'=>array_fill_keys(array_map('mb_strtolower',$accepted),'Правильно. '.$hint)];
                $labels=self::labels($groups,$row[5]??[]); if($labels!==[]){$tokenLabels[$index]=$labels;}
                $added[]=['id'=>$id,'index'=>$index,'kind'=>$kind,'condition'=>$condition,'hint_uk'=>$hint,'answer'=>$answer,'accepted'=>$accepted,'ordered_groups'=>$groups];
            }
            foreach(self::strictNativeSurfaces()[$topic]??[] as $group=>$indices){foreach($indices as $index){
                $item=$after[$group][$index]??null;
                if(!is_array($item)||!is_string($item['answer']??null)){throw new RuntimeException('Exact native strict surface control missing.');}
                $after[$group][$index]['qnq_strict_surface_answers']=array_values(array_unique([$item['answer'],...($item['accepted']??[])]));
            }}
            $after['intro']='Виконай завдання й натисни «Перевірити».';
            $after['input_title']='Вправа 3. Доповни й побудуй речення';
            $after['input_intro']='Заповнюй лише пропуски; повні речення вводь вручну або складай із груп слів.';
            $after['questions_negations_practice_quality']=['revision'=>'v1','topic'=>$topic];
            $document['targets'][]=['topic'=>$topic,'definition'=>$target['path'],'definition_sha256'=>hash_file('sha256',self::root().'/'.$target['path']),
                'release'=>$release::SOURCE,'release_sha256'=>$release::SOURCE_SHA,
                'binding'=>$binding+['body_sha256'=>hash('sha256',$stored['body'])],
                'local_url'=>'http://gramlyze.loc/theory/'.implode('/',[...$release::ANCESTRIES[$i],$release::SLUGS[$i]]),
                'before_data'=>$before,'before_data_sha256'=>self::digest($before),'after_data'=>$after,'after_data_sha256'=>self::digest($after),
                'input_updates'=>$updates,'added'=>$added,'input_token_labels'=>$tokenLabels];
        }
        return $document;
    }

    public static function editingHints(): array
    {
        return [
            'question-forms'=>[1=>'Побудуй нейтральне питання про стан співрозмовника; зайве допоміжне для дії прибери.',2=>'Запитай про звичне місце проживання жінки; додай потрібне допоміжне й віднови початкову форму дії.',3=>'Запитай про вміння плавати; модальне саме стоїть перед співрозмовником.',4=>'Запитай нейтрально про невідомого виконавця минулого дзвінка; підсилення не потрібне.'],
            'yes-no'=>[1=>'Потрібне нейтральне питання про стан жінки, без допоміжного для дії.',2=>'Збережи минулий час руху групи додому; після допоміжного віднови початкову форму.',3=>'Постав модальне перед співрозмовником і збережи мовця як одержувача допомоги.'],
            'wh'=>[1=>'Додай допоміжне для звичного місця проживання жінки; основну дію поверни до початкової форми.',2=>'Невідома людина є підметом; для нейтрального питання забери зайве підсилювальне допоміжне.',3=>'Запитай про кількість незлічуваних грошей, не про число окремих монет.'],
            'answers'=>[1=>'Відповідай про власне вміння плавати ствердно, повторивши потрібне модальне.',2=>'Коротко підтвердь стан названої жінки; особову форму стану не пропускай.',3=>'Дай коротке ствердження про власне вподобання за допомогою допоміжного, не основного дієслова.'],
            'alternative'=>[1=>'Після допоміжного третьої особи прибери закінчення основної дії, зберігши обидва варіанти.',2=>'Додай явне слово вибору між двома температурами; форму стану збережи.',3=>'Постав модальне перед співрозмовником; збережи дві навички як альтернативи.'],
            'tags'=>[1=>'Для звичного вподобання потрібне допоміжне дії; у підтвердженні зміни полярність.',2=>'У кінці збережи модальне головної частини та заперечну полярність.',3=>'Головна частина стверджувальна; приймаються повна формальна й звична скорочена моделі підтвердження.'],
            'negative-questions'=>[1=>'Після заперечного допоміжного прибери закінчення основної дії, не змінюючи змісту.',2=>'Перенеси заперечне модальне перед співрозмовником і збережи навичку плавання.',3=>'Залиши лише одне заперечне допоміжне перед співрозмовником; теперішнє знання збережи.'],
            'subject'=>[], 'embedded'=>[], 'indirect'=>[], 'negative-forms'=>[], 'double-negatives'=>[],
        ];
    }
    /** Explicit word-order-sensitive surfaces; generic contraction substitution cannot reorder a subject and negation. */
    public static function strictNativeSurfaces(): array
    {
        return [
            'negative-questions'=>['inputs'=>[0,1,2,3,4,5,6,7],'rephrase'=>[1,2,3]],
            'tags'=>['inputs'=>[0,2,4,6,8],'rephrase'=>[1,2,3]],
            'answers'=>['inputs'=>[0,1,2,3,6,7,8,9],'rephrase'=>[1,2,3]],
        ];
    }
    public static function strictWidgetIds(): array
    {
        return [35007,35015,35080,35081,35089,34839,34841,34843,34847,34849,59723,59724];
    }

    public static function load(): array
    {
        $path=self::root().'/'.self::SOURCE;
        if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path))){throw new RuntimeException('Questions/negations native source bytes differ.');}
        static $verified=null;
        if($verified===null){$verified=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);self::validate($verified);}
        foreach($verified['targets'] as $target){foreach(['definition','release'] as $kind){$file=self::root().'/'.$target[$kind];
            if(!is_file($file)||is_link($file)||!hash_equals($target[$kind.'_sha256'],hash_file('sha256',$file))){throw new RuntimeException('Questions/negations native pinned source differs.');}
        }}
        return $verified;
    }
    public static function validate(array $document): void
    {
        if($document!==self::compile()){throw new RuntimeException('Questions/negations native finite projection differs.');}
    }
    /** Explicit source-bound display compiler: short instructions, separate stimuli, two or three items per exercise. */
    public static function compileCompact(): array
    {
        $source=self::load();$out=['schema_version'=>1,'source'=>['path'=>self::SOURCE,'sha256'=>self::SOURCE_SHA],'targets'=>[]];
        foreach($source['targets'] as $target){
            $data=$target['after_data'];$items=[];$groups=[];
            foreach(['selects','choices','inputs','rephrase'] as $kind){
                $buckets=[];$examples=[];$items[$kind]=[];
                foreach($data[$kind]??[] as $index=>$item){
                    $instruction=match($kind){'selects'=>'Обери правильну форму.','choices'=>'Обери правильне твердження.','rephrase'=>'Виправ або перебудуй повне речення.',default=>match($item['practice_kind']){'gap'=>'Уведи лише пропущену частину.','translation'=>'Переклади повне речення.','order'=>'Склади речення з усіх груп слів.'}};
                    if($kind==='inputs'){
                        if($item['practice_kind']==='gap'){
                            $frame=$item['before'];if(!str_contains($frame,'____')){$frame=rtrim($frame).' ____';}
                            $stimuli=[['lang'=>'uk','text'=>$item['after']],['lang'=>'en','text'=>$frame]];
                        }else{$stimuli=[['lang'=>'uk','text'=>$item['after']]];}
                    }else{
                        $text=$item['label']??$item['original']??null;
                        if($text===null){$text=implode(' ',array_filter([$item['example_label']??null,$item['example_original']??null,$item['example_target']??null]));}
                        // These are exact finite source fields, not a runtime truncation or sentence splitter.
                        $text=trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags(str_replace(['</p>','<br>','<br/>','<br />'],"\n",$text)),ENT_QUOTES|ENT_HTML5,'UTF-8')));
                        $stimuli=[['lang'=>'uk','text'=>$text]];
                    }
                    $items[$kind][]=['index'=>$index,'source_item_sha256'=>self::digest($item),'instruction_uk'=>$instruction,'stimuli'=>$stimuli];
                    if(empty($item['answer'])&&empty($item['accepted'])){$examples[]=$index;continue;}
                    $bucket=$kind==='inputs'?$item['practice_kind']:$kind;$buckets[$bucket][]=$index;
                }
                foreach($buckets as $bucket=>$indices){
                    $chunks=[];while(count($indices)>0){$take=count($indices)===4?2:min(3,count($indices));$chunks[]=array_splice($indices,0,$take);}
                    foreach($chunks as $chunkIndex=>$chunk){
                        $title=match($bucket){'selects'=>'Обери форму','choices'=>'Перевір зміст','gap'=>'Заповни пропуски','translation'=>'Переклади речення','order'=>'Розташуй слова','rephrase'=>'Виправ речення'};
                        $instruction=match($bucket){'selects'=>'Обери один варіант.','choices'=>'Збережи зміст умови.','gap'=>'Уведи тільки відсутню частину.','translation'=>'Введи переклад або скористайся токенами.','order'=>'Використай усі групи слів або введи речення вручну.','rephrase'=>'Збережи учасників, час і задум.'};
                        $group=['key'=>'qnq-'.$target['topic'].'-'.$bucket.'-'.($chunkIndex+1),'kind'=>$kind,'indices'=>$chunk,'title'=>$title,'instruction'=>$instruction];
                        if($examples!==[]){$group['example_indices']=$examples;$examples=[];}
                        $groups[]=$group;
                    }
                }
            }
            $out['targets'][]=['topic'=>$target['topic'],'binding'=>$target['binding'],'after_data_sha256'=>$target['after_data_sha256'],'items'=>$items,'groups'=>$groups];
        }
        return $out;
    }
    private static function entry(object $block,array $data,bool $projected): ?array
    {
        if(app()->getLocale()!=='uk'||($block->locale??null)!=='uk'||($block->type??null)!=='practice-set'
            ||!str_starts_with($block->seeder??'','Database\\Seeders\\Page_V3\\QuestionsNegations\\')){return null;}
        try{foreach(self::load()['targets'] as $target){foreach($target['binding'] as $field=>$value){if($field==='body_sha256'){continue;}
            if(($block->$field??null)!==$value){continue 2;}}
            if(is_string($block->body??null)&&hash_equals($target['binding']['body_sha256'],hash('sha256',$block->body))
                &&$data===$target[$projected?'after_data':'before_data']){return $target;}
        }}catch(Throwable){/* Changed or foreign sources retain their complete previous practice. */} return null;
    }
    public static function project(object $block,array $data,bool $canonicalCaller): array { return $canonicalCaller?(self::entry($block,$data,false)['after_data']??$data):$data; }
    public static function isProjected(object $block,array $data): bool { return self::entry($block,$data,true)!==null; }
    public static function originalDataForDisplay(object $block,array $data): array { return self::entry($block,$data,true)['before_data']??$data; }
    public static function inputTokenLabels(object $block,array $data): array { return self::entry($block,$data,true)['input_token_labels']??[]; }

    /** Per-question guidance and independently reviewed lexical alternatives for the existing sixty widgets. */
    public static function widgetPlans(): array
    {
        return [
            34263=>['Запитай співрозмовника про теперішню готовність; постав особову форму стану перед ним.'],
            34265=>['Запитай про звичну роботу жінки тут; після допоміжного потрібна початкова форма дії.'],
            34266=>['Почни з питального слова місця й запитай про звичне проживання групи.'],
            34269=>['Запитай про можливість допомоги мовцеві; модальне став перед співрозмовником.'],
            34272=>['Невідомий виконавець минулого дзвінка є підметом; не додавай зайвого питального допоміжного.', ['Who phoned you?'], ['called'=>['phoned']]],
            34335=>['Запитай про звичне вподобання чаю одним співрозмовником; допоміжне постав перед ним.'],
            34338=>['Запитай про теперішню професію жінки; не пропускай означник перед назвою професії.'],
            34339=>['Запитай про вміння плавати, не дозвіл; потрібне модальне дієслово здатності.'],
            34341=>['Запитай про вчорашній дощ; час передай допоміжним, а дію залиш у початковій формі.'],
            34345=>['Перевір звичний розклад зустрічі загальним питанням; названий час залиш у кінці.', ['Does the meeting begin at nine?'], ['start'=>['begin']]],
            34409=>['Запитай про час минулого прибуття групи; після допоміжного потрібна початкова форма.'],
            34410=>['Почни з питання про причину теперішнього перебування чоловіка тут; збережи форму стану.'],
            34411=>['Невідома людина є додатком: запитай, кого співрозмовник бачив учора, з допоміжним минулого часу.'],
            34412=>['Запитай про число потрібних книжок усією питальною групою; іменник злічуваний і множинний.'],
            34414=>['Запитай про напрямок поточного руху співрозмовника тривалою формою; обставину теперішнього моменту збережи.'],
            34839=>['Дай лише всю коротку стверджувальну відповідь про власне вподобання; повтори потрібне допоміжне.'],
            34841=>['Коротко підтвердь готовність названої жінки; стверджувальну особову форму в кінці не скорочуй.'],
            34843=>['Коротко підтвердь власне вміння плавати, повторивши модальне; основну дію не повторюй.'],
            34847=>['Назви лише місце проживання в Києві з прийменником; повне речення не потрібне.'],
            34849=>['Коротко запереч про минуле запізнення групи; за видимою умовою використай скорочену форму.'],
            34923=>['Запропонуй співрозмовнику вибір бажаного напою ввічливою моделлю; чай назви перед кавою.'],
            34926=>['Запитай про вибір між двома конкретними ручками; назву предмета повтори для кожного кольору.'],
            34927=>['Запитай про поточну поїздку тривалою формою; прийменник способу повтори перед кожним видом транспорту.', ['Are you traveling by bus or by train?'], ['travelling'=>['traveling']]],
            34929=>['Вибери конкретну синю ручку короткою фразою й додай ввічливе слово; відповіді «так» недостатньо.'],
            34934=>['Запитай про майбутній вибір оплати; картку назви перед готівкою й збережи типові прийменники.'],
            35007=>['Збережи теперішній розклад одного потяга й додай стандартне скорочене заперечне підтвердження.', ["The train departs soon, doesn't it?"], ['leaves'=>['departs']]],
            35008=>['Збережи минулий заперечний стан кількох документів; у кінці потрібне стверджувальне підтвердження.'],
            35013=>['Почни з наказового прохання надіслати адресу мовцеві; додай звичне підтвердження після наказу.', ['Send the address to me, will you?']],
            35014=>['Запропонуй спільно переглянути вже відомий план і додай типовий запит підтвердження такої пропозиції.', ["Let's go over the plan, shall we?"], ['review'=>['go over']]],
            35015=>['Збережи позитивне повідомлення мовця про власну відповідальність і звичне скорочене заперечне підтвердження.'],
            59723=>['Факт знання позитивний: коротко підтвердь його від імені мовця, не погоджуйся із запереченням питання.'],
            59724=>['Факт участі негативний: коротко відповідай про жінку повною заперечною формою, як вимагає умова.'],
            35080=>['Один принтер неочікувано не працює: побудуй повне скорочене заперечне питання теперішнього часу.'],
            35081=>['Запитай здивовано про минуле пояснення правила Марією; після заперечного допоміжного дія початкова.'],
            35089=>['Побудуй заперечне питання-пораду команді; перегляд предмета має бути передусім.', ["Shouldn't the team go over it first?"], ['review'=>['go over']]],
            34491=>['Невідомий мешканець є підметом; запитай нейтрально про цей будинок без зайвого допоміжного.'],
            34492=>['Невідома річ є підметом минулого падіння; збережи напрямок зі столу.', ['What fell from the table?'], ['off'=>['from']]],
            34496=>['Запитай про невідомого виконавця минулого дзвінка матері мовця; не перетворюй матір на виконавицю.', ['Who phoned my mother?'], ['called'=>['phoned']]],
            34500=>['Запитай, яка річ зламалася сьогодні вранці; потрібна неперехідна минула форма, без виконавця-людини.'],
            34502=>['Невідома причина є підметом: запитай про минуле спричинення вже названого шуму.'],
            35444=>['Інверсію залиш тільки в головному питанні; усередині збережи порядок твердження й поданий сполучник.'],
            35445=>['Повідом про власну невпевненість, не став головного прямого питання; збережи приєднання до нашої групи сьогодні.'],
            59725=>['Збережи теперішнє знання місця роботи Ніни; внутрішня частина має порядок твердження.'],
            59726=>['Запитай про знання відправника посилки; невідомий відправник є підметом внутрішньої минулої дії.', ['Do you know who sent the package?'], ['parcel?'=>['package?']]],
            59727=>['Збережи заперечення звичної дії щоп’ятниці; прибирати потрібно питальний порядок, а не допоміжне заперечення.'],
            35516=>['Увічливо попроси сказати інформацію про теперішній стан банку; усередині потрібен порядок твердження.'],
            35517=>['Запитай зараз про результат відповіді сьогодні; збережи заданий сполучник і форму теперішнього результату.'],
            59728=>['Увічливо попроси час звичного закриття музею; внутрішню частину не перетворюй на пряме питання.', ['Could you tell me when the museum shuts?'], ['closes?'=>['shuts?']]],
            59729=>['Одразу назви місце квиткової каси біля входу повним твердженням; не обмежуйся буквальним підтвердженням можливості.', ['The ticket office is next to the entrance.','The ticket office is by the entrance.','The ticket office is near the entrance.'], ['beside'=>['next to','by','near']]],
            59730=>['М’яка минула рамка передає теперішній запит про власну оплату; збережи поданий сполучник і спосіб оплати.'],
            35571=>['Повідом про теперішню неготовність жінки; за видимою умовою потрібне повне заперечення стану.'],
            35573=>['Збережи постійне місце роботи чоловіка й повне заперечення; основна дія після допоміжного початкова.'],
            35577=>['Передай заборону паркування, не відсутність обов’язку; за умовою потрібне однослівне повне заперечення.'],
            35578=>['Збережи майбутнє прибуття групи з повним запереченням; обставина до полудня стоїть у кінці.'],
            35579=>['Звіту ще не завершено до теперішнього моменту; збережи повне заперечення та слово незавершеності в кінці.'],
            35655=>['Збережи повне допоміжне заперечення та неозначений займенник; не додавай другого заперечного слова.'],
            35656=>['Передай повну відсутність готівки сьогодні; після допоміжного заперечення потрібний незаперечний означник.'],
            35659=>['Почни із заперечного займенника про жодну людину; минуле дієслово дзвінка залиш без другого заперечення.', ['Nobody phoned after midnight today.'], ['called'=>['phoned']]],
            35660=>['Передай відсутність висловленого про план заперечним займенником; основну минулу дію не заперечуй удруге.'],
            35662=>['Ідеться рівно про дві відповіді: заперечний означник уже передає відсутність правильної; другого заперечення не додавай.'],
        ];
    }

    /** Mechanical export of exact live bindings plus independently authored finite teaching plans. */
    public static function compileWidgetBindings(array $capture): array
    {
        if(($capture['schema_version']??null)!==1||count($capture['targets']??[])!==12||count($capture['questions']??[])!==60){throw new RuntimeException('Complete local widget capture required.');}
        $plans=self::widgetPlans();$out=$capture;$out['package']='questions-negations-native-widget-bindings';
        foreach($out['questions'] as &$binding){
            $plan=$plans[$binding['id']]??null;if($plan===null){throw new RuntimeException('Reviewed widget teaching plan missing.');}self::plainUk($plan[0]);
            $canonical=implode(' ',$binding['correct_tokens']);$binding['hint_uk']=$plan[0];
            $binding['accepted_sentences']=array_values(array_unique([$canonical,...($plan[1]??[])]));
            if(in_array($binding['id'],self::strictWidgetIds(),true)){$binding['qnq_strict_surface_answers']=$binding['accepted_sentences'];}
            $binding['token_labels']=self::labels($binding['correct_tokens'],$plan[2]??[]);
            $known=$binding['correct_tokens'];$extras=[];
            foreach($binding['accepted_sentences'] as $sentence){foreach(preg_split('/\s+/u',$sentence) as $word){if(!in_array($word,$known,true)){$extras[]=$word;$known[]=$word;}}}
            $binding['added_options']=$extras;
        }unset($binding);
        return $out;
    }

    public static function loadWidgets(): array
    {
        $path=self::root().'/'.self::WIDGET_BINDINGS;
        if(!is_file($path)||is_link($path)||!hash_equals(self::WIDGET_BINDINGS_SHA,hash_file('sha256',$path))){throw new RuntimeException('Questions/negations widget binding bytes differ.');}
        static $data=null;
        if($data===null){$data=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);self::validateWidgets($data);}
        foreach($data['targets'] as $target){$audit=self::root().'/'.$target['audit'];if(!is_file($audit)||is_link($audit)||!hash_equals($target['audit_sha256'],hash_file('sha256',$audit))){throw new RuntimeException('Questions/negations widget audit pin differs.');}}
        return $data;
    }
    public static function validateWidgets(array $data): void
    {
        $original=$data;
        foreach($original['questions']??[] as &$binding){foreach(['hint_uk','accepted_sentences','token_labels','added_options','qnq_strict_surface_answers'] as $key){unset($binding[$key]);}}unset($binding);
        if(self::compileWidgetBindings($original)!==$data){throw new RuntimeException('Questions/negations widget teaching projection differs.');}
        if(array_column($data['targets'],'topic')!==self::TOPICS||count(array_unique(array_column($data['questions'],'id')))!==60){throw new RuntimeException('Questions/negations widget finite target scope differs.');}
        foreach($data['targets'] as $target){if(count($target['selected_ids'])!==5){throw new RuntimeException('Each lower widget requires five distinct selected questions.');}}
    }

    /** Exact canonical-theory clones only; stored models, answers and progress identities are never changed. */
    public static function projectWidget(object $question,?string $blockUuid,array $payload,bool $canonicalCaller=false): array
    {
        if(!$canonicalCaller||app()->getLocale()!=='uk'||($question->locale??'uk')!=='uk'||($payload['type']??null)!=='4'||(string)($question->type??'')!=='4'){return $payload;}
        try{foreach(self::loadWidgets()['questions'] as $binding){
            foreach(['id','uuid','seeder','type','level','question'] as $field){$actual=$question->$field??null;if($field==='type'){$actual=(string)$actual;}if($actual!==$binding[$field]){continue 2;}}
            if($blockUuid!==$binding['block_uuid']||($payload['id']??null)!==$binding['id']||($payload['level']??null)!==$binding['level']
                ||($payload['question']??null)!==$binding['question']||($payload['correct_tokens']??null)!==$binding['correct_tokens']
                ||($payload['accepted_sentences']??[])!==[]||!is_array($payload['options']??null)){return $payload;}
            if(method_exists($question,'relationLoaded')&&!$question->relationLoaded('answers')){return $payload;}
            $loaded=collect($question->answers??[])->sortBy('marker',SORT_NATURAL)->values();
            if($loaded->count()!==count($binding['answers'])||count($payload['answers']??[])!==$loaded->count()){return $payload;}
            foreach($binding['answers'] as $i=>$expected){$actual=$loaded[$i];$shown=$payload['answers'][$i];
                if(method_exists($actual,'relationLoaded')&&!$actual->relationLoaded('option')){return $payload;}
                foreach(['id','question_id','marker','position','option_id'] as $field){if(($actual->$field??null)!==$expected[$field]){return $payload;}}
                if(($actual->option->id??null)!==$expected['option_id']||($actual->option->option??null)!==$expected['option']
                    ||($shown['marker']??null)!==$expected['marker']||($shown['correct']??null)!==$expected['option']){return $payload;}
            }
            foreach($binding['correct_tokens'] as $token){if(!in_array($token,$payload['options'],true)){return $payload;}}
            $payload['accepted_sentences']=$binding['accepted_sentences'];
            $payload['options']=array_values(array_unique([...$payload['options'],...$binding['added_options']]));
            $payload['derived_compose_options']=array_values(array_unique([...($payload['derived_compose_options']??[]),...$binding['added_options']]));
            $payload['token_labels']=array_replace($payload['token_labels']??[],$binding['token_labels']);
            $payload['source_bound_compose_hint']=true;$payload['compose_preanswer_hint']=$binding['hint_uk'];
            if(isset($binding['qnq_strict_surface_answers'])){$payload['qnq_strict_surface_answers']=$binding['qnq_strict_surface_answers'];}
            $payload['qnq_widget_alias_revision']=self::digest($binding);return $payload;
        }}catch(Throwable){/* Changed, foreign or unloaded bindings retain their complete original payload. */}return $payload;
    }
}
