<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Support\NounsArticlesQuantityPracticeSupplement;
use App\Support\PassiveVoiceWidgetHints;
use App\Support\PronounsDemonstrativesPracticeSupplement;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;

/**
 * Frozen widget projections with fresh per-family loader state, backed only by the
 * checkout's versioned sources. No kernel, network, or database is booted.
 */
final class ReleasedWidgetMembershipTest extends TestCase
{
    private const FOREIGN_BLOCK = 'f0010000-0000-4000-8000-000000000011';
    private const FOREIGN_ID = 2147483646;
    private const FOREIGN_UUID = 'f0020000-0000-4000-8000-000000000012';
    private const FOREIGN_SEEDER = 'Database\\Seeders\\V3\\Polyglot\\ForeignSyntheticControlSeeder';
    private string $root;
    private ?Container $previousApp = null;
    private ?Container $previousFacade = null;
    private ?ConnectionResolverInterface $previousResolver = null;
    private int $sqlAttempts = 0;

    public static function families(): iterable
    {
        yield 'Passive Voice' => [
            PassiveVoiceWidgetHints::class, 'docs/content/passive-voice-widget-hints.v1.json',
            '090795fad9d0c21add94d5f42e8322e7f6e60783d4f775df55888e36e7995adb',
            'pv_widget_hint_revision', 135, 135,
        ];
        yield 'Pronouns / Demonstratives' => [
            PronounsDemonstrativesPracticeSupplement::class,
            'docs/content/pronouns-demonstratives-practice-supplement.v1.json',
            '810422870be39372f35d1b85e87fa3c9f06fe7b6ac0a66ec13e7889930b5d0aa',
            'pdq_widget_alias_revision', 9, 6,
        ];
        yield 'Nouns / Articles / Quantity' => [
            NounsArticlesQuantityPracticeSupplement::class,
            'docs/content/nouns-articles-quantity-practice-supplement.v1.json',
            '51b50d2140d1297716017473c51dab12703b7ad112058dd1265c03fb01ecaa47',
            'naq_widget_alias_revision', 4, 4,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousApp = Container::getInstance();
        $this->previousFacade = Facade::getFacadeApplication();
        $this->previousResolver = Question::getConnectionResolver();
        $this->root = dirname((new ReflectionClass(Question::class))->getFileName(), 3);
        $app = new Application($this->root);
        $app->instance('config', new Repository(['app' => ['locale' => 'uk']]));
        $app->instance('request', Request::create('http://gramlyze.loc/theory/word-order'));
        $reject = function (): never {
            ++$this->sqlAttempts;
            throw new RuntimeException('Widget membership must not access the database.');
        };
        $app->bind('db', $reject);
        Question::setConnectionResolver(new class($reject) implements ConnectionResolverInterface
        {
            public function __construct(private \Closure $reject) {}

            public function connection($name = null) { ($this->reject)(); }

            public function getDefaultConnection() { return 'never'; }

            public function setDefaultConnection($name) {}
        });
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
    }

    protected function tearDown(): void
    {
        if ($this->previousResolver === null) {
            Question::unsetConnectionResolver();
        } else {
            Question::setConnectionResolver($this->previousResolver);
        }
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousFacade);
        Container::setInstance($this->previousApp);
        self::assertSame(0, $this->sqlAttempts);
        parent::tearDown();
    }

    #[DataProvider('families')]
    public function test_frozen_membership_and_dependency_pins_match_versioned_sources(
        string $class, string $source, string $sha, string $revision, int $rows, int $positiveRows,
    ): void {
        $document = $this->document($source, $sha);
        $decl = new ReflectionClass($class);
        $passive = self::isPassive($class);
        $registry = $this->registry($class);
        $pairs = $uuids = $banks = $seen = [];
        $frozenRows = $document[$passive ? 'questions' : 'widgets'];
        self::assertCount($rows, $frozenRows);
        foreach ($frozenRows as $row) {
            self::assertIsInt($row['id']);
            self::assertGreaterThan(0, $row['id']);
            self::assertArrayNotHasKey($row['id'], $seen);
            $seen[$row['id']] = $row;
            $pairs[$row['block_uuid']][] = $row['id'];
            $uuids[$row['uuid']] = true;
            $banks[$row['seeder']] = true;
        }
        self::assertSame($sha, $registry['source_sha']);
        self::assertSame($this->orderedPairs($pairs), $this->orderedPairs($registry['pairs']));
        $flags = JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR;
        if ($passive) {
            self::assertCount(27, $document['targets']);
            $bound = [];
            foreach ($document['targets'] as $target) {
                $uuid = $target['binding']['uuid'];
                self::assertArrayNotHasKey($uuid, $bound);
                self::assertCount(5, $target['selected_ids']);
                $bound[$uuid] = $target['selected_ids'];
                foreach ($target['selected_ids'] as $id) {
                    self::assertSame($uuid, $seen[$id]['block_uuid']);
                    self::assertSame($target['binding']['level'], $seen[$id]['level']);
                    self::assertContains($seen[$id]['seeder'], $target['bank_seeders']);
                }
            }
            self::assertSame($bound, $registry['pairs']);
            $fingerprint = $decl->getConstant('MEMBERSHIP_FINGERPRINT');
        } else {
            $flags |= JSON_UNESCAPED_SLASHES;
            $fingerprint = $decl->getConstant('WIDGET_MEMBERSHIP_SHA');
            self::assertEqualsCanonicalizing(array_keys($uuids), $registry['widgetUuids']);
            self::assertEqualsCanonicalizing(array_keys($banks), $registry['bankSeeders']);
            $mandatory = [];
            foreach (['targets' => ['definition', 'release'], 'widgets' => ['definition', 'audit']] as $group => $kinds) {
                foreach ($document[$group] as $item) {
                    foreach ($kinds as $kind) {
                        $mandatory[] = [$item[$kind], $item[$kind.'_sha256']];
                    }
                }
            }
            self::assertSame($mandatory, $registry['mandatory'], 'Keep ordered duplicate pins.');
            self::assertNotEmpty(array_diff_key(array_column($registry['cold'], 1, 0), array_column($mandatory, 1, 0)));
            foreach ([...$mandatory, ...$registry['cold']] as [$path, $pin]) {
                self::assertSame($pin, hash('sha256', $this->bytes($path)), $path);
            }
        }
        self::assertSame($fingerprint, hash('sha256', json_encode($registry, $flags)));
    }

    #[DataProvider('families')]
    public function test_owned_snapshot_uses_exact_projection_without_mutating_the_model(
        string $class, string $source, string $sha, string $revision, int $rows, int $positiveRows,
    ): void {
        $document = $this->document($source, $sha);
        $passive = self::isPassive($class);
        [$fresh] = $this->fresh($class, $source);
        $positives = 0;
        foreach ($document[$passive ? 'questions' : 'widgets'] as $row) {
            [$question, $payload] = $this->widget($row, $passive);
            $snapshot = serialize([$question->getAttributes(), $question->getRawOriginal(), $question->getRelations()]);
            $expected = $this->expected($row, $payload, $revision, $passive);
            $positives += (int) ($expected !== $payload);
            self::assertSame($expected, $this->project($fresh, $passive, $question, $row['block_uuid'], $payload), (string) $row['id']);
            self::assertSame($snapshot, serialize([$question->getAttributes(), $question->getRawOriginal(), $question->getRelations()]));

            $invalid = $passive
                ? array_replace($payload, ['options' => []])
                : array_replace($payload, ['accepted_sentences' => ['existing alternate']]);
            self::assertSame($invalid, $this->project($fresh, $passive, $question, $row['block_uuid'], $invalid));
        }
        self::assertSame($positiveRows, $positives, 'Digest-only legacy rows must not pass with fabricated answer IDs.');
        self::assertTrue($this->loaded($fresh), 'Own questions retain the immutable full-loader path.');
    }

    #[DataProvider('families')]
    public function test_only_disjoint_exact_identity_skips_and_source_drift_fails_closed(
        string $class, string $source, string $sha, string $revision, int $rows, int $positiveRows,
    ): void {
        $document = $this->document($source, $sha);
        $passive = self::isPassive($class);
        $own = $document[$passive ? 'questions' : 'widgets'][0];
        $foreign = array_replace($own, [
            'id' => self::FOREIGN_ID, 'uuid' => self::FOREIGN_UUID, 'seeder' => self::FOREIGN_SEEDER,
        ]);
        [$foreignQuestion, $foreignPayload] = $this->widget($foreign, $passive);
        [$ownQuestion, $ownPayload] = $this->widget($own, $passive);
        [$fresh, $fixture] = $this->fresh($class, $source);
        self::assertSame($foreignPayload, $this->project($fresh, $passive, $foreignQuestion, self::FOREIGN_BLOCK, $foreignPayload));
        self::assertFalse($this->loaded($fresh));

        $uncertain = [
            [$foreignQuestion, $own['block_uuid'], $foreignPayload],
            [$ownQuestion, self::FOREIGN_BLOCK, $ownPayload],
            [$foreignQuestion, null, $foreignPayload],
            [$foreignQuestion, strtoupper(self::FOREIGN_BLOCK), $foreignPayload],
            [$foreignQuestion, self::FOREIGN_BLOCK, array_replace($foreignPayload, ['id' => (string) self::FOREIGN_ID])],
        ];
        if (!$passive) {
            foreach (['uuid', 'seeder'] as $field) {
                [$question, $payload] = $this->widget(array_replace($foreign, [$field => $own[$field]]), false);
                $uncertain[] = [$question, self::FOREIGN_BLOCK, $payload];
            }
        }
        foreach ($uncertain as [$question, $block, $payload]) {
            [$probe] = $this->fresh($class, $source);
            self::assertSame($payload, $this->project($probe, $passive, $question, $block, $payload));
            self::assertTrue($this->loaded($probe), 'A partial or malformed identity stays on the full path.');
        }

        $expected = $this->expected($own, $ownPayload, $revision, $passive);
        self::assertSame($expected, $this->project($fresh, $passive, $ownQuestion, $own['block_uuid'], $ownPayload));
        self::assertTrue($this->loaded($fresh));
        foreach ($passive ? [$source] : [$source, $own['definition'], $own['audit']] as $drift) {
            $this->write($fixture, $drift, '{');
            try {
                $fresh::load();
                self::fail('Warm public loader accepted drift in '.$drift);
            } catch (RuntimeException $error) {
                self::assertMatchesRegularExpression('/\bdiffers?\b/', $error->getMessage());
            }
            self::assertSame($ownPayload, $this->project($fresh, $passive, $ownQuestion, $own['block_uuid'], $ownPayload));
            self::assertSame($foreignPayload, $this->project($fresh, $passive, $foreignQuestion, self::FOREIGN_BLOCK, $foreignPayload));
            $this->write($fixture, $drift, $this->bytes($drift));
        }
        self::assertSame($expected, $this->project($fresh, $passive, $ownQuestion, $own['block_uuid'], $ownPayload));
        if (!$passive) {
            $registry = $this->registry($class);
            $coldOnly = array_diff_key(array_column($registry['cold'], 1, 0), array_column($registry['mandatory'], 1, 0));
            [$cold, $coldFixture] = $this->fresh($class, $source);
            $this->write($coldFixture, array_key_first($coldOnly), '{');
            self::assertSame($foreignPayload, $this->project($cold, false, $foreignQuestion, self::FOREIGN_BLOCK, $foreignPayload));
            self::assertTrue($this->loaded($cold), 'A changed compile dependency prevents the early skip.');
        }
    }

    private static function isPassive(string $class): bool
    {
        return $class === PassiveVoiceWidgetHints::class;
    }

    private function bytes(string $relative): string
    {
        self::assertMatchesRegularExpression('~^(?:(?!\.\.?(?:/|$))[A-Za-z0-9_.-]+/)+[A-Za-z0-9_.-]+\z~', $relative);
        $file = $this->root.'/'.$relative;
        self::assertFileExists($file);
        self::assertFalse(is_link($file));
        $value = file_get_contents($file);
        self::assertIsString($value);
        return $value;
    }

    private function document(string $source, string $sha): array
    {
        $bytes = $this->bytes($source);
        self::assertSame($sha, hash('sha256', $bytes), 'Exact frozen artifact bytes: '.$source);
        return json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    }

    private function registry(string $class): array
    {
        $index = (new ReflectionClass($class))->getConstant(self::isPassive($class) ? 'MEMBERSHIP' : 'WIDGET_MEMBERSHIP');
        self::assertIsArray($index);
        return $index;
    }

    private function orderedPairs(array $pairs): array
    {
        ksort($pairs, SORT_STRING);
        foreach ($pairs as &$ids) {
            sort($ids, SORT_NUMERIC);
        }
        return $pairs;
    }

    /** Rename only the class declaration; all other source bytes and dirname-based lookups are preserved. */
    private function fresh(string $class, string $source): array
    {
        // Leave room for the exact lexical dependency names on Windows.
        $nonce = bin2hex(random_bytes(4));
        $root = IsolatedTestEnvironment::prepare().'/w'.$nonce;
        self::assertTrue(mkdir($root, 0700));
        IsolatedTestEnvironment::assertOwnedPath($root);
        $paths = [$source];
        if (!self::isPassive($class)) {
            $registry = $this->registry($class);
            $paths = [...$paths, ...array_column($registry['mandatory'], 0), ...array_column($registry['cold'], 0)];
        }
        foreach (array_unique($paths) as $path) {
            $this->write($root, $path, $this->bytes($path));
        }
        $name = substr($class, strrpos($class, '\\') + 1);
        $clone = 'MembershipFixture'.$nonce;
        $php = str_replace('final class '.$name, 'final class '.$clone, $this->bytes('app/Support/'.$name.'.php'), $count);
        self::assertSame(1, $count);
        $file = 'app/Support/'.$clone.'.php';
        $this->write($root, $file, $php);
        require $root.'/'.$file;
        return ['App\\Support\\'.$clone, $root];
    }

    private function write(string $root, string $relative, string $bytes): void
    {
        self::assertMatchesRegularExpression('~^(?:(?!\.\.?(?:/|$))[A-Za-z0-9_.-]+/)+[A-Za-z0-9_.-]+\z~', $relative);
        $file = $root.'/'.$relative;
        $parent = dirname($file);
        if (!is_dir($parent)) {
            self::assertTrue(mkdir($parent, 0700, true));
        }
        IsolatedTestEnvironment::assertOwnedPath($parent);
        self::assertFalse(is_link($file));
        self::assertSame(strlen($bytes), file_put_contents($file, $bytes));
        clearstatcache(true, $file);
    }

    private function loaded(string $class): bool
    {
        foreach ((new ReflectionMethod($class, 'loadChecked'))->getStaticVariables() as $state) {
            if (is_array($state) && isset($state['targets'])) {
                return true;
            }
        }
        return false;
    }

    private function project(string $class, bool $passive, Question $question, ?string $block, array $payload): array
    {
        return $passive
            ? $class::project($question, $block, $payload, true)
            : $class::projectWidget($question, $block, $payload, true);
    }

    private function widget(array $row, bool $passive): array
    {
        $question = new Question;
        $question->incrementing = false;
        $question->setRawAttributes(array_intersect_key($row, array_flip(
            ['id', 'uuid', 'seeder', 'type', 'level', 'category_id', 'source_id', 'question'],
        )), true);
        $instances = $passive ? $row['answers'] : ($row['original_ordered_instances'] ?? null);
        if ($instances === null) {
            $instances = [];
            foreach ($row['canonical_answers'] as $marker => $answer) {
                $id = count($instances) + 1;
                $instances[] = ['id' => $id, 'marker' => $marker, 'position' => null, 'option_id' => $id, 'option' => $answer];
            }
            self::assertNotSame($row['ordered_answer_snapshot_sha256'], $this->digest($instances));
        }
        $options = [];
        $question->setRelation('answers', collect(array_map(static function (array $item) use ($row, &$options): QuestionAnswer {
            $answer = new QuestionAnswer;
            $answer->setRawAttributes(array_diff_key($item, ['option' => true]) + ['question_id' => $row['id']], true);
            $option = new QuestionOption;
            $option->setRawAttributes(['id' => $item['option_id'], 'option' => $item['option']], true);
            $options[$item['option_id']] = $option;
            $answer->setRelation('option', $option);
            return $answer;
        }, $instances)));
        if ($passive) {
            $options = array_map(static function (array $item): QuestionOption {
                $option = new QuestionOption;
                $option->setRawAttributes($item, true);
                return $option;
            }, $row['option_snapshot']);
        }
        $question->setRelation('options', collect(array_values($options)));
        $hints = [];
        $prompt = $passive ? $row['payload_question'] : $row['question'];
        if ($passive && $prompt !== $row['question']) {
            $hint = new QuestionHint;
            $hint->setRawAttributes(['provider' => 'compose_prompt', 'locale' => 'uk', 'hint' => $prompt], true);
            $hints[] = $hint;
        }
        $question->setRelation('hints', collect($hints));
        $question->setRelation('verbHints', collect());
        $question->setRelation('tags', collect());
        return [$question, [
            'id' => $row['id'], 'type' => '4', 'level' => $row['level'], 'question' => $prompt,
            'correct_tokens' => $row['correct_tokens'], 'answers' => array_map(
                static fn (array $answer): array => ['marker' => $answer['marker'], 'correct' => $answer['option']], $instances,
            ),
            'options' => [...$row['correct_tokens'], 'unrelated option'], 'accepted_sentences' => [],
            'token_labels' => ['unrelated option' => 'retain'], 'source_bound_compose_hint' => false,
            'derived_compose_options' => ['existing token'], 'verb_hints' => [], 'hints' => [],
            'unrelated' => ['retain' => true],
        ]];
    }

    private function expected(array $row, array $payload, string $revision, bool $passive): array
    {
        if (!$passive && !is_array($row['original_ordered_instances'] ?? null)) {
            return $payload;
        }
        if ($passive) {
            $payload['question'] = $row['display_question'];
            $payload['source_bound_compose_hint'] = true;
            $payload['compose_preanswer_hint'] = $row['hint_uk'];
        } else {
            $payload['accepted_sentences'] = $row['accepted_sentences'];
            foreach (['options', 'derived_compose_options'] as $key) {
                $payload[$key] = array_values(array_unique(array_merge($payload[$key], $row['added_options'])));
            }
        }
        $payload['token_labels'] = array_replace($payload['token_labels'], $row['token_labels']);
        $payload[$revision] = $this->digest($row);
        return $payload;
    }

    private function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
