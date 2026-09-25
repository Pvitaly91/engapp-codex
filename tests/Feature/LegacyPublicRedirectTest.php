<?php

namespace Tests\Feature;

use App\Modules\LanguageManager\Services\LocaleService;
use Database\Seeders\V2\Polyglot\PolyglotToBeLessonSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Real router/controllers/middleware with private fixtures, never production HTTP. */
class LegacyPublicRedirectTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const HOST = 'https://seo.production.test';
    private const LOCAL = 'http://gramlyze.loc';

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        config([
            'app.locale' => 'uk', 'app.debug' => false,
            'site-mode.production_domains' => ['seo.production.test'],
            'site-mode.production_locales' => ['uk'],
            'site-mode.production_origin' => 'https://gramlyze.com',
            'site-mode.response_cache.enabled' => false,
            'coming-soon.enabled' => true,
            'coming-soon.routes' => [],
            'coming-soon.prefixes' => ['/catalog/tests-cards', '/test/'],
            'coming-soon.development_bypass_prefixes' => ['/catalog/tests-cards'],
            'coming-soon.allowed_test_slug_prefixes' => ['polyglot-', 'course-td-'],
        ]);
        // The live multilingual site has a stable Ukrainian default in Language
        // Manager. A missing table instead tests its separate DB-outage fallback.
        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('native_name');
            $table->boolean('is_default');
            $table->boolean('is_active');
            $table->unsignedInteger('sort_order');
            $table->timestamps();
        });
        foreach (['uk' => 'Українська', 'en' => 'English', 'pl' => 'Polski'] as $code => $name) {
            DB::table('languages')->insert(['code' => $code, 'name' => $name, 'native_name' => $name,
                'is_default' => $code === 'uk', 'is_active' => true, 'sort_order' => $code === 'uk' ? 0 : 1]);
        }
        LocaleService::clearCache();
        app()->setLocale('uk');
        $this->seed(PolyglotToBeLessonSeeder::class);
    }

    protected function tearDown(): void
    {
        LocaleService::clearCache();
        parent::tearDown();
    }

    private function guest(string $url, string $method = 'GET'): TestResponse
    {
        $this->app['session']->flush();
        $this->defaultCookies = [];
        $this->unencryptedCookies = [];
        $this->defaultHeaders = [];
        app()->setLocale('uk');

        return $this->call($method, $url, [], [], [], ['HTTP_ACCEPT' => 'text/html']);
    }

    public function test_catalog_aliases_keep_temporary_status_but_use_the_requested_locale(): void
    {
        foreach (['', '/en', '/pl'] as $locale) {
            foreach (['/catalog-tests/cards', '/tests/cards'] as $alias) {
                foreach (['GET', 'HEAD'] as $method) {
                    $this->guest(self::LOCAL.$locale.$alias, $method)
                        ->assertStatus(302)->assertRedirect(self::LOCAL.$locale.'/catalog/tests-cards');
                }
            }
            $this->guest(self::LOCAL.$locale.'/catalog/tests-cards')->assertOk()
                ->assertHeaderMissing('Location')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }
    }

    public function test_catalog_aliases_cannot_open_the_production_gate_or_loop_through_pl(): void
    {
        foreach (['/catalog-tests/cards', '/tests/cards'] as $alias) {
            $this->guest(self::HOST.$alias)->assertStatus(302)
                ->assertRedirect(self::HOST.'/catalog/tests-cards');
        }
        $this->guest(self::HOST.'/catalog/tests-cards')->assertNotFound()
            ->assertHeaderMissing('Location')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_course_homes_stay_public_without_redirecting_to_a_course_with_a_closed_entry(): void
    {
        $old = '/courses/polyglot-english-a1';
        $current = '/courses/sentence-builder-english-a1';
        foreach ([self::LOCAL, self::HOST] as $host) {
            $target = $this->guest($host.$current)->assertOk()->assertHeaderMissing('Location');
            $target->assertSee('<link rel="canonical" href="https://gramlyze.com'.$current.'">', false);
            if ($host === self::HOST) {
                $target->assertHeaderMissing('X-Robots-Tag');
                $this->assertDoesNotMatchRegularExpression('/<meta[^>]+name=["\']robots["\'][^>]+noindex/i', $target->getContent());
            }
            $this->guest($host.$old)->assertOk()->assertHeaderMissing('Location')
                ->assertSee('English Sentence Builder A1');
            $this->guest($host.$current)->assertOk()->assertHeaderMissing('Location');
        }
    }

    public function test_course_mapping_preserves_available_locales_and_temporary_production_fallback(): void
    {
        foreach (['en', 'pl'] as $locale) {
            $old = '/'.$locale.'/courses/polyglot-english-a1';
            $current = '/'.$locale.'/courses/sentence-builder-english-a1';
            $this->guest(self::LOCAL.$old)->assertOk()->assertHeaderMissing('Location');
            $this->guest(self::LOCAL.$current)->assertOk()->assertHeaderMissing('Location');
            // Locale fallback is a distinct, intentionally temporary step.
            $this->guest(self::HOST.$old)->assertStatus(302)->assertRedirect('/courses/polyglot-english-a1');
            $this->guest(self::HOST.'/courses/polyglot-english-a1')->assertOk()->assertHeaderMissing('Location');
        }
    }

    public function test_alias_query_values_survive_once_without_controlling_the_destination(): void
    {
        $parameters = [
            'search' => 'тема з пробілом + 50% / déjà', 'tags' => ['тема з пробілом', '50% + /'], 'levels' => ['A1', 'B2'],
            'filters' => ['level' => ['A1', 'B2']], 'mode' => 'manual', 'launch' => 'lesson',
            'next' => '//attacker.invalid/x', 'redirect' => 'https://attacker.invalid',
            'url' => '//attacker.invalid', 'return' => '/unrelated',
        ];
        $query = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
        foreach (['/tests/cards', '/catalog-tests/cards'] as $old) {
            $new = '/catalog/tests-cards';
            $response = $this->guest(self::LOCAL.$old.'?'.$query);
            $parts = parse_url($response->headers->get('Location'));
            $this->assertSame('gramlyze.loc', $parts['host']);
            $this->assertSame($new, $parts['path']);
            parse_str($parts['query'] ?? '', $actual);
            $this->assertEquals($parameters, $actual);
            $this->assertStringNotContainsString('%25D1', $parts['query']);
            $target = $this->guest(self::LOCAL.$new.'?'.$query)->assertOk()->assertHeaderMissing('Location');
            $target->assertSee('<link rel="canonical" href="https://gramlyze.com'.$new.'">', false);
        }
    }

    public function test_external_referer_does_not_change_alias_destinations(): void
    {
        foreach (['/tests/cards', '/catalog-tests/cards'] as $old) {
            $new = '/catalog/tests-cards';
            $this->app['session']->flush();
            $this->get(self::LOCAL.$old, ['Referer' => 'https://attacker.invalid/'])
                ->assertRedirect(self::LOCAL.$new);
        }
    }

    public function test_closed_branded_lesson_is_not_a_new_redirect_target(): void
    {
        foreach ([self::LOCAL, self::HOST] as $host) {
            $this->guest($host.'/test/polyglot-to-be-a1/step/compose')->assertOk()->assertHeaderMissing('Location');
            $this->guest($host.'/test/sentence-builder-to-be-a1/step/compose')->assertNotFound()->assertHeaderMissing('Location');
        }
    }

    public function test_unknown_or_empty_courses_and_theory_paths_remain_real_404s(): void
    {
        foreach (['/courses/polyglot-english-a9', '/courses/polyglot-english-c2',
            '/theory/m6-unknown/material', '/m6-unknown'] as $path) {
            $this->guest(self::HOST.$path)->assertNotFound()->assertHeaderMissing('Location');
        }
        $this->guest(self::HOST.'/en/theory/m6-unknown/material')->assertStatus(302)
            ->assertRedirect('/theory/m6-unknown/material');
    }

    public function test_existing_non_get_routes_are_not_caught_by_the_home_alias(): void
    {
        foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->guest(self::LOCAL.'/courses/polyglot-english-a1', $method)
                ->assertStatus(405)->assertHeaderMissing('Location');
            $this->guest(self::LOCAL.'/tests/cards', $method)->assertStatus(405)->assertHeaderMissing('Location');
        }
        $this->guest(self::LOCAL.'/courses/polyglot-english-a1/progress')->assertOk()
            ->assertJson(['authenticated' => false])->assertHeaderMissing('Location');
        $this->postJson(self::LOCAL.'/courses/polyglot-english-a1/progress/attempt', [])
            ->assertStatus(422)->assertHeaderMissing('Location');
        $this->assertSame('courses.progress.attempt', $this->app['request']->route()->getName());
        $this->postJson(self::LOCAL.'/test/polyglot-to-be-a1/state', [])
            ->assertStatus(422)->assertHeaderMissing('Location');
        $this->assertSame('test.js.state', $this->app['request']->route()->getName());
    }

    public function test_production_locale_catalog_chain_is_bounded_and_keeps_the_gate_closed(): void
    {
        foreach (['en', 'pl'] as $locale) {
            $this->guest(self::HOST.'/'.$locale.'/tests/cards?levels%5B0%5D=A1')->assertStatus(302)
                ->assertRedirect('/tests/cards?levels%5B0%5D=A1');
            $this->get(self::HOST.'/tests/cards?levels%5B0%5D=A1')->assertStatus(302)
                ->assertRedirect(self::HOST.'/catalog/tests-cards?levels%5B0%5D=A1');
            $this->get(self::HOST.'/catalog/tests-cards?levels%5B0%5D=A1')->assertNotFound()->assertHeaderMissing('Location');
            $this->guest(self::HOST.'/catalog/tests-cards')->assertNotFound();
        }
    }

    public function test_public_navigation_already_uses_current_mappings_without_rewriting_course_or_mode_links(): void
    {
        foreach (['/courses', '/courses/sentence-builder-english-a1', '/catalog/tests-cards'] as $path) {
            $response = $this->guest(self::LOCAL.$path)->assertOk();
            $document = new \DOMDocument();
            @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
            foreach ($document->getElementsByTagName('a') as $link) {
                $url = $link->getAttribute('href');
                $this->assertDoesNotMatchRegularExpression('~/(catalog-tests/cards|tests/cards|courses/polyglot-english|test/polyglot-)~', $url);
            }
        }
        $this->guest(self::LOCAL.'/courses/sentence-builder-english-a1')->assertOk()
            ->assertSee('/test/sentence-builder-to-be-a1/step/compose', false);
    }
}
