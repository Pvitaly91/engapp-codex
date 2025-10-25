<?php echo $__env->make('engram.pages.partials.grammar-card-styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<section class="grammar-card" lang="<?php echo e($locale); ?>">
    <header>
        <h2 class="gw-title"><?php echo e($page->title); ?></h2>
        <?php if(! empty($subtitleBlock?->body)): ?>
            <p class="gw-sub"><?php echo $subtitleBlock->body; ?></p>
        <?php endif; ?>
    </header>

    <div class="gw-grid">
        <?php $__currentLoopData = ['left', 'right']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $columnKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="gw-col">
                <?php $__currentLoopData = ($columns[$columnKey] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="gw-box<?php echo e($block->css_class ? ' ' . $block->css_class : ''); ?>">
                        <?php if(! empty($block->heading)): ?>
                            <h3><?php echo e($block->heading); ?></h3>
                        <?php endif; ?>
                        <?php echo $block->body; ?>

                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/partials/grammar-card.blade.php ENDPATH**/ ?>