<?php

namespace App\Services;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\Source;
use App\Models\Tag;
use App\Models\VerbHint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class QuestionImportService
{
    /**
     * Restore every question dump stored in database/seeders/questions.
     */
    public function restoreAll(bool $includeDeleted = false): array
    {
        $directory = database_path('seeders/questions');

        if (! File::isDirectory($directory)) {
            return [
                'restored' => [],
                'skipped' => [],
                'errors' => [
                    [
                        'uuid' => null,
                        'message' => 'Каталог з дампами питань не знайдено.',
                    ],
                ],
            ];
        }

        $files = collect(File::files($directory))
            ->filter(fn ($file) => $file->getExtension() === 'json')
            ->filter(fn ($file) => $file->getFilename() !== 'deleted-questions.json')
            ->values();

        $deleted = $includeDeleted ? [] : $this->readDeletedUuids();

        $restored = [];
        $skipped = [];
        $errors = [];

        foreach ($files as $file) {
            $uuid = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            if (! $includeDeleted && $this->isInDeleted($uuid, $deleted)) {
                $skipped[] = $uuid;
                continue;
            }

            try {
                $question = $this->importFromFile($file->getPathname());
                $restored[] = $question->uuid ?? $uuid;
            } catch (\Throwable $exception) {
                $errors[] = [
                    'uuid' => $uuid,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return [
            'restored' => array_values(array_unique($restored)),
            'skipped' => array_values(array_unique($skipped)),
            'errors' => $errors,
        ];
    }

    /**
     * Restore a single question dump by UUID.
     */
    public function restoreByUuid(string $uuid): Question
    {
        $trimmed = trim($uuid);

        if ($trimmed === '') {
            throw new RuntimeException('UUID не може бути порожнім.');
        }

        $path = database_path('seeders/questions/' . $trimmed . '.json');

        if (! File::exists($path)) {
            throw new RuntimeException('Файл дампу для вказаного UUID не знайдено.');
        }

        return $this->importFromFile($path);
    }

    /**
     * @throws RuntimeException
     */
    protected function importFromFile(string $path): Question
    {
        $contents = File::get($path);
        $payload = json_decode($contents, true);

        if (! is_array($payload)) {
            throw new RuntimeException('Невалідна структура JSON у дампі питання.');
        }

        return $this->importPayload($payload);
    }

    /**
     * @throws RuntimeException
     */
    protected function importPayload(array $payload): Question
    {
        $questionData = Arr::get($payload, 'question');

        if (! is_array($questionData)) {
            throw new RuntimeException('Дамп не містить даних питання.');
        }

        $uuid = trim((string) Arr::get($questionData, 'uuid', ''));

        if ($uuid === '') {
            throw new RuntimeException('У дампі відсутній UUID питання.');
        }

        return DB::transaction(function () use ($payload, $questionData, $uuid) {
            $question = Question::query()->firstOrNew(['uuid' => $uuid]);

            $categoryId = $this->resolveCategoryId($questionData, Arr::get($payload, 'category'));
            $sourceId = $this->resolveSourceId($questionData, Arr::get($payload, 'source'));

            $question->fill([
                'uuid' => $uuid,
                'question' => Arr::get($questionData, 'question'),
                'difficulty' => Arr::get($questionData, 'difficulty'),
                'level' => Arr::get($questionData, 'level'),
                'flag' => Arr::get($questionData, 'flag'),
            ]);

            $question->category_id = $categoryId;
            $question->source_id = $sourceId;

            $question->save();

            $tagIds = $this->resolveTagIds(Arr::get($payload, 'tags', []));
            $question->tags()->sync($tagIds);

            $optionMap = $this->syncOptions($question, Arr::get($payload, 'options', []));

            $this->syncAnswers($question, Arr::get($payload, 'answers', []), $optionMap);
            $this->syncVerbHints($question, Arr::get($payload, 'verb_hints', []), $optionMap);
            $this->syncVariants($question, Arr::get($payload, 'variants', []));
            $this->syncHints($question, Arr::get($payload, 'hints', []));
            $this->syncChatGptExplanations($question, Arr::get($payload, 'chatgpt_explanations', []));

            return $question->fresh();
        });
    }

    protected function resolveCategoryId(array $questionData, mixed $categoryPayload): ?int
    {
        $categoryId = Arr::get($questionData, 'category_id');

        if ($categoryId) {
            $existing = Category::find($categoryId);
            if ($existing) {
                return $existing->id;
            }
        }

        if (is_array($categoryPayload)) {
            $candidateId = Arr::get($categoryPayload, 'id');
            if ($candidateId) {
                $existing = Category::find($candidateId);
                if ($existing) {
                    return $existing->id;
                }
            }

            $name = trim((string) Arr::get($categoryPayload, 'name', ''));
            if ($name !== '') {
                return Category::firstOrCreate(['name' => $name])->id;
            }
        }

        return null;
    }

    protected function resolveSourceId(array $questionData, mixed $sourcePayload): ?int
    {
        $sourceId = Arr::get($questionData, 'source_id');

        if ($sourceId) {
            $existing = Source::find($sourceId);
            if ($existing) {
                return $existing->id;
            }
        }

        if (is_array($sourcePayload)) {
            $candidateId = Arr::get($sourcePayload, 'id');
            if ($candidateId) {
                $existing = Source::find($candidateId);
                if ($existing) {
                    return $existing->id;
                }
            }

            $name = trim((string) Arr::get($sourcePayload, 'name', ''));
            if ($name !== '') {
                return Source::firstOrCreate(['name' => $name])->id;
            }
        }

        return null;
    }

    protected function resolveTagIds(array $tagPayloads): array
    {
        $ids = [];

        foreach ($tagPayloads as $tagPayload) {
            if (! is_array($tagPayload)) {
                continue;
            }

            $tagData = Arr::get($tagPayload, 'tag');

            if (! is_array($tagData)) {
                continue;
            }

            $tag = null;

            $tagId = Arr::get($tagData, 'id');
            if ($tagId) {
                $tag = Tag::find($tagId);
            }

            if (! $tag) {
                $name = trim((string) Arr::get($tagData, 'name', ''));
                $category = trim((string) Arr::get($tagData, 'category', ''));

                if ($name !== '') {
                    $query = Tag::query()->where('name', $name);
                    if ($category !== '') {
                        $query->where('category', $category);
                    }

                    $tag = $query->first();

                    if (! $tag) {
                        $attributes = ['name' => $name];
                        if ($category !== '') {
                            $attributes['category'] = $category;
                        }

                        $tag = Tag::create($attributes);
                    }
                }
            }

            if ($tag) {
                $ids[] = $tag->id;
            }
        }

        return array_values(array_unique($ids));
    }

    protected function syncOptions(Question $question, array $optionPayloads): array
    {
        $mapById = [];
        $mapByLabel = [];
        $attachments = [];

        foreach ($optionPayloads as $optionPayload) {
            if (! is_array($optionPayload)) {
                continue;
            }

            $optionData = Arr::get($optionPayload, 'option');
            if (! is_array($optionData)) {
                continue;
            }

            $label = trim((string) Arr::get($optionData, 'option', ''));
            $originalId = Arr::get($optionData, 'id');

            $option = null;

            if ($originalId) {
                $option = QuestionOption::find($originalId);
            }

            if (! $option && $label !== '') {
                $option = QuestionOption::query()->where('option', $label)->first();
            }

            if (! $option && $label !== '') {
                $option = QuestionOption::create(['option' => $label]);
            }

            if (! $option) {
                continue;
            }

            if ($originalId) {
                $mapById[(string) $originalId] = $option->id;
            }

            if ($label !== '') {
                $mapByLabel[strtolower($label)] = $option->id;
            }

            $flag = Arr::get($optionPayload, 'pivot.flag');
            $attachments[] = [
                'question_id' => $question->id,
                'option_id' => $option->id,
                'flag' => is_null($flag) ? null : (int) $flag,
            ];
        }

        DB::table('question_option_question')->where('question_id', $question->id)->delete();

        if (! empty($attachments)) {
            $unique = [];

            foreach ($attachments as $attachment) {
                $key = $attachment['option_id'] . '|' . ($attachment['flag'] === null ? 'null' : $attachment['flag']);
                $unique[$key] = $attachment;
            }

            DB::table('question_option_question')->insert(array_values($unique));
        }

        return [
            'by_id' => $mapById,
            'by_label' => $mapByLabel,
        ];
    }

    protected function syncAnswers(Question $question, array $answerPayloads, array $optionMap): void
    {
        QuestionAnswer::query()->where('question_id', $question->id)->delete();

        foreach ($answerPayloads as $answerPayload) {
            if (! is_array($answerPayload)) {
                continue;
            }

            $answerData = Arr::get($answerPayload, 'answer');
            if (! is_array($answerData)) {
                continue;
            }

            $marker = trim((string) Arr::get($answerData, 'marker', ''));
            if ($marker === '') {
                continue;
            }

            $optionId = $this->resolveOptionId($answerPayload, $optionMap);

            if (! $optionId) {
                continue;
            }

            QuestionAnswer::create([
                'question_id' => $question->id,
                'marker' => strtoupper($marker),
                'option_id' => $optionId,
            ]);
        }
    }

    protected function syncVerbHints(Question $question, array $verbHintPayloads, array $optionMap): void
    {
        VerbHint::query()->where('question_id', $question->id)->delete();

        foreach ($verbHintPayloads as $verbHintPayload) {
            if (! is_array($verbHintPayload)) {
                continue;
            }

            $verbHintData = Arr::get($verbHintPayload, 'verb_hint');
            if (! is_array($verbHintData)) {
                continue;
            }

            $marker = trim((string) Arr::get($verbHintData, 'marker', ''));
            if ($marker === '') {
                continue;
            }

            $optionId = $this->resolveOptionId($verbHintPayload, $optionMap, 'verb_hint');

            if (! $optionId) {
                continue;
            }

            VerbHint::create([
                'question_id' => $question->id,
                'marker' => strtoupper($marker),
                'option_id' => $optionId,
            ]);
        }
    }

    protected function syncVariants(Question $question, array $variantPayloads): void
    {
        QuestionVariant::query()->where('question_id', $question->id)->delete();

        foreach ($variantPayloads as $variantPayload) {
            if (! is_array($variantPayload)) {
                continue;
            }

            $text = trim((string) Arr::get($variantPayload, 'text', ''));

            if ($text === '') {
                continue;
            }

            QuestionVariant::create([
                'question_id' => $question->id,
                'text' => $text,
            ]);
        }
    }

    protected function syncHints(Question $question, array $hintPayloads): void
    {
        QuestionHint::query()->where('question_id', $question->id)->delete();

        foreach ($hintPayloads as $hintPayload) {
            if (! is_array($hintPayload)) {
                continue;
            }

            $hint = trim((string) Arr::get($hintPayload, 'hint', ''));

            if ($hint === '') {
                continue;
            }

            QuestionHint::create([
                'question_id' => $question->id,
                'provider' => trim((string) Arr::get($hintPayload, 'provider', '')) ?: null,
                'locale' => trim((string) Arr::get($hintPayload, 'locale', '')) ?: null,
                'hint' => $hint,
            ]);
        }
    }

    protected function syncChatGptExplanations(Question $question, array $explanationPayloads): void
    {
        $keptIds = [];

        foreach ($explanationPayloads as $explanationPayload) {
            if (! is_array($explanationPayload)) {
                continue;
            }

            $wrong = Arr::get($explanationPayload, 'wrong_answer');
            $correct = Arr::get($explanationPayload, 'correct_answer');
            $language = Arr::get($explanationPayload, 'language');

            $model = ChatGPTExplanation::updateOrCreate(
                [
                    'question' => $question->question,
                    'wrong_answer' => $wrong,
                    'correct_answer' => $correct,
                    'language' => $language,
                ],
                [
                    'explanation' => Arr::get($explanationPayload, 'explanation'),
                ]
            );

            $keptIds[] = $model->id;
        }

        $query = ChatGPTExplanation::query()->where('question', $question->question);

        if (! empty($keptIds)) {
            $query->whereNotIn('id', $keptIds)->delete();
        } else {
            $query->delete();
        }
    }

    protected function resolveOptionId(array $payload, array $optionMap, string $context = 'answer'): ?int
    {
        $dataKey = $context === 'verb_hint' ? 'verb_hint' : 'answer';
        $data = Arr::get($payload, $dataKey, []);
        $originalOptionId = Arr::get($data, 'option_id');

        if ($originalOptionId && isset($optionMap['by_id'][(string) $originalOptionId])) {
            return $optionMap['by_id'][(string) $originalOptionId];
        }

        $optionData = Arr::get($payload, 'option');
        if (is_array($optionData)) {
            $label = trim((string) Arr::get($optionData, 'option', ''));
            if ($label !== '' && isset($optionMap['by_label'][strtolower($label)])) {
                return $optionMap['by_label'][strtolower($label)];
            }

            if ($label !== '') {
                $existing = QuestionOption::query()->where('option', $label)->first();
                if ($existing) {
                    return $existing->id;
                }

                $created = QuestionOption::create(['option' => $label]);
                return $created->id;
            }
        }

        return null;
    }

    protected function readDeletedUuids(): array
    {
        $path = database_path('seeders/questions/deleted-questions.json');

        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn ($value) => strtolower(trim($value)))
            ->values()
            ->all();
    }

    protected function isInDeleted(string $uuid, array $deleted): bool
    {
        $normalised = strtolower(trim($uuid));

        if ($normalised === '') {
            return false;
        }

        return in_array($normalised, $deleted, true);
    }
}
