

<?php $__env->startSection('title', 'Pages v2'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('engram.pages.partials.page-grid', ['pages' => $pages, 'targetRoute' => $targetRoute], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.engram', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/v2/index.blade.php ENDPATH**/ ?>