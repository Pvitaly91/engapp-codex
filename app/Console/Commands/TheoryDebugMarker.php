<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\MarkerTheoryMatcherService;
use Illuminate\Console\Command;

class TheoryDebugMarker extends Command
{
    protected $signature = 'theory:debug-marker {questionUuid} {marker} {--limit=10}';

    protected $description = 'Debug theory matching for a specific marker';

    public function handle(MarkerTheoryMatcherService $matcher): int
    {
        $question = Question::where('uuid', $this->argument('questionUuid'))->first();

        if (! $question) {
            $this->error('Question not found');

            return self::FAILURE;
        }

        $marker = $this->argument('marker');
        $limit = (int) $this->option('limit');

        $this->info("Question: {$question->uuid} | Marker: {$marker}");

        $markerTags = $matcher->getMarkerTags($question->id, $marker)->pluck('name')->all();

        if (empty($markerTags)) {
            $this->warn('No marker tags found');

            return self::SUCCESS;
        }

        $this->line('Marker tags: '.implode(', ', $markerTags));

        $debug = $matcher->debugMatches($question->id, $marker, $limit);

        if (empty($debug['candidates'] ?? [])) {
            $this->warn('No candidates matched');

            return self::SUCCESS;
        }

        foreach ($debug['candidates'] as $index => $candidate) {
            $block = $candidate['block'];
            $matchedTags = implode(', ', $candidate['matched_tags']);
            $this->line(sprintf(
                '%d) %s | Page: %s | Score: %s | Matched: %s',
                $index + 1,
                $block->uuid,
                $block->page?->title ?? 'N/A',
                $candidate['score'],
                $matchedTags
            ));
        }

        $winner = $debug['block'];
        $this->info('Winning block: '.$winner->uuid);

        return self::SUCCESS;
    }
}
