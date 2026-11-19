<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Finite teaching data only: exact UK theory bindings, no queries, writes or styling. */
final class PronounsDemonstrativesPracticeSupplement
{
    public const SOURCE = 'docs/content/pronouns-demonstratives-practice-supplement.v1.json';
    public const SOURCE_SHA = '810422870be39372f35d1b85e87fa3c9f06fe7b6ac0a66ec13e7889930b5d0aa';

    // Frozen widget identities and dependency pins, bound to SOURCE_SHA.
    private const WIDGET_MEMBERSHIP_SHA = '0578d37e35f5172408e834e615a900f924ce4205bde3512b797d2e0f0585b446';
    private const WIDGET_MEMBERSHIP = [
        'source_sha' => '810422870be39372f35d1b85e87fa3c9f06fe7b6ac0a66ec13e7889930b5d0aa',
        'pairs' => [
            'b00124f3-ec4d-54fb-91b8-b6e5becaf4b9' => [33131, 33133, 33128],
            '1613f5c2-8294-57fd-bbd8-ea49c51a4df4' => [51184, 51187, 51189],
            '4785288c-2232-5b44-92dd-f25cb978cb1a' => [33332],
            '44d5c5f4-df97-5ac1-aa6d-ccc490091d7a' => [33843, 33847],
        ],
        'widgetUuids' => ['prt-poly-a2-09', 'prt-poly-a2-11', 'pers-obj-poly-a1-02', 'pers-obj-poly-a1-05', 'dtt-poly-a1-06', 'prt-poly-a2-06', 'pers-obj-poly-a1-07', 'rcp-poly-a2-01', 'rcp-poly-a2-05'],
        'bankSeeders' => [
            'Database\\Seeders\\V3\\Polyglot\\PolyglotPronounsTheoryAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotPersonalObjectPronounsAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotDemonstrativesThisThatTheseThoseAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotReciprocalPronounsAllLevelsLessonSeeder',
        ],
        'mandatory' => [
            ['database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesEachEveryAllTheorySeeder/definition.json', 'ddfe7ad8fa0cb4429ff3def1a6aa9fe8041533d5060e1fe430abdb5eff6e7ad7'],
            ['database/content-patches/m76-relative-each-reciprocal.v1.0.1.json', 'e5320ab32be7f15684797173a46f68892fc492e2821b2c41f6306cd730c8e5fc'],
            ['database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesIndefinitePronounsTheorySeeder/definition.json', 'ada88e48fee8c6aaa810329024e387f846018ee09709228f84127dd81ac2c5d8'],
            ['database/content-patches/m75-possessive-reflexive-indefinite.v1.json', '099f4173f18713ca568c15ba72e49a6e4e77789b2880507549a25b01ab22eaa5'],
            ['database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesOneOnesTheorySeeder/definition.json', '871d2b1e08d9bc6c2e53023f7094dfcf9e1f2793a12cc0472ffe4cfca9cde2d9'],
            ['database/content-patches/m77-one-prepositions-phrasal.v1.json', '901af7f3832a6aa071baa0229331968d43ea979c8b04cfd70f98bb0de632e962'],
            ['database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesPersonalObjectPronounsTheorySeeder/definition.json', 'ca1f03830eb6ab085d54c0f2b42962347b44c22e45e5a677b62667cdafb81faf'],
            ['database/content-patches/m74-other-pronouns-basics.v1.0.1.json', '7346e58f395daaceaec2d2630f66ee49b52794db958e83ecc82d50543bcb77a8'],
            ['database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder/definition.json', '34991f95b9fef2f3d26c9141ffe458e4640f6c5c844850005e0c824913113b79'],
            ['database/content-patches/m75-possessive-reflexive-indefinite.v1.json', '099f4173f18713ca568c15ba72e49a6e4e77789b2880507549a25b01ab22eaa5'],
            ['database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesPronounsTheorySeeder/definition.json', '28eadd5ee6c5b0a266ffbce9a3793719482764c8e92a68d8a8c206834c1b2803'],
            ['database/content-patches/m74-other-pronouns-basics.v1.0.1.json', '7346e58f395daaceaec2d2630f66ee49b52794db958e83ecc82d50543bcb77a8'],
            ['database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesReciprocalPronounsTheorySeeder/definition.json', '5c45e3291bc3faf7438a35f8f976352ac275c20099bfc439915dd9c06449e84b'],
            ['database/content-patches/m76-relative-each-reciprocal.v1.0.1.json', 'e5320ab32be7f15684797173a46f68892fc492e2821b2c41f6306cd730c8e5fc'],
            ['database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesReflexivePronounsTheorySeeder/definition.json', 'fafa52b227e204175e0d3bb5544f22786e93f104f73558b86365f98b60346a8c'],
            ['database/content-patches/m75-possessive-reflexive-indefinite.v1.json', '099f4173f18713ca568c15ba72e49a6e4e77789b2880507549a25b01ab22eaa5'],
            ['database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesRelativePronounsTheorySeeder/definition.json', '1be3f91b927e2efeaa4455c6369291a8784a2ad3fd560bae9119d0fd56cc71c1'],
            ['database/content-patches/m76-relative-each-reciprocal.v1.0.1.json', 'e5320ab32be7f15684797173a46f68892fc492e2821b2c41f6306cd730c8e5fc'],
            ['database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesThisThatTheseThoseTheorySeeder/definition.json', '7c14c89f073250abf41cdbcd39151c9b9f641c5033cdfa8c0668ad033ed3b15a'],
            ['database/content-patches/m93-future-completion-demonstratives-some-any.v1.json', '58beaf325626784858765db31920c5dd660a49e233f6acb2d4c2d48c72d877a5'],
            ['database/seeders/V3/Polyglot/PolyglotPronounsTheoryAllLevelsLessonSeeder/definition.json', 'a5292fde8fa57c6c56ac9b1288e4d6a0e89d0266dfbc610e530f76069059b028'],
            ['docs/content/m74-other-pronouns-basics-bank-audit.v1.0.1.json', '0937345b2d3c2b8f7842a0e47d75cd8da268a9ae4e43734aaa8447ce49c78e26'],
            ['database/seeders/V3/Polyglot/PolyglotPronounsTheoryAllLevelsLessonSeeder/definition.json', 'a5292fde8fa57c6c56ac9b1288e4d6a0e89d0266dfbc610e530f76069059b028'],
            ['docs/content/m74-other-pronouns-basics-bank-audit.v1.0.1.json', '0937345b2d3c2b8f7842a0e47d75cd8da268a9ae4e43734aaa8447ce49c78e26'],
            ['database/seeders/V3/Polyglot/PolyglotPersonalObjectPronounsAllLevelsLessonSeeder/definition.json', 'ec53f982d67942fa14ffd0e5322318c919c0f0e3cd8e59f239d173204c22e8a9'],
            ['docs/content/m74-other-pronouns-basics-bank-audit.v1.0.1.json', '0937345b2d3c2b8f7842a0e47d75cd8da268a9ae4e43734aaa8447ce49c78e26'],
            ['database/seeders/V3/Polyglot/PolyglotPersonalObjectPronounsAllLevelsLessonSeeder/definition.json', 'ec53f982d67942fa14ffd0e5322318c919c0f0e3cd8e59f239d173204c22e8a9'],
            ['docs/content/m74-other-pronouns-basics-bank-audit.v1.0.1.json', '0937345b2d3c2b8f7842a0e47d75cd8da268a9ae4e43734aaa8447ce49c78e26'],
            ['database/seeders/V3/Polyglot/PolyglotDemonstrativesThisThatTheseThoseAllLevelsLessonSeeder/definition.json', 'a407893591d8418e33a3120fa3e7786cc3b1d85741f4a28fbbd0009e588e9b65'],
            ['docs/content/m93-sentence-builder-bank-audit.v1.json', 'b7c79624cfa6eaaafc4c74cfd79fb7a2d182a0f830472d4ed0a1d3c6c07281b4'],
            ['database/seeders/V3/Polyglot/PolyglotPronounsTheoryAllLevelsLessonSeeder/definition.json', 'a5292fde8fa57c6c56ac9b1288e4d6a0e89d0266dfbc610e530f76069059b028'],
            ['docs/content/m74-other-pronouns-basics-bank-audit.v1.0.1.json', '0937345b2d3c2b8f7842a0e47d75cd8da268a9ae4e43734aaa8447ce49c78e26'],
            ['database/seeders/V3/Polyglot/PolyglotPersonalObjectPronounsAllLevelsLessonSeeder/definition.json', 'ec53f982d67942fa14ffd0e5322318c919c0f0e3cd8e59f239d173204c22e8a9'],
            ['docs/content/m74-other-pronouns-basics-bank-audit.v1.0.1.json', '0937345b2d3c2b8f7842a0e47d75cd8da268a9ae4e43734aaa8447ce49c78e26'],
            ['database/seeders/V3/Polyglot/PolyglotReciprocalPronounsAllLevelsLessonSeeder/definition.json', '20dc136181e8e64321d5b2f08b87552d46d3f732fdda7244e726d194f9bb8689'],
            ['docs/content/m76-sentence-builder-bank-audit.v1.json', 'efb4bffc246ee8779cef476fd5c0fd956163735d59e4788295d2e19d41a8fffc'],
            ['database/seeders/V3/Polyglot/PolyglotReciprocalPronounsAllLevelsLessonSeeder/definition.json', '20dc136181e8e64321d5b2f08b87552d46d3f732fdda7244e726d194f9bb8689'],
            ['docs/content/m76-sentence-builder-bank-audit.v1.json', 'efb4bffc246ee8779cef476fd5c0fd956163735d59e4788295d2e19d41a8fffc'],
        ],
        'cold' => [
            ['docs/content/m76-before-source.v1.json', '08184ba09866bbf50d7f26a497ffa509c53da2f125f65c33ab7da4cd4854caad'],
            ['docs/content/m76-relative-each-reciprocal.v1.0.1.json', '33fa160344fcc980d8d980aadf19e0db03212b1a54a443100e2f24c3bc033738'],
            ['docs/content/theory-sentence-builders/m76-relative-each-reciprocal.v1.json', '77abdac91ab3fa6eedba9b19ae4ccf241f73c8ac004e874b1255fd6e48419789'],
            ['docs/content/m76-source-checksums.v1.0.1.json', '3a8d147f12436087489fcba478f9fcda6442fdf28c3c4c5b550909b0f64f2e6c'],
            ['docs/content/m75-before-source.v1.json', '93ad3acd46790de520b4ef39d0f16d0b7300e43f77db5b111df8fe2a508c0e97'],
            ['docs/content/m75-possessive-reflexive-indefinite.v1.0.0.json', '817b280b6e59bc60198a717b4b965238fa8d3ebcbc8a5181a8c909687db1f6e9'],
            ['docs/content/theory-sentence-builders/m75-possessive-reflexive-indefinite.v1.json', '3ad762f87af6b32c23df64d1ca3baf02993fdb6ba9760a1d79ae168dccb130a1'],
            ['docs/content/m75-source-checksums.v1.0.0.json', '404c61c18fea10a6ded1d7fc71551e4979325bb9b8c60dedbfaf53406a96b4d8'],
            ['docs/content/m77-before-source.v1.json', 'b30c34fbc22da48bbce8a5bb62d90cbae2a90e6bb2b969192a328bf52d4ed5bc'],
            ['docs/content/m77-one-prepositions-phrasal.v1.json', '57a7f061296e591d75764080e0d3b0c8517aa699e932ac98807cd01617507579'],
            ['docs/content/theory-sentence-builders/m77-one-prepositions-phrasal.v1.json', '7d5a99d1d41d0351f62eebfc2131920bba137ee538a4cd6b27e16bb4cf3b53cf'],
            ['docs/content/m77-source-checksums.v1.json', 'ce5e2ad30a751fd318b21375347a4d61c2746195323233d3d088c1fa8face861'],
            ['docs/content/m77-native-bindings.v1.json', '233a223616a5f35efb27500f60cf295041f01e6637058c96b73889fd464b0d1f'],
            ['docs/content/m74-before-source.v1.json', '9aac299f76f4618af7294b5542010385778110d03e239a576a435c93497a1769'],
            ['docs/content/m74-other-pronouns-basics.v1.0.1.json', '118d754ab5fc36c8e941a285240ec9156b4f214f9d9bd760a231cc26a6697fd2'],
            ['docs/content/theory-sentence-builders/revisions/m74-other-pronouns-basics.v1.0.1.json', '74b1df94815b59ba6c75741d39410cf1a2075c50e21ec42e64387546e7fd7849'],
            ['docs/content/m74-source-checksums.v1.0.1.json', '57fda6d76d2431a6dba9c5a0fac935f5d7666c776d15ce5638a42b850d841479'],
            ['docs/content/m93-before-source.v1.json', 'e0752e23219777b107b18e337f0fe9fbf6965f2f96e792c07bfc1ea71af63812'],
            ['docs/content/m93-future-completion-demonstratives-some-any.v1.json', 'dc735717145312d77d90a71ac9552b2773d3cf63cb3fd2958cdfec8bb42a4f80'],
            ['docs/content/theory-sentence-builders/m93-future-completion-demonstratives-some-any.v1.json', '8a03e3dd1de3a1bed77681ff094251e2991e351988d3decdead2769885890acf'],
            ['docs/content/m93-source-checksums.v1.json', '21f8df95ff83e11b16e0757896a7f7d637dac3213fc66137246d8dafdf54d0c6'],
            ['docs/content/m93-native-bindings.v1.json', '245aa6b8fa2a60b8a3a27ab80ad118306234f047b0091b012d9b335b1b51c801'],
            ['docs/content/m93-navigation-review.v1.json', '8963eca7ec218e8423333ed19afcd3295fd60341cc8395ddd75511490a45e2fb'],
        ],
    ];

    public const TOPICS = ['each-every-all','indefinite-pronouns','one-ones','personal-object-pronouns','possessive-forms','pronouns-overview','reciprocal-pronouns','reflexive-pronouns','relative-pronouns','demonstratives'];

    private static function root(): string { return dirname(__DIR__,2); }
    public static function digest(array $value): string
    {
        return hash('sha256',json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
    }
    public static function specs(): array
    {
        return [
            'each-every-all'=>[M76PronounRelations::class,1,'A2'],
            'indefinite-pronouns'=>[M75PronounForms::class,2,'A2'],
            'one-ones'=>[M77OnePrepositionsPhrasal::class,0,'A2'],
            'personal-object-pronouns'=>[M74OtherPronounsBasics::class,2,'A1'],
            'possessive-forms'=>[M75PronounForms::class,0,'A1'],
            'pronouns-overview'=>[M74OtherPronounsBasics::class,1,'A1'],
            'reciprocal-pronouns'=>[M76PronounRelations::class,2,'A2'],
            'reflexive-pronouns'=>[M75PronounForms::class,1,'A2'],
            'relative-pronouns'=>[M76PronounRelations::class,0,'B1'],
            'demonstratives'=>[M93FutureCompletionDemonstrativesSomeAny::class,2,'A1'],
        ];
    }

    /** Null keeps the entire old gap frame; ordered groups reconstruct old answers exactly. */
    public static function retainedPlans(): array
    {
        return [
            'each-every-all'=>[
                [null,'Назви кожну людину окремо; обидва природні означники перед одниною приймаються.'],
                [null,'Охопи всю вже названу групу людей; прийменник частини цілого можна додати або пропустити.'],
                [null,'Назви регулярність щоранку, не дію протягом цілого ранку.'],
                [null,'Після ціни вкажи, що така сума припадає на кожен окремий блокнот.'],
                ['Each of us|has|a ticket.','Почни з кожного з нашої групи й узгодь дієслово з одниною; квиток є в усіх.'],
                ['All the books|are|on the table.','Почни з усіх відомих книжок, потім назви їхнє місце на столі.'],
            ],
            'indefinite-pronouns'=>[
                [null,'Назви одну невідому людину в ствердженні; обидві рівнозначні однословні форми приймаються.'],
                [null,'За умовою очікується підтвердження: назви невідому річ або подію формою для такого очікування.'],
                [null,'Повідом про відсутність будь-якої людини; приймаються однословний і двослівний займенники.'],
                [null,'Охопи всі названі можливості без винятку, не вільний вибір одного варіанта.'],
                ['Everyone|has|their ticket.','Назви всіх людей займенником однини; власника квитка передай нейтрально щодо статі.'],
                ['Nobody|remembers|the code.','Почни із заперечного займенника й узгодь із ним дієслово пам’яті; другого заперечення не додавай.'],
            ],
            'one-ones'=>[
                ["I don't want|the small ones.",'Збережи заперечення та заміни вже названі коробки словом у множині.'],
                ['Which one|is|yours?','Запитай про одну з названих сумок; самостійна присвійна форма завершує питання.'],
            ],
            'personal-object-pronouns'=>[
                [null,'Назви мовця в ролі підмета; решта речення вже показує щоденну працю.'],
                [null,'Назви жінку як одержувачку повідомлення, а не як підмет; уведи лише займенник.'],
                [null,'Назви мовця разом з іншою людиною в ролі підмета; потрібна множина.'],
                [null,'Назви раніше згаданих кількох людей у ролі додатка; підмет уже є в реченні.'],
                ['Does she|know him?','Побудуй питання про те, чи жінка знає чоловіка; збережи обидві різні ролі учасників.'],
                ['My friend|and I|do not know|them.','Поєднай друга й мовця як підмет; названі інші люди залишаються додатком.'],
            ],
            'possessive-forms'=>[
                [null,'Перед назвою телефону потрібна залежна присвійна форма для мовця.'],
                [null,'Після дієслова потрібна самостійна присвійна форма для мовця; телефон не повторюй.'],
                [null,'Перед будинком назви власника як групу, до якої входить мовець.'],
                [null,'Передай належність нашій групі без повторення назви будинку.'],
                ['This is|my new|blue bag.','Почни з указівки на річ; збережи присвійну форму та порядок двох ознак сумки.'],
                ['I need|a room|of my own.','Почни з мовця й потреби; власну кімнату можна назвати обома прийнятими моделями.'],
            ],
            'pronouns-overview'=>[
                [null,'У пропуску назви самого мовця як підмет; решта повідомлення вже є.'],
                [null,'Після прийменника назви мовця як одержувача предмета; потрібна об’єктна форма.'],
                [null,'Перед уже показаною назвою машини вкажи належність мовцеві.'],
                ['This bag|is mine,|and that notebook|is yours.','Збережи обидві частини: близька сумка належить мовцеві, віддалений зошит — співрозмовникові.'],
                ['We help|each other|with our homework.','Збережи взаємність допомоги в нашій групі та спільне домашнє завдання.'],
            ],
            'reciprocal-pronouns'=>[
                ['We listen to|each other.','Обидва учасники слухають іншого, не себе; потрібна взаємна форма після прийменника.'],
                ["Do they know|each other's names?",'Запитай, чи кожен знає ім’я іншого; потрібна взаємна присвійна форма.'],
            ],
            'reflexive-pronouns'=>[
                [null,'Підкресли, що дію особисто виконав саме мовець; уведи підсилювальний займенник.'],
                [null,'Дія спрямована на ту саму жінку, яка її виконує, не на іншу людину.'],
                [null,'Кілька учасників назвали самих себе; потрібна зворотна форма множини.'],
                [null,'Доповни усталений вислів про добре проведений час нашої групи.'],
                ['I bought|this notebook|for myself.','Назви покупку для самого мовця, не його перебування наодинці.'],
                ['We carried|the sofa|upstairs|by ourselves.','Повідом про спільне перенесення дивана без сторонньої допомоги; обидві лексичні пари приймаються.'],
            ],
            'relative-pronouns'=>[
                [null,'Жінка сама виконала дзвінок; потрібне відносне слово в ролі підмета означальної частини.'],
                [null,'Назви машину як додаток означальної частини; введи допустиме відносне слово, не порожню відповідь.'],
                [null,'Укажи зв’язок собаки з чоловіком; потрібна присвійна відносна форма перед назвою тварини.'],
                [null,'Збережи підмет означальної частини; приймається відносне слово або прямо названа позначка його вилучення.'],
                ['The man|who he called|is my boss.','Зателефонував інший чоловік, а не названий керівник; не вилучай займенник, який виконує дзвінок.'],
                ['I know|the woman|whose new bike|is blue.','Після присвійного відносного слова збережи всю групу про новий велосипед жінки.'],
            ],
            'demonstratives'=>[
                ['Those|are|my keys.','Назви три ключі не поруч із мовцем; потрібні вказівна множина й узгоджене дієслово.'],
                ['I like|these ones.','Збережи дві чашки поруч із мовцем; їхню вже названу назву заміни формою множини.'],
            ],
        ];
    }

    /** New tasks are independent from the mixed banks and all retained lower widgets. */
    public static function additions(): array
    {
        return [
            'each-every-all'=>[
                ['gap','Each of the cyclists {a1} a helmet.','wears','Носить шолом: узгодь особову форму дієслова з кожним окремим велосипедистом.'],
                ['gap','The puppy ate {a1} its food.','all','Увесь корм: охопи повну кількість перед уже наведеною присвійною формою; прийменник частини цілого необов’язковий.',['all of']],
                ['translation','Усі ці квіти потребують води. Почни зі слова для всієї вже названої групи.','All these flowers|need|water.','Збережи вказівку на ці квіти та множину; прийменник перед вказівною групою необов’язковий.',
                    ['All of these flowers need water.']],
                ['translation','Кожна з нас отримала окрему чашку. Почни з кожної учасниці нашої групи.','Each of us|received|a separate cup.','Ужий окреме охоплення всіх учасниць і минулий час; можливі обидві звичайні назви чашки.',
                    ['Each of us received a separate mug.','Every one of us received a separate cup.','Every one of us received a separate mug.'],['cup.'=>['mug.']]],
            ],
            'indefinite-pronouns'=>[
                ['gap','The box is empty. There is {a1} inside.','nothing','Нічого всередині: назви повну відсутність речей одним неозначеним займенником.'],
                ['gap','You may choose {a1} from this list; every person is eligible.','anyone','Будь-кого з переліку: дозвіл поширюється на кожну людину, не лише на вже обрану.',['anybody']],
                ['translation','Хтось залишив парасольку на лавці. Почни з невідомої людини.','Someone|left an umbrella|on the bench.','Передай факт у минулому про невідому людину; обидві парні форми займенника приймаються.',
                    ['Somebody left an umbrella on the bench.'],['Someone'=>['Somebody']]],
                ['translation','Нам потрібно щось тепле. Почни з нашої групи як підмета.','We need|something warm.','Після неозначеного займенника постав ознаку тепла; не змінюй потребу на бажання.'],
            ],
            'one-ones'=>[
                ['gap','The green scarves are cheap, but the red {a1} cost more.','ones','Червоні шарфи: заміни повторену назву предметів займенниковою формою множини.'],
                ['gap',"This plate is cracked. I'll use a different {a1}.",'one','Іншу тарілку замість тріснутої: заміни назву одного злічуваного предмета.'],
                ['translation','Візьми чисті, а не брудні. Ідеться про вже названі тарілки; заміни їхню назву в обох частинах.','Take|the clean ones,|not|the dirty ones.','Побудуй команду, збережи дві визначені групи тарілок і протиставлення.'],
                ['translation','Ми вибрали дешевшу. Ідеться про одну з уже названих ламп; заміни її назву.','We chose|the cheaper one.','Збережи минулий вибір однієї лампи та порівняння за ціною; звичайні синоніми вибору приймаються.',
                    ['We picked the cheaper one.','We selected the cheaper one.'],['chose'=>['picked','selected']]],
            ],
            'personal-object-pronouns'=>[
                ['gap','Oleh and I are ready. Please wait for {a1}.','us','На нас: потрібна об’єктна форма для мовця разом з іншою людиною після прийменника.'],
                ['gap','The children are in the garden. {a1} are playing outside.','They','Вони: раніше згадані діти є підметом наступного повідомлення, не додатком.'],
                ['translation','Вона надіслала нам поштову листівку. Почни з жінки, а одержувачів постав перед назвою речі.','She sent us|a postcard.','Передай минуле надсилання: жінка виконує дію, наша група отримує поштову листівку.',
                    ['She mailed us a postcard.','She posted us a postcard.'],['sent'=>['mailed','posted']]],
                ['translation','Вони часто чекають на мене. Почни з них і збережи регулярність.','They often wait|for me.','Назви групу як підмет, а мовця після прийменника; потрібний простий теперішній час.'],
            ],
            'possessive-forms'=>[
                ['gap','Nina has a yellow coat. The yellow coat is {a1}.','hers','Її: назви Ніну власницею без повторення іменника після присвійної форми.'],
                ['gap','We keep {a1} passports in this drawer.','our','Наші паспорти: перед показаною назвою речей потрібна залежна присвійна форма нашої групи.'],
                ['translation','Ці черевики твої, а рукавички мої. Почни з цих черевиків і поєднай частини сполучником додавання.','These boots|are yours,|and the gloves|are mine.','Власників передай двома самостійними формами; рукавички вже відомі співрозмовникам.',
                    ['These shoes are yours, and the gloves are mine.'],['boots'=>['shoes']]],
                ['translation','Їхня квартира над нашою. Обидві квартири вже відомі; почни з їхньої.','Their flat|is|above ours.','Перед першою квартирою назви власника залежною формою, другу квартиру не повторюй.',
                    ['Their apartment is above ours.','Their flat is over ours.','Their apartment is over ours.'],['flat'=>['apartment'],'above'=>['over']]],
            ],
            'pronouns-overview'=>[
                ['gap','My sister and I cycle to school. {a1} always wear helmets.','We','Ми: повернися до мовця разом із сестрою підметовою формою, не повторюй імена.'],
                ['gap','This helmet belongs to Danylo. It is {a1}.','his','Його: назви встановленого власника-чоловіка самостійною присвійною формою.'],
                ['translation','Віддай ці ключі йому. Почни з дії, а одержувача постав у кінці.','Give these keys|to him.','Назви вже визначені близькі ключі та чоловіка-одержувача після прийменника.',
                    ['Hand these keys to him.'],['Give'=>['Hand']]],
                ['translation','Вона дала нам свій номер. Почни з жінки; вона є власницею номера.','She gave us|her number.','Збережи різні ролі: жінка дає власний номер, наша група його отримує.'],
            ],
            'reciprocal-pronouns'=>[
                ['gap','The two teams congratulated {a1} after the match.','each other','Одне одного: кожна команда привітала іншу, а не сама себе.',['one another']],
                ['gap','We read {a1} notes before the meeting.',"each other's",'Нотатки одне одного: кожен читав записи іншого учасника, а не власні. Потрібна взаємна присвійна форма.',["one another's"]],
                ['translation','Сусіди позичають одне одному інструменти. Почни з уже відомих сусідів.','The neighbours|lend each other|tools.','Сусіди дають речі в тимчасове користування іншим сусідам; збережи взаємність.',
                    ['The neighbors lend each other tools.','The neighbours lend one another tools.','The neighbors lend one another tools.'],['neighbours'=>['neighbors'],'each other'=>['one another']]],
                ['translation','Чому ви уникаєте одне одного? Йдеться про двох співрозмовників; почни з питального слова причини.','Why do you|avoid each other?','Побудуй пряме питання про причину взаємного уникання в теперішньому часі.',
                    ['Why do you avoid one another?'],['each other'=>['one another']]],
            ],
            'reflexive-pronouns'=>[
                ['gap','Please help {a1} to some soup, Olena.','yourself','Пригощайся супом: звертаються лише до однієї Олени; доповни усталений зворот зворотною формою.'],
                ['gap','The kitten looked at {a1} in the window.','itself','На власне відображення: кошеня побачило саме себе, а його стать не уточнено.'],
                ['translation','Вона сама навчилася пекти хліб. Передай самонавчання конструкцією зі зворотним займенником.','She taught herself|to bake bread.','Жінка навчала саму себе; після цього назви вміння випікати хліб.',
                    ['She taught herself how to bake bread.']],
                ['translation','Ми розважили себе грою. Почни з нашої групи.','We entertained ourselves|with a game.','Виконавці й ті, кого розважили, збігаються; збережи минулий час та засіб розваги.',
                    ['We amused ourselves with a game.'],['entertained'=>['amused']]],
            ],
            'relative-pronouns'=>[
                ['gap','The engineer {a1} repaired the lift works nearby.','who','Який полагодив ліфт: інженер є виконавцем дії означальної частини; обидві дозволені підметові форми приймаються.',['that']],
                ['gap','Is this the artist {a1} paintings we saw?','whose','Чиї картини: потрібна присвійна відносна форма; у пропуску не повторюй назву картин.'],
                ['translation','Мені подобається лист, який ти написала. Почни з мовця та використай простий теперішній час для вподобання.','I like|the letter|that you wrote.','Лист є додатком означальної частини; можливе відносне слово для речі або його природне вилучення.',
                    ['I like the letter which you wrote.','I like the letter you wrote.'],['that'=>['which']]],
                ['translation','Пекар, чий хліб ми купуємо, живе поруч. Почни з уже відомого пекаря.','The baker|whose bread|we buy|lives nearby.','Присвійне відносне слово пов’язує хліб із пекарем; наша група купує цей хліб.',
                    ['The baker whose bread we buy lives near here.'],['nearby.'=>['near here.']]],
            ],
            'demonstratives'=>[
                ['gap','{a1} apple in my hand is red.','This','Це яблуко: один предмет у руці мовця потребує вказівної форми однини для близького.'],
                ['gap','{a1} trees on the far side of the river are tall.','Those','Ті дерева: названо кілька предметів далеко від мовця; потрібна вказівна множина.'],
                ['translation','Ця ковдра тепла. Мовець тримає одну ковдру в руках.','This blanket|is|warm.','Вкажи на один близький предмет і узгодь із ним дієслово.'],
                ['translation','Ті велосипеди нові. Вони далеко від мовця.','Those bicycles|are|new.','Назви віддалені предмети в множині; обидві звичайні назви велосипеда приймаються.',
                    ['Those bikes are new.'],['bicycles'=>['bikes']]],
            ],
        ];
    }

    public static function editingHints(): array
    {
        return [
            'each-every-all'=>[1=>'Перед прийменниковою групою використай форму для кожної окремої книги або розгорнуту форму з назвою одного учасника.',
                2=>'Усі студенти є множинним підметом: зміни лише форму дієслова, не склад групи.',
                3=>'Збережи книги в множині; слово про кожного постав у дозволену позицію після підмета або перебудуй підмет.'],
            'indefinite-pronouns'=>[1=>'Початковий займенник уже задано: узгодь із ним однину дієслова й збережи стан готовності.',
                2=>'Ознаку цікавості постав після неозначеного займенника, не перед ним.',
                3=>'Передай одне стандартне заперечення в минулому: заперечне дієслово з відповідним займенником або стверджувальне дієслово із заперечним займенником.'],
            'one-ones'=>[],
            'personal-object-pronouns'=>[1=>'Сестра є підметом, тому заміни об’єктну форму жіночого займенника підметовою.',
                2=>'Після прийменника назви мовця об’єктною формою; саму команду не змінюй.',
                3=>'Названа група студентів є підметом; потрібна підметова форма множини.'],
            'possessive-forms'=>[1=>'Після дієслова без назви речі потрібна самостійна присвійна форма мовця.',
                2=>'Перед назвою машини потрібна залежна присвійна форма для встановленої власниці.',
                3=>'Вибери запис зі скороченням займенника й дієслова, не залежну присвійну форму; уведи лише позначку речення.'],
            'pronouns-overview'=>[1=>'Сидінь кілька: вибери речення з узгодженою вказівною множиною, а не одниною.',
                2=>'Наша група є одержувачем розкладу після дієслова надсилання; потрібна об’єктна форма, не підмет.'],
            'reciprocal-pronouns'=>[0=>'Задані двоє учасників зустріли іншого, не себе; збережи взаємний займенник та минулий час.',
                1=>'Обери запис із присвійним оформленням взаємної конструкції; уведи позначку речення, не готовий англійський текст.',
                2=>'Слово після дієслова розуміння має бути прислівником; обох учасників і взаємний займенник збережи.',
                3=>'За умовою учні допомогли іншим учасникам у відповідь одне одному; потрібна взаємна, не зворотна форма.',
                4=>'Є лише один чоловік: він звертається до себе; не додавай іншого учасника й не змінюй підмет.'],
            'reflexive-pronouns'=>[1=>'Заміни діалектний запис стандартною зворотною формою чоловічої однини; решту речення збережи.',
                2=>'В усталеному вислові про задоволення потрібна стандартна зворотна форма множини.',
                3=>'Для звичайної домовленої зустрічі вилучи зайвий зворотний додаток; час п’ятої години збережи.'],
            'relative-pronouns'=>[1=>'До встановленого брата додають відомість: заміни лише відносне слово на форму для людини в додатковій частині.',
                2=>'За прямою умовою заміни відносне слово для тварини однією з двох заданих альтернатив; решту речення збережи.',
                3=>'Перед машиною потрібна присвійна відносна форма, не скорочення займенника з дієсловом.'],
            'demonstratives'=>[],
        ];
    }

    private static function rewrittenOverview(): array
    {
        return [
            1=>['original'=>'Мовець указує на кілька місць, відведених для своєї групи. Потрібний зміст: «Це наші місця». А: This are our seats. Б: These are our seats. У якому реченні узгоджено вказівне слово з множиною? Напиши «речення А» або «речення Б».',
                'placeholder'=>'Назви речення','answer'=>'речення Б','accepted'=>['речення Б','Б'],
                'feedback'=>['речення б'=>'These are our seats. — Це наші місця. These узгоджується з множинним seats та are; this позначає однину.',
                    'б'=>'These are our seats. — Це наші місця. У реченні Б вказівне слово має потрібну множину.']],
            2=>['original'=>'Мовець просить надіслати розклад йому та його групі. Виправ лише займенник одержувача, не змінюй порядок слів: Please send we the timetable. Потрібний зміст: «Будь ласка, надішли нам розклад».',
                'placeholder'=>'Виправ повне речення','answer'=>'Please send us the timetable.','accepted'=>['Please send us the timetable.'],
                'feedback'=>['please send us the timetable.'=>'Please send us the timetable. — Будь ласка, надішли нам розклад. Після send одержувачем є наша група: потрібне us, не підметове we.']],
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
        $out=[];foreach($groups as $group){$values=$aliases[$group]??[];
            if($values===[]){foreach($aliases as $word=>$synonyms){$p='/(?<![A-Za-z])'.preg_quote($word,'/').'(?![A-Za-z])/u';
                if(preg_match_all($p,$group)!==1){continue;}foreach($synonyms as $synonym){$values[]=preg_replace($p,$synonym,$group,1);}}}
            $values=array_values(array_unique(array_filter($values,static fn($v)=>is_string($v)&&$v!==$group)));
            if($values!==[]){$out[$group]=$group.' ('.implode(' / ',$values).')';}}
        return $out;
    }

    /** Previously correct lower-widget questions keep their complete source and progress identity. */
    private static function widgetBindings(): array
    {
        $plans=[33131=>[M74BuilderSelectionPresentation::class,'prepared',['made']],
            33133=>[M74BuilderSelectionPresentation::class,'desk',['table']],
            51184=>[M74BuilderSelectionPresentation::class,'leads',['chairs','runs']],
            51187=>[M74BuilderSelectionPresentation::class,'help',['assistance']],
            33332=>[M93BuilderSelectionPresentation::class,'free',['available','vacant']],
            33128=>[M74BuilderSelectionPresentation::class,'office',[]],
            51189=>[M74BuilderSelectionPresentation::class,'sit',[]],
            33843=>[M76BuilderSelectionPresentation::class,'write',[]],
            33847=>[M76BuilderSelectionPresentation::class,'looked',[]]];$out=[];
        $orders=[
            33131=>['Marta prepared the report by herself at school',
                ['Marta prepared the report at school by herself','Marta made the report at school by herself']],
            51187=>['We thanked her for the help today',
                ['We thanked her today for the help','We thanked her today for the assistance']],
            33128=>['Their office is upstairs at school',['Their office at school is upstairs']],
            51189=>['Please sit next to them today',['Please sit today next to them']],
            33843=>['The friends often write to each other',['The friends write to each other often']],
            33847=>['The sisters looked at each other across the table',['The sisters looked across the table at each other']],
        ];
        foreach($plans as $id=>[$presenter,$word,$aliases]){
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
            $accepted=[implode(' ',$tokens)];foreach($aliases as $alias){$alternate=$tokens;$alternate[$where[0]]=$alias;$accepted[]=implode(' ',$alternate);}
            $orderAliases=[];
            if(isset($orders[$id])){
                if(implode(' ',$tokens)!==$orders[$id][0]){throw new RuntimeException('Exact natural adjunct source frame differs.');}
                $orderAliases=$orders[$id][1];$accepted=array_merge($accepted,$orderAliases);
            }
            $out[]=['id'=>$id,'uuid'=>$sample['uuid'],'seeder'=>$bank,'type'=>'4','level'=>$rows[0]['level'],
                'block_uuid'=>$audit['practice_bindings'][$i]['uuid'],'question'=>$sample['visible_condition_uk'],
                'source_question'=>$sample['displayed_condition_uk'],'correct_tokens'=>$tokens,
                'original_ordered_instances'=>$sample['original_ordered_instances']??null,
                'ordered_answer_snapshot_sha256'=>$sample['snapshot_hashes']['ordered_answer_snapshot']??null,
                'canonical_answers'=>array_combine(array_keys($rows[0]['answers']),$sample['raw_correct_tokens']??array_values($rows[0]['answers'])),
                'accepted_sentences'=>$accepted,'added_options'=>$aliases,
                'lexical_aliases'=>$aliases!==[]?[$word=>$aliases]:[],'order_aliases'=>$orderAliases,
                'token_labels'=>$aliases!==[]?[$word=>$word.' ('.implode(' / ',$aliases).')']:[],'definition'=>$definition,
                'definition_sha256'=>hash_file('sha256',self::root().'/'.$definition),'audit'=>$presenter::AUDIT,'audit_sha256'=>$presenter::AUDIT_SHA];
        }
        return $out;
    }

    public static function compile(): array
    {
        $document=['schema_version'=>1,'package'=>'pronouns-demonstratives-native-practice-supplement','status'=>'frozen','targets'=>[]];$releases=[];
        foreach(self::specs() as $topic=>[$release,$i,$focus]){
            if(!isset($releases[$release])){$releases[$release]=$release::load(self::root());$release::validate(...$releases[$release]);}
            [,$patch]=$releases[$release];$target=$patch['targets'][$i];$slot=$release::PRACTICE_SOURCE_INDICES[$i];$stored=$target['after']['page']['blocks'][$slot];
            $binding=['seeder'=>$target['identity'],'page_id'=>$release::PAGE_IDS[$i],'page_category_id'=>20,
                'uuid'=>M26DetailPackage::uuid($target['identity'],$stored,$slot+1),'locale'=>'uk','type'=>'practice-set',
                'column'=>'left','sort_order'=>$slot+1,'level'=>$release::LEVELS[$i],'heading'=>null,'css_class'=>null];
            $block=(object)($binding+['body'=>$stored['body']]);$raw=json_decode($stored['body'],true,flags:JSON_THROW_ON_ERROR);
            $presentation=$release::presentation($block,$raw);
            if(!is_array($presentation['data']??null)){throw new RuntimeException('Retained native presentation missing.');}
            $before=$presentation['data'];$after=$before;$updates=[];$tokenLabels=[];$rewritten=[];
            if($topic==='indefinite-pronouns'){
                $old=$after['inputs'][5];$replacement=$old;
                $replacement['answer']='Nobody remembers the code.';
                $replacement['accepted']=['Nobody remembers the code.','No one remembers the code.','No-one remembers the code.',
                    'Nobody recalls the code.','No one recalls the code.','No-one recalls the code.'];
                $replacement['after']='Ніхто не пам’ятає коду. Почни із заперечного займенника, збережи теперішній час. Уручну приймаються однословна й двослівна форми для «ніхто» та обидва природні дієслова пам’яті.';
                $replacement['feedback']=array_fill_keys(array_map('mb_strtolower',$replacement['accepted']),
                    'Правильно. Ніхто не пам’ятає коду. Заперечний займенник потребує форми дієслова однини; додаткове заперечення не потрібне.');
                $after['inputs'][5]=$replacement;$rewritten[]=['group'=>'inputs','index'=>5,'before'=>$old,'after'=>$replacement];
                $tokenLabels[5]=['Nobody'=>'Nobody (No one / No-one)','remembers'=>'remembers (recalls)'];
            }
            if(count($before['inputs'])!==count(self::retainedPlans()[$topic])){throw new RuntimeException('Retained native input slots differ.');}
            foreach(self::retainedPlans()[$topic] as $index=>[$ordered,$hint]){
                self::uk($hint);$old=$after['inputs'][$index];$new=$old;$new['verb_hint']=$hint;
                if($ordered===null){$new['practice_kind']='gap';$updates[]=['index'=>$index,'kind'=>'gap','hint_uk'=>$hint];}
                else{$groups=explode('|',$ordered);self::groups($groups,$old['answer']);$shuffled=self::shuffled($groups,$topic.':retained:'.$index);
                    $new['before']=implode(' / ',$shuffled);$new['practice_kind']='order';
                    $updates[]=['index'=>$index,'kind'=>'order','ordered_groups'=>$groups,'shuffled_groups'=>$shuffled,'hint_uk'=>$hint];}
                $new['after']=($new['after']??'').' Підказка: '.$hint;$after['inputs'][$index]=$new;
            }
            if($topic==='pronouns-overview'){
                foreach(self::rewrittenOverview() as $index=>$replacement){$old=$after['rephrase'][$index];
                    foreach(['id','source_index'] as $key){if(array_key_exists($key,$old)){$replacement[$key]=$old[$key];}}
                    $after['rephrase'][$index]=$replacement;$rewritten[]=['group'=>'rephrase','index'=>$index,'before'=>$old,'after'=>$replacement];}
            }
            $editing=[];$editingPlans=self::editingHints()[$topic];
            foreach($after['rephrase']??[] as $index=>$item){if(!is_string($item['answer']??null)){continue;}
                if(!isset($editingPlans[$index])){throw new RuntimeException('Exact contextual editing hint missing.');}
                $hint=$editingPlans[$index];self::uk($hint);$after['rephrase'][$index]['verb_hint']=$hint;
                $after['rephrase'][$index]['original'].=' Підказка: '.$hint;$editing[]=$index;}
            if(array_keys($editingPlans)!==$editing){throw new RuntimeException('Unexpected contextual editing hint slot.');}
            $added=[];
            foreach(self::additions()[$topic] as $n=>$row){
                [$kind,$question,$answer,$hint]=$row;self::uk($hint);$id='pdq-native-'.$topic.'-'.($kind==='gap'?'gap':'translate').'-'.($n%2+1);
                if($kind==='translation'){self::uk($question);$groups=explode('|',$answer);$answer=implode(' ',$groups);self::groups($groups,$answer);
                    $shuffled=self::shuffled($groups,$id);$visible=implode(' / ',$shuffled);$afterText=$question.' Підказка: '.$hint;
                }else{if(substr_count($question,'{a1}')!==1){throw new RuntimeException('Native gap needs one phrase slot.');}
                    $visible=str_replace('{a1}','____',$question);$afterText=$hint;$groups=[];$shuffled=[];}
                $accepted=array_values(array_unique(array_merge([$answer],$row[4]??[])));$index=count($after['inputs']);
                if($kind==='gap'&&count($accepted)>1){$afterText.=' Допустимі варіанти: ('.implode(' / ',$accepted).').';}
                $after['inputs'][]=['id'=>$id,'practice_kind'=>$kind,'before'=>$visible,'after'=>$afterText,'verb_hint'=>$hint,'answer'=>$answer,'accepted'=>$accepted,
                    'feedback'=>array_fill_keys(array_map('mb_strtolower',$accepted),'Правильно. '.$hint)];
                $labels=self::labels($groups,$row[5]??[]);if($labels!==[]){$tokenLabels[$index]=$labels;}
                $added[]=['id'=>$id,'index'=>$index,'kind'=>$kind,'focus_level'=>$focus,'condition'=>$question,'answer'=>$answer,
                    'accepted'=>$accepted,'hint_uk'=>$hint,'ordered_groups'=>$groups,'shuffled_groups'=>$shuffled];
            }
            $oldCount=count($before['inputs']);$after['input_title']='Вправа 3. Заповни пропуски та склади речення';
            $after['input_intro']='Спочатку виконай початкові '.$oldCount.' '.($oldCount===2?'завдання':'завдань').' за їхніми умовами. Далі заповни два пропуски й переклади два українські речення. Повні речення вводь вручну або складай із перемішаних груп.';
            $after['pronouns_demonstratives_practice_supplement']=['revision'=>'v1','topic'=>$topic];
            foreach($rewritten as &$rewrittenItem){$rewrittenItem['after']=$after[$rewrittenItem['group']][$rewrittenItem['index']];}unset($rewrittenItem);
            $document['targets'][]=['topic'=>$topic,'definition'=>$target['path'],'definition_sha256'=>hash_file('sha256',self::root().'/'.$target['path']),
                'release'=>$release::SOURCE,'release_sha256'=>$release::SOURCE_SHA,'focus_level'=>$focus,
                'binding'=>$binding+['body_sha256'=>hash('sha256',$stored['body'])],
                'before_data'=>$before,'before_data_sha256'=>self::digest($before),'after_data'=>$after,'after_data_sha256'=>self::digest($after),
                'input_updates'=>$updates,'editing_hint_indices'=>$editing,'rewritten'=>$rewritten,'added'=>$added,'input_token_labels'=>$tokenLabels];
        }
        $document['widgets']=self::widgetBindings();return $document;
    }
    private static function checkedSourcePath(): string
    {
        $path=self::root().'/'.self::SOURCE;
        if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path))){throw new RuntimeException('Native supplement bytes differ.');}
        return $path;
    }

    private static function loadChecked(string $path): array
    {
        static $verified=null;
        if($verified===null){$verified=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);self::validate($verified);}
        foreach($verified['targets'] as $target){foreach(['definition','release'] as $kind){$file=self::root().'/'.$target[$kind];
            if(!is_file($file)||is_link($file)||!hash_equals($target[$kind.'_sha256'],hash_file('sha256',$file))){throw new RuntimeException('Native source pin differs.');}}}
        foreach($verified['widgets'] as $widget){foreach(['definition','audit'] as $kind){$file=self::root().'/'.$widget[$kind];
            if(!is_file($file)||is_link($file)||!hash_equals($widget[$kind.'_sha256'],hash_file('sha256',$file))){throw new RuntimeException('Widget lexical source pin differs.');}}}
        return $verified;
    }

    public static function load(): array
    {
        return self::loadChecked(self::checkedSourcePath());
    }

    private static function checkDependencies(array $pins, bool $alsoApplication = false): bool
    {
        foreach ($pins as [$relative, $sha]) {
            $file = self::root().'/'.$relative;
            if (!is_file($file) || is_link($file) || !hash_equals($sha, hash_file('sha256', $file))) {
                return false;
            }
            // The native release also uses the application root for its presenter.
            if ($alsoApplication) {
                $applicationFile = base_path($relative);
                if (str_replace('\\', '/', $applicationFile) !== str_replace('\\', '/', $file)
                    && (!is_file($applicationFile) || is_link($applicationFile) || !hash_equals($sha, hash_file('sha256', $applicationFile)))) {
                    return false;
                }
            }
        }

        return true;
    }

    private static function canSkipWidget(object $question, ?string $blockUuid, array $payload): bool
    {
        if (get_class($question) !== \App\Models\Question::class || $blockUuid === null
            || !preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/', $blockUuid)) {
            return false;
        }
        $id = $question->id;
        $uuid = $question->uuid;
        $seeder = $question->seeder;
        if (!is_int($id) || $id <= 0 || ($payload['id'] ?? null) !== $id
            || !is_string($uuid) || $uuid === '' || !is_string($seeder) || $seeder === '') {
            return false;
        }
        $index = self::WIDGET_MEMBERSHIP;
        if ($index['source_sha'] !== self::SOURCE_SHA || isset($index['pairs'][$blockUuid])
            || in_array($uuid, $index['widgetUuids'], true) || in_array($seeder, $index['bankSeeders'], true)) {
            return false;
        }
        foreach ($index['pairs'] as $ids) {
            if (in_array($id, $ids, true)) {
                return false;
            }
        }
        $bytes = json_encode($index, JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
        if (!is_string($bytes) || !hash_equals(self::WIDGET_MEMBERSHIP_SHA, hash('sha256', $bytes))) {
            return false;
        }

        // Keep ordered repeated pins (including duplicates). For a split source/
        // application root, also validate the artifacts used by the presenters.
        // No state is published to the owned loader. On drift, use its old path.
        return self::checkDependencies($index['mandatory'], true)
            && self::checkDependencies($index['cold'], true);
    }
    public static function validate(array $document): void
    {
        if($document!==self::compile()){throw new RuntimeException('Native finite teaching projection differs.');}
    }
    private static function entry(object $block,array $data,bool $projected): ?array
    {
        if(app()->getLocale()!=='uk'||($block->locale??null)!=='uk'||($block->type??null)!=='practice-set'
            ||!str_starts_with($block->seeder??'','Database\\Seeders\\Page_V3\\PronounsDemonstratives\\')){return null;}
        try{foreach(self::load()['targets'] as $target){foreach($target['binding'] as $field=>$value){if($field==='body_sha256'){continue;}
            if(($block->$field??null)!==$value){continue 2;}}
            if(is_string($block->body??null)&&hash_equals($target['binding']['body_sha256'],hash('sha256',$block->body))
                &&$data===$target[$projected?'after_data':'before_data']){return $target;}
        }}catch(Throwable){/* Foreign or changed sources keep the whole original native practice. */}return null;
    }
    public static function project(object $block,array $data,bool $canonicalCaller): array
    {
        return $canonicalCaller?(self::entry($block,$data,false)['after_data']??$data):$data;
    }
    public static function isProjected(object $block,array $data): bool { return self::entry($block,$data,true)!==null; }
    public static function originalDataForDisplay(object $block,array $data): array { return self::entry($block,$data,true)['before_data']??$data; }
    public static function inputTokenLabels(object $block,array $data): array { return self::entry($block,$data,true)['input_token_labels']??[]; }

    /** Exact source-bound aliases only. No database mutation or progress/storage revision. */
    public static function projectWidget(object $question,?string $blockUuid,array $payload,bool $canonicalCaller=false): array
    {
        if(!$canonicalCaller||app()->getLocale()!=='uk'||($payload['type']??null)!=='4'||(string)($question->type??'')!=='4'){return $payload;}
        try{
            $path=self::checkedSourcePath();
            if(self::canSkipWidget($question,$blockUuid,$payload)){return $payload;}
            foreach(self::loadChecked($path)['widgets'] as $binding){
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
            if($loaded->count()!==count($binding['canonical_answers'])||count($payload['answers']??[])!==$loaded->count()){return $payload;}
            if(is_array($binding['original_ordered_instances'])){
                foreach($binding['original_ordered_instances'] as $i=>$answer){$actual=$loaded[$i];$shown=$payload['answers'][$i];
                    if(method_exists($actual,'relationLoaded')&&!$actual->relationLoaded('option')){return $payload;}
                    if(($actual->question_id??null)!==$binding['id']||($actual->id??null)!==$answer['id']||($actual->marker??null)!==$answer['marker']||($actual->option_id??null)!==$answer['option_id']
                        ||($actual->option->id??null)!==$answer['option_id']
                        ||($actual->option->option??null)!==$answer['option']||($shown['marker']??null)!==$answer['marker']
                        ||($shown['correct']??null)!==$answer['option']){return $payload;}}
            }else{
                $snapshot=[];
                foreach($loaded as $i=>$answer){if(method_exists($answer,'relationLoaded')&&!$answer->relationLoaded('option')){return $payload;}
                    $option=$answer->option??null;$shown=$payload['answers'][$i];
                    if(($answer->question_id??null)!==$binding['id']||($option->id??null)!==($answer->option_id??null)
                        ||($binding['canonical_answers'][$answer->marker??'']??null)!==($option->option??null)
                        ||($shown['marker']??null)!==($answer->marker??null)||($shown['correct']??null)!==($option->option??null)){return $payload;}
                    $snapshot[]=['id'=>$answer->id,'marker'=>$answer->marker,'position'=>$answer->position,'option_id'=>$answer->option_id,'option'=>$option->option];}
                if(!is_string($binding['ordered_answer_snapshot_sha256'])||!hash_equals($binding['ordered_answer_snapshot_sha256'],self::digest($snapshot))){return $payload;}
            }
            $payload['accepted_sentences']=$binding['accepted_sentences'];
            $payload['options']=array_values(array_unique(array_merge($payload['options'],$binding['added_options'])));
            $payload['derived_compose_options']=array_values(array_unique(array_merge($payload['derived_compose_options']??[],$binding['added_options'])));
            $payload['token_labels']=array_replace($payload['token_labels']??[],$binding['token_labels']);
            $payload['pdq_widget_alias_revision']=self::digest($binding);return $payload;
        }}catch(Throwable){/* Foreign, changed or unloaded sources retain the original whole payload. */}return $payload;
    }
}
