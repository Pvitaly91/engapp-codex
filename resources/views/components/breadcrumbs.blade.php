<nav class="text-sm text-gray-600 mb-4" aria-label="Breadcrumb">
    <ol class="list-reset flex items-center">
        @foreach($items as $item)
            <li class="flex items-center">
                @if(!$loop->last)
                    <a href="{{ $item['url'] ?? '#' }}" class="text-blue-600 hover:underline">{{ $item['label'] }}</a>
                    <span class="mx-2">/</span>
                @else
                    <span class="text-gray-700">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
