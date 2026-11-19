<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Support\ConditionalsPracticeSource;
use App\Support\ConditionalsPracticeSupplement;
use App\Support\ConditionalsWidgetHints as Widget;
use Illuminate\Config\Repository;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;

/** No served files or working database; each test starts with fresh PHP statics. */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ConditionalsWidgetMembershipTest extends TestCase
{
    private const EXTERNAL_UUID = 'f0000000-0000-4000-8000-000000000001';
    private const EXTERNAL_ID = 2147483646;
    private const BEFORE_SHA = '32446b6d7947aeccdc09ff16cb13a995a1a3ea935e76c7c8725ab8e85fa21a2f';
    private const MODEL_FIELDS = [
        'id', 'uuid', 'seeder', 'type', 'level', 'category_id', 'source_id', 'question', 'options_by_marker',
    ];
    private int $sqlAttempts = 0;

    private static function root(): string
    {
        return dirname(__DIR__, 2);
    }

    private function frozen(): array
    {
        $bytes = file_get_contents(self::root().'/'.Widget::SOURCE);
        self::assertIsString($bytes);
        self::assertSame(Widget::SOURCE_SHA, hash('sha256', $bytes));

        return json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    }

    /** Observe the real source guard and dependent path via the installed application's base_path(). */
    private function runtime(): Application
    {
        $app = new class(self::root()) extends Application
        {
            public array $pathCalls = [];
            public array $override = [];

            public function basePath($path = '')
            {
                $this->pathCalls[] = $path;

                return $this->override[$path] ?? parent::basePath($path);
            }
        };
        $app->instance('config', new Repository([
            'app' => ['locale' => 'uk'],
            'database' => ['default' => 'sqlite', 'connections' => [
                'sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            ]],
        ]));
        $app->instance('request', Request::create('/test-only/conditionals-widget'));
        $database = new DatabaseManager($app, new ConnectionFactory($app));
        $app->instance('db', $database);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
        Question::setConnectionResolver($database);
        $database->connection()->beforeExecuting(function (): void {
            ++$this->sqlAttempts;
        });

        return $app;
    }

    private function pathCount(Application $app, string $path): int
    {
        return count(array_keys($app->pathCalls, $path, true));
    }

    private function localArtifact(?string $bytes, string $extension = 'json'): string
    {
        $parent = IsolatedTestEnvironment::prepare().'/app';
        IsolatedTestEnvironment::assertOwnedPath($parent);
        $path = $parent.'/conditionals-widget-'.bin2hex(random_bytes(12)).'.'.$extension;
        self::assertFileDoesNotExist($path);
        if ($bytes !== null) {
            self::assertSame(strlen($bytes), file_put_contents($path, $bytes, LOCK_EX));
        }

        return $path;
    }

    private function privateFixture(string $variable, string $relative): ?string
    {
        $configured = getenv($variable);
        if ($configured === false || $configured === '') {
            return null;
        }
        $expected = self::root().'/storage/logs/perf-10/'.$relative;
        self::assertFalse(is_link($expected));
        self::assertNotFalse(realpath($expected));
        self::assertSame(realpath($expected), realpath($configured), 'Only the exact private PERF-10 fixture is allowed.');

        return $expected;
    }

    /** Optional historical executable oracle. Only its class declaration is renamed to coexist in this process. */
    private function beforeClass(): ?string
    {
        $path = $this->privateFixture('GRAMLYZE_PERF10_BEFORE', 'recovery/WT/app/Support/ConditionalsWidgetHints.php');
        if ($path === null) {
            return null;
        }
        $bytes = file_get_contents($path);
        self::assertIsString($bytes);
        self::assertSame(self::BEFORE_SHA, hash('sha256', $bytes));
        return $this->isolatedWidgetClass($bytes, 'ConditionalsWidgetHintsPerf10Before');
    }

    private function isolatedWidgetClass(string $bytes, string $class): string
    {
        $renamed = str_replace('final class ConditionalsWidgetHints', 'final class '.$class, $bytes, $count);
        self::assertSame(1, $count);
        require $this->localArtifact($renamed, 'php');

        return 'App\\Support\\'.$class;
    }

    private function model(array $row, bool $subclass = false): Question
    {
        $q = $subclass ? new class extends Question {} : new Question;
        // Keep actual property types; Eloquent's auto-increment key cast would hide bad-type test inputs.
        $q->incrementing = false;
        $q->setRawAttributes(array_intersect_key($row, array_flip(self::MODEL_FIELDS)), true);
        $q->setRelation('answers', collect(array_map(static function (array $item): QuestionAnswer {
            $answer = new QuestionAnswer;
            $answer->setRawAttributes(array_diff_key($item, ['option' => true]), true);
            $option = new QuestionOption;
            $option->setRawAttributes(['id' => $item['option_id'], 'option' => $item['option']], true);
            $answer->setRelation('option', $option);

            return $answer;
        }, $row['answers'])));
        $q->setRelation('options', collect(array_map(static function (array $item): QuestionOption {
            $option = new QuestionOption;
            $option->setRawAttributes($item, true);

            return $option;
        }, $row['option_snapshot'])));
        $q->setRelation('hints', collect(array_map(static function (array $item): QuestionHint {
            $hint = new QuestionHint;
            $hint->setRawAttributes($item, true);

            return $hint;
        }, $row['hints'])));
        $q->setRelation('verbHints', collect(array_map(static function (array $item): VerbHint {
            $hint = new VerbHint;
            $hint->setRawAttributes(array_diff_key($item, ['option' => true]), true);
            $option = new QuestionOption;
            $option->setRawAttributes(['id' => $item['option_id'], 'option' => $item['option']], true);
            $hint->setRelation('option', $option);

            return $hint;
        }, $row['verb_hints'])));

        return $q;
    }

    private function originalPayload(array $row): array
    {
        return [
            'id' => $row['id'], 'type' => '4', 'level' => $row['level'], 'question' => $row['payload_question'],
            'correct_tokens' => $row['correct_tokens'],
            'answers' => array_map(static fn (array $a): array => ['marker' => $a['marker'], 'correct' => $a['option']], $row['answers']),
            // Ensure the input is a multiset, not merely a set of token spellings.
            'options' => array_merge($row['correct_tokens'], ['separate distractor']),
            'accepted_sentences' => ['Retain the existing alias'],
            'token_labels' => ['separate distractor' => 'existing caption'],
            'derived_compose_options' => ['separate distractor', 'separate distractor'],
            'unrelated' => ['retain' => true],
        ];
    }

    /** Independent frozen-content expectation; does not derive outputs from the optimization's registry. */
    private function expected(array $row, array $original): array
    {
        $expected = array_replace($original, [
            'question' => $row['display_question'], 'source_bound_compose_hint' => true, 'compose_preanswer_hint' => $row['hint_uk'],
            'token_labels' => array_replace($original['token_labels'], $row['token_labels']),
            'derived_compose_options' => array_values(array_unique([...$original['derived_compose_options'], ...$row['derived_options']])),
            'conditionals_widget_hint_revision' => hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)),
        ]);
        if ($row['accepted_sentences'] !== []) {
            $expected['accepted_sentences'] = array_values(array_unique([
                implode(' ', $row['correct_tokens']), ...$original['accepted_sentences'], ...$row['accepted_sentences'],
            ]));
        }

        return $expected;
    }

    private function assertUnchanged(Question $q, ?string $blockUuid, array $payload, bool $canonical = true): void
    {
        $before = serialize([$q->getAttributes(), $q->getRawOriginal(), $q->getRelations()]);
        self::assertSame($payload, Widget::project($q, $blockUuid, $payload, $canonical), 'The rejection must return the original payload.');
        self::assertSame($before, serialize([$q->getAttributes(), $q->getRawOriginal(), $q->getRelations()]), 'Model and eager snapshots must stay unchanged.');
    }

    public function test_registry_equals_independent_target_and_question_sets_and_rejects_corruption(): void
    {
        $doc = $this->frozen();
        $fromTargets = [];
        foreach ($doc['targets'] as $target) {
            $uuid = $target['binding']['uuid'];
            self::assertIsString($uuid);
            self::assertArrayNotHasKey($uuid, $fromTargets);
            $fromTargets[$uuid] = $target['selected_ids'];
            self::assertCount(5, $fromTargets[$uuid]);
        }
        $fromQuestions = [];
        $ids = [];
        foreach ($doc['questions'] as $row) {
            self::assertIsInt($row['id']);
            self::assertArrayNotHasKey($row['id'], $ids);
            self::assertSame('4', $row['type']);
            $ids[$row['id']] = true;
            $fromQuestions[$row['block_uuid']][] = $row['id'];
        }
        self::assertCount(13, $fromTargets);
        self::assertCount(65, $ids);
        self::assertSame($fromTargets, $fromQuestions);
        $registry = (new ReflectionClass(Widget::class))->getReflectionConstant('MEMBERSHIP')->getValue();
        self::assertSame(Widget::SOURCE_SHA, $registry['source_sha']);
        $registered = $registry['pairs'];
        // Membership, not author order: compare exact keys and id sets using numeric id order.
        foreach ([$fromTargets, $registered] as $which => $matrix) {
            ksort($matrix, SORT_STRING);
            foreach ($matrix as &$selected) {
                sort($selected, SORT_NUMERIC);
            }
            unset($selected);
            if ($which === 0) {
                $expected = $matrix;
            } else {
                self::assertSame($expected, $matrix);
            }
        }
        $verify = new ReflectionMethod(Widget::class, 'verifiedMembership');
        self::assertNotNull($verify->invoke(null, $registry));
        $a = array_key_first($registered);
        $b = array_keys($registered)[1];
        $changed = [];
        $missing = $registry;
        array_pop($missing['pairs'][$a]);
        $changed[] = $missing;
        $extra = $registry;
        $extra['pairs'][$a][] = self::EXTERNAL_ID;
        $changed[] = $extra;
        $duplicate = $registry;
        $duplicate['pairs'][$a][1] = $duplicate['pairs'][$a][0];
        $changed[] = $duplicate;
        $wrongOwner = $registry; // Still 13/65, still globally unique, but no longer the same exact pairs.
        [$wrongOwner['pairs'][$a][0], $wrongOwner['pairs'][$b][0]] = [$wrongOwner['pairs'][$b][0], $wrongOwner['pairs'][$a][0]];
        $changed[] = $wrongOwner;
        $wrongType = $registry;
        $wrongType['pairs'][$a][0] = (string) $wrongType['pairs'][$a][0];
        $changed[] = $wrongType;
        $changed[] = array_replace($registry, ['source_sha' => str_repeat('0', 64)]);
        $changed[] = ['source_sha' => Widget::SOURCE_SHA];
        $changed[] = null;
        $changed[] = 'invalid registry';
        foreach ($changed as $i => $broken) {
            self::assertNull($verify->invoke(null, $broken), 'Untrusted roster variant '.$i.' cannot prove foreign ownership.');
        }
    }

    public function test_all_sixty_five_owned_pairs_keep_complete_before_payloads_without_queries(): void
    {
        $app = $this->runtime();
        $doc = $this->frozen();
        $results = [];
        $withAliases = $withLabels = 0;
        foreach ($doc['questions'] as $row) {
            $payload = $this->originalPayload($row);
            $q = $this->model($row);
            $actual = Widget::project($q, $row['block_uuid'], $payload, true);
            self::assertSame($this->expected($row, $payload), $actual);
            self::assertTrue($actual['source_bound_compose_hint']);
            if ($row['accepted_sentences'] !== []) {
                ++$withAliases;
                foreach ($row['accepted_sentences'] as $alias) {
                    self::assertContains($alias, $actual['accepted_sentences']);
                }
            }
            if ($row['token_labels'] !== []) {
                ++$withLabels;
                foreach ($row['token_labels'] as $token => $caption) {
                    self::assertSame($caption, $actual['token_labels'][$token]);
                }
            }
            $results[] = [$row, $payload, $actual];
        }
        self::assertCount(65, $results);
        self::assertGreaterThan(0, $withAliases);
        self::assertGreaterThan(0, $withLabels);
        self::assertSame(65, $this->pathCount($app, Widget::SOURCE));
        self::assertSame(1, $this->pathCount($app, ConditionalsPracticeSupplement::SOURCE));
        self::assertSame(1, $this->pathCount($app, ConditionalsPracticeSource::SOURCE));
        $before = $this->beforeClass();
        if ($before !== null) {
            foreach ($results as [$row, $payload, $actual]) {
                self::assertSame($actual, $before::project($this->model($row), $row['block_uuid'], $payload, true));
            }
        }
        self::assertSame(0, $this->sqlAttempts);
    }

    public function test_unavailable_or_wrong_revision_roster_falls_back_through_the_real_project_path(): void
    {
        $app = $this->runtime();
        $row = $this->frozen()['questions'][0];
        $payload = $this->originalPayload($row);
        $q = $this->model($row);
        $bytes = file_get_contents(self::root().'/app/Support/ConditionalsWidgetHints.php');
        self::assertIsString($bytes);
        $missing = str_replace('private const MEMBERSHIP =', 'private const UNAVAILABLE_MEMBERSHIP =', $bytes, $count);
        self::assertSame(1, $count);
        $wrong = preg_replace("/private const MEMBERSHIP_FINGERPRINT = '[a-f0-9]{64}';/", "private const MEMBERSHIP_FINGERPRINT = '".str_repeat('0', 64)."';", $bytes, 1, $count);
        self::assertIsString($wrong);
        self::assertSame(1, $count);

        $fallbacks = [
            'ConditionalsWidgetHintsPerf10MissingRegistry' => $missing,
            'ConditionalsWidgetHintsPerf10WrongDigest' => $wrong,
        ];
        foreach ($fallbacks as $class => $isolatedBytes) {
            $variant = $this->isolatedWidgetClass($isolatedBytes, $class);
            self::assertSame($payload, $variant::project($q, self::EXTERNAL_UUID, $payload, true));
            self::assertSame($this->expected($row, $payload), $variant::project($q, $row['block_uuid'], $payload, true));
        }
        self::assertSame(4, $this->pathCount($app, Widget::SOURCE));
        self::assertSame(2, $this->pathCount($app, ConditionalsPracticeSupplement::SOURCE), 'Neither invalid registry may skip its original expensive path.');
        self::assertSame(0, $this->sqlAttempts);
    }

    public function test_foreign_and_cross_owner_pairs_verify_source_once_each_without_loading_dependent_package(): void
    {
        $app = $this->runtime();
        $doc = $this->frozen();
        $row = $doc['questions'][0];
        $payload = $this->originalPayload($row);
        $cases = [
            [self::EXTERNAL_UUID, self::EXTERNAL_ID],
            [$row['block_uuid'], self::EXTERNAL_ID],
            [self::EXTERNAL_UUID, $row['id']],
        ];
        foreach ($doc['targets'] as $target) {
            // A pair lookup must not be treated as membership of each field separately.
            foreach ($doc['questions'] as $question) {
                if ($target['binding']['uuid'] !== $question['block_uuid']) {
                    $cases[] = [$target['binding']['uuid'], $question['id']];
                }
            }
        }
        self::assertCount(65 * 12 + 3, $cases);
        $boundary = count($cases);
        $path = $this->privateFixture('GRAMLYZE_PERF10_CASE_RECEIPT', 'conditionals-preflight-pairs.json');
        if ($path !== null) {
            $receipt = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            self::assertSame(1, $receipt['schema']);
            self::assertSame(Widget::SOURCE_SHA, $receipt['sourceSha']);
            foreach (['word', 'ppc'] as $key) {
                self::assertCount(5, $receipt[$key]['pairs']);
                foreach ($receipt[$key]['pairs'] as $pair) {
                    self::assertIsString($pair['blockUuid']);
                    self::assertIsInt($pair['id']);
                    $cases[] = [$pair['blockUuid'], $pair['id']];
                }
            }
            self::assertCount($boundary + 10, $cases);
        }
        $owned = array_map(static fn (array $q): array => [$q['block_uuid'], $q['id']], $doc['questions']);
        foreach ($cases as [$uuid, $id]) {
            self::assertFalse(in_array([$uuid, $id], $owned, true));
            $this->assertUnchanged($this->model(array_replace($row, ['id' => $id])), $uuid, $payload);
        }
        self::assertSame(count($cases), $this->pathCount($app, Widget::SOURCE));
        self::assertSame(0, $this->pathCount($app, ConditionalsPracticeSupplement::SOURCE));
        self::assertSame(0, $this->pathCount($app, ConditionalsPracticeSource::SOURCE));
        $before = $this->beforeClass();
        if ($before !== null) {
            foreach (array_merge(array_slice($cases, 0, 3), array_slice($cases, $boundary)) as [$uuid, $id]) {
                self::assertSame($payload, $before::project($this->model(array_replace($row, ['id' => $id])), $uuid, $payload, true));
            }
            self::assertSame(1, $this->pathCount($app, ConditionalsPracticeSupplement::SOURCE));
        }
        self::assertSame(0, $this->sqlAttempts);
    }

    public function test_guard_scope_and_unproved_inputs_do_not_inherit_foreign_status(): void
    {
        $app = $this->runtime();
        $row = $this->frozen()['questions'][0];
        $payload = $this->originalPayload($row);
        $q = $this->model($row);
        $this->assertUnchanged($q, $row['block_uuid'], $payload, false);
        foreach (['en', 'pl'] as $locale) {
            $app['config']->set('app.locale', $locale);
            $this->assertUnchanged($q, $row['block_uuid'], $payload);
        }
        $app['config']->set('app.locale', 'uk');
        $this->assertUnchanged($this->model(array_replace($row, ['type' => '1'])), $row['block_uuid'], $payload);
        foreach ([4, '0', null] as $type) {
            $this->assertUnchanged($q, $row['block_uuid'], array_replace($payload, ['type' => $type]));
        }
        self::assertSame($payload, Widget::project((object) ['type' => '4', 'id' => $row['id']], $row['block_uuid'], $payload, true));
        self::assertSame(0, $this->pathCount($app, Widget::SOURCE));

        $isForeign = new ReflectionMethod(Widget::class, 'isProvenForeign');
        foreach ([[null, 1], ['', 1], ['malformed', 4], [' '.self::EXTERNAL_UUID, 1], [strtoupper(self::EXTERNAL_UUID), 1],
            [self::EXTERNAL_UUID, null], [self::EXTERNAL_UUID, 0], [self::EXTERNAL_UUID, -1], [self::EXTERNAL_UUID, '1'],
            [self::EXTERNAL_UUID, 1.0], [self::EXTERNAL_UUID, true]] as [$uuid, $id]) {
            self::assertFalse($isForeign->invoke(null, $uuid, $id));
        }
        $stringId = $this->model(array_replace($row, ['id' => (string) $row['id']]));
        self::assertIsString($stringId->id);
        $this->assertUnchanged($stringId, $row['block_uuid'], $payload);
        self::assertSame(1, $this->pathCount($app, Widget::SOURCE));
        self::assertSame(1, $this->pathCount($app, ConditionalsPracticeSupplement::SOURCE), 'Uncertain ID must use old validation path.');
        $this->assertUnchanged($q, null, $payload);
        $this->assertUnchanged($q, 'malformed', $payload);
        self::assertSame(3, $this->pathCount($app, Widget::SOURCE));
        self::assertSame($this->expected($row, $payload), Widget::project($this->model($row, subclass: true), $row['block_uuid'], $payload, true));
        self::assertSame(0, $this->sqlAttempts);
    }

    public function test_owned_pairs_cannot_bypass_snapshots_or_token_multiplicity(): void
    {
        $this->runtime();
        $doc = $this->frozen();
        $row = array_values(array_filter($doc['questions'], static fn (array $r): bool => $r['hints'] !== []))[0] ?? null;
        self::assertIsArray($row);
        $payload = $this->originalPayload($row);
        $uuid = $row['block_uuid'];
        $before = $this->beforeClass();

        foreach ([
            static fn (Question $q) => $q->question .= ' changed',
            static fn (Question $q) => $q->answers->first()->marker = 'incorrect',
            static fn (Question $q) => $q->answers->first()->option->option = 'incorrect',
            static fn (Question $q) => $q->options->first()->option = 'incorrect',
            static fn (Question $q) => $q->hints->first()->hint = 'Інша підказка',
            static fn (Question $q) => $q->unsetRelation('hints'),
            static fn (Question $q) => $q->answers->first()->unsetRelation('option'),
            static fn (Question $q) => $q->setRawAttributes(array_replace($q->getAttributes(), ['options_by_marker' => '{}']), true),
            static function (Question $q): void {
                $hint = new VerbHint;
                $hint->setRawAttributes(['id' => 1, 'marker' => 'a1', 'locale' => 'uk', 'option_id' => 1], true);
                $option = new QuestionOption;
                $option->setRawAttributes(['id' => 1, 'option' => 'Стороння підказка'], true);
                $hint->setRelation('option', $option);
                $q->setRelation('verbHints', collect([$hint]));
            },
        ] as $mutate) {
            $q = $this->model($row);
            $mutate($q);
            $this->assertUnchanged($q, $uuid, $payload);
            if ($before !== null) {
                self::assertSame($payload, $before::project($q, $uuid, $payload, true));
            }
        }
        foreach (['id' => -1, 'level' => 'invalid', 'question' => 'invalid', 'answers' => [], 'correct_tokens' => [], 'options' => null] as $key => $value) {
            $this->assertUnchanged($this->model($row), $uuid, array_replace($payload, [$key => $value]));
        }

        $withRepeatedToken = array_values(array_filter($doc['questions'], static fn (array $r): bool =>
            count(array_unique($r['correct_tokens'])) < count($r['correct_tokens'])))[0] ?? null;
        self::assertIsArray($withRepeatedToken);
        $payload = $this->originalPayload($withRepeatedToken);
        $this->assertUnchanged(
            $this->model($withRepeatedToken),
            $withRepeatedToken['block_uuid'],
            array_replace($payload, ['options' => array_values(array_unique($payload['options']))]),
        );
        self::assertSame(0, $this->sqlAttempts);
    }

    public static function sequenceCases(): array
    {
        return [
            'foreign then owned, repeat both' => [[null, null, 0, null, 0, 6]],
            'owned then foreign, then distinct owned' => [[0, null, 6, 6, null]],
        ];
    }

    #[DataProvider('sequenceCases')]
    public function test_calls_preserve_full_validation_and_static_lifetime_across_request_contexts(array $sequence): void
    {
        $app = $this->runtime();
        $doc = $this->frozen();
        $hits = [];
        $needed = false;
        foreach ($sequence as $index => $owner) {
            $row = $doc['questions'][$owner ?? 0];
            $q = $this->model($row);
            $payload = $this->originalPayload($row);
            if ($owner === null) {
                $this->assertUnchanged($q, self::EXTERNAL_UUID, $payload);
            } else {
                $needed = true;
                self::assertSame($this->expected($row, $payload), Widget::project($q, $row['block_uuid'], $payload, true));
                $hits[] = $this->pathCount($app, ConditionalsPracticeSupplement::SOURCE);
            }
            self::assertSame($index + 1, $this->pathCount($app, Widget::SOURCE));
            self::assertSame((int) $needed, $this->pathCount($app, ConditionalsPracticeSupplement::SOURCE));
        }
        self::assertSame([1, 1, 1], $hits);
        $after = $this->runtime(); // A distinct Laravel request context in this same PHP process.
        $row = $doc['questions'][6];
        $q = $this->model($row);
        $payload = $this->originalPayload($row);
        self::assertSame($this->expected($row, $payload), Widget::project($q, $row['block_uuid'], $payload, true));
        self::assertSame(1, $this->pathCount($after, Widget::SOURCE));
        self::assertSame(0, $this->pathCount($after, ConditionalsPracticeSupplement::SOURCE), 'The validated document retains its previous PHP static lifetime.');
        $after->override[Widget::SOURCE] = $this->localArtifact('broken between requests');
        $this->assertUnchanged($q, self::EXTERNAL_UUID, $payload);
        $this->assertUnchanged($q, $row['block_uuid'], $payload);
        self::assertSame(3, $this->pathCount($after, Widget::SOURCE), 'A warm document must not bypass the outer bytes check.');
        self::assertSame(0, $this->sqlAttempts);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Conditional widget immutable source differs.');
        Widget::load();
    }

    public static function brokenSourceCases(): array
    {
        return [
            'missing' => ['missing'],
            'wrong SHA valid JSON' => ['changed'],
            'malformed JSON' => ['malformed'],
            'byte-identical symlink' => ['symlink'],
        ];
    }

    #[DataProvider('brokenSourceCases')]
    public function test_outer_source_checks_are_fail_closed_for_foreign_and_owned_inputs(string $problem): void
    {
        $app = $this->runtime();
        $row = $this->frozen()['questions'][0];
        $q = $this->model($row);
        $payload = $this->originalPayload($row);
        $path = $this->localArtifact(match ($problem) {
            'changed' => '{"valid":"different document"}',
            'malformed' => '{"missing"',
            default => null,
        });
        if ($problem === 'symlink' && (!function_exists('symlink') || !@symlink(self::root().'/'.Widget::SOURCE, $path))) {
            self::markTestSkipped('This test process cannot create a symlink on this OS.');
        }
        $app->override[Widget::SOURCE] = $path;
        try {
            $this->assertUnchanged($q, self::EXTERNAL_UUID, $payload);
            $this->assertUnchanged($q, $row['block_uuid'], $payload);
            self::assertSame(2, $this->pathCount($app, Widget::SOURCE));
            self::assertSame(0, $this->pathCount($app, ConditionalsPracticeSupplement::SOURCE));
            self::assertSame(0, $this->sqlAttempts);
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Conditional widget immutable source differs.');
            Widget::load();
        } finally {
            if ($problem === 'symlink' && is_link($path)) {
                unlink($path);
            }
        }
    }

    public function test_first_owned_path_rejects_dependent_corruption_after_a_foreign_call(): void
    {
        $app = $this->runtime();
        $row = $this->frozen()['questions'][0];
        $q = $this->model($row);
        $payload = $this->originalPayload($row);
        $app->override[ConditionalsPracticeSupplement::SOURCE] = $this->localArtifact('{"valid":"unexpected dependent bytes"}');
        $this->assertUnchanged($q, self::EXTERNAL_UUID, $payload);
        self::assertSame(0, $this->pathCount($app, ConditionalsPracticeSupplement::SOURCE));
        $this->assertUnchanged($q, $row['block_uuid'], $payload);
        self::assertSame(1, $this->pathCount($app, ConditionalsPracticeSupplement::SOURCE));
        self::assertSame(0, $this->pathCount($app, ConditionalsPracticeSource::SOURCE));
        $before = $this->beforeClass();
        if ($before !== null) {
            self::assertSame($payload, $before::project($q, $row['block_uuid'], $payload, true));
            self::assertSame(2, $this->pathCount($app, ConditionalsPracticeSupplement::SOURCE));
        }
        self::assertSame(0, $this->sqlAttempts);
    }

    public function test_public_validation_still_checks_ownership_aliases_and_token_labels(): void
    {
        $this->runtime();
        $doc = $this->frozen();
        Widget::validate($doc);
        foreach (['block_uuid' => self::EXTERNAL_UUID, 'accepted_sentences' => ['A fabricated alias'], 'token_labels' => ['incorrect' => 'label']] as $field => $value) {
            $bad = $doc;
            $bad['questions'][0][$field] = $value;
            try {
                Widget::validate($bad);
                self::fail('Public validation accepted a corrupt '.$field.'.');
            } catch (RuntimeException $error) {
                self::assertSame('Conditional widget exact source differs.', $error->getMessage());
            }
        }
        self::assertSame(0, $this->sqlAttempts);
    }
}
