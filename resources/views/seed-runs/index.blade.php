@extends('layouts.app')

@section('title', 'Seed Runs')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        @php $recentSeedRunOrdinals = collect($recentSeedRunOrdinals ?? []); @endphp
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Seed Runs</h1>
                    <p class="text-sm text-gray-500">Керуйте виконаними та невиконаними сидарами.</p>
                </div>
                @if($tableExists)
                    <form method="POST" action="{{ route('seed-runs.run-missing') }}" data-preloader>
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-500 transition disabled:opacity-50" @if($pendingSeeders->isEmpty()) disabled @endif>
                            <i class="fa-solid fa-play"></i>
                            Виконати всі невиконані
                        </button>
                    </form>
                @endif
            </div>

            @if(session('status'))
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="seed-run-ajax-feedback" class="hidden mb-4 rounded-md border px-4 py-3 text-sm"></div>

            @unless($tableExists)
                <div class="rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-yellow-800">
                    Таблиця <code class="font-mono">seed_runs</code> ще не створена. Запустіть міграції, щоб продовжити.
                </div>
            @endunless
        </div>

        @if($tableExists)
            <form id="pending-bulk-delete-form"
                  method="POST"
                  action="{{ route('seed-runs.destroy-seeder-files') }}"
                  data-preloader
                  data-bulk-delete-form
                  data-bulk-scope="pending"
                  data-confirm="Видалити файли вибраних сидерів?"
                  class="hidden">
                @csrf
                @method('DELETE')
            </form>

            <form id="executed-bulk-delete-form"
                  method="POST"
                  action="{{ route('seed-runs.destroy-seeder-files') }}"
                  data-preloader
                  data-bulk-delete-form
                  data-bulk-scope="executed"
                  data-confirm="Видалити файли вибраних сидерів?"
                  class="hidden">
                @csrf
                @method('DELETE')
            </form>

            <div >
                <div class="bg-white shadow rounded-lg p-6 my-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">Невиконані сидери</h2>
                        @if($pendingSeeders->isNotEmpty())
                            <button type="submit"
                                    form="pending-bulk-delete-form"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded-md hover:bg-red-200 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                    data-bulk-delete-button
                                    data-bulk-scope="pending"
                                    disabled>
                                <i class="fa-solid fa-trash-can"></i>
                                Видалити вибрані файли
                            </button>
                        @endif
                    </div>
                    @if($pendingSeeders->isEmpty())
                        <p class="text-sm text-gray-500">Усі сидери вже виконані.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($pendingSeeders as $pendingSeeder)
                                @php($pendingCheckboxId = 'pending-seeder-' . md5($pendingSeeder->class_name))
                                <li class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox"
                                               id="{{ $pendingCheckboxId }}"
                                               name="class_names[]"
                                               value="{{ $pendingSeeder->class_name }}"
                                               form="pending-bulk-delete-form"
                                               class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                                               data-bulk-delete-checkbox
                                               data-bulk-scope="pending">
                                        <label for="{{ $pendingCheckboxId }}" class="text-sm font-mono text-gray-700 break-all cursor-pointer">
                                            {{ $pendingSeeder->display_class_name }}
                                        </label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($pendingSeeder->supports_preview)
                                            <a href="{{ route('seed-runs.preview', ['class_name' => $pendingSeeder->class_name]) }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-sky-100 text-sky-700 text-xs font-medium rounded-md hover:bg-sky-200 transition">
                                                <i class="fa-solid fa-eye"></i>
                                                Переглянути
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('seed-runs.destroy-seeder-file') }}" data-preloader data-confirm="Видалити файл сидера «{{ e($pendingSeeder->display_class_name) }}»?">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="class_name" value="{{ $pendingSeeder->class_name }}">
                                            <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition">
                                                <i class="fa-solid fa-file-circle-xmark"></i>
                                                Видалити файл
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('seed-runs.mark-executed') }}" data-preloader>
                                            @csrf
                                            <input type="hidden" name="class_name" value="{{ $pendingSeeder->class_name }}">
                                            <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-500 text-white text-xs font-medium rounded-md hover:bg-amber-400 transition">
                                                <i class="fa-solid fa-check"></i>
                                                Позначити виконаним
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('seed-runs.run') }}" data-preloader>
                                            @csrf
                                            <input type="hidden" name="class_name" value="{{ $pendingSeeder->class_name }}">
                                            <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-500 transition">
                                                <i class="fa-solid fa-play"></i>
                                                Виконати
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="bg-white shadow rounded-lg p-6 overflow-hidden">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">Виконані сидери</h2>
                        @if($executedSeederHierarchy->isNotEmpty())
                            <button type="submit"
                                    form="executed-bulk-delete-form"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded-md hover:bg-red-200 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                    data-bulk-delete-button
                                    data-bulk-scope="executed"
                                    disabled>
                                <i class="fa-solid fa-trash-can"></i>
                                Видалити вибрані файли
                            </button>
                        @endif
                    </div>
                    @if($executedSeederHierarchy->isEmpty())
                        <p class="text-sm text-gray-500">Поки що немає виконаних сидерів.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($executedSeederHierarchy as $node)
                                @include('seed-runs.partials.executed-node', [
                                    'node' => $node,
                                    'depth' => 0,
                                    'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
                                ])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div id="seed-run-confirmation-modal" class="hidden fixed inset-0 z-50 items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="seed-run-confirmation-title">
        <div class="absolute inset-0 bg-slate-900/50" data-confirm-overlay></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full mx-4 p-6 space-y-4">
            <div class="space-y-1">
                <h2 id="seed-run-confirmation-title" class="text-lg font-semibold text-gray-800">{{ __('Підтвердження') }}</h2>
                <p class="text-sm text-gray-600" data-confirm-message></p>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition" data-confirm-cancel>{{ __('Скасувати') }}</button>
                <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-500 transition" data-confirm-accept>{{ __('Підтвердити') }}</button>
            </div>
        </div>
    </div>

    <div id="seed-run-preloader" class="hidden fixed inset-0 z-40 bg-black/40 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg px-6 py-4 flex items-center gap-3 text-sm text-gray-700">
            <span class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span>
            <span>Виконується операція…</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const preloader = document.getElementById('seed-run-preloader');
            const feedback = document.getElementById('seed-run-ajax-feedback');
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
            const successClasses = ['bg-emerald-50', 'border-emerald-200', 'text-emerald-700'];
            const errorClasses = ['bg-red-50', 'border-red-200', 'text-red-700'];
            let feedbackTimeout;

            const updateBulkButtonState = function (scope) {
                if (!scope) {
                    return;
                }

                const button = document.querySelector('[data-bulk-delete-button][data-bulk-scope="' + scope + '"]');

                if (!button) {
                    return;
                }

                const checkboxes = document.querySelectorAll('[data-bulk-delete-checkbox][data-bulk-scope="' + scope + '"]');
                const hasChecked = Array.from(checkboxes).some(function (checkbox) {
                    return checkbox.checked;
                });

                button.disabled = !hasChecked;
            };

            const updateAllBulkButtonStates = function () {
                const scopes = new Set();

                document.querySelectorAll('[data-bulk-delete-checkbox]').forEach(function (checkbox) {
                    const scope = checkbox.dataset.bulkScope;

                    if (scope) {
                        scopes.add(scope);
                    }
                });

                scopes.forEach(function (scope) {
                    updateBulkButtonState(scope);
                });
            };

            const syncDeleteWithQuestionsInputs = function (className, checked) {
                if (!className) {
                    return;
                }

                const inputs = document.querySelectorAll('[data-delete-with-questions-input][data-class-name="' + className + '"]');

                inputs.forEach(function (input) {
                    input.value = checked ? '1' : '0';
                });

                const forms = document.querySelectorAll('[data-delete-with-questions-form][data-class-name="' + className + '"]');

                forms.forEach(function (form) {
                    const confirmWithQuestions = form.dataset.confirmWithQuestions || '';
                    const confirmRegular = form.dataset.confirmRegular || form.dataset.confirm || '';

                    if (checked && confirmWithQuestions) {
                        form.dataset.confirm = confirmWithQuestions;
                    } else if (confirmRegular) {
                        form.dataset.confirm = confirmRegular;
                    }
                });
            };

            const findBulkCheckboxForClass = function (scope, className) {
                if (!scope || !className) {
                    return null;
                }

                const candidates = document.querySelectorAll('[data-bulk-delete-checkbox][data-bulk-scope="' + scope + '"]');

                for (const candidate of candidates) {
                    if (candidate.value === className) {
                        return candidate;
                    }
                }

                return null;
            };

            const prepareBulkConfirmMessage = function (scope) {
                if (!scope) {
                    return;
                }

                const form = document.querySelector('form[data-bulk-delete-form][data-bulk-scope="' + scope + '"]');

                if (!form) {
                    return;
                }

                const checkboxes = document.querySelectorAll('[data-bulk-delete-checkbox][data-bulk-scope="' + scope + '"]:checked');
                const count = checkboxes.length;

                if (count <= 0) {
                    return;
                }

                const selectedClasses = Array.from(checkboxes).map(function (checkbox) {
                    return checkbox.value || '';
                });

                const deleteQuestionsCount = selectedClasses.reduce(function (total, className) {
                    const toggle = document.querySelector('[data-delete-with-questions-toggle][data-class-name="' + className + '"]');

                    if (toggle && toggle.checked) {
                        return total + 1;
                    }

                    return total;
                }, 0);

                if (count === 1) {
                    form.dataset.confirm = deleteQuestionsCount > 0
                        ? 'Видалити файл вибраного сидера та пов’язані питання?'
                        : 'Видалити файл вибраного сидера?';
                } else {
                    form.dataset.confirm = deleteQuestionsCount > 0
                        ? 'Видалити файли ' + count + ' вибраних сидерів та пов’язані питання?'
                        : 'Видалити файли ' + count + ' вибраних сидерів?';
                }
            };

            updateAllBulkButtonStates();

            const confirmationModal = document.getElementById('seed-run-confirmation-modal');
            const confirmationMessage = confirmationModal ? confirmationModal.querySelector('[data-confirm-message]') : null;
            const confirmationAccept = confirmationModal ? confirmationModal.querySelector('[data-confirm-accept]') : null;
            const confirmationCancel = confirmationModal ? confirmationModal.querySelector('[data-confirm-cancel]') : null;
            const confirmationOverlay = confirmationModal ? confirmationModal.querySelector('[data-confirm-overlay]') : null;
            let pendingConfirmationForm = null;

            const closeConfirmationModal = function () {
                if (!confirmationModal) {
                    return;
                }

                confirmationModal.classList.add('hidden');
                confirmationModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                pendingConfirmationForm = null;
            };

            const openConfirmationModal = function (form, message) {
                if (!confirmationModal || !confirmationMessage || !confirmationAccept || !confirmationCancel) {
                    return false;
                }

                pendingConfirmationForm = form;
                confirmationMessage.textContent = message;
                confirmationModal.classList.remove('hidden');
                confirmationModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');

                window.setTimeout(function () {
                    confirmationAccept.focus();
                }, 0);

                return true;
            };

            if (confirmationAccept) {
                confirmationAccept.addEventListener('click', function () {
                    if (!pendingConfirmationForm) {
                        closeConfirmationModal();

                        return;
                    }

                    const formToSubmit = pendingConfirmationForm;
                    formToSubmit.dataset.confirmed = 'true';
                    closeConfirmationModal();
                    formToSubmit.requestSubmit();
                });
            }

            const cancelConfirmation = function () {
                if (pendingConfirmationForm) {
                    pendingConfirmationForm = null;
                }

                closeConfirmationModal();
            };

            if (confirmationCancel) {
                confirmationCancel.addEventListener('click', cancelConfirmation);
            }

            if (confirmationOverlay) {
                confirmationOverlay.addEventListener('click', cancelConfirmation);
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && confirmationModal && !confirmationModal.classList.contains('hidden')) {
                    event.preventDefault();
                    cancelConfirmation();
                }
            });

            document.querySelectorAll('form[data-preloader]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirmed === 'true') {
                        delete form.dataset.confirmed;

                        if (preloader) {
                            preloader.classList.remove('hidden');
                        }

                        return;
                    }

                    const confirmMessage = form.dataset.confirm;

                    if (confirmMessage) {
                        event.preventDefault();

                        const modalOpened = openConfirmationModal(form, confirmMessage);

                        if (!modalOpened && !window.confirm(confirmMessage)) {
                            return;
                        }

                        if (!modalOpened) {
                            if (preloader) {
                                preloader.classList.remove('hidden');
                            }

                            form.submit();
                        }

                        return;
                    }

                    if (preloader) {
                        preloader.classList.remove('hidden');
                    }
                });
            });

            document.querySelectorAll('[data-bulk-delete-button]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const scope = button.dataset.bulkScope || '';

                    prepareBulkConfirmMessage(scope);
                });
            });

            document.addEventListener('change', function (event) {
                const deleteToggle = event.target.closest('[data-delete-with-questions-toggle]');

                if (deleteToggle) {
                    const className = deleteToggle.dataset.className || '';
                    const scope = deleteToggle.dataset.bulkScope || '';
                    const checked = deleteToggle.checked;

                    syncDeleteWithQuestionsInputs(className, checked);

                    if (checked) {
                        const relatedCheckbox = findBulkCheckboxForClass(scope, className);

                        if (relatedCheckbox && !relatedCheckbox.checked) {
                            relatedCheckbox.checked = true;
                            updateBulkButtonState(scope);
                        }
                    } else {
                        updateBulkButtonState(scope);
                    }

                    return;
                }

                const checkbox = event.target.closest('[data-bulk-delete-checkbox]');

                if (!checkbox) {
                    return;
                }

                const scope = checkbox.dataset.bulkScope || '';

                if (!checkbox.checked) {
                    const className = checkbox.value || '';
                    const toggles = document.querySelectorAll('[data-delete-with-questions-toggle][data-class-name="' + className + '"]');

                    toggles.forEach(function (toggle) {
                        if (toggle.checked) {
                            toggle.checked = false;
                        }
                    });

                    syncDeleteWithQuestionsInputs(className, false);
                }

                updateBulkButtonState(scope);
            });

            document.querySelectorAll('[data-delete-with-questions-toggle]').forEach(function (toggle) {
                syncDeleteWithQuestionsInputs(toggle.dataset.className || '', toggle.checked);
            });

            const showFeedback = function (message, type = 'success') {
                if (!feedback) {
                    return;
                }

                feedback.textContent = message;
                feedback.classList.remove('hidden');
                feedback.classList.remove(...successClasses, ...errorClasses);

                const classes = type === 'error' ? errorClasses : successClasses;
                classes.forEach(function (className) {
                    feedback.classList.add(className);
                });

                window.clearTimeout(feedbackTimeout);
                feedbackTimeout = window.setTimeout(function () {
                    feedback.classList.add('hidden');
                }, 5000);
            };

            const updateToggleLabels = function (button, expanded) {
                const collapsedLabel = button.querySelector('[data-toggle-label-collapsed]');
                const expandedLabel = button.querySelector('[data-toggle-label-expanded]');

                if (collapsedLabel) {
                    collapsedLabel.classList.toggle('hidden', expanded);
                }

                if (expandedLabel) {
                    expandedLabel.classList.toggle('hidden', !expanded);
                }
            };

            const decrementNumericContent = function (elements) {
                elements.forEach(function (element) {
                    const current = parseInt(element.textContent.trim(), 10);

                    if (Number.isNaN(current)) {
                        return;
                    }

                    const nextValue = Math.max(0, current - 1);
                    element.textContent = nextValue;
                });
            };

            const hasPositiveCount = function (elements) {
                return Array.from(elements).some(function (element) {
                    const value = parseInt(element.textContent.trim(), 10);

                    return Number.isFinite(value) && value > 0;
                });
            };

            const cleanupEmptyGroups = function (seedRunId, categoryKey, sourceKey) {
                const sourceWrappers = document.querySelectorAll('[data-source-wrapper][data-seed-run-id="' + seedRunId + '"][data-category-key="' + categoryKey + '"][data-source-key="' + sourceKey + '"]');
                sourceWrappers.forEach(function (wrapper) {
                    const questionList = wrapper.querySelector('[data-source-questions]');

                    if (!questionList || questionList.querySelector('[data-question-container]')) {
                        return;
                    }

                    wrapper.remove();
                });

                const categoryWrappers = document.querySelectorAll('[data-category-wrapper][data-seed-run-id="' + seedRunId + '"][data-category-key="' + categoryKey + '"]');
                categoryWrappers.forEach(function (wrapper) {
                    if (wrapper.querySelector('[data-question-container]')) {
                        return;
                    }

                    if (wrapper.querySelector('[data-source-wrapper]')) {
                        return;
                    }

                    wrapper.remove();
                });

                const seederContent = document.querySelector('[data-seeder-content][data-seed-run-id="' + seedRunId + '"]');
                const seederSection = document.querySelector('[data-seeder-section][data-seed-run-id="' + seedRunId + '"]');
                const toggleButton = document.querySelector('[data-seeder-toggle][data-seed-run-id="' + seedRunId + '"]');
                const noQuestionsMessage = document.querySelector('[data-no-questions-message][data-seed-run-id="' + seedRunId + '"]');
                const seederCounts = document.querySelectorAll('[data-seed-run-question-count][data-seed-run-id="' + seedRunId + '"]');
                const remainingQuestions = hasPositiveCount(seederCounts);

                if (!remainingQuestions) {
                    if (seederContent) {
                        seederContent.innerHTML = '';
                        seederContent.classList.add('hidden');
                    }

                    if (seederSection) {
                        seederSection.classList.add('hidden');
                    }

                    if (toggleButton) {
                        toggleButton.dataset.loaded = 'false';
                        toggleButton.classList.add('hidden');
                        toggleButton.setAttribute('aria-expanded', 'false');
                        updateToggleLabels(toggleButton, false);

                        const icon = toggleButton.querySelector('[data-seeder-toggle-icon]');

                        if (icon) {
                            icon.classList.remove('rotate-180');
                        }
                    }

                    if (noQuestionsMessage) {
                        noQuestionsMessage.classList.remove('hidden');
                    }
                }
            };

            const handleFolderToggle = async function (button) {
                const folderNode = button.closest('[data-folder-node]');

                if (!folderNode) {
                    return;
                }

                const children = folderNode.querySelector('[data-folder-children]');

                if (!children) {
                    return;
                }

                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                const icon = button.querySelector('[data-folder-icon]');

                if (isExpanded) {
                    button.setAttribute('aria-expanded', 'false');

                    if (icon) {
                        icon.classList.add('-rotate-90');
                    }

                    children.classList.add('hidden');

                    return;
                }

                button.setAttribute('aria-expanded', 'true');

                if (icon) {
                    icon.classList.remove('-rotate-90');
                }

                children.classList.remove('hidden');

                if (folderNode.dataset.loaded === 'true') {
                    return;
                }

                const baseUrl = button.dataset.loadUrl;

                if (!baseUrl) {
                    return;
                }

                folderNode.dataset.loaded = 'loading';
                const depth = children.dataset.depth || '0';
                const path = button.dataset.folderPath || '';
                const params = new URLSearchParams({
                    path: path,
                    depth: depth,
                });

                children.innerHTML = '<p class="text-xs text-gray-500">Завантаження…</p>';

                try {
                    const response = await fetch(baseUrl + '?' + params.toString(), {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const payload = await response.json().catch(function () {
                        return null;
                    });

                    if (!response.ok) {
                        const message = payload && typeof payload.message === 'string'
                            ? payload.message
                            : 'Не вдалося завантажити вміст папки.';
                        throw new Error(message);
                    }

                    children.innerHTML = payload && typeof payload.html === 'string'
                        ? payload.html
                        : '';
                    folderNode.dataset.loaded = 'true';
                    updateAllBulkButtonStates();
                    children.querySelectorAll('[data-delete-with-questions-toggle]').forEach(function (toggle) {
                        syncDeleteWithQuestionsInputs(toggle.dataset.className || '', toggle.checked);
                    });
                } catch (error) {
                    const message = error && typeof error.message === 'string' && error.message
                        ? error.message
                        : 'Не вдалося завантажити вміст папки.';

                    children.innerHTML = '<p class="text-xs text-red-600">' + message + '</p>';
                    folderNode.dataset.loaded = 'error';
                    showFeedback(message, 'error');
                }
            };

            const handleSeederToggle = async function (button) {
                const seedRunId = button.dataset.seedRunId;
                const seederNode = button.closest('[data-seeder-node]');

                if (!seedRunId || !seederNode) {
                    return;
                }

                const content = seederNode.querySelector('[data-seeder-content][data-seed-run-id="' + seedRunId + '"]');

                if (!content) {
                    return;
                }

                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                const icon = button.querySelector('[data-seeder-toggle-icon]');

                if (isExpanded) {
                    button.setAttribute('aria-expanded', 'false');
                    updateToggleLabels(button, false);

                    if (icon) {
                        icon.classList.remove('rotate-180');
                    }

                    content.classList.add('hidden');

                    return;
                }

                button.setAttribute('aria-expanded', 'true');
                updateToggleLabels(button, true);

                if (icon) {
                    icon.classList.add('rotate-180');
                }

                content.classList.remove('hidden');

                if (button.dataset.loaded === 'true') {
                    return;
                }

                const url = button.dataset.loadUrl;

                if (!url) {
                    return;
                }

                button.dataset.loaded = 'loading';
                content.innerHTML = '<p class="text-xs text-gray-500">Завантаження…</p>';

                try {
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const payload = await response.json().catch(function () {
                        return null;
                    });

                    if (!response.ok) {
                        const message = payload && typeof payload.message === 'string'
                            ? payload.message
                            : 'Не вдалося завантажити питання.';
                        throw new Error(message);
                    }

                    content.innerHTML = payload && typeof payload.html === 'string'
                        ? payload.html
                        : '';
                    button.dataset.loaded = 'true';

                    if (!content.innerHTML.trim()) {
                        content.innerHTML = '<p class="text-xs text-gray-500">Питання для цього сидера не знайдені.</p>';
                    }
                } catch (error) {
                    const message = error && typeof error.message === 'string' && error.message
                        ? error.message
                        : 'Не вдалося завантажити питання.';

                    content.innerHTML = '<p class="text-xs text-red-600">' + message + '</p>';
                    button.dataset.loaded = 'error';
                    showFeedback(message, 'error');
                }
            };

            const handleSourceToggle = async function (button) {
                const seedRunId = button.dataset.seedRunId;
                const categoryKey = button.dataset.categoryKey;
                const sourceKey = button.dataset.sourceKey;
                const sourceWrapper = button.closest('[data-source-wrapper]');

                if (!seedRunId || !categoryKey || !sourceKey || !sourceWrapper) {
                    return;
                }

                const questionsContainer = sourceWrapper.querySelector('[data-source-questions][data-seed-run-id="' + seedRunId + '"][data-category-key="' + categoryKey + '"][data-source-key="' + sourceKey + '"]');

                if (!questionsContainer) {
                    return;
                }

                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                const icon = button.querySelector('[data-source-toggle-icon]');

                if (isExpanded) {
                    button.setAttribute('aria-expanded', 'false');
                    updateToggleLabels(button, false);

                    if (icon) {
                        icon.classList.remove('rotate-180');
                    }

                    questionsContainer.classList.add('hidden');

                    return;
                }

                button.setAttribute('aria-expanded', 'true');
                updateToggleLabels(button, true);

                if (icon) {
                    icon.classList.add('rotate-180');
                }

                questionsContainer.classList.remove('hidden');

                if (button.dataset.loaded === 'true') {
                    return;
                }

                const url = button.dataset.loadUrl;

                if (!url) {
                    return;
                }

                button.dataset.loaded = 'loading';
                questionsContainer.innerHTML = '<p class="text-xs text-gray-500">Завантаження…</p>';

                try {
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const payload = await response.json().catch(function () {
                        return null;
                    });

                    if (!response.ok) {
                        const message = payload && typeof payload.message === 'string'
                            ? payload.message
                            : 'Не вдалося завантажити питання.';
                        throw new Error(message);
                    }

                    questionsContainer.innerHTML = payload && typeof payload.html === 'string'
                        ? payload.html
                        : '';
                    button.dataset.loaded = 'true';

                    if (!questionsContainer.innerHTML.trim()) {
                        questionsContainer.innerHTML = '<p class="text-xs text-gray-500">Питань не знайдено.</p>';
                    }
                } catch (error) {
                    const message = error && typeof error.message === 'string' && error.message
                        ? error.message
                        : 'Не вдалося завантажити питання.';

                    questionsContainer.innerHTML = '<p class="text-xs text-red-600">' + message + '</p>';
                    button.dataset.loaded = 'error';
                    showFeedback(message, 'error');
                }
            };

            const handleQuestionToggle = async function (button) {
                const container = button.closest('[data-question-container]');

                if (!container) {
                    return;
                }

                const detailsContainer = container.querySelector('[data-question-details]');

                if (!detailsContainer) {
                    return;
                }

                const answersContainer = detailsContainer.querySelector('[data-question-answers]');
                const tagsContainer = detailsContainer.querySelector('[data-question-tags]');

                if (!answersContainer) {
                    return;
                }

                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                const icon = button.querySelector('[data-question-toggle-icon]');

                if (isExpanded) {
                    button.setAttribute('aria-expanded', 'false');
                    updateToggleLabels(button, false);

                    if (icon) {
                        icon.classList.remove('rotate-180');
                    }

                    detailsContainer.classList.add('hidden');

                    return;
                }

                button.setAttribute('aria-expanded', 'true');
                updateToggleLabels(button, true);

                if (icon) {
                    icon.classList.add('rotate-180');
                }

                detailsContainer.classList.remove('hidden');

                if (button.dataset.loaded === 'true') {
                    if (tagsContainer && tagsContainer.dataset.loaded !== 'true' && tagsContainer.dataset.loaded !== 'loading') {
                        loadQuestionTags(tagsContainer);
                    }

                    return;
                }

                const url = button.dataset.loadUrl;

                if (!url) {
                    answersContainer.innerHTML = '<p class="text-xs text-red-600">Посилання для завантаження не вказане.</p>';
                    button.dataset.loaded = 'error';

                    return;
                }

                button.dataset.loaded = 'loading';
                answersContainer.innerHTML = '<p class="text-xs text-gray-500">Завантаження…</p>';

                try {
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const payload = await response.json().catch(function () {
                        return null;
                    });

                    if (!response.ok) {
                        const message = payload && typeof payload.message === 'string'
                            ? payload.message
                            : 'Не вдалося завантажити варіанти.';
                        throw new Error(message);
                    }

                    answersContainer.innerHTML = payload && typeof payload.html === 'string'
                        ? payload.html
                        : '';
                    button.dataset.loaded = 'true';

                    if (!answersContainer.innerHTML.trim()) {
                        answersContainer.innerHTML = '<p class="text-xs text-gray-500">Варіанти відповіді не знайдені.</p>';
                    }

                    if (tagsContainer && tagsContainer.dataset.loaded !== 'true' && tagsContainer.dataset.loaded !== 'loading') {
                        loadQuestionTags(tagsContainer);
                    }
                } catch (error) {
                    const message = error && typeof error.message === 'string' && error.message
                        ? error.message
                        : 'Не вдалося завантажити варіанти.';

                    answersContainer.innerHTML = '<p class="text-xs text-red-600">' + message + '</p>';
                    button.dataset.loaded = 'error';
                    showFeedback(message, 'error');
                }
            };

            const loadQuestionTags = async function (tagsContainer) {
                if (!tagsContainer) {
                    return;
                }

                const url = tagsContainer.dataset.loadUrl;

                if (!url) {
                    tagsContainer.innerHTML = '<p class="text-xs text-red-600">Посилання для завантаження тегів не вказане.</p>';
                    tagsContainer.dataset.loaded = 'error';

                    return;
                }

                tagsContainer.dataset.loaded = 'loading';
                tagsContainer.innerHTML = '<p class="text-xs text-gray-500">Завантаження…</p>';

                try {
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const payload = await response.json().catch(function () {
                        return null;
                    });

                    if (!response.ok) {
                        const message = payload && typeof payload.message === 'string'
                            ? payload.message
                            : 'Не вдалося завантажити теги.';
                        throw new Error(message);
                    }

                    tagsContainer.innerHTML = payload && typeof payload.html === 'string'
                        ? payload.html
                        : '';
                    tagsContainer.dataset.loaded = 'true';

                    if (!tagsContainer.innerHTML.trim()) {
                        tagsContainer.innerHTML = '<p class="text-xs text-gray-500">Теги не знайдені.</p>';
                    }
                } catch (error) {
                    const message = error && typeof error.message === 'string' && error.message
                        ? error.message
                        : 'Не вдалося завантажити теги.';

                    tagsContainer.innerHTML = '<p class="text-xs text-red-600">' + message + '</p>';
                    tagsContainer.dataset.loaded = 'error';
                    showFeedback(message, 'error');
                }
            };

            document.addEventListener('click', function (event) {
                const folderButton = event.target.closest('[data-folder-toggle]');

                if (folderButton) {
                    event.preventDefault();
                    handleFolderToggle(folderButton);

                    return;
                }

                const seederButton = event.target.closest('[data-seeder-toggle]');

                if (seederButton) {
                    event.preventDefault();
                    handleSeederToggle(seederButton);

                    return;
                }

                const questionButton = event.target.closest('[data-question-toggle]');

                if (questionButton) {
                    event.preventDefault();
                    handleQuestionToggle(questionButton);

                    return;
                }

                const sourceButton = event.target.closest('[data-source-toggle]');

                if (sourceButton) {
                    event.preventDefault();
                    handleSourceToggle(sourceButton);
                }
            });

            document.addEventListener('submit', async function (event) {
                const form = event.target.closest('form[data-question-delete-form]');

                if (!form) {
                    return;
                }

                event.preventDefault();

                const confirmMessage = form.dataset.confirm;

                if (confirmMessage && !window.confirm(confirmMessage)) {
                    return;
                }

                const questionId = form.dataset.questionId;
                const seedRunId = form.dataset.seedRunId;
                const categoryKey = form.dataset.categoryKey;
                const sourceKey = form.dataset.sourceKey;
                const submitButton = form.querySelector('button[type="submit"]');

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-60', 'cursor-not-allowed');
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    const payload = await response.json().catch(function () {
                        return null;
                    });

                    if (!response.ok) {
                        const errorMessage = payload && typeof payload.message === 'string'
                            ? payload.message
                            : 'Не вдалося видалити питання.';
                        throw new Error(errorMessage);
                    }

                    const questionContainer = document.querySelector('[data-question-container][data-question-id="' + questionId + '"]');

                    if (questionContainer) {
                        questionContainer.remove();
                    }

                    decrementNumericContent(document.querySelectorAll('[data-seed-run-question-count][data-seed-run-id="' + seedRunId + '"]'));
                    decrementNumericContent(document.querySelectorAll('[data-category-question-count][data-seed-run-id="' + seedRunId + '"][data-category-key="' + categoryKey + '"]'));
                    decrementNumericContent(document.querySelectorAll('[data-source-question-count][data-seed-run-id="' + seedRunId + '"][data-category-key="' + categoryKey + '"][data-source-key="' + sourceKey + '"]'));

                    cleanupEmptyGroups(seedRunId, categoryKey, sourceKey);

                    const successMessage = payload && typeof payload.message === 'string'
                        ? payload.message
                        : 'Питання успішно видалено.';

                    showFeedback(successMessage);
                } catch (error) {
                    const fallbackErrorMessage = error && typeof error.message === 'string' && error.message
                        ? error.message
                        : 'Не вдалося видалити питання.';

                    showFeedback(fallbackErrorMessage, 'error');

                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.classList.remove('opacity-60', 'cursor-not-allowed');
                    }
                }
            });
        });
    </script>
@endsection
