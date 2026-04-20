<?php

namespace App\Services;

use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Tag;
use App\Support\Database\QuestionUuidResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuestionMetaSyncService
{
    public function sync(array $meta, string $seederClass, string $hintLocale = 'uk'): void
    {
        foreach ($meta as $data) {
            $question = Question::where(
                'uuid',
                app(QuestionUuidResolver::class)->toPersistent((string) ($data['uuid'] ?? ''))
            )->first();

            if (! $question) {
                continue;
            }

            if (Schema::hasColumn('questions', 'seeder') && empty($question->seeder)) {
                $question->forceFill(['seeder' => $seederClass])->save();
            }

            $hintText = $this->formatHints($data['hints'] ?? []);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => $hintLocale],
                    ['hint' => $hintText]
                );
            }

            $gapTags = $data['gap_tags'] ?? [];
            if (! empty($gapTags) && Schema::hasTable('question_marker_tag')) {
                $this->syncMarkerTags($question->id, $gapTags);
            }

            $answers = $data['answers'] ?? [];
            $optionMarkers = $data['option_markers'] ?? [];

            foreach ($data['explanations'] ?? [] as $option => $text) {
                $marker = $optionMarkers[$option] ?? array_key_first($answers);
                $correct = $marker !== null ? ($answers[$marker] ?? reset($answers)) : reset($answers);

                if (! is_string($correct)) {
                    $correct = (string) $correct;
                }

                ChatGPTExplanation::updateOrCreate(
                    [
                        'question' => $question->question,
                        'wrong_answer' => $option,
                        'correct_answer' => $correct,
                        'language' => 'ua',
                    ],
                    ['explanation' => $text]
                );
            }
        }
    }

    public function formatHints(array $hints): ?string
    {
        if (empty($hints)) {
            return null;
        }

        $parts = [];
        foreach ($hints as $text) {
            $clean = trim((string) $text);

            if ($clean === '') {
                continue;
            }

            $parts[] = $clean;
        }

        if ($parts === []) {
            return null;
        }

        return implode("\n", $parts);
    }

    public function syncMarkerTags(int $questionId, array $gapTags): void
    {
        DB::table('question_marker_tag')
            ->where('question_id', $questionId)
            ->delete();

        $now = now();

        foreach ($gapTags as $marker => $tagNames) {
            if (! is_array($tagNames)) {
                continue;
            }

            foreach ($tagNames as $tagName) {
                if (! is_string($tagName) || trim($tagName) === '') {
                    continue;
                }

                $tag = Tag::where('name', trim($tagName))->first();

                if (! $tag) {
                    $tag = Tag::whereRaw('LOWER(name) = ?', [strtolower(trim($tagName))])->first();
                }

                if (! $tag) {
                    continue;
                }

                DB::table('question_marker_tag')->insertOrIgnore([
                    'question_id' => $questionId,
                    'marker' => $marker,
                    'tag_id' => $tag->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
