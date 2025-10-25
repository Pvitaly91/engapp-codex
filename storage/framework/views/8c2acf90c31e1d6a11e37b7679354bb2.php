

<?php $__env->startSection('title', $page->title); ?>

<?php $__env->startSection('content'); ?>
    <?php ($mobileSelectedCategory = $selectedCategory ?? null); ?>
    <?php ($mobileCategoryPages = ($categoryPages ?? collect())); ?>
    <?php ($hasCategoryPages = $mobileSelectedCategory && $mobileCategoryPages->isNotEmpty()); ?>

    <div class="space-y-6 lg:space-y-0 lg:grid lg:grid-cols-[260px_1fr] lg:items-start lg:gap-8">
        <div
            class="lg:hidden space-y-4"
            x-data="{
                showCategories: false,
                showPages: <?php echo e($hasCategoryPages ? 'true' : 'false'); ?>

            }"
        >
            <div class="rounded-2xl border border-border/80 bg-card shadow-soft">
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold"
                    @click="showCategories = !showCategories"
                    :aria-expanded="showCategories"
                >
                    <span>Категорії</span>
                    <span class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                        <span><?php echo e($mobileSelectedCategory->title ?? 'Оберіть категорію'); ?></span>
                        <svg
                            class="h-4 w-4 transition-transform"
                            :class="{ 'rotate-180': showCategories }"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
                <div x-show="showCategories" x-transition style="display: none;" class="border-t border-border/80">
                    <nav class="space-y-1 px-4 py-3">
                        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php ($isActive = $mobileSelectedCategory && $mobileSelectedCategory->is($category)); ?>
                            <a
                                href="<?php echo e(route('pages.category', $category->slug)); ?>"
                                class="block rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-muted/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 <?php echo e($isActive ? 'bg-primary/10 text-primary' : 'text-muted-foreground'); ?>"
                                <?php if($isActive): ?> aria-current="page" <?php endif; ?>
                            >
                                <?php echo e($category->title); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="px-3 py-2 text-sm text-muted-foreground">Немає категорій.</p>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>

            <?php if($hasCategoryPages): ?>
                <div class="rounded-2xl border border-border/80 bg-card shadow-soft">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold"
                        @click="showPages = !showPages"
                        :aria-expanded="showPages"
                    >
                        <span>Сторінки розділу</span>
                        <svg
                            class="h-4 w-4 transition-transform"
                            :class="{ 'rotate-180': showPages }"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="showPages" x-transition style="display: none;" class="border-t border-border/80">
                        <nav class="space-y-1 px-4 py-3">
                            <?php $__currentLoopData = $mobileCategoryPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pageItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php ($isCurrentPage = $page->is($pageItem)); ?>
                                <a
                                    href="<?php echo e(route('pages.show', [$mobileSelectedCategory->slug, $pageItem->slug])); ?>"
                                    class="block rounded-xl px-3 py-2 text-sm transition hover:bg-muted/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary/40 <?php echo e($isCurrentPage ? 'bg-secondary/20 text-secondary-foreground font-semibold' : 'text-muted-foreground'); ?>"
                                    <?php if($isCurrentPage): ?> aria-current="page" <?php endif; ?>
                                >
                                    <?php echo e($pageItem->title); ?>

                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="hidden lg:block">
            <?php echo $__env->make('engram.pages.partials.sidebar', [
                'categories' => $categories,
                'selectedCategory' => $selectedCategory ?? null,
                'categoryPages' => $categoryPages ?? collect(),
                'currentPage' => $page,
                'showCategoryPagesNav' => true,
                'class' => 'lg:sticky lg:top-24',
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <article class="max-w-none space-y-4 lg:col-start-2">
            <?php echo $__env->make('engram.pages.partials.grammar-card', [
                'page' => $page,
                'subtitleBlock' => $subtitleBlock ?? null,
                'columns' => $columns ?? [],
                'locale' => $locale ?? app()->getLocale(),
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </article>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.engram', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/show.blade.php ENDPATH**/ ?>