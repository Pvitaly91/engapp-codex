<?php

namespace Tests\Unit;

use App\Services\M85MistakeNotesPatch as Patch;
use App\Support\M85VerbToBe as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure finite provenance fixtures; no private SQL, Laravel bootstrap or Git execution. */
class M85MistakeNotesSourceProfileTest extends TestCase
{
    private function fixture():array
    {
        $p=['path'=>Patch::FOREIGN_FORMATTER_PATH,
            'root_raw_sha256'=>'4a0d7cf7bcedecf480839ed040a3675965854f274e8993b89c07eddcbcc807ba','root_bytes'=>3299,'root_lf'=>85,'root_crlf'=>77,
            'worktree_raw_sha256'=>'1b05f9e539424e43cf007e82305d01a5b7307dad6a7f653343b4555b76aaeb8f','worktree_bytes'=>3190,'worktree_lf'=>85,'worktree_crlf'=>85,
            'accepted_base'=>Package::BASE_SHA,'worktree_head'=>Package::BASE_SHA,'worktree_path_status'=>'',
            'git_blob_sha1'=>'2b4a74a6a4e5507dbe180eeb2b4732749f5d08de','git_blob_sha256'=>'f2f2689dd1677e23173e13e83f219bf37841eecf5b0d39663b118018f7f18285',
            'git_blob_bytes'=>3105,'git_blob_lf'=>85,'git_blob_crlf'=>0,'worktree_lf_equals_accepted_git_blob'=>true];
        $plan=['sources'=>[Patch::FOREIGN_FORMATTER_PATH=>$p['root_raw_sha256']],'fixture'=>'not a live SQL receipt'];
        $digest=Patch::digest($plan);$plan['sha256']=$digest;return [$p,$plan,$digest];
    }
    public function test_exact_single_context_difference_has_separate_honest_provenance():void
    {
        [$p,$plan,$digest]=$this->fixture();$result=Patch::foreignFormatterProfile($p,$plan,$digest);
        self::assertSame($p,array_intersect_key($result,$p));self::assertFalse($result['historical_worktree_raw_capture_claimed']);
        self::assertFalse($result['copied_or_normalized']);self::assertStringContainsString('preview.sources',$result['root_provenance']);
        self::assertStringContainsString('Git blob',$result['worktree_provenance']);
    }
    public function test_every_changed_profile_field_unknown_path_dirty_status_or_unbound_root_is_rejected():void
    {
        [$p,$plan,$digest]=$this->fixture();
        foreach($p as $key=>$value){$changed=$p;$changed[$key]=is_bool($value)?false:(is_int($value)?$value+1:$value.'changed');$error=null;
            try{Patch::foreignFormatterProfile($changed,$plan,$digest);}catch(RuntimeException $e){$error=$e;}
            self::assertInstanceOf(RuntimeException::class,$error,$key);
        }
        foreach(['sha256','root-source'] as $case){$changed=$plan;if($case==='sha256'){$changed['sha256']=str_repeat('0',64);}else{$changed['sources'][Patch::FOREIGN_FORMATTER_PATH]=str_repeat('0',64);unset($changed['sha256']);$changed['sha256']=Patch::digest($changed);}
            $error=null;try{Patch::foreignFormatterProfile($p,$changed,$case==='sha256'?$digest:$changed['sha256']);}catch(RuntimeException $e){$error=$e;}
            self::assertInstanceOf(RuntimeException::class,$error,$case);
        }
    }
    public function test_context_specific_raw_profiles_preserve_eol_differences_and_captured_absence():void
    {
        $wt="one\r\ntwo\r\n";$root="one\ntwo\n";
        $profile=['exists'=>true,'bytes'=>strlen($wt),'raw_sha256'=>hash('sha256',$wt),
            'lf_sha256'=>hash('sha256',$root),'crlf'=>2,'lf'=>2];
        Patch::assertCapturedContextBytes($wt,$profile);Patch::assertCapturedContextBytes(null,['exists'=>false]);
        self::assertNotSame($profile['raw_sha256'],$profile['lf_sha256']);
        foreach([[$root,$profile],[null,$profile],[$wt,['exists'=>false]],[$wt,array_replace($profile,['crlf'=>0])],
            [$wt.'changed',$profile]] as [$bytes,$expected]){
            $error=null;try{Patch::assertCapturedContextBytes($bytes,$expected);}catch(RuntimeException $e){$error=$e;}
            self::assertInstanceOf(RuntimeException::class,$error,'No ROOT→WT normalization or absent-file recreation is accepted.');
        }
    }
}
