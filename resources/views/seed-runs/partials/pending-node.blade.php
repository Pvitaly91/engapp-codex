@php
    $indent = max(0, $depth) * 1.5;
@endphp

@if(($node['type'] ?? null) === 'folder')
    <div class="space-y-2" style="margin-left: {{ $indent }}rem;" data-pending-folder data-folder-path="{{ $node['path'] }}" data-folder-name="{{ $node['name'] }}" data-seeder-count="{{ $node['seeder_count'] ?? 0 }}">
        <button type="button"
                class="flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900 transition"
                data-pending-folder-toggle
                data-folder-path="{{ $node['path'] }}"
                aria-expanded="true">
            <i class="fa-solid fa-folder-tree text-slate-500"></i>
            <span data-folder-name>{{ $node['name'] }}</span>
            <span class="text-xs font-normal text-slate-500" data-folder-count>({{ $node['seeder_count'] ?? 0 }})</span>
        </button>

        <div class="space-y-3" data-pending-folder-children data-depth="{{ $depth + 1 }}">
            @foreach($node['children'] as $child)
                @include('seed-runs.partials.pending-node', [
                    'node' => $child,
                    'depth' => $depth + 1,
                ])
            @endforeach
        </div>
    </div>
@elseif(($node['type'] ?? null) === 'seeder')
    @php
        $pendingSeeder = $node['pending_seeder'];
        $pendingCheckboxId = 'pending-seeder-' . md5($pendingSeeder->class_name ?? $node['name']);
        $pendingActionsId = $pendingCheckboxId . '-actions';
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between" style="margin-left: {{ $indent }}rem;" data-pending-seeder data-class-name="{{ $pendingSeeder->class_name }}">
        <div class="flex items-center gap-3 sm:flex-1">
            <input type="checkbox"
                   id="{{ $pendingCheckboxId }}"
                   name="class_names[]"
                   value="{{ $pendingSeeder->class_name }}"
                   form="pending-bulk-delete-form"
                   class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                   data-bulk-delete-checkbox
                   data-bulk-scope="pending">
            <label for="{{ $pendingCheckboxId }}" class="inline-flex text-sm font-mono text-gray-700 break-all cursor-pointer min-w-[12rem] sm:min-w-[15rem]">
                @if(!empty($pendingSeeder->display_class_namespace))
                    <span class="text-gray-500">{{ $pendingSeeder->display_class_namespace }}</span>
                    <span class="text-gray-400">\</span>
                @endif
                <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-semibold">
                    {{ $pendingSeeder->display_class_basename }}
                </span>
            </label>
        </div>
        <div class="sm:hidden">
            <button type="button"
                    class="inline-flex items-center justify-between gap-2 w-full px-3 py-1.5 bg-slate-100 text-slate-700 text-xs font-medium rounded-md hover:bg-slate-200 transition"
                    data-pending-actions-toggle
                    data-target="{{ $pendingActionsId }}"
                    aria-expanded="false"
                    aria-controls="{{ $pendingActionsId }}">
                <span data-toggle-label-collapsed>Показати дії</span>
                <span data-toggle-label-expanded class="hidden">Сховати дії</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" data-pending-actions-icon></i>
            </button>
        </div>
        <div id="{{ $pendingActionsId }}"
             class="hidden w-full sm:w-auto sm:block"
             data-pending-actions>
            <div class="flex flex-col gap-2 w-full sm:flex-row sm:flex-wrap sm:items-center">
                <button type="button"
                        class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-md hover:bg-indigo-200 transition w-full sm:w-auto"
                        data-seeder-file-open
                        data-class-name="{{ $pendingSeeder->class_name }}"
                        data-display-name="{{ $pendingSeeder->display_class_name }}">
                    <i class="fa-solid fa-file-code"></i>
                    Код
                </button>
                @if($pendingSeeder->supports_preview)
                    <a href="{{ route('seed-runs.preview', ['class_name' => $pendingSeeder->class_name]) }}" class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-sky-100 text-sky-700 text-xs font-medium rounded-md hover:bg-sky-200 transition w-full sm:w-auto">
                        <i class="fa-solid fa-eye"></i>
                        Переглянути
                    </a>
                @endif
                <form method="POST" action="{{ route('seed-runs.destroy-seeder-file') }}" data-preloader data-confirm="Видалити файл сидера «{{ e($pendingSeeder->display_class_name) }}»?" class="flex w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="class_name" value="{{ $pendingSeeder->class_name }}">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition w-full sm:w-auto">
                        <i class="fa-solid fa-file-circle-xmark"></i>
                        Видалити файл
                    </button>
                </form>
                <form method="POST" action="{{ route('seed-runs.mark-executed') }}" data-preloader class="flex w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="class_name" value="{{ $pendingSeeder->class_name }}">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-amber-500 text-white text-xs font-medium rounded-md hover:bg-amber-400 transition w-full sm:w-auto">
                        <i class="fa-solid fa-check"></i>
                        Позначити виконаним
                    </button>
                </form>
                <form method="POST" action="{{ route('seed-runs.run') }}" data-preloader class="flex w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="class_name" value="{{ $pendingSeeder->class_name }}">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-500 transition w-full sm:w-auto">
                        <i class="fa-solid fa-play"></i>
                        Виконати
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif
