<?php

namespace Tests\Feature;

use App\Models\Page;
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

    public function test_desktop_has_one_shell_and_accessible_distinct_lesson_and_topics_panels(): void
    {
        $page = $this->lesson();
        $attributes = $page->textBlocks->map->getAttributes()->all();
        $html = $this->render($page);
        $xpath = $this->xpath($html);
        $shells = $xpath->query('//*[@data-theory-sidebar-shell]');
        $this->assertSame(1, $shells->length);
        $shell = $shells->item(0);
        $this->assertSame('lesson', $shell->getAttribute('data-theory-sidebar-default'));
        $tabs = $xpath->query('.//*[@role="tab"]', $shell);
        $this->assertSame(2, $tabs->length);
        $this->assertSame(2, $xpath->query('.//*[@role="tabpanel"]', $shell)->length);
        $kinds = [];
        foreach ($tabs as $tab) {
            $kinds[] = $tab->getAttribute('data-theory-sidebar-tab');
            $this->assertNotSame('', $tab->getAttribute('id'));
            $controlled = $tab->getAttribute('aria-controls');
            $this->assertMatchesRegularExpression('/^[A-Za-z][A-Za-z0-9_-]*$/D', $controlled);
            $panels = $xpath->query('.//*[@id="'.$controlled.'" and @role="tabpanel"]', $shell);
            $this->assertSame(1, $panels->length);
            $this->assertSame($tab->getAttribute('id'), $panels->item(0)->getAttribute('aria-labelledby'));
        }
        sort($kinds);
        $this->assertSame(['lesson', 'topics'], $kinds);
        $this->assertSame(1, $xpath->query('.//*[@data-theory-desktop-navigation-loader]', $shell)->length);
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

    public function test_topics_is_the_default_when_a_lesson_has_no_contents_headings(): void
    {
        $html = $this->render($this->lesson(false));
        $xpath = $this->xpath($html);
        $shell = $xpath->query('//*[@data-theory-sidebar-shell]')->item(0);
        $this->assertNotNull($shell);
        $this->assertSame('topics', $shell->getAttribute('data-theory-sidebar-default'));
        $this->assertSame(1, $xpath->query('.//*[@data-theory-desktop-navigation-loader]', $shell)->length);
        $this->assertSame(0, $xpath->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-mobile-toc ")]')->length);
        $this->assertStringContainsString('Do not remove 42 examples.', $html);
        $this->assertStringNotContainsString('data-theory-details', $html);
    }
}
