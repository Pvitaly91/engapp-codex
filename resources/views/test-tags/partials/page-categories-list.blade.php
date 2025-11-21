@if($categories->isEmpty())
    <div class="rounded-lg bg-slate-50 px-4 py-8 text-center text-slate-500">
        {{ $emptyMessage }}
    </div>
@else
    <div class="space-y-2">
        @foreach($categories as $category)
            <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h4 class="font-medium text-slate-900">{{ $category['title'] }}</h4>
                        <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10">
                            {{ $category['pages_count'] }} {{ $category['pages_count'] === 1 ? 'сторінка' : 'сторінок' }}
                        </span>
                    </div>
                    <div class="mt-1 text-sm text-slate-500">
                        Slug: {{ $category['slug'] }}
                    </div>
                </div>
                <a href="{{ $category['url'] }}" 
                   target="_blank"
                   class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Переглянути категорію
                </a>
            </div>
        @endforeach
    </div>
    <div class="mt-4 text-sm text-slate-500">
        Всього категорій: {{ $categories->count() }}
    </div>
@endif
