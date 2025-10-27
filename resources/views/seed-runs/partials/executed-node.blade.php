@php
    $indent = max(0, $depth) * 1.5;
    $recentSeedRunOrdinals = collect($recentSeedRunOrdinals ?? []);
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
                    data-load-url="{{ route('seed-runs.folders.children') }}"
                    aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                     class="h-4 w-4 text-slate-500 transition-transform -rotate-90" data-folder-icon>
                    <path fill-rule="evenodd"
                          d="M6.22 4.47a.75.75 0 011.06 0l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 01-1.06-1.06L10.69 10 6.22 5.53a.75.75 0 010-1.06z"
                          clip-rule="evenodd" />
                </svg>
                <i class="fa-solid fa-folder-tree text-slate-500"></i>
                <span>{{ $node['name'] }}</span>
                <span class="text-xs font-normal text-slate-500">({{ $node['seeder_count'] ?? 0 }})</span>
            </button>
        </div>

        <div class="space-y-3 hidden" data-folder-children data-depth="{{ $depth + 1 }}">
            @if(!empty($folderSeedRunIds))
                <div class="flex flex-col sm:flex-row sm:justify-end gap-2 sm:items-center" data-folder-actions>
                    <form method="POST"
                          action="{{ route('seed-runs.folders.destroy-with-questions') }}"
                          data-preloader
                          data-confirm="{{ __($folderDeleteConfirm, ['folder' => $folderLabel]) }}"
                          class="w-full sm:w-auto">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="folder_label" value="{{ $folderLabel }}">
                        @foreach($folderSeedRunIds as $seedRunId)
                            <input type="hidden" name="seed_run_ids[]" value="{{ $seedRunId }}">
                        @endforeach
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-md hover:bg-amber-500 transition">
                            <i class="fa-solid fa-broom"></i>
                            {{ $folderDeleteButton }}
                        </button>
                    </form>
                    <form method="POST"
                          action="{{ route('seed-runs.folders.destroy') }}"
                          data-preloader
                          data-confirm="Видалити записи про виконання для папки «{{ e($folderLabel) }}»?"
                          class="w-full sm:w-auto">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="folder_label" value="{{ $folderLabel }}">
                        @foreach($folderSeedRunIds as $seedRunId)
                            <input type="hidden" name="seed_run_ids[]" value="{{ $seedRunId }}">
                        @endforeach
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition">
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
        $questionCount = (int) ($seedRun->question_count ?? 0);
        $executedCheckboxId = 'executed-seeder-' . $seedRun->id;
        $deleteQuestionsCheckboxId = 'executed-delete-questions-' . $seedRun->id;
        $deleteFileConfirm = __('Видалити файл сидера «:class»?', ['class' => $seedRun->display_class_name]);
        $deleteFileConfirmWithQuestions = __('Видалити файл сидера «:class» та пов’язані питання?', ['class' => $seedRun->display_class_name]);
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
                                        {{ $node['name'] }}
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

                                <p class="text-xs text-gray-500 mt-2 {{ $questionCount > 0 ? 'hidden' : '' }}" data-no-questions-message data-seed-run-id="{{ $seedRun->id }}">
                                    Питання відсутні.
                                </p>

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

                                <div class="mt-3 space-y-3" data-seeder-section data-seed-run-id="{{ $seedRun->id }}">
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
                                        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap md:flex-col md:items-end" data-seeder-actions>
                                            <div class="flex flex-col sm:flex-row sm:flex-wrap md:flex-col md:items-end gap-2 w-full">
                                                <button type="button"
                                                        class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-md hover:bg-indigo-200 transition"
                                                        data-seeder-file-open
                                                        data-class-name="{{ $seedRun->class_name }}"
                                                        data-display-name="{{ $seedRun->display_class_name }}">
                                                    <i class="fa-solid fa-file-code"></i>
                                                    Код
                                                </button>
                                                <form method="POST"
                                                      action="{{ route('seed-runs.destroy-seeder-file') }}"
                                                      data-preloader
                                                      data-confirm="{{ $deleteFileConfirm }}"
                                                      data-confirm-regular="{{ $deleteFileConfirm }}"
                                                      data-confirm-with-questions="{{ $deleteFileConfirmWithQuestions }}"
                                                      data-delete-with-questions-form
                                                      data-class-name="{{ $seedRun->class_name }}"
                                                      class="flex-1 sm:flex-none md:flex-1 md:w-full"
                                                      id="executed-delete-file-form-{{ $seedRun->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="class_name" value="{{ $seedRun->class_name }}">
                                                    <input type="hidden" name="delete_with_questions" value="0" data-delete-with-questions-input data-class-name="{{ $seedRun->class_name }}">
                                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition">
                                                        <i class="fa-solid fa-file-circle-xmark"></i>
                                                        Видалити файл
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('seed-runs.destroy-with-questions', $seedRun->id) }}" data-preloader data-confirm="{{ __($seederDeleteConfirm) }}" class="flex-1 sm:flex-none md:flex-1 md:w-full">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-md hover:bg-amber-500 transition">
                                                        <i class="fa-solid fa-broom"></i>
                                                        {{ $seederDeleteButton }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('seed-runs.destroy', $seedRun->id) }}" data-preloader data-confirm="Видалити лише запис про виконання?" class="flex-1 sm:flex-none md:flex-1 md:w-full">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition">
                                                        <i class="fa-solid fa-trash"></i>
                                                        Видалити запис
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="text-xs text-gray-500 text-center sm:text-left md:text-right w-full md:w-auto">
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
    </div>
@endif
