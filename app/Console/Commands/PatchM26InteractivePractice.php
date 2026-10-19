<?php

namespace App\Console\Commands;

use App\Services\M26InteractivePracticePatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PatchM26InteractivePractice extends Command
{
    protected $signature = 'content:patch-ppc-interactive-m26
        {--plan= : New private preview basename, or inspected preview for apply}
        {--apply : Apply the exact inspected preview}
        {--restore : Restore the exact inspected backup}
        {--backup= : New exclusive backup basename for apply, existing backup for restore}
        {--database= : Exact inspected local database name for MySQL writes}
        {--local-target= : Verified Windows gramlyze.loc opt-in without changing APP_ENV}
        {--local-proof= : Fresh private loopback web-runtime proof basename}';

    protected $description = 'Preview/apply/restore four existing M26 practice rows without bank writes';

    public function handle(): int
    {
        if (($this->option('apply') || $this->option('restore')) && !app()->environment('local', 'testing')
            && $this->option('local-target') === null) {
            $this->error('Production-profile M26 writes require verified gramlyze.loc local-target.');
            return self::FAILURE;
        }
        $directory = storage_path('app/seo-m26-local');
        if (!is_dir($directory)) { mkdir($directory, 0700, true); }
        $patch = new M26InteractivePracticePatch(DB::connection(), database_path(), $directory,
            $this->option('local-target'), $this->option('local-proof'));
        $path = static function ($name) use ($directory): string {
            if (!is_string($name) || !preg_match('/^[a-zA-Z0-9_-]+\.json$/', $name)) {
                throw new RuntimeException('Use a simple private M26 .json basename.');
            }
            return $directory.'/'.$name;
        };
        try {
            if ($this->option('local-proof') !== null && $this->option('local-target') === null) {
                throw new RuntimeException('M26 local proof requires explicit local-target.');
            }
            if ($this->option('apply') && $this->option('restore')) {
                throw new RuntimeException('Choose M26 apply or restore, never both.');
            }
            if ($this->option('restore')) {
                if ($this->option('plan')) { throw new RuntimeException('M26 restore uses --backup only.'); }
                $result = $patch->restore($path($this->option('backup')), $this->option('database'));
            } elseif ($this->option('apply')) {
                $result = $patch->apply($path($this->option('plan')), $path($this->option('backup')), $this->option('database'));
            } else {
                $plan = $patch->savePlan($path($this->option('plan')));
                $result = ['status' => 'dry-run', 'connection' => $plan['connection'], 'state' => $plan['state'],
                    'updates' => count($plan['updates']), 'inserts' => count($plan['inserts']),
                    'plan' => $path($this->option('plan')), 'sha256' => $plan['sha256']];
            }
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        } catch (\Throwable $error) {
            $this->error($error instanceof RuntimeException && !$error instanceof \PDOException
                ? $error->getMessage() : 'M26 interactive patch failed; no successful transaction was committed.');
            return self::FAILURE;
        }
    }
}
