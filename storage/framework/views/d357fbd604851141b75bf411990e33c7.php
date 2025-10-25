<div x-data="wordSearch()" class="mb-6">
    <label for="word-search" class="block text-sm font-medium text-gray-700 mb-1">Пошук слова</label>
    <input id="word-search" type="text" x-model="query" @input="search" placeholder="Введіть слово"
           class="w-full rounded border px-4 py-2" autocomplete="off">
    <div x-show="results.length" class="mt-2 bg-white border rounded shadow divide-y">
        <template x-for="item in results" :key="item.word">
            <div class="px-3 py-1 text-sm">
                <span class="font-semibold" x-text="item.word"></span>
                <span> — <span x-text="item.translation"></span></span>
                <template x-if="item.forms.base.length || item.forms.past.length || item.forms.participle.length">
                    <span>
                        <template x-if="item.forms.base.length">
                            <span> — <span x-text="item.forms.base.join(', ')"></span></span>
                        </template>
                        <template x-if="item.forms.past.length">
                            <span> — <span x-text="item.forms.past.join(', ')"></span></span>
                        </template>
                        <template x-if="item.forms.participle.length">
                            <span> — <span x-text="item.forms.participle.join(', ')"></span></span>
                        </template>
                    </span>
                </template>
            </div>
        </template>
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
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/components/word-search.blade.php ENDPATH**/ ?>