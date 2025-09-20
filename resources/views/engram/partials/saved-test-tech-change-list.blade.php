@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Str;

    $grouped = ($groupedChanges ?? collect()) instanceof Collection
        ? $groupedChanges
        : collect($groupedChanges ?? []);
    $grouped = $grouped->map(fn ($items) => ($items instanceof Collection) ? $items : collect($items));
    $global = ($globalChanges ?? collect()) instanceof Collection
        ? $globalChanges
        : collect($globalChanges ?? []);
    $hasChanges = $global->isNotEmpty() || $grouped->filter(fn ($collection) => $collection->isNotEmpty())->isNotEmpty();
@endphp

@if(! $hasChanges)
    <div class="rounded-2xl border border-dashed border-stone-200 bg-white p-6 text-center text-sm text-stone-500">
        Змін наразі немає. Внесіть правки на вкладці «Питання», щоб додати їх до черги.
    </div>
@else
    @if($global->isNotEmpty())
        <section class="space-y-3 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <header class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-stone-900">Загальні зміни тесту</h3>
                <span class="text-xs font-semibold uppercase tracking-wide text-stone-500">{{ $global->count() }}</span>
            </header>
            @include('engram.partials.saved-test-tech-change-items', [
                'changes' => $global,
                'test' => $test,
                'questionId' => null,
                'refreshQuestions' => true,
            ])
        </section>
    @endif

    @foreach($grouped->filter(fn ($collection) => $collection->isNotEmpty()) as $questionId => $changes)
        @php
            $firstChange = $changes->first();
            $questionTitle = null;

            if (is_array($firstChange)) {
                $questionTitle = Arr::get($firstChange, 'snapshot.question')
                    ?? Arr::get($firstChange, 'previous_snapshot.question')
                    ?? Arr::get($firstChange, 'question_preview');
            }

            if ($questionTitle) {
                $questionTitle = Str::limit(trim(strip_tags($questionTitle)), 160);
            }

            $heading = $questionId !== null
                ? 'Питання ID ' . $questionId
                : 'Нове питання';
        @endphp
        <section class="space-y-3 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <header class="space-y-1">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-bold text-stone-900">{{ $heading }}</h3>
                    <span class="text-xs font-semibold uppercase tracking-wide text-stone-500">{{ $changes->count() }}</span>
                </div>
                @if($questionTitle)
                    <p class="text-sm text-stone-600">{{ $questionTitle }}</p>
                @endif
            </header>
            @include('engram.partials.saved-test-tech-change-items', [
                'changes' => $changes,
                'test' => $test,
                'questionId' => $questionId,
                'refreshQuestions' => true,
            ])
        </section>
    @endforeach
@endif
