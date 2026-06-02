@php
    $idPrefix = $idPrefix ?? ('codex-prompt-' . ($prompt['key'] ?? 'prompt'));
    $promptIdText = (string) ($prompt['prompt_id_text'] ?? '');
    $summaryTopText = (string) ($prompt['summary_top_text'] ?? '');
    $summaryBottomText = (string) ($prompt['summary_bottom_text'] ?? '');
    $promptText = (string) ($prompt['text'] ?? '');
    $promptTitle = (string) ($prompt['title'] ?? 'Generated prompt');
    $promptDescription = (string) ($promptDescription ?? 'Copy-ready текст без додаткової постобробки.');

    $promptIdTopId = $idPrefix . '-prompt-id-top';
    $summaryTopId = $idPrefix . '-summary-top';
    $promptTextareaId = $idPrefix . '-text';
    $summaryBottomId = $idPrefix . '-summary-bottom';
    $promptIdBottomId = $idPrefix . '-prompt-id-bottom';
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">{{ $promptTitle }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $promptDescription }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">CODEX PROMPT ID (Top)</h3>
                    <p class="mt-1 text-sm text-slate-500">Окремий copy-ready блок з canonical <code>CODEX PROMPT ID:</code> над головним prompt textarea.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500" x-show="copyStates['{{ $promptIdTopId }}']" x-cloak>Copied</span>
                    <button
                        type="button"
                        @click="copyById('{{ $promptIdTopId }}')"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Copy
                    </button>
                </div>
            </div>
            <input
                id="{{ $promptIdTopId }}"
                type="text"
                readonly
                value="{{ $promptIdText }}"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 font-mono text-sm text-slate-900 shadow-sm"
            >
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Summary (Top)</h3>
                    <p class="mt-1 text-sm text-slate-500">Окремий copy-ready summary block над основним prompt.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500" x-show="copyStates['{{ $summaryTopId }}']" x-cloak>Copied</span>
                    <button
                        type="button"
                        @click="copyById('{{ $summaryTopId }}')"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Copy
                    </button>
                </div>
            </div>
            <textarea
                id="{{ $summaryTopId }}"
                readonly
                class="min-h-[180px] w-full rounded-2xl border border-slate-200 bg-white p-4 font-mono text-sm leading-6 text-slate-800 shadow-inner"
            >{{ $summaryTopText }}</textarea>
        </div>

        <div>
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Generated prompt</h3>
                    <p class="mt-1 text-sm text-slate-500">Текст рендериться без змін. Вміст усередині textarea не переформатовується.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500" x-show="copyStates['{{ $promptTextareaId }}']" x-cloak>Copied</span>
                    <button
                        type="button"
                        @click="copyById('{{ $promptTextareaId }}')"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Copy
                    </button>
                </div>
            </div>
            <textarea
                id="{{ $promptTextareaId }}"
                readonly
                class="min-h-[420px] w-full rounded-2xl border border-slate-200 bg-slate-950 p-4 font-mono text-sm leading-6 text-slate-100 shadow-inner focus:border-slate-400 focus:ring-slate-400"
            >{{ $promptText }}</textarea>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Summary (Bottom)</h3>
                    <p class="mt-1 text-sm text-slate-500">Дубльований summary block під головним prompt для читабельності й копіювання.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500" x-show="copyStates['{{ $summaryBottomId }}']" x-cloak>Copied</span>
                    <button
                        type="button"
                        @click="copyById('{{ $summaryBottomId }}')"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Copy
                    </button>
                </div>
            </div>
            <textarea
                id="{{ $summaryBottomId }}"
                readonly
                class="min-h-[180px] w-full rounded-2xl border border-slate-200 bg-white p-4 font-mono text-sm leading-6 text-slate-800 shadow-inner"
            >{{ $summaryBottomText }}</textarea>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">CODEX PROMPT ID (Bottom)</h3>
                    <p class="mt-1 text-sm text-slate-500">Повторений copy-ready блок з canonical <code>CODEX PROMPT ID:</code> під summary та prompt textarea.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500" x-show="copyStates['{{ $promptIdBottomId }}']" x-cloak>Copied</span>
                    <button
                        type="button"
                        @click="copyById('{{ $promptIdBottomId }}')"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Copy
                    </button>
                </div>
            </div>
            <input
                id="{{ $promptIdBottomId }}"
                type="text"
                readonly
                value="{{ $promptIdText }}"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 font-mono text-sm text-slate-900 shadow-sm"
            >
        </div>
    </div>
</div>
