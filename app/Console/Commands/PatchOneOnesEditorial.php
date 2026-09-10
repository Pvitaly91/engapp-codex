<?php

namespace App\Console\Commands;

use App\Services\OneOnesEditorialPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PatchOneOnesEditorial extends Command
{
    protected $signature = 'content:patch-one-ones-m8-1
        {--plan= : New private preview basename, existing basename for apply}
        {--apply : Apply only the inspected M8.1 preview; default is read-only}
        {--database= : Exact inspected local MySQL database name for writes}
        {--backup= : New exclusive private backup basename for apply}';
    protected $description = 'M8.1: update only five accepted One/Ones UK bodies, without reseeding';

    public function handle(): int
    {
        if (!app()->environment('local', 'testing')) { $this->error('Local/testing environment only.'); return self::FAILURE; }
        $directory = storage_path('app/seo-m8-1-local');
        if (!is_dir($directory)) { mkdir($directory, 0700, true); }
        try {
            $name = $this->option('plan');
            if (!$name || !preg_match('/^[a-zA-Z0-9_-]+\.json$/', $name)) { throw new RuntimeException('--plan needs a simple .json basename.'); }
            $patch = new OneOnesEditorialPatch(DB::connection(), database_path(), $directory);
            if ($this->option('apply')) {
                $backup = $this->option('backup');
                if (!$backup || !preg_match('/^[a-zA-Z0-9_-]+\.json$/', $backup)) { throw new RuntimeException('--backup needs a new .json basename.'); }
                $result = $patch->apply($directory.'/'.$name, $directory.'/'.$backup, $this->option('database'));
            } else {
                $plan = $patch->savePlan($directory.'/'.$name);
                $result = ['status' => 'dry-run', 'connection' => $plan['connection'], 'changes' => count($plan['changes']),
                    'plan' => $directory.'/'.$name, 'sha256' => $plan['sha256']];
            }
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e instanceof RuntimeException && !$e instanceof \PDOException ? $e->getMessage() : 'Patch failed; no successful transaction was committed.');
            return self::FAILURE;
        }
    }
}
