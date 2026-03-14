@extends('layouts.catalog-public')

@section('title', __('public.nav.catalog'))

@section('content')
<div class="overflow-hidden">
    <section class="relative border-b px-5 py-8 sm:px-8 lg:px-10 lg:py-10" style="border-color: var(--line);">
        <div class="absolute right-[8%] top-10 hidden h-20 w-20 rounded-full bg-amber/80 lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-56 w-16 rounded-tl-[2.5rem] bg-ocean lg:block"></div>
        <div class="relative grid gap-8 lg:grid-cols-[1.02fr_0.98fr] lg:items-center">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.28em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    {{ __('public.nav.catalog') }}
                </span>
                <h1 class="mt-6 font-display text-4xl font-extrabold leading-[1.04] sm:text-5xl xl:text-[3.4rem]">Каталог тестів</h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 sm:text-xl" style="color: var(--muted);">
                    Обирайте тести за агрегованими темами й рівнями. Фільтри, рівні, картки тестів і переходи працюють так само, як у поточному публічному каталозі.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <article class="rounded-[28px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Tests</p>
                    <p class="mt-3 font-display text-[3rem] font-extrabold leading-none">{{ $tests->count() }}</p>
                    <p class="mt-3 text-sm leading-6" style="color: var(--muted);">Кількість тестів після поточних фільтрів</p>
                </article>
                <article class="rounded-[28px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Tags</p>
                    <p class="mt-3 font-display text-[3rem] font-extrabold leading-none">{{ $tags->flatten()->count() }}</p>
                    <p class="mt-3 text-sm leading-6" style="color: var(--muted);">Агреговані теги каталогу</p>
                </article>
            </div>
        </div>
    </section>

    <section class="px-5 py-8 sm:px-8 lg:px-10 lg:py-10">
        <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
            <aside>
                <div class="sticky top-24 space-y-5">
                    <section class="rounded-[28px] border p-5 shadow-card surface-card-strong" style="border-color: var(--line);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Filters</p>
                                <h2 class="mt-2 font-display text-[1.8rem] font-extrabold leading-none">Catalog map</h2>
                            </div>
                            @if(!empty($selectedTags) || !empty($selectedLevels))
                                <a href="{{ localized_route('catalog.tests-cards') }}" class="text-xs font-extrabold uppercase tracking-[0.18em]" style="color: var(--accent);">Reset</a>
                            @endif
                        </div>

                        <form id="tag-filter" action="{{ localized_route('catalog.tests-cards') }}" method="GET" class="mt-5 space-y-5">
                            @if(isset($availableLevels) && $availableLevels->count())
                                <div class="rounded-[22px] border p-4 surface-card" style="border-color: var(--line);">
                                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Level</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach($availableLevels as $lvl)
                                            @php $id = 'level-' . md5($lvl); @endphp
                                            <div>
                                                <input type="checkbox" name="levels[]" value="{{ $lvl }}" id="{{ $id }}" class="hidden peer" {{ in_array($lvl, $selectedLevels ?? []) ? 'checked' : '' }}>
                                                <label for="{{ $id }}" class="inline-flex cursor-pointer rounded-full border px-3 py-2 text-sm font-semibold transition peer-checked:bg-ocean peer-checked:text-white" style="border-color: var(--line); color: var(--text);">
                                                    {{ $lvl }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @foreach($tags as $category => $tagNames)
                                @php $isOther = in_array(strtolower($category), ['other', 'others']); @endphp
                                <div class="rounded-[22px] border p-4 surface-card" style="border-color: var(--line);">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ $category }}</p>
                                        @if($isOther)
                                            <button type="button" id="toggle-others-btn" class="text-xs font-bold uppercase tracking-[0.18em]" style="color: var(--muted);">Show</button>
                                        @endif
                                    </div>
                                    <div id="{{ $isOther ? 'others-tags' : '' }}" class="mt-3 flex flex-wrap gap-2" @if($isOther) style="display:none;" @endif>
                                        @foreach($tagNames as $tag)
                                            @php $id = 'tag-' . md5($tag); @endphp
                                            <div>
                                                <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                                <label for="{{ $id }}" class="inline-flex cursor-pointer rounded-full border px-3 py-2 text-xs font-bold transition peer-checked:bg-ocean peer-checked:text-white" style="border-color: var(--line); color: var(--text);">
                                                    {{ $tag }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </form>
                    </section>
                </div>
            </aside>

            <div class="min-w-0">
                @if($tests->count())
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($tests as $index => $test)
                            @php
                                $accents = ['bg-ocean', 'bg-amber', 'bg-emerald-500', 'bg-slate-800 dark:bg-slate-200', 'bg-rose-500', 'bg-sky-500'];
                                $accent = $accents[$index % count($accents)];
                                $preferredView = data_get($test->filters, 'preferred_view');
                                if ($preferredView === 'drag-drop') {
                                    $testRoute = localized_route('test.drag-drop', $test->slug);
                                } elseif ($preferredView === 'match') {
                                    $testRoute = localized_route('test.match', $test->slug);
                                } elseif ($preferredView === 'dialogue') {
                                    $testRoute = localized_route('test.dialogue', $test->slug);
                                } else {
                                    $testRoute = localized_route('test.show', $test->slug);
                                }
                                $order = array_flip(['A1','A2','B1','B2','C1','C2']);
                                $levels = $test->levels
                                    ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
                                    ->map(fn($lvl) => $lvl ?? 'N/A');
                            @endphp
                            <article class="overflow-hidden rounded-[26px] border shadow-card surface-card-strong" style="border-color: var(--line);">
                                <a href="{{ $testRoute }}" class="block border-b p-6" style="border-color: var(--line);">
                                    <div class="flex items-start justify-between gap-4">
                                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-[20px] {{ $accent }} text-sm font-extrabold text-white dark:text-slate-950">
                                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                        <span class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--muted);">
                                            {{ count($test->questions) }} Q
                                        </span>
                                    </div>
                                    <h3 class="mt-5 font-display text-[1.75rem] font-extrabold leading-tight">{{ $test->name }}</h3>
                                </a>

                                <div class="space-y-4 p-5">
                                    @if($levels->isNotEmpty())
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($levels as $lvl)
                                                <span class="rounded-full px-3 py-1.5 text-xs font-bold" style="background: var(--accent-soft); color: var(--text);">{{ $lvl }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($test->tag_names->isNotEmpty())
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($test->tag_names->take(4) as $tag)
                                                <span class="rounded-full border px-3 py-1.5 text-xs font-bold" style="border-color: var(--line); color: var(--muted);">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between gap-3 text-xs font-bold uppercase tracking-[0.18em]" style="color: var(--muted);">
                                        <span>{{ optional($test->created_at)->format('d.m.Y') }}</span>
                                        <span>{{ in_array($preferredView, ['drag-drop', 'match', 'dialogue'], true) ? $preferredView : 'card' }}</span>
                                    </div>

                                    <a href="{{ $testRoute }}" class="inline-flex items-center gap-2 rounded-[18px] bg-ocean px-4 py-3 text-sm font-extrabold uppercase tracking-[0.18em] text-white transition hover:bg-[#245592]">
                                        Пройти тест
                                        <span>+</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-[28px] border border-dashed p-10 text-center shadow-card surface-card-strong" style="border-color: var(--line);">
                        <h3 class="font-display text-[1.8rem] font-extrabold">Тестів не знайдено</h3>
                        <p class="mt-3 text-sm leading-6" style="color: var(--muted);">Спробуйте змінити поточні фільтри або скинути їх.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

<script>
    document.querySelectorAll('#tag-filter input[type=checkbox]').forEach((element) => {
        element.addEventListener('change', () => document.getElementById('tag-filter').submit());
    });

    const toggleBtn = document.getElementById('toggle-others-btn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const tags = document.getElementById('others-tags');
            const hidden = tags.style.display === 'none';
            tags.style.display = hidden ? '' : 'none';
            toggleBtn.textContent = hidden ? 'Hide' : 'Show';
        });
    }
</script>
@endsection
