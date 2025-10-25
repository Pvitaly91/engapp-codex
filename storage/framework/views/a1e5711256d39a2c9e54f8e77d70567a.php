<?php
    $options = collect($options ?? []);
    $textAnswers = collect($textAnswers ?? []);
?>

<?php if($options->isEmpty() && $textAnswers->isEmpty()): ?>
    <p class="text-xs text-gray-500">Варіанти відповіді не знайдені.</p>
<?php else: ?>
    <?php if($options->isNotEmpty()): ?>
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Варіанти відповіді</p>
            <ul class="grid gap-2 sm:grid-cols-2">
                <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'flex items-center gap-2 rounded border px-3 py-2 text-sm',
                        'border-emerald-200 bg-emerald-50 text-emerald-900 font-semibold shadow-sm' => $option['is_correct'] ?? false,
                        'border-slate-200 bg-white text-slate-700' => ! ($option['is_correct'] ?? false),
                    ]); ?>">
                        <?php if($option['is_correct'] ?? false): ?>
                            <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.25a1 1 0 0 1-1.425-.01L3.29 9.967a1 1 0 1 1 1.42-1.407l3.162 3.19 6.49-6.538a1 1 0 0 1 1.342-.088Z" clip-rule="evenodd" />
                            </svg>
                        <?php else: ?>
                            <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                        <?php endif; ?>
                        <span><?php echo e($option['label'] ?? '—'); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if($textAnswers->isNotEmpty()): ?>
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Правильні відповіді</p>
            <ul class="space-y-1 text-sm text-slate-700">
                <?php $__currentLoopData = $textAnswers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex items-center gap-2 rounded border border-emerald-200 bg-emerald-50 px-3 py-1.5 font-semibold text-emerald-900 shadow-sm">
                        <span class="font-mono text-[11px] uppercase tracking-wide text-emerald-600"><?php echo e($answer['marker']); ?></span>
                        <span><?php echo e($answer['label'] ?? '—'); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/seed-runs/partials/question-answers.blade.php ENDPATH**/ ?>