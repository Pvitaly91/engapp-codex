<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\VerbHint;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class SavedTestQuestionHydrator
{
    public function hydrate(array $snapshot): Question
    {
        $question = new Question();
        $question->forceFill([
            'uuid' => Arr::get($snapshot, 'uuid'),
            'question' => Arr::get($snapshot, 'question'),
            'difficulty' => Arr::get($snapshot, 'difficulty'),
            'level' => Arr::get($snapshot, 'level'),
            'category_id' => Arr::get($snapshot, 'category_id'),
            'source_id' => Arr::get($snapshot, 'source_id'),
            'flag' => Arr::get($snapshot, 'flag'),
        ]);
        $question->setAttribute('id', Arr::get($snapshot, 'id'));
        $question->exists = true;

        $options = collect(Arr::get($snapshot, 'options', []))->map(function (array $option) {
            $model = new QuestionOption();
            $model->forceFill([
                'option' => Arr::get($option, 'option'),
            ]);
            $model->setAttribute('id', Arr::get($option, 'id'));
            $model->exists = true;

            return $model;
        });

        $optionsById = $options
            ->filter(fn (QuestionOption $option) => $option->id !== null)
            ->keyBy(fn (QuestionOption $option) => $option->id);

        $answers = collect(Arr::get($snapshot, 'answers', []))->map(function (array $answer) use ($optionsById, $snapshot) {
            $model = new QuestionAnswer();
            $model->forceFill([
                'question_id' => Arr::get($snapshot, 'id'),
                'marker' => Arr::get($answer, 'marker'),
                'option_id' => Arr::get($answer, 'option_id'),
            ]);
            $model->setAttribute('id', Arr::get($answer, 'id'));
            $model->exists = true;

            $optionData = Arr::get($answer, 'option');
            $optionId = Arr::get($answer, 'option_id');

            if (is_array($optionData)) {
                $option = new QuestionOption();
                $option->forceFill([
                    'option' => Arr::get($optionData, 'option'),
                ]);
                $option->setAttribute('id', Arr::get($optionData, 'id', $optionId));
                $option->exists = true;

                $model->setRelation('option', $option);
            } elseif ($optionId !== null && $optionsById->has($optionId)) {
                $model->setRelation('option', $optionsById->get($optionId));
            }

            return $model;
        });

        $verbHints = collect(Arr::get($snapshot, 'verb_hints', []))->map(function (array $hint) use ($optionsById, $snapshot) {
            $model = new VerbHint();
            $model->forceFill([
                'question_id' => Arr::get($snapshot, 'id'),
                'marker' => Arr::get($hint, 'marker'),
                'option_id' => Arr::get($hint, 'option_id'),
            ]);
            $model->setAttribute('id', Arr::get($hint, 'id'));
            $model->exists = true;

            $optionData = Arr::get($hint, 'option');
            $optionId = Arr::get($hint, 'option_id');

            if (is_array($optionData)) {
                $option = new QuestionOption();
                $option->forceFill([
                    'option' => Arr::get($optionData, 'option'),
                ]);
                $option->setAttribute('id', Arr::get($optionData, 'id', $optionId));
                $option->exists = true;

                $model->setRelation('option', $option);
            } elseif ($optionId !== null && $optionsById->has($optionId)) {
                $model->setRelation('option', $optionsById->get($optionId));
            }

            return $model;
        });

        $variants = collect(Arr::get($snapshot, 'variants', []))->map(function (array $variant) use ($snapshot) {
            $model = new QuestionVariant();
            $model->forceFill([
                'question_id' => Arr::get($snapshot, 'id'),
                'text' => Arr::get($variant, 'text'),
            ]);
            $model->setAttribute('id', Arr::get($variant, 'id'));
            $model->exists = true;

            return $model;
        });

        $hints = collect(Arr::get($snapshot, 'hints', []))->map(function (array $hint) use ($snapshot) {
            $model = new QuestionHint();
            $model->forceFill([
                'question_id' => Arr::get($snapshot, 'id'),
                'provider' => Arr::get($hint, 'provider'),
                'locale' => Arr::get($hint, 'locale'),
                'hint' => Arr::get($hint, 'hint'),
            ]);
            $model->setAttribute('id', Arr::get($hint, 'id'));
            $model->exists = true;

            return $model;
        });

        $question->setRelation('options', $options instanceof Collection ? $options : collect($options));
        $question->setRelation('answers', $answers instanceof Collection ? $answers : collect($answers));
        $question->setRelation('verbHints', $verbHints instanceof Collection ? $verbHints : collect($verbHints));
        $question->setRelation('variants', $variants instanceof Collection ? $variants : collect($variants));
        $question->setRelation('hints', $hints instanceof Collection ? $hints : collect($hints));

        return $question;
    }
}
