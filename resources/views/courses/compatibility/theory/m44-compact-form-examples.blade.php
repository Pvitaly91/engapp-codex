<div class="space-y-2 mt-2">
    @foreach($formExamples as $example)
        <div>
            <p lang="en" class="text-sm text-foreground">{{ $example['en'] }}</p>
            <p lang="uk" class="text-sm text-muted-foreground">{{ $example['uk'] }}</p>
            @if(isset($example['note_uk']))<p lang="uk" class="text-sm text-muted-foreground">{{ $example['note_uk'] }}</p>@endif
        </div>
    @endforeach
</div>
