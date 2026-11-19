<?php

namespace App\Console\Commands;

use App\Services\M52ContentPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit local preview/apply only; never registered as an HTTP/startup/scheduler apply. */
class PatchM52Content extends Command
{
    protected $signature = 'content:patch-passive-basics-m52 {--plan=} {--apply} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description = 'Scoped native M52 preview/apply for three existing UK Passive Basics owners';

    public function handle(): int
    {
        try {
            if ($this->option('local-target') !== 'gramlyze.loc'
                || !is_string($this->option('local-proof'))
                || preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $this->option('local-proof')) !== 1) {
                throw new RuntimeException('M52 command requires explicit gramlyze.loc and a fresh private proof basename.');
            }
            if (!$this->option('apply') && $this->option('backup') !== null) {
                throw new RuntimeException('M52 backup is only valid for explicit apply.');
            }
            $directory = storage_path('app/seo-m52-local');
            if (!is_dir($directory) || is_link($directory)) { throw new RuntimeException('M52 reviewed private evidence directory is unavailable.'); }
            $path = static function ($name) use ($directory): string {
                if (!is_string($name) || preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name) !== 1) {
                    throw new RuntimeException('M52 evidence requires a direct private JSON basename.');
                }

                return $directory.'/'.$name;
            };
            $patch = new M52ContentPatch(DB::connection(), database_path(), $directory,
                $this->option('local-target'), $this->option('local-proof'));
            if ($this->option('apply')) {
                $result = $patch->apply($path($this->option('plan')), $path($this->option('backup')), $this->option('database'));
            } else {
                $plan = $patch->savePlan($path($this->option('plan')));
                $result = ['status' => 'dry-run', 'connection' => $plan['connection'], 'state' => $plan['state'],
                    'updated' => count($plan['updates']), 'inserted' => count($plan['inserts']),
                    'protected_tables' => count($plan['protected']), 'sha256' => $plan['sha256']];
            }
            $this->line(json_encode($result + ['deleted' => 0, 'questions_inserted' => 0,
                'options_inserted' => 0, 'ordered_answers_inserted' => 0, 'links_inserted' => 0],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        } catch (\Throwable $error) {
            $this->error($error instanceof RuntimeException && !$error instanceof \PDOException
                ? $error->getMessage() : 'M52 did not complete; no successful transaction committed.');

            return self::FAILURE;
        }
    }
}
