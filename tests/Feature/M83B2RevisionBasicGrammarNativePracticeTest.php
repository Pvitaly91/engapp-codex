<?php

namespace Tests\Feature;

use App\Support\M83B2RevisionBasicGrammar as Package;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class M83B2RevisionBasicGrammarNativePracticeTest extends TestCase
{
    public function test_all_sixty_tasks_five_demos_and_sixteen_full_inputs_use_the_unchanged_native_engine():void
    {
        $root=dirname(__DIR__,2);[$before,$patch,$master,$contract]=Package::load($root);
        Package::validate($before,$patch,$master,$contract);
        $factory=getenv('GRAMLYZE_M83_FACTORY_ROOT')?:$root;
        $process=new Process([getenv('NODE_BINARY')?:'node',$root.'/tests/Browser/m83-native-practice.cjs',$factory],$root,timeout:45);
        $process->setInput(json_encode(['lessons'=>$master['lessons']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
        $process->run();self::assertSame(0,$process->getExitCode(),$process->getOutput().$process->getErrorOutput());
        $result=json_decode($process->getOutput(),true,flags:JSON_THROW_ON_ERROR);
        foreach(['tasks'=>60,'demos'=>5,'full_inputs'=>16,'manual_token_nominal_cases'=>64,'delete_return_cases'=>32,
            'semantic_cases'=>26,'network_calls'=>0,'db_calls'=>0,'file_writes'=>0]as $key=>$expected){self::assertSame($expected,$result[$key],$key);}
        self::assertGreaterThanOrEqual(60,$result['aliases']);self::assertGreaterThanOrEqual(60,$result['shown_options']);
    }
}
