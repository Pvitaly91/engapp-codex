<?php $__env->startSection('title', 'Grammar Test Constructor V2'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto p-4 space-y-6">
    <?php
        $autocompleteRoute = url('/api/search?lang=en');
        $checkOneRoute = route('grammar-test.checkOne');
        $generateRoute = $generateRoute ?? route('grammar-test-v2.generate');
        $saveRoute = $saveRoute ?? route('grammar-test-v2.save');
        $savePayloadField = $savePayloadField ?? 'question_uuids';
        $savePayloadKey = $savePayloadKey ?? 'uuid';
        $questions = collect($questions ?? []);
        $categoryCollection = $categoriesDesc ?? $categories ?? collect();
        $tagGroups = $tagsByCategory ?? collect();
        $sourceGroups = $sourcesByCategory ?? collect();

        $selectedCategories = collect($selectedCategories ?? [])->all();
        $selectedSources = collect($selectedSources ?? [])->all();
        $selectedTags = collect($selectedTags ?? [])->all();
        $normalizedFilters = $normalizedFilters ?? null;

        $hasSelectedCategories = !empty($selectedCategories);
        $hasSelectedSources = !empty($selectedSources);
        $hasSelectedTags = !empty($selectedTags);
    ?>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <h1 class="text-2xl font-bold">Конструктор тесту (v2)</h1>
        <a href="<?php echo e(route('saved-tests.list')); ?>"
           class="inline-flex items-center justify-center bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-2xl font-semibold hover:bg-blue-100 transition">
            Збережені тести
        </a>
    </div>

    <form action="<?php echo e($generateRoute); ?>" method="POST" class="bg-white shadow rounded-2xl p-4 sm:p-6">
        <?php echo csrf_field(); ?>
        
            <div class="space-y-6 ">
                <div x-data="{ openCategoryTimes: <?php echo e(($hasSelectedCategories || $errors->has('categories')) ? 'true' : 'false'); ?> }"
                     class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'space-y-3 border border-transparent rounded-2xl p-3',
                        'border-blue-300 bg-blue-50' => $hasSelectedCategories,
                     ]); ?>"
                >
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold text-gray-700">Часи (категорії)</h2>
                        <button type="button"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                @click="openCategoryTimes = !openCategoryTimes">
                            <span x-text="openCategoryTimes ? 'Згорнути' : 'Розгорнути'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openCategoryTimes }" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-3" x-show="openCategoryTimes" x-transition style="display: none;">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <?php $__currentLoopData = $categoryCollection; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $categoryIsSelected = in_array($cat->id, $selectedCategories);
                                ?>
                                <label class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'flex items-center gap-2 text-sm rounded-xl px-3 py-2 transition border',
                                    'bg-gray-50 border-gray-200 hover:border-blue-300' => ! $categoryIsSelected,
                                    'bg-blue-50 border-blue-400 shadow-sm' => $categoryIsSelected,
                                ]); ?>">
                                    <input type="checkbox" name="categories[]" value="<?php echo e($cat->id); ?>"
                                        <?php echo e(in_array($cat->id, $selectedCategories) ? 'checked' : ''); ?>

                                        class="h-5 w-5 text-blue-600 border-gray-300 rounded">
                                    <span class="truncate"><?php echo e(ucfirst($cat->name)); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php $__errorArgs = ['categories'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red-500 text-sm mt-2"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <?php if($sourceGroups->isNotEmpty()): ?>
                    <div x-data="{ openSources: <?php echo e($hasSelectedSources ? 'true' : 'false'); ?> }"
                         class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'space-y-3 border border-transparent rounded-2xl p-3',
                            'border-blue-300 bg-blue-50' => $hasSelectedSources,
                         ]); ?>"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-sm font-semibold text-gray-700">Джерела по категоріях</h2>
                            <button type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                    @click="openSources = !openSources">
                                <span x-text="openSources ? 'Згорнути' : 'Розгорнути'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openSources }" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div x-show="openSources" x-transition style="display: none;">
                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 items-start">
                                <?php $__currentLoopData = $sourceGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $groupSourceIds = collect($group['sources'])->pluck('id');
                                        $groupHasSelected = $groupSourceIds->intersect($selectedSources)->isNotEmpty();
                                    ?>
                                    <div x-data="{ open: <?php echo e($groupHasSelected ? 'true' : 'false'); ?> }"
                                         class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                            'border rounded-2xl overflow-hidden transition',
                                            'border-gray-200' => ! $groupHasSelected,
                                            'border-blue-400 shadow-sm bg-blue-50' => $groupHasSelected,
                                         ]); ?>"
                                    >
                                        <button type="button" class="w-full flex items-center justify-between px-4 py-2 bg-gray-50 text-left font-semibold text-gray-800"
                                            @click="open = !open">
                                            <span class="truncate"><?php echo e(ucfirst($group['category']->name)); ?> (ID: <?php echo e($group['category']->id); ?>)</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-transition style="display: none;" class="px-4 pb-4 pt-2">
                                            <div class="flex flex-wrap gap-2">
                                                <?php $__currentLoopData = $group['sources']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $sourceIsSelected = in_array($source->id, $selectedSources);
                                                    ?>
                                                    <label class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                        'flex items-start gap-2 px-3 py-1 rounded-full border text-sm transition text-left',
                                                        'border-gray-200 bg-white hover:border-blue-300' => ! $sourceIsSelected,
                                                        'border-blue-400 bg-blue-50 shadow-sm' => $sourceIsSelected,
                                                    ]); ?>">
                                                        <input type="checkbox" name="sources[]" value="<?php echo e($source->id); ?>"
                                                            <?php echo e($sourceIsSelected ? 'checked' : ''); ?>

                                                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                        <span class="whitespace-normal break-words"><?php echo e($source->name); ?> (ID: <?php echo e($source->id); ?>)</span>
                                                    </label>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(isset($levels) && count($levels)): ?>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 mb-3">Level</h2>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $levels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lvl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $levelId = 'level-' . md5($lvl); ?>
                                <div>
                                    <input type="checkbox" name="levels[]" value="<?php echo e($lvl); ?>" id="<?php echo e($levelId); ?>"
                                           class="hidden peer"
                                           <?php echo e(in_array($lvl, $selectedLevels ?? []) ? 'checked' : ''); ?>>
                                    <label for="<?php echo e($levelId); ?>" class="px-3 py-1 rounded-full border border-gray-200 cursor-pointer text-sm bg-gray-100 peer-checked:bg-blue-600 peer-checked:text-white">
                                        <?php echo e($lvl); ?>

                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($tagGroups->isNotEmpty()): ?>
                    <div x-data="{ openTags: <?php echo e($hasSelectedTags ? 'true' : 'false'); ?> }"
                         class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'space-y-3 border border-transparent rounded-2xl p-3',
                            'border-blue-300 bg-blue-50' => $hasSelectedTags,
                         ]); ?>"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-sm font-semibold text-gray-700">Tags</h2>
                            <button type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                    @click="openTags = !openTags">
                                <span x-text="openTags ? 'Згорнути' : 'Розгорнути'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openTags }" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-3" x-show="openTags" x-transition style="display: none;">
                            <?php $__currentLoopData = $tagGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tagCategory => $tags): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $categoryTagNames = collect($tags)->pluck('name');
                                    $tagCategoryHasSelected = $categoryTagNames->intersect($selectedTags)->isNotEmpty();
                                ?>
                                <div x-data="{ open: <?php echo e(($tagCategoryHasSelected || $loop->first) ? 'true' : 'false'); ?> }"
                                     class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'border rounded-2xl overflow-hidden transition',
                                        'border-gray-200' => ! $tagCategoryHasSelected,
                                        'border-blue-400 shadow-sm bg-blue-50' => $tagCategoryHasSelected,
                                     ]); ?>"
                                >
                                    <button type="button" class="w-full flex items-center justify-between px-4 py-2 bg-gray-50 text-left font-semibold text-gray-800"
                                            @click="open = !open">
                                        <span><?php echo e($tagCategory); ?></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition style="display: none;" class="px-4 pb-4 pt-2">
                                        <div class="flex flex-wrap gap-2">
                                            <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php $tagId = 'tag-' . md5($tag->id . '-' . $tag->name); ?>
                                                <div>
                                                    <input type="checkbox" name="tags[]" value="<?php echo e($tag->name); ?>" id="<?php echo e($tagId); ?>" class="hidden peer"
                                                           <?php echo e(in_array($tag->name, $selectedTags) ? 'checked' : ''); ?>>
                                                    <label for="<?php echo e($tagId); ?>" class="px-3 py-1 rounded-full border border-gray-200 cursor-pointer text-sm bg-gray-100 peer-checked:bg-blue-600 peer-checked:text-white">
                                                        <?php echo e($tag->name); ?>

                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            
                <div class="grid gap-4 sm:grid-cols-2">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Кількість питань</label>
                        <input type="number" min="1" max="<?php echo e($maxQuestions ?? $maxQuestions); ?>" name="num_questions"
                               value="<?php echo e($numQuestions ?? $maxQuestions); ?>" class="border rounded-lg px-3 py-2 w-full">
                        <?php if(isset($maxQuestions)): ?>
                            <p class="text-xs text-gray-500 mt-1">Доступно: <?php echo e($maxQuestions); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if(($canRandomizeFiltered ?? false) || !empty($randomizeFiltered)): ?>
                        <label class="flex items-start gap-3 p-3 border border-blue-200 rounded-2xl bg-blue-50">
                            <input type="checkbox" name="randomize_filtered" value="1"
                                   class="mt-1 h-5 w-5 text-blue-600 border-gray-300 rounded"
                                   <?php echo e(!empty($randomizeFiltered) ? 'checked' : ''); ?>>
                            <span>
                                <span class="block font-semibold">Рандомні питання по фільтру</span>
                                <span class="block text-xs text-gray-600">При генерації підбиратимуться випадкові питання,
                                    якщо їх більше за вказану кількість.</span>
                            </span>
                        </label>
                    <?php endif; ?>
                </div>

                <div class="grid gap-3 sm:grid-cols-2"
                     x-data="{
                        manual: <?php echo e(!empty($manualInput) ? 'true' : 'false'); ?>,
                        auto: <?php echo e(!empty($autocompleteInput) ? 'true' : 'false'); ?>,
                        checkOne: <?php echo e(!empty($checkOneInput) ? 'true' : 'false'); ?>,
                        builder: <?php echo e(!empty($builderInput) ? 'true' : 'false'); ?>

                     }"
                     x-init="if (!manual) { auto = false; builder = false; }"
                >
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl bg-gray-50">
                        <input type="checkbox" name="manual_input" value="1"
                               class="mt-1 h-5 w-5 text-blue-600 border-gray-300 rounded"
                               x-model="manual"
                               @change="if (!manual) { auto = false; builder = false; }"
                               <?php echo e(!empty($manualInput) ? 'checked' : ''); ?>>
                        <span>
                            <span class="block font-semibold">Ввести відповідь вручну</span>
                            <span class="block text-xs text-gray-500">Дає можливість вводити відповіді самостійно</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl bg-gray-50">
                        <input type="checkbox" name="autocomplete_input" value="1"
                               class="mt-1 h-5 w-5 text-green-600 border-gray-300 rounded"
                               x-model="auto"
                               :disabled="!manual"
                               <?php echo e(!empty($autocompleteInput) ? 'checked' : ''); ?>>
                        <span>
                            <span class="block font-semibold">Автозаповнення відповідей</span>
                            <span class="block text-xs text-gray-500">Підставляє правильні відповіді при ручному вводі</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl bg-gray-50">
                        <input type="checkbox" name="check_one_input" value="1"
                               class="mt-1 h-5 w-5 text-purple-600 border-gray-300 rounded"
                               x-model="checkOne"
                               <?php echo e(!empty($checkOneInput) ? 'checked' : ''); ?>>
                        <span>
                            <span class="block font-semibold">Перевіряти окремо</span>
                            <span class="block text-xs text-gray-500">Дозволяє перевіряти питання одне за одним</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl bg-gray-50">
                        <input type="checkbox" name="builder_input" value="1"
                               class="mt-1 h-5 w-5 text-emerald-600 border-gray-300 rounded"
                               x-model="builder"
                               :disabled="!manual"
                               <?php echo e(!empty($builderInput) ? 'checked' : ''); ?>>
                        <span>
                            <span class="block font-semibold">Вводити по словах</span>
                            <span class="block text-xs text-gray-500">Розбиває відповідь на окремі слова</span>
                        </span>
                    </label>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl">
                        <input type="checkbox" name="include_ai" value="1"
                               class="mt-1 h-5 w-5 text-yellow-600 border-gray-300 rounded"
                               <?php echo e(!empty($includeAi) ? 'checked' : ''); ?>>
                        <span>
                            <span class="block font-semibold">Додати AI-згенеровані питання</span>
                            <span class="block text-xs text-gray-500">Включає питання з прапорцем AI</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl">
                        <input type="checkbox" name="only_ai" value="1"
                               class="mt-1 h-5 w-5 text-orange-600 border-gray-300 rounded"
                               <?php echo e(!empty($onlyAi) ? 'checked' : ''); ?>>
                        <span>
                            <span class="block font-semibold">Тільки AI-згенеровані питання</span>
                            <span class="block text-xs text-gray-500">Обмежує вибір лише AI питаннями</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl">
                        <input type="checkbox" name="include_ai_v2" value="1"
                               class="mt-1 h-5 w-5 text-sky-600 border-gray-300 rounded"
                               <?php echo e(!empty($includeAiV2) ? 'checked' : ''); ?>>
                        <span>
                            <span class="block font-semibold">Додати AI (flag = 2)</span>
                            <span class="block text-xs text-gray-500">Додає питання з новим AI прапорцем</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl">
                        <input type="checkbox" name="only_ai_v2" value="1"
                               class="mt-1 h-5 w-5 text-cyan-600 border-gray-300 rounded"
                               <?php echo e(!empty($onlyAiV2) ? 'checked' : ''); ?>>
                        <span>
                            <span class="block font-semibold">Тільки AI (flag = 2)</span>
                            <span class="block text-xs text-gray-500">Залишає лише питання з прапорцем 2</span>
                        </span>
                    </label>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                    <button type="submit" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-2xl shadow font-semibold text-lg transition">
                        Згенерувати тест
                    </button>
                </div>
            </div>
       
    </form>

    <?php if(!empty($questions) && count($questions)): ?>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-sm text-gray-500">Кількість питань: <?php echo e(count($questions)); ?></div>
            <?php if(count($questions) > 1): ?>
                <button type="button" id="shuffle-questions"
                        class="inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-2xl shadow-sm text-sm font-semibold transition">
                    Перемішати питання
                </button>
            <?php endif; ?>
        </div>

        <form action="<?php echo e(route('grammar-test.check')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>
            <div id="questions-list" class="space-y-6">
                <?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="question-item" data-question-id="<?php echo e($q->id); ?>" data-question-save="<?php echo e($q->{$savePayloadKey}); ?>">
                        <input type="hidden" name="questions[<?php echo e($q->id); ?>]" value="1">
                        <div class="bg-white shadow rounded-2xl p-4 sm:p-6 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div class="text-sm font-semibold text-gray-700 flex flex-wrap items-center gap-2">
                                    <span class="uppercase px-2 py-1 rounded text-xs <?php echo e($q->category->name === 'past' ? 'bg-red-100 text-red-700' : ($q->category->name === 'present' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700')); ?>">
                                        <?php echo e(ucfirst($q->category->name)); ?>

                                    </span>
                                    <?php if($q->source): ?>
                                        <span class="text-xs text-gray-500">Source: <?php echo e($q->source->name); ?></span>
                                    <?php endif; ?>
                                    <?php if($q->flag): ?>
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded bg-yellow-200 text-yellow-800">AI</span>
                                    <?php endif; ?>
                                    <span class="text-xs text-gray-400">Складність: <?php echo e($q->difficulty); ?>/10</span>
                                    <span class="text-xs text-gray-400">Level: <?php echo e($q->level ?? 'N/A'); ?></span>
                                </div>
                                <span class="text-xs text-gray-400">ID: <?php echo e($q->id); ?> | UUID: <?php echo e($q->uuid ?? '—'); ?></span>
                            </div>
                            <div class="flex flex-wrap gap-2 items-baseline">
                                <span class="question-number font-bold mr-2"><?php echo e($loop->iteration); ?>.</span>
                                <?php preg_match_all('/\{a(\d+)\}/', $q->question, $matches); ?>
                                <?php echo $__env->make('components.question-input', [
                                    'question' => $q,
                                    'inputNamePrefix' => "question_{$q->id}_",
                                    'manualInput' => $manualInput,
                                    'autocompleteInput' => $autocompleteInput,
                                    'builderInput' => $builderInput,
                                    'autocompleteRoute' => $autocompleteRoute,
                                ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            </div>
                            <?php if($q->tags->count()): ?>
                                <div class="flex flex-wrap gap-1">
                                    <?php
                                        $colors = ['bg-blue-200 text-blue-800', 'bg-green-200 text-green-800', 'bg-red-200 text-red-800', 'bg-purple-200 text-purple-800', 'bg-pink-200 text-pink-800', 'bg-yellow-200 text-yellow-800', 'bg-indigo-200 text-indigo-800', 'bg-teal-200 text-teal-800'];
                                    ?>
                                    <?php $__currentLoopData = $q->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a href="<?php echo e(route('saved-tests.cards', ['tag' => $tag->name])); ?>" class="inline-flex px-2 py-0.5 rounded text-xs font-semibold hover:underline <?php echo e($colors[$loop->index % count($colors)]); ?>"><?php echo e($tag->name); ?></a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                            <?php if(!empty($checkOneInput)): ?>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="mt-1 bg-purple-600 text-white text-xs rounded px-3 py-1 hover:bg-purple-700"
                                        onclick="checkFullQuestionAjax(this, '<?php echo e($q->id); ?>', '<?php echo e(implode(',', array_map(function($n){return 'a'.$n;}, $matches[1]))); ?>')"
                                    >
                                        Check answer
                                    </button>
                                    <span class="text-xs font-bold" id="result-question-<?php echo e($q->id); ?>"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-2xl shadow font-semibold text-lg transition">
                    Перевірити
                </button>
            </div>
        </form>

        <div class="bg-white shadow rounded-2xl p-4 sm:p-6">
            <form action="<?php echo e($saveRoute); ?>" method="POST" class="flex flex-col sm:flex-row sm:items-center gap-3" id="save-test-form">
                <?php echo csrf_field(); ?>
                <?php
                    $filtersForSave = $normalizedFilters ?? [
                        'categories' => $selectedCategories,
                        'difficulty_from' => $difficultyFrom,
                        'difficulty_to' => $difficultyTo,
                        'num_questions' => $numQuestions,
                        'manual_input' => (bool) $manualInput,
                        'autocomplete_input' => (bool) $autocompleteInput,
                        'check_one_input' => (bool) $checkOneInput,
                        'builder_input' => (bool) $builderInput,
                        'include_ai' => (bool) ($includeAi ?? false),
                        'only_ai' => (bool) ($onlyAi ?? false),
                        'include_ai_v2' => (bool) ($includeAiV2 ?? false),
                        'only_ai_v2' => (bool) ($onlyAiV2 ?? false),
                        'levels' => $selectedLevels ?? [],
                        'tags' => $selectedTags,
                        'sources' => $selectedSources,
                        'randomize_filtered' => (bool) ($randomizeFiltered ?? false),
                    ];
                ?>
                <input type="hidden" name="filters" value="<?php echo e(htmlentities(json_encode($filtersForSave))); ?>">
                <input type="hidden" name="<?php echo e($savePayloadField); ?>" id="questions-order-input" value="<?php echo e(htmlentities(json_encode($questions->pluck($savePayloadKey)))); ?>">
                <input type="text" name="name" value="<?php echo e($autoTestName); ?>" placeholder="Назва тесту" required autocomplete="off"
                       class="border rounded-lg px-3 py-2 w-full sm:w-80">
                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="submit" name="save_mode" value="questions" class="inline-flex justify-center bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-2xl shadow font-semibold transition">
                        Зберегти тест
                    </button>
                    <button type="submit" name="save_mode" value="filters" class="inline-flex justify-center bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-6 py-2 rounded-2xl shadow font-semibold transition">
                        Зберегти фільтр
                    </button>
                </div>
            </form>
        </div>
    <?php elseif(isset($questions)): ?>
        <div class="text-red-600 font-bold text-lg">Питань по вибраних параметрах не знайдено!</div>
    <?php endif; ?>
</div>

<script>
function checkFullQuestionAjax(btn, questionId, markerList) {
    let markers = markerList.split(',');
    let answers = {};
    markers.forEach(function(marker) {
        let input = btn.closest('.bg-white').querySelector(`[name="question_${questionId}_${marker}"]`);
        answers[marker] = input ? input.value : '';
    });
    let resultSpan = document.getElementById(`result-question-${questionId}`);
    let empty = Object.values(answers).some(val => !val);
    if(empty) {
        resultSpan.textContent = 'Введіть всі відповіді';
        resultSpan.className = 'text-xs font-bold text-gray-500';
        return;
    }
    resultSpan.textContent = '...';
    resultSpan.className = 'text-xs font-bold text-gray-500';
    fetch("<?php echo e($checkOneRoute); ?>", {
        method: "POST",
        headers: {'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json'},
        body: JSON.stringify({
            question_id: questionId,
            answers: answers
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.result === 'correct') {
            resultSpan.textContent = '✔ Вірно';
            resultSpan.className = 'text-xs font-bold text-green-700';
        } else if(data.result === 'incorrect') {
            let corrects = Object.values(data.correct).join(', ');
            resultSpan.textContent = '✘ Невірно (правильно: ' + corrects + ')';
            resultSpan.className = 'text-xs font-bold text-red-700';
        } else {
            resultSpan.textContent = '—';
            resultSpan.className = 'text-xs font-bold text-gray-500';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('questions-list');
    const shuffleButton = document.getElementById('shuffle-questions');
    const orderInput = document.getElementById('questions-order-input');
    const saveForm = document.getElementById('save-test-form');

    if (!container) {
        return;
    }

    const getItems = () => Array.from(container.querySelectorAll('[data-question-id]'));

    const updateNumbers = () => {
        getItems().forEach((item, index) => {
            const numberEl = item.querySelector('.question-number');
            if (numberEl) {
                numberEl.textContent = `${index + 1}.`;
            }
        });
    };

    const updateOrderInput = () => {
        if (!orderInput) {
            return;
        }

        const order = getItems().map(item => item.dataset.questionSave);
        orderInput.value = JSON.stringify(order);
    };

    if (shuffleButton) {
        shuffleButton.addEventListener('click', () => {
            const items = getItems();

            if (items.length <= 1) {
                return;
            }

            for (let i = items.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [items[i], items[j]] = [items[j], items[i]];
            }

            items.forEach(item => container.appendChild(item));
            updateNumbers();
            updateOrderInput();
        });
    }

    if (saveForm) {
        saveForm.addEventListener('submit', updateOrderInput);
    }

    updateNumbers();
    updateOrderInput();
});

function builder(route, prefix) {
    const stored = [];
    for (let i = 0; ; i++) {
        const key = storageKey(`${prefix}${i}]`);
        const val = localStorage.getItem(key);
        if (val === null) break;
        stored.push(val);
    }
    if (stored.length === 0) stored.push('');
    return {
        words: stored,
        suggestions: stored.map(() => []),
        valid: stored.map(() => false),
        addWord() {
            this.words.push('');
            this.suggestions.push([]);
            this.valid.push(false);
        },
        removeWord() {
            if (this.words.length > 1) {
                this.words.pop();
                this.suggestions.pop();
                this.valid.pop();
            }
        },
        completeWord(index) {
            if (this.words[index].trim() !== '' && this.valid[index]) {
                if (index === this.words.length - 1) {
                    this.addWord();
                }
                this.$nextTick(() => {
                    const fields = this.$el.querySelectorAll(`input[name^="${prefix}"]`);
                    if (fields[index + 1]) {
                        fields[index + 1].focus();
                    }
                });
            }
        },
        fetchSuggestions(index) {
            const query = this.words[index];
            this.valid[index] = false;
            if (query.length === 0) {
                this.suggestions[index] = [];
                return;
            }
            fetch(route + '&q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    this.suggestions[index] = data.map(i => i.en);
                });
        },
        selectSuggestion(index, val) {
            this.words[index] = val;
            this.valid[index] = true;
            this.suggestions[index] = [];
        }
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pvital01/data/www/gramlyze.com/resources/views/grammar-test-v2.blade.php ENDPATH**/ ?>