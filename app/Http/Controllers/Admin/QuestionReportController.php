<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Services\QuestionReportFileStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionReportController extends Controller
{
    public function index(QuestionReportFileStore $reports): View
    {
        return view('admin.question-reports.index', [
            'reports' => $reports->all(),
            'reportsDirectory' => $reports->directory(),
        ]);
    }

    public function store(Request $request, QuestionReportFileStore $reports): JsonResponse
    {
        $data = $request->validate([
            'question_id' => ['nullable', 'integer', 'required_without:question_uuid'],
            'question_uuid' => ['nullable', 'string', 'max:255', 'required_without:question_id'],
            'comment' => ['required', 'string', 'max:4000'],
            'test_slug' => ['nullable', 'string', 'max:255'],
            'test_name' => ['nullable', 'string', 'max:255'],
            'mode' => ['nullable', 'string', 'max:120'],
            'url' => ['nullable', 'string', 'max:65535'],
        ]);

        $question = Question::query()
            ->when(
                $data['question_id'] ?? null,
                fn ($query, int $questionId) => $query->whereKey($questionId),
                fn ($query) => $query->where('uuid', $data['question_uuid'])
            )
            ->firstOrFail();

        $report = $reports->create($question, $data, $request);

        return response()->json([
            'message' => 'Репорт збережено.',
            'report' => [
                'id' => $report['id'],
                'file' => $report['file'],
            ],
        ], 201);
    }

    public function updateStatus(string $report, Request $request, QuestionReportFileStore $reports): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,fixed'],
        ]);

        $reports->updateStatus($report, $data['status'], $request);

        $message = $data['status'] === 'fixed'
            ? 'Репорт позначено як виправлений.'
            : 'Репорт повернуто в невиконані.';

        return redirect()
            ->route('question-reports.index')
            ->with('status', $message);
    }

    public function destroy(string $report, QuestionReportFileStore $reports): RedirectResponse
    {
        $reports->delete($report);

        return redirect()
            ->route('question-reports.index')
            ->with('status', 'Репорт видалено.');
    }

    public function prompt(Request $request, QuestionReportFileStore $reports): View|RedirectResponse
    {
        $data = $request->validate([
            'scope' => ['required', 'in:open,selected'],
            'report_ids' => ['nullable', 'array'],
            'report_ids.*' => ['string', 'max:255'],
        ]);

        $allReports = $reports->all();
        $selectedReports = $data['scope'] === 'open'
            ? $reports->openReports($allReports)
            : $reports->selectReports($allReports, $data['report_ids'] ?? []);

        if ($selectedReports === []) {
            return redirect()
                ->route('question-reports.index')
                ->withErrors([
                    'prompt' => $data['scope'] === 'open'
                        ? 'Немає невиконаних репортів для prompt.'
                        : 'Виберіть хоча б один репорт для prompt.',
                ]);
        }

        return view('admin.question-reports.index', [
            'reports' => $allReports,
            'reportsDirectory' => $reports->directory(),
            'generatedPrompt' => $reports->buildFixPrompt($selectedReports),
            'promptReportCount' => count($selectedReports),
        ]);
    }
}
