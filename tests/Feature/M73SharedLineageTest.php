<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/** Pure current M73 public-source lineage; no Laravel boot, working environment, SQL, network or Git calls. */
class M73SharedLineageTest extends TestCase
{
    private const BASE = 'cb55b329827e52a56cb6d41edb21e70768cf86f4';
    private const FIXTURE_SHA = 'a63f7829d4d0009b8ce615af46ced46a2065659781df8834abbcb7177917bf0a';
    private const VISUAL_ROOTS = ['resources/views','resources/css','resources/js','public/js'];

    private static function root(): string
    {
        return str_replace('\\','/',dirname(__DIR__,2));
    }

    private static function fixture(): array
    {
        $path=self::root().'/tests/Fixtures/m73-owned-source-hunks.json';
        self::assertFalse(is_link($path)); self::assertSame(self::FIXTURE_SHA,hash_file('sha256',$path));
        $fixture=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
        self::assertSame(1,$fixture['schema_version']); self::assertSame(self::BASE,$fixture['base_sha']);
        return $fixture;
    }

    private static function read(string $relative): string
    {
        self::assertMatchesRegularExpression('~^(?:app|docs/content|database/(?:content-patches|seeders)|resources|public/js)/[A-Za-z0-9_./-]+$~D',$relative);
        self::assertStringNotContainsString('..',$relative);
        $path=self::root().'/'.$relative; self::assertFileExists($path); self::assertFalse(is_link($path));
        return file_get_contents($path);
    }

    private static function lf(string $raw): string
    {
        return str_replace("\r\n","\n",$raw);
    }

    public function test_reversing_only_twelve_m73_hunks_recovers_all_eight_accepted_m72_sources_and_preserves_null_guards(): void
    {
        $fixture=self::fixture(); self::assertCount(8,$fixture['files']);
        self::assertSame([
            'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
            'app/Support/TheoryContentRenderer.php','app/Support/PracticeCompletionLinkedPractice.php',
            'app/Services/Theory/TextBlockToQuestionsMatcherService.php','app/Services/TheoryCourseManifestService.php',
            'app/Http/Controllers/TestJsV2Controller.php',
        ],array_column($fixture['files'],'path'));
        $paths=[]; $hunks=$addedLines=0;
        foreach($fixture['files'] as $file) {
            $paths[]=$file['path']; $raw=self::read($file['path']); $copy=self::lf($raw);
            self::assertSame($file['after_lf_sha256'],hash('sha256',$copy),$file['path'].' complete reviewed current M73 code');
            self::assertNotEmpty($file['hunks']);
            foreach($file['hunks'] as $hunk) {
                ++$hunks;
                self::assertSame(1,substr_count($copy,$hunk['after']),$file['path'].' exact unique owned hunk');
                self::assertStringContainsString('M72',$hunk['before'],'Every accepted M72 dispatch stays in place.');
                self::assertStringNotContainsString('M73',$hunk['before']); self::assertStringContainsString('M73',$hunk['after']);
                self::assertStringStartsWith($hunk['before'],$hunk['after'],'M73 appends callbacks, never replaces old guards or bindings.');
                $tail=substr($hunk['after'],strlen($hunk['before']));
                $delta=substr_count($hunk['after'],"\n")-substr_count($hunk['before'],"\n");
                self::assertContains($delta,[1,2]); $addedLines+=$delta;
                if($file['path']==='app/Support/TheoryLegacyAdapter.php') {
                    self::assertSame(implode("\n",[
                        '                $correction = M73GenericIrregularCollective::correction($item, $design);',
                        '                if ($correction !== null) { $point = array_replace($point, $correction); }','',
                    ]),$tail,'The only two-line addition is the finite M73 callback plus its unchanged NULL guard.');
                } else {
                    foreach(array_filter(explode("\n",$tail),static fn($line)=>$line!=='') as $line) {
                        self::assertStringContainsString('M73',$line,'Only explicit finite M73 dispatch lines are added.');
                    }
                }
                $copy=str_replace($hunk['after'],$hunk['before'],$copy);
            }
            self::assertSame($file['base_lf_sha256'],hash('sha256',$copy),$file['path'].' full accepted M72 base after only M73 reversal');
            self::assertSame($raw,self::read($file['path']),'Read-only evidence does not substitute or rewrite source.');
        }
        self::assertCount(8,array_unique($paths)); self::assertSame(12,$hunks);
        self::assertSame(13,$addedLines); self::assertSame(13,$fixture['owned_added_lines']);
        $controller=self::read('app/Http/Controllers/TestJsV2Controller.php');
        self::assertSame($fixture['controller_raw_sha256'],hash('sha256',$controller));
        self::assertGreaterThan(0,substr_count($controller,"\r\n"));
        self::assertSame(substr_count($controller,"\n"),substr_count($controller,"\r\n"),'Keep exact controller CRLF, not only normalized source.');
        self::assertSame(2,substr_count($controller,'if ($block !== null) { $block = \\App\\Support\\M73GenericIrregularCollective::preserveExcludedTextBlock($block, \'standalone_test\'); }'));
        self::assertSame(2,substr_count($controller,'if ($textBlock) { $textBlock = \\App\\Support\\M73GenericIrregularCollective::preserveExcludedTextBlock($textBlock, \'standalone_test\'); }'));
        self::assertSame(1,substr_count(self::read('app/Support/TheoryContentRenderer.php'),'$bindings = M73BuilderSelectionPresentation::bindings($bindings);'));
        self::assertSame(1,substr_count(self::read('app/Support/PracticeCompletionLinkedPractice.php'),'$block = M73BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory);'));
        self::assertSame(1,substr_count(self::read('app/Services/Theory/TextBlockToQuestionsMatcherService.php'),'$excludeQuestionIds = \\App\\Support\\M73BuilderSelectionPresentation::exclusions($block, $linkedPractice, $excludeQuestionIds);'));
    }

    public function test_all_four_shared_primitives_remain_accepted_m72_code_without_m73_modifications(): void
    {
        $fixture=self::fixture(); self::assertCount(4,$fixture['immutable_primitives']);
        self::assertSame([
            'app/Support/TheoryNativeContentRelease.php','app/Services/TheoryNativeContentPatch.php',
            'app/Services/TheoryLocalReleaseGuard.php','app/Support/TheoryBuilderSelectionPresentation.php',
        ],array_column($fixture['immutable_primitives'],'path'));
        foreach($fixture['immutable_primitives'] as $file) {
            $raw=self::read($file['path']);
            self::assertSame($file['base_lf_sha256'],hash('sha256',self::lf($raw)),$file['path'].' independently accepted unchanged generic primitive');
            self::assertSame($raw,self::read($file['path']));
        }
    }

    public function test_all_fifteen_m72_sources_including_active_bank_revision_and_definitions_keep_exact_before_bytes(): void
    {
        $fixture=self::fixture(); self::assertCount(15,$fixture['protected_m72_sources']);
        $paths=array_column($fixture['protected_m72_sources'],'path'); self::assertCount(15,array_unique($paths));
        $manifest=json_decode(self::read('docs/content/m72-source-checksums.v1.json'),true,flags:JSON_THROW_ON_ERROR);
        $active=array_column($manifest['active_definitions'],'path'); self::assertCount(3,$active);
        $extra=['docs/content/m72-partitives-negatives-geography-bank-audit.v1.0.1.json','docs/content/m72-bank-selection-revision.v1.0.1.json'];
        self::assertSame([...array_column($manifest['artifacts'],'path'),'docs/content/m72-source-checksums.v1.json',...$active,...$extra],$paths);
        foreach($fixture['protected_m72_sources'] as $file) {
            self::assertStringNotContainsString('m73',$file['path']);
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D',$file['base_blob_sha256']); $raw=self::read($file['path']);
            if(array_key_exists('served_before_sha256',$file)) {
                self::assertContains($file['path'],$active,'Served-byte baseline is only for the three continuing active definitions.');
                self::assertArrayHasKey('served_before_bytes',$file);
                self::assertSame($file['served_before_bytes'],strlen($raw));
                self::assertSame($file['served_before_sha256'],hash('sha256',$raw),$file['path'].' exact served BEFORE bytes');
            } else {
                self::assertArrayNotHasKey('served_before_bytes',$file); self::assertSame($file['bytes'],strlen($raw));
                self::assertSame($file['base_blob_sha256'],hash('sha256',$raw),$file['path'].' accepted raw bytes, no EOL normalization');
            }
            self::assertSame($raw,self::read($file['path']));
        }
        $revision=json_decode(self::read($extra[1]),true,flags:JSON_THROW_ON_ERROR);
        self::assertSame('frozen',$revision['status']); self::assertSame('1.0.1',$revision['version']);
        self::assertSame('a7b32c1f05eb75c760073fcc53a5e0e752b045d788c0de46e116ad392536a4a2',$revision['content_checksums_sha256']);
        self::assertSame(['path'=>$extra[0],'sha256'=>'803855b45aa9f01d4ae7a9c6e01b8fa108f975104624ff3825bea21015999529','bytes'=>99977],$revision['active_audit']);
        self::assertSame(32781,$revision['selection']['removed_id']); self::assertSame(32788,$revision['selection']['existing_replacement_id']);
        self::assertNull($revision['selection']['technical_filter_before']); self::assertNull($revision['selection']['technical_filter_after']);
        $presentation=self::read('app/Support/M72BuilderSelectionPresentation.php');
        self::assertStringContainsString("public const AUDIT = '".$extra[0]."';",$presentation);
        self::assertStringContainsString("public const AUDIT_SHA = '".$revision['active_audit']['sha256']."';",$presentation);
        $activeAudit=json_decode(self::read($extra[0]),true,flags:JSON_THROW_ON_ERROR);
        self::assertSame([32775,32778,32779,32780,32788],$activeAudit['targets'][1]['expected_selected_ids']);
        self::assertNull($activeAudit['targets'][1]['level']); self::assertSame('None of the water was clean',$activeAudit['targets'][1]['first_five'][4]['expected_word_key']);
        $master=json_decode(self::read('docs/content/m72-partitives-negatives-geography.v1.0.0.json'),true,flags:JSON_THROW_ON_ERROR);
        $tasks=$demos=0;
        foreach($master['lessons'] as $lesson) { foreach($lesson['blocks'] as $block) {
            if($block['type']!=='practice-set') {continue;}
            foreach(['selects','choices','inputs','rephrase'] as $group) { foreach($block['data'][$group]??[] as $index=>$task) {
                if($group==='rephrase'&&$index===0&&!empty($task['example_original'])) {++$demos;} else {++$tasks;}
            }}
        }}
        self::assertSame(39,$tasks); self::assertSame(3,$demos);
    }

    public function test_all_three_hundred_twenty_six_visual_sources_are_unchanged_with_no_new_or_removed_frontend_files(): void
    {
        $fixture=self::fixture(); self::assertCount(326,$fixture['visual_sources']);
        $expected=array_column($fixture['visual_sources'],'path'); $actual=[];
        foreach(self::VISUAL_ROOTS as $relative) {
            $directory=self::root().'/'.$relative; self::assertDirectoryExists($directory); self::assertFalse(is_link($directory));
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory,RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                self::assertFalse($file->isLink()); if(!$file->isFile()) {continue;}
                $path=str_replace('\\','/',$file->getPathname()); $actual[]=substr($path,strlen(self::root())+1);
            }
        }
        sort($actual,SORT_STRING); $sorted=$expected; sort($sorted,SORT_STRING);
        self::assertSame($sorted,$actual,'No package stylesheet, view, typography hook or frontend file is hidden outside the accepted fixture.');
        foreach($fixture['visual_sources'] as $file) {
            self::assertTrue(array_any(self::VISUAL_ROOTS,static fn($prefix)=>str_starts_with($file['path'],$prefix.'/')));
            self::assertSame($file['base_lf_sha256'],hash('sha256',self::lf(self::read($file['path']))),$file['path'].' accepted visual source');
        }
    }
}
