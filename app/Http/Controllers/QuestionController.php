<?php

namespace App\Http\Controllers;

use App\Http\Resources\TechnicalQuestionResource;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Services\QuestionExportService;
use App\Services\QuestionImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class QuestionController extends Controller
{
    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'question' => ['sometimes', 'required', 'string'],
            'level'    => ['sometimes', 'nullable', 'string', 'in:A1,A2,B1,B2,C1,C2'],
        ]);
  
        // Store old question text for syncing related records
        $oldQuestion = $question->question;
   
        if ($data) {
            $question->fill($data);

            // Save only if there are actual changes
            if ($question->isDirty()) {
                $question->save();

                // Sync "question" field in ChatGPTExplanation if it was updated
                if (array_key_exists('question', $data)) {
                    ChatGPTExplanation::where('question', $oldQuestion)
                        ->update(['question' => $data['question']]);
                }

                // Sync "level" field in ChatGPTExplanation if it was updated
                if (
                    array_key_exists('level', $data)
                    && Schema::hasTable('chatgpt_explanations')
                    && Schema::hasColumn('chatgpt_explanations', 'level')
                ) {
                    ChatGPTExplanation::where('question', $data['question'] ?? $oldQuestion)
                        ->update(['level' => $data['level']]);
                }

                // Re-export question dump when the question text changes so related
                // ChatGPT explanations are written with the updated question value
                if ($question->wasChanged('question')) {
                    app(QuestionExportService::class)->export($question->fresh());
                }
            }
        }

        // Return JSON API response or redirect back
        return $request->wantsJson()
            ? TechnicalQuestionResource::make($question->fresh())
            : redirect()->back();
    }

    public function export(Question $question): JsonResponse
    {
        app(QuestionExportService::class)->export($question->fresh());

        return response()->json([
            'status' => 'ok',
            'message' => 'Дамп питання оновлено.',
        ]);
    }

    public function exportByUuid(Request $request): JsonResponse
    {
        $data = $request->validate([
            'uuid' => ['required', 'string'],
        ]);

        $question = Question::where('uuid', $data['uuid'])->first();

        if (! $question) {
            return response()->json([
                'status' => 'error',
                'message' => 'Питання з таким UUID не знайдено.',
            ], 404);
        }

        app(QuestionExportService::class)->export($question->fresh());

        return response()->json([
            'status' => 'ok',
            'message' => 'Дамп питання оновлено.',
        ]);
    }

    public function restoreFromDumps(Request $request): JsonResponse
    {
        $data = $request->validate([
            'include_deleted' => ['sometimes', 'boolean'],
        ]);

        $includeDeleted = (bool) ($data['include_deleted'] ?? false);

        try {
            $result = app(QuestionImportService::class)->restoreAll($includeDeleted);
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => 'Сталася неочікувана помилка під час відновлення питань.',
            ], 500);
        }

        $restoredCount = count($result['restored'] ?? []);
        $skippedCount = count($result['skipped'] ?? []);
        $errorCount = count($result['errors'] ?? []);

        if ($restoredCount === 0 && $skippedCount === 0 && $errorCount === 0) {
            $message = 'Доступні дампи не знайдено.';
        } else {
            $parts = [];
            $parts[] = 'Відновлено: ' . $restoredCount;
            $parts[] = ($includeDeleted ? 'Пропущено' : 'Пропущено (у списку видалених)') . ': ' . $skippedCount;

            if ($errorCount > 0) {
                $parts[] = 'З помилками: ' . $errorCount;
            }

            $message = implode('; ', $parts) . '.';
        }

        return response()->json([
            'status' => $errorCount > 0 ? 'partial' : 'ok',
            'message' => $message,
            'details' => $result,
        ]);
    }

    public function restoreByUuid(Request $request): JsonResponse
    {
        $data = $request->validate([
            'uuid' => ['required', 'string'],
        ]);

        try {
            $question = app(QuestionImportService::class)->restoreByUuid($data['uuid']);
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => 'Сталася неочікувана помилка під час відновлення питання.',
            ], 500);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Питання відновлено.',
            'question' => [
                'id' => $question->id,
                'uuid' => $question->uuid,
            ],
        ]);
    }
}
