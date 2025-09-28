<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Database\Seeders\QuestionSeeder;

class DoDoesIsAreComprehensiveAiSeeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Do Does Am Is Are Comprehensive AI Test'])->id;

        $sectionSources = [
            'do_forms' => Source::firstOrCreate(['name' => 'Do/Does Auxiliary Practice'])->id,
            'be_forms' => Source::firstOrCreate(['name' => 'To Be Present Forms Practice'])->id,
        ];

        $themeTags = [
            'do_forms' => Tag::firstOrCreate(['name' => 'Do/Does Question Focus'], ['category' => 'English Grammar Theme'])->id,
            'be_forms' => Tag::firstOrCreate(['name' => 'Am/Is/Are Usage Focus'], ['category' => 'English Grammar Theme'])->id,
        ];

        $detailTags = [
            'do_aux_selection' => Tag::firstOrCreate(['name' => 'Auxiliary Do/Does Selection'], ['category' => 'English Grammar Detail'])->id,
            'be_linking_selection' => Tag::firstOrCreate(['name' => 'Verb To Be Present Selection'], ['category' => 'English Grammar Detail'])->id,
        ];

        $rawDataset = [
            [
                'level' => 'A1',
                'question' => '{a1} you like milk in your tea?',
                'answer' => 'do',
                'subject' => 'you',
                'subject_category' => 'you',
                'example' => 'Do you like hot tea?',
            ],
            [
                'level' => 'A1',
                'question' => '{a1} she from Kyiv?',
                'answer' => 'is',
                'subject' => 'she',
                'subject_category' => 'third_singular',
                'example' => 'Is she at home?',
            ],
            [
                'level' => 'A1',
                'question' => '{a1} they in the park right now?',
                'answer' => 'are',
                'subject' => 'they',
                'subject_category' => 'plural',
                'example' => 'Are they ready?',
            ],
            [
                'level' => 'A1',
                'question' => '{a1} I late for class?',
                'answer' => 'am',
                'subject' => 'I',
                'subject_category' => 'i',
                'example' => 'Am I on time?',
            ],
            [
                'level' => 'A1',
                'question' => '{a1} your brother like football?',
                'answer' => 'does',
                'subject' => 'your brother',
                'subject_category' => 'third_singular',
                'example' => 'Does your brother play tennis?',
            ],
            [
                'level' => 'A1',
                'question' => 'Where {a1} you live?',
                'answer' => 'do',
                'subject' => 'you',
                'subject_category' => 'you',
                'example' => 'Do you live nearby?',
            ],
            [
                'level' => 'A1',
                'question' => 'What {a1} your parents cook on Sundays?',
                'answer' => 'do',
                'subject' => 'your parents',
                'subject_category' => 'plural',
                'example' => 'Do your parents cook together?',
            ],
            [
                'level' => 'A1',
                'question' => '{a1} Anna at home today?',
                'answer' => 'is',
                'subject' => 'Anna',
                'subject_category' => 'third_singular',
                'example' => 'Is Anna at school?',
            ],
            [
                'level' => 'A1',
                'question' => 'When {a1} you usually get up?',
                'answer' => 'do',
                'subject' => 'you',
                'subject_category' => 'you',
                'example' => 'Do you get up early?',
            ],
            [
                'level' => 'A1',
                'question' => '{a1} your friends happy with the plan?',
                'answer' => 'are',
                'subject' => 'your friends',
                'subject_category' => 'plural',
                'example' => 'Are your friends excited?',
            ],
            [
                'level' => 'A2',
                'question' => 'Why {a1} he always forget his keys?',
                'answer' => 'does',
                'subject' => 'he',
                'subject_category' => 'third_singular',
                'example' => 'Does he forget things often?',
            ],
            [
                'level' => 'A2',
                'question' => 'How many books {a1} you read every month?',
                'answer' => 'do',
                'subject' => 'you',
                'subject_category' => 'you',
                'example' => 'Do you read many books?',
            ],
            [
                'level' => 'A2',
                'question' => '{a1} we early for the meeting?',
                'answer' => 'are',
                'subject' => 'we',
                'subject_category' => 'plural',
                'example' => 'Are we on time?',
            ],
            [
                'level' => 'A2',
                'question' => 'What time {a1} the museum open on Sundays?',
                'answer' => 'does',
                'subject' => 'the museum',
                'subject_category' => 'third_singular',
                'example' => 'Does the museum open early?',
            ],
            [
                'level' => 'A2',
                'question' => '{a1} the children ready for school yet?',
                'answer' => 'are',
                'subject' => 'the children',
                'subject_category' => 'plural',
                'example' => 'Are the children ready?',
            ],
            [
                'level' => 'A2',
                'question' => 'Which sport {a1} your sister prefer in winter?',
                'answer' => 'does',
                'subject' => 'your sister',
                'subject_category' => 'third_singular',
                'example' => 'Does your sister prefer skating?',
            ],
            [
                'level' => 'A2',
                'question' => '{a1} I supposed to bring my ID?',
                'answer' => 'am',
                'subject' => 'I',
                'subject_category' => 'i',
                'example' => 'Am I supposed to sign here?',
            ],
            [
                'level' => 'A2',
                'question' => 'Where {a1} your grandparents usually spend the summer?',
                'answer' => 'do',
                'subject' => 'your grandparents',
                'subject_category' => 'plural',
                'example' => 'Do your grandparents travel in summer?',
            ],
            [
                'level' => 'A2',
                'question' => '{a1} there a pharmacy near here?',
                'answer' => 'is',
                'subject' => 'there (a pharmacy)',
                'subject_category' => 'there_singular',
                'example' => 'Is there a bank nearby?',
            ],
            [
                'level' => 'A2',
                'question' => 'How often {a1} they visit their cousins?',
                'answer' => 'do',
                'subject' => 'they',
                'subject_category' => 'plural',
                'example' => 'Do they visit often?',
            ],
            [
                'level' => 'B1',
                'question' => '{a1} your manager approve flexible hours for the team?',
                'answer' => 'does',
                'subject' => 'your manager',
                'subject_category' => 'third_singular',
                'example' => 'Does your manager support flexible hours?',
            ],
            [
                'level' => 'B1',
                'question' => 'Why {a1} these buses arrive late in the evening?',
                'answer' => 'do',
                'subject' => 'these buses',
                'subject_category' => 'plural',
                'example' => 'Do these buses arrive on time?',
            ],
            [
                'level' => 'B1',
                'question' => '{a1} the documents ready for signature?',
                'answer' => 'are',
                'subject' => 'the documents',
                'subject_category' => 'plural',
                'example' => 'Are the documents complete?',
            ],
            [
                'level' => 'B1',
                'question' => 'At what stage {a1} the project now?',
                'answer' => 'is',
                'subject' => 'the project',
                'subject_category' => 'third_singular',
                'example' => 'Is the project on schedule?',
            ],
            [
                'level' => 'B1',
                'question' => 'How long {a1} you stay in the library each day?',
                'answer' => 'do',
                'subject' => 'you',
                'subject_category' => 'you',
                'example' => 'Do you stay long in the library?',
            ],
            [
                'level' => 'B1',
                'question' => '{a1} your teammates agree with this approach?',
                'answer' => 'do',
                'subject' => 'your teammates',
                'subject_category' => 'plural',
                'example' => 'Do your teammates agree?',
            ],
            [
                'level' => 'B1',
                'question' => 'Who {a1} responsible for updating the schedule?',
                'answer' => 'is',
                'subject' => 'who (one person)',
                'subject_category' => 'who_singular',
                'example' => 'Who is responsible for this file?',
            ],
            [
                'level' => 'B1',
                'question' => '{a1} we expected to submit the report today?',
                'answer' => 'are',
                'subject' => 'we',
                'subject_category' => 'plural',
                'example' => 'Are we expected to submit it today?',
            ],
            [
                'level' => 'B1',
                'question' => 'Why {a1} I the only person without access?',
                'answer' => 'am',
                'subject' => 'I',
                'subject_category' => 'i',
                'example' => 'Am I the only one without access?',
            ],
            [
                'level' => 'B1',
                'question' => 'Which department {a1} handle customer refunds?',
                'answer' => 'does',
                'subject' => 'which department',
                'subject_category' => 'third_singular',
                'example' => 'Does the finance department handle refunds?',
            ],
            [
                'level' => 'B2',
                'question' => 'To what extent {a1} employees rely on remote tools during winter?',
                'answer' => 'do',
                'subject' => 'employees',
                'subject_category' => 'plural',
                'example' => 'Do employees rely on remote tools in winter?',
            ],
            [
                'level' => 'B2',
                'question' => '{a1} the committee satisfied with the proposed timeline?',
                'answer' => 'is',
                'subject' => 'the committee',
                'subject_category' => 'collective',
                'example' => 'Is the committee satisfied with the plan?',
            ],
            [
                'level' => 'B2',
                'question' => 'How frequently {a1} the analysts present interim findings to the board?',
                'answer' => 'do',
                'subject' => 'the analysts',
                'subject_category' => 'plural',
                'example' => 'Do the analysts present interim findings often?',
            ],
            [
                'level' => 'B2',
                'question' => 'Why {a1} the senior partners insist on revisiting the contract?',
                'answer' => 'do',
                'subject' => 'the senior partners',
                'subject_category' => 'plural',
                'example' => 'Do the senior partners insist on changes?',
            ],
            [
                'level' => 'B2',
                'question' => 'Under what conditions {a1} the software updates fail to install?',
                'answer' => 'do',
                'subject' => 'the software updates',
                'subject_category' => 'plural',
                'example' => 'Do the updates fail to install often?',
            ],
            [
                'level' => 'B2',
                'question' => '{a1} the figures in this table accurate according to the latest audit?',
                'answer' => 'are',
                'subject' => 'the figures',
                'subject_category' => 'plural',
                'example' => 'Are the figures accurate?',
            ],
            [
                'level' => 'B2',
                'question' => 'How soon {a1} the research unit release the survey results?',
                'answer' => 'does',
                'subject' => 'the research unit',
                'subject_category' => 'collective',
                'example' => 'Does the research unit release results quickly?',
            ],
            [
                'level' => 'B2',
                'question' => 'Why {a1} the interns encouraged to question existing routines?',
                'answer' => 'are',
                'subject' => 'the interns',
                'subject_category' => 'plural',
                'example' => 'Are the interns encouraged to ask questions?',
            ],
            [
                'level' => 'B2',
                'question' => '{a1} your proposal address the regulatory changes we discussed?',
                'answer' => 'does',
                'subject' => 'your proposal',
                'subject_category' => 'third_singular',
                'example' => 'Does your proposal address the changes?',
            ],
            [
                'level' => 'B2',
                'question' => 'In which scenarios {a1} stakeholders request additional guarantees?',
                'answer' => 'do',
                'subject' => 'stakeholders',
                'subject_category' => 'plural',
                'example' => 'Do stakeholders request additional guarantees often?',
            ],
            [
                'level' => 'C1',
                'question' => 'To what extent {a1} our clients perceive the rebranding as authentic?',
                'answer' => 'do',
                'subject' => 'our clients',
                'subject_category' => 'plural',
                'example' => 'Do our clients perceive the rebranding as authentic?',
            ],
            [
                'level' => 'C1',
                'question' => 'Why {a1} the advisory board consider sustainability metrics non-negotiable?',
                'answer' => 'does',
                'subject' => 'the advisory board',
                'subject_category' => 'collective',
                'example' => 'Does the advisory board consider sustainability vital?',
            ],
            [
                'level' => 'C1',
                'question' => '{a1} the preliminary findings consistent with last year\'s outcomes?',
                'answer' => 'are',
                'subject' => 'the preliminary findings',
                'subject_category' => 'plural',
                'example' => 'Are the preliminary findings consistent with last year?',
            ],
            [
                'level' => 'C1',
                'question' => 'How effectively {a1} the crisis response protocols function under pressure?',
                'answer' => 'do',
                'subject' => 'the crisis response protocols',
                'subject_category' => 'plural',
                'example' => 'Do the crisis response protocols function under pressure?',
            ],
            [
                'level' => 'C1',
                'question' => 'In whose opinion {a1} the legal guidelines insufficiently precise?',
                'answer' => 'are',
                'subject' => 'the legal guidelines',
                'subject_category' => 'plural',
                'example' => 'Are the legal guidelines precise enough?',
            ],
            [
                'level' => 'C1',
                'question' => 'At which point {a1} the negotiation tactics become counterproductive?',
                'answer' => 'do',
                'subject' => 'the negotiation tactics',
                'subject_category' => 'plural',
                'example' => 'Do the negotiation tactics become counterproductive?',
            ],
            [
                'level' => 'C1',
                'question' => 'To whom {a1} the director report potential conflicts of interest?',
                'answer' => 'does',
                'subject' => 'the director',
                'subject_category' => 'third_singular',
                'example' => 'Does the director report conflicts to the board?',
            ],
            [
                'level' => 'C1',
                'question' => '{a1} the majority of stakeholders aligned with the new governance model?',
                'answer' => 'are',
                'subject' => 'the majority of stakeholders',
                'subject_category' => 'plural',
                'example' => 'Are the majority of stakeholders aligned?',
            ],
            [
                'level' => 'C1',
                'question' => 'Under what circumstances {a1} employees obligated to disclose secondary employment?',
                'answer' => 'are',
                'subject' => 'employees',
                'subject_category' => 'plural',
                'example' => 'Are employees obligated to disclose secondary work?',
            ],
            [
                'level' => 'C1',
                'question' => 'Why {a1} this dataset require extensive normalization before analysis?',
                'answer' => 'does',
                'subject' => 'this dataset',
                'subject_category' => 'third_singular',
                'example' => 'Does this dataset require normalization?',
            ],
            [
                'level' => 'C2',
                'question' => 'To what degree {a1} the projected synergies hinge on cross-departmental cooperation?',
                'answer' => 'do',
                'subject' => 'the projected synergies',
                'subject_category' => 'plural',
                'example' => 'Do the projected synergies hinge on cooperation?',
            ],
            [
                'level' => 'C2',
                'question' => 'In what ways {a1} the audit recommendations intersect with compliance obligations?',
                'answer' => 'do',
                'subject' => 'the audit recommendations',
                'subject_category' => 'plural',
                'example' => 'Do the audit recommendations intersect with compliance obligations?',
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the underlying assumptions about consumer behavior still defensible?',
                'answer' => 'are',
                'subject' => 'the underlying assumptions',
                'subject_category' => 'plural',
                'example' => 'Are the assumptions still defensible?',
            ],
            [
                'level' => 'C2',
                'question' => 'By whose authority {a1} the ethics panel mandate quarterly disclosures?',
                'answer' => 'does',
                'subject' => 'the ethics panel',
                'subject_category' => 'collective',
                'example' => 'Does the ethics panel mandate quarterly disclosures?',
            ],
            [
                'level' => 'C2',
                'question' => 'At what juncture {a1} our contingency plans become fiscally untenable?',
                'answer' => 'do',
                'subject' => 'our contingency plans',
                'subject_category' => 'plural',
                'example' => 'Do our contingency plans become fiscally untenable?',
            ],
            [
                'level' => 'C2',
                'question' => 'To whom {a1} the chief negotiator present preliminary concessions?',
                'answer' => 'does',
                'subject' => 'the chief negotiator',
                'subject_category' => 'third_singular',
                'example' => 'Does the chief negotiator present concessions to the board?',
            ],
            [
                'level' => 'C2',
                'question' => 'In light of recent jurisprudence, {a1} the policy revisions appear sufficiently robust?',
                'answer' => 'do',
                'subject' => 'the policy revisions',
                'subject_category' => 'plural',
                'example' => 'Do the policy revisions appear robust enough?',
            ],
            [
                'level' => 'C2',
                'question' => 'For which stakeholders {a1} the new oversight measures pose significant burdens?',
                'answer' => 'do',
                'subject' => 'the new oversight measures',
                'subject_category' => 'plural',
                'example' => 'Do the new oversight measures pose burdens for some stakeholders?',
            ],
            [
                'level' => 'C2',
                'question' => 'Why {a1} the external reviewers deem the methodology inconclusive?',
                'answer' => 'do',
                'subject' => 'the external reviewers',
                'subject_category' => 'plural',
                'example' => 'Do the external reviewers deem the methodology inconclusive?',
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the executive summaries distributed ahead of the plenary session?',
                'answer' => 'are',
                'subject' => 'the executive summaries',
                'subject_category' => 'plural',
                'example' => 'Are the executive summaries distributed early?',
            ],
        ];


        $options = ['do', 'does', 'am', 'is', 'are'];
        $sections = [
            'do_forms' => [],
            'be_forms' => [],
        ];

        foreach ($rawDataset as $entry) {
            $sectionKey = in_array($entry['answer'], ['do', 'does'], true) ? 'do_forms' : 'be_forms';
            $sections[$sectionKey][] = $this->buildQuestionPayload($entry, $options);
        }

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $detailByTense = [
            'Present Simple Question (Do/Does)' => 'do_aux_selection',
            'Present Simple of To Be' => 'be_linking_selection',
        ];

        $tenseTags = [];
        foreach ($sections as $sectionQuestions) {
            foreach ($sectionQuestions as $question) {
                foreach ($question['tense'] as $tenseName) {
                    if (! isset($tenseTags[$tenseName])) {
                        $tenseTags[$tenseName] = Tag::firstOrCreate(['name' => $tenseName], ['category' => 'English Grammar Tense'])->id;
                    }
                }
            }
        }

        $service = new QuestionSeedingService();
        $items = [];
        $meta = [];

        foreach ($sections as $sectionKey => $sectionQuestions) {
            foreach ($sectionQuestions as $index => $question) {
                $uuid = $this->generateQuestionUuid($sectionKey, $index, $question['question']);

                $answers = [];
                $optionMarkerMap = [];
                $firstMarker = array_key_first($question['answers']);

                if ($firstMarker !== null) {
                    foreach ($question['options'] as $option) {
                        $optionMarkerMap[$option] = $firstMarker;
                    }
                }

                foreach ($question['answers'] as $marker => $answer) {
                    $answers[] = [
                        'marker' => $marker,
                        'answer' => $answer,
                        'verb_hint' => $this->normalizeHint($question['verb_hint'][$marker] ?? null),
                    ];
                    $optionMarkerMap[$answer] = $marker;
                }

                $tagIds = [$themeTags[$sectionKey]];
                $detailKey = $detailByTense[$question['tense'][0] ?? ''] ?? null;
                if ($detailKey !== null && isset($detailTags[$detailKey])) {
                    $tagIds[] = $detailTags[$detailKey];
                }

                foreach ($question['tense'] as $tenseName) {
                    $tagIds[] = $tenseTags[$tenseName];
                }

                $items[] = [
                    'uuid' => $uuid,
                    'question' => $question['question'],
                    'category_id' => $categoryId,
                    'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                    'source_id' => $sectionSources[$sectionKey],
                    'flag' => 2,
                    'level' => $question['level'],
                    'tag_ids' => array_values(array_unique($tagIds)),
                    'answers' => $answers,
                    'options' => $question['options'],
                    'variants' => [],
                ];

                $meta[] = [
                    'uuid' => $uuid,
                    'answers' => $question['answers'],
                    'option_markers' => $optionMarkerMap,
                    'hints' => $question['hints'],
                    'explanations' => $question['explanations'],
                ];
            }
        }

        $service->seed($items);

        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();
            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($data['hints']);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            foreach ($data['explanations'] as $option => $text) {
                $marker = $data['option_markers'][$option] ?? array_key_first($data['answers']);
                $correct = $data['answers'][$marker] ?? reset($data['answers']);

                ChatGPTExplanation::updateOrCreate(
                    [
                        'question' => $question->question,
                        'wrong_answer' => $option,
                        'correct_answer' => $correct,
                        'language' => 'ua',
                    ],
                    ['explanation' => $text]
                );
            }
        }
    }

    private function buildQuestionPayload(array $entry, array $options): array
    {
        $correct = $entry['answer'];
        $usage = in_array($correct, ['do', 'does'], true) ? 'do' : 'be';

        return [
            'question' => $entry['question'],
            'verb_hint' => ['a1' => '(choose do/does/am/is/are)'],
            'options' => $options,
            'answers' => ['a1' => $correct],
            'explanations' => $this->buildExplanations($entry, $options, $usage),
            'hints' => ['a1' => $this->buildHint($entry, $usage)],
            'tense' => [$usage === 'do' ? 'Present Simple Question (Do/Does)' : 'Present Simple of To Be'],
            'level' => $entry['level'],
        ];
    }

    private function buildExplanations(array $entry, array $options, string $usage): array
    {
        $explanations = [];
        $correct = $entry['answer'];

        foreach ($options as $option) {
            if ($option === $correct) {
                $explanations[$option] = $this->buildCorrectExplanation($option, $entry, $usage);
            } else {
                $explanations[$option] = $this->buildWrongExplanation($option, $entry, $usage);
            }
        }

        return $explanations;
    }

    private function buildCorrectExplanation(string $option, array $entry, string $usage): string
    {
        $subjectNote = $this->subjectDescriptor($entry['subject_category'], $entry['subject']);

        if ($usage === 'do') {
            if ($option === 'do') {
                return "✅ **Do** використовується з I/you/we/they у питаннях Present Simple. $subjectNote — тому потрібне **do**.  
Приклад: *{$entry['example']}*.";
            }

            return "✅ **Does** ставимо перед he/she/it або одниною у Present Simple. $subjectNote — тож правильно обрати **does**.  
Приклад: *{$entry['example']}*.";
        }

        return match ($option) {
            'am' => "✅ **Am** — форма дієслова to be для I. $subjectNote — отже правильна форма **am**.  
Приклад: *{$entry['example']}*.",
            'is' => "✅ **Is** — форма to be для однини або he/she/it. $subjectNote — тому потрібне **is**.  
Приклад: *{$entry['example']}*.",
            'are' => "✅ **Are** — форма to be для множини та you. $subjectNote — отже обираємо **are**.  
Приклад: *{$entry['example']}*.",
            default => '',
        };
    }

    private function buildWrongExplanation(string $option, array $entry, string $usage): string
    {
        $correct = $entry['answer'];
        $subjectNote = $this->subjectDescriptor($entry['subject_category'], $entry['subject']);

        if ($usage === 'do') {
            return match ($option) {
                'do' => "❌ **Do** ставимо з I/you/we/they, але тут $subjectNote, тому потрібне **does**.",
                'does' => "❌ **Does** використовується з третьою особою однини, але тут $subjectNote, тому потрібне **do**.",
                'am' => "❌ **Am** — форма to be для I, а в запитанні з дієсловом потрібно допоміжне **$correct**.",
                'is' => "❌ **Is** — форма to be для однини, але нам потрібно допоміжне **$correct** для дієслова.",
                'are' => "❌ **Are** — форма to be для множини/you, тоді як у Present Simple питанні потрібне **$correct**.",
                default => '',
            };
        }

        return match ($option) {
            'do' => "❌ **Do** ставиться перед дієсловами дії, але тут потрібне дієслово to be, тобто **$correct**.",
            'does' => "❌ **Does** використовується для питань з дієсловами дії, а не з to be. Потрібно **$correct**.",
            'am' => $correct === 'is'
                ? "❌ **Am** вживається лише з I, а $subjectNote, тому потрібне **is**."
                : "❌ **Am** використовується тільки з I, а тут $subjectNote, тому підходить **are**.",
            'is' => $correct === 'are'
                ? "❌ **Is** підходить для однини, а $subjectNote, тож потрібно **are**."
                : "❌ **Is** — форма для однини, але $subjectNote потребує **am**.",
            'are' => $correct === 'is'
                ? "❌ **Are** вживається з множиною або you, а $subjectNote, тому потрібно **is**."
                : "❌ **Are** не використовується з I; $subjectNote вимагає **am**.",
            default => '',
        };
    }

    private function buildHint(array $entry, string $usage): string
    {
        $subject = $entry['subject'];
        $subjectNote = $this->subjectHintDescriptor($entry['subject_category'], $subject);
        $example = $entry['example'];

        if ($usage === 'do') {
            $structure = $entry['answer'] === 'does'
                ? '**Does + підмет (he/she/it або однина) + V1?**'
                : '**Do + підмет (I/you/we/they) + V1?**';

            return "Структура: {$structure}  
Підмет: {$subjectNote}.  
Приклад: *{$example}*.";
        }

        $structure = match ($entry['answer']) {
            'am' => '**Am + I + прикметник/іменник?**',
            'is' => $entry['subject_category'] === 'there_singular'
                ? '**Is there + однина + ...?**'
                : '**Is + підмет (однина) + прикметник/іменник/місце?**',
            'are' => '**Are + підмет (множина або you) + прикметник/іменник?**',
            default => '**Is/Are + підмет + ...?**',
        };

        if ($entry['subject_category'] === 'there_singular') {
            $subjectNote = 'there + однина';
        }

        return "Структура: {$structure}  
Підмет: {$subjectNote}.  
Приклад: *{$example}*.";
    }

    private function subjectDescriptor(string $category, string $subject): string
    {
        return match ($category) {
            'i' => 'підмет — I',
            'you' => 'підмет — you',
            'plural' => 'підмет — ' . $subject . ' (множина)',
            'third_singular' => 'підмет — ' . $subject . ' (третя особа однини)',
            'collective' => 'підмет — ' . $subject . ' (колективний іменник в однині)',
            'who_singular' => 'питальне слово who позначає одну особу',
            'there_singular' => 'структура there + однина (наприклад, a pharmacy)',
            default => 'підмет — ' . $subject,
        };
    }

    private function subjectHintDescriptor(string $category, string $subject): string
    {
        return match ($category) {
            'i' => 'I (перша особа однини)',
            'you' => 'you (друга особа)',
            'plural' => $subject . ' — множина',
            'third_singular' => $subject . ' — третя особа однини',
            'collective' => $subject . ' — колективний іменник, який трактуємо як однину',
            'who_singular' => 'who — очікуємо відповідь про одну особу',
            'there_singular' => 'there + однина',
            default => $subject,
        };
    }

    private function normalizeHint(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim($value, "() 	
");
    }

    private function formatHints(array $hints): ?string
    {
        if (empty($hints)) {
            return null;
        }

        $parts = [];
        foreach ($hints as $marker => $text) {
            $parts[] = '{' . $marker . '} ' . trim($text);
        }

        return implode("
", $parts);
    }
}
