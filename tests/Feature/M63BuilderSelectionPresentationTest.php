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
use App\Support\M63BuilderSelectionPresentation as Presentation;
use App\Support\M63RelativeCompletionLinkingPackage as Package;
use App\Support\PracticeCompletionLinkedPractice;
use App\Support\TheorySentenceBuilderAcceptance;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Actual M63 snapshots, unsaved models and the isolated SQLite connection only. */
class M63BuilderSelectionPresentationTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const IDS = [
        [30976, 30978, 30979, 30980, 30981],
        [31047, 31049, 31051, 31055, 31057],
        [30328, 30329, 30330, 30331, 30335],
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
            'type' => 'practice-set', 'level' => Package::LEVELS[$i], 'column' => $binding['column'], 'heading' => $binding['heading'],
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
            $pivots = array_column($sample['option_pivots_snapshot'], null, 'option_id');
            $q->setRelation('options', new ModelCollection(array_map(static function ($row) use ($sharedOptions, $pivots): QuestionOption {
                $option = clone $sharedOptions[$row['id']]; $row = $pivots[$row['id']]; $pivot = new Pivot;
                $pivot->setRawAttributes(['question_id' => $row['question_id'], 'option_id' => $row['option_id'], 'flag' => $row['flag']], true);
                $option->setRelation('pivot', $pivot); return $option;
            }, $sample['original_options_snapshot'])));
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

    public function test_actual_132_candidates_have_exact_thematic_b1_partitions_and_fifteen_reused_questions(): void
    {
        $audit = $this->audit(); [, $patch, , $contract] = Package::load(); Presentation::validateAudit($audit, $patch, $contract);
        self::assertSame([192, 144, 192], array_column($audit['targets'], 'pagewide_old_question_count'));
        self::assertSame([60, 12, 60], array_column($audit['targets'], 'all_pagewide_type4_at_practice_level_count'));
        self::assertSame([12, 12, 12], array_column($audit['targets'], 'exact_contract_pool_count'));
        self::assertSame(132, $audit['review_summary']['actual_type4_at_practice_level_candidates_read']);
        self::assertSame(36, $audit['review_summary']['exact_contract_candidates_read']);
        self::assertSame(15, $audit['scope']['reused_questions']); self::assertSame(0, $audit['scope']['new_questions']);
        self::assertSame(0, $audit['scope']['link_writes']);
        foreach ($audit['targets'] as $i => $target) {
            self::assertSame(self::IDS[$i], $target['expected_existing_selected_ids']);
            self::assertCount(7, $target['excluded_question_ids']); self::assertCount($i === 1 ? 0 : 48, $target['other_bank_eligible_review']);
            $eligible = array_values(array_filter($target['all_eligible_review'], static fn ($q) => !in_array($q['id'], $target['excluded_question_ids'], true)));
            usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            self::assertSame(self::IDS[$i], array_column($eligible, 'id'));
            self::assertSame($target['expected_selected_uuids'], array_column($eligible, 'uuid'));
            foreach ($target['first_five'] as $sample) {
                self::assertSame($sample['context_prefix_uk'].' '.$sample['displayed_condition_uk'], $sample['visible_condition_uk']);
                self::assertStringNotContainsString($sample['expected_word_key'], $sample['context_prefix_uk']);
                self::assertSame([], $sample['hints_snapshot']); self::assertSame([], $sample['verb_hints_snapshot']);
            }
        }
        self::assertSame(130, $audit['review_summary']['ordered_answer_instances']);
        self::assertSame(41, $audit['review_summary']['existing_links_selected']);
        self::assertSame(15, $audit['review_summary']['render_only_token_presentations']);
        self::assertSame(15, $audit['review_summary']['render_only_option_presentations']);
    }

    public function test_only_canonical_weakmap_marked_block_extends_existing_caller_exclusions(): void
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

    public function test_fifteen_exact_display_instances_clone_both_labels_and_answers_without_original_mutation(): void
    {
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog(); $instances = 0; $clones = 0;
        foreach ($this->audit()['targets'] as $i => $target) {
            $questions = $this->questions($target); $before = $questions->toArray();
            $copies = Presentation::bindings(['block' => $this->block($i), 'practiceQuestions' => $questions, 'theoryCanonical' => true])['practiceQuestions'];
            self::assertNotSame($questions, $copies);
            foreach ($copies as $n => $copy) {
                $sample = $target['first_five'][$n]; $original = $questions[$n];
                self::assertNotSame($original, $copy);
                self::assertSame(array_replace($original->getAttributes(), ['question' => $sample['visible_condition_uk']]), $copy->getAttributes());
                $tokens = ComposeTokenCase::normalize($copy->answers->pluck('option.option')->all());
                self::assertSame($sample['correct_tokens'], $tokens);
                self::assertSame($sample['expected_display_sentence'], implode(' ', $tokens).'.');
                $bank = ComposeTokenCase::mergeOptions($copy->options->pluck('option')->all(), $tokens); $counts = array_count_values($bank);
                foreach (array_count_values($tokens) as $token => $count) { self::assertSame($count, $counts[$token]); }
                foreach (['hints', 'verbHints', 'tags', 'markerTags'] as $name) { self::assertSame($original->getRelation($name), $copy->getRelation($name)); }
                foreach ($copy->options as $o => $option) {
                    $plan = collect($sample['option_presentations'])->firstWhere('option_id', $option->id);
                    if ($plan === null) { self::assertSame($original->options[$o], $option); }
                    else {
                        self::assertNotSame($original->options[$o], $option);
                        self::assertSame(array_replace($original->options[$o]->getAttributes(), ['option' => $plan['rendered']]), $option->getAttributes());
                        self::assertSame($plan['option'], $original->options[$o]->option);
                        if (strtolower($plan['option']) !== strtolower($plan['rendered'])) { self::assertNotContains($plan['option'], $bank); }
                    }
                }
                foreach ($copy->answers as $a => $answer) {
                    $plan = collect($sample['token_presentations'])->firstWhere('marker', $answer->marker);
                    if ($plan === null) { self::assertSame($original->answers[$a], $answer); }
                    else {
                        $clones++; self::assertNotSame($original->answers[$a], $answer);
                        self::assertSame($original->answers[$a]->getAttributes(), $answer->getAttributes());
                        self::assertSame($plan['rendered'], $answer->option->option);
                        self::assertNotSame($original->answers[$a]->option, $answer->option);
                        self::assertSame($copy->options->firstWhere('id', $answer->option_id), $answer->option);
                    }
                }
                if (in_array($copy->id, [31047, 31051, 31057, 30328, 30330], true)) { self::assertSame(1, $counts['The']); self::assertSame(1, $counts['the']); }
                if ($copy->id === 30335) { self::assertSame(1, $counts['The']); self::assertSame(2, $counts['the']); }
                $instances += count($tokens);
            }
            self::assertSame($before, $questions->toArray());
            foreach ($questions as $q) {
                self::assertSame([], $q->getDirty());
                foreach ($q->answers as $a) { self::assertSame([], $a->getDirty()); self::assertSame([], $a->option->getDirty()); }
                foreach ($q->options as $o) { self::assertSame([], $o->getDirty()); self::assertSame([], $o->pivot->getDirty()); }
            }
        }
        self::assertSame(130, $instances); self::assertSame(15, $clones);
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_one_stale_question_answer_option_hint_or_missing_relation_refuses_the_entire_collection(): void
    {
        $target = $this->audit()['targets'][0]; $block = $this->block(0);
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
            static fn ($qs) => $qs[2]->answers[2]->option->setAttribute('option', 'Changed'),
            static fn ($qs) => $qs[0]->options[0]->setAttribute('option', 'Changed'),
            static fn ($qs) => $qs[0]->options[0]->pivot->setAttribute('flag', 0),
            static fn ($qs) => $qs[0]->options[0]->pivot->setAttribute('question_id', 999),
            static fn ($qs) => $qs[0]->options[0]->pivot->setAttribute('option_id', 999),
            static fn ($qs) => $qs[0]->options[0]->unsetRelation('pivot'),
            static function ($qs): void {
                $hint = new QuestionHint; $hint->setRawAttributes(['id' => 999, 'question_id' => $qs[0]->id,
                    'provider' => 'manual', 'locale' => 'uk', 'hint' => 'Непогоджено.'], true);
                $qs[0]->setRelation('hints', new ModelCollection([$hint]));
            },
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
        $duplicated = $this->questions($target); $duplicated[4] = $duplicated[0];
        foreach ([$this->questions($target)->take(4), $this->questions($target)->reverse()->values(), $duplicated] as $qs) {
            $bindings = ['block' => $block, 'practiceQuestions' => $qs, 'theoryCanonical' => true];
            self::assertSame($bindings, Presentation::bindings($bindings));
        }
    }

    public function test_foreign_caller_locale_owner_or_practice_binding_and_unapproved_config_cannot_activate(): void
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

    public function test_preflight_refuses_changed_original_snapshot_context_display_plan_pool_or_final_binding(): void
    {
        $original = $this->audit(); [, $patch, , $contract] = Package::load();
        $changes = [
            static function (&$a): void { $a['scope']['question_writes'] = 1; },
            static function (&$a): void { $a['scope']['link_writes'] = 1; },
            static function (&$a): void { $a['targets'][0]['page_id'] = 1; },
            static function (&$a): void { $a['targets'][0]['category_ancestry'][0]['parent_id'] = 1; },
            static function (&$a): void { $a['targets'][0]['first_five'][0]['token_presentations'][0]['rendered'] = 'sister,'; },
            static function (&$a): void { $a['targets'][0]['first_five'][0]['option_presentations'][0]['rendered'] = 'brother'; },
            static function (&$a): void { $a['targets'][0]['first_five'][0]['token_presentations'][0]['raw_option_sha256'] = str_repeat('0', 64); },
            static function (&$a): void { $a['targets'][0]['first_five'][0]['snapshot_hashes']['question_snapshot'] = str_repeat('0', 64); },
            static function (&$a): void { $a['targets'][1]['first_five'][0]['context_prefix_uk'] = 'Інша умова.'; },
            static function (&$a): void { $a['targets'][2]['first_five'][0]['variants_snapshot'][0]['id']++; },
            static function (&$a): void { $a['targets'][0]['first_five'][0]['hints_snapshot'][] = ['hint' => 'Changed']; },
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
        $wrong = $contract; $wrong['targets'][1]['bank_seeders'] = ['ForeignBank'];
        $refused = false; try { Presentation::validateAudit($original, $patch, $wrong); } catch (RuntimeException) { $refused = true; }
        self::assertTrue($refused);
    }

    public function test_only_three_existing_seams_extend_and_accepted_m62_helper_and_widget_sources_remain_exact(): void
    {
        self::assertSame(1, substr_count(file_get_contents(app_path('Support/TheoryContentRenderer.php')), 'M63BuilderSelectionPresentation::bindings($bindings)'));
        self::assertSame(1, substr_count(file_get_contents(app_path('Support/PracticeCompletionLinkedPractice.php')), 'M63BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory)'));
        self::assertSame(1, substr_count(file_get_contents(app_path('Services/Theory/TextBlockToQuestionsMatcherService.php')), 'M63BuilderSelectionPresentation::exclusions($block, $linkedPractice, $excludeQuestionIds)'));
        self::assertStringNotContainsString('M63', file_get_contents(app_path('Support/ComposeTokenCase.php')));
        self::assertStringNotContainsString('M63', file_get_contents(resource_path('views/components/text-block-practice-questions.blade.php')));
        self::assertSame('aa549f10ade64f7383c6c7c5928c01f24e54b24ec2dad8b9a9fcf55ac64f7773', hash_file('sha256', app_path('Support/M62BuilderSelectionPresentation.php')));
        self::assertSame('5daee32edd5dc4b8c6d06f9eb17b56fcd7332eac15d132e2f0c5eab671925e0b', hash_file('sha256', resource_path('views/components/text-block-practice-questions.blade.php')));
    }

    public function test_controlled_role_fixtures_preserve_required_subjects_and_exact_new_punctuation_boundaries(): void
    {
        $targets = $this->audit()['targets']; $nd = $targets[0]['first_five']; $omitting = $targets[1]['first_five'];
        self::assertSame('The laptop, which I bought last year, is still fast.', $nd[1]['expected_display_sentence']);
        self::assertSame('She passed the exam, which surprised everyone.', $nd[2]['expected_display_sentence']);
        self::assertSame(2, substr_count($nd[0]['expected_display_sentence'], ','));
        self::assertSame(1, substr_count($nd[2]['expected_display_sentence'], ','));
        self::assertStringNotContainsString(',.', $nd[2]['expected_display_sentence']);
        self::assertStringContainsString('прекрасна', $nd[3]['displayed_condition_uk']);
        self::assertStringContainsString('прекрасний', $nd[3]['question_snapshot']['question']);
        foreach ($omitting as $i => $sample) {
            self::assertContains($i === 0 ? 'I' : 'we', $sample['correct_tokens']);
            self::assertSame([], $sample['token_presentations']);
            $withoutSubject = array_values(array_filter($sample['correct_tokens'], static fn ($token) => !in_array($token, ['I', 'we'], true)));
            self::assertNotSame($sample['correct_tokens'], $withoutSubject);
            self::assertTrue(str_contains($sample['context_prefix_uk'], 'без відносного займенника')
                || str_contains($sample['context_prefix_uk'], 'Опусти тільки відносний займенник-додаток'));
        }
        $plans = [];
        foreach ($targets as $target) {
            foreach ($target['first_five'] as $sample) {
                foreach ($sample['token_presentations'] as $r) {
                    self::assertSame(hash('sha256', $r['option']), $r['raw_option_sha256']);
                    if ($r['purpose'] === 'case_display') { $plans[] = [$sample['id'], $r['id'], $r['marker'], $r['option_id'], $r['option'], $r['rendered']]; }
                }
            }
        }
        self::assertSame([[30976, 170233, 'a3', 128, 'Who', 'who'], [30980, 170268, 'a2', 50, 'Where', 'where']], $plans);
        self::assertSame('The deadline was changed; therefore, the team adjusted the plan.', $targets[2]['first_five'][4]['expected_display_sentence']);
        self::assertStringContainsString('чи вдалося досягти мети, невідомо', $targets[2]['first_five'][3]['context_prefix_uk']);
    }

    public function test_actual_pagewide_matcher_and_universal_renderer_emit_exact_curated_payload_without_runtime_writes(): void
    {
        $this->rebuildComposeTestSchema();
        foreach ($this->audit()['targets'] as $i => $target) {
            $block = $this->block($i); $heroUuid = $target['first_five'][0]['theory_links_snapshot'][0]['text_block_uuid'];
            DB::table('text_blocks')->insert(['id' => 601000 + $i, 'uuid' => $heroUuid, 'page_id' => $block->page_id,
                'page_category_id' => Package::CATEGORY_IDS[$i], 'locale' => 'uk', 'type' => 'hero', 'sort_order' => 1, 'body' => '{}']);
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
            self::assertSame(0, DB::table('question_theory_text_blocks')->where('text_block_uuid', $block->uuid)->count());
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
                self::assertSame(0, preg_match('/^\\s*(?:insert|update|delete|replace|alter|drop|create)\\b/i', $query['query']));
            }
            DB::connection()->disableQueryLog(); self::assertEquals($before, DB::table('questions')->orderBy('id')->get()->toArray());
        }
    }
}
