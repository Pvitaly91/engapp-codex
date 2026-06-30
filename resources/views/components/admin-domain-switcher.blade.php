@php
    $adminDomainSwitcherVisible = false;

    if (request()->hasSession()) {
        $adminDomainSwitcherVisible = (bool) session('admin_authenticated', false)
            || (bool) session('admin_user_id', false);
    }

    $adminDomainSwitcherVisible = $adminDomainSwitcherVisible
        || (bool) data_get(auth()->user(), 'is_admin', false);

    $productionOrigin = (string) config('site-mode.production_origin', 'https://gramlyze.com');
    $testOrigin = (string) config('site-mode.development_origin', 'http://engapp-codex.loc');
    $isProductionHost = app(\App\Support\SiteMode::class)->isProduction(request());
    $targetOrigin = $isProductionHost ? $testOrigin : $productionOrigin;
    $targetUrl = $targetOrigin . request()->getRequestUri();
@endphp

@if($adminDomainSwitcherVisible)
    <style>
        :root {
            --admin-domain-switcher-height: 42px;
        }

        body.has-admin-domain-switcher {
            padding-top: var(--admin-domain-switcher-height) !important;
        }

        body.has-admin-domain-switcher #site-header,
        body.has-admin-domain-switcher .site-header,
        body.has-admin-domain-switcher header.sticky.top-0,
        body.has-admin-domain-switcher aside.sticky.top-0 {
            top: var(--admin-domain-switcher-height) !important;
        }

        body.has-admin-domain-switcher aside.sticky.top-0.h-screen {
            height: calc(100vh - var(--admin-domain-switcher-height)) !important;
        }

        .admin-domain-switcher {
            position: fixed;
            inset: 0 0 auto 0;
            z-index: 2147483000;
            min-height: var(--admin-domain-switcher-height);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px 14px;
            background: #111827;
            color: #f9fafb;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.22);
        }

        .admin-domain-switcher__inner {
            width: min(100%, 1180px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .admin-domain-switcher__meta {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
        }

        .admin-domain-switcher__badge {
            display: inline-flex;
            min-width: 48px;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            padding: 5px 8px;
            background: #f59e0b;
            color: #111827;
            font-size: 11px;
            letter-spacing: 0.08em;
        }

        .admin-domain-switcher__host {
            overflow: hidden;
            color: #d1d5db;
            font-size: 12px;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-domain-switcher__button {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 6px;
            padding: 7px 12px;
            background: #f9fafb;
            color: #111827;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            text-decoration: none;
            transition: background-color 150ms ease, transform 150ms ease;
        }

        .admin-domain-switcher__button:hover {
            background: #fde68a;
            transform: translateY(-1px);
        }

        .admin-domain-switcher__button:focus-visible {
            outline: 2px solid #fde68a;
            outline-offset: 2px;
        }

        @media (max-width: 640px) {
            :root {
                --admin-domain-switcher-height: 50px;
            }

            .admin-domain-switcher {
                padding-inline: 10px;
            }

            .admin-domain-switcher__inner {
                gap: 8px;
            }

            .admin-domain-switcher__host {
                display: none;
            }

            .admin-domain-switcher__button {
                padding-inline: 10px;
            }
        }
    </style>

    <script>
        document.body.classList.add('has-admin-domain-switcher');
    </script>

    <div
        id="admin-domain-switcher"
        class="admin-domain-switcher"
        data-production-origin="{{ $productionOrigin }}"
        data-test-origin="{{ $testOrigin }}"
        data-is-production="{{ $isProductionHost ? '1' : '0' }}"
    >
        <div class="admin-domain-switcher__inner">
            <div class="admin-domain-switcher__meta">
                <span class="admin-domain-switcher__badge" data-admin-domain-current>{{ $isProductionHost ? 'PROD' : 'TEST' }}</span>
                <span class="admin-domain-switcher__host">{{ request()->getSchemeAndHttpHost() }}</span>
            </div>
            <a
                class="admin-domain-switcher__button"
                href="{{ $targetUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                data-admin-domain-switcher-link
            >
                <span data-admin-domain-target>{{ $isProductionHost ? 'Відкрити на тесті' : 'Відкрити на проді' }}</span>
                <span aria-hidden="true">↗</span>
            </a>
        </div>
    </div>

    <script>
        (function () {
            const root = document.getElementById('admin-domain-switcher');

            if (!root) {
                return;
            }

            const link = root.querySelector('[data-admin-domain-switcher-link]');
            const currentBadge = root.querySelector('[data-admin-domain-current]');
            const targetLabel = root.querySelector('[data-admin-domain-target]');
            const isProduction = root.dataset.isProduction === '1';
            const targetOrigin = isProduction ? root.dataset.testOrigin : root.dataset.productionOrigin;

            if (currentBadge) {
                currentBadge.textContent = isProduction ? 'PROD' : 'TEST';
            }

            if (targetLabel) {
                targetLabel.textContent = isProduction ? 'Відкрити на тесті' : 'Відкрити на проді';
            }

            if (!link || !targetOrigin) {
                return;
            }

            try {
                const targetBase = new URL(targetOrigin);
                const targetUrl = new URL(window.location.href);

                targetUrl.protocol = targetBase.protocol;
                targetUrl.host = targetBase.host;
                link.href = targetUrl.toString();
            } catch (error) {
                link.href = targetOrigin + window.location.pathname + window.location.search + window.location.hash;
            }
        })();
    </script>
@endif
