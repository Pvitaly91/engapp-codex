<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M49AdviceModalsPackage as Package;
use App\Support\TheoryContentRenderer;
use App\Support\TheoryContentSource;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Js;
use RuntimeException;
use Tests\TestCase;

/** Independent author-master expectations; unsaved models and isolated SQLite only. */
class M49AdviceModalsPackageTest extends TestCase
{
    private const CONTENT = ['forms-grid', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list'];

    /** Independent scope: the advice lesson is not moved into the modal category. */
    private const BINDINGS = [
        ['identity' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsHadBetterTheorySeeder',
            'slug' => 'had-better', 'category' => 'conditionals', 'technical_level' => 'A2', 'display_level' => 'A2-B1'],
        ['identity' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsCanCouldTheorySeeder',
            'slug' => 'can-could', 'category' => 'modal-verbs', 'technical_level' => 'A1', 'display_level' => 'A1-A2'],
        ['identity' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMayMightTheorySeeder',
            'slug' => 'may-might', 'category' => 'modal-verbs', 'technical_level' => 'A2', 'display_level' => 'A2-B1'],
    ];

    /** Reviewed alternatives are meanings/registers, not a forced one-answer rule. */
    private const MULTIPLE_OFFERED = [
        'm49-can-could-task-3' => ['Can I open the window?', 'Could I open the window?'],
        'm49-can-could-task-4' => ['Can you call me after class?', 'Could you call me after class?'],
        'm49-can-could-task-6' => ['Yesterday I was able to fix the printer.', 'Yesterday I managed to fix the printer.'],
        'm49-may-might-possibility' => ['may', 'might'],
        'm49-may-might-negative-possibility' => ['may not be', 'might not be'],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('uk');
        $this->withoutVite();
    }

    private function master(): array
    {
        // Do not use definition()/presentation() to generate expected learning content.
        return json_decode(file_get_contents(base_path('docs/content/m49-advice-modals.v1.0.0.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    private function blocks(array $definition): Collection
    {
        $owner = $definition['seeder']['class'];
        $source = $definition['page'];
        $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $source['subtitle_html'],
            'level' => $source['subtitle_level'] ?? null, 'uuid_key' => $source['subtitle_uuid_key'] ?? 'subtitle'];
        $blocks = [];
        foreach ([$subtitle, ...$source['blocks']] as $position => $config) {
            $uuid = M26DetailPackage::uuid($owner, $config, $position);
            $block = new TextBlock;
            $block->setRawAttributes([
                'id' => 490000 + (int) hexdec(substr(str_replace('-', '', $uuid), 0, 6)),
                'page_id' => 490, 'page_category_id' => 49,
                'uuid' => $uuid, 'seeder' => $owner, 'locale' => 'uk',
                'type' => $config['type'], 'column' => $config['column'] ?? null,
                'heading' => $config['heading'] ?? null, 'level' => $config['level'] ?? null,
                'css_class' => $config['css_class'] ?? null, 'sort_order' => $position, 'body' => $config['body'],
                'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
            ], true);
            $block->setRelation('tags', new Collection);
            $block->setRelation('page', null);
            $blocks[] = $block;
        }

        return new Collection($blocks);
    }

    private function page(array $definition, ?Collection $blocks = null): Page
    {
        $page = new Page;
        $page->setRawAttributes(['id' => 490, 'page_category_id' => 49, 'type' => 'theory',
            'seeder' => $definition['seeder']['class'], 'slug' => $definition['slug'],
            'title' => $definition['page']['title'], 'text' => $definition['page']['subtitle_text']], true);
        $page->setRelation('textBlocks', $blocks ?? $this->blocks($definition));
        $page->setRelation('tags', new Collection);
        $page->setRelation('category', null);

        return $page;
    }

    private function block(array $target, int $slot): TextBlock
    {
        return $this->blocks($target['after'])[$slot + 1];
    }

    private function render(TextBlock $block, bool $context = true): string
    {
        app('view')->flushState();

        return view('theory.partials.content-block', [
            'block' => $block, 'theoryCanonical' => true, 'm42StyleContext' => $context,
            'practiceQuestions' => collect(), 'lessonLinks' => [],
        ])->render();
    }

    private function xpath(string $html): DOMXPath
    {
        // libxml's HTML4 parser does not reliably preserve Alpine's @ prefix.
        // Rename only the attribute name for inspection, never its raw JS value.
        $html = preg_replace('/(?<=\\s)@click(?=\\s*=)/', 'data-test-click', $html);
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<!doctype html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    /** Whitespace only: punctuation, case, formulas and words remain exact. */
    private function text(DOMNode|string $value): string
    {
        if (is_string($value)) {
            $value = $this->xpath($value)->query('//body')->item(0);
        }
        $parts = [];
        $walk = function (DOMNode $node) use (&$walk, &$parts): void {
            if ($node->nodeType === XML_TEXT_NODE) { $parts[] = $node->textContent; return; }
            if ($node instanceof DOMElement && in_array($node->tagName, ['script', 'style'], true)) { return; }
            foreach ($node->childNodes as $child) { $walk($child); }
        };
        $walk($value);

        return trim(preg_replace('/\s+/u', ' ', implode(' ', $parts)));
    }

    private function assertLearningFields(DOMNode $rendered, array $fields, string $context): void
    {
        $actual = $this->text($rendered);
        $missing = [];
        foreach ($fields as $path => $field) {
            if ($field !== '' && !str_contains($actual, $this->text($field))) { $missing[$path] = $field; }
        }
        self::assertSame([], $missing, $context.' lost or changed author fields');
    }

    private function fields(array $data, array $names): array
    {
        return array_intersect_key($data, array_flip($names));
    }

    public function test_frozen_master_projects_only_the_three_existing_uk_owners_and_keeps_old_slots(): void
    {
        [$before, $source, $loadedMaster] = Package::load();
        $master = $this->master();
        self::assertSame($master, $loadedMaster);
        self::assertSame('1.0.0', $master['version']);
        self::assertSame('3b0081eda875b3fb5f07cfd8f1441e3ac7102670', $master['base_sha']);
        self::assertSame(array_column(self::BINDINGS, 'identity'), array_column($master['lessons'], 'identity'));
        self::assertSame(array_column(self::BINDINGS, 'slug'), array_column($master['lessons'], 'slug'));
        foreach ([Package::MASTER => Package::MASTER_SHA, Package::SOURCE => Package::SOURCE_SHA,
            Package::BEFORE => Package::BEFORE_SHA] as $path => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $sha, 'The test is only executable after a real source freeze');
            self::assertSame($sha, hash_file('sha256', base_path($path)));
        }
        Package::validate($before, $source, $master);

        foreach ($master['lessons'] as $i => $lesson) {
            $scope = self::BINDINGS[$i];
            $old = $before['targets'][$i]['before'];
            $target = $source['targets'][$i];
            self::assertSame($scope['identity'], $target['identity']);
            self::assertSame($scope['identity'], $old['seeder']['class']);
            self::assertSame([$scope['category']], $target['ancestry']);
            self::assertSame($target['after'], Package::definition($lesson, $old));
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR),
                'The versioned definition itself matches the pinned source projection');
            self::assertSame('uk', $lesson['locale']);
            self::assertSame($scope['display_level'], $lesson['display_level']);
            self::assertSame($scope['technical_level'], $lesson['technical_block_level']);
            self::assertSame($scope['display_level'], $lesson['blocks'][0]['data']['level']);
            self::assertSame('uk', $old['page']['locale']);
            self::assertSame($scope['category'], $old['page']['category']['slug']);
            self::assertCount(6, $old['page']['blocks'], 'All six original block slots remain owned by the same lesson');
            self::assertSame(array_diff_key($old['page'], array_flip(['blocks', 'subtitle_html', 'subtitle_text'])),
                array_diff_key($target['after']['page'], array_flip(['blocks', 'subtitle_html', 'subtitle_text'])));
            $uuids = [];
            foreach ($lesson['blocks'] as $slot => $authorBlock) {
                $config = $target['after']['page']['blocks'][$slot];
                $data = json_decode($config['body'], true, flags: JSON_THROW_ON_ERROR);
                self::assertSame($authorBlock['key'], $data['m49_native_v1']['key']);
                $uuid = M26DetailPackage::uuid($scope['identity'], $config, $slot + 1);
                $uuids[] = $uuid;
                $originalSlot = $authorBlock['existing_source_index'] ?? null;
                if (is_int($originalSlot)) {
                    self::assertSame($originalSlot, $slot, 'Existing source slots are not reordered');
                    self::assertSame(array_diff_key($old['page']['blocks'][$originalSlot], ['body' => true]),
                        array_diff_key($config, ['body' => true]), 'Continuing UUID/column/level/tags stay exact');
                    self::assertSame(M26DetailPackage::uuid($scope['identity'], $old['page']['blocks'][$originalSlot], $originalSlot + 1), $uuid);
                } else {
                    self::assertGreaterThanOrEqual(6, $slot, 'New blocks append after the complete original six-slot sequence');
                    self::assertSame('m49:'.$lesson['slug'].':'.$authorBlock['key'], $config['uuid_key']);
                }
                if ($authorBlock['type'] !== 'navigation-chips') {
                    self::assertSame($scope['technical_level'], $config['level']);
                }
                self::assertSame($uuid, $this->block($target, $slot)->uuid);
            }
            self::assertSame($uuids, array_values(array_unique($uuids)), 'No continuing or new block UUID collides');
        }
        foreach (['owner', 'body', 'key', 'locale', 'category', 'path', 'slug', 'display-level'] as $change) {
            $badBefore = $before; $badSource = $source; $badMaster = $master;
            if ($change === 'owner') { $badSource['targets'][0]['identity'] = 'ForeignOwner'; }
            if ($change === 'body') {
                $changed = json_decode($badSource['targets'][0]['after']['page']['blocks'][1]['body'], true, flags: JSON_THROW_ON_ERROR);
                $changed['items'][0]['subtitle'] .= ' Змінено реальне навчальне поле.';
                $badSource['targets'][0]['after']['page']['blocks'][1]['body'] = M26DetailPackage::json($changed);
            }
            if ($change === 'key') { $badSource['targets'][0]['block_keys'][1] = 'other-form'; }
            if ($change === 'locale') { $badMaster['lessons'][0]['locale'] = 'en'; }
            if ($change === 'category') { $badSource['targets'][0]['ancestry'] = ['modal-verbs']; }
            if ($change === 'path') { $badBefore['targets'][1]['path'] = $before['targets'][0]['path']; }
            if ($change === 'slug') { $badMaster['lessons'][1]['slug'] = 'had-better'; }
            if ($change === 'display-level') { $badMaster['lessons'][1]['blocks'][0]['data']['level'] = 'A2-B1'; }
            try {
                Package::validate($badBefore, $badSource, $badMaster);
                self::fail('Changed '.$change.' passed immutable projection validation');
            } catch (RuntimeException $error) {
                self::assertStringContainsString('M49', $error->getMessage());
            }
        }
    }

    public function test_learning_payload_cannot_select_views_or_add_styles_even_with_a_coherent_projection(): void
    {
        [$before, $source] = Package::load();
        $master = $this->master();
        $cases = [
            ['view', 'theory.fake'],
            ['native_view', 'engram.theory.blocks-v3.other'],
            ['stylesheet', 'lesson.css'],
            ['css_class', 'custom-card'],
            ['class', 'custom-card'],
            ['style', 'color:red'],
            ['intro', '<p class="custom-card">Навчальне пояснення.</p>'],
            ['intro', '<p style="color:red">Навчальне пояснення.</p>'],
            ['intro', '<p onclick="alert(1)">Навчальне пояснення.</p>'],
            ['intro', '<script>alert(1)</script>'],
            ['intro', '<style>p{color:red}</style>'],
            ['intro', '<iframe src="about:blank"></iframe>'],
        ];
        foreach ($cases as [$key, $value]) {
            $badMaster = $master; $badSource = $source;
            $badMaster['lessons'][0]['blocks'][1]['data'][$key] = $value;
            $badSource['targets'][0]['after'] = Package::definition($badMaster['lessons'][0], $before['targets'][0]['before']);
            try {
                Package::validate($before, $badSource, $badMaster);
                self::fail('Presentation selection passed validation: '.$key);
            } catch (RuntimeException $error) {
                self::assertStringContainsString('M49 learning payload', $error->getMessage(),
                    'Must reach the learning-data guard, not fail earlier because the test projection is inconsistent');
            }
        }
        $badMaster = $master; $badSource = $source;
        $badMaster['lessons'][2]['details'][0]['fragment']['data']['sections'][1]['note'] = '<span style="display:none">Прихований текст.</span>';
        $badSource['targets'][2]['after'] = Package::definition($badMaster['lessons'][2], $before['targets'][2]['before']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('M49 learning payload');
        Package::validate($before, $badSource, $badMaster);
    }

    public function test_actual_universal_render_keeps_every_form_point_table_translation_and_summary(): void
    {
        [, $source] = Package::load();
        $totals = [];
        foreach ($this->master()['lessons'] as $i => $lesson) {
            $count = ['forms' => 0, 'usage' => 0, 'table_rows' => []];
            foreach ($lesson['blocks'] as $slot => $authorBlock) {
                if (!in_array($authorBlock['type'], self::CONTENT, true)) { continue; }
                $block = $this->block($source['targets'][$i], $slot);
                $snapshot = $block->getAttributes();
                $html = $this->render($block);
                $xp = $this->xpath($html);
                $section = $xp->query('//section[@data-theory-component="section" and @id="block-'.$block->id.'"]')->item(0);
                self::assertNotNull($section, $authorBlock['key'].' must use the actual universal section');
                self::assertSame(1, substr_count($html, '<!-- theory.content:node -->'));
                self::assertStringNotContainsString('data-theory-render-fallback=', $html);
                $data = $authorBlock['data'];
                $this->assertLearningFields($section, $this->fields($data, ['title', 'intro', 'outro', 'warning']), $authorBlock['key']);

                if ($authorBlock['type'] === 'forms-grid') {
                    $cards = $xp->query('.//*[@data-theory-component="form"]', $section);
                    self::assertCount(count($data['items']), $cards);
                    foreach ($data['items'] as $j => $item) {
                        $fields = $this->fields($item, ['label', 'title', 'subtitle']);
                        foreach ($item['rules'] ?? [] as $k => $rule) {
                            foreach ($this->fields($rule, ['label', 'formula', 'text', 'example']) as $key => $value) {
                                $fields['rules/'.$k.'/'.$key] = $value;
                            }
                        }
                        $this->assertLearningFields($cards->item($j), $fields, $authorBlock['key'].' form '.$j);
                    }
                    $count['forms'] += $cards->length;
                } elseif ($authorBlock['type'] === 'usage-panels') {
                    self::assertSame('native', $section->getAttribute('data-theory-reference'));
                    $panels = $xp->query('.//*[@data-theory-point-panel]', $section);
                    self::assertCount(count($data['sections']), $panels);
                    foreach ($data['sections'] as $j => $point) {
                        $panel = $panels->item($j);
                        self::assertSame($point['color'], $panel->getAttribute('data-theory-accent'));
                        $fields = $this->fields($point, ['label', 'description', 'note']);
                        foreach ($point['examples'] ?? [] as $k => $example) {
                            $fields['examples/'.$k.'/en'] = $example['en'];
                            $fields['examples/'.$k.'/ua'] = $example['ua'];
                        }
                        $this->assertLearningFields($panel, $fields, $authorBlock['key'].' point '.$j);
                        $en = $xp->query('.//*[@data-theory-component="example" and not(ancestor::details)]//p[@lang="en"]', $panel);
                        $uk = $xp->query('.//*[@data-theory-component="example" and not(ancestor::details)]//p[@lang="uk"]', $panel);
                        self::assertSame(array_column($point['examples'] ?? [], 'en'), array_map(fn ($n) => $this->text($n), iterator_to_array($en)));
                        self::assertSame(array_column($point['examples'] ?? [], 'ua'), array_map(fn ($n) => $this->text($n), iterator_to_array($uk)));
                    }
                    $count['usage'] += $panels->length;
                } elseif ($authorBlock['type'] === 'comparison-table') {
                    $rows = $xp->query('.//*[@data-theory-component="table"]//tbody/tr', $section);
                    self::assertCount(count($data['rows']), $rows);
                    foreach ($data['rows'] as $j => $row) {
                        $cells = $xp->query('./td', $rows->item($j));
                        self::assertSame(array_map(fn ($value) => $this->text($value), [$row['en'], $row['ua'], $row['note']]),
                            array_map(fn ($n) => $this->text($n), iterator_to_array($cells)), $authorBlock['key'].' table row '.$j);
                    }
                    $count['table_rows'][] = $rows->length;
                } elseif ($authorBlock['type'] === 'mistakes-grid') {
                    $cards = $xp->query('.//*[@data-theory-component="mistake"]', $section);
                    self::assertCount(count($data['items']), $cards);
                    foreach ($data['items'] as $j => $item) {
                        $this->assertLearningFields($cards->item($j),
                            $this->fields($item, ['label', 'title', 'wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'hint']),
                            $authorBlock['key'].' correction '.$j);
                        $presentation = Package::presentation($block, json_decode($block->body, true, flags: JSON_THROW_ON_ERROR));
                        self::assertNotNull(Package::correction($item, $presentation['m42_native_design']));
                        $foreignItem = $item; $foreignItem['hint'] .= ' Змінено зміст.';
                        self::assertNull(Package::correction($foreignItem, $presentation['m42_native_design']), 'Only exact author correction fields receive the bilingual bridge');
                        self::assertSame([$item['wrong_ua'], $item['right_ua']],
                            array_map(fn ($n) => $this->text($n), iterator_to_array($xp->query('.//p[@lang="uk"]', $cards->item($j)))));
                    }
                } else {
                    $this->assertLearningFields($section, $data['items'], $authorBlock['key'].' summary');
                }
                self::assertSame($snapshot, $block->getAttributes(), 'Rendering does not mutate source models');
                self::assertFalse($block->isDirty());
            }
            $totals[$lesson['slug']] = $count;
        }
        $expected = [];
        foreach ($this->master()['lessons'] as $lesson) {
            $expected[$lesson['slug']] = ['forms' => 0, 'usage' => 0, 'table_rows' => []];
            foreach ($lesson['blocks'] as $block) {
                if ($block['type'] === 'forms-grid') { $expected[$lesson['slug']]['forms'] += count($block['data']['items']); }
                if ($block['type'] === 'usage-panels') { $expected[$lesson['slug']]['usage'] += count($block['data']['sections']); }
                if ($block['type'] === 'comparison-table') { $expected[$lesson['slug']]['table_rows'][] = count($block['data']['rows']); }
            }
        }
        self::assertSame($expected, $totals, 'Rendered counts derive from the actual author structure, not a previous package quota');
    }

    public function test_full_theory_page_keeps_identity_hero_metadata_and_exact_closed_point_owners(): void
    {
        [, $source] = Package::load();
        $detailCounts = [];
        // Independent editorial expectations fixed before runtime/HTTP AFTER.
        $metadata = [
            'had-better' => [
                'title' => 'Had better — правила | Gramlyze',
                'description' => 'Had better виражає сильну пораду в конкретній ситуації: мовець бачить можливий небажаний наслідок, якщо не дослухатися.',
            ],
            'can-could' => [
                'title' => 'Can / Could — правила | Gramlyze',
                'description' => 'Can / Could допомагають говорити про здатність і можливість, просити дозволу та звертатися по допомогу.',
            ],
            'may-might' => [
                'title' => 'May / Might — правила | Gramlyze',
                'description' => 'May / Might допомагають висловити припущення: щось можливе, але не певне.',
            ],
        ];
        foreach ($this->master()['lessons'] as $i => $lesson) {
            $page = $this->page($source['targets'][$i]['after']);
            app('view')->flushState();
            $html = view('theory.show', ['page' => $page, 'categories' => collect(),
                'categoryPages' => collect(), 'topicTests' => collect(), 'selectedCategory' => null])->render();
            $xp = $this->xpath($html);
            self::assertCount(1, $xp->query('//h1'));
            self::assertSame($source['targets'][$i]['after']['page']['title'], $this->text($xp->query('//h1')->item(0)), 'Stored H1 identity is unchanged');
            self::assertSame($metadata[$lesson['slug']]['title'], $this->text($xp->query('//title')->item(0)));
            foreach (['name="description"', 'property="og:description"', 'name="twitter:description"'] as $selector) {
                $meta = $xp->query('//meta[@'.$selector.']');
                self::assertCount(1, $meta);
                self::assertSame($metadata[$lesson['slug']]['description'], $meta->item(0)->getAttribute('content'),
                    'The first complete hero sentence, not the subtitle/AFTER output, owns metadata');
            }
            foreach (['property="og:title"', 'name="twitter:title"'] as $selector) {
                self::assertSame($metadata[$lesson['slug']]['title'], $xp->query('//meta[@'.$selector.']')->item(0)->getAttribute('content'));
            }
            $navigation = array_values(array_filter($lesson['blocks'], fn ($b) => $b['type'] === 'navigation-chips'))[0]['data'];
            foreach ($navigation['items'] as $link) {
                if ($link['current'] ?? false) {
                    self::assertContains($link['label'], array_map(fn ($node) => $this->text($node),
                        iterator_to_array($xp->query('//nav//span'))), 'The current lesson remains a native non-link chip');
                    continue;
                }
                self::assertGreaterThanOrEqual(1, $xp->query('//a[@href="'.$link['url'].'"]')->length, 'Preserve author navigation '.$link['url']);
            }
            $hero = array_values(array_filter($lesson['blocks'], fn ($b) => $b['type'] === 'hero'))[0]['data'];
            $heroNode = $xp->query('//section[contains(concat(" ", normalize-space(@class), " "), " theory-hero ")]')->item(0);
            self::assertNotNull($heroNode);
            $this->assertLearningFields($heroNode, ['intro' => $hero['intro']], $lesson['slug'].' hero');
            $cards = $xp->query('//section[contains(concat(" ", normalize-space(@class), " "), " theory-rules ")]/article');
            self::assertCount(count($hero['rules']), $cards);
            foreach ($hero['rules'] as $j => $rule) {
                $this->assertLearningFields($cards->item($j), $this->fields($rule, ['label', 'text', 'example']), $lesson['slug'].' hero rule '.$j);
            }
            // The native full-page caller retains the subtitle as an inert anchor.
            $subtitleId = 'block-'.$page->textBlocks[0]->id;
            $subtitleAnchor = $xp->query('//*[@id="'.$subtitleId.'" and @aria-hidden="true" and contains(concat(" ", normalize-space(@class), " "), " theory-subtitle-anchor ")]');
            self::assertCount(1, $subtitleAnchor);
            $expectedAnchors = [$subtitleId];
            foreach ($lesson['blocks'] as $slot => $config) {
                if (in_array($config['type'], [...self::CONTENT, 'practice-set'], true)) {
                    $expectedAnchors[] = 'block-'.$page->textBlocks[$slot + 1]->id;
                }
            }
            $actualAnchors = array_map(fn ($node) => $node->getAttribute('id'),
                iterator_to_array($xp->query('//*[starts-with(@id, "block-")]')));
            self::assertSame($expectedAnchors, $actualAnchors, 'All continuing and appended content anchors retain source order');
            $details = $xp->query('//details[@data-theory-details]');
            self::assertCount(count($lesson['details']), $details);
            $detailCounts[] = $details->length;
            foreach ($lesson['details'] as $detail) {
                self::assertMatchesRegularExpression('~^/(items|sections)/[0-9]+$~', $detail['owner_pointer']);
                $owned = $xp->query('//*[@data-theory-section="'.$detail['key'].'"]/details[@data-theory-details]');
                self::assertCount(1, $owned);
                self::assertFalse($owned->item(0)->hasAttribute('open'));
                $ownerSlot = array_search($detail['owner_block_key'], array_column($lesson['blocks'], 'key'), true);
                $ownerConfig = $lesson['blocks'][$ownerSlot];
                $ownerBlock = $page->textBlocks[$ownerSlot + 1];
                $pointIndex = (int) explode('/', $detail['owner_pointer'])[2];
                $component = $ownerConfig['type'] === 'forms-grid' ? 'form' : 'usage';
                $ancestors = $xp->query('ancestor::*[@data-theory-component="'.$component.'"]', $owned->item(0));
                self::assertCount(1, $ancestors);
                $actualOwner = $ancestors->item(0);
                $field = $component === 'form' ? 'items' : 'sections';
                $this->assertLearningFields($actualOwner, $this->fields($ownerConfig['data'][$field][$pointIndex], ['label', 'description', 'title']), 'Exact point owner');
                self::assertCount(1, $xp->query('ancestor::section[@id="block-'.$ownerBlock->id.'"]', $actualOwner));
                self::assertSame($pointIndex + 1, $xp->query('preceding-sibling::*[@data-theory-component="'.$component.'"]', $actualOwner)->length + 1);
                foreach ($lesson['details'] as $other) {
                    if ($other['key'] !== $detail['key']) {
                        self::assertCount(0, $xp->query('.//*[@data-theory-section="'.$other['key'].'"]', $actualOwner), 'No point opens another point’s detail');
                    }
                }
                $this->assertLearningFields($owned->item(0), $this->detailFields($detail),
                    'Only the matching point owns every section of its full author detail');
                foreach ($detail['fragment']['data']['sections'] as $fragmentIndex => $fragment) {
                    $fragments = $xp->query('.//*[@id="'.$detail['key'].'-detail-'.$fragmentIndex.'"]', $owned->item(0));
                    self::assertCount(1, $fragments, 'Every owned fragment keeps a distinct stable anchor');
                    $fields = $this->fields($fragment, ['label', 'description', 'note']);
                    foreach ($fragment['examples'] as $j => $example) {
                        $fields['example/'.$j.'/en'] = $example['en'];
                        $fields['example/'.$j.'/ua'] = $example['ua'];
                    }
                    $this->assertLearningFields($fragments->item(0), $fields, 'Detail fragment '.$fragmentIndex);
                }
            }
        }
        self::assertSame(array_map(fn ($lesson) => count($lesson['details']), $this->master()['lessons']), $detailCounts);
    }

    public function test_all_eighteen_tasks_keep_their_raw_controls_aliases_and_fully_translated_feedback(): void
    {
        [, $source] = Package::load();
        $taskCount = 0; $optionCount = 0; $acceptedOfferedCount = 0; $seenMultiple = [];
        $expectedOptions = 0; $expectedAccepted = 0; $groups = [];
        foreach ($this->master()['lessons'] as $lesson) {
            foreach ($lesson['blocks'] as $block) {
                if ($block['type'] !== 'practice-set') { continue; }
                foreach ([...$block['data']['selects'], ...$block['data']['choices']] as $task) {
                    $offered = $task['options'] ?? $block['data']['choice_options'];
                    $expectedOptions += count($offered);
                    $expectedAccepted += count(array_intersect($offered, $task['accepted']));
                }
            }
        }
        foreach ($this->master()['lessons'] as $i => $lesson) {
            foreach ($lesson['blocks'] as $slot => $authorBlock) {
                if ($authorBlock['type'] !== 'practice-set') { continue; }
                $data = $authorBlock['data'];
                $groups[$lesson['slug']] = [count($data['selects']), count($data['choices'])];
                $block = $this->block($source['targets'][$i], $slot);
                $xp = $this->xpath($this->render($block));
                $factories = $xp->query('//*[@x-data and starts-with(@x-data, "theoryPracticeSet(")]');
                self::assertCount(1, $factories);
                $factory = $factories->item(0);
                self::assertSame(1, preg_match("/\\AtheoryPracticeSet\\(JSON\\.parse\\('(.*)'\\)\\)\\z/s", $factory->getAttribute('x-data'), $matches));
                $json = json_decode('"'.$matches[1].'"', true, flags: JSON_THROW_ON_ERROR);
                $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
                self::assertSame($data['selects'], $payload['selects'], 'Select raw values, aliases and feedback cannot be display-rewritten');
                self::assertSame($data['choices'], $payload['choices'], 'Choice raw values, aliases and feedback cannot be display-rewritten');
                self::assertSame([], $payload['inputs']);
                self::assertSame([], $payload['rephrase']);
                self::assertCount(6, $xp->query('.//label', $factory));
                self::assertSame(range(1, 6), array_column([...$data['selects'], ...$data['choices']], 'source_index'));

                foreach (['selects' => 'selectAnswers', 'choices' => 'choiceAnswers'] as $group => $answers) {
                    foreach ($data[$group] as $j => $task) {
                        $taskCount++;
                        self::assertContains($task['answer'], $task['accepted']);
                        $offeredAccepted = array_values(array_intersect($task['options'] ?? $data['choice_options'], $task['accepted']));
                        $acceptedOfferedCount += count($offeredAccepted);
                        if (isset(self::MULTIPLE_OFFERED[$task['id']])) {
                            self::assertSame(self::MULTIPLE_OFFERED[$task['id']], $offeredAccepted,
                                'Every explicitly accepted natural option must remain offered and correct');
                            $seenMultiple[] = $task['id'];
                        } else {
                            self::assertCount(1, $offeredAccepted, 'Other tasks have one offered answer; non-offered aliases remain intact');
                        }
                        foreach ($task['accepted'] as $alias) {
                            self::assertArrayHasKey(mb_strtolower(trim($alias)), $task['feedback'], 'Explicit aliases retain contextual feedback');
                        }
                        $this->assertLearningFields($factory, $this->fields($task, ['label', 'prompt']), $lesson['slug'].' task '.$task['source_index']);
                        foreach ($task['options'] ?? $data['choice_options'] as $option) {
                            $optionCount++;
                            $key = mb_strtolower(trim($option));
                            self::assertArrayHasKey($key, $task['feedback']);
                            self::assertMatchesRegularExpression('/[А-ЯІЇЄҐа-яіїєґ]/u', $task['feedback'][$key]);
                            // Both accepted editorial formats retain an English sentence and its separate UK translation.
                            // A literal service label is not required when the author uses an em-dash pair.
                            self::assertMatchesRegularExpression('/[.?!]\\s+(?:Переклад[^:]*:\\s+|—\\s+)[А-ЯІЇЄҐ]/u',
                                $task['feedback'][$key], 'Every offered answer has an explicit sentence translation');
                            $expectedClick = $answers.'['.$j.'] = '.Js::from($option);
                            $matched = [];
                            foreach ($xp->query('.//button', $factory) as $button) {
                                if ($button->getAttribute('data-test-click') === $expectedClick) { $matched[] = $button; }
                            }
                            self::assertCount(1, $matched, $lesson['slug'].' raw option '.$option);
                            self::assertSame($option, $this->text($matched[0]));
                        }
                        $foundFeedback = [];
                        foreach ($xp->query('.//*[@x-text]', $factory) as $feedback) {
                            if ($feedback->getAttribute('x-text') === "feedbackText('".$group."', ".$j.")") { $foundFeedback[] = $feedback; }
                        }
                        self::assertCount(1, $foundFeedback);
                        self::assertSame("isChecked('".$group."') && hasAnswer('".$group."', ".$j.")",
                            $foundFeedback[0]->parentNode->getAttribute('x-show'));
                    }
                }
                $clicks = [];
                foreach ($xp->query('.//button', $factory) as $button) { $clicks[] = $button->getAttribute('data-test-click'); }
                foreach (["check('selects')", "resetGroup('selects')", "check('choices')", "resetGroup('choices')"] as $action) {
                    self::assertContains($action, $clicks);
                }
            }
        }
        self::assertSame(18, $taskCount);
        self::assertSame($expectedOptions, $optionCount);
        self::assertSame($expectedAccepted, $acceptedOfferedCount);
        self::assertSame(array_keys(self::MULTIPLE_OFFERED), $seenMultiple);
        self::assertSame(['had-better' => [2, 4], 'can-could' => [2, 4], 'may-might' => [4, 2]], $groups,
            'Native mechanics follow learning goals, not a forced three-plus-three layout');
    }

    private function detailFields(array $detail): array
    {
        $fields = ['detail_title' => $detail['title']];
        foreach ($detail['fragment']['data']['sections'] as $sectionIndex => $fragment) {
            foreach ($this->fields($fragment, ['label', 'description', 'note']) as $key => $value) {
                $fields['section/'.$sectionIndex.'/'.$key] = $value;
            }
            foreach ($fragment['examples'] as $i => $example) {
                $fields['section/'.$sectionIndex.'/example/'.$i.'/en'] = $example['en'];
                $fields['section/'.$sectionIndex.'/example/'.$i.'/ua'] = $example['ua'];
            }
        }

        return $fields;
    }

    public function test_foreign_bindings_and_designs_are_rejected_with_complete_stored_detail_fallback(): void
    {
        [, $source] = Package::load();
        foreach ($this->master()['lessons'] as $i => $lesson) {
            foreach ($lesson['details'] as $detail) {
                $slot = array_search($detail['owner_block_key'], array_column($lesson['blocks'], 'key'), true);
                $block = $this->block($source['targets'][$i], $slot);
                $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
                $presentation = Package::presentation($block, $data);
                self::assertNotNull($presentation);
                $point = (int) explode('/', $detail['owner_pointer'])[2];
                self::assertSame([$point], array_keys($presentation['points']));
                self::assertSame(array_values($detail['fragment']['data']['sections']),
                    array_column($presentation['points'][$point]['fragments'], 'value'),
                    'Multi-section details cannot silently retain only the first section');
                self::assertSame(count($detail['fragment']['data']['sections']),
                    count($presentation['points'][$point]['fragments']));
                $basic = $presentation['data'];
                unset($basic['m49_native_v1']);
                self::assertSame($lesson['blocks'][$slot]['data'], $basic,
                    'Projection restores the exact complete basic layer, not a shortened reconstruction');
                $fields = $this->detailFields($detail);
                foreach (['seeder' => 'ForeignOwner', 'locale' => 'en', 'uuid' => 'foreign-uuid',
                    'sort_order' => 99, 'column' => 'footer', 'heading' => 'Changed heading', 'level' => 'C2', 'css_class' => 'other'] as $field => $value) {
                    $foreign = clone $block; $foreign->setAttribute($field, $value);
                    self::assertNull(Package::presentation($foreign, $data), 'Reject foreign '.$field);
                    $xp = $this->xpath($this->render($foreign));
                    self::assertCount(0, $xp->query('//details[@data-theory-details]'));
                    $this->assertLearningFields($xp->query('//body')->item(0), $fields, 'Complete fallback for '.$field);
                }
                foreach (['body', 'key', 'missing-marker'] as $change) {
                    $changed = $data;
                    if ($change === 'body') { $changed['sections'][0]['description'] .= ' Справжня зміна навчального змісту.'; }
                    if ($change === 'key') { $changed['m49_native_v1']['key'] = 'foreign-key'; }
                    if ($change === 'missing-marker') { unset($changed['m49_native_v1']); }
                    $foreign = clone $block; $foreign->body = M26DetailPackage::json($changed);
                    self::assertNull(Package::presentation($foreign, $changed), 'Reject changed '.$change);
                    $xp = $this->xpath($this->render($foreign));
                    self::assertCount(0, $xp->query('//details[@data-theory-details]'));
                    $this->assertLearningFields($xp->query('//body')->item(0), $fields, 'Complete fallback for '.$change);
                }
                self::assertNull(TheoryContentSource::resolve($block)['presentation'], 'The required m42StyleContext belongs to the caller');
                self::assertNotNull(TheoryContentSource::resolve($block, ['m42StyleContext' => true])['presentation']);
                self::assertNull(TheoryContentRenderer::nativeNode('engram.theory.blocks-v3.'.$block->type, [
                    'block' => $block, 'data' => $data, 'theoryCanonical' => false,
                ]), 'A package marker cannot opt a course into canonical theory rendering');
                $xp = $this->xpath($this->render($block, false));
                self::assertCount(0, $xp->query('//details[@data-theory-details]'));
                $this->assertLearningFields($xp->query('//body')->item(0), $fields, 'Complete text without caller opt-in');

                $design = $presentation['m42_native_design'];
                self::assertTrue(Package::validDesign($design));
                foreach (['package' => 'm42', 'identity' => 'ForeignOwner', 'locale' => 'pl', 'uuid' => 'foreign',
                    'source_index' => 999, 'body_sha256' => str_repeat('0', 64), 'component' => 'forms-grid'] as $field => $value) {
                    $foreign = array_replace($design, [$field => $value]);
                    self::assertFalse(Package::validDesign($foreign), 'Reject forged design '.$field);
                    self::assertNull(Package::pointPanels($foreign));
                    self::assertNull(Package::correction([], $foreign));
                }
                self::assertTrue(Package::validDesign($design), 'Rejected input cannot poison valid cached bindings');
            }
        }
    }

    public function test_json_whitespace_is_separate_from_real_source_field_mutation(): void
    {
        [, $source] = Package::load();
        foreach ($source['targets'] as $target) {
            $block = $this->block($target, 2);
            $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            $expected = Package::presentation($block, $data);
            self::assertNotNull($expected);
            $spaced = clone $block;
            $spaced->body = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            self::assertNotSame($block->body, $spaced->body);
            self::assertSame($expected, Package::presentation($spaced, json_decode($spaced->body, true, flags: JSON_THROW_ON_ERROR)),
                'Binding intentionally compares decoded data, not JSON formatting');
            $changed = $data;
            $changed['sections'][0]['description'] .= ' Додаткове справжнє пояснення.';
            $foreign = clone $block; $foreign->body = M26DetailPackage::json($changed);
            self::assertNull(Package::presentation($foreign, $changed), 'A real source field change must fail closed');
        }
    }

    public function test_courses_restore_exact_old_uk_rows_and_keep_non_uk_rows_and_input_models_untouched(): void
    {
        [$before, $source] = Package::load();
        foreach ($source['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before'];
            $oldBlocks = $this->blocks($old);
            $current = $this->blocks($target['after']);
            foreach (['en', 'pl'] as $locale) {
                foreach ($oldBlocks as $original) {
                    $other = clone $original;
                    $other->locale = $locale;
                    $other->uuid = $locale.'-'.$other->uuid;
                    $other->id += $locale === 'en' ? 20000000 : 40000000;
                    $other->syncOriginal();
                    $current->push($other);
                }
            }
            $page = $this->page($target['after'], $current);
            $pageBefore = $page->getAttributes(); $rawBefore = $page->getRawOriginal();
            $rowsBefore = $current->map(fn ($row) => $row->getAttributes())->all();
            $preserved = Package::preserveCoursePage($page);
            self::assertNotSame($page, $preserved);
            self::assertSame($old['page']['subtitle_text'], $preserved->getRawOriginal('text'));
            $uk = $preserved->textBlocks->where('locale', 'uk')->values();
            self::assertCount(7, $uk);
            self::assertSame($oldBlocks->map(fn ($b) => $b->getAttributes())->all(), $uk->map(fn ($b) => $b->getAttributes())->all());
            foreach (['en', 'pl'] as $locale) {
                $originals = $current->where('locale', $locale)->keyBy('uuid');
                $copies = $preserved->textBlocks->where('locale', $locale)->keyBy('uuid');
                self::assertSame($originals->keys()->all(), $copies->keys()->all());
                foreach ($originals as $uuid => $original) { self::assertSame($original, $copies[$uuid], 'Non-UK models are not rewritten'); }
            }
            foreach ($uk as $copy) { self::assertNotSame($current->firstWhere('uuid', $copy->uuid), $copy); }
            // Exercise the unchanged course partial with the caller-owned restored page.
            $oldPage = $this->page($old, $oldBlocks);
            $coursePage = clone $preserved; $coursePage->setRelation('textBlocks', $uk);
            app('view')->flushState();
            $expected = view('courses.partials.theory-page-content', ['page' => $oldPage])->render();
            app('view')->flushState();
            self::assertSame($expected, view('courses.partials.theory-page-content', ['page' => $coursePage])->render());
            self::assertSame($pageBefore, $page->getAttributes());
            self::assertSame($rawBefore, $page->getRawOriginal());
            self::assertSame($rowsBefore, $current->map(fn ($b) => $b->getAttributes())->all());
            self::assertFalse($page->isDirty());

            foreach (['owner', 'subtitle', 'body', 'missing-row', 'duplicate-row'] as $change) {
                $foreignPage = clone $page;
                $foreignBlocks = new Collection($current->map(fn ($row) => clone $row)->all());
                if ($change === 'owner') {
                    $foreignPage->setRawAttributes(array_replace($pageBefore, ['seeder' => 'ForeignOwner']), true);
                } elseif ($change === 'subtitle') {
                    $foreignBlocks->first(fn ($b) => $b->locale === 'uk' && $b->type === 'subtitle')->body .= ' changed';
                } elseif ($change === 'body') {
                    $foreignBlocks->first(fn ($b) => $b->locale === 'uk' && $b->type === 'forms-grid')->body = '{}';
                } elseif ($change === 'missing-row') {
                    $foreignBlocks = $foreignBlocks->slice(1)->values();
                } else {
                    $foreignBlocks->push(clone $foreignBlocks->first());
                }
                $foreignPage->setRelation('textBlocks', $foreignBlocks);
                self::assertSame($foreignPage, Package::preserveCoursePage($foreignPage), 'Course guard rejects '.$change);
            }
        }
        self::assertNull(Package::preserveCoursePage(null));
    }
}
