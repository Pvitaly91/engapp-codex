<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\TextBlock;
use App\Services\Theory\TextBlockToQuestionsMatcherService;
use App\Support\M26DetailPackage;
use App\Support\PracticeCompletionLinkedPractice as Linked;
use App\Support\PracticeCompletionPackage as Package;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** The real unchanged matcher/widget, with small isolated SQLite fixtures and no bank seeders. */
class PracticeCompletionLinkedPracticeTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private int $fixtureQuestion = 0;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('uk');
        $this->withoutVite();
    }

    private function block(array $target, bool $after = true): TextBlock
    {
        $block = new TextBlock;
        $block->setRawAttributes($target[$after ? 'after' : 'before']['practice'] + [
            'id' => 1000000 + $target['page']['id'], 'page_id' => $target['page']['id'],
            'page_category_id' => $target['page']['page_category_id'],
            'seeder' => $target['identity'], 'locale' => 'uk',
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ], true);
        $block->setRelation('tags', new Collection);
        $block->setRelation('page', null);

        return $block;
    }

    private function xpath(string $html): DOMXPath
    {
        $html = preg_replace('/(?<=\s)@click(?=\s*=)/', 'data-test-click', $html);
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<!doctype html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    private function render(TextBlock $block, \Illuminate\Support\Collection $questions, bool $canonical = true): string
    {
        app('view')->flushState();
        $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);

        return view('engram.theory.blocks-v3.practice-set', [
            'block' => $block, 'data' => Linked::presentationData($block, $data, $canonical),
            'theoryCanonical' => $canonical, 'practiceQuestions' => $questions, 'lessonLinks' => [],
        ])->render();
    }

    public function test_pinned_manifest_has_exact_twelve_source_bound_native_configs(): void
    {
        [, $source] = Package::load();
        $manifest = Linked::load();
        self::assertSame(Linked::MANIFEST_SHA, hash_file('sha256', base_path(Linked::MANIFEST)));
        self::assertSame(Package::SOURCE, $manifest['source']);
        self::assertSame(Package::SOURCE_SHA, $manifest['source_sha256']);
        self::assertSame(Package::OWNERS, array_column($manifest['targets'], 'identity'));
        self::assertCount(12, array_unique(Linked::BANKS));
        $reference = json_decode(File::get(base_path(
            'database/seeders/Page_V3/Tenses/PastPerfectContinuous/PastPerfectContinuousFormsTheorySeeder/definition.json'
        )), true, flags: JSON_THROW_ON_ERROR);
        $practice = collect($reference['page']['blocks'])->firstWhere('type', 'practice-set');
        $referenceConfig = json_decode($practice['body'], true, flags: JSON_THROW_ON_ERROR)['linked_practice'];

        foreach ($manifest['targets'] as $i => $target) {
            self::assertSame($source['targets'][$i]['slug'], $target['slug']);
            self::assertSame($source['targets'][$i]['page']['id'], $target['page_id']);
            self::assertSame($source['targets'][$i]['page']['page_category_id'], $target['page_category_id']);
            self::assertSame(hash('sha256', $source['targets'][$i]['after']['practice']['body']), $target['body_sha256']);
            self::assertSame(['source', 'question_types', 'seeder_classes', 'title', 'intro', 'footer'],
                array_keys($target['linked_practice']));
            self::assertSame(['4'], $target['linked_practice']['question_types']);
            self::assertSame([Linked::BANKS[$i]], $target['linked_practice']['seeder_classes']);
            foreach (['source', 'title', 'footer'] as $field) {
                self::assertSame($referenceConfig[$field], $target['linked_practice'][$field]);
            }
            // The served PPC reference uses this approved wording; the preserved
            // historical WT definition still says "за українським".
            self::assertSame('Склади англійське речення за українською умовою, клікаючи на слова.',
                $target['linked_practice']['intro']);
        }
    }

    public function test_matching_clones_and_display_data_preserve_all_learning_and_original_models(): void
    {
        [, $source] = Package::load();
        $old = 0; $inputs = 0;
        foreach ($source['targets'] as $i => $target) {
            $block = $this->block($target);
            $attributes = $block->getAttributes(); $original = $block->getRawOriginal();
            $relations = $block->getRelations(); $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            $display = Linked::presentationData($block, $data);
            self::assertSame(Linked::load()['targets'][$i]['linked_practice'], $display['linked_practice']);
            $restored = $display; unset($restored['linked_practice']);
            self::assertSame($data, $restored);
            $copy = Linked::matchingBlock($block, true);
            self::assertNotSame($block, $copy);
            self::assertSame($display, json_decode($copy->body, true, flags: JSON_THROW_ON_ERROR));
            self::assertSame(array_diff_key($attributes, ['body' => true]), array_diff_key($copy->getAttributes(), ['body' => true]));
            self::assertSame($relations, $copy->getRelations());
            self::assertSame($attributes, $block->getAttributes());
            self::assertSame($original, $block->getRawOriginal());
            self::assertSame($relations, $block->getRelations());
            self::assertFalse($block->isDirty());
            self::assertSame($block, Linked::matchingBlock($block, false));
            self::assertSame($data, Linked::presentationData($block, $data, false));
            self::assertSame($copy, Linked::matchingBlock($copy, true), 'An already altered clone is not treated as stored AFTER.');
            $old += count($display['selects']) + count($display['choices']);
            $inputs += count($display['inputs']);
        }
        self::assertSame(72, $old);
        self::assertSame(24, $inputs);
    }

    public function test_foreign_callers_metadata_bodies_locales_and_untrusted_decoded_data_stay_untouched(): void
    {
        [, $source] = Package::load();
        foreach ($source['targets'] as $i => $target) {
            $block = $this->block($target);
            $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            $mutations = [
                'page_id' => $block->page_id + 10000, 'page_category_id' => $block->page_category_id + 10000,
                'seeder' => Package::OWNERS[($i + 1) % 12], 'locale' => 'en', 'uuid' => 'foreign-uuid',
                'type' => 'forms-grid', 'sort_order' => 999, 'heading' => 'Foreign heading',
                'column' => $block->column === 'right' ? 'left' : 'right',
                'level' => $block->level === 'C2' ? 'A1' : 'C2', 'css_class' => 'foreign-class',
            ];
            foreach ($mutations as $field => $value) {
                self::assertNotSame($block->$field, $value);
                $foreign = clone $block; $foreign->$field = $value;
                self::assertSame($foreign, Linked::matchingBlock($foreign, true), $field);
                self::assertSame($data, Linked::presentationData($foreign, $data), $field);
            }
            foreach (['pl', 'en'] as $locale) {
                app()->setLocale($locale);
                self::assertSame($block, Linked::matchingBlock($block, true), 'UK fallback block on another app locale is not opted in.');
                self::assertSame($data, Linked::presentationData($block, $data));
            }
            app()->setLocale('uk');
            foreach (['body-format', 'new-condition', 'old-answer', 'existing-linked-config', 'invalid-json'] as $change) {
                $foreign = clone $block; $changed = $data;
                if ($change === 'new-condition') { $changed['inputs'][0]['after'] .= ' Інша умова.'; }
                if ($change === 'old-answer') { $changed['selects'][0]['answer'] = 'Other answer'; }
                if ($change === 'existing-linked-config') { $changed['linked_practice'] = ['source' => 'theory_links']; }
                $foreign->body = $change === 'body-format' ? $block->body."\n"
                    : ($change === 'invalid-json' ? '{broken' : M26DetailPackage::json($changed));
                self::assertSame($foreign, Linked::matchingBlock($foreign, true), $change);
                self::assertSame($changed, Linked::presentationData($foreign, $changed), $change);
            }
            $foreignData = array_replace($data, ['title' => 'Foreign render-only title']);
            self::assertSame($foreignData, Linked::presentationData($block, $foreignData));
            $historical = $this->block($target, false);
            self::assertSame($historical, Linked::matchingBlock($historical, true), 'Historical course body is not eligible.');
            self::assertSame($historical, Linked::matchingBlock($historical, false));
        }
    }

    public function test_manifest_and_source_tampering_fail_closed_without_changing_frozen_files(): void
    {
        $root = storage_path('app/linked-practice-test-'.bin2hex(random_bytes(8)));
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        File::makeDirectory($root, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($root);
        $paths = [Linked::MANIFEST => Linked::MANIFEST_SHA, Package::SOURCE => Package::SOURCE_SHA];
        foreach ($paths as $path => $sha) {
            File::makeDirectory(dirname($root.'/'.$path), 0700, true, true);
            File::copy(base_path($path), $root.'/'.$path);
        }
        self::assertSame(Linked::load(), Linked::load($root));
        foreach ($paths as $path => $sha) {
            $bytes = File::get($root.'/'.$path);
            $changed = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            if ($path === Linked::MANIFEST) {
                $changed['targets'][0]['linked_practice']['seeder_classes'] = [Linked::BANKS[1]];
            } else {
                $changed['targets'][0]['after']['practice']['body'] .= ' changed';
            }
            File::put($root.'/'.$path, M26DetailPackage::json($changed));
            $error = null;
            try { Linked::load($root); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error);
            self::assertStringContainsString('hash differs', $error->getMessage());
            File::put($root.'/'.$path, $bytes);
            self::assertSame($sha, hash_file('sha256', base_path($path)));
        }
    }

    private function linkedQuestion(string $bank, string $level, string $type, ?string $blockUuid): Question
    {
        $n = ++$this->fixtureQuestion;
        $question = Question::create([
            'uuid' => sprintf('10000000-0000-4000-8000-%012d', $n),
            'question' => 'Склади речення номер '.$n.'.', 'difficulty' => 1, 'level' => $level,
            'seeder' => $bank, 'type' => $type,
        ]);
        if ($blockUuid !== null) {
            DB::table('question_theory_text_blocks')->insert([
                'question_uuid' => $question->uuid, 'text_block_uuid' => $blockUuid, 'position' => 0,
            ]);
        }
        foreach (['We', 'work', 'together'] as $i => $token) {
            $option = DB::table('question_options')->where('option', $token)->value('id')
                ?? DB::table('question_options')->insertGetId(['option' => $token]);
            DB::table('question_answers')->insert(['question_id' => $question->id, 'option_id' => $option, 'marker' => 'a'.($i + 1)]);
            DB::table('question_option_question')->insert(['question_id' => $question->id, 'option_id' => $option]);
        }

        return $question;
    }

    public function test_all_twelve_matchers_use_only_page_linked_type_four_exact_bank_and_cefr_then_render_the_existing_widget(): void
    {
        $this->rebuildComposeTestSchema();
        [, $source] = Package::load();
        $matcher = app(TextBlockToQuestionsMatcherService::class);
        foreach ($source['targets'] as $i => $target) {
            $block = $this->block($target);
            DB::table('text_blocks')->insert($block->getAttributes());
            $theoryUuid = sprintf('20000000-0000-4000-8000-%012d', $target['page']['id']);
            DB::table('text_blocks')->insert(['uuid' => $theoryUuid, 'page_id' => $block->page_id,
                'locale' => 'uk', 'type' => 'usage-panels', 'body' => '{}']);
            $expected = [];
            for ($j = 0; $j < 6; $j++) {
                $expected[] = $this->linkedQuestion(Linked::BANKS[$i], $block->level, '4', $theoryUuid)->id;
            }
            $this->linkedQuestion(Linked::BANKS[($i + 1) % 12], $block->level, '4', $theoryUuid);
            $this->linkedQuestion(Linked::BANKS[$i], $block->level, '0', $theoryUuid);
            $this->linkedQuestion(Linked::BANKS[$i], 'C2', '4', $theoryUuid);
            $this->linkedQuestion(Linked::BANKS[$i], $block->level, '4', 'foreign-page-block');
            $this->linkedQuestion(Linked::BANKS[$i], $block->level, '4', null);
            $snapshot = DB::table('text_blocks')->where('id', $block->id)->first();
            self::assertTrue($matcher->findBestQuestionsForTextBlock($block)->isEmpty(), 'No tags and no raw opt-in yields no widget.');
            $questionsByBlock = $matcher->findQuestionsForTextBlocks(new Collection([Linked::matchingBlock($block, true)]), 5);
            self::assertArrayHasKey($block->uuid, $questionsByBlock);
            $questions = $questionsByBlock[$block->uuid];
            self::assertSame(array_slice($expected, 0, 5), $questions->pluck('id')->all());
            self::assertSame([$block->level], $questions->pluck('level')->unique()->values()->all());
            self::assertSame([Linked::BANKS[$i]], $questions->pluck('seeder')->unique()->values()->all());
            self::assertSame(['4'], $questions->pluck('type')->unique()->values()->all());
            self::assertSame(0, DB::table('question_theory_text_blocks')->where('text_block_uuid', $block->uuid)->count());
            self::assertEquals($snapshot, DB::table('text_blocks')->where('id', $block->id)->first());

            $html = $this->render($block, $questions);
            $xp = $this->xpath($html);
            self::assertCount(1, $xp->query('//*[@data-sentence-builder]'));
            self::assertStringContainsString('Вправа 4. Побудуй речення', $html);
            self::assertCount(3, $xp->query('//*[contains(concat(" ", normalize-space(@class), " "), " theory-exercise ")]'));
            self::assertCount(2, $xp->query('//input[@type="text"]'));
            $config = Linked::load()['targets'][$i]['linked_practice'];
            app('view')->flushState();
            $reference = view('components.text-block-practice-questions', [
                'questions' => $questions, 'blockUuid' => $block->uuid,
                'title' => $config['title'], 'intro' => $config['intro'], 'footer' => $config['footer'],
            ])->render();
            $referenceXp = $this->xpath($reference);
            $widget = $xp->query('//*[@data-sentence-builder]')->item(0);
            $expectedWidget = $referenceXp->query('//*[@data-sentence-builder]')->item(0);
            self::assertSame($expectedWidget->ownerDocument->saveHTML($expectedWidget), $widget->ownerDocument->saveHTML($widget),
                'Exactly the existing widget DOM, not a new exercise renderer.');
            self::assertCount(0, $this->xpath($this->render($block, collect(), false))->query('//*[@data-sentence-builder]'));
        }
    }

    public function test_empty_or_only_foreign_question_pools_do_not_broaden_to_other_banks(): void
    {
        $this->rebuildComposeTestSchema();
        [, $source] = Package::load();
        $block = $this->block($source['targets'][0]);
        DB::table('text_blocks')->insert($block->getAttributes());
        $matcher = app(TextBlockToQuestionsMatcherService::class);
        $clone = Linked::matchingBlock($block, true);
        self::assertTrue($matcher->findBestQuestionsForTextBlock($clone)->isEmpty());
        $this->linkedQuestion(Linked::BANKS[1], $block->level, '4', $block->uuid);
        $this->linkedQuestion(Linked::BANKS[0], 'C2', '4', $block->uuid);
        $this->linkedQuestion(Linked::BANKS[0], $block->level, '0', $block->uuid);
        self::assertTrue($matcher->findBestQuestionsForTextBlock($clone)->isEmpty());
        self::assertCount(0, $this->xpath($this->render($block, collect()))->query('//*[@data-sentence-builder]'));
    }
}
