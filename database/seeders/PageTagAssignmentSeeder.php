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
                $tagsByName[mb_strtolower($tagName)] = $tagId;
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
            $categoryTags = $this->findMatchingTags($categoryTitle, $categorySlug, $tagsByName, true);
            if (!empty($categoryTags)) {
                // Get existing tag IDs to prevent duplicates
                $existingTags = $pageCategory->tags()->pluck('tags.id')->toArray();
                $newTags = array_diff($categoryTags, $existingTags);
                
                if (!empty($newTags)) {
                    $pageCategory->tags()->attach($newTags);
                    Log::info("Assigned " . count($newTags) . " new tags to category: {$categoryTitle} (skipped " . count(array_intersect($categoryTags, $existingTags)) . " existing)");
                } else {
                    Log::info("No new tags to assign to category: {$categoryTitle} (all " . count($categoryTags) . " already exist)");
                }
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
                $pageTags = $this->findMatchingTags($pageTitle, $pageSlug, $tagsByName, false);
                if (!empty($pageTags)) {
                    // Get existing tag IDs to prevent duplicates
                    $existingTags = $page->tags()->pluck('tags.id')->toArray();
                    $newTags = array_diff($pageTags, $existingTags);
                    
                    if (!empty($newTags)) {
                        $page->tags()->attach($newTags);
                        Log::info("Assigned " . count($newTags) . " new tags to page: {$pageTitle} (skipped " . count(array_intersect($pageTags, $existingTags)) . " existing)");
                    } else {
                        Log::info("No new tags to assign to page: {$pageTitle} (all " . count($pageTags) . " already exist)");
                    }
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
     * @param bool $isCategory Whether this is a category (true) or page (false)
     * @return array Array of unique tag IDs
     */
    private function findMatchingTags(string $title, string $slug, array $tagsByName, bool $isCategory = false): array
    {
        $matchedTagIds = [];
        $titleLower = mb_strtolower($title);
        $slugLower = strtolower($slug);

        // For pages: use direct mappings for specific tags
        if (!$isCategory) {
            $directMappings = $this->getDirectMappings();
            
            // Check if we have a direct mapping (preferred for specific pages)
            if (isset($directMappings[$slugLower])) {
                foreach ($directMappings[$slugLower] as $tagName) {
                    $tagKey = mb_strtolower($tagName);
                    if (isset($tagsByName[$tagKey])) {
                        $matchedTagIds[$tagsByName[$tagKey]] = true;
                    }
                }
            }
        }

        // For categories: use category mappings for general tags
        if ($isCategory) {
            $categoryMappings = $this->getCategoryMappings();
            
            if (isset($categoryMappings[$slugLower])) {
                foreach ($categoryMappings[$slugLower] as $tagName) {
                    $tagKey = mb_strtolower($tagName);
                    if (isset($tagsByName[$tagKey])) {
                        $matchedTagIds[$tagsByName[$tagKey]] = true;
                    }
                }
            }
        }

        // Keyword-based matching as fallback
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
            // Tense pages - detailed specific tags for each tense
            'present-simple' => [
                'Present Simple',
                'Present Simple Do/Does Choice',
                'Present Simple To Be Choice',
                'Present Simple negative',
                'Present Simple question',
                'Present Auxiliaries Focus',
                'present_simple',
                'present_simple_exercises',
                'quiz_present_simple',
                'simple_present_past',
            ],
            'present-continuous' => [
                'Present Continuous',
                'present_continuous_short_forms',
                'present_continuous_short_answers',
                'present_continuous_dialogue',
                'Ongoing Actions',
                'Ongoing Projects',
            ],
            'present-perfect' => [
                'Present Perfect',
                'Present Perfect Forms',
                'Present Perfect Sentences',
                'Present Perfect Simple',
                'Present Perfect Completion',
                'present_perfect_dialogue',
                'present_perfect_result_or_side_effect',
                'present_perfect_since_last_time',
                'Present Result Situations',
                'Present Evidence',
                'Past Experiences Before Events',
            ],
            'present-perfect-continuous' => [
                'Present Perfect Continuous',
                'Duration Emphasis',
                'Ongoing Actions',
                'Resulting State After Long Action',
            ],
            'past-simple' => [
                'Past Simple',
                'Past Simple Do/Did Choice',
                'Past Simple To Be Choice',
                'Past Simple Passive',
                'Past Simple negative',
                'Past Simple question',
                'Past Simple Time Markers',
                'Past Simple with Ago',
                'past_simple_regular',
                'past_simple_regular_verbs_full',
                'grammar_quiz_past_simple',
            ],
            'past-continuous' => [
                'Past Continuous',
                'Past Continuous Affirmative',
                'Past Continuous Negatives',
                'Past Continuous Questions',
                'Past Continuous Review',
                'past_continuous_tense_test',
                'Simultaneous Past Actions',
                'Specific Time in Past Continuous',
            ],
            'past-perfect' => [
                'Past Perfect',
                'Past Perfect Simple',
                'Past Perfect Passive',
                'Past Perfect Affirmative',
                'Past Perfect Negatives',
                'Past Perfect Questions',
                'Past Perfect Review',
                'Past Perfect Mastery Test',
                'Sequence of Past Actions',
                'Prior Past Actions',
            ],
            'past-perfect-continuous' => [
                'Past Perfect Continuous',
                'Past Perfect Continuous Forms',
                'Past Perfect Continuous Practice',
                'Duration Emphasis',
                'Past Perfect Continuous Cause & Effect',
            ],
            'future-simple' => [
                'Future Simple',
                'Future Simple Will/Do Choice',
                'Future Simple To Be Choice',
                'Future Simple negative',
                'Future Simple question',
                'Future Simple Negative Form',
                'Future Simple Question Form',
                'future_simple_test_1',
                'Simple Future Plans and Decisions',
            ],
            'future-continuous' => [
                'Future Continuous',
                'Future Continuous Negative Form',
                'Future Continuous Question Form',
                'Future Continuous Focus',
                'Ongoing Action at Future Time',
            ],
            'future-perfect' => [
                'Future Perfect',
                'Future Perfect Negative Form',
                'Future Perfect Question Form',
                'Future Perfect Focus',
                'Completion before Future Moment',
            ],
            'future-perfect-continuous' => [
                'Future Perfect Continuous',
                'Future Perfect Continuous Negative Form',
                'Future Perfect Continuous Question Form',
                'Future Perfect Continuous Focus',
                'Duration Emphasis',
            ],
            
            // Conditional pages - detailed conditional tags
            'zero-conditional' => [
                'Zero Conditional',
                'Conditionals Type 0-1-2 Practice',
                'General Facts',
            ],
            'first-conditional' => [
                'First Conditional',
                'First Conditional Sentences',
                'First Conditional Clause Completion',
                'First Conditional Practice',
                'Future Time Reference',
            ],
            'second-conditional' => [
                'Second Conditional',
                'Second Conditional Sentences',
                'Second Conditional Sentence Completion',
                'Second Conditional Practice',
                'Modal Habits & Hypotheticals',
            ],
            'third-conditional' => [
                'Third Conditional',
                'Third Conditional Sentences',
                'Third Conditional Practice',
                'Modal Hypotheticals (Past)',
                'Wrong Choice Consequence',
            ],
            'mixed-conditional' => [
                'Mixed Conditional',
                'Mixed Conditional Sentences',
                'Mixed Conditional (Second→Third)',
                'Mixed Conditional (Third→Second)',
                'Mixed Conditional Sentence Creation',
            ],
            
            // Modal verb pages - detailed modal tags
            'modal-verbs-can-could' => [
                'Can / Could',
                'Can',
                'Modal Verbs Practice',
                'Modal Verbs',
                'Modal: Can / Could',
                'Modal Ability',
                'Modal Ability & Permission',
                'Modal Ability & Permission Comparison',
                'Modal Ability Focus',
                'Modal Possibility vs Ability',
                'Modal Polite Requests',
                'Modal Past Possibility',
            ],
            'modal-verbs-may-might' => [
                'May / Might',
                'Modal Verbs Practice',
                'Modal Verbs',
                'Modal: May / Might',
                'Modal Degrees of Probability',
                'Modal Possibility & Probability',
                'Modal Possibility & Deduction',
                'Modal Formal Permission',
                'Modal Permission',
                'Modal Permission Focus',
                'Modal Future Speculation',
            ],
            'modal-verbs-must-have-to' => [
                'Must / Have to',
                'Modal Verbs Practice',
                'Modal Verbs',
                'Modal: Must / Have to',
                'Modal Obligation & Necessity Comparison',
                'Modal Obligation & Necessity',
                'Modal Necessity vs Obligation',
                'Modal Obligation Focus',
                'Modal Obligation & Prohibition',
                'Modal Deduction Focus',
                'Modal Deduction (Present)',
            ],
            'modal-verbs-should-ought-to' => [
                'Should / Ought to',
                'Modal Verbs Practice',
                'Modal Verbs',
                'Modal: Should / Ought to',
                'Modal Advice & Suggestions',
                'Modal Advice & Criticism',
                'Modal Advice Focus',
                'Modal Past Criticism',
            ],
            'modal-verbs-need-need-to' => [
                'Need / Need to',
                "Need / Needn't",
                'Modal: Need / Need to',
                'Modal Nuanced Necessity',
            ],
            'modal-verbs-perfect-modals' => [
                'Be Supposed To',
                'Shall',
                'Modal Verbs Practice',
                'Modal Verbs',
                'Modal Past Usage',
                'Modal Deduction (Past)',
                'Modal Past Possibility',
                'Modal Past Criticism',
            ],
            
            // Article pages - detailed article tags
            'a-an-the' => [
                'A An The',
                'A or An',
                'Articles',
            ],
            
            // Some/Any page
            'some-any' => [
                'Some or Any',
                'Some/Any/No/Every Compounds (AI)',
                'Something / Anything / Nothing Exercises',
            ],
            
            // Quantifiers page
            'quantifiers' => [
                'Quantity Comparisons (much/many/less/fewer)',
                'Quantifiers',
            ],
            
            // Demonstrative pages - detailed demonstrative tags
            'this-that-these-those' => [
                'this_that_these_those',
                'this_that_these_those_exercise_2',
                'this_that_these_those_exercise_3',
            ],
            
            // Have got pages - detailed possession tags
            'have-got' => [
                'have_has_got',
                'have_has_got_exercise_2',
                'have_has_got_exercise_3',
                'Have Has Got',
            ],
            
            // Irregular verbs page
            'irregular-verbs' => [
                'Past Simple',
                'Irregular Comparative Forms (good/bad/far)',
            ],
            
            // Pronouns page
            'pronouns' => [
                'Indefinite Pronoun Compounds',
                'Gap-fill Pronoun Forms (AI)',
                'Indefinite Pronouns Practice',
                'Indefinite Pronouns Practice AI',
            ],
            
            // Some/Any subcategory pages
            'some-any-people' => [
                'Some/Any/No/Every Compounds (AI)',
                'Indefinite Pronoun Compounds',
                'People',
            ],
            'some-any-places' => [
                'Some/Any/No/Every Compounds (AI)',
                'Indefinite Pronoun Compounds',
                'Places',
            ],
            'some-any-things' => [
                'Something / Anything / Nothing Exercises',
                'Some/Any/No/Every Compounds (AI)',
                'Objects',
            ],
            
            // Question pages - detailed question tags
            'question-forms' => [
                'question_words_drag_drop',
                'Present Simple question',
                'Past Simple question',
                'Future Simple question',
                'Past Continuous Questions',
                'Past Perfect Questions',
                'Modal Question Form',
            ],
            'short-answers' => [
                'short_answers',
                'present_continuous_short_answers',
            ],
            
            // Auxiliary verb pages - detailed auxiliary tags
            'do-does-is-are' => [
                'do_does_is_are',
                'Past Simple Do/Did Choice',
                'Present Simple Do/Does Choice',
                'Present Auxiliaries Focus',
                'Past Auxiliaries Focus',
            ],
            
            // There is/are pages - detailed structure tags
            'there-is-there-are' => [
                'There is there are',
                'There is',
                'There are',
                "There isn't",
                "There aren't",
                "There aren't any",
                'Is there',
                'Are there',
                'There is/There are',
                'There were',
            ],
            
            // Verb to be pages - detailed verb to be tags
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
                'Future Simple To Be Choice',
                'Present Auxiliaries Focus',
                'Past Auxiliaries Focus',
            ],
            
            // Contraction pages - detailed contraction tags
            'contractions-short-forms' => [
                'Contractions',
                'present_continuous_short_forms',
                'present_continuous_short_answers',
            ],
            
            // Comparison pages - detailed comparison tags
            'degrees-of-comparison' => [
                'Degrees of Comparison',
                'Comparative / Superlative Choice',
                'Comparative + than Pattern',
                'As ... as Equality',
                'Superlative Formation (-est / most / least)',
                'Irregular Comparative Forms (good/bad/far)',
                'Quantity Comparisons (much/many/less/fewer)',
                'Comparatives and Superlatives Practice',
            ],
            
            // Translation page
            'translation-techniques' => [
                'Translation',
            ],
            
            // Vocabulary page
            'vocabulary-building' => [
                'Vocabulary',
            ],
        ];
    }

    /**
     * Get category-level mappings
     * 
     * These are general tags that apply to entire categories.
     * Categories should have broader, more general tags that encompass their theme.
     */
    private function getCategoryMappings(): array
    {
        return [
            // Tenses category - all main tense tags
            'tenses' => [
                'Present Simple',
                'Present Continuous',
                'Present Perfect',
                'Present Perfect Continuous',
                'Past Simple',
                'Past Continuous',
                'Past Perfect',
                'Past Perfect Continuous',
                'Future Simple',
                'Future Continuous',
                'Future Perfect',
                'Future Perfect Continuous',
                'Tense: Present',
                'Tense: Past',
                'Tense: Future',
            ],
            
            // Conditionals category - all conditional tags
            'conditions' => [
                'Zero Conditional',
                'First Conditional',
                'Second Conditional',
                'Third Conditional',
                'Mixed Conditional',
                'Conditionals Type 0-1-2 Practice',
                'First Conditional Practice',
                'Second Conditional Practice',
                'Third Conditional Practice',
            ],
            
            // Modal verbs category - general modal tags
            'modal-verbs' => [
                'Modal Verbs',
                'Modal Verbs Practice',
                'Can / Could',
                'May / Might',
                'Must / Have to',
                'Should / Ought to',
                'Will / Would',
                'Need / Need to',
                'Modal: Can / Could',
                'Modal: May / Might',
                'Modal: Must / Have to',
                'Modal: Should / Ought to',
                'Modal: Will / Would',
                'Modal: Need / Need to',
            ],
            
            // Articles and quantifiers category
            'articles-and-quantifiers' => [
                'A An The',
                'A or An',
                'Some or Any',
                'Quantity Comparisons (much/many/less/fewer)',
                'Articles',
                'Quantifiers',
            ],
            
            // Some/Any category
            'some-any' => [
                'Some or Any',
                'Some/Any/No/Every Compounds (AI)',
                'Something / Anything / Nothing Exercises',
                'Indefinite Pronoun Compounds',
            ],
            
            // Demonstratives category
            'demonstratives' => [
                'this_that_these_those',
                'this_that_these_those_exercise_2',
                'this_that_these_those_exercise_3',
            ],
            
            // Possession category
            'possession' => [
                'have_has_got',
                'have_has_got_exercise_2',
                'have_has_got_exercise_3',
                'Have Has Got',
            ],
            
            // Verbs category
            'verbs' => [
                'Past Simple',
                'Irregular Comparative Forms (good/bad/far)',
            ],
            
            // Pronouns category
            'pronouns' => [
                'Indefinite Pronoun Compounds',
                'Gap-fill Pronoun Forms (AI)',
                'Indefinite Pronouns Practice',
                'Indefinite Pronouns Practice AI',
            ],
            
            // Sentence structures category
            'sentence-structures' => [
                'Contractions',
                'do_does_is_are',
                'There is there are',
                'There is/There are',
                'to_be_tense',
            ],
            
            // Translation category
            'translation' => [
                'Translation',
            ],
            
            // Questions category
            'questions' => [
                'question_words_drag_drop',
                'short_answers',
                'Present Simple question',
                'Past Simple question',
                'Future Simple question',
            ],
            
            // Adjectives category
            'adjectives' => [
                'Degrees of Comparison',
                'Comparative / Superlative Choice',
                'Comparative + than Pattern',
                'As ... as Equality',
                'Superlative Formation (-est / most / least)',
                'Irregular Comparative Forms (good/bad/far)',
                'Quantity Comparisons (much/many/less/fewer)',
                'Comparatives and Superlatives Practice',
            ],
            
            // Vocabulary category
            'vocabulary' => [
                'Vocabulary',
            ],
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
