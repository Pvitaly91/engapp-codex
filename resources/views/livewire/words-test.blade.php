<div
  class="space-y-8"
  x-data="{
    showOverlay: false,
    loaderTimer: null,
    targets: ['submitAnswer', 'resetTest'],
  }"
  x-init="
    const component = this;
    const getMethod = (message) => message?.method ?? message?.payload?.method ?? message?.updateQueue?.[0]?.payload?.method ?? null;

    Livewire.hook('message.sent', (message) => {
      const method = getMethod(message);
      if (!component.targets.includes(method)) return;

      if (component.loaderTimer) clearTimeout(component.loaderTimer);
      component.loaderTimer = setTimeout(() => { component.showOverlay = true; }, 1500);
    });

    Livewire.hook('message.processed', (message) => {
      const method = getMethod(message);
      if (!component.targets.includes(method)) return;

      if (component.loaderTimer) clearTimeout(component.loaderTimer);
      component.loaderTimer = null;
      component.showOverlay = false;
    });
  "
>
  <style>
    @keyframes fade-in-soft {
      from { opacity: 0; transform: translateY(6px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pop-in {
      0% { transform: scale(0.97); opacity: 0; }
      60% { transform: scale(1.02); opacity: 1; }
      100% { transform: scale(1); }
    }

    @keyframes choice-glow {
      0% { transform: scale(1); box-shadow: 0 10px 30px -12px rgba(0, 0, 0, 0.25); }
      50% { transform: scale(1.02); box-shadow: 0 14px 40px -16px rgba(0, 0, 0, 0.3); }
      100% { transform: scale(1); box-shadow: 0 10px 30px -12px rgba(0, 0, 0, 0.25); }
    }

    @keyframes choice-shake {
      0%, 100% { transform: translateX(0); }
      15% { transform: translateX(-6px); }
      30% { transform: translateX(6px); }
      45% { transform: translateX(-5px); }
      60% { transform: translateX(5px); }
      75% { transform: translateX(-3px); }
      90% { transform: translateX(3px); }
    }

    .animate-soft { animation: fade-in-soft 280ms ease; }
    .animate-pop { animation: pop-in 220ms ease; }
    .animate-choice { animation: choice-glow 1s ease; }
    .animate-shake { animation: choice-shake 600ms ease; }

    .choice-correct {
      border-color: rgba(34, 197, 94, 0.35);
      background: linear-gradient(135deg, rgba(34, 197, 94, 0.12), rgba(34, 197, 94, 0.04));
      color: rgb(21, 128, 61);
    }

    .choice-wrong {
      border-color: rgba(239, 68, 68, 0.35);
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(239, 68, 68, 0.04));
      color: rgb(185, 28, 28);
    }

    .modal-backdrop {
      background: radial-gradient(circle at center, rgba(15, 23, 42, 0.16), rgba(15, 23, 42, 0.55));
      backdrop-filter: blur(8px);
    }
  </style>

  {{-- Loading Overlay --}}
  <div
    x-show="showOverlay"
    x-transition.opacity.duration.200ms
    x-cloak
    class="fixed inset-0 z-50 bg-black/20 backdrop-blur-sm items-center justify-center flex"
  >
    <div class="bg-white rounded-lg shadow-lg px-6 py-4 flex items-center gap-3 text-sm text-gray-700">
      <span class="w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></span>
      <span>Обробка...</span>
    </div>
  </div>

  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div class="space-y-1">
      <p class="text-sm text-muted-foreground">Практика перекладу · Активна мова: <span class="font-semibold text-primary">{{ strtoupper($activeLang) }}</span></p>
      <h1 class="text-3xl font-semibold text-foreground">Швидкий тест слів</h1>
      <p class="text-muted-foreground max-w-2xl">Виберіть правильний переклад без перезавантаження сторінки. Прогрес зберігається автоматично — можна оновити сторінку і продовжити з того ж місця.</p>
    </div>
    <div class="flex flex-wrap gap-3">
      <button 
        wire:click="resetTest" 
        wire:loading.attr="disabled"
        class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow disabled:opacity-60"
      >
        <span>Почати заново</span>
      </button>
    </div>
  </div>

  <div class="grid gap-4 md:grid-cols-[1.6fr_1fr]">
    <div class="rounded-2xl bg-card p-6 shadow-soft border border-border/70">
      <div class="flex items-center justify-between gap-3">
        <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-sm font-medium text-primary">
          <span class="h-2 w-2 rounded-full bg-primary"></span>
          <span>{{ $this->questionLabel }}</span>
        </div>
        @if($currentQuestion)
          <div class="text-sm text-muted-foreground">Питання {{ $stats['total'] + 1 }} з {{ $totalCount }}</div>
        @endif
      </div>

      @if($currentQuestion && !$failed)
        <div
          class="mt-6 space-y-6"
          wire:key="question-{{ $currentQuestion['word_id'] }}"
          x-data="{
            options: @js($currentQuestion['options']),
            correctAnswer: @js($currentQuestion['correct_answer']),
            selectedIndex: null,
            isPending: false,
            pick(index) {
              if (this.isPending) return;

              this.selectedIndex = index;
              this.isPending = true;

              setTimeout(() => {
                this.$wire.submitAnswer(index);
              }, 1500);
            },
            isCorrect(index) {
              return this.options[index] === this.correctAnswer;
            },
          }"
        >
          <div class="space-y-2">
            <p class="text-sm uppercase tracking-[0.08em] text-muted-foreground">Підказка</p>
            <div class="text-3xl font-semibold text-foreground animate-soft">{{ $currentQuestion['prompt'] }}</div>
            @if(!empty($currentQuestion['tags']))
              <p class="text-sm text-muted-foreground">{{ implode(', ', $currentQuestion['tags']) }}</p>
            @endif
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            @foreach($currentQuestion['options'] as $index => $option)
              <button
                type="button"
                wire:loading.attr="disabled"
                :disabled="isPending"
                @click="pick({{ $index }})"
                @class([
                  'flex items-center justify-between gap-3 rounded-xl border px-4 py-3 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:opacity-60 disabled:cursor-not-allowed',
                  'border-border/80 bg-muted text-foreground' => $highlightedButton !== $option,
                  'choice-correct animate-choice' => $highlightedButton === $option && $highlightCorrect,
                  'choice-wrong animate-choice animate-shake' => $highlightedButton === $option && !$highlightCorrect,
                ])
                x-bind:class="{
                  'choice-correct animate-choice': selectedIndex === {{ $index }} && isCorrect({{ $index }}),
                  'choice-wrong animate-choice animate-shake': selectedIndex === {{ $index }} && !isCorrect({{ $index }}),
                  'border-border/80 bg-muted text-foreground': selectedIndex !== {{ $index }},
                }"
              >
                {{ $option }}
              </button>
            @endforeach
          </div>
        </div>
      @elseif(!$failed)
        <div class="mt-6 text-muted-foreground">
          <p class="text-lg font-semibold text-foreground">Питання не знайдено</p>
          <p>Додайте більше слів з перекладом на активну мову або натисніть «Почати заново», щоб спробувати ще раз.</p>
        </div>
      @else
        <div class="mt-6 text-muted-foreground">
          <p class="text-lg font-semibold text-foreground">Тест не пройдено</p>
          <p>Ви зробили три помилки. Натисніть «Почати заново», щоб спробувати ще раз.</p>
        </div>
      @endif
    </div>

    <div class="space-y-4">
      <div class="rounded-2xl bg-card p-5 shadow-soft border border-border/70">
        <div class="flex items-center justify-between">
          <p class="text-sm font-semibold text-muted-foreground">Прогрес</p>
          <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">{{ $percentage }}%</span>
        </div>
        <div class="mt-3 h-3 rounded-full bg-muted">
          <div class="h-3 rounded-full bg-primary transition-all duration-500" style="width: {{ min($percentage, 100) }}%"></div>
        </div>
        <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
          <div class="rounded-xl bg-muted px-3 py-2">
            <dt class="text-muted-foreground">Усього</dt>
            <dd class="text-lg font-semibold text-foreground">{{ $stats['total'] }} / {{ $totalCount }}</dd>
          </div>
          <div class="rounded-xl bg-success/10 px-3 py-2 text-success">
            <dt class="text-sm">Правильно</dt>
            <dd class="text-lg font-semibold">{{ $stats['correct'] }}</dd>
          </div>
          <div class="rounded-xl bg-destructive/10 px-3 py-2 text-destructive">
            <dt class="text-sm">Помилки</dt>
            <dd class="text-lg font-semibold">{{ $stats['wrong'] }}</dd>
          </div>
        </dl>
      </div>

      @if($showFeedback && $lastResult)
        <div class="rounded-2xl bg-card p-5 shadow-soft border border-border/70 animate-pop">
          <div @class([
            'inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold',
            'bg-success/10 text-success' => $lastResult['isCorrect'],
            'bg-destructive/10 text-destructive' => !$lastResult['isCorrect'],
          ])>
            {{ $lastResult['isCorrect'] ? 'Правильно' : 'Помилка' }}
          </div>
          <div class="mt-3 space-y-2">
            <p class="text-xl font-semibold">
              @if($lastResult['questionType'] === 'en_to_uk')
                {{ $lastResult['word'] }} → {{ $lastResult['correctAnswer'] }}
              @else
                {{ $lastResult['translation'] }} → {{ $lastResult['correctAnswer'] }}
              @endif
            </p>
            <p class="text-muted-foreground">
              {{ $lastResult['isCorrect'] ? 'Чудово! Продовжуйте у тому ж дусі.' : 'Зверніть увагу на правильний варіант і спробуйте ще раз.' }}
            </p>
          </div>
        </div>
      @endif

      @if($completed)
        <div class="rounded-2xl bg-card p-5 shadow-soft border border-dashed border-border/80">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-muted-foreground">Тест завершено</p>
              <p class="text-lg font-semibold text-foreground">Всі доступні слова опрацьовано</p>
            </div>
          </div>
          <p class="mt-3 text-muted-foreground">Можете почати заново, щоб повторити матеріал.</p>
        </div>
      @endif
    </div>
  </div>

  {{-- Failure Modal --}}
  @if($showFailureModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="modal-backdrop absolute inset-0" wire:click="closeFailureModal"></div>
      <div class="relative mx-4 w-full max-w-xl rounded-2xl border border-destructive/30 bg-card p-6 shadow-2xl animate-pop">
        <div class="flex items-start gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10 text-destructive">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01M4.93 4.93l14.14 14.14"/></svg>
          </div>
          <div class="space-y-2">
            <div>
              <p class="text-sm font-semibold text-muted-foreground">Тест не пройдено</p>
              <p class="text-2xl font-semibold text-foreground">Перевищено ліміт у 3 помилки</p>
            </div>
            <p class="text-muted-foreground">Ви зробили три помилки поспіль. Натисніть «Спробувати ще раз», щоб пройти тест повторно.</p>
            <div class="flex flex-wrap gap-3 pt-2">
              <button 
                wire:click="resetTest"
                class="inline-flex items-center gap-2 rounded-xl border border-destructive/50 bg-destructive/10 px-4 py-2 text-sm font-semibold text-destructive shadow-sm transition hover:-translate-y-0.5 hover:shadow"
              >
                Спробувати ще раз
              </button>
              <button 
                wire:click="closeFailureModal"
                class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow"
              >
                Закрити
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
