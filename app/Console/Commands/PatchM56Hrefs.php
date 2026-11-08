<?php

namespace App\Console\Commands;

use App\Services\M56HrefCorrectionPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit four-body forward correction only; no HTTP/startup/scheduler entry. */
class PatchM56Hrefs extends Command
{
    protected $signature = 'content:patch-m56-hrefs {--plan=} {--apply} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description = 'Exact M56 1.0.0 to 1.0.1 href-only four-body local correction';

    public function handle(): int
    {
        try {
            if ($this->option('local-target') !== 'gramlyze.loc' || !is_string($this->option('local-proof'))
                || preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $this->option('local-proof')) !== 1) {
                throw new RuntimeException('M56 href correction needs explicit local target and fresh proof.');
            }
            if (!$this->option('apply') && $this->option('backup') !== null) { throw new RuntimeException('Backup is only valid for apply.'); }
            $directory = storage_path('app/seo-m56-local');
            if (!is_dir($directory) || is_link($directory)) { throw new RuntimeException('M56 private directory unavailable.'); }
            $path = static function ($name) use ($directory): string {
                if (!is_string($name) || preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name) !== 1) { throw new RuntimeException('Direct private JSON basename required.'); }
                return $directory.'/'.$name;
            };
            $service = new M56HrefCorrectionPatch(DB::connection(), database_path(), $directory, $this->option('local-target'), $this->option('local-proof'));
            if ($this->option('apply')) {
                $result = $service->apply($path($this->option('plan')), $path($this->option('backup')), $this->option('database'));
            } else {
                $plan = $service->savePlan($path($this->option('plan')));
                $result = ['status' => 'dry-run', 'state' => $plan['state'], 'sha256' => $plan['sha256'],
                    'updated' => count($plan['updates']), 'inserted' => count($plan['inserts']), 'protected_tables' => count($plan['protected'])];
            }
            $this->line(json_encode($result + ['deleted' => 0], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        } catch (\Throwable $error) {
            $this->error($error instanceof RuntimeException && !$error instanceof \PDOException
                ? $error->getMessage() : 'M56 href correction incomplete; no successful transaction committed.');
            return self::FAILURE;
        }
    }
}
