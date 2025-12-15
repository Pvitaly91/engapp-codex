<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\TextBlock;
use Illuminate\Console\Command;

/**
 * Artisan command to verify BLOCK-FIRST TAGGING implementation for target pages.
 *
 * This command verifies:
 * 1. Page tags are a superset of all block tags (page = union of blocks + anchors)
 * 2. At least one TextBlock has non-empty tags
 * 3. Not all blocks have identical tag sets (diversity check)
 * 4. Generates a report for each target page
 */
class VerifyBlockFirstTagsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tags:verify-block-first
                            {--page= : Specific page slug to verify}
                            {--seeder= : Filter pages by seeder class name (partial match)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify BLOCK-FIRST tagging for target theory pages';

    /**
     * Target seeder classes to verify by default.
     */
    protected array $targetSeeders = [
        'TypesOfQuestionsYesNoQuestionsGeneralQuestionsTheorySeeder',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 BLOCK-FIRST Tag Verification');
        $this->line('');

        $pages = $this->getTargetPages();

        if ($pages->isEmpty()) {
            $this->warn('No target pages found.');

            return Command::FAILURE;
        }

        $this->info("Found {$pages->count()} target page(s)");
        $this->line('');

        $allPassed = true;

        foreach ($pages as $page) {
            $passed = $this->verifyPage($page);
            if (! $passed) {
                $allPassed = false;
            }
            $this->line('');
        }

        if ($allPassed) {
            $this->info('✅ All verifications passed!');

            return Command::SUCCESS;
        }

        $this->error('❌ Some verifications failed.');

        return Command::FAILURE;
    }

    /**
     * Get target pages based on command options or default target seeders.
     */
    protected function getTargetPages()
    {
        $query = Page::with(['tags', 'textBlocks.tags']);

        if ($slug = $this->option('page')) {
            $query->where('slug', $slug);
        } elseif ($seeder = $this->option('seeder')) {
            $query->where('seeder', 'like', "%{$seeder}%");
        } else {
            // Default: target seeders
            $query->where(function ($q) {
                foreach ($this->targetSeeders as $seeder) {
                    $q->orWhere('seeder', 'like', "%{$seeder}%");
                }
            });
        }

        return $query->get();
    }

    /**
     * Verify a single page and its blocks for BLOCK-FIRST tagging compliance.
     */
    protected function verifyPage(Page $page): bool
    {
        $this->info("📄 Page: {$page->title}");
        $this->line("   Slug: {$page->slug}");
        $this->line("   Seeder: {$page->seeder}");

        $pageTagIds = $page->tags->pluck('id')->toArray();
        $pageTagNames = $page->tags->pluck('name')->toArray();

        $this->line('   Page tags ('.$page->tags->count().'): '.$this->formatTagList($pageTagNames, 8));

        $blocks = $page->textBlocks;
        $this->line("   Text blocks: {$blocks->count()}");

        $allBlockTagIds = [];
        $blockTagSets = [];
        $blocksWithTags = 0;

        foreach ($blocks as $block) {
            $blockTagIds = $block->tags->pluck('id')->toArray();
            $allBlockTagIds = array_unique(array_merge($allBlockTagIds, $blockTagIds));

            if (! empty($blockTagIds)) {
                $blocksWithTags++;
                $blockTagSets[] = $blockTagIds;
            }
        }

        $passed = true;
        $checks = [];

        // Check 1: Page tags contain all block tags (superset)
        $missingInPage = array_diff($allBlockTagIds, $pageTagIds);
        if (empty($missingInPage)) {
            $checks[] = ['✅', 'Page tags are superset of all block tags'];
        } else {
            $checks[] = ['❌', 'Page missing '.count($missingInPage).' tag(s) from blocks'];
            $passed = false;
        }

        // Check 2: At least one block has non-empty tags
        if ($blocksWithTags > 0) {
            $checks[] = ['✅', "{$blocksWithTags} block(s) have non-empty tags"];
        } else {
            $checks[] = ['❌', 'No blocks have tags'];
            $passed = false;
        }

        // Check 3: Not all blocks have identical tags (diversity)
        $diversityCheck = $this->checkTagSetDiversity($blockTagSets);
        $checks[] = $diversityCheck;

        foreach ($checks as $check) {
            $this->line("   {$check[0]} {$check[1]}");
        }

        // Top tags report
        $tagCounts = [];
        foreach ($blocks as $block) {
            foreach ($block->tags as $tag) {
                $tagCounts[$tag->name] = ($tagCounts[$tag->name] ?? 0) + 1;
            }
        }
        arsort($tagCounts);
        $topTags = array_slice($tagCounts, 0, 5, true);

        if (! empty($topTags)) {
            $this->line('   📊 Top tags in blocks:');
            foreach ($topTags as $tagName => $count) {
                $this->line("      - {$tagName}: {$count} block(s)");
            }
        }

        return $passed;
    }

    /**
     * Format a list of tag names for display, with optional truncation.
     */
    protected function formatTagList(array $tagNames, int $maxShow = 8): string
    {
        $shown = array_slice($tagNames, 0, $maxShow);
        $result = implode(', ', $shown);

        if (count($tagNames) > $maxShow) {
            $result .= '...';
        }

        return $result;
    }

    /**
     * Check tag set diversity and return a check result.
     *
     * @param  array<array<int>>  $blockTagSets
     * @return array{0: string, 1: string}
     */
    protected function checkTagSetDiversity(array $blockTagSets): array
    {
        if (count($blockTagSets) <= 1) {
            return ['ℹ️', 'Only one block has tags (diversity check skipped)'];
        }

        $uniqueSets = array_unique(array_map(function ($set) {
            sort($set);

            return implode(',', $set);
        }, $blockTagSets));

        if (count($uniqueSets) > 1) {
            return ['✅', 'Blocks have diverse tag sets ('.count($uniqueSets).' unique sets)'];
        }

        return ['⚠️', 'All blocks with tags have identical tag sets'];
    }
}
