<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use App\Support\Database\Seeder;
use RuntimeException;

class PageTagAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $categoryTags = [
            'some-any' => [
                'Some or Any',
                'Some/Any/No/Every Compounds (AI)',
                'Something / Anything / Nothing Exercises',
            ],
            'articles-and-quantifiers' => [
                'A An The',
                'A or An',
                'Quantity Comparisons (much/many/less/fewer)',
            ],
            'demonstratives' => [
                'this_that_these_those',
                'this_that_these_those_exercise_2',
                'this_that_these_those_exercise_3',
            ],
            'possession' => [
                'have_has_got',
                'have_has_got_exercise_2',
                'have_has_got_exercise_3',
            ],
            'pronouns' => [
                'Gap-fill Pronoun Forms (AI)',
                'Indefinite Pronoun Compounds',
                'Indefinite Pronouns Practice',
                'Indefinite Pronouns Practice AI',
            ],
            'sentence-structures' => [
                'Contractions',
                'do_does_is_are',
                'There is',
                'There are',
                'There is/There are',
                'to_be_tense',
            ],
            'modal-verbs' => [
                'Modal Verbs',
            ],
            'questions' => [
                'question_words_drag_drop',
                'short_answers',
            ],
            'adjectives' => [
                'Degrees of Comparison',
                'Irregular Comparative Forms (good/bad/far)',
            ],
            'conditions' => [
                'Zero Conditional',
                'First Conditional',
                'First Conditional Practice',
                'Second Conditional',
                'Second Conditional Sentence Completion',
                'Third Conditional',
                'Third Conditional Practice',
                'Mixed Conditional Sentence Creation',
                'Conditional Sentences (Zero to Second)',
                'Conditional Sentences (Type 1 & 2)',
            ],
            'tenses' => [
                'revision_tenses_full',
                'continuous_tenses_worksheet',
                'Tense: Past',
                'Tense: Present',
                'Tense: Future',
            ],
        ];

        $pageTags = [
            'some-any-people' => [
                'Some or Any',
                'Some/Any/No/Every Compounds (AI)',
                'Something / Anything / Nothing Exercises',
            ],
            'some-any-places' => [
                'Some or Any',
                'Some/Any/No/Every Compounds (AI)',
                'Something / Anything / Nothing Exercises',
            ],
            'some-any-things' => [
                'Some or Any',
                'Some/Any/No/Every Compounds (AI)',
                'Something / Anything / Nothing Exercises',
            ],
            'a-an-the' => [
                'A An The',
                'A or An',
            ],
            'quantifiers' => [
                'Quantity Comparisons (much/many/less/fewer)',
            ],
            'some-any' => [
                'Some or Any',
                'Some/Any/No/Every Compounds (AI)',
                'Something / Anything / Nothing Exercises',
            ],
            'this-that-these-those' => [
                'this_that_these_those',
                'this_that_these_those_exercise_2',
                'this_that_these_those_exercise_3',
            ],
            'have-got' => [
                'have_has_got',
                'have_has_got_exercise_2',
                'have_has_got_exercise_3',
            ],
            'pronouns' => [
                'Gap-fill Pronoun Forms (AI)',
                'Indefinite Pronoun Compounds',
                'Indefinite Pronouns Practice',
                'Indefinite Pronouns Practice AI',
            ],
            'contractions-short-forms' => [
                'Contractions',
            ],
            'do-does-is-are' => [
                'do_does_is_are',
            ],
            'there-is-there-are' => [
                'There is',
                'There are',
                'There is/There are',
                'There is there are',
                'There were',
                "There aren't",
                "There aren't any",
                "There isn't",
                'Is there',
                'Are there',
            ],
            'verb-to-be' => [
                'to_be_tense',
            ],
            'modal-verbs-can-could' => [
                'Modal: Can / Could',
                'can_cant_ability_exercise_3',
            ],
            'modal-verbs-may-might' => [
                'Modal: May / Might',
            ],
            'modal-verbs-must-have-to' => [
                'Modal: Must / Have to',
            ],
            'modal-verbs-need-need-to' => [
                'Modal: Need / Need to',
            ],
            'modal-verbs-perfect-modals' => [
                'Modal Deduction (Past)',
                'Modal Past Possibility',
            ],
            'modal-verbs-should-ought-to' => [
                'Modal: Should / Ought to',
            ],
            'question-forms' => [
                'question_words_drag_drop',
            ],
            'short-answers' => [
                'short_answers',
                'present_continuous_short_answers',
            ],
            'degrees-of-comparison' => [
                'Degrees of Comparison',
                'Irregular Comparative Forms (good/bad/far)',
            ],
            'first-conditional' => [
                'First Conditional',
                'First Conditional Practice',
            ],
            'mixed-conditional' => [
                'Mixed Conditional Sentence Creation',
            ],
            'second-conditional' => [
                'Second Conditional',
                'Second Conditional Sentence Completion',
            ],
            'third-conditional' => [
                'Third Conditional',
                'Third Conditional Practice',
            ],
            'zero-conditional' => [
                'Zero Conditional',
            ],
            'future-continuous' => [
                'future_perfect_vs_future_continuous_exercise_1',
                'future_simple_or_future_continuous',
                'future_simple_future_continuous_future_perfect',
            ],
            'future-perfect' => [
                'future_perfect_vs_future_continuous_exercise_1',
                'future_simple_future_continuous_future_perfect',
            ],
            'future-perfect-continuous' => [
                'future_perfect_vs_future_continuous_exercise_1',
                'future_simple_future_continuous_future_perfect',
            ],
            'future-simple' => [
                'future_simple_test_1',
                'future_simple_or_future_continuous',
            ],
            'past-continuous' => [
                'past_continuous_tense_test',
                'past_simple_or_past_continuous_test',
                'past_simple_continuous_image_test',
                'past_simple_continuous_sentences_test',
            ],
            'past-perfect' => [
                'By the Time Prior Completion',
                'Completed Before Another Event',
                'Duration Before Another Event',
                'Sequencing with By the Time',
            ],
            'past-perfect-continuous' => [
                'Duration Before Another Event',
                'By the Time Prior Completion',
            ],
            'past-simple' => [
                'grammar_quiz_past_simple',
                'past_simple_regular',
                'past_simple_regular_verbs_full',
                'past_simple_continuous_image_test',
                'past_simple_continuous_sentences_test',
            ],
            'present-continuous' => [
                'present_continuous_dialogue',
                'present_continuous_short_forms',
                'present_continuous_short_answers',
                'present_continuous_past_simple_test',
            ],
            'present-perfect' => [
                'present_perfect_dialogue',
                'present_perfect_result_or_side_effect',
                'present_perfect_since_last_time',
            ],
            'present-perfect-continuous' => [
                'present_perfect_result_or_side_effect',
                'present_perfect_since_last_time',
            ],
            'present-simple' => [
                'present_simple',
                'present_simple_exercises',
                'quiz_present_simple',
            ],
        ];

        $this->attachCategoryTags($categoryTags);
        $this->attachPageTags($pageTags);
    }

    protected function attachCategoryTags(array $categoryTags): void
    {
        foreach ($categoryTags as $slug => $tagNames) {
            $category = PageCategory::where('slug', $slug)->first();

            if (!$category) {
                throw new RuntimeException("Page category with slug {$slug} not found.");
            }

            $tags = $this->resolveTags($tagNames);
            $category->tags()->syncWithoutDetaching($tags);
        }
    }

    protected function attachPageTags(array $pageTags): void
    {
        foreach ($pageTags as $slug => $tagNames) {
            $page = Page::where('slug', $slug)->first();

            if (!$page) {
                throw new RuntimeException("Page with slug {$slug} not found.");
            }

            $tags = $this->resolveTags($tagNames);
            $page->tags()->syncWithoutDetaching($tags);
        }
    }

    protected function resolveTags(array $tagNames): array
    {
        $tags = Tag::whereIn('name', $tagNames)->pluck('id', 'name');

        $missingTags = array_diff($tagNames, $tags->keys()->all());

        if (!empty($missingTags)) {
            throw new RuntimeException('Missing tags: ' . implode(', ', $missingTags));
        }

        return $tags->values()->all();
    }
}
