<?php ($categoryPages = $categoryPages ?? collect()); ?>
<?php ($showCategoryPagesNav = $showCategoryPagesNav ?? false); ?>
<?php ($class = trim('space-y-8 ' . ($class ?? ''))); ?>
<aside class="<?php echo e($class); ?>">
    <div>
        <h2 class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Категорії</h2>
        <nav class="mt-3 space-y-1">
            <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php ($isActive = $selectedCategory && $selectedCategory->is($category)); ?>
                <a
                    href="<?php echo e(route('pages.category', $category->slug)); ?>"
                    class="block rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-muted/80 <?php echo e($isActive ? 'bg-primary/10 text-primary' : 'text-muted-foreground'); ?>"
                >
                    <span><?php echo e($category->title); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-muted-foreground">Немає категорій.</p>
            <?php endif; ?>
        </nav>
    </div>

    <?php if($showCategoryPagesNav && $selectedCategory && $categoryPages->isNotEmpty()): ?>
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Сторінки розділу</h3>
            <nav class="mt-3 space-y-1">
                <?php $__currentLoopData = $categoryPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pageItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php ($isCurrentPage = isset($currentPage) && $currentPage && $currentPage->is($pageItem)); ?>
                    <a
                        href="<?php echo e(route('pages.show', [$selectedCategory->slug, $pageItem->slug])); ?>"
                        class="block rounded-xl px-3 py-2 text-sm transition hover:bg-muted/80 <?php echo e($isCurrentPage ? 'bg-secondary/20 text-secondary-foreground font-semibold' : 'text-muted-foreground'); ?>"
                    >
                        <?php echo e($pageItem->title); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>
        </div>
    <?php endif; ?>
</aside>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/partials/sidebar.blade.php ENDPATH**/ ?>