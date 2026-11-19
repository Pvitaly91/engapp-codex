<?php

namespace App\Console\Commands;

use App\Services\PracticeCompletionLocalTargetGuard as Guard;
use App\Services\PracticeCompletionPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit CLI delivery only; never a seeder, HTTP/startup hook or scheduler. */
class PatchPracticeCompletion extends Command
{
    protected $signature = 'content:complete-practice-m46-m49 {--plan=} {--apply} {--backup=} {--database=}
        {--local-target=} {--local-proof=}';
    protected $description = 'Preview/apply exactly twelve existing UK practice bodies with two supplemental inputs each.';

    public function handle(): int
    {
        $target = $this->option('local-target'); $proof = $this->option('local-proof');
        $dir = $target !== null ? Guard::privateDirectory() : storage_path('app/practice-completion-fixtures');
        if (is_link($dir) || (!is_dir($dir) && !mkdir($dir, 0700, true))) {
            throw new RuntimeException('Practice completion private directory unavailable.');
        }
        $name = $this->option('plan');
        if (!is_string($name) || !preg_match('/^[a-zA-Z0-9_-]+\\.json$/D', $name)) {
            throw new RuntimeException('Practice completion requires a direct private plan basename.');
        }
        $db = DB::connection(); $patch = new PracticeCompletionPatch($db, database_path(), $dir, $target, $proof);
        if ($this->option('apply')) {
            $backup = $this->option('backup');
            if (!is_string($backup) || !preg_match('/^[a-zA-Z0-9_-]+\\.json$/D', $backup)) {
                throw new RuntimeException('Practice completion apply requires an exclusive backup basename.');
            }
            $out = $patch->apply($dir.'/'.$name, $dir.'/'.$backup, $this->option('database'));
        } else {
            $out = $patch->savePlan($dir.'/'.$name);
            $out = ['status' => 'dry-run', 'state' => $out['state'], 'updated' => count($out['updates']),
                'inserted' => count($out['inserts']), 'deleted' => 0, 'protected_tables' => count($out['protected']),
                'sha256' => $out['sha256']];
        }
        $out['deleted'] = 0;
        $this->line(json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        return self::SUCCESS;
    }
}
