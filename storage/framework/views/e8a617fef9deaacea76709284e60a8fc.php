<div class="mb-6">
    <div class="flex items-center justify-between text-sm">
        <span id="progress-label" class="text-stone-600"><?php echo e($progress ?? '0 / 0'); ?></span>
        <span id="score-label" class="text-stone-600"><?php echo e($score ?? 'Точність: 0%'); ?></span>
    </div>
    <div class="w-full h-2 bg-stone-200 rounded-full mt-2">
        <div id="progress-bar" class="h-2 bg-stone-900 rounded-full" style="width:0%"></div>
    </div>
</div>
<?php /**PATH /var/www/pvital01/data/www/gramlyze.com/resources/views/components/saved-test-progress.blade.php ENDPATH**/ ?>