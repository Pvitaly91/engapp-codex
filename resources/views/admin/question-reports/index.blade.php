@extends('layouts.app')

@section('title', 'Репорти питань')

@section('content')
    @php
        $reportsCollection = collect($reports);
        $openCount = $reportsCollection->filter(fn ($report) => ($report['status'] ?? 'open') !== 'fixed')->count();
        $fixedCount = $reportsCollection->filter(fn ($report) => ($report['status'] ?? 'open') === 'fixed')->count();
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

        <section class="grid gap-3 sm:grid-cols-3">
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

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
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
                                    $question = $report['question'] ?? [];
                                    $test = $report['test'] ?? [];
                                    $seeder = $question['seeder'] ?? [];
                                    $answers = collect($question['answers'] ?? [])
                                        ->map(fn ($answer) => trim((string) (($answer['marker'] ?? '') . ': ' . ($answer['value'] ?? ''))))
                                        ->filter();
                                    $options = collect($question['options'] ?? [])->filter();
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
                                        <div class="max-w-2xl space-y-3">
                                            <div class="font-semibold text-slate-900">{{ $question['text'] ?? 'Невідоме питання' }}</div>
                                            <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                                                <span class="rounded bg-slate-100 px-2 py-1">ID: {{ $question['id'] ?? 'Н/Д' }}</span>
                                                <span class="rounded bg-slate-100 px-2 py-1">UUID: {{ $question['uuid'] ?? 'Н/Д' }}</span>
                                                @if(filled($question['level'] ?? null))
                                                    <span class="rounded bg-blue-50 px-2 py-1 text-blue-700">{{ $question['level'] }}</span>
                                                @endif
                                                @if(filled($test['slug'] ?? null))
                                                    <span class="rounded bg-emerald-50 px-2 py-1 text-emerald-700">Тест: {{ $test['slug'] }}</span>
                                                @endif
                                            </div>

                                            <details class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                                <summary class="cursor-pointer text-sm font-semibold text-slate-700">Переглянути зарепорчене питання</summary>
                                                <div class="mt-3 space-y-3 text-sm text-slate-700">
                                                    <div>
                                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Знімок питання</div>
                                                        <div class="mt-1 whitespace-pre-line rounded-lg border border-slate-200 bg-white px-3 py-2">{{ $question['text'] ?? 'Н/Д' }}</div>
                                                    </div>
                                                    <div class="grid gap-3 md:grid-cols-2">
                                                        <div>
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Відповіді</div>
                                                            <ul class="mt-1 space-y-1 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                                @forelse($answers as $answer)
                                                                    <li>{{ $answer }}</li>
                                                                @empty
                                                                    <li class="text-slate-400">Н/Д</li>
                                                                @endforelse
                                                            </ul>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Опції</div>
                                                            <ul class="mt-1 space-y-1 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                                @forelse($options as $option)
                                                                    <li>{{ $option }}</li>
                                                                @empty
                                                                    <li class="text-slate-400">Н/Д</li>
                                                                @endforelse
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="grid gap-3 md:grid-cols-2">
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
                                        <div class="max-w-md space-y-3">
                                            <div class="space-y-2">
                                                <code class="block break-all rounded bg-slate-100 px-2 py-1 text-xs text-slate-800">{{ $seeder['class'] ?? 'Н/Д' }}</code>
                                                @if(filled($seeder['file'] ?? null))
                                                    <code class="block break-all rounded bg-blue-50 px-2 py-1 text-xs text-blue-800">{{ $seeder['file'] }}</code>
                                                @endif
                                            </div>
                                            <div class="whitespace-pre-line rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">{{ $report['comment'] ?? '' }}</div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <div class="flex flex-col gap-2">
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
    </script>
@endsection
