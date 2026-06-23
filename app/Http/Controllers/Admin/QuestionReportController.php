<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Services\QuestionReportFileStore;
use App\Services\QuestionReportIssueCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuestionReportController extends Controller
{
    public function index(QuestionReportFileStore $reports, QuestionReportIssueCatalog $issueCatalog): View
    {
        return view('admin.question-reports.index', [
            'reports' => $reports->all(),
            'reportsDirectory' => $reports->directory(),
            'issueCatalog' => $issueCatalog->all(),
        ]);
    }

    public function store(Request $request, QuestionReportFileStore $reports, QuestionReportIssueCatalog $issueCatalog): JsonResponse
    {
        $data = $this->validateReportContent($request, $issueCatalog, [
            'question_id' => ['nullable', 'integer', 'required_without:question_uuid'],
            'question_uuid' => ['nullable', 'string', 'max:255', 'required_without:question_id'],
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
                'issue_types' => $report['issue_types'] ?? [],
                'issue_labels' => $report['issue_labels'] ?? [],
                'issues' => $report['issue_types'] ?? [],
                'comment' => $report['comment'] ?? '',
                'reported_at' => $report['reported_at'] ?? null,
                'status' => $report['status'] ?? 'open',
                'seeder' => data_get($report, 'question.seeder'),
                'test' => $report['test'] ?? [],
                'question_id' => data_get($report, 'question.id'),
                'question_uuid' => data_get($report, 'question.uuid'),
            ],
        ], 201);
    }

    public function update(string $report, Request $request, QuestionReportFileStore $reports, QuestionReportIssueCatalog $issueCatalog): JsonResponse|RedirectResponse
    {
        $data = $this->validateReportContent($request, $issueCatalog);

        $updatedReport = $reports->updateReport($report, $data, $request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Репорт оновлено.',
                'report' => $reports->normalizeReportForAdminPayload($updatedReport),
            ]);
        }

        return redirect()
            ->route('question-reports.index')
            ->with('status', 'Репорт оновлено.');
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

    public function markDbChangedAsFixed(Request $request, QuestionReportFileStore $reports): RedirectResponse
    {
        $stats = $reports->markReportsWithSnapshotChangesAsFixed($request);
        $updated = (int) ($stats['updated'] ?? 0);

        $message = $updated > 0
            ? "Позначено виконаними репортів зі змінами в БД: {$updated}."
            : 'Немає невиконаних репортів зі змінами в БД.';

        if ((int) ($stats['failed'] ?? 0) > 0) {
            $message .= ' Частину файлів не вдалося оновити: '.$stats['failed'].'.';
        }

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
            'version' => ['nullable', 'in:v1,v2'],
            'report_ids' => ['nullable', 'array'],
            'report_ids.*' => ['string', 'max:255'],
        ]);

        $version = $data['version'] ?? 'v1';

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

        $generated = $version === 'v2'
            ? app(\App\Services\QuestionReportFixPromptV2Generator::class)->build($selectedReports)
            : $reports->buildFixPrompt($selectedReports);

        return view('admin.question-reports.index', [
            'reports' => $allReports,
            'reportsDirectory' => $reports->directory(),
            'issueCatalog' => app(QuestionReportIssueCatalog::class)->all(),
            'generatedPrompt' => $generated,
            'promptReportCount' => count($selectedReports),
            'promptVersion' => $version,
        ]);
    }

    /**
     * @param  array<string, array<int, mixed>>  $extraRules
     * @return array<string, mixed>
     */
    private function validateReportContent(Request $request, QuestionReportIssueCatalog $issueCatalog, array $extraRules = []): array
    {
        $allowedIssueTypes = $issueCatalog->keys();
        $validator = Validator::make($request->all(), array_merge($extraRules, [
            'issue_types' => ['nullable', 'array'],
            'issue_types.*' => ['string', Rule::in($allowedIssueTypes)],
            'issues' => ['nullable', 'array'],
            'issues.*' => ['string', Rule::in($allowedIssueTypes)],
            'comment' => ['nullable', 'string', 'max:4000'],
        ]));

        $validator->after(function ($validator) use ($request, $issueCatalog): void {
            $issueTypes = $issueCatalog->normalize(array_merge(
                is_array($request->input('issue_types')) ? $request->input('issue_types') : [],
                is_array($request->input('issues')) ? $request->input('issues') : []
            ));
            $comment = trim((string) $request->input('comment', ''));

            if ($issueTypes === [] && $comment === '') {
                $validator->errors()->add('issue_types', __('report_question.validation.issue_or_comment_required'));
            }
        });

        $data = $validator->validate();

        $data['issue_types'] = $issueCatalog->normalize(array_merge(
            is_array($data['issue_types'] ?? null) ? $data['issue_types'] : [],
            is_array($data['issues'] ?? null) ? $data['issues'] : []
        ));
        $data['comment'] = trim((string) ($data['comment'] ?? ''));

        return $data;
    }
}
