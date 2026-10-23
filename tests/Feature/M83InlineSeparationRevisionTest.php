<?php

namespace Tests\Feature;

use App\Services\M26ContentPatch;
use App\Services\M83InlineSeparationPatch as Patch;
use App\Support\M26DetailPackage as Digest;
use App\Support\M83B2RevisionBasicGrammar as Package;
use App\Support\TheoryInlineHtml;
use DOMDocument;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Exact seven authored breaks; synthetic SQLite only, never live-local acceptance. */
class M83InlineSeparationRevisionTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const PREVIOUS_SOURCE = 'database/content-patches/m83-b2-revision-basic-grammar.v1.json';
    private const PREVIOUS_MASTER = 'docs/content/m83-b2-revision-basic-grammar.v1.json';
    private const PREVIOUS_CHECKSUMS = 'docs/content/m83-source-checksums.v1.json';
    private const MANIFEST = 'docs/content/m83-inline-separation.v1.0.1.json';
    private const MANIFEST_SHA = 'fe8dafb7673451b136723b328662b3253a82a523b2a9910f1e3b61ebd5498f97';
    private const TARGET_ID = 1153;
    private const TARGET_UUID = '2c280a48-644e-5876-8435-bf975022255b';

    private string $source;
    private string $root;
    private string $private;
    private array $patch;
    private array $previous;
    private array $baseline;

    protected function setUp(): void
    {
        parent::setUp();
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('testing', app()->environment());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default'));
        self::assertSame('array', config('session.driver'));
        self::assertNotSame(base_path(), app()->environmentPath());
        $this->rebuildComposeTestSchema();
        app()->setLocale('uk');
        $this->source = str_replace('\\', '/', dirname(__DIR__, 2));
        [, $this->patch] = Package::load($this->source);
        $this->previous = $this->readArtifactJson(self::PREVIOUS_SOURCE);
        $this->root = storage_path('app/m83-inline-fixture-'.bin2hex(random_bytes(7)));
        $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        File::makeDirectory($this->private, 0700, true);
        foreach (Patch::sourcePaths($this->source) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true);
            File::copy($this->source.'/'.$path, $this->root.'/'.$path);
        }
        foreach ([[2, 'basic-grammar'], [59, 'mixed-revision']] as [$id, $slug]) {
            DB::table('page_categories')->insert(['id' => $id, 'parent_id' => null, 'slug' => $slug,
                'title' => $slug, 'type' => 'theory', 'language' => 'uk']);
        }
        $time = '2026-01-01 12:00:00';
        foreach ($this->previous['targets'] as $i => $target) {
            $definition = $target['after'];
            $owner = $target['identity'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i],
                'slug' => $definition['slug'], 'title' => $definition['page']['title'], 'text' => $definition['page']['subtitle_text'],
                'type' => 'theory', 'seeder' => $owner, 'created_at' => $time, 'updated_at' => $time]);
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'],
                'level' => $definition['page']['subtitle_level'] ?? null,
                'uuid_key' => $definition['page']['subtitle_uuid_key'] ?? 'subtitle'];
            foreach ([$subtitle, ...$definition['page']['blocks']] as $position => $block) {
                $id = $i === 4 && $position === 2 ? self::TARGET_ID : 830000 + $i * 100 + $position;
                DB::table('text_blocks')->insert(['id' => $id, 'uuid' => Digest::uuid($owner, $block, $position),
                    'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i], 'locale' => 'uk',
                    'sort_order' => $position, 'type' => $block['type'], 'column' => $block['column'],
                    'heading' => $block['heading'] ?? null, 'css_class' => $block['css_class'] ?? null,
                    'level' => $block['level'] ?? null, 'body' => $block['body'], 'seeder' => $owner,
                    'created_at' => $time, 'updated_at' => $time]);
            }
            $hero = (array) DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->sole();
            $questionId = 8300000 + $i;
            $questionUuid = 'm83-inline-synthetic-'.$i;
            DB::table('questions')->insert(['id' => $questionId, 'uuid' => $questionUuid,
                'question' => 'Незмінне ізольоване питання; не робочий банк.', 'type' => '4',
                'level' => $i === 0 ? 'B2' : 'A1', 'seeder' => 'SyntheticInlineBank',
                'created_at' => $time, 'updated_at' => $time]);
            DB::table('question_theory_text_blocks')->insert(['question_uuid' => $questionUuid,
                'text_block_uuid' => $hero['uuid'], 'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
            DB::table('question_options')->insert(['id' => $questionId, 'option' => 'Unchanged synthetic option '.$i,
                'created_at' => $time, 'updated_at' => $time]);
            DB::table('question_answers')->insert(['question_id' => $questionId, 'option_id' => $questionId,
                'marker' => 'a1', 'created_at' => $time, 'updated_at' => $time]);
            DB::table('question_option_question')->insert(['question_id' => $questionId, 'option_id' => $questionId, 'flag' => null]);
            unset($hero['id']);
            foreach (['en', 'pl'] as $locale) {
                $hero['uuid'] = 'm83-inline-foreign-'.$i.'-'.$locale;
                $hero['locale'] = $locale;
                $hero['body'] = 'Keep foreign '.$locale;
                DB::table('text_blocks')->insert($hero);
            }
        }
        DB::table('saved_grammar_tests')->insert(['id' => 1, 'uuid' => 'm83-inline-saved', 'name' => 'Unchanged saved fixture',
            'slug' => 'm83-inline-saved', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('saved_grammar_test_questions')->insert(['saved_grammar_test_id' => 1,
            'question_uuid' => 'm83-inline-synthetic-0', 'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
        foreach (['m83_inline_progress', 'm83_inline_course_membership'] as $table) {
            DB::connection()->getSchemaBuilder()->create($table, static function (Blueprint $schema): void {
                $schema->id(); $schema->text('payload');
            });
            DB::table($table)->insert(['payload' => 'Keep exact protected synthetic state.']);
        }
        self::assertSame(self::TARGET_UUID, DB::table('text_blocks')->where('id', self::TARGET_ID)->value('uuid'));
        $this->baseline = (new M83SyntheticInlineSeparation(DB::connection(), $this->root.'/database', $this->private))->rawFingerprints();
    }

    private function readArtifactJson(string $path): array
    {
        return json_decode(File::get($this->source.'/'.$path), true, flags: JSON_THROW_ON_ERROR);
    }

    private function service(): M83SyntheticInlineSeparation
    {
        $service = new M83SyntheticInlineSeparation(DB::connection(), $this->root.'/database', $this->private);
        $service->baselineSql = $this->baseline;
        return $service;
    }

    private function file(string $name): string { return $this->private.'/'.$name.'.json'; }

    private function refused(callable $call): void
    {
        $error = null;
        try { $call(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error);
    }

    private function snapshot(): array
    {
        $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false);
        sort($tables); $out = [];
        foreach ($tables as $table) {
            if (str_starts_with($table, 'sqlite_')) { continue; }
            $query = DB::table($table);
            $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $out[$table] = $query->get()->map(static fn ($row) => (array) $row)->all();
        }
        return $out;
    }

    private static function pairs(): array
    {
        return [
            ['Stop! — Зупинись!', 'Be quiet! — Поводься тихо!', 'Leave now! — Іди звідси зараз!'],
            ['Please help me. — Будь ласка, допоможи мені.', 'Pass the salt, please. — Передай сіль, будь ласка.'],
            ['Take an umbrella. — Візьми парасольку.', 'Get some rest. — Відпочинь трохи.'],
            ['Turn left. — Поверни ліворуч.', 'Press the button. — Натисни кнопку.'],
            ['Be careful! — Будь обережний!', 'Watch out! — Обережно!'],
            ['Come in! — Заходь!', 'Have a seat. — Сідай, будь ласка.'],
        ];
    }

    public function test_exact_seven_native_breaks_separate_thirteen_unchanged_bilingual_pairs_in_six_fields(): void
    {
        [,, $master] = Package::load($this->source);
        $old = $this->readArtifactJson(self::PREVIOUS_MASTER);
        $manifest = $this->readArtifactJson(self::MANIFEST);
        self::assertSame(self::MANIFEST_SHA, hash_file('sha256', $this->source.'/'.self::MANIFEST));
        self::assertSame('1.0.0', Package::VERSION);
        self::assertSame('1.0.0', $master['version']);
        self::assertSame('1.0.1', $master['author_revision']);
        self::assertSame([2, 1, 1, 1, 1, 1], array_column($manifest['changes'], 'added_br'));
        self::assertSame(7, $manifest['added_br']);
        self::assertSame(13, $manifest['bilingual_pairs']);
        self::assertCount(6, $manifest['changes']);
        $pairs = 0; $breaks = 0;
        foreach (self::pairs() as $index => $expected) {
            $before = $old['lessons'][4]['blocks'][1]['data']['items'][$index]['subtitle'];
            $after = $master['lessons'][4]['blocks'][1]['data']['items'][$index]['subtitle'];
            self::assertSame(str_replace('</p><p>', '</p><br><p>', $before), $after);
            self::assertSame($before, str_replace('<br>', '', $after));
            self::assertSame($before, $manifest['changes'][$index]['before']);
            self::assertSame($after, $manifest['changes'][$index]['after']);
            self::assertSame('/lessons/4/blocks/1/data/items/'.$index.'/subtitle', $manifest['changes'][$index]['pointer']);
            $rendered = TheoryInlineHtml::render($after)->toHtml();
            self::assertSame($manifest['changes'][$index]['after_native_inline_html'], $rendered);
            self::assertSame(count($expected) - 1, substr_count($rendered, '<br>'));
            self::assertStringNotContainsString('&lt;br', $rendered);
            $plain = array_map(static fn ($line) => html_entity_decode(strip_tags($line), ENT_QUOTES | ENT_HTML5, 'UTF-8'), explode('<br>', $rendered));
            self::assertSame($expected, $plain, 'No sentence or translation is changed, dropped or concatenated.');
            $dom = new DOMDocument('1.0', 'UTF-8');
            $previousErrors = libxml_use_internal_errors(true);
            try { $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$rendered.'</body></html>', LIBXML_NONET); }
            finally { libxml_clear_errors(); libxml_use_internal_errors($previousErrors); }
            self::assertSame(count($expected) - 1, $dom->getElementsByTagName('br')->length);
            $pairs += count($plain); $breaks += substr_count($rendered, '<br>');
        }
        self::assertSame(13, $pairs);
        self::assertSame(7, $breaks);
    }

    public function test_latest_package_validates_without_changing_old_frozen_artifacts_five_other_owners_or_any_practice(): void
    {
        [$before, $active, $master, $contract] = Package::load($this->source);
        Package::validate($before, $active, $master, $contract);
        [$manifest, $previous, $successor] = Patch::revision($this->source);
        self::assertSame($this->previous, $previous);
        self::assertSame($active, $successor);
        self::assertSame('1.0.1', $manifest['author_revision']);
        $prior = $this->readArtifactJson(self::PREVIOUS_MASTER);
        $copy = $master;
        unset($copy['author_revision']);
        foreach (range(0, 5) as $index) {
            $copy['lessons'][4]['blocks'][1]['data']['items'][$index]['subtitle'] = $prior['lessons'][4]['blocks'][1]['data']['items'][$index]['subtitle'];
        }
        self::assertSame($prior, $copy, 'Only six subtitles and one explicit author-revision marker differ.');
        $copy = $active;
        $copy['targets'][4]['after']['page']['blocks'][1]['body'] = $previous['targets'][4]['after']['page']['blocks'][1]['body'];
        self::assertSame($previous, $copy, 'The patch differs in exactly one old body; no Page or practice changes.');
        self::assertSame('cddc5b76cf3f65286014f1a4a3a51b764d565ada1c68ae94c2684595a8eb2ce5',
            hash_file('sha256', $this->source.'/'.self::PREVIOUS_CHECKSUMS));
        $checks = $this->readArtifactJson(self::PREVIOUS_CHECKSUMS);
        foreach ($checks['artifacts'] as $record) {
            self::assertSame($record['sha256'], hash_file('sha256', $this->source.'/'.$record['path']), $record['path']);
            self::assertSame($record['bytes'], filesize($this->source.'/'.$record['path']));
        }
        foreach ($active['targets'] as $index => $target) {
            $path = $target['path'];
            self::assertSame($target['after'], $this->readArtifactJson($path));
            if ($index !== 4) {
                self::assertSame($previous['targets'][$index], $target);
                $pin = array_values(array_filter($checks['active_definitions'], static fn ($row) => $row['path'] === $path));
                self::assertCount(1, $pin);
                self::assertSame($pin[0]['sha256'], hash_file('sha256', $this->source.'/'.$path));
            }
            $practice = Package::PRACTICE_SOURCE_INDICES[$index];
            self::assertSame($prior['lessons'][$index]['blocks'][$practice], $master['lessons'][$index]['blocks'][$practice]);
            self::assertSame($prior['lessons'][$index]['details'], $master['lessons'][$index]['details']);
        }
        self::assertSame('66917ee713f20762e59442c0b099974b7c56fa9420e786b8130618e1f8b3c6b1',
            hash_file('sha256', $this->source.'/'.Package::CONTRACT));
        self::assertSame(M26ContentPatch::class, get_parent_class(Patch::class));
        self::assertSame(1736, strlen($previous['targets'][4]['after']['page']['blocks'][1]['body']));
        self::assertSame(1764, strlen($active['targets'][4]['after']['page']['blocks'][1]['body']));
    }

    public function test_preview_applies_only_one_raw_backed_up_body_and_both_repeats_are_zero_write_noops(): void
    {
        $service = $this->service(); $old = $this->snapshot();
        $plan = $service->savePlan($this->file('preview'));
        self::assertSame($old, $this->snapshot());
        self::assertSame('before', $plan['state']);
        self::assertCount(1, $plan['updates']);
        self::assertSame([], $plan['inserts']);
        $update = $plan['updates'][0];
        self::assertSame('text_blocks', $update['table']);
        self::assertSame(self::TARGET_ID, $update['id']);
        self::assertSame(self::TARGET_UUID, $update['uuid']);
        self::assertSame(Package::OWNERS[4], $update['seeder']);
        self::assertSame(['body'], array_keys($update['before']));
        self::assertSame(['body'], array_keys($update['after']));
        $raw = array_values(array_filter($plan['pages'][Package::OWNERS[4]]['blocks'], static fn ($row) => $row['id'] === self::TARGET_ID));
        self::assertCount(1, $raw);
        self::assertSame($this->previous['targets'][4]['after']['page']['blocks'][1]['body'], $raw[0]['body']);
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame('applied', $result['status']);
        self::assertSame(1, $result['updated']);
        self::assertSame(0, $result['inserted']);
        self::assertSame($plan, json_decode(File::get($this->file('backup')), true, flags: JSON_THROW_ON_ERROR));
        $expected = $old;
        foreach ($expected['text_blocks'] as &$row) {
            if ($row['id'] === self::TARGET_ID) { $row['body'] = $this->patch['targets'][4]['after']['page']['blocks'][1]['body']; }
        }
        unset($row);
        self::assertSame($expected, $this->snapshot(), 'Every other row/value/identity/timestamp remains exact.');
        $after = $service->savePlan($this->file('after'));
        self::assertSame('after', $after['state']);
        self::assertSame([], $after['updates']);
        self::assertSame([], $after['inserts']);
        self::assertSame($plan['protected'], $after['protected']);
        foreach (['preview', 'after'] as $name) {
            $result = $service->apply($this->file($name), $this->file('unused-'.$name));
            self::assertSame('no-op', $result['status']);
            self::assertSame(0, $result['updated']); self::assertSame(0, $result['inserted']);
            self::assertFileDoesNotExist($this->file('unused-'.$name));
            self::assertSame($expected, $this->snapshot());
        }
    }

    public function test_partial_separator_body_or_manual_text_is_not_treated_as_either_exact_state(): void
    {
        $old = $this->previous['targets'][4]['after']['page']['blocks'][1]['body'];
        $new = $this->patch['targets'][4]['after']['page']['blocks'][1]['body'];
        foreach (range(1, 6) as $count) {
            $seen = 0;
            $partial = preg_replace_callback('~</p><p>~', static function (array $match) use (&$seen, $count): string {
                return ++$seen <= $count ? '</p><br><p>' : $match[0];
            }, $old);
            self::assertNotSame($old, $partial); self::assertNotSame($new, $partial);
            DB::table('text_blocks')->where('id', self::TARGET_ID)->update(['body' => $partial]);
            $snapshot = $this->snapshot(); $this->refused(fn () => $this->service()->plan());
            self::assertSame($snapshot, $this->snapshot());
        }
        DB::table('text_blocks')->where('id', self::TARGET_ID)->update(['body' => $old]);
    }

    public function test_target_metadata_timestamps_and_foreign_question_course_and_progress_rows_fail_closed(): void
    {
        $row = (array) DB::table('text_blocks')->where('id', self::TARGET_ID)->sole();
        foreach (['uuid' => 'unknown', 'seeder' => 'ForeignOwner', 'page_id' => 999, 'page_category_id' => 999,
            'locale' => 'en', 'sort_order' => 3, 'type' => 'box', 'column' => 'right', 'level' => 'A1',
            'heading' => 'Changed heading', 'css_class' => 'redesign', 'body' => 'Different text',
            'updated_at' => '2026-02-01 00:00:00'] as $field => $value) {
            DB::table('text_blocks')->where('id', self::TARGET_ID)->update([$field => $value]);
            $snapshot = $this->snapshot(); $this->refused(fn () => $this->service()->plan());
            self::assertSame($snapshot, $this->snapshot(), $field);
            DB::table('text_blocks')->where('id', self::TARGET_ID)->update([$field => $row[$field]]);
        }
        $foreign = DB::table('text_blocks')->where('locale', 'pl')->orderBy('id')->value('id');
        foreach ([['questions', 8300000, 'question'], ['text_blocks', $foreign, 'body'],
            ['m83_inline_progress', 1, 'payload'], ['m83_inline_course_membership', 1, 'payload'],
            ['saved_grammar_test_questions', 1, 'position'], ['question_answers', 1, 'marker']] as [$table, $id, $field]) {
            $before = DB::table($table)->where('id', $id)->value($field);
            DB::table($table)->where('id', $id)->update([$field => $field === 'position' ? 9 : 'Unknown external state']);
            $snapshot = $this->snapshot(); $this->refused(fn () => $this->service()->plan());
            self::assertSame($snapshot, $this->snapshot(), $table);
            DB::table($table)->where('id', $id)->update([$field => $before]);
        }
    }

    public function test_update_failure_rolls_back_the_single_body_and_restore_remains_out_of_scope(): void
    {
        $service = $this->service(); $plan = $service->savePlan($this->file('rollback-preview'));
        $old = $this->snapshot(); $service->failAtUpdate = 0;
        $this->refused(fn () => $service->apply($this->file('rollback-preview'), $this->file('rollback-backup')));
        self::assertSame($old, $this->snapshot());
        self::assertSame($plan, json_decode(File::get($this->file('rollback-backup')), true, flags: JSON_THROW_ON_ERROR));
        $service->failAtUpdate = null;
        self::assertSame($plan, $service->plan());
        $this->refused(fn () => $service->restore($this->file('rollback-backup')));
        self::assertSame($old, $this->snapshot());
    }

    public function test_stale_source_or_rehashed_tampered_preview_is_refused_before_a_new_backup(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $plan = json_decode(File::get($this->file('preview')), true, flags: JSON_THROW_ON_ERROR);
        $plan['updates'][0]['after']['body'] .= ' Unauthorized';
        $plan['updates'][0]['after_sha256'] = Digest::digest($plan['updates'][0]['after']);
        unset($plan['sha256']); $plan['sha256'] = Digest::digest($plan);
        File::put($this->file('tampered'), json_encode($plan, JSON_THROW_ON_ERROR));
        $old = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('tampered'), $this->file('no-tampered')));
        self::assertSame($old, $this->snapshot()); self::assertFileDoesNotExist($this->file('no-tampered'));
        $path = $this->root.'/'.$this->patch['targets'][0]['path'];
        File::put($path, File::get($path)."\n");
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('no-stale-source')));
        self::assertSame($old, $this->snapshot()); self::assertFileDoesNotExist($this->file('no-stale-source'));
    }

    public function test_existing_evidence_and_immutable_predecessor_are_not_overwritten_or_bypassed(): void
    {
        $service = $this->service(); $plan = $service->savePlan($this->file('preview')); $old = $this->snapshot();
        $this->refused(fn () => $service->savePlan($this->file('preview')));
        self::assertSame($plan, json_decode(File::get($this->file('preview')), true, flags: JSON_THROW_ON_ERROR));
        File::put($this->file('occupied'), 'Keep caller-owned backup exact.');
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('occupied')));
        self::assertSame('Keep caller-owned backup exact.', File::get($this->file('occupied')));
        self::assertSame($old, $this->snapshot());
        $path = $this->root.'/'.self::PREVIOUS_SOURCE;
        File::put($path, File::get($path)."\n");
        $this->refused(fn () => $service->plan());
        self::assertSame($old, $this->snapshot());
    }
}

class M83SyntheticInlineSeparation extends Patch
{
    public array $baselineSql = [];
    public ?int $failAtUpdate = null;

    public function rawFingerprints(): array { return $this->correctionFingerprints(); }

    protected function baselineEvidence(): array
    {
        IsolatedTestEnvironment::assertSafeDatabase($this->db);
        if ($this->baselineSql === []) { throw new RuntimeException('Synthetic inline baseline unavailable.'); }
        return ['sql_after' => $this->baselineSql];
    }

    protected function afterUpdate(int $index): void
    {
        if ($this->failAtUpdate === $index) { throw new RuntimeException('Injected isolated inline update failure.'); }
    }
}
