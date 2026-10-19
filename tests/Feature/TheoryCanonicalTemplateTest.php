<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M45FutureComparisonsPackage;
use App\Support\TheoryAuthoredAdapter;
use App\Support\TheoryComponents;
use App\Support\TheoryLegacyAdapter;
use App\Support\TheorySection;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Architecture and content contracts; all framework work uses isolated in-memory fixtures. */
class TheoryCanonicalTemplateTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const FROZEN_PACKAGES = [
        \App\Support\M27LinkingWordsPackage::class, \App\Support\M28EmphasisPackage::class,
        \App\Support\M29SentenceStructurePackage::class, \App\Support\M30ParticipleClausesPackage::class,
        \App\Support\M31ConditionalsPackage::class, \App\Support\M32FormalEnglishPackage::class,
        \App\Support\M33AcademicEnglishPackage::class, \App\Support\M34ArgumentationCohesionPackage::class,
        \App\Support\M35PassiveReportingPackage::class, \App\Support\M36ModalsSubjunctivePackage::class,
        \App\Support\M37GrammarStructuresPackage::class, \App\Support\M38ArticlesCollocationsPackage::class,
        \App\Support\M39AuthoredRevisionPackage::class, \App\Support\M40TensesB1Package::class,
        \App\Support\M41AuthoredTenseComparisonsPackage::class, \App\Support\M43AuthoredTenseUsagePackage::class,
        \App\Support\M44AuthoredFutureFormsPackage::class, M45FutureComparisonsPackage::class,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        app()->setLocale('uk');
        $this->withoutVite();
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $old = libxml_use_internal_errors(true);
        $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($old);

        return new DOMXPath($document);
    }

    /** Keep structure, classes, accessibility and learning text. Only source identity is technical. */
    private function comparableDom(string $html): array
    {
        $body = $this->xpath($html)->query('//body')->item(0);
        $walk = function (DOMNode $node) use (&$walk): mixed {
            if ($node->nodeType === XML_TEXT_NODE) {
                $text = trim(preg_replace('/\s+/u', ' ', $node->textContent));

                return $text === '' ? null : ['text' => $text];
            }
            if (!$node instanceof DOMElement) { return null; }
            $attributes = [];
            foreach ($node->attributes as $attribute) {
                if ($attribute->name === 'id' || preg_match('/^data-m\d+-/', $attribute->name)) { continue; }
                $attributes[$attribute->name] = $attribute->value;
            }
            ksort($attributes);
            $children = [];
            foreach ($node->childNodes as $child) {
                $value = $walk($child);
                if ($value !== null) { $children[] = $value; }
            }

            return ['tag' => $node->tagName, 'attributes' => $attributes, 'children' => $children];
        };

        return $walk($body);
    }

    private function block(array $target, int $slot): TextBlock
    {
        $config = $target['after']['page']['blocks'][$slot];
        $block = new TextBlock;
        $block->forceFill(['id' => 45100 + $slot, 'page_id' => 451, 'page_category_id' => 45,
            'uuid' => M26DetailPackage::uuid($target['identity'], $config, $slot + 1),
            'seeder' => $target['identity'], 'locale' => 'uk', 'type' => $config['type'], 'sort_order' => $slot + 1,
            'body' => $config['body'], 'column' => $config['column'] ?? 'left', 'level' => $config['level'] ?? null]);
        $block->setRelation('tags', collect());
        $block->setRelation('page', null);

        return $block;
    }

    private function renderBlock(TextBlock $block): string
    {
        return view('theory.partials.content-block', ['block' => $block, 'theoryCanonical' => true,
            'm45StyleContext' => true, 'm44StyleContext' => true, 'm43StyleContext' => true, 'm42StyleContext' => true])->render();
    }

    public function test_equivalent_legacy_and_authored_examples_share_the_exact_dom_not_only_colors(): void
    {
        $block = new TextBlock;
        $block->forceFill(['id' => 1, 'type' => 'usage-panels', 'level' => 'B1']);
        $block->setRelation('tags', collect());
        $legacy = TheoryLegacyAdapter::section($block, ['title' => '1. Правило', 'sections' => [[
            'label' => 'Перед іншою подією', 'color' => 'emerald', 'description' => '<p lang="uk">Пояснення.</p>',
            'examples' => [['en' => 'They had been waiting for an hour.', 'ua' => 'Вони чекали вже годину.']],
        ]]]);
        $authored = TheoryAuthoredAdapter::section($block, ['title' => '1. Правило', 'author_section' => [
            'id' => 'fixture-section', 'title' => 'Правило', 'kind' => 'usage', 'points' => [[
                'id' => 'fixture-point', 'title' => 'Перед іншою подією', 'basic_uk' => ['Пояснення.'],
                'examples' => [['en' => 'They had been waiting for an hour.', 'uk' => 'Вони чекали вже годину.']],
            ]],
        ]]);
        $legacyExample = $legacy['items'][0]['examples'][0];
        $authorExample = $authored['items'][0]['examples'][0];
        self::assertSame('example', $legacyExample['kind']);
        self::assertSame('example', $authorExample['kind']);
        self::assertSame($this->comparableDom(TheoryComponents::html($legacyExample)), $this->comparableDom(TheoryComponents::html($authorExample)));
        self::assertSame($this->comparableDom(TheoryComponents::html($legacy['items'][0])), $this->comparableDom(TheoryComponents::html($authored['items'][0])), 'Whole rule point keeps identical structure/classes/ARIA for equivalent normalized meaning');
        self::assertSame(TheoryComponents::html($authorExample), TheoryAuthoredAdapter::render($authorExample));
    }

    public function test_component_classes_structure_and_aria_do_not_depend_on_package_provenance(): void
    {
        foreach ([
            'usage-panels' => ['plain', 'border-border/60 bg-card'],
            'summary-list' => ['summary', 'border-emerald-200/60 bg-gradient-to-br from-emerald-50/30 to-card'],
            'mistakes-grid' => ['mistakes', 'border-rose-200/60 bg-gradient-to-br from-rose-50/50 to-card'],
        ] as $type => [$variant, $surface]) {
            $block = new TextBlock;
            $block->forceFill(['id' => 7, 'type' => $type, 'level' => 'B1']);
            $block->setRelation('tags', collect());
            $legacy = TheoryLegacyAdapter::section($block, ['title' => '1. Розділ', 'items' => [], 'sections' => []]);
            $authored = TheoryAuthoredAdapter::section($block, ['title' => '1. Розділ', 'author_section' => [
                'id' => 'fixture-shell', 'title' => 'Розділ', 'native_kind' => $type, 'points' => [],
            ]]);
            foreach ([$legacy, $authored] as $section) {
                self::assertSame($variant, $section['variant']);
                self::assertSame('theory-section-card rounded-2xl border '.$surface,
                    $this->xpath(TheoryComponents::html($section))->query('//section[@data-theory-component="section"]/div[@class]')->item(0)->getAttribute('class'));
                $expected = $this->comparableDom(TheoryComponents::html($section));
                foreach ([27, 41, 42, 43, 44, 45, 99] as $package) {
                    $tagged = $section;
                    $tagged['attrs'] = ['data-m'.$package.'-source' => 'fixture', 'class' => 'package-surface', 'style' => 'background:red'];
                    self::assertSame($expected, $this->comparableDom(TheoryComponents::html($tagged)), $type.' shell package '.$package);
                }
            }
        }
        $examples = [['kind' => 'example', 'en' => 'I had been reading.', 'uk' => 'Я читав.']];
        $nodes = [
            ['kind' => 'usage', 'label' => 'Процес', 'number' => 1, 'accent' => 'emerald', 'body_html' => new HtmlString('<p>Пояснення.</p>'), 'examples' => $examples],
            ['kind' => 'form', 'label' => 'Повна форма', 'title' => 'had been + V-ing', 'examples' => $examples],
            $examples[0],
            ['kind' => 'note', 'html' => 'Зауваження.', 'variant' => 'plain'],
            ['kind' => 'table', 'caption' => 'Порівняння', 'headers' => ['Форма', 'Значення'], 'rows' => [['cells' => ['had been', 'Процес']]]],
            ['kind' => 'summary', 'items' => [['html' => 'Зберігай часовий зв’язок.']]],
            ['kind' => 'fragment', 'title' => 'Пояснення', 'body_html' => 'Текст.', 'examples' => $examples],
            ['kind' => 'group', 'layout' => 'grid2', 'items' => $examples],
            ['kind' => 'correction', 'variant' => 'right', 'text' => 'She had been working.', 'lang' => 'en'],
        ];
        foreach ($nodes as $node) {
            $expected = $this->comparableDom(TheoryComponents::html($node));
            foreach ([27, 41, 42, 43, 44, 45, 99] as $package) {
                $withProvenance = $node + ['id' => 'source-'.$package,
                    'attrs' => ['data-m'.$package.'-source' => 'fixture', 'class' => 'm'.$package.'-different-design', 'style' => 'color:red'],
                    'package' => $package, 'view' => 'layouts.catalog-public'];
                self::assertSame($expected, $this->comparableDom(TheoryComponents::html($withProvenance)), $node['kind'].' package '.$package);
            }
        }
    }

    public function test_dispatch_and_attributes_are_allowlisted_and_payload_markup_is_escaped(): void
    {
        $html = TheoryComponents::html(['kind' => '../../layouts/catalog-public', 'view' => 'layouts.catalog-public',
            'text' => '<script>alert(1)</script> — повний доступний текст']);
        $xpath = $this->xpath($html);
        self::assertSame(0, $xpath->query('//script')->length);
        self::assertStringContainsString('<script>alert(1)</script> — повний доступний текст', $xpath->query('//body')->item(0)->textContent);
        $attrs = TheoryComponents::attrs(['data-source' => '"><script>alert(1)</script>', 'aria-label' => 'Форма',
            'class' => 'foreign-design', 'style' => 'display:none', 'onclick' => 'bad()', 'data-invalid space' => 'bad']);
        $xpath = $this->xpath('<div'.$attrs.'>Текст</div>');
        self::assertSame(0, $xpath->query('//script|//*[@class]|//*[@style]|//*[@onclick]')->length);
        self::assertSame('Форма', $xpath->query('//*[@aria-label]')->item(0)->getAttribute('aria-label'));
        self::assertSame(1, $xpath->query('//*[@data-source]')->length);
    }

    public function test_canonical_disclosure_uses_the_existing_point_wrapper_and_reference_validation(): void
    {
        $main = new HtmlString('<p>Основне пояснення.</p>');
        $detail = new HtmlString('<section id="detail-source"><p>Додаткове пояснення й приклад.</p></section>');
        $full = new HtmlString($main.$detail);
        $pair = ['source_key' => 'same-point', 'source_revision' => hash('sha256', $full),
            'main' => $main, 'detail' => $detail, 'detail_references' => ['detail-source']];
        $section = TheorySection::resolve('same-point', 'Правило', $full, null, $pair, nativeHtml5: true);
        self::assertNull($section->diagnosticCode);
        $canonical = TheoryComponents::html(['kind' => 'disclosure', 'section' => $section, 'index' => 2]);
        $existing = view('theory.partials.point-disclosure', ['pointSections' => [2 => $section], 'index' => 2])->render();
        self::assertSame($this->comparableDom($existing), $this->comparableDom($canonical));
        $xpath = $this->xpath($canonical);
        self::assertSame(1, $xpath->query('//details[not(@open)]')->length);
        self::assertSame(1, $xpath->query('//details//section[@id="detail-source"]')->length);
        self::assertSame(0, $xpath->query('//summary//button|//summary//a|//details//details')->length);
        $bad = TheorySection::resolve('same-point', 'Правило', $full, null,
            [...$pair, 'detail' => new HtmlString(str_replace('Додаткове', 'Чуже', $detail->toHtml()))], nativeHtml5: true);
        self::assertSame('detail-reference-changed', $bad->diagnosticCode);
        self::assertNull($bad->detail);
        self::assertSame($full->toHtml(), $bad->main->toHtml());
    }

    public function test_native_and_authored_tables_preserve_every_column_translation_and_note(): void
    {
        $block = new TextBlock;
        $block->forceFill(['id' => 2, 'type' => 'comparison-table', 'level' => 'B2']);
        $block->setRelation('tags', collect());
        $data = ['title' => 'Порівняння', 'headers' => ['Речення', 'Переклад', 'Примітка'], 'rows' => [
            ['en' => 'They had been waiting.', 'ua' => 'Вони чекали.', 'note' => 'Процес до іншої події.'],
        ], 'table_min_width' => 680, 'column_min_widths' => [240, 240, 200],
            'outro' => 'Не плутай попередній процес і результат.', 'warning' => 'Контекст визначає значення.'];
        $legacy = TheoryLegacyAdapter::section($block, $data);
        $html = TheoryComponents::html($legacy['items'][0]);
        $xpath = $this->xpath($html);
        self::assertSame(3, $xpath->query('//th')->length);
        self::assertSame(3, $xpath->query('//td')->length);
        foreach (['Речення', 'Переклад', 'Примітка', 'They had been waiting.', 'Вони чекали.', 'Процес до іншої події.'] as $text) {
            self::assertStringContainsString($text, $xpath->query('//body')->item(0)->textContent);
        }
        self::assertSame(1, $xpath->query('//*[@role="region" and @tabindex="0" and @aria-label="Порівняння"]')->length);
        self::assertSame('min-width: 680px', $xpath->query('//table')->item(0)->getAttribute('style'));
        foreach ([240, 240, 200] as $index => $width) {
            self::assertSame('min-width: '.$width.'px', $xpath->query('//th')->item($index)->getAttribute('style'));
            self::assertStringNotContainsString('uppercase', $xpath->query('//th')->item($index)->getAttribute('class'));
        }
        self::assertStringContainsString($data['outro'], TheoryComponents::html($legacy['tail'][0]));
        self::assertStringContainsString($data['warning'], TheoryComponents::html($legacy['tail'][1]));
        $full = TheoryComponents::html($legacy);
        self::assertLessThan(strpos($full, $data['warning']), strpos($full, $data['outro']), 'Original outro precedes the warning.');
        foreach ([null, 0, -1, 4097, '680px', '680; color:red', '680\" onmouseover=\"alert(1)', []] as $unsafeWidth) {
            self::assertSame('', (string) TheoryComponents::minWidth($unsafeWidth));
        }
        self::assertSame(' style="min-width: 4096px"', (string) TheoryComponents::minWidth(4096));
        $unsafeTable = TheoryAuthoredAdapter::table(['columns' => ['Форма'], 'rows' => [['had been']]], 'Без інʼєкції');
        $unsafeTable['min_width'] = '680; color:red';
        $unsafeTable['column_min_widths'] = ['680" onclick="alert(1)'];
        self::assertSame(0, $this->xpath(TheoryComponents::html($unsafeTable))->query('//*[@style or @onclick or @onmouseover]')->length);
        $authored = TheoryAuthoredAdapter::table(['columns' => ['Конструкція', 'Приклад і переклад', 'Примітка'], 'rows' => [[
            'had been + V-ing', ['en' => 'They had been waiting.', 'uk' => 'Вони чекали.', 'note_uk' => 'Тривалість перед минулим моментом.'], ['text_uk' => 'Процес до іншої події.'],
        ]]], 'Порівняння');
        self::assertSame(['text', 'example', 'text'], array_column($authored['rows'][0]['cells'], 'role'));
        self::assertSame('table-cell', $authored['rows'][0]['cells'][1]['node']['variant']);
        $xpath = $this->xpath(TheoryComponents::html($authored));
        self::assertSame(3, $xpath->query('//th')->length);
        self::assertSame(3, $xpath->query('//td')->length);
        self::assertSame(1, $xpath->query('//td//*[@lang="en"]')->length);
        self::assertSame(3, $xpath->query('//td//*[@lang="uk"]')->length);
        foreach ($xpath->query('//td') as $cell) { self::assertSame('py-3 px-4', $cell->getAttribute('class')); }
        self::assertSame(3, $xpath->query('//td[2]/p')->length, 'Bilingual example and note remain direct table-cell paragraphs, not a nested example card.');
        self::assertSame('', $xpath->query('//td[2]/p[@lang="en"]')->item(0)->getAttribute('class'));
        self::assertSame('theory-translation', $xpath->query('//td[2]/p[@lang="uk"]')->item(0)->getAttribute('class'));
        self::assertSame('text-xs text-muted-foreground mt-2', $xpath->query('//td[2]/p[@lang="uk"]')->item(1)->getAttribute('class'));
        self::assertSame(0, $xpath->query('//td[2]//div|//td[2]//span')->length);
        self::assertStringContainsString('Тривалість перед минулим моментом.', $xpath->query('//td[2]')->item(0)->textContent);
        self::assertStringContainsString('Процес до іншої події.', $xpath->query('//td[3]')->item(0)->textContent);
    }

    public function test_shared_practice_shell_matches_reference_dom_for_all_native_accents_and_overflow_states(): void
    {
        foreach (['blue', 'amber', 'emerald', 'purple'] as $accent) {
            foreach ([false, true] as $visible) {
                $expected = '<div class="theory-exercise rounded-xl border border-'.$accent.'-100 bg-'.$accent.'-50/30 overflow-'.($visible ? 'visible' : 'hidden').'">'
                    .'<div class="border-b border-'.$accent.'-100 bg-'.$accent.'-50/50 px-4 py-3">'
                    .'<h3 class="font-semibold text-foreground text-sm flex items-center gap-2">'
                    .'<span class="flex h-5 w-5 items-center justify-center rounded bg-'.$accent.'-500 text-white text-[10px]">1</span>Вправа</h3>'
                    .'<p class="text-base text-muted-foreground mt-1 leading-relaxed" data-practice-instruction>Постав слова у правильному порядку.</p></div>'
                    .'<div class="p-4 space-y-3"><button type="button" data-existing-control aria-label="Перевірити">Перевірити</button></div></div>';
                $actual = Blade::render('<x-theory-practice-exercise :accent="$accent" :visible-overflow="$visible">'
                    .'<div class="border-b border-{{ $accent }}-100 bg-{{ $accent }}-50/50 px-4 py-3">'
                    .'<x-theory-practice-heading title="Вправа" :number="1" :accent="$accent" instruction="Постав слова у правильному порядку." />'
                    .'</div><div class="p-4 space-y-3"><button type="button" data-existing-control aria-label="Перевірити">Перевірити</button></div>'
                    .'</x-theory-practice-exercise>', compact('accent', 'visible'));
                self::assertSame($this->comparableDom($expected), $this->comparableDom($actual), $accent.' '.($visible ? 'visible' : 'hidden'));
            }
        }
    }

    public function test_native_practice_keeps_all_four_mechanics_controls_and_large_instructions(): void
    {
        $block = new TextBlock;
        $block->forceFill(['id' => 3, 'uuid' => 'canonical-practice-fixture', 'type' => 'practice-set', 'level' => 'B2']);
        $block->setRelation('tags', collect());
        $data = ['title' => 'Практика', 'select_intro' => 'Обери форму.', 'choice_intro' => 'Обери твердження.',
            'input_intro' => 'Побудуй речення.', 'rephrase_intro' => 'Перефразуй речення.',
            'selects' => [['label' => 'She ___ waiting.', 'options' => ['had been', 'has been'], 'answer' => 'had been']],
            'choices' => [['label' => 'Обери A або B.', 'options' => ['a', 'b'], 'answer' => 'a']],
            'inputs' => [['before' => 'We / had / been / waiting', 'answer' => 'We had been waiting.']],
            'rephrase' => [['original' => 'We waited.', 'answer' => 'We had been waiting.']],
        ];
        $html = view('engram.theory.blocks-v3.practice-set', ['block' => $block, 'data' => $data, 'theoryCanonical' => true])->render();
        $xpath = $this->xpath($html);
        self::assertSame(4, $xpath->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]')->length);
        self::assertSame(4, $xpath->query('//p[@data-practice-instruction and contains(concat(" ",@class," ")," text-base ")]')->length);
        self::assertSame(2, $xpath->query('//input[@data-word-suggestion-input]')->length);
        foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
            self::assertStringContainsString('check(\''.$group.'\')', $html, 'Original check action for '.$group);
            self::assertStringContainsString('resetGroup(\''.$group.'\')', $html, 'Original reset action for '.$group);
        }
        self::assertStringContainsString('theoryPracticeSet(', $html);
    }

    public function test_frozen_projection_contract_survives_rendering_all_accepted_m45_sections_and_controls(): void
    {
        [$before, $source] = M45FutureComparisonsPackage::load();
        $masterBytes = file_get_contents(base_path(M45FutureComparisonsPackage::MASTER_PATH));
        self::assertSame('333b39ead3e5bb20c2bcde9c42878cf98f176158d7ae6324d0c6510b09b2afb7', hash('sha256', $masterBytes));
        $master = json_decode($masterBytes, true, flags: JSON_THROW_ON_ERROR);
        $details = 0; $renderedDetails = 0; $tasks = 0; $controls = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            foreach ($lesson['sections'] as $section) {
                $html = $this->renderBlock($this->block($source['targets'][$i], $section['slot']));
                $xpath = $this->xpath($html);
                $text = preg_replace('/\s+/u', ' ', $xpath->query('//body')->item(0)->textContent);
                $renderedDetails += $xpath->query('//details[@data-theory-details]')->length;
                foreach ([...$section['points'], ...($section['cards'] ?? [])] as $item) {
                    self::assertStringContainsString($item['title'], $text);
                    if (isset($item['detail'])) {
                        $details++;
                        foreach ($item['detail']['paragraphs_uk'] as $paragraph) { self::assertStringContainsString($paragraph, $text); }
                    }
                }
                self::assertSame(0, $xpath->query('//details[@open]|//details//details')->length);
                self::assertSame(0, $xpath->query('//style')->length, 'Theory content does not activate package styling');
                if (isset($section['cards'])) {
                    self::assertSame(2, $xpath->query('//*[@data-theory-component="form" and not(ancestor::details)]')->length, 'Two accepted constructions stay two compact form cards');
                    foreach ($section['cards'] as $card) {
                        self::assertSame(3, $xpath->query('//*[@id="'.$card['id'].'"]//h4[not(ancestor::details)]')->length, 'Every card keeps three canonical formula rows');
                    }
                }
            }
            foreach ($lesson['practice'] as $task) { $tasks++; $controls += count($task['controls']); }
            $practice = $this->renderBlock($this->block($source['targets'][$i], 7));
            self::assertSame(6, $this->xpath($practice)->query('//*[@data-m45-ui-case]')->length);
            self::assertSame(0, $this->xpath($practice)->query('//textarea[@disabled or @placeholder]')->length);
        }
        self::assertSame([13, 18, 18], [$details, $tasks, $controls]);
        self::assertSame(13, $renderedDetails, 'All accepted depth remains inside standard point-level disclosures');
        self::assertSame($source, M45FutureComparisonsPackage::project($before, $master), 'Render-only helpers never rewrite deterministic saved payloads');
        M45FutureComparisonsPackage::validate($before, $source);
    }

    public function test_all_accepted_package_projections_still_validate_their_original_sources(): void
    {
        self::assertCount(18, self::FROZEN_PACKAGES);
        foreach (self::FROZEN_PACKAGES as $class) {
            [$before, $source] = $class::load();
            $snapshot = serialize([$before, $source]);
            $class::validate($before, $source);
            self::assertSame($snapshot, serialize([$before, $source]), $class.' never rewrites an accepted payload');
            self::assertNotEmpty($source['targets'], $class);
            $target = $source['targets'][0];
            $slot = array_key_first(array_filter($target['after']['page']['blocks'], fn ($config) => in_array($config['type'], ['usage-panels', 'forms-grid', 'comparison-table', 'summary-list', 'mistakes-grid'], true)));
            self::assertNotNull($slot, $class.' representative content block');
            $xpath = $this->xpath($this->renderBlock($this->block($target, $slot)));
            self::assertSame(0, $xpath->query('//style')->length, $class.' cannot activate its aesthetic stylesheet in the theory caller');
            self::assertGreaterThan(0, $xpath->query('//*[@data-theory-component]')->length, $class.' uses canonical components');
        }
    }

    public function test_foreign_or_damaged_m45_identity_keeps_full_basic_detail_and_safe_text(): void
    {
        [, $source] = M45FutureComparisonsPackage::load();
        $original = $this->block($source['targets'][0], 2);
        $data = json_decode($original->body, true, flags: JSON_THROW_ON_ERROR);
        self::assertNotNull(M45FutureComparisonsPackage::presentation($original, $data));
        foreach (['seeder' => 'ForeignOwner', 'locale' => 'pl', 'uuid' => 'unknown', 'type' => 'foreign-view', 'sort_order' => 900] as $field => $value) {
            $block = clone $original; $block->setAttribute($field, $value);
            self::assertNull(M45FutureComparisonsPackage::presentation($block, $data), $field);
            $html = $this->renderBlock($block); $xpath = $this->xpath($html);
            $text = preg_replace('/\s+/u', ' ', $xpath->query('//body')->item(0)->textContent);
            foreach ($data['author_section']['cards'] as $card) {
                foreach ($card['rows'] as $row) { self::assertStringContainsString($row['en'], $text); self::assertStringContainsString($row['uk'], $text); }
                if (isset($card['detail'])) { foreach ($card['detail']['paragraphs_uk'] as $paragraph) { self::assertStringContainsString($paragraph, $text); } }
            }
            self::assertSame(0, $xpath->query('//details')->length, 'Invalid identity cannot collapse available teaching material');
        }
        $data['author_section']['cards'][0]['title'] = '<img src=x onerror="bad()">';
        $block = clone $original; $block->body = M45FutureComparisonsPackage::json($data);
        self::assertNull(M45FutureComparisonsPackage::presentation($block, $data));
        $xpath = $this->xpath($this->renderBlock($block));
        self::assertSame(0, $xpath->query('//img|//*[@onerror]')->length);
        self::assertStringContainsString($data['author_section']['cards'][0]['title'], $xpath->query('//body')->item(0)->textContent);
    }

    public function test_active_package_wrappers_do_not_reimplement_visual_primitives(): void
    {
        $directory = resource_path('views/engram/theory/blocks-v3');
        $wrappers = glob($directory.'/m*-*section.blade.php');
        $wrappers = array_merge($wrappers, glob($directory.'/m*-*detail.blade.php'));
        self::assertNotEmpty($wrappers);
        foreach ($wrappers as $file) {
            $source = file_get_contents($file);
            self::assertDoesNotMatchRegularExpression('/<(?:article|section|header|h[1-6]|div|details|summary|p|table|style)\b/i', $source, basename($file));
        }
        foreach (glob(resource_path('views/theory/components/*.blade.php')) as $file) {
            $source = file_get_contents($file);
            self::assertDoesNotMatchRegularExpression('/\bm(?:2[6-9]|[3-9][0-9])(?:[-_]|Style|Native)/', $source, basename($file).' is package-independent');
        }
        $caller = file_get_contents(resource_path('views/theory/show.blade.php'));
        self::assertMatchesRegularExpression('/[\'\"]theoryCanonical[\'\"]\s*=>\s*true/', $caller, 'The theory caller explicitly chooses canonical components; other consumers keep compatibility');
    }
}
