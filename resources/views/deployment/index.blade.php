@extends('layouts.app')

@section('title', 'Оновлення сайту')

@section('content')
  <div class="max-w-4xl mx-auto space-y-8">
    <header class="space-y-2 text-center">
      <h1 class="text-3xl font-semibold">Оновлення сайту з репозиторію</h1>
      <p class="text-muted-foreground">Ця сторінка дозволяє підтягнути останні зміни з GitHub та за потреби повернутися до попереднього робочого стану.</p>
    </header>

    @if($feedback)
      <div @class([
        'rounded-2xl border p-4 shadow-soft',
        'border-success/40 bg-success/10 text-success' => $feedback['status'] === 'success',
        'border-destructive/40 bg-destructive/10 text-destructive-foreground' => $feedback['status'] === 'error',
      ])>
        <div class="font-medium">{{ $feedback['message'] }}</div>
        @if(! empty($feedback['commands']))
          <ul class="mt-4 space-y-3 text-sm">
            @foreach($feedback['commands'] as $command)
              <li class="rounded-xl border border-border/80 bg-background/80 p-3 text-left">
                <div class="flex items-center justify-between gap-4">
                  <span class="font-semibold">{{ $command['command'] }}</span>
                  <span @class([
                    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold',
                    'bg-success/15 text-success' => $command['successful'],
                    'bg-destructive/15 text-destructive-foreground' => ! $command['successful'],
                  ])>
                    {{ $command['successful'] ? 'OK' : 'Помилка' }}
                  </span>
                </div>
                <pre class="mt-2 overflow-x-auto whitespace-pre-wrap text-xs leading-relaxed">{{ $command['output'] }}</pre>
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    @endif

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">1. Оновити з Git</h2>
          <p class="text-sm text-muted-foreground">Натисніть кнопку нижче, щоб виконати послідовність команд: <code>git fetch origin</code> та <code>git reset --hard origin/&lt;гілка&gt;</code>. Перед скиданням автоматично збережеться резервний коміт.</p>
        </div>
        <form method="POST" action="{{ route('deployment.deploy') }}" class="space-y-4">
          @csrf
          <label class="block text-sm font-medium">Гілка для оновлення</label>
          <input type="text" name="branch" value="main" class="w-full rounded-2xl border border-input bg-background px-4 py-2" />
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">Оновити зараз</button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">2. Створити резервну гілку</h2>
          <p class="text-sm text-muted-foreground">За потреби можна зробити окрему гілку з поточного стану або одного з резервних комітів, щоб зберегти стабільну версію перед великими оновленнями.</p>
        </div>
        <form method="POST" action="{{ route('deployment.backup-branch') }}" class="space-y-4">
          @csrf
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <label class="block text-sm font-medium" for="backup-branch-name">Назва резервної гілки</label>
              <input id="backup-branch-name" type="text" name="branch_name" placeholder="backup/{{ now()->format('Y-m-d') }}" class="w-full rounded-2xl border border-input bg-background px-4 py-2" required />
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-medium" for="backup-branch-commit">Коміт для копії</label>
              <select id="backup-branch-commit" name="commit" class="w-full rounded-2xl border border-input bg-background px-4 py-2">
                <option value="current">Поточний HEAD (визначити автоматично)</option>
                @foreach($backups as $backup)
                  <option value="{{ $backup['commit'] }}">
                    {{ \Illuminate\Support\Carbon::parse($backup['timestamp'])->format('d.m.Y H:i') }} — {{ $backup['commit'] }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
          <p class="text-xs text-muted-foreground">Гілка створюється лише локально. За потреби виконайте <code>git push origin &lt;назва-гілки&gt;</code>, щоб зберегти її на GitHub.</p>
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-soft hover:bg-blue-600/90">Створити гілку</button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">3. Відкотити зміни</h2>
          <p class="text-sm text-muted-foreground">Якщо після оновлення з’явилися проблеми, можна повернути сайт до збереженого стану. Виберіть потрібний коміт зі списку нижче.</p>
        </div>
        @if(count($backups) === 0)
          <p class="text-sm text-muted-foreground">Резервних копій ще немає. Після першого оновлення вони з’являться автоматично.</p>
        @else
          <form method="POST" action="{{ route('deployment.rollback') }}" class="space-y-4">
            @csrf
            <label class="block text-sm font-medium" for="rollback-commit">Оберіть резервний коміт</label>
            <select id="rollback-commit" name="commit" class="w-full rounded-2xl border border-input bg-background px-4 py-2">
              @foreach($backups as $backup)
                <option value="{{ $backup['commit'] }}">
                  {{ \Illuminate\Support\Carbon::parse($backup['timestamp'])->format('d.m.Y H:i') }} — {{ $backup['commit'] }}
                </option>
              @endforeach
            </select>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-warning px-5 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-warning/90">Виконати відкат</button>
          </form>
        @endif
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-4 p-6">
        <h2 class="text-2xl font-semibold">Як працює автоматизація</h2>
        <ol class="list-decimal space-y-2 pl-5 text-sm text-muted-foreground">
          <li>Зберігаємо поточний коміт у файлі <code>storage/app/deployment_backups.json</code>.</li>
          <li>Виконуємо <code>git fetch origin</code>, щоб підтягнути останні зміни з віддаленого репозиторію.</li>
          <li>Перемикаємо робочу копію на обрану гілку командою <code>git reset --hard origin/&lt;гілка&gt;</code>, що ігнорує конфлікти.</li>
          <li>Для повернення стану використовуємо <code>git reset --hard &lt;коміт&gt;</code> з історії резервних копій.</li>
        </ol>
        <p class="text-sm text-muted-foreground">Усі команди виконуються від кореня застосунку, тому структура проєкту та залежності залишаються в актуальному стані.</p>
      </div>
    </section>
  </div>
@endsection
