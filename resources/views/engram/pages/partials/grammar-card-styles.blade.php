@once
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
            border-radius: clamp(18px, 5vw, 26px);
            padding: clamp(18px, 4vw, 32px);
            margin: 0 auto clamp(20px, 4vw, 32px);
            width: min(100%, 880px);
            box-shadow: 0 18px 40px -25px rgba(15, 23, 42, 0.35);
            line-height: 1.55;
        }

        .grammar-card * {
            box-sizing: border-box;
        }

        .gw-title {
            font-size: clamp(24px, 5.4vw, 34px);
            line-height: 1.2;
            margin: 0 0 clamp(10px, 2.5vw, 16px);
        }

        .gw-sub {
            color: var(--muted);
            font-size: clamp(15px, 4vw, 18px);
            margin: 0 0 clamp(16px, 4vw, 22px);
        }

        .gw-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: clamp(16px, 4vw, 28px);
        }

        .gw-col {
            display: flex;
            flex-direction: column;
            gap: clamp(14px, 3.2vw, 22px);
        }

        .gw-box {
            border: 1px solid #e5e7eb;
            border-radius: clamp(12px, 3.5vw, 20px);
            padding: clamp(14px, 4vw, 22px);
            background: #fff;
            box-shadow: 0 10px 25px -20px rgba(15, 23, 42, 0.35);
        }

        .gw-box--scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .gw-box h3 {
            margin: 0 0 clamp(6px, 2vw, 12px);
            font-size: clamp(17px, 4vw, 21px);
            line-height: 1.35;
        }

        .gw-list {
            margin: clamp(6px, 1.8vw, 10px) 0 0 clamp(16px, 4vw, 22px);
        }

        .gw-list li {
            margin: clamp(4px, 1.4vw, 8px) 0;
        }

        .gw-formula {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            background: #0b1220;
            color: #e5e7eb;
            border-radius: clamp(10px, 3vw, 16px);
            padding: clamp(12px, 3.8vw, 18px);
            font-size: clamp(13px, 3.2vw, 16px);
            line-height: 1.55;
            overflow-x: auto;
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
            padding: clamp(10px, 3vw, 16px) clamp(12px, 3.6vw, 18px);
            border-radius: clamp(8px, 2.8vw, 14px);
            margin-top: clamp(10px, 2.6vw, 16px);
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
            gap: clamp(6px, 2.2vw, 12px);
            margin-top: clamp(6px, 2vw, 12px);
        }

        .gw-chip {
            background: var(--chip);
            border: 1px solid #e5e7eb;
            padding: clamp(6px, 2.2vw, 10px) clamp(10px, 3vw, 14px);
            border-radius: 999px;
            font-size: clamp(12px, 2.8vw, 14px);
        }

        .gw-table {
            width: 100%;
            border-collapse: collapse;
            font-size: clamp(13px, 3.2vw, 15px);
        }

        .gw-box--scroll .gw-table {
           /* min-width: 560px;*/
        }

        .gw-table th,
        .gw-table td {
            border: 1px solid #e5e7eb;
            padding: clamp(9px, 3vw, 14px);
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
            padding: clamp(10px, 3.2vw, 16px) clamp(12px, 3.6vw, 18px);
            border-radius: clamp(10px, 3vw, 16px);
        }

        .gw-emoji {
            font-size: clamp(18px, 4vw, 22px);
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
                border: none;
                box-shadow: none;
                padding: clamp(16px, 6vw, 22px);
                margin-left: 0;
                margin-right: 0;
            }

            .gw-title {
                letter-spacing: -0.01em;
            }

            .gw-sub {
                color: #4b5563;
            }

            .gw-box {
                padding: clamp(12px, 4.8vw, 18px);
                box-shadow: 0 14px 32px -28px rgba(15, 23, 42, 0.55);
            }

            .gw-table {
                font-size: 13px;
                display: block;
                overflow-x: auto;
                border-radius: 12px;
                margin: 0 -6px;
                padding-bottom: 4px;
            }

            .gw-table table,
            .gw-table tbody,
            .gw-table tr,
            .gw-table td,
            .gw-table th {
                white-space: nowrap;
            }

            .gw-chips {
                gap: 8px;
            }
        }

        @media (max-width: 480px) {
            .gw-grid {
                gap: 18px;
            }

            .gw-col {
                gap: 18px;
            }

            .gw-box h3 {
                font-size: 16px;
            }

            .gw-chip {
                font-size: 12px;
            }

            .gw-formula {
                font-size: 12px;
            }

            .gw-list {
                margin-left: 18px;
            }
        }

        @media (min-width: 720px) {
            .gw-grid {
                grid-template-columns: 1.2fr 1fr;
            }
        }
    </style>
@endonce
