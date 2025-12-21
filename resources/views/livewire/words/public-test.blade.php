@section('title', 'Тест слів')

<div class="min-h-[60vh]">
    {{-- Header Section --}}
    <div class="mb-8" data-animate data-animate-delay="0">
        <h1 class="text-3xl sm:text-4xl font-bold text-foreground mb-2">Тест слів</h1>
        <p class="text-muted-foreground">Обери правильний переклад слова</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
        {{-- Sidebar - Tags Filter --}}
        <aside class="lg:sticky lg:top-24 lg:self-start" data-animate data-animate-delay="100">
            <div class="bg-card border border-border/70 rounded-2xl shadow-soft p-5"
                 x-data="{ isOpen: window.innerWidth >= 1024 }"
                 x-init="window.addEventListener('resize', () => { if (window.innerWidth >= 1024) isOpen = true; })">
                <button
                    type="button"
                    class="flex w-full items-center justify-between lg:cursor-default"
                    @click="isOpen = !isOpen"
                    :aria-expanded="isOpen"
                >
                    <h2 class="text-lg font-semibold text-card-foreground">Фільтр тегів</h2>
                    <svg
                        class="h-5 w-5 text-muted-foreground transition-transform lg:hidden"
                        :class="{ 'rotate-180': isOpen }"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="isOpen" x-collapse class="mt-4">
                    @if(count($availableTags) === 0)
                        <p class="text-sm text-muted-foreground">Немає доступних тегів</p>
                    @else
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach($availableTags as $tag)
                                <label
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium cursor-pointer transition-all border
                                        {{ in_array($tag['name'], $selectedTags) 
                                            ? 'bg-primary text-primary-foreground border-primary' 
                                            : 'bg-muted/50 text-muted-foreground border-border hover:bg-muted hover:text-foreground' }}"
                                >
                                    <input
                                        type="checkbox"
                                        wire:model="selectedTags"
                                        value="{{ $tag['name'] }}"
                                        class="sr-only"
                                    >
                                    {{ $tag['name'] }}
                                </label>
                            @endforeach
                        </div>

                        <div class="flex flex-col gap-2">
                            <button
                                type="button"
                                wire:click="applyFilter"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                Застосувати
                            </button>
                            @if(count($selectedTags) > 0)
                                <button
                                    type="button"
                                    wire:click="resetFilter"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-background px-4 py-2.5 text-sm font-semibold text-foreground transition hover:bg-muted"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Скинути фільтр
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="space-y-6" data-animate data-animate-delay="200">
            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-card border border-border/70 rounded-xl shadow-soft p-4 text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-foreground">{{ $totalCount }}</div>
                    <div class="text-xs sm:text-sm text-muted-foreground mt-1">Всього слів</div>
                </div>
                <div class="bg-card border border-border/70 rounded-xl shadow-soft p-4 text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-success">{{ $stats['correct'] }}</div>
                    <div class="text-xs sm:text-sm text-muted-foreground mt-1">✅ Правильно</div>
                </div>
                <div class="bg-card border border-border/70 rounded-xl shadow-soft p-4 text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-destructive">{{ $stats['wrong'] }}</div>
                    <div class="text-xs sm:text-sm text-muted-foreground mt-1">❌ Помилок</div>
                </div>
                <div class="bg-card border border-border/70 rounded-xl shadow-soft p-4 text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-primary">
                        {{ $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100) : 0 }}%
                    </div>
                    <div class="text-xs sm:text-sm text-muted-foreground mt-1">Точність</div>
                </div>
            </div>

            {{-- Progress Bar --}}
            @if($totalCount > 0 && !$isComplete)
                <div class="bg-card border border-border/70 rounded-xl shadow-soft p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-foreground">
                            Питання {{ min($currentIndex, $totalCount) }} із {{ $totalCount }}
                        </span>
                        <span class="text-sm text-muted-foreground">{{ $progressPercent }}%</span>
                    </div>
                    <div class="h-2 bg-muted rounded-full overflow-hidden">
                        <div
                            class="h-full bg-primary rounded-full transition-all duration-300"
                            style="width: {{ $progressPercent }}%"
                        ></div>
                    </div>
                </div>
            @endif

            {{-- Feedback Banner --}}
            @if($feedback)
                <div
                    class="rounded-2xl border p-4 sm:p-5 flex items-start gap-4 {{ $feedback['type'] === 'success' ? 'bg-success/10 border-success/30' : 'bg-destructive/10 border-destructive/30' }}"
                    role="alert"
                    aria-live="polite"
                >
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center {{ $feedback['type'] === 'success' ? 'bg-success/20 text-success' : 'bg-destructive/20 text-destructive' }}">
                        @if($feedback['type'] === 'success')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold {{ $feedback['type'] === 'success' ? 'text-success' : 'text-destructive' }}">
                            {{ $feedback['title'] }}
                        </h3>
                        <p class="text-sm {{ $feedback['type'] === 'success' ? 'text-success/80' : 'text-destructive/80' }} mt-1">
                            {{ $feedback['message'] }}
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="clearFeedback"
                        class="flex-shrink-0 p-1 rounded-lg {{ $feedback['type'] === 'success' ? 'text-success/60 hover:text-success hover:bg-success/10' : 'text-destructive/60 hover:text-destructive hover:bg-destructive/10' }} transition"
                        aria-label="Закрити"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- Empty State (no words for current filter) --}}
            @if($totalCount === 0)
                <div class="bg-card border border-border/70 rounded-3xl shadow-soft p-6 sm:p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-warning/20 flex items-center justify-center">
                        <svg class="w-8 h-8 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-foreground mb-2">Немає слів для тесту</h2>
                    <p class="text-muted-foreground mb-6">
                        Змініть фільтр тегів або скиньте його, щоб побачити всі слова
                    </p>
                    @if(count($selectedTags) > 0)
                        <button
                            type="button"
                            wire:click="resetFilter"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Скинути фільтр
                        </button>
                    @endif
                </div>

            {{-- Complete State (user answered all questions) --}}
            @elseif($isComplete)
                <div class="bg-card border border-border/70 rounded-3xl shadow-soft p-6 sm:p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-success/20 flex items-center justify-center">
                        <svg class="w-8 h-8 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-foreground mb-2">Тест завершено!</h2>
                    <p class="text-muted-foreground mb-6">
                        Ви відповіли на всі питання
                    </p>

                    <div class="grid grid-cols-3 gap-4 mb-8 max-w-md mx-auto">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-foreground">{{ $stats['total'] }}</div>
                            <div class="text-xs text-muted-foreground">Всього</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-success">{{ $stats['correct'] }}</div>
                            <div class="text-xs text-muted-foreground">Правильно</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary">
                                {{ $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100) : 0 }}%
                            </div>
                            <div class="text-xs text-muted-foreground">Точність</div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button
                            type="button"
                            wire:click="resetProgress"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Почати знову
                        </button>
                        @if(count($selectedTags) > 0)
                            <button
                                type="button"
                                wire:click="resetFilter"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-background px-6 py-3 text-sm font-semibold text-foreground transition hover:bg-muted"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Скинути фільтр
                            </button>
                        @endif
                    </div>
                </div>

            {{-- Question Card --}}
            @elseif($word)
                <div 
                    class="bg-card border border-border/70 rounded-3xl shadow-soft p-6 sm:p-8"
                    wire:key="word-test-{{ $wordId }}-{{ $questionType }}"
                >
                    {{-- Word Tags --}}
                    @if(count($wordTags) > 0)
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach($wordTags as $tag)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-secondary/20 text-secondary">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Question --}}
                    <div class="mb-8">
                        @if($questionType === 'en_to_uk')
                            <p class="text-muted-foreground mb-3">Обери український переклад для слова:</p>
                            <p class="text-3xl sm:text-4xl font-bold text-primary">{{ $word['word'] }}</p>
                        @else
                            <p class="text-muted-foreground mb-3">Обери англійське слово для перекладу:</p>
                            <p class="text-3xl sm:text-4xl font-bold text-primary">{{ $word['translation'] }}</p>
                        @endif
                    </div>

                    {{-- Answer Options --}}
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($options as $option)
                            <button
                                type="button"
                                wire:click="submitAnswer('{{ addslashes($option) }}')"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="w-full text-left px-5 py-4 rounded-xl border-2 border-border bg-background text-foreground font-medium transition-all hover:border-primary hover:bg-primary/5 hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 active:translate-y-0"
                            >
                                {{ $option }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-wrap gap-3 justify-center">
                    <button
                        type="button"
                        wire:click="resetProgress"
                        class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2.5 text-sm font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Скинути прогрес
                    </button>
                    @if(count($selectedTags) > 0)
                        <button
                            type="button"
                            wire:click="resetFilter"
                            class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2.5 text-sm font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Скинути фільтр
                        </button>
                    @endif
                </div>
            @endif
        </main>
    </div>
</div>
