(function () {
    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') || '' : '';

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-tech-editor]').forEach(function (root) {
            TechQuestionEditor(root, csrfToken);
        });
    });

    function TechQuestionEditor(root, csrf) {
        const saveUrl = root.dataset.saveUrl || null;
        const applyUrl = root.dataset.applyUrl || null;
        const dumpUrl = root.dataset.dumpUrl || null;
        const statusEl = root.querySelector('[data-role="status"]');
        const dumpViewer = root.querySelector('[data-role="dump-viewer"]');

        let saveTimer = null;
        let saving = false;
        let changeQueued = false;
        let applyRequested = false;
        let lastSaveFailed = false;

        bindBaseInputs();
        bindExistingRows();
        bindActions();
        refreshOptionChoices();
        ensureAllPlaceholders();

        function bindBaseInputs() {
            const baseFields = ['question', 'difficulty', 'level', 'flag', 'category_id', 'source_id'];
            baseFields.forEach(function (field) {
                const element = root.querySelector('[data-field="' + field + '"]');
                if (!element) {
                    return;
                }
                const eventName = element.tagName === 'SELECT' ? 'change' : 'input';
                element.addEventListener(eventName, queueSave);
            });
        }

        function bindExistingRows() {
            root.querySelectorAll('[data-collection="options"] [data-role="option"]').forEach(bindOptionRow);
            root.querySelectorAll('[data-collection="answers"] [data-role="answer"]').forEach(bindAnswerRow);
            root.querySelectorAll('[data-collection="verb-hints"] [data-role="verb-hint"]').forEach(bindVerbHintRow);
            root.querySelectorAll('[data-collection="variants"] [data-role="variant"]').forEach(bindVariantRow);
            root.querySelectorAll('[data-collection="hints"] [data-role="hint"]').forEach(bindHintRow);

            root.querySelectorAll('[data-collection]').forEach(function (container) {
                if (container.querySelector('[data-role]')) {
                    removePlaceholder(container);
                }
            });
        }

        function bindActions() {
            const addOptionButton = root.querySelector('[data-action="add-option"]');
            const addAnswerButton = root.querySelector('[data-action="add-answer"]');
            const addVerbHintButton = root.querySelector('[data-action="add-verb-hint"]');
            const addVariantButton = root.querySelector('[data-action="add-variant"]');
            const addHintButton = root.querySelector('[data-action="add-hint"]');
            const applyButton = root.querySelector('[data-action="apply"]');
            const refreshDumpButton = root.querySelector('[data-action="refresh-dump"]');

            if (addOptionButton) {
                addOptionButton.addEventListener('click', function () {
                    const container = root.querySelector('[data-collection="options"]');
                    const row = cloneTemplate('option');
                    if (!container || !row) {
                        return;
                    }
                    removePlaceholder(container);
                    container.appendChild(row);
                    bindOptionRow(row);
                    refreshOptionChoices();
                    queueSave();
                    const optionInput = row.querySelector('[data-field="option-text"]');
                    if (optionInput) {
                        optionInput.focus();
                    }
                });
            }

            if (addAnswerButton) {
                addAnswerButton.addEventListener('click', function () {
                    const container = root.querySelector('[data-collection="answers"]');
                    const row = cloneTemplate('answer');
                    if (!container || !row) {
                        return;
                    }
                    removePlaceholder(container);
                    container.appendChild(row);
                    bindAnswerRow(row);
                    refreshOptionChoices();
                    queueSave();
                    const markerInput = row.querySelector('[data-field="answer-marker"]');
                    if (markerInput) {
                        markerInput.focus();
                    }
                });
            }

            if (addVerbHintButton) {
                addVerbHintButton.addEventListener('click', function () {
                    const container = root.querySelector('[data-collection="verb-hints"]');
                    const row = cloneTemplate('verb-hint');
                    if (!container || !row) {
                        return;
                    }
                    removePlaceholder(container);
                    container.appendChild(row);
                    bindVerbHintRow(row);
                    refreshOptionChoices();
                    queueSave();
                    const markerInput = row.querySelector('[data-field="verb-marker"]');
                    if (markerInput) {
                        markerInput.focus();
                    }
                });
            }

            if (addVariantButton) {
                addVariantButton.addEventListener('click', function () {
                    const container = root.querySelector('[data-collection="variants"]');
                    const row = cloneTemplate('variant');
                    if (!container || !row) {
                        return;
                    }
                    removePlaceholder(container);
                    container.appendChild(row);
                    bindVariantRow(row);
                    queueSave();
                    const textarea = row.querySelector('textarea');
                    if (textarea) {
                        textarea.focus();
                    }
                });
            }

            if (addHintButton) {
                addHintButton.addEventListener('click', function () {
                    const container = root.querySelector('[data-collection="hints"]');
                    const row = cloneTemplate('hint');
                    if (!container || !row) {
                        return;
                    }
                    removePlaceholder(container);
                    container.appendChild(row);
                    bindHintRow(row);
                    queueSave();
                    const providerInput = row.querySelector('[data-field="hint-provider"]');
                    if (providerInput) {
                        providerInput.focus();
                    }
                });
            }

            if (applyButton) {
                applyButton.addEventListener('click', function () {
                    if (!applyUrl) {
                        return;
                    }

                    if (saveTimer) {
                        window.clearTimeout(saveTimer);
                        saveTimer = null;
                    }

                    if (saving) {
                        applyRequested = true;
                        setStatus('Очікуємо завершення збереження...', 'info');
                        return;
                    }

                    if (changeQueued) {
                        applyRequested = true;
                        triggerSave();
                    } else {
                        applyChanges();
                    }
                });
            }

            if (refreshDumpButton) {
                refreshDumpButton.addEventListener('click', function () {
                    if (!dumpUrl) {
                        return;
                    }
                    setStatus('Оновлення дампу...', 'info');
                    fetch(dumpUrl, {
                        headers: { 'Accept': 'application/json' },
                    })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('failed');
                            }
                            return response.json();
                        })
                        .then(function (data) {
                            updateDump(data);
                            setStatus('Дамп оновлено', 'success');
                        })
                        .catch(function (error) {
                            console.error(error);
                            setStatus('Не вдалося завантажити дамп', 'error');
                        });
                });
            }
        }

        function queueSave() {
            changeQueued = true;
            if (saveTimer) {
                window.clearTimeout(saveTimer);
            }
            saveTimer = window.setTimeout(function () {
                saveTimer = null;
                if (!saving) {
                    triggerSave();
                }
            }, 600);
        }

        function triggerSave() {
            if (!saveUrl) {
                return;
            }

            if (saveTimer) {
                window.clearTimeout(saveTimer);
                saveTimer = null;
            }

            const payload = { question: collectData() };
            changeQueued = false;
            saving = true;
            lastSaveFailed = false;
            setStatus('Збереження...', 'info');

            fetch(saveUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(payload),
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('failed');
                    }
                    return response.json();
                })
                .then(function (data) {
                    setStatus('Чернетку збережено', 'success');
                    updateDump(data.draft || null);
                    updateRowIds(data.draft && data.draft.question ? data.draft.question : null);
                })
                .catch(function (error) {
                    console.error(error);
                    lastSaveFailed = true;
                    changeQueued = true;
                    setStatus('Не вдалося зберегти зміни', 'error');
                })
                .finally(function () {
                    saving = false;

                    if (changeQueued && !lastSaveFailed) {
                        triggerSave();
                        return;
                    }

                    if (applyRequested) {
                        const shouldApply = !lastSaveFailed;
                        applyRequested = false;
                        if (shouldApply) {
                            applyChanges();
                        }
                    }
                });
        }

        function collectData() {
            const data = {
                id: root.dataset.questionId ? Number(root.dataset.questionId) : null,
                uuid: root.dataset.questionUuid || null,
                question: valueOf('[data-field="question"]'),
                difficulty: valueOf('[data-field="difficulty"]'),
                level: valueOf('[data-field="level"]'),
                flag: valueOf('[data-field="flag"]'),
                category_id: valueOf('[data-field="category_id"]'),
                source_id: valueOf('[data-field="source_id"]'),
                options: [],
                answers: [],
                verb_hints: [],
                variants: [],
                hints: [],
            };

            root.querySelectorAll('[data-collection="options"] [data-role="option"]').forEach(function (row) {
                const optionInput = row.querySelector('[data-field="option-text"]');
                data.options.push({
                    id: row.dataset.id ? Number(row.dataset.id) : null,
                    option: optionInput ? optionInput.value : '',
                });
            });

            root.querySelectorAll('[data-collection="answers"] [data-role="answer"]').forEach(function (row) {
                data.answers.push({
                    id: row.dataset.id ? Number(row.dataset.id) : null,
                    marker: valueFrom(row, '[data-field="answer-marker"]'),
                    option: valueFrom(row, '[data-field="answer-option"]'),
                });
            });

            root.querySelectorAll('[data-collection="verb-hints"] [data-role="verb-hint"]').forEach(function (row) {
                data.verb_hints.push({
                    id: row.dataset.id ? Number(row.dataset.id) : null,
                    marker: valueFrom(row, '[data-field="verb-marker"]'),
                    option: valueFrom(row, '[data-field="verb-option"]'),
                });
            });

            root.querySelectorAll('[data-collection="variants"] [data-role="variant"]').forEach(function (row) {
                data.variants.push({
                    id: row.dataset.id ? Number(row.dataset.id) : null,
                    text: valueFrom(row, '[data-field="variant-text"]'),
                });
            });

            root.querySelectorAll('[data-collection="hints"] [data-role="hint"]').forEach(function (row) {
                data.hints.push({
                    id: row.dataset.id ? Number(row.dataset.id) : null,
                    provider: valueFrom(row, '[data-field="hint-provider"]'),
                    locale: valueFrom(row, '[data-field="hint-locale"]'),
                    hint: valueFrom(row, '[data-field="hint-text"]'),
                });
            });

            return data;
        }

        function valueOf(selector) {
            const element = root.querySelector(selector);
            return element ? element.value : '';
        }

        function valueFrom(parent, selector) {
            const element = parent.querySelector(selector);
            return element ? element.value : '';
        }

        function setStatus(message, type) {
            if (!statusEl) {
                return;
            }

            statusEl.textContent = message;
            statusEl.classList.remove('text-emerald-600', 'text-red-600', 'text-stone-500');

            switch (type) {
                case 'success':
                    statusEl.classList.add('text-emerald-600');
                    break;
                case 'error':
                    statusEl.classList.add('text-red-600');
                    break;
                default:
                    statusEl.classList.add('text-stone-500');
                    break;
            }
        }

        function updateDump(draft) {
            if (!dumpViewer || !draft) {
                return;
            }

            try {
                dumpViewer.textContent = JSON.stringify(draft, null, 2);
            } catch (error) {
                console.error(error);
            }
        }

        function updateRowIds(questionData) {
            if (!questionData) {
                return;
            }

            assignIds('options', questionData.options || []);
            assignIds('answers', questionData.answers || []);
            assignIds('verb-hints', questionData.verb_hints || []);
            assignIds('variants', questionData.variants || []);
            assignIds('hints', questionData.hints || []);
            refreshOptionChoices();
            restoreSelections('answers');
            restoreSelections('verb-hints');
        }

        function assignIds(collectionName, items) {
            const container = root.querySelector('[data-collection="' + collectionName + '"]');
            if (!container) {
                return;
            }
            const rows = Array.from(container.querySelectorAll('[data-role]'));
            rows.forEach(function (row, index) {
                const item = items[index];
                row.dataset.id = item && item.id ? item.id : '';
                if (collectionName === 'answers' || collectionName === 'verb-hints') {
                    row.dataset.selectedOption = item && item.option ? item.option : '';
                }
            });
        }

        function refreshOptionChoices() {
            const values = Array.from(root.querySelectorAll('[data-collection="options"] [data-field="option-text"]'))
                .map(function (input) { return input.value || ''; })
                .filter(function (text) { return text.trim() !== ''; });
            const uniqueValues = Array.from(new Set(values));

            root.querySelectorAll('select[data-field="answer-option"], select[data-field="verb-option"]').forEach(function (select) {
                const current = select.value;
                select.innerHTML = '';
                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                select.appendChild(emptyOption);

                uniqueValues.forEach(function (text) {
                    const option = document.createElement('option');
                    option.value = text;
                    option.textContent = text;
                    select.appendChild(option);
                });

                if (current && uniqueValues.includes(current)) {
                    select.value = current;
                }
            });
        }

        function bindOptionRow(row) {
            const input = row.querySelector('[data-field="option-text"]');
            const removeButton = row.querySelector('[data-action="remove-option"]');

            if (input) {
                input.addEventListener('input', function () {
                    refreshOptionChoices();
                    queueSave();
                });
            }

            if (removeButton) {
                removeButton.addEventListener('click', function () {
                    const container = row.closest('[data-collection]');
                    row.remove();
                    refreshOptionChoices();
                    ensurePlaceholder(container);
                    queueSave();
                });
            }
        }

        function bindAnswerRow(row) {
            const marker = row.querySelector('[data-field="answer-marker"]');
            const option = row.querySelector('[data-field="answer-option"]');
            const removeButton = row.querySelector('[data-action="remove-answer"]');

            if (marker) {
                marker.addEventListener('input', queueSave);
            }
            if (option) {
                option.addEventListener('change', queueSave);
            }
            if (removeButton) {
                removeButton.addEventListener('click', function () {
                    const container = row.closest('[data-collection]');
                    row.remove();
                    ensurePlaceholder(container);
                    queueSave();
                });
            }
        }

        function bindVerbHintRow(row) {
            const marker = row.querySelector('[data-field="verb-marker"]');
            const option = row.querySelector('[data-field="verb-option"]');
            const removeButton = row.querySelector('[data-action="remove-verb-hint"]');

            if (marker) {
                marker.addEventListener('input', queueSave);
            }
            if (option) {
                option.addEventListener('change', queueSave);
            }
            if (removeButton) {
                removeButton.addEventListener('click', function () {
                    const container = row.closest('[data-collection]');
                    row.remove();
                    ensurePlaceholder(container);
                    queueSave();
                });
            }
        }

        function bindVariantRow(row) {
            const textarea = row.querySelector('[data-field="variant-text"]');
            const removeButton = row.querySelector('[data-action="remove-variant"]');

            if (textarea) {
                textarea.addEventListener('input', queueSave);
            }
            if (removeButton) {
                removeButton.addEventListener('click', function () {
                    const container = row.closest('[data-collection]');
                    row.remove();
                    ensurePlaceholder(container);
                    queueSave();
                });
            }
        }

        function bindHintRow(row) {
            const provider = row.querySelector('[data-field="hint-provider"]');
            const locale = row.querySelector('[data-field="hint-locale"]');
            const hint = row.querySelector('[data-field="hint-text"]');
            const removeButton = row.querySelector('[data-action="remove-hint"]');

            if (provider) {
                provider.addEventListener('input', queueSave);
            }
            if (locale) {
                locale.addEventListener('input', queueSave);
            }
            if (hint) {
                hint.addEventListener('input', queueSave);
            }
            if (removeButton) {
                removeButton.addEventListener('click', function () {
                    const container = row.closest('[data-collection]');
                    row.remove();
                    ensurePlaceholder(container);
                    queueSave();
                });
            }
        }

        function cloneTemplate(name) {
            const template = root.querySelector('template[data-template="' + name + '"]');
            if (!template || !template.content || !template.content.firstElementChild) {
                return null;
            }
            return template.content.firstElementChild.cloneNode(true);
        }

        function applyChanges() {
            if (!applyUrl) {
                return;
            }
            setStatus('Застосування...', 'info');
            fetch(applyUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('failed');
                    }
                    return response.json();
                })
                .then(function (data) {
                    const questionData = data.draft && data.draft.question ? data.draft.question : null;
                    if (questionData) {
                        renderFromData(questionData);
                    }
                    updateDump(data.draft || null);
                    changeQueued = false;
                    setStatus('Зміни застосовано', 'success');
                })
                .catch(function (error) {
                    console.error(error);
                    setStatus('Не вдалося застосувати зміни', 'error');
                });
        }

        function renderFromData(questionData) {
            if (!questionData) {
                return;
            }

            ['question', 'difficulty', 'level', 'flag', 'category_id', 'source_id'].forEach(function (field) {
                const element = root.querySelector('[data-field="' + field + '"]');
                if (element) {
                    element.value = questionData[field] || '';
                }
            });

            rebuildCollection('options', questionData.options || []);
            rebuildCollection('answers', questionData.answers || []);
            rebuildCollection('verb-hints', questionData.verb_hints || []);
            rebuildCollection('variants', questionData.variants || []);
            rebuildCollection('hints', questionData.hints || []);

            refreshOptionChoices();
            restoreSelections('answers');
            restoreSelections('verb-hints');
            ensureAllPlaceholders();
        }

        function rebuildCollection(name, items) {
            const container = root.querySelector('[data-collection="' + name + '"]');
            if (!container) {
                return;
            }

            container.innerHTML = '';

            if (!Array.isArray(items) || items.length === 0) {
                ensurePlaceholder(container);
                return;
            }

            const templateNameMap = {
                options: 'option',
                answers: 'answer',
                'verb-hints': 'verb-hint',
                variants: 'variant',
                hints: 'hint',
            };

            const templateName = templateNameMap[name];

            items.forEach(function (item) {
                const row = cloneTemplate(templateName);
                if (!row) {
                    return;
                }

                row.dataset.id = item && item.id ? item.id : '';

                if (name === 'options') {
                    const input = row.querySelector('[data-field="option-text"]');
                    if (input) {
                        input.value = item && item.option ? item.option : '';
                    }
                    bindOptionRow(row);
                } else if (name === 'answers') {
                    const marker = row.querySelector('[data-field="answer-marker"]');
                    if (marker) {
                        marker.value = item && item.marker ? item.marker : '';
                    }
                    row.dataset.selectedOption = item && item.option ? item.option : '';
                    bindAnswerRow(row);
                } else if (name === 'verb-hints') {
                    const marker = row.querySelector('[data-field="verb-marker"]');
                    if (marker) {
                        marker.value = item && item.marker ? item.marker : '';
                    }
                    row.dataset.selectedOption = item && item.option ? item.option : '';
                    bindVerbHintRow(row);
                } else if (name === 'variants') {
                    const textarea = row.querySelector('[data-field="variant-text"]');
                    if (textarea) {
                        textarea.value = item && item.text ? item.text : '';
                    }
                    bindVariantRow(row);
                } else if (name === 'hints') {
                    const provider = row.querySelector('[data-field="hint-provider"]');
                    const locale = row.querySelector('[data-field="hint-locale"]');
                    const hint = row.querySelector('[data-field="hint-text"]');
                    if (provider) {
                        provider.value = item && item.provider ? item.provider : '';
                    }
                    if (locale) {
                        locale.value = item && item.locale ? item.locale : '';
                    }
                    if (hint) {
                        hint.value = item && item.hint ? item.hint : '';
                    }
                    bindHintRow(row);
                }

                container.appendChild(row);
            });
        }

        function restoreSelections(collectionName) {
            const selector = collectionName === 'answers' ? '[data-field="answer-option"]' : '[data-field="verb-option"]';
            root.querySelectorAll('[data-collection="' + collectionName + '"] [data-role]').forEach(function (row) {
                const select = row.querySelector(selector);
                const value = row.dataset.selectedOption || '';
                if (select) {
                    select.value = value;
                }
                delete row.dataset.selectedOption;
            });
        }

        function ensurePlaceholder(container) {
            if (!container) {
                return;
            }
            removePlaceholder(container);
            const hasRows = container.querySelector('[data-role]');
            if (hasRows) {
                return;
            }
            const text = container.dataset.emptyText || '';
            if (!text) {
                return;
            }
            const placeholder = document.createElement('p');
            placeholder.dataset.role = 'empty-message';
            placeholder.className = 'text-sm text-stone-500';
            placeholder.textContent = text;
            container.appendChild(placeholder);
        }

        function removePlaceholder(container) {
            if (!container) {
                return;
            }
            container.querySelectorAll('[data-role="empty-message"]').forEach(function (element) {
                element.remove();
            });
        }

        function ensureAllPlaceholders() {
            root.querySelectorAll('[data-collection]').forEach(function (container) {
                ensurePlaceholder(container);
            });
        }
    }
})();
