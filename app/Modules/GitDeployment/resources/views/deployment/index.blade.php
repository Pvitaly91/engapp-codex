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
          && \Illuminate\Support\Str::startsWith($feedback['message'], 'Сайт успішно оновлено до останнього стану гілки.');
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
          <p class="text-sm text-muted-foreground">Натисніть кнопку нижче, щоб виконати послідовність команд: <code>git fetch origin</code>, <code>git reset --hard origin/&lt;гілка&gt;</code> та <code>git clean -fd</code>. Перед скиданням автоматично збережеться резервний коміт, а після — будуть видалені локальні файли, яких немає в репозиторії.</p>
        </div>
        <form method="POST" action="{{ route('deployment.deploy') }}" class="space-y-4">
          @csrf
          <label class="block text-sm font-medium">Гілка для оновлення</label>
          <div class="relative">
            <input type="text" name="branch" value="main" class="w-full rounded-2xl border border-input bg-background px-4 py-2 pr-10" />
            <button type="button" onclick="this.previousElementSibling.value=''; this.previousElementSibling.focus();" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition" title="Очистити поле">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          
          <div class="space-y-2">
            <label class="block text-sm font-medium" for="auto-push-branch">Автоматично запушити стан після оновлення до гілки (опціонально)</label>
            <select 
              id="auto-push-branch" 
              name="auto_push_branch"
              class="w-full rounded-2xl border border-input bg-background px-4 py-2"
            >
              <option value="">-- Оберіть гілку або введіть нову --</option>
              @foreach($backupBranches as $branch)
                <option value="{{ $branch->name }}">{{ $branch->name }}</option>
              @endforeach
            </select>
            <p class="text-xs text-muted-foreground">Якщо вказати гілку, після оновлення поточний стан буде автоматично запушено на цю гілку (буде створена, якщо не існує).</p>
          </div>
          
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-5 py-2 text-sm font-semibold text-white shadow-soft hover:bg-red-600/90">Оновити зараз</button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">1.5. Частковий деплой</h2>
          <p class="text-sm text-muted-foreground">Оновлює тільки вибрані шляхи з вказаної гілки без скидання всього дерева. Шляхи задавайте з нового рядка або через кому/крапку з комою.</p>
        </div>
        <form method="POST" action="{{ route('deployment.deploy-partial') }}" class="space-y-4">
          @csrf
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <label class="block text-sm font-medium" for="partial-branch">Гілка</label>
              <input id="partial-branch" type="text" name="branch" value="{{ $feedback['branch'] ?? $currentBranch ?? 'main' }}" class="w-full rounded-2xl border border-input bg-background px-4 py-2" />
            </div>
            <div class="space-y-2 md:col-span-2">
              <label class="block text-sm font-medium" for="partial-existing-paths">Обрати існуючий шлях</label>
              <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <select
                  id="partial-existing-paths"
                  class="w-full rounded-2xl border border-input bg-background px-4 py-2"
                  data-path-select
                  data-target-textarea="partial-paths"
                >
                  <option value="">-- Оберіть шлях із структури сайту --</option>
                  @foreach($existingPaths as $path)
                    <option value="{{ $path }}">{{ $path }}</option>
                  @endforeach
                </select>
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-2xl bg-muted px-4 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-muted/80"
                  data-path-picker
                  data-path-select="partial-existing-paths"
                  data-target-textarea="partial-paths"
                >
                  Додати до списку
                </button>
              </div>
              <p class="text-xs text-muted-foreground">Подвійне натискання на списку також додає шлях у поле нижче.</p>
            </div>
            <div class="space-y-2 md:col-span-2">
              <label class="block text-sm font-medium" for="partial-paths">Шляхи для оновлення</label>
              <textarea id="partial-paths" name="paths" rows="4" class="w-full rounded-2xl border border-input bg-background px-4 py-2" placeholder="app/Modules/Quiz&#10;database/seeders"></textarea>
              <p class="text-xs text-muted-foreground">Кожен шлях з нового рядка або через кому/крапку з комою. Заборонено: .git, .env, storage, vendor, node_modules та відносні виходи на рівень вище.</p>
            </div>
          </div>

          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-soft hover:bg-indigo-600/90">Запустити частковий деплой</button>
        </form>
      </div>
    </section>

    @if($recentUsage->isNotEmpty())
      <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
        <div class="space-y-4 p-6">
          <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold">Історія використання гілок</h2>
            <button 
              type="button" 
              id="toggle-branch-history"
              class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition hover:bg-muted/50 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              aria-expanded="true"
              aria-controls="branch-history-content"
            >
              <span id="toggle-branch-history-text">Згорнути</span>
              <svg id="toggle-branch-history-icon" class="h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
          </div>
          <div id="branch-history-content" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border/70 text-sm">
              <thead class="bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                <tr>
                  <th class="px-4 py-3">Гілка</th>
                  <th class="px-4 py-3">Дія</th>
                  <th class="px-4 py-3">Опис</th>
                  <th class="px-4 py-3">Час використання</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border/60 bg-background/60">
                @foreach($recentUsage as $usage)
                  <tr>
                    <td class="px-4 py-3 font-medium">
                      <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg px-2 py-1 text-left font-medium text-foreground transition hover:bg-muted/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                        data-copy-branch="{{ $usage->branch_name }}"
                      >
                        <span data-copy-branch-text>{{ $usage->branch_name }}</span>
                        <span class="hidden text-xs font-semibold text-success" data-copy-branch-success>Скопійовано!</span>
                        <span class="hidden text-xs font-semibold text-destructive-foreground" data-copy-branch-error>Не вдалося скопіювати</span>
                      </button>
                    </td>
                    <td class="px-4 py-3">
                      @php
                        $actionLabels = [
                          'deploy' => 'Оновлення',
                          'push' => 'Пуш',
                          'auto_push' => 'Автоматичний пуш',
                          'create_and_push' => 'Створення та пуш',
                          'backup' => 'Резервна копія',
                          'partial_deploy' => 'Частковий деплой',
                        ];
                        $actionColors = [
                          'deploy' => 'bg-red-100 text-red-700',
                          'push' => 'bg-emerald-100 text-emerald-700',
                          'auto_push' => 'bg-purple-100 text-purple-700',
                          'create_and_push' => 'bg-blue-100 text-blue-700',
                          'backup' => 'bg-amber-100 text-amber-700',
                          'partial_deploy' => 'bg-indigo-100 text-indigo-700',
                        ];
                      @endphp
                      <span @class(['inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold', $actionColors[$usage->action] ?? 'bg-muted text-foreground'])>
                        {{ $actionLabels[$usage->action] ?? $usage->action }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-muted-foreground">{{ $usage->description ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs">{{ $usage->used_at ? $usage->used_at->format('d.m.Y H:i:s') : '—' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </section>
    @endif

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">2. Швидке створення та пуш гілки</h2>
          <p class="text-sm text-muted-foreground">Введіть назву гілки. Якщо її не існує, вона буде створена автоматично. Поточний стан сайту буде запушено на віддалений репозиторій.</p>
        </div>
        <form method="POST" action="{{ route('deployment.quick-branch') }}" class="space-y-4">
          @csrf
          <label class="block text-sm font-medium" for="quick-branch-name">Назва гілки</label>
          <div class="relative">
            <input id="quick-branch-name" type="text" name="quick_branch_name" placeholder="feature/my-feature" class="w-full rounded-2xl border border-input bg-background px-4 py-2 pr-10" required />
            <button type="button" onclick="this.previousElementSibling.value=''; this.previousElementSibling.focus();" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition" title="Очистити поле">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-soft hover:bg-blue-600/90">Створити та запушити</button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">3. Запушити поточний стан</h2>
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
          <div class="relative">
            <input id="push-current-branch" type="text" name="branch" value="master" class="w-full rounded-2xl border border-input bg-background px-4 py-2 pr-10" />
            <button type="button" onclick="this.previousElementSibling.value=''; this.previousElementSibling.focus();" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition" title="Очистити поле">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-soft hover:bg-emerald-600/90">Запушити поточний коміт</button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">4. Створити резервну гілку</h2>
          <p class="text-sm text-muted-foreground">За потреби можна зробити окрему гілку з поточного стану або одного з резервних комітів, щоб зберегти стабільну версію перед великими оновленнями.</p>
        </div>
        <div class="rounded-2xl border border-border/70 bg-muted/40 p-4 text-sm text-muted-foreground">
          <p class="font-medium text-foreground">Під час створення виконується еквівалент таких команд git:</p>
          <ul class="mt-3 list-disc space-y-2 pl-5">
            <li>
              <code>git rev-parse HEAD</code>
              — визначає хеш поточного коміту, якщо обрано «Поточний HEAD».
            </li>
            <li>
              <code>git update-ref refs/heads/&lt;назва_гілки&gt; &lt;коміт&gt;</code>
              — записує ref нової гілки на зазначений коміт.
            </li>
          </ul>
          <p class="mt-3">Операції виконуються через GitHub API та прямий запис refs без запуску shell-команд.</p>
        </div>
        <form method="POST" action="{{ route('deployment.backup-branch') }}" class="space-y-4">
          @csrf
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <label class="block text-sm font-medium" for="backup-branch-name">Назва резервної гілки</label>
              <div class="relative">
                <input id="backup-branch-name" type="text" name="branch_name" placeholder="backup/{{ now()->format('Y-m-d') }}" class="w-full rounded-2xl border border-input bg-background px-4 py-2 pr-10" required />
                <button type="button" onclick="this.previousElementSibling.value=''; this.previousElementSibling.focus();" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition" title="Очистити поле">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
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
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-semibold">5. Керування резервними гілками</h2>
            <p class="text-sm text-muted-foreground">Усі створені гілки доступні нижче. Звідси ж можна запушити їх на GitHub, щоб зберегти віддалену копію.</p>
          </div>
          @if($backupBranches->isNotEmpty())
            <button 
              type="button" 
              id="toggle-backup-branches"
              class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition hover:bg-muted/50 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              aria-expanded="true"
              aria-controls="backup-branches-content"
            >
              <span id="toggle-backup-branches-text">Згорнути</span>
              <svg id="toggle-backup-branches-icon" class="h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
          @endif
        </div>
        @if($backupBranches->isEmpty())
          <p class="text-sm text-muted-foreground">Поки що немає створених резервних гілок. Створіть першу гілку у попередньому блоці.</p>
        @else
          <div id="backup-branches-content">
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
                      <td class="px-4 py-3 font-medium">
                        <button
                          type="button"
                          class="inline-flex items-center gap-2 rounded-lg px-2 py-1 text-left font-medium text-foreground transition hover:bg-muted/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                          data-copy-branch="{{ $branch->name }}"
                        >
                          <span data-copy-branch-text>{{ $branch->name }}</span>
                          <span class="hidden text-xs font-semibold text-success" data-copy-branch-success>Скопійовано!</span>
                          <span class="hidden text-xs font-semibold text-destructive-foreground" data-copy-branch-error>Не вдалося скопіювати</span>
                        </button>
                      </td>
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
            @if($backupBranches->hasPages())
              <div class="mt-4 flex justify-center">
                {{ $backupBranches->links() }}
              </div>
            @endif
          </div>
        @endif
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">6. Відкотити зміни</h2>
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

  @include('git-deployment::deployment.partials.backup-branch-copy-script')
  @include('git-deployment::deployment.partials.branch-history-toggle-script')
  @include('git-deployment::deployment.partials.backup-branches-toggle-script')
  @include('git-deployment::deployment.partials.searchable-select-script')
  @include('git-deployment::deployment.partials.path-picker-script')
@endsection
