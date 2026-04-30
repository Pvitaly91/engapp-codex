<?php

namespace Database\Seeders\V3\ClaudeOpus47;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Support\Database\JsonTestSeeder;
use Illuminate\Support\Arr;
use RuntimeException;

class PolyglotToBeA1ExtraQuestionsSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }

    protected function syncSavedTestFromDefinition(
        mixed $payload,
        string $seederClass,
        array $questionUuids,
    ): ?SavedGrammarTest {
        if (! is_array($payload) || $payload === []) {
            return null;
        }

        $extendsExisting = (bool) ($payload['extends_existing_saved_test'] ?? false);

        if (! $extendsExisting) {
            return parent::syncSavedTestFromDefinition($payload, $seederClass, $questionUuids);
        }

        $uuid = trim((string) ($payload['uuid'] ?? ''));
        $slug = trim((string) ($payload['slug'] ?? ''));

        if ($uuid === '' || $slug === '') {
            throw new RuntimeException('Extra-questions saved_test must define uuid and slug.');
        }

        $savedTest = SavedGrammarTest::query()->where('uuid', $uuid)->first()
            ?? SavedGrammarTest::query()->where('slug', $slug)->first();

        if (! $savedTest) {
            throw new RuntimeException(sprintf(
                'Existing saved_grammar_test with uuid [%s] / slug [%s] not found; run base PolyglotToBeLessonSeeder first.',
                $uuid,
                $slug
            ));
        }

        $orderedQuestionUuids = $this->normalizeSavedTestQuestionUuids(
            $payload['question_uuids'] ?? [],
            $questionUuids
        );

        $missingQuestionUuids = array_values(array_diff($questionUuids, $orderedQuestionUuids));
        $unknownQuestionUuids = array_values(array_diff($orderedQuestionUuids, $questionUuids));

        if ($missingQuestionUuids !== [] || $unknownQuestionUuids !== []) {
            throw new RuntimeException('saved_test.question_uuids must contain the same UUID set as questions.');
        }

        $existingCount = Question::query()
            ->whereIn('uuid', $orderedQuestionUuids)
            ->count();

        if ($existingCount !== count($orderedQuestionUuids)) {
            throw new RuntimeException('saved_test.question_uuids references questions that were not seeded.');
        }

        $filters = is_array($savedTest->filters) ? $savedTest->filters : [];
        $seederClasses = $this->normalizeStringList($filters['seeder_classes'] ?? []);

        if (! in_array($seederClass, $seederClasses, true)) {
            $seederClasses[] = $seederClass;
        }

        $filters['seeder_classes'] = $seederClasses;

        $existingLinks = $savedTest->questionLinks()->get();
        $existingUuids = $existingLinks->pluck('question_uuid')->all();
        $maxPosition = (int) ($existingLinks->max('position') ?? 0);

        $newLinks = [];
        foreach ($orderedQuestionUuids as $questionUuid) {
            if (in_array($questionUuid, $existingUuids, true)) {
                continue;
            }

            $maxPosition++;
            $newLinks[] = [
                'question_uuid' => $questionUuid,
                'position' => $maxPosition,
            ];
            $existingUuids[] = $questionUuid;
        }

        if ($newLinks !== []) {
            $savedTest->questionLinks()->createMany($newLinks);
        }

        $filters['num_questions'] = $savedTest->questionLinks()->count();

        $savedTest->fill(['filters' => $filters]);
        $savedTest->save();

        return $savedTest;
    }
}
