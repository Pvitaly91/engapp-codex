<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Finite teaching data only. Native views, frozen releases and stored progress stay intact. */
final class AdjectivesAdverbsPracticeSupplement
{
    public const SOURCE = 'docs/content/adjectives-adverbs-practice-supplement.v1.json';
    public const SOURCE_SHA = '6fcbdac90c36fa9ed631922961b4aa8f50914d0028f36c627dbbfe94d81387a5';
    public const WIDGET_BINDINGS = 'docs/content/adjectives-adverbs-widget-bindings.v1.json';
    public const WIDGET_BINDINGS_SHA = '34ebdc83cbdac391658954f27fa20bb2fd91a252aa6c09fed5aa474014b43843';
    public const TOPICS = ['degrees', 'comparative', 'equality', 'adverbs', 'order', 'eding', 'intensifiers'];

    private static function root(): string { return dirname(__DIR__, 2); }
    public static function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
    public static function specs(): array
    {
        return [
            'degrees' => [M68ComparisonBasics::class, 0, 9],
            'comparative' => [M68ComparisonBasics::class, 1, 11],
            'equality' => [M68ComparisonBasics::class, 2, 12],
            'adverbs' => [M69AdjectivesAdverbs::class, 0, 11],
            'order' => [M69AdjectivesAdverbs::class, 1, 6],
            'eding' => [M69AdjectivesAdverbs::class, 2, 6],
            'intensifiers' => [M70IntensifiersNounBasics::class, 0, 6],
        ];
    }
    /** Ordered groups, short condition, contextual hint; all old answers remain source-owned. */
    public static function retained(): array
    {
        return [
            'degrees' => [
                ['This exercise|is easier|than that one.', 'Ця вправа легша за ту. Почни з назви цієї вправи.', 'Порівняй легкість двох вправ; використай усі подані групи, не замінюючи вищий ступінь найвищим.'],
                ['Is the museum|farther than|the bank?', 'Чи музей далі, ніж банк? Порівняй відстані від тієї самої точки.', 'Побудуй пряме питання про відстань до двох уже відомих місць; обидві стандартні форми слова «далі» приймаються.'],
            ],
            'comparative' => [
                ['The green suitcase|is lighter than|the blue one.', 'Зелена валіза легша за синю. Почни із зеленої валізи.', 'Порівняй тільки дві названі валізи; другу назву предмета можна повторити або замінити займенником.'],
                ['Is Route C|the longest|of the|three routes?', 'Названий маршрут найдовший із трьох маршрутів? Постав пряме питання.', 'Запитай про крайній показник у всій трійці, не про порівняння лише з одним іншим маршрутом.'],
            ],
            'equality' => [
                ['The new bag|is not|as expensive as|the old bag.', 'Нова сумка не така дорога, як стара сумка. Почни з нової сумки.', 'Збережи заперечене порівняння рівності й повну другу назву сумки; не замінюй його окремим вищим ступенем.'],
                ['Marta has|as many books|as Danylo.', 'У Марти стільки само книжок, скільки в Данила. Почни з Марти.', 'Порівняй кількість злічуваних предметів; наприкінці дозволено повторити допоміжне або дієслово володіння.'],
            ],
            'adverbs' => [
                ['Does Mark|drive carefully?', 'Марк водить обережно? Постав пряме питання про звичне водіння.', 'Потрібен прислівник способу виконання дії; після питального допоміжного основна дія не має особового закінчення.'],
                ['Lina checked|the labels|carefully yesterday.', 'Учора Ліна уважно перевірила етикетки. Почни з Ліни, час постав наприкінці.', 'Збережи завершену минулу перевірку; прислівник можна поставити перед дією або після її додатка.'],
            ],
            'order' => [
                ['We need|a small|wooden shelf.', 'Нам потрібна маленька дерев’яна полиця. Почни зі «нам потрібна».', 'У нейтральній групі спочатку назви розмір, потім матеріал; використай усі подані слова.'],
                ['Did they buy|two red|racing bikes?', 'Вони купили два червоні гоночні велосипеди? Запитай про минуле.', 'Збережи кількість і колір перед цілісною назвою типу велосипеда; після допоміжного дія має початкову форму.'],
            ],
            'eding' => [
                ['The instructions|were confusing.', 'Інструкції було важко зрозуміти. Опиши їх як джерело труднощів у минулому.', 'Використай прикметник із поданого банку для джерела враження, не для почуттів людини; минулий час збережи.'],
                ['Is Danylo|interested in|art?', 'Данило цікавиться мистецтвом? Постав пряме питання про теперішній стан.', 'Збережи подану прикметникову конструкцію зацікавлення з прийменником; не замінюй її іншим основним дієсловом.'],
            ],
            'intensifiers' => [
                ['This bag|is too heavy|for me.', 'Ця сумка для мене надто важка. Почни із цієї сумки.', 'Передай перевищення прийнятної ваги для мовця, не просто достатню вагу; використай усі подані групи.'],
                ['Do we|have enough|chairs?', 'У нас достатньо стільців? Постав пряме питання.', 'Запитай про достатню кількість злічуваних предметів; слово достатності постав перед назвою предметів.'],
            ],
        ];
    }
    /** Two independent phrase gaps and two full translations for each exact lesson. */
    public static function additions(): array
    {
        return [
            'degrees' => [
                ['gap', 'The red suitcase is ____ than the grey suitcase.', 'heavier', 'Потрібен вищий ступінь ознаки ваги: передай більшу вагу, не найбільшу.'],
                ['gap', 'This is the ____ room in the hostel.', 'quietest', 'Назви найменший рівень шуму серед усіх кімнат; означник уже показано.', ['most quiet']],
                ['translation', 'Нова стежка вужча за стару. Почни з нової стежки.', 'The new path|is narrower than|the old one.', 'Порівняй ширину двох уже відомих стежок; обидві стандартні форми вищого ступеня й звичні назви стежки приймаються.', ['The new path is more narrow than the old one.', 'The new path is narrower than the old path.', 'The new path is more narrow than the old path.', 'The new trail is narrower than the old one.', 'The new trail is more narrow than the old one.', 'The new trail is narrower than the old trail.', 'The new trail is more narrow than the old trail.'], ['path' => ['trail'], 'narrower' => ['more narrow']]],
                ['translation', 'Це найгірший день цього тижня. Почни зі «це».', 'This is|the worst day|of the week.', 'Виділи найгірший день у межах уже названого тижня; потрібна неправильна найвища форма.'],
            ],
            'comparative' => [
                ['gap', 'Of the two scarves, this is the ____ one.', 'softer', 'У визначеній парі порівняй м’якість; потрібен вищий ступінь, не найвищий.'],
                ['gap', 'Of all four lifts, this one is ____.', 'the fastest', 'Назви найбільшу швидкість серед усіх чотирьох; введи всю групу з означником.'],
                ['translation', 'Мій другий ескіз кращий за перший. Почни з мого другого ескізу.', 'My second sketch|is better than|the first.', 'Зістав якість другого ескізу з першим; назву ескізу можна повторити або опустити в другій частині.', ['My second sketch is better than the first sketch.', 'My second drawing is better than the first.', 'My second drawing is better than the first drawing.'], ['sketch' => ['drawing']]],
                ['translation', 'Це найсухіша кімната в підвалі. Почни зі «це».', 'This is|the driest room|in the basement.', 'Виділи кімнату з найменшою вологістю серед підвальних кімнат; звичні назви підвалу приймаються.', ['This is the driest room in the cellar.'], ['basement.' => ['cellar.']]],
            ],
            'equality' => [
                ['gap', 'The blue cord is as ____ as the white cord.', 'thick', 'Товщина однакова; між двома словами рівності потрібна початкова ознака товщини.'],
                ['gap', 'They carried as ____ crates as we did.', 'many', 'Порівнюємо однакове число окремих злічуваних ящиків; введи слово кількості.'],
                ['translation', 'Цей рушник такий самий м’який, як той. Почни із цього рушника.', 'This towel|is as soft|as that one.', 'Порівняй однакову м’якість двох рушників; другу назву предмета можна повторити або замінити займенником.', ['This towel is as soft as that towel.']],
                ['translation', 'Сьогодні вітер не такий сильний, як учора. Почни зі «сьогодні».', 'Today the wind|is not|as strong as|yesterday.', 'Збережи заперечене порівняння сили вітру; повний і скорочений варіанти заперечення приймаються.', ["Today the wind isn't as strong as yesterday.", 'Today the wind is not so strong as yesterday.', "Today the wind isn't so strong as yesterday.", 'Today the wind is not as strong as it was yesterday.', "Today the wind isn't as strong as it was yesterday."]],
            ],
            'adverbs' => [
                ['gap', 'The courier arrived ____.', 'early', 'Передай ранній час прибуття, протилежний запізненню; тут потрібен прислівник часу.'],
                ['gap', 'The soup tastes ____.', 'delicious', 'Опиши приємний смак прикметником, а не спосіб куштування; обидва звичні відповідники «смачний» приймаються.', ['tasty']],
                ['translation', 'Софія ввічливо відповіла. Почни з імені.', 'Sofia|replied politely.', 'Передай завершену минулу відповідь і ввічливий спосіб; звичні дієслова відповіді та обидві позиції прислівника приймаються.', ['Sofia answered politely.', 'Sofia responded politely.', 'Sofia politely replied.', 'Sofia politely answered.', 'Sofia politely responded.'], ['replied' => ['answered', 'responded']]],
                ['translation', 'Ця тканина на дотик гладенька. Почни із цієї тканини.', 'This fabric|feels smooth.', 'Опиши тактильну властивість через дієслово відчуття й прикметник; обидві звичні назви тканини приймаються.', ['This cloth feels smooth.'], ['fabric' => ['cloth']]],
            ],
            'order' => [
                ['gap', 'We need a ____ box.', 'small metal', 'Уведи дві ознаки: маленький розмір перед металевим матеріалом; назва предмета вже показана.', ['little metal']],
                ['gap', 'He wears an ____ watch.', 'old Swiss', 'Уведи вік «старий» перед швейцарським походженням; артикль і назва предмета вже показані.'],
                ['translation', 'Вона знайшла красиву круглу срібну брошку. Почни з «вона».', 'She found|a beautiful round|silver brooch.', 'Описуємо вперше згадану брошку: оцінка перед формою й матеріалом; звичні відповідники краси приймаються.', ['She found a pretty round silver brooch.', 'She found a lovely round silver brooch.'], ['beautiful' => ['pretty', 'lovely']]],
                ['translation', 'Вони замовили три великі чорні дорожні сумки. Почни з «вони».', 'They ordered|three large black|travel bags.', 'Збережи кількість, розмір і колір перед цілісною назвою дорожніх сумок; обидва звичні слова великого розміру приймаються.', ['They ordered three big black travel bags.'], ['large' => ['big']]],
            ],
            'eding' => [
                ['gap', 'The long wait was ____.', 'boring', 'Передай спричинення нудьги, а не її відчуття людиною; звичний синонім «нудний» також приймається.', ['tedious']],
                ['gap', 'I was ____ by the sudden noise.', 'frightened', 'Передай власний переляк від раптового шуму; потрібен стан людини, не властивість шуму.', ['scared']],
                ['translation', 'Нас розчарувала кінцівка. Почни з «ми» та опиши наш стан у минулому.', 'We were|disappointed by|the ending.', 'Опиши розчарування людей через прикметниковий стан; обидва природні прийменники до причини розчарування приймаються.', ['We were disappointed with the ending.'], ['by' => ['with']]],
                ['translation', 'Сюжет був захопливим. Почни з уже відомого сюжету.', 'The plot|was gripping.', 'Описуємо сюжет як джерело захоплення в минулому; звичні прикметникові відповідники приймаються.', ['The plot was exciting.', 'The plot was engaging.', 'The plot was thrilling.'], ['gripping.' => ['exciting.', 'engaging.', 'thrilling.']]],
            ],
            'intensifiers' => [
                ['gap', 'The kettle is ____ to touch.', 'too hot', 'Висока температура робить дотик небезпечним; введи всю групу надмірної температури, не лише підсилювач.'],
                ['gap', 'We have ____ for the picnic.', 'enough food', 'Їжі вистачає для пікніка; введи цілу групу достатньої кількості, не лише означник.', ['sufficient food']],
                ['translation', 'Ця дошка надто широка для полиці. Почни із цієї дошки.', 'This board|is too wide|for the shelf.', 'Ширина дошки перевищує прийнятну для полиці межу; обидві звичні назви дошки приймаються.', ['This plank is too wide for the shelf.'], ['board' => ['plank']]],
                ['translation', 'Ми маємо достатньо ковдр для гостей. Почни з «ми».', 'We have|enough blankets|for the guests.', 'Постав слово достатності перед назвою ковдр; звичні способи вираження володіння та синонім достатності приймаються.', ['We have sufficient blankets for the guests.', "We've got enough blankets for the guests.", 'We have got enough blankets for the guests.', "We've got sufficient blankets for the guests.", 'We have got sufficient blankets for the guests.'], ['enough' => ['sufficient']]],
            ],
        ];
    }
    /** Full English targets/stimuli retained in the old form/meaning tasks, for cross-bank duplicate protection. */
    public static function retainedFormMeaningTargets(): array
    {
        return [
            'm68-degrees-form-1' => ['Mia’s bag is bigger than mine.'],
            'm68-degrees-form-2' => ['This is the most comfortable chair in the set.'],
            'm68-degrees-meaning-1' => ['My car is more reliable than yours.'],
            'm68-degrees-meaning-2' => ['Box A is not bigger than box B.'],
            'm68-comparative-form-1' => ['Ticket A is cheaper than Ticket B.'],
            'm68-comparative-form-2' => ['Ticket A is the cheapest of the three tickets.'],
            'm68-comparative-meaning-1' => ['Route A is shorter than Route B.'],
            'm68-comparative-meaning-2' => ['Nina has one of the highest scores in the game.'],
            'm68-equality-form-1' => ['Olia packs as carefully as her sister.'],
            'm68-equality-form-2' => ['Marta has as much free time as Danylo.'],
            'm68-equality-meaning-1' => ['Today it is not as cold as yesterday.'],
            'm68-equality-meaning-2' => ['The red ribbon is three times as long as the blue ribbon.'],
            'm69-adverbs-form-1' => ['Olena looks tired.'],
            'm69-adverbs-form-2' => ['The children waved happily.'],
            'm69-adverbs-meaning-1' => ['He hardly ever calls.'],
            'm69-adverbs-meaning-2' => ['The hall is very quiet.'],
            'm69-order-form-1' => ['We found an old brass lamp at the market.'],
            'm69-order-form-2' => ['They packed two blue sleeping bags.'],
            'm69-order-meaning-1' => ['a cold, damp cellar', 'a small stone cottage'],
            'm69-order-meaning-2' => ['a lightweight blue raincoat'],
            'm69-eding-form-1' => ['Marta feels tired after cleaning the room.'],
            'm69-eding-form-2' => ['The new teacher is interesting.'],
            'm69-eding-meaning-1' => ['I am boring.'],
            'm69-eding-meaning-2' => ['Olia is not bored.'],
            'm70-int-form-1' => ['The box is big enough for these books.'],
            'm70-int-form-2' => ['We have enough time to finish.'],
            'm70-int-meaning-1' => ['The bag is too heavy for Olia.'],
            'm70-int-meaning-2' => ['The ticket is not too expensive.'],
        ];
    }
    public static function shuffled(array $groups, string $seed): array
    {
        $rows = [];
        foreach ($groups as $i => $text) { $rows[] = ['text' => $text, 'key' => hash('sha256', $seed.'|'.$i.'|'.$text)]; }
        usort($rows, static fn ($a, $b) => strcmp($a['key'], $b['key']));
        $out = array_column($rows, 'text');
        if ($out === $groups) { $out[] = array_shift($out); }
        return $out;
    }
    private static function plainUk(string $text): void
    {
        if (trim($text) === '' || !preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $text)
            || preg_match('/[A-Za-z]|<[^>]+>|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu', $text)) {
            throw new RuntimeException('Adjectives/adverbs teaching condition or hint must be plain Ukrainian.');
        }
    }
    private static function groups(string $ordered, string $answer): array
    {
        $groups = explode('|', $ordered);
        if (count($groups) < 2 || implode(' ', $groups) !== $answer) { throw new RuntimeException('Native groups must reconstruct the whole sentence.'); }
        foreach ($groups as $group) {
            if (trim($group) !== $group || $group === '' || str_contains($group, '/') || count(preg_split('/\s+/u', $group)) > 3) {
                throw new RuntimeException('Native group must contain one to three words.');
            }
        }
        return $groups;
    }
    private static function labels(array $groups, array $aliases): array
    {
        $out = [];
        foreach ($groups as $group) {
            $values = $aliases[$group] ?? [];
            if ($values === []) {
                foreach ($aliases as $word => $synonyms) {
                    $pattern = '/(?<![A-Za-z])'.preg_quote($word, '/').'(?![A-Za-z])/u';
                    if (preg_match($pattern, $group) !== 1) { continue; }
                    foreach ($synonyms as $synonym) { $values[] = preg_replace($pattern, $synonym, $group, 1); }
                }
            }
            if ($values !== []) { $out[$group] = $group.' ('.implode(' / ', array_unique($values)).')'; }
        }
        return $out;
    }
    public static function compile(): array
    {
        $out = ['schema_version' => 1, 'package' => 'adjectives-adverbs-native-practice-quality', 'status' => 'frozen', 'targets' => []];
        $releases = [];
        foreach (self::specs() as $topic => [$release, $i, $slot]) {
            if (!isset($releases[$release])) { $releases[$release] = $release::load(self::root()); $release::validate(...$releases[$release]); }
            [, $patch] = $releases[$release]; $target = $patch['targets'][$i]; $stored = $target['after']['page']['blocks'][$slot];
            $binding = ['seeder' => $target['identity'], 'page_id' => $release::PAGE_IDS[$i], 'page_category_id' => $release::CATEGORY_IDS[$i],
                'uuid' => M26DetailPackage::uuid($target['identity'], $stored, $slot + 1), 'locale' => 'uk', 'type' => 'practice-set',
                'column' => 'left', 'sort_order' => $slot + 1, 'level' => $release::LEVELS[$i], 'heading' => null, 'css_class' => null];
            $block = (object) ($binding + ['body' => $stored['body']]);
            $presentation = $release::presentation($block, json_decode($stored['body'], true, flags: JSON_THROW_ON_ERROR));
            if (!is_array($presentation['data'] ?? null)) { throw new RuntimeException('Exact accepted native presentation missing.'); }
            $before = $presentation['data']; $after = $before; $updates = []; $tokenLabels = [];
            if (count($before['inputs'] ?? []) !== 2 || count($before['selects'] ?? []) !== 2 || count($before['choices'] ?? []) !== 2) {
                throw new RuntimeException('Seven lessons must retain their six exact old tasks.');
            }
            foreach (self::retained()[$topic] as $index => [$ordered, $condition, $hint]) {
                self::plainUk($condition); self::plainUk($hint);
                $groups = self::groups($ordered, $after['inputs'][$index]['answer']);
                $after['inputs'][$index]['before'] = implode(' / ', self::shuffled($groups, 'aa-native:'.$topic.':'.$index));
                $after['inputs'][$index]['after'] = $condition; $after['inputs'][$index]['verb_hint'] = $hint;
                $after['inputs'][$index]['practice_kind'] = 'order';
                $updates[] = ['index' => $index, 'kind' => 'order', 'condition_uk' => $condition, 'hint_uk' => $hint, 'ordered_groups' => $groups];
                if ($topic === 'degrees' && $index === 1) { $tokenLabels[$index] = self::labels($groups, ['farther' => ['further']]); }
            }
            $added = [];
            foreach (self::additions()[$topic] as $n => $row) {
                [$kind, $condition, $ordered, $hint] = $row; self::plainUk($hint);
                $id = 'aa-native-'.$topic.'-'.$kind.'-'.($n % 2 + 1); $groups = [];
                if ($kind === 'translation') {
                    self::plainUk($condition); $answer = str_replace('|', ' ', $ordered); $groups = self::groups($ordered, $answer);
                    $beforeText = implode(' / ', self::shuffled($groups, $id)); $afterText = $condition;
                } else {
                    if (substr_count($condition, '____') !== 1) { throw new RuntimeException('Native gap needs one complete phrase field.'); }
                    $beforeText = $condition; $afterText = $hint; $answer = $ordered;
                }
                $accepted = array_values(array_unique([$answer, ...($row[4] ?? [])]));
                if ($kind === 'gap' && count($accepted) > 1) { $afterText .= ' Приймаються: ('.implode(' / ', $accepted).').'; }
                $index = count($after['inputs']);
                $after['inputs'][] = ['id' => $id, 'practice_kind' => $kind, 'before' => $beforeText, 'after' => $afterText,
                    'verb_hint' => $hint, 'answer' => $answer, 'accepted' => $accepted,
                    'feedback' => array_fill_keys(array_map('mb_strtolower', $accepted), 'Правильно. '.$hint)];
                $labels = self::labels($groups, $row[5] ?? []);
                if ($labels !== []) { $tokenLabels[$index] = $labels; }
                $added[] = ['id' => $id, 'index' => $index, 'kind' => $kind, 'condition' => $condition, 'hint_uk' => $hint,
                    'answer' => $answer, 'accepted' => $accepted, 'ordered_groups' => $groups];
            }
            $after['input_title'] = 'Вправа 3. Доповни й побудуй речення';
            $after['input_intro'] = 'Заповнюй лише пропуски; повні речення вводь вручну або складай із груп слів.';
            $after['adjectives_adverbs_practice_quality'] = ['revision' => 'v1', 'topic' => $topic];
            $out['targets'][] = ['topic' => $topic, 'definition' => $target['path'],
                'definition_sha256' => hash_file('sha256', self::root().'/'.$target['path']),
                'release' => $release::SOURCE, 'release_sha256' => $release::SOURCE_SHA,
                'binding' => $binding + ['body_sha256' => hash('sha256', $stored['body'])],
                'local_url' => 'http://gramlyze.loc/theory/'.implode('/', [...$release::ANCESTRIES[$i], $release::SLUGS[$i]]),
                'before_data' => $before, 'before_data_sha256' => self::digest($before),
                'after_data' => $after, 'after_data_sha256' => self::digest($after),
                'input_updates' => $updates, 'added' => $added, 'input_token_labels' => $tokenLabels];
        }
        return $out;
    }
    public static function load(): array
    {
        $path = self::root().'/'.self::SOURCE;
        if (!is_file($path) || is_link($path) || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $path))) { throw new RuntimeException('Adjectives/adverbs native source bytes differ.'); }
        static $data = null;
        if ($data === null) { $data = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR); self::validate($data); }
        foreach ($data['targets'] as $target) { foreach (['definition', 'release'] as $kind) {
            $file = self::root().'/'.$target[$kind];
            if (!is_file($file) || is_link($file) || !hash_equals($target[$kind.'_sha256'], hash_file('sha256', $file))) { throw new RuntimeException('Pinned native source differs.'); }
        } }
        return $data;
    }
    public static function validate(array $data): void
    {
        if ($data !== self::compile()) { throw new RuntimeException('Adjectives/adverbs finite native projection differs.'); }
    }
    /** Explicit short teaching conditions; no runtime truncation or sentence splitting. */
    public static function displayPlans(): array
    {
        return [
            'degrees' => [
                'selects' => [
                    ['Порівняй розмір сумок; утвори вищий ступінь.', [['uk', 'Сумка Мії більша за мою.'], ['en', 'Mia’s bag is ____ than mine.']]],
                    ['Назви найвищий ступінь зручності; означник уже показано.', [['uk', 'Це найзручніше з трьох крісел у наборі.'], ['en', 'This is the ____ chair in the set.']]],
                ],
                'choices' => [
                    ['Визнач, які два предмети порівнюємо за надійністю.', [['en', 'My car is more reliable than yours.']]],
                    ['Перевір відповідність речення місткості коробок.', [['uk', 'Коробка A вміщує 4 л, коробка B — теж 4 л.'], ['en', 'Box A is not bigger than box B.']]],
                ],
            ],
            'comparative' => [
                'selects' => [
                    ['Порівняй лише ціни перших двох квитків.', [['uk', 'Ціни квитків: A — 60 грн, B — 90 грн, C — 120 грн.'], ['en', 'Ticket A is ____ than Ticket B.']]],
                    ['Назви квиток із найнижчою ціною серед усіх трьох.', [['uk', 'Ціни квитків: A — 60 грн, B — 90 грн, C — 120 грн.'], ['en', 'Ticket A is ____ of the three tickets.']]],
                ],
                'choices' => [
                    ['Обери лише гарантовано підтверджений висновок.', [['uk', 'У наборі три маршрути: A — 4 км, B — 6 км; довжина C невідома.'], ['en', 'A) Route A is shorter than Route B.'], ['en', 'B) Route A is the shortest of the three routes.']]],
                    ['Збережи нічию на найвищому результаті.', [['uk', 'У грі три учасники: Ніна — 9 балів, Ірина — 9, Макс — 6.'], ['en', 'A) Nina has one of the highest scores in the game.'], ['en', 'B) Nina has a higher score than every other player.']]],
                ],
            ],
            'equality' => [
                'selects' => [
                    ['Порівняй виконання дії, не властивість людини.', [['uk', 'Оля та її сестра пакують речі однаково обережно.'], ['en', 'Olia packs as ____ as her sister.']]],
                    ['Порівняй незлічувану кількість вільного часу.', [['uk', 'У неділю в Марти й Данила по дві вільні години.'], ['en', 'Marta has as ____ free time as Danylo.']]],
                ],
                'choices' => [
                    ['Порівняй холод за наведеними температурами.', [['uk', 'Учора було 2 °C, сьогодні 8 °C.'], ['en', 'Today it is not as cold as yesterday.']]],
                    ['Визнач довжину червоної стрічки за порівнянням.', [['uk', 'Синя стрічка має довжину 10 см.'], ['en', 'The red ribbon is three times as long as the blue ribbon.']]],
                ],
            ],
            'adverbs' => [
                'selects' => [
                    ['Опиши втомлений вигляд, не спосіб дивитися на предмет.', [['uk', 'Олена має втомлений вигляд.'], ['en', 'Olena looks ____.']]],
                    ['Опиши спосіб махання прислівником від слова «щасливий».', [['uk', 'Діти радісно помахали рукою.'], ['en', 'The children waved ____.']]],
                ],
                'choices' => [
                    ['Визнач, що повідомлено про частоту дзвінків.', [['en', 'He hardly ever calls.']]],
                    ['Визнач, яке слово підсилює прислівник міри.', [['en', 'The hall is very quiet.']]],
                ],
            ],
            'order' => [
                'selects' => [
                    ['Збережи правильний артикль і незмінні прикметники.', [['uk', 'На ринку ми знайшли стару латунну лампу.'], ['en', 'We found ____ at the market.']]],
                    ['Збережи цілісну назву типу предмета.', [['uk', 'Вони спакували два сині спальники.'], ['en', 'They packed ____.']]],
                ],
                'choices' => [
                    ['Відрізни рівноправні ознаки від уточнення типу будинку.', [['en', 'A) a cold, damp cellar'], ['en', 'B) a small stone cottage']]],
                    ['Обери опис малої ваги, не світлого відтінку синього.', [['en', 'A) a lightweight blue raincoat'], ['en', 'B) a light blue raincoat']]],
                ],
            ],
            'eding' => [
                'selects' => [
                    ['Опиши втому людини, не враження від неї.', [['uk', 'Марта відчуває втому після прибирання кімнати.'], ['en', 'Marta feels ____ after cleaning the room.']]],
                    ['Опиши джерело цікавого враження, не власне зацікавлення.', [['uk', 'Розповіді нової вчительки зацікавлюють слухачів.'], ['en', 'The new teacher is ____.']]],
                ],
                'choices' => [
                    ['Визнач, що вигаданий персонаж повідомляє про себе.', [['en', 'I am boring.']]],
                    ['Не додавай почуття, якого в реченні немає.', [['en', 'Olia is not bored.']]],
                ],
            ],
            'intensifiers' => [
                'selects' => [
                    ['Передай достатній розмір, не надмірний.', [['uk', 'Потрібне значення «достатньо великий».'], ['en', 'The box is ____ for these books.']]],
                    ['Передай достатню кількість часу.', [['uk', 'Потрібне значення «достатньо часу».'], ['en', 'We have ____ to finish.']]],
                ],
                'choices' => [
                    ['Визнач, чи перевищено прийнятну для людини межу.', [['uk', 'Оля може безпечно нести до 8 кг; сумка важить 12 кг.'], ['en', 'The bag is too heavy for Olia.']]],
                    ['Обери лише те, що випливає з речення.', [['uk', 'Відомостей про чийсь бюджет немає.'], ['en', 'The ticket is not too expensive.']]],
                ],
            ],
        ];
    }
    public static function compileCompact(): array
    {
        $out = ['schema_version' => 1, 'source' => ['path' => self::SOURCE, 'sha256' => self::SOURCE_SHA], 'targets' => []];
        foreach (self::load()['targets'] as $target) {
            $items = []; $groups = []; $data = $target['after_data']; $topic = $target['topic'];
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $kind) {
                $items[$kind] = []; $buckets = [];
                foreach ($data[$kind] ?? [] as $index => $item) {
                    if ($kind === 'inputs') {
                        $bucket = $item['practice_kind'];
                        $instruction = match ($bucket) { 'order' => 'Склади речення з поданих груп слів.', 'translation' => 'Переклади повне речення.', 'gap' => 'Уведи лише всю пропущену частину.' };
                        $stimuli = [['lang' => 'uk', 'text' => $item['after']]];
                        if ($bucket === 'gap') { $stimuli[] = ['lang' => 'en', 'text' => $item['before']]; }
                        elseif ($item['verb_hint'] !== $item['after']) { $stimuli[] = ['lang' => 'uk', 'text' => $item['verb_hint']]; }
                    } else {
                        $plan = self::displayPlans()[$topic][$kind][$index] ?? null;
                        if ($plan === null) { throw new RuntimeException('Finite native display teaching plan missing.'); }
                        [$instruction, $rows] = $plan; $stimuli = [];
                        foreach ($rows as [$lang, $text]) { $stimuli[] = ['lang' => $lang, 'text' => $text]; }
                        $bucket = $kind;
                    }
                    $items[$kind][] = ['index' => $index, 'source_item_sha256' => self::digest($item), 'instruction_uk' => $instruction, 'stimuli' => $stimuli];
                    $buckets[$bucket][] = $index;
                }
                foreach ($buckets as $bucket => $indices) {
                    if (count($indices) !== 2) { throw new RuntimeException('Every native exercise must contain its two reviewed tasks.'); }
                    $groups[] = ['key' => 'aa-'.$topic.'-'.$bucket, 'kind' => $kind, 'indices' => $indices,
                        'title' => match ($bucket) { 'selects' => 'Обери форму', 'choices' => 'Перевір зміст', 'order' => 'Розташуй слова', 'gap' => 'Заповни пропуски', 'translation' => 'Переклади речення' },
                        'instruction' => match ($bucket) { 'selects' => 'Обери один варіант.', 'choices' => 'Збережи зміст і всі факти умови.', 'order' => 'Використай усі групи слів або введи речення вручну.', 'gap' => 'Уведи тільки відсутню частину.', 'translation' => 'Введи переклад або скористайся токенами.' }];
                }
            }
            $out['targets'][] = ['topic' => $topic, 'binding' => $target['binding'], 'after_data_sha256' => $target['after_data_sha256'], 'items' => $items, 'groups' => $groups];
        }
        return $out;
    }
    private static function entry(object $block, array $data, bool $projected): ?array
    {
        if (app()->getLocale() !== 'uk' || ($block->locale ?? null) !== 'uk' || ($block->type ?? null) !== 'practice-set'
            || !str_starts_with($block->seeder ?? '', 'Database\\Seeders\\Page_V3\\Adjectives\\')) { return null; }
        try {
            foreach (self::load()['targets'] as $target) {
                foreach ($target['binding'] as $field => $expected) {
                    if ($field !== 'body_sha256' && ($block->$field ?? null) !== $expected) { continue 2; }
                }
                if (is_string($block->body ?? null) && hash_equals($target['binding']['body_sha256'], hash('sha256', $block->body))
                    && $data === $target[$projected ? 'after_data' : 'before_data']) { return $target; }
            }
        } catch (Throwable) { /* Changed or foreign content keeps its complete original data. */ }
        return null;
    }
    public static function project(object $block, array $data, bool $canonicalCaller): array { return $canonicalCaller ? (self::entry($block, $data, false)['after_data'] ?? $data) : $data; }
    public static function isProjected(object $block, array $data): bool { return self::entry($block, $data, true) !== null; }
    public static function originalDataForDisplay(object $block, array $data): array { return self::entry($block, $data, true)['before_data'] ?? $data; }
    public static function inputTokenLabels(object $block, array $data): array { return self::entry($block, $data, true)['input_token_labels'] ?? []; }

    /** Independent lexical review for the existing thirty-five canonical lower-widget questions. */
    public static function widgetPlans(): array
    {
        return [
            16251 => ['Виділи найуважнішу ученицю в межах нашого класу; обидва звичні слова уважності приймаються.', ['Sofia is the most attentive student in our class'], ['careful' => ['attentive']]],
            16253 => ['Зістав теперішню легкість завдання з першим враженням; звичні назви завдання та відповідники «здавалося» приймаються.', ['This task is easier than it seemed at first', 'This exercise is easier than it looked at first', 'This exercise is easier than it seemed at first'], ['task' => ['exercise'], 'looked' => ['seemed']]],
            16255 => ['Порівняй безпечність нового маршруту з автострадою; обидві звичні назви автостради приймаються.', ['His new route is safer than the highway'], ['motorway' => ['highway']]],
            16256 => ['Порівняй минулий приїзд із попереднім очікуванням; обидва звичні дієслова прибуття приймаються.', ['She came earlier than I expected', 'She arrived earlier than I had expected'], ['arrived' => ['came']]],
            16258 => ['Порівняй математичні здібності сестри з власними; британська, американська й повна назви предмета приймаються.', ['My sister is better at math than I am', 'My sister is better at mathematics than I am', 'My sister is better at maths than me', 'My sister is better at math than me', 'My sister is better at mathematics than me'], ['maths' => ['math', 'mathematics']]],
            2640 => ['Порівняй саме цю книжку з уже відомим фільмом; обидві звичні назви фільму приймаються.', ['This book is more interesting than the movie'], ['film' => ['movie']]],
            2641 => ['Виділи найбільшу впевненість серед усієї групи; звичний складений прикметник упевненості теж приймається.', ['She is the most self-assured student in the group'], ['confident' => ['self-assured']]],
            2642 => ['Порівняй ясність другого вже написаного листа з першим; лист може бути звичайним або електронним.', ['Your second letter was clearer than the first', 'Your second email was more clear than the first', 'Your second letter was more clear than the first'], ['email' => ['letter'], 'clearer' => ['more clear']]],
            2647 => ['Назви минулий вибір найдешевшого доступного варіанта; звичні дієслова вибору й назви варіанта приймаються.', ['He selected the cheapest option available', 'He picked the cheapest option available', 'He chose the cheapest choice available', 'He selected the cheapest choice available', 'He picked the cheapest choice available', 'He chose the cheapest available option'], ['chose' => ['selected', 'picked'], 'option' => ['choice']]],
            2649 => ['Розповідаємо про минулий найкращий матч у межах сезону; звична назва спортивної гри також приймається.', ['That was the best game of the season', 'It was the best match of the season', 'It was the best game of the season'], ['match' => ['game'], 'That' => ['It']]],
            2712 => ['Порівняй звичну однакову швидкість бігу; обидва звичні прислівники швидкості приймаються.', ['She runs as fast as her brother', 'She runs as quickly as her brother does', 'She runs as fast as her brother does'], ['quickly' => ['fast']]],
            2713 => ['Теперішня складність нижча за попереднє враження; збережи заперечену модель рівності, а не окремий вищий ступінь.', ['This task is not as hard as it looked', 'This task is not as difficult as it seemed', 'This task is not as hard as it seemed', 'This exercise is not as difficult as it looked', 'This exercise is not as hard as it seemed', 'This exercise is not as hard as it looked', 'This exercise is not as difficult as it seemed'], ['task' => ['exercise'], 'difficult' => ['hard'], 'looked' => ['seemed']]],
            2714 => ['Порівняй чіткість минулого мовлення з мовленням гіда; звичний відповідник виразного мовлення також приймається.', ['He spoke as distinctly as the guide', 'He spoke as clearly as the guide did', 'He spoke as distinctly as the guide did'], ['clearly' => ['distinctly']]],
            2718 => ['Порівняй однакову точність двох пояснень; останній займенник означає її пояснення, не саму людину.', ['Your explanation is as accurate as hers', 'Your explanation is as exact as hers'], ['precise' => ['accurate', 'exact']]],
            2720 => ['Порівняй лише рівень шуму двох офісів; заперечення не означає гіршої якості офісу.', ['Our office is not as loud as theirs'], ['noisy' => ['loud']]],
            2496 => ['Опиши звичну гру на інструменті та її якість; прислівник способу постав після назви інструмента.', ['She plays piano well']],
            2497 => ['Опиши людину через її водійську професію й обережність; обидва звичні прикметники обережності приймаються.', ['He is a cautious driver'], ['careful' => ['cautious']]],
            2502 => ['Передай завершене розв’язання головоломки без труднощів; звичні відповідники легкого виконання також приймаються.', ['He easily solved the puzzle', 'He solved the puzzle effortlessly', 'He solved the puzzle with ease'], ['easily' => ['effortlessly', 'with ease']]],
            2504 => ['Збережи минулу старанну працю протягом усього дня; не підмінюй докладені зусилля майже повною відсутністю дії.', ['They worked diligently all day', 'They worked hard the whole day', 'They worked hard all day long'], ['hard' => ['diligently']]],
            2505 => ['Передай одну завершену подію пізнього прибуття; обидва звичні дієслова приходу приймаються.', ['We came late'], ['arrived' => ['came']]],
            2797 => ['Дай короткий нейтральний опис першозгаданого готелю: оцінка, вік, походження; звичні відповідники чарівності приймаються.', ['We stayed in a lovely old Italian hotel', 'We stayed at a charming old Italian hotel', 'We stayed at a lovely old Italian hotel'], ['charming' => ['lovely'], 'in' => ['at']]],
            2802 => ['Дай нейтральний опис першозгаданого стільця: зручність, мала висота, матеріал; розмовний відповідник зручності теж приймається.', ['She chose a comfy low wooden chair'], ['comfortable' => ['comfy']]],
            2803 => ['Описуємо відкриття першозгаданого закладу: оцінка перед розміром і типом харчування; звичні синоніми модності й розміру приймаються.', ['They opened a fashionable small vegan restaurant', 'They opened a trendy little vegan restaurant', 'They opened a fashionable little vegan restaurant'], ['trendy' => ['fashionable'], 'small' => ['little']]],
            2804 => ['Дай нейтральний опис першозгаданого пальта: оцінка, довжина, матеріал; обидва стандартні написання вовняного матеріалу приймаються.', ['He wore an elegant long woolen coat', 'He wore a stylish long woollen coat', 'He wore a stylish long woolen coat'], ['woollen' => ['woolen'], 'elegant' => ['stylish']]],
            2807 => ['Дай нейтральний опис першозгаданої парасолі: компактність, колір, спосіб складання; звичні відповідники складної конструкції приймаються.', ['She carried a compact black collapsible umbrella'], ['folding' => ['collapsible']]],
            2856 => ['Передай спантеличений вигляд у минулому й причину стану; звичні відповідники спантеличення приймаються.', ['She looked puzzled by the question', 'She looked bewildered by the question', 'She appeared confused by the question', 'She appeared puzzled by the question', 'She appeared bewildered by the question', 'She looked confused about the question'], ['confused' => ['puzzled', 'bewildered'], 'looked' => ['appeared']]],
            2857 => ['Опиши запитання як минуле джерело труднощів розуміння; не приписуй предмету почуттів людини.', ['The question was puzzling', 'The question was perplexing'], ['confusing' => ['puzzling', 'perplexing']]],
            2859 => ['Опиши сам результат у минулому як джерело подиву; звичні відповідники дивовижності приймаються.', ['The result was astonishing', 'The result was incredible', 'The result was stunning'], ['amazing' => ['astonishing', 'incredible', 'stunning']]],
            2860 => ['Передай стан розчарування після вже відомої затримки; не перетворюй його на властивість людини розчаровувати інших.', ['He was disappointed after the delay']],
            2862 => ['Передай задоволення якістю вже відомого сервісу; звичні прикметники задоволеного стану приймаються.', ['They were pleased with the service', 'They were content with the service', 'They were happy with the service'], ['satisfied' => ['pleased', 'content', 'happy']]],
            2943 => ['Передай справжню корисність для початківців, не надзвичайну міру; обидва способи вказати на відомий інструмент приймаються.', ['This tool is really useful for beginners', 'The tool is truly useful for beginners', 'The tool is really helpful for beginners', 'This tool is really helpful for beginners', 'The tool is really useful for novices', 'The tool is truly helpful for beginners', 'The tool is truly useful for novices', 'The tool is really helpful for novices', 'The tool is truly helpful for novices', 'This tool is truly useful for beginners', 'This tool is truly helpful for beginners', 'This tool is really useful for novices', 'This tool is truly useful for novices', 'This tool is really helpful for novices', 'This tool is truly helpful for novices'], ['The' => ['This'], 'really' => ['truly'], 'useful' => ['helpful'], 'beginners' => ['novices']]],
            2946 => ['Передай перевищення прийнятного шуму для роботи; не просто високу міру. Збережи кімнату як підмет.', ['The room was too loud to work in'], ['noisy' => ['loud']]],
            2947 => ['Потужності вистачало для цього пагорба; постав слово достатності після ознаки. Обидві звичні назви двигуна приймаються.', ['The motor was powerful enough for the hill'], ['engine' => ['motor']]],
            2948 => ['Передай глибину минулого розчарування рішенням, не повноту чи помірність; природні відповідники глибини та прийменники причини приймаються.', ['She felt profoundly disappointed by the decision', 'She felt deeply disappointed with the decision', 'She felt deeply disappointed at the decision', 'She felt deeply disappointed about the decision', 'She felt profoundly disappointed with the decision', 'She felt profoundly disappointed at the decision', 'She felt profoundly disappointed about the decision'], ['deeply' => ['profoundly'], 'by' => ['with', 'at', 'about']]],
            2949 => ['Передай надзвичайний холод гірського повітря; час постав наприкінці. Звичні відповідники надзвичайної міри й світанку приймаються.', ['The mountain air was exceedingly cold at dawn', 'The mountain air was exceptionally cold at dawn', 'The mountain air was extremely cold at daybreak', 'The mountain air was exceedingly cold at daybreak', 'The mountain air was exceptionally cold at daybreak'], ['extremely' => ['exceedingly', 'exceptionally'], 'dawn' => ['daybreak']]],
        ];
    }
    public static function compileWidgetBindings(array $capture): array
    {
        if (($capture['schema_version'] ?? null) !== 1 || count($capture['targets'] ?? []) !== 7 || count($capture['questions'] ?? []) !== 35) {
            throw new RuntimeException('Complete canonical local widget capture required.');
        }
        $presenters = [M68BuilderSelectionPresentation::class, M69BuilderSelectionPresentation::class, M70BuilderSelectionPresentation::class];
        $audits = []; foreach ($presenters as $presenter) { $audits[] = $presenter::loadAudit(self::root()); }
        $originals = array_column($capture['questions'], null, 'id'); $capturedTargets = array_column($capture['targets'], null, 'topic');
        $native = array_column(self::load()['targets'], null, 'topic');
        $out = ['schema_version' => 1, 'package' => 'adjectives-adverbs-native-widget-bindings', 'targets' => [], 'questions' => []];
        foreach (self::specs() as $topic => [$release, $i]) {
            $auditIndex = $release === M68ComparisonBasics::class ? 0 : ($release === M69AdjectivesAdverbs::class ? 1 : 2);
            $audit = $audits[$auditIndex]; $reviewed = $audit['targets'][$i]; $captured = $capturedTargets[$topic] ?? [];
            if (($captured['selected_ids'] ?? null) !== $reviewed['expected_selected_ids'] || ($captured['block_uuid'] ?? null) !== $native[$topic]['binding']['uuid']) {
                throw new RuntimeException('Canonical widget capture differs from the frozen curated five.');
            }
            $out['targets'][] = ['topic' => $topic, 'identity' => $reviewed['identity'], 'block_uuid' => $captured['block_uuid'],
                'local_url' => $reviewed['local_url'], 'level' => $reviewed['level'], 'bank_seeders' => $reviewed['bank_seeders'],
                'selected_ids' => $reviewed['expected_selected_ids'], 'audit' => $presenters[$auditIndex]::AUDIT, 'audit_sha256' => $presenters[$auditIndex]::AUDIT_SHA];
            foreach ($reviewed['first_five'] as $sample) {
                $id = $sample['id']; $row = $originals[$id] ?? null; $plan = self::widgetPlans()[$id] ?? null;
                if ($row === null || $plan === null || ($row['canonical_server_payload']['question'] ?? null) !== $sample['visible_condition_uk']) { throw new RuntimeException('Exact canonical widget binding missing.'); }
                self::plainUk($plan[0]); $raw = $row['raw']; $ordered = [];
                foreach ($sample['question_attribute_keys'] as $field) { $ordered[$field] = $raw[$field] ?? null; }
                if (self::digest($ordered) !== $sample['snapshot_hashes']['question_snapshot']) { throw new RuntimeException('Exact original widget row differs.'); }
                $tokens = $row['canonical_server_payload']['correct_tokens'];
                if ($tokens !== $sample['correct_tokens']) { throw new RuntimeException('Canonical native token order differs.'); }
                $answers = [];
                foreach ($row['full_model']['answers'] as $answer) { $answers[] = ['id' => $answer['id'], 'question_id' => $answer['question_id'], 'marker' => $answer['marker'], 'position' => $answer['position'] ?? null, 'option_id' => $answer['option_id'], 'option' => $answer['option']['option']]; }
                usort($answers, static fn ($a, $b) => strnatcasecmp($a['marker'], $b['marker']));
                $accepted = array_values(array_unique([implode(' ', $tokens), ...($plan[1] ?? [])]));
                $known = $tokens; $extras = [];
                foreach ($accepted as $sentence) { foreach (preg_split('/\s+/u', $sentence) as $word) { if (!in_array($word, $known, true)) { $extras[] = $word; $known[] = $word; } } }
                $out['questions'][] = ['topic' => $topic, 'block_uuid' => $captured['block_uuid'], 'id' => $id, 'uuid' => $raw['uuid'],
                    'seeder' => $raw['seeder'], 'type' => '4', 'level' => $raw['level'], 'question' => $sample['visible_condition_uk'], 'source_question' => $raw['question'],
                    'question_attribute_keys' => $sample['question_attribute_keys'], 'snapshot_hashes' => $sample['snapshot_hashes'],
                    'source_options' => $row['canonical_server_payload']['options'],
                    'source_token_labels' => $row['canonical_server_payload']['token_labels'],
                    'source_derived_compose_options' => $row['canonical_server_payload']['derived_compose_options'],
                    'answers' => $answers, 'correct_tokens' => $tokens, 'hint_uk' => $plan[0], 'accepted_sentences' => $accepted,
                    'token_labels' => self::labels($tokens, $plan[2] ?? []), 'added_options' => $extras];
            }
        }
        return $out;
    }
    public static function loadWidgets(): array
    {
        $path = self::root().'/'.self::WIDGET_BINDINGS;
        if (!is_file($path) || is_link($path) || !hash_equals(self::WIDGET_BINDINGS_SHA, hash_file('sha256', $path))) { throw new RuntimeException('Adjectives/adverbs widget binding bytes differ.'); }
        static $data = null;
        if ($data === null) { $data = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR); self::validateWidgets($data); }
        foreach ($data['targets'] as $target) {
            $audit = self::root().'/'.$target['audit'];
            if (!is_file($audit) || is_link($audit) || !hash_equals($target['audit_sha256'], hash_file('sha256', $audit))) { throw new RuntimeException('Pinned widget audit differs.'); }
        }
        return $data;
    }
    public static function validateWidgets(array $data): void
    {
        if (($data['schema_version'] ?? null) !== 1 || ($data['package'] ?? null) !== 'adjectives-adverbs-native-widget-bindings'
            || array_column($data['targets'] ?? [], 'topic') !== self::TOPICS || count($data['questions'] ?? []) !== 35
            || count(array_unique(array_column($data['questions'], 'id'))) !== 35) { throw new RuntimeException('Finite widget scope differs.'); }
        $all = [];
        foreach ($data['targets'] as $target) {
            if (count($target['selected_ids']) !== 5 || count(array_unique($target['selected_ids'])) !== 5) { throw new RuntimeException('Each widget needs its five distinct reviewed questions.'); }
            $all = [...$all, ...$target['selected_ids']];
        }
        if ($all !== array_column($data['questions'], 'id')) { throw new RuntimeException('Widget question order differs.'); }
        foreach ($data['questions'] as $binding) {
            $plan = self::widgetPlans()[$binding['id']] ?? null;
            if ($plan === null || $binding['hint_uk'] !== $plan[0] || $binding['accepted_sentences'] !== array_values(array_unique([implode(' ', $binding['correct_tokens']), ...($plan[1] ?? [])]))
                || $binding['token_labels'] !== self::labels($binding['correct_tokens'], $plan[2] ?? [])) { throw new RuntimeException('Widget teaching plan differs.'); }
            self::plainUk($binding['hint_uk']);
        }
    }
    /** Exact original snapshots plus the frozen canonical condition clone, without SQL or model mutation. */
    private static function widgetMatches(object $question, array $binding): bool
    {
        if (!method_exists($question, 'getAttributes') || !method_exists($question, 'relationLoaded')) { return false; }
        foreach (['id', 'uuid', 'seeder', 'type', 'level', 'question'] as $field) {
            $actual = $question->$field ?? null; if ($field === 'type') { $actual = (string) $actual; }
            if ($actual !== $binding[$field]) { return false; }
        }
        $attributes = $question->getAttributes(); $expectedKeys = $binding['question_attribute_keys'];
        $decorations = array_diff_key($attributes, array_flip($expectedKeys));
        if ($decorations !== [] && (array_keys($decorations) !== ['match_score', 'matched_tag_ids', 'marker_tags']
            || $decorations['match_score'] !== null || $decorations['matched_tag_ids'] !== [] || !is_array($decorations['marker_tags']))) { return false; }
        $keys = array_keys(array_diff_key($attributes, $decorations)); $sorted = $expectedKeys; sort($keys); sort($sorted);
        if ($keys !== $sorted) { return false; }
        $ordered = [];
        foreach ($expectedKeys as $field) { $ordered[$field] = $field === 'question' ? $binding['source_question'] : $attributes[$field]; }
        if (self::digest($ordered) !== $binding['snapshot_hashes']['question_snapshot']) { return false; }
        foreach (['answers', 'options', 'hints', 'verbHints'] as $relation) {
            if (!$question->relationLoaded($relation) || !$question->getRelation($relation) instanceof \Illuminate\Support\Collection) { return false; }
        }
        if ($question->getRelation('verbHints')->isNotEmpty()) { return false; }
        $answers = []; $options = []; $pivots = []; $hints = [];
        foreach ($question->getRelation('answers') as $answer) {
            if (!$answer->relationLoaded('option') || $answer->question_id !== $question->id) { return false; }
            $option = $answer->getRelation('option');
            if (!is_object($option) || $option->id !== $answer->option_id) { return false; }
            $answers[] = ['id' => $answer->id, 'marker' => $answer->marker, 'position' => $answer->position, 'option_id' => $answer->option_id, 'option' => $option->option];
        }
        usort($answers, static fn ($a, $b) => strnatcasecmp($a['marker'], $b['marker']));
        foreach ($question->getRelation('options') as $option) {
            if (!$option->relationLoaded('pivot')) { return false; }
            $pivot = $option->getRelation('pivot');
            if (!is_object($pivot) || !method_exists($pivot, 'getAttributes')) { return false; }
            $attrs = $pivot->getAttributes();
            if (!array_key_exists('flag', $attrs) || ($attrs['question_id'] ?? null) !== $question->id || ($attrs['option_id'] ?? null) !== $option->id) { return false; }
            $options[] = ['id' => $option->id, 'option' => $option->option];
            $pivots[] = ['question_id' => $attrs['question_id'], 'option_id' => $attrs['option_id'], 'flag' => $attrs['flag']];
        }
        usort($options, static fn ($a, $b) => $a['id'] <=> $b['id']);
        usort($pivots, static fn ($a, $b) => $a['option_id'] <=> $b['option_id']);
        foreach ($question->getRelation('hints') as $hint) { $hints[] = ['id' => $hint->id, 'question_id' => $hint->question_id, 'provider' => $hint->provider, 'locale' => $hint->locale, 'hint' => $hint->hint]; }
        $actual = ['ordered_answer_snapshot' => $answers, 'original_options_snapshot' => $options, 'hints_snapshot' => $hints,
            'verb_hints_snapshot' => [], 'loaded_option_pivots' => $pivots];
        foreach ($actual as $field => $rows) { if (self::digest($rows) !== $binding['snapshot_hashes'][$field]) { return false; } }
        return true;
    }
    public static function projectWidget(object $question, ?string $blockUuid, array $payload, bool $canonicalCaller = false): array
    {
        if (!$canonicalCaller || app()->getLocale() !== 'uk' || ($payload['type'] ?? null) !== '4' || (string) ($question->type ?? '') !== '4') { return $payload; }
        try {
            foreach (self::loadWidgets()['questions'] as $binding) {
                if (($question->id ?? null) !== $binding['id']) { continue; }
                if ($blockUuid !== $binding['block_uuid'] || ($payload['id'] ?? null) !== $binding['id'] || ($payload['level'] ?? null) !== $binding['level']
                    || ($payload['question'] ?? null) !== $binding['question'] || ($payload['correct_tokens'] ?? null) !== $binding['correct_tokens']
                    || ($payload['accepted_sentences'] ?? []) !== [] || ($payload['options'] ?? null) !== $binding['source_options']
                    || ($payload['token_labels'] ?? []) !== $binding['source_token_labels']
                    || ($payload['derived_compose_options'] ?? []) !== $binding['source_derived_compose_options']
                    || !self::widgetMatches($question, $binding)) { return $payload; }
                if (count($payload['answers'] ?? []) !== count($binding['answers'])) { return $payload; }
                foreach ($binding['answers'] as $i => $answer) {
                    if (($payload['answers'][$i]['marker'] ?? null) !== $answer['marker'] || ($payload['answers'][$i]['correct'] ?? null) !== $answer['option']) { return $payload; }
                }
                $payload['accepted_sentences'] = $binding['accepted_sentences'];
                $payload['options'] = array_values(array_unique([...$payload['options'], ...$binding['added_options']]));
                $payload['derived_compose_options'] = array_values(array_unique([...($payload['derived_compose_options'] ?? []), ...$binding['added_options']]));
                $payload['token_labels'] = array_replace($payload['token_labels'] ?? [], $binding['token_labels']);
                $payload['source_bound_compose_hint'] = true; $payload['compose_preanswer_hint'] = $binding['hint_uk'];
                $payload['aa_widget_alias_revision'] = self::digest($binding);
                return $payload;
            }
        } catch (Throwable) { /* Unknown, changed, foreign or unloaded bindings retain their full original payload. */ }
        return $payload;
    }
}
