<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/** Independent raw-source lineage only; never loads Laravel/.env, SQL, HTTP or Git. */
class M80OwnedSharedSourceDeltaTest extends TestCase
{
    private const BASE='b56f29df4b3409edc1ad6cfb34b7fedd901e893d';
    private const SOURCE_BEFORE='30159ae3600430cf23673307e01ac37836e47a0556d4b22652c482156d1cbf2a';
    private const DB_BEFORE='75a9e58003f9364c45e4e3c2696aab7c0b8e5d5073a2bb2acefe54fb198dbefb';
    private const FIXTURE_SHA='f176fc095d5e699b2f96a22ae6103e4d82b1be101112cbaa40d54abae9f2acdc';
    private const DISPATCH=[
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php','app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php','app/Http/Controllers/TestJsV2Controller.php',
    ];
    private static function root():string { return str_replace('\\','/',dirname(__DIR__,2)); }
    private static function lf(string $raw):string { return str_replace("\r\n","\n",$raw); }
    private static function read(string $root,string $path):string
    {
        self::assertMatchesRegularExpression('~^(?:app|docs|database|resources|public/js|tests|tools|routes)/[A-Za-z0-9_./-]+$~D',$path);
        self::assertStringNotContainsString('..',$path);self::assertFileExists($root.'/'.$path);self::assertFalse(is_link($root.'/'.$path));
        return file_get_contents($root.'/'.$path);
    }
    private static function fixture():array
    {
        $raw=self::read(self::root(),'tests/Fixtures/m80-owned-source-hunks.json');self::assertSame(self::FIXTURE_SHA,hash('sha256',$raw));
        $data=json_decode($raw,true,flags:JSON_THROW_ON_ERROR);
        self::assertSame(1,$data['schema_version']);self::assertSame(self::BASE,$data['base_sha']);
        self::assertSame(self::SOURCE_BEFORE,$data['independent_source_before_sha256']);self::assertSame(self::DB_BEFORE,$data['independent_db_before_sha256']);
        self::assertSame(self::DISPATCH,array_column($data['files'],'path'));
        return $data;
    }
    private static function contexts(array $ledger):array
    {
        $roots=[self::root()];$served=getenv('GRAMLYZE_M80_SERVED_ROOT');
        if(is_string($served)&&$served!==''){
            self::assertMatchesRegularExpression('~^(?:[A-Za-z]:[/\\\\]|/)~D',$served);self::assertDirectoryExists($served);self::assertFalse(is_link($served));
            $served=str_replace('\\','/',rtrim($served,'/\\'));if($served!==self::root()){$roots[]=$served;}
        }
        $contexts=[];
        foreach($roots as $root){
            $raw=self::read($root,self::DISPATCH[7]);$profiles=[];
            foreach($ledger['files'][7]['profiles'] as $name=>$pin){if($pin['after_raw_sha256']===hash('sha256',$raw)){$profiles[]=$name;}}
            self::assertCount(1,$profiles,'Every actual foreign-inclusive controller matches exactly one separate ROOT/WT AFTER profile.');
            $contexts[]=['root'=>$root,'profile'=>$profiles[0]];
        }
        return $contexts;
    }
    private static function manifest(array $ledger,string $kind):array
    {
        $pin=$ledger['preservation_manifests'][$kind];$raw=self::read(self::root(),$pin['path']);self::assertSame($pin['sha256'],hash('sha256',$raw));
        $data=json_decode($raw,true,flags:JSON_THROW_ON_ERROR);self::assertSame(self::BASE,$data['base_sha']);self::assertSame($kind,$data['scope']);
        self::assertSame(self::SOURCE_BEFORE,$data['independent_source_before_sha256']);$sorted=$data['paths'];sort($sorted,SORT_STRING);
        self::assertSame($sorted,$data['paths']);self::assertSame(count($sorted),count(array_unique($sorted)));
        return $data;
    }
    private static function assertManifest(array $manifest,array $context):void
    {
        $hash=hash_init('sha256');$present=0;
        foreach($manifest['paths'] as $path){
            $file=$context['root'].'/'.$path;self::assertFalse(is_link($file));
            if(!is_file($file)){self::assertFileDoesNotExist($file);$record=['exists'=>false];}
            else{
                $raw=self::read($context['root'],$path);++$present;
                $record=['exists'=>true,'bytes'=>strlen($raw),'raw_sha256'=>hash('sha256',$raw),'lf_sha256'=>hash('sha256',self::lf($raw)),
                    'crlf'=>substr_count($raw,"\r\n"),'lf'=>substr_count($raw,"\n")];
            }
            hash_update($hash,json_encode([$path,$record],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)."\n");
        }
        $pin=$manifest['profiles'][$context['profile']];self::assertSame($pin['present'],$present);self::assertSame($pin['absent'],count($manifest['paths'])-$present);
        self::assertSame($pin['files_sha256'],hash_final($hash),'Every path, captured absence, raw byte, LF projection and EOL count is preserved in its own ROOT/WT profile.');
    }

    public function test_twelve_owned_additions_reverse_to_the_fresh_independent_raw_and_lf_before():void
    {
        $ledger=self::fixture();
        foreach(self::contexts($ledger) as $context){$count=0;
            foreach($ledger['files'] as $file){
                self::assertSame('finite-package-registration',$file['kind']);$pin=$file['profiles'][$context['profile']];
                $raw=self::read($context['root'],$file['path']);$lf=self::lf($raw);
                self::assertSame($pin['after_raw_sha256'],hash('sha256',$raw));self::assertSame($pin['after_lf_sha256'],hash('sha256',$lf));
                self::assertSame($pin['after_lf_line_count'],substr_count($raw,"\n"));self::assertSame($pin['after_crlf_line_count'],substr_count($raw,"\r\n"));
                foreach($file['hunks'] as $i=>$hunk){
                    ++$count;self::assertSame(1,$hunk['added_lines']);self::assertSame(0,$hunk['removed_lines']);
                    self::assertStringContainsString('M79',$hunk['before']);self::assertStringNotContainsString('M80',$hunk['before']);
                    self::assertStringStartsWith($hunk['before'],$hunk['after']);$added=substr($hunk['after'],strlen($hunk['before']));
                    self::assertSame(1,substr_count($added,"\n"));self::assertStringContainsString('M80',$added);
                    self::assertSame(1,substr_count($lf,$hunk['after']));$lf=str_replace($hunk['after'],$hunk['before'],$lf);
                    $lines=preg_split('/(?<=\n)/',$raw,-1,PREG_SPLIT_NO_EMPTY);$normalized=array_map(self::lf(...),$lines);
                    $wanted=preg_split('/(?<=\n)/',$hunk['after'],-1,PREG_SPLIT_NO_EMPTY);$positions=[];
                    foreach(array_keys($lines) as $n){if(array_slice($normalized,$n,count($wanted))===$wanted){$positions[]=$n;}}
                    self::assertCount(1,$positions);$position=$positions[0]+count($wanted)-1;$eol=$pin['hunk_added_line_eols'][$i];
                    self::assertContains($eol,['LF','CRLF']);$expected=$eol==='CRLF'?str_replace("\n","\r\n",$added):$added;
                    self::assertSame($expected,$lines[$position],'Exact owned line retains its independently forecast predecessor EOL.');
                    array_splice($lines,$position,1);$raw=implode('',$lines);
                }
                self::assertSame($pin['before_raw_sha256'],hash('sha256',$raw),'No raw EOL normalization during reversal.');self::assertSame($pin['before_lf_sha256'],hash('sha256',$lf));
                self::assertSame('independent-M80-source-BEFORE',$pin['before_pin_origin']);self::assertSame('independent-finite-twelve-hunk-expectation',$pin['after_pin_origin']);
                if($context['profile']==='worktree'||$file['path']!==self::DISPATCH[7]){self::assertSame($file['accepted_m79_lf_sha256'],hash('sha256',$lf));}
                else{self::assertNotSame($file['accepted_m79_lf_sha256'],hash('sha256',$lf),'Foreign served controller remains, never replaced by WT.');}
            }
            self::assertSame(12,$count);self::assertSame($count,$ledger['owned_added_registration_lines']);
            self::assertSame(0,$ledger['owned_validator_added_lines']);self::assertSame(0,$ledger['owned_validator_removed_lines']);
        }
    }

    public function test_theory_caller_and_old_retained_practice_guards_remain_unchanged_nonvisual():void
    {
        $ledger=self::fixture();foreach(self::contexts($ledger) as $context){$read=static fn($path)=>self::read($context['root'],$path);
            self::assertSame(1,substr_count($read(self::DISPATCH[0]),"[M80NegativesTransformations::class, 'm42StyleContext']"));
            self::assertStringContainsString('if ($requiredContext !== null && !($context[$requiredContext] ?? false)) { continue; }',$read(self::DISPATCH[0]));
            foreach(['M76PronounRelations','M77OnePrepositionsPhrasal','M78PhrasalQuestionBasics','M79QuestionTypesCompletion','M80NegativesTransformations'] as $class){
                self::assertSame(2,substr_count($read(self::DISPATCH[7]),'if ($block !== null) { $block = \\App\\Support\\'.$class.'::preserveExcludedTextBlock($block, \'standalone_test\'); }'));
                self::assertSame(2,substr_count($read(self::DISPATCH[7]),'if ($textBlock) { $textBlock = \\App\\Support\\'.$class.'::preserveExcludedTextBlock($textBlock, \'standalone_test\'); }'));
            }
            self::assertSame(1,substr_count($read(self::DISPATCH[6]),'$page = \\App\\Support\\M80NegativesTransformations::preserveCoursePage($page);'));
            self::assertSame(1,substr_count($read(self::DISPATCH[3]),'$bindings = M80BuilderSelectionPresentation::bindings($bindings);'));
            self::assertStringContainsString("if ((\$bindings['theoryCanonical'] ?? false) !== true)",$read(self::DISPATCH[3]));
            self::assertSame(1,substr_count($read(self::DISPATCH[4]),'$block = M80BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory);'));
            self::assertSame(1,substr_count($read(self::DISPATCH[5]),'$excludeQuestionIds = \\App\\Support\\M80BuilderSelectionPresentation::exclusions($block, $linkedPractice, $excludeQuestionIds);'));
        }
    }

    public function test_all_326_visual_profiles_and_existing_token_shift_label_binding_are_raw_exact():void
    {
        $ledger=self::fixture();$manifest=self::manifest($ledger,'visual');self::assertCount(326,$manifest['paths']);
        foreach(self::contexts($ledger) as $context){
            $actual=[];foreach(['resources/views','resources/css','resources/js','public/js'] as $directory){
                foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($context['root'].'/'.$directory,RecursiveDirectoryIterator::SKIP_DOTS)) as $file){
                    self::assertFalse($file->isLink());if($file->isFile()){$actual[]=substr(str_replace('\\','/',$file->getPathname()),strlen($context['root'])+1);}
                }
            }sort($actual,SORT_STRING);self::assertSame($manifest['paths'],$actual,'No new visual/component/formatter source.');self::assertManifest($manifest,$context);
            $widget=self::read($context['root'],'resources/views/components/text-block-practice-questions.blade.php');
            self::assertSame(1,substr_count($widget,'&& items.every((item, index) => item.value === tokens[index])'));
            self::assertSame(1,substr_count($widget,'items.push(items.shift());'));
            self::assertSame(1,substr_count($widget,'x-text="Object.prototype.hasOwnProperty.call(currentQuestion?.token_labels ?? {}, token.value) && typeof currentQuestion.token_labels[token.value] === \'string\' ? currentQuestion.token_labels[token.value] : token.value"'));
            self::assertStringContainsString('x-text="token.value"',$widget);
        }
    }

    public function test_all_historical_sources_and_903_test_paths_keep_individual_raw_profiles_and_absences():void
    {
        $ledger=self::fixture();$manifest=self::manifest($ledger,'immutable');self::assertCount(2810,$manifest['paths']);
        self::assertSame(1877,$ledger['original_group_counts']['frozen']);self::assertSame(903,$ledger['original_group_counts']['tests']);
        self::assertCount(903,array_filter($manifest['paths'],static fn($p)=>str_starts_with($p,'tests/')));
        self::assertCount(1871,array_filter($manifest['paths'],static fn($p)=>str_starts_with($p,'docs/content/')||str_starts_with($p,'database/content-patches/')||str_starts_with($p,'database/seeders/Page_V3/')));
        self::assertCount(6,$ledger['approved_definition_paths']);self::assertSame([],array_values(array_intersect($ledger['approved_definition_paths'],$manifest['paths'])));
        foreach(['app/Support/ComposeTokenCase.php','app/Support/TheoryNativeContentRelease.php','app/Support/TheoryBuilderSelectionPresentation.php',
            'tests/Feature/M79OwnedSharedSourceDeltaTest.php','tests/Fixtures/m79-owned-source-hunks.json',
            'tools/diagnostics/m79-postconditions.php','routes/api.php','routes/web.php'] as $path){self::assertContains($path,$manifest['paths']);}
        foreach(self::contexts($ledger) as $context){self::assertManifest($manifest,$context);}
    }

    public function test_new_public_lineage_fixtures_contain_only_safe_paths_hunks_and_opaque_fingerprints():void
    {
        $ledger=self::fixture();$data=[$ledger,self::manifest($ledger,'visual'),self::manifest($ledger,'immutable')];
        $json=json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        self::assertDoesNotMatchRegularExpression('~(?:[A-Z]:[/\\\\]|storage/app/|password|DB_PASSWORD|APP_KEY|created_at|updated_at|runtime_identity|sql_before|question_snapshot)~',$json);
        foreach($ledger['files'] as $file){self::assertSame(['worktree','served_root'],array_keys($file['profiles']));
            foreach($file['hunks'] as $hunk){self::assertSame(['before','after','added_lines','removed_lines'],array_keys($hunk));}
            foreach($file['profiles'] as $profile){foreach(['before_raw_sha256','before_lf_sha256','after_raw_sha256','after_lf_sha256'] as $key){self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/',$profile[$key]);}}
        }
    }
}
