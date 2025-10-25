

<?php $__env->startSection('title', 'Теорія'); ?>

<?php $__env->startSection('content'); ?>
    <?php ($categoryPages = $categoryPages ?? collect()); ?>

    <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
        <?php echo $__env->make('engram.pages.partials.sidebar', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'categoryPages' => $categoryPages,
            'showCategoryPagesNav' => false,
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <section class="space-y-6">
            <header class="space-y-2">
                <h1 class="text-3xl font-semibold tracking-tight"><?php echo e(optional($selectedCategory)->title ?? 'Теорія'); ?></h1>
                <p class="text-sm text-muted-foreground">
                    <?php if($selectedCategory): ?>
                        Матеріали розділу «<?php echo e($selectedCategory->title); ?>».
                    <?php else: ?>
                        Виберіть категорію, щоб переглянути сторінки теорії.
                    <?php endif; ?>
                </p>
            </header>

            <?php if($categoryPages->isNotEmpty()): ?>
                <?php echo $__env->make('engram.pages.partials.page-grid', [
                    'pages' => $categoryPages,
                    'category' => $selectedCategory,
                ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php else: ?>
                <div class="rounded-2xl border border-dashed border-muted p-8 text-center text-muted-foreground">
                    Поки що в цій категорії немає сторінок. Спробуйте іншу категорію зліва.
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.engram', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/index.blade.php ENDPATH**/ ?>