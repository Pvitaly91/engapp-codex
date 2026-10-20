<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Models\VerbHint;
use App\Support\ComposeTokenCase;
use App\Support\M26DetailPackage;
use App\Support\M51QuestionSupplement;
use App\Support\M72PartitivesNegativesGeography as Package;
use App\Support\M72BuilderSelectionPresentation as Presentation;
use App\Support\TheoryBuilderSelectionPresentation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

/** Public educational contract and explicitly synthetic unsaved snapshots; no private DB snapshots. */
class M72BuilderSelectionPresentationTest extends TestCase
{
    private const SELECTED = [
        [32115, 32120, 32122, 32123, 32124],
        [32775, 32778, 32779, 32780, 32788],
        [31539, 31542, 31543, 31547, 31550],
    ];

    private function audit(): array
    {
        self::assertSame(Presentation::AUDIT_SHA, hash_file('sha256', base_path(Presentation::AUDIT)));
        self::assertSame(Presentation::REVISION_SHA, hash_file('sha256', base_path(Presentation::REVISION)));
        $revision = json_decode(file_get_contents(base_path(Presentation::REVISION)), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('1.0.1', $revision['version']);
        self::assertSame(['path' => Presentation::AUDIT, 'sha256' => Presentation::AUDIT_SHA, 'bytes' => 99977], $revision['active_audit']);
        self::assertSame(153, count($revision['exact_json_value_diff']));
        self::assertSame(32781, $revision['selection']['removed_id']);
        self::assertSame(32788, $revision['selection']['existing_replacement_id']);
        self::assertNull($revision['selection']['technical_filter_after']);
        self::assertSame('a7b32c1f05eb75c760073fcc53a5e0e752b045d788c0de46e116ad392536a4a2', hash_file('sha256', base_path(Package::CHECKSUMS)));
        self::assertSame('bc51862d48ec3764363574881b14fdc78f0b481730a3a72bd86b9f92264e2c1d', hash_file('sha256', base_path($revision['previous_audit']['path'])));
        return json_decode(file_get_contents(base_path(Presentation::AUDIT)), true, flags: JSON_THROW_ON_ERROR);
    }

    private function block(int $i): TextBlock
    {
        [, $patch] = Package::load();
        $a = $this->audit()['practice_bindings'][$i];
        $source = $patch['targets'][$i]['after']['page']['blocks'][$a['source_index']];
        $block = new TextBlock;
        $block->setRawAttributes(['id' => 720000 + $i, 'uuid' => $a['uuid'], 'page_id' => $a['page_id'],
            'page_category_id' => $a['category_id'], 'seeder' => $a['identity'], 'locale' => 'uk',
            'type' => 'practice-set', 'level' => $a['level'], 'column' => $a['column'], 'heading' => $a['heading'],
            'css_class' => $a['css_class'], 'sort_order' => $a['source_index'] + 1, 'body' => $source['body']], true);
        return $block;
    }

    public function test_three_null_filter_contracts_keep_full_pools_and_fifteen_distinct_reused_thematic_questions(): void
    {
        self::assertSame('2bcd468b25af3c44dcf34ccee0e012f3f683335d', Package::BASE_SHA);
        self::assertSame([88, 87, 83], Package::PAGE_IDS);
        self::assertSame([12, 11, 13], Package::PRACTICE_SOURCE_INDICES);
        $audit = $this->audit();
        [, $patch, , $contract] = Package::load();
        Presentation::validateAudit($audit, $patch, $contract);
        self::assertSame([null, null, null], Package::LEVELS);
        self::assertSame([null, null, null], $audit['review_summary']['technical_filter']);
        self::assertSame(['A2', 'B1', 'A2'], $audit['review_summary']['intended_selected_levels']);
        self::assertSame(37, $audit['review_summary']['candidate_semantic_review']);
        self::assertSame([['A2', 'A2', 'A2', 'A2', 'A2'], ['B1', 'B1', 'B1', 'B1', 'B2'], ['A2', 'A2', 'A2', 'A2', 'A2']], $audit['review_summary']['actual_selected_levels']);
        self::assertCount(3, $audit['targets']);
        self::assertSame(15, $audit['scope']['selected_questions']);
        self::assertSame(15, $audit['scope']['reused_questions']);
        self::assertSame(0, $audit['scope']['new_questions']);
        foreach (['question_writes', 'option_writes', 'answer_writes', 'option_link_writes', 'link_writes',
            'old_hint_writes', 'old_question_updates', 'old_option_updates', 'old_answer_updates', 'grader_changes', 'progress_writes'] as $field) {
            self::assertSame(0, $audit['scope'][$field], $field);
        }
        $allSelected = [];
        foreach ($audit['targets'] as $i => $target) {
            self::assertNull($target['level']);
            self::assertNull($contract['targets'][$i]['level']);
            self::assertSame(['A2', 'B1', 'A2'][$i], $target['intended_selected_level']);
            self::assertSame(self::SELECTED[$i], $target['expected_selected_ids']);
            self::assertCount(1, $target['bank_seeders']);
            $pool = $target['expected_pool_ids_by_bank'][$target['bank_seeders'][0]];
            self::assertCount(72, $pool);
            self::assertCount(72, array_unique($pool));
            self::assertCount(67, $target['excluded_question_ids']);
            self::assertCount($i === 1 ? 13 : 12, $target['all_eligible_review']);
            self::assertCount($i === 1 ? 59 : 60, $target['scope_exclusions']);
            self::assertCount(5, $target['first_five']);
            self::assertSame([], array_intersect(array_column($target['scope_exclusions'], 'id'), self::SELECTED[$i]));
            $partition = [...$target['expected_selected_ids'], ...$target['excluded_question_ids']];
            sort($pool); sort($partition);
            self::assertSame($pool, $partition);
            $selectedReviews = array_values(array_filter($target['all_eligible_review'], static fn ($q) => $q['selected']));
            self::assertSame(self::SELECTED[$i], array_column($selectedReviews, 'id'));
            foreach ($target['all_eligible_review'] as $review) {
                self::assertSame([0, 0], $review['hint_counts']);
                self::assertNotSame('', trim($review['decision']));
                self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $review['full_private_snapshot_sha256']);
            }
            foreach ($target['scope_exclusions'] as $excluded) {
                self::assertNotSame($target['intended_selected_level'], $excluded['level']);
                self::assertStringContainsString('no detailed semantic review claim', $excluded['reason']);
            }
            foreach ($target['first_five'] as $question) {
                self::assertFalse($question['is_new']);
                self::assertSame(M51QuestionSupplement::ROW_COLUMNS['questions'], $question['question_attribute_keys']);
                self::assertCount(6, $question['snapshot_hashes']);
                foreach ($question['snapshot_hashes'] as $sha) {
                    self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha);
                }
                foreach (['full_private_snapshot_sha256', 'variants_sha256', 'links_sha256'] as $field) {
                    self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $question[$field]);
                }
                foreach (['question_snapshot', 'ordered_answer_snapshot', 'original_options_snapshot', 'hints_snapshot',
                    'verb_hints_snapshot', 'loaded_option_pivots', 'created_at', 'updated_at', 'runtime', 'identity'] as $forbidden) {
                    self::assertArrayNotHasKey($forbidden, $question);
                }
                self::assertSame([], $question['token_presentations']);
                self::assertSame([], $question['option_presentations']);
                self::assertNotSame('', trim($question['displayed_condition_uk']));
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $question['visible_condition_uk']);
                self::assertSame($question['correct_tokens'], ComposeTokenCase::normalize($question['raw_correct_tokens']));
                self::assertSame($question['correct_tokens'], ComposeTokenCase::normalize($question['correct_tokens']));
                self::assertSame($question['expected_word_key'], implode(' ', $question['correct_tokens']));
                foreach ($question['correct_tokens'] as $token) {
                    self::assertNotSame('', trim($token));
                    self::assertNotContains(mb_strtolower($token), ['ø', 'без артикля', 'no article']);
                }
                $allSelected[] = $question['id'];
            }
        }
        self::assertCount(15, array_unique($allSelected));
    }

    public function test_native_case_contents_and_token_multiplicity_are_preserved_without_formatters(): void
    {
        $audit = $this->audit();
        foreach ($audit['targets'] as $target) {
            foreach ($target['first_five'] as $question) {
                self::assertSame($question['raw_correct_tokens'], array_column($question['original_ordered_instances'], 'option'));
                self::assertSame($question['correct_tokens'], ComposeTokenCase::normalize($question['raw_correct_tokens']));
                $required = array_count_values($question['correct_tokens']);
                $bank = array_count_values($question['token_bank_with_multiplicity']);
                foreach ($required as $token => $count) {
                    self::assertGreaterThanOrEqual($count, $bank[$token] ?? 0, 'Every ordered token instance must be available.');
                }
                self::assertSame([], $question['token_presentations']);
                self::assertSame([], $question['option_presentations']);
            }
        }
        $cups = array_values(array_filter($audit['targets'][0]['first_five'], static fn ($q) => $q['id'] === 32124))[0];
        self::assertSame(['He', 'gave', 'me', 'two', 'cups', 'of', 'coffee'], $cups['correct_tokens']);
        foreach ([32118, 32121, 32125, 32126] as $id) {
            self::assertContains($id, $audit['targets'][0]['excluded_question_ids']);
        }
        self::assertContains(31541, $audit['targets'][2]['excluded_question_ids']);
        self::assertContains(32781, $audit['targets'][1]['excluded_question_ids']);
        self::assertSame(['None', 'of', 'the', 'water', 'was', 'clean'], $audit['targets'][1]['first_five'][4]['correct_tokens']);
        self::assertSame('B2', $audit['targets'][1]['first_five'][4]['level']);
    }

    public function test_hash_bound_audit_refuses_scope_pool_condition_level_or_practice_binding_changes(): void
    {
        $audit = $this->audit(); [, $patch, , $contract] = Package::load();
        $changes = [
            static function (&$x): void { $x['scope']['question_writes']++; },
            static function (&$x): void { $x['targets'][0]['page_id']++; },
            static function (&$x): void { $x['targets'][0]['level'] = 'A2'; },
            static function (&$x): void { $x['targets'][1]['first_five'][0]['snapshot_hashes']['question_snapshot'] = str_repeat('0', 64); },
            static function (&$x): void { $x['targets'][2]['first_five'][0]['visible_condition_uk'] = 'Інший контекст.'; },
            static function (&$x): void { array_pop($x['targets'][1]['excluded_question_ids']); },
            static function (&$x): void { $x['targets'][0]['expected_selected_ids'][4] = $x['targets'][0]['expected_selected_ids'][0]; },
            static function (&$x): void { $x['practice_bindings'][2]['uuid'] = 'foreign'; },
            static function (&$x): void { $x['practice_bindings'][1]['source_index']++; },
            static function (&$x): void { $x['practice_bindings'][0]['level'] = 'A2'; },
        ];
        foreach ($changes as $change) {
            $bad = $audit; $change($bad); $refused = false;
            try { Presentation::validateAudit($bad, $patch, $contract); } catch (RuntimeException) { $refused = true; }
            self::assertTrue($refused);
        }
    }

    public function test_only_exact_unsaved_theory_matching_clones_extend_exclusions_without_queries_or_mutation(): void
    {
        app()->setLocale('uk'); DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        foreach ($this->audit()['targets'] as $i => $target) {
            $block = $this->block($i); $original = $block->getAttributes();
            $config = ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => $target['bank_seeders']];
            $callerExclusions = [999];
            self::assertSame([999], Presentation::exclusions($block, $config, $callerExclusions));
            $copy = Presentation::matchingBlock($block, true);
            self::assertNotSame($block, $copy);
            self::assertSame([999, ...$target['excluded_question_ids']], Presentation::exclusions($copy, $config, $callerExclusions));
            self::assertSame([999], $callerExclusions);
            self::assertSame([999], Presentation::exclusions(clone $copy, $config, [999]));
            self::assertSame($original, $block->getAttributes());
            self::assertSame([], $block->getDirty());
            self::assertFalse($block->exists);
            foreach (['seeder' => 'Foreign\\NeverRegisteredOwner', 'locale' => 'en', 'type' => 'box', 'level' => 'A2',
                'page_id' => 1, 'page_category_id' => 1, 'uuid' => 'foreign', 'body' => $block->body."\n",
                'sort_order' => 99, 'column' => 'right', 'heading' => 'Foreign', 'css_class' => 'foreign'] as $field => $value) {
                $foreign = clone $block; $foreign->$field = $value;
                self::assertSame($foreign, Presentation::matchingBlock($foreign, true), $field);
            }
            self::assertSame($block, Presentation::matchingBlock($block, false));
            foreach (['en', 'pl'] as $locale) {
                app()->setLocale($locale);
                self::assertSame($block, Presentation::matchingBlock($block, true));
            }
            app()->setLocale('uk');
            $wrongConfig = $config; $wrongConfig['seeder_classes'] = ['Foreign\\Bank'];
            self::assertSame([999], Presentation::exclusions($copy, $wrongConfig, [999]));
            $selectedExcluded = [$target['expected_selected_ids'][0]];
            self::assertSame($selectedExcluded, Presentation::exclusions($copy, $config, $selectedExcluded));
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_canonical_clone_provenance_cannot_be_reused_by_another_finite_profile(): void
    {
        app()->setLocale('uk'); DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        $block = $this->block(0); $original = $block->getAttributes(); $target = $this->audit()['targets'][0];
        $config = ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => $target['bank_seeders']];
        $copy = Presentation::matchingBlock($block, true); self::assertNotSame($block, $copy);
        $foreign = new class extends TheoryBuilderSelectionPresentation {
            protected static function packageClass(): string { throw new RuntimeException('A foreign profile must not inspect another profile clone.'); }
            protected static function policy(): array { throw new RuntimeException('A foreign profile must not inspect another profile policy.'); }
        };
        self::assertSame([999], $foreign::exclusions($copy, $config, [999]));
        self::assertSame([999, ...$target['excluded_question_ids']], Presentation::exclusions($copy, $config, [999]));
        self::assertSame($original, $block->getAttributes()); self::assertSame([], $block->getDirty());
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    /** These deliberately invented A2 models are not reconstructed from opaque real-DB hashes. */
    private function synthetic(): array
    {
        $questions = []; $samples = [];
        for ($n = 0; $n < 5; ++$n) {
            $id = 81000 + $n;
            $attrs = ['id' => $id, 'uuid' => 'm72-synthetic-'.$n, 'question' => 'Синтетична умова.',
                'difficulty' => 2, 'level' => 'A2', 'category_id' => 45, 'source_id' => 305, 'flag' => 0, 'type' => '4',
                'options_by_marker' => null, 'theory_text_block_uuid' => null, 'seeder' => 'SyntheticOnly',
                'created_at' => '2000-01-01 00:00:00', 'updated_at' => '2000-01-01 00:00:00'];
            $question = new Question; $question->setRawAttributes($attrs, true);
            $options = []; $answers = []; $optionRows = []; $answerRows = []; $pivotRows = [];
            foreach (['We', 'have', 'a', 'few', 'minutes'] as $i => $token) {
                $optionId = 92000 + $n * 10 + $i; $answerId = 93000 + $n * 10 + $i; $marker = 'a'.($i + 1);
                $option = new QuestionOption; $option->setRawAttributes(['id' => $optionId, 'option' => $token], true);
                $pivotRow = ['question_id' => $id, 'option_id' => $optionId, 'flag' => null];
                $pivot = new Pivot; $pivot->setRawAttributes($pivotRow, true); $option->setRelation('pivot', $pivot);
                $answer = new QuestionAnswer;
                $answer->setRawAttributes(['id' => $answerId, 'question_id' => $id, 'option_id' => $optionId, 'marker' => $marker, 'position' => null], true);
                $answer->setRelation('option', $option); $options[] = $option; $answers[] = $answer;
                $optionRows[] = ['id' => $optionId, 'option' => $token];
                $answerRows[] = ['id' => $answerId, 'marker' => $marker, 'position' => null, 'option_id' => $optionId, 'option' => $token];
                $pivotRows[] = $pivotRow;
            }
            $question->setRelation('answers', new Collection($answers));
            $question->setRelation('options', new Collection($options));
            $question->setRelation('hints', new Collection); $question->setRelation('verbHints', new Collection);
            $questions[] = $question;
            $samples[] = ['question_attribute_keys' => array_keys($attrs), 'snapshot_hashes' => array_map(
                M26DetailPackage::digest(...), ['question_snapshot' => $attrs, 'ordered_answer_snapshot' => $answerRows,
                    'original_options_snapshot' => $optionRows, 'hints_snapshot' => [], 'verb_hints_snapshot' => [],
                    'loaded_option_pivots' => $pivotRows]), 'token_presentations' => []];
        }
        return [new Collection($questions), ['expected_selected_ids' => array_map(static fn ($q) => $q->id, $questions),
            'expected_selected_uuids' => array_map(static fn ($q) => $q->uuid, $questions), 'first_five' => $samples]];
    }

    public function test_only_complete_known_matcher_decorations_are_allowed_without_original_mutation(): void
    {
        $method = new ReflectionMethod(TheoryBuilderSelectionPresentation::class, 'matchesQuestions');
        [$questions, $target] = $this->synthetic();
        foreach ($questions as $question) {
            $question->setAttribute('match_score', null);
            $question->setAttribute('matched_tag_ids', []);
            $question->setAttribute('marker_tags', []);
        }
        $before = $questions->toArray(); self::assertTrue($method->invoke(null, $questions, $target));
        self::assertSame($before, $questions->toArray());
        $mutations = [
            fn ($q) => $q[4]->setAttribute('match_score', 1),
            fn ($q) => $q[4]->setAttribute('matched_tag_ids', [1]),
            fn ($q) => $q[4]->setAttribute('marker_tags', null),
            fn ($q) => $q[4]->setAttribute('marker_tags', 'foreign'),
            fn ($q) => $q[4]->offsetUnset('marker_tags'),
        ];
        foreach ($mutations as $mutate) {
            [$questions, $target] = $this->synthetic();
            foreach ($questions as $question) {
                $question->setAttribute('match_score', null);
                $question->setAttribute('matched_tag_ids', []);
                $question->setAttribute('marker_tags', []);
            }
            $mutate($questions); $before = $questions->toArray();
            self::assertFalse($method->invoke(null, $questions, $target)); self::assertSame($before, $questions->toArray());
        }
        [$questions, $target] = $this->synthetic(); $questions[0]->setAttribute('match_score', null);
        self::assertFalse($method->invoke(null, $questions, $target), 'Partial decoration tuple must fall back.');
        [$questions, $target] = $this->synthetic(); $questions[1]->setAttribute('unknown_decoration', []);
        self::assertFalse($method->invoke(null, $questions, $target));
    }

    public function test_opaque_snapshot_rejects_stale_attributes_options_pivots_missing_hints_and_unknown_fields_without_queries(): void
    {
        $method = new ReflectionMethod(TheoryBuilderSelectionPresentation::class, 'matchesQuestions');
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        [$questions, $target] = $this->synthetic(); $before = $questions->toArray();
        self::assertTrue($method->invoke(null, $questions, $target)); self::assertSame($before, $questions->toArray());
        $changes = [
            fn ($q) => $q[4]->setAttribute('question', 'Інша умова.'),
            fn ($q) => $q[4]->setAttribute('created_at', '2001-01-01 00:00:00'),
            fn ($q) => $q[4]->setAttribute('level', 'B1'),
            fn ($q) => $q[4]->setAttribute('type', '1'),
            fn ($q) => $q[4]->setAttribute('seeder', 'ForeignBank'),
            fn ($q) => $q[2]->answers[0]->option->setAttribute('option', 'Changed'),
            fn ($q) => $q[1]->options[0]->pivot->setAttribute('flag', 1),
            static function ($q): void {
                $hint = new QuestionHint;
                $hint->setRawAttributes(['id' => 94000, 'question_id' => $q[3]->id, 'provider' => 'synthetic',
                    'locale' => 'uk', 'hint' => 'Інша підказка.'], true);
                $q[3]->setRelation('hints', new Collection([$hint]));
            },
            static function ($q): void {
                $hint = new VerbHint;
                $hint->setRawAttributes(['id' => 94001, 'question_id' => $q[3]->id], true);
                $q[3]->setRelation('verbHints', new Collection([$hint]));
            },
            fn ($q) => $q[3]->unsetRelation('hints'),
            fn ($q) => $q[3]->unsetRelation('verbHints'),
            fn ($q) => $q[0]->options[0]->unsetRelation('pivot'),
            fn ($q) => $q[1]->answers[0]->unsetRelation('option'),
            fn ($q) => $q[4]->setAttribute('unreviewed_field', true),
        ];
        foreach ($changes as $change) {
            [$questions, $target] = $this->synthetic(); $change($questions); $before = $questions->toArray();
            self::assertFalse($method->invoke(null, $questions, $target)); self::assertSame($before, $questions->toArray());
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_public_bindings_fall_back_for_short_duplicate_foreign_or_stale_collections(): void
    {
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        $block = $this->block(0); [$questions] = $this->synthetic();
        $cases = [new Collection, $questions->take(4), new Collection([$questions[0], $questions[0], $questions[2], $questions[3], $questions[4]]), $questions];
        foreach ($cases as $pool) {
            $original = $pool->toArray();
            foreach ([false, true] as $canonical) {
                $bindings = ['block' => $block, 'theoryCanonical' => $canonical, 'practiceQuestions' => $pool, 'unrelated' => 'retained'];
                self::assertSame($bindings, Presentation::bindings($bindings));
                self::assertSame($original, $pool->toArray());
            }
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }
}
