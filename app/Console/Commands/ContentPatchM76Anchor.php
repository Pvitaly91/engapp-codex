<?php

namespace App\Console\Commands;

use App\Services\M76AnchorPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit local successor CLI; never an HTTP/startup/scheduler hook. */
class ContentPatchM76Anchor extends Command
{
    protected $signature='content:patch-m76-anchor {--plan=} {--apply} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description='Exact one-body M76 1.0.1 old-fragment successor';
    public function handle():int
    {
        try{
            if($this->option('local-target')!=='gramlyze.loc'||!is_string($this->option('local-proof'))
                ||preg_match('/^local-proof-[a-f0-9]{32}\.json$/D',$this->option('local-proof'))!==1){
                throw new RuntimeException('M76 anchor requires gramlyze.loc and fresh nonce proof.');
            }
            if(!$this->option('apply')&&$this->option('backup')!==null){throw new RuntimeException('M76 anchor backup requires apply.');}
            $dir=storage_path('app/seo-m76-local');
            if(!is_dir($dir)||is_link($dir)){throw new RuntimeException('M76 anchor private directory unavailable.');}
            $path=static function($name)use($dir):string{
                if(!is_string($name)||preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$name)!==1){throw new RuntimeException('M76 anchor direct private JSON basename required.');}
                return $dir.'/'.$name;
            };
            $service=new M76AnchorPatch(DB::connection(),database_path(),$dir,$this->option('local-target'),$this->option('local-proof'));
            if($this->option('apply')){$result=$service->apply($path($this->option('plan')),$path($this->option('backup')),$this->option('database'));}
            else{$plan=$service->savePlan($path($this->option('plan')));$result=['status'=>'dry-run','state'=>$plan['state'],
                'updated'=>count($plan['updates']),'inserted'=>count($plan['inserts']),'protected_tables'=>count($plan['protected']),'sha256'=>$plan['sha256']];}
            $this->line(json_encode($result+['deleted'=>0],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));return self::SUCCESS;
        }catch(\Throwable $error){
            $this->error($error instanceof RuntimeException&&!$error instanceof \PDOException?$error->getMessage():'M76 anchor incomplete; no successful transaction committed.');
            return self::FAILURE;
        }
    }
}

