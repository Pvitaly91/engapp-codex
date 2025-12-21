<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\MarkerTheoryMatcherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MarkerTheoryAuditCommand extends Command
{
    protected $signature = 'theory:marker-audit
        {--seeder=Database\\Seeders\\Ai\\Claude\\QuestionsDifferentTypesClaudeSeeder : Question seeder class to audit}
        {--limit=0 : Limit number of questions}
        {--min-score=4 : Minimum score to consider a match stable}
    ';

    protected $description = 'Audit marker theory matches and report weak or missing mappings';

    private const THEORY_SEEDERS = [
        'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsYesNoQuestionsGeneralQuestionsTheorySeeder',
        'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsWhQuestionsSpecialQuestionsTheorySeeder',
        'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsQuestionTagsDisjunctiveQuestionsTheorySeeder',
        'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsAlternativeQuestionsTheorySeeder',
        'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsNegativeQuestionsTheorySeeder',
        'Database\\Seeders\\Page_v2\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsAnswersToQuestionsTheorySeeder',
    ];

    public function handle(MarkerTheoryMatcherService $matcher): int
    {
        $seederClass = $this->option('seeder');
        $limit = (int) $this->option('limit');
        $minScore = (float) $this->option('min-score');

        $this->info("Preparing data using {$seederClass} ...");

        $this->seedClass($seederClass);
        foreach (self::THEORY_SEEDERS as $class) {
            $this->seedClass($class);
        }

        $query = Question::query();

        if (Schema::hasColumn('questions', 'seeder')) {
            $query->where('seeder', $seederClass);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $questions = $query->with('answers')->get();

        $failures = [];
        $totalMarkers = 0;

        foreach ($questions as $question) {
            $markers = $question->answers->pluck('marker')->unique();

            foreach ($markers as $marker) {
                $totalMarkers++;
                $result = $matcher->debugMatches($question->id, $marker, 3);
                $markerTags = $matcher->getMarkerTags($question->id, $marker)->pluck('name')->all();

                if ($result === null || ($result['score'] ?? 0) < $minScore) {
                    $failures[] = [
                        'question_id' => $question->id,
                        'marker' => $marker,
                        'question' => Str::limit($question->question, 140),
                        'marker_tags' => $markerTags,
                        'best' => $result ? $this->formatCandidate($result) : null,
                        'candidates' => $result['candidates'] ?? [],
                        'reason' => $this->inferReason($markerTags, $result),
                    ];
                }
            }
        }

        $this->table(
            ['Question', 'Marker', 'Tags', 'Score', 'Matched', 'Reason'],
            collect($failures)->map(fn ($item) => [
                $item['question_id'],
                $item['marker'],
                implode(', ', $item['marker_tags']),
                $item['best']['score'] ?? '—',
                implode(', ', $item['best']['matched_tags'] ?? []),
                $item['reason'],
            ])->all()
        );

        $this->line("Total markers: {$totalMarkers}. Failures: " . count($failures));

        foreach ($failures as $failure) {
            $this->warn(sprintf(
                "Q%s [%s]: %s\n  tags: %s\n  reason: %s\n  candidates: %s",
                $failure['question_id'],
                $failure['marker'],
                $failure['question'],
                implode(', ', $failure['marker_tags']),
                $failure['reason'],
                $this->formatCandidates($failure['candidates'])
            ));
        }

        return self::SUCCESS;
    }

    private function seedClass(string $class): void
    {
        $this->callSilent('db:seed', ['--class' => $class, '--force' => true]);
    }

    private function inferReason(array $markerTags, ?array $result): string
    {
        if (empty($markerTags)) {
            return 'No marker tags present';
        }

        if ($result === null) {
            return 'Matcher returned no candidates';
        }

        if (($result['score'] ?? 0) === 0) {
            return 'No overlapping tags with theory blocks';
        }

        if (($result['score'] ?? 0) < (float) $this->option('min-score')) {
            return 'Score below threshold';
        }

        return 'Weak overlap between marker and theory tags';
    }

    private function formatCandidate(array $candidate): array
    {
        $block = $candidate['block'];

        return [
            'uuid' => $block->uuid,
            'score' => $candidate['score'],
            'matched_tags' => $candidate['matched_tags'],
            'heading' => $block->heading,
        ];
    }

    private function formatCandidates(array $candidates): string
    {
        if (empty($candidates)) {
            return '—';
        }

        return collect($candidates)
            ->map(fn ($c) => ($c['block']->uuid ?? 'n/a').' ['.$c['score'].'] '.implode(',', $c['matched_tags']))
            ->implode(' | ');
    }
}
