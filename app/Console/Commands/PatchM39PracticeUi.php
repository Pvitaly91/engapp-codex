<?php

namespace App\Console\Commands;

use App\Services\M39PracticeUiPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PatchM39PracticeUi extends Command
{
    protected $signature='content:patch-practice-ui-m39 {--plan=} {--apply} {--restore} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description='Explicit finite M39 practice-body UI preview/apply/restore; never seeds';
    public function handle(): int
    {
        $directory=storage_path('app/seo-m39-local');
        $path=static function($name) use($directory):string{
            if(!is_string($name)||!preg_match('/^m39-practice-ui-[a-z0-9-]+\.json$/D',$name))throw new RuntimeException('Use a private M39 practice UI JSON basename.');
            return $directory.'/'.$name;
        };
        try{
            if(!is_dir($directory)||is_link($directory))throw new RuntimeException('Existing private M39 directory required.');
            if($this->option('apply')&&$this->option('restore'))throw new RuntimeException('Choose apply or restore.');
            if($this->option('local-proof')&&!$this->option('local-target'))throw new RuntimeException('Proof requires explicit local target.');
            $p=new M39PracticeUiPatch(DB::connection(),database_path(),$directory,$this->option('local-target'),$this->option('local-proof'));
            if($this->option('restore'))$result=$p->restore($path($this->option('backup')),$this->option('database'));
            elseif($this->option('apply'))$result=$p->apply($path($this->option('plan')),$path($this->option('backup')),$this->option('database'));
            else{$plan=$p->savePlan($path($this->option('plan')));$result=['status'=>'dry-run','connection'=>$plan['connection'],'state'=>$plan['state'],'updated'=>count($plan['updates']),'inserted'=>count($plan['inserts']),'sha256'=>$plan['sha256']];}
            $this->line(json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));return self::SUCCESS;
        }catch(\Throwable $e){
            $this->error($e instanceof RuntimeException&&!$e instanceof \PDOException?$e->getMessage():'M39 practice UI failed; no successful transaction committed.');return self::FAILURE;
        }
    }
}
