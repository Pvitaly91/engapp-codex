@extends('layouts.engram')

@section('title', 'Тест слів — Gramlyze')

@section('content')
<div class="max-w-5xl mx-auto" x-data="{ showFilters: false }">
    {{-- Header Section --}}
    <header class="mb-8" data-animate>
        <h1 class="text-2xl sm:text-3xl font-bold text-foreground">Тест слів</h1>
        <p class="text-sm text-muted-foreground mt-1">
            @if($questionType === 'en_to_uk')
                Обери український переклад для англійського слова
            @else
                Обери англійське слово для українського перекладу
            @endif
        </p>
    </header>

    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Sidebar (Desktop) / Collapsible (Mobile) --}}
        <aside class="w-full lg:w-64 flex-shrink-0">
            {{-- Mobile Filter Toggle --}}
            <button
                @click="showFilters = !showFilters"
                class="lg:hidden w-full flex items-center justify-between rounded-2xl border border-border/70 bg-card px-4 py-3 text-sm font-medium shadow-soft mb-4"
            >
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Фільтри
                    @if(count($selectedTags) > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-primary text-primary-foreground">{{ count($selectedTags) }}</span>
                    @endif
                </span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showFilters }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Filter Panel --}}
            <div
                class="rounded-2xl border border-border/70 bg-card shadow-soft overflow-hidden"
                :class="{ 'hidden lg:block': !showFilters }"
                x-cloak
            >
                <form method="GET" action="{{ route('words.public.test') }}" class="p-4">
                    <input type="hidden" name="filter" value="1">
                    <h3 class="text-sm font-semibold text-foreground mb-3">Фільтр за тегами</h3>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @forelse($allTags as $tag)
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input
                                    type="checkbox"
                                    name="tags[]"
                                    value="{{ $tag->name }}"
                                    class="rounded border-border text-primary focus:ring-primary"
                                    {{ in_array($tag->name, $selectedTags) ? 'checked' : '' }}
                                >
                                <span class="text-sm text-muted-foreground group-hover:text-foreground transition">{{ $tag->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-muted-foreground">Немає доступних тегів</p>
                        @endforelse
                    </div>
                    <div class="mt-4 flex flex-col gap-2">
                        <button type="submit" class="w-full rounded-xl bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 transition">
                            Застосувати
                        </button>
                        <a href="{{ route('words.public.test', ['reset' => 1]) }}" class="w-full rounded-xl border border-border px-4 py-2 text-sm font-medium text-foreground text-center hover:bg-muted transition">
                            Скинути фільтр
                        </a>
                    </div>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 min-w-0">
            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6" data-animate data-animate-delay="100">
                <div class="rounded-2xl border border-border/70 bg-card p-4 shadow-soft">
                    <div class="text-xs text-muted-foreground mb-1">Всього</div>
                    <div class="text-xl font-bold text-foreground">{{ $stats['total'] }} / {{ $totalCount }}</div>
                </div>
                <div class="rounded-2xl border border-border/70 bg-card p-4 shadow-soft">
                    <div class="text-xs text-muted-foreground mb-1">✅ Правильно</div>
                    <div class="text-xl font-bold text-success">{{ $stats['correct'] }}</div>
                </div>
                <div class="rounded-2xl border border-border/70 bg-card p-4 shadow-soft">
                    <div class="text-xs text-muted-foreground mb-1">❌ Помилок</div>
                    <div class="text-xl font-bold text-destructive">{{ $stats['wrong'] }}</div>
                </div>
                <div class="rounded-2xl border border-border/70 bg-card p-4 shadow-soft">
                    <div class="text-xs text-muted-foreground mb-1">Точність</div>
                    <div class="text-xl font-bold text-foreground">{{ $percentage }}%</div>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="mb-6" data-animate data-animate-delay="150">
                <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
                    <span>Питання {{ $currentIndex + 1 }} із {{ $totalCount }}</span>
                    <span>{{ $progressPercent }}%</span>
                </div>
                <div class="w-full h-2 bg-muted rounded-full overflow-hidden">
                    <div class="h-full bg-primary transition-all duration-300 rounded-full" style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>

            {{-- Feedback Banner --}}
            @if(isset($feedback))
                <div class="mb-6" role="alert" aria-live="polite" data-animate data-animate-delay="200">
                    @if($feedback['isCorrect'])
                        <div class="rounded-2xl border border-success/30 bg-success/10 p-4">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">✅</span>
                                <div>
                                    <div class="font-semibold text-success">Правильно!</div>
                                    <p class="text-sm text-foreground mt-1">
                                        <b>{{ $feedback['word'] }}</b> = <b>{{ $feedback['correctAnswer'] }}</b>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-destructive/30 bg-destructive/10 p-4">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">❌</span>
                                <div>
                                    <div class="font-semibold text-destructive">Помилка</div>
                                    <p class="text-sm text-foreground mt-1">
                                        Твоя відповідь: <b>{{ $feedback['userAnswer'] }}</b><br>
                                        Правильна відповідь: <b>{{ $feedback['correctAnswer'] }}</b>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Question Card --}}
            @if($word)
                <div class="rounded-3xl border border-border/70 bg-card shadow-soft overflow-hidden" data-animate data-animate-delay="250">
                    <div class="p-6">
                        {{-- Word Tags --}}
                        @if($word->tags->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($word->tags as $tag)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Prompt --}}
                        <div class="mb-6">
                            @if($questionType === 'en_to_uk')
                                <p class="text-sm text-muted-foreground mb-2">Обери український переклад для слова:</p>
                                <p class="text-3xl sm:text-4xl font-bold text-primary">{{ $word->word }}</p>
                            @else
                                <p class="text-sm text-muted-foreground mb-2">Обери англійське слово для перекладу:</p>
                                <p class="text-3xl sm:text-4xl font-bold text-primary">{{ $translation }}</p>
                            @endif
                        </div>

                        {{-- Answer Options --}}
                        <form method="POST" action="{{ route('words.public.test.check') }}" id="answer-form">
                            @csrf
                            <input type="hidden" name="word_id" value="{{ $word->id }}">
                            <input type="hidden" name="questionType" value="{{ $questionType }}">
                            <input type="hidden" name="redirect_route" value="words.public.test">

                            <div class="grid gap-3">
                                @foreach($options as $index => $option)
                                    <button
                                        type="submit"
                                        name="answer"
                                        value="{{ $option }}"
                                        class="w-full text-left rounded-2xl border-2 border-border bg-background px-5 py-4 text-lg font-medium text-foreground hover:border-primary hover:bg-primary/5 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all duration-200"
                                        data-shortcut="{{ $index + 1 }}"
                                    >
                                        <span class="inline-flex items-center gap-3">
                                            <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-muted text-sm font-bold text-muted-foreground">{{ $index + 1 }}</span>
                                            <span>{{ $option }}</span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </form>

                        {{-- Keyboard Hint --}}
                        <p class="mt-4 text-xs text-muted-foreground text-center">
                            💡 Натисни <kbd class="px-1.5 py-0.5 rounded bg-muted text-foreground font-mono text-xs">1</kbd>–<kbd class="px-1.5 py-0.5 rounded bg-muted text-foreground font-mono text-xs">{{ count($options) }}</kbd> щоб обрати варіант
                        </p>
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="rounded-3xl border border-border/70 bg-card shadow-soft p-8 text-center" data-animate data-animate-delay="250">
                    <div class="text-4xl mb-4">📚</div>
                    <h2 class="text-lg font-semibold text-foreground mb-2">Немає слів для тесту</h2>
                    <p class="text-sm text-muted-foreground mb-4">Спробуй змінити фільтр тегів або скинути його.</p>
                    <a href="{{ route('words.public.test', ['reset' => 1]) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 transition">
                        Скинути фільтр
                    </a>
                </div>
            @endif

            {{-- Actions --}}
            <div class="mt-6 flex flex-wrap gap-3" data-animate data-animate-delay="300">
                <form method="POST" action="{{ route('words.public.test.reset') }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-border px-4 py-2 text-sm font-medium text-foreground hover:bg-muted transition">
                        Скинути прогрес
                    </button>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Keyboard shortcuts for answer selection
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        
        const key = parseInt(e.key);
        if (key >= 1 && key <= 9) {
            const button = document.querySelector(`button[data-shortcut="${key}"]`);
            if (button) {
                e.preventDefault();
                button.click();
            }
        }
    });
</script>
@endsection
