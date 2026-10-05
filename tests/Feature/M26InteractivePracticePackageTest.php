<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\TextBlock;
use App\Services\Theory\TextBlockToQuestionsMatcherService;
use App\Support\M26DetailPackage;
use App\Support\M26InteractivePractice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M26InteractivePracticePackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema();
        app()->setLocale('uk'); $this->withoutVite();
    }

    private function words(string $value): array
    {
        return preg_split('/\s+/u', trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower($value))), flags: PREG_SPLIT_NO_EMPTY);
    }

    public function test_all_twenty_four_cases_have_valid_options_and_shuffled_one_to_three_word_groups(): void
    {
        $payload = M26InteractivePractice::load(); [$master] = M26DetailPackage::load();
        $count = 0;
        foreach ($payload['targets'] as $i => $target) {
            $data = $target['practice']['body_data'];
            self::assertSame(['a', 'b'], $data['choice_options']);
            $mapped = [];
            foreach (['selects', 'choices', 'inputs'] as $group) {
                self::assertCount(2, $data[$group]);
                self::assertCount(2, $target['authored_case_mapping'][$group]);
                array_push($mapped, ...$target['authored_case_mapping'][$group]);
                foreach ($data[$group] as $item) {
                    self::assertNotSame('', trim($item['answer']));
                    self::assertDoesNotMatchRegularExpression('/\{[a-z]\d+\}|<\/?[a-z][^>]*>/i', json_encode($item, JSON_UNESCAPED_UNICODE));
                    $count++;
                }
            }
            self::assertCount(6, array_unique($mapped));
            preg_match_all('/<li id="([^"]+)"/', $master['targets'][$i + 1]['practice_insert']['body_html'], $matches);
            $expectedIds = $matches[1]; sort($expectedIds); sort($mapped);
            self::assertSame($expectedIds, $mapped, 'All six original cases map exactly once.');
            foreach ($data['selects'] as $item) { self::assertContains($item['answer'], $data['options']); }
            foreach ($data['choices'] as $item) {
                self::assertContains($item['answer'], $data['choice_options']);
                self::assertStringContainsString('a)', $item['label']);
                self::assertStringContainsString('b)', $item['label']);
            }
            foreach ($data['inputs'] as $item) {
                $tokens = array_map('trim', explode('/', $item['before']));
                self::assertGreaterThan(1, count($tokens));
                foreach ($tokens as $token) {
                    self::assertGreaterThanOrEqual(1, count($this->words($token)));
                    self::assertLessThanOrEqual(3, count($this->words($token)), $token);
                }
                $bankWords = $this->words(implode(' ', $tokens)); $answerWords = $this->words($item['answer']);
                self::assertNotSame($answerWords, $bankWords, 'Token bank must not already be the answer.');
                $bankBag = array_count_values($bankWords); $answerBag = array_count_values($answerWords);
                ksort($bankBag); ksort($answerBag);
                self::assertSame($answerBag, $bankBag, 'No missing or duplicate sentence words.');
                if (isset($item['accepted'])) { self::assertContains($item['answer'], $item['accepted']); }
            }
        }
        self::assertSame(24, $count);
    }

    public function test_four_definitions_only_replace_final_practice_preserving_all_basic_detail_and_identity_fields(): void
    {
        [$master, $manifest] = M26DetailPackage::load();
        foreach ($master['targets'] as $target) {
            $before = $manifest['definitions'][$target['identity']];
            $legacy = M26DetailPackage::definition($target, $before);
            $latest = M26InteractivePractice::definition($target, $before);
            $current = \App\Support\PastPerfectContinuousPracticeQuality::definition($target, $before);
            self::assertSame($current, json_decode(file_get_contents(base_path($target['definition_path'])), true, flags: JSON_THROW_ON_ERROR));
            if (!isset($target['practice_insert'])) { self::assertSame($legacy, $latest); continue; }
            $root = $target['source_content_root']; $i = count($legacy[$root]['blocks']) - 1;
            $current[$root]['blocks'][$i]['body'] = $latest[$root]['blocks'][$i]['body'];
            self::assertSame($latest, $current, 'Only the four finite practice bodies have a next version.');
            $oldPractice = $legacy[$root]['blocks'][$i]; $newPractice = $latest[$root]['blocks'][$i];
            $latest[$root]['blocks'][$i] = $oldPractice;
            self::assertSame($legacy, $latest, 'No body, owner or position except final practice may change.');
            $newPractice['type'] = $oldPractice['type']; $newPractice['heading'] = $oldPractice['heading']; $newPractice['body'] = $oldPractice['body'];
            self::assertSame($oldPractice, $newPractice, 'UUID key, tags, column, level and ordering are unchanged.');
        }
    }

    public function test_native_practice_renders_all_three_check_reset_groups_and_token_buttons_without_raw_markers(): void
    {
        foreach (M26InteractivePractice::load()['targets'] as $i => $target) {
            $block = new TextBlock;
            $block->forceFill(['id' => 100 + $i, 'uuid' => (string) Str::uuid(), 'type' => 'practice-set',
                'body' => M26DetailPackage::json($target['practice']['body_data']), 'level' => $target['practice']['level']]);
            $block->setRelation('tags', collect());
            $html = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
            foreach (['selects', 'choices', 'inputs'] as $group) {
                self::assertSame(1, substr_count($html, '@click="check(\''.$group.'\')"'));
                self::assertSame(1, substr_count($html, '@click="resetGroup(\''.$group.'\')"'));
            }
            self::assertSame(2, substr_count($html, 'x-for="token in inputTokenBank('));
            self::assertSame(2, substr_count($html, 'autocomplete="off"'));
            self::assertStringNotContainsString('@input.debounce.150ms="searchWordSuggestions(\'inputs\'', $html);
            self::assertStringNotContainsString('data-theory-render-fallback', $html);
            self::assertStringNotContainsString('practice-list', $html);
            self::assertDoesNotMatchRegularExpression('/\{a\d+\}/', $html);
            self::assertStringContainsString('Перевірити', $html);
            self::assertStringContainsString('Обери правильне твердження', $html);
        }
    }

    public function test_each_linked_practice_uses_own_compose_pool_range_labels_do_not_filter_but_exact_cefr_does(): void
    {
        $category = PageCategory::create(['title' => 'Past Perfect Continuous', 'slug' => 'past-perfect-continuous', 'language' => 'uk']);
        $questionCategory = Category::create(['name' => 'Compose fixture']);
        $matcher = app(TextBlockToQuestionsMatcherService::class);
        foreach (M26InteractivePractice::load()['targets'] as $target) {
            $page = Page::create(['title' => $target['key'], 'slug' => $target['slug'], 'page_category_id' => $category->id]);
            $theory = TextBlock::create(['uuid' => (string) Str::uuid(), 'page_id' => $page->id,
                'page_category_id' => $category->id, 'body' => '{}', 'sort_order' => 1]);
            $practice = TextBlock::create(['uuid' => (string) Str::uuid(), 'page_id' => $page->id,
                'page_category_id' => $category->id, 'type' => 'practice-set', 'level' => $target['practice']['level'],
                'body' => M26DetailPackage::json($target['practice']['body_data']), 'sort_order' => 7]);
            $otherPage = Page::create(['title' => 'Other', 'slug' => 'other-'.$target['key'], 'page_category_id' => $category->id]);
            $otherTheory = TextBlock::create(['uuid' => (string) Str::uuid(), 'page_id' => $otherPage->id,
                'page_category_id' => $category->id, 'body' => '{}', 'sort_order' => 1]);
            $seeder = $target['practice']['body_data']['linked_practice']['seeder_classes'][0];
            $expected = [];
            foreach (['A1', 'B1', 'C2', 'wrong-type', 'wrong-seeder', 'wrong-page'] as $kind) {
                $question = Question::create(['uuid' => (string) Str::uuid(), 'question' => 'Question '.$kind,
                    'type' => $kind === 'wrong-type' ? '0' : Question::TYPE_COMPOSE_TOKENS,
                    'seeder' => $kind === 'wrong-seeder' ? 'SiblingSeeder' : $seeder,
                    'category_id' => $questionCategory->id, 'level' => in_array($kind, ['A1', 'B1', 'C2'], true) ? $kind : 'B1']);
                DB::table('question_theory_text_blocks')->insert(['question_uuid' => $question->uuid,
                    'text_block_uuid' => $kind === 'wrong-page' ? $otherTheory->uuid : $theory->uuid, 'position' => 1]);
                if (in_array($kind, ['A1', 'B1', 'C2'], true)) { $expected[$kind] = $question->id; }
            }
            $actual = $matcher->findBestQuestionsForTextBlock($practice, 10)->pluck('id')->all();
            $wanted = array_values($expected); sort($actual); sort($wanted);
            self::assertSame($wanted, $actual, 'Range labels must not hide the exact linked bank.');
            $practice->level = 'B1';
            self::assertSame([$expected['B1']], $matcher->findBestQuestionsForTextBlock($practice, 10)->pluck('id')->all(),
                'An exact CEFR restriction must still work.');
        }
    }
}
