<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Finite teaching-data supplement. Canonical UK theory only; no model or DB mutation. */
final class VerbToBePracticeSupplement
{
    public const SOURCE = 'docs/content/verb-to-be-practice-supplement.v1.json';
    public const SOURCE_SHA = 'ef43fdecf77411bc93a879d9e27d2854b31eb02396077a563ed6bf4b9c3e8ce5';
    public const TOPICS = ['present', 'negatives', 'questions', 'past', 'future', 'there'];

    private static function root(): string { return dirname(__DIR__, 2); }
    public static function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    /** Explicit editorial plans for the retained twelve controls, not inferred sentence splitting. */
    public static function retainedPlans(): array
    {
        return [
            'present' => [
                ['I|am|in the|small garden.', 'Я в маленькому саду.', 'Почни з мовця; після узгодженої теперішньої зв’язки назви місце у вже згаданому саду.',
                    ["I am in the little garden.", "I'm in the little garden."], ['small garden.' => ['little garden.']]],
                ['The red boxes|are|heavy.', 'Червоні коробки важкі.', 'Почни з названих коробок у множині; після зв’язки постав ознаку.', [], []],
            ],
            'negatives' => [
                ['I|am not|at the station.', 'Я не на вокзалі.', 'Почни з мовця й збережи теперішнє заперечення перед назвою конкретного вокзалу.',
                    ["I am not at the railway station.", "I'm not at the railway station.", "I am not at the train station.", "I'm not at the train station."],
                    ['at the station.' => ['at the railway station.', 'at the train station.']]],
                ['The children|are not|in the car.', 'Діти не в машині.', 'Йдеться про вже названих дітей і машину; збережи множину та заперечення.',
                    ['The kids are not in the car.', "The kids aren't in the car."], ['The children' => ['The kids']]],
            ],
            'questions' => [
                ['No,|I am not|at home.', 'Тебе запитали, чи ти вдома. Відповідай: «Ні, я не вдома».', 'Склади повну негативну репліку про себе, а не лише коротку відповідь; назви місце.',
                    ['No, I am not home.', "No, I'm not home."], ['at home.' => ['home.']]],
                ['Who|is|ready?', 'Хто готовий?', 'Питальне слово саме називає невідому людину; не додавай ще одного підмета.', [], []],
            ],
            'past' => [
                ['Olena and I|were|at the museum|yesterday.', 'Ми з Оленою були в музеї вчора.', 'Почни з Олени та мовця; минула зв’язка узгоджується з двома людьми. Уже згаданий музей постав перед часом.',
                    ['Olena and I were in the museum yesterday.'], ['at the museum' => ['in the museum']]],
                ['The library|was not|open|yesterday.', 'Бібліотека не була відчинена вчора.', 'Почни з названої бібліотеки; утвори минуле заперечення зі словом «відчинена», час залиш у кінці.', [], []],
            ],
            'future' => [
                ['We|will not be|at home|on Sunday.', 'Ми не будемо вдома в неділю.', 'Почни з мовців; збережи майбутню зв’язку, заперечення, місце та день.',
                    ['We will not be home on Sunday.', "We won't be home on Sunday.", "We'll not be home on Sunday."], ['at home' => ['home']]],
                ['Where|will|the teachers|be|at noon?', 'Де вчителі будуть опівдні?', 'Почни з питання про місце; між майбутнім допоміжним дієсловом і зв’язкою постав названих учителів.', [], []],
            ],
            'there' => [
                ['There is|some milk|in the fridge.', 'У холодильнику є трохи молока.', 'Почни з конструкції наявності; узгодь її з незлічуваним молоком і збережи невизначену кількість у названому холодильнику.',
                    ['There is some milk in the refrigerator.', "There's some milk in the refrigerator."], ['in the fridge.' => ['in the refrigerator.']]],
                ['Are there|any clean plates|in the cupboard?', 'У шафі є чисті тарілки?', 'Постав нейтральне питання про наявність у названій шафі; збережи множину та нестверджувальне слово кількості.',
                    ['Are there any clean plates in the cabinet?'], ['in the cupboard?' => ['in the cabinet?']]],
            ],
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
            throw new RuntimeException('Verb to Be native condition/hint must be plain Ukrainian.');
        }
    }

    private static function groups(array $groups, string $answer): void
    {
        if (count($groups) < 2 || implode(' ', $groups) !== $answer) { throw new RuntimeException('Verb to Be token grouping changes answer.'); }
        foreach ($groups as $group) {
            if (!is_string($group) || trim($group) !== $group || $group === '' || str_contains($group, '/')
                || count(preg_split('/\s+/u', $group)) > 3) { throw new RuntimeException('Verb to Be groups need one to three words.'); }
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
        [$before, $patch, $master, $contract] = M85VerbToBe::load(self::root());
        M85VerbToBe::validate($before, $patch, $master, $contract);
        $document = ['schema_version' => 1, 'package' => 'verb-to-be-native-practice-supplement', 'status' => 'frozen',
            'release' => M85VerbToBe::SOURCE, 'release_sha256' => M85VerbToBe::SOURCE_SHA, 'targets' => []];
        foreach (self::TOPICS as $i => $topic) {
            $target = $patch['targets'][$i]; $slot = M85VerbToBe::PRACTICE_SOURCE_INDICES[$i];
            $stored = $target['after']['page']['blocks'][$slot];
            $binding = ['seeder' => $target['identity'], 'page_id' => M85VerbToBe::PAGE_IDS[$i], 'page_category_id' => 8,
                'uuid' => M26DetailPackage::uuid($target['identity'], $stored, $slot + 1), 'locale' => 'uk',
                'type' => 'practice-set', 'column' => 'left', 'sort_order' => $slot + 1, 'level' => null, 'heading' => null, 'css_class' => null];
            $block = (object) ($binding + ['body' => $stored['body']]);
            $raw = json_decode($stored['body'], true, flags: JSON_THROW_ON_ERROR);
            $presentation = M85VerbToBe::presentation($block, $raw);
            if (!is_array($presentation['data'] ?? null)) { throw new RuntimeException('Verb to Be original presentation missing.'); }
            $data = $presentation['data']; $after = $data; $updates = []; $tokenLabels = [];
            if (count($data['inputs']) !== 2 || count($data['selects']) !== 2 || count($data['choices']) !== 2) {
                throw new RuntimeException('Verb to Be retained six-task contract differs.');
            }
            foreach (self::retainedPlans()[$topic] as $index => [$groupText, $condition, $hint, $extraAliases, $labelAliases]) {
                $groups = explode('|', $groupText); $old = $data['inputs'][$index];
                self::groups($groups, $old['answer']); self::plainUk($condition); self::plainUk($hint);
                $shuffled = self::shuffled($groups, $topic.':retained:'.$index);
                $new = $old; $new['practice_kind'] = 'order'; $new['before'] = implode(' / ', $shuffled);
                $new['after'] = $condition.' Підказка: '.$hint; $new['verb_hint'] = $hint;
                $new['accepted'] = array_values(array_unique(array_merge($old['accepted'], $extraAliases)));
                foreach ($extraAliases as $alias) { $new['feedback'][mb_strtolower($alias)] = 'Правильно. '.$hint; }
                $after['inputs'][$index] = $new;
                $labels = self::labels($groups, $labelAliases); if ($labels !== []) { $tokenLabels[$index] = $labels; }
                $updates[] = ['index' => $index, 'id' => $old['id'], 'ordered_groups' => $groups, 'shuffled_groups' => $shuffled,
                    'condition_uk' => $condition, 'hint_uk' => $hint, 'added_aliases' => $extraAliases];
            }
            $authorPath = 'docs/content/verb-to-be-quality/'.$topic.'.v1.json';
            $authorFile = self::root().'/'.$authorPath;
            if (!is_file($authorFile) || is_link($authorFile)) { throw new RuntimeException('Verb to Be native author source missing.'); }
            $author = json_decode(file_get_contents($authorFile), true, flags: JSON_THROW_ON_ERROR);
            $focus = M85VerbToBe::FOCUS_LEVELS[$i]; $added = ['selects' => [], 'inputs' => []];
            foreach (['gap' => 'selects', 'compose' => 'inputs'] as $kind => $group) {
                $rows = array_slice(array_values(array_filter($author['questions'] ?? [], static fn ($row) => $row['kind'] === $kind && $row['level'] === $focus)), 0, 2);
                if (count($rows) !== 2) { throw new RuntimeException('Verb to Be native focus pair missing.'); }
                foreach ($rows as $n => $row) {
                    $id = 'vtbq-native-'.$topic.'-'.($kind === 'gap' ? 'gap' : 'translate').'-'.($n + 1);
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
            $after['verb_to_be_practice_supplement'] = ['revision' => 'v1', 'topic' => $topic];
            $document['targets'][] = ['topic' => $topic, 'definition' => $target['path'],
                'definition_sha256' => hash_file('sha256', self::root().'/'.$target['path']),
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
            throw new RuntimeException('Verb to Be native supplement bytes differ.');
        }
        $release = self::root().'/'.M85VerbToBe::SOURCE;
        if (!is_file($release) || is_link($release) || !hash_equals(M85VerbToBe::SOURCE_SHA, hash_file('sha256', $release))) {
            throw new RuntimeException('Verb to Be native accepted release bytes differ.');
        }
        static $verified = null;
        if ($verified === null) {
            $verified = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
            self::validate($verified);
        }
        foreach ($verified['targets'] as $target) {
            foreach (['author', 'definition'] as $kind) {
                $path = self::root().'/'.$target[$kind];
                if (!is_file($path) || is_link($path) || !hash_equals($target[$kind.'_sha256'], hash_file('sha256', $path))) {
                    throw new RuntimeException('Verb to Be native source pin differs.');
                }
            }
        }
        return $verified;
    }

    public static function validate(array $document): void
    {
        if ($document !== self::compile()) { throw new RuntimeException('Verb to Be native finite teaching projection differs.'); }
    }

    private static function entry(object $block, array $data, bool $after): ?array
    {
        if (app()->getLocale() !== 'uk' || ($block->locale ?? null) !== 'uk' || ($block->type ?? null) !== 'practice-set'
            || !in_array($block->seeder ?? null, M85VerbToBe::OWNERS, true)) { return null; }
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
