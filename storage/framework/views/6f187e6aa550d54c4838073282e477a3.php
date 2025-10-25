<?php $__env->startSection('title', 'Seed Runs'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Seed Runs</h1>
                    <p class="text-sm text-gray-500">Керуйте виконаними та невиконаними сидарами.</p>
                </div>
                <?php if($tableExists): ?>
                    <form method="POST" action="<?php echo e(route('seed-runs.run-missing')); ?>" data-preloader>
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-500 transition disabled:opacity-50" <?php if($pendingSeeders->isEmpty()): ?> disabled <?php endif; ?>>
                            <i class="fa-solid fa-play"></i>
                            Виконати всі невиконані
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if(session('status')): ?>
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-green-700">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (! ($tableExists)): ?>
                <div class="rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-yellow-800">
                    Таблиця <code class="font-mono">seed_runs</code> ще не створена. Запустіть міграції, щоб продовжити.
                </div>
            <?php endif; ?>
        </div>

        <?php if($tableExists): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Невиконані сидери</h2>
                    <?php if($pendingSeeders->isEmpty()): ?>
                        <p class="text-sm text-gray-500">Усі сидери вже виконані.</p>
                    <?php else: ?>
                        <ul class="space-y-3">
                            <?php $__currentLoopData = $pendingSeeders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $className): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-center justify-between gap-4">
                                    <span class="text-sm font-mono text-gray-700 break-all"><?php echo e($className); ?></span>
                                    <form method="POST" action="<?php echo e(route('seed-runs.run')); ?>" data-preloader>
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="class_name" value="<?php echo e($className); ?>">
                                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-500 transition">
                                            <i class="fa-solid fa-play"></i>
                                            Виконати
                                        </button>
                                    </form>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="bg-white shadow rounded-lg p-6 overflow-hidden">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Виконані сидери</h2>
                    <?php if($executedSeeders->isEmpty()): ?>
                        <p class="text-sm text-gray-500">Поки що немає виконаних сидерів.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-600">Class</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-600">Виконано</th>
                                        <th class="px-4 py-2 text-right font-medium text-gray-600">Дії</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php $__currentLoopData = $executedSeeders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seedRun): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="px-4 py-2 font-mono text-xs text-gray-700 break-all"><?php echo e($seedRun->class_name); ?></td>
                                            <td class="px-4 py-2 text-gray-600"><?php echo e(optional($seedRun->ran_at)->format('Y-m-d H:i:s')); ?></td>
                                            <td class="px-4 py-2 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST" action="<?php echo e(route('seed-runs.destroy-with-questions', $seedRun->id)); ?>" data-preloader data-confirm="Видалити лог та пов’язані питання?">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-md hover:bg-amber-500 transition">
                                                            <i class="fa-solid fa-broom"></i>
                                                            Видалити з питаннями
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="<?php echo e(route('seed-runs.destroy', $seedRun->id)); ?>" data-preloader data-confirm="Видалити лише запис про виконання?">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition">
                                                            <i class="fa-solid fa-trash"></i>
                                                            Видалити запис
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="seed-run-preloader" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg px-6 py-4 flex items-center gap-3 text-sm text-gray-700">
            <span class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span>
            <span>Виконується операція…</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const preloader = document.getElementById('seed-run-preloader');

            if (!preloader) {
                return;
            }

            document.querySelectorAll('form[data-preloader]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    const confirmMessage = form.dataset.confirm;

                    if (confirmMessage && !window.confirm(confirmMessage)) {
                        event.preventDefault();

                        return;
                    }

                    preloader.classList.remove('hidden');
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pvital01/data/www/gramlyze.com/resources/views/seed-runs/index.blade.php ENDPATH**/ ?>