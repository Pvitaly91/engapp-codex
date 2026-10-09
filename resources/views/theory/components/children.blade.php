@foreach($children ?? [] as $child)
    @include('theory.components.node', ['node' => $child])
@endforeach
