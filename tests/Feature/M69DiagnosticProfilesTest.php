<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M69AdjectivesAdverbs as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure literal-profile parsing and unsaved synthetic insert fixtures. No Laravel boot, DB or HTTP. */
class M69DiagnosticProfilesTest extends TestCase
{
    private function profile(string $basename): array
    {
        $directory = dirname(__DIR__, 2).'/tools/diagnostics';
        $source = file_get_contents($directory.'/'.$basename);
        $require = "require __DIR__.'/frozen-readonly-profile.php';";
        self::assertSame(1, substr_count($source, $require));
        self::assertSame(1, substr_count($source, 'runFrozenReadOnlyProfile('));
        $source = str_replace([$require, 'runFrozenReadOnlyProfile('], ['', '$capture('], $source);
        $result = null;
        $capture = static function (string $file, string $sha, array $replacements, array $context = []) use ($directory, &$result): void {
            if (!in_array($file, ['m53-postconditions.php', 'm52-old-consumers.php', 'm52-sentence-builder-audit.php'], true)) {
                throw new RuntimeException('Synthetic probe cannot load a diagnostic outside its finite primitive set.');
            }
            $bytes = file_get_contents($directory.'/'.$file);
            self::assertSame($sha, hash('sha256', $bytes));
            foreach ($replacements as [$old, $new, $count]) {
                self::assertSame($count, substr_count($bytes, $old), 'Exact literal boundary: '.substr($old, 0, 120));
                $bytes = str_replace($old, $new, $bytes);
            }
            // Compile only. Never eval the expanded primitive or invoke its CLI/SQL/HTTP procedure.
            self::assertNotEmpty(token_get_all($bytes, TOKEN_PARSE));
            $result = ['primitive'=>$file, 'source'=>$bytes, 'replacements'=>$replacements, 'context'=>$context];
        };
        $argv = ['pure-profile-probe'];
        eval(substr($source, strlen("<?php\n")));
        self::assertIsArray($result);
        return $result;
    }

    public function test_all_four_m69_wrappers_expand_only_exact_hash_bound_frozen_literals_and_parse_without_execution(): void
    {
        foreach (['m69-postconditions.php', 'm69-old-consumers.php', 'm69-before-registry.php', 'm69-sentence-builder-audit.php'] as $basename) {
            $profile = $this->profile($basename);
            self::assertStringNotContainsString('M69PassiveConstructionsPackage', $profile['source']);
            self::assertStringNotContainsString('M68ComparisonBasics', $profile['source']);
            self::assertStringNotContainsString('m68-comparison-basics.v1.json', $profile['source']);
        }
        $post = $this->profile('m69-postconditions.php')['source'];
        self::assertStringNotContainsString('QUESTION_PLAN_SHA', $post);
        self::assertStringNotContainsString('m69QuestionState', $post);
        self::assertStringContainsString('$newRows = []; $approved = [];', $post);
        self::assertStringContainsString("'new_subtitles' => count(\$subtitleFacts)", $post);
        $consumer = $this->profile('m69-old-consumers.php')['source'];
        self::assertStringContainsString("\$workingBeforeName = 'before-v2.json';", $consumer);
        self::assertStringContainsString('all_old_rows_and_timestamps_exact', $consumer);
        $registry = $this->profile('m69-sentence-builder-audit.php')['source'];
        self::assertStringContainsString("unset(\$row['http_sha256'], \$current['http_sha256']);", $registry);
        self::assertStringContainsString("'raw_equal'=>", $registry);
    }

    private function insertionRegion(): string
    {
        foreach ($this->profile('m69-postconditions.php')['replacements'] as [, $new]) {
            if (str_contains($new, '$expectedSubtitleOwners = [')) { return $new; }
        }
        throw new RuntimeException('Missing explicit new-subtitle region.');
    }

    private function fixtures(): array
    {
        $patch = ['targets'=>[]]; $before = ['pages'=>[]]; $sourceBefore = ['targets'=>[]]; $preview = ['inserts'=>[]]; $rows = [];
        foreach (Package::OWNERS as $i => $owner) {
            $practice = ['type'=>'practice-set', 'column'=>'left', 'level'=>Package::LEVELS[$i],
                'uuid_key'=>'synthetic:m69-practice:'.$i, 'body'=>'{"synthetic":true}'];
            $target = ['identity'=>$owner, 'page_binding'=>['id'=>Package::PAGE_IDS[$i], 'page_category_id'=>52],
                'after'=>['page'=>['subtitle_html'=>'<p>Synthetic new subtitle.</p>', 'blocks'=>[$practice]]]];
            $patch['targets'][] = $target; $sourceBefore['targets'][] = ['before'=>['page'=>[]]];
            $before['pages'][$owner] = ['page'=>['text'=>$i === 2 ? null : 'Synthetic old subtitle.'], 'blocks'=>[]];
            $configs = [[$practice, 1]];
            if ($i === 2) {
                $configs[] = [['type'=>'subtitle', 'column'=>'header', 'heading'=>null, 'css_class'=>null,
                    'level'=>null, 'uuid_key'=>'subtitle', 'body'=>$target['after']['page']['subtitle_html']], 0];
            }
            foreach ($configs as [$source, $position]) {
                $uuid = M26DetailPackage::uuid($owner, $source, $position);
                $fields = ['uuid'=>$uuid, 'page_id'=>Package::PAGE_IDS[$i], 'page_category_id'=>52, 'locale'=>'uk',
                    'type'=>$source['type'], 'column'=>$source['column'], 'heading'=>$source['heading'] ?? null,
                    'css_class'=>$source['css_class'] ?? null, 'sort_order'=>$position, 'body'=>$source['body'],
                    'level'=>$source['level'] ?? null, 'seeder'=>$owner];
                $preview['inserts'][] = ['table'=>'text_blocks', 'seeder'=>$owner, 'fields'=>$fields, 'sha256'=>M26DetailPackage::digest($fields)];
                $rows[$uuid] = $fields + ['id'=>700000 + count($rows), 'created_at'=>'2000-01-01 00:00:00', 'updated_at'=>'2000-01-01 00:00:00'];
            }
        }
        return compact('patch', 'before', 'sourceBefore', 'preview', 'rows');
    }

    private function checkInsertRegion(array $fixtures): array
    {
        extract($fixtures);
        $digest = static fn (array $value): string => M26DetailPackage::digest($value);
        $same = static function (array $actual, array $expected, string $message): void {
            ksort($actual); ksort($expected);
            if ($actual !== $expected) { throw new RuntimeException($message); }
        };
        $pageBefore = [1=>[], 2=>[], 3=>[]];
        $db = new class($rows) {
            public function __construct(private array $rows) {}
            public function table(string $name): object
            {
                if ($name !== 'text_blocks') { throw new RuntimeException('Synthetic read attempted a different table.'); }
                return new class($this->rows) {
                    private string $uuid;
                    public function __construct(private array $rows) {}
                    public function where(string $field, string $value): self
                    {
                        if ($field !== 'uuid') { throw new RuntimeException('Synthetic read attempted a different key.'); }
                        $this->uuid = $value; return $this;
                    }
                    public function sole(): object
                    {
                        if (!isset($this->rows[$this->uuid])) { throw new RuntimeException('Synthetic row missing.'); }
                        return (object) $this->rows[$this->uuid];
                    }
                };
            }
        };
        eval($this->insertionRegion());
        return ['new_practices'=>$practiceFacts, 'new_subtitles'=>$subtitleFacts, 'new_rows'=>$newTextBlocks];
    }

    public function test_postconditions_partition_three_practices_and_one_absent_page275_subtitle_without_exempting_old_rows(): void
    {
        $result = $this->checkInsertRegion($this->fixtures());
        self::assertCount(3, $result['new_practices']);
        self::assertSame(Package::OWNERS, array_column($result['new_practices'], 'owner'));
        self::assertCount(1, $result['new_subtitles']);
        self::assertSame(Package::OWNERS[2], $result['new_subtitles'][0]['owner']);
        self::assertCount(4, $result['new_rows']);
    }

    public function test_postconditions_refuse_missing_duplicate_foreign_or_stale_new_subtitle_metadata_and_timestamps(): void
    {
        $changes = [
            static function (&$f): void { array_pop($f['preview']['inserts']); },
            static function (&$f): void {
                $insert = &$f['preview']['inserts'][3]; $insert['fields']['page_id'] = 279;
                $insert['sha256'] = M26DetailPackage::digest($insert['fields']);
            },
            static function (&$f): void {
                $insert = &$f['preview']['inserts'][3]; $insert['fields']['sort_order'] = 1;
                $insert['sha256'] = M26DetailPackage::digest($insert['fields']);
            },
            static function (&$f): void { $f['before']['pages'][Package::OWNERS[2]]['page']['text'] = 'Already present.'; },
            static function (&$f): void {
                $f['before']['pages'][Package::OWNERS[2]]['blocks'][] = ['locale'=>'uk', 'type'=>'subtitle'];
            },
            static function (&$f): void { $f['preview']['inserts'][3] = $f['preview']['inserts'][2]; },
            static function (&$f): void {
                $uuid = $f['preview']['inserts'][3]['fields']['uuid'];
                $f['rows'][$uuid]['updated_at'] = '2000-01-02 00:00:00';
            },
        ];
        foreach ($changes as $change) {
            $fixtures = $this->fixtures(); $change($fixtures); $refused = false;
            try { $this->checkInsertRegion($fixtures); } catch (RuntimeException) { $refused = true; }
            self::assertTrue($refused);
        }
    }
}
