<div x-data="wordSearch()" @click.outside="close()" class="space-y-1.5 sm:space-y-2">
    <div class="flex flex-wrap items-center justify-between gap-1">
        <label for="word-search" class="text-xs sm:text-sm font-semibold text-gray-800">{{ __('frontend.tests.word_search.label') }}</label>
        <span class="text-[11px] sm:text-xs text-gray-500">{{ __('frontend.tests.word_search.hint') }}</span>
    </div>
    <div class="relative">
        <input id="word-search" type="text" x-model="query" @input.debounce.150ms="search" @focus="reopen()" @keydown.escape.stop.prevent="close()" placeholder="{{ __('frontend.tests.word_search.placeholder') }}"
               class="w-full rounded-xl sm:rounded-2xl border border-indigo-100 bg-white px-3 py-2.5 text-sm sm:px-4 sm:py-3 sm:text-base shadow transition focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" autocomplete="off">
        <div x-show="open && results.length" x-cloak x-transition class="absolute left-0 right-0 z-20 mt-2 rounded-2xl border border-gray-200 bg-white shadow-xl">
            <div class="divide-y divide-gray-100 max-h-72 overflow-y-auto">
                <template x-for="item in results" :key="item.word">
                    <div class="px-4 py-3 text-sm hover:bg-indigo-50/60">
                        <div class="flex items-start justify-between gap-4">
                            <span class="font-semibold text-gray-900" x-text="item.word"></span>
                            <span class="text-gray-600" x-text="item.translation"></span>
                        </div>
                        <template x-if="item.forms.base.length || item.forms.past.length || item.forms.participle.length">
                            <div class="mt-1 flex flex-wrap gap-2 text-gray-600">
                                <template x-if="item.forms.base.length">
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium" x-text="item.forms.base.join(', ')"></span>
                                </template>
                                <template x-if="item.forms.past.length">
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium" x-text="item.forms.past.join(', ')"></span>
                                </template>
                                <template x-if="item.forms.participle.length">
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium" x-text="item.forms.participle.join(', ')"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
<script>
function wordSearch() {
    const endpoint = @json(localized_route('words.search'));

    return {
        query: '',
        results: [],
        open: false,
        requestId: 0,
        close() {
            this.open = false;
        },
        reopen() {
            if (this.query.trim().length >= 2 && this.results.length) {
                this.open = true;
            }
        },
        async search() {
            const query = this.query.trim();
            const requestId = ++this.requestId;

            if (query.length < 2) {
                this.results = [];
                this.open = false;
                return;
            }

            try {
                const response = await fetch(endpoint + '?q=' + encodeURIComponent(query), {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (requestId !== this.requestId) {
                    return;
                }

                this.results = Array.isArray(data) ? data : [];
                this.open = this.results.length > 0;
            } catch (error) {
                if (requestId !== this.requestId) {
                    return;
                }

                this.results = [];
                this.open = false;
            }
        }
    }
}
</script>
