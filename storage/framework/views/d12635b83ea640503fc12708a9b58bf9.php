

<?php $__env->startSection('title', 'Сторінки — керування'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Сторінки</h1>
                <p class="text-sm text-gray-500">Керуйте сторінками, редагуйте та оновлюйте блоки контенту.</p>
            </div>
            <a href="<?php echo e(route('pages.manage.create')); ?>" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">+ Нова сторінка</a>
        </div>

        <?php if(session('status')): ?>
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-600">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Назва</th>
                        <th class="px-4 py-3 text-left font-medium">Категорія</th>
                        <th class="px-4 py-3 text-left font-medium">Slug</th>
                        <th class="px-4 py-3 text-left font-medium">Оновлено</th>
                        <th class="px-4 py-3 text-right font-medium">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">
                                <?php if($page->category): ?>
                                    <a href="<?php echo e(route('pages.show', [$page->category->slug, $page->slug])); ?>" class="hover:underline" target="_blank" rel="noopener"><?php echo e($page->title); ?></a>
                                <?php else: ?>
                                    <?php echo e($page->title); ?>

                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-500"><?php echo e($page->category?->title ?? '—'); ?></td>
                            <td class="px-4 py-3 text-gray-500"><?php echo e($page->slug); ?></td>
                            <td class="px-4 py-3 text-gray-500"><?php echo e($page->updated_at?->diffForHumans()); ?></td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="<?php echo e(route('pages.manage.edit', $page)); ?>" class="rounded-lg border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-100">Редагувати</a>
                                    <form action="<?php echo e(route('pages.manage.destroy', $page)); ?>" method="POST" onsubmit="return confirm('Видалити сторінку?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1 text-sm text-red-600 hover:bg-red-100">Видалити</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">Ще немає сторінок. Створіть першу сторінку.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/manage/index.blade.php ENDPATH**/ ?>