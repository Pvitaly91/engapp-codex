<?php

namespace App\Console\Commands;

use App\Services\M66ContentPatch;
use App\Services\M66FormulaCorrectionPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit CLI preview/apply only; never invoked by HTTP/startup/scheduler. */
class PatchM66Content extends Command
{
    protected $signature = 'content:patch-verb-patterns-core-m66 {--plan=} {--apply} {--backup=} {--database=} {--local-target=} {--local-proof=} {--correction=}';
    protected $description = 'Scoped native M66 preview/apply for three UK bare/comparison/verbs-gerund lessons';

    public function handle(): int
    {
        try {
            if ($this->option('local-target') !== 'gramlyze.loc' || !is_string($this->option('local-proof'))
                || preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $this->option('local-proof')) !== 1) {
                throw new RuntimeException('M66 requires explicit gramlyze.loc and fresh private proof basename.');
            }
            if (!$this->option('apply') && $this->option('backup') !== null) { throw new RuntimeException('M66 backup is valid only for apply.'); }
            $directory = storage_path('app/seo-m66-local');
            if (!is_dir($directory) || is_link($directory)) { throw new RuntimeException('M66 private evidence directory unavailable.'); }
            $path = static function ($name) use ($directory): string {
                if (!is_string($name) || preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name) !== 1) { throw new RuntimeException('M66 needs a direct private JSON basename.'); }
                return $directory.'/'.$name;
            };
            $correction = $this->option('correction');
            if ($correction !== null && $correction !== '1.0.2') { throw new RuntimeException('M66 permits only the reviewed 1.0.2 formula correction.'); }
            $service = $correction === '1.0.2' ? M66FormulaCorrectionPatch::class : M66ContentPatch::class;
            $patch = new $service(DB::connection(), database_path(), $directory, $this->option('local-target'), $this->option('local-proof'));
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
                ? $error->getMessage() : 'M66 incomplete; no successful transaction committed.');
            return self::FAILURE;
        }
    }
}
