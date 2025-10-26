@php
    $categories = collect($categories ?? []);
@endphp

@if($categories->isEmpty())
    <p class="text-xs text-gray-500">Питання для цього сидера не знайдені.</p>
@else
    @foreach($categories as $categoryGroup)
        <div class="border border-slate-200 rounded-xl overflow-hidden" data-category-wrapper data-seed-run-id="{{ $seedRun->id }}" data-category-key="{{ $categoryGroup['key'] }}">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 bg-slate-100">
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold text-gray-800">{{ $categoryGroup['display_name'] }}</h3>
                    <p class="text-xs text-gray-500">
                        @if($categoryGroup['category'])
                            Категорія ID: {{ $categoryGroup['category']['id'] }}
                        @else
                            Категорія не вказана
                        @endif
                        · Питань:
                        <span class="font-semibold" data-category-question-count data-seed-run-id="{{ $seedRun->id }}" data-category-key="{{ $categoryGroup['key'] }}">
                            {{ $categoryGroup['question_count'] }}
                        </span>
                    </p>
                </div>
            </div>
            <div class="space-y-3 px-4 pb-4 pt-3 bg-white">
                @foreach(collect($categoryGroup['sources']) as $sourceGroup)
                    <div class="border border-slate-200 rounded-lg overflow-hidden" data-source-wrapper data-seed-run-id="{{ $seedRun->id }}" data-category-key="{{ $categoryGroup['key'] }}" data-source-key="{{ $sourceGroup['key'] }}">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-3 py-2 bg-slate-50">
                            <div class="space-y-1">
                                <h4 class="text-sm font-semibold text-slate-700">{{ $sourceGroup['display_name'] }}</h4>
                                <p class="text-xs text-slate-500">
                                    @if($sourceGroup['source'])
                                        Джерело ID: {{ $sourceGroup['source']['id'] }}
                                    @else
                                        Джерело не вказане
                                    @endif
                                    · Питань:
                                    <span class="font-semibold" data-source-question-count data-seed-run-id="{{ $seedRun->id }}" data-category-key="{{ $categoryGroup['key'] }}" data-source-key="{{ $sourceGroup['key'] }}">
                                        {{ $sourceGroup['question_count'] }}
                                    </span>
                                </p>
                            </div>
                            <button type="button"
                                    class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1.5 rounded-full bg-blue-50 hover:bg-blue-100 transition w-full sm:w-auto"
                                    data-source-toggle
                                    data-seed-run-id="{{ $seedRun->id }}"
                                    data-category-key="{{ $categoryGroup['key'] }}"
                                    data-source-key="{{ $sourceGroup['key'] }}"
                                    data-load-url="{{ route('seed-runs.seeders.sources.questions', [$seedRun->id, $categoryGroup['key'], $sourceGroup['key']]) }}"
                                    data-loaded="false"
                                    aria-expanded="false">
                                <span data-toggle-label-collapsed>Показати</span>
                                <span class="hidden" data-toggle-label-expanded>Сховати</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" data-source-toggle-icon viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="hidden space-y-2 px-3 pb-3 pt-2 bg-white" data-source-questions data-seed-run-id="{{ $seedRun->id }}" data-category-key="{{ $categoryGroup['key'] }}" data-source-key="{{ $sourceGroup['key'] }}"></div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endif
