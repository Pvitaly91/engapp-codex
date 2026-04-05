@php
    $theoryTestPages = collect($theoryTestPages ?? []);
@endphp

@foreach($theoryTestPages as $pageGroup)
    @php
        $page = $pageGroup['page'] ?? [];
        $seeders = collect($pageGroup['seeders'] ?? []);
        $executedSeeders = collect($pageGroup['executed_seeders'] ?? []);
        $pendingSeeders = collect($pageGroup['pending_seeders'] ?? []);
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
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 font-medium text-emerald-700 ring-1 ring-emerald-200">
                    Виконані сидери: {{ (int) ($pageGroup['executed_seeders_count'] ?? $executedSeeders->count()) }}
                </span>
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 font-medium text-amber-700 ring-1 ring-amber-200">
                    Невиконані сидери: {{ (int) ($pageGroup['pending_seeders_count'] ?? $pendingSeeders->count()) }}
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
            @if($executedSeeders->isNotEmpty())
                <div class="px-5 py-4">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-800">
                            Виконані сидери
                        </span>
                        <span class="text-xs text-slate-500">{{ $executedSeeders->count() }}</span>
                    </div>

                    <div class="space-y-3">
                        @foreach($executedSeeders as $seedRun)
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
                            <div class="rounded-lg border border-emerald-100 bg-emerald-50/40 px-4 py-4">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="font-mono text-sm font-semibold text-slate-800 break-all">
                                                {{ $seederBaseName }}
                                            </div>
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-800">
                                                Виконано
                                            </span>
                                        </div>
                                        @if($seederDisplayName !== $seederBaseName)
                                            <p class="mt-1 text-xs text-slate-500 break-all">
                                                {{ $seederDisplayName }}
                                            </p>
                                        @endif
                                        <div class="mt-2 flex flex-wrap gap-2 text-[11px] text-slate-600">
                                            <span class="inline-flex items-center rounded-full bg-white px-2.5 py-1 ring-1 ring-slate-200">
                                                Питань: {{ $questionCount }}
                                            </span>
                                            @if($ranAt)
                                                <span class="inline-flex items-center rounded-full bg-white px-2.5 py-1 ring-1 ring-slate-200">
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
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($pendingSeeders->isNotEmpty())
                <div class="px-5 py-4">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-amber-800">
                            Невиконані сидери
                        </span>
                        <span class="text-xs text-slate-500">{{ $pendingSeeders->count() }}</span>
                    </div>

                    <div class="space-y-3">
                        @foreach($pendingSeeders as $pendingSeeder)
                            @php
                                $testTarget = data_get($pendingSeeder, 'test_target');
                                $seederClassName = (string) data_get($pendingSeeder, 'class_name', '');
                                $seederDisplayName = (string) data_get($pendingSeeder, 'display_class_name', $seederClassName ?: 'UnknownSeeder');
                                $seederBaseName = (string) data_get($pendingSeeder, 'display_class_basename', class_basename($seederDisplayName));
                                $supportsPreview = (bool) data_get($pendingSeeder, 'supports_preview', false);
                                $canExecute = (bool) data_get($pendingSeeder, 'can_execute', true);
                                $executionBlockReason = (string) data_get($pendingSeeder, 'execution_block_reason', '');
                            @endphp
                            <div class="rounded-lg border border-amber-100 bg-amber-50/60 px-4 py-4">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="font-mono text-sm font-semibold text-slate-800 break-all">
                                                {{ $seederBaseName }}
                                            </div>
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold text-amber-800">
                                                Ще не виконано
                                            </span>
                                            @if(! $canExecute)
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-semibold text-red-700">
                                                    Заблоковано
                                                </span>
                                            @endif
                                        </div>
                                        @if($seederDisplayName !== $seederBaseName)
                                            <p class="mt-1 text-xs text-slate-500 break-all">
                                                {{ $seederDisplayName }}
                                            </p>
                                        @endif
                                        @if($executionBlockReason !== '')
                                            <p class="mt-2 text-xs text-amber-800">
                                                {{ $executionBlockReason }}
                                            </p>
                                        @endif
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

                                        @if($supportsPreview)
                                            <a href="{{ route('seed-runs.preview', ['class_name' => $seederClassName]) }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-2 rounded-md bg-violet-100 px-3 py-1.5 text-xs font-medium text-violet-700 transition hover:bg-violet-200">
                                                <i class="fa-solid fa-eye"></i>
                                                Попередній перегляд
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endforeach
