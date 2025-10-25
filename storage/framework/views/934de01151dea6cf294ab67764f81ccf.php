

<?php $__env->startSection('title', 'Оновлення сайту через GitHub API'); ?>

<?php $__env->startSection('content'); ?>
  <div class="max-w-4xl mx-auto space-y-8">
    <?php echo $__env->make('deployment.partials.switcher', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <header class="space-y-2 text-center">
      <h1 class="text-3xl font-semibold">Git-операції без shell</h1>
      <p class="text-muted-foreground">Ця версія інтерфейсу використовує виключно GitHub API та роботу з файлами PHP, жодних викликів shell-функцій.</p>
      <?php if($repositoryInfo): ?>
        <p class="text-sm text-muted-foreground">Підключений репозиторій: <span class="font-semibold"><?php echo e($repositoryInfo['owner']); ?>/<?php echo e($repositoryInfo['repo']); ?></span></p>
      <?php else: ?>
        <p class="text-sm text-destructive-foreground">Не вдалося визначити посилання на origin GitHub. Перевірте файл <code>.git/config</code>.</p>
      <?php endif; ?>
      <p class="text-xs text-muted-foreground">Для змін, що потребують запису в GitHub (push / створення гілок), додайте токен у <code>GITHUB_TOKEN</code>.</p>
    </header>

    <div class="flex justify-center">
      <div class="inline-flex items-center gap-3 rounded-full border border-border/60 bg-muted/40 px-5 py-2 text-sm font-medium">
        <span class="text-muted-foreground">Поточна активна гілка:</span>
        <?php if($currentBranch): ?>
          <span class="font-semibold text-foreground"><?php echo e($currentBranch); ?></span>
        <?php else: ?>
          <span class="text-destructive-foreground">невідомо</span>
        <?php endif; ?>
      </div>
    </div>

    <?php if($feedback): ?>
      <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'rounded-2xl border p-4 shadow-soft',
        'border-success/40 bg-success/10 text-success' => $feedback['status'] === 'success',
        'border-destructive/40 bg-destructive/10 text-destructive-foreground' => $feedback['status'] === 'error',
      ]); ?>">
        <div class="font-medium"><?php echo e($feedback['message']); ?></div>
        <?php if(! empty($feedback['commands'])): ?>
          <ul class="mt-4 space-y-3 text-sm">
            <?php $__currentLoopData = $feedback['commands']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $command): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <li class="rounded-xl border border-border/80 bg-background/80 p-3 text-left">
                <div class="flex items-center justify-between gap-4">
                  <span class="font-semibold"><?php echo e($command['command']); ?></span>
                  <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold',
                    'bg-success/15 text-success' => $command['successful'],
                    'bg-destructive/15 text-destructive-foreground' => ! $command['successful'],
                  ]); ?>">
                    <?php echo e($command['successful'] ? 'OK' : 'Помилка'); ?>

                  </span>
                </div>
                <pre class="mt-2 overflow-x-auto whitespace-pre-wrap text-xs leading-relaxed"><?php echo e($command['output'] ?? 'Без виводу'); ?></pre>
              </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </ul>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">1. Синхронізувати з GitHub</h2>
          <p class="text-sm text-muted-foreground">Завантажує zip-архів гілки з GitHub, розпаковує його та оновлює файлову систему проєкту без команд shell.</p>
        </div>
        <form method="POST" action="<?php echo e(route('deployment.github.deploy')); ?>" class="space-y-4">
          <?php echo csrf_field(); ?>
          <label class="block text-sm font-medium">Гілка для оновлення</label>
          <input type="text" name="branch" value="main" class="w-full rounded-2xl border border-input bg-background px-4 py-2" />
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">Оновити зараз</button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">2. Записати поточний стан на GitHub</h2>
          <p class="text-sm text-muted-foreground">Створює новий коміт і оновлює вказану гілку через REST API GitHub. Потрібен токен з правами <code>repo</code>.</p>
        </div>
        <form method="POST" action="<?php echo e(route('deployment.github.push-current')); ?>" class="space-y-4">
          <?php echo csrf_field(); ?>
          <label class="block text-sm font-medium" for="push-current-branch-api">Віддалена гілка</label>
          <input id="push-current-branch-api" type="text" name="branch" value="master" class="w-full rounded-2xl border border-input bg-background px-4 py-2" />
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-soft hover:bg-emerald-600/90">Запушити через API</button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">3. Створити резервну гілку на GitHub</h2>
          <p class="text-sm text-muted-foreground">Оформлює нову гілку у віддаленому репозиторії, використовуючи поточний коміт або обраний з резервної історії.</p>
        </div>
        <form method="POST" action="<?php echo e(route('deployment.github.backup-branch')); ?>" class="space-y-4">
          <?php echo csrf_field(); ?>
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <label class="block text-sm font-medium" for="backup-branch-name-api">Назва резервної гілки</label>
              <input id="backup-branch-name-api" type="text" name="branch_name" placeholder="backup/<?php echo e(now()->format('Y-m-d')); ?>" class="w-full rounded-2xl border border-input bg-background px-4 py-2" required />
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-medium" for="backup-branch-commit-api">Коміт для копії</label>
              <select id="backup-branch-commit-api" name="commit" class="w-full rounded-2xl border border-input bg-background px-4 py-2">
                <option value="current">Поточний HEAD (визначити автоматично)</option>
                <?php $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($backup['commit']); ?>">
                    <?php echo e(\Illuminate\Support\Carbon::parse($backup['timestamp'])->format('d.m.Y H:i')); ?> — <?php echo e($backup['commit']); ?>

                  </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
          <p class="text-sm text-muted-foreground">Гілки створюються безпосередньо у GitHub, але їх статус відображається у базі. За потреби можна повторно оновити віддалену гілку.</p>
        </div>
        <?php if($backupBranches->isEmpty()): ?>
          <p class="text-sm text-muted-foreground">Поки що немає створених резервних гілок.</p>
        <?php else: ?>
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
                <?php $__currentLoopData = $backupBranches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                    <td class="px-4 py-3 font-medium"><?php echo e($branch->name); ?></td>
                    <td class="px-4 py-3 font-mono text-xs"><?php echo e($branch->commit_hash); ?></td>
                    <td class="px-4 py-3"><?php echo e($branch->created_at->format('d.m.Y H:i')); ?></td>
                    <td class="px-4 py-3">
                      <?php if($branch->pushed_at): ?>
                        <span class="inline-flex items-center gap-1 rounded-full bg-success/15 px-3 py-1 text-xs font-semibold text-success">
                          <i class="fa-solid fa-check"></i> Оновлено <?php echo e($branch->pushed_at->format('d.m.Y H:i')); ?>

                        </span>
                      <?php else: ?>
                        <span class="inline-flex items-center gap-1 rounded-full bg-warning/15 px-3 py-1 text-xs font-semibold text-amber-700">
                          <i class="fa-solid fa-cloud-arrow-up"></i> Очікує публікації
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                      <form method="POST" action="<?php echo e(route('deployment.github.backup-branch.push', $branch)); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
                          <i class="fa-solid fa-cloud-arrow-up"></i>
                          Синхронізувати
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">5. Відкат до резервного коміту</h2>
          <p class="text-sm text-muted-foreground">Архів вибраного коміту завантажується з GitHub і застосовується до робочої директорії без жодних shell-команд.</p>
        </div>
        <?php if(count($backups) === 0): ?>
          <p class="text-sm text-muted-foreground">Резервних копій ще немає. Після першого синхронізування вони з’являться автоматично.</p>
        <?php else: ?>
          <form method="POST" action="<?php echo e(route('deployment.github.rollback')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <label class="block text-sm font-medium" for="rollback-commit-api">Оберіть резервний коміт</label>
            <select id="rollback-commit-api" name="commit" class="w-full rounded-2xl border border-input bg-background px-4 py-2">
              <?php $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($backup['commit']); ?>">
                  <?php echo e(\Illuminate\Support\Carbon::parse($backup['timestamp'])->format('d.m.Y H:i')); ?> — <?php echo e($backup['commit']); ?>

                </option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-warning px-5 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-warning/90">Виконати відкат</button>
          </form>
        <?php endif; ?>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-4 p-6">
        <h2 class="text-2xl font-semibold">Як працює режим без shell</h2>
        <ol class="list-decimal space-y-2 pl-5 text-sm text-muted-foreground">
          <li>Дані про поточний коміт зберігаються у <code>storage/app/deployment_backups.json</code>.</li>
          <li>Архіви гілок та комітів завантажуються напряму з GitHub через HTTPS, після чого файли копіюються у проєкт.</li>
          <li>Для пушу формується дерево файлів, створюються блоби та коміт за допомогою REST API GitHub.</li>
          <li>Жодна з операцій не використовує <code>proc_open</code>, <code>exec</code>, <code>shell_exec</code>, <code>system</code>, чи <code>passthru</code>.</li>
        </ol>
      </div>
    </section>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/deployment/github.blade.php ENDPATH**/ ?>