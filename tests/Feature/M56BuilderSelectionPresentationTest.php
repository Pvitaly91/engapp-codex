<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Support\ComposeTokenCase;
use App\Support\M26DetailPackage;
use App\Support\M56BuilderSelectionPresentation as Presentation;
use App\Support\PracticeCompletionLinkedPractice;
use App\Support\TheorySentenceBuilderAcceptance;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Independent captured bank rows; no live database, network, user progress or visual source writes. */
class M56BuilderSelectionPresentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); app()->setLocale('uk'); $this->withoutVite();
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
    }

    private function audit(): array
    {
        self::assertSame(Presentation::AUDIT_SHA, hash_file('sha256', base_path(Presentation::AUDIT)));
        return json_decode(file_get_contents(base_path(Presentation::AUDIT)), true, flags: JSON_THROW_ON_ERROR);
    }

    private function block(int $index = 0): TextBlock
    {
        $audit = $this->audit(); $binding = $audit['practice_bindings'][$index];
        $patch = json_decode(file_get_contents(database_path('content-patches/m56-gerund-simple-passives.v1.json')), true, flags: JSON_THROW_ON_ERROR);
        $target = $patch['targets'][$index]; $slot = $binding['source_index']; $source = $target['after']['page']['blocks'][$slot];
        self::assertSame($binding['body_sha256'], hash('sha256', $source['body']));
        self::assertSame($binding['uuid'], M26DetailPackage::uuid($target['identity'], $source, $slot + 1));
        $block = new TextBlock;
        $block->setRawAttributes(['id' => 5600000 + $index, 'uuid' => $binding['uuid'],
            'page_id' => $binding['page_id'], 'page_category_id' => $binding['category_id'],
            'seeder' => $target['identity'], 'locale' => 'uk', 'type' => 'practice-set', 'level' => $binding['level'],
            'sort_order' => $slot + 1, 'column' => $source['column'], 'body' => $source['body'],
            'heading' => $source['heading'] ?? null, 'css_class' => $source['css_class'] ?? null], true);
        $block->setRelation('tags', new ModelCollection); $block->setRelation('page', null);
        return $block;
    }

    private function config(): array
    {
        return ['source' => 'theory_links', 'question_types' => ['4'],
            'seeder_classes' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoicePassiveGerundAllLevelsLessonSeeder']];
    }

    private function questions(array $target): ModelCollection
    {
        $options = []; $questions = [];
        foreach ($target['first_five'] as $sample) {
            foreach ($sample['original_options_snapshot'] as $row) {
                if (!isset($options[$row['id']])) {
                    $option = new QuestionOption; $option->setRawAttributes($row, true); $options[$row['id']] = $option;
                }
                self::assertSame($row['option'], $options[$row['id']]->option);
            }
        }
        foreach ($target['first_five'] as $sample) {
            $metadata = json_encode(array_fill_keys(array_keys($sample['answers']), $sample['source_options']),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            self::assertSame($sample['question_options_by_marker_sha256'], hash('sha256', $metadata));
            $question = new Question;
            $question->setRawAttributes(['id' => $sample['id'], 'uuid' => $sample['uuid'], 'question' => $sample['condition_uk'],
                'seeder' => $target['bank_seeders'][0], 'type' => '4', 'level' => $target['level'], 'options_by_marker' => $metadata], true);
            $answers = [];
            foreach ($sample['ordered_answer_snapshot'] as $row) {
                $answer = new QuestionAnswer; $answer->setRawAttributes(['id' => $row['id'], 'question_id' => $sample['id'],
                    'marker' => $row['marker'], 'position' => $row['position'], 'option_id' => $row['option_id']], true);
                self::assertSame($row['option'], $options[$row['option_id']]->option);
                $answer->setRelation('option', $options[$row['option_id']]); $answers[] = $answer;
            }
            $question->setRelation('answers', new ModelCollection($answers));
            $question->setRelation('options', new ModelCollection(array_map(static fn ($row) => $options[$row['id']], $sample['original_options_snapshot'])));
            foreach (['tags', 'hints', 'verbHints', 'markerTags'] as $relation) { $question->setRelation($relation, new ModelCollection); }
            $questions[] = $question;
        }
        return new ModelCollection($questions);
    }

    public function test_only_exact_canonical_matching_clones_gain_reviewed_exclusions_without_queries_or_mutation(): void
    {
        $block = $this->block(); $original = $block->toArray(); $config = $this->config();
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        self::assertSame([999], Presentation::exclusions($block, $config, [999]));
        self::assertSame($block, PracticeCompletionLinkedPractice::matchingBlock($block, false));
        $copy = PracticeCompletionLinkedPractice::matchingBlock($block, true);
        self::assertNotSame($block, $copy); self::assertSame($original, $copy->toArray());
        self::assertSame([999, 999, 39124, 39127, 39129], Presentation::exclusions($copy, $config, [999, 999]));
        self::assertSame([39124, 999, 39127, 39129], Presentation::exclusions($copy, $config, [39124, 999]));
        self::assertSame([39123, 999], Presentation::exclusions($copy, $config, [39123, 999]));
        self::assertSame([999], Presentation::exclusions(clone $copy, $config, [999]));
        self::assertSame([999], Presentation::exclusions($block, $config, [999]));
        self::assertSame($copy, PracticeCompletionLinkedPractice::matchingBlock($copy, false));
        self::assertSame([999], Presentation::exclusions($copy, $config, [999]));
        self::assertSame($original, $block->toArray()); self::assertSame([], $block->getDirty());
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_foreign_stale_and_tampered_clones_configs_or_locales_get_no_selection_projection(): void
    {
        $block = $this->block(); $config = $this->config();
        $mutations = ['seeder' => 'ForeignOwner', 'page_id' => 113, 'page_category_id' => 1, 'locale' => 'en',
            'level' => 'A2', 'type' => 'usage-panels', 'uuid' => 'foreign', 'sort_order' => 999, 'column' => 'header',
            'heading' => 'foreign', 'css_class' => 'foreign', 'body' => $block->body."\n"];
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        foreach ($mutations as $field => $value) {
            $bad = clone $block; $bad->$field = $value; $before = $bad->toArray();
            self::assertSame($bad, Presentation::matchingBlock($bad, true), $field);
            $marked = Presentation::matchingBlock($block, true); $marked->$field = $value;
            self::assertSame([999], Presentation::exclusions($marked, $config, [999]), 'recheck '.$field);
            self::assertSame($before, $bad->toArray(), 'unchanged '.$field);
        }
        $copy = Presentation::matchingBlock($block, true);
        foreach ([[], array_replace($config, ['source' => 'tags']), array_replace($config, ['question_types' => ['0', '4']]),
            array_replace($config, ['seeder_classes' => ['ForeignBank']]), $config + ['question_ids' => [39123]]] as $bad) {
            self::assertSame([999], Presentation::exclusions($copy, $bad, [999]));
        }
        foreach (['en', 'pl'] as $locale) {
            app()->setLocale($locale); self::assertSame($block, Presentation::matchingBlock($block, true));
            self::assertSame([999], Presentation::exclusions($copy, $config, [999]));
        }
        app()->setLocale('uk');
        foreach ([1, 2] as $i) {
            $other = $this->block($i); self::assertSame($other, Presentation::matchingBlock($other, true));
            self::assertSame([999], Presentation::exclusions($other, $config, [999]));
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_missing_or_drifted_independent_contract_refuses_projection_only_in_an_owned_temporary_base_path(): void
    {
        $block = $this->block(); $audit = $this->audit(); $root = base_path();
        $auditBytes = file_get_contents(base_path(Presentation::AUDIT));
        $relativeContract = $audit['builder_contract']['path'];
        $contractBytes = file_get_contents(base_path($relativeContract));
        $temporary = sys_get_temp_dir().'/gramlyze-m56-builder-'.bin2hex(random_bytes(8));
        $auditPath = $temporary.'/'.Presentation::AUDIT;
        $contractPath = $temporary.'/'.$relativeContract;
        mkdir(dirname($contractPath), 0700, true);
        try {
            file_put_contents($auditPath, $auditBytes); file_put_contents($contractPath, $contractBytes);
            app()->setBasePath($temporary);
            $valid = Presentation::matchingBlock($block, true);
            self::assertNotSame($block, $valid);
            unlink($contractPath);
            self::assertSame($block, Presentation::matchingBlock($block, true));
            self::assertSame([999], Presentation::exclusions($valid, $this->config(), [999]));
            file_put_contents($contractPath, $contractBytes."\n");
            self::assertSame($block, Presentation::matchingBlock($block, true));
            self::assertSame([999], Presentation::exclusions($valid, $this->config(), [999]));
            file_put_contents($contractPath, $contractBytes);
            file_put_contents($auditPath, $auditBytes."\n");
            self::assertSame($block, Presentation::matchingBlock($block, true));
        } finally {
            app()->setBasePath($root);
            if (is_file($contractPath)) { unlink($contractPath); }
            if (is_file($auditPath)) { unlink($auditPath); }
            rmdir($temporary.'/docs/content/theory-sentence-builders');
            rmdir($temporary.'/docs/content'); rmdir($temporary.'/docs'); rmdir($temporary);
        }
    }

    public function test_all_five_snapshots_are_verified_before_four_contexts_are_cloned_and_original_relations_stay_exact(): void
    {
        $audit = $this->audit(); $questions = $this->questions($audit['targets'][0]); $original = $questions->toArray();
        $binding = ['block' => $this->block(), 'theoryCanonical' => true, 'practiceQuestions' => $questions];
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        $projected = Presentation::bindings($binding)['practiceQuestions']; self::assertNotSame($questions, $projected);
        foreach ($questions as $i => $question) {
            self::assertSame($audit['targets'][0]['first_five'][$i]['visible_condition_uk'], $projected[$i]->question);
            $expected = $question->getAttributes(); $expected['question'] = $projected[$i]->question;
            self::assertSame($expected, $projected[$i]->getAttributes()); self::assertSame([], $projected[$i]->getDirty());
            if ($question->id === 39125) { self::assertSame($question, $projected[$i]); }
            else { self::assertNotSame($question, $projected[$i]); self::assertStringEndsWith($question->question, $projected[$i]->question); }
            foreach (['answers', 'options', 'hints', 'verbHints', 'tags', 'markerTags'] as $relation) {
                self::assertSame($question->getRelation($relation), $projected[$i]->getRelation($relation));
            }
        }
        self::assertSame($original, $questions->toArray());
        $bad = $binding; $bad['theoryCanonical'] = false; self::assertSame($bad, Presentation::bindings($bad));
        unset($bad['theoryCanonical']); self::assertSame($bad, Presentation::bindings($bad));
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_one_changed_question_or_snapshot_refuses_all_contexts_before_any_model_mutation(): void
    {
        $audit = $this->audit(); $target = $audit['targets'][0]; $projection = $audit['condition_projection'];
        $method = new ReflectionMethod(Presentation::class, 'questions');
        $mutations = [
            'id' => fn ($q) => $q[4]->id = 999, 'uuid' => fn ($q) => $q[4]->uuid = 'foreign',
            'bank' => fn ($q) => $q[4]->seeder = 'ForeignBank', 'type' => fn ($q) => $q[4]->type = '0',
            'level' => fn ($q) => $q[4]->level = 'B1', 'condition' => fn ($q) => $q[4]->question .= ' Зміна.',
            'unloaded-answers' => fn ($q) => $q[4]->unsetRelation('answers'),
            'unloaded-options' => fn ($q) => $q[4]->unsetRelation('options'),
            'unloaded-hints' => fn ($q) => $q[4]->unsetRelation('hints'),
            'unloaded-verb-hints' => fn ($q) => $q[4]->unsetRelation('verbHints'),
            'answer-id' => fn ($q) => $q[4]->answers[0]->id = 999,
            'answer-question' => fn ($q) => $q[4]->answers[0]->question_id = 999,
            'answer-marker' => fn ($q) => $q[4]->answers[0]->marker = 'a99',
            'answer-option-id' => fn ($q) => $q[4]->answers[0]->option_id = 999,
            'answer-option-value' => fn ($q) => $q[4]->answers[0]->option->option = 'Foreign',
            'unloaded-option' => fn ($q) => $q[4]->answers[0]->unsetRelation('option'),
            'shared-option-value' => fn ($q) => $q[4]->options[0]->option = 'Foreign',
            'shared-option-id' => fn ($q) => $q[4]->options[0]->id = 999,
            'raw-marker-options' => fn ($q) => $q[4]->setRawAttributes(array_replace($q[4]->getAttributes(), ['options_by_marker' => '{}']), true),
            'nonempty-hints' => fn ($q) => $q[4]->setRelation('hints', new ModelCollection([new QuestionHint])),
            'nonempty-verb-hints' => fn ($q) => $q[4]->setRelation('verbHints', new ModelCollection([new QuestionAnswer])),
        ];
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        foreach ($mutations as $name => $mutation) {
            $questions = $this->questions($target); $mutation($questions); $before = $questions->toArray();
            self::assertNull($method->invoke(null, $questions, $target, $projection), $name);
            self::assertSame($before, $questions->toArray(), $name.' cannot partially prefix earlier questions');
        }
        $questions = $this->questions($target);
        foreach ([new ModelCollection($questions->take(4)->all()), new ModelCollection([$questions[1], $questions[0], $questions[2], $questions[3], $questions[4]]),
            new ModelCollection([$questions[0], $questions[0], $questions[2], $questions[3], $questions[4]])] as $bad) {
            self::assertNull($method->invoke(null, $bad, $target, $projection));
        }
        foreach (['foreign-record', 'changed-original', 'empty-prefix', 'html-prefix', 'changed-answer', 'changed-output', 'short-records'] as $case) {
            $bad = $projection; $badTarget = $target;
            if ($case === 'foreign-record') { $bad['records'][3]['question_id'] = 999; }
            if ($case === 'changed-original') { $bad['records'][3]['before_condition_uk'] = 'Інша умова.'; }
            if ($case === 'empty-prefix') { $bad['records'][3]['context_prefix_uk'] = ''; }
            if ($case === 'html-prefix') { $bad['records'][3]['context_prefix_uk'] = '<b>Префікс</b>'; }
            if ($case === 'changed-answer') { $badTarget['first_five'][4]['ordered_answer_snapshot'][0]['option'] = 'Foreign'; }
            if ($case === 'changed-output') { $bad['records'][3]['condition_uk'] = 'Інший зміст.'; }
            if ($case === 'short-records') { array_pop($bad['records']); }
            self::assertNull($method->invoke(null, $questions, $badTarget, $bad), $case);
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_unchanged_native_widget_has_fifteen_strict_keys_and_token_multiplicity_without_a_new_case_map(): void
    {
        $audit = $this->audit();
        foreach ($audit['targets'] as $i => $target) {
            $questions = $this->questions($target); $original = $questions->toArray(); $block = $this->block($i);
            $before = view('components.text-block-practice-questions', ['questions' => $questions, 'blockUuid' => $block->uuid])->render();
            $bindings = ['block' => $block, 'theoryCanonical' => true, 'practiceQuestions' => $questions];
            $projected = Presentation::bindings($bindings)['practiceQuestions'];
            $after = view('components.text-block-practice-questions', ['questions' => $projected, 'blockUuid' => $block->uuid])->render();
            $old = TheorySentenceBuilderAcceptance::payload($before, $block->uuid);
            $new = TheorySentenceBuilderAcceptance::payload($after, $block->uuid);
            self::assertCount(5, $new); self::assertSame($target['expected_existing_selected_ids'], array_column($new, 'id'));
            foreach ($new as $n => $payload) {
                $sample = $target['first_five'][$n];
                self::assertSame($sample['visible_condition_uk'], $payload['question']);
                self::assertSame($sample['correct_tokens'], $payload['correct_tokens']);
                self::assertSame($sample['correct_tokens'], ComposeTokenCase::normalize(array_column($sample['ordered_answer_snapshot'], 'option')));
                foreach (array_count_values($sample['correct_tokens']) as $token => $count) {
                    self::assertGreaterThanOrEqual($count, array_count_values($payload['options'])[$token] ?? 0, $sample['uuid'].' '.$token);
                }
                $a = $old[$n]; $b = $payload; unset($a['question'], $b['question']); self::assertSame($a, $b);
                self::assertSame([], $payload['hints']); self::assertSame([], $payload['verb_hints']);
            }
            self::assertSame(1, substr_count($after, 'data-sentence-builder')); self::assertSame($original, $questions->toArray());
            if ($i > 0) { self::assertSame($questions, $projected); self::assertSame($before, $after); }
        }
        $renderer = file_get_contents(base_path('app/Support/TheoryContentRenderer.php'));
        self::assertSame(1, substr_count($renderer, 'M56BuilderSelectionPresentation::bindings($bindings)'));
        self::assertLessThan(strpos($renderer, 'M56BuilderSelectionPresentation::bindings($bindings)'), strpos($renderer, 'M55BuilderContextPresentation::bindings($bindings)'));
        $seam = file_get_contents(base_path('app/Support/PracticeCompletionLinkedPractice.php'));
        self::assertSame(1, substr_count($seam, 'M56BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory)'));
        $matcher = file_get_contents(base_path('app/Services/Theory/TextBlockToQuestionsMatcherService.php'));
        self::assertSame(1, substr_count($matcher, 'M56BuilderSelectionPresentation::exclusions($block, $linkedPractice, $excludeQuestionIds)'));
        self::assertStringContainsString("->where('questions.level', \$block->level)", $matcher);
    }
}
