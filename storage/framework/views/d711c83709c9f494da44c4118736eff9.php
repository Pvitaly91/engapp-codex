<?php ($categorySlug = $category->slug ?? null); ?>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e($categorySlug ? route('pages.show', [$categorySlug, $page->slug]) : '#'); ?>" class="block p-4 bg-card text-card-foreground rounded-xl shadow-soft hover:bg-muted">
            <div class="font-semibold"><?php echo e($page->title); ?></div>
            <?php if(! empty($page->text)): ?>
                <p class="mt-2 text-sm text-muted-foreground"><?php echo e($page->text); ?></p>
            <?php endif; ?>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/partials/page-grid.blade.php ENDPATH**/ ?>