<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\TextBlock;
use App\Console\Commands\AuditTheorySentenceBuilders;
use App\Services\Theory\TextBlockToQuestionsMatcherService;
use App\Support\M26DetailPackage;
use App\Support\PracticeCompletionLinkedPractice;
use App\Support\PracticeCompletionPackage;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Uses the existing matcher and native component; synthetic DB fixtures stay in isolated SQLite. */
class TheorySentenceBuilderAcceptanceTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const FUTURE_OWNER = 'Database\\Seeders\\Page_V3\\FutureAccepted\\FutureAcceptedTheorySeeder';

    private const FUTURE_BANK = 'Database\\Seeders\\V3\\Polyglot\\FutureAcceptedLessonSeeder';

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('uk');
        $this->withoutVite();
    }

    private function futureContract(): array
    {
        return ['identity' => self::FUTURE_OWNER, 'definition' => Acceptance::definitionPath(self::FUTURE_OWNER),
            'local_url' => 'http://gramlyze.loc/theory/future-accepted/new-topic',
            'level' => 'B1', 'bank_seeders' => [self::FUTURE_BANK]];
    }

    private function fixtureRoot(): string
    {
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $root = storage_path('app/sentence-builder-acceptance-'.bin2hex(random_bytes(8)));
        File::makeDirectory($root, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($root);

        return $root;
    }

    private function fixtureJson(string $root, string $path, array $value): void
    {
        File::makeDirectory(dirname($root.'/'.$path), 0700, true, true);
        File::put($root.'/'.$path, M26DetailPackage::json($value));
    }

    private function config(): array
    {
        return ['source' => 'theory_links', 'question_types' => [Question::TYPE_COMPOSE_TOKENS],
            'seeder_classes' => [self::FUTURE_BANK], 'title' => 'Побудуй речення',
            'intro' => 'Склади англійське речення за українською умовою.'];
    }

    public function test_missing_or_foreign_command_target_is_rejected_before_database_or_http_activity(): void
    {
        $connection = DB::connection();
        $connection->enableQueryLog();
        $connection->flushQueryLog();
        // The entry guard returns before even requesting a connection. Native
        // cURL exists only in the later guarded target-audit branch, not a facade.
        DB::shouldReceive('connection')->never();
        $console = app(\Illuminate\Contracts\Console\Kernel::class);
        $console->registerCommand(app(AuditTheorySentenceBuilders::class));
        $command = $console->all()['theory:audit-sentence-builders'];
        foreach ([[], ['--target' => 'gramlyze.ub'], ['--target' => 'gramlyze.com']] as $arguments) {
            $tester = new CommandTester($command);
            self::assertSame(1, $tester->execute($arguments));
            self::assertStringContainsString('requires --target=gramlyze.loc', $tester->getDisplay());
        }
        self::assertSame([], $connection->getQueryLog());
    }

    public function test_dynamic_catalogue_requires_independent_contract_and_effective_connection_for_every_target(): void
    {
        $targets = Acceptance::discoverTargets(base_path());
        self::assertGreaterThanOrEqual(70, count($targets));
        self::assertCount(count($targets), array_unique(array_column($targets, 'identity')));
        [, $supplement] = PracticeCompletionPackage::load();
        $supplementByOwner = array_column($supplement['targets'], null, 'identity');
        foreach ($targets as $target) {
            self::assertSame([], $target['errors'], $target['identity']);
            $definition = json_decode(File::get(base_path($target['definition'])), true, flags: JSON_THROW_ON_ERROR);
            self::assertSame($target['identity'], $definition['seeder']['class']);
            $practices = collect($definition['page']['blocks'])->where('type', 'practice-set')->values();
            self::assertCount(1, $practices, $target['identity']);
            $practice = $practices->first();
            $block = new TextBlock;
            if (isset($supplementByOwner[$target['identity']])) {
                $source = $supplementByOwner[$target['identity']];
                $block->setRawAttributes($source['after']['practice'] + ['id' => 2000000 + $source['page']['id'],
                    'page_id' => $source['page']['id'], 'page_category_id' => $source['page']['page_category_id'],
                    'locale' => 'uk', 'seeder' => $target['identity']], true);
            } else {
                $block->setRawAttributes($practice + ['locale' => 'uk', 'seeder' => $target['identity']], true);
            }
            $copy = PracticeCompletionLinkedPractice::matchingBlock($block, true);
            $config = json_decode($copy->body, true, flags: JSON_THROW_ON_ERROR)['linked_practice'] ?? [];
            self::assertSame('theory_links', $config['source'] ?? null, $target['identity']);
            self::assertSame([Question::TYPE_COMPOSE_TOKENS], $config['question_types'] ?? null, $target['identity']);
            $actual = $config['seeder_classes'] ?? []; $expected = $target['contract']['bank_seeders'];
            sort($actual); sort($expected);
            self::assertSame($expected, $actual, $target['identity']);
            self::assertSame($target['contract']['level'], $block->level, $target['identity']);
        }
    }

    public function test_future_after_owner_outside_current_seventy_is_discovered_even_without_a_contract_or_config(): void
    {
        $root = $this->fixtureRoot(); $contract = $this->futureContract();
        $definition = ['seeder' => ['class' => self::FUTURE_OWNER], 'slug' => 'new-topic',
            'page' => ['locale' => 'uk', 'blocks' => [['type' => 'practice-set', 'level' => 'B1', 'body' => '{}']]]];
        $this->fixtureJson($root, $contract['definition'], $definition);
        $source = ['schema_version' => 1, 'targets' => [['identity' => self::FUTURE_OWNER,
            'path' => $contract['definition'], 'after' => $definition]]];
        $this->fixtureJson($root, 'database/content-patches/next-approved-lesson.v1.json', $source);
        $found = Acceptance::discoverTargets($root);
        self::assertCount(1, $found);
        self::assertSame(self::FUTURE_OWNER, $found[0]['identity']);
        self::assertSame(['missing_independent_bank_contract'], $found[0]['errors']);
        $this->fixtureJson($root, Acceptance::CONTRACT_DIRECTORY.'/next-accepted.v1.json',
            ['schema_version' => 1, 'targets' => [$contract]]);
        self::assertSame([], Acceptance::discoverTargets($root)[0]['errors']);
        // Registering a contract does not silently invent the missing runtime connection.
        [$page, $block, $questions, $html] = $this->nativeFixture();
        $result = Acceptance::check($contract, $page, $block, null, $questions, $questions, $html);
        self::assertContains('missing_linked_practice', $result['errors']);
        self::assertFalse($result['ok']);
    }

    public function test_before_drafts_and_design_only_maps_do_not_claim_new_completed_owners(): void
    {
        $root = $this->fixtureRoot(); $contract = $this->futureContract();
        $target = ['identity' => self::FUTURE_OWNER, 'path' => $contract['definition'], 'after' => ['page' => ['blocks' => []]]];
        foreach (['next-before.v1.json', 'next-draft.v1.json', 'next-preview.v1.json', 'next-planned.v1.json'] as $name) {
            $this->fixtureJson($root, 'database/content-patches/'.$name, ['targets' => [$target]]);
        }
        $this->fixtureJson($root, 'database/content-patches/design-map.v1.json', ['targets' => [array_diff_key($target, ['after' => true])]]);
        $this->fixtureJson($root, 'database/content-patches/not-approved.v1.json', ['status' => 'preview', 'targets' => [$target]]);
        self::assertSame([], Acceptance::discoverTargets($root));
    }

    public function test_uncontracted_future_payload_cannot_leak_credential_urls_or_nested_values_into_diagnostics(): void
    {
        $root = $this->fixtureRoot(); $contract = $this->futureContract();
        $definition = ['seeder' => ['class' => self::FUTURE_OWNER],
            'page' => ['locale' => 'uk', 'blocks' => []]];
        $this->fixtureJson($root, $contract['definition'], $definition);
        $sentinel = 'DO_NOT_LEAK_SYNTHETIC_URL_SECRET';
        $credentialUrl = 'http://audit-only-user:'.$sentinel.'@outside.invalid/theory/secret';
        foreach ([$credentialUrl, ['nested' => ['credentials' => $sentinel, 'url' => $credentialUrl]]] as $unsafeUrl) {
            $this->fixtureJson($root, 'database/content-patches/future-approved.v1.json',
                ['targets' => [['identity' => self::FUTURE_OWNER, 'path' => $contract['definition'],
                    'local_url' => $unsafeUrl, 'after' => $definition]]]);
            $found = Acceptance::discoverTargets($root);
            self::assertCount(1, $found);
            self::assertNull($found[0]['local_url']);
            self::assertContains('missing_independent_bank_contract', $found[0]['errors']);
            $diagnostic = json_encode($found, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            self::assertStringNotContainsString($sentinel, $diagnostic);
            self::assertStringNotContainsString('audit-only-user', $diagnostic);
            self::assertStringNotContainsString('outside.invalid', $diagnostic);
            self::assertStringNotContainsString('nested', $diagnostic);
        }
    }

    public function test_contracts_fail_closed_on_duplicate_owner_or_unsupported_bank_binding(): void
    {
        $root = $this->fixtureRoot(); $contract = $this->futureContract();
        $this->fixtureJson($root, Acceptance::CONTRACT_DIRECTORY.'/test.v1.json',
            ['schema_version' => 1, 'targets' => [$contract, $contract]]);
        $error = null;
        try { Acceptance::contracts($root); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error);
        $contract['bank_seeders'] = ['../../foreign.php'];
        $this->fixtureJson($root, Acceptance::CONTRACT_DIRECTORY.'/test.v1.json', ['schema_version' => 1, 'targets' => [$contract]]);
        $error = null;
        try { Acceptance::contracts($root); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error);
    }

    public function test_malformed_after_targets_and_undeclared_or_foreign_locales_fail_closed(): void
    {
        $root = $this->fixtureRoot();
        $valid = ['identity' => self::FUTURE_OWNER, 'path' => $this->futureContract()['definition'],
            'after' => ['page' => ['locale' => 'uk', 'blocks' => []]]];
        $changes = [
            array_replace($valid, ['after' => 'malformed']),
            array_replace($valid, ['identity' => '../../foreign']),
            array_replace($valid, ['after' => ['page' => ['blocks' => []]]]),
            array_replace($valid, ['after' => ['page' => ['locale' => 'en', 'blocks' => []]]]),
        ];
        foreach ($changes as $target) {
            $this->fixtureJson($root, 'database/content-patches/future-approved.v1.json', ['targets' => [$target]]);
            $error = null;
            try { Acceptance::discoverTargets($root); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error);
        }
        $this->fixtureJson($root, 'database/content-patches/future-approved.v1.json',
            ['targets' => [array_replace($valid, ['path' => '../../foreign/definition.json'])]]);
        self::assertContains('source_definition_mismatch:future-approved.v1.json', Acceptance::discoverTargets($root)[0]['errors']);
    }

    private function linkedQuestion(string $bank, string $level = 'B1', string $type = '4', ?string $uuid = null): Question
    {
        $question = Question::create(['uuid' => $uuid ?? (string) \Illuminate\Support\Str::uuid(),
            'question' => 'Ми працюємо разом.', 'seeder' => $bank, 'level' => $level, 'type' => $type]);
        foreach (['We', 'work', 'together', '.'] as $i => $token) {
            $option = DB::table('question_options')->where('option', $token)->value('id')
                ?? DB::table('question_options')->insertGetId(['option' => $token]);
            DB::table('question_answers')->insert(['question_id' => $question->id, 'option_id' => $option, 'marker' => 'a'.($i + 1)]);
            DB::table('question_option_question')->insert(['question_id' => $question->id, 'option_id' => $option]);
        }
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => $question->uuid,
            'text_block_uuid' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'position' => 0]);

        return $question;
    }

    private function nativeFixture(): array
    {
        $this->rebuildComposeTestSchema();
        $page = Page::create(['slug' => 'new-topic', 'title' => 'Future accepted topic', 'type' => 'theory',
            'seeder' => self::FUTURE_OWNER, 'page_category_id' => 100]);
        $data = ['title' => 'Практика', 'linked_practice' => $this->config(),
            'inputs' => [['before' => 'together / We / work', 'after' => 'Ми працюємо разом.',
                'answer' => 'We work together.', 'accepted' => ['We work together.']]]];
        $block = TextBlock::create(['uuid' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            'page_id' => $page->id, 'page_category_id' => 100, 'seeder' => self::FUTURE_OWNER,
            'locale' => 'uk', 'type' => 'practice-set', 'level' => 'B1', 'body' => M26DetailPackage::json($data)]);
        TextBlock::create(['uuid' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'page_id' => $page->id,
            'page_category_id' => 100, 'locale' => 'uk', 'type' => 'usage-panels', 'body' => '{}']);
        for ($i = 1; $i <= 6; $i++) {
            $this->linkedQuestion(self::FUTURE_BANK, uuid: sprintf('cccccccc-cccc-4ccc-8ccc-%012d', $i));
        }
        $this->linkedQuestion('Database\\Seeders\\V3\\Polyglot\\UnrelatedLessonSeeder');
        $this->linkedQuestion(self::FUTURE_BANK, 'B1', '0');
        $this->linkedQuestion(self::FUTURE_BANK, 'C2');
        $questions = app(TextBlockToQuestionsMatcherService::class)->findQuestionsForTextBlocks(new EloquentCollection([$block]), 5)[$block->uuid];
        $html = $this->render($block, $questions);

        return [$page, $block, $questions, $html];
    }

    private function render(TextBlock $block, Collection $questions): string
    {
        app('view')->flushState();

        return view('engram.theory.blocks-v3.practice-set', ['block' => $block,
            'data' => json_decode($block->body, true, flags: JSON_THROW_ON_ERROR),
            'theoryCanonical' => true, 'practiceQuestions' => $questions, 'lessonLinks' => []])->render();
    }

    public function test_future_topic_uses_real_matcher_and_exactly_one_unchanged_native_widget_without_replacing_inputs(): void
    {
        [$page, $block, $questions, $html] = $this->nativeFixture();
        $snapshot = $block->getAttributes();
        $result = Acceptance::check($this->futureContract(), $page, $block, $this->config(), $questions, $questions, $html);
        self::assertSame([], $result['errors']);
        self::assertTrue($result['ok']);
        self::assertSame(1, $result['widget_count']);
        self::assertCount(5, $result['payload']);
        self::assertSame($questions->pluck('id')->all(), array_column($result['payload'], 'id'));
        self::assertSame(['4'], $questions->pluck('type')->unique()->values()->all());
        self::assertSame([self::FUTURE_BANK], $questions->pluck('seeder')->unique()->values()->all());
        self::assertSame(1, substr_count($html, 'data-word-suggestion-input="inputs-'));
        self::assertSame($snapshot, $block->getAttributes());
        self::assertSame('not_checked', $result['browser_visibility']);
    }

    public function test_gate_rejects_missing_connection_empty_short_wrong_type_foreign_bank_and_wrong_level(): void
    {
        [$page, $block, $questions, $html] = $this->nativeFixture();
        $check = fn ($config, $eligible, $selected, $rendered = null) => Acceptance::check(
            $this->futureContract(), $page, $block, $config, $eligible, $selected, $rendered ?? $html);
        self::assertContains('missing_linked_practice', $check(null, $questions, $questions)['errors']);
        self::assertContains('fewer_than_five_eligible', $check($this->config(), collect(), collect(), $this->render($block, collect()))['errors']);
        self::assertContains('wrong_selected_count', $check($this->config(), $questions, $questions->take(4))['errors']);
        self::assertContains('fewer_than_five_eligible', $check($this->config(), $questions->take(4), $questions->take(4))['errors']);
        foreach (['type' => ['0', 'wrong_question_type'], 'seeder' => ['Database\\Seeders\\V3\\Polyglot\\OtherTopicSeeder', 'foreign_question_bank'],
            'level' => ['C2', 'wrong_question_level']] as $field => [$value, $error]) {
            $changed = $questions->map(static fn ($question) => clone $question);
            $changed->first()->$field = $value;
            self::assertContains($error, $check($this->config(), $questions, $changed, $this->render($block, $changed))['errors']);
        }
        $config = $this->config(); $config['seeder_classes'] = ['Database\\Seeders\\V3\\Polyglot\\OtherTopicSeeder'];
        self::assertContains('wrong_config_bank', $check($config, $questions, $questions)['errors']);
        $config = $this->config(); $config['question_types'] = ['0', '4'];
        self::assertContains('wrong_config_type', $check($config, $questions, $questions)['errors']);
    }

    public function test_duplicate_widgets_ids_foreign_payload_and_author_inputs_alone_cannot_pass(): void
    {
        [$page, $block, $questions, $html] = $this->nativeFixture();
        $check = fn ($selected, $rendered) => Acceptance::check($this->futureContract(), $page, $block,
            $this->config(), $questions, $selected, $rendered);
        self::assertContains('duplicate_widget', $check($questions, $html.$html)['errors']);
        $duplicates = collect([$questions[0], $questions[0], $questions[1], $questions[2], $questions[3]]);
        self::assertContains('duplicate_question_id', $check($duplicates, $this->render($block, $duplicates))['errors']);
        self::assertContains('duplicate_payload_id', $check($duplicates, $this->render($block, $duplicates))['errors']);
        $foreign = $questions->map(static fn ($question) => clone $question); $foreign->first()->id = 999999;
        self::assertContains('payload_ids_differ_from_matcher', $check($questions, $this->render($block, $foreign))['errors']);
        $withoutWidget = $this->render($block, collect());
        self::assertStringContainsString('data-word-suggestion-input="inputs-', $withoutWidget);
        self::assertContains('missing_widget', $check($questions, $withoutWidget)['errors']);
    }

    public function test_display_level_ranges_follow_existing_matcher_without_inventing_an_exact_cefr_filter(): void
    {
        [$page, $block, $questions, $html] = $this->nativeFixture();
        $block->level = 'A2–B1'; $contract = $this->futureContract(); $contract['level'] = 'A2–B1';
        self::assertSame([], Acceptance::check($contract, $page, $block, $this->config(), $questions, $questions, $html)['errors']);
        self::assertContains('noncanonical_caller', Acceptance::check($contract, $page, $block,
            $this->config(), $questions, $questions, $html, false)['errors']);
        app()->setLocale('en');
        self::assertContains('wrong_locale', Acceptance::check($contract, $page, $block,
            $this->config(), $questions, $questions, $html)['errors']);
    }
}
