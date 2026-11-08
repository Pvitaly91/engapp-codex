<?php

namespace App\Console\Commands;

use App\Services\M49ContentPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PatchM49Content extends Command
{
    protected $signature = 'content:patch-advice-modals-m49 {--plan=} {--apply} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description = 'Explicit finite M49 content-only preview/apply for three UK advice/modal lessons; never seeds';

    public function handle(): int
    {
        try {
            if ($this->option('local-proof') !== null && $this->option('local-target') === null) {
                throw new RuntimeException('M49 proof requires explicit local target.');
            }
            if (!$this->option('apply') && $this->option('backup') !== null) {
                throw new RuntimeException('M49 backup is only used with explicit apply.');
            }
            $directory = storage_path('app/seo-m49-local');
            if (is_link($directory)) { throw new RuntimeException('M49 evidence directory must not be linked.'); }
            if (!is_dir($directory) && !mkdir($directory, 0700, true)) {
                throw new RuntimeException('M49 private evidence directory unavailable.');
            }
            $path = static function ($name) use ($directory): string {
                if (!is_string($name) || !preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name)) {
                    throw new RuntimeException('Use a private M49 JSON basename.');
                }
                return $directory.'/'.$name;
            };
            $patch = new M49ContentPatch(DB::connection(), database_path(), $directory,
                $this->option('local-target'), $this->option('local-proof'));
            if ($this->option('apply')) {
                $result = $patch->apply($path($this->option('plan')), $path($this->option('backup')), $this->option('database'));
            } else {
                $plan = $patch->savePlan($path($this->option('plan')));
                $result = [
                    'status' => 'dry-run', 'connection' => $plan['connection'], 'state' => $plan['state'],
                    'updated' => count($plan['updates']), 'inserted' => count($plan['inserts']), 'deleted' => 0,
                    'protected_tables' => count($plan['protected']), 'sha256' => $plan['sha256'],
                ];
            }
            $result += ['deleted' => 0];
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        } catch (\Throwable $error) {
            $this->error($error instanceof RuntimeException && !$error instanceof \PDOException
                ? $error->getMessage() : 'M49 failed; no successful transaction committed.');
            return self::FAILURE;
        }
    }
}
