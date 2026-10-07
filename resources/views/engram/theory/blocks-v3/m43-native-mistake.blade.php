<article class="theory-item m43-mistake rounded-xl border bg-white p-4">
    <h3 class="text-xs font-bold uppercase tracking-wider text-rose-700 mb-3">{{ $point['title'] }}</h3>
    <div class="text-sm leading-relaxed space-y-3">{!! \App\Support\M43NativeHtml::paragraphs($point['paragraphs_uk']) !!}</div>
    <div class="theory-example theory-example--wrong flex items-start gap-2.5 rounded-lg border px-3 py-2 mt-3">
        <span aria-hidden="true" data-theory-ui>✕</span>
        <div class="min-w-0">
            <p lang="en" class="font-mono text-xs line-through">{{ $point['wrong_en'] }}</p>
            @if(isset($point['wrong_uk']))<p lang="uk" class="theory-translation text-xs italic">{{ $point['wrong_uk'] }}</p>@endif
        </div>
    </div>
    <div class="theory-example theory-example--right flex items-start gap-2.5 rounded-lg border px-3 py-2 mt-2">
        <span aria-hidden="true" data-theory-ui>✓</span>
        <div class="min-w-0">
            <p lang="en" class="font-mono text-xs">{{ $point['right_en'] }}</p>
            <p lang="uk" class="theory-translation text-xs italic">{{ $point['right_uk'] }}</p>
        </div>
    </div>
    {!! \App\Support\M43NativeHtml::examples($point['examples']) !!}
    @include('theory.partials.point-disclosure', ['index' => 0])
</article>
