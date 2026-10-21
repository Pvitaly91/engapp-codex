<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M72PartitivesNegativesGeography;
use App\Support\M73GenericIrregularCollective;
use App\Support\M74OtherPronounsBasics as Package;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Database\Eloquent\Collection;
use Normalizer;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

/** Frozen content and isolated model guards; no working DB, HTTP, engine edits or progress. */
class M74OtherPronounsBasicsPackageTest extends TestCase
{
    private const PAGE_CONTROLLER_SHA = '491672fed84cc11a690f24d9111999049e258ad5a06f881149495a102b8d1659';

    private function refuses(callable $call, string $case): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    /** Accepted factory alphabet mirrored for validation, never a new grader. */
    private static function normal(string $value): string
    {
        $value = Normalizer::normalize($value, Normalizer::FORM_KD);
        self::assertIsString($value);
        $value = preg_replace('/[\x{0300}-\x{036f}]/u', '', mb_strtolower($value));
        $value = preg_replace('/[\x{2018}\x{2019}\x{201b}\x{2032}\x{0060}´ʼ\x{0027}]/u', '', $value);
        return trim(preg_replace('/\s+/u', ' ', preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value)));
    }

    private function block(array $target, array $source, int $slot): TextBlock
    {
        $block = new TextBlock;
        $block->setRawAttributes(['id' => 7400000 + $target['page_binding']['id'] * 100 + $slot,
            'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'seeder' => $target['identity'], 'locale' => 'uk', 'page_id' => $target['page_binding']['id'],
            'page_category_id' => $target['page_binding']['page_category_id'], 'sort_order' => $slot + 1,
            'type' => $source['type'], 'column' => $source['column'], 'heading' => $source['heading'] ?? null,
            'css_class' => $source['css_class'] ?? null, 'level' => $source['level'] ?? null, 'body' => $source['body']], true);
        $block->setRelation('tags', new Collection); $block->setRelation('page', null);
        return $block;
    }

    public function test_frozen_release_preserves_twenty_eight_slots_and_two_existing_practice_uuids(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        Package::validate($before, $patch, $master, $contract);
        self::assertSame('fd1ad30e710b3d950e574848c886d86f6045f2e3', Package::BASE_SHA);
        self::assertSame([81, 126, 124], Package::PAGE_IDS);
        self::assertSame([16, 20, 20], Package::CATEGORY_IDS);
        self::assertSame([6, 12, 10], Package::CONTINUING_COUNTS);
        self::assertSame([6, 10, 8], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame(['B1', null, null], array_column($contract['targets'], 'level'));
        $slots = $own = $retained = $demos = 0; $newIds = [];
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $lesson = $master['lessons'][$i];
            self::assertCount(Package::CONTINUING_COUNTS[$i] + ($i === 0 ? 1 : 0), $lesson['blocks']);
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected);
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$slots; $new = $target['after']['page']['blocks'][$slot];
                self::assertSame($slot, $lesson['blocks'][$slot]['existing_source_index']);
                self::assertSame(array_key_exists('level', $source), array_key_exists('level', $new));
                self::assertSame($source['level'] ?? null, $new['level'] ?? null);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1), M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
                unset($source['body'], $new['body']); self::assertSame($source, $new);
            }
            foreach ($target['after']['page']['blocks'] as $source) { self::assertLessThanOrEqual(65535, strlen($source['body'])); }
            $footer = Package::CONTINUING_COUNTS[$i] - 1;
            self::assertSame('navigation-chips', $lesson['blocks'][$footer]['type']);
            self::assertSame('footer', $lesson['blocks'][$footer]['column']);
            $oldNav = json_decode($old['page']['blocks'][$footer]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
            $newNav = $lesson['blocks'][$footer]['data']['items']; self::assertCount([8, 9, 9][$i], $newNav);
            $labels = static fn ($items) => array_map(static fn ($item) => array_intersect_key($item, array_flip(['label', 'current'])), $items);
            if ($i === 0) { self::assertSame($oldNav, $newNav); } else { self::assertSame($labels($oldNav), $labels($newNav)); }
            $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $practice = $target['after']['page']['blocks'][$slot];
            self::assertSame('practice-set', $practice['type']); self::assertSame('left', $practice['column']);
            self::assertSame(['B1', null, null][$i], $practice['level'] ?? null);
            self::assertCount(1, array_filter($lesson['blocks'], static fn ($b) => $b['type'] === 'practice-set'));
            if ($i === 0) {
                self::assertSame('m74:another-other-the-other:another-practice', $practice['uuid_key']);
                self::assertSame('right', $lesson['blocks'][3]['column']); self::assertSame('right', $lesson['blocks'][4]['column']);
            } else {
                self::assertSame(['', 'b00124f3-ec4d-54fb-91b8-b6e5becaf4b9', '1613f5c2-8294-57fd-bbd8-ea49c51a4df4'][$i],
                    M26DetailPackage::uuid($target['identity'], $practice, $slot + 1));
            }
            $data = $lesson['blocks'][$slot]['data'];
            foreach (['selects' => [2, 4, 5], 'choices' => [2, 2, 2], 'inputs' => [2, 5, 6], 'rephrase' => [0, 3, 4]] as $group => $counts) {
                self::assertCount($counts[$i], $data[$group] ?? []);
            }
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) { foreach ($data[$group] ?? [] as $n => $task) {
                if ($group === 'rephrase' && $n === 0) {
                    ++$demos; self::assertNotEmpty($task['example_original']); self::assertNotEmpty($task['example_target']);
                    self::assertArrayNotHasKey('answer', $task); self::assertArrayNotHasKey('accepted', $task); continue;
                }
                ++$own; self::assertNotSame('', self::normal($task['answer'])); self::assertContains($task['answer'], $task['accepted']);
                foreach ($task['accepted'] as $alias) {
                    self::assertNotSame('', self::normal($alias));
                    self::assertArrayHasKey(mb_strtolower(trim($alias)), $task['feedback'], 'Actual literal-lower feedback lookup exists.');
                }
                foreach ($task['feedback'] as $key => $text) {
                    self::assertSame(mb_strtolower(trim($key)), $key);
                    self::assertDoesNotMatchRegularExpression('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text);
                }
                $oldCounts = $i === 1 ? ['selects' => 4, 'inputs' => 3, 'rephrase' => 3] : ($i === 2 ? ['selects' => 5, 'inputs' => 4, 'rephrase' => 4] : []);
                if ($n < ($oldCounts[$group] ?? 0)) {
                    ++$retained; self::assertArrayNotHasKey('id', $task); self::assertArrayNotHasKey('source_index', $task);
                } else { self::assertMatchesRegularExpression('/^m74-[a-z0-9-]+$/D', $task['id']); $newIds[] = $task['id']; }
                self::assertArrayNotHasKey('punctuation_sensitive', $task);
            }}
            foreach ([[0, 1], [3, 4], [4, 5]][$i] as $n) {
                $input = $data['inputs'][$n]; self::assertStringContainsString('/', $input['before']);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $input['after']);
                self::assertStringNotContainsString($input['answer'], $input['after']);
                self::assertGreaterThanOrEqual(4, count(preg_split('/\s+/u', trim($input['answer']))));
                $bank = array_map(trim(...), explode('/', $input['before'])); $words = preg_split('/\s+/u', trim($input['answer']));
                sort($bank, SORT_STRING); sort($words, SORT_STRING); self::assertSame($words, $bank);
            }
        }
        self::assertSame(28, $slots); self::assertSame(35, $own); self::assertSame(21, $retained); self::assertSame(2, $demos);
        self::assertCount(14, $newIds); self::assertCount(14, array_unique($newIds));
        foreach (Package::profile()['artifacts'] as $relative => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha); self::assertSame($sha, hash_file('sha256', base_path($relative)));
        }
    }

    public function test_actual_options_and_semantic_apostrophe_transfer_have_distinct_deliberate_keys(): void
    {
        [$before, , $master] = Package::load(); $options = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $data = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
            foreach (['selects', 'choices'] as $group) { foreach ($data[$group] as $task) {
                self::assertContains($task['answer'], $task['options']);
                $keys = array_map(self::normal(...), $task['options']);
                self::assertGreaterThanOrEqual(2, count($keys)); self::assertCount(count($keys), array_unique($keys)); self::assertNotContains('', $keys);
                foreach ($task['options'] as $option) {
                    ++$options; self::assertArrayHasKey(mb_strtolower(trim($option)), $task['feedback']);
                    if (!in_array($option, $task['accepted'], true)) { self::assertNotContains(self::normal($option), array_map(self::normal(...), $task['accepted'])); }
                }
            }}
        }
        self::assertSame(36, $options);
        $old = json_decode($before['targets'][1]['before']['page']['blocks'][10]['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertStringContainsString('Its a beautiful day.', $old['rephrase'][1]['original']);
        self::assertSame(self::normal('Its a beautiful day.'), self::normal("It's a beautiful day."));
        self::assertSame(self::normal('I'), self::normal('i')); self::assertSame(self::normal('This'), self::normal('this'));
        $data = $master['lessons'][1]['blocks'][10]['data']; $task = $data['rephrase'][1];
        self::assertSame('речення Б', $task['answer']); self::assertSame(['речення Б', 'Б'], $task['accepted']);
        self::assertStringContainsString('Its a beautiful day.', $task['original']); self::assertStringContainsString("It's a beautiful day.", $task['original']);
        self::assertNotSame(self::normal('речення А'), self::normal('речення Б'));
        foreach (['речення А', 'А', 'Its a beautiful day.', "It's a beautiful day."] as $wrong) {
            self::assertNotContains(self::normal($wrong), array_map(self::normal(...), $task['accepted']));
        }
        self::assertCount(3, $data['rephrase']); self::assertSame('This book is mine.', $data['rephrase'][2]['answer']);
        self::assertSame(['I', 'mine', 'herself', 'This'], array_column($data['selects'], 'answer'));
        self::assertSame(['I', 'me', 'my'], array_column(array_slice($data['inputs'], 0, 3), 'answer'));
        self::assertArrayNotHasKey('answer', $data['rephrase'][0]);
    }

    public function test_negative_guards_reject_foreign_scope_lost_positions_unlisted_keys_and_normalized_collisions(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        $mutations = [
            'foreign-owner' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['identity'] = Package::OWNERS[0]; },
            'foreign-locale' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['locale'] = 'en'; },
            'pronoun-to-noun-category' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][1]['page_binding']['page_category_id'] = 16; },
            'noun-to-pronoun-category' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][0]['page_binding']['page_category_id'] = 20; },
            'foreign-page' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][2]['page_binding']['id'] = 126; },
            'NULL-as-A2' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][10]['level'] = 'A2'; },
            'display-range-as-filter' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][10]['level'] = 'A1–B1'; },
            'right-column' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][3]['column'] = 'left'; },
            'retained-ID' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][10]['data']['inputs'][0]['id'] = 'replacement'; },
            'retained-source-index' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][8]['data']['selects'][0]['source_index'] = 1; },
            'lost-old-select' => static function (&$b, &$p, &$m, &$c): void { array_shift($m['lessons'][1]['blocks'][10]['data']['selects']); },
            'lost-old-rephrase' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][2]['blocks'][8]['data']['rephrase']); },
            'lost-demo' => static function (&$b, &$p, &$m, &$c): void { unset($m['lessons'][2]['blocks'][8]['data']['rephrase'][0]['example_original']); },
            'graded-demo' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][10]['data']['rephrase'][0]['answer'] = 'A'; },
            'lost-full-input' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][2]['blocks'][8]['data']['inputs']); },
            'duplicate-practice' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][] = $m['lessons'][1]['blocks'][10]; },
            'empty-key' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][1]['blocks'][10]['data']['inputs'][0]; $t['answer'] = ''; $t['accepted'] = ['']; },
            'punctuation-only-accepted' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][8]['data']['inputs'][0]['accepted'][] = '...'; },
            'unlisted-key' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][0]['blocks'][6]['data']['selects'][0]; $t['answer'] = 'unlisted'; $t['accepted'] = ['unlisted']; $t['feedback']['unlisted'] = 'Непоказаний ключ.'; },
            'case-option-collision' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][8]['data']['selects'][0]['options'][] = 'i'; },
            'punctuation-option-collision' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][0]['blocks'][6]['data']['selects'][0]; $o = $t['options'][0].'!'; $t['options'][] = $o; $t['feedback'][mb_strtolower($o)] = 'Повтор після нормалізації.'; },
            'ineffective-English-transfer' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][1]['blocks'][10]['data']['rephrase'][1]; $t['answer'] = "It's a beautiful day."; $t['accepted'] = ["It's a beautiful day."]; },
            'html-feedback' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][10]['data']['rephrase'][1]['feedback']['bad'] = '<b>Розбір</b>'; },
            'foreign-view' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][2]['data']['native_view'] = 'm74-section'; },
            'missing-bank' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][1]['bank_seeders'] = []; },
            'footer-label' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][9]['data']['items'][0]['label'] = 'Інший урок'; },
            'footer-hash-link' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][11]['data']['items'][1]['url'] = '#'; },
            'stale-body' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][2]['after']['page']['blocks'][0]['body'] .= "\n"; },
        ];
        foreach ($mutations as $case => $mutate) {
            $b = $before; $p = $patch; $m = $master; $c = $contract; $mutate($b, $p, $m, $c);
            $this->refuses(static function () use ($case, $b, $p, $m, $c): void {
                if ($case !== 'stale-body') { foreach ($m['lessons'] as $i => $lesson) { $p['targets'][$i]['after'] = Package::definition($lesson, $b['targets'][$i]['before']); } }
                Package::validate($b, $p, $m, $c);
            }, $case);
        }
        foreach ([1, 2] as $i) {
            $profile = Package::profile(); $profile['bindings'][$i]['existing_practice']['level'] = $i === 1 ? 'A2' : 'A1';
            $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->existingPractice($profile['bindings'][$i], $before['targets'][$i]['before']), 'NULL is not the curriculum target.');
        }
    }

    public function test_plain_personal_subtitle_is_exact_not_a_generic_plain_title_bypass(): void
    {
        [$before, $patch, $master, $contract] = Package::load(); $binding = Package::profile()['bindings'][2];
        self::assertSame('captured-plain', $binding['before_subtitle']['mode']);
        self::assertSame(hash('sha256', $before['targets'][2]['before']['page']['subtitle_html']), $binding['before_subtitle']['sha256']);
        self::assertSame(hash('sha256', $master['lessons'][2]['subtitle_html']), $binding['preserved_plain_subtitle']['sha256']);
        self::assertMatchesRegularExpression('/^<p>[^<>]+<\/p>$/suD', $master['lessons'][2]['subtitle_html']);
        foreach (['before-hash', 'after-hash', 'foreign-h1', 'missing-opt-in', 'foreign-owner-opt-in', 'stale-after', 'foreign-metadata'] as $case) {
            $profile = Package::profile(); $m = $master; $p = $patch;
            if ($case === 'before-hash') { $profile['bindings'][2]['before_subtitle']['sha256'] = str_repeat('0', 64); }
            if ($case === 'after-hash') { $profile['bindings'][2]['preserved_plain_subtitle']['sha256'] = str_repeat('0', 64); }
            if ($case === 'foreign-h1') { $profile['bindings'][2]['preserved_plain_subtitle']['h1'] = 'Foreign'; }
            if ($case === 'missing-opt-in') { unset($profile['bindings'][2]['preserved_plain_subtitle']); }
            if ($case === 'foreign-owner-opt-in') { $profile['bindings'][0]['preserved_plain_subtitle'] = $profile['bindings'][2]['preserved_plain_subtitle']; }
            if ($case === 'stale-after') {
                $m['lessons'][2]['subtitle_html'] = str_replace('</p>', ' </p>', $m['lessons'][2]['subtitle_html']);
                $p['targets'][2]['after'] = Package::definition($m['lessons'][2], $before['targets'][2]['before']);
            }
            if ($case === 'foreign-metadata') {
                foreach ($m['editorial_review']['post_apply_metadata'] as &$record) { if (($record['identity'] ?? null) === Package::OWNERS[2]) { $record['identity'] = Package::OWNERS[0]; } }
                unset($record);
            }
            $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->validate($before, $p, $m, $contract), $case);
        }
    }

    public function test_exact_models_present_without_mutation_and_foreign_metadata_falls_back(): void
    {
        app()->setLocale('uk'); [, $patch] = Package::load(); $count = 0;
        foreach ($patch['targets'] as $i => $target) { foreach ($target['after']['page']['blocks'] as $slot => $source) {
            ++$count; $block = $this->block($target, $source, $slot); $raw = $block->getAttributes();
            $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR); $unchanged = $data;
            $presentation = Package::presentation($block, $data); self::assertNotNull($presentation);
            self::assertTrue(Package::validDesign($presentation['m42_native_design']));
            self::assertSame($source['type'], $presentation['m42_native_design']['component']);
            self::assertSame($unchanged, $data); self::assertSame($raw, $block->getAttributes()); self::assertSame([], $block->getDirty());
            if ($slot !== Package::PRACTICE_SOURCE_INDICES[$i]) { continue; }
            foreach (['seeder' => 'Foreign\\Owner', 'locale' => 'pl', 'page_id' => 999, 'page_category_id' => $i === 0 ? 20 : 16,
                'uuid' => 'foreign', 'sort_order' => 99, 'column' => 'right', 'heading' => 'Later heading',
                'css_class' => 'later-style', 'level' => $i === 0 ? 'B1–B2' : 'A2', 'body' => $source['body']."\n"] as $field => $value) {
                $foreign = clone $block; $foreign->$field = $value; $attributes = $foreign->getAttributes(); $dirty = $foreign->getDirty();
                self::assertNull(Package::presentation($foreign, $data), $field);
                self::assertSame($attributes, $foreign->getAttributes()); self::assertSame($dirty, $foreign->getDirty());
            }
            foreach (['en', 'pl'] as $locale) { app()->setLocale($locale); self::assertNull(Package::presentation($block, $data)); }
            app()->setLocale('uk'); $changed = $data; $changed['unreviewed'] = 'Later'; self::assertNull(Package::presentation($block, $changed));
            foreach (['identity' => 'Foreign', 'locale' => 'en', 'uuid' => 'foreign', 'source_index' => 999,
                'body_sha256' => str_repeat('0', 64), 'component' => 'm74-custom-section'] as $field => $value) {
                $bad = $presentation['m42_native_design']; $bad[$field] = $value; self::assertFalse(Package::validDesign($bad), $field);
            }
        }}
        self::assertSame(29, $count);
    }

    public function test_unchanged_title_extractor_and_frozen_loader_preserve_first_strong_and_plain_h1(): void
    {
        [$before, $patch] = Package::load();
        self::assertSame(self::PAGE_CONTROLLER_SHA, hash_file('sha256', base_path('app/Http/Controllers/PageController.php')));
        $controller = (new ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $extract = new ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; self::assertSame($old['page']['title'], $target['after']['page']['title']);
            foreach ([$old['page']['subtitle_html'], $target['after']['page']['subtitle_html']] as $subtitle) {
                self::assertSame(Package::RENDERED_TITLES[$i], $extract->invoke($controller, $subtitle));
            }
        }
        self::assertSame('Pronouns — Огляд', $before['targets'][1]['before']['page']['title']);
        self::assertStringContainsString('<strong>Pronouns</strong>', $patch['targets'][1]['after']['page']['subtitle_html']);
        self::assertStringNotContainsString('<strong>', $before['targets'][2]['before']['page']['subtitle_html']);
        self::assertStringNotContainsString('<strong>', $patch['targets'][2]['after']['page']['subtitle_html']);
        $profile = Package::profile();
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->load(base_path('storage/app/seo-m74-local/nonexistent-root')), 'Missing frozen source');
        $first = array_key_first($profile['artifacts']); $profile['artifacts'][$first] = str_repeat('0', 64);
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->load(), 'Stale frozen pin');
        foreach ([M72PartitivesNegativesGeography::profile(), M73GenericIrregularCollective::profile()] as $prior) {
            foreach ($prior['artifacts'] as $relative => $sha) { self::assertSame($sha, hash_file('sha256', base_path($relative))); }
        }
    }
}
