@php
    $questions = collect($questions ?? []);
@endphp

@if($questions->isEmpty())
    <p class="text-xs text-gray-500">Питань не знайдено.</p>
@else
    @foreach($questions as $question)
        <div class="border border-slate-200 rounded-lg bg-slate-50 px-3 py-2 text-left text-sm leading-relaxed" data-question-container data-question-id="{{ $question['id'] }}" data-seed-run-id="{{ $seedRunId }}" data-category-key="{{ $categoryKey }}" data-source-key="{{ $sourceKey }}">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <button type="button"
                        class="group flex flex-1 flex-col items-start gap-2 text-left text-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-50"
                        data-question-toggle
                        data-load-url="{{ route('seed-runs.questions.answers', [$seedRunId, $question['id']]) }}"
                        aria-expanded="false">
                    <div class="space-y-1 w-full">{!! $question['highlighted_text'] !!}</div>
                    <div class="flex items-center gap-1 text-xs font-semibold text-blue-600">
                        <svg class="h-3.5 w-3.5 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-question-toggle-icon>
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                        </svg>
                        <span data-toggle-label-collapsed>Показати варіанти</span>
                        <span class="hidden" data-toggle-label-expanded>Сховати варіанти</span>
                    </div>
                </button>
                <form method="POST"
                      action="{{ route('seed-runs.questions.destroy', $question['id']) }}"
                      data-question-delete-form
                      data-confirm="Видалити це питання?"
                      data-question-id="{{ $question['id'] }}"
                      data-seed-run-id="{{ $seedRunId }}"
                      data-category-key="{{ $categoryKey }}"
                      data-source-key="{{ $sourceKey }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-red-700 px-2.5 py-1 rounded-full bg-red-50 hover:bg-red-100 transition w-full sm:w-auto">
                        <i class="fa-solid fa-trash-can"></i>
                        Видалити
                    </button>
                </form>
            </div>
            <div class="hidden border-t border-slate-200 pt-3 mt-3 space-y-3 text-sm text-slate-700" data-question-details>
                <div data-question-tags
                     data-load-url="{{ route('seed-runs.questions.tags', [$seedRunId, $question['id']]) }}"
                     class="text-sm text-slate-700"></div>
                <div data-question-answers class="text-sm text-slate-700"></div>
            </div>
        </div>
    @endforeach
@endif
