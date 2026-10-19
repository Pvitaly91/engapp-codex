<?php

namespace App\Console\Commands;

use App\Services\M51ContentPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit preview/apply entry only; no HTTP/startup/scheduler integration. */
class PatchM51Content extends Command
{
    protected $signature = 'content:patch-modal-meanings-m51 {--plan=} {--apply} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description = 'Scoped native M51 preview/apply for three existing UK theory owners';

    public function handle(): int
    {
        try {
            if ($this->option('local-proof') !== null && $this->option('local-target') === null) {
                throw new RuntimeException('M51 proof requires explicit local target.');
            }
            if (!$this->option('apply') && $this->option('backup') !== null) {
                throw new RuntimeException('M51 backup is only valid for explicit apply.');
            }
            $directory = storage_path('app/seo-m51-local');
            if (is_link($directory)) { throw new RuntimeException('M51 private evidence directory is linked.'); }
            if (!is_dir($directory) && !mkdir($directory, 0700, true)) { throw new RuntimeException('M51 private evidence directory unavailable.'); }
            $path = static function ($name) use ($directory): string {
                if (!is_string($name) || preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name) !== 1) {
                    throw new RuntimeException('M51 evidence requires a private JSON basename.');
                }

                return $directory.'/'.$name;
            };
            $patch = new M51ContentPatch(DB::connection(), database_path(), $directory,
                $this->option('local-target'), $this->option('local-proof'));
            if ($this->option('apply')) {
                $result = $patch->apply($path($this->option('plan')), $path($this->option('backup')), $this->option('database'));
                $result['questions_inserted'] = $result['status'] === 'applied' ? 15 : 0;
            } else {
                $plan = $patch->savePlan($path($this->option('plan')));
                $result = ['status' => 'dry-run', 'connection' => $plan['connection'], 'state' => $plan['state'],
                    'updated' => count($plan['updates']), 'inserted' => count($plan['inserts']), 'deleted' => 0,
                    'question_rows' => array_map('count', $plan['question_inserts']),
                    'protected_tables' => count($plan['protected']), 'sha256' => $plan['sha256']];
            }
            $this->line(json_encode($result + ['deleted' => 0], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        } catch (\Throwable $error) {
            $this->error($error instanceof RuntimeException && !$error instanceof \PDOException
                ? $error->getMessage() : 'M51 did not complete; no successful transaction committed.');

            return self::FAILURE;
        }
    }
}
