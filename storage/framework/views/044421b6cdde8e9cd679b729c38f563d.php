

<?php $__env->startSection('title', 'Words Test'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-xl mx-auto mt-8 p-8 bg-white rounded-xl shadow">
        <h2 class="text-2xl font-bold mb-4 text-blue-700">Test your vocabulary</h2>

        
        <?php if(isset($stats)): ?>
            <div class="mb-4 flex gap-4 text-gray-600 text-base">
                <div>Всього: <b><?php echo e($stats['total']); ?></b></div>
                <div>Вірно: <b class="text-green-700"><?php echo e($stats['correct']); ?></b></div>
                <div>Помилки: <b class="text-red-700"><?php echo e($stats['wrong']); ?></b></div>
            </div>
        <?php endif; ?>

        
        <?php if(isset($feedback)): ?>
            <div class="mb-4">
                <?php if($feedback['isCorrect']): ?>
                    <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">
                        Вірно! 🎉 — <b><?php echo e($feedback['word']); ?></b> = <b><?php echo e($feedback['correctAnswer']); ?></b>
                    </div>
                <?php else: ?>
                    <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">
                        Неправильно для <b><?php echo e($feedback['word']); ?></b>.<br>
                        Ваша відповідь: <b><?php echo e($feedback['userAnswer']); ?></b><br>
                        Правильно: <b><?php echo e($feedback['correctAnswer']); ?></b>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if($word): ?>
            <form method="POST" action="<?php echo e(route('words.test.check')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="word_id" value="<?php echo e($word->id); ?>">
                <input type="hidden" name="lang" value="<?php echo e($lang); ?>">
                <input type="hidden" name="questionType" value="<?php echo e($questionType); ?>">

                <div class="mb-6">
                    <?php if($questionType == 'en_to_uk'): ?>
                        <div class="text-lg mb-2">Оберіть правильний <b>український переклад</b> для слова:</div>
                        <div class="text-3xl font-bold text-blue-900 mb-6">
                            <?php echo e($word->word); ?>

                        </div>
                    <?php else: ?>
                        <div class="text-lg mb-2">Оберіть правильне <b>англійське слово</b> для перекладу:</div>
                        <div class="text-3xl font-bold text-blue-900 mb-6">
                            <?php echo e($translation); ?>

                        </div>
                    <?php endif; ?>

                    <div class="flex flex-col gap-3">
                        <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="block">
                                <input type="radio" name="answer" value="<?php echo e($option); ?>" required class="mr-2 align-middle">
                                <span class="align-middle"><?php echo e($option); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 transition">
                    Перевірити
                </button>
            </form>
        <?php else: ?>
            <div class="text-gray-500">Немає слів у словнику!</div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/words/test.blade.php ENDPATH**/ ?>