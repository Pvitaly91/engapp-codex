<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/** Independent raw-source lineage only; never loads Laravel/.env, SQL, HTTP or Git. */
class M82OwnedSharedSourceDeltaTest extends TestCase
{
    private const BASE='b8fecb29492038dc01d374955b61c500c718fa4c';
    private const SOURCE_BEFORE='567f21ca386e0fc36c531ff2abb11f1cd244651de594f3872d39d02605c7401d';
    private const DB_BEFORE='94312691251f96433ee72c3fe544db71767c795495874af3ba1c80c2cbb49c62';
    private const FIXTURE_SHA='246203947784a04b2ebdcb740e4cef6661ccce8c7ea0aae019224f91502bc582';
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
        $raw=self::read(self::root(),'tests/Fixtures/m82-owned-source-hunks.json');self::assertSame(self::FIXTURE_SHA,hash('sha256',$raw));
        $data=json_decode($raw,true,flags:JSON_THROW_ON_ERROR);
        self::assertSame(1,$data['schema_version']);self::assertSame(self::BASE,$data['base_sha']);
        self::assertSame(self::SOURCE_BEFORE,$data['independent_source_before_sha256']);self::assertSame(self::DB_BEFORE,$data['independent_db_before_sha256']);
        self::assertSame(self::DISPATCH,array_column($data['files'],'path'));
        return $data;
    }
    private static function contexts(array $ledger):array
    {
        $roots=[self::root()];$served=getenv('GRAMLYZE_M82_SERVED_ROOT');
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
                $pin=$file['profiles'][$context['profile']];
                $raw=self::read($context['root'],$file['path']);$lf=self::lf($raw);
                self::assertSame($pin['after_raw_sha256'],hash('sha256',$raw));self::assertSame($pin['after_lf_sha256'],hash('sha256',$lf));
                self::assertSame($pin['after_lf_line_count'],substr_count($raw,"\n"));self::assertSame($pin['after_crlf_line_count'],substr_count($raw,"\r\n"));
                self::assertSame('finite-package-registration',$file['kind']);
                foreach($file['hunks'] as $i=>$hunk){
                    ++$count;self::assertSame(1,$hunk['added_lines']);self::assertSame(0,$hunk['removed_lines']);
                    self::assertStringContainsString('M81',$hunk['before']);self::assertStringNotContainsString('M82',$hunk['before']);
                    self::assertStringStartsWith($hunk['before'],$hunk['after']);$added=substr($hunk['after'],strlen($hunk['before']));
                    self::assertSame(1,substr_count($added,"\n"));self::assertStringContainsString('M82',$added);
                    self::assertStringNotContainsString('M81',$added);self::assertStringNotContainsString('m81',$added);
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
                self::assertSame('independent-M82-source-BEFORE',$pin['before_pin_origin']);self::assertSame('independent-finite-twelve-hunk-expectation',$pin['after_pin_origin']);
                if($context['profile']==='worktree'||$file['path']!==self::DISPATCH[7]){self::assertSame($file['accepted_m81_lf_sha256'],hash('sha256',$lf));}
                else{self::assertNotSame($file['accepted_m81_lf_sha256'],hash('sha256',$lf),'Foreign served controller remains, never replaced by WT.');}
            }
            self::assertSame(12,$count);self::assertSame($count,$ledger['owned_added_registration_lines']);
        }
    }

    public function test_theory_caller_and_old_retained_practice_guards_remain_unchanged_nonvisual():void
    {
        $ledger=self::fixture();foreach(self::contexts($ledger) as $context){$read=static fn($path)=>self::read($context['root'],$path);
            self::assertSame(1,substr_count($read(self::DISPATCH[0]),"[M82CommonMistakesLevelRevision::class, 'm42StyleContext']"));
            self::assertStringContainsString('if ($requiredContext !== null && !($context[$requiredContext] ?? false)) { continue; }',$read(self::DISPATCH[0]));
            foreach(['M76PronounRelations','M77OnePrepositionsPhrasal','M78PhrasalQuestionBasics','M79QuestionTypesCompletion','M80NegativesTransformations','M81TransformationsCommonMistakes','M82CommonMistakesLevelRevision'] as $class){
                self::assertSame(2,substr_count($read(self::DISPATCH[7]),'if ($block !== null) { $block = \\App\\Support\\'.$class.'::preserveExcludedTextBlock($block, \'standalone_test\'); }'));
                self::assertSame(2,substr_count($read(self::DISPATCH[7]),'if ($textBlock) { $textBlock = \\App\\Support\\'.$class.'::preserveExcludedTextBlock($textBlock, \'standalone_test\'); }'));
            }
            self::assertSame(1,substr_count($read(self::DISPATCH[6]),'$page = \\App\\Support\\M82CommonMistakesLevelRevision::preserveCoursePage($page);'));
            foreach(['pointPanels','validDesign'] as $method){
                self::assertSame(1,substr_count($read(self::DISPATCH[1]),"if ((\$design['package'] ?? null) === 'm82-native-v1') { return M82CommonMistakesLevelRevision::".$method.'($design); }'));
                self::assertSame(0,substr_count($read(self::DISPATCH[1]),"if ((\$design['package'] ?? null) === 'm81-native-v1') { return M82CommonMistakesLevelRevision::".$method.'($design); }'));
            }
            self::assertSame(1,substr_count($read(self::DISPATCH[3]),'$bindings = M82BuilderSelectionPresentation::bindings($bindings);'));
            self::assertStringContainsString("if ((\$bindings['theoryCanonical'] ?? false) !== true)",$read(self::DISPATCH[3]));
            self::assertSame(1,substr_count($read(self::DISPATCH[4]),'$block = M82BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory);'));
            self::assertSame(1,substr_count($read(self::DISPATCH[5]),'$excludeQuestionIds = \\App\\Support\\M82BuilderSelectionPresentation::exclusions($block, $linkedPractice, $excludeQuestionIds);'));
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

    public function test_all_historical_sources_and_919_test_paths_keep_individual_raw_profiles_and_absences():void
    {
        $ledger=self::fixture();$manifest=self::manifest($ledger,'immutable');self::assertCount(2902,$manifest['paths']);
        self::assertSame(1915,$ledger['original_group_counts']['frozen']);self::assertSame(919,$ledger['original_group_counts']['tests']);
        self::assertCount(919,array_filter($manifest['paths'],static fn($p)=>str_starts_with($p,'tests/')));
        self::assertCount(1909,array_filter($manifest['paths'],static fn($p)=>str_starts_with($p,'docs/content/')||str_starts_with($p,'database/content-patches/')||str_starts_with($p,'database/seeders/Page_V3/')));
        self::assertCount(6,$ledger['approved_definition_paths']);self::assertSame([],array_values(array_intersect($ledger['approved_definition_paths'],$manifest['paths'])));
        self::assertContains('app/Support/TheoryNativeContentRelease.php',$manifest['paths']);
        foreach(['app/Support/ComposeTokenCase.php','app/Support/TheoryBuilderSelectionPresentation.php',
            'tests/Feature/M79OwnedSharedSourceDeltaTest.php','tests/Fixtures/m79-owned-source-hunks.json',
            'tools/diagnostics/m79-postconditions.php','routes/api.php','routes/web.php',
            'app/Support/M80NegativesTransformations.php','app/Support/M80BuilderSelectionPresentation.php',
            'app/Services/M80ContentPatch.php','app/Services/M80LocalTargetGuard.php','app/Console/Commands/ContentPatchM80.php',
            'tests/Feature/M80OwnedSharedSourceDeltaTest.php','tests/Fixtures/m80-owned-source-hunks.json',
            'tools/diagnostics/m80-postconditions.php','docs/content/m80-negatives-transformations.v1.json',
            'docs/content/theory-sentence-builders/m80-negatives-transformations.v1.json',
            'app/Support/M81TransformationsCommonMistakes.php','app/Support/M81BuilderSelectionPresentation.php',
            'app/Services/M81ContentPatch.php','app/Services/M81LocalTargetGuard.php','app/Console/Commands/ContentPatchM81.php',
            'tests/Feature/M81OwnedSharedSourceDeltaTest.php','tests/Fixtures/m81-owned-source-hunks.json',
            'tools/diagnostics/m81-postconditions.php','docs/content/m81-transformations-common-mistakes.v1.json',
            'docs/content/theory-sentence-builders/m81-transformations-common-mistakes.v1.json'] as $path){self::assertContains($path,$manifest['paths']);}
        foreach(self::contexts($ledger) as $context){self::assertManifest($manifest,$context);}
    }


    public function test_a1_a2_course_source_and_caller_boundaries_retain_all_32_lessons_and_final_drills():void
    {
        $ledger=self::fixture();$manifest=self::manifest($ledger,'course');self::assertCount(101,$manifest['paths']);
        self::assertSame('baf41c05a076d2c8df6c3a6132817344e61a2b8a374d456d9108f534090428c7',$manifest['independent_course_source_before_sha256']);
        foreach(self::contexts($ledger) as $context){
            self::assertManifest($manifest,$context);
            foreach(['a1','a2'] as $level){
                $raw=self::read($context['root'],'database/seeders/V3/Polyglot/Course/polyglot-english-'.$level.'.json');
                $course=json_decode($raw,true,flags:JSON_THROW_ON_ERROR);self::assertSame(16,$course['total_planned_lessons']);
                self::assertSame('polyglot-english-'.$level,$course['course_slug']);self::assertCount(16,$course['lessons']);
                self::assertSame(range(1,16),array_column($course['lessons'],'lesson_order'));
                $last=$course['lessons'][15];self::assertSame('polyglot-final-drill-'.$level,$last['slug']);
                self::assertSame('mixed-revision',$last['theory_category_slug']);self::assertSame($level.'-mixed-revision',$last['theory_page_slug']);
                self::assertNull($last['next_lesson_slug']);self::assertSame(strtoupper($level),$last['level']);
                $class=$level==='a1'?'PolyglotFinalDrillLessonSeeder':'PolyglotFinalDrillA2LessonSeeder';
                $definition=json_decode(self::read($context['root'],'database/seeders/V3/Polyglot/'.$class.'/definition.json'),true,flags:JSON_THROW_ON_ERROR);
                self::assertCount(24,$definition['questions']);self::assertCount(24,$definition['saved_test']['question_uuids']);
                $filters=$definition['saved_test']['filters'];self::assertSame(16,$filters['lesson_order']);
                self::assertSame('polyglot-english-'.$level,$filters['course_slug']);
                self::assertSame(['rolling_window'=>100,'min_rating'=>4.5],$filters['completion']);
                self::assertSame($level.'-mixed-revision',$filters['prompt_generator']['theory_page']['slug']);
                self::assertSame('mixed-revision',$filters['prompt_generator']['theory_page']['category_slug_path']);
            }
            $controller=self::read($context['root'],'app/Http/Controllers/PolyglotCourseController.php');
            self::assertStringContainsString("'course' => \$courseStatus['course']",$controller);
            self::assertStringContainsString("'manifest' => \$manifest",$controller);
            $progress=self::read($context['root'],'app/Services/PolyglotProgressService.php');
            self::assertStringContainsString('public const REQUIRED_ANSWERS = 100;',$progress);
            self::assertStringContainsString('public const REQUIRED_AVERAGE = 4.5;',$progress);
            self::assertStringNotContainsString('M82',$progress);
        }
    }

    public function test_e_f_navigation_adds_only_eight_approved_data_urls_without_native_validator_changes():void
    {
        $ledger=self::fixture();
        foreach(self::contexts($ledger) as $context){
            $beforeRaw=self::read($context['root'],'docs/content/m82-before-source.v1.json');
            self::assertSame('2e792f79e2419cf5993b452b403bcaa8c452f4bef39cf8e98960022f49f95b1f',hash('sha256',$beforeRaw));
            $before=json_decode($beforeRaw,true,flags:JSON_THROW_ON_ERROR);
            $master=json_decode(self::read($context['root'],'docs/content/m82-common-mistakes-level-revision.v1.0.2.json'),true,flags:JSON_THROW_ON_ERROR);
            self::assertSame('1.0.2',$master['version']);
            $reviewRaw=self::read($context['root'],'docs/content/m82-navigation-review.v1.json');
            self::assertSame('0e45611733b84425689238b5068cb8d359c75b05b5ffa7295cb5fbf921aff63d',hash('sha256',$reviewRaw));
            $review=json_decode($reviewRaw,true,flags:JSON_THROW_ON_ERROR);self::assertCount(8,$review['targets']);
            self::assertSame('23b89050f061faddd80debce2fefc8dd356a0b49dffa44f79b2200cb5cbbef11',
                hash('sha256',self::read($context['root'],'app/Support/TheoryNativeContentRelease.php')));
            $expectedUrls=[
                ['/theory/basic-grammar/verb-to-be/verb-to-be-present','/theory/basic-grammar/verb-to-be/there-is-there-are',
                    '/theory/some-any/theory-some-any-things','/theory/prykmetniky-ta-pryslinknyky/comparative-vs-superlative'],
                ['/theory/tenses/present-perfect/present-perfect-forms','/theory/conditionals/first-conditional',
                    '/theory/reported-speech/reported-statements','/theory/conditionals/second-conditional'],
            ];
            foreach([4,5] as $offset=>$i){
                $owner=$before['targets'][$i]['identity'];self::assertSame($owner,$master['lessons'][$i]['identity']);
                $old=$before['targets'][$i]['before']['page']['blocks'][3];$new=$master['lessons'][$i]['blocks'][3];
                self::assertSame('navigation-chips',$old['type']);self::assertSame($old['type'],$new['type']);
                self::assertSame('footer',$old['column']);self::assertSame($old['column'],$new['column']);
                $expected=json_decode($old['body'],true,flags:JSON_THROW_ON_ERROR)['items'];self::assertCount(5,$expected);
                foreach(range(0,3) as $n){
                    $proof=$review['targets'][$offset*4+$n];self::assertSame($owner,$proof['owner']);
                    self::assertSame(3,$proof['source_index']);self::assertSame($n,$proof['item_index']);
                    self::assertSame($expected[$n]['label'],$proof['label']);self::assertFalse($expected[$n]['current']);self::assertFalse($proof['current']);
                    self::assertArrayNotHasKey('url',$expected[$n]);self::assertSame($expectedUrls[$offset][$n],$proof['url']);
                    self::assertSame(200,$proof['http_status']);$expected[$n]['url']=$expectedUrls[$offset][$n];
                }
                self::assertTrue($expected[4]['current']);self::assertArrayNotHasKey('url',$expected[4]);
                self::assertSame($expected,$new['data']['items'],'Only the explicitly approved eight data URLs are added; all labels, order and current flags stay exact.');
            }
            foreach(range(0,3) as $i){
                $old=json_decode($before['targets'][$i]['before']['page']['blocks'][5]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];
                self::assertSame($old,$master['lessons'][$i]['blocks'][5]['data']['items'],'A-D navigation data are unrelated and stay exact.');
            }
        }
    }

    public function test_new_public_lineage_fixtures_contain_only_safe_paths_hunks_and_opaque_fingerprints():void
    {
        $ledger=self::fixture();$data=[$ledger,self::manifest($ledger,'visual'),self::manifest($ledger,'immutable'),self::manifest($ledger,'course')];
        $json=json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        self::assertDoesNotMatchRegularExpression('~(?:[A-Z]:[/\\\\]|storage/app/|password|DB_PASSWORD|APP_KEY|created_at|updated_at|runtime_identity|sql_before|question_snapshot)~',$json);
        foreach($ledger['files'] as $file){self::assertSame(['worktree','served_root'],array_keys($file['profiles']));
            foreach($file['hunks'] as $hunk){self::assertSame(['before','after','added_lines','removed_lines'],array_keys($hunk));}
            foreach($file['profiles'] as $profile){foreach(['before_raw_sha256','before_lf_sha256','after_raw_sha256','after_lf_sha256'] as $key){self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/',$profile[$key]);}}
        }
    }
}
