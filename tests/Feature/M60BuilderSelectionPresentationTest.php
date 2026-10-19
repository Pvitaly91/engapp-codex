<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Services\Theory\TextBlockToQuestionsMatcherService;
use App\Support\ComposeTokenCase;
use App\Support\M26DetailPackage;
use App\Support\M60BuilderSelectionPresentation as Presentation;
use App\Support\M60ReportedSpeechBasicsPackage as Package;
use App\Support\PracticeCompletionLinkedPractice;
use App\Support\TheorySentenceBuilderAcceptance;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Actual M60 snapshots, unsaved models and the isolated SQLite connection only. */
class M60BuilderSelectionPresentationTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const IDS = [
        [40767, 40769, 40771, 40773, 40775],
        [19278, 40839, 40840, 40841, 40843],
        [40912, 40913, 40914, 40915, 40916],
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
        $binding = $this->audit()['practice_bindings'][$i]; [, $patch] = Package::load();
        $source = $patch['targets'][$i]['after']['page']['blocks'][$binding['source_index']];
        self::assertSame($binding['uuid'], M26DetailPackage::uuid(Package::OWNERS[$i], $source, $binding['source_index'] + 1));
        $model = new TextBlock;
        $model->setRawAttributes(['id' => 600000 + $i, 'uuid' => $binding['uuid'], 'page_id' => $binding['page_id'],
            'page_category_id' => $binding['category_id'], 'seeder' => $binding['identity'], 'locale' => 'uk',
            'type' => 'practice-set', 'level' => 'B1', 'column' => $binding['column'], 'heading' => $binding['heading'],
            'css_class' => $binding['css_class'], 'sort_order' => $binding['source_index'] + 1, 'body' => $source['body']], true);
        $model->setRelation('tags', new ModelCollection); $model->setRelation('page', null);
        return $model;
    }

    private function questions(array $target): ModelCollection
    {
        $sharedOptions = []; $out = [];
        foreach ($target['first_five'] as $sample) {
            foreach ($sample['original_options_snapshot'] as $row) {
                if (!isset($sharedOptions[$row['id']])) {
                    $option = new QuestionOption; $option->setRawAttributes($row, true); $sharedOptions[$row['id']] = $option;
                }
                self::assertSame($row['option'], $sharedOptions[$row['id']]->option);
            }
        }
        foreach ($target['first_five'] as $sample) {
            $q = new Question; $q->setRawAttributes($sample['question_snapshot'], true); $answers = []; $hints = [];
            foreach ($sample['ordered_answer_snapshot'] as $row) {
                $answer = new QuestionAnswer;
                $answer->setRawAttributes(['id' => $row['id'], 'question_id' => $q->id, 'marker' => $row['marker'],
                    'position' => $row['position'], 'option_id' => $row['option_id']], true);
                $answer->setRelation('option', $sharedOptions[$row['option_id']]); $answers[] = $answer;
            }
            foreach ($sample['hints_snapshot'] as $row) { $hint = new QuestionHint; $hint->setRawAttributes($row, true); $hints[] = $hint; }
            $q->setRelation('answers', new ModelCollection($answers));
            $q->setRelation('options', new ModelCollection(array_map(static fn ($row) => $sharedOptions[$row['id']], $sample['original_options_snapshot'])));
            $q->setRelation('hints', new ModelCollection($hints));
            foreach (['verbHints', 'tags', 'markerTags'] as $name) { $q->setRelation($name, new ModelCollection); }
            $out[] = $q;
        }
        return new ModelCollection($out);
    }

    private function config(array $target): array
    {
        return ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => $target['bank_seeders']];
    }

    public function test_full_actual_review_and_exact_filtered_partitions_reuse_fifteen_without_new_questions_or_links(): void
    {
        $audit = $this->audit(); [, $patch, , $contract] = Package::load();
        Presentation::validateAudit($audit, $patch, $contract);
        self::assertSame([168, 168, 192], array_column($audit['targets'], 'pagewide_old_question_count'));
        self::assertSame([12, 36, 60], array_column($audit['targets'], 'all_pagewide_type4_B1_count'));
        self::assertSame([12, 36, 12], array_column($audit['targets'], 'exact_contract_pool_count'));
        self::assertSame([7, 31, 7], array_map(static fn ($t) => count($t['excluded_question_ids']), $audit['targets']));
        self::assertSame(108, $audit['review_summary']['actual_type4_B1_candidates_read']);
        self::assertSame(15, $audit['scope']['reused_questions']); self::assertSame(0, $audit['scope']['new_questions']);
        self::assertSame(0, $audit['scope']['link_writes']);
        foreach ($audit['targets'] as $i => $target) {
            self::assertSame(self::IDS[$i], $target['expected_existing_selected_ids']);
            $eligible = array_values(array_filter($target['all_eligible_review'], static fn ($q) => !in_array($q['id'], $target['excluded_question_ids'], true)));
            usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            self::assertSame(self::IDS[$i], array_column($eligible, 'id'));
            self::assertSame($target['expected_selected_uuids'], array_column($eligible, 'uuid'));
            foreach ($target['first_five'] as $sample) {
                self::assertSame($sample['context_prefix_uk'].' '.$sample['condition_uk'], $sample['visible_condition_uk']);
                self::assertStringNotContainsString($sample['expected_word_key'], $sample['context_prefix_uk']);
            }
        }
        self::assertSame(48, count($audit['targets'][2]['other_bank_eligible_review']));
    }

    public function test_only_canonical_weakmap_marked_block_extends_existing_exclusions_preserving_every_caller_value(): void
    {
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        foreach ($this->audit()['targets'] as $i => $target) {
            $block = $this->block($i); $before = $block->toArray(); $config = $this->config($target);
            self::assertSame([999], Presentation::exclusions($block, $config, [999]));
            self::assertSame($block, PracticeCompletionLinkedPractice::matchingBlock($block, false));
            $copy = PracticeCompletionLinkedPractice::matchingBlock($block, true);
            self::assertNotSame($block, $copy); self::assertSame($before, $copy->toArray());
            self::assertSame([999, 999, ...$target['excluded_question_ids']], Presentation::exclusions($copy, $config, [999, 999]));
            self::assertSame([self::IDS[$i][0]], Presentation::exclusions($copy, $config, [self::IDS[$i][0]]));
            self::assertSame([999], Presentation::exclusions(clone $copy, $config, [999]));
            Presentation::matchingBlock($copy, false); self::assertSame([999], Presentation::exclusions($copy, $config, [999]));
            self::assertSame($before, $block->toArray()); self::assertSame([], $block->getDirty());
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_all_fifteen_strict_cases_and_116_instances_clone_only_three_answer_options_without_original_mutation(): void
    {
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog(); $instances = 0; $caseCount = 0;
        foreach ($this->audit()['targets'] as $i => $target) {
            $questions = $this->questions($target); $before = $questions->toArray();
            $bindings = ['block' => $this->block($i), 'practiceQuestions' => $questions, 'theoryCanonical' => true];
            $copies = Presentation::bindings($bindings)['practiceQuestions']; self::assertNotSame($questions, $copies);
            foreach ($copies as $n => $copy) {
                $sample = $target['first_five'][$n]; $original = $questions[$n];
                self::assertNotSame($original, $copy);
                self::assertSame(array_replace($original->getAttributes(), ['question' => $sample['visible_condition_uk']]), $copy->getAttributes());
                $tokens = ComposeTokenCase::normalize($copy->answers->pluck('option.option')->all());
                self::assertSame($sample['correct_tokens'], $tokens, 'Strict visible case '.$copy->id);
                $bank = ComposeTokenCase::mergeOptions($copy->options->pluck('option')->all(), $tokens); $counts = array_count_values($bank);
                foreach (array_count_values($tokens) as $token => $count) { self::assertSame($count, $counts[$token], 'Exact multiplicity '.$token); }
                foreach (['options', 'hints', 'verbHints', 'tags', 'markerTags'] as $name) { self::assertSame($original->getRelation($name), $copy->getRelation($name)); }
                if ($sample['case_restorations'] === []) { self::assertSame($original->getRelation('answers'), $copy->getRelation('answers')); }
                foreach ($sample['case_restorations'] as $case) {
                    $old = $original->answers->firstWhere('marker', $case['marker']); $new = $copy->answers->firstWhere('marker', $case['marker']);
                    self::assertNotSame($old, $new); self::assertNotSame($old->option, $new->option);
                    self::assertSame($case['raw'], $old->option->option); self::assertSame($case['rendered'], $new->option->option);
                    self::assertSame($case['raw'], $copy->options->firstWhere('id', $case['option_id'])->option);
                    self::assertNotContains($case['raw'], $bank); self::assertContains($case['rendered'], $bank); $caseCount++;
                }
                if ($copy->id === 40775) { self::assertSame(2, $counts['me']); }
                if ($copy->id === 19278) { self::assertSame(8, count($tokens)); self::assertNotContains('.', $tokens); }
                $instances += count($tokens);
            }
            self::assertSame($before, $questions->toArray());
            foreach ($questions as $q) { self::assertSame([], $q->getDirty()); foreach ($q->answers as $a) { self::assertSame([], $a->getDirty()); self::assertSame([], $a->option->getDirty()); } }
        }
        self::assertSame(116, $instances); self::assertSame(3, $caseCount);
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_one_stale_question_option_answer_hint_or_missing_relation_refuses_the_entire_five(): void
    {
        $target = $this->audit()['targets'][1]; $block = $this->block(1);
        $changes = [
            static fn ($qs) => $qs[4]->setAttribute('question', 'Змінено.'),
            static fn ($qs) => $qs[4]->setAttribute('seeder', 'ForeignBank'),
            static fn ($qs) => $qs[4]->setAttribute('level', 'B2'),
            static fn ($qs) => $qs[4]->setAttribute('type', '0'),
            static fn ($qs) => $qs[4]->setAttribute('category_id', 1),
            static fn ($qs) => $qs[4]->setAttribute('source_id', 1),
            static fn ($qs) => $qs[4]->setAttribute('difficulty', 999),
            static fn ($qs) => $qs[4]->setAttribute('flag', 1),
            static fn ($qs) => $qs[4]->setAttribute('options_by_marker', '{}'),
            static fn ($qs) => $qs[2]->answers[2]->setAttribute('id', 999),
            static fn ($qs) => $qs[2]->answers[2]->setAttribute('marker', 'a99'),
            static fn ($qs) => $qs[2]->answers[2]->option->setAttribute('option', 'where'),
            static fn ($qs) => $qs[0]->options[0]->setAttribute('option', 'Changed'),
            static fn ($qs) => $qs[0]->hints[0]->setAttribute('hint', 'Змінено.'),
            static fn ($qs) => $qs[3]->answers[4]->unsetRelation('option'),
            static fn ($qs) => $qs[4]->unsetRelation('options'),
            static fn ($qs) => $qs[4]->unsetRelation('hints'),
            static fn ($qs) => $qs[4]->unsetRelation('verbHints'),
        ];
        foreach ($changes as $change) {
            $qs = $this->questions($target); $change($qs); $before = $qs->toArray();
            $bindings = ['block' => $block, 'practiceQuestions' => $qs, 'theoryCanonical' => true];
            self::assertSame($bindings, Presentation::bindings($bindings)); self::assertSame($before, $qs->toArray());
        }
        foreach ([$this->questions($target)->take(4), $this->questions($target)->reverse()->values()] as $qs) {
            $bindings = ['block' => $block, 'practiceQuestions' => $qs, 'theoryCanonical' => true];
            self::assertSame($bindings, Presentation::bindings($bindings));
        }
    }

    public function test_foreign_caller_locale_owner_practice_metadata_or_config_cannot_activate(): void
    {
        foreach ($this->audit()['targets'] as $i => $target) {
            $block = $this->block($i); $qs = $this->questions($target); $config = $this->config($target);
            foreach (['seeder' => 'ForeignOwner', 'page_id' => 1, 'page_category_id' => 1, 'locale' => 'en',
                'level' => 'B2', 'type' => 'box', 'uuid' => 'foreign', 'sort_order' => 999,
                'column' => 'header', 'heading' => 'foreign', 'css_class' => 'foreign', 'body' => $block->body."\n"] as $field => $value) {
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

    public function test_preflight_refuses_any_changed_raw_snapshot_context_case_pool_scope_or_final_binding(): void
    {
        $original = $this->audit(); [, $patch, , $contract] = Package::load();
        $changes = [
            static function (&$a): void { $a['scope']['question_writes'] = 1; },
            static function (&$a): void { $a['scope']['link_writes'] = 1; },
            static function (&$a): void { $a['targets'][0]['page_id'] = 1; },
            static function (&$a): void { $a['targets'][0]['category_ancestry'][0]['parent_id'] = 1; },
            static function (&$a): void { $a['targets'][1]['first_five'][0]['case_restorations'][0]['answer_id']++; },
            static function (&$a): void { $a['targets'][1]['first_five'][0]['case_restorations'][0]['rendered'] = 'Who'; },
            static function (&$a): void { $a['targets'][0]['first_five'][0]['context_prefix_uk'] = 'Інша умова.'; },
            static function (&$a): void { $a['targets'][0]['first_five'][0]['variants_snapshot'][0]['id']++; },
            static function (&$a): void { $a['targets'][1]['first_five'][0]['hints_snapshot'][0]['hint'] = 'Changed'; },
            static function (&$a): void { array_pop($a['targets'][1]['all_eligible_review']); },
            static function (&$a): void { array_pop($a['targets'][2]['excluded_question_ids']); },
            static function (&$a): void { $a['targets'][2]['bank_seeders'] = ['ForeignBank']; },
            static function (&$a): void { $a['practice_bindings'][0]['body_sha256'] = str_repeat('0', 64); },
            static function (&$a): void { $a['practice_bindings'][1]['uuid'] = 'foreign'; },
        ];
        foreach ($changes as $change) {
            $audit = $original; $change($audit); $refused = false;
            try { Presentation::validateAudit($audit, $patch, $contract); } catch (RuntimeException) { $refused = true; }
            self::assertTrue($refused);
        }
    }

    public function test_only_three_existing_canonical_seams_are_extended_and_shared_case_or_widget_remain_unmodified(): void
    {
        self::assertSame(1, substr_count(file_get_contents(app_path('Support/TheoryContentRenderer.php')), 'M60BuilderSelectionPresentation::bindings($bindings)'));
        self::assertSame(1, substr_count(file_get_contents(app_path('Support/PracticeCompletionLinkedPractice.php')), 'M60BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory)'));
        self::assertSame(1, substr_count(file_get_contents(app_path('Services/Theory/TextBlockToQuestionsMatcherService.php')), 'M60BuilderSelectionPresentation::exclusions($block, $linkedPractice, $excludeQuestionIds)'));
        self::assertStringNotContainsString('M60', file_get_contents(app_path('Support/ComposeTokenCase.php')));
        self::assertStringNotContainsString('M60', file_get_contents(resource_path('views/components/text-block-practice-questions.blade.php')));
        self::assertSame(1, substr_count(file_get_contents(app_path('Support/TheoryContentRenderer.php')), 'M59BuilderSelectionPresentation::bindings($bindings)'));
    }

    public function test_actual_pagewide_matcher_and_universal_renderer_emit_exact_curated_payload_with_zero_runtime_writes(): void
    {
        $this->rebuildComposeTestSchema();
        foreach ($this->audit()['targets'] as $i => $target) {
            $block = $this->block($i);
            $heroUuid = $target['first_five'][0]['global_links_snapshot'][0]['text_block_uuid'];
            DB::table('text_blocks')->insert(['id' => 601000 + $i, 'uuid' => $heroUuid, 'page_id' => $block->page_id,
                'page_category_id' => 3, 'locale' => 'uk', 'type' => 'hero', 'sort_order' => 1, 'body' => '{}']);
            DB::table('text_blocks')->insert($block->getAttributes());
            $samples = array_column($target['first_five'], null, 'id');
            foreach ([...$target['all_eligible_review'], ...$target['other_bank_eligible_review']] as $row) {
                $sample = $samples[$row['id']] ?? null;
                DB::table('questions')->insert($sample['question_snapshot'] ?? ['id' => $row['id'], 'uuid' => $row['uuid'],
                    'question' => $row['condition_uk'], 'level' => 'B1', 'type' => '4', 'seeder' => $row['bank_seeder']]);
                DB::table('question_theory_text_blocks')->insert(['question_uuid' => $row['uuid'], 'text_block_uuid' => $heroUuid, 'position' => 0]);
                if ($sample === null) { continue; }
                foreach ($sample['original_options_snapshot'] as $option) {
                    DB::table('question_options')->insertOrIgnore($option);
                    DB::table('question_option_question')->insert(['question_id' => $row['id'], 'option_id' => $option['id'], 'flag' => null]);
                }
                foreach ($sample['ordered_answer_snapshot'] as $answer) {
                    DB::table('question_answers')->insert(['id' => $answer['id'], 'question_id' => $row['id'],
                        'option_id' => $answer['option_id'], 'marker' => $answer['marker']]);
                }
                foreach ($sample['hints_snapshot'] as $hint) { DB::table('question_hints')->insert($hint); }
                foreach ($sample['variants_snapshot'] as $variant) { DB::table('question_variants')->insert($variant); }
            }
            foreach ([['level' => 'B2', 'type' => '4', 'seeder' => $target['bank_seeders'][0]],
                ['level' => 'B1', 'type' => '0', 'seeder' => $target['bank_seeders'][0]],
                ['level' => 'B1', 'type' => '4', 'seeder' => 'ForeignBank']] as $n => $foreign) {
                $uuid = '000-foreign-'.$i.'-'.$n;
                DB::table('questions')->insert(['id' => 9100000 + $i * 10 + $n, 'uuid' => $uuid, 'question' => 'Foreign.'] + $foreign);
                DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $heroUuid, 'position' => 0]);
            }
            self::assertSame(0, DB::table('question_theory_text_blocks')->where('text_block_uuid', $block->uuid)->count(), 'No new practice link is required.');
            $before = DB::table('questions')->orderBy('id')->get()->toArray();
            DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
            $matching = PracticeCompletionLinkedPractice::matchingBlock($block, true);
            $questions = app(TextBlockToQuestionsMatcherService::class)->findBestQuestionsForTextBlock($matching, 5);
            self::assertSame(self::IDS[$i], $questions->pluck('id')->all());
            self::assertSame(array_column($target['first_five'], 'condition_uk'), $questions->pluck('question')->all());
            $originals = $questions->toArray(); app('view')->flushState();
            $html = view('theory.partials.content-block', ['block' => $block, 'theoryCanonical' => true,
                'm42StyleContext' => true, 'practiceQuestions' => $questions, 'lessonLinks' => []])->render();
            $payload = TheorySentenceBuilderAcceptance::payload($html, $block->uuid);
            self::assertIsArray($payload); self::assertSame(self::IDS[$i], array_column($payload, 'id'));
            foreach ($target['first_five'] as $n => $sample) {
                self::assertSame($sample['visible_condition_uk'], $payload[$n]['question']);
                self::assertSame($sample['correct_tokens'], $payload[$n]['correct_tokens']);
                $counts = array_count_values($payload[$n]['options']);
                foreach (array_count_values($sample['correct_tokens']) as $token => $count) { self::assertSame($count, $counts[$token]); }
            }
            self::assertSame($originals, $questions->toArray());
            foreach (DB::connection()->getQueryLog() as $query) {
                self::assertSame(0, preg_match('/^\s*(?:insert|update|delete|replace|alter|drop|create)\b/i', $query['query']));
            }
            DB::connection()->disableQueryLog();
            self::assertEquals($before, DB::table('questions')->orderBy('id')->get()->toArray());
        }
    }
}
