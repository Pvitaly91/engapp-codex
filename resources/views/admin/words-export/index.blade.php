@extends('layouts.app')

@section('title', 'Експорт слів (JSON/CSV)')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-5xl flex-col gap-8">
            <header class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">Експорт слів (JSON/CSV)</h1>
                        <p class="text-slate-500">Вигрузка всіх слів по вибраній мові у форматі JSON або CSV</p>
                    </div>
                </div>
            </header>

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

            <section class="space-y-6">
                <!-- Language Selector -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-slate-800">Вибір мови</h2>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($allowedLangs as $langOption)
                            <a
                                href="{{ route('admin.words.export.index', ['lang' => $langOption]) }}"
                                class="inline-flex items-center justify-center rounded-lg border px-4 py-2 text-sm font-medium shadow-sm transition {{ $lang === $langOption ? 'border-blue-500 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}"
                            >
                                @if ($langOption === 'uk')
                                    🇺🇦 Українська
                                @elseif ($langOption === 'pl')
                                    🇵🇱 Польська
                                @elseif ($langOption === 'en')
                                    🇬🇧 Англійська
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- JSON Export Actions -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-slate-800">
                        <i class="fa-solid fa-file-code text-blue-600 mr-2"></i>
                        JSON Експорт для мови: <span class="text-blue-600">{{ strtoupper($lang) }}</span>
                    </h2>
                    <p class="mb-4 text-sm text-slate-500">Повний експорт з усіма даними (id, word, translation, type, tags)</p>
                    <div class="flex flex-wrap gap-3">
                        <form
                            action="{{ route('admin.words.export.run', ['lang' => $lang]) }}"
                            method="POST"
                            class="inline-flex"
                        >
                            @csrf
                            <input type="hidden" name="lang" value="{{ $lang }}">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg border border-emerald-300 bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring"
                            >
                                <i class="fa-solid fa-file-export mr-2"></i>Експорт в JSON
                            </button>
                        </form>
                        @if ($fileExists)
                            <a
                                href="{{ route('admin.words.export.view', ['lang' => $lang]) }}"
                                class="inline-flex items-center justify-center rounded-lg border border-blue-300 bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring"
                            >
                                <i class="fa-solid fa-eye mr-2"></i>Переглянути JSON
                            </a>
                            <a
                                href="{{ route('admin.words.export.download', ['lang' => $lang]) }}"
                                class="inline-flex items-center justify-center rounded-lg border border-indigo-300 bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring"
                            >
                                <i class="fa-solid fa-download mr-2"></i>Скачати JSON
                            </a>
                        @endif
                    </div>
                </div>

                <!-- CSV Export Actions -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-slate-800">
                        <i class="fa-solid fa-file-csv text-green-600 mr-2"></i>
                        CSV Експорт для мови: <span class="text-green-600">{{ strtoupper($lang) }}</span>
                    </h2>
                    <p class="mb-4 text-sm text-slate-500">Простий експорт: тільки 2 колонки (word, translation)</p>
                    <form
                        action="{{ route('admin.words.export.csv') }}"
                        method="POST"
                        class="inline-flex"
                    >
                        @csrf
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg border border-green-300 bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring"
                        >
                            <i class="fa-solid fa-file-csv mr-2"></i>Експорт в CSV
                        </button>
                    </form>
                </div>

                <!-- JSON Import Section -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-slate-800">
                        <i class="fa-solid fa-file-code text-blue-600 mr-2"></i>
                        Імпорт з JSON
                    </h2>
                    <p class="mb-4 text-sm text-slate-500">Завантажте JSON файл з перекладами. Дублікати слів, перекладів та тегів будуть пропущені.</p>
                    <form
                        action="{{ route('admin.words.export.import') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="space-y-4"
                    >
                        @csrf
                        <div>
                            <label for="json_file" class="block text-sm font-medium text-slate-700 mb-2">JSON файл</label>
                            <input
                                type="file"
                                name="json_file"
                                id="json_file"
                                accept=".json"
                                required
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            >
                            @error('json_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-start gap-3">
                            <input
                                type="checkbox"
                                name="overwrite_translations"
                                id="overwrite_translations"
                                value="1"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                            >
                            <label for="overwrite_translations" class="text-sm text-slate-700">
                                <span class="font-medium text-red-600">Перезаписати всі переклади</span>
                                <p class="text-xs text-slate-500 mt-1">
                                    Якщо увімкнено: всі переклади з JSON файлу перезапишуть існуючі (тільки переклади, інші поля не змінюються).
                                    Якщо <code class="bg-slate-100 px-1 rounded">"translation": null</code> — переклад буде видалено.
                                </p>
                            </label>
                        </div>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg border border-amber-300 bg-amber-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-amber-600 focus:outline-none focus:ring"
                        >
                            <i class="fa-solid fa-file-import mr-2"></i>Імпортувати з JSON
                        </button>
                    </form>
                </div>

                <!-- CSV Import Section -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-slate-800">
                        <i class="fa-solid fa-file-csv text-green-600 mr-2"></i>
                        Імпорт з CSV
                    </h2>
                    <p class="mb-4 text-sm text-slate-500">Завантажте CSV файл з 2 колонками: word, translation. Мову перекладу вкажіть нижче.</p>
                    <form
                        action="{{ route('admin.words.export.csv.import') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="space-y-4"
                    >
                        @csrf
                        <div>
                            <label for="csv_file" class="block text-sm font-medium text-slate-700 mb-2">CSV файл</label>
                            <input
                                type="file"
                                name="csv_file"
                                id="csv_file"
                                accept=".csv,.txt"
                                required
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                            >
                            @error('csv_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="csv_lang" class="block text-sm font-medium text-slate-700 mb-2">Мова перекладу</label>
                            <select
                                name="csv_lang"
                                id="csv_lang"
                                required
                                class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm"
                            >
                                <option value="uk" {{ $lang === 'uk' ? 'selected' : '' }}>🇺🇦 Українська</option>
                                <option value="pl" {{ $lang === 'pl' ? 'selected' : '' }}>🇵🇱 Польська</option>
                                <option value="en" {{ $lang === 'en' ? 'selected' : '' }}>🇬🇧 Англійська</option>
                            </select>
                            @error('csv_lang')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-start gap-3">
                            <input
                                type="checkbox"
                                name="csv_overwrite_translations"
                                id="csv_overwrite_translations"
                                value="1"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                            >
                            <label for="csv_overwrite_translations" class="text-sm text-slate-700">
                                <span class="font-medium text-red-600">Перезаписати всі переклади</span>
                                <p class="text-xs text-slate-500 mt-1">
                                    Якщо увімкнено: всі переклади з CSV файлу перезапишуть існуючі (тільки переклади, інші поля не змінюються).
                                    Якщо переклад у файлі порожній — переклад буде видалено з бази.
                                </p>
                            </label>
                        </div>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg border border-teal-300 bg-teal-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-600 focus:outline-none focus:ring"
                        >
                            <i class="fa-solid fa-file-import mr-2"></i>Імпортувати з CSV
                        </button>
                    </form>
                </div>

                <!-- File Info -->
                @if ($fileExists)
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-lg font-semibold text-slate-800">Інформація про файл</h2>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-file-code text-emerald-600"></i>
                                    <div>
                                        <p class="text-xs text-slate-500">Файл</p>
                                        <code class="text-sm font-mono text-slate-700">words_{{ $lang }}.json</code>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-database text-blue-600"></i>
                                    <div>
                                        <p class="text-xs text-slate-500">Розмір</p>
                                        <p class="text-sm font-medium text-slate-700">{{ number_format($fileSize / 1024, 2) }} KB</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-clock text-amber-600"></i>
                                    <div>
                                        <p class="text-xs text-slate-500">Останнє оновлення</p>
                                        <p class="text-sm font-medium text-slate-700">{{ \Carbon\Carbon::createFromTimestamp($lastModified)->format('d.m.Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-globe text-purple-600"></i>
                                    <div>
                                        <p class="text-xs text-slate-500">Мова</p>
                                        <p class="text-sm font-medium text-slate-700">{{ strtoupper($lang) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Public URL -->
                        <div class="mt-4">
                            <h3 class="mb-2 text-sm font-semibold text-slate-700">Публічний URL</h3>
                            <div class="flex flex-wrap items-center gap-2">
                                <code id="public-url" class="flex-1 rounded-lg bg-slate-100 px-3 py-2 text-sm font-mono text-slate-700">{{ $publicUrl }}</code>
                                <button
                                    type="button"
                                    onclick="copyPublicUrl(event)"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring"
                                >
                                    <i class="fa-solid fa-copy mr-1.5"></i>Copy
                                </button>
                                <a
                                    href="{{ $publicUrl }}"
                                    target="_blank"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring"
                                >
                                    <i class="fa-solid fa-external-link-alt mr-1.5"></i>Відкрити
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-triangle-exclamation text-2xl text-amber-600"></i>
                            <div>
                                <h3 class="font-semibold text-amber-800">Файл експорту не знайдено</h3>
                                <p class="text-sm text-amber-700">Для мови <strong>{{ strtoupper($lang) }}</strong> ще не було створено експорту. Натисніть "Експорт в JSON" для створення файлу.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>

    @push('scripts')
        <script>
            function copyPublicUrl(event) {
                const url = document.getElementById('public-url').textContent;
                navigator.clipboard.writeText(url).then(() => {
                    // Show temporary success indicator
                    const button = event.target.closest('button');
                    const originalHTML = button.innerHTML;
                    button.innerHTML = '<i class="fa-solid fa-check mr-1.5"></i>Скопійовано';
                    button.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
                    
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy:', err);
                    alert('Не вдалося скопіювати URL');
                });
            }
        </script>
    @endpush
@endsection
