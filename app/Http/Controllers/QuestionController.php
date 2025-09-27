<?php

namespace App\Http\Controllers;

use App\Http\Resources\TechnicalQuestionResource;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Services\QuestionExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                if (array_key_exists('level', $data)) {
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

}
