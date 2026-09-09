<?php

namespace App\Console\Commands;

use App\Services\PronounContentRepair;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RepairPronounContent extends Command
{
    protected $signature = 'content:repair-pronoun-blocks
        {--plan= : New private plan basename for dry-run, existing basename for apply}
        {--apply : Apply exactly the inspected plan (default is read-only)}
        {--database= : Exact inspected local database name, mandatory for MySQL writes}
        {--backup= : New private backup basename required for apply}';
    protected $description = 'Preview/repair only the two M8 UK pronoun lessons, without reseeding';

    public function handle(): int
    {
        if (!app()->environment('local', 'testing')) { $this->error('Local/testing environment only.'); return self::FAILURE; }
        $directory = storage_path('app/seo-m8-local');
        if (!is_dir($directory)) { mkdir($directory, 0700, true); }
        $repair = new PronounContentRepair(DB::connection(), database_path(), $directory);
        try {
            $name = $this->option('plan');
            if (!$name || !preg_match('/^[a-zA-Z0-9_-]+\.json$/', $name)) { throw new RuntimeException('--plan requires a simple new .json basename.'); }
            if ($this->option('apply')) {
                $backup = $this->option('backup');
                if (!$backup || !preg_match('/^[a-zA-Z0-9_-]+\.json$/', $backup)) { throw new RuntimeException('--backup requires a simple new .json basename.'); }
                $result = $repair->apply($directory.'/'.$name, $directory.'/'.$backup, $this->option('database'));
            } else {
                $plan = $repair->savePlan($directory.'/'.$name);
                $result = ['status' => 'dry-run', 'connection' => $plan['connection'], 'changes' => count($plan['changes']),
                    'plan' => $directory.'/'.$name, 'sha256' => $plan['sha256']];
            }
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            // No connection strings, SQL bindings, credentials or stack trace in CLI output.
            $this->error($e instanceof RuntimeException && !$e instanceof \PDOException ? $e->getMessage() : 'Repair failed; no successful transaction was committed.');
            return self::FAILURE;
        }
    }
}
