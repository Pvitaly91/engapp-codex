<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\TheoryPracticeCompactProjection;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class TheoryPracticeCompactOwnerGateTest extends TestCase
{
    private string $originalRoot;
    private string $originalLocale;
    private string $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalRoot = $this->app->basePath();
        $this->originalLocale = $this->app->getLocale();
        $this->fixture = storage_path('app/perf04-compact-'.bin2hex(random_bytes(6)));
        self::assertTrue(mkdir($this->fixture));
        IsolatedTestEnvironment::assertOwnedPath($this->fixture);
        self::assertTrue(mkdir($this->fixture.'/docs/content', 0777, true));
        IsolatedTestEnvironment::assertOwnedPath($this->fixture.'/docs/content');
        $this->writeFixture(Perf04CompactOwnedProvider::SOURCE, Perf04CompactOwnedProvider::SOURCE_SHA, false);
        $this->writeFixture(Perf04CompactMalformedProvider::SOURCE, Perf04CompactMalformedProvider::SOURCE_SHA, true);
        $this->app->setBasePath($this->fixture);
        $this->app->setLocale('uk');
        Perf04CompactSupplementFixture::$loads = 0;
        Perf04CompactSupplementFixture::$reject = false;
    }

    protected function tearDown(): void
    {
        if (isset($this->originalRoot)) {
            $this->app->setBasePath($this->originalRoot);
            $this->app->setLocale($this->originalLocale);
        }
        parent::tearDown();
    }

    public function test_exact_foreign_owner_skips_expensive_validation_without_caching_a_document(): void
    {
        $data = Perf04CompactSupplementFixture::DATA;
        $block = $this->block();
        $foreign = clone $block;
        $foreign->seeder = 'fixtureexactowner'; // Case-folded is not exact.
        self::assertNull(Perf04CompactOwnedProvider::presentation($foreign, $data, true));
        self::assertSame(0, Perf04CompactSupplementFixture::$loads);

        // If the foreign route had cached the unvalidated document, a later
        // owning route could incorrectly pass without the supplement.
        Perf04CompactSupplementFixture::$reject = true;
        self::assertNull(Perf04CompactOwnedProvider::presentation($block, $data, true));
        self::assertSame(1, Perf04CompactSupplementFixture::$loads);
        Perf04CompactSupplementFixture::$reject = false;

        $target = Perf04CompactSupplementFixture::document()['targets'][1];
        [$a, $b, $c] = $target['groups'];
        $expected = $target + ['groups_by_kind' => [
            'selects' => [$a + ['number' => 1], $c + ['number' => 3]],
            'choices' => [$b + ['number' => 2]],
        ]];
        // Two valid targets share the owner; UUID still selects the second.
        self::assertSame($expected, Perf04CompactOwnedProvider::presentation($block, $data, true));
        self::assertSame(2, Perf04CompactSupplementFixture::$loads);
        self::assertSame($expected, Perf04CompactOwnedProvider::presentation($block, $data, true));
        self::assertSame(Perf04CompactSupplementFixture::BODY, $block->body);
        self::assertSame(Perf04CompactSupplementFixture::DATA, $data);

        $changed = clone $block;
        $changed->body .= ' ';
        self::assertNull(Perf04CompactOwnedProvider::presentation($changed, $data, true));
        self::assertNull(Perf04CompactOwnedProvider::presentation($block, $data + ['unreviewed' => true], true));
        $changed = clone $block;
        $changed->uuid = 'unreviewed-block';
        self::assertNull(Perf04CompactOwnedProvider::presentation($changed, $data, true));

        // Guard even after a successful full load in this process.
        $path = $this->fixture.'/'.Perf04CompactOwnedProvider::SOURCE;
        IsolatedTestEnvironment::assertOwnedPath($path);
        self::assertSame(1, file_put_contents($path, "\n", FILE_APPEND));
        clearstatcache(true, $path);
        self::assertNull(Perf04CompactOwnedProvider::presentation($block, $data, true));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Compact practice source differs.');
        Perf04CompactOwnedProvider::load();
    }

    public function test_nonconcrete_owner_and_malformed_target_use_the_full_validator(): void
    {
        Perf04CompactSupplementFixture::$reject = true;
        $block = $this->block();
        $data = Perf04CompactSupplementFixture::DATA;
        self::assertNull(Perf04CompactOwnedProvider::presentation($block, $data, false));
        $this->app->setLocale('en');
        self::assertNull(Perf04CompactOwnedProvider::presentation($block, $data, true));
        $this->app->setLocale('uk');
        self::assertSame(0, Perf04CompactSupplementFixture::$loads);

        foreach ([null, false, 7, []] as $i => $seeder) {
            $block->seeder = $seeder;
            self::assertNull(Perf04CompactOwnedProvider::presentation($block, $data, true));
            self::assertSame($i + 1, Perf04CompactSupplementFixture::$loads);
        }
        $block->seeder = 'ClearlyForeign';
        Perf04CompactSupplementFixture::$reject = false;
        // The source hash is correct, but the first target has no concrete
        // owner. An incomplete owner scan must not short circuit validation.
        self::assertNull(Perf04CompactMalformedProvider::presentation($block, $data, true));
        self::assertSame(5, Perf04CompactSupplementFixture::$loads);
        self::assertSame(Perf04CompactSupplementFixture::document(), Perf04CompactOwnedProvider::load());
        self::assertSame(6, Perf04CompactSupplementFixture::$loads);
    }

    private function block(): object
    {
        return (object) [
            'seeder' => 'FixtureExactOwner',
            'uuid' => 'second-block',
            'locale' => 'uk',
            'body' => Perf04CompactSupplementFixture::BODY,
        ];
    }

    private function writeFixture(string $path, string $sha, bool $malformed): void
    {
        $bytes = json_encode(
            Perf04CompactSupplementFixture::document($malformed),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
        self::assertSame($sha, hash('sha256', $bytes), 'Fixture must retain its independent byte pin.');
        self::assertSame(strlen($bytes), file_put_contents($this->fixture.'/'.$path, $bytes));
    }
}

final class Perf04CompactOwnedProvider extends TheoryPracticeCompactProjection
{
    public const SOURCE = 'docs/content/compact-owner-fixture.json';
    public const SOURCE_SHA = '1e2c38496cd7e6e02d22fc15539b92342b98fe5d64ec42b6edbce3f5a4a40e31';
    public const SUPPLEMENT = Perf04CompactSupplementFixture::class;
}

final class Perf04CompactMalformedProvider extends TheoryPracticeCompactProjection
{
    public const SOURCE = 'docs/content/compact-malformed-fixture.json';
    public const SOURCE_SHA = '55d5b5e0bb64cebe8800f67fb36a77c8fe08de0f63ed1a08d41ba57738a5b565';
    public const SUPPLEMENT = Perf04CompactSupplementFixture::class;
}

final class Perf04CompactSupplementFixture
{
    public const SOURCE = 'docs/content/compact-supplement-fixture.json';
    public const SOURCE_SHA = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    public const BODY = '{"fixture":"original"}';
    public const DATA = [
        'selects' => [['answer' => 'a'], ['answer' => 'b'], ['answer' => 'c'], ['answer' => 'd']],
        'choices' => [['answer' => 'e'], ['answer' => 'f']],
    ];
    private const AFTER_SHA = 'f0e246a7da1ff6a08f693ee02e2619d8b42bafe75b80e71389a95c2bfb9fad99';
    private const EMPTY_SHA = '4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945';
    private const ITEM_SHA = [
        'f3ec8796d923f3d65f83c7216fe505dbab0aeb85f651145f0cae8514366f9cb0',
        '8a661d998f4ed467405c1448dcbbf376a458de4aab467555f59e817d819cd3cc',
        'e2e7dda0680bb0ebf06a5335c7fdea4de108c0dee91e197627c8d77b18d1cc2b',
        '928e10913943f04feb4c78a6bc937ed40da69895b6d1eed4dc3c3916d43cad8e',
        '3094d0a1e9c523d456b992d22ce9b133936d9c02121d374a9fdce6103538767a',
        '88641ce91fa19a430afeb32bd1c5fb75c17d43ec590f464894b0f254c9931a4c',
    ];
    public static int $loads = 0;
    public static bool $reject = false;

    public static function load(): array
    {
        ++self::$loads;
        if (self::$reject) {
            throw new RuntimeException('Fixture supplement failed closed.');
        }
        return ['targets' => [
            ['topic' => 'first', 'binding' => self::binding('first-block'), 'after_data_sha256' => self::EMPTY_SHA, 'after_data' => []],
            ['topic' => 'second', 'binding' => self::binding('second-block'), 'after_data_sha256' => self::AFTER_SHA, 'after_data' => self::DATA],
        ]];
    }

    public static function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public static function document(bool $malformed = false): array
    {
        $first = self::binding('first-block');
        if ($malformed) {
            $first['seeder'] = null;
        }
        return [
            'schema_version' => 1,
            'source' => ['path' => self::SOURCE, 'sha256' => self::SOURCE_SHA],
            'targets' => [
                ['topic' => 'first', 'binding' => $first, 'after_data_sha256' => self::EMPTY_SHA, 'items' => [], 'groups' => []],
                [
                    'topic' => 'second',
                    'binding' => self::binding('second-block'),
                    'after_data_sha256' => self::AFTER_SHA,
                    'items' => [
                        'selects' => [self::row(0, 0), self::row(1, 1), self::row(2, 2), self::row(3, 3)],
                        'choices' => [self::row(0, 4), self::row(1, 5)],
                    ],
                    'groups' => [
                        self::group('selects-a', 'selects', [0, 1]),
                        self::group('choices', 'choices', [0, 1]),
                        self::group('selects-b', 'selects', [2, 3]),
                    ],
                ],
            ],
        ];
    }

    private static function binding(string $uuid): array
    {
        return ['seeder' => 'FixtureExactOwner', 'uuid' => $uuid, 'locale' => 'uk',
            'body_sha256' => '8d3a1861d28bdd18ef91d1e9f68f282911c929807a41cc8b3160cc54f97d7160'];
    }

    private static function row(int $index, int $digestIndex): array
    {
        return ['index' => $index, 'source_item_sha256' => self::ITEM_SHA[$digestIndex],
            'instruction_uk' => 'Обери відповідь.', 'stimuli' => []];
    }

    private static function group(string $key, string $kind, array $indices): array
    {
        return ['key' => $key, 'kind' => $kind, 'indices' => $indices,
            'title' => 'Вправа', 'instruction' => 'Обери відповідь.'];
    }
}
