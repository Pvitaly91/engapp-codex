<?php $__env->startSection('title', $page->title); ?>

<?php $__env->startSection('content'); ?>
    <article class="max-w-none space-y-4">
        <?php echo $__env->make($page->view, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </article>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.engram', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pvital01/data/www/gramlyze.com/resources/views/engram/pages/show.blade.php ENDPATH**/ ?>