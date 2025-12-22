@extends('layouts.app')

@section('title', 'Експорт слів у JSON')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-5xl flex-col gap-8">
            <header class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">Експорт слів (JSON)</h1>
                        <p class="text-slate-500">Вигрузка всіх слів з перекладом та без перекладу для вибраної мови.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form action="{{ route('admin.words.export.index') }}" method="GET" class="flex items-center gap-2">
                            <label for="lang" class="text-sm font-medium text-slate-600">Мова:</label>
                            <select
                                id="lang"
                                name="lang"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 shadow-sm focus:border-blue-400 focus:outline-none"
                                onchange="this.form.submit()"
                            >
                                @foreach ($langs as $optionLang)
                                    <option value="{{ $optionLang }}" @selected($optionLang === $lang)>
                                        {{ strtoupper($optionLang) }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                        <form action="{{ route('admin.words.export.run') }}" method="POST" class="inline-flex">
                            @csrf
                            <input type="hidden" name="lang" value="{{ $lang }}">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg border border-emerald-300 bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring"
                            >
                                <i class="fa-solid fa-file-export mr-2"></i>Експорт в JSON
                            </button>
                        </form>
                        <a
                            href="{{ route('admin.words.export.view', ['lang' => $lang]) }}"
                            class="inline-flex items-center justify-center rounded-lg border border-blue-300 bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring {{ $fileExists ? '' : 'pointer-events-none opacity-50' }}"
                        >
                            <i class="fa-solid fa-eye mr-2"></i>Переглянути JSON
                        </a>
                        <a
                            href="{{ route('admin.words.export.download', ['lang' => $lang]) }}"
                            class="inline-flex items-center justify-center rounded-lg border border-indigo-300 bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring {{ $fileExists ? '' : 'pointer-events-none opacity-50' }}"
                        >
                            <i class="fa-solid fa-download mr-2"></i>Скачати JSON
                        </a>
                    </div>
                </div>

                @if (session('status'))
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                                <i class="fa-solid fa-language"></i>
                            </span>
                            <div>
                                <p class="text-xs text-slate-500">Вибрана мова</p>
                                <p class="text-sm font-semibold text-slate-800">{{ strtoupper($lang) }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                <i class="fa-solid fa-file-code"></i>
                            </span>
                            <div>
                                <p class="text-xs text-slate-500">Файл</p>
                                <p class="text-sm font-semibold text-slate-800">{{ $relativePath }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $fileExists ? 'Файл існує' : 'Файл ще не створено' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                <i class="fa-solid fa-cloud-arrow-down"></i>
                            </span>
                            <div class="space-y-1">
                                <p class="text-xs text-slate-500">Публічний URL</p>
                                <div class="flex items-center gap-2 text-sm font-medium text-slate-800">
                                    <code class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-700">{{ $publicUrl }}</code>
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50"
                                        onclick="copyUrl('{{ $publicUrl }}', event)"
                                    >
                                        <i class="fa-solid fa-copy mr-1"></i>Copy
                                    </button>
                                </div>
                                @if ($fileExists)
                                    <p class="text-xs text-slate-500">
                                        Розмір: {{ number_format($fileSize / 1024, 2) }} KB · Оновлено: {{ \Carbon\Carbon::createFromTimestamp($lastModified)->format('d.m.Y H:i') }}
                                    </p>
                                @else
                                    <p class="text-xs text-slate-400">Розмір та дата стануть доступними після експорту</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <section class="space-y-3 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                <h2 class="text-base font-semibold text-slate-800">Як це працює?</h2>
                <ul class="list-disc space-y-1 pl-5">
                    <li>Експорт формує два масиви: <strong>with_translation</strong> та <strong>without_translation</strong> для вибраної мови.</li>
                    <li>Файл зберігається за стабільним шляхом <code class="rounded bg-white px-1 py-0.5 text-xs">{{ $relativePath }}</code> у папці <code class="rounded bg-white px-1 py-0.5 text-xs">public</code>.</li>
                    <li>Кожен новий експорт перезаписує попередній файл.</li>
                    <li>Публічний URL можна використовувати для завантаження без авторизації.</li>
                </ul>
            </section>
        </div>
    </div>

    @push('scripts')
        <script>
            function copyUrl(url, event) {
                navigator.clipboard.writeText(url).then(() => {
                    const button = event.target.closest('button');
                    const original = button.innerHTML;
                    button.innerHTML = '<i class="fa-solid fa-check mr-1"></i>Copied';
                    button.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
                    setTimeout(() => {
                        button.innerHTML = original;
                        button.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
                    }, 2000);
                }).catch(() => {
                    alert('Не вдалося скопіювати URL');
                });
            }
        </script>
    @endpush
@endsection
