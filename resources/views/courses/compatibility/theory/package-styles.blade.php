{{-- Historical non-theory consumers retain their presentation unchanged. --}}
@if($m42Design !== null)
    @include('engram.theory.blocks-v3.m42-native-design-styles')
@endif
@if($m27 !== null && isset($decodedBody['m43_v1']))
    @include('engram.theory.blocks-v3.m43-native-styles')
@endif
@if($m27 !== null && isset($decodedBody['m44_v1']))
    @include('engram.theory.blocks-v3.m44-native-styles')
@endif
@if($m27 !== null && isset($decodedBody['m41_v1']) && $decodedBody['m41_v1']['role'] === 'section')
    @if(isset($m27['data']['m41_existing_design']))
        @include('engram.theory.blocks-v3.m41-existing-design-styles')
    @else
        @include('engram.theory.blocks-v3.m41-section-styles')
    @endif
@endif
