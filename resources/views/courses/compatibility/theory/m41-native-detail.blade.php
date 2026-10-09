<div class="space-y-3 m41-native-detail">
    <h4 class="text-sm font-bold text-foreground">{{ $detail['title'] }}</h4>
    @foreach($detail['paragraphs_uk'] as $paragraph)<p class="text-sm leading-relaxed">{{ $paragraph }}</p>@endforeach
    @foreach($detail['examples'] as $example)
        @include('engram.theory.blocks-v3.m41-native-example')
    @endforeach
</div>
