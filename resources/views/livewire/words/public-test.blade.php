<div class="grid gap-6 lg:grid-cols-[280px_1fr]">
  <aside class="lg:sticky lg:top-[calc(var(--header-height,80px)+16px)] h-fit" x-data="{ open: false }">
    <div class="flex items-center justify-between lg:hidden">
      <h2 class="text-lg font-semibold">Фільтри</h2>
      <button
        type="button"
        class="rounded-lg border border-border px-3 py-1.5 text-sm font-semibold text-foreground"
        @click="open = !open"
        :aria-expanded="open"
      >
        <span x-show="!open">Відкрити</span>
        <span x-show="open">Сховати</span>
      </button>
    </div>

    <div class="mt-3 rounded-2xl border border-border bg-card shadow-soft p-4 space-y-4" :class="{ 'hidden': !open }" x-transition x-cloak>
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Вибір тегів</p>
          <h3 class="text-lg font-semibold">Теми слів</h3>
        </div>
        <button
          type="button"
          wire:click="resetFilter"
          class="text-sm font-semibold text-primary hover:text-primary/80"
        >
          Скинути
        </button>
      </div>

      <div class="flex flex-wrap gap-2">
        @forelse($allTags as $tag)
          <label class="inline-flex items-center gap-2 rounded-full border border-border px-3 py-1.5 text-sm font-medium cursor-pointer transition hover:border-primary/70 hover:bg-primary/5">
            <input
              type="checkbox"
              value="{{ $tag['name'] }}"
              wire:model="selectedTags"
              class="h-4 w-4 rounded border-border text-primary focus:ring-primary"
            >
            <span>{{ $tag['name'] }}</span>
          </label>
        @empty
          <p class="text-sm text-muted-foreground">Теги поки що не додані.</p>
        @endforelse
      </div>

      <div class="flex flex-wrap gap-3">
        <button
          type="button"
          wire:click="applyFilter"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
        >
          Застосувати фільтр
        </button>
        <button
          type="button"
          wire:click="resetProgress"
          class="inline-flex items-center justify-center gap-2 rounded-xl border border-border px-4 py-2 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary"
        >
          Почати спочатку
        </button>
      </div>
    </div>
  </aside>

  <div class="space-y-6">
    <div class="space-y-2" data-animate>
      <p class="text-sm font-semibold text-primary uppercase tracking-wide">Практика лексики</p>
      <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-3xl font-bold">Тест слів</h1>
        <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary">Livewire</span>
        <span class="inline-flex items-center gap-1 rounded-full bg-secondary/10 px-3 py-1 text-sm font-semibold text-secondary">Новий дизайн</span>
      </div>
      <p class="text-muted-foreground max-w-3xl">Обирайте правильний переклад або слово. Фільтруйте за тегами, відслідковуйте прогрес та повторюйте спроби без перезавантаження сторінки.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-4" data-animate data-animate-delay="100">
      <div class="rounded-2xl bg-card p-4 shadow-soft border border-border">
        <p class="text-sm text-muted-foreground">Всього</p>
        <p class="text-2xl font-bold">{{ $totalCount }}</p>
      </div>
      <div class="rounded-2xl bg-card p-4 shadow-soft border border-border">
        <p class="text-sm text-muted-foreground">✅ Правильно</p>
        <p class="text-2xl font-bold text-success">{{ $stats['correct'] }}</p>
      </div>
      <div class="rounded-2xl bg-card p-4 shadow-soft border border-border">
        <p class="text-sm text-muted-foreground">❌ Помилки</p>
        <p class="text-2xl font-bold text-destructive">{{ $stats['wrong'] }}</p>
      </div>
      <div class="rounded-2xl bg-card p-4 shadow-soft border border-border">
        <p class="text-sm text-muted-foreground">% Точність</p>
        @php
          $percentage = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;
        @endphp
        <p class="text-2xl font-bold">{{ $percentage }}%</p>
      </div>
    </div>

    <div class="rounded-2xl bg-card border border-border shadow-soft p-4" data-animate data-animate-delay="150">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-sm text-muted-foreground">Прогрес</p>
          @php
            $current = $totalCount > 0 ? min($stats['total'] + 1, $totalCount) : 0;
          @endphp
          <p class="text-lg font-semibold">Питання {{ $isComplete ? $totalCount : $current }} із {{ $totalCount }}</p>
        </div>
        <div class="text-sm text-muted-foreground">{{ $stats['total'] }} відповідей</div>
      </div>
      <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-muted">
        @php
          $progress = $totalCount > 0 ? min(($stats['total'] / $totalCount) * 100, 100) : 0;
        @endphp
        <div class="h-full bg-gradient-to-r from-primary to-secondary transition-all" style="width: {{ $progress }}%"></div>
      </div>
    </div>

    @if($feedback)
      <div class="rounded-2xl border {{ $feedback['type'] === 'success' ? 'border-success/40 bg-success/10 text-success' : 'border-destructive/40 bg-destructive/10 text-destructive' }} px-4 py-3 flex items-start gap-3" aria-live="polite" data-animate data-animate-delay="200">
        <div class="mt-1">{{ $feedback['type'] === 'success' ? '✅' : '⚠️' }}</div>
        <div class="flex-1 space-y-1">
          <p class="font-semibold">{{ $feedback['title'] }}</p>
          <p class="text-sm leading-relaxed">{{ $feedback['message'] }}</p>
        </div>
        <button type="button" class="text-muted-foreground hover:text-foreground" wire:click="closeFeedback">×</button>
      </div>
    @endif

    <div class="rounded-2xl border border-dashed border-border bg-muted/40 p-6" data-animate data-animate-delay="220">
      @if($totalCount === 0)
        <div class="text-center space-y-3">
          <p class="text-lg font-semibold">Немає слів для тесту</p>
          <p class="text-muted-foreground">Спробуйте змінити фільтр або додати нові слова.</p>
          <div class="flex justify-center gap-3">
            <button type="button" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white" wire:click="resetFilter">Скинути фільтр</button>
            <button type="button" class="rounded-xl border border-border px-4 py-2 text-sm font-semibold" wire:click="applyFilter">Оновити список</button>
          </div>
        </div>
      @elseif($isComplete)
        <div class="text-center space-y-4">
          <p class="text-2xl font-bold">Тест завершено!</p>
          <p class="text-muted-foreground">Ви пройшли всі доступні слова. Можна почати знову або змінити фільтр.</p>
          <div class="flex flex-wrap justify-center gap-3">
            <button type="button" class="rounded-xl bg-primary px-5 py-2 text-sm font-semibold text-white shadow-soft" wire:click="resetProgress">Почати знову</button>
            <button type="button" class="rounded-xl border border-border px-5 py-2 text-sm font-semibold" wire:click="resetFilter">Скинути фільтр</button>
          </div>
        </div>
      @elseif($wordId && $word)
        <div wire:key="word-test-{{ $wordId }}-{{ $questionType }}" class="space-y-4">
          <div class="flex flex-wrap gap-2">
            @forelse($word['tags'] as $tag)
              <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $tag }}</span>
            @empty
              <span class="text-xs text-muted-foreground">Без тегів</span>
            @endforelse
          </div>

          <div class="space-y-3">
            @if($questionType === 'en_to_uk')
              <p class="text-sm text-muted-foreground">Оберіть український переклад для:</p>
              <p class="text-3xl font-bold">{{ $word['word'] }}</p>
            @else
              <p class="text-sm text-muted-foreground">Оберіть англійське слово для:</p>
              <p class="text-3xl font-bold">{{ $word['translation'] }}</p>
            @endif
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            @foreach($options as $option)
              <button
                type="button"
                wire:click="submitAnswer(@js($option))"
                class="w-full rounded-xl border border-border bg-white/80 px-4 py-3 text-left text-base font-semibold shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-lg"
              >
                {{ $option }}
              </button>
            @endforeach
          </div>
        </div>
      @else
        <p class="text-muted-foreground">Завантаження питання...</p>
      @endif
    </div>
  </div>
</div>
