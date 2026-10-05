<?php

namespace Tests\Feature;

use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Services\QuestionExportService;
use App\Services\QuestionImportService;
use App\Support\Database\JsonTestDefinitionIndex;
use App\Support\Database\JsonTestLocalizationManager;
use App\Support\Database\JsonTestSeeder;
use App\Support\LocalizedComposeText;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PastPerfectContinuousLocalizedComposeTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const UUID = '1a91a612-6d95-5e37-b2a0-c6245589b8d4';
    private const OWNER = 'Tests\\Fixtures\\PastPerfectContinuousComposeQualitySeeder';
    private const PROMPTS = [
        'uk' => 'Коли вчитель зайшов, вона навчалася вже двадцять хвилин.',
        'en' => 'She started studying twenty minutes before the teacher came in. Build the sentence in Past Perfect Continuous.',
        'pl' => 'Kiedy nauczyciel wszedł, uczyła się już od dwudziestu minut.',
    ];
    private const HINTS = [
        'uk' => 'Передай тривалість навчання до приходу вчителя.',
        'en' => 'Show how long the studying had lasted before the teacher arrived.',
        'pl' => 'Podaj czas trwania nauki do chwili wejścia nauczyciela.',
    ];

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); $this->withoutVite(); app()->setLocale('uk');
        Schema::table('verb_hints', fn (Blueprint $table) => $table->string('locale', 8)->nullable());
    }

    private function definition(): array
    {
        $tokens = ['She', 'had', 'been', 'studying', 'for', 'twenty', 'minutes', 'when', 'the', 'teacher', 'came', 'in'];
        $answers = []; foreach ($tokens as $i => $token) { $answers['a'.($i + 1)] = $token; }
        return ['schema_version' => 1, 'seeder' => ['class' => self::OWNER],
            'defaults' => ['default_locale' => 'uk', 'flag' => 2, 'type' => Question::TYPE_COMPOSE_TOKENS],
            'category' => ['name' => 'Past Perfect Continuous compose fixture'],
            'questions' => [['uuid' => self::UUID, 'question' => self::PROMPTS['uk'], 'source_text_uk' => self::PROMPTS['uk'],
                'target_text' => 'She had been studying for twenty minutes when the teacher came in.',
                'answers' => $answers, 'tokens_correct' => $tokens, 'options' => $tokens, 'level' => 'A2',
                'hints' => [self::HINTS['uk']], 'localizations' => [
                    'en' => ['source_text' => self::PROMPTS['en'], 'hints' => [self::HINTS['en']]],
                    'pl' => ['source_text' => self::PROMPTS['pl'], 'hints' => [self::HINTS['pl']]],
                ]]]];
    }

    private function seedDefinition(array $definition): void
    {
        $seeder = new class extends JsonTestSeeder {
            protected function definitionPath(): string { return 'unused-owned-array-fixture'; }
            public function seedArray(array $definition): void { $this->seedDefinition($definition, $this->resolveSeederClassName($definition)); }
        };
        $seeder->seedArray($definition);
    }

    private function question(): Question
    {
        return Question::query()->with(['hints', 'answers.option', 'options', 'verbHints.option', 'tags', 'chatgptExplanations'])
            ->where('uuid', self::UUID)->firstOrFail();
    }

    private function externalManager(array $base, string $locale, string $prompt): array
    {
        $root = storage_path('app/ppc-compose-localization-fixture-'.bin2hex(random_bytes(8)));
        IsolatedTestEnvironment::assertOwnedPath(dirname($root)); File::makeDirectory($root, 0700);
        $basePath = $root.'/definition.json'; $path = $root.'/'.$locale.'.json';
        File::put($basePath, json_encode($base, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $class = 'Tests\\Fixtures\\'.ucfirst($locale).'ComposeLocalizationSeeder';
        $localization = ['locale' => $locale, 'seeder' => ['class' => $class],
            'target' => ['seeder_class' => self::OWNER, 'definition_path' => $basePath],
            'questions' => [['uuid' => self::UUID, 'source_text' => $prompt]]];
        File::put($path, json_encode($localization, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $manager = new JsonTestLocalizationManager(new JsonTestDefinitionIndex);
        // Descriptor discovery is scoped to this private runtime; no repository fixture writes.
        (new \ReflectionProperty($manager, 'descriptorCache'))->setValue($manager, [$class => [
            'class_name' => $class, 'path' => $path, 'locale' => $locale,
            'target_seeder_class' => self::OWNER, 'target_definition' => '', 'target_definition_path' => $basePath,
        ]]);
        return [$manager, $class, $basePath];
    }

    public function test_inline_source_text_is_persisted_separately_from_localized_hints_and_is_idempotent(): void
    {
        $definition = $this->definition(); $this->seedDefinition($definition); $this->seedDefinition($definition);
        self::assertSame(1, Question::query()->count()); self::assertSame(6, QuestionHint::query()->count());
        $question = $this->question(); self::assertTrue(LocalizedComposeText::optedIn($question));
        foreach (self::PROMPTS as $locale => $prompt) {
            app()->setLocale($locale);
            self::assertSame($prompt, LocalizedComposeText::source($question));
            self::assertSame(self::HINTS[$locale], LocalizedComposeText::hint($question));
            self::assertDatabaseHas('question_hints', ['question_id' => $question->id, 'provider' => 'compose_prompt', 'locale' => $locale, 'hint' => $prompt]);
        }
        self::assertSame(self::PROMPTS['uk'], LocalizedComposeText::source($question, 'ua'));
        self::assertSame(self::PROMPTS['uk'], $question->question);
        self::assertSame($definition['questions'][0]['tokens_correct'], $question->answers
            ->sort(fn ($a, $b) => strnatcasecmp($a->marker, $b->marker))->values()->pluck('option.option')->all());
    }

    public function test_external_source_text_only_payload_merges_and_direct_apply_upserts_without_duplicate_rows(): void
    {
        $base = $this->definition(); unset($base['questions'][0]['localizations']['en']);
        $first = 'She was already studying when the teacher came in; the studying had begun twenty minutes earlier.';
        [$manager, $class, $basePath] = $this->externalManager($base, 'en', $first);
        $merged = $manager->mergeDefinitionLocalizations($base, $basePath, self::OWNER);
        self::assertSame($first, $merged['questions'][0]['localizations']['en']['source_text']);
        $this->seedDefinition($merged);
        self::assertSame($first, LocalizedComposeText::source($this->question(), 'en'));
        $descriptor = $manager->descriptorForClass($class); $loc = json_decode(File::get($descriptor['path']), true, flags: JSON_THROW_ON_ERROR);
        $loc['questions'][0]['source_text'] = self::PROMPTS['en'];
        File::put($descriptor['path'], json_encode($loc, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $before = QuestionHint::query()->count();
        foreach ([1, 2] as $_) {
            $result = $manager->applyVirtualSeeder($class);
            self::assertSame(1, $result['questions_updated']); self::assertSame(1, $result['hints_upserted']);
            self::assertSame(0, $result['missing_questions']);
        }
        self::assertSame($before, QuestionHint::query()->count());
        self::assertSame(self::PROMPTS['en'], LocalizedComposeText::source($this->question(), 'en'));
        $manager->removeVirtualSeederData($class);
        self::assertSame(0, QuestionHint::query()->where('provider', 'compose_prompt')->where('locale', 'en')->count());
        self::assertSame(self::PROMPTS['uk'], LocalizedComposeText::source($this->question(), 'uk'));
    }

    public function test_dynamic_widget_uses_current_locale_prompt_and_hint_but_leaves_english_tokens_unchanged(): void
    {
        $this->seedDefinition($this->definition()); $question = $this->question();
        $question->seeder = 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder';
        foreach (self::PROMPTS as $locale => $prompt) {
            app()->setLocale($locale);
            $html = view('components.text-block-practice-questions', ['questions' => collect([$question]), 'blockUuid' => 'ppc-localized-fixture'])->render();
            self::assertSame(1, preg_match('/questions:\s*(\[[^\r\n]+\]),/', $html, $matches));
            $data = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR)[0];
            self::assertSame($prompt, $data['question']);
            self::assertCount(1, $data['hints']); self::assertSame(self::HINTS[$locale], $data['hints'][0]['hint']);
            self::assertSame(self::HINTS[$locale], $data['compose_preanswer_hint']);
            self::assertSame('chatgpt', $data['hints'][0]['provider']);
            self::assertStringContainsString('data-authored-compose-hint', $html);
            self::assertStringContainsString('x-text="currentComposeHint"', $html);
            self::assertSame(['She', 'had', 'been', 'studying', 'for', 'twenty', 'minutes', 'when', 'the', 'teacher', 'came', 'in'], $data['correct_tokens']);
            self::assertContains('had', $data['options']); self::assertContains('been', $data['options']);
        }
    }

    public function test_unknown_owner_does_not_get_finite_linked_preanswer_hint_contract(): void
    {
        $this->seedDefinition($this->definition()); $question = $this->question();
        $question->seeder = 'Tests\\Fixtures\\UnrelatedAuthoredComposeSeeder';
        app()->setLocale('en');
        $html = view('components.text-block-practice-questions', ['questions' => collect([$question]), 'blockUuid' => 'unknown-owner-fixture'])->render();
        self::assertSame(1, preg_match('/questions:\s*(\[[^\r\n]+\]),/', $html, $matches));
        $data = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR)[0];
        self::assertArrayNotHasKey('compose_preanswer_hint', $data);
        self::assertStringNotContainsString('data-authored-compose-hint', $html);
        self::assertSame(self::PROMPTS['en'], $data['question']);
    }

    public function test_legacy_non_opted_in_source_and_unloaded_relation_fallback_stay_exact(): void
    {
        $question = new Question(['question' => 'I {a1} for two hours before dinner started.', 'type' => '0']);
        self::assertFalse(LocalizedComposeText::optedIn($question));
        self::assertSame($question->question, LocalizedComposeText::source($question, 'pl'));
        self::assertNull(LocalizedComposeText::hint($question));
        $question->setRelation('hints', collect([new QuestionHint(['provider' => 'chatgpt', 'locale' => 'uk', 'hint' => 'Звичайна граматична підказка.'])]));
        self::assertFalse(LocalizedComposeText::optedIn($question));
        self::assertSame($question->question, LocalizedComposeText::source($question, 'en'));
    }

    public function test_english_question_punctuation_is_independent_of_localized_imperative_prompt(): void
    {
        $definition = $this->definition();
        $tokens = ['Had', 'she', 'been', 'studying', 'for', 'twenty', 'minutes', 'when', 'the', 'teacher', 'came', 'in'];
        $row = &$definition['questions'][0];
        $row['question'] = $row['source_text_uk'] = 'Коли вчитель зайшов, вона навчалася вже двадцять хвилин?';
        $row['target_text'] = 'Had she been studying for twenty minutes when the teacher came in?';
        $row['tokens_correct'] = $row['options'] = $tokens; $row['answers'] = [];
        foreach ($tokens as $i => $token) { $row['answers']['a'.($i + 1)] = $token; }
        unset($row); $this->seedDefinition($definition); $question = $this->question();
        foreach (['uk', 'en', 'pl'] as $locale) {
            app()->setLocale($locale);
            $html = view('components.text-block-practice-questions', ['questions' => collect([$question]), 'blockUuid' => 'ppc-question-punctuation-fixture'])->render();
            self::assertSame(1, preg_match('/questions:\s*(\[[^\r\n]+\]),/', $html, $matches));
            $data = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR)[0];
            self::assertTrue($data['authored_compose']);
            self::assertSame('?', $data['compose_punctuation']);
            self::assertSame($tokens, $data['correct_tokens']);
            self::assertSame($locale === 'uk' ? $definition['questions'][0]['source_text_uk'] : self::PROMPTS[$locale], $data['question']);
        }
    }

    public function test_export_import_roundtrip_preserves_compose_metadata_locale_and_reserved_prompts(): void
    {
        $this->seedDefinition($this->definition()); $question = $this->question();
        $theoryUuid = '4b772b0d-9418-5a28-9106-b7ec65a9d8ec';
        $question->update(['theory_text_block_uuid' => $theoryUuid]);
        $hintOption = QuestionOption::firstOrCreate(['option' => 'Навчальна лема: study; покажи тривалість до минулої події.']);
        VerbHint::create(['question_id' => $question->id, 'marker' => 'a4', 'locale' => 'uk', 'option_id' => $hintOption->id]);
        $question = $this->question(); $optionMarkers = $question->options_by_marker; $originalId = $question->id;
        app(QuestionExportService::class)->export($question);
        $path = config('questions.export_path').'/'.self::UUID.'.json'; IsolatedTestEnvironment::assertOwnedPath($path);
        $payload = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('4', $payload['question']['type']); self::assertSame(self::OWNER, $payload['question']['seeder']);
        self::assertSame($theoryUuid, $payload['question']['theory_text_block_uuid']);
        self::assertSame($optionMarkers, $payload['question']['options_by_marker']);
        self::assertSame('uk', $payload['verb_hints'][0]['verb_hint']['locale']);
        self::assertCount(3, array_filter($payload['hints'], fn ($h) => $h['provider'] === 'compose_prompt'));
        // Restore into the same identity, as a scoped import must not erase progress.
        $question->update(['type' => '0', 'seeder' => 'temporary-fixture', 'options_by_marker' => null, 'theory_text_block_uuid' => null]);
        QuestionHint::query()->where('question_id', $question->id)->delete();
        VerbHint::query()->where('question_id', $question->id)->delete();
        $importer = new class extends QuestionImportService {
            public function importArray(array $payload): Question { return $this->importPayload($payload); }
        };
        $restored = $importer->importArray($payload);
        self::assertSame($originalId, $restored->id); self::assertSame(1, Question::query()->count());
        self::assertSame('4', $restored->type); self::assertSame(self::OWNER, $restored->seeder);
        self::assertSame($optionMarkers, $restored->options_by_marker); self::assertSame($theoryUuid, $restored->theory_text_block_uuid);
        $restored = $this->question(); self::assertTrue(LocalizedComposeText::optedIn($restored));
        self::assertSame('uk', $restored->verbHints->sole()->locale);
        foreach (self::PROMPTS as $locale => $prompt) { self::assertSame($prompt, LocalizedComposeText::source($restored, $locale)); }
        $legacyPayload = $payload;
        foreach (['type', 'seeder', 'options_by_marker', 'theory_text_block_uuid'] as $field) { unset($legacyPayload['question'][$field]); }
        $restored = $importer->importArray($legacyPayload);
        self::assertSame('4', $restored->type); self::assertSame(self::OWNER, $restored->seeder);
        self::assertSame($optionMarkers, $restored->options_by_marker); self::assertSame($theoryUuid, $restored->theory_text_block_uuid);
    }

    public function test_explanations_follow_interface_locale_including_ua_alias_without_translating_answers(): void
    {
        $question = new Question;
        $question->setRelation('chatgptExplanations', collect([
            new ChatGPTExplanation(['language' => 'uk', 'wrong_answer' => 'had studying', 'explanation' => 'Потрібне been.']),
            new ChatGPTExplanation(['language' => 'en', 'wrong_answer' => 'had studying', 'explanation' => 'The continuous form requires been.']),
            new ChatGPTExplanation(['language' => 'pl', 'wrong_answer' => 'had studying', 'explanation' => 'Forma ciągła wymaga been.']),
        ]));
        foreach (['uk' => 'Потрібне been.', 'en' => 'The continuous form requires been.', 'pl' => 'Forma ciągła wymaga been.', 'ua' => 'Потрібне been.'] as $locale => $text) {
            app()->setLocale($locale); self::assertSame(['had studying' => $text], LocalizedComposeText::explanations($question));
        }
    }
}
