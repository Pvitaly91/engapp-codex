@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($rows = $data['rows'] ?? [])

<section id="block-{{ $block->id }}" class="theory-native-block scroll-mt-24">
    <div @if(!($embeddedDetail ?? false)) class="theory-section-card rounded-2xl border border-border/60 bg-card" @endif>
        @if(!($embeddedDetail ?? false) && !empty($data['title']))
            <x-theory-native-header :title="$data['title']" :level="$block->level ?? null" />
        @endif

        <div class="theory-section-body p-5">
            @if(!empty($data['intro']))
                <p class="text-sm text-muted-foreground mb-5 leading-relaxed">{!! $data['intro'] !!}</p>
            @endif

            @if((isset($data['m28_v1']) || (isset($data['m29_v1']) || (isset($data['m30_v1']) || isset($data['m31_v1'])))) && !empty($data['sections']))
                <div class="space-y-4 mb-5">
                    @foreach($data['sections'] as $index => $section)
                        <article class="theory-item rounded-xl p-4 bg-muted/50">
                            <div class="text-sm text-foreground/80 leading-relaxed">{!! $section['description'] !!}</div>
                            @include('theory.partials.point-disclosure', ['index' => $index])
                        </article>
                    @endforeach
                </div>
            @endif
            {{-- Table View --}}
            <div class="theory-table-scroll overflow-x-auto" @if(isset($data['m27_v1']) || (isset($data['m28_v1']) || (isset($data['m29_v1']) || (isset($data['m30_v1']) || isset($data['m31_v1']))))) tabindex="0" role="region" aria-label="{{ $data['title'] }}" @endif>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border">
                            @if((isset($data['m27_v1']) || (isset($data['m28_v1']) || (isset($data['m29_v1']) || (isset($data['m30_v1']) || isset($data['m31_v1']))))) && isset($data['headers']))
                                @foreach($data['headers'] as $header)<th scope="col" class="text-left py-3 px-4 text-xs font-bold text-muted-foreground">{{ $header }}</th>@endforeach
                            @else
                            <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ __('theory_blocks.comparison_table.english_sentence') }}</th>
                            <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ __('theory_blocks.comparison_table.translation') }}</th>
                            <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ __('theory_blocks.comparison_table.forms_notes') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/50">
                        @foreach($rows as $index => $row)
                            <tr class="hover:bg-muted/30 transition-colors">
                                @if((isset($data['m27_v1']) || (isset($data['m28_v1']) || (isset($data['m29_v1']) || (isset($data['m30_v1']) || isset($data['m31_v1']))))) && isset($row['cells']))
                                    @foreach($row['cells'] as $cell)<td class="py-3 px-4 text-sm leading-relaxed">{!! $cell !!}</td>@endforeach
                                @else
                                <td class="py-3 px-4">
                                    <code class="theory-example font-mono text-sm font-semibold text-foreground">
                                        {{ \App\Support\TheoryInlineHtml::render($row['en'] ?? '') }}
                                    </code>
                                </td>
                                <td class="theory-translation py-3 px-4 text-muted-foreground">
                                    {{ \App\Support\TheoryInlineHtml::render($row['ua'] ?? '') }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-sm text-foreground/70">
                                        {!! $row['note'] ?? '' !!}
                                    </span>
                                    @include('theory.partials.point-disclosure', ['index' => $index])
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if((isset($data['m28_v1']) || (isset($data['m29_v1']) || (isset($data['m30_v1']) || isset($data['m31_v1'])))) && !empty($data['outro']))
                <div class="mt-5 text-sm text-foreground/80 leading-relaxed">{!! $data['outro'] !!}</div>
            @endif

            {{-- Warning --}}
            @if(!empty($data['warning']))
                <div class="theory-note mt-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-amber-400 text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-amber-800">
                        {!! $data['warning'] !!}
                    </p>
                </div>
            @endif

            {{-- Block Tags --}}
            @unless($embeddedDetail ?? false)
            <x-text-block-tags :block="$block" />

            {{-- Practice Questions --}}
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
            @endunless
        </div>
    </div>
</section>
