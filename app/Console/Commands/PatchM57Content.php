<?php

namespace App\Console\Commands;

use App\Services\M57ContentPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit CLI preview/apply only; never invoked by HTTP/startup/scheduler. */
class PatchM57Content extends Command
{
    protected $signature = 'content:patch-continuous-perfect-passives-m57 {--plan=} {--apply} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description = 'Scoped native M57 preview/apply for three UK continuous/perfect-passive lessons';

    public function handle(): int
    {
        try {
            if ($this->option('local-target') !== 'gramlyze.loc' || !is_string($this->option('local-proof'))
                || preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $this->option('local-proof')) !== 1) {
                throw new RuntimeException('M57 requires explicit gramlyze.loc and fresh private proof basename.');
            }
            if (!$this->option('apply') && $this->option('backup') !== null) { throw new RuntimeException('M57 backup is valid only for apply.'); }
            $directory = storage_path('app/seo-m57-local');
            if (!is_dir($directory) || is_link($directory)) { throw new RuntimeException('M57 private evidence directory unavailable.'); }
            $path = static function ($name) use ($directory): string {
                if (!is_string($name) || preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name) !== 1) { throw new RuntimeException('M57 needs a direct private JSON basename.'); }
                return $directory.'/'.$name;
            };
            $patch = new M57ContentPatch(DB::connection(), database_path(), $directory, $this->option('local-target'), $this->option('local-proof'));
            if ($this->option('apply')) {
                $result = $patch->apply($path($this->option('plan')), $path($this->option('backup')), $this->option('database'));
            } else {
                $plan = $patch->savePlan($path($this->option('plan')));
                $result = ['status' => 'dry-run', 'connection' => $plan['connection'], 'state' => $plan['state'],
                    'updated' => count($plan['updates']), 'inserted' => count($plan['inserts']),
                    'protected_tables' => count($plan['protected']), 'sha256' => $plan['sha256']];
            }
            $this->line(json_encode($result + ['deleted' => 0], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        } catch (\Throwable $error) {
            $this->error($error instanceof RuntimeException && !$error instanceof \PDOException
                ? $error->getMessage() : 'M57 incomplete; no successful transaction committed.');
            return self::FAILURE;
        }
    }
}
