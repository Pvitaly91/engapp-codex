<div class="max-w-5xl mx-auto" x-data="{ showFilters: $wire.entangle('showFilters') }">
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
                wire:click="toggleFilters"
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
                <div class="p-4" x-data="{ localTags: @js($selectedTags) }">
                    <h3 class="text-sm font-semibold text-foreground mb-3">Фільтр за тегами</h3>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @forelse($allTags as $tag)
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input
                                    type="checkbox"
                                    value="{{ $tag->name }}"
                                    x-model="localTags"
                                    class="rounded border-border text-primary focus:ring-primary"
                                >
                                <span class="text-sm text-muted-foreground group-hover:text-foreground transition">{{ $tag->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-muted-foreground">Немає доступних тегів</p>
                        @endforelse
                    </div>
                    <div class="mt-4 flex flex-col gap-2">
                        <button
                            type="button"
                            @click="$wire.applyFilter(localTags)"
                            class="w-full rounded-xl bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 transition"
                        >
                            Застосувати
                        </button>
                        <button
                            type="button"
                            wire:click="resetFilter"
                            class="w-full rounded-xl border border-border px-4 py-2 text-sm font-medium text-foreground text-center hover:bg-muted transition"
                        >
                            Скинути фільтр
                        </button>
                    </div>
                </div>
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
            @if($feedback)
                <div class="mb-6" role="alert" aria-live="polite" wire:key="feedback-{{ $stats['total'] }}">
                    @if($feedback['isCorrect'])
                        <div class="rounded-2xl border border-success/30 bg-success/10 p-4">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">✅</span>
                                <div class="flex-1">
                                    <div class="font-semibold text-success">Правильно!</div>
                                    <p class="text-sm text-foreground mt-1">
                                        <b>{{ $feedback['word'] }}</b> = <b>{{ $feedback['correctAnswer'] }}</b>
                                    </p>
                                </div>
                                <button wire:click="dismissFeedback" class="text-muted-foreground hover:text-foreground">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-destructive/30 bg-destructive/10 p-4">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">❌</span>
                                <div class="flex-1">
                                    <div class="font-semibold text-destructive">Помилка</div>
                                    <p class="text-sm text-foreground mt-1">
                                        Твоя відповідь: <b>{{ $feedback['userAnswer'] }}</b><br>
                                        Правильна відповідь: <b>{{ $feedback['correctAnswer'] }}</b>
                                    </p>
                                </div>
                                <button wire:click="dismissFeedback" class="text-muted-foreground hover:text-foreground">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Complete State --}}
            @if($isComplete)
                <div class="rounded-3xl border border-border/70 bg-card shadow-soft overflow-hidden" data-animate data-animate-delay="250">
                    <div class="p-6 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-success/10 mb-4">
                            <span class="text-4xl">🎉</span>
                        </div>
                        <h2 class="text-2xl font-bold text-foreground mb-2">Тест завершено!</h2>
                        <p class="text-sm text-muted-foreground mb-6">Чудова робота! Ось твої результати:</p>

                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-2 gap-4 mb-6 max-w-md mx-auto">
                            <div class="rounded-2xl bg-muted/50 p-4">
                                <div class="text-xs text-muted-foreground mb-1">Всього питань</div>
                                <div class="text-2xl font-bold text-foreground">{{ $stats['total'] }}</div>
                                <div class="text-xs text-muted-foreground">із {{ $totalCount }}</div>
                            </div>
                            <div class="rounded-2xl bg-muted/50 p-4">
                                <div class="text-xs text-muted-foreground mb-1">Точність</div>
                                <div class="text-2xl font-bold text-primary">{{ $percentage }}%</div>
                            </div>
                            <div class="rounded-2xl bg-success/10 p-4">
                                <div class="text-xs text-muted-foreground mb-1">✅ Правильно</div>
                                <div class="text-2xl font-bold text-success">{{ $stats['correct'] }}</div>
                            </div>
                            <div class="rounded-2xl bg-destructive/10 p-4">
                                <div class="text-xs text-muted-foreground mb-1">❌ Помилок</div>
                                <div class="text-2xl font-bold text-destructive">{{ $stats['wrong'] }}</div>
                            </div>
                        </div>

                        {{-- Encouragement Message --}}
                        <div class="mb-6">
                            @if($percentage >= 90)
                                <p class="text-lg">🏆 Відмінний результат! Ти чудово знаєш ці слова!</p>
                            @elseif($percentage >= 70)
                                <p class="text-lg">👍 Гарна робота! Продовжуй практикуватись!</p>
                            @elseif($percentage >= 50)
                                <p class="text-lg">💪 Непогано! Є куди рости, спробуй ще раз!</p>
                            @else
                                <p class="text-lg">📚 Не здавайся! Практика — ключ до успіху!</p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <button
                                wire:click="restartTest"
                                class="rounded-2xl bg-primary px-6 py-3 text-base font-semibold text-primary-foreground shadow-soft hover:bg-primary/90 transition"
                            >
                                Почати знову
                                @if(count($selectedTags) > 0)
                                    <span class="block text-xs font-normal opacity-80 mt-0.5">з тими ж тегами</span>
                                @endif
                            </button>
                            <button
                                wire:click="resetFilter"
                                class="rounded-2xl border border-border px-6 py-3 text-base font-semibold text-foreground hover:bg-muted transition"
                            >
                                Нова гра
                                <span class="block text-xs font-normal text-muted-foreground mt-0.5">(скинути фільтр)</span>
                            </button>
                        </div>
                    </div>
                </div>
            @elseif($wordId)
                {{-- Question Card --}}
                <div class="rounded-3xl border border-border/70 bg-card shadow-soft overflow-hidden" data-animate data-animate-delay="250" wire:key="question-{{ $wordId }}">
                    <div class="p-6">
                        {{-- Word Tags --}}
                        @if(count($wordTags) > 0)
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($wordTags as $tag)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Prompt --}}
                        <div class="mb-6">
                            @if($questionType === 'en_to_uk')
                                <p class="text-sm text-muted-foreground mb-2">Обери український переклад для слова:</p>
                                <p class="text-3xl sm:text-4xl font-bold text-primary">{{ $wordText }}</p>
                            @else
                                <p class="text-sm text-muted-foreground mb-2">Обери англійське слово для перекладу:</p>
                                <p class="text-3xl sm:text-4xl font-bold text-primary">{{ $translation }}</p>
                            @endif
                        </div>

                        {{-- Answer Options --}}
                        <div class="grid gap-3">
                            @foreach($options as $index => $option)
                                <button
                                    type="button"
                                    wire:click="submitAnswerByIndex({{ $index }})"
                                    wire:loading.attr="disabled"
                                    class="w-full text-left rounded-2xl border-2 border-border bg-background px-5 py-4 text-lg font-medium text-foreground hover:border-primary hover:bg-primary/5 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all duration-200 disabled:opacity-50"
                                    data-shortcut="{{ $index + 1 }}"
                                >
                                    <span class="inline-flex items-center gap-3">
                                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-muted text-sm font-bold text-muted-foreground">{{ $index + 1 }}</span>
                                        <span>{{ $option }}</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        {{-- Loading indicator --}}
                        <div wire:loading class="mt-4 text-center text-sm text-muted-foreground">
                            Завантаження...
                        </div>

                        {{-- Keyboard Hint --}}
                        <p class="mt-4 text-xs text-muted-foreground text-center" wire:loading.remove>
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
                    <button
                        wire:click="resetFilter"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 transition"
                    >
                        Скинути фільтр
                    </button>
                </div>
            @endif

            {{-- Actions --}}
            @if(!$isComplete && $wordId)
                <div class="mt-6 flex flex-wrap gap-3" data-animate data-animate-delay="300">
                    <button
                        wire:click="resetProgress"
                        class="rounded-xl border border-border px-4 py-2 text-sm font-medium text-foreground hover:bg-muted transition"
                    >
                        Скинути прогрес
                    </button>
                </div>
            @endif
        </main>
    </div>
</div>

@script
<script>
    // Keyboard shortcuts for answer selection
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

        const key = parseInt(e.key);
        if (key >= 1 && key <= 9) {
            const button = document.querySelector(`button[data-shortcut="${key}"]`);
            if (button && !button.disabled) {
                e.preventDefault();
                button.click();
            }
        }
    });
</script>
@endscript
