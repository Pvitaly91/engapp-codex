

<?php $__env->startSection('title', 'Редагувати сторінку'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-6xl space-y-8">
        <?php echo $__env->make('engram.pages.v2.manage.partials.form', [
            'heading' => 'Редагування: ' . $page->title,
            'description' => 'Оновіть основні дані сторінки. Нижче ви можете керувати блоками контенту.',
            'formAction' => route('pages-v2.manage.update', $page),
            'formMethod' => 'PUT',
            'submitLabel' => 'Зберегти сторінку',
            'page' => $page,
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow">
            <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Блоки контенту</h2>
                    <p class="text-sm text-gray-500">Редагуйте, видаляйте або додавайте блоки для цієї сторінки.</p>
                </div>
                <a href="<?php echo e(route('pages-v2.manage.blocks.create', $page)); ?>" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">+ Новий блок</a>
            </header>

            <?php if($blocks->isEmpty()): ?>
                <p class="mt-6 text-sm text-gray-500">Для цієї сторінки ще немає блоків. Додайте перший, щоб розпочати.</p>
            <?php else: ?>
                <?php
                    $columnLabels = [
                        'left' => 'Ліва колонка',
                        'right' => 'Права колонка',
                        'header' => 'Шапка',
                    ];
                ?>

                <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">№</th>
                                <th class="px-4 py-3 text-left font-medium">Мова</th>
                                <th class="px-4 py-3 text-left font-medium">Тип</th>
                                <th class="px-4 py-3 text-left font-medium">Заголовок / прев'ю</th>
                                <th class="px-4 py-3 text-left font-medium">Колонка</th>
                                <th class="px-4 py-3 text-left font-medium">Порядок</th>
                                <th class="px-4 py-3 text-right font-medium">Дії</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($index + 1); ?></td>
                                    <td class="px-4 py-3 font-medium"><?php echo e($block->locale); ?></td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo e($block->type); ?></td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-gray-700">
                                            <?php if($block->heading): ?>
                                                <?php echo e($block->heading); ?>

                                            <?php elseif(! empty($block->body)): ?>
                                                <?php echo e(\Illuminate\Support\Str::limit(strip_tags($block->body), 80)); ?>

                                            <?php else: ?>
                                                <span class="text-gray-400">(без назви)</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo e($columnLabels[$block->column] ?? '—'); ?></td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo e($block->sort_order); ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a href="<?php echo e(route('pages-v2.manage.blocks.edit', [$page, $block])); ?>" class="rounded-lg border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-100">Редагувати</a>
                                            <form action="<?php echo e(route('pages-v2.manage.blocks.destroy', [$page, $block])); ?>" method="POST" onsubmit="return confirm('Видалити цей блок?');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1 text-sm text-red-600 hover:bg-red-100">Видалити</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/v2/manage/edit.blade.php ENDPATH**/ ?>