<?php

namespace App\Console\Commands;

use App\Services\M73ContentPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit local CLI only; never invoked by HTTP/startup/scheduler. */
class ContentPatchM73 extends Command
{
    protected $signature = 'content:patch-generic-irregular-collective-m73 {--plan=} {--apply} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description = 'Scoped M73 native content preview/apply for three UK lessons';
    public function handle():int
    {
        try{
            if($this->option('local-target')!=='gramlyze.loc'||!is_string($this->option('local-proof'))
                ||preg_match('/^local-proof-[a-f0-9]{32}\.json$/D',$this->option('local-proof'))!==1){
                throw new RuntimeException('M73 requires gramlyze.loc and a fresh nonce proof basename.');
            }
            if(!$this->option('apply')&&$this->option('backup')!==null){throw new RuntimeException('M73 backup is only valid for apply.');}
            $dir=storage_path('app/seo-m73-local');
            if(!is_dir($dir)||is_link($dir)){throw new RuntimeException('M73 private evidence directory unavailable.');}
            $path=static function($name)use($dir):string{
                if(!is_string($name)||preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$name)!==1){throw new RuntimeException('M73 requires a direct private JSON basename.');}
                return $dir.'/'.$name;
            };
            $patch=new M73ContentPatch(DB::connection(),database_path(),$dir,$this->option('local-target'),$this->option('local-proof'));
            if($this->option('apply')){$result=$patch->apply($path($this->option('plan')),$path($this->option('backup')),$this->option('database'));}
            else{$plan=$patch->savePlan($path($this->option('plan')));$result=['status'=>'dry-run','connection'=>$plan['connection'],
                'state'=>$plan['state'],'updated'=>count($plan['updates']),'inserted'=>count($plan['inserts']),
                'protected_tables'=>count($plan['protected']),'sha256'=>$plan['sha256']];}
            $this->line(json_encode($result+['deleted'=>0],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        }catch(\Throwable $error){
            $this->error($error instanceof RuntimeException&&!$error instanceof \PDOException?$error->getMessage():'M73 incomplete; no successful transaction committed.');
            return self::FAILURE;
        }
    }
}
