<div x-data="wordSearch()" class="space-y-2">
    <div class="flex items-center justify-between">
        <label for="word-search" class="text-sm font-semibold text-gray-800">Пошук слова</label>
        <span class="text-xs text-gray-500">Швидка допомога під час тесту</span>
    </div>
    <div class="relative">
        <input id="word-search" type="text" x-model="query" @input="search" placeholder="Введіть слово"
               class="w-full rounded-2xl border border-indigo-100 bg-white px-4 py-3 text-base shadow-sm transition focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" autocomplete="off">
        <div x-show="results.length" x-transition class="absolute left-0 right-0 z-20 mt-2 rounded-2xl border border-gray-200 bg-white shadow-xl">
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
    return {
        query: '',
        results: [],
        search() {
            if (this.query.length < 2) {
                this.results = [];
                return;
            }
            fetch('/admin/words?q=' + encodeURIComponent(this.query))
                .then(res => res.json())
                .then(data => this.results = data);
        }
    }
}
</script>
