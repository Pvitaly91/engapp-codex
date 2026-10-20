<?php

namespace Tests\Feature;

use App\Services\M70ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M70IntensifiersNounBasics as Package;
use App\Support\TheoryNativeContentRelease;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;
use RuntimeException;
use Tests\TestCase;

class M70AuthoredReleaseTest extends TestCase
{
    private function refuses(callable $call, string $case): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    public function test_frozen_projection_preserves_thirty_two_old_slots_and_both_null_level_practice_uuids(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        Package::validate($before, $patch, $master, $contract);
        self::assertSame('be2b1c2549e70d91cc6f3c47cd7708a68ff7eb71', $master['base_sha']);
        self::assertSame([277, 89, 85], Package::PAGE_IDS);
        self::assertSame([52, 16, 16], Package::CATEGORY_IDS);
        self::assertSame(['B1', null, null], array_column($contract['targets'], 'level'));
        self::assertSame([6, 14, 12], Package::CONTINUING_COUNTS);
        self::assertSame([6, 12, 10], Package::PRACTICE_SOURCE_INDICES);
        $tasks = 0;
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $lesson = $master['lessons'][$i];
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            M70ContentPatch::assertDefinitionScope($old, $target['after'], $target, $i);
            self::assertCount([7, 14, 12][$i], $lesson['blocks']);
            self::assertSame([], $lesson['details'], 'No forced short detail toggles.');
            $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected);
            foreach ($old['page']['blocks'] as $slot => $source) {
                $new = $target['after']['page']['blocks'][$slot];
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1), M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
                self::assertSame(array_key_exists('level', $source), array_key_exists('level', $new));
                unset($source['body'], $new['body']); self::assertSame($source, $new);
            }
            $data = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
            foreach (['selects'=>[2,4,4], 'choices'=>[2,2,2], 'inputs'=>[2,6,5]] as $group => $counts) {
                self::assertCount($counts[$i], $data[$group]); $tasks += $counts[$i];
                foreach ($data[$group] as $task) {
                    self::assertContains($task['answer'], $task['accepted']);
                    foreach ($task['feedback'] as $text) { self::assertDoesNotMatchRegularExpression('/<\/?[a-z]|&(?:#[0-9]+|[a-z]+);/iu', $text); }
                }
            }
            if ($i > 0) {
                self::assertSame(Package::PRACTICE_UUIDS[$i], M26DetailPackage::uuid($target['identity'], $target['after']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]], Package::PRACTICE_SOURCE_INDICES[$i] + 1));
                foreach (array_slice($data['selects'], 0, 4) as $task) { self::assertArrayNotHasKey('id', $task); self::assertArrayNotHasKey('source_index', $task); }
                self::assertCount($i === 1 ? 4 : 3, $data['rephrase']);
                self::assertNotEmpty($data['rephrase'][0]['example_original']);
            }
            foreach ($target['after']['page']['blocks'] as $block) { self::assertLessThanOrEqual(65535, strlen($block['body'])); }
        }
        self::assertSame(29, $tasks); // Five retained rephrase questions are additional graded tasks.
        foreach (Package::profile()['artifacts'] as $path => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha);
            self::assertSame($sha, hash_file('sha256', base_path($path)));
        }
        $checksums = json_decode(file_get_contents(base_path(Package::CHECKSUMS)), true, flags: JSON_THROW_ON_ERROR);
        foreach ($checksums['artifacts'] as $a) { self::assertSame($a['sha256'], hash_file('sha256', base_path($a['path']))); }
    }

    public function test_identity_level_uuid_practice_and_teaching_mutations_fail_even_when_reprojected(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        $mutations = [
            'owner'=>static function (&$b,&$p,&$m,&$c): void { $m['lessons'][0]['identity'] = Package::OWNERS[1]; },
            'locale'=>static function (&$b,&$p,&$m,&$c): void { $m['lessons'][1]['locale'] = 'en'; },
            'category'=>static function (&$b,&$p,&$m,&$c): void { $p['targets'][1]['page_binding']['page_category_id'] = 52; },
            'null-level'=>static function (&$b,&$p,&$m,&$c): void { $m['lessons'][1]['blocks'][12]['level'] = 'A2'; },
            'practice-uuid'=>static function (&$b,&$p,&$m,&$c): void { $b['targets'][2]['before']['page']['blocks'][10]['uuid_key'] = 'foreign'; },
            'stale'=>static function (&$b,&$p,&$m,&$c): void { $p['targets'][0]['after']['page']['blocks'][1]['body'] .= "\n"; },
            'retained-id'=>static function (&$b,&$p,&$m,&$c): void { $m['lessons'][1]['blocks'][12]['data']['selects'][0]['id'] = 'new-identity'; },
            'retained-rephrase'=>static function (&$b,&$p,&$m,&$c): void { array_pop($m['lessons'][2]['blocks'][10]['data']['rephrase']); },
            'practice-data'=>static function (&$b,&$p,&$m,&$c): void { $m['lessons'][0]['blocks'][6]['data']['inputs'][0]['accepted'][] = 'Wrong'; },
            'extra-practice'=>static function (&$b,&$p,&$m,&$c): void { $m['lessons'][1]['blocks'][] = $m['lessons'][1]['blocks'][12]; },
            'bank'=>static function (&$b,&$p,&$m,&$c): void { $c['targets'][1]['bank_seeders'] = []; },
            'view'=>static function (&$b,&$p,&$m,&$c): void { $m['lessons'][0]['blocks'][1]['data']['native_view'] = 'foreign'; },
        ];
        foreach ($mutations as $case => $mutate) {
            $b=$before; $p=$patch; $m=$master; $c=$contract; $mutate($b,$p,$m,$c);
            $this->refuses(function () use ($b,$p,$m,$c,$case): void {
                if (!in_array($case, ['stale','practice-uuid'], true)) {
                    foreach ($m['lessons'] as $i=>$lesson) { $p['targets'][$i]['after'] = Package::definition($lesson, $b['targets'][$i]['before']); }
                }
                Package::validate($b,$p,$m,$c);
            }, $case);
        }
        $profile=Package::profile(); $profile['bindings'][1]['existing_practice']['level']='A2';
        $this->refuses(fn ()=>(new TheoryNativeContentRelease($profile))->existingPractice($profile['bindings'][1],$before['targets'][1]['before']), 'NULL practice is not A2');
    }

    public function test_dynamic_registry_has_all_previous_owners_and_each_m70_exact_type_four_bank(): void
    {
        [, $patch, , $contract] = Package::load();
        $contracts=Acceptance::contracts(base_path()); $targets=array_column(Acceptance::discoverTargets(base_path()), null, 'identity');
        self::assertSame([], array_values(array_diff(array_keys($contracts),array_keys($targets))));
        foreach ($contract['targets'] as $i=>$c) {
            self::assertSame($c,$contracts[Package::OWNERS[$i]]);
            self::assertSame([], $targets[Package::OWNERS[$i]]['errors']);
            self::assertSame($patch['targets'][$i]['local_url'],$c['local_url']);
        }
    }
}
