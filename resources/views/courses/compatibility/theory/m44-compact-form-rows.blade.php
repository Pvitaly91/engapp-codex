<div class="space-y-4 my-3">
    @foreach($rows as $row)
        <div data-m44-form-row="{{ $row['point'] }}">
            <p class="text-xs font-semibold text-muted-foreground mb-1">{{ $row['label'] }}</p>
            <p class="text-sm font-semibold text-foreground" data-m44-formula>{{ $row['formula'] }}</p>
            @include('engram.theory.blocks-v3.m44-compact-form-examples', ['formExamples' => [$row['example']]])
        </div>
    @endforeach
</div>
