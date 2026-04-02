@php
    $indent = $depth > 0 ? 1.5 : 0;
    $recentSeedRunOrdinals = collect($recentSeedRunOrdinals ?? []);
    $isCategorySeeder = false;
@endphp

@if(($node['type'] ?? null) === 'folder')
    @php
        $folderLabel = $node['path'] ?? $node['name'];
        $folderSeedRunIds = $node['seed_run_ids'] ?? [];
        $folderProfile = $node['folder_profile'] ?? [];
        $folderDeleteButton = $folderProfile['delete_button'] ?? __('Видалити з даними');
        $folderDeleteConfirm = $folderProfile['delete_confirm'] ?? __('Видалити всі сидери в папці «:folder» та пов’язані дані?');
    @endphp
    <div class="space-y-3" style="margin-left: {{ $indent }}rem;" data-folder-node data-folder-path="{{ $node['path'] }}" data-depth="{{ $depth }}">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <button type="button"
                    class="flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900 transition"
                    data-folder-toggle
                    data-folder-path="{{ $node['path'] }}"
                    data-folder-tab="{{ $activeSeederTab ?? 'main' }}"
                    data-load-url="{{ route('seed-runs.folders.children') }}"
                    aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                     class="h-4 w-4 text-slate-500 transition-transform -rotate-90" data-folder-icon>
                    <path fill-rule="evenodd"
                          d="M6.22 4.47a.75.75 0 011.06 0l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 01-1.06-1.06L10.69 10 6.22 5.53a.75.75 0 010-1.06z"
                          clip-rule="evenodd" />
                </svg>
                <i class="fa-solid fa-folder-tree text-slate-500"></i>
                <span data-folder-name>{{ $node['name'] }}</span>
                <span class="text-xs font-normal text-slate-500">({{ $node['seeder_count'] ?? 0 }})</span>
            </button>
        </div>

        <div class="space-y-3 hidden" data-folder-children data-depth="{{ $depth + 1 }}">
            @if(!empty($folderSeedRunIds))
                <div class="flex flex-col sm:flex-row sm:flex-wrap sm:justify-end gap-2 sm:items-center w-full lg:ml-auto xl:flex-nowrap" data-folder-actions>
                    <form method="POST"
                          action="{{ route('seed-runs.folders.destroy-with-questions', ['tab' => $activeSeederTab ?? 'main']) }}"
                          data-preloader
                          data-confirm="{{ __($folderDeleteConfirm, ['folder' => $folderLabel]) }}"
                          class="w-full sm:w-auto lg:w-auto">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="folder_label" value="{{ $folderLabel }}">
                        @foreach($folderSeedRunIds as $seedRunId)
                            <input type="hidden" name="seed_run_ids[]" value="{{ $seedRunId }}">
                        @endforeach
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-md hover:bg-amber-500 transition">
                            <i class="fa-solid fa-broom"></i>
                            {{ $folderDeleteButton }}
                        </button>
                    </form>
                    <form method="POST"
                          action="{{ route('seed-runs.folders.destroy', ['tab' => $activeSeederTab ?? 'main']) }}"
                          data-preloader
                          data-confirm="Видалити записи про виконання для папки «{{ e($folderLabel) }}»?"
                          class="w-full sm:w-auto lg:w-auto">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="folder_label" value="{{ $folderLabel }}">
                        @foreach($folderSeedRunIds as $seedRunId)
                            <input type="hidden" name="seed_run_ids[]" value="{{ $seedRunId }}">
                        @endforeach
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition">
                            <i class="fa-solid fa-trash"></i>
                            Видалити записи
                        </button>
                    </form>
                </div>
            @endif

            <div class="space-y-3" data-folder-children-content></div>
        </div>
    </div>
@elseif(($node['type'] ?? null) === 'seeder')
    @php
        $seedRun = $node['seed_run'];
        $dataProfile = $node['data_profile'] ?? ($seedRun->data_profile ?? []);
        $seederDeleteButton = $dataProfile['delete_button'] ?? __('Видалити з даними');
        $seederDeleteConfirm = $dataProfile['delete_confirm'] ?? __('Видалити лог та пов’язані дані?');
        $seedRunOrdinal = $recentSeedRunOrdinals->get($seedRun->id);
        $seedRunIsRecent = !is_null($seedRunOrdinal);
        $isLocalizationSeeder = in_array(($dataProfile['type'] ?? null), ['question_localizations', 'page_localizations'], true);
        $relatedLocalizations = collect($seedRun->related_localizations ?? []);
        $relatedLocalizationPreview = $relatedLocalizations->take(3);
        $relatedLocalizationOverflow = max(0, $relatedLocalizations->count() - $relatedLocalizationPreview->count());
        $pendingLocalizations = collect($seedRun->pending_localizations ?? []);
        $pendingLocalizationPreview = $pendingLocalizations->take(3);
        $pendingLocalizationOverflow = max(0, $pendingLocalizations->count() - $pendingLocalizationPreview->count());
        $questionCount = (int) ($seedRun->question_count ?? 0);
        $executedCheckboxId = 'executed-seeder-' . $seedRun->id;
        $executedLocalizationsId = 'executed-seeder-localizations-' . $seedRun->id;
        $pendingLocalizationsId = 'executed-seeder-pending-localizations-' . $seedRun->id;
        $deleteQuestionsCheckboxId = 'executed-delete-questions-' . $seedRun->id;
        $deleteFileConfirm = __('Видалити файл сидера «:class»?', ['class' => $seedRun->display_class_name]);
        $deleteFileConfirmWithQuestions = __('Видалити файл сидера «:class» та пов’язані питання?', ['class' => $seedRun->display_class_name]);
        $isCategorySeeder = \Illuminate\Support\Str::contains(
            $seedRun->display_class_basename ?? $seedRun->display_class_name ?? '',
            'Category'
        );
        $theoryTarget = $seedRun->theory_target ?? null;
        $promptTheoryTarget = $seedRun->prompt_theory_target ?? null;
        $testTarget = $seedRun->test_target ?? null;
        $executedLabelClasses = $isLocalizationSeeder
            ? 'inline-flex items-center px-2 py-0.5 rounded bg-sky-100 text-sky-800 font-semibold ring-1 ring-sky-200'
            : ($isCategorySeeder
            ? 'inline-flex items-center px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold ring-1 ring-emerald-200'
            : '');
    @endphp
    <div style="margin-left: {{ $indent }}rem;" data-seeder-node data-seed-run-id="{{ $seedRun->id }}" data-depth="{{ $depth }}">
        <div @class([
            'border rounded-xl shadow-sm',
            'border-gray-200' => ! $seedRunIsRecent,
            'border-amber-300' => $seedRunIsRecent,
        ])>
            <div @class([
                'p-4 md:p-6',
                'bg-white' => ! $seedRunIsRecent,
                'bg-amber-50' => $seedRunIsRecent,
            ])>
                <div class="text-xs text-gray-700 break-words">
                        <div class="flex items-start gap-3">
                            <div class="pt-1 shrink-0">
                                <input type="checkbox"
                                       id="{{ $executedCheckboxId }}"
                                       name="class_names[]"
                                       value="{{ $seedRun->class_name }}"
                                       form="executed-bulk-delete-form"
                                       class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                                       data-bulk-delete-checkbox
                                       data-bulk-scope="executed">
                            </div>
                            <div class="flex-1">
                                <div class="font-mono text-sm text-gray-800 flex flex-wrap items-center gap-2">
                                    <label for="{{ $executedCheckboxId }}" class="cursor-pointer">
                                        <span data-seeder-name class="{{ $executedLabelClasses }}">{{ $node['name'] }}</span>
                                    </label>
                                    @if($seedRunIsRecent)
                                        <span class="text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                            Новий{{ ' #' . $seedRunOrdinal }}
                                        </span>
                                    @endif
                                </div>
                                @if(\Illuminate\Support\Str::contains($seedRun->display_class_name, '\\'))
                                    <p class="text-xs text-gray-500 mt-1">{{ $seedRun->display_class_name }}</p>
                                @endif

                                @if($promptTheoryTarget)
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                            {{ $promptTheoryTarget['label'] ?? 'Пов’язана сторінка теорії' }}
                                        </span>
                                        <a href="{{ $promptTheoryTarget['url'] }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="text-sm font-medium text-emerald-700 underline decoration-emerald-300 underline-offset-2 hover:text-emerald-900 break-all">
                                            {{ $promptTheoryTarget['title'] ?? $promptTheoryTarget['url'] }}
                                        </a>
                                    </div>
                                @endif

                                @if($pendingLocalizations->isNotEmpty())
                                    <div class="mt-2 flex flex-col gap-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="button"
                                                    class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100"
                                                    data-localizations-toggle
                                                    data-target="{{ $pendingLocalizationsId }}"
                                                    aria-expanded="false"
                                                    aria-controls="{{ $pendingLocalizationsId }}">
                                                <i class="fa-solid fa-chevron-right text-[10px] transition-transform" data-localizations-toggle-icon></i>
                                                <span data-toggle-label-collapsed>Показати невиконані локалізації ({{ $pendingLocalizations->count() }})</span>
                                                <span data-toggle-label-expanded class="hidden">Сховати невиконані локалізації ({{ $pendingLocalizations->count() }})</span>
                                            </button>

                                            @foreach($pendingLocalizationPreview as $localization)
                                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800">
                                                    {{ $localization['locale_label'] ?? 'N/A' }}
                                                </span>
                                            @endforeach

                                            @if($pendingLocalizationOverflow > 0)
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                                                    +{{ $pendingLocalizationOverflow }}
                                                </span>
                                            @endif
                                        </div>

                                        <div id="{{ $pendingLocalizationsId }}"
                                             class="hidden rounded-lg border border-emerald-100 bg-emerald-50/70 px-3 py-2 space-y-2"
                                             data-localizations-panel>
                                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                <p class="text-[11px] font-medium text-emerald-800">
                                                    Знайдено {{ $pendingLocalizations->count() }} невиконаних локалізацій для цього сидера.
                                                </p>
                                                <form method="POST"
                                                      action="{{ route('seed-runs.folders.run', ['tab' => $activeSeederTab ?? 'main']) }}"
                                                      data-preloader
                                                      data-reload-after-success="true"
                                                      data-confirm="Виконати невиконані локалізації для сидера «{{ e($seedRun->display_class_name) }}»?"
                                                      class="w-full sm:w-auto">
                                                    @csrf
                                                    <input type="hidden" name="folder_label" value="{{ __('Локалізації для :seeder', ['seeder' => $seedRun->display_class_name]) }}">
                                                    @foreach($pendingLocalizations as $localization)
                                                        <input type="hidden" name="class_names[]" value="{{ $localization['class_name'] ?? '' }}">
                                                    @endforeach
                                                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-medium text-white transition hover:bg-emerald-500">
                                                        <i class="fa-solid fa-play"></i>
                                                        Виконати локалізації
                                                    </button>
                                                </form>
                                            </div>

                                            @foreach($pendingLocalizations as $localization)
                                                @php
                                                    $pendingLocalizationDisplayBasename = $localization['display_basename']
                                                        ?? class_basename($localization['class_name'] ?? ($localization['display_name'] ?? ''));
                                                    $pendingLocalizationDisplayName = $localization['display_name'] ?? ($localization['class_name'] ?? '');
                                                @endphp
                                                <div class="rounded-md border border-emerald-100 bg-white/90 px-3 py-2">
                                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 font-semibold text-emerald-800">
                                                            {{ $localization['locale_label'] ?? 'N/A' }}
                                                        </span>
                                                        <span class="font-medium text-slate-700 break-all">
                                                            {{ $pendingLocalizationDisplayBasename }}
                                                        </span>
                                                        <span class="text-slate-500">
                                                            {{ $localization['type_label'] ?? 'Локалізація' }}
                                                        </span>
                                                    </div>
                                                    @if($pendingLocalizationDisplayName !== '' && $pendingLocalizationDisplayName !== $pendingLocalizationDisplayBasename)
                                                        <p class="mt-1 break-all text-[11px] text-slate-500">
                                                            {{ $pendingLocalizationDisplayName }}
                                                        </p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($relatedLocalizations->isNotEmpty())
                                    <div class="mt-2 flex flex-col gap-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="button"
                                                    class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700 transition hover:bg-sky-100"
                                                    data-localizations-toggle
                                                    data-target="{{ $executedLocalizationsId }}"
                                                    aria-expanded="false"
                                                    aria-controls="{{ $executedLocalizationsId }}">
                                                <i class="fa-solid fa-chevron-right text-[10px] transition-transform" data-localizations-toggle-icon></i>
                                                <span data-toggle-label-collapsed>Показати виконані локалізації ({{ $relatedLocalizations->count() }})</span>
                                                <span data-toggle-label-expanded class="hidden">Сховати виконані локалізації ({{ $relatedLocalizations->count() }})</span>
                                            </button>

                                            @foreach($relatedLocalizationPreview as $localization)
                                                <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-800">
                                                    {{ $localization['locale_label'] ?? 'N/A' }}
                                                </span>
                                            @endforeach

                                            @if($relatedLocalizationOverflow > 0)
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                                                    +{{ $relatedLocalizationOverflow }}
                                                </span>
                                            @endif
                                        </div>

                                        <div id="{{ $executedLocalizationsId }}"
                                             class="hidden rounded-lg border border-sky-100 bg-sky-50/70 px-3 py-2 space-y-2"
                                             data-localizations-panel>
                                            @foreach($relatedLocalizations as $localization)
                                                @php
                                                    $localizationDisplayBasename = $localization['display_basename']
                                                        ?? class_basename($localization['class_name'] ?? ($localization['display_name'] ?? ''));
                                                    $localizationDisplayName = $localization['display_name'] ?? ($localization['class_name'] ?? '');
                                                @endphp
                                                <div class="rounded-md border border-sky-100 bg-white/90 px-3 py-2">
                                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                                        <div class="min-w-0">
                                                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                                                <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 font-semibold text-sky-800">
                                                                    {{ $localization['locale_label'] ?? 'N/A' }}
                                                                </span>
                                                                <span class="font-medium text-slate-700 break-all">
                                                                    {{ $localizationDisplayBasename }}
                                                                </span>
                                                                <span class="text-slate-500">
                                                                    {{ $localization['type_label'] ?? 'Локалізація' }}
                                                                </span>
                                                            </div>
                                                            @if($localizationDisplayName !== '' && $localizationDisplayName !== $localizationDisplayBasename)
                                                                <p class="mt-1 break-all text-[11px] text-slate-500">
                                                                    {{ $localizationDisplayName }}
                                                                </p>
                                                            @endif
                                                            @if(!empty($localization['ran_at']))
                                                                <p class="mt-1 text-[11px] text-slate-500">
                                                                    Останній запуск: {{ $localization['ran_at'] }}
                                                                </p>
                                                            @endif
                                                        </div>

                                                        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                                                            <form method="POST"
                                                                  action="{{ route('seed-runs.refresh', ['seedRun' => $localization['seed_run_id'], 'tab' => $activeSeederTab ?? 'main']) }}"
                                                                  data-preloader
                                                                  data-reload-after-success="true"
                                                                  data-confirm="Оновити дані локалізації «{{ e($localizationDisplayName) }}»?"
                                                                  class="w-full sm:w-auto">
                                                                @csrf
                                                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-md bg-blue-600 px-2.5 py-1.5 text-[11px] font-medium text-white transition hover:bg-blue-500">
                                                                    <i class="fa-solid fa-rotate"></i>
                                                                    Оновити
                                                                </button>
                                                            </form>

                                                            <form method="POST"
                                                                  action="{{ route('seed-runs.destroy-with-questions', ['seedRun' => $localization['seed_run_id'], 'tab' => $activeSeederTab ?? 'main']) }}"
                                                                  data-preloader
                                                                  data-reload-after-success="true"
                                                                  data-confirm="Видалити локалізацію «{{ e($localizationDisplayName) }}» з БД?"
                                                                  class="w-full sm:w-auto">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-md bg-red-600 px-2.5 py-1.5 text-[11px] font-medium text-white transition hover:bg-red-500">
                                                                    <i class="fa-solid fa-database"></i>
                                                                    Видалити з БД
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <p class="text-xs text-gray-500 mt-2 {{ $questionCount > 0 ? 'hidden' : '' }}" data-no-questions-message data-seed-run-id="{{ $seedRun->id }}">
                                    {{ $isLocalizationSeeder ? 'Локалізації доступні через попередній перегляд.' : 'Питання відсутні.' }}
                                </p>

                                @unless($isLocalizationSeeder)
                                    <div class="mt-2 flex items-center gap-2 text-[11px] text-red-700">
                                        <input type="checkbox"
                                               id="{{ $deleteQuestionsCheckboxId }}"
                                               name="delete_with_questions[]"
                                               value="{{ $seedRun->class_name }}"
                                               form="executed-bulk-delete-form"
                                               class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                                               data-delete-with-questions-toggle
                                               data-class-name="{{ $seedRun->class_name }}"
                                               data-bulk-scope="executed">
                                        <label for="{{ $deleteQuestionsCheckboxId }}" class="cursor-pointer select-none">
                                            Видаляти також питання цього сидера
                                        </label>
                                    </div>
                                @endunless

                                <div class="mt-3 space-y-3" data-seeder-section data-seed-run-id="{{ $seedRun->id }}">
                                    @if($testTarget)
                                        <a href="{{ $testTarget['url'] }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           title="{{ $testTarget['title'] ?? ($testTarget['label'] ?? 'Готовий тест') }}"
                                           class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-sky-100 text-sky-700 text-xs font-medium rounded-md hover:bg-sky-200 transition w-full sm:w-auto">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            {{ $testTarget['label'] ?? 'Готовий тест' }}
                                        </a>
                                    @endif

                                    @if($questionCount > 0)
                                        <button type="button"
                                                class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1.5 rounded-full bg-blue-50 hover:bg-blue-100 transition w-full sm:w-auto"
                                                data-seeder-toggle
                                                data-seed-run-id="{{ $seedRun->id }}"
                                                data-load-url="{{ route('seed-runs.seeders.categories', $seedRun->id) }}"
                                                data-loaded="false"
                                                aria-expanded="false">
                                            <span data-toggle-label-collapsed>
                                                Показати питання (
                                                <span class="font-semibold" data-seed-run-question-count data-seed-run-id="{{ $seedRun->id }}">{{ $questionCount }}</span>
                                                )
                                            </span>
                                            <span class="hidden" data-toggle-label-expanded>
                                                Сховати питання (
                                                <span class="font-semibold" data-seed-run-question-count data-seed-run-id="{{ $seedRun->id }}">{{ $questionCount }}</span>
                                                )
                                            </span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" data-seeder-toggle-icon viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @endif

                                    <div class="{{ $questionCount > 0 ? 'hidden ' : '' }}space-y-4"
                                         data-seeder-content
                                             data-seed-run-id="{{ $seedRun->id }}">
                                        <div class="flex flex-col gap-2 w-full sm:flex-row sm:flex-wrap lg:items-start lg:justify-between lg:gap-3 xl:flex-nowrap" data-seeder-actions>
                                            <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 w-full sm:w-auto lg:flex-1 lg:min-w-0 xl:flex-nowrap xl:items-center">
                                                @if($theoryTarget)
                                                    <a href="{{ $theoryTarget['url'] }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       title="{{ $theoryTarget['title'] ?? $theoryTarget['label'] }}"
                                                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-emerald-100 text-emerald-700 text-xs font-medium rounded-md hover:bg-emerald-200 transition">
                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                        {{ $theoryTarget['label'] }}
                                                    </a>
                                                @endif
                                                <a href="{{ route('seed-runs.preview', ['class_name' => $seedRun->class_name]) }}"
                                                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-purple-100 text-purple-700 text-xs font-medium rounded-md hover:bg-purple-200 transition">
                                                    <i class="fa-solid fa-eye"></i>
                                                    Попередній перегляд
                                                </a>
                                                <button type="button"
                                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-md hover:bg-indigo-200 transition"
                                                        data-seeder-file-open
                                                        data-class-name="{{ $seedRun->class_name }}"
                                                        data-display-name="{{ $seedRun->display_class_name }}">
                                                    <i class="fa-solid fa-file-code"></i>
                                                    Код
                                                </button>
                                                <form method="POST"
                                                      action="{{ route('seed-runs.destroy-seeder-file', ['tab' => $activeSeederTab ?? 'main']) }}"
                                                      data-preloader
                                                      data-confirm="{{ $deleteFileConfirm }}"
                                                      data-confirm-regular="{{ $deleteFileConfirm }}"
                                                      data-confirm-with-questions="{{ $deleteFileConfirmWithQuestions }}"
                                                      data-delete-with-questions-form
                                                      data-class-name="{{ $seedRun->class_name }}"
                                                      class="w-full sm:w-auto lg:w-auto"
                                                      id="executed-delete-file-form-{{ $seedRun->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="class_name" value="{{ $seedRun->class_name }}">
                                                    <input type="hidden" name="delete_with_questions" value="0" data-delete-with-questions-input data-class-name="{{ $seedRun->class_name }}">
                                                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition">
                                                        <i class="fa-solid fa-file-circle-xmark"></i>
                                                        Видалити файл
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('seed-runs.refresh', ['seedRun' => $seedRun->id, 'tab' => $activeSeederTab ?? 'main']) }}" data-preloader data-confirm="Оновити дані сидера «{{ e($seedRun->display_class_name) }}»? Всі поточні дані будуть видалені та створені заново." class="w-full sm:w-auto lg:w-auto">
                                                    @csrf
                                                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-500 transition">
                                                        <i class="fa-solid fa-rotate"></i>
                                                        Оновити дані
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('seed-runs.destroy-with-questions', ['seedRun' => $seedRun->id, 'tab' => $activeSeederTab ?? 'main']) }}" data-preloader data-confirm="{{ __($seederDeleteConfirm) }}" class="w-full sm:w-auto lg:w-auto">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-md hover:bg-amber-500 transition">
                                                        <i class="fa-solid fa-broom"></i>
                                                        {{ $seederDeleteButton }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('seed-runs.destroy', ['seedRun' => $seedRun->id, 'tab' => $activeSeederTab ?? 'main']) }}" data-preloader data-confirm="Видалити лише запис про виконання?" class="w-full sm:w-auto lg:w-auto">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition">
                                                        <i class="fa-solid fa-trash"></i>
                                                        Видалити запис
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="text-xs text-gray-500 text-center sm:text-left lg:text-right w-full lg:w-auto">
                                                <span class="font-semibold text-gray-700 block md:hidden">Виконано:</span>
                                                <span>{{ optional($seedRun->ran_at)->format('Y-m-d H:i:s') }}</span>
                                            </div>
                                        </div>

                                        <div class="space-y-4"
                                             data-seeder-questions-container
                                             data-seed-run-id="{{ $seedRun->id }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endif
