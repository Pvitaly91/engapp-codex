@php
    $columnValue = old('column', $block->column);
    $bodyFieldId = 'block-body-editor';
@endphp

@once
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closetag.min.js" referrerpolicy="no-referrer"></script>
@endonce

<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">{{ $heading }}</h1>
            <p class="text-sm text-gray-500">{{ $description }}</p>
        </div>
        <a href="{{ route('pages.manage.edit', $page) }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">← До блоків сторінки</a>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">
            <div class="mb-2 font-semibold">Перевірте форму:</div>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" class="space-y-6">
        @csrf
        @if (($formMethod ?? 'POST') === 'PUT')
            @method('PUT')
        @endif

        <section class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow">
            <header class="space-y-1">
                <h2 class="text-xl font-semibold">Дані блока</h2>
                <p class="text-sm text-gray-500">Сторінка: <span class="font-medium text-gray-700">{{ $page->title }}</span></p>
            </header>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm font-medium">Мова</span>
                    <input type="text" name="locale" maxlength="8" value="{{ old('locale', $block->locale) }}" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium">Тип</span>
                    <input type="text" name="type" maxlength="32" value="{{ old('type', $block->type) }}" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium">Колонка</span>
                    <select name="column" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="" @selected($columnValue === null || $columnValue === '')>—</option>
                        <option value="header" @selected($columnValue === 'header')>Шапка</option>
                        <option value="left" @selected($columnValue === 'left')>Ліва колонка</option>
                        <option value="right" @selected($columnValue === 'right')>Права колонка</option>
                    </select>
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium">Порядок</span>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $block->sort_order) }}" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="text-sm font-medium">CSS клас</span>
                    <input type="text" name="css_class" value="{{ old('css_class', $block->css_class) }}" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="text-sm font-medium">Заголовок</span>
                    <input type="text" name="heading" value="{{ old('heading', $block->heading) }}" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="text-sm font-medium">Контент</span>
                    <textarea id="{{ $bodyFieldId }}" name="body" rows="12" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('body', $block->body) }}</textarea>
                </label>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('pages.manage.edit', $page) }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">Скасувати</a>
            <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.CodeMirror === 'undefined') {
            return;
        }

        var textarea = document.getElementById(@json($bodyFieldId));

        if (!textarea || textarea.dataset.codemirrorInitialized === '1') {
            return;
        }

        textarea.dataset.codemirrorInitialized = '1';

        var editor = window.CodeMirror.fromTextArea(textarea, {
            mode: 'htmlmixed',
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            autoCloseBrackets: true,
            autoCloseTags: true,
            tabSize: 4,
            indentUnit: 4,
        });

        editor.setSize('100%', '24rem');
    });
</script>
