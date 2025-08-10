<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WordScanningService;

class ScanWordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'words:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan questions and options and store unique words';

    /**
     * Execute the console command.
     */
    public function handle(WordScanningService $service): int
    {
        $count = $service->scan();
        $this->info("Inserted {$count} new words.");
        return Command::SUCCESS;
    }
}
