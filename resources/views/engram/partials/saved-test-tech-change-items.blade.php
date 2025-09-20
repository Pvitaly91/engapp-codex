@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Carbon;

    $changeCollection = ($changes ?? collect()) instanceof \Illuminate\Support\Collection
        ? $changes
        : collect($changes);
    $questionIdContext = $questionId ?? null;
    $refreshQuestions = isset($refreshQuestions) ? (bool) $refreshQuestions : true;
@endphp

@foreach($changeCollection as $change)
    @php
        $changeId = Arr::get($change, 'id');
        $summary = Arr::get($change, 'summary') ?: 'Зміна без опису';
        $type = Arr::get($change, 'change_type', 'generic');
        $questionId = $questionIdContext ?? Arr::get($change, 'question_id');
        $questionPreview = Arr::get($change, 'question_preview');
        $snapshot = Arr::get($change, 'snapshot');
        $previousSnapshot = Arr::get($change, 'previous_snapshot');
        $questionUuid = Arr::get($change, 'question_uuid');
        $createdAt = Arr::get($change, 'created_at');
        $createdForHumans = $createdAt ? Carbon::parse($createdAt)->diffForHumans(null, true) : null;
        $updatedAt = Arr::get($change, 'updated_at');
        $updatedForHumans = $updatedAt && $updatedAt !== $createdAt
            ? Carbon::parse($updatedAt)->diffForHumans(null, true)
            : null;
        $historyCollection = collect(Arr::get($change, 'history', []))
            ->filter(fn ($item) => is_array($item))
            ->values();
        $historyChangeEntries = $historyCollection->filter(function ($item) {
            return array_key_exists('snapshot', $item) || array_key_exists('payload', $item);
        });
        $historyChangeCount = $historyChangeEntries->count();
        $historyPreview = $historyChangeEntries
            ->sortBy(fn ($item) => $item['stored_at'] ?? '')
            ->values();
        if ($historyPreview->count() > 5) {
            $historyPreview = $historyPreview->slice($historyPreview->count() - 5)->values();
        }
    @endphp
    <article class="space-y-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <p class="text-sm font-semibold uppercase tracking-wide text-stone-500">{{ strtoupper($type) }}</p>
                <h3 class="text-lg font-bold text-stone-900">{{ $summary }}</h3>
                <dl class="flex flex-wrap gap-3 text-xs text-stone-500">
                    @if($questionId)
                        <div class="flex items-center gap-1">
                            <dt class="font-semibold uppercase tracking-wide">Питання</dt>
                            <dd class="rounded-full bg-stone-900 px-2 py-0.5 text-white">ID {{ $questionId }}</dd>
                        </div>
                    @endif
                    @if($questionUuid)
                        <div class="flex items-center gap-1">
                            <dt class="font-semibold uppercase tracking-wide">UUID</dt>
                            <dd class="rounded-full bg-stone-100 px-2 py-0.5 font-mono text-[11px] text-stone-600">{{ $questionUuid }}</dd>
                        </div>
                    @endif
                    @if($createdForHumans)
                        <div class="flex items-center gap-1">
                            <dt class="font-semibold uppercase tracking-wide">Додано</dt>
                            <dd>{{ $createdForHumans }} тому</dd>
                        </div>
                    @endif
                    @if($updatedForHumans)
                        <div class="flex items-center gap-1">
                            <dt class="font-semibold uppercase tracking-wide">Оновлено</dt>
                            <dd>{{ $updatedForHumans }} тому</dd>
                        </div>
                    @endif
                    @if($historyChangeCount > 0)
                        <div class="flex items-center gap-1">
                            <dt class="font-semibold uppercase tracking-wide">Ітерацій</dt>
                            <dd class="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-700">{{ $historyChangeCount }}</dd>
                        </div>
                    @endif
                </dl>
                @if($questionPreview)
                    <p class="rounded-lg bg-stone-100 px-3 py-2 text-sm text-stone-700">
                        <span class="font-semibold text-stone-600">Поточне питання:</span>
                        <span class="ml-2">{{ $questionPreview }}</span>
                    </p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="POST"
                      action="{{ route('saved-test.tech.changes.apply', [$test->slug, $changeId]) }}"
                      data-refresh-questions="{{ $refreshQuestions ? 'true' : 'false' }}"
                      data-refresh-changes="true"
                      data-refresh-question-changes="{{ $questionId !== null ? 'true' : 'false' }}"
                      @if($questionId !== null)
                          data-question-id="{{ $questionId }}"
                      @endif
                      @if($questionUuid)
                          data-question-uuid="{{ $questionUuid }}"
                      @endif
>
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white shadow hover:bg-emerald-700">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m7 10 2 2 4-4m4 2a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                        <span>Застосувати</span>
                    </button>
                </form>
                <form method="POST"
                      action="{{ route('saved-test.tech.changes.destroy', [$test->slug, $changeId]) }}"
                      data-confirm="Видалити цю зміну з черги?"
                      data-refresh-questions="false"
                      data-refresh-changes="true"
                      data-refresh-question-changes="{{ $questionId !== null ? 'true' : 'false' }}"
                      @if($questionId !== null)
                          data-question-id="{{ $questionId }}"
                      @endif
                      @if($questionUuid)
                          data-question-uuid="{{ $questionUuid }}"
                      @endif
>
                    @csrf
                    @method('delete')
                    <button type="submit"
                            class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-600 hover:bg-stone-100">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 8 8m0-8-8 8m9-3v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h2" />
                        </svg>
                        <span>Скасувати</span>
                    </button>
                </form>
            </div>
        </header>
        <div class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-4 text-sm text-stone-700">
            <h4 class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-stone-500">
                <span>Попередній перегляд</span>
            </h4>
            <div class="mt-3 space-y-3">
                @if($historyPreview->isNotEmpty())
                    <div class="rounded-xl border border-stone-200 bg-white/70 px-3 py-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Історія змін</p>
                        <ol class="mt-2 space-y-2 text-xs text-stone-600">
                            @foreach($historyPreview as $historyItem)
                                @php
                                    $historySummary = Arr::get($historyItem, 'summary');
                                    $historyEvent = Arr::get($historyItem, 'event');
                                    $historyType = Arr::get($historyItem, 'change_type');
                                    $historyStoredAt = Arr::get($historyItem, 'stored_at');
                                    $historyHuman = $historyStoredAt
                                        ? Carbon::parse($historyStoredAt)->diffForHumans(null, true)
                                        : null;

                                    if ($historyEvent === 'applied') {
                                        $historySummary = 'Зміни застосовано';
                                    } elseif ($historyEvent === 'discarded') {
                                        $historySummary = 'Зміну скасовано';
                                    } elseif (! $historySummary) {
                                        $historySummary = 'Оновлення питання';
                                    }
                                @endphp
                                <li class="rounded-lg bg-stone-100 px-3 py-2">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-sm font-semibold text-stone-700">{{ $historySummary }}</span>
                                        @if($historyHuman)
                                            <span class="text-[11px] uppercase tracking-wide text-stone-400">{{ $historyHuman }} тому</span>
                                        @endif
                                    </div>
                                    @if($historyType && ! in_array($historyEvent, ['applied', 'discarded'], true))
                                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-stone-500">{{ $historyType }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                        @if($historyChangeCount > $historyPreview->count())
                            <p class="mt-2 text-[11px] text-stone-400">Показано останні {{ $historyPreview->count() }} з {{ $historyChangeCount }} змін.</p>
                        @endif
                    </div>
                @endif
                @include('engram.partials.saved-test-tech-change-snapshot', [
                    'snapshot' => $snapshot,
                    'previousSnapshot' => $previousSnapshot,
                ])
            </div>
        </div>
    </article>
@endforeach
