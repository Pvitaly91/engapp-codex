function createQuestionsManager() {
    const container = document.getElementById('questions-list');
    const shuffleButton = document.getElementById('shuffle-questions');
    const orderInput = document.getElementById('questions-order-input');
    const saveForm = document.getElementById('save-test-form');

    const getItems = () => Array.from(container ? container.querySelectorAll('[data-question-id]') : []);

    const updateNumbers = () => {
        const items = getItems();
        items.forEach((item, index) => {
            const numberEl = item.querySelector('.question-number');
            if (numberEl) {
                numberEl.textContent = `${index + 1}.`;
            }
        });

        const countLabel = document.getElementById('question-count');
        if (countLabel) {
            countLabel.textContent = `Кількість питань: ${items.length}`;
        }

        if (container?.dataset?.keepVisible === 'true') {
            return;
        }

        // Show/hide forms based on question count
        const checkForm = container?.closest('form');
        const saveFormContainer = document.querySelector('#save-test-form')?.closest('.bg-white');
        if (items.length === 0) {
            if (checkForm) checkForm.style.display = 'none';
            if (saveFormContainer) saveFormContainer.style.display = 'none';
        } else {
            if (checkForm) checkForm.style.display = '';
            if (saveFormContainer) saveFormContainer.style.display = '';
        }
    };

    const updateOrderInput = () => {
        if (!orderInput) {
            return;
        }

        const order = getItems().map(item => item.dataset.questionSave);
        orderInput.value = JSON.stringify(order);
    };

    const appendHtml = (html) => {
        if (!container || !html) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;

        const newItems = Array.from(wrapper.querySelectorAll('.question-item'));
        newItems.forEach(item => container.appendChild(item));

        updateNumbers();
        updateOrderInput();

        // Show forms when questions are added
        const checkForm = container.closest('form');
        const saveFormContainer = document.querySelector('#save-test-form')?.closest('.bg-white');
        if (checkForm) checkForm.style.display = '';
        if (saveFormContainer) saveFormContainer.style.display = '';
    };

    const showDeleteModal = (questionItem) => {
        const modal = document.getElementById('question-delete-confirmation-modal');
        const overlay = modal?.querySelector('[data-modal-overlay]');
        const confirmBtn = modal?.querySelector('[data-modal-confirm]');
        const cancelBtn = modal?.querySelector('[data-modal-cancel]');

        if (!modal || !overlay || !confirmBtn || !cancelBtn) {
            return;
        }

        const handleConfirm = () => {
            questionItem.remove();
            updateNumbers();
            updateOrderInput();
            closeModal();
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            confirmBtn.removeEventListener('click', handleConfirm);
            cancelBtn.removeEventListener('click', closeModal);
            overlay.removeEventListener('click', closeModal);
        };

        confirmBtn.addEventListener('click', handleConfirm);
        cancelBtn.addEventListener('click', closeModal);
        overlay.addEventListener('click', closeModal);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const removeQuestion = (button) => {
        const questionItem = button.closest('.question-item');
        if (!questionItem) {
            return;
        }

        showDeleteModal(questionItem);
    };

    const init = () => {
        if (!container) {
            return;
        }

        // Add event listener for remove buttons
        container.addEventListener('click', (event) => {
            const removeBtn = event.target.closest('.remove-question-btn');
            if (removeBtn) {
                event.preventDefault();
                removeQuestion(removeBtn);
            }
        });

        if (shuffleButton) {
            shuffleButton.addEventListener('click', () => {
                const items = getItems();

                if (items.length <= 1) {
                    return;
                }

                for (let i = items.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [items[i], items[j]] = [items[j], items[i]];
                }

                items.forEach(item => container.appendChild(item));
                updateNumbers();
                updateOrderInput();
            });
        }

        if (saveForm) {
            saveForm.addEventListener('submit', updateOrderInput);
        }

        window.addEventListener('grammar-test:add-questions', (event) => {
            appendHtml(event.detail && event.detail.html ? event.detail.html : '');
        });

        updateNumbers();
        updateOrderInput();
    };

    return { init, updateNumbers, updateOrderInput, appendHtml };
}

function questionPicker(searchUrl, renderUrl, config = {}) {
    return {
        open: false,
        query: '',
        loading: false,
        results: [],
        selected: [],
        filters: {
            seederClasses: [],
            sources: [],
            levels: [],
            tags: [],
            aggregatedTags: [],
        },
        onlyAiV2: false,
        appliedFilters: null,
        filtersDirty: false,
        init() {
            this.$watch('query', () => {
                if (this.open) {
                    this.fetchResults();
                }
            });

            this.$watch('open', (value) => {
                if (value) {
                    this.fetchResults();
                }
            });

            ['filters.seederClasses', 'filters.sources', 'filters.levels', 'filters.tags', 'filters.aggregatedTags']
                .forEach((path) => {
                    this.$watch(path, () => {
                        this.markFiltersDirty();
                    });
                });

            this.$watch('onlyAiV2', () => {
                this.markFiltersDirty();
            });

            this.appliedFilters = this.snapshotFilters();
        },
        toggleFilter(key, value) {
            const current = Array.isArray(this.filters[key]) ? [...this.filters[key]] : [];
            const idx = current.indexOf(value);

            if (idx === -1) {
                current.push(value);
            } else {
                current.splice(idx, 1);
            }

            this.filters[key] = current;
            this.markFiltersDirty();
        },
        markFiltersDirty() {
            this.filtersDirty = true;
        },
        snapshotFilters() {
            return {
                seederClasses: [...(this.filters.seederClasses || [])],
                sources: [...(this.filters.sources || [])],
                levels: [...(this.filters.levels || [])],
                tags: [...(this.filters.tags || [])],
                aggregatedTags: [...(this.filters.aggregatedTags || [])],
                onlyAiV2: !!this.onlyAiV2,
            };
        },
        applyFilters() {
            this.appliedFilters = this.snapshotFilters();
            this.filtersDirty = false;

            if (this.open) {
                this.fetchResults();
            }
        },
        resetFilters() {
            this.filters = {
                seederClasses: [],
                sources: [],
                levels: [],
                tags: [],
                aggregatedTags: [],
            };
            this.onlyAiV2 = false;
            this.applyFilters();
        },
        close() {
            this.open = false;
        },
        selectionLabel() {
            if (!this.selected.length) {
                return 'Не вибрано';
            }

            return `Вибрано: ${this.selected.length}`;
        },
        normalizedTerms() {
            return String(this.query || '')
                .toLowerCase()
                .split(/\s+/)
                .map(t => t.trim())
                .filter(Boolean);
        },
        escapeHtml(text = '') {
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },
        escapeRegExp(string) {
            return String(string).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        },
        highlightText(text = '') {
            return this.applyTermHighlight(this.escapeHtml(text));
        },
        applyTermHighlight(html = '') {
            const terms = this.normalizedTerms();

            if (!terms.length || !html) {
                return html;
            }

            let highlighted = html;

            terms.forEach(term => {
                const regex = new RegExp(`(${this.escapeRegExp(term)})`, 'gi');
                highlighted = highlighted.replace(regex, '<mark>$1</mark>');
            });

            return highlighted;
        },
        highlightAnswers(html = '', answers = []) {
            if (!html) {
                return html;
            }

            let rendered = html;

            (Array.isArray(answers) ? answers : []).forEach(answer => {
                const text = this.escapeHtml(answer?.text || '');

                if (!text) {
                    return;
                }

                const regex = new RegExp(`(${this.escapeRegExp(text)})`, 'gi');
                rendered = rendered.replace(
                    regex,
                    '<span class="bg-amber-100 text-amber-900 font-semibold px-1 rounded">$1</span>'
                );
            });

            return rendered;
        },
        renderQuestionPreview(item) {
            const base = this.escapeHtml(item?.rendered_question || item?.question || '');
            const withAnswers = this.highlightAnswers(base, item?.answers || []);

            return this.applyTermHighlight(withAnswers);
        },
        isSelected(item) {
            return this.selected.some(sel => sel.id === item.id && sel.uuid === item.uuid);
        },
        toggle(item) {
            if (this.isSelected(item)) {
                this.selected = this.selected.filter(sel => !(sel.id === item.id && sel.uuid === item.uuid));
            } else {
                this.selected = [...this.selected, item];
            }
        },
        fetchResults() {
            if (this.filtersDirty) {
                return;
            }

            this.loading = true;
            const params = new URLSearchParams();
            params.set('q', this.query || '');

            const activeFilters = this.appliedFilters || this.snapshotFilters();
            const filterKeys = ['seeder_classes', 'sources', 'levels', 'tags', 'aggregated_tags'];
            const stateKeys = ['seederClasses', 'sources', 'levels', 'tags', 'aggregatedTags'];

            filterKeys.forEach((param, index) => {
                const stateKey = stateKeys[index];
                const values = activeFilters[stateKey] || [];

                values.forEach(value => params.append(`${param}[]`, value));
            });

            if (activeFilters.onlyAiV2) {
                params.set('only_ai_v2', '1');
            }

            fetch(`${searchUrl}?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    this.results = Array.isArray(data.items) ? data.items : [];
                })
                .catch(() => {
                    this.results = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        apply() {
            if (!this.selected.length) {
                return;
            }

            this.loading = true;

            const payload = {
                question_ids: this.selected.map(item => item.id),
                question_uuids: this.selected.map(item => item.uuid).filter(Boolean),
                manual_input: !!config.manualInput,
                autocomplete_input: !!config.autocompleteInput,
                builder_input: !!config.builderInput,
                check_one_input: !!config.checkOneInput,
                save_payload_key: config.savePayloadKey || 'uuid',
            };

            fetch(renderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(payload),
            })
                .then(res => res.json())
                .then(data => {
                    window.dispatchEvent(new CustomEvent('grammar-test:add-questions', {
                        detail: { html: (data && data.html) || '' },
                    }));
                    this.selected = [];
                    this.query = '';
                    this.close();
                })
                .catch(() => {})
                .finally(() => {
                    this.loading = false;
                });
        },
    };
}
