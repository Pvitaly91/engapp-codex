<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M54BuilderTokenPresentation as Presentation;
use App\Support\M54PassivePatternsPackage as Package;
use App\Support\TheoryContentRenderer;
use App\Support\TheorySentenceBuilderAcceptance;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Pure model/render fixtures from independent BEFORE; no working .env, MySQL, HTTP or progress. */
class M54BuilderTokenPresentationTest extends TestCase
{
    /** Original shared option rows, including their historical spelling; not corrected AFTER fixtures. */
    private const OPTIONS_JSON = <<<'JSON'
{"2":"have","6":"has","8":"the","9":"She","11":"a","12":"letter","14":"We","18":"He","22":"to","23":"been","26":"was","27":"in","34":"this","41":"not","49":"did","55":"my","57":"yesterday","69":"her","71":"were","79":"two","80":"hours","84":"do","88":"will","94":"would","96":"am","101":"are","107":"is","114":"new","127":"does","156":"by","157":"train","178":"meeting","179":"should","182":"must","183":"get","187":"his","196":"up","198":"late","207":"be","218":"can","235":"for","243":"off","297":"after","323":"with","351":"slept","352":"well","367":"sister","378":"old","389":"winter","430":"bag","436":"got","458":"had","529":"could","532":"him","587":"quickly","637":"being","1619":"politely","2464":"down","2467":"until","2539":"arrived","2549":"children","2550":"sent","2553":"put","2588":"may","2611":"turned","2815":"looked","2883":"main","2910":"might","3079":"accident","3119":"baby","3143":"complaint","3541":"hall","4025":"gets","5071":"tree","5464":"happened","6260":"Given","6266":"Brought","7075":"Friday","7312":"grandparents","7380":"chance","15886":"belongs","17177":"request","21573":"died","23475":".","23724":"shown","23797":"dealt"}
JSON;
    private const QUESTION_OPTIONS_JSON = <<<'JSON'
{"37815":[2,6,9,11,22,23,26,41,49,71,84,88,94,96,101,107,114,127,156,179,182,183,207,218,323,436,458,529,637,2588,2910,4025,6260,7380,23475],"37816":[2,6,11,22,23,26,41,49,69,71,84,88,94,96,101,107,114,127,156,179,182,183,207,218,235,323,436,458,529,637,2588,2910,4025,6260,7380,23475],"37817":[2,6,11,12,18,22,23,26,41,49,71,84,88,94,96,101,107,127,156,179,182,183,207,218,323,436,458,529,637,2550,2588,2910,4025,23475],"37818":[2,6,11,12,22,23,26,41,49,71,84,88,94,96,101,107,127,156,179,182,183,207,218,235,323,436,458,529,532,637,2550,2588,2910,4025,23475],"37819":[2,6,8,14,22,23,26,41,49,71,84,88,94,96,101,107,127,156,179,182,183,207,218,323,436,458,529,637,2588,2883,2910,3541,4025,23475,23724],"37887":[2,6,8,22,23,26,41,49,71,84,88,94,96,101,107,127,156,179,182,183,207,218,297,323,352,436,458,529,637,2549,2588,2815,2910,4025,23475],"37888":[2,6,8,22,23,26,41,49,71,84,88,94,96,101,107,127,156,178,179,182,183,207,218,243,323,436,458,529,637,2467,2553,2588,2910,4025,7075,23475],"37889":[2,6,9,22,23,26,41,49,69,71,84,88,94,96,101,107,127,156,179,182,183,196,207,218,235,323,436,458,529,637,2588,2910,4025,6266,7312,23475],"37890":[2,6,22,23,26,41,49,71,84,88,94,96,101,107,127,156,179,182,183,187,207,218,323,436,458,529,637,1619,2464,2588,2611,2910,4025,17177,23475],"37891":[2,6,8,22,23,26,41,49,71,84,88,94,96,101,107,127,156,179,182,183,207,218,235,323,436,458,529,587,637,2588,2910,3143,4025,23475,23797],"37959":[2,6,8,23,26,41,49,57,71,84,88,94,96,101,107,127,156,179,182,183,207,218,323,436,458,529,637,2588,2910,3079,4025,5464,23475],"37960":[2,6,8,23,26,41,49,71,84,88,94,96,101,107,127,156,157,179,182,183,198,207,218,323,436,458,529,637,2539,2588,2910,4025,23475],"37961":[2,6,8,23,26,41,49,71,79,80,84,88,94,96,101,107,127,156,179,182,183,207,218,235,323,351,436,458,529,637,2588,2910,3119,4025,23475],"37962":[2,6,8,23,26,27,41,49,71,84,88,94,96,101,107,127,156,179,182,183,207,218,323,378,389,436,458,529,637,2588,2910,4025,5071,21573,23475],"37963":[2,6,22,23,26,34,41,49,55,71,84,88,94,96,101,107,127,156,179,182,183,207,218,323,367,430,436,458,529,637,2588,2910,4025,15886,23475]}
JSON;

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

    private function questions(array $target): ModelCollection
    {
        $values = json_decode(self::OPTIONS_JSON, true, flags: JSON_THROW_ON_ERROR);
        $ids = json_decode(self::QUESTION_OPTIONS_JSON, true, flags: JSON_THROW_ON_ERROR); $options = [];
        foreach ($values as $id => $value) {
            $option = new QuestionOption; $option->setRawAttributes(['id' => (int) $id, 'option' => $value], true); $options[$id] = $option;
        }
        $out = [];
        foreach ($target['first_five'] as $sample) {
            $question = new Question;
            $metadata = json_encode(array_fill_keys(array_keys($sample['answers']), $sample['source_options']),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            self::assertSame($sample['question_options_by_marker_sha256'], hash('sha256', $metadata));
            $question->setRawAttributes(['id' => $sample['id'], 'uuid' => $sample['uuid'], 'question' => $sample['condition_uk'],
                'seeder' => $target['bank_seeders'][0], 'type' => '4', 'level' => 'B1', 'options_by_marker' => $metadata], true);
            $answers = [];
            foreach ($sample['ordered_answer_snapshot'] as $row) {
                $answer = new QuestionAnswer; $answer->setRawAttributes(['id' => $row['id'], 'question_id' => $sample['id'],
                    'marker' => $row['marker'], 'position' => $row['position'], 'option_id' => $row['option_id']], true);
                self::assertSame($row['option'], $options[$row['option_id']]->option);
                $answer->setRelation('option', $options[$row['option_id']]); $answers[] = $answer;
            }
            $question->setRelation('answers', new ModelCollection($answers));
            $question->setRelation('options', new ModelCollection(array_map(static fn ($id) => $options[$id], $ids[$sample['id']])));
            foreach (['tags', 'hints', 'verbHints', 'markerTags'] as $relation) { $question->setRelation($relation, new ModelCollection); }
            $out[] = $question;
        }
        return new ModelCollection($out);
    }

    private function binding(ModelCollection $questions, int $i): array
    {
        [, $patch] = Package::load(); $target = $patch['targets'][$i]; $slot = count($target['after']['page']['blocks']) - 1;
        $source = $target['after']['page']['blocks'][$slot]; $block = new TextBlock;
        $block->setRawAttributes(['id' => 5400000 + $i, 'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => 1, 'seeder' => $target['identity'], 'locale' => 'uk',
            'type' => 'practice-set', 'level' => 'B1', 'column' => $source['column'], 'sort_order' => $slot + 1,
            'body' => $source['body'], 'heading' => $source['heading'] ?? null, 'css_class' => $source['css_class'] ?? null], true);
        $block->setRelation('tags', new ModelCollection); $block->setRelation('page', null);
        $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR); $shown = Package::presentation($block, $data);
        self::assertNotNull($shown);
        return ['block' => $block, 'data' => $data, 'theoryCanonical' => true, 'practiceQuestions' => $questions,
            'lessonLinks' => [], 'm42Design' => $shown['m42_native_design']];
    }

    private function project(ModelCollection $questions, array $target): mixed
    {
        return (new ReflectionMethod(Presentation::class, 'questions'))->invoke(null, $questions, $target);
    }

    private function freshCopy(ModelCollection $questions): ModelCollection
    {
        return $questions->map(static function (Question $question): Question {
            $copy = clone $question;
            $copy->setRelation('answers', $question->answers->map(static function (QuestionAnswer $answer): QuestionAnswer {
                $clone = clone $answer; $clone->setRelation('option', clone $answer->getRelation('option')); return $clone;
            }));
            $copy->setRelation('options', $question->options->map(static fn ($option) => clone $option));
            return $copy;
        });
    }

    public function test_only_three_reviewed_instances_change_in_clones_and_original_shared_rows_are_untouched_without_queries(): void
    {
        $audit = $this->audit(); $changed = [];
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        foreach ($audit['targets'] as $i => $target) {
            $questions = $this->questions($target); $before = $questions->toArray(); $bindings = $this->binding($questions, $i);
            $after = Presentation::bindings($bindings); $projected = $after['practiceQuestions'];
            self::assertCount(5, $projected);
            foreach ($questions as $n => $question) {
                $copy = $projected[$n];
                self::assertSame($question->getAttributes(), $copy->getAttributes());
                foreach (['options', 'hints', 'tags', 'verbHints', 'markerTags'] as $relation) {
                    self::assertSame($question->getRelation($relation), $copy->getRelation($relation));
                }
                foreach ($question->answers as $a => $answer) {
                    $new = $copy->answers[$a];
                    self::assertSame($answer->getAttributes(), $new->getAttributes());
                    if ($answer->option->option !== $new->option->option) {
                        self::assertNotSame($question, $copy); self::assertNotSame($answer, $new); self::assertNotSame($answer->option, $new->option);
                        self::assertSame($answer->option_id, $new->option->id);
                        self::assertSame(mb_strtolower($answer->option->option), mb_strtolower($new->option->option));
                        self::assertSame([], $new->getDirty()); self::assertSame([], $new->option->getDirty());
                        $changed[] = [$question->id, $answer->marker, $answer->option->option, $new->option->option];
                    } else { self::assertSame($answer, $new); }
                }
            }
            self::assertSame($before, $questions->toArray());
            if ($i === 1) { self::assertSame($bindings, $after, 'Restrictions needs no token case restoration'); }
        }
        self::assertSame([[37889, 'a3', 'Brought', 'brought'], [37815, 'a3', 'Given', 'given'], [37816, 'a5', 'Given', 'given']], $changed);
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_foreign_question_answer_option_metadata_or_pool_refuses_entire_projection_before_any_clone_or_query(): void
    {
        $target = $this->audit()['targets'][2]; $questions = $this->questions($target);
        $mutations = [
            'id' => fn ($q) => $q[4]->id = 999, 'uuid' => fn ($q) => $q[4]->uuid = 'foreign',
            'bank' => fn ($q) => $q[4]->seeder = 'ForeignBank', 'type' => fn ($q) => $q[4]->type = '0',
            'level' => fn ($q) => $q[4]->level = 'B2', 'condition' => fn ($q) => $q[4]->question .= ' Зміна.',
            'unloaded-answers' => fn ($q) => $q[4]->unsetRelation('answers'),
            'unloaded-options' => fn ($q) => $q[4]->unsetRelation('options'),
            'unloaded-answer-option' => fn ($q) => $q[4]->answers[0]->unsetRelation('option'),
            'answer-id' => fn ($q) => $q[4]->answers[0]->id = 999,
            'answer-question' => fn ($q) => $q[4]->answers[0]->question_id = 999,
            'answer-marker' => fn ($q) => $q[4]->answers[0]->marker = 'a99',
            'answer-option-id' => fn ($q) => $q[4]->answers[0]->option_id = 999,
            'answer-option-value' => fn ($q) => $q[4]->answers[0]->option->option = 'foreign',
            'shared-option-value' => fn ($q) => $q[4]->options[0]->option = 'foreign',
            'shared-option-id' => fn ($q) => $q[4]->options[0]->id = 999,
            'stale-options-by-marker' => fn ($q) => $q[4]->setRawAttributes(array_replace($q[4]->getAttributes(), ['options_by_marker' => '{}']), true),
        ];
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        foreach ($mutations as $name => $mutation) {
            $bad = $this->freshCopy($questions); $mutation($bad); $before = $bad->toArray();
            self::assertNull($this->project($bad, $target), $name);
            self::assertSame($before, $bad->toArray(), $name.' must not partially fix earlier Given instances');
        }
        foreach ([new ModelCollection($questions->take(4)->all()), new ModelCollection([$questions[1], $questions[0], $questions[2], $questions[3], $questions[4]]),
            new ModelCollection([$questions[0], $questions[0], $questions[2], $questions[3], $questions[4]])] as $bad) {
            self::assertNull($this->project($bad, $target));
        }
        foreach (['meaning', 'unreviewed-case', 'stale-snapshot'] as $case) {
            $bad = $target;
            if ($case === 'meaning') { $bad['first_five'][0]['correct_tokens'][2] = 'taken'; }
            if ($case === 'unreviewed-case') { $bad['first_five'][0]['correct_tokens'][2] = 'gIVEN'; }
            if ($case === 'stale-snapshot') { $bad['first_five'][0]['ordered_answer_snapshot'][0]['option'] = 'Foreign'; }
            self::assertNull($this->project($questions, $bad), $case);
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_only_exact_frozen_canonical_uk_m54_practice_binding_receives_the_projection(): void
    {
        $audit = $this->audit();
        foreach ($audit['targets'] as $i => $target) {
            $bindings = $this->binding($this->questions($target), $i);
            foreach (['seeder' => 'ForeignOwner', 'page_id' => 92, 'page_category_id' => 999, 'locale' => 'en', 'level' => 'B2',
                'type' => 'usage-panels', 'uuid' => 'foreign', 'sort_order' => 999, 'column' => 'header',
                'heading' => 'foreign', 'body' => $bindings['block']->body."\n"] as $field => $value) {
                $bad = $bindings; $bad['block'] = clone $bindings['block']; $bad['block']->$field = $value;
                self::assertSame($bad, Presentation::bindings($bad), $field);
            }
            foreach (['en', 'pl'] as $locale) {
                app()->setLocale($locale); self::assertSame($bindings, Presentation::bindings($bindings));
            }
            app()->setLocale('uk'); $bad = $bindings; $bad['theoryCanonical'] = false; self::assertSame($bad, Presentation::bindings($bad));
            unset($bad['theoryCanonical']); self::assertSame($bad, Presentation::bindings($bad));
            $bad = $bindings; $bad['practiceQuestions'] = $bindings['practiceQuestions']->take(4);
            self::assertSame($bad, Presentation::bindings($bad));
        }
        $source = file_get_contents(base_path('app/Support/TheoryContentRenderer.php'));
        self::assertSame(1, substr_count($source, 'M54BuilderTokenPresentation::bindings($bindings)'));
        self::assertLessThan(strpos($source, 'M54BuilderTokenPresentation::bindings($bindings)'), strpos($source, "if ((\$bindings['theoryCanonical'] ?? false) !== true)"));
        self::assertLessThan(strpos($source, 'M54BuilderTokenPresentation::bindings($bindings)'), strpos($source, 'M53BuilderHintPresentation::bindings($bindings)'));
    }

    public function test_unchanged_native_widget_has_exact_authored_tokens_but_identical_casefold_grading_data_and_non_theory_payload(): void
    {
        $audit = $this->audit();
        foreach ($audit['targets'] as $i => $target) {
            $questions = $this->questions($target); $original = $questions->toArray(); $bindings = $this->binding($questions, $i);
            $before = view('engram.theory.blocks-v3.practice-set', $bindings)->render();
            $after = TheoryContentRenderer::render('engram.theory.blocks-v3.practice-set', $bindings);
            $old = TheorySentenceBuilderAcceptance::payload($before, $bindings['block']->uuid);
            $new = TheorySentenceBuilderAcceptance::payload($after, $bindings['block']->uuid);
            self::assertCount(5, $new); self::assertSame($target['expected_existing_selected_ids'], array_column($new, 'id'));
            foreach ($new as $n => $question) {
                self::assertSame($target['first_five'][$n]['correct_tokens'], $question['correct_tokens']);
                foreach (['correct_tokens', 'options'] as $field) {
                    self::assertSame(array_map(mb_strtolower(...), $old[$n][$field]), array_map(mb_strtolower(...), $question[$field]));
                }
                $a = $old[$n]; $b = $question; unset($a['answers'], $a['correct_tokens'], $a['options'], $b['answers'], $b['correct_tokens'], $b['options']);
                self::assertSame($a, $b);
                self::assertSame($old[$n]['question'], $question['question']);
            }
            $excluded = $bindings; $excluded['theoryCanonical'] = false;
            $untouched = TheorySentenceBuilderAcceptance::payload(TheoryContentRenderer::render('engram.theory.blocks-v3.practice-set', $excluded), $bindings['block']->uuid);
            self::assertSame($old, $untouched); self::assertSame($original, $questions->toArray());
            self::assertSame(1, substr_count($after, 'data-sentence-builder'));
        }
    }
}
