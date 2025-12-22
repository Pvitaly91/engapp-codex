@extends('layouts.app')

@section('title', 'Перегляд експортованих слів (JSON)')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-8">
            <header class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">Перегляд експортованих слів</h1>
                        <p class="text-slate-500">Експорт слів для мови: <strong>{{ strtoupper($lang) }}</strong></p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('admin.words.export.index', ['lang' => $lang]) }}"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring"
                        >
                            <i class="fa-solid fa-arrow-left mr-2"></i>Назад до експорту
                        </a>
                        <a
                            href="{{ route('admin.words.export.download', ['lang' => $lang]) }}"
                            class="inline-flex items-center justify-center rounded-lg border border-indigo-300 bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring"
                        >
                            <i class="fa-solid fa-download mr-2"></i>Скачати JSON
                        </a>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-5">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-file-code text-emerald-600"></i>
                            <div>
                                <p class="text-xs text-slate-500">Файл</p>
                                <code class="text-sm font-mono text-slate-700">{{ $filePath }}</code>
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
                            <i class="fa-solid fa-list-ol text-purple-600"></i>
                            <div>
                                <p class="text-xs text-slate-500">Всього слів</p>
                                <p class="text-sm font-medium text-slate-700">{{ $jsonData['counts']['total_words'] ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-globe text-cyan-600"></i>
                            <div>
                                <p class="text-xs text-slate-500">Мова</p>
                                <p class="text-sm font-medium text-slate-700">{{ strtoupper($jsonData['lang'] ?? $lang) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Public URL -->
                <div class="rounded-lg border border-slate-200 bg-white p-4">
                    <h3 class="mb-2 text-sm font-semibold text-slate-700">Публічний URL</h3>
                    <div class="flex flex-wrap items-center gap-2">
                        <code id="public-url" class="flex-1 rounded-lg bg-slate-100 px-3 py-2 text-sm font-mono text-slate-700">{{ $publicUrl }}</code>
                        <button
                            type="button"
                            onclick="copyPublicUrl()"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring"
                        >
                            <i class="fa-solid fa-copy mr-1.5"></i>Copy
                        </button>
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

            <!-- Statistics -->
            @if (isset($jsonData['counts']))
                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-slate-800">Статистика</h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-slate-500">Всього слів</p>
                                    <p class="text-2xl font-bold text-slate-800">{{ $jsonData['counts']['total_words'] ?? 0 }}</p>
                                </div>
                                <i class="fa-solid fa-list text-3xl text-slate-300"></i>
                            </div>
                        </div>
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-emerald-600">З перекладом</p>
                                    <p class="text-2xl font-bold text-emerald-800">{{ $jsonData['counts']['with_translation'] ?? 0 }}</p>
                                </div>
                                <i class="fa-solid fa-check-circle text-3xl text-emerald-300"></i>
                            </div>
                        </div>
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-amber-600">Без перекладу</p>
                                    <p class="text-2xl font-bold text-amber-800">{{ $jsonData['counts']['without_translation'] ?? 0 }}</p>
                                </div>
                                <i class="fa-solid fa-exclamation-circle text-3xl text-amber-300"></i>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            <!-- JSON Content -->
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-800">Вміст JSON файлу</h2>
                    <button
                        onclick="copyToClipboard(event)"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring"
                    >
                        <i class="fa-solid fa-copy mr-1.5"></i>Копіювати
                    </button>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-900 p-6 shadow-sm">
                    <pre id="json-content" class="overflow-x-auto text-sm"><code class="language-json text-slate-100">{{ $jsonContent }}</code></pre>
                </div>
            </section>

            <!-- Sample Words -->
            @if (isset($jsonData['with_translation']) && is_array($jsonData['with_translation']) && count($jsonData['with_translation']) > 0)
                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-slate-800">Приклади слів з перекладом (перші 10)</h2>
                    <div class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Слово</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Переклад</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Тип</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Теги</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach (array_slice($jsonData['with_translation'], 0, 10) as $word)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $word['id'] ?? '' }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $word['word'] ?? '' }}</td>
                                        <td class="px-4 py-3 text-sm text-emerald-700">{{ $word['translation'] ?? '' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-500">{{ $word['type'] ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach (array_slice($word['tags'] ?? [], 0, 3) as $tag)
                                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-700">{{ $tag }}</span>
                                                @endforeach
                                                @if (count($word['tags'] ?? []) > 3)
                                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">+{{ count($word['tags']) - 3 }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            @if (isset($jsonData['without_translation']) && is_array($jsonData['without_translation']) && count($jsonData['without_translation']) > 0)
                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-slate-800">Приклади слів без перекладу (перші 10)</h2>
                    <div class="rounded-lg border border-amber-200 bg-white shadow-sm overflow-hidden">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-amber-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Слово</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Тип</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Теги</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach (array_slice($jsonData['without_translation'], 0, 10) as $word)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $word['id'] ?? '' }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $word['word'] ?? '' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-500">{{ $word['type'] ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach (array_slice($word['tags'] ?? [], 0, 3) as $tag)
                                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-700">{{ $tag }}</span>
                                                @endforeach
                                                @if (count($word['tags'] ?? []) > 3)
                                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">+{{ count($word['tags']) - 3 }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            function copyToClipboard(event) {
                const content = document.getElementById('json-content').textContent;
                navigator.clipboard.writeText(content).then(() => {
                    // Show temporary success message
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
                    alert('Не вдалося скопіювати текст');
                });
            }

            function copyPublicUrl() {
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
