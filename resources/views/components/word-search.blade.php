<!-- Word Search Component - Modern Design -->
<div x-data="wordSearch()" class="relative mb-6">
    <label for="word-search" class="block text-sm font-semibold text-gray-700 mb-2">
        <svg class="w-4 h-4 inline-block mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        Пошук слова
    </label>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <input id="word-search" type="text" x-model="query" @input="search" placeholder="Введіть слово для пошуку..."
               class="w-full pl-12 pr-4 py-3 text-base rounded-xl border-2 border-gray-200 bg-white shadow-sm focus:outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 placeholder-gray-400" autocomplete="off">
    </div>
    <!-- Dropdown Results -->
    <div x-show="results.length" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="absolute z-20 left-0 right-0 mt-2 bg-white rounded-2xl border-2 border-gray-100 shadow-xl overflow-hidden max-h-80 overflow-y-auto">
        <template x-for="(item, index) in results" :key="item.word">
            <div class="px-4 py-3 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 transition-colors cursor-pointer border-b border-gray-100 last:border-b-0">
                <div class="flex items-center gap-3">
                    <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-gradient-to-br from-indigo-100 to-purple-100 text-indigo-600 text-sm font-bold" x-text="index + 1"></span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-gray-900" x-text="item.word"></span>
                            <span class="text-gray-400">—</span>
                            <span class="text-gray-600" x-text="item.translation"></span>
                        </div>
                        <template x-if="item.forms.base.length || item.forms.past.length || item.forms.participle.length">
                            <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                <template x-if="item.forms.base.length">
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700" x-text="item.forms.base.join(', ')"></span>
                                </template>
                                <template x-if="item.forms.past.length">
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700" x-text="item.forms.past.join(', ')"></span>
                                </template>
                                <template x-if="item.forms.participle.length">
                                    <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700" x-text="item.forms.participle.join(', ')"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
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
