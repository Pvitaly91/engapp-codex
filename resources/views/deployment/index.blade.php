@extends('layouts.app')

@section('title', 'Оновлення сайту')

@section('content')
  <div class="max-w-4xl mx-auto space-y-8">
    <header class="space-y-4 text-center">
      @php
        $shellActive = request()->routeIs('deployment.index');
        $nativeActive = request()->routeIs('deployment.native.*');
      @endphp
      <div class="inline-flex items-center rounded-full border border-border/70 bg-muted/40 p-1 text-sm">
        <a
          href="{{ route('deployment.index') }}"
          class="rounded-full px-4 py-1.5 font-medium transition {{ $shellActive ? 'bg-primary text-primary-foreground shadow-sm ring-2 ring-primary/60 ring-offset-2 ring-offset-background' : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground' }}"
          @if($shellActive) aria-current="page" @endif
        >
          SSH режим
        </a>
        <a
          href="{{ route('deployment.native.index') }}"
          class="rounded-full px-4 py-1.5 font-medium transition {{ $nativeActive ? 'bg-primary text-primary-foreground shadow-sm ring-2 ring-primary/60 ring-offset-2 ring-offset-background' : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground' }}"
          @if($nativeActive) aria-current="page" @endif
        >
          API режим
        </a>
      </div>
      <div class="space-y-2">
        <h1 class="text-3xl font-semibold">Оновлення сайту з репозиторію</h1>
        <p class="text-muted-foreground">Ця сторінка виконує git-команди безпосередньо через SSH. Ви також можете скористатися альтернативною сторінкою, що працює через GitHub API.</p>
      </div>
    </header>

    <div class="flex justify-center">
      <div class="inline-flex items-center gap-3 rounded-full border border-border/60 bg-muted/40 px-5 py-2 text-sm font-medium">
        <span class="text-muted-foreground">Поточна активна гілка:</span>
        @if($currentBranch)
          <span class="font-semibold text-foreground">{{ $currentBranch }}</span>
        @else
          <span class="text-destructive-foreground">невідомо</span>
        @endif
      </div>
    </div>

    @if($feedback)
      @php
        $highlightSuccessfulUpdate = $feedback['status'] === 'success'
          && \Illuminate\Support\Str::contains($feedback['message'], 'Сайт успішно оновлено до останнього стану гілки.');
      @endphp
      @php
        $highlightShellUnavailable = $feedback['status'] === 'error'
          && \Illuminate\Support\Str::contains($feedback['message'], 'Режим через SSH недоступний на цьому сервері.');
      @endphp
      <div @class([
        'rounded-2xl border p-4 shadow-soft',
        'border-success/40 bg-success/10 text-success' => $feedback['status'] === 'success',
        'border-destructive/40 bg-destructive/10 text-destructive-foreground' => $feedback['status'] === 'error',
      ])>
        <div class="font-medium">
          @if($highlightSuccessfulUpdate)
            <span class="inline-flex items-center gap-2 rounded-xl bg-success/20 px-3 py-2 text-success">
              <span class="inline-flex h-2.5 w-2.5 rounded-full bg-success"></span>
              {{ $feedback['message'] }}
            </span>
          @elseif($highlightShellUnavailable)
            <span class="inline-flex items-center gap-2 rounded-xl bg-destructive/15 px-3 py-2 text-destructive-foreground">
              <span class="inline-flex h-2.5 w-2.5 rounded-full bg-destructive"></span>
              {{ $feedback['message'] }}
            </span>
          @else
            {{ $feedback['message'] }}
          @endif
        </div>
        @if(! empty($feedback['commands']))
          <ul class="mt-4 space-y-3 text-sm">
            @foreach($feedback['commands'] as $command)
              <li @class([
                'rounded-xl border p-3 text-left shadow-soft/20',
                'border-border/80 bg-background/80' => $command['successful'],
                'border-destructive/60 bg-destructive/10 text-destructive-foreground' => ! $command['successful'],
              ])>
                <div class="flex items-center justify-between gap-4">
                  <span @class([
                    'font-semibold',
                    'text-destructive-foreground' => ! $command['successful'],
                  ])>{{ $command['command'] }}</span>
                  <span @class([
                    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold',
                    'bg-success/15 text-success' => $command['successful'],
                    'bg-destructive/15 text-destructive-foreground' => ! $command['successful'],
                  ])>
                    {{ $command['successful'] ? 'OK' : 'Помилка' }}
                  </span>
                </div>
                <pre @class([
                  'mt-2 overflow-x-auto whitespace-pre-wrap text-xs leading-relaxed',
                  'text-destructive-foreground' => ! $command['successful'],
                ])>{{ $command['output'] }}</pre>
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
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-5 py-2 text-sm font-semibold text-white shadow-soft hover:bg-red-600/90">Оновити зараз</button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">2. Запушити поточний стан</h2>
          <p class="text-sm text-muted-foreground">Виконайте <code>git push</code>, щоб надіслати поточний коміт на потрібну віддалену гілку (за замовчуванням <code>master</code>).</p>
          <div class="mt-3 rounded-2xl border border-border/70 bg-muted/30 p-4 text-xs text-muted-foreground">
            <p class="font-semibold text-foreground">Команди, які буде виконано:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
              <li><code>git rev-parse --abbrev-ref HEAD</code> — визначає поточну гілку.</li>
              <li>
                <code>git push --force origin HEAD:&lt;обрана_гілка&gt;</code>
                — примусово оновлює віддалену гілку станом вашого локального HEAD.
              </li>
            </ul>
          </div>
        </div>
        <form method="POST" action="{{ route('deployment.push-current') }}" class="space-y-4">
          @csrf
          <label class="block text-sm font-medium" for="push-current-branch">Віддалена гілка</label>
          <input id="push-current-branch" type="text" name="branch" value="master" class="w-full rounded-2xl border border-input bg-background px-4 py-2" />
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-soft hover:bg-emerald-600/90">Запушити поточний коміт</button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">3. Створити резервну гілку</h2>
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
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-soft hover:bg-blue-600/90">Створити гілку</button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">4. Керування резервними гілками</h2>
          <p class="text-sm text-muted-foreground">Усі створені гілки доступні нижче. Звідси ж можна запушити їх на GitHub, щоб зберегти віддалену копію.</p>
        </div>
        @if($backupBranches->isEmpty())
          <p class="text-sm text-muted-foreground">Поки що немає створених резервних гілок. Створіть першу гілку у попередньому блоці.</p>
        @else
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border/70 text-sm">
              <thead class="bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                <tr>
                  <th class="px-4 py-3">Назва</th>
                  <th class="px-4 py-3">Коміт</th>
                  <th class="px-4 py-3">Створено</th>
                  <th class="px-4 py-3">Статус</th>
                  <th class="px-4 py-3 text-right">Дії</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border/60 bg-background/60">
                @foreach($backupBranches as $branch)
                  <tr>
                    <td class="px-4 py-3 font-medium">{{ $branch->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $branch->commit_hash }}</td>
                    <td class="px-4 py-3">{{ $branch->created_at->format('d.m.Y H:i') }}</td>
                    <td class="px-4 py-3">
                      @if($branch->pushed_at)
                        <span class="inline-flex items-center gap-1 rounded-full bg-success/15 px-3 py-1 text-xs font-semibold text-success">
                          <i class="fa-solid fa-check"></i> Запушено {{ $branch->pushed_at->format('d.m.Y H:i') }}
                        </span>
                      @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-warning/15 px-3 py-1 text-xs font-semibold text-amber-700">
                          <i class="fa-solid fa-cloud-arrow-up"></i> Лише локально
                        </span>
                      @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                      @if(! $branch->pushed_at)
                        <form method="POST" action="{{ route('deployment.backup-branch.push', $branch) }}" class="inline">
                          @csrf
                          <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            Запушити
                          </button>
                        </form>
                      @else
                        <span class="text-xs text-muted-foreground">Віддалена копія актуальна</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">5. Відкотити зміни</h2>
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
