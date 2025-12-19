<?php

namespace App\Console\Commands;

use App\Models\TextBlock;
use App\Services\Theory\TextBlockToQuestionsMatcherService;
use Illuminate\Console\Command;

class TheoryDebugPracticeCommand extends Command
{
    protected $signature = 'theory:debug-practice {textBlockUuid} {--limit=10}';

    protected $description = 'Debug practice question matching for a theory text block';

    public function __construct(private TextBlockToQuestionsMatcherService $matcherService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $uuid = $this->argument('textBlockUuid');
        $limit = (int) $this->option('limit');

        $block = TextBlock::query()
            ->where('uuid', $uuid)
            ->with('tags')
            ->first();

        if (! $block) {
            $this->error('Text block not found');

            return self::FAILURE;
        }

        $data = $this->matcherService->debugMatches($block, $limit);

        $this->info('Anchor tags: '.($data['anchor_tags'] ? implode(', ', $data['anchor_tags']) : '—'));
        $this->info('Detail tags: '.($data['detail_tags'] ? implode(', ', $data['detail_tags']) : '—'));
        $this->line('Candidates after anchors: '.$data['candidates_after_anchors']);
        $this->line('Candidates after details: '.$data['candidates_after_details']);

        if ($data['questions']->isEmpty()) {
            $this->warn('No matching questions found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Question ID', 'Score', 'Matched tags'],
            $data['questions']->map(fn ($q) => [
                $q['id'],
                $q['score'],
                implode(', ', $q['matched_tags'] ?? []),
            ])->toArray()
        );

        return self::SUCCESS;
    }
}

