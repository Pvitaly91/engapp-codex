<?php

namespace Tests\Feature;

use App\Support\M89PresentPerfectCompletionContinuous as Package;
use PHPUnit\Framework\TestCase;

/** Independent raw-source lineage only: no Laravel bootstrap, environment, SQL, HTTP or Git. */
class M89SourceBoundaryTest extends TestCase
{
    private const FIXTURE_SHA='0ef4b9639e6b96c6bd977a2c5474c9c8f2130c89a3b63c58d03d2c3548f27519';
    private const SOURCE_BEFORE='8c3f38c4381f839602b507d73924bc46d5bf06b2492dab5ceffdb0bc3a2d3bb0';
    private const DB_BEFORE='6c443660442bc7f683264c381b559973e19ea2fce9e58fa8410e6b21ff0416ae';
    private const WT_ATTRIBUTES_BEFORE_SHA='280671550ea4ecfd23b407d3ff942d36c048ab2dd749b0bc35c0ebd96629a641';
    private const WT_ATTRIBUTES_BEFORE_BYTES=67605;
    // Future append must be separately reviewed and pinned; PENDING never accepts any suffix.
    private const WT_ATTRIBUTES_SUFFIX_SHA='e41a37edbbf2deb4ebf866eb7e30b1fc3490a6e54aec83931ed33d6ae569d7f9';
    private const WT_ATTRIBUTES_SUFFIX_BYTES=2723;
    private const WT_ATTRIBUTES_AFTER_SHA='c78abfb95e873fa57d31ed066992b894df9248df1fe3db556438af2213bb3a5b';
    // Independent ordered-JSON teaching/task pins are PENDING the reviewed M89 projection/freeze; no historical pins are reused.
    private const PROJECTED_LESSONS=['b6eb8d5242c7a26b0348223fbaac65f82157b4eee509226ac21e30f3f695b2ed','76dddb6d46a95cb54e3a719ddceb6e366e2ba96df9e4fadcab56317899bfaab0','baf37c705845a949689066498efee41b78a6f9a6a8d520008a7e8aef65df1aac','fd14bbe4fd7cf853990910a9d677c2720c217c0320b0bf050026e18928aa376c','96039a2e86db7f9d756c93fff98f86b185a2331590868bd09af437a2a480a88e','55309830024dee36f80348baabee8590d7aff8ca1d19939442bd0260e6793a9c'];
    private const AUTHORED_LESSONS=['b6eb8d5242c7a26b0348223fbaac65f82157b4eee509226ac21e30f3f695b2ed','76dddb6d46a95cb54e3a719ddceb6e366e2ba96df9e4fadcab56317899bfaab0','baf37c705845a949689066498efee41b78a6f9a6a8d520008a7e8aef65df1aac','fd14bbe4fd7cf853990910a9d677c2720c217c0320b0bf050026e18928aa376c','a67641e7ffc2003523a50751b821f58d74ef07973e65dd40525678708d82e565','33f2f7ec37f339c1fe95671a1a9641a910f3e42afe3a7146391389f76d616300'];
    private const AUTHORED_TASK_MAPS=['d73f2f2f5437cecc4a54ffe1770cbf3299aa54b3e5726adad374c44af98cc395','32551b5167efdc04f8f72caa941b3aa88d9d73913f9dd69092aea9e312606f1f','d78b5fea931799e92b4695c1577033053df4b4233518b4fb022076f7f6513099','1e70d38e72cb8c28292e4e0a27fae1e95dc8fefa047bd34e9d585d11f26775a3','770aec9684884238d840277fb31e943fef60698f0e4e53eb24937218a5d7041c','fd27628612df8e1e6169325c4b346f72b30c433b76318860fe08add694dd2c6d'];
    private const PROJECTED_TASK_MAPS=['054c64965b9fc2592346b2318a16e070323e0c2e9e39a0fd7dc209342ece98c4','9cdad405ddb7a156e0e6c5b7b8638ed47be1d5c6f064726223fef4245966b658','d78b5fea931799e92b4695c1577033053df4b4233518b4fb022076f7f6513099','1e70d38e72cb8c28292e4e0a27fae1e95dc8fefa047bd34e9d585d11f26775a3','3d750eb8f1b5945989b08f5f5fde25fe7cf1abdcb33976495cf4190c22b5d941','a91ebcb800357b8816bb5aab850a76c8d6bf10b0d7ea647772e1affa6112ad1d'];
    private const EDITORIAL_KEYS=['ab'=>['source_definition','source_definition_sha256','independent_before_sha256','sources','component_map','source_findings','coverage_map','practice_contract','practice_heading_projection','input_alias_review','details_decision','finite_data_refinements'],'cd'=>['source_definition','source_definition_sha256','independent_before_sha256','source_before_sha256','source_access','component_map','subtitle_editorial_transition','retained_source_english_models','retained_source_english_models_all_present','coverage_map','after_practice_inventory','accepted_only_transition','token_feasibility_findings','normalization_boundary','authored_time_scope','details_decision','public_data_policy'],'ef'=>['source_definition','source_definition_sha256','independent_before_sha256','sources','component_map','source_findings','coverage_map','practice_contract','input_alias_review','details_decision']];
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
        $raw=self::read(self::root(),'tests/Fixtures/m89-owned-source-hunks.json'); self::assertSame(self::FIXTURE_SHA,hash('sha256',$raw));
        $f=json_decode($raw,true,flags:JSON_THROW_ON_ERROR); self::assertSame(1,$f['schema_version']); self::assertSame(Package::BASE_SHA,$f['base_sha']);
        self::assertSame(self::SOURCE_BEFORE,$f['independent_source_before_sha256']); self::assertSame(self::DB_BEFORE,$f['independent_db_before_sha256']);
        self::assertSame(self::DISPATCH,array_column($f['files'],'path')); return $f;
    }
    private static function contexts(array $fixture): array
    {
        $roots=[self::root()];
        foreach (['GRAMLYZE_M89_SERVED_ROOT','GRAMLYZE_M89_SOURCE_WORKTREE'] as $variable) {
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
                    self::assertStringContainsString('M88',$hunk['before']); self::assertStringNotContainsString('M89',$hunk['before']);
                    self::assertStringStartsWith($hunk['before'],$hunk['after']); $added=substr($hunk['after'],strlen($hunk['before']));
                    self::assertSame(1,substr_count($added,"\n")); self::assertStringContainsString('M89',$added);
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
            self::assertSame(1,substr_count($source,"[M89PresentPerfectCompletionContinuous::class, 'm42StyleContext']"));
            self::assertStringContainsString('if ($requiredContext !== null && !($context[$requiredContext] ?? false)) { continue; }',$source);
            $renderer=self::read($context['root'],self::DISPATCH[3]);
            self::assertStringContainsString("if ((\$bindings['theoryCanonical'] ?? false) !== true)",$renderer);
            $controller=self::read($context['root'],self::DISPATCH[7]);
            self::assertSame(2,substr_count($controller,'if ($block !== null) { $block = \\App\\Support\\M89PresentPerfectCompletionContinuous::preserveExcludedTextBlock($block, \'standalone_test\'); }'));
            self::assertSame(2,substr_count($controller,'if ($textBlock) { $textBlock = \\App\\Support\\M89PresentPerfectCompletionContinuous::preserveExcludedTextBlock($textBlock, \'standalone_test\'); }'));
        }
    }

    public function test_authorized_hint_presenter_delta_is_exact_and_reverses_to_both_independent_before_profiles(): void
    {
        $f=self::fixture();$h=$f['authorized_hint_presenter']??null;
        self::assertIsArray($h);
        self::assertSame(['before_sha256','after_sha256','reverse_hunks'],array_keys($h));
        self::assertSame('b89e7578f8ea1850b799c76739bd5aac81611315de7fccf86fa62ddb7f51eb4a',$h['before_sha256']);
        self::assertSame('4866bcda9ea8644bae5f46a30709d30b0838a775c6fa7dd07da8ea46d66d0664',$h['after_sha256']);
        self::assertIsArray($h['reverse_hunks']);self::assertTrue(array_is_list($h['reverse_hunks']));self::assertNotEmpty($h['reverse_hunks']);
        foreach(self::contexts($f)as$context){
            $raw=self::read($context['root'],'app/Support/TheoryBuilderSelectionPresentation.php');
            self::assertSame($h['after_sha256'],hash('sha256',$raw));$reverse=$raw;
            foreach($h['reverse_hunks']as$hunk){
                self::assertSame(['before','after'],array_keys($hunk));
                self::assertIsString($hunk['before']);self::assertIsString($hunk['after']);
                self::assertNotSame('',$hunk['after']);self::assertNotSame($hunk['before'],$hunk['after']);
                self::assertSame(1,substr_count($reverse,$hunk['after']),'Only the exact independently reviewed helper hunk may reverse.');
                $reverse=str_replace($hunk['after'],$hunk['before'],$reverse);
            }
            self::assertSame($h['before_sha256'],hash('sha256',$reverse),'No unrelated presenter change may hide behind the approved hint support.');
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
        self::assertSame(['path'=>'tests/Fixtures/m89-owned-source-hunks.json','sha256'=>self::FIXTURE_SHA],$m['owned_source_fixture']);
        foreach(['ab','cd','ef'] as $group){self::assertContains('docs/content/m89-author-parts/'.$group.'.v1.json',array_column($m['artifacts'],'path'),'All three teaching author parts are independently pinned; Private agent/runtime bookkeeping is explicitly omitted while teaching parts remain exact.');}
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
        $editorial=$read('docs/content/m89-present-perfect-completion-continuous.editorial.v1.json');
        self::assertSame(Package::OWNERS,array_column($master['lessons'],'identity'));
        self::assertSame($master['editorial_review']['lesson_reviews'],$editorial['lesson_reviews']);
        foreach ($master['lessons'] as $i=>$lesson) {
            $owner=Package::OWNERS[$i];
            self::assertSame(self::PROJECTED_LESSONS[$i],\App\Support\M26DetailPackage::digest($lesson),'Teaching projection is unchanged by privacy sanitization.');
            self::assertSame(self::PROJECTED_TASK_MAPS[$i],\App\Support\M26DetailPackage::digest($master['editorial_review']['task_map'][$owner]));
        }
        foreach (['ab','cd','ef'] as $groupIndex=>$group) {
            $parts=$read('docs/content/m89-author-parts/'.$group.'.v1.json');
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
                    $role=$taskGroup==='inputs'?'full_sentence':($taskGroup==='choices'&&($i<2||$index>=2)?'meaning':'grammar_form');
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
