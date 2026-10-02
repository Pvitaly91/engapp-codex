@if(($pointSections[$index] ?? null)?->detail !== null)
    <div data-theory-section="{{ $pointSections[$index]->key }}" data-theory-native-extension data-theory-point-index="{{ $index }}">
        @include('theory.partials.section-disclosure', ['section' => $pointSections[$index]])
    </div>
@endif
