<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\SiteTreeItem;
use App\Models\SiteTreeVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SiteTreeSeeder extends Seeder
{
    /**
     * Minimum length for title/slug matching to avoid false positives
     */
    private const MIN_MATCH_LENGTH = 5;

    /**
     * Common English words to exclude when normalizing titles
     */
    private const COMMON_WORDS = ['the', 'a', 'an', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'and', 'or', 'but'];

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

        $rootTitles = array_map(
            static fn (array $section): string => $section['title'],
            $tree
        );

        SiteTreeItem::query()
            ->where('variant_id', $baseVariant->id)
            ->whereNull('parent_id')
            ->whereNotIn('title', $rootTitles)
            ->delete();

        // Link pages to tree items
        $this->linkPagesToTreeItems($baseVariant->id);
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
            $childTitles = [];
            foreach ($data['children'] as $childSortOrder => $child) {
                $childTitles[] = $child['title'];
                $this->createOrUpdateItem($child, $item->id, $childSortOrder, $variantId);
            }

            SiteTreeItem::query()
                ->where('variant_id', $variantId)
                ->where('parent_id', $item->id)
                ->whereNotIn('title', $childTitles)
                ->delete();
        } else {
            SiteTreeItem::query()
                ->where('variant_id', $variantId)
                ->where('parent_id', $item->id)
                ->delete();
        }
    }

    /**
     * Link pages to tree items based on seeder class name or slug
     */
    private function linkPagesToTreeItems(int $variantId): void
    {
        // Get all tree items for this variant
        $treeItems = SiteTreeItem::where('variant_id', $variantId)->get();

        // Get all pages with their categories
        $pages = Page::with('category')->get();
        $categories = PageCategory::query()->get();

        $linkedCount = 0;
        $notLinkedCount = 0;

        foreach ($treeItems as $item) {
            $result = $this->findMatchingPage($item, $pages);
            $linkedPage = $result['page'];
            $linkMethod = $result['method'];
            $linkedTitle = null;
            $linkedUrl = null;

            if ($linkedPage) {
                $linkedTitle = $linkedPage->title;
                $linkedUrl = $this->generatePageUrl($linkedPage);
            } else {
                $categoryResult = $this->findMatchingCategory($item, $categories);
                $linkedCategory = $categoryResult['category'];
                $linkMethod = $categoryResult['method'];

                if ($linkedCategory) {
                    $linkedTitle = $linkedCategory->title;
                    $linkedUrl = $this->generateCategoryUrl($linkedCategory);
                }
            }

            if ($linkedTitle && $linkedUrl) {
                
                // Update the tree item with linked page info
                $item->update([
                    'linked_page_title' => $linkedTitle,
                    'linked_page_url' => $linkedUrl,
                    'link_method' => $linkMethod,
                ]);

                $linkedCount++;
                Log::info("✓ Linked: '{$item->title}' -> '{$linkedTitle}' ({$linkedUrl}) via {$linkMethod}");
            } else {
                $item->update([
                    'linked_page_title' => null,
                    'linked_page_url' => null,
                    'link_method' => null,
                ]);

                $notLinkedCount++;
                Log::info("✗ Not linked: '{$item->title}'");
            }
        }

        Log::info("Page linking complete: {$linkedCount} linked, {$notLinkedCount} not linked");
    }

    /**
     * Find a matching page for a tree item
     * Strategy:
     * 1. Try to match by exact title (most reliable)
     * 2. Try to match by seeder class name (if page has seeder)
     * 3. Try to match by slug pattern
     * 
     * @param SiteTreeItem $item
     * @param \Illuminate\Database\Eloquent\Collection $pages
     * @return array{page: Page|null, method: string|null}
     */
    private function findMatchingPage(SiteTreeItem $item, $pages): array
    {
        // Strategy 1: Exact title match (case insensitive)
        // This is the most reliable method
        foreach ($pages as $page) {
            if (strcasecmp(trim($item->title), trim($page->title)) === 0) {
                return ['page' => $page, 'method' => 'exact_title'];
            }
        }

        // Strategy 2: Match by seeder class name
        // Look for pages whose seeder class name contains parts of the tree item title
        foreach ($pages as $page) {
            if ($page->seeder) {
                $seederBaseName = class_basename($page->seeder);
                
                // Try to extract meaningful parts from the tree item title
                // For example: "Advanced word order and emphasis" -> "AdvancedWordOrderEmphasis"
                $normalizedTitle = $this->normalizeTitle($item->title);
                
                // Check if seeder name contains the normalized title
                // Only match if normalized title is meaningful (longer than MIN_MATCH_LENGTH)
                if (strlen($normalizedTitle) > self::MIN_MATCH_LENGTH && stripos($seederBaseName, $normalizedTitle) !== false) {
                    return ['page' => $page, 'method' => 'seeder_name'];
                }
            }
        }

        // Strategy 3: Match by slug pattern
        // Generate potential slug from tree item title and try to match
        $potentialSlug = $this->titleToSlug($item->title);
        
        // Only try slug matching if we have a meaningful slug
        if (strlen($potentialSlug) > self::MIN_MATCH_LENGTH) {
            foreach ($pages as $page) {
                // Check if either slug contains the other (substring match)
                if (stripos($page->slug, $potentialSlug) !== false || stripos($potentialSlug, $page->slug) !== false) {
                    return ['page' => $page, 'method' => 'slug_match'];
                }
            }
        }

        return ['page' => null, 'method' => null];
    }

    /**
     * Find a matching category for a tree item using the same strategy as pages.
     *
     * @param SiteTreeItem $item
     * @param \Illuminate\Database\Eloquent\Collection $categories
     * @return array{category: PageCategory|null, method: string|null}
     */
    private function findMatchingCategory(SiteTreeItem $item, $categories): array
    {
        foreach ($categories as $category) {
            if (strcasecmp(trim($item->title), trim($category->title)) === 0) {
                return ['category' => $category, 'method' => 'exact_title'];
            }
        }

        foreach ($categories as $category) {
            if ($category->seeder) {
                $seederBaseName = class_basename($category->seeder);
                $normalizedTitle = $this->normalizeTitle($item->title);

                if (strlen($normalizedTitle) > self::MIN_MATCH_LENGTH && stripos($seederBaseName, $normalizedTitle) !== false) {
                    return ['category' => $category, 'method' => 'seeder_name'];
                }
            }
        }

        $potentialSlug = $this->titleToSlug($item->title);

        if (strlen($potentialSlug) > self::MIN_MATCH_LENGTH) {
            foreach ($categories as $category) {
                if (stripos($category->slug, $potentialSlug) !== false || stripos($potentialSlug, $category->slug) !== false) {
                    return ['category' => $category, 'method' => 'slug_match'];
                }
            }
        }

        return ['category' => null, 'method' => null];
    }

    /**
     * Normalize title by removing common words and extracting key terms
     */
    private function normalizeTitle(string $title): string
    {
        // Remove level indicators like A1, B2, etc.
        $title = preg_replace('/\s*[A-C][1-2](?:-[A-C][1-2])?\s*/i', '', $title);
        
        // Remove content in parentheses and after — dash
        $title = preg_replace('/\s*[—–-]\s*.*$/', '', $title);
        $title = preg_replace('/\s*\(.*?\)\s*/', '', $title);
        
        // Extract only English words (skip Ukrainian translations)
        if (preg_match('/^([^—–-]+)/', $title, $matches)) {
            $title = $matches[1];
        }
        
        // Remove common words
        $words = explode(' ', $title);
        $words = array_filter($words, function($word) {
            return !in_array(strtolower(trim($word)), self::COMMON_WORDS);
        });
        
        // Convert to PascalCase-like format
        $normalized = implode('', array_map('ucfirst', array_map('trim', $words)));
        
        return $normalized;
    }

    /**
     * Convert title to slug format
     */
    private function titleToSlug(string $title): string
    {
        // Remove content in parentheses and after — dash
        $title = preg_replace('/\s*[—–-]\s*.*$/', '', $title);
        $title = preg_replace('/\s*\(.*?\)\s*/', '', $title);
        
        // Extract only English part
        if (preg_match('/^([^—–-]+)/', $title, $matches)) {
            $title = trim($matches[1]);
        }
        
        // Convert to lowercase and replace spaces with hyphens
        $slug = strtolower($title);
        $slug = preg_replace('/[^\w\s-]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return $slug;
    }

    /**
     * Generate URL for a page based on its type and category
     */
    private function generatePageUrl(Page $page): string
    {
        $pageType = $page->type ?? 'pages';
        $categorySlug = $page->category?->slug ?? 'uncategorized';
        $pageSlug = $page->slug;

        return "/{$pageType}/{$categorySlug}/{$pageSlug}";
    }

    /**
     * Generate URL for a category based on its type.
     */
    private function generateCategoryUrl(PageCategory $category): string
    {
        $pageType = $category->type ?? 'pages';

        return "/{$pageType}/{$category->slug}";
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
                    [
                        'title' => 'Verb to Be',
                        'level' => 'A1–A2',
                        'children' => [
                            ['title' => 'Verb to Be: Present Forms', 'level' => 'A1'],
                            ['title' => 'Verb to Be: Negatives', 'level' => 'A1'],
                            ['title' => 'Verb to Be: Questions and Short Answers', 'level' => 'A1'],
                            ['title' => 'Verb to Be: Past Forms', 'level' => 'A1–A2'],
                            ['title' => 'Verb to Be: Future', 'level' => 'A1–A2'],
                            ['title' => 'There Is / There Are', 'level' => 'A1–A2'],
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
                    [
                        'title' => 'Some / Any',
                        'level' => 'A1–A2',
                        'children' => [
                            ['title' => 'Some / Any — Кількість у ствердженні та запереченні', 'level' => 'A1–A2'],
                            ['title' => 'Some / Any — Люди', 'level' => 'A2'],
                            ['title' => 'Somewhere / Anywhere / Nowhere / Everywhere — місця з Some / Any', 'level' => 'A2'],
                            ['title' => 'Some / Any — Речі', 'level' => 'A2'],
                        ],
                    ],
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
                'title' => 'Tenses',
                'level' => 'A1–A2',
                'children' => [
                    [
                        'title' => 'Present Simple',
                        'level' => 'A1–A2',
                        'children' => [
                            ['title' => 'Present Simple: Forms and Use', 'level' => 'A1–A2'],
                            ['title' => 'Present Simple: Negatives', 'level' => 'A1–A2'],
                            ['title' => 'Present Simple: Questions and Short Answers', 'level' => 'A1–A2'],
                            ['title' => 'Present Simple: Adverbs of Frequency', 'level' => 'A1–A2'],
                        ],
                    ],
                    [
                        'title' => 'Present Continuous',
                        'level' => 'A1–A2',
                        'children' => [
                            ['title' => 'Present Continuous: Forms and Use', 'level' => 'A1–A2'],
                            ['title' => 'Present Continuous: Negatives', 'level' => 'A1–A2'],
                            ['title' => 'Present Continuous: Questions and Short Answers', 'level' => 'A1–A2'],
                            ['title' => 'Present Continuous: Time Expressions', 'level' => 'A1–A2'],
                        ],
                    ],
                    [
                        'title' => 'Past Simple',
                        'level' => 'A1–A2',
                        'children' => [
                            ['title' => 'Past Simple: Forms and Use', 'level' => 'A1–A2'],
                            ['title' => 'Past Simple: Negatives', 'level' => 'A1–A2'],
                            ['title' => 'Past Simple: Questions and Short Answers', 'level' => 'A1–A2'],
                            ['title' => 'Past Simple: Time Expressions', 'level' => 'A1–A2'],
                        ],
                    ],
                    [
                        'title' => 'Past Continuous',
                        'level' => 'A2',
                        'children' => [
                            ['title' => 'Past Continuous: Forms and Use', 'level' => 'A2'],
                            ['title' => 'Past Continuous: Negatives', 'level' => 'A2'],
                            ['title' => 'Past Continuous: Questions and Short Answers', 'level' => 'A2'],
                            ['title' => 'Past Continuous: Time Expressions', 'level' => 'A2'],
                        ],
                    ],
                ],
            ],
            [
                'title' => '7. Майбутні форми',
                'level' => 'A2–B1',
                'children' => [
                    ['title' => 'Will vs Be Going To — яка різниця?', 'level' => 'A2–B1'],
                ],
            ],
            [
                'title' => '8. Модальні дієслова',
                'level' => 'A1–C1',
                'children' => [
                    ['title' => 'Can / Could', 'level' => 'A1–A2'],
                    ['title' => 'May / Might', 'level' => 'A2–B1'],
                    ['title' => 'Must / Have to', 'level' => 'A2–B1'],
                    ['title' => 'Should / Ought to', 'level' => 'A2–B1'],
                    ['title' => 'Need / Needn\'t / Don\'t have to', 'level' => 'A2–B1'],
                    ['title' => 'Modals of Deduction', 'level' => 'B1–B2'],
                    ['title' => 'Past Modals', 'level' => 'B1–B2'],
                    ['title' => 'Requests / Offers / Suggestions', 'level' => 'A2–B1'],
                ],
            ],
            [
                'title' => '9. Питальні речення та заперечення',
                'level' => 'A1–B2',
                'children' => [
                    [
                        'title' => 'Види питальних речень',
                        'level' => 'A1–B1',
                        'children' => [
                            ['title' => 'Yes/No Questions (General Questions) — Загальні питання', 'level' => 'A1'],
                            ['title' => 'Wh-questions (Special Questions) — Спеціальні питання: who, what, where, when, why, how', 'level' => 'A1–A2'],
                            ['title' => 'Answers to Questions — Короткі і повні відповіді (Yes, I do / No, I don\'t)', 'level' => 'A1'],
                            ['title' => 'Alternative Questions — Альтернативні питання (coffee or tea?)', 'level' => 'A2'],
                            ['title' => 'Question Tags (Disjunctive Questions) — Розділові питання: …, don\'t you? […, isn\'t it?]', 'level' => 'B1'],
                            ['title' => 'Negative Questions — Заперечні питання (Don\'t you know…?)', 'level' => 'B1'],
                        ],
                    ],
                    ['title' => 'Question word order — порядок слів у питаннях', 'level' => 'A1–A2'],
                    ['title' => 'Subject vs object questions — who called you? vs who did you call?', 'level' => 'B1'],
                    ['title' => 'Indirect questions — Can you tell me…?', 'level' => 'B1–B2'],
                    ['title' => 'Negation in Simple — do/does/did + not', 'level' => 'A1–A2'],
                    ['title' => 'Negation with be, modals and have got', 'level' => 'A1–A2'],
                    ['title' => 'Negative pronouns and adverbs — nobody, nothing, nowhere', 'level' => 'A2–B1'],
                    ['title' => 'Double negatives — помилки типу I don\'t know nothing', 'level' => 'B1'],
                ],
            ],
            [
                'title' => '10. Прикметники та прислівники',
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
                'title' => '11. Умовні речення',
                'level' => 'A2–C1',
                'children' => [
                    ['title' => 'Zero Conditional', 'level' => 'A2–B1'],
                    ['title' => 'First Conditional', 'level' => 'A2–B1'],
                    ['title' => 'Second Conditional', 'level' => 'B1–B2'],
                    ['title' => 'Third Conditional', 'level' => 'B2'],
                    ['title' => 'Mixed Conditionals', 'level' => 'B2–C1'],
                    ['title' => 'Unless / Provided / As long as', 'level' => 'B1–B2'],
                    ['title' => 'In case', 'level' => 'B1–B2'],
                    ['title' => 'Wish / If only', 'level' => 'B1–B2'],
                    ['title' => 'Would rather', 'level' => 'B1–B2'],
                    ['title' => 'Had better', 'level' => 'A2–B1'],
                ],
            ],
            [
                'title' => '12. Переклад та типові помилки',
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
                'title' => '13. Просунута граматика та стиль (Advanced grammar & style)',
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
                'title' => '14. Пасивний стан (Passive Voice)',
                'level' => 'A2–C1',
                'children' => [
                    ['title' => 'Основні правила утворення пасиву', 'level' => 'A2'],
                    ['title' => 'Заперечення та питання у пасивному стані', 'level' => 'A2–B1'],
                    ['title' => 'Пасив з модальними дієсловами', 'level' => 'B1'],
                    ['title' => 'Get-пасив (get + V3)', 'level' => 'B1'],
                    ['title' => 'Каузатив (have/get something done)', 'level' => 'B1–B2'],
                    ['title' => 'Пасив двооб\'єктних дієслів', 'level' => 'B1–B2'],
                    ['title' => 'Безособовий пасив (Impersonal Passive)', 'level' => 'B1–B2'],
                    ['title' => 'Пасив фразових дієслів', 'level' => 'B2'],
                    ['title' => 'Обмеження вживання пасиву', 'level' => 'B2'],
                    ['title' => 'Формальність та стиль пасиву', 'level' => 'B2–C1'],
                    ['title' => 'Приклади вживання та типові помилки', 'level' => 'A2–B2'],
                    [
                        'title' => 'Пасив у різних часах',
                        'level' => 'A2–B2',
                        'children' => [
                            ['title' => 'Present Simple Passive — Теперішній простий пасив', 'level' => 'A2'],
                            ['title' => 'Present Continuous Passive — Теперішній тривалий пасив', 'level' => 'B1'],
                            ['title' => 'Present Perfect Passive — Теперішній доконаний пасив', 'level' => 'B1'],
                            ['title' => 'Past Simple Passive — Минулий простий пасив', 'level' => 'A2'],
                            ['title' => 'Past Continuous Passive — Минулий тривалий пасив', 'level' => 'B1'],
                            ['title' => 'Past Perfect Passive — Минулий доконаний пасив', 'level' => 'B1–B2'],
                            ['title' => 'Future Simple Passive — Майбутній простий пасив', 'level' => 'B1'],
                            ['title' => 'Future Continuous Passive — Майбутній тривалий пасив', 'level' => 'B2'],
                            ['title' => 'Future Perfect Passive — Майбутній доконаний пасив', 'level' => 'B2'],
                        ],
                    ],
                    [
                        'title' => 'Інфінітив та герундій у пасиві',
                        'level' => 'B2',
                        'children' => [
                            ['title' => 'Пасивний інфінітив — to be done / to have been done', 'level' => 'B2'],
                            ['title' => 'Пасивний герундій — being done / having been done', 'level' => 'B2'],
                        ],
                    ],
                ],
            ],
            [
                'title' => '15. Прийменники (Prepositions)',
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
                'title' => '16. Дискурс та текст (Discourse & Text)',
                'level' => 'C1–C2',
                'children' => [
                    ['title' => "Cohesion & coherence — зв'язність тексту", 'level' => 'C1'],
                    ['title' => 'Paragraph structure — topic sentence, supporting details, conclusion', 'level' => 'C1'],
                    ['title' => 'Signposting in texts — firstly, on the other hand, in conclusion…', 'level' => 'C1'],
                    ['title' => 'Punctuation nuances — крапка з комою, тире, дужки, лапки', 'level' => 'C2'],
                ],
            ],
            [
                'title' => '17. Reported Speech',
                'level' => 'B1–B2',
                'children' => [
                    ['title' => 'Reported Statements', 'level' => 'B1'],
                    ['title' => 'Reported Questions', 'level' => 'B1–B2'],
                    ['title' => 'Reported Commands and Requests', 'level' => 'B1–B2'],
                    ['title' => 'Backshift of Tenses', 'level' => 'B1–B2'],
                    ['title' => 'Time and Place Changes', 'level' => 'B1–B2'],
                    ['title' => 'Say / Tell / Ask', 'level' => 'B1–B2'],
                ],
            ],
            [
                'title' => '18. Clauses and Linking Words',
                'level' => 'B1–B2',
                'children' => [
                    ['title' => 'Relative Clauses', 'level' => 'B1'],
                    ['title' => 'Defining Relative Clauses', 'level' => 'B1'],
                    ['title' => 'Non-defining Relative Clauses', 'level' => 'B1–B2'],
                    ['title' => 'Omitting Relative Pronouns', 'level' => 'B1–B2'],
                    ['title' => 'Linking Words', 'level' => 'B1'],
                    ['title' => 'Reason: because / since / as', 'level' => 'B1'],
                    ['title' => 'Result: so / therefore / as a result', 'level' => 'B1–B2'],
                    ['title' => 'Contrast: although / though / however', 'level' => 'B1–B2'],
                    ['title' => 'Purpose: to / in order to / so that', 'level' => 'B1–B2'],
                ],
            ],
            [
                'title' => '19. Prepositions and Phrasal Verbs',
                'level' => 'A1–B2',
                'children' => [
                    ['title' => 'Prepositions of Time', 'level' => 'A1'],
                    ['title' => 'Prepositions of Place', 'level' => 'A1'],
                    ['title' => 'Prepositions of Movement', 'level' => 'A2–B1'],
                    ['title' => 'Dependent Prepositions', 'level' => 'B1–B2'],
                    ['title' => 'Phrasal Verbs Basics', 'level' => 'B1'],
                    ['title' => 'Separable and Inseparable Phrasal Verbs', 'level' => 'B1–B2'],
                    ['title' => 'Common Phrasal Verbs by Topic', 'level' => 'B1–B2'],
                ],
            ],
            [
                'title' => '20. Common Mistakes',
                'level' => 'A2–B2',
                'children' => [
                    ['title' => 'Articles: Common Mistakes', 'level' => 'A2–B1'],
                    ['title' => 'Word Order: Common Mistakes', 'level' => 'A2–B1'],
                    ['title' => 'Present Perfect: Common Mistakes', 'level' => 'A2–B1'],
                    ['title' => 'Countable vs Uncountable Nouns: Common Mistakes', 'level' => 'A2–B1'],
                    ['title' => 'Prepositions: Common Mistakes', 'level' => 'A2–B1'],
                    ['title' => 'B1/B2 Grammar Traps', 'level' => 'B1–B2'],
                ],
            ],
            [
                'title' => '21. Sentence Transformations',
                'level' => 'B1–C1',
                'children' => [
                    ['title' => 'Sentence Transformations — Tenses', 'level' => 'B1–B2'],
                    ['title' => 'Sentence Transformations — Modals', 'level' => 'B1–B2'],
                    ['title' => 'Sentence Transformations — Passive', 'level' => 'B1–B2'],
                    ['title' => 'Sentence Transformations — Reported Speech', 'level' => 'B1–B2'],
                    ['title' => 'Sentence Transformations — Conditionals', 'level' => 'B1–B2'],
                    ['title' => 'Sentence Transformations — Relative Clauses / Linking Words', 'level' => 'B1–B2'],
                    ['title' => 'Sentence Transformations — Articles / Quantifiers', 'level' => 'B1–B2'],
                    ['title' => 'Sentence Transformations — Word Order / Emphasis', 'level' => 'B2–C1'],
                ],
            ],
            [
                'title' => '22. Verb Patterns',
                'level' => 'A2–B2',
                'children' => [
                    ['title' => 'Gerund', 'level' => 'A2–B1'],
                    ['title' => 'To-Infinitive', 'level' => 'A2–B1'],
                    ['title' => 'Bare Infinitive', 'level' => 'A2–B1'],
                    ['title' => 'Gerund vs Infinitive', 'level' => 'B1'],
                    ['title' => 'Verbs + Gerund', 'level' => 'B1'],
                    ['title' => 'Verbs + Infinitive', 'level' => 'B1'],
                    ['title' => 'Stop / Remember / Forget / Try / Regret', 'level' => 'B1–B2'],
                    ['title' => 'Be used to / Get used to / Used to', 'level' => 'B1–B2'],
                ],
            ],
        ];
    }
}
