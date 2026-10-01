<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
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

    public function test_desktop_has_two_visible_sibling_cards_topics_first_without_tabs(): void
    {
        $page = $this->lesson();
        $attributes = $page->textBlocks->map->getAttributes()->all();
        $html = $this->render($page);
        $xpath = $this->xpath($html);
        $shells = $xpath->query('//*[@data-theory-sidebar-shell]');
        $this->assertSame(1, $shells->length);
        $shell = $shells->item(0);
        $cards = $xpath->query('./section[@data-theory-sidebar-card]', $shell);
        $this->assertSame(2, $cards->length);
        $this->assertSame('topics', $cards->item(0)->getAttribute('data-theory-sidebar-card'));
        $this->assertSame('lesson', $cards->item(1)->getAttribute('data-theory-sidebar-card'));
        $this->assertSame(0, $xpath->query('.//*[@role="tablist" or @role="tab" or @role="tabpanel" or @data-theory-sidebar-tab]', $shell)->length);
        foreach ($cards as $card) {
            $kind = $card->getAttribute('data-theory-sidebar-card');
            $panels = $xpath->query('.//*[@data-theory-sidebar-panel="'.$kind.'"]', $card);
            $this->assertSame(1, $panels->length);
            $this->assertFalse($card->hasAttribute('hidden'));
            $this->assertFalse($panels->item(0)->hasAttribute('hidden'));
            $this->assertSame(1, $xpath->query('.//h2', $card)->length);
        }
        $topics = $cards->item(0);
        $contents = $cards->item(1);
        $this->assertStringContainsString(__('public.common.categories'), $xpath->query('.//h2', $topics)->item(0)->textContent);
        $this->assertStringContainsString(__('theory_blocks.section.contents'), $xpath->query('.//h2', $contents)->item(0)->textContent);
        $this->assertSame(1, $xpath->query('.//*[@data-theory-desktop-navigation-loader]', $topics)->length);
        $this->assertSame(1, $xpath->query('.//*[@data-theory-sidebar-collapse]', $topics)->length);
        $this->assertSame(0, $xpath->query('.//*[@data-theory-sidebar-collapse]', $contents)->length);
        $this->assertSame(2, $xpath->query('.//*[@data-theory-toc-links]//a', $contents)->length);
        $this->assertStringContainsString('!theorySidebarCollapsed', $contents->getAttribute('x-show'));
        $shellHtml = $shell->ownerDocument->saveHTML($shell);
        $this->assertStringNotContainsString('pane ===', $shellHtml);
        $this->assertStringNotContainsString('selectPane', $shellHtml);
        $this->assertSame(1, $xpath->query('.//noscript//a[@href="'.localized_route('theory.index').'"]', $shell)->length);
        $this->assertSame($attributes, $page->textBlocks->map->getAttributes()->all());
    }

    public function test_sidebar_refactor_keeps_complete_learning_dom_old_anchors_and_mobile_contents(): void
    {
        $html = $this->render($this->lesson());
        $xpath = $this->xpath($html);
        $main = $xpath->query('//*[@data-theory-main]')->item(0);
        $this->assertNotNull($main);
        foreach (['Do not remove 42 examples.', 'We have been working.', 'Ми працюємо вже деякий час.', 'Keep every original explanation.'] as $text) {
            $this->assertStringContainsString($text, $main->textContent);
        }
        foreach (['block-711', 'block-712'] as $id) {
            $this->assertSame(1, $xpath->query('//*[@id="'.$id.'"]')->length);
        }
        $this->assertSame(1, $xpath->query('//h1')->length);
        $this->assertSame(1, $xpath->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-mobile-toc ")]')->length);
        $this->assertStringNotContainsString('data-theory-details', $html);
        $this->assertStringNotContainsString('const updateTheoryTocPin', $html);
        $this->assertStringNotContainsString("card.style.position = 'fixed'", $html);
    }

    public function test_lesson_without_contents_headings_keeps_only_the_topics_card(): void
    {
        $html = $this->render($this->lesson(false));
        $xpath = $this->xpath($html);
        $shell = $xpath->query('//*[@data-theory-sidebar-shell]')->item(0);
        $this->assertNotNull($shell);
        $this->assertSame(1, $xpath->query('./section[@data-theory-sidebar-card="topics"]', $shell)->length);
        $this->assertSame(0, $xpath->query('.//*[@data-theory-sidebar-card="lesson"]', $shell)->length);
        $this->assertSame(0, $xpath->query('.//*[@role="tablist" or @role="tab" or @role="tabpanel"]', $shell)->length);
        $this->assertSame(1, $xpath->query('.//*[@data-theory-desktop-navigation-loader]', $shell)->length);
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
        $shells = $xpath->query('//*[@data-theory-sidebar-shell]');
        $this->assertSame(1, $shells->length);
        $shell = $shells->item(0);
        $this->assertSame(1, $xpath->query('./section[@data-theory-sidebar-card="topics"]', $shell)->length);
        $this->assertSame(0, $xpath->query('.//*[@data-theory-sidebar-card="lesson" or @data-theory-sidebar-panel="lesson" or @data-theory-toc-links]', $shell)->length);
        $this->assertSame(0, $xpath->query('.//*[@role="tablist" or @role="tab" or @role="tabpanel"]', $shell)->length);
        $this->assertSame(1, $xpath->query('.//*[@data-theory-desktop-navigation-loader]', $shell)->length);
        $this->assertSame(1, $xpath->query('.//noscript//a[@href="'.localized_route('theory.index').'"]', $shell)->length);
        $this->assertSame(1, $xpath->query('//h1')->length);
        $this->assertStringContainsString('Навігаційна категорія', $xpath->query('//*[@data-theory-main]')->item(0)->textContent);
    }
}
