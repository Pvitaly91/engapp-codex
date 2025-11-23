<?php

namespace Database\Seeders\chatGpt;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use App\Support\Database\Seeder;

class GptPageTagAssignmentSeeder extends Seeder
{
    private const CATEGORY_TAGS = [
        'tenses' => [
            'Grammar theory essentials',
            'Verb tense overview',
            'Time reference guide',
        ],
        'conditions' => [
            'Conditional sentences theory',
            'If-clause building blocks',
            'Hypothetical outcome focus',
        ],
        'modal-verbs' => [
            'Modal verbs overview',
            'Degrees of certainty',
            'Obligation and permission theory',
        ],
        'articles-and-quantifiers' => [
            'Articles theory basics',
            'Countability and quantifiers',
            'Determiner placement rules',
        ],
        'some-any' => [
            'Some/Any overview',
            'Indefinite compounds primer',
            'Positive vs negative choice',
        ],
        'demonstratives' => [
            'Demonstratives overview',
            'Near vs far reference',
            'This/That/These/Those signals',
        ],
        'possession' => [
            'Possession structures',
            'Have got theory',
            'Ownership expressions',
        ],
        'verbs' => [
            'Irregular verbs overview',
            'Past form patterns',
            'Common irregular sets',
        ],
        'pronouns' => [
            'Pronoun system overview',
            'Indefinite pronouns theory',
            'Reference words practice',
        ],
        'sentence-structures' => [
            'Sentence building blocks',
            'Auxiliary support theory',
            'Word order essentials',
        ],
        'translation' => [
            'Translation strategies',
            'Contextual equivalence',
            'Interference avoidance',
        ],
        'questions' => [
            'Question formation overview',
            'Inversion patterns',
            'Short answer rules',
        ],
        'adjectives' => [
            'Adjective grammar basics',
            'Comparison system overview',
            'Modifiers and order',
        ],
        'vocabulary' => [
            'Vocabulary growth strategy',
            'Lexical family focus',
            'Context-based learning',
        ],
    ];

    private const PAGE_TAGS = [
        'present-simple' => [
            'Present Simple theory',
            'Habitual action rules',
            'Do/Does auxiliary use',
        ],
        'present-continuous' => [
            'Present Continuous theory',
            'Action in progress forms',
            'Be plus verb-ing rules',
        ],
        'present-perfect' => [
            'Present Perfect theory',
            'Since/for timeline rules',
            'Result vs experience focus',
        ],
        'present-perfect-continuous' => [
            'Present Perfect Continuous duration',
            'Unfinished action focus',
            'Since/for with progress',
        ],
        'past-simple' => [
            'Past Simple theory',
            'Finished past markers',
            'Did auxiliary structure',
        ],
        'past-continuous' => [
            'Past Continuous background actions',
            'Interrupted past events',
            'Was/Were plus verb-ing form',
        ],
        'past-perfect' => [
            'Past Perfect sequence theory',
            'Earlier-than-past reference',
            'Had plus past participle rules',
        ],
        'past-perfect-continuous' => [
            'Past Perfect Continuous duration',
            'Cause and effect in narratives',
            'Had been plus verb-ing',
        ],
        'future-simple' => [
            'Future Simple will rules',
            'Predictions and promises',
            'Spontaneous decision cues',
        ],
        'future-continuous' => [
            'Future Continuous ongoing actions',
            'Specific time in future focus',
            'Will be plus verb-ing',
        ],
        'future-perfect' => [
            'Future Perfect completion',
            'Will have plus past participle',
            'Deadlines in future theory',
        ],
        'future-perfect-continuous' => [
            'Future Perfect Continuous duration',
            'Progress before future point',
            'Will have been plus verb-ing',
        ],
        'zero-conditional' => [
            'Zero Conditional facts',
            'Present plus present pattern',
            'Result of general truths',
        ],
        'first-conditional' => [
            'First Conditional predictions',
            'Present plus will pattern',
            'Real future conditions',
        ],
        'second-conditional' => [
            'Second Conditional hypotheticals',
            'Past plus would pattern',
            'Unreal present situations',
        ],
        'third-conditional' => [
            'Third Conditional regrets',
            'Past perfect plus would have pattern',
            'Unreal past outcomes',
        ],
        'mixed-conditional' => [
            'Mixed Conditional theory',
            'Time shift cause and result',
            'Present result of past cause',
        ],
        'modal-verbs-can-could' => [
            'Ability and permission theory',
            'Can versus could nuance',
            'Polite request patterns',
        ],
        'modal-verbs-may-might' => [
            'Possibility and probability ranges',
            'May versus might subtlety',
            'Formal permission with may',
        ],
        'modal-verbs-must-have-to' => [
            'Obligation versus necessity',
            'Must versus have to theory',
            'Prohibition with must not',
        ],
        'modal-verbs-need-need-to' => [
            'Need versus need to difference',
            'Negative forms needn\'t',
            'Nuanced necessity',
        ],
        'modal-verbs-perfect-modals' => [
            'Perfect modal structures',
            'Past deduction patterns',
            'Expectations versus reality',
        ],
        'modal-verbs-should-ought-to' => [
            'Advice and recommendation theory',
            'Should versus ought to nuance',
            'Soft obligation forms',
        ],
        'a-an-the' => [
            'Articles overview theory',
            'Definite versus indefinite rules',
            'Zero article situations',
        ],
        'quantifiers' => [
            'Much and many rules',
            'Countable versus uncountable focus',
            'Quantifier placement',
        ],
        'some-any' => [
            'Some versus any usage',
            'Positive negative and questions',
            'Compound pronouns focus',
        ],
        'some-any-people' => [
            'Someone anyone no one forms',
            'People-focused compounds',
            'Affirmative versus negative reference',
        ],
        'some-any-places' => [
            'Somewhere anywhere nowhere forms',
            'Places-focused compounds',
            'Location reference nuance',
        ],
        'some-any-things' => [
            'Something anything nothing forms',
            'Things-focused compounds',
            'Object reference nuance',
        ],
        'this-that-these-those' => [
            'Distance and number contrast',
            'Demonstratives for objects',
            'Pointer words in context',
        ],
        'have-got' => [
            'Have got possession',
            'Affirmative and negative patterns',
            'Short answer forms with have',
        ],
        'irregular-verbs' => [
            'Irregular verb patterns',
            'Past and participle pairing',
            'Grouping irregular families',
        ],
        'pronouns' => [
            'Indefinite pronoun system',
            'Compound pronoun formation',
            'Reference without nouns',
        ],
        'contractions-short-forms' => [
            'Common contractions list',
            'Formal versus informal choice',
            'Apostrophe spelling rules',
        ],
        'do-does-is-are' => [
            'Auxiliary selection theory',
            'Do support versus be',
            'Question and negative support',
        ],
        'there-is-there-are' => [
            'Existential there structure',
            'Singular plural agreement',
            'Positive negative question patterns',
        ],
        'verb-to-be' => [
            'Be forms across tenses',
            'Statements questions negatives with be',
            'Linking verb usage',
        ],
        'translation-techniques' => [
            'Equivalence versus literal choice',
            'Register and tone matching',
            'Cultural adaptation tips',
        ],
        'question-forms' => [
            'Wh-question formation',
            'Inversion with auxiliaries',
            'Question word order',
        ],
        'short-answers' => [
            'Short answer structure',
            'Echoing auxiliary choice',
            'Affirmative versus negative replies',
        ],
        'degrees-of-comparison' => [
            'Comparative formation rules',
            'Superlative endings',
            'Irregular adjective list',
        ],
        'vocabulary-building' => [
            'Vocabulary acquisition plans',
            'Spaced repetition focus',
            'Topic-based word sets',
        ],
    ];

    public function run(): void
    {
        $tagCache = [];

        $this->assignCategoryTags($tagCache);
        $this->assignPageTags($tagCache);
    }

    private function assignCategoryTags(array &$tagCache): void
    {
        foreach (self::CATEGORY_TAGS as $categorySlug => $tagNames) {
            $category = PageCategory::where('slug', $categorySlug)->first();

            if (!$category) {
                continue;
            }

            $category->tags()->syncWithoutDetaching($this->getTagIds($tagNames, $tagCache));
        }
    }

    private function assignPageTags(array &$tagCache): void
    {
        foreach (self::PAGE_TAGS as $pageSlug => $tagNames) {
            $page = Page::where('slug', $pageSlug)->first();

            if (!$page) {
                continue;
            }

            $page->tags()->syncWithoutDetaching($this->getTagIds($tagNames, $tagCache));
        }
    }

    private function getTagIds(array $tagNames, array &$tagCache): array
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            if (!isset($tagCache[$tagName])) {
                $tagCache[$tagName] = Tag::firstOrCreate(
                    ['name' => $tagName],
                    ['category' => 'Theory']
                )->id;
            }

            $tagIds[] = $tagCache[$tagName];
        }

        return array_unique($tagIds);
    }
}
