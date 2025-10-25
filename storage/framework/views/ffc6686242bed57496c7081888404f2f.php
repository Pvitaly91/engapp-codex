<?php if (! $__env->hasRenderedOnce('6d73d7b1-ea15-4e5f-9c3b-709906c2fa3f')): $__env->markAsRenderedOnce('6d73d7b1-ea15-4e5f-9c3b-709906c2fa3f'); ?>
    <style>
        /* Shared styles for grammar theory cards */
        .grammar-card {
            --bg: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --accent: #2563eb;
            --chip: #f3f4f6;
            --ok: #10b981;
            --warn: #f59e0b;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
            color: var(--text);
            background: var(--bg);
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 20px;
            max-width: 980px;
            margin: 0 auto 24px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .04);
        }

        .grammar-card * {
            box-sizing: border-box;
        }

        .gw-title {
            font-size: 28px;
            line-height: 1.2;
            margin: 0 0 10px;
        }

        .gw-sub {
            color: var(--muted);
            margin: 0 0 18px;
        }

        .gw-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .gw-col {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .gw-box {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            background: #fff;
        }

        .gw-box--scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .gw-box h3 {
            margin: 0 0 8px;
            font-size: 18px;
        }

        .gw-list {
            margin: 8px 0 0 18px;
        }

        .gw-list li {
            margin: 6px 0;
        }

        .gw-formula {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            background: #0b1220;
            color: #e5e7eb;
            border-radius: 10px;
            padding: 12px;
            font-size: 14px;
            overflow: auto;
        }

        .gw-code-badge {
            display: inline-block;
            font-size: 12px;
            color: #d1d5db;
            border: 1px solid #334155;
            padding: 2px 8px;
            border-radius: 999px;
            margin-bottom: 8px;
        }

        .gw-ex {
            background: #f9fafb;
            border-left: 4px solid var(--accent);
            padding: 10px 12px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .gw-en {
            font-weight: 600;
        }

        .gw-ua {
            color: var(--muted);
        }

        .gw-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .gw-chip {
            background: var(--chip);
            border: 1px solid #e5e7eb;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 13px;
        }

        .gw-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .gw-box--scroll .gw-table {
            min-width: 560px;
        }

        .gw-table th,
        .gw-table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            vertical-align: top;
        }

        .gw-table th {
            background: #f8fafc;
            text-align: left;
        }

        .gw-hint {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 10px 12px;
            border-radius: 10px;
        }

        .gw-emoji {
            font-size: 18px;
        }

        .tag-ok {
            color: var(--ok);
            font-weight: 700;
        }

        .tag-warn {
            color: var(--warn);
            font-weight: 700;
        }

        .gw-inversion {
            max-width: 100%;
            overflow-x: auto;
            white-space: normal;
        }

        .gw-inversion pre {
            max-width: 100%;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }

        @media (max-width: 640px) {
            .grammar-card {
                padding: 16px;
            }

            .gw-title {
                font-size: 24px;
            }

            .gw-sub {
                font-size: 15px;
            }

            .gw-box {
                padding: 12px;
            }

            .gw-table {
                font-size: 13px;
            }
        }

        @media (min-width: 720px) {
            .gw-grid {
                grid-template-columns: 1.2fr 1fr;
            }
        }
    </style>
<?php endif; ?>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/engram/pages/static/partials/grammar-card-styles.blade.php ENDPATH**/ ?>