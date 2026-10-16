<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Support\ComposeTokenCase;
use App\Support\M26DetailPackage;
use App\Support\M59BuilderSelectionPresentation as Presentation;
use App\Support\M59PassiveFutureAgentReviewPackage as Package;
use App\Support\PracticeCompletionLinkedPractice;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Captured real M59 pools and shared options; never the working database or personal progress. */
class M59BuilderSelectionPresentationTest extends TestCase
{
    private const IDS = [
        [51651, 51653, 51656, 51657, 51658],
        [38247, 38250, 38251, 38252, 38256],
        [17064, 17066, 17042, 17031, 38767],
    ];

    protected function setUp(): void
    {
        parent::setUp(); app()->setLocale('uk'); $this->withoutVite();
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
    }

    private function audit(): array
    {
        self::assertSame(Presentation::AUDIT_SHA, hash_file('sha256', base_path(Presentation::AUDIT)));
        return json_decode(file_get_contents(base_path(Presentation::AUDIT)), true, flags: JSON_THROW_ON_ERROR);
    }

    private function block(int $i): TextBlock
    {
        $audit = $this->audit(); $binding = $audit['practice_bindings'][$i]; [, $patch] = Package::load();
        $source = $patch['targets'][$i]['after']['page']['blocks'][$binding['source_index']];
        self::assertSame($binding['uuid'], M26DetailPackage::uuid(Package::OWNERS[$i], $source, $binding['source_index'] + 1));
        self::assertSame($binding['body_sha256'], hash('sha256', $source['body']));
        $block = new TextBlock;
        $block->setRawAttributes(['id' => 590000 + $i, 'uuid' => $binding['uuid'], 'page_id' => $binding['page_id'],
            'page_category_id' => $binding['category_id'], 'seeder' => Package::OWNERS[$i], 'locale' => 'uk',
            'type' => 'practice-set', 'level' => $binding['level'], 'column' => $binding['column'],
            'heading' => $binding['heading'], 'css_class' => $binding['css_class'],
            'sort_order' => $binding['source_index'] + 1, 'body' => $source['body']], true);
        $block->setRelation('tags', new ModelCollection); $block->setRelation('page', null);
        return $block;
    }

    private function config(array $target): array
    {
        return ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => $target['bank_seeders']];
    }

    /** Deliberately share each physical option object across answers/questions, as Eloquent can. */
    private function questions(array $target): ModelCollection
    {
        $options = []; $out = [];
        foreach ($target['first_five'] as $sample) {
            foreach ($sample['original_options_snapshot'] as $row) {
                if (!isset($options[$row['id']])) { $model = new QuestionOption; $model->setRawAttributes($row, true); $options[$row['id']] = $model; }
                self::assertSame($row['option'], $options[$row['id']]->option);
            }
        }
        foreach ($target['first_five'] as $sample) {
            $question = new Question;
            self::assertSame($sample['question_options_by_marker_sha256'], hash('sha256', $sample['raw_options_by_marker']));
            $question->setRawAttributes(['id' => $sample['id'], 'uuid' => $sample['uuid'], 'question' => $sample['condition_uk'],
                'seeder' => $sample['bank_seeder'], 'type' => '4', 'level' => $sample['level'],
                'category_id' => $sample['category_id'], 'source_id' => $sample['source_id'],
                'options_by_marker' => $sample['raw_options_by_marker']], true);
            $answers = [];
            foreach ($sample['ordered_answer_snapshot'] as $row) {
                $answer = new QuestionAnswer;
                $answer->setRawAttributes(['id' => $row['id'], 'question_id' => $sample['id'], 'marker' => $row['marker'],
                    'position' => $row['position'], 'option_id' => $row['option_id']], true);
                self::assertSame($row['option'], $options[$row['option_id']]->option);
                $answer->setRelation('option', $options[$row['option_id']]); $answers[] = $answer;
            }
            $hints = [];
            foreach ($sample['hints_snapshot'] as $row) { $hint = new QuestionHint; $hint->setRawAttributes($row, true); $hints[] = $hint; }
            $question->setRelation('answers', new ModelCollection($answers));
            $question->setRelation('options', new ModelCollection(array_map(static fn ($row) => $options[$row['id']], $sample['original_options_snapshot'])));
            $question->setRelation('hints', new ModelCollection($hints));
            foreach (['tags', 'verbHints', 'markerTags'] as $name) { $question->setRelation($name, new ModelCollection); }
            $out[] = $question;
        }
        return new ModelCollection($out);
    }

    public function test_frozen_exact_three_bank_partitions_select_fifteen_existing_questions_and_zero_writes(): void
    {
        $audit = $this->audit(); [, $patch, , $contract] = Package::load();
        Presentation::validateAudit($audit, $patch, $contract);
        self::assertSame([144, 144, 192], array_column($audit['targets'], 'pagewide_old_question_count'));
        self::assertSame([12, 12, 60], array_map(static fn ($t) => count($t['all_eligible_review']), $audit['targets']));
        self::assertSame([7, 7, 55], array_map(static fn ($t) => count($t['excluded_question_ids']), $audit['targets']));
        self::assertSame(15, $audit['scope']['reused_questions']); self::assertSame(0, $audit['scope']['new_questions']);
        foreach ($audit['targets'] as $i => $target) {
            self::assertSame(self::IDS[$i], $target['expected_existing_selected_ids']);
            $remaining = array_values(array_filter($target['all_eligible_review'], static fn ($q) => !in_array($q['id'], $target['excluded_question_ids'], true)));
            usort($remaining, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            self::assertSame(self::IDS[$i], array_column($remaining, 'id'));
            self::assertSame($target['expected_selected_uuids'], array_column($remaining, 'uuid'));
            foreach ($target['first_five'] as $sample) {
                self::assertSame($sample['context_prefix_uk'].' '.$sample['condition_uk'], $sample['visible_condition_uk']);
                self::assertStringNotContainsString($sample['expected_word_key'], $sample['context_prefix_uk']);
            }
        }
    }

    public function test_canonical_matching_clones_reuse_existing_exclusion_seam_without_queries_or_changed_models(): void
    {
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        foreach ($this->audit()['targets'] as $i => $target) {
            $block = $this->block($i); $before = $block->toArray(); $config = $this->config($target);
            self::assertSame([999], Presentation::exclusions($block, $config, [999]));
            self::assertSame($block, PracticeCompletionLinkedPractice::matchingBlock($block, false));
            $copy = PracticeCompletionLinkedPractice::matchingBlock($block, true);
            self::assertNotSame($block, $copy); self::assertSame($before, $copy->toArray());
            self::assertSame([999, 999, ...$target['excluded_question_ids']], Presentation::exclusions($copy, $config, [999, 999]));
            self::assertSame([$target['expected_existing_selected_ids'][0]], Presentation::exclusions($copy, $config, [$target['expected_existing_selected_ids'][0]]));
            self::assertSame([999], Presentation::exclusions(clone $copy, $config, [999]));
            self::assertSame($copy, Presentation::matchingBlock($copy, false));
            self::assertSame([999], Presentation::exclusions($copy, $config, [999]));
            self::assertSame($before, $block->toArray()); self::assertSame([], $block->getDirty());
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_all_fifteen_real_snapshots_gain_only_reviewed_context_and_one_exact_answer_option_case_clone(): void
    {
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog(); $instances = 0; $changedCase = 0;
        foreach ($this->audit()['targets'] as $i => $target) {
            $questions = $this->questions($target); $before = $questions->toArray();
            $bindings = ['block' => $this->block($i), 'practiceQuestions' => $questions, 'theoryCanonical' => true];
            $after = Presentation::bindings($bindings)['practiceQuestions']; self::assertNotSame($questions, $after);
            foreach ($after as $n => $copy) {
                $sample = $target['first_five'][$n]; $original = $questions[$n];
                self::assertNotSame($original, $copy); self::assertSame($sample['visible_condition_uk'], $copy->question);
                self::assertSame(array_replace($original->getAttributes(), ['question' => $sample['visible_condition_uk']]), $copy->getAttributes());
                $correct = ComposeTokenCase::normalize($copy->answers->pluck('option.option')->all());
                self::assertSame($sample['correct_tokens'], $correct, 'strict raw case '.$copy->id);
                $bank = ComposeTokenCase::mergeOptions($copy->options->pluck('option')->all(), $correct);
                $counts = array_count_values($bank);
                foreach (array_count_values($correct) as $token => $count) { self::assertSame($count, $counts[$token], 'multiplicity '.$copy->id.':'.$token); }
                self::assertSame($original->getRelation('options'), $copy->getRelation('options'));
                self::assertSame($original->getRelation('hints'), $copy->getRelation('hints'));
                if ($copy->id === 51653) {
                    self::assertSame('Progress', $original->answers->last()->option->option);
                    self::assertSame('progress', $copy->answers->last()->option->option);
                    self::assertSame('Progress', $copy->options->firstWhere('id', 5009)->option);
                    self::assertContains('progress', $bank); self::assertNotContains('Progress', $bank); $changedCase++;
                } else { self::assertSame($original->getRelation('answers'), $copy->getRelation('answers')); }
                $instances += count($correct);
            }
            self::assertSame($before, $questions->toArray());
            foreach ($questions as $q) { self::assertSame([], $q->getDirty()); foreach ($q->answers as $answer) { self::assertSame([], $answer->getDirty()); self::assertSame([], $answer->option->getDirty()); } }
        }
        self::assertSame(1, $changedCase); self::assertSame(129, $instances);
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_a_single_changed_question_answer_option_metadata_hint_or_missing_relation_rejects_the_whole_five(): void
    {
        $target = $this->audit()['targets'][2]; $block = $this->block(2);
        $mutations = [
            static fn ($qs) => $qs[4]->setAttribute('question', $qs[4]->question.' changed'),
            static fn ($qs) => $qs[4]->setAttribute('seeder', 'ForeignBank'),
            static fn ($qs) => $qs[4]->setAttribute('level', 'C1'),
            static fn ($qs) => $qs[4]->setAttribute('type', '0'),
            static fn ($qs) => $qs[4]->setAttribute('category_id', 999),
            static fn ($qs) => $qs[4]->setAttribute('source_id', 999),
            static fn ($qs) => $qs[4]->setAttribute('options_by_marker', '{}'),
            static fn ($qs) => $qs[4]->answers[1]->setAttribute('marker', 'a99'),
            static fn ($qs) => $qs[4]->answers[1]->option->setAttribute('option', 'Changed'),
            static fn ($qs) => $qs[0]->hints[0]->setAttribute('hint', 'Змінено.'),
            static fn ($qs) => $qs[4]->unsetRelation('options'),
        ];
        foreach ($mutations as $mutate) {
            $qs = $this->questions($target); $mutate($qs); $before = $qs->toArray();
            $bindings = ['block' => $block, 'practiceQuestions' => $qs, 'theoryCanonical' => true];
            self::assertSame($bindings, Presentation::bindings($bindings)); self::assertSame($before, $qs->toArray());
        }
        foreach ([$this->questions($target)->take(4), $this->questions($target)->reverse()->values()] as $qs) {
            $bindings = ['block' => $block, 'practiceQuestions' => $qs, 'theoryCanonical' => true];
            self::assertSame($bindings, Presentation::bindings($bindings));
        }
    }

    public function test_foreign_or_stale_practice_caller_locale_and_config_cannot_activate_curation(): void
    {
        foreach ($this->audit()['targets'] as $i => $target) {
            $block = $this->block($i); $config = $this->config($target); $qs = $this->questions($target);
            foreach (['seeder' => 'ForeignOwner', 'page_id' => 999, 'page_category_id' => 999, 'locale' => 'en',
                'level' => 'C1', 'type' => 'box', 'uuid' => 'foreign', 'sort_order' => 999, 'column' => 'header',
                'heading' => 'foreign', 'css_class' => 'foreign', 'body' => $block->body."\n"] as $field => $value) {
                $bad = clone $block; $bad->$field = $value;
                self::assertSame($bad, Presentation::matchingBlock($bad, true), $field);
                $bindings = ['block' => $bad, 'practiceQuestions' => $qs, 'theoryCanonical' => true];
                self::assertSame($bindings, Presentation::bindings($bindings), $field);
            }
            $marked = Presentation::matchingBlock($block, true);
            foreach ([[], array_replace($config, ['source' => 'tags']), array_replace($config, ['question_types' => ['0', '4']]),
                array_replace($config, ['seeder_classes' => ['ForeignBank']]), $config + ['question_ids' => self::IDS[$i]]] as $bad) {
                self::assertSame([999], Presentation::exclusions($marked, $bad, [999]));
            }
            $bindings = ['block' => $block, 'practiceQuestions' => $qs, 'theoryCanonical' => false];
            self::assertSame($bindings, Presentation::bindings($bindings));
            foreach (['en', 'pl'] as $locale) {
                app()->setLocale($locale); self::assertSame($block, Presentation::matchingBlock($block, true));
                self::assertSame([999], Presentation::exclusions($marked, $config, [999]));
            }
            app()->setLocale('uk');
        }
    }

    public function test_writer_audit_rejects_wrong_ancestry_short_pool_foreign_bank_variant_case_or_write_scope(): void
    {
        $original = $this->audit(); [, $patch, , $contract] = Package::load();
        $mutations = [
            static function (&$a): void { $a['scope']['question_writes'] = 1; },
            static function (&$a): void { $a['targets'][0]['category_ancestry'][1]['parent_id'] = 999; },
            static function (&$a): void { $a['targets'][1]['page_id'] = 1; },
            static function (&$a): void { $a['targets'][2]['level'] = 'C1'; },
            static function (&$a): void { array_pop($a['targets'][0]['all_eligible_review']); },
            static function (&$a): void { array_pop($a['targets'][2]['excluded_question_ids']); },
            static function (&$a): void { $a['targets'][2]['bank_seeders'] = ['ForeignBank']; },
            static function (&$a): void { $a['targets'][0]['first_five'][1]['correct_tokens'][11] = 'Progress'; },
            static function (&$a): void { $a['targets'][1]['first_five'][0]['variants_snapshot'][0]['id']++; },
            static function (&$a): void { $a['targets'][2]['first_five'][0]['context_prefix_uk'] = 'English only'; },
            static function (&$a): void { $a['practice_bindings'][0]['body_sha256'] = str_repeat('0', 64); },
            static function (&$a): void { $a['practice_bindings'][2]['uuid'] = 'foreign'; },
        ];
        foreach ($mutations as $mutate) {
            $audit = $original; $mutate($audit); $failed = false;
            try { Presentation::validateAudit($audit, $patch, $contract); } catch (RuntimeException) { $failed = true; }
            self::assertTrue($failed, 'Negative audit must refuse, not partially select.');
        }
    }

    public function test_only_three_existing_canonical_seams_are_added_without_new_case_whitelist_or_grader(): void
    {
        $renderer = file_get_contents(app_path('Support/TheoryContentRenderer.php'));
        $seam = file_get_contents(app_path('Support/PracticeCompletionLinkedPractice.php'));
        $matcher = file_get_contents(app_path('Services/Theory/TextBlockToQuestionsMatcherService.php'));
        self::assertSame(1, substr_count($renderer, 'M59BuilderSelectionPresentation::bindings($bindings)'));
        self::assertSame(1, substr_count($seam, 'M59BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory)'));
        self::assertSame(1, substr_count($matcher, 'M59BuilderSelectionPresentation::exclusions($block, $linkedPractice, $excludeQuestionIds)'));
        $case = file_get_contents(app_path('Support/ComposeTokenCase.php'));
        self::assertStringNotContainsString('M59', $case); self::assertStringNotContainsString("'progress'", $case);
        self::assertSame(1, substr_count($renderer, 'M56BuilderSelectionPresentation::bindings($bindings)'));
    }
}
