<?php
    $columnValue = old('column', $block->column);
?>

<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold"><?php echo e($heading); ?></h1>
            <p class="text-sm text-gray-500"><?php echo e($description); ?></p>
        </div>
        <a href="<?php echo e(route('pages.manage.edit', $page)); ?>" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">← До блоків сторінки</a>
    </div>

    <?php if($errors->any()): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">
            <div class="mb-2 font-semibold">Перевірте форму:</div>
            <ul class="list-disc space-y-1 pl-5">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e($formAction); ?>" class="space-y-6">
        <?php echo csrf_field(); ?>
        <?php if(($formMethod ?? 'POST') === 'PUT'): ?>
            <?php echo method_field('PUT'); ?>
        <?php endif; ?>

        <section class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow">
            <header class="space-y-1">
                <h2 class="text-xl font-semibold">Дані блока</h2>
                <p class="text-sm text-gray-500">Сторінка: <span class="font-medium text-gray-700"><?php echo e($page->title); ?></span></p>
            </header>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm font-medium">Мова</span>
                    <input type="text" name="locale" maxlength="8" value="<?php echo e(old('locale', $block->locale)); ?>" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium">Тип</span>
                    <input type="text" name="type" maxlength="32" value="<?php echo e(old('type', $block->type)); ?>" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium">Колонка</span>
                    <select name="column" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="" <?php if($columnValue === null || $columnValue === ''): echo 'selected'; endif; ?>>—</option>
                        <option value="header" <?php if($columnValue === 'header'): echo 'selected'; endif; ?>>Шапка</option>
                        <option value="left" <?php if($columnValue === 'left'): echo 'selected'; endif; ?>>Ліва колонка</option>
                        <option value="right" <?php if($columnValue === 'right'): echo 'selected'; endif; ?>>Права колонка</option>
                    </select>
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium">Порядок</span>
                    <input type="number" name="sort_order" min="0" value="<?php echo e(old('sort_order', $block->sort_order)); ?>" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="text-sm font-medium">CSS клас</span>
                    <input type="text" name="css_class" value="<?php echo e(old('css_class', $block->css_class)); ?>" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="text-sm font-medium">Заголовок</span>
                    <input type="text" name="heading" value="<?php echo e(old('heading', $block->heading)); ?>" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="text-sm font-medium">Контент</span>
                    <textarea name="body" rows="8" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200"><?php echo e(old('body', $block->body)); ?></textarea>
                </label>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="<?php echo e(route('pages.manage.edit', $page)); ?>" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">Скасувати</a>
            <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <?php echo e($submitLabel); ?>

            </button>
        </div>
    </form>
</div>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/manage/blocks/partials/form.blade.php ENDPATH**/ ?>