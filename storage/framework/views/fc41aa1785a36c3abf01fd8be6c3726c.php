<?php ($recentSeedRunOrdinals = collect($recentSeedRunOrdinals ?? [])); ?>

<?php $__currentLoopData = $nodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $node): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php echo $__env->make('seed-runs.partials.executed-node', [
        'node' => $node,
        'depth' => $depth,
        'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/seed-runs/partials/node-collection.blade.php ENDPATH**/ ?>