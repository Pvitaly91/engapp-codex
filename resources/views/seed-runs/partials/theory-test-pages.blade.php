@php
    $theoryTestPages = collect($theoryTestPages ?? []);
@endphp

@foreach($theoryTestPages as $pageGroup)
    @php
        $page = $pageGroup['page'] ?? [];
        $seeders = collect($pageGroup['seeders'] ?? []);
    @endphp
    <div class="overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-emerald-100 bg-emerald-50 px-5 py-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
                    {{ $page['label'] ?? 'Сторінка теорії' }}
                </div>
                <div class="mt-2">
                    @if(!empty($page['url']))
                        <a href="{{ $page['url'] }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-lg font-semibold text-emerald-800 underline decoration-emerald-300 underline-offset-2 hover:text-emerald-950 break-words">
                            {{ $page['title'] ?? $page['url'] }}
                        </a>
                    @else
                        <h3 class="text-lg font-semibold text-emerald-900 break-words">
                            {{ $page['title'] ?? 'Сторінка теорії' }}
                        </h3>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-2 text-xs">
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 font-medium text-slate-700 ring-1 ring-slate-200">
                    Сидерів: {{ (int) ($pageGroup['seeders_count'] ?? $seeders->count()) }}
                </span>
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 font-medium text-slate-700 ring-1 ring-slate-200">
                    Тестів: {{ (int) ($pageGroup['tests_count'] ?? 0) }}
                </span>
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 font-medium text-slate-700 ring-1 ring-slate-200">
                    Питань: {{ (int) ($pageGroup['question_count'] ?? 0) }}
                </span>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach($seeders as $seedRun)
                @php
                    $testTarget = data_get($seedRun, 'test_target');
                    $seederClassName = (string) data_get($seedRun, 'class_name', '');
                    $seederDisplayName = (string) data_get($seedRun, 'display_class_name', $seederClassName ?: 'UnknownSeeder');
                    $seederBaseName = (string) data_get($seedRun, 'display_class_basename', class_basename($seederDisplayName));
                    $questionCount = (int) data_get($seedRun, 'question_count', 0);
                    $ranAtRaw = data_get($seedRun, 'ran_at_formatted', data_get($seedRun, 'ran_at'));
                    $ranAt = $ranAtRaw instanceof \DateTimeInterface
                        ? $ranAtRaw->format('Y-m-d H:i:s')
                        : (is_string($ranAtRaw) ? $ranAtRaw : null);
                @endphp
                <div class="flex flex-col gap-3 px-5 py-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <div class="font-mono text-sm font-semibold text-slate-800 break-all">
                            {{ $seederBaseName }}
                        </div>
                        @if($seederDisplayName !== $seederBaseName)
                            <p class="mt-1 text-xs text-slate-500 break-all">
                                {{ $seederDisplayName }}
                            </p>
                        @endif
                        <div class="mt-2 flex flex-wrap gap-2 text-[11px] text-slate-600">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1">
                                Питань: {{ $questionCount }}
                            </span>
                            @if($ranAt)
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1">
                                    Виконано: {{ $ranAt }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if($testTarget)
                            <a href="{{ $testTarget['url'] }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               title="{{ $testTarget['title'] ?? ($testTarget['label'] ?? 'Готовий тест') }}"
                               class="inline-flex items-center gap-2 rounded-md bg-sky-100 px-3 py-1.5 text-xs font-medium text-sky-700 transition hover:bg-sky-200">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                {{ $testTarget['label'] ?? 'Готовий тест' }}
                            </a>
                        @endif

                        <a href="{{ route('seed-runs.preview', ['class_name' => $seederClassName]) }}"
                           target="_blank"
                           class="inline-flex items-center gap-2 rounded-md bg-violet-100 px-3 py-1.5 text-xs font-medium text-violet-700 transition hover:bg-violet-200">
                            <i class="fa-solid fa-eye"></i>
                            Попередній перегляд
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
