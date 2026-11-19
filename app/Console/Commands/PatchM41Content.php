<?php

namespace App\Console\Commands;

use App\Services\M41ContentPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PatchM41Content extends Command
{
    protected $signature = 'content:patch-authored-tense-comparisons-m41 {--plan=} {--apply} {--restore} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description = 'Explicit finite M41 preview/apply/restore for three UK lessons; never seeds';

    public function handle(): int
    {
        $directory=storage_path('app/seo-m41-local');
        if (!is_dir($directory)) { mkdir($directory,0700,true); }
        $path=static function ($name) use ($directory): string {
            if (!is_string($name)||!preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$name)) { throw new RuntimeException('Use a private M41 JSON basename.'); }
            return $directory.'/'.$name;
        };
        try {
            if ($this->option('apply')&&$this->option('restore')) { throw new RuntimeException('Choose apply or restore.'); }
            if ($this->option('local-proof') && !$this->option('local-target')) { throw new RuntimeException('Proof requires explicit local target.'); }
            $p=new M41ContentPatch(DB::connection(),database_path(),$directory,$this->option('local-target'),$this->option('local-proof'));
            if ($this->option('restore')) { $r=$p->restore($path($this->option('backup')),$this->option('database')); }
            elseif ($this->option('apply')) { $r=$p->apply($path($this->option('plan')),$path($this->option('backup')),$this->option('database')); }
            else { $plan=$p->savePlan($path($this->option('plan'))); $r=['status'=>'dry-run','connection'=>$plan['connection'],'state'=>$plan['state'],
                'updated'=>count($plan['updates']),'inserted'=>count($plan['inserts']),'sha256'=>$plan['sha256']]; }
            $this->line(json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)); return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e instanceof RuntimeException && !$e instanceof \PDOException ? $e->getMessage() : 'M41 failed; no successful transaction committed.'); return self::FAILURE;
        }
    }
}
