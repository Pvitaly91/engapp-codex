<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TechnicalQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $question = $this->resource;

        $relations = [];

        if (Schema::hasTable('question_answers')) {
            $relations[] = 'answers.option';
        }

        if (Schema::hasTable('verb_hints')) {
            $relations[] = 'verbHints.option';
        }

        if (Schema::hasTable('question_variants')) {
            $relations[] = 'variants';
        }

        if (Schema::hasTable('question_options')) {
            $relations[] = 'options';
        }

        if (Schema::hasTable('question_hints')) {
            $relations[] = 'hints';
        }

        if (Schema::hasTable('chatgpt_explanations')) {
            $relations[] = 'chatgptExplanations';
        }

        if (! empty($relations)) {
            $question->loadMissing($relations);
        }

        $answersCollection = Schema::hasTable('question_answers')
            ? $question->answers
            : new Collection();

        $verbHintsCollection = Schema::hasTable('verb_hints')
            ? $question->verbHints
            : new Collection();

        $answers = $answersCollection
            ->map(function ($answer) use ($verbHintsCollection) {
                $markerKey = strtolower($answer->marker);
                $verbHint = $verbHintsCollection
                    ->firstWhere(fn ($hint) => strtolower($hint->marker) === $markerKey);
                $value = $answer->option->option
                    ?? $answer->answer
                    ?? '';

                return [
                    'id' => $answer->id,
                    'marker' => strtoupper($answer->marker),
                    'marker_key' => $markerKey,
                    'value' => $value,
                    'option' => $answer->option ? [
                        'id' => $answer->option->id,
                        'label' => $answer->option->option,
                    ] : null,
                    'verb_hint' => $verbHint ? [
                        'id' => $verbHint->id,
                        'value' => optional($verbHint->option)->option,
                    ] : null,
                ];
            })
            ->values();

        $answersByMarker = $answers
            ->mapWithKeys(fn ($answer) => [$answer['marker_key'] => $answer['value']]);

        $variants = Schema::hasTable('question_variants')
            ? $question->variants
                ->filter(function ($variant) {
                    return is_string($variant->text) && trim($variant->text) !== '';
                })
                ->map(fn ($variant) => [
                    'id' => $variant->id,
                    'text' => $variant->text,
                ])
                ->values()
            : new Collection();

        $correctOptionIds = $answersCollection
            ->pluck('option_id')
            ->filter()
            ->unique()
            ->values();

        $options = Schema::hasTable('question_options')
            ? $question->options
            ->map(function ($option) use ($correctOptionIds) {
                return [
                    'id' => $option->id,
                    'label' => $option->option,
                    'is_correct' => $correctOptionIds->contains($option->id),
                ];
            })
            : new Collection();

        foreach ($answersCollection as $answer) {
            $option = $answer->option;

            if (! $option) {
                continue;
            }

            if ($options->contains(fn ($item) => $item['id'] === $option->id)) {
                continue;
            }

            $options->push([
                'id' => $option->id,
                'label' => $option->option,
                'is_correct' => true,
            ]);
        }

        $options = $options
            ->filter(fn ($item) => filled($item['label']))
            ->unique('id')
            ->values();

        $questionHints = Schema::hasTable('question_hints')
            ? $question->hints
                ->sortBy(fn ($hint) => $hint->provider . '|' . $hint->locale)
                ->map(fn ($hint) => [
                    'id' => $hint->id,
                    'provider' => $hint->provider,
                    'locale' => $hint->locale,
                    'hint' => $hint->hint,
                ])
                ->values()
            : new Collection();

        $markers = new Collection();

        if (is_string($question->question)) {
            if (preg_match_all('/\{(a\d+)\}/i', $question->question, $matches)) {
                $markers = collect($matches[1])
                    ->map(fn ($marker) => strtolower($marker))
                    ->unique()
                    ->values();
            }
        }

        $markers = $markers->merge($answersByMarker->keys())->unique()->values();

        $chatGptExplanations = Schema::hasTable('chatgpt_explanations')
            ? $question->chatgptExplanations
                ->sortBy(function ($explanation) {
                    return sprintf(
                        '%s|%s|%s',
                        strtolower($explanation->language ?? ''),
                        $explanation->wrong_answer ?? '',
                        $explanation->correct_answer ?? ''
                    );
                })
                ->map(fn ($explanation) => [
                    'id' => $explanation->id,
                    'language' => $explanation->language,
                    'wrong_answer' => $explanation->wrong_answer,
                    'correct_answer' => $explanation->correct_answer,
                    'explanation' => $explanation->explanation,
                ])
                ->values()
            : new Collection();

        return [
            'id' => $question->id,
            'uuid' => $question->uuid,
            'question' => $question->question,
            'level' => $question->level,
            'answers' => $answers->all(),
            'answers_by_marker' => $answersByMarker->all(),
            'variants' => $variants->all(),
            'options' => $options->all(),
            'question_hints' => $questionHints->all(),
            'markers' => $markers->all(),
            'chatgpt_explanations' => $chatGptExplanations->all(),
        ];
    }
}
