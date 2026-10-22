<?php

namespace Tests\Feature;

use App\Support\M77OnePrepositionsPhrasal;
use App\Support\M78PhrasalQuestionBasics as Package;
use App\Support\TheoryNativeContentRelease;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

/** Pure source validation: no application, SQL, HTTP, private row fixtures or source writes. */
class M78RetainedPracticeIdentityTest extends TestCase
{
    private const DRAFT_SHA = 'fec85aeac231a936a9dfaeee352ae8d0bca6c3a61c693933eb902789eae789d0';
    private const BANK = 'Database\\Seeders\\V3\\Polyglot\\IdentityFixtureSeeder';

    private function root(): string { return dirname(__DIR__, 2); }

    /** A prefreeze run must explicitly name the reviewed, teaching-data-only draft and its raw pin. */
    private function release(): array
    {
        $path = getenv('GRAMLYZE_M78_IDENTITY_DRAFT');
        if ($path !== false && $path !== '') {
            self::assertSame(self::DRAFT_SHA, getenv('GRAMLYZE_M78_IDENTITY_DRAFT_SHA'));
            self::assertFileExists($path); self::assertFalse(is_link($path));
            self::assertSame(self::DRAFT_SHA, hash_file('sha256', $path));
            $draft = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            self::assertSame(0, $draft['db_connections']); self::assertSame(0, $draft['db_writes']);
            self::assertSame(0, $draft['http_requests']);
            $fixture = [$draft['source_before'], $draft['patch'], $draft['master'], $draft['contract'],
                Package::draftProfile($draft['native_bindings']['bindings'])];
        } else {
            $frozen = Package::load($this->root());
            $fixture = [$frozen[0], $frozen[1], $frozen[2], $frozen[3], Package::profile()];
            Package::validate($fixture[0], $fixture[1], $fixture[2], $fixture[3]);
        }
        $this->validate($fixture);
        return $fixture;
    }

    private function validate(array $fixture): void
    {
        [$before, $patch, $master, $contract, $profile] = $fixture;
        (new TheoryNativeContentRelease($profile))->validate($before, $patch, $master, $contract);
        Package::assertEditorial($before, $patch, $master);
    }

    private function refuses(callable $call, string $case, ?string $message = null): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
        if ($message !== null) { self::assertStringContainsString($message, $caught->getMessage(), $case); }
    }

    /** Re-serialize a teaching mutation, so a test reaches the identity guard rather than a stale AFTER. */
    private function changedPractice(array $fixture, int $lesson, callable $change): array
    {
        $slot = Package::PRACTICE_SOURCE_INDICES[$lesson];
        $change($fixture[2]['lessons'][$lesson]['blocks'][$slot]['data']);
        $fixture[1]['targets'][$lesson]['after'] = (new TheoryNativeContentRelease($fixture[4]))->definition(
            $fixture[2]['lessons'][$lesson], $fixture[0]['targets'][$lesson]['before']);
        return $fixture;
    }

    private function practiceFixture(): array
    {
        $task = static fn (string $answer, array $options): array => ['answer' => $answer, 'accepted' => [$answer],
            'options' => $options, 'feedback' => array_fill_keys(array_map(mb_strtolower(...), $options), 'Пояснення відповіді.')];
        $data = [
            'selects' => [$task('is', ['is', 'are']), $task('are', ['is', 'are'])],
            'choices' => [$task('a', ['a', 'b']), $task('b', ['a', 'b'])],
            'inputs' => [
                $task('I am ready.', ['I am ready.']) + ['before' => 'ready. / I / am', 'after' => 'Я готовий.'],
                $task('She is here.', ['She is here.']) + ['before' => 'here. / She / is', 'after' => 'Вона тут.'],
            ],
            'linked_practice' => ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => [self::BANK]],
        ];
        $contract = ['counts' => ['selects' => 2, 'choices' => 2, 'inputs' => 2], 'sentence_input_indices' => [0, 1]];
        return [$data, $contract, $data];
    }

    private function assertNative(array $data, array $contract, ?array $captured): array
    {
        $ids = [];
        (new ReflectionMethod(TheoryNativeContentRelease::class, 'assertPractice'))->invokeArgs(
            new TheoryNativeContentRelease(['label' => 'M78 identity fixture']), [$data, [self::BANK], &$ids, $contract, $captured]);
        return $ids;
    }

    public function test_only_captured_absent_ids_allow_retained_full_sentence_inputs_without_invented_identity(): void
    {
        [$data, $contract, $captured] = $this->practiceFixture(); $original = $captured;
        self::assertSame([], $this->assertNative($data, $contract, $captured));
        self::assertSame($original, $captured, 'The captured source is a caller-owned immutable value.');
        $this->refuses(fn () => $this->assertNative($data, $contract, null), 'No source ledger means no missing-ID exemption.',
            'visible Ukrainian input condition or native bank');
    }

    public function test_existing_ids_cannot_be_removed_changed_or_repeated(): void
    {
        [$data, $contract, $captured] = $this->practiceFixture();
        $data['inputs'][0]['id'] = $captured['inputs'][0]['id'] = 'captured-input-1';
        self::assertSame(['captured-input-1' => true], $this->assertNative($data, $contract, $captured));
        $removed = $data; unset($removed['inputs'][0]['id']);
        $changed = $data; $changed['inputs'][0]['id'] = 'replacement-input';
        foreach (['removed' => $removed, 'changed' => $changed] as $case => $candidate) {
            $this->refuses(fn () => $this->assertNative($candidate, $contract, $captured), $case,
                'captured native task identity/absence changed');
        }
        $duplicated = $data; $duplicated['inputs'][1]['id'] = 'captured-input-1';
        $this->refuses(fn () => $this->assertNative($duplicated, $contract, $captured), 'An old absent identity cannot borrow an existing ID.');
    }

    public function test_absent_ids_and_source_indices_cannot_be_invented_and_existing_indices_are_strict(): void
    {
        [$data, $contract, $captured] = $this->practiceFixture();
        foreach (['id' => 'invented-id', 'source_index' => 1] as $field => $value) {
            $candidate = $data; $candidate['inputs'][0][$field] = $value;
            $this->refuses(fn () => $this->assertNative($candidate, $contract, $captured), $field,
                'captured native task identity/absence changed');
        }
        $data['inputs'][0]['source_index'] = $captured['inputs'][0]['source_index'] = 7;
        self::assertSame([], $this->assertNative($data, $contract, $captured));
        foreach ([null, '7', 8] as $index) {
            $candidate = $data;
            if ($index === null) { unset($candidate['inputs'][0]['source_index']); }
            else { $candidate['inputs'][0]['source_index'] = $index; }
            $this->refuses(fn () => $this->assertNative($candidate, $contract, $captured), 'Existing numeric source_index is exact.');
        }
    }

    public function test_new_inputs_still_require_unique_nonempty_ids_even_with_a_retained_ledger(): void
    {
        [$data, $contract, $captured] = $this->practiceFixture();
        $data['inputs'][] = ['before' => 'closed. / It / is', 'after' => 'Воно зачинене.', 'answer' => 'It is closed.',
            'accepted' => ['It is closed.'], 'feedback' => ['it is closed.' => 'Стверджувальна відповідь.']];
        $contract['counts']['inputs'] = 3; $contract['sentence_input_indices'][] = 2;
        foreach ([null, '', 7] as $id) {
            $candidate = $data;
            if ($id !== null) { $candidate['inputs'][2]['id'] = $id; }
            $this->refuses(fn () => $this->assertNative($candidate, $contract, $captured), 'A new sentence cannot inherit absent-ID permission.',
                'new native task requires a stable ID');
        }
        $data['inputs'][2]['id'] = 'm78-new-input';
        self::assertSame(['m78-new-input' => true], $this->assertNative($data, $contract, $captured));
    }

    public function test_full_m78_validation_preserves_all_forty_nine_old_positions_including_four_demos(): void
    {
        $fixture = $this->release(); $original = $fixture; $retained = $demos = $oldInputs = 0;
        foreach ([2, 3, 4, 5] as $i) {
            $slot = Package::PRACTICE_SOURCE_INDICES[$i];
            $old = json_decode($fixture[0]['targets'][$i]['before']['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR);
            $new = $fixture[2]['lessons'][$i]['blocks'][$slot]['data'];
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
                foreach ($old[$group] ?? [] as $index => $task) {
                    ++$retained;
                    if (isset($task['example_original'])) { ++$demos; }
                    if ($group === 'inputs') { ++$oldInputs; }
                    foreach (['id', 'source_index'] as $field) {
                        self::assertSame(array_key_exists($field, $task), array_key_exists($field, $new[$group][$index]));
                        if (array_key_exists($field, $task)) { self::assertSame($task[$field], $new[$group][$index][$field]); }
                    }
                }
            }
        }
        self::assertSame(49, $retained); self::assertSame(4, $demos); self::assertSame(16, $oldInputs);
        self::assertSame($original, $fixture, 'Full validation must not fill absent source identities.');
    }

    public function test_full_validation_rejects_fabricated_old_identity_even_when_after_is_reserialized(): void
    {
        $fixture = $this->release();
        foreach (['id' => 'm78-invented-old-input', 'source_index' => 0] as $field => $value) {
            $changed = $this->changedPractice($fixture, 4, static function (&$data) use ($field, $value): void {
                $data['inputs'][0][$field] = $value;
            });
            $this->refuses(fn () => $this->validate($changed), $field, 'captured native task identity/absence changed');
        }
    }

    public function test_full_validation_requires_ids_for_new_answer_sentences_and_new_meaning_choices(): void
    {
        $fixture = $this->release();
        foreach ([[5, 'inputs', 4], [4, 'choices', 0]] as [$lesson, $group, $index]) {
            foreach ([null, ''] as $id) {
                $changed = $this->changedPractice($fixture, $lesson, static function (&$data) use ($group, $index, $id): void {
                    if ($id === null) { unset($data[$group][$index]['id']); }
                    else { $data[$group][$index]['id'] = $id; }
                });
                $this->refuses(fn () => $this->validate($changed), 'New task '.$group.':'.$index,
                    'new native task requires a stable ID');
            }
        }
    }

    public function test_demo_positions_do_not_bypass_retained_identity_or_absence_guards(): void
    {
        $fixture = $this->release();
        foreach (['id' => 'm78-invented-demo', 'source_index' => 0] as $field => $value) {
            $changed = $this->changedPractice($fixture, 4, static function (&$data) use ($field, $value): void {
                $data['rephrase'][0][$field] = $value;
            });
            $this->refuses(fn () => $this->validate($changed), 'An old demo is a retained position, not an identity escape hatch.');
        }
    }

    public function test_full_validation_rejects_foreign_or_stale_captured_practice_bindings(): void
    {
        $fixture = $this->release();
        $foreign = $fixture; $foreign[0]['targets'][4]['before']['seeder']['class'] = Package::OWNERS[5];
        $this->refuses(fn () => $this->validate($foreign), 'A different owner cannot provide the identity ledger.');
        $foreign = $fixture; $foreign[1]['targets'][4]['page_binding']['page_category_id'] = 21;
        $this->refuses(fn () => $this->validate($foreign), 'A foreign category cannot provide a captured practice.');
        foreach (['uuid' => '00000000-0000-5000-8000-000000000000', 'level' => 'A2', 'source_index' => 11] as $field => $value) {
            $stale = $fixture; $stale[4]['bindings'][4]['existing_practice'][$field] = $value;
            $this->refuses(fn () => $this->validate($stale), 'Captured practice '.$field.' must stay exact.');
        }
        $staleBody = $fixture;
        $staleBody[1]['targets'][4]['after']['page']['blocks'][12]['body'] .= ' ';
        $this->refuses(fn () => $this->validate($staleBody), 'A stale stored body is not the current exact projection.');
    }

    public function test_m77_html_legacy_practice_remains_a_projection_not_a_native_identity_ledger(): void
    {
        $release = M77OnePrepositionsPhrasal::load($this->root());
        M77OnePrepositionsPhrasal::validate($release[0], $release[1], $release[2], $release[3]);
        $old = $release[0]['targets'][0]['before']['page']['blocks'][8];
        self::assertSame('box', $old['type']); self::assertNull(json_decode($old['body'], true));
        $graded = $transferred = 0;
        foreach ($release[2]['lessons'] as $i => $lesson) {
            $data = $lesson['blocks'][M77OnePrepositionsPhrasal::PRACTICE_SOURCE_INDICES[$i]]['data'];
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
                foreach ($data[$group] as $task) {
                    if (isset($task['example_original'])) { continue; }
                    ++$graded;
                    if ($i === 0 && $group === 'selects') { ++$transferred; self::assertArrayNotHasKey('id', $task); }
                }
            }
        }
        self::assertSame(42, $graded); self::assertSame(8, $transferred);
    }
}
