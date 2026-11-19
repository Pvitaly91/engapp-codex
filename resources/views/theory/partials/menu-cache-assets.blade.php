@once
    @include('components.theory-search')
    <script defer src="{{ asset('js/theory-menu.js') }}?v={{ filemtime(public_path('js/theory-menu.js')) }}"></script>
    <style>
        @media (min-width: 1024px) {
            [data-theory-aside] {
                contain: none;
                inline-size: 390px;
            }

            html[data-theory-sidebar-collapsed="true"] [data-theory-layout]:not([data-collapsed]) [data-theory-aside],
            [data-theory-layout][data-collapsed="true"] [data-theory-aside] {
                inline-size: 116px;
            }
        }

        @media (min-width: 1280px) {
            [data-theory-aside] {
                inline-size: 410px;
            }

            html[data-theory-sidebar-collapsed="true"] [data-theory-layout]:not([data-collapsed]) [data-theory-aside],
            [data-theory-layout][data-collapsed="true"] [data-theory-aside] {
                inline-size: 116px;
            }
        }

        [data-theory-sidebar] {
            transform: translateZ(0);
            overflow: hidden;
        }

        [data-theory-main] {
            will-change: transform;
        }

        html[data-theory-sidebar-collapsed="true"] [data-theory-layout]:not([data-collapsed]) .theory-sidebar-expanded-only,
        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-sidebar-expanded-only,
        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-nav-label,
        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-nav-count,
        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-nav-toggle,
        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-nav-children,
        [data-theory-sidebar][data-collapsed="true"] .theory-sidebar-expanded-only,
        [data-theory-sidebar][data-collapsed="true"] .theory-nav-label,
        [data-theory-sidebar][data-collapsed="true"] .theory-nav-count,
        [data-theory-sidebar][data-collapsed="true"] .theory-nav-toggle,
        [data-theory-sidebar][data-collapsed="true"] .theory-nav-children {
            display: none !important;
        }

        [data-theory-sidebar][data-theory-sidebar-searching="true"] .theory-nav-children {
            display: block !important;
        }

        [data-theory-layout][data-settled="false"] [data-theory-sidebar] [data-theory-sidebar-scroll],
        [data-theory-sidebar][data-collapsed="true"] [data-theory-sidebar-scroll] {
            padding-left: 3px;
            padding-right: 8px;
            scrollbar-gutter: stable;
        }

        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-nav-link,
        [data-theory-sidebar][data-collapsed="true"] .theory-nav-link {
            display: flex;
            justify-content: center;
            border-radius: 18px;
            padding: 10px !important;
        }

        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-nav-icon,
        [data-theory-sidebar][data-collapsed="true"] .theory-nav-icon {
            height: 44px;
            width: 44px;
        }

        [data-theory-menu-children][hidden] {
            display: none;
        }

        [data-theory-menu-fragment="desktop"] {
            --menu-current-fill: 88%;
            --menu-branch-fill: 48%;
        }

        [data-theory-menu-fragment="mobile"] {
            --menu-current-fill: 86%;
            --menu-branch-fill: 46%;
        }

        /* Reset on every category so active branch colors do not spill into sibling or child cards. */
        [data-theory-menu-category] {
            --menu-category-border: var(--line);
            --menu-icon-border: color-mix(in srgb, var(--accent) 16%, var(--line));
            --menu-icon-background: color-mix(in srgb, var(--accent-soft) 38%, white);
            --menu-icon-stroke: var(--accent);
            --menu-toggle-border: var(--line);
            --menu-detail-color: var(--muted);
            --menu-count-background: rgba(91, 108, 128, 0.10);
            --menu-category-dot: color-mix(in srgb, var(--muted) 30%, white);
        }

        [data-theory-menu-active-branch] {
            --menu-category-border: color-mix(in srgb, var(--accent) 40%, var(--line));
            --menu-category-background: color-mix(in srgb, var(--accent-soft) var(--menu-branch-fill), white);
            --menu-icon-border: transparent;
            --menu-icon-background: linear-gradient(135deg, var(--accent) 0%, color-mix(in srgb, var(--accent) 76%, white) 100%);
            --menu-icon-stroke: #ffffff;
            --menu-toggle-border: color-mix(in srgb, var(--accent) 35%, var(--line));
            --menu-detail-color: var(--accent);
            --menu-count-background: rgba(47, 103, 177, 0.12);
            --menu-category-dot: var(--accent);
        }

        [data-theory-menu-category-link] {
            border-color: var(--menu-category-border);
            color: var(--text);
        }

        [data-theory-menu-category-link][data-theory-menu-branch-link] {
            background: var(--menu-category-background) !important;
        }

        [data-theory-menu-category-link][data-theory-nav-active="true"] {
            border-color: var(--accent);
            background: linear-gradient(180deg, color-mix(in srgb, var(--accent-soft) var(--menu-current-fill), white) 0%, white 100%) !important;
            box-shadow: inset 4px 0 0 var(--accent);
        }

        [data-theory-menu-icon] {
            border-color: var(--menu-icon-border);
            background: var(--menu-icon-background);
        }

        [data-theory-menu-icon] > svg {
            color: var(--menu-icon-stroke) !important;
        }

        [data-theory-menu-toggle] {
            border-color: var(--menu-toggle-border);
            color: var(--menu-detail-color);
        }

        [data-theory-menu-toggle][aria-expanded="true"] > svg {
            transform: rotate(90deg);
        }

        [data-theory-menu-count] {
            background: var(--menu-count-background);
            color: var(--menu-detail-color);
        }

        [data-theory-menu-category-dot] {
            background: var(--menu-category-dot);
        }

        [data-theory-menu-page] {
            color: var(--muted);
        }

        [data-theory-menu-page-dot] {
            border-color: color-mix(in srgb, var(--line) 88%, var(--muted));
        }

        [data-theory-nav-current-page="true"] {
            background: color-mix(in srgb, var(--accent-soft) 78%, white) !important;
            color: var(--text);
        }

        [data-theory-nav-current-page="true"] [data-theory-menu-page-dot] {
            border-color: var(--accent);
            background: var(--accent);
        }
    </style>
@endonce
