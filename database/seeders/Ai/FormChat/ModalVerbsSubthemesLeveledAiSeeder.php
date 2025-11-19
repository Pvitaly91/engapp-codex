<?php

namespace Database\Seeders\Ai\FormChat;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ModalVerbsSubthemesLeveledAiSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1, 'A2' => 2, 'B1' => 3, 'B2' => 4, 'C1' => 5, 'C2' => 5,
    ];

    /** Підтеми модалів: label для тегів + типовий набір опцій */
    private array $subthemes = [
        'ability' => [
            'label' => 'Modal Ability',
            'options' => ['can', 'could', 'be able to', 'may'],
        ],
        'permission' => [
            'label' => 'Modal Permission',
            'options' => ['may', 'can', "mustn't", 'should'],
        ],
        'obligation' => [
            'label' => 'Modal Obligation & Necessity',
            'options' => ['must', 'have to', "don’t have to", 'should'],
        ],
        'advice' => [
            'label' => 'Modal Advice & Suggestions',
            'options' => ['should', 'ought to', "had better", "shouldn’t"],
        ],
        'prohibition' => [
            'label' => 'Modal Prohibition',
            'options' => ["mustn't", "can't", "shouldn't", 'may not'],
        ],
        'possibility_present' => [
            'label' => 'Modal Possibility (Present)',
            'options' => ['might', 'may', 'could', 'can'],
        ],
        'possibility_past' => [
            'label' => 'Modal Possibility (Past)',
            'options' => ['might have', 'may have', 'could have', "should have"],
        ],
        'deduction_present' => [
            'label' => 'Modal Deduction (Present)',
            'options' => ['must', "can’t", 'might', 'could'],
        ],
        'deduction_past' => [
            'label' => 'Modal Deduction (Past)',
            'options' => ['must have', "can’t have", 'might have', 'could have'],
        ],
        'expectation' => [
            'label' => 'Expectation / Be supposed to',
            'options' => ['be supposed to', 'should', 'must', 'shall'],
        ],
        'requests_offers' => [
            'label' => 'Requests & Offers (Politeness)',
            'options' => ['Could', 'Would', 'Can', 'Shall'],
        ],
    ];

    /** Карта модальних пар для тегів (стисла версія) */
    private array $modalPairMap = [
        'can_could' => ['can', "can't", 'can have', "can't have", 'could', "couldn't", 'could have', "couldn’t have"],
        'may_might' => ['may', 'may have', 'may not', 'may not have', 'might', 'might have', 'might not', 'might not have'],
        'must_have_to' => ['must', "mustn't", 'must have', "mustn’t have", 'have to', 'has to', 'had to', "don't have to", "doesn't have to", "didn't have to"],
        'need_need_to' => ['need', 'need to', 'needs to', 'needed to', "needn't", 'need not', "needn't have"],
        'should_ought_to' => ['should', "shouldn't", 'should have', "shouldn't have", 'ought to', 'ought not to', 'ought to have', 'ought not to have', 'had better'],
        'will_would' => ['will', "won't", 'will have', "won't have", 'would', "wouldn't", 'would have', "wouldn't have", 'Shall', 'shall', "shan't"],
        'supposed_to' => ['be supposed to', 'supposed to'],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Modal Verbs AI — Subthemes & Levels'])->id;

        // Джерела по підтемаx: банк (10) + порівняльні (рівні)
        $sourceBank = [];
        $sourceLeveled = [];
        foreach ($this->subthemes as $key => $data) {
            $sourceBank[$key] = Source::firstOrCreate(['name' => "AI Modals — {$data['label']} (Bank)"])->id;
            $sourceLeveled[$key] = Source::firstOrCreate(['name' => "AI Modals — {$data['label']} (Leveled)"])->id;
        }

        // Теги
        $modalsTagId = Tag::firstOrCreate(['name' => 'Modal Verbs'], ['category' => 'English Grammar Theme'])->id;
        $themeTagIds = [];
        foreach ($this->subthemes as $key => $data) {
            $themeTagIds[$key] = Tag::firstOrCreate(['name' => $data['label']], ['category' => 'English Grammar Theme'])->id;
        }
        $pairTagIds = [];
        foreach ($this->modalPairMap as $pairKey => $keywords) {
            $pairTagIds[$pairKey] = Tag::firstOrCreate(
                ['name' => $this->pairName($pairKey)],
                ['category' => 'English Grammar Modal Pair']
            )->id;
        }
        // Теги для виправлених питань
        $fixedTagId = Tag::firstOrCreate(['name' => 'fixed'], ['category' => 'Question Status'])->id;

        // 1) Банк: по 10 питань на підтему
        $bankEntries = $this->buildPerSubthemeBank(10);

        // 2) Порівняльні: A1–C2 по 6 на кожний рівень підтеми
        $leveledEntries = $this->buildLeveledComparatives(6);

        $entries = array_merge($bankEntries, $leveledEntries);

        $items = [];
        $meta  = [];

        foreach ($entries as $i => $entry) {
            $level     = $entry['level'];
            $subKey    = $entry['subtheme'];
            $question  = $entry['question'];
            $answer    = (string) ($entry['answer'] ?? '');
            $options   = array_values(array_unique(array_map('strval', $entry['options'] ?? [])));
            $verbHint  = $this->normalizeHint($entry['verb_hint'] ?? null);

            $answersMap = ['a1' => $answer];
            $optionMarkerMap = [];
            foreach ($options as $opt) {
                $optionMarkerMap[$opt] = 'a1';
            }

            $example = $this->formatExample($question, $answersMap); // приклад без розкриття відповіді в підказках/поясненнях :contentReference[oaicite:6]{index=6}

            // Розширені підказки (без правильної відповіді)
            $hints = [
                $this->hintForSubtheme($subKey, $level),
                $this->hintForForm($subKey),
                "Приклад побудови речення: *{$example}*",
            ];

            // Пояснення для кожного варіанту (без розкриття правильної відповіді)
            $explanations = [];
            foreach ($options as $opt) {
                $explanations[$opt] = $this->explanationForOption($opt, $answer, $subKey, $level, $example);
            }

            // Теги: базовий + підтеми + модальні пари
            $tagIds = [$modalsTagId, $themeTagIds[$subKey]];
            $tagIds = array_values(array_unique(array_merge($tagIds, $this->detectPairTags($options, $pairTagIds))));
            
            // Додати теги для виправлених питань
            if (!empty($entry['fixed'])) {
                $tagIds[] = $fixedTagId;
                
                // Додати тег з інформацією про виправлення
                if (!empty($entry['correction'])) {
                    $correctionTagId = Tag::firstOrCreate(
                        ['name' => $entry['correction']], 
                        ['category' => 'Question Correction']
                    )->id;
                    $tagIds[] = $correctionTagId;
                }
            }

            $uuid = $this->generateQuestionUuid($level, $subKey, $entry['bucket'], $i + 1);

            $items[] = [
                'uuid'        => $uuid,
                'type'        => 0,               // вимога користувача
                'question'    => $question,
                'category_id' => $categoryId,
                'difficulty'  => $this->levelDifficulty[$level] ?? 3,
                'source_id'   => $entry['bucket'] === 'bank' ? $sourceBank[$subKey] : $sourceLeveled[$subKey],
                'flag'        => 2,               // вимога користувача та як у наявних AI сидерах :contentReference[oaicite:7]{index=7}
                'level'       => $level,
                'tag_ids'     => $tagIds,
                'answers'     => [[ 'marker' => 'a1', 'answer' => $answer, 'verb_hint' => $verbHint ]],
                'options'     => $options,
                'variants'    => [$question],
            ];

            $meta[] = [
                'uuid'            => $uuid,
                'answers'         => $answersMap,
                'option_markers'  => $optionMarkerMap,
                'hints'           => $hints,
                'explanations'    => $explanations,
            ];
        }

        // Єдиний запис у БД: питання + підказки + ChatGPT пояснення (згідно функціоналу базового класу) :contentReference[oaicite:8]{index=8}
        $this->seedQuestionData($items, $meta);
    }

    /** ---------- Генератори питань ---------- */

    private function buildPerSubthemeBank(int $perSubtheme): array
    {
        $levels = ['A1','A2','B1','B2','C1','C2'];
        $questions = [];

        foreach ($this->subthemes as $key => $cfg) {
            for ($i = 0; $i < $perSubtheme; $i++) {
                $level = $levels[$i % count($levels)];
                $tpl   = $this->templateFor($key, $i);
                $answer = $tpl['answer'];
                $opts   = $this->optionsFor($key, $tpl['distract'] ?? [], $answer);

                $questions[] = [
                    'bucket'   => 'bank',
                    'subtheme' => $key,
                    'level'    => $level,
                    'question' => $tpl['q'],
                    'answer'   => $answer,
                    'options'  => $opts,
                    'verb_hint'=> $tpl['hint'] ?? null,
                    'fixed'    => $tpl['fixed'] ?? false,
                    'correction' => $tpl['correction'] ?? null,
                ];
            }
        }

        return $questions;
    }

    private function buildLeveledComparatives(int $perLevel): array
    {
        $levels = ['A1','A2','B1','B2','C1','C2'];
        $questions = [];

        foreach ($this->subthemes as $key => $_) {
            foreach ($levels as $l) {
                for ($i = 0; $i < $perLevel; $i++) {
                    $tpl   = $this->leveledTemplateFor($key, $l, $i);
                    $answer = $tpl['answer'];
                    $opts   = $this->optionsFor($key, $tpl['distract'] ?? [], $answer);

                    $questions[] = [
                        'bucket'   => 'leveled',
                        'subtheme' => $key,
                        'level'    => $l,
                        'question' => $tpl['q'],
                        'answer'   => $answer,
                        'options'  => $opts,
                        'verb_hint'=> $tpl['hint'] ?? null,
                        'fixed'    => $tpl['fixed'] ?? false,
                        'correction' => $tpl['correction'] ?? null,
                    ];
                }
            }
        }

        return $questions;
    }

    /** Шаблони для банку (10 на підтему) */
    private function templateFor(string $sub, int $i): array
    {
        // Кілька коротких пулів під різні підтеми; вибір за індексом
        switch ($sub) {
            case 'ability':
                $pool = [
                    ['q' => 'She {a1} swim across the river.', 'answer' => 'can', 'hint' => 'she; swim', 'distract' => ['could','may','must']],
                    ['q' => 'When he was five, he {a1} read simple books.', 'answer' => 'could', 'hint' => 'he; read', 'distract' => ['can','may','must']],
                    ['q' => 'After training, they will {a1} finish the climb.', 'answer' => 'be able to', 'hint' => 'they; finish', 'distract' => ['can','must','may']],
                ];
                return $pool[$i % count($pool)];

            case 'permission':
                $pool = [
                    ['q' => '{a1} I borrow your bike?', 'answer' => 'May', 'hint' => 'I; borrow', 'distract' => ['Can',"Mustn’t",'Should']],
                    ['q' => 'You {a1} park here — it is allowed now.', 'answer' => 'can', 'hint' => 'you; park', 'distract' => ['may','must','should']],
                    ['q' => 'Students {a1} use phones during the exam.', 'answer' => "mustn't", 'hint' => 'students; use', 'distract' => ['can','should','may']],
                ];
                return $pool[$i % count($pool)];

            case 'obligation':
                $pool = [
                    ['q' => 'You {a1} wear a helmet on the site.', 'answer' => 'must', 'hint' => 'you; wear', 'distract' => ['should',"don’t have to",'may']],
                    ['q' => 'Employees {a1} submit reports by Friday.', 'answer' => 'have to', 'hint' => 'employees; submit', 'distract' => ['must','should','may']],
                    ['q' => 'You {a1} come on Sunday — it’s optional.', 'answer' => "don’t have to", 'hint' => 'you; come', 'distract' => ['must','should','may']],
                ];
                return $pool[$i % count($pool)];

            case 'advice':
                $pool = [
                    ['q' => 'You {a1} take an umbrella today.', 'answer' => 'should', 'hint' => 'you; take', 'distract' => ['must','can','may']],
                    ['q' => 'You {a1} to see a dentist about that tooth.', 'answer' => 'ought', 'hint' => 'you; see', 'distract' => ['should','must','may'], 'fixed' => true, 'correction' => 'ought to -> ought'],
                    ['q' => 'You {a1} not skip breakfast.', 'answer' => "had better", 'hint' => 'you; skip', 'distract' => ['should','must','may']],
                ];
                return $pool[$i % count($pool)];

            case 'prohibition':
                $pool = [
                    ['q' => 'You {a1} smoke here; it’s a hospital.', 'answer' => "mustn't", 'hint' => 'you; smoke', 'distract' => ["can't","shouldn't",'may not']],
                    ['q' => 'Visitors {a1} enter this area — it’s restricted.', 'answer' => "can’t", 'hint' => 'visitors; enter', 'distract' => ["mustn't","shouldn't",'may']],
                    ['q' => 'You {a1} talk during the test.', 'answer' => "shouldn’t", 'hint' => 'you; talk', 'distract' => ["mustn't","can’t",'may not']],
                ];
                return $pool[$i % count($pool)];

            case 'possibility_present':
                $pool = [
                    ['q' => 'It {a1} rain later — the sky is grey.', 'answer' => 'might', 'hint' => 'it; rain', 'distract' => ['may','can','could']],
                    ['q' => 'She {a1} be at home now; her car is outside.', 'answer' => 'may', 'hint' => 'she; be', 'distract' => ['might','can','could']],
                    ['q' => 'They {a1} arrive early if traffic is good.', 'answer' => 'could', 'hint' => 'they; arrive', 'distract' => ['may','can','might']],
                ];
                return $pool[$i % count($pool)];

            case 'possibility_past':
                $pool = [
                    ['q' => 'He {a1} missed the bus — that’s why he’s late.', 'answer' => 'might have', 'hint' => 'he; miss', 'distract' => ['may have','could have','should have']],
                    ['q' => 'She {a1} forgotten her keys.', 'answer' => 'may have', 'hint' => 'she; forget', 'distract' => ['might have','could have','should have']],
                    ['q' => 'They {a1} taken a different route.', 'answer' => 'could have', 'hint' => 'they; take', 'distract' => ['might have','may have','should have']],
                ];
                return $pool[$i % count($pool)];

            case 'deduction_present':
                $pool = [
                    ['q' => 'The lights are on — they {a1} be at home.', 'answer' => 'must', 'hint' => 'they; be', 'distract' => ['might',"can’t",'could']],
                    ['q' => 'It’s too quiet — the kids {a1} be sleeping.', 'answer' => 'might', 'hint' => 'kids; be', 'distract' => ['must',"can’t",'could']],
                    ['q' => 'He left five minutes ago — he {a1} be there yet.', 'answer' => "can’t", 'hint' => 'he; be', 'distract' => ['must','might','could']],
                ];
                return $pool[$i % count($pool)];

            case 'deduction_past':
                $pool = [
                    ['q' => 'The ground is wet — it {a1} rained at night.', 'answer' => 'must have', 'hint' => 'it; rain', 'distract' => ['might have',"can’t have",'could have']],
                    ['q' => 'She was at work — she {a1} been at home.', 'answer' => "can’t have", 'hint' => 'she; be', 'distract' => ['must have','might have','could have']],
                    ['q' => 'I didn’t see him — he {a1} left early.', 'answer' => 'might have', 'hint' => 'he; leave', 'distract' => ['must have','could have',"can’t have"]],
                ];
                return $pool[$i % count($pool)];

            case 'expectation':
                $pool = [
                    ['q' => 'You are {a1} be here by 9 a.m.', 'answer' => 'supposed to', 'hint' => 'you; be', 'distract' => ['must','should','shall']],
                    ['q' => 'Guests {a1} check out by noon.', 'answer' => 'should', 'hint' => 'guests; check out', 'distract' => ['must','shall','supposed to']],
                    ['q' => 'According to the plan, the train is {a1} depart at 10.', 'answer' => 'supposed to', 'hint' => 'train; depart', 'distract' => ['should','must','shall']],
                ];
                return $pool[$i % count($pool)];

            case 'requests_offers':
                $pool = [
                    ['q' => '{a1} you help me with this box?', 'answer' => 'Could', 'hint' => 'you; help', 'distract' => ['Would','Can','Shall']],
                    ['q' => '{a1} we open the window?', 'answer' => 'Shall', 'hint' => 'we; open', 'distract' => ['Can','Would','Could']],
                    ['q' => '{a1} you like some tea?', 'answer' => 'Would', 'hint' => 'you; like', 'distract' => ['Could','Can','Shall']],
                ];
                return $pool[$i % count($pool)];
        }

        return ['q' => 'You {a1} choose the best modal.', 'answer' => 'should', 'hint' => 'you; choose', 'distract' => ['must','can','may']];
    }

    /** Шаблони для порівняльного блоку (по рівнях) */
    private function leveledTemplateFor(string $sub, string $level, int $i): array
    {
        // Для вищих рівнів — складніші конструкції
        $isHigh = in_array($level, ['B2','C1','C2'], true);

        switch ($sub) {
            case 'ability':
                return $isHigh
                    ? ['q' => 'After the upgrade, the system will {a1} handle more requests.', 'answer' => 'be able to', 'hint' => 'system; handle', 'distract' => ['can','must','may']]
                    : ['q' => 'He {a1} lift this bag.', 'answer' => 'can', 'hint' => 'he; lift', 'distract' => ['could','must','may']];

            case 'permission':
                return $isHigh
                    ? ['q' => 'Due to policy changes, staff {a1} work remotely twice a week.', 'answer' => 'may', 'hint' => 'staff; work', 'distract' => ['can','should','must']]
                    : ['q' => '{a1} I open the door?', 'answer' => 'May', 'hint' => 'I; open', 'distract' => ['Can','Should','Must']];

            case 'obligation':
                return $isHigh
                    ? ['q' => 'Contractors {a1} submit invoices within 5 days.', 'answer' => 'must', 'hint' => 'contractors; submit', 'distract' => ['should',"don’t have to",'may']]
                    : ['q' => 'You {a1} wear a seat belt.', 'answer' => 'must', 'hint' => 'you; wear', 'distract' => ['should','may',"don’t have to"]];

            case 'advice':
                return $isHigh
                    ? ['q' => 'You {a1} reconsider the timeline given the risks.', 'answer' => 'should', 'hint' => 'you; reconsider', 'distract' => ['must','may','can']]
                    : ['q' => 'You {a1} drink more water.', 'answer' => 'should', 'hint' => 'you; drink', 'distract' => ['must','may','can']];

            case 'prohibition':
                return $isHigh
                    ? ['q' => 'Visitors {a1} enter the archive room without supervision.', 'answer' => "mustn't", 'hint' => 'visitors; enter', 'distract' => ["can’t","shouldn’t",'may not']]
                    : ['q' => 'You {a1} run in the corridor.', 'answer' => "mustn’t", 'hint' => 'you; run', 'distract' => ["can’t","shouldn’t",'may not']];

            case 'possibility_present':
                return $isHigh
                    ? ['q' => 'Given the data, the result {a1} be inconclusive.', 'answer' => 'might', 'hint' => 'result; be', 'distract' => ['may','could','can']]
                    : ['q' => 'It {a1} rain later.', 'answer' => 'might', 'hint' => 'it; rain', 'distract' => ['may','could','can']];

            case 'possibility_past':
                return $isHigh
                    ? ['q' => 'Due to delays, the team {a1} missed the deadline.', 'answer' => 'might have', 'hint' => 'team; miss', 'distract' => ['may have','could have','should have']]
                    : ['q' => 'She {a1} lost her keys.', 'answer' => 'may have', 'hint' => 'she; lose', 'distract' => ['might have','could have','should have']];

            case 'deduction_present':
                return $isHigh
                    ? ['q' => 'Everything is packed — they {a1} be ready to leave.', 'answer' => 'must', 'hint' => 'they; be', 'distract' => ['might',"can’t",'could']]
                    : ['q' => 'It’s too early — he {a1} be asleep yet.', 'answer' => "can’t", 'hint' => 'he; be', 'distract' => ['must','might','could']];

            case 'deduction_past':
                return $isHigh
                    ? ['q' => 'The streets are wet — it {a1} rained at night.', 'answer' => 'must have', 'hint' => 'it; rain', 'distract' => ['might have',"can’t have",'could have']]
                    : ['q' => 'She was at the office — she {a1} been at home.', 'answer' => "can’t have", 'hint' => 'she; be', 'distract' => ['must have','might have','could have']];

            case 'expectation':
                return $isHigh
                    ? ['q' => 'All participants are {a1} arrive by 10:00.', 'answer' => 'supposed to', 'hint' => 'participants; arrive', 'distract' => ['should','must','shall']]
                    : ['q' => 'You are {a1} be here on time.', 'answer' => 'supposed to', 'hint' => 'you; be', 'distract' => ['should','must','shall']];

            case 'requests_offers':
                return $isHigh
                    ? ['q' => '{a1} we schedule a brief sync?', 'answer' => 'Shall', 'hint' => 'we; schedule', 'distract' => ['Can','Could','Would']]
                    : ['q' => '{a1} you hold the door, please?', 'answer' => 'Could', 'hint' => 'you; hold', 'distract' => ['Would','Can','Shall']];
        }

        return ['q' => 'You {a1} pick the best modal.', 'answer' => 'should', 'hint' => 'you; pick', 'distract' => ['must','may','can']];
    }

    /** ---------- Пояснення, підказки, опції ---------- */

    private function optionsFor(string $sub, array $distract, string $answer): array
    {
        $base = $this->subthemes[$sub]['options'] ?? [];
        $pool = array_values(array_unique(array_merge([$answer], $distract, $base)));
        // 3–4 варіанти
        return array_slice($pool, 0, max(3, min(4, count($pool))));
    }

    private function hintForSubtheme(string $sub, string $level): string
    {
        $map = [
            'ability' => 'ОБЕРИ форму, що виражає **здібність/можливість** виконати дію.',
            'permission' => 'Зверни увагу на **дозвіл/заборону**; ввічливість теж важлива.',
            'obligation' => 'Подумай, чи йдеться про **обов’язок / необхідність** або її відсутність.',
            'advice' => 'Шукай **пораду / м’яке очікування**.',
            'prohibition' => 'Ідея — **заборона** певної дії.',
            'possibility_present' => 'Йдеться про **ймовірність у теперішньому**.',
            'possibility_past' => 'Йдеться про **ймовірність у минулому** (форма з *have*).',
            'deduction_present' => 'Потрібен **логічний висновок про теперішнє**.',
            'deduction_past' => 'Потрібен **логічний висновок про минуле** (форма з *have*).',
            'expectation' => 'Шукай **очікування/правила** (*be supposed to*, *should* тощо).',
            'requests_offers' => 'Фокус на **ввічливих проханнях / пропозиціях**.',
        ];
        return ($map[$sub] ?? 'Оціни модальне значення.') . " Рівень: {$level}.";
    }

    private function hintForForm(string $sub): string
    {
        $map = [
            'possibility_past' => 'Форма: modal + **have + V3**.',
            'deduction_past'   => 'Форма: modal + **have + V3**.',
            'requests_offers'  => 'У запитаннях модальне слово часто стоїть **перед підметом**.',
        ];
        return $map[$sub] ?? 'Пам’ятай: після модального — **база дієслова (V1)**.';
    }

    /** Пояснення без розкриття коректної відповіді у тексті */
    private function explanationForOption(string $option, string $answer, string $sub, string $level, string $example): string
    {
        $isCorrect = mb_strtolower(trim($option)) === mb_strtolower(trim($answer));

        if ($isCorrect) {
            return "✅ Правильна модальна форма для цього контексту. " .
                   $this->subthemeClause($sub) .
                   $this->levelClause($level) .
                   "\nПриклад: *{$example}*";
        }

        // Опис значення опції, щоб пояснити помилку — без підказки на правильний варіант
        $meaning = $this->optionMeaning($option);

        return "❌ Ця форма {$meaning}, але не відповідає комунікативній меті завдання. " .
               $this->subthemeClause($sub) .
               $this->levelClause($level) .
               "\nПриклад: *{$example}*";
    }

    private function subthemeClause(string $sub): string
    {
        $map = [
            'ability' => 'Потрібно передати **здібність/можливість**.',
            'permission' => 'Йдеться про **дозвіл/заборону**.',
            'obligation' => 'Йдеться про **обов’язок/необхідність**.',
            'advice' => 'Очікується **порада/рекомендація**.',
            'prohibition' => 'Очікується **заборона** дії.',
            'possibility_present' => 'Контекст — **ймовірність у теперішньому**.',
            'possibility_past' => 'Контекст — **ймовірність у минулому**.',
            'deduction_present' => 'Потрібен **логічний висновок про теперішнє**.',
            'deduction_past' => 'Потрібен **логічний висновок про минуле**.',
            'expectation' => 'Йдеться про **очікування/правила**.',
            'requests_offers' => 'Фокус — **ввічливе прохання/пропозиція**.',
        ];
        return ' ' . ($map[$sub] ?? '');
    }

    private function levelClause(string $level): string
    {
        $map = [
            'A1' => 'Рівень A1: проста форма.',
            'A2' => 'Рівень A2: базові модальні.',
            'B1' => 'B1: додаємо контекст/відтінки.',
            'B2' => 'B2: складніші нюанси.',
            'C1' => 'C1: тонкі семантичні різниці.',
            'C2' => 'C2: найвищий рівень точності.',
        ];
        return ' ' . ($map[$level] ?? '');
    }

    private function optionMeaning(string $option): string
    {
        $n = mb_strtolower(trim($option));
        // Дуже стислий «класифікатор» значень
        if (str_contains($n, 'supposed to')) return 'передає очікування/правило';
        if (str_contains($n, 'have to'))     return 'виражає зовнішній обов’язок';
        if (str_contains($n, 'must'))        return 'виражає сильний обов’язок / певний висновок';
        if (str_contains($n, 'ought'))       return 'звучить як моральна порада';
        if (str_contains($n, 'had better'))  return 'підказує наполегливу пораду з попередженням';
        if (str_contains($n, 'might have') || str_contains($n, 'may have') || str_contains($n, 'could have'))
            return 'описує можливість у минулому';
        if (str_contains($n, 'might') || str_contains($n, 'may') || str_contains($n, 'could'))
            return 'виражає ймовірність/можливість';
        if (str_contains($n, 'can’t have'))  return 'стверджує неможливість у минулому';
        if ($n === "can’t" || $n === "can't")return 'виражає неможливість/заборону';
        if (str_contains($n, 'can'))         return 'виражає загальну здатність або дозвіл';
        if (str_contains($n, "mustn"))       return 'виражає сувору заборону';
        if (str_contains($n, "shouldn"))     return 'радить не робити дію';
        if (str_contains($n, 'should'))      return 'виражає пораду/очікування';
        if (str_contains($n, 'be able to'))  return 'описує здатність (часто у складніших часах)';
        if (str_contains($n, 'shall'))       return 'позначає пропозицію/офіційні формулювання';
        if (str_contains($n, 'would'))       return 'ввічливі пропозиції/гіпотетичні ситуації';
        return 'не надає потрібного модального значення';
    }

    private function detectPairTags(array $options, array $pairTagIds): array
    {
        $matched = [];
        foreach ($this->modalPairMap as $pairKey => $keywords) {
            foreach ($options as $opt) {
                $norm = mb_strtolower($opt);
                foreach ($keywords as $kw) {
                    $kw = mb_strtolower($kw);
                    if ($kw !== '' && str_contains($norm, $kw) && isset($pairTagIds[$pairKey])) {
                        $matched[] = $pairTagIds[$pairKey];
                        break 2;
                    }
                }
            }
        }
        return array_values(array_unique($matched));
    }

    private function pairName(string $key): string
    {
        return match ($key) {
            'can_could' => 'Can / Could',
            'may_might' => 'May / Might',
            'must_have_to' => 'Must / Have to',
            'need_need_to' => 'Need / Need to',
            'should_ought_to' => 'Should / Ought to',
            'will_would' => 'Will / Would',
            'supposed_to' => 'Be Supposed To',
            default => 'Modal Pair',
        };
    }
}
