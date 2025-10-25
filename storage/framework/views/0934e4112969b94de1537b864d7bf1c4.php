

<?php $__env->startSection('title', 'Редагувати блок — ' . $page->title); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('engram.pages.v2.manage.blocks.partials.form', [
        'heading' => 'Редагування блока',
        'description' => 'Оновіть дані блока сторінки «' . $page->title . '».',
        'formAction' => route('pages-v2.manage.blocks.update', [$page, $block]),
        'formMethod' => 'PUT',
        'submitLabel' => 'Зберегти блок',
        'page' => $page,
        'block' => $block,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/v2/manage/blocks/edit.blade.php ENDPATH**/ ?>