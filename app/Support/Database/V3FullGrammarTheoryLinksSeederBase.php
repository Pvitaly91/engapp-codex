<?php

namespace App\Support\Database;

use App\Models\Question;
use App\Services\EnglishSentenceGrammarClassifier;
use App\Services\TheoryFeatureBundleResolver;
use Illuminate\Support\Facades\DB;

/**
 * Shared base for V3 relation seeders that produce a FULL grammar
 * breakdown for every question — not just the topic of the gap, but
 * every grammar feature present in the sentence (articles, possessives,
 * adjective order, plural nouns, passive voice, conjunctions, relative
 * clauses, demonstratives, etc.).
 *
 * Subclasses declare:
 *  - questionSeederClass(): which V3 source seeder produced the questions.
 *  - primaryFeature(): the topic this seeder anchors on (defaults to
 *    'verb_to_be_paradigm' since most current users live in the
 *    /theory/verb-to-be category).
 *
 * Wiring uses two services:
 *  - EnglishSentenceGrammarClassifier — returns set of feature slugs.
 *  - TheoryFeatureBundleResolver       — maps slugs → TextBlock UUIDs.
 *
 * The legacy single Question.theory_text_block_uuid column is also
 * populated with the first block for backward compatibility.
 *
 * Subclasses do NOT modify the source seeder's questions — only links.
 */
abstract class V3FullGrammarTheoryLinksSeederBase extends Seeder
{
    abstract protected function questionSeederClass(): string;

    /**
     * Override to anchor on a different theory page. Defaults to verb-to-be
     * paradigm because every current consumer is under /theory/verb-to-be.
     */
    protected function primaryFeature(): string
    {
        return 'verb_to_be_paradigm';
    }

    public function run(): void
    {
        $questions = Question::query()
            ->where('seeder', $this->questionSeederClass())
            ->orderBy('id')
            ->get(['id', 'uuid', 'question']);

        if ($questions->isEmpty()) {
            if ($this->command !== null) {
                $this->command->warn(sprintf(
                    'No questions found for seeder [%s]. Run that seeder first.',
                    $this->questionSeederClass()
                ));
            }

            return;
        }

        $classifier = app(EnglishSentenceGrammarClassifier::class);
        $resolver = app(TheoryFeatureBundleResolver::class);
        $primary = $this->primaryFeature();
        $now = now();
        $featureCounts = [];
        $totalBlocks = 0;

        foreach ($questions as $question) {
            $features = $classifier->classify((string) $question->question);

            // Always include the primary topic so the panel always leads
            // with the seeder's own grammar (e.g. "to be: Present" forms).
            if (! in_array($primary, $features, true)) {
                array_unshift($features, $primary);
            }

            foreach ($features as $f) {
                $featureCounts[$f] = ($featureCounts[$f] ?? 0) + 1;
            }

            $blockUuids = $resolver->resolve($features, $primary);
            $totalBlocks += count($blockUuids);

            DB::table('question_theory_text_blocks')
                ->where('question_id', $question->id)
                ->delete();

            $rows = [];
            foreach ($blockUuids as $position => $blockUuid) {
                $rows[] = [
                    'question_id' => $question->id,
                    'text_block_uuid' => $blockUuid,
                    'position' => $position,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows !== []) {
                DB::table('question_theory_text_blocks')->insert($rows);
            }

            $question->theory_text_block_uuid = $blockUuids[0] ?? null;
            $question->save();
        }

        if ($this->command !== null) {
            arsort($featureCounts);
            $top = collect($featureCounts)
                ->take(10)
                ->map(fn (int $c, string $slug): string => "{$slug}={$c}")
                ->implode(', ');

            $this->command->info(sprintf(
                'Linked %d questions; %d total pivot rows. Top features: %s.',
                $questions->count(),
                $totalBlocks,
                $top
            ));
        }
    }
}
