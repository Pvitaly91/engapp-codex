<?php

namespace Tests\Feature;

use App\Support\M91FutureSimpleCompletionContinuous as Package;
use PHPUnit\Framework\TestCase;

/** Independent raw-source lineage only: no Laravel bootstrap, environment, SQL, HTTP or Git. */
class M91SourceBoundaryTest extends TestCase
{
    private const FIXTURE_SHA='da11ee0775b57ff50f325e811715b612069a1c0e3c2e9083a456928ddde8d57f';
    private const SOURCE_BEFORE='19abedf1075c58db5a08f29864c74c0fb259b0d567b64f327a71d5b1a7cf4c51';
    private const DB_BEFORE='182192d1e92382649dc4179df5ca88f43a858c2f4cd8a0f527d87dc3222ee18f';
    private const WT_ATTRIBUTES_BEFORE_SHA='051ac9a4011fdaaf3457f28de058da28a26dabb97971d31eec7e880119d1c53c';
    private const WT_ATTRIBUTES_BEFORE_BYTES=73095;
    // Only the reviewed 39-path M91 text eol=lf suffix may follow the exact captured WT prefix.
    private const WT_ATTRIBUTES_SUFFIX_SHA='603b8e112522641030c5ea9fea99fe0fe1a6aa273d0c0bedc6f1d5e51cb4a5eb';
    private const WT_ATTRIBUTES_SUFFIX_BYTES=2873;
    private const WT_ATTRIBUTES_AFTER_SHA='5374648c771a5faa410cfab677d682e2d2e4edefe67399d95e6147cd361d5912';
    // Final pins derive from the reviewed M91 freeze-plan-v2 and exact39-path attributes proposal; no private runtime evidence is exported.
    private const PROJECTED_LESSONS=['5a8504e9dbf5a47bcba5f7a13fc92b55c16490d2476ace494af795a56b7ea078','1a106d7d45203d92c08e8a9d8a40ce97b6b50bb78c800403cc1242d6f4f36e63','d446b8ffcfc2678d7baf4b6c4b8894cbb07d6b4a276503ff9d32987c45459f35','fb0b8f36f7a8a95ec59de5feabcdd69f818037763360ac09fc2de90fd254b086','cac8db61b3186eb65bc61362abb6e01783f242e326556eba685f415a1534bb63','cc075c582a0d7b96a6cf862a02c42f040857cc28972ff984d4628b2ff1c63135'];
    private const AUTHORED_LESSONS=['46adc4cf8671d3d3ba52145e0d8aa9398073495b818c626a0c0189ad74aaea27','35a81fb6128c0adeea9d2d89cf1f5d3f54a759c2cc02c6735027afd7f691f838','61aa9875099890fefd32e8a97774294bbfc692499d700c441e849f3d0f001a4c','0ce4d3626ad67137e876f29f5c9d89998735b1dd829ae6545d073a3880c14dcd','c28420c4452a2e165550527db0db5ce1712acc6d4b26e82dc3866e77741ca28f','4be657d05792dd23333937100172d61b4ba42fd81dab4b7bffbb8d7ec69748a8'];
    private const AUTHORED_TASK_MAPS=['6517ccd806e0df0995d73f3272485fa4e920b10d2b5c5cdf60705d153fab9ef6','2fa8a3c8ade5827548b3667411d5f29beb17e4207cc1ee1271d138b5998e662a','b2bb7c84bd17b1ead17cc294b6d293d1973d2ea7b9a0df6637f20fc6bf4e5ab4','6a9892123628a4ceaf946b26abc2415f520171f5e95871ed096f85584572ade5','ecb7ae730b0d368661d9484155459e26b1bb4b32f8087b5fc917ebf1a6518890','3ff74df26e6aa9b5ebbb0aba4e273201174313c4e2d08b0339b1ea029a8ef01b'];
    private const PROJECTED_TASK_MAPS=['6bc892ba6a0b8830b36057918eb347a11a1c0835bd7c94d7e6d1395009318def','8d3940a748312f38f205f10f905f648e086071b02a815bc37a7f8971a798521f','b2bb7c84bd17b1ead17cc294b6d293d1973d2ea7b9a0df6637f20fc6bf4e5ab4','6a9892123628a4ceaf946b26abc2415f520171f5e95871ed096f85584572ade5','6f90ced214ea9cb35b1d7cae93d2c369b9003332d606912d8b078e83a79d2a03','e4730f7804d483a3616959e2f42189a981aa417626e91f219d00b6a8b5d7e96f'];
    private const EDITORIAL_KEYS=['ab'=>['source_definition','source_definition_sha256','independent_before_sha256','sources','component_map','source_findings','practice_contract','practice_heading_projection','input_alias_review','details_decision','finite_data_refinements'],'cd'=>['source_definition','source_definition_sha256','source_access','component_map','source_findings','after_practice_inventory','details_decision','independent_before_sha256','source_before_sha256','subtitle_decision','meaning_coverage','native_bank_feasibility'],'ef'=>['source_definition','source_definition_sha256','independent_before_sha256','sources','component_map','source_findings','practice_contract','input_alias_review','token_feasibility_findings','details_decision','repetition_review','native_visible_instruction_revision']];
    private const DISPATCH=[
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php','app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php','app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php',
    ];
    private const UNCHANGED=[
        'app/Support/ComposeTokenCase.php'=>['2a496b6142a664eaf01a2086f63452f485bb51147ada20e03d410441b329588d','f1ba518446fe46e1927142cfc94454ec2b65dc389c4e3271c526c0ab4957d6d8'],
        'app/Support/TheoryNativeContentRelease.php'=>['23b89050f061faddd80debce2fefc8dd356a0b49dffa44f79b2200cb5cbbef11','23b89050f061faddd80debce2fefc8dd356a0b49dffa44f79b2200cb5cbbef11'],
        'app/Services/TheoryNativeContentPatch.php'=>['af31eb1a0cc7375f11ace1432e68792255209b696654a93a789c580d1113ec9f','af31eb1a0cc7375f11ace1432e68792255209b696654a93a789c580d1113ec9f'],
        'resources/views/engram/theory/blocks-v3/practice-set.blade.php'=>['0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186','05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e'],
        'resources/views/components/text-block-practice-questions.blade.php'=>['088fb035c1421c43a9e06ac96e4f0c1328aa6acc499e1dfbdbb0ecf39d3cf92d','773c82eed2e099e7a512a5760a4cb425cc83a72a33e40ea9420db19e663862fe'],
        'public/js/english-answer-variants.js'=>['92f4984a6d6d77fd4774a716e8fef5db79f2df1eab29b54333aed60a47cc2540','7000925d6de41fdb6671457bd8d1d7766b9c5b05d2c3d5fe0f1195f80666c681'],
        'public/data/english-contractions.json'=>['9a5d411c3ad87774f7c3d2e79bcd4ac7aebefad8ae7c1a43efbd327a17ca8baf','2815805630fc1987314c9632a055cf3feced856372f73cb29c2a6ade80cb6491'],
        'app/Support/TheoryBuilderSelectionPresentation.php'=>['4866bcda9ea8644bae5f46a30709d30b0838a775c6fa7dd07da8ea46d66d0664','4866bcda9ea8644bae5f46a30709d30b0838a775c6fa7dd07da8ea46d66d0664'],
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
        $raw=self::read(self::root(),'tests/Fixtures/m91-owned-source-hunks.json'); self::assertSame(self::FIXTURE_SHA,hash('sha256',$raw));
        $f=json_decode($raw,true,flags:JSON_THROW_ON_ERROR); self::assertSame(1,$f['schema_version']); self::assertSame(Package::BASE_SHA,$f['base_sha']);
        self::assertSame(self::SOURCE_BEFORE,$f['independent_source_before_sha256']); self::assertSame(self::DB_BEFORE,$f['independent_db_before_sha256']);
        self::assertSame(self::DISPATCH,array_column($f['files'],'path')); return $f;
    }
    private static function contexts(array $fixture): array
    {
        $roots=[self::root()];
        foreach (['GRAMLYZE_M91_SERVED_ROOT','GRAMLYZE_M91_SOURCE_WORKTREE'] as $variable) {
            $candidate=getenv($variable);
            if (!is_string($candidate)||$candidate==='') { continue; }
            self::assertMatchesRegularExpression('~^(?:[A-Za-z]:[/\\\\]|/)~D',$candidate);
            self::assertDirectoryExists($candidate); self::assertFalse(is_link($candidate));
            $candidate=str_replace('\\','/',rtrim($candidate,'/\\'));
            if (!in_array($candidate,$roots,true)) { $roots[]=$candidate; }
        }
        $out=[];
        foreach ($roots as $root) {
            $raw=self::read($root,self::DISPATCH[7]); $profiles=[];
            foreach ($fixture['files'][7]['profiles'] as $name=>$p) { if ($p['after_raw_sha256']===hash('sha256',$raw)) { $profiles[]=$name; } }
            self::assertCount(1,$profiles,'Actual foreign-inclusive controller must match exactly one independently reviewed AFTER profile.');
            $out[]=['root'=>$root,'profile'=>$profiles[0]];
        }
        $names=array_column($out,'profile'); sort($names);
        self::assertSame(['served_root','worktree'],$names,'Supply both exact independently captured contexts; no synthetic or normalized counterpart.');
        return $out;
    }

    public function test_exact_twelve_nonvisual_registrations_reverse_to_independent_lf_before_on_each_profile(): void
    {
        $f=self::fixture();
        foreach (self::contexts($f) as $context) {
            $count=0;
            foreach ($f['files'] as $file) {
                $pin=$file['profiles'][$context['profile']]; $raw=self::read($context['root'],$file['path']); $lf=self::lf($raw); $reverseRaw=$raw;
                self::assertSame('finite-package-registration',$file['kind']);
                self::assertSame($pin['after_raw_sha256'],hash('sha256',$raw)); self::assertSame($pin['after_lf_sha256'],hash('sha256',$lf));
                self::assertSame($pin['after_lf_line_count'],substr_count($raw,"\n")); self::assertSame($pin['after_crlf_line_count'],substr_count($raw,"\r\n"));
                foreach ($file['hunks'] as $hunk) {
                    self::assertSame([1,0],[$hunk['added_lines'],$hunk['removed_lines']]);
                    self::assertStringContainsString('M90',$hunk['before']); self::assertStringNotContainsString('M91',$hunk['before']);
                    self::assertStringStartsWith($hunk['before'],$hunk['after']); $added=substr($hunk['after'],strlen($hunk['before']));
                    self::assertSame(1,substr_count($added,"\n")); self::assertStringContainsString('M91',$added);
                    $pattern='/^'.preg_quote(rtrim($added,"\n"),'/').'\\r?\\n/m';
                    $reverseRaw=preg_replace($pattern,'',$reverseRaw,-1,$removed);
                    self::assertSame(1,$removed,'Only the exact added whole raw line may be removed.');
                    self::assertSame(1,substr_count($lf,$hunk['after'])); $lf=str_replace($hunk['after'],$hunk['before'],$lf); ++$count;
                }
                self::assertSame($pin['before_raw_sha256'],hash('sha256',$reverseRaw),'Complete original context-specific raw bytes must reverse exactly.');
                self::assertSame($pin['before_lf_sha256'],hash('sha256',$lf),'No broad rewrite or deletion is hidden behind owned additive lines.');
            }
            self::assertSame(12,$count); self::assertSame($count,$f['owned_added_registration_lines']);
            self::assertSame([0,0],[$f['owned_validator_added_lines'],$f['owned_validator_removed_lines']]);
        }
    }

    public function test_native_validator_formatter_and_grading_engine_keep_their_independent_raw_bytes(): void
    {
        $f=self::fixture();
        foreach (self::contexts($f) as $context) {
            $i=$context['profile']==='served_root'?0:1;
            foreach (self::UNCHANGED as $path=>$sha) { self::assertSame($sha[$i],hash('sha256',self::read($context['root'],$path)),$path); }
            $source=self::read($context['root'],self::DISPATCH[0]);
            self::assertSame(1,substr_count($source,"[M91FutureSimpleCompletionContinuous::class, 'm42StyleContext']"));
            self::assertStringContainsString('if ($requiredContext !== null && !($context[$requiredContext] ?? false)) { continue; }',$source);
            $renderer=self::read($context['root'],self::DISPATCH[3]);
            self::assertStringContainsString("if ((\$bindings['theoryCanonical'] ?? false) !== true)",$renderer);
            $controller=self::read($context['root'],self::DISPATCH[7]);
            self::assertSame(2,substr_count($controller,'if ($block !== null) { $block = \\App\\Support\\M91FutureSimpleCompletionContinuous::preserveExcludedTextBlock($block, \'standalone_test\'); }'));
            self::assertSame(2,substr_count($controller,'if ($textBlock) { $textBlock = \\App\\Support\\M91FutureSimpleCompletionContinuous::preserveExcludedTextBlock($textBlock, \'standalone_test\'); }'));
        }
    }

    public function test_m89_complete_hint_snapshot_helper_is_reused_byte_exact_without_new_delta_authority(): void
    {
        $f=self::fixture();self::assertArrayNotHasKey('authorized_hint_presenter',$f);
        foreach(self::contexts($f) as $context){
            $raw=self::read($context['root'],'app/Support/TheoryBuilderSelectionPresentation.php');
            self::assertSame('4866bcda9ea8644bae5f46a30709d30b0838a775c6fa7dd07da8ea46d66d0664',hash('sha256',$raw));
            self::assertStringContainsString('complete-loaded-verb-hints-v1',$raw);
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
        self::assertSame(['path'=>'tests/Fixtures/m91-owned-source-hunks.json','sha256'=>self::FIXTURE_SHA],$m['owned_source_fixture']);
        foreach(['ab','cd','ef'] as $group){self::assertContains('docs/content/m91-author-parts/'.$group.'.v1.json',array_column($m['artifacts'],'path'),'All three teaching author parts are independently pinned; Private agent/runtime bookkeeping is explicitly omitted while teaching parts remain exact.');}
        self::assertDoesNotMatchRegularExpression('~DB_PASSWORD|APP_KEY|password|runtime_identity|created_at|updated_at~i',$raw);
    }

    private static function privateReviewKeys(mixed $value,string $pointer=''): array
    {
        if (!is_array($value)) { return []; } $found=[];
        foreach ($value as $key=>$child) {
            $path=$pointer.'/'.$key;
            if (is_string($key)&&preg_match('/^(?:before_binding|actual_SQL_status|actual_sql_metadata|source_read|source_metadata_status|source_metadata_policy|source_practice_inventory|source_sql_body_exact|source_sql_subtitle_exact|pending_main_acceptance|pending_main_proofs|agent_review_not_human_review|review_kind|signoff|offline_native_review|native_replay_status|continuing_identity_rows|uk_rows|created_at|updated_at|row_timestamp|sql_before|sql_after_http|runtime_identity|password|db_password|app_key|credentials|cookies|nonce)$/iD',$key)) {
                $found[]=$path;
            }
            array_push($found,...self::privateReviewKeys($child,$path));
        }
        return $found;
    }

    public function test_public_review_exports_omit_raw_metadata_and_keep_exact_learning_and_ledger_pins(): void
    {
        $manifestRaw=self::read(self::root(),Package::CHECKSUMS);
        self::assertSame(Package::CHECKSUMS_SHA,hash('sha256',$manifestRaw));
        $manifest=json_decode($manifestRaw,true,flags:JSON_THROW_ON_ERROR);
        $records=array_column($manifest['artifacts'],null,'path');
        $read=static function(string $path) use ($records): array {
            self::assertArrayHasKey($path,$records); $raw=self::read(self::root(),$path);
            self::assertSame($records[$path]['sha256'],hash('sha256',$raw));
            self::assertSame($records[$path]['bytes'],strlen($raw));
            $data=json_decode($raw,true,flags:JSON_THROW_ON_ERROR);
            self::assertSame([],self::privateReviewKeys($data),'Removed metadata must not be renamed or reintroduced: '.$path);
            return $data;
        };
        $master=$read(Package::MASTER);
        $editorial=$read('docs/content/m91-future-simple-completion-continuous.editorial.v1.json');
        self::assertSame(Package::OWNERS,array_column($master['lessons'],'identity'));
        self::assertSame($master['editorial_review']['lesson_reviews'],$editorial['lesson_reviews']);
        foreach ($master['lessons'] as $i=>$lesson) {
            $owner=Package::OWNERS[$i];
            self::assertSame(self::PROJECTED_LESSONS[$i],\App\Support\M26DetailPackage::digest($lesson),'Teaching projection is unchanged by privacy sanitization.');
            self::assertSame(self::PROJECTED_TASK_MAPS[$i],\App\Support\M26DetailPackage::digest($master['editorial_review']['task_map'][$owner]));
        }
        foreach (['ab','cd','ef'] as $groupIndex=>$group) {
            $parts=$read('docs/content/m91-author-parts/'.$group.'.v1.json');
            self::assertCount(2,$parts['parts']);
            foreach ($parts['parts'] as $partIndex=>$part) {
                $i=$groupIndex*2+$partIndex; $owner=Package::OWNERS[$i];
                self::assertSame(['lesson','editorial_review','task_map'],array_keys($part));
                self::assertSame($owner,$part['lesson']['identity']);
                self::assertSame(self::AUTHORED_LESSONS[$i],\App\Support\M26DetailPackage::digest($part['lesson']));
                self::assertSame(self::AUTHORED_TASK_MAPS[$i],\App\Support\M26DetailPackage::digest($part['task_map']));
                $projected=[];$expectedRoles=[];
                foreach($part['task_map']as$row){
                    $taskGroup=$row['after_group'];$index=$row['after_group_index'];
                    $role=$taskGroup==='inputs'?'full_sentence':($taskGroup==='choices'?'meaning':'grammar_form');
                    $expectedRoles[]=['identity'=>$owner,'group'=>$taskGroup,'index'=>$index,
                        'author_role_fields'=>array_intersect_key($row,array_flip(['assessment_role','assessed_role'])),
                        'assessed_role'=>$role];
                    $row['assessed_role']=$role;$projected[]=$row;
                }
                self::assertSame($projected,$master['editorial_review']['task_map'][$owner],
                    'Only the explicitly declared assessed_role projection may differ; task data, source identity and aliases are exact.');
                $recorded=array_values(array_filter($master['editorial_review']['native_task_role_projection']??[],
                    static fn($row)=>($row['identity']??null)===$owner));
                self::assertCount(count($expectedRoles),$recorded);
                foreach($recorded as$n=>$record){
                    self::assertSame(['identity','group','index','author_role_fields','assessed_role','reason'],array_keys($record));
                    self::assertIsString($record['reason']);self::assertNotSame('',trim($record['reason']));
                    unset($record['reason']);self::assertSame($expectedRoles[$n],$record);
                }
                self::assertSame([],array_values(array_diff(array_keys($part['editorial_review']),self::EDITORIAL_KEYS[$group])));
                foreach (['source_access','additional_access_attempts','sources'] as $field) {
                    if (array_key_exists($field,$part['editorial_review'])) {
                        self::assertSame($part['editorial_review'][$field],$master['editorial_review']['lesson_reviews'][$owner][$field],'Primary source catalogues remain exact.');
                    }
                }
            }
        }
    }

    public function test_attributes_keep_root_absent_and_worktree_exact_prefix_or_explicit_reviewed_suffix(): void
    {
        foreach (self::contexts(self::fixture()) as $context) {
            $path=$context['root'].'/.gitattributes';
            self::assertFalse(is_link($path));
            if ($context['profile']==='served_root') {
                self::assertFileDoesNotExist($path,'No foreign ROOT attributes file may be introduced.');
                continue;
            }
            self::assertFileExists($path); $raw=file_get_contents($path);
            self::assertGreaterThanOrEqual(self::WT_ATTRIBUTES_BEFORE_BYTES,strlen($raw));
            self::assertSame(self::WT_ATTRIBUTES_BEFORE_SHA,hash('sha256',substr($raw,0,self::WT_ATTRIBUTES_BEFORE_BYTES)));
            $suffix=substr($raw,self::WT_ATTRIBUTES_BEFORE_BYTES);
            if ($suffix==='') {
                self::assertSame(self::WT_ATTRIBUTES_BEFORE_SHA,hash('sha256',$raw));
                continue; // Exact captured BEFORE, never a generic append exception.
            }
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D',self::WT_ATTRIBUTES_SUFFIX_SHA);
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D',self::WT_ATTRIBUTES_AFTER_SHA);
            self::assertSame(self::WT_ATTRIBUTES_SUFFIX_BYTES,strlen($suffix));
            self::assertSame(self::WT_ATTRIBUTES_SUFFIX_SHA,hash('sha256',$suffix));
            self::assertSame(self::WT_ATTRIBUTES_AFTER_SHA,hash('sha256',$raw));
        }
    }

}
