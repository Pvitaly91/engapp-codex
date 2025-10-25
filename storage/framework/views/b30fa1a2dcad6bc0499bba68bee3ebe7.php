<?php
    $node = $node ?? [];
    $depth = max(0, $depth ?? 0);
    $indent = $depth * 1.25;
?>

<?php if(($node['type'] ?? null) === 'folder'): ?>
    <div class="space-y-2" style="margin-left: <?php echo e($indent); ?>rem;">
        <div x-data="{ open: <?php echo e($depth === 0 ? 'true' : 'false'); ?> }" class="space-y-2">
            <button type="button"
                    class="flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900 transition"
                    @click="open = !open"
                    :aria-expanded="open.toString()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                     class="h-4 w-4 text-slate-500 transition-transform" :class="{ '-rotate-90': !open }">
                    <path fill-rule="evenodd"
                          d="M6.22 4.47a.75.75 0 011.06 0l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 01-1.06-1.06L10.69 10 6.22 5.53a.75.75 0 010-1.06z"
                          clip-rule="evenodd" />
                </svg>
                <i class="fa-solid fa-folder-tree text-slate-500"></i>
                <span><?php echo e($node['name']); ?></span>
            </button>

            <div x-show="open" x-transition style="display: none;" class="space-y-2">
                <?php $__currentLoopData = $node['children'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('grammar-test.partials.seeder-tree-node', [
                        'node' => $child,
                        'depth' => $depth + 1,
                        'selectedSeederClasses' => $selectedSeederClasses ?? [],
                        'selectedSourceCollection' => $selectedSourceCollection ?? collect(),
                        'recentSeederClasses' => $recentSeederClasses ?? collect(),
                        'recentSeederOrdinals' => $recentSeederOrdinals ?? collect(),
                        'recentSourceIds' => $recentSourceIds ?? collect(),
                        'recentSourceOrdinals' => $recentSourceOrdinals ?? collect(),
                    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
<?php elseif(($node['type'] ?? null) === 'seeder'): ?>
    <?php
        $group = $node['group'] ?? [];
        $className = $node['class'] ?? ($group['seeder'] ?? null);
        $displaySeederName = $node['name'] ?? $node['display_name'] ?? $className;
        $seederSources = collect($group['sources'] ?? []);
        $selectedSeederClasses = $selectedSeederClasses ?? [];
        $selectedSourceCollection = collect($selectedSourceCollection ?? []);
        $recentSeederClasses = collect($recentSeederClasses ?? []);
        $recentSeederOrdinals = collect($recentSeederOrdinals ?? []);
        $recentSourceIds = collect($recentSourceIds ?? []);
        $recentSourceOrdinals = collect($recentSourceOrdinals ?? []);

        $seederIsSelected = in_array($className, $selectedSeederClasses, true);
        $seederSourceIds = $seederSources->pluck('id');
        $seederHasSelectedSources = $seederSourceIds->intersect($selectedSourceCollection)->isNotEmpty();
        $groupIsActive = $seederIsSelected || $seederHasSelectedSources;
        $seederInputId = 'seeder-' . md5($className ?? uniqid('', true));
        $seederIsNew = $recentSeederClasses->contains($className);
        $seederOrdinal = $recentSeederOrdinals->get($className);
    ?>

    <div style="margin-left: <?php echo e($indent); ?>rem;">
        <div x-data="{
                open: <?php echo e($groupIsActive ? 'true' : 'false'); ?>,
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
                'border-gray-200 bg-white' => ! $groupIsActive,
                'border-blue-400 shadow-sm bg-blue-50' => $groupIsActive,
             ]); ?>"
        >
            <div class="flex items-center justify-between gap-3 px-4 py-2 bg-gray-50 cursor-pointer"
                 @click="toggle()"
            >
                <label for="<?php echo e($seederInputId); ?>"
                       class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'flex items-center gap-2 text-sm font-semibold text-gray-800 cursor-pointer',
                            'text-blue-800' => $seederIsSelected,
                       ]); ?>"
                       @click.stop
                >
                    <input type="checkbox" name="seeder_classes[]" value="<?php echo e($className); ?>" id="<?php echo e($seederInputId); ?>"
                           <?php echo e($seederIsSelected ? 'checked' : ''); ?>

                           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <span class="truncate flex items-center gap-2" title="<?php echo e($className); ?>">
                        <span class="truncate"><?php echo e($displaySeederName); ?></span>
                        <?php if($seederIsNew): ?>
                            <span class="text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                Новий<?php echo e(!is_null($seederOrdinal) ? ' #' . $seederOrdinal : ''); ?>

                            </span>
                        <?php endif; ?>
                    </span>
                </label>
                <button type="button"
                        class="inline-flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:bg-blue-100"
                        @click.stop="toggle()"
                        :aria-expanded="open.toString()"
                        aria-label="Перемкнути список джерел">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <div x-show="open" x-transition style="display: none;" class="px-4 pb-4 pt-2 bg-white">
                <?php if($seederSources->isEmpty()): ?>
                    <p class="text-xs text-gray-500">Для цього сидера немає пов'язаних джерел.</p>
                <?php else: ?>
                    <div class="flex flex-wrap gap-2">
                        <?php $__currentLoopData = $seederSources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $sourceIsSelected = $selectedSourceCollection->contains($source->id);
                                $sourceIsNew = $recentSourceIds->contains($source->id);
                                $sourceOrdinal = $recentSourceOrdinals->get($source->id);
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
                                    <span><?php echo e($source->name); ?> (ID: <?php echo e($source->id); ?>)</span>
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
    </div>
<?php endif; ?>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/grammar-test/partials/seeder-tree-node.blade.php ENDPATH**/ ?>