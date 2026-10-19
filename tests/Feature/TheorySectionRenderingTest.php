<?php

namespace Tests\Feature;

use App\Support\TheoryInlineHtml;
use App\Support\TheorySection;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\HtmlString;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TheorySectionRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:'] as $key => $expected) {
            if ((getenv($key) ?: ($_ENV[$key] ?? null)) !== $expected) {
                throw new \RuntimeException('Set '.$key.'='.$expected.' before rendering tests.');
            }
        }
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        \Tests\Support\IsolatedTestEnvironment::assertOwnedPath(config('view.compiled'));
        app()->setLocale('uk');
    }

    public function test_content_only_keeps_full_default_slot_without_disclosure(): void
    {
        $source = '<p id="old-anchor">No omissions: not 12 — Переклад.</p><details id="old-key"><summary>Відповіді</summary><p>Answer.</p></details>';
        $html = \Illuminate\Support\Facades\Blade::render('<x-theory-section section-key="existing" title="Existing" number="2">'.$source.'</x-theory-section>');
        $xpath = $this->xpath($html);
        $this->assertSame(0, $xpath->query('//*[@data-theory-details]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="old-anchor"]')->length);
        $this->assertSame(1, $xpath->query('//details[@id="old-key"][not(@open)]')->length);
        $this->assertStringContainsString($source, $html);
        $this->assertStringNotContainsString('Докладніше', $html);
    }

    public function test_valid_pair_is_server_dom_closed_independent_and_keeps_nested_answers(): void
    {
        [$full, $pair] = $this->fixturePair();
        $html = $this->render($full, $pair);
        $xpath = $this->xpath($html);
        $this->assertSame(1, $xpath->query('//details[@data-theory-details][not(@open)]')->length);
        $this->assertSame(1, $xpath->query('//details[@data-theory-details]//section[@id="fixture-source"]')->length);
        $this->assertSame(1, $xpath->query('//details[@id="fixture-answer"][not(@open)]')->length);
        $this->assertSame(0, $xpath->query('//template|//summary//button|//summary//a')->length);
        $this->assertSame(1, $xpath->query('//summary[contains(@class,"theory-section-toggle")]')->length);
        $this->assertStringContainsString('Докладніше', $html);
        $this->assertStringContainsString('Згорнути', $html);
        $this->assertStringNotContainsString('theory-section-toggle-arrow', $html);
        $this->assertStringNotContainsString('⌄', $html);
        $this->assertStringContainsString($pair['main']->toHtml(), $html);
        $this->assertStringContainsString($pair['detail']->toHtml(), $html);
        $ids = [];
        foreach ($xpath->query('//*[@id]') as $node) $ids[] = $node->getAttribute('id');
        $this->assertSame($ids, array_values(array_unique($ids)));
    }

    #[DataProvider('invalidPairs')]
    public function test_invalid_pairs_fallback_to_original_full_content_with_reason(string $case, string $reason): void
    {
        [$full, $pair] = $this->fixturePair();
        $pair = match ($case) {
            'unknown' => new \stdClass(),
            'key' => [...$pair, 'source_key' => 'another'],
            'revision' => [...$pair, 'source_revision' => str_repeat('0', 64)],
            'missing-main' => array_diff_key($pair, ['main' => true]),
            'empty-detail' => [...$pair, 'detail' => new HtmlString('<p> </p>')],
            'empty-entity-detail' => [...$pair, 'detail' => new HtmlString('<section id="fixture-source">&nbsp;</section>')],
            'refs' => [...$pair, 'detail_references' => []],
            'changed' => [...$pair, 'detail' => new HtmlString(str_replace('Past Simple', 'Past Changed', $pair['detail']->toHtml()))],
            'duplicate' => [...$pair, 'main' => new HtmlString('<p id="fixture-source">Main.</p>')],
            'control-collision' => [...$pair, 'main' => new HtmlString('<p id="theory-details-'.substr(hash('sha256', 'fixture'), 0, 20).'">Main.</p>')],
            'missing-ref' => [...$pair, 'detail_references' => ['not-found']],
        };
        $section = TheorySection::resolve('fixture', 'Existing', $full, null, $pair);
        $this->assertSame($reason, $section->diagnosticCode);
        $this->assertNull($section->detail);
        $this->assertSame($full->toHtml(), $section->main->toHtml());
        $html = $this->render($full, $pair);
        $this->assertStringContainsString($full->toHtml(), $html);
        $this->assertStringContainsString('data-theory-section-fallback="'.$reason.'"', $html);
        $this->assertSame(0, $this->xpath($html)->query('//*[@data-theory-details]')->length);
    }

    public static function invalidPairs(): array
    {
        return [
            ['unknown', 'invalid-pair-shape'], ['key', 'source-key-mismatch'],
            ['revision', 'source-revision-mismatch'], ['missing-main', 'missing-or-invalid-main'],
            ['empty-detail', 'empty-detail'], ['empty-entity-detail', 'empty-detail'], ['refs', 'missing-detail-references'],
            ['changed', 'detail-reference-changed'], ['duplicate', 'main-detail-anchor-collision'],
            ['missing-ref', 'detail-reference-not-found'],
            ['control-collision', 'generated-control-anchor-collision'],
        ];
    }

    public function test_plain_strings_titles_keys_and_numbers_are_escaped_without_entity_redecode(): void
    {
        $html = $this->render('<img src=x onerror=alert(1)> &amp;', null, '<em>Title</em> &amp;', 'unsafe" key', '<b>2</b>');
        $xpath = $this->xpath($html);
        $this->assertSame(0, $xpath->query('//img|//em|//b|//*[@onerror]')->length);
        $this->assertSame('<em>Title</em> &amp;', trim($xpath->query('//*[contains(@class,"theory-section-title")]')->item(0)->textContent));
        $this->assertStringContainsString('&amp;amp;', $html);
        $this->assertSame('unsafe" key', $xpath->query('//*[@data-theory-section]')->item(0)->getAttribute('data-theory-section'));
    }

    public function test_author_number_is_not_duplicated_and_heading_level_is_allowlisted(): void
    {
        $html = view('components.theory-section', ['sectionKey' => 'n', 'title' => '2. Форми', 'number' => 2, 'headingLevel' => 'script', 'fullContent' => 'Text'])->render();
        $xpath = $this->xpath($html);
        $this->assertSame(0, $xpath->query('//*[contains(@class,"theory-section-number")]')->length);
        $this->assertSame(1, $xpath->query('//h2[text()="2. Форми"]')->length);
        $this->assertSame(0, $xpath->query('//script')->length);
    }

    public function test_a_pair_without_a_title_context_preserves_full_content(): void
    {
        [$full, $pair] = $this->fixturePair();
        $section = TheorySection::resolve('fixture', '', $full, null, $pair);
        $this->assertSame('missing-title-context', $section->diagnosticCode);
        $this->assertNull($section->detail);
        $this->assertSame($full->toHtml(), $section->main->toHtml());
    }

    #[DataProvider('locales')]
    public function test_ui_translations_do_not_change_learning_content(string $locale, string $more, string $less): void
    {
        app()->setLocale($locale);
        [$full, $pair] = $this->fixturePair();
        $html = $this->render($full, $pair, '<strong>Контекст</strong>');
        $this->assertStringContainsString($more, $html);
        $this->assertStringContainsString($less, $html);
        $this->assertStringContainsString($pair['detail']->toHtml(), $html);
        $this->assertSame(0, $this->xpath($html)->query('//summary//strong')->length);
        $this->assertStringContainsString(' — Контекст', $html);
    }

    public static function locales(): array
    {
        return [['uk', 'Докладніше', 'Згорнути'], ['en', 'More details', 'Collapse'], ['pl', 'Więcej informacji', 'Zwiń']];
    }

    public function test_write_isolated_browser_fixture_with_two_independent_sections(): void
    {
        [$full, $pair] = $this->fixturePair();
        $first = $this->render($full, $pair, '1. Past Simple vs Past Continuous');
        $secondFull = new HtmlString(str_replace(['fixture-source', 'fixture-answer', 'fixture-deep'], ['second-source', 'second-answer', 'second-deep'], $full->toHtml()));
        $secondPair = [...$pair, 'source_key' => 'second', 'source_revision' => hash('sha256', $secondFull->toHtml()),
            'detail' => new HtmlString(str_replace(['fixture-source', 'fixture-answer', 'fixture-deep'], ['second-source', 'second-answer', 'second-deep'], $pair['detail']->toHtml())),
            'detail_references' => ['second-source']];
        $second = $this->render($secondFull, $secondPair, '2. Past Simple vs Past Continuous', 'second');
        $fixture = '<main data-theory-design><a id="outside-before" href="#fixture-source">Anchor</a>'.$first.$second.'<a id="outside-after" href="#second-source">Anchor</a></main>';
        $path = storage_path('app/m25-theory-section-fixture.html');
        \Tests\Support\IsolatedTestEnvironment::assertOwnedPath(dirname($path));
        file_put_contents($path, $fixture);
        \Tests\Support\IsolatedTestEnvironment::assertOwnedPath($path);
        $this->assertSame(2, $this->xpath($fixture)->query('//*[@data-theory-details]')->length);
    }

    private function render(string|HtmlString $full, mixed $pair = null, string $title = 'Existing', string $key = 'fixture', ?string $number = null): string
    {
        return view('components.theory-section', ['sectionKey' => $key, 'title' => $title, 'number' => $number, 'fullContent' => $full, 'pair' => $pair])->render();
    }

    public function test_native_html5_keeps_alpine_attributes_without_weakening_reference_validation(): void
    {
        $main = new HtmlString('<section id="basic"><button @click="expanded = !expanded">Теги</button></section>');
        $detail = new HtmlString('<section id="detail"><p>Had not been working — не працював.</p></section>');
        $full = new HtmlString($main->toHtml().$detail->toHtml());
        $pair = ['source_key'=>'native', 'source_revision'=>hash('sha256', $full->toHtml()),
            'main'=>$main, 'detail'=>$detail, 'detail_references'=>['detail']];
        $resolve = fn($f,$p)=>TheorySection::resolve('native', 'Форми', $f, null, $p, nativeHtml5: true);
        $valid = $resolve($full,$pair);
        self::assertNull($valid->diagnosticCode);
        self::assertSame($main->toHtml(), $valid->main->toHtml());
        self::assertSame($detail->toHtml(), $valid->detail->toHtml());
        self::assertSame('source-revision-mismatch', $resolve($full,[...$pair,'source_revision'=>str_repeat('0',64)])->diagnosticCode);
        self::assertSame('detail-reference-not-found', $resolve($full,[...$pair,'detail_references'=>['missing']])->diagnosticCode);
        self::assertSame('detail-reference-changed', $resolve($full,[...$pair,'detail'=>new HtmlString(str_replace('not ', '', $detail->toHtml()))])->diagnosticCode);
        self::assertSame('main-detail-anchor-collision', $resolve($full,[...$pair,'main'=>new HtmlString('<p id="detail">Main</p>')])->diagnosticCode);
        $broken = new HtmlString('<section><p><b>Broken</p></section>'.$detail->toHtml());
        self::assertSame('unparseable-full', $resolve($broken,[...$pair,'source_revision'=>hash('sha256',$broken->toHtml())])->diagnosticCode);
        // Legacy author-pair mode remains unchanged, rather than ignoring error 68.
        self::assertSame('unparseable-full', TheorySection::resolve('native','Форми',$full,null,$pair)->diagnosticCode);
    }

    private function fixturePair(): array
    {
        // Exact current snippets; a technical pair, not an approved short lesson.
        $definition = json_decode(file_get_contents(base_path('database/seeders/Page_V3/Tenses/TensesPastSimpleVsPastContinuousTheorySeeder/definition.json')), true, 512, JSON_THROW_ON_ERROR);
        $hero = json_decode($definition['page']['blocks'][0]['body'], true, 512, JSON_THROW_ON_ERROR);
        $main = new HtmlString('<p>'.TheoryInlineHtml::render($hero['rules'][0]['text']).'</p>');
        $detail = new HtmlString('<section id="fixture-source"><p>'.TheoryInlineHtml::render($hero['intro']).'</p><details id="fixture-answer"><summary>Відповіді</summary><p id="fixture-deep">'.TheoryInlineHtml::render($hero['rules'][0]['example']).'</p></details></section>');
        $full = new HtmlString($main->toHtml().$detail->toHtml());
        return [$full, ['source_key' => 'fixture', 'source_revision' => hash('sha256', $full->toHtml()), 'main' => $main, 'detail' => $detail, 'detail_references' => ['fixture-source']]];
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return new DOMXPath($dom);
    }
}
