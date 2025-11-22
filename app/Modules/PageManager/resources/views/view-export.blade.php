@extends('layouts.app')

@section('title', 'Перегляд експортованого JSON')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-8">
            <header class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">Перегляд експортованого JSON</h1>
                        <p class="text-slate-500">Експорт сторінок згрупованих по категоріям</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('pages.manage.index') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring"
                        >
                            Назад до сторінок
                        </a>
                        <a
                            href="{{ route('pages.manage.export.download') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-indigo-300 bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring"
                        >
                            Скачати JSON
                        </a>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-4">
                        <div class="flex items-center gap-2">
                            <div>
                                <p class="text-xs text-slate-500">Файл</p>
                                <code class="text-sm font-mono text-slate-700">{{ $filePath }}</code>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div>
                                <p class="text-xs text-slate-500">Розмір</p>
                                <p class="text-sm font-medium text-slate-700">{{ number_format($fileSize / 1024, 2) }} KB</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div>
                                <p class="text-xs text-slate-500">Останнє оновлення</p>
                                <p class="text-sm font-medium text-slate-700">{{ \Carbon\Carbon::createFromTimestamp($lastModified)->format('d.m.Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div>
                                <p class="text-xs text-slate-500">Всього сторінок</p>
                                <p class="text-sm font-medium text-slate-700">{{ $jsonData['total_pages'] ?? 0 }}</p>
                            </div>
                        </div>
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

            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-800">Вміст JSON файлу</h2>
                    <button
                        onclick="copyToClipboard(event)"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring"
                    >
                        Копіювати
                    </button>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-900 p-6 shadow-sm">
                    <pre id="json-content" class="overflow-x-auto text-sm"><code class="language-json text-slate-100">{{ $jsonContent }}</code></pre>
                </div>
            </section>

            @if (isset($jsonData['categories']) && is_array($jsonData['categories']))
                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-slate-800">Категорії ({{ count($jsonData['categories']) }})</h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($jsonData['categories'] as $category)
                            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-slate-800">{{ $category['category_title'] ?? 'Без категорії' }}</h3>
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                                        {{ count($category['pages'] ?? []) }} сторінок
                                    </span>
                                </div>
                                @if (isset($category['pages']) && is_array($category['pages']) && count($category['pages']) > 0)
                                    <div class="mt-3 space-y-1">
                                        @foreach (array_slice($category['pages'], 0, 5) as $page)
                                            <div class="text-xs text-slate-600">
                                                • {{ $page['page_title'] ?? '' }}
                                            </div>
                                        @endforeach
                                        @if (count($category['pages']) > 5)
                                            <div class="text-xs text-slate-500">
                                                +{{ count($category['pages']) - 5 }} ще
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
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
                    button.innerHTML = 'Скопійовано';
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
        </script>
    @endpush
@endsection
