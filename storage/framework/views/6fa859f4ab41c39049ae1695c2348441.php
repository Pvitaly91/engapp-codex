

<?php $__env->startSection('title', 'Створити сторінку'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('engram.pages.manage.partials.form', [
        'heading' => 'Нова сторінка',
        'description' => 'Створіть структуру сторінки та додайте блоки контенту.',
        'formAction' => route('pages.manage.store'),
        'formMethod' => 'POST',
        'submitLabel' => 'Створити сторінку',
        'page' => $page,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/manage/create.blade.php ENDPATH**/ ?>