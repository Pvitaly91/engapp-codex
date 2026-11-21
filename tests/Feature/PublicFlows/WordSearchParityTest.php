<?php

namespace Tests\Feature\PublicFlows;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

final class WordSearchParityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        Schema::create('words', function (Blueprint $table): void {
            $table->id();
            $table->string('word')->unique();
            $table->string('type')->nullable();
            $table->timestamps();
        });
        Schema::create('translates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('word_id')->constrained();
            $table->string('lang', 8);
            $table->string('translation');
            $table->timestamps();
            $table->unique(['word_id', 'lang', 'translation']);
            $table->index(['lang', 'translation', 'word_id'], 'translates_locale_translation_word_search_index');
        });

        $entries = [
            ['go', 'base', [['pl', 'iść'], ['uk', 'йти'], ['uk', 'бігти'], ['en', 'move']]],
            ['went', 'past', [['uk', 'йти'], ['en', 'move'], ['pl', 'iść']]],
            ['gone', 'participle', [['uk', 'йти'], ['en', 'move'], ['pl', 'iść']]],
            ['departed', 'past', [['uk', 'йти'], ['en', 'leave']]],
            ['sprinted', 'past', [['uk', 'бігти']]],
            ['walk', 'base', [['uk', 'йти'], ['en', 'walk'], ['pl', 'iść']]],
            ['go near', null, [['uk', 'йти']]],
            ['gold', null, []],
            ['blank', 'base', [['uk', ''], ['uk', 'йти']]],
            ['odd', 'unknown', [['uk', 'йти']]],
            ['zero type', '0', [['uk', 'йти']]],
            ['number', 'base', [['uk', '0']]],
            ['1', 'past', [['uk', '0']]],
            ['01', 'past', [['uk', '0']]],
            ['numeric exact', 'base', [['uk', '01']]],
            ['numeric key', 'base', [['uk', '1']]],
            ['said exact', 'past', [['uk', '01']]],
            ['said numeric', 'past', [['uk', '1']]],
            ['pair first', null, [['uk', '02']]],
            ['pair later', 'base', [['uk', '2']]],
            ['paired past', 'past', [['uk', '2']]],
            ['only', 'base', [['uk', 'єдиний']]],
            ['sy%mbol', null, []],
            ['sy_mbol', null, []],
            ['sy\\mbol', null, []],
            ['sy\\\\double', null, []],
            ['syXmbol', null, []],
            [str_repeat('é', 64).'-entry', null, []],
            [str_repeat('é', 63).'x', null, []],
        ];
        // Deliberately seed cap entries in reverse order so the ten-result
        // test cannot pass by returning insertion order.
        foreach (array_reverse(range(1, 13)) as $index) {
            $entries[] = [sprintf('cap-%02d', $index), null, []];
        }

        foreach ($entries as [$word, $type, $translations]) {
            $wordId = DB::table('words')->insertGetId(['word' => $word, 'type' => $type]);
            foreach ($translations as [$lang, $translation]) {
                DB::table('translates')->insert(['word_id' => $wordId, 'lang' => $lang, 'translation' => $translation]);
            }
        }
    }

    public function test_word_search_matches_the_ordered_perf_21_http_contract(): void
    {
        // Captured through the HTTP kernel of exact PERF-21 A
        // (aff0324efd3878e18e236d9cea1e8bf725b887d8) on the fixture above.
        // These are response snapshots, not a second search implementation.
        $goneUk = self::entry('gone', 'йти', 'uk', ['go', 'walk', 'blank'], ['went', 'departed']);
        $uk = [
            self::entry('go', 'йти', 'uk', ['walk', 'blank'], ['went', 'departed'], ['gone']),
            self::entry('go near', 'йти'),
            self::entry('gold'),
            $goneUk,
        ];
        $en = [
            self::entry('go', 'move', 'en', [], ['went'], ['gone']),
            self::entry('go near', null, 'en'),
            self::entry('gold', null, 'en'),
            self::entry('gone', 'move', 'en', ['go'], ['went']),
        ];
        $pl = [
            self::entry('go', 'iść', 'pl', ['walk'], ['went'], ['gone']),
            self::entry('go near', null, 'pl'),
            self::entry('gold', null, 'pl'),
            self::entry('gone', 'iść', 'pl', ['go', 'walk'], ['went']),
        ];
        $cap = array_map(fn (int $index) => self::entry(sprintf('cap-%02d', $index)), range(1, 10));
        // SQLite does not implicitly treat backslash as LIKE's escape
        // character, unlike the live MariaDB. Preserve A's actual SQLite
        // results here and also check the literal bound pattern below.
        $oneBackslash = self::entry('sy\\mbol');
        $twoBackslashes = self::entry('sy\\\\double');
        $cases = [
            'mixed forms' => ['/api/word-search/uk', 'go', $uk],
            'case and input trimming' => ['/api/word-search/uk', '  GO  ', $uk],
            'default API locale' => ['/api/word-search', 'go', $uk],
            'web UK locale' => ['/words', 'go', $uk],
            'API EN locale' => ['/api/word-search/en', 'go', $en],
            'web EN locale' => ['/en/words', 'go', $en],
            'API PL locale' => ['/api/word-search/pl', 'go', $pl],
            'web PL locale' => ['/pl/words', 'go', $pl],
            'exclude self in past' => ['/api/word-search/uk', 'went', [self::entry('went', 'йти', 'uk', ['go', 'walk', 'blank'], ['departed'], ['gone'])]],
            'exclude self in participle' => ['/api/word-search/uk', 'gone', [$goneUk]],
            'ignore alternate translation after empty first' => ['/api/word-search/uk', 'blank', [self::entry('blank', '')]],
            'unknown nonempty type' => ['/api/word-search/uk', 'odd', [self::entry('odd', 'йти', 'uk', ['go', 'walk', 'blank'], ['went', 'departed'], ['gone'])]],
            'typeless result with a translation that has typed forms' => ['/api/word-search/uk', 'go near', [self::entry('go near', 'йти')]],
            'false-like type' => ['/api/word-search/uk', 'zero', [self::entry('zero type', 'йти')]],
            'numeric-like forms are loosely deduplicated' => ['/api/word-search/uk', 'number', [self::entry('number', '0', 'uk', [], ['1'])]],
            'numeric-like first translations are loosely deduplicated' => ['/api/word-search/uk', 'numeric', [
                self::entry('numeric exact', '01', 'uk', [], ['said exact']),
                self::entry('numeric key', '1'),
            ]],
            'individual numeric-like translation' => ['/api/word-search/uk', 'numeric key', [self::entry('numeric key', '1', 'uk', [], ['said numeric'])]],
            'typeless first translation still participates in loose deduplication' => ['/api/word-search/uk', 'pair ', [
                self::entry('pair first', '02'),
                self::entry('pair later', '2'),
                self::entry('paired past', '2'),
            ]],
            'the visible typed result gets forms when queried alone' => ['/api/word-search/uk', 'pair later', [self::entry('pair later', '2', 'uk', [], ['paired past'])]],
            'empty self-only group' => ['/api/word-search/uk', 'only', [self::entry('only', 'єдиний')]],
            'sort and cap' => ['/api/word-search/uk', 'cap', $cap],
            'no matches' => ['/api/word-search/uk', 'never-matches', []],
            'no q parameter' => ['/api/word-search/uk', null, []],
            'one Unicode character' => ['/api/word-search/uk', 'é', []],
            'only spaces on web' => ['/words', '  ', []],
            'escaped percent' => ['/api/word-search/uk', 'sy%', [$twoBackslashes, $oneBackslash]],
            'escaped underscore' => ['/api/word-search/uk', 'sy_', [$twoBackslashes, $oneBackslash]],
            'escaped backslash' => ['/api/word-search/uk', 'sy\\', [$twoBackslashes]],
            '64-character Unicode truncation' => ['/api/word-search/uk', str_repeat('é', 64).'ignored', [self::entry(str_repeat('é', 64).'-entry')]],
        ];

        foreach ($cases as $label => [$path, $query, $expected]) {
            app()->setLocale('uk');
            $response = $this->getJson('http://gramlyze.loc'.$path.($query === null ? '' : '?q='.rawurlencode($query)));

            $response->assertOk()
                ->assertHeader('Content-Type', 'application/json')
                ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store, private');
            $this->assertSame($expected, $response->json(), $label);
            $this->assertSame(json_encode($expected, JSON_THROW_ON_ERROR), $response->getContent(), $label);
        }
    }

    public function test_like_metacharacters_and_length_keep_the_original_sql_binding(): void
    {
        DB::enableQueryLog();
        try {
            foreach ([
                ['sy%', 'sy\\%%'],
                ['sy_', 'sy\\_%'],
                ['sy\\', 'sy\\\\%'],
                [str_repeat('é', 64).'%ignore', str_repeat('é', 64).'%'],
            ] as [$term, $expected]) {
                DB::flushQueryLog();
                $this->getJson('http://gramlyze.loc/api/word-search/uk?q='.rawurlencode($term))->assertOk();
                $bindings = collect(DB::getQueryLog())
                    ->filter(fn (array $query) => str_contains(strtolower($query['query']), ' like '))
                    ->pluck('bindings')->collapse()->all();
                $this->assertContains($expected, $bindings);
            }

            DB::flushQueryLog();
            $this->getJson('http://gramlyze.loc/api/word-search/uk?q=%C3%A9')->assertOk()->assertExactJson([]);
            $this->assertSame([], DB::getQueryLog(), 'A one-character API query does not use SQL.');
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }

    public function test_forms_query_runs_only_when_a_visible_word_can_use_it(): void
    {
        DB::enableQueryLog();
        try {
            foreach (['go near' => 2, 'zero' => 2, 'blank' => 2, 'go' => 3, 'odd' => 3, 'cap' => 2] as $term => $expected) {
                DB::flushQueryLog();
                $response = $this->getJson('http://gramlyze.loc/api/word-search/uk?q='.rawurlencode($term));
                $response->assertOk();

                $reads = collect(DB::getQueryLog())
                    ->pluck('query')
                    ->filter(fn (string $sql) => preg_match('/^select\b.*\bfrom\s+"(?:words|translates)"/i', $sql))
                    ->values();
                $this->assertCount($expected, $reads, $term);
                $this->assertMatchesRegularExpression('/\blimit\s+10\b/i', $reads[0], $term);
                $this->assertCount($expected === 3 ? 1 : 0, $reads->filter(
                    fn (string $sql) => preg_match('/\bjoin\s+"translates"/i', $sql)
                ), $term);

                if ($term === 'cap') {
                    $response->assertJsonCount(10);
                }
            }
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }

    public function test_api_word_search_keeps_a_stateless_route(): void
    {
        $route = app('router')->getRoutes()->getByName('api.words.search');

        $this->assertNotNull($route);
        $this->assertNotContains(StartSession::class, app('router')->gatherRouteMiddleware($route));
        $this->assertNotContains(ThrottleRequests::class.':api', app('router')->gatherRouteMiddleware($route));
        $this->getJson('http://gramlyze.loc/api/word-search/de?q=go')->assertNotFound();
    }

    public function test_cache_control_in_the_isolated_production_profile(): void
    {
        // This Laravel test request never uses the network. Only this isolated
        // app recognizes gramlyze.loc as a production-profile host here.
        config(['site-mode.production_domains' => ['gramlyze.loc']]);

        foreach ([
            'go' => 'max-age=300, public, s-maxage=300',
            'never-matches' => 'max-age=300, public, s-maxage=300',
            'g' => 'no-cache, private',
        ] as $query => $cacheControl) {
            $this->getJson('http://gramlyze.loc/api/word-search/uk?q='.$query)
                ->assertOk()
                ->assertHeader('Cache-Control', $cacheControl)
                ->assertHeaderMissing('Set-Cookie');
        }
    }

    private static function entry(
        string $word,
        ?string $translation = null,
        string $lang = 'uk',
        array $base = [],
        array $past = [],
        array $participle = [],
    ): array {
        return [
            'word' => $word,
            'translation' => $translation,
            'translation_lang' => $lang,
            'forms' => ['base' => $base, 'past' => $past, 'participle' => $participle],
        ];
    }
}
