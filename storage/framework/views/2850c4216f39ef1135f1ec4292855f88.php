<?php
    $indent = max(0, $depth) * 1.5;
    $recentSeedRunOrdinals = collect($recentSeedRunOrdinals ?? []);
?>

<?php if(($node['type'] ?? null) === 'folder'): ?>
    <?php
        $folderLabel = $node['path'] ?? $node['name'];
        $folderSeedRunIds = $node['seed_run_ids'] ?? [];
        $folderProfile = $node['folder_profile'] ?? [];
        $folderDeleteButton = $folderProfile['delete_button'] ?? __('Видалити з даними');
        $folderDeleteConfirm = $folderProfile['delete_confirm'] ?? __('Видалити всі сидери в папці «:folder» та пов’язані дані?');
    ?>
    <div class="space-y-3" style="margin-left: <?php echo e($indent); ?>rem;" data-folder-node data-folder-path="<?php echo e($node['path']); ?>" data-depth="<?php echo e($depth); ?>">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <button type="button"
                    class="flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900 transition"
                    data-folder-toggle
                    data-folder-path="<?php echo e($node['path']); ?>"
                    data-load-url="<?php echo e(route('seed-runs.folders.children')); ?>"
                    aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                     class="h-4 w-4 text-slate-500 transition-transform -rotate-90" data-folder-icon>
                    <path fill-rule="evenodd"
                          d="M6.22 4.47a.75.75 0 011.06 0l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 01-1.06-1.06L10.69 10 6.22 5.53a.75.75 0 010-1.06z"
                          clip-rule="evenodd" />
                </svg>
                <i class="fa-solid fa-folder-tree text-slate-500"></i>
                <span><?php echo e($node['name']); ?></span>
                <span class="text-xs font-normal text-slate-500">(<?php echo e($node['seeder_count'] ?? 0); ?>)</span>
            </button>

            <?php if(!empty($folderSeedRunIds)): ?>
                <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                    <form method="POST"
                          action="<?php echo e(route('seed-runs.folders.destroy-with-questions')); ?>"
                          data-preloader
                          data-confirm="<?php echo e(__($folderDeleteConfirm, ['folder' => $folderLabel])); ?>"
                          class="w-full sm:w-auto">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <input type="hidden" name="folder_label" value="<?php echo e($folderLabel); ?>">
                        <?php $__currentLoopData = $folderSeedRunIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seedRunId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <input type="hidden" name="seed_run_ids[]" value="<?php echo e($seedRunId); ?>">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-md hover:bg-amber-500 transition">
                            <i class="fa-solid fa-broom"></i>
                            <?php echo e($folderDeleteButton); ?>

                        </button>
                    </form>
                    <form method="POST"
                          action="<?php echo e(route('seed-runs.folders.destroy')); ?>"
                          data-preloader
                          data-confirm="Видалити записи про виконання для папки «<?php echo e(e($folderLabel)); ?>»?"
                          class="w-full sm:w-auto">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <input type="hidden" name="folder_label" value="<?php echo e($folderLabel); ?>">
                        <?php $__currentLoopData = $folderSeedRunIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seedRunId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <input type="hidden" name="seed_run_ids[]" value="<?php echo e($seedRunId); ?>">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition">
                            <i class="fa-solid fa-trash"></i>
                            Видалити записи
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-3 hidden" data-folder-children data-depth="<?php echo e($depth + 1); ?>"></div>
    </div>
<?php elseif(($node['type'] ?? null) === 'seeder'): ?>
    <?php
        $seedRun = $node['seed_run'];
        $dataProfile = $node['data_profile'] ?? ($seedRun->data_profile ?? []);
        $seederDeleteButton = $dataProfile['delete_button'] ?? __('Видалити з даними');
        $seederDeleteConfirm = $dataProfile['delete_confirm'] ?? __('Видалити лог та пов’язані дані?');
        $seedRunOrdinal = $recentSeedRunOrdinals->get($seedRun->id);
        $seedRunIsRecent = !is_null($seedRunOrdinal);
        $questionCount = (int) ($seedRun->question_count ?? 0);
    ?>
    <div style="margin-left: <?php echo e($indent); ?>rem;" data-seeder-node data-seed-run-id="<?php echo e($seedRun->id); ?>" data-depth="<?php echo e($depth); ?>">
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'border rounded-xl shadow-sm',
            'border-gray-200' => ! $seedRunIsRecent,
            'border-amber-300' => $seedRunIsRecent,
        ]); ?>">
            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'p-4 md:p-6',
                'bg-white' => ! $seedRunIsRecent,
                'bg-amber-50' => $seedRunIsRecent,
            ]); ?>">
                <div class="md:grid md:grid-cols-[minmax(0,3fr)_minmax(0,1fr)] md:items-start md:gap-6">
                    <div class="text-xs text-gray-700 break-words">
                        <div class="font-mono text-sm text-gray-800 flex flex-wrap items-center gap-2">
                            <?php echo e($node['name']); ?>

                            <?php if($seedRunIsRecent): ?>
                                <span class="text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                    Новий<?php echo e(' #' . $seedRunOrdinal); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if(\Illuminate\Support\Str::contains($seedRun->display_class_name, '\\')): ?>
                            <p class="text-xs text-gray-500 mt-1"><?php echo e($seedRun->display_class_name); ?></p>
                        <?php endif; ?>

                        <p class="text-xs text-gray-500 mt-2 <?php echo e($questionCount > 0 ? 'hidden' : ''); ?>" data-no-questions-message data-seed-run-id="<?php echo e($seedRun->id); ?>">
                            Питання відсутні.
                        </p>

                        <?php if($questionCount > 0): ?>
                            <div class="mt-3 space-y-3" data-seeder-section data-seed-run-id="<?php echo e($seedRun->id); ?>">
                                <button type="button"
                                        class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1.5 rounded-full bg-blue-50 hover:bg-blue-100 transition w-full sm:w-auto"
                                        data-seeder-toggle
                                        data-seed-run-id="<?php echo e($seedRun->id); ?>"
                                        data-load-url="<?php echo e(route('seed-runs.seeders.categories', $seedRun->id)); ?>"
                                        data-loaded="false"
                                        aria-expanded="false">
                                    <span data-toggle-label-collapsed>
                                        Показати питання (
                                        <span class="font-semibold" data-seed-run-question-count data-seed-run-id="<?php echo e($seedRun->id); ?>"><?php echo e($questionCount); ?></span>
                                        )
                                    </span>
                                    <span class="hidden" data-toggle-label-expanded>
                                        Сховати питання (
                                        <span class="font-semibold" data-seed-run-question-count data-seed-run-id="<?php echo e($seedRun->id); ?>"><?php echo e($questionCount); ?></span>
                                        )
                                    </span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" data-seeder-toggle-icon viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div class="hidden space-y-4" data-seeder-content data-seed-run-id="<?php echo e($seedRun->id); ?>"></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4 md:mt-0 flex flex-col sm:flex-row md:flex-col md:items-end gap-2 text-sm text-gray-600">
                        <form method="POST" action="<?php echo e(route('seed-runs.destroy-with-questions', $seedRun->id)); ?>" data-preloader data-confirm="<?php echo e(__($seederDeleteConfirm)); ?>" class="flex-1 sm:flex-none md:flex-1 md:w-full">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-md hover:bg-amber-500 transition">
                                <i class="fa-solid fa-broom"></i>
                                <?php echo e($seederDeleteButton); ?>

                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(route('seed-runs.destroy', $seedRun->id)); ?>" data-preloader data-confirm="Видалити лише запис про виконання?" class="flex-1 sm:flex-none md:flex-1 md:w-full">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition">
                                <i class="fa-solid fa-trash"></i>
                                Видалити запис
                            </button>
                        </form>
                        <div class="text-xs text-gray-500 text-center sm:text-left md:text-right">
                            <span class="font-semibold text-gray-700 block md:hidden">Виконано:</span>
                            <span><?php echo e(optional($seedRun->ran_at)->format('Y-m-d H:i:s')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/seed-runs/partials/executed-node.blade.php ENDPATH**/ ?>