<?php

namespace Tests\Unit;

use App\Http\Controllers\GrammarTestController;
use App\Http\Controllers\TestJsV2Controller;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\Test;
use App\Models\VerbHint;
use App\Services\GrammarTestFilterService;
use App\Services\MarkerTheoryMatcherService;
use App\Services\PolyglotCourseManifestService;
use App\Services\PolyglotLessonDebugPayloadBuilder;
use App\Services\QuestionDeletionService;
use App\Services\QuestionTechnicalInfoService;
use App\Services\QuestionVariantService;
use App\Services\ResolvedSavedTest;
use App\Services\SavedTestResolver;
use App\Services\TagAggregationService;
use App\Services\TheoryBlockMatcherService;
use App\Support\LocalizedComposeText;
use App\Support\SentenceReorderQuestionFactory;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Exercise the real initial/fresh payload serializers with in-memory relations.
 * No seeder, schema rebuild, or application-database write is required.
 */
class PastPerfectContinuousBuilderPayloadTest extends TestCase
{
    private const TOKENS = [
        'She', 'had', 'been', 'reading', 'while', 'I', 'had', 'been',
        'cooking', 'before', 'dinner', 'started.',
    ];

    private const SOURCES = [
        'uk' => 'Перед вечерею вона читала, а я готував. Збери речення в заданому порядку: її дія, моя дія, минула подія.',
        'en' => 'Before dinner she was reading and I was cooking. Build the sentence in the given order: her activity, my activity, the past event.',
        'pl' => 'Przed kolacją ona czytała, a ja gotowałem. Ułóż zdanie w podanej kolejności: jej czynność, moja czynność, wydarzenie z przeszłości.',
    ];

    private const HINTS = [
        'uk' => 'Визнач тривалу дію перед минулою подією.',
        'en' => 'Identify the ongoing activity before the past event.',
        'pl' => 'Rozpoznaj czynność trwającą przed wydarzeniem z przeszłości.',
    ];

    public static function serializerLocales(): array
    {
        $cases = [];
        foreach ([TestJsV2Controller::class => 'html-initial', GrammarTestController::class => 'fresh-json'] as $controller => $path) {
            foreach (['uk', 'en', 'pl'] as $locale) {
                $cases[$path.'-'.$locale] = [$controller, $locale];
            }
        }

        return $cases;
    }

    #[DataProvider('serializerLocales')]
    public function test_opted_in_compose_sources_and_every_marker_hint_are_localized(string $controller, string $locale): void
    {
        $question = $this->question(true);
        $payload = $this->dataset($controller, $locale, [$question])[0];

        $this->assertSame(self::SOURCES[$locale], $payload['question']);
        $this->assertSame(self::SOURCES[$locale], $payload['compose_source_text']);
        $this->assertSame(self::TOKENS, $payload['answers']);
        $this->assertSame(array_combine($this->markers(), self::TOKENS), $payload['answer_map']);
        $this->assertSame($this->expectedHints($this->markers(), $locale), $payload['verb_hints']);
        $this->assertSame(self::HINTS[$locale].' [a1]', $payload['verb_hint']);
        $this->assertSame($this->markers(), $payload['markers']);
        $this->assertSame(12, $payload['markers_count']);
        $this->assertSame(2, count(array_keys($payload['answers'], 'had', true)));
        $this->assertSame(2, count(array_keys($payload['answers'], 'been', true)));
        $this->assertArrayNotHasKey('presentation', $payload);
        $this->assertSame(self::SOURCES['uk'], $question->question, 'Serialization must not mutate the raw source.');
        $this->assertSame(self::HINTS[$locale], LocalizedComposeText::hint($question));
    }

    #[DataProvider('serializerLocales')]
    public function test_finite_compose_serializers_restore_noninitial_when_without_mutating_shared_options(string $controller, string $locale): void
    {
        foreach ([true, false] as $optedIn) {
            $question = $this->question($optedIn);
            $question->answers->firstWhere('marker', 'a5')->option->option = 'When';
            $payload = $this->dataset($controller, $locale, [$question])[0];
            $expected = $optedIn ? 'when' : 'When';

            $this->assertSame($expected, $payload['answers'][4]);
            $this->assertSame($expected, $payload['answer_map']['a5']);
            $this->assertContains($expected, $payload['options']);
            $this->assertNotContains($optedIn ? 'When' : 'when', $payload['options']);
            $this->assertSame('When', $question->answers->firstWhere('marker', 'a5')->option->option);
        }
    }

    #[DataProvider('serializerLocales')]
    public function test_legacy_compose_questions_keep_the_raw_prompt_without_implicit_optin(string $controller, string $locale): void
    {
        $question = $this->question(false);
        $payload = $this->dataset($controller, $locale, [$question])[0];

        $this->assertSame(self::SOURCES['uk'], $payload['question']);
        $this->assertNull($payload['compose_source_text']);
        $this->assertSame(self::TOKENS, $payload['answers']);
        $this->assertSame($this->expectedHints($this->markers(), $locale), $payload['verb_hints']);
        $this->assertFalse(LocalizedComposeText::optedIn($question));
        $this->assertArrayNotHasKey('presentation', $payload);
    }

    #[DataProvider('serializerLocales')]
    public function test_reorder_template_constraint_requires_explicit_source_optin(string $controller, string $locale): void
    {
        foreach ([true, false] as $optedIn) {
            $question = $this->question($optedIn, false);
            $payload = $this->dataset($controller, $locale, [$question], true)[0];

            $this->assertSame('He {a1} for two hours before the rain {a2}.', $payload['question']);
            $this->assertSame($optedIn ? self::SOURCES[$locale] : null, $payload['compose_source_text']);
            $this->assertSame(SentenceReorderQuestionFactory::PRESENTATION, $payload['presentation']);
            $this->assertSame($optedIn, $payload['reorder_template_constraint']);
            $this->assertSame('He had been walking for two hours before the rain started.', $payload['reorder_answer']);
            $this->assertSame($payload['question'], $payload['reorder_source_question']);
            $this->assertSame($this->expectedHints(['a1', 'a2'], $locale), $payload['verb_hints']);
            $this->assertSame(['had been walking', 'started'], $payload['answers']);
        }
    }

    #[DataProvider('serializerLocales')]
    public function test_persisted_optin_prompts_and_hints_refresh_without_resetting_progress(string $controller, string $locale): void
    {
        $question = $this->question(true);
        $stored = $this->dataset($controller, $locale, [$question])[0];
        $stored['question'] = 'Outdated task condition';
        $stored['compose_source_text'] = 'Outdated compose source';
        $stored['sourceTextUk'] = 'Outdated source field';
        $stored['hint'] = 'Outdated hint';
        $stored['hintUk'] = 'Outdated compose hint';
        $stored['chosen'] = ['a1' => 'She'];
        $stored['attempts'] = 3;
        $stored['answerHistory'] = [['answer' => 'She had', 'correct' => false]];

        $result = $this->persisted($controller, $locale, [$stored], $question)[0];

        $this->assertSame(self::SOURCES[$locale], $result['question']);
        $this->assertSame(self::SOURCES[$locale], $result['compose_source_text']);
        $this->assertSame(self::SOURCES[$locale], $result['sourceTextUk']);
        $this->assertSame(self::HINTS[$locale], $result['hint']);
        $this->assertSame(self::HINTS[$locale], $result['hintUk']);
        $this->assertSame($this->expectedHints($this->markers(), $locale), $result['verb_hints']);
        foreach (['uuid', 'id', 'answers', 'answer_map', 'options', 'chosen', 'attempts', 'answerHistory'] as $field) {
            $this->assertSame($stored[$field], $result[$field], 'Refresh must preserve '.$field.'.');
        }
    }

    #[DataProvider('serializerLocales')]
    public function test_persisted_legacy_variant_text_and_fields_remain_unchanged(string $controller, string $locale): void
    {
        $question = $this->question(false);
        $stored = $this->dataset($controller, $locale, [$question])[0];
        $stored['question'] = 'Persisted legacy variant, not canonical question text';
        $stored['sourceTextUk'] = 'Legacy source field';
        $stored['hintUk'] = 'Legacy hint field';
        $stored['hint'] = 'Legacy hint';
        $stored['attempts'] = 2;

        $this->assertSame([$stored], $this->persisted($controller, $locale, [$stored], $question));
    }

    #[DataProvider('serializerLocales')]
    public function test_persisted_reorder_optin_refreshes_constraint_but_preserves_english_answer_and_placements(string $controller, string $locale): void
    {
        $question = $this->question(true, false);
        $stored = $this->dataset($controller, $locale, [$question], true)[0];
        $stored['compose_source_text'] = 'Outdated source';
        $stored['reorder_template_constraint'] = false;
        $stored['placements'] = [0 => 'He had', 1 => 'been walking'];

        $result = $this->persisted($controller, $locale, [$stored], $question)[0];

        $this->assertSame(self::SOURCES[$locale], $result['compose_source_text']);
        $this->assertTrue($result['reorder_template_constraint']);
        $this->assertSame(self::HINTS[$locale], $result['hint']);
        foreach (['question', 'reorder_answer', 'reorder_tokens', 'reorder_source_question', 'placements'] as $field) {
            $this->assertSame($stored[$field], $result[$field], 'Locale refresh must preserve '.$field.'.');
        }
    }

    #[DataProvider('serializerLocales')]
    public function test_content_revision_guard_detects_missing_stale_and_target_rewrites(string $controller, string $locale): void
    {
        $question = $this->question(true);
        $stored = $this->dataset($controller, $locale, [$question])[0];
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $stored['compose_content_revision']);
        $this->assertFalse($this->withRows($question, fn (): bool => LocalizedComposeText::cachedNeedsRefresh([$stored])));
        $old = $stored;
        unset($old['compose_content_revision']);
        $this->assertTrue($this->withRows($question, fn (): bool => LocalizedComposeText::cachedNeedsRefresh([$old])));
        $old['compose_content_revision'] = str_repeat('0', 64);
        $this->assertTrue($this->withRows($question, fn (): bool => LocalizedComposeText::cachedNeedsRefresh([$old])));
        $question->answers->first()->option->option = 'finished.';
        $this->assertTrue($this->withRows($question, fn (): bool => LocalizedComposeText::cachedNeedsRefresh([$stored])));
    }

    #[DataProvider('serializerLocales')]
    public function test_revision_rebuild_ignores_removed_stored_variants_without_rotating_or_resetting(string $controller, string $locale): void
    {
        $question = $this->question(true);
        $payload = $this->dataset($controller, $locale, [$question], false, false, 'Deleted old sentence variant')[0];
        $this->assertSame(self::SOURCES[$locale], $payload['question']);
        $this->assertSame(self::TOKENS, $payload['answers']);
        $this->assertSame(LocalizedComposeText::revision($question), $payload['compose_content_revision']);
        $this->assertFalse(LocalizedComposeText::storedVariantIsCurrent($question, 'Deleted old sentence variant'));
        $this->assertTrue(LocalizedComposeText::storedVariantIsCurrent($question, self::SOURCES['uk']));
    }

    public function test_revision_is_locale_independent_and_changes_for_each_canonical_component(): void
    {
        $question = $this->question(true);
        $revision = LocalizedComposeText::revision($question);
        foreach (['uk', 'en', 'pl'] as $locale) {
            app()->setLocale($locale);
            $this->assertSame($revision, LocalizedComposeText::revision($question));
        }
        foreach (['raw', 'answers', 'options', 'hints', 'verbHints', 'optionsByMarker'] as $component) {
            $changed = $this->question(true);
            match ($component) {
                'raw' => $changed->question = 'Updated authored condition',
                'answers' => $changed->answers->first()->option->option = 'finished.',
                'options' => $changed->options->first()->option = 'They',
                'hints' => $changed->hints->first()->hint = 'Updated localized source',
                'verbHints' => $changed->verbHints->first()->option->option = 'Updated lexical hint',
                'optionsByMarker' => $changed->options_by_marker = ['a1' => ['She', 'They']],
            };
            $this->assertNotSame($revision, LocalizedComposeText::revision($changed), $component.' must invalidate the canonical revision.');
        }
    }

    public function test_revision_cache_guard_never_invalidates_legacy_or_comparison_banks(): void
    {
        $seeders = (new ReflectionClass(LocalizedComposeText::class))->getConstant('REVISION_SEEDERS');
        $this->assertCount(9, $seeders);
        foreach ($seeders as $seeder) {
            $question = $this->question(true);
            $question->seeder = $seeder;
            $this->assertTrue(LocalizedComposeText::revisionEligible($question));
            $this->assertNotNull(LocalizedComposeText::revision($question));
        }
        foreach ([
            'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\Tenses\\Comparisons\\PastPerfectVsPastPerfectContinuousAllLevelsV3Seeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousBasicsLessonSeeder',
            null,
        ] as $seeder) {
            $question = $this->question(true);
            $question->seeder = $seeder;
            $this->assertNull(LocalizedComposeText::revision($question));
            $this->assertFalse($this->withRows($question, fn (): bool => LocalizedComposeText::cachedNeedsRefresh([
                ['id' => $question->id, 'uuid' => $question->uuid],
            ]), false));
        }
        $legacy = $this->question(false);
        $this->assertNull(LocalizedComposeText::revision($legacy));
        $this->assertFalse($this->withRows($legacy, fn (): bool => LocalizedComposeText::cachedNeedsRefresh([
            ['id' => $legacy->id, 'uuid' => $legacy->uuid],
        ])));
    }

    private function persisted(string $controllerClass, string $locale, array $stored, Question $question): array
    {
        app()->setLocale($locale);
        session(['locale' => $locale]);
        $this->app->instance('request', Request::create('http://gramlyze.loc/tests/fixture', 'GET', ['locale' => $locale]));
        return $this->withRows($question, function () use ($controllerClass, $stored): array {
            $controller = (new ReflectionClass($controllerClass))->newInstanceWithoutConstructor();
            return (new ReflectionMethod($controllerClass, 'localizePersistedQuestionVerbHints'))->invoke($controller, $stored);
        });
    }

    private function withRows(Question $question, callable $callback, bool $expectsHints = true): mixed
    {
        $rows = ['questions' => [(object) $question->getAttributes()], 'verb_hints' => [], 'question_options' => [], 'question_hints' => [], 'question_answers' => []];
        $pivotOptions = [];
        $targetOptionIds = [];
        foreach ($question->options as $index => $option) {
            $id = 10000 + $index;
            $rows['question_options'][] = (object) ['id' => $id, 'option' => $option->option];
            $pivotOptions[] = (object) [
                'id' => $id, 'option' => $option->option, 'pivot_question_id' => $question->id,
                'pivot_option_id' => $id, 'pivot_flag' => null,
            ];
            $targetOptionIds[$option->option] ??= $id;
        }
        foreach ($question->answers as $index => $answer) {
            $id = $targetOptionIds[$answer->option->option] ?? (20000 + $index);
            if ($id >= 20000) {
                $rows['question_options'][] = (object) ['id' => $id, 'option' => $answer->option->option];
            }
            $rows['question_answers'][] = (object) ['id' => $index + 1, 'question_id' => $question->id, 'marker' => $answer->marker, 'option_id' => $id];
        }
        foreach ($question->verbHints as $index => $hint) {
            $rows['verb_hints'][] = (object) array_merge($hint->getAttributes(), [
                'id' => $index + 1, 'question_id' => $question->id, 'option_id' => $index + 1,
            ]);
            $rows['question_options'][] = (object) ['id' => $index + 1, 'option' => $hint->option->option];
        }
        foreach ($question->hints as $index => $hint) {
            $rows['question_hints'][] = (object) array_merge($hint->getAttributes(), ['id' => $index + 1, 'question_id' => $question->id]);
        }

        // The real Eloquent eager-loader runs against synthetic result rows.
        // Any attempted PDO connection fails, so this cannot access a database.
        $queried = [];
        $select = function (string $query, array $bindings = []) use ($rows, $pivotOptions, &$queried): array {
            if (preg_match('/from "([^"]+)"/', $query, $matches) !== 1 || ! array_key_exists($matches[1], $rows)) {
                throw new \LogicException('Unexpected persisted-payload fixture query.');
            }
            $queried[] = $matches[1];
            if ($matches[1] === 'question_options') {
                if (str_contains($query, 'question_option_question')) {
                    return $pivotOptions;
                }
                $ids = $bindings;
                if ($ids === [] && preg_match('/"question_options"\."id" in \(([\d, ]+)\)/', $query, $idsMatch) === 1) {
                    $ids = array_map('intval', explode(',', $idsMatch[1]));
                }
                return array_values(array_filter($rows['question_options'], fn ($row): bool => in_array($row->id, $ids)));
            }
            return $rows[$matches[1]];
        };
        $connection = new class($select) extends SQLiteConnection
        {
            public function __construct(private \Closure $fixtureSelect)
            {
                parent::__construct(
                    static fn () => throw new \LogicException('Persisted-payload unit fixtures must not connect to PDO.'),
                    ':memory:', '', ['name' => 'ppc-payload-unit'],
                );
            }

            public function select($query, $bindings = [], $useReadPdo = true, array $fetchUsing = [])
            {
                return ($this->fixtureSelect)($query, $bindings);
            }
        };
        $resolver = new ConnectionResolver(['ppc-payload-unit' => $connection]);
        $resolver->setDefaultConnection('ppc-payload-unit');
        $originalResolver = Question::getConnectionResolver();

        try {
            Question::setConnectionResolver($resolver);
            $result = $callback();
            if ($expectsHints) {
                $this->assertContains('question_hints', $queried, 'Canonical prompt hints must be eager-loaded for persisted payloads.');
            } else {
                $this->assertNotContains('question_hints', $queried, 'Out-of-scope legacy guards must not query prompt hints.');
            }

            return $result;
        } finally {
            Question::setConnectionResolver($originalResolver);
        }
    }

    private function markers(): array
    {
        return array_map(static fn (int $index): string => 'a'.$index, range(1, count(self::TOKENS)));
    }

    private function expectedHints(array $markers, string $locale): array
    {
        return array_combine($markers, array_map(
            static fn (string $marker): string => self::HINTS[$locale].' ['.$marker.']',
            $markers
        ));
    }

    private function question(bool $optedIn, bool $compose = true): Question
    {
        $question = new Question([
            'uuid' => 'ed6af400-e200-4ea3-89f5-123456789abc',
            'question' => $compose ? self::SOURCES['uk'] : 'He {a1} for two hours before the rain {a2}.',
            'type' => $compose ? Question::TYPE_COMPOSE_TOKENS : '0',
            'level' => 'B2',
            'seeder' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder',
        ]);
        $question->id = 900001;
        $values = $compose ? self::TOKENS : ['had been walking', 'started'];
        $answers = [];
        $options = [];
        $verbHints = [];
        foreach ($values as $index => $value) {
            $marker = 'a'.($index + 1);
            $option = new QuestionOption(['option' => $value]);
            $answers[] = (new QuestionAnswer(['marker' => $marker]))->setRelation('option', $option);
            $options[] = $option;
            // Deliberately insert other locales and the unlocalized legacy hint
            // first. Every marker, not only a1, must choose the active locale.
            foreach (['', 'pl', 'en', 'uk'] as $locale) {
                $text = $locale === '' ? 'Legacy verb hint' : self::HINTS[$locale];
                $verbHints[] = (new VerbHint(['marker' => strtoupper($marker), 'locale' => $locale]))
                    ->setRelation('option', new QuestionOption(['option' => $text.' ['.$marker.']']));
            }
        }

        $hints = [];
        foreach (['pl', 'en', 'uk'] as $locale) {
            if ($optedIn) {
                $hints[] = new QuestionHint([
                    'provider' => LocalizedComposeText::PROVIDER,
                    'locale' => $locale,
                    'hint' => self::SOURCES[$locale],
                ]);
            }
            $hints[] = new QuestionHint([
                'provider' => 'polyglot-v3', 'locale' => $locale, 'hint' => self::HINTS[$locale],
            ]);
        }

        return $question
            ->setRelation('category', new Category(['name' => 'Past Perfect Continuous']))
            ->setRelation('answers', new EloquentCollection(array_reverse($answers)))
            ->setRelation('options', new EloquentCollection($options))
            ->setRelation('verbHints', new EloquentCollection($verbHints))
            ->setRelation('hints', new EloquentCollection($hints))
            ->setRelation('theoryTextBlocks', new EloquentCollection())
            ->setRelation('variants', new EloquentCollection());
    }

    private function dataset(string $controllerClass, string $locale, array $questions, bool $mixed = false, ?bool $freshVariants = null, ?string $storedVariant = null): array
    {
        app()->setLocale($locale);
        config(['session.driver' => 'array']);
        session(['locale' => $locale]);
        $this->app->instance('request', Request::create('http://gramlyze.loc/tests/fixture', 'GET', ['locale' => $locale]));
        $filters = $mixed ? ['__meta' => ['theory_page_mixed_polyglot_test' => true]] : [];
        $test = new Test(['slug' => 'isolated-ppc-payload', 'filters' => $filters]);
        $collection = collect($questions);
        $resolved = new ResolvedSavedTest($test, collect([900001]), $collection->pluck('uuid'), false);

        $resolver = Mockery::mock(SavedTestResolver::class);
        $resolver->shouldReceive('loadQuestions')->once()->with($resolved, Mockery::on(
            static fn (array $relations): bool => in_array('hints', $relations, true)
                && in_array('verbHints.option', $relations, true)
                && in_array('answers.option', $relations, true)
        ))->andReturn($collection);
        $variants = Mockery::mock(QuestionVariantService::class);
        $variants->shouldReceive('supportsVariants')->once()->andReturn(true);
        $technicalInfo = Mockery::mock(QuestionTechnicalInfoService::class);
        $storedVariants = $storedVariant === null ? [] : [900001 => $storedVariant];
        $fresh = $freshVariants ?? ($controllerClass === GrammarTestController::class);
        $variants->shouldReceive('getStoredVariants')->once()->with($test->slug)->andReturn($storedVariants);
        if ($fresh) {
            $variants->shouldReceive('clearForTest')->once()->with($test->slug);
            $variants->shouldReceive('applyRandomVariants')->once()->with($test, $collection, $storedVariants)->andReturn($collection);
        } else {
            foreach ($questions as $question) {
                if (LocalizedComposeText::storedVariantIsCurrent($question, $storedVariant)) {
                    $variants->shouldReceive('applyStoredVariant')->once()->with($test->slug, $question);
                }
            }
        }

        if ($controllerClass === TestJsV2Controller::class) {
            $markerMatcher = Mockery::mock(MarkerTheoryMatcherService::class);
            $markerMatcher->shouldReceive('getAllMarkerTags')->times(count($questions))->with(900001)->andReturn([]);
            $controller = new TestJsV2Controller(
                $variants, $resolver, $markerMatcher, $technicalInfo,
                Mockery::mock(PolyglotCourseManifestService::class),
                Mockery::mock(PolyglotLessonDebugPayloadBuilder::class),
            );
        } else {
            $filterService = Mockery::mock(GrammarTestFilterService::class);
            $filterService->shouldReceive('normalize')->once()->with($filters)->andReturn($filters);
            $controller = new GrammarTestController(
                $variants, $resolver, $filterService, Mockery::mock(QuestionDeletionService::class),
                $technicalInfo, Mockery::mock(TagAggregationService::class),
                Mockery::mock(TheoryBlockMatcherService::class),
            );
        }

        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $method = new ReflectionMethod($controllerClass, 'buildQuestionDataset');
        $payload = $method->invoke($controller, $resolved, $fresh, false);
        $this->assertSame([], DB::connection()->getQueryLog(), 'Payload serialization must not query the application database.');

        return $payload;
    }
}
