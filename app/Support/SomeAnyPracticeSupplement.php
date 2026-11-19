<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Finite UK theory teaching data; no database writes, styling, or alternate answer engine. */
final class SomeAnyPracticeSupplement
{
    public const SOURCE = 'docs/content/some-any-practice-supplement.v1.json';
    public const SOURCE_SHA = '602310acddc4cfec1fe072ca24c2090c1e800e9fb1af4e6692b509da9b4235d0';
    public const WIDGET_BINDINGS = 'docs/content/some-any-widget-bindings.v1.json';
    public const WIDGET_BINDINGS_SHA = '2008db17f63b374c87a89b953432758f92febb3175ba2d2f07e635e38c9e0a98';
    public const TOPICS = ['places', 'people', 'things'];

    private static function root(): string { return dirname(__DIR__, 2); }
    public static function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
    public static function specs(): array
    {
        return ['places' => [M93FutureCompletionDemonstrativesSomeAny::class, 3, 'A2'],
            'people' => [M93FutureCompletionDemonstrativesSomeAny::class, 4, 'A2'],
            'things' => [M93FutureCompletionDemonstrativesSomeAny::class, 5, 'A2']];
    }
    public static function retainedPlans(): array
    {
        return [
            'places' => [
                [null, 'Заперечення вже наведене; уведи лише слово місця зі значенням «нікуди».'],
                [null, 'Усі місця зайняті; потрібне одне слово, яке саме передає відсутність місця.'],
                [null, 'Назви повне охоплення всіх згаданих міст одним словом; не додавай назви особи.'],
                ['We can|meet|anywhere|in this park.', 'Почни з нас і можливості зустрітися; назви вільний вибір місця лише в цьому парку.'],
                ['There is|nowhere|to park|near|the station.', 'Почни з конструкції наявності; запереч місце для паркування саме біля вокзалу.'],
            ],
            'people' => [
                ['Some people|in our group|speak Spanish.', 'Почни з деяких людей, уточни нашу групу й назви мову; збережи множину.'],
                ['I do not|know|anyone|here.', 'Почни з мовця та заперечення; назви відсутність знайомих лише в цьому місці.'],
            ],
            'things' => [
                ['Some things|in this bag|are heavy.', 'Почни з деяких речей, уточни цю сумку й узгодь множину з ознакою ваги.'],
                ['I would like|something|cold|to drink.', 'Почни з ввічливого бажання мовця; назви щось холодне, а призначення напою постав наприкінці.'],
            ],
        ];
    }
    public static function editingHints(): array
    {
        return ['places' => [
            1 => 'Збережи мовця й п’ятничні вечори; використай заперечне допоміжне дієслово та слово місця без другого заперечення.',
            2 => 'Збережи жінку та відсутність телефона; заперечний зміст передай одним словом місця й узгодь дієслово однини.',
        ], 'people' => [], 'things' => []];
    }
    public static function additions(): array
    {
        return [
            'places' => [
                ['translation', 'Біля озера є десь тихе місце. Почни з конструкції наявності; не називай конкретного місця.',
                    'There is|somewhere quiet|near the lake.', 'Після слова невизначеного місця постав ознаку тиші; місце біля озера назви наприкінці.',
                    ["There's somewhere quiet near the lake.", 'There is somewhere quiet by the lake.', "There's somewhere quiet by the lake."],
                    ['near the lake.' => ['by the lake.']]],
                ['translation', 'Ти можеш залишити свою сумку будь-де в цій кімнаті. Йдеться про вільний вибір місця.',
                    'You can leave|your bag|anywhere|in this room.', 'Почни зі співрозмовника й дозволу залишити сумку; вільний вибір обмежений цією кімнатою.',
                    ['You can put your bag anywhere in this room.'], ['You can leave' => ['You can put']]],
            ],
            'people' => [
                ['gap', '{a1} has left a red glove on the bench. (Хтось залишив червону рукавичку на лавці.)',
                    'Someone', 'Назви невідому особу одним займенником; однина допоміжного дієслова вже наведена.', ['Somebody']],
                ['gap', "There isn't {a1} in the music room. (У музичній кімнаті нікого немає.)",
                    'anybody', 'Заперечення вже наведене; уведи займенник для будь-якої людини, не додаючи другого заперечення.', ['anyone']],
                ['translation', 'Усі чекають біля головного входу. Почни з одного займенника для всіх людей; дія відбувається зараз.',
                    'Everyone|is waiting|by the|main entrance.', 'Займенник для всіх людей потребує однини; місце біля головного входу постав наприкінці.',
                    ['Everybody is waiting by the main entrance.', 'Everyone is waiting near the main entrance.', 'Everybody is waiting near the main entrance.'],
                    ['Everyone' => ['Everybody'], 'by the' => ['near the']]],
                ['translation', 'Ніхто не знає нового пароля. Почни із заперечного займенника; не додавай другого заперечення.',
                    'Nobody|knows|the new password.', 'Назви відсутність обізнаних людей заперечним займенником; дієслово має форму однини.',
                    ['No one knows the new password.'], ['Nobody' => ['No one']]],
            ],
            'things' => [
                ['gap', 'There is {a1} warm in this cup. (У цій чашці є щось тепле; вміст не називаємо.)',
                    'something', 'Назви невизначений вміст чашки без назви предмета; ознака температури вже стоїть після пропуску.'],
                ['gap', 'There is {a1} in this envelope. (Цей конверт порожній.)',
                    'nothing', 'Передай відсутність будь-якого вмісту одним заперечним займенником; не змінюй наведеного дієслова.'],
                ['translation', 'Чи є щось під диваном? Це нейтральне запитання без очікування позитивної відповіді.',
                    'Is there|anything|under the sofa?', 'Почни з питання про наявність; невизначений предмет під диваном не вважається наперед знайденим.',
                    ['Is there anything under the couch?'], ['under the sofa?' => ['under the couch?']]],
                ['translation', 'Усе готове до шкільного концерту. Почни із займенника, який охоплює всі потрібні речі.',
                    'Everything|is ready|for the|school concert.', 'Займенник для всіх речей потребує однини; далі назви готовність і призначення концерту.'],
            ],
        ];
    }
    public static function shuffled(array $groups, string $seed): array
    {
        $rows = [];
        foreach ($groups as $i => $text) { $rows[] = ['text' => $text, 'key' => hash('sha256', $seed.'|'.$i.'|'.$text)]; }
        usort($rows, static fn ($a, $b) => strcmp($a['key'], $b['key']));
        $result = array_column($rows, 'text');
        if ($result === $groups) { $result[] = array_shift($result); }
        return $result;
    }
    private static function uk(string $text): void
    {
        if (trim($text) === '' || !preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $text)
            || preg_match('/[A-Za-z]|<[^>]+>|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu', $text)) {
            throw new RuntimeException('Native Some/Any hint must be plain Ukrainian.');
        }
    }
    private static function groups(array $groups, string $answer): void
    {
        if (count($groups) < 2 || implode(' ', $groups) !== $answer) { throw new RuntimeException('Native Some/Any answer changed.'); }
        foreach ($groups as $group) {
            if ($group === '' || trim($group) !== $group || str_contains($group, '/') || count(preg_split('/\s+/u', $group)) > 3) {
                throw new RuntimeException('Native Some/Any group needs one to three words.');
            }
        }
    }
    public static function compile(): array
    {
        $release = M93FutureCompletionDemonstrativesSomeAny::load(self::root());
        M93FutureCompletionDemonstrativesSomeAny::validate(...$release);
        $audit = M93BuilderSelectionPresentation::loadAudit();
        $document = ['schema_version' => 1, 'package' => 'some-any-native-practice-supplement', 'status' => 'frozen', 'targets' => []];
        foreach (self::specs() as $topic => [$class, $i, $focus]) {
            $target = $release[1]['targets'][$i]; $slot = $class::PRACTICE_SOURCE_INDICES[$i];
            $stored = $target['after']['page']['blocks'][$slot]; $bound = $audit['practice_bindings'][$i];
            $binding = ['seeder' => $target['identity'], 'page_id' => $class::PAGE_IDS[$i], 'page_category_id' => $class::CATEGORY_IDS[$i],
                'uuid' => $bound['uuid'], 'locale' => 'uk', 'type' => 'practice-set', 'column' => 'left', 'sort_order' => $slot + 1,
                'level' => $class::LEVELS[$i], 'heading' => null, 'css_class' => null];
            $block = (object) ($binding + ['body' => $stored['body']]);
            $raw = json_decode($stored['body'], true, flags: JSON_THROW_ON_ERROR);
            $presentation = $class::presentation($block, $raw);
            if (!is_array($presentation['data'] ?? null)) { throw new RuntimeException('Native Some/Any retained presentation missing.'); }
            $before = $presentation['data']; $after = $before; $updates = []; $labels = [];
            if (count($before['inputs']) !== count(self::retainedPlans()[$topic])) { throw new RuntimeException('Native Some/Any retained slots differ.'); }
            foreach (self::retainedPlans()[$topic] as $index => [$ordered, $hint]) {
                self::uk($hint); $new = $before['inputs'][$index]; $new['verb_hint'] = $hint;
                $kind = $ordered === null ? 'gap' : 'order'; $new['practice_kind'] = $kind;
                $plan = ['index' => $index, 'kind' => $kind, 'hint_uk' => $hint];
                if ($ordered !== null) {
                    $groups = explode('|', $ordered); self::groups($groups, $new['answer']);
                    $shuffled = self::shuffled($groups, $topic.':retained:'.$index); $new['before'] = implode(' / ', $shuffled);
                    $plan += ['ordered_groups' => $groups, 'shuffled_groups' => $shuffled];
                }
                $new['after'] .= ' Підказка: '.$hint; $after['inputs'][$index] = $new; $updates[] = $plan;
            }
            foreach (self::editingHints()[$topic] as $index => $hint) {
                self::uk($hint); $after['rephrase'][$index]['verb_hint'] = $hint; $after['rephrase'][$index]['original'] .= ' Підказка: '.$hint;
            }
            $added = [];
            foreach (self::additions()[$topic] as $n => $row) {
                [$kind, $condition, $answer, $hint] = $row; self::uk($hint);
                $id = 'some-any-native-'.$topic.'-'.($kind === 'gap' ? 'gap' : 'translate').'-'.($n % 2 + 1);
                $groups = $shuffled = [];
                if ($kind === 'translation') {
                    self::uk($condition); $groups = explode('|', $answer); $answer = implode(' ', $groups); self::groups($groups, $answer);
                    $shuffled = self::shuffled($groups, $id); $visible = implode(' / ', $shuffled); $afterText = $condition.' Підказка: '.$hint;
                } else {
                    if (substr_count($condition, '{a1}') !== 1 || preg_match('/\{a[2-9]/u', $condition)) { throw new RuntimeException('Native Some/Any gap needs one slot.'); }
                    $visible = str_replace('{a1}', '____', $condition); $afterText = $hint;
                }
                $accepted = array_values(array_unique(array_merge([$answer], $row[4] ?? []))); $index = count($after['inputs']);
                if ($kind === 'gap' && count($accepted) > 1) { $afterText .= ' Допустимі варіанти: ('.implode(' / ', $accepted).').'; }
                $after['inputs'][] = ['id' => $id, 'practice_kind' => $kind, 'before' => $visible, 'after' => $afterText, 'verb_hint' => $hint,
                    'answer' => $answer, 'accepted' => $accepted, 'feedback' => array_fill_keys(array_map('mb_strtolower', $accepted), 'Правильно. '.$hint)];
                foreach ($row[5] ?? [] as $token => $aliases) { $labels[$index][$token] = $token.' ('.implode(' / ', $aliases).')'; }
                $added[] = ['id' => $id, 'index' => $index, 'kind' => $kind, 'focus_level' => $focus, 'condition' => $condition,
                    'answer' => $answer, 'accepted' => $accepted, 'hint_uk' => $hint, 'ordered_groups' => $groups, 'shuffled_groups' => $shuffled];
            }
            // Retained lexical alternatives remain visible without changing their original answers or feedback.
            if ($topic === 'people') { $labels[1]['anyone'] = 'anyone (anybody)'; }
            ksort($labels);
            $after['input_title'] = 'Заповни пропуски та склади речення';
            $after['input_intro'] = 'Виконай короткі завдання за їхніми умовами. Повні речення вводь вручну або складай із перемішаних груп.';
            $after['some_any_practice_supplement'] = ['revision' => 'v1', 'topic' => $topic];
            $document['targets'][] = ['topic' => $topic, 'local_url' => $audit['targets'][$i]['local_url'], 'definition' => $target['path'], 'definition_sha256' => hash_file('sha256', self::root().'/'.$target['path']),
                'release' => $class::SOURCE, 'release_sha256' => $class::SOURCE_SHA, 'focus_level' => $focus,
                'binding' => $binding + ['body_sha256' => hash('sha256', $stored['body'])], 'before_data' => $before, 'before_data_sha256' => self::digest($before),
                'after_data' => $after, 'after_data_sha256' => self::digest($after), 'input_updates' => $updates,
                'editing_hint_indices' => array_keys(self::editingHints()[$topic]), 'added' => $added, 'input_token_labels' => $labels];
        }
        return $document;
    }
    public static function load(): array
    {
        $file = self::root().'/'.self::SOURCE;
        if (!is_file($file) || is_link($file) || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $file))) { throw new RuntimeException('Native Some/Any supplement bytes differ.'); }
        static $document = null;
        if ($document === null) { $candidate = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR); self::validate($candidate); $document = $candidate; }
        return $document;
    }
    public static function validate(array $document): void
    {
        if ($document !== self::compile()) { throw new RuntimeException('Native Some/Any finite projection differs.'); }
    }
    private static function entry(object $block, array $data, bool $projected): ?array
    {
        if (app()->getLocale() !== 'uk' || ($block->locale ?? null) !== 'uk' || ($block->type ?? null) !== 'practice-set'
            || !str_starts_with($block->seeder ?? '', 'Database\\Seeders\\Page_V3\\Articles\\SomeAny\\')) { return null; }
        try {
            foreach (self::load()['targets'] as $target) {
                foreach ($target['binding'] as $field => $value) { if ($field !== 'body_sha256' && ($block->$field ?? null) !== $value) { continue 2; } }
                if (is_string($block->body ?? null) && hash_equals($target['binding']['body_sha256'], hash('sha256', $block->body))
                    && $data === $target[$projected ? 'after_data' : 'before_data']) { return $target; }
            }
        } catch (Throwable) { /* Changed or foreign content keeps its complete previous native presentation. */ }
        return null;
    }
    public static function project(object $block, array $data, bool $canonicalCaller): array { return $canonicalCaller ? (self::entry($block, $data, false)['after_data'] ?? $data) : $data; }
    public static function isProjected(object $block, array $data): bool { return self::entry($block, $data, true) !== null; }
    public static function originalDataForDisplay(object $block, array $data): array { return self::entry($block, $data, true)['before_data'] ?? $data; }
    public static function inputTokenLabels(object $block, array $data): array { return self::entry($block, $data, true)['input_token_labels'] ?? []; }

    public static function widgetPlans(): array
    {
        return [
            2353 => 'Запитай про минулий результат пошуку місця для заряджання ноутбука; після допоміжного дієслова потрібна початкова форма дії.',
            2354 => 'Почни з нас і минулої наявності; відсутність місця передай одним заперечним словом, а конкретну бурю назви наприкінці.',
            2355 => 'Почни з туристів та минулого стану множини; охопи всі частини саме старого міста, а не весь світ.',
            2362 => 'Запропонуй спільну зупинку на каву через питання про можливість; невизначене місце не назване, а шлях збережений наприкінці.',
            2363 => 'Збережи групу людей і минуле допоміжне заперечення; після нього потрібне незаперечне слово місця для зберігання коробок.',
            2280 => 'Почни з невизначеної частини людей, збережи уточнення класу мовця й теперішню роботу неповний день; не замінюй множину однією особою.',
            2281 => 'Побудуй нейтральне минуле питання про людей із готелю; після допоміжного дієслова потрібна початкова форма скарги.',
            2282 => 'Заперечний підмет складається з двох слів; одразу уточни команду, збережи минулу дію та ранній час без другого заперечення.',
            2283 => 'Назви невідому особу одним заданим займенником; узгодь однину в теперішньому тривалому часі, а рецепцію назви наприкінці.',
            2288 => 'Почни з відсутності людей у множині; збережи минулу тривалу дію сидіння саме поблизу озера без другого заперечення.',
            15963 => 'Запитай нейтрально про минулу покупку невизначених речей; після допоміжного дієслова потрібна початкова форма дії, вечеря — наприкінці.',
            15964 => 'Почни із заперечного займенника для речей; уточни шухляду й належність мовцеві, не додаючи другого заперечення чи твердження про порожнечу.',
            15965 => 'Почни з потреби мовця, постав ознаку тепла після займенника для невизначеної речі та додай причину через холод наприкінці.',
            15966 => 'Збережи нас і минулу відсутність можливості бачити; після допоміжного заперечення потрібен незаперечний займенник для речей.',
            15972 => 'Почни з невизначеної частини речей у множині; порівняй простоту через інтернет та особисто, не поширюючи висновок на все.',
        ];
    }
    public static function compileWidgetBindings(array $capture): array
    {
        $audit = M93BuilderSelectionPresentation::loadAudit();
        if (($capture['source'] ?? null) !== ['path' => M93BuilderSelectionPresentation::AUDIT, 'sha256' => M93BuilderSelectionPresentation::AUDIT_SHA]) {
            throw new RuntimeException('Native Some/Any canonical widget audit pin differs.');
        }
        if (($capture['schema_version'] ?? null) !== 1 || array_column($capture['targets'] ?? [], 'topic') !== self::TOPICS
            || count($capture['questions'] ?? []) !== 15) { throw new RuntimeException('Native Some/Any exact fifteen widget bindings required.'); }
        $document = $capture; $document['package'] = 'some-any-native-widget-bindings'; $seen = [];
        foreach ($document['targets'] as $n => $target) {
            $i = $n + 3;
            if (($target['identity'] ?? null) !== $audit['targets'][$i]['identity'] || ($target['local_url'] ?? null) !== $audit['targets'][$i]['local_url']
                || ($target['page_id'] ?? null) !== $audit['targets'][$i]['page_id'] || ($target['category_id'] ?? null) !== $audit['targets'][$i]['category_id']
                || ($target['locale'] ?? null) !== 'uk'
                || ($target['block_uuid'] ?? null) !== $audit['practice_bindings'][$i]['uuid']
                || ($target['selected_ids'] ?? null) !== $audit['targets'][$i]['expected_selected_ids']) { throw new RuntimeException('Native Some/Any widget owner differs.'); }
        }
        $samples = [];
        foreach (array_slice($audit['targets'], 3, 3, true) as $i => $target) {
            foreach ($target['first_five'] as $j => $sample) {
                $samples[$sample['id']] = $sample + ['seeder' => $target['bank_seeders'][0], 'level' => $target['selected_levels'][$j],
                    'block_uuid' => $audit['practice_bindings'][$i]['uuid']];
            }
        }
        foreach ($document['questions'] as &$binding) {
            $id = $binding['id'] ?? null; $sample = $samples[$id] ?? null; $hint = self::widgetPlans()[$id] ?? null;
            if ($sample === null || $hint === null || isset($seen[$id]) || ($binding['uuid'] ?? null) !== $sample['uuid']
                || ($binding['type'] ?? null) !== '4' || ($binding['seeder'] ?? null) !== $sample['seeder']
                || ($binding['level'] ?? null) !== $sample['level'] || ($binding['block_uuid'] ?? null) !== $sample['block_uuid']
                || ($binding['source_question'] ?? null) !== $sample['displayed_condition_uk']
                || ($binding['question'] ?? null) !== $sample['visible_condition_uk'] || ($binding['correct_tokens'] ?? null) !== $sample['correct_tokens']
                || array_column($binding['answers'] ?? [], 'option') !== $sample['raw_correct_tokens']) { throw new RuntimeException('Native Some/Any canonical widget question differs.'); }
            foreach ($binding['answers'] as $i => $answer) {
                if (($answer['question_id'] ?? null) !== $id || ($answer['marker'] ?? null) !== 'a'.($i + 1)
                    || ($answer['position'] ?? null) !== null || !is_int($answer['id'] ?? null) || !is_int($answer['option_id'] ?? null)) {
                    throw new RuntimeException('Native Some/Any canonical widget answer instances differ.');
                }
            }
            $seen[$id] = true; self::uk($hint); $binding['hint_uk'] = $hint;
            // Named teaching models are intentional: do not silently replace no one/someone or named lexical supports.
            $binding['accepted_sentences'] = []; $binding['added_options'] = []; $binding['token_labels'] = [];
            $binding['lexical_policy'] = 'Retain the exact model explicitly requested by the visible canonical condition; no unrestricted synonym claim.';
        }
        unset($binding);
        return $document;
    }
    public static function validateWidgets(array $document): void
    {
        if ($document !== self::compileWidgetBindings($document)) { throw new RuntimeException('Native Some/Any widget teaching projection differs.'); }
    }
    public static function loadWidgets(): array
    {
        $file = self::root().'/'.self::WIDGET_BINDINGS;
        if (!is_file($file) || is_link($file) || !hash_equals(self::WIDGET_BINDINGS_SHA, hash_file('sha256', $file))) { throw new RuntimeException('Native Some/Any widget binding bytes differ.'); }
        static $document = null;
        if ($document === null) { $candidate = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR); self::validateWidgets($candidate); $document = $candidate; }
        return $document;
    }
    /** Exact canonical-theory clones only; IDs, answer instances, token pools and progress identities do not change. */
    public static function projectWidget(object $question, ?string $blockUuid, array $payload, bool $canonicalCaller = false): array
    {
        if (!$canonicalCaller || app()->getLocale() !== 'uk' || ($question->locale ?? 'uk') !== 'uk' || ($payload['type'] ?? null) !== '4'
            || (string) ($question->type ?? '') !== '4') { return $payload; }
        try {
            foreach (self::loadWidgets()['questions'] as $binding) {
                foreach (['id', 'uuid', 'seeder', 'type', 'level', 'question'] as $field) {
                    $value = $question->$field ?? null; if ($field === 'type') { $value = (string) $value; }
                    if ($value !== $binding[$field]) { continue 2; }
                }
                if ($blockUuid !== $binding['block_uuid'] || ($payload['id'] ?? null) !== $binding['id'] || ($payload['level'] ?? null) !== $binding['level']
                    || ($payload['question'] ?? null) !== $binding['question'] || ($payload['correct_tokens'] ?? null) !== $binding['correct_tokens']
                    || ($payload['answers'] ?? null) !== array_map(static fn ($a) => ['marker' => $a['marker'], 'correct' => $a['option']], $binding['answers'])
                    || ($payload['accepted_sentences'] ?? []) !== [] || !is_array($payload['options'] ?? null)) { return $payload; }
                if (method_exists($question, 'relationLoaded') && !$question->relationLoaded('answers')) { return $payload; }
                $answers = collect($question->answers ?? [])->sortBy('marker', SORT_NATURAL)->values();
                if ($answers->count() !== count($binding['answers'])) { return $payload; }
                foreach ($binding['answers'] as $i => $expected) {
                    $actual = $answers[$i];
                    if (method_exists($actual, 'relationLoaded') && !$actual->relationLoaded('option')) { return $payload; }
                    foreach (['id', 'question_id', 'marker', 'position', 'option_id'] as $field) { if (($actual->$field ?? null) !== $expected[$field]) { return $payload; } }
                    if (($actual->option->id ?? null) !== $expected['option_id'] || ($actual->option->option ?? null) !== $expected['option']) { return $payload; }
                }
                foreach ($binding['correct_tokens'] as $token) { if (!in_array($token, $payload['options'], true)) { return $payload; } }
                $payload['source_bound_compose_hint'] = true; $payload['compose_preanswer_hint'] = $binding['hint_uk'];
                $payload['saq_widget_hint_revision'] = self::digest($binding);
                return $payload;
            }
        } catch (Throwable) { /* Changed, foreign or unloaded models retain their complete original widget payload. */ }
        return $payload;
    }
}
