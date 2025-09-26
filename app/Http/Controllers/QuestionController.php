<?php

namespace App\Http\Controllers;

use App\Http\Resources\TechnicalQuestionResource;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\ChatGPTExplanation;

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
            }
        }

        // Return JSON API response or redirect back
        return $request->wantsJson()
            ? TechnicalQuestionResource::make($question->fresh())
            : redirect()->back();
    }

}
