

<?php $__env->startSection('title', 'Train'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mb-6">
        
        <!-- Меню з темами -->
        <div class="flex flex-wrap gap-2 mb-8">
            <?php $__currentLoopData = $topics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('train', $key == 'all' ? null : $key)); ?>"
                   class="px-4 py-2 rounded-xl border <?php if($current == $key): ?> bg-blue-600 text-white <?php else: ?> bg-white text-gray-700 hover:bg-blue-50 <?php endif; ?> transition text-sm font-medium shadow-sm">
                    <?php echo e($label); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    
    <!-- Основний контент розділу (приклад) -->
    <div>
        <?php if($current == 'all'): ?>
            <div class="text-gray-600 italic">Please, select a topic to start training.</div>
        <?php else: ?>
            <?php
                $viewPath = 'trainers.' . $current;
            ?>
            <?php if(view()->exists($viewPath)): ?>
                <?php echo $__env->make($viewPath, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php else: ?>
                <div class="p-8 bg-white rounded-xl shadow text-center text-gray-400">
                    [ Trainer for topic <b><?php echo e($topics[$current] ?? ucfirst($current)); ?></b> is not implemented yet ]
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/train/index.blade.php ENDPATH**/ ?>