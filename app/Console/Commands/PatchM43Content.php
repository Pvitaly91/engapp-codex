<?php

namespace App\Console\Commands;

use App\Services\M43ContentPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PatchM43Content extends Command
{
    protected $signature = 'content:patch-authored-tense-usage-m43 {--plan=} {--apply} {--restore} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description = 'Explicit finite M43 preview/apply/restore for exactly three existing UK owners; never seeds';

    public function handle(): int
    {
        try {
            if ($this->option('apply') && $this->option('restore')) { throw new RuntimeException('Choose M43 apply or restore, never both.'); }
            if ($this->option('local-proof') !== null && $this->option('local-target') === null) { throw new RuntimeException('M43 proof requires explicit local target.'); }
            if ($this->option('restore') && $this->option('plan')) { throw new RuntimeException('M43 restore uses --backup only.'); }
            $directory = storage_path('app/seo-m43-local');
            if (is_link($directory)) { throw new RuntimeException('M43 evidence directory must not be linked.'); }
            if (!is_dir($directory) && !mkdir($directory, 0700, true)) { throw new RuntimeException('M43 private directory unavailable.'); }
            $path = static function ($name) use ($directory): string {
                if (!is_string($name) || !preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name)) { throw new RuntimeException('Use a private M43 JSON basename.'); }
                return $directory.'/'.$name;
            };
            $patch = new M43ContentPatch(DB::connection(), database_path(), $directory, $this->option('local-target'), $this->option('local-proof'));
            if ($this->option('restore')) { $result = $patch->restore($path($this->option('backup')), $this->option('database')); }
            elseif ($this->option('apply')) { $result = $patch->apply($path($this->option('plan')), $path($this->option('backup')), $this->option('database')); }
            else {
                $plan = $patch->savePlan($path($this->option('plan')));
                $result = ['status' => 'dry-run', 'connection' => $plan['connection'], 'state' => $plan['state'],
                    'updated' => count($plan['updates']), 'inserted' => count($plan['inserts']), 'deleted' => 0,
                    'protected_tables' => count($plan['protected']), 'sha256' => $plan['sha256']];
            }
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        } catch (\Throwable $error) {
            $this->error($error instanceof RuntimeException && !$error instanceof \PDOException
                ? $error->getMessage() : 'M43 failed; no successful transaction committed.');
            return self::FAILURE;
        }
    }
}
