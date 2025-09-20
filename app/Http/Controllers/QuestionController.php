<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Services\QuestionDumpService;
use Illuminate\Http\Request;
use Throwable;

class QuestionController extends Controller
{
    public function __construct(private QuestionDumpService $dumpService)
    {
    }

    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'question' => 'required|string',
        ]);

        $question->question = $data['question'];
        $question->save();

        $this->dumpService->storeDump($question);

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->back();
    }

    public function applyDump(Request $request, Question $question)
    {
        try {
            $this->dumpService->applyDump($question);
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'applied']);
        }

        return redirect()->back()->with('status', 'Дамп успішно застосовано.');
    }
}
