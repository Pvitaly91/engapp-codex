<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuestionReportFileStore
{
    private const DIRECTORY = 'question-reports';

    public function create(Question $question, array $data, Request $request): array
    {
        $question->loadMissing([
            'category',
            'source',
            'answers.option',
            'options',
        ]);

        $now = Carbon::now();
        $id = $this->makeReportId($now, $question);
        $relativePath = self::DIRECTORY.'/'.$id.'.json';

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
            'question' => [
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
                'options' => $question->options
                    ->pluck('option')
                    ->filter(fn ($option) => is_string($option) && trim($option) !== '')
                    ->values()
                    ->all(),
            ],
            'comment' => trim((string) $data['comment']),
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

        Storage::disk('local')->put(
            $path,
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
        );

        return $report;
    }

    public function delete(string $reportId): void
    {
        $path = $this->pathForReportId($reportId);

        abort_if($path === null, 404);

        Storage::disk('local')->delete($path);
    }

    public function buildFixPrompt(array $reports): string
    {
        $blocks = collect($reports)
            ->values()
            ->map(fn (array $report, int $index): string => $this->promptBlock($report, $index + 1))
            ->implode("\n\n");

        return trim(<<<PROMPT
Ти працюєш у Laravel репозиторії Gramlyze. Потрібно виправити зарепорчені питання у тестах.

Вимоги:
- Для кожного репорту знайди відповідний сидер або JSON definition за `seeder.file`, `seeder.class`, `question.uuid` або `question.id`.
- Виправ лише проблему, описану в коментарі адміна: граматику питання, неправильний варіант відповіді або пов'язані опції.
- Не змінюй unrelated питання.
- Після змін запусти релевантні тести або мінімальну перевірку синтаксису.
- У фінальній відповіді вкажи, які репорти виправлено і які файли змінено.

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

    private function readReportFile(string $path): ?array
    {
        $decoded = json_decode(Storage::disk('local')->get($path), true);

        if (! is_array($decoded)) {
            return null;
        }

        $decoded['file'] = $decoded['file'] ?? $path;
        $decoded['status'] = $decoded['status'] ?? 'open';

        return $decoded;
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

        return collect(Storage::disk('local')->files(self::DIRECTORY))
            ->first(function (string $path) use ($reportId): bool {
                $report = $this->readReportFile($path);

                return (string) ($report['id'] ?? '') === $reportId;
            });
    }

    private function promptBlock(array $report, int $number): string
    {
        $question = $report['question'] ?? [];
        $test = $report['test'] ?? [];
        $seeder = $question['seeder'] ?? [];
        $answers = collect($question['answers'] ?? [])
            ->map(fn ($answer): string => sprintf('- %s: %s', $answer['marker'] ?? 'marker', $answer['value'] ?? ''))
            ->implode("\n");
        $options = collect($question['options'] ?? [])
            ->map(fn ($option): string => '- '.$option)
            ->implode("\n");

        return trim(sprintf(
            <<<'REPORT'
%d. Репорт `%s`
Файл репорту: %s
Статус: %s
Коментар адміна: %s
Тест: %s
URL тесту: %s
Question ID: %s
Question UUID: %s
Питання: %s
Рівень: %s
Категорія: %s
Джерело: %s
Seeder class: %s
Seeder file: %s
Відповіді:
%s
Опції:
%s
REPORT,
            $number,
            $report['id'] ?? 'unknown',
            $report['file'] ?? '',
            $report['status'] ?? 'open',
            $report['comment'] ?? '',
            $test['slug'] ?? $test['name'] ?? '',
            $test['url'] ?? '',
            $question['id'] ?? '',
            $question['uuid'] ?? '',
            $question['text'] ?? '',
            $question['level'] ?? '',
            $question['category'] ?? '',
            data_get($question, 'source.name', ''),
            $seeder['class'] ?? '',
            $seeder['file'] ?? '',
            $answers !== '' ? $answers : '- Н/Д',
            $options !== '' ? $options : '- Н/Д',
        ));
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

    private function answersPayload(Question $question): array
    {
        return $question->answers
            ->sortBy(fn ($answer): string => $this->markerSortValue($answer->marker))
            ->map(fn ($answer): array => [
                'marker' => $answer->marker,
                'value' => $answer->option?->option,
            ])
            ->values()
            ->all();
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
