@extends('layouts.app')

@section('title', 'Менеджер Artisan команд')

@section('content')
  <div class="max-w-4xl mx-auto space-y-8" x-data="{ 
    showModal: false, 
    modalTitle: '', 
    modalDescription: '', 
    modalAction: null,
    openModal(title, description, action) {
      this.modalTitle = title;
      this.modalDescription = description;
      this.modalAction = action;
      this.showModal = true;
    },
    confirmAction() {
      if (this.modalAction) {
        this.modalAction();
      }
      this.showModal = false;
    }
  }">
    <header class="space-y-2 text-center">
      <h1 class="text-3xl font-semibold">Менеджер Artisan команд</h1>
      <p class="text-muted-foreground">Виконуйте Artisan команди безпосередньо з адмін-панелі для керування кешем та оптимізації додатку.</p>
    </header>

    @if($feedback)
      <div @class([
        'rounded-2xl border p-4 shadow-soft',
        'border-success/40 bg-success/10 text-success' => $feedback['status'] === 'success',
        'border-destructive/40 bg-destructive/10 text-destructive-foreground' => $feedback['status'] === 'error',
      ])>
        <div class="font-medium">{{ $feedback['message'] }}</div>
        @if(! empty($feedback['output']))
          <pre class="mt-3 max-h-64 overflow-y-auto whitespace-pre-wrap rounded-xl border border-border/70 bg-background/70 p-3 text-xs leading-relaxed">{{ $feedback['output'] }}</pre>
        @endif
      </div>
    @endif

    <!-- Clear All Caches Button -->
    <section class="rounded-3xl border border-amber-200 bg-amber-50 shadow-soft">
      <div class="space-y-4 p-6">
        <div>
          <h2 class="text-2xl font-semibold text-amber-900">Очистити всі кеші</h2>
          <p class="text-sm text-amber-700">Виконує всі команди очищення кешу послідовно: cache:clear, config:clear, route:clear, view:clear.</p>
        </div>
        <form method="POST" action="{{ route('artisan.clear-all-caches') }}" x-ref="clearAllForm">
          @csrf
          <button 
            type="button"
            class="inline-flex items-center justify-center rounded-2xl bg-amber-600 px-5 py-2 text-sm font-semibold text-white shadow-soft hover:bg-amber-700"
            @click="openModal('Очистити всі кеші', 'Ви впевнені, що хочете очистити всі кеші? Це виконає команди cache:clear, config:clear, route:clear та view:clear.', () => $refs.clearAllForm.submit())"
          >
            <i class="fa-solid fa-broom-ball mr-2"></i>
            Очистити всі кеші
          </button>
        </form>
      </div>
    </section>

    <div class="grid gap-6 md:grid-cols-2">
      @foreach($commands as $commandKey => $commandData)
        <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
          <div class="space-y-4 p-6">
            <div>
              <h2 class="text-xl font-semibold">{{ $commandData['title'] }}</h2>
              <p class="text-sm text-muted-foreground">{!! $commandData['description'] !!}</p>
            </div>
            <form method="POST" action="{{ route('artisan.execute', $commandKey) }}" x-ref="form_{{ $commandKey }}">
              @csrf
              <button 
                type="button" 
                class="inline-flex items-center justify-center rounded-2xl {{ $commandData['button_class'] }} px-5 py-2 text-sm font-semibold text-primary-foreground shadow-soft hover:opacity-90"
                @click="openModal('{{ $commandData['title'] }}', 'Ви впевнені, що хочете виконати цю команду?', () => $refs.form_{{ $commandKey }}.submit())"
              >
                <i class="fa-solid {{ $commandData['icon'] }} mr-2"></i>
                Виконати
              </button>
            </form>
          </div>
        </section>
      @endforeach
    </div>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-4 p-6">
        <div>
          <h2 class="text-2xl font-semibold">Рекомендації</h2>
          <div class="mt-3 space-y-2 text-sm text-muted-foreground">
            <p>
              <strong>Під час розробки:</strong> використовуйте "Скасувати оптимізацію" та команди очищення кешів для негайного відображення змін у коді.
            </p>
            <p>
              <strong>У продакшені:</strong> використовуйте "Оптимізувати додаток" після розгортання нової версії для максимальної продуктивності.
            </p>
            <p>
              <strong>Увага:</strong> Ці команди впливають на всіх користувачів додатку. Використовуйте їх обережно у продакшені.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Confirmation Modal -->
    <div 
      x-show="showModal" 
      x-cloak
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      @click.self="showModal = false"
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
    >
      <div 
        class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden"
        @click.stop
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
      >
        <div class="p-6">
          <h3 class="text-xl font-semibold text-gray-900 mb-3" x-text="modalTitle"></h3>
          <p class="text-gray-600 mb-6" x-text="modalDescription"></p>
          <div class="flex gap-3 justify-end">
            <button 
              type="button"
              class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium"
              @click="showModal = false"
            >
              Скасувати
            </button>
            <button 
              type="button"
              class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-medium"
              @click="confirmAction()"
            >
              Підтвердити
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
