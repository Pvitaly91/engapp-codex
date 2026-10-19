<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Http\Controllers\TestJsV2Controller;
use App\Http\Controllers\TheoryController;
use App\Http\Middleware\AddStructuredData;
use App\Models\Page;
use App\Models\Question;
use App\Models\TextBlock;
use App\Services\Theory\TextBlockToQuestionsMatcherService;
use App\Support\M26DetailPackage;
use App\Support\M52PassiveBasicsPackage as Package;
use App\Support\TheoryContentSource;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Pure release/model/render checks plus isolated SQLite fixtures; never working .env, MySQL, HTTP or progress. */
class M52PassiveBasicsPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const BANKS = [
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoiceBasicsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoiceNegativesQuestionsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoiceModalVerbsAllLevelsLessonSeeder',
    ];

    private const BEFORE_TITLES = ['Пасивний стан (Passive Voice)', 'Negatives & Questions in Passive', 'Пасив з модальними дієсловами'];

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

    private function blocks(array $target, array $definition): ModelCollection
    {
        $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'],
            'level' => $definition['page']['subtitle_level'] ?? null, 'uuid_key' => $definition['page']['subtitle_uuid_key'] ?? 'subtitle'];
        $blocks = [];
        foreach ([$subtitle, ...$definition['page']['blocks']] as $position => $source) {
            $block = new TextBlock;
            $block->setRawAttributes(['id' => 5200000 + $target['page_binding']['id'] * 100 + $position,
                'page_id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
                'uuid' => M26DetailPackage::uuid($target['identity'], $source, $position), 'seeder' => $target['identity'], 'locale' => 'uk',
                'type' => $source['type'], 'column' => $source['column'] ?? null, 'level' => $source['level'] ?? null,
                'heading' => $source['heading'] ?? null, 'css_class' => $source['css_class'] ?? null, 'body' => $source['body'],
                'sort_order' => $position, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'], true);
            $block->setRelation('tags', new ModelCollection); $block->setRelation('page', null); $blocks[] = $block;
        }

        return new ModelCollection($blocks);
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

    private function render(TextBlock $block, ?Collection $questions = null): string
    {
        app('view')->flushState();

        return view('theory.partials.content-block', ['block' => $block, 'theoryCanonical' => true, 'm42StyleContext' => true,
            'practiceQuestions' => $questions ?? collect(), 'lessonLinks' => []])->render();
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
        try { $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }

        return new DOMXPath($dom);
    }

    private function plain(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function refusal(callable $call): void
    {
        try { $call(); self::fail('Expected M52 closed-source refusal.'); }
        catch (RuntimeException $error) { self::assertNotSame('', $error->getMessage()); }
    }

    /** Independent editorial expectations read before AFTER generation; not inferred from generated bodies. */
    private function ownFeedback(): array
    {
        return [
            'm52-formation-plural-present' => ['The parcels are sorted here every day.', 'Посилки сортують тут щодня.'],
            'm52-formation-v3-past' => ['The sign was written yesterday.', 'Цю табличку написали вчора.'],
            'm52-formation-retain-agent' => ['The suitcase was packed by Sofia.', 'Валізу спакувала Софія.'],
            'm52-formation-cause-instrument' => ['The fence was damaged by the storm. The paper was cut with scissors.', 'Буря пошкодила паркан.', 'Папір розрізали ножицями.'],
            'm52-formation-build-past' => ['The shelves were repaired by Nora yesterday.', 'Полиці вчора полагодила Нора.'],
            'm52-formation-build-present' => ['The maps are printed by Iryna.', 'Карти друкує Ірина.'],
            'm52-negative-perfect-not' => ['The report has not been sent yet.', 'Звіт ще не надіслали.'],
            'm52-negative-future-answer' => ['Will the windows be washed tomorrow? — Yes, they will.', 'Вікна помиють завтра? — Так.'],
            'm52-negative-who-patient' => ['Who was invited to the meeting?', 'Кого запросили на зустріч?'],
            'm52-negative-neutral-inversion' => ['Were the keys found?', 'Ключі знайшли?'],
            'm52-negative-build-question' => ['Was the parcel sent on Monday?', 'Посилку надіслали в понеділок?'],
            'm52-negative-build-perfect' => ['The plans have not been approved yet.', 'Плани ще не затвердили.'],
            'm52-modal-must-passive' => ['The battery must be checked before the flight.', 'Перед польотом акумулятор треба перевірити.'],
            'm52-modal-perfect-been' => ['The file should have been copied yesterday.', 'Файл слід було скопіювати учора.'],
            'm52-modal-no-obligation' => ["The receipt doesn't have to be printed.", 'Чек не обов’язково друкувати.'],
            'm52-modal-perfect-expectation' => ['The notice should have been sent by now.', 'Повідомлення, очікується, вже мали відправити.'],
            'm52-modal-build-prohibition' => ['The gate must not be opened during the test.', 'Під час випробування ворота відчиняти заборонено.'],
            'm52-modal-build-perfect-agent' => ['The cables should have been checked by Olha.', 'Кабелі слід було перевірити Ользі.'],
        ];
    }

    public function test_frozen_sources_keep_actual_variable_slots_metadata_anchors_and_independent_three_bank_contract(): void
    {
        [$before, $patch, $master, $contract] = Package::load(); Package::validate($before, $patch, $master, $contract);
        self::assertSame('0e2ed40c438400e2f2a3992314220fdf206db9a7', $master['base_sha']);
        self::assertSame([1, 98, 97], array_column(array_column($patch['targets'], 'page_binding'), 'id'));
        self::assertSame([1, 1, 1], array_column(array_column($patch['targets'], 'page_binding'), 'page_category_id'));
        foreach ([Package::MASTER => Package::MASTER_SHA, Package::BEFORE => Package::BEFORE_SHA,
            Package::SOURCE => Package::SOURCE_SHA, Package::CONTRACT => Package::CONTRACT_SHA] as $path => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha);
            self::assertSame($sha, hash_file('sha256', base_path($path)), 'Draft sentinels and immutable byte drift must not pass.');
        }
        $catalogue = array_column(Acceptance::discoverTargets(base_path()), null, 'identity');
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before'];
            self::assertSame($target['after'], Package::definition($master['lessons'][$i], $old));
            self::assertSame($target['after'], json_decode(File::get(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount([9, 11, 11][$i], $old['page']['blocks']);
            self::assertCount([10, 12, 12][$i], $target['after']['page']['blocks']);
            self::assertSame($old['page']['title'], $target['after']['page']['title']);
            self::assertSame([], $catalogue[$target['identity']]['errors']);
            self::assertSame([self::BANKS[$i]], $contract['targets'][$i]['bank_seeders']);
            self::assertSame(['A2', 'B1', 'B1'][$i], $contract['targets'][$i]['level']);
            foreach ($old['page']['blocks'] as $slot => $config) {
                $new = $target['after']['page']['blocks'][$slot];
                self::assertSame(array_diff_key($config, ['body' => true]), array_diff_key($new, ['body' => true]));
                self::assertSame(M26DetailPackage::uuid($target['identity'], $config, $slot + 1), M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
            }
            $practice = $master['lessons'][$i]['blocks'][[9, 11, 11][$i]];
            self::assertSame('practice-set', $practice['type']);
            foreach (['selects', 'choices', 'inputs'] as $group) { self::assertCount(2, $practice['data'][$group]); }
            self::assertSame([self::BANKS[$i]], $practice['data']['linked_practice']['seeder_classes']);
            self::assertSame(['4'], $practice['data']['linked_practice']['question_types']);
        }
    }

    public function test_generic_subtitle_extraction_keeps_before_display_titles_h1_breadcrumb_and_learning_resource_without_new_hook(): void
    {
        [, $patch] = Package::load();
        $controller = (new \ReflectionClass(TheoryController::class))->newInstanceWithoutConstructor();
        $extract = new \ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        $localize = new \ReflectionMethod(PageController::class, 'applyLocalizedTitlesToModels');
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']); $page = $this->page($target, $blocks); $raw = $page->getRawOriginal();
            $candidate = $extract->invoke($controller, $target['after']['page']['subtitle_html']);
            self::assertSame(self::BEFORE_TITLES[$i], $candidate);
            $localize->invoke($controller, collect([$page]), [$page->id => ['uk' => $candidate]], 'uk', 'uk');
            self::assertSame($candidate, $page->title); self::assertSame($raw, $page->getRawOriginal());
            $html = $this->pageResponse($target, $page); $xp = $this->xpath($html);
            self::assertCount(1, $xp->query('//h1')); self::assertSame($candidate, trim($xp->query('//h1')->item(0)->textContent));
            $breadcrumb = $xp->query('//div[contains(concat(" ",normalize-space(@class)," ")," nd-page ")]/nav[@aria-label]/span[last()]');
            self::assertCount(1, $breadcrumb); self::assertSame($candidate, trim($breadcrumb->item(0)->textContent));
            $learning = [];
            foreach ($xp->query('//script[@type="application/ld+json"]') as $node) {
                $json = json_decode($node->textContent, true, flags: JSON_THROW_ON_ERROR);
                foreach ($json['@graph'] ?? [] as $item) { if (($item['@type'] ?? null) === 'LearningResource') { $learning[] = $item; } }
            }
            self::assertCount(1, $learning); self::assertSame($candidate, $learning[0]['name']); self::assertSame($candidate, $learning[0]['headline']);
            self::assertSame($raw, $page->getRawOriginal());
        }
    }

    public function test_all_eighteen_tasks_keep_full_english_uk_feedback_and_visible_manual_token_conditions(): void
    {
        [, $patch, $master] = Package::load(); $expected = $this->ownFeedback(); $seen = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $practice = $lesson['blocks'][[9, 11, 11][$i]]['data'];
            foreach (['selects', 'choices', 'inputs'] as $group) {
                foreach ($practice[$group] as $task) {
                    self::assertArrayHasKey($task['id'], $expected); self::assertNotContains($task['id'], $seen); $seen[] = $task['id'];
                    [$english, $uk] = $expected[$task['id']]; self::assertContains($task['answer'], $task['accepted']);
                    foreach ($task['options'] ?? $task['accepted'] as $option) { self::assertArrayHasKey(mb_strtolower(trim($option)), $task['feedback']); }
                    foreach ($task['feedback'] as $feedback) {
                        self::assertStringContainsString($uk, $feedback);
                        if (isset($expected[$task['id']][2])) { self::assertStringContainsString($expected[$task['id']][2], $feedback); }
                        $candidates = $group === 'inputs' ? [$english, ...$task['accepted']] : [$english];
                        // Formation cause/instrument has translations interleaved between its two full sentences.
                        $sentences = $task['id'] === 'm52-formation-cause-instrument'
                            ? ['The fence was damaged by the storm.', 'The paper was cut with scissors.'] : null;
                        if ($sentences !== null) { foreach ($sentences as $sentence) { self::assertStringContainsString($sentence, $feedback); } }
                        else { self::assertNotEmpty(array_filter($candidates, static fn ($candidate) => str_contains($feedback, $candidate))); }
                    }
                    if ($group === 'inputs') {
                        self::assertStringContainsString('/', $task['before']); self::assertMatchesRegularExpression('/[\x{0400}-\x{04FF}]/u', $task['after']);
                        self::assertStringNotContainsString($task['answer'], $task['before']); self::assertStringNotContainsString($task['answer'], $task['after']);
                        $tokens = array_map('trim', explode('/', $task['before'])); self::assertGreaterThan(1, count($tokens));
                        self::assertNotSame($task['answer'], implode(' ', $tokens), 'Native token bank must not expose completed order.');
                    }
                }
            }
            $body = json_decode($patch['targets'][$i]['after']['page']['blocks'][[9, 11, 11][$i]]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach (['selects', 'choices', 'inputs'] as $group) { self::assertSame($practice[$group], $body[$group]); }
        }
        self::assertSame(array_keys($expected), $seen);
    }

    public function test_native_tables_show_active_and_passive_translations_and_components_preserve_author_basic_detail_points(): void
    {
        [, $patch, $master] = Package::load();
        $tableExpected = [
            ['Mary bakes a cake.', 'Марія пече торт.', 'A cake is baked by Mary.', 'Торт пече Марія.'],
            ["It hasn't been done.", 'Це не зробили.', 'Has it been done?', 'Це зробили?', "Yes, it has. / No, it hasn't."],
            ['We may postpone the meeting.', 'Можливо, ми відкладемо зустріч.', 'The meeting may be postponed by us.', 'Можливо, зустріч відкладемо ми.'],
        ];
        $detailOwners = [['formation-forms', '/items/1'], ['negative-yes-no', '/sections/0'], ['modal-perfect-meaning', '/sections/3']];
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']); $lesson = $master['lessons'][$i];
            self::assertCount(1, $lesson['details']); self::assertSame($detailOwners[$i][0], $lesson['details'][0]['owner_block_key']);
            self::assertSame($detailOwners[$i][1], $lesson['details'][0]['owner_pointer']);
            foreach ($lesson['blocks'] as $slot => $author) {
                $block = $blocks[$slot + 1]; $snapshot = $block->getAttributes(); $html = $this->render($block); $xp = $this->xpath($html);
                self::assertSame($snapshot, $block->getAttributes());
                foreach (['m52-section', 'm52-detail', 'm52-styles', '<style'] as $forbidden) { self::assertStringNotContainsString($forbidden, $html); }
                if ($author['type'] === 'usage-panels') {
                    $points = $xp->query('//*[@data-theory-point-panel]'); self::assertCount(count($author['data']['sections']), $points);
                    foreach ($author['data']['sections'] as $n => $section) {
                        self::assertSame($section['color'], $points->item($n)->getAttribute('data-theory-accent'));
                        foreach ($section['examples'] ?? [] as $example) {
                            self::assertStringContainsString($this->plain($example['en']), $this->plain($html));
                            self::assertStringContainsString($this->plain($example['ua']), $this->plain($html));
                        }
                    }
                }
                if ($author['type'] === 'comparison-table') {
                    self::assertCount(1, $xp->query('//table')); self::assertCount(count($author['data']['rows']), $xp->query('//tbody/tr'));
                    foreach ($tableExpected[$i] as $text) { self::assertStringContainsString($text, $this->plain($html)); }
                    foreach ($author['data']['rows'] as $row) {
                        foreach ($row['cells'] as $cell) { self::assertStringContainsString($this->plain($cell), $this->plain($html)); }
                    }
                }
                if ($author['type'] === 'mistakes-grid') {
                    foreach ($author['data']['items'] as $item) {
                        foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua'] as $field) { self::assertStringContainsString($this->plain($item[$field]), $this->plain($html)); }
                    }
                }
                if ($author['type'] === 'practice-set') {
                    self::assertCount(3, $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]'));
                    self::assertCount(2, $xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]'));
                    foreach ($author['data']['inputs'] as $input) { self::assertStringContainsString($input['after'], $html); }
                }
            }
            $detail = $lesson['details'][0]; $slot = array_search($detail['owner_block_key'], array_column($lesson['blocks'], 'key'), true);
            $block = $blocks[$slot + 1]; $shown = Package::presentation($block, json_decode($block->body, true, flags: JSON_THROW_ON_ERROR));
            $ownerIndex = (int) explode('/', $detail['owner_pointer'])[2]; self::assertSame([$ownerIndex], array_keys($shown['points']));
            self::assertSame($detail['fragment']['data']['sections'], array_column($shown['points'][$ownerIndex]['fragments'], 'value'));
            $html = $this->render($block); self::assertCount(1, $this->xpath($html)->query('//details[@data-theory-details]'));
            foreach ($detail['fragment']['data']['sections'] as $section) {
                self::assertStringContainsString($this->plain($section['description']), $this->plain($html));
                foreach ($section['examples'] as $example) {
                    self::assertStringContainsString($this->plain($example['en']), $this->plain($html));
                    self::assertStringContainsString($this->plain($example['ua']), $this->plain($html));
                }
            }
        }
    }

    public function test_foreign_bindings_stale_bodies_and_invalid_author_metadata_do_not_acquire_m52_presentation(): void
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
                if (in_array($block->type, ['hero', 'navigation-chips'], true)) {
                    self::assertNull($resolved);
                } else {
                    self::assertSame(Package::presentation($block, $data), $resolved);
                }
            }
        }
        foreach (['bank', 'level', 'locale', 'ancestry', 'title', 'styled', 'view'] as $case) {
            $badMaster = $master; $badPatch = $patch;
            if ($case === 'bank') { $badMaster['lessons'][0]['blocks'][9]['data']['linked_practice']['seeder_classes'] = [self::BANKS[1]]; }
            if ($case === 'level') { $badMaster['lessons'][0]['technical_block_level'] = 'B1'; }
            if ($case === 'locale') { $badMaster['lessons'][0]['locale'] = 'en'; }
            if ($case === 'ancestry') { $badPatch['targets'][0]['ancestry'] = ['modal-verbs']; }
            if ($case === 'title') { $badMaster['lessons'][0]['subtitle_html'] = '<p><strong>Foreign title</strong> Пояснення.</p>'; }
            if ($case === 'styled') { $badMaster['lessons'][0]['blocks'][1]['data']['intro'] = '<p class="foreign">Пояснення.</p>'; }
            if ($case === 'view') { $badMaster['lessons'][0]['blocks'][1]['data']['view'] = 'theory.foreign'; }
            $badPatch['targets'][0]['after'] = Package::definition($badMaster['lessons'][0], $before['targets'][0]['before']);
            $this->refusal(static fn () => Package::validate($before, $badPatch, $badMaster, $contract));
        }
        $bad = $contract; $bad['targets'] = [];
        $this->refusal(static fn () => Package::validate($before, $patch, $master, $bad));
    }

    public function test_course_and_standalone_callers_keep_full_before_and_original_models_without_queries(): void
    {
        [$before, $patch] = Package::load();
        $controller = (new \ReflectionClass(TestJsV2Controller::class))->newInstanceWithoutConstructor();
        $serialize = new \ReflectionMethod(TestJsV2Controller::class, 'resolveTheoryBlocksForQuestion');
        foreach ($patch['targets'] as $i => $target) {
            $old = $this->blocks($target, $before['targets'][$i]['before']); $after = $this->blocks($target, $target['after']);
            foreach (['en', 'pl'] as $locale) {
                foreach ($old as $block) { $other = clone $block; $other->locale = $locale; $other->uuid = $locale.'-'.$other->uuid; $after->push($other); }
            }
            $page = $this->page($target, $after); $snapshot = $after->map(static fn ($b) => $b->getAttributes())->all(); $pageAttributes = $page->getAttributes();
            DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
            foreach (['uk', 'en', 'pl'] as $locale) {
                app()->setLocale($locale); $copy = Package::preserveCoursePage($page); self::assertNotSame($page, $copy);
                self::assertSame($before['targets'][$i]['before']['page']['subtitle_text'], $copy->text);
                self::assertSame($old->map(static fn ($b) => $b->getAttributes())->all(), $copy->textBlocks->where('locale', 'uk')->values()->map(static fn ($b) => $b->getAttributes())->all());
                foreach ($after->reject(static fn ($b) => $b->locale === 'uk') as $other) { self::assertSame($other, $copy->textBlocks->firstWhere('uuid', $other->uuid)); }
            }
            $question = new Question; $question->setRawAttributes(['id' => 7200000 + $i, 'theory_text_block_uuid' => null], true);
            $question->setRelation('theoryTextBlocks', $old); $expected = $serialize->invoke($controller, $question);
            $ukAfter = $after->where('locale', 'uk')->values(); $question->setRelation('theoryTextBlocks', $ukAfter);
            foreach (['uk', 'en', 'pl'] as $locale) { app()->setLocale($locale); self::assertSame($expected, $serialize->invoke($controller, $question)); }
            foreach ($ukAfter as $position => $block) {
                if ($position === [10, 12, 12][$i]) { self::assertNull(Package::preserveExcludedTextBlock($block, 'standalone_test')); }
                else { self::assertSame($old[$position]->body, Package::preserveExcludedTextBlock($block, 'standalone_test')->body); }
                self::assertSame($block, Package::preserveExcludedTextBlock($block, 'theory'));
            }
            self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
            self::assertSame($snapshot, $after->map(static fn ($b) => $b->getAttributes())->all()); self::assertSame($pageAttributes, $page->getAttributes());
            $foreign = clone $page; $foreign->text .= ' changed'; self::assertSame($foreign, Package::preserveCoursePage($foreign));
        }
    }

    /** Actual old source questions, independently captured UUID/ID order, in private SQLite only. */
    private function nativeBankFixture(): array
    {
        $this->rebuildComposeTestSchema();
        [, $patch, , $contract] = Package::load();
        $ledger = json_decode(File::get(base_path('docs/content/m52-passive-basics-bank-audit.v1.json')), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(0, $ledger['decision']['new_questions']); self::assertSame(0, $ledger['decision']['new_links']);
        $result = [];
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']); $page = $this->page($target, $blocks);
            DB::table('pages')->insert($page->getAttributes());
            foreach ($blocks as $block) { DB::table('text_blocks')->insert($block->getAttributes()); }
            $bank = json_decode(File::get(base_path($ledger['targets'][$i]['bank_definition'])), true, flags: JSON_THROW_ON_ERROR);
            self::assertSame($ledger['targets'][$i]['bank_definition_sha256'], hash_file('sha256', base_path($ledger['targets'][$i]['bank_definition'])));
            $sourceByUuid = array_column($bank['questions'], null, 'uuid');
            foreach ($ledger['targets'][$i]['first_five'] as $sample) {
                $source = $sourceByUuid[$sample['uuid']]; self::assertSame($sample['condition_uk'], $source['question']);
                DB::table('questions')->insert(['id' => $sample['id'], 'uuid' => $sample['uuid'], 'question' => $source['question'],
                    'seeder' => self::BANKS[$i], 'level' => ['A2', 'B1', 'B1'][$i], 'type' => '4']);
                $answerOptionIds = [];
                foreach ($source['answers'] as $marker => $token) {
                    $stored = in_array($token, ['Friday', 'Sundays'], true) ? $token : mb_strtolower($token);
                    $id = DB::table('question_options')->whereRaw('lower(option) = ?', [mb_strtolower($stored)])->value('id')
                        ?? DB::table('question_options')->insertGetId(['option' => $stored]);
                    DB::table('question_answers')->insert(['question_id' => $sample['id'], 'option_id' => $id, 'marker' => $marker]);
                    $answerOptionIds[] = $id;
                }
                foreach ($source['options'] as $token) {
                    $stored = in_array($token, ['Friday', 'Sundays'], true) ? $token : mb_strtolower($token);
                    $id = DB::table('question_options')->whereRaw('lower(option) = ?', [mb_strtolower($stored)])->value('id')
                        ?? DB::table('question_options')->insertGetId(['option' => $stored]);
                    $answerOptionIds[] = $id;
                }
                foreach (array_unique($answerOptionIds) as $id) { DB::table('question_option_question')->insert(['question_id' => $sample['id'], 'option_id' => $id]); }
                foreach ($sample['UK_block_uuids'] as $position => $uuid) {
                    self::assertContains($uuid, $blocks->pluck('uuid')->all());
                    DB::table('question_theory_text_blocks')->insert(['question_uuid' => $sample['uuid'], 'text_block_uuid' => $uuid, 'position' => $position]);
                }
            }
            $practice = $blocks->firstWhere('type', 'practice-set'); $config = json_decode($practice->body, true)['linked_practice'];
            $selected = app(TextBlockToQuestionsMatcherService::class)->findQuestionsForTextBlocks(new ModelCollection([$practice]), 5)[$practice->uuid];
            $html = $this->render($practice, $selected);
            $result[] = [$contract['targets'][$i], $page, $practice, $config, $selected, $html, $ledger['targets'][$i]];
        }

        return $result;
    }

    public function test_existing_fifteen_type4_questions_match_native_payload_case_duplicate_instances_and_one_widget_without_new_banks(): void
    {
        foreach ($this->nativeBankFixture() as [$contract, $page, $block, $config, $selected, $html, $ledger]) {
            $result = Acceptance::check($contract, $page, $block, $config, $selected, $selected, $html);
            self::assertSame([], $result['errors']); self::assertTrue($result['ok']); self::assertSame(1, $result['widget_count']);
            self::assertSame($ledger['expected_selected_ids'], $selected->pluck('id')->all());
            self::assertSame($ledger['expected_selected_uuids'], $selected->pluck('uuid')->all());
            self::assertCount(5, $result['payload']); self::assertSame('not_checked', $result['browser_visibility']);
            self::assertCount(2, $this->xpath($html)->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]'));
            foreach ($result['payload'] as $i => $question) {
                self::assertSame($ledger['first_five'][$i]['condition_uk'], $question['question']);
                self::assertSame($ledger['first_five'][$i]['correct_tokens'], $question['correct_tokens']);
                self::assertStringContainsString($this->plain($config['intro']), $this->plain($html));
                if ($question['id'] === 37313) {
                    self::assertSame(2, count(array_filter($question['correct_tokens'], static fn ($token) => mb_strtolower($token) === 'the')));
                    self::assertSame(2, count(array_filter($question['options'], static fn ($token) => mb_strtolower($token) === 'the')));
                }
            }
        }
        self::assertSame(15, DB::table('questions')->count());
    }

    public function test_acceptance_refuses_missing_short_foreign_type_level_owner_locale_duplicates_and_extra_widget(): void
    {
        foreach ($this->nativeBankFixture() as [$contract, $page, $block, $config, $selected, $html]) {
            $check = fn ($configuration, $eligible, $chosen, $rendered = null, $p = null, $b = null) => Acceptance::check(
                $contract, $p ?? $page, $b ?? $block, $configuration, $eligible, $chosen, $rendered ?? $html);
            self::assertContains('missing_linked_practice', $check(null, $selected, $selected)['errors']);
            self::assertContains('fewer_than_five_eligible', $check($config, collect(), collect(), $this->render($block, collect()))['errors']);
            self::assertContains('wrong_selected_count', $check($config, $selected, $selected->take(4))['errors']);
            self::assertContains('fewer_than_five_eligible', $check($config, $selected->take(4), $selected->take(4))['errors']);
            foreach (['type' => ['0', 'wrong_question_type'], 'level' => ['C2', 'wrong_question_level'],
                'seeder' => ['Database\\Seeders\\V3\\Polyglot\\ForeignLessonSeeder', 'foreign_question_bank']] as $field => [$value, $error]) {
                $bad = $selected->map(static fn ($q) => clone $q); $bad->first()->$field = $value;
                self::assertContains($error, $check($config, $selected, $bad, $this->render($block, $bad))['errors']);
            }
            $badConfig = $config; $badConfig['seeder_classes'] = ['Database\\Seeders\\V3\\Polyglot\\ForeignLessonSeeder'];
            self::assertContains('wrong_config_bank', $check($badConfig, $selected, $selected)['errors']);
            $badConfig = $config; $badConfig['question_types'] = ['0']; self::assertContains('wrong_config_type', $check($badConfig, $selected, $selected)['errors']);
            $badBlock = clone $block; $badBlock->locale = 'en'; self::assertContains('wrong_locale', $check($config, $selected, $selected, b: $badBlock)['errors']);
            $badPage = clone $page; $badPage->seeder = 'ForeignOwner'; self::assertContains('wrong_owner', $check($config, $selected, $selected, p: $badPage)['errors']);
            $duplicates = collect([$selected[0], $selected[0], $selected[1], $selected[2], $selected[3]]);
            self::assertContains('duplicate_question_id', $check($config, $selected, $duplicates, $this->render($block, $duplicates))['errors']);
            self::assertContains('duplicate_widget', $check($config, $selected, $selected, $html.$html)['errors']);
            self::assertContains('missing_widget', $check($config, $selected, $selected, $this->render($block, collect()))['errors']);
        }
    }

    public function test_m52_discovery_fails_loudly_without_independent_contract_instead_of_silently_losing_three_new_targets(): void
    {
        [, $patch] = Package::load(); IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $root = storage_path('app/m52-discovery-'.bin2hex(random_bytes(8))); File::makeDirectory($root, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($root);
        foreach ($patch['targets'] as $target) {
            File::makeDirectory(dirname($root.'/'.$target['path']), 0700, true, true);
            File::put($root.'/'.$target['path'], M26DetailPackage::json($target['after']));
        }
        File::makeDirectory($root.'/database/content-patches', 0700, true, true);
        File::put($root.'/database/content-patches/m52-passive-basics.v1.json', M26DetailPackage::json($patch));
        $targets = Acceptance::discoverTargets($root); self::assertCount(3, $targets);
        self::assertSame(Package::OWNERS, array_column($targets, 'identity'));
        foreach ($targets as $target) { self::assertContains('missing_independent_bank_contract', $target['errors']); }
        File::makeDirectory($root.'/'.Acceptance::CONTRACT_DIRECTORY, 0700, true, true);
        File::put($root.'/'.Package::CONTRACT, File::get(base_path(Package::CONTRACT)));
        foreach (Acceptance::discoverTargets($root) as $target) { self::assertSame([], $target['errors']); }
    }
}
