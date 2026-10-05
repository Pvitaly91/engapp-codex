<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Support\LocalizedComposeText;
use App\Support\PpcComposePresentation;
use App\Support\SiteMode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;

if (! defined('PPC_COMPOSE_PRESENTATION_LIBRARY_ONLY')) { define('PPC_COMPOSE_PRESENTATION_LIBRARY_ONLY', true); }
require_once dirname(__DIR__, 2).'/scripts/build_ppc_compose_presentation.php';

class PpcComposePresentationTest extends TestCase
{
    public function test_production_profile_keeps_the_same_finite_title_help_and_canonical_target(): void
    {
        $originalRequest = app('request');
        $originalLocale = app()->getLocale();
        $originalDomains = config('site-mode.production_domains');
        $entry = collect($this->package('builder')['questions'])->firstWhere('editorial_uuid', 'pastpc-forms-poly-a1-01');
        $path = base_path('database/seeders/V3/Polyglot/PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder/definition.json');
        $sourceHash = hash_file('sha256', $path);
        $definition = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $canonical = collect($definition['questions'])->firstWhere('uuid', $entry['editorial_uuid']);
        $question = $this->question($entry)->forceFill(['type' => 4, 'question' => $canonical['question']]);
        $answers = [];
        foreach ($canonical['answers'] as $marker => $token) {
            $answers[] = (new QuestionAnswer(['marker' => $marker]))->setRelation('option', new QuestionOption(['option' => $token]));
        }
        $question->setRelation('answers', new Collection($answers));
        foreach (['uk', 'en', 'pl'] as $locale) {
            $question->hints->push(new QuestionHint(['provider' => 'chatgpt', 'locale' => $locale,
                'hint' => $canonical['localizations'][$locale]['hints'][0]]));
        }
        $snapshot = $question->toArray();
        try {
            config(['site-mode.production_domains' => ['gramlyze.com']]);
            // Synthetic request only: this does not send HTTP or change the app environment.
            app()->instance('request', Request::create('https://gramlyze.com/test/past-perfect-continuous/forms', 'GET'));
            $this->assertSame(SiteMode::PRODUCTION, app(SiteMode::class)->current());
            $this->assertSame('gramlyze.com', request()->getHost());
            foreach (['uk', 'en', 'pl'] as $locale) {
                app()->setLocale($locale);
                $this->assertSame($entry['locales'][$locale]['display_source'], LocalizedComposeText::source($question));
                $this->assertSame($entry['locales'][$locale]['instructions']."\n\n".$canonical['localizations'][$locale]['hints'][0], LocalizedComposeText::hint($question));
                $this->assertSame($canonical['target_text'], $question->answers->map(fn ($answer) => $answer->option->option)->implode(' ').'.');
                $this->assertSame($snapshot, $question->toArray());
            }
            $this->assertSame($sourceHash, hash_file('sha256', $path));
        } finally {
            app()->instance('request', $originalRequest);
            app()->setLocale($originalLocale);
            config(['site-mode.production_domains' => $originalDomains]);
        }
    }

    public function test_both_packages_cover_exactly_624_canonical_tasks_without_assessment_data(): void
    {
        $inventory = $this->inventory();
        $total = 0;
        foreach (['builder' => 336, 'mixed' => 288] as $scope => $count) {
            $actual = $this->package($scope);
            $this->assertSame(ppcComposePresentationPackage(base_path(), $inventory, $scope), $actual);
            $this->assertCount($count, $actual['questions']);
            foreach ($actual['questions'] as $entry) {
                $this->assertSame(['seeder_class', 'editorial_uuid', 'persistent_uuid', 'locales'], array_keys($entry));
                foreach ($entry['locales'] as $locale => $localized) {
                    $this->assertSame(['expected_source', 'display_source', 'instructions'], array_keys($localized));
                    $this->assertSame(['display_source' => $localized['display_source'], 'instructions' => $localized['instructions']],
                        PpcComposePresentation::forQuestion($this->question($entry), $locale, $localized['expected_source']));
                }
                $total++;
            }
        }
        $this->assertSame(624, $total);
    }

    public function test_builder_projection_keeps_every_uuid_answer_option_target_and_role_unchanged(): void
    {
        $count = 0; $short = 0;
        foreach (ppcQualityBuilderBanks() as $bank => $levels) {
            $name = $bank === 'BasicsB2' ? 'PolyglotPastPerfectContinuousBasicsB2LessonSeeder'
                : 'PolyglotPastPerfectContinuous'.$bank.'AllLevelsLessonSeeder';
            $path = base_path('database/seeders/V3/Polyglot/'.$name.'/definition.json');
            $before = hash_file('sha256', $path);
            $definition = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            $positions = [];
            foreach ($definition['questions'] as $canonical) {
                $index = $positions[$canonical['level']] ?? 0;
                $author = $levels[$canonical['level']][$index];
                $this->assertSame($canonical, ppcQualityBuilderProjection($bank, $canonical, $author));
                $display = ppcQualityBuilderDisplayProjection($bank, $canonical, $author);
                foreach (['uk', 'en', 'pl'] as $locale) {
                    $this->assertSame($author[$locale], $display[$locale]['display_source']);
                    $this->assertSame($canonical['localizations'][$locale]['source_text'], $display[$locale]['expected_source']);
                    $this->assertSame(trim(ppcQualityBuilderTaskPrefixes($author)[$locale]), $display[$locale]['instructions']);
                }
                if (preg_match('/^short-(positive|negative)-/', $author['focus'])) {
                    $short++;
                    foreach ($display as $localized) {
                        $this->assertSame($localized['expected_source'], $localized['display_source'], 'Short-answer evidence/role must stay intact.');
                        $this->assertSame('', $localized['instructions']);
                    }
                }
                $positions[$canonical['level']] = $index + 1;
                $count++;
            }
            $this->assertSame($before, hash_file('sha256', $path));
        }
        $this->assertSame(336, $count);
        $this->assertSame(10, $short);
    }

    public function test_first_forms_prompt_matches_the_requested_compact_sentence(): void
    {
        $entry = collect($this->package('builder')['questions'])->firstWhere('editorial_uuid', 'pastpc-forms-poly-a1-01');
        $display = PpcComposePresentation::forQuestion($this->question($entry), 'uk', $entry['locales']['uk']['expected_source']);
        $this->assertSame('Я перед цим плавав, тому моє волосся було мокрим.', $display['display_source']);
        $this->assertStringNotContainsString('Лексична основа', $display['display_source']);
        $this->assertStringContainsString('Past Perfect Continuous', $display['instructions']);
        $this->assertStringContainsString('Почни з підмета', $display['instructions']);
    }

    public static function rejectedLookups(): array
    {
        return ['other-theme' => ['other'], 'unknown-uuid' => ['uuid'], 'wrong-source' => ['source'],
            'unknown-locale' => ['locale'], 'nearly-matching-source' => ['near'], 'editorial-basics-uuid-is-not-persistent' => ['editorial']];
    }

    #[DataProvider('rejectedLookups')]
    public function test_unknown_or_nonexact_inputs_keep_the_canonical_fallback(string $case): void
    {
        $entry = $case === 'editorial'
            ? collect($this->package('builder')['questions'])->first(fn ($row) => str_contains($row['seeder_class'], 'BasicsB2'))
            : $this->package('builder')['questions'][0];
        $question = $this->question($entry);
        $source = $entry['locales']['uk']['expected_source']; $locale = 'uk';
        match ($case) {
            'other' => $question->seeder = 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectFormsAllLevelsLessonSeeder',
            'uuid' => $question->uuid = 'unknown-ppc-uuid',
            'source' => $source = 'Unrelated task source',
            'near' => $source .= ' ',
            'locale' => $locale = 'de',
            'editorial' => $question->uuid = $entry['editorial_uuid'],
        };
        $this->assertNull(PpcComposePresentation::forQuestion($question, $locale, $source));
    }

    public function test_ua_alias_and_revision_are_locale_independent_but_source_guarded(): void
    {
        $entry = $this->package('builder')['questions'][0]; $question = $this->question($entry);
        $this->assertSame(PpcComposePresentation::forQuestion($question, 'uk', $entry['locales']['uk']['expected_source']),
            PpcComposePresentation::forQuestion($question, ' UA ', $entry['locales']['uk']['expected_source']));
        $revision = PpcComposePresentation::revision($question);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $revision);
        foreach (['uk', 'en', 'pl'] as $locale) {
            app()->setLocale($locale);
            $this->assertSame($revision, PpcComposePresentation::revision($question));
        }
        $question->hints->first()->hint .= ' stale';
        $this->assertNull(PpcComposePresentation::revision($question));
        $this->assertNull(PpcComposePresentation::revision((new Question)->forceFill(['seeder' => $entry['seeder_class'], 'uuid' => $entry['persistent_uuid']])));
    }

    public function test_revision_tracks_display_and_instruction_changes_without_canonical_mutation(): void
    {
        $entry = $this->package('builder')['questions'][0]; $question = $this->question($entry);
        $revision = PpcComposePresentation::revision($question);
        $property = new ReflectionProperty(PpcComposePresentation::class, 'packages');
        $original = $property->getValue(); $changed = $original;
        $path = base_path('database/content-patches/ppc-compose-presentation/builder.json');
        $key = $entry['seeder_class']."\0".$entry['persistent_uuid'];
        try {
            $changed[$path][$key]['locales']['pl']['instructions'] .= ' Additional finite guidance.';
            $property->setValue(null, $changed);
            $this->assertNotSame($revision, PpcComposePresentation::revision($question));
            $this->assertSame($entry['locales']['uk']['expected_source'], $question->hints->first()->hint);
        } finally {
            $property->setValue(null, $original);
        }
    }

    public static function invalidPackages(): array
    {
        return ['duplicate' => ['duplicate'], 'answer-field' => ['answer'], 'wrong-count' => ['count'], 'missing-locale' => ['locale']];
    }

    #[DataProvider('invalidPackages')]
    public function test_malformed_finite_packages_are_ignored_without_partial_lookup(string $case): void
    {
        $package = $this->package('builder');
        match ($case) {
            'duplicate' => $package['questions'][1] = $package['questions'][0],
            'answer' => $package['questions'][0]['answers'] = ['Forbidden'],
            'count' => $package['counts']['questions'] = 335,
            'locale' => array_pop($package['questions'][0]['locales']),
        };
        $path = storage_path('app/presentation-fixture-'.bin2hex(random_bytes(4)).'.json');
        file_put_contents($path, json_encode($package, JSON_THROW_ON_ERROR));
        $method = new ReflectionMethod(PpcComposePresentation::class, 'readPackage');
        $this->assertSame([], $method->invoke(null, $path, 'builder'));
    }

    private function inventory(): array
    {
        return json_decode(file_get_contents(base_path('docs/reports/past-perfect-continuous-quality-inventory.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    private function package(string $scope): array
    {
        return json_decode(file_get_contents(base_path('database/content-patches/ppc-compose-presentation/'.$scope.'.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    private function question(array $entry): Question
    {
        $question = (new Question)->forceFill(['seeder' => $entry['seeder_class'], 'uuid' => $entry['persistent_uuid']]);
        $question->setRelation('hints', new Collection(array_map(static fn ($locale, $value) => new QuestionHint([
            'provider' => 'compose_prompt', 'locale' => $locale, 'hint' => $value['expected_source'],
        ]), array_keys($entry['locales']), array_values($entry['locales']))));
        return $question;
    }
}
