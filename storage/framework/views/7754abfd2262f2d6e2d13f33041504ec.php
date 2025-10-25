<nav class="mb-6 text-sm space-y-2">
    <div class="flex items-center gap-2">
        <span class="font-semibold">View mode:</span>
        <a href="<?php echo e(route('saved-test.js', $test->slug)); ?>" class="px-3 py-1 rounded-lg border border-stone-300 <?php echo e(!\Illuminate\Support\Str::contains(request()->path(), 'step') ? 'bg-stone-900 text-white' : ''); ?>">card</a>
        <a href="<?php echo e(route('saved-test.js.step', $test->slug)); ?>" class="px-3 py-1 rounded-lg border border-stone-300 <?php echo e(\Illuminate\Support\Str::contains(request()->path(), 'step') ? 'bg-stone-900 text-white' : ''); ?>">step</a>
    </div> 
    <div class="flex items-center gap-2">
         <span class="font-semibold">Diffculity:</span> 
        <?php if(\Illuminate\Support\Str::contains(request()->path(), 'step')): ?>   
            <a href="<?php echo e(route('saved-test.js.step', $test->slug)); ?>" class="px-3 py-1 rounded-lg border border-stone-300 <?php echo e(request()->routeIs('saved-test.js.step') ? 'bg-stone-900 text-white' : ''); ?>">Easy</a>
            <a href="<?php echo e(route('saved-test.js.step-select', $test->slug)); ?>" class="px-3 py-1 rounded-lg border border-stone-300 <?php echo e(request()->routeIs('saved-test.js.step-select') ? 'bg-stone-900 text-white' : ''); ?>">Mdium</a>
            <a href="<?php echo e(route('saved-test.js.step-input', $test->slug)); ?>" class="px-3 py-1 rounded-lg border border-stone-300 <?php echo e(request()->routeIs('saved-test.js.step-input') ? 'bg-stone-900 text-white' : ''); ?>">Hard</a>
            <a href="<?php echo e(route('saved-test.js.step-manual', $test->slug)); ?>" class="px-3 py-1 rounded-lg border border-stone-300 <?php echo e(request()->routeIs('saved-test.js.step-manual') ? 'bg-stone-900 text-white' : ''); ?>">Expert</a>
        <?php else: ?>
            <a href="<?php echo e(route('saved-test.js', $test->slug)); ?>" class="px-3 py-1 rounded-lg border border-stone-300 <?php echo e(request()->routeIs('saved-test.js') ? 'bg-stone-900 text-white' : ''); ?>">Easy</a>
            <a href="<?php echo e(route('saved-test.js.select', $test->slug)); ?>" class="px-3 py-1 rounded-lg border border-stone-300 <?php echo e(request()->routeIs('saved-test.js.select') ? 'bg-stone-900 text-white' : ''); ?>">Mdium</a>
            <a href="<?php echo e(route('saved-test.js.input', $test->slug)); ?>" class="px-3 py-1 rounded-lg border border-stone-300 <?php echo e(request()->routeIs('saved-test.js.input') ? 'bg-stone-900 text-white' : ''); ?>">Hard</a>
            <a href="<?php echo e(route('saved-test.js.manual', $test->slug)); ?>" class="px-3 py-1 rounded-lg border border-stone-300 <?php echo e(request()->routeIs('saved-test.js.manual') ? 'bg-stone-900 text-white' : ''); ?>">Expert</a>
        <?php endif; ?>
     </div>  
</nav>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/components/test-mode-nav.blade.php ENDPATH**/ ?>