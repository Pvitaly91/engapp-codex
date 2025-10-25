<nav class="mx-auto mb-8 flex max-w-xl justify-center rounded-full border border-border/60 bg-muted/40 p-1 text-sm font-medium">
  <?php
    $current = request()->route()->getName();
    $links = [
      'deployment.index' => 'CLI (shell)',
      'deployment.github.index' => 'GitHub API',
    ];
  ?>
  <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $routeName => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
      $active = $current === $routeName;
    ?>
    <a
      href="<?php echo e(route($routeName)); ?>"
      class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'flex-1 rounded-full px-4 py-2 text-center transition-colors',
        'bg-primary text-primary-foreground shadow-soft' => $active,
        'text-muted-foreground hover:bg-muted/60' => ! $active,
      ]); ?>"
    >
      <?php echo e($label); ?>

    </a>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/deployment/partials/switcher.blade.php ENDPATH**/ ?>