@php
    $compact = (bool) ($compact ?? false);
    $embedConfig = [
        'basePath' => $basePath,
        'initialFilePath' => $initialFilePath,
        'requestedPath' => $requestedPath,
        'initialMissingTarget' => $initialMissingTarget,
        'directoryTarget' => $directoryTarget,
        'targetType' => $targetInfo['type'] ?? null,
        'standalone' => (bool) ($standalone ?? false),
        'compact' => $compact,
        'csrfToken' => csrf_token(),
        'routes' => [
            'read' => route('file-manager.v2.read'),
            'update' => route('file-manager.v2.update'),
            'standalone' => route('file-manager.embed'),
            'fragment' => route('file-manager.embed.fragment'),
        ],
        'assets' => [
            'style' => route('file-manager.asset', ['path' => 'codemirror/codemirror.min.css']),
            'scripts' => [
                route('file-manager.asset', ['path' => 'codemirror/codemirror.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/addon/mode/multiplex.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/mode/clike/clike.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/mode/xml/xml.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/mode/javascript/javascript.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/mode/css/css.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/mode/htmlmixed/htmlmixed.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/mode/php/php.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/mode/markdown/markdown.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/mode/sql/sql.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/mode/shell/shell.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/mode/yaml/yaml.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/mode/properties/properties.min.js']),
                route('file-manager.asset', ['path' => 'codemirror/mode/python/python.min.js']),
            ],
        ],
    ];
@endphp

<div class="fm-embed-shell{{ !empty($standalone) ? ' fm-embed-shell--standalone' : '' }}{{ $compact ? ' fm-embed-shell--compact' : '' }}">
    <div class="fm-embed-root{{ $compact ? ' fm-embed-root--compact' : '' }}" data-file-manager-embed-root>
        <script type="application/json" data-fm-config>@json($embedConfig)</script>

        <style>
            .fm-embed-shell { padding: 1rem; }
            .fm-embed-shell--standalone {
                display: flex;
                min-height: 100vh;
                min-height: 100dvh;
                height: 100vh;
                height: 100dvh;
                box-sizing: border-box;
                background:
                    radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 28rem),
                    linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            }
            .fm-embed-shell--compact {
                padding: 0;
                height: 100%;
                min-height: 0;
            }
            .fm-embed-root {
                display: flex;
                flex-direction: column;
                border: 1px solid #dbe4f0;
                border-radius: 1.5rem;
                background: rgba(255, 255, 255, 0.96);
                box-shadow: 0 24px 80px -36px rgba(15, 23, 42, 0.45);
                overflow: hidden;
            }
            .fm-embed-shell--standalone .fm-embed-root {
                flex: 1 1 auto;
                min-height: 0;
            }
            .fm-embed-root--compact {
                height: 100%;
                min-height: 0;
                border: 0;
                border-radius: 0;
                background: transparent;
                box-shadow: none;
            }
            .fm-embed-header {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                justify-content: space-between;
                align-items: flex-start;
                padding: 1.25rem 1.5rem;
                border-bottom: 1px solid #e2e8f0;
                background: linear-gradient(135deg, #eff6ff 0%, #ffffff 65%, #f8fafc 100%);
            }
            .fm-embed-eyebrow {
                margin: 0 0 0.5rem;
                font-size: 0.75rem;
                font-weight: 700;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: #1d4ed8;
            }
            .fm-embed-title {
                margin: 0;
                font-size: 1.5rem;
                line-height: 1.25;
                color: #0f172a;
                word-break: break-word;
            }
            .fm-embed-path {
                margin: 0.5rem 0 0;
                font-size: 0.875rem;
                color: #475569;
                word-break: break-all;
            }
            .fm-embed-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-top: 0.875rem;
            }
            .fm-embed-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                padding: 0.35rem 0.7rem;
                border-radius: 999px;
                background: #e2e8f0;
                font-size: 0.75rem;
                font-weight: 600;
                color: #334155;
            }
            .fm-embed-badge[data-kind="dirty"] { background: #fef3c7; color: #92400e; }
            .fm-embed-badge[data-kind="saved"] { background: #dcfce7; color: #166534; }
            .fm-embed-badge[data-kind="readonly"] { background: #e2e8f0; color: #475569; }
            .fm-embed-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
                justify-content: flex-end;
            }
            .fm-embed-button,
            .fm-embed-link {
                appearance: none;
                border: 1px solid #cbd5e1;
                border-radius: 0.9rem;
                background: #fff;
                color: #0f172a;
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
                font-weight: 600;
                line-height: 1.2;
                text-decoration: none;
                cursor: pointer;
                transition: background-color 0.2s ease, border-color 0.2s ease, opacity 0.2s ease;
            }
            .fm-embed-button:hover,
            .fm-embed-link:hover { background: #f8fafc; border-color: #94a3b8; }
            .fm-embed-button:disabled { cursor: not-allowed; opacity: 0.55; }
            .fm-embed-button[data-variant="primary"] {
                border-color: #0f766e;
                background: #0f766e;
                color: #fff;
            }
            .fm-embed-button[data-variant="primary"]:hover {
                background: #115e59;
                border-color: #115e59;
            }
            .fm-embed-content { padding: 1rem 1.5rem 1.5rem; }
            .fm-embed-shell--standalone .fm-embed-content,
            .fm-embed-root--compact .fm-embed-content {
                display: flex;
                flex: 1 1 auto;
                flex-direction: column;
                min-height: 0;
            }
            .fm-embed-root--compact .fm-embed-content { padding: 0; }
            .fm-embed-notice {
                display: none;
                margin-bottom: 1rem;
                padding: 0.95rem 1rem;
                border-radius: 1rem;
                font-size: 0.875rem;
                line-height: 1.5;
            }
            .fm-embed-notice.is-visible { display: block; }
            .fm-embed-notice[data-type="success"] {
                background: #ecfdf5;
                border: 1px solid #a7f3d0;
                color: #166534;
            }
            .fm-embed-notice[data-type="error"] {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #b91c1c;
            }
            .fm-embed-empty,
            .fm-embed-binary,
            .fm-embed-loading {
                display: none;
                min-height: 16rem;
                border: 1px dashed #cbd5e1;
                border-radius: 1.25rem;
                background: #f8fafc;
                color: #475569;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 2rem;
                box-sizing: border-box;
            }
            .fm-embed-empty.is-visible,
            .fm-embed-binary.is-visible,
            .fm-embed-loading.is-visible { display: flex; }
            .fm-embed-editor-panel { display: none; gap: 1rem; }
            .fm-embed-editor-panel.is-visible {
                display: flex;
                flex-direction: column;
                flex: 1 1 auto;
                min-height: 0;
            }
            .fm-embed-editor-shell {
                position: relative;
                display: flex;
                flex-direction: column;
                flex: 1 1 auto;
                overflow: hidden;
                border: 1px solid #0f172a;
                border-radius: 1.25rem;
                background: #020617;
                min-height: 30rem;
            }
            .fm-embed-shell--standalone .fm-embed-editor-shell,
            .fm-embed-root--compact .fm-embed-editor-shell {
                min-height: 0;
            }
            .fm-embed-editor-shell .CodeMirror {
                height: 30rem;
                font-size: 0.92rem;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            }
            .fm-embed-shell--standalone .fm-embed-editor-shell .CodeMirror,
            .fm-embed-root--compact .fm-embed-editor-shell .CodeMirror {
                height: 100%;
            }
            .fm-embed-editor-shell[data-mode="fallback"] .CodeMirror { display: none !important; }
            .fm-embed-editor-source {
                position: absolute;
                opacity: 0;
                pointer-events: none;
                width: 0;
                height: 0;
            }
            .fm-embed-fallback {
                display: none;
                position: relative;
                min-height: 30rem;
            }
            .fm-embed-shell--standalone .fm-embed-fallback,
            .fm-embed-root--compact .fm-embed-fallback {
                flex: 1 1 auto;
                min-height: 0;
                height: 100%;
            }
            .fm-embed-editor-shell[data-mode="fallback"] .fm-embed-fallback { display: block; }
            .fm-embed-fallback-highlight,
            .fm-embed-fallback-textarea {
                margin: 0;
                min-height: 30rem;
                padding: 1rem 1.1rem;
                font-size: 0.92rem;
                line-height: 1.7;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                white-space: pre;
                overflow: auto;
                box-sizing: border-box;
            }
            .fm-embed-shell--standalone .fm-embed-fallback-highlight,
            .fm-embed-shell--standalone .fm-embed-fallback-textarea,
            .fm-embed-root--compact .fm-embed-fallback-highlight,
            .fm-embed-root--compact .fm-embed-fallback-textarea {
                min-height: 0;
                height: 100%;
            }
            .fm-embed-fallback-highlight {
                color: #e2e8f0;
                pointer-events: none;
            }
            .fm-embed-fallback-textarea {
                position: absolute;
                inset: 0;
                width: 100%;
                resize: vertical;
                border: 0;
                outline: none;
                background: transparent;
                color: transparent;
                caret-color: #f8fafc;
            }
            .fm-token.fm-comment { color: #64748b; }
            .fm-token.fm-string { color: #86efac; }
            .fm-token.fm-number { color: #fca5a5; }
            .fm-token.fm-keyword { color: #7dd3fc; }
            @media (max-width: 900px) {
                .fm-embed-shell { padding: 0.75rem; }
                .fm-embed-shell--compact { padding: 0; }
                .fm-embed-header,
                .fm-embed-content {
                    padding-left: 1rem;
                    padding-right: 1rem;
                }
                .fm-embed-root--compact .fm-embed-content {
                    padding-left: 0;
                    padding-right: 0;
                }
                .fm-embed-actions {
                    width: 100%;
                    justify-content: flex-start;
                }
            }
        </style>

        @unless($compact)
        <div class="fm-embed-header">
            <div>
                <p class="fm-embed-eyebrow">File Manager Embed</p>
                <h1 class="fm-embed-title" data-fm-title>Відкрий файл проєкту</h1>
                <p class="fm-embed-path" data-fm-path>Очікується пряме посилання на файл або параметр <code>path</code>.</p>
                <div class="fm-embed-meta">
                    <span class="fm-embed-badge" data-fm-syntax-badge>Text</span>
                    <span class="fm-embed-badge" data-fm-size-badge hidden>0 B</span>
                    <span class="fm-embed-badge" data-fm-status-badge data-kind="readonly" hidden>Read only</span>
                </div>
            </div>

            <div class="fm-embed-actions">
                <a href="#" class="fm-embed-link" data-fm-open-link target="_blank" rel="noopener noreferrer">Відкрити окремо</a>
                <button type="button" class="fm-embed-button" data-fm-copy-url>Копіювати URL</button>
                <button type="button" class="fm-embed-button" data-fm-reload>Перечитати</button>
                <button type="button" class="fm-embed-button" data-variant="primary" data-fm-save disabled>Зберегти</button>
            </div>
        </div>
        @endunless

        <div class="fm-embed-content">
            <div class="fm-embed-notice" data-fm-notice data-type="success"></div>

            <div class="fm-embed-empty is-visible" data-fm-empty>
                <div>
                    <strong style="display:block;font-size:1rem;color:#0f172a;">Немає вибраного файлу</strong>
                    <p style="margin:0.75rem 0 0;">Передайте шлях у параметрі <code>path</code> або відкрийте цей редактор прямим посиланням на конкретний файл.</p>
                </div>
            </div>

            <div class="fm-embed-loading" data-fm-loading>
                <div>
                    <strong style="display:block;font-size:1rem;color:#0f172a;">Завантаження файлу…</strong>
                    <p style="margin:0.75rem 0 0;">Зчитую вміст і ініціалізую редактор.</p>
                </div>
            </div>

            <div class="fm-embed-binary" data-fm-binary>
                <div>
                    <strong style="display:block;font-size:1rem;color:#0f172a;">Файл не є текстовим</strong>
                    <p style="margin:0.75rem 0 0;">Для цього типу доступне тільки пряме відкриття та посилання. Онлайн-редагування вимкнено.</p>
                </div>
            </div>

            <div class="fm-embed-editor-panel" data-fm-editor-panel>
                <div class="fm-embed-editor-shell" data-fm-editor-shell data-mode="fallback">
                    <textarea class="fm-embed-editor-source" data-fm-editor-source aria-hidden="true"></textarea>

                    <div class="fm-embed-fallback">
                        <pre class="fm-embed-fallback-highlight" data-fm-fallback-highlight></pre>
                        <textarea class="fm-embed-fallback-textarea" data-fm-fallback-textarea spellcheck="false" wrap="off"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
