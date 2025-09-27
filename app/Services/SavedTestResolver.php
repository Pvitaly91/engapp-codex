<?php

namespace App\Services;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\Test;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class SavedTestResolver
{
    public function resolve(string $slug): ResolvedSavedTest
    {
        $legacy = Test::where('slug', $slug)->first();

        if ($legacy) {
            $questionIds = collect($legacy->questions ?? [])
                ->filter(fn ($id) => filled($id))
                ->map(fn ($id) => (int) $id)
                ->values();

            $uuidMap = $questionIds->isEmpty()
                ? collect()
                : Question::whereIn('id', $questionIds)->pluck('uuid', 'id');

            $questionUuids = $questionIds
                ->map(fn ($id) => $uuidMap[$id] ?? null)
                ->filter()
                ->values();

            return new ResolvedSavedTest($legacy, $questionIds, $questionUuids, false);
        }

        $saved = SavedGrammarTest::with('questionLinks')->where('slug', $slug)->first();

        if ($saved) {
            $questionUuids = $saved->questionLinks
                ->sortBy('position')
                ->pluck('question_uuid')
                ->filter(fn ($uuid) => filled($uuid))
                ->values();

            $idMap = $questionUuids->isEmpty()
                ? collect()
                : Question::whereIn('uuid', $questionUuids)->pluck('id', 'uuid');

            $questionIds = $questionUuids
                ->map(fn ($uuid) => isset($idMap[$uuid]) ? (int) $idMap[$uuid] : null)
                ->filter(fn ($id) => $id !== null)
                ->values();

            return new ResolvedSavedTest($saved, $questionIds, $questionUuids, true);
        }

        throw new ModelNotFoundException("Saved test [{$slug}] not found.");
    }

    public function loadQuestions(ResolvedSavedTest $resolved, array $relations = []): Collection
    {
        if ($resolved->questionIds->isEmpty()) {
            return collect();
        }

        $query = Question::with($relations)->whereIn('id', $resolved->questionIds);

        $questions = $query->get()->keyBy('id');

        return $resolved->questionIds
            ->map(fn ($id) => $questions->get($id))
            ->filter()
            ->values();
    }
}
