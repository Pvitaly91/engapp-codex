@extends('layouts.public-v2')

@section('title', __('public.search.title'))

@section('content')
<div class="space-y-8">
    <section class="glass-card rounded-3xl p-6 shadow-lifted border border-white/5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">{{ __('public.search.results_title') }}</p>
                <h1 class="text-3xl font-semibold text-white">{{ __('public.search.found_for') }} <span class="text-lilac">"{{ e($query) }}"</span></h1>
                <p class="text-sm text-slate-300">{{ __('public.search.try_another') }}</p>
            </div>
            <form action="{{ route('site.search') }}" method="GET" class="w-full md:w-auto md:min-w-[320px]">
                <label class="sr-only" for="search-q">{{ __('public.search.placeholder') }}</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔎</span>
                    <input id="search-q" name="q" type="search" value="{{ e($query) }}" class="input-base w-full rounded-2xl border-white/10 bg-white/5 px-10 py-3 text-sm" placeholder="{{ __('public.search.placeholder') }}" />
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-xl bg-gradient-to-r from-lilac to-mint px-4 py-2 text-xs font-semibold text-white shadow-soft focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lilac">{{ __('public.search.button') }}</button>
                </div>
            </form>
        </div>
        <div class="mt-6 grid gap-4 md:grid-cols-[1.5fr_.9fr]">
            <div class="grid gap-4 sm:grid-cols-2" aria-label="Search highlights">
                <div class="rounded-2xl border border-white/5 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-[0.12em] text-slate-400">{{ __('public.common.type_theory') }}</p>
                    <p class="mt-2 text-sm text-slate-200">{{ __('public.nav.theory') }} &amp; {{ __('public.nav.catalog') }} {{ __('public.common.go_to') }}</p>
                </div>
                <div class="rounded-2xl border border-white/5 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-[0.12em] text-slate-400">{{ __('public.search.dictionary') }}</p>
                    <p class="mt-2 text-sm text-slate-200">{{ __('public.search.dictionary_title') }}</p>
                </div>
            </div>
            <div class="rounded-2xl border border-white/5 bg-night/60 p-4" x-data="dictionarySearch('{{ $__currentLocale ?? app()->getLocale() }}')" aria-label="Dictionary search">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <p class="text-xs uppercase tracking-[0.12em] text-slate-400">{{ __('public.search.dictionary') }}</p>
                        <p class="text-sm font-semibold text-white">{{ __('public.search.dictionary_title') }}</p>
                    </div>
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] uppercase text-slate-300">{{ strtoupper($__currentLocale ?? app()->getLocale()) }}</span>
                </div>
                <div class="mt-3">
                    <label for="dictionary-inline" class="sr-only">{{ __('public.search.dictionary_placeholder') }}</label>
                    <input id="dictionary-inline" type="search" x-model="query" @input.debounce.300="lookup" class="input-base w-full rounded-xl px-3 py-2 text-sm" placeholder="{{ __('public.search.dictionary_placeholder') }}" />
                </div>
                <div class="mt-3 max-h-52 overflow-auto rounded-xl border border-white/5 bg-[#0b1221]">
                    <template x-if="loading">
                        <p class="px-4 py-3 text-sm text-slate-300">{{ __('public.common.loading') }}</p>
                    </template>
                    <template x-if="!loading && results.length === 0 && query.length">
                        <p class="px-4 py-3 text-sm text-slate-400">{{ __('public.search.no_translation') }}</p>
                    </template>
                    <template x-for="item in results" :key="item.en">
                        <div class="flex items-start justify-between gap-3 border-b border-white/5 px-4 py-3 last:border-b-0">
                            <div>
                                <p class="font-semibold text-white" x-text="item.en"></p>
                                <p class="text-sm text-slate-300" x-text="item.translation || '{{ __('public.search.no_translation') }}'"></p>
                            </div>
                            <span class="rounded-full bg-white/5 px-2 py-1 text-[11px] uppercase text-slate-400">{{ __('public.search.word') }}</span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-white">{{ __('public.search.results_title') }}</h2>
            <span class="rounded-full bg-white/5 px-3 py-1 text-xs text-slate-300">{{ $results->count() }} {{ __('public.search.results_title') }}</span>
        </div>

        @if($results->isEmpty())
            <div class="rounded-3xl border border-dashed border-white/10 bg-white/5 p-10 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white/5 text-lilac">🔍</div>
                <p class="text-lg font-semibold text-white">{{ __('public.search.nothing_found') }}</p>
                <p class="mt-2 text-sm text-slate-300">{{ __('public.search.try_another') }}</p>
                <a href="{{ route('catalog.tests-cards') }}" class="mt-4 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-lilac to-mint px-6 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:-translate-y-0.5 hover:shadow-lifted">{{ __('public.search.to_catalog') }}</a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($results as $item)
                    <a href="{{ $item['url'] }}" class="group flex flex-col gap-3 rounded-2xl border border-white/5 bg-[#0b1221]/80 p-5 shadow-soft transition hover:-translate-y-1 hover:border-lilac/50 hover:shadow-lifted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lilac">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-base font-semibold text-white group-hover:text-lilac">{{ $item['title'] }}</h3>
                            <span class="shrink-0 rounded-full bg-white/10 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-200">
                                {{ $item['type'] === 'page' ? __('public.common.type_theory') : __('public.common.type_test') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-lilac">
                            <span>{{ __('public.common.go_to') }}</span>
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>

<script>
    function dictionarySearch(locale) {
        return {
            query: '',
            results: [],
            loading: false,
            async lookup() {
                const q = this.query.trim();
                if (!q) { this.results = []; return; }
                this.loading = true;
                try {
                    const res = await fetch(`/api/search?lang=${locale}&q=${encodeURIComponent(q)}`);
                    if (res.ok) {
                        this.results = await res.json();
                    }
                } catch (e) {
                    console.warn(e);
                }
                this.loading = false;
            },
        };
    }
</script>
@endsection
