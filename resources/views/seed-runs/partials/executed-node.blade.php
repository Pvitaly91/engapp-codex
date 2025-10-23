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
    <div x-data="{ open: false }" class="space-y-3" style="margin-left: {{ $indent }}rem;">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <button type="button"
                    class="flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900 transition"
                    @click="open = !open">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                     class="h-4 w-4 text-slate-500 transition-transform" :class="open ? 'rotate-0' : '-rotate-90'">
                    <path fill-rule="evenodd"
                          d="M6.22 4.47a.75.75 0 011.06 0l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 01-1.06-1.06L10.69 10 6.22 5.53a.75.75 0 010-1.06z"
                          clip-rule="evenodd" />
                </svg>
                <i class="fa-solid fa-folder-tree text-slate-500"></i>
                <span>{{ $node['name'] }}</span>
                <span class="text-xs font-normal text-slate-500">({{ $node['seeder_count'] ?? 0 }})</span>
            </button>

            @if(!empty($folderSeedRunIds))
                <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
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
        </div>

        <div x-show="open" x-transition style="display: none;" x-cloak class="space-y-3">
            @foreach($node['children'] as $child)
                @include('seed-runs.partials.executed-node', [
                    'node' => $child,
                    'depth' => $depth + 1,
                    'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
                ])
            @endforeach
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
    @endphp
    <div style="margin-left: {{ $indent }}rem;">
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
                <div class="md:grid md:grid-cols-[minmax(0,3fr)_minmax(0,1fr)] md:items-start md:gap-6">
                    <div class="text-xs text-gray-700 break-words">
                        <div class="font-mono text-sm text-gray-800 flex flex-wrap items-center gap-2">{{ $node['name'] }}
                            @if($seedRunIsRecent)
                                <span class="text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                    Новий{{ ' #' . $seedRunOrdinal }}
                                </span>
                            @endif
                        </div>
                        @if(\Illuminate\Support\Str::contains($seedRun->display_class_name, '\\'))
                            <p class="text-xs text-gray-500 mt-1">{{ $seedRun->display_class_name }}</p>
                        @endif

                        <p class="text-xs text-gray-500 mt-2 {{ $seedRun->question_count > 0 ? 'hidden' : '' }}" data-no-questions-message data-seed-run-id="{{ $seedRun->id }}">
                            Питання відсутні.
                        </p>

                        @if($seedRun->question_count > 0)
                            <div x-data="{ open: false }" class="mt-3 space-y-3" data-seed-run-question-wrapper data-seed-run-id="{{ $seedRun->id }}">
                                <button type="button"
                                        class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1.5 rounded-full bg-blue-50 hover:bg-blue-100 transition w-full sm:w-auto"
                                        @click="open = !open">
                                    <span x-show="!open" x-cloak>
                                        Показати питання (
                                        <span class="font-semibold" data-seed-run-question-count data-seed-run-id="{{ $seedRun->id }}">{{ $seedRun->question_count }}</span>
                                        )
                                    </span>
                                    <span x-show="open" x-cloak>
                                        Сховати питання (
                                        <span class="font-semibold" data-seed-run-question-count data-seed-run-id="{{ $seedRun->id }}">{{ $seedRun->question_count }}</span>
                                        )
                                    </span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition style="display: none;" class="space-y-4">
                                    @foreach($seedRun->question_groups as $categoryIndex => $categoryGroup)
                                        <div x-data="{ openCategory: true }" class="border border-slate-200 rounded-xl overflow-hidden" data-category-wrapper data-seed-run-id="{{ $seedRun->id }}" data-category-index="{{ $categoryIndex }}">
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 bg-slate-100">
                                                <div class="space-y-1">
                                                    <h3 class="text-sm font-semibold text-gray-800">{{ $categoryGroup['display_name'] }}</h3>
                                                    <p class="text-xs text-gray-500">
                                                        @if($categoryGroup['category'])
                                                            Категорія ID: {{ $categoryGroup['category']['id'] }}
                                                        @else
                                                            Категорія не вказана
                                                        @endif
                                                        · Питань:
                                                        <span class="font-semibold" data-category-question-count data-seed-run-id="{{ $seedRun->id }}" data-category-index="{{ $categoryIndex }}">
                                                            {{ $categoryGroup['question_count'] }}
                                                        </span>
                                                    </p>
                                                </div>
                                                <button type="button"
                                                        class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1.5 rounded-full bg-blue-50 hover:bg-blue-100 transition w-full sm:w-auto"
                                                        @click="openCategory = !openCategory">
                                                    <span x-text="openCategory ? 'Сховати' : 'Показати'"></span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openCategory }" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div x-show="openCategory" x-transition style="display: none;" class="space-y-3 px-4 pb-4 pt-3 bg-white">
                                                @foreach($categoryGroup['sources'] as $sourceIndex => $sourceGroup)
                                                    <div x-data="{ openSource: true }" class="border border-slate-200 rounded-lg overflow-hidden" data-source-wrapper data-seed-run-id="{{ $seedRun->id }}" data-category-index="{{ $categoryIndex }}" data-source-index="{{ $sourceIndex }}">
                                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-3 py-2 bg-slate-50">
                                                            <div class="space-y-1">
                                                                <h4 class="text-sm font-semibold text-slate-700">{{ $sourceGroup['display_name'] }}</h4>
                                                                <p class="text-xs text-slate-500">
                                                                    @if($sourceGroup['source'])
                                                                        Джерело ID: {{ $sourceGroup['source']['id'] }}
                                                                    @else
                                                                        Джерело не вказане
                                                                    @endif
                                                                    · Питань:
                                                                    <span class="font-semibold" data-source-question-count data-seed-run-id="{{ $seedRun->id }}" data-category-index="{{ $categoryIndex }}" data-source-index="{{ $sourceIndex }}">
                                                                        {{ $sourceGroup['questions']->count() }}
                                                                    </span>
                                                                </p>
                                                            </div>
                                                            <button type="button"
                                                                    class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1.5 rounded-full bg-blue-50 hover:bg-blue-100 transition w-full sm:w-auto"
                                                                    @click="openSource = !openSource">
                                                                <span x-text="openSource ? 'Сховати' : 'Показати'"></span>
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openSource }" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                        <div x-show="openSource" x-transition style="display: none;" class="space-y-2 px-3 pb-3 pt-2 bg-white">
                                                            @foreach($sourceGroup['questions'] as $question)
                                                                <div class="border border-slate-200 rounded-lg bg-slate-50 px-3 py-2 text-left text-sm leading-relaxed" data-question-container data-question-id="{{ $question['id'] }}" data-seed-run-id="{{ $seedRun->id }}" data-category-index="{{ $categoryIndex }}" data-source-index="{{ $sourceIndex }}">
                                                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                                                        <div class="text-gray-800 space-y-1">{!! $question['highlighted_text'] !!}</div>
                                                                        <form method="POST"
                                                                              action="{{ route('seed-runs.questions.destroy', $question['id']) }}"
                                                                              data-question-delete-form
                                                                              data-confirm="Видалити це питання?"
                                                                              data-question-id="{{ $question['id'] }}"
                                                                              data-seed-run-id="{{ $seedRun->id }}"
                                                                              data-category-index="{{ $categoryIndex }}"
                                                                              data-source-index="{{ $sourceIndex }}">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-red-700 px-2.5 py-1 rounded-full bg-red-50 hover:bg-red-100 transition w-full sm:w-auto">
                                                                                <i class="fa-solid fa-trash-can"></i>
                                                                                Видалити
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 md:mt-0 flex flex-col sm:flex-row md:flex-col md:items-end gap-2 text-sm text-gray-600">
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
                        <div class="text-xs text-gray-500 text-center sm:text-left md:text-right">
                            <span class="font-semibold text-gray-700 block md:hidden">Виконано:</span>
                            <span>{{ optional($seedRun->ran_at)->format('Y-m-d H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
