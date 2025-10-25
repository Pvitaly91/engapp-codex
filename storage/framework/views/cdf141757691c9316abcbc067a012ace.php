

<?php $__env->startSection('title', __('Попередній перегляд сидера')); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800"><?php echo e(__('Попередній перегляд сидера')); ?></h1>
                    <p class="text-sm text-gray-500">
                        <?php echo e(__('Переконайтеся, що питання та повʼязані дані виглядають коректно, перш ніж запускати сидер.')); ?>

                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?php echo e(route('seed-runs.index')); ?>" class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition">
                        <i class="fa-solid fa-arrow-left"></i>
                        <?php echo e(__('Повернутися до списку')); ?>

                    </a>
                    <form method="POST" action="<?php echo e(route('seed-runs.run')); ?>" data-preloader>
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="class_name" value="<?php echo e($className); ?>">
                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-500 transition">
                            <i class="fa-solid fa-play"></i>
                            <?php echo e(__('Виконати сидер')); ?>

                        </button>
                    </form>
                </div>
            </div>

            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div>
                    <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs"><?php echo e(__('Клас сидера')); ?></dt>
                    <dd class="font-mono break-all"><?php echo e($className); ?></dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs"><?php echo e(__('Читабельна назва')); ?></dt>
                    <dd><?php echo e($displayClassName); ?></dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs"><?php echo e(__('Кількість питань у попередньому перегляді')); ?></dt>
                    <dd><?php echo e($questions->count()); ?></dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs"><?php echo e(__('Існуючих питань для цього сидера')); ?></dt>
                    <dd><?php echo e($existingQuestionCount === null ? '—' : $existingQuestionCount); ?></dd>
                </div>
            </dl>

            <?php if(!is_null($existingQuestionCount) && $existingQuestionCount > 0): ?>
                <div class="mt-4 rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-800">
                    <?php echo e(__('Деякі питання вже існують у базі даних для цього сидера. Попередній перегляд показує лише нові записи, які будуть створені.')); ?>

                </div>
            <?php endif; ?>
        </div>

        <?php if($questions->isEmpty()): ?>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-gray-500">
                    <?php echo e(__('Немає питань для попереднього перегляду. Сидер, можливо, вже виконувався або не повертає даних.')); ?>

                </p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white shadow rounded-lg p-6 space-y-4" data-question-preview>
                        <div class="space-y-1">
                            <h2 class="text-lg font-semibold text-gray-800"><?php echo $question['highlighted_text']; ?></h2>
                            <p class="text-xs text-gray-500 font-mono break-all">UUID: <?php echo e($question['uuid']); ?></p>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide"><?php echo e(__('Правильні відповіді')); ?></h3>
                            <?php
                                $filledAnswers = $question['answers']->filter(fn ($answer) => filled($answer['label']));
                            ?>
                            <?php if($filledAnswers->isEmpty()): ?>
                                <p class="mt-2 text-sm text-gray-500"><?php echo e(__('Коректні відповіді відсутні.')); ?></p>
                            <?php else: ?>
                                <ul class="mt-2 space-y-1">
                                    <?php $__currentLoopData = $filledAnswers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="flex items-center gap-2">
                                            <span class="font-mono text-xs text-gray-500"><?php echo e($answer['marker']); ?></span>
                                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-xs font-medium"><?php echo e($answer['label']); ?></span>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-3">
                            <div class="border border-slate-200 rounded-lg" data-preview-section>
                                <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                    <span><?php echo e(__('Деталі питання')); ?></span>
                                    <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div class="hidden border-t border-slate-200 px-3 py-3 text-sm text-slate-700 space-y-3" data-preview-section-content>
                                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide"><?php echo e(__('Категорія')); ?></dt>
                                            <dd><?php echo e($question['category'] ?? __('Без категорії')); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide"><?php echo e(__('Джерело')); ?></dt>
                                            <dd><?php echo e($question['source'] ?? __('Без джерела')); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide"><?php echo e(__('Рівень')); ?></dt>
                                            <dd><?php echo e($question['level'] ?? __('Невідомо')); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide"><?php echo e(__('Складність')); ?></dt>
                                            <dd><?php echo e($question['difficulty']); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide"><?php echo e(__('Прапор')); ?></dt>
                                            <dd><?php echo e($question['flag']); ?></dd>
                                        </div>
                                    </dl>

                                    <?php if($question['tags']->isNotEmpty()): ?>
                                        <div>
                                            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide"><?php echo e(__('Теги')); ?></h4>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <?php $__currentLoopData = $question['tags']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs">
                                                        <?php echo e($tag['name']); ?>

                                                        <?php if(!empty($tag['category'])): ?>
                                                            <span class="ml-2 text-[10px] uppercase tracking-wide text-indigo-500"><?php echo e($tag['category']); ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div>
                                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide"><?php echo e(__('Текст питання без підсвічування')); ?></h4>
                                        <p class="mt-2 text-slate-700 whitespace-pre-line"><?php echo e($question['raw_text']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <?php if($question['options']->isNotEmpty()): ?>
                                <div class="border border-slate-200 rounded-lg" data-preview-section>
                                    <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                        <span><?php echo e(__('Варіанти відповідей')); ?></span>
                                        <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="hidden border-t border-slate-200 px-3 py-3" data-preview-section-content>
                                        <div class="flex flex-wrap gap-2">
                                            <?php $__currentLoopData = $question['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-xs font-medium"><?php echo e($option); ?></span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if($question['verb_hints']->isNotEmpty()): ?>
                                <div class="border border-slate-200 rounded-lg" data-preview-section>
                                    <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                        <span><?php echo e(__('Підказки дієслів')); ?></span>
                                        <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="hidden border-t border-slate-200 px-3 py-3" data-preview-section-content>
                                        <ul class="space-y-1 text-sm text-gray-600">
                                            <?php $__currentLoopData = $question['verb_hints']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li><span class="font-mono text-xs text-gray-500"><?php echo e($hint['marker']); ?></span> — <?php echo e($hint['label']); ?></li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if($question['variants']->isNotEmpty()): ?>
                                <div class="border border-slate-200 rounded-lg" data-preview-section>
                                    <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                        <span><?php echo e(__('Варіанти формулювань')); ?></span>
                                        <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="hidden border-t border-slate-200 px-3 py-3" data-preview-section-content>
                                        <ul class="list-disc list-inside space-y-1 text-sm text-gray-600">
                                            <?php $__currentLoopData = $question['variants']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li><?php echo e($variant); ?></li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if($question['hints']->isNotEmpty()): ?>
                                <div class="border border-slate-200 rounded-lg" data-preview-section>
                                    <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                        <span><?php echo e(__('Підказки')); ?></span>
                                        <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="hidden border-t border-slate-200 px-3 py-3 space-y-2" data-preview-section-content>
                                        <?php $__currentLoopData = $question['hints']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="rounded bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-gray-600 space-y-1">
                                                <div class="flex items-center justify-between text-xs text-slate-500">
                                                    <span><?php echo e($hint['provider'] ?? __('Невідомий провайдер')); ?></span>
                                                    <span><?php echo e(strtoupper($hint['locale'] ?? '–')); ?></span>
                                                </div>
                                                <p class="whitespace-pre-line"><?php echo e($hint['text']); ?></p>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if($question['explanations']->isNotEmpty()): ?>
                                <div class="border border-slate-200 rounded-lg" data-preview-section>
                                    <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                        <span><?php echo e(__('Пояснення ChatGPT')); ?></span>
                                        <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="hidden border-t border-slate-200 px-3 py-3 space-y-2" data-preview-section-content>
                                        <?php $__currentLoopData = $question['explanations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $explanation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="rounded bg-purple-50 border border-purple-200 px-3 py-2 text-sm text-purple-800 space-y-1">
                                                <div class="text-xs text-purple-600 font-semibold"><?php echo e(__('Неправильна відповідь:')); ?> <?php echo e($explanation['wrong_answer']); ?></div>
                                                <p class="whitespace-pre-line"><?php echo e($explanation['text']); ?></p>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('click', function (event) {
            const toggle = event.target.closest('[data-preview-section-toggle]');

            if (!toggle) {
                return;
            }

            const section = toggle.closest('[data-preview-section]');

            if (!section) {
                return;
            }

            const content = section.querySelector('[data-preview-section-content]');

            if (!content) {
                return;
            }

            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            const icon = toggle.querySelector('[data-preview-section-icon]');

            if (isExpanded) {
                toggle.setAttribute('aria-expanded', 'false');
                content.classList.add('hidden');

                if (icon) {
                    icon.classList.remove('rotate-180');
                }
            } else {
                toggle.setAttribute('aria-expanded', 'true');
                content.classList.remove('hidden');

                if (icon) {
                    icon.classList.add('rotate-180');
                }
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/seed-runs/preview.blade.php ENDPATH**/ ?>