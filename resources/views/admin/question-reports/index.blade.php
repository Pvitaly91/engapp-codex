@extends('layouts.app')

@section('title', 'Репорти питань')

@section('content')
    <style>
        .question-report-list-table,
        .question-report-list-table thead,
        .question-report-list-table tbody,
        .question-report-list-table tr,
        .question-report-list-table th,
        .question-report-list-table td {
            display: block;
            width: 100%;
        }

        .question-report-list-table thead {
            display: none;
        }

        .question-report-list-table tbody {
            display: grid;
            gap: 1rem;
        }

        .question-report-list-table tr {
            border: 1px solid rgb(226 232 240);
            border-radius: 1rem;
            background: #fff;
            padding: 1rem;
            box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
        }

        .question-report-list-table td {
            padding: 0 !important;
            white-space: normal !important;
        }

        .question-report-list-table td > *,
        .question-report-list-table details,
        .question-report-list-table form,
        .question-report-list-table textarea,
        .question-report-list-table code {
            max-width: none !important;
            width: 100%;
        }

        .question-report-list-table td + td {
            margin-top: 1rem;
            border-top: 1px solid rgb(241 245 249);
            padding-top: 1rem !important;
        }

        .question-report-list-table td:nth-child(n + 2)::before {
            display: block;
            margin-bottom: 0.5rem;
            color: rgb(100 116 139);
            content: "";
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .question-report-list-table td:nth-child(2)::before { content: "Статус"; }
        .question-report-list-table td:nth-child(3)::before { content: "Час"; }
        .question-report-list-table td:nth-child(4)::before { content: "Питання"; }
        .question-report-list-table td:nth-child(5)::before { content: "Сидер і коментар"; }
        .question-report-list-table td:nth-child(6)::before { content: "Дії"; }
    </style>

    @php
        $reportsCollection = collect($reports);
        $openCount = $reportsCollection->filter(fn ($report) => ($report['status'] ?? 'open') !== 'fixed')->count();
        $fixedCount = $reportsCollection->filter(fn ($report) => ($report['status'] ?? 'open') === 'fixed')->count();
        $snapshotCounts = $reportsCollection->countBy(fn ($report) => $report['snapshot_status'] ?? 'missing');
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 py-8">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Репорти питань</h1>
                <p class="mt-2 text-sm text-slate-500">
                    Помилки зберігаються як JSON-файли у <code class="rounded bg-slate-100 px-1.5 py-0.5">{{ $reportsDirectory }}</code>.
                    Статус також пишеться в ці файли, тому зміни можна закомітити й запушити.
                </p>
            </div>
            <a href="{{ route('saved-tests.list') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                До тестів
            </a>
        </header>

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->has('prompt'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ $errors->first('prompt') }}
            </div>
        @endif

        @if($errors->any() && ! $errors->has('prompt'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="grid gap-3 sm:grid-cols-3 xl:grid-cols-7">
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Всього</div>
                <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $reportsCollection->count() }}</div>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-amber-700">Невиконані</div>
                <div class="mt-1 text-2xl font-semibold text-amber-900">{{ $openCount }}</div>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Виправлені</div>
                <div class="mt-1 text-2xl font-semibold text-emerald-900">{{ $fixedCount }}</div>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-white px-4 py-3 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Snapshot original</div>
                <div class="mt-1 text-2xl font-semibold text-emerald-900">{{ $snapshotCounts->get('original', 0) }}</div>
            </div>
            <div class="rounded-xl border border-blue-200 bg-white px-4 py-3 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">Snapshot backfilled</div>
                <div class="mt-1 text-2xl font-semibold text-blue-900">{{ $snapshotCounts->get('backfilled', 0) }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Snapshot missing</div>
                <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $snapshotCounts->get('missing', 0) }}</div>
            </div>
            <div class="rounded-xl border border-rose-200 bg-white px-4 py-3 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-rose-700">Snapshot error</div>
                <div class="mt-1 text-2xl font-semibold text-rose-900">{{ $snapshotCounts->get('error', 0) }}</div>
            </div>
        </section>

        @if(filled($generatedPrompt ?? null))
            <section class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-blue-950">Сформований prompt</h2>
                        <p class="mt-1 text-sm text-blue-800">У prompt включено репортів: {{ $promptReportCount ?? 0 }}.</p>
                    </div>
                    <button type="button" id="copy-question-report-prompt" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Скопіювати prompt
                    </button>
                </div>
                <textarea id="question-report-prompt" class="mt-4 min-h-[360px] w-full rounded-xl border border-blue-200 bg-white p-3 font-mono text-xs leading-5 text-slate-800 shadow-inner" readonly>{{ $generatedPrompt }}</textarea>
            </section>
        @endif

        @if($reports === [])
            <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm">
                <h2 class="text-lg font-semibold text-slate-800">Репортів ще немає</h2>
                <p class="mt-2 text-sm text-slate-500">Коли адмін зарепортить питання з тесту, воно з'явиться тут.</p>
            </section>
        @else
            <form id="question-report-prompt-form" method="POST" action="{{ route('question-reports.prompt') }}">
                @csrf
            </form>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" id="question-report-select-all" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            Вибрати всі
                        </label>
                        <button type="submit" form="question-report-prompt-form" name="scope" value="selected" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Prompt з вибраних
                        </button>
                        <button type="submit" form="question-report-prompt-form" name="scope" value="open" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                            Prompt з невиконаних
                        </button>
                    </div>
                    <div class="text-sm text-slate-500">Вибрані репорти не змінюють статус, вони тільки потрапляють у prompt.</div>
                </div>

                <div class="p-4">
                    <table class="question-report-list-table w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="w-10 px-4 py-3">#</th>
                                <th class="px-4 py-3">Статус</th>
                                <th class="px-4 py-3">Час</th>
                                <th class="px-4 py-3">Питання</th>
                                <th class="px-4 py-3">Сидер і коментар</th>
                                <th class="px-4 py-3">Дії</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($reports as $report)
                                @php
                                    $legacyQuestion = $report['question'] ?? [];
                                    $reportSnapshot = is_array($report['question_snapshot'] ?? null) ? $report['question_snapshot'] : null;
                                    $currentSnapshot = is_array($report['current_question_snapshot'] ?? null) ? $report['current_question_snapshot'] : null;
                                    $snapshotDiff = is_array($report['snapshot_diff'] ?? null) ? $report['snapshot_diff'] : [];
                                    $snapshotStatus = $report['snapshot_status'] ?? 'missing';
                                    $snapshotStatusLabel = [
                                        'original' => 'Snapshot: original',
                                        'backfilled' => 'Snapshot: backfilled',
                                        'missing' => 'Snapshot: missing',
                                        'error' => 'Snapshot: error',
                                    ][$snapshotStatus] ?? 'Snapshot: missing';
                                    $snapshotStatusClass = [
                                        'original' => 'bg-emerald-100 text-emerald-800',
                                        'backfilled' => 'bg-blue-100 text-blue-800',
                                        'missing' => 'bg-slate-100 text-slate-600',
                                        'error' => 'bg-rose-100 text-rose-800',
                                    ][$snapshotStatus] ?? 'bg-slate-100 text-slate-600';
                                    $previewSnapshot = $currentSnapshot ?: $reportSnapshot;
                                    $question = $previewSnapshot ? [
                                        'id' => data_get($previewSnapshot, 'question.id'),
                                        'uuid' => data_get($previewSnapshot, 'question.uuid'),
                                        'text' => data_get($previewSnapshot, 'question.text'),
                                        'type' => data_get($previewSnapshot, 'question.type'),
                                        'level' => data_get($previewSnapshot, 'question.level'),
                                        'difficulty' => data_get($previewSnapshot, 'question.difficulty'),
                                        'category' => data_get($previewSnapshot, 'question.category.name'),
                                        'source' => data_get($previewSnapshot, 'question.source'),
                                        'seeder' => data_get($previewSnapshot, 'question.seeder'),
                                        'answers' => $previewSnapshot['answers'] ?? [],
                                        'options' => $previewSnapshot['options'] ?? [],
                                        'verb_hints' => $previewSnapshot['verb_hints'] ?? [],
                                        'hints' => $previewSnapshot['hints'] ?? [],
                                        'variants' => $previewSnapshot['variants'] ?? [],
                                        'tags' => $previewSnapshot['tags'] ?? [],
                                    ] : $legacyQuestion;
                                    $test = $report['test'] ?? [];
                                    $seeder = $question['seeder'] ?? [];
                                    $snapshotText = fn (?array $snapshot): string => trim((string) data_get($snapshot, 'question.text', ''));
                                    $snapshotAnswers = fn (?array $snapshot) => collect($snapshot['answers'] ?? [])->map(function ($answer) {
                                        if (! is_array($answer)) {
                                            return trim((string) $answer);
                                        }

                                        return collect([
                                            filled($answer['marker'] ?? null) ? 'marker: '.$answer['marker'] : null,
                                            filled($answer['answer'] ?? null) ? 'answer: '.$answer['answer'] : null,
                                            filled($answer['option_value'] ?? null) ? 'option: '.$answer['option_value'] : null,
                                            filled($answer['option_id'] ?? null) ? 'option_id: '.$answer['option_id'] : null,
                                            filled($answer['verb_hint'] ?? null) ? 'verb_hint: '.$answer['verb_hint'] : null,
                                        ])->filter()->implode(' | ');
                                    })->filter()->values();
                                    $snapshotOptions = fn (?array $snapshot) => collect($snapshot['options'] ?? [])->map(function ($option) {
                                        if (! is_array($option)) {
                                            return trim((string) $option);
                                        }

                                        return trim((string) ($option['option'] ?? $option['value'] ?? ''));
                                    })->filter()->values();
                                    $snapshotVerbHints = fn (?array $snapshot) => collect($snapshot['verb_hints'] ?? [])->map(function ($hint) {
                                        if (! is_array($hint)) {
                                            return trim((string) $hint);
                                        }

                                        return collect([
                                            filled($hint['marker'] ?? null) ? 'marker: '.$hint['marker'] : null,
                                            filled($hint['option_value'] ?? null) ? 'value: '.$hint['option_value'] : null,
                                            filled($hint['hint'] ?? null) && ($hint['hint'] ?? null) !== ($hint['option_value'] ?? null) ? 'hint: '.$hint['hint'] : null,
                                        ])->filter()->implode(' | ');
                                    })->filter()->values();
                                    $snapshotTags = fn (?array $snapshot) => collect($snapshot['tags'] ?? [])->map(fn ($tag) => is_array($tag) ? trim((string) ($tag['name'] ?? '')) : trim((string) $tag))->filter()->values();
                                    $snapshotSavedTests = fn (?array $snapshot) => collect($snapshot['saved_tests'] ?? [])->map(fn ($savedTest) => is_array($savedTest) ? trim((string) (($savedTest['slug'] ?? '').' #'.($savedTest['position'] ?? ''))) : trim((string) $savedTest))->filter()->values();
                                    $answers = collect($question['answers'] ?? [])
                                        ->map(function ($answer) {
                                            if (! is_array($answer)) {
                                                return trim((string) $answer);
                                            }

                                            $parts = [
                                                filled($answer['marker'] ?? null) ? 'marker: '.$answer['marker'] : null,
                                                filled($answer['answer'] ?? null) ? 'answer: '.$answer['answer'] : null,
                                                filled($answer['option_value'] ?? null) ? 'option: '.$answer['option_value'] : null,
                                                filled($answer['option_id'] ?? null) ? 'option_id: '.$answer['option_id'] : null,
                                                filled($answer['verb_hint'] ?? null) ? 'verb_hint: '.$answer['verb_hint'] : null,
                                            ];

                                            return collect($parts)->filter()->implode(' | ');
                                        })
                                        ->filter();
                                    $answerEntries = collect($question['answers'] ?? [])
                                        ->filter(fn ($answer) => is_array($answer))
                                        ->values();
                                    $acceptedAnswerValues = $answerEntries
                                        ->map(fn (array $answer) => trim((string) ($answer['answer'] ?? $answer['option_value'] ?? $answer['value'] ?? '')))
                                        ->filter()
                                        ->values();
                                    $answerByMarker = $answerEntries
                                        ->mapWithKeys(function (array $answer) {
                                            $marker = trim((string) ($answer['marker'] ?? ''));
                                            $value = trim((string) ($answer['answer'] ?? $answer['option_value'] ?? $answer['value'] ?? ''));

                                            return $marker !== '' && $value !== '' ? [$marker => $value] : [];
                                        });
                                    $options = collect($question['options'] ?? [])
                                        ->map(function ($option) {
                                            if (! is_array($option)) {
                                                return trim((string) $option);
                                            }

                                            return trim((string) ($option['value'] ?? $option['option'] ?? ''));
                                        })
                                        ->filter();
                                    $rawVerbHints = collect($question['verb_hints'] ?? [])
                                        ->filter(fn ($hint) => is_array($hint))
                                        ->values();
                                    $hasVerbHintSnapshot = array_key_exists('verb_hints', $question)
                                        || $answerEntries->contains(fn (array $answer) => array_key_exists('verb_hint', $answer));
                                    $verbHintByMarker = $rawVerbHints
                                        ->mapWithKeys(function (array $hint) {
                                            $marker = trim((string) ($hint['marker'] ?? ''));
                                            $value = trim((string) ($hint['option_value'] ?? $hint['value'] ?? $hint['verb_hint'] ?? ''));

                                            return $marker !== '' && $value !== '' ? [$marker => $value] : [];
                                        });
                                    foreach ($answerEntries as $answerEntry) {
                                        $marker = trim((string) ($answerEntry['marker'] ?? ''));
                                        $legacyVerbHint = trim((string) ($answerEntry['verb_hint'] ?? ''));

                                        if ($marker !== '' && $legacyVerbHint !== '' && ! $verbHintByMarker->has($marker)) {
                                            $verbHintByMarker->put($marker, $legacyVerbHint);
                                        }
                                    }
                                    $verbHintCards = $answerEntries
                                        ->map(function (array $answer) use ($verbHintByMarker, $hasVerbHintSnapshot) {
                                            $marker = trim((string) ($answer['marker'] ?? ''));
                                            $accepted = trim((string) ($answer['answer'] ?? $answer['option_value'] ?? $answer['value'] ?? ''));
                                            $hint = $marker !== '' ? trim((string) $verbHintByMarker->get($marker, '')) : '';

                                            return [
                                                'marker' => $marker,
                                                'accepted' => $accepted,
                                                'hint' => $hint,
                                                'has_hint' => $hint !== '',
                                                'snapshot_known' => $hasVerbHintSnapshot,
                                            ];
                                        })
                                        ->filter(fn (array $card) => $card['marker'] !== '' || $card['accepted'] !== '')
                                        ->values();
                                    $questionText = (string) ($question['text'] ?? '');
                                    $questionPreviewParts = collect(preg_split('/(\{[^}]+\})/', $questionText, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [])
                                        ->map(function (string $part) use ($answerByMarker, $verbHintByMarker) {
                                            if (preg_match('/^\{([^}]+)\}$/', $part, $matches) !== 1) {
                                                return [
                                                    'type' => 'text',
                                                    'text' => $part,
                                                ];
                                            }

                                            $marker = trim((string) $matches[1]);
                                            $answer = trim((string) $answerByMarker->get($marker, ''));
                                            $verbHint = trim((string) $verbHintByMarker->get($marker, ''));

                                            return [
                                                'type' => 'answer',
                                                'marker' => $marker,
                                                'text' => $answer !== '' ? $answer : $part,
                                                'verb_hint' => $verbHint,
                                                'has_answer' => $answer !== '',
                                            ];
                                        })
                                        ->values();
                                    $verbHints = collect($question['verb_hints'] ?? [])->map(function ($hint) {
                                        if (! is_array($hint)) {
                                            return trim((string) $hint);
                                        }

                                        return collect([
                                            filled($hint['marker'] ?? null) ? 'marker: '.$hint['marker'] : null,
                                            filled($hint['option_value'] ?? null) ? 'value: '.$hint['option_value'] : null,
                                            filled($hint['option_id'] ?? null) ? 'option_id: '.$hint['option_id'] : null,
                                        ])->filter()->implode(' | ');
                                    })->filter();
                                    $hints = collect($question['hints'] ?? [])->map(function ($hint) {
                                        if (! is_array($hint)) {
                                            return trim((string) $hint);
                                        }

                                        return collect([
                                            filled($hint['provider'] ?? null) ? '['.$hint['provider'].']' : null,
                                            filled($hint['locale'] ?? null) ? $hint['locale'] : null,
                                            $hint['hint'] ?? null,
                                        ])->filter()->implode(' ');
                                    })->filter();
                                    $variants = collect($question['variants'] ?? [])->map(fn ($variant) => is_array($variant) ? ($variant['text'] ?? '') : (string) $variant)->filter();
                                    $tags = collect($question['tags'] ?? [])->map(function ($tag) {
                                        if (! is_array($tag)) {
                                            return trim((string) $tag);
                                        }

                                        return trim((string) ($tag['name'] ?? ''));
                                    })->filter();
                                    $issueTypes = collect($report['issue_types'] ?? [])->filter();
                                    $issueLabels = collect($report['issue_labels'] ?? [])->filter();
                                    $comment = trim((string) ($report['comment'] ?? ''));
                                    $status = $report['status'] ?? 'open';
                                    $isFixed = $status === 'fixed';
                                @endphp
                                <tr class="align-top">
                                    <td class="px-4 py-4">
                                        <input type="checkbox" name="report_ids[]" value="{{ $report['id'] ?? '' }}" form="question-report-prompt-form" class="js-question-report-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4">
                                        @if($isFixed)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Виправлено</span>
                                            @if(filled($report['fixed_at'] ?? null))
                                                <div class="mt-2 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($report['fixed_at'])->format('d.m.Y H:i') }}</div>
                                            @endif
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Невиконаний</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-slate-600">
                                        {{ filled($report['reported_at'] ?? null) ? \Illuminate\Support\Carbon::parse($report['reported_at'])->format('d.m.Y H:i') : 'Н/Д' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="w-full space-y-3">
                                            <div class="font-semibold text-slate-900">{{ $question['text'] ?? 'Невідоме питання' }}</div>
                                            <div>
                                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Заповнене питання</div>
                                                <div class="mt-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-base font-medium leading-8 text-slate-900">
                                                    @forelse($questionPreviewParts as $part)
                                                        @if(($part['type'] ?? '') === 'answer')
                                                            <span class="{{ ($part['has_answer'] ?? false) ? 'inline-flex flex-wrap items-baseline gap-1 rounded-md border border-emerald-300 bg-white px-2 py-0.5 font-semibold text-emerald-800 shadow-sm' : 'inline-flex flex-wrap items-baseline gap-1 rounded-md border border-rose-300 bg-white px-2 py-0.5 font-semibold text-rose-700 shadow-sm' }}">
                                                                <span>{{ $part['text'] }}</span>
                                                                <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ $part['marker'] ?? '' }}</span>
                                                            </span>
                                                            @if(filled($part['verb_hint'] ?? null))
                                                                <span class="ml-1 text-xs font-bold text-red-700">({{ $part['verb_hint'] }})</span>
                                                            @endif
                                                        @else
                                                            <span>{{ $part['text'] ?? '' }}</span>
                                                        @endif
                                                    @empty
                                                        <span class="text-sm text-slate-400">Н/Д</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                            <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                                                <span class="rounded bg-slate-100 px-2 py-1">ID: {{ $question['id'] ?? 'Н/Д' }}</span>
                                                <span class="rounded bg-slate-100 px-2 py-1">UUID: {{ $question['uuid'] ?? 'Н/Д' }}</span>
                                                @if(filled($question['level'] ?? null))
                                                    <span class="rounded bg-blue-50 px-2 py-1 text-blue-700">{{ $question['level'] }}</span>
                                                @endif
                                                @if(filled($test['slug'] ?? null))
                                                    <span class="rounded bg-emerald-50 px-2 py-1 text-emerald-700">Тест: {{ $test['slug'] }}</span>
                                                @endif
                                                <span class="rounded px-2 py-1 {{ $snapshotStatusClass }}">{{ $snapshotStatusLabel }}</span>
                                            </div>
                                            @if($snapshotStatus === 'backfilled')
                                                <div class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-800">
                                                    Цей дамп було створено автоматично після report на основі поточного стану БД, тому він може не повністю відповідати стану питання на момент створення report.
                                                </div>
                                            @elseif($snapshotStatus === 'missing')
                                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600">
                                                    Question snapshot відсутній. Запусти <code>php artisan question-reports:backfill-snapshots</code>, щоб backfill-нути старі reports.
                                                </div>
                                            @elseif($snapshotStatus === 'error')
                                                <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800">
                                                    Snapshot error: {{ $report['snapshot_backfill_error'] ?? 'Невідома помилка' }}
                                                </div>
                                            @endif
                                            <div class="flex flex-wrap gap-2" data-question-report-issue-labels="{{ $report['id'] ?? '' }}">
                                                @forelse($issueLabels as $label)
                                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900">{{ $label }}</span>
                                                @empty
                                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">{{ __('report_question.no_issue_type') }}</span>
                                                @endforelse
                                            </div>

                                            <details class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                                <summary class="cursor-pointer text-sm font-semibold text-slate-700">Debug info питання</summary>
                                                <div class="mt-3 space-y-3 text-sm text-slate-700">
                                                    <div>
                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Знімок питання</div>
                                                        <div class="mt-1 whitespace-pre-line rounded-lg border border-slate-200 bg-white px-3 py-2">{{ $question['text'] ?? 'Н/Д' }}</div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Diff summary</div>
                                                        <div class="mt-1 flex flex-wrap gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                            @foreach($snapshotDiff as $diffKey => $diffSection)
                                                                @continue($diffKey === 'has_changes' || ! is_array($diffSection))
                                                                @php
                                                                    $diffStatus = $diffSection['status'] ?? 'missing';
                                                                    $diffClass = [
                                                                        'changed' => 'bg-amber-100 text-amber-800',
                                                                        'same' => 'bg-emerald-100 text-emerald-800',
                                                                        'missing' => 'bg-slate-100 text-slate-500',
                                                                    ][$diffStatus] ?? 'bg-slate-100 text-slate-500';
                                                                @endphp
                                                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $diffClass }}">
                                                                    {{ $diffSection['label'] ?? $diffKey }}: {{ $diffSection['status_label'] ?? 'Немає даних' }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="grid gap-3 xl:grid-cols-2">
                                                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Дамп питання на момент report</div>
                                                            @if($reportSnapshot)
                                                                @if($snapshotStatus === 'backfilled')
                                                                    <div class="mt-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-800">
                                                                        Backfilled із поточного стану БД після report; може не відповідати оригінальному report-time state.
                                                                    </div>
                                                                @endif
                                                                <div class="mt-2 space-y-2">
                                                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Question text</div>
                                                                        <div class="mt-1">{{ $snapshotText($reportSnapshot) !== '' ? $snapshotText($reportSnapshot) : 'Н/Д' }}</div>
                                                                    </div>
                                                                    <div class="grid gap-2 lg:grid-cols-2">
                                                                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">UUID: {{ data_get($reportSnapshot, 'question.uuid', 'Н/Д') }}</div>
                                                                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">Level/type: {{ data_get($reportSnapshot, 'question.level', 'Н/Д') }} / {{ data_get($reportSnapshot, 'question.type', 'Н/Д') }}</div>
                                                                    </div>
                                                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">Seeder: {{ data_get($reportSnapshot, 'question.seeder.class', 'Н/Д') }}</div>
                                                                    <div>
                                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Answers</div>
                                                                        <ul class="mt-1 space-y-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                                                            @forelse($snapshotAnswers($reportSnapshot) as $line)
                                                                                <li>{{ $line }}</li>
                                                                            @empty
                                                                                <li class="text-slate-400">Н/Д</li>
                                                                            @endforelse
                                                                        </ul>
                                                                    </div>
                                                                    <div>
                                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Options</div>
                                                                        <ul class="mt-1 space-y-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                                                            @forelse($snapshotOptions($reportSnapshot) as $line)
                                                                                <li>{{ $line }}</li>
                                                                            @empty
                                                                                <li class="text-slate-400">Н/Д</li>
                                                                            @endforelse
                                                                        </ul>
                                                                    </div>
                                                                    <div>
                                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Verb hints</div>
                                                                        <ul class="mt-1 space-y-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                                                            @forelse($snapshotVerbHints($reportSnapshot) as $line)
                                                                                <li>{{ $line }}</li>
                                                                            @empty
                                                                                <li class="text-slate-400">Н/Д</li>
                                                                            @endforelse
                                                                        </ul>
                                                                    </div>
                                                                    <div>
                                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tags / saved tests</div>
                                                                        <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                                                                            <div>Tags: {{ $snapshotTags($reportSnapshot)->isNotEmpty() ? $snapshotTags($reportSnapshot)->implode(', ') : 'Н/Д' }}</div>
                                                                            <div class="mt-1">Saved tests: {{ $snapshotSavedTests($reportSnapshot)->isNotEmpty() ? $snapshotSavedTests($reportSnapshot)->implode(', ') : 'Н/Д' }}</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">Snapshot відсутній</div>
                                                            @endif
                                                        </div>
                                                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Поточне питання в базі</div>
                                                            @if($currentSnapshot)
                                                                <div class="mt-2 space-y-2">
                                                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Question text</div>
                                                                        <div class="mt-1">{{ $snapshotText($currentSnapshot) !== '' ? $snapshotText($currentSnapshot) : 'Н/Д' }}</div>
                                                                    </div>
                                                                    <div class="grid gap-2 lg:grid-cols-2">
                                                                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">UUID: {{ data_get($currentSnapshot, 'question.uuid', 'Н/Д') }}</div>
                                                                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">Level/type: {{ data_get($currentSnapshot, 'question.level', 'Н/Д') }} / {{ data_get($currentSnapshot, 'question.type', 'Н/Д') }}</div>
                                                                    </div>
                                                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">Seeder: {{ data_get($currentSnapshot, 'question.seeder.class', 'Н/Д') }}</div>
                                                                    <div>
                                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Answers</div>
                                                                        <ul class="mt-1 space-y-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                                                            @forelse($snapshotAnswers($currentSnapshot) as $line)
                                                                                <li>{{ $line }}</li>
                                                                            @empty
                                                                                <li class="text-slate-400">Н/Д</li>
                                                                            @endforelse
                                                                        </ul>
                                                                    </div>
                                                                    <div>
                                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Options</div>
                                                                        <ul class="mt-1 space-y-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                                                            @forelse($snapshotOptions($currentSnapshot) as $line)
                                                                                <li>{{ $line }}</li>
                                                                            @empty
                                                                                <li class="text-slate-400">Н/Д</li>
                                                                            @endforelse
                                                                        </ul>
                                                                    </div>
                                                                    <div>
                                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Verb hints</div>
                                                                        <ul class="mt-1 space-y-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                                                            @forelse($snapshotVerbHints($currentSnapshot) as $line)
                                                                                <li>{{ $line }}</li>
                                                                            @empty
                                                                                <li class="text-slate-400">Н/Д</li>
                                                                            @endforelse
                                                                        </ul>
                                                                    </div>
                                                                    <div>
                                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tags / saved tests</div>
                                                                        <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                                                                            <div>Tags: {{ $snapshotTags($currentSnapshot)->isNotEmpty() ? $snapshotTags($currentSnapshot)->implode(', ') : 'Н/Д' }}</div>
                                                                            <div class="mt-1">Saved tests: {{ $snapshotSavedTests($currentSnapshot)->isNotEmpty() ? $snapshotSavedTests($currentSnapshot)->implode(', ') : 'Н/Д' }}</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800">Поточне питання в БД не знайдено</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Типи помилок</div>
                                                        <div class="mt-1 rounded-lg border border-slate-200 bg-white px-3 py-2" data-question-report-issue-summary="{{ $report['id'] ?? '' }}">
                                                            @if($issueLabels->isNotEmpty())
                                                                <div class="flex flex-wrap gap-2">
                                                                    @foreach($issueLabels as $label)
                                                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900">{{ $label }}</span>
                                                                    @endforeach
                                                                </div>
                                                                <div class="mt-2 break-all font-mono text-xs text-slate-500">{{ $issueTypes->implode(', ') }}</div>
                                                            @else
                                                                <span class="text-slate-400">{{ __('report_question.no_issue_type') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="grid gap-3">
                                                        <div>
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Відповіді</div>
                                                            <ul class="mt-1 space-y-1 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                                @forelse($answerEntries as $answer)
                                                                    @php
                                                                        $acceptedAnswer = trim((string) ($answer['answer'] ?? $answer['option_value'] ?? $answer['value'] ?? ''));
                                                                    @endphp
                                                                    <li class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2">
                                                                        <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">marker: {{ $answer['marker'] ?? 'Н/Д' }}</div>
                                                                        <div class="mt-1 text-sm font-semibold text-emerald-900">{{ $acceptedAnswer !== '' ? $acceptedAnswer : 'Н/Д' }}</div>
                                                                        @if(filled($answer['option_id'] ?? null))
                                                                            <div class="mt-1 text-xs text-emerald-700">option_id: {{ $answer['option_id'] }}</div>
                                                                        @endif
                                                                    </li>
                                                                @empty
                                                                    @forelse($answers as $answer)
                                                                        <li>{{ $answer }}</li>
                                                                    @empty
                                                                        <li class="text-slate-400">Н/Д</li>
                                                                    @endforelse
                                                                @endforelse
                                                            </ul>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Опції</div>
                                                            <ul class="mt-1 space-y-1 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                                @forelse($options as $option)
                                                                    @php
                                                                        $isAcceptedOption = $acceptedAnswerValues->contains($option);
                                                                    @endphp
                                                                    <li class="{{ $isAcceptedOption ? 'rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 font-semibold text-emerald-900' : '' }}">
                                                                        {{ $option }}
                                                                        @if($isAcceptedOption)
                                                                            <span class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700">correct</span>
                                                                        @endif
                                                                    </li>
                                                                @empty
                                                                    <li class="text-slate-400">Н/Д</li>
                                                                @endforelse
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="grid gap-3">
                                                        <div>
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Verb hints</div>
                                                            <ul class="mt-1 space-y-1 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                                @forelse($verbHintCards as $hintCard)
                                                                    <li class="{{ $hintCard['has_hint'] ? 'rounded-lg border border-blue-200 bg-blue-50 px-3 py-2' : 'rounded-lg border border-rose-200 bg-rose-50 px-3 py-2' }}">
                                                                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wide {{ $hintCard['has_hint'] ? 'text-blue-700' : 'text-rose-700' }}">
                                                                            <span>marker: {{ $hintCard['marker'] !== '' ? $hintCard['marker'] : 'Н/Д' }}</span>
                                                                            @if($hintCard['accepted'] !== '')
                                                                                <span class="rounded-full bg-white px-2 py-0.5 normal-case tracking-normal">answer: {{ $hintCard['accepted'] }}</span>
                                                                            @endif
                                                                        </div>
                                                                        @if($hintCard['has_hint'])
                                                                            <div class="mt-1 font-semibold text-blue-900">{{ $hintCard['hint'] }}</div>
                                                                        @elseif($hintCard['snapshot_known'])
                                                                            <div class="mt-1 font-semibold text-rose-800">Відсутній verb_hint у цьому питанні</div>
                                                                        @else
                                                                            <div class="mt-1 font-semibold text-amber-800">Verb_hint не збережено у snapshot цього report. Перевір актуальне питання в DB або створи report повторно.</div>
                                                                        @endif
                                                                    </li>
                                                                @empty
                                                                    @forelse($verbHints as $hint)
                                                                        <li>{{ $hint }}</li>
                                                                    @empty
                                                                        @if($hasVerbHintSnapshot)
                                                                            <li class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 font-semibold text-rose-800">Відсутній verb_hint у цьому питанні</li>
                                                                        @else
                                                                            <li class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 font-semibold text-amber-800">Verb_hint не збережено у snapshot цього report. Перевір актуальне питання в DB або створи report повторно.</li>
                                                                        @endif
                                                                    @endforelse
                                                                @endforelse
                                                            </ul>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Hints</div>
                                                            <ul class="mt-1 space-y-1 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                                @forelse($hints as $hint)
                                                                    <li>{{ $hint }}</li>
                                                                @empty
                                                                    <li class="text-slate-400">Н/Д</li>
                                                                @endforelse
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="grid gap-3">
                                                        <div>
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Variants</div>
                                                            <ul class="mt-1 max-h-40 space-y-1 overflow-y-auto rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                                @forelse($variants as $variant)
                                                                    <li>{{ $variant }}</li>
                                                                @empty
                                                                    <li class="text-slate-400">Н/Д</li>
                                                                @endforelse
                                                            </ul>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tags</div>
                                                            <div class="mt-1 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                                @forelse($tags as $tag)
                                                                    <span class="mr-1 inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-700">{{ $tag }}</span>
                                                                @empty
                                                                    <span class="text-slate-400">Н/Д</span>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="grid gap-3">
                                                        <div>
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Категорія / джерело</div>
                                                            <div class="mt-1 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                                <div>{{ $question['category'] ?? 'Н/Д' }}</div>
                                                                <div class="text-xs text-slate-500">{{ data_get($question, 'source.name', 'Н/Д') }}</div>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Файл репорту</div>
                                                            <code class="mt-1 block break-all rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs">{{ $report['file'] ?? '' }}</code>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-wrap gap-3">
                                                        @if(filled($test['url'] ?? null))
                                                            <a href="{{ $test['url'] }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700" target="_blank" rel="noopener">Відкрити тест</a>
                                                        @endif
                                                        @if(filled($question['id'] ?? null))
                                                            <a href="{{ route('question-review.edit', $question['id']) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700" target="_blank" rel="noopener">Відкрити в ревʼю питань</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </details>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="w-full space-y-3">
                                            <div class="space-y-2">
                                                <code class="block break-all rounded bg-slate-100 px-2 py-1 text-xs text-slate-800">{{ $seeder['class'] ?? 'Н/Д' }}</code>
                                                @if(filled($seeder['file'] ?? null))
                                                    <code class="block break-all rounded bg-blue-50 px-2 py-1 text-xs text-blue-800">{{ $seeder['file'] }}</code>
                                                @endif
                                            </div>
                                            <div class="flex flex-wrap gap-2" data-question-report-issue-labels="{{ $report['id'] ?? '' }}">
                                                @forelse($issueLabels as $label)
                                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900">{{ $label }}</span>
                                                @empty
                                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">{{ __('report_question.no_issue_type') }}</span>
                                                @endforelse
                                            </div>
                                            <div
                                                class="{{ $comment !== '' ? 'whitespace-pre-line rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900' : 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500' }}"
                                                data-question-report-comment="{{ $report['id'] ?? '' }}">{{ $comment !== '' ? $comment : __('report_question.no_comment') }}</div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <div class="flex flex-col gap-2">
                                            <details class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                <summary class="cursor-pointer text-xs font-semibold text-slate-700">Редагувати report</summary>
                                                <form method="POST" action="{{ route('question-reports.update', $report['id']) }}" class="mt-3 space-y-3 text-left" data-question-report-edit-form data-report-id="{{ $report['id'] ?? '' }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <div>
                                                        <div class="text-sm font-bold text-slate-900">{{ __('report_question.title') }}</div>
                                                        <p class="mt-1 text-xs text-slate-500">{{ __('report_question.comment_optional') }}</p>
                                                        <div class="mt-2 grid gap-2">
                                                            @foreach($issueCatalog as $issue)
                                                                @php
                                                                    $issueKey = $issue['key'] ?? $issue['slug'] ?? '';
                                                                    $isChecked = $issueTypes->contains($issueKey);
                                                                @endphp
                                                                @continue($issueKey === '')
                                                                <label class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                                                                    <input
                                                                        type="checkbox"
                                                                        name="issue_types[]"
                                                                        value="{{ $issueKey }}"
                                                                        @checked($isChecked)
                                                                        class="mt-1 rounded border-amber-300 text-amber-600 focus:ring-amber-500"
                                                                        data-question-report-edit-issue>
                                                                    <span>
                                                                        <span class="block text-sm font-semibold text-amber-950">{{ $issue['label'] ?? $issueKey }}</span>
                                                                        @if(filled($issue['description'] ?? null))
                                                                            <span class="mt-0.5 block text-xs leading-5 text-amber-800">{{ $issue['description'] }}</span>
                                                                        @endif
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                        <p class="{{ $issueTypes->contains('other') ? '' : 'hidden' }} mt-2 text-xs font-semibold text-amber-800" data-question-report-edit-other-hint>
                                                            {{ __('report_question.other_requires_comment_hint') }}
                                                        </p>
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-500" for="question-report-comment-{{ $report['id'] }}">
                                                            {{ __('report_question.comment_label') }}
                                                        </label>
                                                        <textarea
                                                            id="question-report-comment-{{ $report['id'] }}"
                                                            name="comment"
                                                            maxlength="4000"
                                                            class="mt-1 min-h-[110px] w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">{{ $comment }}</textarea>
                                                    </div>

                                                    <div class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700" data-question-report-edit-error></div>
                                                    <div class="hidden rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700" data-question-report-edit-status></div>

                                                    <button type="submit" class="rounded-lg bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-60" data-question-report-edit-submit>
                                                        Зберегти report
                                                    </button>
                                                </form>
                                            </details>
                                        @if($isFixed)
                                            <form method="POST" action="{{ route('question-reports.status', $report['id']) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="open">
                                                <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                    Повернути в роботу
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('question-reports.status', $report['id']) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="fixed">
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                                                    Позначити виправленим
                                                </button>
                                            </form>
                                        @endif
                                            <form method="POST" action="{{ route('question-reports.destroy', $report['id']) }}" onsubmit="return confirm('Видалити цей репорт? JSON-файл буде видалено з storage/app/question-reports.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                    Видалити
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>

    <script>
        document.getElementById('question-report-select-all')?.addEventListener('change', (event) => {
            document.querySelectorAll('.js-question-report-checkbox').forEach((checkbox) => {
                checkbox.checked = event.target.checked;
            });
        });

        document.getElementById('copy-question-report-prompt')?.addEventListener('click', async () => {
            const prompt = document.getElementById('question-report-prompt');
            if (!prompt) {
                return;
            }

            prompt.select();
            await navigator.clipboard?.writeText(prompt.value);
        });

        const questionReportEditValidationMessage = @json(__('report_question.validation.issue_or_comment_required'));
        const questionReportEditSavedMessage = @json('Репорт оновлено.');
        const questionReportEditSavingMessage = @json('Збереження...');
        const questionReportEditFailedMessage = @json('Не вдалося зберегти report.');
        const questionReportNoCommentMessage = @json(__('report_question.no_comment'));
        const questionReportNoIssueTypeMessage = @json(__('report_question.no_issue_type'));
        const questionReportIssueChipClasses = 'rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900';
        const questionReportEmptyIssueChipClasses = 'rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500';
        const questionReportCommentClasses = 'whitespace-pre-line rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900';
        const questionReportEmptyCommentClasses = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500';

        function syncQuestionReportEditOtherHint(form) {
            const hint = form.querySelector('[data-question-report-edit-other-hint]');
            if (!hint) {
                return;
            }

            const otherChecked = Boolean(form.querySelector('[data-question-report-edit-issue][value="other"]:checked'));
            hint.classList.toggle('hidden', !otherChecked);
        }

        function questionReportCreateIssueChip(label, isEmpty = false) {
            const chip = document.createElement('span');
            chip.className = isEmpty ? questionReportEmptyIssueChipClasses : questionReportIssueChipClasses;
            chip.textContent = label;

            return chip;
        }

        function questionReportIssueLabels(report) {
            return Array.isArray(report.issue_labels)
                ? report.issue_labels.map((label) => String(label || '').trim()).filter(Boolean)
                : [];
        }

        function questionReportIssueTypes(report) {
            return Array.isArray(report.issue_types)
                ? report.issue_types.map((type) => String(type || '').trim()).filter(Boolean)
                : [];
        }

        function updateQuestionReportIssueLabelBlocks(report) {
            const reportId = String(report.id || '');
            const labels = questionReportIssueLabels(report);

            document.querySelectorAll('[data-question-report-issue-labels]').forEach((block) => {
                if (block.dataset.questionReportIssueLabels !== reportId) {
                    return;
                }

                const nodes = labels.length > 0
                    ? labels.map((label) => questionReportCreateIssueChip(label))
                    : [questionReportCreateIssueChip(questionReportNoIssueTypeMessage, true)];

                block.replaceChildren(...nodes);
            });
        }

        function updateQuestionReportIssueSummaryBlocks(report) {
            const reportId = String(report.id || '');
            const labels = questionReportIssueLabels(report);
            const issueTypes = questionReportIssueTypes(report);

            document.querySelectorAll('[data-question-report-issue-summary]').forEach((block) => {
                if (block.dataset.questionReportIssueSummary !== reportId) {
                    return;
                }

                if (labels.length === 0) {
                    const empty = document.createElement('span');
                    empty.className = 'text-slate-400';
                    empty.textContent = questionReportNoIssueTypeMessage;
                    block.replaceChildren(empty);

                    return;
                }

                const chips = document.createElement('div');
                chips.className = 'flex flex-wrap gap-2';
                chips.replaceChildren(...labels.map((label) => questionReportCreateIssueChip(label)));

                const keys = document.createElement('div');
                keys.className = 'mt-2 break-all font-mono text-xs text-slate-500';
                keys.textContent = issueTypes.join(', ');

                block.replaceChildren(chips, keys);
            });
        }

        function updateQuestionReportCommentBlocks(report) {
            const reportId = String(report.id || '');
            const comment = String(report.comment || '').trim();

            document.querySelectorAll('[data-question-report-comment]').forEach((block) => {
                if (block.dataset.questionReportComment !== reportId) {
                    return;
                }

                block.className = comment === '' ? questionReportEmptyCommentClasses : questionReportCommentClasses;
                block.textContent = comment === '' ? questionReportNoCommentMessage : comment;
            });
        }

        function syncQuestionReportEditFormFromReport(form, report) {
            const issueTypes = new Set(questionReportIssueTypes(report));
            form.querySelectorAll('[data-question-report-edit-issue]').forEach((input) => {
                input.checked = issueTypes.has(String(input.value || '').trim());
            });
            syncQuestionReportEditOtherHint(form);
        }

        function setQuestionReportEditFeedback(form, type, message) {
            const error = form.querySelector('[data-question-report-edit-error]');
            const status = form.querySelector('[data-question-report-edit-status]');

            if (error) {
                error.classList.toggle('hidden', type !== 'error');
                error.textContent = type === 'error' ? message : '';
            }

            if (status) {
                status.classList.toggle('hidden', type !== 'status');
                status.textContent = type === 'status' ? message : '';
            }
        }

        function firstQuestionReportValidationError(payload) {
            if (!payload || typeof payload !== 'object' || !payload.errors) {
                return null;
            }

            for (const messages of Object.values(payload.errors)) {
                if (Array.isArray(messages) && messages.length > 0) {
                    return String(messages[0]);
                }
            }

            return null;
        }

        async function submitQuestionReportEditForm(form) {
            const submit = form.querySelector('[data-question-report-edit-submit]');
            const originalSubmitText = submit?.textContent || '';
            const reportId = String(form.dataset.reportId || '');
            const issueTypes = Array.from(form.querySelectorAll('[data-question-report-edit-issue]:checked'))
                .map((input) => String(input.value || '').trim())
                .filter(Boolean);
            const comment = String(form.querySelector('textarea[name="comment"]')?.value || '');
            const formData = new FormData(form);

            formData.set('_method', 'PATCH');
            formData.set('comment', comment);
            formData.delete('issue_types[]');
            issueTypes.forEach((issueType) => formData.append('issue_types[]', issueType));

            setQuestionReportEditFeedback(form, null, '');

            if (submit) {
                submit.disabled = true;
                submit.textContent = questionReportEditSavingMessage;
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                    credentials: 'same-origin',
                });
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error(questionReportEditFailedMessage);
                }

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(firstQuestionReportValidationError(payload) || payload.message || questionReportEditFailedMessage);
                }

                const report = payload.report || {};
                if (!report.id || String(report.id) !== reportId) {
                    throw new Error(questionReportEditFailedMessage);
                }

                syncQuestionReportEditFormFromReport(form, report);
                updateQuestionReportIssueLabelBlocks(report);
                updateQuestionReportIssueSummaryBlocks(report);
                updateQuestionReportCommentBlocks(report);
                setQuestionReportEditFeedback(form, 'status', payload.message || questionReportEditSavedMessage);
            } catch (error) {
                setQuestionReportEditFeedback(form, 'error', error.message || questionReportEditFailedMessage);
            } finally {
                if (submit) {
                    submit.disabled = false;
                    submit.textContent = originalSubmitText;
                }
            }
        }

        document.querySelectorAll('[data-question-report-edit-form]').forEach((form) => {
            syncQuestionReportEditOtherHint(form);

            form.addEventListener('change', (event) => {
                if (event.target.closest('[data-question-report-edit-issue]')) {
                    syncQuestionReportEditOtherHint(form);
                }
            });

            form.addEventListener('submit', (event) => {
                const issueTypes = Array.from(form.querySelectorAll('[data-question-report-edit-issue]:checked'))
                    .map((input) => String(input.value || '').trim())
                    .filter(Boolean);
                const comment = String(form.querySelector('textarea[name="comment"]')?.value || '').trim();

                if (issueTypes.length > 0 || comment !== '') {
                    if (!window.fetch) {
                        return;
                    }

                    event.preventDefault();
                    submitQuestionReportEditForm(form);

                    return;
                }

                event.preventDefault();
                setQuestionReportEditFeedback(form, 'error', questionReportEditValidationMessage);
            });
        });
    </script>
@endsection
