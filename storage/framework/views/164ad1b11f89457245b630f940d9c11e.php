

<?php $__env->startSection('title', 'Менеджер міграцій'); ?>

<?php $__env->startSection('content'); ?>
  <div class="max-w-4xl mx-auto space-y-8">
    <header class="space-y-2 text-center">
      <h1 class="text-3xl font-semibold">Керування міграціями</h1>
      <p class="text-muted-foreground">Запускайте нові міграції або відкатуйте останню партію безпосередньо з адмін-панелі.</p>
    </header>

    <?php if($feedback): ?>
      <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'rounded-2xl border p-4 shadow-soft',
        'border-success/40 bg-success/10 text-success' => $feedback['status'] === 'success',
        'border-destructive/40 bg-destructive/10 text-destructive-foreground' => $feedback['status'] === 'error',
      ]); ?>">
        <div class="font-medium"><?php echo e($feedback['message']); ?></div>
        <?php if(! empty($feedback['output'])): ?>
          <pre class="mt-3 max-h-64 overflow-y-auto whitespace-pre-wrap rounded-xl border border-border/70 bg-background/70 p-3 text-xs leading-relaxed"><?php echo e($feedback['output']); ?></pre>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">Виконати всі нові міграції</h2>
          <p class="text-sm text-muted-foreground">Команда <code>php artisan migrate --force</code> буде запущена на сервері. Всі незастосовані міграції будуть виконані.</p>
        </div>
        <form method="POST" action="<?php echo e(route('migrations.run')); ?>" class="space-y-3">
          <?php echo csrf_field(); ?>
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
            <i class="fa-solid fa-database mr-2"></i>
            Запустити міграції
          </button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">Відкотити останню партію</h2>
          <p class="text-sm text-muted-foreground">Запускається <code>php artisan migrate:rollback --step=1 --force</code>. Це поверне останні застосовані міграції.</p>
        </div>
        <form method="POST" action="<?php echo e(route('migrations.rollback')); ?>" class="space-y-3">
          <?php echo csrf_field(); ?>
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-warning px-5 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-warning/90">
            <i class="fa-solid fa-rotate-left mr-2"></i>
            Відкотити останню партію
          </button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">Незастосовані міграції</h2>
          <p class="text-sm text-muted-foreground">Список файлів, які ще не були виконані. Після запуску міграцій він спорожніє.</p>
        </div>
        <?php if($pendingMigrations->isEmpty()): ?>
          <p class="text-sm text-muted-foreground">Усі міграції виконані. Нових файлів не знайдено.</p>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border/70 text-sm">
              <thead class="bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                <tr>
                  <th class="px-4 py-3">Назва</th>
                  <th class="px-4 py-3">Шлях</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border/60 bg-background/60">
                <?php $__currentLoopData = $pendingMigrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $migration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                    <td class="px-4 py-3 font-medium"><?php echo e($migration['name']); ?></td>
                    <td class="px-4 py-3 text-xs font-mono"><?php echo e($migration['path']); ?></td>
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
          <h2 class="text-2xl font-semibold">Остання виконана партія</h2>
          <p class="text-sm text-muted-foreground">Дані з таблиці <code>migrations</code> для останнього значення <code>batch</code>.</p>
        </div>
        <?php if($lastBatch->isEmpty()): ?>
          <p class="text-sm text-muted-foreground">Ще не було виконано жодної міграції.</p>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border/70 text-sm">
              <thead class="bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                <tr>
                  <th class="px-4 py-3">Назва</th>
                  <th class="px-4 py-3">Партія</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border/60 bg-background/60">
                <?php $__currentLoopData = $lastBatch; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $migration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                    <td class="px-4 py-3 font-medium"><?php echo e($migration->migration); ?></td>
                    <td class="px-4 py-3">#<?php echo e($migration->batch); ?></td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/migrations/index.blade.php ENDPATH**/ ?>