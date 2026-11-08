@php
    $deferredPracticeFields = \App\Support\ModalVerbsPracticeCompact::deferredFields($compactItem ?? null, $item);
    $deferredPracticeVisibility = "isItemChecked('".$practiceKind."', ".$index.") && !isEmpty('".$practiceKind."', ".$index.")";
@endphp
@if($deferredPracticeFields !== [])
    <x-theory-practice-explanation :disclosure="true" title="Повна умова й пояснення"
        :attributes="new \Illuminate\View\ComponentAttributeBag([
            'x-show' => $deferredPracticeVisibility,
            'data-practice-source-key' => $practiceKind.'-'.$index,
            'class' => 'mt-3'.$instructionPresentation,
        ])">
        <div class="space-y-3">
            @foreach($deferredPracticeFields as $deferredField)
                @if($deferredField['kind'] === 'options')
                    <ol class="list-decimal pl-5 space-y-3">
                        @foreach($deferredField['value'] as $originalOption)<li>{{ $originalOption }}</li>@endforeach
                    </ol>
                @else
                    <div>{!! $deferredField['value'] !!}</div>
                @endif
            @endforeach
        </div>
    </x-theory-practice-explanation>
@endif
