@php
    $questions = collect($questions ?? []);
@endphp

@if($questions->isEmpty())
    <p class="text-xs text-gray-500">Питань не знайдено.</p>
@else
    @foreach($questions as $question)
        <div class="border border-slate-200 rounded-lg bg-slate-50 px-3 py-2 text-left text-sm leading-relaxed" data-question-container data-question-id="{{ $question['id'] }}" data-seed-run-id="{{ $seedRunId }}" data-category-key="{{ $categoryKey }}" data-source-key="{{ $sourceKey }}">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div class="text-gray-800 space-y-1">{!! $question['highlighted_text'] !!}</div>
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
        </div>
    @endforeach
@endif
