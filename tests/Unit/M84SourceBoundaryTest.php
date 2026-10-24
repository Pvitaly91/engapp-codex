<?php

namespace Tests\Unit;

use App\Support\M84WordOrderHaveGot as Package;
use PHPUnit\Framework\TestCase;

/** Independent raw-source lineage only: no Laravel bootstrap, environment, SQL, HTTP or Git. */
class M84SourceBoundaryTest extends TestCase
{
    private const FIXTURE_SHA='9bf427faa6045c7060e2d369c9caea1c797dcd80f573e8f7e0cd4eafe2cdee84';
    private const SOURCE_BEFORE='9066938a376b3f4eeae6fa65f6fea6afc7a2bdd624a4ed93dc32e37c59b8e47e';
    private const DB_BEFORE='babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916';
    private const DISPATCH=[
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php','app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php','app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php',
    ];
    private const UNCHANGED=[
        'app/Support/TheoryNativeContentRelease.php'=>['23b89050f061faddd80debce2fefc8dd356a0b49dffa44f79b2200cb5cbbef11','23b89050f061faddd80debce2fefc8dd356a0b49dffa44f79b2200cb5cbbef11'],
        'app/Support/TheoryBuilderSelectionPresentation.php'=>['b89e7578f8ea1850b799c76739bd5aac81611315de7fccf86fa62ddb7f51eb4a','b89e7578f8ea1850b799c76739bd5aac81611315de7fccf86fa62ddb7f51eb4a'],
        'app/Services/TheoryNativeContentPatch.php'=>['af31eb1a0cc7375f11ace1432e68792255209b696654a93a789c580d1113ec9f','af31eb1a0cc7375f11ace1432e68792255209b696654a93a789c580d1113ec9f'],
        'resources/views/engram/theory/blocks-v3/practice-set.blade.php'=>['0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186','05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e'],
        'resources/views/components/text-block-practice-questions.blade.php'=>['088fb035c1421c43a9e06ac96e4f0c1328aa6acc499e1dfbdbb0ecf39d3cf92d','773c82eed2e099e7a512a5760a4cb425cc83a72a33e40ea9420db19e663862fe'],
        'public/js/english-answer-variants.js'=>['92f4984a6d6d77fd4774a716e8fef5db79f2df1eab29b54333aed60a47cc2540','7000925d6de41fdb6671457bd8d1d7766b9c5b05d2c3d5fe0f1195f80666c681'],
        'public/data/english-contractions.json'=>['9a5d411c3ad87774f7c3d2e79bcd4ac7aebefad8ae7c1a43efbd327a17ca8baf','2815805630fc1987314c9632a055cf3feced856372f73cb29c2a6ade80cb6491'],
    ];

    private static function root(): string { return str_replace('\\','/',dirname(__DIR__,2)); }
    private static function lf(string $raw): string { return str_replace("\r\n","\n",$raw); }
    private static function read(string $root,string $path): string
    {
        self::assertMatchesRegularExpression('~^(?:app|docs|database|resources|public|tests|tools)/[A-Za-z0-9_./-]+$~D',$path);
        self::assertStringNotContainsString('..',$path); self::assertFileExists($root.'/'.$path); self::assertFalse(is_link($root.'/'.$path));
        return file_get_contents($root.'/'.$path);
    }
    private static function fixture(): array
    {
        $raw=self::read(self::root(),'tests/Fixtures/m84-owned-source-hunks.v1.0.1.json'); self::assertSame(self::FIXTURE_SHA,hash('sha256',$raw));
        $f=json_decode($raw,true,flags:JSON_THROW_ON_ERROR); self::assertSame(1,$f['schema_version']); self::assertSame(Package::BASE_SHA,$f['base_sha']);
        self::assertSame(self::SOURCE_BEFORE,$f['independent_source_before_sha256']); self::assertSame(self::DB_BEFORE,$f['independent_db_before_sha256']);
        self::assertSame(self::DISPATCH,array_column($f['files'],'path')); return $f;
    }
    private static function contexts(array $fixture): array
    {
        $roots=[self::root()]; $served=getenv('GRAMLYZE_M84_SERVED_ROOT');
        if (is_string($served)&&$served!=='') {
            self::assertMatchesRegularExpression('~^(?:[A-Za-z]:[/\\\\]|/)~D',$served); self::assertDirectoryExists($served); self::assertFalse(is_link($served));
            $served=str_replace('\\','/',rtrim($served,'/\\')); if ($served!==self::root()) { $roots[]=$served; }
        }
        $out=[];
        foreach ($roots as $root) {
            $raw=self::read($root,self::DISPATCH[7]); $profiles=[];
            foreach ($fixture['files'][7]['profiles'] as $name=>$p) { if ($p['after_raw_sha256']===hash('sha256',$raw)) { $profiles[]=$name; } }
            self::assertCount(1,$profiles,'Actual foreign-inclusive controller must match exactly one independently reviewed AFTER profile.');
            $out[]=['root'=>$root,'profile'=>$profiles[0]];
        }
        return $out;
    }

    public function test_exact_twelve_nonvisual_registrations_reverse_to_independent_lf_before_on_each_profile(): void
    {
        $f=self::fixture();
        foreach (self::contexts($f) as $context) {
            $count=0;
            foreach ($f['files'] as $file) {
                $pin=$file['profiles'][$context['profile']]; $raw=self::read($context['root'],$file['path']); $lf=self::lf($raw);
                self::assertSame('finite-package-registration',$file['kind']);
                self::assertSame($pin['after_raw_sha256'],hash('sha256',$raw)); self::assertSame($pin['after_lf_sha256'],hash('sha256',$lf));
                self::assertSame($pin['after_lf_line_count'],substr_count($raw,"\n")); self::assertSame($pin['after_crlf_line_count'],substr_count($raw,"\r\n"));
                foreach ($file['hunks'] as $hunk) {
                    self::assertSame([1,0],[$hunk['added_lines'],$hunk['removed_lines']]);
                    self::assertStringContainsString('M83',$hunk['before']); self::assertStringNotContainsString('M84',$hunk['before']);
                    self::assertStringStartsWith($hunk['before'],$hunk['after']); $added=substr($hunk['after'],strlen($hunk['before']));
                    self::assertSame(1,substr_count($added,"\n")); self::assertStringContainsString('M84',$added);
                    self::assertSame(1,substr_count($lf,$hunk['after'])); $lf=str_replace($hunk['after'],$hunk['before'],$lf); ++$count;
                }
                self::assertSame($pin['before_lf_sha256'],hash('sha256',$lf),'No broad rewrite or deletion is hidden behind owned additive lines.');
            }
            self::assertSame(12,$count); self::assertSame($count,$f['owned_added_registration_lines']);
            self::assertSame([0,0],[$f['owned_validator_added_lines'],$f['owned_validator_removed_lines']]);
        }
    }

    public function test_native_validator_builder_formatter_and_grading_engine_keep_their_independent_raw_bytes(): void
    {
        $f=self::fixture();
        foreach (self::contexts($f) as $context) {
            $i=$context['profile']==='served_root'?0:1;
            foreach (self::UNCHANGED as $path=>$sha) { self::assertSame($sha[$i],hash('sha256',self::read($context['root'],$path)),$path); }
            $source=self::read($context['root'],self::DISPATCH[0]);
            self::assertSame(1,substr_count($source,"[M84WordOrderHaveGot::class, 'm42StyleContext']"));
            self::assertStringContainsString('if ($requiredContext !== null && !($context[$requiredContext] ?? false)) { continue; }',$source);
            $renderer=self::read($context['root'],self::DISPATCH[3]);
            self::assertStringContainsString("if ((\$bindings['theoryCanonical'] ?? false) !== true)",$renderer);
            $controller=self::read($context['root'],self::DISPATCH[7]);
            self::assertSame(2,substr_count($controller,'if ($block !== null) { $block = \\App\\Support\\M84WordOrderHaveGot::preserveExcludedTextBlock($block, \'standalone_test\'); }'));
            self::assertSame(2,substr_count($controller,'if ($textBlock) { $textBlock = \\App\\Support\\M84WordOrderHaveGot::preserveExcludedTextBlock($textBlock, \'standalone_test\'); }'));
        }
    }

    public function test_frozen_checksum_manifest_matches_public_sources_and_exact_six_active_definitions(): void
    {
        $raw=self::read(self::root(),Package::CHECKSUMS); self::assertSame(Package::CHECKSUMS_SHA,hash('sha256',$raw));
        $m=json_decode($raw,true,flags:JSON_THROW_ON_ERROR);
        self::assertSame(self::UNCHANGED['app/Support/TheoryNativeContentRelease.php'][0],$m['unchanged_native_validator_sha256']);
        self::assertCount(6,$m['active_definitions']);
        $expected=array_map(static fn ($owner)=>'database/seeders/'.str_replace('\\','/',substr($owner,strlen('Database\\Seeders\\'))).'/definition.json',Package::OWNERS);
        self::assertSame($expected,array_column($m['active_definitions'],'path'));
        foreach (['artifacts','active_definitions'] as $group) {
            self::assertIsArray($m[$group]); self::assertTrue(array_is_list($m[$group])); self::assertNotEmpty($m[$group]);
            self::assertCount(count($m[$group]),array_unique(array_column($m[$group],'path')));
            foreach ($m[$group] as $record) {
                $bytes=self::read(self::root(),$record['path']); self::assertSame($record['sha256'],hash('sha256',$bytes),$record['path']); self::assertSame($record['bytes'],strlen($bytes),$record['path']);
            }
        }
        self::assertNotEmpty($m['author_inputs']);
        foreach ($m['author_inputs'] as $input) { self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D',$input['sha256']); self::assertGreaterThan(0,$input['bytes']); }
        self::assertDoesNotMatchRegularExpression('~DB_PASSWORD|APP_KEY|password|runtime_identity|created_at|updated_at~i',$raw);
    }
}
