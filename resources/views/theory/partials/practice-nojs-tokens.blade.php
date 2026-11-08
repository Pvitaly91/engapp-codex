<noscript>
    <x-theory-practice-token-bank caption="Банк токенів" :attributes="new \Illuminate\View\ComponentAttributeBag(['data-'.$practiceScope.'-static-token-bank' => ''])">
        @foreach($control['tokens'] as $token)
            <x-theory-practice-token :static="true" :attributes="new \Illuminate\View\ComponentAttributeBag(['data-'.$practiceScope.'-static-token' => ''])">{{ $token }}</x-theory-practice-token>
        @endforeach
    </x-theory-practice-token-bank>
</noscript>
