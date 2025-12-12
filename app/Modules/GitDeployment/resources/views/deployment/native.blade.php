@extends('layouts.app')

@section('title', 'Оновлення сайту через API')

@section('content')
  <div class="max-w-4xl mx-auto space-y-8">
    <header class="space-y-4 text-center">
      @php
        $shellActive = request()->routeIs('deployment.index');
        $nativeActive = request()->routeIs('deployment.native.*');
      @endphp
      <div class="inline-flex items-center rounded-full border border-border/70 bg-muted/40 p-1 text-sm">
        @if($supportsShell)
          <a
            href="{{ route('deployment.index') }}"
            class="rounded-full px-4 py-1.5 font-medium transition {{ $shellActive ? 'bg-primary text-primary-foreground shadow-sm ring-2 ring-primary/60 ring-offset-2 ring-offset-background' : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground' }}"
            @if($shellActive) aria-current="page" @endif
          >
            SSH режим
          </a>
        @endif
        <a
          href="{{ route('deployment.native.index') }}"
          class="rounded-full px-4 py-1.5 font-medium transition {{ $nativeActive ? 'bg-primary text-primary-foreground shadow-sm ring-2 ring-primary/60 ring-offset-2 ring-offset-background' : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground' }}"
          @if($nativeActive) aria-current="page" @endif
        >
          API режим
        </a>
      </div>
      @unless($supportsShell)
        <p class="text-sm font-medium text-destructive-foreground">
          SSH режим недоступний на цьому сервері, тому доступна лише робота через API.
        </p>
      @endunless
      <div class="space-y-2">
        <h1 class="text-3xl font-semibold">Оновлення сайту через GitHub API</h1>
        <p class="text-muted-foreground">Усі операції виконуються через GitHub API та файлову систему Laravel без виклику <code>proc_open</code>, <code>exec</code> чи подібних функцій.</p>
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
          && \Illuminate\Support\Str::startsWith($feedback['message'], 'Сайт успішно оновлено до останнього стану гілки через GitHub API.');
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
        @if(! empty($feedback['logs']))
          <ul @class([
            'mt-4 space-y-3 text-sm text-foreground/80',
            'text-destructive-foreground' => $feedback['status'] === 'error',
          ])>
            @foreach($feedback['logs'] as $log)
              <li @class([
                'rounded-xl border p-3 text-left shadow-soft/20',
                'border-border/80 bg-background/80' => $feedback['status'] !== 'error',
                'border-destructive/60 bg-destructive/10 text-destructive-foreground' => $feedback['status'] === 'error',
              ])>
                {{ $log }}
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    @endif

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">1. Оновити з GitHub API</h2>
          <p class="text-sm text-muted-foreground">Завантажує архів гілки з GitHub API, оновлює файли напряму та видаляє локальні елементи, яких немає в репозиторії — усе без виконання SSH-команд.</p>
        </div>
        <form method="POST" action="{{ route('deployment.native.deploy') }}" class="space-y-4">
          @csrf
          <label class="block text-sm font-medium">Гілка для оновлення</label>
          <div class="relative">
            <input type="text" name="branch" value="{{ $feedback['branch'] ?? 'main' }}" class="w-full rounded-2xl border border-input bg-background px-4 py-2 pr-10" />
            <button type="button" onclick="this.previousElementSibling.value=''; this.previousElementSibling.focus();" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition" title="Очистити поле">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          
          <div class="space-y-2">
            <label class="block text-sm font-medium" for="native-auto-push-branch">Автоматично запушити стан після оновлення до гілки (опціонально)</label>
            <select 
              id="native-auto-push-branch" 
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
          <h2 class="text-2xl font-semibold">1.5. Частковий деплой через API</h2>
          <p class="text-sm text-muted-foreground">Оновіть лише вказані папки/файли з обраної гілки через GitHub API, не чіпаючи решту робочого дерева. Ідеально для оновлення окремих модулів чи сідерів без повного деплою.</p>
        </div>
        <form method="POST" action="{{ route('deployment.native.deploy-partial') }}" class="space-y-4">
          @csrf
          <div class="space-y-2">
            <label class="block text-sm font-medium">Гілка для оновлення</label>
            <div class="relative">
              <input type="text" name="branch" value="{{ $feedback['branch'] ?? 'main' }}" class="w-full rounded-2xl border border-input bg-background px-4 py-2 pr-10" />
              <button type="button" onclick="this.previousElementSibling.value=''; this.previousElementSibling.focus();" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition" title="Очистити поле">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
          
          <div class="space-y-2">
            <label class="block text-sm font-medium" for="native-partial-deploy-paths">Шляхи для оновлення (кожен з нового рядка)</label>
            <textarea 
              id="native-partial-deploy-paths" 
              name="paths" 
              rows="4"
              class="w-full rounded-2xl border border-input bg-background px-4 py-3 text-sm font-mono"
              placeholder="database/seeders&#10;app/Modules/Quiz&#10;resources/views/components"
            ></textarea>
            <p class="text-xs text-muted-foreground">Вкажіть шляхи до папок або файлів. Можна розділяти новими рядками, комами або крапкою з комою. Захищені директорії (.git, .env, storage, vendor, node_modules) не можна оновлювати.</p>
            
            @if(!empty($availableFolders))
              <div class="mt-3">
                <button type="button" class="text-sm text-primary hover:text-primary/80 font-medium" onclick="document.getElementById('folder-picker-api').classList.toggle('hidden')">
                  <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                  </svg>
                  Вибрати з існуючих папок
                </button>
                <div id="folder-picker-api" class="hidden mt-2 p-3 rounded-xl border border-border/70 bg-muted/30 max-h-48 overflow-y-auto">
                  <div class="flex flex-wrap gap-2">
                    @foreach($availableFolders as $folder)
                      <button 
                        type="button" 
                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium bg-background border border-border/70 hover:bg-primary/10 hover:border-primary/50 transition"
                        onclick="addFolderToTextarea('native-partial-deploy-paths', '{{ $folder }}')"
                      >
                        <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        {{ $folder }}
                      </button>
                    @endforeach
                  </div>
                </div>
              </div>
            @endif
          </div>
          
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-orange-600 px-5 py-2 text-sm font-semibold text-white shadow-soft hover:bg-orange-600/90">Виконати частковий деплой через API</button>
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
                          'partial_deploy' => 'Частковий деплой',
                          'push' => 'Пуш',
                          'auto_push' => 'Автоматичний пуш',
                          'create_and_push' => 'Створення та пуш',
                          'backup' => 'Резервна копія',
                        ];
                        $actionColors = [
                          'deploy' => 'bg-red-100 text-red-700',
                          'partial_deploy' => 'bg-orange-100 text-orange-700',
                          'push' => 'bg-emerald-100 text-emerald-700',
                          'auto_push' => 'bg-purple-100 text-purple-700',
                          'create_and_push' => 'bg-blue-100 text-blue-700',
                          'backup' => 'bg-amber-100 text-amber-700',
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
          <p class="text-sm text-muted-foreground">Введіть назву гілки. Якщо її не існує, вона буде створена автоматично. Поточний стан сайту буде запушено на віддалений репозиторій через GitHub API.</p>
        </div>
        <form method="POST" action="{{ route('deployment.native.quick-branch') }}" class="space-y-4">
          @csrf
          <label class="block text-sm font-medium" for="native-quick-branch-name">Назва гілки</label>
          <div class="relative">
            <input id="native-quick-branch-name" type="text" name="quick_branch_name" placeholder="feature/my-feature" class="w-full rounded-2xl border border-input bg-background px-4 py-2 pr-10" required />
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
          <p class="text-sm text-muted-foreground">Формує новий коміт за допомогою GitHub API та оновлює вказану гілку без SSH-команд.</p>
        </div>
        <form method="POST" action="{{ route('deployment.native.push-current') }}" class="space-y-4">
          @csrf
          <label class="block text-sm font-medium" for="native-push-current-branch">Віддалена гілка</label>
          <div class="relative">
            <input id="native-push-current-branch" type="text" name="branch" value="{{ $feedback['branch'] ?? 'main' }}" class="w-full rounded-2xl border border-input bg-background px-4 py-2 pr-10" />
            <button type="button" onclick="this.previousElementSibling.value=''; this.previousElementSibling.focus();" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition" title="Очистити поле">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-soft hover:bg-emerald-600/90">Запушити через API</button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">4. Створити резервну гілку</h2>
          <p class="text-sm text-muted-foreground">Створює локальний ref у <code>.git/refs/heads</code> без запуску git-команд.</p>
        </div>
        <div class="rounded-2xl border border-border/70 bg-muted/40 p-4 text-sm text-muted-foreground">
          <p class="font-medium text-foreground">Еквівалент наступних команд git:</p>
          <ul class="mt-3 list-disc space-y-2 pl-5">
            <li>
              <code>git rev-parse HEAD</code>
              — визначає поточний коміт, якщо обрано «Поточний HEAD».
            </li>
            <li>
              <code>git update-ref refs/heads/&lt;назва_гілки&gt; &lt;коміт&gt;</code>
              — записує нове посилання гілки на вибраний коміт.
            </li>
          </ul>
          <p class="mt-3">Усі дії виконуються через GitHub API та прямий запис refs без запуску shell-команд.</p>
        </div>
        <form method="POST" action="{{ route('deployment.native.backup-branch') }}" class="space-y-4">
          @csrf
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <label class="block text-sm font-medium" for="native-backup-branch-name">Назва резервної гілки</label>
              <div class="relative">
                <input id="native-backup-branch-name" type="text" name="branch_name" placeholder="backup/{{ now()->format('Y-m-d') }}" class="w-full rounded-2xl border border-input bg-background px-4 py-2 pr-10" required />
                <button type="button" onclick="this.previousElementSibling.value=''; this.previousElementSibling.focus();" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition" title="Очистити поле">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-medium" for="native-backup-branch-commit">Коміт для копії</label>
              <select id="native-backup-branch-commit" name="commit" class="w-full rounded-2xl border border-input bg-background px-4 py-2">
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
            <p class="text-sm text-muted-foreground">Публікуйте створені гілки на GitHub через REST API без SSH-команд.</p>
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
                          <form method="POST" action="{{ route('deployment.native.backup-branch.push', $branch) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
                              <i class="fa-solid fa-cloud-arrow-up"></i>
                              Запушити через API
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
          <p class="text-sm text-muted-foreground">Скачайте архів вибраного коміту та відновіть файли без SSH-команд.</p>
        </div>
        @if(count($backups) === 0)
          <p class="text-sm text-muted-foreground">Резервних копій ще немає. Після першого оновлення вони з’являться автоматично.</p>
        @else
          <form method="POST" action="{{ route('deployment.native.rollback') }}" class="space-y-4">
            @csrf
            <label class="block text-sm font-medium" for="native-rollback-commit">Оберіть резервний коміт</label>
            <select id="native-rollback-commit" name="commit" class="w-full rounded-2xl border border-input bg-background px-4 py-2">
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
        <h2 class="text-2xl font-semibold">Як працює API-оновлення</h2>
        <ol class="list-decimal space-y-2 pl-5 text-sm text-muted-foreground">
          <li>Поточний коміт записується у <code>storage/app/deployment_backups.json</code>.</li>
          <li>Архів потрібної гілки або коміту завантажується через GitHub REST API.</li>
          <li>Файли з архіву розпаковуються й замінюють робоче дерево засобами Laravel Filesystem.</li>
          <li>Для пушу формується новий коміт через GitHub API, а гілка оновлюється запитом <code>git/refs</code>.</li>
        </ol>
        <p class="text-sm text-muted-foreground">Жодні PHP-функції типу <code>proc_open</code>, <code>exec</code>, <code>shell_exec</code>, <code>system</code> або <code>passthru</code> не використовуються.</p>
      </div>
    </section>
  </div>

  @include('git-deployment::deployment.partials.backup-branch-copy-script')
  @include('git-deployment::deployment.partials.branch-history-toggle-script')
  @include('git-deployment::deployment.partials.backup-branches-toggle-script')
  @include('git-deployment::deployment.partials.searchable-select-script')
  @include('git-deployment::deployment.partials.folder-picker-script')
@endsection
