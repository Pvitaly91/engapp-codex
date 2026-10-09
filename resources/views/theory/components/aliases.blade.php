@foreach($node['aliases'] ?? [] as $alias)
    <span id="{{ $alias }}" class="theory-subtitle-anchor" aria-hidden="true"></span>
@endforeach
