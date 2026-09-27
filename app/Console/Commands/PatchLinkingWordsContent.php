<?php

namespace App\Console\Commands;

use App\Services\LinkingWordsContentPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PatchLinkingWordsContent extends Command
{
    protected $signature = 'content:patch-linking-words-m11
        {--plan= : New private preview basename, or inspected preview for apply}
        {--apply : Apply the exact inspected preview}
        {--restore : Restore the exact inspected backup, never reseed}
        {--backup= : New backup basename for apply; existing backup for restore}
        {--database= : Exact inspected local database name, mandatory for MySQL writes}
        {--local-target= : Explicit verified Windows gramlyze.loc opt-in; does not change APP_ENV}
        {--local-proof= : Fresh private loopback web-runtime proof basename for local-target}
        {--only=* : Optional exact M11 seeder basename(s) for independently verified pages}';
    protected $description = 'Preview/apply/restore only three M11 Linking Words UK lessons';

    public function handle(): int
    {
        if (($this->option('apply') || $this->option('restore')) && !app()->environment('local', 'testing') && $this->option('local-target') === null) {
            $this->error('Local/testing environment only; production writes are refused.'); return self::FAILURE;
        }
        $directory = storage_path('app/seo-m11-local');
        if (!is_dir($directory)) { mkdir($directory, 0700, true); }
        $patch = new LinkingWordsContentPatch(DB::connection(), database_path(), $directory,
            $this->option('local-target'), $this->option('local-proof'));
        $path = function ($name) use ($directory): string {
            if (!is_string($name) || !preg_match('/^[a-zA-Z0-9_-]+\.json$/', $name)) { throw new RuntimeException('Use a simple private .json basename.'); }
            return $directory.'/'.$name;
        };
        try {
            if ($this->option('local-proof') !== null && $this->option('local-target') === null) { throw new RuntimeException('Local proof requires explicit local-target.'); }
            if ($this->option('apply') && $this->option('restore')) { throw new RuntimeException('Choose apply or restore, never both.'); }
            if (($this->option('apply') || $this->option('restore')) && $this->option('only')) { throw new RuntimeException('Apply/restore scope comes only from the inspected file.'); }
            if ($this->option('restore')) {
                if ($this->option('plan')) { throw new RuntimeException('Restore uses --backup only.'); }
                $result = $patch->restore($path($this->option('backup')), $this->option('database'));
            } elseif ($this->option('apply')) {
                $result = $patch->apply($path($this->option('plan')), $path($this->option('backup')), $this->option('database'));
            } else {
                $plan = $patch->savePlan($path($this->option('plan')), $this->option('only') ?: LinkingWordsContentPatch::NAMES);
                $result = ['status' => 'dry-run', 'connection' => $plan['connection'], 'changes' => count($plan['changes']),
                    'plan' => $path($this->option('plan')), 'sha256' => $plan['sha256']];
            }
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e instanceof RuntimeException && !$e instanceof \PDOException ? $e->getMessage() : 'M11 patch failed; no successful transaction was committed.');
            return self::FAILURE;
        }
    }
}
