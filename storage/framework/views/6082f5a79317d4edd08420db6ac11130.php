<nav class="text-sm text-gray-600 mb-4" aria-label="Breadcrumb">
    <ol class="list-reset flex items-center">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="flex items-center">
                <?php if(!$loop->last): ?>
                    <a href="<?php echo e($item['url'] ?? '#'); ?>" class="text-blue-600 hover:underline"><?php echo e($item['label']); ?></a>
                    <span class="mx-2">/</span>
                <?php else: ?>
                    <span class="text-gray-700"><?php echo e($item['label']); ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</nav>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/components/breadcrumbs.blade.php ENDPATH**/ ?>