<?php

namespace App\Console\Commands;

use App\Services\PassiveVoiceDebugContentRepair;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RepairPassiveVoiceDebugContent extends Command
{
    protected $signature = 'seo:repair-passive-voice-debug
        {--dry-run : Inspect only (the default)}
        {--apply : Apply the exact repair or restoration on the confirmed local database}
        {--database= : Expected local database name, required for MySQL writes}
        {--backup= : New local JSON record-backup path, required when applying a repair}
        {--restore= : Inspect or restore a backup created by this command}';

    protected $description = 'Safely repair the identified Passive Voice fixture contamination, with an obligatory record backup.';

    public function handle(): int
    {
        try {
            if ($this->option('apply') && $this->option('dry-run')) {
                throw new \RuntimeException('Choose either --apply or --dry-run.');
            }
            $repair = new PassiveVoiceDebugContentRepair(DB::connection(), database_path(PassiveVoiceDebugContentRepair::DEFINITION));
            if ($this->option('restore')) {
                $result = $repair->restore($this->option('restore'), (bool) $this->option('apply'), $this->option('database'));
            } elseif ($this->option('apply')) {
                if (! $this->option('backup')) {
                    throw new \RuntimeException('--backup must name a new local record-backup file before applying the repair.');
                }
                $result = $repair->apply($this->option('backup'), $this->option('database'));
            } else {
                $result = $repair->plan();
            }
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return empty($result['conflicts']) ? self::SUCCESS : self::FAILURE;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
