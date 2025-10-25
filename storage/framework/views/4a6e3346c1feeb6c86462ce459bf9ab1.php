

<?php $__env->startSection('title', $page->title); ?>

<?php $__env->startSection('content'); ?>
    <article class="max-w-none space-y-4">
        <?php echo $__env->make('engram.pages.partials.grammar-card', [
            'page' => $page,
            'subtitleBlock' => $subtitleBlock ?? null,
            'columns' => $columns ?? [],
            'locale' => $locale ?? app()->getLocale(),
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </article>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.engram', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/v2/show.blade.php ENDPATH**/ ?>