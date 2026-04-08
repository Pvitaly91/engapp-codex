<div class="max-w-6xl mx-auto space-y-6">
    {{-- Loading Overlay --}}
    <div wire:loading.flex class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg px-6 py-4 flex items-center gap-3 text-sm text-gray-700">
            <span class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span>
            <span>Виконується операція…</span>
        </div>
    </div>

    {{-- Header --}}
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Seed Runs (V2)</h1>
                <p class="text-sm text-gray-500">Керуйте виконаними та невиконаними сидарами (Livewire версія).</p>
            </div>
            @if($tableExists && $activeSeederTab !== 'theory-tests' && count($pendingSeederHierarchy) > 0)
                <button 
                    type="button" 
                    wire:click="confirmRunMissing"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-500 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    @disabled($runnablePendingCount === 0)
                >
                    <i class="fa-solid fa-play"></i>
                    {{ $activeSeederTab === 'localizations' ? 'Виконати доступні локалізації' : 'Виконати всі невиконані' }}
                </button>
            @endif
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            @php
                $mainCount = (int) data_get($seederTabCounts, 'main.total', 0);
                $localizationsCount = (int) data_get($seederTabCounts, 'localizations.total', 0);
                $theoryTestsCount = (int) data_get($seederTabCounts, 'theory-tests.total', 0);
            @endphp
            <button
                type="button"
                wire:click="setActiveSeederTab('main')"
                @class([
                    'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition',
                    'bg-slate-900 text-white shadow-sm' => $activeSeederTab === 'main',
                    'bg-slate-100 text-slate-700 hover:bg-slate-200' => $activeSeederTab !== 'main',
                ])
            >
                <span>Основні сидери</span>
                <span class="rounded-full bg-white/15 px-2 py-0.5 text-xs">{{ $mainCount }}</span>
            </button>
            <button
                type="button"
                wire:click="setActiveSeederTab('localizations')"
                @class([
                    'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition',
                    'bg-sky-600 text-white shadow-sm' => $activeSeederTab === 'localizations',
                    'bg-slate-100 text-slate-700 hover:bg-slate-200' => $activeSeederTab !== 'localizations',
                ])
            >
                <span>Локалізації</span>
                <span class="rounded-full bg-white/15 px-2 py-0.5 text-xs">{{ $localizationsCount }}</span>
            </button>
            <button
                type="button"
                wire:click="setActiveSeederTab('theory-tests')"
                @class([
                    'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition',
                    'bg-emerald-600 text-white shadow-sm' => $activeSeederTab === 'theory-tests',
                    'bg-slate-100 text-slate-700 hover:bg-slate-200' => $activeSeederTab !== 'theory-tests',
                ])
            >
                <span>Теорія з тестами</span>
                <span class="rounded-full bg-white/15 px-2 py-0.5 text-xs">{{ $theoryTestsCount }}</span>
            </button>
        </div>

        {{-- Status Messages --}}
        @if($statusMessage || !empty($statusLinks) || !empty($statusErrors))
            <div 
                class="mb-4 rounded-md border px-4 py-3 text-sm
                    {{ $statusType === 'success'
                        ? 'bg-emerald-50 border-emerald-200 text-emerald-700'
                        : ($statusType === 'warning'
                            ? 'bg-amber-50 border-amber-200 text-amber-800'
                            : 'bg-red-50 border-red-200 text-red-700') }}"
                data-feedback-container
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1 space-y-3" data-feedback-copy-content>
                        @if($statusMessage)
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide">
                                    {{ $statusType === 'error' ? 'Не виконано' : ($statusType === 'warning' ? 'Частково виконано' : 'Виконано') }}
                                </p>
                                <p class="mt-1 whitespace-pre-line">{{ $statusMessage }}</p>
                            </div>
                        @endif

                        @if(!empty($statusErrors))
                            <div class="rounded-md border border-red-200 bg-white/80 px-3 py-2 text-red-700">
                                <p class="text-[11px] font-semibold uppercase tracking-wide">Помилки</p>
                                <ul class="mt-2 list-disc space-y-1 pl-4">
                                    @foreach($statusErrors as $statusError)
                                        <li class="whitespace-pre-line">{{ $statusError }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($statusLinks))
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($statusLinks as $link)
                                    <a href="{{ $link['url'] ?? '#' }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       title="{{ $link['title'] ?? ($link['label'] ?? 'Тест') }}"
                                       class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-100 transition">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        {{ $link['title'] ?? ($link['label'] ?? 'Готовий тест') }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button"
                                class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1.5 text-xs font-semibold text-current ring-1 ring-current/10 transition hover:bg-white"
                                data-copy-feedback
                                title="Скопіювати текст повідомлення">
                            <i class="fa-regular fa-copy"></i>
                            <span data-copy-feedback-label>Скопіювати</span>
                        </button>
                        <button type="button" wire:click="clearStatus" class="shrink-0 text-current opacity-60 hover:opacity-100">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @once
            <script>
                if (!window.__seedRunsFeedbackCopyBound) {
                    window.__seedRunsFeedbackCopyBound = true;

                    const extractFeedbackText = function (container) {
                        if (!container) {
                            return '';
                        }

                        const source = container.querySelector('[data-feedback-copy-content]') || container;

                        return String(source.innerText || source.textContent || '')
                            .replace(/\n{3,}/g, '\n\n')
                            .trim();
                    };

                    const copyTextToClipboard = async function (text) {
                        if (!text) {
                            return false;
                        }

                        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                            await navigator.clipboard.writeText(text);

                            return true;
                        }

                        const textarea = document.createElement('textarea');
                        textarea.value = text;
                        textarea.setAttribute('readonly', 'readonly');
                        textarea.style.position = 'fixed';
                        textarea.style.opacity = '0';
                        document.body.appendChild(textarea);
                        textarea.select();

                        const copied = document.execCommand('copy');
                        document.body.removeChild(textarea);

                        return copied;
                    };

                    const updateCopyButtonLabel = function (button, label) {
                        if (!button) {
                            return;
                        }

                        const labelNode = button.querySelector('[data-copy-feedback-label]');

                        if (!labelNode) {
                            return;
                        }

                        if (!labelNode.dataset.defaultLabel) {
                            labelNode.dataset.defaultLabel = labelNode.textContent || 'Скопіювати';
                        }

                        labelNode.textContent = label;

                        window.clearTimeout(Number(button.dataset.copyFeedbackTimeout || '0'));
                        button.dataset.copyFeedbackTimeout = String(window.setTimeout(function () {
                            labelNode.textContent = labelNode.dataset.defaultLabel || 'Скопіювати';
                        }, 1800));
                    };

                    document.addEventListener('click', async function (event) {
                        const button = event.target.closest('[data-copy-feedback]');

                        if (!button) {
                            return;
                        }

                        const container = button.closest('[data-feedback-container]');

                        if (!container) {
                            return;
                        }

                        event.preventDefault();

                        const text = extractFeedbackText(container);

                        if (!text) {
                            updateCopyButtonLabel(button, 'Порожньо');

                            return;
                        }

                        try {
                            await copyTextToClipboard(text);
                            updateCopyButtonLabel(button, 'Скопійовано');
                        } catch (error) {
                            updateCopyButtonLabel(button, 'Помилка');
                        }
                    });
                }
            </script>
        @endonce

        @unless($tableExists)
            <div class="rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-yellow-800">
                Таблиця <code class="font-mono">seed_runs</code> ще не створена. Запустіть міграції, щоб продовжити.
            </div>
        @endunless
    </div>

    @if($tableExists)
        <div class="space-y-6">
            @if($activeSeederTab === 'theory-tests')
                <div class="bg-white shadow rounded-lg p-6 overflow-hidden">
                    <div class="mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">Сторінки теорії з пов’язаними сидарами</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Показані сторінки теорії, які прив’язані до сидерів через Prompt Generator. Для кожної сторінки видно виконані та невиконані сидери, а також доступні готові тести.
                        </p>
                    </div>

                    @if(empty($theoryTestPages))
                        <p class="text-sm text-gray-500">Поки що не знайдено сторінок теорії, пов’язаних із сидарами через Prompt Generator.</p>
                    @else
                        <div class="space-y-4">
                            @include('seed-runs.partials.theory-test-pages', ['theoryTestPages' => $theoryTestPages])
                        </div>
                    @endif
                </div>
            @else
                {{-- Pending Seeders --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">
                            {{ $activeSeederTab === 'localizations' ? 'Невиконані локалізації' : 'Невиконані сидери' }}
                        </h2>
                        <button 
                            type="button"
                            wire:click="openCreateModal"
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-500 transition"
                        >
                            <i class="fa-solid fa-plus"></i>
                            Створити сидер
                        </button>
                    </div>

                    @if(empty($pendingSeederHierarchy))
                        <p class="text-sm text-gray-500">Усі сидери вже виконані.</p>
                    @else
                        @if($runnablePendingCount === 0 && $activeSeederTab === 'localizations')
                            <div class="mb-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                Усі локалізації в цій вкладці заблоковані. Спочатку виконайте їхні основні сидери.
                            </div>
                        @endif
                        <div
                            class="space-y-3"
                            x-data="{ expandedFolders: {} }"
                        >
                            @foreach($pendingSeederHierarchy as $node)
                                @include('seed-runs-v2::livewire.partials.pending-node', ['node' => $node, 'depth' => 0])
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Executed Seeders --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">
                            {{ $activeSeederTab === 'localizations' ? 'Виконані локалізації' : 'Виконані сидери' }}
                        </h2>
                        <div class="relative w-full sm:max-w-xs">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-sm"></i>
                            </span>
                            <input 
                                type="search"
                                wire:model.live.debounce.300ms="searchQuery"
                                class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Пошук сидера…"
                            >
                        </div>
                    </div>

                    @if(empty($executedSeederHierarchy))
                        <p class="text-sm text-gray-500">Поки що немає виконаних сидерів.</p>
                    @else
                        <div
                            class="space-y-4"
                            x-data="{ expandedFolders: {}, expandedSeeders: {} }"
                        >
                            @foreach($executedSeederHierarchy as $node)
                                @include('seed-runs-v2::livewire.partials.executed-node', ['node' => $node, 'depth' => 0])
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Confirmation Modal --}}
    @if($showConfirmModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center"
            wire:loading.class="hidden"
            wire:target="executeConfirmedAction"
            x-data
            x-init="$el.querySelector('[data-confirm-accept]')?.focus()"
        >
            <div class="absolute inset-0 bg-slate-900/50" wire:click="cancelConfirm"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full mx-4 p-6 space-y-4">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold text-gray-800">Підтвердження</h2>
                    <p class="text-sm text-gray-600">{{ $confirmMessage }}</p>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" wire:click="cancelConfirm" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition">
                        Скасувати
                    </button>
                    <button
                        type="button"
                        wire:click="executeConfirmedAction"
                        data-confirm-accept
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-500 transition"
                    >
                        Підтвердити
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- File Edit Modal --}}
    @if($showFileModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeFileModal"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 flex flex-col overflow-hidden max-h-[90vh]">
                <div class="px-6 py-4 border-b border-slate-200 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-800">Редагування файлу — {{ $fileModalDisplayName }}</h2>
                        <p class="text-xs text-slate-500 mt-1 break-words">{{ $fileModalPath }}</p>
                        @if($fileModalLastModified)
                            <p class="text-[11px] text-slate-400 mt-1">Останнє оновлення: {{ $fileModalLastModified }}</p>
                        @endif
                    </div>
                    <button type="button" wire:click="closeFileModal" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 flex-1 overflow-auto">
                    <textarea 
                        wire:model="fileModalContents"
                        class="w-full h-96 font-mono text-sm text-slate-800 border border-slate-200 rounded-lg p-4 focus:border-blue-500 focus:ring-blue-500 resize-y"
                        spellcheck="false"
                    ></textarea>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-3">
                    <button type="button" wire:click="closeFileModal" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-100 transition">
                        Скасувати
                    </button>
                    <button type="button" wire:click="saveFile" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-500 transition">
                        Зберегти файл
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Create Seeder Modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeCreateModal"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 flex flex-col overflow-hidden max-h-[90vh]">
                <div class="px-6 py-4 border-b border-slate-200 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-800">Створити новий сидер</h2>
                        <p class="text-xs text-slate-500 mt-1">Вкажіть назву класу та PHP код сидера</p>
                    </div>
                    <button type="button" wire:click="closeCreateModal" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Назва класу <span class="text-red-500">*</span></label>
                            <input 
                                type="text"
                                wire:model="newSeederClassName"
                                class="w-full font-mono text-sm text-slate-800 border border-slate-200 rounded-lg px-4 py-2 focus:border-emerald-500 focus:ring-emerald-500"
                                placeholder="MyNewSeeder"
                            >
                            <p class="mt-1 text-xs text-slate-500">Має починатися з великої літери (наприклад: MyNewSeeder)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Папка (необов'язково)</label>
                            <select 
                                wire:model="newSeederFolder"
                                class="w-full font-mono text-sm text-slate-800 border border-slate-200 rounded-lg px-4 py-2 focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option value="">Коренева папка (database/seeders)</option>
                                @foreach($seederFolders as $folder)
                                    <option value="{{ $folder }}">{{ $folder }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">PHP код сидера <span class="text-red-500">*</span></label>
                        <textarea 
                            wire:model="newSeederContents"
                            class="w-full h-80 font-mono text-sm text-slate-800 border border-slate-200 rounded-lg p-4 focus:border-emerald-500 focus:ring-emerald-500 resize-y"
                            spellcheck="false"
                        ></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-3">
                    <button type="button" wire:click="closeCreateModal" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-100 transition">
                        Скасувати
                    </button>
                    <button type="button" wire:click="createSeeder" class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-md hover:bg-emerald-500 transition">
                        Створити сидер
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
