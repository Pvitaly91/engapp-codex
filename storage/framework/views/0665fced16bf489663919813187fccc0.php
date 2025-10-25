<?php $__env->startSection('title', 'Pages'); ?>

<?php $__env->startSection('content'); ?>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('pages.show', $page->slug)); ?>" class="block p-4 bg-card text-card-foreground rounded-xl shadow-soft hover:bg-muted">
                <?php echo e($page->title); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.engram', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pvital01/data/www/gramlyze.com/resources/views/engram/pages/index.blade.php ENDPATH**/ ?>