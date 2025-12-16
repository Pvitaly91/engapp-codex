<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\MarkerTheoryMatcherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Artisan command to debug marker theory matching.
 *
 * Usage:
 *   php artisan theory:debug-marker {questionUuid} {marker}
 *   php artisan theory:debug-marker --id=123 a1
 */
class TheoryDebugMarkerCommand extends Command
{
    protected $signature = 'theory:debug-marker
        {questionUuid? : The question UUID}
        {marker? : The marker name (e.g., a1, a2)}
        {--id= : Question ID instead of UUID}
        {--limit=10 : Number of candidates to show}';

    protected $description = 'Debug marker theory matching for a specific question and marker';

    public function handle(MarkerTheoryMatcherService $matcher): int
    {
        $question = $this->resolveQuestion();

        if (! $question) {
            $this->error('Question not found.');

            return self::FAILURE;
        }

        $marker = $this->argument('marker');
        if (! $marker) {
            // Try to detect markers from question text
            $markers = [];
            if (preg_match_all('/\{(a\d+)\}/', $question->question, $matches)) {
                $markers = $matches[1];
            }

            if (empty($markers)) {
                $this->error('No marker specified and no markers found in question.');

                return self::FAILURE;
            }

            $marker = $this->choice('Select a marker:', $markers, 0);
        }

        $limit = (int) $this->option('limit');

        $this->info("📋 Question Details");
        $this->line("   ID: {$question->id}");
        $this->line("   UUID: {$question->uuid}");
        $this->line("   Text: ".Str::limit($question->question, 100));
        $this->line("   Level: {$question->level}");
        $this->line('');

        $this->info("🏷️ Marker: {$marker}");

        // Get marker tags
        $markerTags = $matcher->getMarkerTags($question->id, $marker);

        if ($markerTags->isEmpty()) {
            $this->warn('   No marker tags found for this marker.');
            $this->line('');

            // Show question-level tags for comparison
            if ($question->tags->isNotEmpty()) {
                $this->info('📌 Question-level tags:');
                foreach ($question->tags as $tag) {
                    $this->line("   - {$tag->name}");
                }
            }

            return self::SUCCESS;
        }

        $this->line('   Tags: '.implode(', ', $markerTags->pluck('name')->toArray()));
        $this->line('');

        // Get debug matches
        $result = $matcher->debugMatches($question->id, $marker, $limit);

        if (! $result) {
            $this->warn('❌ No matching theory blocks found.');
            $this->line('');
            $this->info('Possible reasons:');
            $this->line('   1. No TextBlocks have tags that match the marker tags');
            $this->line('   2. All matches were filtered out due to low score (general tags only)');
            $this->line('   3. Marker tags are too general to match specific theory blocks');

            return self::SUCCESS;
        }

        $this->info("✅ Best Match");
        $this->displayCandidate($result, true);
        $this->line('');

        // Display candidates
        if (! empty($result['candidates'])) {
            $this->info("📊 Top {$limit} Candidates");
            $this->line('');

            foreach ($result['candidates'] as $index => $candidate) {
                $this->displayCandidate($candidate, false, $index + 1);
            }
        }

        // Analysis section
        $this->line('');
        $this->info('🔍 Analysis');
        $this->analyzeMatch($markerTags->pluck('name')->toArray(), $result);

        return self::SUCCESS;
    }

    private function resolveQuestion(): ?Question
    {
        if ($id = $this->option('id')) {
            return Question::find($id);
        }

        $uuid = $this->argument('questionUuid');
        if ($uuid) {
            return Question::where('uuid', $uuid)->first();
        }

        // Interactive selection
        $this->info('No question specified. Please provide a question UUID or ID.');

        return null;
    }

    private function displayCandidate(array $candidate, bool $isBest = false, ?int $rank = null): void
    {
        $block = $candidate['block'];
        $prefix = $rank ? "#{$rank}" : ($isBest ? '🏆' : '');

        $this->line("{$prefix} Block: {$block->uuid}");
        $this->line("   Heading: ".($block->heading ?? '(no heading)'));
        $this->line("   Type: {$block->type}");
        $this->line("   Level: ".($block->level ?? 'N/A'));
        $this->line("   Score: {$candidate['score']}");
        $this->line("   Detailed matches: {$candidate['detailed_matches']}");
        $this->line("   General matches: {$candidate['general_matches']}");
        $this->line("   Matched tags: ".implode(', ', $candidate['matched_tags']));

        // Show page context if available
        if ($block->page) {
            $this->line("   Page: {$block->page->title} (slug: {$block->page->slug})");
        }

        $this->line('');
    }

    private function analyzeMatch(array $markerTags, array $result): void
    {
        $matchedTags = $result['matched_tags'] ?? [];
        $unmatchedTags = array_diff($markerTags, $matchedTags);

        if (! empty($unmatchedTags)) {
            $this->line("   Unmatched marker tags: ".implode(', ', $unmatchedTags));
            $this->line("   Consider adding these tags to theory blocks for better matching.");
        }

        // Check if best match might be an intro block
        $block = $result['block'];
        $isIntroType = in_array($block->type, ['subtitle', 'hero'], true);
        $hasIntroTag = in_array('introduction', array_map('strtolower', $matchedTags), true)
            || in_array('overview', array_map('strtolower', $matchedTags), true);

        if ($isIntroType || $hasIntroTag) {
            $this->warn('   ⚠️ Best match appears to be an intro/overview block.');
            $this->line('   This may indicate marker tags are too general or theory blocks');
            $this->line('   need more specific tags.');
        }

        // Check detailed match ratio
        $detailedRatio = $result['detailed_matches'] / max(1, count($matchedTags));
        if ($detailedRatio < 0.5) {
            $this->warn('   ⚠️ Low detailed match ratio ('.round($detailedRatio * 100).'%).');
            $this->line('   Consider adding more specific tags to marker or theory blocks.');
        }
    }
}
