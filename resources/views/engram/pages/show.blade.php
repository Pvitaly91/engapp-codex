@extends('layouts.engram')

@section('title', $page->title)

@section('content')
    @php($mobileSelectedCategory = $selectedCategory ?? null)
    @php($mobileCategoryPages = ($categoryPages ?? collect()))
    @php($hasCategoryPages = $mobileSelectedCategory && $mobileCategoryPages->isNotEmpty())

    <div class="space-y-6 lg:space-y-0 lg:grid lg:grid-cols-[260px_1fr] lg:items-start lg:gap-8">
        <div
            class="lg:hidden space-y-4"
            x-data="{
                showCategories: false,
                showPages: {{ $hasCategoryPages ? 'true' : 'false' }}
            }"
        >
            <div class="rounded-2xl border border-border/80 bg-card shadow-soft">
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold"
                    @click="showCategories = !showCategories"
                    :aria-expanded="showCategories"
                >
                    <span>Категорії</span>
                    <span class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                        <span>{{ $mobileSelectedCategory->title ?? 'Оберіть категорію' }}</span>
                        <svg
                            class="h-4 w-4 transition-transform"
                            :class="{ 'rotate-180': showCategories }"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
                <div x-show="showCategories" x-transition style="display: none;" class="border-t border-border/80">
                    <nav class="space-y-1 px-4 py-3">
                        @forelse($categories as $category)
                            @php($isActive = $mobileSelectedCategory && $mobileSelectedCategory->is($category))
                            <a
                                href="{{ route('pages.category', $category->slug) }}"
                                class="block rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-muted/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 {{ $isActive ? 'bg-primary/10 text-primary' : 'text-muted-foreground' }}"
                                @if($isActive) aria-current="page" @endif
                            >
                                {{ $category->title }}
                            </a>
                        @empty
                            <p class="px-3 py-2 text-sm text-muted-foreground">Немає категорій.</p>
                        @endforelse
                    </nav>
                </div>
            </div>

            @if($hasCategoryPages)
                <div class="rounded-2xl border border-border/80 bg-card shadow-soft">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold"
                        @click="showPages = !showPages"
                        :aria-expanded="showPages"
                    >
                        <span>Сторінки розділу</span>
                        <svg
                            class="h-4 w-4 transition-transform"
                            :class="{ 'rotate-180': showPages }"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="showPages" x-transition style="display: none;" class="border-t border-border/80">
                        <nav class="space-y-1 px-4 py-3">
                            @foreach($mobileCategoryPages as $pageItem)
                                @php($isCurrentPage = $page->is($pageItem))
                                <a
                                    href="{{ route('pages.show', [$mobileSelectedCategory->slug, $pageItem->slug]) }}"
                                    class="block rounded-xl px-3 py-2 text-sm transition hover:bg-muted/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary/40 {{ $isCurrentPage ? 'bg-secondary/20 text-secondary-foreground font-semibold' : 'text-muted-foreground' }}"
                                    @if($isCurrentPage) aria-current="page" @endif
                                >
                                    {{ $pageItem->title }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </div>
            @endif
        </div>

        <div class="hidden lg:block">
            @include('engram.pages.partials.sidebar', [
                'categories' => $categories,
                'selectedCategory' => $selectedCategory ?? null,
                'categoryPages' => $categoryPages ?? collect(),
                'currentPage' => $page,
                'showCategoryPagesNav' => true,
                'class' => 'lg:sticky lg:top-24',
            ])
        </div>

        <article class="max-w-none space-y-4 lg:col-start-2">
            @include('engram.pages.partials.grammar-card', [
                'page' => $page,
                'subtitleBlock' => $subtitleBlock ?? null,
                'columns' => $columns ?? [],
                'locale' => $locale ?? app()->getLocale(),
            ])

            @if(isset($relatedTests) && $relatedTests->isNotEmpty())
                <div class="rounded-2xl border border-border/80 bg-card shadow-soft p-6">
                    <h2 class="text-lg font-semibold mb-4">Пов'язані тести</h2>
                    <div class="space-y-2">
                        @foreach($relatedTests as $test)
                            <a
                                href="{{ route('saved-test.js', $test->slug) }}"
                                class="block rounded-xl border border-border/60 bg-background p-4 transition hover:border-primary/40 hover:bg-primary/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50"
                            >
                                <h3 class="font-medium text-foreground">{{ $test->name }}</h3>
                                @if($test->description)
                                    <p class="mt-1 text-sm text-muted-foreground line-clamp-2">{{ $test->description }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </article>
    </div>
@endsection
