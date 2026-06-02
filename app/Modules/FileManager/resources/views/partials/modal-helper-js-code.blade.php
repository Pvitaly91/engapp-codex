(function () {
    if (window.FileManagerModal && typeof window.FileManagerModal.confirm === 'function') {
        return;
    }

    const STYLE_ID = 'fm-global-modal-style';
    const ROOT_ID = 'fm-global-modal-root';
    const state = {
        root: null,
        resolver: null,
        keydownHandler: null,
        lastFocused: null,
    };

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function ensureStyles() {
        if (document.getElementById(STYLE_ID)) {
            return;
        }

        const style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent = `
            body.fm-modal-open {
                overflow: hidden;
            }
            .fm-modal {
                position: fixed;
                inset: 0;
                z-index: 1200;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                box-sizing: border-box;
            }
            .fm-modal.is-visible {
                display: flex;
            }
            .fm-modal__backdrop {
                position: absolute;
                inset: 0;
                background: rgba(15, 23, 42, 0.68);
                backdrop-filter: blur(6px);
            }
            .fm-modal__panel {
                position: relative;
                z-index: 1;
                width: min(100%, 32rem);
                border: 1px solid rgba(148, 163, 184, 0.28);
                border-radius: 1.5rem;
                background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
                box-shadow: 0 32px 96px -48px rgba(15, 23, 42, 0.72);
                overflow: hidden;
            }
            .fm-modal__header {
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                padding: 1.25rem 1.25rem 0;
            }
            .fm-modal__icon {
                display: inline-flex;
                height: 3rem;
                width: 3rem;
                flex-shrink: 0;
                align-items: center;
                justify-content: center;
                border-radius: 1rem;
                font-size: 1.1rem;
                font-weight: 700;
            }
            .fm-modal__icon[data-variant="primary"] {
                background: #dbeafe;
                color: #1d4ed8;
            }
            .fm-modal__icon[data-variant="danger"] {
                background: #fee2e2;
                color: #b91c1c;
            }
            .fm-modal__close {
                margin-left: auto;
                border: 0;
                background: transparent;
                color: #64748b;
                cursor: pointer;
                font-size: 1.2rem;
                line-height: 1;
                padding: 0.25rem;
            }
            .fm-modal__body {
                padding: 0 1.25rem 1.25rem;
            }
            .fm-modal__title {
                margin: 0;
                font-size: 1.125rem;
                line-height: 1.35;
                color: #0f172a;
            }
            .fm-modal__message {
                margin: 0.5rem 0 0;
                color: #475569;
                font-size: 0.95rem;
                line-height: 1.6;
            }
            .fm-modal__actions {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 0.75rem;
                padding: 0 1.25rem 1.25rem;
            }
            .fm-modal__button {
                appearance: none;
                border: 1px solid #cbd5e1;
                border-radius: 0.95rem;
                background: #ffffff;
                color: #0f172a;
                padding: 0.75rem 1rem;
                font-size: 0.92rem;
                font-weight: 600;
                line-height: 1.2;
                cursor: pointer;
                transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
            }
            .fm-modal__button:hover {
                background: #f8fafc;
                border-color: #94a3b8;
            }
            .fm-modal__button[data-variant="primary"] {
                background: #0f766e;
                border-color: #0f766e;
                color: #ffffff;
            }
            .fm-modal__button[data-variant="primary"]:hover {
                background: #115e59;
                border-color: #115e59;
            }
            .fm-modal__button[data-variant="danger"] {
                background: #dc2626;
                border-color: #dc2626;
                color: #ffffff;
            }
            .fm-modal__button[data-variant="danger"]:hover {
                background: #b91c1c;
                border-color: #b91c1c;
            }
            @media (max-width: 640px) {
                .fm-modal__actions {
                    flex-direction: column-reverse;
                }
                .fm-modal__button {
                    width: 100%;
                }
            }
        `;

        document.head.appendChild(style);
    }

    function ensureRoot() {
        if (state.root && document.body.contains(state.root)) {
            return state.root;
        }

        const root = document.createElement('div');
        root.id = ROOT_ID;
        root.innerHTML = `
            <div class="fm-modal" data-fm-modal hidden>
                <div class="fm-modal__backdrop" data-fm-modal-cancel></div>
                <div class="fm-modal__panel" role="dialog" aria-modal="true" aria-labelledby="fm-modal-title">
                    <div class="fm-modal__header">
                        <div class="fm-modal__icon" data-fm-modal-icon data-variant="primary">?</div>
                        <div class="fm-modal__body">
                            <h3 class="fm-modal__title" id="fm-modal-title" data-fm-modal-title>Підтвердження дії</h3>
                            <p class="fm-modal__message" data-fm-modal-message>Підтвердіть виконання дії.</p>
                        </div>
                        <button type="button" class="fm-modal__close" data-fm-modal-close aria-label="Закрити">×</button>
                    </div>
                    <div class="fm-modal__actions">
                        <button type="button" class="fm-modal__button" data-fm-modal-cancel>Скасувати</button>
                        <button type="button" class="fm-modal__button" data-fm-modal-confirm data-variant="primary">Продовжити</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(root);

        root.querySelectorAll('[data-fm-modal-cancel], [data-fm-modal-close]').forEach((node) => {
            node.addEventListener('click', () => close(false));
        });

        root.querySelector('[data-fm-modal-confirm]')?.addEventListener('click', () => close(true));

        state.root = root;
        return root;
    }

    function getElements() {
        const root = ensureRoot();

        return {
            root,
            modal: root.querySelector('[data-fm-modal]'),
            icon: root.querySelector('[data-fm-modal-icon]'),
            title: root.querySelector('[data-fm-modal-title]'),
            message: root.querySelector('[data-fm-modal-message]'),
            confirm: root.querySelector('[data-fm-modal-confirm]'),
            cancelButton: root.querySelector('[data-fm-modal-cancel]'),
        };
    }

    function setVisible(visible) {
        const { modal } = getElements();

        if (!modal) {
            return;
        }

        modal.hidden = !visible;
        modal.classList.toggle('is-visible', visible);
        document.body.classList.toggle('fm-modal-open', visible);
    }

    function close(result) {
        if (state.keydownHandler) {
            document.removeEventListener('keydown', state.keydownHandler, true);
            state.keydownHandler = null;
        }

        setVisible(false);

        const resolver = state.resolver;
        const lastFocused = state.lastFocused;
        state.resolver = null;
        state.lastFocused = null;

        if (typeof resolver === 'function') {
            resolver(!!result);
        }

        if (lastFocused && typeof lastFocused.focus === 'function') {
            window.setTimeout(() => lastFocused.focus(), 0);
        }
    }

    async function confirm(options = {}) {
        ensureStyles();

        if (state.resolver) {
            close(false);
        }

        const elements = getElements();
        const variant = options.variant === 'danger' ? 'danger' : 'primary';

        elements.icon.dataset.variant = variant;
        elements.icon.textContent = variant === 'danger' ? '!' : '?';
        elements.title.textContent = options.title || 'Підтвердження дії';
        elements.message.innerHTML = escapeHtml(options.message || 'Підтвердіть виконання дії.').replace(/\n/g, '<br>');
        elements.confirm.dataset.variant = variant;
        elements.confirm.textContent = options.confirmLabel || 'Продовжити';
        if (elements.cancelButton) {
            elements.cancelButton.textContent = options.cancelLabel || 'Скасувати';
        }

        state.lastFocused = document.activeElement instanceof HTMLElement ? document.activeElement : null;
        state.keydownHandler = (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                close(false);
                return;
            }

            if (event.key === 'Enter' && !event.shiftKey && document.activeElement !== elements.cancelButton) {
                event.preventDefault();
                close(true);
            }
        };

        document.addEventListener('keydown', state.keydownHandler, true);
        setVisible(true);
        window.setTimeout(() => elements.confirm?.focus(), 0);

        return new Promise((resolve) => {
            state.resolver = resolve;
        });
    }

    window.FileManagerModal = {
        confirm,
        close,
    };
})();
