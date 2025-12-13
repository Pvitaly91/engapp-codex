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
            @if($tableExists && count($pendingSeederHierarchy) > 0)
                <button 
                    type="button" 
                    wire:click="confirmRunMissing"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-500 transition"
                >
                    <i class="fa-solid fa-play"></i>
                    Виконати всі невиконані
                </button>
            @endif
        </div>

        {{-- Status Messages --}}
        @if($statusMessage)
            <div 
                class="mb-4 rounded-md border px-4 py-3 text-sm flex items-center justify-between
                    {{ $statusType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700' }}"
            >
                <span>{{ $statusMessage }}</span>
                <button type="button" wire:click="clearStatus" class="text-current opacity-60 hover:opacity-100">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @unless($tableExists)
            <div class="rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-yellow-800">
                Таблиця <code class="font-mono">seed_runs</code> ще не створена. Запустіть міграції, щоб продовжити.
            </div>
        @endunless
    </div>

    @if($tableExists)
        <div class="space-y-6">
            {{-- Pending Seeders --}}
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Невиконані сидери</h2>
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
                    <div class="space-y-3" x-data="{ expandedFolders: {} }">
                        @foreach($pendingSeederHierarchy as $node)
                            @include('livewire.admin.seed-runs.partials.pending-node', ['node' => $node, 'depth' => 0])
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Executed Seeders --}}
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Виконані сидери</h2>
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
                    <div class="space-y-4" x-data="{ expandedFolders: {}, expandedSeeders: {} }">
                        @foreach($executedSeederHierarchy as $node)
                            @include('livewire.admin.seed-runs.partials.executed-node', ['node' => $node, 'depth' => 0])
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Confirmation Modal --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center" x-data x-init="$el.querySelector('[data-confirm-accept]')?.focus()">
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
                    <button type="button" wire:click="executeConfirmedAction" data-confirm-accept class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-500 transition">
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
