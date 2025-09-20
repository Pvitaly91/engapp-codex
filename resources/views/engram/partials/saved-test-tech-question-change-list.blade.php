@php
    $changeCollection = ($changes ?? collect()) instanceof \Illuminate\Support\Collection
        ? $changes
        : collect($changes);
@endphp

@if($changeCollection->isEmpty())
    <div class="rounded-xl border border-dashed border-amber-200 bg-white px-4 py-3 text-sm text-amber-700/80">
        Змін для цього питання наразі немає.
    </div>
@else
    @include('engram.partials.saved-test-tech-change-items', [
        'changes' => $changeCollection,
        'test' => $test,
        'questionId' => $questionId ?? null,
        'refreshQuestions' => true,
    ])
@endif
