<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/** Pure M74 source lineage: no Laravel boot, environment, database, network or Git. */
class M74OwnedSharedSourceDeltaTest extends TestCase
{
    private const BASE = 'fd1ad30e710b3d950e574848c886d86f6045f2e3';
    private const FIXTURE_SHA = '47b922e810e86f63581c019d496495842b3e2bffcbcc5e389a3c95639b88f264';
    private const M73_FIXTURE_SHA = 'a63f7829d4d0009b8ce615af46ced46a2065659781df8834abbcb7177917bf0a';
    private const VISUAL_ROOTS = ['resources/views','resources/css','resources/js','public/js'];
    private const DISPATCH_PATHS = [
        'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php',
    ];

    private static function root(): string
    {
        return str_replace('\\','/',dirname(__DIR__,2));
    }

    private static function fixture(): array
    {
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D',self::FIXTURE_SHA,'M74 owned-source fixture is not frozen yet.');
        $path=self::root().'/tests/Fixtures/m74-owned-source-hunks.json';
        self::assertFileExists($path); self::assertFalse(is_link($path));
        self::assertSame(self::FIXTURE_SHA,hash_file('sha256',$path),'Independent exact fixture bytes, not a runtime-generated baseline.');
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

    private static function plainSubtitleAddition(): string
    {
        return implode("\n",[
            "            if (array_key_exists('preserved_plain_subtitle', \$binding)) {",
            "                \$plain = \$binding['preserved_plain_subtitle'];",
            "                \$metadata = array_values(array_filter(\$master['editorial_review']['post_apply_metadata'] ?? [],",
            "                    static fn (\$record) => (\$record['identity'] ?? null) === \$binding['identity']));",
            "                if ((\$binding['before_subtitle']['mode'] ?? null) !== 'captured-plain' || \$subtitleAbsent",
            "                    || !is_array(\$plain) || array_keys(\$plain) !== ['sha256', 'extracted_title', 'h1']",
            "                    || preg_match('/^[a-f0-9]{64}$/D', \$plain['sha256'] ?? '') !== 1",
            "                    || !hash_equals(\$plain['sha256'], hash('sha256', \$lesson['subtitle_html'] ?? ''))",
            "                    || \$plain['extracted_title'] !== \$binding['rendered_title'] || \$plain['h1'] !== \$binding['rendered_title']",
            "                    || preg_match('/^<p>[^<>]+<\\/p>$/suD', \$lesson['subtitle_html'] ?? '') !== 1",
            "                    || \$metadata !== [['identity' => \$binding['identity']] + \$plain]) {",
            "                    \$this->fail('exact continuing plain subtitle/independent H1 metadata differs.');",
            "                }",
            "                unset(\$subtitles[1]); // Finite plain AFTER only; captured BEFORE still passes its original proof below.",
            "            }",
            '',
        ]);
    }

    public function test_only_twelve_dispatch_hunks_and_one_finite_plain_subtitle_hunk_reverse_to_the_full_accepted_m73_sources(): void
    {
        $fixture=self::fixture();
        self::assertSame([...self::DISPATCH_PATHS,'app/Support/TheoryNativeContentRelease.php'],array_column($fixture['files'],'path'));
        self::assertCount(9,$fixture['files']);
        $dispatchHunks=$dispatchLines=$genericHunks=$genericLines=0;
        foreach($fixture['files'] as $file){
            $raw=self::read($file['path']);$copy=self::lf($raw);
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D',$file['base_blob_sha256']);
            self::assertSame($file['after_lf_sha256'],hash('sha256',$copy),$file['path'].' complete reviewed M74 source');
            self::assertNotEmpty($file['hunks']);
            foreach($file['hunks'] as $hunk){
                self::assertNotSame('',$hunk['before']);self::assertNotSame('',$hunk['after']);
                self::assertSame(1,substr_count($copy,$hunk['after']),$file['path'].' unique exact owned hunk');
                $delta=substr_count($hunk['after'],"\n")-substr_count($hunk['before'],"\n");
                if($file['path']==='app/Support/TheoryNativeContentRelease.php'){
                    ++$genericHunks;$genericLines+=$delta;
                    $added=self::plainSubtitleAddition();
                    self::assertSame(15,substr_count($added,"\n"));
                    self::assertSame(1,substr_count($hunk['after'],$added));
                    self::assertSame($hunk['before'],str_replace($added,'',$hunk['after']),'The generic exception inserts only the exact finite hash/H1/metadata proof; no original guard changes.');
                    self::assertStringNotContainsString('M74',$hunk['after'],'The primitive must not dispatch by package number.');
                }else{
                    ++$dispatchHunks;$dispatchLines+=$delta;
                    self::assertContains($file['path'],self::DISPATCH_PATHS);
                    self::assertStringContainsString('M73',$hunk['before'],'Accepted M73 dispatch remains in place.');
                    self::assertStringNotContainsString('M74',$hunk['before']);
                    self::assertStringContainsString('M74',$hunk['after']);
                    self::assertStringStartsWith($hunk['before'],$hunk['after'],'Only append exact finite M74 callbacks.');
                    self::assertContains($delta,[1,2]);
                    $tail=substr($hunk['after'],strlen($hunk['before']));
                    if($file['path']==='app/Support/TheoryLegacyAdapter.php'){
                        self::assertSame(implode("\n",[
                            '                $correction = M74OtherPronounsBasics::correction($item, $design);',
                            '                if ($correction !== null) { $point = array_replace($point, $correction); }','',
                        ]),$tail,'The sole two-line dispatch retains its object/NULL guard.');
                    }else{
                        foreach(array_filter(explode("\n",$tail),static fn($line)=>$line!=='') as $line){
                            self::assertStringContainsString('M74',$line,'No unrelated line is hidden in a dispatch addition.');
                        }
                    }
                }
                $copy=str_replace($hunk['after'],$hunk['before'],$copy);
            }
            self::assertSame($file['base_lf_sha256'],hash('sha256',$copy),$file['path'].' full accepted fd1ad30 source after only reviewed M74 reversal');
            self::assertSame($raw,self::read($file['path']),'Evidence does not rewrite working bytes.');
        }
        self::assertSame(12,$dispatchHunks);self::assertSame(13,$dispatchLines);
        self::assertSame(1,$genericHunks);self::assertSame(15,$genericLines);
        self::assertSame(28,$fixture['owned_added_lines']);
    }

    public function test_standalone_callbacks_keep_object_guards_and_exact_controller_crlf_while_page_controller_stays_unchanged(): void
    {
        $fixture=self::fixture();$controller=self::read('app/Http/Controllers/TestJsV2Controller.php');
        self::assertSame($fixture['controller_raw_sha256'],hash('sha256',$controller));
        self::assertGreaterThan(0,substr_count($controller,"\r\n"));
        self::assertSame(substr_count($controller,"\n"),substr_count($controller,"\r\n"),'Keep controller CRLF bytes, not only normalized source.');
        foreach(['M73GenericIrregularCollective','M74OtherPronounsBasics'] as $class){
            self::assertSame(2,substr_count($controller,'if ($block !== null) { $block = \\App\\Support\\'.$class.'::preserveExcludedTextBlock($block, \'standalone_test\'); }'));
            self::assertSame(2,substr_count($controller,'if ($textBlock) { $textBlock = \\App\\Support\\'.$class.'::preserveExcludedTextBlock($textBlock, \'standalone_test\'); }'));
        }
        $page=$fixture['protected_page_controller'];
        self::assertSame('app/Http/Controllers/PageController.php',$page['path']);
        self::assertSame($page['base_lf_sha256'],hash('sha256',self::lf(self::read($page['path']))),'No global title extractor or PageController change.');
        self::assertSame(1,substr_count(self::read('app/Support/TheoryContentRenderer.php'),'$bindings = M74BuilderSelectionPresentation::bindings($bindings);'));
        self::assertSame(1,substr_count(self::read('app/Support/PracticeCompletionLinkedPractice.php'),'$block = M74BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory);'));
        self::assertSame(1,substr_count(self::read('app/Services/Theory/TextBlockToQuestionsMatcherService.php'),'$excludeQuestionIds = \\App\\Support\\M74BuilderSelectionPresentation::exclusions($block, $linkedPractice, $excludeQuestionIds);'));
    }

    public function test_other_three_primitives_remain_accepted_and_plain_subtitle_exception_is_not_a_new_normalizer_or_title_heuristic(): void
    {
        $fixture=self::fixture();
        self::assertSame([
            'app/Services/TheoryNativeContentPatch.php',
            'app/Services/TheoryLocalReleaseGuard.php',
            'app/Support/TheoryBuilderSelectionPresentation.php',
        ],array_column($fixture['immutable_primitives'],'path'));
        foreach($fixture['immutable_primitives'] as $file){
            self::assertSame($file['base_lf_sha256'],hash('sha256',self::lf(self::read($file['path']))),$file['path'].' no M74 mutation');
        }
        $release=self::lf(self::read('app/Support/TheoryNativeContentRelease.php'));
        self::assertSame(1,substr_count($release,self::plainSubtitleAddition()));
        self::assertStringContainsString("if (!\$subtitleAbsent && \$phase === 0 && isset(\$binding['before_subtitle'])) {",$release,'Captured BEFORE still passes its original independent proof.');
        self::assertStringNotContainsString('M74',$release);
    }

    public function test_previous_m73_release_and_m72_bank_only_revision_keep_exact_raw_source_bytes(): void
    {
        $fixture=self::fixture();
        $m73Path=self::root().'/tests/Fixtures/m73-owned-source-hunks.json';
        self::assertFalse(is_link($m73Path));self::assertSame(self::M73_FIXTURE_SHA,hash_file('sha256',$m73Path),'Do not rewrite historical M73 lineage evidence.');
        $m73=json_decode(file_get_contents($m73Path),true,flags:JSON_THROW_ON_ERROR);
        $checksums=json_decode(self::read('docs/content/m73-source-checksums.v1.json'),true,flags:JSON_THROW_ON_ERROR);
        $required=array_values(array_unique([
            ...array_column($checksums['artifacts'],'path'),
            'docs/content/m73-source-checksums.v1.json',
            ...array_column($checksums['active_definitions'],'path'),
            'app/Support/M73GenericIrregularCollective.php','app/Support/M73BuilderSelectionPresentation.php',
            'app/Services/M73ContentPatch.php','app/Services/M73LocalTargetGuard.php','app/Console/Commands/ContentPatchM73.php',
            ...array_column($m73['protected_m72_sources'],'path'),
        ]));
        $actual=array_column($fixture['protected_m73_sources'],'path');
        self::assertCount(count($actual),array_unique($actual));
        self::assertSame([],array_values(array_diff($required,$actual)),'Every M73 frozen source/thin profile and previously protected M72 source must remain protected.');
        foreach($fixture['protected_m73_sources'] as $file){
            self::assertStringNotContainsString('m74',strtolower($file['path']));
            $raw=self::read($file['path']);
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D',$file['base_blob_sha256']);
            if(array_key_exists('served_before_sha256',$file)){
                self::assertArrayHasKey('served_before_bytes',$file);
                self::assertSame($file['served_before_sha256'],hash('sha256',$raw),$file['path'].' exact independent served BEFORE');
                self::assertSame($file['served_before_bytes'],strlen($raw));
            }else{
                self::assertSame($file['base_blob_sha256'],hash('sha256',$raw),$file['path'].' accepted raw bytes, not EOL-normalized');
                self::assertSame($file['bytes'],strlen($raw));
            }
        }
        $revision=json_decode(self::read('docs/content/m72-bank-selection-revision.v1.0.1.json'),true,flags:JSON_THROW_ON_ERROR);
        self::assertSame('1.0.1',$revision['version']);self::assertSame('frozen',$revision['status']);
        self::assertSame(32781,$revision['selection']['removed_id']);self::assertSame(32788,$revision['selection']['existing_replacement_id']);
        self::assertNull($revision['selection']['technical_filter_before']);self::assertNull($revision['selection']['technical_filter_after']);
    }

    public function test_all_326_visual_sources_keep_the_same_inventory_and_accepted_lf_hashes(): void
    {
        $fixture=self::fixture();self::assertCount(326,$fixture['visual_sources']);$actual=[];
        foreach(self::VISUAL_ROOTS as $relative){
            $directory=self::root().'/'.$relative;self::assertDirectoryExists($directory);self::assertFalse(is_link($directory));
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory,RecursiveDirectoryIterator::SKIP_DOTS)) as $file){
                self::assertFalse($file->isLink());if(!$file->isFile()){continue;}
                $actual[]=substr(str_replace('\\','/',$file->getPathname()),strlen(self::root())+1);
            }
        }
        $expected=array_column($fixture['visual_sources'],'path');sort($expected,SORT_STRING);sort($actual,SORT_STRING);
        self::assertSame($expected,$actual,'No frontend file, independent M74 view or package stylesheet may be added or removed.');
        foreach($fixture['visual_sources'] as $file){
            self::assertTrue(array_any(self::VISUAL_ROOTS,static fn($prefix)=>str_starts_with($file['path'],$prefix.'/')));
            self::assertSame($file['base_lf_sha256'],hash('sha256',self::lf(self::read($file['path']))),$file['path'].' unchanged visual source');
        }
    }
}
