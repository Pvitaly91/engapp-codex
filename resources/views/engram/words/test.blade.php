@extends('layouts.engram')

@section('title', 'Тест слів')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10 text-stone-900">
    <div class="grid gap-8 lg:grid-cols-[280px_1fr]">
        <aside class="lg:sticky lg:top-6" x-data="{ open: false, isDesktop: window.innerWidth >= 1024 }" @resize.window="isDesktop = window.innerWidth >= 1024">
            <div class="mb-4 flex items-center justify-between lg:hidden">
                <h2 class="text-lg font-semibold">Фільтри</h2>
                <button type="button" @click="open = !open" class="inline-flex items-center gap-2 rounded-xl border border-border/70 px-3 py-2 text-sm font-medium">
                    <span x-show="!open">Показати</span>
                    <span x-show="open">Сховати</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                    </svg>
                </button>
            </div>

            <div class="rounded-2xl border border-border/70 bg-card p-4 shadow-soft" x-cloak x-show="open || isDesktop" data-animate>
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold">Теги</h3>
                    <a href="{{ route('words.public.test', ['reset' => 1]) }}" class="text-sm text-primary hover:underline">Скинути</a>
                </div>

                <form method="GET" action="{{ route('words.public.test') }}" class="mt-4 space-y-3">
                    <input type="hidden" name="filter" value="1">
                    <div class="grid grid-cols-1 gap-2">
                        @foreach($allTags as $tag)
                            <label class="flex items-center gap-3 rounded-xl border border-border/70 px-3 py-2 text-sm shadow-soft/40">
                                <input
                                    type="checkbox"
                                    name="tags[]"
                                    value="{{ $tag->name }}"
                                    class="h-4 w-4 rounded border-border/70 text-primary focus:ring-primary"
                                    {{ in_array($tag->name, $selectedTags) ? 'checked' : '' }}
                                >
                                <span class="text-stone-800">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex flex-wrap gap-2 pt-1">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-soft transition hover:opacity-90">
                            Застосувати
                        </button>
                        <a href="{{ route('words.public.test', ['reset' => 1]) }}" class="inline-flex items-center justify-center rounded-xl border border-border/70 px-4 py-2 text-sm font-semibold shadow-soft transition hover:bg-muted/40">
                            Скинути фільтр
                        </a>
                    </div>
                </form>
            </div>
        </aside>

        <div class="space-y-6" data-animate>
            <div class="rounded-3xl border border-border/70 bg-card px-6 py-6 shadow-soft" data-animate>
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-wide text-stone-500">Практика</p>
                        <h1 class="text-3xl font-bold text-stone-900">Тест слів</h1>
                        <p class="text-sm text-stone-600">Англійська ↔ Українська. Обирайте правильний переклад.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-2xl border border-border/70 bg-white/60 px-3 py-2 shadow-soft">
                            <p class="text-xs text-stone-500">Всього</p>
                            <p class="text-lg font-semibold text-stone-900">{{ $stats['total'] }} / {{ $totalCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-border/70 bg-white/60 px-3 py-2 shadow-soft">
                            <p class="text-xs text-stone-500">✅ Правильно</p>
                            <p class="text-lg font-semibold text-emerald-700">{{ $stats['correct'] }}</p>
                        </div>
                        <div class="rounded-2xl border border-border/70 bg-white/60 px-3 py-2 shadow-soft">
                            <p class="text-xs text-stone-500">❌ Помилок</p>
                            <p class="text-lg font-semibold text-rose-700">{{ $stats['wrong'] }}</p>
                        </div>
                        <div class="rounded-2xl border border-border/70 bg-white/60 px-3 py-2 shadow-soft">
                            <p class="text-xs text-stone-500">% Успіху</p>
                            <p class="text-lg font-semibold text-stone-900">{{ $percentage }}%</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-2">
                    <div class="flex items-center justify-between text-sm text-stone-600">
                        <span>Питання {{ $currentIndex ?? 0 }} із {{ $totalCount }}</span>
                        <span>{{ $progressPercent ?? 0 }}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-muted">
                        <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $progressPercent ?? 0 }}%"></div>
                    </div>
                </div>
            </div>

            @if($feedback)
                <div class="rounded-2xl border border-border/70 bg-card px-4 py-3 shadow-soft" aria-live="polite" data-animate data-animate-delay="75">
                    @if($feedback['isCorrect'])
                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-6 w-6 rounded-full bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto mt-1.5 h-4 w-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-emerald-700">Правильно!</p>
                                <p class="text-sm text-stone-700">{{ $feedback['word'] }} = {{ $feedback['correctAnswer'] }}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-6 w-6 rounded-full bg-rose-100 text-rose-700 ring-1 ring-rose-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto mt-1.5 h-4 w-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636 5.636 18.364m0-12.728 12.728 12.728" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-rose-700">Помилка</p>
                                <p class="text-sm text-stone-700">Твоя відповідь: {{ $feedback['userAnswer'] }}</p>
                                <p class="text-sm text-stone-700">Правильна: {{ $feedback['correctAnswer'] }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if($word)
                <div class="rounded-3xl border border-border/70 bg-card p-6 shadow-soft" data-animate data-animate-delay="50">
                    <div class="mb-4 flex flex-wrap gap-2">
                        @foreach($word->tags as $tag)
                            <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-stone-700">{{ $tag->name }}</span>
                        @endforeach
                    </div>

                    <div class="space-y-2">
                        @if($questionType === 'en_to_uk')
                            <p class="text-sm uppercase tracking-wide text-stone-500">Обери український переклад для слова:</p>
                            <p class="text-3xl font-bold text-stone-900">{{ $word->word }}</p>
                        @else
                            <p class="text-sm uppercase tracking-wide text-stone-500">Обери англійське слово для перекладу:</p>
                            <p class="text-3xl font-bold text-stone-900">{{ $translation }}</p>
                        @endif
                        <p class="text-xs text-stone-500">Натисни 1–5 щоб обрати варіант.</p>
                    </div>

                    <form method="POST" action="{{ route('words.public.test.check') }}" class="mt-6 space-y-3">
                        @csrf
                        <input type="hidden" name="word_id" value="{{ $word->id }}">
                        <input type="hidden" name="questionType" value="{{ $questionType }}">
                        <input type="hidden" name="redirect_route" value="words.public.test">

                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach($options as $index => $option)
                                <button
                                    type="submit"
                                    name="answer"
                                    value="{{ $option }}"
                                    data-option-index="{{ $index }}"
                                    class="group flex w-full items-center justify-between rounded-2xl border border-border/70 bg-white/70 px-4 py-3 text-left text-lg font-semibold shadow-soft transition hover:-translate-y-0.5 hover:border-primary/70 hover:shadow-lg"
                                >
                                    <span class="text-stone-900">{{ $option }}</span>
                                    <span class="text-xs text-stone-500">{{ $index + 1 }}</span>
                                </button>
                            @endforeach
                        </div>
                    </form>
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-border/70 bg-card p-6 text-center shadow-soft" data-animate>
                    <p class="text-lg font-semibold text-stone-800">Немає слів для тесту — змініть фільтр.</p>
                    <p class="text-sm text-stone-600">Спробуйте обрати інші теги або скинути налаштування.</p>
                </div>
            @endif

            <div class="flex flex-wrap gap-3" data-animate data-animate-delay="50">
                <form method="POST" action="{{ route('words.public.test.reset') }}">
                    @csrf
                    <input type="hidden" name="redirect_route" value="words.public.test">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-border/70 px-4 py-2 text-sm font-semibold shadow-soft transition hover:bg-muted/60">
                        Скинути прогрес
                    </button>
                </form>
                <a href="{{ route('words.public.test', ['reset' => 1]) }}" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-soft transition hover:opacity-90">
                    Скинути фільтр
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('keydown', function (event) {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName)) {
            return;
        }

        const index = parseInt(event.key, 10) - 1;
        if (index >= 0) {
            const button = document.querySelector(`[data-option-index="${index}"]`);
            if (button) {
                button.click();
            }
        }
    });
</script>
@endsection
