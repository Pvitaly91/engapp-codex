<div class="grid gap-6 lg:grid-cols-[1fr_2fr]">
  <div class="lg:sticky lg:top-24 space-y-4" data-animate x-data="{ open: true }">
    <div class="bg-card border border-border/70 rounded-3xl p-5 shadow-soft">
      <div class="flex items-center justify-between gap-2">
        <div>
          <h2 class="text-lg font-semibold">Фільтр тегів</h2>
          <p class="text-sm text-muted-foreground">Оберіть теги, щоб сфокусувати тест</p>
        </div>
        <button class="lg:hidden rounded-full border border-border px-3 py-1 text-sm" @click="open = !open">
          <span x-text="open ? 'Сховати' : 'Показати'"></span>
        </button>
      </div>

      <div class="mt-4">
        <div class="flex flex-wrap gap-2" :class="{'hidden' : !open && window.innerWidth < 1024}">
          @forelse ($availableTags as $tag)
            <label class="inline-flex items-center gap-2 rounded-full border border-border px-3 py-1 text-sm cursor-pointer hover:border-primary hover:text-primary transition">
              <input type="checkbox" wire:model="selectedTags" value="{{ $tag }}" class="text-primary focus:ring-primary">
              <span>{{ $tag }}</span>
            </label>
          @empty
            <p class="text-sm text-muted-foreground">Немає доступних тегів</p>
          @endforelse
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
          <button wire:click="applyFilter" class="inline-flex items-center gap-2 rounded-full bg-primary px-4 py-2 text-white shadow-soft transition hover:-translate-y-0.5">
            Застосувати
          </button>
          <button wire:click="resetFilter" class="inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm transition hover:-translate-y-0.5">
            Скинути фільтр
          </button>
          <button wire:click="resetProgress" class="inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm transition hover:-translate-y-0.5">
            Скинути прогрес
          </button>
        </div>
      </div>
    </div>

    <div class="bg-card border border-border/70 rounded-3xl p-5 shadow-soft" data-animate data-animate-delay="0.05s">
      <h3 class="text-base font-semibold mb-3">Статистика</h3>
      <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4 text-sm">
        <div class="rounded-2xl border border-border/70 p-3">
          <dt class="text-muted-foreground">Всього</dt>
          <dd class="text-xl font-semibold">{{ $totalCount }}</dd>
        </div>
        <div class="rounded-2xl border border-border/70 p-3">
          <dt class="text-muted-foreground">✅ Правильно</dt>
          <dd class="text-xl font-semibold text-success">{{ $stats['correct'] }}</dd>
        </div>
        <div class="rounded-2xl border border-border/70 p-3">
          <dt class="text-muted-foreground">❌ Помилок</dt>
          <dd class="text-xl font-semibold text-destructive">{{ $stats['wrong'] }}</dd>
        </div>
        <div class="rounded-2xl border border-border/70 p-3">
          <dt class="text-muted-foreground">% точності</dt>
          <dd class="text-xl font-semibold">{{ $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100) : 0 }}%</dd>
        </div>
      </dl>
    </div>
  </div>

  <div class="space-y-5">
    <header class="bg-card border border-border/70 rounded-3xl p-6 shadow-soft" data-animate>
      <p class="text-sm text-muted-foreground">Обери правильний переклад</p>
      <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Тест слів</h1>
        <div class="text-sm text-muted-foreground">Питання {{ $currentIndex }} із {{ $totalCount }}</div>
      </div>
      <div class="mt-4 h-2 w-full rounded-full bg-muted">
        <div class="h-2 rounded-full bg-primary transition-all" style="width: {{ $progressPercent }}%"></div>
      </div>
    </header>

    @if ($totalCount === 0)
      <div class="bg-card border border-border/70 rounded-3xl p-6 shadow-soft" data-animate>
        <p class="text-lg font-semibold mb-2">Немає слів для тесту</p>
        <p class="text-muted-foreground">Спробуйте змінити фільтр або додати нові слова.</p>
        <div class="mt-4 flex flex-wrap gap-3">
          <button wire:click="resetFilter" class="rounded-full border border-border px-4 py-2 text-sm">Скинути фільтр</button>
          <button wire:click="resetProgress" class="rounded-full bg-primary px-4 py-2 text-white shadow-soft">Почати спочатку</button>
        </div>
      </div>
    @elseif($isComplete)
      <div class="bg-card border border-border/70 rounded-3xl p-6 shadow-soft space-y-4" data-animate>
        <div class="flex items-center gap-3">
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary text-xl">🎉</div>
          <div>
            <h2 class="text-xl font-semibold">Тест завершено</h2>
            <p class="text-muted-foreground">Ви відповіли на всі доступні питання</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 text-sm">
          <div class="rounded-2xl border border-border/70 p-3">
            <div class="text-muted-foreground">Правильно</div>
            <div class="text-xl font-semibold text-success">{{ $stats['correct'] }}</div>
          </div>
          <div class="rounded-2xl border border-border/70 p-3">
            <div class="text-muted-foreground">Помилок</div>
            <div class="text-xl font-semibold text-destructive">{{ $stats['wrong'] }}</div>
          </div>
          <div class="rounded-2xl border border-border/70 p-3">
            <div class="text-muted-foreground">Питань</div>
            <div class="text-xl font-semibold">{{ $stats['total'] }}</div>
          </div>
          <div class="rounded-2xl border border-border/70 p-3">
            <div class="text-muted-foreground">Точність</div>
            <div class="text-xl font-semibold">{{ $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100) : 0 }}%</div>
          </div>
        </div>
        <div class="flex flex-wrap gap-3">
          <button wire:click="resetProgress" class="rounded-full bg-primary px-4 py-2 text-white shadow-soft">Почати знову</button>
          <button wire:click="resetFilter" class="rounded-full border border-border px-4 py-2 text-sm">Скинути фільтр</button>
        </div>
      </div>
    @else
      <div class="space-y-4">
        @if ($feedback)
          <div class="rounded-3xl border shadow-soft p-4 flex items-start gap-3 {{ $feedback['type'] === 'success' ? 'bg-success/10 border-success/50 text-success' : 'bg-destructive/10 border-destructive/50 text-destructive' }}" aria-live="polite" data-animate>
            <div class="text-xl">{{ $feedback['type'] === 'success' ? '✅' : '⚠️' }}</div>
            <div class="flex-1">
              <div class="font-semibold">{{ $feedback['title'] }}</div>
              <div class="text-sm">{{ $feedback['message'] }}</div>
            </div>
            <button wire:click="$set('feedback', null)" class="text-sm text-muted-foreground hover:text-foreground">×</button>
          </div>
        @endif

        <div class="bg-card border border-border/70 rounded-3xl p-6 shadow-soft space-y-4" data-animate wire:key="word-test-{{ $wordId }}-{{ $questionType }}">
          <div class="flex flex-wrap gap-2">
            @foreach ($wordTags as $tag)
              <span class="rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground">{{ $tag }}</span>
            @endforeach
          </div>

          <div class="space-y-2">
            @if ($questionType === 'en_to_uk')
              <p class="text-sm text-muted-foreground">Обери український переклад для слова:</p>
              <div class="text-3xl font-bold">{{ $word['word'] ?? '' }}</div>
            @else
              <p class="text-sm text-muted-foreground">Обери англійське слово для перекладу:</p>
              <div class="text-3xl font-bold">{{ $word['translation'] ?? '' }}</div>
            @endif
          </div>

          <div class="grid gap-3 sm:grid-cols-2">
            @foreach ($options as $option)
              <button wire:click="submitAnswer(@js($option))" class="rounded-2xl border border-border px-4 py-3 text-left text-base font-semibold transition hover:-translate-y-0.5 hover:border-primary hover:shadow-soft">
                {{ $option }}
              </button>
            @endforeach
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
