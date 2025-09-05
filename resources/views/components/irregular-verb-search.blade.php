<div x-data="irregularVerbSearch()" class="mb-6">
    <label for="verb-search" class="block text-sm font-medium text-gray-700 mb-1">Пошук неправильного дієслова</label>
    <input id="verb-search" type="text" x-model="query" @input="search" placeholder="enter base form"
           class="w-full rounded border px-4 py-2" autocomplete="off">
    <div x-show="results.length" class="mt-2 bg-white border rounded shadow divide-y">
        <template x-for="item in results" :key="item.base">
            <div class="px-3 py-1 text-sm">
                <span class="font-semibold" x-text="item.base"></span>
                <template x-if="item.past.length">
                    <span> — <span x-text="item.past.join(', ')"></span></span>
                </template>
                <template x-if="item.participle.length">
                    <span> — <span x-text="item.participle.join(', ')"></span></span>
                </template>
            </div>
        </template>
    </div>
</div>
<script>
function irregularVerbSearch() {
    return {
        query: '',
        results: [],
        search() {
            if (this.query.length < 2) {
                this.results = [];
                return;
            }
            fetch('/irregular-verbs?q=' + encodeURIComponent(this.query))
                .then(res => res.json())
                .then(data => this.results = data);
        }
    }
}
</script>
