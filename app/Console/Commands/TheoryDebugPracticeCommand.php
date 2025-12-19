<?php

namespace App\Console\Commands;

use App\Models\TextBlock;
use App\Services\Theory\TextBlockToQuestionsMatcherService;
use Illuminate\Console\Command;

class TheoryDebugPracticeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theory:debug-practice {textBlockUuid : The UUID of the text block to analyze}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug practice question matching for a text block by UUID';

    public function __construct(
        private TextBlockToQuestionsMatcherService $matcherService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $uuid = $this->argument('textBlockUuid');

        $block = TextBlock::with('tags')->where('uuid', $uuid)->first();

        if (! $block) {
            $this->error("Text block with UUID '{$uuid}' not found.");

            return Command::FAILURE;
        }

        $this->info("=== Text Block Debug Info ===\n");
        $this->line("UUID: {$block->uuid}");
        $this->line("Heading: " . ($block->heading ?: '(none)'));
        $this->line("Type: {$block->type}");
        $this->line("Level: " . ($block->level ?: '(none)'));

        $diagnostics = $this->matcherService->getMatchingDiagnostics($block, 10);

        $this->newLine();
        $this->info("=== All Block Tags ===");
        if (empty($diagnostics['all_block_tags'])) {
            $this->warn("No tags found on this block.");
        } else {
            foreach ($diagnostics['all_block_tags'] as $tag) {
                $this->line("  • {$tag}");
            }
        }

        $this->newLine();
        $this->info("=== Tag Classification ===");

        $this->line("Anchor Tags (theme/type):");
        if (empty($diagnostics['anchor_tags'])) {
            $this->warn("  (none)");
        } else {
            foreach ($diagnostics['anchor_tags'] as $tag) {
                $this->line("  • {$tag}");
            }
        }

        $this->line("Detail Tags (grammar features):");
        if (empty($diagnostics['detail_tags'])) {
            $this->warn("  (none)");
        } else {
            foreach ($diagnostics['detail_tags'] as $tag) {
                $this->line("  • {$tag}");
            }
        }

        $this->newLine();
        $this->info("=== Matching Statistics ===");
        $this->line("Candidates after anchor tag filter: {$diagnostics['candidates_after_anchors']}");
        $this->line("Candidates after detail tag filter: {$diagnostics['candidates_after_details']}");

        $this->newLine();
        $this->info("=== Top 10 Matching Questions ===");

        if (empty($diagnostics['top_questions'])) {
            $this->warn("No matching questions found.");

            if (empty($diagnostics['anchor_tags'])) {
                $this->comment("→ The block has no anchor tags. Add tags like 'Types of Questions', 'Yes/No Questions', etc.");
            } elseif (! empty($diagnostics['detail_tags']) && $diagnostics['candidates_after_details'] === 0) {
                $this->comment("→ No questions match both the anchor AND detail tags. Questions may lack the required detail tags.");
            } elseif ($diagnostics['candidates_after_anchors'] === 0) {
                $this->comment("→ No questions have the required anchor tags. Check if questions are tagged correctly.");
            }

            return Command::SUCCESS;
        }

        $headers = ['#', 'ID', 'Question (truncated)', 'Score', 'Matched Tags'];
        $rows = [];

        foreach ($diagnostics['top_questions'] as $index => $q) {
            $rows[] = [
                $index + 1,
                $q['id'],
                $q['question'],
                $q['score'],
                implode(', ', array_slice($q['matched_tags'], 0, 5)) .
                    (count($q['matched_tags']) > 5 ? '...' : ''),
            ];
        }

        $this->table($headers, $rows);

        return Command::SUCCESS;
    }
}
