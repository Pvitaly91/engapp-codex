<?php

namespace App\Support\Database;

use App\Models\Question;
use App\Models\TextBlock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Shared base for relation seeders that link every question of a V3
 * "questions only" seeder to the most relevant theory text blocks
 * (via the question_theory_text_blocks pivot) so the auto-generated
 * theory-page mixed test surfaces the "Show theory" button on every
 * question — same UX as for Polyglot tests.
 *
 * Subclasses declare:
 *  - questionSeederClass(): which V3 seeder produced the questions to link.
 *  - bundleFor(array $bundleKeys): which theory blocks to attach.
 *
 * This base class handles the rest: text-based pattern classifier
 * (existential there / inversion markers), block-uuid resolution,
 * pivot row writing, and an info summary at the end.
 *
 * It does NOT modify the source seeder's questions — only links.
 */
abstract class V3QuestionsTheoryLinksSeederBase extends Seeder
{
    /**
     * Fully-qualified class name of the V3 source seeder whose questions
     * should be linked.
     */
    abstract protected function questionSeederClass(): string;

    /**
     * Build the ordered list of [seederClass, sortOrder] block specs for
     * a question whose classifier produced $bundleKeys (subset of
     * ['there_is', 'inversion']).
     *
     * @param  array<int, string>  $bundleKeys
     * @return array<int, array{0: string, 1: int}>
     */
    abstract protected function bundleFor(array $bundleKeys): array;

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

        $blockCache = [];
        $now = now();
        $stats = ['default' => 0, 'with_there_is' => 0, 'with_inversion' => 0, 'with_both' => 0];

        foreach ($questions as $question) {
            $bundleKeys = $this->classify((string) $question->question);

            if (count($bundleKeys) > 1) {
                $stats['with_both']++;
            } elseif (in_array('there_is', $bundleKeys, true)) {
                $stats['with_there_is']++;
            } elseif (in_array('inversion', $bundleKeys, true)) {
                $stats['with_inversion']++;
            } else {
                $stats['default']++;
            }

            $blockSpecs = $this->bundleFor($bundleKeys);
            $blockUuids = $this->resolveBlockUuids($blockSpecs, $blockCache);

            // Pivot is keyed by question UUID — survives reseed of source
            // question seeders (where the numeric id changes).
            DB::table('question_theory_text_blocks')
                ->where('question_uuid', $question->uuid)
                ->delete();

            $rows = [];
            foreach ($blockUuids as $position => $blockUuid) {
                $rows[] = [
                    'question_uuid' => $question->uuid,
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
            $this->command->info(sprintf(
                'Linked %d questions: default=%d, +there_is=%d, +inversion=%d, +both=%d.',
                $questions->count(),
                $stats['default'],
                $stats['with_there_is'],
                $stats['with_inversion'],
                $stats['with_both'],
            ));
        }
    }

    /**
     * Classify the English question text into bundle keys.
     *
     * @return array<int, string>
     */
    protected function classify(string $text): array
    {
        $trimmed = trim($text);

        if ($trimmed === '') {
            return [];
        }

        $bundle = [];

        if ($this->mentionsThereExistential($trimmed)) {
            $bundle[] = 'there_is';
        }

        if ($this->mentionsInversion($trimmed)) {
            $bundle[] = 'inversion';
        }

        return $bundle;
    }

    protected function mentionsThereExistential(string $text): bool
    {
        $lower = ' ' . Str::lower($text) . ' ';

        return Str::startsWith(Str::lower($text), 'there ')
            || preg_match('/\bthere\s*\{[^}]*\}/i', $text) === 1
            || str_contains($lower, ' there is ')
            || str_contains($lower, ' there are ')
            || str_contains($lower, ' there was ')
            || str_contains($lower, ' there were ')
            || str_contains($lower, ' there will be ')
            || str_contains($lower, ' there isn')
            || str_contains($lower, ' there aren')
            || str_contains($lower, ' there wasn')
            || str_contains($lower, ' there weren');
    }

    protected function mentionsInversion(string $text): bool
    {
        $lower = ' ' . Str::lower($text) . ' ';

        $startMarkers = [
            'not only ', 'never ', 'rarely ', 'seldom ', 'hardly ',
            'no sooner ', 'gone ', 'blessed ', 'such ',
            'under no circumstances ', 'at no time ', 'in no way ', 'nor ',
            'only when ', 'only after ', 'only then ', 'little ',
        ];
        $startLower = Str::lower(ltrim($text));
        foreach ($startMarkers as $marker) {
            if (Str::startsWith($startLower, $marker)) {
                return true;
            }
        }

        if (str_contains($lower, ' neither ') && str_contains($lower, ' nor ')) {
            return true;
        }

        return false;
    }

    /**
     * Resolve [seederClass, sortOrder] tuples into TextBlock UUIDs in order,
     * deduped, with a per-run cache.
     *
     * @param  array<int, array{0: string, 1: int}>  $blockSpecs
     * @param  array<string, \App\Models\TextBlock|null>  $cache
     * @return array<int, string>
     */
    protected function resolveBlockUuids(array $blockSpecs, array &$cache): array
    {
        $seen = [];
        $uuids = [];

        foreach ($blockSpecs as [$seederClass, $sortOrder]) {
            $key = $seederClass . '#' . $sortOrder;

            if (! array_key_exists($key, $cache)) {
                $cache[$key] = TextBlock::query()
                    ->where('seeder', $seederClass)
                    ->where('sort_order', $sortOrder)
                    ->first();
            }

            $block = $cache[$key];

            if (! $block) {
                throw new RuntimeException(sprintf(
                    'Theory text block missing: seeder=%s sort_order=%d. Run that page seeder first.',
                    $seederClass,
                    $sortOrder
                ));
            }

            if (isset($seen[$block->uuid])) {
                continue;
            }

            $seen[$block->uuid] = true;
            $uuids[] = $block->uuid;
        }

        return $uuids;
    }
}
