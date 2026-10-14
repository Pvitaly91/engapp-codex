<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Http\Controllers\TheoryController;
use App\Http\Middleware\AddStructuredData;
use App\Models\Page;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M53PassiveConstructionsPackage as Package;
use App\Support\TheoryContentSource;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Frozen release, author fields and real shared views in isolated SQLite; no working DB/HTTP/progress. */
class M53PassiveConstructionsPackageTest extends TestCase
{
    private const COUNTS = [10, 9, 10];
    private const PAGE_IDS = [92, 95, 96];
    private const LEVELS = ['B1', 'B1', 'B2'];
    private const DISPLAY = ['B1–B2', 'B1', 'B2–C1'];
    private const H1 = ['Каузатив (Causative)', 'Get-passive', 'Безособовий пасив (Impersonal Passive)'];
    private const BANKS = [
        ['Database\\Seeders\\V3\\Polyglot\\PolyglotCausativeHaveGetLessonSeeder'],
        ['Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoiceGetPassiveAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\M53GetPassiveB1SupplementSeeder'],
        ['Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoiceImpersonalPassiveAllLevelsLessonSeeder'],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('uk'); $this->withoutVite();
        self::assertSame('testing', app()->environment());
        self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
    }

    private function blocks(array $target, array $definition): ModelCollection
    {
        $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'],
            'level' => $definition['page']['subtitle_level'] ?? null, 'uuid_key' => $definition['page']['subtitle_uuid_key'] ?? 'subtitle'];
        $rows = [];
        foreach ([$subtitle, ...$definition['page']['blocks']] as $position => $source) {
            $block = new TextBlock;
            $block->setRawAttributes(['id' => 5300000 + $target['page_binding']['id'] * 100 + $position,
                'page_id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
                'uuid' => M26DetailPackage::uuid($target['identity'], $source, $position), 'seeder' => $target['identity'], 'locale' => 'uk',
                'type' => $source['type'], 'column' => $source['column'] ?? null, 'level' => $source['level'] ?? null,
                'heading' => $source['heading'] ?? null, 'css_class' => $source['css_class'] ?? null,
                'body' => $source['body'], 'sort_order' => $position, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'], true);
            $block->setRelation('tags', new ModelCollection); $block->setRelation('page', null); $rows[] = $block;
        }

        return new ModelCollection($rows);
    }

    private function page(array $target, ModelCollection $blocks): Page
    {
        $page = new Page;
        $page->setRawAttributes(['id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
            'type' => 'theory', 'seeder' => $target['identity'], 'slug' => $target['slug'], 'title' => $target['after']['page']['title'],
            'text' => $target['after']['page']['subtitle_text']], true);
        $page->setRelation('textBlocks', $blocks); $page->setRelation('tags', new ModelCollection); $page->setRelation('category', null);

        return $page;
    }

    private function render(TextBlock $block): string
    {
        app('view')->flushState();

        return view('theory.partials.content-block', ['block' => $block, 'theoryCanonical' => true, 'm42StyleContext' => true,
            'practiceQuestions' => collect(), 'lessonLinks' => []])->render();
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument('1.0', 'UTF-8'); $previous = libxml_use_internal_errors(true);
        try { $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }

        return new DOMXPath($dom);
    }

    private function plain(string $text): string
    {
        $text = preg_replace('/<(?:br\s*\/?|\/(?:p|div|li|section))>/i', ' ', $text);
        $plain = preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        // Joining separate DOM text nodes may add whitespace before a link's following punctuation.
        return trim(preg_replace('/\s+([,.!?;:])/u', '$1', $plain));
    }

    /** Text nodes only: serialized answers in x-data/script cannot satisfy a learning field assertion. */
    private function learningText(string $html): string
    {
        $parts = [];
        foreach ($this->xpath($html)->query('//text()[not(ancestor::script) and not(ancestor::style)]') as $node) { $parts[] = $node->textContent; }

        return $this->plain(implode(' ', $parts));
    }

    private function refusal(callable $call): void
    {
        try { $call(); self::fail('Expected fail-closed M53 author/source refusal.'); }
        catch (RuntimeException $error) { self::assertNotSame('', $error->getMessage()); }
    }

    /** Read from reviewed author decisions before deterministic AFTER generation, not inferred from bodies. */
    private function feedbackExpectations(): array
    {
        return [
            'm53-causative-does-get' => [['Does Nina get her jacket cleaned here?'], ['Ніна віддає куртку в чистку тут?']],
            'm53-causative-participle' => [['Leo had the button replaced.'], ['Леву замінили ґудзик.']],
            'm53-causative-organiser' => [['Danylo had his bike brakes replaced.'], ['Данилові замінили гальма велосипеда за його замовленням.']],
            'm53-causative-past-perfect' => [['Oleh had repaired the chair himself before his sister arrived.'], ['Олег сам полагодив стілець ще до приходу сестри.']],
            'm53-causative-build-service' => [['Olena had her violin repaired yesterday.'], ['Олені вчора полагодили скрипку за її замовленням.']],
            'm53-causative-build-negative' => [["Taras didn't have his coat cleaned yesterday.", 'Taras did not have his coat cleaned yesterday.'], ['Тарас учора не віддавав пальто в чистку.']],
            'm53-get-did-base' => [['Did the parcel get damaged during delivery?'], ['Посилку пошкодили під час доставки?']],
            'm53-get-negative-base' => [["Mark didn't get paid yesterday."], ['Марку вчора не заплатили.']],
            'm53-get-positive' => [['Zoia got selected for the choir.'], ['Зою відібрали до хору.']],
            'm53-get-versus-causative' => [['The poster got damaged.', 'I got the poster reprinted.'], ['Плакат пошкодили.', 'Мені передрукували плакат за моїм замовленням.']],
            'm53-get-build-question' => [['Did Marta get promoted last week?'], ['Марту підвищили минулого тижня?']],
            'm53-get-build-negative' => [["We didn't get paid yesterday.", 'We did not get paid yesterday.'], ['Нам учора не заплатили.']],
            'm53-impersonal-earlier' => [['Oksana is reported to have finished the course last month.'], ['Повідомляють, що Оксана завершила курс минулого місяця.']],
            'm53-impersonal-simultaneous' => [['At noon, the studio was believed to be empty.'], ['Опівдні вважали, що студія в цей час порожня.']],
            'm53-impersonal-expect-future' => [['At six, the courier was expected to arrive at eight.'], ['О шостій очікували, що кур’єр прибуде о восьмій.']],
            'm53-impersonal-not-known' => [['The door is not known to have been opened.'], ['Немає підтверджених відомостей, що двері відчиняли.']],
            'm53-impersonal-build-it' => [['It is believed that the library is quiet.'], ['Вважають, що в бібліотеці тихо.']],
            'm53-impersonal-build-earlier' => [['The guide is reported to have left yesterday.'], ['Повідомляють, що гід пішов учора.']],
        ];
    }

    public function test_frozen_release_keeps_actual_variable_slots_titles_levels_uuids_and_independent_bank_contracts(): void
    {
        [$before, $patch, $master, $contract] = Package::load(); Package::validate($before, $patch, $master, $contract);
        self::assertSame('frozen', $master['status']); self::assertSame('1.0.0', $master['version']);
        self::assertSame('ba489be880d9a7c2b3dbc1698e887a05eb166fbe', $master['base_sha']);
        self::assertSame(self::PAGE_IDS, array_column(array_column($patch['targets'], 'page_binding'), 'id'));
        self::assertSame([1, 1, 1], array_column(array_column($patch['targets'], 'page_binding'), 'page_category_id'));
        foreach ([Package::MASTER => Package::MASTER_SHA, Package::BEFORE => Package::BEFORE_SHA,
            Package::SOURCE => Package::SOURCE_SHA, Package::CONTRACT => Package::CONTRACT_SHA] as $path => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha);
            self::assertSame($sha, hash_file('sha256', base_path($path)), 'Draft/sentinel/mutable bytes cannot count as frozen release.');
        }
        $catalogue = array_column(Acceptance::discoverTargets(base_path()), null, 'identity');
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $lesson = $master['lessons'][$i];
            self::assertSame($target['after'], Package::definition($lesson, $old));
            self::assertSame($target['after'], json_decode(File::get(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount(self::COUNTS[$i], $old['page']['blocks']); self::assertCount(self::COUNTS[$i] + 1, $target['after']['page']['blocks']);
            self::assertSame($old['page']['title'], $target['after']['page']['title']); self::assertSame($old['slug'], $target['after']['slug']);
            self::assertSame('uk', $lesson['locale']); self::assertSame(self::LEVELS[$i], $lesson['technical_block_level']);
            self::assertSame(self::DISPLAY[$i], $lesson['display_level']); self::assertSame(self::DISPLAY[$i], $lesson['blocks'][0]['data']['level']);
            self::assertSame([], $catalogue[$target['identity']]['errors']); self::assertSame(self::BANKS[$i], $contract['targets'][$i]['bank_seeders']);
            self::assertSame(self::LEVELS[$i], $contract['targets'][$i]['level']);
            foreach ($old['page']['blocks'] as $slot => $config) {
                $new = $target['after']['page']['blocks'][$slot];
                self::assertSame(array_diff_key($config, ['body' => true]), array_diff_key($new, ['body' => true]));
                self::assertSame($slot, $lesson['blocks'][$slot]['existing_source_index']);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $config, $slot + 1), M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
            }
            $practice = $lesson['blocks'][self::COUNTS[$i]];
            self::assertSame('practice-set', $practice['type']); self::assertNull($practice['existing_source_index']); self::assertSame('left', $practice['column']);
            self::assertSame(self::LEVELS[$i], $practice['level']);
            foreach (['selects', 'choices', 'inputs'] as $group) { self::assertCount(2, $practice['data'][$group]); }
            self::assertSame(self::BANKS[$i], $practice['data']['linked_practice']['seeder_classes']);
            self::assertSame(['4'], $practice['data']['linked_practice']['question_types']);
            self::assertSame('theory_links', $practice['data']['linked_practice']['source']);
        }
        self::assertSame('B2', $master['lessons'][0]['blocks'][5]['level']);
        self::assertSame('B2', $master['lessons'][1]['blocks'][4]['level']);
    }

    public function test_existing_generic_title_extractor_preserves_independent_before_h1_breadcrumb_and_learning_resource_without_m53_hook(): void
    {
        [, $patch, $master] = Package::load();
        $controller = (new \ReflectionClass(TheoryController::class))->newInstanceWithoutConstructor();
        $extract = new \ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        $localize = new \ReflectionMethod(PageController::class, 'applyLocalizedTitlesToModels');
        foreach ($patch['targets'] as $i => $target) {
            $page = $this->page($target, $this->blocks($target, $target['after'])); $raw = $page->getRawOriginal();
            $candidate = $extract->invoke($controller, $target['after']['page']['subtitle_html']); self::assertSame(self::H1[$i], $candidate);
            $localize->invoke($controller, collect([$page]), [$page->id => ['uk' => $candidate]], 'uk', 'uk');
            self::assertSame($candidate, $page->title); self::assertSame($raw, $page->getRawOriginal());
            app('view')->flushState();
            $view = view('theory.show', ['page' => $page, 'categories' => collect(), 'categoryPages' => collect(), 'topicTests' => collect(), 'selectedCategory' => null]);
            $request = Request::create($target['local_url'], 'GET');
            $route = (new Route(['GET'], 'theory/{categoryPath}/{pageSlug}', [TheoryController::class, 'showByCategoryPath']))->name('theory.show');
            $request->setRouteResolver(static fn () => $route);
            $html = (new AddStructuredData)->handle($request, static fn () => new Response($view, 200, ['Content-Type' => 'text/html']))->getContent();
            $xp = $this->xpath($html); self::assertCount(1, $xp->query('//h1')); self::assertSame($candidate, trim($xp->query('//h1')->item(0)->textContent));
            // Hero/footer are intentionally handled by theory.show, not their empty compatibility partials.
            $pageText = $this->learningText($html); $hero = $master['lessons'][$i]['blocks'][0]['data'];
            self::assertStringContainsString(self::DISPLAY[$i], $pageText);
            self::assertStringContainsString($this->plain($hero['intro']), $pageText);
            foreach ($hero['rules'] as $rule) {
                foreach (['label', 'text', 'example'] as $field) { self::assertStringContainsString($this->plain($rule[$field]), $pageText); }
            }
            $footer = array_values(array_filter($master['lessons'][$i]['blocks'], static fn ($item) => $item['type'] === 'navigation-chips'))[0]['data'];
            foreach ($footer['items'] as $item) {
                self::assertStringContainsString($this->plain($item['label']), $pageText);
                if (!($item['current'] ?? false)) { self::assertGreaterThan(0, $xp->query('//*[@href="'.$item['url'].'"]')->length); }
            }
            $crumb = $xp->query('//div[contains(concat(" ",normalize-space(@class)," ")," nd-page ")]/nav[@aria-label]/span[last()]');
            self::assertCount(1, $crumb); self::assertSame($candidate, trim($crumb->item(0)->textContent));
            $learning = [];
            foreach ($xp->query('//script[@type="application/ld+json"]') as $node) {
                $json = json_decode($node->textContent, true, flags: JSON_THROW_ON_ERROR);
                foreach ($json['@graph'] ?? [] as $item) { if (($item['@type'] ?? null) === 'LearningResource') { $learning[] = $item; } }
            }
            self::assertCount(1, $learning); self::assertSame($candidate, $learning[0]['name']); self::assertSame($candidate, $learning[0]['headline']);
            self::assertSame($raw, $page->getRawOriginal());
        }
        self::assertStringNotContainsString('M53', File::get(base_path('app/Http/Controllers/PageController.php')));
    }

    public function test_all_eighteen_own_tasks_have_visible_supported_conditions_exact_tokens_and_full_translated_feedback(): void
    {
        [, $patch, $master] = Package::load(); $expected = $this->feedbackExpectations(); $seen = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $practice = $lesson['blocks'][self::COUNTS[$i]]['data'];
            foreach (['selects', 'choices', 'inputs'] as $group) {
                foreach ($practice[$group] as $task) {
                    self::assertArrayHasKey($task['id'], $expected); self::assertNotContains($task['id'], $seen); $seen[] = $task['id'];
                    [$english, $uk] = $expected[$task['id']]; self::assertContains($task['answer'], $task['accepted']);
                    foreach ($task['options'] ?? $task['accepted'] as $answer) { self::assertArrayHasKey(mb_strtolower(trim($answer)), $task['feedback']); }
                    foreach ($task['feedback'] as $feedback) {
                        foreach ($uk as $translation) { self::assertStringContainsString($translation, $feedback); }
                        if ($task['id'] === 'm53-get-versus-causative') {
                            foreach ($english as $sentence) { self::assertStringContainsString($sentence, $feedback); }
                        } else { self::assertNotEmpty(array_filter($english, static fn ($sentence) => str_contains($feedback, $sentence))); }
                    }
                    if ($group === 'inputs') {
                        self::assertStringContainsString('/', $task['before']); self::assertMatchesRegularExpression('/[\x{0400}-\x{04FF}]/u', $task['after']);
                        self::assertStringNotContainsString($task['answer'], $task['before']); self::assertStringNotContainsString($task['answer'], $task['after']);
                        self::assertArrayNotHasKey('hint', $task); self::assertArrayNotHasKey('placeholder', $task);
                        $tokens = array_map('trim', explode('/', $task['before'])); $words = preg_split('/\s+/u', $task['answer']);
                        self::assertNotSame(implode(' ', $tokens), $task['answer']); sort($tokens); sort($words); self::assertSame($words, $tokens);
                        foreach ($task['accepted'] as $alias) {
                            self::assertArrayHasKey(mb_strtolower(preg_replace('/[.!?]$/u', '', trim($alias))), $task['feedback'], 'Existing engine accepts absent terminal punctuation; finite feedback must stay bilingual.');
                        }
                    } else { self::assertCount(3, $task['options']); }
                }
            }
            $after = json_decode($patch['targets'][$i]['after']['page']['blocks'][self::COUNTS[$i]]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach (['selects', 'choices', 'inputs'] as $group) { self::assertSame($practice[$group], $after[$group]); }
        }
        self::assertSame(array_keys($expected), $seen);
    }

    public function test_shared_renderer_preserves_every_author_basic_field_translation_table_formula_and_practice_condition(): void
    {
        [, $patch, $master] = Package::load();
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']); $lesson = $master['lessons'][$i];
            foreach ($lesson['blocks'] as $slot => $author) {
                $block = $blocks[$slot + 1]; $snapshot = $block->getAttributes(); $html = $this->render($block); $text = $this->learningText($html); $xp = $this->xpath($html);
                self::assertSame($snapshot, $block->getAttributes());
                foreach (['m53-section', 'm53-detail', 'm53-styles', '<style'] as $forbidden) { self::assertStringNotContainsString($forbidden, $html); }
                $data = $author['data'];
                if (in_array($author['type'], ['forms-grid', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list'], true)) {
                    self::assertStringContainsString('theory.content:node', $html, 'Canonical content path, not an M53 view.');
                    foreach (['title', 'intro', 'warning'] as $field) { if (!empty($data[$field])) { self::assertStringContainsString($this->plain($data[$field]), $text); } }
                }
                if ($author['type'] === 'forms-grid') {
                    foreach ($data['items'] as $item) { foreach (['label', 'title', 'subtitle'] as $field) { self::assertStringContainsString($this->plain($item[$field]), $text); } }
                }
                if ($author['type'] === 'usage-panels') {
                    $points = $xp->query('//*[@data-theory-point-panel]'); self::assertCount(count($data['sections']), $points);
                    foreach ($data['sections'] as $n => $section) {
                        self::assertSame($section['color'], $points->item($n)->getAttribute('data-theory-accent'));
                        foreach (['label', 'description', 'note'] as $field) { if (!empty($section[$field])) { self::assertStringContainsString($this->plain($section[$field]), $text); } }
                        foreach ($section['examples'] ?? [] as $example) {
                            self::assertStringContainsString($this->plain($example['en']), $text); self::assertStringContainsString($this->plain($example['ua']), $text);
                        }
                    }
                }
                if ($author['type'] === 'comparison-table') {
                    self::assertCount(1, $xp->query('//table')); self::assertCount(count($data['rows']), $xp->query('//tbody/tr'));
                    foreach ($data['headers'] as $header) { self::assertStringContainsString($this->plain($header), $text); }
                    foreach ($data['rows'] as $row) { foreach ($row['cells'] as $cell) { self::assertStringContainsString($this->plain($cell), $text); } }
                }
                if ($author['type'] === 'mistakes-grid') {
                    foreach ($data['items'] as $item) {
                        foreach (['label', 'title', 'wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) { self::assertStringContainsString($this->plain($item[$field]), $text); }
                    }
                }
                if ($author['type'] === 'summary-list') { foreach ($data['items'] as $item) { self::assertStringContainsString($this->plain($item), $text); } }
                if ($author['type'] === 'practice-set') {
                    self::assertCount(3, $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]'));
                    self::assertCount(2, $xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]'));
                    foreach ($data['inputs'] as $input) { self::assertStringContainsString($this->plain($input['after']), $text); }
                    foreach (['selects', 'choices'] as $group) {
                        foreach ($data[$group] as $item) { self::assertStringContainsString($this->plain($item['label']), $text); }
                    }
                }
            }
        }
    }

    public function test_five_details_remain_exact_owned_closed_points_with_all_learning_fields_in_server_text(): void
    {
        [, $patch, $master] = Package::load();
        $expected = [
            [['causative-services', '/sections/1'], ['causative-tenses', '/items/3']],
            [['get-tenses', '/sections/3']],
            [['impersonal-infinitives', '/sections/1'], ['impersonal-more-forms', '/items/7']],
        ];
        foreach ($patch['targets'] as $i => $target) {
            $lesson = $master['lessons'][$i]; $blocks = $this->blocks($target, $target['after']);
            self::assertSame($expected[$i], array_map(static fn ($detail) => [$detail['owner_block_key'], $detail['owner_pointer']], $lesson['details']));
            foreach ($lesson['details'] as $detail) {
                $slot = array_search($detail['owner_block_key'], array_column($lesson['blocks'], 'key'), true); self::assertIsInt($slot);
                $block = $blocks[$slot + 1]; $shown = Package::presentation($block, json_decode($block->body, true, flags: JSON_THROW_ON_ERROR));
                $index = (int) explode('/', $detail['owner_pointer'])[2]; self::assertSame([$index], array_keys($shown['points']));
                self::assertSame($detail['fragment']['data']['sections'], array_column($shown['points'][$index]['fragments'], 'value'));
                $html = $this->render($block); $xp = $this->xpath($html); $text = $this->learningText($html);
                self::assertCount(1, $xp->query('//details[@data-theory-details and not(@open)]')); self::assertCount(0, $xp->query('//details[@data-theory-details and @open]'));
                foreach ($detail['fragment']['data']['sections'] as $section) {
                    foreach (['label', 'description', 'note'] as $field) { if (!empty($section[$field])) { self::assertStringContainsString($this->plain($section[$field]), $text); } }
                    foreach ($section['examples'] as $example) {
                        self::assertStringContainsString($this->plain($example['en']), $text); self::assertStringContainsString($this->plain($example['ua']), $text);
                    }
                }
            }
        }
    }

    public function test_foreign_owner_locale_metadata_and_stale_body_cannot_acquire_m53_presentation_or_bypass_contract(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach ($patch['targets'] as $target) {
            foreach ($this->blocks($target, $target['after'])->slice(1) as $block) {
                $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR); self::assertNotNull(Package::presentation($block, $data));
                foreach (['page_id' => 99999, 'page_category_id' => 99999, 'seeder' => 'ForeignOwner', 'locale' => 'en', 'uuid' => 'foreign',
                    'type' => 'box', 'sort_order' => 99999, 'heading' => 'foreign', 'level' => 'C2', 'css_class' => 'foreign', 'body' => $block->body."\n"] as $field => $value) {
                    $foreign = clone $block; $foreign->$field = $value; self::assertNull(Package::presentation($foreign, $data), $field);
                }
                self::assertNull(TheoryContentSource::resolve($block)['presentation']);
                $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true])['presentation'];
                if (in_array($block->type, ['hero', 'navigation-chips'], true)) { self::assertNull($resolved); }
                else { self::assertSame(Package::presentation($block, $data), $resolved); }
            }
        }
        foreach (['bank', 'level', 'locale', 'ancestry', 'title', 'styled', 'view'] as $case) {
            $badMaster = $master; $badPatch = $patch;
            if ($case === 'bank') { $badMaster['lessons'][0]['blocks'][10]['data']['linked_practice']['seeder_classes'] = self::BANKS[2]; }
            if ($case === 'level') { $badMaster['lessons'][0]['technical_block_level'] = 'B2'; }
            if ($case === 'locale') { $badMaster['lessons'][0]['locale'] = 'en'; }
            if ($case === 'ancestry') { $badPatch['targets'][0]['ancestry'] = ['modal-verbs']; }
            if ($case === 'title') { $badMaster['lessons'][0]['subtitle_html'] = '<p><strong>Foreign title</strong> Пояснення.</p>'; }
            if ($case === 'styled') { $badMaster['lessons'][0]['blocks'][1]['data']['intro'] = '<p class="foreign">Пояснення.</p>'; }
            if ($case === 'view') { $badMaster['lessons'][0]['blocks'][1]['data']['view'] = 'theory.foreign'; }
            $badPatch['targets'][0]['after'] = Package::definition($badMaster['lessons'][0], $before['targets'][0]['before']);
            $this->refusal(static fn () => Package::validate($before, $badPatch, $badMaster, $contract));
        }
        $bad = $contract; $bad['targets'] = []; $this->refusal(static fn () => Package::validate($before, $patch, $master, $bad));
    }
}
