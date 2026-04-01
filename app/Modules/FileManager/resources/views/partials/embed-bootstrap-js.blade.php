@include('file-manager::partials.modal-helper-js-code')

(function () {
    if (window.FileManagerEmbed && typeof window.FileManagerEmbed.bootAll === 'function') {
        return;
    }

    const EMBED_FRAGMENT_URL = @json($fragmentUrl);
    const EMBED_STANDALONE_URL = @json($standaloneUrl);
    const scriptPromises = new Map();
    const stylePromises = new Map();
    const bootedRoots = new WeakSet();

    const LANGUAGE_DEFINITIONS = {
        blade: { label: 'Blade / PHP', mode: 'application/x-httpd-php', fallback: 'php' },
        php: { label: 'PHP', mode: 'application/x-httpd-php', fallback: 'php' },
        js: { label: 'JavaScript', mode: 'javascript', fallback: 'js' },
        ts: { label: 'TypeScript', mode: 'javascript', fallback: 'ts' },
        json: { label: 'JSON', mode: 'application/json', fallback: 'js' },
        html: { label: 'HTML', mode: 'htmlmixed', fallback: 'html' },
        css: { label: 'CSS / SCSS', mode: 'css', fallback: 'css' },
        markdown: { label: 'Markdown', mode: 'markdown', fallback: 'md' },
        sql: { label: 'SQL', mode: 'sql', fallback: 'sql' },
        shell: { label: 'Shell', mode: 'shell', fallback: 'shell' },
        xml: { label: 'XML / SVG', mode: 'xml', fallback: 'xml' },
        yaml: { label: 'YAML', mode: 'yaml', fallback: 'yaml' },
        properties: { label: 'ENV / Config', mode: 'properties', fallback: 'properties' },
        python: { label: 'Python', mode: 'python', fallback: 'python' },
        plain: { label: 'Plain text', mode: 'text/plain', fallback: 'plain' },
    };

    const EXTENSION_TO_LANGUAGE = {
        php: 'php',
        blade: 'blade',
        js: 'js',
        jsx: 'js',
        cjs: 'js',
        mjs: 'js',
        ts: 'ts',
        tsx: 'ts',
        json: 'json',
        html: 'html',
        htm: 'html',
        xhtml: 'html',
        css: 'css',
        scss: 'css',
        sass: 'css',
        less: 'css',
        md: 'markdown',
        markdown: 'markdown',
        sql: 'sql',
        sh: 'shell',
        bash: 'shell',
        zsh: 'shell',
        env: 'properties',
        ini: 'properties',
        conf: 'properties',
        config: 'properties',
        properties: 'properties',
        xml: 'xml',
        svg: 'xml',
        yml: 'yaml',
        yaml: 'yaml',
        py: 'python',
        txt: 'plain',
        log: 'plain',
        toml: 'properties',
        csv: 'plain',
        tsv: 'plain',
        vue: 'html',
        svelte: 'html',
    };

    const FALLBACK_KEYWORDS = {
        php: ['function', 'class', 'public', 'protected', 'private', 'return', 'if', 'else', 'elseif', 'foreach', 'for', 'while', 'switch', 'case', 'default', 'break', 'continue', 'try', 'catch', 'finally', 'new', 'use', 'namespace', 'extends', 'implements', 'static', 'self', 'parent', 'echo', 'null', 'true', 'false'],
        js: ['function', 'const', 'let', 'var', 'return', 'if', 'else', 'for', 'while', 'switch', 'case', 'default', 'break', 'continue', 'try', 'catch', 'finally', 'class', 'extends', 'new', 'import', 'from', 'export', 'null', 'true', 'false'],
        ts: ['function', 'const', 'let', 'var', 'return', 'if', 'else', 'for', 'while', 'switch', 'case', 'default', 'break', 'continue', 'try', 'catch', 'finally', 'class', 'extends', 'new', 'import', 'from', 'export', 'type', 'interface', 'implements', 'public', 'private', 'protected', 'readonly', 'null', 'true', 'false'],
        css: ['color', 'background', 'display', 'flex', 'grid', 'position', 'absolute', 'relative', 'font', 'padding', 'margin', 'border'],
        sql: ['select', 'insert', 'update', 'delete', 'from', 'where', 'join', 'left', 'right', 'inner', 'outer', 'group', 'by', 'order', 'limit', 'values', 'into', 'create', 'table', 'drop', 'alter'],
        yaml: ['true', 'false', 'null', 'yes', 'no'],
        properties: ['true', 'false', 'null'],
        python: ['def', 'class', 'return', 'if', 'elif', 'else', 'for', 'while', 'break', 'continue', 'try', 'except', 'finally', 'import', 'from', 'as', 'None', 'True', 'False'],
    };

    function escapeHtmlSafe(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function normalizePath(value) {
        return String(value || '')
            .replace(/\\/g, '/')
            .replace(/\/+/g, '/')
            .replace(/^\/+|\/+$/g, '');
    }

    function formatFileSize(bytes) {
        const numeric = Number(bytes || 0);

        if (!Number.isFinite(numeric) || numeric <= 0) {
            return '0 B';
        }

        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        let size = numeric;
        let unitIndex = 0;

        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex += 1;
        }

        return `${Math.round(size * 100) / 100} ${units[unitIndex]}`;
    }

    function detectLanguage(path) {
        const normalizedPath = normalizePath(path).toLowerCase();

        if (normalizedPath.endsWith('.blade.php')) {
            return 'blade';
        }

        if (normalizedPath === '.env' || normalizedPath.endsWith('/.env')) {
            return 'properties';
        }

        const parts = normalizedPath.split('.');
        const extension = parts.length > 1 ? parts.pop() : '';

        return EXTENSION_TO_LANGUAGE[extension] || 'plain';
    }

    function highlightWithFallback(code, languageKey) {
        const text = typeof code === 'string' ? code : '';
        const patterns = [
            { regex: /\/\*[\s\S]*?\*\//g, className: 'comment' },
            { regex: /(^|[^:])\/\/.*$/gm, className: 'comment' },
            { regex: /#.*$/gm, className: 'comment' },
            { regex: /'(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*"/g, className: 'string' },
            { regex: /\b\d+(?:\.\d+)?\b/g, className: 'number' },
        ];

        const keywordList = FALLBACK_KEYWORDS[languageKey] || [];

        if (keywordList.length > 0) {
            patterns.push({
                regex: new RegExp(`\\b(${keywordList.join('|')})\\b`, 'gi'),
                className: 'keyword',
            });
        }

        const matches = [];
        patterns.forEach((pattern) => {
            let match;
            while ((match = pattern.regex.exec(text)) !== null) {
                matches.push({
                    start: match.index,
                    end: match.index + match[0].length,
                    className: pattern.className,
                    value: match[0],
                });
            }
        });

        matches.sort((a, b) => a.start - b.start || a.end - b.end);

        let cursor = 0;
        let result = '';
        matches.forEach((match) => {
            if (match.start < cursor) {
                return;
            }

            result += escapeHtmlSafe(text.slice(cursor, match.start));
            result += `<span class="fm-token fm-${match.className}">${escapeHtmlSafe(match.value)}</span>`;
            cursor = match.end;
        });

        result += escapeHtmlSafe(text.slice(cursor));
        return result;
    }

    function loadStyleOnce(href) {
        if (!href) {
            return Promise.resolve();
        }

        if (stylePromises.has(href)) {
            return stylePromises.get(href);
        }

        const promise = new Promise((resolve, reject) => {
            const existing = document.querySelector(`link[data-fm-style="${href}"]`);

            if (existing) {
                if (existing.dataset.loaded === 'true') {
                    resolve();
                    return;
                }

                existing.addEventListener('load', () => resolve(), { once: true });
                existing.addEventListener('error', () => reject(new Error(`Failed to load stylesheet: ${href}`)), { once: true });
                return;
            }

            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.dataset.fmStyle = href;
            link.addEventListener('load', () => {
                link.dataset.loaded = 'true';
                resolve();
            }, { once: true });
            link.addEventListener('error', () => reject(new Error(`Failed to load stylesheet: ${href}`)), { once: true });
            document.head.appendChild(link);
        });

        stylePromises.set(href, promise);
        return promise;
    }

    function loadScriptOnce(src) {
        if (!src) {
            return Promise.resolve();
        }

        if (scriptPromises.has(src)) {
            return scriptPromises.get(src);
        }

        const promise = new Promise((resolve, reject) => {
            const existing = document.querySelector(`script[data-fm-script="${src}"]`);

            if (existing) {
                if (existing.dataset.loaded === 'true') {
                    resolve();
                    return;
                }

                existing.addEventListener('load', () => resolve(), { once: true });
                existing.addEventListener('error', () => reject(new Error(`Failed to load script: ${src}`)), { once: true });
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.defer = true;
            script.dataset.fmScript = src;
            script.addEventListener('load', () => {
                script.dataset.loaded = 'true';
                resolve();
            }, { once: true });
            script.addEventListener('error', () => reject(new Error(`Failed to load script: ${src}`)), { once: true });
            document.head.appendChild(script);
        });

        scriptPromises.set(src, promise);
        return promise;
    }

    async function ensureCodeMirror(config) {
        if (window.CodeMirror) {
            await loadStyleOnce(config.assets?.style);
            return true;
        }

        await loadStyleOnce(config.assets?.style);
        const scripts = Array.isArray(config.assets?.scripts) ? config.assets.scripts : [];

        for (const source of scripts) {
            await loadScriptOnce(source);
        }

        return !!window.CodeMirror;
    }

    class FileManagerEmbedInstance {
        constructor(root) {
            this.root = root;
            this.config = this.readConfig(root);
            this.state = {
                currentPath: normalizePath(this.config.initialFilePath || ''),
                originalContent: '',
                content: '',
                writable: false,
                isText: false,
                dirty: false,
                loading: false,
                saving: false,
                language: 'plain',
            };
            this.editor = null;
            this.elements = {
                title: root.querySelector('[data-fm-title]'),
                path: root.querySelector('[data-fm-path]'),
                syntaxBadge: root.querySelector('[data-fm-syntax-badge]'),
                sizeBadge: root.querySelector('[data-fm-size-badge]'),
                statusBadge: root.querySelector('[data-fm-status-badge]'),
                notice: root.querySelector('[data-fm-notice]'),
                empty: root.querySelector('[data-fm-empty]'),
                loading: root.querySelector('[data-fm-loading]'),
                binary: root.querySelector('[data-fm-binary]'),
                editorPanel: root.querySelector('[data-fm-editor-panel]'),
                editorShell: root.querySelector('[data-fm-editor-shell]'),
                editorSource: root.querySelector('[data-fm-editor-source]'),
                fallbackHighlight: root.querySelector('[data-fm-fallback-highlight]'),
                fallbackTextarea: root.querySelector('[data-fm-fallback-textarea]'),
                openLink: root.querySelector('[data-fm-open-link]'),
                copyUrl: root.querySelector('[data-fm-copy-url]'),
                reload: root.querySelector('[data-fm-reload]'),
                save: root.querySelector('[data-fm-save]'),
            };
        }

        readConfig(root) {
            const node = root.querySelector('[data-fm-config]');

            if (!node) {
                return {};
            }

            try {
                return JSON.parse(node.textContent || '{}');
            } catch (error) {
                return {};
            }
        }

        async init() {
            this.bindEvents();

            if (this.config.initialMissingTarget) {
                this.showEmpty();
                this.showNotice(`Файл "${this.config.initialMissingTarget}" не знайдено або доступ до нього заборонено.`, 'error');
                return;
            }

            if (this.config.directoryTarget) {
                this.showEmpty();
                this.showNotice('Embeddable-редактор очікує шлях до файлу, а не до папки.', 'error');
                return;
            }

            if (!this.state.currentPath) {
                this.showEmpty();
                return;
            }

            await this.loadFile(this.state.currentPath);
        }

        hasUnsavedChanges() {
            return !!this.state.dirty;
        }

        async confirmDiscardChanges(message = 'Є незбережені зміни. Продовжити без збереження?') {
            if (!this.hasUnsavedChanges()) {
                return true;
            }

            if (window.FileManagerModal?.confirm) {
                return window.FileManagerModal.confirm({
                    title: 'Незбережені зміни',
                    message,
                    confirmLabel: 'Продовжити',
                    cancelLabel: 'Скасувати',
                    variant: 'danger',
                });
            }

            return false;
        }

        bindEvents() {
            this.elements.copyUrl?.addEventListener('click', async () => {
                await this.copyCurrentUrl();
            });

            this.elements.reload?.addEventListener('click', async () => {
                await this.reloadCurrentFile();
            });

            this.elements.save?.addEventListener('click', async () => {
                await this.save();
            });

            this.elements.fallbackTextarea?.addEventListener('input', () => {
                this.setContent(this.elements.fallbackTextarea.value, false);
            });

            this.elements.fallbackTextarea?.addEventListener('scroll', () => {
                if (this.elements.fallbackHighlight) {
                    this.elements.fallbackHighlight.scrollTop = this.elements.fallbackTextarea.scrollTop;
                    this.elements.fallbackHighlight.scrollLeft = this.elements.fallbackTextarea.scrollLeft;
                }
            });

            this.root.addEventListener('keydown', async (event) => {
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
                    event.preventDefault();
                    await this.save();
                }
            });
        }

        setVisible(target) {
            ['empty', 'loading', 'binary'].forEach((key) => {
                this.elements[key]?.classList.toggle('is-visible', key === target);
            });
            this.elements.editorPanel?.classList.toggle('is-visible', target === 'editor');
        }

        showEmpty() {
            this.setVisible('empty');
            this.updateHeader(null);
            this.updateButtons();
        }

        showLoading() {
            this.setVisible('loading');
            this.updateButtons();
        }

        showBinary() {
            this.setVisible('binary');
            this.updateButtons();
        }

        showEditor() {
            this.setVisible('editor');
        }

        showNotice(message, type) {
            if (!this.elements.notice) {
                return;
            }

            this.elements.notice.textContent = message || '';
            this.elements.notice.dataset.type = type === 'error' ? 'error' : 'success';
            this.elements.notice.classList.toggle('is-visible', !!message);
        }

        hideNotice() {
            this.showNotice('', 'success');
        }

        updateHeader(file) {
            const path = file?.path || this.state.currentPath || this.config.requestedPath || '';
            const language = LANGUAGE_DEFINITIONS[this.state.language] || LANGUAGE_DEFINITIONS.plain;
            const sizeVisible = !!file && typeof file.size !== 'undefined';
            const isReadonly = !!file && !file.writable;

            if (this.elements.title) {
                this.elements.title.textContent = file?.name || 'Відкрий файл проєкту';
            }

            if (this.elements.path) {
                this.elements.path.textContent = path
                    ? `${this.config.basePath}/${path}`.replace(/\\/g, '/')
                    : 'Очікується пряме посилання на файл або параметр path.';
            }

            if (this.elements.syntaxBadge) {
                this.elements.syntaxBadge.textContent = language.label;
            }

            if (this.elements.sizeBadge) {
                this.elements.sizeBadge.hidden = !sizeVisible;
                this.elements.sizeBadge.textContent = sizeVisible ? formatFileSize(file.size) : '0 B';
            }

            if (this.elements.statusBadge) {
                this.elements.statusBadge.hidden = !file;

                if (!file) {
                    this.elements.statusBadge.textContent = '';
                    this.elements.statusBadge.dataset.kind = 'readonly';
                } else if (this.state.dirty) {
                    this.elements.statusBadge.textContent = 'Незбережені зміни';
                    this.elements.statusBadge.dataset.kind = 'dirty';
                } else if (isReadonly) {
                    this.elements.statusBadge.textContent = 'Read only';
                    this.elements.statusBadge.dataset.kind = 'readonly';
                } else {
                    this.elements.statusBadge.textContent = 'Готово';
                    this.elements.statusBadge.dataset.kind = 'saved';
                }
            }

            if (this.elements.openLink) {
                this.elements.openLink.href = this.buildStandaloneUrl(path);
            }
        }

        updateButtons() {
            const canSave = this.state.isText && this.state.writable && this.state.dirty && !this.state.loading && !this.state.saving;

            if (this.elements.save) {
                this.elements.save.disabled = !canSave;
                this.elements.save.textContent = this.state.saving ? 'Збереження…' : 'Зберегти';
            }

            if (this.elements.reload) {
                this.elements.reload.disabled = this.state.loading || !this.state.currentPath;
            }

            this.emitState();
        }

        getUiState() {
            return {
                currentPath: this.state.currentPath || this.config.requestedPath || '',
                title: this.elements.title?.textContent || '',
                writable: !!this.state.writable,
                isText: !!this.state.isText,
                dirty: !!this.state.dirty,
                loading: !!this.state.loading,
                saving: !!this.state.saving,
                canSave: this.state.isText && this.state.writable && this.state.dirty && !this.state.loading && !this.state.saving,
                standaloneUrl: this.buildStandaloneUrl(this.state.currentPath || this.config.requestedPath || ''),
            };
        }

        emitState() {
            this.root.dispatchEvent(new CustomEvent('file-manager-embed:state', {
                detail: this.getUiState(),
            }));
        }

        async copyCurrentUrl() {
            const url = this.buildStandaloneUrl(this.state.currentPath || this.config.requestedPath || '');

            try {
                await navigator.clipboard.writeText(url);
                this.showNotice('Пряме посилання скопійовано.', 'success');
            } catch (error) {
                this.showNotice(url, 'success');
            }

            return url;
        }

        async reloadCurrentFile(force = false) {
            if (!this.state.currentPath) {
                return;
            }

            await this.loadFile(this.state.currentPath, force);
        }

        async loadFile(path, force = false) {
            const normalizedPath = normalizePath(path);

            if (!normalizedPath) {
                this.showEmpty();
                return;
            }

            if (!force && this.state.dirty && !(await this.confirmDiscardChanges('Є незбережені зміни. Перечитати файл і втратити локальні правки?'))) {
                return;
            }

            this.state.loading = true;
            this.state.currentPath = normalizedPath;
            this.state.dirty = false;
            this.showLoading();
            this.hideNotice();
            this.updateButtons();

            try {
                const response = await fetch(`${this.config.routes.read}?path=${encodeURIComponent(normalizedPath)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });
                const payload = await response.json();

                if (!response.ok || !payload.success || !payload.file) {
                    throw new Error(payload.error || 'Не вдалося відкрити файл.');
                }

                const file = payload.file;
                this.state.writable = !!file.writable;
                this.state.isText = !!file.is_text;
                this.state.language = detectLanguage(file.path || normalizedPath);
                this.updateHeader(file);

                if (!file.is_text) {
                    this.showBinary();
                    this.state.originalContent = '';
                    this.state.content = '';
                    return;
                }

                this.state.originalContent = String(file.content || '');
                this.state.content = this.state.originalContent;
                await this.mountEditor();
                this.setContent(this.state.originalContent, true);
                this.showEditor();
            } catch (error) {
                this.showEmpty();
                this.showNotice(error instanceof Error ? error.message : 'Не вдалося відкрити файл.', 'error');
            } finally {
                this.state.loading = false;
                this.updateButtons();
            }
        }

        async mountEditor() {
            this.elements.editorShell?.setAttribute('data-mode', 'fallback');

            try {
                const ready = await ensureCodeMirror(this.config);

                if (!ready || !window.CodeMirror || !this.elements.editorSource) {
                    throw new Error('CodeMirror недоступний');
                }

                if (!this.editor) {
                    this.editor = window.CodeMirror.fromTextArea(this.elements.editorSource, {
                        lineNumbers: true,
                        mode: (LANGUAGE_DEFINITIONS[this.state.language] || LANGUAGE_DEFINITIONS.plain).mode,
                        readOnly: !this.state.writable,
                        lineWrapping: false,
                        viewportMargin: Infinity,
                        extraKeys: {
                            'Ctrl-S': () => this.save(),
                            'Cmd-S': () => this.save(),
                        },
                    });

                    this.editor.on('change', () => {
                        if (this._syncingEditor) {
                            return;
                        }

                        this.state.content = this.editor.getValue();
                        this.state.dirty = this.state.content !== this.state.originalContent;
                        this.renderFallback();
                        this.updateHeader({
                            path: this.state.currentPath,
                            name: this.state.currentPath.split('/').pop(),
                            size: this.state.content.length,
                            writable: this.state.writable,
                        });
                        this.updateButtons();
                    });
                }

                this.editor.setOption('mode', (LANGUAGE_DEFINITIONS[this.state.language] || LANGUAGE_DEFINITIONS.plain).mode);
                this.editor.setOption('readOnly', !this.state.writable);
                this.elements.editorShell?.setAttribute('data-mode', 'codemirror');
                setTimeout(() => this.editor?.refresh(), 0);
            } catch (error) {
                this.elements.editorShell?.setAttribute('data-mode', 'fallback');
                this.renderFallback();
            }
        }

        setContent(content, skipDirtyUpdate) {
            const nextContent = String(content || '');
            this.state.content = nextContent;

            if (this.elements.editorSource) {
                this.elements.editorSource.value = nextContent;
            }

            if (this.elements.fallbackTextarea && this.elements.fallbackTextarea.value !== nextContent) {
                this.elements.fallbackTextarea.value = nextContent;
            }

            if (this.editor && this.editor.getValue() !== nextContent) {
                this._syncingEditor = true;
                this.editor.setValue(nextContent);
                this._syncingEditor = false;
            }

            if (!skipDirtyUpdate) {
                this.state.dirty = nextContent !== this.state.originalContent;
            }

            this.renderFallback();
            this.updateHeader({
                path: this.state.currentPath,
                name: this.state.currentPath.split('/').pop(),
                size: nextContent.length,
                writable: this.state.writable,
            });
            this.updateButtons();
        }

        renderFallback() {
            if (!this.elements.fallbackHighlight) {
                return;
            }

            const definition = LANGUAGE_DEFINITIONS[this.state.language] || LANGUAGE_DEFINITIONS.plain;
            this.elements.fallbackHighlight.innerHTML = highlightWithFallback(this.state.content, definition.fallback);
        }

        async save() {
            if (!this.state.currentPath || !this.state.isText || !this.state.writable || !this.state.dirty || this.state.saving) {
                return;
            }

            this.state.saving = true;
            this.hideNotice();
            this.updateButtons();

            try {
                const body = new URLSearchParams();
                body.set('_token', this.config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
                body.set('path', this.state.currentPath);
                body.set('content', this.editor ? this.editor.getValue() : this.state.content);

                const response = await fetch(this.config.routes.update, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    },
                    body: body.toString(),
                    credentials: 'same-origin',
                });
                const payload = await response.json();

                if (!response.ok || !payload.success) {
                    throw new Error(payload.error || 'Не вдалося зберегти файл.');
                }

                this.state.originalContent = this.editor ? this.editor.getValue() : this.state.content;
                this.state.content = this.state.originalContent;
                this.state.dirty = false;
                this.updateHeader({
                    path: this.state.currentPath,
                    name: this.state.currentPath.split('/').pop(),
                    size: payload.size || this.state.content.length,
                    writable: this.state.writable,
                });
                this.updateButtons();
                this.showNotice(payload.message || 'Файл успішно збережено.', 'success');
            } catch (error) {
                this.showNotice(error instanceof Error ? error.message : 'Не вдалося зберегти файл.', 'error');
            } finally {
                this.state.saving = false;
                this.updateButtons();
            }
        }

        buildStandaloneUrl(path) {
            const url = new URL(EMBED_STANDALONE_URL, window.location.origin);

            if (path) {
                url.searchParams.set('path', normalizePath(path));
            }

            return url.toString();
        }
    }

    function boot(root) {
        if (!root || bootedRoots.has(root)) {
            return root;
        }

        const instance = new FileManagerEmbedInstance(root);
        bootedRoots.add(root);
        root.__fileManagerEmbed = instance;
        instance.init();
        return root;
    }

    function bootAll(context) {
        const scope = context instanceof Element || context instanceof Document ? context : document;
        scope.querySelectorAll('[data-file-manager-embed-root]').forEach((root) => boot(root));
    }

    async function loadInto(target, pathOrUrl, options = {}) {
        const element = typeof target === 'string'
            ? document.querySelector(target)
            : target;

        if (!element) {
            throw new Error('Target container for FileManagerEmbed was not found.');
        }

        const existingRoot = element.querySelector('[data-file-manager-embed-root]');
        const existingInstance = existingRoot?.__fileManagerEmbed;

        if (options.warnOnUnsaved !== false && existingInstance && typeof existingInstance.confirmDiscardChanges === 'function') {
            if (!(await existingInstance.confirmDiscardChanges())) {
                return existingRoot;
            }
        }

        const input = String(pathOrUrl || '').trim();
        const requestUrl = /^https?:\/\//i.test(input) || input.startsWith('/')
            ? input
            : `${EMBED_FRAGMENT_URL}?path=${encodeURIComponent(normalizePath(input))}`;

        const response = await fetch(requestUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(options.errorMessage || 'Не вдалося завантажити embeddable-редактор.');
        }

        element.innerHTML = await response.text();
        bootAll(element);
        return element.querySelector('[data-file-manager-embed-root]');
    }

    window.FileManagerEmbed = {
        boot,
        bootAll,
        loadInto,
        routes: {
            fragment: EMBED_FRAGMENT_URL,
            standalone: EMBED_STANDALONE_URL,
        },
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => bootAll(document), { once: true });
    } else {
        bootAll(document);
    }

    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (!(node instanceof Element)) {
                        return;
                    }

                    if (node.matches?.('[data-file-manager-embed-root]')) {
                        boot(node);
                        return;
                    }

                    if (node.querySelector?.('[data-file-manager-embed-root]')) {
                        bootAll(node);
                    }
                });
            });
        });

        if (document.documentElement) {
            observer.observe(document.documentElement, {
                childList: true,
                subtree: true,
            });
        }
    }
})();
