<?php

namespace Tests\Unit;

use App\Services\PresentPerfectPassiveWordSlotsLocalGuard as Guard;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure policy fixtures: no DB, web requests, subprocesses or private diagnostic reads. */
final class PresentPerfectPassiveWordSlotsLocalGuardTest extends TestCase
{
    public function test_scope_is_only_present_perfect_passive(): void
    {
        self::assertSame([['identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentPerfectTheorySeeder',
            'locale'=>'uk','path'=>'/theory/passive-voice/passive-voice-tenses/theory-passive-voice-present-perfect']],Guard::scope());
        self::assertSame(Guard::ROOT.'/storage/app/present-perfect-passive-word-slots',Guard::privateDirectory());
    }

    public function test_only_exact_loopback_get_has_proof_authority(): void
    {
        $nonce=str_repeat('a',32);$url='http://gramlyze.loc/api/_local/present-perfect-passive-word-slots-'.$nonce;
        $make=static function(string $url,string $method='GET',array $server=[]): Request {
            $request=Request::create($url,$method,server:array_merge(['REMOTE_ADDR'=>'127.0.0.1'],$server));
            $request->server->set('REQUEST_METHOD',$method);return $request;
        };
        self::assertTrue(Guard::allowsProofRequest($make($url),$nonce,Guard::ROOT,Guard::ROOT.'/public'));
        foreach(['HEAD','POST','DELETE'] as $method) { self::assertFalse(Guard::allowsProofRequest($make($url,$method),$nonce,Guard::ROOT,Guard::ROOT.'/public')); }
        foreach(['https://gramlyze.loc','http://gramlyze.ub','http://gramlyze.com','http://127.0.0.1'] as $origin) {
            self::assertFalse(Guard::allowsProofRequest($make(str_replace('http://gramlyze.loc',$origin,$url)),$nonce,Guard::ROOT,Guard::ROOT.'/public'));
        }
        foreach([['REMOTE_ADDR'=>'192.168.0.102'],['HTTP_COOKIE'=>'fixture'],['HTTP_FORWARDED'=>'for=127.0.0.1'],['HTTP_AUTHORIZATION'=>'fixture']] as $server) {
            self::assertFalse(Guard::allowsProofRequest($make($url,'GET',$server),$nonce,Guard::ROOT,Guard::ROOT.'/public'));
        }
        self::assertFalse(Guard::allowsProofRequest($make($url.'?other=1'),$nonce,Guard::ROOT,Guard::ROOT.'/public'));
        self::assertFalse(Guard::allowsProofRequest($make($url),str_repeat('b',32),Guard::ROOT,Guard::ROOT.'/public'));
    }

    public function test_mask_preserves_every_unrelated_column_and_only_target_relation_sets_are_replaceable(): void
    {
        $ids=['64560'=>true];$question=['id'=>64560,'question'=>'before','options_by_marker'=>'{}','uuid'=>'kept','level'=>'B2','created_at'=>'old','updated_at'=>'old'];
        self::assertSame(array_replace($question,['question'=>null,'options_by_marker'=>null]),Guard::protectedRow('questions',$question,$ids));
        $foreign=array_replace($question,['id'=>64561]);self::assertSame($foreign,Guard::protectedRow('questions',$foreign,$ids));
        foreach(['question_answers','verb_hints','question_option_question'] as $table) {
            self::assertNull(Guard::protectedRow($table,['question_id'=>64560,'id'=>1],$ids));
            self::assertSame(['question_id'=>64561,'id'=>1],Guard::protectedRow($table,['question_id'=>64561,'id'=>1],$ids));
        }
        foreach(['question_tag','saved_grammar_tests','test_questions','user_progress'] as $table) {
            $row=['id'=>1,'question_id'=>64560,'value'=>'untouched'];self::assertSame($row,Guard::protectedRow($table,$row,$ids));
        }
        $hint=['id'=>1,'question_id'=>64560,'provider'=>'compose_prompt','locale'=>'uk','hint'=>'before','created_at'=>'old'];
        self::assertSame(array_replace($hint,['hint'=>null]),Guard::protectedRow('question_hints',$hint,$ids));
        foreach([['provider'=>'other'],['locale'=>'en']] as $change) { $other=array_replace($hint,$change);self::assertSame($other,Guard::protectedRow('question_hints',$other,$ids)); }
    }

    public function test_physical_proof_rejects_a_remote_or_wrong_process_target(): void
    {
        $guard=new Guard;$e=['addresses'=>['127.0.0.1'],'document_root'=>strtolower(Guard::ROOT).'/public','active_vhosts'=>1,
            'apache_name'=>'httpd.exe','apache_pid'=>1,'apache_pid_file'=>1,'mysql_listeners'=>[['port'=>3306,'pid'=>2,'name'=>'mysqld.exe']]];
        $guard->assertEvidence($e,strtolower(Guard::ROOT),2,3306);$this->addToAssertionCount(1);
        $e['addresses']=['192.168.0.102'];$this->expectException(RuntimeException::class);$guard->assertEvidence($e,strtolower(Guard::ROOT),2,3306);
    }
}
