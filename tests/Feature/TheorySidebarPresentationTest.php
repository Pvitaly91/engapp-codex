<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use DOMDocument;
use DOMXPath;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

class TheorySidebarPresentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        IsolatedTestEnvironment::assertSafeDatabase(\Illuminate\Support\Facades\DB::connection());
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->withoutVite();
        app()->setLocale('uk');
    }

    private function lesson(bool $titles = true): Page
    {
        $page = new Page;
        $page->setRawAttributes(['id' => 99, 'title' => 'Повний тестовий урок', 'slug' => 'sidebar-fixture', 'type' => 'theory'], true);
        $page->setRelation('tags', collect());
        $page->setRelation('category', null);
        $bodies = [
            ['type' => 'usage-panels', 'data' => [
                'title' => $titles ? '1. Повне пояснення' : '',
                'sections' => [['description' => 'Do not remove 42 examples.', 'examples' => [['en' => 'We have been working.', 'ua' => 'Ми працюємо вже деякий час.']]]],
            ]],
            ['type' => 'summary-list', 'data' => [
                'title' => $titles ? '2. Авторський висновок' : '',
                'items' => ['Keep every original explanation.'],
            ]],
        ];
        $page->setRelation('textBlocks', collect($bodies)->map(function (array $item, int $index): TextBlock {
            $block = new TextBlock;
            $block->setRawAttributes(['id' => 711 + $index, 'uuid' => 'sidebar-block-'.$index,
                'type' => $item['type'], 'body' => json_encode($item['data'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'sort_order' => $index], true);
            $block->setRelation('tags', collect());
            return $block;
        }));
        return $page;
    }

    private function render(Page $page): string
    {
        return view('theory.show', ['page' => $page, 'categories' => collect(),
            'categoryPages' => collect(), 'topicTests' => collect()])->render();
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        return new DOMXPath($dom);
    }

    public function test_desktop_restores_the_legacy_menu_and_separate_numbered_contents(): void
    {
        $page = $this->lesson();
        $attributes = $page->textBlocks->map->getAttributes()->all();
        $html = $this->render($page);
        $xpath = $this->xpath($html);
        $sidebars = $xpath->query('//*[@data-theory-sidebar-legacy]');
        $this->assertSame(1, $sidebars->length);
        $sidebar = $sidebars->item(0);
        $menu = $xpath->query('./section[@data-theory-sidebar]', $sidebar)->item(0);
        $this->assertNotNull($menu);
        foreach (['rounded-[28px]', 'p-4', 'xl:p-5', 'shadow-card', 'surface-card-strong'] as $class) {
            $this->assertStringContainsString($class, $menu->getAttribute('class'));
        }
        $this->assertStringContainsString(__('frontend.copilot_theory.map'), $menu->textContent);
        $heading = $xpath->query('.//h2', $menu)->item(0);
        $this->assertStringContainsString(__('public.common.categories'), $heading->textContent);
        $this->assertStringContainsString('text-xl', $heading->getAttribute('class'));
        $this->assertSame(1, $xpath->query('.//*[@data-theory-desktop-navigation-loader]', $menu)->length);
        $this->assertSame(1, $xpath->query('.//button[contains(@class,"h-10") and contains(@class,"w-10")]', $menu)->length);
        $pin = $xpath->query('./*[@data-theory-toc-pin-root]', $sidebar)->item(0);
        $this->assertNotNull($pin);
        $this->assertStringContainsString('!theorySidebarCollapsed', $pin->getAttribute('x-show'));
        $this->assertStringContainsString('theorySidebarSettled', $pin->getAttribute('x-show'));
        $contents = $xpath->query('./section[@data-theory-toc-card]', $pin)->item(0);
        $this->assertNotNull($contents);
        $this->assertStringContainsString('rounded-[28px]', $contents->getAttribute('class'));
        $this->assertStringContainsString(__('frontend.copilot_theory.contents'), $contents->textContent);
        $links = $xpath->query('.//*[@data-theory-toc-links]//a', $contents);
        $this->assertSame(2, $links->length);
        foreach ($links as $index => $link) {
            $this->assertSame('#block-'.(711 + $index), $link->getAttribute('href'));
            $this->assertStringContainsString('px-3 py-3 text-sm', $link->getAttribute('class'));
            $badge = $xpath->query('./span[contains(@class,"bg-amber")]', $link)->item(0);
            $this->assertNotNull($badge);
            $this->assertStringContainsString('h-7 w-7', $badge->getAttribute('class'));
            $this->assertSame((string) ($index + 1), trim($badge->textContent));
        }
        $this->assertSame(0, $xpath->query('//*[@data-theory-sidebar-shell or @data-theory-sidebar-card or @data-theory-sidebar-panel or @data-theory-sidebar-tab or @role="tablist" or @role="tab" or @role="tabpanel"]')->length);
        $this->assertSame($attributes, $page->textBlocks->map->getAttributes()->all());
    }

    public function test_sidebar_refactor_keeps_complete_learning_dom_old_anchors_and_mobile_contents(): void
    {
        $html = $this->render($this->lesson());
        $xpath = $this->xpath($html);
        $main = $xpath->query('//*[@data-theory-main]')->item(0);
        $this->assertNotNull($main);
        $this->assertStringContainsString('theory-design', $main->getAttribute('class'));
        $this->assertSame(0, $xpath->query('//*[@data-theory-aside]/ancestor::*[contains(concat(" ",normalize-space(@class)," ")," theory-design ")]')->length);
        foreach (['Do not remove 42 examples.', 'We have been working.', 'Ми працюємо вже деякий час.', 'Keep every original explanation.'] as $text) {
            $this->assertStringContainsString($text, $main->textContent);
        }
        foreach (['block-711', 'block-712'] as $id) {
            $this->assertSame(1, $xpath->query('//*[@id="'.$id.'"]')->length);
        }
        $this->assertSame(1, $xpath->query('//h1')->length);
        $this->assertSame(1, $xpath->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-mobile-toc ")]')->length);
        $this->assertStringNotContainsString('data-theory-details', $html);
    }

    public function test_lesson_without_contents_headings_keeps_only_the_topics_card(): void
    {
        $html = $this->render($this->lesson(false));
        $xpath = $this->xpath($html);
        $sidebar = $xpath->query('//*[@data-theory-sidebar-legacy]')->item(0);
        $this->assertNotNull($sidebar);
        $this->assertSame(1, $xpath->query('./section[@data-theory-sidebar]', $sidebar)->length);
        $this->assertSame(0, $xpath->query('.//*[@data-theory-toc-card or @data-theory-toc-pin-root]', $sidebar)->length);
        $this->assertSame(0, $xpath->query('.//*[@role="tablist" or @role="tab" or @role="tabpanel"]', $sidebar)->length);
        $this->assertSame(1, $xpath->query('.//*[@data-theory-desktop-navigation-loader]', $sidebar)->length);
        $this->assertSame(0, $xpath->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-mobile-toc ")]')->length);
        $this->assertStringContainsString('Do not remove 42 examples.', $html);
        $this->assertStringNotContainsString('data-theory-details', $html);
    }

    public function test_category_keeps_topics_only_and_a_non_javascript_navigation_fallback(): void
    {
        $category = new PageCategory;
        $category->setRawAttributes(['id' => 98, 'title' => 'Навігаційна категорія', 'slug' => 'sidebar-category', 'recursive_pages_count' => 0], true);
        $category->setRelation('tags', collect());
        $category->setRelation('parent', null);
        $html = view('theory.category', ['selectedCategory' => $category, 'categories' => collect(),
            'categoryPages' => collect(), 'categoryDescription' => ['hasBlocks' => false]])->render();
        $xpath = $this->xpath($html);
        $sidebars = $xpath->query('//*[@data-theory-sidebar-legacy]');
        $this->assertSame(1, $sidebars->length);
        $sidebar = $sidebars->item(0);
        $this->assertStringContainsString('sticky top-24', $sidebar->getAttribute('class'));
        $this->assertSame(1, $xpath->query('./section[@data-theory-sidebar]', $sidebar)->length);
        $this->assertSame(0, $xpath->query('.//*[@data-theory-toc-card or @data-theory-toc-pin-root or @data-theory-toc-links]', $sidebar)->length);
        $this->assertSame(0, $xpath->query('.//*[@role="tablist" or @role="tab" or @role="tabpanel"]', $sidebar)->length);
        $this->assertSame(1, $xpath->query('.//*[@data-theory-desktop-navigation-loader]', $sidebar)->length);
        $this->assertSame(1, $xpath->query('.//noscript//a[@href="'.localized_route('theory.index').'"]', $sidebar)->length);
        $this->assertSame(1, $xpath->query('//h1')->length);
        $this->assertStringContainsString('Навігаційна категорія', $xpath->query('//*[@data-theory-main]')->item(0)->textContent);
    }

    public function test_page_tags_are_a_separate_legacy_card_not_a_disclosure_or_contents_footer(): void
    {
        $page = $this->lesson();
        $tag = new Tag;
        $tag->setRawAttributes(['id' => 27, 'name' => 'Present Perfect'], true);
        $page->setRelation('tags', collect([$tag]));
        $xpath = $this->xpath($this->render($page));
        $tags = $xpath->query('//*[@data-theory-sidebar-legacy]/section[not(@data-theory-sidebar)]');
        $this->assertSame(1, $tags->length);
        $card = $tags->item(0);
        $this->assertStringContainsString(__('public.common.page_tags'), $card->textContent);
        $this->assertStringContainsString('rounded-[28px]', $card->getAttribute('class'));
        $this->assertStringContainsString('!theorySidebarCollapsed', $card->getAttribute('x-show'));
        $pill = $xpath->query('.//span[contains(@class,"rounded-full")]', $card)->item(0);
        $this->assertNotNull($pill);
        $this->assertStringContainsString('px-3 py-1.5 text-xs font-bold', $pill->getAttribute('class'));
        $this->assertStringContainsString('Present Perfect', $pill->textContent);
        $this->assertSame(0, $xpath->query('.//details', $card)->length);
    }

    public function test_desktop_navigation_fragment_keeps_the_original_search_icons_counts_and_card_links(): void
    {
        $category = new PageCategory;
        $category->setRawAttributes(['id' => 98, 'title' => 'Present Perfect', 'slug' => 'present-perfect', 'pages_count' => 4], true);
        $category->setRelation('children', collect());
        $category->setRelation('parent', null);
        $category->setRelation('ordered_tree_items', collect());
        $html = view('theory.partials.mobile-navigation-content', ['variant' => 'desktop',
            'categories' => collect([$category]), 'selectedCategory' => $category, 'currentPage' => null, 'routePrefix' => 'theory'])->render();
        $xpath = $this->xpath($html);
        $input = $xpath->query('//*[@id="theory-sidebar-search-desktop" and @data-theory-sidebar-search-input]')->item(0);
        $this->assertNotNull($input);
        $this->assertSame('search', $input->getAttribute('type'));
        $this->assertSame('off', $input->getAttribute('autocomplete'));
        $search = $xpath->query('//*[@data-theory-sidebar-search]')->item(0);
        $this->assertStringContainsString('rounded-[24px] border p-3', $search->getAttribute('class'));
        $this->assertStringContainsString(__('public.theory.category_search_label'), $search->textContent);
        $this->assertSame('1 / 1', trim($xpath->query('//*[@data-theory-sidebar-search-count]')->item(0)->textContent));
        $link = $xpath->query('//a[@data-theory-nav-active="true"]')->item(0);
        $this->assertNotNull($link);
        $this->assertSame(localized_route('theory.category', $category->slug), $link->getAttribute('href'));
        $this->assertStringContainsString('rounded-[22px] border px-3.5 py-3.5', $link->getAttribute('class'));
        $this->assertSame(1, $xpath->query('.//*[contains(@class,"theory-nav-icon")]//svg', $link)->length);
        $this->assertSame('4', trim($xpath->query('.//*[contains(@class,"theory-nav-count")]', $link)->item(0)->textContent));
        $this->assertSame(1, $xpath->query('//*[@data-theory-sidebar-scroll and contains(@class,"overflow-y-auto")]')->length);
    }
}
