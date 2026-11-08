<div class="theory-item rounded-xl border bg-white m41-mistakes-point p-4">
    @include('engram.theory.blocks-v3.m41-native-point-heading')
    <div class="space-y-3">
        @foreach($point['paragraphs_uk'] as $paragraphIndex => $paragraph)
            @php
                $error = collect($pointConfig['error_paragraphs'])->firstWhere('paragraph_index', $paragraphIndex);
                $fragments = $error ? \App\Support\M41ExistingDesignPackage::paragraphFragments($paragraph, $error) : [['role' => 'plain', 'text' => $paragraph]];
                $paragraphHtml = '';
                foreach($fragments as $fragment) {
                    $text = e($fragment['text']);
                    if($fragment['role'] === 'plain') { $paragraphHtml .= '<span class="m41-error-context">'.$text.'</span>'; continue; }
                    $wrong = $fragment['role'] === 'wrong';
                    $path = $wrong ? 'M6 18L18 6M6 6l12 12' : 'M5 13l4 4L19 7';
                    $box = $wrong ? 'theory-example--wrong bg-rose-50 border-rose-100 text-rose-700' : 'theory-example--right bg-emerald-50 border-emerald-100 text-emerald-700';
                    $paragraphHtml .= '<span class="theory-example m41-error-example flex items-center gap-2.5 rounded-lg border px-3 py-2 '.$box.'"><svg aria-hidden="true" data-theory-ui class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="'.$path.'"/></svg><span class="font-mono text-xs '.($wrong ? 'line-through' : '').'" data-m41-error-fragment="'.$fragment['role'].'">'.$text.'</span></span>';
                }
            @endphp
            <div class="text-sm leading-relaxed" data-m41-paragraph="{{ $paragraphIndex }}">{!! $paragraphHtml !!}</div>
        @endforeach
        @foreach($point['examples'] as $example)
            @include('engram.theory.blocks-v3.m41-native-example')
        @endforeach
    </div>
    @include('theory.partials.point-disclosure', ['index' => 0])
</div>
