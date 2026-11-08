<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Finite teaching-data supplement. Canonical UK theory only; no model or DB mutation. */
final class BasicGrammarPracticeSupplement
{
    public const SOURCE = 'docs/content/basic-grammar-practice-supplement.v1.json';
    public const SOURCE_SHA = '18cfba270899b2d959d4b31772237044eaf6c416c8cc8be9ee350289cee5b7bd';
    public const TOPICS = ['parts-of-speech', 'sentence-structure', 'sentence-types', 'imperatives', 'conjunctions', 'have-got'];

    private static function root(): string { return dirname(__DIR__, 2); }
    public static function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public static function specs(): array
    {
        return [
            'parts-of-speech' => [M83B2RevisionBasicGrammar::class, 1],
            'sentence-structure' => [M83B2RevisionBasicGrammar::class, 2],
            'sentence-types' => [M83B2RevisionBasicGrammar::class, 3],
            'imperatives' => [M83B2RevisionBasicGrammar::class, 4],
            'conjunctions' => [M83B2RevisionBasicGrammar::class, 5],
            'have-got' => [M84WordOrderHaveGot::class, 5],
        ];
    }

    /** Retained input indices; null group means the old gap frame stays intact. */
    public static function retainedPlans(): array
    {
        return [
            'parts-of-speech' => [
                [null, null, 'Потрібен прислівник способу співу, утворений від прикметника зі значенням «гарний».'],
                [null, null, 'Потрібен прикметник зі значенням «хороший», який описує учня перед іменником.'],
                [null, null, 'Передай наполегливість праці, а не майже повну відсутність дії.'],
                ['A small bird|sings|loudly.', 'Маленька пташка голосно співає. Це перша згадка про одну пташку.', 'Почни з неозначеного артикля та опису пташки; спосіб співу постав у кінці.'],
                ['Olia and I|are|in the garden.', 'Оля і я перебуваємо в саду.', 'Почни з Олі, потім назви мовця; теперішня зв’язка узгоджується з двома людьми. Сад уже відомий.'],
            ],
            'sentence-structure' => [
                ['Maria|loves|music.', 'Марія любить музику.', 'Збережи нейтральний порядок: виконавець, ставлення й додаток.'],
                ['He|plays|tennis.', 'Він грає в теніс.', 'Почни з виконавця; після дії постав назву заняття.'],
                ['The students|read|books.', 'Студенти читають книги.', 'Почни з уже названих студентів; збережи множину й прямий додаток.'],
            ],
            'sentence-types' => [
                ["She|doesn't like|music.", 'Початкове твердження: «Вона любить музику». Замість нього повідом: «Вона не любить музику».', 'Склади нейтральне заперечне твердження; особове закінчення має лише допоміжне дієслово.'],
                ['Does she|like|music?', 'Початкове твердження: «Вона любить музику». Замість нього постав питання: «Вона любить музику?»', 'Побудуй нейтральне загальне питання, не перепитування зі стверджувальним порядком.'],
                ['Be|quiet.', 'Початкове повідомлення: «Ти поводишся тихо». Замість нього звернися зі спонуканням: «Поводься тихо».', 'Склади базове спонукання зі зв’язкою та ознакою без вираженого підмета.'],
            ],
            'imperatives' => [
                ["Don't|open|the window.", 'Не відчиняй вікно.', 'Склади звичайну заборону відчиняти вже назване вікно; окремий підмет не потрібен.'],
                ["Don't|be|loud.", 'Не галасуй.', 'Склади заборону зі зв’язкою та ознакою гучності; окремого підмета не додавай.'],
                ["Don't|forget|your keys.", 'Не забудь свої ключі.', 'Склади повне застереження, зберігши заперечення та назву речей співрозмовника.'],
            ],
            'conjunctions' => [
                ['I was tired,|so I|went to bed.', 'Я був утомлений, тому ліг спати.', 'Втома є причиною: почни з неї, а після сполучника наслідку назви подію.'],
                ["She's rich,|but|she's not|happy.", 'Вона багата, але не щаслива.', 'Автор навмисно протиставляє два факти; збережи особу та заперечення щастя.'],
                ["I can't|come|because I'm|busy.", 'Я не можу прийти, тому що я зайнятий.', 'Почни з неможливості прийти; після сполучника причини поясни її зайнятістю.'],
            ],
            'have-got' => [
                ['Our teacher|has got|two pets.', 'У нашої вчительки є дві домашні тварини.', 'Почни з названої вчительки; використай теперішню конструкцію володіння з допоміжним дієсловом і словом про наявність.'],
                ['The children|have not got|a red ball.', 'У дітей немає червоного м’яча.', 'Почни з уже названих дітей і збережи заперечну конструкцію наявності.'],
            ],
        ];
    }

    public static function editingHints(): array
    {
        return [
            'parts-of-speech' => [1 => 'Назви сім класів слів у початковому порядку; не плутай частини мови з членами речення.',
                2 => 'Назви класи всіх десяти слів; допоміжне й основне дієслова обліковуй окремо.'],
            'sentence-structure' => [1 => 'Віднови нейтральний порядок мовця, дії та додатка; час залиш наприкінці.',
                2 => 'Віднови нейтральний порядок виконавця, дії та заняття без акцентного винесення.'],
            'sentence-types' => [1 => 'Після допоміжного дієслова основне не має другого особового закінчення.',
                2 => 'Залиши базову команду без вираженого підмета, зберігши зв’язку та ознаку тиші.'],
            'imperatives' => [1 => 'У базовій команді потрібна початкова форма без інфінітивної частки.',
                2 => 'Віднови пропущену зв’язку між запереченням і ознакою запізнення; збережи обставину.'],
            'conjunctions' => [1 => 'Перед цілою частиною з підметом і дієсловом потрібен сполучник причини, не прийменниковий зворот.'],
            'have-got' => [],
        ];
    }

    /** Finite wording corrections needed by the appended controls, never style choices. */
    public static function headerUpdates(): array
    {
        return [
            'parts-of-speech' => [
                'select_intro' => 'У перших трьох пунктах визнач частину мови виділеного слова. В останніх двох обери форму для пропуску.',
                'input_intro' => 'У перших трьох підпунктах уведи одне слово. Решта відповідей — повні речення: вводь їх вручну або складай із перемішаних груп.',
            ],
            'sentence-structure' => [
                'select_intro' => 'У перших трьох пунктах визнач роль виділеної частини: підмет, дієслово чи додаток. В останніх двох доповни речення, зберігаючи ці ролі.',
            ],
            'sentence-types' => [
                'select_intro' => 'У перших чотирьох пунктах визнач названу в умові форму або тип висловлювання. В останніх двох доповни речення за умовою.',
                'input_intro' => 'У перших трьох пунктах зміни твердження на задану форму, зберігаючи лексичний зміст. В останніх двох переклади українське речення. Вводь вручну або складай із груп.',
            ],
            'imperatives' => [
                'select_intro' => 'Обери варіант за умовою кожного пункту. Наголошений підмет не заборонений загалом; де прямо зазначено, використовуй базову команду без нього.',
                'input_title' => 'Вправа 3. Напиши або склади команду',
                'input_intro' => 'Виконай українську умову вручну або натисканням на перемішані групи. Збережи ствердження чи заперечення.',
            ],
            'conjunctions' => [
                'select_intro' => 'Обери сполучник за чітко заданим змістом кожного пункту. Інший зв’язок може бути граматичним, але тут потрібно зберегти саме описаний задум.',
            ],
            'have-got' => [],
        ];
    }

    /** Reviewed lexical alternatives only; never a global synonym dictionary. */
    public static function retainedAliases(): array
    {
        return [
            'parts-of-speech' => [3 => [['A little bird sings loudly.'], ['A small bird' => ['A little bird']]]],
            'sentence-structure' => [0 => [['Maria likes music.'], ['loves' => ['likes']]]],
            'sentence-types' => [0 => [["She doesn't love music.", 'She does not love music.'], ["doesn't like" => ["doesn't love"]]],
                1 => [['Does she love music?'], ['like' => ['love']]]],
            'imperatives' => [1 => [["Don't be noisy.", 'Do not be noisy.'], ['loud.' => ['noisy.']]]],
            'conjunctions' => [],
            'have-got' => [1 => [['The kids have not got a red ball.', "The kids haven't got a red ball."], ['The children' => ['The kids']]]],
        ];
    }

    public static function shuffled(array $groups, string $seed): array
    {
        $decorated = [];
        foreach ($groups as $i => $text) { $decorated[] = ['text' => $text, 'sort' => hash('sha256', $seed.'|'.$i.'|'.$text)]; }
        usort($decorated, static fn ($a, $b) => strcmp($a['sort'], $b['sort']));
        $result = array_column($decorated, 'text');
        if ($result === $groups) { $result[] = array_shift($result); }
        return $result;
    }

    private static function plainUk(string $value): void
    {
        if (trim($value) === '' || preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $value) !== 1
            || preg_match('/[A-Za-z]|<[^>]+>|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu', $value) === 1) {
            throw new RuntimeException('Basic Grammar native condition/hint must be plain Ukrainian.');
        }
    }

    private static function groups(array $groups, string $answer): void
    {
        if (count($groups) < 2 || implode(' ', $groups) !== $answer) { throw new RuntimeException('Basic Grammar token grouping changes answer.'); }
        foreach ($groups as $group) {
            if (!is_string($group) || trim($group) !== $group || $group === '' || str_contains($group, '/')
                || count(preg_split('/\s+/u', $group)) > 3) { throw new RuntimeException('Basic Grammar groups need one to three words.'); }
        }
    }

    private static function labels(array $groups, array $aliases): array
    {
        $out = [];
        foreach ($groups as $group) {
            $values = $aliases[$group] ?? [];
            if ($values === []) {
                foreach ($aliases as $word => $synonyms) {
                    $pattern = '/(?<![A-Za-z])'.preg_quote($word, '/').'(?![A-Za-z])/u';
                    if (preg_match_all($pattern, $group) !== 1) { continue; }
                    foreach ($synonyms as $synonym) { $values[] = preg_replace($pattern, $synonym, $group, 1); }
                }
            }
            $values = array_values(array_unique(array_filter($values, static fn ($value) => is_string($value) && $value !== $group)));
            if ($values !== []) { $out[$group] = $group.' ('.implode(' / ', $values).')'; }
        }
        return $out;
    }

    /** File-only deterministic compiler; source content cannot choose a view, CSS or runtime callback. */
    public static function compile(): array
    {
        $document = ['schema_version' => 1, 'package' => 'basic-grammar-native-practice-supplement', 'status' => 'frozen', 'targets' => []];
        $releases = [];
        foreach (self::specs() as $topic => [$release, $i]) {
            if (!isset($releases[$release])) {
                $releases[$release] = $release::load(self::root());
                $release::validate(...$releases[$release]);
            }
            [, $patch] = $releases[$release];
            $target = $patch['targets'][$i]; $slot = $release::PRACTICE_SOURCE_INDICES[$i];
            $stored = $target['after']['page']['blocks'][$slot];
            $binding = ['seeder' => $target['identity'], 'page_id' => $release::PAGE_IDS[$i], 'page_category_id' => 2,
                'uuid' => M26DetailPackage::uuid($target['identity'], $stored, $slot + 1), 'locale' => 'uk',
                'type' => 'practice-set', 'column' => 'left', 'sort_order' => $slot + 1, 'level' => null, 'heading' => null, 'css_class' => null];
            $block = (object) ($binding + ['body' => $stored['body']]);
            $raw = json_decode($stored['body'], true, flags: JSON_THROW_ON_ERROR);
            $presentation = $release::presentation($block, $raw);
            if (!is_array($presentation['data'] ?? null)) { throw new RuntimeException('Basic Grammar original presentation missing.'); }
            $data = $presentation['data']; $after = $data; $updates = []; $tokenLabels = [];
            if (count($data['inputs']) !== count(self::retainedPlans()[$topic])) {
                throw new RuntimeException('Basic Grammar retained input count differs.');
            }
            foreach (self::retainedPlans()[$topic] as $index => [$groupText, $condition, $hint]) {
                $old = $data['inputs'][$index]; self::plainUk($hint); $new = $old; $new['verb_hint'] = $hint;
                if ($groupText === null) {
                    $new['practice_kind'] = 'gap'; $new['after'] .= '<p>Підказка: '.$hint.'</p>';
                    $updates[] = ['index' => $index, 'kind' => 'gap', 'hint_uk' => $hint];
                } else {
                    $groups = explode('|', $groupText);
                    self::groups($groups, $old['answer']); self::plainUk($condition);
                    $shuffled = self::shuffled($groups, $topic.':retained:'.$index);
                    $new['practice_kind'] = 'order'; $new['before'] = implode(' / ', $shuffled);
                    $new['after'] = $condition.' Підказка: '.$hint;
                    $updates[] = ['index' => $index, 'kind' => 'order', 'ordered_groups' => $groups, 'shuffled_groups' => $shuffled,
                        'condition_uk' => $condition, 'hint_uk' => $hint];
                }
                [$aliases, $labelAliases] = self::retainedAliases()[$topic][$index] ?? [[], []];
                foreach ($aliases as $alias) {
                    $new['accepted'][] = $alias;
                    $new['feedback'][mb_strtolower($alias)] = 'Правильно. '.$hint;
                }
                $new['accepted'] = array_values(array_unique($new['accepted']));
                if ($groupText !== null) {
                    $labels = self::labels($groups, $labelAliases);
                    if ($labels !== []) { $tokenLabels[$index] = $labels; }
                }
                $after['inputs'][$index] = $new;
            }
            foreach (self::editingHints()[$topic] as $index => $hint) {
                self::plainUk($hint);
                if (!is_string($after['rephrase'][$index]['answer'] ?? null)) { throw new RuntimeException('Basic Grammar editing control missing.'); }
                $after['rephrase'][$index]['verb_hint'] = $hint;
                $after['rephrase'][$index]['original'] .= ' Підказка: '.$hint;
            }
            foreach (self::headerUpdates()[$topic] as $field => $value) { $after[$field] = $value; }
            $authorPath = 'docs/content/basic-grammar-quality/'.$topic.'.v1.json';
            $authorFile = self::root().'/'.$authorPath;
            if (!is_file($authorFile) || is_link($authorFile)) { throw new RuntimeException('Basic Grammar native author source missing.'); }
            $author = json_decode(file_get_contents($authorFile), true, flags: JSON_THROW_ON_ERROR);
            $focus = 'A1'; $added = ['selects' => [], 'inputs' => []];
            foreach (['gap' => 'selects', 'compose' => 'inputs'] as $kind => $group) {
                $rows = array_slice(array_values(array_filter($author['questions'] ?? [], static fn ($row) => $row['kind'] === $kind && $row['level'] === $focus)), 0, 2);
                if (count($rows) !== 2) { throw new RuntimeException('Basic Grammar native focus pair missing.'); }
                foreach ($rows as $n => $row) {
                    $id = 'bgq-native-'.$topic.'-'.($kind === 'gap' ? 'gap' : 'translate').'-'.($n + 1);
                    self::plainUk($row['hint_uk']);
                    if ($kind === 'gap') {
                        if (substr_count($row['question'], '{a1}') !== 1 || preg_match('/\{a[2-9]/', $row['question']) === 1) { throw new RuntimeException('Native gap needs one answer slot.'); }
                        $accepted = array_values(array_unique(array_merge([$row['answer']], $row['answer_synonyms'] ?? [])));
                        $note = ($row['answer_synonyms'] ?? []) === [] ? '' : '<p>Допустимі синоніми: '.htmlspecialchars(implode(' / ', $row['answer_synonyms']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'.</p>';
                        $label = '<p>'.htmlspecialchars($row['uk'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</p><p lang="en">'.htmlspecialchars(str_replace('{a1}', '____', $row['question']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</p><p>'.$row['hint_uk'].'</p>'.$note;
                        $after['selects'][] = ['id' => $id, 'practice_kind' => 'gap', 'label' => $label, 'options' => $row['options'],
                            'answer' => $row['answer'], 'accepted' => $accepted, 'verb_hint' => $row['hint_uk'],
                            'feedback' => array_fill_keys(array_map('mb_strtolower', $accepted), 'Правильно. '.$row['hint_uk'])];
                        $added['selects'][] = ['id' => $id, 'source_id' => $row['id']];
                    } else {
                        self::plainUk($row['question']); self::groups($row['groups'], $row['target']);
                        $shuffled = self::shuffled($row['groups'], $id); $inputIndex = count($after['inputs']);
                        $accepted = array_values(array_unique(array_merge([$row['target']], $row['accepted_targets'] ?? [])));
                        $after['inputs'][] = ['id' => $id, 'practice_kind' => 'translation', 'before' => implode(' / ', $shuffled),
                            'after' => $row['question'].' Підказка: '.$row['hint_uk'], 'verb_hint' => $row['hint_uk'],
                            'answer' => $row['target'], 'accepted' => $accepted,
                            'feedback' => array_fill_keys(array_map('mb_strtolower', $accepted), 'Правильно. '.$row['hint_uk'])];
                        $labels = self::labels($row['groups'], $row['display_synonyms'] ?? []);
                        if ($labels !== []) { $tokenLabels[$inputIndex] = $labels; }
                        $added['inputs'][] = ['id' => $id, 'source_id' => $row['id'], 'ordered_groups' => $row['groups'], 'shuffled_groups' => $shuffled];
                    }
                }
            }
            $after['basic_grammar_practice_supplement'] = ['revision' => 'v1', 'topic' => $topic];
            $document['targets'][] = ['topic' => $topic, 'definition' => $target['path'],
                'definition_sha256' => hash_file('sha256', self::root().'/'.$target['path']),
                'release' => $release::SOURCE, 'release_sha256' => $release::SOURCE_SHA,
                'author' => $authorPath, 'author_sha256' => hash_file('sha256', $authorFile), 'focus_level' => $focus,
                'binding' => $binding + ['body_sha256' => hash('sha256', $stored['body'])],
                'before_data' => $data, 'before_data_sha256' => self::digest($data),
                'after_data' => $after, 'after_data_sha256' => self::digest($after),
                'input_updates' => $updates, 'added' => $added, 'input_token_labels' => $tokenLabels];
        }
        return $document;
    }

    public static function load(): array
    {
        $file = self::root().'/'.self::SOURCE;
        if (!is_file($file) || is_link($file) || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $file))) {
            throw new RuntimeException('Basic Grammar native supplement bytes differ.');
        }
        static $verified = null;
        if ($verified === null) {
            $verified = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
            self::validate($verified);
        }
        foreach ($verified['targets'] as $target) {
            foreach (['author', 'definition', 'release'] as $kind) {
                $path = self::root().'/'.$target[$kind];
                if (!is_file($path) || is_link($path) || !hash_equals($target[$kind.'_sha256'], hash_file('sha256', $path))) {
                    throw new RuntimeException('Basic Grammar native source pin differs.');
                }
            }
        }
        return $verified;
    }

    public static function validate(array $document): void
    {
        if ($document !== self::compile()) { throw new RuntimeException('Basic Grammar native finite teaching projection differs.'); }
    }

    private static function entry(object $block, array $data, bool $after): ?array
    {
        if (app()->getLocale() !== 'uk' || ($block->locale ?? null) !== 'uk' || ($block->type ?? null) !== 'practice-set'
            || !in_array($block->seeder ?? null, array_merge(array_slice(M83B2RevisionBasicGrammar::OWNERS, 1), [M84WordOrderHaveGot::OWNERS[5]]), true)) { return null; }
        try {
            foreach (self::load()['targets'] as $target) {
                foreach ($target['binding'] as $field => $value) {
                    if ($field === 'body_sha256') { continue; }
                    if (($block->$field ?? null) !== $value) { continue 2; }
                }
                if (is_string($block->body ?? null) && hash_equals($target['binding']['body_sha256'], hash('sha256', $block->body))
                    && $data === $target[$after ? 'after_data' : 'before_data']) { return $target; }
            }
        } catch (Throwable) { /* Unknown or changed data keeps the complete original practice. */ }
        return null;
    }

    public static function project(object $block, array $data, bool $canonicalCaller): array
    {
        return $canonicalCaller ? (self::entry($block, $data, false)['after_data'] ?? $data) : $data;
    }
    public static function isProjected(object $block, array $data): bool { return self::entry($block, $data, true) !== null; }
    public static function originalDataForDisplay(object $block, array $data): array { return self::entry($block, $data, true)['before_data'] ?? $data; }
    public static function inputTokenLabels(object $block, array $data): array { return self::entry($block, $data, true)['input_token_labels'] ?? []; }
}
