<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Support\ClausesLinkingWidgetHints;
use App\Support\CommonMistakesWidgetHints;
use App\Support\ReportedSpeechWidgetHints;
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

/** Independent frozen expectations, no working DB and no served-file mutation. */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class WidgetMembershipBatchTest extends TestCase
{
    private const EXTERNAL_UUID = 'f0000000-0000-4000-8000-000000000001';
    private const EXTERNAL_ID = 2147483646;
    private const MODEL_FIELDS = ['id', 'uuid', 'seeder', 'type', 'level', 'category_id', 'source_id', 'difficulty', 'flag', 'theory_text_block_uuid', 'question', 'options_by_marker'];
    private const FAMILIES = [
        'reported' => [ReportedSpeechWidgetHints::class, 7, 35, 'reported_speech_widget_hint_revision', 'ReportedSpeechPracticeSupplement', '550ea3ecdc1804520dee4cb9a9c255beab5765a7797f168ebef7b80a48ce93a2'],
        'clauses' => [ClausesLinkingWidgetHints::class, 17, 85, 'clauses_linking_widget_hint_revision', 'ClausesLinkingPracticeSupplement', 'a1f040fd549b82fa0633780c40b4d57686b135df0a464cd36856ca0aabf12b68'],
        'common' => [CommonMistakesWidgetHints::class, 6, 30, 'common_mistakes_widget_hint_revision', 'CommonMistakesPracticeSource', 'ec2eb69dcd29716a5f0fe8de037f1b48a901ca03b63bd01a21b705fff3c88129'],
    ];
    private int $sqlAttempts = 0;

    public static function families(): array
    {
        return array_combine(array_keys(self::FAMILIES), array_map(static fn (string $key): array => [$key], array_keys(self::FAMILIES)));
    }

    private static function root(): string
    {
        return dirname(__DIR__, 2);
    }

    private function frozen(string $family): array
    {
        $widget = self::FAMILIES[$family][0];
        $bytes = file_get_contents(self::root().'/'.$widget::SOURCE);
        self::assertIsString($bytes);
        self::assertSame($widget::SOURCE_SHA, hash('sha256', $bytes));

        return json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    }

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
            'database' => ['default' => 'sqlite', 'connections' => ['sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]],
        ]));
        $app->instance('request', Request::create('/test-only/widget-membership-batch'));
        $database = new DatabaseManager($app, new ConnectionFactory($app));
        $app->instance('db', $database);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
        Question::setConnectionResolver($database);
        $database->connection()->beforeExecuting(function (): void { ++$this->sqlAttempts; });

        return $app;
    }

    private function dependency(string $family): string
    {
        $provider = 'App\\Support\\'.self::FAMILIES[$family][4];

        return $provider::SOURCE;
    }

    private function countPath(Application $app, string $path): int
    {
        return count(array_keys($app->pathCalls, $path, true));
    }

    private function artifact(?string $bytes, string $extension = 'json'): string
    {
        $parent = IsolatedTestEnvironment::prepare().'/app';
        IsolatedTestEnvironment::assertOwnedPath($parent);
        $path = $parent.'/widget-membership-'.bin2hex(random_bytes(12)).'.'.$extension;
        self::assertFileDoesNotExist($path);
        if ($bytes !== null) {
            self::assertSame(strlen($bytes), file_put_contents($path, $bytes, LOCK_EX));
        }

        return $path;
    }

    private function sourceClass(string $family, string $bytes, string $suffix): string
    {
        $name = class_basename(self::FAMILIES[$family][0]);
        $replacement = $name.'Perf12'.$suffix;
        $renamed = str_replace('final class '.$name, 'final class '.$replacement, $bytes, $count);
        self::assertSame(1, $count);
        require $this->artifact($renamed, 'php');

        return 'App\\Support\\'.$replacement;
    }

    /** Optional exact historical executable oracle; class declaration alone is renamed. */
    private function beforeClass(string $family): ?string
    {
        $directory = getenv('GRAMLYZE_PERF12_BEFORE');
        if ($directory === false || $directory === '') {
            return null;
        }
        $expected = self::root().'/storage/logs/perf-12/recovery/ROOT/app/Support';
        self::assertSame(realpath($expected), realpath($directory));
        $path = $directory.'/'.class_basename(self::FAMILIES[$family][0]).'.php';
        self::assertFalse(is_link($path));
        $bytes = file_get_contents($path);
        self::assertIsString($bytes);
        self::assertSame(self::FAMILIES[$family][5], hash('sha256', $bytes));

        return $this->sourceClass($family, $bytes, 'Before');
    }

    private function model(array $row, bool $subclass = false): Question
    {
        $q = $subclass ? new class extends Question {} : new Question;
        $q->incrementing = false; // Preserve malformed identity types instead of coercing a primary key.
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

    private function payload(array $row): array
    {
        return [
            'id' => $row['id'], 'type' => '4', 'level' => $row['level'], 'question' => $row['payload_question'],
            'correct_tokens' => $row['correct_tokens'],
            'answers' => array_map(static fn (array $a): array => ['marker' => $a['marker'], 'correct' => $a['option']], $row['answers']),
            'options' => [...$row['correct_tokens'], 'separate distractor'],
            'accepted_sentences' => ['Retain the existing alias'], 'token_labels' => ['separate distractor' => 'existing caption'],
            'derived_compose_options' => ['separate distractor', 'separate distractor'], 'unrelated' => ['retain' => true],
        ];
    }

    /** Expected fields come from independent frozen content, never the new membership table. */
    private function expected(string $family, array $row, array $original, ?array $state = null): array
    {
        $widget = self::FAMILIES[$family][0];
        $state ??= $row;
        $expected = $original;
        $revision = hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        if (($row['hint_only'] ?? false) === true) {
            $expected['source_bound_compose_hint'] = true;
            $expected['compose_preanswer_hint'] = $row['hint_uk'];
            $expected[self::FAMILIES[$family][3]] = $revision;
            return $expected;
        }
        $labels = array_intersect_key($row['token_labels'], array_fill_keys($state['correct_tokens'], true));
        if ($family === 'clauses' && $row['id'] === 44665) {
            $labels['bike'] = $labels['bike,']; // One exact frozen display alias; not a normalizer.
        }
        $expected['question'] = $row['display_question'];
        $expected['compose_prompt_parts'] = $row['display_parts'];
        $expected['compose_prompt_revision'] = $widget::SOURCE_SHA;
        $expected['source_bound_compose_hint'] = true;
        $expected['compose_preanswer_hint'] = $row['hint_uk'];
        if ($row['accepted_sentences'] !== []) {
            $expected['accepted_sentences'] = array_values(array_unique([implode(' ', $state['correct_tokens']), ...$original['accepted_sentences'], ...$row['accepted_sentences']]));
        }
        $expected['token_labels'] = array_replace($original['token_labels'] ?? [], $labels);
        $expected['derived_compose_options'] = array_values(array_unique([...$original['derived_compose_options'], ...$row['derived_options']]));
        $expected[self::FAMILIES[$family][3]] = $revision;

        return $expected;
    }

    private function unchanged(string $family, Question $q, ?string $uuid, array $payload, bool $caller = true): void
    {
        $widget = self::FAMILIES[$family][0];
        $before = serialize([$q->getAttributes(), $q->getRawOriginal(), $q->getRelations()]);
        self::assertSame($payload, $widget::project($q, $uuid, $payload, $caller));
        self::assertSame($before, serialize([$q->getAttributes(), $q->getRawOriginal(), $q->getRelations()]));
    }

    #[DataProvider('families')]
    public function test_registry_is_exactly_both_independent_source_pair_sets(string $family): void
    {
        [$widget, $owners, $rows] = self::FAMILIES[$family];
        $doc = $this->frozen($family);
        $targets = $questions = $ids = [];
        foreach ($doc['targets'] as $target) {
            $uuid = $target['binding']['uuid'];
            self::assertIsString($uuid);
            self::assertArrayNotHasKey($uuid, $targets);
            $targets[$uuid] = $target['selected_ids'];
            self::assertCount(5, $targets[$uuid]);
            foreach ($targets[$uuid] as $id) {
                self::assertIsInt($id);
                self::assertGreaterThan(0, $id);
            }
        }
        foreach ($doc['questions'] as $row) {
            self::assertIsInt($row['id']);
            self::assertArrayNotHasKey($row['id'], $ids);
            self::assertSame('4', $row['type']);
            $ids[$row['id']] = true;
            $questions[$row['block_uuid']][] = $row['id'];
            $owner = array_values(array_filter($doc['targets'], static fn (array $t): bool => $t['binding']['uuid'] === $row['block_uuid']));
            self::assertCount(1, $owner);
            self::assertSame($owner[0]['binding']['level'], $row['level']);
            self::assertContains($row['seeder'], $owner[0]['bank_seeders']);
            self::assertContains($row['id'], $owner[0]['selected_ids']);
        }
        self::assertCount($owners, $targets);
        self::assertCount($rows, $ids);
        self::assertSame($targets, $questions);
        $registry = (new ReflectionClass($widget))->getReflectionConstant('MEMBERSHIP')->getValue();
        self::assertSame($widget::SOURCE_SHA, $registry['source_sha']);
        foreach ([$targets, $registry['pairs']] as $index => $matrix) {
            ksort($matrix, SORT_STRING);
            foreach ($matrix as &$selected) { sort($selected, SORT_NUMERIC); }
            unset($selected);
            if ($index === 0) { $expected = $matrix; } else { self::assertSame($expected, $matrix); }
        }
        $verify = new ReflectionMethod($widget, 'verifiedMembership');
        self::assertNotNull($verify->invoke(null, $registry));
        $a = array_key_first($registry['pairs']);
        $b = array_keys($registry['pairs'])[1];
        $missing = $extra = $duplicate = $wrongOwner = $wrongType = $registry;
        array_pop($missing['pairs'][$a]);
        $extra['pairs'][$a][] = self::EXTERNAL_ID;
        $duplicate['pairs'][$a][1] = $duplicate['pairs'][$a][0];
        [$wrongOwner['pairs'][$a][0], $wrongOwner['pairs'][$b][0]] = [$wrongOwner['pairs'][$b][0], $wrongOwner['pairs'][$a][0]];
        $wrongType['pairs'][$a][0] = (string) $wrongType['pairs'][$a][0];
        foreach ([$missing, $extra, $duplicate, $wrongOwner, $wrongType,
            array_replace($registry, ['source_sha' => str_repeat('0', 64)]), ['source_sha' => $widget::SOURCE_SHA],
            array_replace($registry, ['pairs' => []]), array_replace($registry, ['unknown' => true]), null, 'invalid registry'] as $bad) {
            self::assertNull($verify->invoke(null, $bad));
        }
    }

    #[DataProvider('families')]
    public function test_every_owned_pair_keeps_both_pinned_states_exact_payload_and_models(string $family): void
    {
        $widget = self::FAMILIES[$family][0];
        $app = $this->runtime();
        $doc = $this->frozen($family);
        $before = $this->beforeClass($family);
        $projections = [];
        $hintOnly = $aliases = $labels = 0;
        foreach ($doc['questions'] as $row) {
            foreach ([$row, $row['canonical_server_snapshot']] as $state) {
                $q = $this->model($state);
                $payload = $this->payload($state);
                $raw = serialize([$q->getAttributes(), $q->getRawOriginal(), $q->getRelations()]);
                self::assertSame(array_intersect_key($state, array_flip($widget::FIELDS)), $widget::prepared($q));
                $actual = $widget::project($q, $row['block_uuid'], $payload, true);
                self::assertSame($this->expected($family, $row, $payload, $state), $actual);
                self::assertSame($raw, serialize([$q->getAttributes(), $q->getRawOriginal(), $q->getRelations()]));
                if (($row['hint_only'] ?? false) === true) {
                    ++$hintOnly;
                    foreach (['question', 'correct_tokens', 'answers', 'options', 'accepted_sentences', 'token_labels', 'derived_compose_options'] as $key) {
                        self::assertSame($payload[$key], $actual[$key]);
                    }
                    self::assertArrayNotHasKey('compose_prompt_parts', $actual);
                    self::assertArrayNotHasKey('compose_prompt_revision', $actual);
                } else {
                    self::assertSame($row['display_parts'], $actual['compose_prompt_parts']);
                    self::assertSame('instruction', $actual['compose_prompt_parts'][1]['kind']);
                    self::assertStringStartsWith(' ', $actual['compose_prompt_parts'][1]['text']);
                    self::assertSame($row['display_question'], implode('', array_column($actual['compose_prompt_parts'], 'text')));
                }
                $aliases += (int) ($row['accepted_sentences'] !== []);
                $labels += count($row['token_labels']);
                $projections[] = [$row, $state, $payload, $actual];
            }
        }
        self::assertCount(self::FAMILIES[$family][2] * 2, $projections);
        self::assertGreaterThan(0, $aliases);
        self::assertGreaterThan(0, $labels);
        self::assertSame($family === 'common' ? 10 : 0, $hintOnly);
        self::assertSame(count($projections), $this->countPath($app, $widget::SOURCE));
        self::assertGreaterThan(0, $this->countPath($app, $this->dependency($family)));
        if ($before !== null) {
            foreach ($projections as [$row, $state, $payload, $actual]) {
                self::assertSame($actual, $before::project($this->model($state), $row['block_uuid'], $payload, true));
            }
        }
        self::assertSame(0, $this->sqlAttempts);
    }

    #[DataProvider('families')]
    public function test_all_cross_owner_identities_and_actual_foreign_pairs_keep_only_outer_integrity(string $family): void
    {
        $widget = self::FAMILIES[$family][0];
        $app = $this->runtime();
        $doc = $this->frozen($family);
        $row = $doc['questions'][0];
        $payload = $this->payload($row);
        $owned = array_map(static fn (array $q): array => [$q['block_uuid'], $q['id']], $doc['questions']);
        $cases = [[self::EXTERNAL_UUID, self::EXTERNAL_ID], [$row['block_uuid'], self::EXTERNAL_ID], [self::EXTERNAL_UUID, $row['id']]];
        $allRows = array_merge(...array_map(fn (string $key): array => $this->frozen($key)['questions'], array_keys(self::FAMILIES)));
        $allUuids = array_unique(array_column($allRows, 'block_uuid'));
        foreach ($allUuids as $uuid) {
            foreach ($allRows as $other) {
                if (!in_array([$uuid, $other['id']], $owned, true)) { $cases[] = [$uuid, $other['id']]; }
            }
        }
        $receipt = getenv('GRAMLYZE_PERF12_CASE_RECEIPT');
        if ($receipt !== false && $receipt !== '') {
            $expected = self::root().'/storage/logs/perf-12/triple-actual-foreign.json';
            self::assertSame(realpath($expected), realpath($receipt));
            $actual = json_decode(file_get_contents($receipt), true, flags: JSON_THROW_ON_ERROR);
            self::assertSame(1, $actual['schema']);
            $source = array_values(array_filter($actual['families'], static fn (array $item): bool => $item['family'] === $family));
            self::assertCount(1, $source);
            self::assertSame($widget::SOURCE_SHA, $source[0]['sourceSha']);
            foreach (['word', 'ppc'] as $key) {
                self::assertCount(5, $actual['controls'][$key][$family]);
                foreach ($actual['controls'][$key][$family] as $pair) {
                    self::assertIsString($pair['blockUuid']);
                    self::assertIsInt($pair['id']);
                    $cases[] = [$pair['blockUuid'], $pair['id']];
                }
            }
        }
        foreach ($cases as [$uuid, $id]) {
            self::assertFalse(in_array([$uuid, $id], $owned, true));
            $this->unchanged($family, $this->model(array_replace($row, ['id' => $id])), $uuid, $payload);
        }
        self::assertSame(count($cases), $this->countPath($app, $widget::SOURCE));
        self::assertSame(0, $this->countPath($app, $this->dependency($family)));
        foreach (array_keys($widget::PRESENTATION_SOURCES) as $source) {
            self::assertSame(0, $this->countPath($app, $source));
        }
        self::assertSame(0, $this->sqlAttempts);
    }

    #[DataProvider('families')]
    public function test_missing_stale_and_malformed_registry_use_full_original_project_path(string $family): void
    {
        $widget = self::FAMILIES[$family][0];
        $app = $this->runtime();
        $row = $this->frozen($family)['questions'][0];
        $payload = $this->payload($row);
        $bytes = file_get_contents((new ReflectionClass($widget))->getFileName());
        $missing = str_replace('private const MEMBERSHIP =', 'private const UNAVAILABLE_MEMBERSHIP =', $bytes, $count);
        self::assertSame(1, $count);
        $wrong = preg_replace("/private const MEMBERSHIP_FINGERPRINT = '[a-f0-9]{64}';/", "private const MEMBERSHIP_FINGERPRINT = '".str_repeat('0', 64)."';", $bytes, 1, $count);
        self::assertSame(1, $count);
        $malformed = preg_replace('/private const MEMBERSHIP\s*=\s*.*?;/s', "private const MEMBERSHIP = ['source_sha' => 'stale', 'pairs' => 'invalid'];", $bytes, 1, $count);
        self::assertSame(1, $count);
        foreach (['Missing' => $missing, 'WrongFingerprint' => $wrong, 'Malformed' => $malformed] as $suffix => $variantBytes) {
            $variant = $this->sourceClass($family, $variantBytes, $suffix);
            $beforeCount = $this->countPath($app, $this->dependency($family));
            self::assertSame($payload, $variant::project($this->model($row), self::EXTERNAL_UUID, $payload, true));
            self::assertGreaterThan($beforeCount, $this->countPath($app, $this->dependency($family)));
            self::assertSame($this->expected($family, $row, $payload), $variant::project($this->model($row), $row['block_uuid'], $payload, true));
        }
        self::assertSame(6, $this->countPath($app, $widget::SOURCE));
        self::assertSame(0, $this->sqlAttempts);
    }

    #[DataProvider('families')]
    public function test_existing_scope_and_uncertain_identity_guards_remain_conservative(string $family): void
    {
        $widget = self::FAMILIES[$family][0];
        $app = $this->runtime();
        $row = $this->frozen($family)['questions'][0];
        $payload = $this->payload($row);
        $q = $this->model($row);
        $this->unchanged($family, $q, $row['block_uuid'], $payload, false);
        foreach (['en', 'pl'] as $locale) {
            $app['config']->set('app.locale', $locale);
            $this->unchanged($family, $q, $row['block_uuid'], $payload);
        }
        $app['config']->set('app.locale', 'uk');
        $this->unchanged($family, $this->model(array_replace($row, ['type' => '1'])), $row['block_uuid'], $payload);
        foreach ([4, '0', null] as $type) { $this->unchanged($family, $q, $row['block_uuid'], array_replace($payload, ['type' => $type])); }
        self::assertSame($payload, $widget::project((object) ['type' => '4', 'id' => $row['id']], $row['block_uuid'], $payload, true));
        self::assertSame(0, $this->countPath($app, $widget::SOURCE));
        $this->unchanged($family, $this->model($row, true), self::EXTERNAL_UUID, $payload);
        self::assertGreaterThan(0, $this->countPath($app, $this->dependency($family)), 'A Question subclass cannot receive a proven-foreign shortcut.');
        $foreign = new ReflectionMethod($widget, 'isProvenForeign');
        foreach ([[null, 1], ['', 1], ['malformed', 1], [' '.self::EXTERNAL_UUID, 1], [strtoupper(self::EXTERNAL_UUID), 1],
            [self::EXTERNAL_UUID, null], [self::EXTERNAL_UUID, 0], [self::EXTERNAL_UUID, -1], [self::EXTERNAL_UUID, '1'], [self::EXTERNAL_UUID, 1.0], [self::EXTERNAL_UUID, true]] as [$uuid, $id]) {
            self::assertFalse($foreign->invoke(null, $uuid, $id));
        }
        foreach ([null, '', 'malformed', strtoupper(self::EXTERNAL_UUID)] as $uuid) { $this->unchanged($family, $q, $uuid, $payload); }
        foreach ([null, 0, -1, (string) $row['id'], 1.0, true] as $id) {
            $bad = $this->model(array_replace($row, ['id' => $id]));
            self::assertSame($id, $bad->id);
            $this->unchanged($family, $bad, $row['block_uuid'], $payload);
        }
        self::assertSame($this->expected($family, $row, $payload), $widget::project($this->model($row, true), $row['block_uuid'], $payload, true));
        self::assertSame(0, $this->sqlAttempts);
    }

    #[DataProvider('families')]
    public function test_owned_snapshots_relations_hints_and_multiplicity_cannot_be_bypassed(string $family): void
    {
        $this->runtime();
        $doc = $this->frozen($family);
        $withHints = array_values(array_filter($doc['questions'], static fn (array $r): bool => $r['hints'] !== []));
        $row = $withHints[0] ?? $doc['questions'][0];
        $before = $this->beforeClass($family);
        $mutations = [
            static fn (Question $q) => $q->question .= ' changed',
            static fn (Question $q) => $q->answers->first()->marker = 'incorrect',
            static fn (Question $q) => $q->answers->first()->option->option = 'incorrect',
            static fn (Question $q) => $q->options->first()->option = 'incorrect',
            static function (Question $q): void {
                if ($q->hints->isNotEmpty()) { $q->hints->first()->hint = 'Інша підказка'; return; }
                $hint = new QuestionHint;
                $hint->setRawAttributes(['id' => 1, 'provider' => 'fixture', 'locale' => 'uk', 'hint' => 'Стороння підказка'], true);
                $q->setRelation('hints', collect([$hint]));
            },
            static fn (Question $q) => $q->unsetRelation('answers'),
            static fn (Question $q) => $q->unsetRelation('options'),
            static fn (Question $q) => $q->unsetRelation('hints'),
            static fn (Question $q) => $q->unsetRelation('verbHints'),
            static fn (Question $q) => $q->answers->first()->unsetRelation('option'),
            static fn (Question $q) => $q->setRawAttributes(array_replace($q->getAttributes(), ['options_by_marker' => '{}']), true),
            static fn (Question $q) => $q->setRawAttributes(array_replace($q->getAttributes(), ['difficulty' => 'changed']), true),
            static fn (Question $q) => $q->setRawAttributes(array_replace($q->getAttributes(), ['flag' => 'changed']), true),
            static fn (Question $q) => $q->setRawAttributes(array_replace($q->getAttributes(), ['theory_text_block_uuid' => self::EXTERNAL_UUID]), true),
            static function (Question $q): void {
                $hint = new VerbHint;
                $hint->setRawAttributes(['id' => 1, 'marker' => 'a1', 'locale' => 'uk', 'option_id' => 1], true);
                $option = new QuestionOption;
                $option->setRawAttributes(['id' => 1, 'option' => 'Стороння підказка'], true);
                $hint->setRelation('option', $option);
                $q->setRelation('verbHints', collect([$hint]));
            },
            static function (Question $q): void {
                $hint = new VerbHint;
                $hint->setRawAttributes(['id' => 1, 'marker' => 'a1', 'locale' => 'uk', 'option_id' => 1], true);
                $q->setRelation('verbHints', collect([$hint])); // An attached hint with its option relation absent is untrusted too.
            },
        ];
        foreach ([$row, $row['canonical_server_snapshot']] as $state) {
            $payload = $this->payload($state);
            foreach ($mutations as $mutate) {
                $q = $this->model($state);
                $mutate($q);
                $this->unchanged($family, $q, $row['block_uuid'], $payload);
                if ($before !== null) { self::assertSame($payload, $before::project($q, $row['block_uuid'], $payload, true)); }
            }
            foreach (['id' => -1, 'level' => 'invalid', 'question' => 'invalid', 'answers' => [], 'correct_tokens' => [], 'options' => null] as $field => $value) {
                $this->unchanged($family, $this->model($state), $row['block_uuid'], array_replace($payload, [$field => $value]));
            }
        }
        $repeated = array_values(array_filter($doc['questions'], static fn (array $r): bool => count(array_unique($r['correct_tokens'])) < count($r['correct_tokens'])))[0] ?? null;
        self::assertIsArray($repeated, 'Exercise the actual repeated-token multiset, not a synthetic set.');
        foreach ([$repeated, $repeated['canonical_server_snapshot']] as $state) {
            $payload = $this->payload($state);
            $this->unchanged($family, $this->model($state), $repeated['block_uuid'], array_replace($payload, ['options' => array_values(array_unique($payload['options']))]));
        }
        self::assertSame(0, $this->sqlAttempts);
    }

    public static function sequences(): array
    {
        $out = [];
        foreach (array_keys(self::FAMILIES) as $family) {
            $out[$family.' / foreign-first'] = [$family, [null, null, [0, false], null, [0, true], [6, true]]];
            $out[$family.' / owned-first'] = [$family, [[0, false], null, [6, true], [6, false], null]];
        }

        return $out;
    }

    #[DataProvider('sequences')]
    public function test_foreign_owned_repeats_and_new_request_retain_original_static_lifetime(string $family, array $sequence): void
    {
        $widget = self::FAMILIES[$family][0];
        $app = $this->runtime();
        $doc = $this->frozen($family);
        $loadedAt = null;
        foreach ($sequence as $index => $owner) {
            $row = $doc['questions'][$owner[0] ?? 0];
            $state = ($owner[1] ?? false) ? $row['canonical_server_snapshot'] : $row;
            $payload = $this->payload($state);
            if ($owner === null) {
                $this->unchanged($family, $this->model($state), self::EXTERNAL_UUID, $payload);
            } else {
                self::assertSame($this->expected($family, $row, $payload, $state), $widget::project($this->model($state), $row['block_uuid'], $payload, true));
                $loadedAt ??= $this->countPath($app, $this->dependency($family));
            }
            self::assertSame($index + 1, $this->countPath($app, $widget::SOURCE));
            self::assertSame($loadedAt ?? 0, $this->countPath($app, $this->dependency($family)));
        }
        self::assertGreaterThan(0, $loadedAt);
        $next = $this->runtime();
        $row = $doc['questions'][6];
        $state = $row['canonical_server_snapshot'];
        $payload = $this->payload($state);
        self::assertSame($this->expected($family, $row, $payload, $state), $widget::project($this->model($state), $row['block_uuid'], $payload, true));
        self::assertSame(1, $this->countPath($next, $widget::SOURCE));
        self::assertSame(0, $this->countPath($next, $this->dependency($family)));
        $next->override[$widget::SOURCE] = $this->artifact('broken after validated cache');
        $this->unchanged($family, $this->model($state), self::EXTERNAL_UUID, $payload);
        $this->unchanged($family, $this->model($state), $row['block_uuid'], $payload);
        self::assertSame(3, $this->countPath($next, $widget::SOURCE));
        self::assertSame(0, $this->sqlAttempts);
        $this->expectException(RuntimeException::class);
        $widget::load();
    }

    public static function brokenSources(): array
    {
        $out = [];
        foreach (array_keys(self::FAMILIES) as $family) {
            foreach (['missing', 'changed', 'malformed', 'symlink'] as $problem) { $out[$family.' / '.$problem] = [$family, $problem]; }
        }

        return $out;
    }

    #[DataProvider('brokenSources')]
    public function test_outer_source_failures_fallback_in_project_but_throw_in_public_load(string $family, string $problem): void
    {
        $widget = self::FAMILIES[$family][0];
        $app = $this->runtime();
        $row = $this->frozen($family)['questions'][0];
        $payload = $this->payload($row);
        $path = $this->artifact(match ($problem) { 'changed' => '{"different":"valid JSON"}', 'malformed' => '{"unfinished"', default => null });
        if ($problem === 'symlink' && (!function_exists('symlink') || !@symlink(self::root().'/'.$widget::SOURCE, $path))) {
            self::markTestSkipped('This OS/process does not allow creating a symlink.');
        }
        $app->override[$widget::SOURCE] = $path;
        try {
            $this->unchanged($family, $this->model($row), self::EXTERNAL_UUID, $payload);
            $this->unchanged($family, $this->model($row), $row['block_uuid'], $payload);
            self::assertSame(2, $this->countPath($app, $widget::SOURCE));
            self::assertSame(0, $this->countPath($app, $this->dependency($family)));
            self::assertSame(0, $this->sqlAttempts);
            $this->expectException(RuntimeException::class);
            $widget::load();
        } finally {
            if ($problem === 'symlink' && is_link($path)) { unlink($path); }
        }
    }

    public static function presentationSources(): array
    {
        $out = [];
        foreach (self::FAMILIES as $family => [$widget]) {
            foreach (array_keys($widget::PRESENTATION_SOURCES) as $path) {
                foreach ([false, true] as $canonical) { $out[$family.' / '.$path.' / '.($canonical ? 'canonical' : 'original')] = [$family, $path, $canonical]; }
            }
        }

        return $out;
    }

    #[DataProvider('presentationSources')]
    public function test_foreign_does_not_certify_dependent_presenters_or_audits(string $family, string $path, bool $canonical): void
    {
        $widget = self::FAMILIES[$family][0];
        $app = $this->runtime();
        $doc = $this->frozen($family);
        $row = $doc['questions'][0];
        $state = $canonical ? $row['canonical_server_snapshot'] : $row;
        $payload = $this->payload($state);
        $app->override[$path] = $this->artifact('Changed approved dependent bytes');
        $this->unchanged($family, $this->model($state), self::EXTERNAL_UUID, $payload);
        self::assertSame(0, $this->countPath($app, $this->dependency($family)));
        $this->unchanged($family, $this->model($state), $row['block_uuid'], $payload);
        self::assertGreaterThan(0, $this->countPath($app, $path));
        self::assertSame(0, $this->sqlAttempts);
        $this->expectException(RuntimeException::class);
        $widget::validate($doc);
    }

    #[DataProvider('families')]
    public function test_first_owned_after_foreign_still_checks_full_primary_dependency(string $family): void
    {
        $widget = self::FAMILIES[$family][0];
        $app = $this->runtime();
        $row = $this->frozen($family)['questions'][0];
        $payload = $this->payload($row);
        $app->override[$this->dependency($family)] = $this->artifact('{"different":"dependent bytes"}');
        $this->unchanged($family, $this->model($row), self::EXTERNAL_UUID, $payload);
        self::assertSame(0, $this->countPath($app, $this->dependency($family)));
        $this->unchanged($family, $this->model($row), $row['block_uuid'], $payload);
        self::assertGreaterThan(0, $this->countPath($app, $this->dependency($family)));
        self::assertSame(0, $this->sqlAttempts);
        $this->expectException(RuntimeException::class);
        $widget::validate($this->frozen($family));
    }

    #[DataProvider('families')]
    public function test_public_validation_still_rejects_exact_snapshot_alias_and_label_mutations(string $family): void
    {
        $widget = self::FAMILIES[$family][0];
        $this->runtime();
        $doc = $this->frozen($family);
        $widget::validate($doc);
        foreach (['block_uuid' => self::EXTERNAL_UUID, 'accepted_sentences' => ['Fabricated alias'], 'token_labels' => ['incorrect' => 'caption'],
            'display_parts' => [['kind' => 'sentence', 'text' => 'Changed boundary']], 'canonical_server_snapshot' => []] as $field => $value) {
            $bad = $doc;
            $bad['questions'][0][$field] = $value;
            try {
                $widget::validate($bad);
                self::fail('Public validation accepted corrupted '.$field);
            } catch (RuntimeException $error) {
                self::assertNotSame('', $error->getMessage());
            }
        }
        self::assertSame(0, $this->sqlAttempts);
    }

    public function test_clauses_exact_bike_display_alias_and_conflicts_are_not_generalized(): void
    {
        $this->runtime();
        $row = array_values(array_filter($this->frozen('clauses')['questions'], static fn (array $r): bool => $r['id'] === 44665))[0];
        $before = $this->beforeClass('clauses');
        foreach ([$row, $row['canonical_server_snapshot']] as $state) {
            $payload = $this->payload($state);
            $actual = ClausesLinkingWidgetHints::project($this->model($state), $row['block_uuid'], $payload, true);
            self::assertSame($row['token_labels']['bike,'], $actual['token_labels']['bike,']);
            self::assertSame($actual['token_labels']['bike,'], $actual['token_labels']['bike']);
            self::assertArrayNotHasKey('Bike', $actual['token_labels']);
            $conflict = array_replace($payload, ['token_labels' => ['bike' => 'conflicting caption']]);
            $this->unchanged('clauses', $this->model($state), $row['block_uuid'], $conflict);
            $same = array_replace($payload, ['token_labels' => ['bike' => $row['token_labels']['bike,']]]);
            self::assertSame($this->expected('clauses', $row, $same, $state), ClausesLinkingWidgetHints::project($this->model($state), $row['block_uuid'], $same, true));
            $this->unchanged('clauses', $this->model($state), $row['block_uuid'], array_replace($payload, ['token_labels' => 'malformed labels']));
            // The established null-coalescing rule treats null exactly like an absent map.
            $nullLabels = array_replace($payload, ['token_labels' => null]);
            $projected = ClausesLinkingWidgetHints::project($this->model($state), $row['block_uuid'], $nullLabels, true);
            self::assertSame($this->expected('clauses', $row, $nullLabels, $state), $projected);
            if ($before !== null) { self::assertSame($projected, $before::project($this->model($state), $row['block_uuid'], $nullLabels, true)); }
        }
        self::assertSame(0, $this->sqlAttempts);
    }

    public function test_real_blade_order_chain_preserves_final_payload_against_exact_old_classes(): void
    {
        $this->runtime();
        $blade = file_get_contents(self::root().'/resources/views/components/text-block-practice-questions.blade.php');
        self::assertSame('5197c946eb6f8b28d7278fdd376843bb5a56eb5130ba2f6375af518bd91a6d92', hash('sha256', $blade), 'The shared renderer and its ordering are unchanged.');
        preg_match_all('/\\$payload = \\\\App\\\\Support\\\\([A-Za-z]+)::(projectWidget|project)\\(([^\\r\\n]*)\\);/', $blade, $matches, PREG_SET_ORDER);
        preg_match_all('/return \\\\App\\\\Support\\\\([A-Za-z]+)::(project)\\(\\$q, \\$blockUuid, \\$payload, \\$canonicalTheoryCaller === true\\);/', $blade, $terminal, PREG_SET_ORDER);
        self::assertCount(1, $terminal, 'Include the actual terminal return, not only intermediate assignments.');
        $matches = array_merge($matches, $terminal);
        $order = array_column($matches, 1);
        self::assertSame([
            'WordOrderPracticeSupplement', 'NounsArticlesQuantityPracticeSupplement', 'PronounsDemonstrativesPracticeSupplement',
            'FutureContinuousVsFuturePerfectContinuousPracticeSupplement', 'QuestionsNegationsPracticeSupplement',
            'AdjectivesAdverbsPracticeSupplement', 'SomeAnyPracticeSupplement', 'PassiveVoicePracticeSupplement',
            'ModalVerbsWidgetHints', 'ConditionalsWidgetHints', 'TheorySentenceBuilderPresentation',
            'ReportedSpeechWidgetHints', 'ClausesLinkingWidgetHints', 'PrepositionsPhrasalWidgetHints', 'CommonMistakesWidgetHints',
            'SentenceTransformationsWidgetHints', 'VerbPatternsWidgetHints', 'MixedRevisionWidgetHints', 'AcademicEnglishWidgetHints',
            'ArticlesQuantifiersWidgetHints', 'FormalEnglishWidgetHints', 'RelativeClausesWidgetHints', 'SentenceStructureWidgetHints',
            'VocabCollocationsWidgetHints',
        ], $order, 'Use the whole actual shared chain, including all earlier and later callers.');
        $old = [];
        foreach (self::FAMILIES as $family => [$widget]) {
            $old[$widget] = $this->beforeClass($family);
            if ($old[$widget] === null) { self::markTestSkipped('Exact private old sources are not installed in this portable test context.'); }
        }
        foreach (array_keys(self::FAMILIES) as $family) {
            foreach ($this->frozen($family)['questions'] as $row) {
                foreach ([$row, $row['canonical_server_snapshot']] as $state) {
                    $q = $this->model($state);
                    $snapshot = serialize([$q->getAttributes(), $q->getRawOriginal(), $q->getRelations()]);
                    $a = $b = $this->payload($state);
                    foreach ($matches as [, $short, $method]) {
                        $class = 'App\\Support\\'.$short;
                        $before = $old[$class] ?? $class;
                        if ($short === 'TheorySentenceBuilderPresentation') {
                            $a = $before::$method($a, true, 'uk');
                            $b = $class::$method($b, true, 'uk');
                        } else {
                            $a = $before::$method($q, $row['block_uuid'], $a, true);
                            $b = $class::$method($q, $row['block_uuid'], $b, true);
                        }
                        self::assertSame($a, $b, $family.' '.$row['id'].' after '.$short);
                    }
                    self::assertSame($snapshot, serialize([$q->getAttributes(), $q->getRawOriginal(), $q->getRelations()]));
                }
            }
        }
        self::assertSame(0, $this->sqlAttempts);
    }
}
