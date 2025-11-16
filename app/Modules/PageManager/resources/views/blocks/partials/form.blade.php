@php
    $columnValue = old('column', $block->column);
    $bodyFieldId = 'block-body-editor';
    $entityTitle = $entityTitle ?? '';
    $contextLabel = $contextLabel ?? 'Сторінка';
    $backUrl = $backUrl ?? url()->previous();
    $backLabel = $backLabel ?? '← Назад';
    $cancelUrl = $cancelUrl ?? $backUrl;
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

<style>
    .pm-code-editor {
        position: relative;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }

    .pm-code-highlight,
    .pm-code-textarea {
        box-sizing: border-box;
        font-size: 14px;
        line-height: 1.5;
        padding: 14px;
        border-radius: 0.75rem;
    }

    .pm-code-highlight {
        position: absolute;
        inset: 0;
        margin: 0;
        overflow: auto;
        white-space: pre-wrap;
        word-break: break-word;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        color: #0f172a;
        pointer-events: none;
    }

    .pm-code-textarea {
        position: relative;
        width: 100%;
        min-height: 24rem;
        background: transparent;
        color: transparent;
        caret-color: #111827;
        border: 1px solid #d1d5db;
        resize: vertical;
        overflow: auto;
    }

    .pm-code-textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.16);
    }

    .pm-code-token-tag {
        color: #2563eb;
    }

    .pm-code-token-attr {
        color: #0f766e;
    }

    .pm-code-token-string {
        color: #b45309;
    }

    .pm-code-token-comment {
        color: #9ca3af;
        font-style: italic;
    }
</style>

<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">{{ $heading }}</h1>
            <p class="text-sm text-gray-500">{{ $description }}</p>
        </div>
        <a href="{{ $backUrl }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">{{ $backLabel }}</a>
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
                <p class="text-sm text-gray-500">{{ $contextLabel }}: <span class="font-medium text-gray-700">{{ $entityTitle }}</span></p>
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
            <a href="{{ $cancelUrl }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">Скасувати</a>
            <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var textarea = document.getElementById(@json($bodyFieldId));

        if (!textarea || textarea.dataset.codemirrorInitialized === '1') {
            return;
        }

        textarea.dataset.codemirrorInitialized = '1';

        if (typeof window.CodeMirror !== 'undefined') {
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

            return;
        }

        var escapeHtml = function (value) {
            return (value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        };

        var highlightHtml = function (value) {
            var escaped = escapeHtml(value || '');

            escaped = escaped.replace(/(&lt;!--[\s\S]*?--&gt;)/g, '<span class="pm-code-token-comment">$1</span>');

            escaped = escaped.replace(/(&lt;\/?[a-zA-Z0-9-]+)([^&]*?)(\/?&gt;)/g, function (match, open, attrs, close) {
                var highlightedAttrs = attrs.replace(/([a-zA-Z_:][a-zA-Z0-9_:\-]*)(\s*=\s*)("[^"]*"|'[^']*'|[^\s"'>]+)?/g, function (
                    _,
                    name,
                    eq,
                    val
                ) {
                    var cleanedValue = val || '';

                    return '<span class="pm-code-token-attr">' + name + '</span>' + (eq || '') + (cleanedValue
                        ? '<span class="pm-code-token-string">' + cleanedValue + '</span>'
                        : '');
                });

                return '<span class="pm-code-token-tag">' + open + '</span>' + highlightedAttrs + '<span class="pm-code-token-tag">' + close + '</span>';
            });

            return escaped;
        };

        var wrapWithFallbackEditor = function (textareaEl) {
            var wrapper = document.createElement('div');
            wrapper.className = 'pm-code-editor';

            var highlight = document.createElement('pre');
            highlight.className = 'pm-code-highlight';
            highlight.setAttribute('aria-hidden', 'true');

            var code = document.createElement('code');
            highlight.appendChild(code);

            var parent = textareaEl.parentNode;
            parent.insertBefore(wrapper, textareaEl);
            wrapper.appendChild(highlight);
            wrapper.appendChild(textareaEl);

            textareaEl.classList.add('pm-code-textarea');

            var sync = function () {
                code.innerHTML = highlightHtml(textareaEl.value || '');
                highlight.scrollTop = textareaEl.scrollTop;
                highlight.scrollLeft = textareaEl.scrollLeft;
            };

            textareaEl.addEventListener('input', sync);
            textareaEl.addEventListener('scroll', function () {
                highlight.scrollTop = textareaEl.scrollTop;
                highlight.scrollLeft = textareaEl.scrollLeft;
            });

            sync();
        };

        wrapWithFallbackEditor(textarea);
    });
</script>
