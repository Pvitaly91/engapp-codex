@extends('layouts.app')

@section('title', 'Агрегація тегів')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-5xl flex-col gap-8">
            <header class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">Агрегація тегів</h1>
                        <p class="text-slate-500">Об'єднуйте схожі теги під одним головним тегом.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('test-tags.index') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring"
                        >
                            ← Назад до тегів
                        </a>
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
                <h2 class="text-xl font-semibold text-slate-800">Створити нову агрегацію</h2>
                <form method="POST" action="{{ route('test-tags.aggregations.store') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    @csrf
                    
                    <div>
                        <label for="main_tag" class="block text-sm font-medium text-slate-700 mb-1">
                            Головний тег
                        </label>
                        <input
                            type="text"
                            id="main_tag"
                            name="main_tag"
                            list="tags-list"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Введіть назву головного тегу"
                            required
                        >
                        <datalist id="tags-list">
                            @foreach ($allTags as $tag)
                                <option value="{{ $tag->name }}">
                            @endforeach
                        </datalist>
                        @error('main_tag')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Схожі теги (по одному на рядок)
                        </label>
                        <div id="similar-tags-container" class="space-y-2">
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    name="similar_tags[]"
                                    list="tags-list"
                                    class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    placeholder="Введіть назву схожого тегу"
                                    required
                                >
                                <button
                                    type="button"
                                    onclick="removeTagInput(this)"
                                    class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                                >
                                    Видалити
                                </button>
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="addTagInput()"
                            class="mt-2 inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            + Додати тег
                        </button>
                        @error('similar_tags')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                        >
                            Створити агрегацію
                        </button>
                    </div>
                </form>
            </section>

            <section class="space-y-4">
                <h2 class="text-xl font-semibold text-slate-800">Існуючі агрегації</h2>
                @if (empty($aggregations))
                    <p class="text-sm text-slate-500 rounded-xl border border-slate-200 bg-white p-6">
                        Агрегації ще не створено.
                    </p>
                @else
                    <div class="space-y-4">
                        @foreach ($aggregations as $aggregation)
                            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-800">
                                            {{ $aggregation['main_tag'] }}
                                        </h3>
                                        <p class="text-sm text-slate-500">Головний тег</p>
                                    </div>
                                    <form
                                        action="{{ route('test-tags.aggregations.destroy', ['mainTag' => $aggregation['main_tag']]) }}"
                                        method="POST"
                                        data-confirm="Видалити агрегацію для тегу «{{ $aggregation['main_tag'] }}»?"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50"
                                        >
                                            Видалити
                                        </button>
                                    </form>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-700 mb-2">Схожі теги:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($aggregation['similar_tags'] ?? [] as $similarTag)
                                            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800">
                                                {{ $similarTag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-xl border border-blue-200 bg-blue-50 p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">
                    <i class="fa-solid fa-circle-info mr-2"></i>Інформація про файл конфігурації
                </h3>
                <p class="text-sm text-blue-800 mb-2">
                    Агрегації зберігаються у файлі: <code class="bg-blue-100 px-2 py-1 rounded">config/tags/aggregation.json</code>
                </p>
                <p class="text-sm text-blue-800">
                    Цей файл доступний у Git і може бути керований вручну або через цей інтерфейс.
                </p>
            </section>
        </div>
    </div>

    <div
        id="aggregation-confirmation-modal"
        class="fixed inset-0 z-40 hidden items-center justify-center"
        role="dialog"
        aria-modal="true"
        aria-labelledby="aggregation-confirmation-title"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-confirm-overlay></div>
        <div class="relative w-full max-w-sm space-y-5 rounded-xl bg-white px-6 py-5 shadow-xl">
            <div class="space-y-2">
                <h2 id="aggregation-confirmation-title" class="text-lg font-semibold text-slate-800">Підтвердження</h2>
                <p class="text-sm text-slate-600" data-confirm-message></p>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button
                    type="button"
                    class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200"
                    data-confirm-cancel
                >
                    Скасувати
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-red-500"
                    data-confirm-accept
                >
                    Підтвердити
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function addTagInput() {
            const container = document.getElementById('similar-tags-container');
            const newInput = document.createElement('div');
            newInput.className = 'flex gap-2';
            newInput.innerHTML = `
                <input
                    type="text"
                    name="similar_tags[]"
                    list="tags-list"
                    class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Введіть назву схожого тегу"
                    required
                >
                <button
                    type="button"
                    onclick="removeTagInput(this)"
                    class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                >
                    Видалити
                </button>
            `;
            container.appendChild(newInput);
        }

        function removeTagInput(button) {
            const container = document.getElementById('similar-tags-container');
            if (container.children.length > 1) {
                button.parentElement.remove();
            }
        }

        // Confirmation modal logic (similar to test-tags index page)
        const initAggregationConfirmation = () => {
            const modal = document.getElementById('aggregation-confirmation-modal');
            const messageTarget = modal ? modal.querySelector('[data-confirm-message]') : null;
            const acceptButton = modal ? modal.querySelector('[data-confirm-accept]') : null;
            const cancelButton = modal ? modal.querySelector('[data-confirm-cancel]') : null;
            const overlay = modal ? modal.querySelector('[data-confirm-overlay]') : null;
            const forms = document.querySelectorAll('form[data-confirm]');

            if (!modal || !messageTarget || !acceptButton || !cancelButton) {
                forms.forEach((form) => {
                    form.addEventListener('submit', (event) => {
                        if (form.dataset.confirmed === 'true') {
                            form.dataset.confirmed = '';
                            return;
                        }

                        const message = form.dataset.confirm || '';
                        if (message && !window.confirm(message)) {
                            event.preventDefault();
                        }
                    });
                });

                return;
            }

            let pendingForm = null;

            const closeModal = (restoreFocus = true) => {
                modal.classList.add('hidden');
                modal.classList.remove('flex', 'items-center', 'justify-center');

                if (restoreFocus && pendingForm) {
                    const focusTarget = pendingForm.querySelector('button, [type="submit"], [tabindex]:not([tabindex="-1"])');
                    if (focusTarget && typeof focusTarget.focus === 'function') {
                        focusTarget.focus();
                    }
                }

                pendingForm = null;
            };

            const openModal = (form, message) => {
                messageTarget.textContent = message || 'Підтвердьте дію.';
                pendingForm = form;
                modal.classList.remove('hidden');
                modal.classList.add('flex', 'items-center', 'justify-center');
                acceptButton.focus();
            };

            const handleFormSubmit = (event) => {
                const form = event.target;

                if (form.dataset.confirmed === 'true') {
                    form.dataset.confirmed = '';
                    return;
                }

                event.preventDefault();
                const message = form.dataset.confirm || '';
                openModal(form, message);
            };

            forms.forEach((form) => {
                form.addEventListener('submit', handleFormSubmit);
            });

            acceptButton.addEventListener('click', () => {
                if (!pendingForm) {
                    closeModal();
                    return;
                }

                const formToSubmit = pendingForm;
                formToSubmit.dataset.confirmed = 'true';
                closeModal(false);
                formToSubmit.requestSubmit();
            });

            const cancelHandler = () => {
                closeModal();
            };

            cancelButton.addEventListener('click', cancelHandler);

            if (overlay) {
                overlay.addEventListener('click', cancelHandler);
            }

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    cancelHandler();
                }
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAggregationConfirmation);
        } else {
            initAggregationConfirmation();
        }
    </script>
@endpush
