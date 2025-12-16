<?php

namespace Database\Seeders\Ai\Claude;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\EnglishTagging\GapTagInferer;
use App\Support\TextBlock\TextBlockUuidGenerator;
use Database\Seeders\QuestionSeeder;

class QuestionsDifferentTypesClaudeSeeder extends QuestionSeeder
{
    private array $tagNameCache = [];

    public function run(): void
    {
        // Initialize the GapTagInferer service for gap-focused tagging
        $gapTagInferer = new GapTagInferer();
        $categoryId = Category::firstOrCreate(['name' => 'Questions - Different Types'])->id;

        // Create separate sources for each question topic
        $sources = [
            'yes_no' => Source::firstOrCreate(['name' => 'AI generated: Yes/No Questions (General Questions)'])->id,
            'wh_questions' => Source::firstOrCreate(['name' => 'AI generated: Wh-Questions (Special Questions)'])->id,
            'subject_questions' => Source::firstOrCreate(['name' => 'AI generated: Subject Questions'])->id,
            'indirect_questions' => Source::firstOrCreate(['name' => 'AI generated: Indirect Questions'])->id,
            'tag_questions' => Source::firstOrCreate(['name' => 'AI generated: Tag Questions (Disjunctive Questions)'])->id,
            'alternative_questions' => Source::firstOrCreate(['name' => 'AI generated: Alternative Questions'])->id,
            'negative_questions' => Source::firstOrCreate(['name' => 'AI generated: Negative Questions'])->id,
        ];

        $themeTag = Tag::firstOrCreate(
            ['name' => 'Question Formation Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $sharedTheoryTags = [
            Tag::firstOrCreate(['name' => 'Types of Questions'], ['category' => 'English Grammar Theme'])->id,
            Tag::firstOrCreate(['name' => 'Question Forms'], ['category' => 'English Grammar Theme'])->id,
            Tag::firstOrCreate(['name' => 'Grammar'], ['category' => 'English Grammar Theme'])->id,
            Tag::firstOrCreate(['name' => 'Theory'], ['category' => 'English Grammar Theme'])->id,
        ];

        // General tags for all questions
        $questionSentencesTag = Tag::firstOrCreate(
            ['name' => 'Question Sentences'],
            ['category' => 'English Grammar Theme']
        )->id;

        $typesOfQuestionSentencesTag = Tag::firstOrCreate(
            ['name' => 'Types of Question Sentences'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTags = [
            'yes_no' => Tag::firstOrCreate(['name' => 'Yes/No Questions'], ['category' => 'English Grammar Detail'])->id,
            'wh_questions' => Tag::firstOrCreate(['name' => 'Wh-Questions'], ['category' => 'English Grammar Detail'])->id,
            'subject_questions' => Tag::firstOrCreate(['name' => 'Subject Questions'], ['category' => 'English Grammar Detail'])->id,
            'indirect_questions' => Tag::firstOrCreate(['name' => 'Indirect Questions'], ['category' => 'English Grammar Detail'])->id,
            'tag_questions' => Tag::firstOrCreate(['name' => 'Tag Questions'], ['category' => 'English Grammar Detail'])->id,
            'alternative_questions' => Tag::firstOrCreate(['name' => 'Alternative Questions'], ['category' => 'English Grammar Detail'])->id,
            'negative_questions' => Tag::firstOrCreate(['name' => 'Negative Questions'], ['category' => 'English Grammar Detail'])->id,
        ];

        $detailContextTags = [
            'yes_no' => [
                Tag::firstOrCreate(['name' => 'General Questions'], ['category' => 'English Grammar Detail'])->id,
            ],
            'wh_questions' => [
                Tag::firstOrCreate(['name' => 'Special Questions'], ['category' => 'English Grammar Detail'])->id,
                Tag::firstOrCreate(['name' => 'Question Words'], ['category' => 'English Grammar Detail'])->id,
            ],
            'subject_questions' => [
                Tag::firstOrCreate(['name' => 'Subject Questions'], ['category' => 'English Grammar Detail'])->id,
            ],
            'indirect_questions' => [
                Tag::firstOrCreate(['name' => 'Indirect Questions'], ['category' => 'English Grammar Detail'])->id,
            ],
            'tag_questions' => [
                Tag::firstOrCreate(['name' => 'Question Tags'], ['category' => 'English Grammar Detail'])->id,
                Tag::firstOrCreate(['name' => 'Disjunctive Questions'], ['category' => 'English Grammar Detail'])->id,
            ],
            'alternative_questions' => [
                Tag::firstOrCreate(['name' => 'Choice Questions'], ['category' => 'English Grammar Detail'])->id,
            ],
            'negative_questions' => [
                Tag::firstOrCreate(['name' => 'Negative Question Forms'], ['category' => 'English Grammar Detail'])->id,
            ],
        ];

        // CEFR Level tags for filtering by proficiency
        $levelTags = [
            'A1' => Tag::firstOrCreate(['name' => 'CEFR A1'], ['category' => 'English Grammar Level'])->id,
            'A2' => Tag::firstOrCreate(['name' => 'CEFR A2'], ['category' => 'English Grammar Level'])->id,
            'B1' => Tag::firstOrCreate(['name' => 'CEFR B1'], ['category' => 'English Grammar Level'])->id,
            'B2' => Tag::firstOrCreate(['name' => 'CEFR B2'], ['category' => 'English Grammar Level'])->id,
            'C1' => Tag::firstOrCreate(['name' => 'CEFR C1'], ['category' => 'English Grammar Level'])->id,
            'C2' => Tag::firstOrCreate(['name' => 'CEFR C2'], ['category' => 'English Grammar Level'])->id,
        ];

        // Grammar tense/structure tags
        $tenseTags = [
            'present_simple' => Tag::firstOrCreate(['name' => 'Present Simple'], ['category' => 'English Grammar Tense'])->id,
            'past_simple' => Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'English Grammar Tense'])->id,
            'future_simple' => Tag::firstOrCreate(['name' => 'Future Simple'], ['category' => 'English Grammar Tense'])->id,
            'present_continuous' => Tag::firstOrCreate(['name' => 'Present Continuous'], ['category' => 'English Grammar Tense'])->id,
            'past_continuous' => Tag::firstOrCreate(['name' => 'Past Continuous'], ['category' => 'English Grammar Tense'])->id,
            'present_perfect' => Tag::firstOrCreate(['name' => 'Present Perfect'], ['category' => 'English Grammar Tense'])->id,
            'past_perfect' => Tag::firstOrCreate(['name' => 'Past Perfect'], ['category' => 'English Grammar Tense'])->id,
            'present_perfect_continuous' => Tag::firstOrCreate(['name' => 'Present Perfect Continuous'], ['category' => 'English Grammar Tense'])->id,
            'past_perfect_continuous' => Tag::firstOrCreate(['name' => 'Past Perfect Continuous'], ['category' => 'English Grammar Tense'])->id,
            'modal_verbs' => Tag::firstOrCreate(['name' => 'Modal Verbs'], ['category' => 'English Grammar Tense'])->id,
            'to_be' => Tag::firstOrCreate(['name' => 'To Be'], ['category' => 'English Grammar Tense'])->id,
        ];

        // Auxiliary verb type tags
        $auxiliaryTags = [
            'do_does_did' => Tag::firstOrCreate(['name' => 'Do/Does/Did'], ['category' => 'English Grammar Auxiliary'])->id,
            'be_auxiliary' => Tag::firstOrCreate(['name' => 'Be (am/is/are/was/were)'], ['category' => 'English Grammar Auxiliary'])->id,
            'have_has_had' => Tag::firstOrCreate(['name' => 'Have/Has/Had'], ['category' => 'English Grammar Auxiliary'])->id,
            'will_would' => Tag::firstOrCreate(['name' => 'Will/Would'], ['category' => 'English Grammar Auxiliary'])->id,
            'can_could' => Tag::firstOrCreate(['name' => 'Can/Could'], ['category' => 'English Grammar Auxiliary'])->id,
            'should' => Tag::firstOrCreate(['name' => 'Should'], ['category' => 'English Grammar Auxiliary'])->id,
            'must' => Tag::firstOrCreate(['name' => 'Must'], ['category' => 'English Grammar Auxiliary'])->id,
            'may_might' => Tag::firstOrCreate(['name' => 'May/Might'], ['category' => 'English Grammar Auxiliary'])->id,
        ];

        // Map gap tag names (returned by GapTagInferer) to tag IDs
        // This allows converting gap-focused inference results to actual tag IDs
        $gapTagNameToId = [
            // Structural patterns
            'Indirect Questions' => $detailTags['indirect_questions'],
            'Question Word Order' => Tag::firstOrCreate(['name' => 'Question Word Order'], ['category' => 'English Grammar Detail'])->id,
            'Embedded Questions' => Tag::firstOrCreate(['name' => 'Embedded Questions'], ['category' => 'English Grammar Detail'])->id,
            'Tag Questions' => $detailTags['tag_questions'],
            'Question Tags' => Tag::firstOrCreate(['name' => 'Question Tags'], ['category' => 'English Grammar Detail'])->id,
            'Subject Questions' => $detailTags['subject_questions'],
            'Question Formation' => Tag::firstOrCreate(['name' => 'Question Formation'], ['category' => 'English Grammar Detail'])->id,
            // Auxiliary/Tense patterns
            'Do/Does/Did' => $auxiliaryTags['do_does_did'],
            'Present Simple' => $tenseTags['present_simple'],
            'Past Simple' => $tenseTags['past_simple'],
            'Be (am/is/are/was/were)' => $auxiliaryTags['be_auxiliary'],
            'To Be' => $tenseTags['to_be'],
            'Present Continuous' => $tenseTags['present_continuous'],
            'Past Continuous' => $tenseTags['past_continuous'],
            'Have/Has/Had' => $auxiliaryTags['have_has_had'],
            'Present Perfect' => $tenseTags['present_perfect'],
            'Past Perfect' => $tenseTags['past_perfect'],
            'Modal Verbs' => $tenseTags['modal_verbs'],
            'Will/Would' => $auxiliaryTags['will_would'],
            'Future Simple' => $tenseTags['future_simple'],
            'Can/Could' => $auxiliaryTags['can_could'],
            'Should' => $auxiliaryTags['should'],
            'Must' => $auxiliaryTags['must'],
            'May/Might' => $auxiliaryTags['may_might'],
        ];

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $questions = $this->buildQuestions();

        $items = [];
        $meta = [];

        $anchorTagNames = $this->resolveTagNames([
            $sharedTheoryTags[0],
            $sharedTheoryTags[1],
        ]);

        foreach ($questions as $index => $question) {
            $uuid = $this->generateQuestionUuid($question['level'], $index, $question['question']);

            $answers = [];
            $optionMarkers = [];

            // Check if this is a multi-marker question (options are nested arrays with markers)
            // Multi-marker format: ['a1' => [...], 'a2' => [...]]
            // Single-marker format: ['option1', 'option2', ...]
            $isMultiMarker = false;
            if (isset($question['options']) && ! empty($question['options'])) {
                $firstKey = array_key_first($question['options']);
                $firstValue = $question['options'][$firstKey] ?? null;
                $isMultiMarker = is_string($firstKey)
                    && preg_match('/^a\d+$/', $firstKey)
                    && is_array($firstValue);
            }

            if ($isMultiMarker) {
                // Multi-marker question format (like MixedConditionalsAIGeneratedSeeder)
                foreach ($question['options'] as $marker => $markerOptions) {
                    foreach ($markerOptions as $option) {
                        $optionMarkers[$option] = $marker;
                    }
                }

                foreach ($question['answers'] as $marker => $answer) {
                    $verbHint = $question['verb_hints'][$marker] ?? null;
                    $answers[] = [
                        'marker' => $marker,
                        'answer' => $answer,
                        'verb_hint' => $this->normalizeHint($verbHint),
                    ];
                }

                // Flatten options for storage
                $flatOptions = $this->flattenOptions($question['options']);
            } else {
                // Single-marker question format (original format)
                foreach ($question['options'] as $option) {
                    $optionMarkers[$option] = 'a1';
                }

                $answers[] = [
                    'marker' => 'a1',
                    'answer' => $question['answers']['a1'],
                    'verb_hint' => $this->normalizeHint($question['verb_hint'] ?? null),
                ];

                $flatOptions = $question['options'];
            }

            $tagIds = array_merge([
                $themeTag,
                $questionSentencesTag,
                $typesOfQuestionSentencesTag,
            ], $sharedTheoryTags);

            // Add detail tag (question type)
            if (isset($question['detail']) && isset($detailTags[$question['detail']])) {
                $tagIds[] = $detailTags[$question['detail']];
            }

            if (isset($question['detail']) && isset($detailContextTags[$question['detail']])) {
                $tagIds = array_merge($tagIds, $detailContextTags[$question['detail']]);
            }

            // Add CEFR level tag
            if (isset($question['level']) && isset($levelTags[$question['level']])) {
                $tagIds[] = $levelTags[$question['level']];
            }

            // ============================================================
            // Gap-focused tagging: Use GapTagInferer for each marker
            // ============================================================
            $gapTagsPerMarker = [];
            $allGapTagIds = [];

            // Process each marker for gap-focused tagging
            $markersToProcess = $isMultiMarker
                ? array_keys($question['answers'])
                : ['a1'];

            $detailTagNames = [];
            if (isset($question['detail'])) {
                $detailTagIds = [];

                if (isset($detailTags[$question['detail']])) {
                    $detailTagIds[] = $detailTags[$question['detail']];
                }

                if (isset($detailContextTags[$question['detail']])) {
                    $detailTagIds = array_merge($detailTagIds, $detailContextTags[$question['detail']]);
                }

                if (! empty($detailTagIds)) {
                    $detailTagNames = $this->resolveTagNames($detailTagIds);
                }
            }

            foreach ($markersToProcess as $marker) {
                $correctAnswer = $question['answers'][$marker] ?? '';

                // Get options for this specific marker
                $markerOptions = $isMultiMarker
                    ? ($question['options'][$marker] ?? [])
                    : $question['options'];

                // Get verb hint for this marker
                $verbHint = $isMultiMarker
                    ? ($question['verb_hints'][$marker] ?? null)
                    : ($question['verb_hint'] ?? null);

                // Call GapTagInferer to get gap-focused tags (returns tag names)
                $gapTagNames = $gapTagInferer->infer(
                    $question['question'],
                    $marker,
                    $correctAnswer,
                    $markerOptions,
                    $verbHint
                );

                // Convert tag names to tag IDs (limit 1-3 per marker)
                $gapTagIdsForMarker = [];
                foreach (array_slice($gapTagNames, 0, 3) as $tagName) {
                    if (isset($gapTagNameToId[$tagName])) {
                        $gapTagIdsForMarker[] = $gapTagNameToId[$tagName];
                    }
                }

                $fallbackVerbHintTags = [];
                if (empty($gapTagIdsForMarker)) {
                    $fallbackVerbHintTags = $this->getTagsFromVerbHint(
                        $verbHint ?? '',
                        $tenseTags,
                        $auxiliaryTags
                    );
                }

                // Store per-marker tags (names for meta, IDs for union)
                $gapTagsPerMarker[$marker] = $this->buildMarkerTagChain(
                    $gapTagNames,
                    $gapTagIdsForMarker,
                    $fallbackVerbHintTags,
                    $anchorTagNames,
                    $detailTagNames
                );

                // Collect gap tag IDs (using array_push with spread for efficiency)
                array_push($allGapTagIds, ...$gapTagIdsForMarker);
                array_push($allGapTagIds, ...$fallbackVerbHintTags);
            }

            // Deduplicate gap tag IDs before merging
            $allGapTagIds = array_unique($allGapTagIds);

            // If gap inference returned tags, use them as primary
            if (! empty($allGapTagIds)) {
                $tagIds = array_merge($tagIds, $allGapTagIds);
            } else {
                // Fallback: Add tense/structure and auxiliary tags based on verb_hints
                // For multi-marker, try each marker's verb_hint; for single-marker, use the main verb_hint
                $verbHintsToTry = $isMultiMarker
                    ? array_values(array_filter($question['verb_hints'] ?? []))
                    : [($question['verb_hint'] ?? null)];

                foreach ($verbHintsToTry as $hint) {
                    if ($hint !== null) {
                        $additionalTags = $this->getTagsFromVerbHint($hint, $tenseTags, $auxiliaryTags);
                        $tagIds = array_merge($tagIds, $additionalTags);
                    }
                }
            }

            // Determine source_id based on question detail/topic
            $sourceId = $sources['yes_no']; // Default fallback
            if (isset($question['detail']) && isset($sources[$question['detail']])) {
                $sourceId = $sources[$question['detail']];
            }

            // Get theory text block UUID based on question detail type
            $theoryTextBlockUuid = $this->getTheoryTextBlockUuid($question['detail'] ?? null);

            $items[] = [
                'uuid' => $uuid,
                'question' => $question['question'],
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 2,
                'type' => 0,
                'level' => $question['level'],
                'theory_text_block_uuid' => $theoryTextBlockUuid,
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => $answers,
                'options' => $flatOptions,
                'variants' => [],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $question['answers'],
                'option_markers' => $optionMarkers,
                'hints' => $question['hints'],
                'explanations' => $question['explanations'],
                'gap_tags' => $gapTagsPerMarker, // Store per-marker gap tags in meta
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    /**
     * Get the theory text block UUID for a question type.
     * Links questions to theory blocks from TypesOfQuestions seeders.
     *
     * @param  string|null  $questionType  The question type (detail field)
     * @return string|null The UUID of the related theory text block
     */
    private function getTheoryTextBlockUuid(?string $questionType): ?string
    {
        if ($questionType === null) {
            return null;
        }

        // Map question types to their theory seeder classes and block keys
        $theoryMappings = [
            'yes_no' => [
                'seeder' => 'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsYesNoQuestionsGeneralQuestionsTheorySeeder',
                'uuid_key' => 'usage-panels-do-does-did',
            ],
            'wh_questions' => [
                'seeder' => 'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsWhQuestionsSpecialQuestionsTheorySeeder',
                'uuid_key' => 'usage-panels-who',
            ],
            'subject_questions' => [
                'seeder' => 'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsWhQuestionsSpecialQuestionsTheorySeeder',
                'uuid_key' => 'usage-panels-structure',
            ],
            'tag_questions' => [
                'seeder' => 'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsQuestionTagsDisjunctiveQuestionsTheorySeeder',
                'uuid_key' => 'usage-panels-to-be',
            ],
            'indirect_questions' => [
                // Note: There's no specific indirect questions theory seeder, using Wh-questions as fallback
                'seeder' => 'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsWhQuestionsSpecialQuestionsTheorySeeder',
                'uuid_key' => 'forms-grid-what-is',
            ],
            'alternative_questions' => [
                'seeder' => 'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsAlternativeQuestionsTheorySeeder',
                'uuid_key' => 'usage-panels-basic-structure',
            ],
            'negative_questions' => [
                'seeder' => 'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsNegativeQuestionsTheorySeeder',
                'uuid_key' => 'usage-panels-do-does-did',
            ],
        ];

        if (! isset($theoryMappings[$questionType])) {
            return null;
        }

        $mapping = $theoryMappings[$questionType];

        return TextBlockUuidGenerator::generateWithKey($mapping['seeder'], $mapping['uuid_key']);
    }

    /**
     * Extract tense/structure and auxiliary tags from verb_hint
     */
    private function getTagsFromVerbHint(string $verbHint, array $tenseTags, array $auxiliaryTags): array
    {
        $tags = [];
        $hint = strtolower($verbHint);

        // Tense/structure tags
        if (preg_match('/present simple|auxiliary.*questions|auxiliary for 3rd person/', $hint)) {
            $tags[] = $tenseTags['present_simple'];
            $tags[] = $auxiliaryTags['do_does_did'];
        }
        if (preg_match('/past simple|past.*auxiliary|\bdid\b|past event|past without auxiliary/', $hint)) {
            $tags[] = $tenseTags['past_simple'];
            if (! preg_match('/without auxiliary/', $hint)) {
                $tags[] = $auxiliaryTags['do_does_did'];
            }
        }
        if (preg_match('/\bfuture\b|\bwill\b/', $hint) && ! preg_match('/would/', $hint)) {
            $tags[] = $tenseTags['future_simple'];
            $tags[] = $auxiliaryTags['will_would'];
        }
        if (preg_match('/present continuous|be \+ -ing|continuous tense/', $hint) && ! preg_match('/perfect/', $hint)) {
            $tags[] = $tenseTags['present_continuous'];
            $tags[] = $auxiliaryTags['be_auxiliary'];
        }
        if (preg_match('/past continuous/', $hint)) {
            $tags[] = $tenseTags['past_continuous'];
            $tags[] = $auxiliaryTags['be_auxiliary'];
        }
        if (preg_match('/present perfect(?! continuous)|perfect passive|perfect tense|experience question|life experience/', $hint)) {
            $tags[] = $tenseTags['present_perfect'];
            $tags[] = $auxiliaryTags['have_has_had'];
        }
        if (preg_match('/past perfect(?! continuous)/', $hint)) {
            $tags[] = $tenseTags['past_perfect'];
            $tags[] = $auxiliaryTags['have_has_had'];
        }
        if (preg_match('/present perfect continuous/', $hint)) {
            $tags[] = $tenseTags['present_perfect_continuous'];
            $tags[] = $auxiliaryTags['have_has_had'];
        }
        if (preg_match('/past perfect continuous/', $hint)) {
            $tags[] = $tenseTags['past_perfect_continuous'];
            $tags[] = $auxiliaryTags['have_has_had'];
        }
        if (preg_match('/\bto be\b|be \(|^be$|state with|weather/', $hint)) {
            $tags[] = $tenseTags['to_be'];
            $tags[] = $auxiliaryTags['be_auxiliary'];
        }
        if (preg_match('/modal|\bcan\b|could|should|must|\bmay\b|might|would|ought/', $hint)) {
            $tags[] = $tenseTags['modal_verbs'];

            if (preg_match('/\bcan\b|could/', $hint)) {
                $tags[] = $auxiliaryTags['can_could'];
            }
            if (preg_match('/should/', $hint)) {
                $tags[] = $auxiliaryTags['should'];
            }
            if (preg_match('/must/', $hint)) {
                $tags[] = $auxiliaryTags['must'];
            }
            if (preg_match('/\bmay\b|might/', $hint)) {
                $tags[] = $auxiliaryTags['may_might'];
            }
            if (preg_match('/would/', $hint)) {
                $tags[] = $auxiliaryTags['will_would'];
            }
        }

        return array_unique($tags);
    }

    /**
     * Flatten nested options array into a single array of unique options.
     */
    private function flattenOptions(array $options): array
    {
        $flat = [];
        foreach ($options as $values) {
            if (is_array($values)) {
                foreach ($values as $value) {
                    if (! in_array($value, $flat, true)) {
                        $flat[] = $value;
                    }
                }
            } elseif (! in_array($values, $flat, true)) {
                $flat[] = $values;
            }
        }

        return $flat;
    }

    private function resolveTagNames(array $tagIds): array
    {
        $resolved = [];

        foreach ($tagIds as $id) {
            if (! is_int($id)) {
                continue;
            }

            if (isset($this->tagNameCache[$id])) {
                $resolved[] = $this->tagNameCache[$id];
                continue;
            }

            $name = Tag::find($id)?->name;

            if ($name) {
                $this->tagNameCache[$id] = $name;
                $resolved[] = $name;
            }
        }

        return array_values(array_unique(array_filter($resolved)));
    }

    private function buildMarkerTagChain(
        array $gapTagNames,
        array $gapTagIds,
        array $fallbackVerbHintTags,
        array $anchorTagNames,
        array $detailTagNames
    ): array {
        $tags = [];

        $tags = array_merge($tags, $anchorTagNames);
        $tags = array_merge($tags, $detailTagNames);
        $tags = array_merge($tags, $gapTagNames);

        if (empty($gapTagNames)) {
            $tags = array_merge($tags, $this->resolveTagNames($gapTagIds));
            $tags = array_merge($tags, $this->resolveTagNames($fallbackVerbHintTags));
        }

        $normalized = array_values(
            array_unique(
                array_map(fn ($tag) => trim((string) $tag), $tags)
            )
        );

        return array_slice(array_filter($normalized), 0, 6);
    }

    private function buildQuestions(): array
    {
        $questions = [];

        // SET 1 - Mixed question types (12 per level)
        $questions = array_merge($questions, $this->getA1Questions());
        $questions = array_merge($questions, $this->getA2Questions());
        $questions = array_merge($questions, $this->getB1Questions());
        $questions = array_merge($questions, $this->getB2Questions());
        $questions = array_merge($questions, $this->getC1Questions());
        $questions = array_merge($questions, $this->getC2Questions());

        // SET 2 - Organized by question type (proportional distribution)
        $questions = array_merge($questions, $this->getSet2YesNoQuestions());
        $questions = array_merge($questions, $this->getSet2WhQuestions());
        $questions = array_merge($questions, $this->getSet2SubjectQuestions());
        $questions = array_merge($questions, $this->getSet2TagQuestions());
        $questions = array_merge($questions, $this->getSet2IndirectQuestions());

        // SET 3 - Multiple fill-in-the-blank questions ({a1}...{aN})
        $questions = array_merge($questions, $this->getMultipleFillInBlankQuestions());

        return $questions;
    }

    private function getA1Questions(): array
    {
        return [
            [
                'level' => 'A1',
                'question' => '{a1} you like pizza?',
                'answers' => ['a1' => 'Do'],
                'options' => ['Do', 'Does', 'Are', 'Is'],
                'verb_hint' => 'auxiliary verb for questions',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'У Present Simple для формування загальних питань (Yes/No questions) потрібне допоміжне дієслово. Вибір допоміжного дієслова залежить від підмета речення та типу основного дієслова. Зверніть увагу, що "like" — це дієслово дії (action verb), а не дієслово стану "to be". Формула для питань: Auxiliary + Subject + Base verb.'],
                'explanations' => [
                    'Do' => 'Do — це допоміжне дієслово Present Simple, яке використовується з підметами I, you, we, they. Воно допомагає формувати питання з дієсловами дії. Структура: Do + you + базова форма дієслова (без закінчень).',
                    'Does' => 'Does — це допоміжне дієслово Present Simple для третьої особи однини (he, she, it). З підметом "you" це допоміжне дієслово не узгоджується граматично. Порівняйте: Does she like pizza? (вона) vs Do you like pizza? (ти/ви).',
                    'Are' => 'Are — це форма дієслова "to be" для множини та другої особи. Дієслово "to be" використовується для опису стану, професії, місцезнаходження тощо. Але "like" — це дієслово дії, яке потребує допоміжного дієслова do/does для питань.',
                    'Is' => 'Is — це форма дієслова "to be" для третьої особи однини (he, she, it). По-перше, воно не узгоджується з "you", по-друге, "to be" не поєднується з дієсловами дії типу "like" у питаннях Present Simple.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} she speak English?',
                'answers' => ['a1' => 'Does'],
                'options' => ['Does', 'Do', 'Is', 'Can'],
                'verb_hint' => 'auxiliary verb for 3rd person',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'У Present Simple питання формуються за допомогою допоміжних дієслів. Підмет "she" належить до третьої особи однини. Для третьої особи однини (he, she, it) існує спеціальна форма допоміжного дієслова. Зверніть увагу: "speak" — дієслово дії, тому потрібне саме допоміжне дієслово, а не "to be".'],
                'explanations' => [
                    'Does' => 'Does — допоміжне дієслово Present Simple для третьої особи однини (he, she, it). Після does основне дієслово завжди стоїть у базовій формі без закінчення -s. Структура: Does + she/he/it + base verb.',
                    'Do' => 'Do — допоміжне дієслово Present Simple, але воно узгоджується з підметами I, you, we, they. Підмет "she" — це третя особа однини, яка потребує іншої форми допоміжного дієслова.',
                    'Is' => 'Is — форма дієслова "to be" для третьої особи однини. Однак "to be" використовується для стану, опису, професії. Дієслово "speak" є дієсловом дії, тому потребує допоміжного дієслова do/does, а не to be.',
                    'Can' => 'Can — модальне дієслово, що виражає здатність або можливість. Воно змінює значення речення з простого питання на питання про здібності. Порівняйте: загальне питання про факт vs питання про здатність.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'What {a1} your name?',
                'answers' => ['a1' => 'is'],
                'options' => ['is', 'are', 'does', 'do'],
                'verb_hint' => 'be',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Це Wh-питання про ім'я людини. У таких питаннях використовується дієслово 'to be', яке зв'язує підмет (your name) з присудком. Вибір форми дієслова to be залежить від числа підмета. 'Your name' — це однина (одне ім'я), тому потрібна відповідна форма to be."],
                'explanations' => [
                    'is' => "Is — форма дієслова 'to be' для третьої особи однини. 'Your name' граматично є однинним підметом (одне ім'я, навіть якщо воно складається з кількох слів), тому узгоджується з формою для однини.",
                    'are' => "Are — форма дієслова 'to be' для множини (they are) та другої особи (you are). Але підмет тут 'your name' (твоє ім'я), а не 'you'. 'Name' — іменник в однині, тому потребує форми для однини.",
                    'does' => "Does — допоміжне дієслово для формування питань з дієсловами дії у Present Simple. Але тут немає дієслова дії — ми питаємо про стан/ідентичність (що є твоїм ім'ям), тому потрібне дієслово 'to be'.",
                    'do' => "Do — допоміжне дієслово для питань з дієсловами дії. Конструкція 'What is your name?' використовує дієслово to be для зв'язку підмета з його ідентичністю, а не дієслово дії.",
                ],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} they at home?',
                'answers' => ['a1' => 'Are'],
                'options' => ['Are', 'Is', 'Do', 'Does'],
                'verb_hint' => 'be',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Це загальне питання (Yes/No question) про місцезнаходження. Фраза 'at home' вказує на місце, а не на дію. Коли ми питаємо про стан чи місцезнаходження, використовуємо дієслово 'to be'. Форма to be залежить від підмета: they — це множина/третя особа множини."],
                'explanations' => [
                    'Are' => "Are — форма дієслова 'to be' для множини (we, you, they). Підмет 'they' — це третя особа множини, тому узгоджується саме з цією формою. У питаннях to be виноситься на початок: Are + they + місце.",
                    'Is' => "Is — форма дієслова 'to be' для третьої особи однини (he, she, it). Підмет 'they' є множиною, тому ця форма граматично не узгоджується з підметом.",
                    'Do' => "Do — допоміжне дієслово для формування питань з дієсловами дії (run, eat, work). Але 'at home' не є дією — це місцезнаходження, яке описується через дієслово to be, а не через дієслова дії.",
                    'Does' => "Does — допоміжне дієслово Present Simple для третьої особи однини з дієсловами дії. По-перше, 'they' — множина, по-друге, тут йдеться про місцезнаходження (to be), а не про дію.",
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'Where {a1} you live?',
                'answers' => ['a1' => 'do'],
                'options' => ['do', 'does', 'are', 'is'],
                'verb_hint' => 'auxiliary for present tense questions',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Це Wh-питання про місце проживання. Дієслово 'live' — це дієслово дії (action verb), яке описує постійну дію/стан у Present Simple. Для формування питань з дієсловами дії потрібне допоміжне дієслово. Вибір форми допоміжного дієслова залежить від підмета речення."],
                'explanations' => [
                    'do' => "Do — допоміжне дієслово Present Simple для підметів I, you, we, they. Воно використовується для формування питань з дієсловами дії. Структура Wh-питання: Where + do + you + базова форма дієслова.",
                    'does' => "Does — допоміжне дієслово Present Simple для третьої особи однини (he, she, it). Підмет 'you' не належить до третьої особи однини, тому ця форма граматично не підходить.",
                    'are' => "Are — форма дієслова 'to be', яка використовується для опису стану, характеристик, місцезнаходження у конкретний момент. Але 'live' — це дієслово дії, яке потребує допоміжного дієслова do/does для питань.",
                    'is' => "Is — форма дієслова 'to be' для третьої особи однини. По-перше, 'you' не узгоджується з is, по-друге, 'live' — дієслово дії, не to be.",
                ],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} you go to school yesterday?',
                'answers' => ['a1' => 'Did'],
                'options' => ['Did', 'Do', 'Was', 'Were'],
                'verb_hint' => 'auxiliary for past tense questions',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Слово 'yesterday' вказує на минулий час (Past Simple). Дієслово 'go' — це дієслово дії. Для формування питань у Past Simple з дієсловами дії потрібне допоміжне дієслово минулого часу. Важливо: після цього допоміжного дієслова основне дієслово стоїть у базовій формі (go, а не went)."],
                'explanations' => [
                    'Did' => "Did — допоміжне дієслово Past Simple для формування питань з дієсловами дії. Воно однакове для всіх підметів (I, you, he, she, it, we, they). Після did завжди йде базова форма дієслова: Did + subject + base verb.",
                    'Do' => "Do — допоміжне дієслово Present Simple. Слово 'yesterday' чітко вказує на минулий час, тому потрібна форма минулого часу допоміжного дієслова.",
                    'Was' => "Was — форма дієслова 'to be' у минулому часі для I, he, she, it. Але 'go' — це дієслово дії, яке потребує допоміжного дієслова did для питань, а не форми to be.",
                    'Were' => "Were — форма дієслова 'to be' у минулому часі для you, we, they. Але 'go' — дієслово дії, а не стан. Питання з to be (was/were) формуються без основного дієслова дії.",
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'When {a1} she arrive?',
                'answers' => ['a1' => 'did'],
                'options' => ['did', 'does', 'was', 'is'],
                'verb_hint' => 'past tense auxiliary',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Це Wh-питання про час події у минулому. Дієслово 'arrive' — дієслово дії, що описує завершену подію. Контекст питання вказує на минулий час (коли вона прибула?). Для питань у Past Simple з дієсловами дії потрібне відповідне допоміжне дієслово."],
                'explanations' => [
                    'did' => "Did — допоміжне дієслово Past Simple. Воно використовується для всіх підметів (she, he, I, they тощо) при формуванні питань з дієсловами дії у минулому часі. Структура: When + did + she + arrive (базова форма).",
                    'does' => "Does — допоміжне дієслово Present Simple для третьої особи однини. Питання 'When did she arrive?' стосується минулої події (прибуття, яке вже відбулося), тому теперішній час не підходить.",
                    'was' => "Was — форма дієслова 'to be' у минулому часі для she/he/it. Але 'arrive' — це дієслово дії, а не стан. З дієсловами дії у минулому використовується did, а не was/were.",
                    'is' => "Is — форма дієслова 'to be' у теперішньому часі. По-перше, контекст вказує на минулий час, по-друге, 'arrive' — дієслово дії, яке не поєднується з to be у цій конструкції.",
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'Who {a1} this book?',
                'answers' => ['a1' => 'wrote'],
                'options' => ['wrote', 'write', 'did write', 'writes'],
                'verb_hint' => 'write',
                'detail' => 'subject_questions',
                'hints' => ['a1' => "Це питання про підмет (Subject Question) — ми питаємо, ХТО виконав дію. У питаннях про підмет (коли Who/What є підметом речення) структура відрізняється від звичайних питань. Контекст вказує на минулу подію (книга вже написана). Зверніть увагу на особливість формування таких питань."],
                'explanations' => [
                    'wrote' => "Wrote — форма Past Simple дієслова 'write'. У питаннях про підмет (Subject Questions) дієслово стоїть у тій самій формі, що й у стверджувальному реченні, без допоміжного дієслова. Структура: Who + V2 (Past Simple form) + object.",
                    'write' => "Write — базова форма (інфінітив) дієслова. Контекст питання вказує на минулу подію (книга вже існує, хтось її написав), тому потрібна форма минулого часу.",
                    'did write' => "Did write — конструкція з допоміжним дієсловом did. Однак у питаннях про підмет (коли Who є підметом) допоміжне дієслово не використовується. Порівняйте: 'Who wrote?' (питання про підмет) vs 'What did she write?' (питання про об'єкт).",
                    'writes' => "Writes — форма Present Simple для третьої особи однини. Контекст питання вказує на минулу подію (книга вже написана), тому теперішній час не відповідає ситуації.",
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'You like coffee, {a1}?',
                'answers' => ['a1' => "don't you"],
                'options' => ["don't you", 'do you', "aren't you", 'are you'],
                'verb_hint' => 'Use negative tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Це розділове питання (Tag Question). Головне речення 'You like coffee' є ствердним і використовує дієслово дії 'like' у Present Simple. Правило для tag questions: якщо головне речення ствердне, tag має бути заперечним, і навпаки. Tag формується з допоміжного дієслова + займенника."],
                'explanations' => [
                    "don't you" => "Don't you — заперечний tag з допоміжним дієсловом 'do' (для Present Simple з you). Головне речення ствердне (You like), тому tag має бути заперечним. Займенник 'you' повторює підмет речення.",
                    'do you' => "Do you — ствердний tag. Але правило tag questions говорить: ствердне речення + заперечний tag. Оскільки 'You like coffee' — ствердне речення, ствердний tag порушує це правило.",
                    "aren't you" => "Aren't you — заперечний tag з дієсловом 'to be'. Але головне речення використовує дієслово дії 'like', а не to be. Tag має відповідати допоміжному дієслову головного речення (do для Present Simple з дієсловами дії).",
                    'are you' => "Are you — ствердний tag з дієсловом 'to be'. Тут дві невідповідності: 1) головне речення використовує 'like' (дієслово дії), а не to be; 2) після ствердного речення потрібен заперечний tag.",
                ],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} you swim?',
                'answers' => ['a1' => 'Can'],
                'options' => ['Can', 'Do', 'Are', 'Does'],
                'verb_hint' => 'modal verb',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Контекст питання вказує на здатність людини виконувати дію (плавати). Для вираження здатності, можливості чи вміння в англійській мові використовуються модальні дієслова. Модальні дієслова не потребують допоміжного дієслова для формування питань — вони самі виносяться на початок речення."],
                'explanations' => [
                    'Can' => "Can — модальне дієслово, що виражає здатність, вміння або можливість. Для питань з модальними дієсловами вони виносяться на початок речення: Can + subject + base verb. Після модальних дієслів завжди йде базова форма дієслова.",
                    'Do' => "Do — допоміжне дієслово Present Simple для формування питань з дієсловами дії. Питання 'Do you swim?' означатиме 'Ти плаваєш?' (факт), а не 'Ти вмієш плавати?' (здатність). Різні значення потребують різних конструкцій.",
                    'Are' => "Are — форма дієслова 'to be'. Конструкція 'Are you swimming?' була б Present Continuous (Ти плаваєш зараз?). Але питання про здатність потребує іншої граматичної структури.",
                    'Does' => "Does — допоміжне дієслово Present Simple для третьої особи однини (he, she, it). По-перше, 'you' не третя особа однини. По-друге, питання про здатність формується інакше, ніж звичайне питання про факт.",
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'How {a1} you today?',
                'answers' => ['a1' => 'are'],
                'options' => ['are', 'is', 'do', 'does'],
                'verb_hint' => 'be',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Це питання про стан, самопочуття людини. Коли ми питаємо про стан (а не про дію), використовуємо дієслово 'to be'. Це також усталений англійський вираз привітання. Форма to be залежить від підмета речення."],
                'explanations' => [
                    'are' => "Are — форма дієслова 'to be' для you (та we, they). Питання 'How are you?' — усталений вираз для запитування про стан, самопочуття. Структура: How + are + you + (time marker).",
                    'is' => "Is — форма дієслова 'to be' для третьої особи однини (he, she, it). Підмет 'you' граматично не узгоджується з цією формою. Порівняйте: How is she? vs How are you?",
                    'do' => "Do — допоміжне дієслово для питань з дієсловами дії. Але питання про стан/самопочуття використовує дієслово to be, а не дієслова дії. 'How do you' потребувало б дієслова дії після (How do you feel?).",
                    'does' => "Does — допоміжне дієслово Present Simple для третьої особи однини. По-перше, 'you' не третя особа однини. По-друге, питання про стан використовує to be, а не допоміжне дієслово.",
                ],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} it cold last night?',
                'answers' => ['a1' => 'Was'],
                'options' => ['Was', 'Were', 'Did', 'Is'],
                'verb_hint' => 'be (past)',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Слово 'last night' вказує на минулий час. 'Cold' — це прикметник, що описує стан (погоду). Коли ми питаємо про стан чи характеристику в минулому, використовуємо минулу форму дієслова 'to be'. Форма залежить від підмета: 'it' — третя особа однини."],
                'explanations' => [
                    'Was' => "Was — форма минулого часу дієслова 'to be' для I, he, she, it. Підмет 'it' (безособовий підмет для погоди) узгоджується саме з цією формою. Структура: Was + it + adjective.",
                    'Were' => "Were — форма минулого часу дієслова 'to be' для you, we, they. Підмет 'it' — третя особа однини, тому ця форма граматично не підходить.",
                    'Did' => "Did — допоміжне дієслово Past Simple для питань з дієсловами дії. Але 'cold' — це прикметник (стан), а не дієслово дії. Питання про стан у минулому формується через was/were.",
                    'Is' => "Is — форма теперішнього часу дієслова 'to be'. Слова 'last night' чітко вказують на минулий час, тому теперішня форма не відповідає часовому контексту речення.",
                ],
            ],
        ];
    }

    // Placeholder methods for other levels - to be implemented
    private function getA2Questions(): array
    {
        return [
            [
                'level' => 'A2',
                'question' => 'Why {a1} he always late?',
                'answers' => ['a1' => 'is'],
                'options' => ['is', 'does', 'do', 'are'],
                'verb_hint' => 'be',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Це Wh-питання про причину стану. Слово 'late' — прикметник, що описує стан людини (бути запізнілим). Коли ми описуємо стан або характеристику за допомогою прикметника, використовуємо дієслово 'to be'. Форма to be залежить від підмета речення."],
                'explanations' => [
                    'is' => "Is — форма дієслова 'to be' для третьої особи однини (he, she, it). Конструкція 'be late' означає 'бути запізнілим' — описує стан. Структура: Why + is + he + adjective.",
                    'does' => "Does — допоміжне дієслово Present Simple для третьої особи з дієсловами дії. Але 'late' — це прикметник, а не дієслово. Для прикметників потрібне дієслово to be, а не do/does.",
                    'do' => "Do — допоміжне дієслово Present Simple для I, you, we, they. По-перше, 'he' — третя особа однини. По-друге, 'late' — прикметник, який поєднується з to be, а не з do.",
                    'are' => "Are — форма дієслова 'to be' для you, we, they. Підмет 'he' — третя особа однини, тому ця форма граматично не узгоджується.",
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Can you tell me where {a1}?',
                'answers' => ['a1' => 'the station is'],
                'options' => ['the station is', 'is the station', 'does the station', 'the station are'],
                'verb_hint' => 'Use statement order',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "Це непряме питання (Indirect Question). Непрямі питання вбудовані в інше речення і починаються зі слів типу 'Can you tell me', 'Do you know', 'I wonder'. Головна особливість непрямих питань — у них використовується прямий порядок слів (як у стверджувальному реченні), а не інверсія."],
                'explanations' => [
                    'the station is' => "У непрямих питаннях порядок слів такий самий, як у стверджувальному реченні: підмет + дієслово. 'The station' (підмет) стоїть перед 'is' (дієсловом). Порівняйте: Where is the station? (пряме) vs Can you tell me where the station is? (непряме).",
                    'is the station' => "Інверсія (дієслово перед підметом) використовується у прямих питаннях: Where is the station? Але у непрямих питаннях після 'Can you tell me where...' потрібен прямий порядок слів без інверсії.",
                    'does the station' => "Does — допоміжне дієслово для Present Simple з дієсловами дії. Але тут 'station' поєднується з 'to be' (станція знаходиться де), а не з дієсловом дії. Does не використовується з to be.",
                    'the station are' => "'The station' — іменник в однині (одна станція). Форма 'are' використовується з множиною. Однинний підмет потребує форми дієслова для однини.",
                ],
            ],
            [
                'level' => 'A2',
                'question' => "She doesn't speak French, {a1}?",
                'answers' => ['a1' => 'does she'],
                'options' => ['does she', "doesn't she", 'is she', "isn't she"],
                'verb_hint' => 'Use positive tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Це розділове питання (Tag Question). Головне речення \"She doesn't speak French\" є заперечним (містить doesn't). Основне правило tag questions: заперечне речення потребує ствердного tag, і навпаки. Tag формується з допоміжного дієслова головного речення + займенника."],
                'explanations' => [
                    'does she' => "У tag questions використовується протилежна полярність до головного речення. Головне речення заперечне (doesn't speak), тому tag має бути ствердним. Допоміжне дієслово з головного речення — does (doesn't = does + not), займенник — she.",
                    "doesn't she" => "Заперечний tag після заперечного речення порушує правило tag questions. Коли головне речення вже містить заперечення (doesn't), tag має бути ствердним для створення балансу в питанні.",
                    'is she' => "Is — форма дієслова 'to be'. Але головне речення використовує дієслово дії 'speak' з допоміжним 'does'. Tag має містити те саме допоміжне дієслово, що й у головному реченні.",
                    "isn't she" => "Isn't — заперечна форма to be. По-перше, головне речення використовує does (з дієсловом дії speak), а не to be. По-друге, після заперечного речення потрібен ствердний tag.",
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'How many languages {a1} she speak?',
                'answers' => ['a1' => 'does'],
                'options' => ['does', 'do', 'is', 'are'],
                'verb_hint' => 'auxiliary for 3rd person singular',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Це Wh-питання про кількість у Present Simple. Дієслово 'speak' — дієслово дії, що вимагає допоміжного дієслова для формування питання. Підмет 'she' — третя особа однини, що визначає форму допоміжного дієслова."],
                'explanations' => [
                    'does' => "Does — допоміжне дієслово Present Simple для третьої особи однини (he, she, it). Підмет 'she' потребує саме цієї форми. Структура: How many + noun + does + she + base verb.",
                    'do' => "Do — допоміжне дієслово Present Simple для I, you, we, they. Підмет 'she' — третя особа однини, яка граматично не узгоджується з формою do.",
                    'is' => "Is — форма дієслова 'to be'. Але 'speak' — дієслово дії, яке потребує допоміжного дієслова do/does для питань. Конструкція 'is she speak' граматично неможлива.",
                    'are' => "Are — форма дієслова 'to be' для множини. По-перше, 'she' — однина. По-друге, 'speak' — дієслово дії, яке не поєднується з to be у цій конструкції.",
                ],
            ],
            [
                'level' => 'A2',
                'question' => '{a1} they working now?',
                'answers' => ['a1' => 'Are'],
                'options' => ['Are', 'Is', 'Do', 'Does'],
                'verb_hint' => 'be + -ing',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Слово 'now' вказує на дію, що відбувається прямо зараз. Для опису дій у момент мовлення використовується Present Continuous. Ця часова форма складається з дієслова to be + дієслово з закінченням -ing. Форма to be залежить від підмета."],
                'explanations' => [
                    'Are' => "Are — форма дієслова 'to be' для they (а також we, you). Present Continuous формується як: be + V-ing. Для питання to be виноситься на початок: Are + they + working.",
                    'Is' => "Is — форма дієслова 'to be' для третьої особи однини (he, she, it). Підмет 'they' — множина/третя особа множини, тому ця форма не узгоджується.",
                    'Do' => "Do — допоміжне дієслово Present Simple. Але слово 'now' і форма 'working' (-ing) вказують на Present Continuous, який формується з to be, а не з do.",
                    'Does' => "Does — допоміжне дієслово Present Simple для третьої особи однини. По-перше, 'they' — множина. По-друге, 'working' — форма -ing, яка потребує to be для Present Continuous.",
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'What {a1} you doing?',
                'answers' => ['a1' => 'are'],
                'options' => ['are', 'is', 'do', 'does'],
                'verb_hint' => 'be + -ing',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Форма 'doing' (-ing закінчення) вказує на Present Continuous — час, який описує дію в процесі. Present Continuous складається з дієслова to be + V-ing. У питаннях to be виноситься на початок. Форма to be залежить від підмета."],
                'explanations' => [
                    'are' => "Are — форма дієслова 'to be' для you (а також we, they). Present Continuous потребує to be перед формою -ing. Структура Wh-питання: What + are + you + doing.",
                    'is' => "Is — форма дієслова 'to be' для третьої особи однини. Підмет 'you' граматично не узгоджується з is. Порівняйте: What is he doing? vs What are you doing?",
                    'do' => "Do — допоміжне дієслово Present Simple. Але 'doing' — це форма -ing, яка є частиною Present Continuous. Present Continuous завжди використовує to be, а не do/does.",
                    'does' => "Does — допоміжне дієслово Present Simple для третьої особи однини. Тут два невідповідності: 'you' — не третя особа однини, і форма 'doing' потребує to be, а не does.",
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Who {a1} the window?',
                'answers' => ['a1' => 'broke'],
                'options' => ['broke', 'did break', 'break', 'broken'],
                'verb_hint' => 'break',
                'detail' => 'subject_questions',
                'hints' => ['a1' => "Це питання про підмет (Subject Question) — ми питаємо, ХТО виконав дію. У питаннях про підмет граматична структура відрізняється від звичайних питань. Контекст вказує на минулу подію (вікно вже розбите). Зверніть увагу на особливість формування цього типу питань."],
                'explanations' => [
                    'broke' => "Broke — форма Past Simple дієслова 'break'. У питаннях про підмет (коли Who/What є підметом речення) дієслово стоїть у звичайній формі часу, без допоміжного дієслова did. Структура: Who + V2 + object.",
                    'did break' => "Конструкція 'did + base form' використовується у звичайних питаннях Past Simple: What did she break? Але у питаннях про підмет (Who broke?) допоміжне дієслово did не потрібне.",
                    'break' => "Break — базова форма (інфінітив) дієслова. Контекст вказує на минулу завершену подію (вікно вже розбите), тому потрібна форма минулого часу.",
                    'broken' => "Broken — третя форма дієслова (Past Participle, V3). Ця форма використовується у Perfect часах (have broken) або Passive Voice (was broken), але не для простих питань Past Simple.",
                ],
            ],
            [
                'level' => 'A2',
                'question' => '{a1} you come to the party?',
                'answers' => ['a1' => 'Will'],
                'options' => ['Will', 'Do', 'Are', 'Did'],
                'verb_hint' => 'future',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Контекст питання вказує на майбутню подію (вечірка, яка ще не відбулася). Для опису майбутніх дій або планів в англійській мові використовуються різні конструкції. Одна з основних — Future Simple з допоміжним дієсловом. Зверніть увагу на часовий контекст питання."],
                'explanations' => [
                    'Will' => "Will — допоміжне дієслово Future Simple. Воно використовується для всіх підметів (I, you, he, she, it, we, they) для вираження майбутніх дій, рішень, передбачень. Структура: Will + subject + base verb.",
                    'Do' => "Do — допоміжне дієслово Present Simple. Питання 'Do you come?' означало б питання про звичку або регулярну дію у теперішньому часі, а не про конкретну майбутню подію.",
                    'Are' => "Are — форма дієслова 'to be'. Конструкція 'Are you coming?' (Present Continuous) може виражати заплановану майбутню дію, але в поєднанні з базовою формою 'come' граматично неможлива.",
                    'Did' => "Did — допоміжне дієслово Past Simple. Питання 'Did you come?' стосувалося б минулої події (вечірки, яка вже відбулася), а не майбутньої.",
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'How much {a1} it cost?',
                'answers' => ['a1' => 'does'],
                'options' => ['does', 'do', 'is', 'are'],
                'verb_hint' => 'auxiliary for it/he/she',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Це Wh-питання про ціну у Present Simple. Дієслово 'cost' — дієслово дії, яке описує вартість. Для формування питань з дієсловами дії у Present Simple потрібне допоміжне дієслово. Підмет 'it' — третя особа однини."],
                'explanations' => [
                    'does' => "Does — допоміжне дієслово Present Simple для третьої особи однини (he, she, it). Підмет 'it' потребує саме цієї форми. Структура: How much + does + it + cost (base form).",
                    'do' => "Do — допоміжне дієслово Present Simple для I, you, we, they. Підмет 'it' — третя особа однини, яка граматично потребує форми does, а не do.",
                    'is' => "Is — форма дієслова 'to be'. Але 'cost' — дієслово дії (коштувати), яке потребує допоміжного дієслова do/does для питань, а не to be. Конструкція 'is it cost' граматично неможлива.",
                    'are' => "Are — форма дієслова 'to be' для множини. По-перше, 'it' — однина. По-друге, 'cost' — дієслово дії, яке не поєднується з to be у питаннях про ціну.",
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Do you know what time {a1}?',
                'answers' => ['a1' => 'the train left'],
                'options' => ['the train left', 'did the train leave', 'left the train', 'the train leave'],
                'verb_hint' => 'Use statement order',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "Це непряме питання (Indirect Question), вбудоване у головне речення 'Do you know...'. У непрямих питаннях використовується прямий порядок слів (підмет + дієслово), як у стверджувальному реченні. Контекст вказує на минулу подію (поїзд вже відправився)."],
                'explanations' => [
                    'the train left' => "У непрямих питаннях порядок слів такий самий, як у стверджувальному реченні: підмет (the train) + дієслово у відповідному часі (left). Допоміжне дієслово did не використовується у непрямих питаннях.",
                    'did the train leave' => "Конструкція з did та інверсією (did the train leave) використовується у прямих питаннях: What time did the train leave? Але після 'Do you know what time...' потрібен прямий порядок слів без did.",
                    'left the train' => "Порядок слів 'дієслово + підмет' не є природним для англійської мови у цьому контексті. У стверджувальних реченнях та непрямих питаннях підмет завжди перед дієсловом.",
                    'the train leave' => "Leave — базова форма дієслова. Але контекст вказує на минулу подію (ми питаємо про час, коли поїзд уже відправився), тому потрібна форма Past Simple (left).",
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Whose book {a1} this?',
                'answers' => ['a1' => 'is'],
                'options' => ['is', 'are', 'does', 'do'],
                'verb_hint' => 'be',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Це Wh-питання про володіння/власника. 'Whose' питає 'чий/чия/чиє'. У таких питаннях дієслово 'to be' зв'язує підмет з присудком. 'Book' — іменник в однині, що визначає форму to be. Вказівний займенник 'this' підтверджує однину."],
                'explanations' => [
                    'is' => "Is — форма дієслова 'to be' для однини. 'Book' — іменник в однині, 'this' — вказівний займенник однини. Конструкція: Whose + singular noun + is + this.",
                    'are' => "Are — форма дієслова 'to be' для множини. 'Book' — іменник в однині (одна книга). Якби було 'books', то використовувалося б 'are'. Порівняйте: Whose book is this? vs Whose books are these?",
                    'does' => "Does — допоміжне дієслово для питань з дієсловами дії у Present Simple. Але питання про володіння використовує дієслово to be (чия це книга?), а не дієслова дії.",
                    'do' => "Do — допоміжне дієслово Present Simple для питань з дієсловами дії. Питання 'Whose book is this?' використовує to be для встановлення ідентичності/власності, а не дієслово дії.",
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'They visited Paris last year, {a1}?',
                'answers' => ['a1' => "didn't they"],
                'options' => ["didn't they", 'did they', "weren't they", 'were they'],
                'verb_hint' => 'Use negative tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Це розділове питання (Tag Question). Головне речення 'They visited Paris last year' є ствердним і використовує Past Simple (visited). Правило tag questions: ствердне речення потребує заперечного tag, заперечне — ствердного. Tag формується з допоміжного дієслова відповідного часу + займенника."],
                'explanations' => [
                    "didn't they" => "У tag questions використовується протилежна полярність. Головне речення ствердне (They visited), тому tag має бути заперечним. 'Visited' — Past Simple, тому допоміжне дієслово — did (didn't у заперечній формі). Займенник they повторює підмет.",
                    'did they' => "Ствердний tag після ствердного речення порушує правило tag questions. Коли головне речення ствердне (They visited), tag має бути заперечним для створення балансу в питанні.",
                    "weren't they" => "Weren't — заперечна форма were (Past tense to be). Але головне речення використовує дієслово дії 'visited', а не to be. Tag має відповідати допоміжному дієслову головного речення (did для Past Simple з дієсловами дії).",
                    'were they' => "Were — форма to be у минулому часі. По-перше, головне речення використовує дієслово дії visited (потребує did). По-друге, після ствердного речення потрібен заперечний tag.",
                ],
            ],
        ];
    }

    private function getB1Questions(): array
    {
        return [
            // 1. Present Perfect - Have/Has
            [
                'level' => 'B1',
                'question' => '{a1} you ever been to London?',
                'answers' => ['a1' => 'Have'],
                'options' => ['Have', 'Has', 'Did', 'Do'],
                'verb_hint' => 'present perfect auxiliary',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Слово 'ever' (коли-небудь) часто використовується з Present Perfect для питань про життєвий досвід. Present Perfect формується з допоміжного дієслова have/has + Past Participle (V3). 'Been' — це V3 від 'be'. Вибір між have та has залежить від підмета."],
                'explanations' => [
                    'Have' => "Have — допоміжне дієслово Present Perfect для підметів I, you, we, they. Present Perfect з 'ever' використовується для питань про досвід протягом життя. Структура: Have + you + ever + V3.",
                    'Has' => "Has — допоміжне дієслово Present Perfect для третьої особи однини (he, she, it). Підмет 'you' граматично не узгоджується з has. Порівняйте: Has she ever been? vs Have you ever been?",
                    'Did' => "Did — допоміжне дієслово Past Simple. Питання з did зазвичай стосуються конкретного моменту в минулому, тоді як 'ever' вказує на будь-який момент протягом життя, що типово для Present Perfect.",
                    'Do' => "Do — допоміжне дієслово Present Simple для питань про регулярні дії або факти. Але з 'ever been' питаємо про досвід (чи був коли-небудь), що потребує Present Perfect.",
                ],
            ],

            // 2. Negative Question - Present Simple
            [
                'level' => 'B1',
                'question' => '{a1} he know about the meeting?',
                'answers' => ['a1' => "Doesn't"],
                'options' => ["Doesn't", "Don't", "Isn't", "Didn't"],
                'verb_hint' => 'negative question form',
                'detail' => 'negative_questions',
                'hints' => ['a1' => "Це заперечне питання (Negative Question). Заперечні питання виражають здивування, очікування підтвердження або риторичне питання. У Present Simple заперечне питання формується з заперечної форми допоміжного дієслова. Підмет 'he' — третя особа однини."],
                'explanations' => [
                    "Doesn't" => "Doesn't — заперечна форма does для третьої особи однини (he, she, it). Заперечне питання виражає здивування: 'Невже він не знає?' або очікування: 'Він же знає, правда?'. Структура: Doesn't + he + base verb.",
                    "Don't" => "Don't — заперечна форма do для I, you, we, they. Підмет 'he' — третя особа однини, яка потребує форми doesn't, а не don't.",
                    "Isn't" => "Isn't — заперечна форма is (to be). Але 'know' — дієслово дії, яке потребує допоміжного дієслова do/does для питань, а не to be.",
                    "Didn't" => "Didn't — заперечна форма did (Past Simple). Контекст питання про зустріч (meeting) вказує на теперішній час або майбутню подію, а не на минуле.",
                ],
            ],

            // 3. Alternative Question with "or"
            [
                'level' => 'B1',
                'question' => 'Do you prefer coffee {a1} tea?',
                'answers' => ['a1' => 'or'],
                'options' => ['or', 'and', 'but', 'nor'],
                'verb_hint' => 'choice connector',
                'detail' => 'alternative_questions',
                'hints' => ['a1' => "Це альтернативне питання (Alternative Question), яке пропонує вибір між двома варіантами. Альтернативні питання відрізняються від загальних (Yes/No) тим, що потребують конкретної відповіді, а не просто так/ні. Для з'єднання варіантів використовується спеціальний сполучник."],
                'explanations' => [
                    'or' => "Or — сполучник, що означає 'або' і використовується для пропозиції вибору між варіантами. Альтернативні питання завжди містять or: A or B? Відповідь — один із варіантів.",
                    'and' => "And — сполучник, що означає 'і' і об'єднує елементи. 'Coffee and tea' означало б обидва разом, а не вибір між ними. Питання з and не є альтернативним.",
                    'but' => "But — сполучник протиставлення, що означає 'але'. Він не використовується для пропозиції вибору в питаннях типу 'що ти надаєш перевагу'.",
                    'nor' => "Nor — сполучник, що використовується в заперечних конструкціях (neither...nor). Він не підходить для альтернативних питань про вибір.",
                ],
            ],

            // 4. Present Perfect Continuous - How long
            [
                'level' => 'B1',
                'question' => 'How long {a1} you been studying English?',
                'answers' => ['a1' => 'have'],
                'options' => ['have', 'has', 'are', 'do'],
                'verb_hint' => 'perfect continuous auxiliary',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "'How long' (як довго) часто вживається з Present Perfect Continuous для питань про тривалість дії, яка почалася в минулому і продовжується досі. Present Perfect Continuous: have/has + been + V-ing. Форма 'been studying' вказує на цей час."],
                'explanations' => [
                    'have' => "Have — допоміжне дієслово Present Perfect (Continuous) для I, you, we, they. Структура: have + been + V-ing. 'How long have you been studying?' — питання про тривалість процесу.",
                    'has' => "Has — допоміжне дієслово для he, she, it. Підмет 'you' потребує have, а не has. Порівняйте: How long has she been studying? vs How long have you been studying?",
                    'are' => "Are — форма to be для Present Continuous (are + V-ing). Але 'been studying' — це Perfect Continuous, який потребує have/has + been, а не просто are.",
                    'do' => "Do — допоміжне дієслово Present Simple. Але форма 'been studying' є частиною Perfect Continuous, який формується з have/has, а не do.",
                ],
            ],

            // 5. Tag Question - Present Perfect
            [
                'level' => 'B1',
                'question' => "They haven't arrived yet, {a1}?",
                'answers' => ['a1' => 'have they'],
                'options' => ['have they', "haven't they", 'did they', "didn't they"],
                'verb_hint' => 'positive tag after negative',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Це розділове питання (Tag Question) з Present Perfect. Головне речення 'They haven't arrived' є заперечним (містить haven't). Слово 'yet' підтверджує Present Perfect. Правило: заперечне речення потребує ствердного tag."],
                'explanations' => [
                    'have they' => "У tag questions протилежна полярність: заперечне речення (haven't arrived) потребує ствердного tag. Допоміжне дієслово з головного речення — have (haven't = have + not), займенник — they.",
                    "haven't they" => "Заперечний tag після заперечного речення порушує правило. Подвійне заперечення (haven't...haven't they) не використовується у стандартних tag questions.",
                    'did they' => "Did — допоміжне дієслово Past Simple. Але головне речення використовує Present Perfect (haven't arrived). Tag має відповідати часу головного речення.",
                    "didn't they" => "Didn't — заперечна форма Past Simple. По-перше, час не відповідає (Present Perfect vs Past Simple). По-друге, після заперечного речення потрібен ствердний tag.",
                ],
            ],

            // 6. Past Continuous - Were/Was
            [
                'level' => 'B1',
                'question' => 'What {a1} you doing at 8 pm yesterday?',
                'answers' => ['a1' => 'were'],
                'options' => ['were', 'was', 'did', 'have'],
                'verb_hint' => 'past continuous auxiliary',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "'Yesterday' та конкретний час (8 pm) вказують на минуле. Форма 'doing' (-ing) означає тривалу дію. Past Continuous описує дію, яка відбувалася у конкретний момент в минулому: was/were + V-ing. Вибір was/were залежить від підмета."],
                'explanations' => [
                    'were' => "Were — форма минулого часу to be для you, we, they. Past Continuous формується як: was/were + V-ing. Питання 'What were you doing?' — про дію в процесі в конкретний момент минулого.",
                    'was' => "Was — форма минулого часу to be для I, he, she, it. Підмет 'you' граматично узгоджується з were, а не was. Порівняйте: What was he doing? vs What were you doing?",
                    'did' => "Did — допоміжне дієслово Past Simple. Але форма 'doing' (-ing) є частиною Continuous часу. Past Simple ('What did you do?') описує завершену дію, а не процес.",
                    'have' => "Have — допоміжне дієслово Present Perfect. Контекст 'yesterday' та конкретний час чітко вказують на минуле, а не на зв'язок із теперішнім.",
                ],
            ],

            // 7. Indirect Question - Present Perfect
            [
                'level' => 'B1',
                'question' => 'Can you tell me where {a1}?',
                'answers' => ['a1' => 'she has gone'],
                'options' => ['she has gone', 'has she gone', 'she gone has', 'gone she has'],
                'verb_hint' => 'statement order in indirect question',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "Це непряме питання (Indirect Question) з Present Perfect. 'Has gone' — Present Perfect, який описує дію з результатом у теперішньому (вона пішла і зараз її немає). У непрямих питаннях порядок слів прямий, як у стверджувальному реченні."],
                'explanations' => [
                    'she has gone' => "У непрямих питаннях використовується прямий порядок слів: підмет (she) + допоміжне дієслово (has) + основне дієслово (gone). Це відрізняється від прямого питання: Where has she gone?",
                    'has she gone' => "Інверсія (has she) використовується у прямих питаннях: Where has she gone? Але після 'Can you tell me where...' потрібен прямий порядок слів без інверсії.",
                    'she gone has' => "Порядок 'підмет + V3 + has' не є граматично можливим в англійській мові. У Present Perfect has/have завжди стоїть перед Past Participle.",
                    'gone she has' => "Порядок 'V3 + підмет + has' нагадує мову Йоди з 'Зоряних воєн', але не є стандартною англійською граматикою.",
                ],
            ],

            // 8. Going to - Future
            [
                'level' => 'B1',
                'question' => '{a1} she going to join us?',
                'answers' => ['a1' => 'Is'],
                'options' => ['Is', 'Are', 'Does', 'Will'],
                'verb_hint' => 'be + going to',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Конструкція 'be going to + verb' виражає заплановані майбутні дії або наміри. Це альтернатива Future Simple (will). 'Going to' — фіксована фраза, яка потребує дієслова to be перед собою. Форма to be залежить від підмета."],
                'explanations' => [
                    'Is' => "Is — форма to be для третьої особи однини (he, she, it). Конструкція 'be going to': Is + she + going to + base verb. Питання виражає намір або план.",
                    'Are' => "Are — форма to be для you, we, they. Підмет 'she' — третя особа однини, яка потребує is, а не are. Порівняйте: Are you going to join? vs Is she going to join?",
                    'Does' => "Does — допоміжне дієслово Present Simple. Конструкція 'be going to' не поєднується з do/does. Структура: to be + going to + verb, а не does + going to.",
                    'Will' => "Will — допоміжне дієслово Future Simple. 'Will she join?' — правильне, але це інша конструкція. 'Going to' потребує to be, а не will.",
                ],
            ],

            // 9. Subject Question - Who
            [
                'level' => 'B1',
                'question' => 'Who {a1} the best answer?',
                'answers' => ['a1' => 'gave'],
                'options' => ['gave', 'did give', 'has given', 'gives'],
                'verb_hint' => 'past tense verb',
                'detail' => 'subject_questions',
                'hints' => ['a1' => "Це питання про підмет (Subject Question) — ми питаємо, ХТО виконав дію. Контекст вказує на минулу подію (хтось уже дав відповідь). У питаннях про підмет структура відрізняється від звичайних питань — допоміжне дієслово не додається."],
                'explanations' => [
                    'gave' => "Gave — форма Past Simple дієслова 'give'. У питаннях про підмет дієслово стоїть у звичайній формі часу без допоміжного дієслова. Структура: Who + V2 (Past Simple). Відповідь: 'John gave the best answer.'",
                    'did give' => "Конструкція 'did + base form' використовується у звичайних питаннях: What did she give? Але у питаннях про підмет (Who gave?) допоміжне дієслово did не потрібне.",
                    'has given' => "Has given — Present Perfect, який виражає зв'язок із теперішнім. Контекст 'the best answer' (означена подія) вказує на Past Simple, а не Perfect.",
                    'gives' => "Gives — форма Present Simple для третьої особи однини. Але контекст питання вказує на завершену минулу подію (відповідь уже дана).",
                ],
            ],

            // 10. Negative Question - Past Simple
            [
                'level' => 'B1',
                'question' => '{a1} you see the sign?',
                'answers' => ['a1' => "Didn't"],
                'options' => ["Didn't", "Don't", "Haven't", "Weren't"],
                'verb_hint' => 'negative past question',
                'detail' => 'negative_questions',
                'hints' => ['a1' => "Це заперечне питання (Negative Question), яке виражає здивування або очікування. Контекст 'see the sign' (побачити знак) вказує на конкретну минулу подію. Заперечне питання формується з заперечної форми допоміжного дієслова відповідного часу."],
                'explanations' => [
                    "Didn't" => "Didn't — заперечна форма did (Past Simple). Заперечне питання 'Didn't you see?' виражає здивування: 'Невже ти не бачив?' або 'Ти ж бачив, правда?'. Структура: Didn't + subject + base verb.",
                    "Don't" => "Don't — заперечна форма do (Present Simple). Питання 'Don't you see?' стосувалося б теперішнього моменту або звички, а не конкретної минулої події.",
                    "Haven't" => "Haven't — заперечна форма have (Present Perfect). Present Perfect використовується для досвіду або результату, але 'see the sign' — конкретна минула подія.",
                    "Weren't" => "Weren't — заперечна форма were (Past to be). Але 'see' — дієслово дії, яке потребує did для питань, а не to be.",
                ],
            ],

            // 11. Alternative Question - Is/Are
            [
                'level' => 'B1',
                'question' => 'Is it black {a1} white?',
                'answers' => ['a1' => 'or'],
                'options' => ['or', 'and', 'but', 'so'],
                'verb_hint' => 'alternative connector',
                'detail' => 'alternative_questions',
                'hints' => ['a1' => "Це альтернативне питання (Alternative Question), яке пропонує вибір між двома кольорами. Альтернативні питання структурно подібні до загальних (Yes/No), але пропонують конкретні варіанти для відповіді. Для з'єднання варіантів використовується спеціальний сполучник."],
                'explanations' => [
                    'or' => "Or — сполучник вибору, що означає 'або'. Альтернативні питання завжди містять or між варіантами. Відповідь на таке питання — один із запропонованих варіантів, а не yes/no.",
                    'and' => "And — сполучник об'єднання, що означає 'і'. 'Is it black and white?' — питання про те, чи має об'єкт обидва кольори одночасно, а не вибір між ними.",
                    'but' => "But — сполучник протиставлення, що означає 'але'. Він не використовується в питаннях для пропозиції вибору між варіантами.",
                    'so' => "So — сполучник наслідку, що означає 'тому'. Він не підходить для альтернативних питань, де потрібен сполучник вибору.",
                ],
            ],

            // 12. How often - Frequency question
            [
                'level' => 'B1',
                'question' => 'How often {a1} they meet?',
                'answers' => ['a1' => 'do'],
                'options' => ['do', 'does', 'are', 'have'],
                'verb_hint' => 'present simple auxiliary',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "'How often' (як часто) — питання про частоту дії. Дієслово 'meet' (зустрічатися) описує регулярну дію, тому використовується Present Simple. Для формування питань з дієсловами дії у Present Simple потрібне допоміжне дієслово. Підмет 'they' визначає його форму."],
                'explanations' => [
                    'do' => "Do — допоміжне дієслово Present Simple для I, you, we, they. Питання про частоту з 'How often' формується: How often + do/does + subject + base verb. Відповідь описуватиме частоту: daily, weekly тощо.",
                    'does' => "Does — допоміжне дієслово Present Simple для третьої особи однини (he, she, it). Підмет 'they' — множина, яка потребує do, а не does.",
                    'are' => "Are — форма дієслова 'to be'. Але 'meet' — дієслово дії, яке потребує допоміжного дієслова do/does для питань. Конструкція 'are they meet' граматично неможлива.",
                    'have' => "Have — допоміжне дієслово Present Perfect (have + V3). Але питання про частоту зазвичай використовує Present Simple (do/does), а не Perfect часи.",
                ],
            ],
        ];
    }

    private function getB2Questions(): array
    {
        return [
            // 1. Past Perfect
            [
                'level' => 'B2',
                'question' => '{a1} you finished the project before the deadline?',
                'answers' => ['a1' => 'Had'],
                'options' => ['Had', 'Have', 'Did', 'Were'],
                'verb_hint' => 'past perfect auxiliary',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Фраза 'before the deadline' (до дедлайну) вказує на дію, яка відбулася раніше за інший момент у минулому. Для опису такої послідовності подій використовується Past Perfect (передминулий час). Past Perfect формується з допоміжного дієслова + Past Participle (V3)."],
                'explanations' => [
                    'Had' => "Had — допоміжне дієслово Past Perfect для всіх підметів. Past Perfect описує дію, що відбулася раніше за інший момент у минулому. Структура: Had + subject + V3 (finished).",
                    'Have' => "Have — допоміжне дієслово Present Perfect. Present Perfect зв'язує минуле з теперішнім, але 'before the deadline' вказує на відносність двох минулих моментів.",
                    'Did' => "Did — допоміжне дієслово Past Simple. Past Simple описує завершену дію в минулому, але не підкреслює, що вона відбулася раніше за інший момент.",
                    'Were' => "Were — форма to be у минулому часі. Але 'finished' — Past Participle, який потребує have/had для формування Perfect часів, а не to be.",
                ],
            ],

            // 2. Modal Perfect - Should have
            [
                'level' => 'B2',
                'question' => 'What {a1} I done differently?',
                'answers' => ['a1' => 'should have'],
                'options' => ['should have', 'should', 'have', 'had'],
                'verb_hint' => 'modal + perfect',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Це питання про минулу дію, яку слід було виконати інакше. Modal Perfect (модальний перфект) використовується для вираження критики, поради або жалю щодо минулих дій. Форма 'done' — Past Participle (V3) — вказує на потребу Perfect конструкції."],
                'explanations' => [
                    'should have' => "Should have — Modal Perfect для вираження того, що було б правильним зробити в минулому. Структура: should have + V3. Виражає пораду або критику щодо минулого.",
                    'should' => "Should — модальне дієслово для порад у теперішньому/майбутньому ('You should do'). Але 'done' (V3) вказує на минулу дію, яка потребує Perfect форми.",
                    'have' => "Have — допоміжне дієслово Present Perfect. Але 'What have I done?' означає 'Що я зробив?', а не 'Що я мав би зробити інакше?'. Модальне значення потребує should.",
                    'had' => "Had — допоміжне дієслово Past Perfect. 'What had I done?' описує послідовність минулих подій, але не виражає модального значення (що слід було зробити).",
                ],
            ],

            // 3. Tag Question - Modal Perfect
            [
                'level' => 'B2',
                'question' => 'He could have told us earlier, {a1}?',
                'answers' => ['a1' => "couldn't he"],
                'options' => ["couldn't he", 'could he', "didn't he", "hasn't he"],
                'verb_hint' => 'negative tag with modal',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Це розділове питання (Tag Question) з Modal Perfect 'could have'. Головне речення ствердне і виражає можливість/здатність у минулому. Правило tag questions: ствердне речення потребує заперечного tag. Tag формується з модального дієслова головного речення."],
                'explanations' => [
                    "couldn't he" => "У tag questions після ствердного речення потрібен заперечний tag. Модальне дієслово в головному реченні — could, тому tag формується з couldn't + займенник (he).",
                    'could he' => "Ствердний tag після ствердного речення порушує правило tag questions. Ствердне + ствердне не створює правильного розділового питання.",
                    "didn't he" => "Didn't — форма Past Simple. Але головне речення використовує модальне дієслово could, і tag має відповідати саме цьому модальному дієслову, а не переходити на did.",
                    "hasn't he" => "Hasn't — Present Perfect. Але головне речення використовує could have (Modal Perfect), і tag має відповідати модальному дієслову could.",
                ],
            ],

            // 4. Negative Question - Present Perfect
            [
                'level' => 'B2',
                'question' => '{a1} you heard the news yet?',
                'answers' => ['a1' => "Haven't"],
                'options' => ["Haven't", "Didn't", "Don't", "Aren't"],
                'verb_hint' => 'negative perfect question',
                'detail' => 'negative_questions',
                'hints' => ['a1' => "Слово 'yet' (ще) типово вживається з Present Perfect. Це заперечне питання (Negative Question), яке виражає здивування: 'Невже ти ще не чув?' або очікування позитивної відповіді. Заперечне питання формується з заперечної форми допоміжного дієслова."],
                'explanations' => [
                    "Haven't" => "Haven't — заперечна форма have (Present Perfect). Заперечне питання 'Haven't you heard?' виражає здивування, що співрозмовник ще не знає новину. Слово 'yet' підтверджує Present Perfect.",
                    "Didn't" => "Didn't — заперечна форма Past Simple. Але 'yet' типово використовується з Present Perfect, а не Past Simple. 'Didn't you hear?' питало б про конкретний момент у минулому.",
                    "Don't" => "Don't — заперечна форма Present Simple. Але 'heard' — Past Participle, який вказує на Present Perfect, а 'yet' підтверджує цей час.",
                    "Aren't" => "Aren't — заперечна форма are (to be). Але 'heard' потребує допоміжного дієслова have для Present Perfect, а не to be.",
                ],
            ],

            // 5. Passive Voice - Present Perfect
            [
                'level' => 'B2',
                'question' => '{a1} the documents been reviewed?',
                'answers' => ['a1' => 'Have'],
                'options' => ['Have', 'Has', 'Were', 'Did'],
                'verb_hint' => 'perfect passive auxiliary',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Конструкція 'been reviewed' — це Present Perfect Passive. 'Reviewed' — дієприкметник, а 'been' вказує на Passive Voice у Perfect часі. Present Perfect Passive формується: have/has + been + V3. Підмет 'documents' (множина) визначає форму допоміжного дієслова."],
                'explanations' => [
                    'Have' => "Have — допоміжне дієслово Present Perfect для множини. 'Documents' — множина, тому потрібне have. Структура: Have + plural subject + been + V3.",
                    'Has' => "Has — допоміжне дієслово Present Perfect для однини (he, she, it). 'Documents' — множина, тому has граматично не узгоджується.",
                    'Were' => "Were — форма to be у Past Simple. 'Were reviewed' — Past Simple Passive, але 'been reviewed' вказує на Present Perfect Passive з have/has.",
                    'Did' => "Did — допоміжне дієслово Past Simple для активного стану. Воно не використовується з конструкцією 'been reviewed', яка є Perfect Passive.",
                ],
            ],

            // 6. Indirect Question - Past Perfect
            [
                'level' => 'B2',
                'question' => 'Do you know why {a1}?',
                'answers' => ['a1' => 'she had left early'],
                'options' => ['she had left early', 'had she left early', 'she left early had', 'early left she had'],
                'verb_hint' => 'statement order with had',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "Це непряме питання (Indirect Question) з Past Perfect. 'Had left' описує дію, що відбулася раніше за інший момент у минулому. У непрямих питаннях порядок слів прямий, як у стверджувальному реченні, без інверсії."],
                'explanations' => [
                    'she had left early' => "У непрямих питаннях порядок слів: підмет (she) + допоміжне дієслово (had) + Past Participle (left) + обставина (early). Це прямий порядок без інверсії.",
                    'had she left early' => "Інверсія (had she) використовується у прямих питаннях: Why had she left early? Але після 'Do you know why...' потрібен прямий порядок слів.",
                    'she left early had' => "Had не може стояти в кінці речення в англійській граматиці. Допоміжне дієслово завжди перед основним дієсловом: she had left.",
                    'early left she had' => "Такий порядок слів не відповідає жодній англійській граматичній структурі. Обставина (early) зазвичай стоїть в кінці.",
                ],
            ],

            // 7. Subject Question - What with Present Perfect
            [
                'level' => 'B2',
                'question' => 'What {a1} happened to your car?',
                'answers' => ['a1' => 'has'],
                'options' => ['has', 'have', 'did', 'was'],
                'verb_hint' => 'perfect tense for result',
                'detail' => 'subject_questions',
                'hints' => ['a1' => "Це питання про підмет (Subject Question) у Present Perfect. 'What' є підметом речення (що сталося?). Present Perfect використовується, коли результат події видимий у теперішньому (машина пошкоджена). Форма допоміжного дієслова залежить від числа підмета."],
                'explanations' => [
                    'has' => "Has — допоміжне дієслово Present Perfect для однини. 'What' як підмет граматично розглядається як третя особа однини, тому потребує has. У питаннях про підмет порядок слів такий самий, як у стверджувальному реченні.",
                    'have' => "Have — допоміжне дієслово Present Perfect для множини (I, you, we, they). Але 'What' як підмет граматично є однинним у такому контексті.",
                    'did' => "Did — допоміжне дієслово Past Simple. Але Past Simple (What did happen?) не підкреслює зв'язок з теперішнім результатом так, як Present Perfect.",
                    'was' => "Was — форма to be у Past Simple. Конструкція 'What was happened?' граматично неможлива, оскільки 'happen' — неперехідне дієслово і не вживається в Passive Voice таким чином.",
                ],
            ],

            // 8. Alternative Question - Would you like
            [
                'level' => 'B2',
                'question' => 'Would you like tea {a1} coffee?',
                'answers' => ['a1' => 'or'],
                'options' => ['or', 'and', 'nor', 'but'],
                'verb_hint' => 'choice between options',
                'detail' => 'alternative_questions',
                'hints' => ['a1' => "Це альтернативне питання (Alternative Question) з ввічливою формою 'Would you like'. Питання пропонує вибір між двома напоями. На відміну від загальних питань (Yes/No), альтернативні питання очікують конкретну відповідь — один із варіантів."],
                'explanations' => [
                    'or' => "Or — сполучник вибору, що означає 'або'. Альтернативні питання пропонують конкретні варіанти для відповіді. 'Would you like tea or coffee?' очікує відповідь 'Tea' або 'Coffee', а не 'Yes/No'.",
                    'and' => "And — сполучник об'єднання. 'Tea and coffee' означало б обидва напої одночасно. Це змінило б значення питання з вибору на пропозицію обох варіантів.",
                    'nor' => "Nor — сполучник для заперечних конструкцій (neither...nor). Він не використовується в ввічливих пропозиціях типу 'Would you like...'",
                    'but' => "But — сполучник протиставлення, що означає 'але'. Він не підходить для пропозиції вибору між варіантами в альтернативних питаннях.",
                ],
            ],

            // 9. Negative Question - Modal
            [
                'level' => 'B2',
                'question' => '{a1} it be better to postpone?',
                'answers' => ['a1' => "Wouldn't"],
                'options' => ["Wouldn't", "Won't", "Isn't", "Doesn't"],
                'verb_hint' => 'negative modal suggestion',
                'detail' => 'negative_questions',
                'hints' => ['a1' => "Це заперечне питання з модальним дієсловом для ввічливої пропозиції або поради. Заперечне питання виражає очікування згоди: 'Чи не краще було б...?' Для ввічливості та умовності використовується відповідна форма модального дієслова."],
                'explanations' => [
                    "Wouldn't" => "Wouldn't — заперечна форма would для ввічливих пропозицій та гіпотетичних ситуацій. 'Wouldn't it be better?' — ввічлива форма поради, яка очікує згоди співрозмовника.",
                    "Won't" => "Won't — заперечна форма will для майбутніх дій. 'Won't it be better?' менш ввічливо і більш категорично, ніж умовне 'wouldn't'.",
                    "Isn't" => "Isn't — заперечна форма is (to be). 'Isn't it better?' — питання про факт, тоді як 'Wouldn't it be better?' — пропозиція з умовним відтінком.",
                    "Doesn't" => "Doesn't — заперечна форма does для Present Simple. Конструкція 'Doesn't it be better' граматично неможлива, оскільки to be не поєднується з do/does.",
                ],
            ],

            // 10. Past Perfect Continuous
            [
                'level' => 'B2',
                'question' => 'How long {a1} they been waiting before the bus arrived?',
                'answers' => ['a1' => 'had'],
                'options' => ['had', 'have', 'were', 'did'],
                'verb_hint' => 'past perfect continuous',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Фраза 'before the bus arrived' вказує на відносність двох минулих подій. Питання про тривалість дії, яка відбувалася до іншого моменту в минулому, використовує Past Perfect Continuous: had + been + V-ing. Форма 'been waiting' підтверджує цей час."],
                'explanations' => [
                    'had' => "Had — допоміжне дієслово Past Perfect (Continuous) для всіх підметів. Past Perfect Continuous описує тривалу дію до певного моменту в минулому. Структура: had + been + V-ing.",
                    'have' => "Have — допоміжне дієслово Present Perfect (Continuous). Але 'before the bus arrived' вказує на минуле, а не на зв'язок з теперішнім.",
                    'were' => "Were — форма to be у минулому часі. 'Were waiting' — Past Continuous, який описує дію в минулому, але не підкреслює її тривалість до іншого моменту.",
                    'did' => "Did — допоміжне дієслово Past Simple. Воно не поєднується з конструкцією 'been waiting', яка є частиною Perfect Continuous.",
                ],
            ],

            // 11. Indirect Question - Whether
            [
                'level' => 'B2',
                'question' => "I'm not sure whether {a1} there or not.",
                'answers' => ['a1' => 'we should go'],
                'options' => ['we should go', 'should we go', 'we go should', 'go we should'],
                'verb_hint' => 'statement order with whether',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "Це непряме питання з 'whether' (чи). 'Whether...or not' виражає невизначеність між двома варіантами. У непрямих питаннях, навіть із модальними дієсловами типу should, використовується прямий порядок слів."],
                'explanations' => [
                    'we should go' => "У непрямих питаннях порядок слів такий самий, як у стверджувальному реченні: підмет (we) + модальне дієслово (should) + основне дієслово (go). Інверсія не використовується.",
                    'should we go' => "Інверсія (should we) використовується у прямих питаннях: Should we go? Але після 'I'm not sure whether...' потрібен прямий порядок слів.",
                    'we go should' => "Модальне дієслово should не може стояти після основного дієслова в англійській граматиці. Модальні дієслова завжди перед основним дієсловом.",
                    'go we should' => "Такий порядок слів (дієслово + підмет + модальне) не відповідає жодній стандартній англійській граматичній структурі.",
                ],
            ],

            // 12. Tag Question - Imperative
            [
                'level' => 'B2',
                'question' => "Let's start the meeting, {a1}?",
                'answers' => ['a1' => 'shall we'],
                'options' => ['shall we', "let's we", "don't we", 'will we'],
                'verb_hint' => 'suggestion tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Конструкція 'Let's' (let us) виражає пропозицію зробити щось разом. Це особливий випадок tag questions: після Let's використовується спеціальний tag, який також виражає запрошення до спільної дії."],
                'explanations' => [
                    'shall we' => "Shall we — стандартний tag після Let's. Він запрошує співрозмовника погодитися з пропозицією. Це єдиний правильний tag для конструкції Let's у формальній граматиці.",
                    "let's we" => "Конструкція 'let's we' не існує в англійській граматиці. Tag має бути окремою граматичною одиницею з допоміжним дієсловом + займенником.",
                    "don't we" => "Don't we — tag для стверджувальних речень з we у Present Simple. Але Let's — це особлива конструкція, яка потребує shall we.",
                    'will we' => "Will we — можливий у розмовній мові, особливо у британському англійському, але shall we є більш стандартним і поширеним tag для Let's.",
                ],
            ],
        ];
    }

    private function getC1Questions(): array
    {
        return [
            // 1. Negative Inversion - Rarely
            [
                'level' => 'C1',
                'question' => 'Rarely {a1} such dedication to a project.',
                'answers' => ['a1' => 'have I seen'],
                'options' => ['have I seen', 'I have seen', 'did I see', 'I saw'],
                'verb_hint' => 'inversion after negative adverb',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Коли заперечний або обмежувальний прислівник (rarely, seldom, never, hardly) стоїть на початку речення для емфази, використовується інверсія — допоміжне дієслово виноситься перед підметом. Present Perfect підкреслює накопичений досвід."],
                'explanations' => [
                    'have I seen' => "Після rarely на початку речення потрібна інверсія: допоміжне дієслово (have) перед підметом (I). Present Perfect (have seen) виражає досвід протягом життя. Це формальна літературна конструкція.",
                    'I have seen' => "Прямий порядок слів (підмет + дієслово) використовується в звичайних реченнях. Але коли rarely стоїть на початку для емфази, потрібна інверсія.",
                    'did I see' => "Did I see — інверсія з Past Simple. Однак Present Perfect (have seen) краще підходить для вираження накопиченого досвіду ('за все життя рідко бачив').",
                    'I saw' => "Без інверсії та в Past Simple. Порушує два правила: 1) після rarely на початку потрібна інверсія; 2) для досвіду краще Present Perfect.",
                ],
            ],

            // 2. Tag Question - Modal Perfect (ought to)
            [
                'level' => 'C1',
                'question' => 'She ought to have been more careful, {a1}?',
                'answers' => ['a1' => "oughtn't she"],
                'options' => ["oughtn't she", 'ought she', "shouldn't she", "didn't she"],
                'verb_hint' => 'tag from ought to',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Конструкція 'ought to have + V3' виражає критику або жаль щодо минулої дії. Це Modal Perfect з формальним модальним дієсловом 'ought to'. Tag формується з модального дієслова головного речення. Ствердне речення потребує заперечного tag."],
                'explanations' => [
                    "oughtn't she" => "Oughtn't — заперечна форма ought. Після ствердного 'ought to have' потрібен заперечний tag. Це формальна британська форма, яка зберігає відповідність модальному дієслову.",
                    'ought she' => "Ствердний tag після ствердного речення порушує правило tag questions. Потрібна протилежна полярність: ствердне речення + заперечний tag.",
                    "shouldn't she" => "Shouldn't — заперечна форма should, яке семантично близьке до ought. У розмовній мові допустимо, але формально tag має відповідати модальному дієслову головного речення (oughtn't).",
                    "didn't she" => "Didn't — Past Simple. Але 'ought to have' — Modal Perfect, і tag має відповідати саме цій конструкції, а не переходити на Past Simple.",
                ],
            ],

            // 3. Indirect Question - Modal Perfect
            [
                'level' => 'C1',
                'question' => 'Do you have any idea where {a1}?',
                'answers' => ['a1' => 'he might have gone'],
                'options' => ['he might have gone', 'might he have gone', 'he have might gone', 'might have he gone'],
                'verb_hint' => 'statement order with might have',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "Це непряме питання з Modal Perfect 'might have + V3', яке виражає припущення про минулу дію. У непрямих питаннях, навіть зі складними модальними конструкціями, використовується прямий порядок слів."],
                'explanations' => [
                    'he might have gone' => "У непрямих питаннях порядок слів: підмет (he) + модальне дієслово (might) + have + V3 (gone). Це прямий порядок, як у стверджувальному реченні.",
                    'might he have gone' => "Інверсія (might he) використовується у прямих питаннях: Where might he have gone? Але після 'Do you have any idea where...' потрібен прямий порядок.",
                    'he have might gone' => "Have не може стояти перед might. У Modal Perfect структура фіксована: modal + have + V3.",
                    'might have he gone' => "Підмет не може стояти між have та V3. Правильна структура: subject + might + have + V3.",
                ],
            ],

            // 4. Negative Question - Perfect Continuous
            [
                'level' => 'C1',
                'question' => '{a1} we been pursuing the wrong strategy?',
                'answers' => ['a1' => "Haven't"],
                'options' => ["Haven't", "Hadn't", "Aren't", "Weren't"],
                'verb_hint' => 'negative perfect continuous',
                'detail' => 'negative_questions',
                'hints' => ['a1' => "Це заперечне питання у Present Perfect Continuous, яке виражає здогадку або занепокоєння. Конструкція 'been pursuing' (been + V-ing) вказує на Perfect Continuous. Заперечне питання формується з заперечної форми допоміжного дієслова."],
                'explanations' => [
                    "Haven't" => "Haven't — заперечна форма have для Present Perfect (Continuous). Питання виражає занепокоєння: 'Чи не помилялися ми весь цей час?' Present Perfect Continuous підкреслює тривалість процесу.",
                    "Hadn't" => "Hadn't — заперечна форма had для Past Perfect (Continuous). Це вказувало б на дію до іншого моменту в минулому, а не на зв'язок з теперішнім.",
                    "Aren't" => "Aren't — заперечна форма are для Present Continuous. Але 'been pursuing' — Perfect Continuous (have/has + been + V-ing), не просто Continuous.",
                    "Weren't" => "Weren't — заперечна форма were для Past Continuous. Час не відповідає конструкції 'been pursuing', яка є Perfect Continuous.",
                ],
            ],

            // 5. Subject Question - Modal Perfect
            [
                'level' => 'C1',
                'question' => 'Who {a1} sent this message?',
                'answers' => ['a1' => 'could have'],
                'options' => ['could have', 'could', 'did', 'has'],
                'verb_hint' => 'modal speculation about past',
                'detail' => 'subject_questions',
                'hints' => ['a1' => "Це питання про підмет (Subject Question) з Modal Perfect для вираження здогадки про минулу дію. 'Sent' — Past Participle (V3), що вказує на Perfect конструкцію. Питання виражає невизначеність: 'Хто міг надіслати?'"],
                'explanations' => [
                    'could have' => "Could have + V3 (Modal Perfect) виражає здогадку або можливість у минулому. У питаннях про підмет порядок слів: Who + could have + V3. Виражає невпевненість: 'Хто міг це зробити?'",
                    'could' => "Could без have виражає здатність або можливість у теперішньому/майбутньому. Але 'sent' (V3) вказує на минулу дію, яка потребує Perfect форми.",
                    'did' => "Did — допоміжне дієслово Past Simple для констатації факту. 'Who did send?' питає про факт, а не здогадку. Modal Perfect (could have) додає відтінок невпевненості.",
                    'has' => "Has — допоміжне дієслово Present Perfect. 'Who has sent?' — питання про факт із зв'язком до теперішнього. Але контекст вимагає здогадки, яку виражає could have.",
                ],
            ],

            // 6. Inversion - Not only
            [
                'level' => 'C1',
                'question' => 'Not only {a1} the deadline, but they also exceeded expectations.',
                'answers' => ['a1' => 'did they meet'],
                'options' => ['did they meet', 'they met', 'they did meet', 'met they'],
                'verb_hint' => 'inversion after not only',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Конструкція 'Not only... but also' для підкреслення двох фактів. Коли 'Not only' стоїть на початку речення для емфази, потрібна інверсія з допоміжним дієсловом. Це формальна літературна конструкція."],
                'explanations' => [
                    'did they meet' => "Після 'Not only' на початку речення потрібна інверсія: допоміжне дієслово (did) перед підметом (they), потім базова форма (meet). Це паралельно до конструкції з but also.",
                    'they met' => "Прямий порядок слів (підмет + дієслово) використовується в звичайних реченнях. Але коли 'Not only' стоїть на початку для емфази, потрібна інверсія.",
                    'they did meet' => "Конструкція 'they did meet' — емфатична форма для підкреслення, але вона не є інверсією. Після 'Not only' на початку потрібна саме інверсія (did they).",
                    'met they' => "Інверсія основного дієслова без допоміжного (met they) не використовується в сучасній англійській. Потрібна інверсія з допоміжним дієсловом: did they meet.",
                ],
            ],

            // 7. Complex Indirect Question - Conditional
            [
                'level' => 'C1',
                'question' => 'I wonder whether {a1} if they had known the risks.',
                'answers' => ['a1' => 'they would have agreed'],
                'options' => ['they would have agreed', 'would they have agreed', 'they had agreed would', 'would have they agreed'],
                'verb_hint' => 'statement order in conditional',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "Це непряме питання з Third Conditional (нереальна умова в минулому). 'If they had known' — умова, основне речення з 'would have + V3'. У непрямих питаннях навіть зі складними умовними конструкціями використовується прямий порядок слів."],
                'explanations' => [
                    'they would have agreed' => "У непрямих питаннях порядок слів: підмет (they) + would have + V3 (agreed). Third Conditional: would have + V3 у головному реченні, had + V3 у if-clause.",
                    'would they have agreed' => "Інверсія (would they) використовується у прямих питаннях: Would they have agreed? Але після 'I wonder whether...' потрібен прямий порядок.",
                    'they had agreed would' => "Would не може стояти в кінці. Структура Third Conditional: subject + would have + V3.",
                    'would have they agreed' => "Підмет не може стояти між have та V3. Правильний порядок: they + would have + agreed.",
                ],
            ],

            // 8. Negative Question - Should have
            [
                'level' => 'C1',
                'question' => '{a1} you have informed me earlier?',
                'answers' => ['a1' => "Shouldn't"],
                'options' => ["Shouldn't", "Hadn't", "Wouldn't", "Didn't"],
                'verb_hint' => 'negative modal criticism',
                'detail' => 'negative_questions',
                'hints' => ['a1' => "Це заперечне питання з Modal Perfect для вираження критики або докору щодо минулої дії. 'Have informed' (have + V3) вказує на Perfect конструкцію. Заперечне питання очікує згоди: 'Хіба ти не мав би...?'"],
                'explanations' => [
                    "Shouldn't" => "Shouldn't have + V3 виражає критику за минулу бездіяльність або неправильну дію. Заперечне питання 'Shouldn't you have...?' риторично підкреслює, що дію слід було виконати.",
                    "Hadn't" => "Hadn't — Past Perfect. 'Hadn't you informed?' питало б про послідовність подій у минулому, а не виражало б модальну критику.",
                    "Wouldn't" => "Wouldn't have + V3 виражає небажання або гіпотетичну ситуацію, а не критику чи обов'язок. Інше модальне значення.",
                    "Didn't" => "Didn't — Past Simple. 'Didn't you inform?' — питання про факт. Але 'have informed' вказує на Perfect, і контекст вимагає модальної критики (shouldn't).",
                ],
            ],

            // 9. Future Perfect Question
            [
                'level' => 'C1',
                'question' => 'Which proposals {a1} implemented by the end of the quarter?',
                'answers' => ['a1' => 'will have been'],
                'options' => ['will have been', 'will be', 'have been', 'will been'],
                'verb_hint' => 'future perfect passive',
                'detail' => 'subject_questions',
                'hints' => ['a1' => "Фраза 'by the end of the quarter' (до кінця кварталу) вказує на завершеність дії до певного моменту в майбутньому. Для таких ситуацій використовується Future Perfect. Слово 'implemented' (впроваджено) вказує на Passive Voice."],
                'explanations' => [
                    'will have been' => "Will have been + V3 — Future Perfect Passive. Описує дію, яка буде завершена до певного моменту в майбутньому. 'By the end of' — типовий маркер Future Perfect.",
                    'will be' => "Will be + V3 — Future Simple Passive. Описує дію в майбутньому, але не підкреслює завершеність до певного моменту, що вимагає 'by the end of'.",
                    'have been' => "Have been + V3 — Present Perfect Passive. Описує зв'язок минулого з теперішнім, але 'by the end of the quarter' вказує на майбутнє.",
                    'will been' => "Конструкція 'will been' граматично неможлива. Future Perfect потребує will + have + been, а не will + been.",
                ],
            ],

            // 10. Alternative Question - Complex
            [
                'level' => 'C1',
                'question' => 'Would you rather work independently {a1} collaborate with a team?',
                'answers' => ['a1' => 'or'],
                'options' => ['or', 'and', 'than', 'but'],
                'verb_hint' => 'preference between options',
                'detail' => 'alternative_questions',
                'hints' => ['a1' => "Конструкція 'would rather' виражає перевагу. Для альтернативних питань, що пропонують вибір між варіантами переваги, використовується спеціальний сполучник. Відповідь буде конкретним вибором, а не yes/no."],
                'explanations' => [
                    'or' => "Or — сполучник вибору для альтернативних питань. 'Would you rather A or B?' пропонує вибір між двома перевагами. Відповідь: 'I'd rather work independently' або 'I'd rather collaborate'.",
                    'and' => "And — сполучник об'єднання. 'Work independently and collaborate' означало б робити обидва одночасно, що не є вибором переваги.",
                    'than' => "Than використовується з prefer: 'I prefer A than B' (хоча 'prefer A to B' правильніше). Але з 'would rather' використовується or для питань та than для стверджень.",
                    'but' => "But — сполучник протиставлення. Він не використовується для пропозиції вибору в альтернативних питаннях.",
                ],
            ],

            // 11. Rhetorical Negative Question
            [
                'level' => 'C1',
                'question' => '{a1} we all benefit from better communication?',
                'answers' => ['a1' => "Wouldn't"],
                'options' => ["Wouldn't", "Won't", "Don't", "Aren't"],
                'verb_hint' => 'rhetorical conditional',
                'detail' => 'negative_questions',
                'hints' => ['a1' => "Це риторичне питання, яке не потребує відповіді, а скоріше виражає думку мовця. Риторичні питання часто використовують заперечну форму для припущення згоди. Умовне модальне дієслово робить питання м'якшим і ввічливішим."],
                'explanations' => [
                    "Wouldn't" => "Wouldn't — умовна форма, яка робить риторичне питання м'якшим та ввічливішим. 'Wouldn't we all benefit?' припускає згоду і звучить менш категорично.",
                    "Won't" => "Won't — Future Simple. 'Won't we all benefit?' також можливе, але звучить більш категорично та прямолінійно, ніж умовне wouldn't.",
                    "Don't" => "Don't — Present Simple. 'Don't we all benefit?' питає про теперішній факт. Менш елегантно для риторичного питання у формальному контексті.",
                    "Aren't" => "Aren't — форма to be. Але 'benefit' — дієслово дії, яке потребує допоміжного дієслова (do/does/will), а не to be.",
                ],
            ],

            // 12. Indirect Question - Continuous Perfect
            [
                'level' => 'C1',
                'question' => "I'd like to understand what {a1} all this time.",
                'answers' => ['a1' => 'you have been working on'],
                'options' => ['you have been working on', 'have you been working on', 'you been have working on', 'on you have been working'],
                'verb_hint' => 'statement order with phrasal verb',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "Це непряме питання з Present Perfect Continuous та фразовим дієсловом 'work on' (працювати над). У непрямих питаннях порядок слів прямий. Прийменник фразового дієслова зазвичай залишається в кінці питання."],
                'explanations' => [
                    'you have been working on' => "Прямий порядок слів: підмет (you) + have been + V-ing (working) + прийменник (on). Прийменник залишається в кінці, що типово для англійської.",
                    'have you been working on' => "Інверсія (have you) використовується у прямих питаннях: What have you been working on? Але після 'I'd like to understand what...' потрібен прямий порядок.",
                    'you been have working on' => "Have не може стояти після been. Правильна структура Present Perfect Continuous: have/has + been + V-ing.",
                    'on you have been working' => "Прийменник зазвичай не виноситься на початок у розмовній англійській. Природний порядок: ...what you have been working on.",
                ],
            ],
        ];
    }

    private function getC2Questions(): array
    {
        return [
            // 1. Cleft Sentence
            [
                'level' => 'C2',
                'question' => 'Was it the CEO {a1} made the decision?',
                'answers' => ['a1' => 'who'],
                'options' => ['who', 'that', 'which', 'whom'],
                'verb_hint' => 'cleft with person',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Це cleft sentence (розщеплене речення) — конструкція для підкреслення певного елемента речення. Структура: It is/was + emphasized element + relative pronoun + rest of sentence. Вибір відносного займенника залежить від того, чи підкреслений елемент — людина, річ чи абстрактне поняття."],
                'explanations' => [
                    'who' => "Who — відносний займенник для людей у ролі підмета. У cleft sentences для підкреслення людини (the CEO) who є стилістично кращим вибором, особливо у формальному контексті.",
                    'that' => "That — універсальний відносний займенник, який можна використовувати як для людей, так і для речей. У cleft sentences that граматично можливий, але для людей who вважається стилістично кращим.",
                    'which' => "Which — відносний займенник для речей та тварин. CEO — це людина, тому which граматично не підходить у цьому контексті.",
                    'whom' => "Whom — відносний займенник для людей у ролі об'єкта. Але у цьому реченні CEO є підметом дії 'made the decision', тому потрібен займенник для підмета.",
                ],
            ],

            // 2. Inverted Conditional (Had)
            [
                'level' => 'C2',
                'question' => '{a1} I known about the difficulties, I would have helped.',
                'answers' => ['a1' => 'Had'],
                'options' => ['Had', 'If had', 'Would', 'If I had'],
                'verb_hint' => 'inverted conditional without if',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Це Third Conditional (нереальна умова в минулому). У формальному стилі замість 'if' можна використовувати інверсію — допоміжне дієслово виноситься перед підметом. Це надає реченню більш літературного та формального звучання."],
                'explanations' => [
                    'Had' => "Інверсія у Third Conditional: Had + subject + V3 замість 'If + subject + had + V3'. Це формальна літературна конструкція, яка не потребує 'if'.",
                    'If had' => "Конструкція 'If had I' граматично неможлива. При використанні інверсії 'if' опускається повністю.",
                    'Would' => "Would використовується у головній частині Third Conditional (would have helped), а не в умовній частині. Умовна частина потребує had.",
                    'If I had' => "'If I had known' — стандартна форма Third Conditional. Граматично правильна, але питання вимагає інверсії як альтернативної формальної структури.",
                ],
            ],

            // 3. Subjunctive in Indirect Question
            [
                'level' => 'C2',
                'question' => 'The board insists that we clarify whether {a1} present.',
                'answers' => ['a1' => 'he be'],
                'options' => ['he be', 'he is', 'is he', 'he will be'],
                'verb_hint' => 'subjunctive after insist',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "Дієслово 'insist' (наполягати) у формальній англійській мові потребує subjunctive mood (умовного способу) у підрядному реченні. Subjunctive використовує базову форму дієслова (be, go, have) без узгодження з підметом."],
                'explanations' => [
                    'he be' => "Subjunctive mood після 'insist': базова форма дієслова 'be' для всіх підметів (he be, she be, they be). Це формальна британська та американська норма для вимог та наполягань.",
                    'he is' => "He is — indicative mood (дійсний спосіб). У розмовній британській англійській можливо, але формальна норма після 'insist' вимагає subjunctive.",
                    'is he' => "Інверсія (is he) не використовується у непрямих питаннях. Після 'whether' потрібен прямий порядок слів.",
                    'he will be' => "Will be виражає майбутній час, але після 'insist' потрібен subjunctive, який не використовує will.",
                ],
            ],

            // 4. Inversion - Scarcely
            [
                'level' => 'C2',
                'question' => 'Scarcely {a1} the presentation when questions started.',
                'answers' => ['a1' => 'had she finished'],
                'options' => ['had she finished', 'she had finished', 'she finished', 'did she finish'],
                'verb_hint' => 'inversion after scarcely',
                'detail' => 'yes_no',
                'hints' => ['a1' => "'Scarcely...when' — конструкція для вираження послідовності подій: 'ледь...як'. Коли scarcely/hardly стоїть на початку для емфази, потрібна інверсія. Часова конструкція: Past Perfect для першої події, Past Simple для другої."],
                'explanations' => [
                    'had she finished' => "Після scarcely/hardly на початку речення потрібна інверсія з Past Perfect: had + subject + V3. Past Perfect показує, що перша дія ледь завершилася, коли почалася друга.",
                    'she had finished' => "Прямий порядок слів (she had finished) використовується, коли scarcely не на початку: 'She had scarcely finished when...' Але тут scarcely на початку, тому потрібна інверсія.",
                    'she finished' => "Past Simple без інверсії. Порушує два правила: 1) потрібна інверсія; 2) потрібен Past Perfect для послідовності подій.",
                    'did she finish' => "Інверсія з Past Simple (did she finish) не підходить. Конструкція scarcely...when потребує Past Perfect для вираження 'ледь завершив, як...'",
                ],
            ],

            // 5. Tag Question - Must have (deduction)
            [
                'level' => 'C2',
                'question' => 'He must have been lying, {a1}?',
                'answers' => ['a1' => "mustn't he"],
                'options' => ["mustn't he", 'must he', "hasn't he", "wasn't he"],
                'verb_hint' => 'tag from must have',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "'Must have + V3' виражає впевнену дедукцію про минуле: 'напевно/мабуть'. Це ствердне речення з модальним дієсловом. Правило tag questions: ствердне речення потребує заперечного tag. Tag формується з модального дієслова головного речення."],
                'explanations' => [
                    "mustn't he" => "Після ствердного 'must have' потрібен заперечний tag з тим самим модальним дієсловом: mustn't + pronoun. Це логічний та граматичний tag для модальної дедукції.",
                    'must he' => "Ствердний tag після ствердного речення порушує правило протилежної полярності в tag questions.",
                    "hasn't he" => "Hasn't — tag для Present Perfect (He has been lying, hasn't he?). Але 'must have' — Modal Perfect з іншою структурою та значенням (дедукція).",
                    "wasn't he" => "Wasn't — tag для Past Continuous (He was lying, wasn't he?). Але 'must have been lying' — Modal Perfect Continuous, і tag має відповідати must.",
                ],
            ],

            // 6. Inversion - Little
            [
                'level' => 'C2',
                'question' => 'Little {a1} that this would change everything.',
                'answers' => ['a1' => 'did we know'],
                'options' => ['did we know', 'we knew', 'we did know', 'knew we'],
                'verb_hint' => 'inversion after little',
                'detail' => 'yes_no',
                'hints' => ['a1' => "'Little' на початку речення у значенні 'мало/ледь' є негативним за значенням і потребує інверсії для емфази. Це літературна конструкція для вираження: 'Ми й не підозрювали, що...' Інверсія формується з допоміжним дієсловом."],
                'explanations' => [
                    'did we know' => "Після 'little' на початку речення потрібна інверсія: did + subject + base verb. 'Little did we know' — усталена ідіоматична конструкція для 'ми й не підозрювали'.",
                    'we knew' => "Прямий порядок слів (we knew) використовується без інверсії: 'We knew little that...' Але коли 'little' на початку для емфази, потрібна інверсія.",
                    'we did know' => "Конструкція 'we did know' — емфатична форма для підкреслення факту знання. Але це не інверсія, яку вимагає 'little' на початку.",
                    'knew we' => "Інверсія основного дієслова без допоміжного (knew we) — архаїчна форма, яка не використовується в сучасній англійській.",
                ],
            ],

            // 7. Future Perfect Continuous - Indirect
            [
                'level' => 'C2',
                'question' => 'By next month, can you estimate how long {a1} on this?',
                'answers' => ['a1' => 'we will have been working'],
                'options' => ['we will have been working', 'will we have been working', 'we have been working will', 'will have we been working'],
                'verb_hint' => 'statement order with future perfect continuous',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "'By next month' вказує на момент у майбутньому. Питання про тривалість дії до цього моменту потребує Future Perfect Continuous: will have been + V-ing. У непрямих питаннях порядок слів прямий."],
                'explanations' => [
                    'we will have been working' => "У непрямих питаннях порядок слів: subject + will have been + V-ing. Future Perfect Continuous описує тривалість дії до певного моменту в майбутньому.",
                    'will we have been working' => "Інверсія (will we) використовується у прямих питаннях: How long will we have been working? Але після 'can you estimate how long...' потрібен прямий порядок.",
                    'we have been working will' => "Will не може стояти в кінці речення. Правильна структура: will + have been + V-ing.",
                    'will have we been working' => "Підмет не може стояти між have та been. Правильний порядок: we + will have been + working.",
                ],
            ],

            // 8. Emphatic Cleft with Wh-word
            [
                'level' => 'C2',
                'question' => 'What it was {a1} impressed me most was their dedication.',
                'answers' => ['a1' => 'that'],
                'options' => ['that', 'which', 'what', 'who'],
                'verb_hint' => 'cleft connector',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Це pseudo-cleft sentence (псевдо-розщеплене речення) — складна емфатична конструкція. Структура: 'What it was + connector + clause'. Це поєднання wh-cleft та it-cleft для подвійного підкреслення."],
                'explanations' => [
                    'that' => "That — стандартний сполучник у cleft sentences після 'it was'. Навіть у складних pseudo-cleft конструкціях 'that' з'єднує підкреслений елемент із рештою речення.",
                    'which' => "Which — відносний займенник для речей. У цій конструкції 'that' є сполучником, а не відносним займенником, тому which не підходить.",
                    'what' => "What вже використано на початку речення як частина wh-cleft ('What it was...'). Повторення what було б надлишковим та граматично неправильним.",
                    'who' => "Who — відносний займенник для людей. Але підкреслений елемент тут — абстрактне поняття ('їхня відданість'), а не людина.",
                ],
            ],

            // 9. Inverted Conditional - Should
            [
                'level' => 'C2',
                'question' => '{a1} any issues arise, please contact me.',
                'answers' => ['a1' => 'Should'],
                'options' => ['Should', 'If should', 'Would', 'If'],
                'verb_hint' => 'inverted conditional first type',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Це First Conditional (реальна умова) у формальному стилі. Замість 'If + present simple' можна використовувати інверсію з 'should' для вираження менш ймовірної, але можливої події. Це надає реченню формального та ввічливого тону."],
                'explanations' => [
                    'Should' => "Інверсія з should у First Conditional: Should + subject + base verb. Виражає менш ймовірну умову у формальному стилі. Еквівалентно 'If any issues should arise' або 'If any issues arise'.",
                    'If should' => "Конструкція 'If should' граматично неможлива. При інверсії 'if' опускається повністю: Should any issues arise (не: If should any issues arise).",
                    'Would' => "Would використовується у Second Conditional (нереальна умова теперішнього). Але контекст (please contact me) вказує на реальну можливість, тому потрібен First Conditional.",
                    'If' => "'If any issues arise' — стандартна форма First Conditional. Граматично правильна, але питання вимагає інверсії як альтернативної формальної структури.",
                ],
            ],

            // 10. Past Continuous Passive - Indirect
            [
                'level' => 'C2',
                'question' => 'The investigators need to determine what {a1} at the time.',
                'answers' => ['a1' => 'was being done'],
                'options' => ['was being done', 'was done', 'has been done', 'had been done'],
                'verb_hint' => 'past continuous passive',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "'At the time' вказує на конкретний момент у минулому. Питання про дію, яка тривала (була в процесі) у цей момент, потребує Past Continuous. Оскільки питання про дію ('що робилося'), а не про виконавця, потрібен Passive Voice."],
                'explanations' => [
                    'was being done' => "Was being + V3 — Past Continuous Passive. Описує дію, яка тривала (була в процесі) у певний момент минулого. 'At the time' підкреслює тривалість дії.",
                    'was done' => "Was done — Past Simple Passive. Описує завершену дію в минулому, але не підкреслює тривалість. 'At the time' вимагає Continuous для вираження процесу.",
                    'has been done' => "Has been done — Present Perfect Passive. Описує зв'язок минулої дії з теперішнім. Але 'at the time' вказує на конкретний момент у минулому.",
                    'had been done' => "Had been done — Past Perfect Passive. Описує дію, яка відбулася раніше за інший момент у минулому. Але 'at the time' вказує на тривалість, не на послідовність.",
                ],
            ],

            // 11. Complex Double Modal - Indirect
            [
                'level' => 'C2',
                'question' => 'One might wonder whether {a1} sooner had we known.',
                'answers' => ['a1' => 'we might not have acted'],
                'options' => ['we might not have acted', 'might we not have acted', 'we acted might not have', 'not have acted we might'],
                'verb_hint' => 'statement order with complex modal',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "Це непряме питання зі складною модальною конструкцією 'might not have + V3' (Modal Perfect Negative) та інверсією в умовній частині ('had we known'). У непрямих питаннях основна частина зберігає прямий порядок слів."],
                'explanations' => [
                    'we might not have acted' => "У непрямих питаннях порядок слів: subject + modal + not + have + V3. Навіть зі складними заперечними модальними перфектами після 'whether' потрібен прямий порядок.",
                    'might we not have acted' => "Інверсія (might we) використовується у прямих питаннях. Але після 'One might wonder whether...' потрібен прямий порядок слів.",
                    'we acted might not have' => "Might та have не можуть бути відокремлені від acted. Правильна структура: subject + might not have + V3.",
                    'not have acted we might' => "Такий порядок слів не відповідає жодній англійській граматичній структурі. Заперечення стоїть після модального дієслова.",
                ],
            ],

            // 12. Alternative Question - Sophisticated
            [
                'level' => 'C2',
                'question' => 'Should we proceed with the original plan {a1} reconsider our approach?',
                'answers' => ['a1' => 'or'],
                'options' => ['or', 'and', 'nor', 'but'],
                'verb_hint' => 'choice in formal context',
                'detail' => 'alternative_questions',
                'hints' => ['a1' => "Це альтернативне питання (Alternative Question) у формальному діловому контексті. Незважаючи на складність лексики та формальний стиль, структура альтернативного питання залишається незмінною: пропозиція вибору між двома варіантами дій."],
                'explanations' => [
                    'or' => "Or — сполучник вибору, який використовується у всіх альтернативних питаннях, незалежно від рівня формальності. Відповідь передбачає вибір одного з варіантів.",
                    'and' => "And — сполучник об'єднання. 'Proceed and reconsider' означало б робити обидві дії, що логічно суперечливо (продовжувати план та переглядати його одночасно).",
                    'nor' => "Nor — сполучник для заперечних конструкцій (neither...nor). Він не використовується в альтернативних питаннях, які пропонують позитивний вибір.",
                    'but' => "But — сполучник протиставлення. Він не використовується для пропозиції вибору в питаннях типу 'X або Y?'",
                ],
            ],
        ];
    }

    // SET 2: Yes/No Questions (~31 questions)
    private function getSet2YesNoQuestions(): array
    {
        return [
            // A1 Level - Basic Yes/No
            [
                'level' => 'A1',
                'question' => '{a1} she a teacher?',
                'answers' => ['a1' => 'Is'],
                'options' => ['Is', 'Are', 'Do', 'Does'],
                'verb_hint' => 'to be form',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Це загальне питання (Yes/No question) про професію. Коли питаємо 'хто є ким' (ідентичність, професія, стан), використовуємо дієслово 'to be'. Форма to be залежить від підмета: she — третя особа однини."],
                'explanations' => [
                    'Is' => "Is — форма дієслова 'to be' для третьої особи однини (he, she, it). Питання про професію формується: Is + she + noun (a teacher).",
                    'Are' => "Are — форма дієслова 'to be' для you, we, they. Підмет 'she' — третя особа однини, тому ця форма не узгоджується.",
                    'Do' => "Do — допоміжне дієслово для питань з дієсловами дії. Але 'a teacher' — іменник (професія), а не дієслово. Для ідентичності використовується to be.",
                    'Does' => "Does — допоміжне дієслово Present Simple для питань з дієсловами дії. Але тут питання про стан/ідентичність (бути вчителем), яке потребує to be.",
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'A1',
                'question' => '{a1} they students?',
                'answers' => ['a1' => 'Are'],
                'options' => ['Are', 'Is', 'Do', 'Does'],
                'verb_hint' => 'plural to be',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Це загальне питання про ідентичність групи людей. Підмет 'they' — множина. Коли питаємо про те, ким є люди (статус, професія), використовуємо дієслово 'to be' у відповідній формі."],
                'explanations' => [
                    'Are' => "Are — форма дієслова 'to be' для множини (we, you, they). Підмет 'they' потребує саме цієї форми. Питання: Are + they + noun.",
                    'Is' => "Is — форма дієслова 'to be' для однини (he, she, it). Підмет 'they' — множина, тому is граматично не узгоджується.",
                    'Do' => "Do — допоміжне дієслово для питань з дієсловами дії. Але 'students' — іменник, а не дієслово. Для питання про статус потрібне to be.",
                    'Does' => "Does — допоміжне дієслово для третьої особи однини (he, she, it) з дієсловами дії. По-перше, 'they' — множина. По-друге, тут питання про стан, не дію.",
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'A1',
                'question' => '{a1} he like football?',
                'answers' => ['a1' => 'Does'],
                'options' => ['Does', 'Do', 'Is', 'Are'],
                'verb_hint' => 'auxiliary for he/she/it',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Це загальне питання про вподобання. Дієслово 'like' (подобатися) — дієслово дії. Для формування питань з дієсловами дії у Present Simple потрібне допоміжне дієслово. Підмет 'he' — третя особа однини."],
                'explanations' => [
                    'Does' => "Does — допоміжне дієслово Present Simple для третьої особи однини (he, she, it). Структура: Does + he + base verb (like). Після does дієслово без -s.",
                    'Do' => "Do — допоміжне дієслово Present Simple для I, you, we, they. Підмет 'he' — третя особа однини, яка потребує does.",
                    'Is' => "Is — форма дієслова 'to be'. Але 'like' — дієслово дії (вподобання), яке потребує do/does для питань, а не to be.",
                    'Are' => "Are — форма дієслова 'to be' для множини. По-перше, 'he' — однина. По-друге, 'like' — дієслово дії, не стан.",
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],

            // A2 Level - More complex
            [
                'level' => 'A2',
                'question' => '{a1} you play tennis every week?',
                'answers' => ['a1' => 'Do'],
                'options' => ['Do', 'Does', 'Are', 'Did'],
                'verb_hint' => 'present simple auxiliary',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Фраза 'every week' (кожного тижня) — маркер регулярної дії, що вказує на Present Simple. Дієслово 'play' — дієслово дії. Для питань з дієсловами дії у Present Simple потрібне допоміжне дієслово. Підмет 'you' визначає його форму."],
                'explanations' => [
                    'Do' => "Do — допоміжне дієслово Present Simple для I, you, we, they. Маркери частоти (every week, always, usually) вказують на Present Simple. Структура: Do + you + base verb.",
                    'Does' => "Does — допоміжне дієслово Present Simple для третьої особи однини (he, she, it). Підмет 'you' не узгоджується з does.",
                    'Are' => "Are — форма дієслова 'to be'. Конструкція 'Are you playing?' — Present Continuous для дії зараз. Але 'every week' вказує на регулярну дію (Present Simple).",
                    'Did' => "Did — допоміжне дієслово Past Simple. Слова 'every week' вказують на теперішню звичку, а не минулу подію.",
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'A2',
                'question' => '{a1} it rain yesterday?',
                'answers' => ['a1' => 'Did'],
                'options' => ['Did', 'Does', 'Was', 'Is'],
                'verb_hint' => 'past simple auxiliary',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Слово 'yesterday' (вчора) чітко вказує на минулий час (Past Simple). Дієслово 'rain' — дієслово дії, що описує погодне явище. Для питань з дієсловами дії у Past Simple потрібне допоміжне дієслово."],
                'explanations' => [
                    'Did' => "Did — допоміжне дієслово Past Simple для всіх підметів. 'Yesterday' — маркер Past Simple. Після did дієслово стоїть у базовій формі: Did + it + rain.",
                    'Does' => "Does — допоміжне дієслово Present Simple. Але 'yesterday' вказує на минулий час, тому теперішній час не підходить.",
                    'Was' => "Was — форма to be у минулому часі. 'Was it raining?' — Past Continuous. Але з 'rain' як дієсловом дії для простого факту використовується did.",
                    'Is' => "Is — форма to be у теперішньому часі. По-перше, 'yesterday' вказує на минуле. По-друге, 'rain' — дієслово дії, яке потребує do/did.",
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'A2',
                'question' => '{a1} she working now?',
                'answers' => ['a1' => 'Is'],
                'options' => ['Is', 'Does', 'Are', 'Do'],
                'verb_hint' => 'continuous tense',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Слово 'now' (зараз) вказує на дію, яка відбувається в момент мовлення. Форма 'working' (-ing) підтверджує Continuous час. Present Continuous формується з to be + V-ing. Підмет 'she' визначає форму to be."],
                'explanations' => [
                    'Is' => "Is — форма to be для третьої особи однини (he, she, it). Present Continuous для питань: Is + she + V-ing. 'Now' підкреслює дію в процесі.",
                    'Does' => "Does — допоміжне дієслово Present Simple. Але 'now' та форма 'working' вказують на Continuous, який потребує to be, а не does.",
                    'Are' => "Are — форма to be для you, we, they. Підмет 'she' — третя особа однини, яка потребує is.",
                    'Do' => "Do — допоміжне дієслово Present Simple. Present Continuous (now + working) потребує to be, а не do.",
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],

            // B1 Level
            [
                'level' => 'B1',
                'question' => '{a1} they visited Paris before?',
                'answers' => ['a1' => 'Have'],
                'options' => ['Have', 'Has', 'Did', 'Were'],
                'verb_hint' => 'experience question',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Слово 'before' (раніше, коли-небудь) вказує на досвід протягом життя без конкретного часу. Для питань про життєвий досвід використовується Present Perfect. Форма 'visited' (V3) підтверджує цей час."],
                'explanations' => [
                    'Have' => "Have — допоміжне дієслово Present Perfect для I, you, we, they. Питання про досвід: Have + they + V3 (visited). 'Before' типово з Perfect часами.",
                    'Has' => "Has — допоміжне дієслово Present Perfect для третьої особи однини. Підмет 'they' — множина, яка потребує have.",
                    'Did' => "Did — Past Simple для конкретного часу в минулому. 'Before' без конкретної дати вказує на досвід (Present Perfect), а не факт у певний час.",
                    'Were' => "Were — форма to be у минулому часі. Конструкція 'Were they visited?' граматично неможлива з дієсловом 'visit'.",
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'B1',
                'question' => '{a1} you going to travel this summer?',
                'answers' => ['a1' => 'Are'],
                'options' => ['Are', 'Do', 'Will', 'Have'],
                'verb_hint' => 'going to future',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Конструкція 'be going to + verb' виражає заплановані майбутні дії або наміри. 'Going to' — фіксована фраза, яка потребує дієслова to be перед собою. Підмет 'you' визначає форму to be."],
                'explanations' => [
                    'Are' => "Are — форма to be для you (а також we, they). Конструкція 'be going to': Are + you + going to + base verb. Виражає плани на майбутнє.",
                    'Do' => "Do — допоміжне дієслово Present Simple. Конструкція 'going to' не поєднується з do. 'Do you go?' — питання про звичку, а не план.",
                    'Will' => "Will — допоміжне дієслово Future Simple. 'Will you travel?' — інша конструкція майбутнього часу, але 'going to' потребує to be.",
                    'Have' => "Have — допоміжне дієслово Perfect часів. Конструкція 'going to' потребує to be (am, is, are), а не have.",
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'B1',
                'question' => '{a1} it been raining all day?',
                'answers' => ['a1' => 'Has'],
                'options' => ['Has', 'Have', 'Is', 'Was'],
                'verb_hint' => 'perfect continuous',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Фраза 'all day' (весь день) вказує на тривалість дії до теперішнього моменту. Для опису тривалої дії, яка почалася в минулому і продовжується досі, використовується Present Perfect Continuous: have/has + been + V-ing."],
                'explanations' => [
                    'Has' => "Has — допоміжне дієслово для Present Perfect (Continuous) з третьою особою однини (he, she, it). 'It' (безособовий підмет для погоди) потребує has.",
                    'Have' => "Have — допоміжне дієслово Present Perfect (Continuous) для I, you, we, they. Підмет 'it' — третя особа однини, яка потребує has.",
                    'Is' => "Is — форма to be для Present Continuous. 'Is it raining?' — питання про дію зараз. Але 'all day' підкреслює тривалість, що потребує Perfect Continuous.",
                    'Was' => "Was — форма to be у минулому часі для Past Continuous. Але 'all day' з результатом у теперішньому потребує Present Perfect Continuous.",
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],

            // B2 Level
            [
                'level' => 'B2',
                'question' => '{a1} she been informed about the changes?',
                'answers' => ['a1' => 'Has'],
                'options' => ['Has', 'Have', 'Was', 'Is'],
                'verb_hint' => 'perfect passive',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Конструкція 'been informed' — Present Perfect Passive. 'Informed' — дієприкметник, а 'been' вказує на Perfect та Passive. Present Perfect Passive формується: have/has + been + V3. Підмет 'she' визначає форму."],
                'explanations' => [
                    'Has' => "Has — допоміжне дієслово Present Perfect для третьої особи однини (he, she, it). Present Perfect Passive: Has + she + been + V3. Питання про результат інформування.",
                    'Have' => "Have — допоміжне дієслово Present Perfect для множини. Підмет 'she' — однина, яка потребує has.",
                    'Was' => "Was + informed — Past Simple Passive. Описує минулу подію. Але 'Has been informed' підкреслює зв'язок з теперішнім (чи вона зараз знає?).",
                    'Is' => "Is + informed — Present Simple Passive для загальних фактів. Але 'been informed' вказує на Present Perfect, який описує завершену дію з результатом.",
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'B2',
                'question' => '{a1} the reports been reviewed yet?',
                'answers' => ['a1' => 'Have'],
                'options' => ['Have', 'Has', 'Were', 'Are'],
                'verb_hint' => 'plural perfect passive',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Слово 'yet' (ще) типово вживається з Present Perfect для питань про завершеність дії. 'Been reviewed' — Perfect Passive конструкція. Підмет 'the reports' — множина, що визначає форму допоміжного дієслова."],
                'explanations' => [
                    'Have' => "Have — допоміжне дієслово Present Perfect для множини. 'Reports' — множина, тому потрібне have. 'Yet' підкреслює очікування завершення дії.",
                    'Has' => "Has — допоміжне дієслово Present Perfect для однини. 'Reports' — множина, тому has не узгоджується.",
                    'Were' => "Were + reviewed — Past Simple Passive. Але 'yet' вказує на зв'язок з теперішнім (чи готові звіти зараз?), що потребує Present Perfect.",
                    'Are' => "Are + reviewed — Present Simple Passive для регулярних дій. Але 'yet' та 'been' вказують на Present Perfect, а не Present Simple.",
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'B2',
                'question' => '{a1} you been waiting long?',
                'answers' => ['a1' => 'Have'],
                'options' => ['Have', 'Has', 'Are', 'Were'],
                'verb_hint' => 'duration until now',
                'detail' => 'yes_no',
                'hints' => ['a1' => "'Long' (довго) питає про тривалість очікування до теперішнього моменту. Форма 'been waiting' вказує на Present Perfect Continuous — час для опису тривалої дії, що почалася в минулому і продовжується досі."],
                'explanations' => [
                    'Have' => "Have — допоміжне дієслово Present Perfect (Continuous) для you (а також I, we, they). Питання про тривалість: Have + you + been + V-ing.",
                    'Has' => "Has — допоміжне дієслово Present Perfect (Continuous) для третьої особи однини. Підмет 'you' потребує have.",
                    'Are' => "Are — форма to be для Present Continuous. 'Are you waiting?' — питання про дію зараз. Але 'long' питає про тривалість, що потребує Perfect Continuous.",
                    'Were' => "Were — форма to be для Past Continuous. 'Were you waiting?' стосується минулого. Але 'long' без минулого контексту вказує на зв'язок з теперішнім.",
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],

            // C1 Level
            [
                'level' => 'C1',
                'question' => '{a1} the project been completed by next week?',
                'answers' => ['a1' => 'Will'],
                'options' => ['Will', 'Has', 'Have', 'Is'],
                'verb_hint' => 'future perfect passive',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'By next week вказує на Future Perfect Passive.'],
                'explanations' => [
                    'Will' => '✅ Правильно! Future Perfect Passive. Приклад: Will the project have been completed by next week?',
                    'Has' => '❌ Неправильно. Has для Present Perfect. Правильна відповідь: Will the project have been completed by next week?',
                    'Have' => '❌ Неправильно. Have для Present Perfect. Правильна відповідь: Will the project have been completed by next week?',
                    'Is' => '❌ Неправильно. Is для Present. Правильна відповідь: Will the project have been completed by next week?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'C1',
                'question' => '{a1} we been overlooking something important?',
                'answers' => ['a1' => 'Have'],
                'options' => ['Have', 'Are', 'Were', 'Had'],
                'verb_hint' => 'continuous perfect question',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'Питання про можливу тривалу дію до теперішнього моменту.'],
                'explanations' => [
                    'Have' => '✅ Правильно! Present Perfect Continuous з we. Приклад: Have we been overlooking something important?',
                    'Are' => '❌ Неправильно. Are для Present Continuous. Правильна відповідь: Have we been overlooking something important?',
                    'Were' => '❌ Неправильно. Were для минулого. Правильна відповідь: Have we been overlooking something important?',
                    'Had' => '❌ Неправильно. Had для Past Perfect. Правильна відповідь: Have we been overlooking something important?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'C1',
                'question' => 'Could the data {a1} analyzed more thoroughly?',
                'answers' => ['a1' => 'have been'],
                'options' => ['have been', 'be', 'been', 'has been'],
                'verb_hint' => 'modal perfect passive',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'Could + perfect passive для критики минулого.'],
                'explanations' => [
                    'have been' => '✅ Правильно! Could have been для можливості у минулому. Приклад: Could the data have been analyzed more thoroughly?',
                    'be' => '❌ Неправильно. Be для простої форми. Правильна відповідь: Could the data have been analyzed more thoroughly?',
                    'been' => '❌ Неправильно. Потрібно have been. Правильна відповідь: Could the data have been analyzed more thoroughly?',
                    'has been' => '❌ Неправильно. Після could використовується have. Правильна відповідь: Could the data have been analyzed more thoroughly?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],

            // C2 Level
            [
                'level' => 'C2',
                'question' => 'Might the results {a1} influenced by external factors?',
                'answers' => ['a1' => 'have been'],
                'options' => ['have been', 'be', 'been', 'has been'],
                'verb_hint' => 'modal speculation passive',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'Might have been для здогадки про минуле у пасиві.'],
                'explanations' => [
                    'have been' => '✅ Правильно! Might have been для здогадки про минуле. Приклад: Might the results have been influenced by external factors?',
                    'be' => '❌ Неправильно. Be для теперішнього/майбутнього. Правильна відповідь: Might the results have been influenced by external factors?',
                    'been' => '❌ Неправильно. Потрібно have been. Правильна відповідь: Might the results have been influenced by external factors?',
                    'has been' => '❌ Неправильно. Після might використовується have. Правильна відповідь: Might the results have been influenced by external factors?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'C2',
                'question' => 'Ought the committee {a1} consulted earlier?',
                'answers' => ['a1' => 'to have been'],
                'options' => ['to have been', 'have been', 'to be', 'been'],
                'verb_hint' => 'ought to perfect passive',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'Ought to + perfect passive для критики минулого (формально).'],
                'explanations' => [
                    'to have been' => '✅ Правильно! Ought to have been для критики минулого. Приклад: Ought the committee to have been consulted earlier?',
                    'have been' => '❌ Неправильно. Після ought потрібно to. Правильна відповідь: Ought the committee to have been consulted earlier?',
                    'to be' => '❌ Неправильно. To be для теперішнього/майбутнього. Правильна відповідь: Ought the committee to have been consulted earlier?',
                    'been' => '❌ Неправильно. Потрібно to have been. Правильна відповідь: Ought the committee to have been consulted earlier?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the implications been fully considered?',
                'answers' => ['a1' => 'Have'],
                'options' => ['Have', 'Has', 'Were', 'Are'],
                'verb_hint' => 'plural perfect passive',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'Питання про завершеність дії до теперішнього з множиною.'],
                'explanations' => [
                    'Have' => '✅ Правильно! Present Perfect Passive з множиною. Приклад: Have the implications been fully considered?',
                    'Has' => '❌ Неправильно. Has для однини. Правильна відповідь: Have the implications been fully considered?',
                    'Were' => '❌ Неправильно. Were для Past Simple. Правильна відповідь: Have the implications been fully considered?',
                    'Are' => '❌ Неправильно. Are для Present Simple. Правильна відповідь: Have the implications been fully considered?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],

            // Additional questions for better distribution
            [
                'level' => 'A1',
                'question' => '{a1} this your book?',
                'answers' => ['a1' => 'Is'],
                'options' => ['Is', 'Are', 'Does', 'Do'],
                'verb_hint' => 'singular to be',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'З this (однина) використовуємо is.'],
                'explanations' => [
                    'Is' => '✅ Правильно! З this використовуємо is. Приклад: Is this your book?',
                    'Are' => '❌ Неправильно. Are для множини. Правильна відповідь: Is this your book?',
                    'Does' => '❌ Неправильно. Does для дій. Правильна відповідь: Is this your book?',
                    'Do' => '❌ Неправильно. Do не використовується з to be. Правильна відповідь: Is this your book?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'A2',
                'question' => '{a1} you speak French?',
                'answers' => ['a1' => 'Do'],
                'options' => ['Do', 'Does', 'Are', 'Can'],
                'verb_hint' => 'present ability',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'Питання про здатність у Present Simple з you.'],
                'explanations' => [
                    'Do' => '✅ Правильно! Present Simple з you. Приклад: Do you speak French?',
                    'Does' => '❌ Неправильно. Does тільки для he/she/it. Правильна відповідь: Do you speak French?',
                    'Are' => '❌ Неправильно. Are для continuous або to be. Правильна відповідь: Do you speak French?',
                    'Can' => '❌ Неправильно. Can можливий, але питання про факт. Правильна відповідь: Do you speak French?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'B1',
                'question' => '{a1} you been to Italy?',
                'answers' => ['a1' => 'Have'],
                'options' => ['Have', 'Has', 'Did', 'Were'],
                'verb_hint' => 'life experience',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'Питання про життєвий досвід у Present Perfect.'],
                'explanations' => [
                    'Have' => '✅ Правильно! Present Perfect для досвіду з you. Приклад: Have you been to Italy?',
                    'Has' => '❌ Неправильно. Has для he/she/it. Правильна відповідь: Have you been to Italy?',
                    'Did' => '❌ Неправильно. Did для конкретного часу. Правильна відповідь: Have you been to Italy?',
                    'Were' => '❌ Неправильно. Were не використовується з been. Правильна відповідь: Have you been to Italy?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'B2',
                'question' => '{a1} you been working on this project?',
                'answers' => ['a1' => 'Have'],
                'options' => ['Have', 'Has', 'Are', 'Were'],
                'verb_hint' => 'continuous until now',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'Питання про тривалість дії до теперішнього моменту.'],
                'explanations' => [
                    'Have' => '✅ Правильно! Present Perfect Continuous з you. Приклад: Have you been working on this project?',
                    'Has' => '❌ Неправильно. Has для he/she/it. Правильна відповідь: Have you been working on this project?',
                    'Are' => '❌ Неправильно. Are для Present Continuous. Правильна відповідь: Have you been working on this project?',
                    'Were' => '❌ Неправильно. Were для минулого. Правильна відповідь: Have you been working on this project?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'C1',
                'question' => '{a1} the documents been filed correctly?',
                'answers' => ['a1' => 'Have'],
                'options' => ['Have', 'Has', 'Were', 'Are'],
                'verb_hint' => 'plural perfect passive',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'Present Perfect Passive з множинним підметом.'],
                'explanations' => [
                    'Have' => '✅ Правильно! Present Perfect Passive з множиною. Приклад: Have the documents been filed correctly?',
                    'Has' => '❌ Неправильно. Has для однини. Правильна відповідь: Have the documents been filed correctly?',
                    'Were' => '❌ Неправильно. Were для Past Simple. Правильна відповідь: Have the documents been filed correctly?',
                    'Are' => '❌ Неправильно. Are для Present Simple. Правильна відповідь: Have the documents been filed correctly?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'A1',
                'question' => '{a1} you tired?',
                'answers' => ['a1' => 'Are'],
                'options' => ['Are', 'Is', 'Do', 'Does'],
                'verb_hint' => 'state with you',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'Питання про стан з you використовує are.'],
                'explanations' => [
                    'Are' => '✅ Правильно! З you використовуємо are. Приклад: Are you tired?',
                    'Is' => '❌ Неправильно. Is для he/she/it. Правильна відповідь: Are you tired?',
                    'Do' => '❌ Неправильно. Do для дій, але tired — стан. Правильна відповідь: Are you tired?',
                    'Does' => '❌ Неправильно. Does не використовується з you. Правильна відповідь: Are you tired?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'A2',
                'question' => '{a1} he come to the party last night?',
                'answers' => ['a1' => 'Did'],
                'options' => ['Did', 'Does', 'Was', 'Has'],
                'verb_hint' => 'past event',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'Last night вказує на Past Simple.'],
                'explanations' => [
                    'Did' => '✅ Правильно! Past Simple з did. Приклад: Did he come to the party last night?',
                    'Does' => '❌ Неправильно. Does для теперішнього часу. Правильна відповідь: Did he come to the party last night?',
                    'Was' => '❌ Неправильно. Was для стану, але come — дія. Правильна відповідь: Did he come to the party last night?',
                    'Has' => '❌ Неправильно. Has для Present Perfect. Правильна відповідь: Did he come to the party last night?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
            [
                'level' => 'B1',
                'question' => '{a1} it been snowing all morning?',
                'answers' => ['a1' => 'Has'],
                'options' => ['Has', 'Have', 'Was', 'Is'],
                'verb_hint' => 'perfect continuous weather',
                'detail' => 'yes_no',
                'hints' => ['a1' => 'All morning вказує на тривалість до теперішнього моменту.'],
                'explanations' => [
                    'Has' => '✅ Правильно! Present Perfect Continuous з it. Приклад: Has it been snowing all morning?',
                    'Have' => '❌ Неправильно. Have для I/you/we/they. Правильна відповідь: Has it been snowing all morning?',
                    'Was' => '❌ Неправильно. Was для Past Continuous. Правильна відповідь: Has it been snowing all morning?',
                    'Is' => '❌ Неправильно. Is для Present Continuous. Правильна відповідь: Has it been snowing all morning?',
                ],
                'source' => 'SET 2: Yes/No Questions',
            ],
        ];
    }

    // SET 2: Wh-Questions (~15 questions) - Answers are question words
    private function getSet2WhQuestions(): array
    {
        return [
            // A1 Level
            [
                'level' => 'A1',
                'question' => '{a1} is your name?',
                'answers' => ['a1' => 'What'],
                'options' => ['What', 'Who', 'Where', 'When'],
                'verb_hint' => 'asking for information',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Для питання про інформацію (ім'я, річ) використовуємо What."],
                'explanations' => [
                    'What' => "✅ Правильно! What для питання про ім'я або річ. Приклад: What is your name?",
                    'Who' => '❌ Неправильно. Who для питання про особу. Правильна відповідь: What is your name?',
                    'Where' => '❌ Неправильно. Where для питання про місце. Правильна відповідь: What is your name?',
                    'When' => '❌ Неправильно. When для питання про час. Правильна відповідь: What is your name?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],
            [
                'level' => 'A1',
                'question' => '{a1} do you live?',
                'answers' => ['a1' => 'Where'],
                'options' => ['Where', 'What', 'Who', 'Why'],
                'verb_hint' => 'location question',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Для питання про місце використовуємо Where.'],
                'explanations' => [
                    'Where' => '✅ Правильно! Where для питання про місце. Приклад: Where do you live?',
                    'What' => '❌ Неправильно. What для речей/інформації. Правильна відповідь: Where do you live?',
                    'Who' => '❌ Неправильно. Who для людей. Правильна відповідь: Where do you live?',
                    'Why' => '❌ Неправильно. Why для причин. Правильна відповідь: Where do you live?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],
            [
                'level' => 'A1',
                'question' => '{a1} is he?',
                'answers' => ['a1' => 'Who'],
                'options' => ['Who', 'What', 'Where', 'How'],
                'verb_hint' => 'person identity',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Для питання про особу (хто це) використовуємо Who.'],
                'explanations' => [
                    'Who' => '✅ Правильно! Who для питання про особу. Приклад: Who is he?',
                    'What' => '❌ Неправильно. What для речей. Правильна відповідь: Who is he?',
                    'Where' => '❌ Неправильно. Where для місця. Правильна відповідь: Who is he?',
                    'How' => '❌ Неправильно. How для способу. Правильна відповідь: Who is he?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],

            // A2 Level
            [
                'level' => 'A2',
                'question' => '{a1} did you go yesterday?',
                'answers' => ['a1' => 'Where'],
                'options' => ['Where', 'When', 'Why', 'Who'],
                'verb_hint' => 'location in past',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Питання про місце у минулому — Where.'],
                'explanations' => [
                    'Where' => '✅ Правильно! Where для питання про місце. Приклад: Where did you go yesterday?',
                    'When' => '❌ Неправильно. When для часу, але вже є yesterday. Правильна відповідь: Where did you go yesterday?',
                    'Why' => '❌ Неправильно. Why для причини. Правильна відповідь: Where did you go yesterday?',
                    'Who' => '❌ Неправильно. Who для людей. Правильна відповідь: Where did you go yesterday?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],
            [
                'level' => 'A2',
                'question' => '{a1} are you late?',
                'answers' => ['a1' => 'Why'],
                'options' => ['Why', 'When', 'Where', 'What'],
                'verb_hint' => 'reason question',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Для питання про причину використовуємо Why.'],
                'explanations' => [
                    'Why' => '✅ Правильно! Why для питання про причину. Приклад: Why are you late?',
                    'When' => '❌ Неправильно. When для часу. Правильна відповідь: Why are you late?',
                    'Where' => '❌ Неправильно. Where для місця. Правильна відповідь: Why are you late?',
                    'What' => '❌ Неправильно. What для речей. Правильна відповідь: Why are you late?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],
            [
                'level' => 'A2',
                'question' => '{a1} does the class start?',
                'answers' => ['a1' => 'When'],
                'options' => ['When', 'Where', 'Why', 'How'],
                'verb_hint' => 'time question',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Для питання про час використовуємо When.'],
                'explanations' => [
                    'When' => '✅ Правильно! When для питання про час. Приклад: When does the class start?',
                    'Where' => '❌ Неправильно. Where для місця. Правильна відповідь: When does the class start?',
                    'Why' => '❌ Неправильно. Why для причини. Правильна відповідь: When does the class start?',
                    'How' => '❌ Неправильно. How для способу. Правильна відповідь: When does the class start?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],

            // B1 Level
            [
                'level' => 'B1',
                'question' => '{a1} have you been learning English?',
                'answers' => ['a1' => 'How long'],
                'options' => ['How long', 'How much', 'How many', 'How often'],
                'verb_hint' => 'duration question',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Для питання про тривалість використовуємо How long.'],
                'explanations' => [
                    'How long' => '✅ Правильно! How long для тривалості. Приклад: How long have you been learning English?',
                    'How much' => '❌ Неправильно. How much для кількості незліченного. Правильна відповідь: How long have you been learning English?',
                    'How many' => '❌ Неправильно. How many для кількості зліченного. Правильна відповідь: How long have you been learning English?',
                    'How often' => '❌ Неправильно. How often для частоти. Правильна відповідь: How long have you been learning English?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],
            [
                'level' => 'B1',
                'question' => '{a1} did she say that?',
                'answers' => ['a1' => 'Why'],
                'options' => ['Why', 'When', 'Where', 'How'],
                'verb_hint' => 'reason in past',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Питання про причину дії у минулому.'],
                'explanations' => [
                    'Why' => '✅ Правильно! Why для причини. Приклад: Why did she say that?',
                    'When' => '❌ Неправильно. When для часу. Правильна відповідь: Why did she say that?',
                    'Where' => '❌ Неправильно. Where для місця. Правильна відповідь: Why did she say that?',
                    'How' => '❌ Неправильно. How для способу. Правильна відповідь: Why did she say that?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],
            [
                'level' => 'B1',
                'question' => '{a1} do you exercise per week?',
                'answers' => ['a1' => 'How often'],
                'options' => ['How often', 'How long', 'How much', 'How many'],
                'verb_hint' => 'frequency question',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Для питання про частоту використовуємо How often.'],
                'explanations' => [
                    'How often' => '✅ Правильно! How often для частоти. Приклад: How often do you exercise per week?',
                    'How long' => '❌ Неправильно. How long для тривалості. Правильна відповідь: How often do you exercise per week?',
                    'How much' => '❌ Неправильно. How much для кількості. Правильна відповідь: How often do you exercise per week?',
                    'How many' => '❌ Неправильно. How many для кількості зліченного. Правильна відповідь: How often do you exercise per week?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],

            // B2 Level
            [
                'level' => 'B2',
                'question' => '{a1} would you handle this situation?',
                'answers' => ['a1' => 'How'],
                'options' => ['How', 'Why', 'When', 'Where'],
                'verb_hint' => 'manner question',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Для питання про спосіб дії використовуємо How.'],
                'explanations' => [
                    'How' => '✅ Правильно! How для способу дії. Приклад: How would you handle this situation?',
                    'Why' => '❌ Неправильно. Why для причини. Правильна відповідь: How would you handle this situation?',
                    'When' => '❌ Неправильно. When для часу. Правильна відповідь: How would you handle this situation?',
                    'Where' => '❌ Неправильно. Where для місця. Правильна відповідь: How would you handle this situation?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],
            [
                'level' => 'B2',
                'question' => '{a1} did you realize the mistake?',
                'answers' => ['a1' => 'When'],
                'options' => ['When', 'Why', 'Where', 'How'],
                'verb_hint' => 'time of realization',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Питання про момент усвідомлення.'],
                'explanations' => [
                    'When' => '✅ Правильно! When для моменту дії. Приклад: When did you realize the mistake?',
                    'Why' => '❌ Неправильно. Why для причини. Правильна відповідь: When did you realize the mistake?',
                    'Where' => '❌ Неправильно. Where для місця. Правильна відповідь: When did you realize the mistake?',
                    'How' => '❌ Неправильно. How для способу. Правильна відповідь: When did you realize the mistake?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],
            [
                'level' => 'B2',
                'question' => '{a1} should we prioritize?',
                'answers' => ['a1' => 'What'],
                'options' => ['What', 'Who', 'When', 'Where'],
                'verb_hint' => 'thing selection',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Питання про вибір речі/завдання.'],
                'explanations' => [
                    'What' => '✅ Правильно! What для вибору речі. Приклад: What should we prioritize?',
                    'Who' => '❌ Неправильно. Who для людей. Правильна відповідь: What should we prioritize?',
                    'When' => '❌ Неправильно. When для часу. Правильна відповідь: What should we prioritize?',
                    'Where' => '❌ Неправильно. Where для місця. Правильна відповідь: What should we prioritize?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],

            // C1 Level
            [
                'level' => 'C1',
                'question' => '{a1} might have caused the delay?',
                'answers' => ['a1' => 'What'],
                'options' => ['What', 'Who', 'Why', 'When'],
                'verb_hint' => 'cause speculation',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Здогадка про можливу причину (річ/подію).'],
                'explanations' => [
                    'What' => '✅ Правильно! What для речі/події. Приклад: What might have caused the delay?',
                    'Who' => '❌ Неправильно. Who для людей. Правильна відповідь: What might have caused the delay?',
                    'Why' => '❌ Неправильно. Why для причини, але вже є caused. Правильна відповідь: What might have caused the delay?',
                    'When' => '❌ Неправильно. When для часу. Правильна відповідь: What might have caused the delay?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],
            [
                'level' => 'C1',
                'question' => '{a1} were you planning to inform us?',
                'answers' => ['a1' => 'When'],
                'options' => ['When', 'Why', 'How', 'Where'],
                'verb_hint' => 'intended time',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Питання про запланований момент.'],
                'explanations' => [
                    'When' => '✅ Правильно! When для запланованого часу. Приклад: When were you planning to inform us?',
                    'Why' => '❌ Неправильно. Why для причини. Правильна відповідь: When were you planning to inform us?',
                    'How' => '❌ Неправильно. How для способу. Правильна відповідь: When were you planning to inform us?',
                    'Where' => '❌ Неправильно. Where для місця. Правильна відповідь: When were you planning to inform us?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],

            // C2 Level
            [
                'level' => 'C2',
                'question' => '{a1} ought we to have consulted?',
                'answers' => ['a1' => 'Whom'],
                'options' => ['Whom', 'Who', 'What', 'When'],
                'verb_hint' => 'formal person object',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "У формальній мові для об'єкта (кого) використовуємо Whom."],
                'explanations' => [
                    'Whom' => "✅ Правильно! Whom для об'єкта у формальній мові. Приклад: Whom ought we to have consulted?",
                    'Who' => '❌ Неправильно. Who менш формальний. Правильна відповідь: Whom ought we to have consulted?',
                    'What' => '❌ Неправильно. What для речей. Правильна відповідь: Whom ought we to have consulted?',
                    'When' => '❌ Неправильно. When для часу. Правильна відповідь: Whom ought we to have consulted?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],
            [
                'level' => 'A1',
                'question' => '{a1} old are you?',
                'answers' => ['a1' => 'How'],
                'options' => ['How', 'What', 'When', 'Why'],
                'verb_hint' => 'age question',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Для питання про вік використовуємо How old.'],
                'explanations' => [
                    'How' => '✅ Правильно! How old для питання про вік. Приклад: How old are you?',
                    'What' => '❌ Неправильно. What для речей. Правильна відповідь: How old are you?',
                    'When' => '❌ Неправильно. When для часу. Правильна відповідь: How old are you?',
                    'Why' => '❌ Неправильно. Why для причини. Правильна відповідь: How old are you?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],
            [
                'level' => 'B1',
                'question' => '{a1} did you choose this option?',
                'answers' => ['a1' => 'Why'],
                'options' => ['Why', 'When', 'How', 'What'],
                'verb_hint' => 'reason for choice',
                'detail' => 'wh_questions',
                'hints' => ['a1' => 'Питання про причину вибору.'],
                'explanations' => [
                    'Why' => '✅ Правильно! Why для причини вибору. Приклад: Why did you choose this option?',
                    'When' => '❌ Неправильно. When для часу. Правильна відповідь: Why did you choose this option?',
                    'How' => '❌ Неправильно. How для способу. Правильна відповідь: Why did you choose this option?',
                    'What' => '❌ Неправильно. What для речі. Правильна відповідь: Why did you choose this option?',
                ],
                'source' => 'SET 2: Wh-Questions',
            ],
        ];
    }

    // SET 2: Subject Questions (~6 questions)
    private function getSet2SubjectQuestions(): array
    {
        return [
            // A2 Level
            [
                'level' => 'A2',
                'question' => 'Who {a1} this book?',
                'answers' => ['a1' => 'wrote'],
                'options' => ['wrote', 'did write', 'was writing', 'has written'],
                'verb_hint' => 'past simple without auxiliary',
                'detail' => 'subject_questions',
                'hints' => ['a1' => 'У питаннях про підмет не використовуємо допоміжне дієслово. Просто V2.'],
                'explanations' => [
                    'wrote' => '✅ Правильно! У питаннях про підмет просто V2. Приклад: Who wrote this book?',
                    'did write' => '❌ Неправильно. У питаннях про підмет не використовуємо did. Правильна відповідь: Who wrote this book?',
                    'was writing' => '❌ Неправильно. Це Past Continuous, але потрібен простий факт. Правильна відповідь: Who wrote this book?',
                    'has written' => '❌ Неправильно. Це Present Perfect, контекст вимагає Past Simple. Правильна відповідь: Who wrote this book?',
                ],
                'source' => 'SET 2: Subject Questions',
            ],
            [
                'level' => 'A2',
                'question' => 'What {a1} on the table?',
                'answers' => ['a1' => 'is'],
                'options' => ['is', 'does', 'are', 'do'],
                'verb_hint' => 'singular to be',
                'detail' => 'subject_questions',
                'hints' => ['a1' => 'What як підмет — однина, використовуємо is.'],
                'explanations' => [
                    'is' => '✅ Правильно! What (підмет) + is. Приклад: What is on the table?',
                    'does' => '❌ Неправильно. Does не використовується з to be. Правильна відповідь: What is on the table?',
                    'are' => '❌ Неправильно. Are для множини. Правильна відповідь: What is on the table?',
                    'do' => '❌ Неправильно. Do не використовується з to be. Правильна відповідь: What is on the table?',
                ],
                'source' => 'SET 2: Subject Questions',
            ],

            // B1 Level
            [
                'level' => 'B1',
                'question' => 'Who {a1} first?',
                'answers' => ['a1' => 'arrived'],
                'options' => ['arrived', 'did arrive', 'has arrived', 'was arriving'],
                'verb_hint' => 'simple past',
                'detail' => 'subject_questions',
                'hints' => ['a1' => 'У питаннях про підмет у Past Simple просто V2, без did.'],
                'explanations' => [
                    'arrived' => '✅ Правильно! У питаннях про підмет просто V2. Приклад: Who arrived first?',
                    'did arrive' => '❌ Неправильно. У питаннях про підмет не використовуємо did. Правильна відповідь: Who arrived first?',
                    'has arrived' => '❌ Неправильно. Контекст вимагає Past Simple. Правильна відповідь: Who arrived first?',
                    'was arriving' => '❌ Неправильно. Це Past Continuous. Правильна відповідь: Who arrived first?',
                ],
                'source' => 'SET 2: Subject Questions',
            ],
            [
                'level' => 'B1',
                'question' => 'What {a1} the problem?',
                'answers' => ['a1' => 'caused'],
                'options' => ['caused', 'did cause', 'has caused', 'was causing'],
                'verb_hint' => 'past without auxiliary',
                'detail' => 'subject_questions',
                'hints' => ['a1' => 'What як підмет у Past Simple — просто V2.'],
                'explanations' => [
                    'caused' => '✅ Правильно! What (підмет) + V2. Приклад: What caused the problem?',
                    'did cause' => '❌ Неправильно. У питаннях про підмет не використовуємо did. Правильна відповідь: What caused the problem?',
                    'has caused' => '❌ Неправильно. Контекст вимагає Past Simple. Правильна відповідь: What caused the problem?',
                    'was causing' => '❌ Неправильно. Це Past Continuous. Правильна відповідь: What caused the problem?',
                ],
                'source' => 'SET 2: Subject Questions',
            ],

            // B2 Level
            [
                'level' => 'B2',
                'question' => 'Who {a1} the decision?',
                'answers' => ['a1' => 'made'],
                'options' => ['made', 'did make', 'has made', 'was making'],
                'verb_hint' => 'simple past action',
                'detail' => 'subject_questions',
                'hints' => ['a1' => 'Питання про підмет у минулому — V2 без допоміжного.'],
                'explanations' => [
                    'made' => '✅ Правильно! Who (підмет) + V2. Приклад: Who made the decision?',
                    'did make' => '❌ Неправильно. У питаннях про підмет не використовуємо did. Правильна відповідь: Who made the decision?',
                    'has made' => '❌ Неправильно. Контекст вимагає Past Simple. Правильна відповідь: Who made the decision?',
                    'was making' => '❌ Неправильно. Це Past Continuous. Правильна відповідь: Who made the decision?',
                ],
                'source' => 'SET 2: Subject Questions',
            ],

            // C1 Level
            [
                'level' => 'C1',
                'question' => 'Which factors {a1} contributed to the outcome?',
                'answers' => ['a1' => 'have'],
                'options' => ['have', 'has', 'did', 'were'],
                'verb_hint' => 'plural perfect',
                'detail' => 'subject_questions',
                'hints' => ['a1' => 'Which factors (підмет, множина) у Present Perfect.'],
                'explanations' => [
                    'have' => '✅ Правильно! Множина як підмет + have. Приклад: Which factors have contributed to the outcome?',
                    'has' => '❌ Неправильно. Has для однини. Правильна відповідь: Which factors have contributed to the outcome?',
                    'did' => '❌ Неправильно. Контекст вимагає Perfect, не Past Simple. Правильна відповідь: Which factors have contributed to the outcome?',
                    'were' => '❌ Неправильно. Were для Past Continuous. Правильна відповідь: Which factors have contributed to the outcome?',
                ],
                'source' => 'SET 2: Subject Questions',
            ],
        ];
    }

    // SET 2: Tag Questions (~8 questions)
    private function getSet2TagQuestions(): array
    {
        return [
            // A2 Level
            [
                'level' => 'A2',
                'question' => 'You like coffee, {a1}?',
                'answers' => ['a1' => "don't you"],
                'options' => ["don't you", 'do you', "aren't you", 'are you'],
                'verb_hint' => 'negative tag after positive',
                'detail' => 'tag_questions',
                'hints' => ['a1' => 'Після ствердного речення у Present Simple додаємо заперечний tag.'],
                'explanations' => [
                    "don't you" => "✅ Правильно! Після ствердження заперечний tag. Приклад: You like coffee, don't you?",
                    'do you' => "❌ Неправильно. Після ствердження потрібен заперечний tag. Правильна відповідь: You like coffee, don't you?",
                    "aren't you" => "❌ Неправильно. Aren't для to be. Правильна відповідь: You like coffee, don't you?",
                    'are you' => "❌ Неправильно. Are для to be, і потрібен заперечний tag. Правильна відповідь: You like coffee, don't you?",
                ],
                'source' => 'SET 2: Tag Questions',
            ],
            [
                'level' => 'A2',
                'question' => "She doesn't smoke, {a1}?",
                'answers' => ['a1' => 'does she'],
                'options' => ['does she', "doesn't she", 'is she', "isn't she"],
                'verb_hint' => 'positive after negative',
                'detail' => 'tag_questions',
                'hints' => ['a1' => 'Після заперечного речення додаємо ствердний tag.'],
                'explanations' => [
                    'does she' => "✅ Правильно! Після заперечення ствердний tag. Приклад: She doesn't smoke, does she?",
                    "doesn't she" => "❌ Неправильно. Після заперечення потрібен ствердний tag. Правильна відповідь: She doesn't smoke, does she?",
                    'is she' => "❌ Неправильно. Is для to be. Правильна відповідь: She doesn't smoke, does she?",
                    "isn't she" => "❌ Неправильно. Isn't для to be. Правильна відповідь: She doesn't smoke, does she?",
                ],
                'source' => 'SET 2: Tag Questions',
            ],

            // B1 Level
            [
                'level' => 'B1',
                'question' => 'They can swim, {a1}?',
                'answers' => ['a1' => "can't they"],
                'options' => ["can't they", 'can they', "don't they", 'do they'],
                'verb_hint' => 'modal tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Tag від модального can — can't."],
                'explanations' => [
                    "can't they" => "✅ Правильно! Tag від can — can't. Приклад: They can swim, can't they?",
                    'can they' => "❌ Неправильно. Після ствердження потрібен заперечний tag. Правильна відповідь: They can swim, can't they?",
                    "don't they" => "❌ Неправильно. Don't для Present Simple, але тут can. Правильна відповідь: They can swim, can't they?",
                    'do they' => "❌ Неправильно. Do не використовується з can. Правильна відповідь: They can swim, can't they?",
                ],
                'source' => 'SET 2: Tag Questions',
            ],
            [
                'level' => 'B1',
                'question' => "He won't be late, {a1}?",
                'answers' => ['a1' => 'will he'],
                'options' => ['will he', "won't he", 'is he', "isn't he"],
                'verb_hint' => 'future tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Після заперечного won't додаємо ствердний will."],
                'explanations' => [
                    'will he' => "✅ Правильно! Після won't ствердний will. Приклад: He won't be late, will he?",
                    "won't he" => "❌ Неправильно. Після заперечення потрібен ствердний tag. Правильна відповідь: He won't be late, will he?",
                    'is he' => "❌ Неправильно. Is не відповідає won't. Правильна відповідь: He won't be late, will he?",
                    "isn't he" => "❌ Неправильно. Isn't не відповідає won't. Правильна відповідь: He won't be late, will he?",
                ],
                'source' => 'SET 2: Tag Questions',
            ],

            // B2 Level
            [
                'level' => 'B2',
                'question' => 'You would help me, {a1}?',
                'answers' => ['a1' => "wouldn't you"],
                'options' => ["wouldn't you", 'would you', "won't you", 'will you'],
                'verb_hint' => 'conditional tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Tag від would — wouldn't."],
                'explanations' => [
                    "wouldn't you" => "✅ Правильно! Tag від would — wouldn't. Приклад: You would help me, wouldn't you?",
                    'would you' => "❌ Неправильно. Після ствердження потрібен заперечний tag. Правильна відповідь: You would help me, wouldn't you?",
                    "won't you" => "❌ Неправильно. Won't від will, не від would. Правильна відповідь: You would help me, wouldn't you?",
                    'will you' => "❌ Неправильно. Will не відповідає would. Правильна відповідь: You would help me, wouldn't you?",
                ],
                'source' => 'SET 2: Tag Questions',
            ],
            [
                'level' => 'B2',
                'question' => 'She had left early, {a1}?',
                'answers' => ['a1' => "hadn't she"],
                'options' => ["hadn't she", 'had she', "didn't she", 'did she'],
                'verb_hint' => 'past perfect tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Tag від Past Perfect had — hadn't."],
                'explanations' => [
                    "hadn't she" => "✅ Правильно! Tag від had — hadn't. Приклад: She had left early, hadn't she?",
                    'had she' => "❌ Неправильно. Після ствердження потрібен заперечний tag. Правильна відповідь: She had left early, hadn't she?",
                    "didn't she" => "❌ Неправильно. Didn't від Past Simple. Правильна відповідь: She had left early, hadn't she?",
                    'did she' => "❌ Неправильно. Did не відповідає had. Правильна відповідь: She had left early, hadn't she?",
                ],
                'source' => 'SET 2: Tag Questions',
            ],

            // C1 Level
            [
                'level' => 'C1',
                'question' => 'Nobody called, {a1}?',
                'answers' => ['a1' => 'did they'],
                'options' => ['did they', "didn't they", 'do they', "don't they"],
                'verb_hint' => 'tag after nobody',
                'detail' => 'tag_questions',
                'hints' => ['a1' => 'Після nobody (заперечне значення) використовуємо ствердний tag з they.'],
                'explanations' => [
                    'did they' => '✅ Правильно! Після nobody ствердний tag з they. Приклад: Nobody called, did they?',
                    "didn't they" => '❌ Неправильно. Після nobody (заперечне) потрібен ствердний tag. Правильна відповідь: Nobody called, did they?',
                    'do they' => '❌ Неправильно. Do для Present Simple, але called — Past. Правильна відповідь: Nobody called, did they?',
                    "don't they" => "❌ Неправильно. Don't не відповідає Past Simple. Правильна відповідь: Nobody called, did they?",
                ],
                'source' => 'SET 2: Tag Questions',
            ],

            // C2 Level
            [
                'level' => 'C2',
                'question' => "Let's proceed with the plan, {a1}?",
                'answers' => ['a1' => 'shall we'],
                'options' => ['shall we', "let's we", 'will we', "won't we"],
                'verb_hint' => 'suggestion tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Після Let's використовуємо shall we."],
                'explanations' => [
                    'shall we' => "✅ Правильно! Після Let's використовуємо shall we. Приклад: Let's proceed with the plan, shall we?",
                    "let's we" => "❌ Неправильно. Неправильна конструкція. Правильна відповідь: Let's proceed with the plan, shall we?",
                    'will we' => "❌ Неправильно. Will можливий, але shall we більш поширений. Правильна відповідь: Let's proceed with the plan, shall we?",
                    "won't we" => "❌ Неправильно. Won't не використовується після Let's. Правильна відповідь: Let's proceed with the plan, shall we?",
                ],
                'source' => 'SET 2: Tag Questions',
            ],
            [
                'level' => 'A2',
                'question' => "It's cold today, {a1}?",
                'answers' => ['a1' => "isn't it"],
                'options' => ["isn't it", 'is it', "doesn't it", 'does it'],
                'verb_hint' => 'weather tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Після ствердного речення з it's додаємо isn't it."],
                'explanations' => [
                    "isn't it" => "✅ Правильно! Tag від it's — isn't it. Приклад: It's cold today, isn't it?",
                    'is it' => "❌ Неправильно. Після ствердження потрібен заперечний tag. Правильна відповідь: It's cold today, isn't it?",
                    "doesn't it" => "❌ Неправильно. Doesn't для дій, але cold — стан. Правильна відповідь: It's cold today, isn't it?",
                    'does it' => "❌ Неправильно. Does не використовується з to be. Правильна відповідь: It's cold today, isn't it?",
                ],
                'source' => 'SET 2: Tag Questions',
            ],
            [
                'level' => 'B1',
                'question' => "You haven't seen my keys, {a1}?",
                'answers' => ['a1' => 'have you'],
                'options' => ['have you', "haven't you", 'did you', "didn't you"],
                'verb_hint' => 'perfect tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Після заперечного haven't додаємо ствердний have."],
                'explanations' => [
                    'have you' => "✅ Правильно! Після haven't ствердний have. Приклад: You haven't seen my keys, have you?",
                    "haven't you" => "❌ Неправильно. Після заперечення потрібен ствердний tag. Правильна відповідь: You haven't seen my keys, have you?",
                    'did you' => "❌ Неправильно. Did не відповідає haven't. Правильна відповідь: You haven't seen my keys, have you?",
                    "didn't you" => "❌ Неправильно. Didn't для Past Simple. Правильна відповідь: You haven't seen my keys, have you?",
                ],
                'source' => 'SET 2: Tag Questions',
            ],
        ];
    }

    // SET 2: Indirect Questions (~12 questions)
    private function getSet2IndirectQuestions(): array
    {
        return [
            // A2 Level
            [
                'level' => 'A2',
                'question' => 'Can you tell me where {a1}?',
                'answers' => ['a1' => 'the station is'],
                'options' => ['the station is', 'is the station', 'the is station', 'station is the'],
                'verb_hint' => 'statement order',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => 'У непрямих питаннях прямий порядок слів: підмет + дієслово.'],
                'explanations' => [
                    'the station is' => '✅ Правильно! У непрямих питаннях прямий порядок. Приклад: Can you tell me where the station is?',
                    'is the station' => '❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: Can you tell me where the station is?',
                    'the is station' => '❌ Неправильно. Неправильний порядок слів. Правильна відповідь: Can you tell me where the station is?',
                    'station is the' => '❌ Неправильно. Повністю неправильний порядок. Правильна відповідь: Can you tell me where the station is?',
                ],
                'source' => 'SET 2: Indirect Questions',
            ],
            [
                'level' => 'A2',
                'question' => 'Do you know what {a1}?',
                'answers' => ['a1' => 'time it is'],
                'options' => ['time it is', 'it is time', 'is it time', 'time is it'],
                'verb_hint' => 'no inversion',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => 'У непрямих питаннях звичайний порядок слів без інверсії.'],
                'explanations' => [
                    'time it is' => '✅ Правильно! Прямий порядок слів. Приклад: Do you know what time it is?',
                    'it is time' => '❌ Неправильно. Неправильний порядок для питання про час. Правильна відповідь: Do you know what time it is?',
                    'is it time' => '❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: Do you know what time it is?',
                    'time is it' => '❌ Неправильно. Неправильний порядок. Правильна відповідь: Do you know what time it is?',
                ],
                'source' => 'SET 2: Indirect Questions',
            ],

            // B1 Level
            [
                'level' => 'B1',
                'question' => 'Could you tell me how much {a1}?',
                'answers' => ['a1' => 'it costs'],
                'options' => ['it costs', 'costs it', 'does it cost', 'it does cost'],
                'verb_hint' => 'subject before verb',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => 'У непрямих питаннях підмет перед дієсловом, без do/does.'],
                'explanations' => [
                    'it costs' => '✅ Правильно! Прямий порядок без do/does. Приклад: Could you tell me how much it costs?',
                    'costs it' => '❌ Неправильно. Підмет має бути перед дієсловом. Правильна відповідь: Could you tell me how much it costs?',
                    'does it cost' => '❌ Неправильно. У непрямих питаннях не використовуємо does. Правильна відповідь: Could you tell me how much it costs?',
                    'it does cost' => '❌ Неправильно. Не потрібен does. Правильна відповідь: Could you tell me how much it costs?',
                ],
                'source' => 'SET 2: Indirect Questions',
            ],
            [
                'level' => 'B1',
                'question' => 'I wonder why {a1} late.',
                'answers' => ['a1' => 'she was'],
                'options' => ['she was', 'was she', 'she were', 'were she'],
                'verb_hint' => 'statement word order',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => 'Після I wonder використовується прямий порядок слів.'],
                'explanations' => [
                    'she was' => '✅ Правильно! Прямий порядок після I wonder. Приклад: I wonder why she was late.',
                    'was she' => '❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: I wonder why she was late.',
                    'she were' => '❌ Неправильно. Were не використовується з she у цьому контексті. Правильна відповідь: I wonder why she was late.',
                    'were she' => '❌ Неправильно. Інверсія та неправильна форма. Правильна відповідь: I wonder why she was late.',
                ],
                'source' => 'SET 2: Indirect Questions',
            ],
            [
                'level' => 'B1',
                'question' => 'Can you explain how {a1}?',
                'answers' => ['a1' => 'this works'],
                'options' => ['this works', 'works this', 'does this work', 'this does work'],
                'verb_hint' => 'normal order',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => 'У непрямих питаннях звичайний порядок слів без допоміжних дієслів.'],
                'explanations' => [
                    'this works' => '✅ Правильно! Прямий порядок без does. Приклад: Can you explain how this works?',
                    'works this' => '❌ Неправильно. Підмет перед дієсловом. Правильна відповідь: Can you explain how this works?',
                    'does this work' => '❌ Неправильно. У непрямих питаннях не використовуємо does. Правильна відповідь: Can you explain how this works?',
                    'this does work' => '❌ Неправильно. Не потрібен does. Правильна відповідь: Can you explain how this works?',
                ],
                'source' => 'SET 2: Indirect Questions',
            ],

            // B2 Level
            [
                'level' => 'B2',
                'question' => "I'm not sure whether {a1} tomorrow.",
                'answers' => ['a1' => 'he will come'],
                'options' => ['he will come', 'will he come', 'he come will', 'will come he'],
                'verb_hint' => 'statement order with modal',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => 'Після whether використовується прямий порядок слів.'],
                'explanations' => [
                    'he will come' => "✅ Правильно! Прямий порядок після whether. Приклад: I'm not sure whether he will come tomorrow.",
                    'will he come' => "❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: I'm not sure whether he will come tomorrow.",
                    'he come will' => "❌ Неправильно. Неправильний порядок слів. Правильна відповідь: I'm not sure whether he will come tomorrow.",
                    'will come he' => "❌ Неправильно. Підмет має бути між will та come. Правильна відповідь: I'm not sure whether he will come tomorrow.",
                ],
                'source' => 'SET 2: Indirect Questions',
            ],
            [
                'level' => 'B2',
                'question' => 'Do you remember when {a1}?',
                'answers' => ['a1' => 'they arrived'],
                'options' => ['they arrived', 'arrived they', 'did they arrive', 'they did arrive'],
                'verb_hint' => 'past without auxiliary',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => 'У непрямих питаннях у минулому не використовуємо did.'],
                'explanations' => [
                    'they arrived' => '✅ Правильно! Прямий порядок без did. Приклад: Do you remember when they arrived?',
                    'arrived they' => '❌ Неправильно. Підмет перед дієсловом. Правильна відповідь: Do you remember when they arrived?',
                    'did they arrive' => '❌ Неправильно. У непрямих питаннях не використовуємо did. Правильна відповідь: Do you remember when they arrived?',
                    'they did arrive' => '❌ Неправильно. Не потрібен did. Правильна відповідь: Do you remember when they arrived?',
                ],
                'source' => 'SET 2: Indirect Questions',
            ],
            [
                'level' => 'B2',
                'question' => 'Could you clarify what {a1} next?',
                'answers' => ['a1' => 'we should do'],
                'options' => ['we should do', 'should we do', 'we do should', 'do we should'],
                'verb_hint' => 'modal in statement order',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => 'У непрямих питаннях модальне дієслово після підмета.'],
                'explanations' => [
                    'we should do' => '✅ Правильно! Прямий порядок з should. Приклад: Could you clarify what we should do next?',
                    'should we do' => '❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: Could you clarify what we should do next?',
                    'we do should' => '❌ Неправильно. Should має бути перед do. Правильна відповідь: Could you clarify what we should do next?',
                    'do we should' => '❌ Неправильно. Повністю неправильний порядок. Правильна відповідь: Could you clarify what we should do next?',
                ],
                'source' => 'SET 2: Indirect Questions',
            ],

            // C1 Level
            [
                'level' => 'C1',
                'question' => "I'd like to understand why {a1} this approach.",
                'answers' => ['a1' => 'they chose'],
                'options' => ['they chose', 'chose they', 'did they choose', 'they did choose'],
                'verb_hint' => 'past statement order',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => 'У непрямих питаннях у минулому прямий порядок без did.'],
                'explanations' => [
                    'they chose' => "✅ Правильно! Прямий порядок без did. Приклад: I'd like to understand why they chose this approach.",
                    'chose they' => "❌ Неправильно. Підмет перед дієсловом. Правильна відповідь: I'd like to understand why they chose this approach.",
                    'did they choose' => "❌ Неправильно. У непрямих питаннях не використовуємо did. Правильна відповідь: I'd like to understand why they chose this approach.",
                    'they did choose' => "❌ Неправильно. Не потрібен did. Правильна відповідь: I'd like to understand why they chose this approach.",
                ],
                'source' => 'SET 2: Indirect Questions',
            ],
            [
                'level' => 'C1',
                'question' => 'We need to determine if {a1} feasible.',
                'answers' => ['a1' => 'this is'],
                'options' => ['this is', 'is this', 'this be', 'be this'],
                'verb_hint' => 'to be statement order',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => 'Після if використовується прямий порядок слів з is.'],
                'explanations' => [
                    'this is' => '✅ Правильно! Прямий порядок після if. Приклад: We need to determine if this is feasible.',
                    'is this' => '❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: We need to determine if this is feasible.',
                    'this be' => '❌ Неправильно. Be — неправильна форма, потрібен is. Правильна відповідь: We need to determine if this is feasible.',
                    'be this' => '❌ Неправильно. Інверсія та неправильна форма. Правильна відповідь: We need to determine if this is feasible.',
                ],
                'source' => 'SET 2: Indirect Questions',
            ],

            // C2 Level
            [
                'level' => 'C2',
                'question' => 'One might wonder whether {a1} been considered.',
                'answers' => ['a1' => 'all options have'],
                'options' => ['all options have', 'have all options', 'all have options', 'options all have'],
                'verb_hint' => 'perfect statement order',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => 'У непрямих питаннях підмет перед have у Present Perfect.'],
                'explanations' => [
                    'all options have' => '✅ Правильно! Прямий порядок з have. Приклад: One might wonder whether all options have been considered.',
                    'have all options' => '❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: One might wonder whether all options have been considered.',
                    'all have options' => '❌ Неправильно. Неправильний порядок слів. Правильна відповідь: One might wonder whether all options have been considered.',
                    'options all have' => '❌ Неправильно. All має бути перед options. Правильна відповідь: One might wonder whether all options have been considered.',
                ],
                'source' => 'SET 2: Indirect Questions',
            ],
            [
                'level' => 'C2',
                'question' => 'The question remains as to how {a1} implemented.',
                'answers' => ['a1' => 'this should be'],
                'options' => ['this should be', 'should this be', 'this be should', 'be this should'],
                'verb_hint' => 'passive modal statement',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => 'У непрямих питаннях прямий порядок навіть з пасивними модальними конструкціями.'],
                'explanations' => [
                    'this should be' => '✅ Правильно! Прямий порядок з модальним пасивом. Приклад: The question remains as to how this should be implemented.',
                    'should this be' => '❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: The question remains as to how this should be implemented.',
                    'this be should' => '❌ Неправильно. Should має бути перед be. Правильна відповідь: The question remains as to how this should be implemented.',
                    'be this should' => '❌ Неправильно. Повністю неправильний порядок. Правильна відповідь: The question remains as to how this should be implemented.',
                ],
                'source' => 'SET 2: Indirect Questions',
            ],
        ];
    }

    /**
     * SET 3: Multiple fill-in-the-blank questions ({a1}...{aN})
     * Equal number of questions for each CEFR level (A1-C2)
     * These questions have multiple blanks that need to be filled.
     */
    private function getMultipleFillInBlankQuestions(): array
    {
        return [
            // ===== A1 Level: 4 questions =====
            [
                'level' => 'A1',
                'question' => '{a1} you {a2} pizza?',
                'answers' => ['a1' => 'Do', 'a2' => 'like'],
                'options' => [
                    'a1' => ['Do', 'Does', 'Are', 'Is'],
                    'a2' => ['like', 'likes', 'liking', 'liked'],
                ],
                'verb_hints' => ['a1' => 'auxiliary for you', 'a2' => 'base form after do'],
                'detail' => 'yes_no',
                'hints' => [
                    'a1' => 'Для формування питання з you використовуємо Do.',
                    'a2' => 'Після допоміжного дієслова do/does використовується базова форма дієслова без закінчення -s.',
                ],
                'explanations' => [
                    'Do' => '✅ Правильно! З you використовуємо Do.',
                    'Does' => '❌ Неправильно. Does використовується з he/she/it.',
                    'Are' => '❌ Неправильно. Are використовується з дієсловом to be.',
                    'Is' => '❌ Неправильно. Is використовується з he/she/it та to be.',
                    'like' => '✅ Правильно! Базова форма після do.',
                    'likes' => '❌ Неправильно. Після do використовується базова форма без -s.',
                    'liking' => '❌ Неправильно. -ing форма тут не потрібна.',
                    'liked' => '❌ Неправильно. Минулий час тут не потрібен.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} she {a2} English?',
                'answers' => ['a1' => 'Does', 'a2' => 'speak'],
                'options' => [
                    'a1' => ['Does', 'Do', 'Is', 'Are'],
                    'a2' => ['speak', 'speaks', 'speaking', 'spoke'],
                ],
                'verb_hints' => ['a1' => 'auxiliary for she', 'a2' => 'base form after does'],
                'detail' => 'yes_no',
                'hints' => [
                    'a1' => 'Для формування питання з she використовуємо Does.',
                    'a2' => 'Після does дієслово завжди у базовій формі.',
                ],
                'explanations' => [
                    'Does' => '✅ Правильно! З she використовуємо Does.',
                    'Do' => '❌ Неправильно. Do використовується з I/you/we/they.',
                    'Is' => '❌ Неправильно. Is для дієслова to be.',
                    'Are' => '❌ Неправильно. Are для множини з to be.',
                    'speak' => '✅ Правильно! Базова форма після does.',
                    'speaks' => '❌ Неправильно. Після does дієслово без -s.',
                    'speaking' => '❌ Неправильно. -ing форма не використовується.',
                    'spoke' => '❌ Неправильно. Past Simple тут не потрібен.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'What {a1} your name? My name {a2} Anna.',
                'answers' => ['a1' => 'is', 'a2' => 'is'],
                'options' => [
                    'a1' => ['is', 'are', 'does', 'do'],
                    'a2' => ['is', 'are', 'am', 'be'],
                ],
                'verb_hints' => ['a1' => 'to be singular', 'a2' => 'to be singular'],
                'detail' => 'wh_questions',
                'hints' => [
                    'a1' => 'Your name — однина, тому is.',
                    'a2' => 'My name — однина, тому is.',
                ],
                'explanations' => [
                    'is' => '✅ Правильно! Is для однини.',
                    'are' => '❌ Неправильно. Are для множини.',
                    'does' => '❌ Неправильно. Does для дій.',
                    'do' => '❌ Неправильно. Do для дій.',
                    'am' => '❌ Неправильно. Am тільки з I.',
                    'be' => '❌ Неправильно. Потрібна відмінена форма.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} they {a2} at home now?',
                'answers' => ['a1' => 'Are', 'a2' => 'staying'],
                'options' => [
                    'a1' => ['Are', 'Is', 'Do', 'Does'],
                    'a2' => ['staying', 'stay', 'stays', 'stayed'],
                ],
                'verb_hints' => ['a1' => 'to be for continuous', 'a2' => 'present participle'],
                'detail' => 'yes_no',
                'hints' => [
                    'a1' => 'Present Continuous: Are + they + V-ing.',
                    'a2' => 'У Present Continuous дієслово має форму -ing.',
                ],
                'explanations' => [
                    'Are' => '✅ Правильно! Are для Present Continuous з they.',
                    'Is' => '❌ Неправильно. Is для he/she/it.',
                    'Do' => '❌ Неправильно. Do для Present Simple.',
                    'Does' => '❌ Неправильно. Does для Present Simple.',
                    'staying' => '✅ Правильно! V-ing для Present Continuous.',
                    'stay' => '❌ Неправильно. Базова форма для Present Simple.',
                    'stays' => '❌ Неправильно. -s форма для Present Simple.',
                    'stayed' => '❌ Неправильно. Past Simple тут не потрібен.',
                ],
            ],

            // ===== A2 Level: 4 questions =====
            [
                'level' => 'A2',
                'question' => '{a1} you {a2} to the cinema yesterday?',
                'answers' => ['a1' => 'Did', 'a2' => 'go'],
                'options' => [
                    'a1' => ['Did', 'Do', 'Were', 'Was'],
                    'a2' => ['go', 'went', 'going', 'gone'],
                ],
                'verb_hints' => ['a1' => 'past auxiliary', 'a2' => 'base form after did'],
                'detail' => 'yes_no',
                'hints' => [
                    'a1' => 'Для Past Simple питань використовуємо Did.',
                    'a2' => 'Після did завжди базова форма дієслова.',
                ],
                'explanations' => [
                    'Did' => '✅ Правильно! Did для Past Simple питань.',
                    'Do' => '❌ Неправильно. Do для Present Simple.',
                    'Were' => '❌ Неправильно. Were для to be.',
                    'Was' => '❌ Неправильно. Was для to be.',
                    'go' => '✅ Правильно! Базова форма після did.',
                    'went' => '❌ Неправильно. Після did — базова форма.',
                    'going' => '❌ Неправильно. -ing тут не потрібна.',
                    'gone' => '❌ Неправильно. V3 тут не потрібна.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Where {a1} she {a2} last summer?',
                'answers' => ['a1' => 'did', 'a2' => 'travel'],
                'options' => [
                    'a1' => ['did', 'does', 'was', 'is'],
                    'a2' => ['travel', 'traveled', 'travelling', 'travels'],
                ],
                'verb_hints' => ['a1' => 'past auxiliary', 'a2' => 'base form'],
                'detail' => 'wh_questions',
                'hints' => [
                    'a1' => 'Last summer — минулий час, потрібен did.',
                    'a2' => 'Після did — базова форма дієслова.',
                ],
                'explanations' => [
                    'did' => '✅ Правильно! Did для минулого часу.',
                    'does' => '❌ Неправильно. Does для теперішнього.',
                    'was' => '❌ Неправильно. Was для to be.',
                    'is' => '❌ Неправильно. Is для теперішнього.',
                    'travel' => '✅ Правильно! Базова форма після did.',
                    'traveled' => '❌ Неправильно. V2 не потрібна після did.',
                    'travelling' => '❌ Неправильно. -ing тут не потрібна.',
                    'travels' => '❌ Неправильно. -s форма тут не потрібна.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Can you tell me where the bank {a1}? I {a2} find it.',
                'answers' => ['a1' => 'is', 'a2' => "can't"],
                'options' => [
                    'a1' => ['is', 'are', 'does', 'do'],
                    'a2' => ["can't", "don't", "won't", "didn't"],
                ],
                'verb_hints' => ['a1' => 'statement order', 'a2' => 'modal negative'],
                'detail' => 'indirect_questions',
                'hints' => [
                    'a1' => 'У непрямих питаннях прямий порядок слів.',
                    'a2' => "Can't означає неможливість.",
                ],
                'explanations' => [
                    'is' => '✅ Правильно! Прямий порядок: the bank is.',
                    'are' => '❌ Неправильно. Bank — однина.',
                    'does' => '❌ Неправильно. Does не підходить.',
                    'do' => '❌ Неправильно. Do не підходить.',
                    "can't" => '✅ Правильно! Неможливість знайти.',
                    "don't" => '❌ Неправильно. Контекст вимагає can.',
                    "won't" => '❌ Неправильно. Майбутній час не підходить.',
                    "didn't" => '❌ Неправильно. Минулий час не підходить.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'You {a1} French, {a2} you?',
                'answers' => ['a1' => 'speak', 'a2' => "don't"],
                'options' => [
                    'a1' => ['speak', 'speaks', 'speaking', 'spoke'],
                    'a2' => ["don't", 'do', "aren't", 'are'],
                ],
                'verb_hints' => ['a1' => 'present simple', 'a2' => 'negative tag'],
                'detail' => 'tag_questions',
                'hints' => [
                    'a1' => 'You speak — Present Simple з you.',
                    'a2' => "Після ствердження — заперечний tag: don't you.",
                ],
                'explanations' => [
                    'speak' => '✅ Правильно! Базова форма з you.',
                    'speaks' => '❌ Неправильно. -s для he/she/it.',
                    'speaking' => '❌ Неправильно. -ing тут не потрібна.',
                    'spoke' => '❌ Неправильно. Минулий час.',
                    "don't" => '✅ Правильно! Заперечний tag.',
                    'do' => '❌ Неправильно. Потрібен заперечний tag.',
                    "aren't" => '❌ Неправильно. Aren\'t для to be.',
                    'are' => '❌ Неправильно. Are для to be.',
                ],
            ],

            // ===== B1 Level: 4 questions =====
            [
                'level' => 'B1',
                'question' => '{a1} you ever {a2} to London?',
                'answers' => ['a1' => 'Have', 'a2' => 'been'],
                'options' => [
                    'a1' => ['Have', 'Has', 'Did', 'Were'],
                    'a2' => ['been', 'be', 'being', 'was'],
                ],
                'verb_hints' => ['a1' => 'present perfect auxiliary', 'a2' => 'past participle'],
                'detail' => 'yes_no',
                'hints' => [
                    'a1' => 'Present Perfect: Have + you + V3.',
                    'a2' => 'Been — це V3 від be.',
                ],
                'explanations' => [
                    'Have' => '✅ Правильно! Have для Present Perfect з you.',
                    'Has' => '❌ Неправильно. Has для he/she/it.',
                    'Did' => '❌ Неправильно. Did для Past Simple.',
                    'Were' => '❌ Неправильно. Were не підходить.',
                    'been' => '✅ Правильно! V3 від be.',
                    'be' => '❌ Неправильно. Потрібна V3 форма.',
                    'being' => '❌ Неправильно. -ing тут не потрібна.',
                    'was' => '❌ Неправильно. Was — це Past Simple.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'How long {a1} you {a2} waiting here?',
                'answers' => ['a1' => 'have', 'a2' => 'been'],
                'options' => [
                    'a1' => ['have', 'has', 'are', 'were'],
                    'a2' => ['been', 'be', 'being', 'was'],
                ],
                'verb_hints' => ['a1' => 'perfect continuous', 'a2' => 'been for continuous'],
                'detail' => 'wh_questions',
                'hints' => [
                    'a1' => 'Present Perfect Continuous: have/has + been + V-ing.',
                    'a2' => 'Been обов\'язковий для Perfect Continuous.',
                ],
                'explanations' => [
                    'have' => '✅ Правильно! Have з you для Perfect Continuous.',
                    'has' => '❌ Неправильно. Has для he/she/it.',
                    'are' => '❌ Неправильно. Are для Present Continuous.',
                    'were' => '❌ Неправильно. Were для Past Continuous.',
                    'been' => '✅ Правильно! Have + been + V-ing.',
                    'be' => '❌ Неправильно. Потрібна форма been.',
                    'being' => '❌ Неправильно. Being не підходить.',
                    'was' => '❌ Неправильно. Was — Past Simple.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => "They {a1} arrived yet, {a2} they?",
                'answers' => ['a1' => "haven't", 'a2' => 'have'],
                'options' => [
                    'a1' => ["haven't", "didn't", "don't", "weren't"],
                    'a2' => ['have', "haven't", 'did', "didn't"],
                ],
                'verb_hints' => ['a1' => 'negative perfect', 'a2' => 'positive tag'],
                'detail' => 'tag_questions',
                'hints' => [
                    'a1' => "Haven't — заперечення в Present Perfect.",
                    'a2' => 'Після заперечення — ствердний tag.',
                ],
                'explanations' => [
                    "haven't" => '✅ Правильно! Present Perfect заперечення.',
                    "didn't" => '❌ Неправильно. Past Simple.',
                    "don't" => '❌ Неправильно. Present Simple.',
                    "weren't" => '❌ Неправильно. Past to be.',
                    'have' => '✅ Правильно! Ствердний tag після заперечення.',
                    "haven't" => '❌ Неправильно. Після заперечення — ствердний tag.',
                    'did' => '❌ Неправильно. Did не відповідає haven\'t.',
                    "didn't" => '❌ Неправильно. Didn\'t не відповідає haven\'t.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'I wonder where she {a1} and what she {a2}.',
                'answers' => ['a1' => 'is', 'a2' => 'wants'],
                'options' => [
                    'a1' => ['is', 'does', 'was', 'did'],
                    'a2' => ['wants', 'want', 'wanting', 'wanted'],
                ],
                'verb_hints' => ['a1' => 'statement order', 'a2' => 'present simple 3rd person'],
                'detail' => 'indirect_questions',
                'hints' => [
                    'a1' => 'Непряме питання: прямий порядок слів.',
                    'a2' => 'She wants — третя особа однини.',
                ],
                'explanations' => [
                    'is' => '✅ Правильно! Where she is — прямий порядок.',
                    'does' => '❌ Неправильно. Does для питань.',
                    'was' => '❌ Неправильно. Контекст теперішній.',
                    'did' => '❌ Неправильно. Did для минулого.',
                    'wants' => '✅ Правильно! She wants — 3-я особа.',
                    'want' => '❌ Неправильно. Потрібна -s для she.',
                    'wanting' => '❌ Неправильно. -ing не потрібна.',
                    'wanted' => '❌ Неправильно. Минулий час.',
                ],
            ],

            // ===== B2 Level: 4 questions =====
            [
                'level' => 'B2',
                'question' => '{a1} it not be better if we {a2} them first?',
                'answers' => ['a1' => 'Would', 'a2' => 'asked'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Should', 'Could'],
                    'a2' => ['asked', 'ask', 'asking', 'have asked'],
                ],
                'verb_hints' => ['a1' => 'negative question modal', 'a2' => 'past simple in conditional'],
                'detail' => 'negative_questions',
                'hints' => [
                    'a1' => "Would для гіпотетичних пропозицій.",
                    'a2' => 'If + Past Simple для нереальної умови.',
                ],
                'explanations' => [
                    'Would' => '✅ Правильно! Would для гіпотези.',
                    'Will' => '❌ Неправильно. Will для реальних ситуацій.',
                    'Should' => '❌ Неправильно. Should для порад.',
                    'Could' => '❌ Неправильно. Could можливий, але would краще.',
                    'asked' => '✅ Правильно! Past Simple в if-clause.',
                    'ask' => '❌ Неправильно. Потрібен Past Simple.',
                    'asking' => '❌ Неправильно. -ing не підходить.',
                    'have asked' => '❌ Неправильно. Perfect тут не потрібен.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'Do you prefer tea {a1} coffee, or {a2} you like both?',
                'answers' => ['a1' => 'or', 'a2' => 'do'],
                'options' => [
                    'a1' => ['or', 'and', 'but', 'nor'],
                    'a2' => ['do', 'does', 'are', 'would'],
                ],
                'verb_hints' => ['a1' => 'alternative choice', 'a2' => 'auxiliary for you'],
                'detail' => 'alternative_questions',
                'hints' => [
                    'a1' => 'Or для вибору між варіантами.',
                    'a2' => 'Do you — питання з you.',
                ],
                'explanations' => [
                    'or' => '✅ Правильно! Or для альтернативи.',
                    'and' => '❌ Неправильно. And не для вибору.',
                    'but' => '❌ Неправильно. But для протиставлення.',
                    'nor' => '❌ Неправильно. Nor для заперечення.',
                    'do' => '✅ Правильно! Do з you.',
                    'does' => '❌ Неправильно. Does для he/she/it.',
                    'are' => '❌ Неправильно. Are для to be.',
                    'would' => '❌ Неправильно. Would тут не підходить.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'Who {a1} the idea and who {a2} the final decision?',
                'answers' => ['a1' => 'suggested', 'a2' => 'made'],
                'options' => [
                    'a1' => ['suggested', 'did suggest', 'suggest', 'has suggested'],
                    'a2' => ['made', 'did make', 'make', 'has made'],
                ],
                'verb_hints' => ['a1' => 'subject question past', 'a2' => 'subject question past'],
                'detail' => 'subject_questions',
                'hints' => [
                    'a1' => 'У питаннях про підмет — V2 без did.',
                    'a2' => 'Те саме правило: V2 без did.',
                ],
                'explanations' => [
                    'suggested' => '✅ Правильно! V2 без допоміжного.',
                    'did suggest' => '❌ Неправильно. Did не потрібен.',
                    'suggest' => '❌ Неправильно. Потрібен Past Simple.',
                    'has suggested' => '❌ Неправильно. Perfect не підходить.',
                    'made' => '✅ Правильно! V2 без допоміжного.',
                    'did make' => '❌ Неправильно. Did не потрібен.',
                    'make' => '❌ Неправильно. Потрібен Past Simple.',
                    'has made' => '❌ Неправильно. Perfect не підходить.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'Could you clarify what we {a1} do and when we {a2} start?',
                'answers' => ['a1' => 'should', 'a2' => 'should'],
                'options' => [
                    'a1' => ['should', 'shall', 'will', 'must'],
                    'a2' => ['should', 'shall', 'will', 'can'],
                ],
                'verb_hints' => ['a1' => 'modal for recommendation', 'a2' => 'modal in indirect'],
                'detail' => 'indirect_questions',
                'hints' => [
                    'a1' => 'Should для рекомендацій.',
                    'a2' => 'Непряме питання зберігає модальне дієслово.',
                ],
                'explanations' => [
                    'should' => '✅ Правильно! Should для порад.',
                    'shall' => '❌ Неправильно. Shall більш формальний.',
                    'will' => '❌ Неправильно. Will для фактів.',
                    'must' => '❌ Неправильно. Must занадто сильний.',
                    'can' => '❌ Неправильно. Can для здатності.',
                ],
            ],

            // ===== C1 Level: 4 questions =====
            [
                'level' => 'C1',
                'question' => '{a1} the committee not {a2} consulted earlier, {a3} they?',
                'answers' => ['a1' => 'Should', 'a2' => 'have been', 'a3' => "shouldn't"],
                'options' => [
                    'a1' => ['Should', 'Would', 'Could', 'Must'],
                    'a2' => ['have been', 'be', 'been', 'have'],
                    'a3' => ["shouldn't", 'should', "wouldn't", 'would'],
                ],
                'verb_hints' => ['a1' => 'modal past criticism', 'a2' => 'perfect passive', 'a3' => 'negative tag'],
                'detail' => 'tag_questions',
                'hints' => [
                    'a1' => 'Should have been — критика минулої бездіяльності.',
                    'a2' => 'Have been для Perfect Passive.',
                    'a3' => "Після ствердного should — заперечний shouldn't.",
                ],
                'explanations' => [
                    'Should' => '✅ Правильно! Should have been — критика.',
                    'Would' => '❌ Неправильно. Would для гіпотез.',
                    'Could' => '❌ Неправильно. Could для можливості.',
                    'Must' => '❌ Неправильно. Must для обов\'язку.',
                    'have been' => '✅ Правильно! Perfect Passive.',
                    'be' => '❌ Неправильно. Потрібна Perfect форма.',
                    'been' => '❌ Неправильно. Потрібен have.',
                    'have' => '❌ Неправильно. Потрібен been.',
                    "shouldn't" => '✅ Правильно! Заперечний tag.',
                    'should' => '❌ Неправильно. Потрібен заперечний tag.',
                    "wouldn't" => '❌ Неправильно. Має відповідати should.',
                    'would' => '❌ Неправильно. Має відповідати should.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => '{a1} exactly {a2} the failure and how might we {a3} it?',
                'answers' => ['a1' => 'What', 'a2' => 'caused', 'a3' => 'prevent'],
                'options' => [
                    'a1' => ['What', 'Who', 'Which', 'Why'],
                    'a2' => ['caused', 'did cause', 'has caused', 'causing'],
                    'a3' => ['prevent', 'preventing', 'prevented', 'have prevented'],
                ],
                'verb_hints' => ['a1' => 'subject question word', 'a2' => 'past without auxiliary', 'a3' => 'base form after might'],
                'detail' => 'subject_questions',
                'hints' => [
                    'a1' => 'What caused — питання про причину (підмет).',
                    'a2' => 'У питаннях про підмет — V2 без did.',
                    'a3' => 'Після might — базова форма.',
                ],
                'explanations' => [
                    'What' => '✅ Правильно! What для речі/причини.',
                    'Who' => '❌ Неправильно. Who для людей.',
                    'Which' => '❌ Неправильно. Which для вибору.',
                    'Why' => '❌ Неправильно. Why не підходить як підмет.',
                    'caused' => '✅ Правильно! V2 без did.',
                    'did cause' => '❌ Неправильно. Did не потрібен.',
                    'has caused' => '❌ Неправильно. Perfect не підходить.',
                    'causing' => '❌ Неправильно. -ing не підходить.',
                    'prevent' => '✅ Правильно! Базова форма після might.',
                    'preventing' => '❌ Неправильно. -ing не потрібна.',
                    'prevented' => '❌ Неправильно. Past не потрібен.',
                    'have prevented' => '❌ Неправильно. Perfect не потрібен.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => "I'm uncertain as to whether they {a1} {a2} the implications fully.",
                'answers' => ['a1' => 'have', 'a2' => 'understood'],
                'options' => [
                    'a1' => ['have', 'had', 'has', 'having'],
                    'a2' => ['understood', 'understand', 'understanding', 'understands'],
                ],
                'verb_hints' => ['a1' => 'present perfect auxiliary', 'a2' => 'past participle'],
                'detail' => 'indirect_questions',
                'hints' => [
                    'a1' => 'They have — Present Perfect з they.',
                    'a2' => 'Understood — V3 для Present Perfect.',
                ],
                'explanations' => [
                    'have' => '✅ Правильно! Have з they.',
                    'had' => '❌ Неправильно. Had для Past Perfect.',
                    'has' => '❌ Неправильно. Has для he/she/it.',
                    'having' => '❌ Неправильно. -ing тут не підходить.',
                    'understood' => '✅ Правильно! V3 для Perfect.',
                    'understand' => '❌ Неправильно. Потрібна V3.',
                    'understanding' => '❌ Неправильно. -ing не підходить.',
                    'understands' => '❌ Неправильно. -s не підходить.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => '{a1} the regulations {a2} implemented properly, or {a3} there loopholes?',
                'answers' => ['a1' => 'Were', 'a2' => 'being', 'a3' => 'were'],
                'options' => [
                    'a1' => ['Were', 'Was', 'Are', 'Is'],
                    'a2' => ['being', 'been', 'be', 'to be'],
                    'a3' => ['were', 'was', 'are', 'is'],
                ],
                'verb_hints' => ['a1' => 'past passive question', 'a2' => 'continuous passive', 'a3' => 'past plural'],
                'detail' => 'alternative_questions',
                'hints' => [
                    'a1' => 'Were для Past Passive з множиною.',
                    'a2' => 'Being implemented — Past Continuous Passive.',
                    'a3' => 'Were there — існування в минулому.',
                ],
                'explanations' => [
                    'Were' => '✅ Правильно! Were для множини.',
                    'Was' => '❌ Неправильно. Was для однини.',
                    'Are' => '❌ Неправильно. Are для теперішнього.',
                    'Is' => '❌ Неправильно. Is для теперішнього.',
                    'being' => '✅ Правильно! Being для Continuous Passive.',
                    'been' => '❌ Неправильно. Been для Perfect.',
                    'be' => '❌ Неправильно. Потрібна being.',
                    'to be' => '❌ Неправильно. Інфінітив не підходить.',
                    'were' => '✅ Правильно! Were there для множини.',
                    'was' => '❌ Неправильно. Was для однини.',
                    'are' => '❌ Неправильно. Are для теперішнього.',
                    'is' => '❌ Неправильно. Is для теперішнього.',
                ],
            ],

            // ===== C2 Level: 4 questions =====
            [
                'level' => 'C2',
                'question' => '{a1} the epistemological foundations {a2} reconsidered, {a3} they not?',
                'answers' => ['a1' => 'Ought', 'a2' => 'to be', 'a3' => 'ought'],
                'options' => [
                    'a1' => ['Ought', 'Should', 'Must', 'Would'],
                    'a2' => ['to be', 'be', 'being', 'been'],
                    'a3' => ['ought', "oughtn't", 'should', "shouldn't"],
                ],
                'verb_hints' => ['a1' => 'formal modal', 'a2' => 'infinitive after ought', 'a3' => 'positive tag'],
                'detail' => 'tag_questions',
                'hints' => [
                    'a1' => 'Ought to — формальна рекомендація.',
                    'a2' => 'Після ought завжди to + infinitive.',
                    'a3' => "Після заперечного 'not' — ствердний tag.",
                ],
                'explanations' => [
                    'Ought' => '✅ Правильно! Ought to — формальний стиль.',
                    'Should' => '❌ Неправильно. Should менш формальний.',
                    'Must' => '❌ Неправильно. Must для обов\'язку.',
                    'Would' => '❌ Неправильно. Would для гіпотез.',
                    'to be' => '✅ Правильно! Ought to be.',
                    'be' => '❌ Неправильно. Потрібен to.',
                    'being' => '❌ Неправильно. -ing не підходить.',
                    'been' => '❌ Неправильно. Been для Perfect.',
                    'ought' => '✅ Правильно! Ствердний tag.',
                    "oughtn't" => '❌ Неправильно. Після заперечення — ствердний.',
                    'should' => '❌ Неправильно. Tag має відповідати ought.',
                    "shouldn't" => '❌ Неправильно. Tag має відповідати ought.',
                ],
            ],
            [
                'level' => 'C2',
                'question' => '{a1} {a2} that precipitated the paradigm shift and {a3} might its ramifications be?',
                'answers' => ['a1' => 'What', 'a2' => 'was it', 'a3' => 'what'],
                'options' => [
                    'a1' => ['What', 'Which', 'Who', 'Why'],
                    'a2' => ['was it', 'it was', 'were it', 'it were'],
                    'a3' => ['what', 'which', 'how', 'why'],
                ],
                'verb_hints' => ['a1' => 'thing question word', 'a2' => 'cleft sentence', 'a3' => 'thing question'],
                'detail' => 'wh_questions',
                'hints' => [
                    'a1' => 'What для питання про річ/подію.',
                    'a2' => 'Was it — cleft sentence (It was X that...).',
                    'a3' => 'What might its ramifications be — питання про результати.',
                ],
                'explanations' => [
                    'What' => '✅ Правильно! What для речі.',
                    'Which' => '❌ Неправильно. Which для вибору.',
                    'Who' => '❌ Неправильно. Who для людей.',
                    'Why' => '❌ Неправильно. Why для причини.',
                    'was it' => '✅ Правильно! Cleft sentence.',
                    'it was' => '❌ Неправильно. Потрібна інверсія.',
                    'were it' => '❌ Неправильно. It — однина.',
                    'it were' => '❌ Неправильно. Неправильний порядок.',
                    'what' => '✅ Правильно! Питання про результати.',
                    'which' => '❌ Неправильно. Which для вибору.',
                    'how' => '❌ Неправильно. How для способу.',
                    'why' => '❌ Неправильно. Why для причини.',
                ],
            ],
            [
                'level' => 'C2',
                'question' => 'One might inquire whether the methodology {a1} {a2} appropriately vetted.',
                'answers' => ['a1' => 'had', 'a2' => 'been'],
                'options' => [
                    'a1' => ['had', 'has', 'have', 'having'],
                    'a2' => ['been', 'be', 'being', 'was'],
                ],
                'verb_hints' => ['a1' => 'past perfect auxiliary', 'a2' => 'past participle passive'],
                'detail' => 'indirect_questions',
                'hints' => [
                    'a1' => 'Had been — Past Perfect Passive.',
                    'a2' => 'Been vetted — Passive Voice.',
                ],
                'explanations' => [
                    'had' => '✅ Правильно! Had для Past Perfect.',
                    'has' => '❌ Неправильно. Has для Present Perfect.',
                    'have' => '❌ Неправильно. Have не підходить.',
                    'having' => '❌ Неправильно. -ing не підходить.',
                    'been' => '✅ Правильно! Been для Passive.',
                    'be' => '❌ Неправильно. Потрібен been.',
                    'being' => '❌ Неправильно. Being для Continuous.',
                    'was' => '❌ Неправильно. Was не підходить тут.',
                ],
            ],
            [
                'level' => 'C2',
                'question' => '{a1} it the case that the hypothesis {a2} {a3} substantiated, or {a4} there alternative explanations?',
                'answers' => ['a1' => 'Is', 'a2' => 'has been', 'a3' => 'adequately', 'a4' => 'are'],
                'options' => [
                    'a1' => ['Is', 'Was', 'Are', 'Were'],
                    'a2' => ['has been', 'had been', 'have been', 'was'],
                    'a3' => ['adequately', 'adequate', 'adequacy', 'adequating'],
                    'a4' => ['are', 'is', 'was', 'were'],
                ],
                'verb_hints' => ['a1' => 'present question', 'a2' => 'present perfect passive', 'a3' => 'adverb', 'a4' => 'plural existence'],
                'detail' => 'alternative_questions',
                'hints' => [
                    'a1' => 'Is it the case that — формальна конструкція.',
                    'a2' => 'Has been substantiated — Present Perfect Passive.',
                    'a3' => 'Adequately — прислівник перед дієприкметником.',
                    'a4' => 'Are there — існування множини.',
                ],
                'explanations' => [
                    'Is' => '✅ Правильно! Is it the case.',
                    'Was' => '❌ Неправильно. Контекст теперішній.',
                    'Are' => '❌ Неправильно. It — однина.',
                    'Were' => '❌ Неправильно. Минулий час.',
                    'has been' => '✅ Правильно! Present Perfect Passive.',
                    'had been' => '❌ Неправильно. Past Perfect.',
                    'have been' => '❌ Неправильно. Hypothesis — однина.',
                    'was' => '❌ Неправильно. Past Simple.',
                    'adequately' => '✅ Правильно! Прислівник.',
                    'adequate' => '❌ Неправильно. Потрібен прислівник.',
                    'adequacy' => '❌ Неправильно. Іменник.',
                    'adequating' => '❌ Неправильно. Такого слова немає.',
                    'are' => '✅ Правильно! Are there для множини.',
                    'is' => '❌ Неправильно. Explanations — множина.',
                    'was' => '❌ Неправильно. Минулий час.',
                    'were' => '❌ Неправильно. Минулий час.',
                ],
            ],
        ];
    }
}
