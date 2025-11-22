<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PageTagAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('Starting PageTagAssignmentSeeder');

        $tagsData = json_decode(file_get_contents(config_path('tags/exported_tags.json')), true);
        $pagesData = json_decode(file_get_contents(config_path('pages/exported_pages.json')), true);

        if (!$tagsData || !$pagesData) {
            Log::error('Failed to load tags or pages data');
            return;
        }

        // Index tags by name and ID for quick lookup
        $tagsByName = [];
        $tagsById = [];
        
        foreach ($tagsData['categories'] as $tagCategory) {
            foreach ($tagCategory['tags'] as $tagData) {
                $tagName = $tagData['name'];
                $tagId = $tagData['id'];
                $tagsByName[strtolower($tagName)] = $tagId;
                $tagsById[$tagId] = $tagName;
            }
        }

        Log::info('Loaded ' . count($tagsByName) . ' tags');

        // Process each category and its pages
        foreach ($pagesData['categories'] as $categoryData) {
            $categoryId = $categoryData['category_id'];
            $categoryTitle = $categoryData['category_title'];
            $categorySlug = $categoryData['category_slug'];

            $pageCategory = PageCategory::find($categoryId);
            if (!$pageCategory) {
                Log::warning("Category not found: {$categoryId} - {$categoryTitle}");
                continue;
            }

            // Assign tags to category
            $categoryTags = $this->findMatchingTags($categoryTitle, $categorySlug, $tagsByName);
            if (!empty($categoryTags)) {
                $pageCategory->tags()->syncWithoutDetaching($categoryTags);
                Log::info("Assigned " . count($categoryTags) . " tags to category: {$categoryTitle}");
            }

            // Process pages in this category
            foreach ($categoryData['pages'] as $pageData) {
                $pageId = $pageData['page_id'];
                $pageTitle = $pageData['page_title'];
                $pageSlug = $pageData['page_slug'];

                $page = Page::find($pageId);
                if (!$page) {
                    Log::warning("Page not found: {$pageId} - {$pageTitle}");
                    continue;
                }

                // Assign tags to page
                $pageTags = $this->findMatchingTags($pageTitle, $pageSlug, $tagsByName);
                if (!empty($pageTags)) {
                    $page->tags()->syncWithoutDetaching($pageTags);
                    Log::info("Assigned " . count($pageTags) . " tags to page: {$pageTitle}");
                }
            }
        }

        Log::info('Completed PageTagAssignmentSeeder');
    }

    /**
     * Find matching tags for a given title and slug
     * 
     * @param string $title The title of the page or category
     * @param string $slug The slug of the page or category
     * @param array $tagsByName Associative array of tag names (lowercase) to tag IDs
     * @return array Array of unique tag IDs
     */
    private function findMatchingTags(string $title, string $slug, array $tagsByName): array
    {
        $matchedTagIds = [];
        $titleLower = mb_strtolower($title);
        $slugLower = strtolower($slug);

        // Direct mappings for specific pages to tags
        $directMappings = $this->getDirectMappings();
        
        // Check if we have a direct mapping (preferred for specific pages)
        if (isset($directMappings[$slugLower])) {
            foreach ($directMappings[$slugLower] as $tagName) {
                $tagKey = mb_strtolower($tagName);
                if (isset($tagsByName[$tagKey])) {
                    $matchedTagIds[$tagsByName[$tagKey]] = true;
                }
            }
            // Return early if we found direct matches
            return array_keys($matchedTagIds);
        }

        // Keyword-based matching for categories and pages without direct mappings
        $keywordMappings = $this->getKeywordMappings();
        
        foreach ($keywordMappings as $keyword => $tagNames) {
            if (str_contains($titleLower, $keyword) || str_contains($slugLower, $keyword)) {
                $tagList = is_array($tagNames) ? $tagNames : [$tagNames];
                foreach ($tagList as $tagName) {
                    $tagKey = mb_strtolower($tagName);
                    if (isset($tagsByName[$tagKey])) {
                        $matchedTagIds[$tagsByName[$tagKey]] = true;
                    }
                }
            }
        }

        // Return unique tag IDs
        return array_keys($matchedTagIds);
    }

    /**
     * Get direct mappings from slugs to tag names
     */
    private function getDirectMappings(): array
    {
        return [
            // Tense pages
            'present-simple' => ['Present Simple', 'Present Simple Do/Does Choice', 'Present Simple To Be Choice'],
            'present-continuous' => ['Present Continuous', 'Present Continuous Affirmative Form', 'Present Continuous Negative Form', 'Present Continuous Question Form'],
            'present-perfect' => ['Present Perfect', 'Present Perfect Forms', 'Present Perfect Sentences', 'Present Perfect Simple'],
            'present-perfect-continuous' => ['Present Perfect Continuous', 'Present Perfect Continuous Affirmative Form', 'Present Perfect Continuous Negative Form', 'Present Perfect Continuous Question Form'],
            'past-simple' => ['Past Simple', 'Past Simple Do/Did Choice', 'Past Simple To Be Choice', 'Past Simple Passive'],
            'past-continuous' => ['Past Continuous', 'Past Continuous Affirmative Form', 'Past Continuous Negative Form', 'Past Continuous Question Form'],
            'past-perfect' => ['Past Perfect', 'Past Perfect Simple', 'Past Perfect Passive'],
            'past-perfect-continuous' => ['Past Perfect Continuous'],
            'future-simple' => ['Future Simple', 'Future Simple Will/Do Choice', 'Future Simple To Be Choice', 'Future Simple negative', 'Future Simple question', 'Future Simple Negative Form', 'Future Simple Question Form'],
            'future-continuous' => ['Future Continuous', 'Future Continuous Negative Form', 'Future Continuous Question Form', 'Future Continuous Focus'],
            'future-perfect' => ['Future Perfect', 'Future Perfect Negative Form', 'Future Perfect Question Form', 'Future Perfect Focus'],
            'future-perfect-continuous' => ['Future Perfect Continuous', 'Future Perfect Continuous Negative Form', 'Future Perfect Continuous Question Form', 'Future Perfect Continuous Focus'],
            
            // Conditional pages
            'zero-conditional' => ['Zero Conditional', 'Zero Conditional Sentences'],
            'first-conditional' => ['First Conditional', 'First Conditional Sentences', 'First Conditional Clause Completion'],
            'second-conditional' => ['Second Conditional', 'Second Conditional Sentences', 'Second Conditional Sentence Completion'],
            'third-conditional' => ['Third Conditional', 'Third Conditional Sentences'],
            'mixed-conditional' => ['Mixed Conditional', 'Mixed Conditional Sentences', 'Mixed Conditional (Second→Third)', 'Mixed Conditional (Third→Second)', 'Mixed Conditional Sentence Creation'],
            
            // Modal verb pages
            'modal-verbs-can-could' => ['Can / Could', 'Ability (can/could)', 'Permission (can/could)'],
            'modal-verbs-may-might' => ['May / Might', 'Permission (may)', 'Possibility (may/might)'],
            'modal-verbs-must-have-to' => ['Must / Have to', 'Obligation (must/have to)', 'Necessity (must)'],
            'modal-verbs-should-ought-to' => ['Should / Ought to', 'Advice (should/ought to)', 'Recommendation (should)'],
            'modal-verbs-need-need-to' => ['Need / Need to', 'Need / Needn\'t'],
            'modal-verbs-perfect-modals' => ['Perfect Modals', 'Be Supposed To', 'Shall'],
            
            // Article pages
            'a-an-the' => ['A An The', 'A or An', 'Articles'],
            
            // Demonstrative pages
            'this-that-these-those' => ['this_that_these_those', 'this_that_these_those_exercise_2', 'this_that_these_those_exercise_3'],
            
            // Have got pages
            'have-got' => ['have_has_got', 'have_has_got_exercise_2', 'have_has_got_exercise_3'],
            
            // Question pages
            'question-forms' => ['Question Formation', 'Question Word Order', 'Wh-Questions', 'Yes/No Questions'],
            'short-answers' => ['short_answers', 'Short Answers'],
            
            // Auxiliary verb pages
            'do-does-is-are' => ['do_does_is_are', 'Auxiliary Verb Choice'],
            
            // There is/are pages
            'there-is-there-are' => ['There is there are', 'There is', 'There are', 'There isn\'t', 'There aren\'t', 'Is there', 'Are there'],
            
            // Verb to be pages
            'verb-to-be' => ['to_be_tense', 'Verb "to be"'],
            
            // Pronoun pages
            'pronouns' => ['Personal Pronouns', 'Possessive Pronouns', 'Reflexive Pronouns', 'Demonstrative Pronouns', 'Indefinite Pronoun Compounds', 'Gap-fill Pronoun Forms (AI)'],
            
            // Some/Any pages
            'some-any' => ['Some/Any', 'Some / Any / No / Every Compounds (AI)', 'Something / Anything / Nothing Exercises'],
            'some-any-people' => ['somebody', 'anybody', 'nobody', 'everybody', 'someone', 'anyone', 'no one', 'everyone'],
            'some-any-places' => ['somewhere', 'anywhere', 'nowhere', 'everywhere'],
            'some-any-things' => ['something', 'anything', 'nothing', 'everything'],
            
            // Quantifier pages
            'quantifiers' => ['Much/Many', 'A lot of', 'Few/Little', 'Quantifiers'],
            
            // Irregular verb pages
            'irregular-verbs' => ['Irregular Verbs', 'Irregular Past Forms'],
            
            // Contraction pages
            'contractions-short-forms' => ['Contractions', 'Short Forms'],
            
            // Comparison pages
            'degrees-of-comparison' => [
                'Degrees of Comparison', 
                'Comparative / Superlative Choice', 
                'Comparative + than Pattern', 
                'As ... as Equality', 
                'Superlative Formation (-est / most / least)', 
                'Irregular Comparative Forms (good/bad/far)', 
                'Quantity Comparisons (much/many/less/fewer)'
            ],
            
            // Translation and vocabulary pages
            'translation-techniques' => ['Translation'],
            'vocabulary-building' => ['Vocabulary', 'Word Formation'],
        ];
    }

    /**
     * Get keyword-based mappings
     * 
     * These are used for category-level matching when no direct mapping exists.
     * Only include tags that actually exist in the system.
     */
    private function getKeywordMappings(): array
    {
        return [
            // Category-level keywords - Tenses
            'tenses' => ['Present Simple', 'Present Continuous', 'Present Perfect', 'Present Perfect Continuous', 'Past Simple', 'Past Continuous', 'Past Perfect', 'Past Perfect Continuous', 'Future Simple', 'Future Continuous', 'Future Perfect', 'Future Perfect Continuous'],
            'часи' => ['Present Simple', 'Present Continuous', 'Present Perfect', 'Present Perfect Continuous', 'Past Simple', 'Past Continuous', 'Past Perfect', 'Past Perfect Continuous', 'Future Simple', 'Future Continuous', 'Future Perfect', 'Future Perfect Continuous'],
            
            // Category-level keywords - Conditionals
            'conditions' => ['First Conditional', 'Second Conditional', 'Third Conditional', 'Mixed Conditional', 'Zero Conditional'],
            'умовні' => ['First Conditional', 'Second Conditional', 'Third Conditional', 'Mixed Conditional', 'Zero Conditional'],
            
            // Category-level keywords - Modal Verbs
            'modal' => ['Can / Could', 'May / Might', 'Must / Have to', 'Should / Ought to', 'Will / Would', 'Need / Need to'],
            'модальні' => ['Can / Could', 'May / Might', 'Must / Have to', 'Should / Ought to', 'Will / Would', 'Need / Need to'],
            
            // Category-level keywords - Articles
            'articles' => ['A An The', 'A or An'],
            'артиклі' => ['A An The', 'A or An'],
            
            // Category-level keywords - Pronouns
            'pronouns' => ['Indefinite Pronoun Compounds', 'Gap-fill Pronoun Forms (AI)'],
            'займенники' => ['Indefinite Pronoun Compounds', 'Gap-fill Pronoun Forms (AI)'],
            
            // Category-level keywords - Quantifiers
            'quantifiers' => ['Quantifiers'],
            'кількісні' => ['Quantifiers'],
            
            // Category-level keywords - Demonstratives
            'demonstrat' => ['this_that_these_those'],
            'вказівні' => ['this_that_these_those'],
            
            // Category-level keywords - Possession/Have got
            'possession' => ['have_has_got'],
            'володіння' => ['have_has_got'],
            
            // Category-level keywords - Comparison
            'comparison' => ['Degrees of Comparison'],
            'прикметник' => ['Degrees of Comparison'],
        ];
    }
}
