<?php

namespace Database\Seeders;

use App\Models\SiteTreeItem;
use App\Models\SiteTreeVariant;
use Illuminate\Database\Seeder;

class SiteTreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create base variant
        $baseVariant = SiteTreeVariant::firstOrCreate(
            ['is_base' => true],
            [
                'name' => 'Базова структура',
                'slug' => 'base',
            ]
        );

        $tree = $this->getTreeData();

        foreach ($tree as $sortOrder => $section) {
            $this->createOrUpdateItem($section, null, $sortOrder, $baseVariant->id);
        }
    }

    private function createOrUpdateItem(array $data, ?int $parentId, int $sortOrder, int $variantId): void
    {
        $item = SiteTreeItem::updateOrCreate(
            [
                'variant_id' => $variantId,
                'title' => $data['title'],
                'parent_id' => $parentId,
            ],
            [
                'level' => $data['level'] ?? null,
                'is_checked' => true,
                'sort_order' => $sortOrder,
            ]
        );

        if (isset($data['children'])) {
            foreach ($data['children'] as $childSortOrder => $child) {
                $this->createOrUpdateItem($child, $item->id, $childSortOrder, $variantId);
            }
        }
    }

    private function getTreeData(): array
    {
        return [
            [
                'title' => '1. Базова граматика',
                'level' => 'A1',
                'children' => [
                    ['title' => 'Parts of speech — Частини мови', 'level' => 'A1'],
                    ['title' => 'Sentence structure — Будова простого речення (S–V–O)', 'level' => 'A1'],
                    ['title' => 'Sentence types — види речень (стверджувальні, заперечні, питальні, наказові)', 'level' => 'A1'],
                    ['title' => 'Imperatives — наказові речення (Sit down!, Don\'t open it)', 'level' => 'A1'],
                    ['title' => 'Basic conjunctions — and, but, or, because, so', 'level' => 'A1–A2'],
                    [
                        'title' => 'Word Order — Порядок слів',
                        'level' => 'A1–B2',
                        'children' => [
                            ['title' => 'Basic word order in statements — Порядок слів у ствердженні', 'level' => 'A1'],
                            ['title' => 'Word order in questions and negatives — Питання та заперечення', 'level' => 'A1–A2'],
                            ['title' => 'Word order with adverbs and adverbials — Прислівники та обставини', 'level' => 'A2–B1'],
                            ['title' => 'Word order with verbs and objects — Допоміжні, модальні, фразові дієслова', 'level' => 'A2–B1'],
                            ['title' => 'Advanced word order and emphasis — Інверсія та підсилення', 'level' => 'B1–B2'],
                        ],
                    ],
                ],
            ],
            [
                'title' => '2. Іменники, артиклі та кількість',
                'level' => 'A1–C1',
                'children' => [
                    ['title' => 'Countable vs Uncountable nouns — Злічувані / незлічувані', 'level' => 'A1–A2'],
                    ['title' => 'Articles A / An / The — Артиклі', 'level' => 'A1–A2'],
                    ['title' => 'Plural nouns — множина іменників (s, es, ies)', 'level' => 'A1'],
                    ['title' => 'Zero article — Нульовий артикль', 'level' => 'A2–B1'],
                    ['title' => 'Quantifiers — Much, Many, A Lot, Few, Little', 'level' => 'A2'],
                    ['title' => 'Few / A few / Little / A little — тонкі відмінності', 'level' => 'A2–B1'],
                    ['title' => 'Partitives with uncountable nouns — a piece of, a cup of…', 'level' => 'A2–B1'],
                    ['title' => 'No / None / Neither / Either як означники кількості', 'level' => 'B1'],
                    ['title' => 'Some / Any — Кількість у ствердженні та запереченні', 'level' => 'A1–A2'],
                    ['title' => 'Some / Any — Люди', 'level' => 'A2'],
                    ['title' => 'Some / Any — Місця', 'level' => 'A2'],
                    ['title' => 'Some / Any — Речі', 'level' => 'A2'],
                    ['title' => 'Articles with geographical names — артиклі з географічними назвами', 'level' => 'B2'],
                    ['title' => 'Advanced articles — узагальнення, generic reference (the rich, a tiger, ∅ people)', 'level' => 'C1'],
                ],
            ],
            [
                'title' => '3. Займенники та вказівні слова',
                'level' => 'A1–C1',
                'children' => [
                    ['title' => 'Pronouns — займенники', 'level' => 'A1'],
                    ['title' => "Personal & object pronouns — особові й об'єктні", 'level' => 'A1'],
                    ['title' => 'Possessive adjectives vs pronouns — my / mine, your / yours…', 'level' => 'A1–A2'],
                    ['title' => 'Indefinite pronouns — someone, anyone, nobody, nothing', 'level' => 'A2–B1'],
                    ['title' => 'Reflexive pronouns — myself, yourself, themselves…', 'level' => 'A2–B1'],
                    ['title' => 'Relative pronouns — who, which, that, whose…', 'level' => 'B1'],
                    ['title' => 'Each / Every / All — відмінності', 'level' => 'B1'],
                    ['title' => 'Reciprocal pronouns — each other, one another', 'level' => 'B1'],
                    ['title' => 'One / Ones — заміна іменників', 'level' => 'B2'],
                    ['title' => 'Whatever / Whenever / Whoever — невизначені відносні займенники', 'level' => 'C1'],
                    ['title' => 'This / That / These / Those — Вказівні займенники', 'level' => 'A1'],
                ],
            ],
            [
                'title' => '4. Verb to be та базові конструкції',
                'level' => 'A1–A2',
                'children' => [
                    ['title' => 'Verb to be — дієслово «бути»', 'level' => 'A1'],
                    ['title' => 'Contractions — скорочені форми в англійській', 'level' => 'A1'],
                    ['title' => 'There is / There are — наявність предметів', 'level' => 'A1'],
                    ['title' => 'It is vs There is — формальний підмет', 'level' => 'A2'],
                    ['title' => 'It as a formal subject — погода, час, відстань (It is raining, It is late)', 'level' => 'A2'],
                    ['title' => 'Do / Does vs. Is / Are — вибір допоміжного дієслова', 'level' => 'A1–A2'],
                ],
            ],
            [
                'title' => '5. Дієслова та володіння',
                'level' => 'A1–C2',
                'children' => [
                    ['title' => 'Regular verbs — правильні дієслова (-ed)', 'level' => 'A1'],
                    ['title' => 'Pronunciation of -ed — вимова /t/ /d/ /ɪd/', 'level' => 'A2–B1'],
                    ['title' => 'Spelling of -ed and -ing endings — play → played, stop → stopping', 'level' => 'A2'],
                    ['title' => 'Irregular verbs — неправильні дієслова', 'level' => 'A2'],
                    ['title' => 'Stative vs dynamic verbs — дієслова стану / дії', 'level' => 'B1'],
                    ['title' => 'Linking verbs — seem, look, get + adjective', 'level' => 'B1'],
                    ['title' => 'Have got — володіння й характеристики', 'level' => 'A1'],
                    ['title' => 'Have vs Have got — різниця у вживанні', 'level' => 'A1–A2'],
                    ['title' => 'Possessive \'s vs of — John\'s car, the car of my friend', 'level' => 'A2'],
                    ['title' => 'Gerund vs Infinitive — stop doing vs stop to do', 'level' => 'B1'],
                    ['title' => 'Verb patterns with -ing / to-infinitive — want to do, enjoy doing', 'level' => 'B1'],
                    ['title' => 'Phrasal verbs — базові (get up, look for, turn on…)', 'level' => 'B1'],
                    ['title' => 'Verbs with two objects — give me it / give it to me', 'level' => 'B2'],
                    ['title' => 'Make / let / help — конструкції примусу й дозволу', 'level' => 'B2'],
                    ['title' => 'Used to / Be used to / Get used to — звички й адаптація', 'level' => 'B2'],
                    ['title' => 'Advanced phrasal verbs — складні та багатозначні', 'level' => 'C1'],
                    ['title' => 'Subjunctive mood — I suggest that he go, It is vital that…', 'level' => 'C2'],
                ],
            ],
            [
                'title' => '6. Часи (Tenses)',
                'level' => 'A1–C1',
                'children' => [
                    ['title' => 'Present Simple — Теперішній простий час', 'level' => 'A1'],
                    ['title' => 'Present Continuous — Теперішній тривалий час', 'level' => 'A1–A2'],
                    ['title' => 'Present Perfect — Теперішній доконаний час', 'level' => 'A2–B1'],
                    ['title' => 'Present Perfect Continuous — Теперішній доконано-тривалий час', 'level' => 'B2'],
                    ['title' => 'Past Simple — Минулий простий час', 'level' => 'A1–A2'],
                    ['title' => 'Past Continuous — Минулий тривалий час', 'level' => 'A2–B1'],
                    ['title' => 'Past Perfect — Минулий доконаний час', 'level' => 'B1–B2'],
                    ['title' => 'Past Perfect Continuous — Минулий доконано-тривалий час', 'level' => 'B2'],
                    ['title' => 'Future Simple — Майбутній простий час', 'level' => 'A1–A2'],
                    ['title' => 'Future Continuous — Майбутній тривалий час', 'level' => 'B1–B2'],
                    ['title' => 'Future Perfect — Майбутній доконаний час', 'level' => 'B2'],
                    ['title' => 'Future Perfect Continuous — Майбутній доконано-тривалий час', 'level' => 'B2–C1'],
                    ['title' => 'Future in the past — was going to, would do, was to do', 'level' => 'C1'],
                    [
                        'title' => 'Додаткові сторінки (контраст і огляд)',
                        'level' => null,
                        'children' => [
                            ['title' => 'Present Simple vs Present Continuous — порівняння', 'level' => 'A1–A2'],
                            ['title' => 'Past Simple vs Past Continuous — порівняння', 'level' => 'A2–B1'],
                            ['title' => 'Past Simple vs Present Perfect — порівняння', 'level' => 'B1'],
                            ['title' => 'Present tenses with future meaning — present simple / continuous для майбутнього', 'level' => 'B1'],
                            ['title' => 'Future forms — will / be going to / Present Continuous', 'level' => 'A2–B1'],
                            ['title' => 'Timeline of tenses — лінійка часів', 'level' => 'B1–B2'],
                        ],
                    ],
                ],
            ],
            [
                'title' => '7. Модальні дієслова',
                'level' => 'A1–C1',
                'children' => [
                    ['title' => 'Can / Could — модальні дієслова', 'level' => 'A1–A2'],
                    ['title' => 'May / Might — модальні дієслова', 'level' => 'B1'],
                    ['title' => 'Must / Have to — модальні дієслова', 'level' => 'A2–B1'],
                    ['title' => 'Semi-modals — be able to, be allowed to, have (got) to', 'level' => 'B1'],
                    ['title' => 'Need / Need to — модальні дієслова', 'level' => 'B1'],
                    ['title' => 'Should / Ought to — модальні дієслова', 'level' => 'B1'],
                    ['title' => 'Perfect Modals, Had Better, Be Supposed To', 'level' => 'B2'],
                    ['title' => 'Requests & offers with modals — ввічливі прохання/пропозиції', 'level' => 'A2–B1'],
                    ['title' => 'Degrees of certainty with modals — must, might, can\'t…', 'level' => 'B1–B2'],
                    ['title' => 'Other modals — dare, shall (formal/legal), be to', 'level' => 'C1'],
                ],
            ],
            [
                'title' => '8. Питальні речення та заперечення',
                'level' => 'A1–B2',
                'children' => [
                    ['title' => 'Question forms — як ставити запитання', 'level' => 'A1'],
                    ['title' => 'Wh-questions — who, what, where, when, why, how', 'level' => 'A1–A2'],
                    ['title' => 'Short answers — короткі відповіді', 'level' => 'A1'],
                    ['title' => 'Question word order — порядок слів у питаннях', 'level' => 'A1–A2'],
                    ['title' => 'Question tags — isn\'t it?, don\'t you?', 'level' => 'B1'],
                    ['title' => 'Subject vs object questions — who called you? vs who did you call?', 'level' => 'B1'],
                    ['title' => 'Indirect questions — Can you tell me…?', 'level' => 'B1–B2'],
                    ['title' => 'Negation in Simple — do/does/did + not', 'level' => 'A1–A2'],
                    ['title' => 'Negation with be, modals and have got', 'level' => 'A1–A2'],
                    ['title' => 'Negative pronouns and adverbs — nobody, nothing, nowhere', 'level' => 'A2–B1'],
                    ['title' => 'Double negatives — помилки типу I don\'t know nothing', 'level' => 'B1'],
                ],
            ],
            [
                'title' => '9. Прикметники та прислівники',
                'level' => 'A1–C1',
                'children' => [
                    ['title' => 'Adjectives — базові описові слова', 'level' => 'A1'],
                    ['title' => 'Adjectives vs adverbs — різниця', 'level' => 'A2'],
                    ['title' => 'Degrees of Comparison — ступені порівняння прикметників і прислівників', 'level' => 'A2'],
                    ['title' => 'Comparative vs Superlative — вживання', 'level' => 'A2'],
                    ['title' => 'Equality comparison — as…as, not as…as', 'level' => 'A2'],
                    ['title' => 'So / such — підсилення прикметників та іменників', 'level' => 'B1'],
                    ['title' => 'Too / enough — надмірність і достатність (too big, big enough)', 'level' => 'A2–B1'],
                    ['title' => '-ed vs -ing adjectives — bored vs boring', 'level' => 'A2–B1'],
                    ['title' => 'Order of adjectives — порядок (opinion, size, age…)', 'level' => 'B1'],
                    ['title' => 'Gradable vs non-gradable adjectives — very cold vs absolutely freezing', 'level' => 'B2'],
                    ['title' => 'Adverbs of frequency — usually, often, never', 'level' => 'A1–A2'],
                    ['title' => 'Adverbs of time and place — yesterday, here, there', 'level' => 'A1'],
                    ['title' => 'Adverbs formation — утворення (-ly) та винятки (fast, hard)', 'level' => 'B1'],
                    ['title' => 'Adverbial positions — зміна значення залежно від позиції', 'level' => 'C1'],
                    ['title' => 'Adjectives + prepositions — good at, afraid of…', 'level' => 'B1–B2'],
                ],
            ],
            [
                'title' => '10. Умовні речення',
                'level' => 'A2–C1',
                'children' => [
                    ['title' => 'Zero Conditional — загальні факти та рутини', 'level' => 'A2–B1'],
                    ['title' => 'First Conditional — реальні майбутні наслідки', 'level' => 'B1'],
                    ['title' => 'Second Conditional — уявні або малоймовірні ситуації', 'level' => 'B1–B2'],
                    ['title' => 'Third Conditional — нереальне минуле', 'level' => 'B2'],
                    ['title' => 'Mixed Conditionals — змішані часові комбінації', 'level' => 'B2–C1'],
                    ['title' => 'I wish / If only (present) — жаль про теперішнє', 'level' => 'B1'],
                    ['title' => 'I wish / If only (past) — жаль про минуле', 'level' => 'B2'],
                    ['title' => 'If, Unless, In case, As long as — сполучники в умовних', 'level' => 'B1–B2'],
                    ['title' => 'Alternatives to "if" — provided (that), should you…, on condition that…', 'level' => 'C1'],
                    ['title' => 'Conditionals with modals — can / could / might у if-структурах', 'level' => 'B2'],
                    ['title' => 'Real vs unreal conditionals — узагальнення', 'level' => 'B1–B2'],
                ],
            ],
            [
                'title' => '11. Переклад та типові помилки',
                'level' => 'A2–C1',
                'children' => [
                    ['title' => 'Translation techniques — як перекладати ефективно', 'level' => 'B2'],
                    ['title' => 'Word order: English vs Ukrainian — порядок слів', 'level' => 'A2–B1'],
                    ['title' => 'False friends — "фальшиві друзі перекладача"', 'level' => 'B1–B2'],
                    ['title' => 'When not to translate literally — де дослівний переклад шкодить', 'level' => 'B1–B2'],
                    ['title' => 'Articles in translation — вживання a / the там, де в укр. нічого', 'level' => 'A2–B1'],
                    ['title' => 'Typical mistakes for Ukrainian learners — типові помилки українців', 'level' => 'A2–B2'],
                ],
            ],
            [
                'title' => '12. Просунута граматика та стиль (Advanced grammar & style)',
                'level' => 'B1–C2',
                'children' => [
                    ['title' => 'Inversion for emphasis — Never have I seen…', 'level' => 'C1'],
                    ['title' => 'Inversion in conditionals — Had I known…, Were I you…', 'level' => 'C1'],
                    ['title' => 'Emphatic do — підсилювальне do (I do like it)', 'level' => 'B2'],
                    ['title' => 'Participle & reduced clauses — Having finished…, People living…', 'level' => 'C1'],
                    ['title' => 'Reduced relative clauses — the book (written by…)', 'level' => 'C1'],
                    ['title' => 'Cleft sentences — It was John who…, What I need is…', 'level' => 'C1'],
                    ['title' => 'Advanced verb patterns — reporting verbs, verbs of perception', 'level' => 'C1'],
                    ['title' => 'Advanced modality & hedging — It might appear that…, He would seem to…', 'level' => 'C1–C2'],
                    ['title' => 'Ellipsis and substitution — so do I, neither do I, if so / if not', 'level' => 'C1'],
                    ['title' => 'Discourse markers in formal writing — however, moreover, nevertheless…', 'level' => 'C1–C2'],
                    ['title' => 'Narrative tenses & aspect nuances — he\'d been working, he would often come…', 'level' => 'C1–C2'],
                    ['title' => 'Nominalisation in academic English — introduction of…, failure to…', 'level' => 'C2'],
                    ['title' => 'Register and style — formal / neutral / informal grammar choices', 'level' => 'C1–C2'],
                    [
                        'title' => 'Reported speech — непряма мова, узгодження часів',
                        'level' => 'B1–B2',
                        'children' => [
                            ['title' => 'Reported statements — He said (that) he was tired', 'level' => 'B1'],
                            ['title' => 'Reported questions — He asked where I lived', 'level' => 'B1–B2'],
                            ['title' => 'Reported requests and commands — He told me to sit down', 'level' => 'B1–B2'],
                            ['title' => 'Backshift of tenses — узгодження часів у непрямій мові', 'level' => 'B1–B2'],
                        ],
                    ],
                    ['title' => 'Linking words & connectors — although, however, therefore, in spite of…', 'level' => 'B2'],
                ],
            ],
            [
                'title' => '13. Пасивний стан (Passive Voice)',
                'level' => 'A2–C1',
                'children' => [
                    ['title' => 'Passive: Present Simple — It is made in China', 'level' => 'A2'],
                    ['title' => 'Passive: Past Simple — It was built in 1990', 'level' => 'A2'],
                    ['title' => 'Passive: all main tenses — огляд утворення', 'level' => 'B1–B2'],
                    ['title' => 'Get-passive — get married, get fired', 'level' => 'B2'],
                    ['title' => 'When to use passive — стилістика та типові помилки', 'level' => 'B1–B2'],
                    ['title' => 'Personal vs impersonal passive — It is said that…, He is said to…', 'level' => 'B2'],
                    ['title' => 'Causative form — have / get something done', 'level' => 'B2'],
                    ['title' => 'Passive with gerunds/infinitives — dislike being told what to do', 'level' => 'C1'],
                ],
            ],
            [
                'title' => '14. Прийменники (Prepositions)',
                'level' => 'A1–C1',
                'children' => [
                    ['title' => 'Prepositions of place — in, on, at', 'level' => 'A1'],
                    ['title' => 'Prepositions of time — in, on, at', 'level' => 'A1'],
                    ['title' => 'Prepositions of movement — into, out of, through, across…', 'level' => 'B1'],
                    ['title' => 'Verb + preposition — wait for, listen to, look after…', 'level' => 'B2'],
                    ['title' => 'Noun + preposition — a rise in, reason for, trouble with', 'level' => 'B2'],
                    ['title' => 'Complex prepositions — in spite of, due to, on behalf of…', 'level' => 'C1'],
                ],
            ],
            [
                'title' => '15. Дискурс та текст (Discourse & Text)',
                'level' => 'C1–C2',
                'children' => [
                    ['title' => "Cohesion & coherence — зв'язність тексту", 'level' => 'C1'],
                    ['title' => 'Paragraph structure — topic sentence, supporting details, conclusion', 'level' => 'C1'],
                    ['title' => 'Signposting in texts — firstly, on the other hand, in conclusion…', 'level' => 'C1'],
                    ['title' => 'Punctuation nuances — крапка з комою, тире, дужки, лапки', 'level' => 'C2'],
                ],
            ],
        ];
    }
}
