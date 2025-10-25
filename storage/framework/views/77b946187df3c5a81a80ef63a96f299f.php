

<?php $__env->startSection('title', 'Збережені тести'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex flex-col md:flex-row gap-6">
    <aside class="md:w-48 w-full md:shrink-0">
        <form id="tag-filter" action="<?php echo e(route('saved-tests.cards')); ?>" method="GET">
            <?php if(isset($availableLevels) && $availableLevels->count()): ?>
                <div class="mb-4">
                    <label class="block text-sm mb-1">Level:</label>
                    <div class="flex flex-wrap gap-2">
                        <?php $__currentLoopData = $availableLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lvl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $id = 'level-' . md5($lvl); ?>
                            <div>
                                <input type="checkbox" name="levels[]" value="<?php echo e($lvl); ?>" id="<?php echo e($id); ?>" class="hidden peer" <?php echo e(in_array($lvl, $selectedLevels ?? []) ? 'checked' : ''); ?>>
                                <label for="<?php echo e($id); ?>" class="px-3 py-1 rounded border border-border cursor-pointer text-sm bg-muted peer-checked:bg-primary peer-checked:text-primary-foreground"><?php echo e($lvl); ?></label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $tagNames): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $isOther = in_array(strtolower($category), ['other', 'others']); ?>
                <?php if($isOther): ?>
                    <div class="mb-4" id="others-filter" data-open="false">
                        <h3 class="text-lg font-bold mb-2 flex justify-between items-center">
                            <span><?php echo e($category); ?></span>
                            <button type="button" id="toggle-others-btn" class="text-xs text-primary underline">Show</button>
                        </h3>
                        <div id="others-tags" class="flex flex-wrap gap-2" style="display:none;">
                            <?php $__currentLoopData = $tagNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $id = 'tag-' . md5($tag); ?>
                                <div>
                                    <input type="checkbox" name="tags[]" value="<?php echo e($tag); ?>" id="<?php echo e($id); ?>" class="hidden peer" <?php echo e(in_array($tag, $selectedTags ?? []) ? 'checked' : ''); ?>>
                                    <label for="<?php echo e($id); ?>" class="px-3 py-1 rounded border border-border cursor-pointer text-sm bg-muted peer-checked:bg-primary peer-checked:text-primary-foreground"><?php echo e($tag); ?></label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <h3 class="text-lg font-bold mb-2"><?php echo e($category); ?></h3>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <?php $__currentLoopData = $tagNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $id = 'tag-' . md5($tag); ?>
                            <div>
                                <input type="checkbox" name="tags[]" value="<?php echo e($tag); ?>" id="<?php echo e($id); ?>" class="hidden peer" <?php echo e(in_array($tag, $selectedTags ?? []) ? 'checked' : ''); ?>>
                                <label for="<?php echo e($id); ?>" class="px-3 py-1 rounded border border-border cursor-pointer text-sm bg-muted peer-checked:bg-primary peer-checked:text-primary-foreground"><?php echo e($tag); ?></label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </form>
        <?php if(!empty($selectedTags) || !empty($selectedLevels)): ?>
            <div class="mt-2">
                <a href="<?php echo e(route('saved-tests.cards')); ?>" class="text-xs text-muted-foreground hover:underline">Скинути фільтр</a>
            </div>
        <?php endif; ?>
    </aside>
    <div class="flex-1">
        <?php if($tests->count()): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php $__currentLoopData = $tests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-card text-card-foreground p-4 rounded-2xl shadow-soft flex flex-col">
                        <div class="font-bold text-lg mb-1"><?php echo e($test->name); ?></div>
                        <div class="text-xs text-muted-foreground mb-2">
                            Створено: <?php echo e($test->created_at->format('d.m.Y')); ?><br>
                            Питань: <?php echo e(count($test->questions)); ?><br>
                            <?php
                                $order = array_flip(['A1','A2','B1','B2','C1','C2']);
                                $levels = $test->levels
                                    ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
                                    ->map(fn($lvl) => $lvl ?? 'N/A');
                            ?>
                            Рівні: <?php echo e($levels->join(', ')); ?>

                        </div>
                        <div class="mb-3 text-xs">
                            <?php $__currentLoopData = $test->tag_names; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-block bg-muted px-2 py-0.5 mr-1 mb-1 rounded"><?php echo e($t); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php if($test->description): ?>
                            <div class="test-description text-sm mb-3"><?php echo e(\Illuminate\Support\Str::limit(strip_tags($test->description, 120))); ?></div>
                        <?php endif; ?>
                        <?php
                            $preferredView = data_get($test->filters, 'preferred_view');
                            $testRoute = $preferredView === 'drag-drop'
                                ? route('saved-test.js.drag-drop', $test->slug)
                                : route('saved-test.js', $test->slug);
                        ?>
                        <a href="<?php echo e($testRoute); ?>" class="mt-auto inline-block text-center bg-primary hover:bg-primary/80 text-primary-foreground px-4 py-2 rounded-2xl text-sm font-semibold">Пройти тест</a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="text-muted-foreground">Ще немає збережених тестів.</div>
        <?php endif; ?>
</div>
</div>
<script>
    document.querySelectorAll('#tag-filter input[type=checkbox]').forEach(el => {
        el.addEventListener('change', () => document.getElementById('tag-filter').submit());
    });
    const toggleBtn = document.getElementById('toggle-others-btn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const tags = document.getElementById('others-tags');
            const hidden = tags.style.display === 'none';
            tags.style.display = hidden ? '' : 'none';
            toggleBtn.textContent = hidden ? 'Hide' : 'Show';
        });
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.engram', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/saved-tests-cards.blade.php ENDPATH**/ ?>