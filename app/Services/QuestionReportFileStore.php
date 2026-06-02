<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class QuestionReportFileStore
{
    private const DIRECTORY = 'question-reports';

    public function __construct(
        private QuestionReportIssueCatalog $issueCatalog = new QuestionReportIssueCatalog(),
        private QuestionReportSnapshotService $snapshotService = new QuestionReportSnapshotService(),
    ) {
    }

    public function create(Question $question, array $data, Request $request): array
    {
        $this->loadQuestionRelations($question);

        $now = Carbon::now();
        $id = $this->makeReportId($now, $question);
        $relativePath = self::DIRECTORY.'/'.$id.'.json';
        $issueTypes = $this->issueTypesFrom($data);
        $questionSnapshot = $this->snapshotService->buildForQuestion($question);

        $report = [
            'id' => $id,
            'status' => 'open',
            'reported_at' => $now->toIso8601String(),
            'admin' => $this->adminPayload($request),
            'test' => [
                'slug' => $data['test_slug'] ?? null,
                'name' => $data['test_name'] ?? null,
                'mode' => $data['mode'] ?? null,
                'url' => $data['url'] ?? $request->headers->get('referer'),
            ],
            'question' => $this->questionPayload($question),
            'question_snapshot' => $questionSnapshot,
            'issue_types' => $issueTypes,
            'issue_labels' => $this->issueCatalog->labels($issueTypes),
            'comment' => trim((string) ($data['comment'] ?? '')),
            'file' => $relativePath,
        ];

        Storage::disk('local')->makeDirectory(self::DIRECTORY);
        Storage::disk('local')->put(
            $relativePath,
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
        );

        return $report;
    }

    public function all(): array
    {
        if (! Storage::disk('local')->exists(self::DIRECTORY)) {
            return [];
        }

        return collect(Storage::disk('local')->files(self::DIRECTORY))
            ->filter(fn (string $path): bool => Str::endsWith($path, '.json'))
            ->map(fn (string $path): ?array => $this->readReportFile($path))
            ->filter()
            ->sortByDesc(fn (array $report): string => (string) ($report['reported_at'] ?? ''))
            ->values()
            ->all();
    }

    public function updateStatus(string $reportId, string $status, Request $request): array
    {
        $path = $this->pathForReportId($reportId);

        abort_if($path === null, 404);

        $report = $this->readReportFile($path);

        abort_if($report === null, 404);

        $now = Carbon::now()->toIso8601String();

        $report['status'] = $status;
        $report['status_updated_at'] = $now;

        if ($status === 'fixed') {
            $report['fixed_at'] = $now;
            $report['fixed_by'] = $this->adminPayload($request);
        } else {
            unset($report['fixed_at'], $report['fixed_by']);
        }

        $this->writeReportFile($path, $report);

        return $report;
    }

    public function updateReport(string $reportId, array $data, Request $request): array
    {
        $path = $this->pathForReportId($reportId);

        abort_if($path === null, 404);

        $report = $this->readReportFile($path);

        abort_if($report === null, 404);

        $issueTypes = $this->issueTypesFrom($data);
        $now = Carbon::now()->toIso8601String();

        $report['issue_types'] = $issueTypes;
        $report['issue_labels'] = $this->issueCatalog->labels($issueTypes);
        $report['comment'] = trim((string) ($data['comment'] ?? ''));
        $report['edited_at'] = $now;
        $report['edited_by'] = $this->adminPayload($request);
        unset($report['issues']);

        $written = $this->writeReportFile($path, $report);

        abort_if(! $written, 500, 'Не вдалося записати report файл.');

        return $this->readReportFile($path) ?? $report;
    }

    public function delete(string $reportId): void
    {
        $path = $this->pathForReportId($reportId);

        abort_if($path === null, 404);

        Storage::disk('local')->delete($path);
    }

    public function buildFixPrompt(array $reports): string
    {
        $reports = collect($reports)
            ->filter(fn ($report): bool => is_array($report))
            ->map(fn (array $report): array => $this->decorateReport($this->normalizeReportArray($report)))
            ->values()
            ->all();

        $blocks = collect($reports)
            ->map(fn (array $report, int $index): string => $this->promptBlock($report, $index + 1))
            ->implode("\n\n");

        $directives = $this->collectIssueDirectives($reports);
        $directiveSection = $directives === []
            ? ''
            : "\n\nIssue-specific instructions:\n".implode("\n", array_map(
                static fn (string $directive): string => '- '.$directive,
                $directives,
            ));

        $seederSweep = $this->buildSeederSweepSection($reports);

        return trim(<<<PROMPT
Ти працюєш у Laravel репозиторії Gramlyze. Потрібно виправити зарепорчені питання у тестах.

Вимоги:
- Для кожного репорту знайди відповідний сидер або JSON definition за `seeder.file`, `seeder.class`, `question.uuid` або `question.id`.
- Виправ проблему, описану в коментарі адміна, та всі issue, відмічені checkbox-ами для цього репорту.
- Якщо report має seeder class або seeder file, перевір усі питання цього seeder і виправ аналогічні проблеми, а не тільки reported question.
- Якщо seeder не вказаний, знайди source seeder за `question.uuid` або `question.id`.
- Не змінюй unrelated питання у seeders, до яких немає report.
- Після змін запусти релевантні тести або мінімальну перевірку синтаксису.
- У фінальній відповіді вкажи, які report виправлено, які файли змінено, і які додаткові питання виправлено під час seeder-wide перевірки.{$directiveSection}{$seederSweep}

Репорти для виправлення:

{$blocks}
PROMPT);
    }

    public function openReports(array $reports): array
    {
        return collect($reports)
            ->filter(fn (array $report): bool => ($report['status'] ?? 'open') !== 'fixed')
            ->values()
            ->all();
    }

    public function selectReports(array $reports, array $ids): array
    {
        $selected = collect($ids)
            ->map(fn ($id): string => trim((string) $id))
            ->filter()
            ->unique()
            ->flip();

        return collect($reports)
            ->filter(fn (array $report): bool => $selected->has((string) ($report['id'] ?? '')))
            ->values()
            ->all();
    }

    public function directory(): string
    {
        return Storage::disk('local')->path(self::DIRECTORY);
    }

    public function currentSnapshotForReport(array $report): ?array
    {
        return $this->snapshotService->buildCurrentByReport($report);
    }

    public function compareReportSnapshotToCurrent(array $report): array
    {
        return $this->snapshotService->compare(
            is_array($report['question_snapshot'] ?? null) ? $report['question_snapshot'] : null,
            $this->currentSnapshotForReport($report)
        );
    }

    public function backfillMissingSnapshots(bool $dryRun = false, bool $onlyOpen = false): array
    {
        $stats = [
            'total' => 0,
            'already_had_snapshot' => 0,
            'backfilled' => 0,
            'would_backfill' => 0,
            'skipped_question_not_found' => 0,
            'skipped_fixed' => 0,
            'invalid_json' => 0,
            'failed' => 0,
            'files' => [],
        ];

        if (! Storage::disk('local')->exists(self::DIRECTORY)) {
            return $stats;
        }

        foreach (Storage::disk('local')->files(self::DIRECTORY) as $path) {
            if (! Str::endsWith($path, '.json')) {
                continue;
            }

            $stats['total']++;
            $decoded = json_decode(Storage::disk('local')->get($path), true);

            if (! is_array($decoded)) {
                $stats['invalid_json']++;
                $stats['files'][] = ['file' => $path, 'status' => 'invalid_json'];
                continue;
            }

            if ($onlyOpen && ($decoded['status'] ?? 'open') === 'fixed') {
                $stats['skipped_fixed']++;
                $stats['files'][] = ['file' => $path, 'status' => 'skipped_fixed'];
                continue;
            }

            if (is_array($decoded['question_snapshot'] ?? null)) {
                $stats['already_had_snapshot']++;
                $stats['files'][] = ['file' => $path, 'status' => 'already_had_snapshot'];
                continue;
            }

            $snapshot = $this->snapshotService->buildCurrentByReport($decoded);

            if ($snapshot === null) {
                $stats['skipped_question_not_found']++;
                $stats['files'][] = ['file' => $path, 'status' => 'question_not_found'];

                if (! $dryRun) {
                    $decoded['snapshot_backfill_error'] = 'Question not found by uuid/id';
                    $this->writeReportFile($path, $decoded);
                }

                continue;
            }

            if ($dryRun) {
                $stats['would_backfill']++;
                $stats['files'][] = ['file' => $path, 'status' => 'would_backfill'];
                continue;
            }

            $decoded['question_snapshot'] = $snapshot;
            $decoded['snapshot_backfilled_at'] = Carbon::now()->toIso8601String();
            $decoded['snapshot_backfill_source'] = 'current_db_state';
            unset($decoded['snapshot_backfill_error']);

            if ($this->writeReportFile($path, $decoded)) {
                $stats['backfilled']++;
                $stats['files'][] = ['file' => $path, 'status' => 'backfilled'];
            } else {
                $stats['failed']++;
                $stats['files'][] = ['file' => $path, 'status' => 'failed'];
            }
        }

        return $stats;
    }

    /**
     * @param  array<int, string>  $uuids
     * @return array<int, array<string, mixed>>
     */
    public function reportsForQuestionUuids(array $uuids): array
    {
        $uuidSet = $this->normalizedLookupSet($uuids);

        if ($uuidSet === []) {
            return [];
        }

        return collect($this->all())
            ->filter(fn (array $report): bool => isset($uuidSet[(string) data_get($report, 'question.uuid', '')]))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $uuids
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function openReportsByQuestionUuid(array $uuids): array
    {
        return $this->openReportsByQuestionIdentifiers([], $uuids);
    }

    /**
     * @param  array<int, int|string>  $ids
     * @param  array<int, string>  $uuids
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function openReportsByQuestionIdentifiers(array $ids = [], array $uuids = []): array
    {
        $idSet = $this->normalizedLookupSet($ids);
        $uuidSet = $this->normalizedLookupSet($uuids);

        if ($idSet === [] && $uuidSet === []) {
            return [];
        }

        $map = [];

        foreach ($this->all() as $report) {
            if (($report['status'] ?? 'open') === 'fixed') {
                continue;
            }

            $questionId = (string) data_get($report, 'question.id', '');
            $questionUuid = (string) data_get($report, 'question.uuid', '');

            if (! isset($idSet[$questionId]) && ! isset($uuidSet[$questionUuid])) {
                continue;
            }

            $payload = $this->normalizeReportForAdminPayload($report);

            if (isset($idSet[$questionId])) {
                $map[$questionId][] = $payload;
            }

            if ($questionUuid !== '' && isset($uuidSet[$questionUuid]) && $questionUuid !== $questionId) {
                $map[$questionUuid][] = $payload;
            }
        }

        return $map;
    }

    /**
     * Backward-compatible lookup for older callers. New code should pass the
     * current test question ids or UUIDs through openReportsByQuestionIdentifiers().
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function openReportsByQuestion(?array $reports = null): array
    {
        $reports = $reports ?? $this->all();
        $map = [];

        foreach ($reports as $report) {
            if (! is_array($report) || ($report['status'] ?? 'open') === 'fixed') {
                continue;
            }

            $payload = $this->normalizeReportForAdminPayload($report);
            $questionId = (string) data_get($report, 'question.id', '');
            $questionUuid = (string) data_get($report, 'question.uuid', '');

            if ($questionId !== '') {
                $map[$questionId][] = $payload;
            }

            if ($questionUuid !== '' && $questionUuid !== $questionId) {
                $map[$questionUuid][] = $payload;
            }
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeReportForAdminPayload(array $report): array
    {
        $report = $this->normalizeReportArray($report);

        return [
            'id' => $report['id'] ?? null,
            'status' => $report['status'] ?? 'open',
            'reported_at' => $report['reported_at'] ?? null,
            'issue_types' => $report['issue_types'],
            'issue_labels' => $report['issue_labels'],
            'comment' => (string) ($report['comment'] ?? ''),
            'seeder' => [
                'class' => data_get($report, 'question.seeder.class'),
                'file' => data_get($report, 'question.seeder.file'),
            ],
            'test' => [
                'slug' => data_get($report, 'test.slug'),
                'name' => data_get($report, 'test.name'),
                'mode' => data_get($report, 'test.mode'),
                'url' => data_get($report, 'test.url'),
            ],
            'question_id' => data_get($report, 'question.id'),
            'question_uuid' => data_get($report, 'question.uuid'),
        ];
    }

    private function readReportFile(string $path): ?array
    {
        $decoded = json_decode(Storage::disk('local')->get($path), true);

        if (! is_array($decoded)) {
            return null;
        }

        $decoded['file'] = $decoded['file'] ?? $path;
        $decoded['status'] = $decoded['status'] ?? 'open';

        return $this->decorateReport($this->normalizeReportArray($decoded));
    }

    private function normalizeReportArray(array $report): array
    {
        $issueTypes = $this->issueTypesFrom($report);

        $report['issue_types'] = $issueTypes;
        $report['issue_labels'] = $this->issueCatalog->labels($issueTypes);
        $report['comment'] = (string) ($report['comment'] ?? '');
        $report['status'] = $report['status'] ?? 'open';

        return $report;
    }

    private function decorateReport(array $report): array
    {
        $report['has_question_snapshot'] = is_array($report['question_snapshot'] ?? null);
        $report['snapshot_status'] = $this->snapshotStatus($report);
        $report['current_question_snapshot'] = $this->currentSnapshotForReport($report);
        $report['snapshot_diff'] = $this->snapshotService->compare(
            $report['has_question_snapshot'] ? $report['question_snapshot'] : null,
            $report['current_question_snapshot']
        );

        return $report;
    }

    private function snapshotStatus(array $report): string
    {
        if (filled($report['snapshot_backfill_error'] ?? null)) {
            return 'error';
        }

        if (! is_array($report['question_snapshot'] ?? null)) {
            return 'missing';
        }

        return filled($report['snapshot_backfill_source'] ?? null)
            || data_get($report, 'question_snapshot.snapshot_source') === 'current_db_state'
            ? 'backfilled'
            : 'original';
    }

    private function writeReportFile(string $path, array $report): bool
    {
        unset(
            $report['has_question_snapshot'],
            $report['snapshot_status'],
            $report['current_question_snapshot'],
            $report['snapshot_diff']
        );

        return Storage::disk('local')->put(
            $path,
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
        ) !== false;
    }

    private function pathForReportId(string $reportId): ?string
    {
        $reportId = trim($reportId);

        if ($reportId === '' || preg_match('/^[A-Za-z0-9._-]+$/', $reportId) !== 1) {
            return null;
        }

        $directPath = self::DIRECTORY.'/'.$reportId.'.json';
        if (Storage::disk('local')->exists($directPath)) {
            return $directPath;
        }

        if (! Storage::disk('local')->exists(self::DIRECTORY)) {
            return null;
        }

        return collect(Storage::disk('local')->files(self::DIRECTORY))
            ->first(function (string $path) use ($reportId): bool {
                $report = $this->readReportFile($path);

                return (string) ($report['id'] ?? '') === $reportId;
            });
    }

    /**
     * @param  array<int, array<string, mixed>>  $reports
     * @return array<int, string>
     */
    private function collectIssueDirectives(array $reports): array
    {
        $byIssue = [];

        foreach ($reports as $report) {
            foreach ($this->issueTypesFrom($report) as $issueType) {
                $byIssue[$issueType] = true;
            }
        }

        return $this->issueCatalog->instructions(array_keys($byIssue));
    }

    /**
     * @param  array<int, array<string, mixed>>  $reports
     */
    private function buildSeederSweepSection(array $reports): string
    {
        $groups = [];
        $unknown = [];

        foreach ($reports as $report) {
            $seederClass = trim((string) (
                data_get($report, 'question_snapshot.question.seeder.class')
                ?: data_get($report, 'current_question_snapshot.question.seeder.class')
                ?: data_get($report, 'question.seeder.class', '')
            ));
            $seederFile = trim((string) (
                data_get($report, 'question_snapshot.question.seeder.file')
                ?: data_get($report, 'current_question_snapshot.question.seeder.file')
                ?: data_get($report, 'question.seeder.file', '')
            ));
            $questionRef = $this->questionRef($report);

            if ($seederClass === '' && $seederFile === '') {
                $unknown[] = $questionRef;
                continue;
            }

            $key = $seederClass !== '' ? $seederClass : $seederFile;

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'class' => $seederClass,
                    'file' => $seederFile,
                    'question_refs' => [],
                ];
            }

            if ($groups[$key]['class'] === '' && $seederClass !== '') {
                $groups[$key]['class'] = $seederClass;
            }

            if ($groups[$key]['file'] === '' && $seederFile !== '') {
                $groups[$key]['file'] = $seederFile;
            }

            $groups[$key]['question_refs'][$questionRef] = true;
        }

        $blocks = [];

        foreach ($groups as $group) {
            $display = $group['class'] !== '' ? $group['class'] : $group['file'];
            $refs = implode(', ', array_keys($group['question_refs']));
            $fileLine = $group['file'] !== '' ? "\nSeeder file: {$group['file']}" : '';

            $blocks[] = trim(<<<BLOCK
Seeder: {$display}{$fileLine}
Reported questions from this seeder: {$refs}
Оскільки проблема знайдена в питанні з seeder {$display}, перевір усі питання, які генеруються цим seeder, а не тільки reported question. Знайди і виправ аналогічні проблеми у всьому seeder. Особливо перевір verb_hints, accepted answers, options, переклади, відповідність темі та рівню.
BLOCK);
        }

        foreach (array_unique($unknown) as $questionRef) {
            $blocks[] = "Seeder для цього питання не знайдено. Знайди source seeder за question.uuid або question.id. Question: {$questionRef}.";
        }

        if ($blocks === []) {
            return '';
        }

        return "\n\nСидери для повної перевірки:\n".implode("\n\n", $blocks);
    }

    private function promptBlock(array $report, int $number): string
    {
        $report = $this->decorateReport($this->normalizeReportArray($report));
        $snapshot = is_array($report['question_snapshot'] ?? null) ? $report['question_snapshot'] : null;
        $current = is_array($report['current_question_snapshot'] ?? null) ? $report['current_question_snapshot'] : $this->currentSnapshotForReport($report);
        $diff = $report['snapshot_diff'] ?? $this->snapshotService->compare($snapshot, $current);
        $question = $this->promptQuestionData($report, $snapshot, $current);
        $test = $report['test'] ?? [];
        $issueTypes = $report['issue_types'];
        $issueLabels = $report['issue_labels'];
        $issueInstructions = $this->issueCatalog->instructions($issueTypes);
        $comment = trim((string) ($report['comment'] ?? ''));
        $seederClass = trim((string) data_get($question, 'seeder.class', ''));
        $seederFile = trim((string) data_get($question, 'seeder.file', ''));
        $seederNote = $seederClass === '' && $seederFile === ''
            ? 'Seeder для цього питання не знайдено. Знайди source seeder за question.uuid або question.id.'
            : '';
        $snapshotWarning = $this->snapshotPromptWarning($report);

        return trim(sprintf(
            <<<'REPORT'
%d. Репорт `%s`
Файл репорту: %s
Статус: %s
Snapshot status: %s
Snapshot note: %s
Issue keys: %s
Issue labels: %s
Issue instructions:
%s
Коментар адміна: %s
Тест: %s
Mode: %s
URL тесту: %s
Question ID: %s
Question UUID: %s
Питання: %s
Тип: %s
Рівень: %s
Складність: %s
Категорія: %s
Джерело: %s
Seeder class: %s
Seeder file: %s
%s
Diff summary:
%s

Дамп питання на момент report:
%s

Поточне питання в базі:
%s
REPORT,
            $number,
            $report['id'] ?? 'unknown',
            $report['file'] ?? '',
            $report['status'] ?? 'open',
            $report['snapshot_status'] ?? 'missing',
            $snapshotWarning,
            $issueTypes === [] ? '(none)' : implode(', ', $issueTypes),
            $issueLabels === [] ? '(тип помилки не вказано)' : implode(', ', $issueLabels),
            $this->formatLines($issueInstructions),
            $comment !== '' ? $comment : '(коментар відсутній)',
            $test['slug'] ?? $test['name'] ?? '',
            $test['mode'] ?? '',
            $test['url'] ?? '',
            $question['id'] ?? '',
            $question['uuid'] ?? '',
            $question['text'] ?? '',
            $question['type'] ?? '',
            $question['level'] ?? '',
            $question['difficulty'] ?? '',
            $question['category'] ?? '',
            data_get($question, 'source.name', ''),
            $seederClass,
            $seederFile,
            $seederNote,
            $this->formatDiffForPrompt($diff),
            $this->formatSnapshotForPrompt($snapshot),
            $this->formatSnapshotForPrompt($current),
        ));
    }

    private function promptQuestionData(array $report, ?array $snapshot, ?array $current): array
    {
        return [
            'id' => data_get($snapshot, 'question.id') ?? data_get($current, 'question.id') ?? data_get($report, 'question.id'),
            'uuid' => data_get($snapshot, 'question.uuid') ?? data_get($current, 'question.uuid') ?? data_get($report, 'question.uuid'),
            'text' => data_get($snapshot, 'question.text') ?? data_get($current, 'question.text') ?? data_get($report, 'question.text'),
            'type' => data_get($snapshot, 'question.type') ?? data_get($current, 'question.type') ?? data_get($report, 'question.type'),
            'level' => data_get($snapshot, 'question.level') ?? data_get($current, 'question.level') ?? data_get($report, 'question.level'),
            'difficulty' => data_get($snapshot, 'question.difficulty') ?? data_get($current, 'question.difficulty') ?? data_get($report, 'question.difficulty'),
            'category' => data_get($snapshot, 'question.category.name') ?? data_get($current, 'question.category.name') ?? data_get($report, 'question.category'),
            'source' => [
                'name' => data_get($snapshot, 'question.source.name') ?? data_get($current, 'question.source.name') ?? data_get($report, 'question.source.name'),
            ],
            'seeder' => [
                'class' => data_get($snapshot, 'question.seeder.class') ?? data_get($current, 'question.seeder.class') ?? data_get($report, 'question.seeder.class'),
                'file' => data_get($snapshot, 'question.seeder.file') ?? data_get($current, 'question.seeder.file') ?? data_get($report, 'question.seeder.file'),
            ],
        ];
    }

    private function snapshotPromptWarning(array $report): string
    {
        return match ($report['snapshot_status'] ?? 'missing') {
            'backfilled' => 'Snapshot was backfilled from current DB state after the report, so it may not represent the original report-time state.',
            'missing' => 'Snapshot is missing. Use current DB state and report comment/issue types, but be careful because original report-time state is unavailable.',
            'error' => 'Snapshot is missing or failed to backfill: '.(string) ($report['snapshot_backfill_error'] ?? 'unknown error'),
            default => 'Original report-time snapshot is available.',
        };
    }

    private function formatDiffForPrompt(array $diff): string
    {
        $lines = [];

        foreach ($diff as $key => $section) {
            if ($key === 'has_changes' || ! is_array($section)) {
                continue;
            }

            $lines[] = sprintf('- %s: %s', $section['label'] ?? $key, $section['status_label'] ?? $section['status'] ?? '');
        }

        return $lines !== [] ? implode("\n", $lines) : '- Немає даних';
    }

    private function formatSnapshotForPrompt(?array $snapshot): string
    {
        if ($snapshot === null) {
            return '- Немає даних';
        }

        return trim(sprintf(
            <<<'SNAPSHOT'
Snapshot source: %s
Captured at: %s
Question ID: %s
Question UUID: %s
Question text: %s
Type: %s
Level: %s
Difficulty: %s
Category: %s
Source: %s
Seeder class: %s
Seeder file: %s
Answers:
%s
Options:
%s
Verb hints:
%s
Hints:
%s
Variants:
%s
Tags:
%s
Saved tests:
%s
SNAPSHOT,
            $snapshot['snapshot_source'] ?? '',
            $snapshot['captured_at'] ?? '',
            data_get($snapshot, 'question.id', ''),
            data_get($snapshot, 'question.uuid', ''),
            data_get($snapshot, 'question.text', ''),
            data_get($snapshot, 'question.type', ''),
            data_get($snapshot, 'question.level', ''),
            data_get($snapshot, 'question.difficulty', ''),
            data_get($snapshot, 'question.category.name', ''),
            data_get($snapshot, 'question.source.name', ''),
            data_get($snapshot, 'question.seeder.class', ''),
            data_get($snapshot, 'question.seeder.file', ''),
            $this->formatAnswersForPrompt($snapshot['answers'] ?? []),
            $this->formatOptionsForPrompt($snapshot['options'] ?? []),
            $this->formatVerbHintsForPrompt($snapshot['verb_hints'] ?? []),
            $this->formatHintsForPrompt($snapshot['hints'] ?? []),
            $this->formatVariantsForPrompt($snapshot['variants'] ?? []),
            $this->formatTagsForPrompt($snapshot['tags'] ?? []),
            $this->formatSavedTestsForPrompt($snapshot['saved_tests'] ?? []),
        ));
    }

    private function loadQuestionRelations(Question $question): void
    {
        $relations = ['category', 'source', 'answers.option', 'options'];

        foreach (['verbHints.option', 'hints', 'variants', 'tags'] as $relation) {
            $method = Str::before($relation, '.');

            if (method_exists($question, $method) && $this->relationStorageExists($question, $method)) {
                $relations[] = $relation;
            }
        }

        $question->loadMissing($relations);
    }

    private function relationStorageExists(Question $question, string $method): bool
    {
        try {
            $relation = $question->{$method}();

            if (method_exists($relation, 'getRelated') && ! Schema::hasTable($relation->getRelated()->getTable())) {
                return false;
            }

            if (method_exists($relation, 'getTable') && ! Schema::hasTable($relation->getTable())) {
                return false;
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function questionPayload(Question $question): array
    {
        return [
            'id' => $question->id,
            'uuid' => $question->uuid,
            'text' => $question->question,
            'type' => $question->type,
            'level' => $question->level,
            'difficulty' => $question->difficulty,
            'category' => $question->category?->name,
            'source' => [
                'id' => $question->source_id,
                'name' => $question->source?->name,
            ],
            'seeder' => [
                'class' => $question->seeder,
                'file' => $this->seederFilePath($question->seeder),
            ],
            'answers' => $this->answersPayload($question),
            'options' => $this->optionsPayload($question),
            'verb_hints' => $this->verbHintsPayload($question),
            'hints' => $this->hintsPayload($question),
            'variants' => $this->variantsPayload($question),
            'tags' => $this->tagsPayload($question),
        ];
    }

    private function answersPayload(Question $question): array
    {
        return $question->answers
            ->sortBy(fn ($answer): string => $this->markerSortValue($answer->marker))
            ->map(function ($answer): array {
                $rawAnswer = $answer->getAttribute('answer');
                $optionValue = $answer->option?->option;

                return [
                    'marker' => $answer->marker,
                    'answer' => $rawAnswer,
                    'option_value' => $optionValue,
                    'option_id' => $answer->option_id,
                    'verb_hint' => $answer->getAttribute('verb_hint'),
                    'value' => $optionValue ?? $rawAnswer,
                ];
            })
            ->values()
            ->all();
    }

    private function optionsPayload(Question $question): array
    {
        return $question->options
            ->map(fn ($option): array => [
                'id' => $option->id,
                'value' => $option->option,
                'option' => $option->option,
                'pivot_flag' => $option->pivot?->flag,
            ])
            ->values()
            ->all();
    }

    private function verbHintsPayload(Question $question): array
    {
        if (! $question->relationLoaded('verbHints')) {
            return [];
        }

        return $question->verbHints
            ->sortBy(fn ($hint): string => $this->markerSortValue($hint->marker))
            ->map(fn ($hint): array => [
                'marker' => $hint->marker,
                'option_id' => $hint->option_id,
                'option_value' => $hint->option?->option,
                'value' => $hint->option?->option,
            ])
            ->values()
            ->all();
    }

    private function hintsPayload(Question $question): array
    {
        if (! $question->relationLoaded('hints')) {
            return [];
        }

        return $question->hints
            ->map(fn ($hint): array => [
                'provider' => $hint->provider,
                'locale' => $hint->locale,
                'hint' => $hint->hint,
            ])
            ->values()
            ->all();
    }

    private function variantsPayload(Question $question): array
    {
        if (! $question->relationLoaded('variants')) {
            return [];
        }

        return $question->variants
            ->map(fn ($variant): array => [
                'id' => $variant->id,
                'text' => $variant->text,
            ])
            ->values()
            ->all();
    }

    private function tagsPayload(Question $question): array
    {
        if (! $question->relationLoaded('tags')) {
            return [];
        }

        return $question->tags
            ->map(fn ($tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'category' => $tag->category,
            ])
            ->values()
            ->all();
    }

    private function formatAnswersForPrompt(array $answers): string
    {
        $lines = collect($answers)
            ->map(function ($answer): string {
                if (! is_array($answer)) {
                    return '- '.(string) $answer;
                }

                return sprintf(
                    '- marker=%s | answer=%s | option_value=%s | option_id=%s | verb_hint=%s',
                    $answer['marker'] ?? '',
                    $answer['answer'] ?? '',
                    $answer['option_value'] ?? ($answer['value'] ?? ''),
                    $answer['option_id'] ?? '',
                    $answer['verb_hint'] ?? ''
                );
            })
            ->filter(fn (string $line): bool => trim($line) !== '-')
            ->implode("\n");

        return $lines !== '' ? $lines : '- Н/Д';
    }

    private function formatOptionsForPrompt(array $options): string
    {
        $lines = collect($options)
            ->map(function ($option): string {
                if (! is_array($option)) {
                    return '- '.(string) $option;
                }

                return sprintf(
                    '- option_id=%s | value=%s | pivot_flag=%s',
                    $option['id'] ?? '',
                    $option['value'] ?? ($option['option'] ?? ''),
                    data_get($option, 'pivot.flag', $option['pivot_flag'] ?? '')
                );
            })
            ->filter(fn (string $line): bool => trim($line) !== '-')
            ->implode("\n");

        return $lines !== '' ? $lines : '- Н/Д';
    }

    private function formatVerbHintsForPrompt(array $verbHints): string
    {
        $lines = collect($verbHints)
            ->map(function ($hint): string {
                if (! is_array($hint)) {
                    return '- '.(string) $hint;
                }

                return sprintf(
                    '- marker=%s | option_value=%s | option_id=%s',
                    $hint['marker'] ?? '',
                    $hint['option_value'] ?? ($hint['value'] ?? ($hint['hint'] ?? '')),
                    $hint['option_id'] ?? ''
                );
            })
            ->filter(fn (string $line): bool => trim($line) !== '-')
            ->implode("\n");

        return $lines !== '' ? $lines : '- Н/Д';
    }

    private function formatHintsForPrompt(array $hints): string
    {
        $lines = collect($hints)
            ->map(function ($hint): string {
                if (! is_array($hint)) {
                    return '- '.(string) $hint;
                }

                return sprintf(
                    '- provider=%s | locale=%s | hint=%s',
                    $hint['provider'] ?? '',
                    $hint['locale'] ?? '',
                    $hint['hint'] ?? ''
                );
            })
            ->filter(fn (string $line): bool => trim($line) !== '-')
            ->implode("\n");

        return $lines !== '' ? $lines : '- Н/Д';
    }

    private function formatVariantsForPrompt(array $variants): string
    {
        $lines = collect($variants)
            ->map(function ($variant): string {
                if (! is_array($variant)) {
                    return '- '.(string) $variant;
                }

                return sprintf('- variant_id=%s | text=%s', $variant['id'] ?? '', $variant['text'] ?? '');
            })
            ->filter(fn (string $line): bool => trim($line) !== '-')
            ->implode("\n");

        return $lines !== '' ? $lines : '- Н/Д';
    }

    private function formatTagsForPrompt(array $tags): string
    {
        $lines = collect($tags)
            ->map(function ($tag): string {
                if (! is_array($tag)) {
                    return '- '.(string) $tag;
                }

                return sprintf(
                    '- %s%s',
                    $tag['name'] ?? '',
                    filled($tag['category'] ?? null) ? ' ('.$tag['category'].')' : ''
                );
            })
            ->filter(fn (string $line): bool => trim($line) !== '-')
            ->implode("\n");

        return $lines !== '' ? $lines : '- Н/Д';
    }

    private function formatSavedTestsForPrompt(array $savedTests): string
    {
        $lines = collect($savedTests)
            ->map(function ($test): string {
                if (! is_array($test)) {
                    return '- '.(string) $test;
                }

                return sprintf(
                    '- id=%s | uuid=%s | slug=%s | name=%s | position=%s',
                    $test['id'] ?? '',
                    $test['uuid'] ?? '',
                    $test['slug'] ?? '',
                    $test['name'] ?? '',
                    $test['position'] ?? ''
                );
            })
            ->filter(fn (string $line): bool => trim($line) !== '-')
            ->implode("\n");

        return $lines !== '' ? $lines : '- Н/Д';
    }

    private function formatLines(array $lines): string
    {
        $formatted = collect($lines)
            ->map(fn (string $line): string => '- '.$line)
            ->implode("\n");

        return $formatted !== '' ? $formatted : '- Н/Д';
    }

    private function issueTypesFrom(array $source): array
    {
        $raw = [];

        foreach (['issue_types', 'issues'] as $key) {
            $value = $source[$key] ?? [];

            if (is_array($value)) {
                $raw = array_merge($raw, $value);
            }
        }

        return $this->issueCatalog->normalize($raw);
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<string, true>
     */
    private function normalizedLookupSet(array $values): array
    {
        $set = [];

        foreach ($values as $value) {
            $key = trim((string) $value);

            if ($key !== '') {
                $set[$key] = true;
            }
        }

        return $set;
    }

    private function questionRef(array $report): string
    {
        $uuid = trim((string) (
            data_get($report, 'question_snapshot.question.uuid')
            ?: data_get($report, 'current_question_snapshot.question.uuid')
            ?: data_get($report, 'question.uuid', '')
        ));

        if ($uuid !== '') {
            return $uuid;
        }

        $id = trim((string) (
            data_get($report, 'question_snapshot.question.id')
            ?: data_get($report, 'current_question_snapshot.question.id')
            ?: data_get($report, 'question.id', '')
        ));

        return $id !== '' ? 'id:'.$id : (string) ($report['id'] ?? 'unknown');
    }

    private function makeReportId(Carbon $now, Question $question): string
    {
        $questionKey = $question->uuid ?: 'q'.$question->id;

        return sprintf(
            '%s-q%s-%s-%s',
            $now->format('Ymd-His-v'),
            $question->id,
            Str::lower(Str::slug((string) $questionKey)),
            Str::lower(Str::random(6))
        );
    }

    private function adminPayload(Request $request): array
    {
        $user = Auth::user();

        return [
            'user_id' => $user?->id ?? $request->session()->get('admin_user_id'),
            'name' => $user?->name,
            'email' => $user?->email,
        ];
    }

    private function markerSortValue(?string $marker): string
    {
        $normalized = strtolower(trim((string) $marker));

        if (preg_match('/^([a-z_]+)(\d+)$/', $normalized, $matches) === 1) {
            return sprintf('%s%08d', $matches[1], (int) $matches[2]);
        }

        return $normalized;
    }

    private function seederFilePath(?string $seederClass): ?string
    {
        $normalized = str_replace('/', '\\', trim((string) $seederClass));

        if ($normalized === '') {
            return null;
        }

        $prefix = 'Database\\Seeders\\';
        if (! Str::startsWith($normalized, $prefix)) {
            return null;
        }

        $relativeClass = Str::after($normalized, $prefix);
        $relativePath = 'database/seeders/'.str_replace('\\', '/', $relativeClass).'.php';

        return file_exists(base_path($relativePath)) ? $relativePath : null;
    }
}
