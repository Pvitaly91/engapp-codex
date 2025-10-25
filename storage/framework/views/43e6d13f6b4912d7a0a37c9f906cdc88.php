<?php $__env->startSection('title', 'Збережені тести'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Збережені тести</h1>
        <a href="<?php echo e(route('grammar-test')); ?>"
           class="inline-flex items-center justify-center bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-2xl font-semibold hover:bg-blue-100 transition">
            + До фільтра / конструктора тестів
        </a>
    </div>

    <?php echo $__env->make('components.word-search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php if($tests->count()): ?>
        <ul class="space-y-4">
            <?php $__currentLoopData = $tests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex-1 min-w-0">
                    <a href="<?php echo e(route('saved-test.show', $test->slug)); ?>" class="text-lg text-blue-700 font-semibold hover:underline">
                        <?php echo e($test->name); ?>

                    </a>
                    <?php
                        $questionsAttribute = $test->questions;
                        if (! is_array($questionsAttribute) && is_string($questionsAttribute)) {
                            $questionsAttribute = json_decode($questionsAttribute, true) ?? [];
                        }
                        $questionCount = $test->question_count ?? (is_array($questionsAttribute) ? count($questionsAttribute) : 0);
                    ?>
                    <div class="mt-2 text-xs text-gray-500 flex flex-wrap gap-x-3 gap-y-1">
                        <span class="whitespace-nowrap">Створено: <?php echo e($test->created_at->format('d.m.Y H:i')); ?></span>
                        <span class="whitespace-nowrap">Питань: <?php echo e($questionCount); ?></span>
                    </div>
                    <?php if($test->description): ?>
                        <div class="test-description text-sm text-gray-800 mt-3"><?php echo e(\Illuminate\Support\Str::limit($test->description, 140)); ?></div>
                    <?php endif; ?>
                </div>
                <div class="flex flex-wrap gap-2 sm:w-100">
                    <a href="<?php echo e(route('saved-test.show', $test->slug)); ?>"
                       class="flex-1 sm:flex-none text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-2xl text-sm font-semibold transition">
                        Пройти тест
                    </a>
                    <a href="<?php echo e(route('saved-test.tech', $test->slug)); ?>"
                       class="flex-1 sm:flex-none text-center bg-white border border-blue-200 text-blue-700 px-4 py-2 rounded-2xl text-sm font-semibold hover:bg-blue-50 transition">
                        Технічна сторінка
                    </a>
                    <form action="<?php echo e(route('saved-tests.destroy', $test->slug)); ?>" method="POST"
                          onsubmit="return confirm('Видалити цей тест?');" class="inline-flex flex-1 sm:flex-none">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit"
                            class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-2xl text-sm font-semibold transition">
                            Видалити
                        </button>
                    </form>
                </div>
            </li>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <div class="mt-6">
            <?php echo e($tests->links()); ?>

        </div>
    <?php else: ?>
        <div class="text-gray-600">Ще немає збережених тестів.</div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pvital01/data/www/gramlyze.com/resources/views/saved-tests.blade.php ENDPATH**/ ?>