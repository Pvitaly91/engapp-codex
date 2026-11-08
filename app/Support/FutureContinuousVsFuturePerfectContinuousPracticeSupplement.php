<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Finite render-only teaching supplement. Frozen M45 content and courses stay unchanged. */
final class FutureContinuousVsFuturePerfectContinuousPracticeSupplement
{
    public const SOURCE = 'docs/content/future-continuous-vs-future-perfect-continuous-practice-supplement.v1.json';
    public const SOURCE_SHA = '712956d03f92a8d73c2388ed5a207556947e446a4ceaf2840c6ec2f4dd3ac1c1';
    public const OWNER = 'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder';
    public const URL = 'http://gramlyze.loc/theory/maibutni-formy/future-continuous-vs-future-perfect-continuous';
    public const WIDGET_BINDINGS = 'docs/content/future-continuous-comparison-widget-bindings.v1.json';
    public const WIDGET_BINDINGS_SHA = '80f5d9e2dc18686aa735c52da3395df084c82aa90ff33c03b805919168abc214';

    private static function root(): string { return dirname(__DIR__, 2); }
    public static function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
    private static function html(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    private static function prompt(string $title, string $instruction, ?string $context = null): string
    {
        return '<h4>'.self::html($title).'</h4><p lang="uk" data-practice-instruction>'.self::html($instruction).'</p>'
            .($context === null ? '' : '<p lang="uk">'.self::html($context).'</p>');
    }
    private static function manual(string $id, string $kind, string $label, string $condition, string $answer,
        string $hint, array $accepted = [], array $tokens = []): array
    {
        $control = ['id' => $id, 'kind' => 'manual', 'source_kind' => $kind === 'order' ? 'tokens' : 'manual',
            'practice_kind' => $kind, 'label' => $label, 'required' => true,
            $kind === 'gap' ? 'stimulus_en' : 'stimulus_uk' => $condition,
            'verb_hint' => $hint, 'options' => [], 'answer' => $answer,
            'accepted' => array_values(array_unique([$answer, ...$accepted])), 'tokens' => $tokens];
        return $control;
    }

    /** Reviewed new native targets are deliberately independent from the mixed question bank. */
    public static function additions(): array
    {
        return [
            ['id' => 'fc-fpc-native-gaps', 'title' => 'Доповни речення',
                'instruction' => 'Введи лише пропущену дієслівну форму.',
                'controls' => [
                    self::manual('fc-fpc-native-gap-1', 'gap', 'Процес о восьмій',
                        'At eight tomorrow, the curator ____ the vase.', 'will be examining',
                        'Оглядати, уважно перевіряти: передай процес саме о цій майбутній годині, без підрахунку попереднього часу.',
                        ['will be inspecting']),
                    self::manual('fc-fpc-native-gap-2', 'gap', 'Дванадцять годин до півночі',
                        'By midnight, the rescue team ____ for twelve hours.', 'will have been searching',
                        'Шукати: передай дванадцять годин, які вже минуть до півночі; введи всі потрібні допоміжні дієслова.'),
                ],
                'feedback' => 'У першому реченні показано процес у майбутній момент: will be examining (або will be inspecting). У другому рахуємо його тривалість до півночі: will have been searching.'],
            ['id' => 'fc-fpc-native-translations', 'title' => 'Переклади речення',
                'instruction' => 'Склади англійське речення з токенів або введи його повністю.',
                'controls' => [
                    self::manual('fc-fpc-native-translation-1', 'translation', 'Процес завтра опівдні',
                        'Завтра опівдні сусіди висаджуватимуть троянди. Почни з часу.',
                        'At noon tomorrow, the neighbours will be planting roses.',
                        'Висаджувати троянди: покажи процес саме опівдні, не його накопичену тривалість; обидва звичайні написання назви сусідів приймаються.',
                        ['At noon tomorrow, the neighbors will be planting roses.',
                            'Tomorrow at noon, the neighbours will be planting roses.',
                            'Tomorrow at noon, the neighbors will be planting roses.'],
                        ['At noon tomorrow,', 'the neighbours', 'will be planting', 'roses.']),
                    self::manual('fc-fpc-native-translation-2', 'translation', 'Місяць реставрації до неділі',
                        'До неділі я реставруватиму цю скрипку вже місяць. Почни з терміну.',
                        'By Sunday, I will have been restoring this violin for a month.',
                        'Реставрувати цю скрипку: передай місяць роботи до неділі; можна скоротити допоміжне дієслово після займенника.',
                        ["By Sunday, I'll have been restoring this violin for a month.",
                            'By Sunday, I will have been restoring this violin for one month.',
                            "By Sunday, I'll have been restoring this violin for one month."],
                        ['By Sunday,', 'I', 'will have been', 'restoring this violin', 'for a month.']),
                ],
                'feedback' => 'Процес опівдні: At noon tomorrow, the neighbours (neighbors) will be planting roses. Накопичена тривалість: By Sunday, I will have been restoring this violin for a month.'],
            ['id' => 'fc-fpc-native-order', 'title' => 'Упорядкуй слова',
                'instruction' => 'Склади речення з груп слів або введи його вручну.',
                'controls' => [
                    self::manual('fc-fpc-native-order-1', 'order', 'Шість годин до заходу сонця',
                        'До заходу сонця волонтери прибиратимуть пляж уже шість годин. Почни з часу.',
                        'By sunset, the volunteers will have been clearing the beach for six hours.',
                        'Прибирати пляж: назви період роботи до заходу сонця; звичайний синонім прибирання також приймається.',
                        ['By sunset, the volunteers will have been cleaning the beach for six hours.',
                            'By sunset, the volunteers will have been clearing the beach for 6 hours.',
                            'By sunset, the volunteers will have been cleaning the beach for 6 hours.'],
                        ['By sunset,', 'the volunteers', 'will have been', 'clearing the beach', 'for six hours.']),
                ],
                'feedback' => 'До майбутнього моменту вже мине шість годин роботи: By sunset, the volunteers will have been clearing (cleaning) the beach for six hours.'],
        ];
    }

    /** Twelve reviewed old B2 teaching rows; no automatic word-order permutations. */
    private static function widgets(): array
    {
        $path = self::root().'/'.self::WIDGET_BINDINGS;
        if (!is_file($path) || is_link($path) || !hash_equals(self::WIDGET_BINDINGS_SHA, hash_file('sha256', $path))) {
            throw new RuntimeException('Future comparison widget teaching bindings differ.');
        }
        $bindings = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $definitionPath = self::root().'/'.$bindings['definition'];
        if (!is_file($definitionPath) || is_link($definitionPath)
            || !hash_equals($bindings['definition_sha256'], hash_file('sha256', $definitionPath))) {
            throw new RuntimeException('Future comparison old widget definition differs.');
        }
        $source = json_decode(file_get_contents($definitionPath), true, flags: JSON_THROW_ON_ERROR);
        $rows = array_values(array_filter($source['questions'], static fn ($q) => $q['level'] === 'B2'));
        $specs = [
            ['Працюватиму саме о шостій завтра: покажи процес у цей момент, не попередні години роботи.',
                ['I will be working at six tomorrow', 'I will be working tomorrow at six',
                    'Tomorrow at six I will be working', 'Tomorrow I will be working at six'], []],
            ['До шостої завтра вже мине вісім годин роботи: передай накопичену тривалість.',
                ['I will have been working for eight hours by six tomorrow',
                    'By tomorrow at six I will have been working for eight hours',
                    'Tomorrow by six I will have been working for eight hours'], []],
            ['Подорожуватимемо саме о цій порі наступного тижня; обидва звичайні написання дієслова приймаються.',
                ['We will be travelling this time next week', 'This time next week we will be traveling',
                    'We will be traveling this time next week', 'At this time next week we will be travelling',
                    'At this time next week we will be traveling', 'Next week at this time we will be travelling',
                    'Next week at this time we will be traveling'], ['travelling' => 'travelling (traveling)']],
            ['До цієї пори наступного тижня подорож уже триватиме три дні; передай саме цей накопичений період.',
                ['We will have been travelling for three days by this time next week',
                    'By this time next week we will have been traveling for three days',
                    'We will have been traveling for three days by this time next week'], ['travelling' => 'travelling (traveling)']],
            ['Складатиме вже названий іспит саме опівдні: покажи процес, а не завершений результат; звичайні синоніми складання іспиту приймаються.',
                ['She will be taking the exam at noon', 'At noon she will be sitting the exam',
                    'She will be sitting the exam at noon', 'At noon she will be sitting for the exam',
                    'She will be sitting for the exam at noon'], ['taking' => 'taking (sitting)']],
            ['До полудня складання вже названого іспиту триватиме дві години; передай тривалість, не завершення іспиту.',
                ['She will have been taking the exam for two hours by noon',
                    'By noon she will have been sitting the exam for two hours',
                    'She will have been sitting the exam for two hours by noon',
                    'By noon she will have been sitting for the exam for two hours',
                    'She will have been sitting for the exam for two hours by noon'], ['taking' => 'taking (sitting)']],
            ['До п’ятниці вже мине місяць тренувань; передай накопичений період. Звичайні синоніми тренування приймаються.',
                ['He will have been training for a month by Friday', 'By Friday he will have been practising for a month',
                    'He will have been practising for a month by Friday', 'By Friday he will have been practicing for a month',
                    'He will have been practicing for a month by Friday'], ['training' => 'training (practising / practicing)']],
            ['Тренуватиметься з уже названою командою в п’ятницю: покажи процес у цей день, без підрахунку попередньої тривалості.',
                ['He will be training with the team on Friday', 'On Friday he will be practising with the team',
                    'He will be practising with the team on Friday', 'On Friday he will be practicing with the team',
                    'He will be practicing with the team on Friday'], ['training' => 'training (practising / practicing)']],
            ['Чекатиму надворі саме о дев’ятій: покажи процес у цей момент; обидва звичайні позначення перебування надворі приймаються.',
                ['I will be waiting outside at nine', 'At nine I will be waiting outdoors',
                    'I will be waiting outdoors at nine'], ['outside' => 'outside (outdoors)']],
            ['До дев’ятої вже мине тридцять хвилин очікування; назви цей накопичений період.',
                ['I will have been waiting for thirty minutes by nine'], []],
            ['Представлятиму вже відомі результати під час зустрічі: покажи процес; звичайні синоніми представлення приймаються.',
                ['I will be presenting the results during the meeting', 'During the meeting I will be showing the results',
                    'I will be showing the results during the meeting', 'During the meeting I will be reporting the results',
                    'I will be reporting the results during the meeting'], ['presenting' => 'presenting (showing / reporting)']],
            ['До зустрічі підготовка вже триватиме два тижні; передай накопичений період. Звичайний синонім підготовки приймається.',
                ['I will have been preparing for two weeks by the meeting',
                    'By the meeting I will have been getting ready for two weeks',
                    'I will have been getting ready for two weeks by the meeting'], ['preparing' => 'preparing (getting ready)']],
        ];
        if (count($rows) !== 12 || count($bindings['questions']) !== 12) { throw new RuntimeException('Old B2 widget scope differs.'); }
        $widgets = [];
        foreach ($bindings['questions'] as $i => $binding) {
            $row = $rows[$i];
            if ($binding['uuid'] !== $row['uuid'] || $binding['question'] !== $row['question'] || $binding['level'] !== 'B2'
                || $binding['type'] !== '4' || $binding['seeder'] !== $source['seeder']['class']
                || array_column($binding['answers'], 'marker') !== array_keys($row['answers'])) {
                throw new RuntimeException('Old widget definition/teaching binding differs.');
            }
            foreach ($binding['answers'] as $p => $answer) {
                $expected = array_values($row['answers'])[$p];
                // Shared option rows have existing case-insensitive uniqueness.
                // The exact original spelling remains pinned below for runtime.
                if (mb_strtolower($answer['option']) !== mb_strtolower($expected)) {
                    throw new RuntimeException('Old widget canonical marker differs.');
                }
            }
            [$hint, $aliases, $labels] = $specs[$i];
            $known = array_map('mb_strtolower', $row['tokens_correct']); $extras = [];
            foreach ($aliases as $alias) {
                foreach (preg_split('/\s+/u', $alias) as $word) {
                    if (!in_array(mb_strtolower($word), $known, true)) { $extras[] = $word; $known[] = mb_strtolower($word); }
                }
            }
            $widgets[] = $binding + ['block_uuid' => '79b8a425-a798-5270-9813-072a7c5294d3',
                'definition' => $bindings['definition'], 'definition_sha256' => $bindings['definition_sha256'],
                'correct_tokens' => $row['tokens_correct'], 'hint_uk' => $hint,
                'accepted_sentences' => array_values(array_unique([implode(' ', $row['tokens_correct']), ...$aliases])),
                'added_options' => $extras, 'token_labels' => $labels];
        }
        return $widgets;
    }

    public static function compile(): array
    {
        [, $release] = M45FutureComparisonsPackage::load(self::root());
        $target = $release['targets'][2];
        if ($target['identity'] !== self::OWNER) { throw new RuntimeException('Future comparison native owner differs.'); }
        $definition = $target['path'];
        $definitionBytes = file_get_contents(self::root().'/'.$definition);
        if (!is_string($definitionBytes) || json_decode($definitionBytes, true, flags: JSON_THROW_ON_ERROR) !== $target['after']) {
            throw new RuntimeException('Future comparison native definition differs.');
        }
        $stored = $target['after']['page']['blocks'][7];
        $before = json_decode($stored['body'], true, flags: JSON_THROW_ON_ERROR);
        if (count($before['cases']) !== 6 || array_column($before['cases'], 'source_index') !== range(1, 6)) {
            throw new RuntimeException('Future comparison native cases differ.');
        }
        $after = $before;
        $after['author_self_check']['intro'] = '<p lang="uk">Виконай завдання й натисни «Перевірити».</p>';
        $short = [
            ['Процес об одинадцятій', 'Обери форму для процесу саме об одинадцятій.',
                'Завтра археологи копатимуть від сьомої ранку. Гід описує лише те, що відбуватиметься об одинадцятій.'],
            ['Три години радіоефіру', 'Переклади повне речення, починаючи з терміну.',
                'Завтра ефір почнеться о шостій і ще триватиме о дев’ятій.'],
            ['Тимчасовий офіс', 'Обери, що означає названий тиждень.',
                'У листопаді основний офіс закриють на ремонт.'],
            ['Питання про процес', 'Склади питання з усіх груп слів або введи його вручну.', null],
            ['Виправ форму', 'Перепиши повне речення, відновивши пропущене допоміжне дієслово.', null],
            ['Чи відомий кінець роботи?', 'Обери висновок, якого зробити не можна.',
                'Завтра фарбування почнеться о дев’ятій і ще триватиме опівдні.'],
        ];
        foreach ($short as $i => [$title, $instruction, $context]) {
            $after['author_self_check']['prompts'][$i] = self::prompt($title, $instruction, $context);
        }
        // Keep every old answer, ID and source index. Only visible stimuli and finite aliases change.
        $after['cases'][0]['controls'][0]['stimulus_en'] = 'At 11 tomorrow, the team ___ at the site.';
        unset($after['cases'][1]['controls'][0]['stimulus_en']);
        $after['cases'][1]['controls'][0]['stimulus_uk'] = 'До дев’ятої завтра вона вестиме радіоефір уже три години.';
        $after['cases'][1]['controls'][0]['practice_kind'] = 'translation';
        $after['cases'][1]['controls'][0]['verb_hint'] = 'Почни з терміну до дев’ятої завтра. Збережи подану лексику про ефір і передай вже накопичені три години.';
        $after['cases'][1]['controls'][0]['tokens'] = ['By 9 tomorrow,', 'she', 'will have been', 'broadcasting', 'for three hours.'];
        $after['cases'][3]['controls'][0]['practice_kind'] = 'order';
        $after['cases'][3]['controls'][0]['stimulus_uk'] = 'Що вони обговорюватимуть завтра о другій годині дня?';
        $after['cases'][3]['controls'][0]['verb_hint'] = 'Запитай про обговорення саме в цей майбутній момент; постав питальне слово на початку.';
        // The existing punctuation policy spaces the abbreviation p.m. into two
        // fragments. A finite pm spelling keeps this one time group readable.
        $after['cases'][3]['controls'][0]['tokens'] = ['What', 'will they', 'be discussing', 'at 2 pm', 'tomorrow?'];
        $after['cases'][3]['controls'][0]['accepted'][] = 'What will they be discussing tomorrow at 2 p.m.?';
        $after['cases'][3]['controls'][0]['accepted'][] = 'What will they be discussing at 2 pm tomorrow?';
        $after['cases'][3]['controls'][0]['accepted'][] = 'What will they be discussing tomorrow at 2 pm?';
        $after['cases'][4]['controls'][0]['verb_hint'] = 'Віднови ланцюжок допоміжних дієслів для тривалості тренування до п’ятниці; решту слів збережи.';

        foreach (self::additions() as $i => $task) {
            $position = 7 + $i;
            $after['cases'][] = ['id' => $task['id'], 'source_index' => $position,
                'scoring' => 'all_required_controls', 'interaction' => count($task['controls']) > 1 ? 'compound' : 'manual',
                'controls' => $task['controls']];
            $after['author_self_check']['prompts'][] = self::prompt($task['title'], $task['instruction']);
            $after['author_self_check']['answers'][] = '<p lang="uk">'.self::html($task['feedback']).'</p>';
        }
        $after['future_continuous_comparison_native'] = ['revision' => 'v1', 'old_cases' => 6, 'added_controls' => 5];
        $uuid = M26DetailPackage::uuid(self::OWNER, $stored, 8);
        if ($uuid !== '79b8a425-a798-5270-9813-072a7c5294d3') { throw new RuntimeException('Native UUID differs.'); }
        return ['schema_version' => 1, 'local_url' => self::URL,
            'definition' => $definition, 'definition_sha256' => hash('sha256', $definitionBytes),
            'release' => M45FutureComparisonsPackage::SOURCE, 'release_sha256' => M45FutureComparisonsPackage::SOURCE_SHA,
            'master' => M45FutureComparisonsPackage::MASTER_PATH, 'master_sha256' => M45FutureComparisonsPackage::MASTER_SHA,
            'binding' => ['seeder' => self::OWNER, 'page_id' => 59, 'page_category_id' => 12, 'locale' => 'uk',
                'uuid' => $uuid, 'type' => 'practice-set', 'column' => 'left', 'sort_order' => 8, 'level' => 'B2',
                'heading' => null, 'css_class' => null, 'body_sha256' => hash('sha256', $stored['body'])],
            'before_data_sha256' => self::digest($before), 'after_data_sha256' => self::digest($after),
            'before_data' => $before, 'after_data' => $after,
            'added_control_ids' => array_merge(...array_map(static fn ($task) => array_column($task['controls'], 'id'), self::additions())),
            'widget_bindings' => self::WIDGET_BINDINGS, 'widget_bindings_sha256' => self::WIDGET_BINDINGS_SHA,
            'widgets' => self::widgets()];
    }

    public static function load(): array
    {
        $path = self::root().'/'.self::SOURCE;
        if (!is_file($path) || is_link($path) || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $path))) {
            throw new RuntimeException('Future comparison native supplement bytes differ.');
        }
        static $verified = null;
        if ($verified === null) {
            $document = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            self::validate($document); $verified = $document;
        }
        foreach (['definition', 'release', 'master', 'widget_bindings'] as $kind) {
            $source = self::root().'/'.$verified[$kind];
            if (!is_file($source) || is_link($source) || !hash_equals($verified[$kind.'_sha256'], hash_file('sha256', $source))) {
                throw new RuntimeException('Future comparison native source pin differs.');
            }
        }
        foreach ($verified['widgets'] as $widget) {
            $source = self::root().'/'.$widget['definition'];
            if (!is_file($source) || is_link($source) || !hash_equals($widget['definition_sha256'], hash_file('sha256', $source))) {
                throw new RuntimeException('Future comparison widget source pin differs.');
            }
        }
        return $verified;
    }
    public static function validate(array $document): void
    {
        if ($document !== self::compile()) { throw new RuntimeException('Future comparison finite native projection differs.'); }
    }
    private static function entry(object $block, array $data, bool $projected): ?array
    {
        if (app()->getLocale() !== 'uk' || ($block->seeder ?? null) !== self::OWNER
            || ($block->locale ?? null) !== 'uk' || ($block->type ?? null) !== 'practice-set') { return null; }
        try {
            $target = self::load();
            foreach ($target['binding'] as $field => $value) {
                if ($field !== 'body_sha256' && ($block->$field ?? null) !== $value) { return null; }
            }
            if (is_string($block->body ?? null) && hash_equals($target['binding']['body_sha256'], hash('sha256', $block->body))
                && $data === $target[$projected ? 'after_data' : 'before_data']) { return $target; }
        } catch (Throwable) { /* Changed or foreign content keeps the complete original practice. */ }
        return null;
    }
    public static function project(object $block, array $data, bool $canonicalCaller): array
    {
        return $canonicalCaller ? (self::entry($block, $data, false)['after_data'] ?? $data) : $data;
    }
    public static function isProjected(object $block, array $data): bool
    {
        return self::entry($block, $data, true) !== null;
    }

    /** Exact old question/loaded marker bindings only; no stored question, answer, token or progress mutation. */
    public static function projectWidget(object $question, ?string $blockUuid, array $payload, bool $canonicalCaller = false): array
    {
        if (!$canonicalCaller || app()->getLocale() !== 'uk' || ($question->locale ?? 'uk') !== 'uk'
            || ($payload['type'] ?? null) !== '4' || (string) ($question->type ?? '') !== '4') { return $payload; }
        try {
            foreach (self::load()['widgets'] as $binding) {
                foreach (['id', 'uuid', 'seeder', 'type', 'level', 'question'] as $field) {
                    $value = $question->$field ?? null; if ($field === 'type') { $value = (string) $value; }
                    if ($value !== $binding[$field]) { continue 2; }
                }
                if ($blockUuid !== $binding['block_uuid'] || ($payload['id'] ?? null) !== $binding['id']
                    || ($payload['level'] ?? null) !== $binding['level'] || ($payload['question'] ?? null) !== $binding['question']
                    || ($payload['correct_tokens'] ?? null) !== $binding['correct_tokens']
                    || ($payload['accepted_sentences'] ?? []) !== [] || !is_array($payload['options'] ?? null)) { return $payload; }
                if (method_exists($question, 'relationLoaded') && !$question->relationLoaded('answers')) { return $payload; }
                $loaded = collect($question->answers ?? [])->sortBy('marker', SORT_NATURAL)->values();
                if ($loaded->count() !== count($binding['answers']) || count($payload['answers'] ?? []) !== $loaded->count()) { return $payload; }
                foreach ($binding['answers'] as $i => $answer) {
                    $actual = $loaded[$i]; $shown = $payload['answers'][$i]; $option = $actual->option ?? null;
                    if (method_exists($actual, 'relationLoaded') && !$actual->relationLoaded('option')) { return $payload; }
                    if (($actual->question_id ?? null) !== $binding['id'] || ($actual->marker ?? null) !== $answer['marker']
                        || ($actual->option_id ?? null) !== $answer['option_id'] || ($option->id ?? null) !== $answer['option_id']
                        || ($option->option ?? null) !== $answer['option'] || ($shown['marker'] ?? null) !== $answer['marker']
                        || ($shown['correct'] ?? null) !== $answer['option']) { return $payload; }
                }
                foreach ($binding['correct_tokens'] as $token) { if (!in_array($token, $payload['options'], true)) { return $payload; } }
                $payload['accepted_sentences'] = $binding['accepted_sentences'];
                $payload['options'] = array_values(array_unique([...$payload['options'], ...$binding['added_options']]));
                $payload['derived_compose_options'] = array_values(array_unique([...($payload['derived_compose_options'] ?? []), ...$binding['added_options']]));
                $payload['token_labels'] = array_replace($payload['token_labels'] ?? [], $binding['token_labels']);
                $payload['source_bound_compose_hint'] = true; $payload['compose_preanswer_hint'] = $binding['hint_uk'];
                $payload['fccq_widget_alias_revision'] = self::digest($binding);
                return $payload;
            }
        } catch (Throwable) { /* A changed or unloaded source retains the complete original widget payload. */ }
        return $payload;
    }
}
