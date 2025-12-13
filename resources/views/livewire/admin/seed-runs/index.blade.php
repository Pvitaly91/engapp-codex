@section('title', 'Seed Runs (V2)')

<div class="max-w-6xl mx-auto space-y-6" x-data="{ confirmAction(message, callback) { if(window.confirm(message)) { callback() } } }">
    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Seed Runs (V2)</h1>
                <p class="text-sm text-gray-500">Livewire версія без перезавантаження сторінки.</p>
            </div>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    x-on:click.prevent="confirmAction('Виконати всі невиконані сидери?', () => $wire.runMissing())"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-500 transition disabled:opacity-50"
                    wire:loading.attr="disabled"
                >
                    <i class="fa-solid fa-play"></i>
                    Виконати всі невиконані
                </button>
                <button
                    type="button"
                    wire:click="refreshOverview"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition"
                    wire:loading.attr="disabled"
                >
                    <i class="fa-solid fa-rotate"></i>
                    Оновити
                </button>
            </div>
        </div>

        @if($statusMessage)
            <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-green-700">
                {{ $statusMessage }}
            </div>
        @endif

        @if($errorMessage)
            <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-red-700">
                {{ $errorMessage }}
            </div>
        @endif

        @unless(($overview['tableExists'] ?? false))
            <div class="rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-yellow-800">
                Таблиця <code class="font-mono">seed_runs</code> ще не створена. Запустіть міграції, щоб продовжити.
            </div>
        @endunless
    </div>

    @if($overview['tableExists'] ?? false)
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="bg-white shadow rounded-lg p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">Невиконані сидери</h2>
                    <span class="text-sm text-gray-500">Кількість: {{ collect($overview['pendingSeeders'] ?? [])->count() }}</span>
                </div>

                @if(empty($overview['pendingSeeders']))
                    <p class="text-sm text-gray-500">Усі сидери вже виконані.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($overview['pendingSeeders'] as $pending)
                            <li class="py-3 flex flex-col gap-2">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $pending['display_class_name'] ?? $pending['class_name'] }}</p>
                                        @if(!empty($pending['display_class_namespace']))
                                            <p class="text-xs text-gray-500">{{ $pending['display_class_namespace'] }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-md hover:bg-emerald-500 transition"
                                            x-on:click.prevent="confirmAction('Запустити сидер?', () => $wire.runSeeder('{{ $pending['class_name'] }}'))"
                                            wire:loading.attr="disabled"
                                        >
                                            <i class="fa-solid fa-play"></i>
                                            Run
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-500 transition"
                                            x-on:click.prevent="confirmAction('Позначити як виконаний?', () => $wire.markExecuted('{{ $pending['class_name'] }}'))"
                                            wire:loading.attr="disabled"
                                        >
                                            <i class="fa-solid fa-check"></i>
                                            Mark
                                        </button>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="bg-white shadow rounded-lg p-6 space-y-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Виконані сидери</h2>
                        <p class="text-sm text-gray-500">Кількість: {{ $this->filteredExecuted->count() }}</p>
                    </div>
                    <div class="relative w-full lg:max-w-xs">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-sm"></i>
                        </span>
                        <input
                            type="search"
                            wire:model.debounce.300ms="search"
                            placeholder="Пошук сидера…"
                            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>

                @if($this->filteredExecuted->isEmpty())
                    <p class="text-sm text-gray-500">Поки що немає виконаних сидерів.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($this->filteredExecuted as $seedRun)
                            <li class="py-3 flex flex-col gap-1">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $seedRun['display_class_name'] ?? $seedRun['class_name'] }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $seedRun['ran_at'] ?? '—' }}
                                            @if(isset($overview['recentSeedRunOrdinals'][$seedRun['id'] ?? null]))
                                                • #{{ $overview['recentSeedRunOrdinals'][$seedRun['id']] }}
                                            @endif
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-md"
                                        wire:click="runSeeder('{{ $seedRun['class_name'] }}')"
                                        wire:loading.attr="disabled"
                                    >
                                        <i class="fa-solid fa-rotate-right"></i>
                                        Повторити
                                    </button>
                                </div>
                                @if(!empty($seedRun['data_profile']))
                                    @php
                                        $dataProfile = is_array($seedRun['data_profile'])
                                            ? implode(' ', array_filter($seedRun['data_profile']))
                                            : $seedRun['data_profile'];
                                    @endphp
                                    <p class="text-xs text-gray-600">{{ $dataProfile }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif

    <div
        wire:loading.flex
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm items-center justify-center"
    >
        <div class="bg-white rounded-lg shadow-lg px-6 py-4 flex items-center gap-3 text-sm text-gray-700">
            <span class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span>
            <span>Виконується операція…</span>
        </div>
    </div>
</div>
