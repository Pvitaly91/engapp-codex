@php
    $categories = $categories ?? collect();
    $searchId = $searchId ?? 'theory-sidebar-search';
@endphp

@if($categories->isNotEmpty())
    <div class="theory-sidebar-expanded-only mt-4 shrink-0 rounded-[24px] border p-3 shadow-sm surface-card" style="border-color: var(--line);" data-theory-sidebar-search>
        <label for="{{ $searchId }}" class="block">
            <span class="text-[10px] font-extrabold uppercase tracking-[0.2em]" style="color: var(--accent);">{{ __('public.theory.category_search_label') }}</span>
            <span class="mt-2 flex items-center gap-2 rounded-[18px] border px-3 py-2.5 surface-card-strong" style="border-color: var(--line);">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true" style="color: var(--accent);">
                    <path d="m21 21-4.35-4.35m1.35-5.15a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <input
                    id="{{ $searchId }}"
                    type="search"
                    autocomplete="off"
                    data-theory-sidebar-search-input
                    class="min-w-0 flex-1 bg-transparent text-sm font-bold outline-none placeholder:font-semibold"
                    style="color: var(--text);"
                    placeholder="{{ __('public.theory.category_search_placeholder') }}"
                >
            </span>
        </label>
        <div class="mt-3 flex items-center justify-between gap-2">
            <span class="rounded-full px-3 py-1.5 text-xs font-extrabold soft-accent" data-theory-sidebar-search-count>
                {{ $categories->count() }} / {{ $categories->count() }}
            </span>
            <button type="button" class="hidden rounded-2xl border px-3 py-2 text-xs font-extrabold transition hover:bg-slate-50" style="border-color: var(--line); color: var(--muted);" data-theory-sidebar-search-clear>
                {{ __('public.theory.category_search_clear') }}
            </button>
        </div>
        <div class="mt-3 hidden rounded-[18px] border border-dashed px-3 py-3 text-center" style="border-color: var(--line);" data-theory-sidebar-search-empty>
            <p class="text-sm font-extrabold">{{ __('public.theory.category_search_empty_title') }}</p>
            <p class="mt-1 text-xs leading-5" style="color: var(--muted);">{{ __('public.theory.category_search_empty_hint') }}</p>
        </div>
    </div>
@endif
