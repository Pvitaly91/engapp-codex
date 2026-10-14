<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Http\Controllers\TestJsV2Controller;
use App\Http\Controllers\TheoryController;
use App\Http\Middleware\AddStructuredData;
use App\Models\Page;
use App\Models\Question;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M50ObligationAdvicePackage;
use App\Support\M51ModalMeaningsPackage as Package;
use App\Support\PageMetadata;
use App\Support\TheoryContentSource;
use App\Support\TheorySentenceBuilderAcceptance;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** In-memory models and actual shared Blade/controller methods; no working environment, MySQL, seeder or HTTP requests. */
class M51ModalMeaningsPackageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); app()->setLocale('uk'); $this->withoutVite();
        self::assertSame('testing', app()->environment());
        self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
    }

    private function blocks(array $target, array $definition): Collection
    {
        $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'],
            'level' => $definition['page']['subtitle_level'] ?? null, 'uuid_key' => $definition['page']['subtitle_uuid_key'] ?? 'subtitle'];
        $blocks = [];
        foreach ([$subtitle, ...$definition['page']['blocks']] as $position => $config) {
            $block = new TextBlock;
            $block->setRawAttributes(['id' => 5100000 + $target['page_binding']['id'] * 100 + $position,
                'page_id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
                'uuid' => M26DetailPackage::uuid($target['identity'], $config, $position), 'seeder' => $target['identity'], 'locale' => 'uk',
                'type' => $config['type'], 'column' => $config['column'] ?? null, 'level' => $config['level'] ?? null,
                'heading' => $config['heading'] ?? null, 'css_class' => $config['css_class'] ?? null, 'body' => $config['body'],
                'sort_order' => $position, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'], true);
            $block->setRelation('tags', new Collection); $block->setRelation('page', null); $blocks[] = $block;
        }

        return new Collection($blocks);
    }

    private function page(array $target, Collection $blocks): Page
    {
        $page = new Page;
        $page->setRawAttributes(['id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
            'type' => 'theory', 'seeder' => $target['identity'], 'slug' => $target['slug'], 'title' => $target['after']['page']['title'],
            'text' => $target['after']['page']['subtitle_text']], true);
        $page->setRelation('textBlocks', $blocks); $page->setRelation('tags', new Collection); $page->setRelation('category', null);

        return $page;
    }

    private function render(TextBlock $block): string
    {
        app('view')->flushState();

        return view('theory.partials.content-block', ['block' => $block, 'theoryCanonical' => true,
            'm42StyleContext' => true, 'practiceQuestions' => collect(), 'lessonLinks' => []])->render();
    }

    private function pageResponse(array $target, Page $page): string
    {
        app('view')->flushState();
        $view = view('theory.show', ['page' => $page, 'categories' => collect(), 'categoryPages' => collect(),
            'topicTests' => collect(), 'selectedCategory' => null]);
        $request = Request::create($target['local_url'], 'GET');
        $route = (new Route(['GET'], 'theory/{categoryPath}/{pageSlug}', [TheoryController::class, 'showByCategoryPath']))->name('theory.show');
        $request->setRouteResolver(static fn () => $route);

        return (new AddStructuredData)->handle($request, static fn () => new Response($view, 200, ['Content-Type' => 'text/html']))->getContent();
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument('1.0', 'UTF-8'); $previous = libxml_use_internal_errors(true);
        try { $dom->loadHTML('<!doctype html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }

        return new DOMXPath($dom);
    }

    private function plain(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function refusal(callable $call): void
    {
        try { $call(); self::fail('Expected M51 closed package refusal.'); }
        catch (RuntimeException $error) { self::assertNotSame('', $error->getMessage()); }
    }

    /** These editorial expectations are declared independently of generated AFTER bodies. */
    private function ownFeedback(): array
    {
        return [
            'm51-deduction-must-base' => ['Nora must know the route.', 'Нора, напевно, знає маршрут.'],
            'm51-deduction-might-process' => ['They might be working upstairs now.', 'Можливо, вони зараз працюють нагорі.'],
            'm51-deduction-may-not-meaning' => ['The guide may not be available today.', 'Можливо, гід сьогодні недоступний.'],
            'm51-deduction-cant-meaning' => ["Leo is speaking to us live. He can't be asleep.", 'Лео розмовляє з нами наживо. Не може бути, щоб він спав.'],
            'm51-deduction-build-process' => ['They might be waiting outside.', 'Можливо, вони чекають надворі.'],
            'm51-deduction-build-negative' => ["This code can't have four digits.", 'Не може бути, щоб цей код мав чотири цифри.'],
            'm51-past-must-have' => ['The owner must have opened the shop yesterday.', 'Власник, напевно, відчинив магазин учора.'],
            'm51-past-shouldnt-v3' => ["I shouldn't have deleted the file.", 'Мені не слід було видаляти файл.'],
            'm51-past-unused-meaning' => ['Oleh could have taken the ferry, but he chose the train.', 'Олег міг скористатися поромом, але обрав потяг.'],
            'm51-past-expectation-meaning' => ["The download should have finished by now. I haven't checked the laptop.", 'Завантаження вже мало б завершитися. Я ще не перевірив ноутбук.'],
            'm51-past-build-impossible' => ["Alex can't have read the message yesterday.", 'Не може бути, щоб Алекс прочитав повідомлення вчора.'],
            'm51-past-build-criticism' => ['Iryna should not have told the secret.', 'Ірині не слід було розповідати таємницю.'],
            'm51-requests-could-base' => ['Could you help me with this box?', 'Не міг би ти допомогти мені з цією коробкою?'],
            'm51-requests-like-to' => ['Would you like to come with me?', 'Хочеш піти зі мною?'],
            'm51-requests-me-to-actor' => ['Would you like me to print the tickets?', 'Хочеш, щоб я надрукувала квитки?'],
            'm51-requests-shared-plan' => ["Why don't we meet at six?", 'Чому б нам не зустрітися о шостій?'],
            'm51-requests-build-request' => ['Would you close the gate, please?', 'Не зачиниш хвіртку, будь ласка?'],
            'm51-requests-build-negative-plan' => ["Let's not leave the bags here.", 'Давай не залишатимемо сумки тут.'],
        ];
    }

    public function test_frozen_projection_keeps_independent_before_owners_levels_components_and_dynamic_bank_contracts(): void
    {
        [$before, $patch, $master] = Package::load(); Package::validate($before, $patch, $master);
        self::assertSame('7b0ca49b3cbfd7135b80f6931cdd42834eb24c53', $master['base_sha']);
        self::assertSame([73, 77, 78], array_column(array_column($before['targets'], 'page_binding'), 'id'));
        self::assertSame([73, 77, 78], array_column(array_column($patch['targets'], 'page_binding'), 'id'));
        self::assertSame(Package::OWNERS, array_column($before['targets'], 'identity'));
        foreach ([Package::BEFORE => Package::BEFORE_SHA, Package::SOURCE => Package::SOURCE_SHA, Package::MASTER => Package::MASTER_SHA] as $path => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha); self::assertSame($sha, hash_file('sha256', base_path($path)));
        }
        $contracts = TheorySentenceBuilderAcceptance::contracts(base_path());
        $catalogue = array_column(TheorySentenceBuilderAcceptance::discoverTargets(base_path()), null, 'identity');
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before'];
            self::assertSame($old['page']['title'], $target['after']['page']['title']);
            self::assertSame($target['after'], Package::definition($master['lessons'][$i], $old));
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertSame([], $catalogue[$target['identity']]['errors']);
            self::assertSame([Package::BANKS[$i]], $contracts[$target['identity']]['bank_seeders']);
            self::assertSame(Package::TECHNICAL_LEVELS[$i], $contracts[$target['identity']]['level']);
            self::assertSame(Package::DISPLAY_LEVELS[$i], $master['lessons'][$i]['blocks'][0]['data']['level']);
            self::assertSame(['hero', 'forms-grid', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips', 'practice-set'],
                array_column($target['after']['page']['blocks'], 'type'));
            foreach ($old['page']['blocks'] as $slot => $config) {
                $new = $target['after']['page']['blocks'][$slot];
                self::assertSame(array_diff_key($config, ['body' => true]), array_diff_key($new, ['body' => true]));
                self::assertSame(M26DetailPackage::uuid($target['identity'], $config, $slot + 1), M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
            }
            $practice = json_decode($target['after']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertSame('theory_links', $practice['linked_practice']['source']);
            self::assertSame(['4'], $practice['linked_practice']['question_types']);
            self::assertSame([Package::BANKS[$i]], $practice['linked_practice']['seeder_classes']);
            foreach (['selects', 'choices', 'inputs'] as $group) { self::assertCount(2, $practice[$group]); }
            self::assertLessThanOrEqual(65535, strlen($target['after']['page']['blocks'][6]['body']));
        }
        foreach (M50ObligationAdvicePackage::OWNERS as $owner) { self::assertArrayHasKey($owner, $catalogue); self::assertSame([], $catalogue[$owner]['errors']); }
    }

    public function test_actual_generic_subtitle_localizer_keeps_all_three_h1_breadcrumbs_and_learning_resource_without_new_title_workaround(): void
    {
        [$before, $patch, $master] = Package::load();
        $controller = (new \ReflectionClass(TheoryController::class))->newInstanceWithoutConstructor();
        $extract = new \ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        $preview = new \ReflectionMethod(PageController::class, 'extractLocalizedPreviewFromSubtitle');
        $localize = new \ReflectionMethod(PageController::class, 'applyLocalizedTitlesToModels');
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']); $page = $this->page($target, $blocks);
            $expected = $before['targets'][$i]['before']['page']['title'];
            $raw = $page->getRawOriginal(); $attributes = $page->getAttributes(); $blockAttributes = $blocks->map(static fn ($b) => $b->getAttributes())->all();
            $title = $extract->invoke($controller, $target['after']['page']['subtitle_html']); self::assertSame($expected, $title);
            self::assertNotSame($expected, $preview->invoke($controller, $target['after']['page']['subtitle_html'], $title));
            DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
            self::assertNull(M50ObligationAdvicePackage::displayTitle($page, 'uk', 'theory'), 'M51 does not broaden the finite M50 title guard');
            $localize->invoke($controller, collect([$page]), [$page->getKey() => ['uk' => $title]], 'uk', 'uk');
            self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
            self::assertSame($attributes, $page->getAttributes()); self::assertSame($raw, $page->getRawOriginal());
            $html = $this->pageResponse($target, $page); $xp = $this->xpath($html);
            self::assertCount(1, $xp->query('//h1')); self::assertSame($expected, trim($xp->query('//h1')->item(0)->textContent));
            $breadcrumb = $xp->query('//div[contains(concat(" ",normalize-space(@class)," ")," nd-page ")]/nav[@aria-label]/span[last()]');
            self::assertCount(1, $breadcrumb); self::assertSame($expected, trim($breadcrumb->item(0)->textContent));
            $seo = PageMetadata::theory($expected, '', $master['lessons'][$i]['blocks'][0]['data']['intro'], $target['identity'], 'uk');
            self::assertSame($seo['title'], $xp->query('//title')->item(0)->textContent);
            self::assertSame($seo['description'], $xp->query('//meta[@name="description"]')->item(0)->getAttribute('content'));
            $graphs = [];
            foreach ($xp->query('//script[@type="application/ld+json"]') as $script) {
                $graph = json_decode($script->textContent, true, flags: JSON_THROW_ON_ERROR);
                foreach ($graph['@graph'] ?? [] as $node) { $graphs[$node['@type']][] = $node; }
            }
            self::assertCount(1, $graphs['LearningResource'] ?? []); self::assertCount(1, $graphs['BreadcrumbList'] ?? []);
            self::assertSame($expected, $graphs['LearningResource'][0]['name']); self::assertSame($expected, $graphs['LearningResource'][0]['headline']);
            self::assertSame(Str::limit($this->plain($master['lessons'][$i]['blocks'][0]['data']['intro']), 300, '…'), $graphs['LearningResource'][0]['description']);
            $items = $graphs['BreadcrumbList'][0]['itemListElement']; self::assertSame($expected, $items[count($items) - 1]['name']);
            self::assertSame($raw, $page->getRawOriginal()); self::assertSame($blockAttributes, $blocks->map(static fn ($b) => $b->getAttributes())->all());
        }
    }

    public function test_actual_native_components_keep_basic_points_bilingual_corrections_and_complete_practice_without_package_markup(): void
    {
        [, $patch, $master] = Package::load();
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']);
            foreach ($master['lessons'][$i]['blocks'] as $slot => $author) {
                $block = $blocks[$slot + 1]; $snapshot = $block->getAttributes(); $html = $this->render($block); $xp = $this->xpath($html);
                self::assertSame($snapshot, $block->getAttributes());
                self::assertStringNotContainsString('m51-section', $html); self::assertStringNotContainsString('m51-detail', $html);
                self::assertStringNotContainsString('m51-styles', $html); self::assertStringNotContainsString('<style', $html);
                if (in_array($author['type'], ['forms-grid', 'usage-panels', 'mistakes-grid', 'summary-list'], true)) {
                    self::assertStringContainsString('<!-- theory.content:node -->', $html);
                    self::assertGreaterThanOrEqual(1, $xp->query('//*[@data-theory-component="section"]')->length);
                }
                if ($author['type'] === 'usage-panels') {
                    $points = $xp->query('//*[@data-theory-point-panel]'); self::assertCount(count($author['data']['sections']), $points);
                    foreach ($author['data']['sections'] as $n => $section) {
                        self::assertSame($section['color'], $points->item($n)->getAttribute('data-theory-accent'));
                        self::assertStringContainsString($section['label'], $points->item($n)->textContent);
                        foreach ($section['examples'] as $example) {
                            self::assertStringContainsString($this->plain($example['en']), $this->plain($html));
                            self::assertStringContainsString($this->plain($example['ua']), $this->plain($html));
                        }
                    }
                }
                if ($author['type'] === 'mistakes-grid') {
                    $items = $xp->query('//*[@data-theory-component="mistake"]'); self::assertCount(count($author['data']['items']), $items);
                    foreach ($author['data']['items'] as $n => $item) {
                        foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua'] as $field) {
                            self::assertStringContainsString($this->plain($item[$field]), preg_replace('/\s+/u', ' ', $items->item($n)->textContent));
                        }
                    }
                }
                if ($author['type'] === 'practice-set') {
                    self::assertCount(3, $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]'));
                    self::assertCount(2, $xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]'));
                    foreach ($author['data']['inputs'] as $input) { self::assertStringContainsString($input['after'], $html); }
                }
            }
        }
    }

    public function test_all_eighteen_own_tasks_have_independent_full_sentence_translations_and_feedback_for_visible_options_aliases_and_known_wrong_probes(): void
    {
        [, $patch, $master] = Package::load(); $expected = $this->ownFeedback(); $seen = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $practice = $lesson['blocks'][6]['data'];
            foreach (['selects', 'choices', 'inputs'] as $group) {
                foreach ($practice[$group] as $task) {
                    self::assertArrayHasKey($task['id'], $expected); self::assertNotContains($task['id'], $seen); $seen[] = $task['id'];
                    [$english, $ukrainian] = $expected[$task['id']];
                    self::assertContains($task['answer'], $task['accepted']);
                    foreach ($task['options'] ?? $task['accepted'] as $option) {
                        self::assertArrayHasKey(mb_strtolower(trim($option)), $task['feedback']);
                    }
                    foreach ($task['feedback'] as $feedback) {
                        self::assertStringContainsString($ukrainian, $feedback);
                        $fullEnglish = $group === 'inputs' ? array_merge([$english], $task['accepted']) : [$english];
                        self::assertNotEmpty(array_filter($fullEnglish, static fn ($candidate) => str_contains($feedback, $candidate)), 'Complete English sentence, not a gap-only key');
                    }
                    if ($group === 'inputs') {
                        self::assertStringContainsString('/', $task['before']); self::assertMatchesRegularExpression('/[\x{0400}-\x{04FF}]/u', $task['after']);
                        self::assertStringNotContainsString($task['answer'], $task['before']); self::assertStringNotContainsString($task['answer'], $task['after']);
                        $tokens = array_map('trim', explode('/', $task['before'])); self::assertGreaterThan(1, count($tokens));
                        self::assertNotSame($task['answer'], implode(' ', $tokens), 'Bank is not already in answer order');
                    }
                }
            }
            $body = json_decode($patch['targets'][$i]['after']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach (['selects', 'choices', 'inputs'] as $group) { self::assertSame($practice[$group], $body[$group]); }
        }
        self::assertSame(array_keys($expected), $seen);
    }

    public function test_exact_details_keep_one_owner_full_basic_and_native_fragments_and_all_foreign_bindings_fall_back(): void
    {
        [, $patch, $master] = Package::load();
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']);
            foreach ($master['lessons'][$i]['blocks'] as $slot => $author) {
                $block = $blocks[$slot + 1]; $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
                $shown = Package::presentation($block, $data); self::assertNotNull($shown);
                $basic = $shown['data']; unset($basic['m51_native_v1']); self::assertSame($author['data'], $basic);
                foreach (['page_id' => 9999, 'page_category_id' => 9999, 'seeder' => Package::OWNERS[($i + 1) % 3], 'locale' => 'en',
                    'uuid' => 'foreign', 'sort_order' => 999, 'heading' => 'foreign', 'level' => 'C2', 'css_class' => 'foreign'] as $field => $value) {
                    $foreign = clone $block; $foreign->$field = $value; self::assertNull(Package::presentation($foreign, $data), $field);
                }
                $stale = clone $block; $stale->body .= "\n"; self::assertNull(Package::presentation($stale, $data));
                self::assertNull(TheoryContentSource::resolve($block)['presentation']);
                if (!in_array($block->type, ['hero', 'navigation-chips'], true)) {
                    self::assertSame($shown, TheoryContentSource::resolve($block, ['m42StyleContext' => true])['presentation']);
                }
            }
            foreach ($master['lessons'][$i]['details'] as $detail) {
                $slot = array_search($detail['owner_block_key'], array_column($master['lessons'][$i]['blocks'], 'key'), true);
                $block = $blocks[$slot + 1]; $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
                $shown = Package::presentation($block, $data); $index = (int) explode('/', $detail['owner_pointer'])[2];
                self::assertSame([$index], array_keys($shown['points']));
                self::assertSame($detail['fragment']['data']['sections'], array_column($shown['points'][$index]['fragments'], 'value'));
                $html = $this->render($block); $xp = $this->xpath($html);
                self::assertCount(1, $xp->query('//details[@data-theory-details]'));
                self::assertCount(1, $xp->query('//*[@data-theory-point-index="'.$index.'"]'));
                foreach ($detail['fragment']['data']['sections'] as $section) {
                    foreach ($section['examples'] as $example) {
                        self::assertStringContainsString($this->plain($example['en']), $this->plain($html));
                        self::assertStringContainsString($this->plain($example['ua']), $this->plain($html));
                    }
                }
                $stale = clone $block; $stale->body .= "\n"; $fallback = $this->render($stale);
                self::assertCount(0, $this->xpath($fallback)->query('//details[@data-theory-details]'));
                foreach ($detail['fragment']['data']['sections'] as $section) { self::assertStringContainsString($this->plain($section['description']), $this->plain($fallback)); }
            }
        }
        $block = $this->blocks($patch['targets'][0], $patch['targets'][0]['after'])[2];
        foreach (['en', 'pl'] as $locale) { app()->setLocale($locale); self::assertNull(Package::presentation($block, json_decode($block->body, true))); }
    }

    public function test_foreign_bank_empty_inputs_changed_levels_bad_title_and_styled_or_view_selecting_payload_are_refused(): void
    {
        [$before, $patch, $master] = Package::load();
        foreach (['bank', 'inputs', 'technical', 'display', 'subtitle', 'view', 'styled'] as $change) {
            $bad = $master;
            if ($change === 'bank') { $bad['lessons'][0]['blocks'][6]['data']['linked_practice']['seeder_classes'] = [Package::BANKS[1]]; }
            if ($change === 'inputs') { $bad['lessons'][0]['blocks'][6]['data']['inputs'] = []; }
            if ($change === 'technical') { $bad['lessons'][0]['technical_block_level'] = 'A2'; }
            if ($change === 'display') { $bad['lessons'][0]['display_level'] = 'A2-B1'; }
            if ($change === 'subtitle') { $bad['lessons'][0]['subtitle_html'] = '<p><strong>Foreign title</strong> пояснює тему.</p>'; }
            if ($change === 'view') { $bad['lessons'][0]['blocks'][1]['data']['view'] = 'theory.foreign'; }
            if ($change === 'styled') { $bad['lessons'][0]['blocks'][1]['data']['intro'] = '<p class="other">Пояснення</p>'; }
            $changed = $patch; $changed['targets'][0]['after'] = Package::definition($bad['lessons'][0], $before['targets'][0]['before']);
            $this->refusal(static fn () => Package::validate($before, $changed, $bad));
        }
    }

    public function test_courses_keep_exact_independent_before_non_uk_blocks_and_original_models_without_queries(): void
    {
        [$before, $patch] = Package::load();
        foreach ($patch['targets'] as $i => $target) {
            $old = $this->blocks($target, $before['targets'][$i]['before']); $after = $this->blocks($target, $target['after']);
            foreach (['en', 'pl'] as $locale) {
                foreach ($old as $block) { $other = clone $block; $other->locale = $locale; $other->uuid = $locale.'-'.$other->uuid; $other->syncOriginal(); $after->push($other); }
            }
            $page = $this->page($target, $after); $attributes = $page->getAttributes(); $snapshot = $after->map(static fn ($b) => $b->getAttributes())->all();
            DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
            foreach (['uk', 'en', 'pl'] as $locale) {
                app()->setLocale($locale); $copy = Package::preserveCoursePage($page); self::assertNotSame($page, $copy);
                self::assertSame($before['targets'][$i]['before']['page']['subtitle_text'], $copy->text);
                self::assertSame($old->map(static fn ($b) => $b->getAttributes())->all(), $copy->textBlocks->where('locale', 'uk')->values()->map(static fn ($b) => $b->getAttributes())->all());
                foreach ($after->reject(static fn ($b) => $b->locale === 'uk') as $other) { self::assertSame($other, $copy->textBlocks->firstWhere('uuid', $other->uuid)); }
            }
            foreach (['uuid' => 'foreign', 'page_id' => 99999, 'page_category_id' => 99999, 'seeder' => 'ForeignOwner', 'locale' => 'en',
                'sort_order' => 1, 'type' => 'box', 'column' => 'left', 'heading' => 'changed', 'level' => 'C2', 'css_class' => 'changed', 'body' => $after[0]->body."\n"] as $field => $value) {
                $subtitle = clone $after[0]; $subtitle->$field = $value;
                $foreign = clone $page; $foreign->setRelation('textBlocks', new Collection([$subtitle, ...array_slice($after->all(), 1)]));
                self::assertSame($foreign, Package::preserveCoursePage($foreign), 'Edited subtitle '.$field);
            }
            foreach (['id' => 99999, 'page_category_id' => 99999, 'seeder' => 'ForeignOwner', 'type' => 'course', 'slug' => 'foreign', 'title' => 'Changed', 'text' => 'Changed'] as $field => $value) {
                $foreign = clone $page; $foreign->$field = $value; self::assertSame($foreign, Package::preserveCoursePage($foreign), 'Edited Page '.$field);
            }
            self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
            self::assertSame($attributes, $page->getAttributes()); self::assertSame($snapshot, $after->map(static fn ($b) => $b->getAttributes())->all());
        }
    }

    public function test_standalone_help_keeps_exact_before_and_does_not_mutate_models_or_hide_foreign_blocks(): void
    {
        [$before, $patch] = Package::load();
        $controller = (new \ReflectionClass(TestJsV2Controller::class))->newInstanceWithoutConstructor();
        $serialize = new \ReflectionMethod(TestJsV2Controller::class, 'resolveTheoryBlocksForQuestion');
        foreach ($patch['targets'] as $i => $target) {
            $old = $this->blocks($target, $before['targets'][$i]['before']); $after = $this->blocks($target, $target['after']);
            $question = new Question; $question->setRawAttributes(['id' => 7100000 + $i, 'theory_text_block_uuid' => null], true);
            $question->setRelation('theoryTextBlocks', $old); $expected = $serialize->invoke($controller, $question);
            $question->setRelation('theoryTextBlocks', $after); $snapshot = $after->map(static fn ($b) => $b->getAttributes())->all();
            foreach (['uk', 'en', 'pl'] as $locale) { app()->setLocale($locale); self::assertSame($expected, $serialize->invoke($controller, $question)); }
            foreach ($after as $position => $block) {
                if ($position === 7) { self::assertNull(Package::preserveExcludedTextBlock($block, 'standalone_test')); }
                else {
                    $copy = Package::preserveExcludedTextBlock($block, 'standalone_test'); self::assertNotSame($block, $copy);
                    self::assertSame($old[$position]->body, $copy->body);
                    self::assertSame(array_diff_key($block->getAttributes(), ['body' => true]), array_diff_key($copy->getAttributes(), ['body' => true]));
                }
                self::assertSame($block, Package::preserveExcludedTextBlock($block, 'theory'));
                foreach (['locale' => 'en', 'seeder' => 'ForeignOwner', 'page_id' => 99999, 'page_category_id' => 99999, 'uuid' => 'foreign', 'level' => 'C2', 'body' => $block->body."\n"] as $field => $value) {
                    $foreign = clone $block; $foreign->$field = $value; self::assertSame($foreign, Package::preserveExcludedTextBlock($foreign, 'standalone_test'));
                }
            }
            self::assertSame($snapshot, $after->map(static fn ($b) => $b->getAttributes())->all());
        }
    }

    public function test_current_m50_h1_and_exact_course_subtitle_guards_remain_intact_after_m51_registration(): void
    {
        [$before, $patch, $master] = M50ObligationAdvicePackage::load(); M50ObligationAdvicePackage::validate($before, $patch, $master);
        $controller = (new \ReflectionClass(TheoryController::class))->newInstanceWithoutConstructor();
        $extract = new \ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        $localize = new \ReflectionMethod(PageController::class, 'applyLocalizedTitlesToModels');
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']); $page = $this->page($target, $blocks); $original = $page->getAttributes();
            $candidate = $extract->invoke($controller, $target['after']['page']['subtitle_html']);
            self::assertNotSame($before['targets'][$i]['before']['page']['title'], $candidate, 'Reproduce M50 historical generic-title mismatch');
            self::assertSame($target['after']['page']['title'], M50ObligationAdvicePackage::displayTitle($page, 'uk', 'theory'));
            $localize->invoke($controller, collect([$page]), [$page->getKey() => ['uk' => $candidate]], 'uk', 'uk');
            self::assertSame($original, $page->getAttributes()); self::assertSame($original, $page->getRawOriginal());
            self::assertSame($page, Package::preserveCoursePage($page));
            $restored = M50ObligationAdvicePackage::preserveCoursePage($page); self::assertNotSame($page, $restored);
            self::assertSame($before['targets'][$i]['before']['page']['subtitle_html'], $restored->textBlocks->firstWhere('type', 'subtitle')->body);
            $foreign = clone $page; $subtitle = clone $blocks[0]; $subtitle->heading = 'Foreign subtitle';
            $foreign->setRelation('textBlocks', new Collection([$subtitle, ...array_slice($blocks->all(), 1)]));
            self::assertSame($foreign, M50ObligationAdvicePackage::preserveCoursePage($foreign));
            $block = $blocks[3]; $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            self::assertNull(Package::presentation($block, $data));
            self::assertSame(M50ObligationAdvicePackage::presentation($block, $data), TheoryContentSource::resolve($block, ['m42StyleContext' => true])['presentation']);
        }
    }
}
