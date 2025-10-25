<?php
    $level = $level ?? 0;
    $indent = max(0, $level) * 1.25;
?>

<div class="space-y-2">
    <?php $__currentLoopData = $nodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $node): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(($node['type'] ?? null) === 'folder'): ?>
            <?php
                $folderIsOpen = $node['has_active_descendant'] ?? false;
                $folderChildren = $node['children'] ?? [];
                $folderSeederCount = $node['seeder_count'] ?? 0;
                $folderDisplayName = $node['name'] ?? $node['display_name'] ?? '';
            ?>
            <div x-data="{ open: <?php echo e($folderIsOpen ? 'true' : 'false'); ?> }" class="space-y-2">
                <div class="flex items-center gap-2" style="margin-left: <?php echo e($indent); ?>rem;">
                    <button type="button"
                            class="flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900 transition min-w-0"
                            @click="open = !open"
                            :aria-expanded="open.toString()">
                        <span class="flex-shrink-0 inline-flex items-center justify-center h-6 w-6 rounded-full border border-slate-200 bg-white">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-3.5 w-3.5 text-slate-500 transition-transform"
                                 :class="{ 'rotate-90': open }"
                                 viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M6.22 4.47a.75.75 0 011.06 0l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 01-1.06-1.06L10.69 10 6.22 5.53a.75.75 0 010-1.06z"
                                      clip-rule="evenodd" />
                            </svg>
                        </span>
                        <span class="flex items-center gap-2 min-w-0">
                            <i class="fa-solid fa-folder-tree text-slate-500 flex-shrink-0"></i>
                            <span class="truncate" title="<?php echo e($folderDisplayName); ?>"><?php echo e($folderDisplayName); ?></span>
                        </span>
                        <span class="text-xs font-normal text-slate-500 flex-shrink-0">(<?php echo e($folderSeederCount); ?>)</span>
                    </button>
                </div>

                <div x-show="open" x-transition style="display: none;">
                    <?php echo $__env->make('grammar-test.partials.seeder-tree', [
                        'nodes' => $folderChildren,
                        'level' => $level + 1,
                        'selectedSourceCollection' => $selectedSourceCollection,
                        'recentSourceIds' => $recentSourceIds,
                        'recentSourceOrdinals' => $recentSourceOrdinals,
                    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        <?php elseif(($node['type'] ?? null) === 'seeder'): ?>
            <?php
                $seederSources = $node['sources'] instanceof \Illuminate\Support\Collection
                    ? $node['sources']
                    : collect($node['sources'] ?? []);
                $seederIsSelected = (bool) ($node['is_selected'] ?? false);
                $seederHasSelectedSources = (bool) ($node['has_selected_sources'] ?? false);
                $seederIsActive = (bool) ($node['is_active'] ?? false);
                $seederInputId = $node['input_id'] ?? ('seeder-' . md5($node['class_name'] ?? (string) \Illuminate\Support\Str::uuid()));
                $seederIsNew = (bool) ($node['is_new'] ?? false);
                $seederOrdinal = $node['ordinal'] ?? null;
                $seederDisplayName = $node['display_name'] ?? $node['name'] ?? $node['class_name'] ?? '';
                $groupIsHighlighted = $seederIsSelected || $seederHasSelectedSources;
            ?>
            <div x-data="{
                    open: <?php echo e($seederIsActive ? 'true' : 'false'); ?>,
                    toggle(openState = undefined) {
                        if (openState === undefined) {
                            this.open = !this.open;
                            return;
                        }

                        this.open = !!openState;
                    }
                }"
                 @toggle-all-seeder-sources.window="toggle($event.detail.open)"
                 class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'border rounded-2xl overflow-hidden transition',
                    'border-gray-200 bg-white' => ! $groupIsHighlighted,
                    'border-blue-400 shadow-sm bg-blue-50' => $groupIsHighlighted,
                 ]); ?>"
                 style="margin-left: <?php echo e($indent); ?>rem;"
            >
                <div class="flex items-center justify-between gap-3 px-4 py-2">
                    <label for="<?php echo e($seederInputId); ?>"
                           class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'flex items-center gap-3 text-sm font-semibold text-gray-800 cursor-pointer min-w-0 flex-1',
                                'text-blue-800' => $seederIsSelected,
                           ]); ?>"
                           @click.stop
                    >
                        <input type="checkbox"
                               name="seeder_classes[]"
                               value="<?php echo e($node['class_name']); ?>"
                               id="<?php echo e($seederInputId); ?>"
                               <?php echo e($seederIsSelected ? 'checked' : ''); ?>

                               class="h-4 w-4 text-blue-600 border-gray-300 rounded flex-shrink-0">
                        <span class="min-w-0">
                            <span class="truncate block" title="<?php echo e($node['full_class_name'] ?? $seederDisplayName); ?>"><?php echo e($seederDisplayName); ?></span>
                            <?php if($seederIsNew): ?>
                                <span class="block text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full mt-1 w-fit">
                                    Новий<?php echo e(!is_null($seederOrdinal) ? ' #' . $seederOrdinal : ''); ?>

                                </span>
                            <?php endif; ?>
                        </span>
                    </label>

                    <button type="button"
                            class="inline-flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:bg-blue-100 flex-shrink-0"
                            @click.stop="toggle()"
                            :aria-expanded="open.toString()"
                            aria-label="Перемкнути список джерел">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div x-show="open" x-transition style="display: none;" class="px-4 pb-4 pt-2">
                    <?php if($seederSources->isEmpty()): ?>
                        <p class="text-xs text-gray-500">Для цього сидера немає пов'язаних джерел.</p>
                    <?php else: ?>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $seederSources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $sourceIsSelected = $selectedSourceCollection->contains($source->id ?? null);
                                    $sourceIsNew = $recentSourceIds->contains($source->id ?? null);
                                    $sourceOrdinal = $recentSourceOrdinals->get($source->id ?? null);
                                ?>
                                <label class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'flex items-start gap-2 px-3 py-1 rounded-full border text-sm transition text-left',
                                    'border-gray-200 bg-white hover:border-blue-300' => ! $sourceIsSelected,
                                    'border-blue-400 bg-blue-50 shadow-sm' => $sourceIsSelected,
                                ]); ?>">
                                    <input type="checkbox" name="sources[]" value="<?php echo e($source->id); ?>"
                                           <?php echo e($sourceIsSelected ? 'checked' : ''); ?>

                                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    <span class="whitespace-normal break-words flex items-center gap-2 flex-wrap">
                                        <span title="<?php echo e($source->name); ?>"><?php echo e($source->name); ?> (ID: <?php echo e($source->id); ?>)</span>
                                        <?php if($sourceIsNew): ?>
                                            <span class="text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                                Новий<?php echo e(!is_null($sourceOrdinal) ? ' #' . $sourceOrdinal : ''); ?>

                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/grammar-test/partials/seeder-tree.blade.php ENDPATH**/ ?>