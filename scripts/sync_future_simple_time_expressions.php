<?php

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\VerbHint;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$definitionPath = base_path('database/seeders/V3/FutureForms/FutureSimple/FutureSimpleTimeExpressionsAllLevelsV3Seeder/definition.json');
$localizationPath = base_path('database/seeders/V3/FutureForms/FutureSimple/FutureSimpleTimeExpressionsAllLevelsV3Seeder/localizations/uk.json');

$definition = json_decode(file_get_contents($definitionPath), true, 512, JSON_THROW_ON_ERROR);
$localization = json_decode(file_get_contents($localizationPath), true, 512, JSON_THROW_ON_ERROR);
$localizedQuestions = collect($localization['questions'] ?? [])->keyBy('uuid');

$updated = 0;

foreach ($definition['questions'] ?? [] as $item) {
    $uuid = (string) ($item['uuid'] ?? '');
    if ($uuid === '') {
        continue;
    }

    $question = Question::where('uuid', $uuid)->first();
    if (! $question) {
        continue;
    }

    DB::transaction(function () use ($question, $item, $localizedQuestions, &$updated) {
        $markers = $item['markers'] ?? [];
        $options = [];
        $optionsByMarker = [];

        foreach ($markers as $marker => $payload) {
            $markerOptions = array_values(array_unique(array_filter(array_map('strval', $payload['options'] ?? []))));
            $optionsByMarker[$marker] = $markerOptions;
            foreach ($markerOptions as $option) {
                if (! in_array($option, $options, true)) {
                    $options[] = $option;
                }
            }
        }

        $attributes = ['question' => $item['question']];
        if (Schema::hasColumn('questions', 'options_by_marker')) {
            $attributes['options_by_marker'] = $optionsByMarker;
        }
        $question->forceFill($attributes)->save();

        QuestionAnswer::where('question_id', $question->id)->delete();
        DB::table('question_option_question')->where('question_id', $question->id)->delete();
        VerbHint::where('question_id', $question->id)->delete();

        if (Schema::hasTable('question_variants')) {
            QuestionVariant::where('question_id', $question->id)->delete();
        }

        foreach ($markers as $marker => $payload) {
            $answerOption = QuestionOption::firstOrCreate(['option' => (string) $payload['answer']]);
            QuestionAnswer::create([
                'question_id' => $question->id,
                'marker' => (string) $marker,
                'option_id' => $answerOption->id,
            ]);

            $defaultHint = trim((string) ($payload['verb_hint'] ?? ''));
            if ($defaultHint !== '') {
                $hintOption = QuestionOption::firstOrCreate(['option' => $defaultHint]);
                DB::table('question_option_question')->insertOrIgnore([
                    'question_id' => $question->id,
                    'option_id' => $hintOption->id,
                    'flag' => 1,
                ]);
                VerbHint::create([
                    'question_id' => $question->id,
                    'marker' => (string) $marker,
                    'locale' => '',
                    'option_id' => $hintOption->id,
                ]);
            }
        }

        foreach ($options as $option) {
            $questionOption = QuestionOption::firstOrCreate(['option' => $option]);
            DB::table('question_option_question')->insertOrIgnore([
                'question_id' => $question->id,
                'option_id' => $questionOption->id,
                'flag' => null,
            ]);
        }

        if (Schema::hasTable('question_variants')) {
            foreach ($item['variants'] ?? [$item['question']] as $variant) {
                QuestionVariant::create([
                    'question_id' => $question->id,
                    'text' => (string) $variant,
                ]);
            }
        }

        $localized = $localizedQuestions->get($item['uuid']);
        foreach (($localized['localizations']['uk']['verb_hints'] ?? []) as $marker => $hint) {
            $clean = trim((string) $hint);
            if ($clean === '') {
                continue;
            }

            $hintOption = QuestionOption::firstOrCreate(['option' => $clean]);
            DB::table('question_option_question')->insertOrIgnore([
                'question_id' => $question->id,
                'option_id' => $hintOption->id,
                'flag' => 1,
            ]);
            VerbHint::create([
                'question_id' => $question->id,
                'marker' => (string) $marker,
                'locale' => 'uk',
                'option_id' => $hintOption->id,
            ]);
        }

        $updated++;
    });
}

echo "Future Simple time-expression questions synced: {$updated}\n";
