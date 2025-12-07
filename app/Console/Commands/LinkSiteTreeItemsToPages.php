<?php

namespace App\Console\Commands;

use App\Services\SiteTreeLinkingService;
use Illuminate\Console\Command;

class LinkSiteTreeItemsToPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site-tree:link-pages {--variant-id= : Variant ID to link (default: base variant)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link site tree items to pages by seeder name or slug';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $variantId = $this->option('variant-id');
        
        $this->info('🔗 Linking site tree items to pages...');
        
        try {
            $linkingService = new SiteTreeLinkingService();
            $result = $linkingService->linkItemsToPages($variantId);
            
            $this->info("Found " . count($linkingService->getPageUrlMap()) . " pages with URLs");
            $this->info("Found {$result['total']} site tree items");
            
            $this->info("✅ Linking complete!");
            $this->info("   - New links created: {$result['linked']}");
            $this->info("   - Existing links updated: {$result['updated']}");
            $this->info("   - Items without matches: {$result['skipped']}");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
