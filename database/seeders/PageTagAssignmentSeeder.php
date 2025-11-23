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

        // Load and validate tags data
        $tagsPath = config_path('tags/exported_tags.json');
        if (!file_exists($tagsPath)) {
            Log::error("Tags file not found: {$tagsPath}");
            return;
        }
        
        $tagsData = json_decode(file_get_contents($tagsPath), true);
        if (!$tagsData || !isset($tagsData['categories']) || !is_array($tagsData['categories'])) {
            Log::error("Invalid tags data structure in {$tagsPath}");
            return;
        }

        // Load and validate pages data
        $pagesPath = config_path('pages/exported_pages.json');
        if (!file_exists($pagesPath)) {
            Log::error("Pages file not found: {$pagesPath}");
            return;
        }
        
        $pagesData = json_decode(file_get_contents($pagesPath), true);
        if (!$pagesData || !isset($pagesData['categories']) || !is_array($pagesData['categories'])) {
            Log::error("Invalid pages data structure in {$pagesPath}");
            return;
        }

        // Index tags by name and ID for quick lookup
        $tagsByName = [];
        $tagsById = [];
        
        foreach ($tagsData['categories'] as $tagCategory) {
            if (!isset($tagCategory['tags']) || !is_array($tagCategory['tags'])) {
                continue;
            }
            
            foreach ($tagCategory['tags'] as $tagData) {
                if (!isset($tagData['name']) || !isset($tagData['id'])) {
                    continue;
                }
                
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
            'present-continuous' => ['Present Continuous', 'present_continuous_short_forms', 'present_continuous_short_answers'],
            'present-perfect' => ['Present Perfect', 'Present Perfect Forms', 'Present Perfect Sentences', 'Present Perfect Simple'],
            'present-perfect-continuous' => ['Present Perfect Continuous'],
            'past-simple' => ['Past Simple', 'Past Simple Do/Did Choice', 'Past Simple To Be Choice', 'Past Simple Passive'],
            'past-continuous' => [
                'Past Continuous',
                'Past Continuous Affirmative',
                'Past Continuous Negatives',
                'Past Continuous Questions',
                'Past Continuous Review'
            ],
            'past-perfect' => ['Past Perfect', 'Past Perfect Simple', 'Past Perfect Passive'],
            'past-perfect-continuous' => ['Past Perfect Continuous'],
            'future-simple' => ['Future Simple', 'Future Simple Will/Do Choice', 'Future Simple To Be Choice', 'Future Simple negative', 'Future Simple question', 'Future Simple Negative Form', 'Future Simple Question Form'],
            'future-continuous' => ['Future Continuous', 'Future Continuous Negative Form', 'Future Continuous Question Form', 'Future Continuous Focus'],
            'future-perfect' => ['Future Perfect', 'Future Perfect Negative Form', 'Future Perfect Question Form', 'Future Perfect Focus'],
            'future-perfect-continuous' => ['Future Perfect Continuous', 'Future Perfect Continuous Negative Form', 'Future Perfect Continuous Question Form', 'Future Perfect Continuous Focus'],
            
            // Conditional pages
            'zero-conditional' => ['Zero Conditional', 'Conditionals Type 0-1-2 Practice'],
            'first-conditional' => ['First Conditional', 'First Conditional Sentences', 'First Conditional Clause Completion'],
            'second-conditional' => ['Second Conditional', 'Second Conditional Sentences', 'Second Conditional Sentence Completion'],
            'third-conditional' => ['Third Conditional', 'Third Conditional Sentences'],
            'mixed-conditional' => ['Mixed Conditional', 'Mixed Conditional Sentences', 'Mixed Conditional (Second→Third)', 'Mixed Conditional (Third→Second)', 'Mixed Conditional Sentence Creation'],
            
            // Modal verb pages
            'modal-verbs-can-could' => ['Can / Could', 'Can', 'Modal Verbs Practice', 'Modal Verbs'],
            'modal-verbs-may-might' => ['May / Might', 'Modal Verbs Practice', 'Modal Verbs', 'Modal Degrees of Probability'],
            'modal-verbs-must-have-to' => ['Must / Have to', 'Modal Verbs Practice', 'Modal Verbs', 'Modal Obligation & Necessity Comparison'],
            'modal-verbs-should-ought-to' => ['Should / Ought to', 'Modal Verbs Practice', 'Modal Verbs'],
            'modal-verbs-need-need-to' => ['Need / Need to', "Need / Needn't"],
            'modal-verbs-perfect-modals' => ['Be Supposed To', 'Shall', 'Modal Verbs Practice', 'Modal Verbs'],
            
            // Article pages
            'a-an-the' => ['A An The', 'A or An'],
            
            // Demonstrative pages
            'this-that-these-those' => ['this_that_these_those', 'this_that_these_those_exercise_2', 'this_that_these_those_exercise_3'],
            
            // Have got pages
            'have-got' => ['have_has_got', 'have_has_got_exercise_2', 'have_has_got_exercise_3'],
            
            // Question pages
            'question-forms' => [
                'question_words_drag_drop',
                'Present Simple question',
                'Past Simple question',
                'Future Simple question',
                'Past Continuous Questions',
                'Past Perfect Questions',
                'Modal Question Form'
            ],
            'short-answers' => ['short_answers', 'present_continuous_short_answers'],
            
            // Auxiliary verb pages
            'do-does-is-are' => ['do_does_is_are', 'Past Simple Do/Did Choice', 'Present Simple Do/Does Choice'],
            
            // There is/are pages
            'there-is-there-are' => [
                'There is there are', 
                'There is', 
                'There are', 
                "There isn't", 
                "There aren't",
                "There aren't any",
                'Is there', 
                'Are there',
                'There is/There are'
            ],
            
            // Verb to be pages
            'verb-to-be' => [
                'to_be_tense',
                'To be negative (present)',
                'To be negative (past)',
                'To be negative (future)',
                'To be question (present)',
                'To be question (past)',
                'To be question (future)',
                'Present Simple To Be Choice',
                'Past Simple To Be Choice',
                'Future Simple To Be Choice'
            ],
            
            // Pronoun pages
            'pronouns' => [
                'Indefinite Pronoun Compounds', 
                'Gap-fill Pronoun Forms (AI)',
                'Indefinite Pronouns Practice',
                'Indefinite Pronouns Practice AI'
            ],
            
            // Some/Any pages
            'some-any' => [
                'Some or Any',
                'Some/Any/No/Every Compounds (AI)', 
                'Something / Anything / Nothing Exercises'
            ],
            'some-any-people' => ['Some/Any/No/Every Compounds (AI)', 'Indefinite Pronoun Compounds'],
            'some-any-places' => ['Some/Any/No/Every Compounds (AI)', 'Indefinite Pronoun Compounds'],
            'some-any-things' => ['Something / Anything / Nothing Exercises', 'Some/Any/No/Every Compounds (AI)'],
            
            // Quantifier pages
            'quantifiers' => ['Quantity Comparisons (much/many/less/fewer)'],
            
            // Irregular verb pages
            'irregular-verbs' => ['Past Simple', 'Irregular Comparative Forms (good/bad/far)'],
            
            // Contraction pages
            'contractions-short-forms' => ['Contractions', 'present_continuous_short_forms', 'present_continuous_short_answers'],
            
            // Comparison pages
            'degrees-of-comparison' => [
                'Degrees of Comparison', 
                'Comparative / Superlative Choice', 
                'Comparative + than Pattern', 
                'As ... as Equality', 
                'Superlative Formation (-est / most / least)', 
                'Irregular Comparative Forms (good/bad/far)', 
                'Quantity Comparisons (much/many/less/fewer)',
                'Comparatives and Superlatives Practice'
            ],
            
            // Translation and vocabulary pages
            'translation-techniques' => [],  // No matching tags found in system
            'vocabulary-building' => [],  // No matching tags found in system
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
            'pronouns' => ['Indefinite Pronoun Compounds', 'Gap-fill Pronoun Forms (AI)', 'Indefinite Pronouns Practice', 'Indefinite Pronouns Practice AI'],
            'займенники' => ['Indefinite Pronoun Compounds', 'Gap-fill Pronoun Forms (AI)', 'Indefinite Pronouns Practice', 'Indefinite Pronouns Practice AI'],
            
            // Category-level keywords - Quantifiers
            'quantifiers' => ['Quantity Comparisons (much/many/less/fewer)'],
            'кількісні' => ['Quantity Comparisons (much/many/less/fewer)'],
            
            // Category-level keywords - Demonstratives
            'demonstrat' => ['this_that_these_those', 'this_that_these_those_exercise_2', 'this_that_these_those_exercise_3'],
            'вказівні' => ['this_that_these_those', 'this_that_these_those_exercise_2', 'this_that_these_those_exercise_3'],
            
            // Category-level keywords - Possession/Have got
            'possession' => ['have_has_got', 'have_has_got_exercise_2', 'have_has_got_exercise_3'],
            'володіння' => ['have_has_got', 'have_has_got_exercise_2', 'have_has_got_exercise_3'],
            
            // Category-level keywords - Comparison/Adjectives
            'comparison' => ['Degrees of Comparison', 'Comparative / Superlative Choice', 'Comparative + than Pattern', 'Comparatives and Superlatives Practice'],
            'прикметник' => ['Degrees of Comparison', 'Comparative / Superlative Choice', 'Comparative + than Pattern', 'Comparatives and Superlatives Practice'],
            'adjective' => ['Degrees of Comparison', 'Comparative / Superlative Choice', 'Comparative + than Pattern', 'Comparatives and Superlatives Practice'],
            
            // Category-level keywords - Questions
            'question' => ['question_words_drag_drop', 'Present Simple question', 'Past Simple question', 'Future Simple question'],
            'питальні' => ['question_words_drag_drop', 'Present Simple question', 'Past Simple question', 'Future Simple question'],
            
            // Category-level keywords - Sentence structures
            'structures' => ['Contractions'],
            'конструкції' => ['Contractions'],
            
            // Category-level keywords - Verbs
            'verbs' => ['Past Simple', 'Irregular Comparative Forms (good/bad/far)'],
            'дієслова' => ['Past Simple', 'Irregular Comparative Forms (good/bad/far)'],
        ];
    }
}
